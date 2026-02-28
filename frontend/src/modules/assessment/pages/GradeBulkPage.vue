<script setup lang="ts">
import { onMounted, ref, computed, watch } from 'vue'
import Select from 'primevue/select'
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Message from 'primevue/message'
import EmptyState from '@/shared/components/EmptyState.vue'
import { assessmentService } from '@/services/assessment.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { curriculumService } from '@/services/curriculum.service'
import { academicCalendarService } from '@/services/academic-calendar.service'
import { enrollmentService } from '@/services/enrollment.service'
import { useToast } from '@/composables/useToast'
import { useSchoolScope } from '@/composables/useSchoolScope'
import { useListFilters } from '@/composables/useListFilters'
import { extractApiError } from '@/shared/utils/api-error'
import type { School, ClassGroup } from '@/types/school-structure'
import type { TeacherAssignment } from '@/types/curriculum'
import type { AssessmentPeriod } from '@/types/academic-calendar'
import type { AssessmentConfig, AssessmentInstrument, ConceptualScale } from '@/types/assessment'

interface StudentGrade {
  student_id: number
  student_name: string
  numeric_value: number | null
  conceptual_value: string | null
}

const toast = useToast()
const { shouldShowSchoolFilter, userSchoolId, userSchoolName } = useSchoolScope()

const schools = ref<School[]>([])
const classGroups = ref<(ClassGroup & { label: string })[]>([])
const assignments = ref<(TeacherAssignment & { label: string })[]>([])
const periods = ref<AssessmentPeriod[]>([])
const instruments = ref<AssessmentInstrument[]>([])
const conceptualScales = ref<ConceptualScale[]>([])
const config = ref<AssessmentConfig | null>(null)

const selectedSchoolId = ref<number | null>(null)
const selectedClassGroupId = ref<number | null>(null)
const selectedAssignmentId = ref<number | null>(null)
const selectedPeriodId = ref<number | null>(null)
const selectedInstrumentId = ref<number | null>(null)
let initializing = false

const { initFromQuery, syncToUrl, clearAll } = useListFilters([
  { key: 'school_id', ref: selectedSchoolId, type: 'number' },
  { key: 'class_group_id', ref: selectedClassGroupId, type: 'number' },
])

const students = ref<StudentGrade[]>([])
const loading = ref(false)
const loadingDeps = ref(false)
const loadingGrades = ref(false)
const submitting = ref(false)

const selectedClassGroup = computed(() =>
  classGroups.value.find(cg => cg.id === selectedClassGroupId.value)
)

const isDescriptive = computed(() => config.value?.grade_type === 'descriptive')
const isConceptual = computed(() => config.value?.grade_type === 'conceptual')
const isNumeric = computed(() => !config.value || config.value.grade_type === 'numeric')

const canLoadGrades = computed(() =>
  selectedClassGroupId.value && selectedAssignmentId.value && selectedPeriodId.value && selectedInstrumentId.value
)

const canSubmit = computed(() =>
  canLoadGrades.value && students.value.length > 0
)

const depsPlaceholder = computed(() => {
  if (!selectedClassGroupId.value) return 'Selecione a turma primeiro'
  if (loadingDeps.value) return 'Carregando...'
  return 'Selecione'
})

const assignmentPlaceholder = computed(() => {
  if (!selectedClassGroupId.value) return 'Selecione a turma primeiro'
  if (loadingDeps.value) return 'Carregando...'
  if (assignments.value.length === 0) return 'Nenhuma disciplina vinculada'
  return 'Selecione'
})

const instrumentPlaceholder = computed(() => {
  if (!selectedClassGroupId.value) return 'Selecione a turma primeiro'
  if (loadingDeps.value) return 'Carregando...'
  if (instruments.value.length === 0) return 'Nenhum instrumento configurado'
  return 'Selecione'
})

const conceptualOptions = computed(() =>
  conceptualScales.value.map(s => ({ label: `${s.code} - ${s.label}`, value: s.code }))
)

const hasActiveFilters = computed(() => selectedSchoolId.value !== null || selectedClassGroupId.value !== null)

async function loadSchools() {
  try {
    const response = await schoolStructureService.getSchools({ per_page: 200 })
    schools.value = response.data
  } catch {
    toast.error('Erro ao carregar escolas')
  }
}

