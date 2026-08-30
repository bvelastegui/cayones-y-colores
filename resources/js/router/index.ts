import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: () => import('@/pages/Auth/Login.vue'),
        },
        {
            path: '/',
            name: 'home',
            component: () => import('@/pages/Welcome.vue'),
        },
    ],
});

export default router;
