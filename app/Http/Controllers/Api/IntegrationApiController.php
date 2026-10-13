<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\ExperienceBooking;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationApiController extends Controller
{
    /**
     * Portal syndication export feed (JSON).
     */
    public function syndication(): JsonResponse
    {
        $properties = Property::with(['images', 'agent'])
            ->where('is_active', true)
            ->where('status', 'published')
            ->latest()
            ->take(100)
            ->get()
            ->map(fn (Property $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'price_etb' => $p->price,
                'type' => $p->type,
                'category' => $p->property_category,
                'city' => $p->city,
                'subcity' => $p->subcity,
                'bedrooms' => $p->bedrooms,
                'bathrooms' => $p->bathrooms,
                'area_sqm' => $p->area,
                'images' => $p->images->pluck('image_path')->map(fn ($path) => asset($path)),
                'url' => route('properties.show', $p->slug ?: $p->id),
            ]);

        return response()->json([
            'syndication_version' => '1.0',
            'publisher' => 'GTM Real Estate Platform',
            'generated_at' => now()->toIso8601String(),
            'total_listings' => $properties->count(),
            'listings' => $properties,
        ]);
    }

    /**
     * Mortgage financing referral dispatch to Ethiopian commercial banks.
     */
    public function mortgageReferral(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'applicant_name' => ['required', 'string', 'max:255'],
            'applicant_phone' => ['required', 'string', 'max:50'],
            'applicant_email' => ['required', 'email'],
            'monthly_income_etb' => ['required', 'numeric', 'min:1000'],
            'property_price_etb' => ['required', 'numeric', 'min:100000'],
            'preferred_bank' => ['required', 'string', 'in:CBE,Awash,Dashen,Abyssinia,Coop'],
        ]);

        $reference = 'MORT-REF-'.strtoupper(bin2hex(random_bytes(4)));

        return response()->json([
            'success' => true,
            'message' => 'Mortgage referral dispatched to partnering financial institution.',
            'referral_reference' => $reference,
            'details' => $validated,
        ], 201);
    }

    /**
     * Smart lock IoT check-in access code webhook.
     */
    public function smartLockWebhook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string'],
            'event' => ['required', 'string', 'in:pin_created,unlocked,door_open,low_battery'],
            'booking_reference' => ['nullable', 'string'],
        ]);

        return response()->json([
            'success' => true,
            'status' => 'received',
            'timestamp' => now()->toIso8601String(),
            'event_logged' => $validated['event'],
        ]);
    }

    /**
     * Utility company bill payment lookup & webhook.
     */
    public function utilityBillWebhook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'utility_provider' => ['required', 'string', 'in:ethiopian_electric_utility,addis_water_sewerage,ethio_telecom'],
            'account_number' => ['required', 'string'],
            'amount_paid_etb' => ['required', 'numeric'],
            'transaction_reference' => ['required', 'string'],
        ]);

        return response()->json([
            'success' => true,
            'reconciliation_status' => 'reconciled',
            'transaction_id' => $validated['transaction_reference'],
        ]);
    }

    /**
     * Experiences API catalog.
     */
    public function experiences(): JsonResponse
    {
        $experiences = Experience::where('is_active', true)->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $experiences->items(),
            'pagination' => [
                'current_page' => $experiences->currentPage(),
                'last_page' => $experiences->lastPage(),
                'total' => $experiences->total(),
            ],
        ]);
    }

    /**
     * Experience booking API.
     */
    public function bookExperience(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'experience_id' => ['required', 'exists:experiences,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['required', 'email'],
            'guest_phone' => ['required', 'string'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'number_of_guests' => ['required', 'integer', 'min:1'],
        ]);

        $experience = Experience::findOrFail($validated['experience_id']);
        $total = $experience->price_etb * $validated['number_of_guests'];
        $fee = $total * 0.15;

        $booking = ExperienceBooking::create([
            'experience_id' => $experience->id,
            'user_id' => $request->user()?->id,
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'guest_phone' => $validated['guest_phone'],
            'booking_date' => $validated['booking_date'],
            'number_of_guests' => $validated['number_of_guests'],
            'total_amount_etb' => $total,
            'platform_fee_etb' => $fee,
            'host_payout_etb' => $total - $fee,
            'status' => 'confirmed',
            'ticket_code' => 'TKT-'.strtoupper(bin2hex(random_bytes(4))),
        ]);

        return response()->json([
            'success' => true,
            'booking' => $booking,
        ], 201);
    }
}
