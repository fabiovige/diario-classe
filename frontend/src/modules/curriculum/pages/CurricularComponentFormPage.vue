<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Button from 'primevue/button'
import { curriculumService } from '@/services/curriculum.service'
import { useToast } from '@/composables/useToast'
import { extractApiError } from '@/shared/utils/api-error'
import { KNOWLEDGE_AREA_OPTIONS } from '@/shared/utils/enum-labels'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const id = computed(() => route.params.id ? Number(route.params.id) : null)
const isEdit = computed(() => !!id.value)
const loading = ref(false)

const form = ref({
  name: '',
  knowledge_area: '' as string,
  code: '',
})

async function loadComponent() {
  if (!id.value) return
  loading.value = true
  try {
    const component = await curriculumService.getComponent(id.value)
    form.value.name = component.name
    form.value.knowledge_area = component.knowledge_area
    form.value.code = component.code
  } catch {
    toast.error('Erro ao carregar componente curricular')
    router.push('/curriculum/components')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    if (isEdit.value) {
      await curriculumService.updateComponent(id.value!, form.value)
      toast.success('Componente curricular atualizado')
    } else {
      await curriculumService.createComponent(form.value)
      toast.success('Componente curricular criado')
    }
    router.push('/curriculum/components')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao salvar componente curricular'))
  } finally {
    loading.value = false
  }
}

onMounted(loadComponent)
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-fluent-primary">{{ isEdit ? 'Editar Componente Curricular' : 'Novo Componente Curricular' }}</h1>

    <div class="max-w-175 rounded-lg border border-fluent-border bg-white p-6 shadow-sm">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Area de Conhecimento *</label>
          <Select v-model="form.knowledge_area" :options="KNOWLEDGE_AREA_OPTIONS" optionLabel="label" optionValue="value" placeholder="Selecione" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Codigo</label>
          <InputText v-model="form.code" class="w-full" />
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="router.push('/curriculum/components')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
