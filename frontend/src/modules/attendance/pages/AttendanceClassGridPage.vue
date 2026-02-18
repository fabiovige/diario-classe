<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Button from 'primevue/button'
import EmptyState from '@/shared/components/EmptyState.vue'
import { attendanceService } from '@/services/attendance.service'
import { useToast } from '@/composables/useToast'
import { formatDate } from '@/shared/utils/formatters'
import type { AttendanceRecord } from '@/types/attendance'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const classGroupId = Number(route.params.classGroupId)
const records = ref<AttendanceRecord[]>([])
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(50)

interface GroupedRow {
  student_name: string
  student_id: number
  records: Record<string, AttendanceRecord>
}

const dates = computed(() => {
  const dateSet = new Set<string>()
  records.value.forEach(r => {
    if (r.date) dateSet.add(r.date)
  })
  return Array.from(dateSet).sort()
})

const groupedData = computed(() => {
  const map = new Map<number, GroupedRow>()
  records.value.forEach(r => {
    if (!map.has(r.student_id)) {
      map.set(r.student_id, {
        student_id: r.student_id,
        student_name: r.student?.name ?? `Aluno #${r.student_id}`,
        records: {},
      })
    }
    const row = map.get(r.student_id)!
    if (r.date) row.records[r.date] = r
  })
  return Array.from(map.values()).sort((a, b) => a.student_name.localeCompare(b.student_name))
})

async function loadData() {
  loading.value = true
  try {
    const response = await attendanceService.getByClass(classGroupId, {
      page: currentPage.value,
      per_page: perPage.value,
    })
    records.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar frequencia da turma')
  } finally {
    loading.value = false
  }
}

function getStatusShort(status: string): string {
  const map: Record<string, string> = { present: 'P', absent: 'A', justified: 'J', dispensed: 'D' }
  return map[status] ?? status
}

function getStatusColor(status: string): string {
  const map: Record<string, string> = { present: '#0F7B0F', absent: '#C42B1C', justified: '#0078D4', dispensed: '#94a3b8' }
  return map[status] ?? '#94a3b8'
}

onMounted(loadData)
</script>

<template>
  <div class="p-6">
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-[#0078D4]">Mapa de Frequencia</h1>
      <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" @click="router.back()" />
    </div>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <EmptyState v-if="!loading && groupedData.length === 0" message="Nenhum registro de frequencia encontrado" />

      <div v-if="groupedData.length > 0" class="overflow-x-auto">
        <table class="w-full border-collapse text-[0.8125rem]">
          <thead>
            <tr>
              <th class="border border-[#E0E0E0] p-2 text-center bg-[#f9fafb] font-semibold whitespace-nowrap sticky left-0 z-[1] min-w-[200px] text-left">Aluno</th>
              <th
                v-for="date in dates"
                :key="date"
                class="border border-[#E0E0E0] p-2 text-center bg-[#f9fafb] font-semibold whitespace-nowrap min-w-[70px]"
              >
                {{ formatDate(date) }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in groupedData" :key="row.student_id">
              <td class="border border-[#E0E0E0] p-2 text-center sticky left-0 z-[1] min-w-[200px] bg-white text-left">
                <a
                  class="cursor-pointer text-[#0078D4] underline"
                  @click="router.push(`/attendance/student/${row.student_id}`)"
                >
                  {{ row.student_name }}
                </a>
              </td>
              <td
                v-for="date in dates"
                :key="date"
                class="border border-[#E0E0E0] p-2 text-center min-w-[70px]"
              >
                <span v-if="row.records[date]" class="font-bold" :style="{ color: getStatusColor(row.records[date].status) }">
                  {{ getStatusShort(row.records[date].status) }}
                </span>
                <span v-else class="font-bold text-[#cbd5e1]">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
