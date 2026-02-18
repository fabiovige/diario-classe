<script setup lang="ts">
import { onMounted, ref } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Toolbar from 'primevue/toolbar'
import Paginator from 'primevue/paginator'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import { assessmentService } from '@/services/assessment.service'
import { useToast } from '@/composables/useToast'
import type { Grade } from '@/types/assessment'

const toast = useToast()

const items = ref<Grade[]>([])
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

onMounted(loadData)
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">Notas</h1>

    <div class="card-section">
      <Toolbar class="mb-3">
        <template #start>
          <InputText v-model="search" placeholder="Buscar nota..." @keyup.enter="onSearch" />
          <Button icon="pi pi-search" class="ml-2" @click="onSearch" />
        </template>
      </Toolbar>

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
        @page="onPageChange"
      />
    </div>
  </div>
</template>

<style scoped>
.mb-3 { margin-bottom: 1rem; }
.ml-2 { margin-left: 0.5rem; }
</style>
