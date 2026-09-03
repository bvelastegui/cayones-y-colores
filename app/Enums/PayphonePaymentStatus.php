<?php

namespace App\Enums;

enum PayphonePaymentStatus: string
{
    case Creating = 'creating';
    case Prepared = 'prepared';
    case Approved = 'approved';
    case Cancelled = 'cancelled';
    case Failed = 'failed';
}
