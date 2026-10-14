<?php

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Property;
use App\Models\PropertyFavorite;
use App\Models\PropertyReview;
use App\Models\RolePermission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// ── 1. DASHBOARD ACCESS & BOUNDARY CONTROLS ──────────────────────────────────

test('guest or traveler can access unified guest dashboard', function () {
    $traveler = User::factory()->create(['role' => 'traveler']);

    $response = $this->actingAs($traveler)->get(route('guest.dashboard'));

    $response->assertOk()
        ->assertSee('Guest / Traveler Workspace')
        ->assertSee('My Bookings')
        ->assertSee('Saved Stays')
        ->assertSee('My Reviews');
});

test('guest cannot access guest dashboard without permissions (403)', function () {
    $traveler = User::factory()->create(['role' => 'traveler']);

    // Revoke all guest dashboard permissions
    RolePermission::where('role', 'traveler')->delete();
    RolePermission::where('role', 'guest')->delete();
    config(['personas.personas.3.permissions' => []]);

    $response = $this->actingAs($traveler)->get(route('guest.dashboard'));
    $response->assertForbidden();
});

test('admin cannot open guest personal dashboard (403)', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('guest.dashboard'));

    $response->assertForbidden();
});

test('unauthenticated visitor is redirected to login', function () {
    $response = $this->get(route('guest.dashboard'));

    $response->assertRedirect(route('login'));
});

// ── 2. BOOKING DATES MODIFICATION ─────────────────────────────────────────────

test('guest can modify booking dates for upcoming reservation', function () {
    $guest = User::factory()->create(['role' => 'traveler']);
    $property = Property::factory()->create(['type' => 'stay', 'price' => 3500]);

    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-TEST-001',
        'property_id' => $property->id,
        'user_id' => $guest->id,
        'guest_name' => $guest->name,
        'guest_email' => $guest->email,
        'guest_phone' => '+251911000000',
        'check_in_date' => Carbon::today()->addDays(5)->format('Y-m-d'),
        'check_out_date' => Carbon::today()->addDays(8)->format('Y-m-d'),
        'total_nights' => 3,
        'guests_count' => 2,
        'nightly_price' => 3500,
        'accommodation_total' => 10500,
        'cleaning_fee' => 500,
        'service_fee' => 525,
        'total_amount' => 11525,
        'status' => 'confirmed',
        'payment_status' => 'paid',
    ]);

    $newCheckIn = Carbon::today()->addDays(10)->format('Y-m-d');
    $newCheckOut = Carbon::today()->addDays(15)->format('Y-m-d');

    $response = $this->actingAs($guest)->post(route('guest.bookings.modify', $booking), [
        'check_in_date' => $newCheckIn,
        'check_out_date' => $newCheckOut,
        'guests_count' => 3,
    ]);

    $response->assertRedirect();
    $booking->refresh();

    expect($booking->check_in_date->format('Y-m-d'))->toBe($newCheckIn)
        ->and($booking->check_out_date->format('Y-m-d'))->toBe($newCheckOut)
        ->and($booking->total_nights)->toBe(5)
        ->and($booking->guests_count)->toBe(3);

    $log = AuditLog::where('user_id', $guest->id)->where('action', 'guest.booking_modified')->first();
    expect($log)->not->toBeNull();
});

