<?php

use App\Models\Payment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * P0 follow-up: separation of duties on payment confirmation.
 *
 * SRS §83 - whoever made a payment can never verify or reject it, even when
 * they also hold a finance/admin role. The payer cannot confirm their own
 * money, the same rule the developer escrow desk enforces.
 */
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function financeOfficer(string $email): User
{
    return User::create([
        'name' => 'Finance '.$email,
        'email' => $email,
        'password' => Hash::make('password123'),
        'role' => 'finance_officer',
    ]);
}

test('a finance officer who paid cannot verify their own payment', function () {
    $payer = financeOfficer('payer.verify@example.com');

    $payment = Payment::factory()->awaitingConfirmation()->create([
        'user_id' => $payer->id,
        'payer_email' => $payer->email,
    ]);

    $this->actingAs($payer)
        ->putJson("/payments/{$payment->id}/verify")
        ->assertForbidden();

    expect($payment->fresh()->status)->toBe('pending')
        ->and($payment->fresh()->verified_by)->toBeNull();
});

test('a finance officer who paid cannot reject their own payment', function () {
    $payer = financeOfficer('payer.reject@example.com');

    $payment = Payment::factory()->awaitingConfirmation()->create([
        'user_id' => $payer->id,
        'payer_email' => $payer->email,
    ]);

    $this->actingAs($payer)
        ->putJson("/payments/{$payment->id}/reject", ['admin_notes' => 'Refusing my own payment'])
        ->assertForbidden();

    expect($payment->fresh()->status)->toBe('pending');
});

test('an admin who is the payer cannot verify or reject their own payment', function () {
    $admin = User::create([
        'name' => 'Admin Payer',
        'email' => 'admin.payer@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $payment = Payment::factory()->awaitingConfirmation()->create([
        'user_id' => $admin->id,
        'payer_email' => $admin->email,
    ]);

    $this->actingAs($admin)->putJson("/payments/{$payment->id}/verify")->assertForbidden();
    $this->actingAs($admin)->putJson("/payments/{$payment->id}/reject", ['admin_notes' => 'Self reject'])->assertForbidden();

    expect($payment->fresh()->status)->toBe('pending');
});

test('a matching payer email is blocked even when the submitter id differs', function () {
    $officer = financeOfficer('matching.payer@example.com');

    $payment = Payment::factory()->awaitingConfirmation()->create([
        'user_id' => null,
        'payer_email' => $officer->email,
    ]);

    $this->actingAs($officer)
        ->putJson("/payments/{$payment->id}/verify")
        ->assertForbidden();

    expect($payment->fresh()->status)->toBe('pending');
});

test('a finance officer can still verify a payment somebody else made', function () {
    $payer = User::factory()->create(['role' => 'buyer']);

    $payment = Payment::factory()->awaitingConfirmation()->create([
        'user_id' => $payer->id,
        'payer_email' => $payer->email,
    ]);

    $officer = financeOfficer('independent.officer@example.com');

    $this->actingAs($officer)
        ->putJson("/payments/{$payment->id}/verify")
        ->assertRedirect();

    expect($payment->fresh()->status)->toBe('completed')
        ->and($payment->fresh()->verified_by)->toBe($officer->id);
});
