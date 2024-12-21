import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import ExampleComponent from './components/ExampleComponent.vue';
import App from './components/App.vue';

const routes = [
    { path: '/', component: ExampleComponent }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

const app = createApp(App);
app.use(router);
app.mount('#frontend');
