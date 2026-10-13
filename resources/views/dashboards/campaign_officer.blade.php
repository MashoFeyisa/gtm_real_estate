@extends('dashboards.layout')

@section('title', 'Campaign Desk — Promotions & Marketing Review')
@section('portal_name', 'Campaign Desk')
@section('dashboard-title', 'Promotions & Marketing Campaign Review Desk')
@section('dashboard-subtitle', 'Editorial review of promotional campaigns, diaspora investor showcases, open house incentives, and lead distribution.')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if (auth()->user()->hasAnyPermission(['campaigns.approve', 'campaigns.manage']))
        <a href="#review-queue" data-target-section="review-queue" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📣</span>
                <span>Review Queue</span>
            </span>
            <span class="rounded-full bg-amber-400/20 text-amber-200 border border-amber-300/30 px-2 py-0.5 text-[10px] font-black uppercase">{{ $stats['under_review'] }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['campaigns.manage', 'campaigns.approve']))
        <a href="#published-campaigns" data-target-section="published-campaigns" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🚀</span>
                <span>Live Campaigns</span>
            </span>
            <span class="rounded-full bg-emerald-400/20 text-emerald-200 border border-emerald-300/30 px-2 py-0.5 text-[10px] font-black uppercase">{{ $stats['published'] }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['campaigns.manage', 'campaigns.leads_view', 'leads.manage']))
        <a href="#leads-queue" data-target-section="leads-queue" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🎯</span>
                <span>Campaign Leads</span>
            </span>
            <span class="rounded-full bg-blue-400/20 text-blue-200 border border-blue-300/30 px-2 py-0.5 text-[10px] font-black uppercase">{{ $stats['total_leads'] }}</span>
        </a>
    @endif
@endsection

