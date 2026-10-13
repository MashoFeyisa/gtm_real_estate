@extends('app')

@section('title', 'Property Comparison — GTM Real Estate')
@section('description', 'Side-by-side comparison of Ethiopian properties, amenities, pricing per square meter, and physical specifications.')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        {{-- Header & Controls --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between border-b border-[#d9cab3] pb-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-[#102b25] px-3 py-1 text-xs font-bold text-[#d4af37]">Decision Intelligence</span>
                    <span class="text-xs font-semibold text-slate-500">Side-by-Side Analysis</span>
                </div>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-[#1d3c34] sm:text-4xl">Compare Properties</h1>
                <p class="mt-1 text-sm text-slate-600">Review specifications, price per square meter, utility infrastructure, and verified scores across up to 4 properties.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if (isset($availableToAdd) && $availableToAdd->isNotEmpty() && $properties->count() < 4)
                    <form method="POST" action="" id="quick-add-compare-form" data-auth-gate class="inline-flex items-center gap-1.5">
                        @csrf
                        <select id="quick-add-compare-select" class="rounded-full border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-semibold text-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#102b25]">
                            <option value="">+ Add property to compare...</option>
                            @foreach ($availableToAdd as $addProp)
                                <option value="{{ route('properties.compare.toggle', $addProp) }}">
                                    {{ Str::limit($addProp->title, 26) }} — ETB {{ number_format($addProp->price) }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" onclick="const sel = document.getElementById('quick-add-compare-select'); if(sel.value){ const f = document.getElementById('quick-add-compare-form'); f.action = sel.value; f.submit(); }" class="rounded-full bg-[#102b25] px-3.5 py-2 text-xs font-bold text-[#d4af37] transition hover:bg-[#1a443a] cursor-pointer">
                            Add
                        </button>
                    </form>
                @endif
                <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-1.5 rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-bold text-[#1d3c34] shadow-xs transition hover:bg-[#f6f1e8]">
                    <span>← Browse Marketplace</span>
                </a>
                @if ($properties->isNotEmpty() && ! ($isSample ?? false))
                    <form method="POST" action="{{ route('properties.compare.clear') }}" data-auth-gate>
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100 cursor-pointer">
                            <span>Clear All ({{ $properties->count() }})</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if ($isSample ?? false)
            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#d4af37]/60 bg-[#fef8e8] px-5 py-3.5 text-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#d4af37] text-[#102b25] font-black text-xs">★</span>
                    <div>
                        <p class="font-bold text-[#102b25]">Featured Comparison Preview</p>
                        <p class="text-xs text-slate-600">Showing 2 prime residences side-by-side. You can add or replace listings using the dropdown above or from any listing card in the marketplace.</p>
                    </div>
                </div>
                <a href="{{ route('properties.index') }}" class="rounded-full bg-[#102b25] px-4 py-2 text-xs font-bold text-[#d4af37] transition hover:bg-[#1a443a]">
                    Browse All Properties &rarr;
                </a>
            </div>
        @endif

        @if ($properties->isEmpty())
            {{-- Empty State --}}
            <div class="mt-12 rounded-[2.5rem] border border-dashed border-[#d9cab3] bg-[#fffaf2] p-12 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#102b25]/10 text-2xl text-[#102b25]">
                    ⚖️
                </div>
                <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">No properties selected for comparison</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-600">
                    Browse our luxury villas, modern apartments, and commercial listings, and click the <strong class="text-[#102b25]">"Compare"</strong> button on any listing card to evaluate them side-by-side.
                </p>
                <div class="mt-6">
                    <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-2 rounded-full bg-[#102b25] px-6 py-3 text-sm font-bold text-[#d4af37] shadow-md transition hover:bg-[#1a443a]">
                        Explore Marketplace Listings
                    </a>
                </div>
            </div>
        @else
            {{-- Comparison Matrix Table --}}
            <div class="mt-8 overflow-x-auto rounded-[2rem] border border-[#d9cab3] bg-white shadow-sm">
                <table class="w-full min-w-[760px] border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-[#ecd9be] bg-[#fffaf2]">
                            <th class="w-1/5 p-4 text-xs font-bold uppercase tracking-wider text-[#587165]">Attribute</th>
                            @foreach ($properties as $property)
                                <th class="p-4 align-top">
                                    <div class="relative group">
                                        <div class="relative h-44 w-full overflow-hidden rounded-2xl border border-[#d9cab3] bg-slate-100 shadow-inner">
                                            <img src="{{ $property->primary_image_url ?: asset('images/luxury/central-plaza.jpg') }}" alt="{{ $property->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" onerror="this.onerror=null; this.src='{{ asset('images/luxury/central-plaza.jpg') }}';">
                                            <span class="absolute top-2 left-2 rounded-full bg-[#102b25]/90 px-2.5 py-1 text-[11px] font-bold text-[#d4af37] backdrop-blur-xs">
                                                {{ ucfirst($property->type) }}
                                            </span>
                                            <form method="POST" action="{{ route('properties.compare.toggle', $property) }}" data-auth-gate class="absolute top-2 right-2">
                                                @csrf
                                                <button type="submit" title="Remove from comparison" class="flex h-7 w-7 items-center justify-center rounded-full bg-black/60 text-white transition hover:bg-rose-600 cursor-pointer">
                                                    ✕
                                                </button>
                                            </form>
                                        </div>
                                        <h4 class="mt-2.5 text-base font-bold text-[#1d3c34] line-clamp-1">
                                            <a href="{{ route('properties.show', $property->slug) }}" class="hover:underline">
                                                {{ $property->title }}
                                            </a>
                                        </h4>
                                        <p class="text-xs text-slate-500 font-mono">{{ $property->property_code }}</p>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        {{-- Price & Price / m² --}}
                        <tr class="bg-amber-50/40">
                            <td class="p-4 font-bold text-[#1d3c34]">Listed Price</td>
                            @foreach ($properties as $property)
                                <td class="p-4">
                                    <div class="text-lg font-black text-[#1d3c34]" data-base-price="{{ $property->price }}" data-price-suffix="{{ $property->type === 'rent' ? '/mo' : ($property->type === 'stay' ? '/night' : '') }}">
                                        ETB {{ number_format($property->price) }} {{ $property->type === 'rent' ? '/mo' : ($property->type === 'stay' ? '/night' : '') }}
                                    </div>
                                    @if ($property->price_per_sqm)
                                        <span class="inline-block mt-0.5 rounded-md bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-900">
                                            ETB {{ number_format($property->price_per_sqm) }} / m²
                                        </span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>

                        {{-- Category & Type --}}
                        <tr>
                            <td class="p-4 font-bold text-slate-700">Category & Type</td>
                            @foreach ($properties as $property)
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#102b25]/10 px-3 py-1 text-xs font-bold text-[#102b25]">
                                        {{ ucfirst(str_replace('_', ' ', $property->property_category ?? 'Residential')) }}
                                    </span>
                                </td>
                            @endforeach
                        </tr>

                        {{-- Location Hierarchy --}}
                        <tr class="bg-slate-50/50">
                            <td class="p-4 font-bold text-slate-700">Location</td>
                            @foreach ($properties as $property)
                                <td class="p-4 text-xs">
                                    <div class="font-bold text-[#1d3c34]">{{ $property->city ?? 'Addis Ababa' }}</div>
                                    @if ($property->subcity)
                                        <div class="text-slate-600">{{ $property->subcity }} {{ $property->woreda ? '• '.$property->woreda : '' }}</div>
                                    @endif
                                    @if ($property->neighborhood)
                                        <div class="text-slate-500 font-medium">{{ $property->neighborhood }}</div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>

                        {{-- Area & Spaces --}}
                        <tr>
                            <td class="p-4 font-bold text-slate-700">Dimensions & Rooms</td>
                            @foreach ($properties as $property)
                                <td class="p-4 text-xs space-y-1">
                                    <div><strong class="font-bold text-slate-900">{{ $property->bedrooms }}</strong> Bedrooms • <strong class="font-bold text-slate-900">{{ $property->bathrooms }}</strong> Bathrooms</div>
                                    <div>Lot Area: <span class="font-semibold">{{ $property->area }} sq ft</span></div>
                                    @if ($property->building_area)
                                        <div>Building: <span class="font-semibold text-emerald-800">{{ $property->building_area }} m²</span></div>
                                    @endif
                                    <div>Floors: <span class="font-semibold">{{ $property->floors ?: 1 }}</span></div>
                                </td>
                            @endforeach
                        </tr>

                        {{-- Construction & Year --}}
                        <tr class="bg-slate-50/50">
                            <td class="p-4 font-bold text-slate-700">Condition & Year</td>
                            @foreach ($properties as $property)
                                <td class="p-4 text-xs">
                                    <div class="font-bold uppercase tracking-wider text-slate-800">{{ $property->condition ?: 'Excellent' }}</div>
                                    @if ($property->year_built)
                                        <div class="text-slate-500">Built in {{ $property->year_built }}</div>
                                    @endif
                                    <div class="text-slate-600">Parking: {{ $property->parking_spaces ?: 1 }} spaces</div>
                                </td>
                            @endforeach
                        </tr>

                        {{-- Infrastructure Utilities --}}
                        <tr>
                            <td class="p-4 font-bold text-slate-700">Utilities & Security</td>
                            @foreach ($properties as $property)
                                <td class="p-4 text-xs">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="rounded px-2 py-0.5 text-[11px] font-bold {{ $property->has_electricity ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-400 line-through' }}">⚡ Grid Power</span>
                                        <span class="rounded px-2 py-0.5 text-[11px] font-bold {{ $property->has_water ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-400 line-through' }}">💧 Piped Water</span>
                                        <span class="rounded px-2 py-0.5 text-[11px] font-bold {{ $property->has_generator ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-400 line-through' }}">🔋 Generator</span>
                                        <span class="rounded px-2 py-0.5 text-[11px] font-bold {{ $property->has_water_tank ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-400 line-through' }}">🚰 Water Tank</span>
                                        <span class="rounded px-2 py-0.5 text-[11px] font-bold {{ $property->has_solar ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-400 line-through' }}">☀️ Solar Power</span>
                                        <span class="rounded px-2 py-0.5 text-[11px] font-bold {{ $property->has_security ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-400 line-through' }}">🛡️ 24/7 Guard</span>
                                    </div>
                                </td>
                            @endforeach
                        </tr>



                        {{-- Quality Completeness Score --}}
                        <tr>
                            <td class="p-4 font-bold text-slate-700">Listing Completeness</td>
                            @foreach ($properties as $property)
                                @php
                                    $quality = $property->quality_score;
                                @endphp
                                <td class="p-4 text-xs">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-20 overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-full bg-emerald-600 rounded-full" style="width: {{ $quality['score'] }}%"></div>
                                        </div>
                                        <span class="font-bold text-emerald-800">{{ $quality['badge'] }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-500 font-medium">Quality Index</span>
                                </td>
                            @endforeach
                        </tr>

                        {{-- Investment Yield Metrics --}}
                        <tr class="bg-amber-50/30">
                            <td class="p-4 font-bold text-slate-700">Yield & ROI Outlook</td>
                            @foreach ($properties as $property)
                                @php
                                    $metrics = $property->investment_metrics;
                                @endphp
                                <td class="p-4 text-xs space-y-1">
                                    <div>Gross Yield: <strong class="font-bold text-emerald-800">{{ $metrics['gross_yield_percentage'] }}%</strong></div>
                                    <div>Net Capitalization: <strong class="font-bold text-[#1d3c34]">{{ $metrics['net_yield_percentage'] }}%</strong></div>
                                    <div class="text-[11px] text-slate-500">Est. Net Cash Flow: ETB {{ number_format($metrics['net_operating_income']) }}/yr</div>
                                </td>
                            @endforeach
                        </tr>

                        {{-- Agent & Actions --}}
                        <tr class="bg-white">
                            <td class="p-4 font-bold text-[#1d3c34]">Action</td>
                            @foreach ($properties as $property)
                                <td class="p-4 align-middle">
                                    <div class="flex flex-col gap-2">
                                        <a href="{{ route('properties.show', $property->slug) }}" class="w-full text-center rounded-xl bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-[#275045]">
                                            View Full Listing
                                        </a>
                                        @if ($property->type === 'stay')
                                            <a href="{{ route('properties.show', $property->slug) }}#stay-reservation-form" class="w-full text-center rounded-xl bg-[#d4af37] px-4 py-2 text-xs font-bold text-[#102b25] transition hover:bg-[#c49f27]">
                                                Book Stay
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
