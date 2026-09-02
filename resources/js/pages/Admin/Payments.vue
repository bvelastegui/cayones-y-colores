<script setup lang="ts">
import { Banknote } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AdminCrudView, {
  type Column,
  type Field,
  type FieldOption,
} from '@/components/AdminCrudView.vue';

const token = localStorage.getItem('token') ?? '';
const tuitionOptions = ref<FieldOption[]>([]);

const columns: Column[] = [
  { key: 'id', label: 'ID' },
  { key: 'tuition.student.last_name', label: 'Estudiante' },
  {
    key: 'amount_paid',
    label: 'Monto pagado',
    formatter: (row) => `$${Number(row.amount_paid).toFixed(2)}`,
  },
  { key: 'payment_method', label: 'Método' },
  { key: 'payment_date', label: 'Fecha' },
  { key: 'reference_number', label: 'Referencia' },
];

const fields = ref<Field[]>([
  {
    name: 'tuition_id',
    label: 'Pensión',
    type: 'select',
    required: true,
    options: [],
  },
  {
    name: 'payment_method',
    label: 'Método de pago',
    type: 'select',
    required: true,
    options: [
      { value: 'cash', label: 'Efectivo' },
      { value: 'credit_card', label: 'Tarjeta de crédito' },
      { value: 'transfer', label: 'Transferencia' },
      { value: 'payphone', label: 'Payphone' },
    ],
  },
  {
    name: 'amount_paid',
    label: 'Monto pagado',
    type: 'number',
    required: true,
  },
  {
    name: 'payment_date',
    label: 'Fecha de pago',
    type: 'date',
    required: true,
  },
  { name: 'reference_number', label: 'Número de comprobante', type: 'text' },
]);

async function fetchTuitions(): Promise<void> {
  const response = await fetch('/api/tuitions?per_page=1000', {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    return;
  }

  const data = (await response.json()) as {
    data: {
      id: number;
      student: { first_name: string; last_name: string };
      amount: number;
    }[];
  };

  tuitionOptions.value = data.data.map((t) => ({
    value: t.id,
    label: `${t.student.first_name} ${t.student.last_name} - $${Number(t.amount).toFixed(2)}`,
  }));

  fields.value[0].options = tuitionOptions.value;
}

onMounted(() => {
  void fetchTuitions();
});
</script>

<template>
  <AppLayout>
    <AdminCrudView
      title="Pagos"
      description="Registro de pagos físicos en ventanilla y online."
      endpoint="/api/payments"
      :columns="columns"
      :fields="fields"
      :icon="Banknote"
    />
  </AppLayout>
</template>
