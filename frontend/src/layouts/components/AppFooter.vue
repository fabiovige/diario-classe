<script setup lang="ts">
const year = new Date().getFullYear()
</script>

<template>
  <footer class="app-footer">
    <span>{{ year }} - Prefeitura Municipal de Jandira - Secretaria de Educacao</span>
  </footer>
</template>

<style scoped>
.app-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  height: var(--footer-height);
  background: linear-gradient(to right, var(--jandira-footer-start), var(--jandira-footer-end));
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.75rem;
  padding: 0 1rem;
}
</style>
