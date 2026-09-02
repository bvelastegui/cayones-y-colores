<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function teach(User $user, Course $course): bool
    {
        return $user->teacher?->courses()->whereKey($course->id)->exists() ?? false;
    }
}
