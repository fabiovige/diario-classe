<script setup lang="ts">
const year = new Date().getFullYear()
</script>

<template>
  <footer class="flex h-[var(--footer-height)] items-center justify-center bg-gradient-to-r from-[#003366] to-[#001A33] px-4 text-xs text-white/80">
    <span>{{ year }} - Prefeitura Municipal de Jandira - Secretaria de Educacao</span>
  </footer>
</template>
