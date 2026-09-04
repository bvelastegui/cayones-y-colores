<?php

namespace App\Models;

use Database\Factories\StudentProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id', 'preferred_name', 'gender', 'nationality', 'birth_place', 'previous_institution',
    'previous_level', 'academic_background', 'educational_needs', 'educational_supports', 'languages',
    'blood_type', 'pediatrician_name', 'pediatrician_phone', 'developmental_notes', 'care_instructions',
    'medical_observations', 'additional_notes',
])]
class StudentProfile extends Model
{
    /** @use HasFactory<StudentProfileFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'academic_background' => 'encrypted',
            'educational_needs' => 'encrypted',
            'educational_supports' => 'encrypted',
            'blood_type' => 'encrypted',
            'pediatrician_name' => 'encrypted',
            'pediatrician_phone' => 'encrypted',
            'developmental_notes' => 'encrypted',
            'care_instructions' => 'encrypted',
            'medical_observations' => 'encrypted',
            'additional_notes' => 'encrypted',
        ];
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