test('guest cannot modify booking dates to overlap with another reservation', function () {
    $guest = User::factory()->create(['role' => 'traveler']);
    $otherUser = User::factory()->create(['role' => 'traveler']);
    $property = Property::factory()->create(['type' => 'stay', 'price' => 3000]);

    // Booking 1 (other user)
    Booking::create([
        'booking_reference' => 'STAY-AA-TEST-OTHER',
        'property_id' => $property->id,
        'user_id' => $otherUser->id,
        'guest_name' => $otherUser->name,
        'guest_email' => $otherUser->email,
        'guest_phone' => '+251911111111',
        'check_in_date' => Carbon::today()->addDays(10)->format('Y-m-d'),
        'check_out_date' => Carbon::today()->addDays(15)->format('Y-m-d'),
        'total_nights' => 5,
        'guests_count' => 1,
        'nightly_price' => 3000,
        'accommodation_total' => 15000,
        'total_amount' => 16000,
        'status' => 'confirmed',
    ]);

    // Booking 2 (current guest)
    $guestBooking = Booking::create([
        'booking_reference' => 'STAY-AA-TEST-GUEST',
        'property_id' => $property->id,
        'user_id' => $guest->id,
        'guest_name' => $guest->name,
        'guest_email' => $guest->email,
        'guest_phone' => '+251911000000',
        'check_in_date' => Carbon::today()->addDays(20)->format('Y-m-d'),
        'check_out_date' => Carbon::today()->addDays(25)->format('Y-m-d'),
        'total_nights' => 5,
        'guests_count' => 1,
        'nightly_price' => 3000,
        'accommodation_total' => 15000,
        'total_amount' => 16000,
        'status' => 'confirmed',
    ]);

    // Try modifying guest booking to overlap with other user's booking
    $response = $this->actingAs($guest)->post(route('guest.bookings.modify', $guestBooking), [
        'check_in_date' => Carbon::today()->addDays(12)->format('Y-m-d'),
        'check_out_date' => Carbon::today()->addDays(16)->format('Y-m-d'),
        'guests_count' => 1,
    ]);

    $response->assertRedirect();
    expect(session('error'))->toContain('unavailable');
    expect($guestBooking->fresh()->check_in_date->format('Y-m-d'))->toBe(Carbon::today()->addDays(20)->format('Y-m-d'));
});

test('guest cannot modify another guests booking (403)', function () {
    $guest1 = User::factory()->create(['role' => 'traveler']);
    $guest2 = User::factory()->create(['role' => 'traveler']);
    $property = Property::factory()->create(['type' => 'stay']);

    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-TEST-002',
        'property_id' => $property->id,
        'user_id' => $guest1->id,
        'guest_name' => $guest1->name,
        'guest_email' => $guest1->email,
        'guest_phone' => '+251911000000',
        'check_in_date' => Carbon::today()->addDays(5)->format('Y-m-d'),
        'check_out_date' => Carbon::today()->addDays(8)->format('Y-m-d'),
        'total_nights' => 3,
        'guests_count' => 2,
        'nightly_price' => 2000,
        'accommodation_total' => 6000,
        'total_amount' => 6500,
        'status' => 'confirmed',
    ]);

    $response = $this->actingAs($guest2)->post(route('guest.bookings.modify', $booking), [
        'check_in_date' => Carbon::today()->addDays(6)->format('Y-m-d'),
        'check_out_date' => Carbon::today()->addDays(9)->format('Y-m-d'),
    ]);

    $response->assertForbidden();
});

// ── 3. CANCELLATION & REFUND WORKFLOW ─────────────────────────────────────────

test('guest can cancel booking with Ethiopian refund calculation', function () {
    $guest = User::factory()->create(['role' => 'traveler']);
    $property = Property::factory()->create(['type' => 'stay']);

    // Check-in in 5 days (> 48h) => 100% refund of accommodation + cleaning fee
    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-TEST-CANCEL',
        'property_id' => $property->id,
        'user_id' => $guest->id,
        'guest_name' => $guest->name,
        'guest_email' => $guest->email,
        'guest_phone' => '+251911000000',
        'check_in_date' => Carbon::today()->addDays(5)->format('Y-m-d'),
        'check_out_date' => Carbon::today()->addDays(8)->format('Y-m-d'),
        'total_nights' => 3,
        'guests_count' => 2,
        'nightly_price' => 2000,
        'accommodation_total' => 6000,
        'cleaning_fee' => 500,
        'service_fee' => 300,
        'total_amount' => 6800,
        'status' => 'confirmed',
        'payment_status' => 'paid',
    ]);

    $response = $this->actingAs($guest)->post(route('guest.bookings.cancel', $booking), [
        'cancellation_reason' => 'Emergency trip postponement',
    ]);

    $response->assertRedirect();
    $booking->refresh();

    expect($booking->status)->toBe('cancelled')
        ->and($booking->cancelled_at)->not->toBeNull()
        ->and($booking->cancellation_reason)->toBe('Emergency trip postponement')
        ->and($booking->refund_amount)->toBe(6500.0) // 6800 total - 300 service fee
        ->and($booking->refund_status)->toBe('pending_disbursement');

    $log = AuditLog::where('user_id', $guest->id)->where('action', 'guest.booking_cancelled')->first();
    expect($log)->not->toBeNull();
});

