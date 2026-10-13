@extends('dashboards.layout')

@section('title', 'Seller & Landlord Dashboard')
@section('portal_name', 'Seller / Landlord Desk')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if (auth()->user()->hasAnyPermission(['properties.create', 'properties.update', 'properties.view', 'properties.delete']))
        <a href="#properties" data-target-section="properties" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🏠</span>
                <span>My Properties</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $properties->count() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['listings.create', 'listings.publish', 'listings.manage', 'properties.publish']))
        <a href="#listings" data-target-section="listings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📋</span>
                <span>Listings Workflow</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $listings->count() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['inquiries.view', 'leads.manage', 'inquiries.reply', 'sellers.contact']))
        <a href="#leads-inquiries" data-target-section="leads-inquiries" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>💬</span>
                <span>Leads & Inquiries</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $leads->count() + $inquiries->count() }}</span>
        </a>
    @endif
@endsection

@section('content')
<div x-data="{ openCreateModal: false }" class="space-y-8">
    {{-- Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- 1. Properties & Listing Quality Score --}}
    <div id="properties" data-section="properties" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Properties &amp; Listing Quality Scores</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($properties as $property)
                @php $quality = $scores[$property->id] ?? 50; @endphp
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] overflow-hidden flex flex-col justify-between p-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $property->status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ucfirst($property->status) }}
                            </span>
                            <span class="text-xs font-bold text-slate-500">{{ ucfirst($property->type) }}</span>
                        </div>
                        <h4 class="mt-2 font-bold text-base text-[#1d3c34] line-clamp-1">{{ $property->title }}</h4>
                        <p class="text-xs text-slate-500 mt-0.5">📍 {{ $property->city }} &bull; {{ number_format($property->price) }} ETB</p>

                        {{-- Quality Score Gauge --}}
                        <div class="mt-4 rounded-xl border border-[#e7ddca] bg-white p-3">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700">Listing Quality Score</span>
                                <span class="font-black {{ $quality >= 80 ? 'text-emerald-700' : ($quality >= 50 ? 'text-amber-700' : 'text-rose-700') }}">{{ $quality }}%</span>
                            </div>
                            <div class="mt-1.5 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full {{ $quality >= 80 ? 'bg-emerald-500' : ($quality >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $quality }}%"></div>
                            </div>
                            <p class="mt-1.5 text-[10px] text-slate-400">
                                {{ $quality >= 80 ? '✓ High quality (good photos & docs)' : 'Tip: Add Carta & more photos to reach 100%' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-[#e7ddca] flex items-center justify-between">
                        <span class="text-[11px] text-slate-400">{{ $property->images->count() }} photos &bull; {{ $property->documents->count() }} docs</span>
                        <a href="{{ route('properties.show', $property->slug) }}" class="text-xs font-bold text-[#1d3c34] hover:underline">
                            View &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <p class="col-span-3 text-xs text-slate-400 py-4 text-center">No properties registered.</p>
            @endforelse
        </div>
    </div>

    {{-- 2. Listings & Status Workflow --}}
    <div id="listings" data-section="listings" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Listings &amp; Lifecycle Status (Draft &rarr; Published &rarr; Sold)</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e7ddca] text-sm">
                <thead>
                    <tr class="bg-[#fbf9f5] text-left text-xs font-black uppercase text-[#587165]">
                        <th class="py-3 px-4">Listing Code</th>
                        <th class="py-3 px-4">Title</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Price</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca]">
                    @forelse ($listings as $listing)
                        <tr class="hover:bg-[#fcfaf7]">
                            <td class="py-3 px-4 font-mono font-bold text-xs text-[#1d3c34]">{{ $listing->listing_code ?? ('LST-' . $listing->id) }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $listing->title ?? ($listing->property?->title ?? 'Listing') }}</td>
                            <td class="py-3 px-4 text-xs">{{ ucfirst($listing->listing_type ?? 'Sale') }}</td>
                            <td class="py-3 px-4 font-bold text-emerald-800">{{ number_format($listing->price ?? $listing->property?->price ?? 0) }} ETB</td>
                            <td class="py-3 px-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ match ($listing->status) { 'published' => 'bg-emerald-100 text-emerald-800', 'paused' => 'bg-amber-100 text-amber-800', 'sold' => 'bg-blue-100 text-blue-800', default => 'bg-slate-200 text-slate-700' } }}">
                                    {{ ucfirst(str_replace('_', ' ', $listing->status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if ($listing->property)
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('properties.show', $listing->property->slug) }}" class="text-xs font-bold text-[#1d3c34] hover:underline">Preview</a>
                                        <form action="{{ route('seller.listings.publish', $listing->property) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="rounded-lg px-2.5 py-1 text-[11px] font-bold {{ $listing->status === 'published' ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' }}">
                                                {{ $listing->status === 'published' ? 'Pause' : 'Publish' }}
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-xs text-slate-400">No active listings created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. Inbound Leads, Offers & Inquiries --}}
    <div id="leads-inquiries" data-section="leads-inquiries" class="section-panel hidden scroll-mt-6 grid gap-6 md:grid-cols-2">
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Buyer Leads &amp; Offers</h3>
            <div class="mt-4 space-y-3">
                @forelse ($leads->take(5) as $lead)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-3 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-[#1d3c34]">{{ $lead->buyer_name ?? 'Prospective Buyer' }}</p>
                            <p class="text-slate-500">{{ $lead->property?->title ?? 'Property' }} &bull; Offer: <strong class="text-emerald-700">{{ number_format($lead->offered_price) }} ETB</strong></p>
                        </div>
                        <span class="rounded-full bg-blue-100 text-blue-800 px-2 py-0.5 text-[10px] font-bold">{{ ucfirst($lead->status) }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No buyer leads yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Received Inquiries</h3>
            <div class="mt-4 space-y-3">
                @forelse ($inquiries->take(5) as $inq)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-3 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#1d3c34]">{{ $inq->name }}</span>
                            <span class="text-slate-400 text-[10px]">{{ $inq->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-slate-600 mt-1 line-clamp-1">"{{ $inq->message }}"</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No direct inquiries received yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Create Property & Listing Modal --}}
    <div x-show="openCreateModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 p-4 sm:p-6 md:p-20 backdrop-blur-sm" @keydown.escape.window="openCreateModal = false">
        <div class="relative mx-auto max-w-2xl rounded-3xl bg-white p-6 md:p-8 shadow-2xl border border-[#e7ddca]" @click.outside="openCreateModal = false">
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                <div>
                    <h3 class="text-xl font-black text-[#1d3c34]">Register New Property &amp; Listing</h3>
                    <p class="text-xs text-slate-500">Publish your asset directly to the Ethiopian marketplace</p>
                </div>
                <button type="button" @click="openCreateModal = false" class="rounded-full bg-slate-100 p-2 text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form action="{{ route('seller.listings.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700">Property Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Modern 3-Bedroom Villa in Bole" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Transaction Type *</label>
                        <select name="type" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none">
                            <option value="sale">For Sale</option>
                            <option value="rent">For Rent</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Category *</label>
                        <select name="category" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none">
                            <option value="home">Residential Home</option>
                            <option value="villa">Villa</option>
                            <option value="apartment">Apartment</option>
                            <option value="commercial">Commercial</option>
                            <option value="land">Land</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Price (ETB) *</label>
                        <input type="number" name="price" required min="1" step="any" placeholder="e.g. 15000000" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Bedrooms</label>
                        <input type="number" name="bedrooms" value="3" min="0" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Bathrooms</label>
                        <input type="number" name="bathrooms" value="2" min="1" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Area (sqm) *</label>
                        <input type="number" name="area" value="150" required min="1" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">City *</label>
                        <input type="text" name="city" value="Addis Ababa" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Subcity</label>
                        <input type="text" name="subcity" placeholder="Bole" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Initial Status *</label>
                        <select name="status" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none">
                            <option value="published">Publish Immediately</option>
                            <option value="draft">Save as Draft</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Specific Address / Neighborhood</label>
                    <input type="text" name="address" placeholder="e.g. Near Edna Mall, Woreda 03" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Description</label>
                    <textarea name="description" rows="3" placeholder="Provide details on features, water tank, backup generator, finishings, etc." class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none"></textarea>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-[#e7ddca] pt-4">
                    <button type="button" @click="openCreateModal = false" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 px-6 py-2.5 text-xs font-bold text-white shadow-sm transition">Create &amp; Save Listing</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
