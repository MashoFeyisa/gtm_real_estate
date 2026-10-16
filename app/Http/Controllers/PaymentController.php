<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\PaymentGatewayLog;
use App\Models\RentSchedule;
use App\Models\User;
use App\Notifications\PaymentStatusNotification;
use App\Services\FinanceSettings;
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
        // Every fee, VAT and net amount comes from the one shared settings
        // service - nothing in this path may hard-code a rate (SRS §171, §195).
        $settings = app(FinanceSettings::class);
        $platformFee = $settings->platformFee($amount);
        $taxAmount = $settings->vatOnFee($platformFee);
        $netAmount = $settings->netAmount($amount, $platformFee, $taxAmount);

        // A retried submission must not create a second payment (SRS §80). The
        // caller supplies an idempotency key; the same key with the same payload
        // returns the row that was already written, while the same key with a
        // different payload is a conflict (HTTP 409).
        $idempotencyKey = $request->header('Idempotency-Key') ?? $request->input('idempotency_key');
        $idempotencyHash = null;

        if (is_string($idempotencyKey) && $idempotencyKey !== '') {
            $idempotencyHash = hash('sha256', json_encode([
                'payment_type' => $validated['payment_type'],
                'payment_method' => $validated['payment_method'],
                'amount' => $amount,
                'payer_name' => $validated['payer_name'],
                'payer_email' => $validated['payer_email'],
                'payer_phone' => $validated['payer_phone'] ?? null,
                'property_id' => $validated['property_id'] ?? null,
                'payable_type' => $validated['payable_type'] ?? null,
                'payable_id' => $validated['payable_id'] ?? null,
                'bank_transaction_reference' => $validated['bank_transaction_reference'] ?? null,
            ]));

            $existing = Payment::where('idempotency_key', $idempotencyKey)->first();

            if ($existing) {
                if (! hash_equals((string) $existing->idempotency_hash, $idempotencyHash)) {
                    abort(409, 'This idempotency key was already used with a different payload.');
                }

                return redirect()->route('payments.receipt', $existing->transaction_number)
                    ->with('info', "Payment {$existing->transaction_number} was already submitted for this request.");
            }
        }

        // No payment method verifies itself. A method being "instant" only
        // controls how fast the provider reports back - a mobile-money transfer
        // still has to be confirmed by the payer's provider or by finance.
        // A payer can never be the verifier of their own payment (SRS §83).
        $payment = Payment::create([
            'payable_type' => $validated['payable_type'] ?? null,
            'payable_id' => $validated['payable_id'] ?? null,
            'property_id' => $validated['property_id'] ?? null,
            'user_id' => auth()->id(),
            'idempotency_key' => is_string($idempotencyKey) && $idempotencyKey !== '' ? $idempotencyKey : null,
            'idempotency_hash' => $idempotencyHash,
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
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'verified_at' => null,
            'verified_by' => null,
        ]);

        return redirect()->route('payments.receipt', $payment->transaction_number)
            ->with('success', "Payment {$payment->transaction_number} submitted. It will be confirmed once your {$payment->method_label} transfer is verified.");
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

        // Rent installments belong to a lease, and the lease (not always the
        // property) names the agent who brokered the deal, so the lease agent
        // must be able to open the receipt too.
        if (! $isAgent && $user?->agentProfile && $payment->payable_type === RentSchedule::class) {
            $lease = RentSchedule::find($payment->payable_id)?->lease;
            $isAgent = $lease && $lease->agent_id === $user->agentProfile->id;
        }

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

        $this->abortIfPaymentOriginator($payment, $user);

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

        $this->abortIfPaymentOriginator($payment, $user);

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
     * Separation of duties (SRS §83): whoever made a payment can never verify
     * or reject it, even when they also hold a finance role. A person cannot
     * confirm their own money - the same rule the escrow desk enforces.
     */
    protected function abortIfPaymentOriginator(Payment $payment, User $user): void
    {
        $isSubmitter = $payment->user_id !== null && $payment->user_id === $user->id;
        $isPayerEmail = $payment->payer_email !== null
            && $user->email !== null
            && strcasecmp((string) $payment->payer_email, (string) $user->email) === 0;

        if ($isSubmitter || $isPayerEmail) {
            abort(403, 'Separation of duties: the payer cannot verify or reject their own payment.');
        }
    }

    /**
     * Process an authenticated payment-gateway webhook (Telebirr, CBE Birr, Chapa).
     *
     * Idempotency is keyed on the provider's own transaction reference: the
     * same reference twice creates one payment row and settles it once.
     */
    public function handleGatewayCallback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_number' => ['nullable', 'string', 'max:100'],
            'gateway_reference' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:completed,success,failed'],
            'provider' => ['nullable', 'string', 'max:50'],
            'payer_name' => ['nullable', 'string', 'max:255'],
            'payer_email' => ['nullable', 'email', 'max:255'],
            'payer_phone' => ['nullable', 'string', 'max:50'],
            'payment_type' => ['nullable', 'string', 'in:earnest_deposit,stay_booking,unit_reservation,rent_installment,service_fee'],
            'payment_method' => ['nullable', 'string', 'in:telebirr,cbe_birr,bank_transfer,cash'],
            'property_id' => ['nullable', 'exists:properties,id'],
        ]);

        $provider = $validated['provider'] ?? (string) $request->header('X-Provider', 'gateway');
        $gatewayRef = $validated['gateway_reference'];
        $txnNumber = $validated['transaction_number'] ?? null;
        $isSuccess = in_array(strtolower($validated['status']), ['completed', 'success'], true);

        $response = DB::transaction(function () use ($validated, $provider, $gatewayRef, $txnNumber, $isSuccess, $request) {
            // Locate or create the payment the provider is reporting on. The
            // provider reference is the identity, so a replayed webhook can
            // never open a second payment row.
            $payment = $this->resolveCallbackPayment($validated, $gatewayRef, $txnNumber);

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

            if ($payment->status === 'refunded' || $payment->status === 'failed') {
                return response()->json([
                    'success' => false,
                    'status' => $payment->status,
                    'idempotent' => true,
                    'message' => "Transaction {$payment->transaction_number} is already {$payment->status}; no change made.",
                    'transaction_number' => $payment->transaction_number,
                ], 200);
            }

            if (! $isSuccess) {
                $payment->update([
                    'status' => 'failed',
                    'bank_transaction_reference' => $gatewayRef,
                    'admin_notes' => "Gateway ({$provider}) reported failed payment",
                ]);

                return response()->json([
                    'success' => false,
                    'status' => 'failed',
                    'idempotent' => false,
                    'message' => 'Payment marked as failed per gateway status.',
                    'transaction_number' => $payment->transaction_number,
                ], 200);
            }

            $previousStatus = $payment->status;

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
                oldValue: ['status' => $previousStatus],
                newValue: ['status' => 'completed', 'gateway_reference' => $gatewayRef],
                reason: "Gateway {$provider} callback verified and settled",
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

        $this->recordGatewayOutcome($request, $provider, $gatewayRef, $txnNumber, $response);

        return $response;
    }

    /**
     * Find the pending payment for a gateway callback, or create one.
     *
     * A provider reports a transaction the platform has never seen (a payer
     * charged directly on the provider's checkout) as easily as one it raised
     * itself, so the callback may be the first record of a payment. Matching is
     * by transaction number first, then by the provider reference, so the same
     * reference always resolves to the same payment.
     *
     * @param  array<string, mixed>  $validated
     */
    protected function resolveCallbackPayment(array $validated, string $gatewayRef, ?string $txnNumber): Payment
    {
        $payment = Payment::query()
            ->lockForUpdate()
            ->where(function ($query) use ($gatewayRef, $txnNumber): void {
                $query->where('bank_transaction_reference', $gatewayRef);

                if ($txnNumber !== null) {
                    $query->orWhere('transaction_number', $txnNumber);
                }
            })
            ->orderBy('id')
            ->first();

        if ($payment) {
            return $payment;
        }

        $amount = (float) $validated['amount'];
        $settings = app(FinanceSettings::class);
        $platformFee = $settings->platformFee($amount);
        $taxAmount = $settings->vatOnFee($platformFee);

        return Payment::create([
            'payable_type' => null,
            'payable_id' => null,
            'property_id' => $validated['property_id'] ?? null,
            'user_id' => isset($validated['payer_email']) ? User::where('email', $validated['payer_email'])->value('id') : null,
            'payer_name' => $validated['payer_name'] ?? 'Gateway Payer',
            'payer_email' => $validated['payer_email'] ?? $gatewayRef.'@gateway.invalid',
            'payer_phone' => $validated['payer_phone'] ?? null,
            'payment_type' => $validated['payment_type'] ?? 'service_fee',
            'payment_method' => $validated['payment_method'] ?? 'telebirr',
            'bank_name' => null,
            'bank_account_number' => null,
            'bank_transaction_reference' => $gatewayRef,
            'currency' => 'ETB',
            'amount' => $amount,
            'platform_fee' => $platformFee,
            'tax_amount' => $taxAmount,
            'net_amount' => $settings->netAmount($amount, $platformFee, $taxAmount),
            'status' => 'pending',
            'notes' => 'Created from an authenticated gateway callback.',
        ]);
    }

    /**
     * Update the middleware's audit row with what the callback actually did.
     */
    protected function recordGatewayOutcome(
        Request $request,
        string $provider,
        string $gatewayRef,
        ?string $txnNumber,
        JsonResponse $response
    ): void {
        $log = $request->attributes->get('provider_signature_log');

        if (! $log instanceof PaymentGatewayLog) {
            return;
        }

        $decoded = json_decode($response->getContent() ?: '{}', true);
        $payment = Payment::where('bank_transaction_reference', $gatewayRef)->first();

        $log->update([
            'event' => 'callback.processed',
            'outcome' => ($decoded['success'] ?? false) ? 'processed' : 'rejected',
            'response_code' => $response->getStatusCode(),
            'payment_id' => $payment?->id,
            'transaction_number' => $decoded['transaction_number'] ?? $txnNumber,
            'message' => $decoded['message'] ?? null,
        ]);
    }
}
