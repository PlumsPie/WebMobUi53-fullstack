<script setup>
import { ref } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';
import { usePollStore } from '@/stores/usePollStore';

const emit = defineEmits(['created']);
const { fetchApi } = useFetchApi();
const { addPoll } = usePollStore();

const form = ref({
  question:       '',
  title:          '',
  is_draft:       true,
  allow_multiple_choices: false,
  results_public: true,
  ends_at:        '',
});

const loading = ref(false);
const errorMsg = ref('');

async function submit() {
  errorMsg.value = '';
  if (!form.value.question.trim()) {
    errorMsg.value = 'La question est obligatoire.';
    return;
  }

  loading.value = true;
  try {
    const payload = { ...form.value, ends_at: form.value.ends_at || null };
    const newPoll = await fetchApi({ url: '/polls', data: payload });
    addPoll(newPoll);
    emit('created', newPoll);
    // Réinitialiser
    form.value = { question: '', title: '', is_draft: true, allow_multiple: false, results_public: true, ends_at: '' };
  } catch (err) {
    errorMsg.value = err?.data?.message ?? 'Une erreur est survenue.';
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="border rounded p-4 mb-6 bg-gray-50">
    <h2 class="font-semibold mb-4">Nouveau sondage</h2>

    <div class="mb-3">
      <label class="block text-sm font-medium mb-1">Question *</label>
      <input v-model="form.question" type="text" class="w-full border rounded px-3 py-2"
        placeholder="Quelle est votre question ?" />
    </div>

    <div class="mb-3">
      <label class="block text-sm font-medium mb-1">Titre (optionnel)</label>
      <input v-model="form.title" type="text" class="w-full border rounded px-3 py-2"
        placeholder="Titre court..." />
    </div>

    <div class="flex flex-wrap gap-4 mb-3">
      <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" v-model="form.is_draft" />
        Créer en brouillon
      </label>
      <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" v-model="form.allow_multiple" />
        Choix multiple
      </label>
      <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" v-model="form.results_public" />
        Résultats publics
      </label>
    </div>

    <div class="mb-3">
      <label class="block text-sm font-medium mb-1">Date de fin (optionnel)</label>
      <input type="datetime-local" v-model="form.ends_at" class="border rounded px-3 py-2" />
    </div>

    <p v-if="errorMsg" class="text-red-600 text-sm mb-2">{{ errorMsg }}</p>

    <div class="flex gap-2">
      <button @click="submit" :disabled="loading"
        class="bg-green-600 text-white px-4 py-2 rounded disabled:opacity-50">
        {{ loading ? 'Création...' : 'Créer' }}
      </button>
    </div>
  </div>
</template>
