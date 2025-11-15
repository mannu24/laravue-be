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
            path: '/feed',
            name: 'feed',
            component: () => import('./views/Feed.vue'),
            meta: { auth: false, both: true },
        },
        {
            path: '/qna',
            name: 'qna',
            component: () => import('./views/Qna.vue'),
            meta: { auth: false, both: true },
        },
        {
            path: '/qna/ask',
            name: 'qna-new',
            component: () => import('./views/QuestionAsk.vue'),
            meta: { auth: true },
        },
        {
            path: '/qna/ask/:slug',
            name: 'qna-edit',
            component: () => import('./views/EditAskedQuestion.vue'),
            meta: { auth: true },
        },
        {
            path: '/qna/:slug',
            name: 'qna-detail',
            component: () => import('./views/QuestionDetail.vue'),
            meta: { auth: false, both: true },
        },
        {
            path: '/projects/:slug',
            name: 'project-detail',
            component: () => import('./views/ProjectDetail.vue'),
            meta: { auth: false, both: true },
        },
        {
            path: '/projects/:slug/edit',
            name: 'edit-project',
            component: () => import('./views/EditProject.vue'),
            meta: { auth: true },
        },
        {
            path: '/add-project',
            name: 'add-project',
            component: () => import('./views/AddProject.vue'),
            meta: { auth: false, both: true },
        },
        {
            path: '/profile/@:username',
            name: 'profile',
            component: () => import('./views/Profile.vue'),
            meta: { auth: true, both: false },
        },
        {
            path: '/@:username',
            name: 'user-feed',
            component: () => import('./views/UserProfile.vue'),
            meta: {auth: false, both: true},
        },
        {
            path: '/@:username/post_:post_code',
            name: 'post-detail',
            component: () => import('./views/PostDetail.vue'),
            meta: { auth: false, both: true },
        },
        {
            path: '/search',
            name: 'search-results',
            component: () => import('./views/SearchResults.vue'),
            meta: { auth: false, both: true },
        },
        {
            path: '/notifications',
            name: 'notifications',
            component: () => import('./views/Notifications.vue'),
            meta: { auth: true },
        },
        {
            path: '/settings',
            name: 'settings',
            component: () => import('./views/Settings.vue'),
            meta: { auth: true },
        },
        {
            path: '/:pathMatch(.*)*',
            name: 'not-found',
            component: () => import('./views/ErrorPage.vue'),
            meta: { auth: false, both: true, },
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
