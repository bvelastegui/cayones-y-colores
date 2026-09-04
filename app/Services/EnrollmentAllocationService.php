<?php

namespace App\Services;

use App\Contracts\Notifications\PushNotificationSender;
use App\Enums\AcademicPeriodStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\UserRole;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\EnrollmentAssignedNotification;
use App\Notifications\EnrollmentCapacityIssueNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentAllocationService
{
    public function __construct(
        private EnrollmentAuditService $auditService,
        private PushNotificationSender $pushNotificationSender,
    ) {}

    /** @return array{assigned: int, pending: int} */
    public function allocatePeriod(
        AcademicPeriod $period,
        ?User $actor = null,
        bool $force = false,
        ?string $reason = null,
        ?string $ipAddress = null,
    ): array {
        if (! $force && now()->lt($period->enrollment_closes_at)) {
            throw ValidationException::withMessages([
                'period' => ['La asignación automática se ejecuta al cerrar el periodo de matrículas.'],
            ]);
        }

        $assignedEnrollments = collect();
        $pendingEnrollments = collect();

        $result = DB::transaction(function () use (
            $period, $actor, $force, $reason, $ipAddress, &$assignedEnrollments, &$pendingEnrollments,
        ): array {
            $lockedPeriod = AcademicPeriod::query()->lockForUpdate()->findOrFail($period->id);
            $lockedPeriod->update([
                'status' => AcademicPeriodStatus::Allocating,
                'allocation_started_at' => now(),
                'allocation_completed_at' => null,
            ]);

            $enrollments = Enrollment::query()
                ->with(['student.representative', 'level'])
                ->where('academic_period_id', $lockedPeriod->id)
                ->where('status', EnrollmentStatus::PaidPendingAssignment)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            /** @var array<int, Collection<int, Course>> $coursesByLevel */
            $coursesByLevel = [];
            /** @var array<int, int> $activeCounts */
            $activeCounts = [];

            foreach ($enrollments as $enrollment) {
                if (! isset($coursesByLevel[$enrollment->level_id])) {
                    $courses = Course::query()
                        ->with('level')
                        ->where('level_id', $enrollment->level_id)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get();

                    $coursesByLevel[$enrollment->level_id] = $courses;

                    foreach ($courses as $course) {
                        $activeCounts[$course->id] = Enrollment::query()
                            ->where('course_id', $course->id)
                            ->where('status', EnrollmentStatus::Active)
                            ->count();
                    }
                }

                $course = $coursesByLevel[$enrollment->level_id]
                    ->filter(fn (Course $candidate): bool => $activeCounts[$candidate->id] < $candidate->level->max_capacity)
                    ->sortBy(fn (Course $candidate): string => sprintf('%010d-%010d', $activeCounts[$candidate->id], $candidate->id))
                    ->first();

                if (! $course instanceof Course) {
                    $enrollment->update(['assignment_issue' => 'no_capacity']);
                    $this->auditService->record($enrollment, $actor, 'assignment_failed_no_capacity', [
                        'forced' => $force,
                        'reason' => $reason,
                    ], $ipAddress);
                    $pendingEnrollments->push($enrollment->fresh(['student', 'level']));

                    continue;
                }

                $enrollment->update([
                    'course_id' => $course->id,
                    'status' => EnrollmentStatus::Active,
                    'assigned_at' => now(),
                    'assignment_issue' => null,
                ]);
                $activeCounts[$course->id]++;

                $this->auditService->record($enrollment, $actor, 'course_assigned', [
                    'course_id' => $course->id,
                    'forced' => $force,
                    'reason' => $reason,
                ], $ipAddress);
                $assignedEnrollments->push($enrollment->fresh([
                    'student.representative.user', 'level', 'course',
                ]));
            }

            $lockedPeriod->update([
                'status' => AcademicPeriodStatus::Allocated,
                'allocation_completed_at' => now(),
            ]);

            return ['assigned' => $assignedEnrollments->count(), 'pending' => $pendingEnrollments->count()];
        }, 3);

        $this->sendNotifications($assignedEnrollments, $pendingEnrollments);

        return $result;
    }

    public function assignManually(
        Enrollment $enrollment,
        Course $course,
        User $actor,
        string $reason,
        ?string $ipAddress = null,
    ): Enrollment {
        $assignedEnrollment = DB::transaction(function () use ($enrollment, $course, $actor, $reason, $ipAddress): Enrollment {
            $lockedEnrollment = Enrollment::query()->lockForUpdate()->findOrFail($enrollment->id);
            $lockedCourse = Course::query()->with('level')->lockForUpdate()->findOrFail($course->id);

            if (! in_array($lockedEnrollment->status, [EnrollmentStatus::PaidPendingAssignment, EnrollmentStatus::Active], true)) {
                throw ValidationException::withMessages([
                    'enrollment' => ['Solo se puede asignar una matrícula pagada o reasignar una matrícula activa.'],
                ]);
            }

            if ($lockedEnrollment->level_id !== $lockedCourse->level_id) {
                throw ValidationException::withMessages(['course_id' => ['El paralelo no pertenece al nivel de la matrícula.']]);
            }

            $activeCount = Enrollment::query()
                ->where('course_id', $lockedCourse->id)
                ->where('status', EnrollmentStatus::Active)
                ->where('id', '!=', $lockedEnrollment->id)
                ->count();

            if ($activeCount >= $lockedCourse->level->max_capacity) {
                throw ValidationException::withMessages(['course_id' => ['El paralelo alcanzó su aforo máximo.']]);
            }

            $previousCourseId = $lockedEnrollment->course_id;
            $lockedEnrollment->update([
                'course_id' => $lockedCourse->id,
                'status' => EnrollmentStatus::Active,
                'assigned_at' => now(),
                'assignment_issue' => null,
            ]);

            $this->auditService->record($lockedEnrollment, $actor, 'course_assigned_manually', [
                'previous_course_id' => $previousCourseId,
                'course_id' => $lockedCourse->id,
                'reason' => $reason,
            ], $ipAddress);

            return $lockedEnrollment->fresh(['student.representative.user', 'level', 'course']);
        }, 3);

        $this->sendNotifications(collect([$assignedEnrollment]), collect());

        return $assignedEnrollment;
    }

    /**
     * @param  Collection<int, Enrollment>  $assigned
     * @param  Collection<int, Enrollment>  $pending
     */
    private function sendNotifications(Collection $assigned, Collection $pending): void
    {
        foreach ($assigned as $enrollment) {
            $user = $enrollment->student->representative->user;

            if ($user === null) {
                continue;
            }

            $user->notify(new EnrollmentAssignedNotification($enrollment));
            try {
                $this->pushNotificationSender->sendToUsers(
                    collect([$user->id]),
                    'Paralelo asignado',
                    "{$enrollment->student->full_name} fue asignado al paralelo {$enrollment->course->parallel}.",
                    ['url' => '/parent'],
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        if ($pending->isEmpty()) {
            return;
        }

        $admins = User::query()->where('role', UserRole::Admin)->where('is_active', true)->get();
        foreach ($pending as $enrollment) {
            foreach ($admins as $admin) {
                $admin->notify(new EnrollmentCapacityIssueNotification($enrollment));
            }
        }
    }
}
