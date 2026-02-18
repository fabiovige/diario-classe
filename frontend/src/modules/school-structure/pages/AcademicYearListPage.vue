<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Toolbar from 'primevue/toolbar'
import Paginator from 'primevue/paginator'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { academicYearStatusLabel } from '@/shared/utils/enum-labels'
import { formatDate } from '@/shared/utils/formatters'
import type { AcademicYear } from '@/types/school-structure'

const router = useRouter()
const toast = useToast()

const items = ref<AcademicYear[]>([])
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(15)
const search = ref('')

async function loadData() {
  loading.value = true
  try {
    const params: Record<string, unknown> = { page: currentPage.value, per_page: perPage.value }
    if (search.value) params.search = search.value
    const response = await schoolStructureService.getAcademicYears(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar anos letivos')
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

onMounted(loadData)
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Anos Letivos</h1>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <Toolbar class="mb-4 border-none bg-transparent p-0">
        <template #start>
          <InputText v-model="search" placeholder="Buscar ano letivo..." @keyup.enter="onSearch" />
          <Button icon="pi pi-search" class="ml-2" @click="onSearch" />
        </template>
        <template #end>
          <Button label="Novo Ano Letivo" icon="pi pi-plus" @click="router.push('/school-structure/academic-years/new')" />
        </template>
      </Toolbar>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhum ano letivo encontrado" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column field="year" header="Ano" sortable />
        <Column header="Escola">
          <template #body="{ data }">
            {{ data.school?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Status">
          <template #body="{ data }">
            <StatusBadge :status="data.status" :label="academicYearStatusLabel(data.status)" />
          </template>
        </Column>
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
        <Column header="Acoes" :style="{ width: '80px' }">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" text rounded @click="router.push(`/school-structure/academic-years/${data.id}/edit`)" />
          </template>
        </Column>
      </DataTable>

      <Paginator
        v-if="totalRecords > perPage"
        :rows="perPage"
        :totalRecords="totalRecords"
        :first="(currentPage - 1) * perPage"
        :rowsPerPageOptions="[10, 15, 25, 50]"
        @page="onPageChange"
        class="mt-4 border-t border-[#E0E0E0] pt-3"
      />
    </div>
  </div>
</template>
