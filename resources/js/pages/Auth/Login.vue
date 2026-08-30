<script setup lang="ts">
import { Palette } from '@lucide/vue';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
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

        localStorage.setItem('token', data.token);

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
    <div
        class="bg-background flex min-h-screen items-center justify-center p-4"
    >
        <Card class="w-full max-w-sm">
            <CardHeader class="text-center">
                <div
                    class="bg-primary text-primary-foreground mx-auto mb-3 flex size-12 items-center justify-center rounded-2xl"
                >
                    <Palette class="size-7" />
                </div>
                <CardTitle>Crayones y Colores</CardTitle>
                <CardDescription
                    >Ingresa tus credenciales para continuar</CardDescription
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
                            <FieldLabel for="password">Contraseña</FieldLabel>
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
                    <Button type="submit" class="w-full" :disabled="loading">
                        <Spinner v-if="loading" data-icon="inline-start" />
                        {{ loading ? 'Ingresando...' : 'Ingresar' }}
                    </Button>
                </CardFooter>
            </form>
        </Card>
    </div>
</template>
