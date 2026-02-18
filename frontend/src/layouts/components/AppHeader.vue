<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Menu from 'primevue/menu'
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
  <header class="app-header">
    <div class="header-left">
      <AppBreadcrumb />
    </div>
    <div class="header-right">
      <div class="header-user" @click="toggleUserMenu">
        <i class="pi pi-user header-avatar" />
        <div class="header-user-info">
          <span class="header-user-name">{{ authStore.userName }}</span>
          <span class="header-user-role">{{ authStore.roleSlug }}</span>
        </div>
        <i class="pi pi-chevron-down header-chevron" />
      </div>
      <Menu ref="userMenu" :model="userMenuItems" :popup="true" />
    </div>
  </header>
</template>

<script lang="ts">
import AppBreadcrumb from './AppBreadcrumb.vue'
export default { components: { AppBreadcrumb } }
</script>

<style scoped>
.app-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: var(--header-height);
  padding: 0 1.5rem;
  background: #fff;
  border-bottom: 1px solid #e2e8f0;
  position: sticky;
  top: 0;
  z-index: 90;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-user {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  transition: background 0.2s;
}

.header-user:hover {
  background: #f1f5f9;
}

.header-avatar {
  font-size: 1.25rem;
  color: var(--jandira-primary);
  background: #e6eef8;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.header-user-info {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
}

.header-user-name {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--jandira-text);
}

.header-user-role {
  font-size: 0.6875rem;
  color: #94a3b8;
  text-transform: capitalize;
}

.header-chevron {
  font-size: 0.625rem;
  color: #94a3b8;
}
</style>
