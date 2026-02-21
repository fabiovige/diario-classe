<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'
import { peopleService } from '@/services/people.service'
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
  social_name: '',
  birth_date: '',
  gender: '',
  race_color: '',
  cpf: '',
  has_disability: false,
})

const genderOptions = [
  { label: 'Masculino', value: 'masculino' },
  { label: 'Feminino', value: 'feminino' },
  { label: 'Outro', value: 'outro' },
]

const raceColorOptions = [
  { label: 'Branca', value: 'branca' },
  { label: 'Preta', value: 'preta' },
  { label: 'Parda', value: 'parda' },
  { label: 'Amarela', value: 'amarela' },
  { label: 'Indigena', value: 'indigena' },
  { label: 'Nao declarada', value: 'nao_declarada' },
]

async function loadStudent() {
  if (!id.value) return
  loading.value = true
  try {
    const student = await peopleService.getStudent(id.value)
    form.value.name = student.name
    form.value.social_name = student.social_name ?? ''
    form.value.birth_date = student.birth_date ?? ''
    form.value.gender = student.gender
    form.value.race_color = student.race_color
    form.value.cpf = student.cpf
    form.value.has_disability = student.has_disability
  } catch {
    toast.error('Erro ao carregar aluno')
    router.push('/people/students')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    const data: Record<string, unknown> = { ...form.value }
    if (!data.social_name) delete data.social_name

    if (isEdit.value) {
      await peopleService.updateStudent(id.value!, data as any)
      toast.success('Aluno atualizado')
    } else {
      await peopleService.createStudent(data as any)
      toast.success('Aluno criado')
    }
    router.push('/people/students')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao salvar aluno'))
  } finally {
    loading.value = false
  }
}

onMounted(loadStudent)
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">{{ isEdit ? 'Editar Aluno' : 'Novo Aluno' }}</h1>

    <div class="max-w-[700px] rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Nome Social</label>
          <InputText v-model="form.social_name" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Data de Nascimento *</label>
          <InputText v-model="form.birth_date" type="date" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Genero *</label>
          <Select v-model="form.gender" :options="genderOptions" optionLabel="label" optionValue="value" placeholder="Selecione" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Raca/Cor *</label>
          <Select v-model="form.race_color" :options="raceColorOptions" optionLabel="label" optionValue="value" placeholder="Selecione" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">CPF</label>
          <InputText v-model="form.cpf" class="w-full" />
        </div>
        <div class="flex items-center gap-2">
          <Checkbox v-model="form.has_disability" :binary="true" inputId="has_disability" />
          <label for="has_disability">Possui deficiencia</label>
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="router.push('/people/students')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
