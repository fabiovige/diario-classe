<script setup lang="ts">
import { computed } from 'vue'
import { useAppStore } from '@/stores/app'
import AppSidebar from './components/AppSidebar.vue'
import AppHeader from './components/AppHeader.vue'
import AppFooter from './components/AppFooter.vue'

const appStore = useAppStore()

const contentMargin = computed(() =>
  appStore.sidebarCollapsed ? 'var(--sidebar-collapsed-width)' : 'var(--sidebar-width)'
)
</script>

<template>
  <div class="flex min-h-screen">
    <AppSidebar />
    <div class="flex flex-1 flex-col transition-all duration-300" :style="{ marginLeft: contentMargin }">
      <AppHeader />
      <main class="flex-1 bg-fluent-bg p-6 max-md:p-4">
        <slot />
      </main>
      <AppFooter />
    </div>
  </div>
</template>
