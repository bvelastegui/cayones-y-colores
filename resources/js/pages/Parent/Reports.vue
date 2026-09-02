<script setup lang="ts">
import { BookOpen } from '@lucide/vue';
import { computed, onMounted, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { useCurrentStudent } from '@/composables/currentStudent';

interface Report {
  id: number;
  development_area: string;
  evaluated_skill: string;
  achievement_level: string;
  observations: string | null;
  created_at: string;
  teacher: { first_name: string; last_name: string };
}

const token = localStorage.getItem('token') ?? '';
const { selectedStudent } = useCurrentStudent();

const reports = ref<Report[]>([]);
const loading = ref(false);
const error = ref('');

const studentLabel = computed(() => {
  if (!selectedStudent.value) {
    return '';
  }

  return `${selectedStudent.value.first_name} ${selectedStudent.value.last_name}`;
});

async function fetchReports(): Promise<void> {
  if (!selectedStudent.value) {
    reports.value = [];
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    const response = await fetch(
      `/api/me/students/${selectedStudent.value.id}/reports`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
      },
    );

    if (!response.ok) {
      throw new Error('Error al cargar los informes.');
    }

    reports.value = await response.json();
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    loading.value = false;
  }
}

function statusVariant(
  level: string,
): 'default' | 'secondary' | 'outline' | 'destructive' {
  switch (level) {
    case 'A':
      return 'default';
    case 'EP':
      return 'secondary';
    case 'I':
      return 'outline';
    default:
      return 'destructive';
  }
}

function levelLabel(level: string): string {
  switch (level) {
    case 'A':
      return 'Destreza Alcanzada (A)';
    case 'EP':
      return 'En Proceso (EP)';
    case 'I':
      return 'Iniciado (I)';
    case 'NE':
      return 'No Evaluado (NE)';
    default:
      return level;
  }
}

watch(
  () => selectedStudent.value?.id,
  () => {
    void fetchReports();
  },
);

onMounted(() => {
  void fetchReports();
});
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">
          <BookOpen class="mr-2 inline size-6" />
          Informes de avance
        </h1>
        <p class="text-muted-foreground">
          Consulta el progreso académico de
          <span
            v-if="selectedStudent"
            class="text-foreground font-medium"
          >
            {{ studentLabel }}
          </span>
          <span v-else>tu hijo</span>
          .
        </p>
      </div>

      <p
        v-if="error"
        class="text-destructive text-sm"
      >
        {{ error }}
      </p>
      <p
        v-if="loading"
        class="text-muted-foreground"
      >
        Cargando...
      </p>

      <div
        v-if="!loading && reports.length === 0"
        class="text-muted-foreground text-center"
      >
        No hay informes registrados para este estudiante.
      </div>

      <div class="space-y-4">
        <Card
          v-for="report in reports"
          :key="report.id"
          class="border-none shadow-sm"
        >
          <CardHeader>
            <div class="flex items-start justify-between">
              <div>
                <CardTitle class="text-base">
                  {{ report.development_area }}
                </CardTitle>
                <CardDescription>
                  {{ report.evaluated_skill }}
                </CardDescription>
              </div>
              <Badge :variant="statusVariant(report.achievement_level)">
                {{ levelLabel(report.achievement_level) }}
              </Badge>
            </div>
          </CardHeader>
          <CardContent class="space-y-2">
            <p
              v-if="report.observations"
              class="text-muted-foreground text-sm"
            >
              {{ report.observations }}
            </p>
            <div class="text-muted-foreground text-xs">
              Registrado por {{ report.teacher.first_name }}
              {{ report.teacher.last_name }} el
              {{ new Date(report.created_at).toLocaleDateString() }}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
