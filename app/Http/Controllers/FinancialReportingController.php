<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Commission;
use App\Models\FinancialLedger;
use App\Models\FrontDeskShift;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\RentSchedule;
use App\Notifications\CommissionGeneratedNotification;
use App\Services\AnalyticsService;
use App\Services\WorkflowIntegrationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        ]);

        if (! $user || (! $user->isAdmin() && ! $user->isFinanceOfficer() && ! $hasFinancePerm && ! $user->agentProfile)) {
            abort(403, 'Unauthorized access to financial reports.');
        }

        $isAdmin = $user->isAdmin();
        $isFinanceOfficer = $user->isFinanceOfficer();
        $canViewPlatformBooks = $isAdmin || $isFinanceOfficer || $hasFinancePerm;

        // Agents join this desk only for their own deals: scope every ledger,
        // payment and shift query below to their portfolio. The platform-wide
        // books stay reserved for Admin and Finance Officers.
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

        $commissions = (clone $commissionsQuery)->latest()->get();
        $pendingCommissionsCount = $commissions->where('status', 'pending')->count();
        $totalPendingCommissions = (float) $commissions->where('status', 'pending')->sum('commission_amount');
        $totalPaidCommissions = (float) $commissions->where('status', 'paid')->sum('commission_amount');
        $totalWithholdingTax = (float) $commissions->where('status', 'paid')->sum('withholding_tax_amount');
        $totalNetPaidOut = (float) $commissions->where('status', 'paid')->sum('net_payout_amount');
        $companyRetainedCommissions = (float) $commissions->sum('company_retained_amount');
        $coBrokeredDealsCount = $commissions->where('split_type', 'co_brokered')->count();

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

        // Lease & rental collection desk (SRS §84–§88). Finance Officers manage existing
        // agreements here but are not permitted to register new lease agreements.
        $leaseQuery = Lease::query()->with(['property', 'agent', 'schedules.payment']);
        if (! $isAdmin && ! $isFinanceOfficer && $user->agentProfile) {
            $leaseQuery->where('agent_id', $user->agentProfile->id);
        }
        $leases = $leaseQuery->latest()->get();

        // Transactions awaiting finance confirmation (short stays, earnest deposits,
        // unit reservations and bank rent payments).
        $pendingPayments = Payment::query()
            ->with(['property', 'user', 'payable'])
            ->where('status', 'pending')
            ->when($scopedAgent, fn ($paymentQuery) => $paymentQuery->where(function ($query) use ($scopedAgent, $user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('property', fn ($propertyQuery) => $propertyQuery->where('agent_id', $scopedAgent->id));
            }))
            ->latest()
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

        $actionableTasks = app(WorkflowIntegrationService::class)->getActorActionableTasks($user);
        $financeCharts = $scopedAgent ? null : app(AnalyticsService::class)->getFinanceChartData();

        return view('dashboard.finance', compact(
            'ledgers',
            'commissions',
            'stats',
            'dateFilter',
            'isAdmin',
            'isFinanceOfficer',
            'leases',
            'rentSchedules',
            'pendingPayments',
            'frontDeskShifts',
            'actionableTasks',
            'financeCharts'
        ));
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
            // UTF-8 BOM for Excel compatibility with Amharic characters
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
