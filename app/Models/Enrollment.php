<?php

namespace App\Models;

use App\Enums\EnrollmentOutcome;
use App\Enums\EnrollmentStatus;
use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $student_id
 * @property int|null $academic_period_id
 * @property int|null $level_id
 * @property int|null $course_id
 * @property Carbon $enrollment_date
 * @property EnrollmentStatus $status
 * @property EnrollmentOutcome|null $level_outcome
 * @property Carbon|null $form_completed_at
 * @property Carbon|null $payment_started_at
 * @property Carbon|null $paid_at
 * @property Carbon|null $assigned_at
 * @property Carbon|null $finalized_at
 * @property Carbon|null $exception_until
 * @property string|null $assignment_issue
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'student_id', 'academic_period_id', 'level_id', 'course_id', 'enrollment_date', 'status',
    'level_outcome', 'form_completed_at', 'payment_started_at', 'paid_at', 'assigned_at',
    'finalized_at', 'exception_until', 'assignment_issue',
])]
class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enrollment_date' => 'date',
            'status' => EnrollmentStatus::class,
            'level_outcome' => EnrollmentOutcome::class,
            'form_completed_at' => 'datetime',
            'payment_started_at' => 'datetime',
            'paid_at' => 'datetime',
            'assigned_at' => 'datetime',
            'finalized_at' => 'datetime',
            'exception_until' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return BelongsTo<AcademicPeriod, $this> */
    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    /** @return BelongsTo<Level, $this> */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /** @return HasOne<EnrollmentForm, $this> */
    public function form(): HasOne
    {
        return $this->hasOne(EnrollmentForm::class);
    }

    /** @return HasOne<Tuition, $this> */
    public function tuition(): HasOne
    {
        return $this->hasOne(Tuition::class);
    }

    /** @return HasMany<EnrollmentAudit, $this> */
    public function audits(): HasMany
    {
        return $this->hasMany(EnrollmentAudit::class);
    }
}
