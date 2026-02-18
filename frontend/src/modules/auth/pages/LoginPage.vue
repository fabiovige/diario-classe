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
  <div class="rounded-xl bg-white p-8 shadow-[0_8px_32px_rgba(0,0,0,0.15)]">
    <h2 class="mb-6 text-center text-xl font-semibold text-[#0078D4]">Entrar</h2>

    <Message v-if="errorMessage" severity="error" :closable="false" class="mb-4">
      {{ errorMessage }}
    </Message>

    <form @submit.prevent="handleLogin" class="flex flex-col gap-5">
      <div class="flex flex-col gap-1.5">
        <label for="email" class="text-[0.8125rem] font-medium text-[#1A1A1A]">E-mail</label>
        <InputText id="email" v-model="email" type="email" placeholder="seu@email.com" class="w-full" required autofocus />
      </div>

      <div class="flex flex-col gap-1.5">
        <label for="password" class="text-[0.8125rem] font-medium text-[#1A1A1A]">Senha</label>
        <Password id="password" v-model="password" :feedback="false" toggleMask class="w-full" inputClass="w-full" required />
      </div>

      <Button type="submit" label="Entrar" icon="pi pi-sign-in" class="mt-2 w-full" :loading="loading" />
    </form>
  </div>
</template>
