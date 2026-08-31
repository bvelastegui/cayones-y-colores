<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';
import {
    Baby,
    Banknote,
    BookOpen,
    GraduationCap,
    HeartHandshake,
    Layers,
    LayoutDashboard,
    Receipt,
    School,
    Settings,
    UserCheck,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';

import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import TeamSwitcher from '@/components/TeamSwitcher.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarRail,
} from '@/components/ui/sidebar';
import { useAuth } from '@/composables/auth';

interface NavItem {
    title: string;
    url: string;
    icon: LucideIcon;
    isActive?: boolean;
}

const props = defineProps<{
    collapsible?: 'icon' | 'none';
}>();

const auth = useAuth();

const mainItems = computed<NavItem[]>(() => {
    switch (auth.user.value?.role) {
        case 'admin':
            return [
                {
                    title: 'Panel',
                    url: '/admin',
                    icon: LayoutDashboard,
                    isActive: true,
                },
                {
                    title: 'Admisiones',
                    url: '/admin/admissions',
                    icon: Settings,
                },
            ];
        case 'teacher':
            return [
                {
                    title: 'Mis cursos',
                    url: '/teacher',
                    icon: BookOpen,
                },
            ];
        case 'representative':
            return [
                {
                    title: 'Mis hijos',
                    url: '/parent',
                    icon: HeartHandshake,
                },
            ];
        default:
            return [];
    }
});

const adminItems = computed<NavItem[]>(() => {
    if (auth.user.value?.role !== 'admin') {
        return [];
    }

    return [
        { title: 'Usuarios', url: '/admin/users', icon: Users },
        { title: 'Niveles', url: '/admin/levels', icon: Layers },
        { title: 'Cursos', url: '/admin/courses', icon: School },
        {
            title: 'Representantes',
            url: '/admin/representatives',
            icon: HeartHandshake,
        },
        { title: 'Docentes', url: '/admin/teachers', icon: GraduationCap },
        { title: 'Estudiantes', url: '/admin/students', icon: Baby },
        {
            title: 'Asignaciones',
            url: '/admin/course-teachers',
            icon: UserCheck,
        },
        { title: 'Pensiones', url: '/admin/tuitions', icon: Receipt },
        { title: 'Pagos', url: '/admin/payments', icon: Banknote },
    ];
});
</script>

<template>
    <Sidebar :collapsible="collapsible ?? 'icon'">
        <SidebarHeader>
            <TeamSwitcher />
        </SidebarHeader>
        <SidebarContent>
            <NavMain :items="mainItems" />
            <NavMain
                v-if="adminItems.length > 0"
                label="Administración"
                :items="adminItems"
            />
        </SidebarContent>
        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
        <SidebarRail />
    </Sidebar>
</template>
