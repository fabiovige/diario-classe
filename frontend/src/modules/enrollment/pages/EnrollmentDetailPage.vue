<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Select from 'primevue/select'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import FormDialog from '@/shared/components/FormDialog.vue'
import { enrollmentService } from '@/services/enrollment.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { enrollmentStatusLabel, movementTypeLabel } from '@/shared/utils/enum-labels'
import { formatDate, formatDateTime } from '@/shared/utils/formatters'
import type { Enrollment, EnrollmentMovement } from '@/types/enrollment'
import type { ClassGroup } from '@/types/school-structure'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const enrollmentId = Number(route.params.id)
const enrollment = ref<Enrollment | null>(null)
const movements = ref<EnrollmentMovement[]>([])
const loading = ref(false)

const assignDialogVisible = ref(false)
const assignLoading = ref(false)
const classGroups = ref<ClassGroup[]>([])
const selectedClassGroupId = ref<number | null>(null)

async function loadEnrollment() {
  loading.value = true
  try {
    const [enrollmentRes, movementsRes] = await Promise.all([
      enrollmentService.getEnrollment(enrollmentId),
      enrollmentService.getMovements(enrollmentId),
    ])
    enrollment.value = enrollmentRes
    movements.value = movementsRes
  } catch {
    toast.error('Erro ao carregar matricula')
    router.push('/enrollment/enrollments')
  } finally {
    loading.value = false
  }
}

async function openAssignDialog() {
  try {
    const response = await schoolStructureService.getClassGroups({ per_page: 100 })
    classGroups.value = response.data
    selectedClassGroupId.value = null
    assignDialogVisible.value = true
  } catch {
    toast.error('Erro ao carregar turmas')
  }
}

async function handleAssign() {
  if (!selectedClassGroupId.value) return
  assignLoading.value = true
  try {
    await enrollmentService.assignToClass(enrollmentId, { class_group_id: selectedClassGroupId.value })
    toast.success('Aluno enturmado')
    assignDialogVisible.value = false
    loadEnrollment()
  } catch (error: any) {
    toast.error(error.response?.data?.error ?? 'Erro ao enturmar')
  } finally {
    assignLoading.value = false
  }
}

onMounted(loadEnrollment)
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Detalhes da Matricula</h1>
      <div class="header-actions">
        <Button label="Enturmar" icon="pi pi-users" @click="openAssignDialog" />
        <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" @click="router.push('/enrollment/enrollments')" />
      </div>
    </div>

    <div v-if="enrollment" class="card-section">
      <div class="info-grid">
        <div class="info-item">
          <span class="info-label">Numero</span>
          <span class="info-value">{{ enrollment.enrollment_number }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Aluno</span>
          <span class="info-value">{{ enrollment.student?.name ?? '--' }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Escola</span>
          <span class="info-value">{{ enrollment.school?.name ?? '--' }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Ano Letivo</span>
          <span class="info-value">{{ enrollment.academic_year?.year ?? '--' }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Status</span>
          <StatusBadge :status="enrollment.status" :label="enrollmentStatusLabel(enrollment.status)" />
        </div>
        <div class="info-item">
          <span class="info-label">Data Matricula</span>
          <span class="info-value">{{ formatDate(enrollment.enrollment_date) }}</span>
        </div>
      </div>
    </div>

    <div v-if="enrollment" class="card-section mt-3">
      <h2 class="section-title">Enturmacoes</h2>
      <EmptyState v-if="!enrollment.class_assignments || enrollment.class_assignments.length === 0" message="Nenhuma enturmacao registrada" />
      <DataTable v-if="enrollment.class_assignments && enrollment.class_assignments.length > 0" :value="enrollment.class_assignments" stripedRows responsiveLayout="scroll">
        <Column header="Turma">
          <template #body="{ data }">
            {{ data.class_group?.name ?? '--' }}
          </template>
        </Column>
        <Column field="status" header="Status" />
        <Column header="Inicio">
          <template #body="{ data }">
            {{ formatDate(data.start_date) }}
          </template>
        </Column>
        <Column header="Fim">
          <template #body="{ data }">
            {{ formatDate(data.end_date) }}
          </template>
        </Column>
      </DataTable>
    </div>

    <div class="card-section mt-3">
      <h2 class="section-title">Movimentacoes</h2>
      <EmptyState v-if="movements.length === 0" message="Nenhuma movimentacao registrada" />
      <DataTable v-if="movements.length > 0" :value="movements" stripedRows responsiveLayout="scroll">
        <Column header="Tipo">
          <template #body="{ data }">
            {{ movementTypeLabel(data.type) }}
          </template>
        </Column>
        <Column header="Data">
          <template #body="{ data }">
            {{ formatDate(data.movement_date) }}
          </template>
        </Column>
        <Column field="reason" header="Motivo" />
        <Column header="Criado em">
          <template #body="{ data }">
            {{ formatDateTime(data.created_at) }}
          </template>
        </Column>
      </DataTable>
    </div>

    <FormDialog v-model:visible="assignDialogVisible" title="Enturmar Aluno" :loading="assignLoading" @save="handleAssign">
      <div class="dialog-form">
        <div class="field">
          <label>Turma *</label>
          <Select v-model="selectedClassGroupId" :options="classGroups" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" filter />
        </div>
      </div>
    </FormDialog>
  </div>
</template>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.header-actions { display: flex; gap: 0.5rem; }
.info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.25rem; }
.info-item { display: flex; flex-direction: column; gap: 0.25rem; }
.info-label { font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; }
.info-value { font-size: 0.9375rem; }
.section-title { font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem; }
.mt-3 { margin-top: 1.5rem; }
.dialog-form { display: flex; flex-direction: column; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.375rem; }
.field label { font-size: 0.8125rem; font-weight: 500; }
.w-full { width: 100%; }
</style>
