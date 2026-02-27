<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Paginator from 'primevue/paginator'
import Select from 'primevue/select'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import { academicCalendarService } from '@/services/academic-calendar.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import { useSchoolScope } from '@/composables/useSchoolScope'
import { periodTypeLabel } from '@/shared/utils/enum-labels'
import { formatDate } from '@/shared/utils/formatters'
import type { AssessmentPeriod } from '@/types/academic-calendar'
import type { School, AcademicYear } from '@/types/school-structure'

const router = useRouter()
const toast = useToast()
const { confirmDelete } = useConfirm()
const { shouldShowSchoolFilter, userSchoolId, userSchoolName } = useSchoolScope()

const items = ref<AssessmentPeriod[]>([])
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(15)
const search = ref('')

const schools = ref<School[]>([])
const academicYears = ref<AcademicYear[]>([])
const selectedSchoolId = ref<number | null>(null)
const selectedAcademicYearId = ref<number | null>(null)

const hasActiveFilters = computed(() =>
  selectedSchoolId.value !== null || selectedAcademicYearId.value !== null
)

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

watch(selectedSchoolId, () => {
  selectedAcademicYearId.value = null
  academicYears.value = []
  loadAcademicYears()
  onFilter()
})

watch(selectedAcademicYearId, () => {
  onFilter()
})

async function loadData() {
  loading.value = true
  try {
    const params: Record<string, unknown> = { page: currentPage.value, per_page: perPage.value }
    if (search.value) params.search = search.value
    if (selectedSchoolId.value) params.school_id = selectedSchoolId.value
    if (selectedAcademicYearId.value) params.academic_year_id = selectedAcademicYearId.value
    const response = await academicCalendarService.getPeriods(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar periodos avaliativos')
  } finally {
    loading.value = false
  }
}

function onPageChange(event: { page: number; rows: number }) {
  currentPage.value = event.page + 1
  perPage.value = event.rows
  loadData()
}

function onSearch() {
  currentPage.value = 1
  loadData()
}

function onFilter() {
  currentPage.value = 1
  loadData()
}

function clearFilters() {
  selectedSchoolId.value = null
  selectedAcademicYearId.value = null
  academicYears.value = []
  currentPage.value = 1
  loadData()
}

function handleDelete(period: AssessmentPeriod) {
  confirmDelete(async () => {
    try {
      await academicCalendarService.deletePeriod(period.id)
      toast.success('Periodo avaliativo excluido')
      loadData()
    } catch {
      toast.error('Erro ao excluir periodo avaliativo')
    }
  })
}

onMounted(() => {
  if (shouldShowSchoolFilter.value) {
    loadSchools()
  }
  if (!shouldShowSchoolFilter.value && userSchoolId.value) {
    selectedSchoolId.value = userSchoolId.value
    return
  }
  loadData()
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Periodos Avaliativos</h1>

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
        <div class="flex flex-col gap-1.5 w-48">
          <label class="text-[0.8125rem] font-medium">Ano Letivo</label>
          <Select v-model="selectedAcademicYearId" :options="academicYears" optionLabel="year" optionValue="id" placeholder="Todos os anos" class="w-full" showClear :disabled="!selectedSchoolId" />
        </div>
        <div class="flex flex-col gap-1.5">
          <InputText v-model="search" placeholder="Buscar periodo..." @keyup.enter="onSearch" />
        </div>
        <Button icon="pi pi-search" @click="onSearch" />
        <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text @click="clearFilters" />
        <div class="ml-auto">
          <Button label="Novo Periodo" icon="pi pi-plus" @click="router.push('/academic-calendar/periods/new')" />
        </div>
      </div>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhum periodo avaliativo encontrado" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column field="name" header="Nome" sortable />
        <Column header="Tipo">
          <template #body="{ data }">
            {{ periodTypeLabel(data.type) }}
          </template>
        </Column>
        <Column field="number" header="Numero" sortable />
        <Column header="Inicio">
          <template #body="{ data }">
            {{ formatDate(data.start_date) }}
          </template>
        </Column>
        <Column header="Fim">
          <template #body="{ data }">
            {{ formatDate(data.end_date) }}
          </template>
        </Column>
        <Column header="Status">
          <template #body="{ data }">
            <StatusBadge :status="data.status" :label="data.status" />
          </template>
        </Column>
        <Column header="Acoes" :style="{ width: '120px' }">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" text rounded class="mr-1" @click="router.push(`/academic-calendar/periods/${data.id}/edit`)" />
            <Button icon="pi pi-trash" text rounded severity="danger" @click="handleDelete(data)" />
          </template>
        </Column>
      </DataTable>

      <Paginator
        v-if="totalRecords > perPage"
        class="mt-4 border-t border-[#E0E0E0] pt-3"
        :rows="perPage"
        :totalRecords="totalRecords"
        :first="(currentPage - 1) * perPage"
        :rowsPerPageOptions="[10, 15, 25, 50]"
        @page="onPageChange"
      />
    </div>
  </div>
</template>
