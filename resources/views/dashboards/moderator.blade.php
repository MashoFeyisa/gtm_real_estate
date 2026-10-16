@extends('dashboards.layout')

@section('portal_name', 'Moderation Desk')
@section('dashboard-title', 'Content Moderation & Listing Quality Desk')
@section('dashboard-subtitle', 'Listing review queues, fraud & fake reports, review sentiment moderation, and duplicate detection.')

@section('sidebar-nav')
    @if (auth()->user()->hasAnyPermission(['listings.review', 'moderation.manage']))
        <x-sidebar-item href="#listings" icon="📋" label="Listings Review" :count="$stats['pending_listings']" />
    @endif
    @if (auth()->user()->hasAnyPermission(['reports.resolve', 'reports.view', 'moderation.manage', 'reports.handle']))
        <x-sidebar-item href="#reports" icon="🚨" label="Fraud & Safety Reports" :count="$stats['pending_reports']" />
    @endif
    @if (auth()->user()->hasAnyPermission(['reviews.moderate', 'moderation.manage', 'content.moderate']))
        <x-sidebar-item href="#reviews" icon="💬" label="Review Moderation" :count="$stats['hidden_reviews']" />
    @endif
    @if (auth()->user()->hasAnyPermission(['properties.merge', 'moderation.manage']))
        <x-sidebar-item href="#duplicates" icon="👥" label="Duplicate Listings" :count="$stats['duplicate_clusters']" />
    @endif
@endsection

