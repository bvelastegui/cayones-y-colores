<?php

namespace App\Services;

use App\Enums\EnrollmentOutcome;
use App\Enums\EnrollmentStatus;
use App\Enums\StudentLifecycleStatus;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentOutcomeService
{
    public function __construct(private EnrollmentAuditService $auditService) {}

    public function complete(
        Enrollment $enrollment,
        EnrollmentOutcome $outcome,
        User $actor,
        ?Level $overrideLevel = null,
        ?string $reason = null,
        ?string $ipAddress = null,
    ): Enrollment {
        return DB::transaction(function () use ($enrollment, $outcome, $actor, $overrideLevel, $reason, $ipAddress): Enrollment {
            $lockedEnrollment = Enrollment::query()->with(['student', 'level.nextLevel'])
                ->lockForUpdate()->findOrFail($enrollment->id);

            if ($lockedEnrollment->status !== EnrollmentStatus::Active) {
                throw ValidationException::withMessages([
                    'enrollment' => ['Solo una matrícula activa puede registrar el resultado del nivel.'],
                ]);
            }

            $nextLevel = $overrideLevel;
            $graduated = false;

            if ($nextLevel === null) {
                if ($outcome === EnrollmentOutcome::Completed) {
                    $nextLevel = $lockedEnrollment->level->nextLevel;
                    $graduated = $nextLevel === null;
                } else {
                    $nextLevel = $lockedEnrollment->level;
                }
            }

            $lockedEnrollment->student->update([
                'level_id' => $nextLevel?->id,
                'lifecycle_status' => $graduated ? StudentLifecycleStatus::Graduated : StudentLifecycleStatus::Active,
            ]);
            $lockedEnrollment->update([
                'level_outcome' => $outcome,
                'status' => $graduated ? EnrollmentStatus::Graduated : EnrollmentStatus::Finalized,
                'finalized_at' => now(),
            ]);

            $this->auditService->record($lockedEnrollment, $actor, 'level_outcome_recorded', [
                'outcome' => $outcome->value,
                'previous_level_id' => $lockedEnrollment->level_id,
                'next_level_id' => $nextLevel?->id,
                'override' => $overrideLevel !== null,
                'reason' => $reason,
            ], $ipAddress);

            return $lockedEnrollment->fresh(['student.level', 'level', 'course']);
        });
    }
}
