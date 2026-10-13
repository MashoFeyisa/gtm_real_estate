<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use App\Notifications\GuestBookingNotification;
use App\Notifications\PaymentStatusNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class StayBookingController extends Controller
{
    /**
     * Display public short-stay properties and vacation accommodations.
     */
    public function index(Request $request): View
    {
        $query = Property::query()
            ->publicVisible()
            ->with(['agent', 'listings']);

        // Check if there are properties with explicit 'stay' listings, or apartments/villas
        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->input('city').'%');
        }

        if ($request->filled('guests')) {
            $guests = (int) $request->input('guests');
            $query->where('bedrooms', '>=', max(1, (int) ceil($guests / 2)));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        $properties = $query->latest()->paginate(12)->withQueryString();

        return view('stays.index', compact('properties'));
    }

    /**
     * Calculate stay pricing and check date availability in real-time.
     */
    public function calculate(Request $request, Property $property): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json([
                'auth_required' => true,
                'message' => 'Sign in before checking stay availability.',
                'login_url' => route('login'),
                'register_url' => route('register'),
            ], 401);
        }

        $validated = $request->validate([
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests' => ['nullable', 'integer', 'min:1'],
        ]);

        $checkIn = Carbon::parse($validated['check_in'])->startOfDay();
        $checkOut = Carbon::parse($validated['check_out'])->startOfDay();
        $nights = max(1, $checkIn->diffInDays($checkOut));

        $isAvailable = $property->isAvailableForDates(
            $checkIn->format('Y-m-d'),
            $checkOut->format('Y-m-d')
        );

        $nightlyPrice = $property->getNightlyRate();
        $cleaningFee = $property->getCleaningFee();
        $accommodationTotal = round($nights * $nightlyPrice, 2);
        $customServiceFee = $property->getCustomServiceFee();
        $serviceFee = ! is_null($customServiceFee) ? round($customServiceFee, 2) : round($accommodationTotal * 0.05, 2);
        $totalAmount = round($accommodationTotal + $cleaningFee + $serviceFee, 2);

        return response()->json([
            'available' => $isAvailable,
            'nights' => $nights,
            'nightly_price' => $nightlyPrice,
            'formatted_nightly_price' => 'ETB '.number_format($nightlyPrice),
            'accommodation_total' => $accommodationTotal,
            'formatted_accommodation_total' => 'ETB '.number_format($accommodationTotal),
            'cleaning_fee' => $cleaningFee,
            'formatted_cleaning_fee' => 'ETB '.number_format($cleaningFee),
            'service_fee' => $serviceFee,
            'formatted_service_fee' => 'ETB '.number_format($serviceFee),
            'total_amount' => $totalAmount,
            'formatted_total' => 'ETB '.number_format($totalAmount),
            'currency' => 'ETB',
            'message' => $isAvailable ? 'Dates are available for reservation!' : 'Selected dates are booked. Please choose alternative dates.',
        ]);
    }

    /**
     * Reserve / book a short-stay accommodation.
     */
    public function book(Request $request, Property $property): RedirectResponse
    {
        if (! auth()->check()) {
            return $this->redirectGuestToSignup(
                $request,
                route('properties.show', $property->slug),
                'Register / Login to continue: Create a client account or sign in before reserving this stay.'
            );
        }

        $validated = $request->validate([
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:50'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'guests_count' => ['nullable', 'integer', 'min:1'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
        ]);

        $checkIn = Carbon::parse($validated['check_in_date'])->startOfDay();
        $checkOut = Carbon::parse($validated['check_out_date'])->startOfDay();
        $nights = max(1, $checkIn->diffInDays($checkOut));

        // Concurrency / Overlap Guard
        if (! $property->isAvailableForDates($checkIn->format('Y-m-d'), $checkOut->format('Y-m-d'))) {
            return back()
                ->withInput()
                ->withErrors(['check_in_date' => 'Selected dates are no longer available. Please choose another date range.']);
        }

        $stayListing = $property->stayListing();
        $nightlyPrice = $property->getNightlyRate();
        $cleaningFee = $property->getCleaningFee();
        $accommodationTotal = round($nights * $nightlyPrice, 2);
        $customServiceFee = $property->getCustomServiceFee();
        $serviceFee = ! is_null($customServiceFee) ? round($customServiceFee, 2) : round($accommodationTotal * 0.05, 2);
        $totalAmount = round($accommodationTotal + $cleaningFee + $serviceFee, 2);

        $booking = Booking::create([
            'property_id' => $property->id,
            'listing_id' => $stayListing?->id,
            'user_id' => auth()->id(),
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'guest_phone' => $validated['guest_phone'],
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
            'total_nights' => $nights,
            'guests_count' => (int) ($validated['guests_count'] ?? 1),
            'nightly_price' => $nightlyPrice,
            'accommodation_total' => $accommodationTotal,
            'cleaning_fee' => $cleaningFee,
            'service_fee' => $serviceFee,
            'security_deposit' => $stayListing ? (float) $stayListing->deposit_amount : 0,
            'total_amount' => $totalAmount,
            'currency' => 'ETB',
            // The reservation is only confirmed once the finance desk clears payment.
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'special_requests' => $validated['special_requests'] ?? null,
        ]);

        ActivityLog::record(
            'stay_reservation_created',
            "Guest {$validated['guest_name']} reserved stay ({$booking->booking_reference}) on \"{$property->title}\".",
            $booking
        );

        // Notify Receptionists and Assigned Host
        $receptionStaff = User::whereIn('role', ['receptionist', 'admin'])->get();
        foreach ($receptionStaff as $staff) {
            $staff->notify(new GuestBookingNotification($booking, 'created'));
        }

        return redirect()->route('properties.show', $property->slug)
            ->with('stay_booking_success', [
                'id' => $booking->id,
                'reference' => $booking->booking_reference,
                'nights' => $booking->total_nights,
                'check_in' => $checkIn->format('M d, Y'),
                'check_out' => $checkOut->format('M d, Y'),
                'total' => $booking->formatted_total,
                'amount' => (float) $booking->total_amount,
                'guest' => $booking->guest_name,
            ]);
    }

    /**
     * Submit payment for a short-stay reservation (SRS §78–§83).
     *
     * The amount is always derived from the reservation, never from the request,
     * and instant channels clear immediately while bank slips await the finance desk.
     */
    public function pay(Request $request, Booking $booking): RedirectResponse
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        $isOwner = (int) $booking->user_id === (int) $user->id || $booking->guest_email === $user->email;
        if (! $isOwner) {
            abort(403, 'You are not authorized to pay for this reservation.');
        }

        if (in_array($booking->status, ['cancelled', 'completed'], true)) {
            return back()->with('error', "Reservation {$booking->booking_reference} can no longer be paid for.");
        }

        if ($booking->isPaid()) {
            return back()->with('error', "Reservation {$booking->booking_reference} has already been paid.");
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:telebirr,cbe_birr,bank_transfer,cash'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'bank_transaction_reference' => ['nullable', 'string', 'max:100'],
            'deposit_slip' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:12288'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $slipPath = $request->hasFile('deposit_slip')
            ? $request->file('deposit_slip')->store('deposit_slips', 'public')
            : null;

        $isInstant = in_array($validated['payment_method'], ['telebirr'], true);
        $amount = (float) $booking->total_amount;
        $platformFee = (float) $booking->service_fee;

        $payment = Payment::create([
            'payable_type' => Booking::class,
            'payable_id' => $booking->id,
            'property_id' => $booking->property_id,
            'user_id' => $user->id,
            'payer_name' => $booking->guest_name,
            'payer_email' => $booking->guest_email,
            'payer_phone' => $booking->guest_phone,
            'payment_type' => 'stay_booking',
            'payment_method' => $validated['payment_method'],
            'bank_name' => $validated['bank_name'] ?? ($validated['payment_method'] === 'cbe_birr' ? 'Commercial Bank of Ethiopia' : null),
            'bank_transaction_reference' => $validated['bank_transaction_reference'] ?? null,
            'deposit_slip_path' => $slipPath,
            'currency' => $booking->currency ?? 'ETB',
            'amount' => $amount,
            'platform_fee' => $platformFee,
            'tax_amount' => round($platformFee * 0.15, 2),
            'net_amount' => round($amount - $platformFee, 2),
            'status' => $isInstant ? 'completed' : 'pending',
            'notes' => $validated['notes'] ?? "Short-stay payment for {$booking->booking_reference}",
            'verified_at' => $isInstant ? now() : null,
            'verified_by' => $isInstant ? $user->id : null,
        ]);

        if ($payment->status === 'completed') {
            $payment->settle();
            $user->notify(new PaymentStatusNotification($payment, 'completed'));

            return redirect()->route('payments.receipt', $payment->transaction_number)
                ->with('success', "Payment {$payment->transaction_number} received. Reservation {$booking->booking_reference} is confirmed.");
        }

        $booking->markAwaitingPaymentConfirmation();

        return redirect()->route('payments.receipt', $payment->transaction_number)
            ->with('success', "Payment {$payment->transaction_number} submitted. The finance desk will confirm it and issue your receipt.");
    }

    /**
     * Admin/Host update booking status (confirm, check-in, complete, cancel).
     */
    public function updateStatus(Request $request, Booking $booking): RedirectResponse|JsonResponse
    {
        $this->authorizeAdminOrAgent($booking);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,checked_in,checked_out,completed,cancelled'],
            'host_notes' => ['nullable', 'string'],
        ]);

        $booking->update([
            'status' => $validated['status'],
            'host_notes' => $validated['host_notes'] ?? $booking->host_notes,
        ]);

        $message = "Booking {$booking->booking_reference} updated to {$booking->status}.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'section' => 'stay-bookings',
            ]);
        }

        return redirect()->to(route('dashboard').'#stay-bookings')->with('success', $message);
    }

    /**
     * Delete a booking from the stay reservation list.
     */
    public function destroy(Booking $booking): RedirectResponse|JsonResponse
    {
        $this->authorizeBookingDeletion($booking);

        $booking->delete();

        $message = "Booking {$booking->booking_reference} deleted.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'section' => 'stay-bookings',
            ]);
        }

        return redirect()->to(route('dashboard').'#stay-bookings')->with('success', $message);
    }

    protected function authorizeBookingDeletion(Booking $booking): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin()
            || (int) $booking->user_id === (int) $user->id
            || $booking->guest_email === $user->email) {
            return;
        }

        $this->authorizeAdminOrAgent($booking);
    }

    protected function authorizeAdminOrAgent(Booking $booking): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return;
        }

        if ($user->agentProfile && $booking->property->agent_id === $user->agentProfile->id) {
            return;
        }

        if ($user->role === 'property_owner' && $user->hasPermission('bookings.update') && $booking->user_id === $user->id) {
            return;
        }

        if ($user->role === 'property_manager' && ($user->hasPermission('bookings.update') || $user->hasPermission('bookings.manage') || $user->hasPermission('bookings.cancel'))) {
            return;
        }

        if ($user->role === 'host' && $booking->property->host_id === $user->id) {
            return;
        }

        abort(403, 'Unauthorized booking management.');
    }
}
