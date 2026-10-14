<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentStatusNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    /**
     * Submit an Ethiopian payment or bank deposit slip (SRS §78–§81).
     */
    public function store(Request $request): RedirectResponse
    {
        if (! auth()->check()) {
            return $this->redirectGuestToSignup(
                $request,
                route('client.portal'),
                'Register / Login to continue: Create a client account or sign in before submitting a payment.'
            );
        }

        $validated = $request->validate([
            'payment_type' => ['required', 'string', 'in:earnest_deposit,stay_booking,unit_reservation,rent_installment,service_fee'],
            'payment_method' => ['required', 'string', 'in:telebirr,cbe_birr,bank_transfer,cash'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payer_name' => ['required', 'string', 'max:255'],
            'payer_email' => ['required', 'email', 'max:255'],
            'payer_phone' => ['nullable', 'string', 'max:50'],
            'property_id' => ['nullable', 'exists:properties,id'],
            'payable_type' => ['nullable', 'string'],
            'payable_id' => ['nullable', 'integer'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_transaction_reference' => ['nullable', 'string', 'max:100'],
            'deposit_slip' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:12288'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $slipPath = null;
        if ($request->hasFile('deposit_slip')) {
            $slipPath = $request->file('deposit_slip')->store('deposit_slips', 'public');
        }

        $amount = (float) $validated['amount'];
        // Standard platform service fee estimate (e.g., 2% of amount)
        $platformFee = round($amount * 0.02, 2);
        $taxAmount = round($platformFee * 0.15, 2); // 15% VAT on service fee
        $netAmount = round($amount - $platformFee, 2);

        // Immediate completion for instant mobile simulation; pending for bank deposit slips requiring audit
        $isInstant = in_array($validated['payment_method'], ['telebirr']);
        $initialStatus = $isInstant ? 'completed' : 'pending';

        $payment = Payment::create([
            'payable_type' => $validated['payable_type'] ?? null,
            'payable_id' => $validated['payable_id'] ?? null,
            'property_id' => $validated['property_id'] ?? null,
            'user_id' => auth()->id(),
            'payer_name' => $validated['payer_name'],
            'payer_email' => $validated['payer_email'],
            'payer_phone' => $validated['payer_phone'] ?? null,
            'payment_type' => $validated['payment_type'],
            'payment_method' => $validated['payment_method'],
            'bank_name' => $validated['bank_name'] ?? ($validated['payment_method'] === 'cbe_birr' ? 'Commercial Bank of Ethiopia' : null),
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_transaction_reference' => $validated['bank_transaction_reference'] ?? null,
            'deposit_slip_path' => $slipPath,
            'currency' => 'ETB',
            'amount' => $amount,
            'platform_fee' => $platformFee,
            'tax_amount' => $taxAmount,
            'net_amount' => $netAmount,
            'status' => $initialStatus,
            'notes' => $validated['notes'] ?? null,
            'verified_at' => $isInstant ? now() : null,
            'verified_by' => $isInstant ? auth()->id() : null,
        ]);

        if ($payment->status === 'completed') {
            $payment->settle();
        }

        return redirect()->route('payments.receipt', $payment->transaction_number)
            ->with('success', "Payment {$payment->transaction_number} processed successfully.");
    }

    /**
     * Display printable official Ethiopian digital payment receipt (SRS §81, §82).
     */
    public function receipt(string $transactionNumber): View
    {
        $payment = Payment::with(['property', 'user', 'verifier', 'ledgers'])
            ->where('transaction_number', $transactionNumber)
            ->firstOrFail();

        $user = auth()->user();
        $isPayer = $payment->user_id === $user?->id;
        $isAgent = $payment->property && $user?->agentProfile && $payment->property->agent_id === $user->agentProfile->id;

        abort_unless($user && ($user->isAdmin() || $user->isFinanceOfficer() || $isPayer || $isAgent), 403);

        return view('payments.receipt', compact('payment'));
    }

    /**
     * Verify and approve a pending bank deposit slip (Admin/Finance Only, SRS §82, §83).
     */
    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            'bank_transaction_reference' => ['nullable', 'string', 'max:100'],
        ]);

        if ($payment->status === 'completed') {
            return back()->with('info', "Transaction {$payment->transaction_number} was already verified.");
        }

        $payment->update([
            'status' => 'completed',
            'admin_notes' => $validated['admin_notes'] ?? $payment->admin_notes,
            'bank_transaction_reference' => $validated['bank_transaction_reference'] ?? $payment->bank_transaction_reference,
            'verified_at' => now(),
            'verified_by' => $user->id,
        ]);

        $payment->settle();

        $payerUser = $payment->user ?? User::where('email', $payment->payer_email)->first();
        if ($payerUser) {
            $payerUser->notify(new PaymentStatusNotification($payment, 'completed'));
        }

        // Also notify property agent if payment is attached to property
        $agentUser = $payment->property?->agent?->user;
        if ($agentUser && (! $payerUser || $agentUser->id !== $payerUser->id)) {
            $agentUser->notify(new PaymentStatusNotification($payment, 'completed'));
        }

        ActivityLog::record(
            'payment_verified',
            "Finance Officer {$user->name} verified payment {$payment->transaction_number} (ETB ".number_format($payment->amount, 2).').',
            $payment
        );

        return back()->with('success', "Transaction {$payment->transaction_number} verified and confirmed.");
    }

    /**
     * Reject an invalid or unverified deposit slip.
     */
    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ]);

        $payment->update([
            'status' => 'failed',
            'admin_notes' => $validated['admin_notes'],
            'verified_at' => now(),
            'verified_by' => $user->id,
        ]);

        $payment->flagPayableRejected();

        $payerUser = $payment->user ?? User::where('email', $payment->payer_email)->first();
        if ($payerUser) {
            $payerUser->notify(new PaymentStatusNotification($payment, 'failed'));
        }

        return back()->with('success', "Transaction {$payment->transaction_number} marked as failed/rejected.");
    }

    /**
     * Securely download or view uploaded deposit slip.
     */
    public function viewSlip(Payment $payment): StreamedResponse
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        // Must be admin, finance officer, payer or assigned property agent
        $isPayer = $payment->user_id === $user->id;
        $isAgent = $payment->property && $user->agentProfile && $payment->property->agent_id === $user->agentProfile->id;

        if (! $user->isAdmin() && ! $user->isFinanceOfficer() && ! $isPayer && ! $isAgent) {
            abort(403, 'Unauthorized access to payment record.');
        }

        if (! $payment->deposit_slip_path || ! Storage::disk('public')->exists($payment->deposit_slip_path)) {
            abort(404, 'Deposit slip receipt file not found.');
        }

        return Storage::disk('public')->download($payment->deposit_slip_path);
    }

    /**
     * Process idempotent payment gateway webhook / callback (Telebirr, CBE Birr, Chapa).
     */
    public function handleGatewayCallback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_number' => ['nullable', 'string', 'max:100'],
            'gateway_reference' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:completed,success,failed'],
            'provider' => ['nullable', 'string', 'max:50'],
        ]);

        $gatewayRef = $validated['gateway_reference'];
        $txnNumber = $validated['transaction_number'] ?? null;
        $isSuccess = in_array(strtolower($validated['status']), ['completed', 'success'], true);

        return DB::transaction(function () use ($gatewayRef, $txnNumber, $isSuccess, $validated, $request) {
            // Locate payment by transaction number or existing gateway reference
            $paymentQuery = Payment::query()->lockForUpdate();
            if ($txnNumber) {
                $paymentQuery->where('transaction_number', $txnNumber);
            } else {
                $paymentQuery->where('bank_transaction_reference', $gatewayRef);
            }

            $payment = $paymentQuery->first();

            if (! $payment) {
                return response()->json([
                    'success' => false,
                    'error' => 'Payment record not found for the provided transaction references.',
                ], 404);
            }

            // IDEMPOTENCY CHECK: If already completed, return idempotent response immediately without re-settling
            if ($payment->status === 'completed') {
                return response()->json([
                    'success' => true,
                    'status' => 'already_processed',
                    'idempotent' => true,
                    'message' => "Transaction {$payment->transaction_number} was already settled. No duplicate action taken.",
                    'transaction_number' => $payment->transaction_number,
                    'settled_at' => $payment->verified_at?->toIso8601String(),
                ], 200);
            }

            if (! $isSuccess) {
                $payment->update([
                    'status' => 'failed',
                    'bank_transaction_reference' => $gatewayRef,
                    'admin_notes' => "Gateway ({$validated['provider']}) reported failed payment",
                ]);

                return response()->json([
                    'success' => false,
                    'status' => 'failed',
                    'idempotent' => false,
                    'message' => 'Payment marked as failed per gateway status.',
                    'transaction_number' => $payment->transaction_number,
                ], 200);
            }

            // Process settlement atomically
            $payment->update([
                'status' => 'completed',
                'bank_transaction_reference' => $gatewayRef,
                'verified_at' => now(),
            ]);

            $payment->settle();

            AuditLog::log(
                action: 'payment.webhook_settled',
                entity: $payment,
                oldValue: ['status' => 'pending'],
                newValue: ['status' => 'completed', 'gateway_reference' => $gatewayRef],
                reason: "Gateway {$validated['provider']} callback verified and settled",
                request: $request
            );

            return response()->json([
                'success' => true,
                'status' => 'completed',
                'idempotent' => false,
                'message' => "Payment {$payment->transaction_number} successfully verified and settled.",
                'transaction_number' => $payment->transaction_number,
            ], 200);
        });
    }
}
