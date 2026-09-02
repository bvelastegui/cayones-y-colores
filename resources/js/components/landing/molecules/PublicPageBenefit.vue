<script setup lang="ts">
import type { Component } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        icon: Component;
        title: string;
        description: string;
        inverse?: boolean;
    }>(),
    {
        inverse: false,
    },
);
</script>

<template>
    <li class="flex items-start gap-4">
        <span
            :class="
                cn(
                    'flex size-11 shrink-0 items-center justify-center rounded-2xl',
                    props.inverse
                        ? 'bg-primary-foreground/10 text-primary-foreground'
                        : 'bg-primary/10 text-primary',
                )
            "
            aria-hidden="true"
        >
            <component :is="icon" class="size-5" />
        </span>
        <span class="flex flex-col gap-1">
            <strong class="font-heading text-base">{{ title }}</strong>
            <span
                :class="
                    cn(
                        'text-sm leading-6',
                        props.inverse
                            ? 'text-primary-foreground/75'
                            : 'text-muted-foreground',
                    )
                "
            >
                {{ description }}
            </span>
        </span>
    </li>
</template>
