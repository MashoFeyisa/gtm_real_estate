@extends('app')

@section('title', 'Properties — RealEstate')
@section('description', 'Browse all available homes, villas, apartments, and land listings from trusted agents.')

@section('content')
    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Listings</p>
                <h1 class="mt-2 text-4xl font-black tracking-tight text-[#1d3c34]">Properties</h1>
            </div>
            <a href="{{ route('home') }}" class="inline-flex items-center rounded-full border border-[#d9cab3] bg-[#fffaf2] px-4 py-2 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f1e6d2]">Back home</a>
        </div>

        {{-- Properties Search Bar --}}
        <div class="mt-8 rounded-2xl border border-[#d9cab3] bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('properties.index') }}" class="flex flex-col gap-3 md:flex-row md:items-center">
                @if (($selectedCategory ?? 'all') !== 'all')
                    <input type="hidden" name="category" value="{{ $selectedCategory }}">
                @endif
                <div class="relative flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="search" value="{{ $searchQuery ?? request('search') }}" placeholder="Search properties by title, city, neighborhood, features..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] py-2.5 pl-11 pr-4 text-sm font-medium text-slate-800 placeholder-slate-400 focus:border-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select name="type" class="rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:border-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="">All Types (Sale & Rent)</option>
                        <option value="sale" {{ ($selectedType ?? request('type')) === 'sale' ? 'selected' : '' }}>For Sale</option>
                        <option value="rent" {{ ($selectedType ?? request('type')) === 'rent' ? 'selected' : '' }}>For Rent</option>
                    </select>
                    <button type="submit" class="rounded-xl bg-[#1d3c34] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]">
                        Search
                    </button>
                    @if (request()->hasAny(['search', 'type', 'location']))
                        <a href="{{ route('properties.index', ($selectedCategory ?? 'all') !== 'all' ? ['category' => $selectedCategory] : []) }}" class="rounded-xl border border-[#d9cab3] bg-[#fffaf2] px-4 py-2.5 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('properties.index', request()->only(['search', 'type', 'location'])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ ($selectedCategory ?? 'all') === 'all' ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-[#fffaf2] text-[#1d3c34]' }}">
                All
            </a>
            <a href="{{ route('properties.villas', request()->only(['search', 'type', 'location'])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ ($selectedCategory ?? 'all') === 'villa' ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-[#fffaf2] text-[#1d3c34]' }}">
                Villas
            </a>
            <a href="{{ route('properties.homes', request()->only(['search', 'type', 'location'])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ ($selectedCategory ?? 'all') === 'home' ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-[#fffaf2] text-[#1d3c34]' }}">
                Homes
            </a>
            <a href="{{ route('properties.apartments', request()->only(['search', 'type', 'location'])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ ($selectedCategory ?? 'all') === 'apartment' ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-[#fffaf2] text-[#1d3c34]' }}">
                Apartments
            </a>
        </div>

        @if ($properties->isEmpty())
            <div class="mt-8 rounded-[2rem] border border-dashed border-[#d9cab3] bg-[#fffaf2] p-10 text-center text-slate-600">
                @if (request()->hasAny(['search', 'type', 'location']))
                    <p class="text-base font-semibold text-[#1d3c34]">No properties match your search criteria.</p>
                    <p class="mt-2 text-sm text-slate-500">Try adjusting your keywords or clearing the filters.</p>
                    <a href="{{ route('properties.index') }}" class="mt-4 inline-flex items-center rounded-full bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">Clear Search</a>
                @else
                    No properties available yet.
                @endif
            </div>
        @else
            <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($properties as $property)
                    @php
                        $cardGallery = $property->gallery_images;
                    @endphp
                    <article class="group overflow-hidden rounded-[1.75rem] border border-[#d9cab3] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="relative">
                            @if (count($cardGallery) > 1)
                                <div data-property-slider class="group/slider relative h-56 w-full overflow-hidden bg-[#e8efe8] shrink-0 select-none">
                                    <a href="{{ route('properties.show', $property->slug) }}" class="absolute inset-0 z-10" aria-label="{{ $property->title }}"></a>
                                    @foreach ($cardGallery as $idx => $imgUrl)
                                        <div data-property-slide class="absolute inset-0 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] {{ $idx === 0 ? 'opacity-100 scale-100 z-10' : 'opacity-0 scale-105 pointer-events-none z-0' }}">
                                            <img src="{{ $imgUrl }}" alt="{{ $property->title }} - Photo {{ $idx + 1 }}" class="h-full w-full object-cover">
                                        </div>
                                    @endforeach

                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent pointer-events-none"></div>

                                    <div class="absolute inset-x-2 top-1/2 -translate-y-1/2 flex items-center justify-between pointer-events-none z-20">
                                        <button type="button" data-slider-prev aria-label="Previous image" class="pointer-events-auto flex h-7 w-7 items-center justify-center rounded-full bg-black/65 text-[#e7d8b7] backdrop-blur-md opacity-0 group-hover/slider:opacity-100 transition-all duration-200 hover:bg-[#102b25] hover:text-white hover:border-[#d4af37] border border-white/20 active:scale-90 cursor-pointer">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                        </button>
                                        <button type="button" data-slider-next aria-label="Next image" class="pointer-events-auto flex h-7 w-7 items-center justify-center rounded-full bg-black/65 text-[#e7d8b7] backdrop-blur-md opacity-0 group-hover/slider:opacity-100 transition-all duration-200 hover:bg-[#102b25] hover:text-white hover:border-[#d4af37] border border-white/20 active:scale-90 cursor-pointer">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                        </button>
                                    </div>

                                    <div class="absolute bottom-2.5 right-2.5 z-20">
                                        <span data-slider-counter class="rounded-full bg-black/75 px-2 py-0.5 text-[10px] font-bold text-[#e7d8b7] backdrop-blur-xs border border-white/20">
                                            1 / {{ count($cardGallery) }}
                                        </span>
                                    </div>
                                </div>
                            @elseif (count($cardGallery) === 1)
                                <a href="{{ route('properties.show', $property->slug) }}" class="block">
                                    <img src="{{ $cardGallery[0] }}" alt="{{ $property->title }}" class="h-56 w-full object-cover transition duration-300 group-hover:scale-105">
                                </a>
                            @else
                                <a href="{{ route('properties.show', $property->slug) }}" class="block">
                                    <div class="flex h-56 items-center justify-center bg-gradient-to-br from-[#dfe8e3] via-[#edf3ee] to-[#e9d9b7] text-xl font-black text-[#1d3c34]">
                                        {{ Str::limit($property->title, 18) }}
                                    </div>
                                </a>
                            @endif
                        </div>

                        <div class="p-6">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-[0.16em] text-white">
                                        {{ $property->type === 'rent' ? 'Rent' : 'Sale' }}
                                    </span>
                                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">{{ ucfirst($property->property_category ?? $property->category ?? 'property') }}</p>
                                </div>
                                @if ($property->featured)
                                    <span class="rounded-full bg-[#f1e4cf] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#1d3c34]">Featured</span>
                                @endif
                            </div>
                            <h2 class="mt-3 text-xl font-black text-[#1d3c34]">
                                <a href="{{ route('properties.show', $property->slug) }}" class="transition hover:text-[#2e5a4c]">{{ $property->title }}</a>
                            </h2>
                            <p class="mt-1 text-sm text-slate-600">{{ $property->city ?? 'Addis Ababa' }} · {{ $property->address ?? 'Prime location' }}</p>

                            <div class="mt-3 flex items-baseline gap-2">
                                <span class="text-2xl font-black text-[#1d3c34]">ETB {{ number_format($property->price) }}</span>
                                @if ($property->type === 'rent')
                                    <span class="text-xs font-bold text-[#587165]">/ month</span>
                                @endif
                            </div>

                            <div class="mt-3 flex items-center gap-3 text-xs text-slate-600 border-t border-[#f0e4cf] pt-3">
                                <span>{{ $property->bedrooms }} beds</span>
                                <span>•</span>
                                <span>{{ $property->bathrooms }} baths</span>
                                <span>•</span>
                                <span>{{ $property->area }} sqm (ካሬ)</span>
                            </div>

                            <div class="mt-5 flex items-center justify-between gap-2">
                                <a href="{{ route('properties.show', $property->slug) }}" class="inline-flex rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#264d41]">
                                    View Details
                                </a>

                                @if ($property->agent)
                                    <a href="{{ route('agents.show', $property->agent) }}" class="inline-flex items-center gap-1.5 text-xs text-[#587165] hover:text-[#1d3c34]">
                                        <img src="{{ $property->agent->photo_url }}" alt="{{ $property->agent->name }}" class="h-6 w-6 rounded-full object-cover ring-1 ring-[#00b53f]">
                                        <span class="font-semibold">{{ $property->agent->name }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
