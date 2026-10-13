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
            <div class="flex items-center gap-3">
                @php
                    $comparedIds = session('compared_property_ids', []);
                    $comparedCount = count($comparedIds);
                    $compareQuery = !empty($comparedIds) ? ['ids' => implode(',', $comparedIds)] : [];
                @endphp
                <a href="{{ route('properties.compare', $compareQuery) }}" id="compare-matrix-nav-btn" class="compare-matrix-link inline-flex items-center gap-2 rounded-full border border-[#102b25] bg-[#102b25] px-4 py-2 text-sm font-bold text-[#d4af37] shadow-sm transition hover:bg-[#1d3c34]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    <span class="compare-matrix-counter">Compare Matrix ({{ $comparedCount }})</span>
                </a>
                <a href="{{ route('home') }}" class="inline-flex items-center rounded-full border border-[#d9cab3] bg-[#fffaf2] px-4 py-2 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f1e6d2]">Back home</a>
            </div>
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
                    {{-- City Selector (Addis Ababa, Harar, Jijjiga, Shashamane, Dire Dawa, Adama, Hawassa) --}}
                    <select name="city" id="city-filter-select" class="rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:border-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="">All Cities</option>
                        @foreach ($cities ?? [] as $cityKey => $cityData)
                            <option value="{{ $cityKey }}" {{ ($selectedCity ?? request('city')) === $cityKey ? 'selected' : '' }}>
                                {{ $cityData['name'] }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Addis Ababa Sub-Cities (conditional when Addis Ababa or all selected) --}}
                    <select name="subcity" id="subcity-filter-select" class="rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:border-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20 {{ ($selectedCity ?? request('city')) && ($selectedCity ?? request('city')) !== 'Addis Ababa' ? 'hidden' : '' }}">
                        <option value="">All Addis Sub-Cities</option>
                        @foreach ($subcities ?? [] as $subKey => $subData)
                            <option value="{{ $subKey }}" {{ ($selectedSubcity ?? request('subcity')) === $subKey ? 'selected' : '' }}>
                                {{ $subData['name'] }}
                            </option>
                        @endforeach
                    </select>

                    <select name="type" class="rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:border-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="">All Types (Sale & Rent)</option>
                        <option value="sale" {{ ($selectedType ?? request('type')) === 'sale' ? 'selected' : '' }}>For Sale</option>
                        <option value="rent" {{ ($selectedType ?? request('type')) === 'rent' ? 'selected' : '' }}>For Rent</option>
                        <option value="stay" {{ ($selectedType ?? request('type')) === 'stay' ? 'selected' : '' }}>Short Stay</option>
                    </select>
                    <input type="hidden" name="map_bounds" value="{{ $mapBounds ? implode(',', $mapBounds) : '' }}">
                    <input type="hidden" name="map_center" value="{{ $mapCenter ?? '' }}">
                    <input type="hidden" name="map_zoom" value="{{ $mapZoom ?? '' }}">
                    <button type="submit" class="rounded-xl bg-[#1d3c34] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]">
                        Search
                    </button>
                    @if (request()->hasAny(['search', 'type', 'city', 'location', 'subcity', 'woreda']))
                        <a href="{{ route('properties.index', ($selectedCategory ?? 'all') !== 'all' ? ['category' => $selectedCategory] : []) }}" class="rounded-xl border border-[#d9cab3] bg-[#fffaf2] px-4 py-2.5 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2.5">
                <a href="{{ route('properties.index', request()->only(['search', 'type', 'city', 'location', 'subcity'])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ ($selectedCategory ?? 'all') === 'all' ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-[#fffaf2] text-[#1d3c34]' }}">
                    All
                </a>
                <a href="{{ route('properties.villas', request()->only(['search', 'type', 'city', 'location', 'subcity'])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ ($selectedCategory ?? 'all') === 'villa' ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-[#fffaf2] text-[#1d3c34]' }}">
                    Villas
                </a>
                <a href="{{ route('properties.homes', request()->only(['search', 'type', 'city', 'location', 'subcity'])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ ($selectedCategory ?? 'all') === 'home' ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-[#fffaf2] text-[#1d3c34]' }}">
                    Homes
                </a>
                <a href="{{ route('properties.apartments', request()->only(['search', 'type', 'city', 'location', 'subcity'])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ ($selectedCategory ?? 'all') === 'apartment' ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-[#fffaf2] text-[#1d3c34]' }}">
                    Apartments
                </a>
                <a href="{{ route('properties.condominiums', request()->only(['search', 'type', 'city', 'location', 'subcity'])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ in_array(($selectedCategory ?? 'all'), ['condominium', 'condo'], true) ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-[#fffaf2] text-[#1d3c34]' }}">
                    Condominiums
                </a>
                <a href="{{ route('properties.guest_houses', request()->only(['search', 'type', 'city', 'location', 'subcity'])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ in_array(($selectedCategory ?? 'all'), ['guest_house', 'guesthouse'], true) ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-[#fffaf2] text-[#1d3c34]' }}">
                    Guest Houses <span class="ml-1 text-[11px] font-normal opacity-85">(Short Stay)</span>
                </a>
                <a href="{{ route('properties.hotels', request()->only(['search', 'type', 'city', 'location', 'subcity'])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ in_array(($selectedCategory ?? 'all'), ['hotel', 'hotels'], true) ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-[#fffaf2] text-[#1d3c34]' }}">
                    Hotels <span class="ml-1 text-[11px] font-normal opacity-85">(Short Stay)</span>
                </a>
            </div>

            <button type="button" id="toggle-map-view-btn" aria-expanded="true" class="inline-flex items-center gap-2 rounded-full border border-[#d4af37] bg-[#102b25] px-4 py-2 text-xs font-bold text-[#fdf8f0] shadow-sm transition hover:bg-[#1a443a] cursor-pointer">
                <svg class="h-4 w-4 text-[#d4af37]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                <span>Hide Map</span>
            </button>
        </div>

        @if ($mapBounds)
            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#d4af37]/60 bg-[#fdf6e4] px-5 py-4">
                <div>
                    <p class="text-sm font-black text-[#1d3c34]">
                        Map area filter active — {{ $properties->count() }} {{ Str::plural('listing', $properties->count()) }} with a pin inside the area you drew.
                    </p>
                    <p class="mt-1 text-xs text-slate-600">
                        Only listings that have exact GPS coordinates appear inside a drawn area. Pan or zoom the map and search the area again to widen it.
                    </p>
                </div>
                <a href="{{ request()->fullUrlWithoutQuery(['map_bounds', 'map_center', 'map_zoom']) }}" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-bold text-[#1d3c34] transition hover:bg-[#f1e6d2]">
                    Clear area filter
                </a>
            </div>
        @endif

        {{-- Interactive Map View Section --}}
        <div id="map-view-wrapper" class="relative mt-6 overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white shadow-md">
            <div class="flex items-center justify-between border-b border-[#f0e4cf] bg-[#fffaf2] px-6 py-3.5">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-2.5 w-2.5 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#d4af37] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#102b25]"></span>
                    </span>
                    <span class="text-xs font-black uppercase tracking-[0.2em] text-[#1d3c34]">Interactive Luxury Map View</span>
                </div>
                <span class="text-xs font-semibold text-slate-500">
                    Hover card or click gold pin to view listing
                </span>
            </div>
            <div id="luxury-properties-map"
                data-properties="{{ json_encode($mapProperties ?? []) }}"
                data-initial-bounds="{{ $mapBounds ? json_encode([[$mapBounds[0], $mapBounds[1]], [$mapBounds[2], $mapBounds[3]]]) : '' }}"
                data-initial-center="{{ $mapCenter ?? '' }}"
                data-initial-zoom="{{ $mapZoom ?? '' }}"
                class="h-[480px] w-full z-10"
                style="height: 480px; min-height: 480px; width: 100%; display: block; position: relative;"></div>

            <button type="button" id="search-map-area-btn" class="absolute left-1/2 top-4 z-20 hidden -translate-x-1/2 items-center gap-2 rounded-full bg-[#102b25] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#f0d581] shadow-lg transition hover:bg-[#1d3c34] cursor-pointer" aria-live="polite">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h16v16H4z"/></svg>
                <span>Search this area</span>
            </button>
        </div>

        @if ($properties->isEmpty())
            <div class="mt-8 rounded-[2rem] border border-dashed border-[#d9cab3] bg-[#fffaf2] p-10 text-center text-slate-600">
                @if ($mapBounds)
                    <p class="text-base font-semibold text-[#1d3c34]">No listings with a map pin inside the area you drew.</p>
                    <p class="mt-2 text-sm text-slate-500">Pan the map out and search a wider area, or clear the area filter.</p>
                    <a href="{{ request()->fullUrlWithoutQuery(['map_bounds', 'map_center', 'map_zoom']) }}" class="mt-4 inline-flex items-center rounded-full bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">Clear area filter</a>
                @elseif (request()->hasAny(['search', 'type', 'location']))
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
                        $isCardCompared = in_array($property->id, $comparedIds ?? [], true);
                    @endphp
                    <article data-property-card-id="{{ $property->id }}" data-property-card data-property-id="{{ $property->id }}" class="property-card group overflow-hidden rounded-[1.75rem] border transition-all duration-300 hover:-translate-y-1 hover:shadow-lg {{ $isCardCompared ? 'is-marked-for-compare ring-3 ring-[#d4af37] border-[#d4af37] shadow-xl' : 'border-[#d9cab3] bg-white shadow-sm' }}">
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
                                        <button type="button" data-slider-prev aria-label="Previous image" class="pointer-events-auto flex h-8 w-8 items-center justify-center rounded-full bg-black/75 text-white backdrop-blur-md opacity-85 group-hover/slider:opacity-100 transition-all duration-200 hover:scale-110 hover:bg-[#102b25] hover:text-[#d4af37] hover:border-[#d4af37] border border-white/30 active:scale-90 cursor-pointer shadow-md">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                        </button>
                                        <button type="button" data-slider-next aria-label="Next image" class="pointer-events-auto flex h-8 w-8 items-center justify-center rounded-full bg-black/75 text-white backdrop-blur-md opacity-85 group-hover/slider:opacity-100 transition-all duration-200 hover:scale-110 hover:bg-[#102b25] hover:text-[#d4af37] hover:border-[#d4af37] border border-white/30 active:scale-90 cursor-pointer shadow-md">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
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

                            {{-- Select Compare directly from image --}}
                            <div class="absolute top-2.5 left-2.5 z-30">
                                <form method="POST" action="{{ route('properties.compare.toggle', $property) }}" class="inline compare-toggle-form pointer-events-auto" data-property-id="{{ $property->id }}">
                                    @csrf
                                    <button type="submit" 
                                        title="{{ $isCardCompared ? 'Remove from comparison matrix' : 'Compare side by side in matrix' }}" 
                                        data-compared="{{ $isCardCompared ? '1' : '0' }}"
                                        class="compare-toggle-btn compare-img-btn inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold shadow-md backdrop-blur-md transition-all duration-200 cursor-pointer select-none active:scale-95 {{ $isCardCompared ? 'bg-[#d4af37] text-[#102b25] border border-[#d4af37] ring-2 ring-[#d4af37]/40 font-black' : 'bg-[#102b25]/85 text-[#fdf8f0] border border-white/30 hover:bg-[#102b25] hover:text-[#d4af37]' }}">
                                        <span class="compare-icon-wrap flex items-center justify-center">
                                            @if ($isCardCompared)
                                                <svg class="h-3.5 w-3.5 text-[#102b25]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            @else
                                                <svg class="h-3.5 w-3.5 text-[#d4af37]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                            @endif
                                        </span>
                                        <span class="compare-btn-text">{{ $isCardCompared ? '✓ In Matrix' : 'Compare' }}</span>
                                    </button>
                                </form>
                            </div>

                            {{-- Automatic compare matrix mark indicator on image --}}
                            <div class="compare-matrix-marker-badge absolute bottom-2.5 left-2.5 z-30 transition-all duration-300 {{ $isCardCompared ? 'opacity-100 scale-100' : 'opacity-0 scale-95 pointer-events-none hidden' }}">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#102b25]/95 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-[#d4af37] border border-[#d4af37] shadow-lg backdrop-blur-md ring-1 ring-black/40">
                                    <svg class="h-3.5 w-3.5 text-[#d4af37]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <span>Marked for Matrix</span>
                                </span>
                            </div>

                            {{-- Like & Favorite Overlay --}}
                            <div class="absolute top-2.5 right-2.5 z-30">
                                @include('properties.partials.interaction-buttons', ['property' => $property, 'size' => 'sm'])
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-[0.16em] text-white">
                                        {{ $property->type === 'rent' ? 'Rent' : 'Sale' }}
                                    </span>
                                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">{{ ucwords(str_replace('_', ' ', (string) ($property->property_category ?? $property->category ?? 'property'))) }}</p>
                                </div>
                                @if ($property->featured)
                                    <span class="rounded-full bg-[#f1e4cf] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#1d3c34]">Featured</span>
                                @endif
                            </div>
                            <h2 class="mt-3 text-xl font-black text-[#1d3c34]">
                                <a href="{{ route('properties.show', $property->slug) }}" class="transition hover:text-[#2e5a4c]">{{ $property->title }}</a>
                            </h2>
                            <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-600">
                                <svg class="h-3.5 w-3.5 text-[#d4af37] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="font-bold text-[#1d3c34]">{{ $property->subcity ?: $property->city }}</span>
                                @if ($property->neighborhood || $property->woreda)
                                    <span class="text-slate-400">&bull;</span>
                                    <span class="truncate">{{ $property->neighborhood ?: $property->woreda }}</span>
                                @endif
                            </div>

                            <div class="mt-3 flex items-baseline justify-between gap-2">
                                <div>
                                    <span class="text-2xl font-black text-[#1d3c34] currency-price" data-base-price="{{ $property->price }}">ETB {{ number_format($property->price) }}</span>
                                    @if ($property->type === 'rent')
                                        <span class="text-xs font-bold text-[#587165]">/ month</span>
                                    @endif
                                </div>
                                @if ($property->price_per_sqm > 0)
                                    <span class="text-[11px] font-bold text-[#587165] bg-[#fffaf2] px-2 py-0.5 rounded border border-[#ecd9be]">{{ number_format($property->price_per_sqm) }} ETB/m²</span>
                                @endif
                            </div>

                            <div class="mt-3 flex items-center gap-3 text-xs text-slate-600 border-t border-[#f0e4cf] pt-3">
                                <span>{{ $property->bedrooms }} beds</span>
                                <span>•</span>
                                <span>{{ $property->bathrooms }} baths</span>
                                <span>•</span>
                                <span>{{ $property->area }} sqm</span>
                            </div>

                            <div class="mt-5 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('properties.show', $property->slug) }}" class="inline-flex rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#264d41]">
                                        View Details
                                    </a>
                                    @php
                                        $isCompared = in_array($property->id, $comparedIds ?? [], true);
                                    @endphp
                                    <form method="POST" action="{{ route('properties.compare.toggle', $property) }}" class="inline compare-toggle-form" data-property-id="{{ $property->id }}">
                                        @csrf
                                        <button type="submit" title="{{ $isCompared ? 'Remove from comparison matrix' : 'Compare side by side in matrix' }}" class="compare-toggle-btn compare-card-btn inline-flex items-center gap-1 rounded-full px-3.5 py-2 text-xs font-bold transition cursor-pointer {{ $isCompared ? 'bg-[#d4af37] text-[#102b25] font-black' : 'bg-[#1d3c34] text-white hover:bg-[#264d41]' }}" data-compared="{{ $isCompared ? '1' : '0' }}">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                            <span class="compare-btn-text">{{ $isCompared ? '✓ In Matrix' : 'Compare' }}</span>
                                        </button>
                                    </form>
                                </div>

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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const citySelect = document.getElementById('city-filter-select');
                const subcitySelect = document.getElementById('subcity-filter-select');
                if (citySelect && subcitySelect) {
                    citySelect.addEventListener('change', function () {
                        if (this.value === '' || this.value === 'Addis Ababa') {
                            subcitySelect.classList.remove('hidden');
                        } else {
                            subcitySelect.classList.add('hidden');
                            subcitySelect.value = '';
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
