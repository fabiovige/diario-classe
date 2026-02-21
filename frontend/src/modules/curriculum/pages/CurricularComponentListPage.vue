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
import FormDialog from '@/shared/components/FormDialog.vue'
import { curriculumService } from '@/services/curriculum.service'
import { useToast } from '@/composables/useToast'
import { extractApiError } from '@/shared/utils/api-error'
import type { CurricularComponent } from '@/types/curriculum'

const toast = useToast()

const items = ref<CurricularComponent[]>([])
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(15)
const search = ref('')

const dialogVisible = ref(false)
const dialogLoading = ref(false)

const form = ref({
  name: '',
  knowledge_area: '',
  code: '',
})

async function loadData() {
  loading.value = true
  try {
    const params: Record<string, unknown> = { page: currentPage.value, per_page: perPage.value }
    if (search.value) params.search = search.value
    const response = await curriculumService.getComponents(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar componentes curriculares')
  } finally {
    loading.value = false
  }
}

function openDialog() {
  form.value = { name: '', knowledge_area: '', code: '' }
  dialogVisible.value = true
}

async function handleSave() {
  dialogLoading.value = true
  try {
    await curriculumService.createComponent(form.value)
    toast.success('Componente curricular criado')
    dialogVisible.value = false
    loadData()
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao criar componente'))
  } finally {
    dialogLoading.value = false
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
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Componentes Curriculares</h1>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <Toolbar class="mb-4 border-none bg-transparent p-0">
        <template #start>
          <InputText v-model="search" placeholder="Buscar componente..." @keyup.enter="onSearch" />
          <Button icon="pi pi-search" class="ml-2" @click="onSearch" />
        </template>
        <template #end>
          <Button label="Novo Componente" icon="pi pi-plus" @click="openDialog" />
        </template>
      </Toolbar>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhum componente curricular encontrado" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column field="name" header="Nome" sortable />
        <Column field="knowledge_area" header="Area de Conhecimento" sortable />
        <Column field="code" header="Codigo" sortable />
        <Column header="Status">
          <template #body="{ data }">
            <StatusBadge :status="data.active ? 'active' : 'inactive'" :label="data.active ? 'Ativo' : 'Inativo'" />
          </template>
        </Column>
      </DataTable>

      <Paginator
        v-if="totalRecords > perPage"
        class="mt-4 border-t border-[#E0E0E0] pt-3"
        :rows="perPage"
        :totalRecords="totalRecords"
        :first="(currentPage - 1) * perPage"
        :rowsPerPageOptions="[10, 15, 25, 50]"
        @page="onPageChange"
      />
    </div>

    <FormDialog v-model:visible="dialogVisible" title="Novo Componente Curricular" :loading="dialogLoading" @save="handleSave">
      <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Area de Conhecimento *</label>
          <InputText v-model="form.knowledge_area" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Codigo *</label>
          <InputText v-model="form.code" required class="w-full" />
        </div>
      </div>
    </FormDialog>
  </div>
</template>
