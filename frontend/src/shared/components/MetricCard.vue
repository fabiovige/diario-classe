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
  color: '#0078D4',
  loading: false,
})
</script>

<template>
  <div class="rounded-xl border border-fluent-border bg-fluent-surface p-5 text-center shadow-sm">
    <div class="mb-3 flex items-center justify-center gap-2">
      <i :class="icon" class="text-lg" :style="{ color }" />
      <span class="text-[0.8125rem] font-semibold uppercase tracking-wide text-fluent-text-secondary">{{ title }}</span>
    </div>
    <div v-if="loading" class="text-4xl font-bold text-fluent-text-secondary">...</div>
    <div v-else class="text-4xl font-bold leading-none" :style="{ color }">{{ value }}</div>
    <div class="mt-1.5 text-[0.8125rem] text-fluent-text-secondary">{{ label }}</div>
  </div>
</template>
