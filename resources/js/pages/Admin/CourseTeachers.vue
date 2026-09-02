<script setup lang="ts">
import { UserCheck } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AdminCrudView, {
  type Column,
  type Field,
  type FieldOption,
} from '@/components/AdminCrudView.vue';

const token = localStorage.getItem('token') ?? '';
const courseOptions = ref<FieldOption[]>([]);
const teacherOptions = ref<FieldOption[]>([]);

const columns: Column[] = [
  { key: 'id', label: 'ID' },
  { key: 'course.level.name', label: 'Nivel' },
  { key: 'course.parallel', label: 'Paralelo' },
  { key: 'teacher.last_name', label: 'Docente' },
  { key: 'assigned_role', label: 'Rol' },
];

const fields = ref<Field[]>([
  {
    name: 'course_id',
    label: 'Curso',
    type: 'select',
    required: true,
    options: [],
  },
  {
    name: 'teacher_id',
    label: 'Docente',
    type: 'select',
    required: true,
    options: [],
  },
  {
    name: 'assigned_role',
    label: 'Rol asignado',
    type: 'select',
    required: true,
    options: [
      { value: 'principal', label: 'Principal' },
      { value: 'auxiliary', label: 'Auxiliar' },
    ],
  },
]);

async function fetchOptions(): Promise<void> {
  const [coursesResponse, teachersResponse] = await Promise.all([
    fetch('/api/courses?per_page=1000', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    }),
    fetch('/api/teachers?per_page=1000', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    }),
  ]);

  if (coursesResponse.ok) {
    const courses = (await coursesResponse.json()) as {
      data: { id: number; level: { name: string }; parallel: string }[];
    };

    courseOptions.value = courses.data.map((c) => ({
      value: c.id,
      label: `${c.level.name} - Paralelo ${c.parallel}`,
    }));

    fields.value[0].options = courseOptions.value;
  }

  if (teachersResponse.ok) {
    const teachers = (await teachersResponse.json()) as {
      data: { id: number; first_name: string; last_name: string }[];
    };

    teacherOptions.value = teachers.data.map((t) => ({
      value: t.id,
      label: `${t.first_name} ${t.last_name}`,
    }));

    fields.value[1].options = teacherOptions.value;
  }
}

onMounted(() => {
  void fetchOptions();
});
</script>

<template>
  <AppLayout>
    <AdminCrudView
      title="Asignaciones"
      description="Asignación de docentes principales y auxiliares a cursos."
      endpoint="/api/course-teachers"
      :columns="columns"
      :fields="fields"
      :icon="UserCheck"
    />
  </AppLayout>
</template>
