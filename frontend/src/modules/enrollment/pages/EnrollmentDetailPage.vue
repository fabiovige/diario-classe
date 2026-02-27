<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import InputText from 'primevue/inputtext'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import FormDialog from '@/shared/components/FormDialog.vue'
import EnrollmentDocumentsSection from './EnrollmentDocumentsSection.vue'
import { enrollmentService } from '@/services/enrollment.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import { extractApiError } from '@/shared/utils/api-error'
import { enrollmentStatusLabel, movementTypeLabel, classAssignmentStatusLabel } from '@/shared/utils/enum-labels'
import { formatDate, formatDateTime } from '@/shared/utils/formatters'
import type { ClassAssignment, Enrollment, EnrollmentMovement } from '@/types/enrollment'
import type { ClassGroup, School } from '@/types/school-structure'
import type { ClassAssignmentStatus, MovementType } from '@/types/enums'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const { confirmDelete, confirmAction } = useConfirm()

const enrollmentId = Number(route.params.id)
const enrollment = ref<Enrollment | null>(null)
const movements = ref<EnrollmentMovement[]>([])
const loading = ref(false)

const assignDialogVisible = ref(false)
const assignLoading = ref(false)
const classGroups = ref<ClassGroup[]>([])
const assignForm = ref({ class_group_id: null as number | null, start_date: null as Date | null })

const movementDialogVisible = ref(false)
const movementLoading = ref(false)
const schools = ref<School[]>([])
const movementForm = ref({
  type: null as MovementType | null,
  movement_date: null as Date | null,
  reason: '' as string,
  destination_school_id: null as number | null,
})
const movementTypeOptions = [
  { value: 'transferencia_interna' as MovementType, label: 'Transferencia Interna' },
  { value: 'transferencia_externa' as MovementType, label: 'Transferencia Externa' },
  { value: 'abandono' as MovementType, label: 'Abandono' },
  { value: 'cancelamento' as MovementType, label: 'Cancelamento' },
]

const editAssignmentDialogVisible = ref(false)
const editAssignmentLoading = ref(false)
const editAssignmentForm = ref({
  id: null as number | null,
  class_group_id: null as number | null,
  start_date: null as Date | null,
  end_date: null as Date | null,
  status: null as ClassAssignmentStatus | null,
})
const classAssignmentStatusOptions = [
  { value: 'active' as ClassAssignmentStatus, label: 'Ativa' },
  { value: 'transferred' as ClassAssignmentStatus, label: 'Transferida' },
  { value: 'cancelled' as ClassAssignmentStatus, label: 'Cancelada' },
]

const isTransferType = (type: MovementType | null) =>
  type === 'transferencia_interna' || type === 'transferencia_externa'

function toISODate(date: Date): string {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

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
    classGroups.value = response.data.map(cg => {
      const baseName = [cg.grade_level?.name, cg.name, cg.shift?.name_label].filter(Boolean).join(' - ')
      const active = cg.active_students_count ?? 0
      const max = cg.max_students ?? 0
      const vacancy = max > 0 ? ` (${active}/${max} vagas)` : ''
      return { ...cg, label: baseName + vacancy }
    })
    assignForm.value = { class_group_id: null, start_date: null }
    assignDialogVisible.value = true
  } catch {
    toast.error('Erro ao carregar turmas')
  }
}

async function handleAssign() {
  if (!assignForm.value.class_group_id || !assignForm.value.start_date) return
  assignLoading.value = true
  try {
    const result = await enrollmentService.assignToClass(enrollmentId, {
      class_group_id: assignForm.value.class_group_id,
      start_date: toISODate(assignForm.value.start_date!),
    }) as unknown as Record<string, unknown>
    const warnings = (result?.warnings ?? []) as string[]
    if (warnings.length > 0) {
      warnings.forEach(w => toast.warn(w))
    }
    toast.success('Aluno enturmado')
    assignDialogVisible.value = false
    loadEnrollment()
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao enturmar'))
  } finally {
    assignLoading.value = false
  }
}

