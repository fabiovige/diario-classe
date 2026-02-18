<script setup lang="ts">
import { onMounted, ref } from 'vue'
import MetricCard from '@/shared/components/MetricCard.vue'
import { useAuthStore } from '@/stores/auth'
import { schoolStructureService } from '@/services/school-structure.service'
import { enrollmentService } from '@/services/enrollment.service'
import { periodClosingService } from '@/services/period-closing.service'
import { peopleService } from '@/services/people.service'

const authStore = useAuthStore()
const loading = ref(true)
const metrics = ref({
  classGroups: 0,
  enrollments: 0,
  teachers: 0,
  closingsPending: 0,
})

async function loadMetrics() {
  loading.value = true
  const schoolId = authStore.userSchoolId
  try {
    const params = schoolId ? { school_id: schoolId, per_page: 1 } : { per_page: 1 }
    const [classGroupsRes, enrollmentsRes, teachersRes, closingsRes] = await Promise.all([
      schoolStructureService.getClassGroups(params),
      enrollmentService.getEnrollments(params),
      peopleService.getTeachers(params),
      periodClosingService.getClosings({ ...params, status: 'pending' }),
    ])
    metrics.value.classGroups = classGroupsRes.meta.total
    metrics.value.enrollments = enrollmentsRes.meta.total
    metrics.value.teachers = teachersRes.meta.total
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
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Dashboard - Diretor(a)</h1>
    <div class="grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-4">
      <MetricCard title="Turmas" :value="metrics.classGroups" label="Na escola" icon="pi pi-th-large" color="#0078D4" :loading="loading" />
      <MetricCard title="Matriculas" :value="metrics.enrollments" label="Ativas" icon="pi pi-users" color="#0F7B0F" :loading="loading" />
      <MetricCard title="Professores" :value="metrics.teachers" label="Na escola" icon="pi pi-user" color="#60CDFF" :loading="loading" />
      <MetricCard title="Fechamentos" :value="metrics.closingsPending" label="Pendentes de aprovacao" icon="pi pi-clock" color="#9D5D00" :loading="loading" />
    </div>
  </div>
</template>
