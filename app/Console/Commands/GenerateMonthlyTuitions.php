<?php

namespace App\Console\Commands;

use App\Services\TuitionService;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tuitions:generate {--date= : Fecha de generación (Y-m-d), por defecto hoy}')]
#[Description('Genera las pensiones mensuales para los estudiantes matriculados.')]
class GenerateMonthlyTuitions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(TuitionService $tuitionService): int
    {
        $dateOption = $this->option('date');
        $generationDate = $dateOption ? Carbon::createFromFormat('Y-m-d', $dateOption) : Carbon::today();

        if (! $generationDate instanceof Carbon) {
            $this->error('El formato de fecha debe ser Y-m-d.');

            return self::FAILURE;
        }

        $created = $tuitionService->generateMonthlyTuitions($generationDate);

        $this->info("Se generaron {$created} pensiones para {$generationDate->format('Y-m-d')}.");

        return self::SUCCESS;
    }
}