test('guest cannot cancel another guests booking (403)', function () {
    $guest1 = User::factory()->create(['role' => 'traveler']);
    $guest2 = User::factory()->create(['role' => 'traveler']);
    $property = Property::factory()->create(['type' => 'stay']);

    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-TEST-FORBIDDEN-CANCEL',
        'property_id' => $property->id,
        'user_id' => $guest1->id,
        'guest_name' => $guest1->name,
        'guest_email' => $guest1->email,
        'guest_phone' => '+251911000000',
        'check_in_date' => Carbon::today()->addDays(5)->format('Y-m-d'),
        'check_out_date' => Carbon::today()->addDays(8)->format('Y-m-d'),
        'nightly_price' => 2000,
        'accommodation_total' => 6000,
        'total_amount' => 6500,
        'status' => 'confirmed',
    ]);

    $response = $this->actingAs($guest2)->post(route('guest.bookings.cancel', $booking));
    $response->assertForbidden();
});

// ── 4. SELF CHECK-IN & CHECK-OUT ──────────────────────────────────────────────

test('guest can complete self check-in on confirmed reservation', function () {
    $guest = User::factory()->create(['role' => 'traveler']);
    $property = Property::factory()->create(['type' => 'stay']);

    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-CHECKIN-001',
        'property_id' => $property->id,
        'user_id' => $guest->id,
        'guest_name' => $guest->name,
        'guest_email' => $guest->email,
        'guest_phone' => '+251911000000',
        'check_in_date' => Carbon::today()->format('Y-m-d'),
        'check_out_date' => Carbon::today()->addDays(3)->format('Y-m-d'),
        'nightly_price' => 2500,
        'accommodation_total' => 7500,
        'total_amount' => 8000,
        'status' => 'confirmed',
        'payment_status' => 'paid',
    ]);

    $response = $this->actingAs($guest)->post(route('guest.bookings.check-in', $booking));
    $response->assertRedirect();

    expect($booking->fresh()->status)->toBe('checked_in')
        ->and($booking->fresh()->checked_in_at)->not->toBeNull();

    $log = AuditLog::where('user_id', $guest->id)->where('action', 'guest.checked_in')->first();
    expect($log)->not->toBeNull();
});

test('guest cannot check in to another guests reservation (403)', function () {
    $guest1 = User::factory()->create(['role' => 'traveler']);
    $guest2 = User::factory()->create(['role' => 'traveler']);
    $property = Property::factory()->create(['type' => 'stay']);

    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-CHECKIN-OTHER',
        'property_id' => $property->id,
        'user_id' => $guest1->id,
        'guest_name' => $guest1->name,
        'guest_email' => $guest1->email,
        'guest_phone' => '+251911000000',
        'check_in_date' => Carbon::today()->format('Y-m-d'),
        'check_out_date' => Carbon::today()->addDays(3)->format('Y-m-d'),
        'nightly_price' => 2500,
        'accommodation_total' => 7500,
        'total_amount' => 8000,
        'status' => 'confirmed',
    ]);

    $response = $this->actingAs($guest2)->post(route('guest.bookings.check-in', $booking));
    $response->assertForbidden();
});

