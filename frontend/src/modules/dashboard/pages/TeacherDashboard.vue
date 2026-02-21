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
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Dashboard - Professor(a)</h1>

    <div
      class="mb-6 flex items-center justify-between rounded-lg border border-[#0078D4] bg-[#F0F6FF] p-5"
    >
      <div>
        <h2 class="text-lg font-semibold text-[#0078D4]">Minhas Aulas de Hoje</h2>
        <p class="mt-0.5 text-[0.875rem] text-[#605E5C]">Acesse suas turmas, faca chamada, registre aulas e lance notas</p>
      </div>
      <Button label="Abrir" icon="pi pi-arrow-right" iconPos="right" @click="router.push('/my-classes')" />
    </div>

    <div class="grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-4">
      <MetricCard title="Minhas Turmas" :value="metrics.assignments" label="Atribuicoes ativas" icon="pi pi-th-large" color="#0078D4" :loading="loading" />
      <MetricCard title="Diarios" :value="metrics.lessonRecords" label="Registros de aula" icon="pi pi-book" color="#0F7B0F" :loading="loading" />
    </div>
  </div>
</template>
