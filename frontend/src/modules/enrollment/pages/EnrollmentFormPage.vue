<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import Select from 'primevue/select'
import Button from 'primevue/button'
import { enrollmentService } from '@/services/enrollment.service'
import { peopleService } from '@/services/people.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import type { Student } from '@/types/people'
import type { School, AcademicYear } from '@/types/school-structure'

const router = useRouter()
const toast = useToast()

const loading = ref(false)

const form = ref({
  student_id: null as number | null,
  academic_year_id: null as number | null,
  school_id: null as number | null,
})

const students = ref<Student[]>([])
const schools = ref<School[]>([])
const academicYears = ref<AcademicYear[]>([])

async function loadAuxData() {
  try {
    const [studentsRes, schoolsRes, ayRes] = await Promise.all([
      peopleService.getStudents({ per_page: 100 }),
      schoolStructureService.getSchools({ per_page: 100 }),
      schoolStructureService.getAcademicYears({ per_page: 100 }),
    ])
    students.value = studentsRes.data
    schools.value = schoolsRes.data
    academicYears.value = ayRes.data
  } catch {
    toast.error('Erro ao carregar dados auxiliares')
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    await enrollmentService.createEnrollment(form.value)
    toast.success('Matricula criada')
    router.push('/enrollment/enrollments')
  } catch (error: any) {
    toast.error(error.response?.data?.error ?? 'Erro ao criar matricula')
  } finally {
    loading.value = false
  }
}

onMounted(loadAuxData)
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">Nova Matricula</h1>

    <div class="card-section form-card">
      <form @submit.prevent="handleSubmit" class="form-grid">
        <div class="field">
          <label>Aluno *</label>
          <Select v-model="form.student_id" :options="students" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" filter />
        </div>
        <div class="field">
          <label>Escola *</label>
          <Select v-model="form.school_id" :options="schools" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="field">
          <label>Ano Letivo *</label>
          <Select v-model="form.academic_year_id" :options="academicYears" optionLabel="year" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>

        <div class="form-actions">
          <Button label="Cancelar" severity="secondary" @click="router.push('/enrollment/enrollments')" />
          <Button type="submit" label="Matricular" icon="pi pi-check" :loading="loading" />
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
