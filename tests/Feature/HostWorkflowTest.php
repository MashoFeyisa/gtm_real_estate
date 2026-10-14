<?php

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\GuestServiceRequest;
use App\Models\Property;
use App\Models\PropertyReview;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('public');
});

// ── 1. HOST ACCESS & BOUNDARY CONTROLS ────────────────────────────────────────

test('authenticated host can access host dashboard with unified shell', function () {
    $host = User::factory()->create([
        'name' => 'Sara Host',
        'email' => 'sara.host@example.com',
        'role' => 'host',
    ]);

    $response = $this->actingAs($host)->get(route('host.dashboard'));

    $response->assertOk()
        ->assertSee('Sara Host')
        ->assertSee('Short-Stay Host Desk')
        ->assertSee('Overview')
        ->assertSee('Accommodations')
        ->assertSee('Pricing Rules')
        ->assertSee('Cleaning &amp; Housekeeping', false)
        ->assertSee('Reviews &amp; Payouts', false);
});

test('admin cannot open individual host dashboard due to boundary enforcement', function () {
    $admin = User::factory()->create([
        'name' => 'Platform Admin',
        'email' => 'admin@example.com',
        'role' => 'admin',
    ]);

    $this->actingAs($admin)->get(route('host.dashboard'))
        ->assertForbidden();
});

test('suspended host cannot access host dashboard', function () {
    $host = User::factory()->create([
        'role' => 'host',
        'status' => 'suspended',
    ]);

    $this->actingAs($host)->get(route('host.dashboard'))
        ->assertForbidden();
});

// ── 2. STAY LISTINGS CRUD & LIFECYCLE ─────────────────────────────────────────

test('host can register short-stay accommodation with pricing rules and manual', function () {
    $host = User::factory()->create(['role' => 'host']);

    $response = $this->actingAs($host)->post(route('host.stays.store'), [
        'title' => 'Bole Medhanealem Modern 2BR Serviced Suite',
        'description' => 'Fully furnished high-floor serviced apartment with backup generator and fast fiber internet.',
        'price' => 4500,
        'weekend_price' => 5200,
        'seasonal_price' => 6000,
        'minimum_stay' => 2,
        'cleaning_fee' => 500,
        'service_fee' => 250,
        'house_rules' => 'No smoking inside. Quiet hours after 22:00.',
        'house_manual' => 'WiFi Network: BoleSuite_5G, Password: EthiopianCoffee2026. Water heater switch in hallway.',
        'check_in_instructions' => 'Keycard lockbox on door #402. Code: 7890.',
        'check_in_time' => '14:00',
        'check_out_time' => '11:00',
        'instant_booking_enabled' => '1',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'address' => 'Near Edna Mall, Cameroon St.',
        'status' => 'published',
        'image' => UploadedFile::fake()->image('suite_cover.jpg', 800, 600),
    ]);

    $response->assertRedirect(route('host.dashboard'));

    $property = Property::where('host_id', $host->id)->where('title', 'Bole Medhanealem Modern 2BR Serviced Suite')->first();
    expect($property)->not->toBeNull()
        ->and((float) $property->price)->toBe(4500.0)
        ->and((float) $property->weekend_price)->toBe(5200.0)
        ->and($property->minimum_stay)->toBe(2)
        ->and($property->instant_booking_enabled)->toBeTrue()
        ->and($property->type)->toBe('stay')
        ->and($property->is_active)->toBeTrue()
        ->and(Storage::disk('public')->exists($property->image_path))->toBeTrue();

    $log = AuditLog::where('user_id', $host->id)->where('action', 'host.stay_created')->first();
    expect($log)->not->toBeNull();
});

