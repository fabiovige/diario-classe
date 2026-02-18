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
  color: 'var(--jandira-primary)',
  loading: false,
})
</script>

<template>
  <div class="metric-card">
    <div class="metric-header">
      <i :class="icon" :style="{ color }" />
      <span class="metric-title">{{ title }}</span>
    </div>
    <div v-if="loading" class="metric-value metric-loading">...</div>
    <div v-else class="metric-value" :style="{ color }">{{ value }}</div>
    <div class="metric-label">{{ label }}</div>
  </div>
</template>

<style scoped>
.metric-card {
  background: #fff;
  border-radius: 12px;
  padding: 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  border: 1px solid #e2e8f0;
  text-align: center;
}

.metric-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.metric-header i {
  font-size: 1.125rem;
}

.metric-title {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.metric-value {
  font-size: 2.25rem;
  font-weight: 700;
  line-height: 1;
}

.metric-loading {
  color: #94a3b8;
}

.metric-label {
  font-size: 0.8125rem;
  color: #94a3b8;
  margin-top: 0.375rem;
}
</style>
