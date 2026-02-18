<script setup lang="ts">
interface Props {
  visible: boolean
}

defineProps<Props>()
</script>

<template>
  <div v-if="visible" class="absolute inset-0 z-50 flex items-center justify-center bg-white/70">
    <i class="pi pi-spin pi-spinner text-3xl text-fluent-primary" />
  </div>
</template>
