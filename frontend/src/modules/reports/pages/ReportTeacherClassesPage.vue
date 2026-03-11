<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useListFilters } from '@/composables/useListFilters'
import Button from 'primevue/button'
import Select from 'primevue/select'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import ProgressSpinner from 'primevue/progressspinner'
import EmptyState from '@/shared/components/EmptyState.vue'
import { curriculumService } from '@/services/curriculum.service'
import { peopleService } from '@/services/people.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { useAuthStore } from '@/stores/auth'
import { useSchoolScope } from '@/composables/useSchoolScope'
import { generateTeacherClassesReportPdf } from '@/shared/utils/teacher-classes-report-pdf'
import { exportToCsv } from '@/shared/utils/csv-export'
import type { DailyAssignmentSummary } from '@/types/curriculum'
import type { Teacher } from '@/types/people'
import type { School } from '@/types/school-structure'

const toast = useToast()
const authStore = useAuthStore()
const { shouldShowSchoolFilter, userSchoolId, userSchoolName } = useSchoolScope()

const isTeacher = computed(() => authStore.roleSlug === 'teacher')

const schools = ref<School[]>([])
const teachers = ref<(Teacher & { label: string })[]>([])
const loadingTeachers = ref(false)

const selectedSchoolId = ref<number | null>(null)
const selectedTeacherId = ref<number | null>(null)
const selectedMonth = ref(new Date().getMonth())
const selectedYear = ref(new Date().getFullYear())
const teacherIdResolved = ref<number | null>(null)
let initializing = false

const { initFromQuery, syncToUrl, clearAll } = useListFilters([
  { key: 'school_id', ref: selectedSchoolId, type: 'number' },
  { key: 'teacher_id', ref: selectedTeacherId, type: 'number' },
  { key: 'month', ref: selectedMonth, type: 'number' },
  { key: 'year', ref: selectedYear, type: 'number' },
])

const loading = ref(false)

const monthOptions = [
  { label: 'Janeiro', value: 0 },
  { label: 'Fevereiro', value: 1 },
  { label: 'Marco', value: 2 },
  { label: 'Abril', value: 3 },
  { label: 'Maio', value: 4 },
  { label: 'Junho', value: 5 },
  { label: 'Julho', value: 6 },
  { label: 'Agosto', value: 7 },
  { label: 'Setembro', value: 8 },
  { label: 'Outubro', value: 9 },
  { label: 'Novembro', value: 10 },
  { label: 'Dezembro', value: 11 },
]

const yearOptions = computed(() => {
  const current = new Date().getFullYear()
  return Array.from({ length: 5 }, (_, i) => ({ label: String(current - 2 + i), value: current - 2 + i }))
})

interface ReportRow {
  date: string
  classGroup: string
  component: string
  hasAttendance: boolean
  hasLessonRecord: boolean
}

const reportRows = ref<ReportRow[]>([])

const hasActiveFilters = computed(() =>
  selectedSchoolId.value !== null || selectedTeacherId.value !== null
)

const effectiveTeacherId = computed(() => isTeacher.value ? teacherIdResolved.value : selectedTeacherId.value)

const selectedTeacherName = computed(() => {
  if (isTeacher.value) return authStore.user?.name ?? ''
  const t = teachers.value.find(t => t.id === selectedTeacherId.value)
  return t?.label ?? ''
})

const selectedSchoolName = computed(() => {
  if (!shouldShowSchoolFilter.value) return userSchoolName.value ?? ''
  return schools.value.find(s => s.id === selectedSchoolId.value)?.name ?? ''
})

const selectedPeriodLabel = computed(() => {
  const month = monthOptions.find(m => m.value === selectedMonth.value)?.label ?? ''
  return `${month} ${selectedYear.value}`
})

async function loadSchools() {
  try {
    const response = await schoolStructureService.getSchools({ per_page: 200 })
    schools.value = response.data
  } catch {
    toast.error('Erro ao carregar escolas')
  }
}

