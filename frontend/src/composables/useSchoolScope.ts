import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

export function useSchoolScope() {
  const store = useAuthStore()

  const isAdmin = computed(() => store.roleSlug === 'admin')
  const userSchoolId = computed(() => store.userSchoolId)
  const userSchoolName = computed(() => store.userSchoolName)
  const shouldShowSchoolFilter = computed(() => isAdmin.value)

  return {
    isAdmin,
    userSchoolId,
    userSchoolName,
    shouldShowSchoolFilter,
  }
}
