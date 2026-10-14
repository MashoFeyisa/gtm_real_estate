@extends('dashboards.layout')

@section('title', 'Buyer Dashboard')
@section('portal_name', 'Buyer Workspace')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>

    @if (auth()->user()->hasAnyPermission(['properties.save', 'properties.favorite']))
        <a href="#collections" data-target-section="collections" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📁</span>
                <span>Collections</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $collections->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['properties.compare', 'properties.view']))
        <a href="#compare" data-target-section="compare" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>⚖️</span>
                <span>Compare Matrix</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $comparedProperties->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['searches.save', 'properties.save']))
        <a href="#saved-searches" data-target-section="saved-searches" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🔔</span>
                <span>Saved Searches</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $savedSearches->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['inquiries.view', 'inquiries.create', 'messages.view']))
        <a href="#inquiries" data-target-section="inquiries" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>💬</span>
                <span>Inquiries &amp; Messages</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $inquiries->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['viewings.view', 'viewings.create']))
        <a href="#viewings" data-target-section="viewings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📅</span>
                <span>Viewings</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $viewings->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['offers.view', 'offers.create']))
        <a href="#offers" data-target-section="offers" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🤝</span>
                <span>My Offers</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $offers->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['reservations.view', 'reservations.create']))
        <a href="#reservations" data-target-section="reservations" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🏢</span>
                <span>Unit Reservations</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $reservations->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['documents.view', 'documents.create']))
        <a href="#documents" data-target-section="documents" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🗄️</span>
                <span>Document Vault</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $documents->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['transactions.view', 'orders.view']))
        <a href="#deals" data-target-section="deals" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📜</span>
                <span>Transaction Tracker</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $deals->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['properties.save', 'properties.favorite']))
        <a href="#saved-properties" data-target-section="saved-properties" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>❤️</span>
                <span>Saved Properties</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $likedProperties->count() + $favoritedProperties->count() }}</span>
        </a>
    @endif
@endsection

