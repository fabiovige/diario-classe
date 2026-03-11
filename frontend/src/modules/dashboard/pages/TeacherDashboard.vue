<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'
import MetricCard from '@/shared/components/MetricCard.vue'
import { curriculumService } from '@/services/curriculum.service'
import { classRecordService } from '@/services/class-record.service'

const router = useRouter()

const loading = ref(true)
const metrics = ref({
  assignments: 0,
  lessonRecords: 0,
})

async function loadMetrics() {
  loading.value = true
  try {
    const [assignmentsRes, recordsRes] = await Promise.all([
      curriculumService.getAssignments({ per_page: 1 }),
      classRecordService.getRecords({ per_page: 1 }),
    ])
    metrics.value.assignments = assignmentsRes.meta.total
    metrics.value.lessonRecords = recordsRes.meta.total
  } catch {
    // metrics stay at 0
  } finally {
    loading.value = false
  }
}

onMounted(loadMetrics)
</script>

<template>

  <div
    class="mb-6 flex items-center justify-between rounded-lg border border-md-primary bg-md-selected p-5"
  >
    <div>
      <h2 class="text-lg font-semibold text-md-primary">Minhas Aulas de Hoje</h2>
      <p class="mt-0.5 text-[0.875rem] text-md-text-secondary">Acesse suas turmas, faca chamada, registre aulas e lance notas</p>
    </div>
    <Button label="Abrir" icon="pi pi-arrow-right" iconPos="right" @click="router.push('/my-classes')" />
  </div>

  <div class="metric-grid">
    <MetricCard title="Minhas Turmas" :value="metrics.assignments" label="Atribuicoes ativas" icon="pi pi-th-large" color="#1976D2" :loading="loading" />
    <MetricCard title="Diarios" :value="metrics.lessonRecords" label="Registros de aula" icon="pi pi-book" color="#0F7B0F" :loading="loading" />
  </div>
</template>
