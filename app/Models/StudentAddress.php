<?php

namespace App\Models;

use Database\Factories\StudentAddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id', 'country', 'province', 'city', 'parish', 'main_street', 'secondary_street',
    'house_number', 'reference', 'residence_type', 'housing_relationship',
])]
class StudentAddress extends Model
{
    /** @use HasFactory<StudentAddressFactory> */
    use HasFactory;

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
