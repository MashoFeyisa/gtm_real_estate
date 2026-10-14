<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\GuestServiceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyReview;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HostDashboardController extends Controller
{
    /**
     * Display the Host Dashboard workspace with unified shell.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            abort(403, 'Admin boundary: Administrators cannot open individual host dashboards.');
        }

        if ($user?->status === 'suspended') {
            abort(403, 'Account suspended.');
        }

        $hasHostPerm = $user?->hasAnyPermission([
            'stays.listing_view',
            'stays.view',
            'stays.listing_create',
            'stays.listing_update',
            'stays.pricing_manage',
            'stays.availability_manage',
            'bookings.view',
            'cleaning.view',
        ]);

        if (! $user?->isHost() && ! $hasHostPerm) {
            abort(403, 'Permission denied: Short-stay host access required.');
        }

        // Strict Host Isolation: Only properties owned or hosted by this user
        $properties = Property::with(['images', 'bookings', 'guestServiceRequests'])
            ->where(function ($q) use ($user) {
                $q->where('host_id', $user->id)
                    ->orWhere('user_id', $user->id);
            })
            ->where(function ($q) {
                $q->whereIn('type', ['stay', 'short_stay', 'daily'])
                    ->orWhereIn('property_category', ['guest_house', 'hotel', 'guesthouse', 'apartment', 'villa', 'studio']);
            })
            ->latest()
            ->get();

        $propertyIds = $properties->pluck('id')->all();

        $bookings = Booking::with('property')
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get();

        $upcomingBookings = $bookings->filter(fn ($b) => in_array($b->status, ['confirmed', 'checked_in', 'pending', 'hold'], true));
        $pendingRequests = $bookings->where('status', 'pending');

        $cleaningTasks = GuestServiceRequest::with(['property', 'booking', 'handler'])
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get();

        $totalRevenue = (float) $bookings->whereNotIn('status', ['cancelled', 'rejected'])->sum('total_amount');
        $totalNights = (int) $bookings->whereNotIn('status', ['cancelled', 'rejected'])->sum('total_nights');
        $occupancyRate = min(100, round(($totalNights / max(1, count($properties) * 30)) * 100, 1));

        $payouts = Payment::whereIn('property_id', $propertyIds)
            ->orWhere('user_id', $user->id)
            ->latest()
            ->take(15)
            ->get();

        $reviews = PropertyReview::with('property')
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get();

        return view('dashboards.host', compact(
            'properties',
            'bookings',
            'upcomingBookings',
            'pendingRequests',
            'cleaningTasks',
            'totalRevenue',
            'occupancyRate',
            'payouts',
            'reviews'
        ));
    }

    /**
     * Create a new short-stay accommodation listing.
     */
    public function storeStayListing(Request $request): RedirectResponse
    {
        $this->authorize('create', Property::class);

        $user = $request->user();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:1'],
            'weekend_price' => ['nullable', 'numeric', 'min:1'],
            'seasonal_price' => ['nullable', 'numeric', 'min:1'],
            'minimum_stay' => ['nullable', 'integer', 'min:1'],
            'cleaning_fee' => ['nullable', 'numeric', 'min:0'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
            'house_rules' => ['nullable', 'string'],
            'house_manual' => ['nullable', 'string'],
            'check_in_instructions' => ['nullable', 'string'],
            'check_in_time' => ['nullable', 'string'],
            'check_out_time' => ['nullable', 'string'],
            'instant_booking_enabled' => ['nullable', 'boolean'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'city' => ['nullable', 'string'],
            'subcity' => ['nullable', 'string'],
            'neighborhood' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:draft,published'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ]);

        $status = $validated['status'] ?? 'published';
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('properties', 'public');
        }

        $property = Property::create([
            'user_id' => $user->id,
            'host_id' => $user->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(5)),
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'price' => $validated['price'],
            'weekend_price' => $validated['weekend_price'] ?? null,
            'seasonal_price' => $validated['seasonal_price'] ?? null,
            'minimum_stay' => $validated['minimum_stay'] ?? 1,
            'cleaning_fee' => $validated['cleaning_fee'] ?? 0,
            'service_fee' => $validated['service_fee'] ?? 0,
            'house_rules' => $validated['house_rules'] ?? null,
            'house_manual' => $validated['house_manual'] ?? null,
            'check_in_instructions' => $validated['check_in_instructions'] ?? null,
            'check_in_time' => $validated['check_in_time'] ?? '14:00',
            'check_out_time' => $validated['check_out_time'] ?? '11:00',
            'instant_booking_enabled' => (bool) $request->input('instant_booking_enabled', true),
            'bedrooms' => $validated['bedrooms'] ?? 1,
            'bathrooms' => $validated['bathrooms'] ?? 1,
            'type' => 'stay',
            'property_category' => 'guest_house',
            'status' => $status,
            'city' => $validated['city'] ?? 'Addis Ababa',
            'subcity' => $validated['subcity'] ?? 'Bole',
            'neighborhood' => $validated['neighborhood'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => $status === 'published',
            'is_available' => true,
        ]);

        if ($imagePath) {
            $property->images()->create([
                'image_path' => $imagePath,
                'sort_order' => 1,
            ]);
        }

        AuditLog::log(
            action: 'host.stay_created',
            entity: $property,
            reason: "Host {$user->name} created short-stay accommodation \"{$property->title}\".",
            user: $user,
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', 'Short-stay accommodation "'.$property->title.'" registered successfully.');
    }

    /**
     * Update an existing stay listing.
     */
    public function updateStayListing(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeHostProperty($request->user(), $property);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:1'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'city' => ['nullable', 'string'],
            'subcity' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:draft,published,paused'],
        ]);

        $status = $validated['status'] ?? $property->status;

        $property->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? $property->description,
            'price' => $validated['price'],
            'bedrooms' => $validated['bedrooms'] ?? $property->bedrooms,
            'bathrooms' => $validated['bathrooms'] ?? $property->bathrooms,
            'city' => $validated['city'] ?? $property->city,
            'subcity' => $validated['subcity'] ?? $property->subcity,
            'address' => $validated['address'] ?? $property->address,
            'status' => $status,
            'is_active' => $status === 'published',
        ]);

        AuditLog::log(
            action: 'host.stay_updated',
            entity: $property,
            reason: "Host updated accommodation details for \"{$property->title}\".",
            user: $request->user(),
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', 'Accommodation updated successfully.');
    }

    /**
     * Delete a stay listing.
     */
    public function destroyStayListing(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeHostProperty($request->user(), $property);

        $title = $property->title;

        // Clean up media
        foreach ($property->images as $img) {
            if ($img->image_path && Storage::disk('public')->exists($img->image_path)) {
                Storage::disk('public')->delete($img->image_path);
            }
            $img->delete();
        }

        $property->delete();

        AuditLog::log(
            action: 'host.stay_deleted',
            reason: "Host deleted accommodation \"{$title}\".",
            user: $request->user(),
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', 'Accommodation "'.$title.'" removed.');
    }

    /**
     * Toggle published / active status.
     */
    public function togglePublishStay(Property $property): RedirectResponse
    {
        $this->authorizeHostProperty(auth()->user(), $property);

        $newActive = ! $property->is_active;
        $property->is_active = $newActive;
        $property->status = $newActive ? 'published' : 'draft';
        $property->save();

        foreach ($property->listings as $listing) {
            $listing->update(['status' => $newActive ? 'published' : 'draft']);
        }

        AuditLog::log(
            action: 'host.stay_toggle_publish',
            entity: $property,
            reason: 'Stay accommodation status changed to '.($newActive ? 'Published' : 'Draft'),
            user: auth()->user()
        );

        return redirect()->route('host.dashboard')->with('success', 'Stay status updated to '.($newActive ? 'Published' : 'Draft').'.');
    }

    /**
     * Update pricing rules (weekend pricing, seasonal pricing, minimum stay, fees).
     */
    public function updatePricing(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeHostProperty($request->user(), $property);

        $validated = $request->validate([
            'price' => ['required', 'numeric', 'min:1'],
            'weekend_price' => ['nullable', 'numeric', 'min:1'],
            'seasonal_price' => ['nullable', 'numeric', 'min:1'],
            'minimum_stay' => ['required', 'integer', 'min:1', 'max:365'],
            'cleaning_fee' => ['nullable', 'numeric', 'min:0'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
        ]);

        $property->update([
            'price' => $validated['price'],
            'weekend_price' => $validated['weekend_price'] ?? null,
            'seasonal_price' => $validated['seasonal_price'] ?? null,
            'minimum_stay' => (int) $validated['minimum_stay'],
            'cleaning_fee' => (float) ($validated['cleaning_fee'] ?? 0),
            'service_fee' => (float) ($validated['service_fee'] ?? 0),
        ]);

        foreach ($property->listings as $listing) {
            $listing->update([
                'price' => $validated['price'],
                'cleaning_fee' => (float) ($validated['cleaning_fee'] ?? 0),
                'service_fee' => (float) ($validated['service_fee'] ?? 0),
                'minimum_stay' => (int) $validated['minimum_stay'],
            ]);
        }

        AuditLog::log(
            action: 'host.pricing_updated',
            entity: $property,
            reason: "Host updated pricing rules for \"{$property->title}\". Nightly: ETB {$validated['price']}, Min stay: {$validated['minimum_stay']} nights.",
            user: $request->user(),
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', 'Pricing rules and minimum stay updated.');
    }

    /**
     * Update house rules, house manual, and check-in instructions.
     */
    public function updateRulesAndManual(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeHostProperty($request->user(), $property);

        $validated = $request->validate([
            'house_rules' => ['nullable', 'string', 'max:2000'],
            'house_manual' => ['nullable', 'string', 'max:3000'],
            'check_in_instructions' => ['nullable', 'string', 'max:2000'],
            'check_in_time' => ['nullable', 'string', 'max:20'],
            'check_out_time' => ['nullable', 'string', 'max:20'],
            'instant_booking_enabled' => ['nullable', 'boolean'],
        ]);

        $property->update([
            'house_rules' => $validated['house_rules'] ?? null,
            'house_manual' => $validated['house_manual'] ?? null,
            'check_in_instructions' => $validated['check_in_instructions'] ?? null,
            'check_in_time' => $validated['check_in_time'] ?? '14:00',
            'check_out_time' => $validated['check_out_time'] ?? '11:00',
            'instant_booking_enabled' => (bool) $request->input('instant_booking_enabled', false),
        ]);

        AuditLog::log(
            action: 'host.rules_manual_updated',
            entity: $property,
            reason: "Host updated house rules and manual for \"{$property->title}\".",
            user: $request->user(),
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', 'House rules, manual, and check-in instructions saved.');
    }

    /**
     * Calendar: Block dates for maintenance, personal use, or blackout.
     */
    public function blockCalendarDates(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeHostProperty($request->user(), $property);

        $validated = $request->validate([
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $checkIn = Carbon::parse($validated['check_in_date'])->startOfDay();
        $checkOut = Carbon::parse($validated['check_out_date'])->startOfDay();
        $nights = max(1, $checkIn->diffInDays($checkOut));

        // Concurrency check
        $hasOverlap = Booking::query()
            ->where('property_id', $property->id)
            ->overlapping($checkIn->toDateString(), $checkOut->toDateString())
            ->exists();

        if ($hasOverlap) {
            return back()->withErrors(['check_in_date' => 'Selected dates overlap with an existing active booking or block.']);
        }

        $blockBooking = Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'booking_reference' => 'BLK-'.strtoupper(Str::random(6)),
            'guest_name' => 'Host Block: '.($validated['reason'] ?: 'Maintenance / Owner Stay'),
            'guest_email' => $user->email,
            'guest_phone' => $user->phone ?? '+251900000000',
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'total_nights' => $nights,
            'guests_count' => 1,
            'nightly_price' => 0,
            'accommodation_total' => 0,
            'cleaning_fee' => 0,
            'service_fee' => 0,
            'total_amount' => 0,
            'currency' => 'ETB',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'special_requests' => $validated['reason'] ?? 'Host blocked dates',
        ]);

        AuditLog::log(
            action: 'host.calendar_dates_blocked',
            entity: $blockBooking,
            reason: "Host blocked calendar from {$checkIn->format('M d, Y')} to {$checkOut->format('M d, Y')}: ".($validated['reason'] ?: 'Maintenance'),
            user: $user,
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', "Dates from {$checkIn->format('M d')} to {$checkOut->format('M d')} blocked on calendar.");
    }

    /**
     * Calendar: Unblock dates (cancel blocked booking).
     */
    public function unblockCalendarDates(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeHostProperty($request->user(), $booking->property);

        if (! str_starts_with($booking->booking_reference, 'BLK-') && ! str_starts_with($booking->booking_reference, 'EXT-')) {
            abort(403, 'Only host blocks and external reservations can be unblocked directly.');
        }

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => 'Unblocked by host',
        ]);

        AuditLog::log(
            action: 'host.calendar_dates_unblocked',
            entity: $booking,
            reason: "Host unblocked dates for {$booking->booking_reference}.",
            user: $request->user(),
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', 'Calendar dates unblocked and reopened for guest bookings.');
    }

    /**
     * Calendar: Record external reservation (Airbnb, Booking.com, Vrbo, Walk-in).
     */
    public function recordExternalReservation(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeHostProperty($request->user(), $property);

        $validated = $request->validate([
            'guest_name' => ['required', 'string', 'max:255'],
            'platform' => ['required', 'string', 'in:airbnb,booking_com,vrbo,walk_in,direct'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'payout_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $checkIn = Carbon::parse($validated['check_in_date'])->startOfDay();
        $checkOut = Carbon::parse($validated['check_out_date'])->startOfDay();
        $nights = max(1, $checkIn->diffInDays($checkOut));

        $hasOverlap = Booking::query()
            ->where('property_id', $property->id)
            ->overlapping($checkIn->toDateString(), $checkOut->toDateString())
            ->exists();

        if ($hasOverlap) {
            return back()->withErrors(['check_in_date' => 'Selected dates overlap with an existing booking.']);
        }

        $platformName = ucfirst(str_replace('_', '.', $validated['platform']));
        $extBooking = Booking::create([
            'property_id' => $property->id,
            'booking_reference' => 'EXT-'.strtoupper(Str::random(6)),
            'guest_name' => $validated['guest_name'].' ('.$platformName.')',
            'guest_email' => 'external.'.Str::slug($validated['platform']).'@gtmrealestate.com',
            'guest_phone' => '+251900000000',
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'total_nights' => $nights,
            'guests_count' => 1,
            'nightly_price' => $nights > 0 ? (float) ($validated['payout_amount'] ?? 0) / $nights : 0,
            'accommodation_total' => (float) ($validated['payout_amount'] ?? 0),
            'cleaning_fee' => 0,
            'service_fee' => 0,
            'total_amount' => (float) ($validated['payout_amount'] ?? 0),
            'currency' => 'ETB',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'special_requests' => "External booking imported from {$platformName}",
        ]);

        AuditLog::log(
            action: 'host.external_reservation_created',
            entity: $extBooking,
            reason: "Recorded external reservation from {$platformName} for {$validated['guest_name']}.",
            user: $request->user(),
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', "External reservation from {$platformName} recorded successfully.");
    }

    /**
     * Accept a pending guest booking request.
     */
    public function acceptBooking(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeHostProperty($request->user(), $booking->property);

        if ($booking->status !== 'pending') {
            return back()->with('info', 'Booking is not in pending state.');
        }

        $booking->update(['status' => 'confirmed']);

        AuditLog::log(
            action: 'host.booking_accepted',
            entity: $booking,
            reason: "Host accepted booking request {$booking->booking_reference} for guest {$booking->guest_name}.",
            user: $request->user(),
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', "Booking {$booking->booking_reference} confirmed.");
    }

    /**
     * Reject a pending guest booking request.
     */
    public function rejectBooking(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeHostProperty($request->user(), $booking->property);

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['rejection_reason'] ?? 'Host declined booking request',
        ]);

        AuditLog::log(
            action: 'host.booking_rejected',
            entity: $booking,
            reason: "Host declined booking {$booking->booking_reference}: ".($validated['rejection_reason'] ?? 'Declined by host'),
            user: $request->user(),
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', "Booking {$booking->booking_reference} declined.");
    }

    /**
     * Mark guest as checked-in.
     */
    public function checkInGuest(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeHostProperty($request->user(), $booking->property);

        $booking->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        AuditLog::log(
            action: 'host.guest_checked_in',
            entity: $booking,
            reason: "Guest {$booking->guest_name} checked in for booking {$booking->booking_reference}.",
            user: $request->user(),
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', "Guest {$booking->guest_name} marked as checked in.");
    }

    /**
     * Mark guest as checked-out and auto-schedule turnover cleaning.
     */
    public function checkOutGuest(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeHostProperty($request->user(), $booking->property);

        $booking->update([
            'status' => 'completed',
            'checked_out_at' => now(),
        ]);

        // Automatically dispatch turnover cleaning task
        GuestServiceRequest::create([
            'property_id' => $booking->property_id,
            'booking_id' => $booking->id,
            'room_number' => $booking->room_number ?? 'Main Unit',
            'guest_name' => $booking->guest_name,
            'service_type' => 'turnover_cleaning',
            'priority' => 'urgent',
            'description' => "Post check-out turnover cleaning for booking {$booking->booking_reference}. Linen change, surface sanitize, restock toiletries.",
            'status' => 'pending',
        ]);

        AuditLog::log(
            action: 'host.guest_checked_out',
            entity: $booking,
            reason: "Guest {$booking->guest_name} checked out. Turnover cleaning task dispatched.",
            user: $request->user(),
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', "Guest {$booking->guest_name} checked out. Turnover cleaning dispatched.");
    }

    /**
     * Create a cleaning or housekeeping dispatch task.
     */
    public function storeCleaningTask(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeHostProperty($request->user(), $property);

        $validated = $request->validate([
            'service_type' => ['required', 'string', 'in:room_cleaning,turnover_cleaning,deep_cleaning,extra_towels,maintenance'],
            'priority' => ['required', 'string', 'in:low,normal,urgent'],
            'room_number' => ['nullable', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        $task = GuestServiceRequest::create([
            'property_id' => $property->id,
            'booking_id' => null,
            'room_number' => $validated['room_number'] ?? 'Standard Unit',
            'guest_name' => 'Host Scheduled Housekeeping',
            'service_type' => $validated['service_type'],
            'priority' => $validated['priority'],
            'description' => $validated['description'],
            'status' => 'pending',
        ]);

        AuditLog::log(
            action: 'host.cleaning_task_created',
            entity: $task,
            reason: "Host created {$validated['service_type']} task for {$property->title}.",
            user: $request->user(),
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', 'Cleaning task dispatched successfully.');
    }

    /**
     * Mark a cleaning task as completed / inspected.
     */
    public function completeCleaningTask(Request $request, GuestServiceRequest $cleaningTask): RedirectResponse
    {
        $this->authorizeHostProperty($request->user(), $cleaningTask->property);

        $validated = $request->validate([
            'response_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cleaningTask->update([
            'status' => 'completed',
            'handled_by' => $request->user()->id,
            'response_notes' => $validated['response_notes'] ?? 'Room inspected and verified sanitized for next guest.',
        ]);

        AuditLog::log(
            action: 'host.cleaning_task_completed',
            entity: $cleaningTask,
            reason: "Host completed cleaning task #{$cleaningTask->id}.",
            user: $request->user(),
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', 'Cleaning task marked as completed and room verified ready.');
    }

    /**
     * Post a host reply to a guest review.
     */
    public function replyReview(Request $request, PropertyReview $review): RedirectResponse
    {
        $this->authorizeHostProperty($request->user(), $review->property);

        $validated = $request->validate([
            'host_reply' => ['required', 'string', 'max:1000'],
        ]);

        $review->update([
            'host_reply' => $validated['host_reply'],
            'host_replied_at' => now(),
        ]);

        AuditLog::log(
            action: 'host.review_replied',
            entity: $review,
            reason: "Host replied to review by {$review->user?->name}.",
            user: $request->user(),
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', 'Host reply posted successfully.');
    }

    /**
     * Verify that the current user owns or hosts this property.
     */
    protected function authorizeHostProperty(User $user, ?Property $property): void
    {
        if (! $property || ((int) $property->host_id !== (int) $user->id && (int) $property->user_id !== (int) $user->id)) {
            abort(403, 'Unauthorized: You do not have host rights to this accommodation.');
        }
    }
}
