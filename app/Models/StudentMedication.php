<?php

namespace App\Models;

use Database\Factories\StudentMedicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'name', 'dose', 'schedule', 'prescriber', 'instructions'])]
class StudentMedication extends Model
{
    /** @use HasFactory<StudentMedicationFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'name' => 'encrypted',
            'dose' => 'encrypted',
            'schedule' => 'encrypted',
            'prescriber' => 'encrypted',
            'instructions' => 'encrypted',
        ];
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
