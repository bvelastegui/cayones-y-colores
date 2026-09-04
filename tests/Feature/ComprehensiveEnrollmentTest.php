<?php

use App\Enums\AcademicPeriodStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\PayphonePaymentStatus;
use App\Enums\TuitionConcept;
use App\Enums\TuitionStatus;
use App\Models\AcademicPeriod;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\PayphonePaymentAttempt;
use App\Models\Representative;
use App\Models\Student;
use App\Models\Tuition;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;

test('a representative completes the integral form before an enrollment charge is generated', function () {
    $this->travelTo(Carbon::parse('2026-09-03 10:00:00'));
    $user = User::factory()->representative()->create();
    $representative = Representative::factory()->create(['user_id' => $user->id]);
    $level = Level::factory()->create(['enrollment_fee' => 175, 'monthly_fee' => 210]);
    $student = Student::factory()->for($representative)->create(['level_id' => $level->id]);
    AcademicPeriod::factory()->create([
        'status' => AcademicPeriodStatus::Open,
        'enrollment_opens_at' => now()->subDay(),
        'enrollment_closes_at' => now()->addDay(),
        'starts_on' => now()->addWeek(),
        'ends_on' => now()->addYear(),
    ]);
    Sanctum::actingAs($user);

    $enrollment = $this->postJson("/api/me/students/{$student->id}/enrollments")
        ->assertCreated()
        ->assertJsonPath('status', EnrollmentStatus::Draft->value)
        ->assertJsonPath('course_id', null)
        ->json();

    expect(Tuition::query()->exists())->toBeFalse();

    foreach (integralEnrollmentSections() as $section => $payload) {
        $this->putJson("/api/me/enrollments/{$enrollment['id']}/form/{$section}", $payload)
            ->assertOk();
    }

    $this->postJson("/api/me/enrollments/{$enrollment['id']}/complete", [
        'accept_privacy_policy' => true,
        'accept_medical_data_processing' => true,
        'accept_emergency_authorization' => true,
    ])->assertOk()
        ->assertJsonPath('status', EnrollmentStatus::PendingPayment->value)
        ->assertJsonPath('tuition.amount', '175.00');

    $this->assertDatabaseHas('tuitions', [
        'enrollment_id' => $enrollment['id'],
        'student_id' => $student->id,
        'concept' => TuitionConcept::Enrollment->value,
        'billing_period' => null,
        'amount' => 175,
    ]);
    expect($student->allergies()->firstOrFail()->allergen)->toBe('Maní')
        ->and($student->allergies()->firstOrFail()->severity)->toBe('severe');
});

test('overdue monthly pensions block completion of the enrollment form', function () {
    $user = User::factory()->representative()->create();
    $representative = Representative::factory()->create(['user_id' => $user->id]);
    $level = Level::factory()->create();
    $student = Student::factory()->for($representative)->create(['level_id' => $level->id]);
    AcademicPeriod::factory()->create();
    Tuition::factory()->for($student)->create([
        'concept' => TuitionConcept::Monthly,
        'status' => TuitionStatus::Overdue,
        'due_date' => today()->subDay(),
    ]);
    Sanctum::actingAs($user);

    $enrollmentId = $this->postJson("/api/me/students/{$student->id}/enrollments")->json('id');
    foreach (integralEnrollmentSections() as $section => $payload) {
        $this->putJson("/api/me/enrollments/{$enrollmentId}/form/{$section}", $payload)->assertOk();
    }

    $this->postJson("/api/me/enrollments/{$enrollmentId}/complete", [
        'accept_privacy_policy' => true,
        'accept_medical_data_processing' => true,
        'accept_emergency_authorization' => true,
    ])->assertUnprocessable()->assertJsonValidationErrors('tuitions');
});

test('a representative cannot start a new enrollment payment after the enrollment window closes', function () {
    $user = User::factory()->representative()->create();
    $representative = Representative::factory()->create(['user_id' => $user->id]);
    $level = Level::factory()->create();
    $student = Student::factory()->for($representative)->create(['level_id' => $level->id]);
    $period = AcademicPeriod::factory()->create([
        'status' => AcademicPeriodStatus::Open,
        'enrollment_closes_at' => now()->subMinute(),
    ]);
    $enrollment = Enrollment::factory()->for($student)->create([
        'academic_period_id' => $period->id,
        'level_id' => $level->id,
        'course_id' => null,
        'status' => EnrollmentStatus::PendingPayment,
    ]);
    Tuition::factory()->for($student)->create([
        'enrollment_id' => $enrollment->id,
        'concept' => TuitionConcept::Enrollment,
        'billing_period' => null,
    ]);
    Sanctum::actingAs($user);

    $this->postJson("/api/me/enrollments/{$enrollment->id}/payphone")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('period');
});

