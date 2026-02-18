<script setup lang="ts">
interface Props {
  visible: boolean
}

defineProps<Props>()
</script>

<template>
  <div v-if="visible" class="loading-overlay">
    <i class="pi pi-spin pi-spinner loading-spinner" />
  </div>
</template>

<style scoped>
.loading-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.7);
  z-index: 50;
}

.loading-spinner {
  font-size: 2rem;
  color: var(--jandira-primary);
}
</style>
