<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Chips from 'primevue/chips'
import { identityService } from '@/services/identity.service'
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
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">{{ isEdit ? 'Editar Perfil' : 'Novo Perfil' }}</h1>

    <div class="max-w-[600px] rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
        </div>
        <div v-if="!isEdit" class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Slug *</label>
          <InputText v-model="form.slug" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Permissoes</label>
          <Chips v-model="form.permissions" placeholder="Digite e pressione Enter" class="w-full" />
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="router.push('/identity/roles')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
