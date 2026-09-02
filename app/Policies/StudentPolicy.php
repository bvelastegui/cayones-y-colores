<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function manage(User $user, Student $student): bool
    {
        return $user->representative?->id === $student->representative_id;
    }
}
