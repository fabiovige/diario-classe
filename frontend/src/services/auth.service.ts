import { apiPost, apiGet } from '@/config/api'
import type { LoginRequest, LoginResponse, User } from '@/types/auth'

export const authService = {
  login(data: LoginRequest): Promise<LoginResponse> {
    return apiPost<LoginResponse>('auth/login', data)
  },

  logout(): Promise<void> {
    return apiPost('auth/logout')
  },

  me(): Promise<User> {
    return apiGet<User>('auth/me')
  },
}
