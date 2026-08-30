<script setup lang="ts">
import {
    BookOpen,
    CalendarHeart,
    GraduationCap,
    HeartHandshake,
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
                    <School class="text-primary size-8" />
                    <span class="text-xl font-bold tracking-tight"
                        >CENESTUR</span
                    >
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
            <section class="mx-auto max-w-6xl px-6 py-16 lg:py-24">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div class="space-y-6">
                        <h1
                            class="text-4xl font-extrabold tracking-tight lg:text-6xl"
                        >
                            Educación con propósito desde los primeros años
                        </h1>
                        <p class="text-muted-foreground text-lg">
                            Formamos a los más pequeños con amor, disciplina y
                            valores. Nuestro sistema integral conecta a padres,
                            docentes y administración en un solo lugar.
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
                    <div class="bg-primary/5 rounded-2xl p-8">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <Card>
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
                            <Card>
                                <CardHeader class="pb-2">
                                    <CalendarHeart
                                        class="text-primary size-6"
                                    />
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
                            <Card>
                                <CardHeader class="pb-2">
                                    <BookOpen class="text-primary size-6" />
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
                            <Card>
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

            <section class="bg-muted/30 border-t">
                <div class="mx-auto max-w-6xl px-6 py-16">
                    <div class="mb-10 text-center">
                        <h2 class="text-3xl font-bold tracking-tight">
                            Niveles educativos
                        </h2>
                        <p class="text-muted-foreground mt-2">
                            Proyecto educativo desde los primeros pasos hasta
                            primero de básica.
                        </p>
                    </div>
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <Card class="text-center">
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
                        <Card class="text-center">
                            <CardHeader>
                                <School class="text-primary mx-auto size-8" />
                                <CardTitle>Inicial</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <CardDescription
                                    >Inicial 1 y 2, desarrollo del pensamiento
                                    lógico.</CardDescription
                                >
                            </CardContent>
                        </Card>
                        <Card class="text-center">
                            <CardHeader>
                                <GraduationCap
                                    class="text-primary mx-auto size-8"
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
                        <Card class="text-center">
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
                    ¿Listo para formar parte de nuestra comunidad?
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
            © {{ new Date().getFullYear() }} CENESTUR. Todos los derechos
            reservados.
        </footer>
    </div>
</template>
