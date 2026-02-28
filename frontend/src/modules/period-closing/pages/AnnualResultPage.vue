<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useListFilters } from '@/composables/useListFilters'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Select from 'primevue/select'
import Dialog from 'primevue/dialog'
import ProgressBar from 'primevue/progressbar'
import MetricCard from '@/shared/components/MetricCard.vue'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import { periodClosingService } from '@/services/period-closing.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { useSchoolScope } from '@/composables/useSchoolScope'
import { finalResultStatusLabel } from '@/shared/utils/enum-labels'
import { extractApiError } from '@/shared/utils/api-error'
import { generateAnnualResultPdf } from '@/shared/utils/annual-result-pdf'
import type { AnnualResultResponse, AnnualStudentResult, ClassGroupClosingStatus } from '@/types/period-closing'
import type { School, AcademicYear, ClassGroup } from '@/types/school-structure'
import type { AxiosError } from 'axios'

const toast = useToast()
const { shouldShowSchoolFilter, userSchoolId, userSchoolName } = useSchoolScope()

const schools = ref<School[]>([])
const academicYears = ref<AcademicYear[]>([])
const classGroups = ref<(ClassGroup & { label: string })[]>([])
const selectedSchoolId = ref<number | null>(null)
const selectedAcademicYearId = ref<number | null>(null)
const selectedClassGroupId = ref<number | null>(null)
let initializing = false

const { initFromQuery, syncToUrl, clearAll } = useListFilters([
  { key: 'school_id', ref: selectedSchoolId, type: 'number' },
  { key: 'academic_year_id', ref: selectedAcademicYearId, type: 'number' },
  { key: 'class_group_id', ref: selectedClassGroupId, type: 'number' },
])

const data = ref<AnnualResultResponse | null>(null)
const loading = ref(false)
const calculating = ref(false)
const closingYear = ref(false)
const showCloseDialog = ref(false)

const classGroupStatusList = ref<ClassGroupClosingStatus[]>([])
const classGroupStatusLoading = ref(false)

interface CloseErrorTeacher {
  teacher_name: string
  count: number
  details: string[]
}

const closeErrors = ref<{ pending_closings_by_teacher?: CloseErrorTeacher[]; students_without_results?: { class_group: string; count: number }[] } | null>(null)
const showBlockersDialog = ref(false)

const hasActiveFilters = computed(() =>
  selectedSchoolId.value !== null || selectedAcademicYearId.value !== null || selectedClassGroupId.value !== null,
)

const isYearClosed = computed(() => data.value?.class_group?.academic_year?.status === 'closed')

const showClassGroupOverview = computed(() =>
  selectedSchoolId.value && selectedAcademicYearId.value && !selectedClassGroupId.value,
)

const allClassGroupsReady = computed(() => {
  if (classGroupStatusList.value.length === 0) return false
  return classGroupStatusList.value.every(cg => cg.ready)
})

const notReadyCount = computed(() => classGroupStatusList.value.filter(cg => !cg.ready).length)

async function loadSchools() {
  try {
    const response = await schoolStructureService.getSchools({ per_page: 200 })
    schools.value = response.data
  } catch {
    toast.error('Erro ao carregar escolas')
  }
}

async function loadAcademicYears() {
  if (!selectedSchoolId.value) {
    academicYears.value = []
    return
  }
  try {
    const response = await schoolStructureService.getAcademicYears({ school_id: selectedSchoolId.value, per_page: 200 })
    academicYears.value = response.data
  } catch {
    toast.error('Erro ao carregar anos letivos')
  }
}

async function loadClassGroups() {
  if (!selectedSchoolId.value) {
    classGroups.value = []
    return
  }
  try {
    const params: Record<string, unknown> = { school_id: selectedSchoolId.value, per_page: 200 }
    if (selectedAcademicYearId.value) params.academic_year_id = selectedAcademicYearId.value
    const response = await schoolStructureService.getClassGroups(params)
    classGroups.value = response.data.map(cg => ({
      ...cg,
      label: [cg.grade_level?.name, cg.name, cg.shift?.name_label].filter(Boolean).join(' - '),
    })).sort((a, b) => a.label.localeCompare(b.label, 'pt-BR'))
  } catch {
    toast.error('Erro ao carregar turmas')
  }
}

async function loadClassGroupStatus() {
  if (!selectedAcademicYearId.value) {
    classGroupStatusList.value = []
    return
  }
  classGroupStatusLoading.value = true
  try {
    classGroupStatusList.value = await periodClosingService.getClassGroupStatus({
      academic_year_id: selectedAcademicYearId.value,
    })
  } catch {
    toast.error('Erro ao carregar status das turmas')
  } finally {
    classGroupStatusLoading.value = false
  }
}

