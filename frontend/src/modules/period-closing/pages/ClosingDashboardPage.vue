<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Paginator from 'primevue/paginator'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import { periodClosingService } from '@/services/period-closing.service'
import { useToast } from '@/composables/useToast'
import { periodClosingStatusLabel } from '@/shared/utils/enum-labels'
import type { PeriodClosing } from '@/types/period-closing'

const router = useRouter()
const toast = useToast()

const items = ref<PeriodClosing[]>([])
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(15)

const dashboard = ref<any>(null)

async function loadDashboard() {
  try {
    dashboard.value = await periodClosingService.getDashboard()
  } catch {
    toast.error('Erro ao carregar dashboard')
  }
}

async function loadData() {
  loading.value = true
  try {
    const response = await periodClosingService.getClosings({
      page: currentPage.value,
      per_page: perPage.value,
    })
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar fechamentos')
  } finally {
    loading.value = false
  }
}

function onPageChange(event: { page: number; rows: number }) {
  currentPage.value = event.page + 1
  perPage.value = event.rows
  loadData()
}

onMounted(async () => {
  await Promise.all([loadDashboard(), loadData()])
})
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">Fechamento de Periodo</h1>

    <div v-if="dashboard" class="stats-row">
      <div class="stat-card">
        <span class="stat-value">{{ dashboard.pending ?? 0 }}</span>
        <span class="stat-label">Pendentes</span>
      </div>
      <div class="stat-card stat-warn">
        <span class="stat-value">{{ dashboard.submitted ?? 0 }}</span>
        <span class="stat-label">Enviados</span>
      </div>
      <div class="stat-card stat-info">
        <span class="stat-value">{{ dashboard.validated ?? 0 }}</span>
        <span class="stat-label">Validados</span>
      </div>
      <div class="stat-card stat-success">
        <span class="stat-value">{{ dashboard.closed ?? 0 }}</span>
        <span class="stat-label">Fechados</span>
      </div>
      <div class="stat-card stat-danger">
        <span class="stat-value">{{ dashboard.rejected ?? 0 }}</span>
        <span class="stat-label">Rejeitados</span>
      </div>
    </div>

    <div class="card-section mt-3">
      <EmptyState v-if="!loading && items.length === 0" message="Nenhum fechamento encontrado" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column header="Periodo">
          <template #body="{ data }">
            {{ data.assessment_period?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Status">
          <template #body="{ data }">
            <StatusBadge :status="data.status" :label="periodClosingStatusLabel(data.status)" />
          </template>
        </Column>
        <Column header="Notas">
          <template #body="{ data }">
            <i :class="data.all_grades_complete ? 'pi pi-check-circle text-success' : 'pi pi-times-circle text-danger'" />
          </template>
        </Column>
        <Column header="Frequencia">
          <template #body="{ data }">
            <i :class="data.all_attendance_complete ? 'pi pi-check-circle text-success' : 'pi pi-times-circle text-danger'" />
          </template>
        </Column>
        <Column header="Diario">
          <template #body="{ data }">
            <i :class="data.all_lesson_records_complete ? 'pi pi-check-circle text-success' : 'pi pi-times-circle text-danger'" />
          </template>
        </Column>
        <Column header="Acoes" :style="{ width: '80px' }">
          <template #body="{ data }">
            <Button icon="pi pi-eye" text rounded @click="router.push(`/period-closing/${data.id}`)" />
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
      />
    </div>
  </div>
</template>

<style scoped>
.stats-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; }
.stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; text-align: center; display: flex; flex-direction: column; gap: 0.25rem; }
.stat-value { font-size: 1.5rem; font-weight: 700; }
.stat-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; }
.stat-success .stat-value { color: #22c55e; }
.stat-warn .stat-value { color: #f59e0b; }
.stat-info .stat-value { color: #3b82f6; }
.stat-danger .stat-value { color: #ef4444; }
.mt-3 { margin-top: 1.5rem; }
.text-success { color: #22c55e; }
.text-danger { color: #ef4444; }
</style>
