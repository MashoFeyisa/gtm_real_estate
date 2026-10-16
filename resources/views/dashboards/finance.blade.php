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
    $canProcessPayouts = auth()->user()->hasPermission('payouts.update');

    // Agents join this desk scoped to their own deals: the controller filters
    // every collection, so the view must surface those read sections (without
    // the finance-only confirm/reject and platform export actions).
    $isScopedAgent = ($scopedAgent ?? null) !== null;
    $canViewLedger = $isScopedAgent || auth()->user()->hasAnyPermission(['ledger.view', 'finance.manage']);
    $canExportLedger = $isAdmin || $isFinanceOfficer;

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
    @if ($canManagePayments)
        <x-sidebar-item href="#payment-queue" icon="💳" label="Payments to Confirm" :badge="$pendingPayments->isNotEmpty() ? $pendingPayments->count() : null" />
    @endif
    @if ($isScopedAgent)
        <x-sidebar-item href="#my-payments" icon="💳" label="My Payments" :count="$pendingPayments->count()" />
    @endif
    <x-sidebar-item href="#lease-rental" icon="📑" label="Manage Rental & Lease" :badge="$overdueInstallmentCount > 0 ? ($overdueInstallmentCount . ' overdue') : ($activeLeaseCount > 0 ? ($activeLeaseCount . ' active') : null)" :alert="$overdueInstallmentCount > 0" />
    @if ($canViewLedger)
        <x-sidebar-item href="#ledger" icon="📒" label="Double-Entry Ledgers" :count="$ledgers->total()" />
    @endif
    @if (auth()->user()->hasAnyPermission(['refunds.manage', 'refunds.view']))
        <x-sidebar-item href="#refunds" icon="↩️" label="Refunds & Adjustments" :count="$completedPayments->count()" />
    @endif
    @if (auth()->user()->hasAnyPermission(['invoices.manage', 'invoices.view']))
        <x-sidebar-item href="#invoices" icon="🧾" label="Invoices & Billing" :count="$invoices->total()" />
    @endif
    @if (auth()->user()->hasAnyPermission(['payouts.view', 'payouts.update']))
        <x-sidebar-item href="#payouts" icon="💸" label="Payouts & Transfers" :count="$payouts->total()" />
    @endif
    @if (auth()->user()->hasAnyPermission(['subscriptions.manage', 'subscriptions.view']))
        <x-sidebar-item href="#subscriptions" icon="💳" label="Corporate Subscriptions" :count="$subscriptions->count()" />
    @endif
    @if ($canManageCommissions)
        <x-sidebar-item href="#commissions" icon="💼" label="Receive Commission" :count="$stats['pending_commissions_count']" />
    @endif
    @if (auth()->user()->hasAnyPermission(['reconciliation.view', 'reconciliation.manage']))
        <x-sidebar-item href="#reconciliation" icon="⚖️" label="Shift Reconciliation" :count="$frontDeskShifts->count()" />
    @endif
    <x-sidebar-item href="#profile" icon="👤" label="My Profile" />
@endsection

