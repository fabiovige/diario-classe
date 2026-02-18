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
    classGroups.value = response.data.map(cg => ({ ...cg, label: [cg.grade_level?.name, cg.name, cg.shift?.name].filter(Boolean).join(' - ') }))
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
  <div class="p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold text-[#0078D4]">Detalhes da Matricula</h1>
      <div class="flex gap-2">
        <Button label="Enturmar" icon="pi pi-users" @click="openAssignDialog" />
        <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" @click="router.push('/enrollment/enrollments')" />
      </div>
    </div>

    <div v-if="enrollment" class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <div class="grid grid-cols-[repeat(auto-fill,minmax(250px,1fr))] gap-5">
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Numero</span>
          <span class="text-[0.9375rem]">{{ enrollment.enrollment_number }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Aluno</span>
          <span class="text-[0.9375rem]">{{ enrollment.student?.name ?? '--' }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Escola</span>
          <span class="text-[0.9375rem]">{{ enrollment.school?.name ?? '--' }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Ano Letivo</span>
          <span class="text-[0.9375rem]">{{ enrollment.academic_year?.year ?? '--' }}</span>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Status</span>
          <StatusBadge :status="enrollment.status" :label="enrollmentStatusLabel(enrollment.status)" />
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs font-semibold uppercase text-[#616161]">Data Matricula</span>
          <span class="text-[0.9375rem]">{{ formatDate(enrollment.enrollment_date) }}</span>
        </div>
      </div>
    </div>

    <div v-if="enrollment" class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm mt-6">
      <h2 class="text-lg font-semibold mb-4">Enturmacoes</h2>
      <EmptyState v-if="!enrollment.class_assignments || enrollment.class_assignments.length === 0" message="Nenhuma enturmacao registrada" />
      <DataTable v-if="enrollment.class_assignments && enrollment.class_assignments.length > 0" :value="enrollment.class_assignments" stripedRows responsiveLayout="scroll">
        <Column header="Turma">
          <template #body="{ data }">
            {{ [data.class_group?.grade_level?.name, data.class_group?.name, data.class_group?.shift?.name].filter(Boolean).join(' - ') || '--' }}
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

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm mt-6">
      <h2 class="text-lg font-semibold mb-4">Movimentacoes</h2>
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
      <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Turma *</label>
          <Select v-model="selectedClassGroupId" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Selecione" class="w-full" filter />
        </div>
      </div>
    </FormDialog>
  </div>
</template>
