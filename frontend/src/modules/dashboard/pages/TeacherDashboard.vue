<script setup lang="ts">
import { onMounted, ref } from 'vue'
import MetricCard from '@/shared/components/MetricCard.vue'
import { curriculumService } from '@/services/curriculum.service'
import { classRecordService } from '@/services/class-record.service'

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
  <div class="page-container">
    <h1 class="page-title">Dashboard - Professor(a)</h1>
    <div class="dashboard-grid">
      <MetricCard
        title="Minhas Turmas"
        :value="metrics.assignments"
        label="Atribuicoes ativas"
        icon="pi pi-th-large"
        color="var(--jandira-primary)"
        :loading="loading"
      />
      <MetricCard
        title="Diarios"
        :value="metrics.lessonRecords"
        label="Registros de aula"
        icon="pi pi-book"
        color="var(--jandira-secondary)"
        :loading="loading"
      />
    </div>
  </div>
</template>

<style scoped>
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
}
</style>
