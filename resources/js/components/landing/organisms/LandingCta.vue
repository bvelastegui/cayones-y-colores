<script setup lang="ts">
import { Heart, Star } from '@lucide/vue';
import ActionLink from '@/components/landing/atoms/ActionLink.vue';
</script>

<template>
  <section aria-labelledby="cta-title">
    <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:px-10 lg:py-24">
      <div
        class="bg-sun text-sun-foreground relative overflow-hidden rounded-[2.5rem] px-6 py-14 text-center sm:px-12 lg:py-20"
      >
        <Star
          class="absolute top-8 left-8 size-10 rotate-12 fill-current opacity-25"
          aria-hidden="true"
        />
        <Heart
          class="absolute right-8 bottom-8 size-12 -rotate-12 fill-current opacity-20"
          aria-hidden="true"
        />
        <div
          class="relative mx-auto flex max-w-3xl flex-col items-center gap-6"
        >
          <span class="text-sm font-bold tracking-widest uppercase">
            El próximo gran comienzo
          </span>
          <h2
            id="cta-title"
            class="font-heading text-3xl font-bold tracking-tight sm:text-5xl"
          >
            Ven a descubrir dónde crecerá feliz
          </h2>
          <p class="text-sun-foreground/75 max-w-2xl text-lg leading-8">
            Completa la solicitud de admisión y demos juntos el primer paso para
            ser parte de Crayones y Colores.
          </p>
          <ActionLink
            to="/apply"
            show-arrow
          >
            Solicitar admisión
          </ActionLink>
        </div>
      </div>
    </div>
  </section>
</template>
