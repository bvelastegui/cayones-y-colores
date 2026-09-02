<?php

namespace App\Models;

use App\Enums\AdmissionStatus;
use Database\Factories\AdmissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Admission',
    title: 'Admission',
    description: 'Solicitud de admisión al centro infantil',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'level_id', type: 'integer', example: 2),
        new OA\Property(property: 'applicant_first_name', type: 'string', example: 'Luciana'),
        new OA\Property(property: 'applicant_last_name', type: 'string', example: 'Santos'),
        new OA\Property(property: 'applicant_birth_date', type: 'string', format: 'date', example: '2020-05-12'),
        new OA\Property(property: 'representative_names', type: 'string', example: 'María Santos'),
        new OA\Property(property: 'contact_email', type: 'string', format: 'email', example: 'maria@example.com'),
        new OA\Property(property: 'contact_phone', type: 'string', example: '0991234567'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'rejected'], example: 'pending'),
        new OA\Property(property: 'application_date', type: 'string', format: 'date', example: '2026-08-30'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
/**
 * @property int $id
 * @property int $level_id
 * @property string $applicant_first_name
 * @property string $applicant_last_name
 * @property Carbon $applicant_birth_date
 * @property string $representative_names
 * @property string $contact_email
 * @property string $contact_phone
 * @property AdmissionStatus $status
 * @property int|null $representative_id
 * @property int|null $student_id
 * @property Carbon $application_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'level_id',
    'applicant_first_name',
    'applicant_last_name',
    'applicant_birth_date',
    'representative_names',
    'contact_email',
    'contact_phone',
    'status',
    'representative_id',
    'student_id',
    'application_date',
])]
class Admission extends Model
{
    /** @use HasFactory<AdmissionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'applicant_birth_date' => 'date',
            'application_date' => 'date',
            'status' => AdmissionStatus::class,
        ];
    }

    public function isPending(): bool
    {
        return AdmissionStatus::from((string) $this->getRawOriginal('status')) === AdmissionStatus::Pending;
    }

    /**
     * @return BelongsTo<Level, $this>
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * @return BelongsTo<Representative, $this>
     */
    public function representative(): BelongsTo
    {
        return $this->belongsTo(Representative::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
