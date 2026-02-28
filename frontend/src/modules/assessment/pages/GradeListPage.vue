<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useListFilters } from '@/composables/useListFilters'
import { useConfirm } from '@/composables/useConfirm'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import Paginator from 'primevue/paginator'
import Select from 'primevue/select'
import Tag from 'primevue/tag'
import EmptyState from '@/shared/components/EmptyState.vue'
import { assessmentService } from '@/services/assessment.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { curriculumService } from '@/services/curriculum.service'
import { academicCalendarService } from '@/services/academic-calendar.service'
import { useToast } from '@/composables/useToast'
import { useSchoolScope } from '@/composables/useSchoolScope'
import type { Grade } from '@/types/assessment'
import type { School, ClassGroup } from '@/types/school-structure'
import type { TeacherAssignment } from '@/types/curriculum'
import type { AssessmentPeriod } from '@/types/academic-calendar'

const toast = useToast()
const { confirmDelete } = useConfirm()
const { shouldShowSchoolFilter, userSchoolId, userSchoolName } = useSchoolScope()

const items = ref<Grade[]>([])
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(15)

const schools = ref<School[]>([])
const classGroups = ref<(ClassGroup & { label: string })[]>([])
const assignments = ref<(TeacherAssignment & { label: string })[]>([])
const periods = ref<AssessmentPeriod[]>([])

const selectedSchoolId = ref<number | null>(null)
const selectedClassGroupId = ref<number | null>(null)
const selectedAssignmentId = ref<number | null>(null)
const selectedPeriodId = ref<number | null>(null)

let initializing = false

const { initFromQuery, syncToUrl, clearAll } = useListFilters([
  { key: 'school_id', ref: selectedSchoolId, type: 'number' },
  { key: 'class_group_id', ref: selectedClassGroupId, type: 'number' },
  { key: 'teacher_assignment_id', ref: selectedAssignmentId, type: 'number' },
  { key: 'assessment_period_id', ref: selectedPeriodId, type: 'number' },
  { key: 'page', ref: currentPage, type: 'number' },
])

const hasActiveFilters = computed(() =>
  selectedSchoolId.value !== null
  || selectedClassGroupId.value !== null
  || selectedAssignmentId.value !== null
  || selectedPeriodId.value !== null
)

const editingId = ref<number | null>(null)
const editNumericValue = ref<number | null>(null)
const editConceptualValue = ref<string | null>(null)
const editObservations = ref<string>('')
const saving = ref(false)

async function loadSchools() {
  try {
    const response = await schoolStructureService.getSchools({ per_page: 200 })
    schools.value = response.data
  } catch {
    toast.error('Erro ao carregar escolas')
  }
}

async function loadClassGroups() {
  if (!selectedSchoolId.value) {
    classGroups.value = []
    return
  }
  try {
    const response = await schoolStructureService.getClassGroups({ school_id: selectedSchoolId.value, per_page: 200 })
    classGroups.value = response.data.map(cg => ({
      ...cg,
      label: [cg.grade_level?.name, cg.name, cg.shift?.name_label].filter(Boolean).join(' - '),
    })).sort((a, b) => a.label.localeCompare(b.label, 'pt-BR'))
  } catch {
    toast.error('Erro ao carregar turmas')
  }
}

async function loadAssignments() {
  if (!selectedClassGroupId.value) {
    assignments.value = []
    return
  }
  try {
    const response = await curriculumService.getAssignments({ class_group_id: selectedClassGroupId.value, per_page: 200 })
    assignments.value = response.data.map(a => ({
      ...a,
      label: a.curricular_component?.name ?? a.experience_field?.name ?? `Atribuicao #${a.id}`,
    })).sort((a, b) => a.label.localeCompare(b.label, 'pt-BR'))
  } catch {
    toast.error('Erro ao carregar disciplinas')
  }
}

