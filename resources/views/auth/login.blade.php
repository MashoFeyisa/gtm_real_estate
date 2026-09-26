<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f6f1e8] text-slate-800 antialiased">
    <div class="mx-auto flex min-h-screen max-w-6xl items-center justify-center px-6 py-12">
        <div class="grid w-full overflow-hidden rounded-[2rem] bg-white shadow-[0_30px_80px_rgba(29,60,52,0.18)] ring-1 ring-[#d9cab3] lg:grid-cols-[1.05fr_0.95fr]">
            <div class="bg-gradient-to-br from-[#1d3c34] via-[#2a4c42] to-[#6e7d68] p-10 text-[#f9f3e9]">
                <div class="inline-flex items-center gap-3 rounded-full border border-white/20 bg-white/10 px-3 py-2">
                    <img src="{{ asset('images/logo1.jpg') }}" alt="Real Estate Logo" class="h-10 w-10 rounded-xl object-cover ring-2 ring-[#d9cab3]">
                    <span class="text-sm font-bold uppercase tracking-[0.22em]">Real Estate</span>
                </div>
                <h1 class="mt-8 text-4xl font-black">Welcome back</h1>
                <p class="mt-4 max-w-sm text-base leading-7 text-[#e7efe8]">
                    Sign in to manage properties, track attendance, and review your real-estate operations with confidence.
                </p>
                <div class="mt-10 rounded-[1.5rem] border border-white/20 bg-white/5 p-5 backdrop-blur-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#d9cab3]">Operations</p>
                    <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                        <div class="rounded-xl bg-white/5 p-3">
                            <div class="text-xl font-black">128</div>
                            <div class="text-[10px] uppercase tracking-[0.18em] text-[#dfeee4]">Listings</div>
                        </div>
                        <div class="rounded-xl bg-white/5 p-3">
                            <div class="text-xl font-black">42</div>
                            <div class="text-[10px] uppercase tracking-[0.18em] text-[#dfeee4]">Present</div>
                        </div>
                        <div class="rounded-xl bg-white/5 p-3">
                            <div class="text-xl font-black">18</div>
                            <div class="text-[10px] uppercase tracking-[0.18em] text-[#dfeee4]">Agents</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-8 sm:p-10">
                <h2 class="text-3xl font-black text-[#1d3c34]">Admin Login</h2>

                @if ($errors->any())
                    <div class="mt-5 rounded-xl border border-[#efc9c4] bg-[#fff2f1] px-4 py-3 text-sm text-[#9e3f3a]">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                            class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-4 py-3 text-slate-900 outline-none transition focus:border-[#2d5d4d] focus:ring-4 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                        <input id="password" name="password" type="password" required
                            class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-4 py-3 text-slate-900 outline-none transition focus:border-[#2d5d4d] focus:ring-4 focus:ring-[#dfeee4]">
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-slate-600">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#2d5d4d]">
                            Remember me
                        </label>
                        <a href="{{ route('home') }}" class="font-semibold text-[#2d5d4d] hover:text-[#1d3c34]">Back home</a>
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-[#1d3c34] px-4 py-3 text-sm font-bold text-white shadow-lg shadow-[#1d3c34]/20 transition hover:bg-[#244d42]">
                        Sign in
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
