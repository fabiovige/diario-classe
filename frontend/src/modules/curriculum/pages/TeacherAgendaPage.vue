<script setup lang="ts">
import { onMounted, ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'
import Select from 'primevue/select'
import ProgressSpinner from 'primevue/progressspinner'
import EmptyState from '@/shared/components/EmptyState.vue'
import { curriculumService } from '@/services/curriculum.service'
import { peopleService } from '@/services/people.service'
import { useToast } from '@/composables/useToast'
import { useAuthStore } from '@/stores/auth'
import type { ClassSchedule, TimeSlot } from '@/types/curriculum'
import type { Teacher } from '@/types/people'

const router = useRouter()
const toast = useToast()
const authStore = useAuthStore()

const isTeacher = computed(() => authStore.roleSlug === 'teacher')
const isManager = computed(() => ['admin', 'director', 'coordinator'].includes(authStore.roleSlug ?? ''))

const currentYear = ref(new Date().getFullYear())
const currentMonth = ref(new Date().getMonth())
const teacherId = ref<number | null>(null)
const selectedTeacherId = ref<number | null>(null)
const teachers = ref<(Teacher & { label: string })[]>([])
const loadingTeachers = ref(false)

const schedules = ref<ClassSchedule[]>([])
const timeSlots = ref<TimeSlot[]>([])
const loading = ref(false)

const monthLabel = computed(() => {
  const date = new Date(currentYear.value, currentMonth.value, 1)
  return date.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
    .replace(/^\w/, c => c.toUpperCase())
})

const weekDays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab']

interface CalendarDay {
  date: number
  isCurrentMonth: boolean
  isToday: boolean
  isWeekend: boolean
  dayOfWeek: number
  fullDate: string
  classes: ClassSchedule[]
}

const calendarDays = computed<CalendarDay[]>(() => {
  const firstDay = new Date(currentYear.value, currentMonth.value, 1)
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0)
  const startDow = firstDay.getDay()
  const today = new Date()
  const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`

  const days: CalendarDay[] = []

  const prevMonthLast = new Date(currentYear.value, currentMonth.value, 0)
  for (let i = startDow - 1; i >= 0; i--) {
    const d = prevMonthLast.getDate() - i
    const date = new Date(currentYear.value, currentMonth.value - 1, d)
    days.push({
      date: d,
      isCurrentMonth: false,
      isToday: false,
      isWeekend: date.getDay() === 0 || date.getDay() === 6,
      dayOfWeek: date.getDay(),
      fullDate: formatDate(date),
      classes: [],
    })
  }

  for (let d = 1; d <= lastDay.getDate(); d++) {
    const date = new Date(currentYear.value, currentMonth.value, d)
    const dow = date.getDay()
    const jsDowToIsoDow: Record<number, number> = { 0: 7, 1: 1, 2: 2, 3: 3, 4: 4, 5: 5, 6: 6 }
    const isoDow = jsDowToIsoDow[dow]
    const daySchedules = (dow >= 1 && dow <= 5)
      ? schedules.value.filter(s => s.day_of_week === isoDow)
      : []

    days.push({
      date: d,
      isCurrentMonth: true,
      isToday: formatDate(date) === todayStr,
      isWeekend: dow === 0 || dow === 6,
      dayOfWeek: dow,
      fullDate: formatDate(date),
      classes: daySchedules,
    })
  }

  const remaining = 42 - days.length
  for (let d = 1; d <= remaining; d++) {
    const date = new Date(currentYear.value, currentMonth.value + 1, d)
    days.push({
      date: d,
      isCurrentMonth: false,
      isToday: false,
      isWeekend: date.getDay() === 0 || date.getDay() === 6,
      dayOfWeek: date.getDay(),
      fullDate: formatDate(date),
      classes: [],
    })
  }

  return days
})

function formatDate(date: Date): string {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

function prevMonth() {
  if (currentMonth.value === 0) {
    currentMonth.value = 11
    currentYear.value--
    return
  }
  currentMonth.value--
}

function nextMonth() {
  if (currentMonth.value === 11) {
    currentMonth.value = 0
    currentYear.value++
    return
  }
  currentMonth.value++
}

function goToday() {
  const now = new Date()
  currentYear.value = now.getFullYear()
  currentMonth.value = now.getMonth()
}

function openDay(day: CalendarDay) {
  if (!day.isCurrentMonth) return
  if (day.isWeekend) return
  if (day.classes.length === 0) return
  router.push(`/my-classes?date=${day.fullDate}`)
}

const COMPONENT_COLORS: Record<string, string> = {
  LP: 'bg-blue-200 text-blue-900',
  MAT: 'bg-green-200 text-green-900',
  CIE: 'bg-purple-200 text-purple-900',
  HIS: 'bg-orange-200 text-orange-900',
  GEO: 'bg-teal-200 text-teal-900',
  ART: 'bg-pink-200 text-pink-900',
  EDF: 'bg-red-200 text-red-900',
  ING: 'bg-indigo-200 text-indigo-900',
  ER: 'bg-amber-200 text-amber-900',
}

function getScheduleColor(schedule: ClassSchedule): string {
  const code = schedule.teacher_assignment?.curricular_component?.code ?? ''
  return COMPONENT_COLORS[code] ?? 'bg-gray-200 text-gray-800'
}

function getScheduleLabel(schedule: ClassSchedule): string {
  const a = schedule.teacher_assignment
  if (!a) return ''
  const component = a.curricular_component?.code ?? a.experience_field?.name ?? ''
  const classGroup = a.class_group?.name ?? ''
  return `${component} ${classGroup}`.trim()
}

const uniqueSlots = computed(() => {
  const slotMap = new Map<number, TimeSlot>()
  for (const slot of timeSlots.value) {
    if (slot.type === 'class' && !slotMap.has(slot.number)) {
      slotMap.set(slot.number, slot)
    }
  }
  return Array.from(slotMap.values()).sort((a, b) => a.number - b.number)
})

function getSchedulesBySlot(daySchedules: ClassSchedule[], slotNumber: number): ClassSchedule[] {
  return daySchedules.filter(s => s.time_slot?.number === slotNumber)
}

async function resolveTeacherId() {
  const userId = authStore.user?.id
  if (!userId) return

  try {
    const response = await peopleService.getTeachers({ user_id: userId, per_page: 1 })
    if (response.data.length > 0) {
      teacherId.value = response.data[0].id
    }
  } catch {
    toast.error('Erro ao identificar professor')
  }
}

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

async function loadSchedules() {
  const tid = isManager.value ? selectedTeacherId.value : teacherId.value
  if (!tid) {
    schedules.value = []
    timeSlots.value = []
    return
  }

  loading.value = true
  try {
    const response = await curriculumService.getClassSchedules({ teacher_id: tid })
    schedules.value = (response as any).data ?? response

    const allSlots: TimeSlot[] = []
    for (const schedule of schedules.value) {
      if (schedule.time_slot && !allSlots.find(s => s.id === schedule.time_slot!.id)) {
        allSlots.push(schedule.time_slot)
      }
    }
    timeSlots.value = allSlots.sort((a, b) => a.number - b.number)
  } catch {
    toast.error('Erro ao carregar agenda')
  } finally {
    loading.value = false
  }
}

watch(selectedTeacherId, loadSchedules)

onMounted(async () => {
  if (isManager.value) {
    await loadTeachers()
    return
  }
  await resolveTeacherId()
  await loadSchedules()
})
</script>

<template>
  <div class="p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <h1 class="text-2xl font-semibold text-fluent-primary">Agenda</h1>
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
    </div>

    <div class="rounded-lg border border-fluent-border bg-white p-6 shadow-sm">
      <div v-if="isManager && !selectedTeacherId && !loading">
        <EmptyState icon="pi pi-user" message="Selecione um professor para visualizar a agenda" />
      </div>

      <div v-if="loading" class="flex items-center justify-center py-16">
        <ProgressSpinner strokeWidth="3" />
      </div>

      <div v-if="!loading && (isTeacher ? teacherId : selectedTeacherId)">
        <EmptyState v-if="schedules.length === 0" icon="pi pi-calendar" message="Nenhuma aula cadastrada na grade" />

        <template v-if="schedules.length > 0">
          <div class="mb-5 flex items-center justify-between">
            <Button icon="pi pi-chevron-left" text rounded @click="prevMonth" />
            <div class="flex items-center gap-3">
              <h2 class="text-lg font-semibold capitalize text-[#323130]">{{ monthLabel }}</h2>
              <Button label="Hoje" size="small" severity="secondary" @click="goToday" />
            </div>
            <Button icon="pi pi-chevron-right" text rounded @click="nextMonth" />
          </div>

          <div class="grid grid-cols-7 gap-px overflow-hidden rounded-lg border border-fluent-border bg-fluent-border">
            <div
              v-for="wd in weekDays"
              :key="wd"
              class="bg-gray-50 px-2 py-2 text-center text-xs font-semibold text-gray-500"
            >
              {{ wd }}
            </div>

            <div
              v-for="(day, idx) in calendarDays"
              :key="idx"
              class="min-h-[100px] bg-white p-1.5 transition-colors"
              :class="{
                'bg-gray-50/50 text-gray-300': !day.isCurrentMonth,
                'bg-blue-50/40': day.isToday,
                'bg-gray-50': day.isWeekend && day.isCurrentMonth,
                'cursor-pointer hover:bg-blue-50': day.isCurrentMonth && !day.isWeekend && day.classes.length > 0,
              }"
              @click="openDay(day)"
            >
              <div
                class="mb-1 text-right text-xs font-medium"
                :class="{
                  'text-gray-300': !day.isCurrentMonth,
                  'text-gray-400': day.isWeekend && day.isCurrentMonth,
                  'text-[#323130]': day.isCurrentMonth && !day.isWeekend,
                }"
              >
                <span
                  v-if="day.isToday"
                  class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-[#0078D4] text-white"
                >{{ day.date }}</span>
                <span v-else>{{ day.date }}</span>
              </div>

              <div v-if="day.isCurrentMonth && !day.isWeekend && day.classes.length > 0" class="flex flex-col gap-0.5">
                <template v-for="slot in uniqueSlots" :key="slot.id">
                  <div
                    v-for="schedule in getSchedulesBySlot(day.classes, slot.number)"
                    :key="schedule.id"
                    class="truncate rounded px-1 py-0.5 text-[0.6rem] font-medium leading-tight"
                    :class="getScheduleColor(schedule)"
                    :title="`${slot.start_time} - ${schedule.teacher_assignment?.curricular_component?.name ?? schedule.teacher_assignment?.experience_field?.name ?? ''} - ${schedule.teacher_assignment?.class_group?.name ?? ''}`"
                  >
                    {{ getScheduleLabel(schedule) }}
                  </div>
                </template>
              </div>
            </div>
          </div>

          <div class="mt-4 flex flex-wrap gap-3">
            <div
              v-for="(color, code) in COMPONENT_COLORS"
              :key="code"
              class="flex items-center gap-1.5"
            >
              <span class="inline-block h-3 w-3 rounded" :class="color.split(' ')[0]" />
              <span class="text-xs text-gray-600">{{ code }}</span>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
