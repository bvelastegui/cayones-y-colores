<script setup lang="ts">
import { Palette } from '@lucide/vue';
import { RouterLink } from 'vue-router';
import { Button } from '@/components/ui/button';
</script>

<template>
    <div class="bg-background flex min-h-screen flex-col">
        <header class="bg-card border-b">
            <div
                class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4"
            >
                <RouterLink to="/" class="flex items-center gap-2">
                    <div
                        class="bg-primary text-primary-foreground flex size-10 items-center justify-center rounded-xl shadow-sm"
                    >
                        <Palette class="size-6" />
                    </div>
                    <span
                        class="text-foreground text-xl font-bold tracking-tight"
                    >
                        Crayones y Colores
                    </span>
                </RouterLink>
                <nav class="flex items-center gap-3">
                    <Button variant="ghost" as-child>
                        <RouterLink to="/">Inicio</RouterLink>
                    </Button>
                    <Button variant="ghost" as-child>
                        <RouterLink to="/apply">Admisión</RouterLink>
                    </Button>
                    <Button as-child>
                        <RouterLink to="/login">Acceder</RouterLink>
                    </Button>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer
            class="bg-card text-muted-foreground border-t py-6 text-center text-sm"
        >
            © {{ new Date().getFullYear() }} Crayones y Colores. Todos los
            derechos reservados.
        </footer>
    </div>
</template>
