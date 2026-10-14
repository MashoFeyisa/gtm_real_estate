<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\CompanyWhiteLabel;
use App\Models\FinancialLedger;
use App\Models\FrontDeskShift;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\PayoutRequest;
use App\Models\RentSchedule;
use App\Notifications\CommissionGeneratedNotification;
use App\Services\AnalyticsService;
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

        // Revenue Breakdown by category
        $grossInflow = (float) (clone $query)->where('entry_type', 'credit')->sum('amount');
        $grossOutflow = (float) (clone $query)->where('entry_type', 'debit')->sum('amount');
        $netRetained = round($grossInflow - $grossOutflow, 2);

        $rentalRevenue = (float) (clone $query)->where('entry_type', 'credit')->where('category', 'rental_revenue')->sum('amount');
        $earnestDeposits = (float) (clone $query)->where('entry_type', 'credit')->where('category', 'earnest_deposit')->sum('amount');
        $bookingRevenue = (float) (clone $query)->where('entry_type', 'credit')->where('category', 'booking_revenue')->sum('amount');
        $developerDeposits = (float) (clone $query)->where('entry_type', 'credit')->where('category', 'developer_deposit')->sum('amount');
        $platformFees = (float) (clone $query)->where('entry_type', 'credit')->where('category', 'platform_fee')->sum('amount');
        $disbursedCommissions = (float) (clone $query)->where('entry_type', 'debit')->where('category', 'agent_commission')->sum('amount');

        // Ethiopian Tax Breakdown (15% VAT on platform revenue + 2% Withholding Tax on agent payouts)
        $vatAccrued = round($platformFees * 0.15, 2);

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

        return view('dashboards.finance', compact(
            'ledgers',
            'commissions',
            'stats',
            'dateFilter',
            'isAdmin',
            'isFinanceOfficer',
            'leases',
            'rentSchedules',
            'pendingPayments',
            'completedPayments',
            'frontDeskShifts',
            'invoices',
            'payouts',
            'subscriptions',
            'actionableTasks',
            'financeCharts'
        ));
    }

    /**
     * Process refund as a financial adjustment entry in the ledger (SRS §132).
     * Financial records are never overwritten, only adjusted.
     */
    public function issueRefund(Request $request, Payment $payment): RedirectResponse
    {
        $user = $request->user();
        if (! $user || (! $user->isFinanceStaff() && ! $user->hasPermission('refunds.manage'))) {
            abort(403, 'Permission denied: refunds.manage required to issue refunds.');
        }

        $validated = $request->validate([
            'refund_amount' => ['required', 'numeric', 'min:1', 'max:'.$payment->amount],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $refundAmount = (float) $validated['refund_amount'];
        $reason = $validated['reason'];

        DB::transaction(function () use ($payment, $refundAmount, $reason, $user) {
            $isFullRefund = $refundAmount >= (float) $payment->amount;

            $payment->update([
                'status' => $isFullRefund ? 'refunded' : 'partially_refunded',
                'refunded_amount' => (float) $payment->refunded_amount + $refundAmount,
                'refund_reason' => $reason,
            ]);

            // Add immutable financial adjustment entry to ledger
            $ledgerCode = 'LED-ADJ-REF-'.strtoupper(substr(uniqid(), -8));
            FinancialLedger::create([
                'ledger_code' => $ledgerCode,
                'payment_id' => $payment->id,
                'entry_type' => 'debit',
                'category' => 'refund_adjustment',
                'amount' => $refundAmount,
                'currency' => $payment->currency ?? 'ETB',
                'description' => "Refund adjustment for Payment #{$payment->transaction_number}: {$reason}",
                'reference' => $payment->transaction_number,
                'user_id' => $user->id,
            ]);

            AuditLog::log(
                user: $user,
                action: 'payment.refund_issued',
                entity: $payment,
                oldValue: ['status' => $payment->getOriginal('status'), 'refunded_amount' => $payment->getOriginal('refunded_amount')],
                newValue: ['status' => $payment->status, 'refunded_amount' => $payment->refunded_amount, 'adjustment_code' => $ledgerCode],
                reason: "Refund adjustment: {$reason}",
            );
        });

        return back()->with('success', "Refund adjustment of {$refundAmount} ETB recorded for Payment #{$payment->transaction_number}.");
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
        $vatRate = 15.00;
        $vatAmount = round($subtotal * 0.15, 2);
        $whtRate = 2.00;
        $whtAmount = round($subtotal * 0.02, 2);
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
     * Submit Payout request with 2% withholding tax.
     */
    public function storePayoutRequest(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $validated = $request->validate([
            'gross_amount' => ['required', 'numeric', 'min:100'],
            'role' => ['nullable', 'string', 'in:host,seller,agent,professional'],
            'payment_method' => ['required', 'string', 'in:bank_transfer,telebirr,cbe_birr'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
        ]);

        $gross = (float) $validated['gross_amount'];
        $wht = round($gross * 0.02, 2); // 2% Ethiopian withholding tax
        $net = round($gross - $wht, 2);

        $payout = PayoutRequest::create([
            'payout_code' => 'PAY-REQ-'.strtoupper(substr(uniqid(), -8)),
            'user_id' => $user->id,
            'role' => $validated['role'] ?? ($user->role ?: 'host'),
            'gross_amount' => $gross,
            'withholding_tax' => $wht,
            'net_amount' => $net,
            'payment_method' => $validated['payment_method'],
            'bank_name' => $validated['bank_name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'status' => 'pending',
        ]);

        AuditLog::log(
            user: $user,
            action: 'payout.requested',
            entity: $payout,
            oldValue: null,
            newValue: ['gross_amount' => $gross, 'net_amount' => $net],
            reason: 'User submitted payout request',
            request: $request
        );

        return back()->with('success', "Payout request {$payout->payout_code} for {$net} ETB submitted (after 2% WHT: {$wht} ETB).");
    }

    /**
     * Approve, reject, or disburse payout request.
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

        $oldStatus = $payout->status;

        if ($validated['decision'] === 'disburse') {
            DB::transaction(function () use ($payout, $validated, $user) {
                $payout->update([
                    'status' => 'disbursed',
                    'transaction_reference' => $validated['transaction_reference'] ?? 'DISB-'.date('YmdHis'),
                    'processed_by' => $user->id,
                    'processed_at' => now(),
                ]);

                // Create ledger debit entry for payout disbursement
                FinancialLedger::create([
                    'ledger_code' => 'LED-OUT-'.strtoupper(substr(uniqid(), -8)),
                    'entry_type' => 'debit',
                    'category' => 'payout',
                    'amount' => $payout->net_amount,
                    'currency' => 'ETB',
                    'description' => "Payout disbursement #{$payout->payout_code} to user #{$payout->user_id}",
                    'reference' => $payout->transaction_reference,
                    'user_id' => $user->id,
                ]);
            });
        } elseif ($validated['decision'] === 'approve') {
            $payout->update([
                'status' => 'approved',
                'processed_by' => $user->id,
            ]);
        } else {
            // reject
            $payout->update([
                'status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'] ?? 'Rejected by finance audit.',
                'processed_by' => $user->id,
                'processed_at' => now(),
            ]);
        }

        AuditLog::log(
            user: $user,
            action: 'payout.processed',
            entity: $payout,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => $payout->status, 'ref' => $payout->transaction_reference],
            reason: "Finance decision: {$validated['decision']}",
            request: $request
        );

        return back()->with('success', "Payout #{$payout->payout_code} set to ".strtoupper($payout->status).'.');
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

        $taxRate = (float) ($validated['tax_rate'] ?? 2.0);
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
