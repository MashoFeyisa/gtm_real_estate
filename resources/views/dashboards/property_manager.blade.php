@extends('dashboards.layout')

@section('title', 'Property Manager Dashboard')
@section('portal_name', 'Property Operations Management Desk')

@section('sidebar-nav')
    @if (auth()->user()->hasAnyPermission(['units.manage', 'units.create', 'units.view', 'buildings.manage']))
        <x-sidebar-item href="#units" icon="🏢" label="Units & Occupancy" :count="$totalUnits" />
    @endif
    @if (auth()->user()->hasAnyPermission(['tenants.manage', 'tenants.view', 'guests.view']))
        <x-sidebar-item href="#tenants-guests" icon="🧑‍🤝‍🧑" label="Tenants & Guests" :count="$tenants->count() + $guests->count()" />
    @endif
    @if (auth()->user()->hasAnyPermission(['rent.manage', 'rent.view', 'payments.manage']))
        <x-sidebar-item href="#rent-collection" icon="💳" label="Rent & Collections" :count="$rentSchedules->count()" />
    @endif
    @if (auth()->user()->hasAnyPermission(['housekeeping.manage', 'maintenance.manage', 'maintenance.view']))
        <x-sidebar-item href="#maintenance-cleaning" icon="🧹" label="Maintenance & Tasks" :count="$maintenanceTickets->count() + $cleaningTasks->count()" />
    @endif
@endsection

