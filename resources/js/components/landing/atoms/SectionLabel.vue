<script setup lang="ts">
import { Sparkles } from '@lucide/vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        label: string;
        align?: 'start' | 'center';
        inverse?: boolean;
    }>(),
    {
        align: 'start',
        inverse: false,
    },
);
</script>

<template>
    <div
        :class="
            cn(
                'flex',
                props.align === 'center' ? 'justify-center' : 'justify-start',
            )
        "
    >
        <span
            :class="
                cn(
                    'inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-bold tracking-wider uppercase',
                    props.inverse
                        ? 'bg-primary-foreground/10 text-primary-foreground'
                        : 'bg-sun/20 text-sun-foreground',
                )
            "
        >
            <Sparkles class="size-4" aria-hidden="true" />
            {{ label }}
        </span>
    </div>
</template>
