<script setup lang="ts">
import { computed } from 'vue'
import AppMenuItem from './AppMenuItem.vue'
import { useAuthStore } from '@/stores/auth'
import { getMenuByRole } from '@/config/permissions'

const authStore = useAuthStore()

const model = computed(() => {
  if (!authStore.roleSlug) return []
  return getMenuByRole(authStore.roleSlug)
})
</script>

<template>
  <ul class="layout-menu">
    <template v-for="(item, i) in model" :key="item.label || i">
      <li v-if="(item as any).separator" class="menu-separator" />
      <AppMenuItem v-else :item="item" :root="true" />
    </template>
  </ul>
</template>
