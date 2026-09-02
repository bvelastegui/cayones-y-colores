<script setup lang="ts">
import { ref } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import SectionLabel from '@/components/landing/atoms/SectionLabel.vue';
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
import PublicLayout from '@/layouts/PublicLayout.vue';

const route = useRoute();
const email = typeof route.query.email === 'string' ? route.query.email : '';
const token = typeof route.query.token === 'string' ? route.query.token : '';

const password = ref('');
const passwordConfirmation = ref('');
const error = ref(
  token && email ? '' : 'El enlace de invitación no es válido.',
);
const loading = ref(false);
const completed = ref(false);

async function submit(): Promise<void> {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetch('/api/reset-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({
        email,
        token,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
      }),
    });
    const data = await response.json();

    if (!response.ok) {
      const validationError = Object.values(data.errors ?? {})
        .flat()
        .find((message): message is string => typeof message === 'string');

      throw new Error(
        validationError ?? data.message ?? 'No se pudo crear la contraseña.',
      );
    }

    completed.value = true;
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
      class="mx-auto flex w-full max-w-2xl flex-1 items-center px-5 py-12 sm:px-8 lg:py-20"
      aria-labelledby="set-password-title"
    >
      <Card class="border-border/70 shadow-primary/5 w-full shadow-xl">
        <CardHeader class="gap-4">
          <SectionLabel label="Acceso seguro" />
          <div class="flex flex-col gap-2">
            <CardTitle
              id="set-password-title"
              class="font-heading text-2xl"
            >
              {{ completed ? 'Contraseña creada' : 'Crea tu contraseña' }}
            </CardTitle>
            <CardDescription class="text-base leading-6">
              {{
                completed
                  ? 'Tu cuenta está lista. Ya puedes iniciar sesión.'
                  : `Define la contraseña para la cuenta ${email || 'indicada en la invitación'}.`
              }}
            </CardDescription>
          </div>
        </CardHeader>

        <CardContent v-if="completed">
          <p
            class="text-muted-foreground"
            role="status"
          >
            La contraseña se guardó correctamente.
          </p>
        </CardContent>

        <form
          v-else
          :aria-busy="loading"
          aria-label="Formulario para crear contraseña"
          @submit.prevent="submit"
        >
          <CardContent>
            <FieldGroup>
              <Field :data-invalid="Boolean(error)">
                <FieldLabel for="password">Nueva contraseña</FieldLabel>
                <Input
                  id="password"
                  v-model="password"
                  name="password"
                  type="password"
                  autocomplete="new-password"
                  class="min-h-11"
                  :aria-invalid="Boolean(error)"
                  :disabled="!token || !email"
                  required
                />
              </Field>
              <Field :data-invalid="Boolean(error)">
                <FieldLabel for="password-confirmation"
                  >Confirma la contraseña</FieldLabel
                >
                <Input
                  id="password-confirmation"
                  v-model="passwordConfirmation"
                  name="password_confirmation"
                  type="password"
                  autocomplete="new-password"
                  class="min-h-11"
                  :aria-invalid="Boolean(error)"
                  :disabled="!token || !email"
                  required
                />
              </Field>
              <FieldError
                v-if="error"
                role="alert"
                :errors="[error]"
              />
            </FieldGroup>
          </CardContent>

          <CardFooter class="mt-8 border-t">
            <Button
              type="submit"
              size="lg"
              class="min-h-11 w-full"
              :disabled="loading || !token || !email"
            >
              <Spinner
                v-if="loading"
                data-icon="inline-start"
              />
              {{ loading ? 'Guardando...' : 'Crear contraseña' }}
            </Button>
          </CardFooter>
        </form>

        <CardFooter
          v-if="completed"
          class="border-t"
        >
          <Button
            as-child
            size="lg"
            class="min-h-11 w-full"
          >
            <RouterLink to="/login">Iniciar sesión</RouterLink>
          </Button>
        </CardFooter>
      </Card>
    </section>
  </PublicLayout>
</template>
