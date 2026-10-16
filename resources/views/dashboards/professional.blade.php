@extends('dashboards.layout')

@section('title', 'Professional Service Provider Dashboard')
@section('portal_name', 'Professional Workspace')

@section('sidebar-nav')
    @if (auth()->user()->hasAnyPermission(['services.jobs_receive', 'services.jobs_accept']))
        <x-sidebar-item href="#requests" icon="🧰" label="Job Invitations" :count="$requests->count()" />
    @endif
    @if (auth()->user()->hasAnyPermission(['services.jobs_receive', 'services.reports_upload', 'services.profile_manage']))
        <x-sidebar-item href="#appointments-reports" icon="📑" label="Appointments & Reports" :count="$appointments->count() + $reports->count()" />
    @endif
    <x-sidebar-item href="#profile-rates" icon="👤" label="Profile & Rates" />
@endsection

@section('content')
<div class="space-y-8">
    {{-- Overview Section (Analytics & Charts) --}}
    <div id="overview" data-section="overview" class="section-panel space-y-8">
        {{-- Banner --}}
        <div class="token-hero">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <span class="token-hero-chip">Licensed Professional Desk</span>
                    <h1 class="token-hero-title mt-2 text-2xl md:text-3xl">Professional Services &amp; Appraisals</h1>
                    <p class="token-hero-sub mt-1 text-sm">Valuers, Lawyers, Inspectors, Surveyors and Contractors: Manage job invitations, appointments, legal reports, client payments, and reviews.</p>
                </div>
                <div class="text-right">
                    <span class="token-hero-stat-label text-xs">Total Settled Fees</span>
                    <p class="token-hero-stat-value text-2xl">{{ number_format($totalEarnings) }} ETB</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 border-t border-[var(--token-hero-border)] pt-6">
                <div>
                    <p class="token-hero-stat-label text-xs">Service Requests</p>
                    <p class="token-hero-stat-value text-2xl">{{ $requests->count() }}</p>
                </div>
                <div>
                    <p class="token-hero-stat-label text-xs">Scheduled Appointments</p>
                    <p class="token-hero-stat-value text-2xl">{{ $appointments->count() }}</p>
                </div>
                <div>
                    <p class="token-hero-stat-label text-xs">Certified Reports</p>
                    <p class="token-hero-stat-value text-2xl">{{ $reports->count() }}</p>
                </div>
                <div>
                    <p class="token-hero-stat-label text-xs">Client Reviews</p>
                    <p class="token-hero-stat-value text-2xl">{{ $reviews->count() }}</p>
                </div>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- 1. Incoming Service Requests Queue --}}
    <div id="requests" data-section="requests" class="section-panel hidden scroll-mt-6 token-card">
        <h2 class="text-xl font-black border-b border-[var(--token-border)] pb-3">Service Invitations &amp; Job Queue</h2>
        <div class="mt-4 token-table-wrap">
            <table class="token-table">
                <thead>
                    <tr>
                        <th>Request Ref</th>
                        <th>Service Type</th>
                        <th>Client / Property</th>
                        <th>Fee (ETB)</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr>
                            <td class="font-mono font-bold text-xs">{{ $req->request_code }}</td>
                            <td class="font-semibold">{{ ucfirst(str_replace('_', ' ', $req->service_type)) }}</td>
                            <td class="text-xs">
                                <strong>{{ $req->user?->name ?? 'Client' }}</strong> &bull; <span class="token-muted">{{ $req->property?->title ?? 'Direct Case' }}</span>
                            </td>
                            <td class="font-bold text-[var(--token-success)]">{{ number_format($req->fee) }} ETB</td>
                            <td>
                                <span class="token-badge {{ match ($req->status) { 'completed' => 'token-badge-success', 'in_progress' => 'token-badge-info', 'accepted' => 'token-badge-warning', default => 'token-badge-neutral' } }}">
                                    {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                                </span>
                            </td>
                            <td class="text-right space-x-1">
                                @if ($req->status === 'requested')
                                    <form method="POST" action="{{ route('professional.requests.status', $req) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="accepted">
                                        <button type="submit" class="token-btn token-btn-primary !py-1 !px-3 !text-xs">Accept</button>
                                    </form>
                                    <button type="button" onclick="document.getElementById('quote-modal-{{ $req->id }}').classList.toggle('hidden')" class="token-btn token-btn-secondary !py-1 !px-3 !text-xs">
                                        Quote Fee
                                    </button>
                                    <form method="POST" action="{{ route('professional.requests.status', $req) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="declined">
                                        <button type="submit" class="token-btn token-btn-danger !py-1 !px-2.5 !text-xs">Decline</button>
                                    </form>
                                @elseif ($req->status === 'quote_submitted')
                                    <button type="button" onclick="document.getElementById('apt-modal-{{ $req->id }}').classList.toggle('hidden')" class="token-btn token-btn-primary !py-1 !px-3 !text-xs">
                                        Schedule
                                    </button>
                                    <form method="POST" action="{{ route('professional.requests.status', $req) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="in_progress">
                                        <button type="submit" class="token-btn token-btn-primary !py-1 !px-3 !text-xs">Start Work</button>
                                    </form>
                                @elseif ($req->status === 'accepted')
                                    <button type="button" onclick="document.getElementById('apt-modal-{{ $req->id }}').classList.toggle('hidden')" class="token-btn token-btn-primary !py-1 !px-3 !text-xs">
                                        Schedule
                                    </button>
                                    <form method="POST" action="{{ route('professional.requests.status', $req) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="in_progress">
                                        <button type="submit" class="token-btn token-btn-primary !py-1 !px-3 !text-xs">Start Work</button>
                                    </form>
                                @elseif ($req->status === 'in_progress')
                                    <button type="button" onclick="document.getElementById('report-modal-{{ $req->id }}').classList.toggle('hidden')" class="token-btn token-btn-primary !py-1 !px-3 !text-xs">
                                        Upload Report &rarr;
                                    </button>
                                @else
                                    <span class="token-badge token-badge-success">✓ Completed</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Fee Quote Modal --}}
                        <tr id="quote-modal-{{ $req->id }}" class="hidden">
                            <td colspan="6" class="p-4 bg-[var(--token-surface-subtle)]">
                                <div class="token-card !p-5">
                                    <h4 class="font-black text-sm mb-3">Submit Fee Quotation Proposal (ref: {{ $req->request_code }})</h4>
                                    <form method="POST" action="{{ route('professional.requests.quote', $req) }}" class="space-y-3">
                                        @csrf
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-bold token-muted">Fee Amount (ETB) *</label>
                                                <input type="number" step="0.01" min="1" name="fee" value="{{ old('fee', $req->fee ?: 5000) }}" required class="mt-1 token-field">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold token-muted">Estimated Duration (Days)</label>
                                                <input type="number" min="1" max="365" name="estimated_days" value="3" class="mt-1 token-field">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold token-muted">Quotation Notes / Terms</label>
                                            <textarea name="quote_notes" rows="2" placeholder="Fee covers initial on-site physical inspection, valuation certification, and registry search..." class="mt-1 token-field"></textarea>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2">
                                            <button type="button" onclick="document.getElementById('quote-modal-{{ $req->id }}').classList.add('hidden')" class="token-btn token-btn-ghost !text-xs">Cancel</button>
                                            <button type="submit" class="token-btn token-btn-primary !text-xs">Submit Formal Quote</button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Appointment Scheduling Modal --}}
                        <tr id="apt-modal-{{ $req->id }}" class="hidden">
                            <td colspan="6" class="p-4 bg-[var(--token-surface-subtle)]">
                                <div class="token-card !p-5">
                                    <h4 class="font-black text-sm mb-3">Schedule Site Inspection Appointment (ref: {{ $req->request_code }})</h4>
                                    <form method="POST" action="{{ route('professional.requests.appointment', $req) }}" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-bold token-muted">Appointment Date &amp; Time *</label>
                                            <input type="datetime-local" name="appointment_date" required class="mt-1 token-field">
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2">
                                            <button type="button" onclick="document.getElementById('apt-modal-{{ $req->id }}').classList.add('hidden')" class="token-btn token-btn-ghost !text-xs">Cancel</button>
                                            <button type="submit" class="token-btn token-btn-primary !text-xs">Confirm Appointment</button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Report Upload Modal for In-Progress Requests --}}
                        @if ($req->status === 'in_progress')
                            <tr id="report-modal-{{ $req->id }}" class="hidden">
                                <td colspan="6" class="p-4 bg-[var(--token-surface-subtle)]">
                                    <div class="token-card !p-5">
                                        <h4 class="font-black text-sm mb-3">Upload Certified Findings &amp; Report (ref: {{ $req->request_code }})</h4>
                                        <form method="POST" action="{{ route('professional.requests.report', $req) }}" enctype="multipart/form-data" class="space-y-3">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-bold token-muted">Report Title *</label>
                                                <input type="text" name="report_title" required placeholder="e.g. Valuation &amp; Boundary Survey Certification" class="mt-1 token-field">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold token-muted">Key Professional Findings *</label>
                                                <textarea name="findings" rows="3" required placeholder="Summarize property boundary verification, structural state, and fair market appraisal..." class="mt-1 token-field"></textarea>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold token-muted">Attach Official Report PDF / Image</label>
                                                <input type="file" name="report_file" class="mt-1 token-field">
                                            </div>
                                            <div class="flex justify-end gap-2 pt-2">
                                                <button type="button" onclick="document.getElementById('report-modal-{{ $req->id }}').classList.add('hidden')" class="token-btn token-btn-ghost !text-xs">Cancel</button>
                                                <button type="submit" class="token-btn token-btn-primary !text-xs">Submit &amp; Certify</button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-xs token-muted">No active professional service requests.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 2. Appointments & Completed Reports --}}
    <div id="appointments-reports" data-section="appointments-reports" class="section-panel hidden scroll-mt-6 grid gap-6 md:grid-cols-2">
        <div class="token-card">
            <h3 class="text-lg font-black border-b border-[var(--token-border)] pb-3">Scheduled Appointments &amp; Inspections</h3>
            <div class="mt-4 space-y-3">
                @forelse ($appointments as $apt)
                    <div class="token-card !p-3 text-xs flex items-center justify-between">
                        <div>
                            <span class="font-bold">{{ $apt->title }}</span>
                            <p class="token-muted mt-0.5">📅 {{ $apt->appointment_date?->format('M d, Y H:i') }} &bull; {{ $apt->property?->city ?? 'Site Visit' }}</p>
                        </div>
                        <span class="token-badge token-badge-info">{{ ucfirst($apt->status) }}</span>
                    </div>
                @empty
                    <p class="text-xs token-muted py-4 text-center">No upcoming appointments scheduled.</p>
                @endforelse
            </div>
        </div>

        <div class="token-card">
            <h3 class="text-lg font-black border-b border-[var(--token-border)] pb-3">Certified Reports &amp; Client Reviews</h3>
            <div class="mt-4 space-y-3">
                @forelse ($reviews as $rev)
                    <div class="token-card !p-3 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold">{{ $rev->user?->name ?? 'Verified Client' }}</span>
                            <span class="text-xs font-black text-[var(--token-warning)]">★ {{ $rev->client_rating }}/5</span>
                        </div>
                        <p class="token-muted mt-1">"{{ $rev->client_review }}"</p>
                    </div>
                @empty
                    <p class="text-xs token-muted py-4 text-center">No client reviews submitted yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 3. Professional Profile & Rates Configuration --}}
    <div id="profile-rates" data-section="profile-rates" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex items-center justify-between border-b border-[var(--token-border)] pb-4">
            <div>
                <h3 class="text-xl font-black">Professional Profile &amp; Service Rates</h3>
                <p class="text-xs token-muted mt-0.5">Configure your professional credentials, hourly and fixed rates, and client-facing bio.</p>
            </div>
            <span class="token-badge token-badge-success">Verified Practitioner</span>
        </div>

        <form method="POST" action="{{ route('professional.profile.update') }}" class="mt-6 space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold token-muted">Profession / Specialty *</label>
                    <input type="text" name="profession" value="{{ old('profession', auth()->user()->profession) }}" placeholder="e.g. Certified Property Valuer, Real Estate Lawyer, Structural Engineer" class="mt-1 token-field">
                </div>
                <div>
                    <label class="block text-xs font-bold token-muted">Hourly Rate (ETB)</label>
                    <input type="number" step="0.01" min="0" name="hourly_rate" value="{{ old('hourly_rate', auth()->user()->hourly_rate) }}" placeholder="e.g. 1500.00" class="mt-1 token-field">
                </div>
                <div>
                    <label class="block text-xs font-bold token-muted">License / Bar / Registration Ref</label>
                    <input type="text" name="license_number" value="{{ old('license_number', auth()->user()->license_number) }}" placeholder="e.g. MOUDC-ETH-2026-VAL" class="mt-1 token-field font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold token-muted">Professional Bio &amp; Credentials</label>
                    <textarea name="bio" rows="4" placeholder="Over 10 years experience in real estate cadastral surveys, legal title transfer, and residential valuation across Addis Ababa..." class="mt-1 token-field">{{ old('bio', auth()->user()->bio) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold token-muted">Portfolio &amp; Past Experience Summary</label>
                    <textarea name="portfolio_summary" rows="4" placeholder="Completed boundary certifications for 200+ properties, high-rise structural reviews, and commercial lease dispute arbitration..." class="mt-1 token-field">{{ old('portfolio_summary', auth()->user()->portfolio_summary) }}</textarea>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-[var(--token-border)]">
                <button type="submit" class="token-btn token-btn-primary">
                    Save Professional Profile &amp; Rates
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
