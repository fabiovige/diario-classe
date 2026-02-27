import { useRoute, useRouter } from 'vue-router'
import type { Ref } from 'vue'
import type { LocationQueryValue } from 'vue-router'

interface FilterDef {
  key: string
  ref: Ref<number | string | null>
  type: 'number' | 'string'
}

export function useListFilters(filters: FilterDef[]) {
  const route = useRoute()
  const router = useRouter()

  function initFromQuery() {
    for (const f of filters) {
      const raw = route.query[f.key] as LocationQueryValue
      if (raw === null || raw === undefined) continue
      f.ref.value = f.type === 'number' ? Number(raw) : raw
    }
  }

  function syncToUrl() {
    const query: Record<string, string> = {}
    for (const f of filters) {
      const v = f.ref.value
      if (v !== null && v !== undefined && v !== '') {
        query[f.key] = String(v)
      }
    }
    router.replace({ query })
  }

  function clearAll() {
    for (const f of filters) {
      f.ref.value = f.type === 'number' ? null : ''
    }
    router.replace({ query: {} })
  }

  return { initFromQuery, syncToUrl, clearAll }
}
