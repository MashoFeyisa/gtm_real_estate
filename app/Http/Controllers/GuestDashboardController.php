<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyFavorite;
use App\Models\PropertyReview;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class GuestDashboardController extends Controller
{
    /**
     * Display the unified guest & short-stay traveler dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            abort(403, 'Admin boundary: Administrators cannot open individual guest dashboards.');
        }

        if ($user?->status === 'suspended') {
            abort(403, 'Account suspended.');
        }

        if (! $user?->hasAnyPermission(['bookings.view', 'stays.view', 'stays.search', 'stays.reserve', 'reviews.view'])) {
            abort(403, 'Permission denied: Guest stays access required.');
        }

        Booking::completeExpiredReservations();

        $bookings = Booking::query()
            ->with(['property.images', 'property.host', 'payments'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('guest_email', $user->email);
            })
            ->latest()
            ->get();

        $upcomingBooking = $bookings->first(fn ($b) => in_array($b->status, ['confirmed', 'checked_in', 'pending'], true));

        $reviews = PropertyReview::query()
            ->where('user_id', $user->id)
            ->with('property.images')
            ->latest()
            ->get();

        $savedStays = PropertyFavorite::query()
            ->where('user_id', $user->id)
            ->whereHas('property', fn ($q) => $q->whereIn('type', ['stay', 'short_stay', 'daily']))
            ->with('property.images')
            ->latest()
            ->get();

        $availableStays = Property::query()
            ->publicVisible()
            ->whereIn('type', ['stay', 'short_stay', 'daily'])
            ->with('images')
            ->latest()
            ->take(8)
            ->get();

        $reviewedBookingIds = $reviews->pluck('booking_id')->filter()->all();
        $completedStaysForReview = $bookings->filter(function ($b) use ($reviewedBookingIds) {
            return in_array($b->status, ['completed', 'checked_in'], true) && ! in_array($b->id, $reviewedBookingIds, true);
        });

        return view('dashboards.guest', compact(
            'bookings',
            'upcomingBooking',
            'reviews',
            'savedStays',
            'availableStays',
            'completedStaysForReview'
        ));
    }

    /**
     * Modify booking dates and guest count.
     */
    public function modifyBooking(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();

        $isOwner = (int) $booking->user_id === (int) $user->id || $booking->guest_email === $user->email;
        if (! $isOwner) {
            abort(403, 'Unauthorized booking modification.');
        }

        if (! $user->hasAnyPermission(['bookings.update', 'bookings.create', 'stays.reserve'])) {
            abort(403, 'Permission denied: Cannot modify bookings.');
        }

        if (! in_array($booking->status, ['pending', 'confirmed'], true)) {
            return back()->with('error', 'Cannot modify dates for a booking with status: '.ucfirst($booking->status));
        }

        $validated = $request->validate([
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'guests_count' => ['nullable', 'integer', 'min:1'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
        ]);

        $newCheckIn = Carbon::parse($validated['check_in_date'])->startOfDay();
        $newCheckOut = Carbon::parse($validated['check_out_date'])->startOfDay();

        // Overlapping guard excluding current booking
        $overlap = Booking::query()
            ->where('property_id', $booking->property_id)
            ->where('id', '!=', $booking->id)
            ->active()
            ->whereDate('check_in_date', '<', $newCheckOut->format('Y-m-d'))
            ->whereDate('check_out_date', '>', $newCheckIn->format('Y-m-d'))
            ->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'The requested dates are unavailable for this property. Please choose different dates.');
        }

        $nights = max(1, $newCheckIn->diffInDays($newCheckOut));
        $nightlyPrice = (float) $booking->nightly_price;
        $accommodationTotal = round($nights * $nightlyPrice, 2);
        $cleaningFee = (float) $booking->cleaning_fee;
        $serviceFee = round($accommodationTotal * 0.05, 2);
        $totalAmount = round($accommodationTotal + $cleaningFee + $serviceFee, 2);

        $booking->update([
            'check_in_date' => $newCheckIn->format('Y-m-d'),
            'check_out_date' => $newCheckOut->format('Y-m-d'),
            'total_nights' => $nights,
            'guests_count' => (int) ($validated['guests_count'] ?? $booking->guests_count),
            'accommodation_total' => $accommodationTotal,
            'service_fee' => $serviceFee,
            'total_amount' => $totalAmount,
            'special_requests' => $validated['special_requests'] ?? $booking->special_requests,
        ]);

        AuditLog::log(
            action: 'guest.booking_modified',
            entity: $booking,
            reason: "Guest modified booking {$booking->booking_reference} dates to {$newCheckIn->format('Y-m-d')} – {$newCheckOut->format('Y-m-d')}.",
            user: $user,
            request: $request,
        );

        return back()->with('success', "Booking {$booking->booking_reference} successfully updated for {$nights} night(s).");
    }

    /**
     * Cancel a booking with Ethiopian cancellation policy & refund calculation.
     */
    public function cancelBooking(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();

        $isOwner = (int) $booking->user_id === (int) $user->id || $booking->guest_email === $user->email;
        if (! $isOwner) {
            abort(403, 'Unauthorized booking cancellation.');
        }

        if (! $user->hasAnyPermission(['bookings.cancel'])) {
            abort(403, 'Permission denied: Cannot cancel bookings.');
        }

        if (! in_array($booking->status, ['pending', 'confirmed'], true)) {
            return back()->with('error', 'Cannot cancel a booking that is already '.ucfirst($booking->status));
        }

        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $refundAmount = 0.0;
        $refundStatus = 'none';

        if ($booking->isPaid()) {
            $checkIn = Carbon::parse($booking->check_in_date)->startOfDay();
            $now = Carbon::now()->startOfDay();
            $daysUntil = $now->diffInDays($checkIn, false);

            if ($daysUntil >= 2) {
                // > 48 hours: Full refund minus platform service fee
                $refundAmount = max(0.0, round((float) $booking->total_amount - (float) $booking->service_fee, 2));
                $refundStatus = 'pending_disbursement';
            } elseif ($daysUntil >= 0) {
                // Under 48 hours: 50% refund minus platform service fee
                $base = max(0.0, (float) $booking->total_amount - (float) $booking->service_fee);
                $refundAmount = round($base * 0.5, 2);
                $refundStatus = 'pending_disbursement';
            } else {
                // Same day / past check-in: Non-refundable
                $refundAmount = 0.0;
                $refundStatus = 'non_refundable';
            }
        }

        // A cancellation that owes money is a real refund: it advances the
        // booking's payment and posts a balanced journal through the one refund
        // writer (SRS §132), so an online stay cancellation leaves the same
        // trace as a finance-desk refund.
        //
        // Two fees are deliberately kept apart:
        //   * the guest-facing service fee (`booking.service_fee`) is the
        //     policy's own retention - it is simply left out of the refund
        //     amount above; and
        //   * the platform fee (`payment.platform_fee`) is the platform's cut
        //     on the money, so the accounting reverses it proportionally.
        if ($refundAmount > 0) {
            $payment = Payment::query()
                ->where('payable_type', Booking::class)
                ->where('payable_id', $booking->id)
                ->where('status', 'completed')
                ->latest('id')
                ->first();

            if ($payment) {
                app(RefundService::class)->refund(
                    payment: $payment,
                    amount: $refundAmount,
                    reason: $validated['cancellation_reason'] ?? 'Stay cancelled by guest',
                    actor: $user,
                    idempotencyKey: $request->header('Idempotency-Key'),
                    reverseFee: true,
                );
            }
        }

        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['cancellation_reason'] ?? 'Cancelled by guest',
            'refund_amount' => $refundAmount,
            'refund_status' => $refundStatus,
        ]);

        AuditLog::log(
            action: 'guest.booking_cancelled',
            entity: $booking,
            reason: "Guest cancelled booking {$booking->booking_reference}. Refund calculated: ETB ".number_format($refundAmount, 2),
            user: $user,
            request: $request,
        );

        $message = "Reservation {$booking->booking_reference} cancelled.";
        if ($refundAmount > 0) {
            $message .= ' Refund eligible: ETB '.number_format($refundAmount, 2).' scheduled for finance disbursement.';
        }

        return back()->with('success', $message);
    }

    /**
     * Self check-in action activating access PIN.
     */
    public function checkIn(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();

        $isOwner = (int) $booking->user_id === (int) $user->id || $booking->guest_email === $user->email;
        if (! $isOwner) {
            abort(403, 'Unauthorized check-in.');
        }

        if (! $user->hasAnyPermission(['stays.check_in'])) {
            abort(403, 'Permission denied: Cannot check in.');
        }

        if ($booking->status !== 'confirmed') {
            return back()->with('error', 'Only confirmed reservations can be checked in.');
        }

        $booking->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        AuditLog::log(
            action: 'guest.checked_in',
            entity: $booking,
            reason: "Guest completed self check-in for stay {$booking->booking_reference}.",
            user: $user,
            request: $request,
        );

        return back()->with('success', "Welcome! You have checked in to {$booking->property?->title}. Your door PIN is active.");
    }

    /**
     * Self check-out action concluding stay.
     */
    public function checkOut(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();

        $isOwner = (int) $booking->user_id === (int) $user->id || $booking->guest_email === $user->email;
        if (! $isOwner) {
            abort(403, 'Unauthorized check-out.');
        }

        if (! $user->hasAnyPermission(['stays.check_out'])) {
            abort(403, 'Permission denied: Cannot check out.');
        }

        if ($booking->status !== 'checked_in') {
            return back()->with('error', 'Only checked-in stays can be checked out.');
        }

        $booking->update([
            'status' => 'completed',
            'checked_out_at' => now(),
        ]);

        AuditLog::log(
            action: 'guest.checked_out',
            entity: $booking,
            reason: "Guest completed self check-out for stay {$booking->booking_reference}.",
            user: $user,
            request: $request,
        );

        return back()->with('success', 'Thank you for staying! You have checked out. Please take a moment to review your host.');
    }

    /**
     * Display printable travel receipt voucher.
     */
    public function receipt(Request $request, Booking $booking): View
    {
        $user = $request->user();

        $isOwner = (int) $booking->user_id === (int) $user->id || $booking->guest_email === $user->email;
        if (! $isOwner) {
            abort(403, 'Unauthorized receipt access.');
        }

        if (! $user->hasAnyPermission(['bookings.view', 'stays.view', 'payments.view'])) {
            abort(403, 'Permission denied: Cannot view receipts.');
        }

        return view('dashboards.partials.stay-receipt', compact('booking'));
    }

    /**
     * Create review for a completed stay.
     */
    public function storeReview(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasAnyPermission(['reviews.create', 'stays.review_host'])) {
            abort(403, 'Permission denied: Cannot submit reviews.');
        }

        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'review_title' => ['nullable', 'string', 'max:255'],
            'review_body' => ['required', 'string', 'min:5', 'max:3000'],
        ]);

        if (! empty($validated['booking_id'])) {
            $booking = Booking::find($validated['booking_id']);
            if ($booking && (int) $booking->user_id !== (int) $user->id && $booking->guest_email !== $user->email) {
                abort(403, 'Unauthorized review submission for this reservation.');
            }
        }

        $review = PropertyReview::create([
            'property_id' => $validated['property_id'],
            'user_id' => $user->id,
            'booking_id' => $validated['booking_id'] ?? null,
            'rating' => $validated['rating'],
            'cleanliness_rating' => $validated['rating'],
            'accuracy_rating' => $validated['rating'],
            'communication_rating' => $validated['rating'],
            'location_rating' => $validated['rating'],
            'value_rating' => $validated['rating'],
            'review_title' => $validated['review_title'] ?? null,
            'review_body' => $validated['review_body'],
            'is_verified_stay' => ! empty($validated['booking_id']),
            'status' => 'published',
        ]);

        AuditLog::log(
            action: 'guest.review_created',
            entity: $review,
            reason: "Guest submitted review ID {$review->id} for property ID {$review->property_id}.",
            user: $user,
            request: $request,
        );

        return back()->with('success', 'Your review has been published successfully.');
    }

    /**
     * Update an existing guest review.
     */
    public function updateReview(Request $request, PropertyReview $review): RedirectResponse
    {
        $user = $request->user();

        if ((int) $review->user_id !== (int) $user->id) {
            abort(403, 'Unauthorized review modification.');
        }

        if (! $user->hasAnyPermission(['reviews.update', 'reviews.create'])) {
            abort(403, 'Permission denied: Cannot edit reviews.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review_title' => ['nullable', 'string', 'max:255'],
            'review_body' => ['required', 'string', 'min:5', 'max:3000'],
        ]);

        $review->update([
            'rating' => $validated['rating'],
            'review_title' => $validated['review_title'] ?? $review->review_title,
            'review_body' => $validated['review_body'],
        ]);

        AuditLog::log(
            action: 'guest.review_updated',
            entity: $review,
            reason: "Guest updated review ID {$review->id}.",
            user: $user,
            request: $request,
        );

        return back()->with('success', 'Your review has been updated successfully.');
    }

    /**
     * Delete a guest review.
     */
    public function destroyReview(Request $request, PropertyReview $review): RedirectResponse
    {
        $user = $request->user();

        if ((int) $review->user_id !== (int) $user->id) {
            abort(403, 'Unauthorized review deletion.');
        }

        if (! $user->hasAnyPermission(['reviews.delete', 'reviews.view', 'reviews.create'])) {
            abort(403, 'Permission denied: Cannot delete reviews.');
        }

        $review->delete();

        AuditLog::log(
            action: 'guest.review_deleted',
            entity: $review,
            reason: "Guest deleted review ID {$review->id}.",
            user: $user,
            request: $request,
        );

        return back()->with('success', 'Your review has been removed.');
    }

    /**
     * Remove a short stay from saved favorites.
     */
    public function unsaveStay(Request $request, PropertyFavorite $favorite): RedirectResponse
    {
        $user = $request->user();

        if ((int) $favorite->user_id !== (int) $user->id) {
            abort(403, 'Unauthorized action.');
        }

        if (! $user->hasAnyPermission(['properties.save'])) {
            abort(403, 'Permission denied.');
        }

        $favorite->delete();

        return back()->with('success', 'Stay removed from saved favorites.');
    }
}
