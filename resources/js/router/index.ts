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
        {
            path: '/apply',
            name: 'admissions.apply',
            component: () => import('@/pages/Admissions/Apply.vue'),
        },
        {
            path: '/admin',
            name: 'admin.dashboard',
            component: () => import('@/pages/Admin/Admissions.vue'),
        },
        {
            path: '/teacher',
            name: 'teacher.dashboard',
            component: () => import('@/pages/Teacher/Index.vue'),
        },
        {
            path: '/parent',
            name: 'parent.dashboard',
            component: () => import('@/pages/Parent/Index.vue'),
        },
    ],
});

export default router;
