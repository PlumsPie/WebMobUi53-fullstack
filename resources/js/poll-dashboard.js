import './bootstrap';
import { createApp } from 'vue';
import App from './AppPollDashboard.vue';

// Initialise Sanctum avant de monter l'app
fetch('/sanctum/csrf-cookie', { credentials: 'include' }).then(() => {
  const el = document.getElementById('app');
  const props = JSON.parse(el.dataset.props ?? '{}');
  createApp(App, props).mount(el);
});
