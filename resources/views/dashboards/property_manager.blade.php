@extends('dashboards.layout')

@section('title', 'Property Manager Dashboard')
@section('portal_name', 'Property Operations Management Desk')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if (auth()->user()->hasAnyPermission(['units.manage', 'units.create', 'units.view', 'buildings.manage']))
        <a href="#units" data-target-section="units" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🏢</span>
                <span>Units & Occupancy</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $totalUnits }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['tenants.manage', 'tenants.view', 'guests.view']))
        <a href="#tenants-guests" data-target-section="tenants-guests" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🧑‍🤝‍🧑</span>
                <span>Tenants & Guests</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $tenants->count() + $guests->count() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['rent.manage', 'rent.view', 'payments.manage']))
        <a href="#rent-collection" data-target-section="rent-collection" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>💳</span>
                <span>Rent & Collections</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $rentSchedules->count() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['housekeeping.manage', 'maintenance.manage', 'maintenance.view']))
        <a href="#maintenance-cleaning" data-target-section="maintenance-cleaning" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🧹</span>
                <span>Maintenance & Tasks</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $maintenanceTickets->count() + $cleaningTasks->count() }}</span>
        </a>
    @endif
@endsection

@section('content')
<div class="space-y-8">
    {{-- Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- Add Unit Modal/Form --}}
    <div id="add-unit-modal" class="hidden rounded-3xl border border-[#d4af37] bg-[#fbf8f3] p-6 shadow-sm">
        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3 mb-4">
            <div>
                <h3 class="text-lg font-black text-[#1d3c34]">Add New Unit / Property to Portfolio</h3>
                <p class="text-xs text-slate-500 mt-0.5">Register a residential apartment, condominium, or commercial space under active management</p>
            </div>
            <button type="button" onclick="document.getElementById('add-unit-modal').classList.add('hidden')" class="text-xs font-bold text-slate-400 hover:text-slate-600 cursor-pointer">&times; Close</button>
        </div>
        <form method="POST" action="{{ route('property_manager.units.store') }}" enctype="multipart/form-data" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
            @csrf
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700">Unit / Property Title *</label>
                <input type="text" name="title" required placeholder="e.g. Sunrise Tower &bull; Unit 402 Luxury 2BR" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs focus:ring-2 focus:ring-[#1d3c34] focus:outline-hidden">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Property Category *</label>
                <select name="property_category" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                    <option value="apartment" selected>Apartment</option>
                    <option value="condominium">Condominium</option>
                    <option value="villa">Villa / House</option>
                    <option value="guest_house">Guest House / Short Stay</option>
                    <option value="office">Commercial / Office</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Listing / Operation Type *</label>
                <select name="type" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                    <option value="rent" selected>For Rent (Long-Term)</option>
                    <option value="stay">Short Stay (Nightly Turn)</option>
                    <option value="sale">For Sale</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Price / Rent Amount (ETB) *</label>
                <input type="number" step="0.01" name="price" required placeholder="e.g. 45000" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-mono font-bold focus:ring-2 focus:ring-[#1d3c34] focus:outline-hidden">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Initial Occupancy Status *</label>
                <select name="status" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                    <option value="available" selected>Vacant (Available for Lease)</option>
                    <option value="rented">Occupied (Tenant in Place)</option>
                    <option value="maintenance">Under Turnover / Maintenance</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">City *</label>
                <input type="text" name="city" value="Addis Ababa" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs focus:ring-2 focus:ring-[#1d3c34] focus:outline-hidden">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Subcity / Area</label>
                <input type="text" name="subcity" placeholder="e.g. Bole, Kirkos, CMC" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs focus:ring-2 focus:ring-[#1d3c34] focus:outline-hidden">
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Beds</label>
                    <input type="number" name="bedrooms" value="2" min="0" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-2 py-2 text-xs text-center">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Baths</label>
                    <input type="number" name="bathrooms" value="2" min="0" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-2 py-2 text-xs text-center">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Area m²</label>
                    <input type="number" name="area" value="85" min="0" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-2 py-2 text-xs text-center">
                </div>
            </div>
            <div class="sm:col-span-2 md:col-span-3">
                <label class="block text-xs font-bold text-slate-700">Unit Cover Photo / Image</label>
                <input type="file" name="image" accept="image/*" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#1d3c34] file:text-white hover:file:bg-[#254d43] cursor-pointer">
                <p class="text-[11px] text-slate-400 mt-1">Upload a real unit photo (JPEG, PNG, WebP up to 20MB)</p>
            </div>
            <div class="sm:col-span-2 md:col-span-3 flex items-center justify-end gap-3 pt-2 border-t border-[#e7ddca]">
                <button type="button" onclick="document.getElementById('add-unit-modal').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white hover:bg-[#254d43] transition shadow-sm cursor-pointer">
                    + Register Unit to Portfolio
                </button>
            </div>
        </form>
    </div>

    {{-- Dispatch Cleaning Modal/Form --}}
    <div id="dispatch-cleaning-modal" class="hidden rounded-3xl border border-[#d4af37] bg-[#fbf8f3] p-6 shadow-sm">
        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3 mb-4">
            <h3 class="text-lg font-black text-[#1d3c34]">Dispatch Housekeeping / Cleaning Task</h3>
            <button type="button" onclick="document.getElementById('dispatch-cleaning-modal').classList.add('hidden')" class="text-xs font-bold text-slate-400 hover:text-slate-600">&times; Close</button>
        </div>
        <form method="POST" action="{{ route('property_manager.cleaning.dispatch') }}" class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700">Property / Building *</label>
                <select name="property_id" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                    @foreach ($properties as $prop)
                        <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Unit / Room Number *</label>
                <input type="text" name="room_number" required placeholder="e.g. Unit 302" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Assign Cleaner</label>
                <select name="cleaner_user_id" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                    <option value="">-- Unassigned (Queue) --</option>
                    @foreach ($cleaners as $cleaner)
                        <option value="{{ $cleaner->id }}">{{ $cleaner->name }} ({{ ucfirst($cleaner->role) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Priority</label>
                <select name="priority" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                    <option value="normal" selected>Normal Turnover</option>
                    <option value="urgent">Urgent (Arrival today)</option>
                    <option value="low">Deep Clean (Routine)</option>
                </select>
            </div>
            <div class="sm:col-span-2 md:col-span-4 flex justify-end">
                <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white hover:bg-[#254d43]">
                    Dispatch Task
                </button>
            </div>
        </form>
    </div>

    {{-- File Maintenance Modal/Form --}}
    <div id="file-maintenance-modal" class="hidden rounded-3xl border border-[#1d3c34]/30 bg-[#fbf8f3] p-6 shadow-sm">
        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3 mb-4">
            <h3 class="text-lg font-black text-[#1d3c34]">File New Maintenance Request</h3>
            <button type="button" onclick="document.getElementById('file-maintenance-modal').classList.add('hidden')" class="text-xs font-bold text-slate-400 hover:text-slate-600">&times; Close</button>
        </div>
        <form method="POST" action="{{ route('maintenance.store') }}" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700">Property / Unit *</label>
                <select name="property_id" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                    @foreach ($properties as $prop)
                        <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Category *</label>
                <select name="category" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                    <option value="plumbing">Plumbing</option>
                    <option value="electrical">Electrical</option>
                    <option value="appliance">Appliance / HVAC</option>
                    <option value="structural">Structural / Locksmith</option>
                    <option value="cleaning">Sanitation & Pest</option>
                    <option value="other">General Facility</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Priority *</label>
                <select name="priority" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                    <option value="low">Low (Routine)</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High (Urgent)</option>
                    <option value="emergency">Emergency</option>
                </select>
            </div>
            <div class="sm:col-span-2 md:col-span-3">
                <label class="block text-xs font-bold text-slate-700">Issue Title *</label>
                <input type="text" name="title" required placeholder="e.g. Master Bedroom AC Unit Leaking Water" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
            </div>
            <div class="sm:col-span-2 md:col-span-3">
                <label class="block text-xs font-bold text-slate-700">Detailed Description *</label>
                <textarea name="description" rows="3" required placeholder="Describe location inside unit, symptoms, and urgency..." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs"></textarea>
            </div>
            <div class="sm:col-span-2 md:col-span-3 flex justify-end">
                <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white hover:bg-[#254d43]">
                    Submit Maintenance Ticket
                </button>
            </div>
        </form>
    </div>

    {{-- 1. Units Inventory Matrix (Occupied vs Vacant) --}}
    <div id="units" data-section="units" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34]">Units &amp; Occupancy Roll</h2>
                <p class="text-xs text-slate-500 mt-0.5">Buildings, residential units, and commercial spaces under active management</p>
            </div>
            <button type="button" onclick="document.getElementById('add-unit-modal').classList.remove('hidden'); document.getElementById('add-unit-modal').scrollIntoView({behavior: 'smooth'})" class="rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition shadow-sm cursor-pointer flex items-center gap-1.5">
                <span class="text-sm font-black text-[#d4af37]">+</span>
                <span>Add New Unit</span>
            </button>
        </div>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse ($properties as $unit)
                @php
                    $isOccupied = $unit->status === 'rented' || $unit->leases->where('status', 'active')->count() > 0;
                    $statusBadge = match($unit->status) {
                        'rented' => ['label' => 'Occupied', 'class' => 'bg-emerald-100 text-emerald-800'],
                        'maintenance' => ['label' => 'Maintenance', 'class' => 'bg-rose-100 text-rose-800'],
                        default => ['label' => $isOccupied ? 'Occupied' : 'Vacant', 'class' => $isOccupied ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'],
                    };
                @endphp
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] overflow-hidden flex flex-col justify-between hover:border-[#1d3c34] transition shadow-xs">
                    <div>
                        {{-- Property Thumbnail --}}
                        <div class="relative h-32 w-full bg-slate-200 overflow-hidden">
                            @if($unit->primary_image_url)
                                <img src="{{ $unit->primary_image_url }}" alt="{{ $unit->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#1d3c34]/10 to-[#2d5d4d]/20 text-slate-400">
                                    <span class="text-2xl">🏢</span>
                                </div>
                            @endif
                            <div class="absolute top-2 left-2 flex items-center gap-1.5">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold shadow-xs {{ $statusBadge['class'] }}">
                                    {{ $statusBadge['label'] }}
                                </span>
                            </div>
                            <span class="absolute top-2 right-2 rounded-full bg-black/60 backdrop-blur-xs text-white px-2 py-0.5 text-[10px] font-mono">
                                #{{ $unit->id }}
                            </span>
                        </div>

                        <div class="p-3.5">
                            <h4 class="font-bold text-sm text-[#1d3c34] line-clamp-1" title="{{ $unit->title }}">{{ $unit->title }}</h4>
                            <p class="text-xs text-slate-500 mt-0.5">📍 {{ $unit->city }}{{ $unit->subcity ? ', ' . $unit->subcity : '' }}</p>
                            <div class="mt-2 flex items-center justify-between text-xs">
                                <span class="font-bold text-[#1d3c34] text-sm">{{ number_format($unit->price) }} <span class="text-[10px] font-normal text-slate-500">ETB</span></span>
                                <span class="text-[11px] text-slate-500">{{ $unit->bedrooms }}b &bull; {{ $unit->bathrooms }}ba &bull; {{ $unit->area }}m²</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3.5 pt-0 border-t border-[#e7ddca]/60 mt-1 flex items-center justify-between text-xs">
                        <button type="button" onclick="document.getElementById('edit-unit-{{ $unit->id }}').classList.remove('hidden')" class="rounded-full bg-[#1d3c34] text-white px-3 py-1 font-bold text-[11px] hover:bg-[#254d43] transition cursor-pointer flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            <span>Manage / Edit</span>
                        </button>
                        <a href="{{ route('properties.show', $unit->slug) }}" target="_blank" class="font-bold text-slate-600 hover:text-[#1d3c34] text-[11px] inline-flex items-center gap-1 hover:underline">
                            <span>Details</span>
                            <span>↗</span>
                        </a>
                    </div>
                </div>

                {{-- Edit / Manage Unit Modal --}}
                <div id="edit-unit-{{ $unit->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl border border-[#e7ddca] max-h-[90vh] overflow-y-auto text-left">
                        <div class="flex items-start justify-between border-b border-[#e7ddca] pb-3">
                            <div>
                                <span class="text-xs font-mono font-bold text-slate-400">Unit #{{ $unit->id }}</span>
                                <h3 class="text-lg font-black text-[#1d3c34] mt-0.5">Manage Unit: {{ $unit->title }}</h3>
                            </div>
                            <button type="button" onclick="document.getElementById('edit-unit-{{ $unit->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
                        </div>

                        <form method="POST" action="{{ route('property_manager.units.update', $unit->id) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="block text-xs font-bold text-slate-700">Unit / Property Title *</label>
                                <input type="text" name="title" value="{{ $unit->title }}" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs focus:ring-2 focus:ring-[#1d3c34]">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Occupancy Status *</label>
                                    <select name="status" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                                        <option value="available" {{ $unit->status === 'available' ? 'selected' : '' }}>Vacant (Available)</option>
                                        <option value="rented" {{ $unit->status === 'rented' ? 'selected' : '' }}>Occupied (Rented)</option>
                                        <option value="maintenance" {{ $unit->status === 'maintenance' ? 'selected' : '' }}>Under Turnover / Maintenance</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Price / Rent Amount (ETB) *</label>
                                    <input type="number" step="0.01" name="price" value="{{ $unit->price }}" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-mono font-bold">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Property Category</label>
                                    <select name="property_category" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                                        <option value="apartment" {{ ($unit->property_category ?? $unit->category) === 'apartment' ? 'selected' : '' }}>Apartment</option>
                                        <option value="condominium" {{ ($unit->property_category ?? $unit->category) === 'condominium' ? 'selected' : '' }}>Condominium</option>
                                        <option value="villa" {{ ($unit->property_category ?? $unit->category) === 'villa' ? 'selected' : '' }}>Villa / House</option>
                                        <option value="guest_house" {{ ($unit->property_category ?? $unit->category) === 'guest_house' ? 'selected' : '' }}>Guest House / Short Stay</option>
                                        <option value="office" {{ ($unit->property_category ?? $unit->category) === 'office' ? 'selected' : '' }}>Commercial / Office</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Operation Type</label>
                                    <select name="type" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                                        <option value="rent" {{ $unit->type === 'rent' ? 'selected' : '' }}>For Rent (Long-Term)</option>
                                        <option value="stay" {{ $unit->type === 'stay' ? 'selected' : '' }}>Short Stay (Nightly)</option>
                                        <option value="sale" {{ $unit->type === 'sale' ? 'selected' : '' }}>For Sale</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Beds</label>
                                    <input type="number" name="bedrooms" value="{{ $unit->bedrooms ?? 1 }}" min="0" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-2 py-2 text-xs text-center">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Baths</label>
                                    <input type="number" name="bathrooms" value="{{ $unit->bathrooms ?? 1 }}" min="0" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-2 py-2 text-xs text-center">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Area m²</label>
                                    <input type="number" name="area" value="{{ $unit->area ?? 75 }}" min="0" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-2 py-2 text-xs text-center">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700">Update / Replace Unit Image</label>
                                <input type="file" name="image" accept="image/*" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#1d3c34] file:text-white hover:file:bg-[#254d43] cursor-pointer">
                                @if($unit->image_path)
                                    <p class="text-[11px] text-emerald-600 mt-1">✓ Current image set: {{ basename($unit->image_path) }}</p>
                                @endif
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#e7ddca]">
                                <button type="button" onclick="document.getElementById('edit-unit-{{ $unit->id }}').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 cursor-pointer">
                                    Cancel
                                </button>
                                <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white hover:bg-[#254d43] transition shadow-sm cursor-pointer">
                                    Save Unit Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <p class="col-span-4 text-xs text-slate-400 py-4 text-center">No units assigned.</p>
            @endforelse
        </div>
    </div>

    {{-- 2. Active Tenants & In-House Guests --}}
    <div id="tenants-guests" data-section="tenants-guests" class="section-panel hidden scroll-mt-6 grid gap-6 md:grid-cols-2">
        {{-- Long-Term Tenants --}}
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3">
                <h3 class="text-lg font-black text-[#1d3c34]">Active Tenants (Long-Term)</h3>
                <span class="text-xs font-bold text-slate-400 font-mono">{{ $tenants->count() }} active</span>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($tenants->take(6) as $tenant)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-3.5 text-xs flex items-center justify-between">
                        <div>
                            <p class="font-bold text-sm text-[#1d3c34]">{{ $tenant->tenant_name ?? 'Tenant' }}</p>
                            <p class="text-slate-500 mt-0.5">{{ $tenant->property?->title ?? 'Property' }} &bull; <strong class="text-slate-700">{{ number_format($tenant->monthly_rent) }} ETB/mo</strong></p>
                            <p class="text-[11px] text-slate-400 mt-0.5">📞 {{ $tenant->tenant_phone ?? 'N/A' }} &bull; {{ $tenant->tenant_email ?? 'N/A' }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1.5">
                            <span class="rounded-full bg-emerald-100 text-emerald-800 px-2 py-0.5 text-[10px] font-bold">Lease Active</span>
                            <button type="button" onclick="document.getElementById('lease-modal-{{ $tenant->id }}').classList.remove('hidden')" class="text-[11px] font-bold text-[#1d3c34] hover:underline cursor-pointer flex items-center gap-1">
                                <span>Lease Terms</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                    </div>

                    {{-- Lease Terms & Details Modal --}}
                    <div id="lease-modal-{{ $tenant->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl border border-[#e7ddca] max-h-[90vh] overflow-y-auto text-left">
                            <div class="flex items-start justify-between border-b border-[#e7ddca] pb-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-emerald-100 text-emerald-800 px-2.5 py-0.5 text-xs font-bold">{{ ucfirst($tenant->status) }}</span>
                                        <span class="text-xs font-mono font-bold text-slate-500">{{ $tenant->lease_code }}</span>
                                    </div>
                                    <h3 class="text-lg font-black text-[#1d3c34] mt-1">{{ $tenant->tenant_name }} &bull; Tenancy Terms &amp; Agreement</h3>
                                </div>
                                <button type="button" onclick="document.getElementById('lease-modal-{{ $tenant->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2 text-xs">
                                <div class="rounded-2xl bg-[#fbf9f5] p-3.5 border border-[#e7ddca]">
                                    <p class="font-bold text-[#1d3c34] uppercase tracking-wider text-[10px]">Tenant Details</p>
                                    <div class="mt-2 space-y-1">
                                        <p><span class="text-slate-500">Name:</span> <strong class="text-slate-800">{{ $tenant->tenant_name }}</strong></p>
                                        <p><span class="text-slate-500">Phone:</span> <strong class="text-slate-800">{{ $tenant->tenant_phone ?? 'N/A' }}</strong></p>
                                        <p><span class="text-slate-500">Email:</span> <strong class="text-slate-800">{{ $tenant->tenant_email ?? 'N/A' }}</strong></p>
                                        <p><span class="text-slate-500">ID ({{ ucfirst($tenant->tenant_id_type ?? 'Kebele') }}):</span> <strong class="text-slate-800 font-mono">{{ $tenant->tenant_id_number ?? 'N/A' }}</strong></p>
                                        @if($tenant->tenant_occupation)
                                            <p><span class="text-slate-500">Occupation:</span> <strong class="text-slate-800">{{ $tenant->tenant_occupation }}</strong></p>
                                        @endif
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-[#fbf9f5] p-3.5 border border-[#e7ddca]">
                                    <p class="font-bold text-[#1d3c34] uppercase tracking-wider text-[10px]">Financial Terms</p>
                                    <div class="mt-2 space-y-1">
                                        <p><span class="text-slate-500">Monthly Rent:</span> <strong class="text-[#1d3c34] text-sm">{{ number_format((float) ($tenant->monthly_rent ?? 0)) }} ETB</strong></p>
                                        <p><span class="text-slate-500">Security Deposit:</span> <strong class="text-slate-800">{{ number_format((float) ($tenant->security_deposit ?? 0)) }} ETB</strong></p>
                                        <p><span class="text-slate-500">Payment Frequency:</span> <strong class="text-slate-800 uppercase">{{ $tenant->payment_frequency ?? 'Monthly' }}</strong></p>
                                        <p><span class="text-slate-500">Grace Period:</span> <strong class="text-slate-800">{{ $tenant->grace_period_days ?? 5 }} Days</strong></p>
                                        <p><span class="text-slate-500">Late Fee:</span> <strong class="text-slate-800">{{ $tenant->late_fee_percentage ?? 2 }}%</strong></p>
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-[#fbf9f5] p-3.5 border border-[#e7ddca]">
                                    <p class="font-bold text-[#1d3c34] uppercase tracking-wider text-[10px]">Property / Leased Unit</p>
                                    <div class="mt-2 space-y-1">
                                        <p><span class="text-slate-500">Property:</span> <strong class="text-slate-800">{{ $tenant->property?->title ?? 'N/A' }}</strong></p>
                                        <p><span class="text-slate-500">Location:</span> <strong class="text-slate-800">{{ $tenant->property?->city ?? 'Addis Ababa' }}{{ $tenant->property?->subcity ? ', ' . $tenant->property?->subcity : '' }}</strong></p>
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-[#fbf9f5] p-3.5 border border-[#e7ddca]">
                                    <p class="font-bold text-[#1d3c34] uppercase tracking-wider text-[10px]">Term Duration</p>
                                    <div class="mt-2 space-y-1">
                                        <p><span class="text-slate-500">Duration:</span> <strong class="text-slate-800">{{ $tenant->duration_months ?? 12 }} Months</strong></p>
                                        <p><span class="text-slate-500">Start Date:</span> <strong class="text-slate-800">{{ $tenant->start_date ? \Carbon\Carbon::parse($tenant->start_date)->format('M d, Y') : 'N/A' }}</strong></p>
                                        <p><span class="text-slate-500">End Date:</span> <strong class="text-slate-800">{{ $tenant->end_date ? \Carbon\Carbon::parse($tenant->end_date)->format('M d, Y') : 'N/A' }}</strong></p>
                                    </div>
                                </div>
                            </div>

                            @if($tenant->terms_and_conditions || $tenant->notes)
                                <div class="mt-3 rounded-2xl bg-amber-50/50 p-3.5 border border-amber-200/60 text-xs">
                                    <p class="font-bold text-amber-900 uppercase tracking-wider text-[10px]">Special Clauses &amp; Terms</p>
                                    <p class="mt-1 text-slate-700 whitespace-pre-line">{{ $tenant->terms_and_conditions ?: $tenant->notes }}</p>
                                </div>
                            @endif

                            <div class="mt-5 flex items-center justify-between border-t border-[#e7ddca] pt-4">
                                <button type="button" onclick="document.getElementById('lease-modal-{{ $tenant->id }}').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 cursor-pointer">
                                    Close
                                </button>
                                <div class="flex items-center gap-2">
                                    @if(Route::has('leases.agreement'))
                                        <a href="{{ route('leases.agreement', $tenant->id) }}" class="rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition shadow-sm inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <span>Download Agreement PDF</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No active tenants on roll.</p>
                @endforelse
            </div>
        </div>

        {{-- In-House & Arriving Guests --}}
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3">
                <h3 class="text-lg font-black text-[#1d3c34]">In-House &amp; Arriving Guests</h3>
                <span class="text-xs font-bold text-slate-400 font-mono">{{ $guests->count() }} bookings</span>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($guests->take(6) as $gst)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-3.5 text-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="font-bold text-sm text-[#1d3c34]">{{ $gst->guest_name }}</p>
                                <span class="rounded-full bg-blue-100 text-blue-800 px-2 py-0.5 text-[10px] font-bold">{{ ucfirst(str_replace('_', ' ', $gst->status)) }}</span>
                            </div>
                            <p class="text-slate-500 mt-0.5">{{ $gst->property?->title ?? 'Stay' }} &bull; Ref: <code class="font-mono text-slate-700">{{ $gst->booking_reference }}</code></p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Check-in: {{ $gst->check_in_date?->format('M d') }} &bull; Check-out: {{ $gst->check_out_date?->format('M d') }}</p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if ($gst->status === 'confirmed')
                                <form method="POST" action="{{ route('bookings.update-status', $gst->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="checked_in">
                                    <button type="submit" class="rounded-full bg-emerald-700 px-3 py-1 text-[11px] font-bold text-white hover:bg-emerald-800 transition">
                                        Check In
                                    </button>
                                </form>
                            @elseif ($gst->status === 'checked_in')
                                <form method="POST" action="{{ route('bookings.update-status', $gst->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="checked_out">
                                    <button type="submit" class="rounded-full bg-blue-700 px-3 py-1 text-[11px] font-bold text-white hover:bg-blue-800 transition">
                                        Check Out
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('bookings.update-status', $gst->id) }}" onsubmit="return confirm('Cancel this guest booking?');">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="rounded-full border border-rose-300 px-2.5 py-1 text-[11px] font-semibold text-rose-700 hover:bg-rose-50 transition">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No short-stay guests currently.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 3. Rent Collection & Schedules Roll --}}
    <div id="rent-collection" data-section="rent-collection" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[#e7ddca] pb-3 gap-2">
            <div>
                <h3 class="text-lg font-black text-[#1d3c34]">Rent Collection &amp; Installment Schedules</h3>
                <p class="text-xs text-slate-500 mt-0.5">Collect installments, monitor overdue balances, and advance tenant payment schedules</p>
            </div>
            <span class="rounded-full bg-slate-100 text-slate-700 px-3 py-1 text-xs font-bold font-mono">
                {{ $rentSchedules->where('status', '!=', 'paid')->count() }} Pending Collection
            </span>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#e7ddca] text-slate-400 font-semibold uppercase tracking-wider text-[10px]">
                        <th class="py-2.5 px-3">Period / Ref</th>
                        <th class="py-2.5 px-3">Property &amp; Tenant</th>
                        <th class="py-2.5 px-3">Due Date</th>
                        <th class="py-2.5 px-3">Amount Due</th>
                        <th class="py-2.5 px-3">Amount Paid</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rentSchedules as $schedule)
                        @php
                            $isPaid = $schedule->status === 'paid';
                            $dueAmount = (float) $schedule->amount_due;
                            $paidAmount = (float) $schedule->amount_paid;
                            $remaining = max(0, $dueAmount - $paidAmount);
                        @endphp
                        <tr class="hover:bg-[#fbf9f5] transition">
                            <td class="py-3 px-3 font-mono font-bold text-[#1d3c34]">
                                {{ $schedule->period_label ?? 'Installment #'.$schedule->installment_number }}
                            </td>
                            <td class="py-3 px-3">
                                <p class="font-bold text-slate-800">{{ $schedule->lease?->property?->title ?? 'Leased Unit' }}</p>
                                <p class="text-[11px] text-slate-500">{{ $schedule->lease?->tenant_name ?? 'Tenant' }}</p>
                            </td>
                            <td class="py-3 px-3 text-slate-600 font-mono">
                                {{ $schedule->due_date ? \Carbon\Carbon::parse($schedule->due_date)->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="py-3 px-3 font-bold text-slate-800 font-mono">
                                {{ number_format($dueAmount) }} ETB
                            </td>
                            <td class="py-3 px-3 font-mono {{ $paidAmount > 0 ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                                {{ number_format($paidAmount) }} ETB
                            </td>
                            <td class="py-3 px-3">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ match ($schedule->status) { 'paid' => 'bg-emerald-100 text-emerald-800', 'partial' => 'bg-amber-100 text-amber-800', 'overdue' => 'bg-rose-100 text-rose-800', default => 'bg-slate-100 text-slate-700' } }}">
                                    {{ ucfirst($schedule->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                @if (! $isPaid)
                                    <button type="button" onclick="document.getElementById('record-payment-{{ $schedule->id }}').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-3 py-1 text-[11px] font-bold text-white hover:bg-[#254d43] transition cursor-pointer">
                                        Record Payment
                                    </button>
                                @else
                                    <span class="text-[11px] font-semibold text-emerald-700">✓ Settled</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Collapsible Inline Payment Modal for this schedule --}}
                        @if (! $isPaid)
                            <tr id="record-payment-{{ $schedule->id }}" class="hidden bg-[#fbf8f3]">
                                <td colspan="7" class="p-4 border-y border-[#d4af37]/40">
                                    <form method="POST" action="{{ route('rent-schedules.record-payment', $schedule->id) }}" class="flex flex-wrap items-center gap-3">
                                        @csrf
                                        <div class="text-xs font-bold text-[#1d3c34]">
                                            Collect Rent for {{ $schedule->period_label ?? '#'.$schedule->installment_number }}:
                                        </div>
                                        <div>
                                            <input type="number" step="0.01" name="amount" value="{{ $remaining }}" required class="w-28 rounded-lg border border-[#d9cab3] bg-white px-2.5 py-1.5 text-xs font-mono font-bold" placeholder="Amount ETB">
                                        </div>
                                        <div>
                                            <select name="payment_method" required class="rounded-lg border border-[#d9cab3] bg-white px-2.5 py-1.5 text-xs">
                                                <option value="telebirr" selected>Telebirr</option>
                                                <option value="cbe_birr">CBE Birr</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                                <option value="cash">Cash (Direct)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <input type="text" name="bank_transaction_reference" placeholder="Txn Ref (optional)" class="w-36 rounded-lg border border-[#d9cab3] bg-white px-2.5 py-1.5 text-xs font-mono">
                                        </div>
                                        <button type="submit" class="rounded-full bg-[#d4af37] px-4 py-1.5 text-xs font-bold text-[#1d3c34] hover:bg-[#b7842d] transition">
                                            Confirm Receipt
                                        </button>
                                        <button type="button" onclick="document.getElementById('record-payment-{{ $schedule->id }}').classList.add('hidden')" class="text-xs text-slate-400 hover:text-slate-600">
                                            Cancel
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400 text-xs">No rent schedules generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 4. Maintenance & Cleaning Task Queues --}}
    <div id="maintenance-cleaning" data-section="maintenance-cleaning" class="section-panel hidden scroll-mt-6 grid gap-6 md:grid-cols-2">
        {{-- Maintenance Tickets --}}
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3">
                <h3 class="text-lg font-black text-[#1d3c34]">Maintenance Tickets</h3>
                <button type="button" onclick="document.getElementById('file-maintenance-modal').classList.toggle('hidden')" class="text-xs font-bold text-[#1d3c34] hover:underline cursor-pointer">
                    + New Ticket
                </button>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($maintenanceTickets->take(6) as $ticket)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-3.5 text-xs flex flex-col gap-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="font-bold text-sm text-[#1d3c34]">{{ $ticket->title }}</span>
                                <p class="text-slate-500 mt-0.5">{{ $ticket->property?->title ?? 'Unit' }} &bull; Priority: <strong class="text-rose-700">{{ ucfirst($ticket->priority) }}</strong></p>
                            </div>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold shrink-0 {{ $ticket->status === 'completed' || $ticket->status === 'closed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-[#e7ddca]/60 text-[11px]">
                            <span class="text-slate-400 font-mono">{{ $ticket->ticket_number ?? '#TKT-'.$ticket->id }}</span>
                            <div class="flex items-center gap-1.5">
                                @if ($ticket->status === 'open')
                                    <form method="POST" action="{{ route('maintenance.update-status', $ticket->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="in_progress">
                                        <button type="submit" class="rounded-full bg-amber-600 px-2.5 py-0.5 text-[10px] font-bold text-white hover:bg-amber-700 transition">
                                            Start Work
                                        </button>
                                    </form>
                                @elseif ($ticket->status === 'in_progress')
                                    <form method="POST" action="{{ route('maintenance.update-status', $ticket->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="rounded-full bg-emerald-700 px-2.5 py-0.5 text-[10px] font-bold text-white hover:bg-emerald-800 transition">
                                            Resolve
                                        </button>
                                    </form>
                                @endif
                                @if ($ticket->status !== 'closed')
                                    <form method="POST" action="{{ route('maintenance.update-status', $ticket->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="closed">
                                        <button type="submit" class="rounded-full border border-slate-300 px-2.5 py-0.5 text-[10px] font-semibold text-slate-600 hover:bg-slate-100 transition">
                                            Close
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No maintenance tickets pending.</p>
                @endforelse
            </div>
        </div>

        {{-- Cleaning & Turnover Tasks --}}
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3">
                <h3 class="text-lg font-black text-[#1d3c34]">Cleaning &amp; Turnover Tasks</h3>
                <button type="button" onclick="document.getElementById('dispatch-cleaning-modal').classList.toggle('hidden')" class="text-xs font-bold text-[#1d3c34] hover:underline cursor-pointer">
                    + Dispatch
                </button>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($cleaningTasks->take(6) as $task)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-3.5 text-xs flex flex-col gap-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="font-bold text-sm text-[#1d3c34]">{{ $task->room_number }} &bull; {{ $task->task_code }}</span>
                                <p class="text-slate-500 mt-0.5">{{ $task->property?->title ?? 'Residence' }} &bull; Cleaner: <strong class="text-slate-700">{{ $task->cleaner?->name ?? 'Unassigned' }}</strong></p>
                            </div>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold shrink-0 {{ match ($task->status) { 'ready' => 'bg-emerald-100 text-emerald-800', 'cleaning' => 'bg-blue-100 text-blue-800', default => 'bg-amber-100 text-amber-800' } }}">
                                {{ ucfirst($task->status) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-[#e7ddca]/60 text-[11px]">
                            <span class="text-slate-400 font-mono">{{ $task->scheduled_date ? \Carbon\Carbon::parse($task->scheduled_date)->format('M d') : 'Immediate' }}</span>
                            @if ($task->status !== 'ready')
                                <form method="POST" action="{{ route('cleaner.tasks.update-status', $task->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="ready">
                                    <button type="submit" class="rounded-full bg-emerald-700 px-2.5 py-0.5 text-[10px] font-bold text-white hover:bg-emerald-800 transition">
                                        Mark Ready
                                    </button>
                                </form>
                            @else
                                <span class="text-[10px] font-bold text-emerald-700">✓ Unit Guest-Ready</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No cleaning tasks in queue.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
