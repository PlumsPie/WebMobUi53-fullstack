<script setup>
import { usePollStore } from '@/stores/usePollStore';
import PollTable from './components/PollTable.vue';
import PollCreateForm from './components/PollCreateForm.vue';
import { setDefaultHeaders } from '@/composables/useFetchApi';
import { ref } from 'vue';

const props = defineProps({
  polls:    { type: Array,  default: () => [] },
  loginUrl: { type: String, default: null },
  username: { type: String, default: null },
  apiToken: { type: String, default: null },
});
if (props.apiToken) {
  setDefaultHeaders({ 'Authorization': `Bearer ${props.apiToken}` });
}
const { setPolls } = usePollStore();
setPolls(props.polls);

const showCreateForm = ref(false);
</script>

<template>
  <div class="p-4 max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Mes sondages</h1>
      <button @click="showCreateForm = !showCreateForm" class="bg-blue-600 text-white px-4 py-2 rounded">
        + Nouveau sondage
      </button>
    </div>

    <PollCreateForm v-if="showCreateForm" @created="showCreateForm = false" />

    <PollTable />
  </div>
</template>
