@extends('dashboards.layout')

@section('title', 'Property Owner & Landlord Dashboard')
@section('portal_name', 'Property Owner / Landlord Desk')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>

    @if (auth()->user()?->hasAnyPermission(['properties.create', 'properties.view', 'properties.update', 'properties.delete', 'property.create', 'property.view']))
    <a href="#properties" data-target-section="properties" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>🏠</span>
            <span>My Properties</span>
        </span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $properties->count() }}</span>
    </a>
    @endif

    <a href="#media" data-target-section="media" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>📸</span>
            <span>Media Studio</span>
        </span>
    </a>

    <a href="#documents" data-target-section="documents" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>📜</span>
            <span>Document Vault</span>
        </span>
    </a>

    <a href="#leads-offers" data-target-section="leads-offers" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>🤝</span>
            <span>Offers &amp; Leads</span>
        </span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $leads->count() + $inquiries->count() }}</span>
    </a>

    <a href="#viewings" data-target-section="viewings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>👁️</span>
            <span>Viewings</span>
        </span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $viewings->count() }}</span>
    </a>

    <a href="#leases-tenants" data-target-section="leases-tenants" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>🔑</span>
            <span>Leases &amp; Tenants</span>
        </span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $leases->count() }}</span>
    </a>

    <a href="#rent-collection" data-target-section="rent-collection" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>💰</span>
            <span>Rent Collection</span>
        </span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $rentSchedules->count() }}</span>
    </a>

    <a href="#deposits" data-target-section="deposits" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>🛡️</span>
            <span>Security Deposits</span>
        </span>
    </a>

    <a href="#maintenance" data-target-section="maintenance" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>🛠️</span>
            <span>Maintenance</span>
        </span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $maintenanceTickets->count() }}</span>
    </a>
@endsection

