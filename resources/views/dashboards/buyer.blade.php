@extends('dashboards.layout')

@section('title', 'Buyer Dashboard')
@section('portal_name', 'Buyer Workspace')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if (auth()->user()->hasAnyPermission(['searches.save', 'properties.save', 'searches.view']))
        <a href="#saved-searches" data-target-section="saved-searches" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🔔</span>
                <span>Saved Searches</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $savedSearches->count() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['offers.create', 'offers.view', 'offer.create', 'offer.view']))
        <a href="#offers" data-target-section="offers" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🤝</span>
                <span>My Offers</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $offers->count() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['viewings.create', 'viewings.view', 'viewing.create', 'viewing.view']))
        <a href="#viewings" data-target-section="viewings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📅</span>
                <span>Viewings</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $viewings->count() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['properties.save', 'properties.favorite', 'property.save']))
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
    {{-- Top Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- 1. Saved Searches & Alerts --}}
    <div id="saved-searches" data-section="saved-searches" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#e7ddca] pb-4">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34]">Saved Searches &amp; Alerts</h2>
                <p class="text-xs text-slate-500">Automated price drop and new listing notifications matching your criteria.</p>
            </div>
            <button type="button" onclick="document.getElementById('saved-search-form').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition">
                + New Search Alert
            </button>
        </div>

        {{-- New Saved Search Form --}}
        <div id="saved-search-form" class="hidden mt-4 rounded-2xl border border-[#d4af37] bg-[#fbf8f3] p-5">
            <h3 class="text-sm font-black text-[#1d3c34] mb-3">Create Search Alert</h3>
            <form method="POST" action="{{ route('buyer.saved-searches.store') }}" class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700">Alert Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Bole Apartments under 15M" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">City / District</label>
                    <input type="text" name="city" placeholder="Addis Ababa, Bole, Kazanchis" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Max Budget (ETB)</label>
                    <input type="number" name="max_price" placeholder="15000000" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Frequency</label>
                    <select name="alert_frequency" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
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
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-4 flex items-start justify-between">
                    <div>
                        <h4 class="font-bold text-sm text-[#1d3c34]">{{ $search->title }}</h4>
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

    {{-- 2. Offers & Multi-Round Negotiations --}}
    <div id="offers" data-section="offers" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">My Formal Offers &amp; Negotiations</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e7ddca] text-sm">
                <thead>
                    <tr class="bg-[#fbf9f5] text-left text-xs font-black uppercase text-[#587165]">
                        <th class="py-3 px-4">Offer Ref</th>
                        <th class="py-3 px-4">Property</th>
                        <th class="py-3 px-4">Offered Price</th>
                        <th class="py-3 px-4">Current Status</th>
                        <th class="py-3 px-4">Negotiation Rounds</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca]">
                    @forelse ($offers as $offer)
                        <tr class="hover:bg-[#fcfaf7]">
                            <td class="py-3 px-4 font-mono font-bold text-xs text-[#1d3c34]">{{ $offer->offer_reference }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $offer->property?->title ?? '—' }}</td>
                            <td class="py-3 px-4 font-bold text-emerald-800">{{ number_format($offer->offered_price) }} ETB</td>
                            <td class="py-3 px-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ match ($offer->status) { 'accepted' => 'bg-emerald-100 text-emerald-800', 'rejected' => 'bg-rose-100 text-rose-800', 'countered' => 'bg-amber-100 text-amber-800', default => 'bg-blue-100 text-blue-800' } }}">
                                    {{ ucfirst(str_replace('_', ' ', $offer->status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-600">{{ $offer->negotiations->count() }} rounds</td>
                            <td class="py-3 px-4 text-right">
                                @if ($offer->property)
                                    <a href="{{ route('properties.show', $offer->property->slug) }}" class="text-xs font-bold text-[#1d3c34] hover:underline">View Property &rarr;</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-xs text-slate-400">No formal offers submitted yet. Make offers directly on property listings.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. Scheduled Viewings & Appointments --}}
    <div id="viewings" data-section="viewings" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Scheduled Viewings</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($viewings as $viewing)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-4">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold text-[#587165]">{{ $viewing->viewing_reference }}</span>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ match ($viewing->status) { 'confirmed' => 'bg-emerald-100 text-emerald-800', 'completed' => 'bg-blue-100 text-blue-800', 'cancelled' => 'bg-rose-100 text-rose-800', default => 'bg-amber-100 text-amber-800' } }}">
                            {{ ucfirst($viewing->status) }}
                        </span>
                    </div>
                    <h4 class="mt-2 font-bold text-sm text-[#1d3c34] line-clamp-1">{{ $viewing->property?->title ?? 'Viewing Appointment' }}</h4>
                    <p class="text-xs text-slate-500 mt-1">📅 {{ $viewing->viewing_date?->format('M d, Y') }} &bull; ⏰ {{ $viewing->time_slot ?? 'Flexible' }}</p>
                    <p class="text-xs text-slate-600 mt-1">Agent: {{ $viewing->agent?->name ?? 'Assigned Consultant' }}</p>
                </div>
            @empty
                <p class="col-span-3 text-xs text-slate-400 py-4 text-center">No viewings scheduled yet.</p>
            @endforelse
        </div>
    </div>

    {{-- 4. Saved Properties --}}
    <div id="saved-properties" data-section="saved-properties" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Saved &amp; Favorited Properties</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @php $saved = $likedProperties->merge($favoritedProperties)->unique('property_id'); @endphp
            @forelse ($saved as $item)
                @if ($item->property)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] overflow-hidden flex flex-col">
                        <div class="h-36 bg-slate-200 relative">
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
                                <h4 class="font-bold text-sm text-[#1d3c34] line-clamp-1">{{ $item->property->title }}</h4>
                                <p class="text-xs text-slate-500 mt-1">📍 {{ $item->property->city }}, {{ $item->property->subcity ?? '' }}</p>
                            </div>
                            <div class="mt-4 pt-2 border-t border-[#e7ddca] flex items-center justify-between">
                                <span class="text-[10px] text-slate-500">{{ ucfirst($item->property->type) }}</span>
                                <a href="{{ route('properties.show', $item->property->slug) }}" class="text-xs font-bold text-[#1d3c34] hover:underline">View &rarr;</a>
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
