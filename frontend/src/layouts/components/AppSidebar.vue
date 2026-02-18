<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import PanelMenu from 'primevue/panelmenu'
import Button from 'primevue/button'
import { useAppStore } from '@/stores/app'
import { useAuthStore } from '@/stores/auth'
import { getMenuByRole } from '@/config/permissions'

const router = useRouter()
const appStore = useAppStore()
const authStore = useAuthStore()

const menuItems = computed(() => {
  if (!authStore.roleSlug) return []

  return getMenuByRole(authStore.roleSlug).map((item) => mapMenuItem(item))
})

function mapMenuItem(item: { label: string; icon: string; to?: string; items?: any[] }): any {
  const mapped: any = {
    label: item.label,
    icon: item.icon,
  }

  if (item.to) {
    mapped.command = () => router.push(item.to!)
  }

  if (item.items) {
    mapped.items = item.items.map(mapMenuItem)
  }

  return mapped
}
</script>

<template>
  <aside class="app-sidebar" :class="{ collapsed: appStore.sidebarCollapsed }">
    <div class="sidebar-header">
      <template v-if="!appStore.sidebarCollapsed">
        <img src="/img/logo-jandira.svg" alt="Jandira" class="sidebar-logo" />
        <span class="sidebar-title">Diario de Classe</span>
      </template>
      <Button
        :icon="appStore.sidebarCollapsed ? 'pi pi-angle-right' : 'pi pi-angle-left'"
        text
        rounded
        severity="secondary"
        class="sidebar-toggle"
        @click="appStore.toggleSidebar()"
      />
    </div>
    <div v-if="!appStore.sidebarCollapsed" class="sidebar-menu">
      <PanelMenu :model="menuItems" class="sidebar-panel-menu" />
    </div>
    <div v-if="appStore.sidebarCollapsed" class="sidebar-icons">
      <Button
        v-for="item in menuItems"
        :key="item.label"
        :icon="item.icon"
        text
        rounded
        severity="secondary"
        v-tooltip.right="item.label"
        class="sidebar-icon-btn"
        @click="item.command?.()"
      />
    </div>
  </aside>
</template>

<style scoped>
.app-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  width: var(--sidebar-width);
  background: #fff;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  z-index: 100;
  overflow-y: auto;
}

.app-sidebar.collapsed {
  width: var(--sidebar-collapsed-width);
}

.sidebar-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border-bottom: 1px solid #e2e8f0;
  min-height: var(--header-height);
}

.sidebar-logo {
  width: 32px;
  height: 32px;
}

.sidebar-title {
  font-weight: 700;
  font-size: 0.875rem;
  color: var(--jandira-primary);
  white-space: nowrap;
}

.sidebar-toggle {
  margin-left: auto;
}

.sidebar-menu {
  flex: 1;
  overflow-y: auto;
  padding: 0.5rem;
}

.sidebar-icons {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 0.5rem 0;
}

.sidebar-icon-btn {
  width: 40px;
  height: 40px;
}

:deep(.sidebar-panel-menu .p-panelmenu-header-action) {
  padding: 0.6rem 0.75rem;
  font-size: 0.875rem;
}

:deep(.sidebar-panel-menu .p-menuitem-link) {
  padding: 0.5rem 0.75rem 0.5rem 2rem;
  font-size: 0.8125rem;
}

@media (max-width: 768px) {
  .app-sidebar {
    transform: translateX(-100%);
  }

  .app-sidebar.mobile-open {
    transform: translateX(0);
  }
}
</style>
