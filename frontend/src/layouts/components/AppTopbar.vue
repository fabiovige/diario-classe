<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Menu from 'primevue/menu'
import AppBreadcrumb from './AppBreadcrumb.vue'
import { useAuthStore } from '@/stores/auth'
import { useAuth } from '@/composables/useAuth'
import { useLayout } from '../composables/useLayout'

const router = useRouter()
const authStore = useAuthStore()
const { logout } = useAuth()
const { toggleMenu } = useLayout()

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
  <div class="layout-topbar">
    <div class="layout-topbar-logo-container">
      <button class="layout-menu-button" @click="toggleMenu()">
        <i class="pi pi-bars" />
      </button>
      <router-link to="/dashboard" class="layout-topbar-logo">
        <img src="/img/logo-jandira.svg" alt="Jandira" />
        <span>Diario de Classe</span>
      </router-link>
    </div>
    <div class="layout-topbar-actions">
      <AppBreadcrumb />
      <div class="layout-topbar-user" @click="toggleUserMenu">
        <i class="pi pi-user layout-topbar-user-avatar" />
        <div class="layout-topbar-user-info max-md:hidden">
          <span class="layout-topbar-user-name">{{ authStore.userName }}</span>
          <span class="layout-topbar-user-role">{{ authStore.roleSlug }}</span>
        </div>
        <i class="pi pi-chevron-down layout-topbar-user-chevron max-md:hidden" />
      </div>
      <Menu ref="userMenu" :model="userMenuItems" :popup="true" />
    </div>
  </div>
</template>
