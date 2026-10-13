@extends('dashboards.layout')

@section('title', 'Professional Service Provider Dashboard')
@section('portal_name', 'Professional Workspace')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if (auth()->user()->hasAnyPermission(['services.jobs_receive', 'services.jobs_accept']))
        <a href="#requests" data-target-section="requests" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🧰</span>
                <span>Job Invitations</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $requests->count() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['services.jobs_receive', 'services.reports_upload', 'services.profile_manage']))
        <a href="#appointments-reports" data-target-section="appointments-reports" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📑</span>
                <span>Appointments & Reports</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $appointments->count() + $reports->count() }}</span>
        </a>
    @endif
@endsection

@section('content')
<div class="space-y-8">
    {{-- Overview Section (Analytics & Charts) --}}
    <div id="overview" data-section="overview" class="section-panel space-y-8">
        {{-- Banner --}}
        <div class="scroll-mt-6 rounded-3xl border border-[#d9cab3] bg-gradient-to-r from-[#1d3c34] to-[#2d5d4d] p-6 text-white shadow-sm md:p-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-3 py-1 text-xs font-bold text-[#f8e7b9] uppercase tracking-wider">Licensed Professional Desk</span>
                    <h1 class="mt-2 text-2xl md:text-3xl font-black text-white">Professional Services &amp; Appraisals</h1>
                    <p class="mt-1 text-sm text-slate-200">Valuers, Lawyers, Inspectors, Surveyors and Contractors: Manage job invitations, appointments, legal reports, client payments, and reviews.</p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-300">Total Settled Fees</span>
                    <p class="text-2xl font-black text-emerald-300">{{ number_format($totalEarnings) }} ETB</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 border-t border-white/20 pt-6">
                <div>
                    <p class="text-xs font-semibold text-slate-300">Service Requests</p>
                    <p class="text-2xl font-black text-white">{{ $requests->count() }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-300">Scheduled Appointments</p>
                    <p class="text-2xl font-black text-[#f8e7b9]">{{ $appointments->count() }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-300">Certified Reports</p>
                    <p class="text-2xl font-black text-white">{{ $reports->count() }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-300">Client Reviews</p>
                    <p class="text-2xl font-black text-white">{{ $reviews->count() }}</p>
                </div>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- 1. Incoming Service Requests Queue --}}
    <div id="requests" data-section="requests" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Service Invitations &amp; Job Queue</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e7ddca] text-sm">
                <thead>
                    <tr class="bg-[#fbf9f5] text-left text-xs font-black uppercase text-[#587165]">
                        <th class="py-3 px-4">Request #</th>
                        <th class="py-3 px-4">Service Type</th>
                        <th class="py-3 px-4">Client / Property</th>
                        <th class="py-3 px-4">Fee (ETB)</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca]">
                    @forelse ($requests as $req)
                        <tr class="hover:bg-[#fcfaf7]">
                            <td class="py-3 px-4 font-mono font-bold text-xs text-[#1d3c34]">{{ $req->request_code }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ ucfirst(str_replace('_', ' ', $req->service_type)) }}</td>
                            <td class="py-3 px-4 text-xs text-slate-600">
                                <strong>{{ $req->user?->name ?? 'Client' }}</strong> &bull; {{ $req->property?->title ?? 'Direct Case' }}
                            </td>
                            <td class="py-3 px-4 font-bold text-emerald-800">{{ number_format($req->fee) }} ETB</td>
                            <td class="py-3 px-4">
                                <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ match ($req->status) { 'completed' => 'bg-emerald-100 text-emerald-800', 'in_progress' => 'bg-blue-100 text-blue-800', 'accepted' => 'bg-amber-100 text-amber-800', default => 'bg-slate-200 text-slate-700' } }}">
                                    {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if ($req->status === 'requested')
                                    <form method="POST" action="{{ route('professional.requests.status', $req) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="accepted">
                                        <button type="submit" class="rounded-full bg-[#1d3c34] px-3 py-1 text-xs font-bold text-white hover:bg-[#254d43]">Accept</button>
                                    </form>
                                @elseif ($req->status === 'accepted')
                                    <form method="POST" action="{{ route('professional.requests.status', $req) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="in_progress">
                                        <button type="submit" class="rounded-full bg-blue-700 px-3 py-1 text-xs font-bold text-white hover:bg-blue-800">Start Work</button>
                                    </form>
                                @elseif ($req->status === 'in_progress')
                                    <button type="button" onclick="document.getElementById('report-modal-{{ $req->id }}').classList.toggle('hidden')" class="rounded-full bg-emerald-700 px-3 py-1 text-xs font-bold text-white hover:bg-emerald-800">
                                        Upload Report &rarr;
                                    </button>
                                @else
                                    <span class="text-xs font-bold text-emerald-700">✓ Completed</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Report Upload Modal for In-Progress Requests --}}
                        @if ($req->status === 'in_progress')
                            <tr id="report-modal-{{ $req->id }}" class="hidden bg-[#fdfbf7]">
                                <td colspan="6" class="p-6">
                                    <div class="rounded-2xl border border-[#d4af37] bg-white p-5 shadow-xs">
                                        <h4 class="font-black text-sm text-[#1d3c34] mb-3">Upload Certified Findings &amp; Report (#{{ $req->request_code }})</h4>
                                        <form method="POST" action="{{ route('professional.requests.report', $req) }}" enctype="multipart/form-data" class="space-y-3">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700">Report Title *</label>
                                                <input type="text" name="report_title" required placeholder="e.g. Valuation &amp; Boundary Survey Certification" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700">Key Professional Findings *</label>
                                                <textarea name="findings" rows="3" required placeholder="Summarize property boundary verification, structural state, and fair market appraisal..." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs"></textarea>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700">Attach Official Report PDF / Image</label>
                                                <input type="file" name="report_file" class="mt-1 w-full text-xs text-slate-500">
                                            </div>
                                            <div class="flex justify-end gap-2 pt-2">
                                                <button type="button" onclick="document.getElementById('report-modal-{{ $req->id }}').classList.add('hidden')" class="rounded-full px-4 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-800">Cancel</button>
                                                <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-1.5 text-xs font-bold text-white hover:bg-[#254d43]">Submit &amp; Certify</button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-xs text-slate-400">No active professional service requests.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 2. Appointments & Completed Reports --}}
    <div id="appointments-reports" data-section="appointments-reports" class="section-panel hidden scroll-mt-6 grid gap-6 md:grid-cols-2">
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Scheduled Appointments &amp; Inspections</h3>
            <div class="mt-4 space-y-3">
                @forelse ($appointments as $apt)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-3 text-xs flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#1d3c34]">{{ $apt->title }}</span>
                            <p class="text-slate-500 mt-0.5">📅 {{ $apt->appointment_date?->format('M d, Y H:i') }} &bull; {{ $apt->property?->city ?? 'Site Visit' }}</p>
                        </div>
                        <span class="rounded-full bg-blue-100 text-blue-800 px-2 py-0.5 text-[10px] font-bold">{{ ucfirst($apt->status) }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No upcoming appointments scheduled.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Certified Reports &amp; Client Reviews</h3>
            <div class="mt-4 space-y-3">
                @forelse ($reviews as $rev)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-3 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#1d3c34]">{{ $rev->user?->name ?? 'Verified Client' }}</span>
                            <span class="text-xs font-black text-[#d4af37]">★ {{ $rev->client_rating }}/5</span>
                        </div>
                        <p class="text-slate-600 mt-1">"{{ $rev->client_review }}"</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No client reviews submitted yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
