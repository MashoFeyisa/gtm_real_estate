@extends('dashboards.layout')

@section('portal_name', 'Moderation Desk')
@section('dashboard-title', 'Content Moderation & Listing Quality Desk')
@section('dashboard-subtitle', 'Listing review queues, fraud & fake reports, review sentiment moderation, and duplicate detection.')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if (auth()->user()->hasAnyPermission(['listings.review', 'moderation.manage']))
        <a href="#listings" data-target-section="listings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📋</span>
                <span>Listings Review</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['pending_listings'] }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['reports.resolve', 'reports.view', 'moderation.manage']))
        <a href="#reports" data-target-section="reports" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🚨</span>
                <span>Fraud & Safety Reports</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['pending_reports'] }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['reviews.moderate', 'moderation.manage']))
        <a href="#reviews" data-target-section="reviews" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>💬</span>
                <span>Review Moderation</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['hidden_reviews'] }}</span>
        </a>
    @endif
@endsection

@section('dashboard-content')
    <!-- Overview Section (Analytics & Charts) -->
    <div id="overview" data-section="overview" class="section-panel space-y-8">
        <!-- Metric Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Pending Listings</span>
                <div class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending_listings'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Pending Reports</span>
                <div class="text-2xl font-bold text-rose-600 mt-1">{{ $stats['pending_reports'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Published Listings</span>
                <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['published_listings'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Hidden Reviews</span>
                <div class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['hidden_reviews'] }}</div>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    <!-- Section 1: Listings Moderation Queue -->
    <div id="listings" data-section="listings" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Listing Review & Quality Queue</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="p-3">Listing Code</th>
                        <th class="p-3">Title & Property</th>
                        <th class="p-3">Type</th>
                        <th class="p-3">Price (ETB)</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Moderation Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($listingsQueue as $listing)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                            <td class="p-3 font-mono text-slate-900 dark:text-white font-semibold">{{ $listing->listing_code }}</td>
                            <td class="p-3">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $listing->title }}</div>
                                <div class="text-[10px] text-slate-400">{{ $listing->property->city ?? 'Addis Ababa' }} | Quality Score: {{ $listing->quality_score ?? 85 }}/100</div>
                            </td>
                            <td class="p-3 uppercase text-[10px] font-semibold">{{ $listing->listing_type }}</td>
                            <td class="p-3 font-semibold">{{ number_format($listing->price) }} ETB</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                    @if($listing->status === 'published') bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                                    @elseif($listing->status === 'rejected') bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300
                                    @elseif($listing->status === 'pending_review') bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300
                                    @else bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300 @endif">
                                    {{ strtoupper(str_replace('_', ' ', $listing->status)) }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <form method="POST" action="{{ route('moderator.listings.decision', $listing) }}" class="inline-flex items-center gap-1.5">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" name="status" value="published" class="px-2.5 py-1 bg-emerald-600 text-white rounded text-[11px] font-medium hover:bg-emerald-700">Publish</button>
                                    <button type="submit" name="status" value="approved" class="px-2.5 py-1 bg-blue-600 text-white rounded text-[11px] font-medium hover:bg-blue-700">Approve</button>
                                    <button type="submit" name="status" value="rejected" class="px-2.5 py-1 bg-rose-600 text-white rounded text-[11px] font-medium hover:bg-rose-700">Reject</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-400">No listings currently in moderation queue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Reports & Incident Triage -->
    <div id="reports" data-section="reports" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Fraud, Scam & Duplicate Reports</h3>
        <div class="space-y-3">
            @forelse($reportsQueue as $report)
                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-xs text-slate-900 dark:text-white">Report #{{ $report->id }} - {{ $report->subject ?? 'Listing Issue' }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                            @if($report->status === 'resolved') bg-emerald-100 text-emerald-800
                            @elseif($report->status === 'investigating') bg-blue-100 text-blue-800
                            @else bg-rose-100 text-rose-800 @endif">
                            {{ $report->status }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mb-3">{{ $report->description ?? $report->reason }}</p>
                    <form method="POST" action="{{ route('moderator.reports.decision', $report) }}" class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <input type="text" name="resolution_notes" placeholder="Resolution notes..." class="text-[11px] px-2 py-1 rounded border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex-1">
                        <button type="submit" name="status" value="resolved" class="px-2.5 py-1 bg-emerald-600 text-white rounded text-[11px]">Resolve</button>
                        <button type="submit" name="status" value="dismissed" class="px-2.5 py-1 bg-slate-500 text-white rounded text-[11px]">Dismiss</button>
                    </form>
                </div>
            @empty
                <div class="text-slate-400 text-xs py-4 text-center">No reported listings.</div>
            @endforelse
        </div>
    </div>

    <!-- Section 3: Review Moderation -->
    <div id="reviews" data-section="reviews" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">User Review Moderation</h3>
        <div class="space-y-3">
            @forelse($reviewsQueue as $review)
                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold text-xs text-slate-900 dark:text-white">{{ $review->review_title ?? 'Property Review' }}</span>
                        <span class="text-xs text-amber-500 font-bold">★ {{ $review->rating }}/5</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mb-3 italic">"{{ $review->comment }}"</p>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] text-slate-400">By {{ $review->user->name ?? 'Guest' }}</span>
                        <form method="POST" action="{{ route('moderator.reviews.decision', $review) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            @if($review->status === 'hidden')
                                <button type="submit" name="status" value="published" class="px-2.5 py-1 bg-emerald-600 text-white rounded text-[11px]">Restore</button>
                            @else
                                <button type="submit" name="status" value="hidden" class="px-2.5 py-1 bg-rose-600 text-white rounded text-[11px]">Hide / Censor</button>
                            @endif
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-slate-400 text-xs py-4 text-center">No reviews submitted yet.</div>
            @endforelse
        </div>
    </div>
@endsection
