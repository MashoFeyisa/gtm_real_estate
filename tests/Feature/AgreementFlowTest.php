<?php

use App\Mail\OrderConfirmation;
use App\Mail\OrderReceived;
use App\Mail\OrderStatusUpdate;
use App\Models\Agent;
use App\Models\Commission;
use App\Models\Order;
use App\Models\Property;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

function createAgentWithProperty(string $type): array
{
    $agent = Agent::create([
        'name' => 'Agreement Agent',
        'email' => 'agreement.agent@example.com',
        'phone' => '+251900111222',
        'bio' => 'Agreement agent',
    ]);

    $account = User::factory()->create([
        'name' => 'Agreement Agent',
        'email' => 'agreement.agent@example.com',
        'password' => Hash::make('agentpass1'),
        'role' => 'agent',
    ]);

    $agent->forceFill(['user_id' => $account->id])->save();

    $property = Property::create([
        'title' => 'Agreement Test Property',
        'slug' => 'agreement-test-property',
        'description' => 'A property used to test the agreement flow.',
        'price' => 150000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 150,
        'type' => $type,
        'status' => 'published',
        'city' => 'Harar',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    return [$agent, $account, $property];
}

test('buy request on a sale property stores type sale', function () {
    [, , $property] = createAgentWithProperty('sale');

    $this->post('/properties/'.$property->id.'/orders', [
        'name' => 'Sale Buyer',
        'email' => 'sale.buyer@example.com',
        'offer_amount' => 140000,
    ])->assertRedirect()->assertSessionHas('success', 'Your buy request was sent. The agent will contact you shortly.');

    $order = Order::query()->where('email', 'sale.buyer@example.com')->first();
    expect($order->type)->toBe('sale');
    expect($order->lease_months)->toBeNull();
});

test('request on a rent property stores type rent with lease details', function () {
    [, , $property] = createAgentWithProperty('rent');

    $this->post('/properties/'.$property->id.'/orders', [
        'name' => 'Rent Client',
        'email' => 'rent.client@example.com',
        'offer_amount' => 900,
        'lease_start' => now()->addWeek()->format('Y-m-d'),
        'lease_months' => 24,
    ])->assertRedirect()->assertSessionHas('success', 'Your rental request was sent. The agent will contact you shortly.');

    $order = Order::query()->where('email', 'rent.client@example.com')->first();
    expect($order->type)->toBe('rent');
    expect($order->lease_months)->toBe(24);
    expect($order->lease_start)->not->toBeNull();
});

test('accepting an order generates a branded agreement pdf', function () {
    Storage::fake('local');
    Mail::fake();

    [$agent, $account, $property] = createAgentWithProperty('sale');

    $order = Order::create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'type' => 'sale',
        'name' => 'PDF Buyer',
        'email' => 'pdf.buyer@example.com',
        'offer_amount' => 145000,
        'status' => 'pending',
    ]);

    $this->actingAs($account)
        ->patch('/agent-portal/orders/'.$order->id.'/status', ['status' => 'accepted'])
        ->assertRedirect('/agent-portal')
        ->assertSessionHas('success', 'The buy request was accepted and the agreement was generated.');

    $order->refresh();

    expect($order->status)->toBe('accepted');
    expect($order->agreement_path)->not->toBeNull();
    expect($order->agreed_at)->not->toBeNull();
    Storage::disk('local')->assertExists($order->agreement_path);

    $pdf = Storage::disk('local')->get($order->agreement_path);
    expect(str_starts_with($pdf, '%PDF'))->toBeTrue();
    // Sale agreements use the Amharic purchase template, so the Ethiopic font is embedded.
    expect($pdf)->toContain('NotoSansEthiopic');

    Mail::assertSent(OrderStatusUpdate::class, fn ($mail) => $mail->order->id === $order->id);
});

