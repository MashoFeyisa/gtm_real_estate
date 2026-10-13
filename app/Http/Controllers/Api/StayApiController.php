<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StayApiController extends Controller
{
    /**
     * List short-stay properties available for travelers.
     */
    public function index(Request $request): JsonResponse
    {
        $stays = Property::query()
            ->with(['images'])
            ->where('is_active', true)
            ->whereIn('type', ['stay', 'rent'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $stays->items(),
            'pagination' => [
                'current_page' => $stays->currentPage(),
                'last_page' => $stays->lastPage(),
                'total' => $stays->total(),
            ],
        ]);
    }

    /**
     * Create mobile stay reservation booking hold.
     */
    public function book(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:50'],
            'guests_count' => ['required', 'integer', 'min:1'],
        ]);

        $property = Property::findOrFail($validated['property_id']);
        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);

        if (! $property->isAvailableForDates($checkIn->toDateString(), $checkOut->toDateString())) {
            return response()->json([
                'success' => false,
                'message' => 'The selected dates are unfortunately not available.',
            ], 422);
        }

        $nights = $checkIn->diffInDays($checkOut);
        $nightlyPrice = (float) ($property->nightly_rate ?? $property->price ?? 2500);
        $totalAmount = $nightlyPrice * max(1, $nights);

        $booking = Booking::create([
            'property_id' => $property->id,
            'user_id' => $request->user()?->id,
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'guest_phone' => $validated['guest_phone'],
            'guests_count' => $validated['guests_count'],
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'total_nights' => $nights,
            'nightly_price' => $nightlyPrice,
            'accommodation_total' => $totalAmount,
            'total_amount' => $totalAmount,
            'payment_status' => 'pending',
            'status' => 'confirmed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stay reservation created successfully.',
            'data' => [
                'booking_id' => $booking->id,
                'property' => $property->title,
                'check_in' => $booking->check_in_date->toDateString(),
                'check_out' => $booking->check_out_date->toDateString(),
                'nights' => $nights,
                'total_amount_etb' => $booking->total_amount,
                'status' => $booking->status,
            ],
        ], 201);
    }
}
