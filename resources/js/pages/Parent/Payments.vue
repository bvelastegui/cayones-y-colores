<script setup lang="ts">
import { AlertCircle, CreditCard } from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { useCurrentStudent } from '@/composables/currentStudent';

interface Tuition {
  id: number;
  student_id: number;
  amount: string;
  remaining_balance: string;
  generation_date: string;
  due_date: string;
  status: 'pending' | 'partial' | 'paid' | 'overdue';
  student: { first_name: string; last_name: string };
}

const token = localStorage.getItem('token') ?? '';
const { selectedStudent } = useCurrentStudent();

const tuitions = ref<Tuition[]>([]);
const loading = ref(false);
const paying = ref(false);
const selectedTuitionIds = ref<number[]>([]);
const error = ref('');
const success = ref('');

const filteredTuitions = computed(() => {
  if (!selectedStudent.value) {
    return tuitions.value;
  }

  return tuitions.value.filter(
    (tuition) => tuition.student_id === selectedStudent.value?.id,
  );
});

const totalPending = computed(() =>
  filteredTuitions.value.reduce(
    (sum, tuition) => sum + Number(tuition.remaining_balance),
    0,
  ),
);

const allVisibleSelected = computed(
  () =>
    filteredTuitions.value.length > 0 &&
    filteredTuitions.value.every((tuition) =>
      selectedTuitionIds.value.includes(tuition.id),
    ),
);

async function fetchTuitions(): Promise<void> {
  loading.value = true;
  error.value = '';
  success.value = '';

  try {
    const response = await fetch('/api/me/tuitions', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Error al cargar las pensiones.');
    }

    tuitions.value = await response.json();
    const availableIds = new Set(tuitions.value.map((tuition) => tuition.id));
    selectedTuitionIds.value = selectedTuitionIds.value.filter((id) =>
      availableIds.has(id),
    );
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    loading.value = false;
  }
}

async function pay(): Promise<void> {
  if (selectedTuitionIds.value.length === 0) {
    error.value = 'Selecciona al menos una pensión.';

    return;
  }

  paying.value = true;
  error.value = '';
  success.value = '';

  try {
    const response = await fetch('/api/me/payments/payphone', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({ tuition_ids: selectedTuitionIds.value }),
    });

    const data = (await response.json()) as {
      message?: string;
      payment_url?: string;
    };

    if (!response.ok) {
      throw new Error(data.message ?? 'No se pudo procesar el pago.');
    }

    if (!data.payment_url) {
      throw new Error('PayPhone no devolvió una dirección de pago válida.');
    }

    window.location.assign(data.payment_url);
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    paying.value = false;
  }
}

function toggleAllVisible(): void {
  const visibleIds = filteredTuitions.value.map((tuition) => tuition.id);

  if (allVisibleSelected.value) {
    selectedTuitionIds.value = selectedTuitionIds.value.filter(
      (id) => !visibleIds.includes(id),
    );

    return;
  }

  selectedTuitionIds.value = [
    ...new Set([...selectedTuitionIds.value, ...visibleIds]),
  ];
}

function statusLabel(status: string): string {
  switch (status) {
    case 'pending':
      return 'Pendiente';
    case 'partial':
      return 'Parcial';
    case 'paid':
      return 'Pagada';
    case 'overdue':
      return 'Vencida';
    default:
      return status;
  }
}

function statusVariant(
  status: string,
): 'default' | 'secondary' | 'destructive' | 'outline' {
  switch (status) {
    case 'paid':
      return 'default';
    case 'partial':
      return 'secondary';
    case 'overdue':
      return 'destructive';
    default:
      return 'outline';
  }
}

function isDueSoon(dueDate: string): boolean {
  const due = new Date(dueDate);
  const today = new Date();
  const diff = due.getTime() - today.getTime();

  return diff >= 0 && diff <= 3 * 24 * 60 * 60 * 1000;
}

