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
  const map: Record<string, string> = { present: '#22c55e', absent: '#ef4444', justified: '#3b82f6', dispensed: '#94a3b8' }
  return map[status] ?? '#94a3b8'
}

onMounted(loadData)
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Mapa de Frequencia</h1>
      <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" @click="router.back()" />
    </div>

    <div class="card-section">
      <EmptyState v-if="!loading && groupedData.length === 0" message="Nenhum registro de frequencia encontrado" />

      <div v-if="groupedData.length > 0" class="grid-wrapper">
        <table class="attendance-grid">
          <thead>
            <tr>
              <th class="student-col">Aluno</th>
              <th v-for="date in dates" :key="date" class="date-col">{{ formatDate(date) }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in groupedData" :key="row.student_id">
              <td class="student-col">
                <a class="student-link" @click="router.push(`/attendance/student/${row.student_id}`)">
                  {{ row.student_name }}
                </a>
              </td>
              <td v-for="date in dates" :key="date" class="date-col">
                <span v-if="row.records[date]" class="status-cell" :style="{ color: getStatusColor(row.records[date].status) }">
                  {{ getStatusShort(row.records[date].status) }}
                </span>
                <span v-else class="status-cell empty">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.grid-wrapper { overflow-x: auto; }
.attendance-grid { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.attendance-grid th,
.attendance-grid td { padding: 0.5rem; border: 1px solid #e2e8f0; text-align: center; }
.attendance-grid th { background: #f8fafc; font-weight: 600; white-space: nowrap; }
.student-col { text-align: left !important; min-width: 200px; position: sticky; left: 0; background: #fff; z-index: 1; }
.attendance-grid th.student-col { background: #f8fafc; }
.date-col { min-width: 70px; }
.status-cell { font-weight: 700; }
.status-cell.empty { color: #cbd5e1; }
.student-link { cursor: pointer; color: var(--jandira-primary, #2563eb); text-decoration: underline; }
</style>
