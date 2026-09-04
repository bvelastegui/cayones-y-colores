<?php

namespace App\Models;

use App\Enums\StudentLifecycleStatus;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Student',
    title: 'Student',
    description: 'Estudiante matriculado en el centro infantil',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'representative_id', type: 'integer', example: 3),
        new OA\Property(property: 'id_card', type: 'string', example: '1751234567'),
        new OA\Property(property: 'first_name', type: 'string', example: 'Ana'),
        new OA\Property(property: 'last_name', type: 'string', example: 'Martínez'),
        new OA\Property(property: 'full_name', type: 'string', example: 'Ana Martínez'),
        new OA\Property(property: 'birth_date', type: 'string', format: 'date', example: '2019-03-15'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
/**
 * @property int $id
 * @property int $representative_id
 * @property int|null $level_id
 * @property StudentLifecycleStatus $lifecycle_status
 * @property string $id_card
 * @property string $first_name
 * @property string $last_name
 * @property Carbon $birth_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $full_name
 */
#[Fillable(['representative_id', 'level_id', 'lifecycle_status', 'id_card', 'first_name', 'last_name', 'birth_date'])]
class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $appends = ['full_name'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'lifecycle_status' => StudentLifecycleStatus::class,
        ];
    }

    /** @return Attribute<string, never> */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }

    /**
     * @return BelongsTo<Representative, $this>
     */
    public function representative(): BelongsTo
    {
        return $this->belongsTo(Representative::class);
    }

    /** @return BelongsTo<Level, $this> */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /** @return HasOne<StudentProfile, $this> */
    public function profile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    /** @return HasOne<StudentAddress, $this> */
    public function residence(): HasOne
    {
        return $this->hasOne(StudentAddress::class);
    }

    /** @return HasOne<StudentLegalRepresentative, $this> */
    public function legalRepresentative(): HasOne
    {
        return $this->hasOne(StudentLegalRepresentative::class);
    }

    /** @return HasOne<StudentBillingProfile, $this> */
    public function billingProfile(): HasOne
    {
        return $this->hasOne(StudentBillingProfile::class);
    }

    /** @return HasOne<StudentHealthInsurance, $this> */
    public function healthInsurance(): HasOne
    {
        return $this->hasOne(StudentHealthInsurance::class);
    }

    /** @return HasMany<StudentEmergencyContact, $this> */
    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(StudentEmergencyContact::class)->orderBy('position');
    }

    /** @return HasMany<StudentMedicalCondition, $this> */
    public function medicalConditions(): HasMany
    {
        return $this->hasMany(StudentMedicalCondition::class);
    }

    /** @return HasMany<StudentAllergy, $this> */
    public function allergies(): HasMany
    {
        return $this->hasMany(StudentAllergy::class);
    }

    /** @return HasMany<StudentMedication, $this> */
    public function medications(): HasMany
    {
        return $this->hasMany(StudentMedication::class);
    }

    /**
     * @return HasOne<Admission, $this>
     */
    public function admission(): HasOne
    {
        return $this->hasOne(Admission::class);
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @return BelongsToMany<Course, $this>
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments')
            ->withPivot('enrollment_date', 'status')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Tuition, $this>
     */
    public function tuitions(): HasMany
    {
        return $this->hasMany(Tuition::class);
    }

    /**
     * @return HasMany<AcademicReport, $this>
     */
    public function academicReports(): HasMany
    {
        return $this->hasMany(AcademicReport::class);
    }
}