async function loadTeachers() {
  const schoolId = selectedSchoolId.value
  if (shouldShowSchoolFilter.value && !schoolId) {
    teachers.value = []
    return
  }
  loadingTeachers.value = true
  try {
    const params: Record<string, unknown> = { active: true, per_page: 200 }
    if (schoolId) params.school_id = schoolId
    const response = await peopleService.getTeachers(params)
    teachers.value = response.data.map(t => ({
      ...t,
      label: t.user?.name ?? `Professor #${t.id}`,
    }))
  } catch {
    toast.error('Erro ao carregar professores')
  } finally {
    loadingTeachers.value = false
  }
}

async function resolveTeacherId() {
  const userId = authStore.user?.id
  if (!userId) return
  try {
    const response = await peopleService.getTeachers({ user_id: userId, per_page: 1 })
    if (response.data.length > 0) {
      teacherIdResolved.value = response.data[0].id
    }
  } catch {
    toast.error('Erro ao identificar professor')
  }
}

watch(selectedSchoolId, () => {
  if (initializing) return
  selectedTeacherId.value = null
  teachers.value = []
  reportRows.value = []
  loadTeachers()
})

async function loadData() {
  const tid = effectiveTeacherId.value
  if (!tid) {
    toast.warn('Selecione um professor para gerar o relatorio')
    return
  }

  loading.value = true
  syncToUrl()
  reportRows.value = []

  try {
    const daysInMonth = new Date(selectedYear.value, selectedMonth.value + 1, 0).getDate()
    const rows: ReportRow[] = []

    for (let d = 1; d <= daysInMonth; d++) {
      const date = new Date(selectedYear.value, selectedMonth.value, d)
      const dow = date.getDay()
      if (dow === 0 || dow === 6) continue

      const dateStr = `${selectedYear.value}-${String(selectedMonth.value + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`
      const summaries: DailyAssignmentSummary[] = await curriculumService.getDailySummary(dateStr, tid)

      for (const s of summaries) {
        rows.push({
          date: date.toLocaleDateString('pt-BR'),
          classGroup: s.class_group?.name
            ? [s.class_group.grade_level?.name, s.class_group.name].filter(Boolean).join(' - ')
            : '--',
          component: s.curricular_component?.name ?? s.experience_field?.name ?? '--',
          hasAttendance: s.has_attendance,
          hasLessonRecord: s.has_lesson_record,
        })
      }
    }

    reportRows.value = rows
  } catch {
    toast.error('Erro ao carregar resumo de aulas')
  } finally {
    loading.value = false
  }
}

function handleExportPdf() {
  if (reportRows.value.length === 0) return
  generateTeacherClassesReportPdf(reportRows.value, {
    schoolName: selectedSchoolName.value,
    teacherName: selectedTeacherName.value,
    period: selectedPeriodLabel.value,
  })
}

function handleExportCsv() {
  if (reportRows.value.length === 0) return
  const headers = ['Data', 'Turma', 'Disciplina', 'Chamada', 'Diario']
  const rows = reportRows.value.map(r => [
    r.date,
    r.classGroup,
    r.component,
    r.hasAttendance ? 'Sim' : 'Nao',
    r.hasLessonRecord ? 'Sim' : 'Nao',
  ])
  exportToCsv(`aulas_professor_${selectedTeacherName.value.replace(/\s+/g, '_')}`, headers, rows)
}

function clearFilters() {
  clearAll()
  teachers.value = []
  reportRows.value = []
  selectedMonth.value = new Date().getMonth()
  selectedYear.value = new Date().getFullYear()
}

onMounted(async () => {
  initializing = true
  initFromQuery()
  initializing = false

  if (isTeacher.value) {
    await resolveTeacherId()
    return
  }

  if (shouldShowSchoolFilter.value) {
    await loadSchools()
    if (selectedSchoolId.value) {
      await loadTeachers()
    }
  } else {
    if (userSchoolId.value && !selectedSchoolId.value) {
      selectedSchoolId.value = userSchoolId.value
      return
    }
    await loadTeachers()
  }
})
</script>

