<script setup lang="ts">
import { computed } from 'vue'
import DataTable from 'primevue/datatable'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Paginator from 'primevue/paginator'
import Toolbar from 'primevue/toolbar'
import EmptyState from './EmptyState.vue'

interface Props {
  title: string
  items: any[]
  loading?: boolean
  totalRecords?: number
  perPage?: number
  currentPage?: number
  searchPlaceholder?: string
  createLabel?: string
  createRoute?: string
  showSearch?: boolean
  showCreate?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  totalRecords: 0,
  perPage: 15,
  currentPage: 1,
  searchPlaceholder: 'Buscar...',
  createLabel: 'Novo',
  showSearch: true,
  showCreate: true,
})

const emit = defineEmits<{
  search: [query: string]
  pageChange: [event: { page: number; rows: number }]
  create: []
}>()

const isEmpty = computed(() => !props.loading && props.items.length === 0)
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">{{ title }}</h1>

    <div class="card-section">
      <Toolbar class="data-toolbar">
        <template #start>
          <InputText
            v-if="showSearch"
            :placeholder="searchPlaceholder"
            class="toolbar-search"
            @input="emit('search', ($event.target as HTMLInputElement).value)"
          />
        </template>
        <template #end>
          <slot name="toolbar-end">
            <Button
              v-if="showCreate"
              :label="createLabel"
              icon="pi pi-plus"
              @click="createRoute ? $router.push(createRoute) : emit('create')"
            />
          </slot>
        </template>
      </Toolbar>

      <EmptyState v-if="isEmpty" message="Nenhum registro encontrado" />

      <DataTable
        v-if="!isEmpty"
        :value="items"
        :loading="loading"
        stripedRows
        responsiveLayout="scroll"
        class="data-table"
      >
        <slot />
      </DataTable>

      <Paginator
        v-if="totalRecords > perPage"
        :rows="perPage"
        :totalRecords="totalRecords"
        :first="(currentPage - 1) * perPage"
        :rowsPerPageOptions="[10, 15, 25, 50]"
        @page="emit('pageChange', $event)"
        class="data-paginator"
      />
    </div>
  </div>
</template>

<style scoped>
.data-toolbar {
  margin-bottom: 1rem;
  border: none;
  padding: 0;
  background: transparent;
}

.toolbar-search {
  width: 280px;
}

.data-table {
  margin-bottom: 1rem;
}

.data-paginator {
  border-top: 1px solid #e2e8f0;
  padding-top: 0.75rem;
}
</style>
