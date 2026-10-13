<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password &bull; {{ $siteBrand['name'] ?? 'GTM Real Estate' }}</title>
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

            <a href="{{ route('login') }}" class="group inline-flex items-center gap-1.5 rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-bold text-[#1d3c34] shadow-2xs transition hover:border-[#1d3c34] hover:bg-[#fdfbf7]">
                <svg class="h-4 w-4 text-[#9b6c17] group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Sign In</span>
            </a>
        </div>
    </header>

    {{-- Main Auth Workspace --}}
    <main class="mx-auto flex w-full max-w-6xl flex-1 items-center px-4 py-6 sm:px-6 md:py-10">
        <div class="auth-panel-in my-auto grid w-full overflow-hidden rounded-[2rem] border border-[#d9cab3]/80 bg-white shadow-[0_25px_70px_rgba(29,60,52,0.14)] lg:grid-cols-12">

            {{-- Left Column: Secure Reset Showcase --}}
            <section class="group relative flex min-h-[430px] flex-col overflow-hidden bg-[#102b25] text-white lg:col-span-5 lg:min-h-[700px]">
                <img src="{{ asset('images/auth-client-key.jpg') }}" alt="Client receiving keys to their dream luxury house" class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-1000 ease-out group-hover:scale-105">

                {{-- Dual Gradient Masks for High Contrast & Readability --}}
                <div class="absolute inset-0 bg-gradient-to-b from-[#102b25]/90 via-[#102b25]/45 to-[#102b25]/90 pointer-events-none"></div>
                <div class="absolute inset-y-0 right-0 hidden w-px bg-gradient-to-b from-transparent via-[#d4af37]/60 to-transparent lg:block pointer-events-none"></div>

                <div class="relative z-10 flex flex-1 flex-col justify-between gap-10 p-6 sm:p-9">
                    {{-- Top: Badges + Heading --}}
                    <div class="space-y-5">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-black/45 px-3.5 py-1.5 text-xs font-bold text-[#f4ebd9] backdrop-blur-md">
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Single-Use Reset Link
                            </span>

                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#d4af37] px-3 py-1.5 text-[11px] font-black uppercase tracking-wider text-[#102b25]">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Encrypted Session
                            </span>
                        </div>

                        <div class="space-y-2.5">
                            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-[#d4af37]">Secure Reset &bull; Regain Access</p>
                            <h1 class="font-serif-luxury text-[1.75rem] font-semibold leading-[1.15] text-white drop-shadow-md sm:text-4xl lg:text-[2.5rem]">
                                Choose a new password.
                            </h1>
                            <p class="max-w-sm text-sm leading-relaxed text-[#dfeee4]">
                                Your reset link is single-use and expires shortly. Pick a strong password to keep your account protected.
                            </p>
                        </div>
                    </div>

                    {{-- Bottom: Security Assurance Badge --}}
                    <span class="inline-flex items-center gap-1.5 self-start rounded-2xl border border-white/20 bg-black/45 px-3.5 py-2 text-xs font-semibold text-[#f4ebd9] backdrop-blur-md">
                        <svg class="h-4 w-4 text-[#d4af37]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/></svg>
                        Verified Escrow
                    </span>
                </div>
            </section>

            {{-- Right Column: Modern Reset Form --}}
            <section class="flex flex-col justify-center bg-white p-6 sm:p-9 lg:col-span-7 lg:p-12">
                <div class="mx-auto w-full max-w-lg">
                    {{-- Mode Switch --}}
                    <div class="flex items-center justify-between gap-3">
                        <nav class="inline-flex rounded-full border border-[#d9cab3] bg-[#f5f0e7] p-1" aria-label="Authentication">
                            <a href="{{ route('login') }}" class="rounded-full px-5 py-2 text-xs font-bold text-[#587165] transition hover:text-[#1d3c34]">Sign In</a>
                            <a href="{{ route('password.request') }}" class="rounded-full px-5 py-2 text-xs font-bold text-[#587165] transition hover:text-[#1d3c34]">Request Link</a>
                        </nav>

                        <span class="hidden items-center gap-1.5 text-[11px] font-bold text-[#587165] sm:inline-flex">
                            <svg class="h-3.5 w-3.5 text-[#9b6c17]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                            Secure Reset
                        </span>
                    </div>

                    {{-- Form Header --}}
                    <div class="mt-8">
                        <p class="text-[11px] font-black uppercase tracking-[0.25em] text-[#9b6c17]">Secure Reset</p>
                        <h2 class="mt-2 font-serif-luxury text-3xl font-semibold text-[#102b25] sm:text-[2.1rem]">Choose a new password</h2>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">Pick a strong password to keep your account protected.</p>
                        <div class="mt-5 h-px bg-gradient-to-r from-[#d4af37] via-[#d9cab3] to-transparent"></div>
                    </div>

                    {{-- Error Feedback --}}
                    @if ($errors->any())
                        <div role="alert" class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800">
                            <div class="flex items-center gap-2 font-bold">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M18 10A8 8 0 1110 2a8 8 0 018 8zm-7 4a1 1 0 100-2 1 1 0 000 2zm1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>Reset Error</span>
                            </div>
                            <p class="mt-1 font-medium leading-relaxed text-rose-700">{{ $errors->first() }}</p>
                        </div>
                    @endif

                    {{-- The Reset Password Form --}}
                    <form method="POST" action="{{ route('password.update') }}" class="mt-7 space-y-4">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        {{-- Email Input --}}
                        <div>
                            <label for="email" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Email Address <span class="text-[#9b6c17]">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                                </span>
                                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autofocus
                                    autocomplete="email"
                                    placeholder="client@example.com"
                                    class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfbf7] py-3 pl-11 pr-4 text-sm font-medium text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-[#1d3c34] focus:bg-white focus:ring-4 focus:ring-[#1d3c34]/20">
                            </div>
                        </div>

                        {{-- New Password Input with Toggle --}}
                        <div>
                            <label for="password" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">New Password <span class="text-[#9b6c17]">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                                <input id="password" name="password" type="password" required
                                    autocomplete="new-password"
                                    placeholder="At least 8 characters with mixed case, numbers & symbols"
                                    class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfbf7] py-3 pl-11 pr-11 text-sm font-medium text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-[#1d3c34] focus:bg-white focus:ring-4 focus:ring-[#1d3c34]/20">

                                <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-[#1d3c34] focus:outline-none transition cursor-pointer" aria-label="Toggle password visibility" title="Show or hide password">
                                    <svg id="eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eye-slash-icon" class="hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Confirm Password Input with Toggle --}}
                        <div>
                            <label for="password_confirmation" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Confirm New Password <span class="text-[#9b6c17]">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </span>
                                <input id="password_confirmation" name="password_confirmation" type="password" required
                                    autocomplete="new-password"
                                    placeholder="Repeat the new password"
                                    class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfbf7] py-3 pl-11 pr-11 text-sm font-medium text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-[#1d3c34] focus:bg-white focus:ring-4 focus:ring-[#1d3c34]/20">

                                <button type="button" id="toggle-password-confirmation" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-[#1d3c34] focus:outline-none transition cursor-pointer" aria-label="Toggle confirm password visibility" title="Show or hide password">
                                    <svg id="eye-icon-confirmation" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eye-slash-icon-confirmation" class="hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" id="btn-submit-reset" class="mt-2 flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#1d3c34] px-5 py-3.5 text-sm font-black text-white shadow-[0_12px_30px_-10px_rgba(29,60,52,0.55)] transition hover:-translate-y-0.5 hover:bg-[#254d43] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#d4af37] active:translate-y-0">
                            <span>Reset Password &amp; Sign In</span>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3"/></svg>
                        </button>

                        {{-- Security Footnote --}}
                        <p class="flex items-center justify-center gap-1.5 pt-1 text-[11px] font-semibold text-slate-400">
                            <svg class="h-3.5 w-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                            <span>Protected by 256-bit SSL encryption</span>
                        </p>
                    </form>

                    {{-- Switch Links --}}
                    <div class="mt-6 border-t border-[#f0e7d8] pt-5 text-center">
                        <p class="text-xs text-slate-600">
                            Link expired or invalid?
                            <a href="{{ route('password.request') }}" class="font-black text-[#1d3c34] transition hover:text-[#9b6c17] hover:underline ml-1">Request a new link &rarr;</a>
                        </p>
                        <p class="mt-2 text-xs text-slate-600">
                            Remembered your password?
                            <a href="{{ route('login') }}" class="font-black text-[#1d3c34] transition hover:text-[#9b6c17] hover:underline ml-1">Sign In &rarr;</a>
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    {{-- Bottom Footnote --}}
    <footer class="w-full px-5 py-5">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 text-xs text-slate-500 sm:flex-row">
            <p>
                &copy; {{ date('Y') }} {{ $siteBrand['name'] ?: 'GTM Real Estate' }}. All rights reserved.
                <span class="mx-1 text-slate-300">&bull;</span>
                <span class="font-bold text-[#587165]">{{ $listingsCount ?? \App\Models\Property::count() }} Live Listings <span class="text-slate-300">&bull;</span> {{ $agentsCount ?? \App\Models\Agent::count() }} Verified Agents</span>
            </p>
            <nav class="flex flex-wrap items-center justify-center gap-x-5 gap-y-1.5 font-semibold">
                <a href="{{ route('home') }}" class="transition hover:text-[#1d3c34]">Browse Listings</a>
                <a href="{{ route('terms') }}" class="transition hover:text-[#1d3c34]">Terms of Service</a>
                <a href="{{ route('privacy-policy') }}" class="transition hover:text-[#1d3c34]">Privacy Policy</a>
            </nav>
        </div>
    </footer>

    {{-- Toggle Password Visibility Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const setupToggle = function (toggleId, inputId, eyeId, slashId) {
                const toggleBtn = document.getElementById(toggleId);
                const passwordInput = document.getElementById(inputId);
                const eyeIcon = document.getElementById(eyeId);
                const eyeSlashIcon = document.getElementById(slashId);

                if (toggleBtn && passwordInput) {
                    toggleBtn.addEventListener('click', function () {
                        const isPassword = passwordInput.getAttribute('type') === 'password';
                        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                        if (eyeIcon && eyeSlashIcon) {
                            eyeIcon.classList.toggle('hidden', isPassword);
                            eyeSlashIcon.classList.toggle('hidden', !isPassword);
                        }
                    });
                }
            };

            setupToggle('toggle-password', 'password', 'eye-icon', 'eye-slash-icon');
            setupToggle('toggle-password-confirmation', 'password_confirmation', 'eye-icon-confirmation', 'eye-slash-icon-confirmation');
        });
    </script>
</body>
</html>
