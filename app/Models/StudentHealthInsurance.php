<?php

namespace App\Models;

use Database\Factories\StudentHealthInsuranceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'has_insurance', 'provider', 'policy_number', 'plan_name', 'policy_holder', 'emergency_phone', 'expires_on'])]
class StudentHealthInsurance extends Model
{
    /** @use HasFactory<StudentHealthInsuranceFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['has_insurance' => 'boolean', 'expires_on' => 'date'];
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
