@php
    $leases = $leases ?? \App\Models\Lease::with(['property', 'agent', 'schedules.payment'])->latest()->get();
    $rentSchedules = $rentSchedules ?? \App\Models\RentSchedule::whereIn('lease_id', $leases->pluck('id'))->with(['lease.property'])->latest()->get();
    $activeLeasesCount = $activeLeasesCount ?? $leases->where('status', 'active')->count();
    $totalRentCollected = $totalRentCollected ?? (float) $rentSchedules->where('status', 'paid')->sum('amount_paid');
    $overdueRentCount = $overdueRentCount ?? $rentSchedules->filter(fn ($s) => $s->is_overdue)->count();

    $user = auth()->user();
    if ($user && $user->agentProfile && ! $user->isAdmin()) {
        $allProperties = $user->agentProfile->properties()->orderBy('title')->get();
    } else {
        $allProperties = \App\Models\Property::query()->orderBy('title')->get();
    }

    $rentalProperties = $allProperties->where('type', 'rent');
    if ($rentalProperties->isEmpty()) {
        $rentalProperties = $allProperties;
    }

    $activeLeases = $leases->where('status', 'active');
    $overdueSchedules = $rentSchedules->filter(fn ($s) => $s->is_overdue);
    $totalScheduledAmount = $rentSchedules->sum('amount_due');
    $outstandingRent = max(0, $totalScheduledAmount - $totalRentCollected);
@endphp

