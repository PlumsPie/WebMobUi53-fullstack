import './bootstrap';
import { createApp } from 'vue';
import { setDefaultHeaders } from './composables/useFetchApi';
import AppPollVote from './AppPollVote.vue';

const el = document.getElementById('app-vote');
const apiToken = el.dataset.token;
if (apiToken) {
  setDefaultHeaders({ 'Authorization': `Bearer ${apiToken}` });
}

createApp(AppPollVote).mount(el);
