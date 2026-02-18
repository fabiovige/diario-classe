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
  <div class="p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Boletim Escolar</h1>
      <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" @click="router.back()" />
    </div>

    <div v-if="student" class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-1">
        <h2 class="text-lg font-semibold">{{ student.name }}</h2>
        <span v-if="student.display_name" class="text-[0.875rem] text-[#616161]">{{ student.display_name }}</span>
      </div>
    </div>

    <div class="mt-6 rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
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

      <div v-if="reportCard && reportCard.frequency" class="mt-6">
        <h3 class="text-lg font-semibold mb-4">Frequencia</h3>
        <div class="grid grid-cols-[repeat(auto-fill,minmax(150px,1fr))] gap-4">
          <div class="flex flex-col items-center gap-1 rounded-lg border border-[#E0E0E0] bg-white p-4 text-center">
            <span class="text-2xl font-bold">{{ reportCard.frequency.total_classes ?? '--' }}</span>
            <span class="text-xs uppercase text-[#616161]">Total Aulas</span>
          </div>
          <div class="flex flex-col items-center gap-1 rounded-lg border border-[#E0E0E0] bg-white p-4 text-center">
            <span class="text-2xl font-bold text-[#0F7B0F]">{{ reportCard.frequency.present ?? '--' }}</span>
            <span class="text-xs uppercase text-[#616161]">Presencas</span>
          </div>
          <div class="flex flex-col items-center gap-1 rounded-lg border border-[#E0E0E0] bg-white p-4 text-center">
            <span class="text-2xl font-bold text-[#C42B1C]">{{ reportCard.frequency.absent ?? '--' }}</span>
            <span class="text-xs uppercase text-[#616161]">Faltas</span>
          </div>
          <div class="flex flex-col items-center gap-1 rounded-lg border border-[#E0E0E0] bg-white p-4 text-center">
            <span class="text-2xl font-bold text-[#0078D4]">{{ reportCard.frequency.frequency_percentage ? reportCard.frequency.frequency_percentage + '%' : '--' }}</span>
            <span class="text-xs uppercase text-[#616161]">Frequencia</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