<section id="leases" data-section="leases" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#f0e4cf] pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Lease Desk</span>
                <span class="text-xs font-bold text-[#587165]">የረጅም ጊዜ ኪራይ እና ውል አስተዳደር</span>
            </div>
            <h2 class="text-2xl font-black text-[#1d3c34] mt-1">Rental &amp; Lease Management Desk</h2>
            <p class="text-xs text-slate-500">Track tenant agreements, automated Ethiopian installment schedules, monthly collections, and lease renewals.</p>
        </div>

        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('leases.process-schedules') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-[#102b25] bg-[#102b25] px-3.5 py-2 text-xs font-bold text-white hover:bg-[#1d3c34] transition shadow-xs cursor-pointer" title="Audit rent schedules and escalate overdue installments past grace period">
                    <span>⚡ Audit &amp; Escalation Check</span>
                </button>
            </form>
            <button type="button" onclick="openCreateLeaseModal()" class="inline-flex items-center gap-1.5 rounded-full bg-[#102b25] px-4 py-2 text-xs font-bold text-[#d4af37] hover:bg-[#1d3c34] transition shadow-xs cursor-pointer">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ New Lease Agreement</span>
            </button>
        </div>
    </div>

    {{-- Lease Metrics Overview --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 my-6">
        <div class="rounded-2xl border border-[#d9cab3] bg-[#fffdfa] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Active Leases</span>
            <p class="mt-1 font-mono text-xl font-black text-[#1d3c34]">{{ $activeLeasesCount }}</p>
            <span class="text-[11px] text-emerald-700 font-bold">Of {{ $leases->count() }} total contracts</span>
        </div>

        <div class="rounded-2xl border border-[#d9cab3] bg-[#fffdfa] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Rent Collected</span>
            <p class="mt-1 font-mono text-xl font-black text-emerald-700">ETB {{ number_format($totalRentCollected, 2) }}</p>
            <span class="text-[11px] text-emerald-800 font-bold">{{ $rentSchedules->where('status', 'paid')->count() }} installments paid</span>
        </div>

        <div class="rounded-2xl border border-[#d9cab3] bg-[#fffdfa] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Overdue Installments</span>
            <p class="mt-1 font-mono text-xl font-black text-rose-700">{{ $overdueRentCount }}</p>
            <span class="text-[11px] text-rose-800 font-bold">Requires tenant follow-up</span>
        </div>

        <div class="rounded-2xl border border-[#d9cab3] bg-[#fffdfa] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Outstanding Balance</span>
            <p class="mt-1 font-mono text-xl font-black text-amber-800">ETB {{ number_format($outstandingRent, 2) }}</p>
            <span class="text-[11px] text-slate-500">Remaining contract receivables</span>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-[#f0e4cf] flex items-center gap-4 text-xs font-bold mb-4">
        <button type="button" onclick="switchLeaseTab('contracts')" id="tab-btn-contracts" class="lease-tab-btn pb-2 border-b-2 border-[#1d3c34] text-[#1d3c34]">
            Active Contracts ({{ $leases->count() }})
        </button>
        <button type="button" onclick="switchLeaseTab('schedules')" id="tab-btn-schedules" class="lease-tab-btn pb-2 border-b-2 border-transparent text-slate-400 hover:text-slate-700">
            Rent Installment Schedules ({{ $rentSchedules->count() }})
        </button>
        <button type="button" onclick="switchLeaseTab('overdue')" id="tab-btn-overdue" class="lease-tab-btn pb-2 border-b-2 border-transparent text-slate-400 hover:text-slate-700">
            Overdue Rent ({{ $overdueRentCount }})
        </button>
    </div>

    {{-- TAB 1: CONTRACTS --}}
    <div id="lease-view-contracts" class="lease-tab-content">
        @if ($leases->isEmpty())
            <div class="text-center py-12 text-slate-400">
                <svg class="h-12 w-12 mx-auto mb-3 opacity-40 text-[#1d3c34]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="font-bold text-slate-600">No Lease Contracts Registered Yet</p>
                <p class="text-xs mt-1">Click "+ New Lease Agreement" to enroll tenants and generate automatic monthly installment schedules.</p>
                <button type="button" onclick="openCreateLeaseModal()" class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-[#102b25] px-4 py-2 text-xs font-bold text-[#d4af37]">
                    + Register First Lease
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#fcf8f2] text-slate-500 font-bold uppercase tracking-wider border-b border-[#f0e4cf]">
                        <tr>
                            <th class="py-3 px-3">Lease Code</th>
                            <th class="py-3 px-3">Property</th>
                            <th class="py-3 px-3">Tenant Details</th>
                            <th class="py-3 px-3">Monthly Rent</th>
                            <th class="py-3 px-3">Duration &amp; Term</th>
                            <th class="py-3 px-3">Collection Progress</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f5ede0]">
                        @foreach ($leases as $l)
                            @php
                                $totalDue = $l->total_rent_due ?: 1;
                                $paidPct = min(100, round(($l->total_rent_paid / $totalDue) * 100));
                            @endphp
                            <tr class="hover:bg-[#fcf9f5] transition">
                                <td class="py-3 px-3 font-mono font-bold text-[#1d3c34]">
                                    {{ $l->lease_code }}
                                    <div class="text-[10px] text-slate-400 font-normal">Freq: {{ ucfirst($l->payment_frequency) }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <a href="{{ route('properties.show', $l->property->slug ?? $l->property->id) }}" class="font-bold text-[#1d3c34] hover:underline" target="_blank">
                                        {{ Str::limit($l->property->title ?? 'Property', 25) }}
                                    </a>
                                    <div class="text-[10px] text-slate-500">{{ $l->property->city ?? 'Addis Ababa' }} · {{ $l->property->address ?? 'Ethiopia' }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800">{{ $l->tenant_name }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $l->tenant_phone }} · {{ $l->tenant_email }}</div>
                                    @if ($l->tenant_id_number)
                                        <div class="text-[10px] font-mono text-[#587165]">{{ strtoupper($l->tenant_id_type) }}: {{ $l->tenant_id_number }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-mono">
                                    <div class="font-black text-[#1d3c34]">ETB {{ number_format($l->monthly_rent, 2) }}</div>
                                    @if ($l->security_deposit > 0)
                                        <div class="text-[10px] text-slate-400">Dep: ETB {{ number_format($l->security_deposit, 2) }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-700">{{ $l->duration_months }} Months</div>
                                    <div class="text-[10px] text-slate-500">{{ $l->start_date?->format('M d, Y') }} — {{ $l->end_date?->format('M d, Y') }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="flex items-center justify-between text-[10px] font-bold text-slate-600 mb-1">
                                        <span>ETB {{ number_format($l->total_rent_paid, 0) }}</span>
                                        <span>{{ $paidPct }}%</span>
                                    </div>
                                    <div class="w-24 bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ $paidPct }}%"></div>
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-black border {{ $l->status_badge[1] }}">
                                        {{ $l->status_badge[0] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('leases.agreement', $l->id) }}" target="_blank" class="rounded-lg border border-[#102b25] bg-[#102b25] px-2.5 py-1 text-[11px] font-bold text-[#d4af37] hover:bg-[#1d3c34] transition">
                                            ውል (PDF)
                                        </a>
                                        <button type="button" onclick="viewLeaseSchedule({{ $l->id }})" class="rounded-lg border border-[#d9cab3] bg-white px-2.5 py-1 text-[11px] font-bold text-[#1d3c34] hover:bg-[#fcf8f2] transition cursor-pointer">
                                            Schedule
                                        </button>
                                        <form method="POST" action="{{ route('leases.update-status', $l->id) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            @if ($l->status === 'active')
                                                <input type="hidden" name="status" value="terminated">
                                                <button type="submit" onclick="return confirm('Are you sure you want to terminate this lease?')" class="rounded-lg border border-rose-200 bg-rose-50 px-2 py-1 text-[10px] font-bold text-rose-700 hover:bg-rose-100 transition cursor-pointer">
                                                    Terminate
                                                </button>
                                            @else
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700 hover:bg-emerald-100 transition cursor-pointer">
                                                    Activate
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($leases instanceof \Illuminate\Pagination\LengthAwarePaginator && $leases->hasPages())
                <div class="mt-6 border-t border-[#e7ddca] pt-4">
                    {{ $leases->fragment('leases')->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- TAB 2: SCHEDULES --}}
    <div id="lease-view-schedules" class="lease-tab-content hidden">
        @if ($rentSchedules->isEmpty())
            <div class="text-center py-12 text-slate-400">
                <p class="font-bold text-slate-600">No Rent Installments Scheduled</p>
                <p class="text-xs mt-1">Schedules are generated automatically whenever a lease is registered.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#fcf8f2] text-slate-500 font-bold uppercase tracking-wider border-b border-[#f0e4cf]">
                        <tr>
                            <th class="py-3 px-3">Period / Label</th>
                            <th class="py-3 px-3">Lease &amp; Property</th>
                            <th class="py-3 px-3">Tenant</th>
                            <th class="py-3 px-3">Due Date</th>
                            <th class="py-3 px-3">Amount Due</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f5ede0]">
                        @foreach ($rentSchedules as $s)
                            <tr class="hover:bg-[#fcf9f5] transition">
                                <td class="py-3 px-3 font-bold text-slate-800">
                                    {{ $s->period_label }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-mono text-[#1d3c34] font-bold">{{ $s->lease->lease_code ?? 'N/A' }}</span>
                                    <div class="text-[10px] text-slate-500">{{ Str::limit($s->lease->property->title ?? 'Property', 22) }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-700">{{ $s->lease->tenant_name ?? 'Tenant' }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $s->lease->tenant_phone ?? '' }}</div>
                                </td>
                                <td class="py-3 px-3 font-mono">
                                    <span class="{{ $s->is_overdue ? 'text-rose-700 font-bold' : 'text-slate-600' }}">
                                        {{ $s->due_date?->format('M d, Y') }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-mono font-black text-[#1d3c34]">
                                    ETB {{ number_format($s->amount_due, 2) }}
                                    @if ($s->amount_paid > 0 && $s->status !== 'paid')
                                        <div class="text-[10px] text-emerald-600">Paid: ETB {{ number_format($s->amount_paid, 2) }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-black border {{ $s->status_badge[1] }}">
                                        {{ $s->status_badge[0] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    @if ($s->status === 'paid')
                                        @if ($s->payment)
                                            <a href="{{ route('payments.receipt', $s->payment->transaction_number) }}" target="_blank" class="rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-800 hover:bg-emerald-100 transition">
                                                Receipt →
                                            </a>
                                        @else
                                            <span class="text-[11px] font-bold text-emerald-700">Settled ✓</span>
                                        @endif
                                    @else
                                        <button type="button" onclick="openRecordRentPaymentModal({{ $s->id }}, '{{ addslashes($s->period_label) }}', {{ $s->amount_due }})" class="rounded-lg bg-[#102b25] px-2.5 py-1 text-[11px] font-bold text-[#d4af37] hover:bg-[#1d3c34] transition cursor-pointer">
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

    {{-- TAB 3: OVERDUE --}}
    <div id="lease-view-overdue" class="lease-tab-content hidden">
        @if ($overdueSchedules->isEmpty())
            <div class="text-center py-12 text-slate-400">
                <svg class="h-12 w-12 mx-auto mb-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-bold text-emerald-800 text-sm">All Rent Installments Are Up To Date!</p>
                <p class="text-xs text-slate-500 mt-0.5">No overdue rental schedules currently exist.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-rose-50/60 text-rose-900 font-bold uppercase tracking-wider border-b border-rose-200">
                        <tr>
                            <th class="py-3 px-3">Period</th>
                            <th class="py-3 px-3">Tenant &amp; Contact</th>
                            <th class="py-3 px-3">Property</th>
                            <th class="py-3 px-3">Original Due Date</th>
                            <th class="py-3 px-3">Overdue Amount</th>
                            <th class="py-3 px-3 text-right">Collect Rent</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rose-100">
                        @foreach ($overdueSchedules as $s)
                            <tr class="hover:bg-rose-50/30 transition">
                                <td class="py-3 px-3 font-bold text-rose-950">{{ $s->period_label }}</td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800">{{ $s->lease->tenant_name }}</div>
                                    <div class="text-[10px] text-slate-600">{{ $s->lease->tenant_phone }} · {{ $s->lease->tenant_email }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-[#1d3c34]">{{ Str::limit($s->lease->property->title ?? 'Property', 25) }}</span>
                                    <div class="text-[10px] font-mono text-slate-500">{{ $s->lease->lease_code }}</div>
                                </td>
                                <td class="py-3 px-3 font-mono font-bold text-rose-700">
                                    {{ $s->due_date?->format('M d, Y') }}
                                </td>
                                <td class="py-3 px-3 font-mono font-black text-rose-800">
                                    ETB {{ number_format($s->amount_due - $s->amount_paid, 2) }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <button type="button" onclick="openRecordRentPaymentModal({{ $s->id }}, '{{ addslashes($s->period_label) }}', {{ $s->amount_due - $s->amount_paid }})" class="rounded-lg bg-rose-700 px-3 py-1 text-[11px] font-bold text-white hover:bg-rose-800 transition cursor-pointer">
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
</section>

{{-- MODAL 1: CREATE LEASE AGREEMENT --}}
<div id="create-lease-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 p-4 backdrop-blur-xs flex items-center justify-center">
    <div class="relative w-full max-w-2xl rounded-3xl bg-white p-6 sm:p-8 shadow-2xl border border-[#d9cab3] my-8 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
            <div>
                <span class="rounded bg-[#102b25] px-2 py-0.5 text-[9px] font-black uppercase text-[#d4af37]">Ethiopian Tenancy Contract</span>
                <h3 class="text-xl font-black text-[#1d3c34] mt-1">Register New Lease Agreement</h3>
            </div>
            <button type="button" onclick="closeCreateLeaseModal()" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('leases.store') }}" class="mt-5 space-y-4">
            @csrf

            {{-- Property Selection --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Select Property to Lease *</label>
                <select name="property_id" id="lease-property-select" onchange="autoPopulatePropertyRent(this)" required class="w-full rounded-xl border border-[#cdbb97] px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#1d3c34] focus:outline-none">
                    <option value="">-- Choose a Property --</option>
                    @foreach ($rentalProperties as $rp)
                        <option value="{{ $rp->id }}" data-rent="{{ (float) $rp->price }}">
                            {{ $rp->title }} ({{ $rp->city ?? 'Addis Ababa' }} - ETB {{ number_format($rp->price) }}/mo)
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tenant KYC Information --}}
            <div class="rounded-2xl border border-[#e8ded0] bg-[#faf6f0] p-4">
                <p class="text-xs font-black uppercase tracking-wider text-[#1d3c34] mb-3">Tenant Information (የተከራይ መረጃ)</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Tenant Full Name *</label>
                        <input type="text" name="tenant_name" required placeholder="e.g. Almaz Bekele" class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Tenant Email *</label>
                        <input type="email" name="tenant_email" required placeholder="almaz@example.com" class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Tenant Phone Number *</label>
                        <input type="tel" name="tenant_phone" required placeholder="0911XXXXXX" class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">ID Type &amp; Number</label>
                        <div class="grid grid-cols-2 gap-1.5">
                            <select name="tenant_id_type" class="rounded-xl border border-[#cdbb97] px-2 py-2 text-xs focus:border-[#1d3c34] focus:outline-none bg-white">
                                <option value="kebele_id">Kebele ID</option>
                                <option value="passport">Passport</option>
                                <option value="national_id">National ID / Fayda</option>
                            </select>
                            <input type="text" name="tenant_id_number" placeholder="ID Number" class="rounded-xl border border-[#cdbb97] px-2 py-2 text-xs focus:border-[#1d3c34] focus:outline-none bg-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact_name" placeholder="Contact Person" class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Emergency Contact Phone</label>
                        <input type="tel" name="emergency_contact_phone" placeholder="09XXXXXXXX" class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none bg-white">
                    </div>
                </div>
            </div>

            {{-- Financial & Term Parameters --}}
            <div class="rounded-2xl border border-[#e8ded0] bg-[#faf6f0] p-4">
                <p class="text-xs font-black uppercase tracking-wider text-[#1d3c34] mb-3">Lease Financial Terms (የውል እና የክፍያ ውሎች)</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Start Date *</label>
                        <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Duration (Months) *</label>
                        <select name="duration_months" required class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none bg-white">
                            <option value="6">6 Months</option>
                            <option value="12" selected>12 Months (1 Year)</option>
                            <option value="24">24 Months (2 Years)</option>
                            <option value="36">36 Months (3 Years)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Payment Frequency *</label>
                        <select name="payment_frequency" required class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none bg-white">
                            <option value="monthly" selected>Monthly (በየወሩ)</option>
                            <option value="quarterly">Quarterly (በየ3 ወሩ)</option>
                            <option value="semi_annually">Semi-Annually (በየ6 ወሩ)</option>
                            <option value="annually">Annually (በአመት አንድ ጊዜ)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Monthly Rent (ETB) *</label>
                        <input type="number" step="0.01" name="monthly_rent" id="lease-monthly-rent" required placeholder="30000" class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-xs font-mono font-bold focus:border-[#1d3c34] focus:outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Security Deposit (ETB)</label>
                        <input type="number" step="0.01" name="security_deposit" value="0" placeholder="0.00" class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-xs font-mono focus:border-[#1d3c34] focus:outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Grace Period (Days)</label>
                        <input type="number" name="grace_period_days" value="5" min="0" max="30" class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none bg-white">
                    </div>
                </div>
            </div>

            {{-- Terms & Notes --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Special Terms / Provisions (Optional)</label>
                <textarea name="terms_and_conditions" rows="2" placeholder="e.g. Utilities included/excluded, maintenance responsibilities..." class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#f0e4cf]">
                <button type="button" onclick="closeCreateLeaseModal()" class="rounded-full border border-slate-300 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" class="rounded-full bg-[#102b25] px-6 py-2.5 text-xs font-black text-[#d4af37] hover:bg-[#1d3c34] transition shadow-md">
                    Enroll Lease &amp; Generate Schedules
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: RECORD RENT INSTALLMENT PAYMENT --}}
<div id="record-rent-payment-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 p-4 backdrop-blur-xs flex items-center justify-center">
    <div class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl border border-[#d9cab3]">
        <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-3">
            <div>
                <span class="rounded bg-[#102b25] px-2 py-0.5 text-[9px] font-black uppercase text-[#d4af37]">Ethiopian Payment Core</span>
                <h3 class="text-lg font-black text-[#1d3c34] mt-1">Record Rent Payment</h3>
            </div>
            <button type="button" onclick="closeRecordRentPaymentModal()" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="record-rent-payment-form" method="POST" action="" class="mt-4 space-y-3.5">
            @csrf
            <div>
                <p id="rent-payment-period-display" class="text-xs font-bold text-[#1d3c34] bg-[#fcf8f2] p-2.5 rounded-xl border border-[#f0e4cf]"></p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Amount Paid (ETB) *</label>
                <input type="number" step="0.01" name="amount_paid" id="rent-payment-amount" required class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-sm font-mono font-bold text-[#1d3c34] focus:border-[#1d3c34] focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Payment Method (የክፍያ መንገድ) *</label>
                <select name="payment_method" required class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none">
                    <option value="telebirr">Telebirr (ቴሌብር)</option>
                    <option value="cbe_birr">CBE Birr (ሲቢኢ ብር)</option>
                    <option value="bank_transfer" selected>Bank Transfer / Deposit Slip (ባንክ ክፍያ)</option>
                    <option value="cash">Cash at Office (ጥሬ ገንዘብ)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Bank Reference / FT Number</label>
                <input type="text" name="bank_transaction_reference" placeholder="e.g. FT24099XXXXX" class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-xs font-mono focus:border-[#1d3c34] focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Receipt Notes (Optional)</label>
                <input type="text" name="notes" placeholder="e.g. Paid in full via CBE Mobile" class="w-full rounded-xl border border-[#cdbb97] px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#f0e4cf]">
                <button type="button" onclick="closeRecordRentPaymentModal()" class="rounded-full border border-slate-300 px-4 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="rounded-full bg-[#102b25] px-5 py-2 text-xs font-black text-[#d4af37] hover:bg-[#1d3c34] shadow-sm">
                    Confirm &amp; Issue Receipt
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: VIEW LEASE FULL SCHEDULE MODAL --}}
<div id="view-lease-schedule-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 p-4 backdrop-blur-xs flex items-center justify-center">
    <div class="relative w-full max-w-3xl rounded-3xl bg-white p-6 shadow-2xl border border-[#d9cab3] my-8 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
            <div>
                <span id="modal-lease-code" class="rounded bg-[#102b25] px-2 py-0.5 text-[9px] font-black uppercase text-[#d4af37]"></span>
                <h3 id="modal-lease-title" class="text-xl font-black text-[#1d3c34] mt-1">Lease Installment Schedule</h3>
                <p id="modal-lease-tenant" class="text-xs text-slate-500"></p>
            </div>
            <button type="button" onclick="closeLeaseScheduleModal()" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="my-4 grid grid-cols-3 gap-3 text-center text-xs">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-2.5">
                <span class="text-[10px] text-slate-400 uppercase font-bold">Total Contract Value</span>
                <p id="modal-total-due" class="font-mono font-black text-[#1d3c34] text-sm mt-0.5"></p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-2.5">
                <span class="text-[10px] text-emerald-700 uppercase font-bold">Total Collected</span>
                <p id="modal-total-paid" class="font-mono font-black text-emerald-800 text-sm mt-0.5"></p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-2.5">
                <span class="text-[10px] text-amber-700 uppercase font-bold">Outstanding Balance</span>
                <p id="modal-balance" class="font-mono font-black text-amber-900 text-sm mt-0.5"></p>
            </div>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#fcf8f2] text-slate-500 font-bold uppercase tracking-wider border-b border-[#f0e4cf]">
                    <tr>
                        <th class="py-2.5 px-3">#</th>
                        <th class="py-2.5 px-3">Installment Period</th>
                        <th class="py-2.5 px-3">Due Date</th>
                        <th class="py-2.5 px-3">Amount Due</th>
                        <th class="py-2.5 px-3">Status</th>
                    </tr>
                </thead>
                <tbody id="modal-schedules-tbody" class="divide-y divide-[#f5ede0]">
                    {{-- Dynamically populated via JS --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function switchLeaseTab(tabName) {
        document.querySelectorAll('.lease-tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.lease-tab-btn').forEach(btn => {
            btn.classList.remove('border-[#1d3c34]', 'text-[#1d3c34]');
            btn.classList.add('border-transparent', 'text-slate-400');
        });

        const activeContent = document.getElementById('lease-view-' + tabName);
        const activeBtn = document.getElementById('tab-btn-' + tabName);
        if (activeContent) activeContent.classList.remove('hidden');
        if (activeBtn) {
            activeBtn.classList.remove('border-transparent', 'text-slate-400');
            activeBtn.classList.add('border-[#1d3c34]', 'text-[#1d3c34]');
        }
    }

    function openCreateLeaseModal() {
        document.getElementById('create-lease-modal').classList.remove('hidden');
    }

    function closeCreateLeaseModal() {
        document.getElementById('create-lease-modal').classList.add('hidden');
    }

    function autoPopulatePropertyRent(selectEl) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const rent = selectedOption.getAttribute('data-rent');
        if (rent && parseFloat(rent) > 0) {
            document.getElementById('lease-monthly-rent').value = parseFloat(rent);
        }
    }

    function openRecordRentPaymentModal(scheduleId, periodLabel, amountDue) {
        const form = document.getElementById('record-rent-payment-form');
        form.action = '/rent-schedules/' + scheduleId + '/pay';
        document.getElementById('rent-payment-period-display').innerText = 'Recording payment for: ' + periodLabel;
        document.getElementById('rent-payment-amount').value = parseFloat(amountDue).toFixed(2);
        document.getElementById('record-rent-payment-modal').classList.remove('hidden');
    }

    function closeRecordRentPaymentModal() {
        document.getElementById('record-rent-payment-modal').classList.add('hidden');
    }

    function viewLeaseSchedule(leaseId) {
        fetch('/leases/' + leaseId)
            .then(res => res.json())
            .then(data => {
                document.getElementById('modal-lease-code').innerText = data.lease.lease_code;
                document.getElementById('modal-lease-title').innerText = data.lease.property ? data.lease.property.title : 'Lease Agreement';
                document.getElementById('modal-lease-tenant').innerText = 'Tenant: ' + data.lease.tenant_name + ' (' + data.lease.tenant_phone + ')';
                document.getElementById('modal-total-due').innerText = 'ETB ' + parseFloat(data.summary.total_rent_due).toLocaleString('en-US', {minimumFractionDigits: 2});
                document.getElementById('modal-total-paid').innerText = 'ETB ' + parseFloat(data.summary.total_rent_paid).toLocaleString('en-US', {minimumFractionDigits: 2});
                document.getElementById('modal-balance').innerText = 'ETB ' + parseFloat(data.summary.outstanding_balance).toLocaleString('en-US', {minimumFractionDigits: 2});

                const tbody = document.getElementById('modal-schedules-tbody');
                tbody.innerHTML = '';

                data.schedules.forEach(s => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-[#fcf9f5] transition';
                    const statusClass = s.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : (new Date(s.due_date) < new Date() ? 'bg-rose-100 text-rose-800' : 'bg-blue-50 text-blue-800');
                    const statusText = s.status === 'paid' ? 'Paid ✓' : (new Date(s.due_date) < new Date() ? 'Overdue' : 'Pending');

                    tr.innerHTML = `
                        <td class="py-2.5 px-3 font-mono font-bold">${s.installment_number}</td>
                        <td class="py-2.5 px-3 font-bold text-slate-800">${s.period_label}</td>
                        <td class="py-2.5 px-3 font-mono text-slate-600">${s.due_date}</td>
                        <td class="py-2.5 px-3 font-mono font-black text-[#1d3c34]">ETB ${parseFloat(s.amount_due).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        <td class="py-2.5 px-3"><span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-black ${statusClass}">${statusText}</span></td>
                    `;
                    tbody.appendChild(tr);
                });

                document.getElementById('view-lease-schedule-modal').classList.remove('hidden');
            });
    }

    function closeLeaseScheduleModal() {
        document.getElementById('view-lease-schedule-modal').classList.add('hidden');
    }
</script>
