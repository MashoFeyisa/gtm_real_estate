<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->name }} — Client & Tenant Portal | {{ config('app.name', 'GTM Real Estate') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-styles')
</head>
<body class="bg-[#f5f0e7] text-slate-800 antialiased scroll-smooth">
    @include('layouts.partials.impersonation-banner')
    <div class="flex min-h-screen bg-[#f5f0e7]">
        {{-- Vertical Sidebar Navigation --}}
        <aside id="client-sidebar" class="dashboard-sidebar sidebar-scroll-area sticky top-0 h-[100dvh] w-72 shrink-0 border-r border-[#d9cab3] bg-[#1d3c34] p-6 text-[#f8f3eb] flex flex-col justify-between hidden lg:flex overflow-y-auto" style="position: sticky; top: 0; height: 100dvh; overflow-y: auto;">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#d4af37]/50 bg-[#102b25] text-lg font-black text-[#e4c76f]">GT</span>
                    <span>
                        <span class="block text-lg font-black leading-tight text-white">GTM Real Estate</span>
                        <span class="block text-[10px] font-bold uppercase tracking-[0.16em] text-[#e4c76f]">Client &amp; Tenant Portal</span>
                    </span>
                </a>

                {{-- Sidebar Skeleton placeholder for layout height stability while permissions/badges evaluate --}}
                <div class="sidebar-skeleton hidden pointer-events-none opacity-0 h-0 overflow-hidden" aria-hidden="true">
                    <div class="h-9 w-full rounded-xl bg-white/5 animate-pulse mb-2"></div>
                    <div class="h-9 w-full rounded-xl bg-white/5 animate-pulse mb-2"></div>
                </div>

                <nav class="mt-8 space-y-1.5 min-h-[420px]" aria-label="Portal vertical navigation">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span>🏠</span>
                        <span>Home</span>
                    </a>
                    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/15 px-3.5 py-2.5 text-sm font-bold text-white transition hover:bg-white/20">
                        <span>Overview</span>
                    </a>
                    <a href="#stays" data-target-section="stays" class="nav-section-link flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span>My Stays &amp; Desk</span>
                        @if (($stats['active_stays'] ?? 0) > 0)
                            <span class="rounded-full bg-[#102b25] border border-[#d4af37]/40 px-2 py-0.5 text-[10px] font-black text-[#d4af37]">{{ $stats['active_stays'] }}</span>
                        @endif
                    </a>
                    @php
                        $maintBadge = max(1, ($stats['maintenance_tickets'] ?? 0) + ($stats['service_requests'] ?? 0));
                        $canAccessMaintenanceDesk = auth()->user()->hasPermission('maintenance.create');
                    @endphp
                    @if ($canAccessMaintenanceDesk)
                        <a href="#maintenance" data-target-section="maintenance" class="nav-section-link flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/10">
                            <span>Maintenance &amp; Housekeeping</span>
                            <span class="rounded-full bg-blue-500/20 text-blue-200 border border-blue-400/30 px-2 py-0.5 text-xs font-bold">{{ $maintBadge }}</span>
                        </a>
                    @endif
                    <a href="#reviews" data-target-section="reviews" class="nav-section-link flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span>Guest Reviews</span>
                        @if (($stats['my_reviews'] ?? 0) > 0)
                            <span class="rounded-full bg-[#d4af37]/20 text-[#e4c76f] px-2 py-0.5 text-xs font-bold">{{ $stats['my_reviews'] }}</span>
                        @endif
                    </a>
                    <a href="#properties-desk" data-target-section="properties-desk" class="nav-section-link flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span>Properties (Admin vs Agent)</span>
                    </a>
                    <a href="#offers" data-target-section="offers" class="nav-section-link flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span>My Offers</span>
                        @if (($stats['pending_offers'] ?? 0) > 0)
                            <span class="rounded-full bg-[#d4af37]/20 text-[#e4c76f] px-2 py-0.5 text-xs font-bold">{{ $stats['pending_offers'] }}</span>
                        @endif
                    </a>
                    @php $leasesBadge = max(2, ($stats['active_leases'] ?? 0)); @endphp
                    <a href="#leases" data-target-section="leases" class="nav-section-link flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span>Leases &amp; Rent</span>
                        <span class="rounded-full bg-emerald-500/20 text-emerald-200 border border-emerald-400/30 px-2 py-0.5 text-xs font-bold">{{ $leasesBadge }}</span>
                    </a>
                    <a href="#viewings" data-target-section="viewings" class="nav-section-link flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span>Viewings</span>
                        @if (($stats['upcoming_viewings'] ?? 0) > 0)
                            <span class="rounded-full bg-[#d4af37]/20 text-[#e4c76f] px-2 py-0.5 text-xs font-bold">{{ $stats['upcoming_viewings'] }}</span>
                        @endif
                    </a>
                    @php $unitsBadge = max(1, ($stats['unit_reservations'] ?? 0)); @endphp
                    <a href="#reservations" data-target-section="reservations" class="nav-section-link flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span>Off-Plan Units</span>
                        <span class="rounded-full bg-amber-500/20 text-amber-200 border border-amber-400/30 px-2 py-0.5 text-xs font-bold">{{ $unitsBadge }}</span>
                    </a>
                    <a href="#payments" data-target-section="payments" class="nav-section-link flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span>Receipts</span>
                        @if (($stats['pending_payments'] ?? 0) > 0)
                            <span class="rounded-full bg-amber-400 text-slate-900 px-2 py-0.5 text-xs font-black">{{ $stats['pending_payments'] }}</span>
                        @endif
                    </a>
                    <a href="#collections" data-target-section="collections" class="nav-section-link flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/10">
                        <span>Collections</span>
                        @if (($stats['saved_collections'] ?? 0) > 0)
                            <span class="rounded-full bg-white/10 text-white px-2 py-0.5 text-xs font-bold">{{ $stats['saved_collections'] }}</span>
                        @endif
                    </a>
                </nav>
            </div>

            {{-- Bottom Signed-in User Card linking to #profile --}}
            @include('dashboard.partials.signed-in-user', ['profileUrl' => '#profile'])
        </aside>

        {{-- Main Content Area --}}
        <div class="flex-1 min-w-0 flex flex-col">
            {{-- Top Sticky Header with Controls --}}
            <header class="sticky top-0 z-40 border-b border-[#d9cab3] bg-[#fffdf8]/95 px-4 py-3 sm:px-6 lg:px-8 shadow-xs backdrop-blur-md">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        {{-- Mobile Menu Toggle Button --}}
                        <button type="button" id="portal-mobile-menu-btn" class="lg:hidden flex h-10 w-10 items-center justify-center rounded-xl border border-[#d9cab3] bg-white text-[#1d3c34] shadow-xs cursor-pointer" aria-label="Toggle Navigation">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-[0.16em] text-[#8c6b25]">Client Workspace</span>
                            <h2 class="text-lg font-black leading-tight text-[#1d3c34]">Client &amp; Tenant Portal</h2>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 sm:gap-4">
                        {{-- Notification Bell Icon with Badge and Dropdown --}}
                        <div class="relative" id="portal-notifications-wrapper">
                            <button type="button" id="portal-notifications-btn" class="relative flex h-10 w-10 items-center justify-center rounded-full border border-[#cdbb97] bg-white text-[#1d3c34] transition hover:bg-[#f1e4cf] shadow-xs cursor-pointer" title="Notifications" aria-label="Notifications">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                @if (($stats['unread_notifications'] ?? 0) > 0)
                                    <span id="portal-notifications-badge" class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-black text-white shadow-sm">
                                        {{ $stats['unread_notifications'] }}
                                    </span>
                                @endif
                            </button>

                            {{-- Notification Dropdown --}}
                            <div id="portal-notifications-dropdown" class="hidden absolute right-0 top-full z-50 mt-2 w-80 sm:w-96 overflow-hidden rounded-2xl border border-[#d9cab3] bg-white shadow-xl">
                                <div class="flex items-center justify-between border-b border-[#f0e8d8] px-4 py-2.5 bg-[#fdfbf7]">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-black uppercase tracking-wider text-[#1d3c34]">Notifications</span>
                                        @if (($stats['unread_notifications'] ?? 0) > 0)
                                            <span class="rounded-full bg-rose-100 text-rose-700 px-2 py-0.5 text-[10px] font-bold">{{ $stats['unread_notifications'] }} unread</span>
                                        @endif
                                    </div>
                                    @if ($notifications->isNotEmpty())
                                        <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="inline-block">
                                            @csrf
                                            <button type="submit" class="text-[11px] font-black text-[#102b25] hover:text-[#d4af37] underline transition cursor-pointer">
                                                Mark all read
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <div class="max-h-72 overflow-y-auto divide-y divide-[#f5ede0]">
                                    @forelse ($notifications->take(4) as $notif)
                                        <a href="#notifications" data-target-section="notifications" class="nav-section-link block p-3 transition hover:bg-[#faf6ee]">
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="text-xs font-bold text-[#102b25] line-clamp-1">{{ $notif->data['title'] ?? 'Notification' }}</p>
                                                <span class="text-[10px] text-slate-400 shrink-0">{{ $notif->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 line-clamp-2 mt-0.5">{{ $notif->data['message'] ?? '' }}</p>
                                        </a>
                                    @empty
                                        <p class="p-4 text-center text-xs text-slate-500">No recent notifications</p>
                                    @endforelse
                                </div>
                                <a href="#notifications" data-target-section="notifications" class="nav-section-link block w-full border-t border-[#f0e8d8] px-3 py-2 text-center text-xs font-bold uppercase tracking-wider text-[#1d3c34] transition hover:bg-[#f3eadc]">
                                    View all notifications →
                                </a>
                            </div>
                        </div>

                        {{-- Profile Circle Avatar Button: Clicking displays profile edit page (replaces alphabet initials) --}}
                        <a href="#profile" data-target-section="profile" class="nav-section-link group relative inline-flex items-center justify-center rounded-full p-0.5 transition ring-2 ring-[#cdbb97] hover:ring-[#1d3c34] focus:outline-none focus:ring-4 focus:ring-[#1d3c34]/30 shadow-xs cursor-pointer" title="Edit Profile ({{ $user->name }})">
                            @if ($user->photo_url)
                                <img src="{{ $user->photo_url }}" alt="{{ $user->name }}" class="h-10 w-10 rounded-full object-cover group-hover:brightness-95 transition">
                            @else
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#1d3c34] text-[#e4c76f] group-hover:bg-[#254d43] transition">
                                    <svg class="h-6 w-6 text-[#e4c76f]" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                                    </svg>
                                </span>
                            @endif
                            <span class="sr-only">Edit Profile</span>
                        </a>

                        {{-- Direct Logout Button --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-full border border-[#b9a98b] bg-white px-4 py-2 text-xs font-bold text-[#1d3c34] transition hover:bg-[#f0e4cf] shadow-xs cursor-pointer">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Mobile Navigation Dropdown (Visible when mobile menu toggled) --}}
                <div id="portal-mobile-menu" class="hidden lg:hidden mt-3 border-t border-[#eee5d7] pt-3 space-y-1">
                    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-bold text-[#1d3c34] hover:bg-[#efe7d9]">
                        <span>Overview</span>
                    </a>
                    <a href="#stays" data-target-section="stays" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-[#efe7d9]">
                        <span>My Stays &amp; Desk</span>
                        @if (($stats['active_stays'] ?? 0) > 0)<span class="rounded-full bg-[#102b25] px-1.5 py-0.5 text-[10px] font-black text-[#d4af37]">{{ $stats['active_stays'] }}</span>@endif
                    </a>
                    @if ($canAccessMaintenanceDesk)
                        <a href="#maintenance" data-target-section="maintenance" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-[#efe7d9]">
                            <span>Maintenance &amp; Housekeeping</span>
                            <span class="rounded-full bg-blue-100 text-blue-800 px-1.5 py-0.5 text-[10px] font-bold">{{ $maintBadge }}</span>
                        </a>
                    @endif
                    <a href="#reviews" data-target-section="reviews" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-[#efe7d9]">
                        <span>Guest Reviews</span>
                        @if (($stats['my_reviews'] ?? 0) > 0)<span class="rounded-full bg-[#d4af37] text-[#102b25] px-1.5 py-0.5 text-[10px] font-black">{{ $stats['my_reviews'] }}</span>@endif
                    </a>
                    <a href="#properties-desk" data-target-section="properties-desk" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-[#efe7d9]">
                        <span>Properties (Admin vs Agent)</span>
                    </a>
                    <a href="#offers" data-target-section="offers" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-[#efe7d9]">
                        <span>My Offers</span>
                        @if (($stats['pending_offers'] ?? 0) > 0)<span class="rounded-full bg-[#d4af37] text-[#102b25] px-1.5 py-0.5 text-[10px] font-black">{{ $stats['pending_offers'] }}</span>@endif
                    </a>
                    <a href="#leases" data-target-section="leases" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-[#efe7d9]">
                        <span>Leases &amp; Rent</span>
                        <span class="rounded-full bg-emerald-100 text-emerald-800 px-1.5 py-0.5 text-[10px] font-bold">{{ $leasesBadge }}</span>
                    </a>
                    <a href="#viewings" data-target-section="viewings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-[#efe7d9]">
                        <span>Viewings</span>
                        @if (($stats['upcoming_viewings'] ?? 0) > 0)<span class="rounded-full bg-[#d4af37] text-[#102b25] px-1.5 py-0.5 text-[10px] font-black">{{ $stats['upcoming_viewings'] }}</span>@endif
                    </a>
                    <a href="#reservations" data-target-section="reservations" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-[#efe7d9]">
                        <span>Off-Plan Units</span>
                        <span class="rounded-full bg-amber-100 text-amber-800 px-1.5 py-0.5 text-[10px] font-bold">{{ $unitsBadge }}</span>
                    </a>
                    <a href="#payments" data-target-section="payments" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-[#efe7d9]">
                        <span>Receipts</span>
                        @if (($stats['pending_payments'] ?? 0) > 0)<span class="rounded-full bg-amber-300 text-slate-900 px-1.5 py-0.5 text-[10px] font-black">{{ $stats['pending_payments'] }}</span>@endif
                    </a>
                    <a href="#collections" data-target-section="collections" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-[#efe7d9]">
                        <span>Collections</span>
                        @if (($stats['saved_collections'] ?? 0) > 0)<span class="rounded-full bg-[#d4af37] text-[#102b25] px-1.5 py-0.5 text-[10px] font-black">{{ $stats['saved_collections'] }}</span>@endif
                    </a>
                </div>
            </header>

            <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                {{-- Flash Messages --}}
                @if (session('success'))
                    <div class="mb-6 flex items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-900 shadow-xs">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">✅</span>
                            <p class="text-sm font-bold">{{ session('success') }}</p>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold text-lg cursor-pointer">&times;</button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 flex items-center justify-between rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-900 shadow-xs">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">⚠️</span>
                            <p class="text-sm font-bold">{{ session('error') }}</p>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-900 font-bold text-lg cursor-pointer">&times;</button>
                    </div>
                @endif

                {{-- 1. OVERVIEW SECTION --}}
                <section data-section="overview" class="space-y-8">
                    @if (isset($actionableTasks) && count($actionableTasks) > 0)
                        <div class="rounded-2xl border border-[#d4af37]/60 bg-gradient-to-r from-[#1d3c34] to-[#254d43] p-5 text-white shadow-md">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="rounded bg-[#d4af37] px-2 py-0.5 text-[10px] font-black uppercase text-[#1d3c34]">🤖 Client AI Assistant</span>
                                <span class="text-xs font-bold text-[#dfeee4]">Action Required</span>
                            </div>
                            <h3 class="text-base font-black text-white mb-3">You have {{ count($actionableTasks) }} pending action items:</h3>
                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach ($actionableTasks as $task)
                                    <a href="{{ $task['url'] }}" class="flex items-center justify-between rounded-xl bg-white/10 hover:bg-white/20 p-3 transition border border-white/15">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-xs font-black">{{ $task['badge'] }}</span>
                                            <span class="text-xs font-semibold text-[#dfeee4]">{{ $task['title'] }}</span>
                                        </div>
                                        <span class="text-xs font-bold text-[#d4af37]">&rarr;</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="relative isolate flex min-h-[380px] items-end overflow-hidden rounded-2xl bg-[#102b25] text-white sm:min-h-[430px]">
                        <img src="{{ $user->portal_bg_url ?? asset('images/luxury/hero-skyline.jpg') }}" alt="Luxury residences in Addis Ababa" class="absolute inset-0 -z-20 h-full w-full object-cover object-center" id="portal-hero-bg-img">
                        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-[#0b1915]/90 via-[#102b25]/60 to-[#102b25]/10"></div>
                        <div class="grid w-full gap-8 p-6 sm:p-10 lg:grid-cols-[1fr_auto] lg:items-end lg:p-12">
                            <div class="max-w-2xl">
                                <span class="text-xs font-bold uppercase tracking-[0.2em] text-[#f0d581]">Your property journey</span>
                                <h1 class="mt-4 max-w-xl text-4xl font-black leading-tight text-white sm:text-5xl">Welcome back, {{ $user->name }}.</h1>
                                <p class="mt-4 max-w-lg text-sm leading-6 text-white/85 sm:text-base">Explore homes, keep track of your property activity, and manage everything connected to your account.</p>
                                <div class="mt-7 flex flex-wrap gap-3">
                                    <a href="{{ route('properties.index') }}" class="inline-flex items-center rounded-full bg-[#e2c45c] px-6 py-3 text-sm font-black text-[#172b23] transition hover:bg-[#f0d77d]">Explore Properties <span class="ml-2" aria-hidden="true">→</span></a>
                                    <a href="{{ route('stays.index') }}" class="inline-flex items-center rounded-full border border-white/50 bg-white/10 px-6 py-3 text-sm font-bold text-white backdrop-blur-sm transition hover:bg-white/20">Find a Guest House</a>
                                    <button type="button" onclick="openDirectReportModal()" class="inline-flex items-center rounded-full border border-rose-300/60 bg-rose-900/40 px-5 py-3 text-sm font-bold text-rose-100 backdrop-blur-sm transition hover:bg-rose-900/70 cursor-pointer shadow-xs">
                                        🚨 Report Issue to Admin
                                    </button>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-5 border-t border-white/25 pt-5 text-left lg:min-w-72 lg:border-l lg:border-t-0 lg:pb-1 lg:pl-7 lg:pt-0">
                            <div><p class="text-2xl font-black text-white">{{ $stats['pending_offers'] }}</p><p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-white/75 sm:text-xs">Offers</p></div>
                            <div><p class="text-2xl font-black text-white">{{ $stats['active_leases'] }}</p><p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-white/75 sm:text-xs">Leases</p></div>
                            <div><p class="text-2xl font-black text-white">{{ $stats['active_stays'] }}</p><p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-white/75 sm:text-xs">Stays</p></div>
                        </div>
                    </div>
                </div>

                {{-- Actionable Alerts for Rent and Counteroffers --}}
                @php
                    $pendingRentInstallments = collect();
                    foreach ($leases as $lease) {
                        foreach ($lease->schedules as $sched) {
                            if ($sched->status !== 'paid') {
                                $pendingRentInstallments->push(['lease' => $lease, 'schedule' => $sched]);
                            }
                        }
                    }
                    $counteredOffers = $offers->where('status', 'countered');
                @endphp

                @if ($pendingRentInstallments->isNotEmpty())
                    @php $nextRent = $pendingRentInstallments->first(); @endphp
                    <div class="rounded-2xl border-2 border-[#d4af37] bg-white p-5 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-xl bg-[#d4af37]/20 flex items-center justify-center text-2xl shrink-0">
                                🔔
                            </div>
                            <div>
                                <span class="rounded bg-rose-100 text-rose-800 text-[10px] font-black uppercase px-2 py-0.5">Rent Due Alert</span>
                                <h3 class="text-base font-black text-[#102b25] mt-0.5">
                                    {{ $nextRent['lease']->property?->title }} — {{ $nextRent['schedule']->period_label }}
                                </h3>
                                <p class="text-xs text-slate-600">
                                    Amount Due: <strong class="text-[#102b25] font-bold">{{ number_format($nextRent['schedule']->amount_due, 2) }} ETB</strong> | Due Date: <strong>{{ \Carbon\Carbon::parse($nextRent['schedule']->due_date)->format('M d, Y') }}</strong>
                                </p>
                            </div>
                        </div>
                        <button type="button" onclick="openRentPaymentModal('{{ $nextRent['schedule']->id }}', '{{ $nextRent['lease']->lease_code }}', '{{ $nextRent['schedule']->period_label }}', '{{ $nextRent['schedule']->amount_due }}', '{{ addslashes($nextRent['lease']->property?->title ?? 'Rental Property') }}', '{{ $nextRent['lease']->property_id }}')" class="rounded-xl bg-[#102b25] px-5 py-2.5 text-xs font-black text-[#d4af37] hover:bg-[#1a443b] transition shrink-0 cursor-pointer shadow-sm">
                            💳 Pay Rent Now
                        </button>
                    </div>
                @endif

                @if ($counteredOffers->isNotEmpty())
                    <div class="rounded-2xl border border-amber-300 bg-amber-50 p-5 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-xl bg-amber-100 flex items-center justify-center text-2xl shrink-0">
                                💬
                            </div>
                            <div>
                                <span class="rounded bg-amber-200 text-amber-900 text-[10px] font-black uppercase px-2 py-0.5">Counteroffer Received</span>
                                <h3 class="text-base font-black text-amber-950 mt-0.5">
                                    You have {{ $counteredOffers->count() }} active counteroffer awaiting your response!
                                </h3>
                                <p class="text-xs text-amber-800">
                                    Review the seller/agent's proposed amount and accept, counter, or decline.
                                </p>
                            </div>
                        </div>
                        <a href="#offers" onclick="document.querySelector('[data-target-section=\'offers\']').click()" class="rounded-xl bg-[#102b25] px-4 py-2 text-xs font-bold text-white hover:bg-black transition shrink-0">
                            Review Counteroffers →
                        </a>
                    </div>
                @endif

                {{-- Key Statistics Cards --}}
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="rounded-2xl border border-[#d9cab3] bg-white p-4 shadow-xs">
                        <span class="text-2xl">🏠</span>
                        <p class="text-xs font-bold text-slate-500 mt-2">Active Leases</p>
                        <p class="text-2xl font-black text-[#102b25]">{{ $stats['active_leases'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-[#d9cab3] bg-white p-4 shadow-xs">
                        <span class="text-2xl">🤝</span>
                        <p class="text-xs font-bold text-slate-500 mt-2">Pending Offers</p>
                        <p class="text-2xl font-black text-[#102b25]">{{ $stats['pending_offers'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-[#d9cab3] bg-white p-4 shadow-xs">
                        <span class="text-2xl">📅</span>
                        <p class="text-xs font-bold text-slate-500 mt-2">Viewings</p>
                        <p class="text-2xl font-black text-[#102b25]">{{ $stats['upcoming_viewings'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-[#d9cab3] bg-white p-4 shadow-xs">
                        <span class="text-2xl">🛏️</span>
                        <p class="text-xs font-bold text-slate-500 mt-2">Stay Bookings</p>
                        <p class="text-2xl font-black text-[#102b25]">{{ $stats['active_stays'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-[#d9cab3] bg-white p-4 shadow-xs">
                        <span class="text-2xl">🏗️</span>
                        <p class="text-xs font-bold text-slate-500 mt-2">Unit Reservations</p>
                        <p class="text-2xl font-black text-[#102b25]">{{ $stats['unit_reservations'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-[#d9cab3] bg-white p-4 shadow-xs">
                        <span class="text-2xl">💳</span>
                        <p class="text-xs font-bold text-slate-500 mt-2">Total Paid (ETB)</p>
                        <p class="text-lg font-black text-emerald-700 truncate" title="{{ number_format($stats['total_paid'], 2) }}">
                            {{ number_format($stats['total_paid']) }}
                        </p>
                    </div>
                </div>

                {{-- Recent Activities Summary Tabs --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Quick Leases Card --}}
                    <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs">
                        <div class="flex items-center justify-between border-b border-[#ebdcc9] pb-4 mb-4">
                            <div>
                                <h3 class="text-lg font-black text-[#102b25]">My Tenancies</h3>
                                <p class="text-xs text-slate-500">Active and historical leases</p>
                            </div>
                            <span class="rounded-full bg-[#102b25] px-2.5 py-1 text-xs font-bold text-white">{{ $leases->count() }}</span>
                        </div>

                        @if ($leases->isEmpty())
                            <div class="py-8 text-center text-slate-400">
                                <span class="text-3xl">🏠</span>
                                <p class="mt-2 text-sm">No registered tenancy agreements yet.</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach ($leases->take(2) as $lease)
                                    <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-4 flex items-center justify-between gap-4">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 uppercase">{{ $lease->status }}</span>
                                                <span class="text-xs font-mono font-bold text-slate-500">{{ $lease->lease_code }}</span>
                                            </div>
                                            <h4 class="font-bold text-[#102b25] text-sm mt-1">{{ $lease->property?->title ?? 'Property' }}</h4>
                                            <p class="text-xs text-slate-600 mt-0.5">Rent: <strong>{{ number_format($lease->monthly_rent, 2) }} ETB / mo</strong></p>
                                        </div>
                                        <a href="{{ route('leases.agreement', $lease->id) }}" class="rounded-xl border border-[#102b25] px-3 py-1.5 text-xs font-bold text-[#102b25] hover:bg-[#102b25] hover:text-white transition">
                                            Lease (PDF)
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Quick Offers Card --}}
                    <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs">
                        <div class="flex items-center justify-between border-b border-[#ebdcc9] pb-4 mb-4">
                            <div>
                                <h3 class="text-lg font-black text-[#102b25]">My Offers & Deals</h3>
                                <p class="text-xs text-slate-500">Live negotiation tracking</p>
                            </div>
                            <span class="rounded-full bg-[#102b25] px-2.5 py-1 text-xs font-bold text-white">{{ $offers->count() }}</span>
                        </div>

                        @if ($offers->isEmpty())
                            <div class="py-8 text-center text-slate-400">
                                <span class="text-3xl">🤝</span>
                                <p class="mt-2 text-sm">You haven't submitted any purchase or rental offers yet.</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach ($offers->take(2) as $offer)
                                    <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-4 flex items-center justify-between gap-4">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="rounded bg-[#d4af37]/20 text-[#9b6c17] text-[10px] font-bold px-2 py-0.5 uppercase">{{ str_replace('_', ' ', $offer->status) }}</span>
                                                <span class="text-xs font-mono font-bold text-slate-500">{{ $offer->offer_reference }}</span>
                                            </div>
                                            <h4 class="font-bold text-[#102b25] text-sm mt-1">{{ $offer->property?->title ?? 'Property' }}</h4>
                                            <p class="text-xs text-slate-600 mt-0.5">Offered: <strong>{{ number_format($offer->offered_price, 2) }} ETB</strong></p>
                                        </div>
                                        <a href="#offers" onclick="document.querySelector('[data-target-section=\'offers\']').click()" class="rounded-xl bg-[#102b25] px-3 py-1.5 text-xs font-bold text-white hover:bg-black transition">
                                            View Details
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- 2. MY OFFERS & NEGOTIATIONS SECTION --}}
            <section data-section="offers" class="hidden space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">My Offers & Negotiations</h2>
                        <p class="text-sm text-slate-600">Track and respond to price negotiations, counteroffers, and download executed sales agreements.</p>
                    </div>
                </div>

                @if ($offers->isEmpty())
                    <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                        <span class="text-4xl">🤝</span>
                        <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Formal Offers Submitted</h3>
                        <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                            Browse properties on our platform and click "Make an Offer" to initiate negotiations directly with certified agents.
                        </p>
                        <a href="{{ route('properties.index') }}" class="mt-4 inline-block rounded-xl bg-[#102b25] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#15342c]">
                            Browse Properties
                        </a>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach ($offers as $offer)
                            <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-sm">
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-[#f0e4cf] pb-4">
                                    <div class="flex items-center gap-4">
                                        @if ($offer->property?->primary_image_url)
                                            <img src="{{ $offer->property->primary_image_url }}" alt="{{ $offer->property->title }}" class="h-16 w-20 rounded-2xl object-cover border border-[#ebdcc9]" loading="lazy" onerror="this.onerror=null;this.src='{{ asset($offer->property->default_background_image ?? 'images/luxury/hero-skyline.jpg') }}';">
                                        @else
                                            <img src="{{ asset('images/luxury/hero-skyline.jpg') }}" alt="Property" class="h-16 w-20 rounded-2xl object-cover border border-[#ebdcc9]">
                                        @endif
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="rounded bg-[#ebdcc9] px-2 py-0.5 font-mono text-[11px] font-bold text-[#102b25]">{{ $offer->offer_reference }}</span>
                                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black uppercase text-slate-700">{{ $offer->offer_type }}</span>
                                                <span class="rounded-full @if($offer->status === 'accepted') bg-emerald-100 text-emerald-800 @elseif($offer->status === 'countered') bg-amber-100 text-amber-800 @elseif($offer->status === 'rejected') bg-rose-100 text-rose-800 @else bg-blue-100 text-blue-800 @endif px-2.5 py-0.5 text-[10px] font-black uppercase">
                                                    {{ str_replace('_', ' ', $offer->status) }}
                                                </span>
                                            </div>
                                            <h3 class="text-lg font-black text-[#102b25] mt-1">{{ $offer->property?->title ?? 'Property' }}</h3>
                                            <p class="text-xs text-slate-500">Submitted: {{ $offer->created_at->format('M d, Y') }} | Financing: <strong class="capitalize">{{ str_replace('_', ' ', $offer->financing_type) }}</strong></p>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-4 text-right">
                                        <div>
                                            <p class="text-xs font-bold text-slate-400 uppercase">Listed Price</p>
                                            <p class="text-sm font-semibold text-slate-600 line-through">{{ number_format($offer->original_price, 2) }} ETB</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-[#102b25] uppercase">Your Offered Price</p>
                                            <p class="text-lg font-black text-[#102b25]">{{ number_format($offer->offered_price, 2) }} ETB</p>
                                        </div>
                                        @if ($offer->current_counter_amount)
                                            <div class="rounded-xl bg-amber-50 border border-amber-200 px-3 py-1">
                                                <p class="text-[10px] font-bold text-amber-800 uppercase">Counter Amount</p>
                                                <p class="text-base font-black text-amber-900">{{ number_format($offer->current_counter_amount, 2) }} ETB</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Counteroffer Action Callout if status is countered --}}
                                @if ($offer->status === 'countered')
                                    <div class="mt-4 rounded-2xl border-2 border-[#d4af37] bg-[#fcf9f2] p-5">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                            <div>
                                                <span class="rounded bg-[#d4af37] text-[#102b25] text-[10px] font-black uppercase px-2 py-0.5">Counteroffer Notice</span>
                                                <p class="mt-1 text-sm font-bold text-[#102b25]">
                                                    The seller proposed <strong>{{ number_format($offer->current_counter_amount, 2) }} ETB</strong>.
                                                </p>
                                                @if ($offer->negotiations->first())
                                                    <p class="text-xs text-slate-600 italic mt-0.5">
                                                        "{{ $offer->negotiations->first()->message }}"
                                                    </p>
                                                @endif
                                            </div>

                                            <div class="flex flex-wrap items-center gap-2">
                                                {{-- Accept Counteroffer --}}
                                                <form method="POST" action="{{ route('offers.accept', $offer->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2 text-xs font-black text-white hover:bg-emerald-800 transition cursor-pointer">
                                                        ✓ Accept Proposed Price
                                                    </button>
                                                </form>

                                                {{-- Counter Back Toggle --}}
                                                <button type="button" onclick="document.getElementById('counter-form-{{ $offer->id }}').classList.toggle('hidden')" class="rounded-xl bg-[#102b25] px-4 py-2 text-xs font-black text-[#d4af37] hover:bg-black transition cursor-pointer">
                                                    Counter Back
                                                </button>

                                                {{-- Decline --}}
                                                <form method="POST" action="{{ route('offers.reject', $offer->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="rounded-xl border border-rose-300 text-rose-700 hover:bg-rose-50 px-3 py-2 text-xs font-bold transition cursor-pointer">
                                                        Decline
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        {{-- Inline Counter Form --}}
                                        <form id="counter-form-{{ $offer->id }}" method="POST" action="{{ route('offers.counter', $offer->id) }}" class="hidden mt-4 pt-4 border-t border-[#ebdcc9] grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700">New Counter Amount (ETB)</label>
                                                <input type="number" name="counter_amount" required min="1" step="any" placeholder="e.g. 8500000" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="block text-xs font-bold text-slate-700">Message / Terms</label>
                                                <div class="flex gap-2 mt-1">
                                                    <input type="text" name="message" required placeholder="Reasoning or condition..." class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                                                    <button type="submit" class="rounded-xl bg-[#102b25] text-[#d4af37] font-bold px-4 py-2 text-xs hover:bg-black shrink-0">
                                                        Submit Counter
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                                {{-- If Accepted / Converted: Download Agreements --}}
                                @if ($offer->status === 'accepted' || $offer->order_id || $offer->lease_id)
                                    <div class="mt-4 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div>
                                            @if (($offer->order?->hasAgreement() && $offer->order->isAgreementApprovedForGeneration()) || $offer->lease?->agreement_document_path)
                                                <span class="rounded bg-emerald-200 text-emerald-900 text-[10px] font-black uppercase px-2 py-0.5">Agreement Ready</span>
                                            @else
                                                <span class="rounded bg-amber-100 text-amber-900 text-[10px] font-black uppercase px-2 py-0.5">Admin Review</span>
                                            @endif
                                            <p class="text-sm font-bold text-emerald-950 mt-1">
                                                @if (($offer->order?->hasAgreement() && $offer->order->isAgreementApprovedForGeneration()) || $offer->lease?->agreement_document_path)
                                                    Offer accepted! Your official agreement has been prepared.
                                                @else
                                                    Your agent submitted the agreed terms. The agreement will be available after admin review and commission collection.
                                                @endif
                                            </p>
                                            @if ($offer->closing_status)
                                                <p class="text-xs text-emerald-800">
                                                    Closing Pipeline Stage: <strong class="uppercase font-mono">{{ str_replace('_', ' ', $offer->closing_status) }}</strong>
                                                </p>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-2">
                                            @if ($offer->order_id && $offer->order?->hasAgreement() && $offer->order->isAgreementApprovedForGeneration())
                                                <a href="{{ route('orders.agreement', $offer->order_id) }}" class="rounded-xl bg-[#102b25] text-white px-4 py-2 text-xs font-bold hover:bg-black transition flex items-center gap-1.5 shadow-sm">
                                                    <span>📄</span>
                                                    <span>Download Purchase Agreement (PDF)</span>
                                                </a>
                                            @endif

                                            @if ($offer->lease_id && $offer->lease?->agreement_document_path)
                                                <a href="{{ route('leases.agreement', $offer->lease_id) }}" class="rounded-xl bg-[#102b25] text-[#d4af37] px-4 py-2 text-xs font-bold hover:bg-black transition flex items-center gap-1.5 shadow-sm">
                                                    <span>Lease</span>
                                                    <span>Download Tenancy Agreement (PDF)</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Collapsible Negotiation Audit Trail --}}
                                @if ($offer->negotiations->isNotEmpty())
                                    <div class="mt-4 pt-4 border-t border-[#ebdcc9]">
                                        <details class="group">
                                            <summary class="cursor-pointer text-xs font-bold text-slate-500 hover:text-[#102b25] list-none flex items-center justify-between">
                                                <span>Audit Trail: {{ $offer->negotiations->count() }} negotiation round(s)</span>
                                                <span class="group-open:rotate-180 transition text-sm">▼</span>
                                            </summary>
                                            <div class="mt-3 space-y-2 pl-2 border-l-2 border-[#d9cab3]">
                                                @foreach ($offer->negotiations as $round)
                                                    <div class="text-xs text-slate-700 bg-[#fcf9f5] rounded-xl p-2.5">
                                                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-500">
                                                            <span class="capitalize text-[#102b25]">{{ str_replace('_', ' ', $round->sender_type) }}: {{ number_format($round->amount, 2) }} ETB</span>
                                                            <span>{{ $round->created_at->format('M d, Y H:i') }}</span>
                                                        </div>
                                                        @if ($round->message)
                                                            <p class="mt-1 text-slate-600">{{ $round->message }}</p>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </details>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- 3. MY LEASES & RENT SCHEDULES SECTION --}}
            <section data-section="leases" class="hidden space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">My Leases & Rent Schedules</h2>
                        <p class="text-sm text-slate-600">Track active residential and commercial leases, download your legal tenancy agreement, and pay rent installments directly.</p>
                    </div>
                </div>

                @if ($leases->isEmpty())
                    <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                        <span class="text-4xl">🏠</span>
                        <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Active Tenancies Found</h3>
                        <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                            When you rent a property through GTM Real Estate, your official 12-month installment schedule and signed lease agreement will appear here.
                        </p>
                        <a href="{{ route('properties.index') }}?type=rent" class="mt-4 inline-block rounded-xl bg-[#102b25] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#15342c]">
                            Browse Rental Properties
                        </a>
                    </div>
                @else
                    <div class="space-y-8">
                        @foreach ($leases as $lease)
                            <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-sm space-y-6">
                                {{-- Lease Header --}}
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-[#f0e4cf] pb-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="rounded bg-[#102b25] text-[#d4af37] px-2.5 py-0.5 text-xs font-mono font-bold">{{ $lease->lease_code }}</span>
                                            <span class="rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase px-2.5 py-0.5">{{ $lease->status }}</span>
                                            <span class="rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold px-2 py-0.5 capitalize">{{ $lease->payment_frequency }}</span>
                                        </div>
                                        <h3 class="text-xl font-black text-[#102b25] mt-1">{{ $lease->property?->title ?? 'Rental Property' }}</h3>
                                        <p class="text-xs text-slate-500">
                                            Duration: <strong>{{ $lease->duration_months }} Months</strong> ({{ \Carbon\Carbon::parse($lease->start_date)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($lease->end_date)->format('M d, Y') }})
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-3">
                                        <a href="{{ route('leases.agreement', $lease->id) }}" class="rounded-xl bg-[#102b25] px-4 py-2.5 text-xs font-bold text-[#d4af37] hover:bg-black transition flex items-center gap-1.5 shadow-xs">
                                            <span>Lease</span>
                                            <span>Download Lease (PDF)</span>
                                        </a>
                                    </div>
                                </div>

                                {{-- Financial Breakdown Cards --}}
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-[#fcf9f5] rounded-2xl p-4 border border-[#ebdcc9]">
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">Monthly Rent</p>
                                        <p class="text-base font-black text-[#102b25]">{{ number_format($lease->monthly_rent, 2) }} ETB</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">Security Deposit</p>
                                        <p class="text-base font-black text-[#102b25]">{{ number_format($lease->security_deposit, 2) }} ETB</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">Total Rent Paid</p>
                                        <p class="text-base font-black text-emerald-700">{{ number_format($lease->total_rent_paid, 2) }} ETB</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">Outstanding Balance</p>
                                        <p class="text-base font-black text-rose-700">{{ number_format($lease->outstanding_balance, 2) }} ETB</p>
                                    </div>
                                </div>

                                {{-- Installment Schedule Table --}}
                                <div>
                                    <h4 class="text-sm font-black text-[#102b25] uppercase tracking-wider mb-3">
                                        12-Month Installment Schedule
                                    </h4>
                                    <div class="overflow-x-auto rounded-2xl border border-[#ebdcc9]">
                                        <table class="w-full text-left text-xs">
                                            <thead class="bg-[#ebdcc9]/50 text-[#102b25] font-bold uppercase text-[10px]">
                                                <tr>
                                                    <th class="p-3">#</th>
                                                    <th class="p-3">Period</th>
                                                    <th class="p-3">Due Date</th>
                                                    <th class="p-3">Amount Due</th>
                                                    <th class="p-3">Amount Paid</th>
                                                    <th class="p-3">Status</th>
                                                    <th class="p-3 text-right">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-[#ebdcc9]/40 bg-white">
                                                @foreach ($lease->schedules as $sched)
                                                    <tr class="hover:bg-[#fcf9f5]">
                                                        <td class="p-3 font-bold font-mono">{{ $sched->installment_number }}</td>
                                                        <td class="p-3 font-bold text-[#102b25]">{{ $sched->period_label }}</td>
                                                        <td class="p-3 text-slate-600">{{ \Carbon\Carbon::parse($sched->due_date)->format('M d, Y') }}</td>
                                                        <td class="p-3 font-bold text-[#102b25]">{{ number_format($sched->amount_due, 2) }} ETB</td>
                                                        <td class="p-3 font-semibold text-emerald-700">{{ number_format($sched->amount_paid, 2) }} ETB</td>
                                                        <td class="p-3">
                                                            @if ($sched->status === 'paid')
                                                                <span class="rounded bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase px-2 py-0.5">Paid ✓</span>
                                                            @elseif ($sched->status === 'overdue')
                                                                <span class="rounded bg-rose-100 text-rose-800 text-[10px] font-black uppercase px-2 py-0.5">Overdue</span>
                                                            @else
                                                                <span class="rounded bg-amber-100 text-amber-800 text-[10px] font-black uppercase px-2 py-0.5">Pending</span>
                                                            @endif
                                                        </td>
                                                        <td class="p-3 text-right">
                                                            @if ($sched->status !== 'paid')
                                                                <button type="button" onclick="openRentPaymentModal('{{ $sched->id }}', '{{ $lease->lease_code }}', '{{ $sched->period_label }}', '{{ $sched->amount_due }}', '{{ addslashes($lease->property?->title ?? 'Property') }}', '{{ $lease->property_id }}')" class="rounded-lg bg-[#102b25] text-[#d4af37] px-3 py-1 font-bold text-xs hover:bg-black transition cursor-pointer">
                                                                    Pay
                                                                </button>
                                                            @elseif ($sched->payment)
                                                                <a href="{{ route('payments.receipt', $sched->payment->transaction_number) }}" class="text-xs font-bold text-[#102b25] underline hover:text-[#d4af37]">
                                                                    Receipt #{{ $sched->payment->transaction_number }}
                                                                </a>
                                                            @else
                                                                <span class="text-xs text-slate-400 font-semibold">Settled</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- 4. MY SCHEDULED VIEWINGS SECTION --}}
            <section data-section="viewings" class="hidden space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">My Viewing Appointments</h2>
                        <p class="text-sm text-slate-600">Track and manage your physical and virtual property tour appointments.</p>
                    </div>
                </div>

                @if ($viewings->isEmpty())
                    <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                        <span class="text-4xl">📅</span>
                        <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Viewing Appointments</h3>
                        <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                            Schedule in-person or live video tours on any property listing page with our verified agents.
                        </p>
                        <a href="{{ route('properties.index') }}" class="mt-4 inline-block rounded-xl bg-[#102b25] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#15342c]">
                            Browse Properties
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($viewings as $viewing)
                            <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs flex flex-col justify-between gap-4">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="font-mono text-xs font-bold text-slate-500">{{ $viewing->viewing_reference }}</span>
                                        <span class="rounded-full @if($viewing->status === 'confirmed') bg-emerald-100 text-emerald-800 @elseif($viewing->status === 'cancelled') bg-rose-100 text-rose-800 @else bg-amber-100 text-amber-800 @endif px-2.5 py-0.5 text-[10px] font-black uppercase">
                                            {{ $viewing->status }}
                                        </span>
                                    </div>
                                    <h3 class="text-base font-black text-[#102b25] mt-2">{{ $viewing->property?->title ?? 'Property' }}</h3>
                                    <p class="text-xs text-slate-600 mt-1">
                                        📍 {{ $viewing->property?->location ?? 'Addis Ababa' }}
                                    </p>
                                    <div class="mt-3 rounded-2xl bg-[#fcf9f5] border border-[#ebdcc9] p-3 text-xs space-y-1">
                                        <p>🗓️ Date: <strong>{{ \Carbon\Carbon::parse($viewing->viewing_date)->format('l, M d, Y') }}</strong></p>
                                        <p>⏰ Slot: <strong>{{ $viewing->formatted_time_slot }}</strong></p>
                                        <p>📹 Tour Mode: <strong class="capitalize">{{ str_replace('_', ' ', $viewing->viewing_type) }}</strong></p>
                                        @if ($viewing->agent)
                                            <p>👤 Agent: <strong>{{ $viewing->agent->name }}</strong> ({{ $viewing->agent->phone ?? 'Contact Office' }})</p>
                                        @endif
                                    </div>
                                </div>

                                @if (in_array($viewing->status, ['requested', 'confirmed']))
                                    <form method="POST" action="{{ route('viewings.update-status', $viewing->id) }}" class="pt-2 border-t border-[#f0e4cf]">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" onclick="return confirm('Are you sure you want to cancel this viewing appointment?')" class="rounded-xl border border-rose-300 text-rose-700 hover:bg-rose-50 px-3 py-1.5 text-xs font-bold w-full transition">
                                            Cancel Appointment
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- 5. MY STAYS & FRONT DESK HUB SECTION --}}
            <section data-section="stays" class="hidden space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Guest Hospitality Desk</span>
                            <h2 class="text-2xl font-black text-[#102b25]">My Stays, Check-In &amp; Concierge Desk</h2>
                        </div>
                        <p class="text-sm text-slate-600 mt-0.5">Track your hotel and serviced apartments, assigned room keys, check-in/out times, and request housekeeping.</p>
                    </div>

                    @if ($bookings->isNotEmpty())
                        <button type="button" onclick="openGuestServiceModal()" class="rounded-xl bg-[#102b25] px-4 py-2.5 text-xs font-bold text-[#d4af37] hover:bg-black transition flex items-center gap-1.5 shadow-xs shrink-0">
                            <span>🛎️</span>
                            <span>Request Housekeeping / Room Service</span>
                        </button>
                    @endif
                </div>

                {{-- Desk Concierge Quick Assistance Card --}}
                <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🛎️</span>
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-[#102b25]">Front Desk &amp; Concierge Service</p>
                            <p class="text-xs text-slate-600">Standard Check-In: <strong>14:00</strong> &bull; Standard Check-Out: <strong>11:00</strong> &bull; 24/7 Front Desk Hotline: <strong>+251 11 661 0000</strong></p>
                        </div>
                    </div>
                    <span class="rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold px-3 py-1 shrink-0">
                        ● Front Desk Active &amp; Ready
                    </span>
                </div>

                @if ($bookings->isEmpty())
                    <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                        <span class="text-4xl">🛏️</span>
                        <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Stay Reservations Found</h3>
                        <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                            Looking for fully furnished luxury apartments or vacation villas in Addis Ababa? Explore our curated short stays.
                        </p>
                        <a href="{{ route('stays.index') }}" class="mt-4 inline-block rounded-xl bg-[#102b25] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#15342c]">
                            Explore Stays
                        </a>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach ($bookings as $booking)
                            <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-sm space-y-4">
                                {{-- Card Header --}}
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-[#f0e4cf] pb-4">
                                    <div class="flex items-start gap-4">
                                        <div class="h-14 w-14 rounded-2xl bg-[#d4af37]/20 flex items-center justify-center text-2xl shrink-0">
                                            🛏️
                                        </div>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-mono text-xs font-bold text-slate-500">{{ $booking->booking_reference }}</span>
                                                @if ($booking->status === 'checked_in')
                                                    <span class="rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] font-black uppercase px-2.5 py-0.5">
                                                        🟢 In-House (Checked In)
                                                    </span>
                                                @elseif ($booking->status === 'checked_out')
                                                    <span class="rounded-full bg-slate-100 text-slate-700 text-[10px] font-black uppercase px-2.5 py-0.5">
                                                        Checked Out
                                                    </span>
                                                @elseif ($booking->status === 'confirmed')
                                                    <span class="rounded-full bg-amber-100 text-amber-800 border border-amber-300 text-[10px] font-black uppercase px-2.5 py-0.5">
                                                        🟡 Confirmed (Arriving Soon)
                                                    </span>
                                                @else
                                                    <span class="rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold uppercase px-2.5 py-0.5">
                                                        {{ $booking->status }}
                                                    </span>
                                                @endif
                                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $booking->payment_status_badge[1] }}">
                                                    {{ $booking->payment_status_badge[0] }}
                                                </span>
                                            </div>
                                            <h3 class="text-lg font-black text-[#102b25] mt-1">{{ $booking->property?->title ?? 'Stay Accommodation' }}</h3>
                                            @if ($booking->property && $booking->property->address)
                                                <p class="text-xs text-slate-500">📍 {{ $booking->property->address }}, {{ $booking->property->city ?? 'Addis Ababa' }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Price & Room Assigned --}}
                                    <div class="flex items-center gap-3 lg:text-right">
                                        <div class="rounded-2xl bg-[#102b25] text-white p-3 text-center min-w-28">
                                            <p class="text-[9px] font-bold uppercase tracking-wider text-[#d4af37]">Room Number</p>
                                            <p class="text-base font-black font-mono">{{ $booking->room_number ?: 'Pending' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase">Total Amount</p>
                                            <p class="text-lg font-black text-[#102b25]">{{ number_format($booking->total_amount, 2) }} ETB</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Stay Schedule & Manager Details --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-[#fcf9f5] rounded-2xl p-4 border border-[#ebdcc9] text-xs">
                                    <div>
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Check-In Date &amp; Time</span>
                                        <p class="font-bold text-[#102b25] mt-0.5">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d, Y') }} (from 14:00)</p>
                                        @if ($booking->checked_in_at)
                                            <span class="text-emerald-700 font-semibold block text-[11px]">✓ Checked-in at {{ \Carbon\Carbon::parse($booking->checked_in_at)->format('H:i') }}</span>
                                        @else
                                            <span class="text-slate-400 text-[11px]">Key available at front desk</span>
                                        @endif
                                    </div>

                                    <div>
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Check-Out Date &amp; Time</span>
                                        <p class="font-bold text-[#102b25] mt-0.5">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') }} (by 11:00 AM)</p>
                                        @if ($booking->checked_out_at)
                                            <span class="text-slate-500 font-semibold block text-[11px]">✓ Checked-out at {{ \Carbon\Carbon::parse($booking->checked_out_at)->format('H:i') }}</span>
                                        @else
                                            <span class="text-slate-400 text-[11px]">{{ $booking->total_nights }} nights duration</span>
                                        @endif
                                    </div>

                                    <div>
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Property Management</span>
                                        @if ($booking->property?->agent)
                                            <p class="font-bold text-[#102b25] mt-0.5">👤 Managed by Agent {{ $booking->property->agent->name }}</p>
                                            <p class="text-[11px] text-slate-500">📞 {{ $booking->property->agent->phone ?? 'Contact office' }}</p>
                                        @else
                                            <p class="font-bold text-[#102b25] mt-0.5">🏛️ GTM Admin Corporate Desk</p>
                                            <p class="text-[11px] text-slate-500">Direct HQ management</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Active Room Service Requests for this Stay --}}
                                @php $stayServiceReqs = $booking->guestServiceRequests; @endphp
                                @if ($stayServiceReqs->isNotEmpty())
                                    <div class="rounded-xl bg-[#fffdfa] p-3 border border-[#f0e4cf]">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Active Housekeeping &amp; Room Requests for this Stay:</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($stayServiceReqs as $sr)
                                                <span class="inline-flex items-center gap-1.5 rounded-lg border px-2 py-1 text-xs {{ $sr->status_badge_class }}">
                                                    <span>{{ $sr->service_type_label }}</span>
                                                    <span class="text-[10px] font-black uppercase">({{ $sr->status }})</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Action Buttons --}}
                                <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-[#f0e4cf]">
                                    {{-- Housekeeping Modal Trigger --}}
                                    <button type="button" onclick="openGuestServiceModal({{ $booking->id }}, '{{ $booking->room_number }}', '{{ addslashes($booking->property?->title ?? 'Stay') }}')" class="rounded-xl border border-[#b9a98b] bg-white px-3.5 py-2 text-xs font-bold text-[#1d3c34] hover:bg-[#f0e4cf] transition flex items-center gap-1.5 shadow-xs">
                                        <span>🛎️</span>
                                        <span>Request Room Service</span>
                                    </button>

                                    {{-- Official Folio & Invoice --}}
                                    <a href="{{ route('receptionist.bookings.folio', $booking->id) }}" target="_blank" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition flex items-center gap-1.5 shadow-xs">
                                        <span>📄</span>
                                        <span>Official Folio &amp; Tax Invoice</span>
                                    </a>

                                    {{-- Leave Review --}}
                                    <button type="button" onclick="openGuestReviewModal({{ $booking->property_id }}, {{ $booking->id }}, '{{ addslashes($booking->property?->title ?? 'Stay Accommodation') }}')" class="rounded-xl border border-[#d4af37] bg-[#fcf9f5] px-3.5 py-2 text-xs font-bold text-[#102b25] hover:bg-[#ebdcc9] transition flex items-center gap-1.5 shadow-xs">
                                        <span>⭐</span>
                                        <span>Rate &amp; Review</span>
                                    </button>

                                    @php $stayPayment = $booking->payments->first(); @endphp
                                    @if ($booking->isPaid())
                                        @if ($stayPayment)
                                            <a href="{{ route('payments.receipt', $stayPayment->transaction_number) }}" target="_blank" class="rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-800 transition hover:bg-emerald-100">
                                                Receipt #{{ $stayPayment->transaction_number }}
                                            </a>
                                        @endif
                                    @elseif (! in_array($booking->status, ['cancelled', 'completed'], true))
                                        <button type="button" onclick="openUniversalPaymentModal({ type: 'stay_booking', formAction: '{{ route('bookings.pay', $booking) }}', amount: {{ (float) $booking->total_amount }}, lockAmount: true, title: 'Pay for Stay Reservation', purpose: 'Stay payment ' + @js($booking->booking_reference) + ' · ' + @js($booking->property?->title ?? 'Stay Accommodation'), submitLabel: 'Confirm & Pay' })" class="cursor-pointer rounded-xl bg-[#102b25] px-4 py-2 text-xs font-bold text-[#d4af37] transition hover:bg-black">
                                            💳 Pay Now
                                        </button>
                                    @endif

                                    @if (! in_array($booking->status, ['checked_in', 'completed'], true))
                                        <form method="POST" action="{{ route('bookings.destroy', $booking) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100" onclick="return confirm('Cancel/Delete this stay reservation?')">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- 6. MY OFF-PLAN UNIT RESERVATIONS SECTION --}}
            <section data-section="reservations" class="hidden space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">My Off-Plan Unit Reservations</h2>
                        <p class="text-sm text-slate-600">Track construction progress, milestone disbursements, and unit specifications for reserved developments.</p>
                    </div>
                </div>

                @if ($reservations->isEmpty())
                    <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                        <span class="text-4xl">🏗️</span>
                        <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Off-Plan Units Reserved</h3>
                        <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                            Invest in premium Addis Ababa developments with flexible milestone payment plans from foundation to handover.
                        </p>
                        <a href="{{ route('developments.index') }}" class="mt-4 inline-block rounded-xl bg-[#102b25] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#15342c]">
                            Explore Developments
                        </a>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach ($reservations as $res)
                            <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#f0e4cf] pb-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs font-bold text-slate-500">{{ $res->reservation_reference }}</span>
                                            <span class="rounded-full bg-blue-100 text-blue-800 text-[10px] font-black uppercase px-2.5 py-0.5">{{ $res->status }}</span>
                                        </div>
                                        <h3 class="text-lg font-black text-[#102b25] mt-1">{{ $res->project?->title ?? 'Development Project' }}</h3>
                                        <p class="text-xs text-slate-600">
                                            Unit: <strong>{{ $res->unit?->unit_number ?? 'N/A' }}</strong> | Bedrooms: <strong>{{ $res->unit?->bedrooms ?? '-' }}</strong> | Size: <strong>{{ $res->unit?->size_sqm ?? '-' }} SQM</strong>
                                        </p>
                                    </div>

                                    @php
                                        $resProject = $res->project ?? $res->unit?->building?->project;
                                        $projectUrl = $resProject ? route('developments.show', $resProject->slug ?? $resProject->id) : route('developments.index');
                                    @endphp
                                    <a href="{{ $projectUrl }}" class="inline-flex items-center gap-2 rounded-xl bg-[#102b25] border border-[#d4af37]/60 px-4 py-2.5 text-xs font-black uppercase tracking-wider text-[#f5f0e7] hover:bg-[#1d3c34] hover:text-[#d4af37] shadow-sm transition shrink-0">
                                        <span class="text-sm">🏗️</span>
                                        <span>View Project Status &rarr;</span>
                                    </a>
                                </div>

                                {{-- Construction Milestone Progress Bar --}}
                                <div>
                                    <div class="flex items-center justify-between text-xs font-bold mb-2">
                                        <span class="text-[#102b25]">Construction Progress</span>
                                        <span class="text-[#d4af37] font-black">Active Development</span>
                                    </div>
                                    <div class="w-full bg-[#f0e4cf] rounded-full h-3 overflow-hidden">
                                        <div class="bg-gradient-to-r from-[#102b25] to-[#d4af37] h-3 rounded-full" style="width: 65%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- 7. MY RECEIPTS & PAYMENT SLIPS SECTION --}}
            <section data-section="payments" class="hidden space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">My Receipts & Payment Slips</h2>
                        <p class="text-sm text-slate-600">Auditable transaction vouchers, bank deposit slips, and verified financial receipts.</p>
                    </div>
                </div>

                @if ($payments->isEmpty())
                    <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                        <span class="text-4xl">🧾</span>
                        <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Payment Records Found</h3>
                        <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                            When you submit earnest deposits, rent installments, or booking payments, your digital vouchers and receipts will be cataloged here.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-3xl border border-[#d9cab3] bg-white shadow-xs">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#ebdcc9]/50 text-[#102b25] font-bold uppercase text-[10px]">
                                <tr>
                                    <th class="p-4">Transaction #</th>
                                    <th class="p-4">Date</th>
                                    <th class="p-4">Category</th>
                                    <th class="p-4">Method / Bank</th>
                                    <th class="p-4">Amount</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-right">Receipt / Voucher</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#ebdcc9]/40 bg-white">
                                @foreach ($payments as $payment)
                                    <tr class="hover:bg-[#fcf9f5]">
                                        <td class="p-4 font-mono font-bold text-[#102b25]">{{ $payment->transaction_number }}</td>
                                        <td class="p-4 text-slate-600">{{ $payment->created_at->format('M d, Y') }}</td>
                                        <td class="p-4 font-semibold text-slate-800 capitalize">{{ str_replace('_', ' ', $payment->payment_type) }}</td>
                                        <td class="p-4 text-slate-600">
                                            <span class="font-bold text-[#102b25] capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                                            @if ($payment->bank_transaction_reference)
                                                <span class="block text-[10px] text-slate-400 font-mono">Ref: {{ $payment->bank_transaction_reference }}</span>
                                            @endif
                                        </td>
                                        <td class="p-4 font-black text-[#102b25]">{{ number_format($payment->amount, 2) }} ETB</td>
                                        <td class="p-4">
                                            @if ($payment->status === 'completed')
                                                <span class="rounded bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase px-2 py-0.5">Verified ✓</span>
                                            @elseif ($payment->status === 'pending')
                                                <span class="rounded bg-amber-100 text-amber-800 text-[10px] font-black uppercase px-2 py-0.5">Under Audit</span>
                                            @else
                                                <span class="rounded bg-rose-100 text-rose-800 text-[10px] font-black uppercase px-2 py-0.5">Failed</span>
                                            @endif
                                        </td>
                                        <td class="p-4 text-right space-x-2">
                                            <a href="{{ route('payments.receipt', $payment->transaction_number) }}" class="rounded-lg bg-[#102b25] text-white px-3 py-1 font-bold text-xs hover:bg-black transition inline-block">
                                                Voucher
                                            </a>
                                            @if ($payment->deposit_slip_path)
                                                <a href="{{ route('payments.slip', $payment->id) }}" class="rounded-lg border border-[#102b25] text-[#102b25] px-2.5 py-1 font-bold text-xs hover:bg-[#102b25] hover:text-white transition inline-block">
                                                    Slip
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($payments instanceof \Illuminate\Pagination\LengthAwarePaginator && $payments->hasPages())
                        <div class="mt-6 border-t border-[#d9cab3] pt-4">
                            {{ $payments->fragment('payments')->links() }}
                        </div>
                    @endif
                @endif
            </section>

            {{-- 8. NOTIFICATIONS & ALERTS SECTION --}}
            <section data-section="notifications" class="hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">Notifications & Alerts</h2>
                        <p class="text-sm text-slate-600">Important updates on your offers, lease rent schedules, viewings, and payments.</p>
                    </div>
                    @if ($notifications->isNotEmpty())
                        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#102b25] border border-[#d4af37]/60 px-4 py-2.5 text-xs font-black uppercase tracking-wider text-[#f5f0e7] hover:bg-[#1d3c34] hover:text-[#d4af37] shadow-sm transition cursor-pointer">
                                <svg class="h-4 w-4 text-[#d4af37]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Mark All as Read</span>
                                @if (($stats['unread_notifications'] ?? 0) > 0)
                                    <span class="rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-bold text-white">{{ $stats['unread_notifications'] }} unread</span>
                                @endif
                            </button>
                        </form>
                    @endif
                </div>

                @if ($notifications->isEmpty())
                    <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                        <span class="text-4xl">🔔</span>
                        <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Notifications Yet</h3>
                        <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                            When agents respond to your offers, rent installments approach their due date, or deposits are verified, you will be notified here.
                        </p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($notifications as $notification)
                            @php
                                $data = $notification->data;
                                $isUnread = is_null($notification->read_at);
                            @endphp
                            <div class="rounded-2xl border {{ $isUnread ? 'border-[#d4af37] bg-[#fffdf8] shadow-sm' : 'border-[#ebdcc9] bg-white' }} p-4 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <div class="h-10 w-10 rounded-xl {{ $isUnread ? 'bg-[#d4af37]/20 text-[#102b25]' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center text-lg shrink-0 mt-0.5">
                                        @if (($data['type'] ?? '') === 'rent_schedule')
                                            🏠
                                        @elseif (($data['type'] ?? '') === 'property_offer')
                                            🤝
                                        @elseif (($data['type'] ?? '') === 'payment')
                                            💳
                                        @else
                                            📅
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-black text-[#102b25]">{{ $data['title'] ?? 'Notification' }}</h4>
                                            @if ($isUnread)
                                                <span class="rounded bg-rose-500 text-white text-[9px] font-black uppercase px-1.5 py-0.5">New</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-700 mt-1">{{ $data['message'] ?? '' }}</p>
                                        <span class="text-[10px] text-slate-400 font-semibold block mt-1">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                                    @if (!empty($data['url']))
                                        <a href="{{ $data['url'] }}" class="rounded-lg bg-[#102b25] text-white px-3 py-1.5 text-xs font-bold hover:bg-black transition">
                                            View Details
                                        </a>
                                    @endif
                                    @if ($isUnread)
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 px-2.5 py-1.5 text-xs font-semibold cursor-pointer">
                                                Mark read
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- 9. MY SAVED COLLECTIONS SECTION --}}
            <section data-section="collections" class="hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">My Curated Collections</h2>
                        <p class="text-sm text-slate-600">Saved property shortlists, comparison sets, and investment portfolios.</p>
                    </div>
                </div>

                @if ($collections->isEmpty())
                    <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                        <span class="text-4xl">📁</span>
                        <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Collections Created Yet</h3>
                        <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                            Browse properties on the marketplace and click "+ Add to Collection" to organize your favorite homes, villas, and short stays.
                        </p>
                        <a href="{{ route('properties.index') }}" class="mt-4 inline-block rounded-xl bg-[#102b25] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#15342c]">
                            Browse Properties
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($collections as $col)
                            <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between gap-2 border-b border-[#f0e4cf] pb-3">
                                        <h3 class="font-black text-lg text-[#102b25]">{{ $col->name }}</h3>
                                        <form method="POST" action="{{ route('collections.destroy', $col) }}" onsubmit="return confirm('Delete this collection?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-600 text-xs font-bold">&times; Delete</button>
                                        </form>
                                    </div>
                                    @if ($col->description)
                                        <p class="text-xs text-slate-600 mt-2">{{ $col->description }}</p>
                                    @endif
                                    <div class="mt-4 space-y-2">
                                        @forelse ($col->properties as $prop)
                                            <div class="flex items-center justify-between p-2 rounded-xl bg-[#fffaf2] border border-[#ecd9be] text-xs">
                                                <a href="{{ route('properties.show', $prop->slug) }}" class="font-bold text-[#1d3c34] hover:underline truncate max-w-[180px]">
                                                    {{ $prop->title }}
                                                </a>
                                                <span class="font-black text-[#855e16]">ETB {{ number_format($prop->price) }}</span>
                                            </div>
                                        @empty
                                            <p class="text-xs text-slate-400 italic py-2">Empty collection</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-t border-[#f0e4cf] flex items-center justify-between text-xs text-slate-500">
                                    <span>{{ $col->properties->count() }} items</span>
                                    <span>{{ $col->is_public ? '🌐 Public' : '🔒 Private' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- 10. MAINTENANCE & HOUSEKEEPING REQUESTS SECTION --}}
            @if ($canAccessMaintenanceDesk)
            <section data-section="maintenance" class="hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">Maintenance &amp; Housekeeping Desk</h2>
                        <p class="text-sm text-slate-600">Track and dispatch in-room hotel services, housekeeping requests, and residential lease maintenance tickets.</p>
                    </div>
                    @if ($bookings->isNotEmpty())
                        <button type="button" onclick="openGuestServiceModal({{ $bookings->first()->id }}, '{{ $bookings->first()->room_number }}', '{{ addslashes($bookings->first()->property?->title ?? 'Current Stay') }}')" class="inline-flex items-center gap-2 rounded-2xl bg-[#102b25] px-4 py-2.5 text-xs font-bold text-[#d4af37] shadow-sm hover:bg-black transition cursor-pointer">
                            <span>🛎️</span>
                            <span>Request Housekeeping / Room Service</span>
                        </button>
                    @endif
                </div>

                {{-- Tab Switcher for Housekeeping vs Long-Term Residential Maintenance --}}
                <div class="flex gap-2 border-b border-[#ebdcc9] pb-2">
                    <button type="button" id="tab-btn-housekeeping" onclick="switchMaintenanceTab('housekeeping')" class="rounded-xl bg-[#102b25] px-4 py-2 text-xs font-black text-[#d4af37] transition">
                        🛎️ Hotel Housekeeping &amp; Room Service ({{ $serviceRequests->count() }})
                    </button>
                    <button type="button" id="tab-btn-residential" onclick="switchMaintenanceTab('residential')" class="rounded-xl bg-white border border-[#ebdcc9] px-4 py-2 text-xs font-bold text-slate-600 hover:bg-[#fcf9f5] transition">
                        🛠️ Lease Maintenance Tickets ({{ $tickets->count() }})
                    </button>
                </div>

                {{-- Tab 1: Guest Housekeeping & Room Service Requests --}}
                <div id="maintenance-tab-housekeeping" class="space-y-4">
                    @if ($serviceRequests->isEmpty())
                        <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                            <span class="text-4xl">🧹</span>
                            <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Active Housekeeping Requests</h3>
                            <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                                Need room cleaning, extra towels, airport transport, or quick in-room repairs during your stay? Dispatch a request directly to the front desk.
                            </p>
                            @if ($bookings->isNotEmpty())
                                <button type="button" onclick="openGuestServiceModal({{ $bookings->first()->id }}, '{{ $bookings->first()->room_number }}', '{{ addslashes($bookings->first()->property?->title ?? 'Stay Accommodation') }}')" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#102b25] px-5 py-2.5 text-xs font-black text-[#d4af37] hover:bg-black transition">
                                    <span>🛎️</span>
                                    <span>Dispatch Front Desk Service</span>
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($serviceRequests as $req)
                                <div class="rounded-3xl border border-[#d9cab3] bg-white p-5 shadow-xs flex flex-col justify-between gap-4">
                                    <div>
                                        <div class="flex items-center justify-between gap-2 border-b border-[#f0e4cf] pb-3">
                                            <div class="flex items-center gap-2">
                                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase {{ $req->status_badge_class }}">
                                                    ● {{ ucfirst($req->status) }}
                                                </span>
                                                @if ($req->priority === 'urgent')
                                                    <span class="rounded-full bg-rose-100 text-rose-800 px-2 py-0.5 text-[10px] font-black uppercase">
                                                        🔥 Urgent
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="font-mono text-xs font-bold text-slate-500">Room {{ $req->room_number ?: 'N/A' }}</span>
                                        </div>

                                        <h3 class="text-base font-black text-[#102b25] mt-3">{{ $req->service_type_label }}</h3>
                                        <p class="text-xs text-slate-600 mt-1">
                                            Stay: <strong>{{ $req->booking?->property?->title ?? 'GTM Residence' }}</strong> (Ref: <span class="font-mono">{{ $req->booking?->booking_reference ?? 'N/A' }}</span>)
                                        </p>
                                        <p class="mt-3 text-xs text-slate-700 bg-[#fffaf2] p-3 rounded-xl border border-[#ecd9be]">
                                            "{{ $req->description }}"
                                        </p>

                                        @if ($req->response_notes)
                                            <div class="mt-3 rounded-xl bg-blue-50 border border-blue-200 p-3 text-xs text-blue-900">
                                                <strong>Desk Update:</strong> {{ $req->response_notes }}
                                                @if ($req->handler)
                                                    <span class="block text-[10px] text-blue-700 mt-1">— Handled by {{ $req->handler->name }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <div class="pt-2 border-t border-[#f0e4cf] flex items-center justify-between text-[11px] text-slate-400">
                                        <span>Requested: {{ $req->created_at->format('M d, Y · H:i') }}</span>
                                        <span class="font-bold text-[#102b25]">Front Desk 24/7</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Tab 2: Residential Lease Maintenance Tickets --}}
                <div id="maintenance-tab-residential" class="hidden space-y-4">
                    @if ($tickets->isEmpty())
                        <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                            <span class="text-4xl">🛠️</span>
                            <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Residential Maintenance Tickets</h3>
                            <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                                If you encounter plumbing, electrical, internet, or appliance issues at your leased home, submit a service ticket directly on the property page.
                            </p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($tickets as $ticket)
                                <div class="rounded-2xl border border-[#d9cab3] bg-white p-5 shadow-xs">
                                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#f0e4cf] pb-3">
                                        <div class="flex items-center gap-2.5">
                                            <span class="font-mono font-black text-sm text-[#102b25]">{{ $ticket->ticket_number }}</span>
                                            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $ticket->priority_badge_class }}">
                                                {{ ucfirst($ticket->priority) }}
                                            </span>
                                            <span class="font-bold text-sm text-[#1d3c34]">{{ $ticket->title }}</span>
                                        </div>
                                        <span class="rounded-full px-3 py-1 text-[11px] font-black uppercase {{ $ticket->status_badge_class }}">
                                            {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                                        </span>
                                    </div>
                                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-slate-600">
                                        <div>
                                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Property</span>
                                            @if ($ticket->property)
                                                <a href="{{ route('properties.show', $ticket->property->slug) }}" class="font-bold text-[#1d3c34] hover:underline">
                                                    {{ $ticket->property->title }}
                                                </a>
                                            @else
                                                <span>N/A</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Category</span>
                                            <span class="font-bold">{{ ucfirst($ticket->category) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Submitted</span>
                                            <span>{{ $ticket->created_at->format('M d, Y · H:i') }}</span>
                                        </div>
                                    </div>
                                    <p class="mt-3 text-xs text-slate-700 bg-[#fffaf2] p-3 rounded-xl border border-[#ecd9be]">{{ $ticket->description }}</p>
                                    @if ($ticket->resolution_notes)
                                        <div class="mt-2 text-xs text-emerald-800 bg-emerald-50 p-2.5 rounded-xl border border-emerald-200">
                                            <strong>Resolution Note:</strong> {{ $ticket->resolution_notes }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
            @endif

            {{-- 11. GUEST & CLIENT REVIEWS SECTION --}}
            <section data-section="reviews" class="hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">My Property &amp; Stay Reviews</h2>
                        <p class="text-sm text-slate-600">Verified reviews and guest ratings you have submitted for your stays and leased homes.</p>
                    </div>
                    @if ($bookings->isNotEmpty())
                        <button type="button" onclick="openGuestReviewModal({{ $bookings->first()->property_id }}, {{ $bookings->first()->id }}, '{{ addslashes($bookings->first()->property?->title ?? 'Stay Accommodation') }}')" class="inline-flex items-center gap-2 rounded-2xl bg-[#102b25] px-4 py-2.5 text-xs font-bold text-[#d4af37] shadow-sm hover:bg-black transition cursor-pointer">
                            <span>⭐</span>
                            <span>Write a Review</span>
                        </button>
                    @endif
                </div>

                @if ($myReviews->isEmpty())
                    <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                        <span class="text-4xl">⭐</span>
                        <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Reviews Submitted Yet</h3>
                        <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                            Share your verified stay experience, rating hospitality, cleanliness, accuracy, communication, and location to help future guests.
                        </p>
                        @if ($bookings->isNotEmpty())
                            <button type="button" onclick="openGuestReviewModal({{ $bookings->first()->property_id }}, {{ $bookings->first()->id }}, '{{ addslashes($bookings->first()->property?->title ?? 'Recent Stay') }}')" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#102b25] px-5 py-2.5 text-xs font-black text-[#d4af37] hover:bg-black transition">
                                <span>⭐</span>
                                <span>Rate Recent Stay</span>
                            </button>
                        @else
                            <a href="{{ route('stays.index') }}" class="mt-4 inline-block rounded-xl bg-[#102b25] px-5 py-2.5 text-xs font-black text-[#d4af37] hover:bg-black transition">
                                Discover Luxury Stays
                            </a>
                        @endif
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($myReviews as $rev)
                            <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs flex flex-col justify-between gap-4">
                                <div>
                                    <div class="flex items-start justify-between gap-3 border-b border-[#f0e4cf] pb-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <div class="flex text-amber-500 text-sm">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        @if ($i <= $rev->rating) ★ @else <span class="text-slate-300">★</span> @endif
                                                    @endfor
                                                </div>
                                                <span class="font-black text-sm text-[#102b25]">{{ $rev->rating }}.0</span>
                                                @if ($rev->is_verified_stay)
                                                    <span class="rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase px-2 py-0.5">
                                                        ✓ Verified Stay
                                                    </span>
                                                @endif
                                            </div>
                                            <h3 class="text-base font-black text-[#102b25] mt-1">
                                                {{ $rev->review_title ?: 'Stay Experience Review' }}
                                            </h3>
                                        </div>
                                        <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase @if($rev->status === 'published') bg-emerald-100 text-emerald-800 @else bg-slate-100 text-slate-600 @endif">
                                            {{ $rev->status }}
                                        </span>
                                    </div>

                                    @if ($rev->property)
                                        <div class="mt-3 flex items-center gap-3">
                                            <img src="{{ $rev->property->primary_image_url }}" alt="{{ $rev->property->title }}" class="h-12 w-12 rounded-xl object-cover shrink-0" loading="lazy" onerror="this.onerror=null;this.src='{{ asset($rev->property->default_background_image) }}';">
                                            <div>
                                                <a href="{{ route('properties.show', $rev->property->slug) }}" class="font-bold text-xs text-[#1d3c34] hover:underline">
                                                    {{ $rev->property->title }}
                                                </a>
                                                <p class="text-[11px] text-slate-500">📍 {{ $rev->property->location ?? 'Addis Ababa' }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Sub-ratings breakdown --}}
                                    <div class="mt-4 grid grid-cols-3 sm:grid-cols-5 gap-2 rounded-2xl bg-[#fcf9f5] p-3 text-center border border-[#ebdcc9]">
                                        <div>
                                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Cleanliness</span>
                                            <span class="font-black text-xs text-[#102b25]">★ {{ $rev->cleanliness_rating }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Accuracy</span>
                                            <span class="font-black text-xs text-[#102b25]">★ {{ $rev->accuracy_rating }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Service</span>
                                            <span class="font-black text-xs text-[#102b25]">★ {{ $rev->communication_rating }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Location</span>
                                            <span class="font-black text-xs text-[#102b25]">★ {{ $rev->location_rating }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Value</span>
                                            <span class="font-black text-xs text-[#102b25]">★ {{ $rev->value_rating }}</span>
                                        </div>
                                    </div>

                                    <p class="mt-3 text-xs text-slate-700 bg-white p-3 rounded-xl border border-[#ebdcc9] leading-relaxed">
                                        "{{ $rev->review_body }}"
                                    </p>
                                </div>

                                <div class="pt-2 border-t border-[#f0e4cf] flex items-center justify-between text-[11px] text-slate-400">
                                    <span>Posted: {{ $rev->created_at->format('M d, Y') }}</span>
                                    @if ($rev->booking)
                                        <span class="font-mono text-[10px]">Booking Ref: {{ $rev->booking->booking_reference }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- 12. PROPERTIES: ADMIN CORPORATE VS LICENSED AGENT SECTION --}}
            <section data-section="properties-desk" class="hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">Properties Management Desk</h2>
                        <p class="text-sm text-slate-600">Distinct channels for GTM Corporate Headquarter properties vs Licensed Agent portfolios.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" id="tab-btn-all-props" onclick="switchPropertyDeskTab('all')" class="rounded-xl bg-[#102b25] px-4 py-2 text-xs font-black text-[#d4af37] transition">
                            🌐 All Properties ({{ $allProperties instanceof \Illuminate\Pagination\LengthAwarePaginator ? $allProperties->total() : $allProperties->count() }})
                        </button>
                        <button type="button" id="tab-btn-campaign-props" onclick="switchPropertyDeskTab('campaigns')" class="rounded-xl bg-white border border-[#ebdcc9] px-4 py-2 text-xs font-bold text-slate-600 hover:bg-[#fcf9f5] transition">
                            📢 Special Campaigns ({{ $activeCampaigns->count() }})
                        </button>
                        <button type="button" id="tab-btn-admin-props" onclick="switchPropertyDeskTab('admin')" class="rounded-xl bg-white border border-[#ebdcc9] px-4 py-2 text-xs font-bold text-slate-600 hover:bg-[#fcf9f5] transition">
                            🏛️ Corporate HQ ({{ $adminProperties->count() }})
                        </button>
                        <button type="button" id="tab-btn-agent-props" onclick="switchPropertyDeskTab('agent')" class="rounded-xl bg-white border border-[#ebdcc9] px-4 py-2 text-xs font-bold text-slate-600 hover:bg-[#fcf9f5] transition">
                            👤 Agent Portfolios ({{ $agentProperties->count() }})
                        </button>
                    </div>
                </div>

                {{-- Tab 0: All Properties (Unified Marketplace Feed) --}}
                <div id="property-tab-all" class="space-y-4">
                    <div class="rounded-2xl bg-[#fcf9f5] border border-[#ebdcc9] p-4 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🌐</span>
                            <div>
                                <h3 class="text-sm font-black text-[#102b25]">Complete Real Estate Inventory</h3>
                                <p class="text-xs text-slate-600">All authenticated residential, commercial, luxury, and promotional properties across Ethiopia.</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-[#102b25] text-[#d4af37] text-xs font-black px-3 py-1">
                            {{ $allProperties instanceof \Illuminate\Pagination\LengthAwarePaginator ? $allProperties->total() : $allProperties->count() }} Properties Live
                        </span>
                    </div>

                    @if ($allProperties->isEmpty())
                        <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                            <span class="text-4xl">🏡</span>
                            <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Properties Available</h3>
                            <p class="text-sm text-slate-500 mt-1">Check back shortly for new listings.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach ($allProperties as $p)
                                <div class="rounded-3xl border border-[#d9cab3] bg-white overflow-hidden shadow-xs flex flex-col justify-between hover:shadow-md transition">
                                    <div>
                                        <div class="relative h-44 w-full bg-[#102b25]/10 overflow-hidden">
                                            <img src="{{ $p->primary_image_url }}" alt="{{ $p->title }}" class="h-full w-full object-cover" loading="lazy" onerror="this.onerror=null;this.src='{{ asset($p->default_background_image) }}';">
                                            @if ($p->campaigns->whereIn('status', ['published', 'approved'])->isNotEmpty())
                                                <span class="absolute top-3 left-3 rounded-full bg-rose-600 text-white text-[10px] font-black uppercase px-2.5 py-1 shadow-sm animate-pulse">
                                                    🔥 Campaign Featured
                                                </span>
                                            @elseif ($p->agent_id)
                                                <span class="absolute top-3 left-3 rounded-full bg-blue-900 text-white text-[10px] font-black uppercase px-2.5 py-1 shadow-sm">
                                                    👤 Agent Listing
                                                </span>
                                            @else
                                                <span class="absolute top-3 left-3 rounded-full bg-[#102b25] text-[#d4af37] text-[10px] font-black uppercase px-2.5 py-1 shadow-sm">
                                                    🏛️ Direct GTM HQ
                                                </span>
                                            @endif
                                            <span class="absolute top-3 right-3 rounded-full bg-white/90 backdrop-blur-xs text-[#102b25] text-[10px] font-black uppercase px-2.5 py-1">
                                                {{ ucfirst($p->listing_type) }}
                                            </span>
                                        </div>
                                        <div class="p-4">
                                            <h4 class="font-black text-sm text-[#102b25] line-clamp-1">{{ $p->title }}</h4>
                                            <p class="text-xs text-slate-500 mt-0.5">📍 {{ $p->location ?? ($p->city ?? 'Addis Ababa') }}</p>
                                            <p class="mt-2 text-base font-black text-[#1d3c34]">
                                                {{ number_format($p->price, 2) }} ETB
                                                @if ($p->listing_type === 'rent') <span class="text-xs font-normal text-slate-500">/ mo</span> @endif
                                            </p>
                                            <div class="mt-2 flex items-center gap-3 text-[11px] text-slate-600 font-semibold">
                                                <span>🛏️ {{ $p->bedrooms ?? 0 }} Beds</span>
                                                <span>🚿 {{ $p->bathrooms ?? 0 }} Baths</span>
                                                <span>📐 {{ $p->area_sqm ?? 0 }} m²</span>
                                            </div>
                                            @if ($p->agent)
                                                <div class="mt-3 rounded-xl bg-[#fcf9f5] border border-[#ebdcc9] p-2 flex items-center gap-2">
                                                    <div class="h-6 w-6 rounded-full bg-[#102b25] text-white flex items-center justify-center text-[10px] font-black shrink-0">
                                                        {{ substr($p->agent->name, 0, 1) }}
                                                    </div>
                                                    <div class="overflow-hidden text-[11px]">
                                                        <p class="font-bold text-[#102b25] truncate">{{ $p->agent->name }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="p-4 pt-0">
                                        <a href="{{ route('properties.show', $p->slug ?: $p->id) }}" class="block w-full text-center rounded-xl bg-[#102b25] text-white py-2 text-xs font-bold hover:bg-black transition">
                                            View Property Details →
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($allProperties instanceof \Illuminate\Pagination\LengthAwarePaginator && $allProperties->hasPages())
                            <div class="mt-6 border-t border-[#ebdcc9] pt-4">
                                {{ $allProperties->fragment('properties-desk')->links() }}
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Tab Special: Active Promotional Campaigns --}}
                <div id="property-tab-campaigns" class="hidden space-y-4">
                    <div class="rounded-2xl bg-gradient-to-r from-[#1d3c34] to-[#264d41] p-4 text-white flex items-center justify-between">
                        <div>
                            <span class="rounded bg-[#d4af37] text-[#1d3c34] text-[10px] font-black px-2 py-0.5 uppercase tracking-wider">Promotional Desk</span>
                            <h3 class="text-base font-black mt-1">Active Marketing Campaigns &amp; Special Deals</h3>
                            <p class="text-xs text-[#dfeee4]">Verified promotions, diaspora investment opportunities, and seasonal rate discounts.</p>
                        </div>
                        <span class="rounded-full bg-white/20 border border-white/20 px-3 py-1 text-xs font-black">
                            {{ $activeCampaigns->count() }} Active
                        </span>
                    </div>

                    @if ($activeCampaigns->isEmpty())
                        <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                            <span class="text-4xl">📣</span>
                            <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Active Promotional Campaigns</h3>
                            <p class="text-sm text-slate-500 mt-1">Check back soon for upcoming holiday promotions and diaspora deals.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            @foreach ($activeCampaigns as $camp)
                                <div class="rounded-3xl border border-[#d9cab3] bg-white overflow-hidden shadow-xs hover:shadow-md transition flex flex-col justify-between">
                                    <div class="p-5 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="rounded-full bg-rose-100 text-rose-800 text-[10px] font-black uppercase px-2.5 py-0.5">
                                                🔥 {{ ucfirst(str_replace('_', ' ', $camp->campaign_type)) }}
                                            </span>
                                            <span class="text-[11px] font-bold text-slate-400">
                                                Target: {{ ucfirst($camp->target_audience ?: 'All Buyers') }}
                                            </span>
                                        </div>
                                        <h4 class="text-base font-black text-[#102b25]">{{ $camp->title }}</h4>
                                        <p class="text-xs font-bold text-[#8a5d13] italic">"{{ $camp->headline }}"</p>
                                        <p class="text-xs text-slate-700 line-clamp-3 leading-relaxed">{{ $camp->description }}</p>

                                        @if ($camp->property)
                                            <div class="mt-3 rounded-2xl border border-[#e8dccb] bg-[#fcfaf7] p-3 flex items-center justify-between gap-3">
                                                <div>
                                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Featured Asset</span>
                                                    <p class="text-xs font-black text-[#1d3c34] line-clamp-1">{{ $camp->property->title }}</p>
                                                    <p class="text-xs font-mono font-bold text-emerald-800 mt-0.5">ETB {{ number_format($camp->property->price, 0) }}</p>
                                                </div>
                                                <a href="{{ route('properties.show', $camp->property->slug ?: $camp->property->id) }}" class="rounded-xl bg-[#102b25] px-3 py-1.5 text-xs font-bold text-[#d4af37] hover:bg-black transition shrink-0">
                                                    View Asset ↗
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-5 pt-0 border-t border-[#f0e4cf] flex items-center justify-between text-xs text-slate-500">
                                        <span>Agent: <strong class="text-[#102b25]">{{ $camp->agent?->name ?: 'GTM Corporate' }}</strong></span>
                                        @if ($camp->property)
                                            <a href="{{ route('properties.show', $camp->property->slug ?: $camp->property->id) }}" class="font-bold text-emerald-800 underline">
                                                Full Listing Details &rarr;
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Tab A: Admin Direct Properties --}}
                <div id="property-tab-admin" class="hidden space-y-4">
                    <div class="rounded-2xl bg-[#fcf9f5] border border-[#ebdcc9] p-4 flex items-center gap-3">
                        <span class="text-2xl">🏛️</span>
                        <div>
                            <h3 class="text-sm font-black text-[#102b25]">GTM Real Estate Corporate Managed</h3>
                            <p class="text-xs text-slate-600">Directly verified, managed, and executed by GTM Real Estate headquarter executive administrators.</p>
                        </div>
                    </div>

                    @if ($adminProperties->isEmpty())
                        <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                            <span class="text-4xl">🏛️</span>
                            <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Corporate Properties Available</h3>
                            <p class="text-sm text-slate-500 mt-1">Check back shortly for new corporate releases.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach ($adminProperties as $p)
                                <div class="rounded-3xl border border-[#d9cab3] bg-white overflow-hidden shadow-xs flex flex-col justify-between hover:shadow-md transition">
                                    <div>
                                        <div class="relative h-44 w-full bg-[#102b25]/10 overflow-hidden">
                                            <img src="{{ $p->primary_image_url }}" alt="{{ $p->title }}" class="h-full w-full object-cover" loading="lazy" onerror="this.onerror=null;this.src='{{ asset($p->default_background_image) }}';">
                                            <span class="absolute top-3 left-3 rounded-full bg-[#102b25] text-[#d4af37] text-[10px] font-black uppercase px-2.5 py-1 shadow-sm">
                                                🏛️ Direct GTM HQ
                                            </span>
                                            <span class="absolute top-3 right-3 rounded-full bg-white/90 backdrop-blur-xs text-[#102b25] text-[10px] font-black uppercase px-2.5 py-1">
                                                {{ ucfirst($p->listing_type) }}
                                            </span>
                                        </div>
                                        <div class="p-4">
                                            <h4 class="font-black text-sm text-[#102b25] line-clamp-1">{{ $p->title }}</h4>
                                            <p class="text-xs text-slate-500 mt-0.5">📍 {{ $p->location ?? 'Addis Ababa' }}</p>
                                            <p class="mt-2 text-base font-black text-[#1d3c34]">
                                                {{ number_format($p->price, 2) }} ETB
                                                @if ($p->listing_type === 'rent') <span class="text-xs font-normal text-slate-500">/ mo</span> @endif
                                            </p>
                                            <div class="mt-2 flex items-center gap-3 text-[11px] text-slate-600 font-semibold">
                                                <span>🛏️ {{ $p->bedrooms ?? 0 }} Beds</span>
                                                <span>🚿 {{ $p->bathrooms ?? 0 }} Baths</span>
                                                <span>📐 {{ $p->area_sqm ?? 0 }} m²</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4 pt-0">
                                        <a href="{{ route('properties.show', $p->slug) }}" class="block w-full text-center rounded-xl bg-[#102b25] text-white py-2 text-xs font-bold hover:bg-black transition">
                                            View Property Details →
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Tab B: Agent Managed Properties --}}
                <div id="property-tab-agent" class="hidden space-y-4">
                    <div class="rounded-2xl bg-[#fcf9f5] border border-[#ebdcc9] p-4 flex items-center gap-3">
                        <span class="text-2xl">👤</span>
                        <div>
                            <h3 class="text-sm font-black text-[#102b25]">Licensed Agent Managed Portfolios</h3>
                            <p class="text-xs text-slate-600">Represented by certified licensed brokers and agency partners with dedicated agent contact.</p>
                        </div>
                    </div>

                    @if ($agentProperties->isEmpty())
                        <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                            <span class="text-4xl">👤</span>
                            <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Agent Properties Listed</h3>
                            <p class="text-sm text-slate-500 mt-1">Our certified agents are preparing new listings.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach ($agentProperties as $p)
                                <div class="rounded-3xl border border-[#d9cab3] bg-white overflow-hidden shadow-xs flex flex-col justify-between hover:shadow-md transition">
                                    <div>
                                        <div class="relative h-44 w-full bg-[#102b25]/10 overflow-hidden">
                                            <img src="{{ $p->primary_image_url }}" alt="{{ $p->title }}" class="h-full w-full object-cover" loading="lazy" onerror="this.onerror=null;this.src='{{ asset($p->default_background_image) }}';">
                                            <span class="absolute top-3 left-3 rounded-full bg-blue-900 text-white text-[10px] font-black uppercase px-2.5 py-1 shadow-sm">
                                                👤 Agent Listing
                                            </span>
                                            <span class="absolute top-3 right-3 rounded-full bg-white/90 backdrop-blur-xs text-[#102b25] text-[10px] font-black uppercase px-2.5 py-1">
                                                {{ ucfirst($p->listing_type) }}
                                            </span>
                                        </div>
                                        <div class="p-4">
                                            <h4 class="font-black text-sm text-[#102b25] line-clamp-1">{{ $p->title }}</h4>
                                            <p class="text-xs text-slate-500 mt-0.5">📍 {{ $p->location ?? 'Addis Ababa' }}</p>
                                            <p class="mt-2 text-base font-black text-[#1d3c34]">
                                                {{ number_format($p->price, 2) }} ETB
                                                @if ($p->listing_type === 'rent') <span class="text-xs font-normal text-slate-500">/ mo</span> @endif
                                            </p>
                                            @if ($p->agent)
                                                <div class="mt-3 rounded-xl bg-[#fcf9f5] border border-[#ebdcc9] p-2 flex items-center gap-2">
                                                    <div class="h-7 w-7 rounded-full bg-[#102b25] text-white flex items-center justify-center text-xs font-black shrink-0">
                                                        {{ substr($p->agent->name, 0, 1) }}
                                                    </div>
                                                    <div class="overflow-hidden text-[11px]">
                                                        <p class="font-bold text-[#102b25] truncate">{{ $p->agent->name }}</p>
                                                        <p class="text-slate-500 text-[10px] truncate">📞 {{ $p->agent->phone ?? 'Contact' }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="p-4 pt-0">
                                        <a href="{{ route('properties.show', $p->slug) }}" class="block w-full text-center rounded-xl bg-[#102b25] text-white py-2 text-xs font-bold hover:bg-black transition">
                                            View Property Details →
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Inquiries & Purchase Agreements Breakdown --}}
                <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs space-y-4">
                    <h3 class="text-base font-black text-[#102b25]">My Inquiries &amp; Contacts by Manager</h3>
                    <p class="text-xs text-slate-600">Track all messages sent to GTM HQ direct desk versus licensed property agents.</p>

                    @if ($inquiries->isEmpty())
                        <p class="text-xs text-slate-400 italic">No inquiries dispatched yet. Contact any property manager directly from listing pages.</p>
                    @else
                        <div class="divide-y divide-[#f0e4cf]">
                            @foreach ($inquiries as $inq)
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            @if ($inq->agent)
                                                <span class="rounded bg-blue-100 text-blue-800 text-[10px] font-black uppercase px-2 py-0.5">👤 Agent: {{ $inq->agent->name }}</span>
                                            @else
                                                <span class="rounded bg-[#102b25] text-[#d4af37] text-[10px] font-black uppercase px-2 py-0.5">🏛️ GTM Admin HQ</span>
                                            @endif
                                            <span class="font-bold text-[#102b25]">{{ $inq->subject ?: 'Property Inquiry' }}</span>
                                        </div>
                                        <p class="text-slate-600 mt-1 line-clamp-1">"{{ $inq->message }}"</p>
                                    </div>
                                    <div class="text-right text-[11px] text-slate-400 shrink-0">
                                        {{ $inq->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            {{-- 13. MY PROFILE SECTION --}}
            <section data-section="profile" class="hidden space-y-6">
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
                    @include('layouts.partials.profile-form', [
                        'user' => $user,
                        'profileAction' => route('profile.update'),
                        'profileHeading' => 'My Profile',
                        'profileSubheading' => 'Update your name, Ethiopian phone number, password, profile photo, and portal banner background.',
                        'profileRoleLabel' => ucfirst($user->role ?? 'Client'),
                        'showPortalBg' => true,
                    ])
                </div>
            </section>
        </main>
    </div>
</div>

    {{-- Guest Service / Housekeeping Modal --}}
    <div id="guest-service-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
        <div class="w-full max-w-lg rounded-3xl bg-[#fffaf2] border border-[#d9cab3] p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-[#ebdcc9] pb-3">
                <div>
                    <h3 class="text-lg font-black text-[#102b25]">Request Front Desk Service</h3>
                    <p id="modal-service-subtitle" class="text-xs text-slate-600">Housekeeping, amenities, and concierge</p>
                </div>
                <button type="button" onclick="closeGuestServiceModal()" class="text-slate-400 hover:text-slate-800 text-2xl font-bold cursor-pointer">&times;</button>
            </div>

            <form method="POST" action="{{ route('receptionist.service-requests.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" id="service-modal-booking-id" name="booking_id" value="">

                <div>
                    <label class="block text-xs font-bold text-slate-700">Room Number Assigned</label>
                    <input type="text" id="service-modal-room-number" readonly class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-slate-100 px-3 py-2 text-xs font-mono font-bold text-[#102b25]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Service Category</label>
                    <select name="service_type" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#102b25]">
                        <option value="room_cleaning">🧹 Room Cleaning / Complete Housekeeping</option>
                        <option value="extra_towels">🧺 Extra Linens, Pillows &amp; Towels</option>
                        <option value="maintenance">🔧 In-Room Repair / Maintenance (AC, Water, TV)</option>
                        <option value="airport_transfer">🚕 Airport Shuttle / Transfer Booking</option>
                        <option value="wake_up_call">⏰ Morning Wake-Up Call</option>
                        <option value="inquiry">ℹ️ Concierge / City Guide Inquiry</option>
                        <option value="other">🛎️ Other Special Guest Assistance</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Priority Level</label>
                    <select name="priority" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#102b25]">
                        <option value="normal">Normal Priority</option>
                        <option value="urgent">🔥 Urgent Priority (Immediate Attention)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Special Instructions / Description</label>
                    <textarea name="description" rows="3" required placeholder="Please describe what you need or preferred time..." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs text-[#102b25]"></textarea>
                </div>

                <div class="pt-3 border-t border-[#ebdcc9] flex items-center justify-end gap-3">
                    <button type="button" onclick="closeGuestServiceModal()" class="rounded-xl border border-[#d9cab3] px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-xl bg-[#102b25] text-[#d4af37] px-6 py-2.5 text-xs font-black hover:bg-black transition cursor-pointer shadow-sm">
                        Dispatch to Front Desk
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Guest Review Modal --}}
    <div id="guest-review-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
        <div class="w-full max-w-lg rounded-3xl bg-[#fffaf2] border border-[#d9cab3] p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-[#ebdcc9] pb-3">
                <div>
                    <h3 class="text-lg font-black text-[#102b25]">Rate &amp; Review Your Stay</h3>
                    <p id="modal-review-subtitle" class="text-xs text-slate-600">Share your verified guest experience</p>
                </div>
                <button type="button" onclick="closeGuestReviewModal()" class="text-slate-400 hover:text-slate-800 text-2xl font-bold cursor-pointer">&times;</button>
            </div>

            <form id="guest-review-form" method="POST" action="" class="space-y-4">
                @csrf
                <input type="hidden" id="review-modal-booking-id" name="booking_id" value="">

                <div>
                    <label class="block text-xs font-bold text-slate-700">Overall Rating (1 to 5 Stars)</label>
                    <div class="flex items-center gap-2 mt-1">
                        <select name="rating" required class="rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-sm font-black text-[#102b25]">
                            <option value="5">⭐⭐⭐⭐⭐ 5 - Exceptional</option>
                            <option value="4">⭐⭐⭐⭐ 4 - Very Good</option>
                            <option value="3">⭐⭐⭐ 3 - Good / Average</option>
                            <option value="2">⭐⭐ 2 - Below Expectations</option>
                            <option value="1">⭐ 1 - Disappointing</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 bg-[#fcf9f5] p-3 rounded-2xl border border-[#ebdcc9]">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700">Cleanliness</label>
                        <select name="cleanliness_rating" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-2 py-1.5 text-xs font-bold">
                            <option value="5">5 - Spotless</option>
                            <option value="4">4 - Very Clean</option>
                            <option value="3">3 - Clean</option>
                            <option value="2">2 - Needs Work</option>
                            <option value="1">1 - Poor</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700">Accuracy</label>
                        <select name="accuracy_rating" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-2 py-1.5 text-xs font-bold">
                            <option value="5">5 - Exactly as listed</option>
                            <option value="4">4 - Very accurate</option>
                            <option value="3">3 - Mostly accurate</option>
                            <option value="2">2 - Somewhat different</option>
                            <option value="1">1 - Misleading</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700">Communication &amp; Desk</label>
                        <select name="communication_rating" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-2 py-1.5 text-xs font-bold">
                            <option value="5">5 - Prompt &amp; Friendly</option>
                            <option value="4">4 - Responsive</option>
                            <option value="3">3 - Acceptable</option>
                            <option value="2">2 - Slow response</option>
                            <option value="1">1 - Unresponsive</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700">Location</label>
                        <select name="location_rating" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-2 py-1.5 text-xs font-bold">
                            <option value="5">5 - Prime &amp; Safe</option>
                            <option value="4">4 - Great</option>
                            <option value="3">3 - Decent</option>
                            <option value="2">2 - Inconvenient</option>
                            <option value="1">1 - Difficult to access</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Review Title (Optional)</label>
                    <input type="text" name="review_title" placeholder="e.g. Fantastic stay with superb hospitality!" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs text-[#102b25]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Your Detailed Experience (Min. 10 chars)</label>
                    <textarea name="review_body" rows="3" required minlength="10" placeholder="Tell us what you enjoyed most about the amenities, host, and location..." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs text-[#102b25]"></textarea>
                </div>

                <div class="pt-3 border-t border-[#ebdcc9] flex items-center justify-end gap-3">
                    <button type="button" onclick="closeGuestReviewModal()" class="rounded-xl border border-[#d9cab3] px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-xl bg-[#102b25] text-[#d4af37] px-6 py-2.5 text-xs font-black hover:bg-black transition cursor-pointer shadow-sm">
                        Publish Review
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Rent Payment Modal --}}
    <div id="rent-payment-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
        <div class="w-full max-w-lg rounded-3xl bg-[#fffaf2] border border-[#d9cab3] p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-[#ebdcc9] pb-3">
                <div>
                    <h3 class="text-lg font-black text-[#102b25]">Pay Rent Installment</h3>
                    <p id="modal-rent-subtitle" class="text-xs text-slate-600">Submit installment payment</p>
                </div>
                <button type="button" onclick="closeRentPaymentModal()" class="text-slate-400 hover:text-slate-800 text-2xl font-bold cursor-pointer">&times;</button>
            </div>

            <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="payment_type" value="rent_installment">
                <input type="hidden" name="payable_type" value="App\Models\RentSchedule">
                <input type="hidden" id="modal-schedule-id" name="payable_id" value="">
                <input type="hidden" id="modal-property-id" name="property_id" value="">

                <div>
                    <label class="block text-xs font-bold text-slate-700">Amount Due (ETB)</label>
                    <input type="number" id="modal-amount-input" name="amount" required step="any" min="1" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-sm font-bold text-[#102b25]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Payer Name</label>
                        <input type="text" name="payer_name" value="{{ $user->name }}" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Payer Email</label>
                        <input type="email" name="payer_email" value="{{ $user->email }}" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Payment Channel</label>
                    <select name="payment_method" id="modal-payment-method" onchange="handlePaymentMethodChange(this.value)" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold">
                        <option value="telebirr">Telebirr — Instant Verification</option>
                        <option value="cbe_birr">CBE Birr — Instant / Transfer</option>
                        <option value="bank_transfer">Commercial Bank of Ethiopia (CBE Slip Upload)</option>
                    </select>
                </div>

                {{-- Bank Transfer Fields (visible when bank_transfer is chosen) --}}
                <div id="bank-slip-fields" class="hidden space-y-3 pt-2 border-t border-[#ebdcc9]">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Bank Reference / Txn Number</label>
                        <input type="text" name="bank_transaction_reference" placeholder="e.g. FT261003XYZ" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Upload Bank Deposit Slip (JPG, PNG, PDF)</label>
                        <input type="file" name="deposit_slip" accept="image/*,.pdf" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                    </div>
                </div>

                <div class="pt-3 border-t border-[#ebdcc9] flex items-center justify-end gap-3">
                    <button type="button" onclick="closeRentPaymentModal()" class="rounded-xl border border-[#d9cab3] px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-xl bg-[#102b25] text-[#d4af37] px-6 py-2.5 text-xs font-black hover:bg-black transition cursor-pointer shadow-sm">
                        Confirm &amp; Process Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    @include('properties.partials.payment-modal')

    {{-- Direct Report / Complaint to Admin HQ Modal --}}
    <div id="direct-report-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg rounded-3xl bg-white p-6 md:p-8 shadow-2xl border border-rose-100">
            <button type="button" onclick="closeDirectReportModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 font-bold text-xl">
                    🚨
                </div>
                <div>
                    <h3 class="text-xl font-black text-[#1d3c34]">Send Report to Admin HQ</h3>
                    <p class="text-xs text-slate-500">Direct trust &amp; safety inquiry, broker complaint, or fraudulent activity alert.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('reports.store-direct') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Report Category / Reason <span class="text-red-500">*</span></label>
                    <select name="reason" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-rose-500">
                        <option value="scam">🚨 Suspected Scam / Payment Fraud</option>
                        <option value="agent_misconduct">👤 Broker / Agent Misconduct or Fake Representation</option>
                        <option value="wrong_price">💰 Misleading or Inaccurate Property Pricing</option>
                        <option value="fake_property">❌ Fake / Non-Existent Property</option>
                        <option value="suspicious_documents">📑 Suspicious or Inauthentic Legal Documents</option>
                        <option value="service_complaint">🛎️ Front Desk / Customer Service Complaint</option>
                        <option value="other">ℹ️ Other Incident or Marketplace Violation</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Subject / Brief Title</label>
                    <input type="text" name="subject" placeholder="e.g. Complaint regarding broker misconduct on listing" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-medium text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-rose-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Detailed Statement &amp; Evidence <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="3" required minlength="10" placeholder="Please describe what happened in detail so the Admin HQ team can investigate..." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-medium text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Contact Email for Admin Follow-up</label>
                    <input type="email" name="contact_email" value="{{ $user->email }}" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-slate-50 px-3.5 py-2 text-xs font-medium text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-rose-500">
                </div>

                <div class="rounded-xl bg-rose-50 p-3 text-[11px] text-rose-800">
                    🔒 Delivered directly to GTM Administration. An automated high-priority alert will be sent to the administrator dashboard.
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="closeDirectReportModal()" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-xl bg-rose-600 px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-white hover:bg-rose-700 transition shadow-sm cursor-pointer">
                        Dispatch Report to Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Portal JavaScript for Section Navigation, Modals, and Tab Switchers --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navLinks = document.querySelectorAll('.nav-section-link');
            const sections = document.querySelectorAll('[data-section]');

            function activateSection(target) {
                sections.forEach(sec => {
                    if (sec.dataset.section === target) {
                        sec.classList.remove('hidden');
                    } else {
                        sec.classList.add('hidden');
                    }
                });

                navLinks.forEach(link => {
                    if (link.dataset.targetSection === target) {
                        link.classList.remove('text-[#dfeee4]', 'text-slate-700', 'border-transparent');
                        link.classList.add('bg-white/15', 'text-white', 'font-bold');
                        link.setAttribute('aria-current', 'page');
                    } else {
                        link.classList.remove('bg-white/15', 'text-white', 'font-bold');
                        link.classList.add('text-[#dfeee4]');
                        link.removeAttribute('aria-current');
                    }
                });

                // Auto-close mobile drawer if open
                const mobileMenu = document.getElementById('portal-mobile-menu');
                if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }

                // Auto-close notifications dropdown
                const notifDropdown = document.getElementById('portal-notifications-dropdown');
                if (notifDropdown && !notifDropdown.classList.contains('hidden')) {
                    notifDropdown.classList.add('hidden');
                }

                if (updateHash && window.location.hash !== '#' + target) {
                    history.replaceState(null, '', '#' + target);
                }

                try {
                    sessionStorage.setItem('dashboard_active_section', target);
                    sessionStorage.setItem('dashboard_active_section_' + window.location.pathname, target);
                } catch (e) {}
            }

            window.activateSection = activateSection;
            window.showPortalSection = activateSection;

            navLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    const target = this.dataset.targetSection;
                    if (!target) return;
                    e.preventDefault();
                    activateSection(target, true);
                });
            });

            // Notification dropdown toggle
            const notifBtn = document.getElementById('portal-notifications-btn');
            const notifDropdown = document.getElementById('portal-notifications-dropdown');
            if (notifBtn && notifDropdown) {
                notifBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    notifDropdown.classList.toggle('hidden');
                });
                document.addEventListener('click', function (e) {
                    if (!notifDropdown.contains(e.target) && !notifBtn.contains(e.target)) {
                        notifDropdown.classList.add('hidden');
                    }
                });
            }

            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('portal-mobile-menu-btn');
            const mobileMenu = document.getElementById('portal-mobile-menu');
            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    mobileMenu.classList.toggle('hidden');
                });
            }

            let savedSessionSection = null;
            try {
                savedSessionSection = sessionStorage.getItem('dashboard_active_section_' + window.location.pathname) || sessionStorage.getItem('dashboard_active_section');
            } catch (e) {}

            // Initial section from URL hash or sessionStorage
            const hash = window.location.hash.replace('#', '');
            const initialSection = hash || savedSessionSection || 'overview';
            if (document.querySelector(`[data-section="${initialSection}"]`)) {
                activateSection(initialSection, !!hash);
            } else {
                activateSection('overview', false);
            }

            window.addEventListener('hashchange', function () {
                const newHash = window.location.hash.replace('#', '');
                if (newHash && document.querySelector(`[data-section="${newHash}"]`)) {
                    activateSection(newHash, false);
                }
            });

            // Rent Payment Modal
            window.openRentPaymentModal = function (scheduleId, leaseCode, periodLabel, amountDue, propertyTitle, propertyId) {
                const modal = document.getElementById('rent-payment-modal');
                document.getElementById('modal-schedule-id').value = scheduleId;
                document.getElementById('modal-property-id').value = propertyId;
                document.getElementById('modal-amount-input').value = amountDue;
                document.getElementById('modal-rent-subtitle').textContent = `${propertyTitle} — ${periodLabel} (${leaseCode})`;
                modal.classList.remove('hidden');
            };

            window.closeRentPaymentModal = function () {
                document.getElementById('rent-payment-modal').classList.add('hidden');
            };

            window.handlePaymentMethodChange = function (method) {
                const bankFields = document.getElementById('bank-slip-fields');
                if (method === 'bank_transfer') {
                    bankFields.classList.remove('hidden');
                } else {
                    bankFields.classList.add('hidden');
                }
            };

            // Guest Housekeeping / Service Modal
            window.openGuestServiceModal = function (bookingId, roomNumber, stayTitle) {
                const modal = document.getElementById('guest-service-modal');
                document.getElementById('service-modal-booking-id').value = bookingId;
                document.getElementById('service-modal-room-number').value = roomNumber || 'Pending Check-In';
                document.getElementById('modal-service-subtitle').textContent = `Stay: ${stayTitle}`;
                modal.classList.remove('hidden');
            };

            window.closeGuestServiceModal = function () {
                document.getElementById('guest-service-modal').classList.add('hidden');
            };

            // Guest Review Modal
            window.openGuestReviewModal = function (propertyId, bookingId, propertyTitle) {
                const modal = document.getElementById('guest-review-modal');
                const form = document.getElementById('guest-review-form');
                form.action = `/properties/${propertyId}/reviews`;
                document.getElementById('review-modal-booking-id').value = bookingId || '';
                document.getElementById('modal-review-subtitle').textContent = `Reviewing: ${propertyTitle}`;
                modal.classList.remove('hidden');
            };

            window.closeGuestReviewModal = function () {
                document.getElementById('guest-review-modal').classList.add('hidden');
            };

            // Direct Admin Report Modal
            window.openDirectReportModal = function () {
                const modal = document.getElementById('direct-report-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                }
            };

            window.closeDirectReportModal = function () {
                const modal = document.getElementById('direct-report-modal');
                if (modal) {
                    modal.classList.add('hidden');
                }
            };

            // Tab switchers inside sections
            window.switchMaintenanceTab = function (tab) {
                const housekeepingContent = document.getElementById('maintenance-tab-housekeeping');
                const residentialContent = document.getElementById('maintenance-tab-residential');
                const btnHousekeeping = document.getElementById('tab-btn-housekeeping');
                const btnResidential = document.getElementById('tab-btn-residential');

                if (tab === 'housekeeping') {
                    housekeepingContent.classList.remove('hidden');
                    residentialContent.classList.add('hidden');
                    btnHousekeeping.className = 'rounded-xl bg-[#102b25] px-4 py-2 text-xs font-black text-[#d4af37] transition';
                    btnResidential.className = 'rounded-xl bg-white border border-[#ebdcc9] px-4 py-2 text-xs font-bold text-slate-600 hover:bg-[#fcf9f5] transition';
                } else {
                    residentialContent.classList.remove('hidden');
                    housekeepingContent.classList.add('hidden');
                    btnResidential.className = 'rounded-xl bg-[#102b25] px-4 py-2 text-xs font-black text-[#d4af37] transition';
                    btnHousekeeping.className = 'rounded-xl bg-white border border-[#ebdcc9] px-4 py-2 text-xs font-bold text-slate-600 hover:bg-[#fcf9f5] transition';
                }
            };

            window.switchPropertyDeskTab = function (tab) {
                const tabs = {
                    'all': document.getElementById('property-tab-all'),
                    'campaigns': document.getElementById('property-tab-campaigns'),
                    'admin': document.getElementById('property-tab-admin'),
                    'agent': document.getElementById('property-tab-agent')
                };
                const btns = {
                    'all': document.getElementById('tab-btn-all-props'),
                    'campaigns': document.getElementById('tab-btn-campaign-props'),
                    'admin': document.getElementById('tab-btn-admin-props'),
                    'agent': document.getElementById('tab-btn-agent-props')
                };

                const activeBtnClass = 'rounded-xl bg-[#102b25] px-4 py-2 text-xs font-black text-[#d4af37] transition shadow-xs';
                const inactiveBtnClass = 'rounded-xl bg-white border border-[#ebdcc9] px-4 py-2 text-xs font-bold text-slate-600 hover:bg-[#fcf9f5] transition';

                Object.keys(tabs).forEach(k => {
                    if (tabs[k]) {
                        if (k === tab) {
                            tabs[k].classList.remove('hidden');
                        } else {
                            tabs[k].classList.add('hidden');
                        }
                    }
                    if (btns[k]) {
                        btns[k].className = (k === tab) ? activeBtnClass : inactiveBtnClass;
                    }
                });
            };
        });
    </script>
    @include('layouts.partials.sidebar-scroll-preserver')
</body>
</html>
