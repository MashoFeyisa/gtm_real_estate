<?php

use App\Models\Agent;
use App\Models\CleaningTask;
use App\Models\Listing;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyReport;
use App\Models\RentSchedule;
use App\Models\User;

beforeEach(function () {
    $this->seed();
});

test('all 17 target user types resolve to dedicated dashboard routes', function () {
    $expectedDashboards = [
        'buyer@realestate.com' => '/dashboard/buyer',
        'tenant@realestate.com' => '/dashboard/tenant',
        'traveler@realestate.com' => '/dashboard/guest',
        'investor@realestate.com' => '/dashboard/investor',
        'owner@realestate.com' => '/dashboard/seller',
        'host@realestate.com' => '/dashboard/host',
        'agent@realestate.com' => '/agent-portal',
        'company@realestate.com' => '/company',
        'developer@realestate.com' => '/developer-portal',
        'propertymanager@realestate.com' => '/dashboard/property-manager',
        'service@realestate.com' => '/dashboard/professional',
        'cleaner@realestate.com' => '/dashboard/cleaner',
        'admin@realestate.com' => '/dashboard',
        'verification@realestate.com' => '/dashboard/verification-officer',
        'moderator@realestate.com' => '/dashboard/moderator',
        'finance@realestate.com' => '/finance',
        'support@realestate.com' => '/dashboard/customer-support',
    ];

    foreach ($expectedDashboards as $email => $expectedPath) {
        $user = User::query()->where('email', $email)->first();
        expect($user)->not->toBeNull("User {$email} must exist.");
        expect($user->dashboard_url)->toContain($expectedPath);
    }
});

test('each of the 17 target user types can access their dedicated dashboard when authenticated', function () {
    $dashboards = [
        'buyer@realestate.com' => '/dashboard/buyer',
        'tenant@realestate.com' => '/dashboard/tenant',
        'traveler@realestate.com' => '/dashboard/guest',
        'investor@realestate.com' => '/dashboard/investor',
        'owner@realestate.com' => '/dashboard/seller',
        'host@realestate.com' => '/dashboard/host',
        'agent@realestate.com' => '/agent-portal',
        'company@realestate.com' => '/company',
        'developer@realestate.com' => '/developer-portal',
        'propertymanager@realestate.com' => '/dashboard/property-manager',
        'service@realestate.com' => '/dashboard/professional',
        'cleaner@realestate.com' => '/dashboard/cleaner',
        'admin@realestate.com' => '/dashboard',
        'verification@realestate.com' => '/dashboard/verification-officer',
        'moderator@realestate.com' => '/dashboard/moderator',
        'finance@realestate.com' => '/finance',
        'support@realestate.com' => '/dashboard/customer-support',
    ];

    foreach ($dashboards as $email => $uri) {
        $user = User::query()->where('email', $email)->first();
        $response = $this->actingAs($user)->get($uri);
        expect($response->status())->toBe(200, "User {$email} failed to load {$uri}");
    }
});

test('buyer core workflow: saving search, viewing, and making offers', function () {
    $buyer = User::query()->where('email', 'buyer@realestate.com')->first();
    $property = Property::query()->first();

    // 1. Save search
    $response = $this->actingAs($buyer)->post('/buyer/saved-searches', [
        'title' => 'Bole 3-Bedroom Villas',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'max_price' => 25000000,
        'alert_frequency' => 'daily',
    ]);
    $response->assertRedirect();
    $this->assertDatabaseHas('saved_searches', [
        'user_id' => $buyer->id,
        'title' => 'Bole 3-Bedroom Villas',
    ]);

    // 2. Request viewing
    $response = $this->actingAs($buyer)->post("/properties/{$property->id}/viewings", [
        'preferred_date' => now()->addDays(2)->format('Y-m-d'),
        'preferred_time' => '14:30',
        'notes' => 'Looking to purchase for family residence',
    ]);
    $response->assertRedirect();
    $this->assertDatabaseHas('property_viewings', [
        'user_id' => $buyer->id,
        'property_id' => $property->id,
    ]);

    // 3. Make offer
    $response = $this->actingAs($buyer)->post("/properties/{$property->id}/offers", [
        'buyer_name' => $buyer->name,
        'buyer_email' => $buyer->email,
        'buyer_phone' => '+251911223344',
        'offered_price' => 12500000,
        'financing_type' => 'cash',
        'contingencies' => 'Subject to structural inspection',
    ]);
    $response->assertRedirect();
    $this->assertDatabaseHas('property_offers', [
        'user_id' => $buyer->id,
        'property_id' => $property->id,
        'offered_price' => 12500000,
    ]);
});

test('cleaner workflow: updating task status, completing checklist, and writing audit log', function () {
    $cleaner = User::query()->where('email', 'cleaner@realestate.com')->first();
    $task = CleaningTask::query()->where('cleaner_user_id', $cleaner->id)->first();

    expect($task)->not->toBeNull();

    $response = $this->actingAs($cleaner)->patch("/cleaner/tasks/{$task->id}", [
        'status' => 'inspection',
        'notes' => 'Turnaround completed, ready for final check.',
        'completed_checklist' => ['0', '1'],
    ]);

    $response->assertRedirect();
    $task->refresh();
    expect($task->status)->toBe('inspection');
    expect($task->inspected_at)->not->toBeNull();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $cleaner->id,
        'action' => 'cleaning.status_update',
    ]);
});

