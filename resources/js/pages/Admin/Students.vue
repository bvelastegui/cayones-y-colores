<script setup lang="ts">
import { School } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AdminCrudView, {
    type Column,
    type Field,
    type FieldOption,
} from '@/components/AdminCrudView.vue';

const token = localStorage.getItem('token') ?? '';
const representativeOptions = ref<FieldOption[]>([]);

const columns: Column[] = [
    { key: 'id', label: 'ID' },
    { key: 'id_card', label: 'Cédula' },
    { key: 'first_name', label: 'Nombres' },
    { key: 'last_name', label: 'Apellidos' },
    { key: 'representative.last_name', label: 'Representante' },
];

const fields = ref<Field[]>([
    {
        name: 'representative_id',
        label: 'Representante',
        type: 'select',
        required: true,
        options: [],
    },
    { name: 'id_card', label: 'Cédula', type: 'text', required: true },
    { name: 'first_name', label: 'Nombres', type: 'text', required: true },
    { name: 'last_name', label: 'Apellidos', type: 'text', required: true },
    {
        name: 'birth_date',
        label: 'Fecha de nacimiento',
        type: 'date',
        required: true,
    },
]);

async function fetchRepresentatives(): Promise<void> {
    const response = await fetch('/api/representatives?per_page=1000', {
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

    representativeOptions.value = data.data.map((r) => ({
        value: r.id,
        label: `${r.first_name} ${r.last_name}`,
    }));

    fields.value[0].options = representativeOptions.value;
}

onMounted(() => {
    void fetchRepresentatives();
});
</script>

<template>
    <AppLayout>
        <AdminCrudView
            title="Estudiantes"
            description="Estudiantes matriculados o en proceso de admisión."
            endpoint="/api/students"
            :columns="columns"
            :fields="fields"
            :icon="School"
        />
    </AppLayout>
</template>
