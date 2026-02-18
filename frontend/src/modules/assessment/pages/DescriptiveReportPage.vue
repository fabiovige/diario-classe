<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Select from 'primevue/select'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import { assessmentService } from '@/services/assessment.service'
import { peopleService } from '@/services/people.service'
import { curriculumService } from '@/services/curriculum.service'
import { academicCalendarService } from '@/services/academic-calendar.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import type { Student } from '@/types/people'
import type { ExperienceField } from '@/types/curriculum'
import type { AssessmentPeriod } from '@/types/academic-calendar'
import type { ClassGroup } from '@/types/school-structure'

const toast = useToast()

const loading = ref(false)

const students = ref<Student[]>([])
const experienceFields = ref<ExperienceField[]>([])
const periods = ref<AssessmentPeriod[]>([])
const classGroups = ref<ClassGroup[]>([])

const form = ref({
  student_id: null as number | null,
  class_group_id: null as number | null,
  experience_field_id: null as number | null,
  assessment_period_id: null as number | null,
  content: '',
})

async function loadAuxData() {
  try {
    const [studentsRes, fieldsRes, periodsRes, cgRes] = await Promise.all([
      peopleService.getStudents({ per_page: 100 }),
      curriculumService.getExperienceFields({ per_page: 100 }),
      academicCalendarService.getPeriods({ per_page: 100 }),
      schoolStructureService.getClassGroups({ per_page: 100 }),
    ])
    students.value = studentsRes.data
    experienceFields.value = fieldsRes.data
    periods.value = periodsRes.data
    classGroups.value = cgRes.data
  } catch {
    toast.error('Erro ao carregar dados auxiliares')
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    await assessmentService.createDescriptiveReport(form.value)
    toast.success('Relatorio descritivo salvo')
    form.value.content = ''
  } catch (error: any) {
    toast.error(error.response?.data?.error ?? 'Erro ao salvar relatorio')
  } finally {
    loading.value = false
  }
}

onMounted(loadAuxData)
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">Relatorio Descritivo</h1>

    <div class="card-section form-card">
      <form @submit.prevent="handleSubmit" class="form-grid">
        <div class="field">
          <label>Aluno *</label>
          <Select v-model="form.student_id" :options="students" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" filter />
        </div>
        <div class="field">
          <label>Turma *</label>
          <Select v-model="form.class_group_id" :options="classGroups" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="field">
          <label>Campo de Experiencia *</label>
          <Select v-model="form.experience_field_id" :options="experienceFields" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="field">
          <label>Periodo *</label>
          <Select v-model="form.assessment_period_id" :options="periods" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="field">
          <label>Conteudo do Relatorio *</label>
          <Textarea v-model="form.content" rows="8" class="w-full" />
        </div>

        <div class="form-actions">
          <Button type="submit" label="Salvar Relatorio" icon="pi pi-check" :loading="loading" />
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
