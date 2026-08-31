<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { ChevronsUpDown, Plus, Users } from '@lucide/vue';

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useAuth } from '@/composables/auth';
import {
    type CurrentStudent,
    useCurrentStudent,
} from '@/composables/currentStudent';

const { isMobile } = useSidebar();
const auth = useAuth();
const { selectedStudent, setCurrentStudent } = useCurrentStudent();

const students = ref<CurrentStudent[]>([]);
const loading = ref(false);

const isRepresentative = computed(
    () => auth.user.value?.role === 'representative',
);

const title = computed(() => {
    if (isRepresentative.value && selectedStudent.value) {
        return `${selectedStudent.value.first_name} ${selectedStudent.value.last_name}`;
    }

    return 'Crayones y Colores';
});

const subtitle = computed(() => {
    if (isRepresentative.value && selectedStudent.value) {
        return selectedStudent.value.course_name ?? 'Sin curso asignado';
    }

    return roleLabel(auth.user.value?.role);
});

function roleLabel(
    role: 'admin' | 'teacher' | 'representative' | undefined,
): string {
    switch (role) {
        case 'admin':
            return 'Administración';
        case 'teacher':
            return 'Docente';
        case 'representative':
            return 'Padre / Representante';
        default:
            return '';
    }
}

function buildStudent(raw: Record<string, unknown>): CurrentStudent {
    const enrollment = ((raw.enrollments as Record<string, unknown>[]) ??
        [])[0];
    const course = enrollment?.course as Record<string, unknown> | undefined;
    const level = course?.level as Record<string, unknown> | undefined;
    const courseName = course
        ? `${course.name as string} (${level?.name as string})`
        : null;

    return {
        id: raw.id as number,
        first_name: raw.first_name as string,
        last_name: raw.last_name as string,
        course_name: courseName,
    };
}

async function fetchStudents(): Promise<void> {
    if (!isRepresentative.value) {
        return;
    }

    loading.value = true;

    try {
        const token = localStorage.getItem('token') ?? '';
        const response = await fetch('/api/me/students', {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('No se pudieron cargar los estudiantes.');
        }

        const data = (await response.json()) as Record<string, unknown>[];
        students.value = data.map(buildStudent);
    } finally {
        loading.value = false;
    }
}

function selectStudent(student: CurrentStudent): void {
    setCurrentStudent(student);
}

watch(
    students,
    (list) => {
        if (list.length > 0 && !selectedStudent.value) {
            setCurrentStudent(list[0]);
        }
    },
    { immediate: true },
);

onMounted(() => {
    void fetchStudents();
});
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        size="lg"
                        class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                    >
                        <div
                            class="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-lg"
                        >
                            <Users class="size-4" />
                        </div>
                        <div
                            class="grid flex-1 text-left text-sm leading-tight"
                        >
                            <span class="truncate font-semibold">{{
                                title
                            }}</span>
                            <span class="truncate text-xs">{{ subtitle }}</span>
                        </div>
                        <ChevronsUpDown
                            v-if="isRepresentative && students.length > 1"
                            class="ml-auto size-4"
                        />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    v-if="isRepresentative"
                    class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                    :side="isMobile ? 'bottom' : 'right'"
                    :side-offset="4"
                    align="start"
                >
                    <DropdownMenuLabel class="text-muted-foreground text-xs">
                        Estudiantes
                    </DropdownMenuLabel>
                    <DropdownMenuItem
                        v-for="student in students"
                        :key="student.id"
                        class="cursor-pointer gap-2 p-2"
                        @click="selectStudent(student)"
                    >
                        <div
                            class="grid flex-1 text-left text-sm leading-tight"
                        >
                            <span class="truncate font-medium">
                                {{ student.first_name }} {{ student.last_name }}
                            </span>
                            <span
                                class="text-muted-foreground truncate text-xs"
                            >
                                {{
                                    student.course_name ?? 'Sin curso asignado'
                                }}
                            </span>
                        </div>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator v-if="students.length > 0" />
                    <DropdownMenuItem class="gap-2 p-2" disabled>
                        <div
                            class="bg-background flex size-6 items-center justify-center rounded-md border"
                        >
                            <Plus class="size-4" />
                        </div>
                        <div class="text-muted-foreground font-medium">
                            Registrar nuevo estudiante
                        </div>
                    </DropdownMenuItem>
                    <DropdownMenuLabel
                        v-if="students.length === 0 && !loading"
                        class="text-muted-foreground p-2 text-xs"
                    >
                        No tienes estudiantes registrados.
                    </DropdownMenuLabel>
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
