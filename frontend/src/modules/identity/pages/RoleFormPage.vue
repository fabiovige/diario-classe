<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'
import { identityService } from '@/services/identity.service'
import { useToast } from '@/composables/useToast'
import { extractApiError } from '@/shared/utils/api-error'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const id = computed(() => route.params.id ? Number(route.params.id) : null)
const isEdit = computed(() => !!id.value)
const loading = ref(false)

const permissionGroups = [
  {
    label: 'Escolas',
    permissions: [
      { value: 'schools.view', label: 'Visualizar' },
      { value: 'schools.manage', label: 'Gerenciar' },
    ],
  },
  {
    label: 'Usuarios',
    permissions: [
      { value: 'users.view', label: 'Visualizar' },
      { value: 'users.manage', label: 'Gerenciar' },
    ],
  },
  {
    label: 'Alunos',
    permissions: [
      { value: 'students.view', label: 'Visualizar' },
      { value: 'students.manage', label: 'Gerenciar' },
    ],
  },
  {
    label: 'Responsaveis',
    permissions: [
      { value: 'guardians.view', label: 'Visualizar' },
      { value: 'guardians.manage', label: 'Gerenciar' },
    ],
  },
  {
    label: 'Professores',
    permissions: [
      { value: 'teachers.view', label: 'Visualizar' },
      { value: 'teachers.manage', label: 'Gerenciar' },
    ],
  },
  {
    label: 'Matriculas',
    permissions: [
      { value: 'enrollments.view', label: 'Visualizar' },
      { value: 'enrollments.manage', label: 'Gerenciar' },
    ],
  },
  {
    label: 'Turmas',
    permissions: [
      { value: 'class_groups.view', label: 'Visualizar' },
      { value: 'class_groups.manage', label: 'Gerenciar' },
    ],
  },
  {
    label: 'Frequencia',
    permissions: [
      { value: 'attendance.view', label: 'Visualizar' },
      { value: 'attendance.manage', label: 'Gerenciar' },
    ],
  },
  {
    label: 'Notas',
    permissions: [
      { value: 'grades.view', label: 'Visualizar' },
      { value: 'grades.manage', label: 'Gerenciar' },
    ],
  },
  {
    label: 'Diario de Classe',
    permissions: [
      { value: 'class_record.view', label: 'Visualizar' },
      { value: 'class_record.manage', label: 'Gerenciar' },
    ],
  },
  {
    label: 'Relatorios',
    permissions: [
      { value: 'reports.view', label: 'Visualizar' },
      { value: 'reports.generate', label: 'Gerar' },
    ],
  },
]

const form = ref({
  name: '',
  slug: '',
  permissions: [] as string[],
})

async function loadRole() {
  if (!id.value) return
  loading.value = true
  try {
    const role = await identityService.getRole(id.value)
    form.value.name = role.name
    form.value.slug = role.slug
    form.value.permissions = role.permissions ?? []
  } catch {
    toast.error('Erro ao carregar perfil')
    router.push('/identity/roles')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    if (isEdit.value) {
      await identityService.updateRole(id.value!, {
        name: form.value.name,
        permissions: form.value.permissions,
      })
      toast.success('Perfil atualizado')
    } else {
      await identityService.createRole(form.value)
      toast.success('Perfil criado')
    }
    router.push('/identity/roles')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao salvar perfil'))
  } finally {
    loading.value = false
  }
}

onMounted(loadRole)
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-fluent-primary">{{ isEdit ? 'Editar Perfil' : 'Novo Perfil' }}</h1>

    <div class="max-w-175 rounded-lg border border-fluent-border bg-white p-6 shadow-sm">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
        </div>
        <div v-if="!isEdit" class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Slug *</label>
          <InputText v-model="form.slug" required class="w-full" />
        </div>

        <div class="flex flex-col gap-2">
          <label class="text-[0.8125rem] font-medium">Permissoes</label>
          <div class="grid grid-cols-2 gap-4 rounded-md border border-fluent-border p-4">
            <div v-for="group in permissionGroups" :key="group.label" class="flex flex-col gap-1.5">
              <span class="text-xs font-semibold text-gray-500 uppercase">{{ group.label }}</span>
              <div v-for="perm in group.permissions" :key="perm.value" class="flex items-center gap-2">
                <Checkbox v-model="form.permissions" :inputId="perm.value" :value="perm.value" />
                <label :for="perm.value" class="cursor-pointer text-sm">{{ perm.label }}</label>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="router.push('/identity/roles')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