watch(selectedSchoolId, () => {
  if (initializing) return
  selectedAcademicYearId.value = null
  selectedClassGroupId.value = null
  academicYears.value = []
  classGroups.value = []
  classGroupStatusList.value = []
  data.value = null
  loadAcademicYears()
  syncToUrl()
})

watch(selectedAcademicYearId, () => {
  if (initializing) return
  selectedClassGroupId.value = null
  classGroups.value = []
  data.value = null
  loadClassGroups()
  loadClassGroupStatus()
  syncToUrl()
})

watch(selectedClassGroupId, () => {
  if (initializing) return
  if (selectedClassGroupId.value) {
    loadResults()
  } else {
    data.value = null
  }
  syncToUrl()
})

async function loadResults() {
  if (!selectedClassGroupId.value) return
  loading.value = true
  try {
    data.value = await periodClosingService.getClassGroupResults(selectedClassGroupId.value)
  } catch {
    data.value = null
    toast.error('Erro ao carregar resultados')
  } finally {
    loading.value = false
  }
}

async function calculateBulk() {
  if (!selectedClassGroupId.value || !data.value) return
  calculating.value = true
  try {
    await periodClosingService.calculateBulkResults({
      class_group_id: selectedClassGroupId.value,
      academic_year_id: data.value.class_group.academic_year.id,
    })
    toast.success('Resultados calculados com sucesso')
    await loadResults()
    await loadClassGroupStatus()
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao calcular resultados'))
  } finally {
    calculating.value = false
  }
}

async function closeAcademicYear() {
  if (!data.value) return
  closingYear.value = true
  closeErrors.value = null
  try {
    await schoolStructureService.closeAcademicYear(data.value.class_group.academic_year.id)
    toast.success('Ano letivo encerrado com sucesso')
    showCloseDialog.value = false
    await loadResults()
    await loadClassGroupStatus()
  } catch (error: unknown) {
    showCloseDialog.value = false
    const axiosError = error as AxiosError<{
      errors?: {
        pending_closings_by_teacher?: CloseErrorTeacher[]
        students_without_results?: { class_group: string; count: number }[]
        period_closings?: string[]
        final_results?: string[]
      }
    }>
    const errors = axiosError?.response?.data?.errors

    if (errors?.pending_closings_by_teacher || errors?.students_without_results) {
      closeErrors.value = {
        pending_closings_by_teacher: errors.pending_closings_by_teacher,
        students_without_results: errors.students_without_results,
      }
      showBlockersDialog.value = true
      return
    }

    toast.error(extractApiError(error, 'Erro ao encerrar ano letivo'))
  } finally {
    closingYear.value = false
  }
}

function selectClassGroup(classGroupId: number) {
  selectedClassGroupId.value = classGroupId
}

function exportPdf() {
  if (!data.value) return
  generateAnnualResultPdf(data.value)
}

function closingProgress(cg: ClassGroupClosingStatus): number {
  if (cg.total_closings === 0) return 0
  return Math.round((cg.closed_closings / cg.total_closings) * 100)
}

function formatGrade(value: number | null): string {
  if (value === null) return '--'
  return value.toFixed(1)
}

function gradeColor(value: number | null): string {
  if (value === null || !data.value) return ''
  return value >= data.value.summary.passing_grade ? 'text-[#0F7B0F] font-bold' : 'text-[#C42B1C] font-bold'
}

function resultLabel(result: string | null): string {
  if (!result) return 'Pendente'
  return finalResultStatusLabel(result as any)
}

function resultBadgeStatus(result: string | null): string {
  if (!result) return 'pending'
  return result
}

function overallAverage(student: AnnualStudentResult): string {
  return formatGrade(student.overall_average)
}

function overallFrequency(student: AnnualStudentResult): string {
  if (student.overall_frequency === null) return '--'
  return `${student.overall_frequency.toFixed(1)}%`
}

