<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import { curriculumService } from '@/services/curriculum.service'
import { useToast } from '@/composables/useToast'
import { extractApiError } from '@/shared/utils/api-error'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const id = computed(() => route.params.id ? Number(route.params.id) : null)
const isEdit = computed(() => !!id.value)
const loading = ref(false)

const form = ref({
  name: '',
  code: '',
})

async function loadField() {
  if (!id.value) return
  loading.value = true
  try {
    const field = await curriculumService.getExperienceField(id.value)
    form.value.name = field.name
    form.value.code = field.code
  } catch {
    toast.error('Erro ao carregar campo de experiencia')
    router.push('/curriculum/experience-fields')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    if (isEdit.value) {
      await curriculumService.updateExperienceField(id.value!, form.value)
      toast.success('Campo de experiencia atualizado')
    } else {
      await curriculumService.createExperienceField(form.value)
      toast.success('Campo de experiencia criado')
    }
    router.push('/curriculum/experience-fields')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao salvar campo de experiencia'))
  } finally {
    loading.value = false
  }
}

onMounted(loadField)
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-fluent-primary">{{ isEdit ? 'Editar Campo de Experiencia' : 'Novo Campo de Experiencia' }}</h1>

    <div class="max-w-175 rounded-lg border border-fluent-border bg-white p-6 shadow-sm">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Codigo *</label>
          <InputText v-model="form.code" required class="w-full" />
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="router.push('/curriculum/experience-fields')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
