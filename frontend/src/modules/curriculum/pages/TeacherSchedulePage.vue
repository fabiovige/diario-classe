<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import { curriculumService } from '@/services/curriculum.service'
import { peopleService } from '@/services/people.service'
import { useToast } from '@/composables/useToast'
import { useAuthStore } from '@/stores/auth'
import type { ClassSchedule, TimeSlot } from '@/types/curriculum'

const toast = useToast()
const authStore = useAuthStore()

const loading = ref(false)
const teacherId = ref<number | null>(null)
const timeSlots = ref<TimeSlot[]>([])
const schedules = ref<ClassSchedule[]>([])

const days = [
  { value: 1, label: 'Segunda' },
  { value: 2, label: 'Terca' },
  { value: 3, label: 'Quarta' },
  { value: 4, label: 'Quinta' },
  { value: 5, label: 'Sexta' },
]

const uniqueSlots = computed(() => {
  const slotMap = new Map<string, TimeSlot>()
  for (const slot of timeSlots.value) {
    const key = `${slot.number}-${slot.type}`
    if (!slotMap.has(key)) {
      slotMap.set(key, slot)
    }
  }
  return Array.from(slotMap.values()).sort((a, b) => a.number - b.number)
})

const grid = computed(() => {
  return uniqueSlots.value.map(slot => ({
    slot,
    isBreak: slot.type === 'break',
    cells: days.map(day => {
      const matchingSchedules = schedules.value.filter(s => {
        const ts = s.time_slot
        return ts && ts.number === slot.number && ts.type === slot.type && s.day_of_week === day.value
      })
      return {
        day: day.value,
        schedules: matchingSchedules,
      }
    }),
  }))
})

async function resolveTeacherId() {
  const userId = authStore.user?.id
  if (!userId) return

  try {
    const response = await peopleService.getTeachers({ user_id: userId, per_page: 1 })
    const teachers = response.data
    if (teachers.length > 0) {
      teacherId.value = teachers[0].id
    }
  } catch {
    toast.error('Erro ao identificar professor')
  }
}

async function loadData() {
  if (!teacherId.value) return

  loading.value = true
  try {
    const response = await curriculumService.getClassSchedules({ teacher_id: teacherId.value })
    schedules.value = (response as any).data ?? response

    const allSlots: TimeSlot[] = []
    for (const schedule of schedules.value) {
      if (schedule.time_slot && !allSlots.find(s => s.id === schedule.time_slot!.id)) {
        allSlots.push(schedule.time_slot)
      }
    }
    timeSlots.value = allSlots.sort((a, b) => a.number - b.number)
  } catch {
    toast.error('Erro ao carregar sua grade')
  } finally {
    loading.value = false
  }
}

function getCellColor(schedule: ClassSchedule): string {
  const code = schedule.teacher_assignment?.curricular_component?.code ?? ''
  const colors: Record<string, string> = {
    LP: 'bg-blue-100 text-blue-800',
    MAT: 'bg-green-100 text-green-800',
    CIE: 'bg-purple-100 text-purple-800',
    HIS: 'bg-orange-100 text-orange-800',
    GEO: 'bg-teal-100 text-teal-800',
    ART: 'bg-pink-100 text-pink-800',
    EDF: 'bg-red-100 text-red-800',
    ING: 'bg-indigo-100 text-indigo-800',
    ER: 'bg-amber-100 text-amber-800',
  }
  return colors[code] ?? 'bg-gray-100 text-gray-800'
}

onMounted(async () => {
  await resolveTeacherId()
  await loadData()
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-fluent-primary">Minha Grade</h1>

    <div class="rounded-lg border border-fluent-border bg-white p-6 shadow-sm">
      <EmptyState v-if="!loading && schedules.length === 0" message="Voce ainda nao possui horarios definidos" />

      <div v-if="loading" class="flex items-center justify-center py-12">
        <i class="pi pi-spin pi-spinner text-2xl text-gray-400" />
      </div>

      <div v-if="!loading && schedules.length > 0" class="overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr>
              <th class="border border-fluent-border bg-gray-50 px-3 py-2 text-left text-sm font-medium" :style="{ width: '120px' }">Horario</th>
              <th v-for="day in days" :key="day.value" class="border border-fluent-border bg-gray-50 px-3 py-2 text-center text-sm font-medium">{{ day.label }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in grid" :key="row.slot.number + '-' + row.slot.type" :class="{ 'bg-amber-50': row.isBreak }">
              <td class="border border-fluent-border px-3 py-2 text-sm">
                <div class="font-medium">{{ row.isBreak ? 'Intervalo' : `${row.slot.number}a Aula` }}</div>
                <div class="text-xs text-gray-500">{{ row.slot.start_time }} - {{ row.slot.end_time }}</div>
              </td>
              <td v-for="cell in row.cells" :key="cell.day" class="border border-fluent-border px-1 py-1 text-center">
                <template v-if="!row.isBreak">
                  <div v-for="schedule in cell.schedules" :key="schedule.id" class="rounded px-2 py-1 text-xs" :class="getCellColor(schedule)">
                    <div class="font-medium">{{ schedule.teacher_assignment?.curricular_component?.name ?? schedule.teacher_assignment?.experience_field?.name ?? '' }}</div>
                    <div class="text-[0.65rem] opacity-75">{{ [schedule.teacher_assignment?.class_group?.grade_level?.name, schedule.teacher_assignment?.class_group?.name].filter(Boolean).join(' ') }}</div>
                  </div>
                  <span v-if="cell.schedules.length === 0" class="text-xs text-gray-300">-</span>
                </template>
                <span v-else class="text-xs text-gray-400">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