test('moderator workflow: approving listing and triaging incident report', function () {
    $moderator = User::query()->where('email', 'moderator@realestate.com')->first();
    $listing = Listing::query()->first();
    $report = PropertyReport::query()->first();

    // Listing publish decision
    $response = $this->actingAs($moderator)->patch("/moderator/listings/{$listing->id}", [
        'status' => 'published',
        'moderation_notes' => 'All criteria verified.',
    ]);
    $response->assertRedirect();
    expect($listing->fresh()->status)->toBe('published');

    // Report resolve decision
    if ($report) {
        $response = $this->actingAs($moderator)->patch("/moderator/reports/{$report->id}", [
            'status' => 'resolved',
            'resolution_notes' => 'Duplicate advertisement removed.',
        ]);
        $response->assertRedirect();
        expect($report->fresh()->status)->toBe('resolved');
    }
});

test('verification officer workflow: verifying property document and company license', function () {
    $officer = User::query()->where('email', 'verification@realestate.com')->first();
    $doc = PropertyDocument::query()->first();
    $agent = Agent::query()->first();

    // Verify document
    if ($doc) {
        $response = $this->actingAs($officer)->patch("/verification-officer/documents/{$doc->id}", [
            'status' => 'verified',
            'verification_notes' => 'Official Addis Ababa cadastre seal checked.',
        ]);
        $response->assertRedirect();
        expect($doc->fresh()->status)->toBe('verified');
        expect($doc->fresh()->verified_by)->toBe($officer->id);
    }

    // Verify agency license
    if ($agent) {
        $response = $this->actingAs($officer)->patch("/verification-officer/companies/{$agent->id}", [
            'decision' => 'verify',
            'notes' => 'Ministry of Trade trade license valid.',
        ]);
        $response->assertRedirect();
        expect($agent->fresh()->license_verified)->toBeTrue();
    }
});

test('media access control enforces permission gating and revokes access when disabled', function () {
    $buyer = User::query()->where('email', 'buyer@realestate.com')->first();
    $admin = User::query()->where('email', 'admin@realestate.com')->first();

    // 1. Regular buyer accesses avatar
    $response = $this->actingAs($buyer)->get("/media/avatars/{$buyer->id}");
    $response->assertStatus(200);

    // 2. Revoke media access for buyer
    $buyer->update(['media_access_enabled' => false]);
    $response = $this->actingAs($buyer->fresh())->get("/media/avatars/{$buyer->id}");
    $response->assertStatus(403);

    // 3. Document confidentiality: confidential document cannot be viewed by unauthorized buyer
    $privateDoc = PropertyDocument::factory()->create([
        'user_id' => $admin->id,
        'access_level' => 'admin_only',
        'file_name' => 'confidential_contract.pdf',
        'file_path' => 'property_documents/confidential.pdf',
    ]);

    $buyer->update(['media_access_enabled' => true]);
    $response = $this->actingAs($buyer)->get("/media/documents/{$privateDoc->id}");
    $response->assertStatus(403);

    // Admin can view
    $response = $this->actingAs($admin)->get("/media/documents/{$privateDoc->id}");
    $response->assertStatus(200);
});

test('suspended users are blocked across all dashboard and permission-protected routes', function () {
    $tenant = User::query()->where('email', 'tenant@realestate.com')->first();

    $tenant->update(['status' => 'suspended']);

    $response = $this->actingAs($tenant->fresh())->get('/dashboard/tenant');
    $response->assertStatus(403);

    expect($tenant->fresh()->hasPermission('leases.view'))->toBeFalse();
    expect($tenant->fresh()->hasPermission('dashboard.view'))->toBeFalse();
});

test('investor can add an asset to portfolio via post request', function () {
    $investor = User::query()->where('email', 'investor@realestate.com')->first();

    $response = $this->actingAs($investor)->post('/investor/portfolios', [
        'asset_name' => 'Bole Atlas 3BR Luxury Flat',
        'asset_type' => 'residential',
        'location' => 'Addis Ababa, Bole',
        'purchase_price' => 18500000,
        'current_valuation' => 22000000,
        'monthly_rental_income' => 150000,
        'monthly_expenses' => 15000,
        'occupancy_rate' => 100,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('investor_portfolios', [
        'user_id' => $investor->id,
        'asset_name' => 'Bole Atlas 3BR Luxury Flat',
        'current_valuation' => 22000000,
    ]);
});

test('tenant workflow: view rent schedule and settle upcoming installment', function () {
    $tenant = User::query()->where('email', 'tenant@realestate.com')->first();
    expect($tenant)->not->toBeNull();

    $schedule = RentSchedule::query()
        ->whereHas('lease', fn ($q) => $q->where('tenant_user_id', $tenant->id))
        ->where('status', '!=', 'paid')
        ->first();

    expect($schedule)->not->toBeNull();

    $response = $this->actingAs($tenant)->post(route('rent-schedules.pay', $schedule->id), [
        'amount_paid' => $schedule->amount_due,
        'payment_method' => 'telebirr',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($schedule->fresh()->status)->toBe('paid');
});
