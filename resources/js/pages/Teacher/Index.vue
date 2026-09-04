<script setup lang="ts">
import { BookOpen, GraduationCap, Plus, Stethoscope } from '@lucide/vue';
import { computed, onMounted, reactive, ref } from 'vue';
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
  DialogTrigger,
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

interface Course {
  id: number;
  parallel: string;
  level: { name: string };
  active_students_count: number;
}

interface Student {
  id: number;
  first_name: string;
  last_name: string;
  pivot: { status: string };
}

interface Report {
  id: number;
  student: { first_name: string; last_name: string };
  development_area: string;
  evaluated_skill: string;
  achievement_level: string;
  created_at: string;
}

interface CareProfile {
  student: { id: number; full_name: string; preferred_name: string | null };
  health: Record<string, string | null> | null;
  medical_conditions: Record<string, string | null>[];
  allergies: Record<string, string | null>[];
  medications: Record<string, string | null>[];
  health_insurance: Record<string, string | boolean | null> | null;
  emergency_contacts: Record<string, string | boolean | number | null>[];
}

const token = localStorage.getItem('token') ?? '';

const courses = ref<Course[]>([]);
const selectedCourse = ref<Course | null>(null);
const students = ref<Student[]>([]);
const reports = ref<Report[]>([]);
const loading = ref(false);
const saving = ref(false);
const error = ref('');
const formError = ref('');
const dialogOpen = ref(false);
const careDialogOpen = ref(false);
const careLoading = ref(false);
const careProfile = ref<CareProfile | null>(null);

const form = reactive({
  student_id: '',
  course_id: '',
  development_area: '',
  evaluated_skill: '',
  achievement_level: '',
  observations: '',
});

const developmentAreas = [
  'Relaciones lógico-matemáticas',
  'Comunicación y lenguaje',
  'Exploración del entorno natural y cultural',
  'Desarrollo personal y social',
  'Expresión corporal',
  'Expresión artística',
];

const achievementLevels = [
  { value: 'A', label: 'Destreza Alcanzada (A)' },
  { value: 'EP', label: 'En Proceso (EP)' },
  { value: 'I', label: 'Iniciado (I)' },
  { value: 'NE', label: 'No Evaluado (NE)' },
];

const selectedCourseLabel = computed(() => {
  if (!selectedCourse.value) {
    return '';
  }

  return `${selectedCourse.value.level.name} - Paralelo ${selectedCourse.value.parallel}`;
});

async function fetchCourses(): Promise<void> {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetch('/api/teacher/courses', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Error al cargar los cursos.');
    }

    courses.value = await response.json();
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    loading.value = false;
  }
}

async function selectCourse(course: Course): Promise<void> {
  selectedCourse.value = course;
  students.value = [];

  try {
    const response = await fetch(`/api/teacher/courses/${course.id}/students`, {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Error al cargar los estudiantes.');
    }

    students.value = await response.json();
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  }
}

async function fetchReports(): Promise<void> {
  try {
    const response = await fetch('/api/teacher/reports?per_page=10', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Error al cargar los informes.');
    }

    const data = (await response.json()) as { data: Report[] };
    reports.value = data.data;
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  }
}

function openReport(student: Student): void {
  form.student_id = String(student.id);
  form.course_id = String(selectedCourse.value?.id ?? '');
  form.development_area = '';
  form.evaluated_skill = '';
  form.achievement_level = '';
  form.observations = '';
  formError.value = '';
  dialogOpen.value = true;
}

async function submitReport(): Promise<void> {
  saving.value = true;
  formError.value = '';

  try {
    const response = await fetch('/api/teacher/reports', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({
        ...form,
        student_id: Number(form.student_id),
        course_id: Number(form.course_id),
      }),
    });

    const data = (await response.json()) as Record<string, unknown>;

    if (!response.ok) {
      throw new Error(
        (data.message as string) ?? 'No se pudo guardar el informe.',
      );
    }

    dialogOpen.value = false;
    await fetchReports();
  } catch (exception) {
    formError.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    saving.value = false;
  }
}

async function openCareProfile(student: Student): Promise<void> {
  careDialogOpen.value = true;
  careLoading.value = true;
  careProfile.value = null;
  error.value = '';

  try {
    const response = await fetch(
      `/api/teacher/students/${student.id}/care-profile`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
      },
    );

    if (!response.ok) {
      throw new Error('No tienes acceso a esta ficha de cuidado.');
    }

    careProfile.value = await response.json();
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
    careDialogOpen.value = false;
  } finally {
    careLoading.value = false;
  }
}

