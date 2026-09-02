<script setup lang="ts">
import { Receipt } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AdminCrudView, {
  type Column,
  type Field,
  type FieldOption,
} from '@/components/AdminCrudView.vue';

const token = localStorage.getItem('token') ?? '';
const studentOptions = ref<FieldOption[]>([]);

const columns: Column[] = [
  { key: 'id', label: 'ID' },
  { key: 'student.last_name', label: 'Estudiante' },
  {
    key: 'amount',
    label: 'Monto',
    formatter: (row) => `$${Number(row.amount).toFixed(2)}`,
  },
  { key: 'generation_date', label: 'Generada' },
  { key: 'due_date', label: 'Vence' },
  { key: 'status', label: 'Estado' },
];

const fields = ref<Field[]>([
  {
    name: 'student_id',
    label: 'Estudiante',
    type: 'select',
    required: true,
    options: [],
  },
  { name: 'amount', label: 'Monto', type: 'number', required: true },
  {
    name: 'generation_date',
    label: 'Fecha de generación',
    type: 'date',
    required: true,
  },
  {
    name: 'due_date',
    label: 'Fecha de vencimiento',
    type: 'date',
    required: true,
  },
  {
    name: 'status',
    label: 'Estado',
    type: 'select',
    required: true,
    options: [
      { value: 'pending', label: 'Pendiente' },
      { value: 'partial', label: 'Parcial' },
      { value: 'paid', label: 'Pagada' },
      { value: 'overdue', label: 'Vencida' },
    ],
  },
]);

async function fetchStudents(): Promise<void> {
  const response = await fetch('/api/students?per_page=1000', {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    return;
  }

  const data = (await response.json()) as {
    data: { id: number; first_name: string; last_name: string }[];
  };

  studentOptions.value = data.data.map((s) => ({
    value: s.id,
    label: `${s.first_name} ${s.last_name}`,
  }));

  fields.value[0].options = studentOptions.value;
}

onMounted(() => {
  void fetchStudents();
});
</script>

<template>
  <AppLayout>
    <AdminCrudView
      title="Pensiones"
      description="Rubros de matrícula y pensiones mensuales."
      endpoint="/api/tuitions"
      :columns="columns"
      :fields="fields"
      :icon="Receipt"
    />
  </AppLayout>
</template>
