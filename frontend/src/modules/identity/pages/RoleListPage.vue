<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import EmptyState from '@/shared/components/EmptyState.vue'
import { identityService } from '@/services/identity.service'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import { roleLabel } from '@/shared/utils/enum-labels'
import type { Role } from '@/types/auth'

const router = useRouter()
const toast = useToast()
const { confirmDelete } = useConfirm()

const items = ref<Role[]>([])
const loading = ref(false)

async function loadData() {
  loading.value = true
  try {
    const response = await identityService.getRoles({ per_page: 100 })
    items.value = response.data
  } catch {
    toast.error('Erro ao carregar perfis')
  } finally {
    loading.value = false
  }
}

function handleDelete(role: Role) {
  confirmDelete(async () => {
    try {
      await identityService.deleteRole(role.id)
      toast.success('Perfil excluido')
      loadData()
    } catch {
      toast.error('Erro ao excluir perfil')
    }
  })
}

onMounted(loadData)
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">Perfis</h1>

    <div class="card-section">
      <div class="toolbar-end mb-3">
        <Button label="Novo Perfil" icon="pi pi-plus" @click="router.push('/identity/roles/new')" />
      </div>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhum perfil encontrado" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column field="name" header="Nome" sortable />
        <Column header="Slug">
          <template #body="{ data }">
            <Tag :value="roleLabel(data.slug)" />
          </template>
        </Column>
        <Column header="Permissoes">
          <template #body="{ data }">
            {{ data.permissions?.length ?? 0 }} permissoes
          </template>
        </Column>
        <Column header="Acoes" :style="{ width: '120px' }">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" text rounded class="mr-1" @click="router.push(`/identity/roles/${data.id}/edit`)" />
            <Button icon="pi pi-trash" text rounded severity="danger" @click="handleDelete(data)" />
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
</template>

<style scoped>
.mb-3 { margin-bottom: 1rem; }
.mr-1 { margin-right: 0.25rem; }
.toolbar-end { display: flex; justify-content: flex-end; }
</style>
