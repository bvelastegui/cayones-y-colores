<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    icon: Component;
    title: string;
    description: string;
    inverse?: boolean;
}>();
</script>

<template>
    <li class="flex items-start gap-4">
        <span
            class="flex size-11 shrink-0 items-center justify-center rounded-2xl"
            :class="
                inverse
                    ? 'bg-primary-foreground/10 text-primary-foreground'
                    : 'bg-leaf/15 text-leaf-foreground'
            "
            aria-hidden="true"
        >
            <component :is="icon" class="size-5" />
        </span>
        <span class="flex flex-col gap-1">
            <strong class="font-heading text-base">{{ title }}</strong>
            <span
                class="leading-6"
                :class="
                    inverse
                        ? 'text-primary-foreground/75'
                        : 'text-muted-foreground'
                "
            >
                {{ description }}
            </span>
        </span>
    </li>
</template>
