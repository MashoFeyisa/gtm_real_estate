@extends('app')

@section('title', ($persona['label'] ?? ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'member'))).' Portal | '.config('app.name', 'GTM Real Estate'))

@section('content')
    {{-- ═══════════ PERSONA PORTAL HERO ═══════════ --}}
    <section class="bg-[#102b25] px-6 py-16 text-white">
        <div class="mx-auto max-w-6xl">
            <p class="text-xs font-black uppercase tracking-[0.3em] text-[#d4af37]">Member Portal</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight text-white">
                {{ $persona['label'] ?? ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'member')) }} Dashboard
            </h1>
            <p class="mt-3 max-w-3xl text-sm text-[#dfeee4]">
                Welcome back, {{ auth()->user()->name }}. {{ $persona['description'] ?? 'Here is everything your account can do on the platform.' }}
            </p>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <span class="rounded-full bg-white/10 px-4 py-1.5 text-xs font-bold text-white ring-1 ring-white/20">
                    {{ $grantedCount }} active {{ Str::plural('capability', $grantedCount) }}
                </span>
                <a href="{{ route('properties.index') }}" class="rounded-full bg-[#d4af37] px-5 py-2 text-xs font-black text-[#102b25] transition hover:bg-white">
                    Explore Properties &rarr;
                </a>
                <a href="{{ route('client.portal') }}" class="rounded-full border border-white/25 px-5 py-2 text-xs font-bold text-white transition hover:bg-white/10">
                    Account &amp; Orders
                </a>
            </div>
        </div>
    </section>

    {{-- ═══════════ CAPABILITY MODULES ═══════════ --}}
    <section class="mx-auto max-w-6xl px-6 py-12">
        @forelse ($permissionGroups as $groupKey => $groupPermissions)
            <div class="{{ $loop->first ? '' : 'mt-10' }}">
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-black text-[#1d3c34]">
                        {{ $personaLabels[$groupKey] ?? ucfirst(str_replace('_', ' ', $groupKey)) }}
                    </h2>
                    <span class="rounded-full bg-[#f9ecd0] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#9b6c17]">
                        {{ $groupPermissions->count() }} {{ Str::plural('capability', $groupPermissions->count()) }}
                    </span>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($groupPermissions as $permission)
                        @php $link = $portalLinks[$permission->key] ?? null; @endphp
                        <div class="flex items-start justify-between gap-3 rounded-[1.25rem] border border-[#e7ddca] bg-[#fbf8f3] p-4 transition hover:border-[#d9cab3] hover:bg-white">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#1d3c34]">
                                    <svg class="h-3.5 w-3.5 text-[#d4af37]" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <div>
                                    <p class="text-sm font-bold text-[#1d3c34]">{{ $permission->label }}</p>
                                    <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-[#587165]">{{ $permission->key }}</p>
                                </div>
                            </div>
                            @if ($link && Route::has($link['route']))
                                <a href="{{ route($link['route']) }}" class="shrink-0 rounded-full bg-white px-3.5 py-1.5 text-[11px] font-black text-[#1d3c34] ring-1 ring-[#d9cab3] transition hover:bg-[#1d3c34] hover:text-white">
                                    {{ $link['label'] }} &rarr;
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="rounded-[1.5rem] border border-dashed border-[#d9cab3] bg-[#fbf8f3] p-10 text-center">
                <h2 class="text-lg font-black text-[#1d3c34]">No capabilities assigned yet</h2>
                <p class="mx-auto mt-2 max-w-xl text-sm text-slate-500">
                    An administrator has not granted any capabilities to your role yet. Once they do, everything you can do will appear here.
                </p>
                <a href="{{ route('properties.index') }}" class="mt-5 inline-block rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                    Browse Properties Instead
                </a>
            </div>
        @endforelse
    </section>

    {{-- Signed-in user bar --}}
    <section class="mx-auto max-w-6xl px-6 pb-12">
        @include('dashboard.partials.signed-in-user', ['variant' => 'light'])
    </section>
@endsection
