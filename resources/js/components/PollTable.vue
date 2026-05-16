<script setup>
  import { usePollStore } from '@/stores/usePollStore';
  import { useFetchApi } from '../composables/useFetchApi';

  const { polls, deletePoll } = usePollStore();

  async function delPoll(id) {
    console.log('delete Poll ID:', id);
    await deletePoll(id);
  }

  const { fetchApi } = useFetchApi();
  function fecthDelete(id) {
    // if (!confirm('Êtes-vous sûr de vouloir supprimer ce sondage ?')) return;

    fetchApi({url: `/polls/${id}`, method: 'DELETE'})
      .then(() => {
        alert('Sondage supprimé avec succès.');
        delPoll(id);
      })
      .catch((error) => {
        console.error('Erreur lors de la suppression du sondage:', error);
        alert('Une erreur est survenue lors de la suppression du sondage.');
      });
  }
</script>

<template>
  <p v-if="polls.length === 0">Aucun sondage.</p>

  <table v-else class="w-full border-collapse text-left">
    <thead>
      <tr>
        <th class="border px-3 py-2">Actions</th>
        <th class="border px-3 py-2">ID</th>
        <th class="border px-3 py-2">Titre</th>
        <th class="border px-3 py-2">Question</th>
        <th class="border px-3 py-2">Brouillon</th>
        <th class="border px-3 py-2">Debut</th>
        <th class="border px-3 py-2">Fin</th>
        <th class="border px-3 py-2">Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="poll in polls" :key="poll.id">
        <td class="border px-3 py-2"><button @click="delPoll(poll.id)">Supp.</button></td>
        <td class="border px-3 py-2">{{ poll.id }}</td>
        <td class="border px-3 py-2">{{ poll.title || '-' }}</td>
        <td class="border px-3 py-2">{{ poll.question }}</td>
        <td class="border px-3 py-2">{{ poll.is_draft ? 'Oui' : 'Non' }}</td>
        <td class="border px-3 py-2">{{ poll.started_at || '-' }}</td>
        <td class="border px-3 py-2">{{ poll.ends_at || '-' }}</td>
        <td class="border px-3 py-2"><button @click="fetchDdelete(poll.id)">Supprimer</button></td>
      </tr>
    </tbody>
  </table>
</template>

<style scoped>
  button {
    background-color: #e3342f;
    color: white;
    padding: 0.25rem 0.5rem;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
  }
</style>