@section('content')
<div class="space-y-8">
    {{-- Alerts & Session Feedback --}}
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800 flex items-center justify-between shadow-xs">
            <span>✅ {{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-800 flex items-center justify-between shadow-xs">
            <span>⚠️ {{ session('error') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">&times;</button>
        </div>
    @endif

    {{-- Top Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- 1. Property Collections Organizer --}}
    <div id="collections" data-section="collections" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#e7ddca] pb-4 dark:border-slate-800">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34] dark:text-white">Custom Property Collections</h2>
                <p class="text-xs text-slate-500">Organize prospective homes into curated collections (e.g. Bole Luxury, Family Villas).</p>
            </div>
            <button type="button" onclick="document.getElementById('new-collection-form').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition">
                + New Collection
            </button>
        </div>

        {{-- New Collection Inline Form --}}
        <div id="new-collection-form" class="hidden mt-4 rounded-2xl border border-[#d4af37] bg-[#fbf8f3] p-5 dark:border-amber-700 dark:bg-slate-800">
            <h3 class="text-sm font-black text-[#1d3c34] mb-3 dark:text-white">Create New Collection</h3>
            <form method="POST" action="{{ route('collections.store') }}" class="grid gap-4 sm:grid-cols-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Collection Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Kazanchis Penthouses" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Description</label>
                    <input type="text" name="description" placeholder="Short note about properties in this group" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                </div>
                <div class="sm:col-span-3 flex justify-end">
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white hover:bg-[#254d43]">
                        Create Collection
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 space-y-6">
            @forelse ($collections as $collection)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5 dark:border-slate-800 dark:bg-slate-800/60">
                    <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3 dark:border-slate-700">
                        <div>
                            <h3 class="text-base font-black text-[#1d3c34] dark:text-white">{{ $collection->name }}</h3>
                            @if ($collection->description)
                                <p class="text-xs text-slate-500 mt-0.5">{{ $collection->description }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full bg-[#1d3c34]/10 px-2.5 py-0.5 text-xs font-bold text-[#1d3c34] dark:bg-white/10 dark:text-white">
                                {{ $collection->properties->count() }} {{ Str::plural('property', $collection->properties->count()) }}
                            </span>
                            <form method="POST" action="{{ route('collections.destroy', $collection) }}" onsubmit="return confirm('Delete this collection?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800">Delete</button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        @forelse ($collection->properties as $prop)
                            <div class="rounded-xl border border-[#e7ddca] bg-white p-3 dark:border-slate-700 dark:bg-slate-900 flex flex-col justify-between">
                                <div>
                                    <h4 class="text-xs font-bold text-[#1d3c34] line-clamp-1 dark:text-white">{{ $prop->title }}</h4>
                                    <p class="text-[11px] font-bold text-emerald-800 mt-1">{{ number_format($prop->price) }} ETB</p>
                                </div>
                                <div class="mt-3 flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800">
                                    <a href="{{ route('properties.show', $prop->slug) }}" class="text-[11px] font-bold text-[#1d3c34] hover:underline dark:text-emerald-400">View Listing</a>
                                    <form method="POST" action="{{ route('collections.toggleProperty', [$collection, $prop]) }}">
                                        @csrf
                                        <button type="submit" class="text-[11px] text-rose-600 hover:text-rose-800 font-bold" title="Remove from collection">Remove</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="col-span-full text-xs text-slate-400 py-2">No properties added to this collection yet. Browse listings and add them.</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-[#e7ddca] p-8 text-center dark:border-slate-800">
                    <p class="text-sm font-bold text-slate-600 dark:text-slate-400">No custom collections created yet.</p>
                    <p class="text-xs text-slate-400 mt-1">Group and compare prospective properties by creating your first collection above.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- 2. Property Comparison Matrix --}}
    <div id="compare" data-section="compare" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#e7ddca] pb-4 dark:border-slate-800">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34] dark:text-white">Side-by-Side Property Comparison Matrix</h2>
                <p class="text-xs text-slate-500">Compare up to 4 selected properties across price, specifications, location, and amenities.</p>
            </div>
            @if ($comparedProperties->isNotEmpty())
                <form method="POST" action="{{ route('properties.compare.clear') }}">
                    @csrf
                    <button type="submit" class="rounded-full border border-rose-300 px-4 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-50 transition">
                        Clear All Comparisons
                    </button>
                </form>
            @endif
        </div>

        @if ($comparedProperties->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-[#e7ddca] p-10 text-center dark:border-slate-800">
                <span class="text-3xl">⚖️</span>
                <h3 class="mt-2 text-sm font-black text-[#1d3c34] dark:text-white">No properties queued for comparison</h3>
                <p class="mt-1 text-xs text-slate-400">Click "Compare" on any property cards while browsing to inspect them side-by-side.</p>
                <a href="{{ route('properties.index') }}" class="mt-4 inline-block rounded-full bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white hover:bg-[#254d43]">
                    Browse Marketplace Listings &rarr;
                </a>
            </div>
        @else
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-[#e7ddca] text-xs dark:divide-slate-800">
                    <thead>
                        <tr class="bg-[#fbf9f5] dark:bg-slate-800">
                            <th class="py-3 px-4 text-left font-black text-[#587165] dark:text-slate-300 w-48">Feature</th>
                            @foreach ($comparedProperties as $prop)
                                <th class="py-3 px-4 text-left font-black text-[#1d3c34] dark:text-white min-w-[200px]">
                                    <div class="flex items-center justify-between">
                                        <span class="truncate">{{ $prop->title }}</span>
                                        <form method="POST" action="{{ route('properties.compare.toggle', $prop) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 font-bold ml-2" title="Remove">&times;</button>
                                        </form>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7ddca] dark:divide-slate-800">
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-500">Asking Price</td>
                            @foreach ($comparedProperties as $prop)
                                <td class="py-3 px-4 font-black text-emerald-800 text-sm dark:text-emerald-400">{{ number_format($prop->price) }} ETB</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-500">Category &amp; Type</td>
                            @foreach ($comparedProperties as $prop)
                                <td class="py-3 px-4 font-semibold">{{ ucfirst($prop->type) }} ({{ ucfirst(str_replace('_', ' ', $prop->property_category ?? 'Residential')) }})</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-500">Location</td>
                            @foreach ($comparedProperties as $prop)
                                <td class="py-3 px-4">{{ $prop->subcity ? $prop->subcity . ', ' : '' }}{{ $prop->city }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-500">Bedrooms &amp; Baths</td>
                            @foreach ($comparedProperties as $prop)
                                <td class="py-3 px-4">{{ $prop->bedrooms ?? '—' }} Beds &bull; {{ $prop->bathrooms ?? '—' }} Baths</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-500">Gross Floor Area</td>
                            @foreach ($comparedProperties as $prop)
                                <td class="py-3 px-4">{{ $prop->area_sqm ? number_format($prop->area_sqm) . ' m²' : '—' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-500">Verification Status</td>
                            @foreach ($comparedProperties as $prop)
                                <td class="py-3 px-4">
                                    <span class="rounded-full bg-emerald-100 text-emerald-800 px-2 py-0.5 text-[10px] font-bold">
                                        {{ ucfirst($prop->verification_status ?? 'Verified') }}
                                    </span>
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-500">Market Estimate</td>
                            @foreach ($comparedProperties as $prop)
                                <td class="py-3 px-4 text-slate-500 italic">Asking-price indicator / platform estimate</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-500">Actions</td>
                            @foreach ($comparedProperties as $prop)
                                <td class="py-3 px-4">
                                    <a href="{{ route('properties.show', $prop->slug) }}" class="rounded-full bg-[#1d3c34] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#254d43] inline-block">
                                        Open Listing &rarr;
                                    </a>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- 3. Saved Searches & Alerts --}}
    <div id="saved-searches" data-section="saved-searches" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#e7ddca] pb-4 dark:border-slate-800">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34] dark:text-white">Saved Searches &amp; Alerts</h2>
                <p class="text-xs text-slate-500">Automated price drop and new listing notifications matching your criteria.</p>
            </div>
            <button type="button" onclick="document.getElementById('saved-search-form').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition">
                + New Search Alert
            </button>
        </div>

        {{-- New Saved Search Form --}}
        <div id="saved-search-form" class="hidden mt-4 rounded-2xl border border-[#d4af37] bg-[#fbf8f3] p-5 dark:border-amber-700 dark:bg-slate-800">
            <h3 class="text-sm font-black text-[#1d3c34] mb-3 dark:text-white">Create Search Alert</h3>
            <form method="POST" action="{{ route('buyer.saved-searches.store') }}" class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Alert Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Bole Apartments under 15M" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">City / District</label>
                    <input type="text" name="city" placeholder="Addis Ababa, Bole" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Max Budget (ETB)</label>
                    <input type="number" name="max_price" placeholder="15000000" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Frequency</label>
                    <select name="alert_frequency" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                        <option value="instant">Instant Notification</option>
                        <option value="daily" selected>Daily Digest</option>
                        <option value="weekly">Weekly</option>
                    </select>
                </div>
                <div class="sm:col-span-2 md:col-span-4 flex justify-end">
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white hover:bg-[#254d43]">
                        Save Alert
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($savedSearches as $search)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-4 flex items-start justify-between dark:border-slate-800 dark:bg-slate-800">
                    <div>
                        <h4 class="font-bold text-sm text-[#1d3c34] dark:text-white">{{ $search->title }}</h4>
                        <p class="text-xs text-slate-500 mt-1">
                            @if (!empty($search->criteria['city'])) City: {{ $search->criteria['city'] }} &bull; @endif
                            @if (!empty($search->criteria['max_price'])) Max: {{ number_format($search->criteria['max_price']) }} ETB @endif
                        </p>
                        <span class="inline-block mt-2 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5">
                            {{ ucfirst($search->alert_frequency) }} Alert
                        </span>
                    </div>
                    <form method="POST" action="{{ route('buyer.saved-searches.destroy', $search) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-bold" title="Delete Alert">&times;</button>
                    </form>
                </div>
            @empty
                <p class="col-span-3 text-xs text-slate-400 py-4 text-center">No saved search alerts yet. Create one to receive automated updates.</p>
            @endforelse
        </div>
    </div>

    {{-- 4. Inquiries & Direct Messages --}}
    <div id="inquiries" data-section="inquiries" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3 dark:border-slate-800 dark:text-white">My Inquiries &amp; Seller Communication</h2>
        <div class="mt-4 space-y-4">
            @forelse ($inquiries as $inquiry)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5 dark:border-slate-800 dark:bg-slate-800">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[#e7ddca] pb-3 dark:border-slate-700">
                        <div>
                            <span class="font-mono text-xs font-bold text-[#587165] dark:text-slate-400">#INQ-{{ $inquiry->id }}</span>
                            <h4 class="font-bold text-sm text-[#1d3c34] dark:text-white">{{ $inquiry->property?->title ?? 'General Inquiry' }}</h4>
                            <p class="text-xs text-slate-500">Contacted: {{ $inquiry->agent?->name ?? 'GTM Consulting Desk' }} &bull; {{ $inquiry->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-bold self-start sm:self-auto {{ match ($inquiry->status) { 'resolved' => 'bg-emerald-100 text-emerald-800', 'contacted' => 'bg-blue-100 text-blue-800', default => 'bg-amber-100 text-amber-800' } }}">
                            {{ ucfirst($inquiry->status) }}
                        </span>
                    </div>

                    <div class="mt-3 text-xs text-slate-700 whitespace-pre-line bg-white rounded-xl p-3 border border-slate-200 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-200">
                        {{ $inquiry->message }}
                    </div>

                    {{-- Reply Form --}}
                    <form method="POST" action="{{ route('buyer.inquiries.reply', $inquiry) }}" class="mt-3 flex gap-2">
                        @csrf
                        <input type="text" name="message" required placeholder="Type follow-up reply to seller..." class="flex-1 rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                        <button type="submit" class="rounded-xl bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43]">
                            Send Reply
                        </button>
                    </form>
                </div>
            @empty
                <p class="text-xs text-slate-400 py-6 text-center">No inquiries sent yet. Contact sellers directly from property listing pages.</p>
            @endforelse
        </div>
    </div>

    {{-- 5. Scheduled Viewings & Appointments --}}
    <div id="viewings" data-section="viewings" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3 dark:border-slate-800 dark:text-white">Scheduled Viewings</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($viewings as $viewing)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5 flex flex-col justify-between dark:border-slate-800 dark:bg-slate-800">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-[#587165] dark:text-slate-400">{{ $viewing->viewing_reference }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ match ($viewing->status) { 'confirmed' => 'bg-emerald-100 text-emerald-800', 'completed' => 'bg-blue-100 text-blue-800', 'cancelled' => 'bg-rose-100 text-rose-800', default => 'bg-amber-100 text-amber-800' } }}">
                                {{ ucfirst($viewing->status) }}
                            </span>
                        </div>
                        <h4 class="mt-2 font-bold text-sm text-[#1d3c34] line-clamp-1 dark:text-white">{{ $viewing->property?->title ?? 'Viewing Appointment' }}</h4>
                        <p class="text-xs text-slate-500 mt-1">📅 {{ $viewing->viewing_date?->format('M d, Y') }} &bull; ⏰ {{ ucfirst(str_replace('_', ' ', $viewing->viewing_time ?? 'Flexible')) }}</p>
                        <p class="text-xs text-slate-600 mt-1 dark:text-slate-300">Agent: {{ $viewing->agent?->user?->name ?? 'Assigned Consultant' }}</p>
                    </div>

                    @if ($viewing->status !== 'cancelled' && $viewing->status !== 'completed')
                        <div class="mt-4 pt-3 border-t border-[#e7ddca] flex items-center justify-between gap-2 dark:border-slate-700">
                            <button type="button" onclick="document.getElementById('reschedule-{{ $viewing->id }}').classList.toggle('hidden')" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                                Reschedule
                            </button>
                            <form method="POST" action="{{ route('buyer.viewings.cancel', $viewing) }}" onsubmit="return confirm('Cancel this inspection appointment?')">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800">
                                    Cancel
                                </button>
                            </form>
                        </div>

                        {{-- Reschedule Dropdown Form --}}
                        <div id="reschedule-{{ $viewing->id }}" class="hidden mt-3 rounded-xl border border-[#d9cab3] bg-white p-3 dark:bg-slate-900 dark:border-slate-700">
                            <form method="POST" action="{{ route('buyer.viewings.reschedule', $viewing) }}" class="space-y-2">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400">New Date</label>
                                    <input type="date" name="viewing_date" required min="{{ date('Y-m-d') }}" value="{{ $viewing->viewing_date?->format('Y-m-d') }}" class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400">Time Window</label>
                                    <select name="viewing_time" required class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                                        <option value="morning_09_11">Morning (09:00 - 11:00)</option>
                                        <option value="afternoon_14_16">Afternoon (14:00 - 16:00)</option>
                                        <option value="evening_17_19">Evening (17:00 - 19:00)</option>
                                    </select>
                                </div>
                                <button type="submit" class="w-full rounded-lg bg-[#1d3c34] py-1 text-xs font-bold text-white hover:bg-[#254d43]">
                                    Confirm Reschedule
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-xs text-slate-400 py-4 text-center">No viewings scheduled yet.</p>
            @endforelse
        </div>
    </div>

    {{-- 6. Offers & Multi-Round Negotiations --}}
    <div id="offers" data-section="offers" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3 dark:border-slate-800 dark:text-white">My Formal Offers &amp; Negotiations</h2>
        <div class="mt-4 space-y-4">
            @forelse ($offers as $offer)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5 dark:border-slate-800 dark:bg-slate-800">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#e7ddca] pb-3 dark:border-slate-700">
                        <div>
                            <span class="font-mono text-xs font-bold text-[#587165] dark:text-slate-400">{{ $offer->offer_reference }}</span>
                            <h4 class="font-bold text-sm text-[#1d3c34] dark:text-white">{{ $offer->property?->title ?? 'Property Offer' }}</h4>
                            <p class="text-xs text-slate-500">Initial: {{ number_format($offer->offered_price) }} ETB &bull; Submitted {{ $offer->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ match ($offer->status) { 'accepted' => 'bg-emerald-100 text-emerald-800', 'rejected' => 'bg-rose-100 text-rose-800', 'countered' => 'bg-amber-100 text-amber-800', 'withdrawn' => 'bg-slate-200 text-slate-700', default => 'bg-blue-100 text-blue-800' } }}">
                                {{ ucfirst(str_replace('_', ' ', $offer->status)) }}
                            </span>
                            @if (!in_array($offer->status, ['accepted', 'completed', 'withdrawn'], true))
                                <form method="POST" action="{{ route('buyer.offers.withdraw', $offer) }}" onsubmit="return confirm('Withdraw this offer?')">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="rounded-full border border-rose-300 px-3 py-1 text-xs font-bold text-rose-700 hover:bg-rose-50">
                                        Withdraw
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    {{-- Negotiation Multi-Round History --}}
                    <div class="mt-4">
                        <h5 class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Negotiation Rounds ({{ $offer->negotiations->count() }})</h5>
                        <div class="mt-2 space-y-2 border-l-2 border-[#1d3c34]/20 pl-4 ml-1">
                            @forelse ($offer->negotiations as $round)
                                <div class="text-xs">
                                    <span class="font-bold {{ $round->sender_type === 'buyer' ? 'text-blue-700 dark:text-blue-400' : 'text-amber-700 dark:text-amber-400' }}">
                                        {{ ucfirst($round->sender_type) }}:
                                    </span>
                                    <span class="font-black text-emerald-800 dark:text-emerald-400">{{ number_format($round->amount) }} ETB</span>
                                    <span class="text-slate-400 text-[10px] ml-1">({{ $round->created_at->format('M d, H:i') }})</span>
                                    @if ($round->message)
                                        <p class="text-slate-600 mt-0.5 italic dark:text-slate-300">"{{ $round->message }}"</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 italic">No counter rounds yet. Awaiting seller response.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Counter Action Bar if Countered --}}
                    @if ($offer->status === 'countered')
                        <div class="mt-4 pt-3 border-t border-[#e7ddca] dark:border-slate-700 flex flex-wrap items-center gap-3">
                            <form method="POST" action="{{ route('offers.accept', $offer) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="rounded-full bg-emerald-700 px-4 py-1.5 text-xs font-bold text-white hover:bg-emerald-800">
                                    Accept Counter ({{ number_format($offer->current_counter_amount) }} ETB)
                                </button>
                            </form>
                            <button type="button" onclick="document.getElementById('counter-form-{{ $offer->id }}').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#254d43]">
                                Counter Again
                            </button>
                        </div>

                        <div id="counter-form-{{ $offer->id }}" class="hidden mt-3 rounded-xl border border-[#d9cab3] bg-white p-3 dark:bg-slate-900 dark:border-slate-700">
                            <form method="POST" action="{{ route('offers.counter', $offer) }}" class="flex gap-2">
                                @csrf
                                <input type="number" name="counter_amount" required placeholder="Your Counter Amount (ETB)" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs w-48 dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                                <input type="text" name="message" placeholder="Optional rationale note" class="flex-1 rounded-lg border border-slate-300 px-3 py-1.5 text-xs dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                                <button type="submit" class="rounded-lg bg-[#1d3c34] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#254d43]">
                                    Send Counter
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="py-6 text-center text-xs text-slate-400">No formal offers submitted yet. Make offers directly on property listings.</p>
            @endforelse
        </div>
    </div>

    {{-- 7. Unit Reservations & Off-Plan Holdings --}}
    <div id="reservations" data-section="reservations" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3 dark:border-slate-800 dark:text-white">Off-Plan Unit Reservations</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($reservations as $res)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5 flex flex-col justify-between dark:border-slate-800 dark:bg-slate-800">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-[#587165] dark:text-slate-400">{{ $res->reservation_reference }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ match ($res->status) { 'confirmed' => 'bg-emerald-100 text-emerald-800', 'cancelled' => 'bg-rose-100 text-rose-800', 'expired' => 'bg-slate-200 text-slate-700', default => 'bg-amber-100 text-amber-800' } }}">
                                {{ ucfirst($res->status) }}
                            </span>
                        </div>
                        <h4 class="mt-2 font-bold text-sm text-[#1d3c34] dark:text-white">{{ $res->project?->name ?? 'Development Project' }}</h4>
                        <p class="text-xs text-slate-600 mt-1 dark:text-slate-300">Unit: {{ $res->unit?->unit_number ?? 'Assigned Unit' }} &bull; Floor {{ $res->unit?->floor_number ?? '—' }}</p>
                        @if ($res->unit?->price)
                            <p class="text-xs font-bold text-emerald-800 mt-1 dark:text-emerald-400">Unit Price: {{ number_format($res->unit->price) }} ETB</p>
                        @endif
                        <p class="text-[10px] text-slate-500 mt-1">Booked: {{ $res->created_at->format('M d, Y') }}</p>
                    </div>

                    @if ($res->status === 'pending' || $res->status === 'confirmed')
                        <div class="mt-4 pt-3 border-t border-[#e7ddca] flex justify-end dark:border-slate-700">
                            <form method="POST" action="{{ route('buyer.reservations.cancel', $res) }}" onsubmit="return confirm('Cancel this unit reservation?')">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800">
                                    Release Unit &amp; Cancel
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-xs text-slate-400 py-4 text-center">No active off-plan reservations.</p>
            @endforelse
        </div>
    </div>

    {{-- 8. Document Vault --}}
    <div id="documents" data-section="documents" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#e7ddca] pb-4 dark:border-slate-800">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34] dark:text-white">Buyer Document Vault</h2>
                <p class="text-xs text-slate-500">Secure private vault for financial pre-approvals, national ID, and proof of funds.</p>
            </div>
            <button type="button" onclick="document.getElementById('upload-document-form').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition">
                + Upload Document
            </button>
        </div>

        {{-- Upload Document Form --}}
        <div id="upload-document-form" class="hidden mt-4 rounded-2xl border border-[#d4af37] bg-[#fbf8f3] p-5 dark:border-amber-700 dark:bg-slate-800">
            <h3 class="text-sm font-black text-[#1d3c34] mb-3 dark:text-white">Upload New Document</h3>
            <form method="POST" action="{{ route('buyer.documents.upload') }}" enctype="multipart/form-data" class="grid gap-4 sm:grid-cols-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Document Title *</label>
                    <input type="text" name="title" required placeholder="e.g. CBE Bank Proof of Funds" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Document Type *</label>
                    <select name="document_type" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                        <option value="proof_of_funds">Proof of Funds / Bank Statement</option>
                        <option value="national_id">National ID / Passport</option>
                        <option value="employment_letter">Employment / Income Verification</option>
                        <option value="tax_id">TIN / Tax Identification</option>
                        <option value="pre_approval">Mortgage Pre-Approval Letter</option>
                        <option value="other">Other Supporting Document</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">File (PDF, PNG, JPG, DOCX up to 10MB) *</label>
                    <input type="file" name="document_file" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                </div>
                <div class="sm:col-span-3 flex justify-end">
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white hover:bg-[#254d43]">
                        Upload to Private Vault
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e7ddca] text-xs dark:divide-slate-800">
                <thead>
                    <tr class="bg-[#fbf9f5] dark:bg-slate-800 text-left font-black uppercase text-[#587165] dark:text-slate-300">
                        <th class="py-3 px-4">Document Title</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Access Level</th>
                        <th class="py-3 px-4">File Size</th>
                        <th class="py-3 px-4">Uploaded</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca] dark:divide-slate-800">
                    @forelse ($documents as $doc)
                        <tr class="hover:bg-[#fcfaf7] dark:hover:bg-slate-800/40">
                            <td class="py-3 px-4 font-bold text-[#1d3c34] dark:text-white">{{ $doc->title }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-600 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</td>
                            <td class="py-3 px-4">
                                <span class="rounded-full bg-slate-100 text-slate-700 px-2 py-0.5 text-[10px] font-bold dark:bg-slate-800 dark:text-slate-300">
                                    🔒 {{ ucfirst($doc->access_level) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $doc->file_size ? number_format($doc->file_size / 1024, 1) . ' KB' : '—' }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $doc->created_at->format('M d, Y') }}</td>
                            <td class="py-3 px-4 text-right space-x-2">
                                @if ($doc->file_path)
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-xs font-bold text-[#1d3c34] hover:underline dark:text-emerald-400">Download</a>
                                @endif
                                <form method="POST" action="{{ route('buyer.documents.destroy', $doc) }}" class="inline" onsubmit="return confirm('Delete this document?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-xs text-slate-400">Your document vault is currently empty. Upload IDs and pre-approval letters above.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 9. Transaction Tracker & Closing Timeline --}}
    <div id="deals" data-section="deals" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3 dark:border-slate-800 dark:text-white">Transaction Tracker &amp; Closing Timeline</h2>
        <div class="mt-4 space-y-6">
            @forelse ($deals as $deal)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5 dark:border-slate-800 dark:bg-slate-800">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[#e7ddca] pb-3 dark:border-slate-700">
                        <div>
                            <span class="font-mono text-xs font-bold text-[#587165] dark:text-slate-400">{{ $deal->order_number ?? '#DEAL-' . $deal->id }}</span>
                            <h4 class="font-bold text-sm text-[#1d3c34] dark:text-white">{{ $deal->property?->title ?? 'Property Acquisition' }}</h4>
                            <p class="text-xs text-slate-500">Agent: {{ $deal->agent?->name ?? 'Licensed Realtor' }} &bull; Total Value: {{ number_format($deal->offer_amount ?? 0) }} ETB</p>
                        </div>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ match ($deal->status) { 'completed' => 'bg-emerald-100 text-emerald-800', 'cancelled' => 'bg-rose-100 text-rose-800', default => 'bg-blue-100 text-blue-800' } }}">
                            {{ ucfirst(str_replace('_', ' ', $deal->status ?? 'In Progress')) }}
                        </span>
                    </div>

                    {{-- 5-Stage Visual Stepper --}}
                    @php
                        $stage = match ($deal->status) {
                            'completed' => 5,
                            'paid', 'escrow_funded' => 4,
                            'agreement_signed' => 3,
                            'under_review' => 2,
                            default => 1,
                        };
                    @endphp
                    <div class="mt-6">
                        <div class="grid grid-cols-5 gap-2 text-center">
                            <div class="flex flex-col items-center">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black {{ $stage >= 1 ? 'bg-emerald-700 text-white' : 'bg-slate-200 text-slate-500' }}">1</span>
                                <span class="mt-1 text-[10px] font-bold {{ $stage >= 1 ? 'text-[#1d3c34] dark:text-emerald-400' : 'text-slate-400' }}">Offer Accepted</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black {{ $stage >= 2 ? 'bg-emerald-700 text-white' : 'bg-slate-200 text-slate-500' }}">2</span>
                                <span class="mt-1 text-[10px] font-bold {{ $stage >= 2 ? 'text-[#1d3c34] dark:text-emerald-400' : 'text-slate-400' }}">Due Diligence</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black {{ $stage >= 3 ? 'bg-emerald-700 text-white' : 'bg-slate-200 text-slate-500' }}">3</span>
                                <span class="mt-1 text-[10px] font-bold {{ $stage >= 3 ? 'text-[#1d3c34] dark:text-emerald-400' : 'text-slate-400' }}">Agreement Signed</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black {{ $stage >= 4 ? 'bg-emerald-700 text-white' : 'bg-slate-200 text-slate-500' }}">4</span>
                                <span class="mt-1 text-[10px] font-bold {{ $stage >= 4 ? 'text-[#1d3c34] dark:text-emerald-400' : 'text-slate-400' }}">Bank Escrow</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black {{ $stage >= 5 ? 'bg-emerald-700 text-white' : 'bg-slate-200 text-slate-500' }}">5</span>
                                <span class="mt-1 text-[10px] font-bold {{ $stage >= 5 ? 'text-[#1d3c34] dark:text-emerald-400' : 'text-slate-400' }}">Title Handover</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-xs text-slate-400">No active acquisition transactions in progress.</p>
            @endforelse
        </div>
    </div>

    {{-- 10. Saved Properties --}}
    <div id="saved-properties" data-section="saved-properties" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3 dark:border-slate-800 dark:text-white">Saved &amp; Favorited Properties</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @php $saved = $likedProperties->merge($favoritedProperties)->unique('property_id'); @endphp
            @forelse ($saved as $item)
                @if ($item->property)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] overflow-hidden flex flex-col dark:border-slate-800 dark:bg-slate-800">
                        <div class="h-36 bg-slate-200 relative dark:bg-slate-700">
                            @if ($item->property->cover_image_url)
                                <img src="{{ $item->property->cover_image_url }}" alt="{{ $item->property->title }}" class="h-full w-full object-cover">
                            @else
                                <div class="h-full w-full flex items-center justify-center text-slate-400 text-xs">No image</div>
                            @endif
                            <span class="absolute top-2 right-2 rounded-full bg-[#1d3c34]/90 px-2.5 py-0.5 text-[10px] font-bold text-white">
                                {{ number_format($item->property->price) }} ETB
                            </span>
                        </div>
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="font-bold text-sm text-[#1d3c34] line-clamp-1 dark:text-white">{{ $item->property->title }}</h4>
                                <p class="text-xs text-slate-500 mt-1">📍 {{ $item->property->city }}, {{ $item->property->subcity ?? '' }}</p>
                            </div>
                            <div class="mt-4 pt-2 border-t border-[#e7ddca] flex items-center justify-between dark:border-slate-700">
                                <span class="text-[10px] text-slate-500">{{ ucfirst($item->property->type) }}</span>
                                <a href="{{ route('properties.show', $item->property->slug) }}" class="text-xs font-bold text-[#1d3c34] hover:underline dark:text-emerald-400">View &rarr;</a>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <p class="col-span-3 text-xs text-slate-400 py-4 text-center">No saved properties. Browse listings and click the heart icon to save.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
