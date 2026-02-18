<script setup lang="ts">
import { useAppStore } from '@/stores/app'
import AppSidebar from './components/AppSidebar.vue'
import AppHeader from './components/AppHeader.vue'
import AppFooter from './components/AppFooter.vue'

const appStore = useAppStore()
</script>

<template>
  <div class="main-layout" :class="{ 'sidebar-collapsed': appStore.sidebarCollapsed }">
    <AppSidebar />
    <div class="main-content-wrapper">
      <AppHeader />
      <main class="main-content">
        <slot />
      </main>
      <AppFooter />
    </div>
  </div>
</template>

<style scoped>
.main-layout {
  display: flex;
  min-height: 100vh;
}

.main-content-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  margin-left: var(--sidebar-width);
  transition: margin-left 0.3s ease;
}

.sidebar-collapsed .main-content-wrapper {
  margin-left: var(--sidebar-collapsed-width);
}

.main-content {
  flex: 1;
  padding: 1.5rem;
  background-color: var(--jandira-bg);
}

@media (max-width: 768px) {
  .main-content-wrapper {
    margin-left: 0;
  }

  .main-content {
    padding: 1rem;
  }
}
</style>
