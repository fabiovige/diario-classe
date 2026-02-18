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
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-fluent-primary">{{ title }}</h1>

    <div class="rounded-lg border border-fluent-border bg-fluent-surface p-6 shadow-sm">
      <Toolbar class="mb-4 border-none bg-transparent p-0">
        <template #start>
          <InputText
            v-if="showSearch"
            :placeholder="searchPlaceholder"
            class="w-[280px]"
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
        class="mb-4"
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
        class="border-t border-fluent-border pt-3"
      />
    </div>
  </div>
</template>
