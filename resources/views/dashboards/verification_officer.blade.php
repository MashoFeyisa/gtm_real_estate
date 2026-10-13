@extends('dashboards.layout')

@section('portal_name', 'Compliance Desk')
@section('dashboard-title', 'Verification Desk & Compliance')
@section('dashboard-subtitle', 'Title deed cartas, site plan mapas, business licenses, developer credentials, and authentication queues.')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if (auth()->user()->hasAnyPermission(['verifications.manage', 'verifications.identity_approve', 'verification_jobs.accept']))
        <a href="#documents" data-target-section="documents" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📎</span>
                <span>Property Documents</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['pending_docs'] }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['verifications.company_approve', 'verifications.manage']))
        <a href="#companies-developers" data-target-section="companies-developers" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🏢</span>
                <span>Agencies & Developers</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['pending_companies'] + $stats['pending_developers'] }}</span>
        </a>
    @endif
@endsection

@section('dashboard-content')
    <!-- Overview Section (Analytics & Charts) -->
    <div id="overview" data-section="overview" class="section-panel space-y-8">
        <!-- Metric Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Pending Documents</span>
                <div class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending_docs'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Verified Documents</span>
                <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['verified_docs'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Pending Companies</span>
                <div class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['pending_companies'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Pending Developers</span>
                <div class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['pending_developers'] }}</div>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    <!-- Section 1: Property Document Verification Queue -->
    <div id="documents" data-section="documents" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Property Documents Verification Queue</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="p-3">Doc #</th>
                        <th class="p-3">Property</th>
                        <th class="p-3">Type</th>
                        <th class="p-3">Title & Number</th>
                        <th class="p-3">Access Level</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($documentsQueue as $doc)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                            <td class="p-3 font-medium">#{{ $doc->id }}</td>
                            <td class="p-3 font-semibold text-slate-900 dark:text-white">{{ $doc->property->title ?? 'N/A' }}</td>
                            <td class="p-3">{{ $doc->formatted_type }}</td>
                            <td class="p-3">
                                <div>{{ $doc->title }}</div>
                                <div class="text-[10px] text-slate-400">Ref: {{ $doc->document_number ?? 'None' }}</div>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                    {{ ucfirst(str_replace('_', ' ', $doc->access_level ?? 'confidential')) }}
                                </span>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                    @if($doc->status === 'verified') bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                                    @elseif($doc->status === 'rejected') bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300
                                    @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif">
                                    {{ strtoupper($doc->status ?? 'pending') }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <form method="POST" action="{{ route('verification-officer.documents.decision', $doc) }}" class="inline-flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="verification_notes" placeholder="Notes..." class="text-[11px] px-2 py-1 rounded border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 w-32">
                                    <button type="submit" name="status" value="verified" class="px-2.5 py-1 bg-emerald-600 text-white rounded text-[11px] font-medium hover:bg-emerald-700">Approve</button>
                                    <button type="submit" name="status" value="rejected" class="px-2.5 py-1 bg-rose-600 text-white rounded text-[11px] font-medium hover:bg-rose-700">Reject</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-400">No documents in queue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Companies & Developers Queues Grid -->
    <div id="companies-developers" data-section="companies-developers" class="section-panel hidden scroll-mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Companies -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Agency / Broker License Verification</h3>
            <div class="space-y-3">
                @forelse($companiesQueue as $agent)
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-sm text-slate-900 dark:text-white">{{ $agent->name }}</div>
                            <div class="text-xs text-slate-500">License: {{ $agent->license_number ?? 'Not specified' }} | Type: {{ ucfirst($agent->type ?? 'agency') }}</div>
                        </div>
                        <form method="POST" action="{{ route('verification-officer.companies.decision', $agent) }}">
                            @csrf
                            @method('PATCH')
                            @if($agent->license_verified)
                                <button type="submit" name="decision" value="revoke" class="px-3 py-1 bg-rose-100 text-rose-700 hover:bg-rose-200 text-xs rounded-lg font-medium">Revoke</button>
                            @else
                                <button type="submit" name="decision" value="verify" class="px-3 py-1 bg-emerald-600 text-white hover:bg-emerald-700 text-xs rounded-lg font-medium">Verify License</button>
                            @endif
                        </form>
                    </div>
                @empty
                    <div class="text-slate-400 text-xs py-4 text-center">No agencies in queue.</div>
                @endforelse
            </div>
        </div>

        <!-- Developers -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Developer & Project Verification</h3>
            <div class="space-y-3">
                @forelse($developersQueue as $project)
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-sm text-slate-900 dark:text-white">{{ $project->name }}</div>
                            <div class="text-xs text-slate-500">Developer: {{ $project->developer_name }} | City: {{ $project->city }}</div>
                        </div>
                        <form method="POST" action="{{ route('verification-officer.developers.decision', $project) }}">
                            @csrf
                            @method('PATCH')
                            @if($project->developer_verified)
                                <button type="submit" name="decision" value="revoke" class="px-3 py-1 bg-rose-100 text-rose-700 hover:bg-rose-200 text-xs rounded-lg font-medium">Revoke</button>
                            @else
                                <button type="submit" name="decision" value="verify" class="px-3 py-1 bg-purple-600 text-white hover:bg-purple-700 text-xs rounded-lg font-medium">Verify Developer</button>
                            @endif
                        </form>
                    </div>
                @empty
                    <div class="text-slate-400 text-xs py-4 text-center">No projects in queue.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