test('a representative can resume an unexpired prepared payment after the enrollment window closes', function () {
    $user = User::factory()->representative()->create();
    $representative = Representative::factory()->create(['user_id' => $user->id]);
    $level = Level::factory()->create();
    $student = Student::factory()->for($representative)->create(['level_id' => $level->id]);
    $period = AcademicPeriod::factory()->create([
        'status' => AcademicPeriodStatus::Open,
        'enrollment_closes_at' => now()->subMinute(),
    ]);
    $enrollment = Enrollment::factory()->for($student)->create([
        'academic_period_id' => $period->id,
        'level_id' => $level->id,
        'course_id' => null,
        'status' => EnrollmentStatus::PaymentInProgress,
    ]);
    $tuition = Tuition::factory()->for($student)->create([
        'enrollment_id' => $enrollment->id,
        'concept' => TuitionConcept::Enrollment,
        'billing_period' => null,
    ]);
    $attempt = PayphonePaymentAttempt::factory()->for($tuition)->create([
        'status' => PayphonePaymentStatus::Prepared,
        'payment_url' => 'https://payphone.test/prepared-payment',
        'payphone_payment_id' => 'prepared-payment',
        'expires_at' => now()->addMinutes(5),
    ]);
    $attempt->items()->create([
        'tuition_id' => $tuition->id,
        'amount_in_cents' => 10000,
    ]);
    Sanctum::actingAs($user);

    $this->postJson("/api/me/enrollments/{$enrollment->id}/payphone")
        ->assertOk()
        ->assertJsonPath('payment_url', 'https://payphone.test/prepared-payment');
});

/** @return array<string, array<string, mixed>> */
function integralEnrollmentSections(): array
{
    return [
        'student' => [
            'first_name' => 'Ana', 'last_name' => 'Pérez', 'birth_date' => '2021-02-10',
            'preferred_name' => 'Anita', 'gender' => 'femenino', 'nationality' => 'Ecuatoriana',
            'birth_place' => 'Quito', 'previous_institution' => '', 'previous_level' => '',
            'academic_background' => 'Primera experiencia escolar.',
        ],
        'health' => ['developmental_notes' => '', 'care_instructions' => 'Mantener hidratada.', 'additional_notes' => ''],
        'conditions' => ['none' => true, 'items' => []],
        'allergies' => ['none' => false, 'items' => [[
            'allergen' => 'Maní', 'severity' => 'severe', 'reaction' => 'Urticaria',
            'response_instructions' => 'Contactar al representante.',
        ]]],
        'medications' => ['none' => true, 'items' => []],
        'address' => [
            'country' => 'Ecuador', 'province' => 'Pichincha', 'city' => 'Quito', 'parish' => '',
            'main_street' => 'Av. Central', 'secondary_street' => '', 'house_number' => '12', 'reference' => '',
        ],
        'legal_representative' => [
            'relationship' => 'madre', 'id_type' => 'cedula', 'id_number' => '1712345678',
            'first_name' => 'María', 'last_name' => 'Pérez', 'email' => 'maria@example.com',
            'phone' => '0999999999', 'occupation' => '', 'workplace' => '', 'work_phone' => '',
        ],
        'billing' => [
            'person_type' => 'natural', 'tax_id_type' => 'cedula', 'tax_id' => '1712345678', 'business_name' => 'María Pérez',
            'email' => 'facturacion@example.com', 'phone' => '0999999999', 'address' => 'Av. Central 12',
        ],
        'emergency_contacts' => ['items' => [[
            'full_name' => 'José Pérez', 'relationship' => 'padre', 'phone' => '0988888888',
            'alternate_phone' => '', 'authorized_pickup' => true,
        ]]],
        'insurance' => [
            'has_insurance' => false, 'provider' => '', 'policy_number' => '', 'plan_name' => '',
            'emergency_phone' => '',
        ],
    ];
}
