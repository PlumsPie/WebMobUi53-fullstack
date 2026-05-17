<script setup>
import { ref, watch } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';
import { usePollStore } from '@/stores/usePollStore';
import PollOptions from './PollOptions.vue';

const props = defineProps({ poll: Object });
const emit = defineEmits(['close']);

const { fetchApi } = useFetchApi();
const { updatePoll } = usePollStore();

// Copie locale pour ne pas muter la prop directement
const form = ref({ ...props.poll, ends_at: props.poll.ends_at?.slice(0, 16) ?? '' });

const loading = ref(false);
const errorMsg = ref('');

async function save() {
  errorMsg.value = '';
  loading.value = true;
  try {
    const updated = await fetchApi({
      url: `/polls/${props.poll.id}`,
      method: 'PATCH',
      data: { ...form.value, ends_at: form.value.ends_at || null },
    });
    updatePoll(updated);
    emit('close');
  } catch (err) {
    errorMsg.value = err?.data?.message ?? 'Erreur lors de la sauvegarde.';
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <!-- Overlay -->
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center mb-4">
        <h2 class="font-bold text-lg">Modifier le sondage</h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-800 text-xl">✕</button>
      </div>

      <!-- Paramètres -->
      <div class="mb-3">
        <label class="block text-sm font-medium mb-1">Question</label>
        <input v-model="form.question" type="text" class="w-full border rounded px-3 py-2" />
      </div>

      <div class="mb-3">
        <label class="block text-sm font-medium mb-1">Titre</label>
        <input v-model="form.title" type="text" class="w-full border rounded px-3 py-2" />
      </div>

      <div class="flex flex-wrap gap-4 mb-3">
        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" v-model="form.is_draft" />
          Brouillon
        </label>
        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" v-model="form.allow_multiple_choices" />
          Choix multiple
        </label>
        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" v-model="form.results_public" />
          Résultats publics
        </label>
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Date de fin</label>
        <input type="datetime-local" v-model="form.ends_at" class="border rounded px-3 py-2" />
      </div>

      <!-- Options de réponse -->
      <PollOptions :poll="props.poll" />

      <p v-if="errorMsg" class="text-red-600 text-sm my-2">{{ errorMsg }}</p>

      <div class="flex gap-2 mt-4">
        <button @click="save" :disabled="loading"
          class="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50">
          {{ loading ? 'Sauvegarde...' : 'Enregistrer' }}
        </button>
        <button @click="$emit('close')" class="border px-4 py-2 rounded">Annuler</button>
      </div>
    </div>
  </div>
</template>
