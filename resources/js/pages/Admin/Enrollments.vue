<script setup lang="ts">
import { ClipboardCheck, Settings2 } from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';

interface Enrollment {
  id: number;
  status: string;
  assignment_issue: string | null;
  exception_until: string | null;
  student: { first_name: string; last_name: string };
  academic_period: { name: string } | null;
  level: { id: number; name: string } | null;
  course: { id: number; parallel: string } | null;
}

interface Course {
  id: number;
  level_id: number;
  parallel: string;
  level: { name: string };
}

const token = localStorage.getItem('token') ?? '';
const enrollments = ref<Enrollment[]>([]);
const courses = ref<Course[]>([]);
const loading = ref(false);
const saving = ref(false);
const error = ref('');
const success = ref('');
const dialogOpen = ref(false);
const selected = ref<Enrollment | null>(null);
const action = ref<'assign' | 'outcome' | 'exception'>('assign');
const courseId = ref('');
const outcome = ref<'completed' | 'not_completed'>('completed');
const exceptionUntil = ref('');
const reason = ref('');

const eligibleCourses = computed(() =>
  courses.value.filter(
    (course) => course.level_id === selected.value?.level?.id,
  ),
);

function headers(): HeadersInit {
  return {
    Authorization: `Bearer ${token}`,
    Accept: 'application/json',
    'Content-Type': 'application/json',
  };
}

async function fetchData(): Promise<void> {
  loading.value = true;
  error.value = '';

  try {
    const [enrollmentsResponse, coursesResponse] = await Promise.all([
      fetch('/api/enrollments?per_page=100', { headers: headers() }),
      fetch('/api/courses?per_page=1000', { headers: headers() }),
    ]);

    if (!enrollmentsResponse.ok || !coursesResponse.ok) {
      throw new Error('No se pudo cargar la gestión de matrículas.');
    }

    enrollments.value = (
      (await enrollmentsResponse.json()) as { data: Enrollment[] }
    ).data;
    courses.value = ((await coursesResponse.json()) as { data: Course[] }).data;
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    loading.value = false;
  }
}

function openAction(
  enrollment: Enrollment,
  nextAction: typeof action.value,
): void {
  selected.value = enrollment;
  action.value = nextAction;
  courseId.value = enrollment.course ? String(enrollment.course.id) : '';
  outcome.value = 'completed';
  exceptionUntil.value = '';
  reason.value = '';
  dialogOpen.value = true;
}

async function submitAction(): Promise<void> {
  if (!selected.value) {
    return;
  }

  saving.value = true;
  error.value = '';
  success.value = '';

  const configuration = {
    assign: {
      url: `/api/enrollments/${selected.value.id}/assignment`,
      payload: { course_id: Number(courseId.value), reason: reason.value },
    },
    outcome: {
      url: `/api/enrollments/${selected.value.id}/outcome`,
      payload: { outcome: outcome.value, reason: reason.value || undefined },
    },
    exception: {
      url: `/api/enrollments/${selected.value.id}/exception`,
      payload: { exception_until: exceptionUntil.value, reason: reason.value },
    },
  }[action.value];

  try {
    const response = await fetch(configuration.url, {
      method: 'PUT',
      headers: headers(),
      body: JSON.stringify(configuration.payload),
    });
    const data = (await response.json()) as { message?: string };

    if (!response.ok) {
      throw new Error(data.message ?? 'No se pudo aplicar la acción.');
    }

    success.value = 'La matrícula fue actualizada correctamente.';
    dialogOpen.value = false;
    await fetchData();
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    saving.value = false;
  }
}

function statusLabel(status: string): string {
  return (
    {
      draft: 'Borrador',
      pending_payment: 'Pendiente de pago',
      payment_in_progress: 'Pago en proceso',
      paid_pending_assignment: 'Pendiente de asignación',
      active: 'Activa',
      finalized: 'Finalizada',
      withdrawn: 'Retirada',
      graduated: 'Graduada',
    }[status] ?? status
  );
}

