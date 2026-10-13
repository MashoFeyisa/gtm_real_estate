<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receptionist Desk &amp; Front Desk — {{ $siteBrand['name'] }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-styles')
    <script>
        (function() {
            try {
                const saved = localStorage.getItem('gtm_receptionist_palette');
                const palettes = {
                    emerald: {
                        primary: '#1d3c34', primary_hover: '#254d43', primary_dark: '#102b25', primary_light: '#dfeee4',
                        secondary: '#d9cab3', secondary_light: '#e7ddca', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#f9ecd0',
                        site_bg: '#f5f0e7', card_bg: '#fdfaf5', text_dark: '#1d3c34', text_muted: '#587165',
                        primary_rgb: '29, 60, 52', primary_dark_rgb: '16, 43, 37', primary_light_rgb: '223, 238, 228', secondary_rgb: '217, 202, 179', accent_rgb: '212, 175, 55', accent_light_rgb: '249, 236, 208'
                    },
                    oak: {
                        primary: '#5c3a21', primary_hover: '#73492a', primary_dark: '#3d2514', primary_light: '#f5ede4',
                        secondary: '#dfd3c3', secondary_light: '#eee6dc', accent: '#c2883f', accent_dark: '#966324', accent_light: '#fbf1e2',
                        site_bg: '#f7f2ea', card_bg: '#fffdfa', text_dark: '#3d2514', text_muted: '#786252',
                        primary_rgb: '92, 58, 33', primary_dark_rgb: '61, 37, 20', primary_light_rgb: '245, 237, 228', secondary_rgb: '223, 211, 195', accent_rgb: '194, 136, 63', accent_light_rgb: '251, 241, 226'
                    },
                    cream: {
                        primary: '#7c5e43', primary_hover: '#926f50', primary_dark: '#563e2a', primary_light: '#f9f5ee',
                        secondary: '#e6dcce', secondary_light: '#f2ede4', accent: '#c59d5f', accent_dark: '#9e7a40', accent_light: '#fcf6ec',
                        site_bg: '#faf7f2', card_bg: '#ffffff', text_dark: '#3d2e22', text_muted: '#877464',
                        primary_rgb: '124, 94, 67', primary_dark_rgb: '86, 62, 42', primary_light_rgb: '249, 245, 238', secondary_rgb: '230, 220, 206', accent_rgb: '197, 157, 95', accent_light_rgb: '252, 246, 236'
                    },
                    sapphire: {
                        primary: '#1e3a5f', primary_hover: '#2b5282', primary_dark: '#0f1f33', primary_light: '#dbeafe',
                        secondary: '#cbd5e1', secondary_light: '#e2e8f0', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#fef3c7',
                        site_bg: '#f1f5f9', card_bg: '#ffffff', text_dark: '#0f172a', text_muted: '#475569',
                        primary_rgb: '30, 58, 95', primary_dark_rgb: '15, 31, 51', primary_light_rgb: '219, 234, 254', secondary_rgb: '203, 213, 225', accent_rgb: '212, 175, 55', accent_light_rgb: '254, 243, 199'
                    },
                    bordeaux: {
                        primary: '#4c1d24', primary_hover: '#632530', primary_dark: '#2d0f14', primary_light: '#fae8eb',
                        secondary: '#d9c7c9', secondary_light: '#ece1e3', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#fef3c7',
                        site_bg: '#fbf7f7', card_bg: '#ffffff', text_dark: '#260a0f', text_muted: '#6b4c52',
                        primary_rgb: '76, 29, 36', primary_dark_rgb: '45, 15, 20', primary_light_rgb: '250, 232, 235', secondary_rgb: '217, 199, 201', accent_rgb: '212, 175, 55', accent_light_rgb: '254, 243, 199'
                    },
                    obsidian: {
                        primary: '#18181b', primary_hover: '#27272a', primary_dark: '#09090b', primary_light: '#e4e4e7',
                        secondary: '#cbd5e1', secondary_light: '#e2e8f0', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#fef3c7',
                        site_bg: '#f8fafc', card_bg: '#ffffff', text_dark: '#09090b', text_muted: '#64748b',
                        primary_rgb: '24, 24, 27', primary_dark_rgb: '9, 9, 11', primary_light_rgb: '228, 228, 231', secondary_rgb: '203, 213, 225', accent_rgb: '212, 175, 55', accent_light_rgb: '254, 243, 199'
                    }
                };
                if (saved && palettes[saved]) {
                    const p = palettes[saved];
                    const root = document.documentElement;
                    for (const [k, v] of Object.entries(p)) {
                        root.style.setProperty('--theme-' + k.replace(/_/g, '-'), v);
                    }
                }
            } catch (e) {}
        })();
    </script>
</head>
<body class="bg-[#f5f0e7] text-slate-800 antialiased scroll-smooth">
    @include('layouts.partials.impersonation-banner')
    @php
        $roleLabel = $isAdmin ? 'Administrator' : 'Front Desk Receptionist';
        $propName = $currentProperty ? $currentProperty->title : 'All GTM Properties';
    @endphp

    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside id="receptionist-sidebar" class="dashboard-sidebar sidebar-scroll-area w-72 shrink-0 border-r border-[#d9cab3] bg-[#1d3c34] p-6 text-[#f8f3eb] flex flex-col justify-between" style="position: sticky; top: 0; height: 100dvh; overflow-y: auto;">
            <div>
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ $siteBrand['logoUrl'] }}" alt="{{ $siteBrand['name'] }} Logo" class="h-10 w-10 rounded-xl object-contain ring-2 ring-[#d9cab3] bg-white/10">
                <span class="text-xl font-black tracking-tight text-white">{{ $siteBrand['name'] }}</span>
            </a>

            {{-- Sidebar Skeleton placeholder for layout height stability while permissions/badges evaluate --}}
            <div class="sidebar-skeleton hidden pointer-events-none opacity-0 h-0 overflow-hidden" aria-hidden="true">
                <div class="h-9 w-full rounded-xl bg-white/5 animate-pulse mb-2"></div>
                <div class="h-9 w-full rounded-xl bg-white/5 animate-pulse mb-2"></div>
            </div>

            <div class="mt-4 rounded-xl border border-white/10 bg-white/5 p-3">
                <p class="text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Active Desk</p>
                <p class="text-sm font-black text-white truncate" title="{{ $propName }}">{{ $propName }}</p>
                @if ($currentProperty && $currentProperty->city)
                    <p class="text-[11px] text-[#dfeee4]/80">📍 {{ $currentProperty->city }}</p>
                @endif
            </div>

            <nav class="mt-6 space-y-1.5 min-h-[420px]">
                <a href="{{ route('home') }}" class="flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🏠</span>
                        <span>Home</span>
                    </span>
                </a>
                <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center justify-between rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
                    <span class="flex items-center gap-2.5">
                        <span>🛎️</span>
                        <span>Front Desk Hub</span>
                    </span>
                </a>
                <a href="#stay-listings" data-target-section="stay-listings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🏡</span>
                        <span>Host Stays <span class="text-xs font-normal opacity-75">(Host / Stay Listings)</span></span>
                    </span>
                    <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">{{ $stats['stay_properties_count'] ?? 0 }}</span>
                </a>
                <a href="#check-ins" data-target-section="check-ins" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🧳</span>
                        <span>Arrivals</span>
                    </span>
                    @if ($stats['today_checkins_count'] > 0)
                        <span class="rounded-full bg-[#d4af37] px-2 py-0.5 text-xs font-black text-[#102b25]">{{ $stats['today_checkins_count'] }}</span>
                    @else
                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">0</span>
                    @endif
                </a>
                <a href="#in-house" data-target-section="in-house" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🚪</span>
                        <span>In-House</span>
                    </span>
                    @if ($stats['in_house_count'] > 0)
                        <span class="rounded-full bg-emerald-500 px-2 py-0.5 text-xs font-black text-white">{{ $stats['in_house_count'] }}</span>
                    @else
                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">0</span>
                    @endif
                </a>
                <a href="#check-outs" data-target-section="check-outs" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>👋</span>
                        <span>Departures</span>
                    </span>
                    @if ($stats['today_checkouts_count'] > 0)
                        <span class="rounded-full bg-amber-400 px-2 py-0.5 text-xs font-black text-[#102b25]">{{ $stats['today_checkouts_count'] }}</span>
                    @else
                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">0</span>
                    @endif
                </a>
                <a href="#walk-in" data-target-section="walk-in" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>➕</span>
                        <span>Walk-In Registration</span>
                    </span>
                </a>
                <a href="#services" data-target-section="services" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🧹</span>
                        <span>Housekeeping</span>
                    </span>
                    @if ($stats['pending_requests_count'] > 0)
                        <span class="rounded-full bg-rose-500 px-2 py-0.5 text-xs font-black text-white">{{ $stats['pending_requests_count'] }}</span>
                    @else
                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">0</span>
                    @endif
                </a>
                <a href="#inquiries" data-target-section="inquiries" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>💬</span>
                        <span>Guest Inquiries <span class="text-xs font-normal opacity-75">(Guest Communication)</span></span>
                    </span>
                    @if (($stats['inquiries_count'] ?? 0) > 0)
                        <span class="rounded-full bg-emerald-400 px-2 py-0.5 text-xs font-black text-[#102b25]">{{ $stats['inquiries_count'] }}</span>
                    @else
                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">0</span>
                    @endif
                </a>
                <a href="#reviews" data-target-section="reviews" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>⭐</span>
                        <span>Reviews Desk <span class="text-xs font-normal opacity-75">(Guest Reviews Desk)</span></span>
                    </span>
                    @if (($stats['reviews_count'] ?? 0) > 0)
                        <span class="rounded-full bg-[#d4af37] px-2 py-0.5 text-xs font-black text-[#102b25]">{{ $stats['reviews_count'] }}</span>
                    @else
                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">0</span>
                    @endif
                </a>
                <a href="#shifts" data-target-section="shifts" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>💵</span>
                        <span>Shift Cash</span>
                    </span>
                    <span class="rounded-full bg-[#102b25] border border-[#d4af37]/40 px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Finance</span>
                </a>
                <a href="#maintenance" data-target-section="maintenance" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🔧</span>
                        <span>Maintenance and Repairs</span>
                    </span>
                    @if (($stats['maintenance_tickets_count'] ?? 0) > 0)
                        <span class="rounded-full bg-amber-400 px-2 py-0.5 text-xs font-black text-[#102b25]">{{ $stats['maintenance_tickets_count'] }}</span>
                    @endif
                </a>
                <a href="#properties-desk" data-target-section="properties-desk" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🏢</span>
                        <span>Properties (Admin vs Agent)</span>
                    </span>
                </a>
                <a href="#bookings" data-target-section="bookings" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>📑</span>
                        <span>All Reservations</span>
                    </span>
                </a>
                <a href="#profile" data-target-section="profile" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>👤</span>
                        <span>My Staff Profile</span>
                    </span>
                </a>

                @if ($isAdmin)
                    <div class="pt-4 border-t border-white/10">
                        <a href="{{ route('dashboard') }}" class="flex items-center rounded-xl px-3 py-2 text-sm font-semibold text-[#d4af37] transition hover:bg-white/10">
                            &larr; Admin Dashboard
                        </a>
                        <a href="{{ route('finance.index') }}" class="flex items-center rounded-xl px-3 py-2 text-sm font-semibold text-[#d4af37] transition hover:bg-white/10">
                            Finance &amp; Tax Desk
                        </a>
                    </div>
                @endif
            </nav>

            {{-- Desk Color Palette Switcher in Bottom of Navigation Bar --}}
            <div class="mt-6 pt-4 border-t border-white/10" id="palette-switcher-wrapper">
                <p class="text-[10px] font-bold uppercase tracking-wider text-[#d4af37] mb-2 flex items-center gap-1.5">
                    <span>🎨</span>
                    <span>Desk Color</span>
                </p>
                <div class="relative">
                    <button type="button" id="palette-switcher-btn" onclick="togglePaletteMenu()" class="w-full flex items-center justify-between rounded-xl border border-white/15 bg-white/10 px-3 py-2 text-xs font-bold text-white transition hover:bg-white/20 cursor-pointer shadow-xs" title="Switch Receptionist Desk Color Palette">
                        <span class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-[#d4af37]"></span>
                            <span id="active-palette-name">Highland Emerald</span>
                        </span>
                        <svg class="h-3.5 w-3.5 text-[#d4af37]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div id="palette-switcher-dropdown" class="hidden absolute left-0 bottom-full z-50 mb-2 w-full overflow-hidden rounded-2xl border border-[#d9cab3] bg-white p-2 shadow-2xl space-y-1">
                        <p class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-slate-400">Desk Color Palette</p>
                        <button type="button" onclick="applyReceptionistPalette('emerald'); togglePaletteMenu();" class="w-full flex items-center justify-between rounded-xl px-2.5 py-2 text-xs font-bold text-[#1d3c34] hover:bg-[#dfeee4] transition text-left cursor-pointer">
                            <span class="flex items-center gap-2">
                                <span class="h-3.5 w-3.5 rounded-full bg-[#1d3c34] border border-white shadow-xs"></span>
                                <span>Highland Emerald</span>
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400">Default (Admin)</span>
                        </button>
                        <button type="button" onclick="applyReceptionistPalette('oak'); togglePaletteMenu();" class="w-full flex items-center justify-between rounded-xl px-2.5 py-2 text-xs font-bold text-[#5c3a21] hover:bg-[#f5ede4] transition text-left cursor-pointer">
                            <span class="flex items-center gap-2">
                                <span class="h-3.5 w-3.5 rounded-full bg-[#5c3a21] border border-white shadow-xs"></span>
                                <span>Oak Wood</span>
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400">Warm Timber</span>
                        </button>
                        <button type="button" onclick="applyReceptionistPalette('cream'); togglePaletteMenu();" class="w-full flex items-center justify-between rounded-xl px-2.5 py-2 text-xs font-bold text-[#7c5e43] hover:bg-[#f9f5ee] transition text-left cursor-pointer">
                            <span class="flex items-center gap-2">
                                <span class="h-3.5 w-3.5 rounded-full bg-[#dfd3c3] border border-[#7c5e43]/30 shadow-xs"></span>
                                <span>Soft Cream</span>
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400">Linen Beige</span>
                        </button>
                        <button type="button" onclick="applyReceptionistPalette('sapphire'); togglePaletteMenu();" class="w-full flex items-center justify-between rounded-xl px-2.5 py-2 text-xs font-bold text-[#1e3a5f] hover:bg-[#dbeafe] transition text-left cursor-pointer">
                            <span class="flex items-center gap-2">
                                <span class="h-3.5 w-3.5 rounded-full bg-[#1e3a5f] border border-white shadow-xs"></span>
                                <span>Sapphire Navy</span>
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400">Executive</span>
                        </button>
                        <button type="button" onclick="applyReceptionistPalette('bordeaux'); togglePaletteMenu();" class="w-full flex items-center justify-between rounded-xl px-2.5 py-2 text-xs font-bold text-[#4c1d24] hover:bg-[#fae8eb] transition text-left cursor-pointer">
                            <span class="flex items-center gap-2">
                                <span class="h-3.5 w-3.5 rounded-full bg-[#4c1d24] border border-white shadow-xs"></span>
                                <span>Bordeaux Velvet</span>
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400">Luxury</span>
                        </button>
                        <button type="button" onclick="applyReceptionistPalette('obsidian'); togglePaletteMenu();" class="w-full flex items-center justify-between rounded-xl px-2.5 py-2 text-xs font-bold text-[#18181b] hover:bg-slate-100 transition text-left cursor-pointer">
                            <span class="flex items-center gap-2">
                                <span class="h-3.5 w-3.5 rounded-full bg-[#18181b] border border-white shadow-xs"></span>
                                <span>Obsidian Slate</span>
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400">Modern</span>
                        </button>
                    </div>
                </div>
            </div>
            </div>

            @include('dashboard.partials.signed-in-user', ['roleLabel' => $roleLabel])
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 px-6 py-12">
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-6 flex items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-900 shadow-xs">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">✅</span>
                        <p class="text-sm font-semibold">{{ session('success') }}</p>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold">&times;</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-900 shadow-xs">
                    <p class="font-bold text-sm">Please resolve the following errors:</p>
                    <ul class="mt-2 list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Page Header & Property Switcher (Admin Dashboard Anatomy) --}}
            <div id="overview-header" class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between scroll-mt-24 border-b border-[#d9cab3] pb-6">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Front Desk Control Panel</p>
                    <h1 class="mt-2 text-4xl font-black text-[#1d3c34]">Receptionist Desk &amp; Guest Management</h1>
                    <p class="mt-1 text-xs text-slate-500 font-medium">
                        🕒 {{ \Carbon\Carbon::now()->format('l, M d, Y · H:i') }} (East Africa Time) &bull; Desk: {{ $propName }}
                    </p>
                </div>

                {{-- Action Bar: Notifications, Property Selector, Profile Ring, Logout --}}
                <div class="flex flex-wrap items-center gap-3">

                    {{-- Property Selector for Admins or Multi-Property Staff --}}
                    @if ($isAdmin || $hotelProperties->count() > 1)
                        <form method="GET" action="{{ route('receptionist.dashboard') }}" class="flex items-center gap-1.5">
                            <select id="property-selector" name="property_id" onchange="this.form.submit()" class="rounded-full border border-[#cdbb97] bg-white px-3.5 py-2 text-xs font-bold text-[#1d3c34] focus:border-[#1d3c34] focus:outline-none shadow-xs">
                                @foreach ($hotelProperties as $p)
                                    <option value="{{ $p->id }}" @selected($currentProperty && $currentProperty->id === $p->id)>
                                        🏢 {{ $p->title }} ({{ $p->city ?? 'Addis Ababa' }})
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @endif

                    {{-- Notifications & Alerts Bell with Dropdown (Admin Dashboard Anatomy) --}}
                    @php
                        $unreadInquiriesCount = ($stats['inquiries_count'] ?? 0);
                        $pendingBookingsCount = isset($pendingBookings) ? $pendingBookings->count() : 0;
                        $pendingRequestsCount = ($stats['pending_requests_count'] ?? 0);
                        $totalReceptionistAlerts = $unreadInquiriesCount + $pendingBookingsCount + $pendingRequestsCount;
                    @endphp
                    <div class="relative" id="dashboard-messages-icon-wrapper">
                        <button type="button" id="dashboard-messages-icon" onclick="toggleAlertsDropdown()" class="relative flex h-9 w-9 items-center justify-center rounded-full border border-[#cdbb97] bg-white text-[#1d3c34] transition hover:bg-[#f1e4cf] cursor-pointer" title="Front Desk Alerts &amp; Activities" aria-label="Notifications" aria-haspopup="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if ($totalReceptionistAlerts > 0)
                                <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-black text-white shadow-sm">{{ $totalReceptionistAlerts }}</span>
                            @endif
                        </button>
                        <div id="dashboard-messages-dropdown" class="hidden absolute right-0 top-full z-50 mt-2 w-80 sm:w-96 overflow-hidden rounded-2xl border border-[#d9cab3] bg-white shadow-xl">
                            <div class="border-b border-[#f0e8d8] bg-[#fbf8f2] px-3.5 py-2.5 flex items-center justify-between">
                                <p class="text-[10px] font-black uppercase tracking-wider text-[#587165]">Front Desk Alerts &amp; Activities</p>
                                @if ($totalReceptionistAlerts > 0)
                                    <span class="rounded-full bg-rose-100 text-rose-700 px-2 py-0.5 text-[10px] font-bold">{{ $totalReceptionistAlerts }} new</span>
                                @endif
                            </div>

                            @if ($pendingBookingsCount > 0)
                                <div class="border-b border-[#f0e8d8] bg-amber-50/60 p-2.5">
                                    <div class="flex items-center justify-between px-1">
                                        <span class="text-[10px] font-black uppercase text-amber-900 tracking-wider">🛎️ Pending Online Bookings</span>
                                        <span class="rounded bg-amber-200 px-1.5 py-0.2 text-[9px] font-black text-amber-900">{{ $pendingBookingsCount }}</span>
                                    </div>
                                    <div class="mt-1 space-y-1">
                                        @foreach ($pendingBookings->take(2) as $pbAlert)
                                            <a href="#stay-listings" onclick="activateSection('stay-listings'); toggleAlertsDropdown();" class="block rounded-xl bg-white p-2 border border-amber-200 text-left hover:bg-amber-50 transition shadow-2xs">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-bold text-xs text-[#102b25]">{{ $pbAlert->guest_name }}</span>
                                                    <span class="text-[10px] font-mono text-amber-700">{{ number_format($pbAlert->total_amount) }} ETB</span>
                                                </div>
                                                <p class="text-[10px] text-slate-500 mt-0.5">{{ $pbAlert->property?->title }} &bull; {{ $pbAlert->total_nights }} nights</p>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if ($pendingRequestsCount > 0)
                                <div class="border-b border-[#f0e8d8] bg-rose-50/50 p-2.5">
                                    <div class="flex items-center justify-between px-1">
                                        <span class="text-[10px] font-black uppercase text-rose-800 tracking-wider">🧹 Open Turnovers / Requests</span>
                                        <span class="rounded bg-rose-200 px-1.5 py-0.2 text-[9px] font-black text-rose-900">{{ $pendingRequestsCount }}</span>
                                    </div>
                                    <p class="px-1 text-[11px] text-slate-600 mt-1">Guests have requested housekeeping or room service.</p>
                                    <button type="button" onclick="activateSection('services'); toggleAlertsDropdown();" class="mt-1 block w-full text-left rounded-xl bg-white p-2 border border-rose-100 text-xs font-bold text-rose-800 hover:bg-rose-50">
                                        View Housekeeping Board &rarr;
                                    </button>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 border-t border-[#f0e8d8] bg-[#fbf8f2] text-center text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                                <button type="button" onclick="activateSection('bookings'); toggleAlertsDropdown();" class="py-2.5 hover:bg-[#f3eadc] border-r border-[#f0e8d8]">All Reservations →</button>
                                <button type="button" onclick="activateSection('inquiries'); toggleAlertsDropdown();" class="py-2.5 hover:bg-[#f3eadc]">Guest Messages →</button>
                            </div>
                        </div>
                    </div>

                    {{-- Staff Profile Circle Avatar Button --}}
                    <a href="#profile" data-target-section="profile" class="nav-section-link group relative inline-flex items-center justify-center rounded-full p-0.5 transition ring-2 ring-[#cdbb97] hover:ring-[#1d3c34] focus:outline-none focus:ring-4 focus:ring-[#1d3c34]/30 shadow-xs cursor-pointer" title="Staff Profile ({{ auth()->user()->name }})">
                        @if (auth()->user()->photo_url)
                            <img src="{{ auth()->user()->photo_url }}" alt="{{ auth()->user()->name }}" class="h-9 w-9 rounded-full object-cover group-hover:brightness-95 transition">
                        @else
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#1d3c34] text-xs font-black text-[#f8f3eb] group-hover:bg-[#254d43] transition">
                                {{ auth()->user()->initials ?: 'FD' }}
                            </span>
                        @endif
                        <span class="sr-only">Staff Profile</span>
                    </a>
                </div>
            </div>

            {{-- Metric Stat Cards (Admin Dashboard Anatomy: rounded-[1.5rem], font-medium, text-3xl font-black) --}}
            <div class="mt-8 grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7">
                <div onclick="activateSection('in-house')" class="group cursor-pointer rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm transition hover:border-[#1d3c34] hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">In-House Guests</p>
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                    <p class="mt-3 text-3xl font-black text-emerald-700">{{ $stats['in_house_count'] }}</p>
                    <p class="mt-1 text-xs text-slate-400 font-semibold group-hover:text-emerald-700 transition">Occupied rooms &rarr;</p>
                </div>

                <div onclick="activateSection('check-ins')" class="group cursor-pointer rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm transition hover:border-[#1d3c34] hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Arrivals</p>
                        <span class="rounded-full bg-[#102b25]/10 px-2 py-0.5 text-[10px] font-black text-[#102b25]">Today</span>
                    </div>
                    <p class="mt-3 text-3xl font-black text-[#1d3c34]">{{ $stats['today_checkins_count'] }}</p>
                    <p class="mt-1 text-xs text-slate-400 font-semibold group-hover:text-[#1d3c34] transition">Awaiting check-in &rarr;</p>
                </div>

                <div onclick="activateSection('check-outs')" class="group cursor-pointer rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm transition hover:border-[#1d3c34] hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Departures</p>
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-black text-amber-800">Today</span>
                    </div>
                    <p class="mt-3 text-3xl font-black text-amber-700">{{ $stats['today_checkouts_count'] }}</p>
                    <p class="mt-1 text-xs text-slate-400 font-semibold group-hover:text-amber-700 transition">Check-out today &rarr;</p>
                </div>

                <div onclick="activateSection('services')" class="group cursor-pointer rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm transition hover:border-[#1d3c34] hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Service Req.</p>
                        @if ($stats['pending_requests_count'] > 0)
                            <span class="rounded-full bg-rose-500 text-white px-2 py-0.5 text-[10px] font-black">{{ $stats['pending_requests_count'] }}</span>
                        @endif
                    </div>
                    <p class="mt-3 text-3xl font-black text-rose-600">{{ $stats['pending_requests_count'] }}</p>
                    <p class="mt-1 text-xs text-slate-400 font-semibold group-hover:text-rose-600 transition">Open requests &rarr;</p>
                </div>

                <div onclick="activateSection('reviews')" class="group cursor-pointer rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm transition hover:border-[#1d3c34] hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Guest Reviews</p>
                        <span class="text-xs">⭐</span>
                    </div>
                    <p class="mt-3 text-3xl font-black text-[#102b25]">{{ $stats['reviews_count'] }}</p>
                    <p class="mt-1 text-xs text-slate-400 font-semibold group-hover:text-[#102b25] transition">Guest ratings &rarr;</p>
                </div>

                <div onclick="activateSection('maintenance')" class="group cursor-pointer rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm transition hover:border-[#1d3c34] hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Repairs/Mnt.</p>
                        <span class="text-xs">🔧</span>
                    </div>
                    <p class="mt-3 text-3xl font-black text-blue-700">{{ $stats['maintenance_tickets_count'] }}</p>
                    <p class="mt-1 text-xs text-slate-400 font-semibold group-hover:text-blue-700 transition">Active tickets &rarr;</p>
                </div>

                <div onclick="activateSection('shifts')" class="group cursor-pointer rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm transition hover:border-[#1d3c34] hover:-translate-y-0.5 col-span-2 sm:col-span-1">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Today's Collections</p>
                        <span class="rounded-full bg-[#102b25] px-1.5 py-0.5 text-[8px] font-black text-[#d4af37]">FINANCE</span>
                    </div>
                    <p class="mt-3 text-2xl font-black text-[#102b25] truncate">{{ number_format($stats['total_collected_today']) }} <span class="text-xs font-normal text-slate-500">ETB</span></p>
                    <p class="mt-1 text-[11px] font-semibold text-slate-500 truncate space-x-1">
                        <span>💵 {{ number_format($stats['cash_collected']) }}</span>
                        <span>📱 {{ number_format($stats['telebirr_collected']) }}</span>
                    </p>
                </div>
            </div>



            {{-- Operational Front Desk Task Bar (In-line Quick Nav) --}}
            <div class="mt-8 rounded-2xl border border-[#d9cab3] bg-white p-2 shadow-xs">
                <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 scrollbar-none">
                    <button type="button" data-pms-tab="overview" onclick="activateSection('overview')" class="pms-ribbon-tab rounded-xl px-3 py-2 text-xs font-black transition flex items-center gap-2 shrink-0 bg-[#102b25] text-[#d4af37] shadow-xs">
                        <span>🛎️</span>
                        <span>Front Desk Hub</span>
                    </button>
                    <button type="button" data-pms-tab="stay-listings" onclick="activateSection('stay-listings')" class="pms-ribbon-tab rounded-xl px-3 py-2 text-xs font-bold transition flex items-center gap-2 shrink-0 bg-transparent text-slate-700 hover:bg-[#fcf9f5] hover:text-[#102b25]">
                        <span>🏡</span>
                        <span>Host Stays</span>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-700">{{ $stats['stay_properties_count'] ?? 0 }}</span>
                    </button>
                    <button type="button" data-pms-tab="check-ins" onclick="activateSection('check-ins')" class="pms-ribbon-tab rounded-xl px-3 py-2 text-xs font-bold transition flex items-center gap-2 shrink-0 bg-transparent text-slate-700 hover:bg-[#fcf9f5] hover:text-[#102b25]">
                        <span>🧳</span>
                        <span>Arrivals</span>
                        @if ($stats['today_checkins_count'] > 0)
                            <span class="rounded-full bg-[#d4af37] px-2 py-0.5 text-[10px] font-black text-[#102b25]">{{ $stats['today_checkins_count'] }}</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-700">0</span>
                        @endif
                    </button>
                    <button type="button" data-pms-tab="in-house" onclick="activateSection('in-house')" class="pms-ribbon-tab rounded-xl px-3 py-2 text-xs font-bold transition flex items-center gap-2 shrink-0 bg-transparent text-slate-700 hover:bg-[#fcf9f5] hover:text-[#102b25]">
                        <span>🚪</span>
                        <span>In-House</span>
                        @if ($stats['in_house_count'] > 0)
                            <span class="rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-black text-white">{{ $stats['in_house_count'] }}</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-700">0</span>
                        @endif
                    </button>
                    <button type="button" data-pms-tab="check-outs" onclick="activateSection('check-outs')" class="pms-ribbon-tab rounded-xl px-3 py-2 text-xs font-bold transition flex items-center gap-2 shrink-0 bg-transparent text-slate-700 hover:bg-[#fcf9f5] hover:text-[#102b25]">
                        <span>👋</span>
                        <span>Departures</span>
                        @if ($stats['today_checkouts_count'] > 0)
                            <span class="rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-black text-slate-900">{{ $stats['today_checkouts_count'] }}</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-700">0</span>
                        @endif
                    </button>
                    <button type="button" data-pms-tab="walk-in" onclick="activateSection('walk-in')" class="pms-ribbon-tab rounded-xl px-3 py-2 text-xs font-bold transition flex items-center gap-2 shrink-0 bg-transparent text-slate-700 hover:bg-[#fcf9f5] hover:text-[#102b25]">
                        <span>➕</span>
                        <span>Walk-In Registration</span>
                    </button>
                    <button type="button" data-pms-tab="services" onclick="activateSection('services')" class="pms-ribbon-tab rounded-xl px-3 py-2 text-xs font-bold transition flex items-center gap-2 shrink-0 bg-transparent text-slate-700 hover:bg-[#fcf9f5] hover:text-[#102b25]">
                        <span>🧹</span>
                        <span>Housekeeping</span>
                        @if ($stats['pending_requests_count'] > 0)
                            <span class="rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white">{{ $stats['pending_requests_count'] }}</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-700">0</span>
                        @endif
                    </button>
                    <button type="button" data-pms-tab="inquiries" onclick="activateSection('inquiries')" class="pms-ribbon-tab rounded-xl px-3 py-2 text-xs font-bold transition flex items-center gap-2 shrink-0 bg-transparent text-slate-700 hover:bg-[#fcf9f5] hover:text-[#102b25]">
                        <span>💬</span>
                        <span>Guest Inquiries</span>
                        @if (($stats['inquiries_count'] ?? 0) > 0)
                            <span class="rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-black text-white">{{ $stats['inquiries_count'] }}</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-700">0</span>
                        @endif
                    </button>
                    <button type="button" data-pms-tab="reviews" onclick="activateSection('reviews')" class="pms-ribbon-tab rounded-xl px-3 py-2 text-xs font-bold transition flex items-center gap-2 shrink-0 bg-transparent text-slate-700 hover:bg-[#fcf9f5] hover:text-[#102b25]">
                        <span>⭐</span>
                        <span>Reviews Desk</span>
                        @if (($stats['reviews_count'] ?? 0) > 0)
                            <span class="rounded-full bg-[#102b25] px-2 py-0.5 text-[10px] font-black text-[#d4af37]">{{ $stats['reviews_count'] }}</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-700">0</span>
                        @endif
                    </button>
                    <button type="button" data-pms-tab="shifts" onclick="activateSection('shifts')" class="pms-ribbon-tab rounded-xl px-3 py-2 text-xs font-bold transition flex items-center gap-2 shrink-0 bg-transparent text-slate-700 hover:bg-[#fcf9f5] hover:text-[#102b25]">
                        <span>💵</span>
                        <span>Shift Cash</span>
                    </button>
                </div>
            </div>

            {{-- 1. SECTION: FRONT DESK OVERVIEW / HUB --}}
            <div data-section="overview" class="section-panel mt-8 space-y-8">
                @if (isset($actionableTasks) && count($actionableTasks) > 0)
                    <div class="rounded-2xl border border-[#d4af37]/60 bg-gradient-to-r from-[#1d3c34] to-[#254d43] p-5 text-white shadow-md">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="rounded bg-[#d4af37] px-2 py-0.5 text-[10px] font-black uppercase text-[#1d3c34]">🤖 Front Desk AI Assistant</span>
                            <span class="text-xs font-bold text-[#dfeee4]">Action Required</span>
                        </div>
                        <h3 class="text-base font-black text-white mb-3">You have {{ count($actionableTasks) }} operational front desk tasks requiring attention:</h3>
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

                {{-- Host Capabilities Control Hub --}}
                <div class="rounded-3xl border border-[#d9cab3] bg-gradient-to-br from-[#102b25] to-[#1d3c34] p-6 text-white shadow-md">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-white/10 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-[#d4af37] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#102b25]">
                                    Host &amp; Short-Stay Desk
                                </span>
                                <span class="text-xs text-[#dfeee4]/80">9 Core Hosting Actions</span>
                            </div>
                            <h2 class="text-xl font-black text-white mt-1">Host Operations &amp; Short-Stay Hub</h2>
                            <p class="text-xs text-[#dfeee4]/80">Create short-stay listings, define prices and availability, enforce house rules, accept reservations, dispatch cleaning, and respond to guest reviews.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" onclick="activateSection('stay-listings'); toggleStayCreateForm();" class="rounded-xl bg-[#d4af37] px-4 py-2 text-xs font-black text-[#102b25] hover:bg-amber-400 transition shadow-xs flex items-center gap-1.5">
                                <span>➕</span>
                                <span>Create Stay Listing</span>
                            </button>
                            <button type="button" onclick="activateSection('stay-listings')" class="rounded-xl bg-white/10 border border-white/20 px-3.5 py-2 text-xs font-bold text-white hover:bg-white/20 transition">
                                <span>🏡</span>
                                <span>Manage All Stays ({{ $stayProperties->count() }})</span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                        <button type="button" onclick="activateSection('stay-listings')" class="rounded-2xl border border-white/10 bg-white/5 p-3 text-left transition hover:bg-white/10 hover:border-[#d4af37]/50">
                            <span class="text-xl">🏡</span>
                            <p class="text-xs font-black text-white mt-1.5">Stay Listings</p>
                            <p class="text-[10px] text-[#dfeee4]/70">{{ $stayProperties->count() }} Properties</p>
                        </button>
                        <button type="button" onclick="activateSection('stay-listings')" class="rounded-2xl border border-white/10 bg-white/5 p-3 text-left transition hover:bg-white/10 hover:border-[#d4af37]/50">
                            <span class="text-xl">📅</span>
                            <p class="text-xs font-black text-white mt-1.5">Availability</p>
                            <p class="text-[10px] text-[#dfeee4]/70">Calendar &amp; Dates</p>
                        </button>
                        <button type="button" onclick="activateSection('stay-listings')" class="rounded-2xl border border-white/10 bg-white/5 p-3 text-left transition hover:bg-white/10 hover:border-[#d4af37]/50">
                            <span class="text-xl">💵</span>
                            <p class="text-xs font-black text-white mt-1.5">Prices &amp; Fees</p>
                            <p class="text-[10px] text-[#dfeee4]/70">Nightly &amp; Cleaning</p>
                        </button>
                        <button type="button" onclick="activateSection('stay-listings')" class="rounded-2xl border border-white/10 bg-white/5 p-3 text-left transition hover:bg-white/10 hover:border-[#d4af37]/50">
                            <span class="text-xl">📋</span>
                            <p class="text-xs font-black text-white mt-1.5">House Rules</p>
                            <p class="text-[10px] text-[#dfeee4]/70">Check-in Policies</p>
                        </button>
                        <button type="button" onclick="activateSection('services')" class="rounded-2xl border border-white/10 bg-white/5 p-3 text-left transition hover:bg-white/10 hover:border-[#d4af37]/50">
                            <span class="text-xl">🧹</span>
                            <p class="text-xs font-black text-white mt-1.5">Manage Cleaning</p>
                            <p class="text-[10px] text-[#dfeee4]/70">Turnovers &amp; Housekeeping</p>
                        </button>
                        <button type="button" onclick="activateSection('reviews')" class="rounded-2xl border border-white/10 bg-white/5 p-3 text-left transition hover:bg-white/10 hover:border-[#d4af37]/50">
                            <span class="text-xl">⭐</span>
                            <p class="text-xs font-black text-white mt-1.5">Receive Reviews</p>
                            <p class="text-[10px] text-[#dfeee4]/70">{{ $reviews->count() }} Guest Ratings</p>
                        </button>
                    </div>
                </div>

                {{-- Pending Bookings Awaiting Host Acceptance Card --}}
                @if (isset($pendingBookings) && $pendingBookings->isNotEmpty())
                    <div class="rounded-3xl border border-amber-300 bg-amber-50/70 p-6 shadow-xs">
                        <div class="flex items-center justify-between border-b border-amber-200 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">🛎️</span>
                                <div>
                                    <h2 class="text-xl font-black text-[#102b25]">Pending Bookings (Awaiting Host Acceptance)</h2>
                                    <p class="text-xs text-amber-900">Online reservations submitted by guests requiring host acceptance and confirmation.</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-amber-500 text-slate-900 px-3 py-1 text-xs font-black">
                                {{ $pendingBookings->count() }} Pending
                            </span>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-amber-200/50 text-[#102b25] font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="p-3">Reference</th>
                                        <th class="p-3">Guest Details</th>
                                        <th class="p-3">Accommodation</th>
                                        <th class="p-3">Dates &amp; Nights</th>
                                        <th class="p-3">Total Amount</th>
                                        <th class="p-3 text-right">Host Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-amber-200/40 bg-white">
                                    @foreach ($pendingBookings as $pb)
                                        <tr class="hover:bg-amber-50/50">
                                            <td class="p-3 font-mono font-bold text-[#102b25]">{{ $pb->booking_reference }}</td>
                                            <td class="p-3">
                                                <p class="font-bold text-slate-900">{{ $pb->guest_name ?? $pb->user?->name ?? 'Guest' }}</p>
                                                <p class="text-[11px] text-slate-500">📞 {{ $pb->guest_phone ?? 'No phone' }}</p>
                                            </td>
                                            <td class="p-3">
                                                <p class="font-semibold text-[#102b25]">{{ $pb->property?->title ?? 'Short-Stay Suite' }}</p>
                                                <p class="text-[10px] text-slate-400">Room: {{ $pb->room_number ?: 'TBD' }}</p>
                                            </td>
                                            <td class="p-3">
                                                <p class="font-semibold">{{ \Carbon\Carbon::parse($pb->check_in_date)->format('M d') }} &rarr; {{ \Carbon\Carbon::parse($pb->check_out_date)->format('M d, Y') }}</p>
                                                <p class="text-[10px] text-slate-400">{{ $pb->total_nights }} nights &bull; {{ $pb->guests_count }} guests</p>
                                            </td>
                                            <td class="p-3 font-bold text-slate-900">{{ number_format($pb->total_amount, 2) }} ETB</td>
                                            <td class="p-3 text-right">
                                                <form method="POST" action="{{ route('receptionist.bookings.accept', $pb->id) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition shadow-xs flex items-center gap-1 ml-auto">
                                                        <span>✓</span>
                                                        <span>Accept Booking</span>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Overview 2-Column Split: Today's Expected Arrivals & In-House Guests (Admin Dashboard Anatomy) --}}
                <div class="mt-8 grid gap-6 lg:grid-cols-2">
                    {{-- Column 1: Expected Arrivals --}}
                    <section class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
                                <div>
                                    <h2 class="text-xl font-black text-[#102b25]">Today's Expected Arrivals (Check-Ins)</h2>
                                    <p class="text-xs text-slate-500">Verify guest national ID, register room key number, and collect payment.</p>
                                </div>
                                <span class="rounded-full bg-[#102b25] text-[#d4af37] px-3 py-1 text-xs font-bold shrink-0">
                                    {{ $expectedCheckins->count() }} Guests Expected
                                </span>
                            </div>

                    @if ($expectedCheckins->isEmpty())
                        <div class="p-8 text-center text-slate-500">
                            <span class="text-3xl">🛎️</span>
                            <p class="mt-2 text-sm font-semibold">No pending arrivals scheduled for today.</p>
                            <p class="text-xs text-slate-400 mt-1">Walk-in guests can be registered using "Walk-In Booking" or publish a new short-stay listing.</p>
                            <div class="mt-3 flex items-center justify-center gap-2">
                                <button type="button" onclick="openWalkInModal()" class="rounded-xl bg-[#1d3c34] px-3.5 py-1.5 text-xs font-bold text-[#e4c76f] hover:bg-black transition">
                                    ➕ Register Walk-In Guest
                                </button>
                                <button type="button" onclick="activateSection('stay-listings'); toggleStayCreateForm();" class="rounded-xl border border-[#d9cab3] bg-white px-3.5 py-1.5 text-xs font-bold text-[#102b25] hover:bg-[#fcf9f5] transition">
                                    🏡 Create Short-Stay Listing
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#ebdcc9]/40 text-[#102b25] font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="p-3">Booking Ref</th>
                                        <th class="p-3">Guest Name &amp; Contact</th>
                                        <th class="p-3">Nights / Dates</th>
                                        <th class="p-3">Total Amount</th>
                                        <th class="p-3">Payment Status</th>
                                        <th class="p-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#ebdcc9]/30">
                                    @foreach ($expectedCheckins as $b)
                                        <tr class="hover:bg-[#fcf9f5]">
                                            <td class="p-3 font-mono font-bold text-[#102b25]">{{ $b->booking_reference }}</td>
                                            <td class="p-3">
                                                <p class="font-bold text-slate-900">{{ $b->guest_name ?? $b->user?->name ?? 'Guest' }}</p>
                                                <p class="text-[11px] text-slate-500">📞 {{ $b->guest_phone ?? $b->user?->phone ?? 'No phone' }}</p>
                                            </td>
                                            <td class="p-3">
                                                <p class="font-semibold">{{ \Carbon\Carbon::parse($b->check_in_date)->format('M d') }} &rarr; {{ \Carbon\Carbon::parse($b->check_out_date)->format('M d') }}</p>
                                                <p class="text-[10px] text-slate-400">{{ $b->total_nights }} nights &bull; {{ $b->guests_count }} guests</p>
                                            </td>
                                            <td class="p-3 font-bold text-slate-900">{{ number_format($b->total_amount, 2) }} ETB</td>
                                            <td class="p-3">
                                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $b->payment_status_badge[1] }}">
                                                    {{ $b->payment_status_badge[0] }}
                                                </span>
                                            </td>
                                            <td class="p-3 text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    @if ($b->status === 'pending')
                                                        <form method="POST" action="{{ route('receptionist.bookings.accept', $b->id) }}" class="inline">
                                                            @csrf
                                                            <button type="submit" class="rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-emerald-700 shadow-xs" title="Accept & Confirm Reservation">
                                                                ✓ Accept
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <button type="button" onclick="openCheckInModal({{ json_encode($b) }})" class="rounded-xl bg-[#102b25] px-3.5 py-1.5 text-xs font-bold text-[#d4af37] transition hover:bg-black">
                                                        🔑 Check In Guest
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                        </div>
                        <div class="mt-4 pt-3 border-t border-[#f0e4cf] flex items-center justify-between text-xs">
                            <span class="text-slate-400 text-[11px]">{{ $expectedCheckins->count() }} arriving guest(s) today</span>
                            <button type="button" onclick="activateSection('check-ins')" class="font-bold text-[#102b25] hover:underline">Full Arrivals Board &rarr;</button>
                        </div>
                    </section>

                    {{-- Column 2: Currently In-House (Admin Dashboard Anatomy) --}}
                    <section class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
                                <div>
                                    <h2 class="text-xl font-black text-[#102b25]">Currently In-House (Occupied Rooms)</h2>
                                    <p class="text-xs text-slate-500">Active guests staying on premise. Dispatch room services or execute check-out.</p>
                                </div>
                                <span class="rounded-full bg-emerald-100 text-emerald-800 px-3 py-1 text-xs font-bold shrink-0">
                                    {{ $inHouseGuests->count() }} In-House
                                </span>
                            </div>

                    @if ($inHouseGuests->isEmpty())
                        <div class="p-8 text-center text-slate-500">
                            <span class="text-3xl">🚪</span>
                            <p class="mt-2 text-sm font-semibold">No guests currently checked into rooms.</p>
                        </div>
                    @else
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#ebdcc9]/40 text-[#102b25] font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="p-3">Room #</th>
                                        <th class="p-3">Guest Name &amp; ID</th>
                                        <th class="p-3">Check-In Time</th>
                                        <th class="p-3">Check-Out Due</th>
                                        <th class="p-3">Active Services</th>
                                        <th class="p-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#ebdcc9]/30">
                                    @foreach ($inHouseGuests as $ih)
                                        <tr class="hover:bg-[#fcf9f5]">
                                            <td class="p-3">
                                                <span class="rounded-lg bg-[#102b25] text-white px-2.5 py-1 font-mono font-black text-xs">
                                                    {{ $ih->room_number ?: 'TBD' }}
                                                </span>
                                            </td>
                                            <td class="p-3">
                                                <p class="font-bold text-slate-900">{{ $ih->guest_name ?? $ih->user?->name }}</p>
                                                <p class="text-[10px] text-slate-400">ID: {{ $ih->guest_id_number ?: 'Not recorded' }} &bull; 📞 {{ $ih->guest_phone ?? $ih->user?->phone }}</p>
                                            </td>
                                            <td class="p-3 text-slate-600">
                                                {{ $ih->checked_in_at ? \Carbon\Carbon::parse($ih->checked_in_at)->format('M d, H:i') : 'Checked In' }}
                                            </td>
                                            <td class="p-3">
                                                <span class="font-semibold text-slate-900">{{ \Carbon\Carbon::parse($ih->check_out_date)->format('M d, Y') }}</span>
                                                @if (\Carbon\Carbon::parse($ih->check_out_date)->isToday())
                                                    <span class="ml-1 rounded-full bg-amber-100 text-amber-800 text-[9px] font-black px-1.5 py-0.5">Today</span>
                                                @endif
                                            </td>
                                            <td class="p-3">
                                                @php $activeReqs = $ih->guestServiceRequests->whereIn('status', ['pending', 'in_progress']); @endphp
                                                @if ($activeReqs->isNotEmpty())
                                                    <span class="rounded-full bg-rose-100 text-rose-800 font-bold px-2 py-0.5 text-[10px]">
                                                        {{ $activeReqs->count() }} active request(s)
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 text-[11px]">— None —</span>
                                                @endif
                                            </td>
                                            <td class="p-3 text-right space-x-1">
                                                <button type="button" onclick="openServiceModal({{ $ih->id }}, '{{ $ih->room_number }}', '{{ addslashes($ih->guest_name ?? $ih->user?->name) }}')" class="rounded-lg border border-[#b9a98b] bg-white px-2.5 py-1 text-xs font-semibold text-[#1d3c34] hover:bg-[#f0e4cf]" title="New Service Request">
                                                    🛎️ Service
                                                </button>
                                                <a href="{{ route('receptionist.bookings.folio', $ih->id) }}" target="_blank" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100" title="Print Guest Folio">
                                                    📄 Folio
                                                </a>
                                                <button type="button" onclick="openCheckOutModal({{ json_encode($ih) }})" class="rounded-lg bg-amber-600 hover:bg-amber-700 text-white px-2.5 py-1 text-xs font-bold">
                                                    Check Out
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                        </div>
                        <div class="mt-4 pt-3 border-t border-[#f0e4cf] flex items-center justify-between text-xs">
                            <span class="text-slate-400 text-[11px]">{{ $inHouseGuests->count() }} occupied room(s)</span>
                            <button type="button" onclick="activateSection('in-house')" class="font-bold text-[#102b25] hover:underline">Full In-House Board &rarr;</button>
                        </div>
                    </section>
                </div>
            </div>

            {{-- 2. SECTION: ARRIVALS & CHECK-IN ONLY --}}
            <div data-section="check-ins" class="section-panel hidden mt-8 space-y-6">
                <div class="flex items-center justify-between border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">Arrivals &amp; Guest Check-In Desk</h2>
                        <p class="text-xs text-slate-600">Register arriving guests, verify IDs, assign keys, and settle booking balances.</p>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs">
                    @if ($expectedCheckins->isEmpty())
                        <div class="p-12 text-center text-slate-500">
                            <span class="text-4xl">🧳</span>
                            <p class="mt-3 text-base font-bold text-[#102b25]">No Pending Arrivals Scheduled</p>
                            <p class="text-xs text-slate-500 mt-1">All scheduled guests for today have either checked in or no bookings match today's date.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#ebdcc9]/40 text-[#102b25] font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="p-3">Reference</th>
                                        <th class="p-3">Guest Details</th>
                                        <th class="p-3">Stay Dates</th>
                                        <th class="p-3">Rate &amp; Total</th>
                                        <th class="p-3">Payment</th>
                                        <th class="p-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#ebdcc9]/30">
                                    @foreach ($expectedCheckins as $b)
                                        <tr class="hover:bg-[#fcf9f5]">
                                            <td class="p-3 font-mono font-bold text-[#102b25]">{{ $b->booking_reference }}</td>
                                            <td class="p-3">
                                                <p class="font-bold text-slate-900">{{ $b->guest_name ?? $b->user?->name ?? 'Guest' }}</p>
                                                <p class="text-[11px] text-slate-500">📞 {{ $b->guest_phone ?? $b->user?->phone }}</p>
                                                <p class="text-[10px] text-slate-400">✉️ {{ $b->guest_email ?? $b->user?->email }}</p>
                                            </td>
                                            <td class="p-3">
                                                <p class="font-semibold">{{ \Carbon\Carbon::parse($b->check_in_date)->format('M d, Y') }} &rarr; {{ \Carbon\Carbon::parse($b->check_out_date)->format('M d, Y') }}</p>
                                                <p class="text-[10px] text-slate-400">{{ $b->total_nights }} nights &bull; {{ $b->guests_count }} guests</p>
                                            </td>
                                            <td class="p-3 font-bold text-slate-900">
                                                {{ number_format($b->total_amount, 2) }} ETB
                                                <p class="text-[10px] text-slate-400 font-normal">({{ number_format($b->nightly_price, 2) }} / night)</p>
                                            </td>
                                            <td class="p-3">
                                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $b->payment_status_badge[1] }}">
                                                    {{ $b->payment_status_badge[0] }}
                                                </span>
                                            </td>
                                            <td class="p-3 text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    @if ($b->status === 'pending')
                                                        <form method="POST" action="{{ route('receptionist.bookings.accept', $b->id) }}" class="inline">
                                                            @csrf
                                                            <button type="submit" class="rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-emerald-700 shadow-xs" title="Accept & Confirm Reservation">
                                                                ✓ Accept
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <button type="button" onclick="openCheckInModal({{ json_encode($b) }})" class="rounded-xl bg-[#102b25] px-4 py-2 text-xs font-bold text-[#d4af37] transition hover:bg-black">
                                                        🔑 Check In
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 3. SECTION: IN-HOUSE GUESTS ONLY --}}
            <div data-section="in-house" class="section-panel hidden mt-8 space-y-6">
                <div class="flex items-center justify-between border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">In-House Guests &amp; Room Registry</h2>
                        <p class="text-xs text-slate-600">Overview of all active guests currently occupying rooms at this property.</p>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs">
                    @if ($inHouseGuests->isEmpty())
                        <div class="p-12 text-center text-slate-500">
                            <span class="text-4xl">🚪</span>
                            <p class="mt-3 text-base font-bold text-[#102b25]">No Active In-House Guests</p>
                            <p class="text-xs text-slate-500 mt-1">Rooms are currently available for walk-in arrivals.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#ebdcc9]/40 text-[#102b25] font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="p-3">Room #</th>
                                        <th class="p-3">Guest Details</th>
                                        <th class="p-3">ID / Passport #</th>
                                        <th class="p-3">Check-In Timestamp</th>
                                        <th class="p-3">Check-Out Due</th>
                                        <th class="p-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#ebdcc9]/30">
                                    @foreach ($inHouseGuests as $ih)
                                        <tr class="hover:bg-[#fcf9f5]">
                                            <td class="p-3 font-mono font-bold text-sm text-[#102b25]">
                                                {{ $ih->room_number }}
                                            </td>
                                            <td class="p-3">
                                                <p class="font-bold text-slate-900">{{ $ih->guest_name ?? $ih->user?->name }}</p>
                                                <p class="text-[11px] text-slate-500">📞 {{ $ih->guest_phone ?? $ih->user?->phone }}</p>
                                            </td>
                                            <td class="p-3 font-mono text-slate-700">
                                                {{ $ih->guest_id_number ?: 'Not logged' }}
                                            </td>
                                            <td class="p-3 text-slate-600">
                                                {{ $ih->checked_in_at ? \Carbon\Carbon::parse($ih->checked_in_at)->format('M d, Y H:i') : '-' }}
                                            </td>
                                            <td class="p-3">
                                                <span class="font-semibold">{{ \Carbon\Carbon::parse($ih->check_out_date)->format('M d, Y') }}</span>
                                            </td>
                                            <td class="p-3 text-right space-x-1">
                                                <button type="button" onclick="openServiceModal({{ $ih->id }}, '{{ $ih->room_number }}', '{{ addslashes($ih->guest_name ?? $ih->user?->name) }}')" class="rounded-lg border border-[#b9a98b] bg-white px-2.5 py-1 text-xs font-semibold text-[#1d3c34] hover:bg-[#f0e4cf]">
                                                    🛎️ Service
                                                </button>
                                                <a href="{{ route('receptionist.bookings.folio', $ih->id) }}" target="_blank" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                                    📄 Folio
                                                </a>
                                                <button type="button" onclick="openCheckOutModal({{ json_encode($ih) }})" class="rounded-lg bg-amber-600 hover:bg-amber-700 text-white px-2.5 py-1 text-xs font-bold">
                                                    Check Out
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 4. SECTION: DEPARTURES & CHECK-OUT ONLY --}}
            <div data-section="check-outs" class="section-panel hidden mt-8 space-y-6">
                <div class="flex items-center justify-between border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">Departures &amp; Check-Out Desk</h2>
                        <p class="text-xs text-slate-600">Verify room key return, outstanding bills, print final guest folios, and release rooms.</p>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs">
                    @if ($expectedCheckouts->isEmpty())
                        <div class="p-12 text-center text-slate-500">
                            <span class="text-4xl">👋</span>
                            <p class="mt-3 text-base font-bold text-[#102b25]">No Pending Check-Outs Due Today</p>
                            <p class="text-xs text-slate-500 mt-1">No in-house guests are scheduled to depart on today's date.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#ebdcc9]/40 text-[#102b25] font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="p-3">Room #</th>
                                        <th class="p-3">Guest Details</th>
                                        <th class="p-3">Check-In Date</th>
                                        <th class="p-3">Departure Date</th>
                                        <th class="p-3">Total Amount</th>
                                        <th class="p-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#ebdcc9]/30">
                                    @foreach ($expectedCheckouts as $co)
                                        <tr class="hover:bg-[#fcf9f5]">
                                            <td class="p-3 font-mono font-bold text-sm text-[#102b25]">
                                                {{ $co->room_number }}
                                            </td>
                                            <td class="p-3">
                                                <p class="font-bold text-slate-900">{{ $co->guest_name ?? $co->user?->name }}</p>
                                                <p class="text-[11px] text-slate-500">📞 {{ $co->guest_phone ?? $co->user?->phone }}</p>
                                            </td>
                                            <td class="p-3 text-slate-600">
                                                {{ \Carbon\Carbon::parse($co->check_in_date)->format('M d, Y') }}
                                            </td>
                                            <td class="p-3 font-semibold text-rose-700">
                                                {{ \Carbon\Carbon::parse($co->check_out_date)->format('M d, Y') }} (Today)
                                            </td>
                                            <td class="p-3 font-bold text-slate-900">
                                                {{ number_format($co->total_amount, 2) }} ETB
                                            </td>
                                            <td class="p-3 text-right space-x-1">
                                                <a href="{{ route('receptionist.bookings.folio', $co->id) }}" target="_blank" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                                    📄 Folio
                                                </a>
                                                <button type="button" onclick="openCheckOutModal({{ json_encode($co) }})" class="rounded-lg bg-amber-600 hover:bg-amber-700 text-white px-3 py-1 text-xs font-bold">
                                                    Complete Check-Out
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 5. SECTION: WALK-IN QUICK BOOKING --}}
            <div data-section="walk-in" class="section-panel hidden mt-8 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37] border border-[#d4af37]/40">
                                Desk Check-In
                            </span>
                            <span class="text-xs text-slate-500 font-semibold">Immediate Room Handover</span>
                        </div>
                        <h2 class="text-2xl font-black text-[#102b25] mt-1">Walk-In Guest Registration &amp; Folio Settlement</h2>
                        <p class="text-xs text-slate-600">Register arriving counter guests, verify Ethiopian national ID / passport, compute night totals, and issue room key instantly.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="activateSection('overview')" class="rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-bold text-[#102b25] hover:bg-[#fcf9f5] transition">
                            &larr; Back to Front Desk Hub
                        </button>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 sm:p-8 shadow-sm max-w-4xl">
                    <form id="walk-in-form" method="POST" action="{{ route('receptionist.walk-in') }}" class="space-y-6">
                        @csrf

                        {{-- Step 1: Accommodation & Room Assignment --}}
                        <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-5 space-y-4">
                            <div class="flex items-center gap-2 border-b border-[#ebdcc9] pb-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#102b25] text-xs font-black text-[#d4af37]">1</span>
                                <h3 class="text-sm font-black text-[#102b25] uppercase tracking-wider">Accommodation &amp; Room Assignment</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Property / Hotel Branch *</label>
                                    <select name="property_id" id="walkin_property_id" required class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-bold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                        @foreach ($hotelProperties as $p)
                                            <option value="{{ $p->id }}" data-price="{{ $p->price }}" @selected($currentProperty && $currentProperty->id === $p->id)>
                                                {{ $p->title }} ({{ $p->city ?? 'Addis Ababa' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-[11px] text-slate-500">Selected branch for guest folio billing.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Assigned Room # *</label>
                                    <div class="relative mt-1.5">
                                        <input type="text" name="room_number" id="walkin_room_number" required placeholder="e.g. Suite 204 or Room 102" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-bold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                        <span class="absolute right-3 top-2.5 text-sm">🔑</span>
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-500">Physical room or suite number being assigned.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Guest Profile & Ethiopian ID Verification --}}
                        <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-5 space-y-4">
                            <div class="flex items-center gap-2 border-b border-[#ebdcc9] pb-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#102b25] text-xs font-black text-[#d4af37]">2</span>
                                <h3 class="text-sm font-black text-[#102b25] uppercase tracking-wider">Guest Profile &amp; ID Verification</h3>
                                <span class="ml-auto rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase px-2 py-0.5">Required for Registration</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Guest Full Name *</label>
                                    <input type="text" name="guest_name" required placeholder="e.g. Marta Tekle" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Ethiopian Phone Number *</label>
                                    <input type="text" name="guest_phone" required placeholder="09... or +251 9..." class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                    <p class="mt-1 text-[11px] text-slate-500">Mobile for SMS notifications and emergency concierge.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">National ID / Kebele / Fayda / Passport # *</label>
                                    <div class="relative mt-1.5">
                                        <input type="text" name="guest_id_number" required placeholder="e.g. ET-KEBELE-12345 or EP-987654" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                        <span class="absolute right-3 top-2.5 text-sm">🪪</span>
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-500">Must inspect government issued ID before room key issuance.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Guest Email (Optional)</label>
                                    <input type="email" name="guest_email" placeholder="guest@example.com" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                    <p class="mt-1 text-[11px] text-slate-500">Digital receipt will be delivered if provided.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: Stay Schedule & Live Folio Calculator --}}
                        <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-5 space-y-4">
                            <div class="flex items-center gap-2 border-b border-[#ebdcc9] pb-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#102b25] text-xs font-black text-[#d4af37]">3</span>
                                <h3 class="text-sm font-black text-[#102b25] uppercase tracking-wider">Stay Schedule &amp; Folio Calculation</h3>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Check-In Date *</label>
                                    <input type="date" name="check_in_date" id="walkin_checkin" value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" required onchange="calculateWalkInTotal()" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Check-Out Date *</label>
                                    <input type="date" name="check_out_date" id="walkin_checkout" value="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}" required onchange="calculateWalkInTotal()" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Guests Count *</label>
                                    <input type="number" name="guests_count" value="1" min="1" max="10" required class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Nightly Rate (ETB) *</label>
                                    <input type="number" step="0.01" name="nightly_price" id="walkin_nightly" value="{{ $currentProperty ? $currentProperty->price : 2500 }}" required oninput="calculateWalkInTotal()" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-black text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                </div>
                            </div>

                            {{-- Live Folio Calculation Summary Banner --}}
                            <div class="rounded-xl bg-white border border-[#d9cab3] p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">🧾</span>
                                    <div>
                                        <p class="text-xs font-black text-[#102b25]">Calculated Folio Summary</p>
                                        <p class="text-[11px] text-slate-500" id="walkin_summary_text">1 night &bull; Rate: {{ number_format($currentProperty ? $currentProperty->price : 2500, 2) }} ETB / night</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Total Due at Desk</span>
                                    <p class="text-xl font-black text-[#102b25]" id="walkin_total_display">{{ number_format($currentProperty ? $currentProperty->price : 2500, 2) }} ETB</p>
                                </div>
                            </div>
                        </div>

                        {{-- Step 4: Payment Settlement & Key Handover --}}
                        <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-5 space-y-4">
                            <div class="flex items-center gap-2 border-b border-[#ebdcc9] pb-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#102b25] text-xs font-black text-[#d4af37]">4</span>
                                <h3 class="text-sm font-black text-[#102b25] uppercase tracking-wider">Payment Settlement &amp; Key Handover</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Settlement Method *</label>
                                    <select name="payment_method" required class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-bold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                        <option value="cash">💵 Cash (Front Desk Cash Drawer)</option>
                                        <option value="telebirr">📱 Telebirr (Merchant / SuperApp Transfer)</option>
                                        <option value="cbe">🏦 CBE Birr / Commercial Bank of Ethiopia</option>
                                        <option value="card_pos">💳 POS Card Terminal</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Payment Reference / Txn # (Optional)</label>
                                    <input type="text" name="payment_reference" placeholder="e.g. Telebirr Txn # / Bank Slip #" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                </div>
                            </div>

                            <div class="rounded-xl bg-white border border-[#d9cab3] p-3 flex items-center gap-3">
                                <input type="checkbox" name="check_in_now" value="1" id="check_in_now_cb" checked class="h-4 w-4 rounded border-slate-300 text-[#102b25] focus:ring-[#102b25]">
                                <div>
                                    <label for="check_in_now_cb" class="text-xs font-bold text-[#102b25] cursor-pointer">
                                        Immediately Check-In Guest &amp; Issue Room Key
                                    </label>
                                    <p class="text-[11px] text-slate-500">Marks room occupied in PMS, updates housekeeping status, and logs receipt into shift drawer.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-[#f0e4cf]">
                            <button type="button" onclick="activateSection('overview')" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-5 py-3 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                Cancel
                            </button>
                            <button type="submit" class="w-full sm:w-auto rounded-xl bg-[#102b25] px-8 py-3.5 text-xs font-black uppercase tracking-wider text-[#d4af37] hover:bg-black transition shadow-md flex items-center justify-center gap-2">
                                <span>🔑</span>
                                <span>Register, Collect Payment &amp; Issue Room Key</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 5B. SECTION: HOST SHORT-STAY LISTINGS & PRICING DESK --}}
            <div data-section="stay-listings" class="section-panel hidden mt-8 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37] border border-[#d4af37]/40">
                                Host / Stay Listings Desk
                            </span>
                            <span class="text-xs text-slate-500 font-semibold">Short-Stay Portfolios</span>
                        </div>
                        <h2 class="text-2xl font-black text-[#102b25] mt-1">Host Short-Stay Listings, Availability &amp; Rules</h2>
                        <p class="text-xs text-slate-600">Create short-stay accommodations, define prices, calendar availability, and customize house rules.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="toggleStayCreateForm()" class="rounded-xl bg-[#102b25] px-4 py-2 text-xs font-bold text-[#d4af37] hover:bg-black transition shadow-xs flex items-center gap-1.5">
                            <span>➕</span>
                            <span>Create Short-Stay Listing</span>
                        </button>
                    </div>
                </div>

                {{-- Create Short-Stay Listing Drawer / Card (Collapsible) --}}
                <div id="stay-create-card" class="hidden rounded-3xl border border-[#d9cab3] bg-white p-6 sm:p-8 shadow-md">
                    <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#102b25] text-xl text-[#d4af37]">🏡</span>
                            <div>
                                <h3 class="text-lg font-black text-[#102b25]">Create New Short-Stay Listing</h3>
                                <p class="text-xs text-slate-500">Publish a new guesthouse, serviced apartment, or hotel suite with custom pricing and house rules.</p>
                            </div>
                        </div>
                        <button type="button" onclick="toggleStayCreateForm()" class="rounded-full h-8 w-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 text-xl font-bold transition">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('receptionist.stays.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                        @csrf

                        {{-- Section 1: Listing Identity & Location --}}
                        <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-5 space-y-4">
                            <div class="flex items-center gap-2 border-b border-[#ebdcc9] pb-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#102b25] text-xs font-black text-[#d4af37]">1</span>
                                <h4 class="text-sm font-black text-[#102b25] uppercase tracking-wider">Listing Identity &amp; Location</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div class="lg:col-span-2">
                                    <label class="block text-xs font-bold uppercase text-slate-600">Listing Title *</label>
                                    <input type="text" name="title" required placeholder="e.g. Bole Medhanealem Luxury 2BR Penthouse Suite" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Property Category *</label>
                                    <select name="property_category" required class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                        <option value="serviced_apartment">🏢 Serviced Apartment</option>
                                        <option value="guest_house">🏡 Guest House</option>
                                        <option value="hotel">🏨 Boutique Hotel / Suite</option>
                                        <option value="apartment">🛋️ Furnished Apartment</option>
                                        <option value="villa">🏰 Private Villa</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">City / Sub-city *</label>
                                    <input type="text" name="city" required value="Addis Ababa" placeholder="e.g. Addis Ababa" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                </div>
                                <div class="lg:col-span-2">
                                    <label class="block text-xs font-bold uppercase text-slate-600">Physical Address / Specific Area *</label>
                                    <input type="text" name="address" required placeholder="e.g. Bole Medhanealem, Cameroon Street, Near Rwanda Embassy" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Nightly Rates & Cleaning Economics --}}
                        <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-5 space-y-4">
                            <div class="flex items-center gap-2 border-b border-[#ebdcc9] pb-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#102b25] text-xs font-black text-[#d4af37]">2</span>
                                <h4 class="text-sm font-black text-[#102b25] uppercase tracking-wider">Nightly Rates &amp; Cleaning Economics</h4>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Nightly Price (ETB) *</label>
                                    <div class="relative mt-1.5">
                                        <input type="number" step="0.01" name="price" required placeholder="e.g. 3500.00" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-black text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                        <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400">ETB / night</span>
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-500">Base accommodation tariff charged per night.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Turnover Cleaning Fee (ETB)</label>
                                    <div class="relative mt-1.5">
                                        <input type="number" step="0.01" name="cleaning_fee" value="0.00" placeholder="e.g. 500.00" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-bold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                        <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400">ETB / stay</span>
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-500">Custom cleaning fee per reservation (default 850 ETB if empty).</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Platform Service Fee (ETB)</label>
                                    <div class="relative mt-1.5">
                                        <input type="number" step="0.01" name="service_fee" placeholder="e.g. 250.00 (leave blank for standard 5%)" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-bold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                        <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400">ETB / stay</span>
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-500">Custom flat service fee (or leave blank to use 5% dynamic fee).</p>
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Space, Capacity & Timing --}}
                        <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-5 space-y-4">
                            <div class="flex items-center gap-2 border-b border-[#ebdcc9] pb-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#102b25] text-xs font-black text-[#d4af37]">3</span>
                                <h4 class="text-sm font-black text-[#102b25] uppercase tracking-wider">Space, Capacity &amp; Operating Hours</h4>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="grid grid-cols-3 gap-2 sm:col-span-2">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-slate-600">Bedrooms</label>
                                        <input type="number" name="bedrooms" value="1" min="0" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-2.5 py-2 text-xs font-semibold text-[#102b25]">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-slate-600">Bathrooms</label>
                                        <input type="number" name="bathrooms" value="1" min="0" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-2.5 py-2 text-xs font-semibold text-[#102b25]">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-slate-600">Area (m²)</label>
                                        <input type="number" name="area" value="50" min="1" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-2.5 py-2 text-xs font-semibold text-[#102b25]">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Standard Check-In</label>
                                    <input type="text" name="check_in_time" value="14:00" placeholder="14:00" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Standard Check-Out</label>
                                    <input type="text" name="check_out_time" value="11:00" placeholder="11:00" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Min Stay (Nights)</label>
                                    <input type="number" name="minimum_stay" value="1" min="1" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-semibold text-[#102b25]">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Max Stay (Nights)</label>
                                    <input type="number" name="maximum_stay" value="30" min="1" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-semibold text-[#102b25]">
                                </div>
                            </div>
                        </div>

                        {{-- Section 4: Initial Availability Schedule --}}
                        <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-5 space-y-4">
                            <div class="flex items-center gap-2 border-b border-[#ebdcc9] pb-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#102b25] text-xs font-black text-[#d4af37]">4</span>
                                <h4 class="text-sm font-black text-[#102b25] uppercase tracking-wider">Initial Calendar Availability</h4>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Availability Status *</label>
                                    <select name="is_available" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-bold text-[#102b25]">
                                        <option value="1">🟢 Open for Bookings</option>
                                        <option value="0">🔴 Blocked / Maintenance</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Available From</label>
                                    <input type="date" name="available_from" value="{{ date('Y-m-d') }}" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-[#102b25]">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Available Until (Optional)</label>
                                    <input type="date" name="available_to" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-[#102b25]">
                                </div>
                            </div>
                        </div>

                        {{-- Section 5: House Rules & Listing Description --}}
                        <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-5 space-y-4">
                            <div class="flex items-center gap-2 border-b border-[#ebdcc9] pb-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#102b25] text-xs font-black text-[#d4af37]">5</span>
                                <h4 class="text-sm font-black text-[#102b25] uppercase tracking-wider">House Rules &amp; Guest Description</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">House Rules *</label>
                                    <textarea name="house_rules" rows="4" required placeholder="Specify check-in requirements, smoking policy, quiet hours, visitors..." class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">1. Valid Ethiopian ID / Passport required at check-in.
2. No indoor smoking.
3. Quiet hours 22:00 – 07:00.
4. No unauthorized parties or unregistered overnight guests.
5. Turn off lights and appliances when vacating.</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Listing Description *</label>
                                    <textarea name="description" rows="4" required placeholder="Describe amenities, furnished appliances, WiFi speed, location highlights..." class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">Premium furnished short-stay accommodation featuring high-speed WiFi, smart TV, hot water, fully equipped kitchenette, backup generator, and dedicated reception desk security.</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Section 6: Photos & Image Gallery --}}
                        <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-5 space-y-4">
                            <div class="flex items-center justify-between border-b border-[#ebdcc9] pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#102b25] text-xs font-black text-[#d4af37]">6</span>
                                    <h4 class="text-sm font-black text-[#102b25] uppercase tracking-wider">Listing Photos &amp; Image Gallery</h4>
                                </div>
                                <span class="text-[10px] font-semibold text-slate-500">Upload custom photos or choose a luxury preset</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Main Listing Photo (JPG, PNG, WEBP, AVIF)</label>
                                    <input type="file" name="image" id="stay_create_image" accept="image/png,image/jpeg,image/webp,image/avif" class="mt-1.5 w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#102b25] file:text-[#d4af37] hover:file:bg-black rounded-xl border border-[#d9cab3] bg-white p-1.5 focus:outline-none cursor-pointer">
                                    <p class="mt-1 text-[11px] text-slate-500">Primary cover image displayed on front desk cards and guest booking engine.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600">Additional Gallery Photos (Multiple)</label>
                                    <input type="file" name="images[]" multiple accept="image/png,image/jpeg,image/webp,image/avif" class="mt-1.5 w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#102b25] file:text-[#d4af37] hover:file:bg-black rounded-xl border border-[#d9cab3] bg-white p-1.5 focus:outline-none cursor-pointer">
                                    <p class="mt-1 text-[11px] text-slate-500">Upload multiple photos for bedroom, bathroom, amenities &amp; views.</p>
                                </div>
                            </div>
                            <div class="pt-3 border-t border-[#ebdcc9]">
                                <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1.5">Or Select Luxury Backdrop Preset (Instant Cover)</label>
                                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                    <label class="flex flex-col items-center gap-1 p-2 rounded-xl border border-[#ebdcc9] bg-white hover:border-[#102b25] cursor-pointer text-center">
                                        <input type="radio" name="cover_preset" value="hero-skyline" class="text-[#102b25]">
                                        <span class="text-[10px] font-bold text-slate-700">Skyline Suite</span>
                                    </label>
                                    <label class="flex flex-col items-center gap-1 p-2 rounded-xl border border-[#ebdcc9] bg-white hover:border-[#102b25] cursor-pointer text-center">
                                        <input type="radio" name="cover_preset" value="luxury-towers" class="text-[#102b25]">
                                        <span class="text-[10px] font-bold text-slate-700">Luxury Towers</span>
                                    </label>
                                    <label class="flex flex-col items-center gap-1 p-2 rounded-xl border border-[#ebdcc9] bg-white hover:border-[#102b25] cursor-pointer text-center">
                                        <input type="radio" name="cover_preset" value="central-plaza" class="text-[#102b25]">
                                        <span class="text-[10px] font-bold text-slate-700">Hotel Plaza</span>
                                    </label>
                                    <label class="flex flex-col items-center gap-1 p-2 rounded-xl border border-[#ebdcc9] bg-white hover:border-[#102b25] cursor-pointer text-center">
                                        <input type="radio" name="cover_preset" value="panoramic-park" class="text-[#102b25]">
                                        <span class="text-[10px] font-bold text-slate-700">Park Guesthouse</span>
                                    </label>
                                    <label class="flex flex-col items-center gap-1 p-2 rounded-xl border border-[#ebdcc9] bg-white hover:border-[#102b25] cursor-pointer text-center">
                                        <input type="radio" name="cover_preset" value="retail-boulevard" class="text-[#102b25]">
                                        <span class="text-[10px] font-bold text-slate-700">Bole Boulevard</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-[#f0e4cf]">
                            <button type="button" onclick="toggleStayCreateForm()" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                Cancel
                            </button>
                            <button type="submit" class="w-full sm:w-auto rounded-xl bg-[#102b25] px-8 py-3 text-xs font-black uppercase tracking-wider text-[#d4af37] hover:bg-black transition shadow-md flex items-center justify-center gap-2">
                                <span>🚀</span>
                                <span>Publish Short-Stay Listing</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- All Hosted Short-Stay Properties --}}
                @if ($stayProperties->isEmpty())
                    <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center text-slate-500">
                        <span class="text-4xl">🏡</span>
                        <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Short-Stay Listings Active</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                            Front desk receptionists can host furnished rooms, suites, and guesthouses. Click "Create Short-Stay Listing" above to begin.
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($stayProperties as $sp)
                            <div class="rounded-3xl border border-[#d9cab3] bg-white overflow-hidden shadow-xs flex flex-col justify-between hover:shadow-md transition">
                                <div>
                                    <div class="relative h-48 w-full bg-[#102b25]/10">
                                        @php
                                            $listingImageUrl = $sp->primary_image_url ?: ($sp->images->first()?->image_url ?? ($sp->image_path ? $sp->resolveImageUrl($sp->image_path) : null));
                                        @endphp
                                        @if ($listingImageUrl)
                                            <img src="{{ $listingImageUrl }}" alt="{{ $sp->title }}" class="h-full w-full object-cover" onerror="this.onerror=null; this.src='{{ asset($sp->default_background_image) }}';">
                                        @else
                                            <div class="h-full w-full flex items-center justify-center text-4xl">🏡</div>
                                        @endif
                                        @if ($sp->images->count() > 1)
                                            <span class="absolute bottom-3 right-3 rounded-full bg-black/70 backdrop-blur-xs text-white text-[10px] font-bold px-2 py-0.5 shadow-sm flex items-center gap-1">
                                                <span>📷</span>
                                                <span>{{ $sp->images->count() }} photos</span>
                                            </span>
                                        @endif
                                        <div class="absolute top-3 left-3 flex flex-wrap gap-1">
                                            @if ($sp->is_available)
                                                <span class="rounded-full bg-emerald-600 text-white text-[10px] font-black uppercase px-2.5 py-1 shadow-sm">
                                                    ✓ Available
                                                </span>
                                            @else
                                                <span class="rounded-full bg-rose-600 text-white text-[10px] font-black uppercase px-2.5 py-1 shadow-sm">
                                                    ✕ Blocked
                                                </span>
                                            @endif
                                            <span class="rounded-full bg-[#102b25] text-[#d4af37] text-[10px] font-black uppercase px-2.5 py-1 shadow-sm">
                                                {{ ucfirst(str_replace('_', ' ', $sp->property_category ?: 'Stay')) }}
                                            </span>
                                        </div>
                                        <span class="absolute top-3 right-3 rounded-full bg-white/95 backdrop-blur-xs text-[#102b25] text-xs font-black px-3 py-1 shadow-sm">
                                            {{ number_format($sp->price, 2) }} ETB <span class="text-[10px] font-normal text-slate-500">/ night</span>
                                        </span>
                                    </div>

                                    <div class="p-5 space-y-3">
                                        <div>
                                            <h4 class="font-black text-base text-[#102b25] line-clamp-1" title="{{ $sp->title }}">{{ $sp->title }}</h4>
                                            <p class="text-xs text-slate-500 mt-0.5">📍 {{ $sp->address ?: $sp->city ?: 'Addis Ababa' }}</p>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600 font-semibold border-y border-[#f0e4cf] py-2">
                                            <span>🛏️ {{ $sp->bedrooms ?? 1 }} Beds</span>
                                            <span>🚿 {{ $sp->bathrooms ?? 1 }} Baths</span>
                                            <span>📐 {{ $sp->area ?? 50 }} m²</span>
                                            <span>🧹 {{ number_format($sp->getCleaningFee(), 2) }} ETB clean</span>
                                            <span>💼 {{ ! is_null($sp->getCustomServiceFee()) ? number_format($sp->getCustomServiceFee(), 2).' ETB service' : '5% service' }}</span>
                                        </div>

                                        <div class="text-[11px] text-slate-600 space-y-1">
                                            <p>🕒 <strong>Check-In:</strong> {{ $sp->check_in_time ?? '14:00' }} &bull; <strong>Check-Out:</strong> {{ $sp->check_out_time ?? '11:00' }}</p>
                                            <p>📅 <strong>Calendar Window:</strong>
                                                @if ($sp->available_from || $sp->available_to)
                                                    {{ $sp->available_from ? \Carbon\Carbon::parse($sp->available_from)->format('M d, Y') : 'Immediate' }} &rarr; {{ $sp->available_to ? \Carbon\Carbon::parse($sp->available_to)->format('M d, Y') : 'Open' }}
                                                @else
                                                    Year-round open
                                                @endif
                                            </p>
                                        </div>

                                        @if ($sp->house_rules)
                                            <details class="text-[11px] bg-[#fcf9f5] border border-[#ebdcc9] rounded-xl p-2.5 cursor-pointer">
                                                <summary class="font-bold text-[#102b25] select-none">📋 View House Rules</summary>
                                                <p class="mt-2 text-slate-700 whitespace-pre-line leading-relaxed">{{ $sp->house_rules }}</p>
                                            </details>
                                        @endif

                                        {{-- Inline Define Availability Form --}}
                                        <div class="rounded-2xl bg-[#ebdcc9]/30 border border-[#ebdcc9] p-3">
                                            <p class="text-[10px] font-black uppercase text-[#102b25] mb-1.5">Define Availability &amp; Dates</p>
                                            <form method="POST" action="{{ route('receptionist.stays.availability', $sp->id) }}" class="space-y-2">
                                                @csrf
                                                @method('PUT')
                                                <div class="grid grid-cols-3 gap-1.5">
                                                    <select name="is_available" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[11px] font-semibold text-[#1d3c34]">
                                                        <option value="1" @selected($sp->is_available)>🟢 Open</option>
                                                        <option value="0" @selected(! $sp->is_available)>🔴 Block</option>
                                                    </select>
                                                    <input type="date" name="available_from" value="{{ $sp->available_from ? \Carbon\Carbon::parse($sp->available_from)->format('Y-m-d') : '' }}" class="rounded-lg border border-[#d9cab3] bg-white px-1.5 py-1 text-[10px] font-semibold" title="Available From">
                                                    <input type="date" name="available_to" value="{{ $sp->available_to ? \Carbon\Carbon::parse($sp->available_to)->format('Y-m-d') : '' }}" class="rounded-lg border border-[#d9cab3] bg-white px-1.5 py-1 text-[10px] font-semibold" title="Available To">
                                                </div>
                                                <button type="submit" class="w-full rounded-lg bg-[#102b25] py-1 text-[11px] font-bold text-[#d4af37] hover:bg-black transition">
                                                    Update Availability Window
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-5 pt-0 flex items-center gap-2 border-t border-[#f0e4cf] mt-3">
                                    <button type="button" onclick="openStayEditModal({{ json_encode($sp) }})" class="flex-1 rounded-xl bg-white border border-[#d9cab3] py-2 text-center text-xs font-bold text-[#102b25] hover:bg-[#fcf9f5] transition">
                                        ✏️ Edit Prices &amp; Rules
                                    </button>
                                    @if ($sp->slug)
                                        <a href="{{ route('properties.show', $sp->slug) }}" target="_blank" class="rounded-xl bg-[#102b25] px-3 py-2 text-xs font-bold text-[#d4af37] hover:bg-black transition" title="Open Public Listing">
                                            👁️
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- 6. SECTION: GUEST SERVICE REQUESTS & CONCIERGE --}}
            <div data-section="services" class="section-panel hidden mt-8 space-y-6">
                <div class="flex items-center justify-between border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">Housekeeping, Room Turnover &amp; Concierge</h2>
                        <p class="text-xs text-slate-600">Schedule room turnarounds, deep sanitization, linen replenishment, and track guest service requests.</p>
                    </div>
                </div>

                {{-- Host / Receptionist Housekeeping Turnover Dispatch Card --}}
                <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 sm:p-7 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#f0e4cf] pb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#102b25] text-xl text-[#d4af37]">🧹</span>
                            <div>
                                <h3 class="text-lg font-black text-[#102b25]">Schedule Turnover / Room Cleaning</h3>
                                <p class="text-xs text-slate-500">Dispatch housekeeping staff to turn over guest rooms between stays or perform scheduled sanitization.</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Quick Presets:</span>
                            <button type="button" onclick="setCleaningPreset('Full turnover: linen replacement, bathroom sanitization, towel restock')" class="inline-flex items-center gap-1.5 rounded-xl bg-[#102b25] border-2 border-[#d4af37] px-3.5 py-1.5 text-xs font-black text-white shadow-xs hover:bg-black hover:border-amber-300 transition cursor-pointer">
                                <span>🛏️</span>
                                <span class="text-white font-black">Full Turnover</span>
                            </button>
                            <button type="button" onclick="setCleaningPreset('Linen & towel refresh, toiletries replenishment')" class="inline-flex items-center gap-1.5 rounded-xl bg-[#0f1f33] border-2 border-[#60a5fa] px-3.5 py-1.5 text-xs font-black text-white shadow-xs hover:bg-black hover:border-blue-300 transition cursor-pointer">
                                <span>🧺</span>
                                <span class="text-white font-black">Linen Refresh</span>
                            </button>
                            <button type="button" onclick="setCleaningPreset('Deep sanitization, floor scrub, AC filter cleaning')" class="inline-flex items-center gap-1.5 rounded-xl bg-[#7c2d12] border-2 border-[#fb923c] px-3.5 py-1.5 text-xs font-black text-white shadow-xs hover:bg-black hover:border-orange-300 transition cursor-pointer">
                                <span>🧼</span>
                                <span class="text-white font-black">Deep Clean</span>
                            </button>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('receptionist.cleaning.store', $currentProperty->id ?? $hotelProperties->first()?->id) }}" class="mt-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase">Room Number *</label>
                            <input type="text" name="room_number" required placeholder="e.g. 102, 204" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase">Priority *</label>
                            <select name="priority" required class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                <option value="normal">Normal Priority</option>
                                <option value="urgent">Urgent (Turnover Before Check-In)</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-600 uppercase">Turnover Checklist / Description *</label>
                            <div class="flex gap-2 mt-1.5">
                                <input type="text" name="description" id="cleaning_description" required placeholder="Linen replacement, bathroom sanitization, towel restock" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                <button type="submit" class="rounded-xl bg-[#102b25] px-5 py-2.5 text-xs font-bold text-[#d4af37] hover:bg-black transition shadow-xs shrink-0 flex items-center gap-1.5">
                                    <span>🧹</span>
                                    <span>Dispatch</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs">
                    @if ($serviceRequests->isEmpty())
                        <div class="p-12 text-center text-slate-500">
                            <span class="text-4xl">🧹</span>
                            <p class="mt-3 text-base font-bold text-[#102b25]">No Guest Service Requests</p>
                            <p class="text-xs text-slate-500 mt-1">All rooms are serviced and no inquiries are currently open.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#ebdcc9]/40 text-[#102b25] font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="p-3">Room</th>
                                        <th class="p-3">Guest Name</th>
                                        <th class="p-3">Service Type</th>
                                        <th class="p-3">Priority</th>
                                        <th class="p-3">Description / Details</th>
                                        <th class="p-3">Status</th>
                                        <th class="p-3 text-right">Update Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#ebdcc9]/30">
                                    @foreach ($serviceRequests as $req)
                                        <tr class="hover:bg-[#fcf9f5]">
                                            <td class="p-3 font-mono font-bold text-[#102b25]">
                                                {{ $req->room_number ?: 'General' }}
                                            </td>
                                            <td class="p-3 font-semibold text-slate-900">
                                                {{ $req->guest_name }}
                                            </td>
                                            <td class="p-3 font-bold text-slate-800">
                                                {{ $req->service_type_label }}
                                            </td>
                                            <td class="p-3">
                                                @if ($req->priority === 'urgent')
                                                    <span class="rounded-full bg-rose-100 text-rose-800 px-2 py-0.5 text-[10px] font-black uppercase">Urgent</span>
                                                @else
                                                    <span class="rounded-full bg-slate-100 text-slate-700 px-2 py-0.5 text-[10px] font-bold uppercase">Normal</span>
                                                @endif
                                            </td>
                                            <td class="p-3 max-w-xs text-slate-600">
                                                {{ $req->description }}
                                                @if ($req->response_notes)
                                                    <p class="mt-1 text-[11px] text-emerald-700 font-semibold italic">Note: {{ $req->response_notes }}</p>
                                                @endif
                                            </td>
                                            <td class="p-3">
                                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $req->status_badge_class }}">
                                                    {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                                                </span>
                                            </td>
                                            <td class="p-3 text-right">
                                                <form method="POST" action="{{ route('receptionist.service-requests.update', $req->id) }}" class="inline-flex items-center gap-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="status" onchange="this.form.submit()" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[11px] font-semibold text-[#1d3c34]">
                                                        <option value="pending" @selected($req->status === 'pending')>Pending</option>
                                                        <option value="in_progress" @selected($req->status === 'in_progress')>In Progress</option>
                                                        <option value="completed" @selected($req->status === 'completed')>Completed</option>
                                                        <option value="cancelled" @selected($req->status === 'cancelled')>Cancelled</option>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 7. SECTION: SHIFT CASH & DRAWER RECONCILIATION --}}
            <div data-section="shifts" class="section-panel hidden mt-8 space-y-6">
                <div class="flex items-center justify-between border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">Shift Cash Reconciliation &amp; Finance Audit Handoff</h2>
                        <p class="text-xs text-slate-600">Reconcile cash collections, Telebirr, CBE, and Card POS slips. Submits directly to the Finance Officer.</p>
                    </div>
                </div>

                {{-- Submit Shift Form Card --}}
                <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs max-w-3xl">
                    <h3 class="text-base font-black text-[#102b25] mb-4">Close Shift &amp; Submit Reconciliation</h3>
                    <form method="POST" action="{{ route('receptionist.shifts.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="property_id" value="{{ $currentProperty?->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-600">Shift Date</label>
                                <input type="date" name="shift_date" value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2 text-xs font-semibold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-600">Shift Type</label>
                                <select name="shift_type" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2 text-xs font-semibold">
                                    <option value="morning">Morning Shift (06:00 - 14:00)</option>
                                    <option value="day" selected>Day Shift (14:00 - 22:00)</option>
                                    <option value="night">Night Shift (22:00 - 06:00)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-[#fcf9f5] rounded-2xl p-4 border border-[#ebdcc9]">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500">💵 Cash In Drawer (ETB)</label>
                                <input type="number" step="0.01" name="cash_collected" value="{{ $stats['cash_collected'] }}" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2 py-1.5 text-xs font-black">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500">📱 Telebirr (ETB)</label>
                                <input type="number" step="0.01" name="telebirr_collected" value="{{ $stats['telebirr_collected'] }}" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2 py-1.5 text-xs font-black">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500">🏦 CBE Birr (ETB)</label>
                                <input type="number" step="0.01" name="cbe_collected" value="{{ $stats['cbe_collected'] }}" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2 py-1.5 text-xs font-black">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500">💳 Card POS (ETB)</label>
                                <input type="number" step="0.01" name="card_pos_collected" value="{{ $stats['pos_collected'] }}" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2 py-1.5 text-xs font-black">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-600">Total Check-Ins Handled</label>
                                <input type="number" name="checkins_count" value="{{ $stats['in_house_count'] }}" min="0" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2 text-xs font-semibold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-600">Total Check-Outs Handled</label>
                                <input type="number" name="checkouts_count" value="{{ $stats['today_checkouts_count'] }}" min="0" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2 text-xs font-semibold">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600">Handoff Notes &amp; Observations</label>
                            <textarea name="notes" rows="2" placeholder="Note any cashier discrepancies, pending repairs, or VIP requests for next shift..." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2 text-xs font-semibold"></textarea>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full rounded-xl bg-[#102b25] px-4 py-3 text-sm font-bold text-[#d4af37] transition hover:bg-black">
                                Submit Shift Report to Finance Desk
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Shift History & Audit Status --}}
                <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs">
                    <h3 class="text-base font-black text-[#102b25] mb-4">Past Shifts &amp; Finance Verification Status</h3>
                    @if ($shiftHistory->isEmpty())
                        <p class="text-xs text-slate-500">No shift reconciliations submitted yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#ebdcc9]/40 text-[#102b25] font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="p-3">Reference</th>
                                        <th class="p-3">Shift Date / Type</th>
                                        <th class="p-3">Receptionist</th>
                                        <th class="p-3">Total Collected</th>
                                        <th class="p-3">Check-Ins / Outs</th>
                                        <th class="p-3">Finance Status</th>
                                        <th class="p-3">Finance Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#ebdcc9]/30">
                                    @foreach ($shiftHistory as $sh)
                                        <tr class="hover:bg-[#fcf9f5]">
                                            <td class="p-3 font-mono font-bold text-[#102b25]">{{ $sh->shift_reference }}</td>
                                            <td class="p-3">
                                                <p class="font-bold">{{ \Carbon\Carbon::parse($sh->shift_date)->format('M d, Y') }}</p>
                                                <p class="text-[10px] text-slate-400 capitalize">{{ $sh->shift_type }} shift</p>
                                            </td>
                                            <td class="p-3 font-semibold text-slate-800">{{ $sh->receptionist?->name ?? 'Front Desk' }}</td>
                                            <td class="p-3 font-black text-[#102b25]">{{ number_format($sh->total_collected, 2) }} ETB</td>
                                            <td class="p-3 text-slate-600">{{ $sh->checkins_count }} in / {{ $sh->checkouts_count }} out</td>
                                            <td class="p-3">
                                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $sh->status_badge_class }}">
                                                    {{ $sh->status_label }}
                                                </span>
                                            </td>
                                            <td class="p-3 text-slate-500 italic max-w-xs">
                                                {{ $sh->finance_notes ?: '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 8. SECTION: ALL RESERVATIONS TABLE --}}
            <div data-section="bookings" class="section-panel hidden mt-8 space-y-6">
                <div class="flex items-center justify-between border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">All Stay Reservations</h2>
                        <p class="text-xs text-slate-600">Complete log of past, active, and upcoming bookings for this accommodation.</p>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs">
                    @if ($recentBookings->isEmpty())
                        <p class="text-xs text-slate-500 p-8 text-center">No reservations found.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#ebdcc9]/40 text-[#102b25] font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="p-3">Reference</th>
                                        <th class="p-3">Guest</th>
                                        <th class="p-3">Room</th>
                                        <th class="p-3">Dates</th>
                                        <th class="p-3">Total Amount</th>
                                        <th class="p-3">Booking Status</th>
                                        <th class="p-3">Payment</th>
                                        <th class="p-3 text-right">Folio</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#ebdcc9]/30">
                                    @foreach ($recentBookings as $rb)
                                        <tr class="hover:bg-[#fcf9f5]">
                                            <td class="p-3 font-mono font-bold text-[#102b25]">{{ $rb->booking_reference }}</td>
                                            <td class="p-3 font-bold text-slate-900">{{ $rb->guest_name ?? $rb->user?->name }}</td>
                                            <td class="p-3 font-mono">{{ $rb->room_number ?: '—' }}</td>
                                            <td class="p-3">
                                                {{ \Carbon\Carbon::parse($rb->check_in_date)->format('M d') }} &rarr; {{ \Carbon\Carbon::parse($rb->check_out_date)->format('M d, Y') }}
                                            </td>
                                            <td class="p-3 font-bold">{{ number_format($rb->total_amount, 2) }} ETB</td>
                                            <td class="p-3">
                                                <span class="rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold uppercase px-2 py-0.5">
                                                    {{ $rb->status }}
                                                </span>
                                            </td>
                                            <td class="p-3">
                                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $rb->payment_status_badge[1] }}">
                                                    {{ $rb->payment_status_badge[0] }}
                                                </span>
                                            </td>
                                            <td class="p-3 text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    @if ($rb->status === 'pending')
                                                        <form method="POST" action="{{ route('receptionist.bookings.accept', $rb->id) }}" class="inline">
                                                            @csrf
                                                            <button type="submit" class="rounded-lg bg-emerald-600 px-2.5 py-1 text-xs font-bold text-white hover:bg-emerald-700 transition shadow-xs" title="Accept & Confirm Reservation">
                                                                ✓ Accept
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <a href="{{ route('receptionist.bookings.folio', $rb->id) }}" target="_blank" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                                        📄 Folio
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $recentBookings->links() }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- 9. SECTION: GUEST & CLIENT REVIEWS MODERATION DESK --}}
            <div data-section="reviews" class="section-panel hidden mt-8 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">Guest Reviews &amp; Hospitality Feedback Desk</h2>
                        <p class="text-xs text-slate-600">Monitor guest sentiment, cleanliness and hospitality ratings, and moderate verified stay feedback.</p>
                    </div>
                    <span class="rounded-full bg-[#102b25] text-[#d4af37] px-3.5 py-1 text-xs font-bold shrink-0">
                        ⭐ {{ $reviews->count() }} Reviews Logged
                    </span>
                </div>

                @if ($reviews->isEmpty())
                    <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center text-slate-500">
                        <span class="text-4xl">⭐</span>
                        <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Guest Reviews Logged Yet</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                            Guests can submit verified reviews for their stays and leased properties from their guest portal.
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($reviews as $rev)
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
                                                        ✓ Verified Guest
                                                    </span>
                                                @endif
                                            </div>
                                            <h3 class="text-base font-black text-[#102b25] mt-1">{{ $rev->review_title ?: 'Stay Experience' }}</h3>
                                            <p class="text-xs text-slate-500 mt-0.5">By <strong>{{ $rev->user?->name ?? 'Guest' }}</strong> &bull; {{ $rev->created_at->format('M d, Y · H:i') }}</p>
                                        </div>
                                        <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase @if($rev->status === 'published') bg-emerald-100 text-emerald-800 @else bg-slate-100 text-slate-600 @endif">
                                            {{ $rev->status }}
                                        </span>
                                    </div>

                                    @if ($rev->property)
                                        <p class="mt-2 text-xs text-slate-600">
                                            Accommodation: <strong class="text-[#102b25]">{{ $rev->property->title }}</strong>
                                            @if ($rev->booking)
                                                (Ref: <span class="font-mono font-bold">{{ $rev->booking->booking_reference }}</span> &bull; Room: <span class="font-mono">{{ $rev->booking->room_number ?: 'N/A' }}</span>)
                                            @endif
                                        </p>
                                    @endif

                                    {{-- Sub-ratings breakdown --}}
                                    <div class="mt-3 grid grid-cols-5 gap-2 rounded-2xl bg-[#fcf9f5] p-2.5 text-center border border-[#ebdcc9] text-[10px]">
                                        <div>
                                            <span class="block text-slate-400 font-bold uppercase">Cleanliness</span>
                                            <span class="font-black text-[#102b25]">★ {{ $rev->cleanliness_rating }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-slate-400 font-bold uppercase">Accuracy</span>
                                            <span class="font-black text-[#102b25]">★ {{ $rev->accuracy_rating }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-slate-400 font-bold uppercase">Desk/Service</span>
                                            <span class="font-black text-[#102b25]">★ {{ $rev->communication_rating }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-slate-400 font-bold uppercase">Location</span>
                                            <span class="font-black text-[#102b25]">★ {{ $rev->location_rating }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-slate-400 font-bold uppercase">Value</span>
                                            <span class="font-black text-[#102b25]">★ {{ $rev->value_rating }}</span>
                                        </div>
                                    </div>

                                    <p class="mt-3 text-xs text-slate-700 bg-white p-3 rounded-xl border border-[#ebdcc9] leading-relaxed">
                                        "{{ $rev->review_body }}"
                                    </p>

                                    {{-- Host Response Section --}}
                                    @if ($rev->host_reply)
                                        <div class="mt-3 rounded-2xl bg-emerald-50 border border-emerald-200 p-3.5">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-black text-emerald-900">💬 Host Response</span>
                                                @if ($rev->host_replied_at)
                                                    <span class="text-[10px] text-emerald-700 font-mono">{{ \Carbon\Carbon::parse($rev->host_replied_at)->format('M d, Y · H:i') }}</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-xs text-emerald-800 leading-relaxed">{{ $rev->host_reply }}</p>
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('receptionist.reviews.reply', $rev->id) }}" class="mt-3 space-y-2 border-t border-[#ebdcc9] pt-3">
                                            @csrf
                                            <label class="block text-[11px] font-bold uppercase text-slate-500">Host Response to Guest</label>
                                            <textarea name="host_reply" rows="2" required placeholder="Thank guest, address feedback, or provide follow-up notes..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2 text-xs font-semibold focus:border-[#1d3c34] focus:outline-none"></textarea>
                                            <div class="flex justify-end">
                                                <button type="submit" class="rounded-xl bg-[#102b25] px-3.5 py-1.5 text-xs font-bold text-[#d4af37] hover:bg-black transition shadow-xs">
                                                    Post Host Reply
                                                </button>
                                            </div>
                                        </form>
                                    @endif
                                </div>

                                @if ($isAdmin)
                                    <div class="pt-2 border-t border-[#f0e4cf] flex items-center justify-between text-xs">
                                        <span class="text-slate-400 text-[11px]">Moderation controls</span>
                                        <form method="POST" action="{{ route('reviews.toggle', $rev->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="rounded-lg border px-3 py-1 text-xs font-bold @if($rev->status === 'published') border-rose-300 text-rose-700 hover:bg-rose-50 @else border-emerald-300 text-emerald-700 hover:bg-emerald-50 @endif transition">
                                                {{ $rev->status === 'published' ? 'Hide Review' : 'Publish Review' }}
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- 10. SECTION: MAINTENANCE & REPAIRS TICKETS DESK --}}
            <div data-section="maintenance" class="section-panel hidden mt-8 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">Maintenance &amp; Facility Repair Desk</h2>
                        <p class="text-xs text-slate-600">Track in-room plumbing, electrical, HVAC, and appliances repair tickets dispatched across properties.</p>
                    </div>
                    <span class="rounded-full bg-blue-100 text-blue-800 px-3.5 py-1 text-xs font-bold shrink-0">
                        🔧 {{ $maintenanceTickets->count() }} Tickets
                    </span>
                </div>

                @if ($maintenanceTickets->isEmpty())
                    <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center text-slate-500">
                        <span class="text-4xl">🛠️</span>
                        <h3 class="mt-3 text-lg font-bold text-[#102b25]">All Facilities Operational</h3>
                        <p class="text-xs text-slate-500 mt-1">No pending maintenance or repair tickets recorded for this property.</p>
                    </div>
                @else
                    <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#ebdcc9]/40 text-[#102b25] font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="p-3">Ticket #</th>
                                        <th class="p-3">Property &amp; Guest/Tenant</th>
                                        <th class="p-3">Category</th>
                                        <th class="p-3">Priority</th>
                                        <th class="p-3">Issue Description</th>
                                        <th class="p-3">Status</th>
                                        <th class="p-3 text-right">Update Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#ebdcc9]/30">
                                    @foreach ($maintenanceTickets as $mt)
                                        <tr class="hover:bg-[#fcf9f5]">
                                            <td class="p-3 font-mono font-black text-[#102b25]">{{ $mt->ticket_number }}</td>
                                            <td class="p-3">
                                                <p class="font-bold text-slate-900">{{ $mt->property?->title ?? 'General Property' }}</p>
                                                <p class="text-[11px] text-slate-500">By: {{ $mt->user?->name ?? 'Guest/Tenant' }} ({{ $mt->user?->phone ?? 'No phone' }})</p>
                                            </td>
                                            <td class="p-3 font-semibold">{{ $mt->category_label }}</td>
                                            <td class="p-3">
                                                <span class="rounded-full px-2 py-0.5 text-[10px] font-black uppercase {{ $mt->priority_badge_class }}">
                                                    {{ $mt->priority }}
                                                </span>
                                            </td>
                                            <td class="p-3 max-w-xs text-slate-700">
                                                <p class="font-bold">{{ $mt->title }}</p>
                                                <p class="text-[11px] text-slate-600 line-clamp-2">"{{ $mt->description }}"</p>
                                                @if ($mt->resolution_notes)
                                                    <p class="mt-1 text-[10px] text-emerald-700 font-semibold italic">Note: {{ $mt->resolution_notes }}</p>
                                                @endif
                                            </td>
                                            <td class="p-3">
                                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase {{ $mt->status_badge_class }}">
                                                    {{ str_replace('_', ' ', $mt->status) }}
                                                </span>
                                            </td>
                                            <td class="p-3 text-right">
                                                <form method="POST" action="{{ route('receptionist.maintenance.update', $mt->id) }}" class="inline-flex items-center gap-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="status" onchange="this.form.submit()" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[11px] font-semibold text-[#1d3c34]">
                                                        <option value="open" @selected($mt->status === 'open')>Open</option>
                                                        <option value="assigned" @selected($mt->status === 'assigned')>Assigned</option>
                                                        <option value="in_progress" @selected($mt->status === 'in_progress')>In Progress</option>
                                                        <option value="completed" @selected($mt->status === 'completed')>Completed</option>
                                                        <option value="closed" @selected($mt->status === 'closed')>Closed</option>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 11. SECTION: GUEST COMMUNICATION & INQUIRIES DESK --}}
            <div data-section="inquiries" class="section-panel hidden mt-8 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">Guest &amp; Client Communication Desk</h2>
                        <p class="text-xs text-slate-600">Direct inquiries, guest questions, and consultation requests submitted by guests and clients.</p>
                    </div>
                    <span class="rounded-full bg-emerald-100 text-emerald-800 px-3.5 py-1 text-xs font-bold shrink-0">
                        💬 {{ $inquiries->count() }} Inquiries
                    </span>
                </div>

                @if ($inquiries->isEmpty())
                    <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center text-slate-500">
                        <span class="text-4xl">💬</span>
                        <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Messages in Communication Desk</h3>
                        <p class="text-xs text-slate-500 mt-1">Guest inquiries submitted from contact forms and listing pages will appear here.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($inquiries as $inq)
                            <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs flex flex-col justify-between gap-4">
                                <div>
                                    <div class="flex items-start justify-between gap-3 border-b border-[#f0e4cf] pb-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="rounded-full @if($inq->status === 'unread') bg-rose-100 text-rose-800 @else bg-slate-100 text-slate-700 @endif px-2.5 py-0.5 text-[10px] font-black uppercase">
                                                    {{ $inq->status ?: 'unread' }}
                                                </span>
                                                @if ($inq->agent)
                                                    <span class="rounded bg-blue-100 text-blue-800 text-[10px] font-black uppercase px-2 py-0.5">👤 Assigned to Agent: {{ $inq->agent->name }}</span>
                                                @else
                                                    <span class="rounded bg-[#102b25] text-[#d4af37] text-[10px] font-black uppercase px-2 py-0.5">🏛️ GTM Reception Desk</span>
                                                @endif
                                            </div>
                                            <h3 class="text-base font-black text-[#102b25] mt-2">{{ $inq->subject ?: 'Guest Accommodation Inquiry' }}</h3>
                                            <p class="text-xs text-slate-500 mt-0.5">From: <strong>{{ $inq->name }}</strong> ({{ $inq->email }})</p>
                                            @if ($inq->phone)
                                                <p class="text-xs text-slate-600 font-semibold mt-0.5">📞 Ethiopian Phone: <a href="tel:{{ $inq->phone }}" class="text-[#102b25] underline">{{ $inq->phone }}</a></p>
                                            @endif
                                        </div>
                                        <span class="text-[11px] text-slate-400 shrink-0 font-mono">{{ $inq->created_at->format('M d · H:i') }}</span>
                                    </div>

                                    <div class="mt-3 bg-[#fcf9f5] rounded-2xl p-4 border border-[#ebdcc9] text-xs text-slate-700 leading-relaxed">
                                        "{{ $inq->message }}"
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-[#f0e4cf] flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        @if ($inq->phone)
                                            <a href="tel:{{ $inq->phone }}" class="rounded-xl bg-emerald-700 text-white px-3 py-1.5 text-xs font-bold hover:bg-emerald-800 transition flex items-center gap-1 shadow-xs">
                                                <span>📞</span>
                                                <span>Call Guest</span>
                                            </a>
                                        @endif
                                        @if ($inq->email)
                                            <a href="mailto:{{ $inq->email }}?subject=Regarding your GTM Real Estate inquiry" class="rounded-xl bg-[#102b25] text-[#d4af37] px-3 py-1.5 text-xs font-bold hover:bg-black transition flex items-center gap-1 shadow-xs">
                                                <span>✉️</span>
                                                <span>Email Reply</span>
                                            </a>
                                        @endif
                                    </div>

                                    <form method="POST" action="{{ route('receptionist.inquiries.reply', $inq->id) }}" class="inline-flex items-center gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[11px] font-semibold text-[#1d3c34]">
                                            <option value="unread" @selected($inq->status === 'unread')>Mark Unread</option>
                                            <option value="read" @selected($inq->status === 'read')>Mark Read</option>
                                            <option value="responded" @selected($inq->status === 'responded')>Responded ✓</option>
                                            <option value="archived" @selected($inq->status === 'archived')>Archived</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- 12. SECTION: PROPERTIES (ADMIN CORPORATE VS LICENSED AGENT) --}}
            <div data-section="properties-desk" class="section-panel hidden mt-8 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#d9cab3] pb-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#102b25]">Property Portfolio Management Desk</h2>
                        <p class="text-xs text-slate-600">Front desk visibility into direct GTM Corporate Headquarter properties versus licensed agent-managed portfolios.</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="receptionist-tab-admin-btn" onclick="switchReceptionistPropTab('admin')" class="rounded-xl bg-[#102b25] px-4 py-2 text-xs font-black text-[#d4af37] transition">
                            🏛️ GTM Admin Properties ({{ $adminProperties->count() }})
                        </button>
                        <button type="button" id="receptionist-tab-agent-btn" onclick="switchReceptionistPropTab('agent')" class="rounded-xl bg-white border border-[#ebdcc9] px-4 py-2 text-xs font-bold text-slate-600 hover:bg-[#fcf9f5] transition">
                            👤 Agent Properties ({{ $agentProperties->count() }})
                        </button>
                    </div>
                </div>

                {{-- Tab A: Corporate Admin Properties --}}
                <div id="receptionist-prop-admin" class="space-y-4">
                    <div class="rounded-2xl bg-[#fcf9f5] border border-[#ebdcc9] p-4 flex items-center gap-3">
                        <span class="text-2xl">🏛️</span>
                        <div>
                            <h3 class="text-sm font-black text-[#102b25]">GTM Real Estate Corporate Managed</h3>
                            <p class="text-xs text-slate-600">Direct corporate holdings managed by Headquarter Executive Administrators and serviced by the Front Desk.</p>
                        </div>
                    </div>

                    @if ($adminProperties->isEmpty())
                        <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center text-slate-500">
                            <span class="text-4xl">🏛️</span>
                            <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Corporate Properties Listed</h3>
                            <p class="text-xs text-slate-500 mt-1">All properties are currently assigned to broker agencies.</p>
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
                                        <a href="{{ route('properties.show', $p->slug) }}" target="_blank" class="block w-full text-center rounded-xl bg-[#102b25] text-white py-2 text-xs font-bold hover:bg-black transition">
                                            Open Listing Page →
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Tab B: Agent Managed Properties --}}
                <div id="receptionist-prop-agent" class="hidden space-y-4">
                    <div class="rounded-2xl bg-[#fcf9f5] border border-[#ebdcc9] p-4 flex items-center gap-3">
                        <span class="text-2xl">👤</span>
                        <div>
                            <h3 class="text-sm font-black text-[#102b25]">Licensed Agent Managed Portfolios</h3>
                            <p class="text-xs text-slate-600">Represented by certified licensed brokers and agency partners with dedicated contact numbers.</p>
                        </div>
                    </div>

                    @if ($agentProperties->isEmpty())
                        <div class="rounded-3xl border border-dashed border-[#d9cab3] bg-white p-12 text-center text-slate-500">
                            <span class="text-4xl">👤</span>
                            <h3 class="mt-3 text-lg font-bold text-[#102b25]">No Agent Properties Listed</h3>
                            <p class="text-xs text-slate-500 mt-1">No agency partners currently have listings published.</p>
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
                                        <a href="{{ route('properties.show', $p->slug) }}" target="_blank" class="block w-full text-center rounded-xl bg-[#102b25] text-white py-2 text-xs font-bold hover:bg-black transition">
                                            Open Listing Page →
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- 13. SECTION: STAFF PROFILE --}}
            <div data-section="profile" class="section-panel hidden mt-8 space-y-6">
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8 max-w-3xl">
                    @include('layouts.partials.profile-form', [
                        'user' => $user,
                        'profileAction' => route('profile.update'),
                        'profileHeading' => 'My Front Desk Profile',
                        'profileSubheading' => 'Update your receptionist display name, phone number, login credentials, and avatar photo.',
                        'profileRoleLabel' => ucfirst($user->role ?? 'Receptionist'),
                    ])
                </div>
            </div>
        </main>
    </div>

    {{-- MODAL: CHECK-IN GUEST --}}
    <div id="check-in-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="w-full max-w-lg rounded-3xl bg-white p-6 sm:p-7 shadow-2xl border border-[#d9cab3]">
            <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-[#102b25] text-lg text-[#d4af37]">🔑</span>
                    <div>
                        <h3 class="text-lg font-black text-[#102b25]">Guest Check-In &amp; Key Issuance</h3>
                        <p class="text-xs text-slate-500">Verify guest identity, register room key number, and collect payment.</p>
                    </div>
                </div>
                <button type="button" onclick="closeCheckInModal()" class="rounded-full h-8 w-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 text-xl font-bold transition">&times;</button>
            </div>

            <form id="check-in-form" method="POST" action="" class="mt-4 space-y-4">
                @csrf
                <div class="bg-[#fcf9f5] rounded-2xl p-4 border border-[#ebdcc9] text-xs space-y-1.5">
                    <div class="flex items-center justify-between">
                        <p class="font-black text-[#102b25] text-sm" id="modal-guest-name">Guest Name</p>
                        <span class="rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase px-2 py-0.5">Ready for Check-In</span>
                    </div>
                    <p class="text-slate-600 font-medium" id="modal-guest-dates">Dates</p>
                    <p class="font-black text-[#102b25] text-sm" id="modal-guest-total">Total Amount: 0 ETB</p>
                </div>

                {{-- Room and ID Registration --}}
                <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-4 space-y-3">
                    <p class="text-[10px] font-black uppercase text-[#102b25] tracking-wider">🪪 Room Assignment &amp; Ethiopian ID Verification</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600">Assign Room Number *</label>
                            <input type="text" name="room_number" id="checkin-room-number" required placeholder="e.g. 104, Suite 201" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600">National ID / Passport # *</label>
                            <input type="text" name="guest_id_number" id="checkin-id-number" required placeholder="Kebele ID or Passport #" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- Counter Settlement --}}
                <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-4 space-y-3">
                    <p class="text-[10px] font-black uppercase text-[#102b25] tracking-wider">💵 Settle Folio Balance at Counter (If Unpaid)</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase">Payment Method</label>
                            <select name="payment_method" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                                <option value="cash">💵 Cash (Front Desk Drawer)</option>
                                <option value="telebirr">📱 Telebirr</option>
                                <option value="cbe">🏦 CBE Birr</option>
                                <option value="card_pos">💳 POS Card Terminal</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase">Amount Collected (ETB)</label>
                            <input type="number" step="0.01" name="amount_collected" id="checkin-amount" placeholder="0.00" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-black text-[#102b25] focus:border-[#102b25] focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-600 uppercase">Payment Reference / Txn # (Optional)</label>
                        <input type="text" name="payment_reference" placeholder="e.g. Telebirr Txn # / Bank Slip #" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#f0e4cf]">
                    <button type="button" onclick="closeCheckInModal()" class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</button>
                    <button type="submit" class="rounded-xl bg-[#102b25] px-6 py-2.5 text-xs font-black uppercase tracking-wider text-[#d4af37] hover:bg-black transition shadow-sm flex items-center gap-1.5">
                        <span>🔑</span>
                        <span>Confirm &amp; Check In Guest</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: CHECK-OUT GUEST --}}
    <div id="check-out-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl border border-[#d9cab3]">
            <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🚪</span>
                    <h3 class="text-lg font-black text-[#102b25]">Confirm Check-Out</h3>
                </div>
                <button type="button" onclick="closeCheckOutModal()" class="text-slate-400 hover:text-slate-700 text-2xl font-bold">&times;</button>
            </div>

            <form id="check-out-form" method="POST" action="" class="mt-4 space-y-4">
                @csrf
                <div class="bg-[#fcf9f5] rounded-xl p-3 border border-[#ebdcc9] text-xs space-y-1">
                    <p class="font-bold text-[#102b25]" id="checkout-guest-name">Guest Name</p>
                    <p class="text-slate-500" id="checkout-room">Room: -</p>
                </div>

                <p class="text-xs text-slate-600">
                    Ensure key card / physical keys are returned and inspect room before releasing.
                </p>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#f0e4cf]">
                    <button type="button" onclick="closeCheckOutModal()" class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="rounded-xl bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 text-xs font-bold">Complete Check-Out</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: NEW SERVICE REQUEST --}}
    <div id="service-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl border border-[#d9cab3]">
            <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🛎️</span>
                    <h3 class="text-lg font-black text-[#102b25]">New Service Request</h3>
                </div>
                <button type="button" onclick="closeServiceModal()" class="text-slate-400 hover:text-slate-700 text-2xl font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('receptionist.service-requests.store') }}" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="booking_id" id="service-booking-id">

                <div class="bg-[#fcf9f5] rounded-xl p-3 border border-[#ebdcc9] text-xs space-y-1">
                    <p class="font-bold text-[#102b25]" id="service-guest-display">Room &amp; Guest</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Service Type</label>
                    <select name="service_type" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2 text-xs font-semibold">
                        <option value="room_cleaning">🧹 Room Cleaning / Housekeeping</option>
                        <option value="extra_towels">🧺 Extra Linens &amp; Towels</option>
                        <option value="airport_transfer">🚕 Airport Shuttle Transfer</option>
                        <option value="wake_up_call">⏰ Wake-Up Call</option>
                        <option value="maintenance">🔧 In-Room Repair / Maintenance</option>
                        <option value="inquiry">ℹ️ Concierge / Guest Inquiry</option>
                        <option value="other">🛎️ Other Assistance</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Priority Level</label>
                    <select name="priority" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2 text-xs font-semibold">
                        <option value="normal">Normal Priority</option>
                        <option value="urgent">Urgent Priority</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Request Details</label>
                    <textarea name="description" rows="3" required placeholder="Specify guest request details..." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2 text-xs font-semibold"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#f0e4cf]">
                    <button type="button" onclick="closeServiceModal()" class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="rounded-xl bg-[#102b25] px-5 py-2 text-xs font-bold text-[#d4af37] hover:bg-black">Dispatch Request</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: EDIT SHORT-STAY LISTING, PRICING & RULES --}}
    <div id="stay-edit-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="w-full max-w-2xl rounded-3xl bg-white p-6 sm:p-7 shadow-2xl border border-[#d9cab3] max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-[#102b25] text-lg text-[#d4af37]">✏️</span>
                    <div>
                        <h3 class="text-lg font-black text-[#102b25]">Edit Short-Stay Pricing, Rules &amp; Availability</h3>
                        <p class="text-xs text-slate-500">Update nightly rate, turnover fee, calendar window, and check-in policies.</p>
                    </div>
                </div>
                <button type="button" onclick="closeStayEditModal()" class="rounded-full h-8 w-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 text-xl font-bold transition">&times;</button>
            </div>

            <form id="stay-edit-form" method="POST" action="" enctype="multipart/form-data" class="mt-5 space-y-5">
                @csrf
                @method('PUT')

                {{-- Group 1: Title & Pricing --}}
                <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-4 space-y-3">
                    <p class="text-[10px] font-black uppercase text-[#102b25] tracking-wider">🏷️ Listing Title &amp; Nightly Rates</p>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600">Listing Title *</label>
                        <input type="text" name="title" id="edit-stay-title" required class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600">Nightly Price (ETB) *</label>
                            <input type="number" step="0.01" name="price" id="edit-stay-price" required class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-black text-[#102b25] focus:border-[#102b25] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600">Cleaning Fee (ETB)</label>
                            <input type="number" step="0.01" name="cleaning_fee" id="edit-stay-cleaning-fee" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600">Service Fee (ETB)</label>
                            <input type="number" step="0.01" name="service_fee" id="edit-stay-service-fee" placeholder="Blank for 5%" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- Group 2: Operating Hours --}}
                <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-4 space-y-3">
                    <p class="text-[10px] font-black uppercase text-[#102b25] tracking-wider">🕒 Standard Operating Hours</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600">Standard Check-In Hour</label>
                            <input type="text" name="check_in_time" id="edit-stay-checkin-time" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600">Standard Check-Out Hour</label>
                            <input type="text" name="check_out_time" id="edit-stay-checkout-time" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- Group 3: Availability Control --}}
                <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-4 space-y-3">
                    <p class="text-[10px] font-black uppercase text-[#102b25] tracking-wider">📅 Availability Window</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600">Booking Status</label>
                            <select name="is_available" id="edit-stay-is-available" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#102b25]">
                                <option value="1">🟢 Available</option>
                                <option value="0">🔴 Blocked</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600">Available From</label>
                            <input type="date" name="available_from" id="edit-stay-available-from" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#102b25]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600">Available To</label>
                            <input type="date" name="available_to" id="edit-stay-available-to" class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#102b25]">
                        </div>
                    </div>
                </div>

                {{-- Group 4: Rules & Description --}}
                <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-4 space-y-3">
                    <p class="text-[10px] font-black uppercase text-[#102b25] tracking-wider">📋 Policies &amp; Details</p>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600">House Rules *</label>
                        <textarea name="house_rules" id="edit-stay-house-rules" rows="3" required class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600">Listing Description *</label>
                        <textarea name="description" id="edit-stay-description" rows="3" required class="mt-1.5 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#102b25] focus:border-[#102b25] focus:outline-none"></textarea>
                    </div>
                </div>

                {{-- Group 5: Photo & Cover Image --}}
                <div class="rounded-2xl border border-[#ebdcc9] bg-[#fcf9f5] p-4 space-y-3">
                    <p class="text-[10px] font-black uppercase text-[#102b25] tracking-wider">🖼️ Update Photos &amp; Image</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600">Upload New Main Image</label>
                            <input type="file" name="image" accept="image/png,image/jpeg,image/webp,image/avif" class="mt-1.5 w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#102b25] file:text-[#d4af37] rounded-xl border border-[#d9cab3] bg-white p-1">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600">Add Gallery Photos</label>
                            <input type="file" name="images[]" multiple accept="image/png,image/jpeg,image/webp,image/avif" class="mt-1.5 w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#102b25] file:text-[#d4af37] rounded-xl border border-[#d9cab3] bg-white p-1">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#f0e4cf]">
                    <button type="button" onclick="closeStayEditModal()" class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</button>
                    <button type="submit" class="rounded-xl bg-[#102b25] px-6 py-2.5 text-xs font-black uppercase tracking-wider text-[#d4af37] hover:bg-black transition shadow-sm">
                        Save Listing &amp; Rules
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- JavaScript for Tab Navigation, Live Calculators, and Modals --}}
    <script>
        function activateSection(sectionName) {
            const panels = document.querySelectorAll('.section-panel');
            const navLinks = document.querySelectorAll('.nav-section-link');
            const ribbonTabs = document.querySelectorAll('.pms-ribbon-tab');

            panels.forEach(panel => {
                const visible = panel.dataset.section === sectionName;
                panel.classList.toggle('hidden', !visible);
            });

            navLinks.forEach(link => {
                const active = link.dataset.targetSection === sectionName;
                link.classList.toggle('bg-white/10', active);
                link.classList.toggle('text-white', active);
                link.classList.toggle('text-[#dfeee4]', !active);
                link.classList.toggle('font-semibold', true);
            });

            ribbonTabs.forEach(tab => {
                const active = tab.dataset.pmsTab === sectionName;
                if (active) {
                    tab.className = 'pms-ribbon-tab rounded-xl px-3 py-2 text-xs font-black transition flex items-center gap-2 shrink-0 bg-[#102b25] text-[#d4af37] shadow-xs';
                } else {
                    tab.className = 'pms-ribbon-tab rounded-xl px-3 py-2 text-xs font-bold transition flex items-center gap-2 shrink-0 bg-transparent text-slate-700 hover:bg-[#fcf9f5] hover:text-[#102b25]';
                }
            });

            if (history.replaceState) {
                history.replaceState(null, '', '#' + sectionName);
            }
        }

        window.activateSection = activateSection;
        window.showDashboardSection = activateSection;

        document.addEventListener('DOMContentLoaded', function () {
            const navLinks = document.querySelectorAll('.nav-section-link');

            navLinks.forEach(link => {
                link.addEventListener('click', function (event) {
                    const target = this.dataset.targetSection;
                    if (!target) return;
                    event.preventDefault();
                    activateSection(target);
                });
            });

            const params = new URLSearchParams(window.location.search);
            const querySection = params.get('section');
            const initialHash = window.location.hash.replace('#', '');
            const targetInit = querySection || initialHash;
            if (targetInit && document.querySelector('[data-section="' + targetInit + '"]')) {
                activateSection(targetInit);
            }

            // Walk-in live calculator init
            const propertySelect = document.getElementById('walkin_property_id');
            if (propertySelect) {
                propertySelect.addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    const price = selectedOption.getAttribute('data-price');
                    if (price) {
                        const nightlyInput = document.getElementById('walkin_nightly');
                        if (nightlyInput) {
                            nightlyInput.value = price;
                            calculateWalkInTotal();
                        }
                    }
                });
            }

            calculateWalkInTotal();
        });

        // Live Folio Calculation for Walk-In Registration
        function calculateWalkInTotal() {
            const checkin = document.getElementById('walkin_checkin');
            const checkout = document.getElementById('walkin_checkout');
            const nightly = document.getElementById('walkin_nightly');
            const summaryText = document.getElementById('walkin_summary_text');
            const totalDisplay = document.getElementById('walkin_total_display');

            if (!checkin || !checkout || !nightly || !summaryText || !totalDisplay) return;

            const d1 = new Date(checkin.value);
            const d2 = new Date(checkout.value);
            const diffTime = d2 - d1;
            let nights = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            if (isNaN(nights) || nights < 1) nights = 1;

            const rate = parseFloat(nightly.value) || 0;
            const total = nights * rate;

            summaryText.textContent = `${nights} night${nights > 1 ? 's' : ''} • Rate: ${rate.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} ETB / night`;
            totalDisplay.textContent = `${total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} ETB`;
        }

        // Housekeeping Turnover Presets
        function setCleaningPreset(presetText) {
            const descInput = document.getElementById('cleaning_description');
            if (descInput) {
                descInput.value = presetText;
                descInput.focus();
            }
        }

        // Check-In Modal Functions
        function openCheckInModal(booking) {
            const modal = document.getElementById('check-in-modal');
            const form = document.getElementById('check-in-form');
            form.action = `/receptionist/check-in/${booking.id}`;

            document.getElementById('modal-guest-name').textContent = `${booking.guest_name || (booking.user ? booking.user.name : 'Guest')} · Ref: ${booking.booking_reference}`;
            document.getElementById('modal-guest-dates').textContent = `Stay: ${booking.check_in_date} to ${booking.check_out_date} (${booking.total_nights} nights)`;
            document.getElementById('modal-guest-total').textContent = `Total: ${Number(booking.total_amount).toLocaleString()} ETB (Status: ${booking.payment_status || 'due'})`;

            document.getElementById('checkin-room-number').value = booking.room_number || '';
            document.getElementById('checkin-id-number').value = booking.guest_id_number || '';
            document.getElementById('checkin-amount').value = (booking.payment_status === 'paid') ? '' : booking.total_amount;

            modal.classList.remove('hidden');
        }

        function closeCheckInModal() {
            document.getElementById('check-in-modal').classList.add('hidden');
        }

        // Check-Out Modal Functions
        function openCheckOutModal(booking) {
            const modal = document.getElementById('check-out-modal');
            const form = document.getElementById('check-out-form');
            form.action = `/receptionist/check-out/${booking.id}`;

            document.getElementById('checkout-guest-name').textContent = `${booking.guest_name || (booking.user ? booking.user.name : 'Guest')} · Ref: ${booking.booking_reference}`;
            document.getElementById('checkout-room').textContent = `Assigned Room: ${booking.room_number || 'N/A'}`;

            modal.classList.remove('hidden');
        }

        function closeCheckOutModal() {
            document.getElementById('check-out-modal').classList.add('hidden');
        }

        // Service Modal Functions
        function openServiceModal(bookingId, roomNumber, guestName) {
            const modal = document.getElementById('service-modal');
            document.getElementById('service-booking-id').value = bookingId;
            document.getElementById('service-guest-display').textContent = `Room ${roomNumber} · ${guestName}`;
            modal.classList.remove('hidden');
        }

        function closeServiceModal() {
            document.getElementById('service-modal').classList.add('hidden');
        }

        // Shortcut buttons
        function openWalkInModal() {
            activateSection('walk-in');
        }

        function openShiftModal() {
            activateSection('shifts');
        }

        // Tab switcher for Admin vs Agent properties
        function switchReceptionistPropTab(tab) {
            const adminTab = document.getElementById('receptionist-prop-admin');
            const agentTab = document.getElementById('receptionist-prop-agent');
            const btnAdmin = document.getElementById('receptionist-tab-admin-btn');
            const btnAgent = document.getElementById('receptionist-tab-agent-btn');

            if (tab === 'admin') {
                adminTab.classList.remove('hidden');
                agentTab.classList.add('hidden');
                btnAdmin.className = 'rounded-xl bg-[#102b25] px-4 py-2 text-xs font-black text-[#d4af37] transition';
                btnAgent.className = 'rounded-xl bg-white border border-[#ebdcc9] px-4 py-2 text-xs font-bold text-slate-600 hover:bg-[#fcf9f5] transition';
            } else {
                agentTab.classList.remove('hidden');
                adminTab.classList.add('hidden');
                btnAgent.className = 'rounded-xl bg-[#102b25] px-4 py-2 text-xs font-black text-[#d4af37] transition';
                btnAdmin.className = 'rounded-xl bg-white border border-[#ebdcc9] px-4 py-2 text-xs font-bold text-slate-600 hover:bg-[#fcf9f5] transition';
            }
        }

        // Host Stay Listings Functions
        function toggleStayCreateForm() {
            const formCard = document.getElementById('stay-create-card');
            if (formCard) {
                formCard.classList.toggle('hidden');
            }
        }

        function openStayEditModal(prop) {
            const modal = document.getElementById('stay-edit-modal');
            const form = document.getElementById('stay-edit-form');
            form.action = `/receptionist/stays/${prop.id}`;

            document.getElementById('edit-stay-title').value = prop.title || '';
            document.getElementById('edit-stay-price').value = prop.price || '';
            document.getElementById('edit-stay-cleaning-fee').value = prop.cleaning_fee || 0;
            document.getElementById('edit-stay-service-fee').value = prop.service_fee !== null && prop.service_fee !== undefined ? prop.service_fee : '';
            document.getElementById('edit-stay-checkin-time').value = prop.check_in_time || '14:00';
            document.getElementById('edit-stay-checkout-time').value = prop.check_out_time || '11:00';
            document.getElementById('edit-stay-house-rules').value = prop.house_rules || '';
            document.getElementById('edit-stay-description').value = prop.description || '';
            document.getElementById('edit-stay-is-available').value = prop.is_available ? '1' : '0';
            document.getElementById('edit-stay-available-from').value = prop.available_from ? prop.available_from.substring(0, 10) : '';
            document.getElementById('edit-stay-available-to').value = prop.available_to ? prop.available_to.substring(0, 10) : '';

            modal.classList.remove('hidden');
        }

        function closeStayEditModal() {
            document.getElementById('stay-edit-modal').classList.add('hidden');
        }

        // Receptionist Theme Palette Presets & Switcher
        const RECEPTIONIST_PALETTES = {
            emerald: {
                name: 'Highland Emerald',
                primary: '#1d3c34', primary_hover: '#254d43', primary_dark: '#102b25', primary_light: '#dfeee4',
                secondary: '#d9cab3', secondary_light: '#e7ddca', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#f9ecd0',
                site_bg: '#f5f0e7', card_bg: '#fdfaf5', text_dark: '#1d3c34', text_muted: '#587165',
                primary_rgb: '29, 60, 52', primary_dark_rgb: '16, 43, 37', primary_light_rgb: '223, 238, 228', secondary_rgb: '217, 202, 179', accent_rgb: '212, 175, 55', accent_light_rgb: '249, 236, 208'
            },
            oak: {
                name: 'Oak Wood',
                primary: '#5c3a21', primary_hover: '#73492a', primary_dark: '#3d2514', primary_light: '#f5ede4',
                secondary: '#dfd3c3', secondary_light: '#eee6dc', accent: '#c2883f', accent_dark: '#966324', accent_light: '#fbf1e2',
                site_bg: '#f7f2ea', card_bg: '#fffdfa', text_dark: '#3d2514', text_muted: '#786252',
                primary_rgb: '92, 58, 33', primary_dark_rgb: '61, 37, 20', primary_light_rgb: '245, 237, 228', secondary_rgb: '223, 211, 195', accent_rgb: '194, 136, 63', accent_light_rgb: '251, 241, 226'
            },
            cream: {
                name: 'Soft Cream',
                primary: '#7c5e43', primary_hover: '#926f50', primary_dark: '#563e2a', primary_light: '#f9f5ee',
                secondary: '#e6dcce', secondary_light: '#f2ede4', accent: '#c59d5f', accent_dark: '#9e7a40', accent_light: '#fcf6ec',
                site_bg: '#faf7f2', card_bg: '#ffffff', text_dark: '#3d2e22', text_muted: '#877464',
                primary_rgb: '124, 94, 67', primary_dark_rgb: '86, 62, 42', primary_light_rgb: '249, 245, 238', secondary_rgb: '230, 220, 206', accent_rgb: '197, 157, 95', accent_light_rgb: '252, 246, 236'
            },
            sapphire: {
                name: 'Sapphire Navy',
                primary: '#1e3a5f', primary_hover: '#2b5282', primary_dark: '#0f1f33', primary_light: '#dbeafe',
                secondary: '#cbd5e1', secondary_light: '#e2e8f0', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#fef3c7',
                site_bg: '#f1f5f9', card_bg: '#ffffff', text_dark: '#0f172a', text_muted: '#475569',
                primary_rgb: '30, 58, 95', primary_dark_rgb: '15, 31, 51', primary_light_rgb: '219, 234, 254', secondary_rgb: '203, 213, 225', accent_rgb: '212, 175, 55', accent_light_rgb: '254, 243, 199'
            },
            bordeaux: {
                name: 'Bordeaux Velvet',
                primary: '#4c1d24', primary_hover: '#632530', primary_dark: '#2d0f14', primary_light: '#fae8eb',
                secondary: '#d9c7c9', secondary_light: '#ece1e3', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#fef3c7',
                site_bg: '#fbf7f7', card_bg: '#ffffff', text_dark: '#260a0f', text_muted: '#6b4c52',
                primary_rgb: '76, 29, 36', primary_dark_rgb: '45, 15, 20', primary_light_rgb: '250, 232, 235', secondary_rgb: '217, 199, 201', accent_rgb: '212, 175, 55', accent_light_rgb: '254, 243, 199'
            },
            obsidian: {
                name: 'Obsidian Slate',
                primary: '#18181b', primary_hover: '#27272a', primary_dark: '#09090b', primary_light: '#e4e4e7',
                secondary: '#cbd5e1', secondary_light: '#e2e8f0', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#fef3c7',
                site_bg: '#f8fafc', card_bg: '#ffffff', text_dark: '#09090b', text_muted: '#64748b',
                primary_rgb: '24, 24, 27', primary_dark_rgb: '9, 9, 11', primary_light_rgb: '228, 228, 231', secondary_rgb: '203, 213, 225', accent_rgb: '212, 175, 55', accent_light_rgb: '254, 243, 199'
            }
        };

        function togglePaletteMenu() {
            const dropdown = document.getElementById('palette-switcher-dropdown');
            if (dropdown) dropdown.classList.toggle('hidden');
        }

        function toggleAlertsDropdown() {
            const dropdown = document.getElementById('dashboard-messages-dropdown');
            if (dropdown) dropdown.classList.toggle('hidden');
        }

        function applyReceptionistPalette(key) {
            const p = RECEPTIONIST_PALETTES[key] || RECEPTIONIST_PALETTES.emerald;
            const root = document.documentElement;
            for (const [prop, val] of Object.entries(p)) {
                if (prop !== 'name') {
                    root.style.setProperty('--theme-' + prop.replace(/_/g, '-'), val);
                }
            }
            try {
                localStorage.setItem('gtm_receptionist_palette', key);
            } catch (e) {}

            const labelEl = document.getElementById('active-palette-name');
            if (labelEl) labelEl.textContent = p.name;
        }

        // Initialize active palette label on load
        (function() {
            const saved = localStorage.getItem('gtm_receptionist_palette') || 'emerald';
            if (RECEPTIONIST_PALETTES[saved]) {
                const labelEl = document.getElementById('active-palette-name');
                if (labelEl) labelEl.textContent = RECEPTIONIST_PALETTES[saved].name;
            }
        })();

        // Click outside dropdowns to close
        document.addEventListener('click', function(event) {
            const paletteWrapper = document.getElementById('palette-switcher-wrapper');
            const paletteDropdown = document.getElementById('palette-switcher-dropdown');
            if (paletteWrapper && paletteDropdown && !paletteWrapper.contains(event.target)) {
                paletteDropdown.classList.add('hidden');
            }

            const alertsWrapper = document.getElementById('dashboard-messages-icon-wrapper');
            const alertsDropdown = document.getElementById('dashboard-messages-dropdown');
            if (alertsWrapper && alertsDropdown && !alertsWrapper.contains(event.target)) {
                alertsDropdown.classList.add('hidden');
            }
        });
    </script>
    @include('layouts.partials.sidebar-scroll-preserver')
</body>
</html>
