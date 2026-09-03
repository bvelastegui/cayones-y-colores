<?php

namespace App\Models;

use App\Enums\PayphonePaymentStatus;
use Database\Factories\PayphonePaymentAttemptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tuition_id
 * @property string $client_transaction_id
 * @property int $amount_in_cents
 * @property PayphonePaymentStatus $status
 * @property string|null $payphone_payment_id
 * @property string|null $payment_url
 * @property int|null $transaction_id
 * @property Carbon $expires_at
 * @property Carbon|null $confirmed_at
 */
#[Fillable([
    'tuition_id',
    'client_transaction_id',
    'amount_in_cents',
    'status',
    'payphone_payment_id',
    'payment_url',
    'transaction_id',
    'expires_at',
    'confirmed_at',
])]
class PayphonePaymentAttempt extends Model
{
    /** @use HasFactory<PayphonePaymentAttemptFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount_in_cents' => 'integer',
            'status' => PayphonePaymentStatus::class,
            'transaction_id' => 'integer',
            'expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Tuition, $this> */
    public function tuition(): BelongsTo
    {
        return $this->belongsTo(Tuition::class);
    }
}
