<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\PropertyFavorite;
use App\Models\PropertyLike;
use App\Models\PropertyOffer;
use App\Models\PropertyViewing;
use App\Models\SavedSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuyerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            abort(403, 'Admin boundary: Administrators cannot open individual client dashboards.');
        }

        $hasBuyerPerm = $user?->hasAnyPermission([
            'properties.browse',
            'searches.save',
            'viewings.book',
            'offers.create',
            'properties.view',
            'property.view',
        ]);

        if ($user?->status === 'suspended' || (! in_array($user?->role, ['buyer', 'client', 'user', 'guest'], true) && ! $hasBuyerPerm)) {
            abort(403, 'Permission denied: Property browsing access required.');
        }

        $likedProperties = PropertyLike::with('property.images')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $favoritedProperties = PropertyFavorite::with('property.images')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $savedSearches = $user->savedSearches()->latest()->get();

        $inquiries = Inquiry::query()
            ->where(function ($q) use ($user) {
                $q->where('email', $user->email)
                    ->orWhere('name', $user->name);
            })
            ->latest()
            ->get();

        $viewings = PropertyViewing::query()
            ->with(['property.images', 'agent.user'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('buyer_email', $user->email);
            })
            ->latest()
            ->get();

        $offers = PropertyOffer::query()
            ->with(['property.images', 'negotiations' => fn ($q) => $q->latest()])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('buyer_email', $user->email);
            })
            ->latest()
            ->get();

        return view('dashboards.buyer', compact(
            'likedProperties',
            'favoritedProperties',
            'savedSearches',
            'inquiries',
            'viewings',
            'offers'
        ));
    }

    public function storeSavedSearch(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'property_category' => ['nullable', 'string', 'max:100'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'alert_frequency' => ['required', 'in:instant,daily,weekly,off'],
        ]);

        $search = $user->savedSearches()->create([
            'title' => $validated['title'],
            'criteria' => [
                'city' => $validated['city'] ?? null,
                'property_category' => $validated['property_category'] ?? null,
                'max_price' => $validated['max_price'] ?? null,
            ],
            'alert_frequency' => $validated['alert_frequency'],
        ]);

        AuditLog::log(
            action: 'buyer.saved_search_created',
            entityType: 'SavedSearch',
            entityId: $search->id,
            newValue: $search->toArray(),
            user: $user,
            request: $request
        );

        return back()->with('success', "Search alert '{$search->title}' saved successfully.");
    }

    public function destroySavedSearch(Request $request, SavedSearch $savedSearch): RedirectResponse
    {
        $user = $request->user();

        if ($savedSearch->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        $savedSearch->delete();

        return back()->with('success', 'Search alert removed.');
    }
}
