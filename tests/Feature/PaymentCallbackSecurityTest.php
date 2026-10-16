<?php

use App\Models\Payment;
use App\Models\PaymentGatewayLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * P0 security: the payment gateway callback.
 *
 * SRS §83 - the callback used to be open to anyone who knew a transaction
 * number. It now requires a provider HMAC signature, logs every call, and is
 * idempotent on the provider's own transaction reference.
 */
beforeEach(function () {
    config(['services.payments.providers.telebirr.secret' => 'test-telebirr-secret']);
});

function signCallback(array $payload, string $secret, ?int $timestamp = null): array
{
    $timestamp ??= now()->timestamp;
    $body = json_encode($payload);

    return [
        'X-Provider' => 'telebirr',
        'X-Provider-Timestamp' => (string) $timestamp,
        'X-Provider-Signature' => hash_hmac('sha256', $timestamp.'.'.$body, $secret),
        'Content-Type' => 'application/json',
    ];
}

test('an unsigned callback is refused and logged', function () {
    $response = $this->postJson('/payments/callback', [
        'gateway_reference' => 'TB-100',
        'amount' => 5000,
        'status' => 'completed',
    ], [
        'X-Provider' => 'telebirr',
        'X-Provider-Timestamp' => (string) now()->timestamp,
    ]);

    $response->assertUnauthorized();

    expect(PaymentGatewayLog::where('event', 'signature.invalid')->exists())->toBeTrue();
    expect(Payment::count())->toBe(0);
});

test('a callback with no timestamp header is refused and logged', function () {
    $payload = ['gateway_reference' => 'TB-TS-1', 'amount' => 5000, 'status' => 'completed'];
    $body = json_encode($payload);

    $response = $this->postJson('/payments/callback', $payload, [
        'X-Provider' => 'telebirr',
        'X-Provider-Signature' => hash_hmac('sha256', $body, 'test-telebirr-secret'),
        'Content-Type' => 'application/json',
    ]);

    $response->assertUnauthorized();
    expect(PaymentGatewayLog::where('event', 'timestamp.missing')->exists())->toBeTrue();
    expect(Payment::count())->toBe(0);
});

test('a stale timestamp is refused even with a valid signature', function () {
    $payload = ['gateway_reference' => 'TB-TS-2', 'amount' => 5000, 'status' => 'completed'];
    $stale = now()->subHour()->timestamp;

    $response = $this->postJson('/payments/callback', $payload, signCallback($payload, 'test-telebirr-secret', $stale));

    $response->assertUnauthorized();
    expect(PaymentGatewayLog::where('event', 'timestamp.expired')->exists())->toBeTrue();
    expect(Payment::count())->toBe(0);
});

test('a future timestamp outside the window is refused', function () {
    $payload = ['gateway_reference' => 'TB-TS-3', 'amount' => 5000, 'status' => 'completed'];
    $future = now()->addHour()->timestamp;

    $response = $this->postJson('/payments/callback', $payload, signCallback($payload, 'test-telebirr-secret', $future));

    $response->assertUnauthorized();
    expect(PaymentGatewayLog::where('event', 'timestamp.expired')->exists())->toBeTrue();
    expect(Payment::count())->toBe(0);
});

test('an empty configured secret rejects every callback', function () {
    config(['services.payments.providers.telebirr.secret' => '']);

    $payload = ['gateway_reference' => 'TB-EMPTY', 'amount' => 100, 'status' => 'completed'];
    $response = $this->postJson('/payments/callback', $payload, signCallback($payload, ''));

    $response->assertUnauthorized();
    expect(PaymentGatewayLog::where('event', 'provider.unknown')->exists())->toBeTrue();
    expect(Payment::count())->toBe(0);
});

test('the callback endpoint is rate limited', function () {
    config(['services.payments.callback_rate_limit' => 3]);

    $payload = ['gateway_reference' => 'TB-RL', 'amount' => 100, 'status' => 'failed'];
    $headers = signCallback($payload, 'test-telebirr-secret');

    for ($i = 0; $i < 3; $i++) {
        $this->postJson('/payments/callback', $payload, $headers)->assertOk();
    }

    $this->postJson('/payments/callback', $payload, $headers)->assertStatus(429);
});

test('a callback signed with the wrong secret is refused and logged', function () {
    $payload = ['gateway_reference' => 'TB-101', 'amount' => 5000, 'status' => 'completed'];

    $response = $this->postJson('/payments/callback', $payload, signCallback($payload, 'attacker-secret'));

    $response->assertUnauthorized();
    expect(PaymentGatewayLog::where('event', 'signature.invalid')->exists())->toBeTrue();
    expect(Payment::count())->toBe(0);
});

test('a provider with no configured secret rejects every callback', function () {
    config(['services.payments.providers.unknownprovider' => null]);

    $payload = ['gateway_reference' => 'XX-1', 'amount' => 100, 'status' => 'completed'];
    $response = $this->postJson('/payments/callback', $payload + ['provider' => 'unknownprovider'], [
        'X-Provider' => 'unknownprovider',
        'X-Provider-Signature' => hash_hmac('sha256', json_encode($payload), 'anything'),
        'Content-Type' => 'application/json',
    ]);

    $response->assertUnauthorized();
    expect(PaymentGatewayLog::where('event', 'provider.unknown')->exists())->toBeTrue();
});

