<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle class="text-primary">Módulo Docente</CardTitle>
                    <CardDescription>
                        Aquí podrás registrar el avance académico de tus
                        estudiantes.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <p class="text-muted-foreground">
                        Próximamente: lista de cursos asignados y formulario de
                        informes por estudiante.
                    </p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
