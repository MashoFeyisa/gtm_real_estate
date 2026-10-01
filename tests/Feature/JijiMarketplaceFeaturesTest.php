<?php

use App\Models\Agent;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\Property;
use App\Models\User;
use App\Services\AgreementPdfService;

test('property page displays jiji style agent contact call whatsapp and etb pricing', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $agent = Agent::query()->create([
        'name' => 'Kassahun Desta',
        'email' => 'kassahun@example.com',
        'phone' => '+251911445566',
        'bio' => 'Top real estate agent in Addis Ababa',
        'user_id' => $user->id,
    ]);

    $property = Property::query()->create([
        'title' => 'Ayat Modern Compound Villa',
        'slug' => 'ayat-modern-compound-villa',
        'description' => 'Beautiful villa in Ayat compound.',
        'price' => 38000000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 350,
        'type' => 'sale',
        'property_category' => 'villa',
        'status' => 'available',
        'city' => 'Addis Ababa',
        'address' => 'Ayat Zone 3',
        'featured' => true,
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $response = $this->get('/properties/'.$property->slug);

    $response->assertOk()
        ->assertSee('Kassahun Desta')
        ->assertSee('ETB 38,000,000')
        ->assertSee('350')
        ->assertSee('sqm')
        ->assertSee('tel:+251911445566')
        ->assertSee('https://wa.me/251911445566', false)
        ->assertSee('Call Agent')
        ->assertSee('WhatsApp')
        ->assertDontSee('Request Call Back')
        ->assertSee('Send Formal Buy Request');
});

test('client can submit a quick call back request with phone and name', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $agent = Agent::query()->create([
        'name' => 'Sara Haile',
        'email' => 'sara.agent@example.com',
        'phone' => '+251922334455',
        'bio' => 'Luxury property advisor',
        'user_id' => $user->id,
    ]);

    $property = Property::query()->create([
        'title' => 'Bole Atlantic Apartment',
        'slug' => 'bole-atlantic-apartment',
        'price' => 65000,
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area' => 140,
        'type' => 'rent',
        'status' => 'available',
        'city' => 'Addis Ababa',
        'address' => 'Bole Medhanialem',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $response = $this->post('/properties/'.$property->id.'/orders', [
        'name' => 'Dawit Girma',
        'phone' => '+251911998877',
        'request_kind' => 'callback',
        'message' => 'Please call me this afternoon regarding a visit.',
    ]);

    $response->assertRedirect('/properties/'.$property->slug)
        ->assertSessionHas('success', 'Your call back request was sent. The agent will call you shortly.');

    $this->assertDatabaseHas('orders', [
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'name' => 'Dawit Girma',
        'phone' => '+251911998877',
        'status' => 'pending',
    ]);

    $inquiry = Inquiry::query()->where('phone', '+251911998877')->first();
    expect($inquiry)->not->toBeNull();
    expect($inquiry->agent_id)->toBe($agent->id);
    expect($inquiry->subject)->toContain('Call Back Request');
});

test('agent storefront shows properties with type filtering and etb pricing', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $agent = Agent::query()->create([
        'name' => 'Henok Tesfaye',
        'email' => 'henok@example.com',
        'phone' => '+251933445566',
        'bio' => 'Commercial and residential agent',
        'user_id' => $user->id,
    ]);

    $saleProperty = Property::query()->create([
        'title' => 'CMC Residence For Sale',
        'slug' => 'cmc-residence-for-sale',
        'price' => 28000000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 210,
        'type' => 'sale',
        'status' => 'available',
        'city' => 'Addis Ababa',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $rentProperty = Property::query()->create([
        'title' => 'Kazanchis Suite For Rent',
        'slug' => 'kazanchis-suite-for-rent',
        'price' => 45000,
        'bedrooms' => 1,
        'bathrooms' => 1,
        'area' => 80,
        'type' => 'rent',
        'status' => 'available',
        'city' => 'Addis Ababa',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    // View all agent listings
    $allResponse = $this->get('/agents/'.$agent->id);
    $allResponse->assertOk()
        ->assertSee('Henok Tesfaye')
        ->assertSee('tel:+251933445566')
        ->assertSee('https://wa.me/251933445566', false)
        ->assertSee('CMC Residence For Sale')
        ->assertSee('Kazanchis Suite For Rent')
        ->assertSee('ETB 28,000,000')
        ->assertSee('ETB 45,000')
        ->assertSee('210 sqm')
        ->assertSee('80 sqm');

    // Filter sale only
    $saleResponse = $this->get('/agents/'.$agent->id.'?type=sale');
    $saleResponse->assertOk()
        ->assertSee('CMC Residence For Sale')
        ->assertDontSee('Kazanchis Suite For Rent');

    // Filter rent only
    $rentResponse = $this->get('/agents/'.$agent->id.'?type=rent');
    $rentResponse->assertOk()
        ->assertSee('Kazanchis Suite For Rent')
        ->assertDontSee('CMC Residence For Sale');
});

test('properties index shows etb pricing and sqm area tags', function () {
    $property = Property::query()->create([
        'title' => 'Sarbet Modern Flat',
        'slug' => 'sarbet-modern-flat',
        'price' => 18500000,
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area' => 125,
        'type' => 'sale',
        'status' => 'available',
        'city' => 'Addis Ababa',
        'is_active' => true,
    ]);

    $response = $this->get('/properties');

    $response->assertOk()
        ->assertSee('Sarbet Modern Flat')
        ->assertSee('ETB 18,500,000')
        ->assertSee('125 sqm');
});

test('agreement seller name is the person who posted the property', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $agent = Agent::query()->create([
        'name' => 'Almaz Tefera',
        'email' => 'almaz.poster@example.com',
        'phone' => '+251911556677',
        'bio' => 'Independent property poster',
        'user_id' => $user->id,
    ]);

    $property = Property::query()->create([
        'title' => 'Gerji Luxury Residence',
        'slug' => 'gerji-luxury-residence',
        'price' => 25000000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 190,
        'type' => 'sale',
        'status' => 'available',
        'city' => 'Addis Ababa',
        'address' => 'Gerji Imperial',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $order = Order::create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'type' => 'sale',
        'name' => 'Buyer Kebede',
        'email' => 'buyer.kebede@example.com',
        'phone' => '+251922114433',
        'offer_amount' => 24500000,
        'status' => 'accepted',
    ]);

    $order->loadMissing(['property.agent', 'agent']);

    $pdfService = app(AgreementPdfService::class);
    $poster = $order->property?->agent ?? $order->agent;

    expect($poster)->not->toBeNull();
    expect($poster->name)->toBe('Almaz Tefera');

    // Test the purchase agreement view rendering contains the poster name as seller
    $viewHtml = view('agreements.purchase-amharic', [
        'order' => $order,
        'property' => $order->property,
        'agent' => $order->agent,
        'seller' => [
            'name' => $poster->name,
            'phone' => $poster->phone,
            'email' => $poster->email,
            'address' => 'Addis Ababa · Gerji Imperial',
        ],
        'company' => [
            'name' => 'GTP Real Estate',
            'tagline' => 'Real Estate',
            'email' => 'gtmrealstate@gmail.com',
            'phone' => '+251 993722346',
            'address' => 'Harar, Ethiopia',
            'logo' => public_path('images/logo.svg'),
        ],
        'signature' => [
            'signed_at' => now(),
            'verification' => 'ABCDE-12345',
            'method' => 'Electronic acceptance',
        ],
    ])->render();

    expect($viewHtml)->toContain('Almaz Tefera');
    expect($viewHtml)->toContain('1.1 ሻጭ');
});

test('user role is removed when adding a user and only agent or admin are allowed', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Attempting to add a user with role 'user' fails validation
    $this->actingAs($admin)
        ->post('/dashboard/users', [
            'name' => 'Attempted User',
            'email' => 'attempted.user@example.com',
            'password' => 'Secret123!',
            'role' => 'user',
        ])
        ->assertSessionHasErrors('role');

    // Adding as agent succeeds
    $this->actingAs($admin)
        ->post('/dashboard/users', [
            'name' => 'Valid Agent User',
            'email' => 'valid.agent@example.com',
            'password' => 'Secret123!',
            'role' => 'agent',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'valid.agent@example.com',
        'role' => 'agent',
    ]);

    // Role select on dashboard does not offer User as an option for adding a new user
    $response = $this->actingAs($admin)->get('/dashboard?section=users');
    $response->assertOk();
    $response->assertDontSee('<option value="user" >User</option>', false);
});
