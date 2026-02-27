<script setup lang="ts">
import { onMounted, ref, computed, watch } from 'vue'
import Select from 'primevue/select'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dialog from 'primevue/dialog'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import EmptyState from '@/shared/components/EmptyState.vue'
import { attendanceService } from '@/services/attendance.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { curriculumService } from '@/services/curriculum.service'
import { enrollmentService } from '@/services/enrollment.service'
import { useToast } from '@/composables/useToast'
import { extractApiError } from '@/shared/utils/api-error'
import type { ClassGroup } from '@/types/school-structure'
import type { TeacherAssignment } from '@/types/curriculum'
import type { AttendanceStatus } from '@/types/enums'

interface StudentRecord {
  student_id: number
  student_name: string
  status: AttendanceStatus
  notes: string | null
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
const loadingRecords = ref(false)
const submitting = ref(false)

const statusOptions: { label: string; value: AttendanceStatus }[] = [
  { label: 'P', value: 'present' },
  { label: 'A', value: 'absent' },
  { label: 'J', value: 'justified_absence' },
  { label: 'D', value: 'excused' },
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
    classGroups.value = response.data.map(cg => ({ ...cg, label: [cg.grade_level?.name, cg.name, cg.shift?.name_label].filter(Boolean).join(' - ') }))
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
      notes: null,
    }))
    await loadExistingRecords()
  } catch {
    toast.error('Erro ao carregar alunos')
  } finally {
    loading.value = false
  }
}

async function loadExistingRecords() {
  if (!selectedClassGroupId.value || !selectedAssignmentId.value || !selectedDate.value || students.value.length === 0) return

  loadingRecords.value = true
  try {
    const response = await attendanceService.getByClass(selectedClassGroupId.value, {
      teacher_assignment_id: selectedAssignmentId.value,
      date: selectedDate.value,
      per_page: 200,
    })

    if (response.data.length === 0) return

    const recordMap = new Map(response.data.map(r => [r.student_id, { status: r.status, notes: r.notes }]))

    students.value = students.value.map(s => {
      const existing = recordMap.get(s.student_id)
      if (!existing) return s
      return {
        ...s,
        status: existing.status as AttendanceStatus,
        notes: existing.notes ?? null,
      }
    })
  } catch {
    toast.error('Erro ao carregar registros existentes')
  } finally {
    loadingRecords.value = false
  }
}

function onClassGroupChange() {
  assignments.value = []
  selectedAssignmentId.value = null
  students.value = []
  loadAssignments()
  loadStudents()
}

function resetStudentStatuses() {
  students.value = students.value.map(s => ({
    ...s,
    status: 'present' as AttendanceStatus,
    notes: null,
  }))
}

watch(
  () => [selectedAssignmentId.value, selectedDate.value],
  async () => {
    if (!selectedClassGroupId.value || !selectedAssignmentId.value || !selectedDate.value || students.value.length === 0) return
    resetStudentStatuses()
    await loadExistingRecords()
  },
)

function getStatusSeverity(status: AttendanceStatus): string {
  const map: Record<AttendanceStatus, string> = { present: 'success', absent: 'danger', justified_absence: 'info', excused: 'secondary' }
  return map[status]
}

const notesDialogVisible = ref(false)
const notesDialogStudent = ref<StudentRecord | null>(null)
const notesDialogStatus = ref<AttendanceStatus | null>(null)
const notesDialogText = ref('')

const notesDialogTitle = computed(() => {
  if (!notesDialogStudent.value || !notesDialogStatus.value) return ''
  const statusLabel = notesDialogStatus.value === 'justified_absence' ? 'Falta Justificada' : 'Dispensado'
  return `${statusLabel} — ${notesDialogStudent.value.student_name}`
})

const notesDialogPlaceholder = computed(() =>
  notesDialogStatus.value === 'justified_absence'
    ? 'Ex: Atestado medico, consulta odontologica...'
    : 'Ex: Participacao em evento escolar, dispensa medica...',
)

function handleStatusClick(student: StudentRecord, status: AttendanceStatus) {
  if (status === 'justified_absence' || status === 'excused') {
    notesDialogStudent.value = student
    notesDialogStatus.value = status
    notesDialogText.value = (student.status === status && student.notes) ? student.notes : ''
    notesDialogVisible.value = true
    return
  }
  student.status = status
  student.notes = null
}

function confirmNotes() {
  if (!notesDialogStudent.value || !notesDialogStatus.value) return
  notesDialogStudent.value.status = notesDialogStatus.value
  notesDialogStudent.value.notes = notesDialogText.value.trim() || null
  notesDialogVisible.value = false
}

function cancelNotes() {
  notesDialogVisible.value = false
}

