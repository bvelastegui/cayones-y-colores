<?php

use App\Actions\Payments\ConfirmPayphonePaymentAction;
use App\Actions\Payments\CreatePaymentAction;
use App\Actions\Payments\DeletePaymentAction;
use App\Actions\Payments\PayTuitionAction;
use App\Actions\Payments\UpdatePaymentAction;
use App\Enums\EnrollmentStatus;
use App\Enums\PaymentMethod;
use App\Enums\PayphonePaymentStatus;
use App\Enums\TuitionConcept;
use App\Enums\TuitionStatus;
use App\Models\Enrollment;
use App\Models\EnrollmentForm;
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
            'currency' => 'USD',
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
            'currency' => 'USD',
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

test('payphone confirmation rejects a currency different from the configured currency', function () {
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
        'client_transaction_id' => '123e4567-e89b-12d3-a456-426614174002',
    ]);
    $attempt->items()->create([
        'tuition_id' => $tuition->id,
        'amount_in_cents' => 12550,
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'https://pay.payphonetodoesposible.com/api/button/V2/Confirm' => Http::response([
            'amount' => 12550,
            'currency' => 'EUR',
            'clientTransactionId' => $attempt->client_transaction_id,
            'statusCode' => 3,
            'transactionStatus' => 'Approved',
            'transactionId' => 987656,
        ]),
    ]);

    expect(fn () => app(ConfirmPayphonePaymentAction::class)->execute(987656, $attempt->client_transaction_id))
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

test('enrollment charges cannot be combined in one payphone transaction', function () {
    $firstEnrollment = Enrollment::factory()->create([
        'course_id' => null,
        'status' => EnrollmentStatus::PendingPayment,
    ]);
    $secondEnrollment = Enrollment::factory()->create([
        'course_id' => null,
        'status' => EnrollmentStatus::PendingPayment,
    ]);
    $firstTuition = Tuition::factory()->create([
        'student_id' => $firstEnrollment->student_id,
        'enrollment_id' => $firstEnrollment->id,
        'concept' => TuitionConcept::Enrollment,
        'billing_period' => null,
        'status' => TuitionStatus::Pending,
    ]);
    $secondTuition = Tuition::factory()->create([
        'student_id' => $secondEnrollment->student_id,
        'enrollment_id' => $secondEnrollment->id,
        'concept' => TuitionConcept::Enrollment,
        'billing_period' => null,
        'status' => TuitionStatus::Pending,
    ]);

    expect(fn () => app(PayTuitionAction::class)->execute(
        new Collection([$firstTuition, $secondTuition]),
        PaymentMethod::Payphone,
        TuitionConcept::Enrollment,
    ))->toThrow(ValidationException::class, 'La matrícula debe pagarse sola en una transacción.');

    expect(PayphonePaymentAttempt::query()->exists())->toBeFalse();
});

test('a prepared enrollment charge cannot be reused through the monthly payment operation', function () {
    $enrollment = Enrollment::factory()->create([
        'course_id' => null,
        'status' => EnrollmentStatus::PaymentInProgress,
    ]);
    $tuition = Tuition::factory()->create([
        'student_id' => $enrollment->student_id,
        'enrollment_id' => $enrollment->id,
        'concept' => TuitionConcept::Enrollment,
        'billing_period' => null,
        'status' => TuitionStatus::Pending,
    ]);
    $attempt = PayphonePaymentAttempt::factory()->for($tuition)->create();
    $attempt->items()->create([
        'tuition_id' => $tuition->id,
        'amount_in_cents' => $attempt->amount_in_cents,
    ]);

    expect(fn () => app(PayTuitionAction::class)->execute(
        new Collection([$tuition]),
        PaymentMethod::Payphone,
    ))->toThrow(ValidationException::class, 'El pago contiene un tipo de rubro no permitido en esta operación.');
});

