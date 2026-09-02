<script setup lang="ts">
import {
  AlertCircle,
  AlertTriangle,
  Baby,
  Bell,
  BookOpen,
  GraduationCap,
  LayoutDashboard,
  Users,
} from '@lucide/vue';
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';

interface Alert {
  type: 'capacity' | 'auxiliary';
  severity: 'critical' | 'warning';
  course_id: number;
  course: string;
  message: string;
}

interface Stats {
  pending_admissions: number;
  active_students: number;
  representatives: number;
  teachers: number;
  courses: number;
  pending_tuitions: number;
}

const router = useRouter();
const token = localStorage.getItem('token') ?? '';

const stats = ref<Stats | null>(null);
const alerts = ref<Alert[]>([]);
const loading = ref(false);
const generating = ref(false);
const error = ref('');

async function fetchDashboard(): Promise<void> {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetch('/api/admin/dashboard', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Error al cargar el panel de administración.');
    }

    const data = (await response.json()) as {
      stats: Stats;
      alerts: Alert[];
    };
    stats.value = data.stats;
    alerts.value = data.alerts;
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    loading.value = false;
  }
}

async function generateTuitions(): Promise<void> {
  generating.value = true;
  error.value = '';

  try {
    const response = await fetch('/api/tuitions/generate', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
    });

    const data = (await response.json()) as { created: number };

    if (!response.ok) {
      throw new Error('No se pudieron generar las pensiones.');
    }

    alert(`Se generaron ${data.created} pensiones.`);
    await fetchDashboard();
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    generating.value = false;
  }
}

onMounted(() => {
  void fetchDashboard();
});
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-start justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">
            <LayoutDashboard class="mr-2 inline size-6" />
            Panel de Administración
          </h1>
          <p class="text-muted-foreground">
            Resumen de operaciones, alertas de aforo y recaudación.
          </p>
        </div>
        <Button
          :disabled="generating"
          @click="generateTuitions"
        >
          <Spinner
            v-if="generating"
            data-icon="inline-start"
          />
          <Bell
            v-else
            class="size-4"
            data-icon="inline-start"
          />
          {{ generating ? 'Generando...' : 'Generar pensiones del mes' }}
        </Button>
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
        v-if="stats && !loading"
        class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
      >
        <Card
          class="cursor-pointer"
          @click="router.push('/admin/admissions')"
        >
          <CardHeader class="pb-2">
            <CardDescription>Admisiones pendientes</CardDescription>
            <CardTitle class="text-3xl">{{
              stats.pending_admissions
            }}</CardTitle>
          </CardHeader>
          <CardContent>
            <Bell class="text-muted-foreground size-5" />
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="pb-2">
            <CardDescription>Estudiantes matriculados</CardDescription>
            <CardTitle class="text-3xl">{{ stats.active_students }}</CardTitle>
          </CardHeader>
          <CardContent>
            <Baby class="text-muted-foreground size-5" />
          </CardContent>
        </Card>
        <Card @click="router.push('/admin/representatives')">
          <CardHeader class="pb-2">
            <CardDescription>Representantes</CardDescription>
            <CardTitle class="text-3xl">{{ stats.representatives }}</CardTitle>
          </CardHeader>
          <CardContent>
            <Users class="text-muted-foreground size-5" />
          </CardContent>
        </Card>
        <Card @click="router.push('/admin/teachers')">
          <CardHeader class="pb-2">
            <CardDescription>Docentes</CardDescription>
            <CardTitle class="text-3xl">{{ stats.teachers }}</CardTitle>
          </CardHeader>
          <CardContent>
            <GraduationCap class="text-muted-foreground size-5" />
          </CardContent>
        </Card>
        <Card @click="router.push('/admin/courses')">
          <CardHeader class="pb-2">
            <CardDescription>Cursos</CardDescription>
            <CardTitle class="text-3xl">{{ stats.courses }}</CardTitle>
          </CardHeader>
          <CardContent>
            <BookOpen class="text-muted-foreground size-5" />
          </CardContent>
        </Card>
        <Card @click="router.push('/admin/tuitions')">
          <CardHeader class="pb-2">
            <CardDescription>Pensiones pendientes</CardDescription>
            <CardTitle class="text-3xl">{{ stats.pending_tuitions }}</CardTitle>
          </CardHeader>
          <CardContent>
            <AlertTriangle class="text-muted-foreground size-5" />
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <AlertCircle class="size-5" />
            Alertas de aforo y auxiliares
          </CardTitle>
          <CardDescription>
            Reglas institucionales de asignación de personal y cupos.
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-3">
          <div
            v-if="alerts.length === 0 && !loading"
            class="text-muted-foreground"
          >
            No hay alertas activas.
          </div>
          <div
            v-for="alert in alerts"
            :key="`${alert.type}-${alert.course_id}`"
            class="flex items-start justify-between gap-4 rounded-lg border p-4"
            :class="
              alert.severity === 'critical'
                ? 'border-destructive/50 bg-destructive/5'
                : 'border-warning/50 bg-warning/5'
            "
          >
            <div>
              <p class="font-medium">{{ alert.course }}</p>
              <p class="text-muted-foreground text-sm">
                {{ alert.message }}
              </p>
            </div>
            <Badge
              :variant="
                alert.severity === 'critical' ? 'destructive' : 'default'
              "
            >
              {{ alert.severity === 'critical' ? 'Crítico' : 'Advertencia' }}
            </Badge>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
