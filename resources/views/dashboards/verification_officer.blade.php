@extends('dashboards.layout')

@section('portal_name', 'Compliance Desk')
@section('dashboard-title', 'Verification Desk & Compliance')
@section('dashboard-subtitle', 'Title deed cartas, site plan mapas, user KYC identities, trust levels (0–5), and corporate licensing.')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if (auth()->user()->hasAnyPermission(['verifications.manage', 'documents.review_property', 'verification_jobs.accept']))
        <a href="#documents" data-target-section="documents" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📎</span>
                <span>Property Documents</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['pending_docs'] }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['verifications.identity', 'verifications.identity_approve', 'verifications.manage']))
        <a href="#identities" data-target-section="identities" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🪪</span>
                <span>User KYC Identities</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['pending_identities'] }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['verifications.manage']))
        <a href="#trust-levels" data-target-section="trust-levels" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🛡️</span>
                <span>Trust Levels (0-5)</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['audited_properties'] }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['verifications.company', 'verifications.company_approve', 'verifications.developer', 'verifications.developer_approve', 'verifications.manage']))
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
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Pending Documents</span>
                <div class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending_docs'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Pending KYC IDs</span>
                <div class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['pending_identities'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Verified Documents</span>
                <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['verified_docs'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Pending Agencies</span>
                <div class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['pending_companies'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Audited Properties</span>
                <div class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['audited_properties'] }}</div>
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
                            <td class="p-3">{{ $doc->formatted_type ?? str_replace('_', ' ', $doc->document_type) }}</td>
                            <td class="p-3">
                                <div class="font-bold">{{ $doc->title }}</div>
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
                                <form method="POST" action="{{ route('verification-officer.documents.decision', $doc) }}" class="inline-flex items-center gap-1.5">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="verification_notes" placeholder="Notes..." class="text-[10px] px-2 py-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 w-32">
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

    <!-- Section 2: User KYC Identity Verification Queue -->
    <div id="identities" data-section="identities" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">User KYC Identity Verification Queue</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="p-3">User</th>
                        <th class="p-3">Role</th>
                        <th class="p-3">ID Type</th>
                        <th class="p-3">ID Document</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($identitiesQueue as $targetUser)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                            <td class="p-3">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $targetUser->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $targetUser->email }} | {{ $targetUser->phone ?? 'No phone' }}</div>
                            </td>
                            <td class="p-3 uppercase text-[10px] font-semibold">{{ $targetUser->role }}</td>
                            <td class="p-3">{{ ucfirst(str_replace('_', ' ', $targetUser->id_card_type ?? 'national_id')) }}</td>
                            <td class="p-3">
                                @if($targetUser->id_card_path)
                                    <a href="{{ asset('storage/'.$targetUser->id_card_path) }}" target="_blank" class="text-blue-600 dark:text-blue-400 underline font-semibold flex items-center gap-1">
                                        <span>🔍 View Uploaded ID</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 italic">No document file uploaded</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                    @if($targetUser->id_verification_status === 'approved') bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                                    @elseif($targetUser->id_verification_status === 'rejected') bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300
                                    @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif">
                                    {{ strtoupper($targetUser->id_verification_status ?? 'unverified') }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <form method="POST" action="{{ route('verification-officer.identities.decision', $targetUser) }}" class="inline-flex items-center gap-1.5">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="rejection_reason" placeholder="Reason/Feedback notes..." class="text-[10px] px-2 py-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 w-36">
                                    <button type="submit" name="decision" value="approve" class="px-2.5 py-1 bg-emerald-600 text-white rounded text-[11px] font-medium hover:bg-emerald-700">Approve ID</button>
                                    <button type="submit" name="decision" value="reject" class="px-2.5 py-1 bg-rose-600 text-white rounded text-[11px] font-medium hover:bg-rose-700">Reject</button>
                                    <button type="submit" name="decision" value="request_photo" class="px-2.5 py-1 bg-amber-600 text-white rounded text-[11px] font-medium hover:bg-amber-700">Request Photo</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-400">No user KYC identity requests in queue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Trust Levels (0-5) & Badges Management -->
    <div id="trust-levels" data-section="trust-levels" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <div class="mb-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Property Trust Levels (0–5) & Badges</h3>
            <p class="text-xs text-slate-500">Assign official Ethiopian real estate verification tiers and title guarantee badges.</p>
        </div>

        <!-- Levels Explanation Guide -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6">
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <div class="font-bold text-xs text-slate-600">Level 0: Unverified</div>
                <p class="text-[10px] text-slate-400 mt-1">Self-posted by user without title or ID check.</p>
            </div>
            <div class="p-3 rounded-xl border border-blue-200 bg-blue-50/40 dark:bg-blue-950/20">
                <div class="font-bold text-xs text-blue-700">Level 1: Doc Submitted</div>
                <p class="text-[10px] text-slate-500 mt-1">Title deed or sales agreement uploaded.</p>
            </div>
            <div class="p-3 rounded-xl border border-indigo-200 bg-indigo-50/40 dark:bg-indigo-950/20">
                <div class="font-bold text-xs text-indigo-700">Level 2: ID & Title</div>
                <p class="text-[10px] text-slate-500 mt-1">Owner identity matched with deed carta.</p>
            </div>
            <div class="p-3 rounded-xl border border-amber-200 bg-amber-50/40 dark:bg-amber-950/20">
                <div class="font-bold text-xs text-amber-700">Level 3: Kebele Verified</div>
                <p class="text-[10px] text-slate-500 mt-1">Cross-checked with Subcity Land registry.</p>
            </div>
            <div class="p-3 rounded-xl border border-teal-200 bg-teal-50/40 dark:bg-teal-950/20">
                <div class="font-bold text-xs text-teal-700">Level 4: Physical Inspect</div>
                <p class="text-[10px] text-slate-500 mt-1">On-site measurement and condition audit.</p>
            </div>
            <div class="p-3 rounded-xl border border-emerald-300 bg-emerald-50/50 dark:bg-emerald-950/30">
                <div class="font-bold text-xs text-emerald-800">Level 5: Guaranteed</div>
                <p class="text-[10px] text-slate-600 mt-1">Title clear, cadastre plot verified, escrow ready.</p>
            </div>
        </div>

        <!-- Properties Trust Level Assignment Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="p-3">Property</th>
                        <th class="p-3">Owner / Agent</th>
                        <th class="p-3">Current Level</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Update Trust Tier & Badges</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($propertiesQueue as $prop)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                            <td class="p-3">
                                <div class="font-bold text-slate-900 dark:text-white">#{{ $prop->id }} - {{ $prop->title }}</div>
                                <div class="text-[10px] text-slate-400">{{ $prop->city }} | {{ number_format($prop->price) }} ETB</div>
                            </td>
                            <td class="p-3">{{ $prop->agent->name ?? $prop->user->name ?? 'Direct Owner' }}</td>
                            <td class="p-3">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold
                                    @if($prop->verification_level >= 4) bg-emerald-100 text-emerald-800
                                    @elseif($prop->verification_level >= 2) bg-blue-100 text-blue-800
                                    @else bg-slate-100 text-slate-700 @endif">
                                    Level {{ $prop->verification_level }}
                                </span>
                            </td>
                            <td class="p-3 uppercase text-[10px] font-semibold">{{ $prop->verification_status ?? 'unverified' }}</td>
                            <td class="p-3 text-right">
                                <form method="POST" action="{{ route('verification-officer.properties.trust-level', $prop) }}" class="inline-flex flex-wrap items-center justify-end gap-1.5">
                                    @csrf
                                    @method('PATCH')
                                    
                                    <select name="verification_level" class="text-[10px] px-2 py-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-semibold">
                                        <option value="0" {{ $prop->verification_level == 0 ? 'selected' : '' }}>Level 0 (Unverified)</option>
                                        <option value="1" {{ $prop->verification_level == 1 ? 'selected' : '' }}>Level 1 (Doc Submitted)</option>
                                        <option value="2" {{ $prop->verification_level == 2 ? 'selected' : '' }}>Level 2 (ID & Title)</option>
                                        <option value="3" {{ $prop->verification_level == 3 ? 'selected' : '' }}>Level 3 (Kebele Verified)</option>
                                        <option value="4" {{ $prop->verification_level == 4 ? 'selected' : '' }}>Level 4 (Physical Inspect)</option>
                                        <option value="5" {{ $prop->verification_level == 5 ? 'selected' : '' }}>Level 5 (Guaranteed)</option>
                                    </select>

                                    <select name="verification_status" class="text-[10px] px-2 py-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-semibold">
                                        <option value="unverified" {{ $prop->verification_status === 'unverified' ? 'selected' : '' }}>Unverified</option>
                                        <option value="pending" {{ $prop->verification_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="verified" {{ $prop->verification_status === 'verified' ? 'selected' : '' }}>Verified</option>
                                        <option value="rejected" {{ $prop->verification_status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>

                                    <input type="text" name="title_deed_number" value="{{ $prop->title_deed_number }}" placeholder="Carta #..." class="text-[10px] px-2 py-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 w-24">

                                    <button type="submit" class="px-2.5 py-1 bg-[#1d3c34] text-white rounded text-[11px] font-semibold hover:bg-[#152e28]">Save Tier</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-slate-400">No properties available for trust level evaluation.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 4: Companies & Developers Queues Grid -->
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
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Developer Project Verification</h3>
            <div class="space-y-3">
                @forelse($developersQueue as $project)
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-sm text-slate-900 dark:text-white">{{ $project->name }}</div>
                            <div class="text-xs text-slate-500">{{ $project->location ?? 'Addis Ababa' }} | Units: {{ $project->units()->count() }}</div>
                        </div>
                        <form method="POST" action="{{ route('verification-officer.developers.decision', $project) }}">
                            @csrf
                            @method('PATCH')
                            @if($project->developer_verified)
                                <button type="submit" name="decision" value="revoke" class="px-3 py-1 bg-rose-100 text-rose-700 hover:bg-rose-200 text-xs rounded-lg font-medium">Revoke</button>
                            @else
                                <button type="submit" name="decision" value="verify" class="px-3 py-1 bg-emerald-600 text-white hover:bg-emerald-700 text-xs rounded-lg font-medium">Verify Project</button>
                            @endif
                        </form>
                    </div>
                @empty
                    <div class="text-slate-400 text-xs py-4 text-center">No developer projects in queue.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
