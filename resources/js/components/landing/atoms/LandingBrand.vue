<script setup lang="ts">
import { Palette } from '@lucide/vue';

withDefaults(
    defineProps<{
        compactOnMobile?: boolean;
    }>(),
    {
        compactOnMobile: false,
    },
);
</script>

<template>
    <span class="flex items-center gap-3">
        <span
            class="bg-primary text-primary-foreground shadow-primary/20 flex size-11 items-center justify-center rounded-2xl shadow-lg"
            aria-hidden="true"
        >
            <Palette class="size-6" />
        </span>
        <span
            class="flex flex-col leading-none"
            :class="compactOnMobile ? 'hidden sm:flex' : ''"
        >
            <span class="font-heading text-lg font-bold tracking-tight">
                Crayones y Colores
            </span>
            <span class="text-muted-foreground mt-1 text-xs font-medium">
                Centro de educación infantil
            </span>
        </span>
    </span>
</template>
