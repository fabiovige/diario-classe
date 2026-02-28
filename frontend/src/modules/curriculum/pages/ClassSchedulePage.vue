<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import Select from 'primevue/select'
import Button from 'primevue/button'
import EmptyState from '@/shared/components/EmptyState.vue'
import { curriculumService } from '@/services/curriculum.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { useSchoolScope } from '@/composables/useSchoolScope'
import { extractApiError } from '@/shared/utils/api-error'
import type { ClassSchedule, TeacherAssignment, TimeSlot } from '@/types/curriculum'
import type { ClassGroup, School } from '@/types/school-structure'

const toast = useToast()
const { shouldShowSchoolFilter, userSchoolName } = useSchoolScope()

const loading = ref(false)
const saving = ref(false)

const schools = ref<School[]>([])
const classGroups = ref<(ClassGroup & { label: string })[]>([])
const selectedSchoolId = ref<number | null>(null)
const selectedClassGroupId = ref<number | null>(null)

const timeSlots = ref<TimeSlot[]>([])
const assignments = ref<TeacherAssignment[]>([])
const schedules = ref<ClassSchedule[]>([])

const days = [
  { value: 1, label: 'Seg' },
  { value: 2, label: 'Ter' },
  { value: 3, label: 'Qua' },
  { value: 4, label: 'Qui' },
  { value: 5, label: 'Sex' },
]

const grid = computed(() => {
  return timeSlots.value.map(slot => ({
    slot,
    isBreak: slot.type === 'break',
    cells: days.map(day => {
      const schedule = schedules.value.find(
        s => s.time_slot_id === slot.id && s.day_of_week === day.value
      )
      return {
        day: day.value,
        schedule,
        assignmentId: schedule?.teacher_assignment_id ?? null,
      }
    }),
  }))
})

const pendingChanges = ref<Map<string, { assignmentId: number | null; timeSlotId: number; dayOfWeek: number }>>(new Map())

const hasChanges = computed(() => pendingChanges.value.size > 0)

function cellKey(slotId: number, day: number): string {
  return `${slotId}-${day}`
}

function getAssignmentLabel(assignment: TeacherAssignment | undefined): string {
  if (!assignment) return ''
  const component = assignment.curricular_component?.name ?? assignment.experience_field?.name ?? ''
  const teacher = assignment.teacher?.user?.name ?? ''
  return `${component} - ${teacher}`
}

function getCellAssignmentId(slotId: number, day: number): number | null {
  const key = cellKey(slotId, day)
  if (pendingChanges.value.has(key)) {
    return pendingChanges.value.get(key)!.assignmentId
  }
  const schedule = schedules.value.find(s => s.time_slot_id === slotId && s.day_of_week === day)
  return schedule?.teacher_assignment_id ?? null
}

function onCellChange(slotId: number, day: number, assignmentId: number | null) {
  const key = cellKey(slotId, day)
  const existingSchedule = schedules.value.find(s => s.time_slot_id === slotId && s.day_of_week === day)
  const originalAssignmentId = existingSchedule?.teacher_assignment_id ?? null

  if (assignmentId === originalAssignmentId) {
    pendingChanges.value.delete(key)
    return
  }

  pendingChanges.value.set(key, { assignmentId, timeSlotId: slotId, dayOfWeek: day })
}

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

watch(selectedSchoolId, () => {
  selectedClassGroupId.value = null
  schedules.value = []
  timeSlots.value = []
  assignments.value = []
  pendingChanges.value.clear()
  loadClassGroups()
})

watch(selectedClassGroupId, () => {
  pendingChanges.value.clear()
  loadScheduleData()
})

async function loadScheduleData() {
  if (!selectedClassGroupId.value) {
    schedules.value = []
    timeSlots.value = []
    assignments.value = []
    return
  }

  loading.value = true
  try {
    const selectedCg = classGroups.value.find(cg => cg.id === selectedClassGroupId.value)
    const shiftId = selectedCg?.shift_id

    const [slotsRes, schedulesRes, assignmentsRes] = await Promise.all([
      curriculumService.getTimeSlots({ shift_id: shiftId }),
      curriculumService.getClassSchedules({ class_group_id: selectedClassGroupId.value }),
      curriculumService.getAssignments({ class_group_id: selectedClassGroupId.value, per_page: 100 }),
    ])

    timeSlots.value = (slotsRes as any).data ?? slotsRes
    schedules.value = (schedulesRes as any).data ?? schedulesRes
    assignments.value = (assignmentsRes as any).data ?? assignmentsRes
  } catch {
    toast.error('Erro ao carregar grade de aulas')
  } finally {
    loading.value = false
  }
}

