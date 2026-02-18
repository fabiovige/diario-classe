<script setup lang="ts">
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AdminDashboard from './AdminDashboard.vue'
import DirectorDashboard from './DirectorDashboard.vue'
import CoordinatorDashboard from './CoordinatorDashboard.vue'
import TeacherDashboard from './TeacherDashboard.vue'
import SecretaryDashboard from './SecretaryDashboard.vue'

const authStore = useAuthStore()

const dashboardComponent = computed(() => {
  const map: Record<string, any> = {
    admin: AdminDashboard,
    director: DirectorDashboard,
    coordinator: CoordinatorDashboard,
    teacher: TeacherDashboard,
    secretary: SecretaryDashboard,
  }
  return map[authStore.roleSlug ?? ''] ?? TeacherDashboard
})
</script>

<template>
  <component :is="dashboardComponent" />
</template>
