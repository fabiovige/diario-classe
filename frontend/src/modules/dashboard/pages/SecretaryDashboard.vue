<script setup lang="ts">
import { onMounted, ref } from 'vue'
import MetricCard from '@/shared/components/MetricCard.vue'
import { useAuthStore } from '@/stores/auth'
import { enrollmentService } from '@/services/enrollment.service'
import { peopleService } from '@/services/people.service'

const authStore = useAuthStore()
const loading = ref(true)
const metrics = ref({
  enrollments: 0,
  students: 0,
  guardians: 0,
})

async function loadMetrics() {
  loading.value = true
  const schoolId = authStore.userSchoolId
  try {
    const params = schoolId ? { school_id: schoolId, per_page: 1 } : { per_page: 1 }
    const [enrollmentsRes, studentsRes, guardiansRes] = await Promise.all([
      enrollmentService.getEnrollments(params),
      peopleService.getStudents(params),
      peopleService.getGuardians({ per_page: 1 }),
    ])
    metrics.value.enrollments = enrollmentsRes.meta.total
    metrics.value.students = studentsRes.meta.total
    metrics.value.guardians = guardiansRes.meta.total
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
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Dashboard - Secretario(a)</h1>
    <div class="grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-4">
      <MetricCard title="Matriculas" :value="metrics.enrollments" label="Ativas" icon="pi pi-file" color="#0078D4" :loading="loading" />
      <MetricCard title="Alunos" :value="metrics.students" label="Cadastrados" icon="pi pi-users" color="#0F7B0F" :loading="loading" />
      <MetricCard title="Responsaveis" :value="metrics.guardians" label="Cadastrados" icon="pi pi-user" color="#60CDFF" :loading="loading" />
    </div>
  </div>
</template>
