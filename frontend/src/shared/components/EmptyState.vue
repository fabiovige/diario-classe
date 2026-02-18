<script setup lang="ts">
interface Props {
  icon?: string
  message?: string
}

withDefaults(defineProps<Props>(), {
  icon: 'pi pi-inbox',
  message: 'Nenhum registro encontrado',
})
</script>

<template>
  <div class="empty-state">
    <i :class="icon" class="empty-icon" />
    <p class="empty-message">{{ message }}</p>
    <slot />
  </div>
</template>

<style scoped>
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  text-align: center;
}

.empty-icon {
  font-size: 3rem;
  color: #cbd5e1;
  margin-bottom: 1rem;
}

.empty-message {
  font-size: 0.9375rem;
  color: #94a3b8;
}
</style>
