<script setup lang="ts">
import { Layers } from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AdminCrudView, {
    type Column,
    type Field,
} from '@/components/AdminCrudView.vue';

const columns: Column[] = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Nombre' },
    { key: 'max_capacity', label: 'Aforo máximo' },
    { key: 'student_aux_ratio', label: 'Ratio auxiliares' },
    {
        key: 'enrollment_fee',
        label: 'Matrícula',
        formatter: (row) => `$${Number(row.enrollment_fee).toFixed(2)}`,
    },
    {
        key: 'monthly_fee',
        label: 'Mensualidad',
        formatter: (row) => `$${Number(row.monthly_fee).toFixed(2)}`,
    },
];

const fields: Field[] = [
    { name: 'name', label: 'Nombre', type: 'text', required: true },
    {
        name: 'max_capacity',
        label: 'Aforo máximo',
        type: 'number',
        required: true,
    },
    {
        name: 'student_aux_ratio',
        label: 'Estudiantes por auxiliar',
        type: 'number',
        required: true,
    },
    {
        name: 'enrollment_fee',
        label: 'Valor matrícula',
        type: 'number',
        required: true,
    },
    {
        name: 'monthly_fee',
        label: 'Valor mensualidad',
        type: 'number',
        required: true,
    },
];
</script>

<template>
    <AppLayout>
        <AdminCrudView
            title="Niveles"
            description="Niveles educativos: maternal, inicial y primero de básica."
            endpoint="/api/levels"
            :columns="columns"
            :fields="fields"
            :icon="Layers"
        />
    </AppLayout>
</template>