@section('content')
<div x-data="{ openCreateModal: false, activePropertyId: {{ $properties->first()?->id ?? 'null' }} }" class="space-y-8">

    {{-- System Status Banners --}}
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs text-emerald-700 hover:text-emerald-900 font-bold">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-sm font-semibold text-rose-900 shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs text-rose-700 hover:text-rose-900 font-bold">&times;</button>
        </div>
    @endif

    {{-- 0. Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- 1. Properties & Full Lifecycle (Publish, Unpublish, Pause, Renew, Edit, Delete) --}}
    <div id="properties" data-section="properties" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#e7ddca] pb-4">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34]">My Registered Properties &amp; Listings</h2>
                <p class="text-xs text-slate-500 mt-0.5">Full lifecycle management: publish, unpublish, pause, renew, update details, or remove.</p>
            </div>
            <button type="button" @click="openCreateModal = true" class="inline-flex items-center gap-1.5 rounded-xl bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition shadow-xs cursor-pointer">
                <span>➕ Register New Property</span>
            </button>
        </div>

        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($properties as $property)
                @php $quality = $scores[$property->id] ?? 50; @endphp
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] overflow-hidden flex flex-col justify-between p-5 hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ match($property->status) { 'published' => 'bg-emerald-100 text-emerald-800 border border-emerald-300', 'paused' => 'bg-amber-100 text-amber-800 border border-amber-300', 'sold' => 'bg-blue-100 text-blue-800 border border-blue-300', default => 'bg-slate-200 text-slate-700' } }}">
                                {{ ucfirst(str_replace('_', ' ', $property->status)) }}
                            </span>
                            <span class="text-xs font-bold text-slate-500">{{ ucfirst($property->type) }}</span>
                        </div>
                        <h4 class="mt-2.5 font-black text-base text-[#1d3c34] line-clamp-1">{{ $property->title }}</h4>
                        <p class="text-xs text-slate-500 mt-0.5">📍 {{ $property->city }}, {{ $property->subcity }} &bull; <strong class="text-emerald-800 font-mono">{{ number_format($property->price) }} ETB</strong></p>

                        {{-- Quality Score Gauge --}}
                        <div class="mt-4 rounded-xl border border-[#e7ddca] bg-white p-3">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700">Listing Quality Score</span>
                                <span class="font-black {{ $quality >= 80 ? 'text-emerald-700' : ($quality >= 50 ? 'text-amber-700' : 'text-rose-700') }}">{{ $quality }}%</span>
                            </div>
                            <div class="mt-1.5 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full {{ $quality >= 80 ? 'bg-emerald-500' : ($quality >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $quality }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-[#e7ddca] space-y-2">
                        <div class="flex items-center justify-between text-[11px] text-slate-500">
                            <span>{{ $property->images->count() }} photos &bull; {{ $property->documents->count() }} docs</span>
                            <a href="{{ route('properties.show', $property->slug) }}" class="font-bold text-[#1d3c34] hover:underline">Preview &rarr;</a>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-end gap-1.5 flex-wrap pt-2">
                            {{-- Toggle Publish / Unpublish --}}
                            <form action="{{ route('seller.listings.publish', $property) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="rounded-lg px-2.5 py-1 text-[11px] font-bold {{ $property->is_active ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' }} transition cursor-pointer">
                                    {{ $property->is_active ? 'Unpublish' : 'Publish' }}
                                </button>
                            </form>

                            {{-- Pause / Renew Status Transitions --}}
                            @if ($property->status !== 'paused')
                                <form action="{{ route('seller.listings.status', ['property' => $property, 'status' => 'paused']) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-slate-100 hover:bg-slate-200 px-2 py-1 text-[11px] font-bold text-slate-700 transition cursor-pointer">
                                        Pause
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('seller.listings.status', ['property' => $property, 'status' => 'published']) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-emerald-100 hover:bg-emerald-200 px-2 py-1 text-[11px] font-bold text-emerald-800 transition cursor-pointer">
                                        Renew
                                    </button>
                                </form>
                            @endif

                            {{-- Delete Property --}}
                            <form action="{{ route('seller.properties.destroy', $property) }}" method="POST" onsubmit="return confirm('Permanently delete this property and all attached documents/photos?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg bg-rose-50 hover:bg-rose-100 px-2 py-1 text-[11px] font-bold text-rose-700 transition cursor-pointer">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-xs text-slate-400">
                    No properties registered yet. Click "Register New Property" to add your first real estate asset.
                </div>
            @endforelse
        </div>
    </div>

    {{-- 2. Media Studio: Photos Multi-Upload, Reorder, Delete, Set Cover --}}
    <div id="media" data-section="media" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 sm:p-8 shadow-sm">
        <div class="border-b border-[#e7ddca] pb-4">
            <h2 class="text-xl font-black text-[#1d3c34]">Media Studio &amp; Photo Gallery</h2>
            <p class="text-xs text-slate-500 mt-0.5">Upload high-resolution property images, assign cover photos, and curate your visual showcase.</p>
        </div>

        <div class="mt-6 space-y-8">
            @forelse ($properties as $property)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#e7ddca] pb-3">
                        <div>
                            <h3 class="font-black text-sm text-[#1d3c34]">{{ $property->title }}</h3>
                            <span class="text-xs text-slate-500">{{ $property->images->count() }} Photos Uploaded</span>
                        </div>
                        {{-- Upload Form --}}
                        <form action="{{ route('seller.photos.upload', $property) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                            @csrf
                            <input type="file" name="photos[]" multiple accept="image/*" required class="text-xs text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-[#1d3c34] file:px-3 file:py-1 file:text-xs file:font-bold file:text-white hover:file:bg-[#254d43]">
                            <button type="submit" class="rounded-lg bg-emerald-700 hover:bg-emerald-800 px-3 py-1.5 text-xs font-bold text-white transition cursor-pointer">
                                Upload
                            </button>
                        </form>
                    </div>

                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                        @forelse ($property->images as $img)
                            <div class="group relative rounded-xl border border-[#e7ddca] bg-white overflow-hidden shadow-2xs">
                                <img src="{{ asset('storage/' . $img->image_path) }}" alt="Photo" class="h-28 w-full object-cover">
                                @if ($property->image_path === $img->image_path)
                                    <span class="absolute top-1.5 left-1.5 rounded bg-emerald-700 text-white px-1.5 py-0.5 text-[9px] font-bold">
                                        Cover
                                    </span>
                                @endif
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                    @if ($property->image_path !== $img->image_path)
                                        <form action="{{ route('seller.photos.cover', $img) }}" method="POST">
                                            @csrf
                                            <button type="submit" title="Set as Cover" class="rounded bg-white px-2 py-1 text-[10px] font-bold text-[#1d3c34] hover:bg-slate-100">
                                                ★ Cover
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('seller.photos.destroy', $img) }}" method="POST" onsubmit="return confirm('Delete photo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete Photo" class="rounded bg-rose-600 px-2 py-1 text-[10px] font-bold text-white hover:bg-rose-700">
                                            ✕
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="col-span-full py-4 text-center text-xs text-slate-400">No photos in gallery. Upload images to increase listing quality score.</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400 py-4 text-center">No properties available for media management.</p>
            @endforelse
        </div>
    </div>

    {{-- 3. Document Vault: Access Levels & Versioning --}}
    <div id="documents" data-section="documents" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 sm:p-8 shadow-sm">
        <div class="border-b border-[#e7ddca] pb-4">
            <h2 class="text-xl font-black text-[#1d3c34]">Property Document Vault (Access Levels &amp; Versions)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Upload title deeds (Carta), land certificates, tax receipts, and blueprints with granular access control (Public, Verified Only, Private) and version tracking.</p>
        </div>

        <div class="mt-6 space-y-6">
            @forelse ($properties as $property)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#e7ddca] pb-3">
                        <div>
                            <h3 class="font-black text-sm text-[#1d3c34]">{{ $property->title }}</h3>
                            <span class="text-xs text-slate-500">{{ $property->documents->count() }} Legal Documents Registered</span>
                        </div>
                        {{-- Document Upload Modal Trigger --}}
                        <details class="relative inline-block text-left">
                            <summary class="list-none inline-flex items-center gap-1.5 rounded-xl bg-[#1d3c34] px-3.5 py-1.5 text-xs font-bold text-white hover:bg-[#254d43] transition cursor-pointer">
                                <span>➕ Add Legal Document</span>
                            </summary>
                            <div class="absolute right-0 z-20 mt-2 w-96 rounded-2xl border border-[#d9cab3] bg-white p-5 shadow-2xl">
                                <h4 class="font-black text-xs text-[#1d3c34] border-b border-[#e7ddca] pb-2 mb-3">Upload Ownership Document</h4>
                                <form action="{{ route('seller.documents.upload', $property) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-slate-500">Document Type</label>
                                        <select name="document_type" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                                            <option value="title_deed">Carta / Title Deed</option>
                                            <option value="site_plan">Site Plan / Blueprint</option>
                                            <option value="tax_clearance">Tax Clearance Certificate</option>
                                            <option value="power_of_attorney">Power of Attorney</option>
                                            <option value="occupancy_permit">Occupancy Permit</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-slate-500">Document Title</label>
                                        <input type="text" name="title" required placeholder="e.g. Bole Carta 2026 Audit" class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-slate-500">Access Level</label>
                                        <select name="access_level" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                                            <option value="verified_only">Verified Buyers / Officers Only (Recommended)</option>
                                            <option value="private">Private (Owner &amp; Admin Only)</option>
                                            <option value="public">Public (Visible to All)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-slate-500">Document File (PDF / Images)</label>
                                        <input type="file" name="file" required accept=".pdf,image/*" class="mt-1 w-full text-xs text-slate-500">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-slate-500">Version Notes (Optional)</label>
                                        <input type="text" name="notes" placeholder="e.g. Renewed land lease certificate" class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                                    </div>
                                    <div class="flex justify-end pt-2">
                                        <button type="submit" class="rounded-lg bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition cursor-pointer">
                                            Upload Document
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </details>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-[#e7ddca] text-xs">
                            <thead>
                                <tr class="text-left font-bold text-slate-500 uppercase">
                                    <th class="py-2 px-3">Title &amp; Type</th>
                                    <th class="py-2 px-3">Version</th>
                                    <th class="py-2 px-3">Access Level</th>
                                    <th class="py-2 px-3">Verification</th>
                                    <th class="py-2 px-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e7ddca]">
                                @forelse ($property->documents as $doc)
                                    <tr>
                                        <td class="py-2.5 px-3">
                                            <span class="font-bold text-slate-800 block">{{ $doc->title }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $doc->document_type }} &bull; {{ number_format($doc->file_size / 1024, 1) }} KB</span>
                                        </td>
                                        <td class="py-2.5 px-3 font-mono font-bold text-[#1d3c34]">
                                            v{{ $doc->version ?? 1 }}
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ match($doc->access_level) { 'public' => 'bg-emerald-100 text-emerald-800', 'verified_only' => 'bg-blue-100 text-blue-800', default => 'bg-purple-100 text-purple-800' } }}">
                                                {{ ucfirst(str_replace('_', ' ', $doc->access_level)) }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $doc->status === 'verified' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ ucfirst($doc->status) }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                @if ($doc->file_path)
                                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="font-bold text-[#1d3c34] hover:underline">Download</a>
                                                @endif
                                                <form action="{{ route('seller.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete this legal document?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-bold text-rose-600 hover:underline cursor-pointer">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-slate-400">No documents uploaded for this property yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400 py-4 text-center">No properties registered.</p>
            @endforelse
        </div>
    </div>

    {{-- 4. Leads, Inquiries & Formal Offers (Accept / Reject / Counter) --}}
    <div id="leads-offers" data-section="leads-offers" class="section-panel hidden scroll-mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Formal Offers --}}
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Received Offers &amp; Negotiations</h3>
            <div class="mt-4 space-y-4">
                @forelse ($leads as $offer)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-4 text-xs">
                        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-2">
                            <div>
                                <span class="font-mono font-bold text-[#1d3c34]">{{ $offer->offer_reference }}</span>
                                <span class="text-slate-500 block text-[11px]">{{ $offer->property?->title }}</span>
                            </div>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ match($offer->status) { 'accepted' => 'bg-emerald-100 text-emerald-800', 'rejected' => 'bg-rose-100 text-rose-800', 'countered' => 'bg-purple-100 text-purple-800', default => 'bg-blue-100 text-blue-800' } }}">
                                {{ ucfirst($offer->status) }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2 text-slate-600">
                            <div>
                                <span class="text-slate-400 uppercase text-[9px] font-bold">Buyer</span>
                                <p class="font-bold text-slate-800">{{ $offer->buyer_name }}</p>
                                <p class="text-[10px]">{{ $offer->buyer_phone }}</p>
                            </div>
                            <div>
                                <span class="text-slate-400 uppercase text-[9px] font-bold">Offered Price</span>
                                <p class="font-mono font-bold text-emerald-800 text-sm">{{ number_format($offer->offered_price) }} ETB</p>
                                @if ($offer->current_counter_amount)
                                    <p class="text-[10px] text-purple-700">Counter: {{ number_format($offer->current_counter_amount) }} ETB</p>
                                @endif
                            </div>
                        </div>

                        {{-- Accept / Reject / Counter Workflow Actions --}}
                        @if (in_array($offer->status, ['pending', 'countered', 'under_review']))
                            <div class="mt-3 pt-3 border-t border-[#e7ddca] flex items-center justify-end gap-2 flex-wrap">
                                {{-- Counter Form --}}
                                <details class="relative inline-block text-left">
                                    <summary class="list-none rounded-lg border border-purple-300 bg-purple-50 px-2.5 py-1 text-[11px] font-bold text-purple-700 hover:bg-purple-100 transition cursor-pointer">
                                        Counter Offer
                                    </summary>
                                    <div class="absolute right-0 bottom-full z-20 mb-2 w-72 rounded-2xl border border-purple-200 bg-white p-4 shadow-xl">
                                        <h5 class="font-bold text-xs text-[#1d3c34] mb-2">Send Counter Offer</h5>
                                        <form action="{{ route('offers.counter', $offer) }}" method="POST" class="space-y-2">
                                            @csrf
                                            <input type="number" name="counter_price" required min="1" step="any" placeholder="Counter Price (ETB)" class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs">
                                            <textarea name="notes" rows="2" placeholder="Counter explanation..." class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs"></textarea>
                                            <div class="flex justify-end pt-1">
                                                <button type="submit" class="rounded-lg bg-purple-700 text-white px-3 py-1 text-xs font-bold hover:bg-purple-800">Submit Counter</button>
                                            </div>
                                        </form>
                                    </div>
                                </details>

                                {{-- Accept --}}
                                <form action="{{ route('offers.accept', $offer) }}" method="POST" onsubmit="return confirm('Accept this offer? This will initiate contract generation.');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white px-2.5 py-1 text-[11px] font-bold cursor-pointer">
                                        Accept Offer
                                    </button>
                                </form>

                                {{-- Reject --}}
                                <form action="{{ route('offers.reject', $offer) }}" method="POST" onsubmit="return confirm('Reject this offer?');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 px-2.5 py-1 text-[11px] font-bold cursor-pointer">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No offers received yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Direct Inquiries --}}
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Client Inquiries &amp; Direct Replies</h3>
            <div class="mt-4 space-y-4">
                @forelse ($inquiries as $inq)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-4 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-[#1d3c34]">{{ $inq->name }}</span>
                            <span class="text-slate-400 text-[10px]">{{ $inq->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-0.5">{{ $inq->email }} &bull; {{ $inq->phone }}</p>
                        <p class="text-slate-700 mt-2 bg-white rounded-lg p-2.5 border border-[#e7ddca]">"{{ $inq->message }}"</p>

                        @if ($inq->notes)
                            <div class="mt-2 text-[11px] text-slate-600 bg-[#ede3d1]/30 p-2 rounded border border-[#e7ddca]">
                                <strong class="text-[#1d3c34]">Response History:</strong>
                                <p class="mt-0.5 whitespace-pre-line">{{ $inq->notes }}</p>
                            </div>
                        @endif

                        {{-- Reply Form --}}
                        <form action="{{ route('seller.inquiries.reply', $inq) }}" method="POST" class="mt-3 flex items-center gap-2">
                            @csrf
                            <input type="text" name="reply_message" required placeholder="Type reply message to client..." class="flex-1 rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                            <button type="submit" class="rounded-lg bg-[#1d3c34] hover:bg-[#254d43] px-3 py-1.5 text-xs font-bold text-white cursor-pointer">
                                Reply
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No client inquiries received.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 5. Viewing Appointments: Confirm / Reschedule / Cancel --}}
    <div id="viewings" data-section="viewings" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 sm:p-8 shadow-sm">
        <div class="border-b border-[#e7ddca] pb-4">
            <h2 class="text-xl font-black text-[#1d3c34]">Viewing Appointments Schedule</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage inspection appointments requested by verified buyers and tenants.</p>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e7ddca] text-xs">
                <thead>
                    <tr class="bg-[#fbf9f5] text-left font-bold text-slate-500 uppercase">
                        <th class="py-3 px-4">Ref</th>
                        <th class="py-3 px-4">Property</th>
                        <th class="py-3 px-4">Client</th>
                        <th class="py-3 px-4">Requested Date &amp; Time</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca]">
                    @forelse ($viewings as $vw)
                        <tr class="hover:bg-[#fcfaf7]">
                            <td class="py-3 px-4 font-mono font-bold text-[#1d3c34]">{{ $vw->viewing_reference }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $vw->property?->title }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block">{{ $vw->buyer_name }}</span>
                                <span class="text-[10px] text-slate-500">{{ $vw->buyer_phone }}</span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $vw->viewing_date?->format('M d, Y') }} at {{ $vw->viewing_time ?? '2:00 PM' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ match($vw->status) { 'confirmed' => 'bg-emerald-100 text-emerald-800', 'completed' => 'bg-blue-100 text-blue-800', 'cancelled' => 'bg-rose-100 text-rose-800', default => 'bg-amber-100 text-amber-800' } }}">
                                    {{ ucfirst($vw->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if ($vw->status === 'pending')
                                        <form action="{{ route('viewings.update-status', $vw) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="rounded bg-emerald-700 hover:bg-emerald-800 text-white px-2 py-1 text-[10px] font-bold cursor-pointer">Confirm</button>
                                        </form>
                                    @endif
                                    @if ($vw->status !== 'cancelled' && $vw->status !== 'completed')
                                        <form action="{{ route('viewings.update-status', $vw) }}" method="POST" onsubmit="return confirm('Cancel appointment?');" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="rounded bg-rose-50 hover:bg-rose-100 text-rose-700 px-2 py-1 text-[10px] font-bold cursor-pointer">Cancel</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">No viewing appointments booked.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 6. Landlord: Leases & Tenant Management (Create, Renew, Terminate) --}}
    <div id="leases-tenants" data-section="leases-tenants" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#e7ddca] pb-4">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34]">Lease Agreements &amp; Tenant Registry</h2>
                <p class="text-xs text-slate-500 mt-0.5">Create formal lease agreements, extend durations upon renewal, or terminate leases.</p>
            </div>
            {{-- Create Lease Trigger --}}
            <details class="relative inline-block text-left">
                <summary class="list-none inline-flex items-center gap-1.5 rounded-xl bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition cursor-pointer">
                    <span>➕ Create New Lease</span>
                </summary>
                <div class="absolute right-0 z-20 mt-2 w-96 rounded-2xl border border-[#d9cab3] bg-white p-5 shadow-2xl text-left">
                    <h4 class="font-black text-xs text-[#1d3c34] border-b border-[#e7ddca] pb-2 mb-3">Create Formal Tenancy Lease</h4>
                    <form action="{{ route('seller.leases.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500">Rental Property</label>
                            <select name="property_id" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                                @foreach ($properties->where('type', '!=', 'sale') as $p)
                                    <option value="{{ $p->id }}">{{ $p->title }} ({{ number_format($p->price) }} ETB/mo)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500">Tenant Full Name</label>
                            <input type="text" name="tenant_name" required placeholder="e.g. Abebe Bikila" class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500">Tenant Email</label>
                                <input type="email" name="tenant_email" required placeholder="tenant@example.com" class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500">Tenant Phone</label>
                                <input type="text" name="tenant_phone" required placeholder="+251911..." class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500">Start Date</label>
                                <input type="date" name="start_date" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500">End Date</label>
                                <input type="date" name="end_date" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500">Months</label>
                                <input type="number" name="duration_months" value="12" min="1" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500">Monthly Rent</label>
                                <input type="number" name="monthly_rent" required min="1" placeholder="25000" class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500">Deposit</label>
                                <input type="number" name="security_deposit" required min="0" placeholder="50000" class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                            </div>
                        </div>
                        <div class="flex justify-end pt-2">
                            <button type="submit" class="rounded-lg bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition cursor-pointer">
                                Create Draft Lease
                            </button>
                        </div>
                    </form>
                </div>
            </details>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e7ddca] text-xs">
                <thead>
                    <tr class="bg-[#fbf9f5] text-left font-bold text-slate-500 uppercase">
                        <th class="py-3 px-4">Lease Code</th>
                        <th class="py-3 px-4">Property</th>
                        <th class="py-3 px-4">Tenant</th>
                        <th class="py-3 px-4">Term &amp; Dates</th>
                        <th class="py-3 px-4">Rent / Mo</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca]">
                    @forelse ($leases as $lease)
                        <tr class="hover:bg-[#fcfaf7]">
                            <td class="py-3 px-4 font-mono font-bold text-[#1d3c34]">{{ $lease->lease_code }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $lease->property?->title }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block">{{ $lease->tenant_name }}</span>
                                <span class="text-[10px] text-slate-500">{{ $lease->tenant_phone }}</span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $lease->start_date?->format('M Y') }} &rarr; {{ $lease->end_date?->format('M Y') }} ({{ $lease->duration_months }} mo)
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-emerald-800">{{ number_format($lease->monthly_rent) }} ETB</td>
                            <td class="py-3 px-4">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ match($lease->status) { 'active' => 'bg-emerald-100 text-emerald-800', 'terminated' => 'bg-rose-100 text-rose-800', default => 'bg-amber-100 text-amber-800' } }}">
                                    {{ ucfirst($lease->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Renew Form Trigger --}}
                                    <details class="relative inline-block text-left">
                                        <summary class="list-none rounded bg-[#1d3c34] text-white px-2 py-1 text-[10px] font-bold cursor-pointer">
                                            Renew
                                        </summary>
                                        <div class="absolute right-0 bottom-full z-20 mb-2 w-72 rounded-2xl border border-[#d9cab3] bg-white p-4 shadow-xl text-left">
                                            <h5 class="font-bold text-xs text-[#1d3c34] mb-2">Renew Lease</h5>
                                            <form action="{{ route('seller.leases.renew', $lease) }}" method="POST" class="space-y-2">
                                                @csrf
                                                @method('PUT')
                                                <div>
                                                    <label class="block text-[9px] uppercase font-bold text-slate-400">New Start Date</label>
                                                    <input type="date" name="new_start_date" required class="w-full rounded border px-2 py-1 text-xs">
                                                </div>
                                                <div>
                                                    <label class="block text-[9px] uppercase font-bold text-slate-400">New End Date</label>
                                                    <input type="date" name="new_end_date" required class="w-full rounded border px-2 py-1 text-xs">
                                                </div>
                                                <div>
                                                    <label class="block text-[9px] uppercase font-bold text-slate-400">Monthly Rent (ETB)</label>
                                                    <input type="number" name="new_monthly_rent" value="{{ $lease->monthly_rent }}" required class="w-full rounded border px-2 py-1 text-xs">
                                                </div>
                                                <div class="flex justify-end pt-1">
                                                    <button type="submit" class="rounded bg-emerald-700 text-white px-3 py-1 text-xs font-bold">Confirm Renewal</button>
                                                </div>
                                            </form>
                                        </div>
                                    </details>

                                    {{-- Terminate Early --}}
                                    @if ($lease->status === 'active')
                                        <details class="relative inline-block text-left">
                                            <summary class="list-none rounded bg-rose-50 text-rose-700 hover:bg-rose-100 px-2 py-1 text-[10px] font-bold cursor-pointer">
                                                Terminate
                                            </summary>
                                            <div class="absolute right-0 bottom-full z-20 mb-2 w-72 rounded-2xl border border-rose-200 bg-white p-4 shadow-xl text-left">
                                                <h5 class="font-bold text-xs text-rose-800 mb-2">Early Termination</h5>
                                                <form action="{{ route('seller.leases.terminate', $lease) }}" method="POST" class="space-y-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <textarea name="termination_reason" required rows="2" placeholder="Reason for early lease termination..." class="w-full rounded border px-2 py-1 text-xs"></textarea>
                                                    <div class="flex justify-end pt-1">
                                                        <button type="submit" class="rounded bg-rose-700 text-white px-3 py-1 text-xs font-bold">Confirm Terminate</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">No leases registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 7. Rent Collection & Overdue Alerts --}}
    <div id="rent-collection" data-section="rent-collection" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 sm:p-8 shadow-sm">
        <div class="border-b border-[#e7ddca] pb-4">
            <h2 class="text-xl font-black text-[#1d3c34]">Rent Collection Schedule &amp; Overdue Ledger</h2>
            <p class="text-xs text-slate-500 mt-0.5">Track rent payments, mark installments overdue, and record offline cash/bank collections.</p>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e7ddca] text-xs">
                <thead>
                    <tr class="bg-[#fbf9f5] text-left font-bold text-slate-500 uppercase">
                        <th class="py-3 px-4">Lease &amp; Period</th>
                        <th class="py-3 px-4">Property</th>
                        <th class="py-3 px-4">Due Date</th>
                        <th class="py-3 px-4">Amount Due</th>
                        <th class="py-3 px-4">Amount Paid</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca]">
                    @forelse ($rentSchedules as $sch)
                        <tr class="hover:bg-[#fcfaf7]">
                            <td class="py-3 px-4">
                                <span class="font-bold text-[#1d3c34] block">{{ $sch->lease?->lease_code }}</span>
                                <span class="text-[10px] text-slate-500">{{ $sch->period_label ?? ('Installment #' . $sch->installment_number) }}</span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $sch->lease?->property?->title }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $sch->due_date?->format('M d, Y') }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ number_format($sch->amount_due, 2) }} ETB</td>
                            <td class="py-3 px-4 font-mono font-bold text-emerald-800">{{ number_format($sch->amount_paid ?? 0, 2) }} ETB</td>
                            <td class="py-3 px-4">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ match($sch->status) { 'paid' => 'bg-emerald-100 text-emerald-800', 'overdue' => 'bg-rose-100 text-rose-800', default => 'bg-amber-100 text-amber-800' } }}">
                                    {{ ucfirst($sch->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if ($sch->status !== 'paid')
                                        {{-- Record Payment Trigger --}}
                                        <details class="relative inline-block text-left">
                                            <summary class="list-none rounded bg-emerald-700 text-white px-2 py-1 text-[10px] font-bold cursor-pointer">
                                                Record Payment
                                            </summary>
                                            <div class="absolute right-0 bottom-full z-20 mb-2 w-72 rounded-2xl border border-emerald-200 bg-white p-4 shadow-xl text-left">
                                                <h5 class="font-bold text-xs text-[#1d3c34] mb-2">Record Offline Payment</h5>
                                                <form action="{{ route('seller.rent.record', $sch) }}" method="POST" class="space-y-2">
                                                    @csrf
                                                    <div>
                                                        <label class="block text-[9px] uppercase font-bold text-slate-400">Amount Paid (ETB)</label>
                                                        <input type="number" name="amount_paid" value="{{ $sch->amount_due }}" step="any" required class="w-full rounded border px-2 py-1 text-xs">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[9px] uppercase font-bold text-slate-400">Payment Channel</label>
                                                        <select name="payment_method" required class="w-full rounded border px-2 py-1 text-xs">
                                                            <option value="cash">Cash in Person</option>
                                                            <option value="telebirr">Telebirr Transfer</option>
                                                            <option value="cbe_birr">CBE Birr</option>
                                                            <option value="bank_transfer">Direct Bank Wire</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-[9px] uppercase font-bold text-slate-400">Receipt Notes</label>
                                                        <input type="text" name="notes" placeholder="e.g. CBE Ref #9842" class="w-full rounded border px-2 py-1 text-xs">
                                                    </div>
                                                    <div class="flex justify-end pt-1">
                                                        <button type="submit" class="rounded bg-emerald-700 text-white px-3 py-1 text-xs font-bold">Save Payment</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>

                                        {{-- Mark Overdue --}}
                                        <form action="{{ route('seller.rent.mark-late', $sch) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="rounded bg-amber-50 hover:bg-amber-100 text-amber-800 px-2 py-1 text-[10px] font-bold cursor-pointer">
                                                Mark Late
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">No rent collection schedules recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 8. Security Deposits: Held, Deducted with Reason, Returned --}}
    <div id="deposits" data-section="deposits" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 sm:p-8 shadow-sm">
        <div class="border-b border-[#e7ddca] pb-4">
            <h2 class="text-xl font-black text-[#1d3c34]">Security Deposits Management</h2>
            <p class="text-xs text-slate-500 mt-0.5">Track escrowed security deposits, record itemized repair deductions, or release refunds at tenancy conclusion.</p>
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($leases as $lease)
                @php
                    $held = max(0, (float)$lease->security_deposit - (float)$lease->deposit_deductions - (float)$lease->deposit_refunded);
                @endphp
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 text-xs">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-[#1d3c34] text-sm">{{ $lease->lease_code }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ match($lease->deposit_status) { 'returned' => 'bg-emerald-100 text-emerald-800', 'partially_deducted' => 'bg-amber-100 text-amber-800', default => 'bg-blue-100 text-blue-800' } }}">
                                {{ ucfirst(str_replace('_', ' ', $lease->deposit_status ?? 'Held')) }}
                            </span>
                        </div>
                        <p class="text-slate-600 mt-1">Tenant: <strong>{{ $lease->tenant_name }}</strong> &bull; Property: {{ $lease->property?->title }}</p>

                        <div class="mt-3 flex items-center gap-4 text-[11px]">
                            <div>
                                <span class="text-slate-400">Initial Deposit:</span>
                                <span class="font-mono font-bold text-slate-800 ml-1">{{ number_format($lease->security_deposit, 2) }} ETB</span>
                            </div>
                            <div>
                                <span class="text-slate-400">Deductions:</span>
                                <span class="font-mono font-bold text-rose-700 ml-1">{{ number_format($lease->deposit_deductions, 2) }} ETB</span>
                            </div>
                            <div>
                                <span class="text-slate-400">Current Balance:</span>
                                <span class="font-mono font-bold text-emerald-800 ml-1">{{ number_format($held, 2) }} ETB</span>
                            </div>
                        </div>

                        @if ($lease->deposit_deduction_reason)
                            <p class="text-[10px] text-slate-500 mt-2 bg-white rounded p-2 border border-[#e7ddca]">
                                <strong>Deduction Reasons:</strong> {{ $lease->deposit_deduction_reason }}
                            </p>
                        @endif
                    </div>

                    @if ($lease->deposit_status !== 'returned')
                        <div class="flex items-center gap-2 self-end md:self-center">
                            {{-- Deduct Deposit Trigger --}}
                            <details class="relative inline-block text-left">
                                <summary class="list-none rounded-lg bg-amber-100 text-amber-800 px-3 py-1.5 text-xs font-bold hover:bg-amber-200 cursor-pointer">
                                    Deduct for Repairs
                                </summary>
                                <div class="absolute right-0 bottom-full z-20 mb-2 w-80 rounded-2xl border border-amber-200 bg-white p-4 shadow-xl text-left">
                                    <h5 class="font-bold text-xs text-[#1d3c34] mb-2">Deduct Deposit</h5>
                                    <form action="{{ route('seller.deposits.deduct', $lease) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <div>
                                            <label class="block text-[9px] uppercase font-bold text-slate-400">Amount to Deduct (ETB)</label>
                                            <input type="number" name="deduction_amount" max="{{ $held }}" step="any" required class="w-full rounded border px-2 py-1 text-xs">
                                        </div>
                                        <div>
                                            <label class="block text-[9px] uppercase font-bold text-slate-400">Reason / Itemized Repairs</label>
                                            <textarea name="deduction_reason" required rows="2" placeholder="e.g. Broken window latch repair: 1500 ETB" class="w-full rounded border px-2 py-1 text-xs"></textarea>
                                        </div>
                                        <div class="flex justify-end pt-1">
                                            <button type="submit" class="rounded bg-amber-700 text-white px-3 py-1 text-xs font-bold">Apply Deduction</button>
                                        </div>
                                    </form>
                                </div>
                            </details>

                            {{-- Return Deposit Form --}}
                            <form action="{{ route('seller.deposits.return', $lease) }}" method="POST" onsubmit="return confirm('Return remaining security deposit ({{ number_format($held, 2) }} ETB) to tenant?');">
                                @csrf
                                <button type="submit" class="rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white px-3 py-1.5 text-xs font-bold cursor-pointer">
                                    Return Deposit
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-xs text-slate-400 py-4 text-center">No active leases or deposits recorded.</p>
            @endforelse
        </div>
    </div>

    {{-- 9. Maintenance Tickets: Assign Contractor & Complete --}}
    <div id="maintenance" data-section="maintenance" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 sm:p-8 shadow-sm">
        <div class="border-b border-[#e7ddca] pb-4">
            <h2 class="text-xl font-black text-[#1d3c34]">Maintenance Tickets &amp; Contractor Dispatch</h2>
            <p class="text-xs text-slate-500 mt-0.5">Receive repair requests from tenants, assign certified technicians, and sign off on completion.</p>
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($maintenanceTickets as $ticket)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5 text-xs flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-[#1d3c34]">{{ $ticket->ticket_number }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ match($ticket->status) { 'completed' => 'bg-emerald-100 text-emerald-800', 'in_progress' => 'bg-blue-100 text-blue-800', default => 'bg-amber-100 text-amber-800' } }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                            <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-700">{{ ucfirst($ticket->category) }}</span>
                        </div>
                        <h4 class="font-black text-sm text-[#1d3c34] mt-1">{{ $ticket->title }}</h4>
                        <p class="text-slate-600">{{ $ticket->description }}</p>
                        <p class="text-[10px] text-slate-400">Property: <strong>{{ $ticket->property?->title }}</strong> &bull; Tenant: {{ $ticket->user?->name }}</p>

                        @if ($ticket->photo_path)
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $ticket->photo_path) }}" target="_blank" class="font-bold text-[#1d3c34] hover:underline flex items-center gap-1">
                                    <span>📷</span> View Attached Photo Evidence
                                </a>
                            </div>
                        @endif

                        @if ($ticket->location_notes)
                            <p class="text-[10px] text-slate-500 mt-2 bg-white rounded p-2 border border-[#e7ddca]">
                                <strong>Contractor / Status Notes:</strong> {{ $ticket->location_notes }}
                            </p>
                        @endif
                    </div>

                    @if ($ticket->status !== 'completed')
                        <div class="flex items-center gap-2 self-end md:self-start">
                            {{-- Assign Contractor Trigger --}}
                            <details class="relative inline-block text-left">
                                <summary class="list-none rounded-lg bg-[#1d3c34] text-white px-3 py-1.5 text-xs font-bold hover:bg-[#254d43] cursor-pointer">
                                    Assign Contractor
                                </summary>
                                <div class="absolute right-0 bottom-full z-20 mb-2 w-72 rounded-2xl border border-[#d9cab3] bg-white p-4 shadow-xl text-left">
                                    <h5 class="font-bold text-xs text-[#1d3c34] mb-2">Assign Maintenance</h5>
                                    <form action="{{ route('seller.maintenance.assign', $ticket) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <div>
                                            <label class="block text-[9px] uppercase font-bold text-slate-400">Contractor / Technician Name</label>
                                            <input type="text" name="assigned_contractor" required placeholder="e.g. Addis Plumbing Services (+251911...)" class="w-full rounded border px-2 py-1 text-xs">
                                        </div>
                                        <div class="flex justify-end pt-1">
                                            <button type="submit" class="rounded bg-[#1d3c34] text-white px-3 py-1 text-xs font-bold">Assign</button>
                                        </div>
                                    </form>
                                </div>
                            </details>

                            {{-- Mark Complete Trigger --}}
                            <details class="relative inline-block text-left">
                                <summary class="list-none rounded-lg bg-emerald-700 text-white px-3 py-1.5 text-xs font-bold hover:bg-emerald-800 cursor-pointer">
                                    Complete
                                </summary>
                                <div class="absolute right-0 bottom-full z-20 mb-2 w-72 rounded-2xl border border-emerald-200 bg-white p-4 shadow-xl text-left">
                                    <h5 class="font-bold text-xs text-[#1d3c34] mb-2">Mark Work Completed</h5>
                                    <form action="{{ route('seller.maintenance.complete', $ticket) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <div>
                                            <label class="block text-[9px] uppercase font-bold text-slate-400">Resolution Sign-Off Notes</label>
                                            <textarea name="resolution_notes" required rows="2" placeholder="e.g. Pipe repaired, inspected by owner..." class="w-full rounded border px-2 py-1 text-xs"></textarea>
                                        </div>
                                        <div class="flex justify-end pt-1">
                                            <button type="submit" class="rounded bg-emerald-700 text-white px-3 py-1 text-xs font-bold">Complete Ticket</button>
                                        </div>
                                    </form>
                                </div>
                            </details>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-xs text-slate-400 py-4 text-center">No maintenance tickets received.</p>
            @endforelse
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
