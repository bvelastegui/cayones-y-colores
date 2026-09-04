<?php

namespace App\Enums;

enum AcademicPeriodStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Closed = 'closed';
    case Allocating = 'allocating';
    case Allocated = 'allocated';
}