@section('dashboard-content')
    <!-- Overview Section (Analytics & Charts) -->
    <div id="overview" data-section="overview" class="section-panel space-y-8">
        {{-- Metric Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-amber-200 shadow-xs">
                <span class="text-xs font-bold text-amber-800 uppercase tracking-wider">Awaiting Review</span>
                <div class="text-3xl font-black text-amber-900 mt-1">{{ $stats['under_review'] }}</div>
                <p class="text-[11px] text-amber-700 mt-0.5">Submitted by agents</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-emerald-200 shadow-xs">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Live &amp; Published</span>
                <div class="text-3xl font-black text-emerald-900 mt-1">{{ $stats['published'] }}</div>
                <p class="text-[11px] text-emerald-700 mt-0.5">Active in marketplace</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-blue-200 shadow-xs">
                <span class="text-xs font-bold text-blue-800 uppercase tracking-wider">Leads &amp; Inquiries</span>
                <div class="text-3xl font-black text-blue-900 mt-1">{{ $stats['total_leads'] }}</div>
                <p class="text-[11px] text-blue-700 mt-0.5">Prospective buyers captured</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-purple-200 shadow-xs">
                <span class="text-xs font-bold text-purple-800 uppercase tracking-wider">Total Impressions</span>
                <div class="text-3xl font-black text-purple-900 mt-1">{{ number_format($stats['total_views']) }}</div>
                <p class="text-[11px] text-purple-700 mt-0.5">{{ number_format($stats['total_clicks']) }} clicks generated</p>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- Section 1: Review Queue --}}
    <section id="review-queue" data-section="review-queue" class="section-panel hidden scroll-mt-6 mb-10 rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs md:p-8">
        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4 mb-6">
            <div>
                <span class="rounded-full bg-amber-100 border border-amber-300 px-3 py-1 text-xs font-black uppercase tracking-wider text-amber-900">Queue</span>
                <h2 class="mt-2 text-2xl font-black text-[#1d3c34]">Editorial Review Queue</h2>
                <p class="text-xs text-slate-600 mt-0.5">Moderate agent campaign copy, verify property ownership, validate incentives, and approve for publishing.</p>
            </div>
            <span class="text-xs font-bold text-slate-500">{{ $stats['under_review'] }} pending</span>
        </div>

        @php
            $pendingCampaigns = $campaigns->where('status', 'under_review');
        @endphp

        @if ($pendingCampaigns->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#d9cab3] p-10 text-center">
                <span class="text-3xl">✨</span>
                <p class="mt-2 text-sm font-bold text-[#1d3c34]">Review queue is clear!</p>
                <p class="text-xs text-slate-500 mt-1">All agent promotional campaigns have been reviewed and decided.</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach ($pendingCampaigns as $c)
                    <div class="rounded-2xl border border-amber-200 bg-[#fdfaf5] p-6 shadow-xs">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="rounded-full bg-amber-100 border border-amber-300 px-2.5 py-0.5 text-[10px] font-black uppercase text-amber-800">
                                        {{ ucfirst(str_replace('_', ' ', $c->campaign_type)) }}
                                    </span>
                                    <span class="text-xs text-slate-500">Submitted {{ $c->created_at?->diffForHumans() }}</span>
                                    @if ($c->agent)
                                        <span class="text-xs font-semibold text-[#1d3c34]">&bull; Agent: {{ $c->agent->name }} ({{ $c->agent->phone }})</span>
                                    @endif
                                </div>
                                <h3 class="text-xl font-black text-[#1d3c34]">{{ $c->title }}</h3>
                                <p class="text-xs font-bold text-[#9b6c17] mt-1 italic">"{{ $c->headline }}"</p>
                                <p class="text-xs text-slate-700 mt-2 leading-relaxed">{{ $c->description }}</p>

                                @if ($c->marketing_copy)
                                    <div class="mt-3 rounded-xl border border-[#d9cab3] bg-white p-3 text-xs text-slate-600">
                                        <span class="font-bold text-[#1d3c34] block mb-1">Marketing Copy:</span>
                                        {{ $c->marketing_copy }}
                                    </div>
                                @endif

                                @if ($c->property)
                                    <div class="mt-3 flex items-center gap-3 text-xs">
                                        <span class="font-bold text-slate-600">Linked Property:</span>
                                        <a href="{{ route('properties.show', $c->property->slug ?? $c->property->id) }}" target="_blank" class="font-bold text-[#1d3c34] hover:underline flex items-center gap-1">
                                            <span>🏡</span> {{ $c->property->title }} (ETB {{ number_format($c->property->price) }})
                                        </a>
                                    </div>
                                @endif

                                <div class="mt-3 flex flex-wrap gap-4 text-xs font-semibold text-slate-600">
                                    <span>Target Audience: <strong class="text-[#1d3c34]">{{ $c->target_audience ?? 'General' }}</strong></span>
                                    <span>Budget: <strong class="text-[#1d3c34]">ETB {{ number_format($c->budget, 2) }}</strong></span>
                                    @if ($c->start_date && $c->end_date)
                                        <span>Duration: <strong class="text-[#1d3c34]">{{ $c->start_date->format('M d, Y') }} &ndash; {{ $c->end_date->format('M d, Y') }}</strong></span>
                                    @endif
                                </div>
                            </div>

                            {{-- Review Decision Form --}}
                            <div class="w-full md:w-80 shrink-0 rounded-2xl border border-[#d9cab3] bg-white p-4">
                                <h4 class="text-xs font-black uppercase tracking-wider text-[#1d3c34] mb-3">Editorial Decision</h4>
                                <form method="POST" action="{{ route('campaigns.update-status', $c) }}" class="space-y-3">
                                    @csrf
                                    @method('PATCH')

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Action *</label>
                                        <select name="status" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34] focus:ring-2 focus:ring-[#1d3c34]">
                                            <option value="approved">Approve Campaign</option>
                                            <option value="published">Publish &amp; Make Live</option>
                                            <option value="rejected">Request Revisions / Reject</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Approved Budget (ETB)</label>
                                        <input type="number" step="0.01" name="budget" value="{{ $c->budget }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-[#1d3c34]">
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Reviewer Feedback Notes</label>
                                        <textarea name="review_notes" rows="2" placeholder="Feedback or conditions for the agent..." class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-[#1d3c34]">{{ $c->review_notes }}</textarea>
                                    </div>

                                    <button type="submit" class="w-full rounded-xl bg-[#1d3c34] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-[#275347] shadow-xs cursor-pointer">
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
    <section id="published-campaigns" data-section="published-campaigns" class="section-panel hidden scroll-mt-6 mb-10 rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs md:p-8">
        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4 mb-6">
            <div>
                <span class="rounded-full bg-emerald-100 border border-emerald-300 px-3 py-1 text-xs font-black uppercase tracking-wider text-emerald-900">Campaigns</span>
                <h2 class="mt-2 text-2xl font-black text-[#1d3c34]">All Active &amp; Market Campaigns</h2>
                <p class="text-xs text-slate-600 mt-0.5">Performance statistics, view impressions, lead generation, and lifecycle statuses.</p>
            </div>
            <span class="text-xs font-bold text-slate-500">{{ $campaigns->count() }} total</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-[#f5f0e7] uppercase font-bold text-slate-700 border-b border-[#d9cab3]">
                    <tr>
                        <th class="px-4 py-3">Campaign</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Agent</th>
                        <th class="px-4 py-3">Property</th>
                        <th class="px-4 py-3">Budget / Spent</th>
                        <th class="px-4 py-3">Performance</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca]">
                    @forelse ($campaigns as $campaign)
                        @php
                            [$badgeLabel, $badgeClasses] = $campaign->status_badge;
                        @endphp
                        <tr class="hover:bg-[#fbf9f5] transition">
                            <td class="px-4 py-3">
                                <span class="font-bold text-[#1d3c34] block">{{ $campaign->title }}</span>
                                <span class="text-[11px] text-slate-500 italic truncate block max-w-xs">"{{ $campaign->headline }}"</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700">
                                    {{ ucfirst(str_replace('_', ' ', $campaign->campaign_type)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $campaign->agent?->name ?? 'Direct' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($campaign->property)
                                    <a href="{{ route('properties.show', $campaign->property->slug ?? $campaign->property->id) }}" target="_blank" class="text-slate-800 hover:text-[#1d3c34] font-medium hover:underline">
                                        {{ Str::limit($campaign->property->title, 25) }}
                                    </a>
                                @else
                                    <span class="text-slate-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono">
                                <span class="font-bold text-[#1d3c34]">ETB {{ number_format($campaign->budget) }}</span>
                                <span class="block text-[10px] text-slate-500">Spent: ETB {{ number_format($campaign->spent) }}</span>
                            </td>
                            <td class="px-4 py-3 font-mono">
                                <span class="font-bold text-purple-900">{{ number_format($campaign->views_count) }} views</span>
                                <span class="block text-[10px] text-blue-700">{{ $campaign->leads->count() }} leads &middot; {{ $campaign->clicks_count }} clicks</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-block rounded-full border px-2.5 py-0.5 text-[10px] font-black uppercase {{ $badgeClasses }}">
                                    {{ $badgeLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('campaigns.update-status', $campaign) }}" class="flex items-center gap-1.5">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[11px] font-semibold text-slate-700">
                                        <option value="published" {{ $campaign->status === 'published' ? 'selected' : '' }}>Live</option>
                                        <option value="paused" {{ $campaign->status === 'paused' ? 'selected' : '' }}>Pause</option>
                                        <option value="completed" {{ $campaign->status === 'completed' ? 'selected' : '' }}>Complete</option>
                                        <option value="rejected" {{ $campaign->status === 'rejected' ? 'selected' : '' }}>Reject</option>
                                    </select>
                                    <button type="submit" class="rounded-lg bg-[#1d3c34] px-2.5 py-1 text-[11px] font-bold text-white hover:bg-[#254d43] cursor-pointer">Set</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">No campaigns recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Section 3: Campaign Leads & Inquiries --}}
    <section id="leads-queue" data-section="leads-queue" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs md:p-8">
        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4 mb-6">
            <div>
                <span class="rounded-full bg-blue-100 border border-blue-300 px-3 py-1 text-xs font-black uppercase tracking-wider text-blue-900">Leads</span>
                <h2 class="mt-2 text-2xl font-black text-[#1d3c34]">Prospective Client Leads</h2>
                <p class="text-xs text-slate-600 mt-0.5">Inquiries generated by marketing campaigns and automatically connected with listing agents.</p>
            </div>
            <span class="text-xs font-bold text-slate-500">{{ $leads->count() }} leads logged</span>
        </div>

        @if ($leads->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#d9cab3] p-8 text-center text-xs text-slate-400">
                No campaign leads received yet. Once visitors submit inquiries on live promotions, they will appear here.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-[#f5f0e7] uppercase font-bold text-slate-700 border-b border-[#d9cab3]">
                        <tr>
                            <th class="px-4 py-3">Lead Contact</th>
                            <th class="px-4 py-3">Campaign</th>
                            <th class="px-4 py-3">Message</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Received</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7ddca]">
                        @foreach ($leads as $lead)
                            <tr class="hover:bg-[#fbf9f5] transition">
                                <td class="px-4 py-3">
                                    <span class="font-bold text-[#1d3c34] block">{{ $lead->name }}</span>
                                    <span class="text-[11px] text-slate-500 block">{{ $lead->email }}</span>
                                    @if ($lead->phone)
                                        <span class="text-[11px] font-mono text-slate-600 block">{{ $lead->phone }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-slate-800">{{ $lead->campaign?->title ?? 'Direct Campaign' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-slate-700 italic">{{ Str::limit($lead->message ?? 'Interested in promotional offer.', 60) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800 uppercase">
                                        {{ $lead->status ?? 'new' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500">
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
