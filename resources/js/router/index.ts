import { useAuth } from '@/composables/auth';
import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'home',
            component: () => import('@/pages/Welcome.vue'),
            meta: { public: true },
        },
        {
            path: '/apply',
            name: 'admissions.apply',
            component: () => import('@/pages/Admissions/Apply.vue'),
            meta: { public: true },
        },
        {
            path: '/login',
            name: 'login',
            component: () => import('@/pages/Auth/Login.vue'),
            meta: { public: true, guest: true },
        },
        {
            path: '/admin',
            name: 'admin.dashboard',
            component: () => import('@/pages/Admin/Admissions.vue'),
            meta: { roles: ['admin'] },
        },
        {
            path: '/teacher',
            name: 'teacher.dashboard',
            component: () => import('@/pages/Teacher/Index.vue'),
            meta: { roles: ['teacher'] },
        },
        {
            path: '/parent',
            name: 'parent.dashboard',
            component: () => import('@/pages/Parent/Index.vue'),
            meta: { roles: ['representative'] },
        },
        {
            path: '/parent/enroll/:studentId',
            name: 'parent.enroll',
            component: () => import('@/pages/Parent/Enroll.vue'),
            meta: { roles: ['representative'] },
        },
    ],
});

const dashboardByRole: Record<string, string> = {
    admin: '/admin',
    teacher: '/teacher',
    representative: '/parent',
};

router.beforeEach(async (to, from, next) => {
    const auth = useAuth(router);

    if (to.meta.public) {
        if (to.meta.guest && auth.isAuthenticated.value) {
            const dashboard =
                dashboardByRole[auth.user.value?.role ?? ''] ?? '/';

            return next(dashboard);
        }

        return next();
    }

    if (!auth.token.value) {
        return next('/login');
    }

    if (!auth.user.value) {
        await auth.fetchUser();
    }

    if (!auth.user.value) {
        return next('/login');
    }

    const allowedRoles = (to.meta.roles as string[] | undefined) ?? [];

    if (
        allowedRoles.length > 0 &&
        !allowedRoles.includes(auth.user.value.role)
    ) {
        const dashboard = dashboardByRole[auth.user.value.role] ?? '/';

        return next(dashboard);
    }

    next();
});

export default router;
