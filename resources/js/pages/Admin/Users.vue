<script setup lang="ts">
import { Users } from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AdminCrudView, {
  type Column,
  type Field,
} from '@/components/AdminCrudView.vue';

const columns: Column[] = [
  { key: 'id', label: 'ID' },
  { key: 'name', label: 'Nombre' },
  { key: 'email', label: 'Correo' },
  { key: 'role', label: 'Rol' },
  {
    key: 'is_active',
    label: 'Activo',
    formatter: (row) => (row.is_active ? 'Sí' : 'No'),
  },
];

const fields: Field[] = [
  { name: 'name', label: 'Nombre', type: 'text', required: true },
  { name: 'email', label: 'Correo', type: 'email', required: true },
  { name: 'password', label: 'Contraseña', type: 'password' },
  {
    name: 'identification',
    label: 'Identificación',
    type: 'text',
    required: true,
  },
  { name: 'phone', label: 'Teléfono', type: 'text' },
  { name: 'address', label: 'Dirección', type: 'text' },
  {
    name: 'role',
    label: 'Rol',
    type: 'select',
    required: true,
    options: [
      { value: 'admin', label: 'Administrador' },
      { value: 'teacher', label: 'Docente' },
      { value: 'representative', label: 'Representante' },
    ],
  },
  {
    name: 'is_active',
    label: 'Activo',
    type: 'select',
    options: [
      { value: '1', label: 'Sí' },
      { value: '0', label: 'No' },
    ],
  },
];
</script>

<template>
  <AppLayout>
    <AdminCrudView
      title="Usuarios"
      description="Gestión de cuentas de usuario del sistema."
      endpoint="/api/users"
      :columns="columns"
      :fields="fields"
      :icon="Users"
    />
  </AppLayout>
</template>
