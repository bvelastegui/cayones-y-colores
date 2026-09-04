<?php

namespace App\Console\Commands;

use App\Enums\AcademicPeriodStatus;
use App\Models\AcademicPeriod;
use App\Services\EnrollmentAllocationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('enrollments:allocate {--period= : ID del periodo académico} {--force : Ejecutar antes del cierre}')]
#[Description('Asigna automáticamente paralelos a las matrículas pagadas')]
class AllocateEnrollmentCourses extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(EnrollmentAllocationService $allocationService): int
    {
        $periods = AcademicPeriod::query()
            ->when($this->option('period'), fn ($query, $id) => $query->whereKey($id))
            ->when(! $this->option('force'), fn ($query) => $query->where('enrollment_closes_at', '<=', now()))
            ->whereIn('status', [AcademicPeriodStatus::Open, AcademicPeriodStatus::Closed, AcademicPeriodStatus::Allocating])
            ->whereNull('allocation_completed_at')
            ->orderBy('enrollment_closes_at')
            ->get();

        foreach ($periods as $period) {
            $result = $allocationService->allocatePeriod($period, force: (bool) $this->option('force'));
            $this->info("{$period->name}: {$result['assigned']} asignadas, {$result['pending']} pendientes.");
        }

        return self::SUCCESS;
    }
}
