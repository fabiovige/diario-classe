<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Menu from 'primevue/menu'
import AppBreadcrumb from './AppBreadcrumb.vue'
import { useAuthStore } from '@/stores/auth'
import { useAuth } from '@/composables/useAuth'

const router = useRouter()
const authStore = useAuthStore()
const { logout } = useAuth()

const userMenu = ref()
const userMenuItems = ref([
  {
    label: 'Meu Perfil',
    icon: 'pi pi-user',
    command: () => router.push('/profile'),
  },
  { separator: true },
  {
    label: 'Sair',
    icon: 'pi pi-sign-out',
    command: () => logout(),
  },
])

function toggleUserMenu(event: Event) {
  userMenu.value.toggle(event)
}
</script>

<template>
  <header class="sticky top-0 z-[90] flex h-[var(--header-height)] items-center justify-between border-b border-fluent-border bg-fluent-surface px-6">
    <div class="flex items-center gap-4">
      <AppBreadcrumb />
    </div>
    <div class="flex items-center gap-4">
      <div class="flex cursor-pointer items-center gap-2 rounded-md px-3 py-1.5 transition-colors hover:bg-fluent-hover" @click="toggleUserMenu">
        <i class="pi pi-user flex h-8 w-8 items-center justify-center rounded-full bg-fluent-selected text-lg text-fluent-primary" />
        <div class="flex flex-col leading-tight">
          <span class="text-[0.8125rem] font-semibold text-fluent-text">{{ authStore.userName }}</span>
          <span class="text-[0.6875rem] capitalize text-fluent-text-secondary">{{ authStore.roleSlug }}</span>
        </div>
        <i class="pi pi-chevron-down text-[0.625rem] text-fluent-text-secondary" />
      </div>
      <Menu ref="userMenu" :model="userMenuItems" :popup="true" />
    </div>
  </header>
</template>
