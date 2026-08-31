<script setup lang="ts">
import {
    AlertCircle,
    BookOpen,
    CreditCard,
    GraduationCap,
    UserPlus,
    Users,
} from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
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
import { useCurrentStudent } from '@/composables/currentStudent';

interface Enrollment {
    status: string;
    course?: { level?: { name: string } } | null;
}

interface Tuition {
    id: number;
    student_id: number;
    amount: string;
    due_date: string;
    status: 'pending' | 'partial' | 'paid' | 'overdue';
    student: { first_name: string; last_name: string };
}

interface Student {
    id: number;
    first_name: string;
    last_name: string;
    birth_date: string;
    admission?: { level?: { name: string } } | null;
    enrollments?: Enrollment[];
}

const router = useRouter();
const { selectedStudent, setCurrentStudent } = useCurrentStudent();

const students = ref<Student[]>([]);
const tuitions = ref<Tuition[]>([]);
const loading = ref(false);
const error = ref('');

const token = localStorage.getItem('token') ?? '';

const selectedRecord = computed<Student | undefined>(() =>
    students.value.find((student) => student.id === selectedStudent.value?.id),
);

const isSelectedActive = computed(
    () =>
        selectedRecord.value?.enrollments?.some(
            (enrollment) => enrollment.status === 'active',
        ) ?? false,
);

const pendingTuitions = computed(() =>
    tuitions.value.filter(
        (tuition) =>
            tuition.status === 'pending' ||
            tuition.status === 'partial' ||
            tuition.status === 'overdue',
    ),
);

const dueSoonTuitions = computed(() =>
    pendingTuitions.value.filter((tuition) => {
        const due = new Date(tuition.due_date);
        const today = new Date();
        const diff = due.getTime() - today.getTime();

        return diff >= 0 && diff <= 3 * 24 * 60 * 60 * 1000;
    }),
);

async function fetchStudents(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        const [studentsResponse, tuitionsResponse] = await Promise.all([
            fetch('/api/me/students', {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json',
                },
            }),
            fetch('/api/me/tuitions', {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json',
                },
            }),
        ]);

        if (!studentsResponse.ok || !tuitionsResponse.ok) {
            throw new Error('Error al cargar los datos.');
        }

        students.value = await studentsResponse.json();
        tuitions.value = await tuitionsResponse.json();

        if (students.value.length > 0 && !selectedStudent.value) {
            const first = students.value[0];

            setCurrentStudent({
                id: first.id,
                first_name: first.first_name,
                last_name: first.last_name,
                course_name:
                    first.enrollments?.find(
                        (enrollment) => enrollment.status === 'active',
                    )?.course?.level?.name ?? null,
            });
        }
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Error desconocido.';
    } finally {
        loading.value = false;
    }
}

function isActive(student: Student): boolean {
    return (
        student.enrollments?.some(
            (enrollment) => enrollment.status === 'active',
        ) ?? false
    );
}

function selectStudent(student: Student): void {
    setCurrentStudent({
        id: student.id,
        first_name: student.first_name,
        last_name: student.last_name,
        course_name:
            student.enrollments?.find(
                (enrollment) => enrollment.status === 'active',
            )?.course?.level?.name ?? null,
    });
}

function enrollSelected(): void {
    if (!selectedStudent.value) {
        return;
    }

    router.push(`/parent/enroll/${selectedStudent.value.id}`);
}

onMounted(() => {
    void fetchStudents();
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Portal de Padres
                </h1>
                <p class="text-muted-foreground">
                    Gestiona la información, matrícula y pagos de tus hijos.
                </p>
            </div>

            <p v-if="error" class="text-destructive text-sm">{{ error }}</p>
            <p v-if="loading" class="text-muted-foreground">Cargando...</p>

            <div
                v-if="!loading && students.length === 0"
                class="text-muted-foreground text-center"
            >
                No tienes estudiantes registrados. Si completaste una admisión,
                espera la aprobación.
            </div>

            <Card
                v-if="dueSoonTuitions.length > 0 && !loading"
                class="border-destructive/20 bg-destructive/5"
            >
                <CardContent class="flex items-start gap-3 py-4">
                    <AlertCircle class="text-destructive mt-0.5 size-5" />
                    <div>
                        <p class="font-medium">Pensiones próximas a vencer</p>
                        <p class="text-muted-foreground text-sm">
                            Tienes {{ dueSoonTuitions.length }} obligación(es)
                            con fecha límite cercana.
                        </p>
                        <Button
                            variant="link"
                            class="px-0"
                            @click="router.push('/parent/payments')"
                        >
                            Ir a pagos
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <Card
                v-if="selectedStudent && !loading"
                class="border-primary/20 bg-primary/5"
            >
                <CardHeader>
                    <div class="flex items-start justify-between">
                        <div>
                            <CardTitle>
                                {{ selectedStudent.first_name }}
                                {{ selectedStudent.last_name }}
                            </CardTitle>
                            <CardDescription>
                                Estudiante seleccionado ·
                                {{
                                    selectedStudent.course_name ??
                                    'Sin curso asignado'
                                }}
                            </CardDescription>
                        </div>
                        <Badge
                            :variant="
                                isSelectedActive ? 'default' : 'secondary'
                            "
                        >
                            {{
                                isSelectedActive
                                    ? 'Matriculado'
                                    : 'Pendiente de matrícula'
                            }}
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-2">
                        <Button
                            v-if="!isSelectedActive"
                            @click="enrollSelected"
                        >
                            <UserPlus class="size-4" data-icon="inline-start" />
                            Matricular
                        </Button>
                        <Button v-else variant="outline" disabled>
                            <GraduationCap
                                class="size-4"
                                data-icon="inline-start"
                            />
                            Ver matrícula
                        </Button>
                        <Button
                            variant="outline"
                            @click="router.push('/parent/reports')"
                        >
                            <BookOpen class="size-4" data-icon="inline-start" />
                            Informes
                        </Button>
                        <Button
                            variant="outline"
                            @click="router.push('/parent/payments')"
                        >
                            <CreditCard
                                class="size-4"
                                data-icon="inline-start"
                            />
                            Pensiones
                        </Button>
                        <Button variant="outline" disabled>
                            <Users class="size-4" data-icon="inline-start" />
                            Asistencia
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <div v-if="students.length > 1 && !loading">
                <h2 class="text-muted-foreground text-sm font-semibold">
                    Todos mis hijos
                </h2>
                <div class="grid gap-4 pt-3 sm:grid-cols-2">
                    <Card
                        v-for="student in students"
                        :key="student.id"
                        class="cursor-pointer border-none shadow-sm transition-shadow hover:shadow-md"
                        :class="{
                            'ring-primary ring-2':
                                student.id === selectedStudent?.id,
                        }"
                        @click="selectStudent(student)"
                    >
                        <CardHeader>
                            <div class="flex items-start justify-between">
                                <div>
                                    <CardTitle
                                        >{{ student.first_name }}
                                        {{ student.last_name }}</CardTitle
                                    >
                                    <CardDescription>
                                        Nivel de interés:
                                        {{
                                            student.admission?.level?.name ??
                                            'Por definir'
                                        }}
                                    </CardDescription>
                                </div>
                                <Badge
                                    :variant="
                                        isActive(student)
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        isActive(student)
                                            ? 'Matriculado'
                                            : 'Pendiente de matrícula'
                                    }}
                                </Badge>
                            </div>
                        </CardHeader>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
