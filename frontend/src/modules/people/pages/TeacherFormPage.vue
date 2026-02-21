<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Button from 'primevue/button'
import { peopleService } from '@/services/people.service'
import { identityService } from '@/services/identity.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { extractApiError } from '@/shared/utils/api-error'
import type { User } from '@/types/auth'
import type { School } from '@/types/school-structure'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const id = computed(() => route.params.id ? Number(route.params.id) : null)
const isEdit = computed(() => !!id.value)
const loading = ref(false)

const form = ref({
  user_id: null as number | null,
  school_id: null as number | null,
  registration_number: '',
  specialization: '',
  hire_date: '',
})

const users = ref<User[]>([])
const schools = ref<School[]>([])

async function loadAuxData() {
  try {
    const [usersRes, schoolsRes] = await Promise.all([
      identityService.getUsers({ per_page: 500, role: 'teacher' }),
      schoolStructureService.getSchools({ per_page: 100 }),
    ])
    users.value = usersRes.data
    schools.value = schoolsRes.data
  } catch {
    toast.error('Erro ao carregar dados auxiliares')
  }
}

async function loadTeacher() {
  if (!id.value) return
  loading.value = true
  try {
    const teacher = await peopleService.getTeacher(id.value)
    form.value.user_id = teacher.user_id
    form.value.school_id = teacher.school_id
    form.value.registration_number = teacher.registration_number
    form.value.specialization = teacher.specialization
    form.value.hire_date = teacher.hire_date ?? ''
  } catch {
    toast.error('Erro ao carregar professor')
    router.push('/people/teachers')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    if (isEdit.value) {
      await peopleService.updateTeacher(id.value!, form.value)
      toast.success('Professor atualizado')
    } else {
      await peopleService.createTeacher(form.value)
      toast.success('Professor criado')
    }
    router.push('/people/teachers')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao salvar professor'))
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadAuxData()
  await loadTeacher()
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">{{ isEdit ? 'Editar Professor' : 'Novo Professor' }}</h1>

    <div class="max-w-[700px] rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Usuario *</label>
          <Select v-model="form.user_id" :options="users" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" filter />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola *</label>
          <Select v-model="form.school_id" :options="schools" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Numero de Registro *</label>
          <InputText v-model="form.registration_number" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Especializacao</label>
          <InputText v-model="form.specialization" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Data de Admissao</label>
          <InputText v-model="form.hire_date" type="date" class="w-full" />
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="router.push('/people/teachers')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
