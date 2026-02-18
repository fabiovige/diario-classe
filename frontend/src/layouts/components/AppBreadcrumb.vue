<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import Breadcrumb from 'primevue/breadcrumb'

const route = useRoute()

const home = { icon: 'pi pi-home', to: '/dashboard' }

const items = computed(() => {
  const breadcrumb = route.meta.breadcrumb as string | undefined
  if (!breadcrumb) return []
  return [{ label: breadcrumb }]
})
</script>

<template>
  <Breadcrumb :home="home" :model="items" class="app-breadcrumb" />
</template>

<style scoped>
.app-breadcrumb {
  background: transparent;
  border: none;
  padding: 0;
}

:deep(.p-breadcrumb-list) {
  font-size: 0.8125rem;
}
</style>
