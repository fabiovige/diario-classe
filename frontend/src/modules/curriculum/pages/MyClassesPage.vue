<script setup lang="ts">
import { onMounted, ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
import EmptyState from '@/shared/components/EmptyState.vue'
import { curriculumService } from '@/services/curriculum.service'
import { peopleService } from '@/services/people.service'
import { useToast } from '@/composables/useToast'
import { useAuthStore } from '@/stores/auth'
import type { DailyAssignmentSummary } from '@/types/curriculum'
import type { Teacher } from '@/types/people'

const route = useRoute()
const toast = useToast()
const authStore = useAuthStore()

const isTeacher = computed(() => authStore.roleSlug === 'teacher')
const isManager = computed(() => ['admin', 'director', 'coordinator'].includes(authStore.roleSlug ?? ''))

const initialDate = typeof route.query.date === 'string' ? route.query.date : new Date().toISOString().split('T')[0]
const selectedDate = ref(initialDate)
const selectedTeacherId = ref<number | null>(null)
const teachers = ref<(Teacher & { label: string })[]>([])
const assignments = ref<DailyAssignmentSummary[]>([])
const loading = ref(false)
const loadingTeachers = ref(false)

const canLoad = computed(() => {
  if (isTeacher.value) return true
  return !!selectedTeacherId.value
})

async function loadTeachers() {
  if (!isManager.value) return
  loadingTeachers.value = true
  try {
    const response = await peopleService.getTeachers({ active: true, per_page: 200 })
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

async function loadSummary() {
  if (!canLoad.value) return
  loading.value = true
  try {
    const params: Record<string, unknown> = { date: selectedDate.value }
    if (isManager.value && selectedTeacherId.value) {
      params.teacher_id = selectedTeacherId.value
    }
    assignments.value = await curriculumService.getDailySummary(selectedDate.value, isManager.value ? selectedTeacherId.value : undefined)
  } catch {
    toast.error('Erro ao carregar aulas do dia')
  } finally {
    loading.value = false
  }
}

function getClassLabel(a: DailyAssignmentSummary): string {
  const parts = [
    a.class_group?.grade_level?.name,
    a.class_group?.name,
    a.class_group?.shift?.name_label,
  ].filter(Boolean)
  return parts.join(' - ')
}

function getSubjectLabel(a: DailyAssignmentSummary): string {
  return a.curricular_component?.name ?? a.experience_field?.name ?? ''
}

watch(selectedDate, () => {
  if (canLoad.value) loadSummary()
})

watch(selectedTeacherId, () => {
  if (canLoad.value) loadSummary()
})

onMounted(async () => {
  await loadTeachers()
  if (isTeacher.value) {
    loadSummary()
  }
})
</script>

<template>
  <div class="p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <h1 class="text-2xl font-semibold text-[#0078D4]">{{ isTeacher ? 'Minhas Aulas' : 'Aulas do Professor' }}</h1>
      <div class="flex flex-wrap items-center gap-3">
        <div v-if="isManager" class="flex items-center gap-2">
          <label class="text-[0.8125rem] font-medium">Professor:</label>
          <Select
            v-model="selectedTeacherId"
            :options="teachers"
            optionLabel="label"
            optionValue="id"
            :placeholder="loadingTeachers ? 'Carregando...' : 'Selecione'"
            :disabled="loadingTeachers"
            filter
            class="w-64"
          />
        </div>
        <div class="flex items-center gap-2">
          <label class="text-[0.8125rem] font-medium">Data:</label>
          <InputText v-model="selectedDate" type="date" />
        </div>
      </div>
    </div>

    <div v-if="isManager && !selectedTeacherId && !loading" class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <EmptyState icon="pi pi-user" message="Selecione um professor para visualizar suas aulas" />
    </div>

    <div v-if="loading" class="flex items-center justify-center py-16">
      <ProgressSpinner strokeWidth="3" />
    </div>

    <EmptyState
      v-if="!loading && canLoad && assignments.length === 0"
      icon="pi pi-calendar"
      message="Nenhuma aula encontrada para esta data"
    />

    <div v-if="!loading && assignments.length > 0" class="grid grid-cols-[repeat(auto-fill,minmax(320px,1fr))] gap-4">
      <div
        v-for="a in assignments"
        :key="a.id"
        class="rounded-lg border border-[#E0E0E0] bg-white p-5 shadow-sm transition-shadow hover:shadow-md"
      >
        <h3 class="text-base font-semibold text-[#323130]">{{ getClassLabel(a) }}</h3>
        <p class="mt-1 text-[0.875rem] text-[#605E5C]">{{ getSubjectLabel(a) }}</p>

        <div class="mt-4 flex flex-wrap gap-2">
          <span
            class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium"
            :class="a.has_attendance ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
          >
            <i :class="a.has_attendance ? 'pi pi-check' : 'pi pi-times'" class="text-[0.625rem]" />
            Chamada
          </span>
          <span
            class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium"
            :class="a.has_lesson_record ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
          >
            <i :class="a.has_lesson_record ? 'pi pi-check' : 'pi pi-times'" class="text-[0.625rem]" />
            Registro
          </span>
          <span
            v-if="a.has_open_period"
            class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-800"
          >
            <i class="pi pi-chart-bar text-[0.625rem]" />
            Avaliacao
          </span>
        </div>

        <div class="mt-4 flex justify-end">
          <Button
            label="Abrir Aula"
            icon="pi pi-arrow-right"
            iconPos="right"
            size="small"
            @click="$router.push(`/my-classes/${a.id}/session?date=${selectedDate}`)"
          />
        </div>
      </div>
    </div>
  </div>
</template>
