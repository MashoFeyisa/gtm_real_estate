@extends('dashboards.layout')

@section('title', 'Buyer Dashboard')
@section('portal_name', 'Buyer Workspace')

@section('sidebar-nav')
    @if (auth()->user()->hasAnyPermission(['properties.save', 'properties.favorite']))
        <x-sidebar-item href="#collections" icon="📁" label="Collections" :count="$collections->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['properties.compare', 'properties.view']))
        <x-sidebar-item href="#compare" icon="⚖️" label="Compare Matrix" :count="$comparedProperties->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['searches.save', 'properties.save']))
        <x-sidebar-item href="#saved-searches" icon="🔔" label="Saved Searches" :count="$savedSearches->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['inquiries.view', 'inquiries.create', 'messages.view']))
        <x-sidebar-item href="#inquiries" icon="💬" label="Inquiries &amp; Messages" :count="$inquiries->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['viewings.view', 'viewings.create']))
        <x-sidebar-item href="#viewings" icon="📅" label="Viewings" :count="$viewings->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['offers.view', 'offers.create']))
        <x-sidebar-item href="#offers" icon="🤝" label="My Offers" :count="$offers->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['reservations.view', 'reservations.create']))
        <x-sidebar-item href="#reservations" icon="🏢" label="Unit Reservations" :count="$reservations->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['documents.view', 'documents.create']))
        <x-sidebar-item href="#documents" icon="🗄️" label="Document Vault" :count="$documents->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['transactions.view', 'orders.view']))
        <x-sidebar-item href="#deals" icon="📜" label="Transaction Tracker" :count="$deals->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['properties.save', 'properties.favorite']))
        <x-sidebar-item href="#saved-properties" icon="❤️" label="Saved Properties" :count="$likedProperties->count() + $favoritedProperties->count()" />
    @endif
@endsection

