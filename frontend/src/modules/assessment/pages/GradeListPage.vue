<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Paginator from 'primevue/paginator'
import Select from 'primevue/select'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import { assessmentService } from '@/services/assessment.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { useSchoolScope } from '@/composables/useSchoolScope'
import type { Grade } from '@/types/assessment'
import type { School, ClassGroup } from '@/types/school-structure'

const toast = useToast()
const { shouldShowSchoolFilter, userSchoolId, userSchoolName } = useSchoolScope()

const items = ref<Grade[]>([])
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
    const response = await assessmentService.getGrades(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar notas')
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
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Notas</h1>

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
        <div class="flex flex-col gap-1.5">
          <InputText v-model="search" placeholder="Buscar nota..." @keyup.enter="onSearch" />
        </div>
        <Button icon="pi pi-search" @click="onSearch" />
        <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text @click="clearFilters" />
      </div>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhuma nota encontrada" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column header="Aluno">
          <template #body="{ data }">
            {{ data.student?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Nota">
          <template #body="{ data }">
            {{ data.numeric_value ?? '--' }}
          </template>
        </Column>
        <Column header="Conceito">
          <template #body="{ data }">
            {{ data.conceptual_value ?? '--' }}
          </template>
        </Column>
        <Column header="Recuperacao">
          <template #body="{ data }">
            <StatusBadge v-if="data.is_recovery" status="justified" label="Sim" />
            <span v-else>Nao</span>
          </template>
        </Column>
        <Column field="observations" header="Observacoes" />
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
