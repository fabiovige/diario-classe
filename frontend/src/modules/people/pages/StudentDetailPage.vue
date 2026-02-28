<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import { peopleService } from '@/services/people.service'
import { useToast } from '@/composables/useToast'
import { formatCpf, formatDate, formatPhone } from '@/shared/utils/formatters'
import type { Student } from '@/types/people'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const studentId = Number(route.params.id)
const student = ref<Student | null>(null)
const loading = ref(false)

async function loadStudent() {
  loading.value = true
  try {
    student.value = await peopleService.getStudent(studentId)
  } catch {
    toast.error('Erro ao carregar aluno')
    router.push('/people/students')
  } finally {
    loading.value = false
  }
}

onMounted(loadStudent)
</script>

<template>
  <div class="mb-4 flex items-center justify-between">
    <h1 class="mb-6 text-2xl font-semibold text-md-primary">Detalhes do Aluno</h1>
    <div class="flex gap-2">
      <Button label="Boletim" icon="pi pi-book" @click="router.push(`/assessment/report-card/${studentId}`)" />
      <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" @click="router.push('/people/students')" />
    </div>
  </div>

  <div v-if="student" class="card">
    <div class="detail-grid">
      <div class="flex flex-col gap-1">
        <span class="text-xs font-semibold uppercase text-md-text-secondary">Nome</span>
        <span class="text-[0.9375rem]">{{ student.name }}</span>
      </div>
      <div class="flex flex-col gap-1">
        <span class="text-xs font-semibold uppercase text-md-text-secondary">Nome Social</span>
        <span class="text-[0.9375rem]">{{ student.social_name ?? '--' }}</span>
      </div>
      <div class="flex flex-col gap-1">
        <span class="text-xs font-semibold uppercase text-md-text-secondary">CPF</span>
        <span class="text-[0.9375rem]">{{ formatCpf(student.cpf) }}</span>
      </div>
      <div class="flex flex-col gap-1">
        <span class="text-xs font-semibold uppercase text-md-text-secondary">Data de Nascimento</span>
        <span class="text-[0.9375rem]">{{ formatDate(student.birth_date) }}</span>
      </div>
      <div class="flex flex-col gap-1">
        <span class="text-xs font-semibold uppercase text-md-text-secondary">Genero</span>
        <span class="text-[0.9375rem]">{{ student.gender }}</span>
      </div>
      <div class="flex flex-col gap-1">
        <span class="text-xs font-semibold uppercase text-md-text-secondary">Raca/Cor</span>
        <span class="text-[0.9375rem]">{{ student.race_color }}</span>
      </div>
      <div class="flex flex-col gap-1">
        <span class="text-xs font-semibold uppercase text-md-text-secondary">Deficiencia</span>
        <span class="text-[0.9375rem]">{{ student.has_disability ? 'Sim' : 'Nao' }}</span>
      </div>
      <div class="flex flex-col gap-1">
        <span class="text-xs font-semibold uppercase text-md-text-secondary">Status</span>
        <StatusBadge :status="student.active ? 'active' : 'inactive'" :label="student.active ? 'Ativo' : 'Inativo'" />
      </div>
    </div>
  </div>

  <div v-if="student" class="card mt-6">
    <h2 class="mb-4 text-lg font-semibold">Responsaveis</h2>

    <EmptyState v-if="!student.guardians || student.guardians.length === 0" message="Nenhum responsavel vinculado" />

    <DataTable v-if="student.guardians && student.guardians.length > 0" :value="student.guardians" stripedRows responsiveLayout="scroll">
      <Column field="name" header="Nome" />
      <Column header="CPF">
        <template #body="{ data }">
          {{ formatCpf(data.cpf) }}
        </template>
      </Column>
      <Column header="Telefone">
        <template #body="{ data }">
          {{ formatPhone(data.phone) }}
        </template>
      </Column>
      <Column field="email" header="E-mail" />
      <Column field="relationship" header="Parentesco" />
      <Column header="Principal">
        <template #body="{ data }">
          <StatusBadge v-if="data.is_primary" status="active" label="Sim" />
          <span v-else>Nao</span>
        </template>
      </Column>
    </DataTable>
  </div>
</template>
