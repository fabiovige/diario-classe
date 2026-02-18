<script setup lang="ts">
import { onMounted, ref } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Toolbar from 'primevue/toolbar'
import Textarea from 'primevue/textarea'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import FormDialog from '@/shared/components/FormDialog.vue'
import { attendanceService } from '@/services/attendance.service'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import { formatDate } from '@/shared/utils/formatters'
import type { AbsenceJustification } from '@/types/attendance'

const toast = useToast()
const { confirmAction } = useConfirm()

const items = ref<AbsenceJustification[]>([])
const loading = ref(false)

const dialogVisible = ref(false)
const dialogLoading = ref(false)

const form = ref({
  student_id: null as number | null,
  start_date: '',
  end_date: '',
  reason: '',
  document_path: '',
})

async function loadData() {
  loading.value = true
  try {
    await attendanceService.getConfigs()
    items.value = []
  } catch {
    toast.error('Erro ao carregar justificativas')
  } finally {
    loading.value = false
  }
}

function openDialog() {
  form.value = { student_id: null, start_date: '', end_date: '', reason: '', document_path: '' }
  dialogVisible.value = true
}

async function handleSave() {
  dialogLoading.value = true
  try {
    await attendanceService.createJustification(form.value)
    toast.success('Justificativa criada')
    dialogVisible.value = false
    loadData()
  } catch (error: any) {
    toast.error(error.response?.data?.error ?? 'Erro ao criar justificativa')
  } finally {
    dialogLoading.value = false
  }
}

function handleApprove(justification: AbsenceJustification) {
  confirmAction('Deseja aprovar esta justificativa?', async () => {
    try {
      await attendanceService.approveJustification(justification.id)
      toast.success('Justificativa aprovada')
      loadData()
    } catch {
      toast.error('Erro ao aprovar justificativa')
    }
  })
}

onMounted(loadData)
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">Justificativas de Falta</h1>

    <div class="card-section">
      <Toolbar class="mb-3">
        <template #start />
        <template #end>
          <Button label="Nova Justificativa" icon="pi pi-plus" @click="openDialog" />
        </template>
      </Toolbar>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhuma justificativa encontrada" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column header="Periodo">
          <template #body="{ data }">
            {{ formatDate(data.start_date) }} - {{ formatDate(data.end_date) }}
          </template>
        </Column>
        <Column field="reason" header="Motivo" />
        <Column header="Status">
          <template #body="{ data }">
            <StatusBadge :status="data.approved ? 'approved' : 'pending'" :label="data.approved ? 'Aprovada' : 'Pendente'" />
          </template>
        </Column>
        <Column header="Criado em">
          <template #body="{ data }">
            {{ formatDate(data.created_at) }}
          </template>
        </Column>
        <Column header="Acoes" :style="{ width: '100px' }">
          <template #body="{ data }">
            <Button v-if="!data.approved" label="Aprovar" size="small" severity="success" @click="handleApprove(data)" />
          </template>
        </Column>
      </DataTable>
    </div>

    <FormDialog v-model:visible="dialogVisible" title="Nova Justificativa" :loading="dialogLoading" @save="handleSave">
      <div class="dialog-form">
        <div class="field">
          <label>ID do Aluno *</label>
          <InputText :modelValue="String(form.student_id ?? '')" @update:modelValue="form.student_id = $event ? Number($event) : null" type="number" required class="w-full" />
        </div>
        <div class="field">
          <label>Data Inicio *</label>
          <InputText v-model="form.start_date" type="date" required class="w-full" />
        </div>
        <div class="field">
          <label>Data Fim *</label>
          <InputText v-model="form.end_date" type="date" required class="w-full" />
        </div>
        <div class="field">
          <label>Motivo *</label>
          <Textarea v-model="form.reason" rows="3" class="w-full" />
        </div>
        <div class="field">
          <label>Caminho do Documento</label>
          <InputText v-model="form.document_path" class="w-full" />
        </div>
      </div>
    </FormDialog>
  </div>
</template>

<style scoped>
.mb-3 { margin-bottom: 1rem; }
.dialog-form { display: flex; flex-direction: column; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.375rem; }
.field label { font-size: 0.8125rem; font-weight: 500; }
.w-full { width: 100%; }
</style>
