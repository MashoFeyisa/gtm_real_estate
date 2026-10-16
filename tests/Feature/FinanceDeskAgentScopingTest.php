<?php

use App\Models\Agent;
use App\Models\FinancialLedger;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->financeOfficer = User::factory()->create(['role' => 'finance_officer']);

    // Agent A (the acting user under test)
    $this->agentAUser = User::factory()->create(['role' => 'agent']);
    $this->agentA = Agent::create([
        'user_id' => $this->agentAUser->id,
        'name' => 'Agent Alpha',
        'email' => 'agent.alpha@example.com',
        'phone' => '+251911000081',
        'is_active' => true,
    ]);
    $this->propertyA = Property::create([
        'property_code' => 'ETH-SCOPING-A-001',
        'title' => 'Agent Alpha Own Listing',
        'slug' => 'agent-alpha-own-listing',
        'type' => 'sale',
        'property_category' => 'apartment',
        'price' => 9000000,
        'city' => 'Addis Ababa',
        'agent_id' => $this->agentA->id,
        'is_active' => true,
        'status' => 'published',
    ]);

    // Agent B (the other agent whose data must stay invisible to Agent A)
    $this->agentBUser = User::factory()->create(['role' => 'agent']);
    $this->agentB = Agent::create([
        'user_id' => $this->agentBUser->id,
        'name' => 'Agent Beta',
        'email' => 'agent.beta@example.com',
        'phone' => '+251911000082',
        'is_active' => true,
    ]);
    $this->propertyB = Property::create([
        'property_code' => 'ETH-SCOPING-B-002',
        'title' => 'Agent Beta Confidential Listing',
        'slug' => 'agent-beta-confidential-listing',
        'type' => 'sale',
        'property_category' => 'villa',
        'price' => 25000000,
        'city' => 'Addis Ababa',
        'agent_id' => $this->agentB->id,
        'is_active' => true,
        'status' => 'published',
    ]);

    // Agent A's own pending payment (must stay visible to Agent A).
    $this->ownPayment = Payment::create([
        'property_id' => $this->propertyA->id,
        'user_id' => $this->agentAUser->id,
        'payer_name' => 'Own Deal Payer Alpha',
        'payer_email' => 'payer.alpha@example.com',
        'payer_phone' => '+251911223301',
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'bank_transfer',
        'currency' => 'ETB',
        'amount' => 500000,
        'status' => 'pending',
    ]);

    // Another client's pending payment on Agent B's listing (must stay invisible to Agent A).
    $this->foreignPayment = Payment::create([
        'property_id' => $this->propertyB->id,
        'user_id' => User::factory()->create()->id,
        'payer_name' => 'Confidential Buyer Beta',
        'payer_email' => 'payer.beta@example.com',
        'payer_phone' => '+251911223302',
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'bank_transfer',
        'currency' => 'ETB',
        'amount' => 1250000,
        'status' => 'pending',
    ]);

    // Ledger entries tied to each payment.
    $this->ownLedger = FinancialLedger::create([
        'payment_id' => $this->ownPayment->id,
        'entry_type' => 'credit',
        'category' => 'earnest_deposit',
        'amount' => 500000,
        'currency' => 'ETB',
        'description' => 'Agent Alpha own deal deposit',
        'reference' => 'LEDGER-ALPHA-OWN-111',
        'user_id' => $this->agentAUser->id,
    ]);

    $this->foreignLedger = FinancialLedger::create([
        'payment_id' => $this->foreignPayment->id,
        'entry_type' => 'credit',
        'category' => 'earnest_deposit',
        'amount' => 1250000,
        'currency' => 'ETB',
        'description' => 'Agent Beta confidential deal deposit',
        'reference' => 'LEDGER-BETA-SECRET-222',
        'user_id' => $this->agentBUser->id,
    ]);
});

test('an agent reaches the finance desk and sees only their own ledger rows', function () {
    // Agents may view their own deal ledger, but the confirmation queue (with
    // its Confirm/Reject actions) is finance-only.
    $this->actingAs($this->agentAUser)
        ->get(route('finance.index'))
        ->assertOk()
        ->assertSee('Agent Alpha own deal deposit')
        ->assertDontSee('Payments Awaiting Confirmation');
});

test('an agent cannot see other agents pending payments or ledger entries', function () {
    $this->actingAs($this->agentAUser)
        ->get(route('finance.index'))
        ->assertOk()
        ->assertDontSee('Confidential Buyer Beta')
        ->assertDontSee('LEDGER-BETA-SECRET-222')
        ->assertDontSee('Agent Beta confidential deal deposit');
});

test('admins still see every pending payment and ledger entry', function () {
    $this->actingAs($this->admin)
        ->get(route('finance.index'))
        ->assertOk()
        ->assertSee('Confidential Buyer Beta')
        ->assertSee('LEDGER-BETA-SECRET-222');
});

test('finance officers still see every pending payment', function () {
    $this->actingAs($this->financeOfficer)
        ->get(route('finance.index'))
        ->assertOk()
        ->assertSee('Confidential Buyer Beta');
});

test('agent b sees their own ledger rows but not agent a ledger', function () {
    $this->actingAs($this->agentBUser)
        ->get(route('finance.index'))
        ->assertOk()
        ->assertSee('Agent Beta confidential deal deposit')
        ->assertDontSee('LEDGER-ALPHA-OWN-111')
        ->assertDontSee('Agent Alpha own deal deposit');
});

test('the payment confirmation queue is finance-only and hidden from agents', function () {
    // The queue carries Confirm / Reject actions, which are finance work. An
    // agent holds payments.view, not payments.manage, so the queue is hidden
    // from them while finance officers still get it.
    $this->actingAs($this->agentAUser)
        ->get(route('finance.index'))
        ->assertOk()
        ->assertDontSee('Payments Awaiting Confirmation');

    $this->actingAs($this->financeOfficer)
        ->get(route('finance.index'))
        ->assertOk()
        ->assertSee('Payments Awaiting Confirmation');
});

test('agents do not receive global company cashflow charts on the finance desk', function () {
    $this->actingAs($this->agentAUser)
        ->get(route('finance.index'))
        ->assertOk()
        ->assertDontSee('Monthly Cash Inflow vs. Outflow (ETB)')
        ->assertDontSee('id="finance-cashflow-chart"', false);

    $this->actingAs($this->admin)
        ->get(route('finance.index'))
        ->assertOk()
        ->assertSee('Monthly Cash Inflow vs. Outflow (ETB)')
        ->assertSee('id="finance-cashflow-chart"', false);
});

test('an agent sees their own scoped payments in a read-only section', function () {
    $this->actingAs($this->agentAUser)
        ->get(route('finance.index'))
        ->assertOk()
        ->assertSee('My Payments')
        ->assertSee('Own Deal Payer Alpha')
        ->assertDontSee('Confidential Buyer Beta')
        // No finance confirm/reject actions for a non-finance viewer.
        ->assertDontSee('✓ Confirm')
        ->assertDontSee(route('payments.verify', $this->ownPayment), false);
});

test('finance keeps the confirmation queue and does not get the agent-only section', function () {
    $this->actingAs($this->financeOfficer)
        ->get(route('finance.index'))
        ->assertOk()
        ->assertSee('Payments Awaiting Confirmation')
        ->assertDontSee('My Payments');
});
