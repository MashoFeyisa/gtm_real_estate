<?php

use App\Models\Agent;
use App\Models\Property;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Agent John',
        'email' => 'agent.john@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->user->id,
        'name' => 'John Doe Real Estate',
        'email' => 'john.doe@example.com',
        'phone' => '+251 91 123 4567',
        'is_active' => true,
    ]);

    $this->property = Property::create([
        'title' => 'Bole Atlantic Luxury Penthouse',
        'slug' => 'bole-atlantic-luxury-penthouse',
        'description' => 'A breath-taking luxury penthouse in the heart of Bole with panoramic views.',
        'price' => 12500000.00,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 320,
        'type' => 'sale',
        'property_category' => 'apartment',
        'city' => 'Addis Ababa',
        'address' => 'Bole Medhanialem, Premium Tower',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $this->agent->id,
    ]);
});

test('public pages include opengraph and schema org structured data', function () {
    // 1. Homepage SEO & Schema.org RealEstateAgent JSON-LD
    $homeResponse = $this->get(route('home'));
    $homeResponse->assertStatus(200);
    $homeResponse->assertSee('og:site_name', false);
    $homeResponse->assertSee('og:title', false);
    $homeResponse->assertSee('og:description', false);
    $homeResponse->assertSee('twitter:card', false);
    $homeResponse->assertSee('schema.org', false);
    $homeResponse->assertSee('RealEstateAgent', false);

    // 2. Property Show Page SEO & Schema.org RealEstateListing JSON-LD
    $showResponse = $this->get(route('properties.show', $this->property->slug));
    $showResponse->assertStatus(200);
    $showResponse->assertSee('og:title', false);
    $showResponse->assertSee('RealEstateListing', false);
    $showResponse->assertSee('Bole Atlantic Luxury Penthouse', false);
    $showResponse->assertSee('12500000', false);
});

test('multi-currency switcher is rendered on public pages with live price attributes', function () {
    $response = $this->get(route('properties.index'));
    $response->assertStatus(200);

    // Multi-currency switcher dropdown controls
    $response->assertSee('data-set-currency="ETB"', false);
    $response->assertSee('data-set-currency="USD"', false);
    $response->assertSee('data-set-currency="EUR"', false);
    $response->assertSee('data-set-currency="GBP"', false);
    $response->assertSee('data-set-currency="AED"', false);

    // Price tags carry currency-price class and data-base-price for live conversion
    $response->assertSee('currency-price', false);
    $response->assertSee('data-base-price="12500000"', false);

    // Live prices are exposed site-wide, not just on the index listing page.
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('currency-price', false);

    $this->get(route('properties.show', $this->property->slug))
        ->assertOk()
        ->assertSee('data-base-price="12500000"', false);

    $this->get(route('agents.show', $this->agent))
        ->assertOk()
        ->assertSee('data-base-price="12500000"', false);
});

test('admin currency exchange rates are persisted and exposed to the frontend converter', function () {
    $admin = User::create([
        'name' => 'Currency Admin',
        'email' => 'fx.admin@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post(route('dashboard.settings.update'), [
            'site_name' => 'GTM Real Estate',
            'contact_email' => 'concierge@gtm.et',
            'contact_phone' => '+251 911 000 000',
            'contact_address' => 'Bole Subcity, Addis Ababa',
            'currency_rate_usd' => 200,
            'currency_rate_eur' => 250,
            'currency_rate_gbp' => 300,
            'currency_rate_aed' => 50,
        ])
        ->assertRedirect(route('dashboard', ['section' => 'settings']));

    expect(SiteSetting::get('currency_rate_usd'))->toBe('200')
        ->and(SiteSetting::get('currency_rate_eur'))->toBe('250')
        ->and(SiteSetting::get('currency_rate_gbp'))->toBe('300')
        ->and(SiteSetting::get('currency_rate_aed'))->toBe('50');

    // Public pages expose the admin rates to the client-side conversion engine
    // as "foreign units per 1 ETB" (1 / ETB-per-unit).
    $this->get(route('properties.index'))
        ->assertOk()
        ->assertSee('GTM_CURRENCY_RATES', false)
        ->assertSee('"USD":0.005', false)
        ->assertSee('"EUR":0.004', false)
        ->assertSee('"AED":0.02', false);
});

test('properties index renders interactive leaflet map with valid property coordinates', function () {
    $response = $this->get(route('properties.index'));
    $response->assertStatus(200);

    // Map container & Leaflet assets
    $response->assertSee('id="luxury-properties-map"', false);
    $response->assertSee('id="toggle-map-view-btn"', false);
    $response->assertSee('leaflet.css', false);
    $response->assertSee('leaflet.js', false);

    // Property card has map hover linking id
    $response->assertSee('data-property-card-id="'.$this->property->id.'"', false);
});

test('property detail page includes luxury financing and investment roi calculator', function () {
    $response = $this->get(route('properties.show', $this->property->slug));
    $response->assertStatus(200);

    // Financial calculator container
    $response->assertSee('id="luxury-financial-calculator"', false);
    $response->assertSee('data-property-price="12500000"', false);

    // Dual Tabs
    $response->assertSee('id="calc-tab-mortgage"', false);
    $response->assertSee('id="calc-tab-roi"', false);

    // Sliders & outputs
    $response->assertSee('id="calc-down-slider"', false);
    $response->assertSee('id="calc-term-select"', false);
    $response->assertSee('id="calc-interest-slider"', false);
    $response->assertSee('id="calc-monthly-total"', false);
    $response->assertSee('id="calc-roi-gross-yield"', false);

    // Neighborhood Location Map
    $response->assertSee('id="single-property-map"', false);
    $response->assertSee('Explore Location & Neighborhood', false);
});
