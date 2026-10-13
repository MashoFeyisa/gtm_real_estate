<?php

use App\Models\Agent;
use App\Models\Commission;
use App\Models\Lease;
use App\Models\Order;
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');

    $this->adminUser = User::create([
        'name' => 'Broker Admin Addis',
        'email' => 'admin.closing@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->agentUser = User::create([
        'name' => 'Agent Yohannes',
        'email' => 'agent.yohannes@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Yohannes Real Estate',
        'email' => 'yohannes@example.com',
        'phone' => '+251 91 333 4455',
        'commission_rate' => 2.5,
        'is_active' => true,
    ]);

    $this->saleProperty = Property::create([
        'property_code' => 'ETH-AA-BOL-00000088',
        'title' => 'Bole Brass Luxury Penthouse',
        'slug' => 'bole-brass-luxury-penthouse',
        'price' => 22000000.00,
        'bedrooms' => 3,
        'bathrooms' => 3,
        'area' => 240,
        'building_area' => 240,
        'type' => 'sale',
        'property_category' => 'apartment',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Bole Brass, Addis Ababa',
        'agent_id' => $this->agent->id,
    ]);

    $this->rentProperty = Property::create([
        'property_code' => 'ETH-AA-OLD-00000099',
        'title' => 'Old Airport Diplomatic Villa',
        'slug' => 'old-airport-diplomatic-villa',
        'price' => 120000.00,
        'bedrooms' => 5,
        'bathrooms' => 4,
        'area' => 500,
        'building_area' => 450,
        'type' => 'rent',
        'property_category' => 'villa',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Old Airport, Addis Ababa',
        'agent_id' => $this->agent->id,
    ]);
});

test('can convert accepted purchase offer into sales contract with agreement PDF and agent commission', function () {
    $offer = PropertyOffer::create([
        'property_id' => $this->saleProperty->id,
        'agent_id' => $this->agent->id,
        'buyer_name' => 'Alazar Mengistu',
        'buyer_email' => 'alazar@example.com',
        'buyer_phone' => '0911778899',
        'offer_type' => 'purchase',
        'original_price' => 22000000.00,
        'offered_price' => 21000000.00,
        'current_counter_amount' => 21500000.00,
        'status' => 'accepted',
        'message' => '50% earnest payment, 50% at DARA title deed transfer.',
    ]);

    $response = $this->actingAs($this->adminUser)
        ->post(route('offers.convert-to-deal', $offer));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $offer->refresh();
    expect($offer->order_id)->not->toBeNull()
        ->and($offer->closing_status)->toBe('agreement_generated');

    $order = Order::find($offer->order_id);
    expect($order)->not->toBeNull()
        ->and((float) $order->offer_amount)->toBe(21500000.00)
        ->and($order->status)->toBe('accepted')
        ->and($order->agreement_path)->not->toBeNull();

    // Verify Property is marked as sold
    expect($this->saleProperty->fresh()->status)->toBe('sold')
        ->and($this->saleProperty->fresh()->is_active)->toBeFalse();

    // Verify Commission is generated (2.5% of 21,500,000 = 537,500 ETB)
    $commission = Commission::where('order_id', $order->id)->first();
    expect($commission)->not->toBeNull()
        ->and((float) $commission->commission_amount)->toBe(537500.00)
        ->and($commission->status)->toBe('pending');
});

test('can convert accepted rental offer into lease contract with tenancy agreement PDF and installments', function () {
    $offer = PropertyOffer::create([
        'property_id' => $this->rentProperty->id,
        'agent_id' => $this->agent->id,
        'buyer_name' => 'Sara Alemayehu',
        'buyer_email' => 'sara.alem@example.com',
        'buyer_phone' => '0922889900',
        'offer_type' => 'rental',
        'original_price' => 120000.00,
        'offered_price' => 110000.00,
        'current_counter_amount' => 11500000.00, // 115,000 monthly
        'current_counter_amount' => 115000.00,
        'deposit_proposed' => 230000.00,
        'status' => 'accepted',
        'message' => 'Embassy lease with 2 months deposit.',
    ]);

    $response = $this->actingAs($this->agentUser)
        ->post(route('offers.convert-to-deal', $offer));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $offer->refresh();
    expect($offer->lease_id)->not->toBeNull()
        ->and($offer->closing_status)->toBe('agreement_generated');

    $lease = Lease::find($offer->lease_id);
    expect($lease)->not->toBeNull()
        ->and((float) $lease->monthly_rent)->toBe(115000.00)
        ->and((float) $lease->security_deposit)->toBe(230000.00)
        ->and($lease->schedules)->toHaveCount(12)
        ->and($lease->agreement_document_path)->not->toBeNull();

    // Verify rent property is marked as rented
    expect($this->rentProperty->fresh()->status)->toBe('rented');
});

test('cannot convert non-accepted offer to deal', function () {
    $offer = PropertyOffer::create([
        'property_id' => $this->saleProperty->id,
        'agent_id' => $this->agent->id,
        'buyer_name' => 'Pending Buyer',
        'buyer_email' => 'pending@example.com',
        'buyer_phone' => '0911000000',
        'offer_type' => 'purchase',
        'original_price' => 22000000.00,
        'offered_price' => 19000000.00,
        'status' => 'submitted',
    ]);

    $response = $this->actingAs($this->adminUser)
        ->post(route('offers.convert-to-deal', $offer));

    $response->assertRedirect();
    $response->assertSessionHas('error');
    expect($offer->fresh()->order_id)->toBeNull();
});

test('paying agent commission records double entry debit in financial ledger', function () {
    $commission = Commission::create([
        'agent_id' => $this->agent->id,
        'property_id' => $this->saleProperty->id,
        'property_price' => 20000000.00,
        'commission_rate' => 2.0,
        'commission_amount' => 400000.00,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->adminUser)
        ->patch(route('dashboard.commissions.status', $commission->id), [
            'status' => 'paid',
            'admin_note' => 'Disbursed via CBE transfer FT2026XX',
        ]);

    $response->assertRedirect();
    expect($commission->fresh()->status)->toBe('paid')
        ->and($commission->fresh()->paid_at)->not->toBeNull();

    $this->assertDatabaseHas('financial_ledgers', [
        'category' => 'agent_commission',
        'entry_type' => 'debit',
        'amount' => 400000.00,
        'reference' => 'COMM-'.$commission->id,
    ]);
});

test('offers desk renders conveyance closing stepper and contract action for accepted deals', function () {
    $offer = PropertyOffer::create([
        'property_id' => $this->saleProperty->id,
        'agent_id' => $this->agent->id,
        'buyer_name' => 'Closing Client Daniel',
        'buyer_email' => 'daniel@example.com',
        'buyer_phone' => '0933445566',
        'offer_type' => 'purchase',
        'original_price' => 22000000.00,
        'offered_price' => 21000000.00,
        'status' => 'accepted',
    ]);

    $response = $this->actingAs($this->adminUser)
        ->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Deal Stage: Closing', false)
        ->assertSee('Proceed to Closing &amp; Generate Contract', false)
        ->assertSee('1. Offer Accepted', false)
        ->assertSee('2. Contract Pending', false);
});
