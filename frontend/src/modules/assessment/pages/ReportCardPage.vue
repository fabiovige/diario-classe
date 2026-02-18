<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import EmptyState from '@/shared/components/EmptyState.vue'
import { assessmentService } from '@/services/assessment.service'
import { peopleService } from '@/services/people.service'
import { useToast } from '@/composables/useToast'
import type { Student } from '@/types/people'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const studentId = Number(route.params.studentId)
const student = ref<Student | null>(null)
const reportCard = ref<any>(null)
const loading = ref(false)

async function loadData() {
  loading.value = true
  try {
    const [studentRes, reportRes] = await Promise.all([
      peopleService.getStudent(studentId),
      assessmentService.getReportCard(studentId),
    ])
    student.value = studentRes
    reportCard.value = reportRes
  } catch {
    toast.error('Erro ao carregar boletim')
  } finally {
    loading.value = false
  }
}

onMounted(loadData)
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Boletim Escolar</h1>
      <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" @click="router.back()" />
    </div>

    <div v-if="student" class="card-section">
      <div class="student-info">
        <h2>{{ student.name }}</h2>
        <span v-if="student.display_name" class="subtitle">{{ student.display_name }}</span>
      </div>
    </div>

    <div class="card-section mt-3">
      <EmptyState v-if="!loading && !reportCard" message="Boletim nao disponivel" />

      <div v-if="reportCard && reportCard.grades">
        <DataTable :value="reportCard.grades" stripedRows responsiveLayout="scroll">
          <Column field="component_name" header="Componente" />
          <Column field="period_1" header="1o Periodo" />
          <Column field="period_2" header="2o Periodo" />
          <Column field="period_3" header="3o Periodo" />
          <Column field="period_4" header="4o Periodo" />
          <Column field="average" header="Media Final" />
        </DataTable>
      </div>

      <div v-if="reportCard && reportCard.frequency" class="frequency-section mt-3">
        <h3 class="section-title">Frequencia</h3>
        <div class="stats-row">
          <div class="stat-card">
            <span class="stat-value">{{ reportCard.frequency.total_classes ?? '--' }}</span>
            <span class="stat-label">Total Aulas</span>
          </div>
          <div class="stat-card stat-success">
            <span class="stat-value">{{ reportCard.frequency.present ?? '--' }}</span>
            <span class="stat-label">Presencas</span>
          </div>
          <div class="stat-card stat-danger">
            <span class="stat-value">{{ reportCard.frequency.absent ?? '--' }}</span>
            <span class="stat-label">Faltas</span>
          </div>
          <div class="stat-card">
            <span class="stat-value">{{ reportCard.frequency.frequency_percentage ? reportCard.frequency.frequency_percentage + '%' : '--' }}</span>
            <span class="stat-label">Frequencia</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.student-info h2 { margin: 0; font-size: 1.25rem; }
.subtitle { color: #64748b; font-size: 0.875rem; }
.section-title { font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem; }
.mt-3 { margin-top: 1.5rem; }
.stats-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; }
.stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; text-align: center; display: flex; flex-direction: column; gap: 0.25rem; }
.stat-value { font-size: 1.5rem; font-weight: 700; }
.stat-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; }
.stat-success .stat-value { color: #22c55e; }
.stat-danger .stat-value { color: #ef4444; }
</style>
