<script setup lang="ts">
import Tag from 'primevue/tag'
import { computed } from 'vue'

interface Props {
  status: string
  label?: string
}

const props = defineProps<Props>()

const severityMap: Record<string, string> = {
  active: 'success',
  approved: 'success',
  present: 'success',
  closed: 'success',
  completed: 'success',
  council_approved: 'success',
  validated: 'info',
  justified: 'info',
  dispensed: 'info',
  submitted: 'warn',
  planning: 'warn',
  pending: 'warn',
  inactive: 'secondary',
  blocked: 'danger',
  absent: 'danger',
  rejected: 'danger',
  retained: 'danger',
  retained_by_absence: 'danger',
  transferred: 'secondary',
  cancelled: 'secondary',
}

const severity = computed(() => (severityMap[props.status] ?? 'secondary') as any)
</script>

<template>
  <Tag :value="label ?? status" :severity="severity" />
</template>
