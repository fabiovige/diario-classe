<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Paginator from 'primevue/paginator'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import MetricCard from '@/shared/components/MetricCard.vue'
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

const dashboard = ref<Record<string, number> | null>(null)

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

function disciplineName(row: PeriodClosing): string {
  return row.teacher_assignment?.curricular_component?.name
    ?? row.teacher_assignment?.experience_field?.name
    ?? '--'
}

function turmaName(row: PeriodClosing): string {
  if (!row.class_group) return '--'
  const grade = row.class_group.grade_level?.name ?? ''
  const shift = row.class_group.shift?.name ?? ''
  const parts = [row.class_group.name, grade, shift].filter(Boolean)
  return parts.join(' - ')
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
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Fechamento de Periodo</h1>

    <div v-if="dashboard" class="grid grid-cols-[repeat(auto-fill,minmax(150px,1fr))] gap-4 mb-6">
      <MetricCard title="Total" :value="dashboard.total ?? 0" label="Fechamentos" color="#0078D4" icon="pi pi-list" />
      <MetricCard title="Pendentes" :value="dashboard.pending ?? 0" label="Aguardando envio" color="#9D5D00" icon="pi pi-clock" />
      <MetricCard title="Em Validacao" :value="dashboard.in_validation ?? 0" label="Aguardando aprovacao" color="#005A9E" icon="pi pi-hourglass" />
      <MetricCard title="Aprovados" :value="dashboard.approved ?? 0" label="Prontos para fechar" color="#0F7B0F" icon="pi pi-check" />
      <MetricCard title="Fechados" :value="dashboard.closed ?? 0" label="Concluidos" color="#0F7B0F" icon="pi pi-lock" />
    </div>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <EmptyState v-if="!loading && items.length === 0" message="Nenhum fechamento encontrado" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column header="Turma">
          <template #body="{ data }">
            {{ turmaName(data) }}
          </template>
        </Column>
        <Column header="Disciplina">
          <template #body="{ data }">
            {{ disciplineName(data) }}
          </template>
        </Column>
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
            <i :class="data.all_grades_complete ? 'pi pi-check-circle text-[#0F7B0F]' : 'pi pi-times-circle text-[#C42B1C]'" />
          </template>
        </Column>
        <Column header="Frequencia">
          <template #body="{ data }">
            <i :class="data.all_attendance_complete ? 'pi pi-check-circle text-[#0F7B0F]' : 'pi pi-times-circle text-[#C42B1C]'" />
          </template>
        </Column>
        <Column header="Diario">
          <template #body="{ data }">
            <i :class="data.all_lesson_records_complete ? 'pi pi-check-circle text-[#0F7B0F]' : 'pi pi-times-circle text-[#C42B1C]'" />
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
        class="mt-4 border-t border-[#E0E0E0] pt-3"
        @page="onPageChange"
      />
    </div>
  </div>
</template>
