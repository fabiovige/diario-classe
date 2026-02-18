<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Select from 'primevue/select'
import Button from 'primevue/button'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import { periodClosingService } from '@/services/period-closing.service'
import { peopleService } from '@/services/people.service'
import { useToast } from '@/composables/useToast'
import { finalResultStatusLabel } from '@/shared/utils/enum-labels'
import { formatPercentage } from '@/shared/utils/formatters'
import type { Student } from '@/types/people'
import type { FinalResult } from '@/types/period-closing'

const toast = useToast()

const students = ref<Student[]>([])
const selectedStudentId = ref<number | null>(null)
const result = ref<FinalResult | null>(null)
const loading = ref(false)

async function loadStudents() {
  try {
    const response = await peopleService.getStudents({ per_page: 100 })
    students.value = response.data
  } catch {
    toast.error('Erro ao carregar alunos')
  }
}

async function loadResult() {
  if (!selectedStudentId.value) return
  loading.value = true
  try {
    result.value = await periodClosingService.getStudentFinalResult(selectedStudentId.value)
  } catch {
    result.value = null
    toast.warn('Resultado final nao encontrado para este aluno')
  } finally {
    loading.value = false
  }
}

async function calculateResult() {
  if (!selectedStudentId.value) return
  loading.value = true
  try {
    result.value = await periodClosingService.calculateFinalResult({
      student_id: selectedStudentId.value,
    })
    toast.success('Resultado calculado')
  } catch (error: any) {
    toast.error(error.response?.data?.error ?? 'Erro ao calcular resultado')
  } finally {
    loading.value = false
  }
}

onMounted(loadStudents)
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">Resultado Final</h1>

    <div class="card-section">
      <div class="filter-bar">
        <div class="field">
          <label>Aluno *</label>
          <Select v-model="selectedStudentId" :options="students" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" filter @change="loadResult" />
        </div>
        <div class="field action-field">
          <Button label="Buscar" icon="pi pi-search" @click="loadResult" :disabled="!selectedStudentId" />
          <Button label="Calcular" icon="pi pi-calculator" severity="info" @click="calculateResult" :disabled="!selectedStudentId" :loading="loading" />
        </div>
      </div>
    </div>

    <div class="card-section mt-3">
      <EmptyState v-if="!loading && !result" message="Selecione um aluno para visualizar o resultado final" />

      <div v-if="result" class="result-card">
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Aluno</span>
            <span class="info-value">{{ result.student?.name ?? `Aluno #${result.student_id}` }}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Resultado</span>
            <StatusBadge :status="result.result" :label="finalResultStatusLabel(result.result)" />
          </div>
          <div class="info-item">
            <span class="info-label">Media Geral</span>
            <span class="info-value result-average">{{ result.overall_average ?? '--' }}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Frequencia Geral</span>
            <span class="info-value">{{ formatPercentage(result.overall_frequency) }}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Conselho</span>
            <span class="info-value">{{ result.council_override ? 'Sim' : 'Nao' }}</span>
          </div>
          <div v-if="result.observations" class="info-item info-full">
            <span class="info-label">Observacoes</span>
            <span class="info-value">{{ result.observations }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.filter-bar { display: grid; grid-template-columns: 1fr auto; gap: 1rem; align-items: end; }
.field { display: flex; flex-direction: column; gap: 0.375rem; }
.field label { font-size: 0.8125rem; font-weight: 500; }
.action-field { display: flex; gap: 0.5rem; align-items: flex-end; }
.w-full { width: 100%; }
.mt-3 { margin-top: 1.5rem; }
.result-card { padding: 1rem 0; }
.info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.25rem; }
.info-item { display: flex; flex-direction: column; gap: 0.25rem; }
.info-full { grid-column: 1 / -1; }
.info-label { font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; }
.info-value { font-size: 0.9375rem; }
.result-average { font-size: 1.5rem; font-weight: 700; }
</style>