test('host can update their own stay listing', function () {
    $host = User::factory()->create(['role' => 'host']);
    $property = Property::factory()->create([
        'host_id' => $host->id,
        'user_id' => $host->id,
        'type' => 'stay',
        'price' => 3000,
        'title' => 'Original Suite',
    ]);

    $response = $this->actingAs($host)->put(route('host.stays.update', $property), [
        'title' => 'Renovated Bole Suite',
        'description' => 'Upgraded interior design and king size bed.',
        'price' => 3800,
        'bedrooms' => 1,
        'bathrooms' => 1,
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'address' => 'Atlas Road',
        'status' => 'published',
    ]);

    $response->assertRedirect(route('host.dashboard'));
    expect($property->fresh()->title)->toBe('Renovated Bole Suite')
        ->and((float) $property->fresh()->price)->toBe(3800.0);
});

test('host cannot update another hosts stay listing', function () {
    $host1 = User::factory()->create(['role' => 'host']);
    $host2 = User::factory()->create(['role' => 'host']);
    $property = Property::factory()->create(['host_id' => $host1->id, 'type' => 'stay']);

    $this->actingAs($host2)->put(route('host.stays.update', $property), [
        'title' => 'Hijacked Suite',
        'price' => 100,
    ])->assertForbidden();
});

test('host can delete their own stay listing', function () {
    $host = User::factory()->create(['role' => 'host']);
    $property = Property::factory()->create(['host_id' => $host->id, 'user_id' => $host->id, 'type' => 'stay']);

    $response = $this->actingAs($host)->delete(route('host.stays.destroy', $property));
    $response->assertRedirect(route('host.dashboard'));

    expect(Property::find($property->id))->toBeNull();
    $log = AuditLog::where('user_id', $host->id)->where('action', 'host.stay_deleted')->first();
    expect($log)->not->toBeNull();
});

test('host cannot delete another hosts stay listing', function () {
    $host1 = User::factory()->create(['role' => 'host']);
    $host2 = User::factory()->create(['role' => 'host']);
    $property = Property::factory()->create(['host_id' => $host1->id, 'type' => 'stay']);

    $this->actingAs($host2)->delete(route('host.stays.destroy', $property))
        ->assertForbidden();
});

test('host can toggle publish status and change pricing rules', function () {
    $host = User::factory()->create(['role' => 'host']);
    $property = Property::factory()->create([
        'host_id' => $host->id,
        'user_id' => $host->id,
        'type' => 'stay',
        'is_active' => true,
        'status' => 'published',
    ]);

    // 1. Toggle publish
    $this->actingAs($host)->post(route('host.stays.publish', $property))
        ->assertRedirect(route('host.dashboard'));
    expect($property->fresh()->is_active)->toBeFalse()
        ->and($property->fresh()->status)->toBe('draft');

    // 2. Update pricing rules
    $pricingResponse = $this->actingAs($host)->put(route('host.pricing.update', $property), [
        'price' => 5000,
        'weekend_price' => 5800,
        'seasonal_price' => 6500,
        'minimum_stay' => 3,
        'cleaning_fee' => 600,
        'service_fee' => 300,
    ]);
    $pricingResponse->assertRedirect(route('host.dashboard'));

    $fresh = $property->fresh();
    expect((float) $fresh->price)->toBe(5000.0)
        ->and((float) $fresh->weekend_price)->toBe(5800.0)
        ->and((float) $fresh->seasonal_price)->toBe(6500.0)
        ->and($fresh->minimum_stay)->toBe(3)
        ->and((float) $fresh->cleaning_fee)->toBe(600.0);

    // 3. Update house rules and manual
    $rulesResponse = $this->actingAs($host)->put(route('host.rules.update', $property), [
        'house_rules' => 'No pets allowed.',
        'house_manual' => 'Boiler is activated automatically.',
        'check_in_instructions' => 'Door keypad code 1234.',
        'check_in_time' => '15:00',
        'check_out_time' => '10:00',
        'instant_booking_enabled' => '1',
    ]);
    $rulesResponse->assertRedirect(route('host.dashboard'));

    $freshRules = $property->fresh();
    expect($freshRules->house_rules)->toBe('No pets allowed.')
        ->and($freshRules->check_in_time)->toBe('15:00')
        ->and($freshRules->instant_booking_enabled)->toBeTrue();
});

// ── 3. CALENDAR & AVAILABILITY BLOCKING ───────────────────────────────────────

