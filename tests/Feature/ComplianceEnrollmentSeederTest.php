<?php

use App\Enums\AcademicPeriodStatus;
use App\Enums\AdmissionStatus;
use App\Enums\AssignedRole;
use App\Enums\EnrollmentStatus;
use App\Enums\PayphonePaymentStatus;
use App\Enums\TuitionStatus;
use App\Models\AcademicPeriod;
use App\Models\Admission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PayphonePaymentAttempt;
use App\Models\Representative;
use App\Models\Student;
use App\Models\StudentRecordAccessLog;
use App\Models\Tuition;
use App\Models\User;
use Database\Seeders\ComplianceEnrollmentSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\seed;
use function Pest\Laravel\travelTo;

it('creates two closed periods and one current enrollment period across every level', function () {
    travelTo('2026-09-04 10:00:00');

    seed(ComplianceEnrollmentSeeder::class);

    $periods = AcademicPeriod::query()->where('name', 'like', 'CMP |%')->get();

    expect($periods)->toHaveCount(3);
    expect($periods->where('status', AcademicPeriodStatus::Closed))->toHaveCount(2);
    expect($periods->where('status', AcademicPeriodStatus::Open))->toHaveCount(1);

    foreach ($periods as $period) {
        expect($period->enrollments()->distinct()->count('level_id'))->toBe(5);
    }

    expect(Student::query()->where('id_card', 'like', 'CMP-EST-%')->count())->toBe(32);
    expect(Enrollment::query()->whereHas('student', fn ($query) => $query->where('id_card', 'like', 'CMP-EST-%'))->count())->toBe(43);

    $statuses = Enrollment::query()
        ->whereHas('student', fn ($query) => $query->where('id_card', 'like', 'CMP-EST-%'))
        ->pluck('status');

    expect($statuses)->toContain(
        EnrollmentStatus::Draft,
        EnrollmentStatus::PendingPayment,
        EnrollmentStatus::PaymentInProgress,
        EnrollmentStatus::PaidPendingAssignment,
        EnrollmentStatus::Active,
        EnrollmentStatus::Finalized,
    );
    expect(Tuition::query()->where('status', TuitionStatus::Overdue)->count())->toBe(5);
    expect(PayphonePaymentAttempt::query()->where('status', PayphonePaymentStatus::Prepared)->count())->toBe(1);
    expect(StudentRecordAccessLog::query()->where('reason', 'Verificación de cumplimiento CMP')->count())->toBe(1);

    $representative = User::query()->where('email', 'compliance.parent@cenestur.test')->firstOrFail();
    expect(Hash::check('password', $representative->password))->toBeTrue();
});

it('creates single and multiple child families with half a prior period overdue', function () {
    travelTo('2026-09-04 10:00:00');

    seed(ComplianceEnrollmentSeeder::class);

    $multipleChildren = Representative::query()->where('email', 'compliance.parent@cenestur.test')->firstOrFail();
    $singleChild = Representative::query()->where('email', 'compliance.single@cenestur.test')->firstOrFail();
    $debtorStudent = Student::query()->where('id_card', 'CMP-EST-004')->firstOrFail();
    $previousPeriod = AcademicPeriod::query()->where('name', 'CMP | Periodo cerrado 2')->firstOrFail();
    $overdueTuitions = Tuition::query()
        ->where('student_id', $debtorStudent->id)
        ->where('status', TuitionStatus::Overdue)
        ->orderBy('billing_period')
        ->get();

    expect($multipleChildren->students()->count())->toBe(2);
    expect($singleChild->students()->count())->toBe(1);
    expect($overdueTuitions)->toHaveCount(5);
    expect($overdueTuitions->firstOrFail()->billing_period->gte($previousPeriod->starts_on))->toBeTrue();
    expect($overdueTuitions->last()->billing_period->lte($previousPeriod->ends_on))->toBeTrue();
});

it('creates overloaded maternal parallels with a principal and several auxiliaries', function () {
    travelTo('2026-09-04 10:00:00');

    seed(ComplianceEnrollmentSeeder::class);

    foreach (['Maternal 1' => 13, 'Maternal 2' => 14] as $levelName => $studentCount) {
        $course = Course::query()
            ->where('parallel', 'CMP-A')
            ->whereHas('level', fn ($query) => $query->where('name', $levelName))
            ->firstOrFail();

        expect($course->enrollments()->where('status', EnrollmentStatus::Active)->count())->toBe($studentCount);
        expect($studentCount)->toBeGreaterThan($course->level->max_capacity);
        expect($course->courseTeachers()->where('assigned_role', AssignedRole::Principal)->count())->toBe(1);
        expect($course->courseTeachers()->where('assigned_role', AssignedRole::Auxiliary)->count())->toBe(4);
    }
});

it('creates current admissions in every level and processing status', function () {
    travelTo('2026-09-04 10:00:00');

    seed(ComplianceEnrollmentSeeder::class);

    $currentPeriod = AcademicPeriod::query()->where('name', 'CMP | Periodo vigente')->firstOrFail();
    $admissions = Admission::query()->where('contact_email', 'like', 'compliance.admission.%')->get();

    expect($admissions)->toHaveCount(15);
    expect($admissions->pluck('level_id')->unique())->toHaveCount(5);
    expect($admissions->where('status', AdmissionStatus::Pending))->toHaveCount(5);
    expect($admissions->where('status', AdmissionStatus::Approved))->toHaveCount(5);
    expect($admissions->where('status', AdmissionStatus::Rejected))->toHaveCount(5);
    expect($admissions->every(fn (Admission $admission) => Carbon::parse($admission->application_date)->between(
        $currentPeriod->enrollment_opens_at,
        $currentPeriod->enrollment_closes_at,
    )))->toBeTrue();
});

it('can run repeatedly without accumulating compliance records', function () {
    travelTo('2026-09-04 10:00:00');

    seed(ComplianceEnrollmentSeeder::class);
    seed(ComplianceEnrollmentSeeder::class);

    expect(AcademicPeriod::query()->where('name', 'like', 'CMP |%')->count())->toBe(3);
    expect(Representative::query()->where('id_card', 'like', 'CMP-REP-DOC-%')->orWhere('id_card', 'like', '179900000%')->count())->toBe(17);
    expect(Student::query()->where('id_card', 'like', 'CMP-EST-%')->count())->toBe(32);
    expect(Enrollment::query()->whereHas('student', fn ($query) => $query->where('id_card', 'like', 'CMP-EST-%'))->count())->toBe(43);
    expect(Admission::query()->where('contact_email', 'like', 'compliance.admission.%')->count())->toBe(15);
    expect(Tuition::query()->whereIn('student_id', Student::query()->where('id_card', 'like', 'CMP-EST-%')->select('id'))->count())->toBe(162);
    expect(StudentRecordAccessLog::query()->where('reason', 'Verificación de cumplimiento CMP')->count())->toBe(1);
});
