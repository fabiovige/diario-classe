<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Message from 'primevue/message'
import { authService } from '@/services/auth.service'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')

async function handleLogin() {
  errorMessage.value = ''
  loading.value = true

  try {
    const response = await authService.login({ email: email.value, password: password.value })
    authStore.setAuth(response.user, response.token)
    router.push('/dashboard')
  } catch (error: any) {
    errorMessage.value = error.response?.data?.error ?? 'Credenciais invalidas. Tente novamente.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-card">
    <h2 class="login-title">Entrar</h2>

    <Message v-if="errorMessage" severity="error" :closable="false" class="login-error">
      {{ errorMessage }}
    </Message>

    <form @submit.prevent="handleLogin" class="login-form">
      <div class="field">
        <label for="email">E-mail</label>
        <InputText
          id="email"
          v-model="email"
          type="email"
          placeholder="seu@email.com"
          class="w-full"
          required
          autofocus
        />
      </div>

      <div class="field">
        <label for="password">Senha</label>
        <Password
          id="password"
          v-model="password"
          :feedback="false"
          toggleMask
          class="w-full"
          inputClass="w-full"
          required
        />
      </div>

      <Button
        type="submit"
        label="Entrar"
        icon="pi pi-sign-in"
        class="w-full login-btn"
        :loading="loading"
      />
    </form>
  </div>
</template>

<style scoped>
.login-card {
  background: #fff;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}

.login-title {
  text-align: center;
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--jandira-primary);
  margin-bottom: 1.5rem;
}

.login-error {
  margin-bottom: 1rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.field label {
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--jandira-text);
}

.w-full {
  width: 100%;
}

.login-btn {
  margin-top: 0.5rem;
}
</style>
