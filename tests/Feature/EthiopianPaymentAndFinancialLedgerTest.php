<?php

use App\Models\Agent;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->adminUser = User::create([
        'name' => 'Finance Admin Dawit',
        'email' => 'admin.finance@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->agentUser = User::create([
        'name' => 'Agent Mesfin',
        'email' => 'agent.mesfin@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Mesfin Girma',
        'email' => 'mesfin@example.com',
        'phone' => '+251 91 144 5566',
        'is_active' => true,
    ]);

    $this->buyerUser = User::create([
        'name' => 'Buyer Henok',
        'email' => 'henok@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $this->property = Property::create([
        'property_code' => 'ETH-AA-BOLE-00000101',
        'title' => 'Bole Medhanialem Executive Residence',
        'slug' => 'bole-medhanialem-executive-residence',
        'price' => 30000000.00,
        'bedrooms' => 4,
        'bathrooms' => 4,
        'area' => 320,
        'building_area' => 350,
        'type' => 'sale',
        'property_category' => 'villa',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Bole Medhanialem, Addis Ababa',
        'agent_id' => $this->agent->id,
    ]);

    $this->offer = PropertyOffer::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'user_id' => $this->buyerUser->id,
        'buyer_name' => 'Henok Tesfaye',
        'buyer_email' => 'henok@example.com',
        'buyer_phone' => '+251 91 123 9999',
        'offer_type' => 'purchase',
        'original_price' => 30000000.00,
        'offered_price' => 28500000.00,
        'earnest_deposit' => 150000.00,
        'status' => 'pending',
    ]);
});

test('a submitted Telebirr payment stays pending until finance verifies it', function () {
    $response = $this->actingAs($this->buyerUser)->post(route('payments.store'), [
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'telebirr',
        'amount' => 150000.00,
        'payer_name' => 'Henok Tesfaye',
        'payer_email' => 'henok@example.com',
        'payer_phone' => '+251 91 123 9999',
        'property_id' => $this->property->id,
        'payable_type' => PropertyOffer::class,
        'payable_id' => $this->offer->id,
        'bank_transaction_reference' => 'TB2627998102',
        'notes' => 'Telebirr 5% earnest money deposit for Bole Villa',
    ]);

    $response->assertRedirect();

    // SRS §83: no payment method verifies itself. A payer can never be the
    // verifier of their own payment, so the row is pending and unsettled.
    $this->assertDatabaseHas('payments', [
        'payer_name' => 'Henok Tesfaye',
        'payment_method' => 'telebirr',
        'amount' => 150000.00,
        'status' => 'pending',
        'bank_transaction_reference' => 'TB2627998102',
    ]);

    $payment = Payment::where('bank_transaction_reference', 'TB2627998102')->first();
    expect($payment)->not->toBeNull()
        ->and($payment->verified_at)->toBeNull()
        ->and($payment->verified_by)->toBeNull()
        ->and($payment->platform_fee)->toBeGreaterThan(0)
        ->and($payment->net_amount)->toBeLessThan(150000.00);

    expect($payment->ledgers()->count())->toBe(0);

    // The payer cannot verify their own payment.
    $this->actingAs($this->buyerUser)->putJson(route('payments.verify', $payment))->assertForbidden();

    // Finance verifies it and only then is the ledger written and the payable advanced.
    $this->actingAs($this->adminUser)->put(route('payments.verify', $payment), [
        'bank_transaction_reference' => 'TB2627998102',
    ])->assertRedirect();

    $payment->refresh();
    expect($payment->status)->toBe('completed')
        ->and($payment->verified_by)->toBe($this->adminUser->id)
        ->and($payment->verified_at)->not->toBeNull();

    $this->assertDatabaseHas('financial_ledgers', [
        'payment_id' => $payment->id,
        'category' => 'earnest_deposit',
        'entry_type' => 'credit',
        // 150,000 - 3,000 platform fee - 450 VAT on the fee.
        'amount' => 146550.00,
    ]);

    $this->assertDatabaseHas('financial_ledgers', [
        'payment_id' => $payment->id,
        'category' => 'platform_fee',
        'entry_type' => 'credit',
    ]);

    // Payable sync happens on settlement, not on submission.
    $this->offer->refresh();
    expect($this->offer->status)->toBe('under_review');
});

test('guest must sign in before submitting a payment', function () {
    $response = $this->post(route('payments.store'));

    $response->assertRedirect(route('register'))
        ->assertSessionHas('signup_prompt', 'Register / Login to continue: Create a client account or sign in before submitting a payment.');

    expect(Payment::count())->toBe(0);
});

