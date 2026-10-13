<?php

use App\Models\Listing;
use App\Models\Permission;
use App\Models\Property;
use App\Models\PropertyReport;
use App\Models\PropertyReview;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->moderator = User::create([
        'name' => 'Moderator Mulu',
        'email' => 'moderator.desk2@example.com',
        'password' => Hash::make('password123'),
        'role' => 'moderator',
    ]);

    $this->buyer = User::create([
        'name' => 'Buyer Bogale',
        'email' => 'buyer.moderation@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $this->property = Property::create([
        'title' => 'Moderated Listing Property',
        'slug' => 'moderated-listing-property',
        'price' => 7000000,
        'type' => 'sale',
        'status' => 'available',
        'is_active' => true,
        'city' => 'Addis Ababa',
    ]);

    $this->listing = Listing::create([
        'property_id' => $this->property->id,
        'listing_type' => 'sale',
        'title' => 'Gate Check Villa Listing',
        'price' => 7000000,
        'status' => 'pending_review',
    ]);

    $this->review = PropertyReview::create([
        'property_id' => $this->property->id,
        'user_id' => $this->buyer->id,
        'rating' => 5,
        'review_title' => 'Nice view',
        'review_body' => 'Stayed for two nights and it was lovely.',
        'status' => 'published',
    ]);

    $this->report = PropertyReport::create([
        'report_number' => 'RPT-MOD-0001',
        'subject' => 'Duplicated listing',
        'reason' => 'duplicate',
        'description' => 'The same photos appear on three other listings.',
        'status' => 'pending',
    ]);
});

test('moderator desk renders the moderation action buttons', function () {
    $response = $this->actingAs($this->moderator)->get(route('role.portal'));

    $response->assertOk();
    $response->assertSee('Listing Reviews');
    $response->assertSee('Publish');
    $response->assertSee('Reject');
    $response->assertSee('Resolve');
    $response->assertSee('Hide');
});

test('moderator can approve a pending listing', function () {
    $this->actingAs($this->moderator)
        ->post(route('moderation.listings.decision', $this->listing), ['decision' => 'approve'])
        ->assertRedirect();

    expect($this->listing->fresh()->status)->toBe('approved');
});

test('moderator can publish an approved listing', function () {
    $this->actingAs($this->moderator)
        ->post(route('moderation.listings.decision', $this->listing), ['decision' => 'publish'])
        ->assertRedirect();

    $listing = $this->listing->fresh();

    expect($listing->status)->toBe('published');
    expect($listing->published_at)->not()->toBeNull();
});

test('moderator can reject a listing back for correction', function () {
    $this->actingAs($this->moderator)
        ->post(route('moderation.listings.decision', $this->listing), ['decision' => 'reject', 'notes' => 'Photos do not match the property.'])
        ->assertRedirect();

    expect($this->listing->fresh()->status)->toBe('rejected');
});

test('moderator can suspend a published listing', function () {
    $this->actingAs($this->moderator)
        ->post(route('moderation.listings.decision', $this->listing), ['decision' => 'publish'])
        ->assertRedirect();

    $this->actingAs($this->moderator)
        ->post(route('moderation.listings.decision', $this->listing->fresh()), ['decision' => 'suspend'])
        ->assertRedirect();

    expect($this->listing->fresh()->status)->toBe('paused');
});

test('moderator can hide and restore a review', function () {
    $this->actingAs($this->moderator)
        ->post(route('moderation.reviews.decision', $this->review), ['decision' => 'hide'])
        ->assertRedirect();

    expect($this->review->fresh()->status)->toBe('hidden');

    $this->actingAs($this->moderator)
        ->post(route('moderation.reviews.decision', $this->review->fresh()), ['decision' => 'approve'])
        ->assertRedirect();

    expect($this->review->fresh()->status)->toBe('published');
});

test('moderator can resolve a property report', function () {
    $this->actingAs($this->moderator)
        ->put(route('reports.update', $this->report), [
            'status' => 'resolved',
            'resolution_notes' => 'Duplicate removed and the seller contacted.',
        ])
        ->assertRedirect();

    $report = $this->report->fresh();

    expect($report->status)->toBe('resolved');
    expect($report->resolved_by)->toBe($this->moderator->id);
    expect($report->resolution_notes)->toBe('Duplicate removed and the seller contacted.');
});

test('non moderators cannot make moderation decisions', function () {
    $this->actingAs($this->buyer)
        ->post(route('moderation.listings.decision', $this->listing), ['decision' => 'approve'])
        ->assertForbidden();

    $this->actingAs($this->buyer)
        ->put(route('reports.update', $this->report), ['status' => 'dismissed'])
        ->assertForbidden();

    expect($this->listing->fresh()->status)->toBe('pending_review');
});

test('moderator loses listing decisions when the permission is revoked', function () {
    UserPermissionOverride::create([
        'user_id' => $this->moderator->id,
        'permission_id' => Permission::query()->where('key', 'listings.review')->value('id'),
        'granted' => false,
    ]);

    $this->actingAs($this->moderator->fresh())
        ->post(route('moderation.listings.decision', $this->listing), ['decision' => 'approve'])
        ->assertForbidden();

    expect($this->listing->fresh()->status)->toBe('pending_review');
});

test('moderator loses report resolution when the permission is revoked', function () {
    UserPermissionOverride::create([
        'user_id' => $this->moderator->id,
        'permission_id' => Permission::query()->where('key', 'reports.handle')->value('id'),
        'granted' => false,
    ]);

    $this->actingAs($this->moderator->fresh())
        ->put(route('reports.update', $this->report), ['status' => 'resolved'])
        ->assertForbidden();

    expect($this->report->fresh()->status)->toBe('pending');
});

test('admins keep access to the moderation decisions', function () {
    $admin = User::create([
        'name' => 'Moderation Admin',
        'email' => 'admin.moderation@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post(route('moderation.listings.decision', $this->listing), ['decision' => 'approve'])
        ->assertRedirect();

    $this->actingAs($admin)
        ->put(route('reports.update', $this->report), ['status' => 'dismissed'])
        ->assertRedirect();

    expect($this->listing->fresh()->status)->toBe('approved');
    expect($this->report->fresh()->status)->toBe('dismissed');
});
