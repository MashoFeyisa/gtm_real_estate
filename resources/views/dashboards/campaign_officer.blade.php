@extends('dashboards.layout')

@section('title', 'Campaign Desk — Promotions & Marketing Review')
@section('portal_name', 'Campaign Desk')
@section('dashboard-title', 'Promotions & Marketing Campaign Review Desk')
@section('dashboard-subtitle', 'Editorial review of promotional campaigns, diaspora investor showcases, open house incentives, and lead distribution.')

@section('sidebar-nav')
    @if (auth()->user()->hasAnyPermission(['campaigns.approve', 'campaigns.manage']))
        <x-sidebar-item href="#review-queue" icon="📣" label="Review Queue" :badge="$stats['under_review']" />
    @endif
    @if (auth()->user()->hasAnyPermission(['campaigns.manage', 'campaigns.approve']))
        <x-sidebar-item href="#published-campaigns" icon="🚀" label="Live Campaigns" :count="$stats['published']" />
    @endif
    @if (auth()->user()->hasAnyPermission(['campaigns.manage', 'campaigns.leads_view', 'leads.manage']))
        <x-sidebar-item href="#leads-queue" icon="🎯" label="Campaign Leads" :count="$stats['total_leads']" />
    @endif
@endsection

@section('dashboard-content')
    <!-- Overview Section (Analytics & Charts) -->
    <div id="overview" data-section="overview" class="section-panel space-y-8">
        {{-- Metric Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="token-stat-card">
                <span class="token-stat-title">Awaiting Review</span>
                <div class="token-stat-value token-stat-warning">{{ $stats['under_review'] }}</div>
                <p class="text-[11px] token-muted mt-1">Submitted by agents</p>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Live &amp; Published</span>
                <div class="token-stat-value token-stat-success">{{ $stats['published'] }}</div>
                <p class="text-[11px] token-muted mt-1">Active in marketplace</p>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Leads &amp; Inquiries</span>
                <div class="token-stat-value token-stat-info">{{ $stats['total_leads'] }}</div>
                <p class="text-[11px] token-muted mt-1">Prospective buyers captured</p>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Total Impressions</span>
                <div class="token-stat-value token-stat-accent">{{ number_format($stats['total_views']) }}</div>
                <p class="text-[11px] token-muted mt-1">{{ number_format($stats['total_clicks']) }} clicks generated</p>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- Section 1: Review Queue --}}
    <section id="review-queue" data-section="review-queue" class="section-panel hidden scroll-mt-6 mb-10 token-card">
        <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-4 mb-6">
            <div>
                <span class="token-badge token-badge-warning uppercase tracking-wider">Queue</span>
                <h2 class="mt-2 text-2xl font-black">Editorial Review Queue</h2>
                <p class="text-xs token-muted mt-0.5">Moderate agent campaign copy, verify property ownership, validate incentives, and approve for publishing.</p>
            </div>
            <span class="text-xs font-bold token-muted">{{ $stats['under_review'] }} pending</span>
        </div>

        @php
            $pendingCampaigns = $campaigns->where('status', 'under_review');
        @endphp

        @if ($pendingCampaigns->isEmpty())
            <div class="rounded-2xl border border-dashed border-[var(--token-border)] p-10 text-center">
                <span class="text-3xl">✨</span>
                <p class="mt-2 text-sm font-bold">Review queue is clear!</p>
                <p class="text-xs token-muted mt-1">All agent promotional campaigns have been reviewed and decided.</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach ($pendingCampaigns as $c)
                    <div class="token-card">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="token-badge token-badge-warning !text-[10px]">
                                        {{ ucfirst(str_replace('_', ' ', $c->campaign_type)) }}
                                    </span>
                                    <span class="text-xs token-muted">Submitted {{ $c->created_at?->diffForHumans() }}</span>
                                    @if ($c->agent)
                                        <span class="text-xs font-semibold">&bull; Agent: {{ $c->agent->name }} ({{ $c->agent->phone }})</span>
                                    @endif
                                </div>
                                <h3 class="text-xl font-black">{{ $c->title }}</h3>
                                <p class="text-xs font-bold token-muted mt-1 italic">"{{ $c->headline }}"</p>
                                <p class="text-xs mt-2 leading-relaxed">{{ $c->description }}</p>

                                @if ($c->marketing_copy)
                                    <div class="mt-3 token-chart-tile text-xs">
                                        <span class="font-bold block mb-1">Marketing Copy:</span>
                                        {{ $c->marketing_copy }}
                                    </div>
                                @endif

                                @if ($c->property)
                                    <div class="mt-3 flex items-center gap-3 text-xs">
                                        <span class="font-bold">Linked Property:</span>
                                        <a href="{{ route('properties.show', $c->property->slug ?? $c->property->id) }}" target="_blank" class="font-bold hover:underline flex items-center gap-1">
                                            <span>🏡</span> {{ $c->property->title }} (ETB {{ number_format($c->property->price) }})
                                        </a>
                                    </div>
                                @endif

                                <div class="mt-3 flex flex-wrap gap-4 text-xs font-semibold">
                                    <span>Target Audience: <strong>{{ $c->target_audience ?? 'General' }}</strong></span>
                                    <span>Budget: <strong>ETB {{ number_format($c->budget, 2) }}</strong></span>
                                    @if ($c->start_date && $c->end_date)
                                        <span>Duration: <strong>{{ $c->start_date->format('M d, Y') }} &ndash; {{ $c->end_date->format('M d, Y') }}</strong></span>
                                    @endif
                                </div>
                            </div>

                            {{-- Review Decision Form --}}
                            <div class="w-full md:w-80 shrink-0 token-chart-tile">
                                <h4 class="text-xs font-black uppercase tracking-wider mb-3">Editorial Decision</h4>
                                <form method="POST" action="{{ route('campaigns.update-status', $c) }}" class="space-y-3">
                                    @csrf
                                    @method('PATCH')

                                    <div>
                                        <label class="token-label">Action *</label>
                                        <select name="status" required class="token-field font-bold">
                                            <option value="approved">Approve Campaign</option>
                                            <option value="published">Publish &amp; Make Live</option>
                                            <option value="rejected">Request Revisions / Reject</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="token-label">Approved Budget (ETB)</label>
                                        <input type="number" step="0.01" name="budget" value="{{ $c->budget }}" class="token-field font-medium">
                                    </div>

                                    <div>
                                        <label class="token-label">Reviewer Feedback Notes</label>
                                        <textarea name="review_notes" rows="2" placeholder="Feedback or conditions for the agent..." class="token-field">{{ $c->review_notes }}</textarea>
                                    </div>

                                    <button type="submit" class="w-full token-btn token-btn-primary cursor-pointer">
                                        Submit Decision &rarr;
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Section 2: All Promotional Campaigns --}}
    <section id="published-campaigns" data-section="published-campaigns" class="section-panel hidden scroll-mt-6 mb-10 token-card">
        <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-4 mb-6">
            <div>
                <span class="token-badge token-badge-success uppercase tracking-wider">Campaigns</span>
                <h2 class="mt-2 text-2xl font-black">All Active &amp; Market Campaigns</h2>
                <p class="text-xs token-muted mt-0.5">Performance statistics, view impressions, lead generation, and lifecycle statuses.</p>
            </div>
            <span class="text-xs font-bold token-muted">{{ $campaigns->count() }} total</span>
        </div>

        <div class="token-table-wrap">
            <table class="token-table">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Type</th>
                        <th>Agent</th>
                        <th>Property</th>
                        <th>Budget / Spent</th>
                        <th>Performance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($campaigns as $campaign)
                        @php
                            [$badgeLabel, $badgeClasses] = $campaign->status_badge;
                        @endphp
                        <tr>
                            <td>
                                <span class="font-bold block">{{ $campaign->title }}</span>
                                <span class="text-[11px] token-muted italic truncate block max-w-xs">"{{ $campaign->headline }}"</span>
                            </td>
                            <td>
                                <span class="token-badge token-badge-neutral !text-[10px]">
                                    {{ ucfirst(str_replace('_', ' ', $campaign->campaign_type)) }}
                                </span>
                            </td>
                            <td class="font-medium">
                                {{ $campaign->agent?->name ?? 'Direct' }}
                            </td>
                            <td>
                                @if ($campaign->property)
                                    <a href="{{ route('properties.show', $campaign->property->slug ?? $campaign->property->id) }}" target="_blank" class="font-medium hover:underline hover:text-[var(--token-primary)]">
                                        {{ Str::limit($campaign->property->title, 25) }}
                                    </a>
                                @else
                                    <span class="token-muted">&mdash;</span>
                                @endif
                            </td>
                            <td class="font-mono">
                                <span class="font-bold">ETB {{ number_format($campaign->budget) }}</span>
                                <span class="block text-[10px] token-muted">Spent: ETB {{ number_format($campaign->spent) }}</span>
                            </td>
                            <td class="font-mono">
                                <span class="font-bold text-[var(--token-accent-stat)]">{{ number_format($campaign->views_count) }} views</span>
                                <span class="block text-[10px] text-[var(--token-info)]">{{ $campaign->leads->count() }} leads &middot; {{ $campaign->clicks_count }} clicks</span>
                            </td>
                            <td>
                                <span class="token-badge {{ $badgeClasses }}">
                                    {{ $badgeLabel }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('campaigns.update-status', $campaign) }}" class="flex items-center gap-1.5">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="token-field font-semibold">
                                        <option value="published" {{ $campaign->status === 'published' ? 'selected' : '' }}>Live</option>
                                        <option value="paused" {{ $campaign->status === 'paused' ? 'selected' : '' }}>Pause</option>
                                        <option value="completed" {{ $campaign->status === 'completed' ? 'selected' : '' }}>Complete</option>
                                        <option value="rejected" {{ $campaign->status === 'rejected' ? 'selected' : '' }}>Reject</option>
                                    </select>
                                    <button type="submit" class="token-btn token-btn-primary !px-2 !py-1 !text-[10px] cursor-pointer">Set</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center token-muted">No campaigns recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Section 3: Campaign Leads & Inquiries --}}
    <section id="leads-queue" data-section="leads-queue" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-4 mb-6">
            <div>
                <span class="token-badge token-badge-info uppercase tracking-wider">Leads</span>
                <h2 class="mt-2 text-2xl font-black">Prospective Client Leads</h2>
                <p class="text-xs token-muted mt-0.5">Inquiries generated by marketing campaigns and automatically connected with listing agents.</p>
            </div>
            <span class="text-xs font-bold token-muted">{{ $leads->count() }} leads logged</span>
        </div>

        @if ($leads->isEmpty())
            <div class="rounded-2xl border border-dashed border-[var(--token-border)] p-8 text-center text-xs token-muted">
                No campaign leads received yet. Once visitors submit inquiries on live promotions, they will appear here.
            </div>
        @else
            <div class="token-table-wrap">
                <table class="token-table">
                    <thead>
                        <tr>
                            <th>Lead Contact</th>
                            <th>Campaign</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leads as $lead)
                            <tr>
                                <td>
                                    <span class="font-bold block">{{ $lead->name }}</span>
                                    <span class="text-[11px] token-muted block">{{ $lead->email }}</span>
                                    @if ($lead->phone)
                                        <span class="text-[11px] font-mono block">{{ $lead->phone }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-semibold">{{ $lead->campaign?->title ?? 'Direct Campaign' }}</span>
                                </td>
                                <td>
                                    <span class="italic">{{ Str::limit($lead->message ?? 'Interested in promotional offer.', 60) }}</span>
                                </td>
                                <td>
                                    <span class="token-badge token-badge-info uppercase !text-[10px]">
                                        {{ $lead->status ?? 'new' }}
                                    </span>
                                </td>
                                <td class="token-muted">
                                    {{ $lead->created_at?->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
