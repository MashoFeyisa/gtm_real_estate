<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Audit &amp; Tax Desk — {{ $siteBrand['name'] }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @php
        $financeTheme = [
            'primary' => '#1d3c34',             // Classic Highland Emerald
            'primary_hover' => '#254d43',
            'primary_dark' => '#102b25',
            'primary_light' => '#dfeee4',
            'secondary' => '#d9cab3',
            'secondary_light' => '#e7ddca',
            'accent' => '#d4af37',              // Imperial Gold
            'accent_dark' => '#9b6c17',
            'accent_light' => '#f9ecd0',
            'site_bg' => '#f5f0e7',
            'card_bg' => '#ffffff',
            'text_dark' => '#1d3c34',
            'text_muted' => '#587165',
            'primary_rgb' => '29, 60, 52',
            'primary_dark_rgb' => '16, 43, 37',
            'primary_light_rgb' => '223, 238, 228',
            'secondary_rgb' => '217, 202, 179',
            'accent_rgb' => '212, 175, 55',
            'accent_light_rgb' => '249, 236, 208',
        ];
    @endphp
    @include('layouts.partials.theme-styles', ['customTheme' => $financeTheme])
    <script>
        (function() {
            try {
                const saved = localStorage.getItem('gtm_finance_palette');
                const palettes = {
                    emerald: {
                        primary: '#1d3c34', primary_hover: '#254d43', primary_dark: '#102b25', primary_light: '#dfeee4',
                        secondary: '#d9cab3', secondary_light: '#e7ddca', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#f9ecd0',
                        site_bg: '#f5f0e7', card_bg: '#ffffff', text_dark: '#1d3c34', text_muted: '#587165',
                        primary_rgb: '29, 60, 52', primary_dark_rgb: '16, 43, 37', primary_light_rgb: '223, 238, 228', secondary_rgb: '217, 202, 179', accent_rgb: '212, 175, 55', accent_light_rgb: '249, 236, 208'
                    },
                    sapphire: {
                        primary: '#1e3a5f', primary_hover: '#2b5282', primary_dark: '#0f1f33', primary_light: '#dbeafe',
                        secondary: '#cbd5e1', secondary_light: '#e2e8f0', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#fef3c7',
                        site_bg: '#f1f5f9', card_bg: '#ffffff', text_dark: '#0f172a', text_muted: '#475569',
                        primary_rgb: '30, 58, 95', primary_dark_rgb: '15, 31, 51', primary_light_rgb: '219, 234, 254', secondary_rgb: '203, 213, 225', accent_rgb: '212, 175, 55', accent_light_rgb: '254, 243, 199'
                    },
                    obsidian: {
                        primary: '#18181b', primary_hover: '#27272a', primary_dark: '#09090b', primary_light: '#e4e4e7',
                        secondary: '#cbd5e1', secondary_light: '#e2e8f0', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#fef3c7',
                        site_bg: '#f8fafc', card_bg: '#ffffff', text_dark: '#09090b', text_muted: '#64748b',
                        primary_rgb: '24, 24, 27', primary_dark_rgb: '9, 9, 11', primary_light_rgb: '228, 228, 231', secondary_rgb: '203, 213, 225', accent_rgb: '212, 175, 55', accent_light_rgb: '254, 243, 199'
                    },
                    bordeaux: {
                        primary: '#4c1d24', primary_hover: '#632530', primary_dark: '#2d0f14', primary_light: '#fae8eb',
                        secondary: '#d9c7c9', secondary_light: '#ece1e3', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#fef3c7',
                        site_bg: '#fbf7f7', card_bg: '#ffffff', text_dark: '#260a0f', text_muted: '#6b4c52',
                        primary_rgb: '76, 29, 36', primary_dark_rgb: '45, 15, 20', primary_light_rgb: '250, 232, 235', secondary_rgb: '217, 199, 201', accent_rgb: '212, 175, 55', accent_light_rgb: '254, 243, 199'
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
        $roleLabel = $isAdmin ? 'Administrator' : ($isFinanceOfficer ? 'Finance Officer' : 'Agent');
        $pendingCommissionCount = (int) $stats['pending_commissions_count'];
        $overdueSchedules = $rentSchedules->filter(fn ($schedule) => $schedule->is_overdue);
        $overdueInstallmentCount = $overdueSchedules->count();
        $activeLeaseCount = $leases->where('status', 'active')->count();
        $collectedRent = (float) $rentSchedules->where('status', 'paid')->sum('amount_paid');
        $paidInstallmentCount = $rentSchedules->where('status', 'paid')->count();
        $scheduledRent = (float) $rentSchedules->sum('amount_due');
        $outstandingRent = max(0, $scheduledRent - $collectedRent);
        $isFinanceStaff = $isAdmin || $isFinanceOfficer;
        $pendingShiftsCount = isset($frontDeskShifts) ? $frontDeskShifts->where('status', 'submitted')->count() : 0;

        // Permission-driven desk: navigation, sections and capability cards follow the viewer's grants.
        $canManagePayments = auth()->user()->hasPermission('payments.manage');
        $canManageCommissions = auth()->user()->hasPermission('commissions.manage');
        $canReconcileAccounts = auth()->user()->hasPermission('reconciliation.manage');
    @endphp

    <div class="flex min-h-screen bg-[#f5f0e7]">
        {{-- Desktop Vertical Sidebar --}}
        <aside id="finance-sidebar" class="dashboard-sidebar sidebar-scroll-area sticky top-0 h-[100dvh] w-72 shrink-0 border-r border-[#d9cab3] bg-[#1d3c34] p-6 text-[#f8f3eb] hidden lg:flex flex-col justify-between overflow-y-auto" style="position: sticky; top: 0; height: 100dvh; overflow-y: auto;">
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
                    <p class="text-sm font-black text-white truncate">Financial Audit &amp; Tax Desk</p>
                    <p class="text-[11px] text-[#dfeee4]/80">👤 {{ auth()->user()->name }} ({{ $roleLabel }})</p>
                </div>

                <nav class="mt-6 space-y-1.5 min-h-[420px]" aria-label="Finance desk navigation">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span>🏠</span>
                        <span>Home</span>
                    </a>
                    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center justify-between rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
                        <span class="flex items-center gap-2.5">
                            <span>📊</span>
                            <span>Overview</span>
                        </span>
                    </a>
                    @if ($canManagePayments)
                    <a href="#payment-queue" data-target-section="payment-queue" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>💳</span>
                            <span>Payments to Confirm</span>
                        </span>
                        @if ($pendingPayments->isNotEmpty())
                            <span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-xs font-bold text-[#9b6c17]">{{ $pendingPayments->count() }}</span>
                        @endif
                    </a>
                    @endif
                    <a href="#lease-rental" data-target-section="lease-rental" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>📑</span>
                            <span>Manage Rental &amp; Lease</span>
                        </span>
                        @if ($overdueInstallmentCount > 0)
                            <span class="rounded-full bg-rose-500 px-2 py-0.5 text-xs font-black text-white">{{ $overdueInstallmentCount }} overdue</span>
                        @elseif ($activeLeaseCount > 0)
                            <span class="rounded-full bg-[#102b25] border border-[#d4af37]/40 px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">{{ $activeLeaseCount }} active</span>
                        @endif
                    </a>
                    @if ($canManageCommissions)
                    <a href="#agent-commission" data-target-section="agent-commission" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>💼</span>
                            <span>Receive Commission</span>
                        </span>
                        @if ($pendingCommissionCount > 0)
                            <span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-xs font-bold text-[#9b6c17]">{{ $pendingCommissionCount }}</span>
                        @endif
                    </a>
                    @endif
                    @if ($canReconcileAccounts)
                    <a href="#double-entry" data-target-section="double-entry" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>⚖️</span>
                            <span>Double-Entry Ledgers</span>
                        </span>
                    </a>
                    @endif
                    <a href="#front-desk-shifts" data-target-section="front-desk-shifts" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>🕒</span>
                            <span>Front Desk Shifts</span>
                        </span>
                        @if ($pendingShiftsCount > 0)
                            <span class="rounded-full bg-amber-400 px-2 py-0.5 text-xs font-black text-[#102b25]">{{ $pendingShiftsCount }} new</span>
                        @endif
                    </a>
                    <a href="#revenue-breakdown" data-target-section="revenue-breakdown" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>📈</span>
                            <span>Revenue Breakdown</span>
                        </span>
                    </a>
                    <a href="#erca-tax" data-target-section="erca-tax" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>🏛️</span>
                            <span>Tax for ERCA</span>
                        </span>
                        <span class="rounded-full bg-[#102b25] border border-[#d4af37]/40 px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">VAT &amp; WHT</span>
                    </a>
                    <a href="#profile" data-target-section="profile" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>👤</span>
                            <span>My Staff Profile</span>
                        </span>
                    </a>

                    @if ($isAdmin)
                        <div class="pt-3 border-t border-white/10 space-y-1">
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-bold text-[#d4af37] transition hover:bg-white/10">
                                <span>👑</span>
                                <span>Admin Dashboard</span>
                            </a>
                            <a href="{{ route('receptionist.dashboard') }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-bold text-[#dfeee4] transition hover:bg-white/10">
                                <span>🛎️</span>
                                <span>Front Desk Reception</span>
                            </a>
                        </div>
                    @elseif (! $isFinanceOfficer)
                        <div class="pt-3 border-t border-white/10">
                            <a href="{{ route('agent.portal') }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-bold text-[#d4af37] transition hover:bg-white/10">
                                <span>🏢</span>
                                <span>Agent Portal</span>
                            </a>
                        </div>
                    @endif

                    @include('layouts.partials.authorized-sidebar-links')
                </nav>
            </div>

            <div>
                {{-- Desk Color Palette Switcher --}}
                <div class="mt-6 pt-4 border-t border-white/10">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#d4af37] mb-2 flex items-center gap-1.5">
                        <span>🎨</span>
                        <span>Desk Color</span>
                    </p>
                    <div class="relative">
                        <button type="button" id="palette-switcher-btn" onclick="togglePaletteMenu()" class="w-full flex items-center justify-between rounded-xl border border-white/15 bg-white/10 px-3 py-2 text-xs font-bold text-white transition hover:bg-white/20 cursor-pointer shadow-xs" title="Switch Desk Color Palette">
                            <span class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-[#d4af37]"></span>
                                <span id="active-palette-name">Highland Emerald</span>
                            </span>
                            <svg class="h-3.5 w-3.5 text-[#d4af37]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div id="palette-switcher-dropdown" class="hidden absolute left-0 bottom-full z-50 mb-2 w-full overflow-hidden rounded-2xl border border-[#d9cab3] bg-white p-2 shadow-2xl space-y-1">
                            <p class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-slate-400">Desk Color Palette</p>
                            <button type="button" onclick="applyFinancePalette('emerald'); togglePaletteMenu();" class="w-full flex items-center justify-between rounded-xl px-2.5 py-2 text-xs font-bold text-[#1d3c34] hover:bg-[#dfeee4] transition text-left cursor-pointer">
                                <span class="flex items-center gap-2">
                                    <span class="h-3.5 w-3.5 rounded-full bg-[#1d3c34] border border-white shadow-xs"></span>
                                    <span>Highland Emerald</span>
                                </span>
                                <span class="text-[10px] font-semibold text-slate-400">Default</span>
                            </button>
                            <button type="button" onclick="applyFinancePalette('sapphire'); togglePaletteMenu();" class="w-full flex items-center justify-between rounded-xl px-2.5 py-2 text-xs font-bold text-[#1e3a5f] hover:bg-[#dbeafe] transition text-left cursor-pointer">
                                <span class="flex items-center gap-2">
                                    <span class="h-3.5 w-3.5 rounded-full bg-[#1e3a5f] border border-white shadow-xs"></span>
                                    <span>Sapphire Navy</span>
                                </span>
                                <span class="text-[10px] font-semibold text-slate-400">Executive</span>
                            </button>
                            <button type="button" onclick="applyFinancePalette('bordeaux'); togglePaletteMenu();" class="w-full flex items-center justify-between rounded-xl px-2.5 py-2 text-xs font-bold text-[#4c1d24] hover:bg-[#fae8eb] transition text-left cursor-pointer">
                                <span class="flex items-center gap-2">
                                    <span class="h-3.5 w-3.5 rounded-full bg-[#4c1d24] border border-white shadow-xs"></span>
                                    <span>Bordeaux Velvet</span>
                                </span>
                                <span class="text-[10px] font-semibold text-slate-400">Luxury</span>
                            </button>
                            <button type="button" onclick="applyFinancePalette('obsidian'); togglePaletteMenu();" class="w-full flex items-center justify-between rounded-xl px-2.5 py-2 text-xs font-bold text-[#18181b] hover:bg-slate-100 transition text-left cursor-pointer">
                                <span class="flex items-center gap-2">
                                    <span class="h-3.5 w-3.5 rounded-full bg-[#18181b] border border-white shadow-xs"></span>
                                    <span>Obsidian Slate</span>
                                </span>
                                <span class="text-[10px] font-semibold text-slate-400">Modern</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Signed In User Box --}}
                @include('dashboard.partials.signed-in-user', ['roleLabel' => $roleLabel])
            </div>
        </aside>

        {{-- Mobile Sidebar Drawer --}}
        <div id="finance-mobile-drawer" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs lg:hidden">
            <div class="fixed inset-y-0 left-0 w-72 bg-[#1d3c34] p-6 text-[#f8f3eb] flex flex-col justify-between overflow-y-auto shadow-2xl">
                <div>
                    <div class="flex items-center justify-between">
                        <a href="{{ route('home') }}" class="flex items-center gap-3">
                            <img src="{{ $siteBrand['logoUrl'] }}" alt="{{ $siteBrand['name'] }} Logo" class="h-9 w-9 rounded-xl object-contain ring-2 ring-[#d9cab3] bg-white/10">
                            <span class="text-lg font-black tracking-tight text-white">{{ $siteBrand['name'] }}</span>
                        </a>
                        <button type="button" onclick="toggleMobileSidebar(false)" class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 text-white hover:bg-white/20 cursor-pointer text-xl leading-none">
                            &times;
                        </button>
                    </div>

                    <div class="mt-4 rounded-xl border border-white/10 bg-white/5 p-3">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Active Desk</p>
                        <p class="text-sm font-black text-white truncate">Financial Audit &amp; Tax Desk</p>
                        <p class="text-[11px] text-[#dfeee4]/80">👤 {{ auth()->user()->name }} ({{ $roleLabel }})</p>
                    </div>

                    <nav class="mt-6 space-y-1.5" aria-label="Mobile Finance desk navigation">
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                            <span>🏠</span>
                            <span>Home</span>
                        </a>
                        <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center justify-between rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold text-white">
                            <span class="flex items-center gap-2.5">
                                <span>📊</span>
                                <span>Overview</span>
                            </span>
                        </a>
                        @if ($canManagePayments)
                        <a href="#payment-queue" data-target-section="payment-queue" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                            <span class="flex items-center gap-2.5">
                                <span>💳</span>
                                <span>Payments to Confirm</span>
                            </span>
                            @if ($pendingPayments->isNotEmpty())
                                <span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-xs font-bold text-[#9b6c17]">{{ $pendingPayments->count() }}</span>
                            @endif
                        </a>
                        @endif
                        <a href="#lease-rental" data-target-section="lease-rental" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                            <span class="flex items-center gap-2.5">
                                <span>📑</span>
                                <span>Manage Rental &amp; Lease</span>
                            </span>
                            @if ($overdueInstallmentCount > 0)
                                <span class="rounded-full bg-rose-500 px-2 py-0.5 text-xs font-black text-white">{{ $overdueInstallmentCount }} overdue</span>
                            @elseif ($activeLeaseCount > 0)
                                <span class="rounded-full bg-[#102b25] border border-[#d4af37]/40 px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">{{ $activeLeaseCount }} active</span>
                            @endif
                        </a>
                        @if ($canManageCommissions)
                        <a href="#agent-commission" data-target-section="agent-commission" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                            <span class="flex items-center gap-2.5">
                                <span>💼</span>
                                <span>Receive Commission</span>
                            </span>
                            @if ($pendingCommissionCount > 0)
                                <span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-xs font-bold text-[#9b6c17]">{{ $pendingCommissionCount }}</span>
                            @endif
                        </a>
                        @endif
                        @if ($canReconcileAccounts)
                        <a href="#double-entry" data-target-section="double-entry" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                            <span class="flex items-center gap-2.5">
                                <span>⚖️</span>
                                <span>Double-Entry Ledgers</span>
                            </span>
                        </a>
                        @endif
                        <a href="#front-desk-shifts" data-target-section="front-desk-shifts" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                            <span class="flex items-center gap-2.5">
                                <span>🕒</span>
                                <span>Front Desk Shifts</span>
                            </span>
                            @if ($pendingShiftsCount > 0)
                                <span class="rounded-full bg-amber-400 px-2 py-0.5 text-xs font-black text-[#102b25]">{{ $pendingShiftsCount }} new</span>
                            @endif
                        </a>
                        <a href="#revenue-breakdown" data-target-section="revenue-breakdown" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                            <span class="flex items-center gap-2.5">
                                <span>📈</span>
                                <span>Revenue Breakdown</span>
                            </span>
                        </a>
                        <a href="#erca-tax" data-target-section="erca-tax" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                            <span class="flex items-center gap-2.5">
                                <span>🏛️</span>
                                <span>Tax for ERCA</span>
                            </span>
                            <span class="rounded-full bg-[#102b25] border border-[#d4af37]/40 px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">VAT &amp; WHT</span>
                        </a>
                        <a href="#profile" data-target-section="profile" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                            <span class="flex items-center gap-2.5">
                                <span>👤</span>
                                <span>My Staff Profile</span>
                            </span>
                        </a>
                    </nav>
                </div>

                <div class="mt-6 border-t border-white/10 pt-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-[#f8f3eb] px-3 py-2 text-xs font-bold text-[#1d3c34] transition hover:bg-white cursor-pointer">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Main Content Column --}}
        <div class="flex-1 min-w-0 flex flex-col">
            {{-- Top Sticky Header Bar --}}
            <header class="sticky top-0 z-40 border-b border-[#d9cab3] bg-[#fffdf8]/95 px-4 py-3 sm:px-6 lg:px-8 shadow-xs backdrop-blur-md">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        {{-- Mobile Menu Toggle --}}
                        <button type="button" id="finance-mobile-menu-btn" onclick="toggleMobileSidebar(true)" class="lg:hidden flex h-10 w-10 items-center justify-center rounded-xl border border-[#d9cab3] bg-white text-[#1d3c34] shadow-xs cursor-pointer" aria-label="Toggle Navigation">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="block text-xs font-bold uppercase tracking-[0.16em] text-[#8c6b25]">Financial Audit &amp; Tax Desk</span>
                                <span class="rounded-full bg-[#102b25] px-2 py-0.5 text-[9px] font-black uppercase text-[#d4af37] border border-[#d4af37]/40 hidden sm:inline-block">
                                    ERCA Tax &bull; IFRS Ledger
                                </span>
                            </div>
                            <h1 class="text-base sm:text-xl font-black leading-tight text-[#1d3c34]">Financial Reporting & Commission Settlement</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        @if ($isFinanceStaff)
                            <a href="{{ route('finance.export-csv') }}" class="hidden sm:inline-flex items-center gap-1.5 rounded-full border border-[#cdbb97] bg-white px-3.5 py-1.5 text-xs font-bold text-[#1d3c34] transition hover:bg-[#f1e4cf] shadow-xs" title="Export Financial Ledger CSV">
                                <span>📥 CSV</span>
                            </a>
                            <a href="{{ route('finance.statement', ['period' => $dateFilter]) }}" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#102b25] px-3.5 py-1.5 text-xs font-bold text-[#d4af37] transition hover:bg-[#1d3c34] shadow-xs" title="Print Official Audit Statement">
                                <span>🖨️ Statement</span>
                            </a>
                            @if ($isAdmin)
                                <a href="{{ route('dashboard') }}#users" class="hidden md:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-[#254d43] shadow-xs">
                                    <span>👤 Add Staff</span>
                                </a>
                            @endif
                        @endif

                        {{-- Profile Avatar Link to #profile --}}
                        <a href="#profile" data-target-section="profile" class="nav-section-link group relative inline-flex items-center justify-center rounded-full p-0.5 transition ring-2 ring-[#cdbb97] hover:ring-[#1d3c34] focus:outline-none focus:ring-4 focus:ring-[#1d3c34]/30 shadow-xs cursor-pointer" title="Edit Profile ({{ auth()->user()->name ?? 'Finance Officer' }})">
                            @if (auth()->user()->photo_url)
                                <img src="{{ auth()->user()->photo_url }}" alt="{{ auth()->user()->name }}" class="h-9 w-9 rounded-full object-cover group-hover:brightness-95 transition">
                            @else
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#1d3c34] text-xs font-black text-[#e4c76f] group-hover:bg-[#254d43] transition">
                                    {{ auth()->user()->initials ?: 'FO' }}
                                </span>
                            @endif
                            <span class="sr-only">Edit Profile</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-full border border-[#cdbb97] bg-white px-3.5 py-1.5 text-xs font-bold text-[#1d3c34] transition hover:bg-[#f1e4cf] shadow-xs cursor-pointer">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
                {{-- Flash Notifications --}}
                @if (session('success'))
                    <div class="mb-6 flex items-center justify-between rounded-2xl border border-[#d7e5d2] bg-[#edf9ee] px-4 py-3.5 text-sm font-medium text-[#214f3a] shadow-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">✅</span>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-[#214f3a] hover:opacity-75 font-bold cursor-pointer">&times;</button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 flex items-center justify-between rounded-2xl border border-[#c86b5c] bg-[#fef3f1] px-4 py-3.5 text-sm font-medium text-[#a24339] shadow-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">⚠️</span>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-[#a24339] hover:opacity-75 font-bold cursor-pointer">&times;</button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-[#c86b5c] bg-[#fef3f1] px-4 py-3.5 text-sm font-medium text-[#a24339] shadow-xs">
                        <p class="font-bold mb-1">Please correct the following:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Period Filter & Reporting Actions --}}
                <div class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-[1.75rem] border border-[#d9cab3] bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-2 overflow-x-auto text-xs">
                        <span class="mr-1 text-[10px] font-bold uppercase text-slate-400">Period:</span>
                        @foreach (['this_month' => 'This Month', 'last_month' => 'Last Month', 'this_quarter' => 'This Quarter', 'this_year' => 'This Year', 'all_time' => 'All Time'] as $key => $label)
                            <a href="{{ route('finance.index', ['period' => $key]) }}" class="whitespace-nowrap rounded-xl px-3 py-1.5 font-bold transition {{ $dateFilter === $key ? 'bg-[#102b25] text-[#d4af37]' : 'bg-[#f9f4ed] text-slate-700 hover:bg-[#ebdcc9]' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <form method="GET" action="{{ route('finance.index') }}" class="flex items-center gap-2 text-xs">
                            <input type="hidden" name="period" value="custom">
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="rounded-xl border border-[#d9cab3] bg-white px-2.5 py-1 text-xs">
                            <span class="text-slate-400">to</span>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="rounded-xl border border-[#d9cab3] bg-white px-2.5 py-1 text-xs">
                            <button type="submit" class="rounded-xl bg-[#102b25] px-3 py-1.5 font-bold text-xs text-white hover:bg-[#1d3c34] cursor-pointer">Filter</button>
                        </form>

                        @if ($isFinanceStaff)
                            <a href="{{ route('finance.export-csv') }}" class="sm:hidden rounded-full border border-[#cdbb97] bg-white px-3 py-1 text-xs font-semibold text-[#1d3c34]">
                                📥 CSV
                            </a>
                            <a href="{{ route('finance.statement', ['period' => $dateFilter]) }}" target="_blank" class="sm:hidden rounded-full bg-[#102b25] px-3 py-1 text-xs font-semibold text-[#d4af37]">
                                🖨️ Print
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Overview Panel --}}
                <section id="overview" data-section="overview" class="section-panel scroll-mt-24">
                @if (isset($actionableTasks) && count($actionableTasks) > 0)
                    <div class="mb-6 rounded-2xl border border-[#d4af37]/60 bg-gradient-to-r from-[#1d3c34] to-[#254d43] p-5 text-white shadow-md">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="rounded bg-[#d4af37] px-2 py-0.5 text-[10px] font-black uppercase text-[#1d3c34]">🤖 Finance AI Assistant</span>
                            <span class="text-xs font-bold text-[#dfeee4]">Action Required</span>
                        </div>
                        <h3 class="text-base font-black text-white mb-3">You have {{ count($actionableTasks) }} pending action items requiring financial verification:</h3>
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

                @if ($canManageCommissions && $pendingCommissionCount > 0)
                    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border-2 border-amber-400 bg-gradient-to-r from-[#fffdf5] via-[#fefbf0] to-[#fcf6e8] p-5 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-xl font-black text-white shadow-xs">💼</span>
                            <div>
                                <h2 class="text-base font-black text-[#102b25]">{{ $pendingCommissionCount }} Agent Commission Payout(s) Awaiting Settlement</h2>
                                <p class="mt-0.5 text-xs text-slate-600">Totalling {{ number_format($stats['total_pending_commissions'], 2) }} ETB before 2% withholding tax deductions.</p>
                            </div>
                        </div>
                        <a href="#agent-commission" data-target-section="agent-commission" class="nav-section-link inline-flex items-center gap-2 rounded-full bg-[#102b25] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#d4af37] transition hover:bg-[#1d3c34] shadow-sm cursor-pointer">
                            <span>Open Commission Desk &rarr;</span>
                        </a>
                    </div>
                @endif

                @if ($canManagePayments && $pendingPayments->isNotEmpty())
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-4 rounded-2xl border-2 border-amber-400 bg-gradient-to-r from-[#fffdf5] via-[#fefbf0] to-[#fcf6e8] p-5 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-xl font-black text-white shadow-xs">💳</span>
                            <div>
                                <h2 class="text-base font-black text-[#102b25]">{{ $pendingPayments->count() }} Payment(s) Awaiting Confirmation</h2>
                                <p class="mt-0.5 text-xs text-slate-600">Totalling {{ number_format($pendingPayments->sum('amount'), 2) }} ETB. Short-stay reservations stay unconfirmed until the payment is verified and a receipt is issued.</p>
                            </div>
                        </div>
                        <a href="#payment-queue" data-target-section="payment-queue" class="nav-section-link inline-flex items-center gap-2 rounded-full bg-[#102b25] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#d4af37] transition hover:bg-[#1d3c34] shadow-sm cursor-pointer">
                            <span>Open Confirmation Queue &rarr;</span>
                        </a>
                    </div>
                @endif



                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    <div class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between text-[#587165]">
                            <p class="text-sm font-medium">Gross Inflows (Credits)</p>
                            <span class="text-xl">📈</span>
                        </div>
                        <p class="mt-3 text-2xl font-black text-emerald-700">{{ number_format($stats['gross_inflow'], 2) }} ETB</p>
                        <p class="mt-1 text-[11px] font-semibold text-slate-500">Rent, earnest money, stays and unit deposits</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between text-[#587165]">
                            <p class="text-sm font-medium">Outflows &amp; Disbursements</p>
                            <span class="text-xl">📉</span>
                        </div>
                        <p class="mt-3 text-2xl font-black text-rose-700">{{ number_format($stats['gross_outflow'], 2) }} ETB</p>
                        <p class="mt-1 text-[11px] font-semibold text-slate-500">Agent commissions and platform payouts</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#edf3ee] p-5 shadow-sm">
                        <div class="flex items-center justify-between text-[#587165]">
                            <p class="text-sm font-medium">Net Retained Balance</p>
                            <span class="text-xl">🏦</span>
                        </div>
                        <p class="mt-3 text-2xl font-black text-[#2d5d4d]">{{ number_format($stats['net_retained'], 2) }} ETB</p>
                        <p class="mt-1 text-[11px] font-semibold text-slate-500">Platform operating income</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#f4efe7] p-5 shadow-sm">
                        <div class="flex items-center justify-between text-[#587165]">
                            <p class="text-sm font-medium">Agency Retained Comm.</p>
                            <span class="text-xl">🤝</span>
                        </div>
                        <p class="mt-3 text-2xl font-black text-[#2d5d4d]">{{ number_format($stats['company_retained_commissions'] ?? 0, 2) }} ETB</p>
                        <p class="mt-1 text-[11px] font-semibold text-slate-500">{{ $stats['co_brokered_deals_count'] ?? 0 }} co-brokered / multi-tier deals</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-[#d4af37]/40 bg-[#fffdfa] p-5 shadow-sm">
                        <div class="flex items-center justify-between text-[#587165]">
                            <p class="text-sm font-medium">ERCA Tax Compliance</p>
                            <span class="text-xl">🏛️</span>
                        </div>
                        <p class="mt-3 text-2xl font-black text-[#9b6c17]">{{ number_format($stats['vat_accrued'] + $stats['total_withholding_tax'], 2) }} ETB</p>
                        <div class="mt-1 flex justify-between text-[11px] font-semibold text-slate-500">
                            <span>15% VAT: <strong>{{ number_format($stats['vat_accrued'], 2) }}</strong></span>
                            <span>2% WHT: <strong>{{ number_format($stats['total_withholding_tax'], 2) }}</strong></span>
                        </div>
                    </div>
                </div>

                {{-- Financial Overview Visualizations --}}
                <div class="mt-8 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-[#102b25]"></span>
                                <h2 class="text-xl font-black text-[#1d3c34]">Financial Analytics &amp; Capital Flow Intelligence</h2>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Real ledger cash inflows, revenue streams, and lease rent collection performance.</p>
                        </div>
                        <span class="rounded-full bg-[#ebdcc9] px-3 py-1 text-xs font-bold text-[#1d3c34]">
                            Audited Ledger
                        </span>
                    </div>

                    @if (!empty($financeCharts))
                    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Chart 1: Cashflow Inflow vs Outflow -->
                        <div class="rounded-2xl border border-slate-100 bg-[#fbf9f5] p-4 lg:col-span-2">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xs font-black uppercase tracking-wider text-[#1d3c34]">Monthly Cash Inflow vs. Outflow (ETB)</h3>
                                <span class="text-[10px] font-bold text-slate-400">Past 6 Months</span>
                            </div>
                            <div id="finance-cashflow-chart" class="min-h-[280px]"></div>
                        </div>

                        <!-- Chart 2: Revenue Categories Streams -->
                        <div class="rounded-2xl border border-slate-100 bg-[#fbf9f5] p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xs font-black uppercase tracking-wider text-[#1d3c34]">Revenue Stream Mix</h3>
                                <span class="text-[10px] font-bold text-slate-400">By Ledger Category</span>
                            </div>
                            <div id="finance-categories-chart" class="min-h-[280px] flex items-center justify-center"></div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="mt-8 grid gap-6 lg:grid-cols-2">
                    <div class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-[#1d3c34]">Financial Overview</h2>
                        <p class="text-sm text-[#587165]">Summary of the current financial position and key metrics.</p>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-[#d9cab3] bg-[#f9f4ed] p-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Active Leases</span>
                                <p class="mt-1 font-mono text-xl font-black text-[#1d3c34]">{{ $activeLeaseCount }}</p>
                                <span class="text-[11px] text-slate-500">Of {{ $leases->count() }} total contracts</span>
                            </div>
                            <div class="rounded-2xl border border-[#d9cab3] bg-[#f9f4ed] p-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Overdue Installments</span>
                                <p class="mt-1 font-mono text-xl font-black text-rose-700">{{ $overdueInstallmentCount }}</p>
                                <span class="text-[11px] text-slate-500">Past grace period</span>
                            </div>
                            <div class="rounded-2xl border border-[#d9cab3] bg-[#f9f4ed] p-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rent Collected</span>
                                <p class="mt-1 font-mono text-xl font-black text-emerald-700">{{ number_format($collectedRent, 2) }} <span class="text-xs">ETB</span></p>
                                <span class="text-[11px] text-slate-500">Across all schedules</span>
                            </div>
                            <div class="rounded-2xl border border-[#d9cab3] bg-[#f9f4ed] p-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Scheduled Rent</span>
                                <p class="mt-1 font-mono text-xl font-black text-[#9b6c17]">{{ number_format($scheduledRent, 2) }} <span class="text-xs">ETB</span></p>
                                <span class="text-[11px] text-slate-500">Total contractual rent</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-[#1d3c34]">Financial Metrics</h2>
                        <p class="text-sm text-[#587165]">Key ledger movements for the selected period.</p>

                        <div class="mt-4 space-y-3">
                            <div class="flex items-center justify-between border-b border-[#f0e8d8] pb-3 text-sm text-slate-500">
                                <span>Gross Inflow</span>
                                <span class="font-black text-emerald-700">{{ number_format($stats['gross_inflow'], 2) }} ETB</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-[#f0e8d8] pb-3 text-sm text-slate-500">
                                <span>Gross Outflow</span>
                                <span class="font-black text-rose-700">{{ number_format($stats['gross_outflow'], 2) }} ETB</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-[#f0e8d8] pb-3 text-sm text-slate-500">
                                <span>Net Retained</span>
                                <span class="font-black text-[#1d3c34]">{{ number_format($stats['net_retained'], 2) }} ETB</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-[#f0e8d8] pb-3 text-sm text-slate-500">
                                <span>VAT Accrued (15%)</span>
                                <span class="font-black text-[#9b6c17]">{{ number_format($stats['vat_accrued'], 2) }} ETB</span>
                            </div>
                            <div class="flex items-center justify-between text-sm text-slate-500">
                                <span>WHT Deducted (2%)</span>
                                <span class="font-black text-[#9b6c17]">{{ number_format($stats['total_withholding_tax'], 2) }} ETB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Payments Awaiting Confirmation --}}
            @if ($canManagePayments)
            <section id="payment-queue" data-section="payment-queue" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-[#1d3c34]">Payments Awaiting Confirmation</h2>
                <p class="text-sm text-[#587165]">Verify bank transfers, Telebirr, CBE Birr, and deposited slips. Confirming a short-stay payment locks in the reservation and issues the guest's receipt automatically.</p>

                @if ($pendingPayments->isEmpty())
                    <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                        <span class="text-3xl">✅</span>
                        <p class="mt-2 text-sm font-semibold text-[#587165]">No payments are waiting for confirmation.</p>
                        <p class="mt-1 text-xs text-slate-400">New short-stay and installment payments appear here the moment a payer submits proof of payment.</p>
                    </div>
                @else
                    <div class="mt-5 overflow-x-auto rounded-2xl border border-[#e7ddca]">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase text-[#1d3c34]">
                                <tr>
                                    <th class="p-3">Transaction</th>
                                    <th class="p-3">Payer &amp; Channel</th>
                                    <th class="p-3">Category / Reservation</th>
                                    <th class="p-3 text-right">Amount (ETB)</th>
                                    <th class="p-3">Reference / Slip</th>
                                    <th class="p-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0e8d8] bg-white">
                                @foreach ($pendingPayments as $pendingPayment)
                                    @php $stayBooking = $pendingPayment->payable instanceof \App\Models\Booking ? $pendingPayment->payable : null; @endphp
                                    <tr class="hover:bg-[#f9f4ed]">
                                        <td class="p-3">
                                            <a href="{{ route('payments.receipt', $pendingPayment->transaction_number) }}" target="_blank" class="font-mono font-bold text-[#1d3c34] underline decoration-dotted">{{ $pendingPayment->transaction_number }}</a>
                                            <span class="mt-0.5 block text-[10px] text-slate-400">{{ $pendingPayment->created_at?->format('M d, Y H:i') }}</span>
                                        </td>
                                        <td class="p-3">
                                            <span class="block font-bold text-slate-800">{{ $pendingPayment->payer_name }}</span>
                                            <span class="mt-0.5 block text-[10px] text-slate-500">{{ $pendingPayment->payer_phone }} · {{ $pendingPayment->method_label }}</span>
                                        </td>
                                        <td class="p-3">
                                            <span class="font-semibold text-slate-800">{{ $pendingPayment->type_label }}</span>
                                            @if ($pendingPayment->property)
                                                <span class="mt-0.5 block text-[10px] text-slate-500">{{ Str::limit($pendingPayment->property->title, 34) }}</span>
                                            @endif
                                            @if ($stayBooking)
                                                <span class="mt-1 block font-mono text-[10px] font-bold text-[#9b6c17]">
                                                    {{ $stayBooking->booking_reference }}
                                                    · {{ $stayBooking->check_in_date?->format('M d') }} → {{ $stayBooking->check_out_date?->format('M d, Y') }}
                                                    · {{ $stayBooking->total_nights }} night(s)
                                                    @if ($stayBooking->payment_status === 'pending')
                                                        · {{ $stayBooking->payment_badge['label'] }}
                                                    @endif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-right">
                                            <span class="block font-mono font-black text-[#1d3c34]">{{ number_format($pendingPayment->amount, 2) }}</span>
                                            <span class="mt-0.5 block text-[10px] text-slate-400">Platform fee {{ number_format($pendingPayment->platform_fee, 2) }}</span>
                                        </td>
                                        <td class="p-3">
                                            <span class="font-mono text-[11px] text-slate-600">{{ $pendingPayment->bank_transaction_reference ?? '—' }}</span>
                                            @if ($pendingPayment->deposit_slip_path)
                                                <a href="{{ route('payments.slip', $pendingPayment) }}" target="_blank" class="mt-0.5 block text-[10px] font-bold text-[#1d3c34] underline">View Deposit Slip</a>
                                            @endif
                                        </td>
                                        <td class="p-3 text-right">
                                            <div class="flex flex-wrap items-center justify-end gap-1.5">
                                                <form method="POST" action="{{ route('payments.verify', $pendingPayment) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="cursor-pointer rounded-full bg-emerald-700 px-3 py-1 text-[11px] font-bold text-white transition hover:bg-emerald-800">✓ Confirm</button>
                                                </form>
                                                <form method="POST" action="{{ route('payments.reject', $pendingPayment) }}" onsubmit="return confirm('Reject this payment? The payer will be notified and can submit a new one.')">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="admin_notes" value="Rejected during finance desk verification.">
                                                    <button type="submit" class="cursor-pointer rounded-full bg-rose-600 px-3 py-1 text-[11px] font-bold text-white transition hover:bg-rose-700">✕ Reject</button>
                                                </form>
                                                <a href="{{ route('payments.receipt', $pendingPayment->transaction_number) }}" target="_blank" class="rounded-full border border-[#d9cab3] bg-white px-3 py-1 text-[11px] font-bold text-[#1d3c34] transition hover:bg-[#f1e4cf]">Receipt</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-3 text-[11px] text-[#587165]">Confirming sends the payer an in-app notification plus a receipt link at <span class="font-mono">/payments/receipt/{transaction}</span>. Rejecting re-opens the booking for a new payment.</p>
                @endif
            </section>
            @endif

            {{-- Revenue Breakdown Matrix --}}
            <section id="revenue-breakdown" data-section="revenue-breakdown" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-[#1d3c34]">Revenue Stream Breakdown (የገቢ ክፍፍል)</h2>
                <p class="text-sm text-[#587165]">Where the reported gross inflow for the selected period originated.</p>

                <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#f9f4ed] p-4 text-center">
                        <span class="text-xs font-bold text-slate-500">🏠 Rental Revenue</span>
                        <p class="mt-1 text-lg font-black text-[#1d3c34]">{{ number_format($stats['rental_revenue'], 2) }} ETB</p>
                    </div>

                    <div class="rounded-2xl border border-[#e7ddca] bg-[#f9f4ed] p-4 text-center">
                        <span class="text-xs font-bold text-slate-500">🤝 Sales Earnest Escrow</span>
                        <p class="mt-1 text-lg font-black text-[#1d3c34]">{{ number_format($stats['earnest_deposits'], 2) }} ETB</p>
                    </div>

                    <div class="rounded-2xl border border-[#e7ddca] bg-[#f9f4ed] p-4 text-center">
                        <span class="text-xs font-bold text-slate-500">🛏️ Stay Bookings</span>
                        <p class="mt-1 text-lg font-black text-[#1d3c34]">{{ number_format($stats['booking_revenue'], 2) }} ETB</p>
                    </div>

                    <div class="rounded-2xl border border-[#e7ddca] bg-[#f9f4ed] p-4 text-center">
                        <span class="text-xs font-bold text-slate-500">🏗️ Developer Off-Plan</span>
                        <p class="mt-1 text-lg font-black text-[#1d3c34]">{{ number_format($stats['developer_deposits'], 2) }} ETB</p>
                    </div>

                    <div class="rounded-2xl border border-[#e7ddca] bg-[#f9f4ed] p-4 text-center">
                        <span class="text-xs font-bold text-slate-500">💼 Platform Service Fees</span>
                        <p class="mt-1 text-lg font-black text-[#1d3c34]">{{ number_format($stats['platform_fees'], 2) }} ETB</p>
                    </div>
                </div>
            </section>

            {{-- Agent Commission Settlement Desk --}}
            @if ($canManageCommissions)
            <section id="agent-commission" data-section="agent-commission" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 border-b border-[#f0e8d8] pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-black text-[#1d3c34]">Agent Commission Settlement Desk</h2>
                        <p class="mt-0.5 text-xs text-[#587165]">
                            Audit, disburse, and generate Ethiopian Withholding Tax (2% WHT) payout vouchers for certified agents.
                        </p>
                    </div>
                    <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-bold text-[#9b6c17] whitespace-nowrap">
                        {{ $pendingCommissionCount }} Pending Payout(s): {{ number_format($stats['total_pending_commissions'], 2) }} ETB
                    </span>
                </div>

                @if ($commissions->isEmpty())
                    <div class="py-10 text-center text-slate-400">
                        <span class="text-3xl">💼</span>
                        <p class="mt-2 text-sm">No commission records registered yet.</p>
                    </div>
                @else
                    <div class="mt-5 overflow-x-auto rounded-2xl border border-[#e7ddca]">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase text-[#1d3c34]">
                                <tr>
                                    <th class="p-3">Agent &amp; Tier</th>
                                    <th class="p-3">Property &amp; Split</th>
                                    <th class="p-3">Deal Value</th>
                                    <th class="p-3">Gross Comm.</th>
                                    <th class="p-3">2% WHT</th>
                                    <th class="p-3">Net Payout</th>
                                    <th class="p-3">Status</th>
                                    <th class="p-3 text-right">Actions / Voucher</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0e8d8] bg-white">
                                @foreach ($commissions as $comm)
                                    @php
                                        $tier = $comm->agent_tier_applied ?? $comm->agent?->tier ?? 'junior';
                                    @endphp
                                    <tr class="hover:bg-[#f9f4ed]">
                                        <td class="p-3">
                                            <p class="font-bold text-[#1d3c34]">{{ $comm->agent?->name ?? '—' }}</p>
                                            <div class="mt-0.5 flex flex-wrap items-center gap-1.5">
                                                <span class="rounded bg-[#102b25] px-1.5 py-0.5 text-[9px] font-black uppercase text-[#d4af37]">
                                                    {{ ucfirst($tier) }} ({{ (int) ($comm->agent?->effective_split_percentage ?? 60) }}%)
                                                </span>
                                                @if ($comm->agent?->license_number)
                                                    <span class="font-mono text-[9px] text-slate-400">Lic: {{ $comm->agent->license_number }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-3 text-slate-600">
                                            <p class="font-semibold text-slate-800">{{ Str::limit($comm->property?->title ?? '—', 28) }}</p>
                                            <div class="mt-0.5 flex flex-wrap items-center gap-1">
                                                @if ($comm->split_type === 'co_brokered')
                                                    <span class="rounded bg-sky-100 px-1.5 py-0.5 text-[9px] font-black uppercase text-sky-800">
                                                        Co-Brokered ({{ $comm->sellingAgent?->name ? 'w/ '.$comm->sellingAgent->name : 'Co-Broker' }})
                                                    </span>
                                                @elseif ($comm->split_type === 'referral')
                                                    <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[9px] font-black uppercase text-amber-800">
                                                        Referral Deal ({{ $comm->referralAgent?->name ?? 'Referrer' }})
                                                    </span>
                                                @else
                                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-black uppercase text-slate-700">
                                                        Direct Listing
                                                    </span>
                                                @endif
                                                @if ($comm->company_retained_amount > 0)
                                                    <span class="text-[9px] font-semibold text-[#9b6c17]">
                                                        +{{ number_format($comm->company_retained_amount, 0) }} ETB Agency
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-3 font-semibold text-[#1d3c34]">
                                            {{ number_format($comm->property_price, 2) }} ETB
                                        </td>
                                        <td class="p-3 font-black text-[#1d3c34]">
                                            {{ number_format($comm->commission_amount, 2) }} ETB
                                            <span class="block text-[10px] font-normal text-slate-400">Rate: {{ $comm->commission_rate }}%</span>
                                        </td>
                                        <td class="p-3 font-semibold text-rose-700">
                                            -{{ number_format($comm->withholding_tax_amount ?: round($comm->commission_amount * 0.02, 2), 2) }} ETB
                                        </td>
                                        <td class="p-3 font-black text-emerald-800">
                                            {{ number_format($comm->net_payout_amount ?: round($comm->commission_amount * 0.98, 2), 2) }} ETB
                                        </td>
                                        <td class="p-3">
                                            @if ($comm->status === 'paid')
                                                <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-black uppercase text-emerald-800">Disbursed ✓</span>
                                            @elseif ($comm->status === 'waived')
                                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-black uppercase text-slate-700">Waived</span>
                                            @else
                                                <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-black uppercase text-amber-800">Pending Settlement</span>
                                            @endif
                                        </td>
                                        <td class="space-x-2 p-3 text-right">
                                            @if ($comm->status === 'pending')
                                                <button type="button" onclick="openCommissionPayoutModal({{ $comm->id }}, @js($comm->agent?->name ?? 'Agent'), @js($comm->property?->title ?? 'Property'), {{ (float) $comm->commission_amount }})" class="cursor-pointer rounded-lg bg-[#102b25] px-3 py-1 text-xs font-bold text-[#d4af37] transition hover:bg-black">
                                                    Disburse / ይክፈሉ
                                                </button>
                                            @elseif ($comm->status === 'paid')
                                                <a href="{{ route('commissions.voucher', $comm->id) }}" target="_blank" class="inline-block whitespace-nowrap rounded-lg border border-[#102b25] bg-[#102b25] px-2.5 py-1 text-sm font-black text-white transition hover:bg-black">
                                                    Voucher
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
            @endif

            {{-- Double-Entry Financial Ledgers --}}
            @if ($canReconcileAccounts)
            <section id="double-entry" data-section="double-entry" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <div class="border-b border-[#f0e8d8] pb-4">
                    <h2 class="text-xl font-black text-[#1d3c34]">Double-Entry Financial Ledgers (ኦዲት የተደረገ ሂሳብ)</h2>
                    <p class="text-xs text-[#587165]">Immutable credit &amp; debit entries for every financial lifecycle movement.</p>
                </div>

                <div class="mt-5 overflow-x-auto rounded-2xl border border-[#e7ddca]">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase text-[#1d3c34]">
                            <tr>
                                <th class="p-3">Ledger Code</th>
                                <th class="p-3">Date</th>
                                <th class="p-3">Category</th>
                                <th class="p-3">Txn Reference</th>
                                <th class="p-3">Description</th>
                                <th class="p-3 text-right">Inflow (Credit)</th>
                                <th class="p-3 text-right">Outflow (Debit)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0e8d8] bg-white">
                            @forelse ($ledgers as $ledger)
                                <tr class="hover:bg-[#f9f4ed]">
                                    <td class="p-3 font-mono font-bold text-slate-700">{{ $ledger->ledger_code }}</td>
                                    <td class="whitespace-nowrap p-3 text-slate-500">{{ $ledger->created_at->format('M d, Y') }}</td>
                                    <td class="p-3 font-bold capitalize text-[#1d3c34]">{{ str_replace('_', ' ', $ledger->category) }}</td>
                                    <td class="p-3 font-mono text-[10px] text-slate-500">{{ $ledger->reference ?: '—' }}</td>
                                    <td class="max-w-sm p-3 text-slate-600">{{ $ledger->description }}</td>
                                    <td class="p-3 text-right font-black text-emerald-700">
                                        {{ $ledger->entry_type === 'credit' ? number_format($ledger->amount, 2).' ETB' : '—' }}
                                    </td>
                                    <td class="p-3 text-right font-black text-rose-700">
                                        {{ $ledger->entry_type === 'debit' ? number_format($ledger->amount, 2).' ETB' : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-slate-500">No ledger entries for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4">
                    {{ $ledgers->fragment('double-entry')->links() }}
                </div>
            </section>
            @endif

            {{-- Lease & Rental Collection Desk --}}
            <section id="lease-rental" data-section="lease-rental" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                {{-- Header --}}
                <div class="flex flex-col gap-4 border-b border-[#f0e8d8] pb-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Lease Desk</span>
                            <span class="text-xs font-bold text-[#587165]">የረጅም ጊዜ ኪራይ እና ውል አስተዳደር</span>
                        </div>
                        <h2 class="mt-1 text-2xl font-black text-[#1d3c34]">Lease & Rental Collection Desk</h2>
                        <p class="text-xs text-[#587165]">Track existing tenancy agreements, automated Ethiopian installment schedules, monthly collections, and lease renewals. Registering new lease agreements is handled from the Admin Dashboard or Agent Portal.</p>
                    </div>
                    <form method="POST" action="{{ route('leases.process-schedules') }}">
                        @csrf
                        <button type="submit" class="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-[#1d3c34] bg-[#1d3c34] px-3.5 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-[#254d43]" title="Audit rent schedules and escalate overdue installments past grace period">
                            <span>⚡ Audit &amp; Escalation Check</span>
                        </button>
                    </form>
                </div>

                {{-- Lease Metrics Overview --}}
                <div class="my-6 grid grid-cols-2 gap-3.5 sm:grid-cols-4">
                    <div class="rounded-2xl border border-[#d9cab3] bg-[#f9f4ed] p-4 shadow-xs">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Active Leases</span>
                        <p class="mt-1 font-mono text-xl font-black text-[#1d3c34]">{{ $activeLeaseCount }}</p>
                        <span class="text-[11px] font-bold text-emerald-700">Of {{ $leases->count() }} total contracts</span>
                    </div>

                    <div class="rounded-2xl border border-[#d9cab3] bg-[#f9f4ed] p-4 shadow-xs">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Rent Collected</span>
                        <p class="mt-1 font-mono text-xl font-black text-emerald-700">ETB {{ number_format($collectedRent, 2) }}</p>
                        <span class="text-[11px] font-bold text-emerald-800">{{ $paidInstallmentCount }} installments paid</span>
                    </div>

                    <div class="rounded-2xl border border-[#d9cab3] bg-[#f9f4ed] p-4 shadow-xs">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Overdue Installments</span>
                        <p class="mt-1 font-mono text-xl font-black text-rose-700">{{ $overdueInstallmentCount }}</p>
                        <span class="text-[11px] font-bold text-rose-800">Requires tenant follow-up</span>
                    </div>

                    <div class="rounded-2xl border border-[#d9cab3] bg-[#f9f4ed] p-4 shadow-xs">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Outstanding Balance</span>
                        <p class="mt-1 font-mono text-xl font-black text-amber-800">ETB {{ number_format($outstandingRent, 2) }}</p>
                        <span class="text-[11px] text-slate-500">Remaining contract receivables</span>
                    </div>
                </div>

                {{-- Tabs --}}
                <div class="mb-4 flex flex-wrap items-center gap-4 border-b border-[#f0e8d8] text-xs font-bold">
                    <button type="button" onclick="switchLeaseTab('contracts')" id="tab-btn-contracts" class="lease-tab-btn cursor-pointer border-b-2 border-[#1d3c34] pb-2 text-[#1d3c34]">
                        Active Contracts ({{ $leases->count() }})
                    </button>
                    <button type="button" onclick="switchLeaseTab('schedules')" id="tab-btn-schedules" class="lease-tab-btn cursor-pointer border-b-2 border-transparent pb-2 text-slate-400 hover:text-slate-700">
                        Rent Installment Schedules ({{ $rentSchedules->count() }})
                    </button>
                    <button type="button" onclick="switchLeaseTab('overdue')" id="tab-btn-overdue" class="lease-tab-btn cursor-pointer border-b-2 border-transparent pb-2 text-slate-400 hover:text-slate-700">
                        Overdue Rent ({{ $overdueInstallmentCount }})
                    </button>
                </div>

                {{-- TAB 1: CONTRACTS --}}
                <div id="lease-view-contracts" class="lease-tab-content">
                    @if ($leases->isEmpty())
                        <div class="py-12 text-center text-slate-400">
                            <span class="text-3xl">📄</span>
                            <p class="mt-2 font-bold text-slate-600">No Lease Contracts Registered Yet</p>
                            <p class="mt-1 text-xs">Lease agreements are enrolled from the Admin Dashboard or Agent Portal.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-2xl border border-[#e7ddca]">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase text-[#1d3c34]">
                                    <tr>
                                        <th class="p-3">Lease Code</th>
                                        <th class="p-3">Property</th>
                                        <th class="p-3">Tenant</th>
                                        <th class="p-3 text-right">Monthly Rent</th>
                                        <th class="p-3">Collection Progress</th>
                                        <th class="p-3">Status</th>
                                        <th class="p-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#f0e8d8] bg-white">
                                    @foreach ($leases as $lease)
                                        @php
                                            $leaseSchedules = $lease->schedules;
                                            $leaseTotalDue = $lease->total_rent_due ?: 1;
                                            $leasePaidPercentage = min(100, (int) round(($lease->total_rent_paid / $leaseTotalDue) * 100));
                                            $leaseStatusBadge = match ($lease->status) {
                                                'active' => ['Active', 'bg-emerald-100 text-emerald-800'],
                                                'pending_renewal' => ['Pending Renewal', 'bg-amber-100 text-amber-800'],
                                                'expired' => ['Expired', 'bg-rose-100 text-rose-800'],
                                                'terminated' => ['Terminated', 'bg-slate-200 text-slate-700'],
                                                default => ['Draft', 'bg-slate-100 text-slate-600'],
                                            };
                                        @endphp
                                        <tr class="hover:bg-[#f9f4ed]">
                                            <td class="p-3 font-mono font-bold text-slate-700">
                                                {{ $lease->lease_code }}
                                                <span class="block font-normal text-[10px] text-slate-400">Freq: {{ ucfirst($lease->payment_frequency) }}</span>
                                            </td>
                                            <td class="p-3 font-semibold text-slate-700">
                                                {{ $lease->property?->title ?? '—' }}
                                                <span class="block text-[10px] font-normal text-slate-400">{{ $lease->property?->city ?? 'Addis Ababa' }}</span>
                                            </td>
                                            <td class="p-3 text-slate-600">
                                                {{ $lease->tenant_name }}
                                                <span class="block text-[10px] text-slate-400">{{ $lease->tenant_phone }} · {{ $lease->tenant_email }}</span>
                                            </td>
                                            <td class="p-3 text-right font-black text-[#1d3c34]">{{ number_format($lease->monthly_rent, 2) }}</td>
                                            <td class="p-3">
                                                <div class="mb-1 flex items-center justify-between text-[10px] font-bold text-slate-600">
                                                    <span>ETB {{ number_format($lease->total_rent_paid, 0) }}</span>
                                                    <span>{{ $leasePaidPercentage }}%</span>
                                                </div>
                                                <div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-200">
                                                    <div class="h-1.5 rounded-full bg-emerald-600" style="width: {{ $leasePaidPercentage }}%"></div>
                                                </div>
                                            </td>
                                            <td class="p-3">
                                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $leaseStatusBadge[1] }}">{{ $leaseStatusBadge[0] }}</span>
                                            </td>
                                            <td class="p-3">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <a href="{{ route('leases.agreement', $lease) }}" target="_blank" class="rounded-full border border-[#102b25] bg-[#102b25] px-2.5 py-1 text-[10px] font-bold text-[#d4af37] transition hover:bg-[#1d3c34]">
                                                        Agreement PDF
                                                    </a>
                                                    <button type="button" onclick="viewLeaseSchedule({{ $lease->id }})" class="cursor-pointer rounded-full border border-[#d9cab3] bg-white px-2.5 py-1 text-[10px] font-bold text-[#1d3c34] transition hover:bg-[#f1e4cf]">
                                                        Schedule
                                                    </button>
                                                    <form method="POST" action="{{ route('leases.update-status', $lease) }}" class="flex items-center gap-1">
                                                        @csrf
                                                        @method('PUT')
                                                        <select name="status" class="rounded-lg border border-[#d9cab3] bg-white px-1.5 py-1 text-[10px] font-bold text-slate-700">
                                                            <option value="active" {{ $lease->status === 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="pending_renewal" {{ $lease->status === 'pending_renewal' ? 'selected' : '' }}>Pending Renewal</option>
                                                            <option value="expired" {{ $lease->status === 'expired' ? 'selected' : '' }}>Expired</option>
                                                            <option value="terminated" {{ $lease->status === 'terminated' ? 'selected' : '' }}>Terminated</option>
                                                            <option value="draft" {{ $lease->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                                        </select>
                                                        <button type="submit" class="cursor-pointer rounded-lg bg-[#102b25] px-2 py-1 text-[10px] font-bold text-white transition hover:bg-[#1d3c34]">Update</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- TAB 2: RENT INSTALLMENT SCHEDULES --}}
                <div id="lease-view-schedules" class="lease-tab-content hidden">
                    @if ($rentSchedules->isEmpty())
                        <div class="py-12 text-center text-slate-400">
                            <p class="font-bold text-slate-600">No Rent Installments Scheduled</p>
                            <p class="mt-1 text-xs">Schedules are generated automatically whenever a lease is registered.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-2xl border border-[#e7ddca]">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase text-[#1d3c34]">
                                    <tr>
                                        <th class="p-3">Period / Label</th>
                                        <th class="p-3">Lease &amp; Property</th>
                                        <th class="p-3">Tenant</th>
                                        <th class="p-3">Due Date</th>
                                        <th class="p-3">Amount Due</th>
                                        <th class="p-3">Status</th>
                                        <th class="p-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#f0e8d8] bg-white">
                                    @foreach ($rentSchedules as $schedule)
                                        <tr class="hover:bg-[#f9f4ed]">
                                            <td class="p-3 font-bold text-slate-800">{{ $schedule->period_label }}</td>
                                            <td class="p-3">
                                                <span class="font-mono font-bold text-[#1d3c34]">{{ $schedule->lease?->lease_code ?? 'N/A' }}</span>
                                                <span class="block text-[10px] text-slate-400">{{ Str::limit($schedule->lease?->property?->title ?? 'Property', 22) }}</span>
                                            </td>
                                            <td class="p-3">
                                                <span class="block font-bold text-slate-700">{{ $schedule->lease?->tenant_name ?? 'Tenant' }}</span>
                                                <span class="block text-[10px] text-slate-400">{{ $schedule->lease?->tenant_phone }}</span>
                                            </td>
                                            <td class="p-3 font-mono">
                                                <span class="{{ $schedule->is_overdue ? 'font-bold text-rose-700' : 'text-slate-600' }}">
                                                    {{ $schedule->due_date?->format('M d, Y') }}
                                                </span>
                                            </td>
                                            <td class="p-3 font-mono font-black text-[#1d3c34]">
                                                ETB {{ number_format($schedule->amount_due, 2) }}
                                                @if ($schedule->amount_paid > 0 && $schedule->status !== 'paid')
                                                    <span class="block text-[10px] font-normal text-emerald-600">Paid: ETB {{ number_format($schedule->amount_paid, 2) }}</span>
                                                @endif
                                            </td>
                                            <td class="p-3">
                                                <span class="rounded-full border px-2.5 py-0.5 text-[10px] font-black {{ $schedule->status_badge[1] }}">
                                                    {{ $schedule->status_badge[0] }}
                                                </span>
                                            </td>
                                            <td class="p-3 text-right">
                                                @if ($schedule->status === 'paid')
                                                    @if ($schedule->payment)
                                                        <a href="{{ route('payments.receipt', $schedule->payment->transaction_number) }}" target="_blank" class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-800 transition hover:bg-emerald-100">
                                                            Receipt →
                                                        </a>
                                                    @else
                                                        <span class="text-[11px] font-bold text-emerald-700">Settled ✓</span>
                                                    @endif
                                                @else
                                                    <button type="button" onclick="openRecordRentPaymentModal({{ $schedule->id }}, @js($schedule->period_label), {{ (float) $schedule->amount_due }})" class="cursor-pointer rounded-full bg-[#102b25] px-2.5 py-1 text-[11px] font-bold text-[#d4af37] transition hover:bg-[#1d3c34]">
                                                        + Pay Rent
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- TAB 3: OVERDUE RENT --}}
                <div id="lease-view-overdue" class="lease-tab-content hidden">
                    @if ($overdueSchedules->isEmpty())
                        <div class="py-12 text-center text-slate-400">
                            <span class="text-3xl">✅</span>
                            <p class="mt-2 text-sm font-bold text-emerald-800">All Rent Installments Are Up To Date!</p>
                            <p class="mt-0.5 text-xs text-slate-500">No overdue rental schedules currently exist.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-2xl border border-rose-200">
                            <table class="w-full text-left text-xs">
                                <thead class="border-b border-rose-200 bg-rose-50 text-[10px] font-bold uppercase text-rose-900">
                                    <tr>
                                        <th class="p-3">Period</th>
                                        <th class="p-3">Tenant &amp; Contact</th>
                                        <th class="p-3">Property</th>
                                        <th class="p-3">Original Due Date</th>
                                        <th class="p-3">Overdue Amount</th>
                                        <th class="p-3 text-right">Collect Rent</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-rose-100 bg-white">
                                    @foreach ($overdueSchedules as $schedule)
                                        <tr class="hover:bg-rose-50/40">
                                            <td class="p-3 font-bold text-rose-950">{{ $schedule->period_label }}</td>
                                            <td class="p-3">
                                                <span class="block font-bold text-slate-800">{{ $schedule->lease?->tenant_name }}</span>
                                                <span class="block text-[10px] text-slate-600">{{ $schedule->lease?->tenant_phone }} · {{ $schedule->lease?->tenant_email }}</span>
                                            </td>
                                            <td class="p-3">
                                                <span class="block font-bold text-[#1d3c34]">{{ Str::limit($schedule->lease?->property?->title ?? 'Property', 25) }}</span>
                                                <span class="block font-mono text-[10px] text-slate-500">{{ $schedule->lease?->lease_code }}</span>
                                            </td>
                                            <td class="p-3 font-mono font-bold text-rose-700">{{ $schedule->due_date?->format('M d, Y') }}</td>
                                            <td class="p-3 font-mono font-black text-rose-800">ETB {{ number_format($schedule->amount_due - $schedule->amount_paid, 2) }}</td>
                                            <td class="p-3 text-right">
                                                <button type="button" onclick="openRecordRentPaymentModal({{ $schedule->id }}, @js($schedule->period_label), {{ (float) $schedule->amount_due - (float) $schedule->amount_paid }})" class="cursor-pointer rounded-full bg-rose-700 px-3 py-1 text-[11px] font-bold text-white transition hover:bg-rose-800">
                                                    Collect ETB
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Front Desk Shifts & Cash Reconciliation Desk --}}
            <section id="front-desk-shifts" data-section="front-desk-shifts" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-[#ebdcc9] pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-[#102b25] px-2 py-0.5 text-[9px] font-black uppercase text-[#d4af37]">Cash Audit Desk</span>
                            <h2 class="text-xl font-black text-[#1d3c34]">Front Desk Shift Reconciliations</h2>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            Verify receptionist cash drawer summaries, Telebirr, CBE Birr, and POS card terminal collections.
                        </p>
                    </div>
                </div>

                @if (! isset($frontDeskShifts) || $frontDeskShifts->isEmpty())
                    <div class="p-12 text-center text-slate-400">
                        <span class="text-3xl">💵</span>
                        <p class="mt-2 text-sm font-semibold">No front desk shift reconciliations logged yet.</p>
                    </div>
                @else
                    <div class="mt-6 overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#ebdcc9]/40 text-[#102b25] uppercase text-[10px] font-bold">
                                <tr>
                                    <th class="p-3">Shift Ref</th>
                                    <th class="p-3">Date &amp; Type</th>
                                    <th class="p-3">Receptionist &amp; Property</th>
                                    <th class="p-3">Breakdown (ETB)</th>
                                    <th class="p-3">Total Collected</th>
                                    <th class="p-3">Status</th>
                                    <th class="p-3 text-right">Audit Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#ebdcc9]/30">
                                @foreach ($frontDeskShifts as $sh)
                                    <tr class="hover:bg-[#fcf9f5]">
                                        <td class="p-3 font-mono font-bold text-[#102b25]">{{ $sh->shift_reference }}</td>
                                        <td class="p-3">
                                            <p class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($sh->shift_date)->format('M d, Y') }}</p>
                                            <p class="text-[10px] text-slate-400 capitalize">{{ $sh->shift_type }} shift</p>
                                        </td>
                                        <td class="p-3">
                                            <p class="font-bold text-slate-900">{{ $sh->receptionist?->name ?? 'Front Desk' }}</p>
                                            <p class="text-[10px] text-slate-500">{{ $sh->property?->title ?? 'GTM Property' }}</p>
                                        </td>
                                        <td class="p-3 text-[11px] space-y-0.5">
                                            <p>💵 Cash: <strong>{{ number_format($sh->cash_collected, 2) }}</strong></p>
                                            <p>📱 Telebirr: <strong>{{ number_format($sh->telebirr_collected, 2) }}</strong></p>
                                            <p>🏦 CBE: <strong>{{ number_format($sh->cbe_collected, 2) }}</strong></p>
                                            <p>💳 POS: <strong>{{ number_format($sh->card_pos_collected, 2) }}</strong></p>
                                        </td>
                                        <td class="p-3 font-mono font-black text-sm text-[#102b25]">
                                            {{ number_format($sh->total_collected, 2) }} ETB
                                        </td>
                                        <td class="p-3">
                                            <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $sh->status_badge_class }}">
                                                {{ $sh->status_label }}
                                            </span>
                                            @if ($sh->financeOfficer)
                                                <p class="text-[9px] text-slate-400 mt-0.5">By {{ $sh->financeOfficer->name }}</p>
                                            @endif
                                        </td>
                                        <td class="p-3 text-right">
                                            @if ($isFinanceStaff)
                                                <form method="POST" action="{{ route('finance.shifts.verify', $sh->id) }}" class="inline-flex items-center gap-1.5">
                                                    @csrf
                                                    <input type="text" name="finance_notes" value="{{ $sh->finance_notes }}" placeholder="Audit memo" class="rounded-lg border border-[#d9cab3] px-2 py-1 text-[11px] w-28">
                                                    @if ($sh->status !== 'verified_by_finance')
                                                        <button type="submit" name="status" value="verified_by_finance" class="rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2.5 py-1 text-xs">
                                                            Verify
                                                        </button>
                                                    @endif
                                                    @if ($sh->status !== 'discrepancy_flagged')
                                                        <button type="submit" name="status" value="discrepancy_flagged" class="rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold px-2.5 py-1 text-xs">
                                                            Flag
                                                        </button>
                                                    @endif
                                                </form>
                                            @else
                                                <span class="text-xs text-slate-400">View Only</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            {{-- Tax for ERCA Compliance Section (Ministry of Revenues / ERCA) --}}
            <section id="erca-tax" data-section="erca-tax" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
                <div class="flex flex-col gap-3 border-b border-[#f0e8d8] pb-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-[#102b25] px-3 py-1 text-xs font-black uppercase tracking-wider text-[#d4af37]">ERCA Tax Core</span>
                            <span class="text-xs font-bold text-[#587165]">የኢትዮጵያ ገቢዎች ሚኒስቴር የታክስ እና ቫት ኦዲት</span>
                        </div>
                        <h2 class="mt-2 text-2xl font-black text-[#1d3c34]">Tax for ERCA Compliance &amp; Audit Filing</h2>
                        <p class="text-xs text-[#587165]">Official tax calculations for Ministry of Revenues (ERCA): 15% VAT accrued on taxable platform fees and 2% Withholding Tax deducted from agent commission payouts.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('finance.statement', ['period' => $dateFilter]) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-full bg-[#102b25] px-4 py-2 text-xs font-black text-[#d4af37] shadow-sm hover:bg-[#1d3c34] transition">
                            <span>🖨️ Print Tax Statement</span>
                        </a>
                        <a href="{{ route('finance.export-csv') }}" class="inline-flex items-center gap-1.5 rounded-full border border-[#cdbb97] bg-white px-4 py-2 text-xs font-bold text-[#1d3c34] hover:bg-[#f1e4cf] transition">
                            <span>📥 Export Tax Records</span>
                        </a>
                    </div>
                </div>

                {{-- ERCA Tax Metrics Cards --}}
                <div class="my-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border-2 border-[#d4af37]/40 bg-gradient-to-br from-[#fffdfa] to-[#fbf5e8] p-5 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#9b6c17]">Total ERCA Tax Obligation</span>
                            <span class="text-2xl">🏛️</span>
                        </div>
                        <p class="mt-2 text-3xl font-black text-[#102b25]">{{ number_format($stats['vat_accrued'] + $stats['total_withholding_tax'], 2) }} <span class="text-sm font-bold">ETB</span></p>
                        <p class="mt-1 text-xs text-slate-600">Combined VAT (15%) + Withholding Tax (2%) for {{ str_replace('_', ' ', $dateFilter) }}</p>
                    </div>

                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-5 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">15% VAT Accrued (ተጨማሪ እሴት ታክስ)</span>
                            <span class="text-2xl">🧾</span>
                        </div>
                        <p class="mt-2 text-3xl font-black text-emerald-900">{{ number_format($stats['vat_accrued'], 2) }} <span class="text-sm font-bold">ETB</span></p>
                        <p class="mt-1 text-xs text-slate-600">Calculated on {{ number_format($stats['platform_fees'], 2) }} ETB taxable platform services</p>
                    </div>

                    <div class="rounded-2xl border border-rose-200 bg-rose-50/40 p-5 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-rose-800">2% Withholding Tax (የቅድመ ግብር ተቀናሽ)</span>
                            <span class="text-2xl">✂️</span>
                        </div>
                        <p class="mt-2 text-3xl font-black text-rose-900">{{ number_format($stats['total_withholding_tax'], 2) }} <span class="text-sm font-bold">ETB</span></p>
                        <p class="mt-1 text-xs text-slate-600">Deducted at source across {{ number_format($stats['total_paid_commissions'], 2) }} ETB agent commissions</p>
                    </div>
                </div>

                {{-- ERCA Tax Ledger Breakdown Table --}}
                <div class="mt-6 rounded-2xl border border-[#e7ddca] overflow-hidden">
                    <div class="bg-[#f9f4ed] px-5 py-3 border-b border-[#e7ddca] flex items-center justify-between">
                        <h3 class="text-xs font-black uppercase tracking-wider text-[#1d3c34]">ERCA Tax Withholding &amp; VAT Settlement Ledger</h3>
                        <span class="text-xs font-bold text-slate-500">{{ $commissions->where('status', 'paid')->count() }} tax withholding vouchers logged</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-white text-[10px] font-bold uppercase text-slate-500 border-b border-[#f0e8d8]">
                                <tr>
                                    <th class="p-3">Voucher Ref</th>
                                    <th class="p-3">Beneficiary Agent / TIN</th>
                                    <th class="p-3">Property / Deal Reference</th>
                                    <th class="p-3 text-right">Gross Taxable</th>
                                    <th class="p-3 text-right">Tax Rate</th>
                                    <th class="p-3 text-right">Tax Retained (ETB)</th>
                                    <th class="p-3 text-right">Net Remitted</th>
                                    <th class="p-3 text-right">Official Voucher</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0e8d8] bg-white">
                                @forelse ($commissions->where('status', 'paid') as $paidComm)
                                    <tr class="hover:bg-[#fcf9f5]">
                                        <td class="p-3 font-mono font-bold text-[#1d3c34]">
                                            COMM-{{ str_pad($paidComm->id, 5, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td class="p-3">
                                            <p class="font-bold text-slate-900">{{ $paidComm->agent?->name ?? 'Agent' }}</p>
                                            <p class="font-mono text-[10px] text-slate-400">TIN: {{ $paidComm->agent?->tin_number ?? 'ET-TIN-PENDING' }}</p>
                                        </td>
                                        <td class="p-3">
                                            <p class="font-medium text-slate-800">{{ Str::limit($paidComm->property?->title ?? 'Sold Property', 26) }}</p>
                                            <p class="text-[10px] text-slate-400">Deal: {{ number_format($paidComm->property_price, 2) }} ETB</p>
                                        </td>
                                        <td class="p-3 text-right font-mono font-bold text-slate-800">
                                            {{ number_format($paidComm->commission_amount, 2) }}
                                        </td>
                                        <td class="p-3 text-right font-bold text-rose-700">
                                            2% WHT
                                        </td>
                                        <td class="p-3 text-right font-mono font-black text-rose-800">
                                            -{{ number_format($paidComm->withholding_tax_amount ?: round($paidComm->commission_amount * 0.02, 2), 2) }}
                                        </td>
                                        <td class="p-3 text-right font-mono font-black text-emerald-800">
                                            {{ number_format($paidComm->net_payout_amount ?: round($paidComm->commission_amount * 0.98, 2), 2) }}
                                        </td>
                                        <td class="p-3 text-right">
                                            <a href="{{ route('commissions.voucher', $paidComm->id) }}" target="_blank" class="inline-block rounded-lg bg-[#102b25] px-3 py-1 text-[11px] font-bold text-[#d4af37] hover:bg-black transition">
                                                Tax Voucher &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-6 text-center text-slate-400 text-xs">
                                            No agent commissions disbursed yet during this period. When commissions are settled, official 2% ERCA WHT vouchers will automatically populate here.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            {{-- My Profile (self-service) --}}
            <section id="profile" data-section="profile" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
                @include('layouts.partials.profile-form', [
                    'user' => auth()->user(),
                    'profileAction' => route('profile.update'),
                    'profileHeading' => 'My Profile',
                    'profileSubheading' => 'Update your name, login password, and profile photo.',
                    'profileRoleLabel' => $roleLabel,
                ])
            </section>
        </main>
    </div>
</div>

    {{-- Record Rent Installment Payment Modal --}}
    <div id="record-rent-payment-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 p-4 backdrop-blur-xs flex items-center justify-center">
        <div class="relative w-full max-w-md rounded-3xl border border-[#d9cab3] bg-[#fdfaf5] p-6 shadow-2xl">
            <div class="flex items-center justify-between border-b border-[#f0e8d8] pb-3">
                <div>
                    <span class="rounded bg-[#102b25] px-2 py-0.5 text-[9px] font-black uppercase text-[#d4af37]">Ethiopian Payment Core</span>
                    <h3 class="mt-1 text-lg font-black text-[#1d3c34]">Record Rent Payment</h3>
                </div>
                <button type="button" onclick="closeRecordRentPaymentModal()" class="cursor-pointer text-2xl font-bold text-slate-400 hover:text-slate-800">&times;</button>
            </div>

            <form id="record-rent-payment-form" method="POST" action="" class="mt-4 space-y-3.5">
                @csrf
                <p id="rent-payment-period-display" class="rounded-xl border border-[#e7ddca] bg-[#f9f4ed] p-2.5 text-xs font-bold text-[#1d3c34]"></p>

                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-700">Amount Paid (ETB) *</label>
                    <input type="number" step="0.01" name="amount_paid" id="rent-payment-amount" required class="w-full rounded-xl border border-[#cdbb97] bg-white px-3 py-2 font-mono text-sm font-bold text-[#1d3c34] focus:border-[#1d3c34] focus:outline-none">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-700">Payment Method (የክፍያ መንገድ) *</label>
                    <select name="payment_method" required class="w-full rounded-xl border border-[#cdbb97] bg-white px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none">
                        <option value="telebirr">Telebirr (ቴሌብር)</option>
                        <option value="cbe_birr">CBE Birr (ሲቢኢ ብር)</option>
                        <option value="bank_transfer" selected>Bank Transfer / Deposit Slip (ባንክ ክፍያ)</option>
                        <option value="cash">Cash at Office (ጥሬ ገንዘብ)</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-700">Bank Reference / FT Number</label>
                    <input type="text" name="bank_transaction_reference" placeholder="e.g. FT24099XXXXX" class="w-full rounded-xl border border-[#cdbb97] bg-white px-3 py-2 font-mono text-xs focus:border-[#1d3c34] focus:outline-none">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-700">Receipt Notes (Optional)</label>
                    <input type="text" name="notes" placeholder="e.g. Paid in full via CBE Mobile" class="w-full rounded-xl border border-[#cdbb97] bg-white px-3 py-2 text-xs focus:border-[#1d3c34] focus:outline-none">
                </div>

                <div class="flex items-center justify-end gap-2.5 border-t border-[#f0e8d8] pt-3">
                    <button type="button" onclick="closeRecordRentPaymentModal()" class="cursor-pointer rounded-full border border-slate-300 px-4 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="cursor-pointer rounded-full bg-[#102b25] px-5 py-2 text-xs font-black text-[#d4af37] shadow-sm hover:bg-[#1d3c34]">
                        Confirm &amp; Issue Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- View Lease Installment Schedule Modal --}}
    <div id="view-lease-schedule-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 p-4 backdrop-blur-xs flex items-center justify-center">
        <div class="relative my-8 max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl border border-[#d9cab3] bg-[#fdfaf5] p-6 shadow-2xl">
            <div class="flex items-center justify-between border-b border-[#f0e8d8] pb-4">
                <div>
                    <span id="modal-lease-code" class="rounded bg-[#102b25] px-2 py-0.5 text-[9px] font-black uppercase text-[#d4af37]"></span>
                    <h3 id="modal-lease-title" class="mt-1 text-xl font-black text-[#1d3c34]">Lease Installment Schedule</h3>
                    <p id="modal-lease-tenant" class="text-xs text-slate-500"></p>
                </div>
                <button type="button" onclick="closeLeaseScheduleModal()" class="cursor-pointer text-2xl font-bold text-slate-400 hover:text-slate-800">&times;</button>
            </div>

            <div class="my-4 grid grid-cols-3 gap-3 text-center text-xs">
                <div class="rounded-xl border border-[#d9cab3] bg-[#f9f4ed] p-2.5">
                    <span class="text-[10px] font-bold uppercase text-slate-400">Total Contract Value</span>
                    <p id="modal-total-due" class="mt-0.5 font-mono text-sm font-black text-[#1d3c34]"></p>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-2.5">
                    <span class="text-[10px] font-bold uppercase text-emerald-700">Total Collected</span>
                    <p id="modal-total-paid" class="mt-0.5 font-mono text-sm font-black text-emerald-800"></p>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-2.5">
                    <span class="text-[10px] font-bold uppercase text-amber-700">Outstanding Balance</span>
                    <p id="modal-balance" class="mt-0.5 font-mono text-sm font-black text-amber-900"></p>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto rounded-2xl border border-[#e7ddca]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase text-[#1d3c34]">
                        <tr>
                            <th class="p-3">#</th>
                            <th class="p-3">Installment Period</th>
                            <th class="p-3">Due Date</th>
                            <th class="p-3">Amount Due</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody id="modal-schedules-tbody" class="divide-y divide-[#f0e8d8] bg-white"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Commission Payout Modal --}}
    <div id="commission-payout-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
        <div class="w-full max-w-lg space-y-4 rounded-3xl border border-[#d9cab3] bg-[#fdfaf5] p-6 shadow-2xl">
            <div class="flex items-center justify-between border-b border-[#f0e8d8] pb-3">
                <div>
                    <h3 class="text-lg font-black text-[#1d3c34]">የኮሚሽን ክፍያ ማስተላለፊያ / Settle Commission</h3>
                    <p id="modal-comm-subtitle" class="text-xs text-slate-600">Disburse agent commission with 2% Withholding Tax</p>
                </div>
                <button type="button" onclick="closeCommissionPayoutModal()" class="cursor-pointer text-2xl font-bold text-slate-400 hover:text-slate-800">&times;</button>
            </div>

            <form id="commission-payout-form" method="POST" action="" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3 rounded-2xl border border-[#e7ddca] bg-[#f9f4ed] p-3 text-xs">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400">Gross Commission</span>
                        <p id="modal-gross-amount" class="text-base font-black text-[#1d3c34]">0.00 ETB</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400">2% WHT Deduction</span>
                        <p id="modal-wht-amount" class="text-base font-black text-rose-700">-0.00 ETB</p>
                    </div>
                    <div class="col-span-2 flex items-center justify-between border-t border-[#e7ddca] pt-2">
                        <span class="text-xs font-black uppercase text-[#1d3c34]">Net Payout Amount:</span>
                        <span id="modal-net-amount" class="text-lg font-black text-emerald-800">0.00 ETB</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Disbursement Channel / የመክፈያ መንገድ</label>
                    <select name="payout_method" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold">
                        <option value="bank_transfer">Commercial Bank of Ethiopia (CBE Direct Transfer)</option>
                        <option value="cbe_birr">CBE Birr Wallet</option>
                        <option value="telebirr">Telebirr SuperApp Wallet</option>
                        <option value="check">Official Bank Check / CPO</option>
                        <option value="cash">Cash Settlement (With Receipt)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Bank Transaction Ref / Check Number</label>
                    <input type="text" name="payout_reference" placeholder="e.g. FT261003COMM99" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 font-mono text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Audit / Finance Note</label>
                    <input type="text" name="admin_note" placeholder="Optional audit memo" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-[#f0e8d8] pt-3">
                    <button type="button" onclick="closeCommissionPayoutModal()" class="cursor-pointer rounded-xl border border-[#d9cab3] px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100">
                        Cancel
                    </button>
                    <button type="submit" class="cursor-pointer rounded-xl bg-[#102b25] px-6 py-2.5 text-xs font-black text-[#d4af37] shadow-sm transition hover:bg-black">
                        Confirm &amp; Disburse Commission
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Section Navigation & Commission Payout Scripts --}}
    <script>
        const commissionSettleUrlTemplate = @js(route('commissions.settle', ['commission' => '__COMMISSION_ID__']));
        const rentPaymentUrlTemplate = @js(route('rent-schedules.pay', ['schedule' => '__SCHEDULE_ID__']));
        const leaseShowUrlTemplate = @js(route('leases.show', ['lease' => '__LEASE_ID__']));

        window.switchLeaseTab = function (tabName) {
            document.querySelectorAll('.lease-tab-content').forEach(element => element.classList.add('hidden'));
            document.querySelectorAll('.lease-tab-btn').forEach(button => {
                button.classList.remove('border-[#1d3c34]', 'text-[#1d3c34]');
                button.classList.add('border-transparent', 'text-slate-400');
            });

            const activeContent = document.getElementById('lease-view-' + tabName);
            const activeButton = document.getElementById('tab-btn-' + tabName);
            if (activeContent) {
                activeContent.classList.remove('hidden');
            }
            if (activeButton) {
                activeButton.classList.remove('border-transparent', 'text-slate-400');
                activeButton.classList.add('border-[#1d3c34]', 'text-[#1d3c34]');
            }
        };

        window.openRecordRentPaymentModal = function (scheduleId, periodLabel, amountDue) {
            const form = document.getElementById('record-rent-payment-form');
            form.action = rentPaymentUrlTemplate.replace('__SCHEDULE_ID__', scheduleId);
            document.getElementById('rent-payment-period-display').innerText = 'Recording payment for: ' + periodLabel;
            document.getElementById('rent-payment-amount').value = parseFloat(amountDue).toFixed(2);
            document.getElementById('record-rent-payment-modal').classList.remove('hidden');
        };

        window.closeRecordRentPaymentModal = function () {
            document.getElementById('record-rent-payment-modal').classList.add('hidden');
        };

        window.viewLeaseSchedule = function (leaseId) {
            fetch(leaseShowUrlTemplate.replace('__LEASE_ID__', leaseId), {
                headers: { 'Accept': 'application/json' }
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modal-lease-code').innerText = data.lease.lease_code;
                    document.getElementById('modal-lease-title').innerText = data.lease.property ? data.lease.property.title : 'Lease Agreement';
                    document.getElementById('modal-lease-tenant').innerText = 'Tenant: ' + data.lease.tenant_name + ' (' + data.lease.tenant_phone + ')';
                    document.getElementById('modal-total-due').innerText = 'ETB ' + parseFloat(data.summary.total_rent_due).toLocaleString('en-US', { minimumFractionDigits: 2 });
                    document.getElementById('modal-total-paid').innerText = 'ETB ' + parseFloat(data.summary.total_rent_paid).toLocaleString('en-US', { minimumFractionDigits: 2 });
                    document.getElementById('modal-balance').innerText = 'ETB ' + parseFloat(data.summary.outstanding_balance).toLocaleString('en-US', { minimumFractionDigits: 2 });

                    const tbody = document.getElementById('modal-schedules-tbody');
                    tbody.innerHTML = '';

                    data.schedules.forEach(schedule => {
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-[#f9f4ed] transition';
                        const isOverdue = new Date(schedule.due_date) < new Date();
                        const statusClass = schedule.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : (isOverdue ? 'bg-rose-100 text-rose-800' : 'bg-blue-50 text-blue-800');
                        const statusText = schedule.status === 'paid' ? 'Paid ✓' : (isOverdue ? 'Overdue' : 'Pending');

                        row.innerHTML = `
                            <td class="p-3 font-mono font-bold">${schedule.installment_number}</td>
                            <td class="p-3 font-bold text-slate-800">${schedule.period_label}</td>
                            <td class="p-3 font-mono text-slate-600">${schedule.due_date}</td>
                            <td class="p-3 font-mono font-black text-[#1d3c34]">ETB ${parseFloat(schedule.amount_due).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                            <td class="p-3"><span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-black ${statusClass}">${statusText}</span></td>
                        `;
                        tbody.appendChild(row);
                    });

                    document.getElementById('view-lease-schedule-modal').classList.remove('hidden');
                });
        };

        window.closeLeaseScheduleModal = function () {
            document.getElementById('view-lease-schedule-modal').classList.add('hidden');
        };

        window.openCommissionPayoutModal = function (commissionId, agentName, propertyTitle, grossAmount) {
            const modal = document.getElementById('commission-payout-modal');
            const form = document.getElementById('commission-payout-form');
            form.action = commissionSettleUrlTemplate.replace('__COMMISSION_ID__', commissionId);

            const gross = parseFloat(grossAmount);
            const wht = Math.round(gross * 0.02 * 100) / 100;
            const net = Math.round((gross - wht) * 100) / 100;

            document.getElementById('modal-comm-subtitle').textContent = `${agentName} — ${propertyTitle}`;
            document.getElementById('modal-gross-amount').textContent = `${gross.toLocaleString('en-US', { minimumFractionDigits: 2 })} ETB`;
            document.getElementById('modal-wht-amount').textContent = `-${wht.toLocaleString('en-US', { minimumFractionDigits: 2 })} ETB`;
            document.getElementById('modal-net-amount').textContent = `${net.toLocaleString('en-US', { minimumFractionDigits: 2 })} ETB`;

            modal.classList.remove('hidden');
        };

        window.closeCommissionPayoutModal = function () {
            document.getElementById('commission-payout-modal').classList.add('hidden');
        };

        window.toggleMobileSidebar = function (open) {
            const drawer = document.getElementById('finance-mobile-drawer');
            if (drawer) {
                drawer.classList.toggle('hidden', !open);
            }
        };

        const financePalettes = {
            emerald: {
                name: 'Highland Emerald',
                primary: '#1d3c34', primary_hover: '#254d43', primary_dark: '#102b25', primary_light: '#dfeee4',
                secondary: '#d9cab3', secondary_light: '#e7ddca', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#f9ecd0',
                site_bg: '#f5f0e7', card_bg: '#ffffff', text_dark: '#1d3c34', text_muted: '#587165',
                primary_rgb: '29, 60, 52', primary_dark_rgb: '16, 43, 37', primary_light_rgb: '223, 238, 228', secondary_rgb: '217, 202, 179', accent_rgb: '212, 175, 55', accent_light_rgb: '249, 236, 208'
            },
            sapphire: {
                name: 'Sapphire Navy',
                primary: '#1e3a5f', primary_hover: '#2b5282', primary_dark: '#0f1f33', primary_light: '#dbeafe',
                secondary: '#cbd5e1', secondary_light: '#e2e8f0', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#fef3c7',
                site_bg: '#f1f5f9', card_bg: '#ffffff', text_dark: '#0f172a', text_muted: '#475569',
                primary_rgb: '30, 58, 95', primary_dark_rgb: '15, 31, 51', primary_light_rgb: '219, 234, 254', secondary_rgb: '203, 213, 225', accent_rgb: '212, 175, 55', accent_light_rgb: '254, 243, 199'
            },
            obsidian: {
                name: 'Obsidian Slate',
                primary: '#18181b', primary_hover: '#27272a', primary_dark: '#09090b', primary_light: '#e4e4e7',
                secondary: '#cbd5e1', secondary_light: '#e2e8f0', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#fef3c7',
                site_bg: '#f8fafc', card_bg: '#ffffff', text_dark: '#09090b', text_muted: '#64748b',
                primary_rgb: '24, 24, 27', primary_dark_rgb: '9, 9, 11', primary_light_rgb: '228, 228, 231', secondary_rgb: '203, 213, 225', accent_rgb: '212, 175, 55', accent_light_rgb: '254, 243, 199'
            },
            bordeaux: {
                name: 'Bordeaux Velvet',
                primary: '#4c1d24', primary_hover: '#632530', primary_dark: '#2d0f14', primary_light: '#fae8eb',
                secondary: '#d9c7c9', secondary_light: '#ece1e3', accent: '#d4af37', accent_dark: '#9b6c17', accent_light: '#fef3c7',
                site_bg: '#fbf7f7', card_bg: '#ffffff', text_dark: '#260a0f', text_muted: '#6b4c52',
                primary_rgb: '76, 29, 36', primary_dark_rgb: '45, 15, 20', primary_light_rgb: '250, 232, 235', secondary_rgb: '217, 199, 201', accent_rgb: '212, 175, 55', accent_light_rgb: '254, 243, 199'
            }
        };

        window.togglePaletteMenu = function () {
            const dropdown = document.getElementById('palette-switcher-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        };

        window.applyFinancePalette = function (key) {
            if (!financePalettes[key]) return;
            const p = financePalettes[key];
            const root = document.documentElement;
            for (const [k, v] of Object.entries(p)) {
                if (k === 'name') continue;
                root.style.setProperty('--theme-' + k.replace(/_/g, '-'), v);
            }
            try {
                localStorage.setItem('gtm_finance_palette', key);
            } catch (e) {}
            const activeName = document.getElementById('active-palette-name');
            if (activeName) {
                activeName.textContent = p.name;
            }
        };

        document.addEventListener('DOMContentLoaded', function () {
            const saved = localStorage.getItem('gtm_finance_palette');
            if (saved && financePalettes[saved]) {
                const activeName = document.getElementById('active-palette-name');
                if (activeName) {
                    activeName.textContent = financePalettes[saved].name;
                }
            }

            document.addEventListener('click', function (e) {
                const btn = document.getElementById('palette-switcher-btn');
                const dropdown = document.getElementById('palette-switcher-dropdown');
                if (btn && dropdown && !btn.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
                const drawer = document.getElementById('finance-mobile-drawer');
                if (drawer && e.target === drawer) {
                    toggleMobileSidebar(false);
                }
            });

            const navLinks = document.querySelectorAll('.nav-section-link');
            const panels = document.querySelectorAll('.section-panel');

            function activateSection(sectionName) {
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
            }

            navLinks.forEach(link => {
                link.addEventListener('click', function (event) {
                    const target = this.dataset.targetSection;
                    if (!target) {
                        return;
                    }

                    event.preventDefault();
                    toggleMobileSidebar(false);
                    activateSection(target);
                    history.replaceState(null, '', '#' + target);
                });
            });

            const initialHash = window.location.hash.replace('#', '');
            if (initialHash && document.querySelector('[data-section="' + initialHash + '"]')) {
                activateSection(initialHash);
            }

            // Finance Overview Charts
            const finData = @json($financeCharts ?? null);
            if (finData && typeof ApexCharts !== 'undefined') {
                // 1. Cashflow Inflow vs Outflow
                const cfEl = document.querySelector("#finance-cashflow-chart");
                if (cfEl && finData.cashflow) {
                    new ApexCharts(cfEl, {
                        chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                        series: [
                            { name: 'Cash Inflow (Credit)', data: finData.cashflow.inflows || [] },
                            { name: 'Disbursements (Debit)', data: finData.cashflow.outflows || [] }
                        ],
                        colors: ['#1d3c34', '#e11d48'],
                        stroke: { curve: 'smooth', width: 2.5 },
                        fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
                        xaxis: { categories: finData.cashflow.months || [], labels: { style: { fontSize: '10px' } } },
                        yaxis: {
                            labels: {
                                formatter: (val) => val >= 1000000 ? (val/1000000).toFixed(1)+'M ETB' : (val >= 1000 ? (val/1000).toFixed(0)+'K ETB' : val+' ETB'),
                                style: { fontSize: '10px' }
                            },
                            min: 0
                        },
                        legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px' },
                        grid: { borderColor: '#f1f5f9' },
                        tooltip: {
                            y: { formatter: (val) => new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(val) + ' ETB' }
                        }
                    }).render();
                }

                // 2. Revenue Categories Donut
                const catEl = document.querySelector("#finance-categories-chart");
                if (catEl && finData.categories_donut) {
                    new ApexCharts(catEl, {
                        chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                        series: finData.categories_donut.series || [],
                        labels: finData.categories_donut.labels || [],
                        colors: ['#1d3c34', '#d4af37', '#3b82f6', '#102b25', '#9b6c17'],
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '65%',
                                    labels: {
                                        show: true,
                                        total: {
                                            show: true,
                                            label: 'Total Revenue',
                                            formatter: () => new Intl.NumberFormat('en-US', { notation: 'compact' }).format(finData.categories_donut.total || 0) + ' ETB'
                                        }
                                    }
                                }
                            }
                        },
                        legend: { position: 'bottom', fontSize: '10px' }
                    }).render();
                }
            }
        });
    </script>
    @include('layouts.partials.sidebar-scroll-preserver')
</body>
</html>
