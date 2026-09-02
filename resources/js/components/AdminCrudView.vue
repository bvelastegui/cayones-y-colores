<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';
import { Pencil, Plus, Trash2 } from '@lucide/vue';
import { computed, reactive, ref, watch } from 'vue';
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

export interface FieldOption {
    value: string | number;
    label: string;
}

export interface Field {
    name: string;
    label: string;
    type: 'text' | 'email' | 'password' | 'number' | 'date' | 'select';
    required?: boolean;
    options?: FieldOption[];
}

export interface Column {
    key: string;
    label: string;
    formatter?: (row: Record<string, unknown>) => string;
}

interface Pagination {
    current_page: number;
    last_page: number;
}

const props = withDefaults(
    defineProps<{
        title: string;
        description?: string;
        endpoint: string;
        columns: Column[];
        fields: Field[];
        icon?: LucideIcon;
        showCreateButton?: boolean;
        showDefaultActions?: boolean;
    }>(),
    {
        showCreateButton: true,
        showDefaultActions: true,
    },
);

const token = localStorage.getItem('token') ?? '';

const rows = ref<Record<string, unknown>[]>([]);
const pagination = ref<Pagination>({ current_page: 1, last_page: 1 });
const loading = ref(false);
const saving = ref(false);
const error = ref('');
const formError = ref('');
const dialogOpen = ref(false);
const editingId = ref<number | null>(null);

const emptyForm = computed<Record<string, string | number>>(() => {
    const form: Record<string, string | number> = {};

    for (const field of props.fields) {
        form[field.name] = field.type === 'number' ? 0 : '';
    }

    return form;
});

const form = reactive<Record<string, string | number>>({});

const dialogTitle = computed(() =>
    editingId.value ? `Editar ${props.title}` : `Nuevo ${props.title}`,
);

function resetForm(): void {
    Object.assign(form, emptyForm.value);
}

function openCreate(): void {
    editingId.value = null;
    resetForm();
    formError.value = '';
    dialogOpen.value = true;
}

function openEdit(row: Record<string, unknown>): void {
    editingId.value = (row.id as number) ?? null;
    resetForm();

    for (const field of props.fields) {
        const value = getNestedValue(row, field.name);
        form[field.name] = normalizeFormValue(value, field);
    }

    formError.value = '';
    dialogOpen.value = true;
}

function normalizeFormValue(value: unknown, field: Field): string | number {
    if (value === undefined || value === null) {
        return field.type === 'number' ? 0 : '';
    }

    if (typeof value === 'boolean') {
        return value ? '1' : '0';
    }

    return value as string | number;
}

function getNestedValue(obj: Record<string, unknown>, path: string): unknown {
    return path.split('.').reduce<unknown>((acc, key) => {
        if (acc && typeof acc === 'object') {
            return (acc as Record<string, unknown>)[key];
        }

        return undefined;
    }, obj);
}

function getCellValue(row: Record<string, unknown>, key: string): string {
    const column = props.columns.find((c) => c.key === key);

    if (column?.formatter) {
        return column.formatter(row);
    }

    const value = getNestedValue(row, key);

    if (value === null || value === undefined) {
        return '';
    }

    return String(value);
}

async function fetchPage(page: number = 1): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch(`${props.endpoint}?page=${page}`, {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Error al cargar los datos.');
        }

        const data = (await response.json()) as {
            data: Record<string, unknown>[];
            current_page: number;
            last_page: number;
        };

        rows.value = data.data;
        pagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
        };
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Error desconocido.';
    } finally {
        loading.value = false;
    }
}

