<script setup lang="ts">
interface Props {
  title: string
  value: string | number
  label: string
  icon?: string
  color?: string
  loading?: boolean
}

withDefaults(defineProps<Props>(), {
  icon: 'pi pi-chart-bar',
  color: '#1976D2',
  loading: false,
})
</script>

<template>
  <div class="card text-center">
    <div class="mb-3 flex items-center justify-center gap-2">
      <i :class="icon" class="text-lg" :style="{ color }" />
      <span class="text-[0.8125rem] font-semibold uppercase tracking-wide text-md-text-secondary">{{ title }}</span>
    </div>
    <div v-if="loading" class="text-4xl font-bold text-md-text-secondary">...</div>
    <div v-else class="text-4xl font-bold leading-none" :style="{ color }">{{ value }}</div>
    <div class="mt-1.5 text-[0.8125rem] text-md-text-secondary">{{ label }}</div>
  </div>
</template>
