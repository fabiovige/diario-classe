<script setup lang="ts">
import { onMounted, ref } from 'vue'
import MetricCard from '@/shared/components/MetricCard.vue'
import { schoolStructureService } from '@/services/school-structure.service'
import { peopleService } from '@/services/people.service'
import { identityService } from '@/services/identity.service'
import { periodClosingService } from '@/services/period-closing.service'

const loading = ref(true)
const metrics = ref({
  schools: 0,
  students: 0,
  teachers: 0,
  users: 0,
  closingsPending: 0,
  classGroups: 0,
})

async function loadMetrics() {
  loading.value = true
  try {
    const [schoolsRes, studentsRes, teachersRes, usersRes, classGroupsRes, closingsRes] = await Promise.all([
      schoolStructureService.getSchools({ per_page: 1 }),
      peopleService.getStudents({ per_page: 1 }),
      peopleService.getTeachers({ per_page: 1 }),
      identityService.getUsers({ per_page: 1 }),
      schoolStructureService.getClassGroups({ per_page: 1 }),
      periodClosingService.getClosings({ per_page: 1, status: 'pending' }),
    ])
    metrics.value.schools = schoolsRes.meta.total
    metrics.value.students = studentsRes.meta.total
    metrics.value.teachers = teachersRes.meta.total
    metrics.value.users = usersRes.meta.total
    metrics.value.classGroups = classGroupsRes.meta.total
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
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Dashboard - Administrador</h1>
    <div class="grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-4">
      <MetricCard title="Escolas" :value="metrics.schools" label="EMEBs cadastradas" icon="pi pi-building" color="#0078D4" :loading="loading" />
      <MetricCard title="Alunos" :value="metrics.students" label="Cadastrados" icon="pi pi-users" color="#0F7B0F" :loading="loading" />
      <MetricCard title="Professores" :value="metrics.teachers" label="Cadastrados" icon="pi pi-user" color="#60CDFF" :loading="loading" />
      <MetricCard title="Usuarios" :value="metrics.users" label="No sistema" icon="pi pi-id-card" color="#0078D4" :loading="loading" />
      <MetricCard title="Turmas" :value="metrics.classGroups" label="Ativas" icon="pi pi-th-large" color="#9D5D00" :loading="loading" />
      <MetricCard title="Fechamentos" :value="metrics.closingsPending" label="Pendentes" icon="pi pi-clock" color="#9D5D00" :loading="loading" />
    </div>
  </div>
</template>
