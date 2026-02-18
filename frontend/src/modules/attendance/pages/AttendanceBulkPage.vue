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
const assignments = ref<TeacherAssignment[]>([])
const selectedClassGroupId = ref<number | null>(null)
const selectedAssignmentId = ref<number | null>(null)
const selectedDate = ref(new Date().toISOString().split('T')[0])
const students = ref<StudentRecord[]>([])
const loading = ref(false)
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

async function loadClassGroups() {
  try {
    const response = await schoolStructureService.getClassGroups({ per_page: 100 })
    classGroups.value = response.data
  } catch {
    toast.error('Erro ao carregar turmas')
  }
}

async function loadAssignments() {
  if (!selectedClassGroupId.value) return
  try {
    const response = await curriculumService.getAssignments({ class_group_id: selectedClassGroupId.value, per_page: 100 })
    assignments.value = response.data
  } catch {
    toast.error('Erro ao carregar atribuicoes')
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

function assignmentLabel(assignment: TeacherAssignment): string {
  return assignment.curricular_component?.name ?? assignment.experience_field?.name ?? `Atribuicao #${assignment.id}`
}

onMounted(loadClassGroups)
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">Registro de Frequencia</h1>

    <div class="card-section">
      <div class="filter-bar">
        <div class="field">
          <label>Turma *</label>
          <Select v-model="selectedClassGroupId" :options="classGroups" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" @change="onClassGroupChange" />
        </div>
        <div class="field">
          <label>Atribuicao *</label>
          <Select v-model="selectedAssignmentId" :options="assignments" :optionLabel="assignmentLabel" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="field">
          <label>Data *</label>
          <InputText v-model="selectedDate" type="date" class="w-full" />
        </div>
      </div>
    </div>

    <div class="card-section mt-3">
      <EmptyState v-if="!loading && students.length === 0" message="Selecione uma turma para carregar os alunos" />

      <DataTable v-if="students.length > 0" :value="students" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column field="student_name" header="Aluno" sortable />
        <Column header="Presenca" :style="{ width: '200px' }">
          <template #body="{ data }">
            <div class="status-buttons">
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

      <div v-if="students.length > 0" class="submit-bar">
        <Button label="Salvar Frequencia" icon="pi pi-check" :loading="submitting" :disabled="!canSubmit" @click="handleSubmit" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.filter-bar { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.375rem; }
.field label { font-size: 0.8125rem; font-weight: 500; }
.w-full { width: 100%; }
.mt-3 { margin-top: 1.5rem; }
.status-buttons { display: flex; gap: 0.375rem; }
.submit-bar { display: flex; justify-content: flex-end; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; }
</style>
