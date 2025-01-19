import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './stores/auth.js';

const router = createRouter({
    history: createWebHistory(),
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) return savedPosition;
        else return { top: 0, behavior: 'smooth' };
    },
    routes: [
        {
            path: '/',
            name: 'home',
            component: () => import('./views/Home.vue'),
            meta: { auth: false, both: true },
        },
        {
            path: '/about',
            name: 'about',
            component: () => import('./views/About.vue'),
            meta: { auth: false, both: true },
        },
        {
            path: '/contact',
            name: 'contact',
            component: () => import('./views/Contact.vue'),
            meta: { auth: false, both: true },
        },
        {
            path: '/login',
            name: 'login',
            component: () => import('./views/Login.vue'),
            meta: { auth: false, both: false },
        },
        {
            path: '/signup',
            name: 'signup',
            component: () => import('./views/Signup.vue'),
            meta: { auth: false, both: false },
        },
        {
            path: '/projects',
            name: 'projects',
            component: () => import('./views/Projects.vue'),
            meta: { auth: false, both: true },
        },
        {
            path: '/projects/:id',
            name: 'project-detail',
            component: () => import('./views/ProjectDetail.vue'),
            meta: { auth: false, both: true },
        },
        {
            path: '/add-project',
            name: 'add-project',
            component: () => import('./views/AddProject.vue'),
            meta: { auth: false, both: true },
        },
        {
            path: '/profile/:username',
            name: 'profile',
            component: () => import('./views/Profile.vue'),
            meta: { auth: true, both: false },
        },
    ],
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    const isLogged = () => authStore?.isAuthenticated;

    if (to.matched.some(record => record.meta.auth)) {
        if (isLogged()) next();
        else {
            if (to.name !== 'login') next({ name: 'login' });
            else next();
        }
    } else if (to.matched.some(record => record.meta.both)) {
        next();
    } else {
        if (isLogged()) next('/');
        else {
            if (to.name !== 'login') next({ name: 'login' });
            else next();
        }
    }
});

export default router;
