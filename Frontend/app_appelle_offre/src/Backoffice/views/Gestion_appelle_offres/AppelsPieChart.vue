<template>
  <div class="card shadow-sm p-3 mb-4">
    <h5 class="mb-3">📊 Répartition des Appels par Domaine</h5>
    <div style="height: 320px;">
      <Pie :data="chartData" :options="chartOptions" />
    </div>
  </div>
</template>

<script setup>
import { Pie } from 'vue-chartjs';
import { computed } from 'vue';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, ArcElement);

const props = defineProps({
  data: {
    type: Array,
    required: true
  }
});

const chartData = computed(() => ({
  labels: props.data.map(d => d.label),
  datasets: [
    {
      label: 'Appels par domaine',
      data: props.data.map(d => d.value),
      backgroundColor: [
        '#f97316',
        '#60a5fa',
        '#34d399',
        '#f472b6',
        '#818cf8',
        '#facc15',
        '#ef4444',
      ],
      borderColor: '#fff',
      borderWidth: 2
    }
  ]
}));

const chartOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: 'right'
    }
  }
};
</script>
