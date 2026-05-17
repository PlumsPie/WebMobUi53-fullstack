<script setup>
import { ref, computed } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';

const props  = defineProps({ poll: Object });
const emit   = defineEmits(['voted']);
const { fetchApi } = useFetchApi();

// Pour choix multiple : tableau d'IDs
const selectedIds  = ref([]);
// Pour choix unique : un seul ID
const singleId     = ref(null);

const errorMsg = ref('');
const loading  = ref(false);

async function submit() {
  errorMsg.value = '';

  const optionIds = props.poll.allow_multiple
    ? selectedIds.value
    : (singleId.value ? [singleId.value] : []);

  if (optionIds.length === 0) {
    errorMsg.value = 'Veuillez sélectionner une option.';
    return;
  }

  loading.value = true;
  try {
    await fetchApi({
      url: `/polls/${props.poll.id}/votes`,
      data: { option_ids: optionIds },
    });
    emit('voted');
  } catch (err) {
    if (err.status === 409) errorMsg.value = 'Vous avez déjà voté à ce sondage.';
    else if (err.status === 403) errorMsg.value = err.data?.message ?? 'Vote non autorisé.';
    else errorMsg.value = 'Erreur lors du vote.';
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="mb-6">
    <h2 class="font-semibold mb-3">Votre choix</h2>

    <!-- Choix multiple -->
    <div v-if="poll.allow_multiple" class="space-y-2">
      <label v-for="opt in poll.options" :key="opt.id"
        class="flex items-center gap-3 cursor-pointer">
        <input type="checkbox" :value="opt.id" v-model="selectedIds"
          class="w-4 h-4" />
        <span>{{ opt.label }}</span>
      </label>
    </div>

    <!-- Choix unique -->
    <div v-else class="space-y-2">
      <label v-for="opt in poll.options" :key="opt.id"
        class="flex items-center gap-3 cursor-pointer">
        <input type="radio" :value="opt.id" v-model="singleId"
          name="vote" class="w-4 h-4" />
        <span>{{ opt.label }}</span>
      </label>
    </div>

    <p v-if="errorMsg" class="text-red-600 text-sm mt-2">{{ errorMsg }}</p>

    <button @click="submit" :disabled="loading"
      class="mt-4 bg-blue-600 text-white px-6 py-2 rounded disabled:opacity-50">
      {{ loading ? 'Envoi...' : 'Voter' }}
    </button>
  </div>
</template>
