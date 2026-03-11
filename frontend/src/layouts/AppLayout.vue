<script setup lang="ts">
import { watch } from 'vue'
import { useRoute } from 'vue-router'
import Toast from 'primevue/toast'
import AppSidebar from './components/AppSidebar.vue'
import AppFooter from './components/AppFooter.vue'
import AppBreadcrumb from './components/AppBreadcrumb.vue'
import { useLayout } from './composables/useLayout'

const route = useRoute()
const { containerClass, hideMobileMenu, toggleMenu } = useLayout()

watch(() => route.path, () => {
  hideMobileMenu()
})
</script>

<template>
  <div class="layout-wrapper" :class="containerClass">
    <AppSidebar />

    <div class="layout-main-container">
      <div class="layout-mobile-topbar">
        <router-link to="/dashboard" class="layout-mobile-topbar-logo">
          <img src="/img/logo-jandira.svg" alt="Jandira" />
          <span>Diario de Classe</span>
        </router-link>
        <button class="layout-menu-button" @click="toggleMenu()">
          <i class="pi pi-bars" />
        </button>
      </div>

      <div class="layout-main">
        <AppBreadcrumb />
        <slot />
      </div>
      <AppFooter />
    </div>

    <div class="layout-mask" @click="hideMobileMenu()" />
    <Toast position="top-right" />
  </div>
</template>
