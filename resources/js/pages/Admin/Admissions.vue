<script setup lang="ts">
import { FileText } from '@lucide/vue';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AdminCrudView, { type Column } from '@/components/AdminCrudView.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

interface Admission extends Record<string, unknown> {
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

const crudView = ref<InstanceType<typeof AdminCrudView> | null>(null);

const token = localStorage.getItem('token') ?? '';

const columns: Column[] = [
  {
    key: 'applicant',
    label: 'Aspirante',
    formatter: (row) =>
      `${row.applicant_first_name as string} ${row.applicant_last_name as string}`,
  },
  { key: 'representative_names', label: 'Representante' },
  {
    key: 'contact',
    label: 'Contacto',
    formatter: (row) => `${row.contact_email as string}`,
  },
  { key: 'level.name', label: 'Nivel' },
  { key: 'application_date', label: 'Fecha' },
  {
    key: 'status',
    label: 'Estado',
    formatter: (row) => statusLabel(row.status as string),
  },
];

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

async function updateStatus(
  admission: Admission,
  action: 'approve' | 'reject',
): Promise<void> {
  try {
    const response = await fetch(`/api/admissions/${admission.id}/${action}`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error(
        `No se pudo ${action === 'approve' ? 'aprobar' : 'rechazar'} la admisión.`,
      );
    }

    await crudView.value?.fetchPage();
  } catch (exception) {
    alert(
      exception instanceof Error ? exception.message : 'Error desconocido.',
    );
  }
}
</script>

<template>
  <AppLayout>
    <AdminCrudView
      ref="crudView"
      title="Admisiones"
      description="Gestión de solicitudes de admisión."
      endpoint="/api/admissions"
      :icon="FileText"
      :columns="columns"
      :fields="[]"
      :show-create-button="false"
      :show-default-actions="false"
    >
      <template #actions="{ row }">
        <Badge
          :variant="statusVariant((row as Admission).status)"
          class="mr-2"
        >
          {{ statusLabel((row as Admission).status) }}
        </Badge>
        <Button
          v-if="(row as Admission).status === 'pending'"
          size="sm"
          @click="updateStatus(row as Admission, 'approve')"
        >
          Aprobar
        </Button>
        <Button
          v-if="(row as Admission).status === 'pending'"
          size="sm"
          variant="outline"
          @click="updateStatus(row as Admission, 'reject')"
        >
          Rechazar
        </Button>
      </template>
    </AdminCrudView>
  </AppLayout>
</template>
