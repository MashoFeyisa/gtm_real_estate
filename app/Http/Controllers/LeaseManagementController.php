<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Property;
use App\Models\RentSchedule;
use App\Models\User;
use App\Services\AgreementPdfService;
use App\Services\RentPaymentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaseManagementController extends Controller
{
    /**
     * Store a new rental lease agreement and generate rent schedule (SRS §84–§88).
     */
    public function store(Request $request, AgreementPdfService $agreements): RedirectResponse
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        // Finance Officers control the lease desk but may never register new lease agreements.
        if ($user->isFinanceOfficer()) {
            abort(403, 'Finance officers are not permitted to register new lease agreements.');
        }

        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'tenant_name' => ['required', 'string', 'max:255'],
            'tenant_email' => ['required', 'email', 'max:255'],
            'tenant_phone' => ['required', 'string', 'max:50'],
            'tenant_id_type' => ['nullable', 'string', 'in:kebele_id,passport,national_id'],
            'tenant_id_number' => ['nullable', 'string', 'max:100'],
            'tenant_occupation' => ['nullable', 'string', 'max:100'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:120'],
            'monthly_rent' => ['required', 'numeric', 'min:1'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'payment_frequency' => ['required', 'string', 'in:monthly,quarterly,semi_annually,annually'],
            'grace_period_days' => ['nullable', 'integer', 'min:0', 'max:30'],
            'late_fee_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'terms_and_conditions' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $property = Property::findOrFail($validated['property_id']);

        // Check authorization: Must be admin or property's assigned agent
        $isAgent = $property->agent_id && $user->agentProfile && $property->agent_id === $user->agentProfile->id;
        if (! $user->isAdmin() && ! $isAgent) {
            abort(403, 'Unauthorized to create lease for this property.');
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = $startDate->copy()->addMonths((int) $validated['duration_months'])->subDay();

        $matchingTenantUser = User::where('email', $validated['tenant_email'])->first();

        $lease = Lease::create([
            'property_id' => $property->id,
            'agent_id' => $property->agent_id ?? ($user->agentProfile?->id),
            'landlord_user_id' => $user->id,
            'tenant_user_id' => $matchingTenantUser?->id,
            'tenant_name' => $validated['tenant_name'],
            'tenant_email' => $validated['tenant_email'],
            'tenant_phone' => $validated['tenant_phone'],
            'tenant_id_type' => $validated['tenant_id_type'] ?? 'kebele_id',
            'tenant_id_number' => $validated['tenant_id_number'] ?? null,
            'tenant_occupation' => $validated['tenant_occupation'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'duration_months' => (int) $validated['duration_months'],
            'monthly_rent' => (float) $validated['monthly_rent'],
            'security_deposit' => (float) ($validated['security_deposit'] ?? 0),
            'payment_frequency' => $validated['payment_frequency'],
            'grace_period_days' => (int) ($validated['grace_period_days'] ?? 5),
            'late_fee_percentage' => (float) ($validated['late_fee_percentage'] ?? 0),
            'status' => 'active',
            'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Auto-generate installment rent schedule
        $lease->generateSchedules();

        // Auto-generate official Ethiopian Tenancy Agreement PDF
        try {
            $agreements->generateLease($lease);
        } catch (\Throwable $e) {
            // Log but don't prevent lease creation if PDF rendering encounters system font/environment constraints
            report($e);
        }

        return back()->with('success', "Lease Agreement {$lease->lease_code} registered successfully with {$lease->schedules()->count()} rent installments scheduled.");
    }

    /**
     * Download official generated Ethiopian Tenancy Agreement PDF (SRS §84–§88).
     */
    public function downloadAgreement(Lease $lease, AgreementPdfService $agreements): StreamedResponse
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        $isAgent = $lease->agent_id && $user->agentProfile && $lease->agent_id === $user->agentProfile->id;
        $isTenant = $lease->tenant_user_id === $user->id || $lease->tenant_email === $user->email;
        if (! $user->isAdmin() && ! $user->isFinanceOfficer() && ! $user->isPropertyManager() && ! $isAgent && ! $isTenant) {
            abort(403, 'Unauthorized access to lease agreement.');
        }

        if (! $lease->agreement_document_path || ! Storage::disk('local')->exists($lease->agreement_document_path)) {
            $agreements->generateLease($lease);
        }

        $filename = "{$lease->lease_code}-rental-agreement.pdf";

        return Storage::disk('local')->download($lease->agreement_document_path, $filename);
    }

    /**
     * Get JSON data for a specific lease and its full rent schedule.
     */
    public function show(Lease $lease): JsonResponse
    {
        $lease->load(['property', 'agent', 'schedules.payment']);

        return response()->json([
            'lease' => $lease,
            'schedules' => $lease->schedules,
            'summary' => [
                'total_rent_due' => $lease->total_rent_due,
                'total_rent_paid' => $lease->total_rent_paid,
                'outstanding_balance' => $lease->outstanding_balance,
            ],
        ]);
    }

    /**
     * Update lease lifecycle status (activate, terminate, renew, etc.).
     */
    public function updateStatus(Request $request, Lease $lease): RedirectResponse
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        $isAgent = $lease->agent_id && $user->agentProfile && $lease->agent_id === $user->agentProfile->id;
        if (! $user->isAdmin() && ! $user->isFinanceOfficer() && ! $user->isPropertyManager() && ! $isAgent) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:active,draft,pending_renewal,expired,terminated'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $lease->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $lease->notes,
        ]);

        return back()->with('success', "Lease {$lease->lease_code} status updated to {$validated['status']}.");
    }

    /**
     * Record a direct installment payment against a rent schedule.
     */
    public function recordInstallmentPayment(Request $request, RentSchedule $schedule): RedirectResponse
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        $lease = $schedule->lease;
        $isAgent = $lease->agent_id && $user->agentProfile && $lease->agent_id === $user->agentProfile->id;
        $isManager = $user->isAdmin() || $user->isFinanceOfficer() || $user->isPropertyManager() || $isAgent;
        $isTenant = ($lease->tenant_user_id && (int) $lease->tenant_user_id === (int) $user->id)
            || ($lease->tenant_email && strcasecmp((string) $lease->tenant_email, (string) $user->email) === 0)
            || (in_array($user->role, ['tenant', 'renter'], true) && (! $lease->tenant_user_id || (int) $lease->tenant_user_id === (int) $user->id));

        if (! $isManager && ! ($isTenant && ($user->hasPermission('rent.pay') || in_array($user->role, ['tenant', 'renter'], true)))) {
            abort(403, 'Unauthorized.');
        }

        if (! $request->filled('amount_paid')) {
            $amountDue = (float) $schedule->amount_due;
            $alreadyPaid = (float) $schedule->amount_paid;
            $remaining = max(1, $amountDue - $alreadyPaid);
            $request->merge(['amount_paid' => $remaining]);
        }

        if (! $request->filled('payment_method')) {
            $request->merge(['payment_method' => 'telebirr']);
        }

        $validated = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'in:telebirr,cbe_birr,bank_transfer,cash'],
            'bank_transaction_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $amount = (float) $validated['amount_paid'];

        // The one rent writer: a verified payment row plus a balanced journal,
        // shared with the landlord desk so both leave the same trace.
        app(RentPaymentService::class)->record(
            schedule: $schedule,
            recorder: $user,
            amount: $amount,
            paymentMethod: $validated['payment_method'],
            reference: $validated['bank_transaction_reference'] ?? null,
            notes: $validated['notes'] ?? null,
            idempotencyKey: $request->header('Idempotency-Key'),
        );

        return back()->with('success', 'Payment of '.number_format($amount, 2)." ETB recorded for {$schedule->period_label}.");
    }

    /**
     * Trigger automated rent schedule audit and escalation check (SRS §87, §88).
     */
    public function processSchedules(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || (! $user->isAdmin() && ! $user->isFinanceOfficer() && ! $user->agentProfile)) {
            abort(403, 'Unauthorized.');
        }

        Artisan::call('rents:process-schedules', ['--notify' => true]);

        return back()->with('success', 'Rent schedule audit and overdue escalation check completed successfully.');
    }
}
