import { useAuthStore } from '@/stores/auth'
import type { RoleSlug } from '@/types/enums'

export function usePermissions() {
  const auth = useAuthStore()

  function hasPermission(permission: string): boolean {
    return auth.hasPermission(permission)
  }

  function hasRole(...roles: RoleSlug[]): boolean {
    return auth.hasRole(...roles)
  }

  function isAdmin(): boolean {
    return auth.hasRole('admin')
  }

  function canEdit(): boolean {
    return auth.hasRole('admin', 'secretary')
  }

  function canManageSchool(): boolean {
    return auth.hasRole('admin', 'director')
  }

  return { hasPermission, hasRole, isAdmin, canEdit, canManageSchool }
}