async function saveChanges() {
  if (!hasChanges.value) return

  saving.value = true
  try {
    const changesByAssignment = new Map<number, Array<{ time_slot_id: number; day_of_week: number }>>()

    for (const change of pendingChanges.value.values()) {
      if (change.assignmentId) {
        if (!changesByAssignment.has(change.assignmentId)) {
          changesByAssignment.set(change.assignmentId, [])
        }
      }
    }

    const affectedAssignmentIds = new Set<number>()
    for (const change of pendingChanges.value.values()) {
      if (change.assignmentId) affectedAssignmentIds.add(change.assignmentId)
      const existing = schedules.value.find(
        s => s.time_slot_id === change.timeSlotId && s.day_of_week === change.dayOfWeek
      )
      if (existing?.teacher_assignment_id) {
        affectedAssignmentIds.add(existing.teacher_assignment_id)
      }
    }

    for (const assignmentId of affectedAssignmentIds) {
      const existingSlots = schedules.value
        .filter(s => s.teacher_assignment_id === assignmentId)
        .filter(s => {
          const key = cellKey(s.time_slot_id, s.day_of_week as number)
          return !pendingChanges.value.has(key)
        })
        .map(s => ({ time_slot_id: s.time_slot_id, day_of_week: s.day_of_week as number }))

      const newSlots = Array.from(pendingChanges.value.values())
        .filter(c => c.assignmentId === assignmentId)
        .map(c => ({ time_slot_id: c.timeSlotId, day_of_week: c.dayOfWeek }))

      const allSlots = [...existingSlots, ...newSlots]
      await curriculumService.saveAssignmentSchedule(assignmentId, allSlots)
    }

    toast.success('Grade salva com sucesso')
    pendingChanges.value.clear()
    await loadScheduleData()
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao salvar grade'))
  } finally {
    saving.value = false
  }
}

const assignmentOptions = computed(() => [
  { id: null, label: '(Vazio)' },
  ...assignments.value.map(a => ({
    id: a.id,
    label: getAssignmentLabel(a),
  })),
])

onMounted(() => {
  if (shouldShowSchoolFilter.value) {
    loadSchools()
  } else {
    loadClassGroups()
  }
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-fluent-primary">Grade de Aulas</h1>

    <div class="rounded-lg border border-fluent-border bg-white p-6 shadow-sm">
      <div class="mb-4 flex flex-wrap items-end gap-4">
        <div v-if="shouldShowSchoolFilter" class="flex flex-col gap-1.5 w-64">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <Select v-model="selectedSchoolId" :options="schools" optionLabel="name" optionValue="id" placeholder="Selecione a escola" class="w-full" filter />
        </div>
        <div v-if="!shouldShowSchoolFilter && userSchoolName" class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola</label>
          <span class="flex h-[2.375rem] items-center rounded-md border border-fluent-border bg-[#F5F5F5] px-3 text-sm">{{ userSchoolName }}</span>
        </div>
        <div class="flex flex-col gap-1.5 w-56">
          <label class="text-[0.8125rem] font-medium">Turma</label>
          <Select v-model="selectedClassGroupId" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Selecione a turma" class="w-full" filter :disabled="!selectedSchoolId && shouldShowSchoolFilter" />
        </div>
      </div>

      <EmptyState v-if="!selectedClassGroupId" message="Selecione uma turma para visualizar a grade de aulas" />

      <div v-if="selectedClassGroupId && !loading && timeSlots.length > 0" class="mt-4">
        <div v-if="hasChanges" class="mb-4 flex items-center justify-end gap-3">
          <Button label="Descartar" severity="secondary" icon="pi pi-times" @click="pendingChanges.clear()" />
          <Button label="Salvar Grade" icon="pi pi-check" :loading="saving" @click="saveChanges" />
        </div>

        <div class="overflow-x-auto">
          <table class="w-full border-collapse">
            <thead>
              <tr>
                <th class="border border-fluent-border bg-gray-50 px-3 py-2 text-left text-sm font-medium" :style="{ width: '120px' }">Horario</th>
                <th v-for="day in days" :key="day.value" class="border border-fluent-border bg-gray-50 px-3 py-2 text-center text-sm font-medium">{{ day.label }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in grid" :key="row.slot.id" :class="{ 'bg-amber-50': row.isBreak }">
                <td class="border border-fluent-border px-3 py-2 text-sm">
                  <div class="font-medium">{{ row.isBreak ? 'Intervalo' : `${row.slot.number}a Aula` }}</div>
                  <div class="text-xs text-gray-500">{{ row.slot.start_time }} - {{ row.slot.end_time }}</div>
                </td>
                <td v-for="cell in row.cells" :key="cell.day" class="border border-fluent-border px-1 py-1 text-center">
                  <template v-if="!row.isBreak">
                    <Select
                      :modelValue="getCellAssignmentId(row.slot.id, cell.day)"
                      @update:modelValue="onCellChange(row.slot.id, cell.day, $event)"
                      :options="assignmentOptions"
                      optionLabel="label"
                      optionValue="id"
                      placeholder="-"
                      class="w-full text-xs"
                      :class="{ 'ring-2 ring-blue-300': pendingChanges.has(cellKey(row.slot.id, cell.day)) }"
                    />
                  </template>
                  <span v-else class="text-xs text-gray-400">-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="loading" class="flex items-center justify-center py-12">
        <i class="pi pi-spin pi-spinner text-2xl text-gray-400" />
      </div>
    </div>
  </div>
</template>
