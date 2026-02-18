import { ref, watch } from 'vue'

export function useFilters<T extends Record<string, unknown>>(
  defaults: T,
  onChange: () => void,
) {
  const filters = ref<T>({ ...defaults }) as { value: T }

  function setFilter<K extends keyof T>(key: K, value: T[K]) {
    filters.value[key] = value
  }

  function resetFilters() {
    filters.value = { ...defaults }
  }

  function toParams(): Record<string, unknown> {
    const params: Record<string, unknown> = {}
    for (const [key, value] of Object.entries(filters.value)) {
      if (value !== null && value !== undefined && value !== '') {
        params[key] = value
      }
    }
    return params
  }

  watch(filters, onChange, { deep: true })

  return { filters, setFilter, resetFilters, toParams }
}
