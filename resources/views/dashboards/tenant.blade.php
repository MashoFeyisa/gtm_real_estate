@extends('dashboards.layout')

@section('title', 'Tenant Dashboard')
@section('portal_name', 'Tenant Workspace')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>

    @if (auth()->user()->hasAnyPermission(['applications.view', 'applications.create']))
        <a href="#applications" data-target-section="applications" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📝</span>
                <span>Applications</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $rentalApplications->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['leases.view', 'leases.manage', 'agreements.view', 'leases.sign']))
        <a href="#leases" data-target-section="leases" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🔑</span>
                <span>My Leases</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $leases->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['rent.view', 'rent.pay', 'rent.track']))
        <a href="#rent-schedule" data-target-section="rent-schedule" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>💰</span>
                <span>Rent Schedule</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $rentSchedules->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['maintenance.view', 'maintenance.create']))
        <a href="#maintenance" data-target-section="maintenance" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🛠️</span>
                <span>Maintenance</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $maintenanceTickets->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['landlords.communicate', 'messages.view', 'inquiries.view']))
        <a href="#messages" data-target-section="messages" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>💬</span>
                <span>Landlord Messages</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $inquiries->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['documents.view', 'documents.create']))
        <a href="#documents" data-target-section="documents" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📁</span>
                <span>Tenancy Vault</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $documents->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['rentals.search', 'properties.view']))
        <a href="#rentals" data-target-section="rentals" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🏠</span>
                <span>Find Rentals</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $rentalProperties->count() }}</span>
        </a>
    @endif
@endsection

