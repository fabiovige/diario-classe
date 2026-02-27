<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Paginator from 'primevue/paginator'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import { identityService } from '@/services/identity.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import { useSchoolScope } from '@/composables/useSchoolScope'
import { userStatusLabel, roleLabel } from '@/shared/utils/enum-labels'
import type { User, Role } from '@/types/auth'
import type { School } from '@/types/school-structure'

const router = useRouter()
const toast = useToast()
const { confirmDelete } = useConfirm()
const { shouldShowSchoolFilter } = useSchoolScope()

const items = ref<User[]>([])
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(15)
const search = ref('')

const roles = ref<Role[]>([])
const schools = ref<School[]>([])
const selectedRoleId = ref<number | null>(null)
const selectedSchoolId = ref<number | null>(null)
const selectedStatus = ref<string | null>(null)

const statusOptions = [
  { label: 'Ativo', value: 'active' },
  { label: 'Inativo', value: 'inactive' },
  { label: 'Bloqueado', value: 'blocked' },
]

async function loadFilters() {
  try {
    const [rolesRes, schoolsRes] = await Promise.all([
      identityService.getRoles({ per_page: 50 }),
      shouldShowSchoolFilter.value ? schoolStructureService.getSchools({ per_page: 200 }) : Promise.resolve(null),
    ])
    roles.value = rolesRes.data
    if (schoolsRes) schools.value = schoolsRes.data
  } catch {
    toast.error('Erro ao carregar filtros')
  }
}

async function loadData() {
  loading.value = true
  try {
    const params: Record<string, unknown> = { page: currentPage.value, per_page: perPage.value }
    if (search.value) params.search = search.value
    if (selectedRoleId.value) params.role_id = selectedRoleId.value
    if (selectedSchoolId.value) params.school_id = selectedSchoolId.value
    if (selectedStatus.value) params.status = selectedStatus.value
    const response = await identityService.getUsers(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar usuarios')
  } finally {
    loading.value = false
  }
}

watch([selectedRoleId, selectedSchoolId, selectedStatus], () => {
  currentPage.value = 1
  loadData()
})

function onPageChange(event: { page: number; rows: number }) {
  currentPage.value = event.page + 1
  perPage.value = event.rows
  loadData()
}

function onSearch() {
  currentPage.value = 1
  loadData()
}

function handleDelete(user: User) {
  confirmDelete(async () => {
    try {
      await identityService.deleteUser(user.id)
      toast.success('Usuario excluido')
      loadData()
    } catch {
      toast.error('Erro ao excluir usuario')
    }
  })
}

onMounted(() => {
  loadFilters()
  loadData()
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Usuarios</h1>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <div class="mb-4 flex flex-wrap items-end gap-4">
        <div v-if="shouldShowSchoolFilter" class="flex flex-col gap-1.5 w-64">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <Select v-model="selectedSchoolId" :options="schools" optionLabel="name" optionValue="id" placeholder="Todas" class="w-full" showClear filter />
        </div>
        <div class="flex flex-col gap-1.5 w-44">
          <label class="text-[0.8125rem] font-medium">Perfil</label>
          <Select v-model="selectedRoleId" :options="roles" optionLabel="name" optionValue="id" placeholder="Todos" class="w-full" showClear />
        </div>
        <div class="flex flex-col gap-1.5 w-40">
          <label class="text-[0.8125rem] font-medium">Status</label>
          <Select v-model="selectedStatus" :options="statusOptions" optionLabel="label" optionValue="value" placeholder="Todos" class="w-full" showClear />
        </div>
        <div class="flex flex-col gap-1.5">
          <InputText v-model="search" placeholder="Nome ou e-mail..." @keyup.enter="onSearch" />
        </div>
        <Button icon="pi pi-search" @click="onSearch" />
        <div class="ml-auto">
          <Button label="Novo Usuario" icon="pi pi-plus" @click="router.push('/identity/users/new')" />
        </div>
      </div>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhum usuario encontrado" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column field="name" header="Nome" sortable />
        <Column field="email" header="E-mail" sortable />
        <Column header="Perfil">
          <template #body="{ data }">
            {{ data.role ? roleLabel(data.role.slug) : '--' }}
          </template>
        </Column>
        <Column v-if="shouldShowSchoolFilter" header="Escola">
          <template #body="{ data }">
            {{ data.school_name ?? '--' }}
          </template>
        </Column>
        <Column header="Status">
          <template #body="{ data }">
            <StatusBadge :status="data.status" :label="userStatusLabel(data.status)" />
          </template>
        </Column>
        <Column header="Acoes" :style="{ width: '120px' }">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" text rounded class="mr-1" @click="router.push(`/identity/users/${data.id}/edit`)" />
            <Button icon="pi pi-trash" text rounded severity="danger" @click="handleDelete(data)" />
          </template>
        </Column>
      </DataTable>

      <Paginator
        v-if="totalRecords > perPage"
        :rows="perPage"
        :totalRecords="totalRecords"
        :first="(currentPage - 1) * perPage"
        :rowsPerPageOptions="[10, 15, 25, 50]"
        @page="onPageChange"
        class="mt-4 border-t border-[#E0E0E0] pt-3"
      />
    </div>
  </div>
</template>