test('host can block dates on calendar and unblock', function () {
    $host = User::factory()->create(['role' => 'host']);
    $property = Property::factory()->create(['host_id' => $host->id, 'type' => 'stay']);

    // 1. Block dates
    $checkIn = now()->addDays(5)->toDateString();
    $checkOut = now()->addDays(8)->toDateString();

    $blockResponse = $this->actingAs($host)->post(route('host.calendar.block', $property), [
        'check_in_date' => $checkIn,
        'check_out_date' => $checkOut,
        'reason' => 'Annual painting and AC maintenance',
    ]);
    $blockResponse->assertRedirect(route('host.dashboard'));

    $block = Booking::where('property_id', $property->id)->where('status', 'confirmed')->first();
    expect($block)->not->toBeNull()
        ->and(str_starts_with($block->booking_reference, 'BLK-'))->toBeTrue()
        ->and($block->total_nights)->toBe(3);

    // 2. Unblock dates
    $unblockResponse = $this->actingAs($host)->delete(route('host.calendar.unblock', $block));
    $unblockResponse->assertRedirect(route('host.dashboard'));

    expect($block->fresh()->status)->toBe('cancelled')
        ->and($block->fresh()->cancellation_reason)->toBe('Unblocked by host');
});

test('host can record external reservation from Airbnb or Booking.com', function () {
    $host = User::factory()->create(['role' => 'host']);
    $property = Property::factory()->create(['host_id' => $host->id, 'type' => 'stay']);

    $response = $this->actingAs($host)->post(route('host.calendar.external', $property), [
        'guest_name' => 'Marcus Vance',
        'platform' => 'airbnb',
        'check_in_date' => now()->addDays(10)->toDateString(),
        'check_out_date' => now()->addDays(14)->toDateString(),
        'payout_amount' => 16000,
    ]);
    $response->assertRedirect(route('host.dashboard'));

    $extBooking = Booking::where('property_id', $property->id)->where('status', 'confirmed')->first();
    expect($extBooking)->not->toBeNull()
        ->and(str_starts_with($extBooking->booking_reference, 'EXT-'))->toBeTrue()
        ->and($extBooking->guest_name)->toContain('Marcus Vance (Airbnb)')
        ->and((float) $extBooking->total_amount)->toBe(16000.0);
});

// ── 4. BOOKINGS TRIAGE & FRONT-DESK CHECK-IN ──────────────────────────────────

test('host can accept, decline, check in, and check out guest with turnover cleaning dispatch', function () {
    $host = User::factory()->create(['role' => 'host']);
    $guest = User::factory()->create(['role' => 'user']);
    $property = Property::factory()->create(['host_id' => $host->id, 'type' => 'stay']);

    // 1. Pending booking request
    $booking = Booking::create([
        'property_id' => $property->id,
        'user_id' => $guest->id,
        'booking_reference' => 'BK-'.strtoupper(fake()->unique()->bothify('##??')),
        'guest_name' => 'Dawit Guest',
        'guest_email' => 'dawit@example.com',
        'guest_phone' => '+251911334455',
        'check_in_date' => now()->toDateString(),
        'check_out_date' => now()->addDays(2)->toDateString(),
        'total_nights' => 2,
        'guests_count' => 2,
        'nightly_price' => 3500,
        'accommodation_total' => 7000,
        'cleaning_fee' => 0,
        'service_fee' => 0,
        'total_amount' => 7000,
        'currency' => 'ETB',
        'status' => 'pending',
    ]);

    // 2. Accept booking
    $this->actingAs($host)->post(route('host.bookings.accept', $booking))
        ->assertRedirect(route('host.dashboard'));
    expect($booking->fresh()->status)->toBe('confirmed');

    // 3. Check in guest
    $this->actingAs($host)->post(route('host.bookings.check-in', $booking))
        ->assertRedirect(route('host.dashboard'));
    expect($booking->fresh()->status)->toBe('checked_in')
        ->and($booking->fresh()->checked_in_at)->not->toBeNull();

    // 4. Check out guest -> triggers turnover cleaning task
    $this->actingAs($host)->post(route('host.bookings.check-out', $booking))
        ->assertRedirect(route('host.dashboard'));
    expect($booking->fresh()->status)->toBe('completed')
        ->and($booking->fresh()->checked_out_at)->not->toBeNull();

    $cleaningTask = GuestServiceRequest::where('booking_id', $booking->id)->first();
    expect($cleaningTask)->not->toBeNull()
        ->and($cleaningTask->service_type)->toBe('turnover_cleaning')
        ->and($cleaningTask->priority)->toBe('urgent')
        ->and($cleaningTask->status)->toBe('pending');
});

