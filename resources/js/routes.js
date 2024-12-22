import { createRouter, createWebHistory } from 'vue-router'
// import Home from '../views/Home.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'home',
            component: import('./views/Home.vue')
        },
        {
            path: '/about',
            name: 'about',
            component: () => import('./views/About.vue')
        },
        {
            path: '/contact',
            name: 'contact',
            component: () => import('./views/Contact.vue')
        },
        {
            path: '/login',
            name: 'login',
            component: () => import('./views/Login.vue')
        },
        {
            path: '/signup',
            name: 'signup',
            component: () => import('./views/Signup.vue')
        },
        {
            path: '/projects',
            name: 'projects',
            component: () => import('./views/Projects.vue')
        },
        {
            path: '/projects/:id',
            name: 'project-detail',
            component: () => import('./views/ProjectDetail.vue')
        },
        {
            path: '/add-project',
            name: 'add-project',
            component: () => import('./views/AddProject.vue')
        },
        {
            path: '/profile/:username',
            name: 'profile',
            component: () => import('./views/Profile.vue')
        }
    ]
})

export default router