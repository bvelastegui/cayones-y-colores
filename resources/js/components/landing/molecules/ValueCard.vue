<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
  icon: Component;
  title: string;
  description: string;
  tone: 'primary' | 'coral' | 'leaf';
}>();

const toneClasses = {
  primary: 'bg-primary/10 text-primary',
  coral: 'bg-coral/15 text-coral-foreground',
  leaf: 'bg-leaf/15 text-leaf-foreground',
};
</script>

<template>
  <article
    class="border-border/70 bg-card flex h-full flex-col gap-5 rounded-3xl border p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg motion-reduce:transform-none"
  >
    <span
      class="flex size-12 items-center justify-center rounded-2xl"
      :class="toneClasses[tone]"
      aria-hidden="true"
    >
      <component
        :is="icon"
        class="size-6"
      />
    </span>
    <div class="flex flex-col gap-2">
      <h3 class="font-heading text-xl font-bold">{{ title }}</h3>
      <p class="text-muted-foreground leading-7">{{ description }}</p>
    </div>
  </article>
</template>
