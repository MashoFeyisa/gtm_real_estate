<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 Server Error — {{ config('app.name', 'GTM Real Estate') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-styles')
</head>
<body class="bg-[#f5f0e7] font-['Plus_Jakarta_Sans'] text-slate-800 antialiased min-h-screen flex items-center justify-center p-6">
    <div class="max-w-xl w-full text-center">
        <div class="rounded-3xl border border-[#d9cab3] bg-white p-8 md:p-12 shadow-xl">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-[#1d3c34] text-3xl text-[#d4af37] ring-4 ring-[#d9cab3]/50">
                ⚙️
            </div>
            <span class="mt-6 inline-block rounded-full bg-rose-100 px-3.5 py-1 text-xs font-black uppercase tracking-wider text-rose-800">
                500 &bull; Server Error
            </span>
            <h1 class="mt-4 text-3xl font-black text-[#1d3c34] tracking-tight">Something Went Wrong</h1>
            <p class="mt-3 text-sm leading-relaxed text-slate-600">
                Our servers experienced an unexpected issue while handling your request. The engineering team has been notified. Please try refreshing or return shortly.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('home') }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-full bg-[#1d3c34] px-6 py-3 text-xs font-bold text-white transition hover:bg-[#254d43] shadow-sm">
                    <span>Return to Home</span>
                </a>
                <button type="button" onclick="window.location.reload()" class="w-full sm:w-auto inline-flex items-center justify-center rounded-full border border-[#d9cab3] bg-[#f8f3eb] px-6 py-3 text-xs font-bold text-[#1d3c34] transition hover:bg-[#e7ddca] cursor-pointer">
                    <span>↻ Refresh Page</span>
                </button>
            </div>
        </div>

        <p class="mt-6 text-xs text-slate-500">
            &copy; {{ date('Y') }} {{ config('app.name', 'GTM Real Estate') }} &bull; Addis Ababa, Ethiopia
        </p>
    </div>
</body>
</html>
