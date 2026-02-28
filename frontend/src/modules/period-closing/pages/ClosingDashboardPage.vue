<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useListFilters } from '@/composables/useListFilters'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Paginator from 'primevue/paginator'
import Select from 'primevue/select'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import ProgressBar from 'primevue/progressbar'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import MetricCard from '@/shared/components/MetricCard.vue'
import { periodClosingService } from '@/services/period-closing.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { useSchoolScope } from '@/composables/useSchoolScope'
import { useAuthStore } from '@/stores/auth'
import { periodClosingStatusLabel } from '@/shared/utils/enum-labels'
import { extractApiError } from '@/shared/utils/api-error'
import type { PeriodClosing, TeacherPendency } from '@/types/period-closing'
import type { School, AcademicYear } from '@/types/school-structure'

const router = useRouter()
const toast = useToast()
const auth = useAuthStore()
const { shouldShowSchoolFilter, userSchoolId, userSchoolName } = useSchoolScope()

const isTeacher = computed(() => auth.hasRole('teacher'))

const items = ref<PeriodClosing[]>([])
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(15)

const schools = ref<School[]>([])
const academicYears = ref<AcademicYear[]>([])
const selectedSchoolId = ref<number | null>(null)
const selectedAcademicYearId = ref<number | null>(null)
let initializing = false

const { initFromQuery, syncToUrl, clearAll } = useListFilters([
  { key: 'school_id', ref: selectedSchoolId, type: 'number' },
  { key: 'academic_year_id', ref: selectedAcademicYearId, type: 'number' },
  { key: 'page', ref: currentPage, type: 'number' },
])

const dashboard = ref<Record<string, number> | null>(null)

const myClosings = ref<PeriodClosing[]>([])
const myLoading = ref(false)

const pendencies = ref<TeacherPendency[]>([])
const pendenciesLoading = ref(false)
const expandedTeachers = ref<Record<number, boolean>>({})

const activeTab = ref('0')

const hasActiveFilters = computed(() => selectedSchoolId.value !== null || selectedAcademicYearId.value !== null)

async function loadSchools() {
  try {
    const response = await schoolStructureService.getSchools({ per_page: 200 })
    schools.value = response.data
  } catch {
    toast.error('Erro ao carregar escolas')
  }
}

async function loadAcademicYears() {
  if (!selectedSchoolId.value) {
    academicYears.value = []
    return
  }
  try {
    const response = await schoolStructureService.getAcademicYears({ school_id: selectedSchoolId.value, per_page: 50 })
    academicYears.value = response.data
  } catch {
    toast.error('Erro ao carregar anos letivos')
  }
}

watch(selectedSchoolId, () => {
  if (initializing) return
  selectedAcademicYearId.value = null
  currentPage.value = 1
  loadAcademicYears()
  loadDashboard()
  loadData()
  loadPendencies()
})

watch(selectedAcademicYearId, () => {
  if (initializing) return
  currentPage.value = 1
  loadDashboard()
  loadData()
  loadPendencies()
})

async function loadDashboard() {
  try {
    const params: Record<string, unknown> = {}
    if (selectedSchoolId.value) params.school_id = selectedSchoolId.value
    dashboard.value = await periodClosingService.getDashboard(params)
  } catch {
    toast.error('Erro ao carregar dashboard')
  }
}

