<script setup lang="ts">
</script>

<template>
  <div class="auth-layout">
    <div class="auth-container">
      <div class="auth-logo">
        <img src="/img/logo-jandira.svg" alt="Jandira" class="auth-logo-img" />
        <h1 class="auth-title">Diario de Classe</h1>
        <p class="auth-subtitle">Rede Municipal de Ensino - Jandira/SP</p>
      </div>
      <slot />
    </div>
    <div class="auth-footer">
      Prefeitura Municipal de Jandira - Secretaria de Educacao
    </div>
  </div>
</template>

<style scoped>
.auth-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--jandira-primary) 0%, var(--jandira-footer-end) 100%);
  padding: 1rem;
}

.auth-container {
  width: 100%;
  max-width: 420px;
}

.auth-logo {
  text-align: center;
  margin-bottom: 2rem;
}

.auth-logo-img {
  width: 80px;
  height: 80px;
  margin-bottom: 1rem;
}

.auth-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 0.25rem;
}

.auth-subtitle {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.8);
}

.auth-footer {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  text-align: center;
  padding: 0.75rem;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.6);
  background: linear-gradient(to right, var(--jandira-footer-start), var(--jandira-footer-end));
}
</style>
