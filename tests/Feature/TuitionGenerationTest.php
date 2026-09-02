<?php

use App\Enums\EnrollmentStatus;
use App\Enums\TuitionStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\Student;
use App\Models\Tuition;
use App\Services\TuitionService;
use Carbon\Carbon;

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
