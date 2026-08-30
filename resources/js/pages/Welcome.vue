<script setup lang="ts">
import {
    BookOpen,
    CalendarHeart,
    GraduationCap,
    HeartHandshake,
    Palette,
    School,
    ShieldCheck,
    Users,
} from '@lucide/vue';
import { useRouter } from 'vue-router';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const router = useRouter();
</script>

<template>
    <div class="bg-background min-h-screen">
        <header class="bg-card border-b">
            <div
                class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4"
            >
                <div class="flex items-center gap-2">
                    <div
                        class="bg-primary text-primary-foreground flex size-10 items-center justify-center rounded-xl shadow-sm"
                    >
                        <Palette class="size-6" />
                    </div>
                    <span
                        class="text-foreground text-xl font-bold tracking-tight"
                    >
                        Crayones y Colores
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="ghost" @click="router.push('/login')">
                        Acceder
                    </Button>
                    <Button @click="router.push('/apply')">
                        Solicitar admisión
                    </Button>
                </div>
            </div>
        </header>

        <main>
            <section class="relative mx-auto max-w-6xl px-6 py-16 lg:py-24">
                <div
                    class="absolute inset-x-0 top-0 -z-10 flex justify-center gap-4 opacity-20"
                >
                    <div class="bg-secondary size-32 rounded-full blur-3xl" />
                    <div class="bg-accent size-32 rounded-full blur-3xl" />
                    <div class="bg-primary size-32 rounded-full blur-3xl" />
                </div>

                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div class="space-y-6">
                        <h1
                            class="text-4xl font-extrabold tracking-tight lg:text-6xl"
                        >
                            Donde cada niño pinta su
                            <span class="text-primary">futuro</span>
                        </h1>
                        <p class="text-muted-foreground text-lg">
                            En Crayones y Colores formamos a los más pequeños
                            con amor, creatividad y valores. Nuestro sistema
                            conecta a padres, docentes y administración para una
                            educación integral.
                        </p>
                        <div class="flex flex-wrap gap-3">
                            <Button size="lg" @click="router.push('/apply')">
                                Inicia una admisión
                            </Button>
                            <Button
                                size="lg"
                                variant="outline"
                                @click="router.push('/login')"
                            >
                                Portal institucional
                            </Button>
                        </div>
                    </div>
                    <div
                        class="from-primary/10 via-secondary/10 to-accent/10 rounded-3xl bg-gradient-to-br p-8"
                    >
                        <div class="grid gap-4 sm:grid-cols-2">
                            <Card class="border-none shadow-sm">
                                <CardHeader class="pb-2">
                                    <Users class="text-primary size-6" />
                                    <CardTitle class="text-base"
                                        >Tres ambientes</CardTitle
                                    >
                                </CardHeader>
                                <CardContent>
                                    <CardDescription
                                        >Padres, docentes y administración
                                        conectados.</CardDescription
                                    >
                                </CardContent>
                            </Card>
                            <Card class="border-none shadow-sm">
                                <CardHeader class="pb-2">
                                    <CalendarHeart class="text-accent size-6" />
                                    <CardTitle class="text-base"
                                        >Pagos digitales</CardTitle
                                    >
                                </CardHeader>
                                <CardContent>
                                    <CardDescription
                                        >Pensiones y matrículas con
                                        recordatorios
                                        automáticos.</CardDescription
                                    >
                                </CardContent>
                            </Card>
                            <Card class="border-none shadow-sm">
                                <CardHeader class="pb-2">
                                    <BookOpen class="text-secondary size-6" />
                                    <CardTitle class="text-base"
                                        >Avance académico</CardTitle
                                    >
                                </CardHeader>
                                <CardContent>
                                    <CardDescription
                                        >Informes alineados al Ministerio de
                                        Educación.</CardDescription
                                    >
                                </CardContent>
                            </Card>
                            <Card class="border-none shadow-sm">
                                <CardHeader class="pb-2">
                                    <ShieldCheck class="text-primary size-6" />
                                    <CardTitle class="text-base"
                                        >Aforo controlado</CardTitle
                                    >
                                </CardHeader>
                                <CardContent>
                                    <CardDescription
                                        >Cumplimiento de normativas por nivel y
                                        paralelo.</CardDescription
                                    >
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-card border-t">
                <div class="mx-auto max-w-6xl px-6 py-16">
                    <div class="mb-10 text-center">
                        <h2 class="text-3xl font-bold tracking-tight">
                            Niveles educativos
                        </h2>
                        <p class="text-muted-foreground mt-2">
                            Acompañamos a cada niño desde sus primeros pasos
                            hasta primero de básica.
                        </p>
                    </div>
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <Card
                            class="overflow-hidden border-none text-center shadow-sm"
                        >
                            <div class="bg-primary h-2" />
                            <CardHeader>
                                <HeartHandshake
                                    class="text-primary mx-auto size-8"
                                />
                                <CardTitle>Maternal</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <CardDescription
                                    >Maternal 1 y 2, acompañamiento afectivo y
                                    sensorial.</CardDescription
                                >
                            </CardContent>
                        </Card>
                        <Card
                            class="overflow-hidden border-none text-center shadow-sm"
                        >
                            <div class="bg-secondary h-2" />
                            <CardHeader>
                                <School class="text-secondary mx-auto size-8" />
                                <CardTitle>Inicial</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <CardDescription
                                    >Inicial 1 y 2, desarrollo del pensamiento
                                    lógico.</CardDescription
                                >
                            </CardContent>
                        </Card>
                        <Card
                            class="overflow-hidden border-none text-center shadow-sm"
                        >
                            <div class="bg-accent h-2" />
                            <CardHeader>
                                <GraduationCap
                                    class="text-accent mx-auto size-8"
                                />
                                <CardTitle>Primero de Básica</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <CardDescription
                                    >Transición escolar con aforo reducido y
                                    personalizado.</CardDescription
                                >
                            </CardContent>
                        </Card>
                        <Card
                            class="overflow-hidden border-none text-center shadow-sm"
                        >
                            <div class="bg-primary h-2" />
                            <CardHeader>
                                <Users class="text-primary mx-auto size-8" />
                                <CardTitle>Padres</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <CardDescription
                                    >Portal propio para matrículas, pagos e
                                    informes.</CardDescription
                                >
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-4xl px-6 py-16 text-center">
                <h2 class="text-3xl font-bold tracking-tight">
                    ¿Listos para pintar juntos un camino de aprendizaje?
                </h2>
                <p class="text-muted-foreground mt-3">
                    Completa el formulario de admisión y recibe una respuesta en
                    pocos días.
                </p>
                <Button class="mt-6" size="lg" @click="router.push('/apply')">
                    Solicitar admisión ahora
                </Button>
            </section>
        </main>

        <footer
            class="bg-card text-muted-foreground border-t py-6 text-center text-sm"
        >
            © {{ new Date().getFullYear() }} Crayones y Colores. Todos los
            derechos reservados.
        </footer>
    </div>
</template>
