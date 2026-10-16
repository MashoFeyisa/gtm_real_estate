<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\CompanyWhiteLabel;
use App\Models\FinanceRate;
use App\Models\FinanceReviewFlag;
use App\Models\FinancialLedger;
use App\Models\FrontDeskShift;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\PayoutRequest;
use App\Models\Refund;
use App\Models\RentSchedule;
use App\Models\VatRemittance;
use App\Notifications\CommissionGeneratedNotification;
use App\Services\AgentBalanceService;
use App\Services\AnalyticsService;
use App\Services\FinanceSettings;
use App\Services\LedgerService;
use App\Services\PayoutService;
use App\Services\RefundService;
use App\Services\WorkflowIntegrationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialReportingController extends Controller
{
    /**
     * Financial Reporting & Commission Settlement Desk (SRS §126–§135).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $catalog = $user ? $user->permissionCatalog() : [];
        $hasFinancePerm = ! empty($catalog) && $user->hasAnyPermission([
            'ledger.view',
            'ledger.create',
            'reconciliation.view',
            'reconciliation.manage',
            'tax.manage',
            'tax.view',
            'payments.manage',
            'refunds.manage',
            'refunds.view',
            'invoices.manage',
            'invoices.view',
            'subscriptions.manage',
            'subscriptions.view',
            'payouts.view',
        ]);

        if (! $user || (! $user->isAdmin() && ! $user->isFinanceOfficer() && ! $hasFinancePerm && ! $user->agentProfile)) {
            abort(403, 'Unauthorized access to financial reports.');
        }

        $isAdmin = $user->isAdmin();
        $isFinanceOfficer = $user->isFinanceOfficer();
        $canViewPlatformBooks = $isAdmin || $isFinanceOfficer || $hasFinancePerm;

        // Agents join this desk only for their own deals
        $scopedAgent = (! $canViewPlatformBooks) ? $user->agentProfile : null;

        $dateFilter = $request->input('period', 'this_month');
        $query = FinancialLedger::query();

        if ($scopedAgent) {
            $query->where(function ($ledgerQuery) use ($scopedAgent, $user) {
                $ledgerQuery->whereHas('payment.property', fn ($propertyQuery) => $propertyQuery->where('agent_id', $scopedAgent->id))
                    ->orWhereHas('payment', fn ($paymentQuery) => $paymentQuery->where('user_id', $user->id));
            });
        }

        // Apply date filtering
        match ($dateFilter) {
            'today' => $query->whereDate('created_at', Carbon::today()),
            'this_week' => $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]),
            'this_month' => $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]),
            'last_month' => $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]),
            'this_quarter' => $query->whereBetween('created_at', [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()]),
            'this_year' => $query->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]),
            'custom' => $request->filled(['start_date', 'end_date'])
                ? $query->whereBetween('created_at', [
                    Carbon::parse($request->input('start_date'))->startOfDay(),
                    Carbon::parse($request->input('end_date'))->endOfDay(),
                ])
                : null,
            default => null, // all_time
        };

        $ledgers = (clone $query)->with(['payment.property', 'user'])->latest()->paginate(12)->withQueryString();

        // Revenue Breakdown by category (the `cash` leg is a balance-sheet
        // account, so it is excluded from the profit-and-loss inflow/outflow).
        //
        // Legacy single-sided rows are never folded into net revenue or the P&L:
        // they were written before double entry, so they are shown as their own
        // totals. Only the balanced current journals drive the profit and loss.
        $current = fn () => (clone $query)->where('is_legacy', false);
        $legacy = fn () => (clone $query)->where('is_legacy', true);

        $grossInflow = (float) $current()->where('entry_type', 'credit')->where('category', '!=', 'cash')->sum('amount');
        $grossOutflow = (float) $current()->where('entry_type', 'debit')->where('category', '!=', 'cash')->sum('amount');
        $netRetained = round($grossInflow - $grossOutflow, 2);

        $rentalRevenue = (float) $current()->where('entry_type', 'credit')->where('category', 'rental_revenue')->sum('amount');
        $earnestDeposits = (float) $current()->where('entry_type', 'credit')->where('category', 'earnest_deposit')->sum('amount');
        $bookingRevenue = (float) $current()->where('entry_type', 'credit')->where('category', 'booking_revenue')->sum('amount');
        $developerDeposits = (float) $current()->where('entry_type', 'credit')->where('category', 'developer_deposit')->sum('amount');
        $platformFees = (float) $current()->where('entry_type', 'credit')->where('category', 'platform_fee')->sum('amount');
        $disbursedCommissions = (float) $current()->where('entry_type', 'debit')->where('category', 'agent_commission')->sum('amount');

        // Ethiopian Tax Breakdown. VAT is read from its own payable account
        // (not re-derived from today's rate, which would rewrite history), and
        // withholding tax from the settled commission records.
        $vatAccrued = (float) $current()->where('entry_type', 'credit')->where('category', 'vat_payable')->sum('amount');
        $vatReversed = (float) $current()->where('entry_type', 'debit')->where('category', 'vat_payable')
            ->where(fn ($q) => $q->where('transaction_group', 'like', 'refund:%')->orWhere('transaction_group', 'like', 'LED-ADJ-REF-%'))
            ->sum('amount');
        $vatRemitted = (float) $current()->where('entry_type', 'debit')->where('category', 'vat_payable')
            ->where('transaction_group', 'like', 'vat-remittance:%')
            ->sum('amount');
        $vatBalance = round($vatAccrued - $vatReversed - $vatRemitted, 2);

        // Pre-change rows are single-sided; they are surfaced separately so a
        // report can tell a legacy entry from a balanced journal leg.
        $legacyEntries = (int) $legacy()->count();
        $legacyInflow = (float) $legacy()->where('entry_type', 'credit')->sum('amount');
        $legacyOutflow = (float) $legacy()->where('entry_type', 'debit')->sum('amount');

        // Commissions Table
        $commissionsQuery = Commission::with(['agent.user', 'listingAgent', 'sellingAgent', 'referralAgent', 'property', 'order', 'lease', 'disbursedBy']);
        if (! $isAdmin && $user->agentProfile) {
            $agentId = $user->agentProfile->id;
            $commissionsQuery->where(function ($q) use ($agentId) {
                $q->where('agent_id', $agentId)
                    ->orWhere('listing_agent_id', $agentId)
                    ->orWhere('selling_agent_id', $agentId)
                    ->orWhere('referral_agent_id', $agentId);
            });
        }

        $commissions = (clone $commissionsQuery)->latest()->paginate(10, ['*'], 'commissions_page')->withQueryString();
        $totalPendingCommissions = (float) (clone $commissionsQuery)->where('status', 'pending')->sum('commission_amount');
        $totalPaidCommissions = (float) (clone $commissionsQuery)->where('status', 'paid')->sum('commission_amount');
        $totalNetPaidOut = (float) (clone $commissionsQuery)->where('status', 'paid')->sum('net_payout_amount');
        $totalWithholdingTax = (float) (clone $commissionsQuery)->where('status', 'paid')->sum('withholding_tax_amount');
        $companyRetainedCommissions = (float) (clone $commissionsQuery)->sum('company_retained_amount');
        $pendingCommissionsCount = (clone $commissionsQuery)->where('status', 'pending')->count();
        $coBrokeredDealsCount = (clone $commissionsQuery)->where('split_type', 'co_brokered')->count();

        $stats = [
            'gross_inflow' => $grossInflow,
            'gross_outflow' => $grossOutflow,
            'net_retained' => $netRetained,
            'rental_revenue' => $rentalRevenue,
            'earnest_deposits' => $earnestDeposits,
            'booking_revenue' => $bookingRevenue,
            'developer_deposits' => $developerDeposits,
            'platform_fees' => $platformFees,
            'disbursed_commissions' => $disbursedCommissions,
            'vat_accrued' => $vatAccrued,
            'vat_reversed' => $vatReversed,
            'vat_remitted' => $vatRemitted,
            'vat_balance' => $vatBalance,
            'legacy_ledger_entries' => $legacyEntries,
            'legacy_inflow' => $legacyInflow,
            'legacy_outflow' => $legacyOutflow,
            'total_withholding_tax' => $totalWithholdingTax,
            'pending_commissions_count' => $pendingCommissionsCount,
            'total_pending_commissions' => $totalPendingCommissions,
            'total_paid_commissions' => $totalPaidCommissions,
            'total_net_paid_out' => $totalNetPaidOut,
            'company_retained_commissions' => $companyRetainedCommissions,
            'co_brokered_deals_count' => $coBrokeredDealsCount,
        ];

        // Leases
        $leaseQuery = Lease::query()->with(['property', 'agent', 'schedules.payment']);
        if (! $isAdmin && ! $isFinanceOfficer && $user->agentProfile) {
            $leaseQuery->where('agent_id', $user->agentProfile->id);
        }
        $leases = $leaseQuery->latest()->get();

        // Pending payments
        $pendingPayments = Payment::query()
            ->with(['property', 'user', 'payable'])
            ->where('status', 'pending')
            ->when($scopedAgent, fn ($paymentQuery) => $paymentQuery->where(function ($query) use ($scopedAgent, $user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('property', fn ($propertyQuery) => $propertyQuery->where('agent_id', $scopedAgent->id));
            }))
            ->latest()
            ->get();

        // Refundable payments
        $completedPayments = Payment::query()
            ->with(['property', 'user'])
            ->whereIn('status', ['completed', 'partially_refunded'])
            ->latest()
            ->take(30)
            ->get();

        $rentSchedules = RentSchedule::query()
            ->whereIn('lease_id', $leases->pluck('id'))
            ->with('lease.property')
            ->latest()
            ->get();

        $frontDeskShifts = FrontDeskShift::query()
            ->with(['receptionist', 'property', 'financeOfficer'])
            ->when($scopedAgent, fn ($shiftQuery) => $shiftQuery->whereHas(
                'property',
                fn ($propertyQuery) => $propertyQuery->where('agent_id', $scopedAgent->id)
            ))
            ->latest()
            ->get();

        // Batch 4: Invoices, Payouts, Subscriptions
        $invoices = Invoice::query()->with(['user', 'property'])->latest()->paginate(10, ['*'], 'invoices_page')->withQueryString();
        $payouts = PayoutRequest::query()->with(['user', 'processor'])->latest()->paginate(10, ['*'], 'payouts_page')->withQueryString();
        $subscriptions = CompanyWhiteLabel::query()->with('user')->latest()->get();

        $actionableTasks = app(WorkflowIntegrationService::class)->getActorActionableTasks($user);
        $financeCharts = $scopedAgent ? null : app(AnalyticsService::class)->getFinanceChartData();

        // Payout desk (SRS §194): the available balance is ledger-derived, and
        // the threshold, schedule and holdback are the configured policy.
        $settings = app(FinanceSettings::class);
        $balanceService = app(AgentBalanceService::class);
        $payoutBalance = $balanceService->payoutableFor($user);
        $payoutHeld = $balanceService->heldFor($user);
        $payoutMinimum = $settings->payoutMinimumThreshold();
        $payoutSchedule = $settings->payoutSchedule();
        $payoutHoldbackRate = $settings->payoutHoldbackRate();
        $payoutScheduleOpen = app(PayoutService::class)->scheduleAllows($user);

        // Rollback-safety flags for the finance desk to review.
        $financeReviewFlags = FinanceReviewFlag::with(['payment', 'reviewable'])
            ->whereNull('resolved_at')
            ->latest()
            ->take(20)
            ->get();

        $pendingRates = FinanceRate::where('status', FinanceRate::STATUS_PENDING)->latest()->take(5)->get();

        return view('dashboards.finance', compact(
            'ledgers',
            'commissions',
            'stats',
            'dateFilter',
            'isAdmin',
            'isFinanceOfficer',
            'scopedAgent',
            'leases',
            'rentSchedules',
            'pendingPayments',
            'completedPayments',
            'frontDeskShifts',
            'invoices',
            'payouts',
            'subscriptions',
            'actionableTasks',
            'financeCharts',
            'payoutBalance',
            'payoutHeld',
            'payoutMinimum',
            'payoutSchedule',
            'payoutHoldbackRate',
            'payoutScheduleOpen',
            'financeReviewFlags',
            'pendingRates'
        ));
    }

    /**
     * Process refund as a financial adjustment entry in the ledger (SRS §132).
     * Financial records are never overwritten, only adjusted.
     *
     * The refundable ceiling is the original amount minus what has already been
     * refunded, so a payment can never be refunded twice over.
     */
    public function issueRefund(Request $request, Payment $payment): RedirectResponse
    {
        $user = $request->user();
        if (! $user || (! $user->isFinanceStaff() && ! $user->hasPermission('refunds.manage'))) {
            abort(403, 'Permission denied: refunds.manage required to issue refunds.');
        }

        $refunds = app(RefundService::class);

        $refundable = $refunds->refundableAmount($payment);

        abort_if($refundable <= 0, 422, 'This payment has already been refunded in full.');

        $validated = $request->validate([
            'refund_amount' => ['required', 'numeric', 'min:0.01', 'max:'.$refundable],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        // A retried refund must not refund twice (SRS §132). The caller supplies
        // an idempotency key; the same key reuses the same ledger group, and a
        // group that is already posted is a no-op.
        $idempotencyKey = $request->header('Idempotency-Key') ?? $request->input('idempotency_key');
        $idempotencyKey = is_string($idempotencyKey) && $idempotencyKey !== '' ? $idempotencyKey : null;

        $refunded = $refunds->refund($payment, (float) $validated['refund_amount'], $validated['reason'], $user, $idempotencyKey);

        if ($refunded <= 0.0) {
            return back()->with('info', 'This refund was already processed.');
        }

        return back()->with('success', "Refund adjustment of {$refunded} ETB recorded for Payment #{$payment->transaction_number}.");
    }

    /**
     * Create tax-compliant invoice with 15% VAT and 2% Withholding Tax.
     */
    public function storeInvoice(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || (! $user->isFinanceStaff() && ! $user->hasPermission('invoices.manage') && ! $user->hasPermission('invoices.create'))) {
            abort(403, 'Permission denied: invoices.manage permission required.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'user_id' => ['nullable', 'exists:users,id'],
            'property_id' => ['nullable', 'exists:properties,id'],
            'subtotal' => ['required', 'numeric', 'min:1'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $subtotal = (float) $validated['subtotal'];
        $settings = app(FinanceSettings::class);
        $vatRate = $settings->vatRate();
        $vatAmount = round($subtotal * $vatRate / 100, 2);
        $whtRate = $settings->withholdingTaxRate();
        $whtAmount = round($subtotal * $whtRate / 100, 2);
        $totalAmount = round($subtotal + $vatAmount - $whtAmount, 2);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-ETH-'.date('Y').'-'.strtoupper(substr(uniqid(), -6)),
            'title' => $validated['title'],
            'user_id' => $validated['user_id'] ?? null,
            'property_id' => $validated['property_id'] ?? null,
            'subtotal' => $subtotal,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'withholding_tax_rate' => $whtRate,
            'withholding_tax_amount' => $whtAmount,
            'total_amount' => $totalAmount,
            'status' => 'unpaid',
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'items' => [
                ['description' => $validated['title'], 'amount' => $subtotal],
            ],
        ]);

        AuditLog::log(
            user: $user,
            action: 'invoice.created',
            entity: $invoice,
            oldValue: null,
            newValue: ['invoice_number' => $invoice->invoice_number, 'total_amount' => $invoice->total_amount],
            reason: 'Finance Officer created tax invoice',
            request: $request
        );

        return back()->with('success', "Invoice {$invoice->invoice_number} created successfully.");
    }

    /**
     * Update invoice payment status.
     */
    public function updateInvoiceStatus(Request $request, Invoice $invoice): RedirectResponse
    {
        $user = $request->user();
        if (! $user || (! $user->isFinanceStaff() && ! $user->hasPermission('invoices.manage') && ! $user->hasPermission('invoices.update'))) {
            abort(403, 'Permission denied: invoices.manage permission required.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:paid,cancelled,overdue,unpaid'],
        ]);

        $oldStatus = $invoice->status;
        $invoice->update([
            'status' => $validated['status'],
            'paid_at' => $validated['status'] === 'paid' ? now() : null,
        ]);

        AuditLog::log(
            user: $user,
            action: 'invoice.status_updated',
            entity: $invoice,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => $invoice->status],
            reason: "Invoice status updated to {$invoice->status}",
            request: $request
        );

        return back()->with('success', "Invoice {$invoice->invoice_number} marked as {$invoice->status}.");
    }

    /**
     * Submit a payout request against the ledger-derived available balance
     * (SRS §194). The minimum threshold, the payout schedule and the holdback
     * are all enforced by the one PayoutService.
     */
    public function storePayoutRequest(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $validated = $request->validate([
            'gross_amount' => ['required', 'numeric', 'min:0.01'],
            'role' => ['nullable', 'string', 'in:host,seller,agent,professional'],
            'payment_method' => ['required', 'string', 'in:bank_transfer,telebirr,cbe_birr'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
        ]);

        $payout = app(PayoutService::class)->request(
            party: $user,
            gross: (float) $validated['gross_amount'],
            method: $validated['payment_method'],
            bankName: $validated['bank_name'] ?? null,
            accountNumber: $validated['account_number'] ?? null,
            role: $validated['role'] ?? ($user->role ?: 'host'),
        );

        return back()->with('success', "Payout request {$payout->payout_code} for {$payout->net_amount} ETB submitted (after {$payout->withholding_tax} ETB WHT).");
    }

    /**
     * Approve, reject, or disburse a payout request (SRS §194).
     *
     * Maker-checker: the requester can never approve or disburse their own
     * payout. Disbursement applies the withholding tax, issues a voucher number
     * and records the bank reference as a manual disbursement.
     */
    public function processPayout(Request $request, PayoutRequest $payout): RedirectResponse
    {
        $user = $request->user();
        if (! $user || (! $user->isFinanceStaff() && ! $user->hasPermission('payouts.update') && ! $user->hasPermission('payments.manage'))) {
            abort(403, 'Permission denied: payouts.update permission required.');
        }

        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:approve,reject,disburse'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $service = app(PayoutService::class);

        match ($validated['decision']) {
            'approve' => $service->approve($user, $payout),
            'reject' => $service->reject($user, $payout, $validated['rejection_reason'] ?? null),
            'disburse' => $service->disburse(
                $user,
                $payout,
                $validated['transaction_reference'] ?? null,
                $request->header('Idempotency-Key'),
            ),
        };

        return back()->with('success', "Payout #{$payout->payout_code} set to ".strtoupper($payout->fresh()->status).'.');
    }

    /**
     * Update corporate subscription.
     */
    public function updateSubscription(Request $request, CompanyWhiteLabel $subscription): RedirectResponse
    {
        $user = $request->user();
        if (! $user || (! $user->isFinanceStaff() && ! $user->hasPermission('subscriptions.manage'))) {
            abort(403, 'Permission denied: subscriptions.manage permission required.');
        }

        $validated = $request->validate([
            'subscription_tier' => ['required', 'string', 'in:standard,premium,enterprise'],
            'months' => ['required', 'integer', 'min:1', 'max:36'],
        ]);

        $oldTier = $subscription->subscription_tier;
        $subscription->update([
            'subscription_tier' => $validated['subscription_tier'],
            'subscription_expires_at' => now()->addMonths((int) $validated['months']),
            'is_active' => true,
        ]);

        AuditLog::log(
            user: $user,
            action: 'subscription.extended',
            entity: $subscription,
            oldValue: ['tier' => $oldTier],
            newValue: ['tier' => $subscription->subscription_tier, 'expires_at' => $subscription->subscription_expires_at],
            reason: "Finance Officer updated subscription to {$subscription->subscription_tier} for {$validated['months']} months",
            request: $request
        );

        return back()->with('success', "Subscription for {$subscription->company_name} updated to {$subscription->subscription_tier}.");
    }

    /**
     * Verify or flag discrepancy on front desk shift reconciliation (Finance Staff Only).
     */
    public function verifyShift(Request $request, FrontDeskShift $shift): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Unauthorized to verify front desk shifts.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:verified_by_finance,discrepancy_flagged'],
            'finance_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $shift->update([
            'status' => $validated['status'],
            'finance_notes' => $validated['finance_notes'] ?? null,
            'finance_officer_id' => $user->id,
            'finance_verified_at' => now(),
        ]);

        $label = $validated['status'] === 'verified_by_finance' ? 'verified' : 'flagged for discrepancy';

        return back()->with('success', "Front desk shift {$shift->shift_reference} has been {$label}.");
    }

    /**
     * Settle an agent commission payout with 2% Ethiopian Withholding Tax (Admin Only, SRS §126–§135).
     */
    public function settleCommission(Request $request, Commission $commission): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Only finance staff can disburse agent commissions.');
        }

        $validated = $request->validate([
            'payout_method' => ['required', 'string', 'in:cbe_birr,telebirr,bank_transfer,check,cash'],
            'payout_reference' => ['nullable', 'string', 'max:100'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:30'],
        ]);

        $taxRate = (float) ($validated['tax_rate'] ?? app(FinanceSettings::class)->withholdingTaxRate());
        $commission->settlePayout(
            disbursedBy: $user,
            method: $validated['payout_method'],
            reference: $validated['payout_reference'] ?? null,
            note: $validated['admin_note'] ?? null,
            taxRate: $taxRate
        );

        // Notify agent if user account exists
        $agentUser = $commission->agent?->user;
        if ($agentUser) {
            $agentUser->notify(new CommissionGeneratedNotification($commission, 'settled'));
        }

        ActivityLog::record(
            'commission_settled',
            "Finance Officer {$user->name} settled commission {$commission->voucher_number} of ETB ".number_format($commission->net_payout_amount, 2)." to {$commission->agent?->name}.",
            $commission
        );

        return back()->with('success', "Commission {$commission->voucher_number} of ".number_format($commission->net_payout_amount, 2)." ETB successfully disbursed to {$commission->agent?->name} (2% WHT: ".number_format($commission->withholding_tax_amount, 2).' ETB).');
    }

    /**
     * Record a new dated finance rate (SRS §171).
     *
     * A rate change is a new fact, never an edit: it needs the
     * `finance.settings.update` permission, a written reason, is audit-logged,
     * and carries an effective date so it applies only from that day forward.
     * Records already written keep the rate snapshotted on them, so history is
     * never recalculated.
     */
    public function updateFinanceRate(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermission('finance.settings.update')) {
            abort(403, 'Permission denied: finance.settings.update required to change finance rates.');
        }

        $validated = $request->validate([
            'key' => ['required', 'string', 'in:'.implode(',', [
                FinanceRate::PLATFORM_FEE,
                FinanceRate::VAT,
                FinanceRate::WITHHOLDING,
                FinanceRate::COMMISSION,
                FinanceRate::EXPERIENCE_FEE,
            ])],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'effective_from' => ['required', 'date', 'after_or_equal:today'],
            'location' => ['nullable', 'string', 'max:100'],
            'service' => ['nullable', 'string', 'max:100'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $settings = app(FinanceSettings::class);
        $oldRate = $settings->rateForRule($validated['key']);

        // The optional second-approval setting holds a change in `pending` until
        // a *different* finance user approves it (SRS §171 governance).
        $needsApproval = $settings->requiresSecondRateApproval();

        $rate = FinanceRate::create([
            'key' => $validated['key'],
            'rate' => $validated['rate'],
            'effective_from' => $validated['effective_from'],
            'location' => $validated['location'] ?? null,
            'service' => $validated['service'] ?? null,
            'created_by' => $user->id,
            'reason' => $validated['reason'],
            'status' => $needsApproval ? FinanceRate::STATUS_PENDING : FinanceRate::STATUS_APPROVED,
            'approved_by' => $needsApproval ? null : $user->id,
            'approved_at' => $needsApproval ? null : now(),
        ]);

        AuditLog::log(
            user: $user,
            action: 'finance.rate_updated',
            entity: $rate,
            oldValue: ['key' => $validated['key'], 'rate' => $oldRate],
            newValue: [
                'key' => $rate->key,
                'rate' => (float) $rate->rate,
                'previous_rate' => $oldRate,
                'effective_from' => $rate->effective_from?->toDateString(),
                'status' => $rate->status,
            ],
            reason: $validated['reason'],
            request: $request
        );

        if ($needsApproval) {
            return back()->with('success', "Finance rate {$rate->key} change to {$rate->rate}% is pending a second approver.");
        }

        return back()->with('success', "Finance rate {$rate->key} set to {$rate->rate}% effective {$rate->effective_from?->toDateString()}.");
    }

    /**
     * Approve a pending finance rate change (SRS §171 governance).
     *
     * Maker-checker: the user who requested the change can never approve it, and
     * only an approved row is ever used to move money.
     */
    public function approveFinanceRate(Request $request, FinanceRate $rate): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermission('finance.settings.approve')) {
            abort(403, 'Permission denied: finance.settings.approve required to approve a rate change.');
        }

        abort_if($rate->status === FinanceRate::STATUS_APPROVED, 422, 'This rate change is already approved.');
        abort_if((int) $rate->created_by === (int) $user->id, 403, 'The requester of a rate change cannot approve it.');

        $rate->update([
            'status' => FinanceRate::STATUS_APPROVED,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        AuditLog::log(
            user: $user,
            action: 'finance.rate_approved',
            entity: $rate,
            oldValue: ['status' => FinanceRate::STATUS_PENDING],
            newValue: ['status' => $rate->status, 'key' => $rate->key, 'rate' => (float) $rate->rate],
            reason: 'Second-person approval of a finance rate change',
            request: $request
        );

        return back()->with('success', "Finance rate {$rate->key} ({$rate->rate}%) approved.");
    }

    /**
     * Record a remittance of collected VAT to the tax authority (SRS §170).
     *
     * Posts the balanced journal "debit vat_payable, credit cash" with the
     * authority's reference, so the VAT report shows what is still owed.
     */
    public function remitVat(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermission('tax.manage')) {
            abort(403, 'Permission denied: tax.manage required to remit VAT.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'reference' => ['required', 'string', 'max:100'],
        ]);

        $amount = round((float) $validated['amount'], 2);

        DB::transaction(function () use ($validated, $amount, $user) {
            $remittance = VatRemittance::create([
                'amount' => $amount,
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'reference' => $validated['reference'],
                'created_by' => $user->id,
            ]);

            $group = 'vat-remittance:'.$remittance->id;
            $remittance->update(['ledger_group' => $group]);

            app(LedgerService::class)->post($group, [
                [
                    'entry_type' => 'debit',
                    'category' => 'vat_payable',
                    'amount' => $amount,
                    'currency' => 'ETB',
                    'description' => "VAT remitted to the tax authority ({$validated['reference']})",
                    'reference' => $validated['reference'],
                    'user_id' => $user->id,
                ],
                [
                    'entry_type' => 'credit',
                    'category' => 'cash',
                    'amount' => $amount,
                    'currency' => 'ETB',
                    'description' => "Cash paid to the tax authority for VAT ({$validated['reference']})",
                    'reference' => $validated['reference'],
                    'user_id' => $user->id,
                ],
            ]);

            AuditLog::log(
                user: $user,
                action: 'finance.vat_remitted',
                entity: $remittance,
                oldValue: null,
                newValue: ['amount' => $amount, 'reference' => $validated['reference'], 'ledger_group' => $group],
                reason: "VAT remittance for {$validated['period_start']} to {$validated['period_end']}",
                request: request()
            );
        });

        return back()->with('success', 'VAT remittance of '.number_format($amount, 2).' ETB recorded.');
    }

    /**
     * Bank reference matching for reconciliation (SRS §133).
     *
     * Lists the references that still need matching and lets finance settle a
     * pending payment by its bank reference.
     */
    public function reconciliation(Request $request): View
    {
        $user = $request->user();
        if (! $user || (! $user->isFinanceStaff() && ! $user->hasPermission('reconciliation.view') && ! $user->hasPermission('reconciliation.manage'))) {
            abort(403, 'Unauthorized to view reconciliation.');
        }

        $pending = Payment::query()
            ->where('status', 'pending')
            ->whereNotNull('bank_transaction_reference')
            ->latest()
            ->take(50)
            ->get();

        $matched = Payment::query()
            ->where('status', 'completed')
            ->whereNotNull('bank_transaction_reference')
            ->latest()
            ->take(50)
            ->get();

        $payoutReferences = PayoutRequest::query()
            ->whereNotNull('transaction_reference')
            ->latest()
            ->take(50)
            ->get();

        return view('finance.reconciliation', compact('pending', 'matched', 'payoutReferences'));
    }

    /**
     * Match a bank reference to a payment and settle it (SRS §82, §133).
     *
     * The matcher can never be the payer who submitted the payment (SRS §83).
     */
    public function matchBankReference(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || (! $user->isFinanceStaff() && ! $user->hasPermission('reconciliation.manage') && ! $user->hasPermission('payments.manage'))) {
            abort(403, 'Permission denied: reconciliation.manage required to match bank references.');
        }

        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:100'],
        ]);

        $payment = Payment::query()
            ->where('bank_transaction_reference', $validated['reference'])
            ->latest('id')
            ->first();

        abort_if($payment === null, 404, 'No payment found for that bank reference.');

        // A payer can never verify their own payment (SRS §83).
        abort_if((int) $payment->user_id === (int) $user->id, 403, 'You cannot match and settle your own payment.');

        if ($payment->status === 'pending') {
            $payment->settle();
            $payment->update([
                'status' => 'completed',
                'verified_at' => now(),
                'verified_by' => $user->id,
            ]);
        }

        AuditLog::log(
            user: $user,
            action: 'finance.bank_reference_matched',
            entity: $payment,
            oldValue: ['status' => 'pending'],
            newValue: ['status' => $payment->status, 'reference' => $validated['reference']],
            reason: 'Bank reference matched to payment during reconciliation',
            request: $request
        );

        return back()->with('success', "Bank reference {$validated['reference']} matched to Payment #{$payment->transaction_number}.");
    }

    /**
     * VAT report: the `vat_payable` balance by period (SRS §170).
     */
    public function vatReport(Request $request): View
    {
        $user = $request->user();
        if (! $user || (! $user->isFinanceStaff() && ! $user->hasPermission('tax.view') && ! $user->hasPermission('tax.manage'))) {
            abort(403, 'Unauthorized to view the VAT report.');
        }

        $legs = FinancialLedger::query()
            ->where('category', 'vat_payable')
            ->where('is_legacy', false)
            ->orderBy('created_at')
            ->get();

        $byPeriod = $legs->groupBy(fn ($leg) => $leg->created_at->format('Y-m'))
            ->map(function ($rows, $period) {
                $collected = round((float) $rows->where('entry_type', 'credit')->sum('amount'), 2);
                $remitted = round((float) $rows->where('entry_type', 'debit')
                    ->filter(fn ($leg) => str_starts_with((string) $leg->transaction_group, 'vat-remittance:'))
                    ->sum('amount'), 2);
                $reversed = round((float) $rows->where('entry_type', 'debit')->sum('amount') - $remitted, 2);

                return [
                    'period' => $period,
                    'collected' => $collected,
                    'reversed' => $reversed,
                    'remitted' => $remitted,
                    'net' => round($collected - $reversed - $remitted, 2),
                ];
            })
            ->values();

        $vatBalance = round(
            (float) $legs->where('entry_type', 'credit')->sum('amount')
            - (float) $legs->where('entry_type', 'debit')->sum('amount'),
            2
        );

        $remittances = VatRemittance::with('creator')->latest()->take(20)->get();

        return view('finance.vat-report', compact('byPeriod', 'vatBalance', 'remittances'));
    }

    /**
     * View official printable Ethiopian Commission Disbursement Voucher (SRS §135).
     */
    public function voucher(Commission $commission): View
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        $agentId = $user->agentProfile?->id;
        $isRelatedAgent = $agentId && (
            $commission->agent_id === $agentId
            || $commission->listing_agent_id === $agentId
            || $commission->selling_agent_id === $agentId
            || $commission->referral_agent_id === $agentId
        );

        if (! $user->isAdmin() && ! $user->isFinanceOfficer() && ! $isRelatedAgent) {
            abort(403, 'Unauthorized access to commission voucher.');
        }

        $commission->load(['agent', 'listingAgent', 'sellingAgent', 'referralAgent', 'property', 'order', 'lease', 'disbursedBy']);

        return view('finance.voucher', compact('commission'));
    }

    /**
     * Export financial ledgers into auditable CSV format (SRS §133).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Unauthorized to export financial statements.');
        }

        $filename = 'GTM-Financial-Ledgers-'.date('Ymd-His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Ledger Code',
                'Date & Time',
                'Entry Type',
                'Category',
                'Gross Amount (ETB)',
                'Currency',
                'Reference / Txn #',
                'Description',
                'Recorded By',
                'Books',
            ]);

            FinancialLedger::with(['user'])->orderBy('created_at')->chunk(200, function ($ledgers) use ($handle) {
                foreach ($ledgers as $ledger) {
                    fputcsv($handle, [
                        $ledger->ledger_code,
                        $ledger->created_at->format('Y-m-d H:i:s'),
                        strtoupper($ledger->entry_type),
                        ucwords(str_replace('_', ' ', $ledger->category)),
                        number_format($ledger->amount, 2, '.', ''),
                        $ledger->currency,
                        $ledger->reference ?: '-',
                        $ledger->description,
                        $ledger->user?->name ?? 'System',
                        $ledger->is_legacy ? 'Legacy (single-sided)' : 'Current (balanced journal)',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Printable Official Ethiopian Financial Audit Statement (SRS §134).
     */
    public function statement(Request $request): View
    {
        $user = $request->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Unauthorized to view financial audit statement.');
        }

        $period = $request->input('period', 'this_month');
        $query = FinancialLedger::query();

        match ($period) {
            'this_month' => $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]),
            'last_month' => $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]),
            'this_year' => $query->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]),
            default => null,
        };

        $ledgers = $query->with('user')->orderBy('created_at', 'desc')->get();
        $totalCredit = $ledgers->where('entry_type', 'credit')->sum('amount');
        $totalDebit = $ledgers->where('entry_type', 'debit')->sum('amount');
        $netBalance = $totalCredit - $totalDebit;

        return view('finance.statement', compact('ledgers', 'totalCredit', 'totalDebit', 'netBalance', 'period'));
    }
}
