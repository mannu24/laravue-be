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
import { useGlobalDataStore } from './stores/globalData';
import { reinitializeEcho } from './echo';

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
const globalDataStore = useGlobalDataStore();
window.authStore = authStore;

const hydrateGlobalData = async (force = false) => {
  if (!authStore.isAuthenticated) {
    globalDataStore.clear();
    return;
  }
  try {
    await globalDataStore.fetchGlobalData({ force });
  } catch (error) {
    console.error('[GlobalData] Failed to load:', error);
  }
};

hydrateGlobalData();

authStore.$subscribe((_mutation, state) => {
  if (state.isAuthenticated) {
    // Reinitialize Echo with new auth token when user logs in
    reinitializeEcho();
    hydrateGlobalData(true);
  } else {
    // Disconnect Echo when user logs out
    if (window.Echo) {
      try {
        window.Echo.disconnect();
      } catch (error) {
        console.warn('[Echo] Error disconnecting on logout:', error);
      }
    }
    globalDataStore.clear();
  }
});

app.mount('#frontend');
