<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import { peopleService } from '@/services/people.service'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const id = computed(() => route.params.id ? Number(route.params.id) : null)
const isEdit = computed(() => !!id.value)
const loading = ref(false)

const form = ref({
  name: '',
  cpf: '',
  phone: '',
  email: '',
  address: '',
  occupation: '',
})

async function loadGuardian() {
  if (!id.value) return
  loading.value = true
  try {
    const guardian = await peopleService.getGuardian(id.value)
    form.value.name = guardian.name
    form.value.cpf = guardian.cpf
    form.value.phone = guardian.phone
    form.value.email = guardian.email
    form.value.address = guardian.address
    form.value.occupation = guardian.occupation
  } catch {
    toast.error('Erro ao carregar responsavel')
    router.push('/people/guardians')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    if (isEdit.value) {
      await peopleService.updateGuardian(id.value!, form.value)
      toast.success('Responsavel atualizado')
    } else {
      await peopleService.createGuardian(form.value)
      toast.success('Responsavel criado')
    }
    router.push('/people/guardians')
  } catch (error: any) {
    toast.error(error.response?.data?.error ?? 'Erro ao salvar responsavel')
  } finally {
    loading.value = false
  }
}

onMounted(loadGuardian)
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">{{ isEdit ? 'Editar Responsavel' : 'Novo Responsavel' }}</h1>

    <div class="max-w-[700px] rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">CPF *</label>
          <InputText v-model="form.cpf" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Telefone</label>
          <InputText v-model="form.phone" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">E-mail</label>
          <InputText v-model="form.email" type="email" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Endereco</label>
          <InputText v-model="form.address" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Profissao</label>
          <InputText v-model="form.occupation" class="w-full" />
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="router.push('/people/guardians')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
