@extends('dashboards.layout')

@section('title', $agent->name . ' — Agent Workspace')
@section('portal_name', 'Agent Workspace')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    <a href="#leads-pipeline" data-target-section="leads-pipeline" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>🎯</span><span>{{ 'Leads & Pipeline' }}</span></span>
        @if ($unreadInquiries->isNotEmpty())
            <span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-xs font-bold text-[#9b6c17]">{{ $unreadInquiries->count() }} new</span>
        @endif
    </a>
    <a href="#viewings" data-target-section="viewings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>👁️</span><span>Viewings</span></span>
        @if ($viewings->whereIn('status', ['requested', 'confirmed'])->count() > 0)
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold">{{ $viewings->whereIn('status', ['requested', 'confirmed'])->count() }}</span>
        @endif
    </a>
    <a href="#offers-negotiations" data-target-section="offers-negotiations" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>🤝</span><span>{{ 'Offers & Negotiations' }}</span></span>
        @if ($offers->whereNotIn('status', ['rejected', 'expired', 'converted'])->count() > 0)
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold">{{ $offers->whereNotIn('status', ['rejected', 'expired', 'converted'])->count() }}</span>
        @endif
    </a>
    <a href="#leases" data-target-section="leases" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>🔑</span><span>Lease and Rental</span></span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold">{{ $leases->where('status', 'active')->count() }}</span>
    </a>
    <a href="#listings" data-target-section="listings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>🏠</span><span>My Listings</span></span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold">{{ $propertiesCount }}</span>
    </a>
    <a href="#buy-requests" data-target-section="buy-requests" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>🧾</span><span>Buy Requests</span></span>
        @if ($pendingOrders->isNotEmpty())
            <span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-xs font-bold text-[#9b6c17]">{{ $pendingOrders->count() }} new</span>
        @endif
    </a>
    <a href="#commissions" data-target-section="commissions" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>💰</span><span>Commissions</span></span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold">{{ $commissions->count() }}</span>
    </a>
    <a href="#clients" data-target-section="clients" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>👥</span><span>Clients</span></span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold">{{ $clients->total() + $orders->count() }}</span>
    </a>
    <a href="#messages" data-target-section="messages" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>💬</span><span>Inquiries &amp; Messages</span></span>
        @if ($inquiries->total() > 0)
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold">{{ $inquiries->total() }}</span>
        @endif
    </a>
    <a href="#feedback" data-target-section="feedback" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>⭐</span><span>Reviews</span></span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold">{{ $feedbackCount }}</span>
    </a>
    <a href="#developments" data-target-section="developments" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>🏗️</span><span>Developments</span></span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold">{{ $projectsCount }}</span>
    </a>
    <a href="#stay-bookings" data-target-section="stay-bookings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>📅</span><span>Stay Bookings</span></span>
        @if ($bookings->isNotEmpty())
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold">{{ $bookings->count() }}</span>
        @endif
    </a>
    <a href="#campaigns" data-target-section="campaigns" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">📣 Campaigns</a>
    <a href="#profile" data-target-section="profile" class="nav-section-link flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span>👤</span><span>Public Agent Profile</span>
    </a>
@endsection

@section('content')
<div class="space-y-8">
    {{-- Alerts --}}
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-800 shadow-xs">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-bold text-rose-800 shadow-xs">
            ⚠️ {{ $errors->first() }}
        </div>
    @endif

    {{-- Portal quick actions: agent tier badge, client message bell, profile shortcut --}}
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-[#d9cab3] bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#587165]">Agent Portal</p>
            <span class="rounded-full border border-[#d4af37]/50 bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">
                ★ {{ $agent->tier_details['name'] }} &bull; {{ (int) $agent->effective_split_percentage }}% Split Tier
            </span>
            @if ($agent->license_verified)
                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">✓ ERCA Certified</span>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <div class="relative" id="agent-messages-icon-wrapper">
                <button type="button" id="agent-messages-icon" class="relative flex h-9 w-9 items-center justify-center rounded-full border border-[#cdbb97] bg-white text-[#1d3c34] transition hover:bg-[#f1e4cf]" title="Notifications" aria-label="Notifications" aria-haspopup="true" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if ($unreadInquiries->isNotEmpty())
                        <span id="agent-messages-icon-badge" class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#9b6c17] px-1 text-[10px] font-black text-white shadow-sm">{{ $unreadInquiries->count() }}</span>
                    @endif
                </button>
                <div id="agent-messages-dropdown" class="hidden absolute right-0 top-full z-50 mt-2 w-80 overflow-hidden rounded-2xl border border-[#d9cab3] bg-white shadow-xl">
                    <p class="border-b border-[#f0e8d8] px-3 py-2 text-[10px] font-black uppercase tracking-wider text-[#587165]">Latest Client Messages</p>
                    <div class="max-h-72 overflow-y-auto">
                        @forelse ($inquiries->take(3) as $recentInquiry)
                            <button type="button" class="agent-messages-nav-item block w-full rounded-xl px-3 py-2.5 text-left transition hover:bg-[#f3eadc]">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="truncate text-xs font-black text-[#1d3c34]">{{ $recentInquiry->subject }}</span>
                                    <span class="shrink-0 text-[10px] text-slate-400">{{ $recentInquiry->created_at?->diffForHumans() }}</span>
                                </span>
                                <span class="mt-0.5 block truncate text-[11px] font-semibold text-slate-600">{{ $recentInquiry->name }}</span>
                                <span class="mt-0.5 line-clamp-2 block text-[11px] leading-4 text-slate-600">{{ $recentInquiry->message }}</span>
                            </button>
                        @empty
                            <p class="px-3 py-3 text-xs text-slate-500">No client messages yet.</p>
                        @endforelse
                    </div>
                    <button type="button" class="agent-messages-nav-item block w-full border-t border-[#f0e8d8] px-3 py-2 text-center text-[11px] font-bold uppercase tracking-wider text-[#1d3c34] transition hover:bg-[#f3eadc]">View all messages</button>
                </div>
            </div>

            {{-- Profile Button at top near notification (Clicking displays Edit Profile page) --}}
            <a href="#profile" data-target-section="profile" class="nav-section-link group flex items-center gap-2.5 rounded-full border border-[#cdbb97] bg-white py-1 pl-1.5 pr-3.5 text-xs font-bold text-[#1d3c34] shadow-xs transition hover:bg-[#f1e4cf] hover:border-[#1d3c34]/40 cursor-pointer" title="Edit Profile">
                <img src="{{ $agent->photo_url }}" alt="{{ $agent->name }}" class="h-8 w-8 rounded-full object-cover ring-1 ring-[#1d3c34]/20">
                <div class="flex flex-col text-left">
                    <span class="text-xs font-bold text-[#1d3c34] leading-tight">{{ $agent->name }}</span>
                    <span class="text-[10px] font-semibold text-[#587165] leading-tight">Edit Profile</span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#587165] transition group-hover:text-[#1d3c34]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </a>
        </div>
    </div>

    {{-- 1. OVERVIEW SECTION --}}
    <div id="overview" data-section="overview" class="section-panel space-y-6">
        <div class="rounded-3xl border border-[#d9cab3] bg-gradient-to-r from-[#1d3c34] to-[#2d5d4d] p-6 text-white shadow-sm md:p-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-3 py-1 text-xs font-bold text-[#f8e7b9] uppercase tracking-wider">Agent CRM &bull; Sales Command Center</span>
                    <h1 class="mt-2 text-2xl md:text-3xl font-black text-white">{{ $agent->name }}</h1>
                    <p class="mt-1 text-xs text-slate-200">
                        @if ($agent->license_number)
                            License: <span class="font-mono text-[#f8e7b9]">{{ $agent->license_number }}</span> &bull;
                        @endif
                        Branch: <span class="font-semibold">{{ $agent->branch?->name ?? 'Headquarters' }}</span> &bull;
                        Pipeline Active &bull;
                        Response rate: <span class="font-bold text-emerald-300">{{ $crmStats['response_rate'] ?? 0 }}%</span>
                    </p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-300">Total Settled Commissions</span>
                    <p class="text-2xl font-black text-emerald-300">{{ number_format($agent->total_commission_paid) }} ETB</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 sm:grid-cols-5 gap-3 border-t border-white/20 pt-6 text-center sm:text-left">
                <div>
                    <p class="text-xs text-slate-300 font-semibold">Leads</p>
                    <p class="text-2xl font-black text-white">{{ $crmStats['leads'] ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-300 font-semibold">New Leads</p>
                    <p class="text-2xl font-black text-[#f8e7b9]">{{ $crmStats['new_leads'] ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-300 font-semibold">Active Offers</p>
                    <p class="text-2xl font-black text-emerald-300">{{ $crmStats['active_offers'] ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-300 font-semibold">Closed Deals</p>
                    <p class="text-2xl font-black text-white">{{ $crmStats['closed_deals'] ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-300 font-semibold">Pipeline Velocity</p>
                    <p class="text-2xl font-black text-teal-200">{{ $clients->total() + $orders->count() }}</p>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-white/10 flex flex-wrap items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-emerald-500/20 text-emerald-300 px-2.5 py-0.5 font-bold uppercase text-[10px]">Listing quality</span>
                    <span class="text-slate-200 font-semibold">95% Excellent &bull; High resolution imagery, complete specifications and verified locations</span>
                </div>
            </div>
        </div>

        {{-- Agent Personal Performance Charts --}}
        <div class="mt-8 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-2">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#102b25]"></span>
                        <h2 class="text-xl font-black text-[#1d3c34]">Agent Performance Intelligence</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Personal deal velocity, portfolio status breakdown, and commission earnings pipeline.</p>
                </div>
                <span class="rounded-full bg-[#ebdcc9] px-3 py-1 text-xs font-bold text-[#1d3c34]">
                    ★ {{ $agent->tier_details['name'] }} Tier ({{ (int) $agent->effective_split_percentage }}% Split)
                </span>
            </div>

            <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Chart 1: Agent Property Status Donut -->
                <div class="rounded-2xl border border-slate-100 bg-[#fbf9f5] p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-black uppercase tracking-wider text-[#1d3c34]">My Listing Portfolio Status</h3>
                        <span class="text-[10px] font-bold text-slate-400">Available vs Rented vs Sold</span>
                    </div>
                    <div id="agent-property-status-chart" class="min-h-[260px] flex items-center justify-center"></div>
                </div>

                <!-- Chart 2: Agent Commission Flow -->
                <div class="rounded-2xl border border-slate-100 bg-[#fbf9f5] p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-black uppercase tracking-wider text-[#1d3c34]">Commission Settlements (ETB)</h3>
                        <span class="text-[10px] font-bold text-slate-400">Paid vs Pending</span>
                    </div>
                    <div id="agent-commission-chart" class="min-h-[260px] flex items-center justify-center"></div>
                </div>
            </div>
        </div>


        @include('dashboards.partials.analytics-charts')
    </div>

    <section id="buy-requests" data-section="buy-requests" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-black text-[#1d3c34]">Buy Requests</h2>
                <p class="mt-1 text-sm text-slate-600">Clients who sent a buy or rent request to you. Contact the client, agree on terms, and submit to Admin.</p>
            </div>
            <span class="rounded-full bg-[#f9ebd8] px-4 py-1.5 text-xs font-bold uppercase tracking-[0.14em] text-[#b7842d]">
                {{ $pendingOrders->count() }} pending
            </span>
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($orders as $order)
                <article class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm md:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-black text-[#1d3c34]">
                                @if ($order->property)
                                    <a href="{{ route('properties.show', $order->property->slug) }}" class="transition hover:text-[#2e5a4c]">{{ $order->property->title }}</a>
                                @else
                                    Property removed
                                @endif
                            </h3>
                            <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                                <span class="font-semibold text-[#1d3c34]">{{ $order->name }}</span>
                                <span>·</span>
                                <a href="mailto:{{ $order->email }}" class="inline-flex items-center gap-1 font-semibold text-[#2d5d4d] hover:underline">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    {{ $order->email }}
                                </a>
                                @if ($order->phone)
                                    <span>·</span>
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $order->phone) }}" class="inline-flex items-center gap-1 font-semibold text-[#2d5d4d] hover:underline">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        {{ $order->phone }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] {{ $order->isRental() ? 'bg-[#e7ecf5] text-[#34547d]' : 'bg-[#f4efe7] text-[#1d3c34]' }}">
                                {{ $order->isRental() ? 'For Rent' : 'For Sale' }}
                            </span>
                            @if ($order->offer_amount)
                                <span class="rounded-full bg-[#f4efe7] px-3 py-1 text-sm font-black text-[#1d3c34]">{{ $order->isRental() ? 'Rent: ETB ' : 'Offer: ETB ' }}{{ number_format($order->offer_amount) }}{{ $order->isRental() ? '/mo' : '' }}</span>
                            @endif
                            <span class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] {{ $order->status === 'pending' ? 'bg-[#f9ecd0] text-[#9b6c17]' : ($order->status === 'accepted' ? 'bg-[#dfeee4] text-[#1d3c34]' : 'bg-[#f8ddd9] text-[#a24339]') }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>

                    @if ($order->message)
                        <div class="mt-3 rounded-xl bg-[#f9f4ed] px-4 py-3 text-sm leading-7 text-slate-600">
                            <span class="font-bold text-[#1d3c34]">Client message:</span> {{ $order->message }}
                        </div>
                    @endif

                    @if ($order->agent_note)
                        <p class="mt-3 rounded-xl bg-[#f9f4ed] px-4 py-3 text-sm text-slate-600"><span class="font-bold text-[#1d3c34]">Agreement / Agent note:</span> {{ $order->agent_note }}</p>
                    @endif

                    @if ($order->status === 'accepted')
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-[#dfeee4] bg-[#edf9ee] px-4 py-3">
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#214f3a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-bold text-[#214f3a]">
                                        Agreed with client &amp; request sent to Admin
                                        @if ($order->submitted_to_admin_at)
                                            · {{ $order->submitted_to_admin_at->format('M d, Y H:i') }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-[#4a6b58]">
                                        Admin status: <span class="font-bold uppercase tracking-wider">{{ $order->admin_status ?? 'pending review' }}</span>
                                        @if ($order->admin_note) · Note: {{ $order->admin_note }} @endif
                                    </p>
                                </div>
                            </div>
                            @if ($order->hasAgreement())
                                <a href="{{ route('orders.agreement', $order) }}" class="rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-white transition hover:bg-[#254d43]">
                                    Download Agreement PDF
                                </a>
                            @endif
                        </div>
                    @endif

                    @if ($order->status === 'pending')
                        <div class="mt-4 flex flex-wrap items-end gap-3 border-t border-[#e7ddca] pt-4">
                            <form method="POST" action="{{ route('agent.orders.status', $order) }}" class="flex flex-1 flex-wrap items-end gap-3">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="accepted">
                                <div class="min-w-0 flex-1 sm:w-64">
                                    <label class="mb-1 block text-xs font-semibold text-slate-700">Agreement note for Admin &amp; Client</label>
                                    <input type="text" name="agent_note" placeholder="Agreed terms, price or contract conditions..." class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>
                                <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]">
                                    Agree &amp; Send to Admin
                                </button>
                            </form>
                            <form method="POST" action="{{ route('agent.orders.status', $order) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="rounded-full border border-[#c86b5c] bg-[#fef3f1] px-5 py-2.5 text-sm font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
                                    Reject
                                </button>
                            </form>
                        </div>
                    @endif

                    <div class="mt-4 flex items-center justify-between border-t border-[#f0e8d8] pt-3">
                        <span class="text-xs text-slate-400">Received {{ $order->created_at?->format('M d, Y') }}</span>
                        <form method="POST" action="{{ route('agent.orders.destroy', $order) }}" data-confirm="Are you sure you want to delete this buy/rent request?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1 text-xs font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                Delete Request
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-[1.5rem] border border-dashed border-[#d9cab3] bg-[#fffaf2] p-10 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#f9ebd8] text-[#b7842d]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                    <p class="mt-4 text-sm text-slate-600">No buy requests yet. When a client selects you and submits a request, it appears here.</p>
                </div>
            @endforelse
        </div>

        @if ($orders instanceof \Illuminate\Pagination\LengthAwarePaginator && $orders->hasPages())
            <div class="mt-6 border-t border-[#e7ddca] pt-4">
                {{ $orders->fragment('orders')->links() }}
            </div>
        @endif
    </section>

    {{-- 2. DIRECT CLIENTS CRM SECTION --}}
    <div id="clients" data-section="clients" class="section-panel hidden space-y-6">
        <div class="flex items-center justify-between rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34]">Direct Clients &amp; Buyer Portfolio</h2>
                <p class="text-xs text-slate-500 mt-0.5">Maintain direct buyer contacts, budgets, lead sources, and stage progression.</p>
            </div>
            <button type="button" onclick="document.getElementById('new-client-drawer').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] shadow-xs cursor-pointer">
                + Add Direct Client
            </button>
        </div>

        {{-- Add Client Drawer Form --}}
        <div id="new-client-drawer" class="hidden rounded-3xl border border-[#d4af37] bg-white p-6 shadow-sm">
            <h3 class="text-base font-black text-[#1d3c34] mb-4">Register New Client Contact</h3>
            <form method="POST" action="{{ route('agent.clients.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Client Full Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Dawit Tadesse" class="mt-1 w-full rounded-2xl border border-[#d9cab3] px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Email Address</label>
                        <input type="email" name="email" placeholder="dawit@example.com" class="mt-1 w-full rounded-2xl border border-[#d9cab3] px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Phone Number</label>
                        <input type="text" name="phone" placeholder="+251 91 123 4567" class="mt-1 w-full rounded-2xl border border-[#d9cab3] px-3 py-2 text-xs">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Lead Source</label>
                        <select name="lead_source" class="mt-1 w-full rounded-2xl border border-[#d9cab3] px-3 py-2 text-xs">
                            <option value="portal">Website Portal</option>
                            <option value="walk_in">Walk-in Office</option>
                            <option value="referral">Client Referral</option>
                            <option value="social">Social Media</option>
                            <option value="direct">Direct Call</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Initial Pipeline Stage</label>
                        <select name="stage" class="mt-1 w-full rounded-2xl border border-[#d9cab3] px-3 py-2 text-xs">
                            <option value="new_lead">New Lead</option>
                            <option value="contacted">Contacted</option>
                            <option value="qualified">Qualified</option>
                            <option value="viewing">Viewing Scheduled</option>
                            <option value="negotiation">Negotiation</option>
                            <option value="offer">Formal Offer</option>
                            <option value="closed">Closed Deal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Budget (ETB)</label>
                        <input type="number" step="0.01" min="0" name="budget" placeholder="15000000" class="mt-1 w-full rounded-2xl border border-[#d9cab3] px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Preferred Location</label>
                        <input type="text" name="preferred_location" placeholder="e.g. Bole, CMC, Sarbet" class="mt-1 w-full rounded-2xl border border-[#d9cab3] px-3 py-2 text-xs">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Agent Notes</label>
                    <textarea name="notes" rows="2" placeholder="Looking for 3-bedroom villa with parking space..." class="mt-1 w-full rounded-2xl border border-[#d9cab3] px-3 py-2 text-xs"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('new-client-drawer').classList.add('hidden')" class="rounded-full px-4 py-1.5 text-xs font-bold text-slate-500">Cancel</button>
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-1.5 text-xs font-bold text-white hover:bg-[#254d43]">Save Client</button>
                </div>
            </form>
        </div>

        {{-- Direct Clients Table --}}
        <div class="overflow-x-auto rounded-3xl border border-[#e7ddca] bg-white shadow-sm">
            <table class="min-w-full divide-y divide-[#e7ddca] text-xs">
                <thead>
                    <tr class="bg-[#fbf9f5] text-left uppercase text-[#587165] font-black">
                        <th class="py-3 px-4">Client Name</th>
                        <th class="py-3 px-4">Contact</th>
                        <th class="py-3 px-4">Lead Source</th>
                        <th class="py-3 px-4">Budget / Preferences</th>
                        <th class="py-3 px-4">Pipeline Stage</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca]">
                    @forelse ($clients as $client)
                        <tr class="hover:bg-[#fcfaf7]">
                            <td class="py-3 px-4 font-bold text-[#1d3c34]">
                                {{ $client->name }}
                                @if ($client->notes)
                                    <p class="text-[10px] text-slate-500 font-normal mt-0.5 truncate max-w-xs">{{ $client->notes }}</p>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                <div>{{ $client->phone ?: 'No phone' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $client->email ?: 'No email' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="rounded-full bg-slate-100 text-slate-700 px-2.5 py-0.5 font-bold uppercase text-[9px]">
                                    {{ str_replace('_', ' ', $client->lead_source) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-emerald-800">
                                {{ $client->budget ? number_format($client->budget) . ' ETB' : 'Flexible' }}
                                @if ($client->preferred_location)
                                    <div class="text-[10px] text-slate-500 font-normal">📍 {{ $client->preferred_location }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <form method="POST" action="{{ route('agent.clients.stage', $client) }}" class="inline">
                                    @csrf
                                    <select name="stage" onchange="this.form.submit()" class="rounded-xl border border-[#d9cab3] bg-white py-1 px-2 text-[10px] font-bold text-slate-800">
                                        @foreach (['new_lead' => 'New Lead', 'contacted' => 'Contacted', 'qualified' => 'Qualified', 'viewing' => 'Viewing', 'negotiation' => 'Negotiation', 'offer' => 'Offer', 'closed' => 'Closed Deal'] as $stgKey => $stgLabel)
                                            <option value="{{ $stgKey }}" {{ $client->stage === $stgKey ? 'selected' : '' }}>{{ $stgLabel }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form method="POST" action="{{ route('agent.clients.destroy', $client) }}" data-confirm="Remove this client from CRM?" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-xs">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No direct clients registered yet. Click "Add Direct Client" to begin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($clients->hasPages())
                <div class="p-4 border-t border-[#e7ddca]">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>

        {{-- Deal Buyers & Completed Transactions Table --}}
        @if ($orders->isNotEmpty() || $leases->isNotEmpty())
            <div class="mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
                <h3 class="text-base font-black text-[#1d3c34] mb-1">Closed Deals &amp; Transaction Clients</h3>
                <p class="text-xs text-slate-500 mb-4">Buyers and tenants who closed transactions on your listed properties.</p>
                <div class="overflow-x-auto rounded-2xl border border-[#e7ddca]">
                    <table class="min-w-full divide-y divide-[#e7ddca] text-xs">
                        <thead class="bg-[#fbf9f5] text-left uppercase text-[#587165] font-black">
                            <tr>
                                <th class="py-3 px-4">Client</th>
                                <th class="py-3 px-4">Type</th>
                                <th class="py-3 px-4">Property</th>
                                <th class="py-3 px-4">Deal Value</th>
                                <th class="py-3 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e7ddca]">
                            @foreach ($orders as $order)
                                <tr class="hover:bg-[#fcfaf7]">
                                    <td class="py-3 px-4 font-bold text-[#1d3c34]">
                                        <div>{{ $order->name ?? 'Walk-in client' }}</div>
                                        <div class="text-[10px] text-slate-400 font-normal">{{ $order->email }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-[#587165]">Purchase</td>
                                    <td class="py-3 px-4 text-[#587165]">{{ $order->property->title ?? '—' }}</td>
                                    <td class="py-3 px-4 font-bold text-emerald-800">{{ number_format((float) ($order->offer_amount ?? 0)) }} ETB</td>
                                    <td class="py-3 px-4 font-bold text-[#1d3c34]">{{ ucfirst($order->status ?? 'pending') }}</td>
                                </tr>
                            @endforeach
                            @foreach ($leases as $lease)
                                <tr class="hover:bg-[#fcfaf7]">
                                    <td class="py-3 px-4 font-bold text-[#1d3c34]">{{ $lease->tenant_name }}</td>
                                    <td class="py-3 px-4 text-[#587165]">Tenant</td>
                                    <td class="py-3 px-4 text-[#587165]">{{ $lease->property->title ?? '—' }}</td>
                                    <td class="py-3 px-4 font-bold text-emerald-800">{{ number_format((float) $lease->monthly_rent) }} ETB/mo</td>
                                    <td class="py-3 px-4 font-bold text-[#1d3c34]">{{ ucfirst($lease->status ?? 'active') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- 3. LEADS & PIPELINE FUNNEL SECTION --}}
    <div id="leads-pipeline" data-section="leads-pipeline" class="section-panel hidden space-y-6">
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h2 class="text-xl font-black text-[#1d3c34]">Interactive CRM Pipeline Funnel</h2>
            <p class="text-xs text-slate-500 mt-1">Lead stage progression from intake to transaction closing.</p>

            <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2 text-center text-xs">
                @php
                    $stages = [
                        'new_lead' => ['label' => '1. New Lead', 'color' => 'bg-amber-500'],
                        'contacted' => ['label' => '2. Contacted', 'color' => 'bg-blue-500'],
                        'qualified' => ['label' => '3. Qualified', 'color' => 'bg-indigo-500'],
                        'viewing' => ['label' => '4. Viewing', 'color' => 'bg-purple-500'],
                        'negotiation' => ['label' => '5. Negotiation', 'color' => 'bg-orange-500'],
                        'offer' => ['label' => '6. Offer', 'color' => 'bg-teal-500'],
                        'closed' => ['label' => '7. Closed', 'color' => 'bg-emerald-600'],
                    ];
                @endphp
                @foreach ($stages as $key => $meta)
                    @php
                        $count = $clients->where('stage', $key)->count() + $inquiries->where('status', $key)->count();
                    @endphp
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <div class="h-1.5 w-full rounded-full {{ $meta['color'] }} mb-2"></div>
                        <span class="font-bold text-[10px] text-slate-700 block truncate">{{ $meta['label'] }}</span>
                        <span class="text-lg font-black text-[#1d3c34] mt-1 block">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <section id="listings" data-section="listings" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-black text-[#1d3c34]">My Listings</h2>
                <p class="mt-1 text-sm text-slate-600">Post properties for sale or rent, publish or unpublish them, and mark them as sold or rented.</p>
            </div>
            <button type="button" id="agent-add-listing-toggle" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]" data-label-create="+ Add Property" data-label-close="Close Form">+ Add Property</button>
        </div>
        <p class="mt-2 text-xs font-bold uppercase tracking-[0.14em] text-[#587165]">{{ $propertiesCount }} total · {{ $publishedCount }} live on site</p>
        @php $averageQuality = $properties->count() > 0 ? (int) round(collect($listingQuality)->avg()) : 0; @endphp
        <div class="mt-2 flex items-center gap-2">
            <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-[#587165]">Listing quality</span>
            <div class="h-2 w-28 overflow-hidden rounded-full bg-[#e7ddca]">
                <div class="h-full rounded-full {{ $averageQuality >= 70 ? 'bg-emerald-600' : ($averageQuality >= 40 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $averageQuality }}%"></div>
            </div>
            <span class="text-[11px] font-black text-[#1d3c34]">{{ $averageQuality }}% average completeness</span>
        </div>

        {{-- Edit listing form (revealed when editing a property) --}}
        @if ($propertyToEdit)
            <div id="edit-listing-{{ $propertyToEdit->id }}" class="mt-6 rounded-[1.5rem] border border-[#d9cab3] bg-[#fffaf2] p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider text-white">Editing</span>
                        <h3 class="mt-1 text-xl font-black text-[#1d3c34]">Edit: {{ $propertyToEdit->title }}</h3>
                    </div>
                    <a href="{{ route('agent.portal', ['section' => 'listings']) }}" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-[#f1e4cf]">Cancel</a>
                </div>

                {{-- All other listing actions, available while editing --}}
                <div class="mb-4 flex flex-wrap items-center gap-2 rounded-2xl border border-[#e7ddca] bg-white px-3.5 py-2.5">
                    <span class="mr-1 text-[10px] font-black uppercase tracking-wider text-slate-400">Actions</span>

                    <button type="button" onclick="openPropertyDocumentModal({{ $propertyToEdit->id }}, '{{ addslashes($propertyToEdit->title) }}')" class="rounded-full border border-emerald-600/30 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800 transition hover:bg-emerald-100" title="Upload Title Deed (Carta), Site Plan, or Municipal Documents">
                        + Carta / Docs
                    </button>

                    <form method="POST" action="{{ route('agent.properties.togglePublish', $propertyToEdit) }}">
                        @csrf
                        <button type="submit" class="rounded-full border border-[#b9a98b] bg-white px-3 py-1.5 text-xs font-bold text-[#1d3c34] transition hover:bg-[#f1e4cf]">
                            {{ $propertyToEdit->is_active ? 'Unpublish' : 'Publish' }}
                        </button>
                    </form>

                    @php
                        $editSoldStatus = $propertyToEdit->type === 'rent' ? 'rented' : 'sold';
                        $editMarkLabel = $propertyToEdit->type === 'rent' ? 'Mark Rented' : 'Mark Sold';
                    @endphp
                    <form method="POST" action="{{ route('agent.properties.toggleSold', $propertyToEdit) }}">
                        @csrf
                        <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#f8f3eb] px-3 py-1.5 text-xs font-bold text-[#1d3c34] transition hover:bg-[#f0e4cf]">
                            {{ $propertyToEdit->status === $editSoldStatus ? 'Mark Available' : $editMarkLabel }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('agent.properties.archive', $propertyToEdit) }}">
                        @csrf
                        <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#f9f4ed] px-3 py-1.5 text-xs font-bold text-[#1d3c34] transition hover:bg-[#f0e4cf]" {{ $propertyToEdit->status === 'archived' ? 'disabled' : '' }}>
                            Archive
                        </button>
                    </form>

                    <form method="POST" action="{{ route('agent.properties.destroy', $propertyToEdit) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-3 py-1.5 text-xs font-bold text-[#a24339] transition hover:bg-[#ffe4e1]" data-confirm="Delete this listing?">Delete</button>
                    </form>

                    @if ($propertyToEdit->is_active)
                        <a href="{{ route('properties.show', $propertyToEdit->slug) }}" class="text-xs font-semibold text-[#2d5d4d] hover:underline">View</a>
                    @endif
                </div>

                <form method="POST" action="{{ route('agent.properties.update', $propertyToEdit) }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                        <input type="text" name="title" value="{{ old('title', $propertyToEdit->title) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Price (ETB)</label>
                        <input type="number" name="price" min="0" step="0.01" value="{{ old('price', $propertyToEdit->price) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Type</label>
                        <select name="type" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="sale" @selected(old('type', $propertyToEdit->type) === 'sale')>For Sale</option>
                            <option value="rent" @selected(old('type', $propertyToEdit->type) === 'rent')>For Rent</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Category</label>
                        @php
                            $editCat = old('category', $propertyToEdit->property_category ?? $propertyToEdit->category ?? 'home');
                            $categoryOptions = [
                                'home' => 'Home / House',
                                'villa' => 'Villa',
                                'apartment' => 'Apartment',
                                'condominium' => 'Condominium',
                                'guest_house' => 'Guest House (Short Stay)',
                                'hotel' => 'Hotel (Short Stay)',
                            ];
                        @endphp
                        <select name="category" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            @foreach ($categoryOptions as $catKey => $catLabel)
                                <option value="{{ $catKey }}" @selected($editCat === $catKey)>{{ $catLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Bedrooms</label>
                        <input type="number" name="bedrooms" min="0" value="{{ old('bedrooms', $propertyToEdit->bedrooms) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Bathrooms</label>
                        <input type="number" name="bathrooms" min="0" value="{{ old('bathrooms', $propertyToEdit->bathrooms) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Area (sq ft)</label>
                        <input type="number" name="area" min="0" value="{{ old('area', $propertyToEdit->area) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Address / Street</label>
                        <input type="text" name="address" value="{{ old('address', $propertyToEdit->address) }}" placeholder="e.g. Namibia St, Near Medhanialem Mall" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    {{-- GIS Location & Sub-City (SRS §47–§56) --}}
                    <div class="md:col-span-2 rounded-2xl border border-[#ecd9be] bg-[#fffaf2] p-4.5">
                        <div class="flex items-center justify-between mb-3 border-b border-[#ecd9be] pb-2.5">
                            <div>
                                <h4 class="text-sm font-black text-[#1d3c34]">GIS Geolocation &amp; Sub-City Taxonomy</h4>
                                <p class="text-xs text-slate-500">Ethiopian administrative sub-city, woreda, neighborhood, and GPS mapping coordinates</p>
                            </div>
                            <span class="rounded-full bg-[#102b25]/10 px-2.5 py-0.5 text-[11px] font-mono font-bold text-[#102b25]">
                                SRS §47–§56
                            </span>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-4">
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Sub-City</label>
                                <select name="subcity" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                    <option value="">Select Sub-City...</option>
                                    @foreach (['Bole', 'Kirkos', 'Yeka', 'Nifas Silk-Lafto', 'Lideta', 'Arada', 'Addis Ketema', 'Gulele', 'Kolfe Keranio', 'Akaky Kaliti', 'Lemi Kura'] as $sc)
                                        <option value="{{ $sc }}" {{ old('subcity', $propertyToEdit->subcity) === $sc ? 'selected' : '' }}>{{ $sc }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Woreda</label>
                                <input type="text" name="woreda" value="{{ old('woreda', $propertyToEdit->woreda) }}" placeholder="e.g. Woreda 03" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Neighborhood</label>
                                <input type="text" name="neighborhood" value="{{ old('neighborhood', $propertyToEdit->neighborhood) }}" placeholder="e.g. Medhanialem, CMC, Sarbet" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">City / Region</label>
                                <input type="text" name="city" value="{{ old('city', $propertyToEdit->city ?? 'Addis Ababa') }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            @include('layouts.partials.location-picker', [
                                'pickerId' => 'edit-property-location-map',
                                'latInputId' => 'edit-property-latitude',
                                'lngInputId' => 'edit-property-longitude',
                                'lat' => $propertyToEdit->latitude,
                                'lng' => $propertyToEdit->longitude,
                                'pickerHelp' => 'Drag the pin to the exact plot location, or pick a sub-city to recenter.',
                            ])
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                        <textarea name="description" rows="3" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('description', $propertyToEdit->description) }}</textarea>
                    </div>

                    {{-- Ethiopian Physical Specifications & Utility Infrastructure (Module 1) --}}
                    <div class="md:col-span-2 rounded-2xl border border-[#ecd9be] bg-[#fffaf2] p-4.5">
                        <div class="flex items-center justify-between mb-3 border-b border-[#ecd9be] pb-2.5">
                            <div>
                                <h4 class="text-sm font-black text-[#1d3c34]">Physical Specifications & Utilities</h4>
                                <p class="text-xs text-slate-500">Verified architectural parameters and infrastructure readiness</p>
                            </div>
                            <span class="rounded-full bg-[#102b25]/10 px-2.5 py-0.5 text-[11px] font-mono font-bold text-[#102b25]">
                                Verified Specifications
                            </span>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-4">
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Building Area (sqm)</label>
                                <input type="number" name="building_area" min="0" value="{{ old('building_area', $propertyToEdit->building_area) }}" placeholder="e.g. 400" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Stories / Total Floors</label>
                                <input type="number" name="floors" min="0" value="{{ old('floors', $propertyToEdit->floors ?? 1) }}" placeholder="e.g. 1 for G+0" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Parking Spaces</label>
                                <input type="number" name="parking_spaces" min="0" value="{{ old('parking_spaces', $propertyToEdit->parking_spaces ?? 1) }}" placeholder="e.g. 1" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Asset Condition</label>
                                <select name="condition" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                    <option value="new" {{ old('condition', $propertyToEdit->condition) === 'new' ? 'selected' : '' }}>Brand New / Off-Plan</option>
                                    <option value="excellent" {{ old('condition', $propertyToEdit->condition ?? 'excellent') === 'excellent' ? 'selected' : '' }}>Excellent</option>
                                    <option value="good" {{ old('condition', $propertyToEdit->condition) === 'good' ? 'selected' : '' }}>Good</option>
                                    <option value="renovated" {{ old('condition', $propertyToEdit->condition) === 'renovated' ? 'selected' : '' }}>Renovated</option>
                                    <option value="under_construction" {{ old('condition', $propertyToEdit->condition) === 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-[#ecd9be]">
                            <label class="block text-xs font-black uppercase tracking-wider text-[#587165] mb-2">Ethiopian Utility Readiness</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs font-semibold text-slate-700">
                                <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                    <input type="checkbox" name="has_electricity" value="1" {{ old('has_electricity', $propertyToEdit->has_electricity ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <span>3-Phase / Electric</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                    <input type="checkbox" name="has_water" value="1" {{ old('has_water', $propertyToEdit->has_water ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <span>Municipal Water Line</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                    <input type="checkbox" name="has_water_tank" value="1" {{ old('has_water_tank', $propertyToEdit->has_water_tank ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <span>Water Reservoir Tank</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                    <input type="checkbox" name="has_generator" value="1" {{ old('has_generator', $propertyToEdit->has_generator ?? false) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <span>Backup Standby Generator</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                    <input type="checkbox" name="has_solar" value="1" {{ old('has_solar', $propertyToEdit->has_solar ?? false) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <span>Solar Energy System</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                    <input type="checkbox" name="has_security" value="1" {{ old('has_security', $propertyToEdit->has_security ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <span>24/7 Gated Security</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-semibold text-slate-700">Property Photos & Cover Image</label>
                            <span id="agent-edit-counter-pill" class="text-xs font-bold text-[#1d3c34] bg-[#dfeee4] px-2.5 py-0.5 rounded-full hidden">0 photos</span>
                        </div>
                        <p class="text-xs text-slate-500 mb-2.5">Upload gallery photos for this listing. The primary cover image is displayed on property cards and search results. You can set any photo as the cover by clicking <strong>"★ Set Cover"</strong>, upload a new cover photo, or select from luxury background presets.</p>

                        {{-- Hidden inputs for selected cover image or luxury preset --}}
                        <input type="hidden" name="cover_image_id" id="agent-cover-image-id" value="{{ optional($propertyToEdit->images->firstWhere('image_path', $propertyToEdit->image_path))->id }}">
                        <input type="hidden" name="cover_preset" id="agent-cover-preset" value="">

                        {{-- Active Cover Photo Section --}}
                        <div class="mb-4 rounded-2xl border border-[#d9cab3] bg-gradient-to-r from-[#fdfaf5] via-white to-[#fbf8f3] p-4 shadow-2xs">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#d4af37] text-[#102b25] text-xs font-black shadow-xs">★</span>
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-800">Primary Cover Photo</span>
                                        <span class="text-[11px] text-slate-500 ml-1.5">(Displayed on website cards and banner headers)</span>
                                    </div>
                                </div>
                                <div>
                                    <label for="agent-cover-file" class="cursor-pointer inline-flex items-center gap-1.5 rounded-xl border border-[#1d3c34]/25 bg-white hover:bg-[#1d3c34] hover:text-white px-3 py-1.5 text-xs font-bold text-[#1d3c34] shadow-2xs transition">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                        <span>Upload New Cover Photo</span>
                                    </label>
                                    <input type="file" id="agent-cover-file" name="cover_image" accept="image/png,image/jpeg,image/webp,image/avif" class="sr-only" onchange="previewAgentCoverImage(this)">
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="relative h-20 w-32 sm:h-24 sm:w-40 shrink-0 overflow-hidden rounded-xl border-2 border-[#d4af37] shadow-sm bg-slate-100">
                                    <img id="agent-active-cover-img" src="{{ $propertyToEdit->primary_image_url }}" alt="Cover Photo" class="h-full w-full object-cover">
                                    <span class="absolute top-1 left-1 rounded bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black uppercase text-[#102b25] shadow-xs">★ Cover</span>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800" id="agent-cover-status-text">Active Cover Image</p>
                                    <p class="text-[11px] text-slate-500 mt-1">To change, click <strong>"★ Set Cover"</strong> on any gallery photo below, upload a new cover above, or pick a luxury preset background.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Quick Luxury Background Preset Selector --}}
                        <div class="mb-4 rounded-xl border border-[#e7ddca] bg-[#fdfaf5] p-3.5">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <span>🏙️</span>
                                    <span>Best Luxury Background Presets (1-Click Insert)</span>
                                </span>
                                <span class="text-[11px] text-slate-500">Insert high-definition architecture background when photos are unavailable</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                @php
                                    $presets = [
                                        ['id' => 'hero-skyline', 'name' => 'Skyline Luxury', 'file' => 'images/luxury/hero-skyline.jpg'],
                                        ['id' => 'luxury-towers', 'name' => 'Modern Towers', 'file' => 'images/luxury/luxury-towers.jpg'],
                                        ['id' => 'central-plaza', 'name' => 'Central Plaza', 'file' => 'images/luxury/central-plaza.jpg'],
                                        ['id' => 'panoramic-park', 'name' => 'Panoramic Park', 'file' => 'images/luxury/panoramic-park.jpg'],
                                        ['id' => 'retail-boulevard', 'name' => 'Retail Boulevard', 'file' => 'images/luxury/retail-boulevard.jpg'],
                                    ];
                                @endphp
                                @foreach ($presets as $pr)
                                    <button type="button" onclick="selectAgentPresetBackground('{{ $pr['id'] }}', '{{ asset($pr['file']) }}', {{ $propertyToEdit->id }})" class="group relative rounded-lg overflow-hidden border border-[#d9cab3] hover:border-[#d4af37] aspect-[16/10] text-left transition cursor-pointer shadow-2xs hover:shadow-xs">
                                        <img src="{{ asset($pr['file']) }}" alt="{{ $pr['name'] }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-transparent"></div>
                                        <span class="absolute bottom-1 left-1.5 right-1.5 text-[10px] font-bold text-white truncate drop-shadow">{{ $pr['name'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        
                        <div id="agent-edit-dropzone" class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#d9cab3] bg-white p-5 text-center transition-all hover:border-[#1d3c34] hover:bg-[#dfeee4]/20 cursor-pointer">
                            <input id="agent-edit-images" type="file" name="images[]" multiple accept="image/png,image/jpeg,image/webp,image/avif" class="sr-only">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#1d3c34]/10 text-[#1d3c34] mb-2 pointer-events-none">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700 pointer-events-none"><span class="text-[#1d3c34] underline decoration-2">Click to choose photos</span> or drag & drop here</p>
                            <p class="text-xs text-slate-400 mt-1 pointer-events-none">PNG, JPG, WEBP — select as many photos as you need</p>
                        </div>
                        <div id="agent-edit-images-preview" class="mt-3.5 hidden grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2.5"></div>

                        @if ($propertyToEdit->images->isNotEmpty() || $propertyToEdit->image_path)
                            <div class="mt-4 border-t border-[#e7ddca] pt-3">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-700">Current Gallery Photos ({{ $propertyToEdit->images->count() ?: 1 }})</p>
                                    <span class="text-[11px] text-slate-500">Click <strong>"★ Set Cover"</strong> on any photo to update primary cover</span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2.5" id="agent-existing-images-container">
                                    @forelse ($propertyToEdit->images as $agentPropImg)
                                        @php
                                            $isCover = ($agentPropImg->image_path === $propertyToEdit->image_path) || ($loop->first && ! $propertyToEdit->image_path);
                                        @endphp
                                        <div class="group relative rounded-xl overflow-hidden border-2 {{ $isCover ? 'border-[#d4af37] ring-2 ring-[#d4af37]/30' : 'border-[#d9cab3]' }} bg-white aspect-square shadow-2xs transition-all" id="agent-prop-img-{{ $agentPropImg->id }}" data-property-id="{{ $propertyToEdit->id }}">
                                            <img src="{{ $agentPropImg->image_url }}" alt="Property Photo" class="h-full w-full object-cover">
                                            
                                            {{-- Cover Control Badge / Button --}}
                                            <div class="agent-cover-control absolute top-1 left-1 z-10">
                                                @if ($isCover)
                                                    <span class="agent-cover-badge rounded-md bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black text-[#102b25] uppercase tracking-wider shadow-xs flex items-center gap-1">★ Cover</span>
                                                @else
                                                    <button type="button" onclick="setAgentCoverImage({{ $propertyToEdit->id }}, {{ $agentPropImg->id }}, '{{ $agentPropImg->image_url }}')" title="Set as primary cover photo" class="agent-make-cover-btn rounded-md bg-black/75 hover:bg-[#d4af37] hover:text-[#102b25] text-white px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider transition backdrop-blur-xs cursor-pointer shadow-xs">★ Set Cover</button>
                                                @endif
                                            </div>

                                            {{-- Delete Button --}}
                                            <button type="button" onclick="deleteAgentPropertyImage({{ $propertyToEdit->id }}, {{ $agentPropImg->id }})" title="Remove photo" class="absolute top-1 right-1 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-red-600/90 text-white hover:bg-red-700 transition shadow-xs cursor-pointer">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @empty
                                        @if ($propertyToEdit->image_path)
                                            <div class="relative rounded-xl overflow-hidden border-2 border-[#d4af37] bg-white aspect-square shadow-2xs">
                                                <img src="{{ $propertyToEdit->primary_image_url }}" alt="{{ $propertyToEdit->title }}" class="h-full w-full object-cover">
                                                <span class="absolute top-1 left-1 rounded-md bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black text-[#102b25] uppercase tracking-wider shadow-xs">★ Cover</span>
                                            </div>
                                        @endif
                                    @endforelse
                                </div>
                            </div>
                        @endif
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Status</label>
                        <select name="status" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            @foreach ([
                                'published' => 'Published',
                                'draft' => 'Draft (unpublished)',
                                'available' => 'Available',
                                'sold' => 'Sold',
                                'rented' => 'Rented',
                                'archived' => 'Archived',
                            ] as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" @selected(old('status', $propertyToEdit->status) === $statusValue)>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2 pt-1">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- New listing form (revealed by the + Add Property button) --}}
        <div id="agent-listing-create-panel" class="mt-6 rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm {{ $errors->any() && ! $propertyToEdit ? '' : 'hidden' }}">
            <h3 class="text-lg font-black text-[#1d3c34]">Post a New Property</h3>
            <form method="POST" action="{{ route('agent.properties.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-4 md:grid-cols-2">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Price (ETB)</label>
                    <input type="number" name="price" min="0" step="0.01" value="{{ old('price') }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Type</label>
                    <select name="type" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        <option value="sale" @selected(old('type', 'sale') === 'sale')>For Sale</option>
                        <option value="rent" @selected(old('type') === 'rent')>For Rent</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Category</label>
                    @php
                        $createCat = old('category', 'home');
                        $createCategoryOptions = [
                            'home' => 'Home / House',
                            'villa' => 'Villa',
                            'apartment' => 'Apartment',
                            'condominium' => 'Condominium',
                            'guest_house' => 'Guest House (Short Stay)',
                            'hotel' => 'Hotel (Short Stay)',
                        ];
                    @endphp
                    <select name="category" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        @foreach ($createCategoryOptions as $catKey => $catLabel)
                            <option value="{{ $catKey }}" @selected($createCat === $catKey)>{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Bedrooms</label>
                    <input type="number" name="bedrooms" min="0" value="{{ old('bedrooms', 0) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Bathrooms</label>
                    <input type="number" name="bathrooms" min="0" value="{{ old('bathrooms', 0) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Area (sq ft)</label>
                    <input type="number" name="area" min="0" value="{{ old('area') }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Address / Street</label>
                    <input type="text" name="address" value="{{ old('address') }}" placeholder="e.g. Namibia St, Near Medhanialem Mall" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                </div>

                {{-- GIS Location & Sub-City (SRS §47–§56) --}}
                <div class="md:col-span-2 rounded-2xl border border-[#ecd9be] bg-[#fffaf2] p-4.5">
                    <div class="flex items-center justify-between mb-3 border-b border-[#ecd9be] pb-2.5">
                        <div>
                            <h4 class="text-sm font-black text-[#1d3c34]">GIS Geolocation &amp; Sub-City Taxonomy</h4>
                            <p class="text-xs text-slate-500">Ethiopian administrative sub-city, woreda, neighborhood, and GPS mapping coordinates</p>
                        </div>
                        <span class="rounded-full bg-[#102b25]/10 px-2.5 py-0.5 text-[11px] font-mono font-bold text-[#102b25]">
                            SRS §47–§56
                        </span>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-700">Sub-City</label>
                            <select name="subcity" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                <option value="">Select Sub-City...</option>
                                @foreach (['Bole', 'Kirkos', 'Yeka', 'Nifas Silk-Lafto', 'Lideta', 'Arada', 'Addis Ketema', 'Gulele', 'Kolfe Keranio', 'Akaky Kaliti', 'Lemi Kura'] as $sc)
                                    <option value="{{ $sc }}" {{ old('subcity') === $sc ? 'selected' : '' }}>{{ $sc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-700">Woreda</label>
                            <input type="text" name="woreda" value="{{ old('woreda') }}" placeholder="e.g. Woreda 03" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-700">Neighborhood</label>
                            <input type="text" name="neighborhood" value="{{ old('neighborhood') }}" placeholder="e.g. Medhanialem, CMC, Sarbet" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-700">City / Region</label>
                            <input type="text" name="city" value="{{ old('city', 'Addis Ababa') }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        @include('layouts.partials.location-picker', [
                            'pickerId' => 'create-property-location-map',
                            'latInputId' => 'create-property-latitude',
                            'lngInputId' => 'create-property-longitude',
                            'lat' => old('latitude'),
                            'lng' => old('longitude'),
                            'pickerHelp' => 'Click the map or drag the pin to set the exact plot coordinates.',
                        ])
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                    <textarea name="description" rows="3" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('description') }}</textarea>
                </div>

                {{-- Ethiopian Physical Specifications & Utility Infrastructure (Module 1) --}}
                <div class="md:col-span-2 rounded-2xl border border-[#ecd9be] bg-[#fffaf2] p-4.5">
                    <div class="flex items-center justify-between mb-3 border-b border-[#ecd9be] pb-2.5">
                        <div>
                            <h4 class="text-sm font-black text-[#1d3c34]">Physical Specifications & Utilities</h4>
                            <p class="text-xs text-slate-500">Verified architectural parameters and infrastructure readiness</p>
                        </div>
                        <span class="rounded-full bg-[#102b25]/10 px-2.5 py-0.5 text-[11px] font-mono font-bold text-[#102b25]">
                            Verified Specifications
                        </span>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-700">Building Area (sqm)</label>
                            <input type="number" name="building_area" min="0" value="{{ old('building_area') }}" placeholder="e.g. 400" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-700">Stories / Total Floors</label>
                            <input type="number" name="floors" min="0" value="{{ old('floors', 1) }}" placeholder="e.g. 1 for G+0" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-700">Parking Spaces</label>
                            <input type="number" name="parking_spaces" min="0" value="{{ old('parking_spaces', 1) }}" placeholder="e.g. 1" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-700">Asset Condition</label>
                            <select name="condition" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                <option value="new" {{ old('condition') === 'new' ? 'selected' : '' }}>Brand New / Off-Plan</option>
                                <option value="excellent" {{ old('condition', 'excellent') === 'excellent' ? 'selected' : '' }}>Excellent</option>
                                <option value="good" {{ old('condition') === 'good' ? 'selected' : '' }}>Good</option>
                                <option value="renovated" {{ old('condition') === 'renovated' ? 'selected' : '' }}>Renovated</option>
                                <option value="under_construction" {{ old('condition') === 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-[#ecd9be]">
                        <label class="block text-xs font-black uppercase tracking-wider text-[#587165] mb-2">Ethiopian Utility Readiness</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs font-semibold text-slate-700">
                            <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                <input type="checkbox" name="has_electricity" value="1" {{ old('has_electricity', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                <span>3-Phase / Electric</span>
                            </label>
                            <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                <input type="checkbox" name="has_water" value="1" {{ old('has_water', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                <span>Municipal Water Line</span>
                            </label>
                            <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                <input type="checkbox" name="has_water_tank" value="1" {{ old('has_water_tank', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                <span>Water Reservoir Tank</span>
                            </label>
                            <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                <input type="checkbox" name="has_generator" value="1" {{ old('has_generator', false) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                <span>Backup Standby Generator</span>
                            </label>
                            <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                <input type="checkbox" name="has_solar" value="1" {{ old('has_solar', false) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                <span>Solar Energy System</span>
                            </label>
                            <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                <input type="checkbox" name="has_security" value="1" {{ old('has_security', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                <span>24/7 Gated Security</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-semibold text-slate-700">Property Photos & Cover Image</label>
                        <span id="agent-create-counter-pill" class="text-xs font-bold text-[#1d3c34] bg-[#dfeee4] px-2.5 py-0.5 rounded-full hidden">0 photos</span>
                    </div>
                    <p class="text-xs text-slate-500 mb-2">Upload multiple photos for this listing, or insert one of the luxury background presets below if you don't have custom photos yet.</p>

                    <input type="hidden" name="cover_preset" id="agent-create-cover-preset" value="">

                    {{-- Quick Luxury Background Preset Selector --}}
                    <div class="mb-3 rounded-xl border border-[#e7ddca] bg-[#fdfaf5] p-3">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <span>🏙️</span>
                                <span>Best Luxury Background Presets (1-Click Insert)</span>
                            </span>
                            <span id="agent-create-preset-status" class="text-[11px] text-slate-500">Pick preset if no custom photos</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                            @php
                                $presets = [
                                    ['id' => 'hero-skyline', 'name' => 'Skyline Luxury', 'file' => 'images/luxury/hero-skyline.jpg'],
                                    ['id' => 'luxury-towers', 'name' => 'Modern Towers', 'file' => 'images/luxury/luxury-towers.jpg'],
                                    ['id' => 'central-plaza', 'name' => 'Central Plaza', 'file' => 'images/luxury/central-plaza.jpg'],
                                    ['id' => 'panoramic-park', 'name' => 'Panoramic Park', 'file' => 'images/luxury/panoramic-park.jpg'],
                                    ['id' => 'retail-boulevard', 'name' => 'Retail Boulevard', 'file' => 'images/luxury/retail-boulevard.jpg'],
                                ];
                            @endphp
                            @foreach ($presets as $pr)
                                <button type="button" onclick="selectAgentCreatePreset('{{ $pr['id'] }}', '{{ $pr['name'] }}')" class="group relative rounded-lg overflow-hidden border border-[#d9cab3] hover:border-[#d4af37] aspect-[16/10] text-left transition cursor-pointer shadow-2xs hover:shadow-xs">
                                    <img src="{{ asset($pr['file']) }}" alt="{{ $pr['name'] }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-transparent"></div>
                                    <span class="absolute bottom-1 left-1.5 right-1.5 text-[10px] font-bold text-white truncate drop-shadow">{{ $pr['name'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    
                    <div id="agent-create-dropzone" class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#d9cab3] bg-[#fdfaf5] p-5 text-center transition-all hover:border-[#1d3c34] hover:bg-[#dfeee4]/20 cursor-pointer">
                        <input id="agent-create-images" type="file" name="images[]" multiple accept="image/png,image/jpeg,image/webp,image/avif" class="sr-only">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#1d3c34]/10 text-[#1d3c34] mb-2 pointer-events-none">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-sm font-bold text-slate-700 pointer-events-none"><span class="text-[#1d3c34] underline decoration-2">Click to choose photos</span> or drag & drop here</p>
                        <p class="text-xs text-slate-400 mt-1 pointer-events-none">PNG, JPG, WEBP — select as many photos as you want (up to 50)</p>
                    </div>
                    <div id="agent-create-images-preview" class="mt-3.5 hidden grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2.5"></div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Publish</label>
                    <select name="status" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        <option value="published" @selected(old('status', 'published') === 'published')>Publish immediately</option>
                        <option value="draft" @selected(old('status') === 'draft')>Save as draft</option>
                    </select>
                </div>
                <div class="md:col-span-2 pt-1">
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                        Post Property
                    </button>
                </div>
            </form>
        </div>

        {{-- My listings table (same style as the admin dashboard property list) --}}
        <div class="mt-6 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-[#1d3c34] text-[#f8f3eb]">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Image</th>
                        <th class="px-5 py-4 font-semibold">Title</th>
                        <th class="px-5 py-4 font-semibold">Price</th>
                        <th class="px-5 py-4 font-semibold">Type</th>
                        <th class="px-5 py-4 font-semibold">Details</th>
                        <th class="px-5 py-4 font-semibold">Verification</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($properties as $property)
                        <tr class="border-t border-[#e7ddca] bg-white">
                            <td class="px-5 py-4">
                                <img src="{{ $property->primary_image_url }}" alt="{{ $property->title }}" class="h-12 w-16 rounded-lg object-cover ring-1 ring-[#e7ddca]">
                            </td>
                            <td class="max-w-[220px] px-5 py-4">
                                <p class="truncate font-semibold text-[#1d3c34]">{{ $property->title }}</p>
                                @if ($property->city || $property->address)
                                    <p class="truncate text-xs text-slate-500">{{ $property->city }}{{ $property->city && $property->address ? ' · ' : '' }}{{ $property->address }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">ETB {{ number_format($property->price) }}{{ $property->type === 'rent' ? '/mo' : '' }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $property->type === 'rent' ? 'bg-[#e7ecf5] text-[#34547d]' : 'bg-[#f4efe7] text-[#1d3c34]' }}">
                                    {{ $property->type === 'rent' ? 'Rent' : 'Sale' }}
                                </span>
                                <span class="mt-1 block text-xs text-slate-500">{{ ucfirst($property->property_category ?? 'Property') }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-600">
                                {{ $property->bedrooms }} bed · {{ $property->bathrooms }} bath<br>
                                {{ number_format($property->area) }} sq ft
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-black {{ ($property->verification_level ?? 0) >= 4 ? 'bg-[#102a24] text-[#e8c374]' : (($property->verification_level ?? 0) >= 3 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                                        <svg class="h-3 w-3 {{ ($property->verification_level ?? 0) >= 3 ? 'text-emerald-500' : 'text-slate-400' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/></svg>
                                        L{{ $property->verification_level ?? 0 }}: {{ $property->verification_level_label }}
                                    </span>
                                    @if ($property->title_deed_number)
                                        <span class="text-[11px] font-mono text-slate-500">Carta: {{ $property->title_deed_number }}</span>
                                    @endif
                                    <span class="text-[11px] text-slate-400">{{ $property->documents_count ?? $property->documents->count() }} doc(s)</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full {{ $property->is_active ? 'bg-[#dfeee4] text-[#1d3c34]' : 'bg-[#f9ecd0] text-[#9b6c17]' }} px-2.5 py-1 text-xs font-bold">
                                    {{ $property->is_active ? 'Published' : 'Unpublished' }}
                                </span>
                                @if (in_array($property->status, ['sold', 'rented']))
                                    <span class="mt-1 block rounded-full bg-[#f8ddd9] px-2.5 py-1 text-xs font-bold text-[#a24339]">{{ ucfirst($property->status) }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" onclick="openPropertyDocumentModal({{ $property->id }}, '{{ addslashes($property->title) }}')" class="rounded-full border border-emerald-600/30 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-800 hover:bg-emerald-100 transition" title="Upload Title Deed (Carta), Site Plan, or Municipal Documents">
                                        + Carta / Docs
                                    </button>
                                    <a href="{{ route('agent.portal', ['section' => 'listings', 'edit_property' => $property->id]) }}" class="rounded-full bg-[#1d3c34] px-3 py-2 text-xs font-bold text-white">Edit</a>
                                    <form method="POST" action="{{ route('agent.properties.togglePublish', $property) }}">
                                        @csrf
                                        <button type="submit" class="rounded-full border border-[#b9a98b] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                                            {{ $property->is_active ? 'Unpublish' : 'Publish' }}
                                        </button>
                                    </form>
                                    @php
                                        $soldStatus = $property->type === 'rent' ? 'rented' : 'sold';
                                        $markLabel = $property->type === 'rent' ? 'Mark Rented' : 'Mark Sold';
                                    @endphp
                                    <form method="POST" action="{{ route('agent.properties.toggleSold', $property) }}">
                                        @csrf
                                        <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#f8f3eb] px-3 py-2 text-xs font-bold text-[#1d3c34]">
                                            {{ $property->status === $soldStatus ? 'Mark Available' : $markLabel }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('agent.properties.archive', $property) }}">
                                        @csrf
                                        <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#f9f4ed] px-3 py-2 text-xs font-bold text-[#1d3c34]" {{ $property->status === 'archived' ? 'disabled' : '' }}>
                                            Archive
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('agent.properties.destroy', $property) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-3 py-2 text-xs font-bold text-[#a24339]" data-confirm="Delete this listing?">Delete</button>
                                    </form>
                                    @if ($property->is_active)
                                        <a href="{{ route('properties.show', $property->slug) }}" class="text-xs font-semibold text-[#2d5d4d] hover:underline">View</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-6 text-center text-slate-500">No listings yet. Click “+ Add Property” to post your first one.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($properties instanceof \Illuminate\Pagination\LengthAwarePaginator && $properties->hasPages())
            <div class="mt-6 border-t border-[#e7ddca] pt-4">
                {{ $properties->fragment('listings')->links() }}
            </div>
        @endif
    </section>


    <section id="developments" data-section="developments" class="section-panel hidden space-y-6">
        {{-- Off-plan Developments Managed by Agent --}}
        @if (isset($projects) && $projects->isNotEmpty())
            <div class="mt-8 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
                <h3 class="text-base font-black text-[#1d3c34] mb-1">Developments Under My Management</h3>
                <p class="text-xs text-slate-500 mb-4">Off-plan projects, towers, unit inventory, and developer milestones.</p>
                <div class="overflow-x-auto rounded-2xl border border-[#e7ddca]">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#102b25] text-white">
                            <tr>
                                <th class="px-4 py-3 font-bold uppercase">Project Code</th>
                                <th class="px-4 py-3 font-bold uppercase">Development Name</th>
                                <th class="px-4 py-3 font-bold uppercase">Developer</th>
                                <th class="px-4 py-3 font-bold uppercase">Location</th>
                                <th class="px-4 py-3 font-bold uppercase">Completion</th>
                                <th class="px-4 py-3 font-bold uppercase text-center">Units</th>
                                <th class="px-4 py-3 font-bold uppercase">Starting Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0e4cf] bg-white text-slate-700">
                            @foreach ($projects as $prj)
                                <tr class="hover:bg-[#fffdfa]">
                                    <td class="px-4 py-3 font-mono font-bold text-[#1d3c34]">{{ $prj->project_code }}</td>
                                    <td class="px-4 py-3 font-bold text-[#1d3c34]">
                                        <a href="{{ route('developments.show', $prj->slug) }}" class="hover:underline">{{ $prj->name }}</a>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 font-semibold">{{ $prj->developer_name }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $prj->subcity ?? $prj->city }}</td>
                                    <td class="px-4 py-3 font-bold text-[#1d3c34]">{{ $prj->overall_completion_percentage }}%</td>
                                    <td class="px-4 py-3 text-center font-bold text-[#1d3c34]">{{ $prj->units->count() }}</td>
                                    <td class="px-4 py-3 font-bold text-[#1d3c34]">{{ $prj->formatted_starting_price }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>

    <section id="stay-bookings" data-section="stay-bookings" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#d4af37]">Stay Platform</span>
                    <h2 class="text-2xl font-black text-[#1d3c34]">Stay Bookings on My Listings</h2>
                </div>
                <p class="mt-1 text-sm text-slate-500">Guest-house and short-stay reservations booked against the properties you manage.</p>
            </div>
            <a href="{{ route('stays.index') }}" target="_blank" class="rounded-full border border-[#d9cab3] bg-[#fffaf2] px-4 py-2 text-xs font-bold text-[#1d3c34] transition hover:bg-[#f0e4cf]">
                View Public Stays &rarr;
            </a>
        </div>

        @if ($bookings->isNotEmpty())
            <div class="mt-6 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
                <table class="min-w-full divide-y divide-[#e7ddca] text-left text-sm">
                    <thead class="bg-[#f9f4ed] text-xs font-bold uppercase tracking-wider text-[#60716c]">
                        <tr>
                            <th class="px-5 py-3">Reference</th>
                            <th class="px-5 py-3">Stay / Property</th>
                            <th class="px-5 py-3">Guest Details</th>
                            <th class="px-5 py-3">Dates &amp; Nights</th>
                            <th class="px-5 py-3">Total Amount</th>
                            <th class="px-5 py-3">Payment</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7ddca] bg-white text-xs">
                        @foreach ($bookings as $booking)
                            @php
                                $statusColors = [
                                    'confirmed' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                    'checked_in' => 'bg-blue-100 text-blue-800 border-blue-300',
                                    'completed' => 'bg-purple-100 text-purple-800 border-purple-300',
                                    'cancelled' => 'bg-red-100 text-red-800 border-red-300',
                                    'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
                                ];
                                [$paymentLabel, $paymentClasses] = $booking->payment_status_badge;
                            @endphp
                            <tr class="transition hover:bg-[#fefbf6]">
                                <td class="px-5 py-4">
                                    <p class="font-mono font-bold text-[#1d3c34]">{{ $booking->booking_reference }}</p>
                                    <span class="text-[10px] text-slate-400">Booked {{ $booking->created_at?->format('M d, Y') }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($booking->property)
                                        <a href="{{ route('properties.show', $booking->property->slug) }}" class="block max-w-[200px] truncate font-bold text-[#1d3c34] hover:underline">
                                            {{ $booking->property->title }}
                                        </a>
                                        <span class="text-[11px] text-slate-500">{{ $booking->property->city }}</span>
                                    @else
                                        <span class="text-slate-400">Listing removed</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-800">{{ $booking->guest_name }}</p>
                                    <p class="text-slate-500">{{ $booking->guest_phone }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $booking->guest_email }}</p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <p class="font-semibold text-slate-700">{{ $booking->check_in_date?->format('M d, Y') }} &rarr; {{ $booking->check_out_date?->format('M d, Y') }}</p>
                                    <span class="rounded bg-[#edf2ee] px-2 py-0.5 text-[10px] font-bold text-[#2d5d4d]">{{ $booking->total_nights }} Nights ({{ $booking->guests_count }} {{ $booking->guests_count === 1 ? 'Guest' : 'Guests' }})</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-black text-[#102b25]">
                                    {{ $booking->formatted_total }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $paymentClasses }}">
                                        {{ $paymentLabel }}
                                    </span>
                                    @if ($booking->isPaid())
                                        <a href="{{ route('payments.receipt', $booking->latestPayment()?->transaction_number) }}" class="mt-1 block text-[10px] font-bold text-[#1d3c34] hover:underline">Receipt &rarr;</a>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $statusColors[$booking->status] ?? 'bg-slate-100 text-slate-700 border-slate-300' }}">
                                        {{ str_replace('_', ' ', $booking->status) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <form method="POST" action="{{ route('bookings.update-status', $booking) }}" class="inline-flex items-center">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" onchange="this.form.submit()" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[11px] font-semibold text-slate-700 focus:outline-none">
                                                <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                <option value="checked_in" {{ $booking->status === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                                                <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancel</option>
                                            </select>
                                        </form>
                                        <form method="POST" action="{{ route('bookings.destroy', $booking) }}" data-confirm="Delete this stay booking permanently?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1 text-xs font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($bookings instanceof \Illuminate\Pagination\LengthAwarePaginator && $bookings->hasPages())
                <div class="mt-6 border-t border-[#e7ddca] pt-4">
                    {{ $bookings->fragment('stay-bookings')->links() }}
                </div>
            @endif
        @else
            <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fffdfa] p-10 text-center">
                <span class="text-3xl">🧳</span>
                <h3 class="mt-2 text-base font-bold text-[#1d3c34]">No Stay Bookings Yet</h3>
                <p class="mt-1 text-xs text-slate-500">Guest reservations made through the Airbnb-style stay booking engine on your listings will appear here.</p>
            </div>
        @endif
    </section>


    {{-- Module 4 & 6: shared viewing, offer, lease and commission desks --}}
    @include('properties.partials.viewings-desk')
    @include('properties.partials.offers-desk')
    @include('properties.partials.leases-desk')
    @include('properties.partials.commissions-desk')

    {{-- 8. INQUIRIES & MESSAGES SECTION --}}
    <div id="messages" data-section="messages" class="section-panel hidden space-y-6">
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Client Messages</h2>
            <div class="mt-4 space-y-4">
                @forelse ($inquiries as $inq)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5 text-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-bold text-sm text-[#1d3c34]">{{ $inq->name }}</span>
                                <span class="text-slate-500 ml-2">({{ $inq->email ?? $inq->phone }})</span>
                                @if ($inq->property)
                                    <span class="ml-2 font-semibold text-emerald-800">&bull; {{ $inq->property->title }}</span>
                                @endif
                            </div>
                            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $inq->status === 'responded' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $inq->status }}
                            </span>
                        </div>
                        <div class="rounded-xl bg-white p-3 border border-[#e7ddca] text-slate-700 whitespace-pre-line">
                            {{ $inq->message }}
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <form method="POST" action="{{ route('agent.inquiries.stage', $inq) }}" class="inline flex items-center gap-2">
                                @csrf
                                <label class="text-[10px] font-bold text-slate-500">Stage:</label>
                                <select name="status" onchange="this.form.submit()" class="rounded-xl border border-[#d9cab3] bg-white py-1 px-2 text-[10px] font-bold">
                                    @foreach (['new' => 'New', 'contacted' => 'Contacted', 'qualified' => 'Qualified', 'viewing' => 'Viewing', 'negotiation' => 'Negotiation', 'offer' => 'Offer', 'closed' => 'Closed', 'responded' => 'Responded'] as $sK => $sL)
                                        <option value="{{ $sK }}" {{ $inq->status === $sK ? 'selected' : '' }}>{{ $sL }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('agent.inquiries.destroy', $inq) }}" data-confirm="Are you sure you want to delete this message?" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1.5 text-xs font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
                                        Delete Message
                                    </button>
                                </form>
                                <button type="button" onclick="document.getElementById('reply-drawer-{{ $inq->id }}').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#254d43]">
                                    Reply Thread &rarr;
                                </button>
                            </div>
                        </div>

                        {{-- Reply Drawer --}}
                        <div id="reply-drawer-{{ $inq->id }}" class="hidden pt-3 border-t border-[#e7ddca]">
                            <form method="POST" action="{{ route('agent.inquiries.reply', $inq) }}" class="space-y-2">
                                @csrf
                                <textarea name="reply_message" rows="3" required placeholder="Type professional response to client..." class="w-full rounded-2xl border border-[#d9cab3] bg-white p-3 text-xs"></textarea>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="document.getElementById('reply-drawer-{{ $inq->id }}').classList.add('hidden')" class="rounded-full px-3 py-1 text-xs text-slate-500 font-bold">Cancel</button>
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-4 py-1 text-xs font-bold text-white hover:bg-[#254d43]">Send Reply</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-6 text-center">No messages received yet.</p>
                @endforelse
            </div>
            @if ($inquiries->hasPages())
                <div class="p-4">
                    {{ $inquiries->links() }}
                </div>
            @endif
        </div>
    </div>

    <section id="feedback" data-section="feedback" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-black text-[#1d3c34]">Client Reviews</h2>
                <p class="mt-1 text-sm text-slate-600">Feedback clients left on your public profile. Admin reviews it before it shows on the home page.</p>
            </div>
            <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold text-[#2d5d4d]">
                {{ $feedbackCount > 0 ? number_format($averageRating, 1).' ★ average · '.$feedbackCount.' approved' : 'No rating yet' }}
            </span>
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($feedbacks as $feedback)
                <article class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-bold text-[#1d3c34]">{{ $feedback->name }}</p>
                            <p class="text-xs text-slate-500">{{ $feedback->email }}</p>
                        </div>
                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            <span class="font-bold text-[#9b6c17]">{{ str_repeat('★', $feedback->rating).''.str_repeat('☆', 5 - $feedback->rating) }}</span>
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $feedback->is_approved ? 'bg-[#dfeee4] text-[#1d3c34]' : 'bg-[#f9ecd0] text-[#9b6c17]' }}">
                                {{ $feedback->is_approved ? 'Approved' : 'Pending review' }}
                            </span>
                        </div>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-600">“{{ $feedback->message }}”</p>
                    <p class="mt-2 text-xs text-slate-400">{{ $feedback->created_at?->format('M d, Y') }}</p>
                </article>
            @empty
                <div class="rounded-[1.5rem] border border-dashed border-[#d9cab3] bg-[#fffaf2] p-10 text-center">
                    <p class="text-sm text-slate-600">No client reviews yet. When clients leave feedback on your public profile, it will appear here.</p>
                </div>
            @endforelse
        </div>

        @if ($feedbacks instanceof \Illuminate\Pagination\LengthAwarePaginator && $feedbacks->hasPages())
            <div class="mt-6 border-t border-[#e7ddca] pt-4">
                {{ $feedbacks->fragment('feedback')->links() }}
            </div>
        @endif
    </section>


    <section id="campaigns" data-section="campaigns" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#f0e8d8] pb-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded bg-[#d4af37] px-2.5 py-0.5 text-[11px] font-black uppercase tracking-wider text-[#1d3c34]">Marketing Pipeline</span>
                    <h2 class="text-2xl font-black text-[#1d3c34]">My Promotional Campaigns</h2>
                </div>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">
                    Launch marketing promotions, diaspora investor campaigns, and featured showcases for your properties.
                </p>
            </div>

            <button type="button" onclick="document.getElementById('new-campaign-modal').classList.toggle('hidden')" class="rounded-xl bg-[#1d3c34] hover:bg-[#254d43] px-4 py-2.5 text-xs font-bold text-white transition flex items-center gap-2 cursor-pointer">
                <span>+ Launch New Campaign</span>
            </button>
        </div>

        {{-- Campaign Creation Modal / Collapsible Form --}}
        <div id="new-campaign-modal" class="hidden mt-6 rounded-2xl border border-[#d4af37]/40 bg-[#fdfaf5] p-6 shadow-sm">
            <h3 class="text-base font-black text-[#1d3c34] mb-1">Create Promotional Campaign</h3>
            <p class="text-xs text-slate-500 mb-4">Submitted campaigns are reviewed and approved by the Campaign Desk before going live.</p>

            <form method="POST" action="{{ route('campaigns.store') }}" class="grid gap-4 md:grid-cols-2">
                @csrf
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Campaign Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Exclusive Bole Luxury Penthouse Diaspora Special" class="w-full rounded-xl border border-[#d9cab3] bg-white p-2.5 text-xs text-[#1d3c34] focus:ring-1 focus:ring-[#1d3c34]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Target Property</label>
                    <select name="property_id" class="w-full rounded-xl border border-[#d9cab3] bg-white p-2.5 text-xs text-[#1d3c34]">
                        <option value="">-- Choose from your properties --</option>
                        @foreach ($properties as $prop)
                            <option value="{{ $prop->id }}">{{ $prop->title }} (ETB {{ number_format($prop->price) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Campaign Type *</label>
                    <select name="campaign_type" required class="w-full rounded-xl border border-[#d9cab3] bg-white p-2.5 text-xs text-[#1d3c34]">
                        <option value="featured_listing">Featured Listing Showcase</option>
                        <option value="diaspora_promo">Diaspora Investor Opportunity</option>
                        <option value="seasonal_discount">Seasonal Price Promotion</option>
                        <option value="open_house">Open House Weekend</option>
                        <option value="project_launch">Off-Plan Project Launch</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Target Audience</label>
                    <select name="target_audience" class="w-full rounded-xl border border-[#d9cab3] bg-white p-2.5 text-xs text-[#1d3c34]">
                        <option value="diaspora">Ethiopian Diaspora (US / Europe / Middle East)</option>
                        <option value="luxury">High-Net-Worth Luxury Buyers</option>
                        <option value="first_time_buyers">First-Time Homeowners</option>
                        <option value="investors">Rental Yield Investors</option>
                        <option value="tenants">Executive Expat Tenants</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Campaign Budget (ETB)</label>
                    <input type="number" name="budget" placeholder="e.g. 50000" class="w-full rounded-xl border border-[#d9cab3] bg-white p-2.5 text-xs text-[#1d3c34]">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Promotional Headline *</label>
                    <input type="text" name="headline" required placeholder="e.g. Turnkey Investment with 14% Annual Dollar-Indexed Rental Yield" class="w-full rounded-xl border border-[#d9cab3] bg-white p-2.5 text-xs text-[#1d3c34]">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Description &amp; Key Highlights *</label>
                    <textarea name="description" rows="3" required placeholder="Describe prime features, neighborhood access, title deed security, and financial upside..." class="w-full rounded-xl border border-[#d9cab3] bg-white p-2.5 text-xs text-[#1d3c34]"></textarea>
                </div>

                <div class="md:col-span-2 flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('new-campaign-modal').classList.add('hidden')" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-xl bg-[#1d3c34] hover:bg-[#254d43] px-5 py-2.5 text-xs font-bold text-white transition shadow-sm">
                        Submit Campaign to Review Desk &rarr;
                    </button>
                </div>
            </form>
        </div>

        {{-- Agent Campaigns Table / Cards --}}
        <div class="mt-6 space-y-4">
            @forelse ($campaigns as $campaign)
                @php
                    [$badgeLabel, $badgeClasses] = $campaign->status_badge;
                @endphp
                <div class="rounded-2xl border border-[#d9cab3] bg-white p-5 shadow-2xs hover:shadow-sm transition">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider border {{ $badgeClasses }}">
                                    {{ $badgeLabel }}
                                </span>
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                                    {{ ucfirst(str_replace('_', ' ', $campaign->campaign_type)) }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    {{ $campaign->created_at?->diffForHumans() }}
                                </span>
                            </div>
                            <h3 class="text-base font-black text-[#1d3c34]">{{ $campaign->title }}</h3>
                            <p class="text-xs font-bold text-[#8a5d13] italic">"{{ $campaign->headline }}"</p>
                            @if ($campaign->property)
                                <p class="text-xs text-slate-700">
                                    Promoting: <span class="font-bold text-[#1d3c34]">{{ $campaign->property->title }}</span>
                                </p>
                            @endif
                            @if ($campaign->review_notes)
                                <div class="rounded-xl bg-amber-50 p-2.5 text-xs text-amber-900 border border-amber-200">
                                    <span class="font-bold">Campaign Officer Feedback:</span> {{ $campaign->review_notes }}
                                </div>
                            @endif
                        </div>

                        <div class="shrink-0 text-right space-y-1">
                            <span class="block text-xs font-bold text-slate-600">Performance</span>
                            <span class="block text-sm font-black text-[#1d3c34]">{{ $campaign->views_count }} Views &middot; {{ $campaign->leads->count() }} Leads</span>
                            <span class="block text-[11px] font-semibold text-slate-600">Budget: ETB {{ number_format($campaign->budget, 2) }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-[#d9cab3] bg-[#fffaf2] p-8 text-center">
                    <span class="text-3xl">📣</span>
                    <p class="mt-2 text-xs font-bold text-[#1d3c34]">No campaigns launched yet</p>
                    <p class="mt-1 text-xs text-slate-500">Promote your properties to diaspora investors and local buyers with targeted marketing campaigns.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- 10. PUBLIC AGENT PROFILE SECTION --}}
    <div id="profile" data-section="profile" class="section-panel hidden space-y-6">
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Public Agent Profile Settings</h2>
            <p class="text-xs text-slate-500 mt-1">Configure your public agent directory profile, license verification, spoken languages, and bio.</p>

            <form method="POST" action="{{ route('agent.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Display Name *</label>
                        <input type="text" name="name" value="{{ old('name', $agent->name) }}" required class="mt-1 w-full rounded-2xl border border-[#d9cab3] px-3.5 py-2.5 text-xs font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Official Email *</label>
                        <input type="email" name="email" value="{{ old('email', $agent->email) }}" required class="mt-1 w-full rounded-2xl border border-[#d9cab3] px-3.5 py-2.5 text-xs font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $agent->phone) }}" class="mt-1 w-full rounded-2xl border border-[#d9cab3] px-3.5 py-2.5 text-xs font-medium">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Broker License #</label>
                        <input type="text" name="license_number" value="{{ old('license_number', $agent->license_number) }}" placeholder="e.g. MOT-BRK-2026-AA" class="mt-1 w-full rounded-2xl border border-[#d9cab3] px-3.5 py-2.5 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Phone Privacy</label>
                        <select name="phone_privacy" class="mt-1 w-full rounded-2xl border border-[#d9cab3] px-3.5 py-2.5 text-xs">
                            <option value="show" {{ $agent->phone_privacy === 'show' ? 'selected' : '' }}>Display on Public Listings</option>
                            <option value="hide" {{ $agent->phone_privacy === 'hide' ? 'selected' : '' }}>Hide Phone</option>
                            <option value="messaging_only" {{ $agent->phone_privacy === 'messaging_only' ? 'selected' : '' }}>Direct Messaging Only</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Agent Biography</label>
                    <textarea name="bio" rows="4" placeholder="Dedicated real estate consultant with extensive expertise in prime residential and commercial properties in Addis Ababa..." class="mt-1 w-full rounded-2xl border border-[#d9cab3] p-3 text-xs">{{ old('bio', $agent->bio) }}</textarea>
                </div>

                <div class="flex justify-end pt-4 border-t border-[#e7ddca]">
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white hover:bg-[#254d43] shadow-xs">
                        Save Agent Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        function generateStrongPassword(targetSelector) {
            const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            const lower = 'abcdefghjkmnpqrstuvwxyz';
            const numbers = '23456789';
            const symbols = '!@#$%&*?+';

            let pwd = '';
            pwd += upper[Math.floor(Math.random() * upper.length)];
            pwd += lower[Math.floor(Math.random() * lower.length)];
            pwd += numbers[Math.floor(Math.random() * numbers.length)];
            pwd += symbols[Math.floor(Math.random() * symbols.length)];

            const all = upper + lower + numbers + symbols;
            for (let i = 4; i < 16; i++) {
                pwd += all[Math.floor(Math.random() * all.length)];
            }
            pwd = pwd.split('').sort(() => 0.5 - Math.random()).join('');

            const input = typeof targetSelector === 'string' ? document.querySelector(targetSelector) : targetSelector;
            if (input) {
                input.value = pwd;
                input.type = 'text';
                navigator.clipboard?.writeText(pwd).catch(() => {});
                alert('Generated Strong Password:\n\n' + pwd + '\n\n(Copied to clipboard and filled in the input field)');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const navLinks = document.querySelectorAll('.nav-section-link');
            const panels = document.querySelectorAll('.section-panel');

            function activateSection(sectionName, updateHash = true) {
                panels.forEach(panel => {
                    const visible = panel.dataset.section === sectionName;
                    panel.classList.toggle('hidden', !visible);
                });

                navLinks.forEach(link => {
                    const active = link.dataset.targetSection === sectionName;
                    if (!link.closest('aside')) {
                        link.classList.toggle('ring-2', active);
                        link.classList.toggle('ring-[#1d3c34]', active);
                        link.classList.toggle('bg-[#f1e4cf]', active);
                        return;
                    }
                    link.classList.toggle('bg-white/10', active);
                    link.classList.toggle('text-white', active);
                    link.classList.toggle('text-[#dfeee4]', !active);
                    link.classList.toggle('font-semibold', true);
                });

                if (updateHash && window.location.hash !== '#' + sectionName) {
                    history.replaceState(null, '', '#' + sectionName);
                }

                try {
                    sessionStorage.setItem('dashboard_active_section', sectionName);
                    sessionStorage.setItem('dashboard_active_section_' + window.location.pathname, sectionName);
                } catch (e) {}
            }

            window.activateSection = activateSection;

            navLinks.forEach(link => {
                link.addEventListener('click', function (event) {
                    const target = this.dataset.targetSection;
                    if (!target) {
                        return;
                    }

                    event.preventDefault();
                    activateSection(target, true);
                });
            });

            const params = new URLSearchParams(window.location.search);
            const paramSection = params.get('section');
            const initialHash = window.location.hash.replace('#', '');
            let savedSessionSection = null;
            try {
                savedSessionSection = sessionStorage.getItem('dashboard_active_section_' + window.location.pathname) || sessionStorage.getItem('dashboard_active_section');
            } catch (e) {}

            let initialSection = 'overview';
            if (params.has('edit_property')) {
                initialSection = 'listings';
            } else if (initialHash && document.querySelector('[data-section="' + initialHash + '"]')) {
                initialSection = initialHash;
            } else if (paramSection && document.querySelector('[data-section="' + paramSection + '"]')) {
                initialSection = paramSection;
            } else if (savedSessionSection && document.querySelector('[data-section="' + savedSessionSection + '"]')) {
                initialSection = savedSessionSection;
            }

            if (document.querySelector('[data-section="' + initialSection + '"]')) {
                activateSection(initialSection, !!initialHash);
            }

            window.addEventListener('hashchange', function () {
                const newHash = window.location.hash.replace('#', '');
                if (newHash && document.querySelector('[data-section="' + newHash + '"]')) {
                    activateSection(newHash, false);
                }
            });

            // "+ Add Property" button toggles the create-listing form.
            const addToggle = document.getElementById('agent-add-listing-toggle');
            const addPanel = document.getElementById('agent-listing-create-panel');

            if (addToggle && addPanel) {
                if (!addPanel.classList.contains('hidden')) {
                    addToggle.textContent = addToggle.dataset.labelClose || 'Close Form';
                }

                addToggle.addEventListener('click', function () {
                    const willShow = addPanel.classList.toggle('hidden') === false;
                    addToggle.textContent = willShow ? (addToggle.dataset.labelClose || 'Close Form') : (addToggle.dataset.labelCreate || '+ Add Property');
                });
            }

            // Top-right notification icon: preview the latest client messages, then jump to the messages section.
            const messagesIcon = document.getElementById('agent-messages-icon');
            const messagesDropdown = document.getElementById('agent-messages-dropdown');
            if (messagesIcon && messagesDropdown) {
                const closeMessagesDropdown = function () {
                    messagesDropdown.classList.add('hidden');
                    messagesIcon.setAttribute('aria-expanded', 'false');
                };

                messagesIcon.addEventListener('click', function (event) {
                    event.preventDefault();
                    const isOpen = messagesDropdown.classList.toggle('hidden') === false;
                    messagesIcon.setAttribute('aria-expanded', String(isOpen));
                });

                document.addEventListener('click', function (event) {
                    if (!messagesIcon.contains(event.target) && !messagesDropdown.contains(event.target)) {
                        closeMessagesDropdown();
                    }
                });

                document.querySelectorAll('.agent-messages-nav-item').forEach(function (item) {
                    item.addEventListener('click', function (event) {
                        const navLink = document.querySelector('.nav-section-link[data-target-section="messages"]');
                        if (navLink) {
                            event.preventDefault();
                            closeMessagesDropdown();
                            navLink.click();
                        }
                    });
                });
            }

            // Message notifications: expand on click and mark as read.
            document.querySelectorAll('.message-card').forEach(function (card) {
                const toggle = card.querySelector('.message-toggle');
                const body = card.querySelector('.message-body');
                const badge = card.querySelector('.message-new-badge');
                const chevron = card.querySelector('.message-chevron');

                if (! toggle || ! body) {
                    return;
                }

                toggle.addEventListener('click', function () {
                    const isOpen = ! body.classList.toggle('hidden');
                    chevron.classList.toggle('rotate-180', isOpen);

                    if (isOpen && card.dataset.read === '0') {
                        card.dataset.read = '1';
                        card.classList.remove('border-[#b9a98b]', 'bg-[#fffaf2]');
                        card.classList.add('border-[#d9cab3]', 'bg-white');
                        if (badge) {
                            badge.remove();
                        }

                        const iconBadge = document.getElementById('agent-messages-icon-badge');
                        if (iconBadge) {
                            const remaining = Math.max(0, (parseInt(iconBadge.textContent, 10) || 0) - 1);
                            if (remaining > 0) {
                                iconBadge.textContent = remaining;
                            } else {
                                iconBadge.remove();
                            }
                        }

                        fetch(card.dataset.readUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        }).catch(function () {
                            // Best-effort: the message stays visually read for this session.
                        });
                    }
                });
            });

            function setupMultiPhotoUploader(inputId, dropzoneId, previewId, counterPillId) {
                const input = document.getElementById(inputId);
                const dropzone = document.getElementById(dropzoneId);
                const preview = document.getElementById(previewId);
                const counterPill = counterPillId ? document.getElementById(counterPillId) : null;
                if (!input || !preview) return;

                let dt = new DataTransfer();

                function renderPreviews() {
                    preview.innerHTML = "";
                    const files = Array.from(dt.files);

                    if (counterPill) {
                        if (files.length > 0) {
                            counterPill.textContent = `${files.length} photo${files.length > 1 ? "s" : ""} added`;
                            counterPill.classList.remove("hidden");
                        } else {
                            counterPill.classList.add("hidden");
                        }
                    }

                    if (files.length === 0) {
                        preview.classList.add("hidden");
                        preview.classList.remove("grid");
                        return;
                    }

                    preview.classList.remove("hidden");
                    preview.classList.add("grid");

                    files.forEach((file, idx) => {
                        const card = document.createElement("div");
                        card.className = "group relative rounded-xl overflow-hidden border border-[#d9cab3] bg-white aspect-square shadow-2xs transition-all hover:shadow-md";

                        const isCover = idx === 0;
                        const sizeMb = (file.size / (1024 * 1024)).toFixed(1);

                        const img = document.createElement("img");
                        img.className = "h-full w-full object-cover";
                        img.src = URL.createObjectURL(file);

                        const badge = document.createElement("span");
                        badge.className = isCover
                            ? "absolute top-1 left-1 rounded bg-[#102b25]/90 px-1.5 py-0.5 text-[9px] font-bold text-[#e7d8b7] uppercase tracking-wider shadow-xs"
                            : "absolute top-1 left-1 rounded bg-black/70 px-1.5 py-0.5 text-[9px] font-bold text-white";
                        badge.textContent = isCover ? "Cover" : `#${idx + 1}`;

                        const sizeBadge = document.createElement("span");
                        sizeBadge.className = "absolute bottom-1 left-1 rounded bg-black/60 px-1 py-0.5 text-[8px] font-medium text-white/90 backdrop-blur-xs";
                        sizeBadge.textContent = `${sizeMb} MB`;

                        const removeBtn = document.createElement("button");
                        removeBtn.type = "button";
                        removeBtn.title = "Remove this photo from upload batch";
                        removeBtn.className = "absolute top-1 right-1 flex h-6 w-6 items-center justify-center rounded-full bg-red-600/90 text-white hover:bg-red-700 transition shadow-xs cursor-pointer";
                        removeBtn.innerHTML = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
                        removeBtn.addEventListener("click", (e) => {
                            e.stopPropagation();
                            e.preventDefault();
                            const newDt = new DataTransfer();
                            Array.from(dt.files).forEach((f, i) => {
                                if (i !== idx) newDt.items.add(f);
                            });
                            dt = newDt;
                            input.files = dt.files;
                            renderPreviews();
                        });

                        card.appendChild(img);
                        card.appendChild(badge);
                        card.appendChild(sizeBadge);
                        card.appendChild(removeBtn);
                        preview.appendChild(card);
                    });
                }

                input.addEventListener("change", function () {
                    Array.from(this.files || []).forEach(file => {
                        if (file.type.startsWith("image/")) {
                            dt.items.add(file);
                        }
                    });
                    this.files = dt.files;
                    renderPreviews();
                });

                if (dropzone) {
                    dropzone.addEventListener("click", (e) => {
                        if (e.target !== input) {
                            input.click();
                        }
                    });

                    ["dragenter", "dragover"].forEach(name => {
                        dropzone.addEventListener(name, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            dropzone.classList.add("border-[#1d3c34]", "bg-[#dfeee4]/40");
                        });
                    });

                    ["dragleave", "drop"].forEach(name => {
                        dropzone.addEventListener(name, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            dropzone.classList.remove("border-[#1d3c34]", "bg-[#dfeee4]/40");
                        });
                    });

                    dropzone.addEventListener("drop", (e) => {
                        if (e.dataTransfer && e.dataTransfer.files) {
                            Array.from(e.dataTransfer.files).forEach(file => {
                                if (file.type.startsWith("image/")) {
                                    dt.items.add(file);
                                }
                            });
                            input.files = dt.files;
                            renderPreviews();
                        }
                    });
                }
            }

            setupMultiPhotoUploader("agent-create-images", "agent-create-dropzone", "agent-create-images-preview", "agent-create-counter-pill");
            setupMultiPhotoUploader("agent-edit-images", "agent-edit-dropzone", "agent-edit-images-preview", "agent-edit-counter-pill");

            window.deleteAgentPropertyImage = function (propertyId, imageId) {
                if (!confirm('Remove this photo from your listing?')) return;
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;

                fetch(`/agent-portal/properties/${propertyId}/images/${imageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.ok) {
                        document.getElementById(`agent-prop-img-${imageId}`)?.remove();
                    } else {
                        alert('Could not remove photo.');
                    }
                })
                .catch(() => alert('Network error while removing photo.'));
            };

            window.setAgentCoverImage = function (propertyId, imageId, imageUrl) {
                const hiddenId = document.getElementById('agent-cover-image-id');
                if (hiddenId) hiddenId.value = imageId;

                const hiddenPreset = document.getElementById('agent-cover-preset');
                if (hiddenPreset) hiddenPreset.value = '';

                const activeCoverImg = document.getElementById('agent-active-cover-img');
                if (activeCoverImg && imageUrl) activeCoverImg.src = imageUrl;

                const statusText = document.getElementById('agent-cover-status-text');
                if (statusText) statusText.textContent = 'Active cover photo updated';

                updateAgentThumbnailsCoverState(imageId);

                if (propertyId) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;
                    fetch(`/agent-portal/properties/${propertyId}/images/${imageId}/cover`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    }).then(res => res.json()).then(data => {
                        if (data.ok) showAgentToastNotification('Cover photo updated successfully!');
                    }).catch(err => console.log('Cover updated locally', err));
                }
            };

            window.updateAgentThumbnailsCoverState = function (activeImageId) {
                const container = document.getElementById('agent-existing-images-container');
                if (!container) return;

                const cards = container.querySelectorAll('[id^="agent-prop-img-"]');
                cards.forEach(card => {
                    const id = card.id.replace('agent-prop-img-', '');
                    const controlDiv = card.querySelector('.agent-cover-control');
                    if (!controlDiv) return;

                    const propertyId = card.getAttribute('data-property-id') || '';

                    if (id == activeImageId) {
                        card.classList.remove('border-[#d9cab3]');
                        card.classList.add('border-[#d4af37]', 'ring-2', 'ring-[#d4af37]/30');
                        controlDiv.innerHTML = '<span class="agent-cover-badge rounded-md bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black text-[#102b25] uppercase tracking-wider shadow-xs flex items-center gap-1">★ Cover</span>';
                    } else {
                        card.classList.remove('border-[#d4af37]', 'ring-2', 'ring-[#d4af37]/30');
                        card.classList.add('border-[#d9cab3]');
                        const img = card.querySelector('img');
                        const src = img ? img.src : '';
                        controlDiv.innerHTML = `<button type="button" onclick="setAgentCoverImage(${propertyId}, ${id}, '${src}')" title="Set as primary cover photo" class="agent-make-cover-btn rounded-md bg-black/75 hover:bg-[#d4af37] hover:text-[#102b25] text-white px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider transition backdrop-blur-xs cursor-pointer shadow-xs">★ Set Cover</button>`;
                    }
                });
            };

            window.selectAgentPresetBackground = function (presetId, imageUrl, propertyId) {
                const hiddenPreset = document.getElementById('agent-cover-preset');
                if (hiddenPreset) hiddenPreset.value = presetId;

                const hiddenId = document.getElementById('agent-cover-image-id');
                if (hiddenId) hiddenId.value = '';

                const activeCoverImg = document.getElementById('agent-active-cover-img');
                if (activeCoverImg) activeCoverImg.src = imageUrl;

                const statusText = document.getElementById('agent-cover-status-text');
                if (statusText) statusText.textContent = `Selected luxury preset: ${presetId}`;

                if (propertyId) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;
                    fetch(`/agent-portal/properties/${propertyId}/preset-cover`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ preset: presetId })
                    }).then(res => res.json()).then(data => {
                        if (data.ok) showAgentToastNotification('Luxury background preset set as cover!');
                    }).catch(err => console.log('Preset chosen', err));
                } else {
                    showAgentToastNotification('Luxury background preset selected!');
                }
            };

            window.selectAgentCreatePreset = function (presetId, presetName) {
                const hiddenPreset = document.getElementById('agent-create-cover-preset');
                if (hiddenPreset) hiddenPreset.value = presetId;

                const statusEl = document.getElementById('agent-create-preset-status');
                if (statusEl) {
                    statusEl.textContent = `✓ Selected: ${presetName}`;
                    statusEl.className = 'text-[11px] font-bold text-[#1d3c34]';
                }
                showAgentToastNotification(`Preset "${presetName}" selected as initial cover!`);
            };

            window.previewAgentCoverImage = function (input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const activeCoverImg = document.getElementById('agent-active-cover-img');
                        if (activeCoverImg) activeCoverImg.src = e.target.result;
                        const statusText = document.getElementById('agent-cover-status-text');
                        if (statusText) statusText.textContent = 'New cover photo file selected (will save with form)';
                        showAgentToastNotification('New cover photo selected!');
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            };

            function showAgentToastNotification(message) {
                let toast = document.getElementById('global-agent-toast');
                if (!toast) {
                    toast = document.createElement('div');
                    toast.id = 'global-agent-toast';
                    toast.className = 'fixed bottom-5 right-5 z-[9999] flex items-center gap-2 rounded-xl bg-[#102b25] text-[#e7d8b7] px-4 py-3 shadow-2xl border border-[#d4af37]/40 text-sm font-semibold transition-all duration-300 opacity-0 translate-y-2 pointer-events-none';
                    document.body.appendChild(toast);
                }
                toast.innerHTML = `<span class="text-[#d4af37]">★</span> <span>${message}</span>`;
                toast.classList.remove('opacity-0', 'translate-y-2');
                toast.classList.add('opacity-100', 'translate-y-0');
                setTimeout(() => {
                    toast.classList.remove('opacity-100', 'translate-y-0');
                    toast.classList.add('opacity-0', 'translate-y-2');
                }, 2800);
            }

            // Agent Portal Overview Charts
            const agentChartData = @json($agentCharts ?? null);
            if (agentChartData && typeof ApexCharts !== 'undefined') {
                // 1. Agent Property Status Donut
                const propStatusEl = document.querySelector("#agent-property-status-chart");
                if (propStatusEl && agentChartData.property_status) {
                    if (agentChartData.property_status.has_data) {
                        new ApexCharts(propStatusEl, {
                            chart: { type: 'donut', height: 260, fontFamily: 'inherit' },
                            series: agentChartData.property_status.series || [],
                            labels: agentChartData.property_status.labels || [],
                            colors: ['#1d3c34', '#3b82f6', '#d4af37', '#94a3b8'],
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '65%',
                                        labels: {
                                            show: true,
                                            total: { show: true, label: 'Listings', formatter: () => agentChartData.property_status.total || 0 }
                                        }
                                    }
                                }
                            },
                            legend: { position: 'bottom', fontSize: '11px' }
                        }).render();
                    } else {
                        propStatusEl.innerHTML = '<div class="text-center p-6 text-slate-400 text-xs font-semibold">No property listings found. Post a listing to view analytics.</div>';
                    }
                }

                // 2. Agent Commission Flow Donut
                const commEl = document.querySelector("#agent-commission-chart");
                if (commEl && agentChartData.commission_breakdown) {
                    if (agentChartData.commission_breakdown.has_data) {
                        new ApexCharts(commEl, {
                            chart: { type: 'donut', height: 260, fontFamily: 'inherit' },
                            series: agentChartData.commission_breakdown.series || [],
                            labels: agentChartData.commission_breakdown.labels || [],
                            colors: ['#1d3c34', '#d4af37'],
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '65%',
                                        labels: {
                                            show: true,
                                            total: {
                                                show: true,
                                                label: 'Total Comm.',
                                                formatter: () => new Intl.NumberFormat('en-US', { notation: 'compact' }).format(agentChartData.commission_breakdown.total || 0) + ' ETB'
                                            }
                                        }
                                    }
                                }
                            },
                            legend: { position: 'bottom', fontSize: '11px' }
                        }).render();
                    } else {
                        commEl.innerHTML = '<div class="text-center p-6 text-slate-400 text-xs font-semibold">No commissions recorded yet. Close your first deal to see earnings!</div>';
                    }
                }
            }
        });
    </script>

    @include('layouts.partials.confirm-modal')
    @include('properties.partials.upload-document-modal')
@endsection
