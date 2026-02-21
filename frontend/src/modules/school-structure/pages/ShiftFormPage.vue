<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Button from 'primevue/button'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { extractApiError } from '@/shared/utils/api-error'
import type { School } from '@/types/school-structure'
import type { ShiftPeriod } from '@/types/enums'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const id = computed(() => route.params.id ? Number(route.params.id) : null)
const isEdit = computed(() => !!id.value)
const loading = ref(false)
const schools = ref<School[]>([])

const shiftNameOptions = [
  { label: 'Manhã', value: 'morning' },
  { label: 'Tarde', value: 'afternoon' },
  { label: 'Noite', value: 'evening' },
  { label: 'Integral', value: 'full_time' },
]

const form = ref({
  name: null as ShiftPeriod | null,
  school_id: null as number | null,
  start_time: '',
  end_time: '',
})

async function loadSchools() {
  try {
    const response = await schoolStructureService.getSchools({ per_page: 100 })
    schools.value = response.data
  } catch {
    toast.error('Erro ao carregar escolas')
  }
}

async function loadShift() {
  if (!id.value) return
  loading.value = true
  try {
    const shift = await schoolStructureService.getShift(id.value)
    form.value.name = shift.name
    form.value.school_id = shift.school_id
    form.value.start_time = shift.start_time ?? ''
    form.value.end_time = shift.end_time ?? ''
  } catch {
    toast.error('Erro ao carregar turno')
    router.push('/school-structure/shifts')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    if (isEdit.value) {
      await schoolStructureService.updateShift(id.value!, form.value)
      toast.success('Turno atualizado')
    } else {
      await schoolStructureService.createShift(form.value)
      toast.success('Turno criado')
    }
    router.push('/school-structure/shifts')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao salvar turno'))
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadSchools()
  await loadShift()
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-fluent-primary">{{ isEdit ? 'Editar Turno' : 'Novo Turno' }}</h1>

    <div class="max-w-175 rounded-lg border border-fluent-border bg-white p-6 shadow-sm">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Nome *</label>
          <Select v-model="form.name" :options="shiftNameOptions" optionLabel="label" optionValue="value" placeholder="Selecione" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola *</label>
          <Select v-model="form.school_id" :options="schools" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="flex flex-col gap-1.5">
            <label class="text-[0.8125rem] font-medium">Horario Inicio *</label>
            <InputText v-model="form.start_time" type="time" class="w-full" />
          </div>
          <div class="flex flex-col gap-1.5">
            <label class="text-[0.8125rem] font-medium">Horario Fim *</label>
            <InputText v-model="form.end_time" type="time" class="w-full" />
          </div>
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="router.push('/school-structure/shifts')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
