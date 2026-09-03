<?php

use App\Actions\Payments\ConfirmPayphonePaymentAction;
use App\Actions\Payments\CreatePaymentAction;
use App\Actions\Payments\DeletePaymentAction;
use App\Actions\Payments\PayTuitionAction;
use App\Actions\Payments\UpdatePaymentAction;
use App\Enums\PaymentMethod;
use App\Enums\PayphonePaymentStatus;
use App\Enums\TuitionStatus;
use App\Models\Payment;
use App\Models\PayphonePaymentAttempt;
use App\Models\Tuition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

test('payphone prepares several backend calculated outstanding balances in one transaction', function () {
    config()->set('services.payphone', [
        'token' => 'test-token',
        'store_id' => 'test-store',
        'confirm_url' => 'https://school.test/payments/payphone/confirm',
        'api_url' => 'https://pay.payphonetodoesposible.com/api/button',
        'currency' => 'USD',
        'time_zone' => -5,
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'https://pay.payphonetodoesposible.com/api/button/Prepare' => Http::response([
            'paymentId' => 'payment-token',
            'payWithCard' => 'https://pay.payphonetodoesposible.com/Anonymous/Index?paymentId=payment-token',
        ]),
    ]);
    $firstTuition = Tuition::factory()->create(['amount' => 125.50, 'status' => TuitionStatus::Partial]);
    $secondTuition = Tuition::factory()->create(['amount' => 80, 'status' => TuitionStatus::Pending]);
    Payment::factory()->create(['tuition_id' => $firstTuition->id, 'amount_paid' => 25.25]);

    $result = app(PayTuitionAction::class)->execute(
        new Collection([$firstTuition, $secondTuition]),
        PaymentMethod::Payphone,
    );

    expect($result->paymentUrl)->toContain('paymentId=payment-token')
        ->and(Payment::query()->count())->toBe(1)
        ->and($firstTuition->refresh()->status)->toBe(TuitionStatus::Partial)
        ->and($secondTuition->refresh()->status)->toBe(TuitionStatus::Pending);
    $this->assertDatabaseHas('payphone_payment_attempts', [
        'tuition_id' => $firstTuition->id,
        'amount_in_cents' => 18025,
        'status' => PayphonePaymentStatus::Prepared->value,
    ]);
    $this->assertDatabaseHas('payphone_payment_attempt_items', [
        'tuition_id' => $firstTuition->id,
        'amount_in_cents' => 10025,
    ]);
    $this->assertDatabaseHas('payphone_payment_attempt_items', [
        'tuition_id' => $secondTuition->id,
        'amount_in_cents' => 8000,
    ]);
    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://pay.payphonetodoesposible.com/api/button/Prepare'
            && $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['amount'] === 18025
            && $request['amountWithoutTax'] === 18025
            && $request['amountWithTax'] === 0
            && $request['tax'] === 0
            && $request['service'] === 0
            && $request['tip'] === 0;
    });
});

test('payphone confirmation records an approved payment and is idempotent', function () {
    config()->set('services.payphone', [
        'token' => 'test-token',
        'store_id' => 'test-store',
        'confirm_url' => 'https://school.test/payments/payphone/confirm',
        'api_url' => 'https://pay.payphonetodoesposible.com/api/button',
        'currency' => 'USD',
        'time_zone' => -5,
    ]);
    $firstTuition = Tuition::factory()->create(['amount' => 125.50, 'status' => TuitionStatus::Pending]);
    $secondTuition = Tuition::factory()->create(['amount' => 80, 'status' => TuitionStatus::Pending]);
    $attempt = PayphonePaymentAttempt::factory()->for($firstTuition)->create([
        'amount_in_cents' => 20550,
        'client_transaction_id' => '123e4567-e89b-12d3-a456-426614174000',
    ]);
    $attempt->items()->createMany([
        ['tuition_id' => $firstTuition->id, 'amount_in_cents' => 12550],
        ['tuition_id' => $secondTuition->id, 'amount_in_cents' => 8000],
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'https://pay.payphonetodoesposible.com/api/button/V2/Confirm' => Http::response([
            'amount' => 20550,
            'clientTransactionId' => $attempt->client_transaction_id,
            'statusCode' => 3,
            'transactionStatus' => 'Approved',
            'transactionId' => 987654,
        ]),
    ]);

    $firstResult = app(ConfirmPayphonePaymentAction::class)->execute(987654, $attempt->client_transaction_id);
    $secondResult = app(ConfirmPayphonePaymentAction::class)->execute(987654, $attempt->client_transaction_id);

    expect($firstResult)->toBeTrue()
        ->and($secondResult)->toBeTrue()
        ->and(Payment::query()->whereIn('tuition_id', [$firstTuition->id, $secondTuition->id])->count())->toBe(2)
        ->and($firstTuition->refresh()->status)->toBe(TuitionStatus::Paid)
        ->and($secondTuition->refresh()->status)->toBe(TuitionStatus::Paid)
        ->and($attempt->refresh()->status)->toBe(PayphonePaymentStatus::Approved);
    $this->assertDatabaseHas('payments', [
        'tuition_id' => $firstTuition->id,
        'amount_paid' => 125.50,
        'reference_number' => 'PAYPHONE-987654',
    ]);
    $this->assertDatabaseHas('payments', [
        'tuition_id' => $secondTuition->id,
        'amount_paid' => 80,
        'reference_number' => 'PAYPHONE-987654',
    ]);
    Http::assertSentCount(1);
});

test('payphone confirmation rejects an amount different from the backend calculation', function () {
    config()->set('services.payphone', [
        'token' => 'test-token',
        'store_id' => 'test-store',
        'confirm_url' => 'https://school.test/payments/payphone/confirm',
        'api_url' => 'https://pay.payphonetodoesposible.com/api/button',
        'currency' => 'USD',
        'time_zone' => -5,
    ]);
    $tuition = Tuition::factory()->create(['amount' => 125.50, 'status' => TuitionStatus::Pending]);
    $attempt = PayphonePaymentAttempt::factory()->for($tuition)->create([
        'amount_in_cents' => 12550,
        'client_transaction_id' => '123e4567-e89b-12d3-a456-426614174001',
    ]);
    $attempt->items()->create([
        'tuition_id' => $tuition->id,
        'amount_in_cents' => 12550,
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'https://pay.payphonetodoesposible.com/api/button/V2/Confirm' => Http::response([
            'amount' => 100,
            'clientTransactionId' => $attempt->client_transaction_id,
            'statusCode' => 3,
            'transactionStatus' => 'Approved',
            'transactionId' => 987655,
        ]),
    ]);

    expect(fn () => app(ConfirmPayphonePaymentAction::class)->execute(987655, $attempt->client_transaction_id))
        ->toThrow(ValidationException::class);
    expect(Payment::query()->where('tuition_id', $tuition->id)->exists())->toBeFalse()
        ->and($tuition->refresh()->status)->toBe(TuitionStatus::Pending);
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

    expect(fn () => app(PayTuitionAction::class)->execute(new Collection([$tuition]), PaymentMethod::Payphone))
        ->toThrow(ValidationException::class);
});
