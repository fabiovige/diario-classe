<script setup lang="ts">
</script>

<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-[#0078D4] to-[#001A33] p-4">
    <div class="w-full max-w-[420px]">
      <div class="mb-8 text-center">
        <img src="/img/logo-jandira.svg" alt="Jandira" class="mx-auto mb-4 h-20 w-20" />
        <h1 class="mb-1 text-[1.75rem] font-bold text-white">Diario de Classe</h1>
        <p class="text-sm text-white/80">Rede Municipal de Ensino - Jandira/SP</p>
      </div>
      <slot />
    </div>
    <footer class="fixed inset-x-0 bottom-0 bg-gradient-to-r from-[#003366] to-[#001A33] py-3 text-center text-xs text-white/60">
      Prefeitura Municipal de Jandira - Secretaria de Educacao
    </footer>
  </div>
</template>
