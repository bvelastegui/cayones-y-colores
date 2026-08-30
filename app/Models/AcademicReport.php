<?php

namespace App\Models;

use Database\Factories\AcademicReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $student_id
 * @property int $teacher_id
 * @property string $development_area
 * @property string $evaluated_skill
 * @property string $achievement_level
 * @property string|null $observations
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'student_id',
    'teacher_id',
    'development_area',
    'evaluated_skill',
    'achievement_level',
    'observations',
])]
class AcademicReport extends Model
{
    /** @use HasFactory<AcademicReportFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