test('accepted rental order generates a rental agreement pdf', function () {
    Storage::fake('local');

    [$agent, $account, $property] = createAgentWithProperty('rent');

    $order = Order::create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'type' => 'rent',
        'name' => 'PDF Tenant',
        'email' => 'pdf.tenant@example.com',
        'offer_amount' => 850,
        'lease_months' => 12,
        'status' => 'pending',
    ]);

    $this->actingAs($account)
        ->patch('/agent-portal/orders/'.$order->id.'/status', ['status' => 'accepted'])
        ->assertRedirect('/agent-portal');

    $order->refresh();
    expect($order->agreement_path)->not->toBeNull();

    $pdf = Storage::disk('local')->get($order->agreement_path);
    expect(str_starts_with($pdf, '%PDF'))->toBeTrue();
    // The Amharic lease template embeds the Ethiopic font subset.
    expect($pdf)->toContain('NotoSansEthiopic');
});

test('agent can download the generated agreement pdf', function () {
    Storage::fake('local');

    [$agent, $account, $property] = createAgentWithProperty('sale');

    $order = Order::create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'type' => 'sale',
        'name' => 'Download Buyer',
        'email' => 'download.buyer@example.com',
        'status' => 'pending',
    ]);

    $this->actingAs($account)
        ->patch('/agent-portal/orders/'.$order->id.'/status', ['status' => 'accepted']);

    $order->refresh();

    $this->actingAs($account)
        ->get('/orders/'.$order->id.'/agreement')
        ->assertOk();
});

test('guests cannot download an agreement', function () {
    [$agent, , $property] = createAgentWithProperty('sale');

    $order = Order::create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'type' => 'sale',
        'name' => 'Guest Tester',
        'email' => 'guest@example.com',
        'status' => 'accepted',
        'agreement_path' => 'agreements/test.pdf',
    ]);

    $this->get('/orders/'.$order->id.'/agreement')->assertRedirect('/login');
});

test('sending a buy or rent request notifies the agent and confirms to the client', function () {
    Mail::fake();

    [$agent, , $property] = createAgentWithProperty('sale');

    $this->post('/properties/'.$property->id.'/orders', [
        'name' => 'Notified Buyer',
        'email' => 'notified.buyer@example.com',
        'offer_amount' => 100000,
    ])->assertRedirect();

    Mail::assertSent(OrderReceived::class, fn ($mail) => $mail->order->email === 'notified.buyer@example.com');
    Mail::assertSent(OrderConfirmation::class, fn ($mail) => $mail->order->email === 'notified.buyer@example.com');

    $order = Order::query()->where('email', 'notified.buyer@example.com')->first();
    expect($order->agent_id)->toBe($agent->id);
});

