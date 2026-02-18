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
  <div class="flex flex-col items-center justify-center px-4 py-12 text-center">
    <i :class="icon" class="mb-4 text-5xl text-fluent-border" />
    <p class="text-[0.9375rem] text-fluent-text-secondary">{{ message }}</p>
    <slot />
  </div>
</template>
