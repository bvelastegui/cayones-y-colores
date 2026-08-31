<script setup lang="ts">
import { Palette, ShieldCheck, Sparkles, Users } from '@lucide/vue';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { useAuth } from '@/composables/auth';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Field,
    FieldError,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';

const router = useRouter();
const auth = useAuth(router);

const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);

async function submit(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch('/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                email: email.value,
                password: password.value,
                device_name: 'web',
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message ?? 'Error al iniciar sesión.');
        }

        auth.setSession(data.token, data.user);

        const routeByRole: Record<string, string> = {
            admin: '/admin',
            teacher: '/teacher',
            representative: '/parent',
        };

        await router.push(routeByRole[data.user.role] ?? '/');
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Error desconocido.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <PublicLayout>
        <div class="relative mx-auto max-w-6xl px-6 py-12 lg:py-16">
            <div
                class="absolute inset-x-0 top-0 -z-10 flex justify-center gap-4 opacity-15"
            >
                <div class="bg-secondary size-40 rounded-full blur-3xl" />
                <div class="bg-accent size-40 rounded-full blur-3xl" />
                <div class="bg-primary size-40 rounded-full blur-3xl" />
            </div>

            <div class="grid items-center gap-10 lg:grid-cols-2">
                <div class="space-y-6">
                    <div>
                        <h1
                            class="text-3xl font-extrabold tracking-tight lg:text-4xl"
                        >
                            Bienvenido de nuevo a
                            <span class="text-primary">Crayones y Colores</span>
                        </h1>
                        <p class="text-muted-foreground mt-3">
                            Accede al portal institucional para gestionar
                            admisiones, matrículas, pagos y el seguimiento
                            académico de tus hijos.
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <Card class="border-none shadow-sm">
                            <CardHeader class="pb-2">
                                <Users class="text-primary size-6" />
                                <CardTitle class="text-base"
                                    >Tres perfiles</CardTitle
                                >
                            </CardHeader>
                            <CardContent>
                                <CardDescription
                                    >Padres, docentes y administradores en un
                                    solo lugar.</CardDescription
                                >
                            </CardContent>
                        </Card>
                        <Card class="border-none shadow-sm">
                            <CardHeader class="pb-2">
                                <ShieldCheck class="text-accent size-6" />
                                <CardTitle class="text-base"
                                    >Acceso seguro</CardTitle
                                >
                            </CardHeader>
                            <CardContent>
                                <CardDescription
                                    >Tu información está protegida con
                                    autenticación por roles.</CardDescription
                                >
                            </CardContent>
                        </Card>
                        <Card class="border-none shadow-sm">
                            <CardHeader class="pb-2">
                                <Sparkles class="text-secondary size-6" />
                                <CardTitle class="text-base"
                                    >Experiencia integral</CardTitle
                                >
                            </CardHeader>
                            <CardContent>
                                <CardDescription
                                    >Desde la admisión hasta el informe de
                                    avance académico.</CardDescription
                                >
                            </CardContent>
                        </Card>
                        <Card class="border-none shadow-sm">
                            <CardHeader class="pb-2">
                                <Palette class="text-primary size-6" />
                                <CardTitle class="text-base"
                                    >Siempre conectado</CardTitle
                                >
                            </CardHeader>
                            <CardContent>
                                <CardDescription
                                    >Recibe notificaciones de pagos y
                                    recordatorios importantes.</CardDescription
                                >
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <Card class="mx-auto w-full max-w-sm shadow-lg">
                    <CardHeader class="text-center">
                        <div
                            class="bg-primary text-primary-foreground mx-auto mb-3 flex size-12 items-center justify-center rounded-2xl"
                        >
                            <Palette class="size-7" />
                        </div>
                        <CardTitle>Iniciar sesión</CardTitle>
                        <CardDescription
                            >Ingresa tus credenciales para
                            continuar</CardDescription
                        >
                    </CardHeader>
                    <form @submit.prevent="submit">
                        <CardContent>
                            <FieldGroup>
                                <Field>
                                    <FieldLabel for="email"
                                        >Correo electrónico</FieldLabel
                                    >
                                    <Input
                                        id="email"
                                        v-model="email"
                                        type="email"
                                        placeholder="admin@cenestur.test"
                                        required
                                    />
                                </Field>
                                <Field>
                                    <FieldLabel for="password"
                                        >Contraseña</FieldLabel
                                    >
                                    <Input
                                        id="password"
                                        v-model="password"
                                        type="password"
                                        placeholder="••••••••"
                                        required
                                    />
                                </Field>
                                <FieldError v-if="error" :errors="[error]" />
                            </FieldGroup>
                        </CardContent>
                        <CardFooter>
                            <Button
                                type="submit"
                                class="w-full"
                                :disabled="loading"
                            >
                                <Spinner
                                    v-if="loading"
                                    data-icon="inline-start"
                                />
                                {{ loading ? 'Ingresando...' : 'Ingresar' }}
                            </Button>
                        </CardFooter>
                    </form>
                </Card>
            </div>
        </div>
    </PublicLayout>
</template>
