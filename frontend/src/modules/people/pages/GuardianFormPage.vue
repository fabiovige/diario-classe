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
  <div class="page-container">
    <h1 class="page-title">{{ isEdit ? 'Editar Responsavel' : 'Novo Responsavel' }}</h1>

    <div class="card-section form-card">
      <form @submit.prevent="handleSubmit" class="form-grid">
        <div class="field">
          <label>Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
        </div>
        <div class="field">
          <label>CPF *</label>
          <InputText v-model="form.cpf" required class="w-full" />
        </div>
        <div class="field">
          <label>Telefone</label>
          <InputText v-model="form.phone" class="w-full" />
        </div>
        <div class="field">
          <label>E-mail</label>
          <InputText v-model="form.email" type="email" class="w-full" />
        </div>
        <div class="field">
          <label>Endereco</label>
          <InputText v-model="form.address" class="w-full" />
        </div>
        <div class="field">
          <label>Profissao</label>
          <InputText v-model="form.occupation" class="w-full" />
        </div>

        <div class="form-actions">
          <Button label="Cancelar" severity="secondary" @click="router.push('/people/guardians')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.form-card { max-width: 700px; }
.form-grid { display: flex; flex-direction: column; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.375rem; }
.field label { font-size: 0.8125rem; font-weight: 500; }
.w-full { width: 100%; }
.form-actions { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1rem; }
</style>
