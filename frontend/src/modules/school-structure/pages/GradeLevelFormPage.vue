<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Select from 'primevue/select'
import Button from 'primevue/button'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { extractApiError } from '@/shared/utils/api-error'
import type { EducationLevel } from '@/types/enums'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const id = computed(() => route.params.id ? Number(route.params.id) : null)
const isEdit = computed(() => !!id.value)
const loading = ref(false)

const educationLevelOptions = [
  { label: 'Ed. Infantil', value: 'early_childhood' },
  { label: 'Fundamental', value: 'elementary' },
  { label: 'Ensino Medio', value: 'high_school' },
]

const form = ref({
  name: '',
  type: null as EducationLevel | null,
  order: null as number | null,
})

async function loadGradeLevel() {
  if (!id.value) return
  loading.value = true
  try {
    const level = await schoolStructureService.getGradeLevel(id.value)
    form.value.name = level.name
    form.value.type = level.type
    form.value.order = level.order
  } catch {
    toast.error('Erro ao carregar nivel de ensino')
    router.push('/school-structure/grade-levels')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    if (isEdit.value) {
      await schoolStructureService.updateGradeLevel(id.value!, form.value)
      toast.success('Nivel de ensino atualizado')
    } else {
      await schoolStructureService.createGradeLevel(form.value)
      toast.success('Nivel de ensino criado')
    }
    router.push('/school-structure/grade-levels')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao salvar nivel de ensino'))
  } finally {
    loading.value = false
  }
}

onMounted(loadGradeLevel)
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-fluent-primary">{{ isEdit ? 'Editar Nivel de Ensino' : 'Novo Nivel de Ensino' }}</h1>

    <div class="max-w-175 rounded-lg border border-fluent-border bg-white p-6 shadow-sm">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Tipo *</label>
          <Select v-model="form.type" :options="educationLevelOptions" optionLabel="label" optionValue="value" placeholder="Selecione" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Ordem *</label>
          <InputNumber v-model="form.order" :min="1" class="w-full" />
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="router.push('/school-structure/grade-levels')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
