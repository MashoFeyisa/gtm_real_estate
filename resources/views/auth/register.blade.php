<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Client Account &bull; {{ $siteBrand['name'] ?? 'GTM Real Estate' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        .font-serif-luxury {
            font-family: 'Playfair Display', Georgia, serif;
        }
        @keyframes auth-panel-in {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .auth-panel-in {
            animation: auth-panel-in 0.55s cubic-bezier(0.22, 0.61, 0.36, 1) both;
        }
    </style>
</head>
<body class="min-h-screen bg-[#f5f0e7] text-slate-800 antialiased flex flex-col justify-between selection:bg-[#1d3c34] selection:text-white">
    {{-- Top Utility Header --}}
    <header class="w-full px-5 py-4 sm:px-8">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="group inline-flex items-center gap-3 transition">
                <img src="{{ $siteBrand['logoUrl'] }}" alt="{{ $siteBrand['name'] }} Logo" class="h-11 w-11 rounded-xl bg-white object-contain ring-2 ring-[#d9cab3] shadow-xs group-hover:scale-105 transition-transform">
                <span class="leading-tight">
                    <span class="block text-base font-extrabold tracking-tight text-[#1d3c34]">{{ $siteBrand['name'] ?: 'GTM Real Estate' }}</span>
                    <span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#9b6c17]">Ethiopian Prime Properties</span>
                </span>
            </a>

            <a href="{{ route('home') }}" class="group inline-flex items-center gap-1.5 rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-bold text-[#1d3c34] shadow-2xs transition hover:border-[#1d3c34] hover:bg-[#fdfbf7]">
                <svg class="h-4 w-4 text-[#9b6c17] group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Home</span>
            </a>
        </div>
    </header>

    {{-- Main Auth Workspace --}}
    <main class="mx-auto flex w-full max-w-6xl flex-1 items-center px-4 py-6 sm:px-6 md:py-10">
        <div class="auth-panel-in my-auto grid w-full overflow-hidden rounded-[2rem] border border-[#d9cab3]/80 bg-white shadow-[0_25px_70px_rgba(29,60,52,0.14)] lg:grid-cols-12">

            {{-- Left Column: Hero Client Key Handover Showcase --}}
            <section class="group relative flex min-h-[430px] flex-col overflow-hidden bg-[#102b25] text-white lg:col-span-5 lg:min-h-[740px]">
                <img src="{{ asset('images/auth-client-key.jpg') }}" alt="Homeowners receiving keys to their luxury residence" class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-1000 ease-out group-hover:scale-105">

                {{-- Dual Gradient Masks for High Contrast & Readability --}}
                <div class="absolute inset-0 bg-gradient-to-b from-[#102b25]/90 via-[#102b25]/45 to-[#102b25]/90 pointer-events-none"></div>
                <div class="absolute inset-y-0 right-0 hidden w-px bg-gradient-to-b from-transparent via-[#d4af37]/60 to-transparent lg:block pointer-events-none"></div>

                <div class="relative z-10 flex flex-1 flex-col justify-between gap-10 p-6 sm:p-9">
                    {{-- Top: Badges + Heading --}}
                    <div class="space-y-5">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-black/45 px-3.5 py-1.5 text-xs font-bold text-[#f4ebd9] backdrop-blur-md">
                                <span class="h-2 w-2 rounded-full bg-[#d4af37] animate-pulse"></span>
                                Free Client Membership
                            </span>

                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#d4af37] px-3 py-1.5 text-[11px] font-black uppercase tracking-wider text-[#102b25]">
                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L1.077 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                Prime Homeownership
                            </span>
                        </div>

                        <div class="space-y-2.5">
                            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-[#d4af37]">Start Your Real Estate Journey</p>
                            <h1 class="font-serif-luxury text-[1.75rem] font-semibold leading-[1.15] text-white drop-shadow-md sm:text-4xl lg:text-[2.5rem]">
                                Unlock the Door to Your Dream Home.
                            </h1>
                            <p class="max-w-sm text-sm leading-relaxed text-[#dfeee4]">
                                Join the verified GTM client list and get first access to new listings before they reach the market.
                            </p>
                        </div>
                    </div>

                    {{-- Bottom: Member Benefits --}}
                    <div class="inline-flex flex-wrap items-center gap-x-4 gap-y-2 rounded-2xl border border-white/20 bg-black/45 px-4 py-2.5 text-xs font-semibold text-[#f4ebd9] backdrop-blur-md self-start">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-[#d4af37]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L1.077 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            Saved Homes
                        </span>
                        <span class="text-white/30">&bull;</span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-[#d4af37]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Private Viewings
                        </span>
                        <span class="text-white/30">&bull;</span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-[#d4af37]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0110 1.944 11.954 11.954 0 0117.834 5c.11.64.166 1.299.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.702.057-1.361.166-2.001zm4.274 10.03a3 3 0 014.532 0 2.999 2.999 0 002.998 0 4.5 4.5 0 10-6.528 0 4.5 4.5 0 001.998 0z" clip-rule="evenodd"/></svg>
                            Verified Offers
                        </span>
                    </div>
                </div>
            </section>

            {{-- Right Column: Registration Form --}}
            <section class="flex flex-col justify-center bg-white p-6 sm:p-9 lg:col-span-7 lg:p-12">
                <div class="mx-auto w-full max-w-lg">
                    {{-- Mode Switch: Sign In / Create Account --}}
                    <div class="flex items-center justify-between gap-3">
                        <nav class="inline-flex rounded-full border border-[#d9cab3] bg-[#f5f0e7] p-1" aria-label="Authentication">
                            <a href="{{ route('login') }}" id="btn-goto-signin" class="rounded-full px-5 py-2 text-xs font-bold text-[#587165] transition hover:text-[#1d3c34]">Sign In</a>
                            <a href="{{ route('register') }}" aria-current="page" class="rounded-full bg-[#1d3c34] px-5 py-2 text-xs font-extrabold text-white shadow-sm transition">Create Account</a>
                        </nav>

                        <span class="hidden items-center gap-1.5 text-[11px] font-bold text-[#587165] sm:inline-flex">
                            <svg class="h-3.5 w-3.5 text-[#9b6c17]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/></svg>
                            Free Forever
                        </span>
                    </div>

                    {{-- Form Header --}}
                    <div class="mt-8">
                        <p class="text-[11px] font-black uppercase tracking-[0.25em] text-[#9b6c17]">New Client Profile</p>
                        <h2 class="mt-2 font-serif-luxury text-3xl font-semibold text-[#102b25] sm:text-[2.1rem]">Create Account</h2>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">Free membership — save homes, book private viewings and track every offer in one place.</p>
                        <div class="mt-5 h-px bg-gradient-to-r from-[#d4af37] via-[#d9cab3] to-transparent"></div>
                    </div>

                    {{-- Session Prompt Alert (Preserves exact prompt for test compatibility) --}}
                    @if (session('signup_prompt'))
                        <div role="alert" class="mt-6 flex items-start gap-2.5 rounded-2xl border border-[#d4af37]/60 bg-[#fff9ea] p-4 text-xs text-[#715418]">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-[#9b6c17]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" clip-rule="evenodd"/></svg>
                            <div>
                                <span class="block font-black text-xs uppercase tracking-wider text-[#9b6c17] mb-0.5">Register / Login to continue</span>
                                <p class="font-semibold leading-relaxed">{{ session('signup_prompt') }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Validation Errors --}}
                    @if ($errors->any())
                        <div role="alert" class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800">
                            <div class="flex items-center gap-2 font-bold">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M18 10A8 8 0 1110 2a8 8 0 018 8zm-7 4a1 1 0 100-2 1 1 0 000 2zm1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>Please check the following:</span>
                            </div>
                            <ul class="mt-1 list-disc pl-5 space-y-0.5 leading-relaxed text-rose-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Register Form --}}
                    <form method="POST" action="{{ route('register.store') }}" class="mt-7 space-y-4">
                        @csrf

                        {{-- Full Name Input --}}
                        <div>
                            <label for="name" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Full Name <span class="text-[#9b6c17]">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                                    autocomplete="name"
                                    placeholder="e.g. Almaz Kebede"
                                    class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfbf7] py-3 pl-11 pr-4 text-sm font-medium text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-[#1d3c34] focus:bg-white focus:ring-4 focus:ring-[#1d3c34]/20">
                            </div>
                        </div>

                        {{-- Email Address Input --}}
                        <div>
                            <label for="email" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Email Address <span class="text-[#9b6c17]">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                                </span>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                                    autocomplete="email"
                                    placeholder="client@example.com"
                                    class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfbf7] py-3 pl-11 pr-4 text-sm font-medium text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-[#1d3c34] focus:bg-white focus:ring-4 focus:ring-[#1d3c34]/20">
                            </div>
                        </div>

                        {{-- Password with Toggle --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Password <span class="text-[#9b6c17]">*</span></label>
                                <span class="text-[11px] font-semibold text-slate-400">Min 8 characters</span>
                            </div>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                                <input id="password" name="password" type="password" required
                                    autocomplete="new-password"
                                    placeholder="Create a secure password"
                                    class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfbf7] py-3 pl-11 pr-11 text-sm font-medium text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-[#1d3c34] focus:bg-white focus:ring-4 focus:ring-[#1d3c34]/20">

                                <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-[#1d3c34] focus:outline-none transition cursor-pointer" aria-label="Toggle password visibility">
                                    <svg id="eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eye-slash-icon" class="hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Live Password Strength Meter --}}
                            <div id="pw-strength" class="mt-2.5 hidden space-y-1.5" aria-live="polite">
                                <div class="flex gap-1.5">
                                    <span data-pw-seg class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors"></span>
                                    <span data-pw-seg class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors"></span>
                                    <span data-pw-seg class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors"></span>
                                    <span data-pw-seg class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors"></span>
                                </div>
                                <div class="flex items-center justify-between gap-3 text-[11px] font-semibold">
                                    <span id="pw-strength-label" class="text-slate-400">Password strength</span>
                                    <span id="pw-strength-hint" class="text-slate-400">8+ chars, letter &amp; number</span>
                                </div>
                            </div>
                        </div>

                        {{-- Password Confirmation --}}
                        <div>
                            <label for="password_confirmation" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Confirm Password <span class="text-[#9b6c17]">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </span>
                                <input id="password_confirmation" name="password_confirmation" type="password" required
                                    autocomplete="new-password"
                                    placeholder="Re-enter your password"
                                    class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfbf7] py-3 pl-11 pr-4 text-sm font-medium text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-[#1d3c34] focus:bg-white focus:ring-4 focus:ring-[#1d3c34]/20">
                            </div>
                        </div>

                        {{-- Submit Button: Sign Up / Create Account --}}
                        <button type="submit" id="btn-submit-signup" class="mt-2 flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#1d3c34] px-5 py-3.5 text-sm font-black text-white shadow-[0_12px_30px_-10px_rgba(29,60,52,0.55)] transition hover:-translate-y-0.5 hover:bg-[#254d43] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#d4af37] active:translate-y-0">
                            <span>Create My Account</span>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3"/></svg>
                        </button>

                        {{-- Terms & Policies --}}
                        <p class="text-center text-[11px] leading-relaxed text-slate-400 pt-1">
                            By creating an account, you agree to our
                            <a href="{{ route('terms') }}" class="font-bold text-[#1d3c34] underline hover:text-[#9b6c17]">Terms of Service</a>
                            and
                            <a href="{{ route('privacy-policy') }}" class="font-bold text-[#1d3c34] underline hover:text-[#9b6c17]">Privacy Policy</a>.
                        </p>

                        {{-- Security Footnote --}}
                        <p class="flex items-center justify-center gap-1.5 pt-1 text-[11px] font-semibold text-slate-400">
                            <svg class="h-3.5 w-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                            <span>Protected by 256-bit SSL encryption</span>
                        </p>
                    </form>
                </div>
            </section>
        </div>
    </main>

    {{-- Bottom Footnote --}}
    <footer class="w-full px-5 py-5">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 text-xs text-slate-500 sm:flex-row">
            <p>&copy; {{ date('Y') }} {{ $siteBrand['name'] ?: 'GTM Real Estate' }}. All rights reserved. Handcrafted luxury living in Ethiopia.</p>
            <nav class="flex flex-wrap items-center justify-center gap-x-5 gap-y-1.5 font-semibold">
                <a href="{{ route('home') }}" class="transition hover:text-[#1d3c34]">Browse Listings</a>
                <a href="{{ route('terms') }}" class="transition hover:text-[#1d3c34]">Terms of Service</a>
                <a href="{{ route('privacy-policy') }}" class="transition hover:text-[#1d3c34]">Privacy Policy</a>
            </nav>
        </div>
    </footer>

    {{-- Password Visibility Toggle + Strength Meter Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePasswordBtn = document.getElementById('toggle-password');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeSlashIcon = document.getElementById('eye-slash-icon');

            if (togglePasswordBtn && passwordInput) {
                togglePasswordBtn.addEventListener('click', function () {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    if (eyeIcon && eyeSlashIcon) {
                        eyeIcon.classList.toggle('hidden', isPassword);
                        eyeSlashIcon.classList.toggle('hidden', !isPassword);
                    }
                });
            }

            const strengthWrap = document.getElementById('pw-strength');
            const strengthLabel = document.getElementById('pw-strength-label');
            const strengthHint = document.getElementById('pw-strength-hint');
            const segments = strengthWrap ? Array.from(strengthWrap.querySelectorAll('[data-pw-seg]')) : [];

            const levels = [
                { score: 1, fill: 'bg-rose-500', label: 'Weak', hint: 'Add length and variety', labelClass: 'text-rose-600' },
                { score: 2, fill: 'bg-amber-400', label: 'Fair', hint: 'Add a number or symbol', labelClass: 'text-amber-600' },
                { score: 3, fill: 'bg-emerald-400', label: 'Good', hint: 'Nearly there', labelClass: 'text-emerald-600' },
                { score: 4, fill: 'bg-emerald-600', label: 'Strong', hint: 'Great password', labelClass: 'text-emerald-700' },
            ];

            const resetMeter = function () {
                segments.forEach(function (segment) {
                    segment.className = 'h-1.5 flex-1 rounded-full bg-slate-200 transition-colors';
                });
                strengthLabel.className = 'text-slate-400';
                strengthLabel.textContent = 'Password strength';
                strengthHint.className = 'text-slate-400';
                strengthHint.textContent = '8+ chars, letter & number';
            };

            if (passwordInput && strengthWrap && segments.length) {
                passwordInput.addEventListener('input', function () {
                    const value = passwordInput.value;

                    if (!value) {
                        strengthWrap.classList.add('hidden');
                        resetMeter();
                        return;
                    }

                    strengthWrap.classList.remove('hidden');

                    let score = 0;
                    if (value.length >= 8) score += 1;
                    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score += 1;
                    if (/\d/.test(value)) score += 1;
                    if (/[^A-Za-z0-9]/.test(value)) score += 1;
                    score = Math.max(1, Math.min(score, 4));

                    const level = levels[score - 1];

                    segments.forEach(function (segment, index) {
                        segment.className = 'h-1.5 flex-1 rounded-full transition-colors ' + (index < score ? level.fill : 'bg-slate-200');
                    });

                    strengthLabel.className = level.labelClass;
                    strengthLabel.textContent = level.label;
                    strengthHint.className = 'text-slate-500';
                    strengthHint.textContent = level.hint;
                });
            }
        });
    </script>
</body>
</html>
