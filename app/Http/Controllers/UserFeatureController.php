<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\PropertyReview;
use App\Models\PropertyViewing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserFeatureController extends Controller
{
    /**
     * Show the user's "Post Property" create page.
     */
    public function createProperty(Request $request): View
    {
        $user = $request->user();
        if (! $user || (! $user->hasPermission('properties.create') && ! $user->hasPermission('property.create'))) {
            abort(403, 'Permission denied: Property creation permission required.');
        }

        return view('user.properties.create');
    }

    /**
     * Store a property created by the user.
     */
    public function storeProperty(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || (! $user->hasPermission('properties.create') && ! $user->hasPermission('property.create'))) {
            abort(403, 'Permission denied: Property creation permission required.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'type' => ['nullable', 'string', 'in:sale,rent,short_stay'],
            'property_category' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'area' => ['nullable', 'numeric', 'min:0'],
        ]);

        $property = Property::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(5)),
            'price' => $validated['price'],
            'type' => $validated['type'] ?? 'sale',
            'property_category' => $validated['property_category'] ?? 'home',
            'city' => $validated['city'] ?? 'Addis Ababa',
            'description' => $validated['description'] ?? null,
            'bedrooms' => $validated['bedrooms'] ?? 1,
            'bathrooms' => $validated['bathrooms'] ?? 1,
            'area' => $validated['area'] ?? 50,
            'building_area' => $validated['area'] ?? 50,
            'status' => 'draft',
            'is_active' => false,
        ]);

        AuditLog::log(
            action: 'property.created_by_user',
            entityType: 'Property',
            entityId: $property->id,
            newValue: ['title' => $property->title, 'price' => $property->price],
            user: $user,
            request: $request
        );

        return redirect()->to($user->dashboard_url)->with('success', "Property '{$property->title}' posted successfully as draft.");
    }

    /**
     * Show the user's "My Bookings" page inside the unified dashboard shell.
     */
    public function bookings(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || (! $user->hasPermission('bookings.view') && ! $user->hasPermission('booking.view'))) {
            abort(403, 'Permission denied: Booking view permission required.');
        }

        return redirect()->to($user->dashboard_url.'#bookings');
    }

    /**
     * Show the user's "My Offers" page.
     */
    public function offers(Request $request): View
    {
        $user = $request->user();
        if (! $user || (! $user->hasPermission('offers.view') && ! $user->hasPermission('offer.view') && ! $user->hasPermission('offers.create') && ! $user->hasPermission('offer.create'))) {
            abort(403, 'Permission denied: Offers access required.');
        }

        $offers = PropertyOffer::where('user_id', $user->id)
            ->with(['property', 'negotiations'])
            ->latest()
            ->get();

        return view('user.offers', compact('offers'));
    }

    /**
     * Show the user's "My Viewings" page.
     */
    public function viewings(Request $request): View
    {
        $user = $request->user();
        if (! $user || (! $user->hasPermission('viewings.view') && ! $user->hasPermission('viewing.view') && ! $user->hasPermission('viewings.create') && ! $user->hasPermission('viewing.create'))) {
            abort(403, 'Permission denied: Viewings access required.');
        }

        $viewings = PropertyViewing::where('user_id', $user->id)
            ->with('property')
            ->latest()
            ->get();

        return view('user.viewings', compact('viewings'));
    }

    /**
     * Show the user's "My Leases & Rent" page inside the unified dashboard shell.
     */
    public function leases(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || (! $user->hasPermission('leases.view') && ! $user->hasPermission('lease.view') && ! $user->hasPermission('rent.pay'))) {
            abort(403, 'Permission denied: Leases access required.');
        }

        return redirect()->to($user->dashboard_url.'#leases');
    }

    /**
     * Show the user's "Maintenance Requests" page inside the unified dashboard shell.
     */
    public function maintenance(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || (! $user->hasPermission('maintenance.view') && ! $user->hasPermission('maintenance.create'))) {
            abort(403, 'Permission denied: Maintenance access required.');
        }

        return redirect()->to($user->dashboard_url.'#maintenance');
    }

    /**
     * Show the user's "My Reviews" page.
     */
    public function reviews(Request $request): View
    {
        $user = $request->user();
        if (! $user || (! $user->hasPermission('reviews.view') && ! $user->hasPermission('reviews.create'))) {
            abort(403, 'Permission denied: Reviews access required.');
        }

        $reviews = PropertyReview::where('user_id', $user->id)
            ->with('property')
            ->latest()
            ->get();

        return view('user.reviews', compact('reviews'));
    }

    /**
     * Show the user's "My Inquiries" page.
     */
    public function inquiries(Request $request): View
    {
        $user = $request->user();
        if (! $user || (! $user->hasPermission('inquiries.view') && ! $user->hasPermission('inquiries.create'))) {
            abort(403, 'Permission denied: Inquiries access required.');
        }

        $agentId = $user->agentProfile?->id;
        $inquiries = Inquiry::query()
            ->where(function ($q) use ($user, $agentId) {
                $q->where('email', $user->email)
                    ->orWhere('user_id', $user->id);

                if ($agentId) {
                    $q->orWhere('agent_id', $agentId);
                }
            })
            ->with(['property', 'agent'])
            ->latest()
            ->get();

        return view('user.inquiries', compact('inquiries'));
    }
}
