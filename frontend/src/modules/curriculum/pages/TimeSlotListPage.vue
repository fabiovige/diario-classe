<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Toolbar from 'primevue/toolbar'
import Select from 'primevue/select'
import InputMask from 'primevue/inputmask'
import InputNumber from 'primevue/inputnumber'
import Dialog from 'primevue/dialog'
import EmptyState from '@/shared/components/EmptyState.vue'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import { curriculumService } from '@/services/curriculum.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import { useSchoolScope } from '@/composables/useSchoolScope'
import { extractApiError } from '@/shared/utils/api-error'
import type { TimeSlot } from '@/types/curriculum'
import type { School, Shift } from '@/types/school-structure'

const toast = useToast()
const { confirmDelete } = useConfirm()
const { shouldShowSchoolFilter, userSchoolName } = useSchoolScope()

const items = ref<TimeSlot[]>([])
const loading = ref(false)

const schools = ref<School[]>([])
const shifts = ref<Shift[]>([])
const selectedSchoolId = ref<number | null>(null)
const selectedShiftId = ref<number | null>(null)

const showFormDialog = ref(false)
const formLoading = ref(false)
const editingId = ref<number | null>(null)
const form = ref({
  shift_id: null as number | null,
  number: null as number | null,
  start_time: '',
  end_time: '',
  type: 'class' as string,
})

const typeOptions = [
  { label: 'Aula', value: 'class' },
  { label: 'Intervalo', value: 'break' },
]

async function loadSchools() {
  try {
    const response = await schoolStructureService.getSchools({ per_page: 200 })
    schools.value = response.data
  } catch {
    toast.error('Erro ao carregar escolas')
  }
}

async function loadShifts() {
  if (!selectedSchoolId.value) {
    shifts.value = []
    return
  }
  try {
    const response = await schoolStructureService.getShifts({ school_id: selectedSchoolId.value, per_page: 100 })
    shifts.value = response.data
  } catch {
    toast.error('Erro ao carregar turnos')
  }
}

watch(selectedSchoolId, () => {
  selectedShiftId.value = null
  items.value = []
  loadShifts()
})

watch(selectedShiftId, () => {
  loadData()
})

async function loadData() {
  if (!selectedShiftId.value) {
    items.value = []
    return
  }
  loading.value = true
  try {
    const response = await curriculumService.getTimeSlots({ shift_id: selectedShiftId.value })
    items.value = (response as any).data ?? response
  } catch {
    toast.error('Erro ao carregar horarios')
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingId.value = null
  form.value = {
    shift_id: selectedShiftId.value,
    number: (items.value.length + 1),
    start_time: '',
    end_time: '',
    type: 'class',
  }
  showFormDialog.value = true
}

function openEdit(slot: TimeSlot) {
  editingId.value = slot.id
  form.value = {
    shift_id: slot.shift_id,
    number: slot.number,
    start_time: slot.start_time,
    end_time: slot.end_time,
    type: typeof slot.type === 'string' ? slot.type : (slot.type as any),
  }
  showFormDialog.value = true
}

async function handleSave() {
  formLoading.value = true
  try {
    if (editingId.value) {
      await curriculumService.updateTimeSlot(editingId.value, form.value)
      toast.success('Horario atualizado')
    } else {
      await curriculumService.createTimeSlot(form.value)
      toast.success('Horario criado')
    }
    showFormDialog.value = false
    loadData()
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao salvar horario'))
  } finally {
    formLoading.value = false
  }
}

function handleDelete(slot: TimeSlot) {
  confirmDelete(async () => {
    try {
      await curriculumService.deleteTimeSlot(slot.id)
      toast.success('Horario excluido')
      loadData()
    } catch {
      toast.error('Erro ao excluir horario')
    }
  })
}

onMounted(() => {
  if (shouldShowSchoolFilter.value) {
    loadSchools()
  }
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-fluent-primary">Horarios de Aula</h1>

    <div class="rounded-lg border border-fluent-border bg-white p-6 shadow-sm">
      <div class="mb-4 grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-4">
        <div v-if="shouldShowSchoolFilter" class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <Select v-model="selectedSchoolId" :options="schools" optionLabel="name" optionValue="id" placeholder="Selecione a escola" class="w-full" filter />
        </div>
        <div v-if="!shouldShowSchoolFilter && userSchoolName" class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <span class="flex h-[2.375rem] items-center rounded-md border border-fluent-border bg-[#F5F5F5] px-3 text-sm">{{ userSchoolName }}</span>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Turno</label>
          <Select v-model="selectedShiftId" :options="shifts" optionLabel="name_label" optionValue="id" placeholder="Selecione o turno" class="w-full" :disabled="!selectedSchoolId && shouldShowSchoolFilter" />
        </div>
      </div>

      <Toolbar v-if="selectedShiftId" class="mb-4 border-none bg-transparent p-0">
        <template #end>
          <Button label="Novo Horario" icon="pi pi-plus" @click="openCreate" />
        </template>
      </Toolbar>

      <EmptyState v-if="!loading && items.length === 0 && selectedShiftId" message="Nenhum horario cadastrado para este turno" />
      <EmptyState v-if="!selectedShiftId" message="Selecione uma escola e um turno para visualizar os horarios" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column header="Numero" field="number" :style="{ width: '80px' }" />
        <Column header="Tipo">
          <template #body="{ data }">
            <StatusBadge :status="data.type === 'class' ? 'active' : 'warning'" :label="data.type_label" />
          </template>
        </Column>
        <Column header="Inicio" field="start_time" />
        <Column header="Fim" field="end_time" />
        <Column header="Acoes" :style="{ width: '120px' }">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" text rounded class="mr-1" @click="openEdit(data)" />
            <Button icon="pi pi-trash" text rounded severity="danger" @click="handleDelete(data)" />
          </template>
        </Column>
      </DataTable>
    </div>

    <Dialog v-model:visible="showFormDialog" :header="editingId ? 'Editar Horario' : 'Novo Horario'" modal :style="{ width: '400px' }">
      <form @submit.prevent="handleSave" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Numero *</label>
          <InputNumber v-model="form.number" :min="1" :max="10" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Tipo *</label>
          <Select v-model="form.type" :options="typeOptions" optionLabel="label" optionValue="value" class="w-full" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="flex flex-col gap-1.5">
            <label class="text-[0.8125rem] font-medium">Inicio *</label>
            <InputMask v-model="form.start_time" mask="99:99" placeholder="HH:MM" class="w-full" />
          </div>
          <div class="flex flex-col gap-1.5">
            <label class="text-[0.8125rem] font-medium">Fim *</label>
            <InputMask v-model="form.end_time" mask="99:99" placeholder="HH:MM" class="w-full" />
          </div>
        </div>
        <div class="mt-2 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="showFormDialog = false" />
          <Button type="submit" :label="editingId ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="formLoading" />
        </div>
      </form>
    </Dialog>
  </div>
</template>
