<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'

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
  } catch (error: any) {
    toast.error(error.response?.data?.error ?? 'Erro ao salvar escola')
  } finally {
    loading.value = false
  }
}

onMounted(loadSchool)
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">{{ isEdit ? 'Editar Escola' : 'Nova Escola' }}</h1>

    <div class="card-section form-card">
      <form @submit.prevent="handleSubmit" class="form-grid">
        <div class="field">
          <label>Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
        </div>
        <div class="field">
          <label>Codigo INEP *</label>
          <InputText v-model="form.inep_code" required class="w-full" />
        </div>
        <div class="field">
          <label>Tipo *</label>
          <InputText v-model="form.type" required class="w-full" />
        </div>
        <div class="field">
          <label>Endereco</label>
          <InputText v-model="form.address" class="w-full" />
        </div>
        <div class="field">
          <label>Telefone</label>
          <InputText v-model="form.phone" class="w-full" />
        </div>
        <div class="field">
          <label>E-mail</label>
          <InputText v-model="form.email" type="email" class="w-full" />
        </div>
        <div class="field-check">
          <Checkbox v-model="form.active" :binary="true" inputId="active" />
          <label for="active">Ativa</label>
        </div>

        <div class="form-actions">
          <Button label="Cancelar" severity="secondary" @click="router.push('/school-structure/schools')" />
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
.field-check { display: flex; align-items: center; gap: 0.5rem; }
.w-full { width: 100%; }
.form-actions { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1rem; }
</style>
