<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Select from 'primevue/select'
import Button from 'primevue/button'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import type { School } from '@/types/school-structure'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const id = computed(() => route.params.id ? Number(route.params.id) : null)
const isEdit = computed(() => !!id.value)
const loading = ref(false)

const form = ref({
  school_id: null as number | null,
  year: new Date().getFullYear(),
  status: 'planning' as string,
  start_date: '',
  end_date: '',
})

const schools = ref<School[]>([])

const statusOptions = [
  { label: 'Planejamento', value: 'planning' },
  { label: 'Ativo', value: 'active' },
  { label: 'Encerrado', value: 'closed' },
]

async function loadAuxData() {
  try {
    const response = await schoolStructureService.getSchools({ per_page: 100 })
    schools.value = response.data
  } catch {
    toast.error('Erro ao carregar escolas')
  }
}

async function loadAcademicYear() {
  if (!id.value) return
  loading.value = true
  try {
    const item = await schoolStructureService.getAcademicYear(id.value)
    form.value.school_id = item.school_id
    form.value.year = item.year
    form.value.status = item.status
    form.value.start_date = item.start_date ?? ''
    form.value.end_date = item.end_date ?? ''
  } catch {
    toast.error('Erro ao carregar ano letivo')
    router.push('/school-structure/academic-years')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    if (isEdit.value) {
      await schoolStructureService.updateAcademicYear(id.value!, form.value)
      toast.success('Ano letivo atualizado')
    } else {
      await schoolStructureService.createAcademicYear(form.value)
      toast.success('Ano letivo criado')
    }
    router.push('/school-structure/academic-years')
  } catch (error: any) {
    toast.error(error.response?.data?.error ?? 'Erro ao salvar ano letivo')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadAuxData()
  await loadAcademicYear()
})
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">{{ isEdit ? 'Editar Ano Letivo' : 'Novo Ano Letivo' }}</h1>

    <div class="card-section form-card">
      <form @submit.prevent="handleSubmit" class="form-grid">
        <div class="field">
          <label>Escola *</label>
          <Select v-model="form.school_id" :options="schools" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="field">
          <label>Ano *</label>
          <InputNumber v-model="form.year" :useGrouping="false" :min="2020" :max="2050" class="w-full" />
        </div>
        <div class="field">
          <label>Status *</label>
          <Select v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full" />
        </div>
        <div class="field">
          <label>Data Inicio</label>
          <InputText v-model="form.start_date" type="date" class="w-full" />
        </div>
        <div class="field">
          <label>Data Fim</label>
          <InputText v-model="form.end_date" type="date" class="w-full" />
        </div>

        <div class="form-actions">
          <Button label="Cancelar" severity="secondary" @click="router.push('/school-structure/academic-years')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.form-card { max-width: 700px; }
.form-grid { display: flex; flex-direction: column; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.375rem; }
.field label { font-size: 0.8125rem; font-weight: 500; }
.w-full { width: 100%; }
.form-actions { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1rem; }
</style>
