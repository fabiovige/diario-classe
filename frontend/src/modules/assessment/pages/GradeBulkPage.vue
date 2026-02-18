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

const classGroups = ref<(ClassGroup & { label: string })[]>([])
const assignments = ref<(TeacherAssignment & { label: string })[]>([])
const periods = ref<AssessmentPeriod[]>([])

const selectedClassGroupId = ref<number | null>(null)
const selectedAssignmentId = ref<number | null>(null)
const selectedPeriodId = ref<number | null>(null)

const students = ref<StudentGrade[]>([])
const loading = ref(false)
const loadingDeps = ref(false)
const submitting = ref(false)

const selectedClassGroup = computed(() =>
  classGroups.value.find(cg => cg.id === selectedClassGroupId.value)
)

const canSubmit = computed(() =>
  selectedClassGroupId.value && selectedAssignmentId.value && selectedPeriodId.value && students.value.length > 0
)

const depsPlaceholder = computed(() => {
  if (!selectedClassGroupId.value) return 'Selecione a turma primeiro'
  if (loadingDeps.value) return 'Carregando...'
  return 'Selecione'
})

const assignmentPlaceholder = computed(() => {
  if (!selectedClassGroupId.value) return 'Selecione a turma primeiro'
  if (loadingDeps.value) return 'Carregando...'
  if (assignments.value.length === 0) return 'Nenhuma disciplina vinculada'
  return 'Selecione'
})

async function loadClassGroups() {
  try {
    const response = await schoolStructureService.getClassGroups({ per_page: 100 })
    classGroups.value = response.data.map(cg => ({
      ...cg,
      label: [cg.grade_level?.name, cg.name, cg.shift?.name].filter(Boolean).join(' - '),
    }))
  } catch {
    toast.error('Erro ao carregar turmas')
  }
}

async function loadDependencies() {
  if (!selectedClassGroupId.value) return
  const academicYearId = selectedClassGroup.value?.academic_year_id
  loadingDeps.value = true
  try {
    const [assignmentsRes, periodsRes, studentsRes] = await Promise.all([
      curriculumService.getAssignments({ class_group_id: selectedClassGroupId.value, per_page: 100 }),
      academicCalendarService.getPeriods({ academic_year_id: academicYearId, per_page: 100 }),
      enrollmentService.getEnrollments({ class_group_id: selectedClassGroupId.value, status: 'active', per_page: 100 }),
    ])
    assignments.value = assignmentsRes.data.map(a => ({
      ...a,
      label: a.curricular_component?.name ?? a.experience_field?.name ?? `Disciplina #${a.id}`,
    }))
    periods.value = periodsRes.data
    students.value = studentsRes.data.map(enrollment => ({
      student_id: enrollment.student_id,
      student_name: enrollment.student?.name ?? `Aluno #${enrollment.student_id}`,
      numeric_value: null,
      conceptual_value: null,
    }))
  } catch {
    toast.error('Erro ao carregar dados da turma')
  } finally {
    loadingDeps.value = false
  }
}

function onClassGroupChange() {
  assignments.value = []
  periods.value = []
  students.value = []
  selectedAssignmentId.value = null
  selectedPeriodId.value = null
  loadDependencies()
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

onMounted(loadClassGroups)
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Lancamento de Notas</h1>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <div class="grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Turma *</label>
          <Select v-model="selectedClassGroupId" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Selecione" class="w-full" filter @change="onClassGroupChange" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Disciplina *</label>
          <Select v-model="selectedAssignmentId" :options="assignments" optionLabel="label" optionValue="id" :placeholder="assignmentPlaceholder" :disabled="!selectedClassGroupId || loadingDeps || assignments.length === 0" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Periodo *</label>
          <Select v-model="selectedPeriodId" :options="periods" optionLabel="name" optionValue="id" :placeholder="depsPlaceholder" :disabled="!selectedClassGroupId || loadingDeps" class="w-full" />
        </div>
      </div>
    </div>

    <div class="mt-6 rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <EmptyState v-if="!loading && students.length === 0" message="Selecione uma turma para carregar os alunos" />

      <DataTable v-if="students.length > 0" :value="students" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column field="student_name" header="Aluno" sortable />
        <Column header="Nota" :style="{ width: '150px' }">
          <template #body="{ data }">
            <InputNumber v-model="data.numeric_value" :min="0" :max="10" :maxFractionDigits="2" class="w-full" />
          </template>
        </Column>
        <Column header="Conceito" :style="{ width: '120px' }">
          <template #body="{ data }">
            <Select v-model="data.conceptual_value" :options="['PS', 'S', 'NS']" placeholder="--" class="w-full" showClear />
          </template>
        </Column>
      </DataTable>

      <div v-if="students.length > 0" class="mt-4 flex justify-end border-t border-[#E0E0E0] pt-4">
        <Button label="Salvar Notas" icon="pi pi-check" :loading="submitting" :disabled="!canSubmit" @click="handleSubmit" />
      </div>
    </div>
  </div>
</template>
