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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-styles')
    @yield('styles')
</head>
<body class="bg-[#f5f0e7] font-['Plus_Jakarta_Sans'] text-slate-800 antialiased scroll-smooth">
    @include('layouts.partials.impersonation-banner')
    <div class="flex min-h-screen">
        {{-- Sticky Isolated Left Sidebar --}}
        <aside id="role-sidebar" class="dashboard-sidebar sidebar-scroll-area w-72 shrink-0 border-r border-[#d9cab3] bg-[#1d3c34] p-6 text-[#f8f3eb] flex flex-col justify-between" style="position: sticky; top: 0; height: 100dvh; overflow-y: auto;">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ $siteBrand['logoUrl'] ?? asset('images/luxury/hero-skyline.jpg') }}" alt="{{ $siteBrand['name'] ?? config('app.name', 'GTM Real Estate') }} Logo" class="h-10 w-10 rounded-xl object-contain ring-2 ring-[#d9cab3] bg-white/10">
                    <div>
                        <span class="text-xl font-black tracking-tight text-white block leading-tight">{{ $siteBrand['name'] ?? config('app.name', 'GTM Real Estate') }}</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">
                            @yield('portal_name', ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'Workspace')))
                        </span>
                    </div>
                </a>

                {{-- Sidebar Skeleton placeholder for layout height stability while permissions/badges evaluate --}}
                <div class="sidebar-skeleton hidden pointer-events-none opacity-0 h-0 overflow-hidden" aria-hidden="true">
                    <div class="h-9 w-full rounded-xl bg-white/5 animate-pulse mb-2"></div>
                    <div class="h-9 w-full rounded-xl bg-white/5 animate-pulse mb-2"></div>
                </div>

                <nav class="mt-8 space-y-2 min-h-[420px]" aria-label="Role dashboard navigation">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span>🏠</span>
                        <span>Home</span>
                    </a>

                    @hasSection('sidebar-nav')
                        @yield('sidebar-nav')
                    @else
                        @include('dashboards.partials.default-sidebar-nav')
                    @endif

                    @include('layouts.partials.authorized-sidebar-links')
                </nav>
            </div>

            @include('dashboard.partials.signed-in-user')
        </aside>

        {{-- Main Content Area --}}
        <main class="flex-1 px-6 py-8 md:px-10 md:py-10 max-w-7xl w-full">
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 shadow-xs flex items-center justify-between">
                    <span>✓ {{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 cursor-pointer">&times;</button>
                </div>
            @endif
            @if (session('error') || $errors->any())
                <div class="mb-6 rounded-2xl border border-rose-300 bg-rose-50 p-4 text-sm font-semibold text-rose-900 shadow-xs">
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
                <div id="overview" class="scroll-mt-6 mb-8 rounded-3xl border border-[#d9cab3] bg-gradient-to-r from-[#1d3c34] to-[#2d5d4d] p-6 text-white shadow-sm md:p-8">
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
            <footer class="mt-12 border-t border-[#e7ddca] pt-6 text-center text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} {{ $siteBrand['name'] ?? config('app.name', 'GTM Real Estate') }} &bull; Addis Ababa, Ethiopia &bull; Permission-Protected Multi-Actor Platform</p>
            </footer>
        </main>
    </div>

    @include('layouts.partials.sidebar-scroll-preserver')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navLinks = document.querySelectorAll('.dashboard-sidebar .nav-section-link, .dashboard-sidebar a[href^="#"]');
            const panels = document.querySelectorAll('.section-panel');

            function activateSection(targetId) {
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

                const targetEl = document.getElementById(targetId);
                if (targetEl && panels.length === 0) {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }

            navLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    const target = (this.dataset.targetSection || this.getAttribute('href') || '').replace(/^#/, '');
                    if (target) {
                        e.preventDefault();
                        activateSection(target);
                        if (history.pushState) {
                            history.pushState(null, null, '#' + target);
                        }
                    }
                });
            });

            if (window.location.hash) {
                const hashTarget = window.location.hash.substring(1);
                if (document.getElementById(hashTarget) || document.querySelector(`[data-section="${hashTarget}"]`)) {
                    activateSection(hashTarget);
                }
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
