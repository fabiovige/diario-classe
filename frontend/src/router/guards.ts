import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { RoleSlug } from '@/types/enums'

export function authGuard(
  to: RouteLocationNormalized,
  _from: RouteLocationNormalized,
  next: NavigationGuardNext,
) {
  const auth = useAuthStore()

  if (to.meta.requiresAuth === false) {
    if (auth.isAuthenticated && to.name === 'login') {
      return next('/dashboard')
    }
    return next()
  }

  if (!auth.isAuthenticated) {
    return next('/login')
  }

  return next()
}

export function roleGuard(
  to: RouteLocationNormalized,
  _from: RouteLocationNormalized,
  next: NavigationGuardNext,
) {
  const auth = useAuthStore()
  const requiredRoles = to.meta.roles as RoleSlug[] | undefined

  if (!requiredRoles || requiredRoles.length === 0) {
    return next()
  }

  if (!auth.roleSlug || !requiredRoles.includes(auth.roleSlug)) {
    return next('/dashboard')
  }

  return next()
}
