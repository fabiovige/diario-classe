<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { Radar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  RadialLinearScale,
  PointElement,
  LineElement,
  Filler,
  Tooltip,
  Legend,
} from 'chart.js'

ChartJS.register(RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend)

interface Dataset {
  label: string
  data: (number | null)[]
  borderColor: string
  backgroundColor: string
}

const props = withDefaults(defineProps<{
  labels: string[]
  datasets: Dataset[]
  scaleMax?: number
  passingGrade?: number
}>(), {
  scaleMax: 10,
  passingGrade: 6,
})

const gridColor = ref('#E0E0E0')
const labelColor = ref('#424242')

onMounted(() => {
  const styles = getComputedStyle(document.documentElement)
  gridColor.value = styles.getPropertyValue('--surface-border').trim() || '#E0E0E0'
  labelColor.value = styles.getPropertyValue('--text-color').trim() || '#424242'
})

const chartData = computed(() => ({
  labels: props.labels,
  datasets: props.datasets.map(ds => ({
    ...ds,
    data: ds.data.map(v => v ?? 0),
    borderWidth: 2,
    pointRadius: 4,
    pointHoverRadius: 6,
    fill: true,
  })),
}))

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    r: {
      min: 0,
      max: props.scaleMax,
      ticks: {
        stepSize: props.scaleMax <= 10 ? 2 : 5,
        backdropColor: 'transparent',
        font: { size: 11 },
        color: labelColor.value,
      },
      grid: {
        color: gridColor.value,
      },
      angleLines: {
        color: gridColor.value,
      },
      pointLabels: {
        font: { size: 12 },
        color: labelColor.value,
      },
    },
  },
  plugins: {
    legend: {
      position: 'top' as const,
      labels: {
        usePointStyle: true,
        padding: 16,
        font: { size: 12 },
        color: labelColor.value,
      },
    },
    tooltip: {
      callbacks: {
        label: (ctx: { dataset: { label: string }; raw: number }) =>
          `${ctx.dataset.label}: ${ctx.raw.toFixed(1)}`,
      },
    },
  },
}))
</script>

<template>
  <div class="relative" style="height: 320px">
    <Radar v-if="labels.length > 0 && datasets.length > 0" :data="chartData" :options="(chartOptions as any)" />
    <div v-else class="flex h-full items-center justify-center text-sm text-md-text-secondary">
      Sem dados para exibir
    </div>
  </div>
</template>
