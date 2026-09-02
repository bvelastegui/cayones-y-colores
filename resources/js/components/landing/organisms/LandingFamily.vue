<script setup lang="ts">
import { BellRing, Check, FileText, MessageCircleHeart } from '@lucide/vue';
import SectionLabel from '@/components/landing/atoms/SectionLabel.vue';
import PromiseItem from '@/components/landing/molecules/PromiseItem.vue';

const promises = [
    {
        icon: MessageCircleHeart,
        title: 'Comunicación que acompaña',
        description:
            'Mantén cerca la información importante del día a día escolar.',
    },
    {
        icon: FileText,
        title: 'Avances siempre disponibles',
        description:
            'Consulta informes y sigue cada paso del proceso de aprendizaje.',
    },
    {
        icon: BellRing,
        title: 'Gestiones más simples',
        description:
            'Recibe recordatorios y revisa matrículas y pensiones desde un solo lugar.',
    },
];
</script>

<template>
    <section
        id="familias"
        class="bg-primary text-primary-foreground overflow-hidden"
        aria-labelledby="family-title"
    >
        <div
            class="mx-auto grid max-w-7xl items-center gap-12 px-5 py-16 sm:px-8 lg:grid-cols-2 lg:px-10 lg:py-24"
        >
            <div class="flex flex-col gap-7">
                <SectionLabel label="Familias conectadas" inverse />
                <div class="flex flex-col gap-4">
                    <h2
                        id="family-title"
                        class="font-heading text-3xl font-bold tracking-tight sm:text-5xl"
                    >
                        La tranquilidad de estar presente, incluso a la
                        distancia
                    </h2>
                    <p
                        class="text-primary-foreground/80 max-w-xl text-lg leading-8"
                    >
                        Nuestro portal acerca a las familias al proceso
                        educativo y reúne la información que necesitan de forma
                        clara y segura.
                    </p>
                </div>

                <ul class="flex flex-col gap-5">
                    <PromiseItem
                        v-for="promise in promises"
                        :key="promise.title"
                        v-bind="promise"
                        inverse
                    />
                </ul>
            </div>

            <div class="relative mx-auto w-full max-w-lg">
                <div
                    class="bg-card text-card-foreground relative rounded-[2.5rem] p-5 shadow-2xl sm:p-7"
                >
                    <div
                        class="border-border flex items-center justify-between border-b pb-5"
                    >
                        <div class="flex flex-col gap-1">
                            <span
                                class="text-muted-foreground text-xs font-bold tracking-wider uppercase"
                            >
                                Portal de familias
                            </span>
                            <strong class="font-heading text-xl"
                                >Hola, familia</strong
                            >
                        </div>
                        <span
                            class="bg-leaf/15 text-leaf-foreground flex size-11 items-center justify-center rounded-full"
                        >
                            <Check class="size-5" aria-hidden="true" />
                        </span>
                    </div>

                    <div class="grid gap-4 py-5 sm:grid-cols-2">
                        <article
                            class="bg-muted flex flex-col gap-4 rounded-3xl p-5 sm:col-span-2"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <div class="flex flex-col gap-1">
                                    <span
                                        class="text-muted-foreground text-xs font-semibold"
                                        >Próximo paso</span
                                    >
                                    <strong>Revisar informe académico</strong>
                                </div>
                                <FileText
                                    class="text-primary size-7"
                                    aria-hidden="true"
                                />
                            </div>
                            <div
                                class="bg-border h-2 overflow-hidden rounded-full"
                            >
                                <div
                                    class="bg-primary h-full w-3/4 rounded-full"
                                />
                            </div>
                        </article>
                        <article
                            class="bg-sun/20 flex flex-col gap-2 rounded-3xl p-5"
                        >
                            <BellRing
                                class="text-sun-foreground size-6"
                                aria-hidden="true"
                            />
                            <strong>Recordatorios</strong>
                            <span class="text-muted-foreground text-sm"
                                >Todo al día y en orden</span
                            >
                        </article>
                        <article
                            class="bg-coral/15 flex flex-col gap-2 rounded-3xl p-5"
                        >
                            <MessageCircleHeart
                                class="text-coral-foreground size-6"
                                aria-hidden="true"
                            />
                            <strong>Comunicación</strong>
                            <span class="text-muted-foreground text-sm"
                                >Cercana y oportuna</span
                            >
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