test('a validly signed callback settles the payment and is logged', function () {
    $payload = ['gateway_reference' => 'TB-200', 'amount' => 7000, 'status' => 'completed', 'provider' => 'telebirr'];

    $response = $this->postJson('/payments/callback', $payload, signCallback($payload, 'test-telebirr-secret'));

    $response->assertOk()
        ->assertJson(['success' => true, 'status' => 'completed']);

    $payment = Payment::where('bank_transaction_reference', 'TB-200')->firstOrFail();

    expect($payment->status)->toBe('completed')
        ->and($payment->amount)->toEqual(7000.0)
        ->and($payment->ledgers()->count())->toBeGreaterThan(0);

    $log = PaymentGatewayLog::where('event', 'callback.processed')->latest('id')->firstOrFail();
    expect($log->outcome)->toBe('processed')
        ->and($log->signature_valid)->toBeTrue()
        ->and($log->payment_id)->not->toBeNull();
});

test('the same transaction reference twice creates one payment', function () {
    $payload = ['gateway_reference' => 'TB-300', 'amount' => 2500, 'status' => 'completed', 'provider' => 'telebirr'];
    $headers = signCallback($payload, 'test-telebirr-secret');

    $this->postJson('/payments/callback', $payload, $headers)->assertOk();
    $this->postJson('/payments/callback', $payload, $headers)->assertOk()->assertJson([
        'idempotent' => true,
        'status' => 'already_processed',
    ]);

    expect(Payment::where('bank_transaction_reference', 'TB-300')->count())->toBe(1);
});

test('a failing provider notification resolves to one payment without settling it', function () {
    $payload = ['gateway_reference' => 'TB-400', 'amount' => 900, 'status' => 'failed', 'provider' => 'telebirr'];
    $headers = signCallback($payload, 'test-telebirr-secret');

    $this->postJson('/payments/callback', $payload, $headers)->assertOk();
    $this->postJson('/payments/callback', $payload, $headers)->assertOk()->assertJson([
        'idempotent' => true,
        'status' => 'failed',
    ]);

    expect(Payment::where('bank_transaction_reference', 'TB-400')->count())->toBe(1);
    expect(Payment::where('bank_transaction_reference', 'TB-400')->first()->status)->toBe('failed');
});

test('a failed callback is honoured only once', function () {
    $failed = ['gateway_reference' => 'TB-500', 'amount' => 100, 'status' => 'failed', 'provider' => 'telebirr'];
    $this->postJson('/payments/callback', $failed, signCallback($failed, 'test-telebirr-secret'))->assertOk();

    $paid = ['gateway_reference' => 'TB-500', 'amount' => 100, 'status' => 'completed', 'provider' => 'telebirr'];
    $this->postJson('/payments/callback', $paid, signCallback($paid, 'test-telebirr-secret'))
        ->assertOk()
        ->assertJson(['status' => 'failed']);

    expect(Payment::where('bank_transaction_reference', 'TB-500')->first()->status)->toBe('failed');
});

test('a telebirr payment is never completed by the person who paid it', function () {
    $payer = User::create([
        'name' => 'Payer Abebe',
        'email' => 'abebe.payer@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $response = $this->actingAs($payer)->post('/payments', [
        'payment_type' => 'service_fee',
        'payment_method' => 'telebirr',
        'amount' => 1200,
        'payer_name' => 'Abebe Payer',
        'payer_email' => 'abebe.payer@example.com',
        'payer_phone' => '+251 911 223344',
    ]);

    $response->assertRedirect();

    $payment = Payment::latest('id')->firstOrFail();

    expect($payment->status)->toBe('pending')
        ->and($payment->verified_at)->toBeNull()
        ->and($payment->verified_by)->toBeNull()
        ->and($payment->ledgers()->count())->toBe(0);

    // And nobody can verify their own payment.
    $this->actingAs($payer)->putJson("/payments/{$payment->id}/verify")->assertForbidden();

    expect($payment->fresh()->status)->toBe('pending');
});

test('finance can still verify a pending telebirr payment', function () {
    $payer = User::create([
        'name' => 'Payer Sara',
        'email' => 'sara.payer@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $this->actingAs($payer)->post('/payments', [
        'payment_type' => 'service_fee',
        'payment_method' => 'telebirr',
        'amount' => 900,
        'payer_name' => 'Sara Payer',
        'payer_email' => 'sara.payer@example.com',
    ]);

    $payment = Payment::latest('id')->firstOrFail();

    $this->actingAs($payer)->putJson("/payments/{$payment->id}/verify")->assertForbidden();

    $finance = User::create([
        'name' => 'Finance Officer Rahel',
        'email' => 'rahel.finance@example.com',
        'password' => Hash::make('password123'),
        'role' => 'finance_officer',
    ]);

    $this->actingAs($finance)->putJson("/payments/{$payment->id}/verify")->assertRedirect();

    expect($payment->fresh()->status)->toBe('completed')
        ->and($payment->fresh()->verified_by)->toBe($finance->id);
});
