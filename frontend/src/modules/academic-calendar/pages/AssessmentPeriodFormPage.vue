<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Select from 'primevue/select'
import Button from 'primevue/button'
import { academicCalendarService } from '@/services/academic-calendar.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import type { AcademicYear } from '@/types/school-structure'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const id = computed(() => route.params.id ? Number(route.params.id) : null)
const isEdit = computed(() => !!id.value)
const loading = ref(false)

const form = ref({
  academic_year_id: null as number | null,
  type: 'bimester' as string,
  number: 1,
  name: '',
  start_date: '',
  end_date: '',
})

const academicYears = ref<AcademicYear[]>([])

const typeOptions = [
  { label: 'Bimestre', value: 'bimester' },
  { label: 'Trimestre', value: 'trimester' },
  { label: 'Semestre', value: 'semester' },
]

async function loadAuxData() {
  try {
    const response = await schoolStructureService.getAcademicYears({ per_page: 100 })
    academicYears.value = response.data
  } catch {
    toast.error('Erro ao carregar anos letivos')
  }
}

async function loadPeriod() {
  if (!id.value) return
  loading.value = true
  try {
    const item = await academicCalendarService.getPeriod(id.value)
    form.value.academic_year_id = item.academic_year_id
    form.value.type = item.type
    form.value.number = item.number
    form.value.name = item.name
    form.value.start_date = item.start_date ?? ''
    form.value.end_date = item.end_date ?? ''
  } catch {
    toast.error('Erro ao carregar periodo')
    router.push('/academic-calendar/periods')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    if (isEdit.value) {
      await academicCalendarService.updatePeriod(id.value!, form.value)
      toast.success('Periodo atualizado')
    } else {
      await academicCalendarService.createPeriod(form.value)
      toast.success('Periodo criado')
    }
    router.push('/academic-calendar/periods')
  } catch (error: any) {
    toast.error(error.response?.data?.error ?? 'Erro ao salvar periodo')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadAuxData()
  await loadPeriod()
})
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">{{ isEdit ? 'Editar Periodo' : 'Novo Periodo' }}</h1>

    <div class="card-section form-card">
      <form @submit.prevent="handleSubmit" class="form-grid">
        <div class="field">
          <label>Ano Letivo *</label>
          <Select v-model="form.academic_year_id" :options="academicYears" optionLabel="year" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="field">
          <label>Tipo *</label>
          <Select v-model="form.type" :options="typeOptions" optionLabel="label" optionValue="value" class="w-full" />
        </div>
        <div class="field">
          <label>Numero *</label>
          <InputNumber v-model="form.number" :min="1" :max="6" class="w-full" />
        </div>
        <div class="field">
          <label>Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
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
          <Button label="Cancelar" severity="secondary" @click="router.push('/academic-calendar/periods')" />
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
