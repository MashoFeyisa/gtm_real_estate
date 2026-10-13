<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Analytics Dashboard - {{ $siteBrand['name'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @include('layouts.partials.theme-styles')
    <style>
        .chart-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .chart-card:hover {
            box-shadow: 0 10px 25px -5px rgba(29, 60, 52, 0.08), 0 8px 10px -6px rgba(29, 60, 52, 0.04);
        }
        .apexcharts-tooltip {
            background: #102b25 !important;
            border: 1px solid #d4af37 !important;
            color: #ffffff !important;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25) !important;
            border-radius: 0.75rem !important;
        }
        .apexcharts-tooltip-title {
            background: #1d3c34 !important;
            border-bottom: 1px solid rgba(212, 175, 55, 0.3) !important;
            font-weight: 700 !important;
            color: #d4af37 !important;
        }
        .apexcharts-text {
            font-family: inherit !important;
        }
    </style>
</head>
<body class="min-h-screen bg-[#f5f0e7] text-slate-800 antialiased font-sans">
    <div class="flex min-h-screen flex-col lg:flex-row">
        <!-- Sidebar Navigation -->
        <aside id="analytics-sidebar" class="dashboard-sidebar sidebar-scroll-area w-full lg:w-72 shrink-0 border-b lg:border-b-0 lg:border-r border-[#d9cab3] bg-[#1d3c34] p-6 text-[#f8f3eb]" style="position: sticky; top: 0; height: 100dvh; overflow-y: auto;">
            <div class="flex items-center justify-between lg:block">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ $siteBrand['logoUrl'] }}" alt="{{ $siteBrand['name'] }} Logo" class="h-10 w-10 rounded-xl object-contain ring-2 ring-[#d9cab3] bg-white/10">
                    <div>
                        <span class="block text-lg font-black tracking-tight text-white">{{ $siteBrand['name'] }}</span>
                        <span class="block text-xs font-semibold text-[#d4af37] uppercase tracking-wider">Analytics Suite</span>
                    </div>
                </a>
                <button id="sidebar-toggle" class="lg:hidden rounded-lg bg-white/10 p-2 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>

            <nav id="sidebar-nav" class="mt-8 hidden lg:block space-y-2">
                <div class="px-3 pb-2 text-[10px] font-black uppercase tracking-[0.2em] text-[#d9cab3]/70">Navigation</div>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/10">
                    <svg class="h-5 w-5 text-[#d9cab3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Admin Operations</span>
                </a>
                <a href="{{ route('dashboard.analytics') }}" class="flex items-center justify-between rounded-xl bg-[#102b25] border border-[#d4af37]/40 px-3 py-2.5 text-sm font-bold text-white shadow-inner">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5 text-[#d4af37]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Analytics Center</span>
                    </span>
                    <span class="rounded-full bg-[#d4af37] px-2 py-0.5 text-[10px] font-black text-[#102b25]">LIVE</span>
                </a>
                <a href="{{ route('company.dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/10">
                    <svg class="h-5 w-5 text-[#d9cab3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Company Hub</span>
                </a>
                <a href="{{ route('finance.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/10">
                    <svg class="h-5 w-5 text-[#d9cab3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Financial Reporting</span>
                </a>
                <a href="{{ route('properties.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/10">
                    <svg class="h-5 w-5 text-[#d9cab3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    <span>Public Catalog</span>
                </a>
            </nav>

            <div class="mt-8 rounded-[1.25rem] border border-white/10 bg-white/5 p-4 hidden lg:block">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#d9cab3]">Admin User</p>
                    <a href="{{ route('dashboard') }}" class="text-[11px] font-bold text-[#d4af37] hover:underline">&larr; Dashboard</a>
                </div>
                <div class="mt-3 flex items-center gap-3">
                    @if (auth()->user()->photo_url)
                        <img src="{{ auth()->user()->photo_url }}" alt="{{ auth()->user()->name }}" class="h-9 w-9 rounded-full object-cover ring-2 ring-[#d9cab3]">
                    @else
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#d9cab3]/20 font-bold text-white text-xs">
                            {{ auth()->user()->initials }}
                        </span>
                    @endif
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-white">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-[#dfeee4] capitalize">{{ auth()->user()->role ?? 'Administrator' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 px-4 sm:px-6 lg:px-10 py-8 lg:py-10 max-w-[1600px] mx-auto w-full">
            <!-- Header & Date Filter Controls -->
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between pb-6 border-b border-[#d9cab3]/60">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#102b25] px-3 py-1 text-xs font-black uppercase tracking-wider text-[#d4af37] border border-[#d4af37]/30">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Verified Database Feed
                        </span>
                        <span id="active-range-badge" class="rounded-full bg-[#ebdcc9] px-3 py-1 text-xs font-bold text-[#1d3c34]">
                            {{ $dateRangeLabel }}
                        </span>
                    </div>
                    <h1 class="mt-2 text-3xl sm:text-4xl font-black tracking-tight text-[#1d3c34]">
                        Real Estate Analytics Dashboard
                    </h1>
                    <p class="text-sm font-medium text-slate-600 mt-1">
                        Live performance metrics, market sales velocity, agent productivity, and inventory distribution.
                    </p>
                </div>

                <!-- Date Range Filter Selector -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <div class="flex flex-wrap items-center bg-[#ebdcc9]/60 p-1.5 rounded-2xl border border-[#d9cab3] gap-1 shadow-sm">
                        <button type="button" data-period="today" class="period-filter-btn rounded-xl px-3.5 py-1.5 text-xs font-bold transition cursor-pointer {{ $currentPeriod === 'today' ? 'bg-[#1d3c34] text-white shadow-sm' : 'text-[#1d3c34] hover:bg-white/40' }}">
                            Today
                        </button>
                        <button type="button" data-period="week" class="period-filter-btn rounded-xl px-3.5 py-1.5 text-xs font-bold transition cursor-pointer {{ $currentPeriod === 'week' ? 'bg-[#1d3c34] text-white shadow-sm' : 'text-[#1d3c34] hover:bg-white/40' }}">
                            This Week
                        </button>
                        <button type="button" data-period="month" class="period-filter-btn rounded-xl px-3.5 py-1.5 text-xs font-bold transition cursor-pointer {{ $currentPeriod === 'month' ? 'bg-[#1d3c34] text-white shadow-sm' : 'text-[#1d3c34] hover:bg-white/40' }}">
                            This Month
                        </button>
                        <button type="button" data-period="year" class="period-filter-btn rounded-xl px-3.5 py-1.5 text-xs font-bold transition cursor-pointer {{ $currentPeriod === 'year' ? 'bg-[#1d3c34] text-white shadow-sm' : 'text-[#1d3c34] hover:bg-white/40' }}">
                            This Year
                        </button>
                        <button type="button" data-period="all" class="period-filter-btn rounded-xl px-3.5 py-1.5 text-xs font-bold transition cursor-pointer {{ $currentPeriod === 'all' ? 'bg-[#1d3c34] text-white shadow-sm' : 'text-[#1d3c34] hover:bg-white/40' }}">
                            All Time
                        </button>
                        <button type="button" id="toggle-custom-filter" class="rounded-xl px-3.5 py-1.5 text-xs font-bold transition cursor-pointer {{ $currentPeriod === 'custom' ? 'bg-[#1d3c34] text-white shadow-sm' : 'text-[#1d3c34] hover:bg-white/40' }}">
                            Custom Range
                        </button>
                    </div>

                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#102b25]">
                        <svg class="h-4 w-4 text-[#d4af37]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Dashboard</span>
                    </a>
                </div>
            </div>

            <!-- Custom Date Range Form (collapsible) -->
            <div id="custom-range-container" class="mt-4 p-4 rounded-2xl bg-white border border-[#d9cab3] shadow-sm {{ $currentPeriod === 'custom' ? '' : 'hidden' }}">
                <form id="custom-range-form" class="flex flex-wrap items-end gap-3 text-sm">
                    <div>
                        <label for="custom_from" class="block text-xs font-bold text-[#1d3c34] uppercase mb-1">Start Date</label>
                        <input type="date" id="custom_from" name="from" value="{{ $from }}" class="rounded-xl border border-[#d9cab3] bg-[#f8f5ef] px-3 py-1.5 text-sm font-semibold text-slate-800 focus:border-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label for="custom_to" class="block text-xs font-bold text-[#1d3c34] uppercase mb-1">End Date</label>
                        <input type="date" id="custom_to" name="to" value="{{ $to }}" class="rounded-xl border border-[#d9cab3] bg-[#f8f5ef] px-3 py-1.5 text-sm font-semibold text-slate-800 focus:border-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                    </div>
                    <button type="submit" class="rounded-xl bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#102b25] transition cursor-pointer flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-[#d4af37]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Apply Custom Filter
                    </button>
                    <button type="button" id="cancel-custom-range" class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-200 transition">
                        Cancel
                    </button>
                </form>
            </div>

            <!-- Global Error Banner -->
            <div id="error-banner" class="hidden mt-4 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-rose-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span id="error-message" class="text-xs sm:text-sm font-semibold">Failed to fetch analytics data. Please retry.</span>
                </div>
                <button type="button" id="retry-btn" class="rounded-lg bg-rose-600 px-3 py-1 text-xs font-bold text-white hover:bg-rose-700 transition">
                    Retry
                </button>
            </div>

            <!-- Loading Indicator Overlay -->
            <div id="loading-indicator" class="hidden fixed inset-0 z-50 bg-[#102b25]/20 backdrop-blur-[2px] flex items-center justify-center">
                <div class="rounded-2xl bg-[#1d3c34] p-5 text-white flex items-center gap-3 shadow-2xl border border-[#d4af37]">
                    <svg class="h-6 w-6 animate-spin text-[#d4af37]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span class="text-sm font-bold tracking-wide">Refreshing Analytics...</span>
                </div>
            </div>

            <!-- 1. KPI CARDS SECTION (8 Required Cards) -->
            <div class="mt-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-[#587165]">Core Performance Indicators</h2>
                    <span class="text-xs font-semibold text-slate-500">Live DB Metrics</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- KPI 1: Total Users -->
                    <div class="kpi-card relative overflow-hidden rounded-2xl bg-white p-5 border border-[#d9cab3] shadow-sm transition hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Users</span>
                            <div class="rounded-xl bg-[#ebdcc9] p-2 text-[#1d3c34]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-baseline justify-between">
                            <span id="kpi-total-users" class="text-3xl font-black text-[#1d3c34]">{{ number_format($payload['kpis']['total_users']) }}</span>
                            <span class="text-[11px] font-bold text-slate-500">Platform Accounts</span>
                        </div>
                        <p class="mt-2 text-[11px] font-medium text-slate-500">
                            Total registered users across all roles
                        </p>
                    </div>

                    <!-- KPI 2: Total Agents -->
                    <div class="kpi-card relative overflow-hidden rounded-2xl bg-white p-5 border border-[#d9cab3] shadow-sm transition hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Agents</span>
                            <div class="rounded-xl bg-[#ebdcc9] p-2 text-[#1d3c34]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-baseline justify-between">
                            <span id="kpi-total-agents" class="text-3xl font-black text-[#1d3c34]">{{ number_format($payload['kpis']['total_agents']) }}</span>
                            <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Active Brokers</span>
                        </div>
                        <p class="mt-2 text-[11px] font-medium text-slate-500">
                            Licensed active company agents
                        </p>
                    </div>

                    <!-- KPI 3: Total Properties -->
                    <div class="kpi-card relative overflow-hidden rounded-2xl bg-white p-5 border border-[#d9cab3] shadow-sm transition hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Properties</span>
                            <div class="rounded-xl bg-[#ebdcc9] p-2 text-[#1d3c34]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-baseline justify-between">
                            <span id="kpi-total-properties" class="text-3xl font-black text-[#1d3c34]">{{ number_format($payload['kpis']['total_properties']) }}</span>
                            <span class="text-[11px] font-bold text-slate-500">Portfolio Size</span>
                        </div>
                        <p class="mt-2 text-[11px] font-medium text-slate-500">
                            Entire real-estate asset catalog
                        </p>
                    </div>

                    <!-- KPI 4: Available Properties -->
                    <div class="kpi-card relative overflow-hidden rounded-2xl bg-white p-5 border border-[#d9cab3] shadow-sm transition hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Available Properties</span>
                            <div class="rounded-xl bg-emerald-100 p-2 text-emerald-800">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-baseline justify-between">
                            <span id="kpi-available-properties" class="text-3xl font-black text-emerald-800">{{ number_format($payload['kpis']['available_properties']) }}</span>
                            <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Ready on Market</span>
                        </div>
                        <p class="mt-2 text-[11px] font-medium text-slate-500">
                            Available for immediate purchase / lease
                        </p>
                    </div>

                    <!-- KPI 5: Rented Properties -->
                    <div class="kpi-card relative overflow-hidden rounded-2xl bg-white p-5 border border-[#d9cab3] shadow-sm transition hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Rented Properties</span>
                            <div class="rounded-xl bg-blue-100 p-2 text-blue-800">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-baseline justify-between">
                            <span id="kpi-rented-properties" class="text-3xl font-black text-blue-800">{{ number_format($payload['kpis']['rented_properties']) }}</span>
                            <span class="text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">Active Leases</span>
                        </div>
                        <p class="mt-2 text-[11px] font-medium text-slate-500">
                            Properties currently generating rental yield
                        </p>
                    </div>

                    <!-- KPI 6: Sold Properties -->
                    <div class="kpi-card relative overflow-hidden rounded-2xl bg-white p-5 border border-[#d9cab3] shadow-sm transition hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#b8860b]">Sold Properties</span>
                            <div class="rounded-xl bg-[#f9ecd0] p-2 text-[#9b6c17]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-baseline justify-between">
                            <span id="kpi-sold-properties" class="text-3xl font-black text-[#9b6c17]">{{ number_format($payload['kpis']['sold_properties']) }}</span>
                            <span class="text-[11px] font-bold text-[#9b6c17] bg-[#f9ecd0]/50 px-2 py-0.5 rounded-full border border-[#d4af37]/30">Closed Deals</span>
                        </div>
                        <p class="mt-2 text-[11px] font-medium text-slate-500">
                            Successfully closed property sales
                        </p>
                    </div>

                    <!-- KPI 7: Total Revenue -->
                    <div class="kpi-card relative overflow-hidden rounded-2xl bg-[#1d3c34] text-white p-5 border border-[#102b25] shadow-sm transition hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#d9cab3]">Total Revenue</span>
                            <div class="rounded-xl bg-white/10 p-2 text-[#d4af37]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-baseline justify-between">
                            <span id="kpi-total-revenue" class="text-2xl sm:text-3xl font-black text-[#d4af37]">{{ number_format($payload['kpis']['total_revenue'], 2) }}</span>
                            <span class="text-[11px] font-black text-[#ebdcc9] uppercase tracking-wider">ETB</span>
                        </div>
                        <p class="mt-2 text-[11px] font-medium text-[#dfeee4]">
                            Verified transaction payments collected
                        </p>
                    </div>

                    <!-- KPI 8: Total Commission -->
                    <div class="kpi-card relative overflow-hidden rounded-2xl bg-[#102b25] text-white p-5 border border-[#d4af37]/40 shadow-sm transition hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#d4af37]">Total Commission</span>
                            <div class="rounded-xl bg-[#d4af37]/20 p-2 text-[#d4af37]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-baseline justify-between">
                            <span id="kpi-total-commission" class="text-2xl sm:text-3xl font-black text-[#d4af37]">{{ number_format($payload['kpis']['total_commission'], 2) }}</span>
                            <span class="text-[11px] font-black text-[#ebdcc9] uppercase tracking-wider">ETB</span>
                        </div>
                        <p class="mt-2 text-[11px] font-medium text-[#dfeee4]">
                            Commission settlement volume recorded
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2. MAIN CHARTS GRID -->
            <div class="mt-10 space-y-8">
                <!-- CHART 1: LINE CHART (Monthly property sales, rentals, commission/revenue) -->
                <div class="chart-card rounded-2xl bg-white p-6 border border-[#d9cab3] shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-[#1d3c34]"></span>
                                <h3 class="text-lg font-black text-[#1d3c34]">Monthly Trends: Property Sales, Rentals &amp; Revenue</h3>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Real database time-series tracking sales volume, rental closings, and financial collections
                            </p>
                        </div>
                        <div class="flex items-center gap-3 text-xs font-bold text-slate-600">
                            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-[#1d3c34]"></span> Sales</span>
                            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-[#3b82f6]"></span> Rentals</span>
                            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-[#d4af37]"></span> Revenue (ETB)</span>
                        </div>
                    </div>
                    <div class="relative mt-4">
                        <div id="chart-monthly-trends" class="min-h-[360px] w-full"></div>
                        <div id="trends-empty" class="hidden absolute inset-0 flex items-center justify-center bg-white/90">
                            <p class="text-sm font-bold text-slate-500">No activity recorded for this period.</p>
                        </div>
                    </div>
                </div>

                <!-- 2-COLUMN SECTION: Agent Performance & Property Status Donut -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- CHART 2: BAR CHART (Agent Performance) -->
                    <div class="chart-card rounded-2xl bg-white p-6 border border-[#d9cab3] shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full bg-[#d4af37]"></span>
                                        <h3 class="text-lg font-black text-[#1d3c34]">Agent Performance &amp; Deal Closings</h3>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Properties sold and rented per agent, sorted by total transaction volume
                                    </p>
                                </div>
                                <span class="rounded-full bg-[#ebdcc9] px-2.5 py-1 text-[11px] font-black text-[#1d3c34]">
                                    Ranked
                                </span>
                            </div>
                            <div class="relative mt-4">
                                <div id="chart-agent-performance" class="min-h-[340px] w-full"></div>
                                <div id="agent-empty" class="hidden absolute inset-0 flex items-center justify-center bg-white/90">
                                    <p class="text-sm font-bold text-slate-500">No agent transactions recorded for this period.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Mini Agent Performance Table -->
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-2">Agent Leaderboard Summary</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-slate-500">
                                            <th class="pb-2 font-bold">Agent Name</th>
                                            <th class="pb-2 font-bold text-center">Properties Sold</th>
                                            <th class="pb-2 font-bold text-center">Properties Rented</th>
                                            <th class="pb-2 font-bold text-right">Total Deals</th>
                                        </tr>
                                    </thead>
                                    <tbody id="agent-summary-tbody" class="divide-y divide-slate-100">
                                        @forelse ($payload['agent_performance']['details'] as $agentRow)
                                            <tr class="hover:bg-slate-50">
                                                <td class="py-2 font-semibold text-[#1d3c34]">{{ $agentRow['name'] }}</td>
                                                <td class="py-2 text-center font-bold text-[#b8860b]">{{ $agentRow['sold'] }}</td>
                                                <td class="py-2 text-center font-bold text-blue-700">{{ $agentRow['rented'] }}</td>
                                                <td class="py-2 text-right font-black text-[#1d3c34]">{{ $agentRow['total_deals'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="py-3 text-center text-slate-400">No agents registered</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- CHART 3: DONUT CHART (Available, Rented, Sold) -->
                    <div class="chart-card rounded-2xl bg-white p-6 border border-[#d9cab3] shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-600"></span>
                                        <h3 class="text-lg font-black text-[#1d3c34]">Property Inventory Breakdown</h3>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Available, Rented, and Sold status distribution with percentages and totals
                                    </p>
                                </div>
                                <span id="donut-total-badge" class="rounded-full bg-[#1d3c34] px-2.5 py-1 text-[11px] font-black text-white">
                                    {{ $payload['property_status']['totals'] }} Properties
                                </span>
                            </div>
                            <div class="relative mt-4">
                                <div id="chart-property-status" class="min-h-[340px] w-full flex items-center justify-center"></div>
                                <div id="donut-empty" class="hidden absolute inset-0 flex items-center justify-center bg-white/90">
                                    <p class="text-sm font-bold text-slate-500">No property inventory data found.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Status Percentage Indicators -->
                        <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-3 gap-2 text-center">
                            <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200">
                                <span class="block text-[11px] font-bold text-emerald-800">Available</span>
                                <span id="donut-stat-available" class="block text-lg font-black text-emerald-900 mt-0.5">
                                    {{ $payload['property_status']['counts']['available'] }}
                                </span>
                                <span id="donut-pct-available" class="block text-[10px] font-bold text-emerald-600">
                                    {{ $payload['property_status']['percentages']['available'] }}%
                                </span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-blue-50 border border-blue-200">
                                <span class="block text-[11px] font-bold text-blue-800">Rented</span>
                                <span id="donut-stat-rented" class="block text-lg font-black text-blue-900 mt-0.5">
                                    {{ $payload['property_status']['counts']['rented'] }}
                                </span>
                                <span id="donut-pct-rented" class="block text-[10px] font-bold text-blue-600">
                                    {{ $payload['property_status']['percentages']['rented'] }}%
                                </span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#f9ecd0] border border-[#d4af37]/40">
                                <span class="block text-[11px] font-bold text-[#9b6c17]">Sold</span>
                                <span id="donut-stat-sold" class="block text-lg font-black text-[#9b6c17] mt-0.5">
                                    {{ $payload['property_status']['counts']['sold'] }}
                                </span>
                                <span id="donut-pct-sold" class="block text-[10px] font-bold text-[#b8860b]">
                                    {{ $payload['property_status']['percentages']['sold'] }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2-COLUMN SECTION: Stacked Bar (Rent vs Sale) & Location Chart -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- CHART 4: STACKED BAR CHART (Rent vs Sale Monthly Transactions) -->
                    <div class="chart-card rounded-2xl bg-white p-6 border border-[#d9cab3] shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-[#102b25]"></span>
                                    <h3 class="text-lg font-black text-[#1d3c34]">Rent vs. Sale Transactions</h3>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Monthly comparative transaction breakdown of rental leases versus property purchases
                                </p>
                            </div>
                            <div class="flex items-center gap-2 text-xs font-bold">
                                <span class="flex items-center gap-1 text-[#1d3c34]"><span class="h-2.5 w-2.5 rounded-full bg-[#1d3c34]"></span> Sale</span>
                                <span class="flex items-center gap-1 text-[#3b82f6]"><span class="h-2.5 w-2.5 rounded-full bg-[#3b82f6]"></span> Rent</span>
                            </div>
                        </div>
                        <div class="relative mt-4">
                            <div id="chart-rent-vs-sale" class="min-h-[340px] w-full"></div>
                            <div id="rent-sale-empty" class="hidden absolute inset-0 flex items-center justify-center bg-white/90">
                                <p class="text-sm font-bold text-slate-500">No rent or sale transactions recorded for this period.</p>
                            </div>
                        </div>
                    </div>

                    <!-- CHART 5: LOCATION CHART (Properties by City/Location) -->
                    <div class="chart-card rounded-2xl bg-white p-6 border border-[#d9cab3] shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-[#d4af37]"></span>
                                    <h3 class="text-lg font-black text-[#1d3c34]">Geographic Distribution</h3>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Property concentration across cities, subcities, and prime municipal markets
                                </p>
                            </div>
                            <span class="rounded-full bg-[#ebdcc9] px-2.5 py-1 text-[11px] font-black text-[#1d3c34]">
                                {{ count($payload['locations']['cities']) }} Locations
                            </span>
                        </div>
                        <div class="relative mt-4">
                            <div id="chart-locations" class="min-h-[340px] w-full"></div>
                            <div id="locations-empty" class="hidden absolute inset-0 flex items-center justify-center bg-white/90">
                                <p class="text-sm font-bold text-slate-500">No location data found.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHART 6: ADVANCED SCATTER CHART (Price vs Area) -->
                <div class="chart-card rounded-2xl bg-white p-6 border border-[#d9cab3] shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-[#b8860b]"></span>
                                <h3 class="text-lg font-black text-[#1d3c34]">Advanced Analytics: Property Price vs. Property Area</h3>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Scatter correlation analyzing property area (m²) against market pricing (ETB) for sale and rental listings
                            </p>
                        </div>
                        <div class="flex items-center gap-3 text-xs font-bold text-slate-600">
                            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-[#1d3c34]"></span> For Sale</span>
                            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-[#d4af37]"></span> For Rent / Stay</span>
                            <span id="scatter-count-badge" class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                                {{ $payload['scatter']['total_points'] }} Data Points
                            </span>
                        </div>
                    </div>
                    <div class="relative mt-4">
                        <div id="chart-price-area-scatter" class="min-h-[400px] w-full"></div>
                        <div id="scatter-empty" class="hidden absolute inset-0 flex items-center justify-center bg-white/90">
                            <p class="text-sm font-bold text-slate-500">No properties with valid area and price found.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer / System Stamp -->
            <div class="mt-12 pt-6 border-t border-[#d9cab3]/60 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-3">
                <p>&copy; {{ date('Y') }} {{ $siteBrand['name'] }} Analytics Intelligence Engine. All data sourced live from platform relational storage.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard') }}" class="font-bold text-[#1d3c34] hover:underline">Admin Operations</a>
                    <a href="{{ route('finance.index') }}" class="font-bold text-[#1d3c34] hover:underline">Finance Desk</a>
                    <a href="{{ route('home') }}" class="font-bold text-[#1d3c34] hover:underline">Public Site</a>
                </div>
            </div>
        </main>
    </div>

    <!-- INITIAL DATA PAYLOAD AND APEXCHARTS JAVASCRIPT LOGIC -->
    <script>
        // Initial server-rendered payload
        window.analyticsData = @json($payload);
        window.currentPeriod = @json($currentPeriod);
        window.csrfToken = @json(csrf_token());

        document.addEventListener('DOMContentLoaded', function () {
            // Mobile sidebar toggle
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebarNav = document.getElementById('sidebar-nav');
            if (sidebarToggle && sidebarNav) {
                sidebarToggle.addEventListener('click', function () {
                    sidebarNav.classList.toggle('hidden');
                });
            }

            // Custom Range Form Toggle
            const toggleCustomBtn = document.getElementById('toggle-custom-filter');
            const customRangeContainer = document.getElementById('custom-range-container');
            const cancelCustomBtn = document.getElementById('cancel-custom-range');
            const customForm = document.getElementById('custom-range-form');

            if (toggleCustomBtn && customRangeContainer) {
                toggleCustomBtn.addEventListener('click', function () {
                    customRangeContainer.classList.toggle('hidden');
                });
            }
            if (cancelCustomBtn && customRangeContainer) {
                cancelCustomBtn.addEventListener('click', function () {
                    customRangeContainer.classList.add('hidden');
                });
            }

            // Format Helpers
            const formatNumber = (num) => new Intl.NumberFormat('en-US').format(num);
            const formatCurrency = (amount) => new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount);

            // Chart Instances Storage
            const charts = {};

            // -------------------------------------------------------------
            // 1. CHART 1: MONTHLY TRENDS (LINE / AREA)
            // -------------------------------------------------------------
            const trendsOptions = {
                chart: {
                    type: 'area',
                    height: 380,
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    },
                    fontFamily: 'inherit',
                    background: 'transparent'
                },
                series: [
                    {
                        name: 'Properties Sold',
                        type: 'line',
                        data: window.analyticsData.monthly_trends.sales
                    },
                    {
                        name: 'Properties Rented',
                        type: 'line',
                        data: window.analyticsData.monthly_trends.rentals
                    },
                    {
                        name: 'Revenue (ETB)',
                        type: 'column',
                        data: window.analyticsData.monthly_trends.revenue
                    }
                ],
                colors: ['#1d3c34', '#3b82f6', '#d4af37'],
                stroke: {
                    curve: 'smooth',
                    width: [3, 3, 0]
                },
                fill: {
                    type: ['gradient', 'gradient', 'solid'],
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.35,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: window.analyticsData.monthly_trends.categories,
                    labels: {
                        style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 }
                    },
                    axisBorder: { color: '#e2e8f0' },
                    axisTicks: { color: '#e2e8f0' }
                },
                yaxis: [
                    {
                        title: { text: 'Transactions (Deals)', style: { color: '#1d3c34', fontWeight: 700, fontSize: '12px' } },
                        labels: {
                            formatter: (val) => Math.round(val),
                            style: { colors: '#64748b' }
                        },
                        min: 0
                    },
                    {
                        show: false,
                        min: 0
                    },
                    {
                        opposite: true,
                        title: { text: 'Revenue (ETB)', style: { color: '#d4af37', fontWeight: 700, fontSize: '12px' } },
                        labels: {
                            formatter: (val) => val >= 1000000 ? (val / 1000000).toFixed(1) + 'M' : (val >= 1000 ? (val / 1000).toFixed(0) + 'K' : val),
                            style: { colors: '#64748b' }
                        },
                        min: 0
                    }
                ],
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: function (val, { seriesIndex }) {
                            if (seriesIndex === 2) {
                                return formatCurrency(val) + ' ETB';
                            }
                            return val + ' deals';
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontWeight: 600
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 3
                }
            };
            charts.trends = new ApexCharts(document.querySelector("#chart-monthly-trends"), trendsOptions);
            charts.trends.render();

            // -------------------------------------------------------------
            // 2. CHART 2: AGENT PERFORMANCE (BAR CHART)
            // -------------------------------------------------------------
            const agentOptions = {
                chart: {
                    type: 'bar',
                    height: 340,
                    toolbar: { show: false },
                    fontFamily: 'inherit'
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        borderRadius: 6,
                        dataLabels: { position: 'top' }
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    style: {
                        fontSize: '11px',
                        colors: ["#1d3c34"],
                        fontWeight: 700
                    }
                },
                series: [
                    {
                        name: 'Properties Sold',
                        data: window.analyticsData.agent_performance.sold
                    },
                    {
                        name: 'Properties Rented',
                        data: window.analyticsData.agent_performance.rented
                    }
                ],
                colors: ['#1d3c34', '#d4af37'],
                xaxis: {
                    categories: window.analyticsData.agent_performance.agents,
                    labels: {
                        style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 }
                    }
                },
                yaxis: {
                    title: { text: 'Number of Properties' },
                    min: 0,
                    forceNiceScale: true
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontWeight: 600
                },
                grid: { borderColor: '#f1f5f9' },
                tooltip: {
                    y: { formatter: (val) => val + ' deals' }
                }
            };
            charts.agents = new ApexCharts(document.querySelector("#chart-agent-performance"), agentOptions);
            charts.agents.render();

            // -------------------------------------------------------------
            // 3. CHART 3: PROPERTY STATUS (DONUT CHART)
            // -------------------------------------------------------------
            const donutOptions = {
                chart: {
                    type: 'donut',
                    height: 340,
                    fontFamily: 'inherit'
                },
                series: window.analyticsData.property_status.series,
                labels: window.analyticsData.property_status.labels,
                colors: ['#1d3c34', '#3b82f6', '#d4af37'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                name: { show: true, fontSize: '14px', fontWeight: 700, color: '#1d3c34' },
                                value: { show: true, fontSize: '20px', fontWeight: 900, color: '#1d3c34' },
                                total: {
                                    show: true,
                                    label: 'Total Units',
                                    color: '#64748b',
                                    fontWeight: 700,
                                    formatter: () => window.analyticsData.property_status.totals
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        return Math.round(val) + '%';
                    }
                },
                legend: {
                    position: 'bottom',
                    fontWeight: 600
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            const total = window.analyticsData.property_status.totals;
                            const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                            return val + ' properties (' + pct + '%)';
                        }
                    }
                }
            };
            charts.donut = new ApexCharts(document.querySelector("#chart-property-status"), donutOptions);
            charts.donut.render();

            // -------------------------------------------------------------
            // 4. CHART 4: RENT VS SALE (STACKED BAR CHART)
            // -------------------------------------------------------------
            const rentVsSaleOptions = {
                chart: {
                    type: 'bar',
                    height: 340,
                    stacked: true,
                    toolbar: { show: false },
                    fontFamily: 'inherit'
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '50%',
                        borderRadius: 4
                    }
                },
                series: [
                    {
                        name: 'Sale Deals',
                        data: window.analyticsData.rent_vs_sale.sale_deals
                    },
                    {
                        name: 'Rent Deals',
                        data: window.analyticsData.rent_vs_sale.rent_deals
                    }
                ],
                colors: ['#1d3c34', '#3b82f6'],
                xaxis: {
                    categories: window.analyticsData.rent_vs_sale.categories,
                    labels: {
                        style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 }
                    }
                },
                yaxis: {
                    title: { text: 'Deals Count' },
                    min: 0,
                    forceNiceScale: true
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontWeight: 600
                },
                grid: { borderColor: '#f1f5f9' },
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: { formatter: (val) => val + ' transactions' }
                }
            };
            charts.rentVsSale = new ApexCharts(document.querySelector("#chart-rent-vs-sale"), rentVsSaleOptions);
            charts.rentVsSale.render();

            // -------------------------------------------------------------
            // 5. CHART 5: LOCATIONS (HORIZONTAL BAR CHART)
            // -------------------------------------------------------------
            const locationOptions = {
                chart: {
                    type: 'bar',
                    height: 340,
                    toolbar: { show: false },
                    fontFamily: 'inherit'
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '55%',
                        borderRadius: 5,
                        dataLabels: { position: 'top' }
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetX: 20,
                    style: {
                        fontSize: '11px',
                        fontWeight: 700,
                        colors: ['#1d3c34']
                    }
                },
                series: [
                    {
                        name: 'Properties',
                        data: window.analyticsData.locations.counts
                    }
                ],
                colors: ['#d4af37'],
                xaxis: {
                    categories: window.analyticsData.locations.cities,
                    labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } }
                },
                yaxis: {
                    labels: { style: { colors: '#1d3c34', fontSize: '12px', fontWeight: 700 } }
                },
                grid: { borderColor: '#f1f5f9' },
                tooltip: {
                    y: { formatter: (val) => val + ' properties' }
                }
            };
            charts.locations = new ApexCharts(document.querySelector("#chart-locations"), locationOptions);
            charts.locations.render();

            // -------------------------------------------------------------
            // 6. CHART 6: SCATTER CHART (PRICE VS AREA)
            // -------------------------------------------------------------
            const scatterSaleData = window.analyticsData.scatter.sale_points.map(p => ({
                x: p.x,
                y: p.y,
                name: p.name,
                city: p.city,
                type: p.type
            }));
            const scatterRentData = window.analyticsData.scatter.rent_points.map(p => ({
                x: p.x,
                y: p.y,
                name: p.name,
                city: p.city,
                type: p.type
            }));

            const scatterOptions = {
                chart: {
                    type: 'scatter',
                    height: 400,
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        }
                    },
                    zoom: { enabled: true, type: 'xy' },
                    fontFamily: 'inherit'
                },
                series: [
                    {
                        name: 'Properties for Sale',
                        data: scatterSaleData
                    },
                    {
                        name: 'Properties for Rent / Stay',
                        data: scatterRentData
                    }
                ],
                colors: ['#1d3c34', '#d4af37'],
                xaxis: {
                    type: 'numeric',
                    title: { text: 'Property Area (m²)', style: { fontWeight: 700, color: '#1d3c34', fontSize: '12px' } },
                    labels: {
                        formatter: (val) => val + ' m²',
                        style: { colors: '#64748b' }
                    },
                    tickAmount: 8
                },
                yaxis: {
                    title: { text: 'Listing Price (ETB)', style: { fontWeight: 700, color: '#1d3c34', fontSize: '12px' } },
                    labels: {
                        formatter: (val) => val >= 1000000 ? (val / 1000000).toFixed(1) + 'M ETB' : (val >= 1000 ? (val / 1000).toFixed(0) + 'K ETB' : val + ' ETB'),
                        style: { colors: '#64748b' }
                    }
                },
                markers: {
                    size: 7,
                    hover: { size: 10 }
                },
                tooltip: {
                    custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                        const point = w.config.series[seriesIndex].data[dataPointIndex];
                        if (!point) return '';
                        const priceM2 = point.x > 0 ? (point.y / point.x).toFixed(2) : '0';
                        return `
                            <div class="p-3 text-xs">
                                <div class="font-black text-[#d4af37] text-sm mb-1">${point.name}</div>
                                <div class="text-slate-300 mb-0.5">Location: <span class="text-white font-semibold">${point.city}</span></div>
                                <div class="text-slate-300 mb-0.5">Type: <span class="text-white font-semibold">${point.type}</span></div>
                                <div class="text-slate-300 mb-0.5">Area: <span class="text-white font-bold">${point.x} m²</span></div>
                                <div class="text-slate-300 mb-0.5">Price: <span class="text-emerald-400 font-bold">${formatCurrency(point.y)} ETB</span></div>
                                <div class="text-slate-400 text-[10px] mt-1 pt-1 border-t border-white/20">Valuation: ${formatCurrency(priceM2)} ETB/m²</div>
                            </div>
                        `;
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontWeight: 600
                },
                grid: { borderColor: '#f1f5f9' }
            };
            charts.scatter = new ApexCharts(document.querySelector("#chart-price-area-scatter"), scatterOptions);
            charts.scatter.render();

            // -------------------------------------------------------------
            // DYNAMIC DATE FILTER LOGIC VIA AJAX
            // -------------------------------------------------------------
            const loadingIndicator = document.getElementById('loading-indicator');
            const errorBanner = document.getElementById('error-banner');
            const errorMessage = document.getElementById('error-message');
            const retryBtn = document.getElementById('retry-btn');
            const periodButtons = document.querySelectorAll('.period-filter-btn');
            let lastFilterParams = { period: window.currentPeriod };

            const showLoading = () => loadingIndicator && loadingIndicator.classList.remove('hidden');
            const hideLoading = () => loadingIndicator && loadingIndicator.classList.add('hidden');
            const showError = (msg) => {
                if (errorBanner && errorMessage) {
                    errorMessage.textContent = msg || 'An error occurred while fetching analytics.';
                    errorBanner.classList.remove('hidden');
                }
            };
            const hideError = () => errorBanner && errorBanner.classList.add('hidden');

            function fetchAnalyticsData(params) {
                lastFilterParams = params;
                hideError();
                showLoading();

                const url = new URL('{{ route("dashboard.analytics.data") }}', window.location.origin);
                Object.keys(params).forEach(key => {
                    if (params[key]) url.searchParams.append(key, params[key]);
                });

                fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(result => {
                    hideLoading();
                    if (!result.success || !result.data) {
                        throw new Error('Malformed server response');
                    }
                    updateDashboardView(result.data, result.date_range);
                })
                .catch(err => {
                    hideLoading();
                    console.error('Error fetching analytics:', err);
                    showError('Failed to refresh analytics: ' + err.message);
                });
            }

            function updateDashboardView(data, dateRange) {
                window.analyticsData = data;

                // 1. Update Range Badge & Active Button Styles
                const badge = document.getElementById('active-range-badge');
                if (badge && dateRange.label) {
                    badge.textContent = dateRange.label;
                }

                periodButtons.forEach(btn => {
                    if (btn.dataset.period === dateRange.period) {
                        btn.className = 'period-filter-btn rounded-xl px-3.5 py-1.5 text-xs font-bold transition cursor-pointer bg-[#1d3c34] text-white shadow-sm';
                    } else {
                        btn.className = 'period-filter-btn rounded-xl px-3.5 py-1.5 text-xs font-bold transition cursor-pointer text-[#1d3c34] hover:bg-white/40';
                    }
                });

                // 2. Update KPI values
                const kpis = data.kpis;
                if (kpis) {
                    const elUsers = document.getElementById('kpi-total-users');
                    const elAgents = document.getElementById('kpi-total-agents');
                    const elProps = document.getElementById('kpi-total-properties');
                    const elAvail = document.getElementById('kpi-available-properties');
                    const elRented = document.getElementById('kpi-rented-properties');
                    const elSold = document.getElementById('kpi-sold-properties');
                    const elRev = document.getElementById('kpi-total-revenue');
                    const elComm = document.getElementById('kpi-total-commission');

                    if (elUsers) elUsers.textContent = formatNumber(kpis.total_users);
                    if (elAgents) elAgents.textContent = formatNumber(kpis.total_agents);
                    if (elProps) elProps.textContent = formatNumber(kpis.total_properties);
                    if (elAvail) elAvail.textContent = formatNumber(kpis.available_properties);
                    if (elRented) elRented.textContent = formatNumber(kpis.rented_properties);
                    if (elSold) elSold.textContent = formatNumber(kpis.sold_properties);
                    if (elRev) elRev.textContent = formatCurrency(kpis.total_revenue);
                    if (elComm) elComm.textContent = formatCurrency(kpis.total_commission);
                }

                // 3. Update Chart 1: Monthly Trends
                const trends = data.monthly_trends;
                if (charts.trends && trends) {
                    charts.trends.updateOptions({
                        xaxis: { categories: trends.categories }
                    });
                    charts.trends.updateSeries([
                        { name: 'Properties Sold', data: trends.sales },
                        { name: 'Properties Rented', data: trends.rentals },
                        { name: 'Revenue (ETB)', data: trends.revenue }
                    ]);
                }

                // 4. Update Chart 2: Agent Performance
                const agentData = data.agent_performance;
                if (charts.agents && agentData) {
                    charts.agents.updateOptions({
                        xaxis: { categories: agentData.agents }
                    });
                    charts.agents.updateSeries([
                        { name: 'Properties Sold', data: agentData.sold },
                        { name: 'Properties Rented', data: agentData.rented }
                    ]);

                    // Update summary table
                    const tbody = document.getElementById('agent-summary-tbody');
                    if (tbody) {
                        tbody.innerHTML = '';
                        if (agentData.details && agentData.details.length > 0) {
                            agentData.details.forEach(a => {
                                const tr = document.createElement('tr');
                                tr.className = 'hover:bg-slate-50';
                                tr.innerHTML = `
                                    <td class="py-2 font-semibold text-[#1d3c34]">${a.name}</td>
                                    <td class="py-2 text-center font-bold text-[#b8860b]">${a.sold}</td>
                                    <td class="py-2 text-center font-bold text-blue-700">${a.rented}</td>
                                    <td class="py-2 text-right font-black text-[#1d3c34]">${a.total_deals}</td>
                                `;
                                tbody.appendChild(tr);
                            });
                        } else {
                            tbody.innerHTML = '<tr><td colspan="4" class="py-3 text-center text-slate-400">No agent transactions recorded</td></tr>';
                        }
                    }
                }

                // 5. Update Chart 3: Property Status Donut
                const status = data.property_status;
                if (charts.donut && status) {
                    charts.donut.updateSeries(status.series);
                    charts.donut.updateOptions({
                        plotOptions: {
                            pie: {
                                donut: {
                                    labels: {
                                        total: {
                                            formatter: () => status.totals
                                        }
                                    }
                                }
                            }
                        }
                    });

                    const totalBadge = document.getElementById('donut-total-badge');
                    if (totalBadge) totalBadge.textContent = status.totals + ' Properties';

                    const statAvail = document.getElementById('donut-stat-available');
                    const statRented = document.getElementById('donut-stat-rented');
                    const statSold = document.getElementById('donut-stat-sold');
                    const pctAvail = document.getElementById('donut-pct-available');
                    const pctRented = document.getElementById('donut-pct-rented');
                    const pctSold = document.getElementById('donut-pct-sold');

                    if (statAvail) statAvail.textContent = status.counts.available;
                    if (statRented) statRented.textContent = status.counts.rented;
                    if (statSold) statSold.textContent = status.counts.sold;
                    if (pctAvail) pctAvail.textContent = status.percentages.available + '%';
                    if (pctRented) pctRented.textContent = status.percentages.rented + '%';
                    if (pctSold) pctSold.textContent = status.percentages.sold + '%';
                }

                // 6. Update Chart 4: Rent vs Sale
                const rentVsSale = data.rent_vs_sale;
                if (charts.rentVsSale && rentVsSale) {
                    charts.rentVsSale.updateOptions({
                        xaxis: { categories: rentVsSale.categories }
                    });
                    charts.rentVsSale.updateSeries([
                        { name: 'Sale Deals', data: rentVsSale.sale_deals },
                        { name: 'Rent Deals', data: rentVsSale.rent_deals }
                    ]);
                }

                // 7. Update Chart 5: Locations
                const locations = data.locations;
                if (charts.locations && locations) {
                    charts.locations.updateOptions({
                        xaxis: { categories: locations.cities }
                    });
                    charts.locations.updateSeries([
                        { name: 'Properties', data: locations.counts }
                    ]);
                }

                // 8. Update Chart 6: Scatter Plot
                const scatter = data.scatter;
                if (charts.scatter && scatter) {
                    const newSaleData = scatter.sale_points.map(p => ({
                        x: p.x, y: p.y, name: p.name, city: p.city, type: p.type
                    }));
                    const newRentData = scatter.rent_points.map(p => ({
                        x: p.x, y: p.y, name: p.name, city: p.city, type: p.type
                    }));
                    charts.scatter.updateSeries([
                        { name: 'Properties for Sale', data: newSaleData },
                        { name: 'Properties for Rent / Stay', data: newRentData }
                    ]);

                    const scatterBadge = document.getElementById('scatter-count-badge');
                    if (scatterBadge) scatterBadge.textContent = scatter.total_points + ' Data Points';
                }
            }

            // Period Button Click Handlers
            periodButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const period = this.dataset.period;
                    if (customRangeContainer) customRangeContainer.classList.add('hidden');
                    fetchAnalyticsData({ period: period });
                });
            });

            // Custom Range Form Submit Handler
            if (customForm) {
                customForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const from = document.getElementById('custom_from').value;
                    const to = document.getElementById('custom_to').value;
                    if (!from || !to) {
                        alert('Please select both a start date and an end date.');
                        return;
                    }
                    fetchAnalyticsData({ period: 'custom', from: from, to: to });
                });
            }

            // Retry Handler
            if (retryBtn) {
                retryBtn.addEventListener('click', function () {
                    fetchAnalyticsData(lastFilterParams);
                });
            }
        });
    </script>
    @include('layouts.partials.sidebar-scroll-preserver')
</body>
</html>
