<?php

use App\Enums\PaymentMethod;
use App\Enums\TuitionStatus;
use App\Models\Payment;
use App\Models\Representative;
use App\Models\Student;
use App\Models\Tuition;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('representative sees paid tuitions and downloads a combined payphone receipt', function () {
    $user = User::factory()->representative()->create();
    $representative = Representative::factory()->create(['user_id' => $user->id]);
    $student = Student::factory()->create(['representative_id' => $representative->id]);
    $firstTuition = Tuition::factory()->for($student)->create([
        'amount' => 125.50,
        'generation_date' => '2026-08-02',
        'billing_period' => '2026-08-01',
        'status' => TuitionStatus::Paid,
    ]);
    $secondTuition = Tuition::factory()->for($student)->create([
        'amount' => 80,
        'generation_date' => '2026-09-02',
        'billing_period' => '2026-09-01',
        'status' => TuitionStatus::Paid,
    ]);
    $firstPayment = Payment::factory()->for($firstTuition)->create([
        'payment_method' => PaymentMethod::Payphone,
        'amount_paid' => 125.50,
        'payment_date' => '2026-09-03',
        'reference_number' => 'PAYPHONE-987654',
    ]);
    Payment::factory()->for($secondTuition)->create([
        'payment_method' => PaymentMethod::Payphone,
        'amount_paid' => 80,
        'payment_date' => '2026-09-03',
        'reference_number' => 'PAYPHONE-987654',
    ]);
    Sanctum::actingAs($user);

    $this->getJson('/api/me/tuitions')
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonPath('0.status', TuitionStatus::Paid->value)
        ->assertJsonPath('0.payments.0.reference_number', 'PAYPHONE-987654');

    $response = $this->get("/api/me/payments/{$firstPayment->id}/receipt", [
        'Accept' => 'application/pdf',
    ]);

    $response->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload('comprobante-payphone-987654.pdf');
    expect($response->getContent())->toStartWith('%PDF');
});

test('representative cannot download another family payment receipt', function () {
    $owner = User::factory()->representative()->create();
    $ownerRepresentative = Representative::factory()->create(['user_id' => $owner->id]);
    $student = Student::factory()->create(['representative_id' => $ownerRepresentative->id]);
    $payment = Payment::factory()->for(
        Tuition::factory()->for($student)->state([
            'generation_date' => '2026-09-02',
            'billing_period' => '2026-09-01',
        ]),
    )->create();
    $intruder = User::factory()->representative()->create();
    Representative::factory()->create(['user_id' => $intruder->id]);
    Sanctum::actingAs($intruder);

    $this->get("/api/me/payments/{$payment->id}/receipt")->assertForbidden();
});
