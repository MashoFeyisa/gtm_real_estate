<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GTM Real Estate Company - Executive Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
    @include('layouts.partials.theme-styles')
</head>
<body class="bg-[#f5f0e7] text-slate-800 antialiased font-sans">
    @include('layouts.partials.impersonation-banner')
    <div class="flex min-h-screen">
        {{-- GTM COMPANY SIDEBAR --}}
        <aside id="company-sidebar" class="dashboard-sidebar sidebar-scroll-area w-72 shrink-0 border-r border-[#d9cab3] bg-[#102b25] p-6 text-[#f8f3eb] flex flex-col justify-between" style="position: sticky; top: 0; height: 100dvh; overflow-y: auto;">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ $siteBrand['logoUrl'] }}" alt="{{ $siteBrand['name'] }} Logo" class="h-10 w-10 rounded-xl object-cover ring-2 ring-[#d4af37] bg-white/10 shadow-sm">
                    <div>
                        <span class="block text-lg font-black tracking-tight text-white leading-tight font-serif">GTM Real Estate</span>
                        <span class="block text-[10px] font-bold uppercase tracking-widest text-[#d4af37]">Enterprise Hub</span>
                    </div>
                </a>

                {{-- Sidebar Skeleton placeholder for layout height stability while permissions/badges evaluate --}}
                <div class="sidebar-skeleton hidden pointer-events-none opacity-0 h-0 overflow-hidden" aria-hidden="true">
                    <div class="h-9 w-full rounded-xl bg-white/5 animate-pulse mb-2"></div>
                    <div class="h-9 w-full rounded-xl bg-white/5 animate-pulse mb-2"></div>
                </div>

                <div class="mt-4 rounded-xl border border-[#d4af37]/30 bg-white/5 p-2.5 text-center">
                    <span class="block text-[10px] font-black uppercase tracking-wider text-[#d4af37]">Corporate Command</span>
                    <span class="block text-xs font-bold text-[#dfeee4] mt-0.5">GTM Real Estate Company</span>
                </div>

                <nav class="mt-6 space-y-1.5 min-h-[420px]" id="company-sidebar-nav">
                    <a href="{{ route('home') }}" class="flex items-center rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="mr-2">🏠</span> Home Website
                    </a>

                    <a href="#overview" data-target-section="overview" class="company-nav-link flex items-center rounded-xl bg-white/15 px-3 py-2 text-xs font-bold text-white shadow-xs">
                        <span class="mr-2">⚡</span> Executive Overview
                    </a>

                    <div class="pt-3 pb-1">
                        <p class="px-3 text-[10px] font-black uppercase tracking-[0.2em] text-[#d4af37]">Marketplace Desks</p>
                    </div>

                    {{-- 1. Branches --}}
                    <a href="#branches" data-target-section="branches" class="company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span class="flex items-center gap-2">
                            <span>🏢</span>
                            <span>Branches</span>
                        </span>
                        <span class="rounded-full bg-[#d4af37] px-2 py-0.5 text-[10px] font-black text-[#102b25]">{{ $branches->count() }}</span>
                    </a>

                    {{-- 2. Agents --}}
                    <a href="#agents" data-target-section="agents" class="company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span class="flex items-center gap-2">
                            <span>👔</span>
                            <span>Agents</span>
                        </span>
                        <span class="rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-bold text-white">{{ $agents->count() }}</span>
                    </a>

                    {{-- 3. Listings --}}
                    <a href="#listings" data-target-section="listings" class="company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span class="flex items-center gap-2">
                            <span>🏷️</span>
                            <span>Listings</span>
                        </span>
                        <span class="rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-bold text-white">{{ $listings->count() }}</span>
                    </a>

                    {{-- 4. Properties --}}
                    <a href="#properties" data-target-section="properties" class="company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span class="flex items-center gap-2">
                            <span>🏡</span>
                            <span>Properties</span>
                        </span>
                        <span class="rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-bold text-white">{{ $properties->count() }}</span>
                    </a>

                    {{-- 5. Clients --}}
                    <a href="#clients" data-target-section="clients" class="company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span class="flex items-center gap-2">
                            <span>👥</span>
                            <span>Clients</span>
                        </span>
                        <span class="rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-bold text-white">{{ $clients->count() }}</span>
                    </a>

                    {{-- 6. Transactions --}}
                    <a href="#transactions" data-target-section="transactions" class="company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span class="flex items-center gap-2">
                            <span>💳</span>
                            <span>Transactions</span>
                        </span>
                        <span class="rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-bold text-white">{{ $orders->count() + $rentSchedules->count() }}</span>
                    </a>

                    {{-- 7. Reports --}}
                    @php
                        $pendingReportsCount = $reports->where('status', 'pending')->count();
                    @endphp
                    <a href="#reports" data-target-section="reports" class="company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span class="flex items-center gap-2">
                            <span>🚨</span>
                            <span>Client Reports</span>
                        </span>
                        @if ($pendingReportsCount > 0)
                            <span class="rounded-full bg-rose-500/20 border border-rose-400 px-2 py-0.5 text-[10px] font-bold text-rose-300 animate-pulse">{{ $pendingReportsCount }} Issues</span>
                        @else
                            <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-2 py-0.5 text-[10px] font-bold text-[#d4af37]">Live</span>
                        @endif
                    </a>

                    @include('layouts.partials.authorized-sidebar-links')
                </nav>
            </div>

            {{-- Signed-in User Widget --}}
            @include('dashboard.partials.signed-in-user')
        </aside>

        {{-- MAIN CONTENT AREA --}}
        <main class="flex-1 px-6 py-10 lg:px-10 overflow-y-auto">
            {{-- Top Bar --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-[#d9cab3] pb-6">
                <div>
                    <span class="text-[11px] font-black uppercase tracking-[0.25em] text-[#587165]">Enterprise Operations Desk</span>
                    <h1 class="mt-1 text-3xl sm:text-4xl font-black text-[#102b25] font-serif">GTM Real Estate Company</h1>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Direct Client Reports Alert Bell --}}
                    <button type="button" onclick="switchCompanySection('reports')" class="relative flex items-center justify-center rounded-full border border-[#d9cab3] bg-white p-2 text-[#102b25] hover:bg-[#fbf6ed] transition cursor-pointer shadow-2xs" title="{{ $pendingReportsCount }} Pending Client Reports">
                        <svg class="h-5 w-5 text-[#102b25]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if ($pendingReportsCount > 0)
                            <span class="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[9px] font-black text-white ring-2 ring-white animate-pulse">
                                {{ $pendingReportsCount }}
                            </span>
                        @endif
                    </button>

                    <span class="rounded-full bg-[#102b25] px-4 py-1.5 text-xs font-bold text-[#d4af37] border border-[#d4af37]/40 shadow-xs">
                        HQ Addis Ababa, Ethiopia
                    </span>
                    <a href="{{ route('home') }}" class="rounded-full border border-[#d9cab3] bg-white px-4 py-1.5 text-xs font-bold text-[#102b25] hover:bg-[#fbf6ed] transition">
                        View Portal &rarr;
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mt-6 rounded-2xl border border-emerald-300 bg-emerald-50 px-5 py-3.5 text-xs font-bold text-emerald-900 shadow-xs">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-6 rounded-2xl border border-rose-300 bg-rose-50 p-4 text-xs font-medium text-rose-900">
                    <p class="font-bold mb-1">Please address the following:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($pendingReportsCount > 0)
                <div class="mt-6 rounded-2xl border-2 border-rose-400 bg-gradient-to-r from-rose-50 via-amber-50 to-orange-50 p-5 text-rose-950 shadow-md flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-600 text-white font-black text-xl shadow-xs animate-bounce">
                            ⚠️
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded bg-rose-700 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-white">Direct Client Incident Alert</span>
                                <span class="text-[11px] font-bold text-rose-800">ከደንበኞች የተላከ አፋጣኝ ሪፖርት</span>
                            </div>
                            <p class="text-xs text-rose-900 mt-1 font-medium">
                                <strong>{{ $pendingReportsCount }}</strong> client {{ Str::plural('issue', $pendingReportsCount) }} submitted via portal requiring immediate company review &amp; investigation.
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="switchCompanySection('reports')" class="inline-flex items-center justify-center rounded-xl bg-rose-800 px-5 py-2.5 text-xs font-black text-white shadow-sm hover:bg-black transition cursor-pointer shrink-0">
                        Investigate Reports Desk &rarr;
                    </button>
                </div>
            @endif

            {{-- ═══════════════════ 0. EXECUTIVE OVERVIEW PANEL ═══════════════════ --}}
            <div data-company-section="overview" class="company-section-panel mt-6">
                {{-- Beast Display Hero Banner --}}
                <div class="relative overflow-hidden rounded-[2rem] border-2 border-[#102b25] bg-gradient-to-br from-[#102b25] via-[#163a31] to-[#0c201c] p-7 md:p-9 text-[#f8f3eb] shadow-xl">
                    <div class="absolute -right-12 -bottom-12 h-72 w-72 rounded-full bg-[#d4af37]/10 blur-3xl pointer-events-none"></div>
                    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-md bg-[#d4af37] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-widest text-[#102b25]">GTM Enterprise</span>
                                <span class="rounded-full bg-white/10 px-3 py-0.5 text-xs font-semibold text-[#f0e8d8] border border-white/15">Real Estate Company</span>
                                <span class="text-xs font-semibold text-[#d4af37]">Addis Ababa &amp; Regions</span>
                            </div>
                            <h2 class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white font-serif">
                                GTM Real Estate Company
                            </h2>
                            <p class="mt-2 text-xs sm:text-sm text-[#dfeee4] max-w-2xl leading-relaxed">
                                Autonomous Enterprise Command Center: Orchestrating branches, licensed broker networks, market listings, property assets, client CRM relationships, escrow transactions, and executive performance reporting.
                            </p>
                        </div>
                    </div>
                    <div class="mt-8 pt-6 border-t border-white/10 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                        <div onclick="switchCompanySection('branches')" class="cursor-pointer rounded-2xl bg-white/5 p-3 text-center transition hover:bg-white/10 border border-white/10">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Branches</span>
                            <span class="block text-2xl font-black text-white mt-0.5">{{ $companyKpis['totalBranches'] }}</span>
                            <span class="block text-[10px] text-emerald-400 font-semibold">{{ $companyKpis['activeBranches'] }} Active</span>
                        </div>
                        <div onclick="switchCompanySection('agents')" class="cursor-pointer rounded-2xl bg-white/5 p-3 text-center transition hover:bg-white/10 border border-white/10">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Agents</span>
                            <span class="block text-2xl font-black text-white mt-0.5">{{ $companyKpis['totalAgents'] }}</span>
                            <span class="block text-[10px] text-emerald-400 font-semibold">{{ $companyKpis['verifiedAgents'] }} Verified</span>
                        </div>
                        <div onclick="switchCompanySection('listings')" class="cursor-pointer rounded-2xl bg-white/5 p-3 text-center transition hover:bg-white/10 border border-white/10">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Listings</span>
                            <span class="block text-2xl font-black text-white mt-0.5">{{ $companyKpis['totalListings'] }}</span>
                            <span class="block text-[10px] text-amber-300 font-semibold">Active Feed</span>
                        </div>
                        <div onclick="switchCompanySection('properties')" class="cursor-pointer rounded-2xl bg-white/5 p-3 text-center transition hover:bg-white/10 border border-white/10">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Properties</span>
                            <span class="block text-2xl font-black text-white mt-0.5">{{ $companyKpis['totalProperties'] }}</span>
                            <span class="block text-[10px] text-emerald-400 font-semibold">Assets</span>
                        </div>
                        <div onclick="switchCompanySection('clients')" class="cursor-pointer rounded-2xl bg-white/5 p-3 text-center transition hover:bg-white/10 border border-white/10">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Clients</span>
                            <span class="block text-2xl font-black text-white mt-0.5">{{ $companyKpis['totalClients'] }}</span>
                            <span class="block text-[10px] text-blue-300 font-semibold">In CRM</span>
                        </div>
                        <div onclick="switchCompanySection('transactions')" class="cursor-pointer rounded-2xl bg-white/5 p-3 text-center transition hover:bg-white/10 border border-white/10">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Transactions</span>
                            <span class="block text-2xl font-black text-white mt-0.5">{{ $orders->count() + $rentSchedules->count() }}</span>
                            <span class="block text-[10px] text-emerald-400 font-semibold">Auditable</span>
                        </div>
                        <div onclick="switchCompanySection('reports')" class="cursor-pointer rounded-2xl bg-white/5 p-3 text-center transition hover:bg-white/10 border border-white/10">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Client Reports</span>
                            <span class="block text-2xl font-black text-white mt-0.5">{{ $reports->count() }}</span>
                            @if ($pendingReportsCount > 0)
                                <span class="block text-[10px] text-rose-400 font-bold animate-pulse">⚠️ {{ $pendingReportsCount }} Pending</span>
                            @else
                                <span class="block text-[10px] text-emerald-400 font-semibold">Trust &amp; Safety</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Highlights Grid --}}
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-sm">
                        <span class="text-[10px] font-black uppercase tracking-wider text-[#587165]">Asset Portfolio Value</span>
                        <p class="mt-2 text-2xl font-black text-[#102b25]">ETB {{ number_format($companyKpis['portfolioValuation'], 0) }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $properties->count() }} active property listings</p>
                    </div>
                    <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-sm">
                        <span class="text-[10px] font-black uppercase tracking-wider text-[#587165]">Gross Deal Turnover</span>
                        <p class="mt-2 text-2xl font-black text-emerald-800">ETB {{ number_format($companyKpis['grossSalesVolume'], 0) }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $orders->count() }} sales orders settled</p>
                    </div>
                    <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-sm">
                        <span class="text-[10px] font-black uppercase tracking-wider text-[#587165]">Rental Yield Collected</span>
                        <p class="mt-2 text-2xl font-black text-[#9b6c17]">ETB {{ number_format($companyKpis['rentCollected'], 0) }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $rentSchedules->where('status', 'paid')->count() }} settled rent installments</p>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════ 1. BRANCHES DESK ═══════════════════ --}}
            <div data-company-section="branches" class="company-section-panel mt-6 hidden">
                @include('dashboard.partials.branches-desk')
            </div>

            {{-- ═══════════════════ 2. AGENTS DESK ═══════════════════ --}}
            <div data-company-section="agents" class="company-section-panel mt-6 hidden">
                <section class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-6 gap-4">
                        <div>
                            <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Broker Workforce</span>
                            <h2 class="mt-1 text-2xl font-black text-[#102b25]">Licensed Broker &amp; Agent Roster</h2>
                            <p class="mt-1 text-xs text-slate-500">Manage real estate broker licenses, branch allocations, and commission split ratios.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-[#fbf8f3] border border-[#d9cab3] px-3.5 py-1 text-xs font-bold text-[#102b25]">
                                Total Agents: {{ $agents->count() }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 overflow-x-auto rounded-2xl border border-[#d9cab3]">
                        <table class="w-full border-collapse text-left text-xs text-slate-700">
                            <thead class="bg-[#102b25] text-[#f8f3eb]">
                                <tr>
                                    <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">Broker Profile</th>
                                    <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">Assigned Branch</th>
                                    <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">License Number</th>
                                    <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px] text-center">Split Rate</th>
                                    <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px] text-center">Properties</th>
                                    <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0e4cf]">
                                @forelse ($agents as $agent)
                                    <tr class="transition hover:bg-[#fbf8f3]">
                                        <td class="px-4 py-3.5 font-bold text-[#102b25]">
                                            <div class="flex items-center gap-3">
                                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#102b25] text-white font-black text-xs">
                                                    {{ strtoupper(substr($agent->name, 0, 2)) }}
                                                </span>
                                                <div>
                                                    <p class="font-bold text-[#102b25]">
                                                        <a href="{{ route('agents.show', $agent->id) }}" target="_blank" class="hover:underline hover:text-[#d4af37]">
                                                            {{ $agent->name }} ↗
                                                        </a>
                                                    </p>
                                                    <p class="text-[10px] text-slate-400">{{ $agent->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <span class="font-semibold text-slate-700">{{ $agent->branch?->name ?: 'HQ Operations' }}</span>
                                        </td>
                                        <td class="px-4 py-3.5 font-mono font-bold text-slate-600">
                                            {{ $agent->license_number ?: 'SRE-LIC-'.str_pad($agent->id, 4, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td class="px-4 py-3.5 text-center font-bold text-[#9b6c17]">
                                            {{ $agent->split_rate ?? 70 }}%
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <span class="rounded-full bg-[#f4ebe1] px-2.5 py-0.5 font-bold text-[#102b25]">
                                                {{ $agent->properties->count() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $agent->license_verified ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $agent->license_verified ? 'Verified Broker' : 'Pending KYC' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No agents registered.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($agents instanceof \Illuminate\Pagination\LengthAwarePaginator && $agents->hasPages())
                        <div class="mt-6 border-t border-[#f0e4cf] pt-4">
                            {{ $agents->fragment('agents')->links() }}
                        </div>
                    @endif
                </section>
            </div>

            {{-- ═══════════════════ 3. LISTINGS DESK ═══════════════════ --}}
            <div data-company-section="listings" class="company-section-panel mt-6 hidden">
                @include('dashboard.partials.company-listings-desk')
            </div>

            {{-- ═══════════════════ 4. PROPERTIES DESK (Listing Management Studio) ═══════════════════ --}}
            <div data-company-section="properties" class="company-section-panel mt-6 hidden">
                <section class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-6">
                        <div>
                            <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Listing Management</span>
                            <h2 class="mt-1 text-2xl font-black text-[#102b25]">Company Property Studio</h2>
                            <p class="mt-1 text-xs text-slate-500">Post new listings, edit details, publish or unpublish, and mark each one available, rented, or sold.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-[#fbf8f3] border border-[#d9cab3] px-3.5 py-1 text-xs font-bold text-[#102b25]">
                                Total: {{ $properties->count() }}
                            </span>
                            <span class="rounded-full bg-emerald-50 border border-emerald-200 px-3.5 py-1 text-xs font-bold text-emerald-800">
                                Live: {{ $properties->where('is_active', true)->count() }}
                            </span>
                            <button type="button" id="company-add-listing-toggle" class="rounded-full bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white transition hover:bg-[#254d43] shadow-sm cursor-pointer" data-label-create="+ New Listing / Property" data-label-close="Close Form">+ New Listing / Property</button>
                        </div>
                    </div>

                    {{-- Add / Edit listing form --}}
                    @php
                        $companyListingFormAction = isset($companyPropertyToEdit) && $companyPropertyToEdit
                            ? route('company.listings.update', $companyPropertyToEdit)
                            : route('company.listings.store');
                    @endphp
                    <form method="POST" action="{{ $companyListingFormAction }}" id="company-listing-form" class="mt-6 hidden rounded-2xl border border-[#d9cab3] bg-[#fdfaf5] p-5">
                        @csrf
                        @if (isset($companyPropertyToEdit) && $companyPropertyToEdit)
                            @method('PUT')
                        @endif
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Listing Title *</label>
                                <input type="text" name="title" required value="{{ $companyPropertyToEdit->title ?? old('title') }}" placeholder="e.g. Bole 4-Bedroom Villa" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Listing Type *</label>
                                <select name="type" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
                                    @foreach (['sale' => 'For Sale', 'rent' => 'For Rent', 'stay' => 'Short Stay'] as $typeValue => $typeLabel)
                                        <option value="{{ $typeValue }}" {{ ($companyPropertyToEdit->type ?? old('type')) === $typeValue ? 'selected' : '' }}>{{ $typeLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Category</label>
                                <select name="category" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
                                    <option value="">Uncategorized</option>
                                    @foreach (($propertyCategories ?? collect())->where('is_active')->groupBy('group') as $categoryGroup => $categoryOptions)
                                        <optgroup label="{{ $categoryGroup }}">
                                            @foreach ($categoryOptions as $categoryOption)
                                                <option value="{{ $categoryOption->slug }}" {{ ($companyPropertyToEdit->property_category ?? old('category')) === $categoryOption->slug ? 'selected' : '' }}>{{ $categoryOption->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Price (ETB) *</label>
                                <input type="number" name="price" required min="0" step="0.01" value="{{ $companyPropertyToEdit->price ?? old('price') }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Bedrooms *</label>
                                <input type="number" name="bedrooms" required min="0" value="{{ $companyPropertyToEdit->bedrooms ?? old('bedrooms', 0) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Bathrooms *</label>
                                <input type="number" name="bathrooms" required min="0" value="{{ $companyPropertyToEdit->bathrooms ?? old('bathrooms', 0) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Area (m²) *</label>
                                <input type="number" name="area" required min="0" value="{{ $companyPropertyToEdit->area ?? old('area') }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">City</label>
                                <input type="text" name="city" value="{{ $companyPropertyToEdit->city ?? old('city') }}" placeholder="Addis Ababa" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Subcity / Area</label>
                                <input type="text" name="subcity" value="{{ $companyPropertyToEdit->subcity ?? old('subcity') }}" placeholder="Bole" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Address</label>
                                <input type="text" name="address" value="{{ $companyPropertyToEdit->address ?? old('address') }}" placeholder="Street, landmark" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Listing Status *</label>
                                <select name="status" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
                                    @foreach (['published' => 'Published (live on site)', 'draft' => 'Draft', 'available' => 'Available', 'sold' => 'Sold', 'rented' => 'Rented', 'archived' => 'Archived'] as $statusValue => $statusLabel)
                                        <option value="{{ $statusValue }}" {{ ($companyPropertyToEdit->status ?? old('status')) === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2 lg:col-span-3">
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Description</label>
                                <textarea name="description" rows="3" placeholder="Describe the property, finishes, and what makes it stand out..." class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">{{ $companyPropertyToEdit->description ?? old('description') }}</textarea>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43] cursor-pointer">
                                {{ isset($companyPropertyToEdit) && $companyPropertyToEdit ? 'Save Changes' : 'Publish Listing' }}
                            </button>
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-600">
                                <input type="checkbox" name="featured" value="1" {{ ($companyPropertyToEdit->featured ?? false) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34]">
                                Feature on homepage
                            </label>
                        </div>
                    </form>

                    @if ($errors->any())
                        <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-800">
                            <ul class="list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Listing management table --}}
                    <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#fbf8f3] text-[10px] font-black uppercase tracking-wider text-[#587165] border-b border-[#e7ddca]">
                                <tr>
                                    <th class="px-4 py-3.5">Listing</th>
                                    <th class="px-4 py-3.5">Type</th>
                                    <th class="px-4 py-3.5">Price</th>
                                    <th class="px-4 py-3.5">Location</th>
                                    <th class="px-4 py-3.5 text-center">Status</th>
                                    <th class="px-4 py-3.5 text-right">Manage</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0e8d8]">
                                @forelse ($properties as $property)
                                    <tr class="hover:bg-[#fcfaf7] transition" data-search="{{ strtolower($property->title.' '.$property->type.' '.($property->city ?? '').' '.($property->subcity ?? '')) }}">
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-3">
                                                @if ($property->images->isNotEmpty())
                                                    <img src="{{ $property->images->first()->image_url }}" alt="{{ $property->title }}" class="h-10 w-12 rounded-lg object-cover ring-1 ring-[#d9cab3]">
                                                @else
                                                    <img src="{{ asset('images/luxury/hero-skyline.jpg') }}" alt="{{ $property->title }}" class="h-10 w-12 rounded-lg object-cover ring-1 ring-[#d9cab3]">
                                                @endif
                                                <span class="font-bold text-sm text-[#1d3c34]">{{ $property->title }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-slate-600">{{ ucfirst($property->type ?? 'sale') }}</td>
                                        <td class="px-4 py-3.5 font-bold text-[#102b25]">{{ number_format((float) $property->price) }} ETB</td>
                                        <td class="px-4 py-3.5 text-slate-600">{{ $property->city ?: '—' }}{{ $property->subcity ? ' · '.$property->subcity : '' }}</td>
                                        <td class="px-4 py-3.5 text-center">
                                            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $property->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $property->is_active ? 'Live · ' : 'Unpublished · ' }}{{ ucfirst($property->status ?? 'draft') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="flex flex-wrap items-center justify-end gap-1.5">
                                                <a href="{{ route('company.listings.edit-form', ['property' => $property, 'edit' => 1]) }}" class="rounded-lg border border-[#d9cab3] bg-white px-2.5 py-1 text-[11px] font-bold text-[#1d3c34] hover:bg-[#fbf6ed] transition cursor-pointer">Edit</a>
                                                <form method="POST" action="{{ route('company.listings.publish', $property) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="rounded-lg bg-emerald-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-emerald-700 transition cursor-pointer">
                                                        {{ $property->is_active ? 'Unpublish' : 'Publish' }}
                                                    </button>
                                                </form>
                                                @foreach (['available', 'rented', 'sold'] as $propertyStatus)
                                                    <form method="POST" action="{{ route('company.listings.status', ['property' => $property, 'status' => $propertyStatus]) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="rounded-lg border border-[#d9cab3] px-2.5 py-1 text-[11px] font-bold text-[#1d3c34] transition hover:bg-[#fbf6ed] cursor-pointer {{ $property->status === $propertyStatus ? 'bg-[#f9ecd0] border-[#c5a059]' : 'bg-white' }}">
                                                            {{ ucfirst($propertyStatus) }}
                                                        </button>
                                                    </form>
                                                @endforeach
                                                @if ($property->slug)
                                                    <a href="{{ route('properties.show', $property->slug) }}" target="_blank" class="rounded-lg border border-[#d9cab3] bg-white px-2.5 py-1 text-[11px] font-bold text-[#1d3c34] hover:bg-[#fbf6ed] transition">View ↗</a>
                                                @endif
                                                <form method="POST" action="{{ route('company.listings.destroy', $property) }}" onsubmit="return confirm('Delete listing &quot;{{ $property->title }}&quot;?')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-rose-200 px-2.5 py-1 text-[11px] font-bold text-rose-700 transition hover:bg-rose-50 cursor-pointer">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-sm font-semibold text-slate-500">
                                            No listings yet — use “+ New Listing / Property” to post your first one.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($properties->hasPages())
                        <div class="mt-5">{{ $properties->links() }}</div>
                    @endif
                </section>
            </div>
            {{-- ═══════════════════ 5. CLIENTS CRM DESK ═══════════════════ --}}
            <div data-company-section="clients" class="company-section-panel mt-6 hidden">
                @include('dashboard.partials.clients-desk')
            </div>

            {{-- ═══════════════════ 6. TRANSACTIONS DESK ═══════════════════ --}}
            <div data-company-section="transactions" class="company-section-panel mt-6 hidden">
                @include('dashboard.partials.transactions-desk')
            </div>

            {{-- ═══════════════════ 7. REPORTS DESK ═══════════════════ --}}
            <div data-company-section="reports" class="company-section-panel mt-6 hidden">
                @include('dashboard.partials.reports-desk')
            </div>
        </main>
    </div>

    {{-- Include Universal Ethiopian Payment Modal --}}
    @include('properties.partials.payment-modal')

    {{-- Company Dashboard Section Switcher & Interactive Script --}}
    <script>
        const validCompanySections = ['overview', 'branches', 'agents', 'listings', 'properties', 'clients', 'transactions', 'reports'];

        function switchCompanySection(sectionName) {
            if (!validCompanySections.includes(sectionName)) {
                return;
            }

            const panels = document.querySelectorAll('.company-section-panel');
            const links = document.querySelectorAll('.company-nav-link');

            panels.forEach(p => {
                if (p.getAttribute('data-company-section') === sectionName) {
                    p.classList.remove('hidden');
                    p.querySelectorAll('.section-panel').forEach(sp => sp.classList.remove('hidden'));
                } else {
                    p.classList.add('hidden');
                }
            });

            links.forEach(l => {
                const target = l.getAttribute('data-target-section');
                if (target === sectionName) {
                    l.classList.add('bg-white/15', 'text-white', 'shadow-xs');
                    l.classList.remove('text-[#dfeee4]');
                } else {
                    l.classList.remove('bg-white/15', 'text-white', 'shadow-xs');
                    l.classList.add('text-[#dfeee4]');
                }
            });

            if (window.location.hash !== '#' + sectionName) {
                window.location.hash = sectionName;
            }
        }
        window.switchCompanySection = switchCompanySection;

        document.addEventListener('DOMContentLoaded', function() {
            // 1. Toggle create form panels (e.g. + Add Branch)
            document.querySelectorAll('.toggle-create-form').forEach(function (button) {
                button.addEventListener('click', function () {
                    const targetId = button.getAttribute('data-target');
                    const panel = document.getElementById(targetId);
                    if (!panel) return;

                    const isHidden = panel.classList.contains('hidden');
                    if (isHidden) {
                        panel.classList.remove('hidden');
                        if (button.dataset.labelClose) {
                            button.textContent = button.dataset.labelClose;
                        }
                    } else {
                        panel.classList.add('hidden');
                        if (button.dataset.labelCreate) {
                            button.textContent = button.dataset.labelCreate;
                        }
                    }
                });
            });

            // 2. Delegate click on any [data-target-section] links and buttons
            document.addEventListener('click', function(e) {
                const targetEl = e.target.closest('[data-target-section]');
                if (targetEl) {
                    const targetSection = targetEl.getAttribute('data-target-section');
                    if (targetSection && validCompanySections.includes(targetSection)) {
                        e.preventDefault();
                        switchCompanySection(targetSection);
                    }
                }
            });

            // 3. Listen to browser back/forward hash changes
            window.addEventListener('hashchange', function() {
                const section = window.location.hash.replace('#', '');
                if (section && validCompanySections.includes(section)) {
                    switchCompanySection(section);
                }
            });

            // 4. Load initial section from hash or query param or default to overview
            const urlParams = new URLSearchParams(window.location.search);
            const querySection = urlParams.get('section');
            const initialHash = window.location.hash.replace('#', '') || querySection || 'overview';
            if (validCompanySections.includes(initialHash)) {
                switchCompanySection(initialHash);
            }
        });
    </script>
    <script>
        (function () {
            const toggleButton = document.getElementById('company-add-listing-toggle');
            const form = document.getElementById('company-listing-form');
            if (toggleButton && form) {
                toggleButton.addEventListener('click', function () {
                    const willShow = form.classList.contains('hidden');
                    form.classList.toggle('hidden', !willShow);
                });
            }

            const params = new URLSearchParams(window.location.search);
            if (form && (params.get('edit_property') || {{ $errors->any() ? 'true' : 'false' }})) {
                form.classList.remove('hidden');
            }
        })();
    </script>
    @include('layouts.partials.sidebar-scroll-preserver')
</body>
</html>
