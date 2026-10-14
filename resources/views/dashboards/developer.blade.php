@extends('dashboards.layout')

@section('title', 'Developer Portal — ' . config('app.name', 'GTM Real Estate'))
@section('portal_name', 'Developer Portal')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center justify-between rounded-xl bg-white/10 px-3 py-2 text-xs font-semibold text-white">
        <span class="flex items-center gap-2"><span>📊</span><span>Overview</span></span>
    </a>
    @if ($canManageProjects)
    <a href="#projects" data-target-section="projects" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>🏗️</span><span>Projects</span></span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">{{ $stats['projects'] }}</span>
    </a>
    @endif
    @if ($canManageBuildings)
    <a href="#buildings" data-target-section="buildings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>🏢</span><span>Buildings</span></span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">{{ $stats['buildings'] }}</span>
    </a>
    @endif
    @if ($canManageUnits)
    <a href="#units" data-target-section="units" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>🚪</span><span>Units</span></span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">{{ $stats['units'] }}</span>
    </a>
    @endif
    @if ($canManageConstruction)
    <a href="#construction" data-target-section="construction" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>🚧</span><span>Construction Progress</span></span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">{{ $stats['avg_completion'] }}%</span>
    </a>
    @endif
    @if ($canManagePricing)
    <a href="#pricing" data-target-section="pricing" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>💰</span><span>Pricing</span></span>
    </a>
    @endif
    @if ($canManagePaymentPlans)
    <a href="#payment-plans" data-target-section="payment-plans" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>📅</span><span>Payment Plans</span></span>
    </a>
    @endif
    @if ($canManageBuyers)
    <a href="#buyers" data-target-section="buyers" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>🧑‍🤝‍🧑</span><span>Buyers</span></span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">{{ $stats['buyers'] }}</span>
    </a>
    @endif
    @if ($canManageReservations)
    <a href="#reservations" data-target-section="reservations" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2"><span>📝</span><span>Reservations</span></span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">{{ $stats['reservations'] }}</span>
    </a>
    @endif
@endsection

