import { createApp } from 'vue';
import App from './App.vue';
import router from './routes';

const app = createApp(App);

// Add router and route globally
app.config.globalProperties.$router = router;
app.config.globalProperties.$route = router.currentRoute; // reactive

app.use(router);
app.mount('#frontend');
