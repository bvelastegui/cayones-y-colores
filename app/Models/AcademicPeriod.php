<?php

namespace App\Models;

use App\Enums\AcademicPeriodStatus;
use Database\Factories\AcademicPeriodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon $starts_on
 * @property Carbon $ends_on
 * @property Carbon $enrollment_opens_at
 * @property Carbon $enrollment_closes_at
 * @property AcademicPeriodStatus $status
 * @property Carbon|null $allocation_started_at
 * @property Carbon|null $allocation_completed_at
 */
#[Fillable([
    'name', 'starts_on', 'ends_on', 'enrollment_opens_at', 'enrollment_closes_at',
    'status', 'allocation_started_at', 'allocation_completed_at',
])]
class AcademicPeriod extends Model
{
    /** @use HasFactory<AcademicPeriodFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'enrollment_opens_at' => 'datetime',
            'enrollment_closes_at' => 'datetime',
            'status' => AcademicPeriodStatus::class,
            'allocation_started_at' => 'datetime',
            'allocation_completed_at' => 'datetime',
        ];
    }

    /** @return HasMany<Enrollment, $this> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /** @param Builder<AcademicPeriod> $query */
    public function scopeOpenForEnrollment(Builder $query): void
    {
        $query->where('status', AcademicPeriodStatus::Open)
            ->where('enrollment_opens_at', '<=', now())
            ->where('enrollment_closes_at', '>', now());
    }

    public function acceptsEnrollmentChanges(?\DateTimeInterface $exceptionUntil = null): bool
    {
        return ($this->status === AcademicPeriodStatus::Open
                && now()->between($this->enrollment_opens_at, $this->enrollment_closes_at))
            || ($exceptionUntil !== null && now()->lte($exceptionUntil));
    }
}
