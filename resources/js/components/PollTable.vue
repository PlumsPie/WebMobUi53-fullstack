<script setup>
import { ref } from 'vue';
import { usePollStore } from '@/stores/usePollStore';
import { useFetchApi } from '@/composables/useFetchApi';
import PollEditModal from './PollEditForm.vue';
import ShareLink from './PollShare.vue';

const { polls, deletePoll } = usePollStore();
const { fetchApi } = useFetchApi();

const editingPoll = ref(null);  // poll en cours d'édition
const sharingPoll = ref(null);  // poll dont on affiche le lien

async function handleDelete(id) {
  if (!confirm('Supprimer ce sondage ?')) return;
  try {
    await deletePoll(id);
  } catch {
    alert('Erreur lors de la suppression.');
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('fr-CH');
}
</script>

<template>
  <p v-if="polls.length === 0" class="text-gray-500 py-4">Aucun sondage.</p>

  <div v-else class="overflow-x-auto">
    <table class="w-full border-collapse text-left text-sm">
      <thead class="bg-gray-100">
        <tr>
          <th class="border px-3 py-2">Question</th>
          <th class="border px-3 py-2">Statut</th>
          <th class="border px-3 py-2">Début</th>
          <th class="border px-3 py-2">Fin</th>
          <th class="border px-3 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="poll in polls" :key="poll.id" class="hover:bg-gray-50">
          <td class="border px-3 py-2">{{ poll.question }}</td>
          <td class="border px-3 py-2">
            <span v-if="poll.is_draft" class="text-yellow-600">Brouillon</span>
            <span v-else-if="poll.ends_at && new Date(poll.ends_at) < new Date()" class="text-red-600">Terminé</span>
            <span v-else class="text-green-600">Actif</span>
          </td>
          <td class="border px-3 py-2">{{ formatDate(poll.started_at) }}</td>
          <td class="border px-3 py-2">{{ formatDate(poll.ends_at) }}</td>
          <td class="border px-3 py-2 flex gap-2 flex-wrap">
            <button @click="editingPoll = poll" class="btn-blue">Modifier</button>
            <button @click="sharingPoll = poll" class="btn-green">Partager</button>
            <button @click="handleDelete(poll.id)" class="btn-red">Supprimer</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Modal d'édition -->
  <PollEditModal
    v-if="editingPoll"
    :poll="editingPoll"
    @close="editingPoll = null"
  />

  <!-- Lien de partage -->
  <ShareLink
    v-if="sharingPoll"
    :token="sharingPoll.secret_token"
    @close="sharingPoll = null"
  />
</template>

<!-- <style scoped>
.btn-blue  { @apply bg-blue-600 text-white px-2 py-1 rounded text-xs; }
.btn-green { @apply bg-green-600 text-white px-2 py-1 rounded text-xs; }
.btn-red   { @apply bg-red-600 text-white px-2 py-1 rounded text-xs; }
</style> -->
