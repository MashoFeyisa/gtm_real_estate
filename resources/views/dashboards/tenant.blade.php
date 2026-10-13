@extends('dashboards.layout')

@section('title', 'Tenant Dashboard')
@section('portal_name', 'Tenant Workspace')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if (auth()->user()->hasAnyPermission(['leases.manage', 'leases.view', 'agreements.view', 'leases.terminate', 'agreements.sign']))
        <a href="#leases" data-target-section="leases" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🔑</span>
                <span>Leases</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $leases->count() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['rent.view', 'rent.track', 'rent.manage', 'rent.pay', 'payments.view']))
        <a href="#rent-schedule" data-target-section="rent-schedule" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>💰</span>
                <span>Rent Schedule</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $rentSchedules->count() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['maintenance.create', 'maintenance.view', 'maintenance.manage', 'maintenance.tickets']))
        <a href="#maintenance" data-target-section="maintenance" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🛠️</span>
                <span>Maintenance</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $maintenanceTickets->count() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['documents.view', 'documents.create', 'documents.upload', 'property.documents']))
        <a href="#documents" data-target-section="documents" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📄</span>
                <span>Documents</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $documents->count() }}</span>
        </a>
    @endif
@endsection

@section('content')
<div class="space-y-8">
    {{-- Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- Maintenance Request Modal/Panel --}}
    <div id="maintenance-form" class="hidden rounded-3xl border border-[#d4af37] bg-[#fbf8f3] p-6 shadow-sm">
        <h3 class="text-lg font-black text-[#1d3c34] mb-3">Submit Maintenance Request</h3>
        <form method="POST" action="{{ route('maintenance.store') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700">Rented Property *</label>
                <select name="property_id" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                    @foreach ($leases as $lease)
                        @if ($lease->property)
                            <option value="{{ $lease->property->id }}">{{ $lease->property->title }} (Lease #{{ $lease->lease_code }})</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Priority Level *</label>
                <select name="priority" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                    <option value="normal" selected>Normal</option>
                    <option value="urgent">Urgent</option>
                    <option value="emergency">Emergency (Plumbing/Electrical)</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700">Issue Summary *</label>
                <input type="text" name="title" required placeholder="e.g. Water heater leak in master bathroom" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700">Detailed Description *</label>
                <textarea name="description" rows="3" required placeholder="Provide details on the issue location and severity..." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs"></textarea>
            </div>
            <div class="sm:col-span-2 flex justify-end">
                <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white hover:bg-[#254d43]">
                    Submit Request
                </button>
            </div>
        </form>
    </div>

    {{-- 1. Active Leases --}}
    <div id="leases" data-section="leases" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">My Lease Agreements</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            @forelse ($leases as $lease)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5">
                    <div class="flex items-center justify-between border-b border-[#e7ddca] pb-2">
                        <span class="font-mono text-xs font-bold text-[#1d3c34]">Lease #{{ $lease->lease_code }}</span>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $lease->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                            {{ ucfirst($lease->status) }}
                        </span>
                    </div>
                    <h3 class="mt-3 font-bold text-base text-[#1d3c34]">{{ $lease->property?->title ?? 'Rented Property' }}</h3>
                    <p class="text-xs text-slate-500 mt-1">📍 {{ $lease->property?->city ?? 'Addis Ababa' }}</p>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs border-t border-[#e7ddca] pt-3">
                        <div>
                            <span class="text-slate-400">Monthly Rent:</span>
                            <p class="font-bold text-emerald-800">{{ number_format($lease->monthly_rent) }} ETB</p>
                        </div>
                        <div>
                            <span class="text-slate-400">Lease Term:</span>
                            <p class="font-semibold">{{ $lease->start_date?->format('M Y') }} – {{ $lease->end_date?->format('M Y') }}</p>
                        </div>
                    </div>
                    @if (Route::has('leases.agreement'))
                        <div class="mt-4 pt-2 border-t border-[#e7ddca] flex justify-end">
                            <a href="{{ route('leases.agreement', $lease) }}" class="text-xs font-bold text-[#1d3c34] hover:underline">
                                📄 Download Official Agreement (PDF) &rarr;
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <p class="col-span-2 text-xs text-slate-400 py-4 text-center">No active leases on file.</p>
            @endforelse
        </div>
    </div>

    {{-- 2. Rent Payment Schedule --}}
    <div id="rent-schedule" data-section="rent-schedule" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Rent Schedule &amp; Payment History</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e7ddca] text-sm">
                <thead>
                    <tr class="bg-[#fbf9f5] text-left text-xs font-black uppercase text-[#587165]">
                        <th class="py-3 px-4">Period</th>
                        <th class="py-3 px-4">Due Date</th>
                        <th class="py-3 px-4">Amount Due</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Paid At</th>
                        <th class="py-3 px-4 text-right">Payment Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca]">
                    @forelse ($rentSchedules as $schedule)
                        <tr class="hover:bg-[#fcfaf7]">
                            <td class="py-3 px-4 font-bold text-xs text-[#1d3c34]">{{ $schedule->period_label ?? ('Installment #' . $schedule->installment_number) }}</td>
                            <td class="py-3 px-4 text-xs font-semibold">{{ $schedule->due_date?->format('M d, Y') }}</td>
                            <td class="py-3 px-4 font-bold text-emerald-800">{{ number_format($schedule->amount_due) }} ETB</td>
                            <td class="py-3 px-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ match ($schedule->status) { 'paid' => 'bg-emerald-100 text-emerald-800', 'overdue' => 'bg-rose-100 text-rose-800', default => 'bg-amber-100 text-amber-800' } }}">
                                    {{ ucfirst($schedule->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-500">{{ $schedule->paid_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="py-3 px-4 text-right">
                                @if ($schedule->status !== 'paid' && Route::has('rent-schedules.pay'))
                                    <button type="button" 
                                            onclick="openTenantRentModal('{{ route('rent-schedules.pay', $schedule) }}', '{{ addslashes($schedule->period_label ?? ('Installment #' . $schedule->installment_number)) }}', {{ (float) max(1, $schedule->amount_due - $schedule->amount_paid) }})" 
                                            class="rounded-full bg-[#1d3c34] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#254d43] shadow-xs cursor-pointer transition">
                                         Pay {{ number_format(max(1, $schedule->amount_due - $schedule->amount_paid)) }} ETB
                                    </button>
                                @else
                                    <span class="text-xs text-emerald-700 font-bold">✓ Settled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-xs text-slate-400">No rent payment schedule recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. Maintenance Service Tickets --}}
    <div id="maintenance" data-section="maintenance" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Maintenance Tickets</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($maintenanceTickets as $ticket)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono font-bold text-slate-600">{{ $ticket->ticket_number }}</span>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ match ($ticket->status) { 'resolved' => 'bg-emerald-100 text-emerald-800', 'in_progress' => 'bg-blue-100 text-blue-800', default => 'bg-amber-100 text-amber-800' } }}">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </div>
                    <h4 class="mt-2 font-bold text-sm text-[#1d3c34]">{{ $ticket->title }}</h4>
                    <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $ticket->description }}</p>
                    <div class="mt-3 pt-2 border-t border-[#e7ddca] flex items-center justify-between text-[11px] text-slate-500">
                        <span>Priority: <strong class="text-slate-700">{{ ucfirst($ticket->priority) }}</strong></span>
                        <span>{{ $ticket->created_at->format('M d') }}</span>
                    </div>
                </div>
            @empty
                <p class="col-span-3 text-xs text-slate-400 py-4 text-center">No maintenance tickets filed.</p>
            @endforelse
        </div>
    </div>

    {{-- 4. Tenancy & Property Documents --}}
    <div id="documents" data-section="documents" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Lease &amp; Property Documents</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($documents as $doc)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-4 flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-sm text-[#1d3c34]">{{ $doc->title }}</h4>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $doc->formatted_type }}</p>
                    </div>
                    @if ($doc->property && Route::has('properties.documents.download'))
                        <a href="{{ route('properties.documents.download', [$doc->property, $doc]) }}" class="rounded-full bg-[#1d3c34] px-3 py-1 text-xs font-bold text-white hover:bg-[#254d43]">
                            Download
                        </a>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-xs text-slate-400 py-4 text-center">No documents shared for this tenancy.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Rent Installment Payment Modal for Tenant --}}
<div id="tenant-rent-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 p-4 backdrop-blur-xs flex items-center justify-center">
    <div class="relative w-full max-w-md rounded-3xl border border-[#d9cab3] bg-[#fdfaf5] p-6 shadow-2xl">
        <div class="flex items-center justify-between border-b border-[#f0e8d8] pb-3">
            <div>
                <span class="rounded bg-[#102b25] px-2 py-0.5 text-[9px] font-black uppercase text-[#d4af37]">Ethiopian Digital Payment</span>
                <h3 class="mt-1 text-lg font-black text-[#1d3c34]">Pay Rent Installment</h3>
            </div>
            <button type="button" onclick="closeTenantRentModal()" class="cursor-pointer text-2xl font-bold text-slate-400 hover:text-slate-800">&times;</button>
        </div>

        <form id="tenant-rent-payment-form" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            <div id="tenant-rent-period-display" class="rounded-xl border border-[#e7ddca] bg-[#f9f4ed] p-3 text-xs font-bold text-[#1d3c34]"></div>

            <div>
                <label class="mb-1 block text-xs font-bold text-slate-700">Payment Amount (ETB) *</label>
                <input type="number" step="0.01" min="1" name="amount_paid" id="tenant-rent-amount" required class="w-full rounded-xl border border-[#cdbb97] bg-white px-3 py-2 font-mono text-sm font-bold text-[#1d3c34] focus:border-[#1d3c34] focus:outline-none">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold text-slate-700">Payment Method (የክፍያ አማራጭ) *</label>
                <select name="payment_method" id="tenant-rent-method" required class="w-full rounded-xl border border-[#cdbb97] bg-white px-3 py-2 text-xs font-bold focus:border-[#1d3c34] focus:outline-none">
                    <option value="telebirr" selected>Telebirr (ቴሌብር) — Instant Mobile Wallet</option>
                    <option value="cbe_birr">CBE Birr (ሲቢኢ ብር) — Commercial Bank</option>
                    <option value="bank_transfer">Bank Transfer (የባንክ ሒሳብ ማስተላለፊያ)</option>
                    <option value="cash">Cash at Office (ጥሬ ገንዘብ)</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold text-slate-700">Bank / Wallet Reference (Optional)</label>
                <input type="text" name="bank_transaction_reference" placeholder="e.g. FT26... or Telebirr Trans ID" class="w-full rounded-xl border border-[#cdbb97] bg-white px-3 py-2 font-mono text-xs focus:border-[#1d3c34] focus:outline-none">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold text-slate-700">Notes (Optional)</label>
                <input type="text" name="notes" placeholder="e.g. Rent installment settlement" class="w-full rounded-xl border border-[#cdbb97] bg-white px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-[#f0e8d8] pt-4">
                <button type="button" onclick="closeTenantRentModal()" class="cursor-pointer rounded-full border border-slate-300 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="cursor-pointer rounded-full bg-[#102b25] px-6 py-2.5 text-xs font-black text-[#d4af37] shadow-sm hover:bg-[#1d3c34] transition">
                    Confirm &amp; Pay
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openTenantRentModal(actionUrl, periodLabel, amountDue) {
        const modal = document.getElementById('tenant-rent-modal');
        const form = document.getElementById('tenant-rent-payment-form');
        if (!modal || !form) return;
        form.action = actionUrl;
        document.getElementById('tenant-rent-period-display').innerText = 'Paying rent for: ' + periodLabel;
        document.getElementById('tenant-rent-amount').value = parseFloat(amountDue).toFixed(2);
        modal.classList.remove('hidden');
    }

    function closeTenantRentModal() {
        const modal = document.getElementById('tenant-rent-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }
</script>
@endsection
