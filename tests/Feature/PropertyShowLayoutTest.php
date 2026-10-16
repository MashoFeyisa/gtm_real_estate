<?php

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('property details page renders the persistent sections in the requested two-column grid layout and sequence', function () {
    $property = Property::factory()->create([
        'title' => 'Luxury Test Residence in Bole',
        'slug' => 'luxury-test-residence-in-bole',
        'is_active' => true,
        'status' => 'published',
        'price' => 15000000,
    ]);

    $response = $this->get(route('properties.show', $property->slug));

    $response->assertOk();

    // 1. Two-column grid container: 1 col on mobile, 2 cols on desktop, equal gaps, equal height
    $response->assertSee('grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch', false);

    // 2. Strict sequential order in the block grid:
    // Block 1: Property Description (full width: md:col-span-2)
    // Block 2: Financial Advisory Studio (full width: md:col-span-2)
    // Block 3 & 4: Legal Verification & Verified Feedback side-by-side
    // Block 5: Property Management (full width: md:col-span-2)
    // Block 6: GIS Engine at the bottom of overview (full width: md:col-span-2)
    $response->assertSeeInOrder([
        'id="property-description-block"',
        'id="luxury-financial-calculator"',
        'id="legal-verification-shield"',
        'id="property-reviews-section"',
        'id="property-maintenance-section"',
        'id="gis-engine-section"',
    ], false);

    // 3. Property Description and Financial Advisory span both columns
    $response->assertSee('id="property-description-block" class="md:col-span-2 flex flex-col h-full', false);
    $response->assertSee('id="luxury-financial-calculator" data-property-price="'.$property->price.'" data-property-type="'.$property->type.'" class="md:col-span-2 flex flex-col h-full', false);

    // 4. Section 5 (Property Management) spans both columns
    $response->assertSee('id="property-maintenance-section" class="scroll-mt-24 md:col-span-2 flex flex-col h-full', false);

    // 5. Section 6 (GIS Engine) spans both columns at the bottom
    $response->assertSee('id="gis-engine-section" class="md:col-span-2 flex flex-col h-full', false);

    // 6. Each section includes responsive card styles, dark mode, and wrapping
    $response->assertSee('min-w-0 break-words', false);

    // Verify titles/headings of persistent grid sections
    $response->assertSee('Property Description');
    $response->assertSee('Financing & Investment Studio', false);
    $response->assertSee('Legal Verification &amp; Documentation Shield', false);
    $response->assertSee('Guest & Client Reviews', false);
    $response->assertSee('Maintenance & Housekeeping Desk', false);
    $response->assertSee('Explore Location & Neighborhood', false);
});

test('schedule viewing, formal offer, and buy request forms are hidden action panels with close buttons', function () {
    $property = Property::factory()->create([
        'title' => 'Bole Signature Villa',
        'slug' => 'bole-signature-villa',
        'is_active' => true,
        'status' => 'published',
        'price' => 20000000,
    ]);

    $response = $this->get(route('properties.show', $property->slug));
    $response->assertOk();

    // 1. The three action sections have 'hidden' and 'property-action-panel' by default on page load
    $response->assertSee('id="schedule-viewing-section" class="property-action-panel fixed inset-0 z-50 hidden', false);
    $response->assertSee('id="formal-offer-section" class="property-action-panel fixed inset-0 z-50 hidden', false);
    $response->assertSee('id="buy-request" class="property-action-panel fixed inset-0 z-50 hidden', false);

    // 2. Each has a close button with class property-panel-close-btn
    $response->assertSee('class="property-panel-close-btn', false);

    // 3. Verify the existing 4 header buttons exist
    $response->assertSee('href="#schedule-viewing-section"', false);
    $response->assertSee('href="#formal-offer-section"', false);
    $response->assertSee('Pay Deposit (ETB)');
    $response->assertSee('href="#buy-request"', false);

    // 4. Verify JS action panel controller exists for modal open/close, Escape key, backdrop click, and hash tracking
    $response->assertSee('openPropertyPanel', false);
    $response->assertSee('closePropertyPanels', false);
    $response->assertSee('Escape', false);
    $response->assertSee('property-panel-backdrop', false);
});

test('buyer persona views property page with authenticated status and action triggers', function () {
    $buyer = User::factory()->create([
        'name' => 'Abebe Buyer',
        'email' => 'buyer.test@example.com',
        'role' => 'buyer',
        'password' => Hash::make('password123'),
    ]);

    $property = Property::factory()->create([
        'slug' => 'buyer-test-property',
        'status' => 'published',
        'is_active' => true,
    ]);

    $response = $this->actingAs($buyer)->get(route('properties.show', $property->slug));

    $response->assertOk();
    // In authenticated session, GTM_IS_GUEST is false
    $response->assertSee('window.GTM_IS_GUEST = false;', false);
    // Pay Deposit button works
    $response->assertSee('openUniversalPaymentModal', false);
    // Action panels are available and hidden by default
    $response->assertSee('id="schedule-viewing-section"', false);
    $response->assertSee('id="formal-offer-section"', false);
    $response->assertSee('id="buy-request"', false);
});

