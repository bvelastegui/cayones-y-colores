<?php

use App\Contracts\Notifications\PushNotificationSender;
use App\Enums\AcademicPeriodStatus;
use App\Enums\EnrollmentOutcome;
use App\Enums\EnrollmentStatus;
use App\Enums\StudentLifecycleStatus;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\Student;
use App\Models\User;
use App\Services\EnrollmentAllocationService;
use App\Services\EnrollmentOutcomeService;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    $sender = Mockery::mock(PushNotificationSender::class);
    $sender->shouldReceive('sendToUsers')->zeroOrMoreTimes();
    app()->instance(PushNotificationSender::class, $sender);
});

test('paid enrollments are assigned to the least occupied course with id as tie breaker', function () {
    $level = Level::factory()->create(['max_capacity' => 2]);
    $firstCourse = Course::factory()->for($level)->create(['parallel' => 'A']);
    $secondCourse = Course::factory()->for($level)->create(['parallel' => 'B']);
    Enrollment::factory()->for($firstCourse)->create([
        'level_id' => $level->id,
        'status' => EnrollmentStatus::Active,
    ]);
    $period = AcademicPeriod::factory()->create([
        'status' => AcademicPeriodStatus::Closed,
        'enrollment_closes_at' => now()->subMinute(),
    ]);
    $firstPending = Enrollment::factory()->create([
        'academic_period_id' => $period->id,
        'level_id' => $level->id,
        'course_id' => null,
        'status' => EnrollmentStatus::PaidPendingAssignment,
    ]);
    $secondPending = Enrollment::factory()->create([
        'academic_period_id' => $period->id,
        'level_id' => $level->id,
        'course_id' => null,
        'status' => EnrollmentStatus::PaidPendingAssignment,
    ]);

    $result = app(EnrollmentAllocationService::class)->allocatePeriod($period);

    expect($result)->toBe(['assigned' => 2, 'pending' => 0])
        ->and($firstPending->refresh()->course_id)->toBe($secondCourse->id)
        ->and($secondPending->refresh()->course_id)->toBe($firstCourse->id)
        ->and($period->refresh()->status)->toBe(AcademicPeriodStatus::Allocated);
});

test('an enrollment remains paid and pending when its level has no capacity', function () {
    $level = Level::factory()->create(['max_capacity' => 1]);
    $course = Course::factory()->for($level)->create();
    Enrollment::factory()->for($course)->create(['level_id' => $level->id, 'status' => EnrollmentStatus::Active]);
    $period = AcademicPeriod::factory()->create([
        'status' => AcademicPeriodStatus::Closed,
        'enrollment_closes_at' => now()->subMinute(),
    ]);
    $pending = Enrollment::factory()->create([
        'academic_period_id' => $period->id,
        'level_id' => $level->id,
        'course_id' => null,
        'status' => EnrollmentStatus::PaidPendingAssignment,
    ]);

    $result = app(EnrollmentAllocationService::class)->allocatePeriod($period);

    expect($result)->toBe(['assigned' => 0, 'pending' => 1])
        ->and($pending->refresh()->status)->toBe(EnrollmentStatus::PaidPendingAssignment)
        ->and($pending->assignment_issue)->toBe('no_capacity');
});

test('a completed level promotes sequentially and the last level graduates the student', function () {
    $admin = User::factory()->admin()->create();
    $nextLevel = Level::factory()->create(['sequence_order' => 2]);
    $level = Level::factory()->create(['sequence_order' => 1, 'next_level_id' => $nextLevel->id]);
    $student = Student::factory()->create(['level_id' => $level->id]);
    $enrollment = Enrollment::factory()->for($student)->create([
        'level_id' => $level->id,
        'status' => EnrollmentStatus::Active,
    ]);

    app(EnrollmentOutcomeService::class)->complete($enrollment, EnrollmentOutcome::Completed, $admin);

    expect($student->refresh()->level_id)->toBe($nextLevel->id)
        ->and($student->lifecycle_status)->toBe(StudentLifecycleStatus::Active)
        ->and($enrollment->refresh()->status)->toBe(EnrollmentStatus::Finalized);

    $lastEnrollment = Enrollment::factory()->for($student)->create([
        'level_id' => $nextLevel->id,
        'status' => EnrollmentStatus::Active,
    ]);
    app(EnrollmentOutcomeService::class)->complete($lastEnrollment, EnrollmentOutcome::Completed, $admin);

    expect($student->refresh()->level_id)->toBeNull()
        ->and($student->lifecycle_status)->toBe(StudentLifecycleStatus::Graduated)
        ->and($lastEnrollment->refresh()->status)->toBe(EnrollmentStatus::Graduated);
});
