<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HostDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $hasHostPerm = $user?->hasAnyPermission([
            'stays.listing_view',
            'stays.view',
            'stays.listing_create',
            'stays.listing_update',
            'stays.pricing_manage',
            'stays.availability_manage',
        ]);

        if ($user?->status === 'suspended' || (! $user?->isHost() && ! $user?->isAdmin() && ! $hasHostPerm)) {
            abort(403, 'Permission denied: Short-stay host access required.');
        }

        $properties = Property::with(['images', 'bookings'])
            ->where('host_id', $user->id)
            ->latest()
            ->get();

        if ($properties->isEmpty() && ($user->isAdmin() || $user->role === 'host')) {
            // Fallback for host demo
            $properties = Property::with(['images', 'bookings'])->whereIn('type', ['stay', 'short_stay', 'daily'])->latest()->take(3)->get();
        }

        $propertyIds = $properties->pluck('id')->all();

        $bookings = Booking::with('property')
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get();

        $upcomingBookings = $bookings->filter(fn ($b) => in_array($b->status, ['confirmed', 'checked_in', 'pending'], true));

        $totalRevenue = (float) $bookings->where('status', '!=', 'cancelled')->sum('total_amount');
        $totalNights = $bookings->where('status', '!=', 'cancelled')->sum('total_nights');
        $occupancyRate = min(100, round(($totalNights / max(1, count($properties) * 30)) * 100, 1));

        $payouts = Payment::whereIn('property_id', $propertyIds)
            ->orWhere('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $reviews = PropertyReview::with('property')
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get();

        return view('dashboards.host', compact(
            'properties',
            'bookings',
            'upcomingBookings',
            'totalRevenue',
            'occupancyRate',
            'payouts',
            'reviews'
        ));
    }

    public function replyReview(Request $request, PropertyReview $review): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'host_reply' => ['required', 'string', 'max:1000'],
        ]);

        $review->update([
            'host_reply' => $validated['host_reply'],
            'host_replied_at' => now(),
        ]);

        AuditLog::log(
            action: 'host.review_replied',
            entityType: 'PropertyReview',
            entityId: $review->id,
            newValue: ['host_reply' => $validated['host_reply']],
            user: $user,
            request: $request
        );

        return back()->with('success', 'Host reply posted successfully.');
    }

    public function storeStayListing(Request $request): RedirectResponse
    {
        $this->authorize('create', Property::class);

        $user = $request->user();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:1'],
            'cleaning_fee' => ['nullable', 'numeric', 'min:0'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
            'house_rules' => ['nullable', 'string'],
            'check_in_time' => ['nullable', 'string'],
            'check_out_time' => ['nullable', 'string'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'city' => ['nullable', 'string'],
            'subcity' => ['nullable', 'string'],
            'neighborhood' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:draft,published'],
        ]);

        $status = $validated['status'] ?? 'published';

        $property = Property::create([
            'user_id' => $user->id,
            'host_id' => $user->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(5)),
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'cleaning_fee' => $validated['cleaning_fee'] ?? 0,
            'service_fee' => $validated['service_fee'] ?? 0,
            'house_rules' => $validated['house_rules'] ?? null,
            'check_in_time' => $validated['check_in_time'] ?? '14:00',
            'check_out_time' => $validated['check_out_time'] ?? '11:00',
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

        AuditLog::log(
            action: 'host.stay_created',
            entityType: 'Property',
            entityId: $property->id,
            newValue: ['title' => $property->title, 'nightly_price' => $property->price],
            user: $user,
            request: $request
        );

        return redirect()->route('host.dashboard')->with('success', 'Short-stay accommodation "'.$property->title.'" registered successfully.');
    }

    public function togglePublishStay(Property $property): RedirectResponse
    {
        $this->authorize('publish', $property);

        $newActive = ! $property->is_active;
        $property->is_active = $newActive;
        $property->status = $newActive ? 'published' : 'draft';
        $property->save();

        foreach ($property->listings as $listing) {
            $listing->update(['status' => $newActive ? 'published' : 'draft']);
        }

        return redirect()->route('host.dashboard')->with('success', 'Stay status updated to '.($newActive ? 'Published' : 'Draft').'.');
    }
}
