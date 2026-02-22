<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Select from 'primevue/select'
import Toolbar from 'primevue/toolbar'
import EmptyState from '@/shared/components/EmptyState.vue'
import RadarChart from '@/shared/components/RadarChart.vue'
import { assessmentService } from '@/services/assessment.service'
import { useToast } from '@/composables/useToast'
import { generateReportCardPdf } from '@/shared/utils/report-card-pdf'
import type {
  ReportCardResponse,
  ReportCardSubject,
  ReportCardPeriod,
} from '@/types/assessment'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const studentId = Number(route.params.studentId)
const report = ref<ReportCardResponse | null>(null)
const loading = ref(false)

const selectedSubjectId = ref<number | null>(null)
const selectedPeriodNumber = ref<number | null>(null)

const hasActiveFilters = computed(() =>
  selectedSubjectId.value !== null || selectedPeriodNumber.value !== null
)

const subjectOptions = computed(() =>
  (report.value?.subjects ?? []).map(s => ({ id: s.teacher_assignment_id, name: s.name }))
)

const periodOptions = computed(() =>
  (report.value?.assessment_periods ?? []).map(p => ({ number: p.number, name: p.name }))
)

const filteredSubjects = computed(() => {
  if (!report.value) return []
  let subjects = report.value.subjects
  if (selectedSubjectId.value !== null) {
    subjects = subjects.filter(s => s.teacher_assignment_id === selectedSubjectId.value)
  }
  return subjects
})

const visiblePeriods = computed((): ReportCardPeriod[] => {
  if (!report.value) return []
  if (selectedPeriodNumber.value !== null) {
    return report.value.assessment_periods.filter(p => p.number === selectedPeriodNumber.value)
  }
  return report.value.assessment_periods
})

const isNumeric = computed(() => report.value?.summary?.grade_type === 'numeric')

const radarLabels = computed(() =>
  (report.value?.subjects ?? []).map(s => s.name)
)

const radarColors = [
  { border: '#0078D4', bg: 'rgba(0,120,212,0.15)' },
  { border: '#0F7B0F', bg: 'rgba(15,123,15,0.15)' },
  { border: '#C42B1C', bg: 'rgba(196,43,28,0.15)' },
  { border: '#8764B8', bg: 'rgba(135,100,184,0.15)' },
]

const radarDatasets = computed(() => {
  if (!report.value) return []
  const periods = selectedPeriodNumber.value !== null
    ? report.value.assessment_periods.filter(p => p.number === selectedPeriodNumber.value)
    : report.value.assessment_periods

  return periods
    .map((period, idx) => {
      const data = report.value!.subjects.map(s => s.periods[String(period.number)]?.average ?? null)
      if (data.every(v => v === null)) return null
      const color = radarColors[idx % radarColors.length]
      return {
        label: period.name,
        data,
        borderColor: color.border,
        backgroundColor: color.bg,
      }
    })
    .filter(Boolean) as { label: string; data: (number | null)[]; borderColor: string; backgroundColor: string }[]
})

const frequencyCards = computed(() => {
  const subjects = filteredSubjects.value
  const totalAbsences = subjects.reduce((sum, s) => {
    if (selectedPeriodNumber.value !== null) {
      return sum + (s.periods[String(selectedPeriodNumber.value)]?.absences ?? 0)
    }
    return sum + s.total_absences
  }, 0)

  const avgFrequency = subjects.length > 0
    ? subjects.reduce((sum, s) => sum + (s.frequency_percentage ?? 0), 0) / subjects.length
    : null

  return { totalAbsences, avgFrequency }
})

function getInitials(name: string): string {
  return name.split(' ').filter(Boolean).slice(0, 2).map(w => w[0]).join('').toUpperCase()
}

function gradeColor(value: number | null): string {
  if (value === null) return ''
  if (!report.value?.summary) return ''
  return value >= report.value.summary.passing_grade ? 'text-[#0F7B0F] font-semibold' : 'text-[#C42B1C] font-semibold'
}

function formatGrade(value: number | null): string {
  if (value === null) return '--'
  return value.toFixed(1)
}

function statusLabel(status: string): string {
  const map: Record<string, string> = {
    pending: 'Cursando',
    calculated: 'Calculado',
    approved: 'Aprovado',
    reproved: 'Reprovado',
  }
  return map[status] ?? status
}

function statusClass(status: string): string {
  const map: Record<string, string> = {
    pending: 'text-[#616161]',
    calculated: 'text-[#0078D4]',
    approved: 'text-[#0F7B0F] font-semibold',
    reproved: 'text-[#C42B1C] font-semibold',
  }
  return map[status] ?? ''
}

function clearFilters() {
  selectedSubjectId.value = null
  selectedPeriodNumber.value = null
}

function truncate(text: string, maxLength = 80): string {
  if (!text) return '--'
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}

function exportPdf() {
  if (!report.value) return

  try {
    generateReportCardPdf(report.value)
    toast.success('PDF exportado')
  } catch {
    toast.error('Erro ao exportar PDF')
  }
}

async function loadData() {
  loading.value = true
  try {
    report.value = await assessmentService.getReportCard(studentId)
  } catch {
    toast.error('Erro ao carregar boletim')
  } finally {
    loading.value = false
  }
}

onMounted(loadData)
</script>

