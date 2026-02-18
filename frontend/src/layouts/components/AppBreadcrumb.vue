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
  <Breadcrumb :home="home" :model="items" class="border-none bg-transparent p-0 text-[0.8125rem]" />
</template>
