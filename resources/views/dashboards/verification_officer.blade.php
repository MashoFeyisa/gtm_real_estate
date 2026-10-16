@extends('dashboards.layout')

@section('portal_name', 'Compliance Desk')
@section('dashboard-title', 'Verification Desk & Compliance')
@section('dashboard-subtitle', 'Title deed cartas, site plan mapas, user KYC identities, trust levels (0–5), and corporate licensing.')

@section('sidebar-nav')
    @if (auth()->user()->hasAnyPermission(['verifications.manage', 'documents.review_property', 'verification_jobs.accept']))
        <x-sidebar-item href="#documents" icon="📎" label="Property Documents" :count="$stats['pending_docs']" />
    @endif
    @if (auth()->user()->hasAnyPermission(['verifications.identity', 'verifications.identity_approve', 'verifications.manage']))
        <x-sidebar-item href="#identities" icon="🪪" label="User KYC Identities" :count="$stats['pending_identities']" />
    @endif
    @if (auth()->user()->hasAnyPermission(['verifications.manage']))
        <x-sidebar-item href="#trust-levels" icon="🛡️" label="Trust Levels (0-5)" :count="$stats['audited_properties']" />
    @endif
    @if (auth()->user()->hasAnyPermission(['verifications.company', 'verifications.company_approve', 'verifications.developer', 'verifications.developer_approve', 'verifications.manage']))
        <x-sidebar-item href="#companies-developers" icon="🏢" label="Agencies & Developers" :count="$stats['pending_companies'] + $stats['pending_developers']" />
    @endif
@endsection

