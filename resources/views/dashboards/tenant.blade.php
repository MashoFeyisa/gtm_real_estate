@extends('dashboards.layout')

@section('title', 'Tenant Dashboard')
@section('portal_name', 'Tenant Workspace')

@section('sidebar-nav')
    @if (auth()->user()->hasAnyPermission(['applications.view', 'applications.create']))
        <x-sidebar-item href="#applications" icon="📝" label="Applications" :count="$rentalApplications->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['leases.view', 'leases.manage', 'agreements.view', 'leases.sign']))
        <x-sidebar-item href="#leases" icon="🔑" label="My Leases" :count="$leases->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['rent.view', 'rent.pay', 'rent.track']))
        <x-sidebar-item href="#rent-schedule" icon="💰" label="Rent Schedule" :count="$rentSchedules->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['maintenance.view', 'maintenance.create']))
        <x-sidebar-item href="#maintenance" icon="🛠️" label="Maintenance" :count="$maintenanceTickets->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['landlords.communicate', 'messages.view', 'inquiries.view']))
        <x-sidebar-item href="#messages" icon="💬" label="Landlord Messages" :count="$inquiries->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['documents.view', 'documents.create']))
        <x-sidebar-item href="#documents" icon="📁" label="Tenancy Vault" :count="$documents->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['rentals.search', 'properties.view']))
        <x-sidebar-item href="#rentals" icon="🏠" label="Find Rentals" :count="$rentalProperties->count()" />
    @endif
@endsection