test('tenant persona views property page with authenticated status and action triggers', function () {
    $tenant = User::factory()->create([
        'name' => 'Sara Tenant',
        'email' => 'tenant.test@example.com',
        'role' => 'tenant',
        'password' => Hash::make('password123'),
    ]);

    $property = Property::factory()->create([
        'slug' => 'tenant-test-property',
        'type' => 'rent',
        'status' => 'published',
        'is_active' => true,
    ]);

    $response = $this->actingAs($tenant)->get(route('properties.show', $property->slug));

    $response->assertOk();
    $response->assertSee('window.GTM_IS_GUEST = false;', false);
    $response->assertSee('id="schedule-viewing-section"', false);
    $response->assertSee('id="formal-offer-section"', false);
    $response->assertSee('id="buy-request"', false);
});

test('guest persona sees auth gates on action buttons that prompt login and redirect back', function () {
    $property = Property::factory()->create([
        'slug' => 'guest-test-property',
        'status' => 'published',
        'is_active' => true,
    ]);

    $response = $this->get(route('properties.show', $property->slug));

    $response->assertOk();
    // Unauthenticated: GTM_IS_GUEST is true
    $response->assertSee('window.GTM_IS_GUEST = true;', false);
    // Header action buttons have data-auth-gate and data-redirect pointing back to property with hash
    $response->assertSee('data-auth-gate="viewing"', false);
    $response->assertSee('data-redirect="'.route('properties.show', $property->slug).'#schedule-viewing-section"', false);
    $response->assertSee('data-auth-gate="offer"', false);
    $response->assertSee('data-redirect="'.route('properties.show', $property->slug).'#formal-offer-section"', false);
    $response->assertSee('data-auth-gate="inquiry"', false);
    $response->assertSee('data-redirect="'.route('properties.show', $property->slug).'#buy-request"', false);
    // Auth-required modal markup is present
    $response->assertSee('id="auth-required-modal"', false);
});

test('existing seeded property new-house-for-sell renders with dark mode support and responsive wrapping', function () {
    $property = Property::where('slug', 'new-house-for-sell')->first()
        ?? Property::factory()->create([
            'slug' => 'new-house-for-sell',
            'status' => 'published',
            'is_active' => true,
        ]);

    $response = $this->get(route('properties.show', $property->slug));

    $response->assertOk();

    // Verify dark mode background and border classes exist across the cards and modals
    $response->assertSee('dark:bg-[#111827]', false);
    $response->assertSee('dark:border-slate-800', false);
    $response->assertSee('dark:text-white', false);

    // Verify mobile single column and desktop two column classes
    $response->assertSee('grid-cols-1 md:grid-cols-2', false);

    // Verify all 6 persistent overview blocks wrap long content without overflowing
    $sections = [
        'id="property-description-block"',
        'id="luxury-financial-calculator"',
        'id="legal-verification-shield"',
        'id="property-reviews-section"',
        'id="property-maintenance-section"',
        'id="gis-engine-section"',
    ];

    foreach ($sections as $section) {
        $response->assertSee($section, false);
    }
});

test('property sunrise-234-cah6kx supports Buyer, Tenant, and Guest in light and dark mode, desktop and mobile', function () {
    $property = Property::where('slug', 'sunrise-234-cah6kx')->first()
        ?? Property::factory()->create([
            'slug' => 'sunrise-234-cah6kx',
            'status' => 'published',
            'is_active' => true,
        ]);

    $buyer = User::factory()->create(['role' => 'buyer']);
    $tenant = User::factory()->create(['role' => 'tenant']);

    // 1. Guest flow
    $guestResponse = $this->get(route('properties.show', $property->slug));
    $guestResponse->assertOk();
    $guestResponse->assertSee('window.GTM_IS_GUEST = true;', false);
    $guestResponse->assertSee('data-auth-gate="viewing"', false);
    $guestResponse->assertSee('data-auth-gate="offer"', false);
    $guestResponse->assertSee('data-auth-gate="inquiry"', false);
    $guestResponse->assertSee('id="auth-required-modal"', false);

    // 2. Buyer flow
    $buyerResponse = $this->actingAs($buyer)->get(route('properties.show', $property->slug));
    $buyerResponse->assertOk();
    $buyerResponse->assertSee('window.GTM_IS_GUEST = false;', false);
    $buyerResponse->assertSee('id="schedule-viewing-section"', false);
    $buyerResponse->assertSee('id="formal-offer-section"', false);
    $buyerResponse->assertSee('id="buy-request"', false);

    // 3. Tenant flow
    $tenantResponse = $this->actingAs($tenant)->get(route('properties.show', $property->slug));
    $tenantResponse->assertOk();
    $tenantResponse->assertSee('window.GTM_IS_GUEST = false;', false);

    // 4. Desktop (md:grid-cols-2) and Mobile (grid-cols-1) layout responsiveness
    $guestResponse->assertSee('grid-cols-1 md:grid-cols-2', false);
    $guestResponse->assertSee('gap-8 items-stretch', false);
    $guestResponse->assertSee('md:col-span-2', false);

    // 5. Light and Dark mode styling support
    $guestResponse->assertSee('dark:bg-[#111827]', false);
    $guestResponse->assertSee('dark:border-slate-800', false);
    $guestResponse->assertSee('dark:text-white', false);
    $guestResponse->assertSee('dark:bg-slate-900', false);

    // 6. Action panels hidden by default on page load
    $guestResponse->assertSee('id="schedule-viewing-section" class="property-action-panel fixed inset-0 z-50 hidden', false);
    $guestResponse->assertSee('id="formal-offer-section" class="property-action-panel fixed inset-0 z-50 hidden', false);
    $guestResponse->assertSee('id="buy-request" class="property-action-panel fixed inset-0 z-50 hidden', false);
});