async function openMovementDialog() {
  try {
    const response = await schoolStructureService.getSchools({ per_page: 100 })
    schools.value = response.data
  } catch {
    toast.error('Erro ao carregar escolas')
  }
  movementForm.value = { type: null, movement_date: null, reason: '', destination_school_id: null }
  movementDialogVisible.value = true
}

async function handleMovement() {
  if (!movementForm.value.type || !movementForm.value.movement_date) return
  movementLoading.value = true
  try {
    await enrollmentService.transfer(enrollmentId, {
      type: movementForm.value.type,
      movement_date: toISODate(movementForm.value.movement_date!),
      reason: movementForm.value.reason || undefined,
      destination_school_id: movementForm.value.destination_school_id ?? undefined,
    })
    toast.success('Movimentacao registrada')
    movementDialogVisible.value = false
    loadEnrollment()
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao registrar movimentacao'))
  } finally {
    movementLoading.value = false
  }
}

async function openEditAssignment(assignment: ClassAssignment) {
  try {
    const response = await schoolStructureService.getClassGroups({ per_page: 100 })
    classGroups.value = response.data.map(cg => {
      const baseName = [cg.grade_level?.name, cg.name, cg.shift?.name_label].filter(Boolean).join(' - ')
      const active = cg.active_students_count ?? 0
      const max = cg.max_students ?? 0
      const vacancy = max > 0 ? ` (${active}/${max} vagas)` : ''
      return { ...cg, label: baseName + vacancy }
    })
  } catch {
    toast.error('Erro ao carregar turmas')
    return
  }
  editAssignmentForm.value = {
    id: assignment.id,
    class_group_id: assignment.class_group_id,
    start_date: assignment.start_date ? new Date(assignment.start_date + 'T12:00:00') : null,
    end_date: assignment.end_date ? new Date(assignment.end_date + 'T12:00:00') : null,
    status: assignment.status,
  }
  editAssignmentDialogVisible.value = true
}

async function handleEditAssignment() {
  if (!editAssignmentForm.value.id) return
  editAssignmentLoading.value = true
  try {
    const data: Record<string, unknown> = {
      class_group_id: editAssignmentForm.value.class_group_id,
      status: editAssignmentForm.value.status,
      start_date: editAssignmentForm.value.start_date ? toISODate(editAssignmentForm.value.start_date) : null,
      end_date: editAssignmentForm.value.end_date ? toISODate(editAssignmentForm.value.end_date) : null,
    }
    await enrollmentService.updateClassAssignment(enrollmentId, editAssignmentForm.value.id, data)
    toast.success('Enturmacao atualizada')
    editAssignmentDialogVisible.value = false
    loadEnrollment()
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao atualizar enturmacao'))
  } finally {
    editAssignmentLoading.value = false
  }
}

function handleReactivate() {
  confirmAction('Tem certeza que deseja reativar esta matricula?', async () => {
    try {
      await enrollmentService.reactivateEnrollment(enrollmentId)
      toast.success('Matricula reativada')
      loadEnrollment()
    } catch (error: unknown) {
      toast.error(extractApiError(error, 'Erro ao reativar matricula'))
    }
  }, 'Reativar Matricula')
}

function handleDeleteAssignment(assignment: ClassAssignment) {
  confirmDelete(async () => {
    try {
      await enrollmentService.deleteClassAssignment(enrollmentId, assignment.id)
      toast.success('Enturmacao excluida')
      loadEnrollment()
    } catch {
      toast.error('Erro ao excluir enturmacao')
    }
  })
}

onMounted(loadEnrollment)
</script>