onMounted(async () => {
  const paymentResult = new URLSearchParams(window.location.search).get(
    'payment',
  );

  await fetchTuitions();

  if (paymentResult === 'approved') {
    success.value = 'Pago confirmado correctamente.';
  } else if (paymentResult === 'cancelled') {
    error.value = 'El pago fue cancelado.';
  } else if (paymentResult === 'error') {
    error.value = 'No fue posible confirmar el pago con PayPhone.';
  }

  if (paymentResult) {
    window.history.replaceState({}, '', window.location.pathname);
  }
});
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">
          <CreditCard class="mr-2 inline size-6" />
          Pensiones
        </h1>
        <p class="text-muted-foreground">
          Revisa y paga las obligaciones pendientes de tus hijos.
        </p>
      </div>

      <p
        v-if="error"
        class="text-destructive text-sm"
      >
        {{ error }}
      </p>
      <p
        v-if="success"
        class="text-sm text-green-600"
      >
        {{ success }}
      </p>
      <p
        v-if="loading"
        class="text-muted-foreground"
      >
        Cargando...
      </p>

      <Card
        v-if="filteredTuitions.length > 0"
        class="border-primary/20 bg-primary/5"
      >
        <CardContent
          class="flex flex-col gap-4 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
          <div class="flex items-center gap-3">
            <AlertCircle class="text-primary size-5" />
            <div class="text-sm">
              <p>
                Total pendiente:
                <span class="font-semibold">
                  ${{ totalPending.toFixed(2) }}
                </span>
              </p>
              <p class="text-muted-foreground">
                {{ selectedTuitionIds.length }}
                {{ selectedTuitionIds.length === 1 ? 'pensión' : 'pensiones' }}
                seleccionadas
              </p>
            </div>
          </div>
          <div class="flex flex-wrap gap-2">
            <Button
              type="button"
              variant="outline"
              :disabled="paying"
              @click="toggleAllVisible"
            >
              {{
                allVisibleSelected ? 'Quitar selección' : 'Seleccionar todas'
              }}
            </Button>
            <Button
              type="button"
              :disabled="selectedTuitionIds.length === 0 || paying"
              @click="pay"
            >
              {{
                paying ? 'Preparando pago...' : 'Pagar selección con PayPhone'
              }}
            </Button>
          </div>
        </CardContent>
      </Card>

      <div
        v-if="!loading && filteredTuitions.length === 0"
        class="text-muted-foreground text-center"
      >
        No tienes pensiones pendientes.
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <Card
          v-for="tuition in filteredTuitions"
          :key="tuition.id"
          class="border-none shadow-sm"
          :class="
            selectedTuitionIds.includes(tuition.id) ? 'ring-primary ring-2' : ''
          "
        >
          <CardHeader>
            <div class="flex items-start justify-between">
              <div>
                <CardTitle class="text-base">
                  Pensión
                  {{
                    new Date(tuition.generation_date).toLocaleDateString(
                      'es-ES',
                      {
                        month: 'long',
                      },
                    )
                  }}
                </CardTitle>
                <CardDescription>
                  {{ tuition.student.first_name }}
                  {{ tuition.student.last_name }}
                </CardDescription>
              </div>
              <Badge :variant="statusVariant(tuition.status)">
                {{ statusLabel(tuition.status) }}
              </Badge>
            </div>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="text-sm">
              Saldo pendiente:
              <span class="text-foreground font-semibold">
                ${{ Number(tuition.remaining_balance).toFixed(2) }}
              </span>
            </div>
            <div
              class="text-sm"
              :class="
                isDueSoon(tuition.due_date)
                  ? 'text-destructive font-medium'
                  : 'text-muted-foreground'
              "
            >
              Vence el
              {{ new Date(tuition.due_date).toLocaleDateString() }}
              <span v-if="isDueSoon(tuition.due_date)">
                (próximo a vencer)
              </span>
            </div>
            <label
              :for="`tuition-${tuition.id}`"
              class="border-border hover:bg-muted/50 flex cursor-pointer items-center gap-3 rounded-md border p-3 transition-colors"
            >
              <input
                :id="`tuition-${tuition.id}`"
                v-model="selectedTuitionIds"
                type="checkbox"
                :value="tuition.id"
                :disabled="paying"
                class="border-input text-primary focus-visible:ring-ring size-4 rounded focus-visible:ring-2 focus-visible:ring-offset-2"
              />
              <span class="text-sm font-medium">Incluir en el pago</span>
            </label>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