onMounted(() => void fetchData());
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">
          <ClipboardCheck class="mr-2 inline size-6" />Matrículas
        </h1>
        <p class="text-muted-foreground">
          Gestiona incidencias, excepciones, asignaciones y resultados de nivel.
        </p>
      </div>
      <p
        v-if="error"
        class="text-destructive text-sm"
      >
        {{ error }}
      </p>
      <p
        v-if="success"
        class="text-sm text-emerald-700"
      >
        {{ success }}
      </p>

      <Card>
        <CardHeader
          ><CardTitle>Procesos registrados</CardTitle
          ><CardDescription
            >Las asignaciones manuales y promociones dejan
            trazabilidad.</CardDescription
          ></CardHeader
        >
        <CardContent class="p-0">
          <Table>
            <TableHeader
              ><TableRow
                ><TableHead>Estudiante</TableHead><TableHead>Periodo</TableHead
                ><TableHead>Nivel</TableHead><TableHead>Paralelo</TableHead
                ><TableHead>Estado</TableHead
                ><TableHead class="text-right">Acciones</TableHead></TableRow
              ></TableHeader
            >
            <TableBody>
              <TableRow v-if="loading"
                ><TableCell
                  colspan="6"
                  class="text-center"
                  >Cargando…</TableCell
                ></TableRow
              >
              <TableRow v-else-if="enrollments.length === 0"
                ><TableCell
                  colspan="6"
                  class="text-muted-foreground text-center"
                  >No hay matrículas.</TableCell
                ></TableRow
              >
              <TableRow
                v-for="enrollment in enrollments"
                :key="enrollment.id"
              >
                <TableCell
                  >{{ enrollment.student.first_name }}
                  {{ enrollment.student.last_name }}</TableCell
                >
                <TableCell>{{
                  enrollment.academic_period?.name ?? 'Anterior'
                }}</TableCell>
                <TableCell>{{
                  enrollment.level?.name ?? 'Sin nivel'
                }}</TableCell>
                <TableCell>{{
                  enrollment.course?.parallel ?? 'Sin asignar'
                }}</TableCell>
                <TableCell
                  ><Badge
                    :variant="
                      enrollment.assignment_issue ? 'destructive' : 'secondary'
                    "
                    >{{
                      enrollment.assignment_issue
                        ? 'Sin cupo'
                        : statusLabel(enrollment.status)
                    }}</Badge
                  ></TableCell
                >
                <TableCell class="space-x-2 text-right">
                  <Button
                    v-if="
                      ['paid_pending_assignment', 'active'].includes(
                        enrollment.status,
                      )
                    "
                    size="sm"
                    variant="outline"
                    @click="openAction(enrollment, 'assign')"
                    >{{
                      enrollment.status === 'active' ? 'Reasignar' : 'Asignar'
                    }}</Button
                  >
                  <Button
                    v-if="enrollment.status === 'active'"
                    size="sm"
                    variant="outline"
                    @click="openAction(enrollment, 'outcome')"
                    >Resultado</Button
                  >
                  <Button
                    v-if="
                      ['draft', 'pending_payment'].includes(enrollment.status)
                    "
                    size="sm"
                    variant="outline"
                    @click="openAction(enrollment, 'exception')"
                    ><Settings2 class="size-4" />Excepción</Button
                  >
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <Dialog v-model:open="dialogOpen">
        <DialogContent class="sm:max-w-lg">
          <DialogHeader
            ><DialogTitle>Actualizar matrícula</DialogTitle
            ><DialogDescription
              >{{ selected?.student.first_name }}
              {{ selected?.student.last_name }} ·
              {{ selected?.level?.name }}</DialogDescription
            ></DialogHeader
          >
          <div class="space-y-4">
            <div v-if="action === 'assign'">
              <Label>Paralelo</Label
              ><Select v-model="courseId"
                ><SelectTrigger
                  ><SelectValue
                    placeholder="Selecciona un paralelo" /></SelectTrigger
                ><SelectContent
                  ><SelectItem
                    v-for="course in eligibleCourses"
                    :key="course.id"
                    :value="String(course.id)"
                    >{{ course.level.name }} · {{ course.parallel }}</SelectItem
                  ></SelectContent
                ></Select
              >
            </div>
            <div v-if="action === 'outcome'">
              <Label>Resultado del nivel</Label
              ><Select v-model="outcome"
                ><SelectTrigger><SelectValue /></SelectTrigger
                ><SelectContent
                  ><SelectItem value="completed"
                    >Completado · promover</SelectItem
                  ><SelectItem value="not_completed"
                    >No completado · mantener nivel</SelectItem
                  ></SelectContent
                ></Select
              >
            </div>
            <div v-if="action === 'exception'">
              <Label>Nueva fecha límite</Label
              ><Input
                v-model="exceptionUntil"
                type="datetime-local"
              />
            </div>
            <div>
              <Label>Motivo</Label
              ><Textarea
                v-model="reason"
                placeholder="Describe el motivo para la auditoría"
              />
            </div>
          </div>
          <DialogFooter
            ><Button
              variant="outline"
              @click="dialogOpen = false"
              >Cancelar</Button
            ><Button
              :disabled="
                saving ||
                !reason ||
                (action === 'assign' && !courseId) ||
                (action === 'exception' && !exceptionUntil)
              "
              @click="submitAction"
              >{{ saving ? 'Guardando…' : 'Confirmar' }}</Button
            ></DialogFooter
          >
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
