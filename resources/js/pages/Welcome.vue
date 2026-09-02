<script setup lang="ts">
import LandingCta from '@/components/landing/organisms/LandingCta.vue';
import LandingFamily from '@/components/landing/organisms/LandingFamily.vue';
import LandingHero from '@/components/landing/organisms/LandingHero.vue';
import LandingPrograms from '@/components/landing/organisms/LandingPrograms.vue';
import LandingValues from '@/components/landing/organisms/LandingValues.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
</script>

<template>
    <PublicLayout>
        <LandingHero />
        <LandingValues />
        <LandingPrograms />
        <LandingFamily />
        <LandingCta />
    </PublicLayout>
</template>
