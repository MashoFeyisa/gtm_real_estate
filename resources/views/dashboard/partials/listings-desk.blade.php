{{-- Marketplace Listings Desk for GTM Real Estate Company --}}
<section id="listings" data-section="listings" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[11px] font-black uppercase tracking-wider text-[#d4af37] border border-[#d4af37]/40">Market Inventory</span>
                <h2 class="text-2xl md:text-3xl font-black text-[#1d3c34]">Marketplace Listings</h2>
            </div>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">
                Oversee all published properties, rentals, and short-stay listings across company branches.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-[#f8f3eb] px-3 py-1 text-xs font-bold text-[#1d3c34] border border-[#ecd9be]">
                Total: {{ $listings->count() }}
            </span>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                Published: {{ $listings->whereIn('status', ['published', 'active'])->count() }}
            </span>
            <button type="button" onclick="if (typeof switchCompanySection === 'function') { switchCompanySection('properties'); } else { window.location.hash = 'properties'; }" class="rounded-full bg-[#1d3c34] px-5 py-2 text-xs md:text-sm font-bold text-white transition hover:bg-[#254d43] shadow-sm ml-2 cursor-pointer">
                + New Listing / Property
            </button>
        </div>
    </div>

    {{-- Top KPI Cards for Listings --}}
    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl border border-[#ecd9be] bg-[#fbf8f2] p-4 text-center">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">Total Listings</span>
            <p class="mt-1 text-2xl font-black text-[#1d3c34]">{{ $listings->count() }}</p>
        </div>
        <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-4 text-center">
            <span class="text-[10px] font-black uppercase tracking-wider text-blue-800">For Sale</span>
            <p class="mt-1 text-2xl font-black text-blue-900">{{ $listings->where('listing_type', 'sale')->count() }}</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4 text-center">
            <span class="text-[10px] font-black uppercase tracking-wider text-[#9b6c17]">Long-Term Rent</span>
            <p class="mt-1 text-2xl font-black text-[#9b6c17]">{{ $listings->where('listing_type', 'rent')->count() }}</p>
        </div>
        <div class="rounded-2xl border border-purple-200 bg-purple-50/40 p-4 text-center">
            <span class="text-[10px] font-black uppercase tracking-wider text-purple-800">Short-Stay Stays</span>
            <p class="mt-1 text-2xl font-black text-purple-900">{{ $listings->where('listing_type', 'stay')->count() }}</p>
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#fdfaf5] p-3.5 rounded-2xl border border-[#ebdcc9]">
        <div class="relative flex-1 max-w-md">
            <input type="text" id="dashboard-listings-search" placeholder="Search listings by title, location, type, price..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2 pl-9 pr-3 text-xs text-[#102b25] placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
            <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <div class="flex flex-wrap items-center gap-1.5" id="listings-type-filters">
            <button type="button" onclick="filterListingsByType('all')" class="listing-filter-btn active rounded-xl px-3 py-1.5 text-xs font-bold transition bg-[#1d3c34] text-white">
                All ({{ $listings->count() }})
            </button>
            <button type="button" onclick="filterListingsByType('sale')" class="listing-filter-btn rounded-xl px-3 py-1.5 text-xs font-bold transition bg-white border border-[#d9cab3] text-slate-700 hover:bg-[#fbf6ed]">
                For Sale ({{ $listings->where('listing_type', 'sale')->count() }})
            </button>
            <button type="button" onclick="filterListingsByType('rent')" class="listing-filter-btn rounded-xl px-3 py-1.5 text-xs font-bold transition bg-white border border-[#d9cab3] text-slate-700 hover:bg-[#fbf6ed]">
                Rentals ({{ $listings->where('listing_type', 'rent')->count() }})
            </button>
            <button type="button" onclick="filterListingsByType('stay')" class="listing-filter-btn rounded-xl px-3 py-1.5 text-xs font-bold transition bg-white border border-[#d9cab3] text-slate-700 hover:bg-[#fbf6ed]">
                Short Stays ({{ $listings->where('listing_type', 'stay')->count() }})
            </button>
        </div>
    </div>

    {{-- Listings Data Table --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-[#e7ddca] bg-white shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#fbf8f3] text-[10px] font-black uppercase tracking-wider text-[#587165] border-b border-[#e7ddca]">
                    <tr>
                        <th class="px-4 py-3.5">Property Listing</th>
                        <th class="px-4 py-3.5">Type</th>
                        <th class="px-4 py-3.5">Price / Rate</th>
                        <th class="px-4 py-3.5">Location</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Views</th>
                        <th class="px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0e8d8]">
                    @forelse ($listings as $listing)
                        @php
                            $prop = $listing->property;
                            $thumb = $prop?->cover_image_url ?: asset('images/luxury/hero-skyline.jpg');
                        @endphp
                        <tr class="hover:bg-[#fcfaf7] transition dashboard-listing-row" data-listing-type="{{ $listing->listing_type }}" data-search="{{ strtolower(($prop?->title ?? $listing->headline) . ' ' . $listing->listing_type . ' ' . ($prop?->city ?? '') . ' ' . ($prop?->subcity ?? '')) }}">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $thumb }}" alt="{{ $prop?->title ?? 'Listing' }}" class="h-10 w-12 rounded-lg object-cover ring-1 ring-[#d9cab3] shrink-0">
                                    <div class="min-w-0 max-w-xs">
                                        <span class="font-bold text-sm text-[#1d3c34] block truncate">{{ $prop?->title ?? $listing->headline }}</span>
                                        @if ($listing->headline && $prop && $listing->headline !== $prop->title)
                                            <p class="text-[11px] text-slate-500 truncate">{{ $listing->headline }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($listing->listing_type === 'sale')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 text-blue-900 font-bold px-2.5 py-0.5 text-[10px]">
                                        🏷️ For Sale
                                    </span>
                                @elseif ($listing->listing_type === 'rent')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-900 font-bold px-2.5 py-0.5 text-[10px]">
                                        🔑 Rental
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-purple-100 text-purple-900 font-bold px-2.5 py-0.5 text-[10px]">
                                        🛎️ Short Stay
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-bold text-[#1d3c34]">
                                @if ($listing->price)
                                    <span>{{ number_format($listing->price) }} ETB</span>
                                    <span class="text-[10px] text-slate-400 font-normal">
                                        {{ $listing->listing_type === 'stay' ? '/night' : ($listing->listing_type === 'rent' ? '/mo' : '') }}
                                    </span>
                                @elseif ($prop?->price)
                                    <span>{{ number_format($prop->price) }} ETB</span>
                                @else
                                    <span class="text-slate-400 font-normal">Price on Request</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                <span>{{ $prop?->city ?: 'Addis Ababa' }}</span>
                                @if ($prop?->subcity)
                                    <span class="text-slate-400 text-[11px]">({{ $prop->subcity }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ in_array($listing->status, ['published', 'active']) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucfirst($listing->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center text-slate-500 font-mono text-[11px]">
                                {{ $listing->view_count ?? ($prop?->view_count ?? 0) }}
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if ($prop)
                                        <a href="{{ route('properties.show', $prop->slug ?: $prop->id) }}" target="_blank" class="rounded-lg border border-[#d9cab3] bg-white px-2.5 py-1 text-[11px] font-bold text-[#1d3c34] hover:bg-[#fbf6ed] transition">
                                            View ↗
                                        </a>
                                        <button type="button" onclick="openListingManageModal({{ $listing->id }}, '{{ addslashes($prop->title) }}', '{{ $listing->status }}', '{{ number_format($listing->price ?? $listing->property_price ?? $prop->price, 0) }} ETB', '{{ route('properties.show', $prop->slug ?: $prop->id) }}')" class="rounded-lg bg-[#1d3c34] px-2.5 py-1 text-[11px] font-bold text-white hover:bg-[#254d43] transition cursor-pointer">
                                            Manage ⚙️
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                No marketplace listings found. Create your first property in the Properties &amp; Developments studio.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($listings->hasPages())
            <div class="mt-6 border-t border-[#e7ddca] pt-4">
                {{ $listings->fragment('listings')->links() }}
            </div>
        @endif
    </div>
</section>

{{-- Listing Management Modal --}}
<div id="listingManageModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 p-4 sm:p-6 backdrop-blur-xs flex items-center justify-center">
    <div class="relative w-full max-w-md rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-2xl text-slate-800 animate-fade-in">
        <div class="flex items-start justify-between border-b border-[#f0e4cf] pb-3">
            <div>
                <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Listing Management</span>
                <h3 id="manageListingTitle" class="mt-1 text-lg font-black text-[#1d3c34]">Listing Title</h3>
            </div>
            <button type="button" onclick="closeListingManageModal()" class="rounded-full bg-slate-100 p-2 text-slate-400 hover:bg-slate-200 transition cursor-pointer">
                ✕
            </button>
        </div>

        <div class="mt-4 space-y-4">
            <div class="rounded-2xl bg-[#fcfaf7] p-3 border border-[#ebdcc9] space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-bold">Current Status:</span>
                    <span id="manageListingStatus" class="font-mono font-black uppercase text-emerald-800">ACTIVE</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-bold">List Price:</span>
                    <span id="manageListingPrice" class="font-mono font-black text-[#1d3c34]">ETB 0</span>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <a id="manageListingViewLink" href="#" target="_blank" class="w-full text-center rounded-xl bg-[#102b25] text-white py-2.5 text-xs font-black hover:bg-black transition">
                    View Live Public Page ↗
                </a>
            </div>
        </div>

        <div class="mt-5 pt-3 border-t border-[#f0e4cf] flex justify-end">
            <button type="button" onclick="closeListingManageModal()" class="rounded-xl border border-[#d9cab3] px-4 py-2 text-xs font-bold text-slate-600 hover:bg-[#f3eadc] transition cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    let currentListingTypeFilter = 'all';

    function openListingManageModal(id, title, status, price, viewUrl) {
        document.getElementById('manageListingTitle').textContent = title;
        document.getElementById('manageListingStatus').textContent = status;
        document.getElementById('manageListingPrice').textContent = price;
        document.getElementById('manageListingViewLink').href = viewUrl;
        document.getElementById('listingManageModal').classList.remove('hidden');
    }

    function closeListingManageModal() {
        document.getElementById('listingManageModal').classList.add('hidden');
    }

    function filterListingsByType(type) {
        currentListingTypeFilter = type;
        document.querySelectorAll('.listing-filter-btn').forEach(btn => {
            const isMatch = btn.textContent.toLowerCase().includes(type) || (type === 'all' && btn.textContent.toLowerCase().includes('all'));
            if (isMatch) {
                btn.className = 'listing-filter-btn active rounded-xl px-3 py-1.5 text-xs font-bold transition bg-[#1d3c34] text-white';
            } else {
                btn.className = 'listing-filter-btn rounded-xl px-3 py-1.5 text-xs font-bold transition bg-white border border-[#d9cab3] text-slate-700 hover:bg-[#fbf6ed]';
            }
        });
        applyListingsTableFilter();
    }

    function applyListingsTableFilter() {
        const query = (document.getElementById('dashboard-listings-search')?.value || '').trim().toLowerCase();
        document.querySelectorAll('.dashboard-listing-row').forEach(row => {
            const rowType = row.dataset.listingType || '';
            const searchData = row.dataset.search || '';
            const matchesType = (currentListingTypeFilter === 'all') || (rowType === currentListingTypeFilter);
            const matchesQuery = !query || searchData.includes(query);
            row.style.display = matchesType && matchesQuery ? '' : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('dashboard-listings-search');
        if (searchInput) {
            searchInput.addEventListener('input', applyListingsTableFilter);
        }
    });
</script>
