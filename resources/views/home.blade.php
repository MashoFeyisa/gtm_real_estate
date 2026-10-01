@extends('app')

@section('title', $siteBrand['name'].' — Premium Living Spaces')
@section('description', \App\Models\SiteSetting::get('hero_description', 'Find luxury villas, modern apartments, and smart investments in trusted neighborhoods with a team that knows the market deeply.'))

@section('content')
    @php
        $getPropertyCategory = fn ($property) => strtolower((string) ($property->property_category ?? $property->category ?? 'home'));
        $propertyJson = function ($property) use ($getPropertyCategory) {
            $images = $property->gallery_images;
            if (empty($images)) {
                $images = ['/images/luxury/hero-skyline.jpg'];
            }
            $cleanPhone = preg_replace('/[^0-9]/', '', (string) ($property->agent?->phone ?? ''));
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '251' . substr($cleanPhone, 1);
            }
            return json_encode([
                'id' => $property->id,
                'title' => $property->title,
                'slug' => $property->slug,
                'price' => $property->price,
                'type' => $property->type,
                'category' => $getPropertyCategory($property),
                'city' => $property->city ?: 'Harar',
                'address' => $property->address ?: 'Prime Location',
                'bedrooms' => $property->bedrooms,
                'bathrooms' => $property->bathrooms,
                'area' => $property->area,
                'description' => $property->description ? strip_tags($property->description) : '',
                'featured' => (bool) $property->featured,
                'images' => $images,
                'agentName' => $property->agent?->name,
                'agentPhone' => $property->agent?->phone,
                'agentPhoto' => $property->agent?->photo_url,
                'whatsappUrl' => $cleanPhone ? 'https://wa.me/' . $cleanPhone . '?text=' . urlencode('Hello ' . ($property->agent?->name ?? 'Agent') . ', I am interested in: ' . $property->title) : null,
            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        };
    @endphp

    {{-- ========================================================================= --}}
    {{-- HERO SECTION: Cinematic Full-Bleed Masterplan Living (Inspired by La Gare) --}}
    {{-- ========================================================================= --}}
    <section id="home" class="relative overflow-hidden bg-[#0c1f1a] text-white">
        {{-- High-resolution masterplan background with layered luxury gradients --}}
        <div class="absolute inset-0 z-0">
            <img src="{{ \App\Models\SiteSetting::imageUrl('hero_image', 'images/luxury/hero-skyline.jpg') }}" alt="Masterplan Luxury Residences" class="h-full w-full object-cover object-center transform scale-105 transition-transform duration-1000 ease-out">
            <div class="absolute inset-0 bg-gradient-to-r from-[#0a1714]/95 via-[#0f2821]/85 to-[#0b1915]/90"></div>
            <div class="absolute inset-0 bg-radial from-transparent via-[#091512]/60 to-[#07110e]/95"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-6 pt-16 pb-20 lg:px-8 lg:pt-24 lg:pb-28">
            <div class="grid items-center gap-12 lg:grid-cols-[1.15fr_0.85fr]">
                {{-- Left Column: Hero Typography & Key Metrics --}}
                <div>
                    <div class="inline-flex items-center gap-2.5 rounded-full border border-[#c5a059]/40 bg-[#c5a059]/15 px-4 py-1.5 text-xs font-bold uppercase tracking-[0.25em] text-[#e7d8b7] backdrop-blur-md shadow-lg shadow-black/20">
                        <span class="h-2 w-2 rounded-full bg-[#d4af37] animate-pulse"></span>
                        {{ \App\Models\SiteSetting::get('hero_badge', 'Premium living spaces') }}
                    </div>

                    <h1 class="mt-6 text-4xl font-black tracking-tight text-white sm:text-5xl lg:text-6xl leading-[1.12]">
                        {{ \App\Models\SiteSetting::get('hero_title', 'Discover homes that feel like your next chapter.') }}
                    </h1>

                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-[#d7e5dc] font-normal">
                        {{ \App\Models\SiteSetting::get('hero_description', 'Find luxury villas, modern apartments, and smart investments in trusted neighborhoods with a team that knows the market deeply.') }}
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="{{ route('properties.index') }}" class="group inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-[#d4af37] via-[#c5a059] to-[#b38e46] px-7 py-3.5 text-sm font-bold uppercase tracking-[0.14em] text-[#0c1f1a] shadow-xl shadow-[#d4af37]/20 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-2xl hover:shadow-[#d4af37]/35">
                            <span>{{ \App\Models\SiteSetting::get('hero_primary_button_text', 'Explore Listings') }}</span>
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                        <a href="{{ url('/app#contact') }}" class="inline-flex items-center gap-2 rounded-full border border-[#e7d8b7]/40 bg-white/10 px-7 py-3.5 text-sm font-semibold text-[#f4efe7] backdrop-blur-md transition-all duration-200 hover:bg-white/20 hover:border-[#e7d8b7]">
                            <span>{{ \App\Models\SiteSetting::get('hero_secondary_button_text', 'Book a Visit') }}</span>
                        </a>
                        <a href="{{ route('careers') }}" class="inline-flex items-center text-xs font-semibold uppercase tracking-[0.18em] text-[#e7d8b7] hover:underline underline-offset-4">
                            {!! \App\Models\SiteSetting::get('hero_careers_link_text', 'Careers &rarr;') !!}
                        </a>
                        <a href="#safety-advisory" class="inline-flex items-center gap-1.5 rounded-full border border-[#d4af37]/50 bg-[#d4af37]/15 px-3.5 py-1.5 text-xs font-bold uppercase tracking-[0.16em] text-[#e7d8b7] backdrop-blur-md transition hover:bg-[#d4af37]/25">
                            <span class="h-2 w-2 rounded-full bg-[#d4af37] animate-pulse"></span>
                            <span>{{ 'Safety & Direct Deal' }}</span>
                        </a>
                    </div>

                    {{-- Live Performance Counters in Hero --}}
                    <div class="mt-10 flex flex-wrap items-center gap-6 sm:gap-8 border-t border-white/15 pt-6 text-sm text-[#cbdad0]">
                        <div class="flex flex-col">
                            <span id="happy-buyers-count" class="text-3xl font-black text-[#e7d8b7] tracking-tight">{{ number_format($homeStats['happy_buyers'] ?? 0) }}</span>
                            <span class="text-xs font-semibold uppercase tracking-[0.16em] text-[#a4b8ad]">{{ \App\Models\SiteSetting::get('hero_stat_1_label', 'happy buyers') }}</span>
                        </div>
                        <div class="h-9 w-px bg-white/20 hidden sm:block"></div>
                        <div class="flex flex-col">
                            <span class="text-3xl font-black text-white tracking-tight">{{ $homeStats['sold'] ?? 0 }}</span>
                            <span class="text-xs font-semibold uppercase tracking-[0.16em] text-[#a4b8ad]">{{ \App\Models\SiteSetting::get('hero_stat_2_label', 'properties sold') }}</span>
                        </div>
                        <div class="h-9 w-px bg-white/20 hidden sm:block"></div>
                        <div class="flex flex-col">
                            <span class="text-3xl font-black text-[#e7d8b7] tracking-tight">{{ \App\Models\SiteSetting::get('hero_stat_3_value', '18 yrs') }}</span>
                            <span class="text-xs font-semibold uppercase tracking-[0.16em] text-[#a4b8ad]">{{ \App\Models\SiteSetting::get('hero_stat_3_label', 'market expertise') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Featured Showcase Glass Card with Multi-Image Slider --}}
                <div class="relative">
                    @php
                        $heroProperty = $featuredProperties->first();
                    @endphp
                    <div class="rounded-[2.5rem] border border-white/20 bg-white/10 p-6 backdrop-blur-xl shadow-[0_30px_90px_rgba(0,0,0,0.45)]">
                        <div class="rounded-[2rem] bg-gradient-to-br from-[#122e26]/90 via-[#1d3c34]/90 to-[#284a3f]/90 p-5 text-[#f9f3e9] border border-white/10 shadow-inner">
                            <div class="flex items-center justify-between">
                                <div class="inline-flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-[#d4af37]"></span>
                                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#e7d8b7]">{{ \App\Models\SiteSetting::get('hero_featured_badge', 'Featured property') }}</p>
                                </div>
                                <span class="rounded-full border border-[#d4af37]/40 bg-[#d4af37]/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-[#e7d8b7]">
                                    {{ $heroProperty ? ($heroProperty->status === 'sold' ? 'Sold' : ucfirst($heroProperty->type)) : 'For Sale' }}
                                </span>
                            </div>

                            @if ($heroProperty)
                                @php
                                    $heroGallery = $heroProperty->gallery_images;
                                @endphp
                                <div data-property-card data-property-json="{{ $propertyJson($heroProperty) }}" class="mt-4">
                                    <div data-property-slider class="group/hero-slider relative aspect-[16/10] overflow-hidden rounded-2xl bg-[#0a1714] select-none">
                                        @forelse ($heroGallery as $idx => $imgUrl)
                                            <div data-property-slide class="absolute inset-0 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] {{ $idx === 0 ? 'opacity-100 scale-100 z-10' : 'opacity-0 scale-105 pointer-events-none z-0' }}">
                                                <img src="{{ $imgUrl }}" alt="{{ $heroProperty->title }}" class="h-full w-full object-cover">
                                            </div>
                                        @empty
                                            <div class="relative h-full w-full">
                                                <img src="{{ asset($heroProperty->default_background_image) }}" alt="{{ $heroProperty->title }}" class="h-full w-full object-cover">
                                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center text-2xl font-black text-[#e7d8b7]">
                                                    {{ Str::limit($heroProperty->title, 20) }}
                                                </div>
                                            </div>
                                        @endforelse

                                        <div class="absolute inset-0 bg-gradient-to-t from-[#0a1714]/85 via-transparent to-black/20 pointer-events-none"></div>

                                        {{-- Slider Arrows (if multiple photos) --}}
                                        @if (count($heroGallery) > 1)
                                            <div class="absolute inset-x-2.5 top-1/2 -translate-y-1/2 flex items-center justify-between pointer-events-none z-20">
                                                <button type="button" data-slider-prev aria-label="Previous photo" class="pointer-events-auto flex h-9 w-9 items-center justify-center rounded-full bg-black/70 text-[#e7d8b7] backdrop-blur-md opacity-0 group-hover/hero-slider:opacity-100 transition-all duration-200 hover:scale-110 hover:bg-[#102b25] hover:text-white hover:border-[#d4af37] border border-white/20 active:scale-95 cursor-pointer">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                                </button>
                                                <button type="button" data-slider-next aria-label="Next photo" class="pointer-events-auto flex h-9 w-9 items-center justify-center rounded-full bg-black/70 text-[#e7d8b7] backdrop-blur-md opacity-0 group-hover/hero-slider:opacity-100 transition-all duration-200 hover:scale-110 hover:bg-[#102b25] hover:text-white hover:border-[#d4af37] border border-white/20 active:scale-95 cursor-pointer">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                                </button>
                                            </div>
                                        @endif

                                        {{-- Quickview Sidebar Trigger Button --}}
                                        <div class="absolute top-3 right-3 z-20">
                                            <button type="button" data-quickview-btn title="Open photo gallery preview sidebar" class="flex items-center gap-1.5 rounded-full border border-white/25 bg-black/70 px-3 py-1.5 text-xs font-bold text-[#e7d8b7] backdrop-blur-md shadow-lg transition-all hover:bg-[#d4af37] hover:text-[#0c1f1a] hover:border-[#d4af37] active:scale-95 cursor-pointer">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span>Sidebar ({{ count($heroGallery) }})</span>
                                            </button>
                                        </div>

                                        <span class="absolute bottom-3 left-3 z-20 rounded-full bg-[#0a1714]/85 backdrop-blur-md px-3 py-1 text-xs font-bold text-[#e7d8b7] border border-white/10">
                                            {{ ucfirst($heroProperty->city ?: 'Prime District') }}
                                        </span>

                                        @if (count($heroGallery) > 1)
                                            <div class="absolute bottom-3 right-3 z-20 flex items-center gap-1.5">
                                                <span data-slider-counter class="rounded-full bg-black/75 px-2.5 py-0.5 text-[10px] font-bold text-[#e7d8b7] backdrop-blur-xs border border-white/15">1 / {{ count($heroGallery) }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <h2 class="mt-4 text-2xl font-black">
                                        <a href="{{ route('properties.show', $heroProperty->slug) }}" class="transition hover:text-[#e7d8b7]">{{ $heroProperty->title }}</a>
                                    </h2>
                                    <p class="mt-1 text-sm text-[#cbdad0]">{{ $heroProperty->bedrooms }} bed • {{ $heroProperty->bathrooms }} bath • {{ number_format($heroProperty->area) }} sqm (ካሬ)</p>

                                    <div class="mt-5 rounded-2xl bg-white/90 p-4 text-[#102b25] backdrop-blur-md shadow-lg">
                                        <div class="flex items-end justify-between">
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.2em] text-[#617466]">{{ $heroProperty->type === 'rent' ? 'Starting at' : 'Price' }}</p>
                                                <p class="mt-1 text-2xl font-black text-[#102b25]">ETB {{ number_format($heroProperty->price) }}{{ $heroProperty->type === 'rent' ? '/mo' : '' }}</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button" data-quickview-btn class="rounded-full border border-[#102b25] bg-transparent px-3.5 py-2 text-xs font-bold uppercase tracking-[0.14em] text-[#102b25] transition hover:bg-[#102b25] hover:text-[#e7d8b7] cursor-pointer">Preview</button>
                                                <a href="{{ route('properties.show', $heroProperty->slug) }}" class="rounded-full bg-[#102b25] px-4 py-2 text-xs font-bold uppercase tracking-[0.16em] text-[#e7d8b7] transition hover:bg-[#1a4037]">Details</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="mt-4 overflow-hidden rounded-2xl bg-gradient-to-br from-[#1e3c33] via-[#2d5145] to-[#476053] aspect-[16/10] relative flex items-center justify-center">
                                    <div class="text-center">
                                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#e7d8b7]">Masterplan Collection</p>
                                        <h3 class="mt-1 text-2xl font-black text-white">Greenview Residence</h3>
                                    </div>
                                </div>
                                <h2 class="mt-4 text-2xl font-black">Greenview Residence</h2>
                                <p class="mt-1 text-sm text-[#cbdad0]">4 bed • 3 bath • 280 sqm (ካሬ)</p>
                                <div class="mt-5 rounded-2xl bg-white/90 p-4 text-[#102b25] backdrop-blur-md shadow-lg">
                                    <div class="flex items-end justify-between">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.2em] text-[#617466]">Starting at</p>
                                            <p class="mt-1 text-2xl font-black text-[#102b25]">ETB 480k</p>
                                        </div>
                                        <div class="rounded-full bg-[#102b25] px-4 py-2 text-xs font-bold uppercase tracking-[0.16em] text-[#e7d8b7]">Prime location</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Category Quick Pills --}}
                        <div class="mt-4 grid grid-cols-3 gap-2.5 sm:gap-3">
                            <a href="{{ route('properties.homes') }}" class="rounded-2xl bg-white/10 hover:bg-white/15 border border-white/10 p-3.5 text-center transition">
                                <p class="text-[11px] uppercase tracking-[0.18em] text-[#c5d6cc]">Homes</p>
                                <p class="mt-1 text-xl font-black text-white">{{ $homeStats['homes'] }}</p>
                            </a>
                            <a href="{{ route('properties.villas') }}" class="rounded-2xl bg-white/10 hover:bg-white/15 border border-white/10 p-3.5 text-center transition">
                                <p class="text-[11px] uppercase tracking-[0.18em] text-[#c5d6cc]">Villas</p>
                                <p class="mt-1 text-xl font-black text-[#e7d8b7]">{{ $homeStats['villas'] }}</p>
                            </a>
                            <a href="{{ route('properties.apartments') }}" class="rounded-2xl bg-white/10 hover:bg-white/15 border border-white/10 p-3.5 text-center transition">
                                <p class="text-[11px] uppercase tracking-[0.18em] text-[#c5d6cc]">Apartments</p>
                                <p class="mt-1 text-xl font-black text-white">{{ $homeStats['apartments'] }}</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Glassmorphic Instant Property Filter Bar --}}
            <div class="mt-12 rounded-[2rem] border border-white/20 bg-white/10 p-4 sm:p-5 backdrop-blur-xl shadow-2xl">
                <form method="GET" action="{{ route('properties.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 items-center">
                    <div>
                        <label for="filter-category" class="block text-[11px] font-bold uppercase tracking-[0.2em] text-[#e7d8b7] mb-1">{{ \App\Models\SiteSetting::get('filter_category_label', 'Residence Category') }}</label>
                        <select id="filter-category" name="category" class="w-full rounded-xl border border-white/20 bg-[#0f2821]/80 px-3.5 py-2.5 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-[#d4af37]">
                            <option value="all">All Residences</option>
                            <option value="villa">Villas</option>
                            <option value="home">Homes & Townhouses</option>
                            <option value="apartment">Modern Apartments</option>
                        </select>
                    </div>

                    <div>
                        <label for="filter-type" class="block text-[11px] font-bold uppercase tracking-[0.2em] text-[#e7d8b7] mb-1">{{ \App\Models\SiteSetting::get('filter_type_label', 'Ownership Type') }}</label>
                        <select id="filter-type" name="type" class="w-full rounded-xl border border-white/20 bg-[#0f2821]/80 px-3.5 py-2.5 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-[#d4af37]">
                            <option value="">Any Status</option>
                            <option value="sale">For Sale (Freehold)</option>
                            <option value="rent">For Lease / Rent</option>
                        </select>
                    </div>

                    <div>
                        <label for="filter-location" class="block text-[11px] font-bold uppercase tracking-[0.2em] text-[#e7d8b7] mb-1">{{ \App\Models\SiteSetting::get('filter_location_label', 'Preferred Location') }}</label>
                        <select id="filter-location" name="location" class="w-full rounded-xl border border-white/20 bg-[#0f2821]/80 px-3.5 py-2.5 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-[#d4af37]">
                            <option value="">All Prime Districts</option>
                            <option value="harar">Harar Central</option>
                            <option value="bole">Addis Ababa / Bole</option>
                            <option value="kazanchis">Kazanchis Diplomatic Zone</option>
                            <option value="sarbet">Old Airport / Sarbet</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-1 pt-2 sm:pt-4 lg:pt-0">
                        <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#d4af37] via-[#c5a059] to-[#b38e46] px-5 py-3 text-sm font-bold uppercase tracking-[0.14em] text-[#0c1f1a] shadow-lg shadow-[#d4af37]/20 transition-all hover:brightness-110 cursor-pointer">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span>{{ \App\Models\SiteSetting::get('filter_button_text', 'Search Properties') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- PRESTIGE PERFORMANCE & CREDIBILITY COUNTER BAR --}}
    {{-- ========================================================================= --}}
    <section class="relative z-20 mx-auto max-w-7xl px-6 -mt-6 sm:-mt-8 lg:px-8">
        <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xl shadow-[#102b25]/5 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-center gap-4 p-2">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#f5ede0] text-[#9b6c17]">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-[#102b25]">{{ \App\Models\SiteSetting::get('counter_1_value', 'ETB 2.8B+') }}</p>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#587165]">{{ \App\Models\SiteSetting::get('counter_1_label', 'Property Volume') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 p-2">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#edf4ef] text-[#1d3c34]">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-[#102b25]">{{ $homeStats['sold'] ?? 0 }} Sold</p>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#587165]">{{ \App\Models\SiteSetting::get('counter_2_label', 'properties sold') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 p-2">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#f5ede0] text-[#9b6c17]">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-[#102b25]">{{ number_format($homeStats['happy_buyers'] ?? 0) }}</p>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#587165]">{{ \App\Models\SiteSetting::get('counter_3_label', 'happy buyers') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 p-2">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#edf4ef] text-[#1d3c34]">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-[#102b25]">{{ \App\Models\SiteSetting::get('counter_4_value', '18 Yrs') }}</p>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#587165]">{{ \App\Models\SiteSetting::get('counter_4_label', 'market expertise') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- MASTER-PLANNED LIFESTYLE & WORLD-CLASS AMENITIES (DIRECTLY FROM LA GARE) --}}
    {{-- ========================================================================= --}}
    <section class="mx-auto max-w-7xl px-6 py-20 lg:px-8">
        <div class="text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 rounded-full border border-[#d7c8a9] bg-[#f5ede0] px-4 py-1 text-xs font-bold uppercase tracking-[0.24em] text-[#102b25]">
                {{ \App\Models\SiteSetting::get('amenities_badge', 'Master-Planned Excellence') }}
            </div>
            <h2 class="mt-4 text-3xl font-black sm:text-4xl text-[#102b25] tracking-tight">
                {{ \App\Models\SiteSetting::get('amenities_title', 'A Curated Lifestyle Beyond Ordinary') }}
            </h2>
            <p class="mt-4 text-base leading-relaxed text-slate-600">
                {{ \App\Models\SiteSetting::get('amenities_description', 'Inspired by premier world-class master communities like La Gare, every residence is enveloped in pristine green courtyards, luxury retail boulevards, and total peace of mind.') }}
            </p>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Amenity 1: Jogging & Walking Tracks --}}
            <div class="group rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#c5a059]">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#edf4ef] text-[#1d3c34] transition-colors group-hover:bg-[#102b25] group-hover:text-[#e7d8b7]">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="mt-5 text-lg font-bold text-[#102b25]">{{ \App\Models\SiteSetting::get('amenity_1_title', 'Jogging & Walking Tracks') }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ \App\Models\SiteSetting::get('amenity_1_description', 'Shaded, tree-lined pedestrian circuits and safe morning running tracks weaving through the development.') }}
                </p>
            </div>

            {{-- Amenity 2: Expansive Green Parks --}}
            <div class="group rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#c5a059]">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#f5ede0] text-[#9b6c17] transition-colors group-hover:bg-[#102b25] group-hover:text-[#e7d8b7]">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
                <h3 class="mt-5 text-lg font-bold text-[#102b25]">{{ \App\Models\SiteSetting::get('amenity_2_title', 'Lush Parks & Courtyards') }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ \App\Models\SiteSetting::get('amenity_2_description', 'Manicured botanical gardens, peaceful open courtyards, and tranquil green sanctuaries for relaxation.') }}
                </p>
            </div>

            {{-- Amenity 3: Retail & Fine Dining Boulevard --}}
            <div class="group rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#c5a059]">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#edf4ef] text-[#1d3c34] transition-colors group-hover:bg-[#102b25] group-hover:text-[#e7d8b7]">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <h3 class="mt-5 text-lg font-bold text-[#102b25]">{{ \App\Models\SiteSetting::get('amenity_3_title', 'Retail & Dining Boulevard') }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ \App\Models\SiteSetting::get('amenity_3_description', 'Curated fashion boutiques, specialty cafes, and gourmet international restaurants steps from your lobby.') }}
                </p>
            </div>

            {{-- Amenity 4: Dedicated Cycling Tracks --}}
            <div class="group rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#c5a059]">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#f5ede0] text-[#9b6c17] transition-colors group-hover:bg-[#102b25] group-hover:text-[#e7d8b7]">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="mt-5 text-lg font-bold text-[#102b25]">{{ \App\Models\SiteSetting::get('amenity_4_title', 'Dedicated Cycling Tracks') }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ \App\Models\SiteSetting::get('amenity_4_description', 'Protected, car-free cycling corridors connecting all residential clusters, green hubs, and community facilities.') }}
                </p>
            </div>

            {{-- Amenity 5: Kids Play & Adventure Areas --}}
            <div class="group rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#c5a059]">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#edf4ef] text-[#1d3c34] transition-colors group-hover:bg-[#102b25] group-hover:text-[#e7d8b7]">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="mt-5 text-lg font-bold text-[#102b25]">{{ \App\Models\SiteSetting::get('amenity_5_title', 'Kids Play & Adventure') }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ \App\Models\SiteSetting::get('amenity_5_description', 'Safe, modern play areas, soft-turf family recreation zones, and interactive splash fountains.') }}
                </p>
            </div>

            {{-- Amenity 6: 24/7 Gated Concierge & Security --}}
            <div class="group rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#c5a059]">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#f5ede0] text-[#9b6c17] transition-colors group-hover:bg-[#102b25] group-hover:text-[#e7d8b7]">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="mt-5 text-lg font-bold text-[#102b25]">{{ \App\Models\SiteSetting::get('amenity_6_title', '24/7 Gated Security & Concierge') }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ \App\Models\SiteSetting::get('amenity_6_description', 'Multi-tier biometric access, discrete on-site security patrols, CCTV surveillance, and VIP reception.') }}
                </p>
            </div>

            {{-- Amenity 7: Wellness & Infinity Fitness Club --}}
            <div class="group rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#c5a059]">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#edf4ef] text-[#1d3c34] transition-colors group-hover:bg-[#102b25] group-hover:text-[#e7d8b7]">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <h3 class="mt-5 text-lg font-bold text-[#102b25]">{{ \App\Models\SiteSetting::get('amenity_7_title', 'Wellness & Fitness Club') }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ \App\Models\SiteSetting::get('amenity_7_description', 'State-of-the-art gym, restorative sauna and steam facilities, outdoor yoga deck, and swimming pool.') }}
                </p>
            </div>

            {{-- Amenity 8: 100% Guaranteed Backup Power & Water Reserves --}}
            <div class="group rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#c5a059]">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#f5ede0] text-[#9b6c17] transition-colors group-hover:bg-[#102b25] group-hover:text-[#e7d8b7]">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="mt-5 text-lg font-bold text-[#102b25]">{{ \App\Models\SiteSetting::get('amenity_8_title', '100% Power & Water Backup') }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ \App\Models\SiteSetting::get('amenity_8_description', 'Heavy-duty industrial generators and deep-well water reserve tanks guarantee zero disruption to your daily life.') }}
                </p>
            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- FEATURED PROPERTIES: Curated Luxury Residences --}}
    {{-- ========================================================================= --}}
    <section id="featured-properties" class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#9b6c17]">{{ \App\Models\SiteSetting::get('featured_properties_badge', 'Properties') }}</p>
                <h2 class="mt-2 text-3xl font-black text-[#102b25] tracking-tight">{{ \App\Models\SiteSetting::get('featured_properties_title', 'Featured Properties') }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('properties.index') }}" class="group inline-flex items-center gap-1.5 text-sm font-bold text-[#102b25] hover:text-[#9b6c17] transition mr-1 sm:mr-2">
                    <span>{{ \App\Models\SiteSetting::get('featured_properties_link_text', 'View all') }}</span>
                    <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                </a>
                <div data-carousel-nav="featured-properties" class="flex items-center gap-2">
                    <button type="button" data-carousel-prev="featured-properties" aria-label="Previous properties" class="group flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-full border border-[#d9cab3] bg-white text-[#102b25] shadow-xs transition-all duration-200 hover:bg-[#102b25] hover:text-[#e7d8b7] hover:border-[#102b25] active:scale-95 disabled:opacity-25 disabled:pointer-events-none cursor-pointer">
                        <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button type="button" data-carousel-next="featured-properties" aria-label="Next properties" class="group flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-full border border-[#d9cab3] bg-white text-[#102b25] shadow-xs transition-all duration-200 hover:bg-[#102b25] hover:text-[#e7d8b7] hover:border-[#102b25] active:scale-95 disabled:opacity-25 disabled:pointer-events-none cursor-pointer">
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div data-carousel="featured-properties" class="relative mt-8">
            <div data-carousel-track class="flex gap-6 overflow-x-auto scroll-smooth snap-x snap-mandatory py-4 -my-4 px-1 -mx-1 no-scrollbar select-none [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden cursor-grab active:cursor-grabbing">
                @forelse ($featuredProperties as $property)
                    @php
                        $cardGallery = $property->gallery_images;
                    @endphp
                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col" data-property-card data-property-json="{{ $propertyJson($property) }}">
                        <div class="group flex flex-col h-full overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl hover:border-[#c5a059]">
                            {{-- Card Image Multi-Photo Slider --}}
                            <div data-property-slider class="group/slider relative h-60 w-full overflow-hidden bg-[#e8efe8] shrink-0 select-none">
                                @forelse ($cardGallery as $idx => $imgUrl)
                                    <div data-property-slide class="absolute inset-0 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] {{ $idx === 0 ? 'opacity-100 scale-100 z-10' : 'opacity-0 scale-105 pointer-events-none z-0' }}">
                                        <img src="{{ $imgUrl }}" alt="{{ $property->title }}" class="h-full w-full object-cover">
                                    </div>
                                @empty
                                    <div class="relative h-full w-full">
                                        <img src="{{ asset($property->default_background_image) }}" alt="{{ $property->title }}" class="h-full w-full object-cover">
                                        <div class="absolute inset-0 bg-black/30 flex items-center justify-center text-xl font-black text-[#e7d8b7]">
                                            {{ Str::limit($property->title, 20) }}
                                        </div>
                                    </div>
                                @endforelse

                                <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-transparent to-black/20 pointer-events-none"></div>

                                {{-- Slider Left/Right Arrows if multiple images --}}
                                @if (count($cardGallery) > 1)
                                    <div class="absolute inset-x-2 top-1/2 -translate-y-1/2 flex items-center justify-between pointer-events-none z-20">
                                        <button type="button" data-slider-prev aria-label="Previous image" class="pointer-events-auto flex h-7 w-7 items-center justify-center rounded-full bg-black/65 text-[#e7d8b7] backdrop-blur-md opacity-0 group-hover/slider:opacity-100 transition-all duration-200 hover:bg-[#102b25] hover:text-white hover:border-[#d4af37] border border-white/20 active:scale-90 cursor-pointer">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                        </button>
                                        <button type="button" data-slider-next aria-label="Next image" class="pointer-events-auto flex h-7 w-7 items-center justify-center rounded-full bg-black/65 text-[#e7d8b7] backdrop-blur-md opacity-0 group-hover/slider:opacity-100 transition-all duration-200 hover:bg-[#102b25] hover:text-white hover:border-[#d4af37] border border-white/20 active:scale-90 cursor-pointer">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                        </button>
                                    </div>
                                @endif

                                {{-- Badges on top left --}}
                                <div class="absolute top-4 left-4 z-20 flex items-center gap-2">
                                    <span class="rounded-full bg-[#102b25]/85 backdrop-blur-md px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white border border-white/20">
                                        {{ ucfirst($getPropertyCategory($property)) }}
                                    </span>
                                    @if ($property->status === 'sold')
                                        <span class="rounded-full bg-[#8c2828] px-3 py-1 text-[11px] font-black uppercase tracking-wider text-white">Sold</span>
                                    @elseif ($property->featured)
                                        <span class="rounded-full bg-[#d4af37] px-3 py-1 text-[11px] font-black uppercase tracking-wider text-[#102b25]">Featured</span>
                                    @endif
                                </div>

                                {{-- Quickview Sidebar Trigger Button on top right --}}
                                <div class="absolute top-3.5 right-3.5 z-20">
                                    <button type="button" data-quickview-btn title="Open photo gallery preview sidebar" class="flex items-center gap-1.5 rounded-full border border-white/25 bg-black/65 px-2.5 py-1 text-[11px] font-bold text-[#e7d8b7] backdrop-blur-md shadow-md transition-all hover:bg-[#d4af37] hover:text-[#0c1f1a] hover:border-[#d4af37] active:scale-95 cursor-pointer">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Sidebar ({{ count($cardGallery) }})</span>
                                    </button>
                                </div>

                                {{-- Bottom row with details & counter --}}
                                <div class="absolute bottom-3 left-4 right-4 z-20 flex items-center justify-between text-white">
                                    <span class="text-xs font-semibold backdrop-blur-sm bg-black/40 px-2.5 py-1 rounded-full border border-white/10">{{ $property->city ?: 'Prime District' }}</span>
                                    @if (count($cardGallery) > 1)
                                        <span data-slider-counter class="text-[10px] font-bold backdrop-blur-sm bg-black/55 px-2 py-0.5 rounded-full border border-white/15 text-[#e7d8b7]">1 / {{ count($cardGallery) }}</span>
                                    @else
                                        <span class="text-xs font-semibold backdrop-blur-sm bg-black/40 px-2.5 py-1 rounded-full border border-white/10">{{ $property->bedrooms }} Bed • {{ $property->bathrooms }} Bath</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col flex-1 p-6">
                                <h3 class="text-xl font-bold text-[#102b25] group-hover:text-[#9b6c17] transition-colors line-clamp-1">
                                    <a href="{{ route('properties.show', $property->slug) }}">{{ $property->title }}</a>
                                </h3>
                                <p class="mt-2 text-sm text-slate-600">{{ $property->bedrooms }} bed • {{ $property->bathrooms }} bath • {{ number_format($property->area) }} sqm (ካሬ)</p>
                                
                                <div class="mt-auto pt-5 flex items-center justify-between border-t border-[#f0e8dc]">
                                    <div>
                                        <p class="text-[11px] uppercase tracking-wider text-slate-500">{{ $property->type === 'rent' ? 'Rental' : 'Asking Price' }}</p>
                                        <span class="text-xl font-black text-[#102b25]">ETB {{ number_format($property->price) }}{{ $property->type === 'rent' ? '/mo' : '' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" data-quickview-btn class="rounded-full border border-[#d9cab3] bg-[#fffaf2] px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-[#102b25] hover:bg-[#102b25] hover:text-[#e7d8b7] transition-colors cursor-pointer">
                                            Preview
                                        </button>
                                        <a href="{{ route('properties.show', $property->slug) }}" class="rounded-full bg-[#f5ede0] px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-[#9b6c17] group-hover:bg-[#102b25] group-hover:text-[#e7d8b7] transition-colors">
                                            Explore
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <article class="flex flex-col h-full overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white shadow-sm">
                            <div class="h-60 bg-gradient-to-br from-[#dfe8e3] via-[#edf3ee] to-[#e9d9b7] flex items-center justify-center text-xl font-black text-[#102b25] shrink-0">
                                Verona Heights
                            </div>
                            <div class="flex flex-col flex-1 p-6">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#9b6c17]">For Sale</p>
                                <h3 class="mt-2 text-xl font-bold text-[#102b25]">Verona Heights</h3>
                                <p class="mt-2 text-sm text-slate-600">3 bed • 2 bath • 180 sqm (ካሬ)</p>
                                <div class="mt-auto pt-5 flex items-center justify-between border-t border-[#f0e8dc]">
                                    <span class="text-xl font-black text-[#102b25]">ETB 410,000</span>
                                    <span class="text-sm font-semibold text-slate-500">Harar</span>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <article class="flex flex-col h-full overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-[#f9f5ee] shadow-sm">
                            <div class="h-60 bg-gradient-to-br from-[#d8c9a8] via-[#f8f3eb] to-[#e7ede7] flex items-center justify-center text-xl font-black text-[#102b25] shrink-0">
                                Aster Luxury Apartments
                            </div>
                            <div class="flex flex-col flex-1 p-6">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#9b6c17]">For Rent</p>
                                <h3 class="mt-2 text-xl font-bold text-[#102b25]">Aster Luxury Apartments</h3>
                                <p class="mt-2 text-sm text-slate-600">2 bed • 2 bath • 120 sqm (ካሬ)</p>
                                <div class="mt-auto pt-5 flex items-center justify-between border-t border-[#f0e8dc]">
                                    <span class="text-xl font-black text-[#102b25]">ETB 1,200/mo</span>
                                    <span class="text-sm font-semibold text-slate-500">City Center</span>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <article class="flex flex-col h-full overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white shadow-sm">
                            <div class="h-60 bg-gradient-to-br from-[#e3ece7] via-[#f7f3eb] to-[#dfe8dc] flex items-center justify-center text-xl font-black text-[#102b25] shrink-0">
                                Cedar Grove Villa
                            </div>
                            <div class="flex flex-col flex-1 p-6">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#9b6c17]">Investment</p>
                                <h3 class="mt-2 text-xl font-bold text-[#102b25]">Cedar Grove Villa</h3>
                                <p class="mt-2 text-sm text-slate-600">4 bed • 3 bath • 260 sqm (ካሬ)</p>
                                <div class="mt-auto pt-5 flex items-center justify-between border-t border-[#f0e8dc]">
                                    <span class="text-xl font-black text-[#102b25]">ETB 540,000</span>
                                    <span class="text-sm font-semibold text-slate-500">Bole</span>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforelse
            </div>

            {{-- Carousel Pagination Dots --}}
            <div data-carousel-dots="featured-properties" class="mt-6 flex items-center justify-center gap-2"></div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- ARCHITECTURAL PANORAMA: "Get Inspired by Magnificent Views" (LA GARE INSPIRED) --}}
    {{-- ========================================================================= --}}
    <section class="relative my-16 overflow-hidden bg-[#0a1714] text-white">
        <div class="absolute inset-0 z-0">
            <img src="{{ \App\Models\SiteSetting::imageUrl('panorama_image', 'images/luxury/central-plaza.jpg') }}" alt="Plaza and Horizon Views" class="h-full w-full object-cover object-center opacity-40">
            <div class="absolute inset-0 bg-gradient-to-r from-[#0a1714] via-[#0c1f1a]/85 to-[#0a1714]"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-6 py-24 text-center lg:px-8">
            <div class="inline-flex items-center gap-2 rounded-full border border-[#c5a059]/40 bg-[#c5a059]/15 px-4 py-1.5 text-xs font-bold uppercase tracking-[0.25em] text-[#e7d8b7] backdrop-blur-md">
                {{ \App\Models\SiteSetting::get('panorama_badge', 'Masterplan Skyline') }}
            </div>
            <h2 class="mt-6 text-3xl font-black sm:text-5xl tracking-tight text-white max-w-3xl mx-auto leading-tight">
                {{ \App\Models\SiteSetting::get('panorama_title', 'Get Inspired by Magnificent Views & Elevated Living') }}
            </h2>
            <p class="mt-6 max-w-2xl mx-auto text-lg leading-relaxed text-[#d7e5dc]">
                {{ \App\Models\SiteSetting::get('panorama_description', 'Experience panoramic sunrises, sprawling green courtyards, and iconic architecture engineered to the highest international quality standards.') }}
            </p>
            <div class="mt-10 flex flex-wrap justify-center gap-4">
                <a href="{{ url('/app#contact') }}" class="rounded-full bg-gradient-to-r from-[#d4af37] via-[#c5a059] to-[#b38e46] px-8 py-3.5 text-sm font-bold uppercase tracking-[0.16em] text-[#0c1f1a] shadow-xl shadow-[#d4af37]/25 transition hover:brightness-110">
                    {{ \App\Models\SiteSetting::get('panorama_primary_button_text', 'Schedule Private Tour') }}
                </a>
                <a href="{{ route('properties.index') }}" class="rounded-full border border-[#e7d8b7]/40 bg-white/10 px-8 py-3.5 text-sm font-semibold text-white backdrop-blur-md transition hover:bg-white/20">
                    {{ \App\Models\SiteSetting::get('panorama_secondary_button_text', 'View Masterplan Units') }}
                </a>
            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- DEVELOPMENTS & SIGNATURE PROJECTS (Managed by Admin in Site Settings) --}}
    {{-- ========================================================================= --}}
    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#9b6c17]">{{ \App\Models\SiteSetting::get('developments_badge', 'Developments') }}</p>
                <h2 class="mt-2 text-3xl font-black text-[#102b25] tracking-tight">{{ \App\Models\SiteSetting::get('developments_title', 'Featured Projects') }}</h2>
            </div>
            <a href="{{ \App\Models\SiteSetting::get('developments_link_url', route('projects')) }}" class="group inline-flex items-center gap-1.5 text-sm font-bold text-[#102b25] hover:text-[#9b6c17] transition">
                <span>{{ \App\Models\SiteSetting::get('developments_link_text', 'Explore projects') }}</span>
                <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
            </a>
        </div>

        <div class="mt-8 grid gap-8 md:grid-cols-3">
            @if ($featuredProjects->isNotEmpty() && ! \App\Models\SiteSetting::get('project_1_title') && ! \App\Models\SiteSetting::get('project_2_title') && ! \App\Models\SiteSetting::get('project_3_title'))
                @foreach ($featuredProjects as $project)
                    <div class="group overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-gradient-to-br from-[#102b25] to-[#1d3c34] text-white shadow-lg transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl">
                        <div class="p-8">
                            <span class="inline-block rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#e7d8b7]">{{ ucfirst($project->type) }}</span>
                            <h3 class="mt-5 text-2xl font-black text-white">{{ $project->title }}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-[#d7e5dc]">{{ Str::limit(strip_tags($project->content), 140) }}</p>
                        </div>
                    </div>
                @endforeach
            @else
                {{-- Project 1: Emerald Heights --}}
                <div class="group overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-[#102b25] text-white shadow-lg transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl">
                    <div class="relative h-48 w-full overflow-hidden">
                        <img src="{{ \App\Models\SiteSetting::imageUrl('project_1_image', 'images/luxury/luxury-towers.jpg') }}" alt="{{ \App\Models\SiteSetting::get('project_1_title', 'Emerald Heights') }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#102b25] via-transparent to-transparent"></div>
                        <span class="absolute top-4 left-4 rounded-full bg-[#d4af37] px-3 py-1 text-xs font-black uppercase tracking-wider text-[#102b25]">
                            {{ \App\Models\SiteSetting::get('project_1_tag', 'Luxury') }}
                        </span>
                    </div>
                    <div class="p-6">
                        <h3 class="text-2xl font-black text-white">{{ \App\Models\SiteSetting::get('project_1_title', 'Emerald Heights') }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-[#cbdad0]">{{ \App\Models\SiteSetting::get('project_1_description', 'Residential living designed around comfort, green views, and daily convenience.') }}</p>
                        <div class="mt-6 border-t border-white/10 pt-4 flex items-center justify-between text-xs text-[#e7d8b7] font-semibold">
                            <span>Master Residential Tower</span>
                            <span>Phase I &rarr;</span>
                        </div>
                    </div>
                </div>

                {{-- Project 2: Harar Square --}}
                <div class="group overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white shadow-lg transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl">
                    <div class="relative h-48 w-full overflow-hidden">
                        <img src="{{ \App\Models\SiteSetting::imageUrl('project_2_image', 'images/luxury/retail-boulevard.jpg') }}" alt="{{ \App\Models\SiteSetting::get('project_2_title', 'Harar Square') }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent"></div>
                        <span class="absolute top-4 left-4 rounded-full bg-[#102b25] px-3 py-1 text-xs font-black uppercase tracking-wider text-[#e7d8b7]">
                            {{ \App\Models\SiteSetting::get('project_2_tag', 'Commercial') }}
                        </span>
                    </div>
                    <div class="p-6">
                        <h3 class="text-2xl font-black text-[#102b25]">{{ \App\Models\SiteSetting::get('project_2_title', 'Harar Square') }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ \App\Models\SiteSetting::get('project_2_description', 'Mixed-use development and retail spaces scheduled for the next growth corridor.') }}</p>
                        <div class="mt-6 border-t border-[#f0e8dc] pt-4 flex items-center justify-between text-xs text-[#9b6c17] font-semibold">
                            <span>Retail & Corporate Plaza</span>
                            <span>Now Leasing &rarr;</span>
                        </div>
                    </div>
                </div>

                {{-- Project 3: Oakland Park --}}
                <div class="group overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white shadow-lg transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl">
                    <div class="relative h-48 w-full overflow-hidden">
                        <img src="{{ \App\Models\SiteSetting::imageUrl('project_3_image', 'images/luxury/panoramic-park.jpg') }}" alt="{{ \App\Models\SiteSetting::get('project_3_title', 'Oakland Park') }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent"></div>
                        <span class="absolute top-4 left-4 rounded-full bg-[#9b6c17] px-3 py-1 text-xs font-black uppercase tracking-wider text-white">
                            {{ \App\Models\SiteSetting::get('project_3_tag', 'Future plan') }}
                        </span>
                    </div>
                    <div class="p-6">
                        <h3 class="text-2xl font-black text-[#102b25]">{{ \App\Models\SiteSetting::get('project_3_title', 'Oakland Park') }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ \App\Models\SiteSetting::get('project_3_description', 'A connected master-planned community focused on smart, sustainable growth.') }}</p>
                        <div class="mt-6 border-t border-[#f0e8dc] pt-4 flex items-center justify-between text-xs text-[#102b25] font-semibold">
                            <span>Eco-Planned Living</span>
                            <span>Register &rarr;</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- ========================================================================= --}}
    {{-- LATEST PROPERTIES                                                         --}}
    {{-- ========================================================================= --}}
    <section id="latest-properties" class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#9b6c17]">{{ \App\Models\SiteSetting::get('latest_properties_badge', 'New listings') }}</p>
                <h2 class="mt-2 text-3xl font-black text-[#102b25] tracking-tight">{{ \App\Models\SiteSetting::get('latest_properties_title', 'Latest Properties') }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('properties.index') }}" class="group inline-flex items-center gap-1.5 text-sm font-bold text-[#102b25] hover:text-[#9b6c17] transition mr-1 sm:mr-2">
                    <span>{{ \App\Models\SiteSetting::get('latest_properties_link_text', 'Browse more') }}</span>
                    <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                </a>
                {{-- Top Slider Bar Navigation (Prev/Next buttons) like Latest News --}}
                <div data-carousel-nav="latest-properties" class="flex items-center gap-2">
                    <button type="button" data-carousel-prev="latest-properties" aria-label="Previous properties" class="group flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-full border border-[#d9cab3] bg-white text-[#102b25] shadow-xs transition-all duration-200 hover:bg-[#102b25] hover:text-[#e7d8b7] hover:border-[#102b25] active:scale-95 disabled:opacity-25 disabled:pointer-events-none cursor-pointer">
                        <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button type="button" data-carousel-next="latest-properties" aria-label="Next properties" class="group flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-full border border-[#d9cab3] bg-white text-[#102b25] shadow-xs transition-all duration-200 hover:bg-[#102b25] hover:text-[#e7d8b7] hover:border-[#102b25] active:scale-95 disabled:opacity-25 disabled:pointer-events-none cursor-pointer">
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div data-carousel="latest-properties" class="relative mt-8">
            <div data-carousel-track class="flex gap-6 overflow-x-auto scroll-smooth snap-x snap-mandatory py-4 -my-4 px-1 -mx-1 no-scrollbar select-none [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden cursor-grab active:cursor-grabbing">
                @forelse ($latestProperties as $property)
                    @php
                        $latestGallery = $property->gallery_images;
                    @endphp
                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col" data-property-card data-property-json="{{ $propertyJson($property) }}">
                        <div class="group flex flex-col h-full overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl hover:border-[#c5a059]">
                            {{-- Card Image Multi-Photo Slider --}}
                            <div data-property-slider class="group/slider relative h-60 w-full overflow-hidden bg-[#e8efe8] shrink-0 select-none">
                                @forelse ($latestGallery as $idx => $imgUrl)
                                    <div data-property-slide class="absolute inset-0 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] {{ $idx === 0 ? 'opacity-100 scale-100 z-10' : 'opacity-0 scale-105 pointer-events-none z-0' }}">
                                        <img src="{{ $imgUrl }}" alt="{{ $property->title }}" class="h-full w-full object-cover">
                                    </div>
                                @empty
                                    <div class="relative h-full w-full">
                                        <img src="{{ asset($property->default_background_image) }}" alt="{{ $property->title }}" class="h-full w-full object-cover">
                                        <div class="absolute inset-0 bg-black/30 flex items-center justify-center text-xl font-black text-[#e7d8b7]">
                                            {{ Str::limit($property->title, 20) }}
                                        </div>
                                    </div>
                                @endforelse

                                <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-transparent to-black/20 pointer-events-none"></div>

                                {{-- Slider Left/Right Arrows if multiple images --}}
                                @if (count($latestGallery) > 1)
                                    <div class="absolute inset-x-2 top-1/2 -translate-y-1/2 flex items-center justify-between pointer-events-none z-20">
                                        <button type="button" data-slider-prev aria-label="Previous image" class="pointer-events-auto flex h-7 w-7 items-center justify-center rounded-full bg-black/65 text-[#e7d8b7] backdrop-blur-md opacity-0 group-hover/slider:opacity-100 transition-all duration-200 hover:bg-[#102b25] hover:text-white hover:border-[#d4af37] border border-white/20 active:scale-90 cursor-pointer">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                        </button>
                                        <button type="button" data-slider-next aria-label="Next image" class="pointer-events-auto flex h-7 w-7 items-center justify-center rounded-full bg-black/65 text-[#e7d8b7] backdrop-blur-md opacity-0 group-hover/slider:opacity-100 transition-all duration-200 hover:bg-[#102b25] hover:text-white hover:border-[#d4af37] border border-white/20 active:scale-90 cursor-pointer">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                        </button>
                                    </div>
                                @endif

                                {{-- Badges on top left --}}
                                <div class="absolute top-4 left-4 z-20 flex items-center gap-2">
                                    <span class="rounded-full bg-[#102b25]/85 backdrop-blur-md px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white border border-white/20">
                                        {{ ucfirst($getPropertyCategory($property)) }}
                                    </span>
                                    @if ($property->status === 'sold')
                                        <span class="rounded-full bg-[#8c2828] px-3 py-1 text-[11px] font-black uppercase tracking-wider text-white">Sold</span>
                                    @elseif ($property->featured)
                                        <span class="rounded-full bg-[#d4af37] px-3 py-1 text-[11px] font-black uppercase tracking-wider text-[#102b25]">Featured</span>
                                    @else
                                        <span class="rounded-full bg-[#102b25] px-3 py-1 text-[11px] font-black uppercase tracking-wider text-[#e7d8b7] border border-[#d4af37]/40">New</span>
                                    @endif
                                </div>

                                {{-- Quickview Sidebar Trigger Button on top right --}}
                                <div class="absolute top-3.5 right-3.5 z-20">
                                    <button type="button" data-quickview-btn title="Open photo gallery preview sidebar" class="flex items-center gap-1.5 rounded-full border border-white/25 bg-black/65 px-2.5 py-1 text-[11px] font-bold text-[#e7d8b7] backdrop-blur-md shadow-md transition-all hover:bg-[#d4af37] hover:text-[#0c1f1a] hover:border-[#d4af37] active:scale-95 cursor-pointer">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Sidebar ({{ count($latestGallery) }})</span>
                                    </button>
                                </div>

                                {{-- Bottom row with details & counter --}}
                                <div class="absolute bottom-3 left-4 right-4 z-20 flex items-center justify-between text-white">
                                    <span class="text-xs font-semibold backdrop-blur-sm bg-black/40 px-2.5 py-1 rounded-full border border-white/10">{{ $property->city ?: 'Prime District' }}</span>
                                    @if (count($latestGallery) > 1)
                                        <span data-slider-counter class="text-[10px] font-bold backdrop-blur-sm bg-black/55 px-2 py-0.5 rounded-full border border-white/15 text-[#e7d8b7]">1 / {{ count($latestGallery) }}</span>
                                    @else
                                        <span class="text-xs font-semibold backdrop-blur-sm bg-black/40 px-2.5 py-1 rounded-full border border-white/10">{{ $property->bedrooms }} Bed • {{ $property->bathrooms }} Bath</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col flex-1 p-6">
                                <h3 class="text-xl font-bold text-[#102b25] group-hover:text-[#9b6c17] transition-colors line-clamp-1">
                                    <a href="{{ route('properties.show', $property->slug) }}">{{ $property->title }}</a>
                                </h3>
                                <p class="mt-2 text-sm text-slate-600">{{ $property->bedrooms }} bed • {{ $property->bathrooms }} bath • {{ number_format($property->area) }} sqm (ካሬ)</p>
                                
                                <div class="mt-auto pt-5 flex items-center justify-between border-t border-[#f0e8dc]">
                                    <div>
                                        <p class="text-[11px] uppercase tracking-wider text-slate-500">{{ $property->type === 'rent' ? 'Rental' : 'Asking Price' }}</p>
                                        <span class="text-xl font-black text-[#102b25]">ETB {{ number_format($property->price) }}{{ $property->type === 'rent' ? '/mo' : '' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" data-quickview-btn class="rounded-full border border-[#d9cab3] bg-[#fffaf2] px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-[#102b25] hover:bg-[#102b25] hover:text-[#e7d8b7] transition-colors cursor-pointer">
                                            Preview
                                        </button>
                                        <a href="{{ route('properties.show', $property->slug) }}" class="rounded-full bg-[#f5ede0] px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-[#9b6c17] group-hover:bg-[#102b25] group-hover:text-[#e7d8b7] transition-colors">
                                            Explore
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <article class="flex flex-col h-full overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white p-7 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#9b6c17]">New</p>
                            <h3 class="mt-4 text-xl font-bold text-[#102b25]">Sunrise Apartments</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Modern, light-filled homes with premium finishes and neighborhood access.</p>
                        </article>
                    </div>
                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <article class="flex flex-col h-full overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-[#f9f5ee] p-7 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#9b6c17]">Updated</p>
                            <h3 class="mt-4 text-xl font-bold text-[#102b25]">Hillcrest Villas</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Spacious villas designed for lifestyle, comfort, and long-term returns.</p>
                        </article>
                    </div>
                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <article class="flex flex-col h-full overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white p-7 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#9b6c17]">Popular</p>
                            <h3 class="mt-4 text-xl font-bold text-[#102b25]">Greenway Residences</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">A growing urban community balancing convenience, green space, and value.</p>
                        </article>
                    </div>
                @endforelse
            </div>
            <div data-carousel-dots="latest-properties" class="mt-6 flex items-center justify-center gap-2"></div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- ABOUT & HERITAGE SECTION --}}
    {{-- ========================================================================= --}}
    <section id="about" class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="rounded-[2.5rem] border border-[#d9cab3] bg-gradient-to-br from-[#f9f5ee] via-[#fffdf9] to-[#edf4ef] p-8 lg:p-12 shadow-sm">
            <div class="grid gap-10 lg:grid-cols-2 items-center">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#9b6c17]">{{ \App\Models\SiteSetting::get('about_badge', 'About '.$siteBrand['name']) }}</p>
                    <h2 class="mt-3 text-3xl font-black text-[#102b25] sm:text-4xl tracking-tight leading-tight">
                        {{ \App\Models\SiteSetting::get('about_title', 'Trusted guidance for every move') }}
                    </h2>
                    <p class="mt-5 text-base leading-relaxed text-slate-600">
                        {{ \App\Models\SiteSetting::get('about_description', 'We help buyers, sellers, and investors discover exceptional properties with trust, transparency, and local expertise. From first viewing to final paperwork, we make every step clear and confident.') }}
                    </p>
                    <div class="mt-8 grid grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-[#d9cab3] bg-white p-4">
                            <p class="text-xl font-black text-[#102b25]">{{ \App\Models\SiteSetting::get('about_pillar_1_title', '100%') }}</p>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">{{ \App\Models\SiteSetting::get('about_pillar_1_desc', 'Clear Legal Titles') }}</p>
                        </div>
                        <div class="rounded-2xl border border-[#d9cab3] bg-white p-4">
                            <p class="text-xl font-black text-[#102b25]">{{ \App\Models\SiteSetting::get('about_pillar_2_title', 'VIP') }}</p>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">{{ \App\Models\SiteSetting::get('about_pillar_2_desc', 'Concierge Advisory') }}</p>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-3xl border border-[#d9cab3] shadow-lg aspect-[4/3]">
                    <img src="{{ \App\Models\SiteSetting::imageUrl('about_image', 'images/luxury/retail-boulevard.jpg') }}" alt="{{ \App\Models\SiteSetting::get('about_image_title', 'Addis Ababa & Harar Master Developments') }}" class="h-full w-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#102b25]/80 via-transparent to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6 text-white">
                        <p class="text-xs uppercase tracking-[0.2em] text-[#e7d8b7] font-bold">{{ \App\Models\SiteSetting::get('about_image_subtitle', 'Heritage of Distinction') }}</p>
                        <p class="text-lg font-bold mt-1">{{ \App\Models\SiteSetting::get('about_image_title', 'Addis Ababa & Harar Master Developments') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- BLOG, NEWS & CAREER OPPORTUNITIES --}}
    {{-- ========================================================================= --}}
    <section id="news" class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#9b6c17]">{{ \App\Models\SiteSetting::get('news_badge', 'Blog & News') }}</p>
                <h2 class="mt-2 text-3xl font-black text-[#102b25] tracking-tight">{{ \App\Models\SiteSetting::get('news_title', 'Latest News') }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('blogs') }}" class="group inline-flex items-center gap-1.5 text-sm font-bold text-[#102b25] hover:text-[#9b6c17] transition mr-1 sm:mr-2">
                    <span>{{ \App\Models\SiteSetting::get('news_link_text', 'View all') }}</span>
                    <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                </a>
                <div data-carousel-nav="news" class="flex items-center gap-2">
                    <button type="button" data-carousel-prev="news" aria-label="Previous news" class="group flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-full border border-[#d9cab3] bg-white text-[#102b25] shadow-xs transition-all duration-200 hover:bg-[#102b25] hover:text-[#e7d8b7] hover:border-[#102b25] active:scale-95 disabled:opacity-25 disabled:pointer-events-none cursor-pointer">
                        <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button type="button" data-carousel-next="news" aria-label="Next news" class="group flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-full border border-[#d9cab3] bg-white text-[#102b25] shadow-xs transition-all duration-200 hover:bg-[#102b25] hover:text-[#e7d8b7] hover:border-[#102b25] active:scale-95 disabled:opacity-25 disabled:pointer-events-none cursor-pointer">
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div data-carousel="news" class="relative mt-8">
            <div data-carousel-track class="flex gap-6 overflow-x-auto scroll-smooth snap-x snap-mandatory py-4 -my-4 px-1 -mx-1 no-scrollbar select-none [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden cursor-grab active:cursor-grabbing">
                @forelse ($posts as $post)
                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        @if ($post->type === 'job')
                            {{-- PROFESSIONAL "WE ARE HIRING" CARD — Corporate green/gold social media style --}}
                            <article class="group relative flex flex-col h-full overflow-hidden rounded-2xl shadow-lg transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">

                                {{-- === TOP SECTION: Dark green header with "WE ARE HIRING" + gold accents === --}}
                                <div class="relative overflow-hidden shrink-0"
                                     style="background: linear-gradient(135deg, #102b25 0%, #1a3d34 50%, #0e241f 100%);">

                                    {{-- Gold corner triangle accent (top-right) --}}
                                    <div class="absolute top-0 right-0 w-24 h-24 pointer-events-none" style="background: linear-gradient(225deg, #d4af37 0%, #d4af37 50%, transparent 50%);"></div>

                                    {{-- Briefcase watermark --}}
                                    <div class="absolute -right-4 -bottom-4 opacity-[0.06] pointer-events-none text-[#d4af37]">
                                        <svg class="h-28 w-28" fill="currentColor" viewBox="0 0 24 24"><path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/></svg>
                                    </div>

                                    <div class="relative z-10 px-5 pt-6 pb-4">
                                        {{-- "WE ARE HIRING" Bold Headline --}}
                                        <h3 class="text-2xl sm:text-[1.7rem] font-black uppercase tracking-wide leading-tight" style="color: #d4af37;">
                                            We Are<br>Hiring
                                        </h3>

                                        {{-- Employment type badge --}}
                                        <div class="mt-2">
                                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.15em] border" style="background: rgba(212,175,55,0.15); color: #e7d8b7; border-color: rgba(212,175,55,0.4);">
                                                <span class="h-1.5 w-1.5 rounded-full animate-pulse" style="background: #d4af37;"></span>
                                                {{ $post->job_type ?: 'Full-time' }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Geometric gold divider line --}}
                                    <div class="relative flex items-center justify-center px-5 pb-1">
                                        <div class="flex-1 h-px" style="background: linear-gradient(90deg, transparent, #d4af37);"></div>
                                        <div class="mx-2 h-2.5 w-2.5 rotate-45 border" style="border-color: #d4af37;"></div>
                                        <div class="flex-1 h-px" style="background: linear-gradient(90deg, #d4af37, transparent);"></div>
                                    </div>

                                    {{-- Top image banner (if uploaded) --}}
                                    @if ($post->image_url)
                                        <div class="relative h-32 w-full overflow-hidden mt-1">
                                            <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">
                                            <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(to bottom, rgba(16,43,37,0.4) 0%, transparent 40%, rgba(16,43,37,0.6) 100%);"></div>
                                        </div>
                                    @endif
                                </div>

                                {{-- === BOTTOM SECTION: Card content on dark green === --}}
                                <div class="flex flex-col flex-1 px-5 pt-4 pb-5" style="background: linear-gradient(180deg, #133029 0%, #102b25 100%);">

                                    {{-- Job Title --}}
                                    <h4 class="text-base font-extrabold text-white leading-snug line-clamp-2 group-hover:text-[#d4af37] transition-colors">
                                        {{ $post->title }}
                                    </h4>

                                    {{-- Category / Department --}}
                                    <div class="mt-2 flex items-center gap-1.5 text-xs" style="color: #8ab89e;">
                                        <svg class="h-3.5 w-3.5 shrink-0" style="color: #d4af37;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <span class="font-semibold">{{ $post->category ?: 'Career Opening' }}</span>
                                    </div>

                                    {{-- Location --}}
                                    <div class="mt-1.5 flex items-center gap-1.5 text-xs" style="color: #8ab89e;">
                                        <svg class="h-3.5 w-3.5 shrink-0" style="color: #d4af37;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="font-semibold">{{ $post->job_location ?: 'Addis Ababa' }}</span>
                                    </div>

                                    {{-- Gold thin divider --}}
                                    <div class="my-3 h-px w-full" style="background: linear-gradient(90deg, transparent, rgba(212,175,55,0.4), transparent);"></div>

                                    {{-- Metadata pills --}}
                                    <div class="flex flex-wrap gap-1.5">
                                        @if ($post->salary_range)
                                            <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10px] font-bold" style="background: rgba(212,175,55,0.12); color: #d4af37; border: 1px solid rgba(212,175,55,0.25);">
                                                <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                {{ $post->salary_range }}
                                            </span>
                                        @endif
                                        @if ($post->experience_level)
                                            <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10px] font-bold" style="background: rgba(138,184,158,0.12); color: #8ab89e; border: 1px solid rgba(138,184,158,0.2);">
                                                <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                                {{ $post->experience_level }}
                                            </span>
                                        @endif
                                        @if ($post->application_deadline)
                                            <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10px] font-bold" style="background: rgba(255,120,100,0.1); color: #ff9a8b; border: 1px solid rgba(255,120,100,0.2);">
                                                <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                Closes {{ $post->application_deadline->format('M d') }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Description excerpt --}}
                                    <p class="mt-2.5 text-[11px] leading-relaxed line-clamp-2" style="color: #7a9f8c;">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($post->content), 110) }}
                                    </p>

                                    {{-- Spacer --}}
                                    <div class="flex-1"></div>

                                    {{-- APPLY NOW button area --}}
                                    <div class="mt-4">
                                        @php
                                            $applyUrl = $post->apply_link ?: route('careers');
                                        @endphp

                                        @if ($post->apply_link)
                                            <div class="mb-2.5 text-xs text-[#a4b8ad]">
                                                <span class="font-bold text-white">Apply Link:</span>
                                                <a href="{{ $post->apply_link }}" target="_blank" rel="noopener" class="font-semibold text-[#d4af37] underline hover:text-[#f3e5ab] break-all">
                                                    {{ $post->apply_link }}
                                                </a>
                                            </div>
                                        @endif

                                        <a href="{{ $applyUrl }}" target="{{ $post->apply_link ? '_blank' : '_self' }}" rel="noopener"
                                           class="flex items-center justify-center gap-2 w-full rounded-lg py-2.5 text-sm font-black uppercase tracking-wider transition-all duration-200 hover:scale-[1.03] active:scale-95"
                                           style="background: #d4af37; color: #102b25;">
                                            <span>Apply Now</span>
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </a>

                                        <div class="mt-2 text-center">
                                            <a href="{{ route('careers') }}" class="text-[11px] font-semibold transition hover:underline" style="color: #8ab89e;">
                                                View All Careers &rarr;
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @else
                            {{-- STANDARD BLOG / NEWS / EDITORIAL CARD --}}
                            <article class="group flex flex-col h-full overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl hover:border-[#c5a059]">
                                @if ($post->image_url)
                                    <div class="relative h-52 w-full overflow-hidden bg-[#e8efe8] shrink-0">
                                        <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                    </div>
                                @endif
                                <div class="flex flex-col flex-1 p-6">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="rounded-full bg-[#f5ede0] px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-[#9b6c17]">
                                            {{ ucfirst($post->type) }}
                                        </span>
                                        @if ($post->category)
                                            <span class="rounded-full bg-[#edf4ef] px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-[#102b25]">{{ $post->category }}</span>
                                        @endif
                                    </div>

                                    <h3 class="mt-4 text-xl font-bold text-[#102b25] group-hover:text-[#9b6c17] transition-colors line-clamp-1">{{ $post->title }}</h3>
                                    <p class="mt-3 text-sm leading-relaxed text-slate-600 flex-1">{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 150) }}</p>
                                    
                                    <div class="mt-auto pt-6 flex items-center justify-between gap-3 border-t border-[#f0e8dc] text-xs">
                                        <span class="font-bold uppercase tracking-wider text-[#102b25]">By {{ $post->author_name ?? $post->user->name }}</span>
                                        @if ($post->published_at)
                                            <span class="text-slate-500">{{ $post->published_at->format('M d, Y') }}</span>
                                        @endif
                                    </div>

                                    @if ($post->apply_link)
                                        @php
                                            $applyUrl = $post->apply_link ?: route('careers');
                                        @endphp
                                        <div class="mt-4 border-t border-[#f0e8dc] pt-3">
                                            <div class="mb-2.5 text-xs">
                                                <span class="font-bold text-[#102b25]">Apply Link:</span>
                                                <a href="{{ $post->apply_link }}" target="_blank" rel="noopener" class="font-semibold text-[#1d3c34] underline hover:text-[#9b6c17] break-all">
                                                    {{ $post->apply_link }}
                                                </a>
                                            </div>
                                            <div class="flex items-center justify-between gap-3">
                                                <a href="{{ $applyUrl }}" target="{{ $post->apply_link ? '_blank' : '_self' }}" rel="noopener" class="inline-flex items-center gap-1.5 rounded-full bg-[#102b25] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#1a4037]">
                                                    <span>Apply Now</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                    </svg>
                                                </a>
                                                <a href="{{ route('careers') }}" class="text-xs font-bold text-[#102b25] hover:underline">
                                                    View Careers &rarr;
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @endif
                    </div>
                @empty
                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <article class="flex flex-col h-full rounded-[2rem] border border-[#d9cab3] bg-white p-7 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#9b6c17]">Blog</p>
                            <h3 class="mt-4 text-xl font-bold text-[#102b25]">How to choose the right neighborhood</h3>
                            <p class="mt-3 text-sm leading-relaxed text-slate-600 flex-1">Learn what matters most when buying a property, from schools to commute time and future growth.</p>
                        </article>
                    </div>

                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <article class="flex flex-col h-full rounded-[2rem] border border-[#d9cab3] bg-[#f9f5ee] p-7 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#9b6c17]">News</p>
                            <h3 class="mt-4 text-xl font-bold text-[#102b25]">New housing projects in the city center</h3>
                            <p class="mt-3 text-sm leading-relaxed text-slate-600 flex-1">Several modern residential buildings are opening this quarter with flexible payment plans and smart features.</p>
                        </article>
                    </div>

                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <article class="flex flex-col h-full rounded-[2rem] border border-[#d9cab3] bg-white p-7 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#9b6c17]">Market</p>
                            <h3 class="mt-4 text-xl font-bold text-[#102b25]">Investment tips for first-time buyers</h3>
                            <p class="mt-3 text-sm leading-relaxed text-slate-600 flex-1">Discover practical steps to evaluate property value, rental demand, and long-term return potential.</p>
                        </article>
                    </div>
                @endforelse
            </div>

            {{-- Carousel Pagination Dots --}}
            <div data-carousel-dots="news" class="mt-6 flex items-center justify-center gap-2"></div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- TESTIMONIALS: Client Feedback & Verified Buyer Reviews --}}
    {{-- ========================================================================= --}}
    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#9b6c17]">{{ \App\Models\SiteSetting::get('testimonials_badge', 'Client feedback') }}</p>
                <h2 class="mt-2 text-3xl font-black text-[#102b25] tracking-tight">{{ \App\Models\SiteSetting::get('testimonials_title', 'Testimonials') }}</h2>
            </div>
            <div data-carousel-nav="testimonials" class="flex items-center gap-2">
                <button type="button" data-carousel-prev="testimonials" aria-label="Previous testimonials" class="group flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-full border border-[#d9cab3] bg-white text-[#102b25] shadow-xs transition-all duration-200 hover:bg-[#102b25] hover:text-[#e7d8b7] hover:border-[#102b25] active:scale-95 disabled:opacity-25 disabled:pointer-events-none cursor-pointer">
                    <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button type="button" data-carousel-next="testimonials" aria-label="Next testimonials" class="group flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-full border border-[#d9cab3] bg-white text-[#102b25] shadow-xs transition-all duration-200 hover:bg-[#102b25] hover:text-[#e7d8b7] hover:border-[#102b25] active:scale-95 disabled:opacity-25 disabled:pointer-events-none cursor-pointer">
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <div data-carousel="testimonials" class="relative mt-8">
            <div data-carousel-track class="flex gap-6 overflow-x-auto scroll-smooth snap-x snap-mandatory py-4 -my-4 px-1 -mx-1 no-scrollbar select-none [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden cursor-grab active:cursor-grabbing">
                @forelse ($testimonials as $testimonial)
                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <div class="flex flex-col h-full rounded-[2rem] border border-[#d9cab3] {{ $loop->index % 2 === 1 ? 'bg-[#f9f5ee]' : 'bg-white' }} p-7 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl hover:border-[#c5a059]">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-bold text-[#d4af37] tracking-wider">{{ str_repeat('★', $testimonial->rating) }}{{ str_repeat('☆', 5 - $testimonial->rating) }}</span>
                                @if ($testimonial->created_at)
                                    <span class="text-[11px] font-semibold text-slate-500">{{ $testimonial->created_at->format('M d, Y') }}</span>
                                @endif
                            </div>
                            <p class="mt-4 flex-1 text-sm leading-relaxed text-slate-700 italic">“{{ Str::limit($testimonial->message, 220) }}”</p>
                            <div class="mt-auto pt-6 flex items-center gap-3 border-t border-[#e7dcc4]">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#102b25] text-xs font-black text-[#e7d8b7]">
                                    {{ collect(explode(' ', $testimonial->name))->map(fn ($word) => Str::upper(Str::substr($word, 0, 1)))->take(2)->implode('') }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-[#102b25]">{{ $testimonial->name }}</p>
                                    @if ($testimonial->agent)
                                        <p class="truncate text-xs text-slate-500">Client of {{ $testimonial->agent->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <div class="flex flex-col h-full rounded-[2rem] border border-[#d9cab3] bg-white p-7 shadow-sm">
                            <p class="text-sm font-bold text-[#d4af37]">★★★★★</p>
                            <p class="mt-4 flex-1 text-sm leading-relaxed text-slate-700">“The team helped us find the right master-plan villa in less than two weeks. Every step was transparent and professional.”</p>
                            <div class="mt-auto pt-6 text-sm font-bold text-[#102b25]">Amanuel & Selam</div>
                        </div>
                    </div>
                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <div class="flex flex-col h-full rounded-[2rem] border border-[#d9cab3] bg-[#f9f5ee] p-7 shadow-sm">
                            <p class="text-sm font-bold text-[#d4af37]">★★★★★</p>
                            <p class="mt-4 flex-1 text-sm leading-relaxed text-slate-700">“Their local knowledge and high-end guidance gave us absolute confidence in every investment decision.”</p>
                            <div class="mt-auto pt-6 text-sm font-bold text-[#102b25]">Hana T.</div>
                        </div>
                    </div>
                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <div class="flex flex-col h-full rounded-[2rem] border border-[#d9cab3] bg-white p-7 shadow-sm">
                            <p class="text-sm font-bold text-[#d4af37]">★★★★★</p>
                            <p class="mt-4 flex-1 text-sm leading-relaxed text-slate-700">“From the private tour to paperwork and title handover, we felt genuinely valued as luxury clients.”</p>
                            <div class="mt-auto pt-6 text-sm font-bold text-[#102b25]">Tesfaye G.</div>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Carousel Pagination Dots --}}
            <div data-carousel-dots="testimonials" class="mt-6 flex items-center justify-center gap-2"></div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- OUR ADVISORS: Real Estate Consultants & Agents --}}
    {{-- ========================================================================= --}}
    <section id="agents" class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#9b6c17]">{{ \App\Models\SiteSetting::get('agents_badge', 'Our agents') }}</p>
                <h2 class="mt-2 text-3xl font-black text-[#102b25] tracking-tight">{{ \App\Models\SiteSetting::get('agents_title', 'Real people, local expertise') }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('agents') }}" class="group inline-flex items-center gap-1.5 text-sm font-bold text-[#102b25] hover:text-[#9b6c17] transition mr-1 sm:mr-2">
                    <span>{{ \App\Models\SiteSetting::get('agents_link_text', 'All agents') }}</span>
                    <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                </a>
                <div data-carousel-nav="agents" class="flex items-center gap-2">
                    <button type="button" data-carousel-prev="agents" aria-label="Previous agents" class="group flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-full border border-[#d9cab3] bg-white text-[#102b25] shadow-xs transition-all duration-200 hover:bg-[#102b25] hover:text-[#e7d8b7] hover:border-[#102b25] active:scale-95 disabled:opacity-25 disabled:pointer-events-none cursor-pointer">
                        <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button type="button" data-carousel-next="agents" aria-label="Next agents" class="group flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-full border border-[#d9cab3] bg-white text-[#102b25] shadow-xs transition-all duration-200 hover:bg-[#102b25] hover:text-[#e7d8b7] hover:border-[#102b25] active:scale-95 disabled:opacity-25 disabled:pointer-events-none cursor-pointer">
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div data-carousel="agents" class="relative mt-8">
            <div data-carousel-track class="flex gap-6 overflow-x-auto scroll-smooth snap-x snap-mandatory py-4 -my-4 px-1 -mx-1 no-scrollbar select-none [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden cursor-grab active:cursor-grabbing">
                @forelse ($agents ?? collect() as $index => $agent)
                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <div class="flex flex-col h-full overflow-hidden rounded-[2rem] border border-[#d9cab3] {{ $index % 2 === 1 ? 'bg-[#f9f5ee]' : 'bg-white' }} shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl hover:border-[#c5a059]">
                            <a href="{{ route('agents.show', $agent) }}" class="block h-52 w-full overflow-hidden bg-[#e8efe8] relative group/photo shrink-0">
                                @if ($agent->photo_url)
                                    <img src="{{ $agent->photo_url }}" alt="{{ $agent->name }}" class="h-full w-full object-cover transition duration-500 group-hover/photo:scale-105">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#dfe8e3] via-[#edf3ee] to-[#e9d9b7] text-2xl font-black text-[#102b25]">
                                        {{ collect(explode(' ', $agent->name))->map(fn ($w) => Str::upper(Str::substr($w, 0, 1)))->take(2)->implode('') }}
                                    </div>
                                @endif
                            </a>
                            <div class="flex flex-col flex-1 p-6">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-bold text-[#102b25]">
                                        <a href="{{ route('agents.show', $agent) }}" class="transition hover:text-[#9b6c17]">{{ $agent->name }}</a>
                                    </h3>
                                    @if ($agent->feedback_count > 0)
                                        <span class="rounded-full bg-[#f5ede0] px-2.5 py-0.5 text-xs font-bold text-[#9b6c17]">
                                            {{ number_format($agent->average_rating, 1) }} ★
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-slate-600 flex-1">{{ $agent->bio ?: 'Senior Property Consultant' }}</p>
                                
                                <div class="mt-auto pt-5 flex flex-wrap gap-2 border-t border-[#f0e8dc]">
                                    <a href="{{ route('agents.show', $agent) }}" class="rounded-full bg-[#102b25] px-4 py-2 text-xs font-bold uppercase tracking-wider text-white transition hover:bg-[#1a4037]">Contact {{ $agent->name }}</a>
                                    @if ($agent->phone)
                                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $agent->phone) }}" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-bold uppercase tracking-wider text-[#102b25] transition hover:bg-[#f5ede0]">Call</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <div class="flex flex-col h-full rounded-[2rem] border border-[#d9cab3] bg-white p-7 shadow-sm">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#edf4ef] text-xl font-black text-[#102b25]">TB</div>
                            <h3 class="mt-5 text-xl font-bold text-[#102b25]">Tadesse Bekele</h3>
                            <p class="mt-2 text-sm text-slate-600 flex-1">Senior Property Consultant</p>
                        </div>
                    </div>

                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <div class="flex flex-col h-full rounded-[2rem] border border-[#d9cab3] bg-[#f9f5ee] p-7 shadow-sm">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#f5ede0] text-xl font-black text-[#9b6c17]">MA</div>
                            <h3 class="mt-5 text-xl font-bold text-[#102b25]">Mekdes Ali</h3>
                            <p class="mt-2 text-sm text-slate-600 flex-1">Masterplan Investment Advisor</p>
                        </div>
                    </div>

                    <div data-carousel-slide class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] shrink-0 snap-start flex flex-col">
                        <div class="flex flex-col h-full rounded-[2rem] border border-[#d9cab3] bg-white p-7 shadow-sm">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#edf4ef] text-xl font-black text-[#102b25]">YG</div>
                            <h3 class="mt-5 text-xl font-bold text-[#102b25]">Yohannes Gebre</h3>
                            <p class="mt-2 text-sm text-slate-600 flex-1">Sales Director</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Carousel Pagination Dots --}}
            <div data-carousel-dots="agents" class="mt-6 flex items-center justify-center gap-2"></div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- VIP REGISTER INTEREST & PRIVATE CONSULTATION SUITE --}}
    {{-- ========================================================================= --}}
    <section id="contact" class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="rounded-[2.5rem] bg-[#102b25] p-8 text-white shadow-2xl lg:p-12 border border-[#d4af37]/30">
            <div class="inline-flex items-center gap-2 rounded-full border border-[#d4af37]/40 bg-[#d4af37]/15 px-4 py-1 text-xs font-bold uppercase tracking-[0.24em] text-[#e7d8b7]">
                {{ \App\Models\SiteSetting::get('contact_badge', 'Contact') }}
            </div>
            <h2 class="mt-4 text-3xl font-black sm:text-4xl tracking-tight">{{ \App\Models\SiteSetting::get('contact_title', 'Let’s find your next address') }}</h2>
            <p class="mt-2 text-[#cbdad0] max-w-2xl text-base">
                {{ \App\Models\SiteSetting::get('contact_description', 'Register your interest to schedule a private tour or discuss prime residential and commercial investment opportunities.') }}
            </p>

            <div class="mt-8 flex flex-wrap gap-6 text-sm text-[#e7d8b7] border-y border-white/10 py-4">
                <span class="flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    Email: {{ $siteBrand['email'] }}
                </span>
                <span class="flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                    Phone: {{ $siteBrand['phone'] }}
                </span>
                <span class="flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                    {{ $siteBrand['address'] }}
                </span>
            </div>

            <div class="mt-8 grid gap-8 lg:grid-cols-[0.85fr_1.15fr] items-start">
                <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur-md">
                    <h3 class="text-xl font-bold text-white">{{ \App\Models\SiteSetting::get('contact_box_title', 'Speak with an agent') }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-[#cbdad0]">
                        {{ \App\Models\SiteSetting::get('contact_box_description', 'Share your property goals and our senior consultants will prepare a curated portfolio matching your requirements, preferred timing, and financing criteria.') }}
                    </p>
                    <div class="mt-6 space-y-4 text-xs text-[#cbdad0]">
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#d4af37]/20 text-[#d4af37]">✓</span>
                            <span>{{ \App\Models\SiteSetting::get('contact_benefit_1', 'Dedicated VIP Property Consultant assigned immediately') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#d4af37]/20 text-[#d4af37]">✓</span>
                            <span>{{ \App\Models\SiteSetting::get('contact_benefit_2', 'On-site or virtual architectural masterplan walkthrough') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#d4af37]/20 text-[#d4af37]">✓</span>
                            <span>{{ \App\Models\SiteSetting::get('contact_benefit_3', 'Full verified title deed transparency guaranteed') }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-6 sm:p-8 text-[#102b25] shadow-xl">
                    @if (session('success'))
                        <div class="mb-5 rounded-2xl border border-[#d9cab3] bg-[#edf4ef] px-4 py-3 text-sm font-semibold text-[#102b25]">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" class="space-y-4">
                        @csrf

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="name" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Your Full Name</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" required placeholder="e.g. Almaz Tadesse" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfbf7] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                            </div>
                            <div>
                                <label for="email" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Email Address</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required placeholder="e.g. almaz@example.com" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfbf7] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="agent_id" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Preferred Advisor</label>
                                <select id="agent_id" name="agent_id" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfbf7] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                                    <option value="">General VIP Inquiry</option>
                                    @foreach ($agents ?? collect() as $agent)
                                        <option value="{{ $agent->id }}" {{ old('agent_id', request('agent')) == $agent->id ? 'selected' : '' }}>{{ $agent->name }} — {{ $agent->bio ?: 'Consultant' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="phone" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Phone Number</label>
                                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="+251 9..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfbf7] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Subject / Property of Interest</label>
                            <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required placeholder="e.g. Inquiring about Emerald Heights 3-Bedroom" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfbf7] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                        </div>

                        <div>
                            <label for="message" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Message / Consultation Details</label>
                            <textarea id="message" name="message" rows="4" required placeholder="Tell us your preferences, timeline, and questions..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfbf7] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#102b25]">{{ old('message') }}</textarea>
                        </div>

                        <button type="submit" class="w-full sm:w-auto rounded-full bg-gradient-to-r from-[#102b25] to-[#1d3c34] px-8 py-3.5 text-sm font-bold uppercase tracking-[0.16em] text-white shadow-lg transition hover:brightness-110 cursor-pointer">
                            {{ \App\Models\SiteSetting::get('contact_submit_text', 'Send Message') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- NEWSLETTER & LIVE SUBSCRIBER INCREMENT --}}
    {{-- ========================================================================= --}}
    <section id="newsletter" class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="rounded-[2.5rem] bg-[#102b25] p-8 text-white shadow-2xl lg:p-12 border border-[#d9cab3]/20">
            <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#d4af37]">{{ \App\Models\SiteSetting::get('newsletter_badge', 'Stay informed') }}</p>
                    <h2 class="mt-2 text-3xl font-black text-white">{{ \App\Models\SiteSetting::get('newsletter_title', 'Newsletter') }}</h2>
                    <p class="mt-2 text-sm text-[#cbdad0] max-w-md">
                        {{ \App\Models\SiteSetting::get('newsletter_description', 'Receive exclusive off-market previews, masterplan phase launches, and quarterly Ethiopian property intelligence.') }}
                    </p>
                </div>

                <div class="w-full max-w-xl">
                    @if (session('newsletter_success'))
                        <div class="mb-3 rounded-xl border border-[#d9cab3]/40 bg-[#1d3c34] px-4 py-3 text-sm font-semibold text-[#f4ecdf]">
                            {{ session('newsletter_success') }}
                        </div>
                    @endif
                    @if ($errors->hasBag('newsletter') && $errors->newsletter->any())
                        <div class="mb-3 rounded-xl border border-red-400/40 bg-[#632323] px-4 py-3 text-sm font-semibold text-red-100">
                            {{ $errors->newsletter->first() }}
                        </div>
                    @endif
                    <div id="newsletter-ajax-feedback" class="hidden mb-3 rounded-xl border px-4 py-3 text-sm font-semibold"></div>

                    <form id="newsletter-form" method="POST" action="{{ route('newsletter.subscribe') }}" class="flex w-full flex-col gap-3 sm:flex-row">
                        @csrf
                        <input id="newsletter-email" type="email" name="email" value="{{ old('email') }}" required placeholder="{{ \App\Models\SiteSetting::get('newsletter_placeholder', 'Enter your email') }}" class="w-full rounded-full border border-[#d9cab3] bg-white px-5 py-3.5 text-sm text-slate-800 outline-none focus:ring-2 focus:ring-[#d4af37]">
                        <button type="submit" id="newsletter-submit-btn" class="rounded-full bg-[#d4af37] px-8 py-3.5 text-sm font-bold uppercase tracking-[0.14em] text-[#102b25] transition hover:bg-[#e7d8b7] cursor-pointer whitespace-nowrap shadow-lg">
                            {{ \App\Models\SiteSetting::get('newsletter_button_text', 'Subscribe') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- SAFETY & DIRECT DEAL WARNING & ADVISORY NOTICE (ABOVE FOOTER) --}}
    {{-- ========================================================================= --}}
    <section id="safety-advisory" class="relative z-20 mx-auto max-w-7xl px-6 pb-14 lg:px-8 scroll-mt-24">
        <div class="rounded-3xl sm:rounded-[2.5rem] border-2 border-amber-300/80 bg-gradient-to-br from-[#fffdf8] via-[#fffbf1] to-[#f9f3e4] p-6 sm:p-8 lg:p-10 shadow-xl shadow-[#102b25]/5 text-slate-800">
            <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr] lg:items-center">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-300 bg-amber-100/90 px-3.5 py-1 text-xs font-black uppercase tracking-[0.2em] text-amber-900 shadow-xs">
                        <svg class="h-4 w-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Buyer Protection & Safety Advisory</span>
                    </div>

                    <h2 class="mt-3 text-2xl sm:text-3xl font-black text-[#102b25] tracking-tight">
                        {{ 'Safety & Direct Deal' }}
                    </h2>

                    <p class="mt-2 text-sm text-slate-600 max-w-2xl leading-relaxed">
                        To protect all clients and buyers, please follow these verified safety guidelines across all property viewings, inquiries, and transactions:
                    </p>

                    <ul class="mt-4 space-y-2.5 text-sm sm:text-base text-slate-800 font-medium">
                        <li class="flex items-start gap-2.5">
                            <span class="text-[#9b6c17] font-black text-lg leading-none shrink-0">•</span>
                            <span>Only inspect properties accompanied by the verified agent.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="text-[#9b6c17] font-black text-lg leading-none shrink-0">•</span>
                            <span>Never send upfront cash or advance before contract signing.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="text-[#9b6c17] font-black text-lg leading-none shrink-0">•</span>
                            <span>Branded agreements are generated automatically on GTM Real Estate.</span>
                        </li>
                        <li class="flex items-start gap-2.5 font-bold text-red-900">
                            <span class="text-red-600 font-black text-lg leading-none shrink-0">•</span>
                            <span>Any client who buys without an agreement given in this webapp, the company does not take responsibility.</span>
                        </li>
                    </ul>
                </div>

                {{-- Prominent Disclaimer Warning Box --}}
                <div class="rounded-2xl sm:rounded-3xl border-2 border-red-300 bg-red-50/95 p-5 sm:p-6 text-red-950 shadow-md">
                    <div class="flex items-start gap-3.5">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-red-100 text-red-700 shadow-xs">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider text-red-900">Important Warning & Disclaimer</h3>
                            <p class="mt-1.5 text-xs sm:text-sm font-bold leading-relaxed text-red-950">
                                Any client who buys without an agreement given in this webapp, the company does not take responsibility.
                            </p>
                            <p class="mt-2 text-xs leading-relaxed text-red-800">
                                Always ensure your transaction is formalized with an official agreement generated directly through the {{ $siteBrand['name'] ?? 'GTM Real Estate' }} web application before any payment or commitment.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- FLOATING VIP CONCIERGE DOCK (Bottom-Right Luxury Pill) --}}
    {{-- ========================================================================= --}}
    <div class="fixed bottom-6 right-6 z-40 flex items-center gap-2 rounded-full border border-[#d4af37]/40 bg-[#102b25]/95 p-1.5 shadow-2xl backdrop-blur-lg">
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteBrand['phone']) }}?text=Hello%20I%20am%20interested%20in%20your%20luxury%20properties" target="_blank" rel="noopener" class="flex h-11 w-11 items-center justify-center rounded-full bg-[#25D366] text-white shadow-md transition hover:scale-105" title="Chat on WhatsApp">
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
            </svg>
        </a>
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $siteBrand['phone']) }}" class="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20" title="Direct Phone Call">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
        </a>
        <a href="{{ url('/app#contact') }}" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-[#d4af37] to-[#c5a059] px-4 py-2 text-xs font-bold uppercase tracking-wider text-[#102b25] transition hover:brightness-110">
            <span>{{ \App\Models\SiteSetting::get('concierge_button_text', 'VIP Tour') }}</span>
        </a>
    </div>

    {{-- Newsletter Live AJAX Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('newsletter-form');
            if (!form) return;

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const emailInput = document.getElementById('newsletter-email');
                const submitBtn = document.getElementById('newsletter-submit-btn');
                const feedbackEl = document.getElementById('newsletter-ajax-feedback');
                const happyBuyersCountEl = document.getElementById('happy-buyers-count');

                const email = emailInput ? emailInput.value.trim() : '';
                if (!email) return;

                const tokenEl = form.querySelector('input[name="_token"]');
                const token = tokenEl ? tokenEl.value : '';

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                }

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({ email: email })
                })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) {
                        const errorMsg = data.errors?.email?.[0] || data.message || 'Something went wrong. Please check your email.';
                        throw new Error(errorMsg);
                    }
                    return data;
                })
                .then((data) => {
                    if (feedbackEl) {
                        feedbackEl.textContent = data.message;
                        feedbackEl.className = 'mb-3 rounded-xl border border-[#d9cab3]/40 bg-[#1d3c34] px-4 py-3 text-sm font-semibold text-[#f4ecdf] block';
                    }
                    if (happyBuyersCountEl && typeof data.count !== 'undefined') {
                        happyBuyersCountEl.textContent = Number(data.count).toLocaleString();
                    }
                    if (emailInput && data.is_new) {
                        emailInput.value = '';
                    }
                })
                .catch((err) => {
                    if (feedbackEl) {
                        feedbackEl.textContent = err.message || 'Could not complete subscription. Please try again.';
                        feedbackEl.className = 'mb-3 rounded-xl border border-red-400/40 bg-[#632323] px-4 py-3 text-sm font-semibold text-red-100 block';
                    }
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                    }
                });
            });
        });
    </script>
@endsection
