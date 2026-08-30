<?php

namespace App\Models;

use App\Enums\TeacherType;
use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property TeacherType $teacher_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['first_name', 'last_name', 'teacher_type'])]
class Teacher extends Model
{
    /** @use HasFactory<TeacherFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'teacher_type' => TeacherType::class,
        ];
    }

    /**
     * @return HasMany<CourseTeacher, $this>
     */
    public function courseTeachers(): HasMany
    {
        return $this->hasMany(CourseTeacher::class);
    }

    /**
     * @return BelongsToMany<Course, $this>
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_teacher')
            ->withPivot('assigned_role')
            ->withTimestamps();
    }

    /**
     * @return HasMany<AcademicReport, $this>
     */
    public function academicReports(): HasMany
    {
        return $this->hasMany(AcademicReport::class);
    }
}
