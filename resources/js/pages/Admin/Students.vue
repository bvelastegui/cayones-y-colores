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
const levelOptions = ref<FieldOption[]>([]);

const columns: Column[] = [
  { key: 'id', label: 'ID' },
  { key: 'id_card', label: 'Cédula' },
  { key: 'first_name', label: 'Nombres' },
  { key: 'last_name', label: 'Apellidos' },
  { key: 'level.name', label: 'Nivel' },
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
  {
    name: 'level_id',
    label: 'Nivel asignado',
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

async function fetchOptions(): Promise<void> {
  const [representativesResponse, levelsResponse] = await Promise.all([
    fetch('/api/representatives?per_page=1000', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    }),
    fetch('/api/levels?per_page=1000', {
      headers: { Accept: 'application/json' },
    }),
  ]);

  if (!representativesResponse.ok || !levelsResponse.ok) {
    return;
  }

  const data = (await representativesResponse.json()) as {
    data: { id: number; first_name: string; last_name: string }[];
  };
  const levels = (await levelsResponse.json()) as {
    data: { id: number; name: string }[];
  };

  representativeOptions.value = data.data.map((r) => ({
    value: r.id,
    label: `${r.first_name} ${r.last_name}`,
  }));
  levelOptions.value = levels.data.map((level) => ({
    value: level.id,
    label: level.name,
  }));

  fields.value[0].options = representativeOptions.value;
  fields.value[1].options = levelOptions.value;
}

onMounted(() => {
  void fetchOptions();
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
