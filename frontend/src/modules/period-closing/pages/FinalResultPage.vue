<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useListFilters } from '@/composables/useListFilters'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Select from 'primevue/select'
import Button from 'primevue/button'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import { periodClosingService } from '@/services/period-closing.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { assessmentService } from '@/services/assessment.service'
import { peopleService } from '@/services/people.service'
import { useToast } from '@/composables/useToast'
import { useSchoolScope } from '@/composables/useSchoolScope'
import { extractApiError } from '@/shared/utils/api-error'
import { finalResultStatusLabel } from '@/shared/utils/enum-labels'
import { formatPercentage } from '@/shared/utils/formatters'
import type { Student } from '@/types/people'
import type { FinalResult } from '@/types/period-closing'
import type { School, ClassGroup } from '@/types/school-structure'
import type { ReportCardResponse } from '@/types/assessment'

const toast = useToast()
const { shouldShowSchoolFilter, userSchoolId, userSchoolName } = useSchoolScope()

const schools = ref<School[]>([])
const classGroups = ref<(ClassGroup & { label: string })[]>([])
const students = ref<Student[]>([])
const selectedSchoolId = ref<number | null>(null)
const selectedClassGroupId = ref<number | null>(null)
const selectedStudentId = ref<number | null>(null)
let initializing = false

const { initFromQuery, syncToUrl, clearAll } = useListFilters([
  { key: 'school_id', ref: selectedSchoolId, type: 'number' },
  { key: 'class_group_id', ref: selectedClassGroupId, type: 'number' },
  { key: 'student_id', ref: selectedStudentId, type: 'number' },
])

const result = ref<FinalResult | null>(null)
const reportCard = ref<ReportCardResponse | null>(null)
const loading = ref(false)

const hasActiveFilters = computed(() =>
  selectedSchoolId.value !== null || selectedClassGroupId.value !== null || selectedStudentId.value !== null,
)

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
    }))
  } catch {
    toast.error('Erro ao carregar turmas')
  }
}

async function loadStudents() {
  const params: Record<string, unknown> = { per_page: 200 }
  if (selectedSchoolId.value) params.school_id = selectedSchoolId.value
  if (selectedClassGroupId.value) params.class_group_id = selectedClassGroupId.value
  try {
    const response = await peopleService.getStudents(params)
    students.value = response.data
  } catch {
    toast.error('Erro ao carregar alunos')
  }
}

watch(selectedSchoolId, () => {
  if (initializing) return
  selectedClassGroupId.value = null
  selectedStudentId.value = null
  classGroups.value = []
  students.value = []
  result.value = null
  reportCard.value = null
  loadClassGroups()
  loadStudents()
  syncToUrl()
})

watch(selectedClassGroupId, () => {
  if (initializing) return
  selectedStudentId.value = null
  result.value = null
  reportCard.value = null
  loadStudents()
  syncToUrl()
})

watch(selectedStudentId, () => {
  if (initializing) return
  result.value = null
  reportCard.value = null
  if (selectedStudentId.value) {
    loadResult()
    loadReportCard()
  }
  syncToUrl()
})

async function loadResult() {
  if (!selectedStudentId.value) return
  loading.value = true
  try {
    const results = await periodClosingService.getStudentFinalResult(selectedStudentId.value)
    result.value = results[0] ?? null
    if (!result.value) {
      toast.warn('Resultado final nao encontrado para este aluno')
    }
  } catch {
    result.value = null
    toast.error('Erro ao buscar resultado final')
  } finally {
    loading.value = false
  }
}

async function loadReportCard() {
  if (!selectedStudentId.value) return
  try {
    reportCard.value = await assessmentService.getReportCard(selectedStudentId.value)
  } catch {
    reportCard.value = null
  }
}

async function calculateResult() {
  if (!selectedStudentId.value) return
  loading.value = true
  try {
    result.value = await periodClosingService.calculateFinalResult({
      student_id: selectedStudentId.value,
    })
    toast.success('Resultado calculado')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao calcular resultado'))
  } finally {
    loading.value = false
  }
}

function formatGrade(value: number | null): string {
  if (value === null) return '--'
  return value.toFixed(1)
}

