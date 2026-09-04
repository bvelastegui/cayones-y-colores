<?php

namespace App\Models;

use Database\Factories\StudentEmergencyContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'position', 'full_name', 'relationship', 'phone', 'alternate_phone', 'address', 'authorized_pickup'])]
class StudentEmergencyContact extends Model
{
    /** @use HasFactory<StudentEmergencyContactFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['position' => 'integer', 'authorized_pickup' => 'boolean'];
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
