<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref } from 'vue'
import AppMenu from './AppMenu.vue'
import { useLayout } from '../composables/useLayout'

const { hideMobileMenu, isDesktop, layoutState } = useLayout()

const sidebarRef = ref<HTMLElement | null>(null)

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
  <div ref="sidebarRef" class="layout-sidebar">
    <AppMenu />
  </div>
</template>
