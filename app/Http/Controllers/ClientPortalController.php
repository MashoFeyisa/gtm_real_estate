<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Campaign;
use App\Models\GuestServiceRequest;
use App\Models\Inquiry;
use App\Models\Lease;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\PropertyReview;
use App\Models\PropertyViewing;
use App\Models\UnitReservation;
use App\Services\WorkflowIntegrationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientPortalController extends Controller
{
    /**
     * Display client & guest self-service portal (SRS §116–§125).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        if ($user?->isAdmin()) {
            abort(403, 'Admin boundary: Administrators cannot open individual client portals.');
        }

        Booking::completeExpiredReservations();

        $offers = PropertyOffer::with(['property.images', 'negotiations' => fn ($q) => $q->latest(), 'order', 'lease'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('buyer_email', $user->email);
            })
            ->latest()
            ->get();

        $leases = Lease::with(['property.images', 'schedules' => fn ($q) => $q->orderBy('installment_number'), 'agent.user'])
            ->where(function ($query) use ($user) {
                $query->where('tenant_user_id', $user->id)
                    ->orWhere('tenant_email', $user->email);
            })
            ->latest()
            ->get();

        $viewings = PropertyViewing::with(['property.images', 'agent.user'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('buyer_email', $user->email);
            })
            ->latest()
            ->get();

        $bookings = Booking::with(['property.images', 'property.agent.user', 'payments', 'guestServiceRequests'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('guest_email', $user->email);
            })
            ->latest()
            ->get();

        $reservations = UnitReservation::with(['project', 'unit.building'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('buyer_email', $user->email);
            })
            ->latest()
            ->get();

        $payments = Payment::with(['property', 'payable'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('payer_email', $user->email);
            })
            ->latest()
            ->paginate(12, ['*'], 'payments_page')
            ->withQueryString();

        $notifications = $user->notifications()->take(20)->get();
        $unreadNotificationsCount = $user->unreadNotifications()->count();

        $collections = $user->savedCollections()->with(['properties.images'])->latest()->get();
        $tickets = $user->maintenanceTickets()->with('property')->latest()->get();

        // Guest Housekeeping & Concierge Service Requests linked to user's stay bookings
        $serviceRequests = GuestServiceRequest::query()
            ->whereIn('booking_id', $bookings->pluck('id'))
            ->with(['booking.property', 'handler'])
            ->latest()
            ->get();

        // Reviews submitted by this guest/client
        $myReviews = PropertyReview::query()
            ->where('user_id', $user->id)
            ->with(['property.images', 'booking'])
            ->latest()
            ->get();

        // Active Promotional Campaigns & Special Offers for Clients
        $activeCampaigns = Campaign::with(['property.images', 'agent.user'])
            ->whereIn('status', ['published', 'approved'])
            ->latest()
            ->get();

        // Properties breakdown: Admin-posted Corporate Flagship vs Licensed Agent-posted
        $adminProperties = Property::query()
            ->where('status', '!=', 'archived')
            ->where(function ($q) {
                $q->where('is_active', true)
                    ->orWhereHas('campaigns', fn ($cq) => $cq->whereIn('status', ['published', 'approved']));
            })
            ->whereNull('agent_id')
            ->with(['images'])
            ->latest()
            ->get();

        $agentProperties = Property::query()
            ->where('status', '!=', 'archived')
            ->where(function ($q) {
                $q->where('is_active', true)
                    ->orWhereHas('campaigns', fn ($cq) => $cq->whereIn('status', ['published', 'approved']));
            })
            ->whereNotNull('agent_id')
            ->with(['images', 'agent.user'])
            ->latest()
            ->get();

        $allProperties = Property::query()
            ->where('status', '!=', 'archived')
            ->where(function ($q) {
                $q->where('is_active', true)
                    ->orWhereHas('campaigns', fn ($cq) => $cq->whereIn('status', ['published', 'approved']));
            })
            ->with(['images', 'agent.user', 'campaigns'])
            ->latest()
            ->paginate(12, ['*'], 'properties_page')
            ->withQueryString();

        // User's orders / agreements
        $orders = Order::query()
            ->where(function ($q) use ($user) {
                $q->where('email', $user->email)
                    ->orWhere('phone', $user->phone);
            })
            ->with(['property.agent', 'agent'])
            ->latest()
            ->get();

        // User's inquiries to Admin or Agents
        $inquiries = Inquiry::query()
            ->where('email', $user->email)
            ->with('agent')
            ->latest()
            ->get();

        $stats = [
            'active_leases' => $leases->where('status', 'active')->count(),
            'pending_offers' => $offers->whereIn('status', ['submitted', 'under_review', 'countered'])->count(),
            'upcoming_viewings' => $viewings->whereIn('status', ['requested', 'confirmed'])->count(),
            'active_stays' => $bookings->whereIn('status', ['confirmed', 'checked_in'])->count(),
            'unit_reservations' => $reservations->count(),
            'total_paid' => Payment::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)->orWhere('payer_email', $user->email);
            })->where('status', 'completed')->sum('amount'),
            'pending_payments' => Payment::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)->orWhere('payer_email', $user->email);
            })->where('status', 'pending')->count(),
            'saved_collections' => $collections->count(),
            'maintenance_tickets' => $tickets->count(),
            'service_requests' => $serviceRequests->whereIn('status', ['pending', 'in_progress'])->count(),
            'my_reviews' => $myReviews->count(),
            'admin_properties' => $adminProperties->count(),
            'agent_properties' => $agentProperties->count(),
            'active_campaigns' => $activeCampaigns->count(),
            'unread_notifications' => $unreadNotificationsCount,
        ];

        $actionableTasks = app(WorkflowIntegrationService::class)->getActorActionableTasks($user);

        return view('client.portal', compact(
            'user',
            'offers',
            'leases',
            'viewings',
            'bookings',
            'reservations',
            'payments',
            'collections',
            'tickets',
            'serviceRequests',
            'myReviews',
            'adminProperties',
            'agentProperties',
            'allProperties',
            'activeCampaigns',
            'orders',
            'inquiries',
            'notifications',
            'stats',
            'actionableTasks'
        ));
    }
}
