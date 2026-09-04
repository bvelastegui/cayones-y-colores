<script setup lang="ts">
import {
  ArrowLeft01Icon,
  CheckmarkCircle02Icon,
  CreditCardIcon,
  Delete02Icon,
  PlusSignIcon,
} from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/vue';
import { computed, onMounted, reactive, ref } from 'vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';

interface Condition {
  name: string;
  details: string;
  care_instructions: string;
}

interface Allergy {
  allergen: string;
  severity: string;
  reaction: string;
  response_instructions: string;
}

interface Medication {
  name: string;
  dose: string;
  schedule: string;
  prescriber: string;
  instructions: string;
}

interface EmergencyContact {
  full_name: string;
  relationship: string;
  phone: string;
  alternate_phone: string;
  address: string;
  authorized_pickup: boolean;
}

interface DraftData {
  student: Record<string, string>;
  health: Record<string, string>;
  conditions: { none: boolean; items: Condition[] };
  allergies: { none: boolean; items: Allergy[] };
  medications: { none: boolean; items: Medication[] };
  address: Record<string, string>;
  legal_representative: Record<string, string>;
  billing: Record<string, string>;
  emergency_contacts: { items: EmergencyContact[] };
  insurance: {
    has_insurance: boolean;
    provider: string;
    policy_number: string;
    plan_name: string;
    policy_holder: string;
    emergency_phone: string;
    expires_on: string;
  };
}

interface Enrollment {
  id: number;
  status: string;
  assignment_issue: string | null;
  level: { name: string; enrollment_fee: string };
  academic_period: { name: string; enrollment_closes_at: string } | null;
  form: {
    current_step: string;
    draft_data: DraftData;
    snapshot_data: DraftData | null;
  } | null;
  tuition?: { amount: string; status: string } | null;
  course?: {
    parallel: string;
    course_teachers: {
      assigned_role: string;
      teacher: { first_name: string; last_name: string; email: string | null };
    }[];
  } | null;
}

const sections = [
  { key: 'student', label: 'Estudiante' },
  { key: 'health', label: 'Salud y cuidado' },
  { key: 'conditions', label: 'Antecedentes médicos' },
  { key: 'allergies', label: 'Alergias' },
  { key: 'medications', label: 'Medicamentos' },
  { key: 'address', label: 'Vivienda' },
  { key: 'legal_representative', label: 'Representante legal' },
  { key: 'billing', label: 'Facturación' },
  { key: 'emergency_contacts', label: 'Emergencias' },
  { key: 'insurance', label: 'Seguro médico' },
] as const;

const route = useRoute();
const router = useRouter();
const studentId = Number(route.params.studentId);
const token = localStorage.getItem('token') ?? '';

const enrollment = ref<Enrollment | null>(null);
const currentIndex = ref(0);
const loading = ref(true);
const saving = ref(false);
const paying = ref(false);
const error = ref('');
const success = ref('');
const acceptedPrivacy = ref(false);
const acceptedMedical = ref(false);
const acceptedEmergency = ref(false);

const draft = reactive<DraftData>({
  student: {
    first_name: '',
    last_name: '',
    birth_date: '',
    preferred_name: '',
    gender: '',
    nationality: '',
    birth_place: '',
    previous_institution: '',
    previous_level: '',
    academic_background: '',
    educational_needs: '',
    educational_supports: '',
    languages: '',
  },
  health: {
    blood_type: '',
    pediatrician_name: '',
    pediatrician_phone: '',
    developmental_notes: '',
    care_instructions: '',
    medical_observations: '',
    additional_notes: '',
  },
  conditions: { none: true, items: [] },
  allergies: { none: true, items: [] },
  medications: { none: true, items: [] },
  address: {
    country: 'Ecuador',
    province: '',
    city: '',
    parish: '',
    main_street: '',
    secondary_street: '',
    house_number: '',
    reference: '',
    residence_type: '',
    housing_relationship: '',
  },
  legal_representative: {
    relationship: '',
    id_type: 'cedula',
    id_number: '',
    first_name: '',
    last_name: '',
    birth_date: '',
    marital_status: '',
    email: '',
    phone: '',
    occupation: '',
    workplace: '',
    work_phone: '',
    address: '',
  },
  billing: {
    person_type: 'natural',
    tax_id_type: 'cedula',
    tax_id: '',
    business_name: '',
    email: '',
    phone: '',
    address: '',
  },
  emergency_contacts: { items: [] },
  insurance: {
    has_insurance: false,
    provider: '',
    policy_number: '',
    plan_name: '',
    policy_holder: '',
    emergency_phone: '',
    expires_on: '',
  },
});

