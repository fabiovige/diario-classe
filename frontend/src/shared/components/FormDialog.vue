<script setup lang="ts">
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'

interface Props {
  visible: boolean
  title: string
  loading?: boolean
  width?: string
}

withDefaults(defineProps<Props>(), {
  loading: false,
  width: '500px',
})

const emit = defineEmits<{
  'update:visible': [value: boolean]
  save: []
  cancel: []
}>()

function close() {
  emit('update:visible', false)
  emit('cancel')
}
</script>

<template>
  <Dialog
    :visible="visible"
    :header="title"
    :style="{ width }"
    modal
    :closable="!loading"
    :draggable="false"
    @update:visible="emit('update:visible', $event)"
  >
    <slot />

    <template #footer>
      <Button
        label="Cancelar"
        icon="pi pi-times"
        text
        :disabled="loading"
        @click="close"
      />
      <Button
        label="Salvar"
        icon="pi pi-check"
        :loading="loading"
        @click="emit('save')"
      />
    </template>
  </Dialog>
</template>
