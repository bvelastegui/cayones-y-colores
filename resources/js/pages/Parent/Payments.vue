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
  generation_date: string;
  due_date: string;
  status: 'pending' | 'partial' | 'paid' | 'overdue';
  student: { first_name: string; last_name: string };
}

const token = localStorage.getItem('token') ?? '';
const { selectedStudent } = useCurrentStudent();

const tuitions = ref<Tuition[]>([]);
const loading = ref(false);
const paying = ref<number | null>(null);
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
    (sum, tuition) => sum + Number(tuition.amount),
    0,
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
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    loading.value = false;
  }
}

async function pay(tuition: Tuition): Promise<void> {
  paying.value = tuition.id;
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
      body: JSON.stringify({ tuition_id: tuition.id }),
    });

    const data = (await response.json()) as { message?: string };

    if (!response.ok) {
      throw new Error(data.message ?? 'No se pudo procesar el pago.');
    }

    success.value = data.message ?? 'Pago realizado correctamente.';
    await fetchTuitions();
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    paying.value = null;
  }
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

onMounted(() => {
  void fetchTuitions();
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
        <CardContent class="flex items-center gap-3 py-4">
          <AlertCircle class="text-primary size-5" />
          <p class="text-sm">
            Total pendiente:
            <span class="font-semibold"> ${{ totalPending.toFixed(2) }} </span>
          </p>
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
              Monto:
              <span class="text-foreground font-semibold">
                ${{ Number(tuition.amount).toFixed(2) }}
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
            <Button
              class="w-full"
              :disabled="tuition.status === 'paid' || paying === tuition.id"
              @click="pay(tuition)"
            >
              <span v-if="paying === tuition.id">Procesando...</span>
              <span v-else-if="tuition.status === 'paid'">Pagada</span>
              <span v-else>Pagar con Payphone</span>
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
