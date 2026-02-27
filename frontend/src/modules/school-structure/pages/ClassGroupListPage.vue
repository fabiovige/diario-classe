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
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import { useSchoolScope } from '@/composables/useSchoolScope'
import type { ClassGroup, School, AcademicYear } from '@/types/school-structure'

const router = useRouter()
const toast = useToast()
const { confirmDelete } = useConfirm()
const { shouldShowSchoolFilter, userSchoolId, userSchoolName } = useSchoolScope()

const items = ref<ClassGroup[]>([])
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
    const response = await schoolStructureService.getClassGroups(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar turmas')
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

function handleDelete(classGroup: ClassGroup) {
  confirmDelete(async () => {
    try {
      await schoolStructureService.deleteClassGroup(classGroup.id)
      toast.success('Turma excluida')
      loadData()
    } catch {
      toast.error('Erro ao excluir turma')
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
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Turmas</h1>

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
          <InputText v-model="search" placeholder="Buscar turma..." @keyup.enter="onSearch" />
        </div>
        <Button icon="pi pi-search" @click="onSearch" />
        <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text @click="clearFilters" />
        <div class="ml-auto">
          <Button label="Nova Turma" icon="pi pi-plus" @click="router.push('/school-structure/class-groups/new')" />
        </div>
      </div>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhuma turma encontrada" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column field="name" header="Nome" sortable />
        <Column header="Nivel">
          <template #body="{ data }">
            {{ data.grade_level?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Turno">
          <template #body="{ data }">
            {{ data.shift?.name_label ?? '--' }}
          </template>
        </Column>
        <Column field="max_students" header="Max. Alunos" />
        <Column header="Acoes" :style="{ width: '120px' }">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" text rounded class="mr-1" @click="router.push(`/school-structure/class-groups/${data.id}/edit`)" />
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
