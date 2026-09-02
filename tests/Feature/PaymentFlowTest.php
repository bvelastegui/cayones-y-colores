<?php

use App\Actions\Payments\CreatePaymentAction;
use App\Actions\Payments\DeletePaymentAction;
use App\Actions\Payments\PayTuitionAction;
use App\Actions\Payments\UpdatePaymentAction;
use App\Enums\PaymentMethod;
use App\Enums\TuitionStatus;
use App\Models\Payment;
use App\Models\Tuition;
use Illuminate\Validation\ValidationException;

test('payphone pays the outstanding balance and marks the tuition as paid', function () {
    $tuition = Tuition::factory()->create(['amount' => 125.50, 'status' => TuitionStatus::Pending]);

    $payment = app(PayTuitionAction::class)->execute($tuition, PaymentMethod::Payphone);

    expect((float) $payment->amount_paid)->toBe(125.5)
        ->and($payment->payment_method)->toBe(PaymentMethod::Payphone)
        ->and($payment->reference_number)->toStartWith('PAYPHONE-')
        ->and($tuition->refresh()->status)->toBe(TuitionStatus::Paid);
});

test('an administrative partial payment marks the tuition as partial', function () {
    $tuition = Tuition::factory()->create(['amount' => 100, 'status' => TuitionStatus::Pending]);

    app(CreatePaymentAction::class)->execute([
        'tuition_id' => $tuition->id,
        'payment_method' => PaymentMethod::Cash,
        'amount_paid' => 40,
        'payment_date' => '2026-09-02',
        'reference_number' => null,
    ]);

    expect($tuition->refresh()->status)->toBe(TuitionStatus::Partial);
});

test('moving a payment synchronizes both affected tuitions', function () {
    $originalTuition = Tuition::factory()->create(['amount' => 100, 'status' => TuitionStatus::Partial]);
    $newTuition = Tuition::factory()->create(['amount' => 100, 'status' => TuitionStatus::Pending]);
    $payment = Payment::factory()->create(['tuition_id' => $originalTuition->id, 'amount_paid' => 40]);

    app(UpdatePaymentAction::class)->execute($payment, ['tuition_id' => $newTuition->id]);

    expect($originalTuition->refresh()->status)->toBe(TuitionStatus::Pending)
        ->and($newTuition->refresh()->status)->toBe(TuitionStatus::Partial);
});

test('deleting the last payment restores the pending status', function () {
    $tuition = Tuition::factory()->create(['amount' => 100, 'status' => TuitionStatus::Partial]);
    $payment = Payment::factory()->create(['tuition_id' => $tuition->id, 'amount_paid' => 40]);

    app(DeletePaymentAction::class)->execute($payment);

    expect($tuition->refresh()->status)->toBe(TuitionStatus::Pending)
        ->and(Payment::find($payment->id))->toBeNull();
});

test('a paid tuition cannot be charged again', function () {
    $tuition = Tuition::factory()->create(['amount' => 100, 'status' => TuitionStatus::Paid]);

    expect(fn () => app(PayTuitionAction::class)->execute($tuition, PaymentMethod::Payphone))
        ->toThrow(ValidationException::class);
});
