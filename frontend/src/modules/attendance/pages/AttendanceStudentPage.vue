<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Paginator from 'primevue/paginator'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import { attendanceService } from '@/services/attendance.service'
import { useToast } from '@/composables/useToast'
import { attendanceStatusLabel } from '@/shared/utils/enum-labels'
import { formatDate, formatPercentage } from '@/shared/utils/formatters'
import type { AttendanceRecord, StudentFrequency } from '@/types/attendance'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const studentId = Number(route.params.studentId)
const records = ref<AttendanceRecord[]>([])
const frequency = ref<StudentFrequency | null>(null)
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(15)

async function loadFrequency() {
  try {
    frequency.value = await attendanceService.getStudentFrequency(studentId)
  } catch {
    toast.error('Erro ao carregar frequencia')
  }
}

async function loadRecords() {
  loading.value = true
  try {
    const response = await attendanceService.getByStudent(studentId, {
      page: currentPage.value,
      per_page: perPage.value,
    })
    records.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar registros')
  } finally {
    loading.value = false
  }
}

function onPageChange(event: { page: number; rows: number }) {
  currentPage.value = event.page + 1
  perPage.value = event.rows
  loadRecords()
}

onMounted(async () => {
  await Promise.all([loadFrequency(), loadRecords()])
})
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Frequencia do Aluno</h1>
      <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" @click="router.back()" />
    </div>

    <div v-if="frequency" class="stats-row">
      <div class="stat-card">
        <span class="stat-value">{{ frequency.total_classes }}</span>
        <span class="stat-label">Total Aulas</span>
      </div>
      <div class="stat-card stat-success">
        <span class="stat-value">{{ frequency.present }}</span>
        <span class="stat-label">Presencas</span>
      </div>
      <div class="stat-card stat-danger">
        <span class="stat-value">{{ frequency.absent }}</span>
        <span class="stat-label">Faltas</span>
      </div>
      <div class="stat-card stat-info">
        <span class="stat-value">{{ frequency.justified }}</span>
        <span class="stat-label">Justificadas</span>
      </div>
      <div class="stat-card">
        <span class="stat-value">{{ formatPercentage(frequency.frequency_percentage) }}</span>
        <span class="stat-label">Frequencia</span>
      </div>
    </div>

    <div class="card-section mt-3">
      <EmptyState v-if="!loading && records.length === 0" message="Nenhum registro de frequencia" />

      <DataTable v-if="records.length > 0" :value="records" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column header="Data">
          <template #body="{ data }">
            {{ formatDate(data.date) }}
          </template>
        </Column>
        <Column header="Status">
          <template #body="{ data }">
            <StatusBadge :status="data.status" :label="attendanceStatusLabel(data.status)" />
          </template>
        </Column>
        <Column header="Registrado em">
          <template #body="{ data }">
            {{ formatDate(data.created_at) }}
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
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.stats-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; }
.stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; text-align: center; display: flex; flex-direction: column; gap: 0.25rem; }
.stat-value { font-size: 1.5rem; font-weight: 700; }
.stat-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; }
.stat-success .stat-value { color: #22c55e; }
.stat-danger .stat-value { color: #ef4444; }
.stat-info .stat-value { color: #3b82f6; }
.mt-3 { margin-top: 1.5rem; }
</style>
