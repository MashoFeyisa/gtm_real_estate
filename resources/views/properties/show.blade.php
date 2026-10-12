@extends('app')

@section('title', $property->title . ' — ' . ($property->city ?? 'Addis Ababa') . ' | GTM Real Estate')
@section('description', Str::limit(strip_tags($property->description ?: ($property->title . ' for ' . ($property->type === 'rent' ? 'rent' : 'sale') . ' in ' . ($property->city ?? 'Addis Ababa'))), 160))
@section('og_title', $property->title . ' | Luxury Real Estate')
@section('og_description', Str::limit(strip_tags($property->description ?: $property->title), 160))
@section('og_image', $property->primary_image_url)
@section('og_type', 'article')

@push('json_ld')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'RealEstateListing',
        'name' => $property->title,
        'description' => Str::limit(strip_tags($property->description ?: $property->title), 250),
        'url' => route('properties.show', $property->slug),
        'datePosted' => $property->created_at?->toIso8601String() ?? now()->toIso8601String(),
        'image' => $property->gallery_images,
        'offers' => [
            '@type' => 'Offer',
            'price' => (string) $property->price,
            'priceCurrency' => 'ETB',
            'availability' => 'https://schema.org/InStock',
            'businessFunction' => $property->type === 'rent' ? 'http://purl.org/goodrelations/v1#LeaseOut' : 'http://purl.org/goodrelations/v1#Sell',
        ],
        'mainEntity' => [
            '@type' => $property->property_category === 'apartment' ? 'Apartment' : 'SingleFamilyResidence',
            'name' => $property->title,
            'numberOfRooms' => max(1, (int) $property->bedrooms),
            'numberOfBedrooms' => max(1, (int) $property->bedrooms),
            'numberOfBathroomsTotal' => max(1, (int) $property->bathrooms),
            'floorSize' => [
                '@type' => 'QuantitativeValue',
                'value' => (float) ($property->area ?: 0),
                'unitCode' => 'MTK',
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $property->city ?? 'Addis Ababa',
                'streetAddress' => $property->address ?? 'Prime Location',
                'addressCountry' => 'ET',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $property->coordinates[0],
                'longitude' => $property->coordinates[1],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endpush

@section('content')
    @php
        $rawPhone = $property->agent?->phone ?? '';
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '251' . substr($cleanPhone, 1);
        }
        $telPhone = preg_replace('/[^0-9+]/', '', $rawPhone);
        $agentListingsCount = $property->agent ? $property->agent->properties()->where('is_active', true)->count() : 0;
    @endphp

    <section class="mx-auto max-w-6xl px-6 py-10 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-2 rounded-full border border-[#d9cab3] bg-[#fffaf2] px-4 py-2 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f1e6d2]">
                &larr; Back to properties
            </a>
            <div class="flex flex-wrap items-center gap-3">
                @include('properties.partials.interaction-buttons', ['property' => $property, 'size' => 'md'])
                @php
                    $comparedIds = session('compared_property_ids', []);
                    $isCompared = in_array($property->id, $comparedIds, true);
                    $comparedCount = count($comparedIds);
                @endphp
                <form method="POST" action="{{ route('properties.compare.toggle', $property) }}" class="inline compare-toggle-form" data-property-id="{{ $property->id }}">
                    @csrf
                    <button type="submit" class="compare-toggle-btn compare-detail-btn inline-flex items-center gap-1.5 rounded-full border px-4 py-2 text-xs font-bold transition cursor-pointer shadow-xs {{ $isCompared ? 'bg-[#d4af37] border-[#d4af37] text-[#102b25] font-black' : 'border-[#102b25] bg-[#102b25] text-white hover:bg-[#1d3c34] hover:border-[#1d3c34] hover:text-[#d4af37]' }}" title="{{ $isCompared ? 'Remove from comparison matrix' : 'Compare side by side in matrix' }}" data-compared="{{ $isCompared ? '1' : '0' }}">
                        <span class="compare-icon-wrap flex items-center justify-center">
                            @if ($isCompared)
                                <svg class="h-4 w-4 text-[#102b25]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @else
                                <svg class="h-4 w-4 text-[#d4af37]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            @endif
                        </span>
                        <span class="compare-btn-text">{{ $isCompared ? '✓ In Matrix' : 'Compare' }}</span>
                    </button>
                </form>
                <a href="{{ route('properties.compare') }}" id="compare-matrix-nav-btn" class="inline-flex items-center gap-1.5 rounded-full border border-[#102b25] bg-[#102b25] px-3.5 py-2 text-xs font-bold text-[#d4af37] hover:bg-[#1d3c34] transition cursor-pointer shadow-xs {{ $comparedCount > 0 ? '' : 'hidden' }}">
                    <span class="compare-matrix-counter">Compare Matrix ({{ $comparedCount }})</span>
                </a>
                <button type="button" data-auth-gate onclick="document.getElementById('report-property-modal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 transition cursor-pointer">
                    <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Report</span>
                </button>
                <button type="button" data-auth-gate title="{{ auth()->check() ? 'Save this property into a collection' : 'Create an account to save to a collection' }}" onclick="document.getElementById('save-to-collection-modal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 rounded-full border border-amber-300 bg-amber-50 px-3.5 py-2 text-xs font-bold text-amber-900 hover:bg-amber-100 transition cursor-pointer">
                    <svg class="h-4 w-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    <span>Collection</span>
                </button>
                @if ($property->agent)
                    <a href="{{ route('agents.show', $property->agent) }}" class="inline-flex items-center gap-2 text-sm font-bold text-[#1d3c34] hover:text-[#2d5d4d]">
                        <span>View all listings by {{ $property->agent->name }}</span>
                        <span class="rounded-full bg-[#1d3c34] px-2 py-0.5 text-xs text-white">{{ $agentListingsCount }}</span>
                        &rarr;
                    </a>
                @endif
            </div>
        </div>


        @if (session('stay_booking_success'))
            @php $stayBookingFlash = session('stay_booking_success'); @endphp
            <div class="mt-6 rounded-2xl border-2 border-amber-400 bg-[#fef9ec] p-6 text-[#102b25] shadow-md animate-in fade-in duration-300">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3.5">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-500 text-white shadow-sm">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-amber-800">Reservation Held · Payment Required</span>
                            <h3 class="text-xl font-black text-[#102b25]">Stay Reserved Successfully!</h3>
                            <p class="text-xs text-slate-600">Booking Reference: <strong class="font-mono text-xs font-black text-[#1d3c34] bg-white px-2 py-0.5 rounded border border-amber-200">{{ $stayBookingFlash['reference'] }}</strong> · Guest: {{ $stayBookingFlash['guest'] }}</p>
                        </div>
                    </div>
                    <div class="text-left text-xs sm:border-l sm:border-amber-300/60 sm:pl-5 sm:text-right">
                        <span class="block font-semibold text-slate-500">{{ $stayBookingFlash['check_in'] }} &rarr; {{ $stayBookingFlash['check_out'] }}</span>
                        <span class="font-black text-base text-[#102b25]">{{ $stayBookingFlash['total'] }} ({{ $stayBookingFlash['nights'] }} Nights)</span>
                        @isset($stayBookingFlash['id'])
                            <button type="button" onclick="openUniversalPaymentModal({ type: 'stay_booking', formAction: '{{ route('bookings.pay', $stayBookingFlash['id']) }}', amount: {{ (float) ($stayBookingFlash['amount'] ?? 0) }}, lockAmount: true, title: 'Pay for Stay Reservation', purpose: 'Short-Stay Accommodation — {{ $stayBookingFlash['reference'] }}', submitLabel: 'Confirm & Pay' })" class="mt-2 inline-flex cursor-pointer items-center gap-1.5 rounded-xl bg-[#102b25] px-5 py-2.5 text-xs font-black text-[#d4af37] shadow-xs transition hover:bg-black">
                                💳 Pay Now
                            </button>
                        @endisset
                    </div>
                </div>
            </div>
        @endif

        @if (session('viewing_scheduled_success'))
            <div class="mt-6 rounded-2xl border-2 border-[#102b25] bg-[#f2f7f4] p-6 text-[#102b25] shadow-md animate-in fade-in duration-300">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#102b25] text-[#d4af37] shadow-sm">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-emerald-800">Viewing Requested</span>
                            <h3 class="text-xl font-black text-[#102b25]">Appointment Scheduled!</h3>
                            <p class="text-xs text-slate-600">Booking Ref: <strong class="font-mono text-xs font-black text-[#102b25] bg-white px-2 py-0.5 rounded border border-[#d9cab3]">{{ session('viewing_scheduled_success')['reference'] }}</strong> · {{ session('viewing_scheduled_success')['buyer_name'] }}</p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right text-xs sm:border-l sm:border-[#d9cab3] sm:pl-5">
                        <span class="block text-slate-600 font-semibold">{{ session('viewing_scheduled_success')['date'] }}</span>
                        <span class="font-black text-sm text-[#102b25]">{{ session('viewing_scheduled_success')['time'] }} ({{ session('viewing_scheduled_success')['type'] }})</span>
                    </div>
                </div>
            </div>
        @endif

        @if (session('offer_submitted_success'))
            <div class="mt-6 rounded-2xl border-2 border-[#d4af37] bg-[#fbf8f0] p-6 text-[#102b25] shadow-md animate-in fade-in duration-300">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-r from-[#9b6c17] to-[#d4af37] text-white shadow-sm">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <span class="rounded-full bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#d4af37]">Formal Offer Submitted</span>
                            <h3 class="text-xl font-black text-[#102b25]">Offer Registered Successfully!</h3>
                            <p class="text-xs text-slate-600">Offer Ref: <strong class="font-mono text-xs font-black text-[#102b25] bg-white px-2 py-0.5 rounded border border-[#d9cab3]">{{ session('offer_submitted_success')['reference'] }}</strong> · {{ session('offer_submitted_success')['buyer_name'] }}</p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right text-xs sm:border-l sm:border-[#d9cab3] sm:pl-5">
                        <span class="block text-slate-500 font-semibold">Offer Amount</span>
                        <span class="font-black text-base text-[#102b25]">{{ session('offer_submitted_success')['offered_price'] }}</span>
                        <span class="block text-[11px] font-bold text-[#9b6c17]">({{ session('offer_submitted_success')['financing_type'] }})</span>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-6 overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white shadow-[0_30px_80px_rgba(29,60,52,0.14)]">
            <div class="grid gap-0 md:grid-cols-[1.15fr_0.85fr]">
                @php
                    $showGallery = $property->gallery_images;
                @endphp
                <div class="relative bg-[#0c1f1a] flex flex-col justify-between overflow-hidden">
                    <div data-property-slider class="group/show-slider relative h-full min-h-[380px] w-full select-none">
                        @forelse ($showGallery as $idx => $imgUrl)
                            <div data-property-slide class="absolute inset-0 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] {{ $idx === 0 ? 'opacity-100 scale-100 z-10' : 'opacity-0 scale-105 pointer-events-none z-0' }}">
                                <img src="{{ $imgUrl }}" alt="{{ $property->title }} - photo {{ $idx + 1 }}" class="h-full w-full object-cover">
                            </div>
                        @empty
                            <div class="relative h-full min-h-[380px] w-full">
                                <img src="{{ asset($property->default_background_image) }}" alt="{{ $property->title }}" class="h-full w-full object-cover">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center text-3xl font-black text-[#e7d8b7]">
                                    {{ Str::limit($property->title, 20) }}
                                </div>
                            </div>
                        @endforelse

                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent pointer-events-none"></div>

                        @if (count($showGallery) > 1)
                            <div class="absolute inset-x-3 top-1/2 -translate-y-1/2 flex items-center justify-between pointer-events-none z-20">
                                <button type="button" data-slider-prev aria-label="Previous photo" class="pointer-events-auto flex h-11 w-11 items-center justify-center rounded-full bg-black/75 text-white backdrop-blur-md opacity-85 group-hover/show-slider:opacity-100 transition hover:scale-110 hover:bg-[#102b25] hover:text-[#d4af37] hover:border-[#d4af37] border-2 border-white/30 active:scale-95 cursor-pointer shadow-xl">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                </button>
                                <button type="button" data-slider-next aria-label="Next photo" class="pointer-events-auto flex h-11 w-11 items-center justify-center rounded-full bg-black/75 text-white backdrop-blur-md opacity-85 group-hover/show-slider:opacity-100 transition hover:scale-110 hover:bg-[#102b25] hover:text-[#d4af37] hover:border-[#d4af37] border-2 border-white/30 active:scale-95 cursor-pointer shadow-xl">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                </button>
                            </div>
                        @endif

                        <div class="absolute left-4 top-4 z-20 flex flex-wrap gap-2">
                            <span class="rounded-full bg-[#1d3c34] px-3 py-1 text-xs font-black uppercase tracking-[0.18em] text-[#f6efe4]">
                                {{ $property->type === 'rent' ? 'For Rent' : 'For Sale' }}
                            </span>
                            @if ($property->featured)
                                <span class="rounded-full bg-[#f1e4cf] px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-[#1d3c34]">
                                    Featured
                                </span>
                            @endif
                            <span class="rounded-full bg-white/90 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-[#2d5d4d] backdrop-blur-sm">
                                Negotiable
                            </span>
                        </div>

                        @if (count($showGallery) > 1)
                            <div class="absolute bottom-4 right-4 z-20 flex items-center gap-1.5">
                                <span data-slider-counter class="rounded-full bg-black/75 px-3 py-1 text-xs font-bold text-[#e7d8b7] backdrop-blur-xs border border-white/20">
                                    1 / {{ count($showGallery) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    @if (count($showGallery) > 1)
                        <div class="flex gap-2 p-3 bg-black/50 overflow-x-auto border-t border-white/10 [scrollbar-width:none]">
                            @foreach ($showGallery as $idx => $thumbUrl)
                                <button type="button" data-slider-dot class="relative h-12 w-16 shrink-0 rounded-lg overflow-hidden border {{ $idx === 0 ? 'border-[#d4af37] ring-1 ring-[#d4af37]' : 'border-white/20 opacity-70 hover:opacity-100' }} transition cursor-pointer">
                                    <img src="{{ $thumbUrl }}" alt="Thumb {{ $idx + 1 }}" class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex flex-col justify-between p-6 md:p-8 lg:p-10">
                    <div>
                        <div class="flex items-center justify-between gap-3 text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">
                            <span>{{ ucwords(str_replace('_', ' ', (string) ($property->property_category ?? $property->category ?? 'Property'))) }}</span>
                            @if ($property->property_code)
                                <span class="rounded-full bg-[#102b25]/10 px-3 py-1 text-[11px] font-mono font-bold text-[#102b25] border border-[#102b25]/20">
                                    ID: {{ $property->property_code }}
                                </span>
                            @else
                                <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-[#2d5d4d]">Active Listing</span>
                            @endif
                        </div>

                        <h1 class="mt-4 text-3xl font-black tracking-tight text-[#1d3c34] sm:text-4xl">{{ $property->title }}</h1>
                        <p class="mt-2 flex items-center gap-1.5 text-base text-slate-600">
                            <svg class="h-4 w-4 text-[#587165]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $property->city ?? 'Addis Ababa' }} · {{ $property->address ?? 'Prime Location' }}</span>
                        </p>

                        <div class="mt-5 rounded-2xl bg-[#fffaf2] p-4 border border-[#ecd9be]">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#587165]">Price</p>
                                @if ($property->listings && $property->listings->count() > 1)
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#8b6508] bg-[#fdf5e2] px-2 py-0.5 rounded-md border border-[#ecd9be]">
                                        {{ $property->listings->count() }} Listing Modes Active
                                    </span>
                                @endif
                            </div>
                            <div class="mt-1 flex flex-wrap items-baseline gap-2">
                                <span class="text-3xl font-black text-[#1d3c34] sm:text-4xl currency-price" data-base-price="{{ $property->price }}">ETB {{ number_format($property->price) }}</span>
                                @if ($property->type === 'rent')
                                    <span class="text-sm font-bold text-[#587165]">/ month</span>
                                @endif
                            </div>

                            <div class="mt-2.5 flex flex-wrap items-center gap-2 pt-2 border-t border-[#ecd9be]/60">
                                @if ($property->price_per_sqm > 0)
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-white px-2.5 py-1 text-xs font-bold text-[#1d3c34] border border-[#d9cab3]">
                                        <svg class="h-3.5 w-3.5 text-[#587165]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                                        <span>{{ number_format($property->price_per_sqm) }} ETB / m²</span>
                                    </span>
                                @endif
                                @php $qScore = $property->quality_score; @endphp
                                <span class="inline-flex items-center gap-1 rounded-lg {{ ($qScore['score'] ?? 0) >= 80 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }} px-2.5 py-1 text-xs font-bold">
                                    <span>⚡ Completeness: {{ $qScore['badge'] ?? '80%' }}</span>
                                </span>
                                @if ($property->reviews_count > 0)
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-900 border border-amber-200">
                                        <span>★ {{ number_format($property->average_rating, 1) }} ({{ $property->reviews_count }} reviews)</span>
                                    </span>
                                @endif
                            </div>

                            @if ($property->listings && $property->listings->count() > 1)
                                <div class="mt-3 pt-3 border-t border-[#ecd9be] flex flex-wrap gap-1.5">
                                    @foreach ($property->listings as $lst)
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-white px-2 py-1 text-[11px] font-bold text-[#1d3c34] border border-[#d9cab3]">
                                            <span class="rounded bg-[#102b25] px-1.5 py-0.2 text-[9px] uppercase font-black text-[#d4af37]">{{ $lst->listing_type }}</span>
                                            <span class="currency-price" data-base-price="{{ $lst->price }}" data-price-suffix="{{ $lst->price_suffix }}">{{ $lst->formatted_price }}{{ $lst->price_suffix }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="mt-5 grid grid-cols-3 gap-3">
                            <div class="rounded-2xl bg-[#f8f3eb] p-3.5 text-center">
                                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#60716c]">Bedrooms</p>
                                <p class="mt-1 text-2xl font-black text-[#1d3c34]">{{ $property->bedrooms }}</p>
                            </div>
                            <div class="rounded-2xl bg-[#edf2ee] p-3.5 text-center">
                                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#60716c]">Bathrooms</p>
                                <p class="mt-1 text-2xl font-black text-[#1d3c34]">{{ $property->bathrooms }}</p>
                            </div>
                            <div class="rounded-2xl bg-[#f3ecdb] p-3.5 text-center">
                                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#60716c]" data-i18n="Area">Area</p>
                                <p class="mt-1 text-2xl font-black text-[#1d3c34]">{{ $property->area }} <span class="text-sm font-semibold">sqm</span></p>
                            </div>

                        </div>

                        {{-- Jiji-style Agent Contact Buttons --}}
                        <div class="mt-6 flex flex-wrap gap-2.5">
                            @if ($property->agent && $telPhone)
                                <a href="tel:{{ $telPhone }}" class="inline-flex flex-1 min-w-[140px] items-center justify-center gap-2 rounded-xl bg-[#00b53f] px-4 py-3 text-sm font-bold text-white shadow-md shadow-[#00b53f]/20 transition hover:bg-[#009b36]">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6.54 5C6.6 5.89 6.75 6.76 6.99 7.59L5.79 8.79C5.38 7.59 5.12 6.32 5.03 5H6.54ZM16.4 17.02C17.25 17.26 18.12 17.41 19 17.47V18.96C17.68 18.87 16.41 18.61 15.2 18.21L16.4 17.02ZM7.5 3H4C3.45 3 3 3.45 3 4C3 13.39 10.61 21 20 21C20.55 21 21 20.55 21 20V16.51C21 15.96 20.55 15.51 20 15.51C18.76 15.51 17.55 15.31 16.43 14.94C16.33 14.9 16.22 14.89 16.12 14.89C15.86 14.89 15.61 14.99 15.41 15.18L13.21 17.38C10.38 15.93 8.06 13.62 6.62 10.79L8.82 8.59C9.1 8.31 9.18 7.92 9.07 7.57C8.7 6.45 8.5 5.25 8.5 4C8.5 3.45 8.05 3 7.5 3Z"/></svg>
                                    <span>Call Agent</span>
                                </a>
                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Hello ' . $property->agent->name . ', I am interested in your property: ' . $property->title . ' (' . url()->current() . ')') }}" target="_blank" rel="noopener noreferrer" class="inline-flex flex-1 min-w-[140px] items-center justify-center gap-2 rounded-xl bg-[#25D366] px-4 py-3 text-sm font-bold text-white shadow-md shadow-[#25D366]/20 transition hover:bg-[#20ba59]">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.588-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.072-1.047-.042-.313-.102-.7-.234-1.206-.453-2.138-.925-3.528-3.08-3.635-3.223-.106-.143-.87-1.157-.87-2.207 0-1.05.549-1.567.744-1.78.196-.214.428-.268.572-.268.143 0 .287.002.412.008.132.006.309-.05.483.369.179.431.613 1.493.666 1.602.053.109.089.237.017.38-.071.144-.107.233-.214.358-.106.126-.224.281-.32.376-.107.106-.219.222-.094.436.125.214.557.918 1.196 1.488.823.733 1.517.96 1.731 1.067.214.107.339.089.464-.054.126-.143.536-.624.679-.838.143-.214.286-.179.482-.107.197.072 1.25.59 1.464.697.214.107.357.161.41.25.054.089.054.517-.09 1.031z"/></svg>
                                    <span>WhatsApp</span>
                                </a>
                            @endif
                            <a href="#schedule-viewing-section" data-auth-gate class="inline-flex flex-1 min-w-[140px] items-center justify-center gap-1.5 rounded-xl bg-[#102b25] px-4 py-3 text-sm font-black text-[#d4af37] shadow-md transition hover:bg-[#1d3c34]">
                                <span>📅 Schedule Viewing</span>
                            </a>
                            <a href="#formal-offer-section" data-auth-gate class="inline-flex flex-1 min-w-[140px] items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-[#9b6c17] to-[#d4af37] px-4 py-3 text-sm font-black text-white shadow-md transition hover:opacity-95">
                                <span>💼 Make Formal Offer</span>
                            </a>
                            <button type="button" onclick="openUniversalPaymentModal({ propertyId: {{ $property->id }}, title: 'Pay Property Deposit / Earnest Money', purpose: 'Earnest Money Deposit for {{ addslashes($property->title) }}', amount: {{ round($property->price * 0.05, -3) ?: 50000 }} })" class="inline-flex flex-1 min-w-[150px] items-center justify-center gap-2 rounded-xl border-2 border-[#102b25] bg-white px-4 py-3 text-sm font-black text-[#102b25] shadow-lg transition hover:bg-[#102b25] hover:text-white cursor-pointer ring-2 ring-emerald-600/30">
                                <span class="text-base">💳</span>
                                <span class="font-black text-[#102b25] uppercase tracking-wide">Pay Deposit (ETB)</span>
                            </button>
                            @if ($property->isShortStay())
                                <a href="#stay-reservation-studio" class="inline-flex flex-1 min-w-[130px] items-center justify-center gap-2 rounded-xl border-2 border-[#102b25] bg-white px-4 py-3 text-sm font-black text-[#102b25] shadow-lg transition hover:bg-[#102b25] hover:text-white cursor-pointer ring-2 ring-[#d4af37]/40">
                                    <span class="text-base">🧳</span>
                                    <span class="font-black text-[#102b25] uppercase tracking-wide">Book Stay</span>
                                </a>
                            @endif
                            <a href="#buy-request" class="inline-flex flex-1 min-w-[120px] items-center justify-center gap-1.5 rounded-xl border border-[#d9cab3] bg-white px-3.5 py-3 text-sm font-bold text-[#1d3c34] shadow-sm transition hover:bg-[#f8f3eb]">
                                <span>Buy / Request</span>
                            </a>
                        </div>
                    </div>

                    {{-- Jiji-style Verified Agent Box --}}
                    @if ($property->agent)
                        <div class="mt-6 rounded-2xl border border-[#d9cab3] bg-[#f8f3eb] p-4">
                            <div class="flex items-center justify-between gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#e8f5e9] px-2.5 py-0.5 text-[11px] font-bold text-[#1b5e20]">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Verified Agent
                                </span>
                                @if ($property->agent->average_rating > 0)
                                    <span class="text-xs font-black text-[#9b6c17]">★ {{ number_format($property->agent->average_rating, 1) }} ({{ $property->agent->feedback_count }})</span>
                                @endif
                            </div>

                            <div class="mt-3 flex items-center gap-3">
                                <img src="{{ $property->agent->photo_url }}" alt="{{ $property->agent->name }}" class="h-12 w-12 rounded-full object-cover ring-2 ring-[#00b53f]">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('agents.show', $property->agent) }}" class="block truncate font-bold text-[#1d3c34] hover:text-[#2e5a4c]">{{ $property->agent->name }}</a>
                                    <p class="truncate text-xs text-slate-600">{{ $property->agent->bio ?: 'Property Consultant' }}</p>
                                    <p class="text-[11px] font-semibold text-[#587165]">{{ $agentListingsCount }} active properties on market</p>
                                </div>
                                <a href="{{ route('agents.show', $property->agent) }}" class="rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs font-bold text-[#1d3c34] transition hover:bg-[#f1e6d2]">
                                    Storefront
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 rounded-2xl border border-[#d9cab3] bg-[#edf2ee] p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#587165]">Listed directly by</p>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="font-bold text-[#1d3c34]">GTM Real Estate Central Office</span>
                                <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-xs text-white">Verified</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Property Overview & Specifications --}}
        <div class="mt-8 grid gap-8 md:grid-cols-[1.4fr_0.6fr]">
            <div class="rounded-[2rem] border border-[#d9cab3] bg-white p-6 shadow-sm sm:p-8">
                <h2 class="text-2xl font-black text-[#1d3c34]">Property Description</h2>
                <div class="mt-4 text-base leading-8 text-slate-600 space-y-4">
                    <p>{{ $property->description ?: 'This property offers a premium living experience in a desirable location with thoughtful design, practical comfort, and strong investment value.' }}</p>
                </div>

                <div class="mt-8 border-t border-[#f0e4cf] pt-6">
                    <h3 class="text-lg font-bold text-[#1d3c34]">Key Details</h3>
                    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <div class="rounded-xl bg-[#fffaf2] p-3 border border-[#f0e4cf]">
                            <span class="block text-xs uppercase text-[#60716c]">Sub-City / City</span>
                            <span class="font-bold text-[#1d3c34]">{{ $property->city ?? 'Addis Ababa' }}</span>
                        </div>
                        <div class="rounded-xl bg-[#fffaf2] p-3 border border-[#f0e4cf]">
                            <span class="block text-xs uppercase text-[#60716c]">Address / Specific Area</span>
                            <span class="font-bold text-[#1d3c34]">{{ $property->address ?? 'Prime Location' }}</span>
                        </div>
                        <div class="rounded-xl bg-[#fffaf2] p-3 border border-[#f0e4cf]">
                            <span class="block text-xs uppercase text-[#60716c]">Area in Square Meters</span>
                            <span class="font-bold text-[#1d3c34]">{{ $property->area }} sqm</span>
                        </div>

                        <div class="rounded-xl bg-[#fffaf2] p-3 border border-[#f0e4cf]">
                            <span class="block text-xs uppercase text-[#60716c]">Property Purpose</span>
                            <span class="font-bold text-[#1d3c34]">{{ $property->type === 'rent' ? 'For Rent' : 'For Sale' }}</span>
                        </div>
                        <div class="rounded-xl bg-[#fffaf2] p-3 border border-[#f0e4cf]">
                            <span class="block text-xs uppercase text-[#60716c]">Category</span>
                            <span class="font-bold text-[#1d3c34]">{{ ucwords(str_replace('_', ' ', (string) ($property->property_category ?? $property->category ?? 'Home'))) }}</span>
                        </div>
                        <div class="rounded-xl bg-[#fffaf2] p-3 border border-[#f0e4cf]">
                            <span class="block text-xs uppercase text-[#60716c]">Negotiation</span>
                            <span class="font-bold text-[#1d3c34]">Open to Offers</span>
                        </div>
                    </div>
                </div>

                {{-- Ethiopian Physical Specifications & Utility Infrastructure (Module 1) --}}
                <div class="mt-8 border-t border-[#f0e4cf] pt-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-[#1d3c34]">Physical Specifications & Utilities</h3>
                            <p class="text-xs text-slate-500">Verified architectural parameters and infrastructure readiness</p>
                        </div>
                        @if ($property->property_code)
                            <span class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#102b25]/5 px-3 py-1 text-xs font-mono font-bold text-[#102b25] border border-[#102b25]/15">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                Master Asset: {{ $property->property_code }}
                            </span>
                        @endif
                    </div>

                    {{-- Specifications Grid --}}
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-xl bg-[#fffaf2] p-3 border border-[#f0e4cf]">
                            <span class="block text-[11px] font-semibold uppercase tracking-wider text-[#60716c]">Building Area</span>
                            <span class="text-sm font-bold text-[#1d3c34]">{{ $property->building_area ? $property->building_area . ' sqm' : ($property->area ? $property->area . ' sqm' : 'N/A') }}</span>
                        </div>
                        <div class="rounded-xl bg-[#fffaf2] p-3 border border-[#f0e4cf]">
                            <span class="block text-[11px] font-semibold uppercase tracking-wider text-[#60716c]">Stories / Floors</span>
                            <span class="text-sm font-bold text-[#1d3c34]">{{ $property->floors !== null && $property->floors !== '' ? ($property->floors > 1 ? 'G+' . ($property->floors - 1) . ' (' . $property->floors . ' levels)' : 'G+0 (1 levels)') : 'G+0 (1 levels)' }}</span>
                        </div>
                        <div class="rounded-xl bg-[#fffaf2] p-3 border border-[#f0e4cf]">
                            <span class="block text-[11px] font-semibold uppercase tracking-wider text-[#60716c]">Parking Spaces</span>
                            <span class="text-sm font-bold text-[#1d3c34]">{{ $property->parking_spaces !== null ? $property->parking_spaces . ' Vehicles' : '1 Vehicles' }}</span>
                        </div>
                        <div class="rounded-xl bg-[#fffaf2] p-3 border border-[#f0e4cf]">
                            <span class="block text-[11px] font-semibold uppercase tracking-wider text-[#60716c]">Asset Condition</span>
                            <span class="text-sm font-bold lowercase text-[#1d3c34]">{{ str_replace('_', ' ', $property->condition ?? 'excellent') }}</span>
                        </div>
                    </div>

                    {{-- Utilities Badges --}}
                    <div class="mt-4 pt-4 border-t border-dashed border-[#ecd9be]">
                        <span class="block text-xs font-bold uppercase tracking-wider text-[#587165] mb-2.5">Ethiopian Utility Readiness</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                            <div class="flex items-center gap-2 rounded-xl p-2.5 {{ $property->has_electricity ? 'bg-[#eef8f2] text-[#1b5e20] border border-[#c8e6c9]' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                                <svg class="h-4 w-4 shrink-0 {{ $property->has_electricity ? 'text-[#00b53f]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span class="font-bold">{{ $property->has_electricity ? '3-Phase / Electric' : 'Standard Electric' }}</span>
                            </div>
                            <div class="flex items-center gap-2 rounded-xl p-2.5 {{ $property->has_water ? 'bg-[#eef8f2] text-[#1b5e20] border border-[#c8e6c9]' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                                <svg class="h-4 w-4 shrink-0 {{ $property->has_water ? 'text-blue-500' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                <span class="font-bold">{{ $property->has_water ? 'Municipal Water Line' : 'City Line Pending' }}</span>
                            </div>
                            <div class="flex items-center gap-2 rounded-xl p-2.5 {{ $property->has_water_tank ? 'bg-[#eef8f2] text-[#1b5e20] border border-[#c8e6c9]' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                                <svg class="h-4 w-4 shrink-0 {{ $property->has_water_tank ? 'text-cyan-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span class="font-bold">{{ $property->has_water_tank ? 'Water Reservoir Tank' : 'No Reservoir Tank' }}</span>
                            </div>
                            <div class="flex items-center gap-2 rounded-xl p-2.5 {{ $property->has_generator ? 'bg-[#eef8f2] text-[#1b5e20] border border-[#c8e6c9]' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                                <svg class="h-4 w-4 shrink-0 {{ $property->has_generator ? 'text-amber-500' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span class="font-bold">{{ $property->has_generator ? 'Backup Standby Generator' : 'Generator Ready' }}</span>
                            </div>
                            <div class="flex items-center gap-2 rounded-xl p-2.5 {{ $property->has_solar ? 'bg-[#eef8f2] text-[#1b5e20] border border-[#c8e6c9]' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                                <svg class="h-4 w-4 shrink-0 {{ $property->has_solar ? 'text-amber-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <span class="font-bold">{{ $property->has_solar ? 'Solar Energy System' : 'Solar Compatible' }}</span>
                            </div>
                            <div class="flex items-center gap-2 rounded-xl p-2.5 {{ $property->has_security ? 'bg-[#eef8f2] text-[#1b5e20] border border-[#c8e6c9]' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                                <svg class="h-4 w-4 shrink-0 {{ $property->has_security ? 'text-[#1d3c34]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span class="font-bold">{{ $property->has_security ? '24/7 Gated Security' : 'Gated Compound' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ethiopian Property Verification & Legal Documentation Shield --}}
                @include('properties.partials.verification-shield')

                {{-- Luxury Mortgage & Investment Calculator --}}
                <div id="luxury-financial-calculator" data-property-price="{{ $property->price }}" data-property-type="{{ $property->type }}" class="mt-8 border-t border-[#f0e4cf] pt-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#d4af37]">Financial Advisory</span>
                                <h3 class="text-xl font-black text-[#1d3c34]">Financing & Investment Studio</h3>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Estimate monthly mortgage payments or analyze rental yields and 5-year capital appreciation.</p>
                        </div>

                        {{-- Dual Mode Tabs --}}
                        <div class="inline-flex rounded-2xl bg-[#f0e4cf]/80 p-1 border-2 border-[#d9cab3]">
                            <button type="button" id="calc-tab-mortgage" class="flex-1 rounded-xl border border-[#1d3c34] bg-[#1d3c34] px-5 py-2.5 text-sm font-black uppercase tracking-wider text-white shadow-sm transition cursor-pointer whitespace-nowrap">
                                Mortgage Estimator
                            </button>
                            <button type="button" id="calc-tab-roi" class="flex-1 rounded-xl border border-[#d9cab3] bg-[#fffaf2] px-5 py-2.5 text-sm font-black uppercase tracking-wider text-[#1d3c34] hover:bg-[#f4ebdc] transition cursor-pointer whitespace-nowrap">
                                ROI &amp; Yield
                            </button>
                        </div>
                    </div>

                    {{-- Panel 1: Mortgage Calculator --}}
                    <div id="calc-panel-mortgage" class="mt-6 grid gap-6 md:grid-cols-[1.1fr_0.9fr]">
                        <div class="space-y-5 rounded-2xl bg-[#fffaf2] p-5 border border-[#ecd9be]">
                            {{-- Down Payment --}}
                            <div>
                                <div class="flex items-center justify-between text-xs font-black text-[#1d3c34]">
                                    <span class="font-bold text-[#1d3c34]">Down Payment (<span id="calc-down-val" class="font-black">20%</span>)</span>
                                    <span id="calc-down-amount" class="text-sm font-black text-[#855e16]">ETB 0</span>
                                </div>
                                <input type="range" id="calc-down-slider" min="5" max="50" step="1" value="20" class="mt-2 w-full accent-[#1d3c34] cursor-pointer">
                                <div class="mt-1 flex justify-between text-[11px] font-bold text-slate-500">
                                    <span>5% (Min)</span>
                                    <span>20% (Standard)</span>
                                    <span>50%</span>
                                </div>
                            </div>

                            {{-- Loan Term --}}
                            <div>
                                <label for="calc-term-select" class="block text-xs font-black text-[#1d3c34]">Loan Duration (Term)</label>
                                <select id="calc-term-select" class="mt-1.5 w-full rounded-xl border-2 border-[#d9cab3] bg-white px-3 py-2.5 text-xs font-black text-[#1d3c34] focus:ring-2 focus:ring-[#1d3c34]/20 focus:outline-none">
                                    <option value="5" class="font-bold">5 Years (60 Months)</option>
                                    <option value="10" class="font-bold">10 Years (120 Months)</option>
                                    <option value="15" class="font-bold">15 Years (180 Months)</option>
                                    <option value="20" selected class="font-bold">20 Years (240 Months - Standard)</option>
                                    <option value="25" class="font-bold">25 Years (300 Months)</option>
                                    <option value="30" class="font-bold">30 Years (360 Months)</option>
                                </select>
                            </div>

                            {{-- Interest Rate --}}
                            <div>
                                <div class="flex items-center justify-between text-xs font-black text-[#1d3c34]">
                                    <span class="font-bold text-[#1d3c34]">Annual Interest Rate</span>
                                    <span id="calc-interest-val" class="text-sm font-black text-[#1d3c34]">14.5%</span>
                                </div>
                                <input type="range" id="calc-interest-slider" min="3.0" max="25.0" step="0.1" value="14.5" class="mt-2 w-full accent-[#1d3c34] cursor-pointer">
                                <div class="mt-2 flex items-center gap-1.5">
                                    <span class="text-[11px] font-black uppercase text-slate-500">Presets:</span>
                                    <button type="button" data-calc-rate="10.0" class="rounded-lg border-2 border-[#d9cab3] bg-white px-2.5 py-0.5 text-[11px] font-black text-[#1d3c34] hover:bg-[#f0e4cf] cursor-pointer">10%</button>
                                    <button type="button" data-calc-rate="14.5" class="rounded-lg border-2 border-[#d9cab3] bg-white px-2.5 py-0.5 text-[11px] font-black text-[#1d3c34] hover:bg-[#f0e4cf] cursor-pointer">14.5%</button>
                                    <button type="button" data-calc-rate="18.0" class="rounded-lg border-2 border-[#d9cab3] bg-white px-2.5 py-0.5 text-[11px] font-black text-[#1d3c34] hover:bg-[#f0e4cf] cursor-pointer">18%</button>
                                </div>
                            </div>

                            {{-- Tax toggle --}}
                            <div class="flex items-center justify-between border-t border-[#f0e4cf] pt-3">
                                <div>
                                    <span class="block text-xs font-black text-[#1d3c34]">Include Estimated Property Tax &amp; Insurance</span>
                                    <span class="block text-[11px] font-bold text-slate-500">~0.8% annually</span>
                                </div>
                                <input type="checkbox" id="calc-tax-toggle" checked class="h-4 w-4 rounded accent-[#1d3c34] cursor-pointer">
                            </div>
                        </div>

                        {{-- Mortgage Outputs --}}
                        <div class="flex flex-col justify-between rounded-2xl border border-[#102b25] bg-[#102b25] p-6 text-white shadow-md">
                            <div>
                                <span class="text-xs font-black uppercase tracking-[0.2em] text-white">Estimated Monthly Payment</span>
                                <p id="calc-monthly-total" class="mt-2 text-3xl font-black text-white sm:text-4xl">
                                    ETB 0
                                </p>
                                <p class="text-xs font-bold text-slate-200 mt-1">Principal, interest &amp; insurance</p>

                                {{-- Proportion Bar --}}
                                <div class="mt-5">
                                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-white/15 flex">
                                        <div id="calc-bar-principal" class="bg-[#d4af37] transition-all duration-300" style="width: 85%;"></div>
                                        <div id="calc-bar-tax" class="bg-[#e7d8b7] transition-all duration-300" style="width: 15%;"></div>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-xs font-bold text-white">
                                        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-[#d4af37]"></span> P&amp;I: <strong id="calc-pi-amount" class="text-white font-black">0</strong></span>
                                        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-[#e7d8b7]"></span> Tax/Ins: <strong id="calc-tax-amount" class="text-white font-black">0</strong></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 space-y-2.5 border-t border-white/15 pt-4 text-xs font-bold">
                                <div class="flex items-center justify-between text-white">
                                    <span class="font-bold">Financed Loan Amount:</span>
                                    <span id="calc-loan-amount" class="font-black text-[#d4af37] text-sm">0</span>
                                </div>
                                <div class="flex items-center justify-between text-white">
                                    <span class="font-bold">Total Interest Paid:</span>
                                    <span id="calc-total-interest" class="font-black text-[#f0d68e] text-sm">0</span>
                                </div>
                                <div class="flex items-center justify-between text-white">
                                    <span class="font-bold">Total Loan Outlay:</span>
                                    <span id="calc-total-cost" class="font-black text-white text-sm">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Panel 2: Investment ROI Calculator --}}
                    <div id="calc-panel-roi" class="hidden mt-6 grid gap-6 md:grid-cols-[1.1fr_0.9fr]">
                        <div class="space-y-5 rounded-2xl bg-[#fffaf2] p-5 border border-[#ecd9be]">
                            <div>
                                <div class="flex items-center justify-between text-xs font-black text-[#1d3c34]">
                                    <span class="font-bold text-[#1d3c34]">Estimated Monthly Rent</span>
                                    <span id="calc-roi-rent-display" class="text-sm font-black text-[#855e16]">ETB 0 / mo</span>
                                </div>
                                <input type="range" id="calc-roi-rent-slider" min="50" max="200" step="5" value="100" class="mt-2 w-full accent-[#1d3c34] cursor-pointer">
                                <div class="mt-1 flex justify-between text-[11px] font-bold text-slate-500">
                                    <span>Conservative (-50%)</span>
                                    <span>Market Baseline</span>
                                    <span>Aggressive (+100%)</span>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between text-xs font-black text-[#1d3c34]">
                                    <span class="font-bold text-[#1d3c34]">Operating Expenses &amp; Vacancy (<span id="calc-roi-expense-val" class="font-black">10%</span>)</span>
                                </div>
                                <input type="range" id="calc-roi-expense-slider" min="0" max="25" step="1" value="10" class="mt-2 w-full accent-[#1d3c34] cursor-pointer">
                            </div>

                            <div class="rounded-xl bg-white p-3.5 border border-[#ecd9be] text-xs text-slate-700">
                                <strong class="font-black text-[#1d3c34] block">💡 Prime Capital Growth Assumption:</strong>
                                <p class="mt-1 text-[11px] font-bold leading-relaxed text-slate-600">Calculations assume Addis Ababa luxury corridor historical 10% annual capital appreciation compounded over 5 years.</p>
                            </div>
                        </div>

                        {{-- ROI Outputs --}}
                        <div class="flex flex-col justify-between rounded-2xl border border-[#102b25] bg-[#102b25] p-6 text-white shadow-md">
                            <div>
                                <span class="text-xs font-black uppercase tracking-[0.2em] text-white">Gross Rental Yield</span>
                                <p id="calc-roi-gross-yield" class="mt-2 text-3xl font-black text-white sm:text-4xl">
                                    0%
                                </p>
                                <p class="text-xs font-bold text-slate-200 mt-1">Annual gross return on asset value</p>
                            </div>

                            <div class="mt-6 space-y-2.5 border-t border-white/15 pt-4 text-xs font-bold">
                                <div class="flex items-center justify-between text-white">
                                    <span class="font-bold">Annual Net Cash Flow:</span>
                                    <span id="calc-roi-net-annual" class="font-black text-[#d4af37] text-sm">0</span>
                                </div>
                                <div class="flex items-center justify-between text-white">
                                    <span class="font-bold">Projected Value in 5 Yrs:</span>
                                    <span id="calc-roi-5yr-val" class="font-black text-[#f0d68e] text-sm">0</span>
                                </div>
                                <div class="flex items-center justify-between text-white">
                                    <span class="font-bold">Total 5-Yr Projected Return:</span>
                                    <span id="calc-roi-total-return" class="font-black text-white text-base">+0%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Interactive Location Map Section --}}
                {{-- Interactive Location Map & Ethiopian Amenity Radar --}}
                <div class="mt-8 border-t border-[#f0e4cf] pt-8">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#d4af37]">GIS Engine </span>
                                <span class="text-xs font-bold text-[#587165]">አካባቢያዊ መረጃ እና ምቹ ሁኔታዎች</span>
                            </div>
                            <h3 class="text-2xl font-black text-[#1d3c34] mt-1">Explore Location & Neighborhood</h3>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $property->subcity ? $property->subcity.' Sub-City · ' : '' }}
                                {{ $property->neighborhood ? $property->neighborhood.' · ' : '' }}
                                {{ $property->city ?? 'Addis Ababa' }}
                            </p>
                        </div>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $property->coordinates[0] }},{{ $property->coordinates[1] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-full border border-[#d9cab3] bg-[#fffaf2] px-4 py-2 text-xs font-bold text-[#1d3c34] hover:bg-[#f0e4cf] transition shadow-xs">
                            <svg class="h-3.5 w-3.5 text-[#1d3c34]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>Open in Google Maps</span>
                        </a>
                    </div>

                    {{-- Geographic Sub-City & Woreda Taxonomy Badges --}}
                    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#102b25] px-3 py-1 font-bold text-[#d4af37]">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <span>{{ $property->subcity ? $property->subcity.' Sub-City (ክፍለ ከተማ)' : ($property->city ?? 'Addis Ababa') }}</span>
                        </span>
                        @if ($property->woreda)
                            <span class="inline-flex items-center gap-1 rounded-full bg-[#f4ece1] px-3 py-1 font-bold text-[#102b25] border border-[#d9cab3]">
                                <span>{{ $property->woreda }}</span>
                            </span>
                        @endif
                        @if ($property->neighborhood)
                            <span class="inline-flex items-center gap-1 rounded-full bg-[#edf3ee] px-3 py-1 font-bold text-[#0f5132] border border-[#c3d9c7]">
                                <span>📍 {{ $property->neighborhood }}</span>
                            </span>
                        @endif
                        <span class="font-mono text-[11px] text-slate-400">
                            GPS: {{ number_format($property->coordinates[0], 4) }}, {{ number_format($property->coordinates[1], 4) }}
                        </span>
                    </div>




                    {{-- Interactive Leaflet Map with Amenity Pins --}}
                    <div class="mt-5 overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-[#f0e4cf] bg-[#fffaf2] px-5 py-3">
                            <span class="text-xs font-bold text-[#102b25]">Property &amp; Verified Neighborhood Amenity Markers</span>
                            <span class="text-[11px] text-slate-500">Interactive: click colored markers to inspect travel times</span>
                        </div>
                        <div id="single-property-map"
                            data-lat="{{ $property->coordinates[0] }}"
                            data-lng="{{ $property->coordinates[1] }}"
                            data-title="{{ $property->title }}"
                            data-address="{{ $property->subcity ? $property->subcity.', ' : '' }}{{ $property->city ?? 'Addis Ababa' }} · {{ $property->address ?? 'Prime Location' }}"
                            data-price="{{ $property->price }}"
                            data-amenities="{{ json_encode($property->nearby_amenities_list) }}"
                            class="h-[380px] w-full z-10"
                            style="height: 380px; min-height: 380px; width: 100%; display: block; position: relative;"></div>
                    </div>

                </div>

                {{-- Module 4: Property Viewing Engine --}}
                @php
                    $myViewings = auth()->check()
                        ? $property->viewings()->where('user_id', auth()->id())->latest()->get()
                        : collect();
                    $myOffers = auth()->check()
                        ? $property->offers()->where('user_id', auth()->id())->with('negotiations')->latest()->get()
                        : collect();
                @endphp

                <div id="schedule-viewing-section" class="scroll-mt-24 mt-8 rounded-[2rem] border border-[#d9cab3] bg-white p-6 md:p-8 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#f0e4cf] pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#d4af37]">Appointment Core</span>
                                <h3 class="text-xl font-black text-[#1d3c34]">Schedule a Property Viewing</h3>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Choose between an on-site guided inspection or a live interactive virtual tour.</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold text-[#1d3c34]">
                            <svg class="h-3.5 w-3.5 text-[#00b53f]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Verified Agent Accompanied
                        </span>
                    </div>

                    @if ($myViewings->isNotEmpty())
                        <div class="mt-4 rounded-2xl bg-[#f8f3eb] p-4 border border-[#ecd9be]">
                            <h4 class="text-xs font-black uppercase tracking-wider text-[#1d3c34]">Your Scheduled Appointments</h4>
                            <div class="mt-2 space-y-2">
                                @foreach ($myViewings as $v)
                                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl bg-white p-3 text-xs border border-[#e2d5c3]">
                                        <div>
                                            <span class="font-mono font-bold text-[#1d3c34]">{{ $v->viewing_reference }}</span>
                                            <span class="text-slate-500">· {{ \Carbon\Carbon::parse($v->viewing_date)->format('M d, Y') }} ({{ $v->formatted_time_slot }})</span>
                                            <span class="ml-1 text-[11px] font-semibold text-slate-600">[{{ ucfirst(str_replace('_', ' ', $v->viewing_type)) }}]</span>
                                        </div>
                                        <span class="rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider
                                            @if($v->status === 'confirmed') bg-emerald-100 text-emerald-800
                                            @elseif($v->status === 'requested') bg-amber-100 text-amber-800
                                            @elseif($v->status === 'completed') bg-blue-100 text-blue-800
                                            @else bg-slate-100 text-slate-700 @endif">
                                            {{ ucfirst($v->status) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('properties.viewings.store', $property) }}" data-auth-gate class="mt-6 space-y-4">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Inspection Format <span class="text-red-500">*</span></label>
                                <div class="mt-1.5 grid grid-cols-2 gap-2">
                                    <label class="flex items-center gap-2 rounded-xl border border-[#d9cab3] bg-[#fffdfa] p-3 text-xs font-bold text-[#1d3c34] cursor-pointer hover:border-[#102b25] transition has-[:checked]:border-[#102b25] has-[:checked]:bg-[#edf2ee]">
                                        <input type="radio" name="viewing_type" value="in_person" checked class="text-[#102b25] focus:ring-[#102b25]">
                                        <span>🚶 In-Person Visit</span>
                                    </label>
                                    <label class="flex items-center gap-2 rounded-xl border border-[#d9cab3] bg-[#fffdfa] p-3 text-xs font-bold text-[#1d3c34] cursor-pointer hover:border-[#102b25] transition has-[:checked]:border-[#102b25] has-[:checked]:bg-[#edf2ee]">
                                        <input type="radio" name="viewing_type" value="virtual_tour" class="text-[#102b25] focus:ring-[#102b25]">
                                        <span>📹 Virtual Tour</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <x-ethiopian-date-picker
                                    name="viewing_date"
                                    id="viewing-date"
                                    label="Preferred Date"
                                    min="{{ date('Y-m-d') }}"
                                    value="{{ old('viewing_date', date('Y-m-d', strtotime('+1 day'))) }}"
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Time Window Slot <span class="text-red-500">*</span></label>
                            <div class="mt-1.5 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <label class="flex items-center justify-between rounded-xl border border-[#d9cab3] bg-[#fffdfa] p-3 text-xs font-bold text-[#1d3c34] cursor-pointer hover:border-[#102b25] transition has-[:checked]:border-[#102b25] has-[:checked]:bg-[#edf2ee]">
                                    <div>
                                        <span class="block font-black">🌅 Morning</span>
                                        <span class="text-[10px] text-slate-500 font-normal">09:00 AM – 11:00 AM</span>
                                    </div>
                                    <input type="radio" name="viewing_time" value="morning_09_11" checked class="text-[#102b25] focus:ring-[#102b25]">
                                </label>
                                <label class="flex items-center justify-between rounded-xl border border-[#d9cab3] bg-[#fffdfa] p-3 text-xs font-bold text-[#1d3c34] cursor-pointer hover:border-[#102b25] transition has-[:checked]:border-[#102b25] has-[:checked]:bg-[#edf2ee]">
                                    <div>
                                        <span class="block font-black">☀️ Afternoon</span>
                                        <span class="text-[10px] text-slate-500 font-normal">02:00 PM – 04:00 PM</span>
                                    </div>
                                    <input type="radio" name="viewing_time" value="afternoon_14_16" class="text-[#102b25] focus:ring-[#102b25]">
                                </label>
                                <label class="flex items-center justify-between rounded-xl border border-[#d9cab3] bg-[#fffdfa] p-3 text-xs font-bold text-[#1d3c34] cursor-pointer hover:border-[#102b25] transition has-[:checked]:border-[#102b25] has-[:checked]:bg-[#edf2ee]">
                                    <div>
                                        <span class="block font-black">🌇 Evening</span>
                                        <span class="text-[10px] text-slate-500 font-normal">05:00 PM – 07:00 PM</span>
                                    </div>
                                    <input type="radio" name="viewing_time" value="evening_17_19" class="text-[#102b25] focus:ring-[#102b25]">
                                </label>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Your Full Name <span class="text-red-500">*</span></label>
                                <input type="text" name="buyer_name" value="{{ old('buyer_name', auth()->user()?->name ?? '') }}" required placeholder="e.g. Dawit Tadesse" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" name="buyer_email" value="{{ old('buyer_email', auth()->user()?->email ?? '') }}" required placeholder="e.g. dawit@example.com" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Phone Number <span class="text-red-500">*</span></label>
                                <input type="tel" name="buyer_phone" value="{{ old('buyer_phone') }}" required placeholder="e.g. +251 91 123 4567" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Special Requests / Notes (Optional)</label>
                            <textarea name="buyer_notes" rows="2" placeholder="e.g. Interested in inspecting boundary walls, parking access, or electrical installations." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">{{ old('buyer_notes') }}</textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                            <span class="text-[11px] text-slate-500">🔒 Free appointment reservation · Confirmation provided by verified agent</span>
                            <button type="submit" class="rounded-xl bg-[#102b25] px-6 py-2.5 text-xs font-black uppercase tracking-wider text-[#d4af37] shadow-md hover:bg-[#1d3c34] transition">
                                Confirm Viewing Request
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Module 4: Formal Offer & Multi-Round Negotiation Engine --}}
                <div id="formal-offer-section" class="scroll-mt-24 mt-8 rounded-[2rem] border-2 border-[#d4af37] bg-white p-6 md:p-8 shadow-xl">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#f0e4cf] pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#d4af37]">Transaction Engine</span>
                                <h3 class="text-xl font-black text-[#1d3c34]">Make Formal Offer & Negotiation Desk</h3>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Submit an official purchase or lease proposal with transparent financing and deposit terms.</p>
                        </div>
                        <div class="text-right">
                            <span class="block text-[10px] uppercase font-bold text-slate-400">Asking Price</span>
                            <span class="text-lg font-black text-[#1d3c34] currency-price" data-base-price="{{ $property->price }}">ETB {{ number_format($property->price) }}</span>
                        </div>
                    </div>

                    {{-- Active Negotiation Trail (if user is logged in and has offers) --}}
                    @if ($myOffers->isNotEmpty())
                        <div class="mt-6 space-y-4">
                            <h4 class="text-xs font-black uppercase tracking-wider text-[#1d3c34]">Your Active Offer & Negotiation Trail</h4>
                            @foreach ($myOffers as $offer)
                                <div class="rounded-2xl border border-[#d9cab3] bg-[#fffaf2] p-4 text-xs">
                                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#ecd9be] pb-3">
                                        <div>
                                            <span class="font-mono font-black text-sm text-[#102b25]">{{ $offer->offer_reference }}</span>
                                            <span class="text-slate-500">· Original Offer: <strong>ETB {{ number_format($offer->offered_price) }}</strong></span>
                                            @if ($offer->current_counter_amount)
                                                <span class="ml-2 font-bold text-[#b45309]">Current Round: ETB {{ number_format($offer->current_counter_amount) }}</span>
                                            @endif
                                        </div>
                                        <span class="rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-wider
                                            @if($offer->status === 'accepted') bg-emerald-100 text-emerald-800
                                            @elseif($offer->status === 'countered') bg-amber-100 text-amber-800
                                            @elseif($offer->status === 'declined') bg-rose-100 text-rose-800
                                            @else bg-blue-100 text-blue-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $offer->status)) }}
                                        </span>
                                    </div>

                                    {{-- Negotiation log --}}
                                    @if ($offer->negotiations->isNotEmpty())
                                        <div class="mt-3 space-y-2">
                                            @foreach ($offer->negotiations as $neg)
                                                <div class="flex items-start gap-2.5 rounded-xl p-2.5 {{ $neg->sender_type === 'buyer' ? 'bg-white border border-[#ecd9be]' : 'bg-[#102b25]/5 border border-[#102b25]/10' }}">
                                                    <span class="rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $neg->sender_type === 'buyer' ? 'bg-slate-200 text-slate-800' : 'bg-[#102b25] text-[#d4af37]' }}">
                                                        {{ $neg->sender_type === 'buyer' ? 'Buyer' : 'Agent/Owner' }}
                                                    </span>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center justify-between">
                                                            <strong class="font-bold text-[#1d3c34]">ETB {{ number_format($neg->amount) }}</strong>
                                                            <span class="text-[10px] text-slate-400">{{ $neg->created_at->diffForHumans() }}</span>
                                                        </div>
                                                        @if ($neg->message)
                                                            <p class="mt-0.5 text-slate-600 italic">“{{ $neg->message }}”</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Buyer Counter Action if status is countered --}}
                                    @if ($offer->status === 'countered' && $offer->latest_counter_by !== 'buyer')
                                        <form method="POST" action="{{ route('offers.counter', $offer) }}" class="mt-4 pt-3 border-t border-[#ecd9be] flex flex-wrap items-center gap-2">
                                            @csrf
                                            <input type="number" step="0.01" min="1000" name="counter_amount" placeholder="Your Counter Proposal (ETB)" required class="rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25] flex-1 min-w-[180px]">
                                            <input type="text" name="message" placeholder="Optional response note..." class="rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25] flex-1 min-w-[200px]">
                                            <button type="submit" class="rounded-xl bg-[#102b25] px-4 py-2 text-xs font-black text-[#d4af37] hover:bg-[#1d3c34] transition">
                                                Counter Propose
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- New Offer Form --}}
                    <form method="POST" action="{{ route('properties.offers.store', $property) }}" data-auth-gate class="mt-6 space-y-4" id="formal-offer-form">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="offered-price-input" class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Your Proposed Price (ETB) <span class="text-red-500">*</span></label>
                                <div class="relative mt-1">
                                    <input type="number" step="0.01" min="1000" id="offered-price-input" name="offered_price" value="{{ old('offered_price', $property->price) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-black text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                                </div>
                                <div id="offer-variance-tag" class="mt-1 text-[11px] font-bold text-slate-500">
                                    Full asking price (ETB {{ number_format($property->price) }})
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Financing Structure <span class="text-red-500">*</span></label>
                                <select name="financing_type" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                                    <option value="cash" {{ old('financing_type') === 'cash' ? 'selected' : '' }}>💵 Cash Settlement (100% Upfront)</option>
                                    <option value="bank_loan" {{ old('financing_type') === 'bank_loan' ? 'selected' : '' }}>🏦 Bank Loan / Commercial Mortgage</option>
                                    <option value="diaspora_remittance" {{ old('financing_type') === 'diaspora_remittance' ? 'selected' : '' }}>🌍 Diaspora Remittance (USD/EUR via CBE / Awash / Dashen)</option>
                                    <option value="installments" {{ old('financing_type') === 'installments' ? 'selected' : '' }}>📅 Structured Installment Plan</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="earnest-deposit-input" class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Earnest Money Deposit (ETB, Optional)</label>
                                <input type="number" step="0.01" min="0" id="earnest-deposit-input" name="earnest_deposit" value="{{ old('earnest_deposit', round($property->price * 0.05)) }}" placeholder="e.g. 5% - 10% commitment deposit" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                                <span class="text-[10px] text-slate-400">Guarantees exclusivity upon offer acceptance</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Offer Validity Duration</label>
                                <select name="validity_days" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                                    <option value="7" selected>7 Days (Standard)</option>
                                    <option value="14">14 Days</option>
                                    <option value="30">30 Days</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Your Full Name <span class="text-red-500">*</span></label>
                                <input type="text" name="buyer_name" value="{{ old('buyer_name', auth()->user()?->name ?? '') }}" required placeholder="e.g. Betelhem Girma" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" name="buyer_email" value="{{ old('buyer_email', auth()->user()?->email ?? '') }}" required placeholder="e.g. betelhem@example.com" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Phone Number <span class="text-red-500">*</span></label>
                                <input type="tel" name="buyer_phone" value="{{ old('buyer_phone') }}" required placeholder="e.g. +251 92 345 6789" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Offer Terms & Contingencies (Optional)</label>
                            <textarea name="buyer_notes" rows="2" placeholder="e.g. Subject to municipal title deed verification (የካርታ ማረጋገጫ) and structural inspection within 5 business days." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">{{ old('buyer_notes') }}</textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                            <span class="text-[11px] text-slate-500">📋 Generates official audit reference for formal transaction ledger</span>
                            <button type="submit" class="w-full sm:w-auto rounded-xl bg-gradient-to-r from-[#9b6c17] to-[#d4af37] px-8 py-3 text-xs font-black uppercase tracking-widest text-white shadow-lg hover:shadow-xl hover:scale-[1.01] active:scale-[0.99] transition cursor-pointer">
                                Submit Formal Offer
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Verified Multi-Criteria Reviews & Ratings --}}
                @include('properties.partials.reviews-section')

                {{-- Resident & Tenant Maintenance & Housekeeping Service Desk --}}
                @include('properties.partials.maintenance-section')
            </div>

            {{-- Safety & Direct Agent Card sidebar --}}
            <div class="space-y-6">
                @if ($property->isShortStay())
                    @php
                        $stayListing = $property->stayListing();
                        $nightlyPrice = $property->getNightlyRate();
                        $cleaningFee = $property->getCleaningFee();
                        $customServiceFee = $property->getCustomServiceFee();
                        $initialServiceFee = ! is_null($customServiceFee) ? $customServiceFee : round(($nightlyPrice * 2) * 0.05, 2);
                        $tomorrow = date('Y-m-d', strtotime('+1 day'));
                        $dayAfter = date('Y-m-d', strtotime('+3 days'));
                    @endphp

                    <div id="stay-reservation-studio" class="scroll-mt-24 rounded-[2rem] border-2 border-[#d4af37] bg-white p-6 shadow-xl relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="rounded-full bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#d4af37]">
                                Short-Stay
                            </span>
                            <span class="text-xs font-bold text-[#1b5e20] flex items-center gap-1">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Instant Reservation
                            </span>
                        </div>

                        <div class="mt-4 flex items-baseline justify-between border-b border-[#f0e4cf] pb-4">
                            <div>
                                <span id="stay-widget-price" class="text-3xl font-black text-[#1d3c34] currency-price" data-base-price="{{ $nightlyPrice }}">ETB {{ number_format($nightlyPrice) }}</span>
                                <span class="text-xs font-semibold text-slate-500">/ night</span>
                            </div>
                            <div class="text-right text-xs">
                                <span class="font-bold text-[#9b6c17]">★ 4.9</span>
                                <span class="text-slate-400">· Serviced Stay</span>
                            </div>
                        </div>

                        {{-- Booking Form --}}
                        <form method="POST" action="{{ route('stays.book', $property) }}" class="mt-5 space-y-4" id="stay-booking-form">
                            @csrf

                            <div class="rounded-2xl border border-[#d9cab3] overflow-hidden bg-[#fffdfa]">
                                <div class="grid grid-cols-2 divide-x divide-[#d9cab3] border-b border-[#d9cab3]">
                                    <div class="p-2.5">
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-[#587165]">Check-In</label>
                                        <input type="date" name="check_in_date" id="stay-checkin" value="{{ old('check_in_date', $tomorrow) }}" min="{{ date('Y-m-d') }}" required class="mt-1 w-full bg-transparent text-xs font-bold text-[#1d3c34] focus:outline-none cursor-pointer">
                                    </div>
                                    <div class="p-2.5">
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-[#587165]">Check-Out</label>
                                        <input type="date" name="check_out_date" id="stay-checkout" value="{{ old('check_out_date', $dayAfter) }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required class="mt-1 w-full bg-transparent text-xs font-bold text-[#1d3c34] focus:outline-none cursor-pointer">
                                    </div>
                                </div>

                                <div class="p-2.5">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-[#587165]">Guests Capacity</label>
                                    <select name="guests_count" id="stay-guests" class="mt-1 w-full bg-transparent text-xs font-bold text-[#1d3c34] focus:outline-none cursor-pointer">
                                        @for ($g = 1; $g <= max(2, $property->bedrooms * 2); $g++)
                                            <option value="{{ $g }}" {{ old('guests_count', 1) == $g ? 'selected' : '' }}>{{ $g }} {{ $g === 1 ? 'Guest' : 'Guests' }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            {{-- Live Breakdown Box --}}
                            <div class="rounded-xl bg-[#fffaf2] p-3.5 border border-[#ecd9be] space-y-2 text-xs">
                                <div class="flex items-center justify-between text-slate-600">
                                    <span id="breakdown-rate-label" class="currency-price" data-base-price="{{ $nightlyPrice }}" data-price-suffix=" × 2 nights">{{ number_format($nightlyPrice) }} ETB × 2 nights</span>
                                    <span id="breakdown-subtotal" class="font-bold text-[#1d3c34] currency-price" data-base-price="{{ $nightlyPrice * 2 }}">{{ number_format($nightlyPrice * 2) }} ETB</span>
                                </div>
                                <div class="flex items-center justify-between text-slate-600">
                                    <span>Cleaning fee</span>
                                    <span id="breakdown-cleaning" class="font-bold text-[#1d3c34] currency-price" data-base-price="{{ $cleaningFee }}">{{ number_format($cleaningFee) }} ETB</span>
                                </div>
                                <div class="flex items-center justify-between text-slate-600">
                                    <span>Platform service fee {{ is_null($customServiceFee) ? '(5%)' : '' }}</span>
                                    <span id="breakdown-service" class="font-bold text-[#1d3c34] currency-price" data-base-price="{{ $initialServiceFee }}">{{ number_format($initialServiceFee) }} ETB</span>
                                </div>
                                <div class="border-t border-[#ecd9be] pt-2 flex items-center justify-between font-black text-sm text-[#1d3c34]">
                                    <span>Total</span>
                                    <span id="breakdown-total" class="text-base text-[#102b25] currency-price" data-base-price="{{ ($nightlyPrice * 2) + $cleaningFee + $initialServiceFee }}">{{ number_format(($nightlyPrice * 2) + $cleaningFee + $initialServiceFee) }} ETB</span>
                                </div>
                            </div>

                            {{-- Contact Fields --}}
                            <div class="space-y-2.5 pt-1">
                                <div>
                                    <input type="text" name="guest_name" value="{{ old('guest_name', auth()->user()?->name) }}" placeholder="Your Full Name *" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="email" name="guest_email" value="{{ old('guest_email', auth()->user()?->email) }}" placeholder="Email Address *" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                                    <input type="tel" name="guest_phone" value="{{ old('guest_phone') }}" placeholder="Phone (e.g. 0911...) *" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                                </div>
                                <div>
                                    <textarea name="special_requests" rows="2" placeholder="Special requests, arrival notes..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">{{ old('special_requests') }}</textarea>
                                </div>
                            </div>

                            <button type="submit" id="stay-book-submit-btn" class="w-full rounded-xl bg-gradient-to-r from-[#102b25] to-[#1d3c34] py-3.5 text-xs font-black uppercase tracking-widest text-[#d4af37] shadow-lg hover:shadow-xl hover:scale-[1.01] active:scale-[0.99] transition cursor-pointer border border-[#d4af37]/30">
                                ⚡ Instant Stay Reservation
                            </button>

                            <p class="text-center text-[10px] text-slate-400">
                                🛡️ Complete utility readiness · Standby generator & water tank verified
                            </p>
                        </form>
                    </div>
                @endif

                @if ($property->agent)
                    <div class="rounded-[2rem] border border-[#d9cab3] bg-[#fffaf4] p-6 shadow-sm">
                        <h3 class="text-lg font-black text-[#1d3c34]">Contact Assigned Agent</h3>
                        <p class="mt-1 text-xs text-slate-500">This request is sent exclusively to the listing agent.</p>

                        <div class="mt-4 flex items-center gap-3">
                            <img src="{{ $property->agent->photo_url }}" alt="{{ $property->agent->name }}" class="h-14 w-14 rounded-full object-cover ring-2 ring-[#00b53f]">
                            <div>
                                <h4 class="font-black text-[#1d3c34]">{{ $property->agent->name }}</h4>
                                <p class="text-xs text-slate-600">{{ $property->agent->phone ?: 'Phone available' }}</p>
                                @if ($property->agent->phone)
                                    <p class="text-[11px] font-bold text-[#00b53f]">Available on Call & WhatsApp</p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-5 space-y-2">
                            @if ($telPhone)
                                <a href="tel:{{ $telPhone }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#00b53f] py-2.5 text-sm font-bold text-white transition hover:bg-[#009b36]">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6.54 5C6.6 5.89 6.75 6.76 6.99 7.59L5.79 8.79C5.38 7.59 5.12 6.32 5.03 5H6.54ZM16.4 17.02C17.25 17.26 18.12 17.41 19 17.47V18.96C17.68 18.87 16.41 18.61 15.2 18.21L16.4 17.02ZM7.5 3H4C3.45 3 3 3.45 3 4C3 13.39 10.61 21 20 21C20.55 21 21 20.55 21 20V16.51C21 15.96 20.55 15.51 20 15.51C18.76 15.51 17.55 15.31 16.43 14.94C16.33 14.9 16.22 14.89 16.12 14.89C15.86 14.89 15.61 14.99 15.41 15.18L13.21 17.38C10.38 15.93 8.06 13.62 6.62 10.79L8.82 8.59C9.1 8.31 9.18 7.92 9.07 7.57C8.7 6.45 8.5 5.25 8.5 4C8.5 3.45 8.05 3 7.5 3Z"/></svg>
                                    <span>Call {{ $property->agent->name }}</span>
                                </a>
                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Hello ' . $property->agent->name . ', I am interested in your property: ' . $property->title) }}" target="_blank" rel="noopener noreferrer" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#25D366] py-2.5 text-sm font-bold text-white transition hover:bg-[#20ba59]">
                                    <span>WhatsApp Chat</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="rounded-[2rem] border border-[#d9cab3] bg-[#edf2ee] p-6 text-sm text-[#1d3c34]">
                    <h4 class="font-black">{{ 'Safety & Direct Deal' }}</h4>
                    <ul class="mt-2 space-y-1.5 text-xs text-slate-700">
                        <li>• Only inspect properties accompanied by the verified agent.</li>
                        <li>• Never send upfront cash or advance before contract signing.</li>
                        <li>• Branded agreements are generated automatically on GTM Real Estate.</li>
                        <li class="font-semibold text-red-700">• Any client who buys without an agreement given in this webapp, the company does not take responsibility.</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Jiji-style Quick Contact & Request Section --}}
        <div id="buy-request" class="mt-10 scroll-mt-24 rounded-[2rem] bg-[#1d3c34] p-8 text-[#f9f3e9] shadow-lg shadow-[#1d3c34]/20 lg:p-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#d9cab3]">{{ $property->type === 'rent' ? 'Rental Request' : 'Buy Request' }}</p>
                    <h2 class="mt-2 text-3xl font-black">Request “{{ $property->title }}”</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-[#ebefd9]">
                        Connect directly with {{ $property->agent ? $property->agent->name : 'our sales team' }} to arrange a viewing or submit an offer.
                    </p>
                </div>
                <div class="flex items-center gap-3 rounded-2xl bg-white/10 p-3 backdrop-blur-sm">
                    @if ($property->agent)
                        <img src="{{ $property->agent->photo_url }}" alt="{{ $property->agent->name }}" class="h-10 w-10 rounded-full object-cover ring-1 ring-white">
                        <div class="text-left text-xs">
                            <span class="block text-slate-300">Listing Agent</span>
                            <span class="font-bold text-white">{{ $property->agent->name }}</span>
                        </div>
                    @else
                        <span class="text-xs font-bold text-white">GTM Central Desk</span>
                    @endif
                </div>
            </div>

            <div class="mt-8 rounded-[1.5rem] bg-[#f8f3eb] p-6 text-[#1d3c34] md:p-8">
                @if (session('success'))
                    <div class="mb-5 rounded-xl border border-[#d7e5d2] bg-[#edf9ee] px-4 py-3 text-sm font-semibold text-[#214f3a]">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-[#c86b5c] bg-[#fef3f1] px-4 py-3 text-sm font-semibold text-[#a24339]">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('orders.store', $property) }}" class="grid gap-4 md:grid-cols-2">
                    @csrf

                    <div>
                        <label for="order-name" class="mb-1 block text-sm font-semibold">Your Name <span class="text-red-500">*</span></label>
                        <input id="order-name" name="name" type="text" value="{{ old('name') }}" required placeholder="e.g. Abebe Kebede" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label for="order-phone" class="mb-1 block text-sm font-semibold">Phone Number <span class="text-red-500">*</span></label>
                        <input id="order-phone" name="phone" type="tel" value="{{ old('phone') }}" required placeholder="e.g. +251 91 123 4567" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label for="order-email" class="mb-1 block text-sm font-semibold">Email Address <span class="text-red-500">*</span></label>
                        <input id="order-email" name="email" type="email" value="{{ old('email') }}" required placeholder="e.g. client@example.com" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label for="order-offer" class="mb-1 block text-sm font-semibold">{{ $property->type === 'rent' ? 'Proposed Rent (ETB / month, optional)' : 'Your Offer (ETB, optional)' }}</label>
                        <input id="order-offer" name="offer_amount" type="number" step="0.01" min="0" value="{{ old('offer_amount') }}" placeholder="Listed: ETB {{ number_format($property->price) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    @if ($property->type === 'rent')
                        <div>
                            <label for="order-lease-start" class="mb-1 block text-sm font-semibold">Preferred Move-in Date</label>
                            <input id="order-lease-start" name="lease_start" type="date" value="{{ old('lease_start') }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label for="order-lease-months" class="mb-1 block text-sm font-semibold">Lease Duration (Months)</label>
                            <input id="order-lease-months" name="lease_months" type="number" min="1" max="120" value="{{ old('lease_months', 12) }}" placeholder="e.g. 6, 12, 24" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <p class="mt-1 text-xs text-slate-500">Specify your desired duration in months</p>
                        </div>
                    @endif

                    <div class="md:col-span-2">
                        <label for="order-message" class="mb-1 block text-sm font-semibold">Message / Request Details</label>
                        <textarea id="order-message" name="message" rows="3" placeholder="Tell the agent when you are available for a visit or if you have specific questions." class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('message') }}</textarea>
                    </div>

                    <div class="md:col-span-2 flex flex-wrap gap-3">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                            Send Formal {{ $property->type === 'rent' ? 'Rental' : 'Buy' }} Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkinInput = document.getElementById('stay-checkin');
            const checkoutInput = document.getElementById('stay-checkout');
            const rateLabel = document.getElementById('breakdown-rate-label');
            const subtotalEl = document.getElementById('breakdown-subtotal');
            const serviceEl = document.getElementById('breakdown-service');
            const totalEl = document.getElementById('breakdown-total');

            if (!checkinInput || !checkoutInput) return;

            const nightlyRate = {{ (float) ($property->getNightlyRate()) }};
            const cleaningFee = {{ (float) ($property->getCleaningFee()) }};
            const customServiceFee = @json($property->getCustomServiceFee());

            const formatMoney = (amount) => window.GTM_Currency
                ? window.GTM_Currency.formatPrice(amount)
                : `ETB ${Math.round(amount).toLocaleString()}`;

            function setMoneyAmount(el, amount, suffix = '') {
                if (!el) return;
                el.dataset.basePrice = amount;
                el.dataset.priceSuffix = suffix;
                el.textContent = `${formatMoney(amount)}${suffix}`;
            }

            function updateStayPrice() {
                const inVal = checkinInput.value;
                const outVal = checkoutInput.value;
                if (!inVal || !outVal) return;

                const d1 = new Date(inVal);
                const d2 = new Date(outVal);
                const timeDiff = d2.getTime() - d1.getTime();
                const nights = Math.max(1, Math.round(timeDiff / (1000 * 3600 * 24)));

                if (nights > 0) {
                    const subtotal = nights * nightlyRate;
                    const serviceFee = customServiceFee !== null ? Number(customServiceFee) : Math.round(subtotal * 0.05);
                    const grandTotal = Math.round(subtotal + cleaningFee + serviceFee);

                    const nightsSuffix = ` × ${nights} ${nights === 1 ? 'night' : 'nights'}`;

                    setMoneyAmount(rateLabel, nightlyRate, nightsSuffix);
                    setMoneyAmount(subtotalEl, subtotal);
                    setMoneyAmount(serviceEl, serviceFee);
                    setMoneyAmount(totalEl, grandTotal);
                }
            }

            // Re-render the live breakdown whenever the visitor switches currency.
            window.addEventListener('currency-changed', updateStayPrice);

            checkinInput.addEventListener('change', function () {
                const nextDay = new Date(this.value);
                nextDay.setDate(nextDay.getDate() + 1);
                checkoutInput.min = nextDay.toISOString().split('T')[0];
                if (new Date(checkoutInput.value) <= new Date(this.value)) {
                    checkoutInput.value = nextDay.toISOString().split('T')[0];
                }
                updateStayPrice();
            });

            checkoutInput.addEventListener('change', updateStayPrice);

            // Module 4: Live variance calculation for formal offer price
            const offerInput = document.getElementById('offered-price-input');
            const varianceTag = document.getElementById('offer-variance-tag');
            const askingPrice = {{ (float) $property->price }};

            if (offerInput && varianceTag && askingPrice > 0) {
                offerInput.addEventListener('input', function() {
                    const val = parseFloat(this.value) || 0;
                    const diff = val - askingPrice;
                    const pct = ((diff / askingPrice) * 100).toFixed(1);
                    if (diff === 0) {
                        varianceTag.textContent = `Full asking price (ETB ${askingPrice.toLocaleString()})`;
                        varianceTag.className = 'mt-1 text-[11px] font-bold text-slate-500';
                    } else if (diff < 0) {
                        varianceTag.textContent = `${Math.abs(pct)}% below asking price (ETB ${Math.abs(diff).toLocaleString()} reduction)`;
                        varianceTag.className = 'mt-1 text-[11px] font-bold text-amber-700';
                    } else {
                        varianceTag.textContent = `${pct}% above asking price (ETB ${diff.toLocaleString()} premium)`;
                        varianceTag.className = 'mt-1 text-[11px] font-bold text-emerald-700';
                    }
                });
            }
        });
    </script>

    @include('properties.partials.payment-modal')
    @include('properties.partials.upload-document-modal')
    @include('properties.partials.report-modal')
    @include('properties.partials.collection-modal')
@endsection
