<script setup lang="ts">
import { onMounted, ref } from 'vue'
import MetricCard from '@/shared/components/MetricCard.vue'
import { useAuthStore } from '@/stores/auth'
import { schoolStructureService } from '@/services/school-structure.service'
import { classRecordService } from '@/services/class-record.service'
import { periodClosingService } from '@/services/period-closing.service'

const authStore = useAuthStore()
const loading = ref(true)
const metrics = ref({
  classGroups: 0,
  lessonRecords: 0,
  closingsPending: 0,
})

async function loadMetrics() {
  loading.value = true
  const schoolId = authStore.userSchoolId
  try {
    const params = schoolId ? { school_id: schoolId, per_page: 1 } : { per_page: 1 }
    const [classGroupsRes, recordsRes, closingsRes] = await Promise.all([
      schoolStructureService.getClassGroups(params),
      classRecordService.getRecords(params),
      periodClosingService.getClosings({ ...params, status: 'submitted' }),
    ])
    metrics.value.classGroups = classGroupsRes.meta.total
    metrics.value.lessonRecords = recordsRes.meta.total
    metrics.value.closingsPending = closingsRes.meta.total
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
    <h1 class="page-title">Dashboard - Coordenador(a)</h1>
    <div class="dashboard-grid">
      <MetricCard
        title="Turmas"
        :value="metrics.classGroups"
        label="Na escola"
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
      <MetricCard
        title="Fechamentos"
        :value="metrics.closingsPending"
        label="A validar"
        icon="pi pi-check-circle"
        color="var(--jandira-warning)"
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