function levelBadge(
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

onMounted(() => {
  void fetchCourses();
  void fetchReports();
});
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">
          <GraduationCap class="mr-2 inline size-6" />
          Módulo Docente
        </h1>
        <p class="text-muted-foreground">
          Registra el avance académico de tus estudiantes asignados.
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

      <div>
        <h2 class="text-muted-foreground text-sm font-semibold">Mis cursos</h2>
        <div class="grid gap-4 pt-3 sm:grid-cols-2 lg:grid-cols-3">
          <Card
            v-for="course in courses"
            :key="course.id"
            class="cursor-pointer border-none shadow-sm transition-shadow hover:shadow-md"
            :class="{
              'ring-primary ring-2': selectedCourse?.id === course.id,
            }"
            @click="selectCourse(course)"
          >
            <CardHeader>
              <CardTitle>
                <BookOpen class="mr-2 inline size-5" />
                {{ course.level.name }}
              </CardTitle>
              <CardDescription>
                Paralelo {{ course.parallel }}
              </CardDescription>
            </CardHeader>
            <CardContent>
              <p class="text-sm">
                {{ course.active_students_count }} estudiantes matriculados
              </p>
            </CardContent>
          </Card>
        </div>
      </div>

      <Card v-if="selectedCourse">
        <CardHeader>
          <CardTitle>{{ selectedCourseLabel }}</CardTitle>
          <CardDescription>
            Selecciona un estudiante para registrar su informe de avance.
          </CardDescription>
        </CardHeader>
        <CardContent class="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Estudiante</TableHead>
                <TableHead class="text-right">Acciones</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="students.length === 0">
                <TableCell
                  colspan="2"
                  class="text-muted-foreground text-center"
                >
                  No hay estudiantes activos en este curso.
                </TableCell>
              </TableRow>
              <TableRow
                v-for="student in students"
                :key="student.id"
              >
                <TableCell>
                  {{ student.first_name }}
                  {{ student.last_name }}
                </TableCell>
                <TableCell class="text-right">
                  <Button
                    variant="outline"
                    class="mr-2"
                    @click="openCareProfile(student)"
                  >
                    <Stethoscope class="size-4" />
                    Ficha de cuidado
                  </Button>
                  <Dialog v-model:open="dialogOpen">
                    <DialogTrigger as-child>
                      <Button @click="openReport(student)">
                        <Plus
                          class="size-4"
                          data-icon="inline-start"
                        />
                        Informe
                      </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-lg">
                      <DialogHeader>
                        <DialogTitle> Registrar informe de avance </DialogTitle>
                        <DialogDescription>
                          {{ student.first_name }}
                          {{ student.last_name }} —
                          {{ selectedCourseLabel }}
                        </DialogDescription>
                      </DialogHeader>

                      <p
                        v-if="formError"
                        class="text-destructive text-sm"
                      >
                        {{ formError }}
                      </p>

                      <div class="grid gap-4 py-2">
                        <div class="grid gap-2">
                          <Label for="development_area">
                            Ámbito de desarrollo
                          </Label>
                          <Select
                            id="development_area"
                            v-model="form.development_area"
                          >
                            <SelectTrigger>
                              <SelectValue placeholder="Selecciona un ámbito" />
                            </SelectTrigger>
                            <SelectContent>
                              <SelectItem
                                v-for="area in developmentAreas"
                                :key="area"
                                :value="area"
                              >
                                {{ area }}
                              </SelectItem>
                            </SelectContent>
                          </Select>
                        </div>

                        <div class="grid gap-2">
                          <Label for="evaluated_skill">
                            Destreza evaluada
                          </Label>
                          <Input
                            id="evaluated_skill"
                            v-model="form.evaluated_skill"
                            placeholder="Ej. Reconocer figuras geométricas"
                          />
                        </div>

                        <div class="grid gap-2">
                          <Label for="achievement_level">
                            Nivel de logro
                          </Label>
                          <Select
                            id="achievement_level"
                            v-model="form.achievement_level"
                          >
                            <SelectTrigger>
                              <SelectValue placeholder="Selecciona el nivel" />
                            </SelectTrigger>
                            <SelectContent>
                              <SelectItem
                                v-for="level in achievementLevels"
                                :key="level.value"
                                :value="level.value"
                              >
                                {{ level.label }}
                              </SelectItem>
                            </SelectContent>
                          </Select>
                        </div>

                        <div class="grid gap-2">
                          <Label for="observations"> Observaciones </Label>
                          <Textarea
                            id="observations"
                            v-model="form.observations"
                            placeholder="Describe el avance observado"
                          />
                        </div>
                      </div>

                      <DialogFooter>
                        <Button
                          variant="outline"
                          @click="dialogOpen = false"
                        >
                          Cancelar
                        </Button>
                        <Button
                          :disabled="saving"
                          @click="submitReport"
                        >
                          {{ saving ? 'Guardando...' : 'Guardar informe' }}
                        </Button>
                      </DialogFooter>
                    </DialogContent>
                  </Dialog>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <Dialog v-model:open="careDialogOpen">
        <DialogContent class="max-h-[85vh] overflow-y-auto sm:max-w-2xl">
          <DialogHeader>
            <DialogTitle>Ficha de cuidado</DialogTitle>
            <DialogDescription>
              Información limitada para la atención diaria del estudiante. Este
              acceso queda auditado.
            </DialogDescription>
          </DialogHeader>
          <p
            v-if="careLoading"
            class="text-muted-foreground text-sm"
          >
            Cargando ficha…
          </p>
          <div
            v-else-if="careProfile"
            class="space-y-5 text-sm"
          >
            <div>
              <p class="font-semibold">{{ careProfile.student.full_name }}</p>
              <p
                v-if="careProfile.student.preferred_name"
                class="text-muted-foreground"
              >
                Nombre preferido: {{ careProfile.student.preferred_name }}
              </p>
            </div>
            <section class="rounded-lg border p-4">
              <h3 class="mb-2 font-semibold">Salud e instrucciones</h3>
              <p>
                Tipo de sangre:
                {{ careProfile.health?.blood_type || 'No registrado' }}
              </p>
              <p>
                Pediatra:
                {{ careProfile.health?.pediatrician_name || 'No registrado' }}
              </p>
              <p class="mt-2 whitespace-pre-wrap">
                {{
                  careProfile.health?.care_instructions ||
                  'Sin instrucciones especiales.'
                }}
              </p>
              <p class="mt-2 whitespace-pre-wrap">
                {{ careProfile.health?.medical_observations }}
              </p>
            </section>
            <section class="grid gap-3 sm:grid-cols-3">
              <div class="rounded-lg border p-3">
                <h3 class="font-semibold">Condiciones</h3>
                <p
                  v-if="careProfile.medical_conditions.length === 0"
                  class="text-muted-foreground"
                >
                  Ninguna declarada
                </p>
                <p
                  v-for="item in careProfile.medical_conditions"
                  :key="String(item.name)"
                >
                  {{ item.name }}
                </p>
              </div>
              <div class="rounded-lg border p-3">
                <h3 class="font-semibold">Alergias</h3>
                <p
                  v-if="careProfile.allergies.length === 0"
                  class="text-muted-foreground"
                >
                  Ninguna declarada
                </p>
                <p
                  v-for="item in careProfile.allergies"
                  :key="String(item.allergen)"
                >
                  {{ item.allergen }} · {{ item.severity }}
                </p>
              </div>
              <div class="rounded-lg border p-3">
                <h3 class="font-semibold">Medicamentos</h3>
                <p
                  v-if="careProfile.medications.length === 0"
                  class="text-muted-foreground"
                >
                  Ninguno declarado
                </p>
                <p
                  v-for="item in careProfile.medications"
                  :key="String(item.name)"
                >
                  {{ item.name }} · {{ item.dose }}
                </p>
              </div>
            </section>
            <section class="rounded-lg border p-4">
              <h3 class="mb-2 font-semibold">Contactos de emergencia</h3>
              <div
                v-for="contact in careProfile.emergency_contacts"
                :key="String(contact.position)"
                class="mb-2 last:mb-0"
              >
                <p class="font-medium">
                  {{ contact.full_name }} · {{ contact.relationship }}
                </p>
                <p class="text-muted-foreground">
                  {{ contact.phone }}
                  <span v-if="contact.alternate_phone"
                    >· {{ contact.alternate_phone }}</span
                  >
                </p>
              </div>
            </section>
          </div>
        </DialogContent>
      </Dialog>

      <Card>
        <CardHeader>
          <CardTitle>Informes recientes</CardTitle>
          <CardDescription>
            Últimos avances registrados por ti.
          </CardDescription>
        </CardHeader>
        <CardContent class="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Fecha</TableHead>
                <TableHead>Estudiante</TableHead>
                <TableHead>Ámbito</TableHead>
                <TableHead>Destreza</TableHead>
                <TableHead>Nivel</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="reports.length === 0">
                <TableCell
                  colspan="5"
                  class="text-muted-foreground text-center"
                >
                  Aún no has registrado informes.
                </TableCell>
              </TableRow>
              <TableRow
                v-for="report in reports"
                :key="report.id"
              >
                <TableCell>
                  {{ new Date(report.created_at).toLocaleDateString() }}
                </TableCell>
                <TableCell>
                  {{ report.student.first_name }}
                  {{ report.student.last_name }}
                </TableCell>
                <TableCell>{{ report.development_area }}</TableCell>
                <TableCell>{{ report.evaluated_skill }}</TableCell>
                <TableCell>
                  <Badge :variant="levelBadge(report.achievement_level)">
                    {{ report.achievement_level }}
                  </Badge>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