@section('dashboard-content')
    <!-- Overview Section (Analytics & Charts) -->
    <div id="overview" data-section="overview" class="section-panel space-y-8 scroll-mt-6">
        {{-- Period Filter --}}
        <div class="flex flex-wrap items-center justify-between gap-4 token-chart-card !p-4">
            <div class="token-period-bar flex items-center gap-2 overflow-x-auto text-xs">
                <span class="mr-1 text-[10px] font-bold uppercase token-muted">Period:</span>
                @foreach (['this_month' => 'This Month', 'last_month' => 'Last Month', 'this_quarter' => 'This Quarter', 'this_year' => 'This Year', 'all_time' => 'All Time'] as $key => $label)
                    <a href="{{ route('finance.index', ['period' => $key]) }}" class="token-period-btn whitespace-nowrap transition {{ $dateFilter === $key ? 'token-period-btn-active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <form method="GET" action="{{ route('finance.index') }}" class="flex items-center gap-2 text-xs">
                <input type="hidden" name="period" value="custom">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="token-field !rounded-xl !px-2.5 !py-1 !text-xs">
                <span class="token-muted">to</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="token-field !rounded-xl !px-2.5 !py-1 !text-xs">
                <button type="submit" class="rounded-xl bg-[var(--theme-primary-dark)] px-3 py-1.5 font-bold text-xs text-[var(--token-text-on-color)] hover:bg-[var(--token-primary)]">Filter</button>
            </form>
        </div>

        {{-- Actionable Tasks --}}
        @if (isset($actionableTasks) && count($actionableTasks) > 0)
            <div class="token-hero !rounded-2xl !p-5">
                <div class="mb-2 flex items-center gap-2">
                    <span class="rounded bg-[var(--token-accent)] px-2 py-0.5 text-[10px] font-black uppercase">🤖 Finance AI Assistant</span>
                    <span class="text-xs font-bold token-hero-sub">Action Required</span>
                </div>
                <h3 class="mb-3 token-hero-title text-base">You have {{ count($actionableTasks) }} pending action items requiring financial verification:</h3>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($actionableTasks as $task)
                        <a href="{{ $task['url'] }}" class="flex items-center justify-between rounded-xl token-hero-stat transition">
                            <div class="flex items-center gap-2.5">
                                <span class="text-xs font-black">{{ $task['badge'] }}</span>
                                <span class="text-xs font-semibold token-hero-sub">{{ $task['title'] }}</span>
                            </div>
                            <span class="text-xs font-bold text-[var(--token-accent)]">&rarr;</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Pending Commission Alert --}}
        @if ($canManageCommissions && $pendingCommissionCount > 0)
            <div class="token-alert token-alert-warning flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--token-warning)] text-xl font-black text-[var(--token-text-on-color)] shadow-sm">💼</span>
                    <div>
                        <h2 class="text-base font-black">{{ $pendingCommissionCount }} Agent Commission Payout(s) Awaiting Settlement</h2>
                        <p class="mt-0.5 text-xs token-muted">Totalling {{ number_format($stats['total_pending_commissions'], 2) }} ETB before 2% withholding tax deductions.</p>
                    </div>
                </div>
                <a href="#commissions" data-target-section="commissions" class="nav-section-link inline-flex items-center gap-2 rounded-full bg-[var(--theme-primary-dark)] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[var(--token-accent)] transition hover:bg-[var(--token-primary)] shadow-sm">
                    <span>Open Commission Desk &rarr;</span>
                </a>
            </div>
        @endif

        {{-- Pending Payment Alert --}}
        @if ($canManagePayments && $pendingPayments->isNotEmpty())
            <div class="token-alert token-alert-warning flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--token-warning)] text-xl font-black text-[var(--token-text-on-color)] shadow-sm">💳</span>
                    <div>
                        <h2 class="text-base font-black">{{ $pendingPayments->count() }} Payment(s) Awaiting Confirmation</h2>
                        <p class="mt-0.5 text-xs token-muted">Totalling {{ number_format($pendingPayments->sum('amount'), 2) }} ETB. Short-stay reservations stay unconfirmed until the payment is verified and a receipt is issued.</p>
                    </div>
                </div>
                <a href="#payment-queue" data-target-section="payment-queue" class="nav-section-link inline-flex items-center gap-2 rounded-full bg-[var(--theme-primary-dark)] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[var(--token-accent)] transition hover:bg-[var(--token-primary)] shadow-sm">
                    <span>Open Confirmation Queue &rarr;</span>
                </a>
            </div>
        @endif

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-5">
            <div class="token-stat-card">
                <span class="token-stat-title">Gross Inflow (Credits)</span>
                <div class="token-stat-value token-stat-success">{{ number_format($stats['gross_inflow'], 2) }} <span class="text-xs font-normal token-muted">ETB</span></div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Gross Outflow (Debits)</span>
                <div class="token-stat-value token-stat-danger">{{ number_format($stats['gross_outflow'], 2) }} <span class="text-xs font-normal token-muted">ETB</span></div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Net Retained Balance</span>
                <div class="token-stat-value token-stat-primary">{{ number_format($stats['net_retained'], 2) }} <span class="text-xs font-normal token-muted">ETB</span></div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Agency Retained Comm.</span>
                <div class="token-stat-value token-stat-accent">{{ number_format($stats['company_retained_commissions'] ?? 0, 2) }} <span class="text-xs font-normal token-muted">ETB</span></div>
                <p class="mt-1 text-[11px] font-semibold token-muted">{{ $stats['co_brokered_deals_count'] ?? 0 }} co-brokered / multi-tier deals</p>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Tax Provision (15% VAT & 2% WHT)</span>
                <div class="token-stat-value token-stat-warning">{{ number_format($stats['vat_accrued'] + $stats['total_withholding_tax'], 2) }} <span class="text-xs font-normal token-muted">ETB</span></div>
            </div>
        </div>

        @if (auth()->user()?->hasPermission('finance.settings.update'))
            {{-- Dated rate book (SRS §171): a change is a new fact with an
                 effective date, a written reason and an audit entry; it never
                 rewrites a record that already snapshotted its rate. --}}
            <div class="token-card mb-8">
                <h2 class="token-section-title !text-lg">Finance Rates &amp; Effective Dates</h2>
                <p class="mt-1 text-xs token-muted">Changing a rate applies to future transactions only; records already written keep the rate they snapshotted.</p>
                <form method="POST" action="{{ route('finance.settings.rates.store') }}" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-5">
                    @csrf
                    <select name="key" class="token-field !text-xs" required>
                        <option value="platform_fee">Platform fee %</option>
                        <option value="vat">VAT %</option>
                        <option value="withholding">Withholding %</option>
                        <option value="commission">Default commission %</option>
                        <option value="experience_fee">Experience fee %</option>
                    </select>
                    <input type="number" step="0.0001" min="0" max="100" name="rate" class="token-field !text-xs" placeholder="Rate %" required>
                    <input type="date" name="effective_from" value="{{ now()->toDateString() }}" min="{{ now()->toDateString() }}" class="token-field !text-xs" required>
                    <input type="text" name="reason" class="token-field !text-xs" placeholder="Reason (required)" required>
                    <button type="submit" class="token-btn-primary !text-xs">Record rate</button>
                </form>
                @if(($pendingRates ?? collect())->isNotEmpty())
                    <div class="mt-4 rounded-lg border border-[var(--token-border-subtle)] bg-[var(--token-surface-subtle)] p-3">
                        <p class="text-xs font-bold">Pending second-person approval</p>
                        @foreach($pendingRates as $pendingRate)
                            <div class="mt-2 flex items-center justify-between gap-3 text-xs">
                                <span>{{ $pendingRate->key }} → {{ $pendingRate->rate }}% (from {{ $pendingRate->effective_from?->toDateString() }})</span>
                                @if(auth()->user()->hasPermission('finance.settings.approve') && (int) $pendingRate->created_by !== (int) auth()->id())
                                    <form method="POST" action="{{ route('finance.settings.rates.approve', $pendingRate) }}">
                                        @csrf
                                        <button type="submit" class="token-btn-primary !text-[10px]">Approve</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <div class="mb-8 flex flex-wrap items-center gap-3">
            <a href="{{ route('finance.vat') }}" target="_blank" class="rounded-lg border border-[var(--token-border-subtle)] px-3 py-1.5 text-xs font-semibold">VAT payable report (SRS §170)</a>
            <a href="{{ route('finance.reconciliation') }}" class="rounded-lg border border-[var(--token-border-subtle)] px-3 py-1.5 text-xs font-semibold">Bank reference reconciliation</a>
            <span class="text-xs token-muted">Outstanding VAT: {{ number_format($stats['vat_balance'] ?? 0, 2) }} ETB</span>
        </div>

        @if (($negativeBalances ?? collect())->isNotEmpty())
            <div class="mb-8 rounded-lg border border-[var(--token-warning)] bg-[var(--token-surface-subtle)] p-4">
                <p class="text-xs font-bold text-[var(--token-warning-text)]">People who owe the platform ({{ $negativeBalances->count() }})</p>
                <ul class="mt-2 space-y-1 text-xs token-muted">
                    @foreach($negativeBalances as $row)
                        <li>{{ $row['user']->name }} — {{ number_format($row['balance'], 2) }} ETB (future payouts net against this)</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (($financeReviewFlags ?? collect())->isNotEmpty())
            <div class="mb-8 rounded-lg border border-[var(--token-danger)] bg-[var(--token-surface-subtle)] p-4">
                <p class="text-xs font-bold text-[var(--token-danger)]">Rollback-safety flags needing finance review ({{ $financeReviewFlags->count() }})</p>
                <ul class="mt-2 space-y-1 text-xs token-muted">
                    @foreach($financeReviewFlags as $flag)
                        <li>#{{ $flag->id }} — {{ $flag->reason }} (payment {{ $flag->payment?->transaction_number ?? 'n/a' }})</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (($stats['legacy_ledger_entries'] ?? 0) > 0)
            <p class="mb-8 rounded-lg border border-[var(--token-border-subtle)] bg-[var(--token-surface-subtle)] px-4 py-2 text-xs token-muted">
                {{ $stats['legacy_ledger_entries'] }} entr{{ $stats['legacy_ledger_entries'] === 1 ? 'y' : 'ies' }} predate the double-entry change and are shown as legacy (single-sided). They are never rewritten and are kept out of net revenue and the P&amp;L; see <span class="font-semibold">docs/LEDGER_RECONCILIATION.md</span>.
            </p>
        @endif

        {{-- Finance Overview Visualizations --}}
        @if (! empty($financeCharts))
            <div class="token-chart-card">
                <div class="flex flex-col gap-2 border-b border-[var(--token-border-subtle)] pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-[var(--token-primary)]"></span>
                            <h2 class="token-section-title !text-xl">Financial Analytics &amp; Capital Flow Intelligence</h2>
                        </div>
                        <p class="mt-0.5 text-xs token-muted">Real ledger cash inflows, revenue streams, and lease rent collection performance.</p>
                    </div>
                    <span class="rounded-full bg-[var(--token-surface-subtle)] px-3 py-1 text-xs font-bold">
                        Audited Ledger
                    </span>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Chart 1: Cashflow Inflow vs Outflow -->
                    <div class="token-chart-tile lg:col-span-2">
                        <div class="mb-2 flex items-center justify-between">
                            <h3 class="token-chart-title text-xs uppercase tracking-wider">Monthly Cash Inflow vs. Outflow (ETB)</h3>
                            <span class="text-[10px] font-bold token-muted">Past 6 Months</span>
                        </div>
                        <div id="finance-cashflow-chart" class="min-h-[280px]"></div>
                    </div>

                    <!-- Chart 2: Revenue Categories Streams -->
                    <div class="token-chart-tile">
                        <div class="mb-2 flex items-center justify-between">
                            <h3 class="token-chart-title text-xs uppercase tracking-wider">Revenue Stream Mix</h3>
                            <span class="text-[10px] font-bold token-muted">By Ledger Category</span>
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
        <div id="payment-queue" data-section="payment-queue" class="token-card section-panel hidden scroll-mt-6 mb-8">
            <div class="mb-6">
                <h3 class="text-lg font-bold">Payments Awaiting Confirmation</h3>
                <p class="text-xs token-muted">Manage payments submitted through bank transfer, Telebirr, CBE Birr, and uploaded deposit slips. Confirming a short-stay payment locks in the reservation and issues the guest's receipt automatically.</p>
            </div>

            @if ($pendingPayments->isEmpty())
                <div class="rounded-2xl border border-dashed border-[var(--token-border)] bg-[var(--token-surface-subtle)] py-12 text-center">
                    <span class="text-3xl">✅</span>
                    <p class="mt-2 text-sm font-semibold token-muted">No payments are waiting for confirmation.</p>
                    <p class="mt-1 text-xs token-muted">New short-stay and installment payments appear here the moment a payer submits proof of payment.</p>
                </div>
            @else
                <div class="token-table-wrap">
                    <table class="token-table w-full !text-xs">
                        <thead class="bg-[var(--token-surface-subtle)] uppercase font-semibold token-muted border-b border-[var(--token-border)]">
                            <tr>
                                <th>Transaction</th>
                                <th>Payer &amp; Channel</th>
                                <th>Category / Reservation</th>
                                <th class="text-right">Amount (ETB)</th>
                                <th>Reference / Slip</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingPayments as $pendingPayment)
                                @php $stayBooking = $pendingPayment->payable instanceof \App\Models\Booking ? $pendingPayment->payable : null; @endphp
                                <tr class="hover:bg-[var(--token-surface-subtle)]">
                                    <td>
                                        <a href="{{ route('payments.receipt', $pendingPayment->transaction_number) }}" target="_blank" class="font-mono font-semibold underline decoration-dotted">{{ $pendingPayment->transaction_number }}</a>
                                        <span class="mt-0.5 block text-[10px] token-muted">{{ $pendingPayment->created_at?->format('M d, Y H:i') }}</span>
                                    </td>
                                    <td>
                                        <span class="block font-bold">{{ $pendingPayment->payer_name }}</span>
                                        <span class="mt-0.5 block text-[10px] token-muted">{{ $pendingPayment->payer_phone }} · {{ $pendingPayment->method_label }}</span>
                                    </td>
                                    <td>
                                        <span class="font-semibold">{{ $pendingPayment->type_label }}</span>
                                        @if ($pendingPayment->property)
                                            <span class="mt-0.5 block text-[10px] token-muted">{{ Str::limit($pendingPayment->property->title, 34) }}</span>
                                        @endif
                                        @if ($stayBooking)
                                            <span class="mt-1 block font-mono text-[10px] font-bold text-[var(--token-accent)]">
                                                {{ $stayBooking->booking_reference }}
                                                · {{ $stayBooking->check_in_date?->format('M d') }} → {{ $stayBooking->check_out_date?->format('M d, Y') }}
                                                · {{ $stayBooking->total_nights }} night(s)
                                                @if ($stayBooking->payment_status)
                                                    · {{ ucwords(str_replace('_', ' ', $stayBooking->payment_status)) }}
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <span class="block font-mono font-bold">{{ number_format($pendingPayment->amount, 2) }}</span>
                                        <span class="mt-0.5 block text-[10px] token-muted">Platform fee {{ number_format($pendingPayment->platform_fee, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="font-mono text-[11px] token-muted">{{ $pendingPayment->bank_transaction_reference ?? '—' }}</span>
                                        @if ($pendingPayment->deposit_slip_path)
                                            <a href="{{ route('payments.slip', $pendingPayment) }}" target="_blank" class="mt-0.5 block text-[10px] font-bold underline">View Deposit Slip</a>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="flex flex-wrap items-center justify-end gap-1.5">
                                            <form method="POST" action="{{ route('payments.verify', $pendingPayment) }}">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="rounded-full bg-[var(--token-success)] px-3 py-1 text-[11px] font-bold text-[var(--token-text-on-color)] transition hover:bg-[var(--token-success-text)]">✓ Confirm</button>
                                            </form>
                                            <form method="POST" action="{{ route('payments.reject', $pendingPayment) }}" onsubmit="return confirm('Reject this payment? The payer will be notified and can submit a new one.');">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="admin_notes" value="Rejected during finance desk verification.">
                                                <button type="submit" class="rounded-full bg-[var(--token-danger)] px-3 py-1 text-[11px] font-bold text-[var(--token-text-on-color)] transition hover:bg-[var(--token-danger)]">✕ Reject</button>
                                            </form>
                                            <a href="{{ route('payments.receipt', $pendingPayment->transaction_number) }}" target="_blank" class="rounded-full border border-[var(--token-border)] bg-[var(--token-surface)] px-3 py-1 text-[11px] font-bold transition hover:bg-[var(--token-surface-subtle)]">Receipt</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-[11px] token-muted">Confirming sends the payer an in-app notification plus a receipt link at <span class="font-mono">/payments/receipt/{transaction}</span>. Rejecting re-opens the booking for a new payment.</p>
            @endif
        </div>
    @endif

    @if ($isScopedAgent)
        <!-- Agent-scoped payments (read only): the finance confirm/reject queue stays finance-only. -->
        <div id="my-payments" data-section="my-payments" class="token-card section-panel hidden scroll-mt-6 mb-8">
            <div class="mb-6">
                <h3 class="text-lg font-bold">My Payments</h3>
                <p class="text-xs token-muted">Payments on your own listings, scoped to your deals. Finance confirms them; you can open the receipt for any row.</p>
            </div>

            @if ($pendingPayments->isEmpty())
                <div class="rounded-2xl border border-dashed border-[var(--token-border)] bg-[var(--token-surface-subtle)] py-12 text-center">
                    <span class="text-3xl">✅</span>
                    <p class="mt-2 text-sm font-semibold token-muted">No payments are awaiting confirmation on your listings.</p>
                </div>
            @else
                <div class="token-table-wrap">
                    <table class="token-table w-full !text-xs">
                        <thead class="bg-[var(--token-surface-subtle)] uppercase font-semibold token-muted border-b border-[var(--token-border)]">
                            <tr>
                                <th>Transaction</th>
                                <th>Payer &amp; Channel</th>
                                <th>Category</th>
                                <th class="text-right">Amount (ETB)</th>
                                <th class="text-right">Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingPayments as $pendingPayment)
                                <tr class="hover:bg-[var(--token-surface-subtle)]">
                                    <td class="font-mono font-semibold">{{ $pendingPayment->transaction_number }}</td>
                                    <td>
                                        <span class="block font-bold">{{ $pendingPayment->payer_name }}</span>
                                        <span class="mt-0.5 block text-[10px] token-muted">{{ $pendingPayment->method_label }}</span>
                                    </td>
                                    <td>
                                        <span class="font-semibold">{{ $pendingPayment->type_label }}</span>
                                        @if ($pendingPayment->property)
                                            <span class="mt-0.5 block text-[10px] token-muted">{{ Str::limit($pendingPayment->property->title, 34) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right font-mono font-bold">{{ number_format($pendingPayment->amount, 2) }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('payments.receipt', $pendingPayment->transaction_number) }}" target="_blank" class="rounded-full border border-[var(--token-border)] bg-[var(--token-surface)] px-3 py-1 text-[11px] font-bold transition hover:bg-[var(--token-surface-subtle)]">Receipt</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    <!-- Lease & Rental Collection Desk -->
    <div id="lease-rental" data-section="lease-rental" class="token-card section-panel hidden scroll-mt-6 mb-8">
        <div class="flex flex-col gap-4 border-b border-[var(--token-border)] pb-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="token-badge token-badge-accent !text-[10px]">Lease Desk</span>
                    <span class="text-xs font-bold token-muted">የረጅም ጊዜ ኪራይ እና ውል አስተዳደር</span>
                </div>
                <h2 class="token-section-title mt-1 !text-2xl">Lease & Rental Collection Desk</h2>
                <p class="mt-1 text-xs token-muted">Track existing tenancy agreements, automated Ethiopian installment schedules, monthly collections, and lease renewals. Registering new lease agreements is handled from the Admin Dashboard or Agent Portal.</p>
            </div>
            <form method="POST" action="{{ route('leases.process-schedules') }}">
                @csrf
                <button type="submit" class="token-btn token-btn-primary inline-flex items-center gap-1.5 !px-3.5 !py-2 !text-xs" title="Audit rent schedules and escalate overdue installments past grace period">
                    <span>⚡ Audit &amp; Escalation Check</span>
                </button>
            </form>
        </div>

        {{-- Lease Metrics Overview --}}
        <div class="my-6 grid grid-cols-2 gap-3.5 sm:grid-cols-4">
            <div class="token-stat-card !p-4">
                <span class="text-[10px] font-bold uppercase tracking-wider token-muted">Active Leases</span>
                <p class="mt-1 font-mono text-xl font-black">{{ $activeLeaseCount }}</p>
                <span class="text-[11px] font-bold text-[var(--token-success-text)]">Of {{ $leases->count() }} total contracts</span>
            </div>

            <div class="token-stat-card !p-4">
                <span class="text-[10px] font-bold uppercase tracking-wider token-muted">Total Rent Collected</span>
                <p class="mt-1 token-stat-value token-stat-success font-mono">ETB {{ number_format($collectedRent, 2) }}</p>
                <span class="text-[11px] font-bold text-[var(--token-success-text)]">{{ $paidInstallmentCount }} installments paid</span>
            </div>

            <div class="token-stat-card !p-4">
                <span class="text-[10px] font-bold uppercase tracking-wider token-muted">Overdue Installments</span>
                <p class="mt-1 token-stat-value token-stat-danger font-mono">{{ $overdueInstallmentCount }}</p>
                <span class="text-[11px] font-bold text-[var(--token-danger-text)]">Requires tenant follow-up</span>
            </div>

            <div class="token-stat-card !p-4">
                <span class="text-[10px] font-bold uppercase tracking-wider token-muted">Outstanding Balance</span>
                <p class="mt-1 token-stat-value token-stat-warning font-mono">ETB {{ number_format($outstandingRent, 2) }}</p>
                <span class="text-[11px] token-muted">Remaining contract receivables</span>
            </div>
        </div>

        {{-- Lease Tabs --}}
        <div class="mb-4 flex flex-wrap items-center gap-4 border-b border-[var(--token-border)] text-xs font-bold">
            <button type="button" onclick="switchLeaseTab('contracts')" id="tab-btn-contracts" class="lease-tab-btn cursor-pointer border-b-2 border-[var(--token-primary)] pb-2 text-[var(--token-primary)]">
                Active Contracts ({{ $leases->count() }})
            </button>
            <button type="button" onclick="switchLeaseTab('schedules')" id="tab-btn-schedules" class="lease-tab-btn cursor-pointer border-b-2 border-transparent pb-2 token-muted">
                Rent Installment Schedules ({{ $rentSchedules->count() }})
            </button>
            <button type="button" onclick="switchLeaseTab('overdue')" id="tab-btn-overdue" class="lease-tab-btn cursor-pointer border-b-2 border-transparent pb-2 token-muted">
                Overdue Rent ({{ $overdueInstallmentCount }})
            </button>
        </div>

        {{-- TAB 1: CONTRACTS --}}
        <div id="lease-view-contracts" class="lease-tab-content">
            @if ($leases->isEmpty())
                <div class="py-12 text-center token-muted">
                    <span class="text-3xl">📄</span>
                    <p class="mt-2 font-bold token-muted">No Lease Contracts Registered Yet</p>
                    <p class="mt-1 text-xs">Lease agreements are enrolled from the Admin Dashboard or Agent Portal.</p>
                </div>
            @else
                <div class="token-table-wrap">
                    <table class="token-table w-full !text-xs">
                        <thead class="bg-[var(--token-surface-subtle)] uppercase font-semibold token-muted border-b border-[var(--token-border)]">
                            <tr>
                                <th>Lease Code</th>
                                <th>Property</th>
                                <th>Tenant</th>
                                <th class="text-right">Monthly Rent</th>
                                <th>Collection Progress</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leases as $lease)
                                @php
                                    $leaseTotalDue = $lease->total_rent_due ?: 1;
                                    $leasePaidPercentage = min(100, (int) round(($lease->total_rent_paid / $leaseTotalDue) * 100));
                                    $leaseStatusBadge = match ($lease->status) {
                                        'active' => ['Active', 'token-badge-success'],
                                        'pending_renewal' => ['Pending Renewal', 'token-badge-warning'],
                                        'expired' => ['Expired', 'token-badge-danger'],
                                        'terminated' => ['Terminated', 'token-badge-neutral'],
                                        default => ['Draft', 'token-badge-neutral'],
                                    };
                                @endphp
                                <tr class="hover:bg-[var(--token-surface-subtle)]">
                                    <td class="font-mono font-bold">
                                        {{ $lease->lease_code }}
                                        <span class="block font-normal text-[10px] token-muted">Freq: {{ ucfirst($lease->payment_frequency) }}</span>
                                    </td>
                                    <td class="font-semibold">
                                        {{ $lease->property?->title ?? '—' }}
                                        <span class="block text-[10px] font-normal token-muted">{{ $lease->property?->city ?? 'Addis Ababa' }}</span>
                                    </td>
                                    <td class="token-muted">
                                        {{ $lease->tenant_name }}
                                        <span class="block text-[10px] token-muted">{{ $lease->tenant_phone }} · {{ $lease->tenant_email }}</span>
                                    </td>
                                    <td class="text-right font-black">{{ number_format($lease->monthly_rent, 2) }}</td>
                                    <td>
                                        <div class="mb-1 flex items-center justify-between text-[10px] font-bold token-muted">
                                            <span>ETB {{ number_format($lease->total_rent_paid, 0) }}</span>
                                            <span>{{ $leasePaidPercentage }}%</span>
                                        </div>
                                        <div class="h-1.5 w-24 overflow-hidden rounded-full bg-[var(--token-border)]">
                                            <div class="h-1.5 rounded-full bg-[var(--token-success)]" style="width: {{ $leasePaidPercentage }}%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="token-badge !text-[10px] {{ $leaseStatusBadge[1] }}">{{ $leaseStatusBadge[0] }}</span>
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('leases.agreement', $lease) }}" target="_blank" class="rounded-full border border-[var(--token-accent-border)] bg-[var(--theme-primary-dark)] px-2.5 py-1 text-[10px] font-bold text-[var(--token-accent)] transition hover:bg-[var(--token-primary)]">
                                                Agreement PDF
                                            </a>
                                            <button type="button" onclick="viewLeaseSchedule({{ $lease->id }})" class="cursor-pointer rounded-full border border-[var(--token-border)] bg-[var(--token-surface)] px-2.5 py-1 text-[10px] font-bold transition hover:bg-[var(--token-surface-subtle)]">
                                                Schedule
                                            </button>
                                            <form method="POST" action="{{ route('leases.update-status', $lease) }}" class="flex items-center gap-1">
                                                @csrf
                                                @method('PUT')
                                                <select name="status" class="rounded-lg border border-[var(--token-border)] bg-[var(--token-surface)] px-1.5 py-1 text-[10px] font-bold">
                                                    <option value="active" {{ $lease->status === 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="pending_renewal" {{ $lease->status === 'pending_renewal' ? 'selected' : '' }}>Pending Renewal</option>
                                                    <option value="expired" {{ $lease->status === 'expired' ? 'selected' : '' }}>Expired</option>
                                                    <option value="terminated" {{ $lease->status === 'terminated' ? 'selected' : '' }}>Terminated</option>
                                                    <option value="draft" {{ $lease->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                                </select>
                                                <button type="submit" class="cursor-pointer rounded-lg bg-[var(--theme-primary-dark)] px-2 py-1 text-[10px] font-bold text-[var(--token-text-on-color)] transition hover:bg-[var(--token-primary)]">Update</button>
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
                <div class="py-12 text-center token-muted">
                    <p class="font-bold token-muted">No Rent Installments Scheduled</p>
                    <p class="mt-1 text-xs">Schedules are generated automatically whenever a lease is registered.</p>
                </div>
            @else
                <div class="token-table-wrap">
                    <table class="token-table w-full !text-xs">
                        <thead class="bg-[var(--token-surface-subtle)] uppercase font-semibold token-muted border-b border-[var(--token-border)]">
                            <tr>
                                <th>Period / Label</th>
                                <th>Lease &amp; Property</th>
                                <th>Tenant</th>
                                <th>Due Date</th>
                                <th>Amount Due</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rentSchedules as $schedule)
                                <tr class="hover:bg-[var(--token-surface-subtle)]">
                                    <td class="font-bold">{{ $schedule->period_label }}</td>
                                    <td>
                                        <span class="font-mono font-bold">{{ $schedule->lease?->lease_code ?? 'N/A' }}</span>
                                        <span class="block text-[10px] token-muted">{{ Str::limit($schedule->lease?->property?->title ?? 'Property', 22) }}</span>
                                    </td>
                                    <td>
                                        <span class="block font-bold">{{ $schedule->lease?->tenant_name ?? 'Tenant' }}</span>
                                        <span class="block text-[10px] token-muted">{{ $schedule->lease?->tenant_phone }}</span>
                                    </td>
                                    <td class="font-mono">
                                        <span class="{{ $schedule->is_overdue ? 'font-bold text-[var(--token-danger-text)]' : 'token-muted' }}">
                                            {{ $schedule->due_date?->format('M d, Y') }}
                                        </span>
                                    </td>
                                    <td class="font-mono font-black">
                                        ETB {{ number_format($schedule->amount_due, 2) }}
                                        @if ($schedule->amount_paid > 0 && $schedule->status !== 'paid')
                                            <span class="block text-[10px] font-normal text-[var(--token-success)]">Paid: ETB {{ number_format($schedule->amount_paid, 2) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="token-badge !text-[10px] {{ $schedule->status_badge[1] }}">
                                            {{ $schedule->status_badge[0] }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        @if ($schedule->status === 'paid')
                                            @if ($schedule->payment)
                                                <a href="{{ route('payments.receipt', $schedule->payment->transaction_number) }}" target="_blank" class="rounded-full border border-[var(--token-success-border)] bg-[var(--token-success-surface)] px-2.5 py-1 text-[11px] font-bold text-[var(--token-success-text)] transition hover:bg-[var(--token-success-surface)]">
                                                    Receipt →
                                                </a>
                                            @else
                                                <span class="text-[11px] font-bold text-[var(--token-success-text)]">Settled ✓</span>
                                            @endif
                                        @else
                                            <button type="button" onclick="openRecordRentPaymentModal({{ $schedule->id }}, @js($schedule->period_label), {{ (float) $schedule->amount_due }})" class="cursor-pointer rounded-full bg-[var(--theme-primary-dark)] px-2.5 py-1 text-[11px] font-bold text-[var(--token-accent)] transition hover:bg-[var(--token-primary)]">
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
                <div class="py-12 text-center token-muted">
                    <span class="text-3xl">✅</span>
                    <p class="mt-2 text-sm font-bold text-[var(--token-success-text)]">All Rent Installments Are Up To Date!</p>
                    <p class="mt-0.5 text-xs token-muted">No overdue rental schedules currently exist.</p>
                </div>
            @else
                <div class="token-table-wrap">
                    <table class="token-table w-full !text-xs">
                        <thead class="border-b border-[var(--token-danger-border)] bg-[var(--token-danger-surface)] text-[10px] font-bold uppercase text-[var(--token-danger-text)]">
                            <tr>
                                <th>Period</th>
                                <th>Tenant &amp; Contact</th>
                                <th>Property</th>
                                <th>Original Due Date</th>
                                <th>Overdue Amount</th>
                                <th class="text-right">Collect Rent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--token-border)] bg-[var(--token-surface)]">
                            @foreach ($overdueSchedules as $schedule)
                                <tr class="hover:bg-[var(--token-danger-surface)]">
                                    <td class="font-bold text-[var(--token-danger-text)]">{{ $schedule->period_label }}</td>
                                    <td>
                                        <span class="block font-bold">{{ $schedule->lease?->tenant_name }}</span>
                                        <span class="block text-[10px] token-muted">{{ $schedule->lease?->tenant_phone }} · {{ $schedule->lease?->tenant_email }}</span>
                                    </td>
                                    <td>
                                        <span class="block font-bold">{{ Str::limit($schedule->lease?->property?->title ?? 'Property', 25) }}</span>
                                        <span class="block font-mono text-[10px] token-muted">{{ $schedule->lease?->lease_code }}</span>
                                    </td>
                                    <td class="font-mono font-bold text-[var(--token-danger-text)]">{{ $schedule->due_date?->format('M d, Y') }}</td>
                                    <td class="font-mono font-black text-[var(--token-danger-text)]">ETB {{ number_format($schedule->amount_due - $schedule->amount_paid, 2) }}</td>
                                    <td class="text-right">
                                        <button type="button" onclick="openRecordRentPaymentModal({{ $schedule->id }}, @js($schedule->period_label), {{ (float) $schedule->amount_due - (float) $schedule->amount_paid }})" class="cursor-pointer rounded-full bg-[var(--token-danger)] px-3 py-1 text-[11px] font-bold text-[var(--token-text-on-color)] transition hover:bg-[var(--token-danger-text)]">
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
    <div id="ledger" data-section="ledger" class="token-card section-panel hidden scroll-mt-6 mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-bold">Double-Entry Financial Ledgers (ኦዲት የተደረገ ሂሳብ)</h3>
                <p class="text-xs token-muted">Auditable double-entry accounting records with immutable credit &amp; debit adjustments for every financial lifecycle movement.</p>
            </div>
            @if ($canExportLedger)
                <div class="flex items-center gap-2">
                    <a href="{{ route('finance.export-csv') }}" class="px-3 py-1.5 rounded-lg border border-[var(--token-border)] text-xs font-semibold hover:bg-[var(--token-surface-subtle)] flex items-center gap-1">
                        <span>📥 Export CSV</span>
                    </a>
                    <a href="{{ route('finance.statement', ['period' => $dateFilter]) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-[var(--token-primary)] text-[var(--token-text-on-color)] text-xs font-semibold hover:bg-[var(--theme-primary-hover)] flex items-center gap-1">
                        <span>🖨️ Audit Statement</span>
                    </a>
                </div>
            @endif
        </div>

        <div class="token-table-wrap">
            <table class="token-table w-full !text-xs">
                <thead class="bg-[var(--token-surface-subtle)] uppercase font-semibold token-muted border-b border-[var(--token-border)]">
                    <tr>
                        <th>Ledger Code</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Amount (ETB)</th>
                        <th>Description</th>
                        <th>Reference</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledgers as $ledger)
                        <tr class="hover:bg-[var(--token-surface-subtle)]">
                            <td class="font-mono font-semibold">{{ $ledger->ledger_code }}</td>
                            <td>
                                <span class="token-badge !text-[10px] uppercase {{ $ledger->entry_type === 'credit' ? 'token-badge-success' : 'token-badge-danger' }}">
                                    {{ $ledger->entry_type }}
                                </span>
                            </td>
                            <td>{{ ucwords(str_replace('_', ' ', $ledger->category)) }}</td>
                            <td class="font-bold {{ $ledger->entry_type === 'credit' ? 'text-[var(--token-success)]' : 'text-[var(--token-danger)]' }}">
                                {{ $ledger->entry_type === 'credit' ? '+' : '-' }}{{ number_format($ledger->amount, 2) }}
                            </td>
                            <td>{{ $ledger->description }}</td>
                            <td class="font-mono text-[10px]">{{ $ledger->reference ?? '-' }}</td>
                            <td class="token-muted text-[10px]">{{ $ledger->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center token-muted">No ledger transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $ledgers->links() }}</div>
    </div>

    <!-- Section 2: Refunds & Adjustments -->
    <div id="refunds" data-section="refunds" class="token-card section-panel hidden scroll-mt-6 mb-8">
        <div class="mb-6">
            <h3 class="text-base font-bold">Refund Processing & Financial Adjustments (SRS §132)</h3>
            <p class="text-xs token-muted">Issue full or partial refunds recorded as auditable adjustment debits in the general ledger without overwriting original records.</p>
        </div>

        <div class="token-table-wrap">
            <table class="token-table w-full !text-xs">
                <thead class="bg-[var(--token-surface-subtle)] uppercase font-semibold token-muted border-b border-[var(--token-border)]">
                    <tr>
                        <th>Txn #</th>
                        <th>Payer / User</th>
                        <th>Payment Type</th>
                        <th>Original (ETB)</th>
                        <th>Refunded (ETB)</th>
                        <th>Status</th>
                        <th class="text-right">Process Refund Adjustment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($completedPayments as $payment)
                        <tr class="hover:bg-[var(--token-surface-subtle)]">
                            <td class="font-mono font-semibold">{{ $payment->transaction_number }}</td>
                            <td>
                                <div class="font-bold">{{ $payment->payer_name }}</div>
                                <div class="text-[10px] token-muted">{{ $payment->payer_email }}</div>
                            </td>
                            <td class="uppercase text-[10px]">{{ str_replace('_', ' ', $payment->payment_type) }}</td>
                            <td class="font-semibold">{{ number_format($payment->amount, 2) }}</td>
                            <td class="text-[var(--token-danger)] font-semibold">{{ number_format($payment->refunded_amount, 2) }}</td>
                            <td>
                                <span class="token-badge !text-[10px] uppercase @if($payment->status === 'completed') token-badge-success @elseif($payment->status === 'partially_refunded') token-badge-warning @else token-badge-danger @endif">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if($payment->status !== 'refunded')
                                    <form method="POST" action="{{ route('finance.payments.refund', $payment) }}" onsubmit="return confirm('Process refund adjustment? An auditable debit entry will be created in the ledger.');" class="inline-flex items-center gap-1.5">
                                        @csrf
                                        <input type="number" step="0.01" min="1" max="{{ $payment->amount - $payment->refunded_amount }}" name="refund_amount" value="{{ $payment->amount - $payment->refunded_amount }}" class="token-field !text-[10px] !px-2 !py-1 w-24">
                                        <input type="text" name="reason" required placeholder="Reason for refund..." class="token-field !text-[10px] !px-2 !py-1 w-36">
                                        <button type="submit" class="px-2.5 py-1 bg-[var(--token-danger)] text-[var(--token-text-on-color)] rounded text-[11px] font-semibold hover:bg-[var(--token-danger)]">Issue Refund</button>
                                    </form>
                                @else
                                    <span class="token-muted text-[10px] italic">Fully Refunded</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center token-muted">No completed payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Invoices & Tax Billing -->
    <div id="invoices" data-section="invoices" class="token-card section-panel hidden scroll-mt-6 mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-base font-bold">Tax-Compliant Invoices (15% VAT & 2% WHT)</h3>
                <p class="text-xs token-muted">Generate and track platform service invoices with standard Ethiopian tax deductions.</p>
            </div>
            <button onclick="document.getElementById('create-invoice-modal').classList.remove('hidden')" class="px-3 py-1.5 bg-[var(--token-primary)] text-[var(--token-text-on-color)] rounded-lg text-xs font-semibold hover:bg-[var(--theme-primary-hover)] flex items-center gap-1">
                <span>➕ Create Tax Invoice</span>
            </button>
        </div>

        <div class="token-table-wrap">
            <table class="token-table w-full !text-xs">
                <thead class="bg-[var(--token-surface-subtle)] uppercase font-semibold token-muted border-b border-[var(--token-border)]">
                    <tr>
                        <th>Invoice #</th>
                        <th>Title & Client</th>
                        <th>Subtotal</th>
                        <th>15% VAT</th>
                        <th>2% WHT</th>
                        <th>Total (ETB)</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-[var(--token-surface-subtle)]">
                            <td class="font-mono font-semibold">{{ $inv->invoice_number }}</td>
                            <td>
                                <div class="font-bold">{{ $inv->title }}</div>
                                <div class="text-[10px] token-muted">{{ $inv->user->name ?? 'Guest Client' }}</div>
                            </td>
                            <td>{{ number_format($inv->subtotal, 2) }}</td>
                            <td class="text-[var(--token-success)]">+{{ number_format($inv->vat_amount, 2) }}</td>
                            <td class="text-[var(--token-danger)]">-{{ number_format($inv->withholding_tax_amount, 2) }}</td>
                            <td class="font-bold">{{ number_format($inv->total_amount, 2) }}</td>
                            <td>
                                <span class="token-badge !text-[10px] uppercase @if($inv->status === 'paid') token-badge-success @elseif($inv->status === 'overdue') token-badge-danger @else token-badge-warning @endif">
                                    {{ $inv->status }}
                                </span>
                            </td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('finance.invoices.status', $inv) }}" class="inline-flex items-center gap-1">
                                    @csrf
                                    @method('PATCH')
                                    @if($inv->status !== 'paid')
                                        <button type="submit" name="status" value="paid" class="px-2.5 py-1 bg-[var(--token-success)] text-[var(--token-text-on-color)] rounded text-[11px] font-medium hover:bg-[var(--token-success-text)]">Mark Paid</button>
                                        <button type="submit" name="status" value="cancelled" class="px-2.5 py-1 bg-[var(--token-border)] text-[var(--token-text)] rounded text-[11px] font-medium hover:bg-[var(--token-text-muted)]">Cancel</button>
                                    @else
                                        <span class="text-[10px] font-semibold text-[var(--token-success)]">Settled</span>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center token-muted">No invoices issued yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $invoices->links() }}</div>
    </div>

    <!-- Section 4: Payouts & Disbursements Queue -->
    <div id="payouts" data-section="payouts" class="token-card section-panel hidden scroll-mt-6 mb-8">
        <div class="mb-6">
            <h3 class="text-base font-bold">Payouts Queue — Maker-Checker &amp; Manual Disbursement (SRS §194)</h3>
            <p class="text-xs token-muted">Available balances are computed from the ledger, never stored. A payout must clear the minimum threshold, the schedule and the holdback; the requester can never approve or disburse their own payout. Transfers are not integrated with a bank — a disbursement is recorded as a manual disbursement with a voucher number and a bank reference.</p>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-[var(--token-border-subtle)] bg-[var(--token-surface-subtle)] p-3">
                <p class="text-[10px] uppercase token-muted font-bold">Your available balance</p>
                <p class="text-lg font-black">{{ number_format($payoutBalance, 2) }} ETB</p>
            </div>
            <div class="rounded-xl border border-[var(--token-border-subtle)] bg-[var(--token-surface-subtle)] p-3">
                <p class="text-[10px] uppercase token-muted font-bold">Held (requests + holdback)</p>
                <p class="text-lg font-black">{{ number_format($payoutHeld, 2) }} ETB</p>
            </div>
            <div class="rounded-xl border border-[var(--token-border-subtle)] bg-[var(--token-surface-subtle)] p-3">
                <p class="text-[10px] uppercase token-muted font-bold">Minimum threshold</p>
                <p class="text-lg font-black">{{ number_format($payoutMinimum, 2) }} ETB</p>
            </div>
            <div class="rounded-xl border border-[var(--token-border-subtle)] bg-[var(--token-surface-subtle)] p-3">
                <p class="text-[10px] uppercase token-muted font-bold">Schedule</p>
                <p class="text-lg font-black capitalize">{{ str_replace('_', ' ', $payoutSchedule) }}</p>
                <p class="text-[10px] token-muted">{{ $payoutScheduleOpen ? 'Open now' : 'Closed now' }} · holdback {{ number_format($payoutHoldbackRate, 2) }}%</p>
            </div>
        </div>

        @if($canRequestPayout ?? false)
        <form method="POST" action="{{ route('finance.payouts.store') }}" class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-5">
            @csrf
            <input type="number" step="0.01" min="{{ $payoutMinimum }}" name="gross_amount" class="token-field !text-xs" placeholder="Amount (ETB)" required>
            <select name="payment_method" class="token-field !text-xs" required>
                <option value="bank_transfer">Bank transfer</option>
                <option value="telebirr">Telebirr</option>
                <option value="cbe_birr">CBE Birr</option>
            </select>
            <input type="text" name="bank_name" class="token-field !text-xs" placeholder="Bank name">
            <input type="text" name="account_number" class="token-field !text-xs" placeholder="Account number">
            <button type="submit" class="token-btn-primary !text-xs">Request payout</button>
        </form>
        @endif

        <div class="token-table-wrap">
            <table class="token-table w-full !text-xs">
                <thead class="bg-[var(--token-surface-subtle)] uppercase font-semibold token-muted border-b border-[var(--token-border)]">
                    <tr>
                        <th>Payout Code</th>
                        <th>Recipient</th>
                        <th>Role</th>
                        <th>Gross (ETB)</th>
                        <th>2% WHT</th>
                        <th>Net Payout</th>
                        <th>Method & Bank</th>
                        <th>Status</th>
                        <th class="text-right">Disbursement Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payouts as $payout)
                        <tr class="hover:bg-[var(--token-surface-subtle)]">
                            <td class="font-mono font-semibold">{{ $payout->payout_code }}</td>
                            <td>
                                <div class="font-bold">{{ $payout->user->name ?? 'User #'.$payout->user_id }}</div>
                                <div class="text-[10px] token-muted">{{ $payout->user->email ?? '' }}</div>
                            </td>
                            <td class="uppercase text-[10px]">{{ $payout->role }}</td>
                            <td>{{ number_format($payout->gross_amount, 2) }}</td>
                            <td class="text-[var(--token-danger)]">-{{ number_format($payout->withholding_tax, 2) }}</td>
                            <td class="font-bold text-[var(--token-success)]">{{ number_format($payout->net_amount, 2) }}</td>
                            <td>
                                <div>{{ strtoupper($payout->payment_method) }}</div>
                                <div class="text-[10px] token-muted">{{ $payout->bank_name ?? 'CBE' }} ({{ ($canSeeAllPayouts ?? false) ? ($payout->account_number ?? 'N/A') : $payout->masked_account_number }})</div>
                            </td>
                            <td>
                                <span class="token-badge !text-[10px] uppercase @if($payout->status === 'disbursed') token-badge-success @elseif($payout->status === 'approved') token-badge-info @elseif($payout->status === 'rejected') token-badge-danger @else token-badge-warning @endif">
                                    {{ $payout->status }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if(! $canProcessPayouts)
                                    <span class="token-muted text-[10px] font-mono">{{ $payout->status }}</span>
                                @elseif($payout->status === 'pending')
                                    <form method="POST" action="{{ route('finance.payouts.process', $payout) }}" class="inline-flex items-center gap-1.5">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="rejection_reason" placeholder="Rejection reason..." class="token-field !text-[10px] !px-2 !py-1 w-32">
                                        <button type="submit" name="decision" value="approve" class="px-2.5 py-1 bg-[var(--token-info)] text-[var(--token-text-on-color)] rounded text-[11px] font-medium">Approve</button>
                                        <button type="submit" name="decision" value="reject" class="px-2.5 py-1 bg-[var(--token-danger)] text-[var(--token-text-on-color)] rounded text-[11px] font-medium">Reject</button>
                                    </form>
                                @elseif($payout->status === 'approved')
                                    <form method="POST" action="{{ route('finance.payouts.process', $payout) }}" class="inline-flex items-center gap-1.5">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="transaction_reference" placeholder="Bank FT/Ref #..." class="token-field !text-[10px] !px-2 !py-1 w-28" required>
                                        <button type="submit" name="decision" value="disburse" class="px-2.5 py-1 bg-[var(--token-success)] text-[var(--token-text-on-color)] rounded text-[11px] font-medium hover:bg-[var(--token-success-text)]">Disburse (manual)</button>
                                        <button type="submit" name="decision" value="reject" class="px-2.5 py-1 bg-[var(--token-danger)] text-[var(--token-text-on-color)] rounded text-[11px] font-medium">Reject</button>
                                    </form>
                                @else
                                    <span class="token-muted text-[10px] font-mono">
                                        {{ $payout->voucher_number ? $payout->voucher_number.' · ' : '' }}{{ $payout->transaction_reference ?: 'Completed' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-6 text-center token-muted">No payout requests in queue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $payouts->links() }}</div>
    </div>

    <!-- Section 5: Corporate Subscriptions Desk -->
    <div id="subscriptions" data-section="subscriptions" class="token-card section-panel hidden scroll-mt-6 mb-8">
        <h3 class="text-base font-bold mb-4">Corporate Agency Subscriptions & White-Label Plans</h3>
        <div class="token-table-wrap">
            <table class="token-table w-full !text-xs">
                <thead class="bg-[var(--token-surface-subtle)] uppercase font-semibold token-muted border-b border-[var(--token-border)]">
                    <tr>
                        <th>Company Name</th>
                        <th>User / Admin</th>
                        <th>Subdomain</th>
                        <th>Tier</th>
                        <th>Expires At</th>
                        <th>Status</th>
                        <th class="text-right">Update Plan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-[var(--token-surface-subtle)]">
                            <td class="font-bold">{{ $sub->company_name }}</td>
                            <td>{{ $sub->user->name ?? 'N/A' }}</td>
                            <td class="font-mono text-[10px]">{{ $sub->subdomain }}.gtm.et</td>
                            <td>
                                <span class="token-badge !text-[10px] uppercase @if($sub->subscription_tier === 'enterprise') token-badge-accent @elseif($sub->subscription_tier === 'premium') token-badge-info @else token-badge-neutral @endif">
                                    {{ $sub->subscription_tier ?? 'standard' }}
                                </span>
                            </td>
                            <td class="token-muted">{{ $sub->subscription_expires_at ? $sub->subscription_expires_at->format('M d, Y') : 'Lifetime' }}</td>
                            <td>
                                <span class="token-badge !text-[10px] {{ $sub->is_active ? 'token-badge-success' : 'token-badge-danger' }}">
                                    {{ $sub->is_active ? 'ACTIVE' : 'EXPIRED' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('finance.subscriptions.update', $sub) }}" class="inline-flex items-center gap-1.5">
                                    @csrf
                                    @method('PATCH')
                                    <select name="subscription_tier" class="token-field !text-[10px] !px-2 !py-1">
                                        <option value="standard" {{ $sub->subscription_tier === 'standard' ? 'selected' : '' }}>Standard</option>
                                        <option value="premium" {{ $sub->subscription_tier === 'premium' ? 'selected' : '' }}>Premium</option>
                                        <option value="enterprise" {{ $sub->subscription_tier === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                                    </select>
                                    <select name="months" class="token-field !text-[10px] !px-2 !py-1">
                                        <option value="1">+1 Month</option>
                                        <option value="6">+6 Months</option>
                                        <option value="12">+12 Months</option>
                                    </select>
                                    <button type="submit" class="px-2.5 py-1 bg-[var(--token-primary)] text-[var(--token-text-on-color)] rounded text-[11px] font-semibold hover:bg-[var(--theme-primary-hover)]">Renew</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center token-muted">No corporate subscriptions configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 6: Commissions Desk -->
    <div id="commissions" data-section="commissions" class="token-card section-panel hidden scroll-mt-6 mb-8">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-bold">Agent Commissions Settlement & Tax Vouchers</h3>
                <p class="text-xs token-muted">Audit, disburse, and generate Ethiopian Withholding Tax (2% WHT) payout vouchers for certified agents.</p>
            </div>
            <span class="token-badge token-badge-warning whitespace-nowrap">
                {{ $pendingCommissionCount }} Pending Payout(s): {{ number_format($stats['total_pending_commissions'], 2) }} ETB
            </span>
        </div>
        <div class="token-table-wrap">
            <table class="token-table w-full !text-xs">
                <thead class="bg-[var(--token-surface-subtle)] uppercase font-semibold token-muted border-b border-[var(--token-border)]">
                    <tr>
                        <th>Voucher #</th>
                        <th>Agent</th>
                        <th>Property</th>
                        <th>Gross (ETB)</th>
                        <th>2% WHT (ETB)</th>
                        <th>Net Disbursed</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $comm)
                        <tr class="hover:bg-[var(--token-surface-subtle)]">
                            <td class="font-mono font-semibold">{{ $comm->voucher_number }}</td>
                            <td class="font-bold">{{ $comm->agent->name ?? 'Agent' }}</td>
                            <td>
                                <p>{{ $comm->property->title ?? 'N/A' }}</p>
                                @if (($comm->split_type ?? null) === 'co_brokered')
                                    <span class="mt-0.5 inline-block token-badge token-badge-info !text-[9px] uppercase">
                                        Co-Brokered ({{ $comm->sellingAgent?->name ? 'w/ '.$comm->sellingAgent->name : 'Co-Broker' }})
                                    </span>
                                @elseif (($comm->split_type ?? null) === 'referral')
                                    <span class="mt-0.5 inline-block token-badge token-badge-warning !text-[9px] uppercase">
                                        Referral Deal ({{ $comm->referralAgent?->name ?? 'Referrer' }})
                                    </span>
                                @else
                                    <span class="mt-0.5 inline-block token-badge token-badge-neutral !text-[9px] uppercase">Direct Listing</span>
                                @endif
                                @if (($comm->company_retained_amount ?? 0) > 0)
                                    <span class="mt-0.5 ml-1 inline-block text-[9px] font-semibold text-[var(--token-warning-text)]">+{{ number_format($comm->company_retained_amount, 0) }} ETB Agency</span>
                                @endif
                            </td>
                            <td>{{ number_format($comm->commission_amount, 2) }}</td>
                            <td class="text-[var(--token-danger)]">-{{ number_format($comm->withholding_tax_amount, 2) }}</td>
                            <td class="font-bold text-[var(--token-success)]">{{ number_format($comm->net_payout_amount, 2) }}</td>
                            <td>
                                <span class="token-badge !text-[10px] uppercase {{ $comm->status === 'paid' ? 'token-badge-success' : 'token-badge-warning' }}">
                                    {{ $comm->status }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('commissions.voucher', $comm) }}" target="_blank" class="px-2.5 py-1 token-badge-neutral rounded text-[11px] font-semibold hover:bg-[var(--token-border)]">View Voucher</a>
                                    @if ($comm->status !== 'paid' && auth()->user()->hasAnyPermission(['commissions.manage', 'payouts.update']))
                                        <form method="POST" action="{{ route('commissions.settle', $comm) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="payout_method" value="bank_transfer">
                                            <input type="text" name="payout_reference" placeholder="Bank ref #" class="token-field !text-[10px] !px-2 !py-1 w-24" required>
                                            <button type="submit" class="px-2.5 py-1 bg-[var(--token-success)] text-[var(--token-text-on-color)] rounded text-[11px] font-semibold">Settle (manual)</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center token-muted">No commissions recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $commissions->links() }}</div>
    </div>

    <!-- Section 7: Shift Reconciliation -->
    <div id="reconciliation" data-section="reconciliation" class="token-card section-panel hidden scroll-mt-6 mb-8">
        <h3 class="text-base font-bold mb-4">Front Desk Shift Collections & Audits</h3>
        <div class="token-table-wrap">
            <table class="token-table w-full !text-xs">
                <thead class="bg-[var(--token-surface-subtle)] uppercase font-semibold token-muted border-b border-[var(--token-border)]">
                    <tr>
                        <th>Shift Ref</th>
                        <th>Receptionist</th>
                        <th>Property</th>
                        <th>Cash (ETB)</th>
                        <th>Telebirr (ETB)</th>
                        <th>Total (ETB)</th>
                        <th>Status</th>
                        <th class="text-right">Verify Shift</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($frontDeskShifts as $shift)
                        <tr class="hover:bg-[var(--token-surface-subtle)]">
                            <td class="font-mono font-semibold">{{ $shift->shift_reference }}</td>
                            <td class="font-bold">{{ $shift->receptionist->name ?? 'Receptionist' }}</td>
                            <td>{{ $shift->property->title ?? 'N/A' }}</td>
                            <td>{{ number_format($shift->cash_total, 2) }}</td>
                            <td>{{ number_format($shift->telebirr_total, 2) }}</td>
                            <td class="font-bold">{{ number_format($shift->grand_total, 2) }}</td>
                            <td class="uppercase text-[10px] font-bold">{{ str_replace('_', ' ', $shift->status) }}</td>
                            <td class="text-right">
                                @if($shift->status === 'closed')
                                    <form method="POST" action="{{ route('finance.shifts.verify', $shift) }}" class="inline-flex items-center gap-1">
                                        @csrf
                                        <button type="submit" name="status" value="verified_by_finance" class="px-2.5 py-1 bg-[var(--token-success)] text-[var(--token-text-on-color)] rounded text-[11px] font-semibold hover:bg-[var(--token-success-text)]">Verify</button>
                                        <button type="submit" name="status" value="discrepancy_flagged" class="px-2.5 py-1 bg-[var(--token-danger)] text-[var(--token-text-on-color)] rounded text-[11px] font-semibold hover:bg-[var(--token-danger)]">Flag Discrepancy</button>
                                    </form>
                                @else
                                    <span class="token-muted text-[10px] italic">Audited</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center token-muted">No shift handovers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Profile Section (self-service) -->
    <div id="profile" data-section="profile" class="token-card section-panel hidden scroll-mt-6 mb-8">
        @include('layouts.partials.profile-form', [
            'user' => auth()->user(),
            'profileAction' => route('profile.update'),
            'profileHeading' => 'My Profile',
            'profileSubheading' => 'Update your name, login password, and profile photo.',
            'profileRoleLabel' => $roleLabel,
        ])
    </div>

    <!-- Create Invoice Modal -->
    <div id="create-invoice-modal" class="token-modal-overlay hidden fixed inset-0 z-50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="token-card max-w-lg w-full shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold">Create Official Tax Invoice</h3>
                <button type="button" onclick="document.getElementById('create-invoice-modal').classList.add('hidden')" class="token-muted">✕</button>
            </div>
            <form method="POST" action="{{ route('finance.invoices.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="token-label mb-1">Invoice Title / Description</label>
                    <input type="text" name="title" required placeholder="e.g. Quarterly Agency Retainer & Platform Fee" class="token-field w-full text-xs">
                </div>
                <div>
                    <label class="token-label mb-1">Subtotal Amount (ETB, before tax)</label>
                    <input type="number" step="0.01" min="1" name="subtotal" required placeholder="50000" class="token-field w-full text-xs">
                    <p class="text-[10px] token-muted mt-1">15% VAT (+7,500) and 2% Withholding Tax (-1,000) will be automatically computed.</p>
                </div>
                <div>
                    <label class="token-label mb-1">Due Date</label>
                    <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+15 days')) }}" class="token-field w-full text-xs">
                </div>
                <div>
                    <label class="token-label mb-1">Invoice Notes / Bank Instructions</label>
                    <textarea name="notes" rows="2" placeholder="Payable to GTM Real Estate, CBE A/C #1000123456789" class="token-field w-full text-xs"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('create-invoice-modal').classList.add('hidden')" class="px-4 py-2 border border-[var(--token-border)] rounded-lg text-xs font-semibold">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-[var(--token-primary)] text-[var(--token-text-on-color)] rounded-lg text-xs font-bold hover:bg-[var(--theme-primary-hover)]">Generate Invoice</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Record Rent Installment Payment Modal --}}
    <div id="record-rent-payment-modal" class="token-modal-overlay hidden fixed inset-0 z-50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="token-card w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between border-b border-[var(--token-border)] pb-3">
                <div>
                    <span class="token-badge token-badge-accent !text-[9px]">Ethiopian Payment Core</span>
                    <h3 class="mt-1 text-lg font-black text-[var(--token-text)]">Record Rent Payment</h3>
                </div>
                <button type="button" onclick="closeRecordRentPaymentModal()" class="text-2xl font-bold token-muted">&times;</button>
            </div>

            <form id="record-rent-payment-form" method="POST" action="" class="mt-4 space-y-3.5">
                @csrf
                <p id="rent-payment-period-display" class="token-chart-tile !rounded-xl text-xs font-bold"></p>

                <div>
                    <label class="token-label mb-1">Amount Paid (ETB) *</label>
                    <input type="number" step="0.01" name="amount_paid" id="rent-payment-amount" required class="token-field w-full font-mono text-sm font-bold">
                </div>

                <div>
                    <label class="token-label mb-1">Payment Method (የክፍያ መንገድ) *</label>
                    <select name="payment_method" required class="token-field w-full text-xs">
                        <option value="telebirr">Telebirr (ቴሌብር)</option>
                        <option value="cbe_birr">CBE Birr (ሲቢኢ ብር)</option>
                        <option value="bank_transfer" selected>Bank Transfer / Deposit Slip (ባንክ ክፍያ)</option>
                        <option value="cash">Cash at Office (ጥሬ ገንዘብ)</option>
                    </select>
                </div>

                <div>
                    <label class="token-label mb-1">Bank Reference / FT Number</label>
                    <input type="text" name="bank_transaction_reference" placeholder="e.g. FT24099XXXXX" class="token-field w-full font-mono text-xs">
                </div>

                <div>
                    <label class="token-label mb-1">Receipt Notes (Optional)</label>
                    <input type="text" name="notes" placeholder="e.g. Paid in full via CBE Mobile" class="token-field w-full font-mono text-xs">
                </div>

                <div class="flex items-center justify-end gap-2.5 border-t border-[var(--token-border)] pt-3">
                    <button type="button" onclick="closeRecordRentPaymentModal()" class="cursor-pointer rounded-full border border-[var(--token-border)] px-4 py-1.5 text-xs font-bold token-muted hover:bg-[var(--token-surface-subtle)]">
                        Cancel
                    </button>
                    <button type="submit" class="cursor-pointer rounded-full bg-[var(--theme-primary-dark)] px-5 py-2 text-xs font-black text-[var(--token-accent)] shadow-sm hover:bg-[var(--token-primary)]">
                        Confirm &amp; Issue Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- View Lease Installment Schedule Modal --}}
    <div id="view-lease-schedule-modal" class="token-modal-overlay hidden fixed inset-0 z-50 backdrop-blur-sm overflow-y-auto p-4">
        <div class="token-card mx-auto my-8 max-h-[90vh] w-full max-w-3xl overflow-y-auto shadow-2xl">
            <div class="flex items-center justify-between border-b border-[var(--token-border)] pb-4">
                <div>
                    <span id="modal-lease-code" class="token-badge token-badge-accent !text-[9px]"></span>
                    <h3 id="modal-lease-title" class="mt-1 text-xl font-black text-[var(--token-text)]">Lease Installment Schedule</h3>
                    <p id="modal-lease-tenant" class="text-xs token-muted"></p>
                </div>
                <button type="button" onclick="closeLeaseScheduleModal()" class="text-2xl font-bold token-muted">&times;</button>
            </div>

            <div class="my-4 grid grid-cols-3 gatext-center text-xs">
                <div class="token-chart-tile !rounded-xl">
                    <span class="token-stat-title">Total Contract Value</span>
                    <p id="modal-total-due" class="mt-0.5 font-mono text-sm font-black"></p>
                </div>
                <div class="token-chart-tile !rounded-xl">
                    <span class="text-[10px] font-bold uppercase text-[var(--token-success-text)]">Total Collected</span>
                    <p id="modal-total-paid" class="mt-0.5 font-mono text-sm font-black text-[var(--token-success-text)]"></p>
                </div>
                <div class="token-chart-tile !rounded-xl">
                    <span class="text-[10px] font-bold uppercase text-[var(--token-warning-text)]">Outstanding Balance</span>
                    <p id="modal-balance" class="mt-0.5 font-mono text-sm font-black text-[var(--token-warning-text)]"></p>
                </div>
            </div>

            <div class="token-table-wrap mt-4">
                <table class="token-table text-xs text-left">
                    <thead class="bg-[var(--token-surface-subtle)] text-[10px] font-bold uppercase token-muted">
                        <tr>
                            <th>#</th>
                            <th>Installment Period</th>
                            <th>Due Date</th>
                            <th>Amount Due</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="modal-schedules-tbody" class=""></tbody>
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
                button.classList.remove('border-[var(--token-primary)]', 'text-[var(--token-primary)]');
                button.classList.add('border-transparent', 'token-muted');
            });

            const activeContent = document.getElementById('lease-view-' + tabName);
            const activeButton = document.getElementById('tab-btn-' + tabName);
            if (activeContent) {
                activeContent.classList.remove('hidden');
            }
            if (activeButton) {
                activeButton.classList.remove('border-transparent', 'token-muted');
                activeButton.classList.add('border-[var(--token-primary)]', 'text-[var(--token-primary)]');
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
                        const statusClass = schedule.status === 'paid' ? 'token-badge-success' : (isOverdue ? 'token-badge-danger' : 'token-badge-info');
                        const statusText = schedule.status === 'paid' ? 'Paid ✓' : (isOverdue ? 'Overdue' : 'Pending');

                        row.innerHTML = `
                            <td class="font-mono font-bold">${schedule.installment_number}</td>
                            <td class="font-bold">${schedule.period_label}</td>
                            <td class="font-mono">${schedule.due_date}</td>
                            <td class="font-mono font-black">ETB ${parseFloat(schedule.amount_due).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                            <td><span class="token-badge !text-[10px] ${statusClass}">${statusText}</span></td>
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
        const tc = (n) => getComputedStyle(document.documentElement).getPropertyValue('--token-chart-' + n).trim();
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
                    colors: [tc('primary'), tc('danger')],
                    stroke: { curve: 'smooth', width: 2.5 },
                    fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
                    xaxis: { categories: finData.cashflow.months || [], labels: { style: { fontSize: '10px', colors: tc('text') } } },
                    yaxis: {
                        labels: {
                            formatter: (val) => val >= 1000000 ? (val / 1000000).toFixed(1) + 'M ETB' : (val >= 1000 ? (val / 1000).toFixed(0) + 'K ETB' : val + ' ETB'),
                            style: { fontSize: '10px', colors: tc('text') }
                        },
                        min: 0
                    },
                    legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px' },
                    grid: { borderColor: tc('grid') },
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
                    colors: [tc('primary'), tc('warning'), tc('info'), tc('success'), tc('accent')],
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
