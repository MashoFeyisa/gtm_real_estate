<header class="sticky top-0 z-50 border-b border-[#d9cab3] bg-[#f8f3eb]/95 backdrop-blur-md shadow-sm">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <img src="{{ asset($siteBrand['logo']) }}" alt="{{ $siteBrand['name'] }} Logo" class="h-12 w-12 rounded-2xl object-cover ring-2 ring-[#d9cab3] shadow-md">
            <span class="text-2xl font-black tracking-tight text-[#1c3d32]">
                {{ $siteBrand['name'] }}
            </span>
        </a>

        <div class="hidden items-center gap-7 md:flex">
            <a href="{{ route('home') }}" class="text-sm font-semibold {{ request()->routeIs('home') || request()->routeIs('app.home') ? 'text-[#0d241e]' : 'text-[#1d3c34]' }} transition hover:text-[#2e5a4c]">Home</a>
            <a href="{{ route('properties.index') }}" class="text-sm font-semibold {{ request()->routeIs('properties.*') ? 'text-[#0d241e]' : 'text-[#1d3c34]' }} transition hover:text-[#2e5a4c]">Properties</a>
            <a href="{{ url('/app#news') }}" class="text-sm font-semibold text-[#1d3c34] transition hover:text-[#2e5a4c]">Blog & News</a>
            <a href="{{ url('/app#contact') }}" class="text-sm font-semibold text-[#1d3c34] transition hover:text-[#2e5a4c]">Contact</a>
        </div>

        <div class="hidden items-center gap-3 md:flex">
            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('dashboard') }}" class="rounded-full border border-[#b9a98b] px-4 py-2 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">Dashboard</a>
                @elseif (auth()->user()->agentProfile)
                    <a href="{{ route('agent.portal') }}" class="rounded-full border border-[#b9a98b] px-4 py-2 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">My Portal</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-full border border-[#b9a98b] px-4 py-2 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="rounded-full border border-[#b9a98b] px-4 py-2 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">Login</a>
            @endauth
        </div>

        <button type="button" id="mobile-menu-toggle" class="rounded-xl border border-[#d9cab3] bg-[#fffaf2] p-2 text-[#1d3c34] md:hidden" aria-label="Open menu" aria-expanded="false">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </nav>

    <div id="mobile-menu" class="hidden border-t border-[#d9cab3] bg-[#fffaf2] px-6 py-4 md:hidden">
        <div class="flex flex-col gap-3">
            <a href="{{ route('home') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">Home</a>
            <a href="{{ route('properties.index') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">Properties</a>
            <a href="{{ route('agents') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">Agents</a>
            <a href="{{ url('/app#news') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">Blog & News</a>
            <a href="{{ url('/app#contact') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">Contact</a>
            <div class="mt-2 flex flex-col gap-2 border-t border-[#d9cab3] pt-4">
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('dashboard') }}" class="rounded-xl border border-[#b9a98b] px-3 py-2.5 text-center text-sm font-semibold text-[#1d3c34]">Dashboard</a>
                    @elseif (auth()->user()->agentProfile)
                        <a href="{{ route('agent.portal') }}" class="rounded-xl border border-[#b9a98b] px-3 py-2.5 text-center text-sm font-semibold text-[#1d3c34]">My Portal</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-[#1d3c34] px-3 py-2.5 text-sm font-semibold text-white">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl border border-[#b9a98b] px-3 py-2.5 text-center text-sm font-semibold text-[#1d3c34]">Login</a>
                @endauth
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('mobile-menu-toggle');
        const menu = document.getElementById('mobile-menu');

        if (toggle && menu) {
            toggle.addEventListener('click', function () {
                const isOpen = !menu.classList.toggle('hidden');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }
    });
</script>