test('guest can complete self check-out on checked_in reservation', function () {
    $guest = User::factory()->create(['role' => 'traveler']);
    $property = Property::factory()->create(['type' => 'stay']);

    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-CHECKOUT-001',
        'property_id' => $property->id,
        'user_id' => $guest->id,
        'guest_name' => $guest->name,
        'guest_email' => $guest->email,
        'guest_phone' => '+251911000000',
        'check_in_date' => Carbon::today()->subDays(2)->format('Y-m-d'),
        'check_out_date' => Carbon::today()->format('Y-m-d'),
        'nightly_price' => 2500,
        'accommodation_total' => 5000,
        'total_amount' => 5500,
        'status' => 'checked_in',
        'checked_in_at' => Carbon::now()->subDays(2),
    ]);

    $response = $this->actingAs($guest)->post(route('guest.bookings.check-out', $booking));
    $response->assertRedirect();

    expect($booking->fresh()->status)->toBe('completed')
        ->and($booking->fresh()->checked_out_at)->not->toBeNull();

    $log = AuditLog::where('user_id', $guest->id)->where('action', 'guest.checked_out')->first();
    expect($log)->not->toBeNull();
});

// ── 5. TRAVEL RECEIPTS ────────────────────────────────────────────────────────

test('guest can view printable travel receipt voucher', function () {
    $guest = User::factory()->create(['role' => 'traveler']);
    $property = Property::factory()->create(['type' => 'stay', 'title' => 'Bole Luxury Penthouse']);

    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-REC-101',
        'property_id' => $property->id,
        'user_id' => $guest->id,
        'guest_name' => $guest->name,
        'guest_email' => $guest->email,
        'guest_phone' => '+251911000000',
        'check_in_date' => Carbon::today()->addDays(1)->format('Y-m-d'),
        'check_out_date' => Carbon::today()->addDays(4)->format('Y-m-d'),
        'total_nights' => 3,
        'guests_count' => 2,
        'nightly_price' => 4000,
        'accommodation_total' => 12000,
        'cleaning_fee' => 600,
        'service_fee' => 600,
        'total_amount' => 13200,
        'status' => 'confirmed',
        'payment_status' => 'paid',
    ]);

    $response = $this->actingAs($guest)->get(route('guest.bookings.receipt', $booking));

    $response->assertOk()
        ->assertSee('Travel & Stay Receipt')
        ->assertSee('STAY-AA-REC-101')
        ->assertSee('Bole Luxury Penthouse')
        ->assertSee('13,200.00');
});

test('guest cannot view another guests travel receipt (403)', function () {
    $guest1 = User::factory()->create(['role' => 'traveler']);
    $guest2 = User::factory()->create(['role' => 'traveler']);
    $property = Property::factory()->create(['type' => 'stay']);

    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-REC-OTHER',
        'property_id' => $property->id,
        'user_id' => $guest1->id,
        'guest_name' => $guest1->name,
        'guest_email' => $guest1->email,
        'guest_phone' => '+251911000000',
        'check_in_date' => Carbon::today()->addDays(1)->format('Y-m-d'),
        'check_out_date' => Carbon::today()->addDays(4)->format('Y-m-d'),
        'nightly_price' => 4000,
        'accommodation_total' => 12000,
        'total_amount' => 13200,
        'status' => 'confirmed',
    ]);

    $response = $this->actingAs($guest2)->get(route('guest.bookings.receipt', $booking));
    $response->assertForbidden();
});

// ── 6. REVIEWS FULL CRUD ──────────────────────────────────────────────────────