<template>
  <div class="card">
    <div class="mb-4 flex flex-wrap items-end gap-4">
      <template v-if="!isTeacher">
        <div v-if="shouldShowSchoolFilter" class="flex flex-col gap-1.5 w-full md:w-64">
          <label class="text-sm font-medium">Escola</label>
          <Select v-model="selectedSchoolId" :options="schools" optionLabel="name" optionValue="id" placeholder="Selecione a escola" class="w-full" filter showClear />
        </div>
        <div v-if="!shouldShowSchoolFilter && userSchoolName" class="flex flex-col gap-1.5">
          <label class="text-sm font-medium">Escola</label>
          <span class="flex h-[2.375rem] items-center rounded-md border border-md-border bg-md-hover px-3 text-sm">{{ userSchoolName }}</span>
        </div>
        <div class="flex flex-col gap-1.5 w-full md:w-64">
          <label class="text-sm font-medium">Professor</label>
          <Select
            v-model="selectedTeacherId"
            :options="teachers"
            optionLabel="label"
            optionValue="id"
            :placeholder="loadingTeachers ? 'Carregando...' : 'Selecione'"
            :disabled="loadingTeachers || (shouldShowSchoolFilter && !selectedSchoolId)"
            filter
            class="w-full"
          />
        </div>
      </template>
      <div class="flex flex-col gap-1.5 w-full md:w-40">
        <label class="text-sm font-medium">Mes</label>
        <Select v-model="selectedMonth" :options="monthOptions" optionLabel="label" optionValue="value" class="w-full" />
      </div>
      <div class="flex flex-col gap-1.5 w-full md:w-32">
        <label class="text-sm font-medium">Ano</label>
        <Select v-model="selectedYear" :options="yearOptions" optionLabel="label" optionValue="value" class="w-full" />
      </div>
      <Button label="Gerar" icon="pi pi-search" @click="loadData" :disabled="!effectiveTeacherId" />
      <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text @click="clearFilters" />
    </div>

    <div v-if="loading" class="flex items-center justify-center py-16">
      <ProgressSpinner strokeWidth="3" />
    </div>

    <EmptyState v-if="!loading && reportRows.length === 0 && effectiveTeacherId" message="Nenhum dado encontrado. Clique em Gerar para buscar os dados." />
    <EmptyState v-if="!loading && !effectiveTeacherId && !isTeacher" icon="pi pi-user" message="Selecione um professor para gerar o relatorio" />

    <template v-if="!loading && reportRows.length > 0">
      <div class="mb-4 flex gap-2">
        <Button label="Exportar PDF" icon="pi pi-file-pdf" severity="danger" @click="handleExportPdf" />
        <Button label="Exportar CSV" icon="pi pi-file-excel" severity="success" @click="handleExportCsv" />
      </div>

      <DataTable :value="reportRows" stripedRows responsiveLayout="scroll">
        <Column field="date" header="Data" :style="{ width: '100px' }" />
        <Column field="classGroup" header="Turma" sortable />
        <Column field="component" header="Disciplina" sortable />
        <Column header="Chamada" :style="{ width: '100px', textAlign: 'center' }">
          <template #body="{ data }">
            <span :class="data.hasAttendance ? 'text-green-600 font-bold' : 'text-red-600 font-bold'">
              {{ data.hasAttendance ? 'Sim' : 'Nao' }}
            </span>
          </template>
        </Column>
        <Column header="Diario" :style="{ width: '100px', textAlign: 'center' }">
          <template #body="{ data }">
            <span :class="data.hasLessonRecord ? 'text-green-600 font-bold' : 'text-red-600 font-bold'">
              {{ data.hasLessonRecord ? 'Sim' : 'Nao' }}
            </span>
          </template>
        </Column>
      </DataTable>
    </template>
  </div>
</template>
