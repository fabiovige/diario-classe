<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import Select from 'primevue/select'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import { assessmentService } from '@/services/assessment.service'
import { curriculumService } from '@/services/curriculum.service'
import { academicCalendarService } from '@/services/academic-calendar.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { enrollmentService } from '@/services/enrollment.service'
import { useToast } from '@/composables/useToast'
import { extractApiError } from '@/shared/utils/api-error'
import type { ExperienceField } from '@/types/curriculum'
import type { AssessmentPeriod } from '@/types/academic-calendar'
import type { ClassGroup } from '@/types/school-structure'

interface StudentOption {
  id: number
  name: string
}

const toast = useToast()

const loading = ref(false)
const loadingDeps = ref(false)

const classGroups = ref<(ClassGroup & { label: string })[]>([])
const students = ref<StudentOption[]>([])
const experienceFields = ref<ExperienceField[]>([])
const periods = ref<AssessmentPeriod[]>([])

const form = ref({
  student_id: null as number | null,
  class_group_id: null as number | null,
  experience_field_id: null as number | null,
  assessment_period_id: null as number | null,
  content: '',
})

const selectedClassGroup = computed(() =>
  classGroups.value.find(cg => cg.id === form.value.class_group_id)
)

const depsPlaceholder = computed(() => {
  if (!form.value.class_group_id) return 'Selecione a turma primeiro'
  if (loadingDeps.value) return 'Carregando...'
  return 'Selecione'
})

async function loadInitialData() {
  try {
    const [cgRes, fieldsRes] = await Promise.all([
      schoolStructureService.getClassGroups({ per_page: 100 }),
      curriculumService.getExperienceFields({ per_page: 100 }),
    ])
    classGroups.value = cgRes.data.map(cg => ({ ...cg, label: [cg.grade_level?.name, cg.name, cg.shift?.name].filter(Boolean).join(' - ') }))
    experienceFields.value = fieldsRes.data
  } catch {
    toast.error('Erro ao carregar dados auxiliares')
  }
}

async function loadDependencies() {
  if (!form.value.class_group_id) return
  const academicYearId = selectedClassGroup.value?.academic_year_id
  loadingDeps.value = true
  try {
    const [periodsRes, studentsRes] = await Promise.all([
      academicCalendarService.getPeriods({ academic_year_id: academicYearId, per_page: 100 }),
      enrollmentService.getEnrollments({ class_group_id: form.value.class_group_id, status: 'active', per_page: 100 }),
    ])
    periods.value = periodsRes.data
    students.value = studentsRes.data.map(enrollment => ({
      id: enrollment.student_id,
      name: enrollment.student?.name ?? `Aluno #${enrollment.student_id}`,
    }))
  } catch {
    toast.error('Erro ao carregar dados da turma')
  } finally {
    loadingDeps.value = false
  }
}

function onClassGroupChange() {
  periods.value = []
  students.value = []
  form.value.student_id = null
  form.value.assessment_period_id = null
  loadDependencies()
}

async function handleSubmit() {
  loading.value = true
  try {
    await assessmentService.createDescriptiveReport(form.value)
    toast.success('Relatorio descritivo salvo')
    form.value.content = ''
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao salvar relatorio'))
  } finally {
    loading.value = false
  }
}

onMounted(loadInitialData)
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Relatorio Descritivo</h1>

    <div class="max-w-[700px] rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Turma *</label>
          <Select v-model="form.class_group_id" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Selecione" class="w-full" filter @change="onClassGroupChange" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Aluno *</label>
          <Select v-model="form.student_id" :options="students" optionLabel="name" optionValue="id" :placeholder="depsPlaceholder" :disabled="!form.class_group_id || loadingDeps" class="w-full" filter />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Campo de Experiencia *</label>
          <Select v-model="form.experience_field_id" :options="experienceFields" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Periodo *</label>
          <Select v-model="form.assessment_period_id" :options="periods" optionLabel="name" optionValue="id" :placeholder="depsPlaceholder" :disabled="!form.class_group_id || loadingDeps" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Conteudo do Relatorio *</label>
          <Textarea v-model="form.content" rows="8" class="w-full" />
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button type="submit" label="Salvar Relatorio" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
