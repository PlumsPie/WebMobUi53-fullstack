<script setup>
import { ref } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';

const props = defineProps({ poll: Object });
const { fetchApi } = useFetchApi();

const newLabel = ref('');
const errorMsg = ref('');

// On travaille directement sur le tableau d'options de la prop
// (la prop poll est un objet réactif du store, donc la mutation se propage)
const options = props.poll.options ?? [];

async function addOption() {
  errorMsg.value = '';
  if (!newLabel.value.trim()) return;

  try {
    const option = await fetchApi({
      url: `/polls/${props.poll.id}/options`,
      data: { label: newLabel.value.trim() },
    });
    options.push(option);
    newLabel.value = '';
  } catch {
    errorMsg.value = 'Erreur lors de l\'ajout.';
  }
}

async function removeOption(optionId) {
  try {
    await fetchApi({
      url: `/polls/${props.poll.id}/options/${optionId}`,
      method: 'DELETE',
    });
    const i = options.findIndex(o => o.id === optionId);
    if (i !== -1) options.splice(i, 1);
  } catch {
    errorMsg.value = 'Erreur lors de la suppression.';
  }
}
</script>

<template>
  <div class="border-t pt-4">
    <h3 class="font-semibold mb-2 text-sm">Options de réponse</h3>

    <ul class="mb-3 space-y-1">
      <li v-for="opt in options" :key="opt.id"
        class="flex justify-between items-center text-sm bg-gray-50 px-2 py-1 rounded">
        <span>{{ opt.label }}</span>
        <button @click="removeOption(opt.id)" class="text-red-500 hover:text-red-700">✕</button>
      </li>
      <li v-if="options.length === 0" class="text-gray-400 text-sm">Aucune option.</li>
    </ul>

    <div class="flex gap-2">
      <input v-model="newLabel" @keyup.enter="addOption" type="text"
        class="border rounded px-2 py-1 flex-1 text-sm"
        placeholder="Nouvelle option..." />
      <button @click="addOption" class="bg-blue-600 text-white px-3 py-1 rounded text-sm">+</button>
    </div>

    <p v-if="errorMsg" class="text-red-600 text-xs mt-1">{{ errorMsg }}</p>
  </div>
</template>
