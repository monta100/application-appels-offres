<template>
  <div class="card shadow-sm p-3 mb-4">
    <h5 class="mb-3">📅 Appels publiés par semaine</h5>
    <div style="height: 320px;">
      <Bar :data="chartData" :options="chartOptions" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';

ChartJS.register(
  Title, Tooltip, Legend,
  BarElement, CategoryScale, LinearScale,
  ChartDataLabels
);

const props = defineProps({
  data: {
    type: Array,
    required: true
  }
});

const chartData = computed(() => ({
  labels: ['S-5', 'S-4', 'S-3', 'S-2', 'S-1', 'Cette semaine'],
  datasets: [
    {
      label: 'Appels publiés',
      data: props.data,
      backgroundColor: 'rgba(255, 99, 132, 0.85)',
      hoverBackgroundColor: 'rgba(255, 99, 132, 1)',
      borderRadius: 8,
      barThickness: 30
    }
  ]
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    datalabels: {
      anchor: 'end',
      align: 'top',
      font: { weight: 'bold', size: 13 },
      color: '#222'
    }
  },
  scales: {
    y: {
      ticks: { color: '#555', font: { size: 12 } },
      beginAtZero: true
    },
    x: {
      ticks: { color: '#555', font: { size: 12 } }
    }
  }
};
</script>
