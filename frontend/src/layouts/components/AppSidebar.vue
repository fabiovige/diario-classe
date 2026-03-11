<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import Menu from 'primevue/menu'
import AppMenu from './AppMenu.vue'
import { useAuthStore } from '@/stores/auth'
import { useAuth } from '@/composables/useAuth'
import { useLayout } from '../composables/useLayout'

const router = useRouter()
const authStore = useAuthStore()
const { logout } = useAuth()
const { hideMobileMenu, isDesktop, layoutState } = useLayout()

const sidebarRef = ref<HTMLElement | null>(null)
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

function onOutsideClick(event: MouseEvent) {
  if (!sidebarRef.value) return
  if (!layoutState.mobileMenuActive) return
  if (isDesktop()) return

  const target = event.target as HTMLElement
  if (sidebarRef.value.contains(target)) return
  if (target.closest('.layout-menu-button')) return

  hideMobileMenu()
}

onMounted(() => {
  document.addEventListener('click', onOutsideClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onOutsideClick)
})
</script>

<template>
  <aside ref="sidebarRef" class="layout-sidebar">
    <div class="sidebar-header">
      <router-link to="/dashboard" class="sidebar-header-logo">
        <img src="/img/logo-jandira.svg" alt="Jandira" />
        <span>Diario de Classe</span>
      </router-link>
    </div>

    <nav class="sidebar-nav">
      <AppMenu />
    </nav>

    <div class="sidebar-footer">
      <button class="sidebar-user" @click="toggleUserMenu">
        <i class="pi pi-user sidebar-user-avatar" />
        <div class="sidebar-user-info">
          <span class="sidebar-user-name">{{ authStore.userName }}</span>
          <span class="sidebar-user-role">{{ authStore.roleSlug }}</span>
        </div>
        <i class="pi pi-chevron-up sidebar-user-chevron" />
      </button>
      <Menu ref="userMenu" :model="userMenuItems" :popup="true" />
    </div>
  </aside>
</template>
