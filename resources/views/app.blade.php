<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', ($siteBrand['name'] ?? 'GTM Real Estate') . ' | Luxury Properties & Exclusive Residences')</title>
    <meta name="description" content="@yield('description', 'Discover prime luxury properties, villas, apartments, and exclusive commercial real estate with Addis Ababa\'s leading agency.')">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- OpenGraph Protocol for Social Sharing & WhatsApp --}}
    <meta property="og:site_name" content="{{ $siteBrand['name'] ?? 'GTM Real Estate' }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('og_title', View::yieldContent('title', ($siteBrand['name'] ?? 'GTM Real Estate') . ' | Luxury Properties'))">
    <meta property="og:description" content="@yield('og_description', View::yieldContent('description', 'Find your dream luxury property.'))">
    <meta property="og:image" content="@yield('og_image', asset('images/luxury/hero-skyline.jpg'))">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', View::yieldContent('title', ($siteBrand['name'] ?? 'GTM Real Estate') . ' | Luxury Properties'))">
    <meta name="twitter:description" content="@yield('og_description', View::yieldContent('description', 'Find your dream luxury property.'))">
    <meta name="twitter:image" content="@yield('og_image', asset('images/luxury/hero-skyline.jpg'))">

    {{-- Leaflet Interactive Maps (Local Bundled + Self-Hosted Fallback) --}}
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
    {{-- CDN reference fallback --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
    <script>
        window.GOOGLE_MAPS_API_KEY = @json(config('services.google.maps_api_key') ?: '');
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-styles')
    @stack('styles')
    @stack('json_ld')
</head>
<body class="bg-[#f6f1e8] text-slate-800 antialiased flex min-h-screen flex-col">
    @include('layouts.partials.header')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('layouts.partials.footer')
    @include('layouts.partials.property-sidebar-drawer')
    @include('layouts.partials.job-application-modal')

    {{-- Guest action gate: JS reads these to send account-less visitors to sign-up --}}
    <script>
        window.GTM_IS_GUEST = {{ auth()->check() ? 'false' : 'true' }};
        window.GTM_REGISTER_URL = @json(route('register'));
    </script>

    @include('layouts.partials.auth-required-modal')
    @include('layouts.partials.compare-floating-bar')
    @stack('scripts')

    {{-- Google Translate Integration --}}
    <script type="text/javascript">
        function googleTranslateElementInit() {
            if (window.google && window.google.translate && document.getElementById('google_translate_element')) {
                new window.google.translate.TranslateElement({
                    pageLanguage: 'en',
                    includedLanguages: 'en,am,om',
                    autoDisplay: false
                }, 'google_translate_element');
            }
        }
    </script>
    <script id="google-translate-script" type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" defer></script>
</body>
</html>
