<script setup lang="ts">
import { BookOpen, HeartHandshake, ShieldCheck } from '@lucide/vue';
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import SectionLabel from '@/components/landing/atoms/SectionLabel.vue';
import PublicPageBenefit from '@/components/landing/molecules/PublicPageBenefit.vue';
import { useAuth } from '@/composables/auth';
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
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';

const router = useRouter();
const auth = useAuth(router);

const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);

const benefits = [
  {
    icon: HeartHandshake,
    title: 'Familias más cerca',
    description:
      'Consulta matrículas, pensiones e informes desde un solo lugar.',
  },
  {
    icon: BookOpen,
    title: 'Aprendizaje acompañado',
    description: 'Sigue los avances académicos y la información importante.',
  },
  {
    icon: ShieldCheck,
    title: 'Información protegida',
    description: 'Cada perfil accede únicamente a las funciones que necesita.',
  },
];

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
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <PublicLayout>
    <section
      class="relative overflow-hidden"
      aria-labelledby="login-title"
    >
      <div
        class="bg-primary/10 absolute top-20 -left-24 size-80 rounded-full blur-3xl"
        aria-hidden="true"
      />
      <div
        class="bg-sun/20 absolute top-1/3 -right-24 size-72 rounded-full blur-3xl"
        aria-hidden="true"
      />

      <div
        class="mx-auto grid max-w-7xl items-center gap-12 px-5 py-12 sm:px-8 lg:grid-cols-[0.78fr_1.22fr] lg:px-10 lg:py-20"
      >
        <aside class="flex flex-col gap-9 lg:sticky lg:top-28 lg:self-start">
          <div class="flex flex-col gap-5">
            <SectionLabel label="Portal institucional" />
            <h1
              id="login-title"
              class="font-heading text-4xl leading-tight font-bold tracking-tight sm:text-5xl"
            >
              Bienvenido de nuevo a
              <span class="text-primary">Crayones y Colores</span>
            </h1>
            <p class="text-muted-foreground text-lg leading-8">
              Accede a tu espacio para mantenerte al día y acompañar cada etapa
              del aprendizaje.
            </p>
          </div>

          <ul class="flex flex-col gap-5">
            <PublicPageBenefit
              v-for="benefit in benefits"
              :key="benefit.title"
              v-bind="benefit"
            />
          </ul>
        </aside>

        <Card
          class="border-border/70 shadow-primary/5 w-full shadow-xl lg:justify-self-end"
        >
          <CardHeader class="gap-4">
            <SectionLabel label="Acceso seguro" />
            <div class="flex flex-col gap-2">
              <CardTitle class="font-heading text-2xl">
                Iniciar sesión
              </CardTitle>
              <CardDescription class="text-base leading-6">
                Ingresa las credenciales asignadas por la institución.
              </CardDescription>
            </div>
          </CardHeader>

          <form
            :aria-busy="loading"
            aria-label="Formulario de inicio de sesión"
            @submit.prevent="submit"
          >
            <CardContent>
              <FieldGroup>
                <Field :data-invalid="Boolean(error)">
                  <FieldLabel for="email"> Correo electrónico </FieldLabel>
                  <Input
                    id="email"
                    v-model="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    placeholder="tu@correo.com"
                    class="min-h-11"
                    :aria-invalid="Boolean(error)"
                    required
                  />
                </Field>
                <Field :data-invalid="Boolean(error)">
                  <FieldLabel for="password"> Contraseña </FieldLabel>
                  <Input
                    id="password"
                    v-model="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Ingresa tu contraseña"
                    class="min-h-11"
                    :aria-invalid="Boolean(error)"
                    required
                  />
                </Field>
                <FieldError
                  v-if="error"
                  :errors="[error]"
                />
                <FieldDescription>
                  El acceso está disponible para familias, docentes y
                  administración.
                </FieldDescription>
              </FieldGroup>
            </CardContent>

            <CardFooter class="mt-8 flex flex-col gap-4 border-t">
              <Button
                type="submit"
                size="lg"
                class="min-h-11 w-full"
                :disabled="loading"
              >
                <Spinner
                  v-if="loading"
                  data-icon="inline-start"
                />
                {{ loading ? 'Ingresando...' : 'Ingresar' }}
              </Button>
              <p class="text-muted-foreground w-full text-center text-sm">
                ¿Aún no formas parte del centro?
                <RouterLink
                  to="/apply"
                  class="text-primary focus-visible:ring-ring rounded-sm font-semibold hover:underline focus-visible:ring-2 focus-visible:outline-none"
                >
                  Solicita admisión
                </RouterLink>
              </p>
            </CardFooter>
          </form>
        </Card>
      </div>
    </section>
  </PublicLayout>
</template>