const currentSection = computed(() => sections[currentIndex.value]);
const progress = computed(() =>
  Math.min(100, Math.round((currentIndex.value / sections.length) * 100)),
);
const canEdit = computed(() =>
  ['draft', 'pending_payment'].includes(enrollment.value?.status ?? ''),
);
const canPay = computed(
  () =>
    enrollment.value?.status === 'pending_payment' ||
    enrollment.value?.status === 'payment_in_progress',
);

function headers(json = false): HeadersInit {
  return {
    Authorization: `Bearer ${token}`,
    Accept: 'application/json',
    ...(json ? { 'Content-Type': 'application/json' } : {}),
  };
}

async function responseData(response: Response): Promise<Enrollment> {
  const data = (await response.json()) as Enrollment & {
    message?: string;
    errors?: Record<string, string[]>;
  };

  if (!response.ok) {
    const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : null;
    throw new Error(
      firstError ?? data.message ?? 'No se pudo procesar la solicitud.',
    );
  }

  return data;
}

function hydrate(data: Enrollment): void {
  enrollment.value = data;
  if (!data.form) {
    return;
  }
  const form = data.form;
  const stored = form.draft_data;
  Object.assign(draft.student, stored.student ?? {});
  Object.assign(draft.health, stored.health ?? {});
  Object.assign(draft.address, stored.address ?? {});
  Object.assign(draft.legal_representative, stored.legal_representative ?? {});
  Object.assign(draft.billing, stored.billing ?? {});
  Object.assign(draft.insurance, stored.insurance ?? {});
  draft.conditions.none = stored.conditions?.none ?? true;
  draft.conditions.items = stored.conditions?.items ?? [];
  draft.allergies.none = stored.allergies?.none ?? true;
  draft.allergies.items = stored.allergies?.items ?? [];
  draft.medications.none = stored.medications?.none ?? true;
  draft.medications.items = stored.medications?.items ?? [];
  draft.emergency_contacts.items = stored.emergency_contacts?.items ?? [];
  const storedIndex = sections.findIndex(
    (section) => section.key === form.current_step,
  );
  currentIndex.value =
    form.current_step === 'review' ? sections.length : Math.max(0, storedIndex);
}

async function fetchEnrollment(): Promise<void> {
  loading.value = true;
  error.value = '';

  try {
    const currentResponse = await fetch(
      `/api/me/students/${studentId}/enrollment`,
      { headers: headers() },
    );
    const current = currentResponse.ok
      ? ((await currentResponse.json()) as Enrollment | null)
      : null;

    if (current && !['finalized', 'withdrawn'].includes(current.status)) {
      if (current.form) {
        hydrate(current);
      } else {
        enrollment.value = current;
      }
      return;
    }

    const startResponse = await fetch(
      `/api/me/students/${studentId}/enrollments`,
      {
        method: 'POST',
        headers: headers(true),
      },
    );
    hydrate(await responseData(startResponse));
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    loading.value = false;
  }
}

async function saveCurrent(): Promise<void> {
  if (!enrollment.value || !currentSection.value) {
    return;
  }

  saving.value = true;
  error.value = '';

  try {
    const section = currentSection.value.key;
    const response = await fetch(
      `/api/me/enrollments/${enrollment.value.id}/form/${section}`,
      {
        method: 'PUT',
        headers: headers(true),
        body: JSON.stringify(draft[section]),
      },
    );
    enrollment.value = await responseData(response);
    currentIndex.value = Math.min(currentIndex.value + 1, sections.length);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    saving.value = false;
  }
}

async function completeEnrollment(): Promise<void> {
  if (
    !enrollment.value ||
    !acceptedPrivacy.value ||
    !acceptedMedical.value ||
    !acceptedEmergency.value
  ) {
    error.value = 'Debes aceptar los consentimientos para continuar.';
    return;
  }

  saving.value = true;
  error.value = '';

  try {
    const response = await fetch(
      `/api/me/enrollments/${enrollment.value.id}/complete`,
      {
        method: 'POST',
        headers: headers(true),
        body: JSON.stringify({
          accept_privacy_policy: acceptedPrivacy.value,
          accept_medical_data_processing: acceptedMedical.value,
          accept_emergency_authorization: acceptedEmergency.value,
        }),
      },
    );
    hydrate(await responseData(response));
    success.value = 'Ficha enviada. Ya puedes pagar la matrícula.';
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
  } finally {
    saving.value = false;
  }
}

