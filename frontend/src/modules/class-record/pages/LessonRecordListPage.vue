<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Toolbar from 'primevue/toolbar'
import Paginator from 'primevue/paginator'
import EmptyState from '@/shared/components/EmptyState.vue'
import { classRecordService } from '@/services/class-record.service'
import { useToast } from '@/composables/useToast'
import { formatDate } from '@/shared/utils/formatters'
import type { LessonRecord } from '@/types/class-record'

const router = useRouter()
const toast = useToast()

const items = ref<LessonRecord[]>([])
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
    const response = await classRecordService.getRecords(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar registros de aula')
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

function truncate(text: string, maxLength = 60): string {
  if (!text) return '--'
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}

onMounted(loadData)
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Diario de Classe</h1>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <Toolbar class="mb-4 border-none bg-transparent p-0">
        <template #start>
          <InputText v-model="search" placeholder="Buscar registro..." @keyup.enter="onSearch" />
          <Button icon="pi pi-search" class="ml-2" @click="onSearch" />
        </template>
        <template #end>
          <Button label="Novo Registro" icon="pi pi-plus" @click="router.push('/class-record/new')" />
        </template>
      </Toolbar>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhum registro de aula encontrado" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column header="Data" sortable>
          <template #body="{ data }">
            {{ formatDate(data.date) }}
          </template>
        </Column>
        <Column header="Turma">
          <template #body="{ data }">
            {{ [data.class_group?.grade_level?.name, data.class_group?.name, data.class_group?.shift?.name].filter(Boolean).join(' - ') || '--' }}
          </template>
        </Column>
        <Column header="Conteudo">
          <template #body="{ data }">
            {{ truncate(data.content) }}
          </template>
        </Column>
        <Column field="class_count" header="Aulas" :style="{ width: '80px' }" />
        <Column header="Acoes" :style="{ width: '80px' }">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" text rounded @click="router.push(`/class-record/${data.id}/edit`)" />
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
