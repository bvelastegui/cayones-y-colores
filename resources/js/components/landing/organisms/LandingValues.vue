<script setup lang="ts">
import { Blocks, HeartHandshake, Lightbulb } from '@lucide/vue';
import SectionLabel from '@/components/landing/atoms/SectionLabel.vue';
import ValueCard from '@/components/landing/molecules/ValueCard.vue';

const values = [
  {
    icon: Lightbulb,
    title: 'Aprender con curiosidad',
    description:
      'Propuestas lúdicas que invitan a preguntar, descubrir y construir conocimientos a su propio ritmo.',
    tone: 'primary' as const,
  },
  {
    icon: HeartHandshake,
    title: 'Crecer con afecto',
    description:
      'Vínculos respetuosos y una mirada atenta para que cada niño se sienta escuchado, seguro y valorado.',
    tone: 'coral' as const,
  },
  {
    icon: Blocks,
    title: 'Explorar haciendo',
    description:
      'Arte, movimiento, juego y naturaleza se integran para convertir cada jornada en una experiencia significativa.',
    tone: 'leaf' as const,
  },
];
</script>

<template>
  <section
    id="propuesta"
    class="bg-muted/50 border-border/60 border-y"
    aria-labelledby="values-title"
  >
    <div
      class="mx-auto flex max-w-7xl flex-col gap-10 px-5 py-16 sm:px-8 lg:px-10 lg:py-24"
    >
      <div class="mx-auto flex max-w-3xl flex-col gap-4 text-center">
        <SectionLabel
          label="Nuestra propuesta"
          align="center"
        />
        <h2
          id="values-title"
          class="font-heading text-3xl font-bold tracking-tight sm:text-5xl"
        >
          Educamos la mente, el corazón y las manos
        </h2>
        <p class="text-muted-foreground text-lg leading-8">
          Diseñamos experiencias para que aprender se sienta natural,
          emocionante y profundamente humano.
        </p>
      </div>

      <div class="grid gap-5 md:grid-cols-3">
        <ValueCard
          v-for="value in values"
          :key="value.title"
          v-bind="value"
        />
      </div>
    </div>
  </section>
</template>
