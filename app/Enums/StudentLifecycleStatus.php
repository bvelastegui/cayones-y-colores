<?php

namespace App\Enums;

enum StudentLifecycleStatus: string
{
    case Active = 'active';
    case Graduated = 'graduated';
}
