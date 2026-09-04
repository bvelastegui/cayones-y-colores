<?php

namespace App\Models;

use Database\Factories\StudentAllergyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'allergen', 'severity', 'reaction', 'response_instructions'])]
class StudentAllergy extends Model
{
    /** @use HasFactory<StudentAllergyFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'allergen' => 'encrypted',
            'severity' => 'encrypted',
            'reaction' => 'encrypted',
            'response_instructions' => 'encrypted',
        ];
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
