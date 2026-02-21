import { apiGet, apiPost, apiPut, apiDelete } from '@/config/api'
import type { User, Role } from '@/types/auth'
import type { PaginatedData } from '@/types/api'
import type { CreateUserRequest, UpdateUserRequest, CreateRoleRequest, UpdateRoleRequest } from '@/types/identity'

export const identityService = {
  getUsers(params?: Record<string, unknown>): Promise<PaginatedData<User>> {
    return apiGet<PaginatedData<User>>('users', params)
  },
  getUser(id: number): Promise<User> {
    return apiGet<User>(`users/${id}`)
  },
  createUser(data: CreateUserRequest): Promise<User> {
    return apiPost<User>('users', data)
  },
  updateUser(id: number, data: UpdateUserRequest): Promise<User> {
    return apiPut<User>(`users/${id}`, data)
  },
  deleteUser(id: number): Promise<void> {
    return apiDelete(`users/${id}`)
  },
  getRoles(): Promise<Role[]> {
    return apiGet<Role[]>('roles')
  },
  getRole(id: number): Promise<Role> {
    return apiGet<Role>(`roles/${id}`)
  },
  createRole(data: CreateRoleRequest): Promise<Role> {
    return apiPost<Role>('roles', data)
  },
  updateRole(id: number, data: UpdateRoleRequest): Promise<Role> {
    return apiPut<Role>(`roles/${id}`, data)
  },
  deleteRole(id: number): Promise<void> {
    return apiDelete(`roles/${id}`)
  },
}
