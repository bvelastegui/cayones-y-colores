<script setup lang="ts">
import { GraduationCap } from '@lucide/vue';
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
    { key: 'teacher_type', label: 'Tipo' },
];

const fields: Field[] = [
    { name: 'id_card', label: 'Cédula', type: 'text', required: true },
    { name: 'first_name', label: 'Nombres', type: 'text', required: true },
    { name: 'last_name', label: 'Apellidos', type: 'text', required: true },
    { name: 'email', label: 'Correo', type: 'email', required: true },
    {
        name: 'teacher_type',
        label: 'Tipo',
        type: 'select',
        required: true,
        options: [
            { value: 'principal', label: 'Principal' },
            { value: 'auxiliary', label: 'Auxiliar' },
        ],
    },
];
</script>

<template>
    <AppLayout>
        <AdminCrudView
            title="Docentes"
            description="Profesores principales y auxiliares del centro."
            endpoint="/api/teachers"
            :columns="columns"
            :fields="fields"
            :icon="GraduationCap"
        />
    </AppLayout>
</template>
