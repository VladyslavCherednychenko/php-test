import './assets/main.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { i18n } from '@/i18n';
import App from '@/App.vue';
import router from '@/router';
import { useAuthStore } from '@/stores/auth';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);

const auth = useAuthStore(pinia);

try {
  await auth.init();
} catch {
  console.warn('User not authenticated on boot.');
}

app.use(router);
app.use(i18n);

await router.isReady();

app.mount('#app');
