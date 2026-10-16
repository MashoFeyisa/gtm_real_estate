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
<body class="bg-[var(--token-bg)] font-['Plus_Jakarta_Sans'] text-[var(--token-text)] antialiased scroll-smooth">
    @include('layouts.partials.impersonation-banner')

    {{-- Mobile Sidebar Backdrop --}}
    <div id="sidebar-backdrop" class="token-modal-overlay fixed inset-0 z-40 backdrop-blur-xs hidden md:hidden" onclick="toggleMobileSidebar(false)"></div>

    <div class="flex min-h-screen">
        {{-- Sticky Isolated Left Sidebar --}}
        <aside id="role-sidebar" class="dashboard-sidebar sidebar-scroll-area w-72 shrink-0 border-r border-[var(--token-sidebar-hover)] bg-[var(--token-sidebar-bg)] text-[var(--token-sidebar-text)] p-6 flex flex-col justify-between fixed md:sticky z-50 md:z-auto -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out" style="position: sticky; top: 0; height: 100dvh; overflow-y: auto;">
            <div class="flex-1 flex flex-col min-h-0">
                {{-- Brand Header --}}
                <div class="flex items-center justify-between pb-3 border-b border-[var(--token-sidebar-hover)]">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <img src="{{ $siteBrand['logoUrl'] ?? asset('images/luxury/hero-skyline.jpg') }}" alt="{{ $siteBrand['name'] ?? config('app.name', 'GTM Real Estate') }} Logo" class="h-9 w-9 rounded-xl object-contain ring-2 ring-[var(--token-sidebar-active)] bg-[var(--token-sidebar-hover)]" loading="lazy">
                        <div>
                            <span class="text-lg font-black tracking-tight text-[var(--token-sidebar-text)] block leading-tight">{{ $siteBrand['name'] ?? config('app.name', 'GTM Real Estate') }}</span>
                            <span class="text-[9px] font-bold uppercase tracking-wider text-[var(--token-accent-light)]">
                                @yield('portal_name', ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'Workspace')))
                            </span>
                        </div>
                    </a>
                    {{-- Mobile close button --}}
                    <button type="button" onclick="toggleMobileSidebar(false)" class="md:hidden text-[var(--token-sidebar-text)] hover:bg-[var(--token-sidebar-hover)] p-1 rounded-lg" aria-label="Close sidebar">
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
                    <div class="token-user-chip mt-4 cursor-pointer group" onclick="openProfileModal('info')" title="Open Profile Management">
                        <div class="token-user-chip-label mb-1">Signed in</div>
                        <div class="flex items-center gap-3">
                            @if ($user->photo_url)
                                <img src="{{ $user->photo_url }}" alt="{{ $user->name }}" class="h-10 w-10 shrink-0 rounded-full object-cover ring-2 ring-[var(--token-accent-light)] group-hover:ring-[var(--token-text-on-color)] transition" loading="lazy">
                            @else
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[var(--token-sidebar-bg)] text-xs font-black text-[var(--token-accent-light)] ring-2 ring-[var(--token-accent-light)] group-hover:ring-[var(--token-text-on-color)] transition">
                                    {{ $user->initials ?: 'GT' }}
                                </span>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-black text-[var(--token-sidebar-text)] group-hover:text-[var(--token-accent-light)] transition">{{ $user->name }}</p>
                                <span class="token-hero-chip mt-0.5 inline-block">
                                    {{ $resolvedRoleBadge }}
                                </span>
                            </div>
                            <span class="text-xs text-[var(--token-sidebar-text)] opacity-70 group-hover:opacity-100 transition" title="Edit Profile">⚙️</span>
                        </div>
                    </div>
                @endif

                {{-- Sidebar Skeleton placeholder for layout height stability while permissions/badges evaluate --}}
                <div class="sidebar-skeleton hidden pointer-events-none opacity-0 h-0 overflow-hidden" aria-hidden="true">
                    <div class="h-9 w-full rounded-xl bg-[var(--token-sidebar-hover)] animate-pulse mb-2"></div>
                    <div class="h-9 w-full rounded-xl bg-[var(--token-sidebar-hover)] animate-pulse mb-2"></div>
                </div>

                {{-- Navigation Links --}}
                <nav class="mt-5 space-y-1.5 overflow-y-auto pr-1 flex-1 sidebar-scroll-area" aria-label="Role dashboard navigation">
                    {{-- 2. Home & Overview (Every user sees these two) --}}
                    <x-sidebar-item href="{{ route('home') }}" icon="🏠" label="Home" />
                    <x-sidebar-item href="#overview" target-section="overview" icon="📊" label="Overview" :active="true" />

                    {{-- 3. Permission-driven menu groups --}}
                    @hasSection('sidebar-nav')
                        @yield('sidebar-nav')
                    @else
                        @include('dashboards.partials.default-sidebar-nav')
                    @endif

                    @include('layouts.partials.authorized-sidebar-links')
                </nav>

                {{-- 4. Logout at the bottom --}}
                <div class="pt-4 border-t border-[var(--token-sidebar-hover)] mt-auto">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="token-signout">
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
            <header class="sticky top-0 z-30 flex h-16 w-full items-center justify-between border-b border-[var(--token-border)] bg-[var(--token-surface)] px-4 sm:px-6 backdrop-blur-md transition-colors md:px-10">
                <div class="flex items-center gap-3">
                    {{-- Mobile Menu Hamburger --}}
                    <button type="button" onclick="toggleMobileSidebar(true)" class="token-btn token-btn-ghost md:hidden !p-0 h-9 w-9" aria-label="Open sidebar menu">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    {{-- Desktop Sidebar Collapse Toggle --}}
                    <button type="button" onclick="toggleSidebarCollapse()" class="token-btn token-btn-ghost hidden md:inline-flex !p-0 h-9 w-9" aria-label="Toggle sidebar collapse" title="Toggle sidebar collapse">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                    </button>

                    {{-- Search Input with Shortcut --}}
                    <div class="relative w-48 sm:w-72 md:w-96">
                        <span class="token-muted absolute inset-y-0 left-0 flex items-center pl-3">
                            🔍
                        </span>
                        <input type="text" id="dashboard-search-input" placeholder="Search properties, records, requests..." class="token-field rounded-full py-1.5 pl-9 pr-4 !text-xs font-medium">
                    </div>
                </div>

                {{-- Right Top Actions --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    {{-- Theme Toggle (Light / Dark) --}}
                    <button type="button" id="theme-toggle-btn" onclick="toggleTheme()" class="token-btn token-btn-secondary !rounded-full !p-0 h-9 w-9" title="Toggle Light / Dark mode" aria-label="Toggle Theme">
                        <span id="theme-icon-light" class="dark:hidden">🌙</span>
                        <span id="theme-icon-dark" class="hidden dark:inline">☀️</span>
                    </button>

                    {{-- Notifications Popover --}}
                    @php
                        $userUnreadCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
                    @endphp
                    <div class="relative" id="topbar-notifications-wrapper">
                        <button type="button" onclick="document.getElementById('topbar-notifications-dropdown').classList.toggle('hidden')" class="token-btn token-btn-secondary !rounded-full !p-0 h-9 w-9 relative" aria-label="Notifications">
                            <span class="text-sm">🔔</span>
                            @if ($userUnreadCount > 0)
                                <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full token-badge-danger !px-1 !text-[9px] !py-0">
                                    {{ $userUnreadCount }}
                                </span>
                            @endif
                        </button>
                        <div id="topbar-notifications-dropdown" class="token-dropdown hidden absolute right-0 mt-2 w-80 rounded-2xl shadow-xl z-50 overflow-hidden">
                            <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] bg-[var(--token-surface-subtle)] px-4 py-3">
                                <span class="text-xs font-bold">Notifications</span>
                                <span class="token-badge token-badge-accent !text-[10px]">{{ $userUnreadCount }} unread</span>
                            </div>
                            <div class="max-h-64 overflow-y-auto p-2 space-y-1 text-xs">
                                @if (auth()->check() && auth()->user()->unreadNotifications()->exists())
                                    @foreach (auth()->user()->unreadNotifications()->take(5)->get() as $n)
                                        <div class="rounded-xl p-2.5 hover:bg-[var(--token-surface-hover)]">
                                            <p class="font-semibold">{{ $n->data['title'] ?? 'System Update' }}</p>
                                            <p class="token-muted text-[11px] mt-0.5">{{ $n->data['message'] ?? '' }}</p>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="py-6 text-center token-muted">
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
                            <button type="button" onclick="document.getElementById('topbar-profile-dropdown').classList.toggle('hidden')" class="token-btn token-btn-secondary !rounded-full !p-1 !pr-2.5 gap-2 cursor-pointer" aria-label="Profile menu">
                                @if (auth()->user()->photo_url)
                                    <img src="{{ auth()->user()->photo_url }}" alt="{{ auth()->user()->name }}" class="h-7 w-7 rounded-full object-cover ring-1 ring-[var(--token-border)]" loading="lazy">
                                @else
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full token-btn-primary !rounded-full text-xs font-black">
                                        {{ auth()->user()->initials ?: 'GT' }}
                                    </span>
                                @endif
                                <span class="hidden sm:inline text-xs font-bold max-w-[100px] truncate">
                                    {{ auth()->user()->name }}
                                </span>
                                <span class="token-muted text-[9px]">&dtrif;</span>
                            </button>
                            <div id="topbar-profile-dropdown" class="token-dropdown hidden absolute right-0 mt-2 w-56 rounded-2xl p-2 shadow-xl z-50">
                                <div class="px-3 py-2 border-b border-[var(--token-border-subtle)]">
                                    <p class="text-xs font-bold truncate">{{ auth()->user()->name }}</p>
                                    <p class="token-muted text-[10px] truncate">{{ auth()->user()->email }}</p>
                                    <span class="token-badge token-badge-accent mt-1.5">
                                        {{ ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'Member')) }}
                                    </span>
                                </div>
                                <div class="py-1">
                                    <button type="button" onclick="openProfileModal('info'); document.getElementById('topbar-profile-dropdown').classList.add('hidden')" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold hover:bg-[var(--token-surface-hover)] cursor-pointer">
                                        <span>⚙️</span>
                                        <span>Profile Settings</span>
                                    </button>
                                    <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold hover:bg-[var(--token-surface-hover)]">
                                        <span>🏠</span>
                                        <span>Marketplace Home</span>
                                    </a>
                                </div>
                                <div class="border-t border-[var(--token-border-subtle)] pt-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-xs font-bold token-alert-danger !border-0 !bg-transparent hover:opacity-80 cursor-pointer">
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
                    <div class="mb-6 token-alert token-alert-success flex items-center justify-between">
                        <span>✓ {{ session('success') }}</span>
                        <button type="button" onclick="this.parentElement.remove()" class="font-bold hover:opacity-80 cursor-pointer">&times;</button>
                    </div>
                @endif
                @if (session('error') || $errors->any())
                    <div class="mb-6 token-alert token-alert-danger">
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
                    <div id="workspace-header" class="token-hero scroll-mt-6 mb-8">
                        <span class="token-hero-chip">
                            @yield('portal_name', 'Workspace Hub')
                        </span>
                        <h1 class="token-hero-title mt-2 text-2xl md:text-3xl">@yield('dashboard-title')</h1>
                        <p class="token-hero-sub mt-1 text-sm">@yield('dashboard-subtitle')</p>
                    </div>
                @endif

                @yield('content')
                @yield('dashboard-content')
                @include('dashboards.partials.granted-feature-panels')

                {{-- Footer --}}
                <footer class="mt-12 border-t border-[var(--token-border)] pt-6 text-center text-xs token-muted">
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

        function toggleSidebarCollapse() {
            const sidebar = document.getElementById('role-sidebar');
            if (!sidebar) return;
            const isCollapsed = sidebar.classList.toggle('is-collapsed');
            sidebar.toggleAttribute('data-collapsed', isCollapsed);
            try {
                localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
            } catch (e) {}
        }

        try {
            if (localStorage.getItem('sidebar_collapsed') === 'true') {
                const sidebar = document.getElementById('role-sidebar');
                if (sidebar) {
                    sidebar.classList.add('is-collapsed');
                    sidebar.setAttribute('data-collapsed', 'true');
                }
            }
        } catch (e) {}

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            window.dispatchEvent(new CustomEvent('themechanged', { detail: { isDark } }));
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Close dropdowns on outside click
            document.addEventListener('click', function (e) {
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
                    link.classList.toggle('token-nav-link-active', isActive);
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