function clearFilters() {
  clearAll()
  data.value = null
  classGroupStatusList.value = []
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
    await loadAcademicYears()
    if (selectedAcademicYearId.value) {
      await loadClassGroups()
      await loadClassGroupStatus()
    }
    if (selectedClassGroupId.value) {
      await loadResults()
    }
  }
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Resultado Anual</h1>

    <div class="mb-6 flex flex-wrap items-end gap-4">
      <div v-if="shouldShowSchoolFilter" class="flex flex-col gap-1.5 w-64">
        <label class="text-[0.8125rem] font-medium">Escola</label>
        <Select v-model="selectedSchoolId" :options="schools" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" filter showClear />
      </div>
      <div v-if="!shouldShowSchoolFilter && userSchoolName" class="flex flex-col gap-1.5">
        <label class="text-[0.8125rem] font-medium">Escola</label>
        <span class="flex h-[2.375rem] items-center rounded-md border border-[#E0E0E0] bg-[#F5F5F5] px-3 text-sm">{{ userSchoolName }}</span>
      </div>

      <div class="flex flex-col gap-1.5 w-48">
        <label class="text-[0.8125rem] font-medium">Ano Letivo</label>
        <Select v-model="selectedAcademicYearId" :options="academicYears" optionLabel="year" optionValue="id" placeholder="Selecione" class="w-full" :disabled="!selectedSchoolId" showClear />
      </div>

      <div class="flex flex-col gap-1.5 w-72">
        <label class="text-[0.8125rem] font-medium">Turma</label>
        <Select v-model="selectedClassGroupId" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Selecione" class="w-full" :disabled="!selectedSchoolId" filter showClear />
      </div>

      <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text @click="clearFilters" />
    </div>

    <!-- PAINEL STATUS DAS TURMAS -->
    <div v-if="showClassGroupOverview" class="mb-6 rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Status das Turmas</h2>
        <div v-if="classGroupStatusList.length > 0" class="flex items-center gap-3">
          <span class="text-sm text-[#616161]">{{ classGroupStatusList.filter(c => c.ready).length }}/{{ classGroupStatusList.length }} prontas</span>
          <Button
            label="Encerrar Ano Letivo"
            icon="pi pi-lock"
            severity="danger"
            size="small"
            :disabled="!allClassGroupsReady"
            v-tooltip.top="!allClassGroupsReady ? `${notReadyCount} turma(s) nao estao prontas` : ''"
            @click="showCloseDialog = true"
          />
        </div>
      </div>

      <EmptyState v-if="!classGroupStatusLoading && classGroupStatusList.length === 0" message="Nenhuma turma encontrada" />

      <DataTable v-if="classGroupStatusList.length > 0" :value="classGroupStatusList" :loading="classGroupStatusLoading" stripedRows size="small">
        <Column header="Turma" :style="{ minWidth: '200px' }">
          <template #body="{ data: cg }">
            <button class="text-[#0078D4] hover:underline font-medium cursor-pointer" @click="selectClassGroup(cg.class_group_id)">
              {{ cg.name }}
            </button>
          </template>
        </Column>
        <Column header="Nivel" field="grade_level" />
        <Column header="Turno" field="shift" />
        <Column header="Fechamentos" :style="{ minWidth: '200px' }">
          <template #body="{ data: cg }">
            <div class="flex items-center gap-2">
              <ProgressBar :value="closingProgress(cg)" :showValue="false" class="h-4 flex-1" />
              <span class="text-xs text-[#616161] whitespace-nowrap">{{ cg.closed_closings }}/{{ cg.total_closings }}</span>
            </div>
          </template>
        </Column>
        <Column header="Resultados" :style="{ minWidth: '150px' }">
          <template #body="{ data: cg }">
            <span :class="cg.students_with_results >= cg.total_students && cg.total_students > 0 ? 'text-[#0F7B0F]' : 'text-[#9D5D00]'">
              {{ cg.students_with_results }}/{{ cg.total_students }}
            </span>
          </template>
        </Column>
        <Column header="Pronta?" :style="{ width: '80px', textAlign: 'center' }">
          <template #body="{ data: cg }">
            <i :class="cg.ready ? 'pi pi-check-circle text-[#0F7B0F] text-lg' : 'pi pi-times-circle text-[#C42B1C] text-lg'" />
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- RESULTADOS DA TURMA SELECIONADA -->
    <template v-if="data">
      <div class="mb-6 grid grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-4">
        <MetricCard title="Total" :value="data.summary.total" label="Alunos" color="#0078D4" icon="pi pi-users" />
        <MetricCard title="Aprovados" :value="data.summary.approved" label="Alunos" color="#0F7B0F" icon="pi pi-check-circle" />
        <MetricCard title="Retidos" :value="data.summary.retained" label="Alunos" color="#C42B1C" icon="pi pi-times-circle" />
        <MetricCard title="Pendentes" :value="data.summary.pending" label="Sem resultado" color="#9D5D00" icon="pi pi-clock" />
      </div>

      <div class="mb-4 flex flex-wrap gap-2">
        <Button label="Calcular Resultados" icon="pi pi-calculator" severity="info" :loading="calculating" :disabled="isYearClosed" @click="calculateBulk" />
        <Button label="Exportar PDF" icon="pi pi-file-pdf" severity="secondary" @click="exportPdf" />
        <Button
          label="Encerrar Ano Letivo"
          icon="pi pi-lock"
          severity="danger"
          :disabled="isYearClosed || !allClassGroupsReady"
          v-tooltip.top="!allClassGroupsReady && !isYearClosed ? `${notReadyCount} turma(s) nao estao prontas` : ''"
          @click="showCloseDialog = true"
        />
        <span v-if="isYearClosed" class="flex items-center gap-1 text-sm font-semibold text-[#0F7B0F]">
          <i class="pi pi-lock" /> Ano letivo encerrado
        </span>
      </div>

      <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
        <EmptyState v-if="!loading && data.students.length === 0" message="Nenhum aluno encontrado nesta turma" />

        <DataTable v-if="data.students.length > 0" :value="data.students" :loading="loading" stripedRows responsiveLayout="scroll" scrollable scrollHeight="600px">
          <Column header="Aluno" frozen :style="{ minWidth: '220px' }">
            <template #body="{ data: student }">
              <span class="font-medium">{{ student.name }}</span>
            </template>
          </Column>
          <Column v-for="period in data.assessment_periods" :key="period.id" :header="period.name" :style="{ minWidth: '90px', textAlign: 'center' }">
            <template #body="{ data: student }">
              <span v-if="student.subjects.length > 0" :class="gradeColor(student.subjects[0]?.periods?.[String(period.number)] ?? null)">
                {{ formatGrade(student.subjects[0]?.periods?.[String(period.number)] ?? null) }}
              </span>
              <span v-else>--</span>
            </template>
          </Column>
          <Column header="Media Final" :style="{ minWidth: '110px', textAlign: 'center' }">
            <template #body="{ data: student }">
              <span :class="gradeColor(student.overall_average)">{{ overallAverage(student) }}</span>
            </template>
          </Column>
          <Column header="Freq %" :style="{ minWidth: '90px', textAlign: 'center' }">
            <template #body="{ data: student }">
              <span :class="student.overall_frequency !== null && student.overall_frequency < 75 ? 'text-[#C42B1C] font-bold' : ''">
                {{ overallFrequency(student) }}
              </span>
            </template>
          </Column>
          <Column header="Situacao" :style="{ minWidth: '140px' }">
            <template #body="{ data: student }">
              <StatusBadge :status="resultBadgeStatus(student.result)" :label="resultLabel(student.result)" />
            </template>
          </Column>
        </DataTable>
      </div>
    </template>

    <div v-if="!data && !showClassGroupOverview" class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <EmptyState message="Selecione escola, ano letivo e turma para visualizar os resultados" />
    </div>

    <!-- DIALOG CONFIRMAR ENCERRAMENTO -->
    <Dialog v-model:visible="showCloseDialog" header="Encerrar Ano Letivo" :modal="true" :style="{ width: '480px' }">
      <div class="flex flex-col gap-4">
        <p>Tem certeza que deseja encerrar o ano letivo <strong>{{ data?.class_group?.academic_year?.year ?? academicYears.find(a => a.id === selectedAcademicYearId)?.year }}</strong>?</p>
        <p class="text-sm text-[#616161]">Esta acao e irreversivel. Apos o encerramento, nenhuma alteracao podera ser feita nos resultados finais deste ano.</p>
      </div>
      <template #footer>
        <Button label="Cancelar" text @click="showCloseDialog = false" />
        <Button label="Encerrar" severity="danger" icon="pi pi-lock" :loading="closingYear" @click="closeAcademicYear" />
      </template>
    </Dialog>

    <!-- DIALOG BLOQUEIOS DETALHADOS -->
    <Dialog v-model:visible="showBlockersDialog" header="Pendencias para Encerramento" :modal="true" :style="{ width: '700px' }">
      <div class="flex flex-col gap-6">
        <div v-if="closeErrors?.pending_closings_by_teacher && closeErrors.pending_closings_by_teacher.length > 0">
          <h3 class="text-base font-semibold mb-3 text-[#C42B1C]">
            <i class="pi pi-exclamation-triangle mr-1" /> Fechamentos Pendentes por Professor
          </h3>
          <div v-for="teacher in closeErrors.pending_closings_by_teacher" :key="teacher.teacher_name" class="mb-3 rounded border border-[#E0E0E0] p-3">
            <div class="font-medium mb-1">{{ teacher.teacher_name }} <span class="text-sm text-[#616161]">({{ teacher.count }} pendente(s))</span></div>
            <ul class="list-disc list-inside text-sm text-[#616161]">
              <li v-for="(detail, i) in teacher.details" :key="i">{{ detail }}</li>
            </ul>
          </div>
        </div>

        <div v-if="closeErrors?.students_without_results && closeErrors.students_without_results.length > 0">
          <h3 class="text-base font-semibold mb-3 text-[#C42B1C]">
            <i class="pi pi-exclamation-triangle mr-1" /> Alunos sem Resultado Final
          </h3>
          <div v-for="group in closeErrors.students_without_results" :key="group.class_group" class="flex items-center gap-2 mb-1 text-sm">
            <span class="font-medium">{{ group.class_group }}:</span>
            <span class="text-[#616161]">{{ group.count }} aluno(s)</span>
          </div>
        </div>
      </div>
      <template #footer>
        <Button label="Fechar" @click="showBlockersDialog = false" />
      </template>
    </Dialog>
  </div>
</template>
