<?php

namespace App\Models;

use Database\Factories\StudentBillingProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'person_type', 'tax_id_type', 'tax_id', 'business_name', 'email', 'phone', 'address'])]
class StudentBillingProfile extends Model
{
    /** @use HasFactory<StudentBillingProfileFactory> */
    use HasFactory;

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
