<script setup>
import { computed } from 'vue';

const props = defineProps({ token: String });
defineEmits(['close']);

const link = computed(() => `${window.location.origin}/vote/${props.token}`);

function copy() {
  navigator.clipboard.writeText(link.value)
    .then(() => alert('Lien copié !'));
}
</script>

<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
      <div class="flex justify-between items-center mb-4">
        <h2 class="font-bold">Lien de partage</h2>
        <button @click="$emit('close')" class="text-gray-500 text-xl">✕</button>
      </div>

      <div class="flex gap-2">
        <input :value="link" readonly
          class="border rounded px-3 py-2 flex-1 text-sm bg-gray-50" />
        <button @click="copy"
          class="bg-blue-600 text-white px-3 py-2 rounded text-sm">
          Copier
        </button>
      </div>

      <a :href="link" target="_blank" class="text-blue-600 text-sm mt-2 block hover:underline">
        Ouvrir le lien ↗
      </a>
    </div>
  </div>
</template>
