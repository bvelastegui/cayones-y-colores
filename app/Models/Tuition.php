<?php

namespace App\Models;

use App\Enums\TuitionConcept;
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
 * @property int|null $enrollment_id
 * @property TuitionConcept $concept
 * @property float $amount
 * @property Carbon $generation_date
 * @property Carbon|null $billing_period
 * @property Carbon $due_date
 * @property TuitionStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['student_id', 'enrollment_id', 'concept', 'amount', 'generation_date', 'billing_period', 'due_date', 'status'])]
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
            'billing_period' => 'date',
            'due_date' => 'date',
            'status' => TuitionStatus::class,
            'concept' => TuitionConcept::class,
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<Enrollment, $this> */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @return HasMany<PayphonePaymentAttempt, $this> */
    public function payphonePaymentAttempts(): HasMany
    {
        return $this->hasMany(PayphonePaymentAttempt::class);
    }

    /** @return HasMany<PayphonePaymentAttemptItem, $this> */
    public function payphonePaymentAttemptItems(): HasMany
    {
        return $this->hasMany(PayphonePaymentAttemptItem::class);
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
