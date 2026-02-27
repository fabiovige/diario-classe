<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Paginator from 'primevue/paginator'
import Select from 'primevue/select'
import EmptyState from '@/shared/components/EmptyState.vue'
import { classRecordService } from '@/services/class-record.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import { useSchoolScope } from '@/composables/useSchoolScope'
import { formatDate } from '@/shared/utils/formatters'
import type { LessonRecord } from '@/types/class-record'
import type { ClassGroup, School } from '@/types/school-structure'

const router = useRouter()
const toast = useToast()
const { confirmDelete } = useConfirm()
const { shouldShowSchoolFilter, userSchoolId, userSchoolName } = useSchoolScope()

const items = ref<LessonRecord[]>([])
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(15)
const search = ref('')

const schools = ref<School[]>([])
const classGroups = ref<(ClassGroup & { label: string })[]>([])
const selectedSchoolId = ref<number | null>(null)
const selectedClassGroupId = ref<number | null>(null)
const dateFrom = ref('')
const dateTo = ref('')

const hasActiveFilters = computed(() =>
  selectedSchoolId.value !== null || selectedClassGroupId.value !== null || dateFrom.value !== '' || dateTo.value !== ''
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
      label: [cg.grade_level?.name, cg.name, cg.shift?.name].filter(Boolean).join(' - '),
    }))
  } catch {
    toast.error('Erro ao carregar turmas')
  }
}

watch(selectedSchoolId, () => {
  selectedClassGroupId.value = null
  classGroups.value = []
  loadClassGroups()
  onFilter()
})

watch(selectedClassGroupId, () => {
  onFilter()
})

async function loadData() {
  loading.value = true
  try {
    const params: Record<string, unknown> = { page: currentPage.value, per_page: perPage.value }
    if (search.value) params.search = search.value
    if (selectedSchoolId.value) params.school_id = selectedSchoolId.value
    if (selectedClassGroupId.value) params.class_group_id = selectedClassGroupId.value
    if (dateFrom.value) params.date_from = dateFrom.value
    if (dateTo.value) params.date_to = dateTo.value
    const response = await classRecordService.getRecords(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar registros de aula')
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
  selectedClassGroupId.value = null
  classGroups.value = []
  dateFrom.value = ''
  dateTo.value = ''
  currentPage.value = 1
  loadData()
}

function truncate(text: string, maxLength = 60): string {
  if (!text) return '--'
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}

function handleDelete(record: LessonRecord) {
  confirmDelete(async () => {
    try {
      await classRecordService.deleteRecord(record.id)
      toast.success('Registro de aula excluido')
      loadData()
    } catch {
      toast.error('Erro ao excluir registro de aula')
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
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Diario de Classe</h1>

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
        <div class="flex flex-col gap-1.5 w-40">
          <label class="text-[0.8125rem] font-medium">Data De</label>
          <InputText v-model="dateFrom" type="date" class="w-full" @change="onFilter" />
        </div>
        <div class="flex flex-col gap-1.5 w-40">
          <label class="text-[0.8125rem] font-medium">Data Ate</label>
          <InputText v-model="dateTo" type="date" class="w-full" @change="onFilter" />
        </div>
        <div class="flex flex-col gap-1.5">
          <InputText v-model="search" placeholder="Buscar registro..." @keyup.enter="onSearch" />
        </div>
        <Button icon="pi pi-search" @click="onSearch" />
        <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text @click="clearFilters" />
        <div class="ml-auto">
          <Button label="Novo Registro" icon="pi pi-plus" @click="router.push('/class-record/new')" />
        </div>
      </div>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhum registro de aula encontrado" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column header="Data" sortable>
          <template #body="{ data }">
            {{ formatDate(data.date) }}
          </template>
        </Column>
        <Column header="Turma">
          <template #body="{ data }">
            {{ [data.class_group?.grade_level?.name, data.class_group?.name, data.class_group?.shift?.name].filter(Boolean).join(' - ') || '--' }}
          </template>
        </Column>
        <Column header="Conteudo">
          <template #body="{ data }">
            {{ truncate(data.content) }}
          </template>
        </Column>
        <Column field="class_count" header="Aulas" :style="{ width: '80px' }" />
        <Column header="Acoes" :style="{ width: '120px' }">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" text rounded class="mr-1" @click="router.push(`/class-record/${data.id}/edit`)" />
            <Button icon="pi pi-trash" text rounded severity="danger" @click="handleDelete(data)" />
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
