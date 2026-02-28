<script setup lang="ts">
import { watch } from 'vue'
import { useRoute } from 'vue-router'
import Toast from 'primevue/toast'
import AppTopbar from './components/AppTopbar.vue'
import AppSidebar from './components/AppSidebar.vue'
import AppFooter from './components/AppFooter.vue'
import { useLayout } from './composables/useLayout'

const route = useRoute()
const { containerClass, hideMobileMenu } = useLayout()

watch(() => route.path, () => {
  hideMobileMenu()
})
</script>

<template>
  <div class="layout-wrapper" :class="containerClass">
    <AppTopbar />
    <AppSidebar />
    <div class="layout-main-container">
      <div class="layout-main">
        <slot />
      </div>
      <AppFooter />
    </div>
    <div class="layout-mask" @click="hideMobileMenu()" />
    <Toast position="top-right" />
  </div>
</template>
