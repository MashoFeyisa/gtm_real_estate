@extends('dashboards.layout')

@section('title', 'Property Owner & Landlord Dashboard')
@section('portal_name', 'Property Owner / Landlord Desk')

@section('sidebar-nav')
    @if (auth()->user()?->hasAnyPermission(['properties.create', 'properties.view', 'properties.update', 'properties.delete', 'property.create', 'property.view']))
        <x-sidebar-item href="#properties" icon="🏠" label="My Properties" :count="$properties->count()" />
    @endif

    <x-sidebar-item href="#media" icon="📸" label="Media Studio" />
    <x-sidebar-item href="#documents" icon="📜" label="Document Vault" />
    <x-sidebar-item href="#leads-offers" icon="🤝" label="Offers & Leads" :count="$leads->count() + $inquiries->count()" />
    <x-sidebar-item href="#viewings" icon="👁️" label="Viewings" :count="$viewings->count()" />
    <x-sidebar-item href="#leases-tenants" icon="🔑" label="Leases & Tenants" :count="$leases->count()" />
    <x-sidebar-item href="#rent-collection" icon="💰" label="Rent Collection" :count="$rentSchedules->count()" />
    <x-sidebar-item href="#deposits" icon="🛡️" label="Security Deposits" />
    <x-sidebar-item href="#maintenance" icon="🛠️" label="Maintenance" :count="$maintenanceTickets->count()" />
@endsection

