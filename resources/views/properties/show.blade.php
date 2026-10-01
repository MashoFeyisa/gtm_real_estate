@extends('app')

@section('title', $property->title)
@section('description', Str::limit(strip_tags($property->description ?: $property->title), 160))

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
            @if ($property->agent)
                <a href="{{ route('agents.show', $property->agent) }}" class="inline-flex items-center gap-2 text-sm font-bold text-[#1d3c34] hover:text-[#2d5d4d]">
                    <span>View all listings by {{ $property->agent->name }}</span>
                    <span class="rounded-full bg-[#1d3c34] px-2 py-0.5 text-xs text-white">{{ $agentListingsCount }}</span>
                    &rarr;
                </a>
            @endif
        </div>

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
                                <button type="button" data-slider-prev aria-label="Previous photo" class="pointer-events-auto flex h-10 w-10 items-center justify-center rounded-full bg-black/70 text-[#e7d8b7] backdrop-blur-md opacity-0 group-hover/show-slider:opacity-100 transition hover:bg-[#102b25] hover:text-white border border-white/20 active:scale-95 cursor-pointer">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                </button>
                                <button type="button" data-slider-next aria-label="Next photo" class="pointer-events-auto flex h-10 w-10 items-center justify-center rounded-full bg-black/70 text-[#e7d8b7] backdrop-blur-md opacity-0 group-hover/show-slider:opacity-100 transition hover:bg-[#102b25] hover:text-white border border-white/20 active:scale-95 cursor-pointer">
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
                            <span>{{ ucfirst($property->property_category ?? $property->category ?? 'Property') }}</span>
                            <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-[#2d5d4d]">Active Listing</span>
                        </div>

                        <h1 class="mt-4 text-3xl font-black tracking-tight text-[#1d3c34] sm:text-4xl">{{ $property->title }}</h1>
                        <p class="mt-2 flex items-center gap-1.5 text-base text-slate-600">
                            <svg class="h-4 w-4 text-[#587165]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $property->city ?? 'Addis Ababa' }} · {{ $property->address ?? 'Prime Location' }}</span>
                        </p>

                        <div class="mt-5 rounded-2xl bg-[#fffaf2] p-4 border border-[#ecd9be]">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#587165]">Price</p>
                            <div class="mt-1 flex flex-wrap items-baseline gap-2">
                                <span class="text-3xl font-black text-[#1d3c34] sm:text-4xl">ETB {{ number_format($property->price) }}</span>
                                @if ($property->type === 'rent')
                                    <span class="text-sm font-bold text-[#587165]">/ month</span>
                                @endif
                            </div>
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
                                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#60716c]">Area (ካሬ)</p>
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
                            <a href="#buy-request" class="inline-flex flex-1 min-w-[140px] items-center justify-center gap-2 rounded-xl bg-[#1d3c34] px-4 py-3 text-sm font-bold text-white shadow-md shadow-[#1d3c34]/20 transition hover:bg-[#254d43]">
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
                            <span class="font-bold text-[#1d3c34]">{{ $property->area }} sqm (ካሬ)</span>
                        </div>
                        <div class="rounded-xl bg-[#fffaf2] p-3 border border-[#f0e4cf]">
                            <span class="block text-xs uppercase text-[#60716c]">Property Purpose</span>
                            <span class="font-bold text-[#1d3c34]">{{ $property->type === 'rent' ? 'For Rent' : 'For Sale' }}</span>
                        </div>
                        <div class="rounded-xl bg-[#fffaf2] p-3 border border-[#f0e4cf]">
                            <span class="block text-xs uppercase text-[#60716c]">Category</span>
                            <span class="font-bold text-[#1d3c34]">{{ ucfirst($property->property_category ?? $property->category ?? 'Home') }}</span>
                        </div>
                        <div class="rounded-xl bg-[#fffaf2] p-3 border border-[#f0e4cf]">
                            <span class="block text-xs uppercase text-[#60716c]">Negotiation</span>
                            <span class="font-bold text-[#1d3c34]">Open to Offers</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Safety & Direct Agent Card sidebar --}}
            <div class="space-y-6">
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
@endsection
