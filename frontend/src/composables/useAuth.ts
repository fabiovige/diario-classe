import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { apiPost, apiGet } from '@/config/api'
import type { LoginRequest, LoginResponse, User } from '@/types/auth'

export function useAuth() {
  const store = useAuthStore()
  const router = useRouter()

  async function login(credentials: LoginRequest): Promise<void> {
    const response = await apiPost<LoginResponse>('auth/login', credentials)
    store.setAuth(response.user, response.token)
  }

  async function logout(): Promise<void> {
    try {
      await apiPost('auth/logout')
    } finally {
      store.clearAuth()
      router.push('/login')
    }
  }

  async function fetchMe(): Promise<User> {
    const user = await apiGet<User>('auth/me')
    store.updateUser(user)
    return user
  }

  return {
    login,
    logout,
    fetchMe,
    isAuthenticated: store.isAuthenticated,
    user: store.user,
  }
}