function openNotesEdit(student: StudentRecord) {
  notesDialogStudent.value = student
  notesDialogStatus.value = student.status
  notesDialogText.value = student.notes ?? ''
  notesDialogVisible.value = true
}

async function handleSubmit() {
  if (!selectedClassGroupId.value || !selectedAssignmentId.value) return
  submitting.value = true
  try {
    const saved = await attendanceService.bulkRecord({
      class_group_id: selectedClassGroupId.value,
      teacher_assignment_id: selectedAssignmentId.value,
      date: selectedDate.value,
      records: students.value.map(s => ({ student_id: s.student_id, status: s.status, notes: s.notes })),
    })
    const savedMap = new Map(saved.map(r => [r.student_id, { status: r.status, notes: r.notes }]))
    students.value = students.value.map(s => {
      const rec = savedMap.get(s.student_id)
      if (!rec) return s
      return { ...s, status: rec.status as AttendanceStatus, notes: rec.notes ?? null }
    })
    toast.success('Frequencia registrada')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao registrar frequencia'))
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
          <InputText v-model="selectedDate" type="date" class="w-full" :disabled="!selectedClassGroupId" />
        </div>
      </div>
    </div>

    <div class="mt-6 rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <div v-if="students.length > 0" class="mb-4 rounded-md border border-[#E0E0E0] bg-[#FAFAFA] px-4 py-3">
        <p class="mb-2 text-[0.8125rem] font-semibold text-[#323130]">Legenda — clique no botao correspondente para cada aluno:</p>
        <div class="flex flex-wrap gap-x-6 gap-y-1.5">
          <span class="flex items-center gap-1.5 text-[0.8125rem]">
            <span class="inline-flex h-6 w-6 items-center justify-center rounded bg-green-100 text-xs font-bold text-green-800">P</span>
            Presente
          </span>
          <span class="flex items-center gap-1.5 text-[0.8125rem]">
            <span class="inline-flex h-6 w-6 items-center justify-center rounded bg-red-100 text-xs font-bold text-red-800">A</span>
            Ausente (falta)
          </span>
          <span class="flex items-center gap-1.5 text-[0.8125rem]">
            <span class="inline-flex h-6 w-6 items-center justify-center rounded bg-blue-100 text-xs font-bold text-blue-800">J</span>
            Falta Justificada (com atestado)
          </span>
          <span class="flex items-center gap-1.5 text-[0.8125rem]">
            <span class="inline-flex h-6 w-6 items-center justify-center rounded bg-gray-100 text-xs font-bold text-gray-600">D</span>
            Dispensado
          </span>
        </div>
      </div>

      <EmptyState v-if="!loading && !loadingRecords && students.length === 0" message="Selecione uma turma para carregar os alunos" />

      <DataTable v-if="students.length > 0" :value="students" :loading="loading || loadingRecords" stripedRows responsiveLayout="scroll">
        <Column field="student_name" header="Aluno" sortable />
        <Column header="Presenca" :style="{ width: '240px' }">
          <template #body="{ data }">
            <div class="flex items-center gap-1.5">
              <Button
                v-for="opt in statusOptions"
                :key="opt.value"
                :label="opt.label"
                :severity="(data.status === opt.value ? getStatusSeverity(opt.value) : 'secondary') as any"
                :outlined="data.status !== opt.value"
                size="small"
                @click="handleStatusClick(data, opt.value)"
              />
              <Button
                v-if="data.notes && (data.status === 'justified_absence' || data.status === 'excused')"
                icon="pi pi-comment"
                severity="info"
                text
                rounded
                size="small"
                v-tooltip.top="data.notes"
                @click="openNotesEdit(data)"
              />
            </div>
          </template>
        </Column>
      </DataTable>

      <div v-if="students.length > 0" class="mt-4 flex justify-end border-t border-[#E0E0E0] pt-4">
        <Button label="Salvar Frequencia" icon="pi pi-check" :loading="submitting" :disabled="!canSubmit" @click="handleSubmit" />
      </div>

      <Dialog v-model:visible="notesDialogVisible" :header="notesDialogTitle" :style="{ width: '480px' }" modal :draggable="false">
        <div class="flex flex-col gap-3">
          <p class="text-[0.8125rem] text-[#605E5C]">
            {{ notesDialogStatus === 'justified_absence' ? 'Informe o motivo da falta justificada:' : 'Informe o motivo da dispensa:' }}
          </p>
          <Textarea v-model="notesDialogText" :placeholder="notesDialogPlaceholder" rows="3" class="w-full" autofocus />
        </div>
        <template #footer>
          <Button label="Cancelar" icon="pi pi-times" text @click="cancelNotes" />
          <Button label="Confirmar" icon="pi pi-check" @click="confirmNotes" />
        </template>
      </Dialog>
    </div>
  </div>
</template>