<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold text-[#0078D4]">Detalhes da Matricula</h1>
      <div class="flex gap-2">
        <Button v-if="enrollment?.status === 'active'" label="Enturmar" icon="pi pi-users" @click="openAssignDialog" />
        <Button v-if="enrollment?.status === 'active'" label="Movimentar" icon="pi pi-arrow-right-arrow-left" severity="warning" @click="openMovementDialog" />
        <Button v-if="enrollment?.status === 'cancelled'" label="Reativar" icon="pi pi-replay" severity="success" @click="handleReactivate" />
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
          <span class="text-xs font-semibold uppercase text-[#616161]">Tipo</span>
          <span class="text-[0.9375rem]">{{ enrollment.enrollment_type_label ?? '--' }}</span>
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
            {{ [data.class_group?.grade_level?.name, data.class_group?.name, data.class_group?.shift?.name_label].filter(Boolean).join(' - ') || '--' }}
          </template>
        </Column>
        <Column header="Status">
          <template #body="{ data }">
            <StatusBadge :status="data.status" :label="classAssignmentStatusLabel(data.status)" />
          </template>
        </Column>
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
        <Column header="Acoes" :style="{ width: '120px' }">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" text rounded class="mr-1" @click="openEditAssignment(data)" />
            <Button icon="pi pi-trash" text rounded severity="danger" @click="handleDeleteAssignment(data)" />
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
        <Column header="Escola Origem">
          <template #body="{ data }">
            {{ data.origin_school?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Escola Destino">
          <template #body="{ data }">
            {{ data.destination_school?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Criado em">
          <template #body="{ data }">
            {{ formatDateTime(data.created_at) }}
          </template>
        </Column>
      </DataTable>
    </div>

    <EnrollmentDocumentsSection v-if="enrollment" :enrollmentId="enrollmentId" />

    <FormDialog v-model:visible="assignDialogVisible" title="Enturmar Aluno" :loading="assignLoading" @save="handleAssign">
      <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Turma *</label>
          <Select v-model="assignForm.class_group_id" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Selecione" class="w-full" filter />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Data de Inicio *</label>
          <DatePicker v-model="assignForm.start_date" dateFormat="dd/mm/yy" placeholder="dd/mm/aaaa" class="w-full" showIcon />
        </div>
      </div>
    </FormDialog>

    <FormDialog v-model:visible="movementDialogVisible" title="Registrar Movimentacao" :loading="movementLoading" @save="handleMovement">
      <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Tipo *</label>
          <Select v-model="movementForm.type" :options="movementTypeOptions" optionLabel="label" optionValue="value" placeholder="Selecione" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Data *</label>
          <DatePicker v-model="movementForm.movement_date" dateFormat="dd/mm/yy" placeholder="dd/mm/aaaa" class="w-full" showIcon />
        </div>
        <div v-if="isTransferType(movementForm.type)" class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola Destino</label>
          <Select v-model="movementForm.destination_school_id" :options="schools" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" filter showClear />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Motivo</label>
          <InputText v-model="movementForm.reason" placeholder="Motivo da movimentacao" class="w-full" />
        </div>
      </div>
    </FormDialog>

    <FormDialog v-model:visible="editAssignmentDialogVisible" title="Editar Enturmacao" :loading="editAssignmentLoading" @save="handleEditAssignment">
      <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Turma *</label>
          <Select v-model="editAssignmentForm.class_group_id" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Selecione" class="w-full" filter />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Data de Inicio *</label>
          <DatePicker v-model="editAssignmentForm.start_date" dateFormat="dd/mm/yy" placeholder="dd/mm/aaaa" class="w-full" showIcon />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Data de Fim</label>
          <DatePicker v-model="editAssignmentForm.end_date" dateFormat="dd/mm/yy" placeholder="dd/mm/aaaa" class="w-full" showIcon showButtonBar />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Status *</label>
          <Select v-model="editAssignmentForm.status" :options="classAssignmentStatusOptions" optionLabel="label" optionValue="value" placeholder="Selecione" class="w-full" />
        </div>
      </div>
    </FormDialog>
  </div>
</template>