async function loadClassGroups() {
  if (!selectedSchoolId.value) {
    classGroups.value = []
    return
  }
  try {
    const response = await schoolStructureService.getClassGroups({ school_id: selectedSchoolId.value, per_page: 200 })
    classGroups.value = response.data.map(cg => ({
      ...cg,
      label: [cg.grade_level?.name, cg.name, cg.shift?.name_label].filter(Boolean).join(' - '),
    })).sort((a, b) => a.label.localeCompare(b.label, 'pt-BR'))
  } catch {
    toast.error('Erro ao carregar turmas')
  }
}

watch(selectedSchoolId, () => {
  if (initializing) return
  selectedClassGroupId.value = null
  classGroups.value = []
  resetDependencies()
  loadClassGroups()
  syncToUrl()
})

async function loadDependencies() {
  if (!selectedClassGroupId.value) return
  const cg = selectedClassGroup.value
  if (!cg) return

  loadingDeps.value = true
  try {
    const [assignmentsRes, periodsRes, studentsRes, configsRes] = await Promise.all([
      curriculumService.getAssignments({ class_group_id: cg.id, per_page: 100 }),
      academicCalendarService.getPeriods({ academic_year_id: cg.academic_year_id, per_page: 100 }),
      enrollmentService.getEnrollments({ class_group_id: cg.id, status: 'active', per_page: 100 }),
      assessmentService.getConfigs({
        school_id: cg.academic_year?.school_id,
        academic_year_id: cg.academic_year_id,
        grade_level_id: cg.grade_level_id,
        per_page: 1,
      }),
    ])

    assignments.value = assignmentsRes.data.map(a => ({
      ...a,
      label: a.curricular_component?.name ?? a.experience_field?.name ?? `Disciplina #${a.id}`,
    }))
    periods.value = periodsRes.data
    students.value = studentsRes.data.map(enrollment => ({
      student_id: enrollment.student_id,
      student_name: enrollment.student?.name ?? `Aluno #${enrollment.student_id}`,
      numeric_value: null,
      conceptual_value: null,
    }))

    const firstConfig = configsRes.data?.[0] ?? null
    config.value = firstConfig
    instruments.value = firstConfig?.instruments ?? []
    conceptualScales.value = firstConfig?.conceptual_scales ?? []
  } catch {
    toast.error('Erro ao carregar dados da turma')
  } finally {
    loadingDeps.value = false
  }
}

