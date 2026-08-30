<?php

namespace App\Models;

use Database\Factories\RepresentativeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $id_card
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $phone
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['id_card', 'first_name', 'last_name', 'email', 'phone'])]
class Representative extends Model
{
    /** @use HasFactory<RepresentativeFactory> */
    use HasFactory;

    /**
     * @return HasMany<Student, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