<template>
  <div class="p-6">
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-[#0078D4]">Boletim Escolar</h1>
      <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" @click="router.back()" />
    </div>

    <EmptyState v-if="!loading && !report" message="Boletim nao disponivel" />

    <div v-if="report" id="report-card-content">
      <div class="mb-6 flex items-center gap-4 rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[#0078D4] text-xl font-bold text-white">
          {{ getInitials(report.student.display_name ?? report.student.name) }}
        </div>
        <div class="flex flex-col gap-0.5">
          <h2 class="text-lg font-semibold">{{ report.student.display_name ?? report.student.name }}</h2>
          <div class="flex flex-wrap gap-x-4 text-sm text-[#616161]">
            <span v-if="report.student.class_group">{{ report.student.class_group.label }}</span>
            <span v-if="report.student.enrollment_number">Mat: {{ report.student.enrollment_number }}</span>
          </div>
        </div>
      </div>

      <div class="mb-6 rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
        <Toolbar class="mb-0 border-none bg-transparent p-0">
          <template #start>
            <div class="flex flex-wrap items-end gap-4">
              <div class="flex flex-col gap-1">
                <label class="text-[0.8125rem] font-medium">Materia</label>
                <Select v-model="selectedSubjectId" :options="subjectOptions" optionLabel="name" optionValue="id" placeholder="Todas as materias" class="w-56" filter showClear />
              </div>
              <div class="flex flex-col gap-1">
                <label class="text-[0.8125rem] font-medium">Periodo</label>
                <Select v-model="selectedPeriodNumber" :options="periodOptions" optionLabel="name" optionValue="number" placeholder="Todos os periodos" class="w-56" showClear />
              </div>
              <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text @click="clearFilters" />
            </div>
          </template>
          <template #end>
            <Button label="Exportar PDF" icon="pi pi-file-pdf" severity="secondary" :loading="loading" @click="exportPdf" />
          </template>
        </Toolbar>
      </div>

      <div class="mb-6 grid gap-6" :class="isNumeric && radarDatasets.length > 0 ? 'grid-cols-1 lg:grid-cols-[1fr_320px]' : ''">
        <div v-if="isNumeric && radarDatasets.length > 0" class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
          <h3 class="mb-4 text-base font-semibold">Desempenho por Materia</h3>
          <RadarChart
            :labels="radarLabels"
            :datasets="radarDatasets"
            :scale-max="report.summary?.scale_max ?? 10"
            :passing-grade="report.summary?.passing_grade ?? 6"
          />
        </div>

        <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
          <h3 class="mb-4 text-base font-semibold">Frequencia</h3>
          <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col items-center gap-1 rounded-lg border border-[#E0E0E0] p-4 text-center">
              <span class="text-2xl font-bold text-[#C42B1C]">{{ frequencyCards.totalAbsences }}</span>
              <span class="text-xs uppercase text-[#616161]">Faltas</span>
            </div>
            <div class="flex flex-col items-center gap-1 rounded-lg border border-[#E0E0E0] p-4 text-center">
              <span class="text-2xl font-bold text-[#0078D4]">{{ frequencyCards.avgFrequency !== null ? frequencyCards.avgFrequency.toFixed(1) + '%' : '--' }}</span>
              <span class="text-xs uppercase text-[#616161]">Frequencia</span>
            </div>
          </div>
        </div>
      </div>

      <div class="mb-6 rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-base font-semibold">Notas</h3>
        <EmptyState v-if="filteredSubjects.length === 0" message="Nenhuma materia encontrada" />
        <DataTable v-if="filteredSubjects.length > 0" :value="filteredSubjects" stripedRows responsiveLayout="scroll">
          <Column header="Materia" :style="{ minWidth: '160px' }">
            <template #body="{ data }: { data: ReportCardSubject }">
              <span class="font-medium">{{ data.name }}</span>
            </template>
          </Column>
          <Column header="Professor" :style="{ minWidth: '140px' }">
            <template #body="{ data }: { data: ReportCardSubject }">
              {{ data.teacher_name || '--' }}
            </template>
          </Column>
          <Column v-for="period in visiblePeriods" :key="period.number" :header="period.name" :style="{ width: '100px', textAlign: 'center' }">
            <template #body="{ data }: { data: ReportCardSubject }">
              <span :class="gradeColor(data.periods[String(period.number)]?.average ?? null)">
                {{ formatGrade(data.periods[String(period.number)]?.average ?? null) }}
              </span>
            </template>
          </Column>
          <Column v-if="!selectedPeriodNumber" header="Media Final" :style="{ width: '110px', textAlign: 'center' }">
            <template #body="{ data }: { data: ReportCardSubject }">
              <span :class="gradeColor(data.final_grade ?? data.final_average)">
                {{ formatGrade(data.final_grade ?? data.final_average) }}
              </span>
            </template>
          </Column>
          <Column v-if="!selectedPeriodNumber" header="Situacao" :style="{ width: '110px' }">
            <template #body="{ data }: { data: ReportCardSubject }">
              <span :class="statusClass(data.status)">{{ statusLabel(data.status) }}</span>
            </template>
          </Column>
        </DataTable>
      </div>

      <div v-if="report.descriptive_reports.length > 0" class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-base font-semibold">Relatorios Descritivos</h3>
        <DataTable :value="report.descriptive_reports" stripedRows responsiveLayout="scroll">
          <Column header="Campo de Experiencia" field="experience_field" :style="{ minWidth: '180px' }" />
          <Column header="Periodo" field="period" :style="{ width: '140px' }" />
          <Column header="Conteudo">
            <template #body="{ data }">
              <span :title="data.content">{{ truncate(data.content) }}</span>
            </template>
          </Column>
        </DataTable>
      </div>
    </div>
  </div>
</template>
