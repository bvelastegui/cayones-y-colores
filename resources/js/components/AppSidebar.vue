<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';
import { BookOpen, HeartHandshake, LayoutDashboard, Users } from '@lucide/vue';
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
}

const props = defineProps<{
    collapsible?: 'icon' | 'none';
}>();

const auth = useAuth();

const navItems = computed<NavItem[]>(() => {
    switch (auth.user.value?.role) {
        case 'admin':
            return [
                {
                    title: 'Admisiones',
                    url: '/admin',
                    icon: LayoutDashboard,
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
                {
                    title: 'Comunidad',
                    url: '#',
                    icon: Users,
                },
            ];
        default:
            return [];
    }
});
</script>

<template>
    <Sidebar :collapsible="collapsible ?? 'icon'">
        <SidebarHeader>
            <TeamSwitcher />
        </SidebarHeader>
        <SidebarContent>
            <NavMain :items="navItems" />
        </SidebarContent>
        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
        <SidebarRail />
    </Sidebar>
</template>
