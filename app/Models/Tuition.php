<?php

namespace App\Models;

use App\Enums\TuitionStatus;
use Database\Factories\TuitionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $student_id
 * @property float $amount
 * @property Carbon $generation_date
 * @property Carbon $due_date
 * @property TuitionStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['student_id', 'amount', 'generation_date', 'due_date', 'status'])]
class Tuition extends Model
{
    /** @use HasFactory<TuitionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'generation_date' => 'date',
            'due_date' => 'date',
            'status' => TuitionStatus::class,
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
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Remaining balance after payments.
     */
    public function remainingBalance(): float
    {
        $paid = $this->payments()->sum('amount_paid');

        return (float) $this->amount - (float) $paid;
    }
}
