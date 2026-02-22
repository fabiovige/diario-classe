<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Toolbar from 'primevue/toolbar'
import Paginator from 'primevue/paginator'
import Select from 'primevue/select'
import EmptyState from '@/shared/components/EmptyState.vue'
import { assessmentService } from '@/services/assessment.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { academicCalendarService } from '@/services/academic-calendar.service'
import { useToast } from '@/composables/useToast'
import type { DescriptiveReport } from '@/types/assessment'
import type { ClassGroup } from '@/types/school-structure'
import type { AssessmentPeriod } from '@/types/academic-calendar'

const router = useRouter()
const toast = useToast()

const items = ref<DescriptiveReport[]>([])
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(15)

const classGroups = ref<(ClassGroup & { label: string })[]>([])
const periods = ref<AssessmentPeriod[]>([])
const selectedClassGroupId = ref<number | null>(null)
const selectedPeriodId = ref<number | null>(null)

const hasActiveFilters = computed(() =>
  selectedClassGroupId.value !== null || selectedPeriodId.value !== null
)

const selectedClassGroup = computed(() =>
  classGroups.value.find(cg => cg.id === selectedClassGroupId.value)
)

async function loadClassGroups() {
  try {
    const response = await schoolStructureService.getClassGroups({ per_page: 200 })
    classGroups.value = response.data.map(cg => ({
      ...cg,
      label: [cg.grade_level?.name, cg.name, cg.shift?.name].filter(Boolean).join(' - '),
    }))
  } catch {
    toast.error('Erro ao carregar turmas')
  }
}

async function loadPeriods() {
  if (!selectedClassGroupId.value) {
    periods.value = []
    return
  }
  try {
    const academicYearId = selectedClassGroup.value?.academic_year_id
    const response = await academicCalendarService.getPeriods({ academic_year_id: academicYearId, per_page: 100 })
    periods.value = response.data
  } catch {
    toast.error('Erro ao carregar periodos')
  }
}

async function loadData() {
  loading.value = true
  try {
    const params: Record<string, unknown> = { page: currentPage.value, per_page: perPage.value }
    if (selectedClassGroupId.value) params.class_group_id = selectedClassGroupId.value
    if (selectedPeriodId.value) params.assessment_period_id = selectedPeriodId.value
    const response = await assessmentService.getDescriptiveReports(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar relatorios descritivos')
  } finally {
    loading.value = false
  }
}

function onPageChange(event: { page: number; rows: number }) {
  currentPage.value = event.page + 1
  perPage.value = event.rows
  loadData()
}

function onClassGroupChange() {
  selectedPeriodId.value = null
  currentPage.value = 1
  loadPeriods()
  loadData()
}

function onPeriodChange() {
  currentPage.value = 1
  loadData()
}

function clearFilters() {
  selectedClassGroupId.value = null
  selectedPeriodId.value = null
  periods.value = []
  currentPage.value = 1
  loadData()
}

function truncate(text: string, maxLength = 60): string {
  if (!text) return '--'
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}

onMounted(() => {
  loadClassGroups()
  loadData()
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Relatorios Descritivos</h1>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <div class="mb-4 grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Turma</label>
          <Select v-model="selectedClassGroupId" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Todas as turmas" class="w-full" filter showClear @change="onClassGroupChange" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Periodo</label>
          <Select v-model="selectedPeriodId" :options="periods" optionLabel="name" optionValue="id" placeholder="Todos os periodos" class="w-full" showClear :disabled="!selectedClassGroupId" @change="onPeriodChange" />
        </div>
      </div>

      <Toolbar class="mb-4 border-none bg-transparent p-0">
        <template #start>
          <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text @click="clearFilters" />
        </template>
        <template #end>
          <Button label="Novo Relatorio" icon="pi pi-plus" @click="router.push('/assessment/descriptive/new')" />
        </template>
      </Toolbar>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhum relatorio descritivo encontrado" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column header="Aluno">
          <template #body="{ data }">
            {{ data.student?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Turma">
          <template #body="{ data }">
            {{ data.class_group?.label ?? '--' }}
          </template>
        </Column>
        <Column header="Campo de Experiencia">
          <template #body="{ data }">
            {{ data.experience_field?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Periodo">
          <template #body="{ data }">
            {{ data.assessment_period?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Conteudo">
          <template #body="{ data }">
            {{ truncate(data.content) }}
          </template>
        </Column>
        <Column header="Acoes" :style="{ width: '80px' }">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" text rounded @click="router.push(`/assessment/descriptive/${data.id}/edit`)" />
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
