<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import Select from 'primevue/select'
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import EmptyState from '@/shared/components/EmptyState.vue'
import { assessmentService } from '@/services/assessment.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { curriculumService } from '@/services/curriculum.service'
import { academicCalendarService } from '@/services/academic-calendar.service'
import { enrollmentService } from '@/services/enrollment.service'
import { useToast } from '@/composables/useToast'
import type { ClassGroup } from '@/types/school-structure'
import type { TeacherAssignment } from '@/types/curriculum'
import type { AssessmentPeriod } from '@/types/academic-calendar'

interface StudentGrade {
  student_id: number
  student_name: string
  numeric_value: number | null
  conceptual_value: string | null
}

const toast = useToast()

const classGroups = ref<ClassGroup[]>([])
const assignments = ref<TeacherAssignment[]>([])
const periods = ref<AssessmentPeriod[]>([])

const selectedClassGroupId = ref<number | null>(null)
const selectedAssignmentId = ref<number | null>(null)
const selectedPeriodId = ref<number | null>(null)

const students = ref<StudentGrade[]>([])
const loading = ref(false)
const submitting = ref(false)

const canSubmit = computed(() =>
  selectedClassGroupId.value && selectedAssignmentId.value && selectedPeriodId.value && students.value.length > 0
)

function assignmentLabel(a: TeacherAssignment): string {
  return a.curricular_component?.name ?? a.experience_field?.name ?? `Atribuicao #${a.id}`
}

async function loadClassGroups() {
  try {
    const response = await schoolStructureService.getClassGroups({ per_page: 100 })
    classGroups.value = response.data
  } catch {
    toast.error('Erro ao carregar turmas')
  }
}

async function loadAssignments() {
  if (!selectedClassGroupId.value) return
  try {
    const response = await curriculumService.getAssignments({ class_group_id: selectedClassGroupId.value, per_page: 100 })
    assignments.value = response.data
  } catch {
    toast.error('Erro ao carregar atribuicoes')
  }
}

async function loadPeriods() {
  try {
    const response = await academicCalendarService.getPeriods({ per_page: 100 })
    periods.value = response.data
  } catch {
    toast.error('Erro ao carregar periodos')
  }
}

async function loadStudents() {
  if (!selectedClassGroupId.value) return
  loading.value = true
  try {
    const response = await enrollmentService.getEnrollments({
      class_group_id: selectedClassGroupId.value,
      status: 'active',
      per_page: 100,
    })
    students.value = response.data.map(enrollment => ({
      student_id: enrollment.student_id,
      student_name: enrollment.student?.name ?? `Aluno #${enrollment.student_id}`,
      numeric_value: null,
      conceptual_value: null,
    }))
  } catch {
    toast.error('Erro ao carregar alunos')
  } finally {
    loading.value = false
  }
}

function onClassGroupChange() {
  assignments.value = []
  selectedAssignmentId.value = null
  students.value = []
  loadAssignments()
  loadStudents()
}

async function handleSubmit() {
  if (!selectedClassGroupId.value || !selectedAssignmentId.value || !selectedPeriodId.value) return
  submitting.value = true
  try {
    await assessmentService.bulkGrades({
      class_group_id: selectedClassGroupId.value,
      teacher_assignment_id: selectedAssignmentId.value,
      assessment_period_id: selectedPeriodId.value,
      grades: students.value.map(s => ({
        student_id: s.student_id,
        numeric_value: s.numeric_value ?? undefined,
        conceptual_value: s.conceptual_value ?? undefined,
      })),
    })
    toast.success('Notas registradas')
  } catch (error: any) {
    toast.error(error.response?.data?.error ?? 'Erro ao registrar notas')
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  await Promise.all([loadClassGroups(), loadPeriods()])
})
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">Lancamento de Notas</h1>

    <div class="card-section">
      <div class="filter-bar">
        <div class="field">
          <label>Turma *</label>
          <Select v-model="selectedClassGroupId" :options="classGroups" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" @change="onClassGroupChange" />
        </div>
        <div class="field">
          <label>Atribuicao *</label>
          <Select v-model="selectedAssignmentId" :options="assignments" :optionLabel="assignmentLabel" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="field">
          <label>Periodo *</label>
          <Select v-model="selectedPeriodId" :options="periods" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
      </div>
    </div>

    <div class="card-section mt-3">
      <EmptyState v-if="!loading && students.length === 0" message="Selecione uma turma para carregar os alunos" />

      <DataTable v-if="students.length > 0" :value="students" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column field="student_name" header="Aluno" sortable />
        <Column header="Nota" :style="{ width: '150px' }">
          <template #body="{ data }">
            <InputNumber v-model="data.numeric_value" :min="0" :max="10" :maxFractionDigits="2" class="grade-input" />
          </template>
        </Column>
        <Column header="Conceito" :style="{ width: '120px' }">
          <template #body="{ data }">
            <Select v-model="data.conceptual_value" :options="['PS', 'S', 'NS']" placeholder="--" class="grade-input" showClear />
          </template>
        </Column>
      </DataTable>

      <div v-if="students.length > 0" class="submit-bar">
        <Button label="Salvar Notas" icon="pi pi-check" :loading="submitting" :disabled="!canSubmit" @click="handleSubmit" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.filter-bar { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.375rem; }
.field label { font-size: 0.8125rem; font-weight: 500; }
.w-full { width: 100%; }
.mt-3 { margin-top: 1.5rem; }
.grade-input { width: 100%; }
.submit-bar { display: flex; justify-content: flex-end; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; }
</style>
