<script setup>
import { ref, onMounted, watch } from 'vue';
import { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend);

const props = defineProps({ results: Array });
const canvas = ref(null);
let chart = null;

function draw() {
  if (!canvas.value) return;
  if (chart) chart.destroy();

  chart = new Chart(canvas.value, {
    type: 'bar',
    data: {
      labels: props.results.map(r => r.label),
      datasets: [{
        label: 'Votes',
        data: props.results.map(r => r.votes_count),
        backgroundColor: 'rgba(59, 130, 246, 0.7)',
        borderColor: 'rgba(59, 130, 246, 1)',
        borderWidth: 1,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } },
    }
  });
}

onMounted(draw);
watch(() => props.results, draw, { deep: true });
</script>

<template>
  <div class="mt-6">
    <h2 class="font-semibold mb-2">Résultats</h2>
    <canvas ref="canvas"></canvas>
  </div>
</template>
