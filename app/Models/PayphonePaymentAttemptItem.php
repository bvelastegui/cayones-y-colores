<?php

namespace App\Models;

use Database\Factories\PayphonePaymentAttemptItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $payphone_payment_attempt_id
 * @property int $tuition_id
 * @property int $amount_in_cents
 */
#[Fillable(['payphone_payment_attempt_id', 'tuition_id', 'amount_in_cents'])]
class PayphonePaymentAttemptItem extends Model
{
    /** @use HasFactory<PayphonePaymentAttemptItemFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['amount_in_cents' => 'integer'];
    }

    /** @return BelongsTo<PayphonePaymentAttempt, $this> */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(PayphonePaymentAttempt::class, 'payphone_payment_attempt_id');
    }

    /** @return BelongsTo<Tuition, $this> */
    public function tuition(): BelongsTo
    {
        return $this->belongsTo(Tuition::class);
    }
}
