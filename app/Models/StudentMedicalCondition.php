<?php

namespace App\Models;

use Database\Factories\StudentMedicalConditionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'name', 'details', 'care_instructions'])]
class StudentMedicalCondition extends Model
{
    /** @use HasFactory<StudentMedicalConditionFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['name' => 'encrypted', 'details' => 'encrypted', 'care_instructions' => 'encrypted'];
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
