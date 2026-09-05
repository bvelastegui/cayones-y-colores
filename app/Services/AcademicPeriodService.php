<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcademicPeriodService
{
    /** @param array<string, mixed> $data */
    public function create(array $data): AcademicPeriod
    {
        return DB::transaction(function () use ($data): AcademicPeriod {
            AcademicPeriod::query()->lockForUpdate()->get();
            $this->ensureNoOverlap($data);

            return AcademicPeriod::create($data);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(AcademicPeriod $academicPeriod, array $data): AcademicPeriod
    {
        return DB::transaction(function () use ($academicPeriod, $data): AcademicPeriod {
            AcademicPeriod::query()->lockForUpdate()->get();
            $this->ensureNoOverlap(array_merge($academicPeriod->only([
                'name', 'starts_on', 'ends_on', 'enrollment_opens_at', 'enrollment_closes_at', 'status',
            ]), $data), $academicPeriod);
            $academicPeriod->update($data);

            return $academicPeriod->fresh();
        });
    }

    /** @param array<string, mixed> $data */
    private function ensureNoOverlap(array $data, ?AcademicPeriod $current = null): void
    {
        $baseQuery = AcademicPeriod::query()
            ->when($current, fn ($query) => $query->where('id', '!=', $current->id));

        $academicDatesOverlap = (clone $baseQuery)
            ->where('starts_on', '<=', $data['ends_on'])
            ->where('ends_on', '>=', $data['starts_on'])
            ->exists();

        $enrollmentWindowsOverlap = (clone $baseQuery)
            ->where('enrollment_opens_at', '<=', $data['enrollment_closes_at'])
            ->where('enrollment_closes_at', '>=', $data['enrollment_opens_at'])
            ->exists();

        if ($academicDatesOverlap || $enrollmentWindowsOverlap) {
            throw ValidationException::withMessages([
                'starts_on' => ['Los periodos académicos y sus ventanas de matrícula no pueden solaparse.'],
            ]);
        }
    }
}
