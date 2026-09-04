<?php

use App\Enums\EnrollmentStatus;
use App\Enums\TuitionConcept;
use App\Enums\TuitionStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\Representative;
use App\Models\Student;
use App\Models\Tuition;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\TuitionService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Laravel\Sanctum\Sanctum;

test('monthly generation creates one tuition per active enrollment', function () {
    $level = Level::factory()->create([
        'enrollment_fee' => 80,
        'monthly_fee' => 150,
    ]);
    $course = Course::factory()->for($level)->create();
    $student = Student::factory()->create();
    Enrollment::factory()->for($student)->for($course)->create(['status' => EnrollmentStatus::Active]);

    $created = app(TuitionService::class)->generateMonthlyTuitions(Carbon::parse('2026-09-02'));

    expect($created)->toBe(1);
    $this->assertDatabaseHas('tuitions', [
        'student_id' => $student->id,
        'amount' => 150,
        'generation_date' => '2026-09-02 00:00:00',
        'billing_period' => '2026-09-01 00:00:00',
        'due_date' => '2026-09-10 00:00:00',
        'status' => TuitionStatus::Pending->value,
    ]);
});

test('monthly generation is idempotent for the same student and month', function () {
    $level = Level::factory()->create(['enrollment_fee' => 80, 'monthly_fee' => 150]);
    $course = Course::factory()->for($level)->create();
    $student = Student::factory()->create();
    Enrollment::factory()->for($student)->for($course)->create(['status' => EnrollmentStatus::Active]);
    $service = app(TuitionService::class);

    $service->generateMonthlyTuitions(Carbon::parse('2026-09-02'));
    $createdAgain = $service->generateMonthlyTuitions(Carbon::parse('2026-09-20'));

    expect($createdAgain)->toBe(0)
        ->and(Tuition::whereBelongsTo($student)->count())->toBe(1);
});

test('database prevents two tuitions for the same student and monthly period', function () {
    $student = Student::factory()->create();

    Tuition::factory()->for($student)->create([
        'generation_date' => '2026-09-02',
        'billing_period' => '2026-09-01',
    ]);

    expect(fn () => Tuition::factory()->for($student)->create([
        'generation_date' => '2026-09-20',
        'billing_period' => '2026-09-01',
    ]))->toThrow(QueryException::class);
});

test('administrative tuition creation reports a duplicate monthly period', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $student = Student::factory()->create();

    Tuition::factory()->for($student)->create([
        'generation_date' => '2026-09-02',
        'billing_period' => '2026-09-01',
    ]);

    $this->postJson('/api/tuitions', [
        'student_id' => $student->id,
        'amount' => 150,
        'generation_date' => '2026-09-20',
        'due_date' => '2026-10-10',
        'status' => TuitionStatus::Pending->value,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('generation_date');
});

test('an enrollment charge can coexist with the monthly pension for the same month', function () {
    $this->travelTo(Carbon::parse('2026-09-20'));
    $user = User::factory()->representative()->create();
    $representative = Representative::factory()->create(['user_id' => $user->id]);
    $student = Student::factory()->for($representative)->create();
    $level = Level::factory()->create(['enrollment_fee' => 80, 'monthly_fee' => 150]);
    $course = Course::factory()->for($level)->create();
    Tuition::factory()->for($student)->create([
        'generation_date' => '2026-09-02',
        'billing_period' => '2026-09-01',
    ]);

    app(EnrollmentService::class)->enroll($student, $course, $user);

    expect(Tuition::query()->whereBelongsTo($student)->count())->toBe(2)
        ->and(Tuition::query()->whereBelongsTo($student)->where('concept', TuitionConcept::Monthly)->count())->toBe(1)
        ->and(Tuition::query()->whereBelongsTo($student)->where('concept', TuitionConcept::Enrollment)->count())->toBe(1);
});
