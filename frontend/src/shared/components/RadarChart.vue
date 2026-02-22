<script setup lang="ts">
import { computed } from 'vue'
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
      },
      grid: {
        color: '#E0E0E0',
      },
      angleLines: {
        color: '#E0E0E0',
      },
      pointLabels: {
        font: { size: 12 },
        color: '#424242',
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
    <div v-else class="flex h-full items-center justify-center text-sm text-[#616161]">
      Sem dados para exibir
    </div>
  </div>
</template>