@section('content')
<div x-data="{ openCreateModal: false, activePropertyId: {{ $properties->first()?->id ?? 'null' }} }" class="space-y-8">

    {{-- System Status Banners --}}
    @if (session('success'))
        <div class="token-alert token-alert-success flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-bold">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="token-alert token-alert-danger flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-bold">&times;</button>
        </div>
    @endif

    {{-- 0. Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- 1. Properties & Full Lifecycle (Publish, Unpublish, Pause, Renew, Edit, Delete) --}}
    <div id="properties" data-section="properties" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[var(--token-border-subtle)] pb-4">
            <div>
                <h2 class="text-xl font-black">My Registered Properties &amp; Listings</h2>
                <p class="text-xs token-muted mt-0.5">Full lifecycle management: publish, unpublish, pause, renew, update details, or remove.</p>
            </div>
            <button type="button" @click="openCreateModal = true" class="token-btn token-btn-primary cursor-pointer">
                <span>➕ Register New Property</span>
            </button>
        </div>

        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($properties as $property)
                @php $quality = $scores[$property->id] ?? 50; @endphp
                <div class="token-chart-tile overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="token-badge {{ match($property->status) { 'published' => 'token-badge-success', 'paused' => 'token-badge-warning', 'sold' => 'token-badge-info', default => 'token-badge-neutral' } }}">
                                {{ ucfirst(str_replace('_', ' ', $property->status)) }}
                            </span>
                            <span class="text-xs font-bold token-muted">{{ ucfirst($property->type) }}</span>
                        </div>
                        <h4 class="mt-2.5 font-black text-base line-clamp-1">{{ $property->title }}</h4>
                        <p class="text-xs token-muted mt-0.5">📍 {{ $property->city }}, {{ $property->subcity }} &bull; <strong class="text-[var(--token-success)] font-mono">{{ number_format($property->price) }} ETB</strong></p>

                        {{-- Quality Score Gauge --}}
                        <div class="mt-4 token-chart-tile">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold">Listing Quality Score</span>
                                <span class="font-black {{ $quality >= 80 ? 'text-[var(--token-success)]' : ($quality >= 50 ? 'text-[var(--token-warning)]' : 'text-[var(--token-danger)]') }}">{{ $quality }}%</span>
                            </div>
                            <div class="mt-1.5 h-2 w-full rounded-full bg-[var(--token-surface-subtle)] overflow-hidden">
                                <div class="h-full rounded-full {{ $quality >= 80 ? 'bg-[var(--token-success)]' : ($quality >= 50 ? 'bg-[var(--token-warning)]' : 'bg-[var(--token-danger)]') }}" style="width: {{ $quality }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-[var(--token-border-subtle)] space-y-2">
                        <div class="flex items-center justify-between text-[11px] token-muted">
                            <span>{{ $property->images->count() }} photos &bull; {{ $property->documents->count() }} docs</span>
                            <a href="{{ route('properties.show', $property->slug) }}" class="font-bold hover:underline">Preview &rarr;</a>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-end gap-1.5 flex-wrap pt-2">
                            {{-- Toggle Publish / Unpublish --}}
                            <form action="{{ route('seller.listings.publish', $property) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="token-btn {{ $property->is_active ? 'token-btn-secondary' : 'token-btn-primary' }} !px-2.5 !py-1 !text-[10px] transition cursor-pointer">
                                    {{ $property->is_active ? 'Unpublish' : 'Publish' }}
                                </button>
                            </form>

                            {{-- Pause / Renew Status Transitions --}}
                            @if ($property->status !== 'paused')
                                <form action="{{ route('seller.listings.status', ['property' => $property, 'status' => 'paused']) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="token-btn token-btn-secondary !px-2.5 !py-1 !text-[10px] transition cursor-pointer">
                                        Pause
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('seller.listings.status', ['property' => $property, 'status' => 'published']) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="token-btn token-btn-primary !px-2.5 !py-1 !text-[10px] transition cursor-pointer">
                                        Renew
                                    </button>
                                </form>
                            @endif

                            {{-- Delete Property --}}
                            <form action="{{ route('seller.properties.destroy', $property) }}" method="POST" onsubmit="return confirm('Permanently delete this property and all attached documents/photos?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="token-btn token-btn-danger !px-2.5 !py-1 !text-[10px] transition cursor-pointer">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-xs token-muted">
                    No properties registered yet. Click "Register New Property" to add your first real estate asset.
                </div>
            @endforelse
        </div>
    </div>

    {{-- 2. Media Studio: Photos Multi-Upload, Reorder, Delete, Set Cover --}}
    <div id="media" data-section="media" class="section-panel hidden scroll-mt-6 token-card">
        <div class="border-b border-[var(--token-border-subtle)] pb-4">
            <h2 class="text-xl font-black">Media Studio &amp; Photo Gallery</h2>
            <p class="text-xs token-muted mt-0.5">Upload high-resolution property images, assign cover photos, and curate your visual showcase.</p>
        </div>

        <div class="mt-6 space-y-8">
            @forelse ($properties as $property)
                <div class="token-chart-tile">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[var(--token-border-subtle)] pb-3">
                        <div>
                            <h3 class="font-black text-sm">{{ $property->title }}</h3>
                            <span class="text-xs token-muted">{{ $property->images->count() }} Photos Uploaded</span>
                        </div>
                        {{-- Upload Form --}}
                        <form action="{{ route('seller.photos.upload', $property) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                            @csrf
                            <input type="file" name="photos[]" multiple accept="image/*" required class="text-xs file:mr-2 file:rounded-lg file:border-0 file:bg-[var(--token-primary)] file:px-3 file:py-1 file:text-xs file:font-bold file:text-[var(--token-text-on-color)] hover:file:opacity-90">
                            <button type="submit" class="token-btn token-btn-primary transition cursor-pointer">
                                Upload
                            </button>
                        </form>
                    </div>

                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                        @forelse ($property->images as $img)
                            <div class="group relative rounded-xl border border-[var(--token-border)] bg-[var(--token-surface)] overflow-hidden shadow-2xs">
                                <img src="{{ asset('storage/' . $img->image_path) }}" alt="Photo" class="h-28 w-full object-cover">
                                @if ($property->image_path === $img->image_path)
                                    <span class="absolute top-1.5 left-1.5 rounded px-1.5 py-0.5 text-[9px] font-bold bg-[var(--token-success)] text-[var(--token-text-on-color)]">
                                        Cover
                                    </span>
                                @endif
                                <div class="token-modal-overlay absolute inset-0 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                    @if ($property->image_path !== $img->image_path)
                                        <form action="{{ route('seller.photos.cover', $img) }}" method="POST">
                                            @csrf
                                            <button type="submit" title="Set as Cover" class="token-btn token-btn-secondary !rounded !px-2 !py-1 !text-[10px]">
                                                ★ Cover
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('seller.photos.destroy', $img) }}" method="POST" onsubmit="return confirm('Delete photo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete Photo" class="token-btn token-btn-danger !rounded !px-2 !py-1 !text-[10px]">
                                            ✕
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="col-span-full py-4 text-center text-xs token-muted">No photos in gallery. Upload images to increase listing quality score.</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <p class="text-xs token-muted py-4 text-center">No properties available for media management.</p>
            @endforelse
        </div>
    </div>

    {{-- 3. Document Vault: Access Levels & Versioning --}}
    <div id="documents" data-section="documents" class="section-panel hidden scroll-mt-6 token-card">
        <div class="border-b border-[var(--token-border-subtle)] pb-4">
            <h2 class="text-xl font-black">Property Document Vault (Access Levels &amp; Versions)</h2>
            <p class="text-xs token-muted mt-0.5">Upload title deeds (Carta), land certificates, tax receipts, and blueprints with granular access control (Public, Verified Only, Private) and version tracking.</p>
        </div>

        <div class="mt-6 space-y-6">
            @forelse ($properties as $property)
                <div class="token-chart-tile">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[var(--token-border-subtle)] pb-3">
                        <div>
                            <h3 class="font-black text-sm">{{ $property->title }}</h3>
                            <span class="text-xs token-muted">{{ $property->documents->count() }} Legal Documents Registered</span>
                        </div>
                        {{-- Document Upload Modal Trigger --}}
                        <details class="relative inline-block text-left">
                            <summary class="list-none token-btn token-btn-primary cursor-pointer">
                                <span>➕ Add Legal Document</span>
                            </summary>
                            <div class="token-dropdown absolute right-0 z-20 mt-2 w-96 p-5">
                                <h4 class="font-black text-xs border-b border-[var(--token-border-subtle)] pb-2 mb-3">Upload Ownership Document</h4>
                                <form action="{{ route('seller.documents.upload', $property) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="token-label uppercase">Document Type</label>
                                        <select name="document_type" required class="token-field mt-1">
                                            <option value="title_deed">Carta / Title Deed</option>
                                            <option value="site_plan">Site Plan / Blueprint</option>
                                            <option value="tax_clearance">Tax Clearance Certificate</option>
                                            <option value="power_of_attorney">Power of Attorney</option>
                                            <option value="occupancy_permit">Occupancy Permit</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="token-label uppercase">Document Title</label>
                                        <input type="text" name="title" required placeholder="e.g. Bole Carta 2026 Audit" class="token-field mt-1">
                                    </div>
                                    <div>
                                        <label class="token-label uppercase">Access Level</label>
                                        <select name="access_level" required class="token-field mt-1">
                                            <option value="verified_only">Verified Buyers / Officers Only (Recommended)</option>
                                            <option value="private">Private (Owner &amp; Admin Only)</option>
                                            <option value="public">Public (Visible to All)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="token-label uppercase">Document File (PDF / Images)</label>
                                        <input type="file" name="file" required accept=".pdf,image/*" class="mt-1 w-full text-xs token-muted">
                                    </div>
                                    <div>
                                        <label class="token-label uppercase">Version Notes (Optional)</label>
                                        <input type="text" name="notes" placeholder="e.g. Renewed land lease certificate" class="token-field mt-1">
                                    </div>
                                    <div class="flex justify-end pt-2">
                                        <button type="submit" class="token-btn token-btn-primary cursor-pointer">
                                            Upload Document
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </details>
                    </div>

                    <div class="mt-4 token-table-wrap">
                        <table class="token-table">
                            <thead>
                                <tr>
                                    <th>Title &amp; Type</th>
                                    <th>Version</th>
                                    <th>Access Level</th>
                                    <th>Verification</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($property->documents as $doc)
                                    <tr>
                                        <td>
                                            <span class="font-bold block">{{ $doc->title }}</span>
                                            <span class="text-[10px] token-muted font-mono">{{ $doc->document_type }} &bull; {{ number_format($doc->file_size / 1024, 1) }} KB</span>
                                        </td>
                                        <td class="font-mono font-bold">
                                            v{{ $doc->version ?? 1 }}
                                        </td>
                                        <td>
                                            <span class="token-badge {{ match($doc->access_level) { 'public' => 'token-badge-success', 'verified_only' => 'token-badge-info', default => 'token-badge-accent' } }}">
                                                {{ ucfirst(str_replace('_', ' ', $doc->access_level)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="token-badge {{ $doc->status === 'verified' ? 'token-badge-success' : 'token-badge-warning' }}">
                                                {{ ucfirst($doc->status) }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                @if ($doc->file_path && $doc->isAccessibleBy(auth()->user()))
                                                    <a href="{{ route('media.document', $doc) }}" target="_blank" class="font-bold hover:underline">Download</a>
                                                @endif
                                                <form action="{{ route('seller.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete this legal document?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-bold text-[var(--token-danger)] hover:underline cursor-pointer">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center token-muted">No documents uploaded for this property yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <p class="text-xs token-muted py-4 text-center">No properties registered.</p>
            @endforelse
        </div>
    </div>

    {{-- 4. Leads, Inquiries & Formal Offers (Accept / Reject / Counter) --}}
    <div id="leads-offers" data-section="leads-offers" class="section-panel hidden scroll-mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Formal Offers --}}
        <div class="token-card">
            <h3 class="token-section-title">Received Offers &amp; Negotiations</h3>
            <div class="mt-4 space-y-4">
                @forelse ($leads as $offer)
                    <div class="token-chart-tile text-xs">
                        <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-2">
                            <div>
                                <span class="font-mono font-bold">{{ $offer->offer_reference }}</span>
                                <span class="token-muted block text-[11px]">{{ $offer->property?->title }}</span>
                            </div>
                            <span class="token-badge {{ match($offer->status) { 'accepted' => 'token-badge-success', 'rejected' => 'token-badge-danger', 'countered' => 'token-badge-accent', default => 'token-badge-info' } }}">
                                {{ ucfirst($offer->status) }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <div>
                                <span class="token-muted uppercase text-[9px] font-bold">Buyer</span>
                                <p class="font-bold">{{ $offer->buyer_name }}</p>
                                <p class="text-[10px]">{{ $offer->buyer_phone }}</p>
                            </div>
                            <div>
                                <span class="token-muted uppercase text-[9px] font-bold">Offered Price</span>
                                <p class="font-mono font-bold text-[var(--token-success)] text-sm">{{ number_format($offer->offered_price) }} ETB</p>
                                @if ($offer->current_counter_amount)
                                    <p class="text-[10px] text-[var(--token-accent-stat)]">Counter: {{ number_format($offer->current_counter_amount) }} ETB</p>
                                @endif
                            </div>
                        </div>

                        {{-- Accept / Reject / Counter Workflow Actions --}}
                        @if (in_array($offer->status, ['pending', 'countered', 'under_review']))
                            <div class="mt-3 pt-3 border-t border-[var(--token-border-subtle)] flex items-center justify-end gap-2 flex-wrap">
                                {{-- Counter Form --}}
                                <details class="relative inline-block text-left">
                                    <summary class="list-none token-btn token-btn-secondary !px-2.5 !py-1 !text-[11px] transition cursor-pointer">
                                        Counter Offer
                                    </summary>
                                    <div class="token-dropdown absolute right-0 bottom-full z-20 mb-2 w-72 p-4">
                                        <h5 class="font-bold text-xs mb-2">Send Counter Offer</h5>
                                        <form action="{{ route('offers.counter', $offer) }}" method="POST" class="space-y-2">
                                            @csrf
                                            <input type="number" name="counter_price" required min="1" step="any" placeholder="Counter Price (ETB)" class="token-field w-full">
                                            <textarea name="notes" rows="2" placeholder="Counter explanation..." class="token-field w-full"></textarea>
                                            <div class="flex justify-end pt-1">
                                                <button type="submit" class="token-btn token-btn-primary">Submit Counter</button>
                                            </div>
                                        </form>
                                    </div>
                                </details>

                                {{-- Accept --}}
                                <form action="{{ route('offers.accept', $offer) }}" method="POST" onsubmit="return confirm('Accept this offer? This will initiate contract generation.');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="token-btn token-btn-primary !px-2.5 !py-1 !text-[11px] cursor-pointer">
                                        Accept Offer
                                    </button>
                                </form>

                                {{-- Reject --}}
                                <form action="{{ route('offers.reject', $offer) }}" method="POST" onsubmit="return confirm('Reject this offer?');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="token-btn token-btn-danger !px-2.5 !py-1 !text-[11px] cursor-pointer">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-xs token-muted py-4 text-center">No offers received yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Direct Inquiries --}}
        <div class="token-card">
            <h3 class="token-section-title">Client Inquiries &amp; Direct Replies</h3>
            <div class="mt-4 space-y-4">
                @forelse ($inquiries as $inq)
                    <div class="token-chart-tile text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm">{{ $inq->name }}</span>
                            <span class="token-muted text-[10px]">{{ $inq->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-[10px] token-muted mt-0.5">{{ $inq->email }} &bull; {{ $inq->phone }}</p>
                        <p class="mt-2 rounded-lg p-2.5 border border-[var(--token-border)] bg-[var(--token-surface)]">"{{ $inq->message }}"</p>

                        @if ($inq->notes)
                            <div class="mt-2 text-[11px] p-2 rounded border border-[var(--token-border-subtle)] bg-[var(--token-surface-subtle)]">
                                <strong>Response History:</strong>
                                <p class="mt-0.5 whitespace-pre-line">{{ $inq->notes }}</p>
                            </div>
                        @endif

                        {{-- Reply Form --}}
                        <form action="{{ route('seller.inquiries.reply', $inq) }}" method="POST" class="mt-3 flex items-center gap-2">
                            @csrf
                            <input type="text" name="reply_message" required placeholder="Type reply message to client..." class="flex-1 token-field">
                            <button type="submit" class="token-btn token-btn-primary cursor-pointer">
                                Reply
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs token-muted py-4 text-center">No client inquiries received.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 5. Viewing Appointments: Confirm / Reschedule / Cancel --}}
    <div id="viewings" data-section="viewings" class="section-panel hidden scroll-mt-6 token-card">
        <div class="border-b border-[var(--token-border-subtle)] pb-4">
            <h2 class="text-xl font-black">Viewing Appointments Schedule</h2>
            <p class="text-xs token-muted mt-0.5">Manage inspection appointments requested by verified buyers and tenants.</p>
        </div>

        <div class="mt-6 token-table-wrap">
            <table class="token-table">
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Property</th>
                        <th>Client</th>
                        <th>Requested Date &amp; Time</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($viewings as $vw)
                        <tr>
                            <td class="font-mono font-bold">{{ $vw->viewing_reference }}</td>
                            <td class="font-semibold">{{ $vw->property?->title }}</td>
                            <td>
                                <span class="font-bold block">{{ $vw->buyer_name }}</span>
                                <span class="text-[10px] token-muted">{{ $vw->buyer_phone }}</span>
                            </td>
                            <td>
                                {{ $vw->viewing_date?->format('M d, Y') }} at {{ $vw->viewing_time ?? '2:00 PM' }}
                            </td>
                            <td>
                                <span class="token-badge {{ match($vw->status) { 'confirmed' => 'token-badge-success', 'completed' => 'token-badge-info', 'cancelled' => 'token-badge-danger', default => 'token-badge-warning' } }}">
                                    {{ ucfirst($vw->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if ($vw->status === 'pending')
                                        <form action="{{ route('viewings.update-status', $vw) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="token-btn token-btn-primary !px-2 !py-1 !text-[10px] cursor-pointer">Confirm</button>
                                        </form>
                                    @endif
                                    @if ($vw->status !== 'cancelled' && $vw->status !== 'completed')
                                        <form action="{{ route('viewings.update-status', $vw) }}" method="POST" onsubmit="return confirm('Cancel appointment?');" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="token-btn token-btn-danger !px-2 !py-1 !text-[10px] cursor-pointer">Cancel</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center token-muted">No viewing appointments booked.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 6. Landlord: Leases & Tenant Management (Create, Renew, Terminate) --}}
    <div id="leases-tenants" data-section="leases-tenants" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[var(--token-border-subtle)] pb-4">
            <div>
                <h2 class="text-xl font-black">Lease Agreements &amp; Tenant Registry</h2>
                <p class="text-xs token-muted mt-0.5">Create formal lease agreements, extend durations upon renewal, or terminate leases.</p>
            </div>
            {{-- Create Lease Trigger --}}
            <details class="relative inline-block text-left">
                <summary class="list-none token-btn token-btn-primary cursor-pointer">
                    <span>➕ Create New Lease</span>
                </summary>
                <div class="token-dropdown absolute right-0 z-20 mt-2 w-96 p-5 text-left">
                    <h4 class="font-black text-xs border-b border-[var(--token-border-subtle)] pb-2 mb-3">Create Formal Tenancy Lease</h4>
                    <form action="{{ route('seller.leases.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="token-label uppercase">Rental Property</label>
                            <select name="property_id" required class="token-field mt-1">
                                @foreach ($properties->where('type', '!=', 'sale') as $p)
                                    <option value="{{ $p->id }}">{{ $p->title }} ({{ number_format($p->price) }} ETB/mo)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="token-label uppercase">Tenant Full Name</label>
                            <input type="text" name="tenant_name" required placeholder="e.g. Abebe Bikila" class="token-field mt-1">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="token-label uppercase">Tenant Email</label>
                                <input type="email" name="tenant_email" required placeholder="tenant@example.com" class="token-field mt-1">
                            </div>
                            <div>
                                <label class="token-label uppercase">Tenant Phone</label>
                                <input type="text" name="tenant_phone" required placeholder="+251911..." class="token-field mt-1">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="token-label uppercase">Start Date</label>
                                <input type="date" name="start_date" required class="token-field mt-1">
                            </div>
                            <div>
                                <label class="token-label uppercase">End Date</label>
                                <input type="date" name="end_date" required class="token-field mt-1">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="token-label uppercase">Months</label>
                                <input type="number" name="duration_months" value="12" min="1" required class="token-field mt-1">
                            </div>
                            <div>
                                <label class="token-label uppercase">Monthly Rent</label>
                                <input type="number" name="monthly_rent" required min="1" placeholder="25000" class="token-field mt-1">
                            </div>
                            <div>
                                <label class="token-label uppercase">Deposit</label>
                                <input type="number" name="security_deposit" required min="0" placeholder="50000" class="token-field mt-1">
                            </div>
                        </div>
                        <div class="flex justify-end pt-2">
                            <button type="submit" class="token-btn token-btn-primary cursor-pointer">
                                Create Draft Lease
                            </button>
                        </div>
                    </form>
                </div>
            </details>
        </div>

        <div class="mt-6 token-table-wrap">
            <table class="token-table">
                <thead>
                    <tr>
                        <th>Lease Code</th>
                        <th>Property</th>
                        <th>Tenant</th>
                        <th>Term &amp; Dates</th>
                        <th>Rent / Mo</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leases as $lease)
                        <tr>
                            <td class="font-mono font-bold">{{ $lease->lease_code }}</td>
                            <td class="font-semibold">{{ $lease->property?->title }}</td>
                            <td>
                                <span class="font-bold block">{{ $lease->tenant_name }}</span>
                                <span class="text-[10px] token-muted">{{ $lease->tenant_phone }}</span>
                            </td>
                            <td>
                                {{ $lease->start_date?->format('M Y') }} &rarr; {{ $lease->end_date?->format('M Y') }} ({{ $lease->duration_months }} mo)
                            </td>
                            <td class="font-mono font-bold text-[var(--token-success)]">{{ number_format($lease->monthly_rent) }} ETB</td>
                            <td>
                                <span class="token-badge {{ match($lease->status) { 'active' => 'token-badge-success', 'terminated' => 'token-badge-danger', default => 'token-badge-warning' } }}">
                                    {{ ucfirst($lease->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Renew Form Trigger --}}
                                    <details class="relative inline-block text-left">
                                        <summary class="list-none token-btn token-btn-primary !px-2 !py-1 !text-[10px] cursor-pointer">
                                            Renew
                                        </summary>
                                        <div class="token-dropdown absolute right-0 bottom-full z-20 mb-2 w-72 p-4 text-left">
                                            <h5 class="font-bold text-xs mb-2">Renew Lease</h5>
                                            <form action="{{ route('seller.leases.renew', $lease) }}" method="POST" class="space-y-2">
                                                @csrf
                                                @method('PUT')
                                                <div>
                                                    <label class="token-label uppercase">New Start Date</label>
                                                    <input type="date" name="new_start_date" required class="token-field w-full">
                                                </div>
                                                <div>
                                                    <label class="token-label uppercase">New End Date</label>
                                                    <input type="date" name="new_end_date" required class="token-field w-full">
                                                </div>
                                                <div>
                                                    <label class="token-label uppercase">Monthly Rent (ETB)</label>
                                                    <input type="number" name="new_monthly_rent" value="{{ $lease->monthly_rent }}" required class="token-field w-full">
                                                </div>
                                                <div class="flex justify-end pt-1">
                                                    <button type="submit" class="token-btn token-btn-primary">Confirm Renewal</button>
                                                </div>
                                            </form>
                                        </div>
                                    </details>

                                    {{-- Terminate Early --}}
                                    @if ($lease->status === 'active')
                                        <details class="relative inline-block text-left">
                                            <summary class="list-none token-btn token-btn-danger !px-2 !py-1 !text-[10px] cursor-pointer">
                                                Terminate
                                            </summary>
                                            <div class="token-dropdown absolute right-0 bottom-full z-20 mb-2 w-72 p-4 text-left">
                                                <h5 class="font-bold text-xs text-[var(--token-danger)] mb-2">Early Termination</h5>
                                                <form action="{{ route('seller.leases.terminate', $lease) }}" method="POST" class="space-y-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <textarea name="termination_reason" required rows="2" placeholder="Reason for early lease termination..." class="token-field w-full"></textarea>
                                                    <div class="flex justify-end pt-1">
                                                        <button type="submit" class="token-btn token-btn-danger">Confirm Terminate</button>
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
                            <td colspan="7" class="text-center token-muted">No leases registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 7. Rent Collection & Overdue Alerts --}}
    <div id="rent-collection" data-section="rent-collection" class="section-panel hidden scroll-mt-6 token-card">
        <div class="border-b border-[var(--token-border-subtle)] pb-4">
            <h2 class="text-xl font-black">Rent Collection Schedule &amp; Overdue Ledger</h2>
            <p class="text-xs token-muted mt-0.5">Track rent payments, mark installments overdue, and record offline cash/bank collections.</p>
        </div>

        <div class="mt-6 token-table-wrap">
            <table class="token-table">
                <thead>
                    <tr>
                        <th>Lease &amp; Period</th>
                        <th>Property</th>
                        <th>Due Date</th>
                        <th>Amount Due</th>
                        <th>Amount Paid</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rentSchedules as $sch)
                        <tr>
                            <td>
                                <span class="font-bold block">{{ $sch->lease?->lease_code }}</span>
                                <span class="text-[10px] token-muted">{{ $sch->period_label ?? ('Installment #' . $sch->installment_number) }}</span>
                            </td>
                            <td class="font-semibold">{{ $sch->lease?->property?->title }}</td>
                            <td>{{ $sch->due_date?->format('M d, Y') }}</td>
                            <td class="font-mono font-bold">{{ number_format($sch->amount_due, 2) }} ETB</td>
                            <td class="font-mono font-bold text-[var(--token-success)]">{{ number_format($sch->amount_paid ?? 0, 2) }} ETB</td>
                            <td>
                                <span class="token-badge {{ match($sch->status) { 'paid' => 'token-badge-success', 'overdue' => 'token-badge-danger', default => 'token-badge-warning' } }}">
                                    {{ ucfirst($sch->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if ($sch->status !== 'paid')
                                        {{-- Record Payment Trigger --}}
                                        <details class="relative inline-block text-left">
                                            <summary class="list-none token-btn token-btn-primary !px-2 !py-1 !text-[10px] cursor-pointer">
                                                Record Payment
                                            </summary>
                                            <div class="token-dropdown absolute right-0 bottom-full z-20 mb-2 w-72 p-4 text-left">
                                                <h5 class="font-bold text-xs mb-2">Record Offline Payment</h5>
                                                <form action="{{ route('seller.rent.record', $sch) }}" method="POST" class="space-y-2">
                                                    @csrf
                                                    <div>
                                                        <label class="token-label uppercase">Amount Paid (ETB)</label>
                                                        <input type="number" name="amount_paid" value="{{ $sch->amount_due }}" step="any" required class="token-field w-full">
                                                    </div>
                                                    <div>
                                                        <label class="token-label uppercase">Payment Channel</label>
                                                        <select name="payment_method" required class="token-field w-full">
                                                            <option value="cash">Cash in Person</option>
                                                            <option value="telebirr">Telebirr Transfer</option>
                                                            <option value="cbe_birr">CBE Birr</option>
                                                            <option value="bank_transfer">Direct Bank Wire</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="token-label uppercase">Receipt Notes</label>
                                                        <input type="text" name="notes" placeholder="e.g. CBE Ref: 9842" class="token-field w-full">
                                                    </div>
                                                    <div class="flex justify-end pt-1">
                                                        <button type="submit" class="token-btn token-btn-primary">Save Payment</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>

                                        {{-- Mark Overdue --}}
                                        <form action="{{ route('seller.rent.mark-late', $sch) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="token-btn token-btn-secondary !px-2 !py-1 !text-[10px] cursor-pointer">
                                                Mark Late
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center token-muted">No rent collection schedules recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 8. Security Deposits: Held, Deducted with Reason, Returned --}}
    <div id="deposits" data-section="deposits" class="section-panel hidden scroll-mt-6 token-card">
        <div class="border-b border-[var(--token-border-subtle)] pb-4">
            <h2 class="text-xl font-black">Security Deposits Management</h2>
            <p class="text-xs token-muted mt-0.5">Track escrowed security deposits, record itemized repair deductions, or release refunds at tenancy conclusion.</p>
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($leases as $lease)
                @php
                    $held = max(0, (float)$lease->security_deposit - (float)$lease->deposit_deductions - (float)$lease->deposit_refunded);
                @endphp
                <div class="token-chart-tile flex flex-col md:flex-row md:items-center md:justify-between gap-4 text-xs">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-sm">{{ $lease->lease_code }}</span>
                            <span class="token-badge {{ match($lease->deposit_status) { 'returned' => 'token-badge-success', 'partially_deducted' => 'token-badge-warning', default => 'token-badge-info' } }}">
                                {{ ucfirst(str_replace('_', ' ', $lease->deposit_status ?? 'Held')) }}
                            </span>
                        </div>
                        <p class="mt-1">Tenant: <strong>{{ $lease->tenant_name }}</strong> &bull; Property: {{ $lease->property?->title }}</p>

                        <div class="mt-3 flex items-center gap-4 text-[11px]">
                            <div>
                                <span class="token-muted">Initial Deposit:</span>
                                <span class="font-mono font-bold ml-1">{{ number_format($lease->security_deposit, 2) }} ETB</span>
                            </div>
                            <div>
                                <span class="token-muted">Deductions:</span>
                                <span class="font-mono font-bold text-[var(--token-danger)] ml-1">{{ number_format($lease->deposit_deductions, 2) }} ETB</span>
                            </div>
                            <div>
                                <span class="token-muted">Current Balance:</span>
                                <span class="font-mono font-bold text-[var(--token-success)] ml-1">{{ number_format($held, 2) }} ETB</span>
                            </div>
                        </div>

                        @if ($lease->deposit_deduction_reason)
                            <p class="text-[10px] token-muted mt-2 rounded p-2 border border-[var(--token-border)] bg-[var(--token-surface)]">
                                <strong>Deduction Reasons:</strong> {{ $lease->deposit_deduction_reason }}
                            </p>
                        @endif
                    </div>

                    @if ($lease->deposit_status !== 'returned')
                        <div class="flex items-center gap-2 self-end md:self-center">
                            {{-- Deduct Deposit Trigger --}}
                            <details class="relative inline-block text-left">
                                <summary class="list-none token-btn token-btn-secondary !px-3 !py-1.5 !text-xs cursor-pointer">
                                    Deduct for Repairs
                                </summary>
                                <div class="token-dropdown absolute right-0 bottom-full z-20 mb-2 w-80 p-4 text-left">
                                    <h5 class="font-bold text-xs mb-2">Deduct Deposit</h5>
                                    <form action="{{ route('seller.deposits.deduct', $lease) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <div>
                                            <label class="token-label uppercase">Amount to Deduct (ETB)</label>
                                            <input type="number" name="deduction_amount" max="{{ $held }}" step="any" required class="token-field w-full">
                                        </div>
                                        <div>
                                            <label class="token-label uppercase">Reason / Itemized Repairs</label>
                                            <textarea name="deduction_reason" required rows="2" placeholder="e.g. Broken window latch repair: 1500 ETB" class="token-field w-full"></textarea>
                                        </div>
                                        <div class="flex justify-end pt-1">
                                            <button type="submit" class="token-btn token-btn-secondary">Apply Deduction</button>
                                        </div>
                                    </form>
                                </div>
                            </details>

                            {{-- Return Deposit Form --}}
                            <form action="{{ route('seller.deposits.return', $lease) }}" method="POST" onsubmit="return confirm('Return remaining security deposit ({{ number_format($held, 2) }} ETB) to tenant?');">
                                @csrf
                                <button type="submit" class="token-btn token-btn-primary cursor-pointer">
                                    Return Deposit
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-xs token-muted py-4 text-center">No active leases or deposits recorded.</p>
            @endforelse
        </div>
    </div>

    {{-- 9. Maintenance Tickets: Assign Contractor & Complete --}}
    <div id="maintenance" data-section="maintenance" class="section-panel hidden scroll-mt-6 token-card">
        <div class="border-b border-[var(--token-border-subtle)] pb-4">
            <h2 class="text-xl font-black">Maintenance Tickets &amp; Contractor Dispatch</h2>
            <p class="text-xs token-muted mt-0.5">Receive repair requests from tenants, assign certified technicians, and sign off on completion.</p>
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($maintenanceTickets as $ticket)
                <div class="token-chart-tile text-xs flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold">{{ $ticket->ticket_number }}</span>
                            <span class="token-badge {{ match($ticket->status) { 'completed' => 'token-badge-success', 'in_progress' => 'token-badge-info', default => 'token-badge-warning' } }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                            <span class="token-badge token-badge-neutral">{{ ucfirst($ticket->category) }}</span>
                        </div>
                        <h4 class="font-black text-sm mt-1">{{ $ticket->title }}</h4>
                        <p>{{ $ticket->description }}</p>
                        <p class="text-[10px] token-muted">Property: <strong>{{ $ticket->property?->title }}</strong> &bull; Tenant: {{ $ticket->user?->name }}</p>

                        @if ($ticket->photo_path)
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $ticket->photo_path) }}" target="_blank" class="font-bold hover:underline flex items-center gap-1">
                                    <span>📷</span> View Attached Photo Evidence
                                </a>
                            </div>
                        @endif

                        @if ($ticket->location_notes)
                            <p class="text-[10px] token-muted mt-2 rounded p-2 border border-[var(--token-border)] bg-[var(--token-surface)]">
                                <strong>Contractor / Status Notes:</strong> {{ $ticket->location_notes }}
                            </p>
                        @endif
                    </div>

                    @if ($ticket->status !== 'completed')
                        <div class="flex items-center gap-2 self-end md:self-start">
                            {{-- Assign Contractor Trigger --}}
                            <details class="relative inline-block text-left">
                                <summary class="list-none token-btn token-btn-primary !px-3 !py-1.5 !text-xs cursor-pointer">
                                    Assign Contractor
                                </summary>
                                <div class="token-dropdown absolute right-0 bottom-full z-20 mb-2 w-72 p-4 text-left">
                                    <h5 class="font-bold text-xs mb-2">Assign Maintenance</h5>
                                    <form action="{{ route('seller.maintenance.assign', $ticket) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <div>
                                            <label class="token-label uppercase">Contractor / Technician Name</label>
                                            <input type="text" name="assigned_contractor" required placeholder="e.g. Addis Plumbing Services (+251911...)" class="token-field w-full">
                                        </div>
                                        <div class="flex justify-end pt-1">
                                            <button type="submit" class="token-btn token-btn-primary">Assign</button>
                                        </div>
                                    </form>
                                </div>
                            </details>

                            {{-- Mark Complete Trigger --}}
                            <details class="relative inline-block text-left">
                                <summary class="list-none token-btn token-btn-primary !px-3 !py-1.5 !text-xs cursor-pointer">
                                    Complete
                                </summary>
                                <div class="token-dropdown absolute right-0 bottom-full z-20 mb-2 w-72 p-4 text-left">
                                    <h5 class="font-bold text-xs mb-2">Mark Work Completed</h5>
                                    <form action="{{ route('seller.maintenance.complete', $ticket) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <div>
                                            <label class="token-label uppercase">Resolution Sign-Off Notes</label>
                                            <textarea name="resolution_notes" required rows="2" placeholder="e.g. Pipe repaired, inspected by owner..." class="token-field w-full"></textarea>
                                        </div>
                                        <div class="flex justify-end pt-1">
                                            <button type="submit" class="token-btn token-btn-primary">Complete Ticket</button>
                                        </div>
                                    </form>
                                </div>
                            </details>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-xs token-muted py-4 text-center">No maintenance tickets received.</p>
            @endforelse
        </div>
    </div>

    {{-- Create Property & Listing Modal --}}
    <div x-show="openCreateModal" style="display: none;" class="token-modal-overlay fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20 backdrop-blur-sm" @keydown.escape.window="openCreateModal = false">
        <div class="relative mx-auto max-w-2xl token-card shadow-2xl" @click.outside="openCreateModal = false">
            <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-4">
                <div>
                    <h3 class="text-xl font-black">Register New Property &amp; Listing</h3>
                    <p class="text-xs token-muted">Publish your asset directly to the Ethiopian marketplace</p>
                </div>
                <button type="button" @click="openCreateModal = false" class="rounded-full p-2 bg-[var(--token-surface-subtle)] token-muted hover:opacity-80">✕</button>
            </div>

            <form action="{{ route('seller.listings.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="token-label">Property Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Modern 3-Bedroom Villa in Bole" class="token-field mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="token-label">Transaction Type *</label>
                        <select name="type" required class="token-field mt-1">
                            <option value="sale">For Sale</option>
                            <option value="rent">For Rent</option>
                        </select>
                    </div>
                    <div>
                        <label class="token-label">Category *</label>
                        <select name="category" required class="token-field mt-1">
                            <option value="home">Residential Home</option>
                            <option value="villa">Villa</option>
                            <option value="apartment">Apartment</option>
                            <option value="commercial">Commercial</option>
                            <option value="land">Land</option>
                        </select>
                    </div>
                    <div>
                        <label class="token-label">Price (ETB) *</label>
                        <input type="number" name="price" required min="1" step="any" placeholder="e.g. 15000000" class="token-field mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="token-label">Bedrooms</label>
                        <input type="number" name="bedrooms" value="3" min="0" class="token-field mt-1" />
                    </div>
                    <div>
                        <label class="token-label">Bathrooms</label>
                        <input type="number" name="bathrooms" value="2" min="1" class="token-field mt-1" />
                    </div>
                    <div>
                        <label class="token-label">Area (sqm) *</label>
                        <input type="number" name="area" value="150" required min="1" class="token-field mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="token-label">City *</label>
                        <input type="text" name="city" value="Addis Ababa" required class="token-field mt-1" />
                    </div>
                    <div>
                        <label class="token-label">Subcity</label>
                        <input type="text" name="subcity" placeholder="Bole" class="token-field mt-1" />
                    </div>
                    <div>
                        <label class="token-label">Initial Status *</label>
                        <select name="status" required class="token-field mt-1">
                            <option value="published">Publish Immediately</option>
                            <option value="draft">Save as Draft</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="token-label">Specific Address / Neighborhood</label>
                    <input type="text" name="address" placeholder="e.g. Near Edna Mall, Woreda 03" class="token-field mt-1" />
                </div>

                <div>
                    <label class="token-label">Description</label>
                    <textarea name="description" rows="3" placeholder="Provide details on features, water tank, backup generator, finishings, etc." class="token-field mt-1"></textarea>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-[var(--token-border-subtle)] pt-4">
                    <button type="button" @click="openCreateModal = false" class="token-btn token-btn-ghost">Cancel</button>
                    <button type="submit" class="token-btn token-btn-primary shadow-sm transition">Create &amp; Save Listing</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
