import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User } from '@/types/auth'
import type { RoleSlug } from '@/types/enums'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(loadUser())
  const token = ref<string | null>(localStorage.getItem('token'))

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const roleSlug = computed<RoleSlug | null>(() => user.value?.role?.slug ?? null)
  const permissions = computed<string[]>(() => user.value?.role?.permissions ?? [])
  const userName = computed(() => user.value?.name ?? '')
  const userSchoolId = computed(() => user.value?.school_id ?? null)
  const userSchoolName = computed(() => user.value?.school_name ?? null)

  function setAuth(newUser: User, newToken: string) {
    user.value = newUser
    token.value = newToken
    localStorage.setItem('token', newToken)
    localStorage.setItem('user', JSON.stringify(newUser))
  }

  function clearAuth() {
    user.value = null
    token.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  function updateUser(newUser: User) {
    user.value = newUser
    localStorage.setItem('user', JSON.stringify(newUser))
  }

  function hasPermission(permission: string): boolean {
    return permissions.value.includes(permission)
  }

  function hasRole(...roles: RoleSlug[]): boolean {
    if (!roleSlug.value) return false
    return roles.includes(roleSlug.value)
  }

  return {
    user,
    token,
    isAuthenticated,
    roleSlug,
    permissions,
    userName,
    userSchoolId,
    userSchoolName,
    setAuth,
    clearAuth,
    updateUser,
    hasPermission,
    hasRole,
  }
})

function loadUser(): User | null {
  const raw = localStorage.getItem('user')
  if (!raw) return null
  try {
    return JSON.parse(raw) as User
  } catch {
    return null
  }
}
