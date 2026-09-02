<script setup lang="ts">
import {
    BookOpen,
    Circle,
    Heart,
    Palette,
    ShieldCheck,
    Star,
} from '@lucide/vue';
import ActionLink from '@/components/landing/atoms/ActionLink.vue';
import SectionLabel from '@/components/landing/atoms/SectionLabel.vue';
</script>

<template>
    <section class="relative overflow-hidden" aria-labelledby="hero-title">
        <div
            class="bg-primary/5 absolute top-24 -left-24 size-72 rounded-full blur-3xl"
            aria-hidden="true"
        />
        <div
            class="bg-sun/20 absolute -right-24 bottom-0 size-80 rounded-full blur-3xl"
            aria-hidden="true"
        />

        <div
            class="mx-auto grid max-w-7xl items-center gap-12 px-5 py-14 sm:px-8 sm:py-20 lg:grid-cols-[1.05fr_0.95fr] lg:px-10 lg:py-24"
        >
            <div class="flex flex-col gap-7">
                <SectionLabel label="Admisiones abiertas 2026" />

                <div class="flex max-w-3xl flex-col gap-5">
                    <h1
                        id="hero-title"
                        class="font-heading text-4xl leading-tight font-bold tracking-tight sm:text-5xl lg:text-7xl"
                    >
                        Un lugar pequeño para
                        <span class="text-primary relative inline-block">
                            grandes comienzos
                            <svg
                                class="text-sun absolute -bottom-2 left-0 h-3 w-full"
                                viewBox="0 0 260 12"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M3 8.5C67 2 171 2 257 7"
                                    stroke="currentColor"
                                    stroke-width="6"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </span>
                    </h1>
                    <p
                        class="text-muted-foreground max-w-2xl text-lg leading-8"
                    >
                        Acompañamos a cada niño con experiencias que despiertan
                        su curiosidad, fortalecen su autonomía y hacen del
                        aprendizaje una aventura feliz.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <ActionLink to="/apply" show-arrow>
                        Iniciar admisión
                    </ActionLink>
                    <ActionLink to="#programas" variant="outline">
                        Conocer los niveles
                    </ActionLink>
                </div>

                <ul
                    class="flex flex-wrap gap-x-6 gap-y-3 text-sm font-semibold"
                >
                    <li class="flex items-center gap-2">
                        <ShieldCheck
                            class="text-leaf-foreground size-5"
                            aria-hidden="true"
                        />
                        Entorno seguro
                    </li>
                    <li class="flex items-center gap-2">
                        <Heart
                            class="text-coral-foreground size-5"
                            aria-hidden="true"
                        />
                        Acompañamiento cercano
                    </li>
                </ul>
            </div>

            <div class="relative mx-auto w-full max-w-xl" aria-hidden="true">
                <div
                    class="bg-sun/25 absolute -top-5 -right-4 size-24 rounded-full"
                />
                <div
                    class="bg-coral/20 absolute -bottom-5 -left-5 size-28 rounded-full"
                />
                <div
                    class="border-border/60 bg-card shadow-primary/10 relative min-h-112 overflow-hidden rounded-4xl border p-6 shadow-2xl sm:p-9"
                >
                    <div
                        class="bg-primary/10 absolute inset-x-8 top-8 h-56 rounded-4xl"
                    />
                    <div
                        class="bg-primary absolute top-20 left-1/2 flex size-40 -translate-x-1/2 items-center justify-center rounded-full"
                    >
                        <Palette class="text-primary-foreground size-20" />
                    </div>
                    <Star
                        class="text-sun absolute top-12 left-14 size-10 fill-current"
                    />
                    <Circle
                        class="text-coral absolute top-20 right-12 size-8 fill-current"
                    />
                    <BookOpen
                        class="text-leaf-foreground absolute top-52 right-16 size-11 rotate-12"
                    />

                    <div
                        class="absolute right-6 bottom-8 left-6 grid grid-cols-2 gap-3 sm:right-9 sm:left-9"
                    >
                        <div
                            class="bg-primary text-primary-foreground rounded-3xl p-5"
                        >
                            <span class="text-2xl font-bold">Crear</span>
                            <p class="text-primary-foreground/80 mt-1 text-sm">
                                jugando y explorando
                            </p>
                        </div>
                        <div class="bg-sun text-sun-foreground rounded-3xl p-5">
                            <span class="text-2xl font-bold">Crecer</span>
                            <p class="text-sun-foreground/75 mt-1 text-sm">
                                con confianza y amor
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