@section('content')
<div class="space-y-8">
    {{-- Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- Add Unit Modal/Form --}}
    <div id="add-unit-modal" class="hidden token-dropdown rounded-3xl p-6">
        <div class="flex items-center justify-between border-b border-[var(--token-border)] pb-3 mb-4">
            <div>
                <h3 class="text-lg font-black">Add New Unit / Property to Portfolio</h3>
                <p class="text-xs token-muted mt-0.5">Register a residential apartment, condominium, or commercial space under active management</p>
            </div>
            <button type="button" onclick="document.getElementById('add-unit-modal').classList.add('hidden')" class="token-btn token-btn-ghost cursor-pointer">&times; Close</button>
        </div>
        <form method="POST" action="{{ route('property_manager.units.store') }}" enctype="multipart/form-data" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
            @csrf
            <div class="sm:col-span-2">
                <label class="token-label">Unit / Property Title *</label>
                <input type="text" name="title" required placeholder="e.g. Sunrise Tower &bull; Unit 402 Luxury 2BR" class="mt-1 token-field !text-xs">
            </div>
            <div>
                <label class="token-label">Property Category *</label>
                <select name="property_category" required class="mt-1 token-field !text-xs">
                    <option value="apartment" selected>Apartment</option>
                    <option value="condominium">Condominium</option>
                    <option value="villa">Villa / House</option>
                    <option value="guest_house">Guest House / Short Stay</option>
                    <option value="office">Commercial / Office</option>
                </select>
            </div>
            <div>
                <label class="token-label">Listing / Operation Type *</label>
                <select name="type" required class="mt-1 token-field !text-xs">
                    <option value="rent" selected>For Rent (Long-Term)</option>
                    <option value="stay">Short Stay (Nightly Turn)</option>
                    <option value="sale">For Sale</option>
                </select>
            </div>
            <div>
                <label class="token-label">Price / Rent Amount (ETB) *</label>
                <input type="number" step="0.01" name="price" required placeholder="e.g. 45000" class="mt-1 token-field !text-xs font-mono font-bold">
            </div>
            <div>
                <label class="token-label">Initial Occupancy Status *</label>
                <select name="status" required class="mt-1 token-field !text-xs">
                    <option value="available" selected>Vacant (Available for Lease)</option>
                    <option value="rented">Occupied (Tenant in Place)</option>
                    <option value="maintenance">Under Turnover / Maintenance</option>
                </select>
            </div>
            <div>
                <label class="token-label">City *</label>
                <input type="text" name="city" value="Addis Ababa" required class="mt-1 token-field !text-xs">
            </div>
            <div>
                <label class="token-label">Subcity / Area</label>
                <input type="text" name="subcity" placeholder="e.g. Bole, Kirkos, CMC" class="mt-1 token-field !text-xs">
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="token-label">Beds</label>
                    <input type="number" name="bedrooms" value="2" min="0" class="mt-1 token-field !text-xs text-center">
                </div>
                <div>
                    <label class="token-label">Baths</label>
                    <input type="number" name="bathrooms" value="2" min="0" class="mt-1 token-field !text-xs text-center">
                </div>
                <div>
                    <label class="token-label">Area m²</label>
                    <input type="number" name="area" value="85" min="0" class="mt-1 token-field !text-xs text-center">
                </div>
            </div>
            <div class="sm:col-span-2 md:col-span-3">
                <label class="token-label">Unit Cover Photo / Image</label>
                <input type="file" name="image" accept="image/*" class="mt-1 token-field !text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[var(--token-primary)] file:text-[var(--token-text-on-color)] hover:file:bg-[var(--token-primary-hover)] cursor-pointer">
                <p class="text-[11px] token-muted mt-1">Upload a real unit photo (JPEG, PNG, WebP up to 20MB)</p>
            </div>
            <div class="sm:col-span-2 md:col-span-3 flex items-center justify-end gap-3 pt-2 border-t border-[var(--token-border)]">
                <button type="button" onclick="document.getElementById('add-unit-modal').classList.add('hidden')" class="token-btn token-btn-ghost cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="token-btn token-btn-primary shadow-sm cursor-pointer">
                    + Register Unit to Portfolio
                </button>
            </div>
        </form>
    </div>

    {{-- Dispatch Cleaning Modal/Form --}}
    <div id="dispatch-cleaning-modal" class="hidden token-dropdown rounded-3xl p-6">
        <div class="flex items-center justify-between border-b border-[var(--token-border)] pb-3 mb-4">
            <h3 class="text-lg font-black">Dispatch Housekeeping / Cleaning Task</h3>
            <button type="button" onclick="document.getElementById('dispatch-cleaning-modal').classList.add('hidden')" class="token-btn token-btn-ghost">&times; Close</button>
        </div>
        <form method="POST" action="{{ route('property_manager.cleaning.dispatch') }}" class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
            @csrf
            <div>
                <label class="token-label">Property / Building *</label>
                <select name="property_id" required class="mt-1 token-field !text-xs">
                    @foreach ($properties as $prop)
                        <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="token-label">Unit / Room Number *</label>
                <input type="text" name="room_number" required placeholder="e.g. Unit 302" class="mt-1 token-field !text-xs">
            </div>
            <div>
                <label class="token-label">Assign Cleaner</label>
                <select name="cleaner_user_id" class="mt-1 token-field !text-xs">
                    <option value="">-- Unassigned (Queue) --</option>
                    @foreach ($cleaners as $cleaner)
                        <option value="{{ $cleaner->id }}">{{ $cleaner->name }} ({{ ucfirst($cleaner->role) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="token-label">Priority</label>
                <select name="priority" class="mt-1 token-field !text-xs">
                    <option value="normal" selected>Normal Turnover</option>
                    <option value="urgent">Urgent (Arrival today)</option>
                    <option value="low">Deep Clean (Routine)</option>
                </select>
            </div>
            <div class="sm:col-span-2 md:col-span-4 flex justify-end">
                <button type="submit" class="token-btn token-btn-primary">
                    Dispatch Task
                </button>
            </div>
        </form>
    </div>

    {{-- File Maintenance Modal/Form --}}
    <div id="file-maintenance-modal" class="hidden token-dropdown rounded-3xl p-6">
        <div class="flex items-center justify-between border-b border-[var(--token-border)] pb-3 mb-4">
            <h3 class="text-lg font-black">File New Maintenance Request</h3>
            <button type="button" onclick="document.getElementById('file-maintenance-modal').classList.add('hidden')" class="token-btn token-btn-ghost">&times; Close</button>
        </div>
        <form method="POST" action="{{ route('maintenance.store') }}" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
            @csrf
            <div>
                <label class="token-label">Property / Unit *</label>
                <select name="property_id" required class="mt-1 token-field !text-xs">
                    @foreach ($properties as $prop)
                        <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="token-label">Category *</label>
                <select name="category" class="mt-1 token-field !text-xs">
                    <option value="plumbing">Plumbing</option>
                    <option value="electrical">Electrical</option>
                    <option value="appliance">Appliance / HVAC</option>
                    <option value="structural">Structural / Locksmith</option>
                    <option value="cleaning">Sanitation & Pest</option>
                    <option value="other">General Facility</option>
                </select>
            </div>
            <div>
                <label class="token-label">Priority *</label>
                <select name="priority" class="mt-1 token-field !text-xs">
                    <option value="low">Low (Routine)</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High (Urgent)</option>
                    <option value="emergency">Emergency</option>
                </select>
            </div>
            <div class="sm:col-span-2 md:col-span-3">
                <label class="token-label">Issue Title *</label>
                <input type="text" name="title" required placeholder="e.g. Master Bedroom AC Unit Leaking Water" class="mt-1 token-field !text-xs">
            </div>
            <div class="sm:col-span-2 md:col-span-3">
                <label class="token-label">Detailed Description *</label>
                <textarea name="description" rows="3" required placeholder="Describe location inside unit, symptoms, and urgency..." class="mt-1 token-field !text-xs"></textarea>
            </div>
            <div class="sm:col-span-2 md:col-span-3 flex justify-end">
                <button type="submit" class="token-btn token-btn-primary">
                    Submit Maintenance Ticket
                </button>
            </div>
        </form>
    </div>

    {{-- 1. Units Inventory Matrix (Occupied vs Vacant) --}}
    <div id="units" data-section="units" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex items-center justify-between border-b border-[var(--token-border)] pb-3">
            <div>
                <h2 class="token-section-title !border-b-0 !pb-0">Units &amp; Occupancy Roll</h2>
                <p class="text-xs token-muted mt-0.5">Buildings, residential units, and commercial spaces under active management</p>
            </div>
            <button type="button" onclick="document.getElementById('add-unit-modal').classList.remove('hidden'); document.getElementById('add-unit-modal').scrollIntoView({behavior: 'smooth'})" class="token-btn token-btn-primary shadow-sm cursor-pointer flex items-center gap-1.5">
                <span class="text-sm font-black text-[var(--token-accent-light)]">+</span>
                <span>Add New Unit</span>
            </button>
        </div>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse ($properties as $unit)
                @php
                    $isOccupied = $unit->status === 'rented' || $unit->leases->where('status', 'active')->count() > 0;
                    $statusBadge = match($unit->status) {
                        'rented' => ['label' => 'Occupied', 'class' => 'token-badge-success'],
                        'maintenance' => ['label' => 'Maintenance', 'class' => 'token-badge-danger'],
                        default => ['label' => $isOccupied ? 'Occupied' : 'Vacant', 'class' => $isOccupied ? 'token-badge-success' : 'token-badge-warning'],
                    };
                @endphp
                <div class="rounded-2xl border border-[var(--token-border)] bg-[var(--token-surface-subtle)] overflow-hidden flex flex-col justify-between hover:border-[var(--token-primary)] transition shadow-xs">
                    <div>
                        {{-- Property Thumbnail --}}
                        <div class="relative h-32 w-full bg-[var(--token-border)] overflow-hidden">
                            @if($unit->primary_image_url)
                                <img src="{{ $unit->primary_image_url }}" alt="{{ $unit->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-[var(--token-primary-surface)] text-[var(--token-text-muted)]">
                                    <span class="text-2xl">🏢</span>
                                </div>
                            @endif
                            <div class="absolute top-2 left-2 flex items-center gap-1.5">
                                <span class="token-badge shadow-xs {{ $statusBadge['class'] }}">
                                    {{ $statusBadge['label'] }}
                                </span>
                            </div>
                            <span class="absolute top-2 right-2 token-badge token-badge-neutral backdrop-blur-xs font-mono">
                                #{{ $unit->id }}
                            </span>
                        </div>

                        <div class="p-3.5">
                            <h4 class="font-bold text-sm line-clamp-1" title="{{ $unit->title }}">{{ $unit->title }}</h4>
                            <p class="text-xs token-muted mt-0.5">📍 {{ $unit->city }}{{ $unit->subcity ? ', ' . $unit->subcity : '' }}</p>
                            <div class="mt-2 flex items-center justify-between text-xs">
                                <span class="font-bold text-sm">{{ number_format($unit->price) }} <span class="text-[10px] font-normal token-muted">ETB</span></span>
                                <span class="text-[11px] token-muted">{{ $unit->bedrooms }}b &bull; {{ $unit->bathrooms }}ba &bull; {{ $unit->area }}m²</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3.5 pt-0 border-t border-[var(--token-border-subtle)] mt-1 flex items-center justify-between text-xs">
                        <button type="button" onclick="document.getElementById('edit-unit-{{ $unit->id }}').classList.remove('hidden')" class="token-btn token-btn-primary cursor-pointer flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            <span>Manage / Edit</span>
                        </button>
                        <a href="{{ route('properties.show', $unit->slug) }}" target="_blank" class="token-btn token-btn-ghost inline-flex items-center gap-1 hover:underline">
                            <span>Details</span>
                            <span>↗</span>
                        </a>
                    </div>
                </div>

                {{-- Edit / Manage Unit Modal --}}
                <div id="edit-unit-{{ $unit->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center token-modal-overlay p-4">
                    <div class="w-full max-w-xl token-dropdown rounded-3xl p-6 max-h-[90vh] overflow-y-auto text-left">
                        <div class="flex items-start justify-between border-b border-[var(--token-border)] pb-3">
                            <div>
                                <span class="text-xs font-mono font-bold token-muted">Unit #{{ $unit->id }}</span>
                                <h3 class="text-lg font-black mt-0.5">Manage Unit: {{ $unit->title }}</h3>
                            </div>
                            <button type="button" onclick="document.getElementById('edit-unit-{{ $unit->id }}').classList.add('hidden')" class="token-btn token-btn-ghost !text-xl cursor-pointer">&times;</button>
                        </div>

                        <form method="POST" action="{{ route('property_manager.units.update', $unit->id) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="token-label">Unit / Property Title *</label>
                                <input type="text" name="title" value="{{ $unit->title }}" required class="mt-1 token-field !text-xs">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="token-label">Occupancy Status *</label>
                                    <select name="status" required class="mt-1 token-field !text-xs">
                                        <option value="available" {{ $unit->status === 'available' ? 'selected' : '' }}>Vacant (Available)</option>
                                        <option value="rented" {{ $unit->status === 'rented' ? 'selected' : '' }}>Occupied (Rented)</option>
                                        <option value="maintenance" {{ $unit->status === 'maintenance' ? 'selected' : '' }}>Under Turnover / Maintenance</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="token-label">Price / Rent Amount (ETB) *</label>
                                    <input type="number" step="0.01" name="price" value="{{ $unit->price }}" required class="mt-1 token-field !text-xs font-mono font-bold">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="token-label">Property Category</label>
                                    <select name="property_category" class="mt-1 token-field !text-xs">
                                        <option value="apartment" {{ ($unit->property_category ?? $unit->category) === 'apartment' ? 'selected' : '' }}>Apartment</option>
                                        <option value="condominium" {{ ($unit->property_category ?? $unit->category) === 'condominium' ? 'selected' : '' }}>Condominium</option>
                                        <option value="villa" {{ ($unit->property_category ?? $unit->category) === 'villa' ? 'selected' : '' }}>Villa / House</option>
                                        <option value="guest_house" {{ ($unit->property_category ?? $unit->category) === 'guest_house' ? 'selected' : '' }}>Guest House / Short Stay</option>
                                        <option value="office" {{ ($unit->property_category ?? $unit->category) === 'office' ? 'selected' : '' }}>Commercial / Office</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="token-label">Operation Type</label>
                                    <select name="type" class="mt-1 token-field !text-xs">
                                        <option value="rent" {{ $unit->type === 'rent' ? 'selected' : '' }}>For Rent (Long-Term)</option>
                                        <option value="stay" {{ $unit->type === 'stay' ? 'selected' : '' }}>Short Stay (Nightly)</option>
                                        <option value="sale" {{ $unit->type === 'sale' ? 'selected' : '' }}>For Sale</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="token-label">Beds</label>
                                    <input type="number" name="bedrooms" value="{{ $unit->bedrooms ?? 1 }}" min="0" class="mt-1 token-field !text-xs text-center">
                                </div>
                                <div>
                                    <label class="token-label">Baths</label>
                                    <input type="number" name="bathrooms" value="{{ $unit->bathrooms ?? 1 }}" min="0" class="mt-1 token-field !text-xs text-center">
                                </div>
                                <div>
                                    <label class="token-label">Area m²</label>
                                    <input type="number" name="area" value="{{ $unit->area ?? 75 }}" min="0" class="mt-1 token-field !text-xs text-center">
                                </div>
                            </div>

                            <div>
                                <label class="token-label">Update / Replace Unit Image</label>
                                <input type="file" name="image" accept="image/*" class="mt-1 token-field !text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[var(--token-primary)] file:text-[var(--token-text-on-color)] hover:file:bg-[var(--token-primary-hover)] cursor-pointer">
                                @if($unit->image_path)
                                    <p class="text-[11px] text-[var(--token-success)] mt-1">✓ Current image set: {{ basename($unit->image_path) }}</p>
                                @endif
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-3 border-t border-[var(--token-border)]">
                                <button type="button" onclick="document.getElementById('edit-unit-{{ $unit->id }}').classList.add('hidden')" class="token-btn token-btn-ghost cursor-pointer">
                                    Cancel
                                </button>
                                <button type="submit" class="token-btn token-btn-primary shadow-sm cursor-pointer">
                                    Save Unit Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <p class="col-span-4 text-xs token-muted py-4 text-center">No units assigned.</p>
            @endforelse
        </div>
    </div>

    {{-- 2. Active Tenants & In-House Guests --}}
    <div id="tenants-guests" data-section="tenants-guests" class="section-panel hidden scroll-mt-6 grid gap-6 md:grid-cols-2">
        {{-- Long-Term Tenants --}}
        <div class="token-card">
            <div class="flex items-center justify-between border-b border-[var(--token-border)] pb-3">
                <h3 class="text-lg font-black">Active Tenants (Long-Term)</h3>
                <span class="text-xs font-bold token-muted font-mono">{{ $tenants->count() }} active</span>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($tenants->take(6) as $tenant)
                    <div class="rounded-2xl border border-[var(--token-border)] bg-[var(--token-surface-subtle)] p-3.5 text-xs flex items-center justify-between">
                        <div>
                            <p class="font-bold text-sm">{{ $tenant->tenant_name ?? 'Tenant' }}</p>
                            <p class="token-muted mt-0.5">{{ $tenant->property?->title ?? 'Property' }} &bull; <strong>{{ number_format($tenant->monthly_rent) }} ETB/mo</strong></p>
                            <p class="text-[11px] token-muted mt-0.5">📞 {{ $tenant->tenant_phone ?? 'N/A' }} &bull; {{ $tenant->tenant_email ?? 'N/A' }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1.5">
                            <span class="token-badge token-badge-success">Lease Active</span>
                            <button type="button" onclick="document.getElementById('lease-modal-{{ $tenant->id }}').classList.remove('hidden')" class="token-btn token-btn-ghost cursor-pointer flex items-center gap-1">
                                <span>Lease Terms</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                    </div>

                    {{-- Lease Terms & Details Modal --}}
                    <div id="lease-modal-{{ $tenant->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center token-modal-overlay p-4">
                        <div class="w-full max-w-2xl token-dropdown rounded-3xl p-6 max-h-[90vh] overflow-y-auto text-left">
                            <div class="flex items-start justify-between border-b border-[var(--token-border)] pb-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="token-badge token-badge-success">{{ ucfirst($tenant->status) }}</span>
                                        <span class="text-xs font-mono font-bold token-muted">{{ $tenant->lease_code }}</span>
                                    </div>
                                    <h3 class="text-lg font-black mt-1">{{ $tenant->tenant_name }} &bull; Tenancy Terms &amp; Agreement</h3>
                                </div>
                                <button type="button" onclick="document.getElementById('lease-modal-{{ $tenant->id }}').classList.add('hidden')" class="token-btn token-btn-ghost !text-xl cursor-pointer">&times;</button>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2 text-xs">
                                <div class="rounded-2xl bg-[var(--token-surface-subtle)] p-3.5 border border-[var(--token-border)]">
                                    <p class="font-bold uppercase tracking-wider text-[10px]">Tenant Details</p>
                                    <div class="mt-2 space-y-1">
                                        <p><span class="token-muted">Name:</span> <strong>{{ $tenant->tenant_name }}</strong></p>
                                        <p><span class="token-muted">Phone:</span> <strong>{{ $tenant->tenant_phone ?? 'N/A' }}</strong></p>
                                        <p><span class="token-muted">Email:</span> <strong>{{ $tenant->tenant_email ?? 'N/A' }}</strong></p>
                                        <p><span class="token-muted">ID ({{ ucfirst($tenant->tenant_id_type ?? 'Kebele') }}):</span> <strong class="font-mono">{{ $tenant->tenant_id_number ?? 'N/A' }}</strong></p>
                                        @if($tenant->tenant_occupation)
                                            <p><span class="token-muted">Occupation:</span> <strong>{{ $tenant->tenant_occupation }}</strong></p>
                                        @endif
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-[var(--token-surface-subtle)] p-3.5 border border-[var(--token-border)]">
                                    <p class="font-bold uppercase tracking-wider text-[10px]">Financial Terms</p>
                                    <div class="mt-2 space-y-1">
                                        <p><span class="token-muted">Monthly Rent:</span> <strong class="text-sm">{{ number_format((float) ($tenant->monthly_rent ?? 0)) }} ETB</strong></p>
                                        <p><span class="token-muted">Security Deposit:</span> <strong>{{ number_format((float) ($tenant->security_deposit ?? 0)) }} ETB</strong></p>
                                        <p><span class="token-muted">Payment Frequency:</span> <strong class="uppercase">{{ $tenant->payment_frequency ?? 'Monthly' }}</strong></p>
                                        <p><span class="token-muted">Grace Period:</span> <strong>{{ $tenant->grace_period_days ?? 5 }} Days</strong></p>
                                        <p><span class="token-muted">Late Fee:</span> <strong>{{ $tenant->late_fee_percentage ?? 2 }}%</strong></p>
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-[var(--token-surface-subtle)] p-3.5 border border-[var(--token-border)]">
                                    <p class="font-bold uppercase tracking-wider text-[10px]">Property / Leased Unit</p>
                                    <div class="mt-2 space-y-1">
                                        <p><span class="token-muted">Property:</span> <strong>{{ $tenant->property?->title ?? 'N/A' }}</strong></p>
                                        <p><span class="token-muted">Location:</span> <strong>{{ $tenant->property?->city ?? 'Addis Ababa' }}{{ $tenant->property?->subcity ? ', ' . $tenant->property?->subcity : '' }}</strong></p>
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-[var(--token-surface-subtle)] p-3.5 border border-[var(--token-border)]">
                                    <p class="font-bold uppercase tracking-wider text-[10px]">Term Duration</p>
                                    <div class="mt-2 space-y-1">
                                        <p><span class="token-muted">Duration:</span> <strong>{{ $tenant->duration_months ?? 12 }} Months</strong></p>
                                        <p><span class="token-muted">Start Date:</span> <strong>{{ $tenant->start_date ? \Carbon\Carbon::parse($tenant->start_date)->format('M d, Y') : 'N/A' }}</strong></p>
                                        <p><span class="token-muted">End Date:</span> <strong>{{ $tenant->end_date ? \Carbon\Carbon::parse($tenant->end_date)->format('M d, Y') : 'N/A' }}</strong></p>
                                    </div>
                                </div>
                            </div>

                            @if($tenant->terms_and_conditions || $tenant->notes)
                                <div class="mt-3 token-alert token-alert-warning text-xs">
                                    <p class="font-bold uppercase tracking-wider text-[10px]">Special Clauses &amp; Terms</p>
                                    <p class="mt-1 whitespace-pre-line">{{ $tenant->terms_and_conditions ?: $tenant->notes }}</p>
                                </div>
                            @endif

                            <div class="mt-5 flex items-center justify-between border-t border-[var(--token-border)] pt-4">
                                <button type="button" onclick="document.getElementById('lease-modal-{{ $tenant->id }}').classList.add('hidden')" class="token-btn token-btn-ghost cursor-pointer">
                                    Close
                                </button>
                                <div class="flex items-center gap-2">
                                    @if(Route::has('leases.agreement'))
                                        <a href="{{ route('leases.agreement', $tenant->id) }}" class="token-btn token-btn-primary shadow-sm inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <span>Download Agreement PDF</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs token-muted py-4 text-center">No active tenants on roll.</p>
                @endforelse
            </div>
        </div>

        {{-- In-House & Arriving Guests --}}
        <div class="token-card">
            <div class="flex items-center justify-between border-b border-[var(--token-border)] pb-3">
                <h3 class="text-lg font-black">In-House &amp; Arriving Guests</h3>
                <span class="text-xs font-bold token-muted font-mono">{{ $guests->count() }} bookings</span>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($guests->take(6) as $gst)
                    <div class="rounded-2xl border border-[var(--token-border)] bg-[var(--token-surface-subtle)] p-3.5 text-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="font-bold text-sm">{{ $gst->guest_name }}</p>
                                <span class="token-badge token-badge-info">{{ ucfirst(str_replace('_', ' ', $gst->status)) }}</span>
                            </div>
                            <p class="token-muted mt-0.5">{{ $gst->property?->title ?? 'Stay' }} &bull; Ref: <code class="font-mono">{{ $gst->booking_reference }}</code></p>
                            <p class="text-[11px] token-muted mt-0.5">Check-in: {{ $gst->check_in_date?->format('M d') }} &bull; Check-out: {{ $gst->check_out_date?->format('M d') }}</p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if ($gst->status === 'confirmed')
                                <form method="POST" action="{{ route('bookings.update-status', $gst->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="checked_in">
                                    <button type="submit" class="token-btn token-btn-primary transition">
                                        Check In
                                    </button>
                                </form>
                            @elseif ($gst->status === 'checked_in')
                                <form method="POST" action="{{ route('bookings.update-status', $gst->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="checked_out">
                                    <button type="submit" class="token-btn token-btn-primary transition">
                                        Check Out
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('bookings.update-status', $gst->id) }}" onsubmit="return confirm('Cancel this guest booking?');">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="token-btn token-btn-danger transition">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-xs token-muted py-4 text-center">No short-stay guests currently.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 3. Rent Collection & Schedules Roll --}}
    <div id="rent-collection" data-section="rent-collection" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[var(--token-border)] pb-3 gap-2">
            <div>
                <h3 class="text-lg font-black">Rent Collection &amp; Installment Schedules</h3>
                <p class="text-xs token-muted mt-0.5">Collect installments, monitor overdue balances, and advance tenant payment schedules</p>
            </div>
            <span class="token-badge token-badge-neutral font-mono">
                {{ $rentSchedules->where('status', '!=', 'paid')->count() }} Pending Collection
            </span>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="token-table">
                <thead>
                    <tr class="font-semibold uppercase tracking-wider text-[10px]">
                        <th>Period / Ref</th>
                        <th>Property &amp; Tenant</th>
                        <th>Due Date</th>
                        <th>Amount Due</th>
                        <th>Amount Paid</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rentSchedules as $schedule)
                        @php
                            $isPaid = $schedule->status === 'paid';
                            $dueAmount = (float) $schedule->amount_due;
                            $paidAmount = (float) $schedule->amount_paid;
                            $remaining = max(0, $dueAmount - $paidAmount);
                        @endphp
                        <tr class="transition">
                            <td class="font-mono font-bold">
                                {{ $schedule->period_label ?? 'Installment #'.$schedule->installment_number }}
                            </td>
                            <td>
                                <p class="font-bold">{{ $schedule->lease?->property?->title ?? 'Leased Unit' }}</p>
                                <p class="text-[11px] token-muted">{{ $schedule->lease?->tenant_name ?? 'Tenant' }}</p>
                            </td>
                            <td class="token-muted font-mono">
                                {{ $schedule->due_date ? \Carbon\Carbon::parse($schedule->due_date)->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="font-bold font-mono">
                                {{ number_format($dueAmount) }} ETB
                            </td>
                            <td class="font-mono {{ $paidAmount > 0 ? 'font-bold text-[var(--token-success)]' : 'token-muted' }}">
                                {{ number_format($paidAmount) }} ETB
                            </td>
                            <td>
                                <span class="token-badge {{ match ($schedule->status) { 'paid' => 'token-badge-success', 'partial' => 'token-badge-warning', 'overdue' => 'token-badge-danger', default => 'token-badge-neutral' } }}">
                                    {{ ucfirst($schedule->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if (! $isPaid)
                                    <button type="button" onclick="document.getElementById('record-payment-{{ $schedule->id }}').classList.toggle('hidden')" class="token-btn token-btn-primary cursor-pointer">
                                        Record Payment
                                    </button>
                                @else
                                    <span class="text-[11px] font-semibold text-[var(--token-success)]">✓ Settled</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Collapsible Inline Payment Modal for this schedule --}}
                        @if (! $isPaid)
                            <tr id="record-payment-{{ $schedule->id }}" class="hidden bg-[var(--token-surface-subtle)]">
                                <td colspan="7" class="p-4 border-y border-[var(--token-accent-border)]">
                                    <form method="POST" action="{{ route('rent-schedules.record-payment', $schedule->id) }}" class="flex flex-wrap items-center gap-3">
                                        @csrf
                                        <div class="text-xs font-bold">
                                            Collect Rent for {{ $schedule->period_label ?? '#'.$schedule->installment_number }}:
                                        </div>
                                        <div>
                                            <input type="number" step="0.01" name="amount" value="{{ $remaining }}" required class="token-field !w-28 !px-2.5 !py-1.5 !text-xs font-mono font-bold" placeholder="Amount ETB">
                                        </div>
                                        <div>
                                            <select name="payment_method" required class="token-field !px-2.5 !py-1.5 !text-xs">
                                                <option value="telebirr" selected>Telebirr</option>
                                                <option value="cbe_birr">CBE Birr</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                                <option value="cash">Cash (Direct)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <input type="text" name="bank_transaction_reference" placeholder="Txn Ref (optional)" class="token-field !w-36 !px-2.5 !py-1.5 !text-xs font-mono">
                                        </div>
                                        <button type="submit" class="token-btn token-btn-primary transition">
                                            Confirm Receipt
                                        </button>
                                        <button type="button" onclick="document.getElementById('record-payment-{{ $schedule->id }}').classList.add('hidden')" class="token-btn token-btn-ghost">
                                            Cancel
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center token-muted text-xs">No rent schedules generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 4. Maintenance & Cleaning Task Queues --}}
    <div id="maintenance-cleaning" data-section="maintenance-cleaning" class="section-panel hidden scroll-mt-6 grid gap-6 md:grid-cols-2">
        {{-- Maintenance Tickets --}}
        <div class="token-card">
            <div class="flex items-center justify-between border-b border-[var(--token-border)] pb-3">
                <h3 class="text-lg font-black">Maintenance Tickets</h3>
                <button type="button" onclick="document.getElementById('file-maintenance-modal').classList.toggle('hidden')" class="token-btn token-btn-ghost hover:underline cursor-pointer">
                    + New Ticket
                </button>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($maintenanceTickets->take(6) as $ticket)
                    <div class="rounded-2xl border border-[var(--token-border)] bg-[var(--token-surface-subtle)] p-3.5 text-xs flex flex-col gap-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="font-bold text-sm">{{ $ticket->title }}</span>
                                <p class="token-muted mt-0.5">{{ $ticket->property?->title ?? 'Unit' }} &bull; Priority: <strong class="text-[var(--token-danger)]">{{ ucfirst($ticket->priority) }}</strong></p>
                            </div>
                            <span class="token-badge shrink-0 {{ $ticket->status === 'completed' || $ticket->status === 'closed' ? 'token-badge-success' : 'token-badge-warning' }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-[var(--token-border-subtle)] text-[11px]">
                            <span class="token-muted font-mono">{{ $ticket->ticket_number ?? '#TKT-'.$ticket->id }}</span>
                            <div class="flex items-center gap-1.5">
                                @if ($ticket->status === 'open')
                                    <form method="POST" action="{{ route('maintenance.update-status', $ticket->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="in_progress">
                                        <button type="submit" class="token-btn token-btn-primary transition">
                                            Start Work
                                        </button>
                                    </form>
                                @elseif ($ticket->status === 'in_progress')
                                    <form method="POST" action="{{ route('maintenance.update-status', $ticket->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="token-btn token-btn-primary transition">
                                            Resolve
                                        </button>
                                    </form>
                                @endif
                                @if ($ticket->status !== 'closed')
                                    <form method="POST" action="{{ route('maintenance.update-status', $ticket->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="closed">
                                        <button type="submit" class="token-btn token-btn-secondary transition">
                                            Close
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs token-muted py-4 text-center">No maintenance tickets pending.</p>
                @endforelse
            </div>
        </div>

        {{-- Cleaning & Turnover Tasks --}}
        <div class="token-card">
            <div class="flex items-center justify-between border-b border-[var(--token-border)] pb-3">
                <h3 class="text-lg font-black">Cleaning &amp; Turnover Tasks</h3>
                <button type="button" onclick="document.getElementById('dispatch-cleaning-modal').classList.toggle('hidden')" class="token-btn token-btn-ghost hover:underline cursor-pointer">
                    + Dispatch
                </button>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($cleaningTasks->take(6) as $task)
                    <div class="rounded-2xl border border-[var(--token-border)] bg-[var(--token-surface-subtle)] p-3.5 text-xs flex flex-col gap-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="font-bold text-sm">{{ $task->room_number }} &bull; {{ $task->task_code }}</span>
                                <p class="token-muted mt-0.5">{{ $task->property?->title ?? 'Residence' }} &bull; Cleaner: <strong>{{ $task->cleaner?->name ?? 'Unassigned' }}</strong></p>
                            </div>
                            <span class="token-badge shrink-0 {{ match ($task->status) { 'ready' => 'token-badge-success', 'cleaning' => 'token-badge-info', default => 'token-badge-warning' } }}">
                                {{ ucfirst($task->status) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-[var(--token-border-subtle)] text-[11px]">
                            <span class="token-muted font-mono">{{ $task->scheduled_date ? \Carbon\Carbon::parse($task->scheduled_date)->format('M d') : 'Immediate' }}</span>
                            @if ($task->status !== 'ready')
                                <form method="POST" action="{{ route('cleaner.tasks.update-status', $task->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="ready">
                                    <button type="submit" class="token-btn token-btn-primary transition">
                                        Mark Ready
                                    </button>
                                </form>
                            @else
                                <span class="text-[10px] font-bold text-[var(--token-success)]">✓ Unit Guest-Ready</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs token-muted py-4 text-center">No cleaning tasks in queue.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
