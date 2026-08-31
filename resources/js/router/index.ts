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
            component: () => import('@/pages/Admin/Dashboard.vue'),
            meta: { roles: ['admin'] },
        },
        {
            path: '/admin/admissions',
            name: 'admin.admissions',
            component: () => import('@/pages/Admin/Admissions.vue'),
            meta: { roles: ['admin'] },
        },
        {
            path: '/admin/users',
            name: 'admin.users',
            component: () => import('@/pages/Admin/Users.vue'),
            meta: { roles: ['admin'] },
        },
        {
            path: '/admin/levels',
            name: 'admin.levels',
            component: () => import('@/pages/Admin/Levels.vue'),
            meta: { roles: ['admin'] },
        },
        {
            path: '/admin/courses',
            name: 'admin.courses',
            component: () => import('@/pages/Admin/Courses.vue'),
            meta: { roles: ['admin'] },
        },
        {
            path: '/admin/representatives',
            name: 'admin.representatives',
            component: () => import('@/pages/Admin/Representatives.vue'),
            meta: { roles: ['admin'] },
        },
        {
            path: '/admin/teachers',
            name: 'admin.teachers',
            component: () => import('@/pages/Admin/Teachers.vue'),
            meta: { roles: ['admin'] },
        },
        {
            path: '/admin/students',
            name: 'admin.students',
            component: () => import('@/pages/Admin/Students.vue'),
            meta: { roles: ['admin'] },
        },
        {
            path: '/admin/course-teachers',
            name: 'admin.course-teachers',
            component: () => import('@/pages/Admin/CourseTeachers.vue'),
            meta: { roles: ['admin'] },
        },
        {
            path: '/admin/tuitions',
            name: 'admin.tuitions',
            component: () => import('@/pages/Admin/Tuitions.vue'),
            meta: { roles: ['admin'] },
        },
        {
            path: '/admin/payments',
            name: 'admin.payments',
            component: () => import('@/pages/Admin/Payments.vue'),
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

router.beforeEach(async (to) => {
    const auth = useAuth(router);

    if (to.meta.public) {
        if (to.meta.guest && auth.isAuthenticated.value) {
            return dashboardByRole[auth.user.value?.role ?? ''] ?? '/';
        }

        return true;
    }

    if (!auth.token.value) {
        return '/login';
    }

    if (!auth.user.value) {
        await auth.fetchUser();
    }

    if (!auth.user.value) {
        return '/login';
    }

    const allowedRoles = (to.meta.roles as string[] | undefined) ?? [];

    if (
        allowedRoles.length > 0 &&
        !allowedRoles.includes(auth.user.value.role)
    ) {
        return dashboardByRole[auth.user.value.role] ?? '/';
    }

    return true;
});

export default router;
