import { ref, computed } from 'vue'

export function usePagination(defaultPerPage = 15) {
  const currentPage = ref(1)
  const perPage = ref(defaultPerPage)
  const totalRecords = ref(0)

  const offset = computed(() => (currentPage.value - 1) * perPage.value)

  function onPageChange(event: { page: number; rows: number }) {
    currentPage.value = event.page + 1
    perPage.value = event.rows
  }

  function setMeta(meta: { current_page: number; per_page: number; total: number }) {
    currentPage.value = meta.current_page
    perPage.value = meta.per_page
    totalRecords.value = meta.total
  }

  function reset() {
    currentPage.value = 1
  }

  return {
    currentPage,
    perPage,
    totalRecords,
    offset,
    onPageChange,
    setMeta,
    reset,
  }
}
