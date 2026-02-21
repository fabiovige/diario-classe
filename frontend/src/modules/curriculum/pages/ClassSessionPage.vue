<script setup lang="ts">
import { onMounted, ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Textarea from 'primevue/textarea'
import InputNumber from 'primevue/inputnumber'
import Select from 'primevue/select'
import Dialog from 'primevue/dialog'
import Message from 'primevue/message'
import ProgressSpinner from 'primevue/progressspinner'
import EmptyState from '@/shared/components/EmptyState.vue'
import { curriculumService } from '@/services/curriculum.service'
import { enrollmentService } from '@/services/enrollment.service'
import { attendanceService } from '@/services/attendance.service'
import { classRecordService } from '@/services/class-record.service'
import { assessmentService } from '@/services/assessment.service'
import { academicCalendarService } from '@/services/academic-calendar.service'
import { useToast } from '@/composables/useToast'
import { extractApiError } from '@/shared/utils/api-error'
import type { TeacherAssignment } from '@/types/curriculum'
import type { AssessmentPeriod } from '@/types/academic-calendar'
import type { AssessmentConfig, AssessmentInstrument, ConceptualScale } from '@/types/assessment'
import type { LessonRecord } from '@/types/class-record'
import type { AttendanceStatus } from '@/types/enums'

interface StudentRecord {
  student_id: number
  student_name: string
  status: AttendanceStatus
  notes: string | null
}

interface StudentGrade {
  student_id: number
  student_name: string
  numeric_value: number | null
  conceptual_value: string | null
}

const route = useRoute()
const router = useRouter()
const toast = useToast()

const assignmentId = computed(() => Number(route.params.assignmentId))
const date = computed(() => (route.query.date as string) ?? new Date().toISOString().split('T')[0])

const assignment = ref<TeacherAssignment | null>(null)
const loading = ref(true)

const attendanceSaved = ref(false)
const lessonSaved = ref(false)

const students = ref<StudentRecord[]>([])
const submittingAttendance = ref(false)

const statusOptions: { label: string; value: AttendanceStatus }[] = [
  { label: 'P', value: 'present' },
  { label: 'A', value: 'absent' },
  { label: 'J', value: 'justified_absence' },
  { label: 'D', value: 'excused' },
]

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

const lessonForm = ref({
  id: null as number | null,
  content: '',
  methodology: '',
  observations: '',
  class_count: 1,
})

const submittingLesson = ref(false)

const periods = ref<AssessmentPeriod[]>([])
const instruments = ref<AssessmentInstrument[]>([])
const conceptualScales = ref<ConceptualScale[]>([])
const config = ref<AssessmentConfig | null>(null)
const selectedPeriodId = ref<number | null>(null)
const selectedInstrumentId = ref<number | null>(null)
const gradeStudents = ref<StudentGrade[]>([])
const loadingGrades = ref(false)
const submittingGrades = ref(false)
const hasOpenPeriod = ref(false)

const isDescriptive = computed(() => config.value?.grade_type === 'descriptive')
const isConceptual = computed(() => config.value?.grade_type === 'conceptual')
const isNumeric = computed(() => !config.value || config.value.grade_type === 'numeric')

const conceptualOptions = computed(() =>
  conceptualScales.value.map(s => ({ label: `${s.code} - ${s.label}`, value: s.code })),
)

const canSubmitGrades = computed(() =>
  selectedPeriodId.value && selectedInstrumentId.value && gradeStudents.value.length > 0,
)

const contextLabel = computed(() => {
  if (!assignment.value) return ''
  const cg = assignment.value.class_group
  const parts = [cg?.grade_level?.name, cg?.name, cg?.shift?.name_label].filter(Boolean)
  const subject = assignment.value.curricular_component?.name ?? assignment.value.experience_field?.name ?? ''
  return `${parts.join(' - ')} | ${subject}`
})

const formattedDate = computed(() => {
  const [y, m, d] = date.value.split('-')
  return `${d}/${m}/${y}`
})

function getStatusSeverity(status: AttendanceStatus): string {
  const map: Record<AttendanceStatus, string> = {
    present: 'success',
    absent: 'danger',
    justified_absence: 'info',
    excused: 'secondary',
  }
  return map[status]
}

async function loadInitialData() {
  loading.value = true
  try {
    const assignmentData = await curriculumService.getAssignment(assignmentId.value)
    assignment.value = assignmentData

    const classGroupId = assignmentData.class_group_id

    const [enrollmentsRes, attendanceRes, recordsRes] = await Promise.all([
      enrollmentService.getEnrollments({ class_group_id: classGroupId, status: 'active', per_page: 100 }),
      attendanceService.getByClass(classGroupId, {
        teacher_assignment_id: assignmentId.value,
        date: date.value,
        per_page: 200,
      }),
      classRecordService.getRecords({
        teacher_assignment_id: assignmentId.value,
        date_from: date.value,
        date_to: date.value,
      }),
    ])

    const attendanceMap = new Map(attendanceRes.data.map(r => [r.student_id, { status: r.status, notes: r.notes }]))

    students.value = enrollmentsRes.data.map(enrollment => {
      const existing = attendanceMap.get(enrollment.student_id)
      return {
        student_id: enrollment.student_id,
        student_name: enrollment.student?.name ?? `Aluno #${enrollment.student_id}`,
        status: (existing?.status as AttendanceStatus) ?? 'present',
        notes: existing?.notes ?? null,
      }
    })

    if (attendanceMap.size > 0) {
      attendanceSaved.value = true
    }

    const existingRecord = recordsRes.data?.[0]
    if (existingRecord) {
      lessonForm.value = {
        id: existingRecord.id,
        content: existingRecord.content,
        methodology: existingRecord.methodology,
        observations: existingRecord.observations,
        class_count: existingRecord.class_count,
      }
      lessonSaved.value = true
    }

    await loadAssessmentData(assignmentData)
  } catch {
    toast.error('Erro ao carregar dados da aula')
  } finally {
    loading.value = false
  }
}

async function loadAssessmentData(assignmentData: TeacherAssignment) {
  const cg = assignmentData.class_group
  if (!cg) return

  try {
    const [periodsRes, configsRes] = await Promise.all([
      academicCalendarService.getPeriods({
        academic_year_id: cg.academic_year_id,
        per_page: 100,
      }),
      assessmentService.getConfigs({
        school_id: cg.academic_year?.school_id,
        academic_year_id: cg.academic_year_id,
        grade_level_id: cg.grade_level_id,
        per_page: 1,
      }),
    ])

    const openPeriods = periodsRes.data.filter(
      p => p.status === 'open' && p.start_date && p.end_date && p.start_date <= date.value && p.end_date >= date.value,
    )
    periods.value = openPeriods
    hasOpenPeriod.value = openPeriods.length > 0

    const firstConfig = configsRes.data?.[0] ?? null
    config.value = firstConfig
    instruments.value = firstConfig?.instruments ?? []
    conceptualScales.value = firstConfig?.conceptual_scales ?? []

    gradeStudents.value = students.value.map(s => ({
      student_id: s.student_id,
      student_name: s.student_name,
      numeric_value: null,
      conceptual_value: null,
    }))
  } catch {
    // assessment data is optional
  }
}

async function loadExistingGrades() {
  if (!selectedPeriodId.value || !selectedInstrumentId.value || !assignment.value) return

  loadingGrades.value = true
  try {
    const response = await assessmentService.getGrades({
      class_group_id: assignment.value.class_group_id,
      teacher_assignment_id: assignmentId.value,
      assessment_period_id: selectedPeriodId.value,
      assessment_instrument_id: selectedInstrumentId.value,
      per_page: 200,
    })

    const gradeMap = new Map(response.data.map(g => [g.student_id, g]))

    gradeStudents.value = gradeStudents.value.map(s => {
      const existing = gradeMap.get(s.student_id)
      if (!existing) return { ...s, numeric_value: null, conceptual_value: null }
      return {
        ...s,
        numeric_value: existing.numeric_value,
        conceptual_value: existing.conceptual_value,
      }
    })
  } catch {
    toast.error('Erro ao carregar notas existentes')
  } finally {
    loadingGrades.value = false
  }
}

watch(() => [selectedPeriodId.value, selectedInstrumentId.value], () => {
  if (!selectedPeriodId.value || !selectedInstrumentId.value) return
  loadExistingGrades()
})

async function handleSaveAttendance() {
  if (!assignment.value) return
  submittingAttendance.value = true
  try {
    await attendanceService.bulkRecord({
      class_group_id: assignment.value.class_group_id,
      teacher_assignment_id: assignmentId.value,
      date: date.value,
      records: students.value.map(s => ({ student_id: s.student_id, status: s.status, notes: s.notes })),
    })
    attendanceSaved.value = true
    toast.success('Chamada registrada')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao registrar chamada'))
  } finally {
    submittingAttendance.value = false
  }
}

async function handleSaveLesson() {
  if (!assignment.value) return
  submittingLesson.value = true
  try {
    const payload = {
      class_group_id: assignment.value.class_group_id,
      teacher_assignment_id: assignmentId.value,
      date: date.value,
      content: lessonForm.value.content,
      methodology: lessonForm.value.methodology,
      observations: lessonForm.value.observations,
      class_count: lessonForm.value.class_count,
    }

    let result: LessonRecord
    if (lessonForm.value.id) {
      result = await classRecordService.updateRecord(lessonForm.value.id, payload)
    } else {
      result = await classRecordService.createRecord(payload)
    }
    lessonForm.value.id = result.id
    lessonSaved.value = true
    toast.success(lessonForm.value.id ? 'Registro atualizado' : 'Registro criado')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao salvar registro'))
  } finally {
    submittingLesson.value = false
  }
}

async function handleSaveGrades() {
  if (!assignment.value || !selectedPeriodId.value || !selectedInstrumentId.value) return
  submittingGrades.value = true
  try {
    await assessmentService.bulkGrades({
      class_group_id: assignment.value.class_group_id,
      teacher_assignment_id: assignmentId.value,
      assessment_period_id: selectedPeriodId.value,
      assessment_instrument_id: selectedInstrumentId.value,
      grades: gradeStudents.value.map(s => ({
        student_id: s.student_id,
        numeric_value: s.numeric_value ?? undefined,
        conceptual_value: s.conceptual_value ?? undefined,
      })),
    })
    toast.success('Notas registradas')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao registrar notas'))
  } finally {
    submittingGrades.value = false
  }
}

onMounted(loadInitialData)
</script>

<template>
  <div class="p-6">
    <div class="mb-6">
      <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" size="small" @click="router.push('/my-classes')" />
      <div v-if="assignment" class="mt-3">
        <h1 class="text-2xl font-semibold text-[#0078D4]">{{ contextLabel }}</h1>
        <p class="mt-1 text-[0.875rem] text-[#605E5C]">{{ formattedDate }}</p>
      </div>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-16">
      <ProgressSpinner strokeWidth="3" />
    </div>

    <Tabs v-if="!loading && assignment" value="attendance">
      <TabList>
        <Tab value="attendance">
          <span class="flex items-center gap-2">
            <i v-if="attendanceSaved" class="pi pi-check-circle text-green-600" />
            Chamada
          </span>
        </Tab>
        <Tab value="lesson">
          <span class="flex items-center gap-2">
            <i v-if="lessonSaved" class="pi pi-check-circle text-green-600" />
            Registro de Aula
          </span>
        </Tab>
        <Tab v-if="hasOpenPeriod" value="assessment">Avaliacao</Tab>
      </TabList>

      <TabPanels>
        <TabPanel value="attendance">
          <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
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

            <EmptyState v-if="students.length === 0" message="Nenhum aluno matriculado nesta turma" />

            <DataTable v-if="students.length > 0" :value="students" stripedRows responsiveLayout="scroll">
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
              <Button label="Salvar Chamada" icon="pi pi-check" :loading="submittingAttendance" @click="handleSaveAttendance" />
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
        </TabPanel>

        <TabPanel value="lesson">
          <div class="max-w-[700px] rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
            <form @submit.prevent="handleSaveLesson" class="flex flex-col gap-4">
              <div class="flex flex-col gap-1.5">
                <label class="text-[0.8125rem] font-medium">Conteudo *</label>
                <Textarea v-model="lessonForm.content" rows="4" class="w-full" />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-[0.8125rem] font-medium">Metodologia</label>
                <Textarea v-model="lessonForm.methodology" rows="3" class="w-full" />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-[0.8125rem] font-medium">Observacoes</label>
                <Textarea v-model="lessonForm.observations" rows="3" class="w-full" />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-[0.8125rem] font-medium">Quantidade de Aulas *</label>
                <InputNumber v-model="lessonForm.class_count" :min="1" :max="10" class="w-full" />
              </div>
              <div class="mt-4 flex justify-end">
                <Button type="submit" :label="lessonForm.id ? 'Atualizar Registro' : 'Salvar Registro'" icon="pi pi-check" :loading="submittingLesson" />
              </div>
            </form>
          </div>
        </TabPanel>

        <TabPanel v-if="hasOpenPeriod" value="assessment">
          <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
            <div class="mb-4 grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-4">
              <div class="flex flex-col gap-1.5">
                <label class="text-[0.8125rem] font-medium">Periodo *</label>
                <Select v-model="selectedPeriodId" :options="periods" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-[0.8125rem] font-medium">Instrumento *</label>
                <Select
                  v-model="selectedInstrumentId"
                  :options="instruments"
                  optionLabel="name"
                  optionValue="id"
                  :placeholder="instruments.length === 0 ? 'Nenhum instrumento configurado' : 'Selecione'"
                  :disabled="instruments.length === 0"
                  class="w-full"
                />
              </div>
            </div>

            <Message v-if="isDescriptive" severity="info" :closable="false">
              Esta turma utiliza avaliacao descritiva. Acesse a pagina de
              <router-link to="/assessment/descriptive" class="font-semibold underline">Relatorios Descritivos</router-link>
              para registrar as avaliacoes.
            </Message>

            <template v-if="!isDescriptive">
              <EmptyState v-if="gradeStudents.length === 0" message="Nenhum aluno para avaliar" />

              <DataTable v-if="gradeStudents.length > 0" :value="gradeStudents" :loading="loadingGrades" stripedRows responsiveLayout="scroll">
                <Column field="student_name" header="Aluno" sortable />
                <Column v-if="isNumeric" header="Nota" :style="{ width: '150px' }">
                  <template #body="{ data }">
                    <InputNumber v-model="data.numeric_value" :min="config?.scale_min ?? 0" :max="config?.scale_max ?? 10" :maxFractionDigits="config?.rounding_precision ?? 2" class="w-full" />
                  </template>
                </Column>
                <Column v-if="isConceptual" header="Conceito" :style="{ width: '180px' }">
                  <template #body="{ data }">
                    <Select v-model="data.conceptual_value" :options="conceptualOptions" optionLabel="label" optionValue="value" placeholder="--" class="w-full" showClear />
                  </template>
                </Column>
              </DataTable>

              <div v-if="gradeStudents.length > 0 && !isDescriptive" class="mt-4 flex justify-end border-t border-[#E0E0E0] pt-4">
                <Button label="Salvar Notas" icon="pi pi-check" :loading="submittingGrades" :disabled="!canSubmitGrades" @click="handleSaveGrades" />
              </div>
            </template>
          </div>
        </TabPanel>
      </TabPanels>
    </Tabs>
  </div>
</template>