@section('content')
<div class="space-y-8">
    {{-- Session Feedback Messages --}}
    @if (session('success'))
        <div class="token-alert token-alert-success text-sm font-bold flex items-center justify-between">
            <span>✅ {{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="token-btn token-btn-ghost !p-1">&times;</button>
        </div>
    @endif
    @if (session('error'))
        <div class="token-alert token-alert-danger text-sm font-bold flex items-center justify-between">
            <span>⚠️ {{ session('error') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="token-btn token-btn-ghost !p-1">&times;</button>
        </div>
    @endif

    {{-- Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- 1. Rental Applications --}}
    <div id="applications" data-section="applications" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[var(--token-border)] pb-4">
            <div>
                <h2 class="text-xl font-black">Rental Applications</h2>
                <p class="text-xs token-muted">Apply for residential and commercial rentals, attach proof of income, and track approval status.</p>
            </div>
            <button type="button" onclick="document.getElementById('new-application-form').classList.toggle('hidden')" class="token-btn token-btn-primary">
                + New Rental Application
            </button>
        </div>

        {{-- New Application Form --}}
        <div id="new-application-form" class="hidden mt-4 token-card !p-5 bg-[var(--token-surface-subtle)]">
            <h3 class="text-sm font-black mb-3">Submit Rental Tenancy Application</h3>
            <form method="POST" action="{{ route('tenant.applications.store') }}" enctype="multipart/form-data" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                @csrf
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold token-muted">Target Rental Property *</label>
                    <select name="property_id" id="application-property-id" required class="mt-1 token-field">
                        <option value="">— Select a rental property —</option>
                        @foreach ($rentalProperties as $rp)
                            <option value="{{ $rp->id }}">{{ $rp->title }} ({{ $rp->city }}, {{ $rp->subcity ?? '' }} &bull; {{ number_format($rp->price) }} ETB/mo)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold token-muted">Proposed Monthly Rent (ETB) *</label>
                    <input type="number" name="offered_price" required placeholder="e.g. 35000" class="mt-1 token-field">
                </div>
                <div>
                    <label class="block text-xs font-bold token-muted">Proposed Security Deposit (ETB)</label>
                    <input type="number" name="deposit_proposed" placeholder="e.g. 70000 (2 months)" class="mt-1 token-field">
                </div>
                <div>
                    <label class="block text-xs font-bold token-muted">Desired Move-in Date</label>
                    <input type="date" name="valid_until" min="{{ date('Y-m-d') }}" class="mt-1 token-field">
                </div>
                <div>
                    <label class="block text-xs font-bold token-muted">Supporting Document (PDF, PNG, JPG)</label>
                    <input type="file" name="supporting_document" class="mt-1 token-field">
                </div>
                <div class="sm:col-span-2 md:col-span-3">
                    <label class="block text-xs font-bold token-muted">Applicant Notes / Employment Background</label>
                    <textarea name="message" rows="2" placeholder="Briefly state your occupation, number of occupants, and lease term preference..." class="mt-1 token-field"></textarea>
                </div>
                <div class="sm:col-span-2 md:col-span-3 flex justify-end">
                    <button type="submit" class="token-btn token-btn-primary">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>

        {{-- Applications List --}}
        <div class="mt-6 space-y-4">
            @forelse ($rentalApplications as $app)
                <div class="token-card !p-5 bg-[var(--token-surface-subtle)]">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[var(--token-border)] pb-3">
                        <div>
                            <span class="font-mono text-xs font-bold token-muted">{{ $app->offer_reference }}</span>
                            <h4 class="font-bold text-sm">{{ $app->property?->title ?? 'Rental Property' }}</h4>
                            <p class="text-xs token-muted">Proposed Rent: <strong class="text-[var(--token-success)]">{{ number_format($app->offered_price) }} ETB/mo</strong> &bull; Submitted {{ $app->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="token-badge {{ match ($app->status) { 'accepted' => 'token-badge-success', 'rejected' => 'token-badge-danger', 'withdrawn' => 'token-badge-neutral', default => 'token-badge-info' } }}">
                                {{ ucfirst($app->status) }}
                            </span>
                            @if (!in_array($app->status, ['accepted', 'completed', 'withdrawn'], true))
                                <button type="button" onclick="document.getElementById('edit-app-{{ $app->id }}').classList.toggle('hidden')" class="text-xs font-bold text-[var(--token-primary)] hover:underline">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('tenant.applications.withdraw', $app) }}" onsubmit="return confirm('Withdraw this rental application?')">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="text-xs font-bold text-[var(--token-danger)] hover:underline">
                                        Withdraw
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @if ($app->message)
                        <p class="mt-3 text-xs token-muted italic">"{{ $app->message }}"</p>
                    @endif

                    @if ($app->supporting_document_path)
                        <div class="mt-3 pt-2 border-t border-[var(--token-border-subtle)] flex items-center justify-between text-xs">
                            <span class="token-muted">📎 Attached Supporting Document:</span>
                            <a href="{{ asset('storage/' . $app->supporting_document_path) }}" target="_blank" class="font-bold text-[var(--token-primary)] hover:underline">Download Attachment &rarr;</a>
                        </div>
                    @endif

                    {{-- Edit Inline Form --}}
                    <div id="edit-app-{{ $app->id }}" class="hidden mt-4 token-card !p-4">
                        <h5 class="text-xs font-black mb-2">Edit Application Terms</h5>
                        <form method="POST" action="{{ route('tenant.applications.update', $app) }}" class="grid gap-3 sm:grid-cols-2">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-[10px] font-bold token-muted">Proposed Rent (ETB)</label>
                                <input type="number" name="offered_price" value="{{ (int) $app->offered_price }}" required class="token-field !py-1.5 !text-xs">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold token-muted">Proposed Deposit (ETB)</label>
                                <input type="number" name="deposit_proposed" value="{{ (int) $app->deposit_proposed }}" class="token-field !py-1.5 !text-xs">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold token-muted">Updated Notes</label>
                                <textarea name="message" rows="2" class="token-field !py-1.5 !text-xs">{{ $app->message }}</textarea>
                            </div>
                            <div class="sm:col-span-2 flex justify-end">
                                <button type="submit" class="token-btn token-btn-primary !py-1.5 !px-4 !text-xs">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-xs token-muted">No rental applications submitted yet. Browse rentals and submit your application above.</p>
            @endforelse
        </div>
    </div>

    {{-- 2. Active Leases & Digital Signing --}}
    <div id="leases" data-section="leases" class="section-panel hidden scroll-mt-6 token-card">
        <h2 class="text-xl font-black border-b border-[var(--token-border)] pb-3">My Lease Agreements</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            @forelse ($leases as $lease)
                <div class="token-card !p-5 flex flex-col justify-between bg-[var(--token-surface-subtle)]">
                    <div>
                        <div class="flex items-center justify-between border-b border-[var(--token-border)] pb-2">
                            <span class="font-mono text-xs font-bold">Lease ref: {{ $lease->lease_code }}</span>
                            <span class="token-badge {{ $lease->status === 'active' ? 'token-badge-success' : 'token-badge-warning' }}">
                                {{ ucfirst($lease->status) }}
                            </span>
                        </div>
                        <h3 class="mt-3 font-bold text-base">{{ $lease->property?->title ?? 'Rented Property' }}</h3>
                        <p class="text-xs token-muted mt-1">📍 {{ $lease->property?->city ?? 'Addis Ababa' }} &bull; Landlord: {{ $lease->landlord?->name ?? 'Property Owner' }}</p>

                        <div class="mt-4 grid grid-cols-2 gap-2 text-xs border-t border-[var(--token-border)] pt-3">
                            <div>
                                <span class="token-muted">Monthly Rent:</span>
                                <p class="font-bold text-[var(--token-success)]">{{ number_format($lease->monthly_rent) }} ETB</p>
                            </div>
                            <div>
                                <span class="token-muted">Lease Term:</span>
                                <p class="font-semibold">{{ $lease->start_date?->format('M Y') }} – {{ $lease->end_date?->format('M Y') }}</p>
                            </div>
                        </div>

                        {{-- Digital Signing Status --}}
                        <div class="mt-3 pt-2 border-t border-[var(--token-border-subtle)] text-xs">
                            @if ($lease->signed_at)
                                <div class="flex items-center gap-1.5 text-[var(--token-success)] font-bold">
                                    <span>✍️ Digitally Signed by {{ $lease->tenant_signature ?? 'Tenant' }}</span>
                                    <span class="text-[10px] token-muted">({{ $lease->signed_at->format('M d, Y') }})</span>
                                </div>
                            @else
                                <div class="flex items-center justify-between">
                                    <span class="text-[var(--token-warning)] font-bold">⚠️ Pending Tenant Digital Signature</span>
                                    <button type="button" onclick="document.getElementById('sign-lease-{{ $lease->id }}').classList.toggle('hidden')" class="token-btn token-btn-primary !py-1 !px-3 !text-xs">
                                        Accept &amp; Sign
                                    </button>
                                </div>

                                {{-- Digital Sign Modal/Form --}}
                                <div id="sign-lease-{{ $lease->id }}" class="hidden mt-3 token-card !p-4">
                                    <h5 class="text-xs font-black mb-2">Digital Lease Acceptance &amp; Signature</h5>
                                    <form method="POST" action="{{ route('tenant.leases.sign', $lease) }}" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label class="block text-[10px] font-bold token-muted">Type Legal Full Name (Digital Signature) *</label>
                                            <input type="text" name="tenant_signature" required value="{{ auth()->user()->name }}" class="token-field !py-1.5 !text-xs">
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <input type="checkbox" name="agree_terms" id="agree-{{ $lease->id }}" required value="1" class="mt-0.5 rounded border-[var(--token-border)]">
                                            <label for="agree-{{ $lease->id }}" class="text-[10px] token-muted">
                                                I confirm I have read and agree to all terms, payment schedules, and house rules of this tenancy.
                                            </label>
                                        </div>
                                        <button type="submit" class="w-full token-btn token-btn-primary">
                                            Confirm &amp; Digitally Sign Lease
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if (Route::has('leases.agreement'))
                        <div class="mt-4 pt-2 border-t border-[var(--token-border)] flex justify-end">
                            <a href="{{ route('leases.agreement', $lease) }}" class="text-xs font-bold text-[var(--token-primary)] hover:underline">
                                📄 Download Official Agreement (PDF) &rarr;
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <p class="col-span-2 text-xs token-muted py-4 text-center">No active leases on file.</p>
            @endforelse
        </div>
    </div>

    {{-- 3. Rent Payment Schedule & Receipts --}}
    <div id="rent-schedule" data-section="rent-schedule" class="section-panel hidden scroll-mt-6 token-card">
        <h2 class="text-xl font-black border-b border-[var(--token-border)] pb-3">Rent Schedule &amp; Payment History</h2>
        <div class="mt-4 token-table-wrap">
            <table class="token-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Due Date</th>
                        <th>Amount Due</th>
                        <th>Status</th>
                        <th>Paid At</th>
                        <th class="text-right">Actions &amp; Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rentSchedules as $schedule)
                        <tr>
                            <td class="font-bold text-xs">{{ $schedule->period_label ?? ('Installment ref: ' . $schedule->installment_number) }}</td>
                            <td class="text-xs font-semibold">{{ $schedule->due_date?->format('M d, Y') }}</td>
                            <td class="font-bold text-[var(--token-success)]">{{ number_format($schedule->amount_due) }} ETB</td>
                            <td>
                                <span class="token-badge {{ match ($schedule->status) { 'paid' => 'token-badge-success', 'overdue' => 'token-badge-danger', default => 'token-badge-warning' } }}">
                                    {{ ucfirst($schedule->status) }}
                                </span>
                            </td>
                            <td class="text-xs token-muted">{{ $schedule->paid_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="text-right space-x-2">
                                @if ($schedule->status !== 'paid' && Route::has('rent-schedules.pay'))
                                    <button type="button" 
                                            onclick="openTenantRentModal('{{ route('rent-schedules.pay', $schedule) }}', '{{ addslashes($schedule->period_label ?? ('Installment ref: ' . $schedule->installment_number)) }}', {{ (float) max(1, $schedule->amount_due - $schedule->amount_paid) }})" 
                                            class="token-btn token-btn-primary !py-1.5 !px-4 !text-xs cursor-pointer">
                                         Pay {{ number_format(max(1, $schedule->amount_due - $schedule->amount_paid)) }} ETB
                                    </button>
                                @else
                                    <a href="{{ route('tenant.rent.receipt', $schedule) }}" target="_blank" class="token-btn token-btn-secondary !py-1 !px-3 !text-xs">
                                        🧾 Receipt Voucher
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-xs token-muted">No rent payment schedule recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 4. Maintenance Service Tickets with Photo Upload & Updates --}}
    <div id="maintenance" data-section="maintenance" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[var(--token-border)] pb-4">
            <div>
                <h2 class="text-xl font-black">Maintenance Requests</h2>
                <p class="text-xs token-muted">File plumbing, electrical, or structural repair tickets with photos, and close tickets once resolved.</p>
            </div>
            <button type="button" onclick="document.getElementById('maintenance-form').classList.toggle('hidden')" class="token-btn token-btn-primary">
                + File Maintenance Request
            </button>
        </div>

        {{-- Maintenance Request Form --}}
        <div id="maintenance-form" class="hidden mt-4 token-card !p-5 bg-[var(--token-surface-subtle)]">
            <h3 class="text-sm font-black mb-3">Submit Maintenance Request</h3>
            <form method="POST" action="{{ route('tenant.maintenance.store') }}" enctype="multipart/form-data" class="grid gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="block text-xs font-bold token-muted">Rented Property *</label>
                    <select name="property_id" required class="mt-1 token-field">
                        @foreach ($leases as $lease)
                            @if ($lease->property)
                                <option value="{{ $lease->property->id }}">{{ $lease->property->title }} (Lease ref: {{ $lease->lease_code }})</option>
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
                    <label class="block text-xs font-bold token-muted">Category *</label>
                    <select name="category" required class="mt-1 token-field">
                        <option value="plumbing">Plumbing &amp; Water Leak</option>
                        <option value="electrical">Electrical &amp; Power</option>
                        <option value="appliance">Appliance / Heating</option>
                        <option value="structural">Structural / Roof / Windows</option>
                        <option value="cleaning">Cleaning / Pest Control</option>
                        <option value="general" selected>General Maintenance</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold token-muted">Priority Level *</label>
                    <select name="priority" required class="mt-1 token-field">
                        <option value="low">Low</option>
                        <option value="normal" selected>Normal</option>
                        <option value="urgent">Urgent</option>
                        <option value="emergency">Emergency (Immediate Hazard)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold token-muted">Attach Photo of Issue (Max 10MB)</label>
                    <input type="file" name="photo" class="mt-1 token-field">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold token-muted">Issue Summary / Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Master bathroom water pipe leak" class="mt-1 token-field">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold token-muted">Detailed Description *</label>
                    <textarea name="description" rows="3" required placeholder="Describe the issue, location inside the property, and when it began..." class="mt-1 token-field"></textarea>
                </div>
                <div class="sm:col-span-2 flex justify-end">
                    <button type="submit" class="token-btn token-btn-primary">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>

        {{-- Maintenance Tickets List --}}
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($maintenanceTickets as $ticket)
                <div class="token-card !p-5 flex flex-col justify-between bg-[var(--token-surface-subtle)]">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold token-muted">{{ $ticket->ticket_number }}</span>
                            <span class="token-badge {{ match ($ticket->status) { 'closed', 'resolved' => 'token-badge-success', 'in_progress' => 'token-badge-info', default => 'token-badge-warning' } }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </div>
                        <h4 class="mt-2 font-bold text-sm">{{ $ticket->title }}</h4>
                        <p class="text-xs token-muted mt-1 whitespace-pre-line">{{ $ticket->description }}</p>

                        @if ($ticket->photo_path)
                            <div class="mt-3">
                                <a href="{{ asset('storage/' . $ticket->photo_path) }}" target="_blank" class="inline-block">
                                    <img src="{{ asset('storage/' . $ticket->photo_path) }}" alt="Issue photo" class="h-20 w-28 rounded-lg object-cover ring-1 ring-[var(--token-border)] hover:opacity-90">
                                </a>
                            </div>
                        @endif

                        <div class="mt-3 pt-2 border-t border-[var(--token-border)] flex items-center justify-between text-[11px] token-muted">
                            <span>Priority: <strong class="text-[var(--token-text)]">{{ ucfirst($ticket->priority) }}</strong></span>
                            <span>{{ $ticket->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    {{-- Actions: Update or Close --}}
                    @if ($ticket->status !== 'closed' && $ticket->status !== 'resolved')
                        <div class="mt-4 pt-3 border-t border-[var(--token-border)] flex items-center justify-between">
                            <button type="button" onclick="document.getElementById('update-ticket-{{ $ticket->id }}').classList.toggle('hidden')" class="text-xs font-bold text-[var(--token-primary)] hover:underline">
                                + Add Note / Photo
                            </button>
                            <form method="POST" action="{{ route('tenant.maintenance.close', $ticket) }}" onsubmit="return confirm('Confirm resolution and close this ticket?')">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="text-xs font-bold text-[var(--token-success)] hover:underline">
                                    Mark Resolved &amp; Close
                                </button>
                            </form>
                        </div>

                        {{-- Update Ticket Form --}}
                        <div id="update-ticket-{{ $ticket->id }}" class="hidden mt-3 token-card !p-3">
                            <form method="POST" action="{{ route('tenant.maintenance.update', $ticket) }}" enctype="multipart/form-data" class="space-y-2">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-[10px] font-bold token-muted">Add Update Note *</label>
                                    <textarea name="additional_notes" rows="2" required placeholder="e.g. Plumber inspected today, parts pending..." class="token-field !py-1 !text-xs"></textarea>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold token-muted">Additional Photo (Optional)</label>
                                    <input type="file" name="photo" class="token-field !py-1 !text-xs">
                                </div>
                                <button type="submit" class="w-full token-btn token-btn-primary !py-1 !text-xs">
                                    Post Update
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-xs token-muted py-4 text-center">No maintenance tickets filed.</p>
            @endforelse
        </div>
    </div>

    {{-- 5. Landlord & Property Manager Messages --}}
    <div id="messages" data-section="messages" class="section-panel hidden scroll-mt-6 token-card">
        <h2 class="text-xl font-black border-b border-[var(--token-border)] pb-3">Landlord &amp; Property Manager Direct Messages</h2>
        <div class="mt-4 space-y-4">
            @forelse ($inquiries as $inquiry)
                <div class="token-card !p-5 bg-[var(--token-surface-subtle)]">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[var(--token-border)] pb-3">
                        <div>
                            <span class="font-mono text-xs font-bold token-muted">MSG-ref: {{ $inquiry->id }}</span>
                            <h4 class="font-bold text-sm">{{ $inquiry->property?->title ?? 'Tenancy Message' }}</h4>
                            <p class="text-xs token-muted">Contact: {{ $inquiry->agent?->name ?? 'Landlord / Consultant' }} &bull; {{ $inquiry->created_at->format('M d, Y') }}</p>
                        </div>
                        <span class="token-badge {{ match ($inquiry->status) { 'resolved' => 'token-badge-success', default => 'token-badge-info' } }}">
                            {{ ucfirst($inquiry->status) }}
                        </span>
                    </div>

                    <div class="mt-3 text-xs token-card !p-3 whitespace-pre-line">
                        {{ $inquiry->message }}
                    </div>

                    <form method="POST" action="{{ route('tenant.messages.reply', $inquiry) }}" class="mt-3 flex gap-2">
                        @csrf
                        <input type="text" name="message" required placeholder="Type reply to landlord..." class="flex-1 token-field !py-2 !text-xs">
                        <button type="submit" class="token-btn token-btn-primary !py-2 !px-4 !text-xs">
                            Send Reply
                        </button>
                    </form>
                </div>
            @empty
                <p class="py-6 text-center text-xs token-muted">No active messaging threads on file.</p>
            @endforelse
        </div>
    </div>

    {{-- 6. Tenancy Documents Vault --}}
    <div id="documents" data-section="documents" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[var(--token-border)] pb-4">
            <div>
                <h2 class="text-xl font-black">Tenancy Document Vault</h2>
                <p class="text-xs token-muted">Store employment verifications, national IDs, lease guarantors, and payment slips securely.</p>
            </div>
            <button type="button" onclick="document.getElementById('upload-tenant-doc').classList.toggle('hidden')" class="token-btn token-btn-primary">
                + Upload Document
            </button>
        </div>

        {{-- Upload Document Form --}}
        <div id="upload-tenant-doc" class="hidden mt-4 token-card !p-5 bg-[var(--token-surface-subtle)]">
            <h3 class="text-sm font-black mb-3">Upload Tenancy Document</h3>
            <form method="POST" action="{{ route('tenant.documents.upload') }}" enctype="multipart/form-data" class="grid gap-4 sm:grid-cols-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold token-muted">Document Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Employment Verification Letter" class="mt-1 token-field">
                </div>
                <div>
                    <label class="block text-xs font-bold token-muted">Document Type *</label>
                    <select name="document_type" required class="mt-1 token-field">
                        <option value="employment_letter">Employment / Income Letter</option>
                        <option value="national_id">National ID / Passport</option>
                        <option value="bank_statement">Bank Statement</option>
                        <option value="lease_guarantor">Guarantor Guarantee</option>
                        <option value="deposit_receipt">Deposit Slip / Receipt</option>
                        <option value="other">Other Document</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold token-muted">File (PDF, PNG, JPG, DOCX max 10MB) *</label>
                    <input type="file" name="document_file" required class="mt-1 token-field">
                </div>
                <div class="sm:col-span-3 flex justify-end">
                    <button type="submit" class="token-btn token-btn-primary">
                        Upload to Private Vault
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 token-table-wrap">
            <table class="token-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Access Level</th>
                        <th>Size</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $doc)
                        <tr>
                            <td class="font-bold">{{ $doc->title }}</td>
                            <td class="token-muted">{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</td>
                            <td>
                                <span class="token-badge token-badge-neutral">
                                    🔒 {{ ucfirst($doc->access_level) }}
                                </span>
                            </td>
                            <td class="token-muted">{{ $doc->file_size ? number_format($doc->file_size / 1024, 1) . ' KB' : '—' }}</td>
                            <td class="text-right space-x-2">
                                @if ($doc->file_path)
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-xs font-bold text-[var(--token-primary)] hover:underline">Download</a>
                                @endif
                                <form method="POST" action="{{ route('tenant.documents.destroy', $doc) }}" class="inline" onsubmit="return confirm('Delete this document?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-[var(--token-danger)] hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-xs token-muted">No documents in your tenancy vault.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 7. Find Rentals Marketplace Feed --}}
    <div id="rentals" data-section="rentals" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex items-center justify-between border-b border-[var(--token-border)] pb-3">
            <div>
                <h2 class="text-xl font-black">Find Available Rentals</h2>
                <p class="text-xs token-muted">Explore prime apartments, villas, and commercial rentals across Addis Ababa.</p>
            </div>
            <a href="{{ route('properties.index', ['type' => 'rent']) }}" class="text-xs font-bold text-[var(--token-primary)] hover:underline">
                View All Rentals &rarr;
            </a>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($rentalProperties as $prop)
                <div class="token-card !p-0 overflow-hidden flex flex-col bg-[var(--token-surface-subtle)]">
                    <div class="h-36 bg-[var(--token-surface-subtle)] relative">
                        @if ($prop->cover_image_url)
                            <img src="{{ $prop->cover_image_url }}" alt="{{ $prop->title }}" class="h-full w-full object-cover">
                        @else
                            <div class="h-full w-full flex items-center justify-center token-muted text-xs">No image</div>
                        @endif
                        <span class="absolute top-2 right-2 token-badge token-badge-accent !font-bold">
                            {{ number_format($prop->price) }} ETB / mo
                        </span>
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <h4 class="font-bold text-sm line-clamp-1">{{ $prop->title }}</h4>
                            <p class="text-xs token-muted mt-1">📍 {{ $prop->city }}, {{ $prop->subcity ?? '' }}</p>
                        </div>
                        <div class="mt-4 pt-2 border-t border-[var(--token-border)] flex items-center justify-between">
                            <a href="{{ route('properties.show', $prop->slug) }}" class="text-xs font-bold text-[var(--token-primary)] hover:underline">View Listing</a>
                            <button type="button" onclick="applyForRental({{ $prop->id }})" class="token-btn token-btn-primary !py-1 !px-3 !text-[11px]">
                                Apply Now
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <p class="col-span-3 text-xs token-muted py-4 text-center">No rental properties available.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Rent Installment Payment Modal for Tenant --}}
<div id="tenant-rent-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-[var(--token-overlay)] p-4 backdrop-blur-xs flex items-center justify-center">
    <div class="relative w-full max-w-md token-card !p-6 shadow-2xl">
        <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-3">
            <div>
                <span class="token-badge token-badge-accent">Ethiopian Digital Payment</span>
                <h3 class="mt-1 text-lg font-black">Pay Rent Installment</h3>
            </div>
            <button type="button" onclick="closeTenantRentModal()" class="token-btn token-btn-ghost !p-2 !rounded-full">&times;</button>
        </div>

        <form id="tenant-rent-payment-form" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            <div id="tenant-rent-period-display" class="token-alert token-alert-info text-xs font-bold"></div>

            <div>
                <label class="mb-1 block text-xs font-bold token-muted">Payment Amount (ETB) *</label>
                <input type="number" step="0.01" min="1" name="amount_paid" id="tenant-rent-amount" required class="token-field font-mono text-sm font-bold">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold token-muted">Payment Method (የክፍያ አማራጭ) *</label>
                <select name="payment_method" id="tenant-rent-method" required class="token-field text-xs font-bold">
                    <option value="telebirr" selected>Telebirr (ቴሌብር) — Instant Mobile Wallet</option>
                    <option value="cbe_birr">CBE Birr (ሲቢኢ ብር) — Commercial Bank</option>
                    <option value="bank_transfer">Bank Transfer (የባንክ ሒሳብ ማስተላለፊያ)</option>
                    <option value="cash">Cash at Office (ጥሬ ገንዘብ)</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold token-muted">Bank / Wallet Reference (Optional)</label>
                <input type="text" name="bank_transaction_reference" placeholder="e.g. FT26... or Telebirr Trans ID" class="token-field font-mono text-xs">
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-[var(--token-border-subtle)] pt-4">
                <button type="button" onclick="closeTenantRentModal()" class="token-btn token-btn-ghost">
                    Cancel
                </button>
                <button type="submit" class="token-btn token-btn-primary">
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
