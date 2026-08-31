<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import {
    BadgeCheck,
    Bell,
    ChevronsUpDown,
    LogOut,
    Settings,
} from '@lucide/vue';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useAuth } from '@/composables/auth';

const { isMobile } = useSidebar();
const auth = useAuth();
const router = useRouter();

const user = computed(() => auth.user.value);

const initials = computed(() => {
    const name = user.value?.name ?? '';

    return (
        name
            .split(' ')
            .map((part) => part[0])
            .slice(0, 2)
            .join('')
            .toUpperCase() || 'U'
    );
});

async function handleLogout(): Promise<void> {
    await auth.logout();
}
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        size="lg"
                        class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                    >
                        <Avatar class="h-8 w-8 rounded-lg">
                            <AvatarImage
                                :src="user?.avatar ?? ''"
                                :alt="user?.name"
                            />
                            <AvatarFallback class="rounded-lg">
                                {{ initials }}
                            </AvatarFallback>
                        </Avatar>
                        <div
                            class="grid flex-1 text-left text-sm leading-tight"
                        >
                            <span class="truncate font-medium">{{
                                user?.name
                            }}</span>
                            <span class="truncate text-xs capitalize">{{
                                user?.role
                            }}</span>
                        </div>
                        <ChevronsUpDown class="ml-auto size-4" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                    :side="isMobile ? 'bottom' : 'right'"
                    align="end"
                    :side-offset="4"
                >
                    <DropdownMenuLabel class="p-0 font-normal">
                        <div
                            class="flex items-center gap-2 px-1 py-1.5 text-left text-sm"
                        >
                            <Avatar class="h-8 w-8 rounded-lg">
                                <AvatarImage
                                    :src="user?.avatar ?? ''"
                                    :alt="user?.name"
                                />
                                <AvatarFallback class="rounded-lg">
                                    {{ initials }}
                                </AvatarFallback>
                            </Avatar>
                            <div
                                class="grid flex-1 text-left text-sm leading-tight"
                            >
                                <span class="truncate font-semibold">{{
                                    user?.name
                                }}</span>
                                <span class="truncate text-xs">{{
                                    user?.email
                                }}</span>
                            </div>
                        </div>
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuGroup>
                        <DropdownMenuItem disabled>
                            <BadgeCheck />
                            Perfil
                        </DropdownMenuItem>
                        <DropdownMenuItem disabled>
                            <Bell />
                            Notificaciones
                        </DropdownMenuItem>
                        <DropdownMenuItem disabled>
                            <Settings />
                            Configuración
                        </DropdownMenuItem>
                    </DropdownMenuGroup>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="handleLogout">
                        <LogOut />
                        Cerrar sesión
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
