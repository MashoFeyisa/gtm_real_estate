@php
    /**
     * Admin-maintained exchange rates, stored as "ETB per 1 unit" of the
     * foreign currency. The frontend expects "foreign units per 1 ETB", so we
     * invert each rate before handing it to the currency engine.
     */
    $currencyEtbRates = [
        'USD' => (float) \App\Models\SiteSetting::get('currency_rate_usd', 125),
        'EUR' => (float) \App\Models\SiteSetting::get('currency_rate_eur', 135),
        'GBP' => (float) \App\Models\SiteSetting::get('currency_rate_gbp', 160),
        'AED' => (float) \App\Models\SiteSetting::get('currency_rate_aed', 34),
    ];

    $currencyRatesPerBirr = [];
    foreach ($currencyEtbRates as $currencyCode => $etbPerUnit) {
        if ($etbPerUnit > 0) {
            $currencyRatesPerBirr[$currencyCode] = 1 / $etbPerUnit;
        }
    }
@endphp
<script>
    window.GTM_CURRENCY_RATES = @json($currencyRatesPerBirr);
</script>

@include('layouts.partials.impersonation-banner')

<header class="sticky top-0 z-50 border-b border-[#0a1714] bg-[#102b25] shadow-md">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <img src="{{ $siteBrand['logoUrl'] }}" alt="{{ $siteBrand['name'] }} Logo" class="h-12 w-12 rounded-2xl bg-white/90 object-contain shadow-md ring-2 ring-white/20">
            <span class="text-2xl font-black tracking-tight text-white">
                {{ $siteBrand['name'] }}
            </span>
        </a>

        <div class="hidden items-center gap-7 md:flex">
            <a href="{{ route('home') }}" data-i18n="Home" class="text-sm font-semibold {{ request()->routeIs('home') || request()->routeIs('app.home') ? 'text-[#d4af37]' : 'text-white' }} transition hover:text-[#d4af37]">Home</a>
            <a href="{{ route('properties.index') }}" data-i18n="Properties" class="text-sm font-semibold {{ request()->routeIs('properties.*') ? 'text-[#d4af37]' : 'text-white' }} transition hover:text-[#d4af37]">Properties</a>
            <a href="{{ route('developments.index') }}" data-i18n="Developments" class="text-sm font-semibold {{ request()->routeIs('developments.*') ? 'text-[#d4af37]' : 'text-white' }} transition hover:text-[#d4af37]">Developments</a>
            <a href="{{ url('/app#news') }}" data-i18n="Blog & News" class="text-sm font-semibold text-white transition hover:text-[#d4af37]">Blog & News</a>
            <a href="{{ url('/app#contact') }}" data-i18n="Contact" class="text-sm font-semibold text-white transition hover:text-[#d4af37]">Contact</a>
        </div>

        <div class="hidden items-center gap-2.5 md:flex">
            {{-- Multi-Currency Switcher Dropdown --}}
            <div class="relative">
                <button type="button" id="currency-dropdown-toggle" class="flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1.5 text-xs font-bold text-white transition shadow-2xs cursor-pointer hover:bg-white/20" aria-label="Select Currency">
                    <span data-currency-active-flag>🇪🇹</span>
                    <span data-currency-active-code>ETB</span>
                    <svg class="h-3 w-3 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="currency-dropdown-menu" class="hidden absolute right-0 mt-2 w-44 rounded-2xl border border-[#d9cab3] bg-white p-1.5 shadow-xl z-50">
                    <div class="px-2 py-1 text-[10px] font-black uppercase tracking-wider text-[#587165]" data-i18n="Display Currency">Display Currency</div>
                    <button type="button" data-set-currency="ETB" class="flex w-full items-center justify-between rounded-xl px-2.5 py-1.5 text-xs font-semibold text-[#1d3c34] hover:bg-[#f3eadc] transition cursor-pointer">
                        <span class="flex items-center gap-2"><span>🇪🇹</span> <span>ETB (Birr)</span></span>
                        <span class="text-[10px] text-slate-400">Base</span>
                    </button>
                    <button type="button" data-set-currency="USD" class="flex w-full items-center justify-between rounded-xl px-2.5 py-1.5 text-xs font-semibold text-[#1d3c34] hover:bg-[#f3eadc] transition cursor-pointer">
                        <span class="flex items-center gap-2"><span>🇺🇸</span> <span>USD ($)</span></span>
                    </button>
                    <button type="button" data-set-currency="EUR" class="flex w-full items-center justify-between rounded-xl px-2.5 py-1.5 text-xs font-semibold text-[#1d3c34] hover:bg-[#f3eadc] transition cursor-pointer">
                        <span class="flex items-center gap-2"><span>🇪🇺</span> <span>EUR (€)</span></span>
                    </button>
                    <button type="button" data-set-currency="GBP" class="flex w-full items-center justify-between rounded-xl px-2.5 py-1.5 text-xs font-semibold text-[#1d3c34] hover:bg-[#f3eadc] transition cursor-pointer">
                        <span class="flex items-center gap-2"><span>🇬🇧</span> <span>GBP (£)</span></span>
                    </button>
                    <button type="button" data-set-currency="AED" class="flex w-full items-center justify-between rounded-xl px-2.5 py-1.5 text-xs font-semibold text-[#1d3c34] hover:bg-[#f3eadc] transition cursor-pointer">
                        <span class="flex items-center gap-2"><span>🇦🇪</span> <span>AED (Dirham)</span></span>
                    </button>
                </div>
            </div>

            {{-- Multi-Language Switcher Dropdown (Powered by Google Translate) --}}
            <div class="relative">
                <button type="button" id="language-dropdown-toggle" class="flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1.5 text-xs font-bold text-white transition shadow-2xs cursor-pointer hover:bg-white/20" aria-label="Select Language">
                    <span data-language-active-flag>🇬🇧</span>
                    <span data-language-active-code>EN</span>
                    <svg class="h-3 w-3 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="language-dropdown-menu" class="hidden absolute right-0 mt-2 w-48 rounded-2xl border border-[#d9cab3] bg-white p-1.5 shadow-xl z-50">
                    <div class="px-2 py-1 text-[10px] font-black uppercase tracking-wider text-[#587165]" data-i18n="Language">Language</div>
                    <button type="button" data-set-language="en" class="flex w-full items-center justify-between rounded-xl px-2.5 py-1.5 text-xs font-semibold text-[#1d3c34] hover:bg-[#f3eadc] transition cursor-pointer">
                        <span class="flex items-center gap-2"><span>🇬🇧</span> <span>English</span></span>
                        <span class="text-[10px] text-slate-400 font-mono">EN</span>
                    </button>
                    <button type="button" data-set-language="am" class="flex w-full items-center justify-between rounded-xl px-2.5 py-1.5 text-xs font-semibold text-[#1d3c34] hover:bg-[#f3eadc] transition cursor-pointer">
                        <span class="flex items-center gap-2"><span>🇪🇹</span> <span>አማርኛ (Amharic)</span></span>
                        <span class="text-[10px] text-slate-400 font-mono">አማ</span>
                    </button>
                    <button type="button" data-set-language="om" class="flex w-full items-center justify-between rounded-xl px-2.5 py-1.5 text-xs font-semibold text-[#1d3c34] hover:bg-[#f3eadc] transition cursor-pointer">
                        <span class="flex items-center gap-2"><span>🇪🇹</span> <span>Afaan Oromoo</span></span>
                        <span class="text-[10px] text-slate-400 font-mono">OM</span>
                    </button>
                    {{-- Hidden Google Translate Element Anchor (No visible widget or icons) --}}
                    <div id="google_translate_element" class="hidden" style="display: none !important;"></div>
                </div>
            </div>

            @auth
                @php
                    $userDashboardUrl = auth()->user()->dashboard_url;
                    $userDashboardLabel = match (auth()->user()->role) {
                        'admin', 'super_admin' => 'Dashboard',
                        'company', 'company_admin', 'real_estate_company', 'branch_manager' => 'Company Hub',
                        'finance_officer' => 'Finance Desk',
                        'receptionist' => 'Front Desk',
                        'agent' => 'Agent Portal',
                        'developer' => 'Developer Portal',
                        'campaign_officer' => 'Campaign Desk',
                        'verification_officer' => 'Verification Desk',
                        'verification_company' => 'Verification Portal',
                        'moderator' => 'Moderation Desk',
                        'customer_support' => 'Support Desk',
                        'property_manager' => 'Property Desk',
                        'cleaner', 'housekeeping' => 'Housekeeping Desk',
                        'professional', 'service_provider' => 'Services Desk',
                        'investor' => 'Investor Hub',
                        'host' => 'Host Hub',
                        'seller', 'landlord', 'property_owner' => 'Owner Hub',
                        'tenant', 'renter' => 'Tenant Hub',
                        'buyer' => 'Buyer Hub',
                        'traveler', 'guest' => 'Guest Hub',
                        default => auth()->user()->agentProfile ? 'Agent Portal' : 'Client Portal',
                    };
                @endphp
                <a href="{{ $userDashboardUrl }}" title="My Portal" data-i18n="{{ $userDashboardLabel }}" class="rounded-full border border-white/25 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">{{ $userDashboardLabel }}</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" data-i18n="Logout" class="rounded-full border border-white/25 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" data-i18n="Login" class="rounded-full border border-white/25 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">Login</a>
            @endauth
        </div>

        <button type="button" id="mobile-menu-toggle" class="rounded-xl border border-white/20 bg-white/10 p-2 text-white md:hidden" aria-label="Open menu" aria-expanded="false">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </nav>

    <div id="mobile-menu" class="hidden border-t border-[#0a1714] bg-[#102b25] px-6 py-4 md:hidden">
        <div class="flex flex-col gap-3">
            {{-- Mobile Currency Selector --}}
            <div class="flex items-center justify-between rounded-xl border border-white/15 bg-white/5 px-3 py-2">
                <span class="text-xs font-bold text-white" data-i18n="Display Currency">Currency</span>
                <div class="flex items-center gap-1">
                    <button type="button" data-set-currency="ETB" class="rounded-lg px-2 py-1 text-xs font-bold text-white hover:bg-white/10">ETB</button>
                    <button type="button" data-set-currency="USD" class="rounded-lg px-2 py-1 text-xs font-bold text-white hover:bg-white/10">USD</button>
                    <button type="button" data-set-currency="EUR" class="rounded-lg px-2 py-1 text-xs font-bold text-white hover:bg-white/10">EUR</button>
                    <button type="button" data-set-currency="GBP" class="rounded-lg px-2 py-1 text-xs font-bold text-white hover:bg-white/10">GBP</button>
                    <button type="button" data-set-currency="AED" class="rounded-lg px-2 py-1 text-xs font-bold text-white hover:bg-white/10">AED</button>
                </div>
            </div>

            {{-- Mobile Language Selector --}}
            <div class="flex items-center justify-between rounded-xl border border-white/15 bg-white/5 px-3 py-2">
                <span class="text-xs font-bold text-white" data-i18n="Language">Language</span>
                <div class="flex items-center gap-1">
                    <button type="button" data-set-language="en" class="rounded-lg px-2.5 py-1 text-xs font-bold text-white hover:bg-white/10 transition">EN</button>
                    <button type="button" data-set-language="am" class="rounded-lg px-2.5 py-1 text-xs font-bold text-white hover:bg-white/10 transition">አማርኛ</button>
                    <button type="button" data-set-language="om" class="rounded-lg px-2.5 py-1 text-xs font-bold text-white hover:bg-white/10 transition">Oromoo</button>
                </div>
                <div id="mobile_google_translate_element" class="hidden" style="display: none !important;"></div>
            </div>

            <a href="{{ route('home') }}" data-i18n="Home" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">Home</a>
            <a href="{{ route('properties.index') }}" data-i18n="Properties" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">Properties</a>
            <a href="{{ route('developments.index') }}" data-i18n="Developments" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">Developments</a>
            <a href="{{ route('agents') }}" data-i18n="Agents" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">Agents</a>
            <a href="{{ url('/app#news') }}" data-i18n="Blog & News" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">Blog & News</a>
            <a href="{{ route('careers') }}" data-i18n="Careers & Jobs" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">Careers & Jobs</a>
            <a href="{{ url('/app#contact') }}" data-i18n="Contact" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">Contact</a>
            <div class="mt-2 flex flex-col gap-2 border-t border-white/15 pt-4">
                @auth
                    @php
                        $mobileDashboardUrl = auth()->user()->dashboard_url;
                        $mobileDashboardLabel = match (auth()->user()->role) {
                            'admin', 'super_admin' => 'Dashboard',
                            'company', 'company_admin', 'real_estate_company', 'branch_manager' => 'Company Hub',
                            'finance_officer' => 'Finance Desk',
                            'receptionist' => 'Front Desk',
                            'agent' => 'Agent Portal',
                            'developer' => 'Developer Portal',
                            'campaign_officer' => 'Campaign Desk',
                            'verification_officer' => 'Verification Desk',
                            'verification_company' => 'Verification Portal',
                            'moderator' => 'Moderation Desk',
                            'customer_support' => 'Support Desk',
                            'property_manager' => 'Property Desk',
                            'cleaner', 'housekeeping' => 'Housekeeping Desk',
                            'professional', 'service_provider' => 'Services Desk',
                            'investor' => 'Investor Hub',
                            'host' => 'Host Hub',
                            'seller', 'landlord', 'property_owner' => 'Owner Hub',
                            'tenant', 'renter' => 'Tenant Hub',
                            'buyer' => 'Buyer Hub',
                            'traveler', 'guest' => 'Guest Hub',
                            default => auth()->user()->agentProfile ? 'Agent Portal' : 'Client Portal',
                        };
                    @endphp
                    <a href="{{ $mobileDashboardUrl }}" data-i18n="{{ $mobileDashboardLabel }}" class="rounded-xl border border-white/25 px-3 py-2.5 text-center text-sm font-semibold text-white">{{ $mobileDashboardLabel }}</a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl border border-white/25 px-3 py-2.5 text-center text-sm font-semibold text-white">Login</a>
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
