<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Enums\TuitionConcept;
use App\Enums\TuitionStatus;
use App\Events\MonthlyTuitionsGenerated;
use App\Models\Enrollment;
use App\Models\Tuition;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TuitionService
{
    /**
     * Generate monthly tuitions for all active enrollments.
     *
     * @return int Number of tuitions created.
     */
    public function generateMonthlyTuitions(Carbon $generationDate): int
    {
        $billingPeriod = $generationDate->copy()->startOfMonth();
        $dueDate = $generationDate->copy()->setDay(10);

        if ($dueDate->lessThan($generationDate)) {
            $dueDate->addMonth();
        }

        $enrollments = Enrollment::with(['student.representative.user', 'course.level'])
            ->where('status', EnrollmentStatus::Active)
            ->get();

        $created = 0;
        $notifiedUserIds = collect();

        DB::transaction(function () use ($enrollments, $generationDate, $billingPeriod, $dueDate, &$created, &$notifiedUserIds): void {
            foreach ($enrollments as $enrollment) {
                $tuition = Tuition::firstOrCreate([
                    'student_id' => $enrollment->student_id,
                    'billing_period' => $billingPeriod->toDateString(),
                ], [
                    'concept' => TuitionConcept::Monthly,
                    'amount' => $enrollment->course->level->monthly_fee,
                    'generation_date' => $generationDate->toDateString(),
                    'due_date' => $dueDate->toDateString(),
                    'status' => TuitionStatus::Pending,
                ]);

                if (! $tuition->wasRecentlyCreated) {
                    continue;
                }

                $created++;

                $user = $enrollment->student->representative?->user;

                if ($user) {
                    $notifiedUserIds->push($user->id);
                }
            }
        });

        if ($notifiedUserIds->isNotEmpty()) {
            MonthlyTuitionsGenerated::dispatch($notifiedUserIds->unique()->values()->all());
        }

        return $created;
    }
}
