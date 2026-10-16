<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Commission;
use App\Models\Lease;
use App\Models\OfferNegotiation;
use App\Models\Order;
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\PropertyViewing;
use App\Models\User;
use App\Notifications\CommissionGeneratedNotification;
use App\Notifications\OfferStatusNotification;
use App\Notifications\ViewingStatusNotification;
use App\Services\AgreementPdfService;
use App\Services\FinanceSettings;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ViewingOfferController extends Controller
{
    /**
     * Schedule a property viewing appointment (SRS §71, §72).
     */
    public function scheduleViewing(Request $request, Property $property): RedirectResponse
    {
        $validated = $request->validate([
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_email' => ['required', 'email', 'max:255'],
            'buyer_phone' => ['required', 'string', 'max:50'],
            'viewing_date' => ['required', 'date', 'after_or_equal:today'],
            'viewing_time' => ['required', 'in:morning_09_11,afternoon_14_16,evening_17_19'],
            'viewing_type' => ['required', 'in:in_person,virtual_tour'],
            'buyer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $viewing = PropertyViewing::create([
            'property_id' => $property->id,
            'agent_id' => $property->agent_id,
            'user_id' => auth()->id(),
            'buyer_name' => $validated['buyer_name'],
            'buyer_email' => $validated['buyer_email'],
            'buyer_phone' => $validated['buyer_phone'],
            'viewing_date' => $validated['viewing_date'],
            'viewing_time' => $validated['viewing_time'],
            'viewing_type' => $validated['viewing_type'],
            'status' => 'requested',
            'buyer_notes' => $validated['buyer_notes'] ?? null,
        ]);

        ActivityLog::record(
            'viewing_scheduled',
            "Client {$validated['buyer_name']} scheduled viewing for property \"{$property->title}\".",
            $viewing
        );

        // Notify listing agent
        $agentUser = $property->agent?->user;
        if ($agentUser) {
            $agentUser->notify(new ViewingStatusNotification($viewing, 'requested'));
        }

        return back()->with('viewing_scheduled_success', [
            'reference' => $viewing->viewing_reference,
            'property_title' => $property->title,
            'date' => Carbon::parse($viewing->viewing_date)->format('M d, Y'),
            'time' => $viewing->formatted_time_slot,
            'type' => ucfirst(str_replace('_', ' ', $viewing->viewing_type)),
            'buyer_name' => $viewing->buyer_name,
        ]);
    }

    /**
     * Update property viewing appointment status (SRS §72: Requested, Confirmed, Rescheduled, Completed, Cancelled).
     */
    public function updateViewingStatus(Request $request, PropertyViewing $viewing): RedirectResponse
    {
        $this->authorizeViewingAccess($viewing);

        $validated = $request->validate([
            'status' => ['required', 'in:requested,confirmed,rescheduled,completed,cancelled,no_show'],
            'agent_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $viewing->update($validated);

        $buyerUser = $viewing->user ?? User::where('email', $viewing->buyer_email)->first();
        if ($buyerUser) {
            $buyerUser->notify(new ViewingStatusNotification($viewing, $validated['status']));
        }

        return back()->with('success', "Viewing {$viewing->viewing_reference} marked as {$validated['status']}.");
    }

    /**
     * Submit a formal purchase or rental offer with financial terms (SRS §76).
     */
    public function submitOffer(Request $request, Property $property): RedirectResponse
    {
        if (! auth()->check()) {
            return $this->redirectGuestToSignup(
                $request,
                route('properties.show', $property->slug),
                'Register / Login to continue: Create an account or sign in to submit a formal offer.'
            );
        }

        $validated = $request->validate([
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_email' => ['required', 'email', 'max:255'],
            'buyer_phone' => ['required', 'string', 'max:50'],
            'offer_type' => ['nullable', 'in:purchase,rental'],
            'offered_price' => ['required', 'numeric', 'min:1'],
            'financing_type' => ['required', 'in:cash,bank_loan,diaspora_remittance,installments'],
            'deposit_proposed' => ['nullable', 'numeric', 'min:0'],
            'earnest_deposit' => ['nullable', 'numeric', 'min:0'],
            'valid_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'validity_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'message' => ['nullable', 'string', 'max:1000'],
            'buyer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $offerType = $validated['offer_type'] ?? ($property->type === 'rent' ? 'rental' : 'purchase');
        $validDays = (int) ($validated['valid_days'] ?? $validated['validity_days'] ?? 7);
        $deposit = isset($validated['deposit_proposed']) || isset($validated['earnest_deposit'])
            ? (float) ($validated['deposit_proposed'] ?? $validated['earnest_deposit'])
            : null;
        $notes = $validated['message'] ?? $validated['buyer_notes'] ?? null;
        $validUntil = Carbon::now()->addDays($validDays);

        $offer = PropertyOffer::create([
            'property_id' => $property->id,
            'agent_id' => $property->agent_id,
            'user_id' => auth()->id(),
            'buyer_name' => $validated['buyer_name'],
            'buyer_email' => $validated['buyer_email'],
            'buyer_phone' => $validated['buyer_phone'],
            'offer_type' => $offerType,
            'original_price' => $property->price,
            'offered_price' => $validated['offered_price'],
            'currency' => 'ETB',
            'financing_type' => $validated['financing_type'],
            'deposit_proposed' => $deposit,
            'valid_until' => $validUntil,
            'status' => 'submitted',
            'message' => $notes,
        ]);

        // Record initial round into negotiation audit trail (SRS §77)
        OfferNegotiation::create([
            'property_offer_id' => $offer->id,
            'sender_type' => 'buyer',
            'amount' => $validated['offered_price'],
            'currency' => 'ETB',
            'message' => $notes ?? 'Initial offer submitted.',
            'terms' => [
                'financing_type' => $validated['financing_type'],
                'deposit_proposed' => $deposit,
            ],
        ]);

        ActivityLog::record(
            'offer_submitted',
            "Client {$validated['buyer_name']} submitted formal offer of ETB ".number_format($validated['offered_price'])." on \"{$property->title}\".",
            $offer
        );

        // Notify listing agent
        $agentUser = $property->agent?->user;
        if ($agentUser) {
            $agentUser->notify(new OfferStatusNotification($offer, 'submitted'));
        }

        return back()->with('offer_submitted_success', [
            'reference' => $offer->offer_reference,
            'property_title' => $property->title,
            'original_price' => $offer->formatted_original_price,
            'offered_price' => $offer->formatted_offered_price,
            'financing_type' => ucfirst(str_replace('_', ' ', $offer->financing_type)),
            'difference' => $offer->difference_percentage,
            'buyer_name' => $offer->buyer_name,
        ]);
    }

    /**
     * Submit a counteroffer in an active negotiation (SRS §77).
     */
    public function counterOffer(Request $request, PropertyOffer $offer): RedirectResponse
    {
        $this->authorizeOfferAccess($offer);

        $validated = $request->validate([
            'counter_amount' => ['required', 'numeric', 'min:1'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $senderType = auth()->user()->isFinanceStaff() ? 'admin' : (auth()->user()->agentProfile ? 'seller_agent' : 'buyer');

        // Update the offer state
        $offer->update([
            'current_counter_amount' => $validated['counter_amount'],
            'latest_counter_by' => $senderType,
            'status' => 'countered',
        ]);

        // Record counteroffer in audit trail
        OfferNegotiation::create([
            'property_offer_id' => $offer->id,
            'sender_type' => $senderType,
            'amount' => $validated['counter_amount'],
            'currency' => 'ETB',
            'message' => $validated['message'],
        ]);

        // Dispatch notification to recipient
        $buyerUser = $offer->user ?? User::where('email', $offer->buyer_email)->first();
        $agentUser = $offer->agent?->user;

        if ($senderType === 'buyer' && $agentUser) {
            $agentUser->notify(new OfferStatusNotification($offer, 'countered'));
        } elseif ($senderType !== 'buyer' && $buyerUser) {
            $buyerUser->notify(new OfferStatusNotification($offer, 'countered'));
        }

        return back()->with('success', 'Counteroffer of ETB '.number_format($validated['counter_amount']).' submitted.');
    }

    /**
     * Accept a submitted offer or counteroffer (SRS §76).
     */
    public function acceptOffer(Request $request, PropertyOffer $offer): RedirectResponse
    {
        $this->authorizeOfferAccess($offer);

        $acceptedAmount = $offer->current_counter_amount ?? $offer->offered_price;

        $senderType = auth()->user()->isFinanceStaff() ? 'admin' : (auth()->user()->agentProfile ? 'seller_agent' : 'buyer');

        $offer->update([
            'status' => 'accepted',
        ]);

        OfferNegotiation::create([
            'property_offer_id' => $offer->id,
            'sender_type' => $senderType,
            'amount' => $acceptedAmount,
            'currency' => 'ETB',
            'message' => 'Offer officially accepted! Proceeding to agreement and closing documentation.',
        ]);

        $buyerUser = $offer->user ?? User::where('email', $offer->buyer_email)->first();
        if ($buyerUser) {
            $buyerUser->notify(new OfferStatusNotification($offer, 'accepted'));
        }

        return back()->with('success', "Offer {$offer->offer_reference} accepted successfully.");
    }

    /**
     * Convert an accepted offer into an official closing deal (Order or Lease) (SRS §76, §96–§105).
     */
    public function convertToDeal(Request $request, PropertyOffer $offer, AgreementPdfService $agreements): RedirectResponse
    {
        $this->authorizeOfferAccess($offer);

        if ($offer->status !== 'accepted') {
            return back()->with('error', 'Only accepted offers can be converted to contracts.');
        }

        $property = $offer->property;
        $acceptedAmount = (float) ($offer->current_counter_amount ?? $offer->offered_price);

        if ($offer->offer_type === 'rental') {
            // Check if lease already exists
            if ($offer->lease_id) {
                return redirect()->route('leases.agreement', $offer->lease_id);
            }

            $lease = Lease::create([
                'property_id' => $property->id,
                'agent_id' => $offer->agent_id,
                'landlord_user_id' => $property->agent?->user_id ?? auth()->id(),
                'tenant_user_id' => $offer->user_id,
                'tenant_name' => $offer->buyer_name,
                'tenant_email' => $offer->buyer_email,
                'tenant_phone' => $offer->buyer_phone,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addMonths(12)->format('Y-m-d'),
                'duration_months' => 12,
                'monthly_rent' => $acceptedAmount,
                'security_deposit' => (float) ($offer->deposit_proposed ?? ($acceptedAmount * 2)),
                'payment_frequency' => 'monthly',
                'status' => 'active',
                'terms_and_conditions' => $offer->message,
            ]);

            $lease->generateSchedules();

            try {
                $agreements->generateLease($lease);
            } catch (\Throwable $e) {
                report($e);
            }

            $offer->update([
                'lease_id' => $lease->id,
                'closing_status' => 'agreement_generated',
            ]);

            $property->update([
                'status' => 'rented',
                'is_active' => false,
            ]);

            if ($offer->agent_id) {
                $settings = app(FinanceSettings::class);
                $rate = $settings->commissionRateFor($offer->agent_id, $property->property_category);
                $commAmount = $settings->commissionFor($acceptedAmount, $rate);

                $commission = Commission::firstOrCreate([
                    'property_id' => $property->id,
                    'agent_id' => $offer->agent_id,
                ], [
                    'property_price' => $acceptedAmount,
                    'commission_rate' => $rate,
                    'commission_amount' => $commAmount,
                    'status' => 'pending',
                ]);

                // Notify Finance Officers and Agent
                $financeStaff = User::whereIn('role', ['finance_officer', 'admin'])->get();
                foreach ($financeStaff as $staff) {
                    $staff->notify(new CommissionGeneratedNotification($commission, 'generated'));
                }
                $agentUser = $offer->agent?->user;
                if ($agentUser) {
                    $agentUser->notify(new CommissionGeneratedNotification($commission, 'generated'));
                }

                ActivityLog::record(
                    'commission_generated',
                    'Rental commission of ETB '.number_format($commAmount, 2)." ({$rate}%) generated for {$offer->agent->name} on Lease {$lease->lease_code}.",
                    $commission
                );
            }

            OfferNegotiation::create([
                'property_offer_id' => $offer->id,
                'sender_type' => auth()->user()->isFinanceStaff() ? 'admin' : 'seller_agent',
                'amount' => $acceptedAmount,
                'currency' => 'ETB',
                'message' => "Converted to official Lease {$lease->lease_code}. Agreement generated with 12 monthly installments scheduled.",
            ]);

            return back()->with('success', "Offer {$offer->offer_reference} successfully converted to Lease {$lease->lease_code}. Official Tenancy Agreement PDF generated.");
        }

        // Sale / Purchase Offer
        if ($offer->order_id) {
            return redirect()->route('orders.agreement', $offer->order_id);
        }

        $order = Order::create([
            'property_id' => $property->id,
            'agent_id' => $offer->agent_id,
            'type' => 'sale',
            'name' => $offer->buyer_name,
            'email' => $offer->buyer_email,
            'phone' => $offer->buyer_phone,
            'offer_amount' => $acceptedAmount,
            'status' => 'accepted',
            'admin_status' => 'approved',
            'agreed_at' => now(),
            'message' => $offer->message,
        ]);

        try {
            $agreements->generate($order);
        } catch (\Throwable $e) {
            report($e);
        }

        $offer->update([
            'order_id' => $order->id,
            'closing_status' => 'agreement_generated',
        ]);

        $property->update([
            'status' => 'sold',
            'is_active' => false,
        ]);

        if ($offer->agent_id) {
            $agent = $offer->agent;
            $settings = app(FinanceSettings::class);
            $rate = $settings->commissionRateFor($offer->agent_id, $property->property_category, null, $agent?->commission_rate);
            $commAmount = $settings->commissionFor($acceptedAmount, $rate);

            $commission = Commission::firstOrCreate([
                'order_id' => $order->id,
            ], [
                'property_id' => $property->id,
                'agent_id' => $offer->agent_id,
                'property_price' => $acceptedAmount,
                'commission_rate' => $rate,
                'commission_amount' => $commAmount,
                'status' => 'pending',
            ]);

            // Notify Finance Officers and Agent
            $financeStaff = User::whereIn('role', ['finance_officer', 'admin'])->get();
            foreach ($financeStaff as $staff) {
                $staff->notify(new CommissionGeneratedNotification($commission, 'generated'));
            }
            $agentUser = $offer->agent?->user;
            if ($agentUser) {
                $agentUser->notify(new CommissionGeneratedNotification($commission, 'generated'));
            }

            ActivityLog::record(
                'commission_generated',
                'Sales commission of ETB '.number_format($commAmount, 2)." ({$rate}%) generated for {$agent->name} on Order {$order->id}.",
                $commission
            );
        }

        OfferNegotiation::create([
            'property_offer_id' => $offer->id,
            'sender_type' => auth()->user()->isAdmin() ? 'admin' : 'seller_agent',
            'amount' => $acceptedAmount,
            'currency' => 'ETB',
            'message' => 'Converted to official Sales Contract. Purchase agreement generated and earnest money escrow milestone initiated.',
        ]);

        return back()->with('success', "Offer {$offer->offer_reference} successfully converted to Sales Contract. Official Agreement PDF generated and earnest money requested.");
    }

    /**
     * Reject a submitted offer (SRS §76).
     */
    public function rejectOffer(Request $request, PropertyOffer $offer): RedirectResponse
    {
        $this->authorizeOfferAccess($offer);

        $offer->update([
            'status' => 'rejected',
        ]);

        OfferNegotiation::create([
            'property_offer_id' => $offer->id,
            'sender_type' => auth()->user()->isAdmin() ? 'admin' : 'seller_agent',
            'amount' => $offer->current_counter_amount ?? $offer->offered_price,
            'currency' => 'ETB',
            'message' => $request->input('reason', 'Offer declined by seller/agent.'),
        ]);

        return back()->with('success', "Offer {$offer->offer_reference} declined.");
    }

    protected function authorizeViewingAccess(PropertyViewing $viewing): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        if ($user->isAdmin()) {
            return;
        }

        if ($user->agentProfile && $viewing->agent_id === $user->agentProfile->id) {
            return;
        }

        if ($viewing->user_id && $user->id === $viewing->user_id) {
            return;
        }

        if ($viewing->buyer_email && strtolower($user->email) === strtolower($viewing->buyer_email)) {
            return;
        }

        if ($viewing->property && (int) $viewing->property->user_id === (int) $user->id) {
            return;
        }

        abort(403, 'Unauthorized access to this viewing appointment.');
    }

    protected function authorizeOfferAccess(PropertyOffer $offer): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        if ($user->isFinanceStaff()) {
            return;
        }

        if ($user->agentProfile && $offer->agent_id === $user->agentProfile->id) {
            return;
        }

        if ($offer->property && (int) $offer->property->user_id === (int) $user->id) {
            return;
        }

        if ($offer->user_id && $user->id === $offer->user_id) {
            return;
        }

        if ($offer->buyer_email && strtolower($user->email) === strtolower($offer->buyer_email)) {
            return;
        }

        abort(403, 'Unauthorized access to this offer.');
    }
}
