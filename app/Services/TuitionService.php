<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Enums\TuitionStatus;
use App\Models\Enrollment;
use App\Models\Tuition;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TuitionService
{
    public function __construct(private PushNotificationService $pushNotificationService) {}

    /**
     * Generate monthly tuitions for all active enrollments.
     *
     * @return int Number of tuitions created.
     */
    public function generateMonthlyTuitions(Carbon $generationDate): int
    {
        $dueDate = $generationDate->copy()->setDay(10);

        if ($dueDate->lessThan($generationDate)) {
            $dueDate->addMonth();
        }

        $enrollments = Enrollment::with(['student.representative.user', 'course.level'])
            ->where('status', EnrollmentStatus::Active)
            ->get();

        $created = 0;
        $notifiedUsers = collect();

        DB::transaction(function () use ($enrollments, $generationDate, $dueDate, &$created, &$notifiedUsers): void {
            foreach ($enrollments as $enrollment) {
                $alreadyExists = Tuition::where('student_id', $enrollment->student_id)
                    ->whereYear('generation_date', $generationDate->year)
                    ->whereMonth('generation_date', $generationDate->month)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                Tuition::create([
                    'student_id' => $enrollment->student_id,
                    'amount' => $enrollment->course->level->monthly_fee,
                    'generation_date' => $generationDate->toDateString(),
                    'due_date' => $dueDate->toDateString(),
                    'status' => TuitionStatus::Pending,
                ]);

                $created++;

                $user = $enrollment->student->representative?->user;

                if ($user) {
                    $notifiedUsers->push($user);
                }
            }
        });

        $this->notifyParents($notifiedUsers->unique('id'));

        return $created;
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function notifyParents(Collection $users): void
    {
        foreach ($users as $user) {
            $this->pushNotificationService->sendToUser(
                $user,
                'Nueva pensión generada',
                'Se generó una nueva pensión mensual. Ingresa al portal para revisar los detalles.',
                ['url' => '/parent/payments'],
            );
        }

        $this->pushNotificationService->flush();
    }
}
