<?php

use App\Mail\OrderConfirmation;
use App\Mail\OrderReceived;
use App\Mail\OrderStatusUpdate;
use App\Models\Agent;
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
        ->assertSessionHas('success', 'Site branding and contact details updated successfully.');

    expect(SiteSetting::get('site_name'))->toBe('Harar Homes');

    $this->get('/')
        ->assertOk()
        ->assertSee('Harar Homes');
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
