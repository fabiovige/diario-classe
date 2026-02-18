<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Button from 'primevue/button'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import { periodClosingService } from '@/services/period-closing.service'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import { periodClosingStatusLabel } from '@/shared/utils/enum-labels'
import { formatDateTime } from '@/shared/utils/formatters'
import type { PeriodClosing } from '@/types/period-closing'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const { confirmAction } = useConfirm()

const closingId = Number(route.params.id)
const closing = ref<PeriodClosing | null>(null)
const loading = ref(false)

async function loadClosing() {
  loading.value = true
  try {
    const response = await periodClosingService.getClosings({ per_page: 100 })
    closing.value = response.data.find(c => c.id === closingId) ?? null
    if (!closing.value) {
      toast.error('Fechamento nao encontrado')
      router.push('/period-closing')
    }
  } catch {
    toast.error('Erro ao carregar fechamento')
    router.push('/period-closing')
  } finally {
    loading.value = false
  }
}

function handleCheck() {
  confirmAction('Deseja verificar os dados deste fechamento?', async () => {
    try {
      closing.value = await periodClosingService.check(closingId)
      toast.success('Verificacao concluida')
    } catch (error: any) {
      toast.error(error.response?.data?.error ?? 'Erro na verificacao')
    }
  })
}

function handleSubmit() {
  confirmAction('Deseja enviar este fechamento para validacao?', async () => {
    try {
      closing.value = await periodClosingService.submit(closingId)
      toast.success('Fechamento enviado')
    } catch (error: any) {
      toast.error(error.response?.data?.error ?? 'Erro ao enviar')
    }
  })
}

function handleValidate() {
  confirmAction('Deseja validar este fechamento?', async () => {
    try {
      closing.value = await periodClosingService.validate(closingId)
      toast.success('Fechamento validado')
    } catch (error: any) {
      toast.error(error.response?.data?.error ?? 'Erro ao validar')
    }
  })
}

function handleClose() {
  confirmAction('Deseja fechar definitivamente este periodo? Esta acao nao pode ser desfeita.', async () => {
    try {
      closing.value = await periodClosingService.close(closingId)
      toast.success('Periodo fechado')
    } catch (error: any) {
      toast.error(error.response?.data?.error ?? 'Erro ao fechar')
    }
  }, 'Fechar Periodo')
}

onMounted(loadClosing)
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Detalhe do Fechamento</h1>
      <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" @click="router.push('/period-closing')" />
    </div>

    <div v-if="closing" class="card-section">
      <div class="info-grid">
        <div class="info-item">
          <span class="info-label">Periodo</span>
          <span class="info-value">{{ closing.assessment_period?.name ?? '--' }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Status</span>
          <StatusBadge :status="closing.status" :label="periodClosingStatusLabel(closing.status)" />
        </div>
        <div class="info-item">
          <span class="info-label">Enviado em</span>
          <span class="info-value">{{ closing.submitted_at ? formatDateTime(closing.submitted_at) : '--' }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Validado em</span>
          <span class="info-value">{{ closing.validated_at ? formatDateTime(closing.validated_at) : '--' }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Aprovado em</span>
          <span class="info-value">{{ closing.approved_at ? formatDateTime(closing.approved_at) : '--' }}</span>
        </div>
        <div v-if="closing.rejection_reason" class="info-item">
          <span class="info-label">Motivo Rejeicao</span>
          <span class="info-value rejection">{{ closing.rejection_reason }}</span>
        </div>
      </div>
    </div>

    <div v-if="closing" class="card-section mt-3">
      <h2 class="section-title">Checklist</h2>
      <div class="checklist">
        <div class="checklist-item">
          <i :class="closing.all_grades_complete ? 'pi pi-check-circle text-success' : 'pi pi-times-circle text-danger'" />
          <span>Todas as notas lancadas</span>
        </div>
        <div class="checklist-item">
          <i :class="closing.all_attendance_complete ? 'pi pi-check-circle text-success' : 'pi pi-times-circle text-danger'" />
          <span>Toda frequencia registrada</span>
        </div>
        <div class="checklist-item">
          <i :class="closing.all_lesson_records_complete ? 'pi pi-check-circle text-success' : 'pi pi-times-circle text-danger'" />
          <span>Todos registros de aula preenchidos</span>
        </div>
      </div>
    </div>

    <div v-if="closing" class="card-section mt-3">
      <h2 class="section-title">Acoes</h2>
      <div class="action-bar">
        <Button v-if="closing.status === 'pending'" label="Verificar" icon="pi pi-search" severity="info" @click="handleCheck" />
        <Button v-if="closing.status === 'pending'" label="Enviar" icon="pi pi-send" severity="warn" @click="handleSubmit" />
        <Button v-if="closing.status === 'submitted'" label="Validar" icon="pi pi-check" severity="info" @click="handleValidate" />
        <Button v-if="closing.status === 'validated'" label="Fechar Periodo" icon="pi pi-lock" severity="success" @click="handleClose" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.25rem; }
.info-item { display: flex; flex-direction: column; gap: 0.25rem; }
.info-label { font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; }
.info-value { font-size: 0.9375rem; }
.rejection { color: #ef4444; }
.section-title { font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem; }
.mt-3 { margin-top: 1.5rem; }
.checklist { display: flex; flex-direction: column; gap: 0.75rem; }
.checklist-item { display: flex; align-items: center; gap: 0.75rem; font-size: 0.9375rem; }
.checklist-item i { font-size: 1.25rem; }
.text-success { color: #22c55e; }
.text-danger { color: #ef4444; }
.action-bar { display: flex; gap: 0.75rem; flex-wrap: wrap; }
</style>
