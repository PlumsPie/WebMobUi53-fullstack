import './bootstrap';
import { createApp } from 'vue';
import AppPollVote from './AppPollVote.vue';

const el = document.getElementById('app-vote');
createApp(AppPollVote).mount(el);
