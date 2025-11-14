import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import CustomInput from './components/elements/CustomInput.vue';
import loader from './components/elements/loader.vue';
import router from './routes';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import globalFunctions from './functions.js';
import { useAuthStore } from './stores/auth';

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

const app = createApp(App);

app.component('CustomInput', CustomInput);
app.component('loader', loader);
app.use(globalFunctions);
app.use(pinia);
app.use(router);

// Make auth store available globally for Echo (after Pinia is initialized)
const authStore = useAuthStore();
window.authStore = authStore;

app.mount('#frontend');
