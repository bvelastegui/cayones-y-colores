<script setup lang="ts">
import { CheckCircle2, ChevronLeft, Users } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
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

interface Course {
    id: number;
    parallel: string;
    level: { name: string; enrollment_fee: number; max_capacity: number };
    active_count: number;
}

interface Student {
    id: number;
    first_name: string;
    last_name: string;
}

const router = useRouter();
const route = useRoute();
const studentId = Number(route.params.studentId);

const student = ref<Student | null>(null);
const courses = ref<Course[]>([]);
const loading = ref(false);
const enrolling = ref(false);
const error = ref('');
const success = ref(false);

const token = localStorage.getItem('token') ?? '';

async function fetchData(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        const [studentsResponse, coursesResponse] = await Promise.all([
            fetch('/api/me/students', {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json',
                },
            }),
            fetch(`/api/me/students/${studentId}/courses`, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json',
                },
            }),
        ]);

        if (!studentsResponse.ok || !coursesResponse.ok) {
            throw new Error('Error al cargar la información de matrícula.');
        }

        const students = (await studentsResponse.json()) as Student[];
        student.value = students.find((s) => s.id === studentId) ?? null;
        courses.value = await coursesResponse.json();
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Error desconocido.';
    } finally {
        loading.value = false;
    }
}

async function enroll(courseId: number): Promise<void> {
    enrolling.value = true;
    error.value = '';

    try {
        const response = await fetch('/api/me/enrollments', {
            method: 'POST',
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                student_id: studentId,
                course_id: courseId,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.message ?? 'No se pudo completar la matrícula.',
            );
        }

        success.value = true;
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Error desconocido.';
    } finally {
        enrolling.value = false;
    }
}

function isFull(course: Course): boolean {
    return course.active_count >= course.level.max_capacity;
}

onMounted(() => {
    void fetchData();
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <Button
                variant="ghost"
                class="-ml-3"
                @click="router.push('/parent')"
            >
                <ChevronLeft class="size-4" data-icon="inline-start" />
                Volver a mis hijos
            </Button>

            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Matrícula online
                </h1>
                <p class="text-muted-foreground">
                    Selecciona el paralelo para
                    <span class="text-foreground font-medium">
                        {{ student?.first_name }} {{ student?.last_name }}
                    </span>
                </p>
            </div>

            <p v-if="error" class="text-destructive text-sm">{{ error }}</p>
            <p v-if="loading" class="text-muted-foreground">Cargando...</p>

            <div
                v-if="success"
                class="bg-card flex flex-col items-center gap-4 rounded-2xl border py-12 text-center"
            >
                <CheckCircle2 class="text-primary size-12" />
                <p class="text-lg font-medium">¡Matrícula completada!</p>
                <p class="text-muted-foreground text-sm">
                    Se generó la obligación de pago correspondiente.
                </p>
                <Button @click="router.push('/parent')">Ver mis hijos</Button>
            </div>

            <div v-else-if="!loading" class="grid gap-4 sm:grid-cols-2">
                <Card
                    v-for="course in courses"
                    :key="course.id"
                    class="border-none shadow-sm"
                    :class="{ 'opacity-60': isFull(course) }"
                >
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div>
                                <CardTitle
                                    >Paralelo {{ course.parallel }}</CardTitle
                                >
                                <CardDescription>{{
                                    course.level.name
                                }}</CardDescription>
                            </div>
                            <Badge
                                :variant="
                                    isFull(course) ? 'destructive' : 'default'
                                "
                            >
                                {{ isFull(course) ? 'Lleno' : 'Disponible' }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div
                            class="text-muted-foreground flex items-center gap-2 text-sm"
                        >
                            <Users class="size-4" />
                            <span>
                                {{ course.active_count }} /
                                {{ course.level.max_capacity }} estudiantes
                            </span>
                        </div>
                        <div class="text-sm">
                            Valor de matrícula:
                            <span class="text-foreground font-semibold">
                                ${{
                                    Number(course.level.enrollment_fee).toFixed(
                                        2,
                                    )
                                }}
                            </span>
                        </div>
                        <Button
                            class="w-full"
                            :disabled="isFull(course) || enrolling"
                            @click="enroll(course.id)"
                        >
                            <Spinner
                                v-if="enrolling"
                                data-icon="inline-start"
                            />
                            {{
                                enrolling
                                    ? 'Procesando...'
                                    : 'Seleccionar paralelo'
                            }}
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
