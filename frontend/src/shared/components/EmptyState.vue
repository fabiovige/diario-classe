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
    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-md-bg">
      <i :class="icon" class="text-3xl text-md-text-secondary" />
    </div>
    <p class="text-[0.9375rem] text-md-text-secondary">{{ message }}</p>
    <slot />
  </div>
</template>
