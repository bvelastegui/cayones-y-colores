<?php

namespace Database\Seeders;

use App\Enums\AcademicPeriodStatus;
use App\Enums\AdmissionStatus;
use App\Enums\AssignedRole;
use App\Enums\EnrollmentOutcome;
use App\Enums\EnrollmentStatus;
use App\Enums\PaymentMethod;
use App\Enums\PayphonePaymentStatus;
use App\Enums\StudentLifecycleStatus;
use App\Enums\TeacherType;
use App\Enums\TuitionConcept;
use App\Enums\TuitionStatus;
use App\Enums\UserRole;
use App\Models\AcademicPeriod;
use App\Models\Admission;
use App\Models\Course;
use App\Models\CourseTeacher;
use App\Models\Enrollment;
use App\Models\EnrollmentAudit;
use App\Models\EnrollmentForm;
use App\Models\Level;
use App\Models\Payment;
use App\Models\PayphonePaymentAttempt;
use App\Models\PayphonePaymentAttemptItem;
use App\Models\Representative;
use App\Models\Student;
use App\Models\StudentAddress;
use App\Models\StudentAllergy;
use App\Models\StudentBillingProfile;
use App\Models\StudentEmergencyContact;
use App\Models\StudentHealthInsurance;
use App\Models\StudentLegalRepresentative;
use App\Models\StudentMedicalCondition;
use App\Models\StudentMedication;
use App\Models\StudentProfile;
use App\Models\StudentRecordAccessLog;
use App\Models\Teacher;
use App\Models\Tuition;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplianceEnrollmentSeeder extends Seeder
{
    use WithoutModelEvents;

    /** @var list<string> */
    private const LEVEL_NAMES = ['Maternal 1', 'Maternal 2', 'Inicial 1', 'Inicial 2', 'Primero EGB'];

    public function run(): void
    {
        DB::transaction(function (): void {
            $this->call(LevelSeeder::class);

            $today = CarbonImmutable::today();
            [$olderPeriod, $previousPeriod, $currentPeriod] = $this->periods($today);
            $levels = $this->levels();
            [$courses, $assignedTeacher] = $this->coursesAndTeachers($levels);
            $admin = $this->user('Administrador Cumplimiento', 'compliance.admin@cenestur.test', 'CMP-ADM-001', UserRole::Admin);
            $families = $this->coreFamilies();
            $students = $this->historicalStudents($families, $levels);

            $enrollments = $this->closedPeriodEnrollments($students, $levels, $courses, $olderPeriod, $previousPeriod);
            $enrollments = [
                ...$enrollments,
                ...$this->currentEnrollmentScenarios($students, $levels, $courses, $currentPeriod),
                ...$this->overloadedParallelEnrollments($levels, $courses, $currentPeriod),
            ];

            $complianceStudentIds = Student::query()->where('id_card', 'like', 'CMP-EST-%')->pluck('id');
            Tuition::query()->whereIn('student_id', $complianceStudentIds)->delete();

            $this->closedPeriodBilling($enrollments, $olderPeriod, $previousPeriod, $students['debtor']);
            $this->currentEnrollmentBilling($enrollments, $today);
            $this->admissions($levels, $currentPeriod);
            $this->audits($enrollments, $admin);
            $this->recordAccess($students['active'], $assignedTeacher);
        });
    }

    /** @return array{AcademicPeriod, AcademicPeriod, AcademicPeriod} */
    private function periods(CarbonImmutable $today): array
    {
        $legacyPreviousPeriod = AcademicPeriod::query()->where('name', 'CMP | Periodo anterior')->first();

        if ($legacyPreviousPeriod !== null
            && ! AcademicPeriod::query()->where('name', 'CMP | Periodo cerrado 2')->exists()) {
            $legacyPreviousPeriod->update(['name' => 'CMP | Periodo cerrado 2']);
        }

        $older = AcademicPeriod::query()->updateOrCreate(['name' => 'CMP | Periodo cerrado 1'], [
            'starts_on' => $today->subYears(2)->startOfMonth(),
            'ends_on' => $today->subYears(2)->addMonths(9)->endOfMonth(),
            'enrollment_opens_at' => $today->subYears(2)->subMonth()->startOfDay(),
            'enrollment_closes_at' => $today->subYears(2)->startOfMonth()->subDay()->endOfDay(),
            'status' => AcademicPeriodStatus::Closed,
            'allocation_started_at' => $today->subYears(2)->startOfMonth(),
            'allocation_completed_at' => $today->subYears(2)->startOfMonth()->addDay(),
        ]);
        $previous = AcademicPeriod::query()->updateOrCreate(['name' => 'CMP | Periodo cerrado 2'], [
            'starts_on' => $today->subYear()->startOfMonth(),
            'ends_on' => $today->subYear()->addMonths(9)->endOfMonth(),
            'enrollment_opens_at' => $today->subYear()->subMonth()->startOfDay(),
            'enrollment_closes_at' => $today->subYear()->startOfMonth()->subDay()->endOfDay(),
            'status' => AcademicPeriodStatus::Closed,
            'allocation_started_at' => $today->subYear()->startOfMonth(),
            'allocation_completed_at' => $today->subYear()->startOfMonth()->addDay(),
        ]);
        $current = AcademicPeriod::query()->updateOrCreate(['name' => 'CMP | Periodo vigente'], [
            'starts_on' => $today->startOfMonth(),
            'ends_on' => $today->addMonths(9)->endOfMonth(),
            'enrollment_opens_at' => $today->subMonth()->startOfDay(),
            'enrollment_closes_at' => $today->addMonth()->endOfDay(),
            'status' => AcademicPeriodStatus::Open,
            'allocation_started_at' => null,
            'allocation_completed_at' => null,
        ]);

        return [$older, $previous, $current];
    }

    /** @return array<string, Level> */
    private function levels(): array
    {
        $levels = [];

        foreach (self::LEVEL_NAMES as $name) {
            $levels[$name] = Level::query()->where('name', $name)->firstOrFail();
        }

        return $levels;
    }

    /**
     * @param  array<string, Level>  $levels
     * @return array{array<string, Course>, Teacher}
     */
    private function coursesAndTeachers(array $levels): array
    {
        $courses = [];

        foreach ($levels as $levelName => $level) {
            $course = Course::query()->firstOrCreate(['level_id' => $level->id, 'parallel' => 'CMP-A']);
            $course->courseTeachers()->delete();
            $slug = str_replace(' ', '-', mb_strtolower($levelName));
            $isLegacyPrincipal = $levelName === 'Maternal 1';
            $principalUser = $this->user(
                "Docente principal {$levelName}",
                $isLegacyPrincipal ? 'compliance.teacher@cenestur.test' : "compliance.teacher.{$slug}@cenestur.test",
                $isLegacyPrincipal ? 'CMP-DOC-001' : 'CMP-DOC-P-'.str_pad((string) $level->sequence_order, 2, '0', STR_PAD_LEFT),
                UserRole::Teacher,
            );
            $principal = Teacher::query()->updateOrCreate(['id_card' => $isLegacyPrincipal ? '1799000101' : "CMP-DOC-P-{$level->sequence_order}"], [
                'user_id' => $principalUser->id,
                'first_name' => 'Docente',
                'last_name' => "Principal {$levelName}",
                'email' => $principalUser->email,
                'teacher_type' => TeacherType::Principal,
            ]);
            CourseTeacher::query()->create([
                'course_id' => $course->id,
                'teacher_id' => $principal->id,
                'assigned_role' => AssignedRole::Principal,
            ]);

            if (in_array($levelName, ['Maternal 1', 'Maternal 2'], true)) {
                foreach (range(1, 4) as $auxiliaryNumber) {
                    $auxiliaryUser = $this->user(
                        "Auxiliar {$auxiliaryNumber} {$levelName}",
                        "compliance.aux.{$slug}.{$auxiliaryNumber}@cenestur.test",
                        "CMP-DOC-A-{$level->sequence_order}-{$auxiliaryNumber}",
                        UserRole::Teacher,
                    );
                    $auxiliary = Teacher::query()->updateOrCreate(
                        ['id_card' => "CMP-DOC-A-{$level->sequence_order}-{$auxiliaryNumber}"],
                        [
                            'user_id' => $auxiliaryUser->id,
                            'first_name' => "Auxiliar {$auxiliaryNumber}",
                            'last_name' => $levelName,
                            'email' => $auxiliaryUser->email,
                            'teacher_type' => TeacherType::Auxiliary,
                        ],
                    );
                    CourseTeacher::query()->create([
                        'course_id' => $course->id,
                        'teacher_id' => $auxiliary->id,
                        'assigned_role' => AssignedRole::Auxiliary,
                    ]);
                }
            }

            $courses[$levelName] = $course;
        }

        $unassignedUser = $this->user('Docente Sin Asignación', 'compliance.unassigned@cenestur.test', 'CMP-DOC-002', UserRole::Teacher);
        Teacher::query()->updateOrCreate(['id_card' => '1799000102'], [
            'user_id' => $unassignedUser->id,
            'first_name' => 'Docente',
            'last_name' => 'Sin Asignación',
            'email' => $unassignedUser->email,
            'teacher_type' => TeacherType::Auxiliary,
        ]);

        $assignedTeacher = $courses['Maternal 2']->courseTeachers()
            ->where('assigned_role', AssignedRole::Principal)
            ->firstOrFail()
            ->teacher;

        return [$courses, $assignedTeacher];
    }

    /** @return array<string, Representative> */
    private function coreFamilies(): array
    {
        return [
            'multi' => $this->family('Familia Rivera', 'Rivera', 'compliance.parent@cenestur.test', 'CMP-REP-001', '1799000001'),
            'single' => $this->family('Familia Torres', 'Torres', 'compliance.single@cenestur.test', 'CMP-REP-002', '1799000002'),
            'debtor' => $this->family('Familia Mora', 'Mora', 'compliance.debtor@cenestur.test', 'CMP-REP-003', '1799000003'),
            'pending' => $this->family('Familia Vega', 'Vega', 'compliance.pending@cenestur.test', 'CMP-REP-004', '1799000004'),
        ];
    }

    private function family(string $name, string $lastName, string $email, string $identification, string $idCard): Representative
    {
        $user = $this->user($name, $email, $identification, UserRole::Representative);

        return Representative::query()->updateOrCreate(['id_card' => $idCard], [
            'user_id' => $user->id,
            'first_name' => 'Representante',
            'last_name' => $lastName,
            'email' => $email,
            'phone' => '099900'.substr($idCard, -4),
        ]);
    }

    private function user(string $name, string $email, string $identification, UserRole $role): User
    {
        return User::query()->updateOrCreate(['email' => $email], [
            'name' => $name,
            'identification' => $identification,
            'password' => 'password',
            'phone' => '0999000000',
            'address' => 'Quito, Ecuador',
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @param  array<string, Representative>  $families
     * @param  array<string, Level>  $levels
     * @return array<string, Student>
     */
    private function historicalStudents(array $families, array $levels): array
    {
        return [
            'promoted' => $this->student($families['multi'], $levels['Inicial 1'], 'CMP-EST-001', 'Lucía', 'Rivera', '2021-02-10'),
            'draft' => $this->student($families['multi'], $levels['Inicial 2'], 'CMP-EST-002', 'Mateo', 'Rivera', '2020-04-12'),
            'processing' => $this->student($families['single'], $levels['Primero EGB'], 'CMP-EST-003', 'Sara', 'Torres', '2019-06-18'),
            'debtor' => $this->student($families['debtor'], $levels['Primero EGB'], 'CMP-EST-004', 'Daniel', 'Mora', '2019-08-21', StudentLifecycleStatus::Graduated),
            'pending' => $this->student($families['debtor'], $levels['Primero EGB'], 'CMP-EST-005', 'Emilia', 'Mora', '2018-11-03'),
            'active' => $this->student($families['pending'], $levels['Maternal 2'], 'CMP-EST-006', 'Nicolás', 'Vega', '2021-12-09'),
        ];
    }

    private function student(
        Representative $representative,
        Level $level,
        string $idCard,
        string $firstName,
        string $lastName,
        string $birthDate,
        StudentLifecycleStatus $lifecycle = StudentLifecycleStatus::Active,
    ): Student {
        $student = Student::query()->updateOrCreate(['id_card' => $idCard], [
            'representative_id' => $representative->id,
            'level_id' => $level->id,
            'lifecycle_status' => $lifecycle,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'birth_date' => $birthDate,
        ]);
        StudentProfile::query()->updateOrCreate(['student_id' => $student->id], [
            'preferred_name' => $firstName,
            'gender' => 'no especificado',
            'nationality' => 'Ecuatoriana',
            'birth_place' => 'Quito',
            'previous_institution' => 'Centro Infantil Semillitas',
            'previous_level' => 'Nivel anterior',
            'academic_background' => 'Desarrollo acorde a la edad.',
            'educational_needs' => 'Ninguna reportada.',
            'educational_supports' => 'Acompañamiento familiar.',
            'languages' => 'Español',
            'blood_type' => 'O+',
            'pediatrician_name' => 'Dra. Elena Paz',
            'pediatrician_phone' => '022900000',
            'developmental_notes' => 'Desarrollo esperado.',
            'care_instructions' => 'Mantener hidratación.',
            'medical_observations' => 'Datos ficticios CMP.',
            'additional_notes' => 'Registro generado por seeder.',
        ]);
        StudentAddress::query()->updateOrCreate(['student_id' => $student->id], [
            'country' => 'Ecuador', 'province' => 'Pichincha', 'city' => 'Quito', 'parish' => 'Iñaquito',
            'main_street' => 'Av. Cumplimiento', 'secondary_street' => 'Calle Pruebas', 'house_number' => 'CMP-10',
            'reference' => 'Frente al parque', 'residence_type' => 'casa', 'housing_relationship' => 'propia',
        ]);
        StudentLegalRepresentative::query()->updateOrCreate(['student_id' => $student->id], [
            'relationship' => 'padre', 'id_type' => 'cedula', 'id_number' => $representative->id_card,
            'first_name' => $representative->first_name, 'last_name' => $representative->last_name,
            'birth_date' => '1990-01-15', 'marital_status' => 'casado', 'email' => $representative->email,
            'phone' => $representative->phone, 'occupation' => 'Analista', 'workplace' => 'Empresa Demo',
            'work_phone' => '022900001', 'address' => 'Quito',
        ]);
        StudentBillingProfile::query()->updateOrCreate(['student_id' => $student->id], [
            'person_type' => 'natural', 'tax_id_type' => 'cedula', 'tax_id' => $representative->id_card,
            'business_name' => $representative->first_name.' '.$representative->last_name,
            'email' => $representative->email, 'phone' => $representative->phone, 'address' => 'Quito',
        ]);
        StudentHealthInsurance::query()->updateOrCreate(['student_id' => $student->id], [
            'has_insurance' => true, 'provider' => 'Seguro Demo', 'policy_number' => "POL-{$idCard}",
            'plan_name' => 'Infantil', 'policy_holder' => $representative->first_name.' '.$representative->last_name,
            'emergency_phone' => '1800000000', 'expires_on' => now()->addYear()->toDateString(),
        ]);
        StudentEmergencyContact::query()->updateOrCreate(['student_id' => $student->id, 'position' => 1], [
            'full_name' => $representative->first_name.' '.$representative->last_name,
            'relationship' => 'padre', 'phone' => $representative->phone,
            'alternate_phone' => '022900002', 'address' => 'Quito', 'authorized_pickup' => true,
        ]);
        $student->medicalConditions()->delete();
        $student->allergies()->delete();
        $student->medications()->delete();
        StudentMedicalCondition::query()->create([
            'student_id' => $student->id, 'name' => 'Asma leve', 'details' => 'Episodios esporádicos.',
            'care_instructions' => 'Avisar al representante.',
        ]);
        StudentAllergy::query()->create([
            'student_id' => $student->id, 'allergen' => 'Maní', 'severity' => 'alta',
            'reaction' => 'Urticaria', 'response_instructions' => 'Avisar al representante.',
        ]);
        StudentMedication::query()->create([
            'student_id' => $student->id, 'name' => 'Inhalador de rescate', 'dose' => 'Según prescripción',
            'schedule' => 'Solo emergencia', 'prescriber' => 'Dra. Elena Paz', 'instructions' => 'Uso autorizado.',
        ]);

        return $student;
    }

    /**
     * @param  array<string, Student>  $students
     * @param  array<string, Level>  $levels
     * @param  array<string, Course>  $courses
     * @return list<Enrollment>
     */
    private function closedPeriodEnrollments(
        array $students,
        array $levels,
        array $courses,
        AcademicPeriod $olderPeriod,
        AcademicPeriod $previousPeriod,
    ): array {
        $paths = [
            'promoted' => ['Maternal 1', 'Maternal 2'],
            'draft' => ['Maternal 2', 'Inicial 1'],
            'processing' => ['Inicial 1', 'Inicial 2'],
            'debtor' => ['Inicial 2', 'Primero EGB'],
            'pending' => ['Primero EGB', 'Primero EGB'],
            'active' => ['Maternal 1', 'Maternal 1'],
        ];
        $enrollments = [];

        foreach ($paths as $studentKey => [$olderLevel, $previousLevel]) {
            $enrollments[] = $this->enrollment(
                $students[$studentKey],
                $olderPeriod,
                $levels[$olderLevel],
                $courses[$olderLevel],
                EnrollmentStatus::Finalized,
                $olderPeriod->ends_on,
                $studentKey === 'pending' || $studentKey === 'active' ? EnrollmentOutcome::NotCompleted : EnrollmentOutcome::Completed,
            );
            $enrollments[] = $this->enrollment(
                $students[$studentKey],
                $previousPeriod,
                $levels[$previousLevel],
                $courses[$previousLevel],
                EnrollmentStatus::Finalized,
                $previousPeriod->ends_on,
                $studentKey === 'pending' ? EnrollmentOutcome::NotCompleted : EnrollmentOutcome::Completed,
            );
        }

        return $enrollments;
    }

    /**
     * @param  array<string, Student>  $students
     * @param  array<string, Level>  $levels
     * @param  array<string, Course>  $courses
     * @return list<Enrollment>
     */
    private function currentEnrollmentScenarios(array $students, array $levels, array $courses, AcademicPeriod $period): array
    {
        return [
            $this->enrollment($students['promoted'], $period, $levels['Inicial 1'], null, EnrollmentStatus::PaidPendingAssignment, $period->starts_on),
            $this->enrollment($students['draft'], $period, $levels['Inicial 2'], null, EnrollmentStatus::Draft, $period->starts_on),
            $this->enrollment($students['processing'], $period, $levels['Primero EGB'], null, EnrollmentStatus::PaymentInProgress, $period->starts_on),
            $this->enrollment($students['pending'], $period, $levels['Primero EGB'], null, EnrollmentStatus::PendingPayment, $period->starts_on),
            $this->enrollment($students['active'], $period, $levels['Maternal 2'], $courses['Maternal 2'], EnrollmentStatus::Active, $period->starts_on),
        ];
    }

    /**
     * @param  array<string, Level>  $levels
     * @param  array<string, Course>  $courses
     * @return list<Enrollment>
     */
    private function overloadedParallelEnrollments(array $levels, array $courses, AcademicPeriod $period): array
    {
        $enrollments = [];

        foreach (range(1, 13) as $familyNumber) {
            $suffix = str_pad((string) $familyNumber, 2, '0', STR_PAD_LEFT);
            $representative = $this->family(
                "Familia Sobrecarga {$suffix}",
                "Sobrecarga {$suffix}",
                "compliance.family{$suffix}@cenestur.test",
                "CMP-REP-LOAD-{$suffix}",
                "CMP-REP-DOC-{$suffix}",
            );
            $maternalOneStudent = $this->student(
                $representative,
                $levels['Maternal 1'],
                "CMP-EST-M1-{$suffix}",
                "Niño M1 {$suffix}",
                'Sobrecarga',
                '2023-03-15',
            );
            $maternalTwoStudent = $this->student(
                $representative,
                $levels['Maternal 2'],
                "CMP-EST-M2-{$suffix}",
                "Niño M2 {$suffix}",
                'Sobrecarga',
                '2022-03-15',
            );
            $enrollments[] = $this->enrollment(
                $maternalOneStudent,
                $period,
                $levels['Maternal 1'],
                $courses['Maternal 1'],
                EnrollmentStatus::Active,
                $period->starts_on,
            );
            $enrollments[] = $this->enrollment(
                $maternalTwoStudent,
                $period,
                $levels['Maternal 2'],
                $courses['Maternal 2'],
                EnrollmentStatus::Active,
                $period->starts_on,
            );
        }

        return $enrollments;
    }

    private function enrollment(
        Student $student,
        AcademicPeriod $period,
        Level $level,
        ?Course $course,
        EnrollmentStatus $status,
        CarbonInterface $date,
        ?EnrollmentOutcome $outcome = null,
    ): Enrollment {
        $submitted = $status !== EnrollmentStatus::Draft;
        $paid = in_array($status, [EnrollmentStatus::PaidPendingAssignment, EnrollmentStatus::Active, EnrollmentStatus::Finalized], true);
        $assigned = in_array($status, [EnrollmentStatus::Active, EnrollmentStatus::Finalized], true);

        $enrollment = Enrollment::query()->updateOrCreate(
            ['student_id' => $student->id, 'academic_period_id' => $period->id],
            [
                'level_id' => $level->id,
                'course_id' => $course?->id,
                'enrollment_date' => $date,
                'status' => $status,
                'level_outcome' => $outcome,
                'form_completed_at' => $submitted ? $date->startOfDay() : null,
                'payment_started_at' => $status === EnrollmentStatus::PaymentInProgress ? $date->startOfDay() : null,
                'paid_at' => $paid ? $date->startOfDay() : null,
                'assigned_at' => $assigned ? $date->startOfDay() : null,
                'finalized_at' => $status === EnrollmentStatus::Finalized ? $date->endOfDay() : null,
                'exception_until' => null,
                'assignment_issue' => null,
            ],
        );
        $this->form($enrollment, $submitted);

        return $enrollment;
    }

    private function form(Enrollment $enrollment, bool $submitted): void
    {
        $student = $enrollment->student;
        $representative = $student->representative;
        $data = [
            'student' => [
                'first_name' => $student->first_name, 'last_name' => $student->last_name,
                'birth_date' => CarbonImmutable::parse($student->birth_date)->toDateString(),
                'preferred_name' => $student->first_name, 'gender' => 'no especificado',
                'nationality' => 'Ecuatoriana', 'birth_place' => 'Quito',
                'previous_institution' => 'Centro Infantil Semillitas', 'previous_level' => 'Nivel anterior',
                'academic_background' => 'Desarrollo acorde a la edad.',
            ],
            'health' => ['developmental_notes' => 'Desarrollo esperado.', 'care_instructions' => 'Mantener hidratación.', 'additional_notes' => 'Caso CMP.'],
            'conditions' => ['none' => false, 'items' => [['name' => 'Asma leve', 'details' => 'Episodios esporádicos.', 'care_instructions' => 'Avisar al representante.']]],
            'allergies' => ['none' => false, 'items' => [['allergen' => 'Maní', 'severity' => 'alta', 'reaction' => 'Urticaria', 'response_instructions' => 'Avisar al representante.']]],
            'medications' => ['none' => false, 'items' => [['name' => 'Inhalador de rescate', 'dose' => 'Según prescripción', 'schedule' => 'Solo emergencia', 'prescriber' => 'Dra. Elena Paz', 'instructions' => 'Uso autorizado.']]],
            'address' => ['country' => 'Ecuador', 'province' => 'Pichincha', 'city' => 'Quito', 'parish' => 'Iñaquito', 'main_street' => 'Av. Cumplimiento', 'secondary_street' => 'Calle Pruebas', 'house_number' => 'CMP-10', 'reference' => 'Frente al parque'],
            'legal_representative' => ['relationship' => 'padre', 'id_type' => 'cedula', 'id_number' => $representative->id_card, 'first_name' => $representative->first_name, 'last_name' => $representative->last_name, 'email' => $representative->email, 'phone' => $representative->phone, 'occupation' => 'Analista', 'workplace' => 'Empresa Demo', 'work_phone' => '022900001'],
            'billing' => ['person_type' => 'natural', 'tax_id_type' => 'cedula', 'tax_id' => $representative->id_card, 'business_name' => $representative->first_name.' '.$representative->last_name, 'email' => $representative->email, 'phone' => $representative->phone, 'address' => 'Quito'],
            'emergency_contacts' => ['items' => [['full_name' => $representative->first_name.' '.$representative->last_name, 'relationship' => 'padre', 'phone' => $representative->phone, 'alternate_phone' => '022900002', 'authorized_pickup' => true]]],
            'insurance' => ['has_insurance' => true, 'provider' => 'Seguro Demo', 'policy_number' => "POL-{$student->id_card}", 'plan_name' => 'Infantil', 'emergency_phone' => '1800000000'],
        ];
        EnrollmentForm::query()->updateOrCreate(['enrollment_id' => $enrollment->id], [
            'current_step' => $submitted ? 'review' : 'address',
            'draft_data' => $data,
            'submitted_data' => $submitted ? $data : null,
            'snapshot_data' => $submitted ? $data : null,
            'privacy_policy_version' => $submitted ? '2026-01' : null,
            'medical_consent_version' => $submitted ? '2026-01' : null,
            'emergency_consent_version' => $submitted ? '2026-01' : null,
            'consented_at' => $submitted ? now()->subDays(3) : null,
            'consent_ip' => $submitted ? '127.0.0.1' : null,
            'consented_by' => $submitted ? $representative->user_id : null,
        ]);
    }

    /** @param list<Enrollment> $enrollments */
    private function closedPeriodBilling(
        array $enrollments,
        AcademicPeriod $olderPeriod,
        AcademicPeriod $previousPeriod,
        Student $debtorStudent,
    ): void {
        foreach ($enrollments as $enrollment) {
            if (! in_array($enrollment->academic_period_id, [$olderPeriod->id, $previousPeriod->id], true)) {
                continue;
            }

            $level = $enrollment->level;
            $this->paidEnrollmentTuition($enrollment, $level, $enrollment->enrollment_date);
            $month = CarbonImmutable::parse($enrollment->academicPeriod->starts_on)->startOfMonth();
            $lastMonth = CarbonImmutable::parse($enrollment->academicPeriod->ends_on)->startOfMonth();
            $monthNumber = 1;

            while ($month->lte($lastMonth)) {
                $isDebtorMonth = $enrollment->academic_period_id === $previousPeriod->id
                    && $enrollment->student_id === $debtorStudent->id
                    && $monthNumber <= 5;
                $tuition = Tuition::query()->create([
                    'student_id' => $enrollment->student_id,
                    'enrollment_id' => null,
                    'concept' => TuitionConcept::Monthly,
                    'amount' => $level->monthly_fee,
                    'generation_date' => $month,
                    'billing_period' => $month,
                    'due_date' => $month->addDays(10),
                    'status' => $isDebtorMonth ? TuitionStatus::Overdue : TuitionStatus::Paid,
                ]);

                if (! $isDebtorMonth) {
                    Payment::query()->create([
                        'tuition_id' => $tuition->id,
                        'payment_method' => PaymentMethod::Transfer,
                        'amount_paid' => $level->monthly_fee,
                        'payment_date' => $month->addDays(5),
                        'reference_number' => "CMP-PENSION-{$enrollment->id}-{$month->format('Ym')}",
                    ]);
                }

                $month = $month->addMonth();
                $monthNumber++;
            }
        }
    }

    /** @param list<Enrollment> $enrollments */
    private function currentEnrollmentBilling(array $enrollments, CarbonImmutable $today): void
    {
        foreach ($enrollments as $enrollment) {
            if ($enrollment->academicPeriod->status !== AcademicPeriodStatus::Open
                || $enrollment->status === EnrollmentStatus::Draft) {
                continue;
            }

            if (in_array($enrollment->status, [EnrollmentStatus::PendingPayment, EnrollmentStatus::PaymentInProgress], true)) {
                $tuition = $this->pendingEnrollmentTuition($enrollment, $enrollment->level, $today);

                if ($enrollment->status === EnrollmentStatus::PaymentInProgress) {
                    $this->payphoneAttempt($tuition, $today);
                }

                continue;
            }

            $this->paidEnrollmentTuition($enrollment, $enrollment->level, $today->subDays(2));
        }
    }

    private function pendingEnrollmentTuition(Enrollment $enrollment, Level $level, CarbonInterface $date): Tuition
    {
        return Tuition::query()->create([
            'student_id' => $enrollment->student_id,
            'enrollment_id' => $enrollment->id,
            'concept' => TuitionConcept::Enrollment,
            'amount' => $level->enrollment_fee,
            'generation_date' => $date,
            'billing_period' => null,
            'due_date' => $date->addDays(5),
            'status' => TuitionStatus::Pending,
        ]);
    }

    private function paidEnrollmentTuition(Enrollment $enrollment, Level $level, CarbonInterface $date): void
    {
        $tuition = Tuition::query()->create([
            'student_id' => $enrollment->student_id,
            'enrollment_id' => $enrollment->id,
            'concept' => TuitionConcept::Enrollment,
            'amount' => $level->enrollment_fee,
            'generation_date' => $date->subDays(2),
            'billing_period' => null,
            'due_date' => $date->addDays(3),
            'status' => TuitionStatus::Paid,
        ]);
        Payment::query()->create([
            'tuition_id' => $tuition->id,
            'payment_method' => PaymentMethod::Transfer,
            'amount_paid' => $level->enrollment_fee,
            'payment_date' => $date,
            'reference_number' => "CMP-MATRICULA-{$enrollment->id}",
        ]);
    }

    private function payphoneAttempt(Tuition $tuition, CarbonImmutable $today): void
    {
        $attempt = PayphonePaymentAttempt::query()->updateOrCreate(
            ['client_transaction_id' => '00000000-0000-4000-8000-000000000004'],
            [
                'tuition_id' => $tuition->id,
                'amount_in_cents' => (int) round((float) $tuition->amount * 100),
                'status' => PayphonePaymentStatus::Prepared,
                'payphone_payment_id' => 'cmp-prepared-payment',
                'payment_url' => 'https://payphone.test/cmp-prepared-payment',
                'transaction_id' => null,
                'expires_at' => $today->addDay()->endOfDay(),
                'confirmed_at' => null,
            ],
        );
        PayphonePaymentAttemptItem::query()->updateOrCreate(
            ['payphone_payment_attempt_id' => $attempt->id, 'tuition_id' => $tuition->id],
            ['amount_in_cents' => $attempt->amount_in_cents],
        );
    }

    /**
     * @param  array<string, Level>  $levels
     */
    private function admissions(array $levels, AcademicPeriod $currentPeriod): void
    {
        $statuses = [AdmissionStatus::Pending, AdmissionStatus::Approved, AdmissionStatus::Rejected];

        foreach (self::LEVEL_NAMES as $levelIndex => $levelName) {
            foreach ($statuses as $statusIndex => $status) {
                $email = "compliance.admission.{$levelIndex}.{$status->value}@cenestur.test";
                $approvedStudent = $status === AdmissionStatus::Approved
                    ? Student::query()->whereHas('enrollments', fn ($query) => $query
                        ->where('academic_period_id', $currentPeriod->id)
                        ->where('level_id', $levels[$levelName]->id))->firstOrFail()
                    : null;
                Admission::query()->updateOrCreate(
                    ['contact_email' => $email],
                    [
                        'level_id' => $levels[$levelName]->id,
                        'applicant_first_name' => "Aspirante {$levelIndex}{$statusIndex}",
                        'applicant_last_name' => $levelName,
                        'applicant_birth_date' => CarbonImmutable::parse($currentPeriod->starts_on)->subYears(4),
                        'representative_names' => "Representante Admisión {$levelIndex}{$statusIndex}",
                        'contact_phone' => '098800'.str_pad((string) ($levelIndex * 3 + $statusIndex), 4, '0', STR_PAD_LEFT),
                        'status' => $status,
                        'representative_id' => $approvedStudent?->representative_id,
                        'student_id' => $approvedStudent?->id,
                        'application_date' => CarbonImmutable::parse($currentPeriod->enrollment_opens_at)->addDays($levelIndex * 3 + $statusIndex),
                    ],
                );
            }
        }
    }

    /** @param list<Enrollment> $enrollments */
    private function audits(array $enrollments, User $admin): void
    {
        EnrollmentAudit::query()->whereIn('enrollment_id', collect($enrollments)->pluck('id'))->delete();

        foreach ($enrollments as $enrollment) {
            EnrollmentAudit::query()->create([
                'enrollment_id' => $enrollment->id,
                'user_id' => $admin->id,
                'event' => "compliance.seeded.{$enrollment->status->value}",
                'metadata' => ['fixture' => 'CMP', 'status' => $enrollment->status->value],
                'ip_address' => '127.0.0.1',
            ]);
        }
    }

    private function recordAccess(Student $student, Teacher $teacher): void
    {
        StudentRecordAccessLog::query()
            ->whereIn('student_id', Student::query()->where('id_card', 'like', 'CMP-EST-%')->select('id'))
            ->where('reason', 'Verificación de cumplimiento CMP')
            ->delete();
        StudentRecordAccessLog::query()->create([
            'student_id' => $student->id,
            'user_id' => $teacher->user_id,
            'scope' => 'medical',
            'reason' => 'Verificación de cumplimiento CMP',
            'ip_address' => '127.0.0.1',
        ]);
    }
}