test('admin can update site branding and contact details', function () {
    $admin = User::factory()->create([
        'name' => 'Branding Admin',
        'email' => 'branding.admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post('/dashboard/settings', [
            'site_name' => 'Harar Homes',
            'contact_email' => 'hello@hararhomes.com',
            'contact_phone' => '+251 900000000',
            'contact_address' => 'Harar, Kezira',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Site settings, content, and images updated successfully.');

    expect(SiteSetting::get('site_name'))->toBe('Harar Homes');

    $this->get('/')
        ->assertOk()
        ->assertSee('Harar Homes');
});

test('site logo renders on header, login page, and admin dashboard', function () {
    SiteSetting::set('site_logo', 'images/logo1.jpg');

    expect($this->get('/'))
        ->assertOk()
        ->assertSee('images/logo1.jpg', false);

    expect($this->get('/login'))
        ->assertOk()
        ->assertSee('images/logo1.jpg', false);

    $admin = User::factory()->create([
        'name' => 'Logo Admin',
        'email' => 'logo.admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    expect($this->actingAs($admin)->get('/dashboard'))
        ->assertOk()
        ->assertSee('images/logo1.jpg', false);
});

test('uploaded logo is served from storage and falls back when missing', function () {
    Storage::fake('public');

    SiteSetting::set('site_logo', 'images/brand-logo.jpg');

    // No file exists yet: the bundled default logo is rendered instead.
    $this->get('/')
        ->assertOk()
        ->assertSee('images/logo.svg', false)
        ->assertDontSee('images/brand-logo.jpg', false);

    // Once the upload exists on the public disk, it is served from /storage.
    Storage::disk('public')->put('images/brand-logo.jpg', 'logo-bytes');

    $this->get('/')
        ->assertOk()
        ->assertSee('/storage/images/brand-logo.jpg', false);
});

test('admin dashboard shows agreement status for orders', function () {
    [$agent, , $property] = createAgentWithProperty('sale');

    $admin = User::factory()->create([
        'name' => 'Orders Admin',
        'email' => 'orders.admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    Order::create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'type' => 'sale',
        'name' => 'Signed Buyer',
        'email' => 'signed.buyer@example.com',
        'status' => 'accepted',
        'agreement_path' => 'agreements/signed.pdf',
        'agreed_at' => now(),
    ]);

    Order::create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'type' => 'rent',
        'name' => 'Pending Tenant',
        'email' => 'pending.tenant@example.com',
        'offer_amount' => 700,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('Buy & Rent Orders');
    $response->assertSee('Agreement generated');
    $response->assertSee('Download PDF');
    $response->assertSee('Signed Buyer');
    $response->assertSee('No agreement yet');
    $response->assertSee('Pending Tenant');
});

test('marking a rent property updates status to rented not sold', function () {
    $admin = User::factory()->create([
        'name' => 'Rent Admin',
        'email' => 'rent.admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $property = Property::create([
        'title' => 'Rentable Apartment',
        'slug' => 'rentable-apartment',
        'description' => 'A rental apartment.',
        'price' => 800,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 90,
        'type' => 'rent',
        'status' => 'published',
        'city' => 'Harar',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->post('/dashboard/properties/'.$property->id.'/toggle-sold')
        ->assertRedirect('/dashboard')
        ->assertSessionHas('success', 'Property rental status updated successfully.');

    expect($property->refresh()->status)->toBe('rented');
    expect($property->is_active)->toBeTrue();

    $this->actingAs($admin)
        ->post('/dashboard/properties/'.$property->id.'/toggle-sold');

    expect($property->refresh()->status)->toBe('available');
});

test('marking a sale property still updates status to sold', function () {
    $admin = User::factory()->create([
        'name' => 'Sale Admin',
        'email' => 'sale.admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $property = Property::create([
        'title' => 'Sellable Villa',
        'slug' => 'sellable-villa',
        'description' => 'A villa for sale.',
        'price' => 300000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 200,
        'type' => 'sale',
        'status' => 'published',
        'city' => 'Harar',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->post('/dashboard/properties/'.$property->id.'/toggle-sold');

    expect($property->refresh()->status)->toBe('sold');
});

test('admin can generate agreement for an order directly from dashboard', function () {
    $admin = User::factory()->create([
        'name' => 'Agreement Admin',
        'email' => 'agr.admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $property = Property::create([
        'title' => 'Admin Direct Luxury Home',
        'slug' => 'admin-direct-luxury-home',
        'description' => 'A property without an agent.',
        'price' => 5000000,
        'bedrooms' => 5,
        'bathrooms' => 4,
        'area' => 350,
        'type' => 'sale',
        'status' => 'published',
        'city' => 'Addis Ababa',
        'is_active' => true,
    ]);

    $order = Order::create([
        'property_id' => $property->id,
        'agent_id' => null,
        'type' => 'sale',
        'name' => 'Direct Client',
        'email' => 'direct.client@example.com',
        'offer_amount' => 5000000,
        'status' => 'pending',
    ]);

    expect($order->hasAgreement())->toBeFalse();

    $response = $this->actingAs($admin)
        ->post(route('dashboard.orders.generateAgreement', $order));

    $response->assertRedirect(route('dashboard', ['section' => 'orders']));
    $order->refresh();

    expect($order->status)->toBe('accepted');
    expect($order->admin_status)->toBe('approved');
    expect($order->hasAgreement())->toBeTrue();
    expect(Storage::disk('local')->exists($order->agreement_path))->toBeTrue();

    // Verify admin can download the agreement
    $download = $this->actingAs($admin)
        ->get(route('orders.agreement', $order));

    $download->assertOk();
});

test('admin can sell property posted by self directly and generate official agreement', function () {
    $admin = User::factory()->create([
        'name' => 'Selling Admin',
        'email' => 'selling.admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $property = Property::create([
        'title' => 'Harar Commercial Hub',
        'slug' => 'harar-commercial-hub',
        'description' => 'Admin self-managed property.',
        'price' => 1200000,
        'bedrooms' => 0,
        'bathrooms' => 2,
        'area' => 180,
        'type' => 'rent',
        'status' => 'published',
        'city' => 'Harar',
        'is_active' => true,
        'agent_id' => null, // posted by admin
    ]);

    $response = $this->actingAs($admin)
        ->post(route('dashboard.properties.generateAgreement', $property), [
            'buyer_name' => 'Abebe Tenant',
            'buyer_email' => 'abebe@example.com',
            'buyer_phone' => '+251911223344',
            'deal_price' => 1200000,
            'lease_months' => 24,
            'admin_note' => '2-year commercial lease agreed.',
        ]);

    $response->assertRedirect(route('dashboard', ['section' => 'orders']));

    $property->refresh();
    expect($property->status)->toBe('rented');
    expect($property->is_active)->toBeFalse();

    $order = Order::where('property_id', $property->id)->latest()->first();
    expect($order)->not->toBeNull();
    expect($order->name)->toBe('Abebe Tenant');
    expect($order->lease_months)->toBe(24);
    expect($order->hasAgreement())->toBeTrue();
    expect(Storage::disk('local')->exists($order->agreement_path))->toBeTrue();
});

test('admin has full control over agent commission rate and commission records', function () {
    $admin = User::factory()->create([
        'name' => 'Commission Admin',
        'email' => 'comm.admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $agent = Agent::create([
        'name' => 'Fast Agent',
        'email' => 'fast@agent.com',
        'phone' => '+251911998877',
        'commission_rate' => 5.0,
    ]);

    // Admin updates agent's default rate
    $rateResponse = $this->actingAs($admin)
        ->put(route('dashboard.agents.commissionRate', $agent), [
            'commission_rate' => 7.5,
        ]);

    $rateResponse->assertRedirect();
    expect($agent->refresh()->commission_rate)->toBe(7.5);

    $property = Property::create([
        'title' => 'Commission Test House',
        'slug' => 'commission-test-house',
        'price' => 1000000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 150,
        'type' => 'sale',
        'status' => 'sold',
        'agent_id' => $agent->id,
    ]);

    $order = Order::create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'type' => 'sale',
        'name' => 'Buyer',
        'email' => 'buyer@example.com',
        'offer_amount' => 1000000,
        'status' => 'accepted',
    ]);

    // Create a commission and admin updates its rate, amount, and status
    $commission = Commission::create([
        'order_id' => $order->id,
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'property_price' => 1000000,
        'commission_rate' => 5.0,
        'commission_amount' => 50000,
        'status' => 'pending',
    ]);

    $commResponse = $this->actingAs($admin)
        ->patch(route('dashboard.commissions.status', $commission), [
            'status' => 'paid',
            'commission_rate' => 8.0,
            'admin_note' => 'Approved and paid via bank transfer',
        ]);

    $commResponse->assertRedirect();
    $commission->refresh();
    expect($commission->status)->toBe('paid');
    expect($commission->commission_rate)->toBe(8.0);
    expect($commission->commission_amount)->toBe(80000.0);
    expect($commission->paid_at)->not->toBeNull();
});

test('admin can view edit agreement content page', function () {
    [$agent, $account, $property] = createAgentWithProperty('sale');

    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin.test@example.com',
        'role' => 'admin',
    ]);

    $order = Order::create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'type' => 'sale',
        'name' => 'John Buyer',
        'email' => 'john.buyer@example.com',
        'offer_amount' => 500000,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('dashboard.orders.editAgreement', $order));

    $response->assertOk()
        ->assertSee('Edit Agreement Content')
        ->assertSee('John Buyer')
        ->assertSee('Agreement Agent')
        ->assertSee('Agreement Test Property');
});

test('non-admin cannot access edit agreement content page', function () {
    [$agent, $account, $property] = createAgentWithProperty('sale');

    $order = Order::create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'type' => 'sale',
        'name' => 'John Buyer',
        'email' => 'john.buyer@example.com',
        'offer_amount' => 500000,
        'status' => 'pending',
    ]);

    // Guest redirected
    $this->get(route('dashboard.orders.editAgreement', $order))
        ->assertRedirect('/login');

    // Agent user is forbidden
    $this->actingAs($account)
        ->get(route('dashboard.orders.editAgreement', $order))
        ->assertForbidden();
});

test('admin can save edited agreement content and generate pdf with custom content', function () {
    Storage::fake('local');

    [$agent, $account, $property] = createAgentWithProperty('sale');

    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin.test2@example.com',
        'role' => 'admin',
    ]);

    $order = Order::create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'type' => 'sale',
        'name' => 'Original Buyer',
        'email' => 'original@example.com',
        'offer_amount' => 500000,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)
        ->post(route('dashboard.orders.storeAgreementContent', $order), [
            'seller_name' => 'Custom Seller Name',
            'seller_phone' => '+251 911 223344',
            'seller_email' => 'custom.seller@example.com',
            'seller_address' => 'Bole, Addis Ababa',
            'buyer_name' => 'Custom Buyer Name',
            'buyer_email' => 'custom.buyer@example.com',
            'buyer_phone' => '+251 922 334455',
            'property_title' => 'Custom Luxury Villa',
            'property_city' => 'Addis Ababa',
            'property_address' => 'Bole Atlas 45',
            'property_category' => 'Villa',
            'bedrooms' => '5',
            'bathrooms' => '4',
            'area' => '400',
            'offer_amount' => '750,000.00',
            'special_terms' => 'Special custom condition: 20% down payment on handover.',
            'agent_note' => 'Agent negotiated 5% closing discount.',
        ]);

    $response->assertRedirect(route('dashboard', ['section' => 'orders']))
        ->assertSessionHas('success');

    $order->refresh();

    expect($order->status)->toBe('accepted');
    expect($order->admin_status)->toBe('approved');
    expect($order->agreement_content)->toBeArray();
    expect($order->agreement_content['seller_name'])->toBe('Custom Seller Name');
    expect($order->agreement_content['buyer_name'])->toBe('Custom Buyer Name');
    expect($order->agreement_content['special_terms'])->toBe('Special custom condition: 20% down payment on handover.');
    expect($order->agreement_path)->not->toBeNull();

    Storage::disk('local')->assertExists($order->agreement_path);
    $pdf = Storage::disk('local')->get($order->agreement_path);
    expect(str_starts_with($pdf, '%PDF'))->toBeTrue();
});

test('property agreement modal with submit_action edit redirects to edit agreement page', function () {
    [$agent, $account, $property] = createAgentWithProperty('sale');

    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin.test3@example.com',
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)
        ->post(route('dashboard.properties.generateAgreement', $property), [
            'buyer_name' => 'Direct Modal Buyer',
            'buyer_email' => 'direct.buyer@example.com',
            'deal_price' => 300000,
            'submit_action' => 'edit',
        ]);

    $order = Order::query()->where('email', 'direct.buyer@example.com')->first();
    expect($order)->not->toBeNull();

    $response->assertRedirect(route('dashboard.orders.editAgreement', $order))
        ->assertSessionHas('success');
});