async function loadPeriods() {
  if (!selectedClassGroupId.value) {
    periods.value = []
    return
  }
  const classGroup = classGroups.value.find(cg => cg.id === selectedClassGroupId.value)
  if (!classGroup?.academic_year_id) {
    periods.value = []
    return
  }
  try {
    const response = await academicCalendarService.getPeriods({ academic_year_id: classGroup.academic_year_id, per_page: 50 })
    periods.value = response.data
  } catch {
    toast.error('Erro ao carregar periodos')
  }
}

watch(selectedSchoolId, () => {
  if (initializing) return
  selectedClassGroupId.value = null
  selectedAssignmentId.value = null
  selectedPeriodId.value = null
  classGroups.value = []
  assignments.value = []
  periods.value = []
  loadClassGroups()
  onFilter()
})

watch(selectedClassGroupId, () => {
  if (initializing) return
  selectedAssignmentId.value = null
  selectedPeriodId.value = null
  assignments.value = []
  periods.value = []
  if (selectedClassGroupId.value) {
    loadAssignments()
    loadPeriods()
  }
  onFilter()
})

watch(selectedAssignmentId, () => {
  if (initializing) return
  onFilter()
})

watch(selectedPeriodId, () => {
  if (initializing) return
  onFilter()
})

async function loadData() {
  loading.value = true
  syncToUrl()
  try {
    const params: Record<string, unknown> = { page: currentPage.value, per_page: perPage.value }
    if (selectedSchoolId.value) params.school_id = selectedSchoolId.value
    if (selectedClassGroupId.value) params.class_group_id = selectedClassGroupId.value
    if (selectedAssignmentId.value) params.teacher_assignment_id = selectedAssignmentId.value
    if (selectedPeriodId.value) params.assessment_period_id = selectedPeriodId.value
    const response = await assessmentService.getGrades(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar notas')
  } finally {
    loading.value = false
  }
}

function onPageChange(event: { page: number; rows: number }) {
  currentPage.value = event.page + 1
  perPage.value = event.rows
  loadData()
}

function onFilter() {
  currentPage.value = 1
  loadData()
}

function clearFilters() {
  clearAll()
  classGroups.value = []
  assignments.value = []
  periods.value = []
  currentPage.value = 1
  loadData()
}

function getSubjectName(grade: Grade): string {
  if (!grade.teacher_assignment) return '--'
  return grade.teacher_assignment.curricular_component?.name
    ?? grade.teacher_assignment.experience_field?.name
    ?? '--'
}

function startEdit(grade: Grade) {
  editingId.value = grade.id
  editNumericValue.value = grade.numeric_value
  editConceptualValue.value = grade.conceptual_value
  editObservations.value = grade.observations ?? ''
}

function cancelEdit() {
  editingId.value = null
}

async function saveEdit(grade: Grade) {
  saving.value = true
  try {
    const updated = await assessmentService.updateGrade(grade.id, {
      numeric_value: editNumericValue.value,
      conceptual_value: editConceptualValue.value,
      observations: editObservations.value,
    })
    const idx = items.value.findIndex(g => g.id === grade.id)
    if (idx !== -1) {
      items.value[idx] = updated
    }
    editingId.value = null
    toast.success('Nota atualizada')
  } catch {
    toast.error('Erro ao atualizar nota')
  } finally {
    saving.value = false
  }
}

function handleDelete(grade: Grade) {
  confirmDelete(async () => {
    try {
      await assessmentService.deleteGrade(grade.id)
      toast.success('Nota excluida')
      loadData()
    } catch {
      toast.error('Erro ao excluir nota')
    }
  })
}

onMounted(async () => {
  if (shouldShowSchoolFilter.value) {
    loadSchools()
  }
  initializing = true
  initFromQuery()
  initializing = false
  if (!shouldShowSchoolFilter.value && userSchoolId.value && !selectedSchoolId.value) {
    selectedSchoolId.value = userSchoolId.value
    return
  }
  if (selectedSchoolId.value) {
    await loadClassGroups()
    if (selectedClassGroupId.value) {
      await Promise.all([loadAssignments(), loadPeriods()])
    }
  }
  loadData()
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Notas</h1>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <div class="mb-4 flex flex-wrap items-end gap-4">
        <div v-if="shouldShowSchoolFilter" class="flex flex-col gap-1.5 w-64">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <Select v-model="selectedSchoolId" :options="schools" optionLabel="name" optionValue="id" placeholder="Todas as escolas" class="w-full" filter showClear />
        </div>
        <div v-if="!shouldShowSchoolFilter && userSchoolName" class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <span class="flex h-[2.375rem] items-center rounded-md border border-[#E0E0E0] bg-[#F5F5F5] px-3 text-sm">{{ userSchoolName }}</span>
        </div>
        <div class="flex flex-col gap-1.5 w-56">
          <label class="text-[0.8125rem] font-medium">Turma</label>
          <Select v-model="selectedClassGroupId" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Todas as turmas" class="w-full" filter showClear :disabled="!selectedSchoolId" />
        </div>
        <div class="flex flex-col gap-1.5 w-56">
          <label class="text-[0.8125rem] font-medium">Disciplina</label>
          <Select v-model="selectedAssignmentId" :options="assignments" optionLabel="label" optionValue="id" placeholder="Todas" class="w-full" filter showClear :disabled="!selectedClassGroupId" />
        </div>
        <div class="flex flex-col gap-1.5 w-48">
          <label class="text-[0.8125rem] font-medium">Periodo</label>
          <Select v-model="selectedPeriodId" :options="periods" optionLabel="name" optionValue="id" placeholder="Todos" class="w-full" showClear :disabled="!selectedClassGroupId" />
        </div>
        <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text @click="clearFilters" />
      </div>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhuma nota encontrada" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column header="Aluno">
          <template #body="{ data }">
            {{ data.student?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Disciplina">
          <template #body="{ data }">
            {{ getSubjectName(data) }}
          </template>
        </Column>
        <Column header="Periodo">
          <template #body="{ data }">
            {{ data.assessment_period?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Instrumento">
          <template #body="{ data }">
            {{ data.assessment_instrument?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Nota">
          <template #body="{ data }">
            <template v-if="editingId === data.id">
              <InputNumber v-model="editNumericValue" :minFractionDigits="0" :maxFractionDigits="2" class="w-20" inputClass="w-full" />
            </template>
            <template v-else>
              {{ data.numeric_value ?? data.conceptual_value ?? '--' }}
            </template>
          </template>
        </Column>
        <Column header="Recuperacao">
          <template #body="{ data }">
            <Tag v-if="data.is_recovery" severity="warn" value="Sim" />
            <span v-else class="text-gray-400">Nao</span>
          </template>
        </Column>
        <Column header="Observacoes">
          <template #body="{ data }">
            <template v-if="editingId === data.id">
              <InputText v-model="editObservations" class="w-full" />
            </template>
            <template v-else>
              {{ data.observations || '--' }}
            </template>
          </template>
        </Column>
        <Column header="Acoes" style="width: 10rem">
          <template #body="{ data }">
            <div class="flex gap-2">
              <template v-if="editingId === data.id">
                <Button icon="pi pi-check" severity="success" text rounded :loading="saving" @click="saveEdit(data)" />
                <Button icon="pi pi-times" severity="secondary" text rounded :disabled="saving" @click="cancelEdit" />
              </template>
              <template v-else>
                <Button icon="pi pi-pencil" text rounded @click="startEdit(data)" />
                <Button icon="pi pi-trash" severity="danger" text rounded @click="handleDelete(data)" />
              </template>
            </div>
          </template>
        </Column>
      </DataTable>

      <Paginator
        v-if="totalRecords > perPage"
        :rows="perPage"
        :totalRecords="totalRecords"
        :first="(currentPage - 1) * perPage"
        :rowsPerPageOptions="[10, 15, 25, 50]"
        class="mt-4 border-t border-[#E0E0E0] pt-3"
        @page="onPageChange"
      />
    </div>
  </div>
</template>