async function payEnrollment(): Promise<void> {
  if (!enrollment.value) {
    return;
  }

  paying.value = true;
  error.value = '';

  try {
    const response = await fetch(
      `/api/me/enrollments/${enrollment.value.id}/payphone`,
      {
        method: 'POST',
        headers: headers(true),
      },
    );
    const data = (await response.json()) as {
      payment_url?: string;
      message?: string;
    };

    if (!response.ok || !data.payment_url) {
      throw new Error(
        data.message ?? 'No se pudo preparar el pago con PayPhone.',
      );
    }

    window.location.assign(data.payment_url);
  } catch (exception) {
    error.value =
      exception instanceof Error ? exception.message : 'Error desconocido.';
    paying.value = false;
  }
}

function addCondition(): void {
  draft.conditions.none = false;
  draft.conditions.items.push({ name: '', details: '', care_instructions: '' });
}

function addAllergy(): void {
  draft.allergies.none = false;
  draft.allergies.items.push({
    allergen: '',
    severity: 'mild',
    reaction: '',
    response_instructions: '',
  });
}

function addMedication(): void {
  draft.medications.none = false;
  draft.medications.items.push({
    name: '',
    dose: '',
    schedule: '',
    prescriber: '',
    instructions: '',
  });
}

function addEmergencyContact(): void {
  if (draft.emergency_contacts.items.length < 3) {
    draft.emergency_contacts.items.push({
      full_name: '',
      relationship: '',
      phone: '',
      alternate_phone: '',
      address: '',
      authorized_pickup: false,
    });
  }
}

function statusLabel(status: string): string {
  return (
    {
      draft: 'Ficha en borrador',
      pending_payment: 'Pendiente de pago',
      payment_in_progress: 'Pago en proceso',
      paid_pending_assignment: 'Pagada · pendiente de asignación',
      active: 'Matrícula activa',
      finalized: 'Nivel finalizado',
      graduated: 'Estudiante graduado',
    }[status] ?? status
  );
}

