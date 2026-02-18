<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import Select from 'primevue/select'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import EmptyState from '@/shared/components/EmptyState.vue'
import { attendanceService } from '@/services/attendance.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { curriculumService } from '@/services/curriculum.service'
import { enrollmentService } from '@/services/enrollment.service'
import { useToast } from '@/composables/useToast'
import type { ClassGroup } from '@/types/school-structure'
import type { TeacherAssignment } from '@/types/curriculum'
import type { AttendanceStatus } from '@/types/enums'

interface StudentRecord {
  student_id: number
  student_name: string
  status: AttendanceStatus
}

const toast = useToast()

const classGroups = ref<ClassGroup[]>([])
const assignments = ref<(TeacherAssignment & { label: string })[]>([])
const selectedClassGroupId = ref<number | null>(null)
const selectedAssignmentId = ref<number | null>(null)
const selectedDate = ref(new Date().toISOString().split('T')[0])
const students = ref<StudentRecord[]>([])
const loading = ref(false)
const loadingAssignments = ref(false)
const submitting = ref(false)

const statusOptions: { label: string; value: AttendanceStatus }[] = [
  { label: 'P', value: 'present' },
  { label: 'A', value: 'absent' },
  { label: 'J', value: 'justified' },
  { label: 'D', value: 'dispensed' },
]

const canSubmit = computed(() =>
  selectedClassGroupId.value && selectedAssignmentId.value && selectedDate.value && students.value.length > 0
)

const assignmentPlaceholder = computed(() => {
  if (!selectedClassGroupId.value) return 'Selecione a turma primeiro'
  if (loadingAssignments.value) return 'Carregando...'
  if (assignments.value.length === 0) return 'Nenhuma disciplina vinculada a esta turma'
  return 'Selecione'
})

async function loadClassGroups() {
  try {
    const response = await schoolStructureService.getClassGroups({ per_page: 100 })
    classGroups.value = response.data.map(cg => ({ ...cg, label: [cg.grade_level?.name, cg.name, cg.shift?.name].filter(Boolean).join(' - ') }))
  } catch {
    toast.error('Erro ao carregar turmas')
  }
}

async function loadAssignments() {
  if (!selectedClassGroupId.value) return
  loadingAssignments.value = true
  try {
    const response = await curriculumService.getAssignments({ class_group_id: selectedClassGroupId.value, per_page: 100 })
    assignments.value = response.data.map(a => ({ ...a, label: a.curricular_component?.name ?? a.experience_field?.name ?? `Disciplina #${a.id}` }))
  } catch {
    toast.error('Erro ao carregar disciplinas')
  } finally {
    loadingAssignments.value = false
  }
}

async function loadStudents() {
  if (!selectedClassGroupId.value) return
  loading.value = true
  try {
    const response = await enrollmentService.getEnrollments({
      class_group_id: selectedClassGroupId.value,
      status: 'active',
      per_page: 100,
    })
    students.value = response.data.map(enrollment => ({
      student_id: enrollment.student_id,
      student_name: enrollment.student?.name ?? `Aluno #${enrollment.student_id}`,
      status: 'present' as AttendanceStatus,
    }))
  } catch {
    toast.error('Erro ao carregar alunos')
  } finally {
    loading.value = false
  }
}

function onClassGroupChange() {
  assignments.value = []
  selectedAssignmentId.value = null
  students.value = []
  loadAssignments()
  loadStudents()
}

function getStatusSeverity(status: AttendanceStatus): string {
  const map: Record<AttendanceStatus, string> = { present: 'success', absent: 'danger', justified: 'info', dispensed: 'secondary' }
  return map[status]
}

async function handleSubmit() {
  if (!selectedClassGroupId.value || !selectedAssignmentId.value) return
  submitting.value = true
  try {
    await attendanceService.bulkRecord({
      class_group_id: selectedClassGroupId.value,
      teacher_assignment_id: selectedAssignmentId.value,
      date: selectedDate.value,
      records: students.value.map(s => ({ student_id: s.student_id, status: s.status })),
    })
    toast.success('Frequencia registrada')
  } catch (error: any) {
    toast.error(error.response?.data?.error ?? 'Erro ao registrar frequencia')
  } finally {
    submitting.value = false
  }
}

onMounted(loadClassGroups)
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Registro de Frequencia</h1>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <div class="grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Turma *</label>
          <Select v-model="selectedClassGroupId" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Selecione" class="w-full" filter @change="onClassGroupChange" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Disciplina *</label>
          <Select v-model="selectedAssignmentId" :options="assignments" optionLabel="label" optionValue="id" :placeholder="assignmentPlaceholder" :disabled="!selectedClassGroupId || assignments.length === 0" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Data *</label>
          <InputText v-model="selectedDate" type="date" class="w-full" />
        </div>
      </div>
    </div>

    <div class="mt-6 rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <EmptyState v-if="!loading && students.length === 0" message="Selecione uma turma para carregar os alunos" />

      <DataTable v-if="students.length > 0" :value="students" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column field="student_name" header="Aluno" sortable />
        <Column header="Presenca" :style="{ width: '200px' }">
          <template #body="{ data }">
            <div class="flex gap-1.5">
              <Button
                v-for="opt in statusOptions"
                :key="opt.value"
                :label="opt.label"
                :severity="data.status === opt.value ? getStatusSeverity(opt.value) as any : 'secondary'"
                :outlined="data.status !== opt.value"
                size="small"
                @click="data.status = opt.value"
              />
            </div>
          </template>
        </Column>
      </DataTable>

      <div v-if="students.length > 0" class="mt-4 flex justify-end border-t border-[#E0E0E0] pt-4">
        <Button label="Salvar Frequencia" icon="pi pi-check" :loading="submitting" :disabled="!canSubmit" @click="handleSubmit" />
      </div>
    </div>
  </div>
</template>
