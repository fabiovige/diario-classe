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
import { classRecordService } from '@/services/class-record.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { useSchoolScope } from '@/composables/useSchoolScope'
import { formatDate } from '@/shared/utils/formatters'
import { generateClassRecordReportPdf } from '@/shared/utils/class-record-report-pdf'
import { exportToCsv } from '@/shared/utils/csv-export'
import type { LessonRecord } from '@/types/class-record'
import type { ClassGroup, School } from '@/types/school-structure'

const toast = useToast()
const { shouldShowSchoolFilter, userSchoolId, userSchoolName } = useSchoolScope()

const schools = ref<School[]>([])
const classGroups = ref<(ClassGroup & { label: string })[]>([])

const selectedSchoolId = ref<number | null>(null)
const selectedClassGroupId = ref<number | null>(null)
const dateFrom = ref<string | null>('')
const dateTo = ref<string | null>('')
let initializing = false

const { initFromQuery, syncToUrl, clearAll } = useListFilters([
  { key: 'school_id', ref: selectedSchoolId, type: 'number' },
  { key: 'class_group_id', ref: selectedClassGroupId, type: 'number' },
  { key: 'date_from', ref: dateFrom, type: 'string' },
  { key: 'date_to', ref: dateTo, type: 'string' },
])

const loading = ref(false)
const items = ref<LessonRecord[]>([])

const hasActiveFilters = computed(() =>
  selectedSchoolId.value !== null || selectedClassGroupId.value !== null
)

const selectedClassGroupLabel = computed(() => {
  const cg = classGroups.value.find(c => c.id === selectedClassGroupId.value)
  return cg?.label ?? ''
})

const selectedSchoolName = computed(() => {
  if (!shouldShowSchoolFilter.value) return userSchoolName.value ?? ''
  return schools.value.find(s => s.id === selectedSchoolId.value)?.name ?? ''
})

interface ReportRow {
  date: string
  component: string
  content: string
  methodology: string
  classCount: number
}

const reportRows = computed<ReportRow[]>(() =>
  items.value.map(r => ({
    date: r.date ? formatDate(r.date) : '--',
    component: r.teacher_assignment?.curricular_component?.name
      ?? r.teacher_assignment?.experience_field?.name
      ?? '--',
    content: r.content || '--',
    methodology: r.methodology || '--',
    classCount: r.class_count,
  }))
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
    })).sort((a, b) => a.label.localeCompare(b.label, 'pt-BR'))
  } catch {
    toast.error('Erro ao carregar turmas')
  }
}

watch(selectedSchoolId, () => {
  if (initializing) return
  selectedClassGroupId.value = null
  classGroups.value = []
  items.value = []
  loadClassGroups()
})

watch(selectedClassGroupId, () => {
  if (initializing) return
  items.value = []
})

async function loadData() {
  if (!selectedClassGroupId.value) {
    toast.warn('Selecione uma turma para gerar o relatorio')
    return
  }

  loading.value = true
  syncToUrl()
  try {
    const params: Record<string, unknown> = {
      class_group_id: selectedClassGroupId.value,
      per_page: 9999,
    }
    if (selectedSchoolId.value) params.school_id = selectedSchoolId.value
    if (dateFrom.value) params.date_from = dateFrom.value
    if (dateTo.value) params.date_to = dateTo.value
    const response = await classRecordService.getRecords(params)
    items.value = response.data
  } catch {
    toast.error('Erro ao carregar registros de aula')
  } finally {
    loading.value = false
  }
}

function handleExportPdf() {
  if (reportRows.value.length === 0) return
  generateClassRecordReportPdf(reportRows.value, {
    schoolName: selectedSchoolName.value,
    classGroupName: selectedClassGroupLabel.value,
    dateFrom: dateFrom.value ?? '',
    dateTo: dateTo.value ?? '',
  })
}

function handleExportCsv() {
  if (reportRows.value.length === 0) return
  const headers = ['Data', 'Disciplina', 'Conteudo', 'Metodologia', 'Aulas']
  const rows = reportRows.value.map(r => [
    r.date,
    r.component,
    r.content,
    r.methodology,
    String(r.classCount),
  ])
  exportToCsv(`diario_classe_${selectedClassGroupLabel.value.replace(/\s+/g, '_')}`, headers, rows)
}

function clearFilters() {
  clearAll()
  classGroups.value = []
  items.value = []
}

function truncate(text: string, maxLength = 80): string {
  if (!text || text === '--') return text
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
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
    await loadClassGroups()
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

    <EmptyState v-if="!loading && items.length === 0 && selectedClassGroupId" message="Nenhum registro de aula encontrado. Clique em Gerar para buscar os dados." />
    <EmptyState v-if="!loading && !selectedClassGroupId" icon="pi pi-filter" message="Selecione uma escola e turma para gerar o relatorio do diario de classe" />

    <template v-if="!loading && reportRows.length > 0">
      <div class="mb-4 flex gap-2">
        <Button label="Exportar PDF" icon="pi pi-file-pdf" severity="danger" @click="handleExportPdf" />
        <Button label="Exportar CSV" icon="pi pi-file-excel" severity="success" @click="handleExportCsv" />
      </div>

      <DataTable :value="reportRows" stripedRows responsiveLayout="scroll">
        <Column field="date" header="Data" :style="{ width: '100px' }" sortable />
        <Column field="component" header="Disciplina" :style="{ width: '160px' }" sortable />
        <Column header="Conteudo">
          <template #body="{ data }">
            {{ truncate(data.content) }}
          </template>
        </Column>
        <Column header="Metodologia" :style="{ width: '200px' }">
          <template #body="{ data }">
            {{ truncate(data.methodology, 60) }}
          </template>
        </Column>
        <Column field="classCount" header="Aulas" :style="{ width: '80px', textAlign: 'center' }" sortable />
      </DataTable>
    </template>
  </div>
</template>
