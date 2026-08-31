<script setup lang="ts">
import {
    BookOpen,
    GraduationCap,
    HeartHandshake,
    LogOut,
    Menu,
    Palette,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useAuth } from '@/composables/auth';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';

const router = useRouter();
const auth = useAuth(router);

interface NavItem {
    label: string;
    to: string;
    icon: typeof Users;
}

const navItems = computed<NavItem[]>(() => {
    switch (auth.user.value?.role) {
        case 'admin':
            return [
                { label: 'Admisiones', to: '/admin', icon: Users },
                { label: 'Docentes', to: '/teacher', icon: GraduationCap },
            ];
        case 'teacher':
            return [{ label: 'Mis cursos', to: '/teacher', icon: BookOpen }];
        case 'representative':
            return [
                { label: 'Mis hijos', to: '/parent', icon: HeartHandshake },
            ];
        default:
            return [];
    }
});

const initials = computed(() => {
    return (
        auth.user.value?.name
            .split(' ')
            .map((part) => part[0])
            .slice(0, 2)
            .join('')
            .toUpperCase() ?? 'U'
    );
});
</script>

<template>
    <div class="bg-background flex min-h-screen flex-col">
        <header class="bg-card sticky top-0 z-30 border-b shadow-sm">
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 lg:px-6"
            >
                <div class="flex items-center gap-3">
                    <Sheet>
                        <SheetTrigger as-child class="lg:hidden">
                            <Button
                                variant="ghost"
                                size="icon"
                                aria-label="Abrir menú"
                            >
                                <Menu class="size-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-64">
                            <SheetHeader>
                                <SheetTitle>Menú</SheetTitle>
                            </SheetHeader>
                            <nav class="mt-6 flex flex-col gap-2">
                                <RouterLink
                                    v-for="item in navItems"
                                    :key="item.to"
                                    :to="item.to"
                                    class="hover:bg-muted flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                                    active-class="bg-primary/10 text-primary"
                                >
                                    <component :is="item.icon" class="size-5" />
                                    {{ item.label }}
                                </RouterLink>
                            </nav>
                        </SheetContent>
                    </Sheet>

                    <RouterLink to="/" class="flex items-center gap-2">
                        <div
                            class="bg-primary text-primary-foreground flex size-9 items-center justify-center rounded-xl"
                        >
                            <Palette class="size-5" />
                        </div>
                        <span
                            class="hidden text-lg font-bold tracking-tight sm:inline"
                        >
                            Crayones y Colores
                        </span>
                    </RouterLink>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm leading-none font-medium">
                            {{ auth.user.value?.name }}
                        </p>
                        <p class="text-muted-foreground text-xs capitalize">
                            {{ auth.user.value?.role }}
                        </p>
                    </div>
                    <Avatar class="size-9">
                        <AvatarFallback>{{ initials }}</AvatarFallback>
                    </Avatar>
                    <Button
                        variant="ghost"
                        size="icon"
                        aria-label="Cerrar sesión"
                        @click="auth.logout"
                    >
                        <LogOut class="size-5" />
                    </Button>
                </div>
            </div>
        </header>

        <div
            class="mx-auto flex w-full max-w-7xl flex-1 gap-6 px-4 py-6 lg:px-6"
        >
            <aside class="hidden w-64 shrink-0 lg:block">
                <nav class="sticky top-24 flex flex-col gap-1">
                    <RouterLink
                        v-for="item in navItems"
                        :key="item.to"
                        :to="item.to"
                        class="hover:bg-muted flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                        active-class="bg-primary/10 text-primary"
                    >
                        <component :is="item.icon" class="size-5" />
                        {{ item.label }}
                    </RouterLink>
                </nav>
            </aside>

            <main class="min-w-0 flex-1">
                <slot />
            </main>
        </div>
    </div>
</template>
