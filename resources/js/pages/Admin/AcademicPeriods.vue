<script setup lang="ts">
import { CalendarRange, Play } from '@lucide/vue';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AdminCrudView, {
  type Column,
  type Field,
} from '@/components/AdminCrudView.vue';
import { Button } from '@/components/ui/button';

interface CrudView {
  fetchPage: (page?: number) => Promise<void>;
}

const token = localStorage.getItem('token') ?? '';
const crud = ref<CrudView | null>(null);
const runningId = ref<number | null>(null);
const error = ref('');
const success = ref('');

const columns: Column[] = [
  { key: 'name', label: 'Periodo' },
  { key: 'starts_on', label: 'Inicio' },
  { key: 'ends_on', label: 'Fin' },
  {
    key: 'enrollment_closes_at',
    label: 'Cierre de matrículas',
    formatter: (row) =>
      new Date(String(row.enrollment_closes_at)).toLocaleString('es-EC'),
  },
  {
    key: 'status',
    label: 'Estado',
    formatter: (row) =>
      ({
        draft: 'Borrador',
        open: 'Abierto',
        closed: 'Cerrado',
        allocating: 'Asignando',
        allocated: 'Asignado',
      })[String(row.status)] ?? String(row.status),
  },
  { key: 'enrollments_count', label: 'Matrículas' },
];

const fields: Field[] = [
  { name: 'name', label: 'Nombre', type: 'text', required: true },
  {
    name: 'starts_on',
    label: 'Inicio de clases',
    type: 'date',
    required: true,
  },
  { name: 'ends_on', label: 'Fin de clases', type: 'date', required: true },
  {
    name: 'enrollment_opens_at',
    label: 'Apertura de matrículas',
    type: 'datetime-local',
    required: true,
  },
  {
    name: 'enrollment_closes_at',
    label: 'Cierre de matrículas',
    type: 'datetime-local',
    required: true,
  },
  {
    name: 'status',
    label: 'Estado',
    type: 'select',
    required: true,
    options: [
      { value: 'draft', label: 'Borrador' },
      { value: 'open', label: 'Abierto' },
      { value: 'closed', label: 'Cerrado' },
    ],
  },
];

async function runAssignments(row: Record<string, unknown>): Promise<void> {
  const reason = window.prompt('Motivo de la ejecución manual:');

  if (!reason) {
    return;
  }

  runningId.value = Number(row.id);
  error.value = '';
  success.value = '';

  try {
    const response = await fetch(
      `/api/academic-periods/${row.id}/assignments/run`,
      {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ force: true, reason }),
      },
    );
    const data = (await response.json()) as {
      assigned?: number;
      pending?: number;
      message?: string;
    };

    if (!response.ok) {
      throw new Error(data.message ?? 'No se pudo ejecutar la asignación.');
    }

    success.value = `${data.assigned ?? 0} matrícula(s) asignadas; ${data.pending ?? 0} continúan pendientes.`;
    await crud.value?.fetchPage();
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    runningId.value = null;
  }
}
</script>

<template>
  <AppLayout>
    <p
      v-if="error"
      class="text-destructive mb-4 text-sm"
    >
      {{ error }}
    </p>
    <p
      v-if="success"
      class="mb-4 text-sm text-emerald-700"
    >
      {{ success }}
    </p>
    <AdminCrudView
      ref="crud"
      title="Periodos académicos"
      description="Define la ventana de matrícula y ejecuta la asignación automática de paralelos."
      endpoint="/api/academic-periods"
      :columns="columns"
      :fields="fields"
      :icon="CalendarRange"
    >
      <template #actions-start="{ row }">
        <Button
          variant="ghost"
          size="icon"
          title="Ejecutar asignación"
          :disabled="runningId === Number(row.id)"
          @click="runAssignments(row)"
        >
          <Play class="size-4" />
        </Button>
      </template>
    </AdminCrudView>
  </AppLayout>
</template>
