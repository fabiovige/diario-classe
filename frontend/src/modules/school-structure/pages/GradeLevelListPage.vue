<script setup lang="ts">
import { onMounted, ref } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import EmptyState from '@/shared/components/EmptyState.vue'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { educationLevelLabel } from '@/shared/utils/enum-labels'
import type { GradeLevel } from '@/types/school-structure'

const toast = useToast()

const items = ref<GradeLevel[]>([])
const loading = ref(false)

async function loadData() {
  loading.value = true
  try {
    items.value = await schoolStructureService.getGradeLevels()
  } catch {
    toast.error('Erro ao carregar niveis de ensino')
  } finally {
    loading.value = false
  }
}

onMounted(loadData)
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Niveis de Ensino</h1>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <EmptyState v-if="!loading && items.length === 0" message="Nenhum nivel de ensino encontrado" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column field="name" header="Nome" sortable />
        <Column header="Tipo">
          <template #body="{ data }">
            {{ educationLevelLabel(data.type) }}
          </template>
        </Column>
        <Column field="order" header="Ordem" sortable />
      </DataTable>
    </div>
  </div>
</template>