// ── 5. CLEANING & HOUSEKEEPING OPERATIONS ────────────────────────────────────

test('host can schedule cleaning task and complete inspection', function () {
    $host = User::factory()->create(['role' => 'host']);
    $property = Property::factory()->create(['host_id' => $host->id, 'type' => 'stay']);

    // 1. Dispatch cleaning task
    $response = $this->actingAs($host)->post(route('host.cleaning.store', $property), [
        'service_type' => 'deep_cleaning',
        'priority' => 'urgent',
        'room_number' => 'Penthouse Suite 801',
        'description' => 'Shampoo carpet, sanitize kitchen surfaces, replace terrace patio cushions.',
    ]);
    $response->assertRedirect(route('host.dashboard'));

    $task = GuestServiceRequest::where('property_id', $property->id)->first();
    expect($task)->not->toBeNull()
        ->and($task->service_type)->toBe('deep_cleaning')
        ->and($task->room_number)->toBe('Penthouse Suite 801')
        ->and($task->status)->toBe('pending');

    // 2. Mark complete and inspected
    $completeResponse = $this->actingAs($host)->put(route('host.cleaning.complete', $task), [
        'response_notes' => 'Room thoroughly cleaned and sanitized. Verified ready for arrival.',
    ]);
    $completeResponse->assertRedirect(route('host.dashboard'));

    expect($task->fresh()->status)->toBe('completed')
        ->and($task->fresh()->handled_by)->toBe($host->id)
        ->and($task->fresh()->response_notes)->toContain('Verified ready for arrival');
});

// ── 6. REVIEWS REPLIES & ISOLATION ───────────────────────────────────────────

test('host can reply to guest review on their accommodation', function () {
    $host = User::factory()->create(['role' => 'host']);
    $guest = User::factory()->create(['role' => 'user']);
    $property = Property::factory()->create(['host_id' => $host->id, 'type' => 'stay']);

    $review = PropertyReview::create([
        'property_id' => $property->id,
        'user_id' => $guest->id,
        'rating' => 5,
        'review_title' => 'Fantastic stay in Bole!',
        'review_body' => 'Very clean, comfortable bed, and the host was super welcoming.',
        'status' => 'approved',
    ]);

    $response = $this->actingAs($host)->post(route('host.reviews.reply', $review), [
        'host_reply' => 'Thank you for staying with us Dawit! You are welcome back anytime.',
    ]);
    $response->assertRedirect(route('host.dashboard'));

    expect($review->fresh()->host_reply)->toBe('Thank you for staying with us Dawit! You are welcome back anytime.')
        ->and($review->fresh()->host_replied_at)->not->toBeNull();
});

test('host cannot reply to review or manage stay of another host', function () {
    $host1 = User::factory()->create(['role' => 'host']);
    $host2 = User::factory()->create(['role' => 'host']);
    $property = Property::factory()->create(['host_id' => $host1->id, 'type' => 'stay']);

    $review = PropertyReview::create([
        'property_id' => $property->id,
        'user_id' => $host1->id,
        'rating' => 4,
        'review_title' => 'Nice place',
        'review_body' => 'Good experience',
        'status' => 'approved',
    ]);

    $this->actingAs($host2)->post(route('host.reviews.reply', $review), [
        'host_reply' => 'Unauthorized reply',
    ])->assertForbidden();
});
