import { apiGet, apiPost, apiPut, apiDelete } from '@/config/api'

export function useApi() {
  return {
    get: apiGet,
    post: apiPost,
    put: apiPut,
    del: apiDelete,
  }
}