test('buyer can submit bank transfer with physical deposit slip upload', function () {
    $file = UploadedFile::fake()->create('cbe_deposit_slip.pdf', 800, 'application/pdf');

    $response = $this->actingAs($this->buyerUser)->post(route('payments.store'), [
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'bank_transfer',
        'amount' => 200000.00,
        'payer_name' => 'Henok Tesfaye',
        'payer_email' => 'henok@example.com',
        'payer_phone' => '+251 91 123 9999',
        'property_id' => $this->property->id,
        'bank_name' => 'Commercial Bank of Ethiopia',
        'bank_transaction_reference' => 'FT2627990145',
        'deposit_slip' => $file,
        'notes' => 'Branch deposit at Bole Medhanialem branch',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'payer_name' => 'Henok Tesfaye',
        'payment_method' => 'bank_transfer',
        'bank_name' => 'Commercial Bank of Ethiopia',
        'bank_transaction_reference' => 'FT2627990145',
        'amount' => 200000.00,
        'status' => 'pending',
    ]);
});

test('admin can verify pending bank deposit slip and confirm transaction', function () {
    $payment = Payment::create([
        'payable_type' => PropertyOffer::class,
        'payable_id' => $this->offer->id,
        'property_id' => $this->property->id,
        'user_id' => $this->buyerUser->id,
        'payer_name' => 'Henok Tesfaye',
        'payer_email' => 'henok@example.com',
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'bank_transfer',
        'bank_name' => 'Awash Bank',
        'bank_transaction_reference' => 'AW2026-9910',
        'amount' => 100000.00,
        'platform_fee' => 2000.00,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->adminUser)->put(route('payments.verify', $payment), [
        'admin_notes' => 'Verified on Awash Bank corporate statement.',
        'bank_transaction_reference' => 'AW2026-9910-CONFIRMED',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    expect($payment->status)->toBe('completed');
    expect($payment->verified_by)->toBe($this->adminUser->id);
    expect($payment->verified_at)->not->toBeNull();
    expect($payment->bank_transaction_reference)->toBe('AW2026-9910-CONFIRMED');

    $this->assertDatabaseHas('financial_ledgers', [
        'payment_id' => $payment->id,
        'category' => 'earnest_deposit',
        'entry_type' => 'credit',
    ]);
});

test('admin can reject invalid deposit slip', function () {
    $payment = Payment::create([
        'property_id' => $this->property->id,
        'user_id' => $this->buyerUser->id,
        'payer_name' => 'Suspicious Payer',
        'payer_email' => 'suspicious@example.com',
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'bank_transfer',
        'amount' => 50000.00,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->adminUser)->put(route('payments.reject', $payment), [
        'admin_notes' => 'Reference number not found on bank clearance statement.',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    expect($payment->status)->toBe('failed');
    expect($payment->admin_notes)->toContain('Reference number not found');
});

test('payment receipt requires sign-in and is visible to the payer', function () {
    $payment = Payment::create([
        'transaction_number' => 'TXN-ETH-2026-TEST99',
        'property_id' => $this->property->id,
        'user_id' => $this->buyerUser->id,
        'payer_name' => 'Henok Tesfaye',
        'payer_email' => 'henok@example.com',
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'telebirr',
        'amount' => 75000.00,
        'platform_fee' => 1500.00,
        'tax_amount' => 225.00,
        'status' => 'completed',
    ]);

    $this->get(route('payments.receipt', $payment->transaction_number))
        ->assertRedirect(route('login'));

    $response = $this->actingAs($this->buyerUser)->get(route('payments.receipt', $payment->transaction_number));

    $response->assertOk();
    $response->assertSee('Official Payment Receipt');
    $response->assertSee('TXN-ETH-2026-TEST99');
    $response->assertSee('75,000.00 ETB');
    $response->assertSee('Telebirr (ቴሌብር)');
    $response->assertSee('Henok Tesfaye');
});

test('client cannot view another payer payment receipt', function () {
    $payment = Payment::create([
        'transaction_number' => 'TXN-ETH-2026-PRIVATE01',
        'property_id' => $this->property->id,
        'user_id' => $this->buyerUser->id,
        'payer_name' => 'Henok Tesfaye',
        'payer_email' => 'henok@example.com',
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'telebirr',
        'amount' => 75000.00,
        'platform_fee' => 1500.00,
        'tax_amount' => 225.00,
        'status' => 'completed',
    ]);

    $otherClient = User::factory()->create(['role' => 'buyer']);

    $this->actingAs($otherClient)
        ->get(route('payments.receipt', $payment->transaction_number))
        ->assertForbidden();
});

test('admin dashboard renders payment desk with data-section payments and pending confirmation banner', function () {
    Payment::create([
        'transaction_number' => 'TXN-ETH-2026-PENDING01',
        'property_id' => $this->property->id,
        'user_id' => $this->buyerUser->id,
        'payer_name' => 'Henok Tesfaye',
        'payer_email' => 'henok@example.com',
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'bank_transfer',
        'amount' => 50000.00,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->adminUser)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Payment Receipt(s) Awaiting Confirmation');
    $response->assertSee('data-section="payments"', false);
    $response->assertSee('TXN-ETH-2026-PENDING01');
    $response->assertSee('✓ Verify');
});