test('guest can create, update, and delete stay review', function () {
    $guest = User::factory()->create(['role' => 'traveler']);
    $property = Property::factory()->create(['type' => 'stay']);

    // 1. Create Review
    $response = $this->actingAs($guest)->post(route('guest.reviews.store'), [
        'property_id' => $property->id,
        'rating' => 5,
        'review_title' => 'Unbeatable location in Bole',
        'review_body' => 'Super clean apartment with fast internet and wonderful hospitality.',
    ]);

    $response->assertRedirect();
    $review = PropertyReview::where('user_id', $guest->id)->where('property_id', $property->id)->first();
    expect($review)->not->toBeNull()
        ->and($review->rating)->toBe(5)
        ->and($review->review_title)->toBe('Unbeatable location in Bole');

    $log = AuditLog::where('user_id', $guest->id)->where('action', 'guest.review_created')->first();
    expect($log)->not->toBeNull();

    // 2. Update Review
    $updateResponse = $this->actingAs($guest)->put(route('guest.reviews.update', $review), [
        'rating' => 4,
        'review_title' => 'Great stay overall',
        'review_body' => 'Updated review body with minor feedback on water pressure.',
    ]);

    $updateResponse->assertRedirect();
    expect($review->fresh()->rating)->toBe(4)
        ->and($review->fresh()->review_title)->toBe('Great stay overall');

    $updateLog = AuditLog::where('user_id', $guest->id)->where('action', 'guest.review_updated')->first();
    expect($updateLog)->not->toBeNull();

    // 3. Delete Review
    $deleteResponse = $this->actingAs($guest)->delete(route('guest.reviews.destroy', $review));
    $deleteResponse->assertRedirect();

    expect(PropertyReview::find($review->id))->toBeNull();

    $deleteLog = AuditLog::where('user_id', $guest->id)->where('action', 'guest.review_deleted')->first();
    expect($deleteLog)->not->toBeNull();
});

test('guest cannot edit or delete another guests review (403)', function () {
    $guest1 = User::factory()->create(['role' => 'traveler']);
    $guest2 = User::factory()->create(['role' => 'traveler']);
    $property = Property::factory()->create(['type' => 'stay']);

    $review = PropertyReview::create([
        'property_id' => $property->id,
        'user_id' => $guest1->id,
        'rating' => 5,
        'review_title' => 'Guest 1 Review',
        'review_body' => 'Amazing experience hosted by an exceptional team.',
        'status' => 'published',
    ]);

    // Update attempt
    $this->actingAs($guest2)->put(route('guest.reviews.update', $review), [
        'rating' => 1,
        'review_body' => 'Malicious edit attempt by unauthorized user.',
    ])->assertForbidden();

    // Delete attempt
    $this->actingAs($guest2)->delete(route('guest.reviews.destroy', $review))
        ->assertForbidden();

    expect(PropertyReview::find($review->id))->not->toBeNull();
});

// ── 7. SAVED STAYS (FAVORITES) ────────────────────────────────────────────────

test('guest can remove saved stay from favorites', function () {
    $guest = User::factory()->create(['role' => 'traveler']);
    $property = Property::factory()->create(['type' => 'stay']);

    $favorite = PropertyFavorite::create([
        'user_id' => $guest->id,
        'property_id' => $property->id,
    ]);

    $response = $this->actingAs($guest)->delete(route('guest.saved-stays.destroy', $favorite));
    $response->assertRedirect();

    expect(PropertyFavorite::find($favorite->id))->toBeNull();
});

test('guest cannot remove another users saved stay (403)', function () {
    $guest1 = User::factory()->create(['role' => 'traveler']);
    $guest2 = User::factory()->create(['role' => 'traveler']);
    $property = Property::factory()->create(['type' => 'stay']);

    $favorite = PropertyFavorite::create([
        'user_id' => $guest1->id,
        'property_id' => $property->id,
    ]);

    $response = $this->actingAs($guest2)->delete(route('guest.saved-stays.destroy', $favorite));
    $response->assertForbidden();

    expect(PropertyFavorite::find($favorite->id))->not->toBeNull();
});
