<script setup>
import { ref, computed, onMounted } from 'vue';
import { useFetchApi } from './composables/useFetchApi';
import { usePolling } from './composables/usePolling';
import VoteForm from './components/VoteForm.vue';
import ResultsChart from './components/ResultsChart.vue';

const { fetchApi } = useFetchApi();

// Extraire le token depuis l'URL (/vote/XXXXXX)
const token = window.location.pathname.split('/').pop();

const poll    = ref(null);
const results = ref([]);
const errorMsg = ref('');
const hasVoted = ref(false);
const loading  = ref(true);

const isExpired = computed(() => {
  if (!poll.value?.ends_at) return false;
  return new Date(poll.value.ends_at) < new Date();
});

const canVote = computed(() =>
  poll.value && !poll.value.is_draft && !isExpired.value && !hasVoted.value
);

async function loadResults() {
  try {
    const data = await fetchApi({ url: `/polls/${poll.value.id}/results` });
    results.value = data;
  } catch {
    // Résultats privés ou erreur → on affiche rien
  }
}

onMounted(async () => {
  try {
    poll.value = await fetchApi({ url: `/polls/${token}` });
    await loadResults();
    // Polling toutes les 5 secondes
    const { start } = usePolling(loadResults, 5000);
    start();
  } catch {
    errorMsg.value = 'Sondage introuvable ou lien invalide.';
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="p-4 max-w-xl mx-auto">

    <div v-if="loading" class="text-gray-500">Chargement...</div>

    <div v-else-if="errorMsg" class="text-red-600 p-4 bg-red-50 rounded">
      {{ errorMsg }}
    </div>

    <div v-else>
      <h1 class="text-2xl font-bold mb-1">{{ poll.question }}</h1>
      <p v-if="poll.title" class="text-gray-500 mb-4">{{ poll.title }}</p>

      <!-- Sondage terminé -->
      <div v-if="isExpired" class="bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4">
        ⛔ Ce sondage est terminé — les votes ne sont plus acceptés.
      </div>

      <!-- Sondage brouillon -->
      <div v-else-if="poll.is_draft" class="bg-yellow-50 border border-yellow-200 text-yellow-700 p-3 rounded mb-4">
        🟡 Ce sondage n'est pas encore lancé.
      </div>

      <!-- Formulaire de vote -->
      <VoteForm
        v-if="canVote"
        :poll="poll"
        @voted="hasVoted = true; loadResults()"
      />

      <!-- Confirmation après vote -->
      <div v-else-if="hasVoted"
        class="bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-4">
        ✅ Votre vote a été enregistré !
      </div>

      <!-- Graphique des résultats -->
      <ResultsChart v-if="results.length > 0" :results="results" />

      <!-- Résultats privés -->
      <p v-else-if="!poll.is_draft" class="text-gray-400 text-sm mt-4">
        Les résultats sont privés.
      </p>
    </div>
  </div>
</template>
