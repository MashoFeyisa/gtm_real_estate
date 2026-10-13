<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyFormRequest;
use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\Lease;
use App\Models\Listing;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyOffer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SellerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user?->status === 'suspended' || (! in_array($user?->role, ['seller', 'landlord', 'property_owner'], true) && ! $user?->isAdmin() && ! $user?->hasPermission('properties.view') && ! $user?->hasPermission('property.view'))) {
            abort(403, 'Permission denied: Seller dashboard access required.');
        }

        // Properties owned or managed by this seller/landlord
        $ownedPropertyIds = Property::where('user_id', $user->id)->pluck('id')
            ->merge(PropertyDocument::where('user_id', $user->id)->pluck('property_id'))
            ->merge(Lease::where('landlord_user_id', $user->id)->pluck('property_id'))
            ->merge(Property::where('host_id', $user->id)->pluck('id'))
            ->unique()
            ->filter()
            ->all();

        $properties = Property::with(['images', 'documents', 'listings'])
            ->where(function ($q) use ($ownedPropertyIds) {
                if (! empty($ownedPropertyIds)) {
                    $q->whereIn('id', $ownedPropertyIds);
                } else {
                    $q->where('id', 0); // empty fallback
                }
            })
            ->latest()
            ->get();

        if ($properties->isEmpty() && ($user->isAdmin() || in_array($user->role, ['landlord', 'seller', 'property_owner'], true))) {
            // For testing or demonstration, load top properties
            $properties = Property::with(['images', 'documents', 'listings'])->latest()->take(5)->get();
        }

        $propertyIds = $properties->pluck('id')->all();

        $listings = Listing::with('property.images')
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get();

        $leads = PropertyOffer::with(['property', 'negotiations'])
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get();

        $agentIds = $properties->pluck('agent_id')->filter()->unique()->all();

        $inquiries = Inquiry::query()
            ->when(! empty($agentIds), fn ($q) => $q->whereIn('agent_id', $agentIds))
            ->latest()
            ->take(10)
            ->get();

        // Listing Quality Score Calculator (0 - 100)
        $scores = [];
        foreach ($properties as $prop) {
            $score = 0;
            if ($prop->images->count() >= 1) {
                $score += 25;
            }
            if ($prop->images->count() >= 3) {
                $score += 15;
            }
            if (strlen((string) $prop->description) > 80) {
                $score += 20;
            }
            if ($prop->documents->count() >= 1) {
                $score += 25;
            }
            if ($prop->price > 0 && ! empty($prop->city)) {
                $score += 15;
            }
            $scores[$prop->id] = min(100, $score);
        }

        return view('dashboards.seller', compact(
            'properties',
            'listings',
            'leads',
            'inquiries',
            'scores'
        ));
    }

    public function storeListing(PropertyFormRequest $request): RedirectResponse
    {
        $this->authorize('create', Property::class);

        $validated = $request->validated();
        $user = $request->user();

        $status = in_array($validated['status'] ?? 'draft', ['published', 'draft', 'pending_review'], true)
            ? ($validated['status'] ?? 'draft')
            : 'draft';

        $property = Property::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(5)),
            'description' => $validated['description'] ?? null,
            'image_path' => $request->storeImage(),
            'price' => $validated['price'],
            'bedrooms' => $validated['bedrooms'] ?? 1,
            'bathrooms' => $validated['bathrooms'] ?? 1,
            'area' => $validated['area'] ?? 50,
            'building_area' => $validated['building_area'] ?? ($validated['area'] ?? 50),
            'floors' => $validated['floors'] ?? 1,
            'parking_spaces' => $validated['parking_spaces'] ?? 1,
            'balconies' => $validated['balconies'] ?? 0,
            'year_built' => $validated['year_built'] ?? null,
            'condition' => $validated['condition'] ?? 'excellent',
            'has_electricity' => (bool) $request->input('has_electricity', false),
            'has_water' => (bool) $request->input('has_water', false),
            'has_generator' => (bool) $request->input('has_generator', false),
            'has_water_tank' => (bool) $request->input('has_water_tank', false),
            'has_solar' => (bool) $request->input('has_solar', false),
            'has_security' => (bool) $request->input('has_security', false),
            'type' => $validated['type'] ?? 'sale',
            'property_category' => $validated['category'] ?? 'home',
            'status' => $status,
            'city' => $validated['city'] ?? 'Addis Ababa',
            'subcity' => $validated['subcity'] ?? null,
            'woreda' => $validated['woreda'] ?? null,
            'neighborhood' => $validated['neighborhood'] ?? null,
            'address' => $validated['address'] ?? null,
            'latitude' => ! empty($validated['latitude']) ? (float) $validated['latitude'] : null,
            'longitude' => ! empty($validated['longitude']) ? (float) $validated['longitude'] : null,
            'featured' => false,
            'is_active' => $status === 'published',
        ]);

        $request->syncPropertyImages($property);

        AuditLog::log(
            action: 'seller.property_created',
            entityType: 'Property',
            entityId: $property->id,
            newValue: ['title' => $property->title, 'status' => $property->status],
            user: $user,
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', 'Listing "'.$property->title.'" created successfully.');
    }

    public function updateListing(PropertyFormRequest $request, Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

        $validated = $request->validated();
        $oldPrice = (float) $property->price;
        $newPrice = (float) $validated['price'];

        $property->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'image_path' => $request->storeImage($property->image_path),
            'price' => $validated['price'],
            'bedrooms' => $validated['bedrooms'] ?? 1,
            'bathrooms' => $validated['bathrooms'] ?? 1,
            'area' => $validated['area'] ?? 50,
            'building_area' => $validated['building_area'] ?? ($validated['area'] ?? 50),
            'type' => $validated['type'] ?? $property->type,
            'property_category' => $validated['category'] ?? $property->property_category,
            'status' => $validated['status'] ?? $property->status,
            'city' => $validated['city'] ?? $property->city,
            'subcity' => $validated['subcity'] ?? $property->subcity,
            'woreda' => $validated['woreda'] ?? $property->woreda,
            'neighborhood' => $validated['neighborhood'] ?? $property->neighborhood,
            'address' => $validated['address'] ?? $property->address,
            'is_active' => in_array($validated['status'] ?? $property->status, ['published', 'available'], true),
        ]);

        if ($oldPrice !== $newPrice) {
            foreach ($property->listings as $listing) {
                $listing->update(['price' => $newPrice]);
            }
        }

        $request->syncPropertyImages($property);

        return redirect()->route('seller.dashboard')->with('success', 'Listing updated successfully.');
    }

    public function togglePublish(Property $property): RedirectResponse
    {
        $this->authorize('publish', $property);

        $newActive = ! $property->is_active;
        $property->is_active = $newActive;
        $property->status = $newActive ? 'published' : 'draft';
        $property->save();

        foreach ($property->listings as $listing) {
            $listing->update(['status' => $newActive ? 'published' : 'draft']);
        }

        return redirect()->route('seller.dashboard')->with('success', 'Listing status updated to '.($newActive ? 'Published' : 'Draft').'.');
    }

    public function updateListingStatus(Property $property, string $status): RedirectResponse
    {
        $this->authorize('update', $property);

        if (! in_array($status, ['draft', 'pending_review', 'published', 'paused', 'sold', 'rented'], true)) {
            abort(422, 'Invalid status.');
        }

        $property->status = $status;
        $property->is_active = in_array($status, ['published', 'sold', 'rented'], true);
        $property->save();

        foreach ($property->listings as $listing) {
            $listing->update(['status' => $status]);
        }

        return redirect()->route('seller.dashboard')->with('success', 'Listing transitioned to '.ucfirst(str_replace('_', ' ', $status)).'.');
    }
}
