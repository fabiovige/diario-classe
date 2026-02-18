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
  <aside
    class="fixed inset-y-0 left-0 z-[100] flex flex-col overflow-y-auto border-r border-fluent-border bg-fluent-surface transition-all duration-300 max-md:-translate-x-full"
    :class="appStore.sidebarCollapsed ? 'w-[var(--sidebar-collapsed-width)]' : 'w-[var(--sidebar-width)]'"
  >
    <div class="flex min-h-[var(--header-height)] items-center gap-2 border-b border-fluent-border px-3 py-3">
      <template v-if="!appStore.sidebarCollapsed">
        <img src="/img/logo-jandira.svg" alt="Jandira" class="h-8 w-8" />
        <span class="whitespace-nowrap text-sm font-bold text-fluent-primary">Diario de Classe</span>
      </template>
      <Button
        :icon="appStore.sidebarCollapsed ? 'pi pi-angle-right' : 'pi pi-angle-left'"
        text
        rounded
        severity="secondary"
        class="ml-auto"
        @click="appStore.toggleSidebar()"
      />
    </div>
    <div v-if="!appStore.sidebarCollapsed" class="flex-1 overflow-y-auto p-2">
      <PanelMenu :model="menuItems" />
    </div>
    <div v-if="appStore.sidebarCollapsed" class="flex flex-col items-center gap-1 py-2">
      <Button
        v-for="item in menuItems"
        :key="item.label"
        :icon="item.icon"
        text
        rounded
        severity="secondary"
        v-tooltip.right="item.label"
        class="h-10 w-10"
        @click="item.command?.()"
      />
    </div>
  </aside>
</template>
