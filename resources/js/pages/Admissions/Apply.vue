<script setup lang="ts">
import {
    CheckCircle2,
    Clock,
    Mail,
    Palette,
    Phone,
    Smile,
    Users,
} from '@lucide/vue';
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';

interface Level {
    id: number;
    name: string;
}

const router = useRouter();

const levels = ref<Level[]>([]);
const levelId = ref('');
const firstName = ref('');
const lastName = ref('');
const birthDate = ref('');
const representativeNames = ref('');
const email = ref('');
const phone = ref('');
const error = ref('');
const loading = ref(false);
const submitted = ref(false);

async function fetchLevels(): Promise<void> {
    try {
        const response = await fetch('/api/levels', {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error('Error al cargar los niveles.');
        }

        const data = await response.json();
        levels.value = data.data ?? [];
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Error desconocido.';
    }
}

async function submit(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch('/api/admissions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                level_id: Number(levelId.value),
                applicant_first_name: firstName.value,
                applicant_last_name: lastName.value,
                applicant_birth_date: birthDate.value,
                representative_names: representativeNames.value,
                contact_email: email.value,
                contact_phone: phone.value,
                application_date: new Date().toISOString().split('T')[0],
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message ?? 'Error al enviar la admisión.');
        }

        submitted.value = true;
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Error desconocido.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    void fetchLevels();
});
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

            <div class="grid gap-10 lg:grid-cols-2 lg:items-start">
                <div class="space-y-6">
                    <div>
                        <h1
                            class="text-3xl font-extrabold tracking-tight lg:text-4xl"
                        >
                            Da el primer paso hacia
                            <span class="text-primary">Crayones y Colores</span>
                        </h1>
                        <p class="text-muted-foreground mt-3">
                            Completa la solicitud de admisión y nuestro equipo
                            se pondrá en contacto contigo para continuar el
                            proceso de inscripción.
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <Card class="border-none shadow-sm">
                            <CardHeader class="pb-2">
                                <Clock class="text-primary size-6" />
                                <CardTitle class="text-base"
                                    >Proceso ágil</CardTitle
                                >
                            </CardHeader>
                            <CardContent>
                                <CardDescription
                                    >Respuesta en pocos días
                                    hábiles.</CardDescription
                                >
                            </CardContent>
                        </Card>
                        <Card class="border-none shadow-sm">
                            <CardHeader class="pb-2">
                                <Users class="text-accent size-6" />
                                <CardTitle class="text-base"
                                    >Acompañamiento</CardTitle
                                >
                            </CardHeader>
                            <CardContent>
                                <CardDescription
                                    >Te guiamos en cada etapa de la
                                    inscripción.</CardDescription
                                >
                            </CardContent>
                        </Card>
                        <Card class="border-none shadow-sm">
                            <CardHeader class="pb-2">
                                <Smile class="text-secondary size-6" />
                                <CardTitle class="text-base"
                                    >Ambiente cálido</CardTitle
                                >
                            </CardHeader>
                            <CardContent>
                                <CardDescription
                                    >Instalaciones pensadas para los
                                    pequeños.</CardDescription
                                >
                            </CardContent>
                        </Card>
                        <Card class="border-none shadow-sm">
                            <CardHeader class="pb-2">
                                <CheckCircle2 class="text-primary size-6" />
                                <CardTitle class="text-base"
                                    >Información clara</CardTitle
                                >
                            </CardHeader>
                            <CardContent>
                                <CardDescription
                                    >Conoce cupos, niveles y
                                    requisitos.</CardDescription
                                >
                            </CardContent>
                        </Card>
                    </div>

                    <Card
                        class="from-primary/10 to-accent/10 border-none bg-gradient-to-br shadow-sm"
                    >
                        <CardHeader>
                            <CardTitle class="text-base"
                                >¿Tienes dudas?</CardTitle
                            >
                        </CardHeader>
                        <CardContent class="space-y-2 text-sm">
                            <div
                                class="text-muted-foreground flex items-center gap-2"
                            >
                                <Phone class="size-4" />
                                <span>+593 99 123 4567</span>
                            </div>
                            <div
                                class="text-muted-foreground flex items-center gap-2"
                            >
                                <Mail class="size-4" />
                                <span>admisiones@crayonesycolores.edu.ec</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card class="shadow-lg">
                    <CardHeader class="text-center">
                        <div
                            class="bg-primary text-primary-foreground mx-auto mb-3 flex size-12 items-center justify-center rounded-2xl"
                        >
                            <Palette class="size-7" />
                        </div>
                        <CardTitle>Solicitud de admisión</CardTitle>
                        <CardDescription>
                            Completa el siguiente formulario para iniciar el
                            proceso de admisión de tu hijo.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="submitted"
                            class="flex flex-col items-center gap-4 py-8 text-center"
                        >
                            <CheckCircle2 class="text-primary size-12" />
                            <p class="text-lg font-medium">
                                ¡Solicitud recibida!
                            </p>
                            <p class="text-muted-foreground text-sm">
                                Revisaremos la información y te contactaremos
                                pronto.
                            </p>
                            <Button variant="outline" @click="router.push('/')">
                                Volver al inicio
                            </Button>
                        </div>

                        <form v-else @submit.prevent="submit">
                            <FieldGroup>
                                <Field>
                                    <FieldLabel for="level"
                                        >Nivel al que aplica</FieldLabel
                                    >
                                    <Select v-model="levelId">
                                        <SelectTrigger id="level">
                                            <SelectValue
                                                placeholder="Selecciona un nivel"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="level in levels"
                                                :key="level.id"
                                                :value="String(level.id)"
                                            >
                                                {{ level.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field>
                                    <FieldLabel for="first_name"
                                        >Nombres del aspirante</FieldLabel
                                    >
                                    <Input
                                        id="first_name"
                                        v-model="firstName"
                                        placeholder="Ej. Luciana"
                                        required
                                    />
                                </Field>

                                <Field>
                                    <FieldLabel for="last_name"
                                        >Apellidos del aspirante</FieldLabel
                                    >
                                    <Input
                                        id="last_name"
                                        v-model="lastName"
                                        placeholder="Ej. Santos"
                                        required
                                    />
                                </Field>

                                <Field>
                                    <FieldLabel for="birth_date"
                                        >Fecha de nacimiento</FieldLabel
                                    >
                                    <Input
                                        id="birth_date"
                                        v-model="birthDate"
                                        type="date"
                                        required
                                    />
                                </Field>

                                <Field>
                                    <FieldLabel for="representative_names"
                                        >Nombres del representante</FieldLabel
                                    >
                                    <Input
                                        id="representative_names"
                                        v-model="representativeNames"
                                        placeholder="Ej. María Santos"
                                        required
                                    />
                                </Field>

                                <Field>
                                    <FieldLabel for="email"
                                        >Correo electrónico</FieldLabel
                                    >
                                    <Input
                                        id="email"
                                        v-model="email"
                                        type="email"
                                        placeholder="maria@ejemplo.com"
                                        required
                                    />
                                </Field>

                                <Field>
                                    <FieldLabel for="phone"
                                        >Teléfono</FieldLabel
                                    >
                                    <Input
                                        id="phone"
                                        v-model="phone"
                                        type="tel"
                                        placeholder="0991234567"
                                        required
                                    />
                                </Field>

                                <FieldError v-if="error" :errors="[error]" />
                            </FieldGroup>

                            <div class="mt-6 flex flex-col gap-3">
                                <Button
                                    type="submit"
                                    class="w-full"
                                    :disabled="loading"
                                >
                                    <Spinner
                                        v-if="loading"
                                        data-icon="inline-start"
                                    />
                                    {{
                                        loading
                                            ? 'Enviando...'
                                            : 'Enviar solicitud'
                                    }}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="w-full"
                                    @click="router.push('/')"
                                >
                                    Cancelar
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </PublicLayout>
</template>
