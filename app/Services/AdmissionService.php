<?php

namespace App\Services;

use App\Enums\AdmissionStatus;
use App\Models\Admission;
use App\Models\Representative;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdmissionService
{
    public function __construct(private UserAccountService $userAccountService) {}

    public function approve(Admission $admission): Admission
    {
        if (! $admission->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'La admisión ya fue procesada.',
            ]);
        }

        DB::transaction(function () use ($admission): void {
            $representative = Representative::firstOrCreate(
                ['email' => $admission->contact_email],
                [
                    'id_card' => $this->generateDocumentNumber('REP'),
                    'first_name' => Str::before($admission->representative_names, ' ') ?: $admission->representative_names,
                    'last_name' => Str::after($admission->representative_names, ' ') ?: '',
                    'phone' => $admission->contact_phone,
                ]
            );

            $this->userAccountService->createForRepresentative($representative);

            $student = Student::create([
                'representative_id' => $representative->id,
                'level_id' => $admission->level_id,
                'id_card' => $this->generateDocumentNumber('STD'),
                'first_name' => $admission->applicant_first_name,
                'last_name' => $admission->applicant_last_name,
                'birth_date' => $admission->applicant_birth_date,
            ]);

            $admission->update([
                'status' => AdmissionStatus::Approved,
                'representative_id' => $representative->id,
                'student_id' => $student->id,
            ]);
        });

        return $admission->load(['level', 'representative', 'student']);
    }

    public function reject(Admission $admission): Admission
    {
        if (! $admission->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'La admisión ya fue procesada.',
            ]);
        }

        $admission->update(['status' => AdmissionStatus::Rejected]);

        return $admission->load(['level', 'representative', 'student']);
    }

    private function generateDocumentNumber(string $prefix): string
    {
        return Str::upper("{$prefix}-".now()->format('YmdHis').'-'.Str::random(6));
    }
}
