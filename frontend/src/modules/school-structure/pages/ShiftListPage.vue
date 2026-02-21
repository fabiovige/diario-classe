<script setup lang="ts">
import { onMounted, ref } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Toolbar from 'primevue/toolbar'
import Paginator from 'primevue/paginator'
import EmptyState from '@/shared/components/EmptyState.vue'
import FormDialog from '@/shared/components/FormDialog.vue'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { extractApiError } from '@/shared/utils/api-error'
import type { Shift, School } from '@/types/school-structure'

const toast = useToast()

const items = ref<Shift[]>([])
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(15)

const dialogVisible = ref(false)
const dialogLoading = ref(false)
const schools = ref<School[]>([])

const form = ref({
  name: '',
  school_id: null as number | null,
  start_time: '',
  end_time: '',
})

async function loadData() {
  loading.value = true
  try {
    const params: Record<string, unknown> = { page: currentPage.value, per_page: perPage.value }
    const response = await schoolStructureService.getShifts(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar turnos')
  } finally {
    loading.value = false
  }
}

async function loadSchools() {
  try {
    const response = await schoolStructureService.getSchools({ per_page: 100 })
    schools.value = response.data
  } catch {
    toast.error('Erro ao carregar escolas')
  }
}

function openDialog() {
  form.value = { name: '', school_id: null, start_time: '', end_time: '' }
  dialogVisible.value = true
}

async function handleSave() {
  dialogLoading.value = true
  try {
    await schoolStructureService.createShift(form.value)
    toast.success('Turno criado')
    dialogVisible.value = false
    loadData()
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao criar turno'))
  } finally {
    dialogLoading.value = false
  }
}

function onPageChange(event: { page: number; rows: number }) {
  currentPage.value = event.page + 1
  perPage.value = event.rows
  loadData()
}

onMounted(async () => {
  await Promise.all([loadData(), loadSchools()])
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Turnos</h1>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <Toolbar class="mb-4 border-none bg-transparent p-0">
        <template #start />
        <template #end>
          <Button label="Novo Turno" icon="pi pi-plus" @click="openDialog" />
        </template>
      </Toolbar>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhum turno encontrado" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column field="name" header="Nome" sortable />
        <Column header="Escola">
          <template #body="{ data }">
            {{ data.school?.name ?? '--' }}
          </template>
        </Column>
        <Column field="start_time" header="Inicio" />
        <Column field="end_time" header="Fim" />
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

    <FormDialog v-model:visible="dialogVisible" title="Novo Turno" :loading="dialogLoading" @save="handleSave">
      <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola *</label>
          <Select v-model="form.school_id" :options="schools" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Horario Inicio</label>
          <InputText v-model="form.start_time" type="time" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Horario Fim</label>
          <InputText v-model="form.end_time" type="time" class="w-full" />
        </div>
      </div>
    </FormDialog>
  </div>
</template>
