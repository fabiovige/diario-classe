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
  <div class="p-6">
    <div class="mb-4 flex items-center justify-between">
      <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Detalhes do Aluno</h1>
      <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" @click="router.push('/people/students')" />
    </div>

    <div v-if="student" class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <div class="grid grid-cols-[repeat(auto-fill,minmax(250px,1fr))] gap-5">
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Nome</span>
          <span class="text-[0.9375rem]">{{ student.name }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Nome Social</span>
          <span class="text-[0.9375rem]">{{ student.social_name ?? '--' }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">CPF</span>
          <span class="text-[0.9375rem]">{{ formatCpf(student.cpf) }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Data de Nascimento</span>
          <span class="text-[0.9375rem]">{{ formatDate(student.birth_date) }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Genero</span>
          <span class="text-[0.9375rem]">{{ student.gender }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Raca/Cor</span>
          <span class="text-[0.9375rem]">{{ student.race_color }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Deficiencia</span>
          <span class="text-[0.9375rem]">{{ student.has_disability ? 'Sim' : 'Nao' }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Status</span>
          <StatusBadge :status="student.active ? 'active' : 'inactive'" :label="student.active ? 'Ativo' : 'Inativo'" />
        </div>
      </div>
    </div>

    <div v-if="student" class="mt-6 rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
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
  </div>
</template>
