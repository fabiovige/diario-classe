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
  <div class="mb-4 flex items-center justify-between">
    <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" @click="router.back()" />
  </div>

  <div v-if="frequency" class="mb-6 metric-grid-sm">
    <div class="flex flex-col items-center gap-1 rounded-lg border border-md-border bg-white p-4 text-center">
      <span class="text-2xl font-bold">{{ frequency.total_classes }}</span>
      <span class="text-xs uppercase text-md-text-secondary">Total Aulas</span>
    </div>
    <div class="flex flex-col items-center gap-1 rounded-lg border border-md-border bg-white p-4 text-center">
      <span class="text-2xl font-bold text-md-success">{{ frequency.present }}</span>
      <span class="text-xs uppercase text-md-text-secondary">Presencas</span>
    </div>
    <div class="flex flex-col items-center gap-1 rounded-lg border border-md-border bg-white p-4 text-center">
      <span class="text-2xl font-bold text-md-error">{{ frequency.absent }}</span>
      <span class="text-xs uppercase text-md-text-secondary">Faltas</span>
    </div>
    <div class="flex flex-col items-center gap-1 rounded-lg border border-md-border bg-white p-4 text-center">
      <span class="text-2xl font-bold text-md-primary">{{ frequency.justified }}</span>
      <span class="text-xs uppercase text-md-text-secondary">Justificadas</span>
    </div>
    <div class="flex flex-col items-center gap-1 rounded-lg border border-md-border bg-white p-4 text-center">
      <span class="text-2xl font-bold">{{ formatPercentage(frequency.frequency_percentage) }}</span>
      <span class="text-xs uppercase text-md-text-secondary">Frequencia</span>
    </div>
  </div>

  <div class="card">
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
      class="mt-4 border-t border-md-border pt-3"
      :rows="perPage"
      :totalRecords="totalRecords"
      :first="(currentPage - 1) * perPage"
      :rowsPerPageOptions="[10, 15, 25, 50]"
      @page="onPageChange"
    />
  </div>
</template>
