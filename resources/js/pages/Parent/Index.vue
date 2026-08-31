<script setup lang="ts">
import { BookOpen, GraduationCap, UserPlus } from '@lucide/vue';
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

interface Student {
    id: number;
    first_name: string;
    last_name: string;
    birth_date: string;
    admission?: { level?: { name: string } } | null;
    enrollments?: { status: string }[];
}

const router = useRouter();
const students = ref<Student[]>([]);
const loading = ref(false);
const error = ref('');

const token = localStorage.getItem('token') ?? '';

async function fetchStudents(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch('/api/me/students', {
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
                    Gestiona la información y matrícula de tus hijos.
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

            <div class="grid gap-4 sm:grid-cols-2">
                <Card
                    v-for="student in students"
                    :key="student.id"
                    class="border-none shadow-sm"
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
                                    isActive(student) ? 'default' : 'secondary'
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
                    <CardContent>
                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-if="!isActive(student)"
                                @click="
                                    router.push(`/parent/enroll/${student.id}`)
                                "
                            >
                                <UserPlus
                                    class="size-4"
                                    data-icon="inline-start"
                                />
                                Matricular
                            </Button>
                            <Button v-else variant="outline" disabled>
                                <GraduationCap
                                    class="size-4"
                                    data-icon="inline-start"
                                />
                                Ver matrícula
                            </Button>
                            <Button variant="outline" disabled>
                                <BookOpen
                                    class="size-4"
                                    data-icon="inline-start"
                                />
                                Informes
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
