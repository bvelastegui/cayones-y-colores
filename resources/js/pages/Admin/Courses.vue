<script setup lang="ts">
import { BookOpen } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AdminCrudView, {
  type Column,
  type Field,
  type FieldOption,
} from '@/components/AdminCrudView.vue';

const token = localStorage.getItem('token') ?? '';
const levelOptions = ref<FieldOption[]>([]);

const columns: Column[] = [
  { key: 'id', label: 'ID' },
  { key: 'level.name', label: 'Nivel' },
  { key: 'parallel', label: 'Paralelo' },
];

const fields = ref<Field[]>([
  {
    name: 'level_id',
    label: 'Nivel',
    type: 'select',
    required: true,
    options: [],
  },
  { name: 'parallel', label: 'Paralelo', type: 'text', required: true },
]);

async function fetchLevels(): Promise<void> {
  const response = await fetch('/api/levels', {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    return;
  }

  const data = (await response.json()) as {
    data: { id: number; name: string }[];
  };

  levelOptions.value = data.data.map((level) => ({
    value: level.id,
    label: level.name,
  }));

  fields.value[0].options = levelOptions.value;
}

onMounted(() => {
  void fetchLevels();
});
</script>

<template>
  <AppLayout>
    <AdminCrudView
      title="Cursos"
      description="Paralelos disponibles por nivel."
      endpoint="/api/courses"
      :columns="columns"
      :fields="fields"
      :icon="BookOpen"
    />
  </AppLayout>
</template>
