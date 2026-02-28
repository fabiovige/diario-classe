<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useListFilters } from '@/composables/useListFilters'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Paginator from 'primevue/paginator'
import Select from 'primevue/select'
import EmptyState from '@/shared/components/EmptyState.vue'
import { assessmentService } from '@/services/assessment.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { academicCalendarService } from '@/services/academic-calendar.service'
import { useToast } from '@/composables/useToast'
import { useSchoolScope } from '@/composables/useSchoolScope'
import type { DescriptiveReport } from '@/types/assessment'
import type { ClassGroup, School } from '@/types/school-structure'
import type { AssessmentPeriod } from '@/types/academic-calendar'

const router = useRouter()
const toast = useToast()
const { shouldShowSchoolFilter, userSchoolId, userSchoolName } = useSchoolScope()

const items = ref<DescriptiveReport[]>([])
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(15)

const schools = ref<School[]>([])
const classGroups = ref<(ClassGroup & { label: string })[]>([])
const periods = ref<AssessmentPeriod[]>([])
const selectedSchoolId = ref<number | null>(null)
const selectedClassGroupId = ref<number | null>(null)
const selectedPeriodId = ref<number | null>(null)
let initializing = false

const { initFromQuery, syncToUrl, clearAll } = useListFilters([
  { key: 'school_id', ref: selectedSchoolId, type: 'number' },
  { key: 'class_group_id', ref: selectedClassGroupId, type: 'number' },
  { key: 'assessment_period_id', ref: selectedPeriodId, type: 'number' },
  { key: 'page', ref: currentPage, type: 'number' },
])

const hasActiveFilters = computed(() =>
  selectedSchoolId.value !== null || selectedClassGroupId.value !== null || selectedPeriodId.value !== null
)

const selectedClassGroup = computed(() =>
  classGroups.value.find(cg => cg.id === selectedClassGroupId.value)
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

async function loadPeriods() {
  if (!selectedClassGroupId.value) {
    periods.value = []
    return
  }
  try {
    const academicYearId = selectedClassGroup.value?.academic_year_id
    const response = await academicCalendarService.getPeriods({ academic_year_id: academicYearId, per_page: 100 })
    periods.value = response.data
  } catch {
    toast.error('Erro ao carregar periodos')
  }
}

watch(selectedSchoolId, () => {
  if (initializing) return
  selectedClassGroupId.value = null
  selectedPeriodId.value = null
  classGroups.value = []
  periods.value = []
  loadClassGroups()
  onFilter()
})

watch(selectedClassGroupId, () => {
  if (initializing) return
  selectedPeriodId.value = null
  periods.value = []
  loadPeriods()
  onFilter()
})

watch(selectedPeriodId, () => {
  if (initializing) return
  onFilter()
})

async function loadData() {
  loading.value = true
  syncToUrl()
  try {
    const params: Record<string, unknown> = { page: currentPage.value, per_page: perPage.value }
    if (selectedSchoolId.value) params.school_id = selectedSchoolId.value
    if (selectedClassGroupId.value) params.class_group_id = selectedClassGroupId.value
    if (selectedPeriodId.value) params.assessment_period_id = selectedPeriodId.value
    const response = await assessmentService.getDescriptiveReports(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar relatorios descritivos')
  } finally {
    loading.value = false
  }
}

function onPageChange(event: { page: number; rows: number }) {
  currentPage.value = event.page + 1
  perPage.value = event.rows
  loadData()
}

function onFilter() {
  currentPage.value = 1
  loadData()
}

function clearFilters() {
  clearAll()
  classGroups.value = []
  periods.value = []
  currentPage.value = 1
  loadData()
}

function truncate(text: string, maxLength = 60): string {
  if (!text) return '--'
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
    if (selectedClassGroupId.value) {
      await loadPeriods()
    }
  }
  loadData()
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Relatorios Descritivos</h1>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <div class="mb-4 flex flex-wrap items-end gap-4">
        <div v-if="shouldShowSchoolFilter" class="flex flex-col gap-1.5 w-64">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <Select v-model="selectedSchoolId" :options="schools" optionLabel="name" optionValue="id" placeholder="Todas as escolas" class="w-full" filter showClear />
        </div>
        <div v-if="!shouldShowSchoolFilter && userSchoolName" class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <span class="flex h-[2.375rem] items-center rounded-md border border-[#E0E0E0] bg-[#F5F5F5] px-3 text-sm">{{ userSchoolName }}</span>
        </div>
        <div class="flex flex-col gap-1.5 w-56">
          <label class="text-[0.8125rem] font-medium">Turma</label>
          <Select v-model="selectedClassGroupId" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Todas as turmas" class="w-full" filter showClear :disabled="!selectedSchoolId" />
        </div>
        <div class="flex flex-col gap-1.5 w-48">
          <label class="text-[0.8125rem] font-medium">Periodo</label>
          <Select v-model="selectedPeriodId" :options="periods" optionLabel="name" optionValue="id" placeholder="Todos os periodos" class="w-full" showClear :disabled="!selectedClassGroupId" />
        </div>
        <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text @click="clearFilters" />
        <div class="ml-auto">
          <Button label="Novo Relatorio" icon="pi pi-plus" @click="router.push('/assessment/descriptive/new')" />
        </div>
      </div>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhum relatorio descritivo encontrado" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column header="Aluno">
          <template #body="{ data }">
            {{ data.student?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Turma">
          <template #body="{ data }">
            {{ [data.class_group?.grade_level?.name, data.class_group?.name, data.class_group?.shift?.name_label].filter(Boolean).join(' - ') || '--' }}
          </template>
        </Column>
        <Column header="Campo de Experiencia">
          <template #body="{ data }">
            {{ data.experience_field?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Periodo">
          <template #body="{ data }">
            {{ data.assessment_period?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Conteudo">
          <template #body="{ data }">
            {{ truncate(data.content) }}
          </template>
        </Column>
        <Column header="Acoes" :style="{ width: '80px' }">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" text rounded @click="router.push(`/assessment/descriptive/${data.id}/edit`)" />
          </template>
        </Column>
      </DataTable>

      <Paginator
        v-if="totalRecords > perPage"
        :rows="perPage"
        :totalRecords="totalRecords"
        :first="(currentPage - 1) * perPage"
        :rowsPerPageOptions="[10, 15, 25, 50]"
        class="mt-4 border-t border-[#E0E0E0] pt-3"
        @page="onPageChange"
      />
    </div>
  </div>
</template>
