<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\RentSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Rent installment collection for the landlord desk: records the payment and
 * advances the rent schedule (financial records are appended, never
 * overwritten).
 */
class RentScheduleController extends Controller
{
    public function recordPayment(Request $request, RentSchedule $rentSchedule): RedirectResponse
    {
        $this->authorizeLandlord($rentSchedule);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'payment_method' => ['required', 'in:telebirr,cbe_birr,bank_transfer,chapa,cash'],
            'bank_transaction_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $lease = $rentSchedule->lease;

        $payment = Payment::create([
            'transaction_number' => 'RENT-'.$rentSchedule->id.'-'.now()->format('YmdHis'),
            'payable_type' => RentSchedule::class,
            'payable_id' => $rentSchedule->id,
            'property_id' => $lease?->property_id,
            'user_id' => auth()->id(),
            'payer_name' => $lease?->tenant_name ?? 'Tenant',
            'payer_email' => $lease?->tenant_email ?? 'unknown@example.com',
            'payer_phone' => $lease?->tenant_phone,
            'payment_type' => 'rent_installment',
            'payment_method' => $validated['payment_method'],
            'bank_transaction_reference' => $validated['bank_transaction_reference'] ?? null,
            'amount' => $validated['amount'],
            'status' => 'completed',
            'notes' => 'Rent installment for period: '.($rentSchedule->period_label ?? '#'.$rentSchedule->installment_number),
        ]);

        $paidTotal = (float) $rentSchedule->amount_paid + (float) $validated['amount'];

        $rentSchedule->update([
            'amount_paid' => $paidTotal,
            'status' => $paidTotal + 0.01 >= (float) $rentSchedule->amount_due ? 'paid' : 'partial',
            'payment_id' => $payment->id,
            'paid_at' => now(),
            'payment_method' => $validated['payment_method'],
        ]);

        return back()->with('success', 'Rent payment of '.number_format((float) $validated['amount']).' ETB recorded.');
    }

    private function authorizeLandlord(RentSchedule $rentSchedule): void
    {
        $user = auth()->user();

        $ownsLease = $user && Lease::query()
            ->where('id', $rentSchedule->lease_id)
            ->where('landlord_user_id', $user->id)
            ->exists();

        abort_unless(
            $user && (
                $user->isAdmin()
                || ($user->role === 'finance_officer' && $user->hasPermission('payment_issues.handle'))
                || ($user->role === 'landlord' && $user->hasPermission('rent.manage') && $ownsLease)
                || ($user->role === 'property_manager' && ($user->hasPermission('rent.update') || $user->hasPermission('rent.manage')))
            ),
            403
        );
    }
}
