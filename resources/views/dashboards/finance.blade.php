@extends('dashboards.layout')

@section('portal_name', 'Finance & Treasury Desk')
@section('dashboard-title')
    Financial Reporting & Commission Settlement
@endsection
@section('dashboard-subtitle', 'Commission settlement, payment verification, lease collections, immutable double-entry ledgers, 15% VAT, 2% withholding tax, invoices, refunds, and payout disbursements.')

@php
    $roleLabel = $isAdmin ? 'Administrator' : ($isFinanceOfficer ? 'Finance Officer' : 'Agent');

    // Permission-driven desk: navigation, sections and alerts follow the viewer's grants.
    $canManagePayments = auth()->user()->hasPermission('payments.manage');
    $canManageCommissions = auth()->user()->hasPermission('commissions.manage');
    $canReconcileAccounts = auth()->user()->hasPermission('reconciliation.manage');

    $pendingCommissionCount = (int) $stats['pending_commissions_count'];
    $overdueSchedules = $rentSchedules->filter(fn ($schedule) => $schedule->is_overdue);
    $overdueInstallmentCount = $overdueSchedules->count();
    $activeLeaseCount = $leases->where('status', 'active')->count();
    $collectedRent = (float) $rentSchedules->where('status', 'paid')->sum('amount_paid');
    $paidInstallmentCount = $rentSchedules->where('status', 'paid')->count();
    $scheduledRent = (float) $rentSchedules->sum('amount_due');
    $outstandingRent = max(0, $scheduledRent - $collectedRent);
@endphp

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if ($canManagePayments)
        <a href="#payment-queue" data-target-section="payment-queue" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>💳</span>
                <span>Payments to Confirm</span>
            </span>
            @if ($pendingPayments->isNotEmpty())
                <span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-[10px] font-black text-[#9b6c17]">{{ $pendingPayments->count() }}</span>
            @endif
        </a>
    @endif
    <a href="#lease-rental" data-target-section="lease-rental" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>📑</span>
            <span>Manage Rental &amp; Lease</span>
        </span>
        @if ($overdueInstallmentCount > 0)
            <span class="rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white">{{ $overdueInstallmentCount }} overdue</span>
        @elseif ($activeLeaseCount > 0)
            <span class="rounded-full bg-[#102b25] border border-[#d4af37]/40 px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">{{ $activeLeaseCount }} active</span>
        @endif
    </a>
    @if ($canManageCommissions)
        <a href="#commissions" data-target-section="commissions" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>💼</span>
                <span>Receive Commission</span>
            </span>
            @if ($pendingCommissionCount > 0)
                <span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-[10px] font-black text-[#9b6c17]">{{ $pendingCommissionCount }}</span>
            @endif
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['ledger.view', 'finance.manage']))
        <a href="#ledger" data-target-section="ledger" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📒</span>
                <span>Double-Entry Ledgers</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $ledgers->total() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['refunds.manage', 'refunds.view']))
        <a href="#refunds" data-target-section="refunds" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>↩️</span>
                <span>Refunds & Adjustments</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $completedPayments->count() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['invoices.manage', 'invoices.view']))
        <a href="#invoices" data-target-section="invoices" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🧾</span>
                <span>Invoices & Billing</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $invoices->total() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['payouts.view', 'payouts.update']))
        <a href="#payouts" data-target-section="payouts" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>💸</span>
                <span>Payouts & Transfers</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $payouts->total() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['subscriptions.manage', 'subscriptions.view']))
        <a href="#subscriptions" data-target-section="subscriptions" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>💳</span>
                <span>Corporate Subscriptions</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $subscriptions->count() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['commissions.view', 'commissions.settle']))
        <a href="#commissions" data-target-section="commissions" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🤝</span>
                <span>Commissions Desk</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['pending_commissions_count'] }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['reconciliation.view', 'reconciliation.manage']))
        <a href="#reconciliation" data-target-section="reconciliation" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>⚖️</span>
                <span>Shift Reconciliation</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $frontDeskShifts->count() }}</span>
        </a>
    @endif
    <a href="#profile" data-target-section="profile" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>👤</span>
            <span>My Profile</span>
        </span>
    </a>
@endsection

