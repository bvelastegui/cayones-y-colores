<?php

namespace App\Observers;

use App\Enums\AdmissionStatus;
use App\Models\Admission;
use App\Models\Representative;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdmissionObserver
{
    /**
     * Handle the Admission "updated" event.
     */
    public function updated(Admission $admission): void
    {
        if (! $admission->wasChanged('status')) {
            return;
        }

        if ($admission->status !== AdmissionStatus::Approved) {
            return;
        }

        DB::transaction(function () use ($admission) {
            $representative = Representative::firstOrCreate(
                ['email' => $admission->contact_email],
                [
                    'id_card' => fake()->unique()->numerify('ADM##########'),
                    'first_name' => Str::before($admission->representative_names, ' ') ?: $admission->representative_names,
                    'last_name' => Str::after($admission->representative_names, ' ') ?: '',
                    'phone' => $admission->contact_phone,
                ]
            );

            Student::create([
                'representative_id' => $representative->id,
                'id_card' => fake()->unique()->numerify('STD##########'),
                'first_name' => $admission->applicant_first_name,
                'last_name' => $admission->applicant_last_name,
                'birth_date' => $admission->applicant_birth_date,
            ]);
        });
    }
}
