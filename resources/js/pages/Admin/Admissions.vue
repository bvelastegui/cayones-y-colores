<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface Admission {
    id: number;
    applicant_first_name: string;
    applicant_last_name: string;
    representative_names: string;
    contact_email: string;
    contact_phone: string;
    status: 'pending' | 'approved' | 'rejected';
    application_date: string;
    level: { name: string } | null;
}

const admissions = ref<Admission[]>([]);
const loading = ref(false);
const error = ref('');

const token = localStorage.getItem('token') ?? '';

async function fetchAdmissions(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch('/api/admissions', {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Error al cargar las admisiones.');
        }

        const data = await response.json();
        admissions.value = data.data ?? [];
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Error desconocido.';
    } finally {
        loading.value = false;
    }
}

async function updateStatus(
    admission: Admission,
    action: 'approve' | 'reject',
): Promise<void> {
    try {
        const response = await fetch(
            `/api/admissions/${admission.id}/${action}`,
            {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json',
                },
            },
        );

        if (!response.ok) {
            throw new Error(
                `No se pudo ${action === 'approve' ? 'aprobar' : 'rechazar'} la admisión.`,
            );
        }

        await fetchAdmissions();
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Error desconocido.';
    }
}

function statusVariant(
    status: string,
): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'approved':
            return 'default';
        case 'rejected':
            return 'destructive';
        default:
            return 'secondary';
    }
}

function statusLabel(status: string): string {
    const labels: Record<string, string> = {
        pending: 'Pendiente',
        approved: 'Aprobada',
        rejected: 'Rechazada',
    };

    return labels[status] ?? status;
}

onMounted(() => {
    void fetchAdmissions();
});
</script>

<template>
    <div class="bg-background min-h-screen p-6">
        <Card>
            <CardHeader>
                <CardTitle>Gestión de Admisiones</CardTitle>
            </CardHeader>
            <CardContent>
                <p v-if="error" class="text-destructive mb-4 text-sm">
                    {{ error }}
                </p>
                <p v-if="loading" class="text-muted-foreground text-sm">
                    Cargando...
                </p>
                <Table v-else>
                    <TableHeader>
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>Aspirante</TableHead>
                            <TableHead>Representante</TableHead>
                            <TableHead>Contacto</TableHead>
                            <TableHead>Nivel</TableHead>
                            <TableHead>Fecha</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="admission in admissions"
                            :key="admission.id"
                        >
                            <TableCell>{{ admission.id }}</TableCell>
                            <TableCell>
                                {{ admission.applicant_first_name }}
                                {{ admission.applicant_last_name }}
                            </TableCell>
                            <TableCell>{{
                                admission.representative_names
                            }}</TableCell>
                            <TableCell>
                                <div class="text-sm">
                                    {{ admission.contact_email }}
                                </div>
                                <div class="text-muted-foreground text-xs">
                                    {{ admission.contact_phone }}
                                </div>
                            </TableCell>
                            <TableCell>{{
                                admission.level?.name ?? '-'
                            }}</TableCell>
                            <TableCell>{{
                                admission.application_date
                            }}</TableCell>
                            <TableCell>
                                <Badge
                                    :variant="statusVariant(admission.status)"
                                >
                                    {{ statusLabel(admission.status) }}
                                </Badge>
                            </TableCell>
                            <TableCell class="flex justify-end gap-2">
                                <Button
                                    v-if="admission.status === 'pending'"
                                    size="sm"
                                    @click="updateStatus(admission, 'approve')"
                                >
                                    Aprobar
                                </Button>
                                <Button
                                    v-if="admission.status === 'pending'"
                                    size="sm"
                                    variant="outline"
                                    @click="updateStatus(admission, 'reject')"
                                >
                                    Rechazar
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </div>
</template>
