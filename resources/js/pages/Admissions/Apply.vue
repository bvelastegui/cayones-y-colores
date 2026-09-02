<script setup lang="ts">
import {
    ArrowLeft,
    CheckCircle2,
    Clock,
    HeartHandshake,
    Mail,
    Phone,
    ShieldCheck,
} from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import SectionLabel from '@/components/landing/atoms/SectionLabel.vue';
import PublicPageBenefit from '@/components/landing/molecules/PublicPageBenefit.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
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
    FieldDescription,
    FieldError,
    FieldGroup,
    FieldLabel,
    FieldLegend,
    FieldSet,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectGroup,
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
const levelsLoading = ref(true);
const levelsError = ref('');
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

const noLevelsAvailable = computed(
    () =>
        !levelsLoading.value && !levelsError.value && levels.value.length === 0,
);

const benefits = [
    {
        icon: Clock,
        title: 'Solicitud sencilla',
        description: 'Completa la información inicial en pocos minutos.',
    },
    {
        icon: HeartHandshake,
        title: 'Acompañamiento cercano',
        description:
            'Nuestro equipo te orientará durante cada etapa del proceso.',
    },
    {
        icon: ShieldCheck,
        title: 'Información segura',
        description:
            'Tus datos se utilizan únicamente para gestionar la admisión.',
    },
];

