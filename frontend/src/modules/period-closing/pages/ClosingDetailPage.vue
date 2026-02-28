<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import ProgressSpinner from 'primevue/progressspinner'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import { periodClosingService } from '@/services/period-closing.service'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import { useAuthStore } from '@/stores/auth'
import { periodClosingStatusLabel } from '@/shared/utils/enum-labels'
import { formatDateTime } from '@/shared/utils/formatters'
import { extractApiError } from '@/shared/utils/api-error'
import type { PeriodClosing } from '@/types/period-closing'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const auth = useAuthStore()
const { confirmAction } = useConfirm()

const closingId = Number(route.params.id)
const closing = ref<PeriodClosing | null>(null)
const loading = ref(true)
const checking = ref(false)
const closing_ = ref(false)
const approving = ref(false)
const rejecting = ref(false)
const reopening = ref(false)
const rejectionReason = ref('')
const reopenReason = ref('')

const isTeacher = computed(() => auth.hasRole('teacher'))
const isCoordinatorOrDirector = computed(() => auth.hasRole('coordinator', 'director', 'admin'))

async function loadClosing() {
  loading.value = true
  try {
    closing.value = await periodClosingService.getClosing(closingId)
  } catch {
    toast.error('Fechamento nao encontrado')
    router.push('/period-closing')
  } finally {
    loading.value = false
  }
}

function disciplineName(c: PeriodClosing): string {
  return c.teacher_assignment?.curricular_component?.name
    ?? c.teacher_assignment?.experience_field?.name
    ?? '--'
}

function turmaName(c: PeriodClosing): string {
  if (!c.class_group) return '--'
  const grade = c.class_group.grade_level?.name ?? ''
  const shift = c.class_group.shift?.name_label ?? ''
  const parts = [c.class_group.name, grade, shift].filter(Boolean)
  return parts.join(' - ')
}

function handleCheck() {
  confirmAction('Deseja verificar os dados deste fechamento? Isso vai checar se notas, frequencia e registros de aula estao completos.', async () => {
    checking.value = true
    try {
      closing.value = await periodClosingService.check(closingId)
      toast.success('Verificacao concluida')
    } catch (error: unknown) {
      toast.error(extractApiError(error, 'Erro na verificacao'))
    } finally {
      checking.value = false
    }
  })
}

function handleTeacherClose() {
  confirmAction('Deseja fechar este bimestre? O sistema verificara automaticamente se todos os dados estao completos.', async () => {
    closing_.value = true
    try {
      closing.value = await periodClosingService.teacherClose(closingId)
      toast.success('Bimestre fechado com sucesso')
    } catch (error: unknown) {
      toast.error(extractApiError(error, 'Erro ao fechar bimestre'))
    } finally {
      closing_.value = false
    }
  }, 'Fechar Bimestre')
}

function handleApprove() {
  confirmAction('Deseja aprovar este fechamento?', async () => {
    approving.value = true
    try {
      closing.value = await periodClosingService.validate(closingId, { approve: true })
      toast.success('Fechamento aprovado')
    } catch (error: unknown) {
      toast.error(extractApiError(error, 'Erro ao aprovar'))
    } finally {
      approving.value = false
    }
  })
}

function handleReject() {
  if (!rejectionReason.value.trim()) {
    toast.warn('Informe o motivo da rejeicao')
    return
  }
  confirmAction('Deseja rejeitar este fechamento e devolver ao professor?', async () => {
    rejecting.value = true
    try {
      closing.value = await periodClosingService.validate(closingId, {
        approve: false,
        rejection_reason: rejectionReason.value.trim(),
      })
      rejectionReason.value = ''
      toast.success('Fechamento rejeitado e devolvido')
    } catch (error: unknown) {
      toast.error(extractApiError(error, 'Erro ao rejeitar'))
    } finally {
      rejecting.value = false
    }
  })
}

