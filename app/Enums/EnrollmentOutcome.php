<?php

namespace App\Enums;

enum EnrollmentOutcome: string
{
    case Completed = 'completed';
    case NotCompleted = 'not_completed';
}