@section('dashboard-content')
    <!-- Overview Section (Analytics & Charts) -->
    <div id="overview" data-section="overview" class="section-panel space-y-8">
        <!-- Metric Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="token-stat-card">
                <span class="token-stat-title">Pending Documents</span>
                <div class="token-stat-value token-stat-warning">{{ $stats['pending_docs'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Pending KYC IDs</span>
                <div class="token-stat-value token-stat-info">{{ $stats['pending_identities'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Verified Documents</span>
                <div class="token-stat-value token-stat-success">{{ $stats['verified_docs'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Pending Agencies</span>
                <div class="token-stat-value token-stat-accent">{{ $stats['pending_companies'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Audited Properties</span>
                <div class="token-stat-value token-stat-primary">{{ $stats['audited_properties'] }}</div>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    <!-- Section 1: Property Document Verification Queue -->
    <div id="documents" data-section="documents" class="section-panel hidden scroll-mt-6 token-card mb-8">
        <h3 class="text-lg font-bold mb-4">Property Documents Verification Queue</h3>
        <div class="token-table-wrap">
            <table class="token-table">
                <thead>
                    <tr>
                        <th>Doc #</th>
                        <th>Property</th>
                        <th>Type</th>
                        <th>Title & Number</th>
                        <th>Access Level</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documentsQueue as $doc)
                        <tr>
                            <td class="font-medium">#{{ $doc->id }}</td>
                            <td class="font-semibold">{{ $doc->property->title ?? 'N/A' }}</td>
                            <td>{{ $doc->formatted_type ?? str_replace('_', ' ', $doc->document_type) }}</td>
                            <td>
                                <div class="font-bold">{{ $doc->title }}</div>
                                <div class="text-[10px] token-muted">Ref: {{ $doc->document_number ?? 'None' }}</div>
                            </td>
                            <td>
                                <span class="token-badge token-badge-neutral !text-[10px]">
                                    {{ ucfirst(str_replace('_', ' ', $doc->access_level ?? 'confidential')) }}
                                </span>
                            </td>
                            <td>
                                <span class="token-badge
                                    @if($doc->status === 'verified') token-badge-success
                                    @elseif($doc->status === 'rejected') token-badge-danger
                                    @else token-badge-warning @endif">
                                    {{ strtoupper($doc->status ?? 'pending') }}
                                </span>
                            </td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('verification-officer.documents.decision', $doc) }}" class="inline-flex items-center gap-1.5">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="verification_notes" placeholder="Notes..." class="token-field !w-32">
                                    <button type="submit" name="status" value="verified" class="token-btn token-btn-primary !px-2 !py-1 !text-[10px]">Approve</button>
                                    <button type="submit" name="status" value="rejected" class="token-btn token-btn-danger !px-2 !py-1 !text-[10px]">Reject</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center token-muted">No documents in queue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: User KYC Identity Verification Queue -->
    <div id="identities" data-section="identities" class="section-panel hidden scroll-mt-6 token-card mb-8">
        <h3 class="text-lg font-bold mb-4">User KYC Identity Verification Queue</h3>
        <div class="token-table-wrap">
            <table class="token-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>ID Type</th>
                        <th>ID Document</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($identitiesQueue as $targetUser)
                        <tr>
                            <td>
                                <div class="font-bold">{{ $targetUser->name }}</div>
                                <div class="text-[10px] token-muted">{{ $targetUser->email }} | {{ $targetUser->phone ?? 'No phone' }}</div>
                            </td>
                            <td class="uppercase text-[10px] font-semibold">{{ $targetUser->role }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $targetUser->id_card_type ?? 'national_id')) }}</td>
                            <td>
                                @if($targetUser->id_card_path)
                                    <a href="{{ asset('storage/'.$targetUser->id_card_path) }}" target="_blank" class="underline font-semibold flex items-center gap-1 text-[var(--token-info)]">
                                        <span>🔍 View Uploaded ID</span>
                                    </a>
                                @else
                                    <span class="token-muted italic">No document file uploaded</span>
                                @endif
                            </td>
                            <td>
                                <span class="token-badge
                                    @if($targetUser->id_verification_status === 'approved') token-badge-success
                                    @elseif($targetUser->id_verification_status === 'rejected') token-badge-danger
                                    @else token-badge-warning @endif">
                                    {{ strtoupper($targetUser->id_verification_status ?? 'unverified') }}
                                </span>
                            </td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('verification-officer.identities.decision', $targetUser) }}" class="inline-flex items-center gap-1.5">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="rejection_reason" placeholder="Reason/Feedback notes..." class="token-field !w-36">
                                    <button type="submit" name="decision" value="approve" class="token-btn token-btn-primary !px-2 !py-1 !text-[10px]">Approve ID</button>
                                    <button type="submit" name="decision" value="reject" class="token-btn token-btn-danger !px-2 !py-1 !text-[10px]">Reject</button>
                                    <button type="submit" name="decision" value="request_photo" class="token-btn token-btn-secondary !px-2 !py-1 !text-[10px]">Request Photo</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center token-muted">No user KYC identity requests in queue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Trust Levels (0-5) & Badges Management -->
    <div id="trust-levels" data-section="trust-levels" class="section-panel hidden scroll-mt-6 token-card mb-8">
        <div class="mb-6">
            <h3 class="text-base font-bold">Property Trust Levels (0–5) & Badges</h3>
            <p class="text-xs token-muted">Assign official Ethiopian real estate verification tiers and title guarantee badges.</p>
        </div>

        <!-- Levels Explanation Guide -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6">
            <div class="token-chart-tile">
                <div class="font-bold text-xs token-muted">Level 0: Unverified</div>
                <p class="text-[10px] token-muted mt-1">Self-posted by user without title or ID check.</p>
            </div>
            <div class="token-chart-tile">
                <div class="font-bold text-xs text-[var(--token-info)]">Level 1: Doc Submitted</div>
                <p class="text-[10px] token-muted mt-1">Title deed or sales agreement uploaded.</p>
            </div>
            <div class="token-chart-tile">
                <div class="font-bold text-xs text-[var(--token-accent-stat)]">Level 2: ID & Title</div>
                <p class="text-[10px] token-muted mt-1">Owner identity matched with deed carta.</p>
            </div>
            <div class="token-chart-tile">
                <div class="font-bold text-xs text-[var(--token-warning)]">Level 3: Kebele Verified</div>
                <p class="text-[10px] token-muted mt-1">Cross-checked with Subcity Land registry.</p>
            </div>
            <div class="token-chart-tile">
                <div class="font-bold text-xs text-[var(--token-primary)]">Level 4: Physical Inspect</div>
                <p class="text-[10px] token-muted mt-1">On-site measurement and condition audit.</p>
            </div>
            <div class="token-chart-tile">
                <div class="font-bold text-xs text-[var(--token-success)]">Level 5: Guaranteed</div>
                <p class="text-[10px] token-muted mt-1">Title clear, cadastre plot verified, escrow ready.</p>
            </div>
        </div>

        <!-- Properties Trust Level Assignment Table -->
        <div class="token-table-wrap">
            <table class="token-table">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Owner / Agent</th>
                        <th>Current Level</th>
                        <th>Status</th>
                        <th class="text-right">Update Trust Tier & Badges</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($propertiesQueue as $prop)
                        <tr>
                            <td>
                                <div class="font-bold">#{{ $prop->id }} - {{ $prop->title }}</div>
                                <div class="text-[10px] token-muted">{{ $prop->city }} | {{ number_format($prop->price) }} ETB</div>
                            </td>
                            <td>{{ $prop->agent->name ?? $prop->user->name ?? 'Direct Owner' }}</td>
                            <td>
                                <span class="token-badge
                                    @if($prop->verification_level >= 4) token-badge-success
                                    @elseif($prop->verification_level >= 2) token-badge-info
                                    @else token-badge-neutral @endif">
                                    Level {{ $prop->verification_level }}
                                </span>
                            </td>
                            <td class="uppercase text-[10px] font-semibold">{{ $prop->verification_status ?? 'unverified' }}</td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('verification-officer.properties.trust-level', $prop) }}" class="inline-flex flex-wrap items-center justify-end gap-1.5">
                                    @csrf
                                    @method('PATCH')

                                    <select name="verification_level" class="token-field font-semibold">
                                        <option value="0" {{ $prop->verification_level == 0 ? 'selected' : '' }}>Level 0 (Unverified)</option>
                                        <option value="1" {{ $prop->verification_level == 1 ? 'selected' : '' }}>Level 1 (Doc Submitted)</option>
                                        <option value="2" {{ $prop->verification_level == 2 ? 'selected' : '' }}>Level 2 (ID & Title)</option>
                                        <option value="3" {{ $prop->verification_level == 3 ? 'selected' : '' }}>Level 3 (Kebele Verified)</option>
                                        <option value="4" {{ $prop->verification_level == 4 ? 'selected' : '' }}>Level 4 (Physical Inspect)</option>
                                        <option value="5" {{ $prop->verification_level == 5 ? 'selected' : '' }}>Level 5 (Guaranteed)</option>
                                    </select>

                                    <select name="verification_status" class="token-field font-semibold">
                                        <option value="unverified" {{ $prop->verification_status === 'unverified' ? 'selected' : '' }}>Unverified</option>
                                        <option value="pending" {{ $prop->verification_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="verified" {{ $prop->verification_status === 'verified' ? 'selected' : '' }}>Verified</option>
                                        <option value="rejected" {{ $prop->verification_status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>

                                    <input type="text" name="title_deed_number" value="{{ $prop->title_deed_number }}" placeholder="Carta #..." class="token-field !w-24">

                                    <button type="submit" class="token-btn token-btn-primary !px-2 !py-1 !text-[10px]">Save Tier</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center token-muted">No properties available for trust level evaluation.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 4: Companies & Developers Queues Grid -->
    <div id="companies-developers" data-section="companies-developers" class="section-panel hidden scroll-mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Companies -->
        <div class="token-card">
            <h3 class="text-base font-bold mb-4">Agency / Broker License Verification</h3>
            <div class="space-y-3">
                @forelse($companiesQueue as $agent)
                    <div class="token-chart-tile flex items-center justify-between">
                        <div>
                            <div class="font-bold text-sm">{{ $agent->name }}</div>
                            <div class="text-xs token-muted">License: {{ $agent->license_number ?? 'Not specified' }} | Type: {{ ucfirst($agent->type ?? 'agency') }}</div>
                        </div>
                        <form method="POST" action="{{ route('verification-officer.companies.decision', $agent) }}">
                            @csrf
                            @method('PATCH')
                            @if($agent->license_verified)
                                <button type="submit" name="decision" value="revoke" class="token-btn token-btn-danger">Revoke</button>
                            @else
                                <button type="submit" name="decision" value="verify" class="token-btn token-btn-primary">Verify License</button>
                            @endif
                        </form>
                    </div>
                @empty
                    <div class="token-muted text-xs py-4 text-center">No agencies in queue.</div>
                @endforelse
            </div>
        </div>

        <!-- Developers -->
        <div class="token-card">
            <h3 class="text-base font-bold mb-4">Developer Project Verification</h3>
            <div class="space-y-3">
                @forelse($developersQueue as $project)
                    <div class="token-chart-tile flex items-center justify-between">
                        <div>
                            <div class="font-bold text-sm">{{ $project->name }}</div>
                            <div class="text-xs token-muted">{{ $project->location ?? 'Addis Ababa' }} | Units: {{ $project->units()->count() }}</div>
                        </div>
                        <form method="POST" action="{{ route('verification-officer.developers.decision', $project) }}">
                            @csrf
                            @method('PATCH')
                            @if($project->developer_verified)
                                <button type="submit" name="decision" value="revoke" class="token-btn token-btn-danger">Revoke</button>
                            @else
                                <button type="submit" name="decision" value="verify" class="token-btn token-btn-primary">Verify Project</button>
                            @endif
                        </form>
                    </div>
                @empty
                    <div class="token-muted text-xs py-4 text-center">No developer projects in queue.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
