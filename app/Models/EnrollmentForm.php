<?php

namespace App\Models;

use Database\Factories\EnrollmentFormFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $enrollment_id
 * @property string $current_step
 * @property array<string, mixed>|null $draft_data
 * @property array<string, mixed>|null $submitted_data
 * @property array<string, mixed>|null $snapshot_data
 * @property Carbon|null $consented_at
 */
#[Fillable([
    'enrollment_id', 'current_step', 'draft_data', 'submitted_data', 'snapshot_data',
    'privacy_policy_version', 'medical_consent_version', 'emergency_consent_version', 'consented_at', 'consent_ip', 'consented_by',
])]
class EnrollmentForm extends Model
{
    /** @use HasFactory<EnrollmentFormFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'draft_data' => 'encrypted:array',
            'submitted_data' => 'encrypted:array',
            'snapshot_data' => 'encrypted:array',
            'consented_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Enrollment, $this> */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /** @return BelongsTo<User, $this> */
    public function consentingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consented_by');
    }
}
