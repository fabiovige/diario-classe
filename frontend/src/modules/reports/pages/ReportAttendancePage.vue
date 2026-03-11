<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useListFilters } from '@/composables/useListFilters'
import Button from 'primevue/button'
import Select from 'primevue/select'
import InputText from 'primevue/inputtext'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import ProgressSpinner from 'primevue/progressspinner'
import EmptyState from '@/shared/components/EmptyState.vue'
import { attendanceService } from '@/services/attendance.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { useSchoolScope } from '@/composables/useSchoolScope'
import { generateAttendanceReportPdf } from '@/shared/utils/attendance-report-pdf'
import { exportToCsv } from '@/shared/utils/csv-export'
import type { AttendanceRecord } from '@/types/attendance'
import type { ClassGroup, AcademicYear, School } from '@/types/school-structure'

const toast = useToast()
const { shouldShowSchoolFilter, userSchoolId, userSchoolName } = useSchoolScope()

const schools = ref<School[]>([])
const academicYears = ref<AcademicYear[]>([])
const classGroups = ref<(ClassGroup & { label: string })[]>([])

const selectedSchoolId = ref<number | null>(null)
const selectedAcademicYearId = ref<number | null>(null)
const selectedClassGroupId = ref<number | null>(null)
const dateFrom = ref<string | null>('')
const dateTo = ref<string | null>('')
let initializing = false

const { initFromQuery, syncToUrl, clearAll } = useListFilters([
  { key: 'school_id', ref: selectedSchoolId, type: 'number' },
  { key: 'academic_year_id', ref: selectedAcademicYearId, type: 'number' },
  { key: 'class_group_id', ref: selectedClassGroupId, type: 'number' },
  { key: 'date_from', ref: dateFrom, type: 'string' },
  { key: 'date_to', ref: dateTo, type: 'string' },
])

const loading = ref(false)
const records = ref<AttendanceRecord[]>([])

const hasActiveFilters = computed(() =>
  selectedSchoolId.value !== null || selectedClassGroupId.value !== null
)

interface StudentSummary {
  studentName: string
  totalClasses: number
  present: number
  absent: number
  justified: number
  excused: number
  frequencyPercentage: number
}

const summaryData = computed<StudentSummary[]>(() => {
  const map = new Map<number, StudentSummary>()

  for (const r of records.value) {
    if (!map.has(r.student_id)) {
      map.set(r.student_id, {
        studentName: r.student?.name ?? `Aluno #${r.student_id}`,
        totalClasses: 0,
        present: 0,
        absent: 0,
        justified: 0,
        excused: 0,
        frequencyPercentage: 0,
      })
    }
    const row = map.get(r.student_id)!
    row.totalClasses++
    if (r.status === 'present') row.present++
    if (r.status === 'absent') row.absent++
    if (r.status === 'justified_absence') row.justified++
    if (r.status === 'excused') row.excused++
  }

  for (const row of map.values()) {
    row.frequencyPercentage = row.totalClasses > 0
      ? ((row.present + row.justified + row.excused) / row.totalClasses) * 100
      : 0
  }

  return Array.from(map.values()).sort((a, b) => a.studentName.localeCompare(b.studentName, 'pt-BR'))
})

const selectedClassGroupLabel = computed(() => {
  const cg = classGroups.value.find(c => c.id === selectedClassGroupId.value)
  return cg?.label ?? ''
})

const selectedSchoolName = computed(() => {
  if (!shouldShowSchoolFilter.value) return userSchoolName.value ?? ''
  return schools.value.find(s => s.id === selectedSchoolId.value)?.name ?? ''
})

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

watch(selectedSchoolId, () => {
  if (initializing) return
  selectedAcademicYearId.value = null
  selectedClassGroupId.value = null
  academicYears.value = []
  classGroups.value = []
  records.value = []
  loadAcademicYears()
  loadClassGroups()
})

watch(selectedAcademicYearId, () => {
  if (initializing) return
  selectedClassGroupId.value = null
  classGroups.value = []
  records.value = []
  loadClassGroups()
})

watch(selectedClassGroupId, () => {
  if (initializing) return
  records.value = []
})

async function loadData() {
  if (!selectedClassGroupId.value) {
    toast.warn('Selecione uma turma para gerar o relatorio')
    return
  }

  loading.value = true
  syncToUrl()
  try {
    const params: Record<string, unknown> = { per_page: 9999 }
    if (dateFrom.value) params.date_from = dateFrom.value
    if (dateTo.value) params.date_to = dateTo.value
    const response = await attendanceService.getByClass(selectedClassGroupId.value, params)
    records.value = response.data
  } catch {
    toast.error('Erro ao carregar dados de frequencia')
  } finally {
    loading.value = false
  }
}

function handleExportPdf() {
  if (summaryData.value.length === 0) return
  generateAttendanceReportPdf(summaryData.value, {
    schoolName: selectedSchoolName.value,
    classGroupName: selectedClassGroupLabel.value,
    dateFrom: dateFrom.value ?? '',
    dateTo: dateTo.value ?? '',
  })
}

