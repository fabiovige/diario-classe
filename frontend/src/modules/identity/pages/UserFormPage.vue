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
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">{{ isEdit ? 'Editar Usuario' : 'Novo Usuario' }}</h1>

    <div class="max-w-[700px] rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">E-mail *</label>
          <InputText v-model="form.email" type="email" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">CPF *</label>
          <InputText v-model="form.cpf" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">{{ isEdit ? 'Nova Senha' : 'Senha *' }}</label>
          <Password v-model="form.password" :feedback="false" toggleMask class="w-full" inputClass="w-full" :required="!isEdit" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Perfil *</label>
          <Select v-model="form.role_id" :options="roles" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <Select v-model="form.school_id" :options="schools" optionLabel="name" optionValue="id" placeholder="Selecione" showClear class="w-full" />
        </div>
        <div v-if="isEdit" class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Status</label>
          <Select v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full" />
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="router.push('/identity/users')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
