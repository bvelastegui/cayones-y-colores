<?php

namespace App\Models;

use App\Enums\AssignedRole;
use Database\Factories\CourseTeacherFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $course_id
 * @property int $teacher_id
 * @property AssignedRole $assigned_role
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['course_id', 'teacher_id', 'assigned_role'])]
class CourseTeacher extends Model
{
    /**
     * @var string
     */
    protected $table = 'course_teacher';

    /** @use HasFactory<CourseTeacherFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assigned_role' => AssignedRole::class,
        ];
    }

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