async function submitForm(): Promise<void> {
    saving.value = true;
    formError.value = '';

    const method = editingId.value ? 'PUT' : 'POST';
    const url = editingId.value
        ? `${props.endpoint}/${editingId.value}`
        : props.endpoint;

    const payload: Record<string, unknown> = {};

    for (const field of props.fields) {
        const value = form[field.name];

        if (
            editingId.value &&
            !field.required &&
            typeof value === 'string' &&
            value === ''
        ) {
            continue;
        }

        if (field.name === 'is_active') {
            payload[field.name] = value === '1';
        } else {
            payload[field.name] = value;
        }
    }

    try {
        const response = await fetch(url, {
            method,
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const data = (await response.json()) as Record<string, unknown>;

        if (!response.ok) {
            throw new Error(
                (data.message as string) ?? 'No se pudo guardar el registro.',
            );
        }

        dialogOpen.value = false;
        await fetchPage(pagination.value.current_page);
    } catch (exception) {
        formError.value =
            exception instanceof Error
                ? exception.message
                : 'Error desconocido.';
    } finally {
        saving.value = false;
    }
}

async function destroy(row: Record<string, unknown>): Promise<void> {
    if (!confirm('¿Estás seguro de eliminar este registro?')) {
        return;
    }

    try {
        const response = await fetch(`${props.endpoint}/${row.id}`, {
            method: 'DELETE',
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Error al eliminar el registro.');
        }

        await fetchPage(pagination.value.current_page);
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Error desconocido.';
    }
}

watch(
    () => props.endpoint,
    () => {
        void fetchPage(1);
    },
    { immediate: true },
);

defineExpose({ fetchPage });
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    <component
                        :is="icon"
                        v-if="icon"
                        class="mr-2 inline size-6"
                    />
                    {{ title }}
                </h1>
                <p v-if="description" class="text-muted-foreground">
                    {{ description }}
                </p>
            </div>

            <Dialog v-model:open="dialogOpen">
                <DialogTrigger v-if="showCreateButton" as-child>
                    <Button @click="openCreate">
                        <Plus class="size-4" data-icon="inline-start" />
                        Nuevo
                    </Button>
                </DialogTrigger>
                <DialogContent class="sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle>{{ dialogTitle }}</DialogTitle>
                        <DialogDescription>
                            Completa los campos y guarda los cambios.
                        </DialogDescription>
                    </DialogHeader>

                    <p v-if="formError" class="text-destructive text-sm">
                        {{ formError }}
                    </p>

                    <div class="grid gap-4 py-2">
                        <div
                            v-for="field in fields"
                            :key="field.name"
                            class="grid gap-2"
                        >
                            <Label :for="field.name">{{ field.label }}</Label>
                            <Input
                                v-if="field.type !== 'select'"
                                :id="field.name"
                                v-model="form[field.name]"
                                :type="field.type"
                                :required="field.required"
                            />
                            <Select
                                v-else
                                :id="field.name"
                                v-model="form[field.name]"
                            >
                                <SelectTrigger>
                                    <SelectValue
                                        :placeholder="`Selecciona ${field.label}`"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="option in field.options"
                                        :key="String(option.value)"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" @click="dialogOpen = false">
                            Cancelar
                        </Button>
                        <Button :disabled="saving" @click="submitForm">
                            {{ saving ? 'Guardando...' : 'Guardar' }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>

        <p v-if="error" class="text-destructive text-sm">{{ error }}</p>

        <div class="overflow-hidden rounded-lg border">
            <Table>
                <TableHeader class="bg-muted sticky top-0 z-10">
                    <TableRow>
                        <TableHead v-for="column in columns" :key="column.key">
                            {{ column.label }}
                        </TableHead>
                        <TableHead class="text-right">Acciones</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="loading">
                        <TableCell
                            :colspan="columns.length + 1"
                            class="text-center"
                        >
                            Cargando...
                        </TableCell>
                    </TableRow>
                    <TableRow v-else-if="rows.length === 0">
                        <TableCell
                            :colspan="columns.length + 1"
                            class="text-muted-foreground text-center"
                        >
                            No hay registros.
                        </TableCell>
                    </TableRow>
                    <TableRow v-for="row in rows" :key="String(row.id)">
                        <TableCell v-for="column in columns" :key="column.key">
                            {{ getCellValue(row, column.key) }}
                        </TableCell>
                        <TableCell class="text-right">
                            <slot name="actions" :row="row">
                                <template v-if="showDefaultActions">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openEdit(row)"
                                    >
                                        <Pencil class="size-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="destroy(row)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </template>
                            </slot>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div class="flex items-center justify-between">
            <p class="text-muted-foreground text-sm">
                Página {{ pagination.current_page }} de
                {{ pagination.last_page }}
            </p>
            <div class="flex gap-2">
                <Button
                    variant="outline"
                    :disabled="pagination.current_page <= 1"
                    @click="fetchPage(pagination.current_page - 1)"
                >
                    Anterior
                </Button>
                <Button
                    variant="outline"
                    :disabled="pagination.current_page >= pagination.last_page"
                    @click="fetchPage(pagination.current_page + 1)"
                >
                    Siguiente
                </Button>
            </div>
        </div>
    </div>
</template>
