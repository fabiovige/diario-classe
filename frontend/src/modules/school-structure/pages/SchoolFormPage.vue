<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'
import { schoolStructureService } from '@/services/school-structure.service'
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
  inep_code: '',
  type: '',
  address: '',
  phone: '',
  email: '',
  active: true,
})

async function loadSchool() {
  if (!id.value) return
  loading.value = true
  try {
    const school = await schoolStructureService.getSchool(id.value)
    form.value.name = school.name
    form.value.inep_code = school.inep_code
    form.value.type = school.type
    form.value.address = school.address
    form.value.phone = school.phone
    form.value.email = school.email
    form.value.active = school.active
  } catch {
    toast.error('Erro ao carregar escola')
    router.push('/school-structure/schools')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    if (isEdit.value) {
      await schoolStructureService.updateSchool(id.value!, form.value)
      toast.success('Escola atualizada')
    } else {
      await schoolStructureService.createSchool(form.value)
      toast.success('Escola criada')
    }
    router.push('/school-structure/schools')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao salvar escola'))
  } finally {
    loading.value = false
  }
}

onMounted(loadSchool)
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">{{ isEdit ? 'Editar Escola' : 'Nova Escola' }}</h1>

    <div class="max-w-[700px] rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Codigo INEP *</label>
          <InputText v-model="form.inep_code" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Tipo *</label>
          <InputText v-model="form.type" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Endereco</label>
          <InputText v-model="form.address" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Telefone</label>
          <InputText v-model="form.phone" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">E-mail</label>
          <InputText v-model="form.email" type="email" class="w-full" />
        </div>
        <div class="flex items-center gap-2">
          <Checkbox v-model="form.active" :binary="true" inputId="active" />
          <label for="active">Ativa</label>
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="router.push('/school-structure/schools')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
