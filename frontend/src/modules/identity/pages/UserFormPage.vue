<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Password from 'primevue/password'
import Button from 'primevue/button'
import { identityService } from '@/services/identity.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import type { Role } from '@/types/auth'
import type { School } from '@/types/school-structure'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const id = computed(() => route.params.id ? Number(route.params.id) : null)
const isEdit = computed(() => !!id.value)
const loading = ref(false)

const form = ref({
  name: '',
  email: '',
  cpf: '',
  password: '',
  role_id: null as number | null,
  school_id: null as number | null,
  status: 'active',
})

const roles = ref<Role[]>([])
const schools = ref<School[]>([])

const statusOptions = [
  { label: 'Ativo', value: 'active' },
  { label: 'Inativo', value: 'inactive' },
  { label: 'Bloqueado', value: 'blocked' },
]

async function loadAuxData() {
  try {
    const [rolesRes, schoolsRes] = await Promise.all([
      identityService.getRoles({ per_page: 100 }),
      schoolStructureService.getSchools({ per_page: 100 }),
    ])
    roles.value = rolesRes.data
    schools.value = schoolsRes.data
  } catch {
    toast.error('Erro ao carregar dados auxiliares')
  }
}

async function loadUser() {
  if (!id.value) return
  loading.value = true
  try {
    const user = await identityService.getUser(id.value)
    form.value.name = user.name
    form.value.email = user.email
    form.value.cpf = user.cpf
    form.value.role_id = user.role?.id ?? null
    form.value.school_id = user.school_id
    form.value.status = user.status
  } catch {
    toast.error('Erro ao carregar usuario')
    router.push('/identity/users')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    const data: Record<string, unknown> = { ...form.value }
    if (!data.password) delete data.password
    if (!data.school_id) delete data.school_id

    if (isEdit.value) {
      await identityService.updateUser(id.value!, data as any)
      toast.success('Usuario atualizado')
    } else {
      await identityService.createUser(data as any)
      toast.success('Usuario criado')
    }
    router.push('/identity/users')
  } catch (error: any) {
    toast.error(error.response?.data?.error ?? 'Erro ao salvar usuario')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadAuxData()
  await loadUser()
})
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">{{ isEdit ? 'Editar Usuario' : 'Novo Usuario' }}</h1>

    <div class="card-section form-card">
      <form @submit.prevent="handleSubmit" class="form-grid">
        <div class="field">
          <label>Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
        </div>
        <div class="field">
          <label>E-mail *</label>
          <InputText v-model="form.email" type="email" required class="w-full" />
        </div>
        <div class="field">
          <label>CPF *</label>
          <InputText v-model="form.cpf" required class="w-full" />
        </div>
        <div class="field">
          <label>{{ isEdit ? 'Nova Senha' : 'Senha *' }}</label>
          <Password v-model="form.password" :feedback="false" toggleMask class="w-full" inputClass="w-full" :required="!isEdit" />
        </div>
        <div class="field">
          <label>Perfil *</label>
          <Select v-model="form.role_id" :options="roles" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="field">
          <label>Escola</label>
          <Select v-model="form.school_id" :options="schools" optionLabel="name" optionValue="id" placeholder="Selecione" showClear class="w-full" />
        </div>
        <div v-if="isEdit" class="field">
          <label>Status</label>
          <Select v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full" />
        </div>

        <div class="form-actions">
          <Button label="Cancelar" severity="secondary" @click="router.push('/identity/users')" />
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
