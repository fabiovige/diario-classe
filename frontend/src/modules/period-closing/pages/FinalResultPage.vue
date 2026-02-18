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
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Resultado Final</h1>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <div class="grid grid-cols-[1fr_auto] items-end gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Aluno *</label>
          <Select v-model="selectedStudentId" :options="students" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" filter @change="loadResult" />
        </div>
        <div class="flex items-end gap-2">
          <Button label="Buscar" icon="pi pi-search" @click="loadResult" :disabled="!selectedStudentId" />
          <Button label="Calcular" icon="pi pi-calculator" severity="info" @click="calculateResult" :disabled="!selectedStudentId" :loading="loading" />
        </div>
      </div>
    </div>

    <div class="mt-6 rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <EmptyState v-if="!loading && !result" message="Selecione um aluno para visualizar o resultado final" />

      <div v-if="result" class="grid grid-cols-[repeat(auto-fill,minmax(250px,1fr))] gap-5">
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Aluno</span>
          <span class="text-[0.9375rem]">{{ result.student?.name ?? `Aluno #${result.student_id}` }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Resultado</span>
          <StatusBadge :status="result.result" :label="finalResultStatusLabel(result.result)" />
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Media Geral</span>
          <span class="text-2xl font-bold">{{ result.overall_average ?? '--' }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Frequencia Geral</span>
          <span class="text-[0.9375rem]">{{ formatPercentage(result.overall_frequency) }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Conselho</span>
          <span class="text-[0.9375rem]">{{ result.council_override ? 'Sim' : 'Nao' }}</span>
        </div>
        <div v-if="result.observations" class="col-span-full flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Observacoes</span>
          <span class="text-[0.9375rem]">{{ result.observations }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
