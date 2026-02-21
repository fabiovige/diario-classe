import { ref } from 'vue'
import { apiGet, apiPost, apiPut, apiDelete } from '@/config/api'
import type { PaginatedData } from '@/types/api'
import { useToast } from './useToast'
import { extractApiError } from '@/shared/utils/api-error'

interface CrudOptions {
  baseUrl: string
  resourceName: string
}

export function useCrud<T extends { id: number }>(options: CrudOptions) {
  const items = ref<T[]>([]) as { value: T[] }
  const selectedItem = ref<T | null>(null) as { value: T | null }
  const loading = ref(false)
  const totalRecords = ref(0)
  const currentPage = ref(1)
  const perPage = ref(15)
  const toast = useToast()

  async function fetchAll(params?: Record<string, unknown>) {
    loading.value = true
    try {
      const queryParams = {
        page: currentPage.value,
        per_page: perPage.value,
        ...params,
      }
      const response = await apiGet<PaginatedData<T>>(options.baseUrl, queryParams)
      items.value = response.data
      totalRecords.value = response.meta.total
      currentPage.value = response.meta.current_page
      perPage.value = response.meta.per_page
    } catch {
      toast.error(`Erro ao carregar ${options.resourceName}`)
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id: number): Promise<T | null> {
    loading.value = true
    try {
      return await apiGet<T>(`${options.baseUrl}/${id}`)
    } catch {
      toast.error(`Erro ao carregar ${options.resourceName}`)
      return null
    } finally {
      loading.value = false
    }
  }

  async function create(data: Partial<T>): Promise<T | null> {
    loading.value = true
    try {
      const result = await apiPost<T>(options.baseUrl, data)
      toast.success(`${options.resourceName} criado com sucesso`)
      return result
    } catch (error: unknown) {
      const message = extractApiError(error, `Erro ao criar ${options.resourceName}`)
      toast.error(message)
      return null
    } finally {
      loading.value = false
    }
  }

  async function update(id: number, data: Partial<T>): Promise<T | null> {
    loading.value = true
    try {
      const result = await apiPut<T>(`${options.baseUrl}/${id}`, data)
      toast.success(`${options.resourceName} atualizado com sucesso`)
      return result
    } catch (error: unknown) {
      const message = extractApiError(error, `Erro ao atualizar ${options.resourceName}`)
      toast.error(message)
      return null
    } finally {
      loading.value = false
    }
  }

  async function remove(id: number): Promise<boolean> {
    loading.value = true
    try {
      await apiDelete(`${options.baseUrl}/${id}`)
      toast.success(`${options.resourceName} excluido com sucesso`)
      return true
    } catch {
      toast.error(`Erro ao excluir ${options.resourceName}`)
      return false
    } finally {
      loading.value = false
    }
  }

  function onPageChange(event: { page: number; rows: number }) {
    currentPage.value = event.page + 1
    perPage.value = event.rows
  }

  return {
    items,
    selectedItem,
    loading,
    totalRecords,
    currentPage,
    perPage,
    fetchAll,
    fetchOne,
    create,
    update,
    remove,
    onPageChange,
  }
}
