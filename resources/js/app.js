import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import CustomInput from './components/elements/CustomInput.vue';
import loader from './components/elements/loader.vue';
import router from './routes';
import mitt from 'mitt';
const emitter = mitt();
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import globalFunctions from './functions.js';
import Swal from 'sweetalert2'

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2500,
})
const app = createApp(App);

window.Swal = Swal
window.toast = Toast
app.config.globalProperties.emitter = emitter
app.component('CustomInput', CustomInput);
app.component('loader', loader);
app.use(globalFunctions);
app.use(pinia);
app.use(router);
app.mount('#frontend');
