<script setup lang="ts">
import { HeartHandshake } from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AdminCrudView, {
  type Column,
  type Field,
} from '@/components/AdminCrudView.vue';

const columns: Column[] = [
  { key: 'id', label: 'ID' },
  { key: 'id_card', label: 'Cédula' },
  { key: 'first_name', label: 'Nombres' },
  { key: 'last_name', label: 'Apellidos' },
  { key: 'email', label: 'Correo' },
  { key: 'phone', label: 'Teléfono' },
];

const fields: Field[] = [
  { name: 'id_card', label: 'Cédula', type: 'text', required: true },
  { name: 'first_name', label: 'Nombres', type: 'text', required: true },
  { name: 'last_name', label: 'Apellidos', type: 'text', required: true },
  { name: 'email', label: 'Correo', type: 'email', required: true },
  { name: 'phone', label: 'Teléfono', type: 'text' },
];
</script>

<template>
  <AppLayout>
    <AdminCrudView
      title="Representantes"
      description="Padres o representantes legales de los estudiantes."
      endpoint="/api/representatives"
      :columns="columns"
      :fields="fields"
      :icon="HeartHandshake"
    />
  </AppLayout>
</template>
