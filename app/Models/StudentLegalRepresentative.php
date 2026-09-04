<?php

namespace App\Models;

use Database\Factories\StudentLegalRepresentativeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id', 'relationship', 'id_type', 'id_number', 'first_name', 'last_name', 'email',
    'phone', 'birth_date', 'marital_status', 'occupation', 'workplace', 'work_phone', 'address',
])]
class StudentLegalRepresentative extends Model
{
    /** @use HasFactory<StudentLegalRepresentativeFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