@section('dashboard-content')
    <!-- Overview Section (Analytics & Charts) -->
    <div id="overview" data-section="overview" class="section-panel space-y-8">
        <!-- Metric Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="token-stat-card">
                <span class="token-stat-title">Pending Listings</span>
                <div class="token-stat-value token-stat-warning">{{ $stats['pending_listings'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Pending Reports</span>
                <div class="token-stat-value token-stat-danger">{{ $stats['pending_reports'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Fraud Flags</span>
                <div class="token-stat-value token-stat-danger">{{ $stats['fraud_reports'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Published Listings</span>
                <div class="token-stat-value token-stat-success">{{ $stats['published_listings'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Hidden Reviews</span>
                <div class="token-stat-value token-stat-accent">{{ $stats['hidden_reviews'] }}</div>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    <!-- Section 1: Listings Moderation Queue -->
    <div id="listings" data-section="listings" class="section-panel hidden scroll-mt-6 token-card mb-8">
        <h3 class="text-lg font-bold mb-4">Listing Review & Quality Queue</h3>
        <div class="token-table-wrap">
            <table class="token-table">
                <thead>
                    <tr>
                        <th>Listing Code</th>
                        <th>Title & Property</th>
                        <th>Type</th>
                        <th>Price (ETB)</th>
                        <th>Status</th>
                        <th class="text-right">Moderation Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listingsQueue as $listing)
                        <tr>
                            <td class="font-mono font-semibold">{{ $listing->listing_code }}</td>
                            <td>
                                <div class="font-bold">{{ $listing->title }}</div>
                                <div class="text-[10px] token-muted">{{ $listing->property->city ?? 'Addis Ababa' }} | Quality Score: {{ $listing->quality_score ?? 85 }}/100</div>
                            </td>
                            <td class="uppercase text-[10px] font-semibold">{{ $listing->listing_type }}</td>
                            <td class="font-semibold">{{ number_format($listing->price) }} ETB</td>
                            <td>
                                <span class="token-badge
                                    @if($listing->status === 'published') token-badge-success
                                    @elseif($listing->status === 'rejected') token-badge-danger
                                    @elseif($listing->status === 'pending_review') token-badge-warning
                                    @else token-badge-neutral @endif">
                                    {{ strtoupper(str_replace('_', ' ', $listing->status)) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('moderator.listings.decision', $listing) }}" class="inline-flex items-center gap-1.5">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="moderation_notes" placeholder="Notes/Correction reason..." class="token-field !w-36">
                                    <button type="submit" name="status" value="published" class="token-btn token-btn-primary !px-2 !py-1 !text-[10px]">Publish</button>
                                    <button type="submit" name="status" value="approved" class="token-btn token-btn-primary !px-2 !py-1 !text-[10px]">Approve</button>
                                    <button type="submit" name="status" value="rejected" class="token-btn token-btn-danger !px-2 !py-1 !text-[10px]">Reject</button>
                                    <button type="submit" name="status" value="paused" class="token-btn token-btn-secondary !px-2 !py-1 !text-[10px]">Suspend</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center token-muted">No listings currently in moderation queue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Reports & Incident Triage -->
    <div id="reports" data-section="reports" class="section-panel hidden scroll-mt-6 token-card mb-8">
        <h3 class="text-base font-bold mb-4">Fraud, Scam & Safety Reports</h3>
        <div class="space-y-4">
            @forelse($reportsQueue as $report)
                <div class="{{ $report->is_fraud_flagged ? 'token-alert token-alert-danger' : 'token-chart-tile' }}">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-xs">Report #{{ $report->id }} - {{ $report->subject ?? 'Listing Issue' }}</span>
                            @if($report->is_fraud_flagged)
                                <span class="token-badge token-badge-danger uppercase animate-pulse">🚩 Fraud Flagged</span>
                            @endif
                            @if($report->action_taken)
                                <span class="token-badge token-badge-warning uppercase">Action: {{ str_replace('_', ' ', $report->action_taken) }}</span>
                            @endif
                        </div>
                        <span class="token-badge uppercase
                            @if($report->status === 'resolved') token-badge-success
                            @elseif($report->status === 'investigating') token-badge-info
                            @else token-badge-danger @endif">
                            {{ $report->status }}
                        </span>
                    </div>
                    <p class="text-xs token-muted mb-3">{{ $report->description ?? $report->reason }}</p>
                    @if($report->property)
                        <div class="text-[11px] token-muted mb-3">
                            Target Property: <strong>{{ $report->property->title }}</strong> (ID: #{{ $report->property->id }}, Status: {{ $report->property->status }})
                        </div>
                    @endif
                    <form method="POST" action="{{ route('moderator.reports.decision', $report) }}" class="flex flex-wrap items-center gap-2 pt-2 border-t border-[var(--token-border-subtle)]">
                        @csrf
                        @method('PATCH')
                        <input type="text" name="resolution_notes" placeholder="Resolution / investigation notes..." class="token-field flex-1 min-w-[200px]">

                        <select name="action_type" class="token-field">
                            <option value="none">Standard Status Update</option>
                            <option value="unpublish_listing">Unpublish & Pause Listing</option>
                            <option value="warn_user">Formally Warn Account Owner</option>
                            <option value="flag_fraud">Flag as Verified Fraud</option>
                        </select>

                        <button type="submit" name="status" value="investigating" class="token-btn token-btn-primary">Investigate</button>
                        <button type="submit" name="status" value="resolved" class="token-btn token-btn-primary">Resolve</button>
                        <button type="submit" name="status" value="dismissed" class="token-btn token-btn-secondary">Dismiss</button>
                    </form>
                </div>
            @empty
                <div class="token-muted text-xs py-4 text-center">No reported listings.</div>
            @endforelse
        </div>
    </div>

    <!-- Section 3: Review Moderation -->
    <div id="reviews" data-section="reviews" class="section-panel hidden scroll-mt-6 token-card mb-8">
        <h3 class="text-base font-bold mb-4">User Review Moderation</h3>
        <div class="space-y-3">
            @forelse($reviewsQueue as $review)
                <div class="token-chart-tile flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-xs">{{ $review->review_title ?? 'Property Review' }}</span>
                            <span class="text-xs font-bold text-[var(--token-accent)]">★ {{ $review->rating }}/5</span>
                            <span class="token-badge uppercase {{ $review->status === 'hidden' ? 'token-badge-danger' : 'token-badge-success' }}">
                                {{ $review->status }}
                            </span>
                        </div>
                        <p class="text-xs token-muted italic">"{{ $review->comment }}"</p>
                        <span class="text-[10px] token-muted">By {{ $review->user->name ?? 'Guest' }} for Property: {{ $review->property->title ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('moderator.reviews.decision', $review) }}">
                            @csrf
                            @method('PATCH')
                            @if($review->status === 'hidden')
                                <button type="submit" name="status" value="published" class="token-btn token-btn-primary">Restore</button>
                            @else
                                <button type="submit" name="status" value="hidden" class="token-btn token-btn-secondary">Hide / Censor</button>
                            @endif
                        </form>
                        <form method="POST" action="{{ route('moderator.reviews.destroy', $review) }}" onsubmit="return confirm('Permanently remove this inappropriate review?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="token-btn token-btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="token-muted text-xs py-4 text-center">No reviews submitted yet.</div>
            @endforelse
        </div>
    </div>

    <!-- Section 4: Duplicate Listings & Merge Desk -->
    <div id="duplicates" data-section="duplicates" class="section-panel hidden scroll-mt-6 token-card mb-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold">Duplicate Listings Detector & Merge Desk</h3>
                <p class="text-xs token-muted">Detect suspected duplicate listings by matching titles or locations, and merge them into a single canonical listing.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Detected Duplicate Clusters -->
            <div class="token-chart-tile">
                <h4 class="text-xs font-bold uppercase tracking-wider mb-3">Detected Duplicate Clusters</h4>
                <div class="space-y-2">
                    @forelse($duplicateCandidates as $cluster)
                        <div class="token-chart-tile flex items-center justify-between">
                            <div>
                                <div class="font-bold text-xs">{{ $cluster->title }}</div>
                                <div class="text-[10px] token-muted">{{ $cluster->city }} | IDs: {{ $cluster->property_ids }}</div>
                            </div>
                            <span class="token-badge token-badge-warning !text-[10px]">{{ $cluster->count }} Listings</span>
                        </div>
                    @empty
                        <div class="token-muted text-xs py-3 text-center">No duplicate clusters detected.</div>
                    @endforelse
                </div>
            </div>

            <!-- Merge Tool Form -->
            <div class="token-chart-tile">
                <h4 class="text-xs font-bold uppercase tracking-wider mb-3">Merge Duplicate Tool</h4>
                <form method="POST" action="{{ route('moderator.listings.merge') }}" onsubmit="return confirm('Are you sure you want to merge this duplicate listing? Inquiries, offers, and orders will be transferred and the duplicate deactivated.');" class="space-y-3">
                    @csrf
                    <div>
                        <label class="token-label">Primary Canonical Property (Retained)</label>
                        <select name="primary_property_id" required class="token-field">
                            <option value="">Select primary property...</option>
                            @foreach($allProperties as $prop)
                                <option value="{{ $prop->id }}">#{{ $prop->id }} - {{ $prop->title }} ({{ number_format($prop->price) }} ETB)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="token-label">Duplicate Property (Will be archived)</label>
                        <select name="duplicate_property_id" required class="token-field">
                            <option value="">Select duplicate property to merge...</option>
                            @foreach($allProperties as $prop)
                                <option value="{{ $prop->id }}">#{{ $prop->id }} - {{ $prop->title }} ({{ number_format($prop->price) }} ETB)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full token-btn token-btn-primary">Execute Property Merge</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
