<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Enums\StudentLifecycleStatus;
use App\Enums\TuitionConcept;
use App\Enums\TuitionStatus;
use App\Models\AcademicPeriod;
use App\Models\Enrollment;
use App\Models\EnrollmentForm;
use App\Models\Student;
use App\Models\Tuition;
use App\Models\User;
use App\Support\EnrollmentFormSections;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EnrollmentFormService
{
    private const PRIVACY_POLICY_VERSION = '2026-01';

    private const MEDICAL_CONSENT_VERSION = '2026-01';

    private const EMERGENCY_CONSENT_VERSION = '2026-01';

    public function __construct(private EnrollmentAuditService $auditService) {}

    public function startOrResume(Student $student, User $user, ?string $ipAddress = null): Enrollment
    {
        $this->ensureOwner($student, $user);

        if ($student->getRawOriginal('lifecycle_status') === StudentLifecycleStatus::Graduated->value) {
            throw ValidationException::withMessages(['student' => ['El estudiante ya culminó la secuencia de niveles.']]);
        }

        if ($student->level_id === null) {
            throw ValidationException::withMessages([
                'student' => ['El administrador debe asignar un nivel al estudiante antes de iniciar la matrícula.'],
            ]);
        }

        if ($student->enrollments()->where('status', EnrollmentStatus::Active)->exists()) {
            throw ValidationException::withMessages([
                'student' => ['El administrador debe finalizar el nivel activo antes de iniciar una nueva matrícula.'],
            ]);
        }

        $period = AcademicPeriod::query()->openForEnrollment()->orderBy('enrollment_closes_at')->first();

        if (! $period instanceof AcademicPeriod) {
            throw ValidationException::withMessages(['period' => ['No existe un periodo de matrículas abierto.']]);
        }

        return DB::transaction(function () use ($student, $user, $period, $ipAddress): Enrollment {
            $enrollment = Enrollment::query()->firstOrCreate([
                'student_id' => $student->id,
                'academic_period_id' => $period->id,
            ], [
                'level_id' => $student->level_id,
                'course_id' => null,
                'enrollment_date' => now()->toDateString(),
                'status' => EnrollmentStatus::Draft,
            ]);

            if (! $enrollment->form()->exists()) {
                $enrollment->form()->create([
                    'current_step' => EnrollmentFormSections::ORDER[0],
                    'draft_data' => $this->initialData($student),
                ]);

                $this->auditService->record($enrollment, $user, 'enrollment_started', [], $ipAddress);
            }

            return $this->present($enrollment->fresh());
        });
    }

    /** @param array<string, mixed> $data */
    public function saveSection(
        Enrollment $enrollment,
        string $section,
        array $data,
        User $user,
        ?string $ipAddress = null,
    ): Enrollment {
        return DB::transaction(function () use ($enrollment, $section, $data, $user, $ipAddress): Enrollment {
            $lockedEnrollment = Enrollment::query()->with(['student.representative', 'academicPeriod', 'form'])
                ->lockForUpdate()->findOrFail($enrollment->id);

            $this->ensureEditable($lockedEnrollment, $user);

            $form = $lockedEnrollment->form;

            if (! $form instanceof EnrollmentForm || $form->snapshot_data !== null) {
                throw ValidationException::withMessages([
                    'enrollment' => ['La ficha confirmada por pago ya no puede modificarse.'],
                ]);
            }

            if (! in_array($section, EnrollmentFormSections::ORDER, true)) {
                throw ValidationException::withMessages(['section' => ['La sección indicada no existe.']]);
            }

            $draft = $form->draft_data ?? [];
            $draft[$section] = $data;
            $form->update([
                'draft_data' => $draft,
                'current_step' => EnrollmentFormSections::next($section),
            ]);

            $this->auditService->record(
                $lockedEnrollment,
                $user,
                'enrollment_section_saved',
                ['section' => $section],
                $ipAddress,
            );

            return $this->present($lockedEnrollment->fresh());
        });
    }

    public function complete(Enrollment $enrollment, User $user, ?string $ipAddress = null): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $user, $ipAddress): Enrollment {
            $lockedEnrollment = Enrollment::query()->with(['student.representative', 'academicPeriod', 'level', 'form'])
                ->lockForUpdate()->findOrFail($enrollment->id);

            $this->ensureEditable($lockedEnrollment, $user);

            $form = $lockedEnrollment->form;

            if (! $form instanceof EnrollmentForm) {
                throw ValidationException::withMessages(['enrollment' => ['La matrícula no tiene una ficha asociada.']]);
            }

            $draft = $form->draft_data ?? [];
            $validatedData = $this->validateCompleteDraft($draft);

            $hasOverduePensions = Tuition::query()
                ->where('student_id', $lockedEnrollment->student_id)
                ->where('concept', TuitionConcept::Monthly)
                ->whereIn('status', [TuitionStatus::Pending, TuitionStatus::Partial, TuitionStatus::Overdue])
                ->whereDate('due_date', '<', today())
                ->exists();

            if ($hasOverduePensions) {
                throw ValidationException::withMessages([
                    'tuitions' => ['Debes cancelar las pensiones vencidas antes de completar la matrícula.'],
                ]);
            }

            $this->synchronizeStudentProfile($lockedEnrollment->student, $validatedData);

            $form->update([
                'submitted_data' => $validatedData,
                'privacy_policy_version' => self::PRIVACY_POLICY_VERSION,
                'medical_consent_version' => self::MEDICAL_CONSENT_VERSION,
                'emergency_consent_version' => self::EMERGENCY_CONSENT_VERSION,
                'consented_at' => now(),
                'consent_ip' => $ipAddress,
                'consented_by' => $user->id,
            ]);

            $lockedEnrollment->update([
                'status' => EnrollmentStatus::PendingPayment,
                'form_completed_at' => now(),
            ]);

            Tuition::query()->updateOrCreate([
                'enrollment_id' => $lockedEnrollment->id,
            ], [
                'student_id' => $lockedEnrollment->student_id,
                'concept' => TuitionConcept::Enrollment,
                'amount' => $lockedEnrollment->level->enrollment_fee,
                'generation_date' => today(),
                'billing_period' => null,
                'due_date' => $lockedEnrollment->academicPeriod()->firstOrFail()->enrollment_closes_at->toDateString(),
                'status' => TuitionStatus::Pending,
            ]);

            $this->auditService->record($lockedEnrollment, $user, 'enrollment_form_completed', [], $ipAddress);

            return $this->present($lockedEnrollment->fresh());
        });
    }

    public function present(Enrollment $enrollment): Enrollment
    {
        return $enrollment->load([
            'academicPeriod', 'level', 'form', 'tuition.payments', 'course.level', 'course.courseTeachers.teacher',
        ]);
    }

    /** @return array<string, mixed> */
    private function initialData(Student $student): array
    {
        $student->loadMissing([
            'profile', 'residence', 'legalRepresentative', 'billingProfile', 'healthInsurance',
            'emergencyContacts', 'medicalConditions', 'allergies', 'medications', 'representative',
        ]);

        $representative = $student->representative;

        $data = [
            'student' => [
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'birth_date' => Carbon::parse($student->birth_date)->toDateString(),
                'preferred_name' => $student->profile?->preferred_name,
                'gender' => $student->profile?->gender,
                'nationality' => $student->profile?->nationality,
                'birth_place' => $student->profile?->birth_place,
                'previous_institution' => $student->profile?->previous_institution,
                'previous_level' => $student->profile?->previous_level,
                'academic_background' => $student->profile?->academic_background,
                'educational_needs' => $student->profile?->educational_needs,
                'educational_supports' => $student->profile?->educational_supports,
                'languages' => $student->profile?->languages,
            ],
            'health' => [
                'blood_type' => $student->profile?->blood_type,
                'pediatrician_name' => $student->profile?->pediatrician_name,
                'pediatrician_phone' => $student->profile?->pediatrician_phone,
                'developmental_notes' => $student->profile?->developmental_notes,
                'care_instructions' => $student->profile?->care_instructions,
                'medical_observations' => $student->profile?->medical_observations,
                'additional_notes' => $student->profile?->additional_notes,
            ],
            'conditions' => [
                'none' => $student->medicalConditions->isEmpty(),
                'items' => $student->medicalConditions->map->only(['name', 'details', 'care_instructions'])->values()->all(),
            ],
            'allergies' => [
                'none' => $student->allergies->isEmpty(),
                'items' => $student->allergies->map->only(['allergen', 'severity', 'reaction', 'response_instructions'])->values()->all(),
            ],
            'medications' => [
                'none' => $student->medications->isEmpty(),
                'items' => $student->medications->map->only(['name', 'dose', 'schedule', 'prescriber', 'instructions'])->values()->all(),
            ],
            'address' => $student->residence?->only([
                'country', 'province', 'city', 'parish', 'main_street', 'secondary_street', 'house_number',
                'reference', 'residence_type', 'housing_relationship',
            ]) ?? ['country' => 'Ecuador'],
            'legal_representative' => $student->legalRepresentative?->only([
                'relationship', 'id_type', 'id_number', 'first_name', 'last_name', 'email', 'phone',
                'birth_date', 'marital_status', 'occupation', 'workplace', 'work_phone', 'address',
            ]) ?? [
                'relationship' => 'representante',
                'id_type' => 'cedula',
                'id_number' => $representative->id_card,
                'first_name' => $representative->first_name,
                'last_name' => $representative->last_name,
                'email' => $representative->email,
                'phone' => $representative->phone,
            ],
            'billing' => $student->billingProfile?->only(['person_type', 'tax_id_type', 'tax_id', 'business_name', 'email', 'phone', 'address']) ?? [
                'person_type' => 'natural',
                'tax_id_type' => 'cedula',
                'tax_id' => $representative->id_card,
                'business_name' => trim($representative->first_name.' '.$representative->last_name),
                'email' => $representative->email,
                'phone' => $representative->phone,
            ],
            'emergency_contacts' => ['items' => $student->emergencyContacts->map->only([
                'full_name', 'relationship', 'phone', 'alternate_phone', 'address', 'authorized_pickup',
            ])->values()->all()],
            'insurance' => $student->healthInsurance?->only([
                'has_insurance', 'provider', 'policy_number', 'plan_name', 'policy_holder', 'emergency_phone', 'expires_on',
            ]) ?? ['has_insurance' => false],
        ];

        if ($student->legalRepresentative?->birth_date !== null) {
            $data['legal_representative']['birth_date'] = Carbon::parse($student->legalRepresentative->birth_date)->toDateString();
        }

        if ($student->healthInsurance?->expires_on !== null) {
            $data['insurance']['expires_on'] = Carbon::parse($student->healthInsurance->expires_on)->toDateString();
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function validateCompleteDraft(array $draft): array
    {
        $validated = [];

        foreach (EnrollmentFormSections::ORDER as $section) {
            $validator = Validator::make(
                is_array($draft[$section] ?? null) ? $draft[$section] : [],
                EnrollmentFormSections::rulesFor($section),
            );

            if ($validator->fails()) {
                throw ValidationException::withMessages([
                    $section => ["La sección {$section} está incompleta o contiene datos inválidos."],
                ]);
            }

            if (! EnrollmentFormSections::hasExplicitMedicalListChoice($section, $validator->validated())) {
                throw ValidationException::withMessages([
                    $section => ['Declara la ausencia o registra al menos un elemento.'],
                ]);
            }

            $validated[$section] = $validator->validated();
        }

        return $validated;
    }

    /** @param array<string, mixed> $data */
    private function synchronizeStudentProfile(Student $student, array $data): void
    {
        $studentData = $data['student'];
        $student->update(Arr::only($studentData, ['first_name', 'last_name', 'birth_date']));

        $student->profile()->updateOrCreate([], array_merge(
            Arr::except($studentData, ['first_name', 'last_name', 'birth_date']),
            $data['health'],
        ));
        $student->residence()->updateOrCreate([], $data['address']);
        $student->legalRepresentative()->updateOrCreate([], $data['legal_representative']);
        $student->billingProfile()->updateOrCreate([], $data['billing']);
        $student->healthInsurance()->updateOrCreate([], $data['insurance']);

        $this->replaceMany($student, 'medicalConditions', $data['conditions']['items']);
        $this->replaceMany($student, 'allergies', $data['allergies']['items']);
        $this->replaceMany($student, 'medications', $data['medications']['items']);

        $student->emergencyContacts()->delete();
        foreach ($data['emergency_contacts']['items'] as $position => $contact) {
            $student->emergencyContacts()->create(array_merge($contact, ['position' => $position + 1]));
        }
    }

    /** @param array<int, array<string, mixed>> $items */
    private function replaceMany(Student $student, string $relation, array $items): void
    {
        $student->{$relation}()->delete();

        foreach ($items as $item) {
            $student->{$relation}()->create($item);
        }
    }

    private function ensureEditable(Enrollment $enrollment, User $user): void
    {
        $this->ensureOwner($enrollment->student, $user);

        if (! in_array($enrollment->status, [EnrollmentStatus::Draft, EnrollmentStatus::PendingPayment], true)) {
            throw ValidationException::withMessages(['enrollment' => ['La matrícula ya no admite cambios en su ficha.']]);
        }

        if (! $enrollment->academicPeriod->acceptsEnrollmentChanges($enrollment->exception_until)) {
            throw ValidationException::withMessages(['period' => ['El periodo de matrículas está cerrado.']]);
        }
    }

    private function ensureOwner(Student $student, User $user): void
    {
        if ($student->representative->user_id !== $user->id) {
            throw ValidationException::withMessages(['student' => ['El estudiante no pertenece a tu cuenta.']]);
        }
    }
}
