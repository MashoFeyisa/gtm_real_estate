<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Workspace') — {{ $siteBrand['name'] ?? config('app.name', 'GTM Real Estate') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
    <script>
        if (typeof Chart === 'undefined') {
            const chartScript = document.createElement('script');
            chartScript.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js';
            document.head.appendChild(chartScript);
        }
    </script>
    @include('layouts.partials.theme-styles')
    @yield('styles')
</head>
<body class="bg-[#f5f0e7] font-['Plus_Jakarta_Sans'] text-slate-800 antialiased scroll-smooth dark:bg-slate-950 dark:text-slate-100">
    @include('layouts.partials.impersonation-banner')

    {{-- Mobile Sidebar Backdrop --}}
    <div id="sidebar-backdrop" class="fixed inset-0 z-40 bg-black/50 backdrop-blur-xs hidden md:hidden" onclick="toggleMobileSidebar(false)"></div>

    <div class="flex min-h-screen">
        {{-- Sticky Isolated Left Sidebar --}}
        <aside id="role-sidebar" class="dashboard-sidebar sidebar-scroll-area w-72 shrink-0 border-r border-[#d9cab3] bg-[#1d3c34] p-6 text-[#f8f3eb] flex flex-col justify-between fixed md:sticky z-50 md:z-auto -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out dark:border-slate-800" style="position: sticky; top: 0; height: 100dvh; overflow-y: auto;">
            <div class="flex-1 flex flex-col min-h-0">
                {{-- Brand Header --}}
                <div class="flex items-center justify-between pb-3 border-b border-white/10">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <img src="{{ $siteBrand['logoUrl'] ?? asset('images/luxury/hero-skyline.jpg') }}" alt="{{ $siteBrand['name'] ?? config('app.name', 'GTM Real Estate') }} Logo" class="h-9 w-9 rounded-xl object-contain ring-2 ring-[#d9cab3] bg-white/10" loading="lazy">
                        <div>
                            <span class="text-lg font-black tracking-tight text-white block leading-tight">{{ $siteBrand['name'] ?? config('app.name', 'GTM Real Estate') }}</span>
                            <span class="text-[9px] font-bold uppercase tracking-wider text-[#d4af37]">
                                @yield('portal_name', ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'Workspace')))
                            </span>
                        </div>
                    </a>
                    {{-- Mobile close button --}}
                    <button type="button" onclick="toggleMobileSidebar(false)" class="md:hidden text-slate-300 hover:text-white p-1 rounded-lg" aria-label="Close sidebar">
                        ✕
                    </button>
                </div>

                {{-- 1. Profile block at the top --}}
                @if (auth()->check())
                    @php
                        $user = auth()->user();
                        $personaConfig = collect(config('personas.personas', []))->first(function ($p) use ($user) {
                            return ($p['role'] ?? null) === $user->role || in_array($user->role ?? '', $p['legacy_roles'] ?? [], true);
                        });
                        $resolvedRoleBadge = $personaConfig['label'] ?? ucfirst(str_replace('_', ' ', $user->role ?? 'Member'));
                    @endphp
                    <div class="mt-4 rounded-2xl border border-white/10 bg-white/5 p-3 hover:bg-white/10 transition cursor-pointer group shadow-xs" onclick="openProfileModal('info')" title="Open Profile Management">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-[#dfeee4]/60 mb-1">Signed in</div>
                        <div class="flex items-center gap-3">
                            @if ($user->photo_url)
                                <img src="{{ $user->photo_url }}" alt="{{ $user->name }}" class="h-10 w-10 shrink-0 rounded-full object-cover ring-2 ring-[#d4af37] group-hover:ring-white transition" loading="lazy">
                            @else
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#102b25] text-xs font-black text-[#d4af37] ring-2 ring-[#d4af37] group-hover:ring-white transition">
                                    {{ $user->initials ?: 'GT' }}
                                </span>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-black text-white group-hover:text-[#d4af37] transition">{{ $user->name }}</p>
                                <span class="mt-0.5 inline-block rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-[#f8e7b9]">
                                    {{ $resolvedRoleBadge }}
                                </span>
                            </div>
                            <span class="text-xs text-slate-400 group-hover:text-white transition" title="Edit Profile">⚙️</span>
                        </div>
                    </div>
                @endif

                {{-- Sidebar Skeleton placeholder for layout height stability while permissions/badges evaluate --}}
                <div class="sidebar-skeleton hidden pointer-events-none opacity-0 h-0 overflow-hidden" aria-hidden="true">
                    <div class="h-9 w-full rounded-xl bg-white/5 animate-pulse mb-2"></div>
                    <div class="h-9 w-full rounded-xl bg-white/5 animate-pulse mb-2"></div>
                </div>

                {{-- Navigation Links --}}
                <nav class="mt-5 space-y-1.5 overflow-y-auto pr-1 flex-1 sidebar-scroll-area" aria-label="Role dashboard navigation">
                    {{-- 2. Home & Overview (Every user sees these two) --}}
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-bold text-[#dfeee4] transition hover:bg-white/10">
                        <span>🏠</span>
                        <span>Home</span>
                    </a>
                    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center gap-2.5 rounded-xl bg-white/10 px-3 py-2 text-xs font-bold text-white transition hover:bg-white/15">
                        <span>📊</span>
                        <span>Overview</span>
                    </a>

                    {{-- 3. Permission-driven menu groups --}}
                    @hasSection('sidebar-nav')
                        @yield('sidebar-nav')
                    @else
                        @include('dashboards.partials.default-sidebar-nav')
                    @endif

                    @include('layouts.partials.authorized-sidebar-links')
                </nav>

                {{-- 4. Logout at the bottom --}}
                <div class="pt-4 border-t border-white/10 mt-auto">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-white/10 px-3 py-2 text-xs font-bold text-[#f8f3eb] transition hover:bg-rose-600/80 hover:text-white cursor-pointer shadow-xs">
                            <span>🚪</span>
                            <span>Sign Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main Viewport Area --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Modern Sticky Top Bar --}}
            <header class="sticky top-0 z-30 flex h-16 w-full items-center justify-between border-b border-[#d9cab3]/60 bg-white/90 px-4 sm:px-6 backdrop-blur-md transition-colors dark:border-slate-800 dark:bg-slate-900/90 md:px-10">
                <div class="flex items-center gap-3">
                    {{-- Mobile Menu Hamburger --}}
                    <button type="button" onclick="toggleMobileSidebar(true)" class="md:hidden flex h-9 w-9 items-center justify-center rounded-xl border border-[#d9cab3] text-[#1d3c34] hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" aria-label="Open sidebar menu">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    {{-- Search Input with Shortcut --}}
                    <div class="relative w-48 sm:w-72 md:w-96">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            🔍
                        </span>
                        <input type="text" id="dashboard-search-input" placeholder="Search properties, records, requests..." class="w-full rounded-full border border-[#d9cab3] bg-[#fdfaf5] py-1.5 pl-9 pr-4 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-[#1d3c34] focus:outline-hidden focus:ring-1 focus:ring-[#1d3c34] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500">
                    </div>
                </div>

                {{-- Right Top Actions --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    {{-- Language Switcher Dropdown --}}
                    @php
                        $currentLocale = app()->getLocale();
                        $locales = [
                            'en' => ['flag' => '🇬🇧', 'name' => 'EN'],
                            'am' => ['flag' => '🇪🇹', 'name' => 'አማ'],
                            'om' => ['flag' => '🇪🇹', 'name' => 'OM'],
                        ];
                    @endphp
                    <div class="relative" id="topbar-lang-wrapper">
                        <button type="button" onclick="document.getElementById('topbar-lang-dropdown').classList.toggle('hidden')" class="flex h-9 items-center gap-1.5 rounded-full border border-[#d9cab3] bg-white px-3 text-xs font-bold text-[#1d3c34] hover:bg-[#f8f3eb] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer" aria-label="Switch Language">
                            <span>{{ $locales[$currentLocale]['flag'] ?? '🌐' }}</span>
                            <span>{{ $locales[$currentLocale]['name'] ?? strtoupper($currentLocale) }}</span>
                            <span class="text-[10px] text-slate-400">&dtrif;</span>
                        </button>
                        <div id="topbar-lang-dropdown" class="hidden absolute right-0 mt-2 w-36 rounded-2xl border border-[#d9cab3] bg-white p-1.5 shadow-xl z-50 dark:border-slate-700 dark:bg-slate-800">
                            @foreach ($locales as $code => $data)
                                <a href="{{ route('locale.switch', $code) }}" class="flex items-center justify-between rounded-xl px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-[#f8f3eb] dark:text-slate-200 dark:hover:bg-slate-700 {{ $currentLocale === $code ? 'bg-[#f8f3eb] text-[#1d3c34] font-bold dark:bg-slate-700' : '' }}">
                                    <span>{{ $data['flag'] }} {{ $code === 'en' ? 'English' : ($code === 'am' ? 'አማርኛ' : 'Afaan Oromo') }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $data['name'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Theme Toggle (Light / Dark) --}}
                    <button type="button" id="theme-toggle-btn" onclick="toggleTheme()" class="flex h-9 w-9 items-center justify-center rounded-full border border-[#d9cab3] bg-white text-[#1d3c34] hover:bg-[#f8f3eb] dark:border-slate-700 dark:bg-slate-800 dark:text-amber-400 dark:hover:bg-slate-700 cursor-pointer" title="Toggle Light / Dark mode" aria-label="Toggle Theme">
                        <span id="theme-icon-light" class="dark:hidden">🌙</span>
                        <span id="theme-icon-dark" class="hidden dark:inline">☀️</span>
                    </button>

                    {{-- Notifications Popover --}}
                    @php
                        $userUnreadCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
                    @endphp
                    <div class="relative" id="topbar-notifications-wrapper">
                        <button type="button" onclick="document.getElementById('topbar-notifications-dropdown').classList.toggle('hidden')" class="relative flex h-9 w-9 items-center justify-center rounded-full border border-[#d9cab3] bg-white text-[#1d3c34] hover:bg-[#f8f3eb] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer" aria-label="Notifications">
                            <span class="text-sm">🔔</span>
                            @if ($userUnreadCount > 0)
                                <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[9px] font-black text-white shadow-xs">
                                    {{ $userUnreadCount }}
                                </span>
                            @endif
                        </button>
                        <div id="topbar-notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 rounded-2xl border border-[#d9cab3] bg-white shadow-xl z-50 overflow-hidden dark:border-slate-700 dark:bg-slate-800">
                            <div class="flex items-center justify-between border-b border-slate-100 bg-[#fbf8f3] px-4 py-3 dark:border-slate-700 dark:bg-slate-800/80">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Notifications</span>
                                <span class="text-[10px] font-semibold text-[#d4af37]">{{ $userUnreadCount }} unread</span>
                            </div>
                            <div class="max-h-64 overflow-y-auto p-2 space-y-1 text-xs">
                                @if (auth()->check() && auth()->user()->unreadNotifications()->exists())
                                    @foreach (auth()->user()->unreadNotifications()->take(5)->get() as $n)
                                        <div class="rounded-xl p-2.5 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                            <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $n->data['title'] ?? 'System Update' }}</p>
                                            <p class="text-slate-500 text-[11px] mt-0.5">{{ $n->data['message'] ?? '' }}</p>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="py-6 text-center text-slate-400">
                                        <span class="text-2xl block mb-1">📭</span>
                                        No unread notifications
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- User Profile Menu --}}
                    @if (auth()->check())
                        <div class="relative" id="topbar-profile-wrapper">
                            <button type="button" onclick="document.getElementById('topbar-profile-dropdown').classList.toggle('hidden')" class="flex items-center gap-2 rounded-full border border-[#d9cab3] bg-white p-1 pr-2.5 hover:bg-[#f8f3eb] dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 cursor-pointer" aria-label="Profile menu">
                                @if (auth()->user()->photo_url)
                                    <img src="{{ auth()->user()->photo_url }}" alt="{{ auth()->user()->name }}" class="h-7 w-7 rounded-full object-cover ring-1 ring-[#d9cab3]" loading="lazy">
                                @else
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#1d3c34] text-xs font-black text-[#d4af37]">
                                        {{ auth()->user()->initials ?: 'GT' }}
                                    </span>
                                @endif
                                <span class="hidden sm:inline text-xs font-bold text-slate-800 dark:text-slate-200 max-w-[100px] truncate">
                                    {{ auth()->user()->name }}
                                </span>
                                <span class="text-[9px] text-slate-400">&dtrif;</span>
                            </button>
                            <div id="topbar-profile-dropdown" class="hidden absolute right-0 mt-2 w-56 rounded-2xl border border-[#d9cab3] bg-white p-2 shadow-xl z-50 dark:border-slate-700 dark:bg-slate-800">
                                <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-700">
                                    <p class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate">{{ auth()->user()->email }}</p>
                                    <span class="mt-1.5 inline-block rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-2 py-0.5 text-[9px] font-bold text-[#9b6c17] dark:text-[#f8e7b9] uppercase">
                                        {{ ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'Member')) }}
                                    </span>
                                </div>
                                <div class="py-1">
                                    <button type="button" onclick="openProfileModal('info'); document.getElementById('topbar-profile-dropdown').classList.add('hidden')" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f8f3eb] dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer">
                                        <span>⚙️</span>
                                        <span>Profile Settings</span>
                                    </button>
                                    <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f8f3eb] dark:text-slate-200 dark:hover:bg-slate-700">
                                        <span>🏠</span>
                                        <span>Marketplace Home</span>
                                    </a>
                                </div>
                                <div class="border-t border-slate-100 pt-1 dark:border-slate-700">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer">
                                            <span>🚪</span>
                                            <span>Sign Out</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </header>

            {{-- Main Content Area --}}
            <main class="flex-1 px-6 py-8 md:px-10 md:py-10 max-w-7xl w-full">
                {{-- Flash Messages --}}
                @if (session('success'))
                    <div class="mb-6 rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 shadow-xs flex items-center justify-between dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200">
                        <span>✓ {{ session('success') }}</span>
                        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 cursor-pointer">&times;</button>
                    </div>
                @endif
                @if (session('error') || $errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-300 bg-rose-50 p-4 text-sm font-semibold text-rose-900 shadow-xs dark:border-rose-800 dark:bg-rose-950/50 dark:text-rose-200">
                        @if (session('error'))
                            <p>{{ session('error') }}</p>
                        @endif
                        @if ($errors->any())
                            <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif

                @hasSection('dashboard-title')
                    <div id="workspace-header" class="scroll-mt-6 mb-8 rounded-3xl border border-[#d9cab3] bg-gradient-to-r from-[#1d3c34] to-[#2d5d4d] p-6 text-white shadow-sm md:p-8 dark:border-slate-800">
                        <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-3 py-1 text-xs font-bold text-[#f8e7b9] uppercase tracking-wider">
                            @yield('portal_name', 'Workspace Hub')
                        </span>
                        <h1 class="mt-2 text-2xl md:text-3xl font-black text-white">@yield('dashboard-title')</h1>
                        <p class="mt-1 text-sm text-slate-200">@yield('dashboard-subtitle')</p>
                    </div>
                @endif

                @yield('content')
                @yield('dashboard-content')

                {{-- Footer --}}
                <footer class="mt-12 border-t border-[#e7ddca] pt-6 text-center text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">
                    <p>&copy; {{ date('Y') }} {{ $siteBrand['name'] ?? config('app.name', 'GTM Real Estate') }} &bull; Addis Ababa, Ethiopia &bull; Permission-Protected Multi-Actor Platform</p>
                </footer>
            </main>
        </div>
    </div>

    @include('layouts.partials.sidebar-scroll-preserver')

    <script>
        function toggleMobileSidebar(open) {
            const sidebar = document.getElementById('role-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (open) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Close dropdowns on outside click
            document.addEventListener('click', function (e) {
                const langWrapper = document.getElementById('topbar-lang-wrapper');
                const langDropdown = document.getElementById('topbar-lang-dropdown');
                if (langWrapper && langDropdown && !langWrapper.contains(e.target)) {
                    langDropdown.classList.add('hidden');
                }

                const notifWrapper = document.getElementById('topbar-notifications-wrapper');
                const notifDropdown = document.getElementById('topbar-notifications-dropdown');
                if (notifWrapper && notifDropdown && !notifWrapper.contains(e.target)) {
                    notifDropdown.classList.add('hidden');
                }

                const profileWrapper = document.getElementById('topbar-profile-wrapper');
                const profileDropdown = document.getElementById('topbar-profile-dropdown');
                if (profileWrapper && profileDropdown && !profileWrapper.contains(e.target)) {
                    profileDropdown.classList.add('hidden');
                }
            });

            const navLinks = document.querySelectorAll('.dashboard-sidebar .nav-section-link, .dashboard-sidebar a[href^="#"]');
            const panels = document.querySelectorAll('.section-panel');

            function activateSection(targetId, updateHash = true) {
                if (!targetId) return;

                if (panels.length > 0) {
                    panels.forEach(panel => {
                        const match = panel.dataset.section === targetId || panel.id === targetId;
                        panel.classList.toggle('hidden', !match);
                    });
                }

                navLinks.forEach(link => {
                    const target = (link.dataset.targetSection || link.getAttribute('href') || '').replace(/^#/, '');
                    const isActive = target === targetId;
                    link.classList.toggle('bg-white/10', isActive);
                    link.classList.toggle('text-white', isActive);
                    link.classList.toggle('text-[#dfeee4]', !isActive);
                });

                if (updateHash && window.location.hash !== '#' + targetId) {
                    history.replaceState(null, '', '#' + targetId);
                }

                try {
                    sessionStorage.setItem('dashboard_active_section', targetId);
                    sessionStorage.setItem('dashboard_active_section_' + window.location.pathname, targetId);
                } catch (e) {}
            }

            window.activateSection = activateSection;

            document.addEventListener('click', function (e) {
                const link = e.target.closest('.dashboard-sidebar .nav-section-link, .dashboard-sidebar a[href^="#"], [data-target-section]');
                if (!link) return;

                const target = (link.dataset.targetSection || link.getAttribute('href') || '').replace(/^#/, '');
                if (!target || target.startsWith('http') || target.startsWith('/')) return;

                const targetPanel = document.getElementById(target) || document.querySelector(`[data-section="${target}"]`);
                if (targetPanel) {
                    e.preventDefault();
                    activateSection(target, true);
                }
            });

            let savedSessionSection = null;
            try {
                savedSessionSection = sessionStorage.getItem('dashboard_active_section_' + window.location.pathname) || sessionStorage.getItem('dashboard_active_section');
            } catch (err) {}

            const hashTarget = window.location.hash ? window.location.hash.substring(1) : null;
            const initialTarget = hashTarget || savedSessionSection || 'overview';

            if (document.getElementById(initialTarget) || document.querySelector(`[data-section="${initialTarget}"]`)) {
                activateSection(initialTarget, !!hashTarget);
            }

            window.addEventListener('hashchange', function () {
                const newHash = window.location.hash ? window.location.hash.substring(1) : '';
                if (newHash && (document.getElementById(newHash) || document.querySelector(`[data-section="${newHash}"]`))) {
                    activateSection(newHash, false);
                }
            });
        });
    </script>
    @include('dashboards.partials.profile-management-modal')
    @yield('scripts')
</body>
</html>
