<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Toolbar from 'primevue/toolbar'
import Paginator from 'primevue/paginator'
import Select from 'primevue/select'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import { peopleService } from '@/services/people.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import { formatDate } from '@/shared/utils/formatters'
import type { Student } from '@/types/people'
import type { School, ClassGroup } from '@/types/school-structure'

const router = useRouter()
const toast = useToast()
const { confirmDelete } = useConfirm()

const items = ref<Student[]>([])
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(15)
const search = ref('')

const schools = ref<School[]>([])
const classGroups = ref<(ClassGroup & { label: string })[]>([])
const selectedSchoolId = ref<number | null>(null)
const selectedClassGroupId = ref<number | null>(null)

const hasActiveFilters = computed(() =>
  selectedSchoolId.value !== null || selectedClassGroupId.value !== null
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

async function loadData() {
  loading.value = true
  try {
    const params: Record<string, unknown> = { page: currentPage.value, per_page: perPage.value }
    if (search.value) params.search = search.value
    if (selectedSchoolId.value) params.school_id = selectedSchoolId.value
    if (selectedClassGroupId.value) params.class_group_id = selectedClassGroupId.value
    const response = await peopleService.getStudents(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar alunos')
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
  currentPage.value = 1
  loadData()
}

function handleDelete(student: Student) {
  confirmDelete(async () => {
    try {
      await peopleService.deleteStudent(student.id)
      toast.success('Aluno excluido')
      loadData()
    } catch {
      toast.error('Erro ao excluir aluno')
    }
  })
}

onMounted(() => {
  loadSchools()
  loadData()
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Alunos</h1>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <div class="mb-4 grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <Select v-model="selectedSchoolId" :options="schools" optionLabel="name" optionValue="id" placeholder="Todas as escolas" class="w-full" filter showClear />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Turma</label>
          <Select v-model="selectedClassGroupId" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Todas as turmas" class="w-full" filter showClear :disabled="!selectedSchoolId" @change="onFilter" />
        </div>
      </div>

      <Toolbar class="mb-4 border-none bg-transparent p-0">
        <template #start>
          <InputText v-model="search" placeholder="Buscar aluno..." @keyup.enter="onSearch" />
          <Button icon="pi pi-search" class="ml-2" @click="onSearch" />
          <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text class="ml-2" @click="clearFilters" />
        </template>
        <template #end>
          <Button label="Novo Aluno" icon="pi pi-plus" @click="router.push('/people/students/new')" />
        </template>
      </Toolbar>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhum aluno encontrado" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column field="name" header="Nome" sortable />
        <Column field="display_name" header="Nome Social" />
        <Column header="Escola">
          <template #body="{ data }">
            {{ data.current_enrollment?.school_name ?? '--' }}
          </template>
        </Column>
        <Column header="Turma">
          <template #body="{ data }">
            {{ data.current_enrollment?.class_group_label ?? '--' }}
          </template>
        </Column>
        <Column header="Data Nasc.">
          <template #body="{ data }">
            {{ formatDate(data.birth_date) }}
          </template>
        </Column>
        <Column header="Status">
          <template #body="{ data }">
            <StatusBadge :status="data.active ? 'active' : 'inactive'" :label="data.active ? 'Ativo' : 'Inativo'" />
          </template>
        </Column>
        <Column header="Acoes" :style="{ width: '190px' }">
          <template #body="{ data }">
            <Button icon="pi pi-eye" text rounded class="mr-1" @click="router.push(`/people/students/${data.id}`)" />
            <Button icon="pi pi-book" text rounded class="mr-1" @click="router.push(`/assessment/report-card/${data.id}`)" title="Boletim" />
            <Button icon="pi pi-pencil" text rounded class="mr-1" @click="router.push(`/people/students/${data.id}/edit`)" />
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