@section('content')
<div class="space-y-8">
    {{-- Alerts --}}
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-300 bg-emerald-50 px-5 py-3.5 text-xs font-bold text-emerald-900 shadow-xs">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-xs font-medium text-rose-900">
            <p class="font-bold mb-1">Please address the following:</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Overview --}}
    <section id="overview" data-section="overview" class="section-panel space-y-6">
        <div class="rounded-[2rem] border border-[#d9cab3] bg-gradient-to-br from-[#1d3c34] to-[#254d43] p-6 text-white shadow-xl md:p-8">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-md bg-[#d4af37] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-widest text-[#102b25]">Developer Portal</span>
                <span class="rounded-full bg-white/10 px-3 py-0.5 text-xs font-semibold text-[#f0e8d8] border border-white/15">Real Estate Development Hub</span>
            </div>
            <h1 class="mt-3 text-2xl md:text-3xl font-black tracking-tight text-white font-serif">Welcome back, {{ auth()->user()->name }}.</h1>
            <p class="mt-2 max-w-2xl text-xs md:text-sm text-[#dfeee4]">Projects, buildings, units, construction progress, pricing, payment plans, buyers, and reservations &mdash; everything your developer role manages, in one desk.</p>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 border-t border-white/15 pt-6">
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                    <p class="text-2xl font-black text-white">{{ $stats['projects'] }}</p>
                    <p class="mt-1 text-[11px] font-bold uppercase tracking-wider text-[#dfeee4]">Projects</p>
                    <p class="mt-0.5 text-[10px] text-[#dfeee4]/70">{{ $stats['under_construction'] }} under construction</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                    <p class="text-2xl font-black text-white">{{ $stats['buildings'] }}</p>
                    <p class="mt-1 text-[11px] font-bold uppercase tracking-wider text-[#dfeee4]">Buildings</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                    <p class="text-2xl font-black text-white">{{ $stats['units'] }}</p>
                    <p class="mt-1 text-[11px] font-bold uppercase tracking-wider text-[#dfeee4]">Units</p>
                    <p class="mt-0.5 text-[10px] text-[#dfeee4]/70">{{ $stats['available_units'] }} available</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                    <p class="text-2xl font-black text-white">{{ $stats['reservations'] }}</p>
                    <p class="mt-1 text-[11px] font-bold uppercase tracking-wider text-[#dfeee4]">Reservations</p>
                    <p class="mt-0.5 text-[10px] text-[#dfeee4]/70">{{ $stats['pending_reservations'] }} pending</p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-3xl border border-[#d9cab3] bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between text-[#587165]">
                    <p class="text-sm font-medium">Avg. Completion</p>
                    <span class="text-xl">🚧</span>
                </div>
                <p class="mt-3 text-2xl font-black text-[#1d3c34]">{{ $stats['avg_completion'] }}%</p>
                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-[#e7ddca]">
                    <div class="h-full rounded-full bg-[#1d3c34]" style="width: {{ $stats['avg_completion'] }}%"></div>
                </div>
            </div>
            <div class="rounded-3xl border border-[#d9cab3] bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between text-[#587165]">
                    <p class="text-sm font-medium">Available Units</p>
                    <span class="text-xl">🚪</span>
                </div>
                <p class="mt-3 text-2xl font-black text-emerald-700">{{ $stats['available_units'] }}</p>
                <p class="mt-1 text-[11px] text-slate-500">Ready to sell or reserve</p>
            </div>
            <div class="rounded-3xl border border-[#d9cab3] bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between text-[#587165]">
                    <p class="text-sm font-medium">Registered Buyers</p>
                    <span class="text-xl">🧑‍🤝‍🧑</span>
                </div>
                <p class="mt-3 text-2xl font-black text-[#1d3c34]">{{ $stats['buyers'] }}</p>
                <p class="mt-1 text-[11px] text-slate-500">Across all reservations</p>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </section>

    {{-- Projects --}}
    @if ($canManageProjects)
    <section id="projects" data-section="projects" class="section-panel hidden space-y-6 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-4 gap-4">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34]">Projects</h2>
                <p class="text-xs text-[#587165]">Master off-plan development projects you manage.</p>
            </div>
            <button type="button" onclick="document.getElementById('new-project-drawer').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#254d43] transition">
                + New Project
            </button>
        </div>

        {{-- New Project Drawer --}}
        <div id="new-project-drawer" class="hidden rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-5">
            <h3 class="text-sm font-black text-[#1d3c34] mb-3">Create Development Project</h3>
            <form method="POST" action="{{ route('developments.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700">Project Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Bole Horizon Towers" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Developer / Company Name *</label>
                        <input type="text" name="developer_name" value="{{ auth()->user()->name }}" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">City *</label>
                        <input type="text" name="city" value="Addis Ababa" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Subcity</label>
                        <input type="text" name="subcity" placeholder="e.g. Bole" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Project Type *</label>
                        <select name="project_type" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="mixed_use">Mixed Use</option>
                            <option value="apartments">Apartments</option>
                            <option value="villas">Villas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Construction Status *</label>
                        <select name="construction_status" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                            <option value="planning">Planning</option>
                            <option value="pre_construction">Pre-Construction</option>
                            <option value="under_construction">Under Construction</option>
                            <option value="near_completion">Near Completion</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Starting Price (ETB) *</label>
                        <input type="number" name="starting_price" required placeholder="12500000" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('new-project-drawer').classList.add('hidden')" class="rounded-full px-4 py-1.5 text-xs font-bold text-slate-500">Cancel</button>
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-1.5 text-xs font-bold text-white hover:bg-[#254d43]">Save Project</button>
                </div>
            </form>
        </div>

        @if ($projects->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                <span class="text-3xl">🏗️</span>
                <p class="mt-2 text-sm font-semibold text-[#587165]">No projects yet.</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-[#e7ddca]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                        <tr>
                            <th class="p-3">Code</th>
                            <th class="p-3">Project</th>
                            <th class="p-3">Type</th>
                            <th class="p-3">Construction</th>
                            <th class="p-3">Completion</th>
                            <th class="p-3">Buildings</th>
                            <th class="p-3">Units</th>
                            <th class="p-3">Starting Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e8d8]">
                        @foreach ($projects as $project)
                            <tr class="transition hover:bg-[#fbf8f3]">
                                <td class="p-3 font-mono text-[11px] font-bold text-[#587165]">{{ $project->project_code }}</td>
                                <td class="p-3 font-bold text-[#1d3c34]">
                                    <a href="{{ route('developments.show', $project->slug) }}" class="hover:underline">{{ $project->name }}</a>
                                    <span class="block text-[10px] font-semibold text-slate-400">{{ $project->city }}</span>
                                </td>
                                <td class="p-3 text-[#587165]">{{ ucwords(str_replace('_', ' ', $project->project_type)) }}</td>
                                <td class="p-3"><span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-[10px] font-bold text-[#9b6c17]">{{ ucwords(str_replace('_', ' ', $project->construction_status)) }}</span></td>
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-16 overflow-hidden rounded-full bg-[#e7ddca]">
                                            <div class="h-full rounded-full bg-[#1d3c34]" style="width: {{ $project->overall_completion_percentage }}%"></div>
                                        </div>
                                        <span class="text-[11px] font-bold text-[#1d3c34]">{{ $project->overall_completion_percentage }}%</span>
                                    </div>
                                </td>
                                <td class="p-3 font-bold text-[#1d3c34]">{{ $project->buildings_count }}</td>
                                <td class="p-3 font-bold text-[#1d3c34]">{{ $project->units_count }}</td>
                                <td class="p-3 font-bold text-[#1d3c34]">{{ $project->formatted_starting_price }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
    @endif

    {{-- Buildings --}}
    @if ($canManageBuildings)
    <section id="buildings" data-section="buildings" class="section-panel hidden space-y-6 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-4 gap-4">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34]">Buildings</h2>
                <p class="text-xs text-[#587165]">Building towers and architectural blocks inventory.</p>
            </div>
            @if ($projects->isNotEmpty())
                <button type="button" onclick="document.getElementById('new-building-drawer').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#254d43] transition">
                    + Add Building
                </button>
            @endif
        </div>

        {{-- New Building Drawer --}}
        @if ($projects->isNotEmpty())
        <div id="new-building-drawer" class="hidden rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-5">
            <h3 class="text-sm font-black text-[#1d3c34] mb-3">Add Building to Project</h3>
            <form method="POST" action="{{ route('developer.buildings.store', $projects->first()) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700">Select Project *</label>
                        <select onchange="this.form.action = '/developer/projects/' + this.value + '/buildings'" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                            @foreach ($projects as $pj)
                                <option value="{{ $pj->id }}">{{ $pj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Building / Tower Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Tower B - Sunset Vista" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Floors *</label>
                        <input type="text" name="total_floors" required placeholder="e.g. G+15" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('new-building-drawer').classList.add('hidden')" class="rounded-full px-4 py-1.5 text-xs font-bold text-slate-500">Cancel</button>
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-1.5 text-xs font-bold text-white hover:bg-[#254d43]">Save Building</button>
                </div>
            </form>
        </div>
        @endif

        @if ($buildings->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                <span class="text-3xl">🏢</span>
                <p class="mt-2 text-sm font-semibold text-[#587165]">No buildings registered yet.</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-[#e7ddca]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                        <tr>
                            <th class="p-3">Building</th>
                            <th class="p-3">Project</th>
                            <th class="p-3">Floors</th>
                            <th class="p-3">Units</th>
                            <th class="p-3">Description</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e8d8]">
                        @foreach ($buildings as $building)
                            <tr class="transition hover:bg-[#fbf8f3]">
                                <td class="p-3 font-bold text-[#1d3c34]">{{ $building->name }}</td>
                                <td class="p-3 text-[#587165]">{{ $building->project->name ?? '—' }}</td>
                                <td class="p-3 text-[#587165]">{{ $building->total_floors }}</td>
                                <td class="p-3 font-bold text-[#1d3c34]">{{ $building->units_count }}</td>
                                <td class="p-3 text-[#587165]">{{ \Illuminate\Support\Str::limit($building->description, 60) }}</td>
                                <td class="p-3 text-right space-x-2">
                                    <button type="button" onclick="document.getElementById('bulk-gen-{{ $building->id }}').classList.toggle('hidden')" class="font-bold text-[#1d3c34] hover:underline">
                                        ⚡ Bulk Units
                                    </button>
                                    <form method="POST" action="{{ route('developer.buildings.destroy', $building) }}" onsubmit="return confirm('Remove building {{ $building->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-bold text-rose-600 hover:text-rose-800">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <tr id="bulk-gen-{{ $building->id }}" class="hidden bg-[#f5f0e7]/40">
                                <td colspan="6" class="p-4">
                                    <div class="rounded-xl border border-[#d9cab3] bg-white p-4">
                                        <h4 class="font-black text-xs text-[#1d3c34] mb-2">⚡ Bulk Generate Units for {{ $building->name }}</h4>
                                        <form method="POST" action="{{ route('developer.buildings.generate-units', $building) }}" class="space-y-3">
                                            @csrf
                                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3 text-xs">
                                                <div>
                                                    <label class="block font-bold text-slate-700">Floor Start *</label>
                                                    <input type="number" name="floor_start" value="1" min="1" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-2 py-1.5">
                                                </div>
                                                <div>
                                                    <label class="block font-bold text-slate-700">Floor End *</label>
                                                    <input type="number" name="floor_end" value="10" min="1" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-2 py-1.5">
                                                </div>
                                                <div>
                                                    <label class="block font-bold text-slate-700">Units/Floor *</label>
                                                    <input type="number" name="units_per_floor" value="4" min="1" max="20" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-2 py-1.5">
                                                </div>
                                                <div>
                                                    <label class="block font-bold text-slate-700">Unit Type *</label>
                                                    <select name="unit_type" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-2 py-1.5">
                                                        <option value="2_bedroom">2 Bedroom</option>
                                                        <option value="3_bedroom" selected>3 Bedroom</option>
                                                        <option value="4_bedroom">4 Bedroom</option>
                                                        <option value="penthouse">Penthouse</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block font-bold text-slate-700">Bed / Bath *</label>
                                                    <div class="flex gap-1 mt-1">
                                                        <input type="number" name="bedrooms" value="3" min="0" required class="w-1/2 rounded-xl border border-[#d9cab3] px-1 py-1.5 text-center">
                                                        <input type="number" name="bathrooms" value="2" min="1" required class="w-1/2 rounded-xl border border-[#d9cab3] px-1 py-1.5 text-center">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block font-bold text-slate-700">Area (sqm) *</label>
                                                    <input type="number" step="0.5" name="area_sqm" value="135.0" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-2 py-1.5">
                                                </div>
                                                <div>
                                                    <label class="block font-bold text-slate-700">Base Price (ETB) *</label>
                                                    <input type="number" name="base_price" value="12000000" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-2 py-1.5">
                                                </div>
                                                <div>
                                                    <label class="block font-bold text-slate-700">Increment/Floor</label>
                                                    <input type="number" name="price_per_floor_increment" value="150000" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-2 py-1.5">
                                                </div>
                                            </div>
                                            <div class="flex justify-end pt-1">
                                                <button type="submit" class="rounded-full bg-[#1d3c34] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#254d43]">
                                                    Generate Units Now
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
    @endif

    {{-- Units --}}
    @if ($canManageUnits)
    <section id="units" data-section="units" class="section-panel hidden space-y-6 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-4 gap-4">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34]">Units</h2>
                <p class="text-xs text-[#587165]">Unit inventory across buildings with availability status.</p>
            </div>
            <span class="rounded-full bg-[#fbf8f3] border border-[#d9cab3] px-3.5 py-1 text-xs font-bold text-[#1d3c34]">
                Total Units: {{ $units->count() }}
            </span>
        </div>

        @if ($units->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                <span class="text-3xl">🚪</span>
                <p class="mt-2 text-sm font-semibold text-[#587165]">No units registered yet.</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-[#e7ddca]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                        <tr>
                            <th class="p-3">Unit</th>
                            <th class="p-3">Project</th>
                            <th class="p-3">Building</th>
                            <th class="p-3">Type</th>
                            <th class="p-3">Beds / Baths</th>
                            <th class="p-3">Area</th>
                            <th class="p-3">Price</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e8d8]">
                        @foreach ($units as $unit)
                            <tr class="transition hover:bg-[#fbf8f3]">
                                <td class="p-3 font-bold text-[#1d3c34]">{{ $unit->unit_number }}</td>
                                <td class="p-3 text-[#587165]">{{ $unit->project->name ?? '—' }}</td>
                                <td class="p-3 text-[#587165]">{{ $unit->building->name ?? '—' }}</td>
                                <td class="p-3 text-[#587165]">{{ ucwords(str_replace('_', ' ', $unit->unit_type)) }}</td>
                                <td class="p-3 text-[#587165]">{{ $unit->bedrooms }} / {{ $unit->bathrooms }}</td>
                                <td class="p-3 text-[#587165]">{{ number_format($unit->area_sqm, 1) }} m²</td>
                                <td class="p-3 font-bold text-[#1d3c34]">{{ $unit->formatted_price }}</td>
                                <td class="p-3">
                                    @php
                                        $statusStyles = [
                                            'available' => 'bg-emerald-100 text-emerald-800',
                                            'reserved' => 'bg-amber-100 text-amber-800',
                                            'sold' => 'bg-[#f9ecd0] text-[#9b6c17]',
                                            'contracted' => 'bg-blue-100 text-blue-800',
                                            'blocked' => 'bg-slate-100 text-slate-600',
                                        ];
                                    @endphp
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $statusStyles[$unit->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($unit->status) }}</span>
                                </td>
                                <td class="p-3 text-right">
                                    <form method="POST" action="{{ route('developments.update-unit-status', $unit) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" onchange="this.form.submit()" class="rounded-lg border border-[#d9cab3] bg-white py-0.5 px-1.5 text-[10px] font-bold">
                                            @foreach (['available', 'reserved', 'contracted', 'sold', 'blocked'] as $ust)
                                                <option value="{{ $ust }}" {{ $unit->status === $ust ? 'selected' : '' }}>{{ ucfirst($ust) }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
    @endif

    {{-- Construction Progress --}}
    @if ($canManageConstruction)
    <section id="construction" data-section="construction" class="section-panel hidden space-y-6 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-4 gap-4">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34]">Construction Progress</h2>
                <p class="text-xs text-[#587165]">Milestone-by-milestone progress and field photos across projects.</p>
            </div>
            @if ($projects->isNotEmpty())
                <button type="button" onclick="document.getElementById('new-milestone-drawer').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#254d43] transition">
                    + Add Milestone
                </button>
            @endif
        </div>

        {{-- Add Milestone Drawer --}}
        @if ($projects->isNotEmpty())
        <div id="new-milestone-drawer" class="hidden rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-5">
            <h3 class="text-sm font-black text-[#1d3c34] mb-3">Add Construction Milestone</h3>
            <form method="POST" action="{{ route('developer.milestones.store', $projects->first()) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700">Project *</label>
                        <select onchange="this.form.action = '/developer/projects/' + this.value + '/milestones'" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                            @foreach ($projects as $pj)
                                <option value="{{ $pj->id }}">{{ $pj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Milestone Name *</label>
                        <input type="text" name="milestone_name" required placeholder="e.g. 5th Floor Slab Pouring" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Percentage Completed (0-100) *</label>
                        <input type="number" name="percentage" min="0" max="100" value="0" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Status *</label>
                        <select name="status" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Target Date</label>
                        <input type="text" name="target_date" placeholder="Q3 2027" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Progress Photo</label>
                        <input type="file" name="photo" accept="image/*" class="mt-1 w-full text-xs">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('new-milestone-drawer').classList.add('hidden')" class="rounded-full px-4 py-1.5 text-xs font-bold text-slate-500">Cancel</button>
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-1.5 text-xs font-bold text-white hover:bg-[#254d43]">Save Milestone</button>
                </div>
            </form>
        </div>
        @endif

        @if ($milestones->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                <span class="text-3xl">🚧</span>
                <p class="mt-2 text-sm font-semibold text-[#587165]">No construction milestones recorded yet.</p>
            </div>
        @else
            @foreach ($projects->groupBy('id') as $projectGroup)
                @php $projectMilestones = $milestones->where('project_id', $projectGroup->first()->id); @endphp
                @if ($projectMilestones->isNotEmpty())
                    <div class="space-y-3">
                        <h3 class="text-sm font-black text-[#1d3c34]">{{ $projectMilestones->first()->project->name ?? 'Project' }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($projectMilestones as $milestone)
                                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf8f3] p-4 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-bold text-[#1d3c34]">{{ $milestone->milestone_name }}</p>
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $milestone->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($milestone->status === 'in_progress' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">{{ ucwords(str_replace('_', ' ', $milestone->status)) }}</span>
                                        </div>
                                        <div class="mt-2 flex items-center gap-3">
                                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-[#e7ddca]">
                                                <div class="h-full rounded-full bg-[#1d3c34]" style="width: {{ $milestone->percentage }}%"></div>
                                            </div>
                                            <span class="text-xs font-black text-[#1d3c34]">{{ $milestone->percentage }}%</span>
                                        </div>
                                        @if ($milestone->target_date)
                                            <p class="mt-1 text-[11px] text-slate-500">Target: {{ $milestone->target_date }}</p>
                                        @endif
                                        @if ($milestone->photo_path)
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $milestone->photo_path) }}" alt="{{ $milestone->milestone_name }}" class="h-24 w-full rounded-xl object-cover border border-[#d9cab3]">
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-3 pt-3 border-t border-[#e7ddca] flex items-center justify-between text-xs">
                                        <form method="POST" action="{{ route('developer.milestones.update', [$milestone->project_id, $milestone]) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="milestone_name" value="{{ $milestone->milestone_name }}">
                                            <input type="hidden" name="status" value="{{ $milestone->status }}">
                                            <input type="number" name="percentage" value="{{ $milestone->percentage }}" min="0" max="100" class="w-16 rounded-lg border border-[#d9cab3] px-2 py-0.5 text-xs text-center">
                                            <button type="submit" class="font-bold text-[#1d3c34] hover:underline">Update %</button>
                                        </form>
                                        <form method="POST" action="{{ route('developer.milestones.destroy', [$milestone->project_id, $milestone]) }}" onsubmit="return confirm('Remove milestone?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-bold text-rose-600 hover:text-rose-800">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </section>
    @endif

    {{-- Pricing --}}
    @if ($canManagePricing)
    <section id="pricing" data-section="pricing" class="section-panel hidden space-y-6 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
        <h2 class="text-xl font-black text-[#1d3c34]">Pricing</h2>
        <p class="text-xs text-[#587165]">Unit pricing ranges and sales velocity per development project.</p>

        @if ($pricingRows->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                <span class="text-3xl">💰</span>
                <p class="mt-2 text-sm font-semibold text-[#587165]">No pricing data yet.</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-[#e7ddca]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                        <tr>
                            <th class="p-3">Project</th>
                            <th class="p-3">Starting Price</th>
                            <th class="p-3">Unit Range</th>
                            <th class="p-3">Units</th>
                            <th class="p-3">Available</th>
                            <th class="p-3">Reserved</th>
                            <th class="p-3">Sold</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e8d8]">
                        @foreach ($pricingRows as $row)
                            <tr class="transition hover:bg-[#fbf8f3]">
                                <td class="p-3 font-bold text-[#1d3c34]">{{ $row['project']->name }}</td>
                                <td class="p-3 font-bold text-[#1d3c34]">{{ $row['project']->formatted_starting_price }}</td>
                                <td class="p-3 text-[#587165]">
                                    @if ($row['min_price'] !== null)
                                        {{ number_format($row['min_price'], 0) }} &mdash; {{ number_format($row['max_price'], 0) }} ETB
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="p-3 font-bold text-[#1d3c34]">{{ $row['unit_count'] }}</td>
                                <td class="p-3 font-bold text-emerald-700">{{ $row['available_count'] }}</td>
                                <td class="p-3 font-bold text-amber-700">{{ $row['reserved_count'] }}</td>
                                <td class="p-3 font-bold text-[#9b6c17]">{{ $row['sold_count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
    @endif

    {{-- Payment Plans --}}
    @if ($canManagePaymentPlans)
    <section id="payment-plans" data-section="payment-plans" class="section-panel hidden space-y-6 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-4 gap-4">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34]">Payment Plans</h2>
                <p class="text-xs text-[#587165]">Off-plan installment schedules and milestone triggers.</p>
            </div>
            @if ($projects->isNotEmpty())
                <button type="button" onclick="document.getElementById('new-plan-drawer').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#254d43] transition">
                    + Add Installment Plan
                </button>
            @endif
        </div>

        {{-- Add Payment Plan Drawer --}}
        @if ($projects->isNotEmpty())
        <div id="new-plan-drawer" class="hidden rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-5">
            <h3 class="text-sm font-black text-[#1d3c34] mb-3">Add Installment Step</h3>
            <form method="POST" action="{{ route('developer.payment-plans.store', $projects->first()) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700">Project *</label>
                        <select onchange="this.form.action = '/developer/projects/' + this.value + '/payment-plans'" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                            @foreach ($projects as $pj)
                                <option value="{{ $pj->id }}">{{ $pj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Installment Name *</label>
                        <input type="text" name="installment_name" required placeholder="e.g. 15% Roofing Milestone" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Percentage (%) *</label>
                        <input type="number" step="0.5" name="percentage" min="1" max="100" required placeholder="15" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Milestone Trigger Event</label>
                        <input type="text" name="milestone_trigger" placeholder="e.g. Upon Roof Slab Completion" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('new-plan-drawer').classList.add('hidden')" class="rounded-full px-4 py-1.5 text-xs font-bold text-slate-500">Cancel</button>
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-1.5 text-xs font-bold text-white hover:bg-[#254d43]">Save Installment</button>
                </div>
            </form>
        </div>
        @endif

        @if ($paymentPlans->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                <span class="text-3xl">📅</span>
                <p class="mt-2 text-sm font-semibold text-[#587165]">No payment plans defined yet.</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-[#e7ddca]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                        <tr>
                            <th class="p-3">Project</th>
                            <th class="p-3">#</th>
                            <th class="p-3">Installment</th>
                            <th class="p-3">Percentage</th>
                            <th class="p-3">Trigger</th>
                            <th class="p-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e8d8]">
                        @foreach ($paymentPlans as $plan)
                            <tr class="transition hover:bg-[#fbf8f3]">
                                <td class="p-3 font-bold text-[#1d3c34]">{{ $plan->project->name ?? '—' }}</td>
                                <td class="p-3 text-[#587165]">{{ $plan->order_index }}</td>
                                <td class="p-3 font-bold text-[#1d3c34]">{{ $plan->installment_name }}</td>
                                <td class="p-3 font-bold text-[#1d3c34]">{{ rtrim(rtrim(number_format((float) $plan->percentage, 2), '0'), '.') }}%</td>
                                <td class="p-3 text-[#587165]">{{ $plan->milestone_trigger ?? '—' }}</td>
                                <td class="p-3 text-right">
                                    <form method="POST" action="{{ route('developer.payment-plans.destroy', $plan) }}" onsubmit="return confirm('Remove installment?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-bold text-rose-600 hover:text-rose-800">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
    @endif

    {{-- Buyers --}}
    @if ($canManageBuyers)
    <section id="buyers" data-section="buyers" class="section-panel hidden space-y-6 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
        <h2 class="text-xl font-black text-[#1d3c34]">Buyers</h2>
        <p class="text-xs text-[#587165]">Registered off-plan buyers across all unit reservations.</p>

        @if ($reservations->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                <span class="text-3xl">🧑‍🤝‍🧑</span>
                <p class="mt-2 text-sm font-semibold text-[#587165]">No buyers registered yet.</p>
            </div>
        @else
            @php $buyers = $reservations->groupBy('buyer_email'); @endphp
            <div class="overflow-x-auto rounded-2xl border border-[#e7ddca]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                        <tr>
                            <th class="p-3">Buyer</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Phone</th>
                            <th class="p-3">Reservations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e8d8]">
                        @foreach ($buyers as $buyerReservations)
                            <tr class="transition hover:bg-[#fbf8f3]">
                                <td class="p-3 font-bold text-[#1d3c34]">{{ $buyerReservations->first()->buyer_name }}</td>
                                <td class="p-3 text-[#587165]">{{ $buyerReservations->first()->buyer_email }}</td>
                                <td class="p-3 text-[#587165]">{{ $buyerReservations->first()->buyer_phone }}</td>
                                <td class="p-3 font-bold text-[#1d3c34]">{{ $buyerReservations->count() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
    @endif

    {{-- Reservations --}}
    @if ($canManageReservations)
    <section id="reservations" data-section="reservations" class="section-panel hidden space-y-6 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
        <h2 class="text-xl font-black text-[#1d3c34]">Reservations</h2>
        <p class="text-xs text-[#587165]">Unit reservations and buyer inquiries progression.</p>

        @if ($reservations->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                <span class="text-3xl">📝</span>
                <p class="mt-2 text-sm font-semibold text-[#587165]">No reservations yet.</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-[#e7ddca]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                        <tr>
                            <th class="p-3">Reference</th>
                            <th class="p-3">Project</th>
                            <th class="p-3">Unit</th>
                            <th class="p-3">Buyer</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Date</th>
                            <th class="p-3 text-right">Transition Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e8d8]">
                        @foreach ($reservations as $reservation)
                            <tr class="transition hover:bg-[#fbf8f3]">
                                <td class="p-3 font-mono text-[11px] font-bold text-[#587165]">{{ $reservation->reservation_reference }}</td>
                                <td class="p-3 text-[#587165]">{{ $reservation->project->name ?? '—' }}</td>
                                <td class="p-3 font-bold text-[#1d3c34]">{{ $reservation->unit->unit_number ?? '—' }}</td>
                                <td class="p-3">
                                    <span class="font-bold text-[#1d3c34]">{{ $reservation->buyer_name }}</span>
                                    <span class="block text-[10px] text-slate-400">{{ $reservation->buyer_email }}</span>
                                </td>
                                <td class="p-3">
                                    @php
                                        $reservationStyles = [
                                            'pending' => 'bg-amber-100 text-amber-800',
                                            'reserved' => 'bg-emerald-100 text-emerald-800',
                                            'confirmed' => 'bg-emerald-100 text-emerald-800',
                                            'contract_signed' => 'bg-blue-100 text-blue-800',
                                            'contracted' => 'bg-[#f9ecd0] text-[#9b6c17]',
                                            'sold' => 'bg-purple-100 text-purple-800',
                                            'cancelled' => 'bg-slate-100 text-slate-600',
                                        ];
                                    @endphp
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $reservationStyles[$reservation->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span>
                                </td>
                                <td class="p-3 text-[#587165]">{{ $reservation->created_at->format('M d, Y') }}</td>
                                <td class="p-3 text-right">
                                    <form method="POST" action="{{ route('developer.reservations.status', $reservation) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" onchange="this.form.submit()" class="rounded-lg border border-[#d9cab3] bg-white py-0.5 px-2 text-[10px] font-bold">
                                            @foreach (['pending' => 'Pending', 'reserved' => 'Reserved', 'contract_signed' => 'Contract Signed', 'sold' => 'Sold', 'cancelled' => 'Cancelled'] as $resVal => $resLbl)
                                                <option value="{{ $resVal }}" {{ $reservation->status === $resVal ? 'selected' : '' }}>{{ $resLbl }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
    @endif
</div>
@endsection