function handleClose() {
  confirmAction('Deseja fechar definitivamente este periodo? Esta acao nao pode ser desfeita.', async () => {
    closing_.value = true
    try {
      closing.value = await periodClosingService.close(closingId)
      toast.success('Periodo fechado com sucesso')
    } catch (error: unknown) {
      toast.error(extractApiError(error, 'Erro ao fechar'))
    } finally {
      closing_.value = false
    }
  }, 'Fechar Periodo')
}

function handleReopen() {
  if (!reopenReason.value.trim()) {
    toast.warn('Informe o motivo da reabertura')
    return
  }
  confirmAction('Deseja reabrir este fechamento? O professor podera fazer alteracoes novamente.', async () => {
    reopening.value = true
    try {
      closing.value = await periodClosingService.reopen(closingId, reopenReason.value.trim())
      reopenReason.value = ''
      toast.success('Fechamento reaberto')
    } catch (error: unknown) {
      toast.error(extractApiError(error, 'Erro ao reabrir'))
    } finally {
      reopening.value = false
    }
  }, 'Reabrir')
}

onMounted(loadClosing)
</script>

<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold text-[#0078D4]">Detalhe do Fechamento</h1>
      <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" @click="router.push('/period-closing')" />
    </div>

    <div v-if="loading" class="flex items-center justify-center py-16">
      <ProgressSpinner strokeWidth="3" />
    </div>

    <template v-if="closing">
      <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
        <div class="grid grid-cols-[repeat(auto-fill,minmax(250px,1fr))] gap-5">
          <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold uppercase text-[#616161]">Turma</span>
            <span class="text-[0.9375rem] font-medium">{{ turmaName(closing) }}</span>
          </div>
          <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold uppercase text-[#616161]">Disciplina</span>
            <span class="text-[0.9375rem] font-medium">{{ disciplineName(closing) }}</span>
          </div>
          <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold uppercase text-[#616161]">Periodo</span>
            <span class="text-[0.9375rem]">{{ closing.assessment_period?.name ?? '--' }}</span>
          </div>
          <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold uppercase text-[#616161]">Status</span>
            <StatusBadge :status="closing.status" :label="periodClosingStatusLabel(closing.status)" />
          </div>
          <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold uppercase text-[#616161]">Enviado em</span>
            <span class="text-[0.9375rem]">{{ closing.submitted_at ? formatDateTime(closing.submitted_at) : '--' }}</span>
          </div>
          <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold uppercase text-[#616161]">Validado em</span>
            <span class="text-[0.9375rem]">{{ closing.validated_at ? formatDateTime(closing.validated_at) : '--' }}</span>
          </div>
          <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold uppercase text-[#616161]">Aprovado em</span>
            <span class="text-[0.9375rem]">{{ closing.approved_at ? formatDateTime(closing.approved_at) : '--' }}</span>
          </div>
          <div v-if="closing.rejection_reason" class="col-span-full flex flex-col gap-1">
            <span class="text-xs font-semibold uppercase text-[#616161]">Motivo Rejeicao/Reabertura</span>
            <span class="text-[0.9375rem] text-[#C42B1C]">{{ closing.rejection_reason }}</span>
          </div>
        </div>
      </div>

      <div class="mt-6 rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold mb-4">Checklist de Completude</h2>
        <div class="flex flex-col gap-3">
          <div class="flex items-center gap-3 text-[0.9375rem]">
            <i :class="closing.all_grades_complete ? 'pi pi-check-circle text-[#0F7B0F] text-xl' : 'pi pi-times-circle text-[#C42B1C] text-xl'" />
            <span>Todas as notas lancadas</span>
          </div>
          <div class="flex items-center gap-3 text-[0.9375rem]">
            <i :class="closing.all_attendance_complete ? 'pi pi-check-circle text-[#0F7B0F] text-xl' : 'pi pi-times-circle text-[#C42B1C] text-xl'" />
            <span>Toda frequencia registrada</span>
          </div>
          <div class="flex items-center gap-3 text-[0.9375rem]">
            <i :class="closing.all_lesson_records_complete ? 'pi pi-check-circle text-[#0F7B0F] text-xl' : 'pi pi-times-circle text-[#C42B1C] text-xl'" />
            <span>Todos registros de aula preenchidos</span>
          </div>
        </div>
      </div>

      <div class="mt-6 rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold mb-4">Acoes</h2>

        <!-- PENDING: Professor pode fechar direto ou enviar para validacao -->
        <div v-if="closing.status === 'pending'" class="flex flex-col gap-4">
          <template v-if="isTeacher">
            <p class="text-sm text-[#616161]">
              Clique em <strong>Fechar Bimestre</strong> para fechar diretamente (o sistema verificara automaticamente se os dados estao completos).
            </p>
            <div class="flex flex-wrap gap-3">
              <Button label="Fechar Bimestre" icon="pi pi-check-circle" severity="success" :loading="closing_" @click="handleTeacherClose" />
              <Button label="Verificar Completude" icon="pi pi-search" severity="info" outlined :loading="checking" @click="handleCheck" />
            </div>
          </template>
          <template v-else>
            <p class="text-sm text-[#616161]">
              Use <strong>Verificar</strong> para checar se notas, frequencia e registros estao completos.
              Quando tudo estiver OK, clique em <strong>Fechar</strong>.
            </p>
            <div class="flex flex-wrap gap-3">
              <Button label="Verificar Completude" icon="pi pi-search" severity="info" outlined :loading="checking" @click="handleCheck" />
              <Button label="Fechar" icon="pi pi-lock" severity="success" :loading="closing_" @click="handleClose" />
            </div>
          </template>
        </div>

        <!-- IN_VALIDATION: Coordenacao aprova ou rejeita -->
        <div v-else-if="closing.status === 'in_validation'" class="flex flex-col gap-4">
          <p class="text-sm text-[#616161]">
            Este fechamento esta aguardando validacao da coordenacao. Aprove ou rejeite com justificativa.
          </p>
          <div class="flex flex-col gap-3">
            <div class="flex flex-col gap-1.5">
              <label class="text-[0.8125rem] font-medium">Motivo da rejeicao (obrigatorio para rejeitar)</label>
              <InputText v-model="rejectionReason" placeholder="Informe o motivo..." class="w-full max-w-[500px]" />
            </div>
            <div class="flex flex-wrap gap-3">
              <Button label="Aprovar" icon="pi pi-check" severity="success" :loading="approving" @click="handleApprove" />
              <Button label="Rejeitar" icon="pi pi-times" severity="danger" :loading="rejecting" @click="handleReject" />
            </div>
          </div>
        </div>

        <!-- APPROVED: Fechar definitivamente -->
        <div v-else-if="closing.status === 'approved'" class="flex flex-col gap-4">
          <p class="text-sm text-[#616161]">
            Fechamento aprovado. Clique para fechar definitivamente o periodo.
          </p>
          <div class="flex flex-wrap gap-3">
            <Button label="Fechar Periodo" icon="pi pi-lock" severity="success" :loading="closing_" @click="handleClose" />
          </div>
        </div>

        <!-- CLOSED: Mensagem + opcao de reabrir para coordenador/diretor -->
        <div v-else-if="closing.status === 'closed'" class="flex flex-col gap-4">
          <p class="text-sm text-[#0F7B0F] font-medium">
            <i class="pi pi-lock mr-1" /> Este periodo esta fechado.
          </p>

          <template v-if="isCoordinatorOrDirector">
            <div class="border-t border-[#E0E0E0] pt-4">
              <p class="text-sm text-[#616161] mb-3">
                Como coordenador/diretor, voce pode reabrir este fechamento para que o professor faca correcoes.
              </p>
              <div class="flex flex-col gap-3">
                <div class="flex flex-col gap-1.5">
                  <label class="text-[0.8125rem] font-medium">Motivo da reabertura</label>
                  <InputText v-model="reopenReason" placeholder="Informe o motivo..." class="w-full max-w-[500px]" />
                </div>
                <div>
                  <Button label="Reabrir Fechamento" icon="pi pi-replay" severity="warn" :loading="reopening" @click="handleReopen" />
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </template>
  </div>
</template>