test('an approved enrollment payment freezes the submitted profile and waits for course assignment', function () {
    config()->set('services.payphone', [
        'token' => 'test-token',
        'store_id' => 'test-store',
        'confirm_url' => 'https://school.test/payments/payphone/confirm',
        'api_url' => 'https://pay.payphonetodoesposible.com/api/button',
        'currency' => 'USD',
        'time_zone' => -5,
    ]);
    $enrollment = Enrollment::factory()->create([
        'course_id' => null,
        'status' => EnrollmentStatus::PaymentInProgress,
    ]);
    $submittedData = ['student' => ['first_name' => 'Ana']];
    EnrollmentForm::factory()->for($enrollment)->create([
        'submitted_data' => $submittedData,
        'snapshot_data' => null,
    ]);
    $tuition = Tuition::factory()->create([
        'student_id' => $enrollment->student_id,
        'enrollment_id' => $enrollment->id,
        'concept' => TuitionConcept::Enrollment,
        'billing_period' => null,
        'amount' => 175,
        'status' => TuitionStatus::Pending,
    ]);
    $attempt = PayphonePaymentAttempt::factory()->for($tuition)->create([
        'amount_in_cents' => 17500,
        'client_transaction_id' => '123e4567-e89b-12d3-a456-426614174099',
    ]);
    $attempt->items()->create(['tuition_id' => $tuition->id, 'amount_in_cents' => 17500]);
    Http::preventStrayRequests();
    Http::fake([
        'https://pay.payphonetodoesposible.com/api/button/V2/Confirm' => Http::response([
            'amount' => 17500,
            'currency' => 'USD',
            'clientTransactionId' => $attempt->client_transaction_id,
            'statusCode' => 3,
            'transactionStatus' => 'Approved',
            'transactionId' => 987699,
        ]),
    ]);

    app(ConfirmPayphonePaymentAction::class)->execute(987699, $attempt->client_transaction_id);

    expect($enrollment->refresh()->status)->toBe(EnrollmentStatus::PaidPendingAssignment)
        ->and($enrollment->paid_at)->not->toBeNull()
        ->and($enrollment->form->snapshot_data)->toBe($submittedData)
        ->and($tuition->refresh()->status)->toBe(TuitionStatus::Paid);
});

test('payphone callback returns an enrollment payment to the student enrollment page', function () {
    config()->set('services.payphone', [
        'token' => 'test-token',
        'store_id' => 'test-store',
        'confirm_url' => 'https://school.test/payments/payphone/confirm',
        'api_url' => 'https://pay.payphonetodoesposible.com/api/button',
        'currency' => 'USD',
        'time_zone' => -5,
    ]);
    $enrollment = Enrollment::factory()->create([
        'course_id' => null,
        'status' => EnrollmentStatus::PaymentInProgress,
    ]);
    EnrollmentForm::factory()->for($enrollment)->create([
        'submitted_data' => ['student' => ['first_name' => 'Ana']],
    ]);
    $tuition = Tuition::factory()->create([
        'student_id' => $enrollment->student_id,
        'enrollment_id' => $enrollment->id,
        'concept' => TuitionConcept::Enrollment,
        'billing_period' => null,
        'amount' => 175,
        'status' => TuitionStatus::Pending,
    ]);
    $attempt = PayphonePaymentAttempt::factory()->for($tuition)->create([
        'amount_in_cents' => 17500,
        'client_transaction_id' => '123e4567-e89b-12d3-a456-426614174098',
    ]);
    $attempt->items()->create(['tuition_id' => $tuition->id, 'amount_in_cents' => 17500]);
    Http::preventStrayRequests();
    Http::fake([
        'https://pay.payphonetodoesposible.com/api/button/V2/Confirm' => Http::response([
            'amount' => 17500,
            'currency' => 'USD',
            'clientTransactionId' => $attempt->client_transaction_id,
            'statusCode' => 3,
            'transactionStatus' => 'Approved',
            'transactionId' => 987698,
        ]),
    ]);

    $this->get('/payments/payphone/confirm?id=987698&clientTransactionId='.$attempt->client_transaction_id)
        ->assertRedirect("/parent/enroll/{$enrollment->student_id}?payment=approved");

    expect($enrollment->refresh()->status)->toBe(EnrollmentStatus::PaidPendingAssignment);
});

test('payphone callback keeps monthly payments on the payments page', function () {
    config()->set('services.payphone', [
        'token' => 'test-token',
        'store_id' => 'test-store',
        'confirm_url' => 'https://school.test/payments/payphone/confirm',
        'api_url' => 'https://pay.payphonetodoesposible.com/api/button',
        'currency' => 'USD',
        'time_zone' => -5,
    ]);
    $tuition = Tuition::factory()->create([
        'concept' => TuitionConcept::Monthly,
        'amount' => 80,
        'status' => TuitionStatus::Pending,
    ]);
    $attempt = PayphonePaymentAttempt::factory()->for($tuition)->create([
        'amount_in_cents' => 8000,
        'client_transaction_id' => '123e4567-e89b-12d3-a456-426614174097',
    ]);
    $attempt->items()->create(['tuition_id' => $tuition->id, 'amount_in_cents' => 8000]);
    Http::preventStrayRequests();
    Http::fake([
        'https://pay.payphonetodoesposible.com/api/button/V2/Confirm' => Http::response([
            'amount' => 8000,
            'currency' => 'USD',
            'clientTransactionId' => $attempt->client_transaction_id,
            'statusCode' => 3,
            'transactionStatus' => 'Approved',
            'transactionId' => 987697,
        ]),
    ]);

    $this->get('/payments/payphone/confirm?id=987697&clientTransactionId='.$attempt->client_transaction_id)
        ->assertRedirect('/parent/payments?payment=approved');

    expect($tuition->refresh()->status)->toBe(TuitionStatus::Paid);
});

