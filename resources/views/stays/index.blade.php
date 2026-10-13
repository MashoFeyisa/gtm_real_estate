@extends('app')

@section('title', 'Short-Stay Accommodations & Vacation Homes — GTM Real Estate')
@section('description', 'Discover Airbnb-style serviced apartments, villas, and executive stays in Addis Ababa and across Ethiopia.')

@section('content')
    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#587165]">Airbnb-Style Stays</p>
                </div>
                <h1 class="mt-2 text-3xl sm:text-4xl font-black tracking-tight text-[#1d3c34]">
                    Short-Stay Accommodations & Executive Stays
                </h1>
                <p class="mt-1.5 text-sm text-slate-600 max-w-2xl">
                    Fully-furnished, verified serviced apartments, residences, and luxury villas with verified utilities, standby generators, and instant reservation.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('properties.index') }}" class="inline-flex items-center rounded-full border border-[#d9cab3] bg-[#fffaf2] px-4 py-2 text-xs font-bold text-[#1d3c34] transition hover:bg-[#f1e6d2]">
                    View Buy & Rent
                </a>
            </div>
        </div>

        {{-- Stays Search & Filter Bar --}}
        <div class="mt-8 rounded-2xl border border-[#d9cab3] bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('stays.index') }}" class="grid gap-3 sm:grid-cols-2 md:grid-cols-4 items-end">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#587165] mb-1.5">Destination / City</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        </span>
                        <input type="text" name="city" value="{{ request('city') }}" placeholder="e.g. Bole, Addis Ababa" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] py-2.5 pl-9 pr-3 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#587165] mb-1.5">Guests Capacity</label>
                    <select name="guests" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="">Any Guests (1+)</option>
                        <option value="1" {{ request('guests') == '1' ? 'selected' : '' }}>1 Guest</option>
                        <option value="2" {{ request('guests') == '2' ? 'selected' : '' }}>2 Guests</option>
                        <option value="4" {{ request('guests') == '4' ? 'selected' : '' }}>3 - 4 Guests</option>
                        <option value="6" {{ request('guests') == '6' ? 'selected' : '' }}>5+ Guests (Family)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#587165] mb-1.5">Budget Per Night (ETB)</label>
                    <div class="flex items-center gap-1.5">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-2.5 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <span class="text-xs text-slate-400">-</span>
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-2.5 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 rounded-xl bg-[#1d3c34] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43] cursor-pointer shadow-sm">
                        Find Stays
                    </button>
                    @if (request()->hasAny(['city', 'guests', 'min_price', 'max_price']))
                        <a href="{{ route('stays.index') }}" class="rounded-xl border border-[#d9cab3] bg-[#fffaf2] px-3 py-2.5 text-xs font-semibold text-slate-700 hover:bg-[#f0e4cf] transition">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Stays Grid --}}
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($properties as $property)
                @php
                    $nightlyRate = $property->getNightlyRate();
                @endphp
                <article class="group relative flex flex-col overflow-hidden rounded-[1.75rem] border border-[#d9cab3] bg-white shadow-sm transition hover:shadow-xl hover:border-[#1d3c34]/30">
                    <div class="relative h-60 w-full overflow-hidden bg-slate-100">
                        <img src="{{ $property->primary_image_url }}" alt="{{ $property->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                        <div class="absolute left-3 top-3 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-[#102b25]/90 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-[#d4af37] backdrop-blur-xs">
                                Short-Stay
                            </span>
                            @if ($property->property_code)
                                <span class="rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-mono font-bold text-[#1d3c34] backdrop-blur-xs">
                                    {{ $property->property_code }}
                                </span>
                            @endif
                        </div>

                        <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white">
                            <div>
                                <span class="text-lg font-black currency-price" data-base-price="{{ $nightlyRate }}">ETB {{ number_format($nightlyRate) }}</span>
                                <span class="text-xs text-white/80">/ night</span>
                            </div>
                            <span class="rounded-lg bg-black/40 px-2 py-0.5 text-[11px] font-semibold backdrop-blur-xs">
                                {{ $property->bedrooms }} Bed · {{ $property->bathrooms }} Bath
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col justify-between p-5">
                        <div>
                            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                <svg class="h-3.5 w-3.5 text-[#587165]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                <span>{{ $property->city ?? 'Addis Ababa' }} · {{ $property->address ?? 'Prime Location' }}</span>
                            </div>
                            <h3 class="mt-2 text-lg font-black text-[#1d3c34] group-hover:text-[#2d5d4d] transition line-clamp-1">
                                <a href="{{ route('properties.show', $property->slug) }}#stay-reservation-studio">{{ $property->title }}</a>
                            </h3>
                            <p class="mt-1.5 text-xs text-slate-600 line-clamp-2">
                                {{ $property->description ?: 'Exclusive serviced accommodation in prime location with full backup utilities and dedicated host care.' }}
                            </p>

                            {{-- Infrastructure Readiness Badges --}}
                            <div class="mt-3.5 flex flex-wrap gap-1.5 text-[11px]">
                                @if ($property->has_electricity)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-[#eef8f2] px-2 py-0.5 font-bold text-[#1b5e20] border border-[#c8e6c9]">
                                        <span>⚡ 3-Phase</span>
                                    </span>
                                @endif
                                @if ($property->has_water_tank)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-[#eef8f2] px-2 py-0.5 font-bold text-[#1b5e20] border border-[#c8e6c9]">
                                        <span>🛢️ Water Tank</span>
                                    </span>
                                @endif
                                @if ($property->has_generator)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-[#eef8f2] px-2 py-0.5 font-bold text-[#1b5e20] border border-[#c8e6c9]">
                                        <span>🔋 Generator</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 font-semibold text-slate-600">
                                        <span>Generator Ready</span>
                                    </span>
                                @endif
                                @if ($property->has_security)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-[#eef8f2] px-2 py-0.5 font-bold text-[#1b5e20] border border-[#c8e6c9]">
                                        <span>🛡️ 24/7 Security</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-t border-[#f0e4cf] flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-[#587165]">
                                Up to {{ max(2, $property->bedrooms * 2) }} Guests
                            </span>
                            <a href="{{ route('properties.show', $property->slug) }}#stay-reservation-studio" class="inline-flex items-center gap-1 rounded-xl bg-[#1d3c34] px-3.5 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-[#254d43]">
                                <span>Reserve Stay</span>
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                    <p class="text-base font-semibold text-slate-600">No short-stay properties found matching your criteria.</p>
                    <a href="{{ route('stays.index') }}" class="mt-3 inline-flex items-center rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white">Reset Filters</a>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $properties->links() }}
        </div>
    </section>
@endsection