@section('content')
<div class="space-y-8">
    {{-- Alerts & Session Feedback --}}
    @if (session('success'))
        <div class="token-alert token-alert-success flex items-center justify-between">
            <span>✅ {{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-[var(--token-success-text)] hover:opacity-70">&times;</button>
        </div>
    @endif
    @if (session('error'))
        <div class="token-alert token-alert-danger flex items-center justify-between">
            <span>⚠️ {{ session('error') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-[var(--token-danger-text)] hover:opacity-70">&times;</button>
        </div>
    @endif

    {{-- Top Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- 1. Property Collections Organizer --}}
    <div id="collections" data-section="collections" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[var(--token-border)] pb-4">
            <div>
                <h2 class="text-xl font-black">Custom Property Collections</h2>
                <p class="text-xs token-muted">Organize prospective homes into curated collections (e.g. Bole Luxury, Family Villas).</p>
            </div>
            <button type="button" onclick="document.getElementById('new-collection-form').classList.toggle('hidden')" class="token-btn token-btn-primary">
                + New Collection
            </button>
        </div>

        {{-- New Collection Inline Form --}}
        <div id="new-collection-form" class="hidden mt-4 token-alert token-alert-accent">
            <h3 class="text-sm font-black mb-3">Create New Collection</h3>
            <form method="POST" action="{{ route('collections.store') }}" class="grid gap-4 sm:grid-cols-3">
                @csrf
                <div>
                    <label class="token-label">Collection Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Kazanchis Penthouses" class="mt-1 token-field">
                </div>
                <div class="sm:col-span-2">
                    <label class="token-label">Description</label>
                    <input type="text" name="description" placeholder="Short note about properties in this group" class="mt-1 token-field">
                </div>
                <div class="sm:col-span-3 flex justify-end">
                    <button type="submit" class="token-btn token-btn-primary">
                        Create Collection
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 space-y-6">
            @forelse ($collections as $collection)
                <div class="token-card">
                    <div class="flex items-center justify-between border-b border-[var(--token-border)] pb-3">
                        <div>
                            <h3 class="text-base font-black">{{ $collection->name }}</h3>
                            @if ($collection->description)
                                <p class="text-xs token-muted mt-0.5">{{ $collection->description }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="token-badge token-badge-success">
                                {{ $collection->properties->count() }} {{ Str::plural('property', $collection->properties->count()) }}
                            </span>
                            <form method="POST" action="{{ route('collections.destroy', $collection) }}" onsubmit="return confirm('Delete this collection?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-[var(--token-danger)] hover:opacity-70">Delete</button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        @forelse ($collection->properties as $prop)
                            <div class="token-card !p-3 flex flex-col justify-between">
                                <div>
                                    <h4 class="text-xs font-bold line-clamp-1">{{ $prop->title }}</h4>
                                    <p class="text-[11px] font-bold mt-1 text-[var(--token-success)]">{{ number_format($prop->price) }} ETB</p>
                                </div>
                                <div class="mt-3 flex items-center justify-between pt-2 border-t border-[var(--token-border)]">
                                    <a href="{{ route('properties.show', $prop->slug) }}" class="text-[11px] font-bold text-[var(--token-primary)] hover:underline">View Listing</a>
                                    <form method="POST" action="{{ route('collections.toggleProperty', [$collection, $prop]) }}">
                                        @csrf
                                        <button type="submit" class="text-[11px] font-bold text-[var(--token-danger)] hover:opacity-70" title="Remove from collection">Remove</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="col-span-full text-xs token-muted py-2">No properties added to this collection yet. Browse listings and add them.</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-[var(--token-border)] p-8 text-center">
                    <p class="text-sm font-bold token-muted">No custom collections created yet.</p>
                    <p class="text-xs token-muted mt-1">Group and compare prospective properties by creating your first collection above.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- 2. Property Comparison Matrix --}}
    <div id="compare" data-section="compare" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[var(--token-border)] pb-4">
            <div>
                <h2 class="text-xl font-black">Side-by-Side Property Comparison Matrix</h2>
                <p class="text-xs token-muted">Compare up to 4 selected properties across price, specifications, location, and amenities.</p>
            </div>
            @if ($comparedProperties->isNotEmpty())
                <form method="POST" action="{{ route('properties.compare.clear') }}">
                    @csrf
                    <button type="submit" class="token-btn token-btn-danger transition">
                        Clear All Comparisons
                    </button>
                </form>
            @endif
        </div>

        @if ($comparedProperties->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-[var(--token-border)] p-10 text-center">
                <span class="text-3xl">⚖️</span>
                <h3 class="mt-2 text-sm font-black">No properties queued for comparison</h3>
                <p class="mt-1 text-xs token-muted">Click "Compare" on any property cards while browsing to inspect them side-by-side.</p>
                <a href="{{ route('properties.index') }}" class="mt-4 token-btn token-btn-primary">
                    Browse Marketplace Listings &rarr;
                </a>
            </div>
        @else
            <div class="mt-6 token-table-wrap">
                <table class="token-table min-w-full text-xs">
                    <thead>
                        <tr>
                            <th class="w-48">Feature</th>
                            @foreach ($comparedProperties as $prop)
                                <th class="min-w-[200px]">
                                    <div class="flex items-center justify-between">
                                        <span class="truncate">{{ $prop->title }}</span>
                                        <form method="POST" action="{{ route('properties.compare.toggle', $prop) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-[var(--token-danger)] hover:opacity-70 font-bold ml-2" title="Remove">&times;</button>
                                        </form>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="font-bold token-muted">Asking Price</td>
                            @foreach ($comparedProperties as $prop)
                                <td class="font-black text-sm text-[var(--token-success)]">{{ number_format($prop->price) }} ETB</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="font-bold token-muted">Category &amp; Type</td>
                            @foreach ($comparedProperties as $prop)
                                <td class="font-semibold">{{ ucfirst($prop->type) }} ({{ ucfirst(str_replace('_', ' ', $prop->property_category ?? 'Residential')) }})</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="font-bold token-muted">Location</td>
                            @foreach ($comparedProperties as $prop)
                                <td>{{ $prop->subcity ? $prop->subcity . ', ' : '' }}{{ $prop->city }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="font-bold token-muted">Bedrooms &amp; Baths</td>
                            @foreach ($comparedProperties as $prop)
                                <td>{{ $prop->bedrooms ?? '—' }} Beds &bull; {{ $prop->bathrooms ?? '—' }} Baths</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="font-bold token-muted">Gross Floor Area</td>
                            @foreach ($comparedProperties as $prop)
                                <td>{{ $prop->area_sqm ? number_format($prop->area_sqm) . ' m²' : '—' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="font-bold token-muted">Verification Status</td>
                            @foreach ($comparedProperties as $prop)
                                <td>
                                    <span class="token-badge token-badge-success !text-[10px]">
                                        {{ ucfirst($prop->verification_status ?? 'Verified') }}
                                    </span>
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="font-bold token-muted">Market Estimate</td>
                            @foreach ($comparedProperties as $prop)
                                <td class="token-muted italic">Asking-price indicator / platform estimate</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="font-bold token-muted">Actions</td>
                            @foreach ($comparedProperties as $prop)
                                <td>
                                    <a href="{{ route('properties.show', $prop->slug) }}" class="token-btn token-btn-primary inline-block">
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
    <div id="saved-searches" data-section="saved-searches" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[var(--token-border)] pb-4">
            <div>
                <h2 class="text-xl font-black">Saved Searches &amp; Alerts</h2>
                <p class="text-xs token-muted">Automated price drop and new listing notifications matching your criteria.</p>
            </div>
            <button type="button" onclick="document.getElementById('saved-search-form').classList.toggle('hidden')" class="token-btn token-btn-primary">
                + New Search Alert
            </button>
        </div>

        {{-- New Saved Search Form --}}
        <div id="saved-search-form" class="hidden mt-4 token-alert token-alert-accent">
            <h3 class="text-sm font-black mb-3">Create Search Alert</h3>
            <form method="POST" action="{{ route('buyer.saved-searches.store') }}" class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                @csrf
                <div>
                    <label class="token-label">Alert Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Bole Apartments under 15M" class="mt-1 token-field">
                </div>
                <div>
                    <label class="token-label">City / District</label>
                    <input type="text" name="city" placeholder="Addis Ababa, Bole" class="mt-1 token-field">
                </div>
                <div>
                    <label class="token-label">Max Budget (ETB)</label>
                    <input type="number" name="max_price" placeholder="15000000" class="mt-1 token-field">
                </div>
                <div>
                    <label class="token-label">Frequency</label>
                    <select name="alert_frequency" class="mt-1 token-field">
                        <option value="instant">Instant Notification</option>
                        <option value="daily" selected>Daily Digest</option>
                        <option value="weekly">Weekly</option>
                    </select>
                </div>
                <div class="sm:col-span-2 md:col-span-4 flex justify-end">
                    <button type="submit" class="token-btn token-btn-primary">
                        Save Alert
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($savedSearches as $search)
                <div class="token-card flex items-start justify-between">
                    <div>
                        <h4 class="font-bold text-sm">{{ $search->title }}</h4>
                        <p class="text-xs token-muted mt-1">
                            @if (!empty($search->criteria['city'])) City: {{ $search->criteria['city'] }} &bull; @endif
                            @if (!empty($search->criteria['max_price'])) Max: {{ number_format($search->criteria['max_price']) }} ETB @endif
                        </p>
                        <span class="inline-block mt-2 token-badge token-badge-success !text-[10px]">
                            {{ ucfirst($search->alert_frequency) }} Alert
                        </span>
                    </div>
                    <form method="POST" action="{{ route('buyer.saved-searches.destroy', $search) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-bold text-[var(--token-danger)] hover:opacity-70" title="Delete Alert">&times;</button>
                    </form>
                </div>
            @empty
                <p class="col-span-3 text-xs token-muted py-4 text-center">No saved search alerts yet. Create one to receive automated updates.</p>
            @endforelse
        </div>
    </div>

    {{-- 4. Inquiries & Direct Messages --}}
    <div id="inquiries" data-section="inquiries" class="section-panel hidden scroll-mt-6 token-card">
        <h2 class="token-section-title">My Inquiries &amp; Seller Communication</h2>
        <div class="mt-4 space-y-4">
            @forelse ($inquiries as $inquiry)
                <div class="token-card">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[var(--token-border)] pb-3">
                        <div>
                            <span class="font-mono text-xs font-bold token-muted">#INQ-{{ $inquiry->id }}</span>
                            <h4 class="font-bold text-sm">{{ $inquiry->property?->title ?? 'General Inquiry' }}</h4>
                            <p class="text-xs token-muted">Contacted: {{ $inquiry->agent?->name ?? 'GTM Consulting Desk' }} &bull; {{ $inquiry->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <span class="token-badge self-start sm:self-auto {{ match ($inquiry->status) { 'resolved' => 'token-badge-success', 'contacted' => 'token-badge-info', default => 'token-badge-warning' } }}">
                            {{ ucfirst($inquiry->status) }}
                        </span>
                    </div>

                    <div class="mt-3 text-xs whitespace-pre-line token-card !p-3 !rounded-xl">
                        {{ $inquiry->message }}
                    </div>

                    {{-- Reply Form --}}
                    <form method="POST" action="{{ route('buyer.inquiries.reply', $inquiry) }}" class="mt-3 flex gap-2">
                        @csrf
                        <input type="text" name="message" required placeholder="Type follow-up reply to seller..." class="flex-1 token-field">
                        <button type="submit" class="token-btn token-btn-primary">
                            Send Reply
                        </button>
                    </form>
                </div>
            @empty
                <p class="text-xs token-muted py-6 text-center">No inquiries sent yet. Contact sellers directly from property listing pages.</p>
            @endforelse
        </div>
    </div>

    {{-- 5. Scheduled Viewings & Appointments --}}
    <div id="viewings" data-section="viewings" class="section-panel hidden scroll-mt-6 token-card">
        <h2 class="token-section-title">Scheduled Viewings</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($viewings as $viewing)
                <div class="token-card flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold token-muted">{{ $viewing->viewing_reference }}</span>
                            <span class="token-badge !text-[10px] {{ match ($viewing->status) { 'confirmed' => 'token-badge-success', 'completed' => 'token-badge-info', 'cancelled' => 'token-badge-danger', default => 'token-badge-warning' } }}">
                                {{ ucfirst($viewing->status) }}
                            </span>
                        </div>
                        <h4 class="mt-2 font-bold text-sm line-clamp-1">{{ $viewing->property?->title ?? 'Viewing Appointment' }}</h4>
                        <p class="text-xs token-muted mt-1">📅 {{ $viewing->viewing_date?->format('M d, Y') }} &bull; ⏰ {{ ucfirst(str_replace('_', ' ', $viewing->viewing_time ?? 'Flexible')) }}</p>
                        <p class="text-xs token-muted mt-1">Agent: {{ $viewing->agent?->user?->name ?? 'Assigned Consultant' }}</p>
                    </div>

                    @if ($viewing->status !== 'cancelled' && $viewing->status !== 'completed')
                        <div class="mt-4 pt-3 border-t border-[var(--token-border)] flex items-center justify-between gap-2">
                            <button type="button" onclick="document.getElementById('reschedule-{{ $viewing->id }}').classList.toggle('hidden')" class="text-xs font-bold text-[var(--token-info)] hover:opacity-70">
                                Reschedule
                            </button>
                            <form method="POST" action="{{ route('buyer.viewings.cancel', $viewing) }}" onsubmit="return confirm('Cancel this inspection appointment?')">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="text-xs font-bold text-[var(--token-danger)] hover:opacity-70">
                                    Cancel
                                </button>
                            </form>
                        </div>

                        {{-- Reschedule Dropdown Form --}}
                        <div id="reschedule-{{ $viewing->id }}" class="hidden mt-3 token-dropdown rounded-xl p-3">
                            <form method="POST" action="{{ route('buyer.viewings.reschedule', $viewing) }}" class="space-y-2">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="token-label">New Date</label>
                                    <input type="date" name="viewing_date" required min="{{ date('Y-m-d') }}" value="{{ $viewing->viewing_date?->format('Y-m-d') }}" class="token-field">
                                </div>
                                <div>
                                    <label class="token-label">Time Window</label>
                                    <select name="viewing_time" required class="token-field">
                                        <option value="morning_09_11">Morning (09:00 - 11:00)</option>
                                        <option value="afternoon_14_16">Afternoon (14:00 - 16:00)</option>
                                        <option value="evening_17_19">Evening (17:00 - 19:00)</option>
                                    </select>
                                </div>
                                <button type="submit" class="w-full token-btn token-btn-primary">
                                    Confirm Reschedule
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-xs token-muted py-4 text-center">No viewings scheduled yet.</p>
            @endforelse
        </div>
    </div>

    {{-- 6. Offers & Multi-Round Negotiations --}}
    <div id="offers" data-section="offers" class="section-panel hidden scroll-mt-6 token-card">
        <h2 class="token-section-title">My Formal Offers &amp; Negotiations</h2>
        <div class="mt-4 space-y-4">
            @forelse ($offers as $offer)
                <div class="token-card">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[var(--token-border)] pb-3">
                        <div>
                            <span class="font-mono text-xs font-bold token-muted">{{ $offer->offer_reference }}</span>
                            <h4 class="font-bold text-sm">{{ $offer->property?->title ?? 'Property Offer' }}</h4>
                            <p class="text-xs token-muted">Initial: {{ number_format($offer->offered_price) }} ETB &bull; Submitted {{ $offer->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="token-badge {{ match ($offer->status) { 'accepted' => 'token-badge-success', 'rejected' => 'token-badge-danger', 'countered' => 'token-badge-warning', 'withdrawn' => 'token-badge-neutral', default => 'token-badge-info' } }}">
                                {{ ucfirst(str_replace('_', ' ', $offer->status)) }}
                            </span>
                            @if (!in_array($offer->status, ['accepted', 'completed', 'withdrawn'], true))
                                <form method="POST" action="{{ route('buyer.offers.withdraw', $offer) }}" onsubmit="return confirm('Withdraw this offer?')">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="token-btn token-btn-danger">
                                        Withdraw
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    {{-- Negotiation Multi-Round History --}}
                    <div class="mt-4">
                        <h5 class="text-xs font-black uppercase tracking-wider token-muted">Negotiation Rounds ({{ $offer->negotiations->count() }})</h5>
                        <div class="mt-2 space-y-2 border-l-2 border-[var(--token-primary)] pl-4 ml-1">
                            @forelse ($offer->negotiations as $round)
                                <div class="text-xs">
                                    <span class="font-bold {{ $round->sender_type === 'buyer' ? 'text-[var(--token-info)]' : 'text-[var(--token-warning)]' }}">
                                        {{ ucfirst($round->sender_type) }}:
                                    </span>
                                    <span class="font-black text-[var(--token-success)]">{{ number_format($round->amount) }} ETB</span>
                                    <span class="token-muted text-[10px] ml-1">({{ $round->created_at->format('M d, H:i') }})</span>
                                    @if ($round->message)
                                        <p class="token-muted mt-0.5 italic">"{{ $round->message }}"</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs token-muted italic">No counter rounds yet. Awaiting seller response.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Counter Action Bar if Countered --}}
                    @if ($offer->status === 'countered')
                        <div class="mt-4 pt-3 border-t border-[var(--token-border)] flex flex-wrap items-center gap-3">
                            <form method="POST" action="{{ route('offers.accept', $offer) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="token-btn token-btn-primary">
                                    Accept Counter ({{ number_format($offer->current_counter_amount) }} ETB)
                                </button>
                            </form>
                            <button type="button" onclick="document.getElementById('counter-form-{{ $offer->id }}').classList.toggle('hidden')" class="token-btn token-btn-primary">
                                Counter Again
                            </button>
                        </div>

                        <div id="counter-form-{{ $offer->id }}" class="hidden mt-3 token-dropdown rounded-xl p-3">
                            <form method="POST" action="{{ route('offers.counter', $offer) }}" class="flex gap-2">
                                @csrf
                                <input type="number" name="counter_amount" required placeholder="Your Counter Amount (ETB)" class="token-field w-48">
                                <input type="text" name="message" placeholder="Optional rationale note" class="flex-1 token-field">
                                <button type="submit" class="token-btn token-btn-primary">
                                    Send Counter
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="py-6 text-center text-xs token-muted">No formal offers submitted yet. Make offers directly on property listings.</p>
            @endforelse
        </div>
    </div>

    {{-- 7. Unit Reservations & Off-Plan Holdings --}}
    <div id="reservations" data-section="reservations" class="section-panel hidden scroll-mt-6 token-card">
        <h2 class="token-section-title">Off-Plan Unit Reservations</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($reservations as $res)
                <div class="token-card flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold token-muted">{{ $res->reservation_reference }}</span>
                            <span class="token-badge !text-[10px] {{ match ($res->status) { 'confirmed' => 'token-badge-success', 'cancelled' => 'token-badge-danger', 'expired' => 'token-badge-neutral', default => 'token-badge-warning' } }}">
                                {{ ucfirst($res->status) }}
                            </span>
                        </div>
                        <h4 class="mt-2 font-bold text-sm">{{ $res->project?->name ?? 'Development Project' }}</h4>
                        <p class="text-xs token-muted mt-1">Unit: {{ $res->unit?->unit_number ?? 'Assigned Unit' }} &bull; Floor {{ $res->unit?->floor_number ?? '—' }}</p>
                        @if ($res->unit?->price)
                            <p class="text-xs font-bold text-[var(--token-success)] mt-1">Unit Price: {{ number_format($res->unit->price) }} ETB</p>
                        @endif
                        <p class="text-[10px] token-muted mt-1">Booked: {{ $res->created_at->format('M d, Y') }}</p>
                    </div>

                    @if ($res->status === 'pending' || $res->status === 'confirmed')
                        <div class="mt-4 pt-3 border-t border-[var(--token-border)] flex justify-end">
                            <form method="POST" action="{{ route('buyer.reservations.cancel', $res) }}" onsubmit="return confirm('Cancel this unit reservation?')">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="text-xs font-bold text-[var(--token-danger)] hover:opacity-70">
                                    Release Unit &amp; Cancel
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-xs token-muted py-4 text-center">No active off-plan reservations.</p>
            @endforelse
        </div>
    </div>

    {{-- 8. Document Vault --}}
    <div id="documents" data-section="documents" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[var(--token-border)] pb-4">
            <div>
                <h2 class="text-xl font-black">Buyer Document Vault</h2>
                <p class="text-xs token-muted">Secure private vault for financial pre-approvals, national ID, and proof of funds.</p>
            </div>
            <button type="button" onclick="document.getElementById('upload-document-form').classList.toggle('hidden')" class="token-btn token-btn-primary">
                + Upload Document
            </button>
        </div>

        {{-- Upload Document Form --}}
        <div id="upload-document-form" class="hidden mt-4 token-alert token-alert-accent">
            <h3 class="text-sm font-black mb-3">Upload New Document</h3>
            <form method="POST" action="{{ route('buyer.documents.upload') }}" enctype="multipart/form-data" class="grid gap-4 sm:grid-cols-3">
                @csrf
                <div>
                    <label class="token-label">Document Title *</label>
                    <input type="text" name="title" required placeholder="e.g. CBE Bank Proof of Funds" class="mt-1 token-field">
                </div>
                <div>
                    <label class="token-label">Document Type *</label>
                    <select name="document_type" required class="mt-1 token-field">
                        <option value="proof_of_funds">Proof of Funds / Bank Statement</option>
                        <option value="national_id">National ID / Passport</option>
                        <option value="employment_letter">Employment / Income Verification</option>
                        <option value="tax_id">TIN / Tax Identification</option>
                        <option value="pre_approval">Mortgage Pre-Approval Letter</option>
                        <option value="other">Other Supporting Document</option>
                    </select>
                </div>
                <div>
                    <label class="token-label">File (PDF, PNG, JPG, DOCX up to 10MB) *</label>
                    <input type="file" name="document_file" required class="mt-1 token-field">
                </div>
                <div class="sm:col-span-3 flex justify-end">
                    <button type="submit" class="token-btn token-btn-primary">
                        Upload to Private Vault
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 token-table-wrap">
            <table class="token-table min-w-full text-xs">
                <thead>
                    <tr>
                        <th>Document Title</th>
                        <th>Type</th>
                        <th>Access Level</th>
                        <th>File Size</th>
                        <th>Uploaded</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $doc)
                        <tr>
                            <td class="font-bold">{{ $doc->title }}</td>
                            <td class="font-semibold token-muted">{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</td>
                            <td>
                                <span class="token-badge token-badge-neutral !text-[10px]">
                                    🔒 {{ ucfirst($doc->access_level) }}
                                </span>
                            </td>
                            <td class="token-muted">{{ $doc->file_size ? number_format($doc->file_size / 1024, 1) . ' KB' : '—' }}</td>
                            <td class="token-muted">{{ $doc->created_at->format('M d, Y') }}</td>
                            <td class="text-right space-x-2">
                                @if ($doc->file_path && $doc->isAccessibleBy(auth()->user()))
                                    <a href="{{ route('media.document', $doc) }}" target="_blank" class="text-xs font-bold text-[var(--token-primary)] hover:underline">Download</a>
                                @endif
                                <form method="POST" action="{{ route('buyer.documents.destroy', $doc) }}" class="inline" onsubmit="return confirm('Delete this document?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-[var(--token-danger)] hover:opacity-70">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-xs token-muted">Your document vault is currently empty. Upload IDs and pre-approval letters above.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 9. Transaction Tracker & Closing Timeline --}}
    <div id="deals" data-section="deals" class="section-panel hidden scroll-mt-6 token-card">
        <h2 class="token-section-title">Transaction Tracker &amp; Closing Timeline</h2>
        <div class="mt-4 space-y-6">
            @forelse ($deals as $deal)
                <div class="token-card">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[var(--token-border)] pb-3">
                        <div>
                            <span class="font-mono text-xs font-bold token-muted">{{ $deal->order_number ?? '#DEAL-' . $deal->id }}</span>
                            <h4 class="font-bold text-sm">{{ $deal->property?->title ?? 'Property Acquisition' }}</h4>
                            <p class="text-xs token-muted">Agent: {{ $deal->agent?->name ?? 'Licensed Realtor' }} &bull; Total Value: {{ number_format($deal->offer_amount ?? 0) }} ETB</p>
                        </div>
                        <span class="token-badge {{ match ($deal->status) { 'completed' => 'token-badge-success', 'cancelled' => 'token-badge-danger', default => 'token-badge-info' } }}">
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
                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black {{ $stage >= 1 ? 'bg-[var(--token-success)] text-[var(--token-text-on-color)]' : 'bg-[var(--token-surface-subtle)] text-[var(--token-text-muted)]' }}">1</span>
                                <span class="mt-1 text-[10px] font-bold {{ $stage >= 1 ? 'text-[var(--token-success)]' : 'token-muted' }}">Offer Accepted</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black {{ $stage >= 2 ? 'bg-[var(--token-success)] text-[var(--token-text-on-color)]' : 'bg-[var(--token-surface-subtle)] text-[var(--token-text-muted)]' }}">2</span>
                                <span class="mt-1 text-[10px] font-bold {{ $stage >= 2 ? 'text-[var(--token-success)]' : 'token-muted' }}">Due Diligence</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black {{ $stage >= 3 ? 'bg-[var(--token-success)] text-[var(--token-text-on-color)]' : 'bg-[var(--token-surface-subtle)] text-[var(--token-text-muted)]' }}">3</span>
                                <span class="mt-1 text-[10px] font-bold {{ $stage >= 3 ? 'text-[var(--token-success)]' : 'token-muted' }}">Agreement Signed</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black {{ $stage >= 4 ? 'bg-[var(--token-success)] text-[var(--token-text-on-color)]' : 'bg-[var(--token-surface-subtle)] text-[var(--token-text-muted)]' }}">4</span>
                                <span class="mt-1 text-[10px] font-bold {{ $stage >= 4 ? 'text-[var(--token-success)]' : 'token-muted' }}">Bank Escrow</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black {{ $stage >= 5 ? 'bg-[var(--token-success)] text-[var(--token-text-on-color)]' : 'bg-[var(--token-surface-subtle)] text-[var(--token-text-muted)]' }}">5</span>
                                <span class="mt-1 text-[10px] font-bold {{ $stage >= 5 ? 'text-[var(--token-success)]' : 'token-muted' }}">Title Handover</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-xs token-muted">No active acquisition transactions in progress.</p>
            @endforelse
        </div>
    </div>

    {{-- 10. Saved Properties --}}
    <div id="saved-properties" data-section="saved-properties" class="section-panel hidden scroll-mt-6 token-card">
        <h2 class="token-section-title">Saved &amp; Favorited Properties</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @php $saved = $likedProperties->merge($favoritedProperties)->unique('property_id'); @endphp
            @forelse ($saved as $item)
                @if ($item->property)
                    <div class="token-card !p-0 overflow-hidden flex flex-col">
                        <div class="h-36 bg-[var(--token-surface-subtle)] relative">
                            @if ($item->property->cover_image_url)
                                <img src="{{ $item->property->cover_image_url }}" alt="{{ $item->property->title }}" class="h-full w-full object-cover">
                            @else
                                <div class="h-full w-full flex items-center justify-center token-muted text-xs">No image</div>
                            @endif
                            <span class="absolute top-2 right-2 token-badge token-badge-success !text-[10px]">
                                {{ number_format($item->property->price) }} ETB
                            </span>
                        </div>
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="font-bold text-sm line-clamp-1">{{ $item->property->title }}</h4>
                                <p class="text-xs token-muted mt-1">📍 {{ $item->property->city }}, {{ $item->property->subcity ?? '' }}</p>
                            </div>
                            <div class="mt-4 pt-2 border-t border-[var(--token-border)] flex items-center justify-between">
                                <span class="text-[10px] token-muted">{{ ucfirst($item->property->type) }}</span>
                                <a href="{{ route('properties.show', $item->property->slug) }}" class="text-xs font-bold text-[var(--token-primary)] hover:underline">View &rarr;</a>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <p class="col-span-3 text-xs token-muted py-4 text-center">No saved properties. Browse listings and click the heart icon to save.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