function clearFilters() {
  clearAll()
  result.value = null
  reportCard.value = null
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
    await loadStudents()
    if (selectedStudentId.value) {
      await loadResult()
      await loadReportCard()
    }
  } else {
    await loadStudents()
  }
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Resultado Final</h1>

    <div class="mb-6 flex flex-wrap items-end gap-4">
      <div v-if="shouldShowSchoolFilter" class="flex flex-col gap-1.5 w-64">
        <label class="text-[0.8125rem] font-medium">Escola</label>
        <Select v-model="selectedSchoolId" :options="schools" optionLabel="name" optionValue="id" placeholder="Todas" class="w-full" filter showClear />
      </div>
      <div v-if="!shouldShowSchoolFilter && userSchoolName" class="flex flex-col gap-1.5">
        <label class="text-[0.8125rem] font-medium">Escola</label>
        <span class="flex h-[2.375rem] items-center rounded-md border border-[#E0E0E0] bg-[#F5F5F5] px-3 text-sm">{{ userSchoolName }}</span>
      </div>

      <div class="flex flex-col gap-1.5 w-72">
        <label class="text-[0.8125rem] font-medium">Turma</label>
        <Select v-model="selectedClassGroupId" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Todas" class="w-full" :disabled="!selectedSchoolId" filter showClear />
      </div>

      <div class="flex flex-col gap-1.5 w-72">
        <label class="text-[0.8125rem] font-medium">Aluno *</label>
        <Select v-model="selectedStudentId" :options="students" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" filter showClear />
      </div>

      <div class="flex items-end gap-2">
        <Button label="Calcular" icon="pi pi-calculator" severity="info" @click="calculateResult" :disabled="!selectedStudentId" :loading="loading" />
      </div>

      <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text @click="clearFilters" />
    </div>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <EmptyState v-if="!loading && !result" message="Selecione um aluno para visualizar o resultado final" />

      <div v-if="result" class="grid grid-cols-[repeat(auto-fill,minmax(250px,1fr))] gap-5">
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Aluno</span>
          <span class="text-[0.9375rem]">{{ result.student?.name ?? `Aluno #${result.student_id}` }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Resultado</span>
          <StatusBadge :status="result.result" :label="finalResultStatusLabel(result.result)" />
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Media Geral</span>
          <span class="text-2xl font-bold">{{ result.overall_average ?? '--' }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Frequencia Geral</span>
          <span class="text-[0.9375rem]">{{ formatPercentage(result.overall_frequency) }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Conselho</span>
          <span class="text-[0.9375rem]">{{ result.council_override ? 'Sim' : 'Nao' }}</span>
        </div>
        <div v-if="result.observations" class="col-span-full flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Observacoes</span>
          <span class="text-[0.9375rem]">{{ result.observations }}</span>
        </div>
      </div>
    </div>

    <div v-if="reportCard && reportCard.subjects.length > 0" class="mt-6 rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <h2 class="mb-4 text-lg font-semibold text-[#0078D4]">Detalhamento por Disciplina</h2>

      <DataTable :value="reportCard.subjects" stripedRows responsiveLayout="scroll">
        <Column header="Disciplina" field="name" :style="{ minWidth: '180px' }" />
        <Column v-for="period in reportCard.assessment_periods" :key="period.id" :header="period.name" :style="{ minWidth: '80px', textAlign: 'center' }">
          <template #body="{ data: subject }">
            {{ formatGrade(subject.periods?.[String(period.number)]?.average ?? null) }}
          </template>
        </Column>
        <Column header="Media Final" :style="{ minWidth: '100px', textAlign: 'center' }">
          <template #body="{ data: subject }">
            <span class="font-bold" :class="subject.final_grade !== null && reportCard!.summary && subject.final_grade >= reportCard!.summary.passing_grade ? 'text-[#0F7B0F]' : subject.final_grade !== null ? 'text-[#C42B1C]' : ''">
              {{ formatGrade(subject.final_grade ?? subject.final_average) }}
            </span>
          </template>
        </Column>
        <Column header="Freq %" :style="{ minWidth: '80px', textAlign: 'center' }">
          <template #body="{ data: subject }">
            {{ subject.frequency_percentage !== null ? `${subject.frequency_percentage.toFixed(1)}%` : '--' }}
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
</template>