test('a cancelled old attempt does not roll back a newer enrollment payment attempt', function () {
    config()->set('services.payphone', [
        'token' => 'test-token',
        'store_id' => 'test-store',
        'confirm_url' => 'https://school.test/payments/payphone/confirm',
        'api_url' => 'https://pay.payphonetodoesposible.com/api/button',
        'currency' => 'USD',
        'time_zone' => -5,
    ]);
    $enrollment = Enrollment::factory()->create([
        'course_id' => null,
        'status' => EnrollmentStatus::PaymentInProgress,
    ]);
    $tuition = Tuition::factory()->create([
        'student_id' => $enrollment->student_id,
        'enrollment_id' => $enrollment->id,
        'concept' => TuitionConcept::Enrollment,
        'billing_period' => null,
        'amount' => 175,
        'status' => TuitionStatus::Pending,
    ]);
    $oldAttempt = PayphonePaymentAttempt::factory()->for($tuition)->create([
        'amount_in_cents' => 17500,
        'client_transaction_id' => '123e4567-e89b-12d3-a456-426614174096',
    ]);
    $oldAttempt->items()->create(['tuition_id' => $tuition->id, 'amount_in_cents' => 17500]);
    $newAttempt = PayphonePaymentAttempt::factory()->for($tuition)->create([
        'amount_in_cents' => 17500,
        'client_transaction_id' => '123e4567-e89b-12d3-a456-426614174095',
    ]);
    $newAttempt->items()->create(['tuition_id' => $tuition->id, 'amount_in_cents' => 17500]);
    Http::preventStrayRequests();
    Http::fake([
        'https://pay.payphonetodoesposible.com/api/button/V2/Confirm' => Http::response([
            'amount' => 17500,
            'currency' => 'USD',
            'clientTransactionId' => $oldAttempt->client_transaction_id,
            'statusCode' => 2,
            'transactionStatus' => 'Canceled',
            'transactionId' => 987696,
        ]),
    ]);

    $result = app(ConfirmPayphonePaymentAction::class)->execute(987696, $oldAttempt->client_transaction_id);

    expect($result)->toBeFalse()
        ->and($oldAttempt->refresh()->status)->toBe(PayphonePaymentStatus::Cancelled)
        ->and($newAttempt->refresh()->status)->toBe(PayphonePaymentStatus::Prepared)
        ->and($enrollment->refresh()->status)->toBe(EnrollmentStatus::PaymentInProgress);
});

test('a cancelled enrollment payment returns the enrollment to pending payment', function () {
    config()->set('services.payphone', [
        'token' => 'test-token',
        'store_id' => 'test-store',
        'confirm_url' => 'https://school.test/payments/payphone/confirm',
        'api_url' => 'https://pay.payphonetodoesposible.com/api/button',
        'currency' => 'USD',
        'time_zone' => -5,
    ]);
    $enrollment = Enrollment::factory()->create([
        'course_id' => null,
        'status' => EnrollmentStatus::PaymentInProgress,
    ]);
    $tuition = Tuition::factory()->create([
        'student_id' => $enrollment->student_id,
        'enrollment_id' => $enrollment->id,
        'concept' => TuitionConcept::Enrollment,
        'billing_period' => null,
        'amount' => 175,
        'status' => TuitionStatus::Pending,
    ]);
    $attempt = PayphonePaymentAttempt::factory()->for($tuition)->create([
        'amount_in_cents' => 17500,
        'client_transaction_id' => '123e4567-e89b-12d3-a456-426614174094',
    ]);
    $attempt->items()->create(['tuition_id' => $tuition->id, 'amount_in_cents' => 17500]);
    Http::preventStrayRequests();
    Http::fake([
        'https://pay.payphonetodoesposible.com/api/button/V2/Confirm' => Http::response([
            'amount' => 17500,
            'currency' => 'USD',
            'clientTransactionId' => $attempt->client_transaction_id,
            'statusCode' => 2,
            'transactionStatus' => 'Canceled',
            'transactionId' => 987695,
        ]),
    ]);

    $result = app(ConfirmPayphonePaymentAction::class)->execute(987695, $attempt->client_transaction_id);

    expect($result)->toBeFalse()
        ->and($attempt->refresh()->status)->toBe(PayphonePaymentStatus::Cancelled)
        ->and($enrollment->refresh()->status)->toBe(EnrollmentStatus::PendingPayment);
    $this->assertDatabaseHas('enrollment_audits', [
        'enrollment_id' => $enrollment->id,
        'event' => 'enrollment_payment_cancelled',
    ]);
});
