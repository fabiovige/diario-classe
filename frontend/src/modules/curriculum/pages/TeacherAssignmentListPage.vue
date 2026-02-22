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
import EmptyState from '@/shared/components/EmptyState.vue'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import { curriculumService } from '@/services/curriculum.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import { useSchoolScope } from '@/composables/useSchoolScope'
import { formatDate } from '@/shared/utils/formatters'
import type { TeacherAssignment } from '@/types/curriculum'
import type { School } from '@/types/school-structure'

const router = useRouter()
const toast = useToast()
const { confirmDelete } = useConfirm()
const { shouldShowSchoolFilter, userSchoolName } = useSchoolScope()

const items = ref<TeacherAssignment[]>([])
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(15)
const search = ref('')

const schools = ref<School[]>([])
const selectedSchoolId = ref<number | null>(null)

const hasActiveFilters = computed(() => selectedSchoolId.value !== null)

async function loadSchools() {
  try {
    const response = await schoolStructureService.getSchools({ per_page: 200 })
    schools.value = response.data
  } catch {
    toast.error('Erro ao carregar escolas')
  }
}

watch(selectedSchoolId, () => {
  currentPage.value = 1
  loadData()
})

async function loadData() {
  loading.value = true
  try {
    const params: Record<string, unknown> = { page: currentPage.value, per_page: perPage.value }
    if (search.value) params.search = search.value
    if (selectedSchoolId.value) params.school_id = selectedSchoolId.value
    const response = await curriculumService.getAssignments(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar atribuicoes')
  } finally {
    loading.value = false
  }
}

function clearFilters() {
  selectedSchoolId.value = null
  currentPage.value = 1
  loadData()
}

function handleDelete(assignment: TeacherAssignment) {
  confirmDelete(async () => {
    try {
      await curriculumService.deleteAssignment(assignment.id)
      toast.success('Atribuicao excluida')
      loadData()
    } catch {
      toast.error('Erro ao excluir atribuicao')
    }
  })
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

onMounted(() => {
  if (shouldShowSchoolFilter.value) {
    loadSchools()
  }
  loadData()
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-fluent-primary">Atribuicoes de Professores</h1>

    <div class="rounded-lg border border-fluent-border bg-white p-6 shadow-sm">
      <div class="mb-4 grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-4">
        <div v-if="shouldShowSchoolFilter" class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <Select v-model="selectedSchoolId" :options="schools" optionLabel="name" optionValue="id" placeholder="Todas as escolas" class="w-full" filter showClear />
        </div>
        <div v-if="!shouldShowSchoolFilter && userSchoolName" class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <span class="flex h-[2.375rem] items-center rounded-md border border-fluent-border bg-[#F5F5F5] px-3 text-sm">{{ userSchoolName }}</span>
        </div>
      </div>

      <Toolbar class="mb-4 border-none bg-transparent p-0">
        <template #start>
          <InputText v-model="search" placeholder="Buscar professor..." @keyup.enter="onSearch" />
          <Button icon="pi pi-search" class="ml-2" @click="onSearch" />
          <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text class="ml-2" @click="clearFilters" />
        </template>
        <template #end>
          <Button label="Nova Atribuicao" icon="pi pi-plus" @click="router.push('/curriculum/assignments/new')" />
        </template>
      </Toolbar>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhuma atribuicao encontrada" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column header="Professor">
          <template #body="{ data }">
            {{ data.teacher?.user?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Turma">
          <template #body="{ data }">
            {{ [data.class_group?.grade_level?.name, data.class_group?.name, data.class_group?.shift?.name_label].filter(Boolean).join(' - ') || '--' }}
          </template>
        </Column>
        <Column header="Componente/Campo">
          <template #body="{ data }">
            {{ data.curricular_component?.name ?? data.experience_field?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Inicio">
          <template #body="{ data }">
            {{ formatDate(data.start_date) }}
          </template>
        </Column>
        <Column header="Status" :style="{ width: '100px' }">
          <template #body="{ data }">
            <StatusBadge :status="data.active ? 'active' : 'inactive'" :label="data.active ? 'Ativo' : 'Inativo'" />
          </template>
        </Column>
        <Column header="Acoes" :style="{ width: '120px' }">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" text rounded class="mr-1" @click="router.push(`/curriculum/assignments/${data.id}/edit`)" />
            <Button icon="pi pi-trash" text rounded severity="danger" @click="handleDelete(data)" />
          </template>
        </Column>
      </DataTable>

      <Paginator
        v-if="totalRecords > perPage"
        class="mt-4 border-t border-fluent-border pt-3"
        :rows="perPage"
        :totalRecords="totalRecords"
        :first="(currentPage - 1) * perPage"
        :rowsPerPageOptions="[10, 15, 25, 50]"
        @page="onPageChange"
      />
    </div>
  </div>
</template>