@section('dashboard-content')
    <!-- Overview Section (Analytics & Charts) -->
    <div id="overview" data-section="overview" class="section-panel space-y-8 scroll-mt-6">
        {{-- Period Filter --}}
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center gap-2 overflow-x-auto text-xs">
                <span class="mr-1 text-[10px] font-bold uppercase text-slate-400">Period:</span>
                @foreach (['this_month' => 'This Month', 'last_month' => 'Last Month', 'this_quarter' => 'This Quarter', 'this_year' => 'This Year', 'all_time' => 'All Time'] as $key => $label)
                    <a href="{{ route('finance.index', ['period' => $key]) }}" class="whitespace-nowrap rounded-xl px-3 py-1.5 font-bold transition {{ $dateFilter === $key ? 'bg-[#102b25] text-[#d4af37]' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <form method="GET" action="{{ route('finance.index') }}" class="flex items-center gap-2 text-xs">
                <input type="hidden" name="period" value="custom">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="rounded-xl border border-slate-300 bg-white px-2.5 py-1 text-xs dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                <span class="text-slate-400">to</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="rounded-xl border border-slate-300 bg-white px-2.5 py-1 text-xs dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                <button type="submit" class="rounded-xl bg-[#102b25] px-3 py-1.5 font-bold text-xs text-white hover:bg-[#1d3c34]">Filter</button>
            </form>
        </div>

        {{-- Actionable Tasks --}}
        @if (isset($actionableTasks) && count($actionableTasks) > 0)
            <div class="rounded-2xl border border-amber-400 bg-gradient-to-r from-[#1d3c34] to-[#254d4d] p-5 text-white shadow-md">
                <div class="mb-2 flex items-center gap-2">
                    <span class="rounded bg-[#d4af37] px-2 py-0.5 text-[10px] font-black uppercase text-[#1d3c34]">🤖 Finance AI Assistant</span>
                    <span class="text-xs font-bold text-[#dfeee4]">Action Required</span>
                </div>
                <h3 class="mb-3 text-base font-black text-white">You have {{ count($actionableTasks) }} pending action items requiring financial verification:</h3>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($actionableTasks as $task)
                        <a href="{{ $task['url'] }}" class="flex items-center justify-between rounded-xl border border-white/15 bg-white/10 p-3 transition hover:bg-white/20">
                            <div class="flex items-center gap-2.5">
                                <span class="text-xs font-black">{{ $task['badge'] }}</span>
                                <span class="text-xs font-semibold text-[#dfeee4]">{{ $task['title'] }}</span>
                            </div>
                            <span class="text-xs font-bold text-[#d4af37]">&rarr;</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Pending Commission Alert --}}
        @if ($canManageCommissions && $pendingCommissionCount > 0)
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border-2 border-amber-400 bg-gradient-to-r from-[#fffdf5] via-[#fefbf0] to-[#fcf6e8] p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-xl font-black text-white shadow-sm">💼</span>
                    <div>
                        <h2 class="text-base font-black text-[#102b25]">{{ $pendingCommissionCount }} Agent Commission Payout(s) Awaiting Settlement</h2>
                        <p class="mt-0.5 text-xs text-slate-600">Totalling {{ number_format($stats['total_pending_commissions'], 2) }} ETB before 2% withholding tax deductions.</p>
                    </div>
                </div>
                <a href="#commissions" data-target-section="commissions" class="nav-section-link inline-flex items-center gap-2 rounded-full bg-[#102b25] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#d4af37] transition hover:bg-[#1d3c34] shadow-sm">
                    <span>Open Commission Desk &rarr;</span>
                </a>
            </div>
        @endif

        {{-- Pending Payment Alert --}}
        @if ($canManagePayments && $pendingPayments->isNotEmpty())
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border-2 border-amber-400 bg-gradient-to-r from-[#fffdf5] via-[#fefbf0] to-[#fcf6e8] p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-xl font-black text-white shadow-sm">💳</span>
                    <div>
                        <h2 class="text-base font-black text-[#102b25]">{{ $pendingPayments->count() }} Payment(s) Awaiting Confirmation</h2>
                        <p class="mt-0.5 text-xs text-slate-600">Totalling {{ number_format($pendingPayments->sum('amount'), 2) }} ETB. Short-stay reservations stay unconfirmed until the payment is verified and a receipt is issued.</p>
                    </div>
                </div>
                <a href="#payment-queue" data-target-section="payment-queue" class="nav-section-link inline-flex items-center gap-2 rounded-full bg-[#102b25] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#d4af37] transition hover:bg-[#1d3c34] shadow-sm">
                    <span>Open Confirmation Queue &rarr;</span>
                </a>
            </div>
        @endif

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-5">
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Gross Inflow (Credits)</span>
                <div class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['gross_inflow'], 2) }} <span class="text-xs font-normal text-slate-500">ETB</span></div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Gross Outflow (Debits)</span>
                <div class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($stats['gross_outflow'], 2) }} <span class="text-xs font-normal text-slate-500">ETB</span></div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-[#1d3c34] dark:text-emerald-400 uppercase tracking-wider">Net Retained Balance</span>
                <div class="text-2xl font-bold text-[#1d3c34] dark:text-emerald-400 mt-1">{{ number_format($stats['net_retained'], 2) }} <span class="text-xs font-normal text-slate-500">ETB</span></div>
            </div>
            <div class="bg-[#f4efe7] dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-[#1d3c34] dark:text-emerald-400 uppercase tracking-wider">Agency Retained Comm.</span>
                <div class="text-2xl font-bold text-[#1d3c34] dark:text-emerald-400 mt-1">{{ number_format($stats['company_retained_commissions'] ?? 0, 2) }} <span class="text-xs font-normal text-slate-500">ETB</span></div>
                <p class="mt-1 text-[11px] font-semibold text-slate-500">{{ $stats['co_brokered_deals_count'] ?? 0 }} co-brokered / multi-tier deals</p>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-amber-300 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Tax Provision (15% VAT & 2% WHT)</span>
                <div class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($stats['vat_accrued'] + $stats['total_withholding_tax'], 2) }} <span class="text-xs font-normal text-slate-500">ETB</span></div>
            </div>
        </div>

        {{-- Finance Overview Visualizations --}}
        @if (! empty($financeCharts))
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="flex flex-col gap-2 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#102b25]"></span>
                            <h2 class="text-xl font-black text-[#1d3c34] dark:text-white">Financial Analytics &amp; Capital Flow Intelligence</h2>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">Real ledger cash inflows, revenue streams, and lease rent collection performance.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-[#1d3c34] dark:bg-slate-700 dark:text-slate-200">
                        Audited Ledger
                    </span>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Chart 1: Cashflow Inflow vs Outflow -->
                    <div class="rounded-2xl border border-slate-100 bg-[#fbf9f5] p-4 lg:col-span-2 dark:border-slate-700 dark:bg-slate-900/40">
                        <div class="mb-2 flex items-center justify-between">
                            <h3 class="text-xs font-black uppercase tracking-wider text-[#1d3c34] dark:text-slate-200">Monthly Cash Inflow vs. Outflow (ETB)</h3>
                            <span class="text-[10px] font-bold text-slate-400">Past 6 Months</span>
                        </div>
                        <div id="finance-cashflow-chart" class="min-h-[280px]"></div>
                    </div>

                    <!-- Chart 2: Revenue Categories Streams -->
                    <div class="rounded-2xl border border-slate-100 bg-[#fbf9f5] p-4 dark:border-slate-700 dark:bg-slate-900/40">
                        <div class="mb-2 flex items-center justify-between">
                            <h3 class="text-xs font-black uppercase tracking-wider text-[#1d3c34] dark:text-slate-200">Revenue Stream Mix</h3>
                            <span class="text-[10px] font-bold text-slate-400">By Ledger Category</span>
                        </div>
                        <div id="finance-categories-chart" class="min-h-[280px] flex items-center justify-center"></div>
                    </div>
                </div>
            </div>
        @endif

        @include('dashboards.partials.analytics-charts')
    </div>

    @if ($canManagePayments)
        <!-- Payments Awaiting Confirmation -->
        <div id="payment-queue" data-section="payment-queue" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Payments Awaiting Confirmation</h3>
                <p class="text-xs text-slate-500">Manage payments submitted through bank transfer, Telebirr, CBE Birr, and uploaded deposit slips. Confirming a short-stay payment locks in the reservation and issues the guest's receipt automatically.</p>
            </div>

            @if ($pendingPayments->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-12 text-center dark:border-slate-700 dark:bg-slate-900/40">
                    <span class="text-3xl">✅</span>
                    <p class="mt-2 text-sm font-semibold text-slate-500">No payments are waiting for confirmation.</p>
                    <p class="mt-1 text-xs text-slate-400">New short-stay and installment payments appear here the moment a payer submits proof of payment.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="p-3">Transaction</th>
                                <th class="p-3">Payer &amp; Channel</th>
                                <th class="p-3">Category / Reservation</th>
                                <th class="p-3 text-right">Amount (ETB)</th>
                                <th class="p-3">Reference / Slip</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach ($pendingPayments as $pendingPayment)
                                @php $stayBooking = $pendingPayment->payable instanceof \App\Models\Booking ? $pendingPayment->payable : null; @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                                    <td class="p-3">
                                        <a href="{{ route('payments.receipt', $pendingPayment->transaction_number) }}" target="_blank" class="font-mono font-semibold text-[#1d3c34] underline decoration-dotted dark:text-emerald-400">{{ $pendingPayment->transaction_number }}</a>
                                        <span class="mt-0.5 block text-[10px] text-slate-400">{{ $pendingPayment->created_at?->format('M d, Y H:i') }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="block font-bold text-slate-800 dark:text-slate-100">{{ $pendingPayment->payer_name }}</span>
                                        <span class="mt-0.5 block text-[10px] text-slate-500">{{ $pendingPayment->payer_phone }} · {{ $pendingPayment->method_label }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $pendingPayment->type_label }}</span>
                                        @if ($pendingPayment->property)
                                            <span class="mt-0.5 block text-[10px] text-slate-500">{{ Str::limit($pendingPayment->property->title, 34) }}</span>
                                        @endif
                                        @if ($stayBooking)
                                            <span class="mt-1 block font-mono text-[10px] font-bold text-[#9b6c17] dark:text-amber-400">
                                                {{ $stayBooking->booking_reference }}
                                                · {{ $stayBooking->check_in_date?->format('M d') }} → {{ $stayBooking->check_out_date?->format('M d, Y') }}
                                                · {{ $stayBooking->total_nights }} night(s)
                                                @if ($stayBooking->payment_status)
                                                    · {{ ucwords(str_replace('_', ' ', $stayBooking->payment_status)) }}
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right">
                                        <span class="block font-mono font-bold text-slate-900 dark:text-white">{{ number_format($pendingPayment->amount, 2) }}</span>
                                        <span class="mt-0.5 block text-[10px] text-slate-400">Platform fee {{ number_format($pendingPayment->platform_fee, 2) }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="font-mono text-[11px] text-slate-600 dark:text-slate-300">{{ $pendingPayment->bank_transaction_reference ?? '—' }}</span>
                                        @if ($pendingPayment->deposit_slip_path)
                                            <a href="{{ route('payments.slip', $pendingPayment) }}" target="_blank" class="mt-0.5 block text-[10px] font-bold text-[#1d3c34] underline dark:text-emerald-400">View Deposit Slip</a>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right">
                                        <div class="flex flex-wrap items-center justify-end gap-1.5">
                                            <form method="POST" action="{{ route('payments.verify', $pendingPayment) }}">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="rounded-full bg-emerald-700 px-3 py-1 text-[11px] font-bold text-white transition hover:bg-emerald-800">✓ Confirm</button>
                                            </form>
                                            <form method="POST" action="{{ route('payments.reject', $pendingPayment) }}" onsubmit="return confirm('Reject this payment? The payer will be notified and can submit a new one.');">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="admin_notes" value="Rejected during finance desk verification.">
                                                <button type="submit" class="rounded-full bg-rose-600 px-3 py-1 text-[11px] font-bold text-white transition hover:bg-rose-700">✕ Reject</button>
                                            </form>
                                            <a href="{{ route('payments.receipt', $pendingPayment->transaction_number) }}" target="_blank" class="rounded-full border border-slate-300 bg-white px-3 py-1 text-[11px] font-bold text-[#1d3c34] transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">Receipt</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-[11px] text-slate-500">Confirming sends the payer an in-app notification plus a receipt link at <span class="font-mono">/payments/receipt/{transaction}</span>. Rejecting re-opens the booking for a new payment.</p>
            @endif
        </div>
    @endif

    <!-- Lease & Rental Collection Desk -->
    <div id="lease-rental" data-section="lease-rental" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Lease Desk</span>
                    <span class="text-xs font-bold text-slate-500">የረጅም ጊዜ ኪራይ እና ውል አስተዳደር</span>
                </div>
                <h2 class="mt-1 text-2xl font-black text-[#1d3c34] dark:text-white">Lease & Rental Collection Desk</h2>
                <p class="mt-1 text-xs text-slate-500">Track existing tenancy agreements, automated Ethiopian installment schedules, monthly collections, and lease renewals. Registering new lease agreements is handled from the Admin Dashboard or Agent Portal.</p>
            </div>
            <form method="POST" action="{{ route('leases.process-schedules') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-[#1d3c34] bg-[#1d3c34] px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#254d43]" title="Audit rent schedules and escalate overdue installments past grace period">
                    <span>⚡ Audit &amp; Escalation Check</span>
                </button>
            </form>
        </div>

        {{-- Lease Metrics Overview --}}
        <div class="my-6 grid grid-cols-2 gap-3.5 sm:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/40">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Active Leases</span>
                <p class="mt-1 font-mono text-xl font-black text-[#1d3c34] dark:text-emerald-400">{{ $activeLeaseCount }}</p>
                <span class="text-[11px] font-bold text-emerald-700">Of {{ $leases->count() }} total contracts</span>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/40">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Rent Collected</span>
                <p class="mt-1 font-mono text-xl font-black text-emerald-700">ETB {{ number_format($collectedRent, 2) }}</p>
                <span class="text-[11px] font-bold text-emerald-800 dark:text-emerald-400">{{ $paidInstallmentCount }} installments paid</span>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/40">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Overdue Installments</span>
                <p class="mt-1 font-mono text-xl font-black text-rose-700">{{ $overdueInstallmentCount }}</p>
                <span class="text-[11px] font-bold text-rose-800 dark:text-rose-400">Requires tenant follow-up</span>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/40">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Outstanding Balance</span>
                <p class="mt-1 font-mono text-xl font-black text-amber-800 dark:text-amber-400">ETB {{ number_format($outstandingRent, 2) }}</p>
                <span class="text-[11px] text-slate-500">Remaining contract receivables</span>
            </div>
        </div>

        {{-- Lease Tabs --}}
        <div class="mb-4 flex flex-wrap items-center gap-4 border-b border-slate-200 text-xs font-bold dark:border-slate-700">
            <button type="button" onclick="switchLeaseTab('contracts')" id="tab-btn-contracts" class="lease-tab-btn cursor-pointer border-b-2 border-[#1d3c34] pb-2 text-[#1d3c34] dark:text-white">
                Active Contracts ({{ $leases->count() }})
            </button>
            <button type="button" onclick="switchLeaseTab('schedules')" id="tab-btn-schedules" class="lease-tab-btn cursor-pointer border-b-2 border-transparent pb-2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                Rent Installment Schedules ({{ $rentSchedules->count() }})
            </button>
            <button type="button" onclick="switchLeaseTab('overdue')" id="tab-btn-overdue" class="lease-tab-btn cursor-pointer border-b-2 border-transparent pb-2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                Overdue Rent ({{ $overdueInstallmentCount }})
            </button>
        </div>

        {{-- TAB 1: CONTRACTS --}}
        <div id="lease-view-contracts" class="lease-tab-content">
            @if ($leases->isEmpty())
                <div class="py-12 text-center text-slate-400">
                    <span class="text-3xl">📄</span>
                    <p class="mt-2 font-bold text-slate-600 dark:text-slate-300">No Lease Contracts Registered Yet</p>
                    <p class="mt-1 text-xs">Lease agreements are enrolled from the Admin Dashboard or Agent Portal.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="p-3">Lease Code</th>
                                <th class="p-3">Property</th>
                                <th class="p-3">Tenant</th>
                                <th class="p-3 text-right">Monthly Rent</th>
                                <th class="p-3">Collection Progress</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach ($leases as $lease)
                                @php
                                    $leaseTotalDue = $lease->total_rent_due ?: 1;
                                    $leasePaidPercentage = min(100, (int) round(($lease->total_rent_paid / $leaseTotalDue) * 100));
                                    $leaseStatusBadge = match ($lease->status) {
                                        'active' => ['Active', 'bg-emerald-100 text-emerald-800'],
                                        'pending_renewal' => ['Pending Renewal', 'bg-amber-100 text-amber-800'],
                                        'expired' => ['Expired', 'bg-rose-100 text-rose-800'],
                                        'terminated' => ['Terminated', 'bg-slate-200 text-slate-700'],
                                        default => ['Draft', 'bg-slate-100 text-slate-600'],
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                                    <td class="p-3 font-mono font-bold text-slate-700 dark:text-slate-300">
                                        {{ $lease->lease_code }}
                                        <span class="block font-normal text-[10px] text-slate-400">Freq: {{ ucfirst($lease->payment_frequency) }}</span>
                                    </td>
                                    <td class="p-3 font-semibold text-slate-700 dark:text-slate-300">
                                        {{ $lease->property?->title ?? '—' }}
                                        <span class="block text-[10px] font-normal text-slate-400">{{ $lease->property?->city ?? 'Addis Ababa' }}</span>
                                    </td>
                                    <td class="p-3 text-slate-600 dark:text-slate-300">
                                        {{ $lease->tenant_name }}
                                        <span class="block text-[10px] text-slate-400">{{ $lease->tenant_phone }} · {{ $lease->tenant_email }}</span>
                                    </td>
                                    <td class="p-3 text-right font-black text-[#1d3c34] dark:text-emerald-400">{{ number_format($lease->monthly_rent, 2) }}</td>
                                    <td class="p-3">
                                        <div class="mb-1 flex items-center justify-between text-[10px] font-bold text-slate-600 dark:text-slate-300">
                                            <span>ETB {{ number_format($lease->total_rent_paid, 0) }}</span>
                                            <span>{{ $leasePaidPercentage }}%</span>
                                        </div>
                                        <div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                            <div class="h-1.5 rounded-full bg-emerald-600" style="width: {{ $leasePaidPercentage }}%"></div>
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $leaseStatusBadge[1] }}">{{ $leaseStatusBadge[0] }}</span>
                                    </td>
                                    <td class="p-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('leases.agreement', $lease) }}" target="_blank" class="rounded-full border border-[#102b25] bg-[#102b25] px-2.5 py-1 text-[10px] font-bold text-[#d4af37] transition hover:bg-[#1d3c34]">
                                                Agreement PDF
                                            </a>
                                            <button type="button" onclick="viewLeaseSchedule({{ $lease->id }})" class="cursor-pointer rounded-full border border-slate-300 bg-white px-2.5 py-1 text-[10px] font-bold text-[#1d3c34] transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                                Schedule
                                            </button>
                                            <form method="POST" action="{{ route('leases.update-status', $lease) }}" class="flex items-center gap-1">
                                                @csrf
                                                @method('PUT')
                                                <select name="status" class="rounded-lg border border-slate-300 bg-white px-1.5 py-1 text-[10px] font-bold text-slate-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
                                                    <option value="active" {{ $lease->status === 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="pending_renewal" {{ $lease->status === 'pending_renewal' ? 'selected' : '' }}>Pending Renewal</option>
                                                    <option value="expired" {{ $lease->status === 'expired' ? 'selected' : '' }}>Expired</option>
                                                    <option value="terminated" {{ $lease->status === 'terminated' ? 'selected' : '' }}>Terminated</option>
                                                    <option value="draft" {{ $lease->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                                </select>
                                                <button type="submit" class="cursor-pointer rounded-lg bg-[#102b25] px-2 py-1 text-[10px] font-bold text-white transition hover:bg-[#1d3c34]">Update</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- TAB 2: RENT INSTALLMENT SCHEDULES --}}
        <div id="lease-view-schedules" class="lease-tab-content hidden">
            @if ($rentSchedules->isEmpty())
                <div class="py-12 text-center text-slate-400">
                    <p class="font-bold text-slate-600 dark:text-slate-300">No Rent Installments Scheduled</p>
                    <p class="mt-1 text-xs">Schedules are generated automatically whenever a lease is registered.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="p-3">Period / Label</th>
                                <th class="p-3">Lease &amp; Property</th>
                                <th class="p-3">Tenant</th>
                                <th class="p-3">Due Date</th>
                                <th class="p-3">Amount Due</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach ($rentSchedules as $schedule)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                                    <td class="p-3 font-bold text-slate-800 dark:text-slate-100">{{ $schedule->period_label }}</td>
                                    <td class="p-3">
                                        <span class="font-mono font-bold text-[#1d3c34] dark:text-emerald-400">{{ $schedule->lease?->lease_code ?? 'N/A' }}</span>
                                        <span class="block text-[10px] text-slate-400">{{ Str::limit($schedule->lease?->property?->title ?? 'Property', 22) }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="block font-bold text-slate-700 dark:text-slate-200">{{ $schedule->lease?->tenant_name ?? 'Tenant' }}</span>
                                        <span class="block text-[10px] text-slate-400">{{ $schedule->lease?->tenant_phone }}</span>
                                    </td>
                                    <td class="p-3 font-mono">
                                        <span class="{{ $schedule->is_overdue ? 'font-bold text-rose-700' : 'text-slate-600 dark:text-slate-300' }}">
                                            {{ $schedule->due_date?->format('M d, Y') }}
                                        </span>
                                    </td>
                                    <td class="p-3 font-mono font-black text-[#1d3c34] dark:text-emerald-400">
                                        ETB {{ number_format($schedule->amount_due, 2) }}
                                        @if ($schedule->amount_paid > 0 && $schedule->status !== 'paid')
                                            <span class="block text-[10px] font-normal text-emerald-600">Paid: ETB {{ number_format($schedule->amount_paid, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        <span class="rounded-full border px-2.5 py-0.5 text-[10px] font-black {{ $schedule->status_badge[1] }}">
                                            {{ $schedule->status_badge[0] }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right">
                                        @if ($schedule->status === 'paid')
                                            @if ($schedule->payment)
                                                <a href="{{ route('payments.receipt', $schedule->payment->transaction_number) }}" target="_blank" class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-800 transition hover:bg-emerald-100">
                                                    Receipt →
                                                </a>
                                            @else
                                                <span class="text-[11px] font-bold text-emerald-700">Settled ✓</span>
                                            @endif
                                        @else
                                            <button type="button" onclick="openRecordRentPaymentModal({{ $schedule->id }}, @js($schedule->period_label), {{ (float) $schedule->amount_due }})" class="cursor-pointer rounded-full bg-[#102b25] px-2.5 py-1 text-[11px] font-bold text-[#d4af37] transition hover:bg-[#1d3c34]">
                                                + Pay Rent
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- TAB 3: OVERDUE RENT --}}
        <div id="lease-view-overdue" class="lease-tab-content hidden">
            @if ($overdueSchedules->isEmpty())
                <div class="py-12 text-center text-slate-400">
                    <span class="text-3xl">✅</span>
                    <p class="mt-2 text-sm font-bold text-emerald-800 dark:text-emerald-400">All Rent Installments Are Up To Date!</p>
                    <p class="mt-0.5 text-xs text-slate-500">No overdue rental schedules currently exist.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="border-b border-rose-200 bg-rose-50 text-[10px] font-bold uppercase text-rose-900 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-200">
                            <tr>
                                <th class="p-3">Period</th>
                                <th class="p-3">Tenant &amp; Contact</th>
                                <th class="p-3">Property</th>
                                <th class="p-3">Original Due Date</th>
                                <th class="p-3">Overdue Amount</th>
                                <th class="p-3 text-right">Collect Rent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rose-100 bg-white dark:divide-rose-900/50 dark:bg-slate-800">
                            @foreach ($overdueSchedules as $schedule)
                                <tr class="hover:bg-rose-50/40 dark:hover:bg-rose-950/30">
                                    <td class="p-3 font-bold text-rose-950 dark:text-rose-200">{{ $schedule->period_label }}</td>
                                    <td class="p-3">
                                        <span class="block font-bold text-slate-800 dark:text-slate-100">{{ $schedule->lease?->tenant_name }}</span>
                                        <span class="block text-[10px] text-slate-600 dark:text-slate-400">{{ $schedule->lease?->tenant_phone }} · {{ $schedule->lease?->tenant_email }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="block font-bold text-[#1d3c34] dark:text-emerald-400">{{ Str::limit($schedule->lease?->property?->title ?? 'Property', 25) }}</span>
                                        <span class="block font-mono text-[10px] text-slate-500">{{ $schedule->lease?->lease_code }}</span>
                                    </td>
                                    <td class="p-3 font-mono font-bold text-rose-700">{{ $schedule->due_date?->format('M d, Y') }}</td>
                                    <td class="p-3 font-mono font-black text-rose-800 dark:text-rose-300">ETB {{ number_format($schedule->amount_due - $schedule->amount_paid, 2) }}</td>
                                    <td class="p-3 text-right">
                                        <button type="button" onclick="openRecordRentPaymentModal({{ $schedule->id }}, @js($schedule->period_label), {{ (float) $schedule->amount_due - (float) $schedule->amount_paid }})" class="cursor-pointer rounded-full bg-rose-700 px-3 py-1 text-[11px] font-bold text-white transition hover:bg-rose-800">
                                            Collect ETB
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Section 1: Double-Entry Financial Ledger -->
    <div id="ledger" data-section="ledger" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Double-Entry Financial Ledgers (ኦዲት የተደረገ ሂሳብ)</h3>
                <p class="text-xs text-slate-500">Auditable double-entry accounting records with immutable credit &amp; debit adjustments for every financial lifecycle movement.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('finance.export-csv') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center gap-1">
                    <span>📥 Export CSV</span>
                </a>
                <a href="{{ route('finance.statement', ['period' => $dateFilter]) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-[#1d3c34] text-white text-xs font-semibold hover:bg-[#152e28] flex items-center gap-1">
                    <span>🖨️ Audit Statement</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="p-3">Ledger Code</th>
                        <th class="p-3">Type</th>
                        <th class="p-3">Category</th>
                        <th class="p-3">Amount (ETB)</th>
                        <th class="p-3">Description</th>
                        <th class="p-3">Reference</th>
                        <th class="p-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($ledgers as $ledger)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                            <td class="p-3 font-mono font-semibold text-slate-900 dark:text-white">{{ $ledger->ledger_code }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $ledger->entry_type === 'credit' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $ledger->entry_type }}
                                </span>
                            </td>
                            <td class="p-3">{{ ucwords(str_replace('_', ' ', $ledger->category)) }}</td>
                            <td class="p-3 font-bold {{ $ledger->entry_type === 'credit' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $ledger->entry_type === 'credit' ? '+' : '-' }}{{ number_format($ledger->amount, 2) }}
                            </td>
                            <td class="p-3">{{ $ledger->description }}</td>
                            <td class="p-3 font-mono text-[10px]">{{ $ledger->reference ?? '-' }}</td>
                            <td class="p-3 text-slate-400 text-[10px]">{{ $ledger->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-400">No ledger transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $ledgers->links() }}</div>
    </div>

    <!-- Section 2: Refunds & Adjustments -->
    <div id="refunds" data-section="refunds" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <div class="mb-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Refund Processing & Financial Adjustments (SRS §132)</h3>
            <p class="text-xs text-slate-500">Issue full or partial refunds recorded as auditable adjustment debits in the general ledger without overwriting original records.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="p-3">Txn #</th>
                        <th class="p-3">Payer / User</th>
                        <th class="p-3">Payment Type</th>
                        <th class="p-3">Original (ETB)</th>
                        <th class="p-3">Refunded (ETB)</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Process Refund Adjustment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($completedPayments as $payment)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                            <td class="p-3 font-mono font-semibold text-slate-900 dark:text-white">{{ $payment->transaction_number }}</td>
                            <td class="p-3">
                                <div class="font-bold">{{ $payment->payer_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $payment->payer_email }}</div>
                            </td>
                            <td class="p-3 uppercase text-[10px]">{{ str_replace('_', ' ', $payment->payment_type) }}</td>
                            <td class="p-3 font-semibold">{{ number_format($payment->amount, 2) }}</td>
                            <td class="p-3 text-rose-600 font-semibold">{{ number_format($payment->refunded_amount, 2) }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    @if($payment->status === 'completed') bg-emerald-100 text-emerald-800
                                    @elseif($payment->status === 'partially_refunded') bg-amber-100 text-amber-800
                                    @else bg-rose-100 text-rose-800 @endif">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                @if($payment->status !== 'refunded')
                                    <form method="POST" action="{{ route('finance.payments.refund', $payment) }}" onsubmit="return confirm('Process refund adjustment? An auditable debit entry will be created in the ledger.');" class="inline-flex items-center gap-1.5">
                                        @csrf
                                        <input type="number" step="0.01" min="1" max="{{ $payment->amount - $payment->refunded_amount }}" name="refund_amount" value="{{ $payment->amount - $payment->refunded_amount }}" class="text-[10px] px-2 py-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 w-24">
                                        <input type="text" name="reason" required placeholder="Reason for refund..." class="text-[10px] px-2 py-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 w-36">
                                        <button type="submit" class="px-2.5 py-1 bg-rose-600 text-white rounded text-[11px] font-semibold hover:bg-rose-700">Issue Refund</button>
                                    </form>
                                @else
                                    <span class="text-slate-400 text-[10px] italic">Fully Refunded</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-400">No completed payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Invoices & Tax Billing -->
    <div id="invoices" data-section="invoices" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Tax-Compliant Invoices (15% VAT & 2% WHT)</h3>
                <p class="text-xs text-slate-500">Generate and track platform service invoices with standard Ethiopian tax deductions.</p>
            </div>
            <button onclick="document.getElementById('create-invoice-modal').classList.remove('hidden')" class="px-3 py-1.5 bg-[#1d3c34] text-white rounded-lg text-xs font-semibold hover:bg-[#152e28] flex items-center gap-1">
                <span>➕ Create Tax Invoice</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="p-3">Invoice #</th>
                        <th class="p-3">Title & Client</th>
                        <th class="p-3">Subtotal</th>
                        <th class="p-3">15% VAT</th>
                        <th class="p-3">2% WHT</th>
                        <th class="p-3">Total (ETB)</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                            <td class="p-3 font-mono font-semibold text-slate-900 dark:text-white">{{ $inv->invoice_number }}</td>
                            <td class="p-3">
                                <div class="font-bold">{{ $inv->title }}</div>
                                <div class="text-[10px] text-slate-400">{{ $inv->user->name ?? 'Guest Client' }}</div>
                            </td>
                            <td class="p-3">{{ number_format($inv->subtotal, 2) }}</td>
                            <td class="p-3 text-emerald-600">+{{ number_format($inv->vat_amount, 2) }}</td>
                            <td class="p-3 text-rose-600">-{{ number_format($inv->withholding_tax_amount, 2) }}</td>
                            <td class="p-3 font-bold text-slate-900 dark:text-white">{{ number_format($inv->total_amount, 2) }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    @if($inv->status === 'paid') bg-emerald-100 text-emerald-800
                                    @elseif($inv->status === 'overdue') bg-rose-100 text-rose-800
                                    @else bg-amber-100 text-amber-800 @endif">
                                    {{ $inv->status }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <form method="POST" action="{{ route('finance.invoices.status', $inv) }}" class="inline-flex items-center gap-1">
                                    @csrf
                                    @method('PATCH')
                                    @if($inv->status !== 'paid')
                                        <button type="submit" name="status" value="paid" class="px-2.5 py-1 bg-emerald-600 text-white rounded text-[11px] font-medium hover:bg-emerald-700">Mark Paid</button>
                                        <button type="submit" name="status" value="cancelled" class="px-2.5 py-1 bg-slate-500 text-white rounded text-[11px] font-medium hover:bg-slate-600">Cancel</button>
                                    @else
                                        <span class="text-emerald-600 text-[10px] font-semibold">Settled</span>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-slate-400">No invoices issued yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $invoices->links() }}</div>
    </div>

    <!-- Section 4: Payouts & Disbursements Queue -->
    <div id="payouts" data-section="payouts" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <div class="mb-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Host & Seller Payouts Queue (2% Withholding Tax Enforced)</h3>
            <p class="text-xs text-slate-500">Review pending host earnings, seller proceeds, and professional fees before disbursing transfers.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="p-3">Payout Code</th>
                        <th class="p-3">Recipient</th>
                        <th class="p-3">Role</th>
                        <th class="p-3">Gross (ETB)</th>
                        <th class="p-3">2% WHT</th>
                        <th class="p-3">Net Payout</th>
                        <th class="p-3">Method & Bank</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Disbursement Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($payouts as $payout)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                            <td class="p-3 font-mono font-semibold text-slate-900 dark:text-white">{{ $payout->payout_code }}</td>
                            <td class="p-3">
                                <div class="font-bold">{{ $payout->user->name ?? 'User #'.$payout->user_id }}</div>
                                <div class="text-[10px] text-slate-400">{{ $payout->user->email ?? '' }}</div>
                            </td>
                            <td class="p-3 uppercase text-[10px]">{{ $payout->role }}</td>
                            <td class="p-3">{{ number_format($payout->gross_amount, 2) }}</td>
                            <td class="p-3 text-rose-600">-{{ number_format($payout->withholding_tax, 2) }}</td>
                            <td class="p-3 font-bold text-emerald-600">{{ number_format($payout->net_amount, 2) }}</td>
                            <td class="p-3">
                                <div>{{ strtoupper($payout->payment_method) }}</div>
                                <div class="text-[10px] text-slate-400">{{ $payout->bank_name ?? 'CBE' }} ({{ $payout->account_number ?? 'N/A' }})</div>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    @if($payout->status === 'disbursed') bg-emerald-100 text-emerald-800
                                    @elseif($payout->status === 'approved') bg-blue-100 text-blue-800
                                    @elseif($payout->status === 'rejected') bg-rose-100 text-rose-800
                                    @else bg-amber-100 text-amber-800 @endif">
                                    {{ $payout->status }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                @if($payout->status === 'pending' || $payout->status === 'approved')
                                    <form method="POST" action="{{ route('finance.payouts.process', $payout) }}" class="inline-flex items-center gap-1.5">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="transaction_reference" placeholder="Bank FT/Ref #..." class="text-[10px] px-2 py-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 w-28">
                                        <button type="submit" name="decision" value="disburse" class="px-2.5 py-1 bg-emerald-600 text-white rounded text-[11px] font-medium hover:bg-emerald-700">Disburse</button>
                                        <button type="submit" name="decision" value="reject" class="px-2.5 py-1 bg-rose-600 text-white rounded text-[11px] font-medium hover:bg-rose-700">Reject</button>
                                    </form>
                                @else
                                    <span class="text-slate-400 text-[10px] font-mono">{{ $payout->transaction_reference ?: 'Completed' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-6 text-center text-slate-400">No payout requests in queue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $payouts->links() }}</div>
    </div>

    <!-- Section 5: Corporate Subscriptions Desk -->
    <div id="subscriptions" data-section="subscriptions" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Corporate Agency Subscriptions & White-Label Plans</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="p-3">Company Name</th>
                        <th class="p-3">User / Admin</th>
                        <th class="p-3">Subdomain</th>
                        <th class="p-3">Tier</th>
                        <th class="p-3">Expires At</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Update Plan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                            <td class="p-3 font-bold text-slate-900 dark:text-white">{{ $sub->company_name }}</td>
                            <td class="p-3">{{ $sub->user->name ?? 'N/A' }}</td>
                            <td class="p-3 font-mono text-[10px]">{{ $sub->subdomain }}.gtm.et</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    @if($sub->subscription_tier === 'enterprise') bg-purple-100 text-purple-800
                                    @elseif($sub->subscription_tier === 'premium') bg-blue-100 text-blue-800
                                    @else bg-slate-100 text-slate-800 @endif">
                                    {{ $sub->subscription_tier ?? 'standard' }}
                                </span>
                            </td>
                            <td class="p-3 text-slate-500">{{ $sub->subscription_expires_at ? $sub->subscription_expires_at->format('M d, Y') : 'Lifetime' }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $sub->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $sub->is_active ? 'ACTIVE' : 'EXPIRED' }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <form method="POST" action="{{ route('finance.subscriptions.update', $sub) }}" class="inline-flex items-center gap-1.5">
                                    @csrf
                                    @method('PATCH')
                                    <select name="subscription_tier" class="text-[10px] px-2 py-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                                        <option value="standard" {{ $sub->subscription_tier === 'standard' ? 'selected' : '' }}>Standard</option>
                                        <option value="premium" {{ $sub->subscription_tier === 'premium' ? 'selected' : '' }}>Premium</option>
                                        <option value="enterprise" {{ $sub->subscription_tier === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                                    </select>
                                    <select name="months" class="text-[10px] px-2 py-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                                        <option value="1">+1 Month</option>
                                        <option value="6">+6 Months</option>
                                        <option value="12">+12 Months</option>
                                    </select>
                                    <button type="submit" class="px-2.5 py-1 bg-[#1d3c34] text-white rounded text-[11px] font-semibold hover:bg-[#152e28]">Renew</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-400">No corporate subscriptions configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 6: Commissions Desk -->
    <div id="commissions" data-section="commissions" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Agent Commissions Settlement & Tax Vouchers</h3>
                <p class="text-xs text-slate-500">Audit, disburse, and generate Ethiopian Withholding Tax (2% WHT) payout vouchers for certified agents.</p>
            </div>
            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 whitespace-nowrap dark:bg-amber-900/40 dark:text-amber-300">
                {{ $pendingCommissionCount }} Pending Payout(s): {{ number_format($stats['total_pending_commissions'], 2) }} ETB
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="p-3">Voucher #</th>
                        <th class="p-3">Agent</th>
                        <th class="p-3">Property</th>
                        <th class="p-3">Gross (ETB)</th>
                        <th class="p-3">2% WHT (ETB)</th>
                        <th class="p-3">Net Disbursed</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($commissions as $comm)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                            <td class="p-3 font-mono font-semibold">{{ $comm->voucher_number }}</td>
                            <td class="p-3 font-bold text-slate-900 dark:text-white">{{ $comm->agent->name ?? 'Agent' }}</td>
                            <td class="p-3">
                                <p>{{ $comm->property->title ?? 'N/A' }}</p>
                                @if (($comm->split_type ?? null) === 'co_brokered')
                                    <span class="mt-0.5 inline-block rounded bg-sky-100 px-1.5 py-0.5 text-[9px] font-black uppercase text-sky-800 dark:bg-sky-900/40 dark:text-sky-300">
                                        Co-Brokered ({{ $comm->sellingAgent?->name ? 'w/ '.$comm->sellingAgent->name : 'Co-Broker' }})
                                    </span>
                                @elseif (($comm->split_type ?? null) === 'referral')
                                    <span class="mt-0.5 inline-block rounded bg-amber-100 px-1.5 py-0.5 text-[9px] font-black uppercase text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                        Referral Deal ({{ $comm->referralAgent?->name ?? 'Referrer' }})
                                    </span>
                                @else
                                    <span class="mt-0.5 inline-block rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-black uppercase text-slate-700 dark:bg-slate-700 dark:text-slate-300">Direct Listing</span>
                                @endif
                                @if (($comm->company_retained_amount ?? 0) > 0)
                                    <span class="mt-0.5 ml-1 inline-block text-[9px] font-semibold text-amber-700 dark:text-amber-400">+{{ number_format($comm->company_retained_amount, 0) }} ETB Agency</span>
                                @endif
                            </td>
                            <td class="p-3">{{ number_format($comm->commission_amount, 2) }}</td>
                            <td class="p-3 text-rose-600">-{{ number_format($comm->withholding_tax_amount, 2) }}</td>
                            <td class="p-3 font-bold text-emerald-600">{{ number_format($comm->net_payout_amount, 2) }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $comm->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $comm->status }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <a href="{{ route('commissions.voucher', $comm) }}" target="_blank" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded text-[11px] font-semibold hover:bg-slate-200">View Voucher</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-slate-400">No commissions recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $commissions->links() }}</div>
    </div>

    <!-- Section 7: Shift Reconciliation -->
    <div id="reconciliation" data-section="reconciliation" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Front Desk Shift Collections & Audits</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="p-3">Shift Ref</th>
                        <th class="p-3">Receptionist</th>
                        <th class="p-3">Property</th>
                        <th class="p-3">Cash (ETB)</th>
                        <th class="p-3">Telebirr (ETB)</th>
                        <th class="p-3">Total (ETB)</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Verify Shift</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($frontDeskShifts as $shift)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                            <td class="p-3 font-mono font-semibold">{{ $shift->shift_reference }}</td>
                            <td class="p-3 font-bold">{{ $shift->receptionist->name ?? 'Receptionist' }}</td>
                            <td class="p-3">{{ $shift->property->title ?? 'N/A' }}</td>
                            <td class="p-3">{{ number_format($shift->cash_total, 2) }}</td>
                            <td class="p-3">{{ number_format($shift->telebirr_total, 2) }}</td>
                            <td class="p-3 font-bold text-slate-900 dark:text-white">{{ number_format($shift->grand_total, 2) }}</td>
                            <td class="p-3 uppercase text-[10px] font-bold">{{ str_replace('_', ' ', $shift->status) }}</td>
                            <td class="p-3 text-right">
                                @if($shift->status === 'closed')
                                    <form method="POST" action="{{ route('finance.shifts.verify', $shift) }}" class="inline-flex items-center gap-1">
                                        @csrf
                                        <button type="submit" name="status" value="verified_by_finance" class="px-2.5 py-1 bg-emerald-600 text-white rounded text-[11px] font-semibold hover:bg-emerald-700">Verify</button>
                                        <button type="submit" name="status" value="discrepancy_flagged" class="px-2.5 py-1 bg-rose-600 text-white rounded text-[11px] font-semibold hover:bg-rose-700">Flag Discrepancy</button>
                                    </form>
                                @else
                                    <span class="text-slate-400 text-[10px] italic">Audited</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-slate-400">No shift handovers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Profile Section (self-service) -->
    <div id="profile" data-section="profile" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8 md:p-8">
        @include('layouts.partials.profile-form', [
            'user' => auth()->user(),
            'profileAction' => route('profile.update'),
            'profileHeading' => 'My Profile',
            'profileSubheading' => 'Update your name, login password, and profile photo.',
            'profileRoleLabel' => $roleLabel,
        ])
    </div>

    <!-- Create Invoice Modal -->
    <div id="create-invoice-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Create Official Tax Invoice</h3>
                <button type="button" onclick="document.getElementById('create-invoice-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form method="POST" action="{{ route('finance.invoices.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Invoice Title / Description</label>
                    <input type="text" name="title" required placeholder="e.g. Quarterly Agency Retainer & Platform Fee" class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Subtotal Amount (ETB, before tax)</label>
                    <input type="number" step="0.01" min="1" name="subtotal" required placeholder="50000" class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-slate-800 dark:text-white">
                    <p class="text-[10px] text-slate-400 mt-1">15% VAT (+7,500) and 2% Withholding Tax (-1,000) will be automatically computed.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Due Date</label>
                    <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+15 days')) }}" class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Invoice Notes / Bank Instructions</label>
                    <textarea name="notes" rows="2" placeholder="Payable to GTM Real Estate, CBE A/C #1000123456789" class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-slate-800 dark:text-white"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('create-invoice-modal').classList.add('hidden')" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-[#1d3c34] text-white rounded-lg text-xs font-bold hover:bg-[#152e28]">Generate Invoice</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Record Rent Installment Payment Modal --}}
    <div id="record-rent-payment-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-700">
                <div>
                    <span class="rounded bg-[#102b25] px-2 py-0.5 text-[9px] font-black uppercase text-[#d4af37]">Ethiopian Payment Core</span>
                    <h3 class="mt-1 text-lg font-black text-[#1d3c34] dark:text-white">Record Rent Payment</h3>
                </div>
                <button type="button" onclick="closeRecordRentPaymentModal()" class="text-2xl font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">&times;</button>
            </div>

            <form id="record-rent-payment-form" method="POST" action="" class="mt-4 space-y-3.5">
                @csrf
                <p id="rent-payment-period-display" class="rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs font-bold text-[#1d3c34] dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></p>

                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-700 dark:text-slate-300">Amount Paid (ETB) *</label>
                    <input type="number" step="0.01" name="amount_paid" id="rent-payment-amount" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 font-mono text-sm font-bold text-[#1d3c34] focus:border-[#1d3c34] focus:outline-none dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-700 dark:text-slate-300">Payment Method (የክፍያ መንገድ) *</label>
                    <select name="payment_method" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                        <option value="telebirr">Telebirr (ቴሌብር)</option>
                        <option value="cbe_birr">CBE Birr (ሲቢኢ ብር)</option>
                        <option value="bank_transfer" selected>Bank Transfer / Deposit Slip (ባንክ ክፍያ)</option>
                        <option value="cash">Cash at Office (ጥሬ ገንዘብ)</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-700 dark:text-slate-300">Bank Reference / FT Number</label>
                    <input type="text" name="bank_transaction_reference" placeholder="e.g. FT24099XXXXX" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 font-mono text-xs focus:border-[#1d3c34] focus:outline-none dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-700 dark:text-slate-300">Receipt Notes (Optional)</label>
                    <input type="text" name="notes" placeholder="e.g. Paid in full via CBE Mobile" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 font-mono text-xs focus:border-[#1d3c34] focus:outline-none dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                </div>

                <div class="flex items-center justify-end gap-2.5 border-t border-slate-200 pt-3 dark:border-slate-700">
                    <button type="button" onclick="closeRecordRentPaymentModal()" class="cursor-pointer rounded-full border border-slate-300 px-4 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                        Cancel
                    </button>
                    <button type="submit" class="cursor-pointer rounded-full bg-[#102b25] px-5 py-2 text-xs font-black text-[#d4af37] shadow-sm hover:bg-[#1d3c34]">
                        Confirm &amp; Issue Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- View Lease Installment Schedule Modal --}}
    <div id="view-lease-schedule-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm overflow-y-auto p-4">
        <div class="mx-auto my-8 max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between border-b border-slate-200 pb-4 dark:border-slate-700">
                <div>
                    <span id="modal-lease-code" class="rounded bg-[#102b25] px-2 py-0.5 text-[9px] font-black uppercase text-[#d4af37]"></span>
                    <h3 id="modal-lease-title" class="mt-1 text-xl font-black text-[#1d3c34] dark:text-white">Lease Installment Schedule</h3>
                    <p id="modal-lease-tenant" class="text-xs text-slate-500"></p>
                </div>
                <button type="button" onclick="closeLeaseScheduleModal()" class="text-2xl font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">&times;</button>
            </div>

            <div class="my-4 grid grid-cols-3 gap-3 text-center text-xs">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-2.5 dark:border-slate-700 dark:bg-slate-900">
                    <span class="text-[10px] font-bold uppercase text-slate-400">Total Contract Value</span>
                    <p id="modal-total-due" class="mt-0.5 font-mono text-sm font-black text-[#1d3c34] dark:text-white"></p>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-2.5 dark:border-emerald-900 dark:bg-emerald-950/30">
                    <span class="text-[10px] font-bold uppercase text-emerald-700 dark:text-emerald-400">Total Collected</span>
                    <p id="modal-total-paid" class="mt-0.5 font-mono text-sm font-black text-emerald-800 dark:text-emerald-300"></p>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-2.5 dark:border-amber-900 dark:bg-amber-950/30">
                    <span class="text-[10px] font-bold uppercase text-amber-700 dark:text-amber-400">Outstanding Balance</span>
                    <p id="modal-balance" class="mt-0.5 font-mono text-sm font-black text-amber-900 dark:text-amber-300"></p>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[10px] font-bold uppercase text-slate-500 dark:bg-slate-700/50">
                        <tr>
                            <th class="p-3">#</th>
                            <th class="p-3">Installment Period</th>
                            <th class="p-3">Due Date</th>
                            <th class="p-3">Amount Due</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody id="modal-schedules-tbody" class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const rentPaymentUrlTemplate = @js(route('rent-schedules.pay', ['schedule' => '__SCHEDULE_ID__']));
        const leaseShowUrlTemplate = @js(route('leases.show', ['lease' => '__LEASE_ID__']));

        window.switchLeaseTab = function (tabName) {
            document.querySelectorAll('.lease-tab-content').forEach(element => element.classList.add('hidden'));
            document.querySelectorAll('.lease-tab-btn').forEach(button => {
                button.classList.remove('border-[#1d3c34]', 'text-[#1d3c34]', 'dark:text-white');
                button.classList.add('border-transparent', 'text-slate-400');
            });

            const activeContent = document.getElementById('lease-view-' + tabName);
            const activeButton = document.getElementById('tab-btn-' + tabName);
            if (activeContent) {
                activeContent.classList.remove('hidden');
            }
            if (activeButton) {
                activeButton.classList.remove('border-transparent', 'text-slate-400');
                activeButton.classList.add('border-[#1d3c34]', 'text-[#1d3c34]');
            }
        };

        window.openRecordRentPaymentModal = function (scheduleId, periodLabel, amountDue) {
            const form = document.getElementById('record-rent-payment-form');
            form.action = rentPaymentUrlTemplate.replace('__SCHEDULE_ID__', scheduleId);
            document.getElementById('rent-payment-period-display').innerText = 'Recording payment for: ' + periodLabel;
            document.getElementById('rent-payment-amount').value = parseFloat(amountDue).toFixed(2);
            document.getElementById('record-rent-payment-modal').classList.remove('hidden');
        };

        window.closeRecordRentPaymentModal = function () {
            document.getElementById('record-rent-payment-modal').classList.add('hidden');
        };

        window.viewLeaseSchedule = function (leaseId) {
            fetch(leaseShowUrlTemplate.replace('__LEASE_ID__', leaseId), {
                headers: { 'Accept': 'application/json' }
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modal-lease-code').innerText = data.lease.lease_code;
                    document.getElementById('modal-lease-title').innerText = data.lease.property ? data.lease.property.title : 'Lease Agreement';
                    document.getElementById('modal-lease-tenant').innerText = 'Tenant: ' + data.lease.tenant_name + ' (' + data.lease.tenant_phone + ')';
                    document.getElementById('modal-total-due').innerText = 'ETB ' + parseFloat(data.summary.total_rent_due).toLocaleString('en-US', { minimumFractionDigits: 2 });
                    document.getElementById('modal-total-paid').innerText = 'ETB ' + parseFloat(data.summary.total_rent_paid).toLocaleString('en-US', { minimumFractionDigits: 2 });
                    document.getElementById('modal-balance').innerText = 'ETB ' + parseFloat(data.summary.outstanding_balance).toLocaleString('en-US', { minimumFractionDigits: 2 });

                    const tbody = document.getElementById('modal-schedules-tbody');
                    tbody.innerHTML = '';

                    data.schedules.forEach(schedule => {
                        const row = document.createElement('tr');
                        const isOverdue = new Date(schedule.due_date) < new Date();
                        const statusClass = schedule.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : (isOverdue ? 'bg-rose-100 text-rose-800' : 'bg-blue-50 text-blue-800');
                        const statusText = schedule.status === 'paid' ? 'Paid ✓' : (isOverdue ? 'Overdue' : 'Pending');

                        row.innerHTML = `
                            <td class="p-3 font-mono font-bold">${schedule.installment_number}</td>
                            <td class="p-3 font-bold text-slate-800">${schedule.period_label}</td>
                            <td class="p-3 font-mono text-slate-600">${schedule.due_date}</td>
                            <td class="p-3 font-mono font-black text-[#1d3c34]">ETB ${parseFloat(schedule.amount_due).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                            <td class="p-3"><span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-black ${statusClass}">${statusText}</span></td>
                        `;
                        tbody.appendChild(row);
                    });

                    document.getElementById('view-lease-schedule-modal').classList.remove('hidden');
                });
        };

        window.closeLeaseScheduleModal = function () {
            document.getElementById('view-lease-schedule-modal').classList.add('hidden');
        };

        // Finance Overview Charts
        const finData = @json($financeCharts ?? null);
        if (finData && typeof ApexCharts !== 'undefined') {
            const cfEl = document.querySelector('#finance-cashflow-chart');
            if (cfEl && finData.cashflow) {
                new ApexCharts(cfEl, {
                    chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                    series: [
                        { name: 'Cash Inflow (Credit)', data: finData.cashflow.inflows || [] },
                        { name: 'Disbursements (Debit)', data: finData.cashflow.outflows || [] }
                    ],
                    colors: ['#1d3c34', '#e11d48'],
                    stroke: { curve: 'smooth', width: 2.5 },
                    fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
                    xaxis: { categories: finData.cashflow.months || [], labels: { style: { fontSize: '10px' } } },
                    yaxis: {
                        labels: {
                            formatter: (val) => val >= 1000000 ? (val / 1000000).toFixed(1) + 'M ETB' : (val >= 1000 ? (val / 1000).toFixed(0) + 'K ETB' : val + ' ETB'),
                            style: { fontSize: '10px' }
                        },
                        min: 0
                    },
                    legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px' },
                    grid: { borderColor: '#f1f5f9' },
                    tooltip: {
                        y: { formatter: (val) => new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(val) + ' ETB' }
                    }
                }).render();
            }

            const catEl = document.querySelector('#finance-categories-chart');
            if (catEl && finData.categories_donut) {
                new ApexCharts(catEl, {
                    chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                    series: finData.categories_donut.series || [],
                    labels: finData.categories_donut.labels || [],
                    colors: ['#1d3c34', '#d4af37', '#3b82f6', '#102b25', '#9b6c17'],
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total Revenue',
                                        formatter: () => new Intl.NumberFormat('en-US', { notation: 'compact' }).format(finData.categories_donut.total || 0) + ' ETB'
                                    }
                                }
                            }
                        }
                    },
                    legend: { position: 'bottom', fontSize: '10px' }
                }).render();
            }
        }
    </script>
@endsection