async function fetchLevels(): Promise<void> {
    levelsLoading.value = true;
    levelsError.value = '';

    try {
        const response = await fetch('/api/levels', {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error('No pudimos cargar los niveles disponibles.');
        }

        const data = await response.json();
        levels.value = data.data ?? [];
    } catch (exception) {
        levelsError.value =
            exception instanceof Error
                ? exception.message
                : 'Error desconocido.';
    } finally {
        levelsLoading.value = false;
    }
}

async function submit(): Promise<void> {
    if (!levelId.value) {
        error.value = 'Selecciona el nivel al que deseas aplicar.';

        return;
    }

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
        <section class="relative overflow-hidden" aria-labelledby="apply-title">
            <div
                class="bg-primary/10 absolute top-20 -left-24 size-80 rounded-full blur-3xl"
                aria-hidden="true"
            />
            <div
                class="bg-sun/20 absolute top-1/3 -right-24 size-72 rounded-full blur-3xl"
                aria-hidden="true"
            />

            <div
                class="mx-auto grid max-w-7xl gap-12 px-5 py-12 sm:px-8 lg:grid-cols-[0.78fr_1.22fr] lg:px-10 lg:py-20"
            >
                <aside
                    class="flex flex-col gap-9 lg:sticky lg:top-28 lg:self-start"
                >
                    <div class="flex flex-col gap-5">
                        <SectionLabel label="Admisiones 2026" />
                        <h1
                            id="apply-title"
                            class="font-heading text-4xl leading-tight font-bold tracking-tight sm:text-5xl"
                        >
                            El primer paso de una
                            <span class="text-primary">gran aventura</span>
                        </h1>
                        <p class="text-muted-foreground text-lg leading-8">
                            Cuéntanos sobre tu familia. Revisaremos la solicitud
                            y te contactaremos para acompañarte en los
                            siguientes pasos.
                        </p>
                    </div>

                    <ul class="flex flex-col gap-5">
                        <PublicPageBenefit
                            v-for="benefit in benefits"
                            :key="benefit.title"
                            v-bind="benefit"
                        />
                    </ul>

                    <div class="bg-sun/20 rounded-3xl p-6">
                        <strong class="font-heading text-lg"
                            >¿Necesitas ayuda?</strong
                        >
                        <p class="text-muted-foreground mt-2 text-sm leading-6">
                            Nuestro equipo de admisiones está listo para
                            orientarte.
                        </p>
                        <address
                            class="mt-4 flex flex-col gap-3 text-sm not-italic"
                        >
                            <a
                                href="tel:+593991234567"
                                class="focus-visible:ring-ring flex min-h-11 items-center gap-3 rounded-xl focus-visible:ring-2 focus-visible:outline-none"
                            >
                                <Phone
                                    class="text-sun-foreground size-5"
                                    aria-hidden="true"
                                />
                                +593 99 123 4567
                            </a>
                            <a
                                href="mailto:admisiones@crayonesycolores.edu.ec"
                                class="focus-visible:ring-ring flex min-h-11 items-center gap-3 rounded-xl break-all focus-visible:ring-2 focus-visible:outline-none"
                            >
                                <Mail
                                    class="text-sun-foreground size-5 shrink-0"
                                    aria-hidden="true"
                                />
                                admisiones@crayonesycolores.edu.ec
                            </a>
                        </address>
                    </div>
                </aside>

                <Card class="border-border/70 shadow-primary/5 shadow-xl">
                    <template v-if="submitted">
                        <CardHeader
                            class="items-center gap-5 pt-10 text-center"
                        >
                            <span
                                class="bg-leaf/15 text-leaf-foreground flex size-16 items-center justify-center rounded-3xl"
                                aria-hidden="true"
                            >
                                <CheckCircle2 class="size-9" />
                            </span>
                            <div
                                class="flex max-w-lg flex-col gap-2"
                                role="status"
                            >
                                <CardTitle class="font-heading text-3xl">
                                    ¡Solicitud recibida!
                                </CardTitle>
                                <CardDescription class="text-base leading-7">
                                    Gracias por confiar en Crayones y Colores.
                                    Revisaremos la información y te
                                    contactaremos pronto.
                                </CardDescription>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="bg-muted rounded-3xl p-6 text-center">
                                <p class="font-semibold">¿Qué sucede ahora?</p>
                                <p
                                    class="text-muted-foreground mt-2 text-sm leading-6"
                                >
                                    Nuestro equipo validará la disponibilidad
                                    del nivel solicitado y se comunicará usando
                                    los datos registrados.
                                </p>
                            </div>
                        </CardContent>
                        <CardFooter class="justify-center pb-6">
                            <Button
                                variant="outline"
                                size="lg"
                                class="min-h-11"
                                @click="router.push('/')"
                            >
                                <ArrowLeft data-icon="inline-start" />
                                Volver al inicio
                            </Button>
                        </CardFooter>
                    </template>

                    <template v-else>
                        <CardHeader class="gap-4">
                            <SectionLabel label="Solicitud en línea" />
                            <div class="flex flex-col gap-2">
                                <CardTitle class="font-heading text-2xl">
                                    Datos para la admisión
                                </CardTitle>
                                <CardDescription class="text-base leading-6">
                                    Todos los campos son necesarios para iniciar
                                    el proceso.
                                </CardDescription>
                            </div>
                        </CardHeader>

                        <form :aria-busy="loading" @submit.prevent="submit">
                            <CardContent>
                                <div class="flex flex-col gap-8">
                                    <FieldSet>
                                        <FieldLegend
                                            >Sobre el aspirante</FieldLegend
                                        >
                                        <FieldDescription>
                                            Información del niño o niña que
                                            desea ingresar.
                                        </FieldDescription>
                                        <FieldGroup
                                            class="grid gap-5 sm:grid-cols-2"
                                        >
                                            <Field
                                                class="sm:col-span-2"
                                                :data-invalid="
                                                    Boolean(error) && !levelId
                                                "
                                            >
                                                <FieldLabel for="level">
                                                    Nivel al que aplica
                                                </FieldLabel>
                                                <Select
                                                    v-model="levelId"
                                                    :disabled="
                                                        levelsLoading ||
                                                        Boolean(levelsError) ||
                                                        noLevelsAvailable
                                                    "
                                                    required
                                                >
                                                    <SelectTrigger
                                                        id="level"
                                                        class="min-h-11 w-full"
                                                        :aria-invalid="
                                                            Boolean(error) &&
                                                            !levelId
                                                        "
                                                    >
                                                        <SelectValue
                                                            :placeholder="
                                                                levelsLoading
                                                                    ? 'Cargando niveles...'
                                                                    : noLevelsAvailable
                                                                      ? 'No hay niveles disponibles'
                                                                      : 'Selecciona un nivel'
                                                            "
                                                        />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            <SelectItem
                                                                v-for="level in levels"
                                                                :key="level.id"
                                                                :value="
                                                                    String(
                                                                        level.id,
                                                                    )
                                                                "
                                                            >
                                                                {{ level.name }}
                                                            </SelectItem>
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                                <FieldError
                                                    v-if="levelsError"
                                                    :errors="[levelsError]"
                                                />
                                                <FieldDescription
                                                    v-else-if="
                                                        noLevelsAvailable
                                                    "
                                                >
                                                    En este momento no hay
                                                    niveles habilitados para
                                                    admisión.
                                                </FieldDescription>
                                                <Button
                                                    v-if="levelsError"
                                                    type="button"
                                                    variant="outline"
                                                    class="min-h-11 self-start"
                                                    @click="fetchLevels"
                                                >
                                                    Intentar de nuevo
                                                </Button>
                                            </Field>

                                            <Field>
                                                <FieldLabel for="first_name">
                                                    Nombres
                                                </FieldLabel>
                                                <Input
                                                    id="first_name"
                                                    v-model="firstName"
                                                    name="first_name"
                                                    autocomplete="given-name"
                                                    placeholder="Ej. Luciana"
                                                    class="min-h-11"
                                                    required
                                                />
                                            </Field>

                                            <Field>
                                                <FieldLabel for="last_name">
                                                    Apellidos
                                                </FieldLabel>
                                                <Input
                                                    id="last_name"
                                                    v-model="lastName"
                                                    name="last_name"
                                                    autocomplete="family-name"
                                                    placeholder="Ej. Santos"
                                                    class="min-h-11"
                                                    required
                                                />
                                            </Field>

                                            <Field class="sm:col-span-2">
                                                <FieldLabel for="birth_date">
                                                    Fecha de nacimiento
                                                </FieldLabel>
                                                <Input
                                                    id="birth_date"
                                                    v-model="birthDate"
                                                    name="birth_date"
                                                    type="date"
                                                    class="min-h-11"
                                                    required
                                                />
                                            </Field>
                                        </FieldGroup>
                                    </FieldSet>

                                    <FieldSet>
                                        <FieldLegend
                                            >Datos del
                                            representante</FieldLegend
                                        >
                                        <FieldDescription>
                                            Usaremos estos medios para
                                            contactarte.
                                        </FieldDescription>
                                        <FieldGroup
                                            class="grid gap-5 sm:grid-cols-2"
                                        >
                                            <Field class="sm:col-span-2">
                                                <FieldLabel
                                                    for="representative_names"
                                                >
                                                    Nombres completos
                                                </FieldLabel>
                                                <Input
                                                    id="representative_names"
                                                    v-model="
                                                        representativeNames
                                                    "
                                                    name="representative_names"
                                                    autocomplete="name"
                                                    placeholder="Ej. María Santos"
                                                    class="min-h-11"
                                                    required
                                                />
                                            </Field>

                                            <Field>
                                                <FieldLabel for="email">
                                                    Correo electrónico
                                                </FieldLabel>
                                                <Input
                                                    id="email"
                                                    v-model="email"
                                                    name="email"
                                                    type="email"
                                                    autocomplete="email"
                                                    placeholder="maria@ejemplo.com"
                                                    class="min-h-11"
                                                    required
                                                />
                                            </Field>

                                            <Field>
                                                <FieldLabel for="phone">
                                                    Teléfono
                                                </FieldLabel>
                                                <Input
                                                    id="phone"
                                                    v-model="phone"
                                                    name="phone"
                                                    type="tel"
                                                    autocomplete="tel"
                                                    inputmode="tel"
                                                    placeholder="0991234567"
                                                    class="min-h-11"
                                                    required
                                                />
                                            </Field>
                                        </FieldGroup>
                                    </FieldSet>

                                    <FieldError
                                        v-if="error"
                                        :errors="[error]"
                                    />
                                </div>
                            </CardContent>

                            <CardFooter
                                class="mt-8 flex flex-col-reverse gap-3 border-t sm:flex-row sm:justify-end"
                            >
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="lg"
                                    class="min-h-11 w-full sm:w-auto"
                                    @click="router.push('/')"
                                >
                                    Cancelar
                                </Button>
                                <Button
                                    type="submit"
                                    size="lg"
                                    class="min-h-11 w-full sm:w-auto"
                                    :disabled="
                                        loading ||
                                        levelsLoading ||
                                        Boolean(levelsError) ||
                                        noLevelsAvailable
                                    "
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
                            </CardFooter>
                        </form>
                    </template>
                </Card>
            </div>
        </section>
    </PublicLayout>
</template>
