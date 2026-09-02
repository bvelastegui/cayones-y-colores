<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    icon: Component;
    stage: string;
    title: string;
    description: string;
    highlights: string[];
    tone: 'primary' | 'sun' | 'coral';
}>();

const toneClasses = {
    primary: 'bg-primary text-primary-foreground',
    sun: 'bg-sun text-sun-foreground',
    coral: 'bg-coral text-coral-foreground',
};
</script>

<template>
    <article
        class="border-border/70 bg-card overflow-hidden rounded-4xl border shadow-sm"
    >
        <div
            class="flex items-center justify-between p-6"
            :class="toneClasses[tone]"
        >
            <span class="text-xs font-bold tracking-widest uppercase">{{
                stage
            }}</span>
            <component :is="icon" class="size-8" aria-hidden="true" />
        </div>
        <div class="flex flex-col gap-5 p-6">
            <div class="flex flex-col gap-2">
                <h3 class="font-heading text-2xl font-bold">{{ title }}</h3>
                <p class="text-muted-foreground leading-7">{{ description }}</p>
            </div>
            <ul class="flex flex-wrap gap-2" aria-label="Áreas de aprendizaje">
                <li
                    v-for="highlight in highlights"
                    :key="highlight"
                    class="bg-muted rounded-full px-3 py-1.5 text-xs font-semibold"
                >
                    {{ highlight }}
                </li>
            </ul>
        </div>
    </article>
</template>
