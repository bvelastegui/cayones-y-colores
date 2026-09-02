<script setup lang="ts">
import { Baby, Blocks, GraduationCap } from '@lucide/vue';
import SectionLabel from '@/components/landing/atoms/SectionLabel.vue';
import ProgramCard from '@/components/landing/molecules/ProgramCard.vue';

const programs = [
    {
        icon: Baby,
        stage: 'Primeros descubrimientos',
        title: 'Maternal 1 y 2',
        description:
            'Un comienzo afectivo y sensorial que fortalece el vínculo, el movimiento y la seguridad emocional.',
        highlights: ['Sensorial', 'Motricidad', 'Vínculos'],
        tone: 'coral' as const,
    },
    {
        icon: Blocks,
        stage: 'Curiosidad en acción',
        title: 'Inicial 1 y 2',
        description:
            'Experiencias de juego que impulsan el lenguaje, la convivencia y el pensamiento creativo.',
        highlights: ['Lenguaje', 'Arte', 'Convivencia'],
        tone: 'sun' as const,
    },
    {
        icon: GraduationCap,
        stage: 'Listos para avanzar',
        title: 'Primero de Básica',
        description:
            'Una transición escolar respetuosa con retos estimulantes, autonomía y acompañamiento personalizado.',
        highlights: ['Autonomía', 'Lectoescritura', 'Lógica'],
        tone: 'primary' as const,
    },
];
</script>

<template>
    <section id="programas" aria-labelledby="programs-title">
        <div
            class="mx-auto flex max-w-7xl flex-col gap-10 px-5 py-16 sm:px-8 lg:px-10 lg:py-24"
        >
            <div class="grid items-end gap-5 lg:grid-cols-2">
                <div class="flex flex-col gap-4">
                    <SectionLabel label="Niveles educativos" />
                    <h2
                        id="programs-title"
                        class="font-heading text-3xl font-bold tracking-tight sm:text-5xl"
                    >
                        Cada etapa merece su propia aventura
                    </h2>
                </div>
                <p
                    class="text-muted-foreground max-w-2xl text-lg leading-8 lg:justify-self-end"
                >
                    Ambientes y experiencias pensados para acompañar los ritmos,
                    intereses y desafíos de cada momento del desarrollo.
                </p>
            </div>

            <div class="grid gap-5 lg:grid-cols-3">
                <ProgramCard
                    v-for="program in programs"
                    :key="program.title"
                    v-bind="program"
                />
            </div>
        </div>
    </section>
</template>
