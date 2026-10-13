<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\PropertyFavorite;
use App\Models\PropertyReview;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            abort(403, 'Admin boundary: Administrators cannot open individual guest dashboards.');
        }

        if ($user?->status === 'suspended' || (! in_array($user?->role, ['guest', 'traveler'], true) && ! $user?->hasPermission('bookings.view') && ! $user?->hasPermission('stays.view'))) {
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

        return view('dashboards.guest', compact(
            'bookings',
            'upcomingBooking',
            'reviews',
            'savedStays'
        ));
    }
}
