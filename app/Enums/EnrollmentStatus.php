<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Draft = 'draft';
    case PendingPayment = 'pending_payment';
    case PaymentInProgress = 'payment_in_progress';
    case PaidPendingAssignment = 'paid_pending_assignment';
    case Active = 'active';
    case Finalized = 'finalized';
    case Withdrawn = 'withdrawn';
    case Graduated = 'graduated';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Draft->value => 'Borrador',
            self::PendingPayment->value => 'Pendiente de pago',
            self::PaymentInProgress->value => 'Pago en proceso',
            self::PaidPendingAssignment->value => 'Pendiente de asignación',
            self::Active->value => 'Activa',
            self::Finalized->value => 'Finalizada',
            self::Withdrawn->value => 'Retirada',
            self::Graduated->value => 'Graduada',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
