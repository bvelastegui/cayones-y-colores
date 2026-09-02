<script setup lang="ts">
import { ArrowRight } from '@lucide/vue';
import { computed } from 'vue';
import { RouterLink } from 'vue-router';

const props = withDefaults(
    defineProps<{
        to: string;
        variant?: 'primary' | 'outline' | 'ghost';
        showArrow?: boolean;
    }>(),
    {
        variant: 'primary',
        showArrow: false,
    },
);

const variantClasses = computed(() => {
    if (props.variant === 'outline') {
        return 'border border-border bg-card text-foreground shadow-sm hover:bg-muted';
    }

    if (props.variant === 'ghost') {
        return 'text-foreground hover:bg-muted';
    }

    return 'bg-primary text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90';
});
</script>

<template>
    <RouterLink
        :to="to"
        class="focus-visible:ring-ring inline-flex min-h-11 items-center justify-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold transition duration-200 hover:-translate-y-0.5 focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none motion-reduce:transform-none"
        :class="variantClasses"
    >
        <slot />
        <ArrowRight v-if="showArrow" class="size-4" aria-hidden="true" />
    </RouterLink>
</template>
