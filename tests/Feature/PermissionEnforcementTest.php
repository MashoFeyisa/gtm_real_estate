<?php

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * P0 security: permission enforcement.
 *
 * Route-level permission middleware now guards the money routes, and a
 * permission key that is not in the catalogue is denied by default instead of
 * silently granted. The administrator boundary (admins hold every permission)
 * is deliberately unchanged.
 */
function permissionUser(string $role, string $email): User
{
    return User::create([
        'name' => ucfirst($role).' Permission Test',
        'email' => $email,
        'password' => Hash::make('password123'),
        'role' => $role,
    ]);
}

function permissionPayment(User $payer): Payment
{
    return Payment::create([
        'user_id' => $payer->id,
        'payer_name' => $payer->name,
        'payer_email' => $payer->email,
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'bank_transfer',
        'amount' => 1000,
        'platform_fee' => 20,
        'tax_amount' => 3,
        'net_amount' => 977,
        'status' => 'completed',
        'verified_at' => now(),
    ]);
}

test('a permission key that is not in the catalogue is denied by default', function () {
    $user = permissionUser('agent', 'deny.agent@example.com');

    expect($user->hasPermission('not.a.real.permission'))->toBeFalse()
        ->and($user->hasPermission('finance.audit'))->toBeFalse()
        ->and($user->hasPermission('totally.made.up'))->toBeFalse();
});

test('a catalogued permission still resolves from the persona defaults', function () {
    $finance = permissionUser('finance_officer', 'granted.finance@example.com');

    expect($finance->hasPermission('refunds.manage'))->toBeTrue()
        ->and($finance->hasPermission('ledger.view'))->toBeTrue();
});

test('the administrator boundary is unchanged: an admin holds every permission', function () {
    $admin = permissionUser('admin', 'boundary.admin@example.com');

    expect($admin->hasPermission('not.a.real.permission'))->toBeTrue()
        ->and($admin->hasPermission('refunds.manage'))->toBeTrue();
});

test('a suspended user is denied even a catalogued permission', function () {
    $finance = permissionUser('finance_officer', 'suspended.finance@example.com');
    $finance->update(['status' => 'suspended']);

    expect($finance->hasPermission('refunds.manage'))->toBeFalse();
});

test('an agent cannot reach the refund route even though the controller would allow it', function () {
    $payer = permissionUser('buyer', 'refund.payer@example.com');
    $payment = permissionPayment($payer);
    $agent = permissionUser('agent', 'refund.agent@example.com');

    $this->actingAs($agent)
        ->post(route('finance.payments.refund', $payment), ['refund_amount' => 100, 'reason' => 'test'])
        ->assertForbidden();

    expect($payment->fresh()->refunded_amount)->toEqual(0);
});

test('finance staff pass the refund route gate', function () {
    $payer = permissionUser('buyer', 'refund.payer2@example.com');
    $payment = permissionPayment($payer);
    $finance = permissionUser('finance_officer', 'refund.finance@example.com');

    $this->actingAs($finance)
        ->post(route('finance.payments.refund', $payment), ['refund_amount' => 100, 'reason' => 'goodwill'])
        ->assertRedirect();

    expect($payment->fresh()->refunded_amount)->toEqual(100.0);
});

test('an administrator passes every money route gate', function () {
    $payer = permissionUser('buyer', 'refund.payer3@example.com');
    $payment = permissionPayment($payer);
    $admin = permissionUser('admin', 'refund.admin@example.com');

    $this->actingAs($admin)
        ->post(route('finance.payments.refund', $payment), ['refund_amount' => 50, 'reason' => 'admin adjustment'])
        ->assertRedirect();

    expect($payment->fresh()->refunded_amount)->toEqual(50.0);
});
