<?php

namespace App\Models;

use Database\Factories\StudentRecordAccessLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'user_id', 'scope', 'reason', 'ip_address'])]
class StudentRecordAccessLog extends Model
{
    /** @use HasFactory<StudentRecordAccessLogFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