function handleExportCsv() {
  if (summaryData.value.length === 0) return
  const headers = ['Aluno', 'Total Aulas', 'Presencas', 'Faltas', 'Justificadas', 'Dispensas', 'Frequencia %']
  const rows = summaryData.value.map(r => [
    r.studentName,
    String(r.totalClasses),
    String(r.present),
    String(r.absent),
    String(r.justified),
    String(r.excused),
    r.frequencyPercentage.toFixed(1),
  ])
  exportToCsv(`frequencia_${selectedClassGroupLabel.value.replace(/\s+/g, '_')}`, headers, rows)
}

function clearFilters() {
  clearAll()
  academicYears.value = []
  classGroups.value = []
  records.value = []
}

onMounted(async () => {
  if (shouldShowSchoolFilter.value) {
    loadSchools()
  }
  initializing = true
  initFromQuery()
  initializing = false
  if (!shouldShowSchoolFilter.value && userSchoolId.value && !selectedSchoolId.value) {
    selectedSchoolId.value = userSchoolId.value
    return
  }
  if (selectedSchoolId.value) {
    await Promise.all([loadAcademicYears(), loadClassGroups()])
  }
})
</script>

<template>
  <div class="card">
    <div class="mb-4 flex flex-wrap items-end gap-4">
      <div v-if="shouldShowSchoolFilter" class="flex flex-col gap-1.5 w-full md:w-64">
        <label class="text-sm font-medium">Escola</label>
        <Select v-model="selectedSchoolId" :options="schools" optionLabel="name" optionValue="id" placeholder="Selecione a escola" class="w-full" filter showClear />
      </div>
      <div v-if="!shouldShowSchoolFilter && userSchoolName" class="flex flex-col gap-1.5">
        <label class="text-sm font-medium">Escola</label>
        <span class="flex h-[2.375rem] items-center rounded-md border border-md-border bg-md-hover px-3 text-sm">{{ userSchoolName }}</span>
      </div>
      <div class="flex flex-col gap-1.5 w-full md:w-48">
        <label class="text-sm font-medium">Ano Letivo</label>
        <Select v-model="selectedAcademicYearId" :options="academicYears" optionLabel="year" optionValue="id" placeholder="Todos" class="w-full" showClear :disabled="!selectedSchoolId" />
      </div>
      <div class="flex flex-col gap-1.5 w-full md:w-56">
        <label class="text-sm font-medium">Turma</label>
        <Select v-model="selectedClassGroupId" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Selecione a turma" class="w-full" filter showClear :disabled="!selectedSchoolId" />
      </div>
      <div class="flex flex-col gap-1.5 w-full md:w-40">
        <label class="text-sm font-medium">Data De</label>
        <InputText v-model="dateFrom" type="date" class="w-full" />
      </div>
      <div class="flex flex-col gap-1.5 w-full md:w-40">
        <label class="text-sm font-medium">Data Ate</label>
        <InputText v-model="dateTo" type="date" class="w-full" />
      </div>
      <Button label="Gerar" icon="pi pi-search" @click="loadData" :disabled="!selectedClassGroupId" />
      <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text @click="clearFilters" />
    </div>

    <div v-if="loading" class="flex items-center justify-center py-16">
      <ProgressSpinner strokeWidth="3" />
    </div>

    <EmptyState v-if="!loading && records.length === 0 && selectedClassGroupId" message="Nenhum registro de frequencia encontrado. Clique em Gerar para buscar os dados." />
    <EmptyState v-if="!loading && !selectedClassGroupId" icon="pi pi-filter" message="Selecione uma escola e turma para gerar o relatorio de frequencia" />

    <template v-if="!loading && summaryData.length > 0">
      <div class="mb-4 flex gap-2">
        <Button label="Exportar PDF" icon="pi pi-file-pdf" severity="danger" @click="handleExportPdf" />
        <Button label="Exportar CSV" icon="pi pi-file-excel" severity="success" @click="handleExportCsv" />
      </div>

      <DataTable :value="summaryData" stripedRows responsiveLayout="scroll">
        <Column field="studentName" header="Aluno" sortable />
        <Column field="totalClasses" header="Total Aulas" :style="{ width: '100px', textAlign: 'center' }" sortable />
        <Column field="present" header="Presencas" :style="{ width: '100px', textAlign: 'center' }" sortable />
        <Column field="absent" header="Faltas" :style="{ width: '80px', textAlign: 'center' }" sortable />
        <Column field="justified" header="Justificadas" :style="{ width: '110px', textAlign: 'center' }" sortable />
        <Column field="excused" header="Dispensas" :style="{ width: '90px', textAlign: 'center' }" sortable />
        <Column header="Frequencia %" :style="{ width: '110px', textAlign: 'center' }" sortable sortField="frequencyPercentage">
          <template #body="{ data }">
            <span :class="data.frequencyPercentage >= 75 ? 'text-green-600 font-bold' : 'text-red-600 font-bold'">
              {{ data.frequencyPercentage.toFixed(1) }}%
            </span>
          </template>
        </Column>
      </DataTable>
    </template>
  </div>
</template>
