<?php

namespace App\Models;

use Database\Factories\LevelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $max_capacity
 * @property int $student_aux_ratio
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'max_capacity', 'student_aux_ratio'])]
class Level extends Model
{
    /** @use HasFactory<LevelFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'max_capacity' => 'integer',
            'student_aux_ratio' => 'integer',
        ];
    }

    /**
     * @return HasMany<Course, $this>
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    /**
     * @return HasMany<Admission, $this>
     */
    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }

    /**
     * Determine whether this level requires auxiliary teachers.
     */
    public function requiresAuxiliary(): bool
    {
        return $this->student_aux_ratio > 0;
    }

    /**
     * Calculate required auxiliary teachers for a given enrollment count.
     */
    public function requiredAuxiliaries(int $studentCount): int
    {
        if (! $this->requiresAuxiliary() || $this->student_aux_ratio === 0) {
            return 0;
        }

        return (int) floor($studentCount / $this->student_aux_ratio);
    }
}
