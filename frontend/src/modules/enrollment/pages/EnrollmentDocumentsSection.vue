<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Textarea from 'primevue/textarea'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import { enrollmentService } from '@/services/enrollment.service'
import { useToast } from '@/composables/useToast'
import type { EnrollmentDocument } from '@/types/enrollment'

const props = defineProps<{ enrollmentId: number }>()
const toast = useToast()

const documents = ref<EnrollmentDocument[]>([])
const loading = ref(false)

const previewVisible = ref(false)
const previewUrl = ref('')
const previewMimeType = ref('')
const previewTitle = ref('')
const previewLoading = ref(false)

const rejectVisible = ref(false)
const rejectReason = ref('')
const rejectDoc = ref<EnrollmentDocument | null>(null)
const rejectLoading = ref(false)

const uploadLoading = ref<string | null>(null)
const reviewLoading = ref<string | null>(null)

const approvedCount = computed(() => documents.value.filter(d => d.status === 'approved').length)

function formatFileSize(bytes: number | null): string {
  if (!bytes) return ''
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

function cardClass(status: string): string {
  const base = 'rounded-lg border px-5 py-4 transition-colors'
  const styles: Record<string, string> = {
    not_uploaded: `${base} bg-white border-[#E0E0E0]`,
    pending_review: `${base} bg-[#FFFBEB] border-[#F59E0B]`,
    approved: `${base} bg-[#F0FFF4] border-[#38A169]`,
    rejected: `${base} bg-[#FFF5F5] border-[#E53E3E]`,
  }
  return styles[status] ?? `${base} bg-white border-[#E0E0E0]`
}

function isImage(mimeType: string | null): boolean {
  return mimeType?.startsWith('image/') ?? false
}

async function loadDocuments() {
  loading.value = true
  try {
    documents.value = await enrollmentService.getDocuments(props.enrollmentId)
  } catch {
    toast.error('Erro ao carregar documentos')
  } finally {
    loading.value = false
  }
}

function triggerUpload(documentType: string) {
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = '.pdf,.jpg,.jpeg,.png,.webp'
  input.onchange = async () => {
    const file = input.files?.[0]
    if (!file) return

    if (file.size > 10 * 1024 * 1024) {
      toast.error('Arquivo excede o tamanho maximo de 10MB')
      return
    }

    uploadLoading.value = documentType
    try {
      await enrollmentService.uploadDocument(props.enrollmentId, documentType, file)
      toast.success('Documento enviado com sucesso')
      await loadDocuments()
    } catch {
      toast.error('Erro ao enviar documento')
    } finally {
      uploadLoading.value = null
    }
  }
  input.click()
}

async function handlePreview(doc: EnrollmentDocument) {
  previewLoading.value = true
  previewTitle.value = doc.document_type_label
  previewMimeType.value = doc.mime_type ?? ''
  previewVisible.value = true
  try {
    previewUrl.value = await enrollmentService.previewDocument(props.enrollmentId, doc.document_type)
  } catch {
    toast.error('Erro ao carregar preview do documento')
    previewVisible.value = false
  } finally {
    previewLoading.value = false
  }
}

function closePreview() {
  previewVisible.value = false
  if (previewUrl.value) {
    window.URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = ''
  }
}

async function handleDownload(doc: EnrollmentDocument) {
  try {
    await enrollmentService.downloadDocument(
      props.enrollmentId,
      doc.document_type,
      doc.original_filename ?? `${doc.document_type_label}.pdf`,
    )
  } catch {
    toast.error('Erro ao baixar documento')
  }
}

async function handleApprove(doc: EnrollmentDocument) {
  reviewLoading.value = doc.document_type
  try {
    await enrollmentService.reviewDocument(props.enrollmentId, doc.document_type, 'approve')
    toast.success('Documento aprovado')
    await loadDocuments()
  } catch {
    toast.error('Erro ao aprovar documento')
  } finally {
    reviewLoading.value = null
  }
}

function openRejectDialog(doc: EnrollmentDocument) {
  rejectDoc.value = doc
  rejectReason.value = ''
  rejectVisible.value = true
}

async function confirmReject() {
  if (!rejectDoc.value || !rejectReason.value.trim()) return

  rejectLoading.value = true
  try {
    await enrollmentService.reviewDocument(
      props.enrollmentId,
      rejectDoc.value.document_type,
      'reject',
      rejectReason.value.trim(),
    )
    toast.success('Documento rejeitado')
    rejectVisible.value = false
    await loadDocuments()
  } catch {
    toast.error('Erro ao rejeitar documento')
  } finally {
    rejectLoading.value = false
  }
}

onMounted(loadDocuments)

onUnmounted(() => {
  if (previewUrl.value) {
    window.URL.revokeObjectURL(previewUrl.value)
  }
})
</script>

<template>
  <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm mt-6">
    <div class="flex items-center justify-between mb-5">
      <h2 class="text-lg font-semibold">Documentos</h2>
      <span v-if="documents.length > 0" class="text-sm text-[#616161]">
        {{ approvedCount }}/{{ documents.length }} aprovados
      </span>
    </div>

    <div v-if="loading" class="text-sm text-[#616161]">Carregando...</div>

    <div v-if="!loading && documents.length > 0" class="flex flex-col gap-3">
      <div
        v-for="doc in documents"
        :key="doc.document_type"
        :class="cardClass(doc.status)"
      >
        <div class="flex items-center justify-between mb-1">
          <span class="font-medium text-[0.9375rem]">{{ doc.document_type_label }}</span>
          <StatusBadge :status="doc.status" :label="doc.status_label" />
        </div>

        <div v-if="doc.has_file" class="text-xs text-[#616161] mb-2">
          {{ doc.original_filename }}
          <span v-if="doc.file_size"> &middot; {{ formatFileSize(doc.file_size) }}</span>
        </div>

        <div v-if="doc.status === 'rejected' && doc.rejection_reason" class="text-sm text-[#E53E3E] mb-2">
          <span class="font-medium">Motivo:</span> {{ doc.rejection_reason }}
        </div>

        <div class="flex items-center gap-2 mt-2">
          <template v-if="doc.status === 'not_uploaded'">
            <Button
              label="Enviar Documento"
              icon="pi pi-upload"
              size="small"
              outlined
              :loading="uploadLoading === doc.document_type"
              @click="triggerUpload(doc.document_type)"
            />
          </template>

          <template v-if="doc.status === 'pending_review'">
            <Button
              label="Visualizar"
              icon="pi pi-eye"
              size="small"
              outlined
              @click="handlePreview(doc)"
            />
            <Button
              label="Baixar"
              icon="pi pi-download"
              size="small"
              outlined
              @click="handleDownload(doc)"
            />
            <Button
              label="Aprovar"
              icon="pi pi-check"
              size="small"
              severity="success"
              :loading="reviewLoading === doc.document_type"
              @click="handleApprove(doc)"
            />
            <Button
              label="Rejeitar"
              icon="pi pi-times"
              size="small"
              severity="danger"
              outlined
              @click="openRejectDialog(doc)"
            />
          </template>

          <template v-if="doc.status === 'approved' && doc.has_file">
            <Button
              label="Visualizar"
              icon="pi pi-eye"
              size="small"
              outlined
              @click="handlePreview(doc)"
            />
            <Button
              label="Baixar"
              icon="pi pi-download"
              size="small"
              outlined
              @click="handleDownload(doc)"
            />
          </template>

          <template v-if="doc.status === 'rejected'">
            <Button
              label="Enviar Novamente"
              icon="pi pi-refresh"
              size="small"
              outlined
              :loading="uploadLoading === doc.document_type"
              @click="triggerUpload(doc.document_type)"
            />
          </template>
        </div>
      </div>
    </div>

    <!-- Preview Dialog -->
    <Dialog
      v-model:visible="previewVisible"
      :header="previewTitle"
      modal
      :style="{ width: '90vw', height: '90vh' }"
      :maximizable="true"
      @hide="closePreview"
    >
      <div v-if="previewLoading" class="flex items-center justify-center h-full">
        <span class="text-[#616161]">Carregando...</span>
      </div>
      <div v-if="!previewLoading && previewUrl" class="w-full h-full flex items-center justify-center">
        <img
          v-if="isImage(previewMimeType)"
          :src="previewUrl"
          :alt="previewTitle"
          class="max-w-full max-h-[75vh] object-contain"
        />
        <iframe
          v-else
          :src="previewUrl"
          class="w-full h-[75vh] border-0"
        />
      </div>
    </Dialog>

    <!-- Reject Dialog -->
    <Dialog
      v-model:visible="rejectVisible"
      header="Rejeitar Documento"
      modal
      :style="{ width: '500px' }"
    >
      <div class="flex flex-col gap-3">
        <p class="text-sm text-[#616161]">
          Informe o motivo da rejeicao de <strong>{{ rejectDoc?.document_type_label }}</strong>:
        </p>
        <Textarea
          v-model="rejectReason"
          rows="4"
          placeholder="Motivo da rejeicao..."
          class="w-full"
        />
      </div>

      <template #footer>
        <Button
          label="Cancelar"
          icon="pi pi-times"
          text
          :disabled="rejectLoading"
          @click="rejectVisible = false"
        />
        <Button
          label="Rejeitar"
          icon="pi pi-times"
          severity="danger"
          :loading="rejectLoading"
          :disabled="!rejectReason.trim()"
          @click="confirmReject"
        />
      </template>
    </Dialog>
  </div>
</template>
