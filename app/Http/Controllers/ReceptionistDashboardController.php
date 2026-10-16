<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\FrontDeskShift;
use App\Models\GuestServiceRequest;
use App\Models\Inquiry;
use App\Models\MaintenanceTicket;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyReview;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\GuestBookingNotification;
use App\Services\LedgerService;
use App\Services\WorkflowIntegrationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReceptionistDashboardController extends Controller
{
    /**
     * Display the Front Desk / Receptionist Dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessReceptionDesk()) {
            abort(403, 'Unauthorized access to Front Desk Reception.');
        }

        $isAdmin = $user->isAdmin();
        $isReceptionist = $user->isReceptionist();

        // Hotel & Guesthouse properties available for receptionists
        $hotelProperties = Property::query()
            ->where(function ($q) use ($user) {
                $q->whereIn('property_category', ['guest_house', 'hotel', 'guesthouse', 'hotels', 'serviced_apartment'])
                    ->orWhere('type', 'stay')
                    ->orWhere('host_id', $user->id)
                    ->orWhereHas('bookings');
            })
            ->orderBy('title')
            ->get();

        if ($hotelProperties->isEmpty()) {
            $hotelProperties = Property::query()->orderBy('title')->get();
        }

        // Short-stay properties hosted or managed by front desk
        $stayProperties = Property::query()
            ->where(function ($q) use ($user) {
                $q->where('type', 'stay')
                    ->orWhereIn('property_category', ['guest_house', 'hotel', 'guesthouse', 'hotels', 'serviced_apartment'])
                    ->orWhere('host_id', $user->id);
            })
            ->with(['listings', 'host', 'images', 'reviews', 'bookings'])
            ->latest()
            ->get();

        // Determine current property
        $selectedPropertyId = $request->input('property_id');
        if ($selectedPropertyId) {
            $currentProperty = Property::find($selectedPropertyId) ?: $hotelProperties->first();
        } elseif (! $isAdmin && $user->assigned_property_id) {
            $currentProperty = Property::find($user->assigned_property_id) ?: $hotelProperties->first();
        } else {
            $currentProperty = $hotelProperties->first();
        }

        $propertyId = $currentProperty?->id;
        $today = Carbon::today()->toDateString();

        // 0. Pending Bookings awaiting Host Acceptance
        $pendingBookings = Booking::query()
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->where('status', 'pending')
            ->with(['user', 'property', 'payments'])
            ->latest()
            ->get();

        if ($pendingBookings->isEmpty()) {
            $pendingBookings = Booking::query()
                ->where('status', 'pending')
                ->with(['user', 'property', 'payments'])
                ->latest()
                ->take(5)
                ->get();
        }

        // 1. Expected Arrivals / Check-ins today
        $expectedCheckins = Booking::query()
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->whereDate('check_in_date', '<=', $today)
            ->whereNull('checked_in_at')
            ->whereNotIn('status', ['cancelled', 'completed', 'checked_out'])
            ->with(['user', 'property', 'payments'])
            ->orderBy('check_in_date')
            ->get();

        // 2. Currently In-House Guests (checked-in)
        $inHouseGuests = Booking::query()
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->whereNotNull('checked_in_at')
            ->whereNull('checked_out_at')
            ->whereNotIn('status', ['cancelled', 'completed', 'checked_out'])
            ->with(['user', 'property', 'payments', 'guestServiceRequests'])
            ->orderBy('check_out_date')
            ->get();

        // 3. Expected Departures / Check-outs today
        $expectedCheckouts = Booking::query()
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->whereDate('check_out_date', '<=', $today)
            ->whereNotNull('checked_in_at')
            ->whereNull('checked_out_at')
            ->whereNotIn('status', ['cancelled', 'completed', 'checked_out'])
            ->with(['user', 'property', 'payments'])
            ->orderBy('check_out_date')
            ->get();

        // 4. All Recent & Upcoming Bookings for this property
        $recentBookings = Booking::query()
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->with(['user', 'property', 'payments'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // 5. Guest Service Requests for the property
        $serviceRequests = GuestServiceRequest::query()
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->with(['booking.user', 'handler'])
            ->latest()
            ->get();

        $pendingRequests = $serviceRequests->where('status', 'pending');
        $inProgressRequests = $serviceRequests->where('status', 'in_progress');

        // 6. Today's collections made at the front desk / for this property
        $todayPayments = Payment::query()
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->whereDate('created_at', $today)
            ->where('status', 'confirmed')
            ->get();

        $cashCollected = (float) $todayPayments->where('payment_method', 'cash')->sum('amount');
        $telebirrCollected = (float) $todayPayments->where('payment_method', 'telebirr')->sum('amount');
        $cbeCollected = (float) $todayPayments->where('payment_method', 'cbe')->sum('amount');
        $posCollected = (float) $todayPayments->whereIn('payment_method', ['card_pos', 'pos', 'credit_card', 'card'])->sum('amount');
        $totalCollectedToday = (float) $todayPayments->sum('amount');

        // 7. Today's Front Desk Shift
        $todayShift = FrontDeskShift::query()
            ->where('receptionist_user_id', $user->id)
            ->whereDate('shift_date', $today)
            ->latest()
            ->first();

        // 8. Shift History
        $shiftHistory = FrontDeskShift::query()
            ->when(! $isAdmin, fn ($q) => $q->where('receptionist_user_id', $user->id))
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->with(['receptionist', 'financeOfficer', 'property'])
            ->latest()
            ->limit(10)
            ->get();

        // 9. Guest & Client Reviews for this property/all properties
        $reviews = PropertyReview::query()
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->with(['user', 'property', 'booking'])
            ->latest()
            ->get();

        // 10. Maintenance Tickets for this property/portfolio
        $maintenanceTickets = MaintenanceTicket::query()
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->with(['property', 'user', 'assignedAgent'])
            ->latest()
            ->get();

        // 11. Guest Inquiries & Communication Desk
        $inquiries = Inquiry::query()
            ->latest()
            ->take(20)
            ->get();

        // 12. Corporate Admin Properties vs Licensed Agent Properties
        $adminProperties = Property::query()
            ->publicVisible()
            ->whereNull('agent_id')
            ->with('images')
            ->latest()
            ->take(8)
            ->get();

        $agentProperties = Property::query()
            ->publicVisible()
            ->whereNotNull('agent_id')
            ->with(['images', 'agent.user'])
            ->latest()
            ->take(8)
            ->get();

        // Brand settings
        $siteBrand = [
            'name' => SiteSetting::get('site_name', 'GTM Real Estate'),
            'logoUrl' => SiteSetting::get('logo_path') ? asset('storage/'.SiteSetting::get('logo_path')) : asset('logo.png'),
        ];

        $stats = [
            'in_house_count' => $inHouseGuests->count(),
            'today_checkins_count' => $expectedCheckins->count(),
            'today_checkouts_count' => $expectedCheckouts->count(),
            'pending_requests_count' => $pendingRequests->count(),
            'total_collected_today' => $totalCollectedToday,
            'cash_collected' => $cashCollected,
            'telebirr_collected' => $telebirrCollected,
            'cbe_collected' => $cbeCollected,
            'pos_collected' => $posCollected,
            'reviews_count' => $reviews->count(),
            'maintenance_tickets_count' => $maintenanceTickets->whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
            'inquiries_count' => $inquiries->where('status', 'unread')->count(),
            'stay_properties_count' => $stayProperties->count(),
            'pending_bookings_count' => Booking::query()->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))->where('status', 'pending')->count(),
        ];

        $actionableTasks = app(WorkflowIntegrationService::class)->getActorActionableTasks($user);

        return view('dashboard.receptionist', compact(
            'user',
            'isAdmin',
            'isReceptionist',
            'hotelProperties',
            'stayProperties',
            'currentProperty',
            'pendingBookings',
            'expectedCheckins',
            'inHouseGuests',
            'expectedCheckouts',
            'recentBookings',
            'serviceRequests',
            'pendingRequests',
            'inProgressRequests',
            'todayShift',
            'shiftHistory',
            'reviews',
            'maintenanceTickets',
            'inquiries',
            'adminProperties',
            'agentProperties',
            'stats',
            'siteBrand',
            'actionableTasks'
        ));
    }

    /**
     * Process Guest Check-In at the Front Desk.
     */
    public function checkIn(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessReceptionDesk()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:50'],
            'guest_id_number' => ['required', 'string', 'max:100'],
            'payment_method' => ['nullable', 'in:cash,telebirr,cbe,card_pos'],
            'amount_collected' => ['nullable', 'numeric', 'min:0'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($booking, $validated, $user) {
            $booking->update([
                'room_number' => $validated['room_number'],
                'guest_id_number' => $validated['guest_id_number'],
                'checked_in_at' => now(),
                'status' => 'checked_in',
            ]);

            // If payment was collected at check-in desk
            if (! empty($validated['amount_collected']) && (float) $validated['amount_collected'] > 0) {
                $amount = (float) $validated['amount_collected'];
                $method = $validated['payment_method'] ?? 'cash';

                $payment = Payment::create([
                    'payable_type' => Booking::class,
                    'payable_id' => $booking->id,
                    'property_id' => $booking->property_id,
                    'user_id' => $booking->user_id,
                    'payer_name' => $booking->guest_name ?? $booking->user?->name,
                    'payer_email' => $booking->guest_email ?? $booking->user?->email,
                    'payer_phone' => $booking->guest_phone ?? $booking->user?->phone,
                    'payment_type' => 'booking_deposit',
                    'payment_method' => $method,
                    'currency' => $booking->currency ?? 'ETB',
                    'amount' => $amount,
                    'platform_fee' => 0,
                    // No platform fee is charged on a desk collection, so no
                    // VAT arises on it (VAT is only ever charged on the fee).
                    'tax_amount' => 0.00,
                    'net_amount' => $amount,
                    'status' => 'confirmed',
                    'verified_at' => now(),
                    'verified_by' => $user->id,
                    'notes' => "Front Desk check-in collection (Room {$validated['room_number']})",
                    'bank_transaction_reference' => $validated['payment_reference'] ?? null,
                ]);

                app(LedgerService::class)->post('payment:'.$payment->id, [
                    [
                        'payment_id' => $payment->id,
                        'entry_type' => 'debit',
                        'category' => 'cash',
                        'amount' => $amount,
                        'currency' => $booking->currency ?? 'ETB',
                        'description' => "Front Desk cash received Room {$validated['room_number']} — {$booking->booking_reference}",
                        'reference' => $payment->transaction_number,
                        'user_id' => $user->id,
                    ],
                    [
                        'payment_id' => $payment->id,
                        'entry_type' => 'credit',
                        'category' => 'booking_revenue',
                        'amount' => $amount,
                        'currency' => $booking->currency ?? 'ETB',
                        'description' => "Front Desk Check-in Room {$validated['room_number']} — {$booking->booking_reference}",
                        'reference' => $payment->transaction_number,
                        'user_id' => $user->id,
                    ],
                ]);

                $booking->update([
                    'payment_status' => 'paid',
                ]);
            }
        });

        ActivityLog::record(
            'guest_checked_in',
            "Receptionist {$user->name} checked in guest {$booking->guest_name} to Room {$validated['room_number']} ({$booking->booking_reference}).",
            $booking
        );

        if ($booking->user) {
            $booking->user->notify(new GuestBookingNotification($booking, 'checked_in'));
        }

        return back()->with('success', "Guest checked in successfully! Assigned to Room {$validated['room_number']}.");
    }

    /**
     * Process Guest Check-Out at the Front Desk.
     */
    public function checkOut(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessReceptionDesk()) {
            abort(403, 'Unauthorized.');
        }

        $booking->update([
            'checked_out_at' => now(),
            'status' => 'checked_out',
        ]);

        ActivityLog::record(
            'guest_checked_out',
            "Receptionist {$user->name} processed check-out for Room {$booking->room_number} ({$booking->booking_reference}).",
            $booking
        );

        if ($booking->user) {
            $booking->user->notify(new GuestBookingNotification($booking, 'checked_out'));
        }

        return back()->with('success', "Guest check-out completed for Room {$booking->room_number}. Folio closed.");
    }

    /**
     * Quick Walk-in Guest Booking at Front Desk.
     */
    public function walkIn(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessReceptionDesk()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:50'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'guest_id_number' => ['required', 'string', 'max:100'],
            'room_number' => ['required', 'string', 'max:50'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'nightly_price' => ['required', 'numeric', 'min:0'],
            'guests_count' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'in:cash,telebirr,cbe,card_pos'],
            'check_in_now' => ['nullable', 'boolean'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $checkInDate = Carbon::parse($validated['check_in_date']);
        $checkOutDate = Carbon::parse($validated['check_out_date']);
        $nights = $checkInDate->diffInDays($checkOutDate) ?: 1;
        $nightlyPrice = (float) $validated['nightly_price'];
        $totalAmount = round($nights * $nightlyPrice, 2);

        // Find or create walk-in guest user account
        $guestEmail = $validated['guest_email'] ?: 'walkin_'.time().'_'.Str::random(4).'@gtmrealestate.com';
        $guestUser = User::firstOrCreate(
            ['email' => $guestEmail],
            [
                'name' => $validated['guest_name'],
                'phone' => $validated['guest_phone'],
                'password' => Hash::make(Str::random(16)),
                'role' => 'user',
            ]
        );

        DB::transaction(function () use ($validated, $guestUser, $nights, $nightlyPrice, $totalAmount, $user, $request) {
            $shouldCheckInNow = $request->boolean('check_in_now', true);

            $booking = Booking::create([
                'user_id' => $guestUser->id,
                'property_id' => $validated['property_id'],
                'room_number' => $validated['room_number'],
                'guest_id_number' => $validated['guest_id_number'],
                'guest_name' => $validated['guest_name'],
                'guest_phone' => $validated['guest_phone'],
                'guest_email' => $guestUser->email,
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
                'total_nights' => $nights,
                'guests_count' => $validated['guests_count'],
                'nightly_price' => $nightlyPrice,
                'accommodation_total' => $totalAmount,
                'total_amount' => $totalAmount,
                'currency' => 'ETB',
                'payment_status' => 'paid',
                'status' => $shouldCheckInNow ? 'checked_in' : 'confirmed',
                'checked_in_at' => $shouldCheckInNow ? now() : null,
            ]);

            // Record Payment
            $payment = Payment::create([
                'payable_type' => Booking::class,
                'payable_id' => $booking->id,
                'property_id' => $booking->property_id,
                'user_id' => $guestUser->id,
                'payer_name' => $validated['guest_name'],
                'payer_email' => $guestUser->email,
                'payer_phone' => $validated['guest_phone'],
                'payment_type' => 'booking_deposit',
                'payment_method' => $validated['payment_method'],
                'currency' => 'ETB',
                'amount' => $totalAmount,
                'platform_fee' => 0,
                // No platform fee is charged on a desk collection, so no VAT
                // arises on it (VAT is only ever charged on the fee).
                'tax_amount' => 0.00,
                'net_amount' => $totalAmount,
                'status' => 'confirmed',
                'verified_at' => now(),
                'verified_by' => $user->id,
                'notes' => "Walk-in Guest Reservation (Room {$validated['room_number']})",
                'bank_transaction_reference' => $validated['payment_reference'] ?? null,
            ]);

            app(LedgerService::class)->post('payment:'.$payment->id, [
                [
                    'payment_id' => $payment->id,
                    'entry_type' => 'debit',
                    'category' => 'cash',
                    'amount' => $totalAmount,
                    'currency' => 'ETB',
                    'description' => "Front Desk Walk-In cash received Room {$validated['room_number']} — {$booking->booking_reference}",
                    'reference' => $payment->transaction_number,
                    'user_id' => $user->id,
                ],
                [
                    'payment_id' => $payment->id,
                    'entry_type' => 'credit',
                    'category' => 'booking_revenue',
                    'amount' => $totalAmount,
                    'currency' => 'ETB',
                    'description' => "Front Desk Walk-In Booking Room {$validated['room_number']} — {$booking->booking_reference}",
                    'reference' => $payment->transaction_number,
                    'user_id' => $user->id,
                ],
            ]);
        });

        return back()->with('success', "Walk-in reservation successfully registered and checked in for Room {$validated['room_number']}!");
    }

    /**
     * Create Guest Service / Concierge Request.
     */
    public function storeServiceRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'service_type' => ['required', 'in:room_cleaning,extra_towels,airport_transfer,wake_up_call,maintenance,inquiry,other'],
            'priority' => ['required', 'in:normal,urgent'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        $booking = Booking::with('user')->findOrFail($validated['booking_id']);
        $user = $request->user();
        $isGuestOwner = $user && ($booking->user_id === $user->id || $booking->guest_email === $user->email);

        if (! $user || (! $user->canAccessReceptionDesk() && ! $isGuestOwner)) {
            abort(403, 'Unauthorized.');
        }

        GuestServiceRequest::create([
            'booking_id' => $booking->id,
            'property_id' => $booking->property_id,
            'room_number' => $booking->room_number,
            'guest_name' => $booking->guest_name ?? $booking->user?->name ?? 'Guest',
            'service_type' => $validated['service_type'],
            'priority' => $validated['priority'],
            'description' => $validated['description'],
            'status' => 'pending',
            'handled_by' => $user->canAccessReceptionDesk() ? $user->id : null,
        ]);

        return back()->with('success', 'Guest service request created and dispatched to front desk.');
    }

    /**
     * Update Guest Service Request status and notes.
     */
    public function updateServiceRequest(Request $request, GuestServiceRequest $serviceRequest): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessReceptionDesk()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
            'response_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $serviceRequest->update([
            'status' => $validated['status'],
            'response_notes' => $validated['response_notes'] ?? $serviceRequest->response_notes,
            'handled_by' => $user->id,
        ]);

        return back()->with('success', "Service request updated to status: {$serviceRequest->status}.");
    }

    /**
     * Submit Front Desk Shift Reconciliation to Finance.
     */
    public function storeShift(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessReceptionDesk()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'property_id' => ['nullable', 'exists:properties,id'],
            'shift_date' => ['required', 'date'],
            'shift_type' => ['required', 'in:morning,day,night'],
            'cash_collected' => ['required', 'numeric', 'min:0'],
            'telebirr_collected' => ['required', 'numeric', 'min:0'],
            'cbe_collected' => ['required', 'numeric', 'min:0'],
            'card_pos_collected' => ['required', 'numeric', 'min:0'],
            'checkins_count' => ['required', 'integer', 'min:0'],
            'checkouts_count' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cash = (float) $validated['cash_collected'];
        $telebirr = (float) $validated['telebirr_collected'];
        $cbe = (float) $validated['cbe_collected'];
        $pos = (float) $validated['card_pos_collected'];
        $total = round($cash + $telebirr + $cbe + $pos, 2);

        $shift = FrontDeskShift::create([
            'receptionist_user_id' => $user->id,
            'property_id' => $validated['property_id'] ?: $user->assigned_property_id,
            'shift_date' => $validated['shift_date'],
            'shift_type' => $validated['shift_type'],
            'cash_collected' => $cash,
            'telebirr_collected' => $telebirr,
            'cbe_collected' => $cbe,
            'card_pos_collected' => $pos,
            'total_collected' => $total,
            'checkins_count' => $validated['checkins_count'],
            'checkouts_count' => $validated['checkouts_count'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'submitted',
        ]);

        return back()->with('success', "Shift reconciliation {$shift->shift_reference} submitted to Finance Desk for audit!");
    }

    /**
     * Printable Official Ethiopian Hotel Stay Folio & Tax Invoice.
     */
    public function folio(Booking $booking): View
    {
        $user = auth()->user();
        $isGuestOwner = $user && ($booking->user_id === $user->id || $booking->guest_email === $user->email);

        if (! $user || (! $user->canAccessReceptionDesk() && ! $isGuestOwner)) {
            abort(403, 'Unauthorized.');
        }

        $booking->load(['property', 'user', 'payments']);

        $siteBrand = [
            'name' => SiteSetting::get('site_name', 'GTM Real Estate'),
            'logoUrl' => SiteSetting::get('logo_path') ? asset('storage/'.SiteSetting::get('logo_path')) : asset('logo.png'),
        ];

        return view('receptionist.folio', compact('booking', 'siteBrand'));
    }

    /**
     * Reply to guest/client inquiry or mark as read.
     */
    public function replyToInquiry(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessReceptionDesk()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:unread,read,responded,archived'],
        ]);

        $inquiry->update([
            'status' => $validated['status'],
            'read_at' => $inquiry->read_at ?: now(),
        ]);

        return back()->with('success', 'Guest message status updated.');
    }

    /**
     * Update maintenance ticket status from Front Desk.
     */
    public function updateMaintenanceStatus(Request $request, MaintenanceTicket $ticket): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessReceptionDesk()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:open,assigned,in_progress,completed,closed'],
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data = ['status' => $validated['status']];
        if (! empty($validated['resolution_notes'])) {
            $data['resolution_notes'] = $validated['resolution_notes'];
        }
        if (in_array($validated['status'], ['completed', 'closed'], true)) {
            $data['resolved_at'] = now();
        }

        $ticket->update($data);

        return back()->with('success', "Maintenance ticket {$ticket->ticket_number} updated to {$ticket->status}.");
    }

    /**
     * Create a short-stay listing with availability, pricing, and house rules.
     */
    public function storeStayListing(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessReceptionDesk()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:1'], // Nightly rate in ETB
            'cleaning_fee' => ['nullable', 'numeric', 'min:0'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
            'bedrooms' => ['required', 'integer', 'min:1'],
            'bathrooms' => ['required', 'integer', 'min:1'],
            'area' => ['nullable', 'numeric', 'min:1'],
            'property_category' => ['required', 'string', 'in:guest_house,hotel,serviced_apartment,apartment,villa'],
            'house_rules' => ['nullable', 'string', 'max:3000'],
            'check_in_time' => ['nullable', 'string', 'max:20'],
            'check_out_time' => ['nullable', 'string', 'max:20'],
            'is_available' => ['nullable', 'boolean'],
            'available_from' => ['nullable', 'date'],
            'available_to' => ['nullable', 'date', 'after_or_equal:available_from'],
            'minimum_stay' => ['nullable', 'integer', 'min:1'],
            'maximum_stay' => ['nullable', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'cover_preset' => ['nullable', 'string', 'in:hero-skyline,luxury-towers,central-plaza,panoramic-park,retail-boulevard'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('properties', 'public');
        } elseif ($request->hasFile('cover_image')) {
            $imagePath = $request->file('cover_image')->store('properties', 'public');
        } elseif ($request->filled('cover_preset')) {
            $imagePath = 'images/luxury/'.$request->input('cover_preset').'.jpg';
        }

        $slug = Str::slug($validated['title']).'-'.Str::lower(Str::random(5));

        $property = Property::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'city' => $validated['city'],
            'address' => $validated['address'],
            'price' => (float) $validated['price'],
            'cleaning_fee' => (float) ($validated['cleaning_fee'] ?? 0),
            'service_fee' => $request->filled('service_fee') ? (float) $validated['service_fee'] : null,
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'area' => (int) ($validated['area'] ?? 50),
            'type' => 'stay',
            'property_category' => $validated['property_category'],
            'house_rules' => $validated['house_rules'] ?? null,
            'check_in_time' => $validated['check_in_time'] ?: '14:00',
            'check_out_time' => $validated['check_out_time'] ?: '11:00',
            'is_available' => $request->boolean('is_available', true),
            'available_from' => $validated['available_from'] ?? null,
            'available_to' => $validated['available_to'] ?? null,
            'status' => 'published',
            'is_active' => true,
            'host_id' => $user->id,
            'image_path' => $imagePath,
        ]);

        if ($imagePath) {
            $property->images()->create([
                'image_path' => $imagePath,
                'sort_order' => 1,
            ]);
        }

        if ($request->hasFile('images')) {
            $sortOrder = $imagePath ? 2 : 1;
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('properties', 'public');
                    $property->images()->create([
                        'image_path' => $path,
                        'sort_order' => $sortOrder++,
                    ]);
                    if (! $property->image_path) {
                        $property->update(['image_path' => $path]);
                    }
                }
            }
        }

        // Ensure short stay listing exists and matches all parameters
        $stayListing = $property->stayListing();
        if ($stayListing) {
            $stayListing->update([
                'title' => $property->title,
                'description' => $property->description,
                'price' => $property->price,
                'cleaning_fee' => $property->cleaning_fee,
                'service_fee' => $property->service_fee,
                'house_rules' => $property->house_rules,
                'available_from' => $property->available_from,
                'available_to' => $property->available_to,
                'minimum_stay' => $validated['minimum_stay'] ?? 1,
                'maximum_stay' => $validated['maximum_stay'] ?? 30,
            ]);
        } else {
            $property->listings()->create([
                'listing_type' => 'stay',
                'title' => $property->title,
                'description' => $property->description,
                'price' => $property->price,
                'currency' => 'ETB',
                'pricing_period' => 'nightly',
                'cleaning_fee' => $property->cleaning_fee,
                'service_fee' => $property->service_fee,
                'house_rules' => $property->house_rules,
                'available_from' => $property->available_from,
                'available_to' => $property->available_to,
                'minimum_stay' => $validated['minimum_stay'] ?? 1,
                'maximum_stay' => $validated['maximum_stay'] ?? 30,
                'status' => 'published',
                'is_featured' => true,
            ]);
        }

        // Auto assign to receptionist if they don't have one
        if (! $user->isAdmin() && empty($user->assigned_property_id)) {
            $user->update(['assigned_property_id' => $property->id]);
        }

        return redirect()->route('receptionist.dashboard', ['property_id' => $property->id])
            ->with('success', "Short-stay listing '{$property->title}' created successfully with pricing, availability, and house rules!");
    }

    /**
     * Update an existing short-stay listing (pricing, rules, availability, check-in time).
     */
    public function updateStayListing(Request $request, Property $property): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessReceptionDesk()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:1'],
            'cleaning_fee' => ['nullable', 'numeric', 'min:0'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
            'house_rules' => ['nullable', 'string', 'max:3000'],
            'check_in_time' => ['nullable', 'string', 'max:20'],
            'check_out_time' => ['nullable', 'string', 'max:20'],
            'is_available' => ['nullable', 'boolean'],
            'available_from' => ['nullable', 'date'],
            'available_to' => ['nullable', 'date'],
            'minimum_stay' => ['nullable', 'integer', 'min:1'],
            'maximum_stay' => ['nullable', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
        ]);

        $property->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => (float) $validated['price'],
            'cleaning_fee' => (float) ($validated['cleaning_fee'] ?? 0),
            'service_fee' => $request->filled('service_fee') ? (float) $validated['service_fee'] : null,
            'house_rules' => $validated['house_rules'] ?? null,
            'check_in_time' => $validated['check_in_time'] ?: '14:00',
            'check_out_time' => $validated['check_out_time'] ?: '11:00',
            'is_available' => $request->boolean('is_available', true),
            'available_from' => $validated['available_from'] ?? null,
            'available_to' => $validated['available_to'] ?? null,
        ]);

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('properties', 'public');
            $property->update(['image_path' => $newPath]);
            $property->images()->create([
                'image_path' => $newPath,
                'sort_order' => (int) $property->images()->max('sort_order') + 1,
            ]);
        } elseif ($request->hasFile('cover_image')) {
            $newPath = $request->file('cover_image')->store('properties', 'public');
            $property->update(['image_path' => $newPath]);
            $property->images()->create([
                'image_path' => $newPath,
                'sort_order' => (int) $property->images()->max('sort_order') + 1,
            ]);
        }

        if ($request->hasFile('images')) {
            $maxOrder = (int) $property->images()->max('sort_order');
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('properties', 'public');
                    $property->images()->create([
                        'image_path' => $path,
                        'sort_order' => ++$maxOrder,
                    ]);
                }
            }
        }

        $stayListing = $property->stayListing();
        if ($stayListing) {
            $stayListing->update([
                'title' => $property->title,
                'description' => $property->description,
                'price' => $property->price,
                'cleaning_fee' => $property->cleaning_fee,
                'service_fee' => $property->service_fee,
                'house_rules' => $property->house_rules,
                'available_from' => $property->available_from,
                'available_to' => $property->available_to,
                'minimum_stay' => $validated['minimum_stay'] ?? 1,
                'maximum_stay' => $validated['maximum_stay'] ?? 30,
            ]);
        }

        return back()->with('success', "Short-stay listing '{$property->title}' updated successfully!");
    }

    /**
     * Define availability toggle and date range for a stay property.
     */
    public function updateAvailability(Request $request, Property $property): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessReceptionDesk()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'is_available' => ['required', 'boolean'],
            'available_from' => ['nullable', 'date'],
            'available_to' => ['nullable', 'date'],
        ]);

        $property->update([
            'is_available' => (bool) $validated['is_available'],
            'available_from' => $validated['available_from'] ?? null,
            'available_to' => $validated['available_to'] ?? null,
        ]);

        $stayListing = $property->stayListing();
        if ($stayListing) {
            $stayListing->update([
                'available_from' => $property->available_from,
                'available_to' => $property->available_to,
            ]);
        }

        $statusLabel = $property->is_available ? 'Available' : 'Blocked / Unavailable';

        return back()->with('success', "Availability for '{$property->title}' updated to {$statusLabel}.");
    }

    /**
     * Accept a pending booking reservation.
     */
    public function acceptBooking(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessReceptionDesk()) {
            abort(403, 'Unauthorized.');
        }

        $booking->update([
            'status' => 'confirmed',
            'host_notes' => $request->input('host_notes', 'Booking accepted by front desk host.'),
        ]);

        return back()->with('success', "Booking {$booking->booking_reference} has been accepted and confirmed!");
    }

    /**
     * Manage cleaning: create housekeeping / cleaning request.
     */
    public function storeCleaningRequest(Request $request, Property $property): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessReceptionDesk()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:1000'],
            'priority' => ['required', 'in:normal,urgent'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
        ]);

        // Link to booking if provided, otherwise latest active booking for the room/property
        $bookingId = $validated['booking_id'] ?? null;
        if (! $bookingId) {
            $booking = Booking::query()
                ->where('property_id', $property->id)
                ->where('room_number', $validated['room_number'])
                ->latest()
                ->first();
            $bookingId = $booking?->id;
        }

        GuestServiceRequest::create([
            'booking_id' => $bookingId,
            'property_id' => $property->id,
            'room_number' => $validated['room_number'],
            'guest_name' => 'Host Housekeeping',
            'service_type' => 'room_cleaning',
            'priority' => $validated['priority'],
            'description' => $validated['description'],
            'status' => 'pending',
            'handled_by' => $user->id,
        ]);

        return back()->with('success', "Housekeeping / cleaning request for Room {$validated['room_number']} scheduled successfully!");
    }

    /**
     * Reply to guest reviews from the host / front desk.
     */
    public function replyToReview(Request $request, PropertyReview $review): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessReceptionDesk()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'host_reply' => ['required', 'string', 'max:2000'],
        ]);

        $review->update([
            'host_reply' => $validated['host_reply'],
            'host_replied_at' => now(),
        ]);

        return back()->with('success', 'Host response posted to guest review!');
    }
}
