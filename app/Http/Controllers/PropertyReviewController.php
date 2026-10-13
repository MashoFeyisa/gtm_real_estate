<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PropertyReviewController extends Controller
{
    /**
     * Submit a guest or client review for a property or stay.
     */
    public function store(Request $request, Property $property): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'cleanliness_rating' => ['nullable', 'integer', 'between:1,5'],
            'accuracy_rating' => ['nullable', 'integer', 'between:1,5'],
            'communication_rating' => ['nullable', 'integer', 'between:1,5'],
            'location_rating' => ['nullable', 'integer', 'between:1,5'],
            'value_rating' => ['nullable', 'integer', 'between:1,5'],
            'review_title' => ['nullable', 'string', 'max:255'],
            'review_body' => ['required', 'string', 'min:10', 'max:3000'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
        ]);

        $user = $request->user();

        // Check if user has a confirmed stay booking for this property
        $isVerifiedStay = false;
        if (! empty($validated['booking_id'])) {
            $isVerifiedStay = $property->bookings()
                ->where('id', $validated['booking_id'])
                ->where('user_id', $user->id)
                ->whereIn('status', ['confirmed', 'active', 'completed', 'checked_out'])
                ->exists();
        } elseif ($property->bookings()->where('user_id', $user->id)->whereIn('status', ['confirmed', 'active', 'completed', 'checked_out'])->exists()) {
            $isVerifiedStay = true;
        }

        $property->reviews()->create([
            'user_id' => $user->id,
            'booking_id' => $validated['booking_id'] ?? null,
            'rating' => $validated['rating'],
            'cleanliness_rating' => $validated['cleanliness_rating'] ?? $validated['rating'],
            'accuracy_rating' => $validated['accuracy_rating'] ?? $validated['rating'],
            'communication_rating' => $validated['communication_rating'] ?? $validated['rating'],
            'location_rating' => $validated['location_rating'] ?? $validated['rating'],
            'value_rating' => $validated['value_rating'] ?? $validated['rating'],
            'review_title' => $validated['review_title'] ?? null,
            'review_body' => $validated['review_body'],
            'is_verified_stay' => $isVerifiedStay,
            'status' => 'published',
        ]);

        return back()->with('success', 'Your review has been published successfully.');
    }

    /**
     * Admin review moderation.
     */
    public function toggleStatus(Request $request, PropertyReview $review): RedirectResponse
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $newStatus = $review->status === 'published' ? 'hidden' : 'published';
        $review->update(['status' => $newStatus]);

        return back()->with('success', 'Review status updated to '.$newStatus.'.');
    }
}
