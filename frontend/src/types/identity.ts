import type { Role, User } from './auth'

export type { Role, User }

export interface CreateUserRequest {
  name: string
  email: string
  cpf: string
  password: string
  role_id: number
  school_id?: number | null
}

export interface UpdateUserRequest {
  name?: string
  email?: string
  cpf?: string
  password?: string
  status?: string
  role_id?: number
  school_id?: number | null
}

export interface CreateRoleRequest {
  name: string
  slug: string
  permissions: string[]
}

export interface UpdateRoleRequest {
  name?: string
  permissions?: string[]
}
