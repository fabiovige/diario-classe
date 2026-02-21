<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Checkbox from 'primevue/checkbox'
import { enrollmentService } from '@/services/enrollment.service'
import { useToast } from '@/composables/useToast'
import { formatDate } from '@/shared/utils/formatters'
import type { EnrollmentDocument } from '@/types/enrollment'
import type { DocumentType } from '@/types/enums'

const props = defineProps<{ enrollmentId: number }>()
const toast = useToast()
const documents = ref<EnrollmentDocument[]>([])
const loading = ref(false)

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

async function toggleDelivered(doc: EnrollmentDocument) {
  try {
    const updated = await enrollmentService.toggleDocument(
      props.enrollmentId,
      doc.document_type as DocumentType,
      { delivered: !doc.delivered },
    )
    const index = documents.value.findIndex(d => d.document_type === doc.document_type)
    if (index >= 0) {
      documents.value[index] = updated
    }
  } catch {
    toast.error('Erro ao atualizar documento')
  }
}

const deliveredCount = () => documents.value.filter(d => d.delivered).length

onMounted(loadDocuments)
</script>

<template>
  <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm mt-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold">Documentos</h2>
      <span v-if="documents.length > 0" class="text-sm text-[#616161]">
        {{ deliveredCount() }}/{{ documents.length }} entregues
      </span>
    </div>

    <div v-if="loading" class="text-sm text-[#616161]">Carregando...</div>

    <div v-if="!loading && documents.length > 0" class="flex flex-col gap-3">
      <div
        v-for="doc in documents"
        :key="doc.document_type"
        class="flex items-center justify-between rounded border border-[#E0E0E0] px-4 py-3"
        :class="doc.delivered ? 'bg-[#F0FFF4] border-[#38A169]' : 'bg-white'"
      >
        <div class="flex items-center gap-3">
          <Checkbox
            :modelValue="doc.delivered"
            :binary="true"
            @update:modelValue="toggleDelivered(doc)"
          />
          <span class="text-[0.9375rem]">{{ doc.document_type_label }}</span>
        </div>
        <span v-if="doc.delivered && doc.delivered_at" class="text-xs text-[#616161]">
          Entregue em {{ formatDate(doc.delivered_at) }}
        </span>
      </div>
    </div>
  </div>
</template>
