import type { RoleSlug, UserStatus } from './enums'

export interface Role {
  id: number
  name: string
  slug: RoleSlug
  permissions: string[]
}

export interface User {
  id: number
  name: string
  email: string
  cpf: string
  status: UserStatus
  school_id: number | null
  role: Role
  created_at: string
  updated_at: string
}

export interface LoginRequest {
  email: string
  password: string
}

export interface LoginResponse {
  user: User
  token: string
}