async function loadData() {
  loading.value = true
  syncToUrl()
  try {
    const params: Record<string, unknown> = {
      page: currentPage.value,
      per_page: perPage.value,
    }
    if (selectedSchoolId.value) params.school_id = selectedSchoolId.value
    const response = await periodClosingService.getClosings(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar fechamentos')
  } finally {
    loading.value = false
  }
}

async function loadMyClosings() {
  myLoading.value = true
  try {
    const params: Record<string, unknown> = {}
    if (selectedAcademicYearId.value) params.academic_year_id = selectedAcademicYearId.value
    myClosings.value = await periodClosingService.getMyClosings(params)
  } catch {
    toast.error('Erro ao carregar meus fechamentos')
  } finally {
    myLoading.value = false
  }
}

async function loadPendencies() {
  if (!selectedSchoolId.value || !selectedAcademicYearId.value) {
    pendencies.value = []
    return
  }
  pendenciesLoading.value = true
  try {
    pendencies.value = await periodClosingService.getPendencies({
      school_id: selectedSchoolId.value,
      academic_year_id: selectedAcademicYearId.value,
    })
  } catch {
    toast.error('Erro ao carregar pendencias')
  } finally {
    pendenciesLoading.value = false
  }
}

async function handleTeacherClose(closing: PeriodClosing) {
  try {
    await periodClosingService.teacherClose(closing.id)
    toast.success('Bimestre fechado com sucesso')
    loadMyClosings()
  } catch (error) {
    toast.error(extractApiError(error, 'Erro ao fechar bimestre'))
  }
}

async function handleBulkTeacherClose() {
  const pendingClosings = myClosings.value.filter(c => c.status === 'pending')
  if (pendingClosings.length === 0) {
    toast.info('Nenhum fechamento pendente')
    return
  }

  const grouped = new Map<string, { class_group_id: number; teacher_assignment_id: number }>()
  for (const c of pendingClosings) {
    const key = `${c.class_group_id}-${c.teacher_assignment_id}`
    if (!grouped.has(key)) {
      grouped.set(key, { class_group_id: c.class_group_id, teacher_assignment_id: c.teacher_assignment_id })
    }
  }

  let totalClosed = 0
  let totalFailed = 0

  for (const params of grouped.values()) {
    try {
      const result = await periodClosingService.bulkTeacherClose(params)
      totalClosed += result.closed.length
      totalFailed += result.failed.length
    } catch {
      totalFailed++
    }
  }

  if (totalClosed > 0) toast.success(`${totalClosed} fechamento(s) concluido(s)`)
  if (totalFailed > 0) toast.warn(`${totalFailed} fechamento(s) com pendencias`)
  loadMyClosings()
}

function disciplineName(row: PeriodClosing): string {
  return row.teacher_assignment?.curricular_component?.name
    ?? row.teacher_assignment?.experience_field?.name
    ?? '--'
}

function turmaName(row: PeriodClosing): string {
  if (!row.class_group) return '--'
  const grade = row.class_group.grade_level?.name ?? ''
  const shift = row.class_group.shift?.name_label ?? ''
  const parts = [row.class_group.name, grade, shift].filter(Boolean)
  return parts.join(' - ')
}

function toggleTeacher(teacherId: number) {
  expandedTeachers.value[teacherId] = !expandedTeachers.value[teacherId]
}

function pendencyProgress(p: TeacherPendency): number {
  const total = p.closings.length + p.total_pending
  if (total === 0) return 0
  return Math.round(((total - p.total_pending) / total) * 100)
}

function onPageChange(event: { page: number; rows: number }) {
  currentPage.value = event.page + 1
  perPage.value = event.rows
  loadData()
}

function clearFilters() {
  clearAll()
  currentPage.value = 1
  if (isTeacher.value) {
    loadMyClosings()
    return
  }
  loadDashboard()
  loadData()
  loadPendencies()
}

const myPendingCount = computed(() => myClosings.value.filter(c => c.status === 'pending').length)
const myClosedCount = computed(() => myClosings.value.filter(c => c.status === 'closed').length)

onMounted(() => {
  if (isTeacher.value) {
    loadMyClosings()
    return
  }

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
  loadDashboard()
  loadData()
  if (selectedSchoolId.value && selectedAcademicYearId.value) {
    loadPendencies()
  }
})
</script>

<template>
  <div class="p-6">
    <!-- VISAO PROFESSOR -->
    <template v-if="isTeacher">
      <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Meus Fechamentos</h1>

      <div v-if="myClosings.length > 0" class="mb-6 grid grid-cols-[repeat(auto-fill,minmax(150px,1fr))] gap-4">
        <MetricCard title="Total" :value="myClosings.length" label="Fechamentos" color="#0078D4" icon="pi pi-list" />
        <MetricCard title="Pendentes" :value="myPendingCount" label="Para fechar" color="#9D5D00" icon="pi pi-clock" />
        <MetricCard title="Fechados" :value="myClosedCount" label="Concluidos" color="#0F7B0F" icon="pi pi-lock" />
      </div>

      <div v-if="myPendingCount > 0" class="mb-4">
        <Button label="Fechar Todos Pendentes" icon="pi pi-check-circle" severity="success" @click="handleBulkTeacherClose" />
      </div>

      <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
        <EmptyState v-if="!myLoading && myClosings.length === 0" message="Nenhum fechamento encontrado" />

        <DataTable v-if="myClosings.length > 0" :value="myClosings" :loading="myLoading" stripedRows responsiveLayout="scroll">
          <Column header="Turma">
            <template #body="{ data }">{{ turmaName(data) }}</template>
          </Column>
          <Column header="Disciplina">
            <template #body="{ data }">{{ disciplineName(data) }}</template>
          </Column>
          <Column header="Periodo">
            <template #body="{ data }">{{ data.assessment_period?.name ?? '--' }}</template>
          </Column>
          <Column header="Notas">
            <template #body="{ data }">
              <i :class="data.all_grades_complete ? 'pi pi-check-circle text-[#0F7B0F]' : 'pi pi-times-circle text-[#C42B1C]'" />
            </template>
          </Column>
          <Column header="Frequencia">
            <template #body="{ data }">
              <i :class="data.all_attendance_complete ? 'pi pi-check-circle text-[#0F7B0F]' : 'pi pi-times-circle text-[#C42B1C]'" />
            </template>
          </Column>
          <Column header="Diario">
            <template #body="{ data }">
              <i :class="data.all_lesson_records_complete ? 'pi pi-check-circle text-[#0F7B0F]' : 'pi pi-times-circle text-[#C42B1C]'" />
            </template>
          </Column>
          <Column header="Status">
            <template #body="{ data }">
              <StatusBadge :status="data.status" :label="periodClosingStatusLabel(data.status)" />
            </template>
          </Column>
          <Column header="Acoes" :style="{ width: '140px' }">
            <template #body="{ data }">
              <div class="flex gap-1">
                <Button v-if="data.status === 'pending'" label="Fechar" icon="pi pi-check" size="small" severity="success" @click="handleTeacherClose(data)" />
                <Button icon="pi pi-eye" text rounded size="small" @click="router.push(`/period-closing/${data.id}`)" />
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </template>

    <!-- VISAO ADMIN/DIRETOR/COORDENADOR -->
    <template v-else>
      <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Fechamento de Periodo</h1>

      <div class="mb-6 flex flex-wrap items-end gap-4">
        <div v-if="shouldShowSchoolFilter" class="flex flex-col gap-1.5 w-64">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <Select v-model="selectedSchoolId" :options="schools" optionLabel="name" optionValue="id" placeholder="Todas as escolas" class="w-full" filter showClear />
        </div>
        <div v-if="!shouldShowSchoolFilter && userSchoolName" class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <span class="flex h-[2.375rem] items-center rounded-md border border-[#E0E0E0] bg-[#F5F5F5] px-3 text-sm">{{ userSchoolName }}</span>
        </div>
        <div class="flex flex-col gap-1.5 w-48">
          <label class="text-[0.8125rem] font-medium">Ano Letivo</label>
          <Select v-model="selectedAcademicYearId" :options="academicYears" optionLabel="year" optionValue="id" placeholder="Selecione" class="w-full" :disabled="!selectedSchoolId" showClear />
        </div>
        <Button v-if="hasActiveFilters" label="Limpar filtros" icon="pi pi-filter-slash" text @click="clearFilters" />
      </div>

      <div v-if="dashboard" class="mb-6 grid grid-cols-[repeat(auto-fill,minmax(150px,1fr))] gap-4">
        <MetricCard title="Total" :value="dashboard.total ?? 0" label="Fechamentos" color="#0078D4" icon="pi pi-list" />
        <MetricCard title="Pendentes" :value="dashboard.pending ?? 0" label="Aguardando envio" color="#9D5D00" icon="pi pi-clock" />
        <MetricCard title="Em Validacao" :value="dashboard.in_validation ?? 0" label="Aguardando aprovacao" color="#005A9E" icon="pi pi-hourglass" />
        <MetricCard title="Aprovados" :value="dashboard.approved ?? 0" label="Prontos para fechar" color="#0F7B0F" icon="pi pi-check" />
        <MetricCard title="Fechados" :value="dashboard.closed ?? 0" label="Concluidos" color="#0F7B0F" icon="pi pi-lock" />
      </div>

      <div class="rounded-lg border border-[#E0E0E0] bg-white shadow-sm">
        <Tabs v-model:value="activeTab">
          <TabList>
            <Tab value="0">Por Professor</Tab>
            <Tab value="1">Por Turma</Tab>
          </TabList>
          <TabPanels>
            <!-- ABA POR PROFESSOR -->
            <TabPanel value="0">
              <div class="p-6">
                <EmptyState v-if="!pendenciesLoading && pendencies.length === 0 && selectedSchoolId && selectedAcademicYearId" message="Nenhuma pendencia encontrada. Todos os fechamentos estao concluidos." />
                <EmptyState v-if="!pendenciesLoading && (!selectedSchoolId || !selectedAcademicYearId)" message="Selecione uma escola e um ano letivo para ver as pendencias por professor." />

                <div v-if="pendencies.length > 0" class="space-y-3">
                  <div v-for="teacher in pendencies" :key="teacher.teacher_id" class="rounded-lg border border-[#E0E0E0]">
                    <div class="flex cursor-pointer items-center gap-4 px-4 py-3 hover:bg-[#F5F5F5]" @click="toggleTeacher(teacher.teacher_id)">
                      <i :class="expandedTeachers[teacher.teacher_id] ? 'pi pi-chevron-down' : 'pi pi-chevron-right'" class="text-sm text-[#616161]" />
                      <div class="flex-1">
                        <span class="font-medium">{{ teacher.teacher_name }}</span>
                        <span class="ml-2 text-sm text-[#616161]">({{ teacher.total_pending }} pendente(s))</span>
                      </div>
                      <div class="w-32">
                        <ProgressBar :value="pendencyProgress(teacher)" :showValue="true" class="h-5" />
                      </div>
                    </div>
                    <div v-if="expandedTeachers[teacher.teacher_id]" class="border-t border-[#E0E0E0] px-4 py-3">
                      <DataTable :value="teacher.closings" stripedRows size="small">
                        <Column header="Turma" field="class_group" />
                        <Column header="Disciplina" field="subject" />
                        <Column header="Periodo" field="period" />
                        <Column header="Notas">
                          <template #body="{ data }">
                            <i :class="data.grades_complete ? 'pi pi-check-circle text-[#0F7B0F]' : 'pi pi-times-circle text-[#C42B1C]'" />
                          </template>
                        </Column>
                        <Column header="Frequencia">
                          <template #body="{ data }">
                            <i :class="data.attendance_complete ? 'pi pi-check-circle text-[#0F7B0F]' : 'pi pi-times-circle text-[#C42B1C]'" />
                          </template>
                        </Column>
                        <Column header="Diario">
                          <template #body="{ data }">
                            <i :class="data.lesson_records_complete ? 'pi pi-check-circle text-[#0F7B0F]' : 'pi pi-times-circle text-[#C42B1C]'" />
                          </template>
                        </Column>
                        <Column header="Status">
                          <template #body="{ data }">
                            <StatusBadge :status="data.status" :label="periodClosingStatusLabel(data.status)" />
                          </template>
                        </Column>
                      </DataTable>
                    </div>
                  </div>
                </div>
              </div>
            </TabPanel>

            <!-- ABA POR TURMA -->
            <TabPanel value="1">
              <div class="p-6">
                <EmptyState v-if="!loading && items.length === 0" message="Nenhum fechamento encontrado" />

                <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
                  <Column header="Turma">
                    <template #body="{ data }">{{ turmaName(data) }}</template>
                  </Column>
                  <Column header="Disciplina">
                    <template #body="{ data }">{{ disciplineName(data) }}</template>
                  </Column>
                  <Column header="Periodo">
                    <template #body="{ data }">{{ data.assessment_period?.name ?? '--' }}</template>
                  </Column>
                  <Column header="Status">
                    <template #body="{ data }">
                      <StatusBadge :status="data.status" :label="periodClosingStatusLabel(data.status)" />
                    </template>
                  </Column>
                  <Column header="Notas">
                    <template #body="{ data }">
                      <i :class="data.all_grades_complete ? 'pi pi-check-circle text-[#0F7B0F]' : 'pi pi-times-circle text-[#C42B1C]'" />
                    </template>
                  </Column>
                  <Column header="Frequencia">
                    <template #body="{ data }">
                      <i :class="data.all_attendance_complete ? 'pi pi-check-circle text-[#0F7B0F]' : 'pi pi-times-circle text-[#C42B1C]'" />
                    </template>
                  </Column>
                  <Column header="Diario">
                    <template #body="{ data }">
                      <i :class="data.all_lesson_records_complete ? 'pi pi-check-circle text-[#0F7B0F]' : 'pi pi-times-circle text-[#C42B1C]'" />
                    </template>
                  </Column>
                  <Column header="Acoes" :style="{ width: '80px' }">
                    <template #body="{ data }">
                      <Button icon="pi pi-eye" text rounded @click="router.push(`/period-closing/${data.id}`)" />
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
            </TabPanel>
          </TabPanels>
        </Tabs>
      </div>
    </template>
  </div>
</template>
