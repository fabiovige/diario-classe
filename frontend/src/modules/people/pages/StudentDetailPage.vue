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
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Detalhes do Aluno</h1>
      <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" @click="router.push('/people/students')" />
    </div>

    <div v-if="student" class="card-section">
      <div class="info-grid">
        <div class="info-item">
          <span class="info-label">Nome</span>
          <span class="info-value">{{ student.name }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Nome Social</span>
          <span class="info-value">{{ student.social_name ?? '--' }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">CPF</span>
          <span class="info-value">{{ formatCpf(student.cpf) }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Data de Nascimento</span>
          <span class="info-value">{{ formatDate(student.birth_date) }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Genero</span>
          <span class="info-value">{{ student.gender }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Raca/Cor</span>
          <span class="info-value">{{ student.race_color }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Deficiencia</span>
          <span class="info-value">{{ student.has_disability ? 'Sim' : 'Nao' }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Status</span>
          <StatusBadge :status="student.active ? 'active' : 'inactive'" :label="student.active ? 'Ativo' : 'Inativo'" />
        </div>
      </div>
    </div>

    <div v-if="student" class="card-section mt-3">
      <h2 class="section-title">Responsaveis</h2>

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

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.25rem; }
.info-item { display: flex; flex-direction: column; gap: 0.25rem; }
.info-label { font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; }
.info-value { font-size: 0.9375rem; }
.section-title { font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem; }
.mt-3 { margin-top: 1.5rem; }
</style>