async function loadExistingGrades() {
  if (!canLoadGrades.value) return

  loadingGrades.value = true
  try {
    const response = await assessmentService.getGrades({
      class_group_id: selectedClassGroupId.value,
      teacher_assignment_id: selectedAssignmentId.value,
      assessment_period_id: selectedPeriodId.value,
      assessment_instrument_id: selectedInstrumentId.value,
      per_page: 200,
    })

    const gradeMap = new Map(response.data.map(g => [g.student_id, g]))

    students.value = students.value.map(s => {
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

function resetDependencies() {
  assignments.value = []
  periods.value = []
  instruments.value = []
  conceptualScales.value = []
  config.value = null
  students.value = []
  selectedAssignmentId.value = null
  selectedPeriodId.value = null
  selectedInstrumentId.value = null
}

function onClassGroupChange() {
  resetDependencies()
  syncToUrl()
  loadDependencies()
}

function clearFilters() {
  clearAll()
  resetDependencies()
  classGroups.value = []
}

watch(
  () => [selectedAssignmentId.value, selectedPeriodId.value, selectedInstrumentId.value],
  () => {
    if (!canLoadGrades.value) return
    loadExistingGrades()
  },
)

function clampGrade(data: StudentGrade) {
  if (data.numeric_value === null) return
  const min = config.value?.scale_min ?? 0
  const max = config.value?.scale_max ?? 10
  if (data.numeric_value < min) data.numeric_value = min
  if (data.numeric_value > max) data.numeric_value = max
}

async function handleSubmit() {
  if (!selectedClassGroupId.value || !selectedAssignmentId.value || !selectedPeriodId.value || !selectedInstrumentId.value) return
  submitting.value = true
  try {
    await assessmentService.bulkGrades({
      class_group_id: selectedClassGroupId.value,
      teacher_assignment_id: selectedAssignmentId.value,
      assessment_period_id: selectedPeriodId.value,
      assessment_instrument_id: selectedInstrumentId.value,
      grades: students.value.map(s => ({
        student_id: s.student_id,
        numeric_value: s.numeric_value ?? undefined,
        conceptual_value: s.conceptual_value ?? undefined,
      })),
    })
    toast.success('Notas registradas')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao registrar notas'))
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  if (shouldShowSchoolFilter.value) {
    await loadSchools()
  }
  initializing = true
  initFromQuery()
  initializing = false

  if (!shouldShowSchoolFilter.value && userSchoolId.value && !selectedSchoolId.value) {
    selectedSchoolId.value = userSchoolId.value
    return
  }

  if (selectedSchoolId.value) {
    await loadClassGroups()
    if (selectedClassGroupId.value) {
      await loadDependencies()
    }
  }
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Lancamento de Notas</h1>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <div class="flex flex-wrap items-end gap-4">
        <div v-if="shouldShowSchoolFilter" class="flex flex-col gap-1.5 w-56">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <Select v-model="selectedSchoolId" :options="schools" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" filter showClear />
        </div>
        <div v-if="!shouldShowSchoolFilter && userSchoolName" class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <span class="flex h-[2.375rem] items-center rounded-md border border-[#E0E0E0] bg-[#F5F5F5] px-3 text-sm">{{ userSchoolName }}</span>
        </div>
        <div class="flex flex-col gap-1.5 w-56">
          <label class="text-[0.8125rem] font-medium">Turma *</label>
          <Select v-model="selectedClassGroupId" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Selecione" class="w-full" :disabled="!selectedSchoolId" filter @change="onClassGroupChange" />
        </div>
        <div class="flex flex-col gap-1.5 w-56">
          <label class="text-[0.8125rem] font-medium">Disciplina *</label>
          <Select v-model="selectedAssignmentId" :options="assignments" optionLabel="label" optionValue="id" :placeholder="assignmentPlaceholder" :disabled="!selectedClassGroupId || loadingDeps || assignments.length === 0" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5 w-48">
          <label class="text-[0.8125rem] font-medium">Periodo *</label>
          <Select v-model="selectedPeriodId" :options="periods" optionLabel="name" optionValue="id" :placeholder="depsPlaceholder" :disabled="!selectedClassGroupId || loadingDeps" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5 w-56">
          <label class="text-[0.8125rem] font-medium">Instrumento *</label>
          <Select v-model="selectedInstrumentId" :options="instruments" optionLabel="name" optionValue="id" :placeholder="instrumentPlaceholder" :disabled="!selectedClassGroupId || loadingDeps || instruments.length === 0" class="w-full" />
        </div>
        <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text @click="clearFilters" />
      </div>
    </div>

    <Message v-if="isDescriptive" severity="info" class="mt-4" :closable="false">
      Esta turma utiliza avaliacao descritiva. Acesse a pagina de
      <router-link to="/assessment/descriptive" class="font-semibold underline">Relatorios Descritivos</router-link>
      para registrar as avaliacoes.
    </Message>

    <div v-if="!isDescriptive" class="mt-6 rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <EmptyState v-if="!loading && !loadingGrades && !loadingDeps && !selectedClassGroupId && students.length === 0" message="Selecione uma turma para carregar os alunos" />
      <EmptyState v-if="!loading && !loadingGrades && !loadingDeps && selectedClassGroupId && students.length === 0" message="Nenhum aluno enturmado nesta turma" />

      <DataTable v-if="students.length > 0" :value="students" :loading="loading || loadingGrades" stripedRows responsiveLayout="scroll">
        <Column field="student_name" header="Aluno" sortable />
        <Column v-if="isNumeric" header="Nota" :style="{ width: '150px' }">
          <template #body="{ data }">
            <InputNumber v-model="data.numeric_value" :min="config?.scale_min ?? 0" :max="config?.scale_max ?? 10" :minFractionDigits="0" :maxFractionDigits="config?.rounding_precision ?? 2" :maxlength="5" inputmode="decimal" @blur="clampGrade(data)" class="w-full" />
          </template>
        </Column>
        <Column v-if="isConceptual" header="Conceito" :style="{ width: '180px' }">
          <template #body="{ data }">
            <Select v-model="data.conceptual_value" :options="conceptualOptions" optionLabel="label" optionValue="value" placeholder="--" class="w-full" showClear />
          </template>
        </Column>
      </DataTable>

      <div v-if="students.length > 0" class="mt-4 flex justify-end border-t border-[#E0E0E0] pt-4">
        <Button label="Salvar Notas" icon="pi pi-check" :loading="submitting" :disabled="!canSubmit" @click="handleSubmit" />
      </div>
    </div>
  </div>
</template>