@section('content')
<div class="space-y-8">
    {{-- Session Feedback Messages --}}
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800 flex items-center justify-between shadow-xs">
            <span>✅ {{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-800 flex items-center justify-between shadow-xs">
            <span>⚠️ {{ session('error') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">&times;</button>
        </div>
    @endif

    {{-- Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- 1. Rental Applications --}}
    <div id="applications" data-section="applications" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#e7ddca] pb-4 dark:border-slate-800">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34] dark:text-white">Rental Applications</h2>
                <p class="text-xs text-slate-500">Apply for residential and commercial rentals, attach proof of income, and track approval status.</p>
            </div>
            <button type="button" onclick="document.getElementById('new-application-form').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition">
                + New Rental Application
            </button>
        </div>

        {{-- New Application Form --}}
        <div id="new-application-form" class="hidden mt-4 rounded-2xl border border-[#d4af37] bg-[#fbf8f3] p-5 dark:border-amber-700 dark:bg-slate-800">
            <h3 class="text-sm font-black text-[#1d3c34] mb-3 dark:text-white">Submit Rental Tenancy Application</h3>
            <form method="POST" action="{{ route('tenant.applications.store') }}" enctype="multipart/form-data" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                @csrf
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Target Rental Property *</label>
                    <select name="property_id" id="application-property-id" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                        <option value="">— Select a rental property —</option>
                        @foreach ($rentalProperties as $rp)
                            <option value="{{ $rp->id }}">{{ $rp->title }} ({{ $rp->city }}, {{ $rp->subcity ?? '' }} &bull; {{ number_format($rp->price) }} ETB/mo)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Proposed Monthly Rent (ETB) *</label>
                    <input type="number" name="offered_price" required placeholder="e.g. 35000" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Proposed Security Deposit (ETB)</label>
                    <input type="number" name="deposit_proposed" placeholder="e.g. 70000 (2 months)" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Desired Move-in Date</label>
                    <input type="date" name="valid_until" min="{{ date('Y-m-d') }}" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Supporting Document (PDF, PNG, JPG)</label>
                    <input type="file" name="supporting_document" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                </div>
                <div class="sm:col-span-2 md:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Applicant Notes / Employment Background</label>
                    <textarea name="message" rows="2" placeholder="Briefly state your occupation, number of occupants, and lease term preference..." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white"></textarea>
                </div>
                <div class="sm:col-span-2 md:col-span-3 flex justify-end">
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2 text-xs font-bold text-white hover:bg-[#254d43]">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>

        {{-- Applications List --}}
        <div class="mt-6 space-y-4">
            @forelse ($rentalApplications as $app)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5 dark:border-slate-800 dark:bg-slate-800">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#e7ddca] pb-3 dark:border-slate-700">
                        <div>
                            <span class="font-mono text-xs font-bold text-[#587165] dark:text-slate-400">{{ $app->offer_reference }}</span>
                            <h4 class="font-bold text-sm text-[#1d3c34] dark:text-white">{{ $app->property?->title ?? 'Rental Property' }}</h4>
                            <p class="text-xs text-slate-500">Proposed Rent: <strong class="text-emerald-800 dark:text-emerald-400">{{ number_format($app->offered_price) }} ETB/mo</strong> &bull; Submitted {{ $app->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ match ($app->status) { 'accepted' => 'bg-emerald-100 text-emerald-800', 'rejected' => 'bg-rose-100 text-rose-800', 'withdrawn' => 'bg-slate-200 text-slate-700', default => 'bg-blue-100 text-blue-800' } }}">
                                {{ ucfirst($app->status) }}
                            </span>
                            @if (!in_array($app->status, ['accepted', 'completed', 'withdrawn'], true))
                                <button type="button" onclick="document.getElementById('edit-app-{{ $app->id }}').classList.toggle('hidden')" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('tenant.applications.withdraw', $app) }}" onsubmit="return confirm('Withdraw this rental application?')">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800">
                                        Withdraw
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @if ($app->message)
                        <p class="mt-3 text-xs text-slate-600 dark:text-slate-300 italic">"{{ $app->message }}"</p>
                    @endif

                    @if ($app->supporting_document_path)
                        <div class="mt-3 pt-2 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                            <span class="text-slate-500">📎 Attached Supporting Document:</span>
                            <a href="{{ asset('storage/' . $app->supporting_document_path) }}" target="_blank" class="font-bold text-[#1d3c34] hover:underline dark:text-emerald-400">Download Attachment &rarr;</a>
                        </div>
                    @endif

                    {{-- Edit Inline Form --}}
                    <div id="edit-app-{{ $app->id }}" class="hidden mt-4 rounded-xl border border-[#d9cab3] bg-white p-4 dark:bg-slate-900 dark:border-slate-700">
                        <h5 class="text-xs font-black text-[#1d3c34] mb-2 dark:text-white">Edit Application Terms</h5>
                        <form method="POST" action="{{ route('tenant.applications.update', $app) }}" class="grid gap-3 sm:grid-cols-2">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400">Proposed Rent (ETB)</label>
                                <input type="number" name="offered_price" value="{{ (int) $app->offered_price }}" required class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400">Proposed Deposit (ETB)</label>
                                <input type="number" name="deposit_proposed" value="{{ (int) $app->deposit_proposed }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400">Updated Notes</label>
                                <textarea name="message" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs dark:bg-slate-800 dark:border-slate-600 dark:text-white">{{ $app->message }}</textarea>
                            </div>
                            <div class="sm:col-span-2 flex justify-end">
                                <button type="submit" class="rounded-lg bg-[#1d3c34] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#254d43]">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-xs text-slate-400">No rental applications submitted yet. Browse rentals and submit your application above.</p>
            @endforelse
        </div>
    </div>

    {{-- 2. Active Leases & Digital Signing --}}
    <div id="leases" data-section="leases" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3 dark:border-slate-800 dark:text-white">My Lease Agreements</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            @forelse ($leases as $lease)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5 flex flex-col justify-between dark:border-slate-800 dark:bg-slate-800">
                    <div>
                        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-2 dark:border-slate-700">
                            <span class="font-mono text-xs font-bold text-[#1d3c34] dark:text-white">Lease #{{ $lease->lease_code }}</span>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $lease->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ucfirst($lease->status) }}
                            </span>
                        </div>
                        <h3 class="mt-3 font-bold text-base text-[#1d3c34] dark:text-white">{{ $lease->property?->title ?? 'Rented Property' }}</h3>
                        <p class="text-xs text-slate-500 mt-1">📍 {{ $lease->property?->city ?? 'Addis Ababa' }} &bull; Landlord: {{ $lease->landlord?->name ?? 'Property Owner' }}</p>

                        <div class="mt-4 grid grid-cols-2 gap-2 text-xs border-t border-[#e7ddca] pt-3 dark:border-slate-700">
                            <div>
                                <span class="text-slate-400">Monthly Rent:</span>
                                <p class="font-bold text-emerald-800 dark:text-emerald-400">{{ number_format($lease->monthly_rent) }} ETB</p>
                            </div>
                            <div>
                                <span class="text-slate-400">Lease Term:</span>
                                <p class="font-semibold dark:text-slate-200">{{ $lease->start_date?->format('M Y') }} – {{ $lease->end_date?->format('M Y') }}</p>
                            </div>
                        </div>

                        {{-- Digital Signing Status --}}
                        <div class="mt-3 pt-2 border-t border-slate-200 dark:border-slate-700 text-xs">
                            @if ($lease->signed_at)
                                <div class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-bold">
                                    <span>✍️ Digitally Signed by {{ $lease->tenant_signature ?? 'Tenant' }}</span>
                                    <span class="text-[10px] text-slate-400">({{ $lease->signed_at->format('M d, Y') }})</span>
                                </div>
                            @else
                                <div class="flex items-center justify-between">
                                    <span class="text-amber-700 dark:text-amber-400 font-bold">⚠️ Pending Tenant Digital Signature</span>
                                    <button type="button" onclick="document.getElementById('sign-lease-{{ $lease->id }}').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-3 py-1 text-xs font-bold text-white hover:bg-[#254d43]">
                                        Accept &amp; Sign
                                    </button>
                                </div>

                                {{-- Digital Sign Modal/Form --}}
                                <div id="sign-lease-{{ $lease->id }}" class="hidden mt-3 rounded-xl border border-[#d4af37] bg-white p-4 dark:bg-slate-900 dark:border-amber-700">
                                    <h5 class="text-xs font-black text-[#1d3c34] dark:text-white mb-2">Digital Lease Acceptance &amp; Signature</h5>
                                    <form method="POST" action="{{ route('tenant.leases.sign', $lease) }}" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400">Type Legal Full Name (Digital Signature) *</label>
                                            <input type="text" name="tenant_signature" required value="{{ auth()->user()->name }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <input type="checkbox" name="agree_terms" id="agree-{{ $lease->id }}" required value="1" class="mt-0.5 rounded border-slate-300">
                                            <label for="agree-{{ $lease->id }}" class="text-[10px] text-slate-600 dark:text-slate-300">
                                                I confirm I have read and agree to all terms, payment schedules, and house rules of this tenancy.
                                            </label>
                                        </div>
                                        <button type="submit" class="w-full rounded-lg bg-emerald-700 py-2 text-xs font-bold text-white hover:bg-emerald-800">
                                            Confirm &amp; Digitally Sign Lease
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if (Route::has('leases.agreement'))
                        <div class="mt-4 pt-2 border-t border-[#e7ddca] flex justify-end dark:border-slate-700">
                            <a href="{{ route('leases.agreement', $lease) }}" class="text-xs font-bold text-[#1d3c34] hover:underline dark:text-emerald-400">
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

    {{-- 3. Rent Payment Schedule & Receipts --}}
    <div id="rent-schedule" data-section="rent-schedule" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3 dark:border-slate-800 dark:text-white">Rent Schedule &amp; Payment History</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e7ddca] text-sm dark:divide-slate-800">
                <thead>
                    <tr class="bg-[#fbf9f5] dark:bg-slate-800 text-left text-xs font-black uppercase text-[#587165] dark:text-slate-300">
                        <th class="py-3 px-4">Period</th>
                        <th class="py-3 px-4">Due Date</th>
                        <th class="py-3 px-4">Amount Due</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Paid At</th>
                        <th class="py-3 px-4 text-right">Actions &amp; Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca] dark:divide-slate-800">
                    @forelse ($rentSchedules as $schedule)
                        <tr class="hover:bg-[#fcfaf7] dark:hover:bg-slate-800/40">
                            <td class="py-3 px-4 font-bold text-xs text-[#1d3c34] dark:text-white">{{ $schedule->period_label ?? ('Installment #' . $schedule->installment_number) }}</td>
                            <td class="py-3 px-4 text-xs font-semibold dark:text-slate-300">{{ $schedule->due_date?->format('M d, Y') }}</td>
                            <td class="py-3 px-4 font-bold text-emerald-800 dark:text-emerald-400">{{ number_format($schedule->amount_due) }} ETB</td>
                            <td class="py-3 px-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ match ($schedule->status) { 'paid' => 'bg-emerald-100 text-emerald-800', 'overdue' => 'bg-rose-100 text-rose-800', default => 'bg-amber-100 text-amber-800' } }}">
                                    {{ ucfirst($schedule->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-500">{{ $schedule->paid_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="py-3 px-4 text-right space-x-2">
                                @if ($schedule->status !== 'paid' && Route::has('rent-schedules.pay'))
                                    <button type="button" 
                                            onclick="openTenantRentModal('{{ route('rent-schedules.pay', $schedule) }}', '{{ addslashes($schedule->period_label ?? ('Installment #' . $schedule->installment_number)) }}', {{ (float) max(1, $schedule->amount_due - $schedule->amount_paid) }})" 
                                            class="rounded-full bg-[#1d3c34] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#254d43] shadow-xs cursor-pointer transition">
                                         Pay {{ number_format(max(1, $schedule->amount_due - $schedule->amount_paid)) }} ETB
                                    </button>
                                @else
                                    <a href="{{ route('tenant.rent.receipt', $schedule) }}" target="_blank" class="rounded-full border border-emerald-400 px-3 py-1 text-xs font-bold text-emerald-800 hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-emerald-950/40">
                                        🧾 Receipt Voucher
                                    </a>
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

    {{-- 4. Maintenance Service Tickets with Photo Upload & Updates --}}
    <div id="maintenance" data-section="maintenance" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#e7ddca] pb-4 dark:border-slate-800">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34] dark:text-white">Maintenance Requests</h2>
                <p class="text-xs text-slate-500">File plumbing, electrical, or structural repair tickets with photos, and close tickets once resolved.</p>
            </div>
            <button type="button" onclick="document.getElementById('maintenance-form').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition">
                + File Maintenance Request
            </button>
        </div>

        {{-- Maintenance Request Form --}}
        <div id="maintenance-form" class="hidden mt-4 rounded-2xl border border-[#d4af37] bg-[#fbf8f3] p-5 dark:border-amber-700 dark:bg-slate-800">
            <h3 class="text-sm font-black text-[#1d3c34] mb-3 dark:text-white">Submit Maintenance Request</h3>
            <form method="POST" action="{{ route('tenant.maintenance.store') }}" enctype="multipart/form-data" class="grid gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Rented Property *</label>
                    <select name="property_id" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                        @foreach ($leases as $lease)
                            @if ($lease->property)
                                <option value="{{ $lease->property->id }}">{{ $lease->property->title }} (Lease #{{ $lease->lease_code }})</option>
                            @endif
                        @endforeach
                        @if ($leases->isEmpty())
                            @foreach ($rentalProperties as $rp)
                                <option value="{{ $rp->id }}">{{ $rp->title }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Category *</label>
                    <select name="category" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                        <option value="plumbing">Plumbing &amp; Water Leak</option>
                        <option value="electrical">Electrical &amp; Power</option>
                        <option value="appliance">Appliance / Heating</option>
                        <option value="structural">Structural / Roof / Windows</option>
                        <option value="cleaning">Cleaning / Pest Control</option>
                        <option value="general" selected>General Maintenance</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Priority Level *</label>
                    <select name="priority" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                        <option value="low">Low</option>
                        <option value="normal" selected>Normal</option>
                        <option value="urgent">Urgent</option>
                        <option value="emergency">Emergency (Immediate Hazard)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Attach Photo of Issue (Max 10MB)</label>
                    <input type="file" name="photo" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Issue Summary / Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Master bathroom water pipe leak" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Detailed Description *</label>
                    <textarea name="description" rows="3" required placeholder="Describe the issue, location inside the property, and when it began..." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white"></textarea>
                </div>
                <div class="sm:col-span-2 flex justify-end">
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white hover:bg-[#254d43]">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>

        {{-- Maintenance Tickets List --}}
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($maintenanceTickets as $ticket)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5 flex flex-col justify-between dark:border-slate-800 dark:bg-slate-800">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-slate-600 dark:text-slate-400">{{ $ticket->ticket_number }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ match ($ticket->status) { 'closed', 'resolved' => 'bg-emerald-100 text-emerald-800', 'in_progress' => 'bg-blue-100 text-blue-800', default => 'bg-amber-100 text-amber-800' } }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </div>
                        <h4 class="mt-2 font-bold text-sm text-[#1d3c34] dark:text-white">{{ $ticket->title }}</h4>
                        <p class="text-xs text-slate-500 mt-1 whitespace-pre-line">{{ $ticket->description }}</p>

                        @if ($ticket->photo_path)
                            <div class="mt-3">
                                <a href="{{ asset('storage/' . $ticket->photo_path) }}" target="_blank" class="inline-block">
                                    <img src="{{ asset('storage/' . $ticket->photo_path) }}" alt="Issue photo" class="h-20 w-28 rounded-lg object-cover ring-1 ring-slate-300 hover:opacity-90">
                                </a>
                            </div>
                        @endif

                        <div class="mt-3 pt-2 border-t border-[#e7ddca] flex items-center justify-between text-[11px] text-slate-500 dark:border-slate-700">
                            <span>Priority: <strong class="text-slate-700 dark:text-slate-300">{{ ucfirst($ticket->priority) }}</strong></span>
                            <span>{{ $ticket->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    {{-- Actions: Update or Close --}}
                    @if ($ticket->status !== 'closed' && $ticket->status !== 'resolved')
                        <div class="mt-4 pt-3 border-t border-[#e7ddca] flex items-center justify-between dark:border-slate-700">
                            <button type="button" onclick="document.getElementById('update-ticket-{{ $ticket->id }}').classList.toggle('hidden')" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                                + Add Note / Photo
                            </button>
                            <form method="POST" action="{{ route('tenant.maintenance.close', $ticket) }}" onsubmit="return confirm('Confirm resolution and close this ticket?')">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="text-xs font-bold text-emerald-700 hover:text-emerald-900">
                                    Mark Resolved &amp; Close
                                </button>
                            </form>
                        </div>

                        {{-- Update Ticket Form --}}
                        <div id="update-ticket-{{ $ticket->id }}" class="hidden mt-3 rounded-xl border border-[#d9cab3] bg-white p-3 dark:bg-slate-900 dark:border-slate-700">
                            <form method="POST" action="{{ route('tenant.maintenance.update', $ticket) }}" enctype="multipart/form-data" class="space-y-2">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400">Add Update Note *</label>
                                    <textarea name="additional_notes" rows="2" required placeholder="e.g. Plumber inspected today, parts pending..." class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs dark:bg-slate-800 dark:border-slate-600 dark:text-white"></textarea>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400">Additional Photo (Optional)</label>
                                    <input type="file" name="photo" class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                                </div>
                                <button type="submit" class="w-full rounded-lg bg-[#1d3c34] py-1 text-xs font-bold text-white hover:bg-[#254d43]">
                                    Post Update
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-xs text-slate-400 py-4 text-center">No maintenance tickets filed.</p>
            @endforelse
        </div>
    </div>

    {{-- 5. Landlord & Property Manager Messages --}}
    <div id="messages" data-section="messages" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3 dark:border-slate-800 dark:text-white">Landlord &amp; Property Manager Direct Messages</h2>
        <div class="mt-4 space-y-4">
            @forelse ($inquiries as $inquiry)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5 dark:border-slate-800 dark:bg-slate-800">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[#e7ddca] pb-3 dark:border-slate-700">
                        <div>
                            <span class="font-mono text-xs font-bold text-[#587165] dark:text-slate-400">#MSG-{{ $inquiry->id }}</span>
                            <h4 class="font-bold text-sm text-[#1d3c34] dark:text-white">{{ $inquiry->property?->title ?? 'Tenancy Message' }}</h4>
                            <p class="text-xs text-slate-500">Contact: {{ $inquiry->agent?->name ?? 'Landlord / Consultant' }} &bull; {{ $inquiry->created_at->format('M d, Y') }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ match ($inquiry->status) { 'resolved' => 'bg-emerald-100 text-emerald-800', default => 'bg-blue-100 text-blue-800' } }}">
                            {{ ucfirst($inquiry->status) }}
                        </span>
                    </div>

                    <div class="mt-3 text-xs text-slate-700 whitespace-pre-line bg-white rounded-xl p-3 border border-slate-200 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-200">
                        {{ $inquiry->message }}
                    </div>

                    <form method="POST" action="{{ route('tenant.messages.reply', $inquiry) }}" class="mt-3 flex gap-2">
                        @csrf
                        <input type="text" name="message" required placeholder="Type reply to landlord..." class="flex-1 rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                        <button type="submit" class="rounded-xl bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43]">
                            Send Reply
                        </button>
                    </form>
                </div>
            @empty
                <p class="py-6 text-center text-xs text-slate-400">No active messaging threads on file.</p>
            @endforelse
        </div>
    </div>

    {{-- 6. Tenancy Documents Vault --}}
    <div id="documents" data-section="documents" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#e7ddca] pb-4 dark:border-slate-800">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34] dark:text-white">Tenancy Document Vault</h2>
                <p class="text-xs text-slate-500">Store employment verifications, national IDs, lease guarantors, and payment slips securely.</p>
            </div>
            <button type="button" onclick="document.getElementById('upload-tenant-doc').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition">
                + Upload Document
            </button>
        </div>

        {{-- Upload Document Form --}}
        <div id="upload-tenant-doc" class="hidden mt-4 rounded-2xl border border-[#d4af37] bg-[#fbf8f3] p-5 dark:border-amber-700 dark:bg-slate-800">
            <h3 class="text-sm font-black text-[#1d3c34] mb-3 dark:text-white">Upload Tenancy Document</h3>
            <form method="POST" action="{{ route('tenant.documents.upload') }}" enctype="multipart/form-data" class="grid gap-4 sm:grid-cols-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Document Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Employment Verification Letter" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Document Type *</label>
                    <select name="document_type" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                        <option value="employment_letter">Employment / Income Letter</option>
                        <option value="national_id">National ID / Passport</option>
                        <option value="bank_statement">Bank Statement</option>
                        <option value="lease_guarantor">Guarantor Guarantee</option>
                        <option value="deposit_receipt">Deposit Slip / Receipt</option>
                        <option value="other">Other Document</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">File (PDF, PNG, JPG, DOCX max 10MB) *</label>
                    <input type="file" name="document_file" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                </div>
                <div class="sm:col-span-3 flex justify-end">
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white hover:bg-[#254d43]">
                        Upload to Private Vault
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e7ddca] text-xs dark:divide-slate-800">
                <thead>
                    <tr class="bg-[#fbf9f5] dark:bg-slate-800 text-left font-black uppercase text-[#587165] dark:text-slate-300">
                        <th class="py-3 px-4">Title</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Access Level</th>
                        <th class="py-3 px-4">Size</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca] dark:divide-slate-800">
                    @forelse ($documents as $doc)
                        <tr class="hover:bg-[#fcfaf7] dark:hover:bg-slate-800/40">
                            <td class="py-3 px-4 font-bold text-[#1d3c34] dark:text-white">{{ $doc->title }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-600 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</td>
                            <td class="py-3 px-4">
                                <span class="rounded-full bg-slate-100 text-slate-700 px-2 py-0.5 text-[10px] font-bold dark:bg-slate-800 dark:text-slate-300">
                                    🔒 {{ ucfirst($doc->access_level) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $doc->file_size ? number_format($doc->file_size / 1024, 1) . ' KB' : '—' }}</td>
                            <td class="py-3 px-4 text-right space-x-2">
                                @if ($doc->file_path)
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-xs font-bold text-[#1d3c34] hover:underline dark:text-emerald-400">Download</a>
                                @endif
                                <form method="POST" action="{{ route('tenant.documents.destroy', $doc) }}" class="inline" onsubmit="return confirm('Delete this document?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-xs text-slate-400">No documents in your tenancy vault.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 7. Find Rentals Marketplace Feed --}}
    <div id="rentals" data-section="rentals" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3 dark:border-slate-800">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34] dark:text-white">Find Available Rentals</h2>
                <p class="text-xs text-slate-500">Explore prime apartments, villas, and commercial rentals across Addis Ababa.</p>
            </div>
            <a href="{{ route('properties.index', ['type' => 'rent']) }}" class="text-xs font-bold text-[#1d3c34] hover:underline dark:text-emerald-400">
                View All Rentals &rarr;
            </a>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($rentalProperties as $prop)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] overflow-hidden flex flex-col dark:border-slate-800 dark:bg-slate-800">
                    <div class="h-36 bg-slate-200 relative dark:bg-slate-700">
                        @if ($prop->cover_image_url)
                            <img src="{{ $prop->cover_image_url }}" alt="{{ $prop->title }}" class="h-full w-full object-cover">
                        @else
                            <div class="h-full w-full flex items-center justify-center text-slate-400 text-xs">No image</div>
                        @endif
                        <span class="absolute top-2 right-2 rounded-full bg-[#1d3c34]/90 px-2.5 py-0.5 text-[10px] font-bold text-white">
                            {{ number_format($prop->price) }} ETB / mo
                        </span>
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <h4 class="font-bold text-sm text-[#1d3c34] line-clamp-1 dark:text-white">{{ $prop->title }}</h4>
                            <p class="text-xs text-slate-500 mt-1">📍 {{ $prop->city }}, {{ $prop->subcity ?? '' }}</p>
                        </div>
                        <div class="mt-4 pt-2 border-t border-[#e7ddca] flex items-center justify-between dark:border-slate-700">
                            <a href="{{ route('properties.show', $prop->slug) }}" class="text-xs font-bold text-[#1d3c34] hover:underline dark:text-emerald-400">View Listing</a>
                            <button type="button" onclick="applyForRental({{ $prop->id }})" class="rounded-full bg-[#1d3c34] px-3 py-1 text-[11px] font-bold text-white hover:bg-[#254d43]">
                                Apply Now
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <p class="col-span-3 text-xs text-slate-400 py-4 text-center">No rental properties available.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Rent Installment Payment Modal for Tenant --}}
<div id="tenant-rent-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 p-4 backdrop-blur-xs flex items-center justify-center">
    <div class="relative w-full max-w-md rounded-3xl border border-[#d9cab3] bg-[#fdfaf5] p-6 shadow-2xl dark:bg-slate-900 dark:border-slate-800">
        <div class="flex items-center justify-between border-b border-[#f0e8d8] pb-3 dark:border-slate-800">
            <div>
                <span class="rounded bg-[#102b25] px-2 py-0.5 text-[9px] font-black uppercase text-[#d4af37]">Ethiopian Digital Payment</span>
                <h3 class="mt-1 text-lg font-black text-[#1d3c34] dark:text-white">Pay Rent Installment</h3>
            </div>
            <button type="button" onclick="closeTenantRentModal()" class="cursor-pointer text-2xl font-bold text-slate-400 hover:text-slate-800">&times;</button>
        </div>

        <form id="tenant-rent-payment-form" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            <div id="tenant-rent-period-display" class="rounded-xl border border-[#e7ddca] bg-[#f9f4ed] p-3 text-xs font-bold text-[#1d3c34] dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200"></div>

            <div>
                <label class="mb-1 block text-xs font-bold text-slate-700 dark:text-slate-300">Payment Amount (ETB) *</label>
                <input type="number" step="0.01" min="1" name="amount_paid" id="tenant-rent-amount" required class="w-full rounded-xl border border-[#cdbb97] bg-white px-3 py-2 font-mono text-sm font-bold text-[#1d3c34] focus:border-[#1d3c34] focus:outline-none dark:bg-slate-800 dark:border-slate-600 dark:text-white">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold text-slate-700 dark:text-slate-300">Payment Method (የክፍያ አማራጭ) *</label>
                <select name="payment_method" id="tenant-rent-method" required class="w-full rounded-xl border border-[#cdbb97] bg-white px-3 py-2 text-xs font-bold focus:border-[#1d3c34] focus:outline-none dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                    <option value="telebirr" selected>Telebirr (ቴሌብር) — Instant Mobile Wallet</option>
                    <option value="cbe_birr">CBE Birr (ሲቢኢ ብር) — Commercial Bank</option>
                    <option value="bank_transfer">Bank Transfer (የባንክ ሒሳብ ማስተላለፊያ)</option>
                    <option value="cash">Cash at Office (ጥሬ ገንዘብ)</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold text-slate-700 dark:text-slate-300">Bank / Wallet Reference (Optional)</label>
                <input type="text" name="bank_transaction_reference" placeholder="e.g. FT26... or Telebirr Trans ID" class="w-full rounded-xl border border-[#cdbb97] bg-white px-3 py-2 font-mono text-xs focus:border-[#1d3c34] focus:outline-none dark:bg-slate-800 dark:border-slate-600 dark:text-white">
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-[#f0e8d8] pt-4 dark:border-slate-800">
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

    function applyForRental(propId) {
        if (window.activateSection) {
            window.activateSection('applications', true);
        }
        const form = document.getElementById('new-application-form');
        if (form) {
            form.classList.remove('hidden');
        }
        const select = document.getElementById('application-property-id');
        if (select) {
            select.value = propId;
        }
    }
</script>
@endsection