onMounted(() => {
  const paymentResult = route.query.payment;

  if (paymentResult === 'approved') {
    success.value = 'Pago de matrícula confirmado correctamente.';
  } else if (paymentResult === 'cancelled') {
    error.value = 'El pago de matrícula fue cancelado.';
  } else if (paymentResult === 'error') {
    error.value = 'No se pudo confirmar el pago. Inténtalo nuevamente.';
  }

  void fetchEnrollment();
});
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-5xl space-y-6">
      <Button
        variant="ghost"
        class="-ml-3"
        @click="router.push('/parent')"
      >
        <HugeiconsIcon
          :icon="ArrowLeft01Icon"
          class="size-4"
        />
        Volver
      </Button>

      <div
        class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
      >
        <div>
          <h1 class="text-2xl font-bold tracking-tight">
            Proceso de matrícula
          </h1>
          <p class="text-muted-foreground">
            Actualiza la ficha integral antes de realizar el pago.
          </p>
        </div>
        <Badge
          v-if="enrollment"
          variant="secondary"
          >{{ statusLabel(enrollment.status) }}</Badge
        >
      </div>

      <p
        v-if="error"
        class="text-destructive border-destructive/20 bg-destructive/5 rounded-lg border p-3 text-sm"
      >
        {{ error }}
      </p>
      <p
        v-if="success"
        class="rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-3 text-sm text-emerald-700"
      >
        {{ success }}
      </p>

      <Card v-if="loading">
        <CardContent
          class="text-muted-foreground flex items-center justify-center gap-2 py-16"
        >
          <Spinner /> Cargando ficha…
        </CardContent>
      </Card>

      <template v-else-if="enrollment">
        <Card class="border-primary/20 bg-primary/5">
          <CardContent class="grid gap-4 py-5 sm:grid-cols-3">
            <div>
              <p class="text-muted-foreground text-xs tracking-wide uppercase">
                Periodo
              </p>
              <p class="font-medium">
                {{ enrollment.academic_period?.name ?? 'Registro anterior' }}
              </p>
            </div>
            <div>
              <p class="text-muted-foreground text-xs tracking-wide uppercase">
                Nivel asignado
              </p>
              <p class="font-medium">{{ enrollment.level.name }}</p>
            </div>
            <div>
              <p class="text-muted-foreground text-xs tracking-wide uppercase">
                Valor de matrícula
              </p>
              <p class="font-medium">
                ${{ Number(enrollment.level.enrollment_fee).toFixed(2) }}
              </p>
            </div>
          </CardContent>
        </Card>

        <Card v-if="enrollment.status === 'active' && enrollment.course">
          <CardHeader>
            <CardTitle class="flex items-center gap-2"
              ><HugeiconsIcon
                :icon="CheckmarkCircle02Icon"
                class="text-primary size-5"
              />
              Matrícula activa</CardTitle
            >
            <CardDescription
              >Paralelo {{ enrollment.course.parallel }} ·
              {{ enrollment.level.name }}</CardDescription
            >
          </CardHeader>
          <CardContent class="space-y-3">
            <div
              v-for="assignment in enrollment.course.course_teachers"
              :key="`${assignment.teacher.email}-${assignment.assigned_role}`"
              class="rounded-lg border p-3 text-sm"
            >
              <p class="font-medium">
                {{ assignment.teacher.first_name }}
                {{ assignment.teacher.last_name }}
              </p>
              <p class="text-muted-foreground">
                {{ assignment.assigned_role }} ·
                {{
                  assignment.teacher.email ?? 'Correo institucional pendiente'
                }}
              </p>
            </div>
          </CardContent>
        </Card>

        <Card v-else-if="enrollment.status === 'paid_pending_assignment'">
          <CardHeader
            ><CardTitle>Pago confirmado</CardTitle
            ><CardDescription
              >El curso se asignará automáticamente al finalizar el periodo. No
              necesitas seleccionar un paralelo.</CardDescription
            ></CardHeader
          >
        </Card>

        <Card v-else-if="canPay && currentIndex >= sections.length">
          <CardHeader
            ><CardTitle>Ficha lista para pago</CardTitle
            ><CardDescription
              >El valor se calculó en el servidor según el nivel
              {{ enrollment.level.name }}.</CardDescription
            ></CardHeader
          >
          <CardContent
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
          >
            <div>
              <p class="text-muted-foreground text-sm">Total</p>
              <p class="text-2xl font-semibold">
                ${{
                  Number(
                    enrollment.tuition?.amount ??
                      enrollment.level.enrollment_fee,
                  ).toFixed(2)
                }}
              </p>
            </div>
            <div class="flex gap-2">
              <Button
                variant="outline"
                @click="currentIndex = 0"
                >Editar ficha</Button
              >
              <Button
                :disabled="paying"
                @click="payEnrollment"
                ><Spinner v-if="paying" /><HugeiconsIcon
                  v-else
                  :icon="CreditCardIcon"
                  class="size-4"
                />{{ paying ? 'Preparando…' : 'Pagar con PayPhone' }}</Button
              >
            </div>
          </CardContent>
        </Card>

        <template v-else-if="canEdit">
          <div class="space-y-2">
            <div class="flex items-center justify-between text-sm">
              <span>{{
                currentIndex < sections.length
                  ? sections[currentIndex].label
                  : 'Revisión y consentimiento'
              }}</span
              ><span class="text-muted-foreground"
                >{{ Math.min(currentIndex + 1, sections.length + 1) }} de
                {{ sections.length + 1 }}</span
              >
            </div>
            <div class="bg-muted h-2 overflow-hidden rounded-full">
              <div
                class="bg-primary h-full rounded-full transition-all"
                :style="{ width: `${progress}%` }"
              />
            </div>
          </div>

          <Card>
            <CardHeader
              ><CardTitle>{{
                currentSection?.label ?? 'Revisión y consentimiento'
              }}</CardTitle
              ><CardDescription
                >Los cambios de esta sección se guardan únicamente al pulsar
                “Guardar y continuar”.</CardDescription
              ></CardHeader
            >
            <CardContent class="space-y-5">
              <div
                v-if="currentSection?.key === 'student'"
                class="grid gap-4 sm:grid-cols-2"
              >
                <div>
                  <Label for="first_name">Nombres</Label
                  ><Input
                    id="first_name"
                    v-model="draft.student.first_name"
                  />
                </div>
                <div>
                  <Label for="last_name">Apellidos</Label
                  ><Input
                    id="last_name"
                    v-model="draft.student.last_name"
                  />
                </div>
                <div>
                  <Label for="birth_date">Fecha de nacimiento</Label
                  ><Input
                    id="birth_date"
                    v-model="draft.student.birth_date"
                    type="date"
                  />
                </div>
                <div>
                  <Label for="preferred_name">Nombre preferido</Label
                  ><Input
                    id="preferred_name"
                    v-model="draft.student.preferred_name"
                  />
                </div>
                <div>
                  <Label for="gender">Género</Label
                  ><Input
                    id="gender"
                    v-model="draft.student.gender"
                  />
                </div>
                <div>
                  <Label for="nationality">Nacionalidad</Label
                  ><Input
                    id="nationality"
                    v-model="draft.student.nationality"
                  />
                </div>
                <div>
                  <Label for="birth_place">Lugar de nacimiento</Label
                  ><Input
                    id="birth_place"
                    v-model="draft.student.birth_place"
                  />
                </div>
                <div>
                  <Label for="previous_institution">Institución anterior</Label
                  ><Input
                    id="previous_institution"
                    v-model="draft.student.previous_institution"
                  />
                </div>
                <div>
                  <Label for="previous_level">Nivel anterior</Label
                  ><Input
                    id="previous_level"
                    v-model="draft.student.previous_level"
                  />
                </div>
                <div class="sm:col-span-2">
                  <Label for="academic_background"
                    >Antecedentes académicos</Label
                  ><Textarea
                    id="academic_background"
                    v-model="draft.student.academic_background"
                  />
                </div>
                <div class="sm:col-span-2">
                  <Label>Necesidades educativas</Label
                  ><Textarea v-model="draft.student.educational_needs" />
                </div>
                <div class="sm:col-span-2">
                  <Label>Apoyos educativos</Label
                  ><Textarea v-model="draft.student.educational_supports" />
                </div>
                <div class="sm:col-span-2">
                  <Label>Idiomas</Label
                  ><Input v-model="draft.student.languages" />
                </div>
              </div>

              <div
                v-if="currentSection?.key === 'health'"
                class="space-y-4"
              >
                <div class="grid gap-4 sm:grid-cols-3">
                  <div>
                    <Label>Tipo de sangre</Label
                    ><Input v-model="draft.health.blood_type" />
                  </div>
                  <div>
                    <Label>Pediatra</Label
                    ><Input v-model="draft.health.pediatrician_name" />
                  </div>
                  <div>
                    <Label>Teléfono del pediatra</Label
                    ><Input v-model="draft.health.pediatrician_phone" />
                  </div>
                </div>
                <div>
                  <Label for="developmental_notes"
                    >Desarrollo y necesidades particulares</Label
                  ><Textarea
                    id="developmental_notes"
                    v-model="draft.health.developmental_notes"
                  />
                </div>
                <div>
                  <Label for="care_instructions">Instrucciones de cuidado</Label
                  ><Textarea
                    id="care_instructions"
                    v-model="draft.health.care_instructions"
                  />
                </div>
                <div>
                  <Label for="additional_notes"
                    >Información adicional relevante</Label
                  ><Textarea
                    id="additional_notes"
                    v-model="draft.health.additional_notes"
                  />
                </div>
                <div>
                  <Label>Observaciones médicas</Label
                  ><Textarea v-model="draft.health.medical_observations" />
                </div>
              </div>

              <div
                v-if="currentSection?.key === 'conditions'"
                class="space-y-4"
              >
                <label
                  class="flex items-center gap-2 rounded-lg border p-3 text-sm"
                >
                  <input
                    v-model="draft.conditions.none"
                    type="checkbox"
                    class="accent-primary size-4"
                    @change="
                      draft.conditions.none
                        ? (draft.conditions.items = [])
                        : null
                    "
                  />
                  No registra antecedentes médicos
                </label>
                <div
                  v-for="(item, index) in draft.conditions.items"
                  :key="index"
                  class="space-y-3 rounded-xl border p-4"
                >
                  <div class="flex justify-between">
                    <p class="font-medium">Antecedente {{ index + 1 }}</p>
                    <Button
                      size="icon"
                      variant="ghost"
                      aria-label="Eliminar antecedente"
                      @click="draft.conditions.items.splice(index, 1)"
                      ><HugeiconsIcon
                        :icon="Delete02Icon"
                        class="size-4"
                    /></Button>
                  </div>
                  <Input
                    v-model="item.name"
                    placeholder="Condición o diagnóstico"
                  /><Textarea
                    v-model="item.details"
                    placeholder="Detalles"
                  /><Textarea
                    v-model="item.care_instructions"
                    placeholder="Instrucciones de cuidado"
                  />
                </div>
                <Button
                  variant="outline"
                  @click="addCondition"
                  ><HugeiconsIcon
                    :icon="PlusSignIcon"
                    class="size-4"
                  />Agregar antecedente</Button
                >
              </div>

              <div
                v-if="currentSection?.key === 'allergies'"
                class="space-y-4"
              >
                <label
                  class="flex items-center gap-2 rounded-lg border p-3 text-sm"
                >
                  <input
                    v-model="draft.allergies.none"
                    type="checkbox"
                    class="accent-primary size-4"
                    @change="
                      draft.allergies.none ? (draft.allergies.items = []) : null
                    "
                  />
                  No registra alergias
                </label>
                <div
                  v-for="(item, index) in draft.allergies.items"
                  :key="index"
                  class="space-y-3 rounded-xl border p-4"
                >
                  <div class="flex justify-between">
                    <p class="font-medium">Alergia {{ index + 1 }}</p>
                    <Button
                      size="icon"
                      variant="ghost"
                      aria-label="Eliminar alergia"
                      @click="draft.allergies.items.splice(index, 1)"
                      ><HugeiconsIcon
                        :icon="Delete02Icon"
                        class="size-4"
                    /></Button>
                  </div>
                  <Input
                    v-model="item.allergen"
                    placeholder="Alérgeno"
                  />
                  <select
                    v-model="item.severity"
                    class="border-input h-9 w-full rounded-md border bg-transparent px-3 text-sm"
                  >
                    <option value="mild">Leve</option>
                    <option value="moderate">Moderada</option>
                    <option value="severe">Severa</option>
                  </select>
                  <Textarea
                    v-model="item.reaction"
                    placeholder="Reacción"
                  /><Textarea
                    v-model="item.response_instructions"
                    placeholder="Cómo actuar"
                  />
                </div>
                <Button
                  variant="outline"
                  @click="addAllergy"
                  ><HugeiconsIcon
                    :icon="PlusSignIcon"
                    class="size-4"
                  />Agregar alergia</Button
                >
              </div>

              <div
                v-if="currentSection?.key === 'medications'"
                class="space-y-4"
              >
                <label
                  class="flex items-center gap-2 rounded-lg border p-3 text-sm"
                >
                  <input
                    v-model="draft.medications.none"
                    type="checkbox"
                    class="accent-primary size-4"
                    @change="
                      draft.medications.none
                        ? (draft.medications.items = [])
                        : null
                    "
                  />
                  No utiliza medicamentos
                </label>
                <div
                  v-for="(item, index) in draft.medications.items"
                  :key="index"
                  class="grid gap-3 rounded-xl border p-4 sm:grid-cols-2"
                >
                  <div class="flex justify-between sm:col-span-2">
                    <p class="font-medium">Medicamento {{ index + 1 }}</p>
                    <Button
                      size="icon"
                      variant="ghost"
                      aria-label="Eliminar medicamento"
                      @click="draft.medications.items.splice(index, 1)"
                      ><HugeiconsIcon
                        :icon="Delete02Icon"
                        class="size-4"
                    /></Button>
                  </div>
                  <Input
                    v-model="item.name"
                    placeholder="Nombre"
                  /><Input
                    v-model="item.dose"
                    placeholder="Dosis"
                  /><Input
                    v-model="item.schedule"
                    placeholder="Horario"
                  /><Input
                    v-model="item.prescriber"
                    placeholder="Médico que prescribe"
                  /><Textarea
                    v-model="item.instructions"
                    class="sm:col-span-2"
                    placeholder="Instrucciones"
                  />
                </div>
                <Button
                  variant="outline"
                  @click="addMedication"
                  ><HugeiconsIcon
                    :icon="PlusSignIcon"
                    class="size-4"
                  />Agregar medicamento</Button
                >
              </div>

              <div
                v-if="currentSection?.key === 'address'"
                class="grid gap-4 sm:grid-cols-2"
              >
                <div>
                  <Label>País</Label><Input v-model="draft.address.country" />
                </div>
                <div>
                  <Label>Provincia</Label
                  ><Input v-model="draft.address.province" />
                </div>
                <div>
                  <Label>Ciudad</Label><Input v-model="draft.address.city" />
                </div>
                <div>
                  <Label>Parroquia</Label
                  ><Input v-model="draft.address.parish" />
                </div>
                <div>
                  <Label>Calle principal</Label
                  ><Input v-model="draft.address.main_street" />
                </div>
                <div>
                  <Label>Calle secundaria</Label
                  ><Input v-model="draft.address.secondary_street" />
                </div>
                <div>
                  <Label>Número</Label
                  ><Input v-model="draft.address.house_number" />
                </div>
                <div>
                  <Label>Referencia</Label
                  ><Input v-model="draft.address.reference" />
                </div>
                <div>
                  <Label>Tipo de vivienda</Label
                  ><Input v-model="draft.address.residence_type" />
                </div>
                <div>
                  <Label>Relación con la vivienda</Label
                  ><Input v-model="draft.address.housing_relationship" />
                </div>
              </div>

              <div
                v-if="currentSection?.key === 'legal_representative'"
                class="grid gap-4 sm:grid-cols-2"
              >
                <div>
                  <Label>Parentesco</Label
                  ><Input v-model="draft.legal_representative.relationship" />
                </div>
                <div>
                  <Label>Tipo de identificación</Label
                  ><select
                    v-model="draft.legal_representative.id_type"
                    class="border-input h-9 w-full rounded-md border bg-transparent px-3 text-sm"
                  >
                    <option value="cedula">Cédula</option>
                    <option value="ruc">RUC</option>
                    <option value="passport">Pasaporte</option>
                  </select>
                </div>
                <div>
                  <Label>Identificación</Label
                  ><Input v-model="draft.legal_representative.id_number" />
                </div>
                <div>
                  <Label>Nombres</Label
                  ><Input v-model="draft.legal_representative.first_name" />
                </div>
                <div>
                  <Label>Apellidos</Label
                  ><Input v-model="draft.legal_representative.last_name" />
                </div>
                <div>
                  <Label>Fecha de nacimiento</Label
                  ><Input
                    v-model="draft.legal_representative.birth_date"
                    type="date"
                  />
                </div>
                <div>
                  <Label>Estado civil</Label
                  ><Input v-model="draft.legal_representative.marital_status" />
                </div>
                <div>
                  <Label>Correo</Label
                  ><Input
                    v-model="draft.legal_representative.email"
                    type="email"
                  />
                </div>
                <div>
                  <Label>Teléfono</Label
                  ><Input v-model="draft.legal_representative.phone" />
                </div>
                <div>
                  <Label>Ocupación</Label
                  ><Input v-model="draft.legal_representative.occupation" />
                </div>
                <div>
                  <Label>Lugar de trabajo</Label
                  ><Input v-model="draft.legal_representative.workplace" />
                </div>
                <div>
                  <Label>Teléfono laboral</Label
                  ><Input v-model="draft.legal_representative.work_phone" />
                </div>
                <div class="sm:col-span-2">
                  <Label>Dirección</Label
                  ><Input v-model="draft.legal_representative.address" />
                </div>
              </div>

              <div
                v-if="currentSection?.key === 'billing'"
                class="grid gap-4 sm:grid-cols-2"
              >
                <div>
                  <Label>Tipo de persona</Label>
                  <select
                    v-model="draft.billing.person_type"
                    class="border-input h-9 w-full rounded-md border bg-transparent px-3 text-sm"
                  >
                    <option value="natural">Persona natural</option>
                    <option value="company">Empresa</option>
                  </select>
                </div>
                <div>
                  <Label>Tipo de identificación</Label
                  ><select
                    v-model="draft.billing.tax_id_type"
                    class="border-input h-9 w-full rounded-md border bg-transparent px-3 text-sm"
                  >
                    <option value="cedula">Cédula</option>
                    <option value="ruc">RUC</option>
                    <option value="passport">Pasaporte</option>
                  </select>
                </div>
                <div>
                  <Label>Identificación</Label
                  ><Input v-model="draft.billing.tax_id" />
                </div>
                <div>
                  <Label>Razón social</Label
                  ><Input v-model="draft.billing.business_name" />
                </div>
                <div>
                  <Label>Correo de facturación</Label
                  ><Input
                    v-model="draft.billing.email"
                    type="email"
                  />
                </div>
                <div>
                  <Label>Teléfono</Label><Input v-model="draft.billing.phone" />
                </div>
                <div>
                  <Label>Dirección</Label
                  ><Input v-model="draft.billing.address" />
                </div>
              </div>

              <div
                v-if="currentSection?.key === 'emergency_contacts'"
                class="space-y-4"
              >
                <div
                  v-for="(item, index) in draft.emergency_contacts.items"
                  :key="index"
                  class="grid gap-3 rounded-xl border p-4 sm:grid-cols-2"
                >
                  <div class="flex justify-between sm:col-span-2">
                    <p class="font-medium">Contacto {{ index + 1 }}</p>
                    <Button
                      size="icon"
                      variant="ghost"
                      aria-label="Eliminar contacto"
                      @click="draft.emergency_contacts.items.splice(index, 1)"
                      ><HugeiconsIcon
                        :icon="Delete02Icon"
                        class="size-4"
                    /></Button>
                  </div>
                  <Input
                    v-model="item.full_name"
                    placeholder="Nombre completo"
                  /><Input
                    v-model="item.relationship"
                    placeholder="Parentesco"
                  /><Input
                    v-model="item.phone"
                    placeholder="Teléfono"
                  /><Input
                    v-model="item.alternate_phone"
                    placeholder="Teléfono alterno"
                  />
                  <Input
                    v-model="item.address"
                    class="sm:col-span-2"
                    placeholder="Dirección"
                  />
                  <label class="flex items-center gap-2 text-sm sm:col-span-2"
                    ><input
                      v-model="item.authorized_pickup"
                      type="checkbox"
                      class="border-input accent-primary size-4 rounded"
                    />Autorizado para retirar al estudiante</label
                  >
                </div>
                <Button
                  variant="outline"
                  :disabled="draft.emergency_contacts.items.length >= 3"
                  @click="addEmergencyContact"
                  ><HugeiconsIcon
                    :icon="PlusSignIcon"
                    class="size-4"
                  />Agregar contacto</Button
                >
              </div>

              <div
                v-if="currentSection?.key === 'insurance'"
                class="space-y-4"
              >
                <label class="flex items-center gap-2 text-sm"
                  ><input
                    v-model="draft.insurance.has_insurance"
                    type="checkbox"
                    class="border-input accent-primary size-4 rounded"
                  />El estudiante tiene seguro médico</label
                >
                <div
                  v-if="draft.insurance.has_insurance"
                  class="grid gap-4 sm:grid-cols-2"
                >
                  <div>
                    <Label>Aseguradora</Label
                    ><Input v-model="draft.insurance.provider" />
                  </div>
                  <div>
                    <Label>Número de póliza</Label
                    ><Input v-model="draft.insurance.policy_number" />
                  </div>
                  <div>
                    <Label>Plan</Label
                    ><Input v-model="draft.insurance.plan_name" />
                  </div>
                  <div>
                    <Label>Titular de la póliza</Label
                    ><Input v-model="draft.insurance.policy_holder" />
                  </div>
                  <div>
                    <Label>Teléfono de emergencia</Label
                    ><Input v-model="draft.insurance.emergency_phone" />
                  </div>
                  <div>
                    <Label>Vigencia hasta</Label
                    ><Input
                      v-model="draft.insurance.expires_on"
                      type="date"
                    />
                  </div>
                </div>
              </div>

              <div
                v-if="currentIndex >= sections.length"
                class="space-y-4"
              >
                <p class="text-muted-foreground text-sm">
                  Al confirmar, la información se actualizará en la ficha actual
                  del estudiante. El pago exitoso congelará una copia histórica
                  que no podrá editarse.
                </p>
                <label
                  class="flex items-start gap-3 rounded-lg border p-4 text-sm"
                  ><input
                    v-model="acceptedPrivacy"
                    type="checkbox"
                    class="accent-primary mt-0.5 size-4"
                  /><span
                    >Acepto la política de privacidad y declaro que la
                    información registrada es correcta.</span
                  ></label
                >
                <label
                  class="flex items-start gap-3 rounded-lg border p-4 text-sm"
                >
                  <input
                    v-model="acceptedEmergency"
                    type="checkbox"
                    class="accent-primary mt-0.5 size-4"
                  />
                  <span
                    >Autorizo la atención y comunicación con los contactos
                    registrados en caso de emergencia.</span
                  >
                </label>
                <label
                  class="flex items-start gap-3 rounded-lg border p-4 text-sm"
                  ><input
                    v-model="acceptedMedical"
                    type="checkbox"
                    class="accent-primary mt-0.5 size-4"
                  /><span
                    >Autorizo el tratamiento de datos médicos para el cuidado
                    académico del estudiante.</span
                  ></label
                >
              </div>

              <div
                class="flex flex-col-reverse gap-2 border-t pt-5 sm:flex-row sm:justify-between"
              >
                <Button
                  variant="outline"
                  :disabled="currentIndex === 0 || saving"
                  @click="currentIndex--"
                  >Anterior</Button
                >
                <Button
                  v-if="currentIndex < sections.length"
                  :disabled="saving"
                  @click="saveCurrent"
                  ><Spinner v-if="saving" />{{
                    saving ? 'Guardando…' : 'Guardar y continuar'
                  }}</Button
                >
                <Button
                  v-else
                  :disabled="
                    saving ||
                    !acceptedPrivacy ||
                    !acceptedMedical ||
                    !acceptedEmergency
                  "
                  @click="completeEnrollment"
                  ><Spinner v-if="saving" />Confirmar ficha</Button
                >
              </div>
            </CardContent>
          </Card>
        </template>
      </template>
    </div>
  </AppLayout>
</template>
