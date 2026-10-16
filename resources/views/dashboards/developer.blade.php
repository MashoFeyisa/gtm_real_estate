@extends('dashboards.layout')

@section('title', 'Developer Portal — ' . config('app.name', 'GTM Real Estate'))
@section('portal_name', 'Developer Portal')

@section('sidebar-nav')
    @if ($canManageProjects)
        <x-sidebar-item href="#projects" icon="🏗️" label="Projects" :count="$stats['projects']" />
    @endif
    @if ($canManageBuildings)
        <x-sidebar-item href="#buildings" icon="🏢" label="Buildings" :count="$stats['buildings']" />
    @endif
    @if ($canManageUnits)
        <x-sidebar-item href="#units" icon="🚪" label="Units" :count="$stats['units']" />
    @endif
    @if ($canManageConstruction)
        <x-sidebar-item href="#construction" icon="🚧" label="Construction Progress" :count="$stats['avg_completion'] . '%'" />
    @endif
    @if ($canManagePricing)
        <x-sidebar-item href="#pricing" icon="💰" label="Pricing" />
    @endif
    @if ($canManagePaymentPlans)
        <x-sidebar-item href="#payment-plans" icon="📅" label="Payment Plans" />
    @endif
    @if ($canManageBuyers)
        <x-sidebar-item href="#buyers" icon="🧑‍🤝‍🧑" label="Buyers" :count="$stats['buyers']" />
    @endif
    @if ($canManageReservations)
        <x-sidebar-item href="#reservations" icon="📝" label="Reservations" :count="$stats['reservations']" />
    @endif
@endsection

@section('content')
<div class="space-y-8">
    {{-- Alerts --}}
    @if (session('success'))
        <div class="token-alert token-alert-success text-xs font-bold">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="token-alert token-alert-danger text-xs font-medium">
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
        <div class="token-hero">
            <div class="flex flex-wrap items-center gap-2">
                <span class="token-hero-chip">Developer Portal</span>
                <span class="token-hero-chip">Real Estate Development Hub</span>
            </div>
            <h1 class="mt-3 text-2xl md:text-3xl font-black tracking-tight token-hero-title font-serif">Welcome back, {{ auth()->user()->name }}.</h1>
            <p class="mt-2 max-w-2xl text-xs md:text-sm token-hero-sub">Projects, buildings, units, construction progress, pricing, payment plans, buyers, and reservations &mdash; everything your developer role manages, in one desk.</p>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 border-t border-[var(--token-hero-border)] pt-6">
                <div class="token-hero-stat">
                    <p class="text-2xl font-black token-hero-stat-value">{{ $stats['projects'] }}</p>
                    <p class="mt-1 token-hero-stat-label">Projects</p>
                    <p class="mt-0.5 text-[10px] token-hero-sub">{{ $stats['under_construction'] }} under construction</p>
                </div>
                <div class="token-hero-stat">
                    <p class="text-2xl font-black token-hero-stat-value">{{ $stats['buildings'] }}</p>
                    <p class="mt-1 token-hero-stat-label">Buildings</p>
                </div>
                <div class="token-hero-stat">
                    <p class="text-2xl font-black token-hero-stat-value">{{ $stats['units'] }}</p>
                    <p class="mt-1 token-hero-stat-label">Units</p>
                    <p class="mt-0.5 text-[10px] token-hero-sub">{{ $stats['available_units'] }} available</p>
                </div>
                <div class="token-hero-stat">
                    <p class="text-2xl font-black token-hero-stat-value">{{ $stats['reservations'] }}</p>
                    <p class="mt-1 token-hero-stat-label">Reservations</p>
                    <p class="mt-0.5 text-[10px] token-hero-sub">{{ $stats['pending_reservations'] }} pending</p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="token-stat-card">
                <div class="flex items-center justify-between">
                    <p class="token-stat-title">Avg. Completion</p>
                    <span class="text-xl">🚧</span>
                </div>
                <p class="token-stat-value token-stat-primary">{{ $stats['avg_completion'] }}%</p>
                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-[var(--token-border)]">
                    <div class="h-full rounded-full bg-[var(--token-primary)]" style="width: {{ $stats['avg_completion'] }}%"></div>
                </div>
            </div>
            <div class="token-stat-card">
                <div class="flex items-center justify-between">
                    <p class="token-stat-title">Available Units</p>
                    <span class="text-xl">🚪</span>
                </div>
                <p class="token-stat-value token-stat-success">{{ $stats['available_units'] }}</p>
                <p class="mt-1 text-[11px] token-muted">Ready to sell or reserve</p>
            </div>
            <div class="token-stat-card">
                <div class="flex items-center justify-between">
                    <p class="token-stat-title">Registered Buyers</p>
                    <span class="text-xl">🧑‍🤝‍🧑</span>
                </div>
                <p class="token-stat-value token-stat-primary">{{ $stats['buyers'] }}</p>
                <p class="mt-1 text-[11px] token-muted">Across all reservations</p>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </section>

    {{-- Projects --}}
    @if ($canManageProjects)
    <section id="projects" data-section="projects" class="section-panel hidden space-y-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[var(--token-border-subtle)] pb-4 gap-4">
            <div>
                <h2 class="token-section-title !border-b-0 !pb-0">Projects</h2>
                <p class="text-xs token-muted">Master off-plan development projects you manage.</p>
            </div>
            <button type="button" onclick="document.getElementById('new-project-drawer').classList.toggle('hidden')" class="token-btn token-btn-primary">
                + New Project
            </button>
        </div>

        {{-- New Project Drawer --}}
        <div id="new-project-drawer" class="hidden rounded-2xl border border-[var(--token-border)] bg-[var(--token-surface-subtle)] p-5">
            <h3 class="text-sm font-black mb-3">Create Development Project</h3>
            <form method="POST" action="{{ route('developments.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="token-label">Project Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Bole Horizon Towers" class="mt-1 token-field">
                    </div>
                    <div>
                        <label class="token-label">Developer / Company Name *</label>
                        <input type="text" name="developer_name" value="{{ auth()->user()->name }}" required class="mt-1 token-field">
                    </div>
                    <div>
                        <label class="token-label">City *</label>
                        <input type="text" name="city" value="Addis Ababa" required class="mt-1 token-field">
                    </div>
                    <div>
                        <label class="token-label">Subcity</label>
                        <input type="text" name="subcity" placeholder="e.g. Bole" class="mt-1 token-field">
                    </div>
                    <div>
                        <label class="token-label">Project Type *</label>
                        <select name="project_type" required class="mt-1 token-field">
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="mixed_use">Mixed Use</option>
                            <option value="apartments">Apartments</option>
                            <option value="villas">Villas</option>
                        </select>
                    </div>
                    <div>
                        <label class="token-label">Construction Status *</label>
                        <select name="construction_status" required class="mt-1 token-field">
                            <option value="planning">Planning</option>
                            <option value="pre_construction">Pre-Construction</option>
                            <option value="under_construction">Under Construction</option>
                            <option value="near_completion">Near Completion</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="token-label">Starting Price (ETB) *</label>
                        <input type="number" name="starting_price" required placeholder="12500000" class="mt-1 token-field">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('new-project-drawer').classList.add('hidden')" class="token-btn token-btn-ghost">Cancel</button>
                    <button type="submit" class="token-btn token-btn-primary">Save Project</button>
                </div>
            </form>
        </div>

        @if ($projects->isEmpty())
            <div class="rounded-2xl border border-dashed border-[var(--token-border)] bg-[var(--token-surface-subtle)] py-12 text-center">
                <span class="text-3xl">🏗️</span>
                <p class="mt-2 text-sm font-semibold token-muted">No projects yet.</p>
            </div>
        @else
            <div class="token-table-wrap">
                <table class="token-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Project</th>
                            <th>Type</th>
                            <th>Construction</th>
                            <th>Completion</th>
                            <th>Buildings</th>
                            <th>Units</th>
                            <th>Starting Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            <tr class="transition">
                                <td class="token-muted font-mono text-[11px] font-bold">{{ $project->project_code }}</td>
                                <td class="font-bold">
                                    <a href="{{ route('developments.show', $project->slug) }}" class="hover:underline">{{ $project->name }}</a>
                                    <span class="block text-[10px] font-semibold token-muted">{{ $project->city }}</span>
                                </td>
                                <td class="token-muted">{{ ucwords(str_replace('_', ' ', $project->project_type)) }}</td>
                                <td><span class="token-badge token-badge-warning">{{ ucwords(str_replace('_', ' ', $project->construction_status)) }}</span></td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-16 overflow-hidden rounded-full bg-[var(--token-border)]">
                                            <div class="h-full rounded-full bg-[var(--token-primary)]" style="width: {{ $project->overall_completion_percentage }}%"></div>
                                        </div>
                                        <span class="text-[11px] font-bold">{{ $project->overall_completion_percentage }}%</span>
                                    </div>
                                </td>
                                <td class="font-bold">{{ $project->buildings_count }}</td>
                                <td class="font-bold">{{ $project->units_count }}</td>
                                <td class="font-bold">{{ $project->formatted_starting_price }}</td>
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
    <section id="buildings" data-section="buildings" class="section-panel hidden space-y-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[var(--token-border-subtle)] pb-4 gap-4">
            <div>
                <h2 class="token-section-title !border-b-0 !pb-0">Buildings</h2>
                <p class="text-xs token-muted">Building towers and architectural blocks inventory.</p>
            </div>
            @if ($projects->isNotEmpty())
                <button type="button" onclick="document.getElementById('new-building-drawer').classList.toggle('hidden')" class="token-btn token-btn-primary">
                    + Add Building
                </button>
            @endif
        </div>

        {{-- New Building Drawer --}}
        @if ($projects->isNotEmpty())
        <div id="new-building-drawer" class="hidden rounded-2xl border border-[var(--token-border)] bg-[var(--token-surface-subtle)] p-5">
            <h3 class="text-sm font-black mb-3">Add Building to Project</h3>
            <form method="POST" action="{{ route('developer.buildings.store', $projects->first()) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="token-label">Select Project *</label>
                        <select onchange="this.form.action = '/developer/projects/' + this.value + '/buildings'" required class="mt-1 token-field">
                            @foreach ($projects as $pj)
                                <option value="{{ $pj->id }}">{{ $pj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="token-label">Building / Tower Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Tower B - Sunset Vista" class="mt-1 token-field">
                    </div>
                    <div>
                        <label class="token-label">Floors *</label>
                        <input type="text" name="total_floors" required placeholder="e.g. G+15" class="mt-1 token-field">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('new-building-drawer').classList.add('hidden')" class="token-btn token-btn-ghost">Cancel</button>
                    <button type="submit" class="token-btn token-btn-primary">Save Building</button>
                </div>
            </form>
        </div>
        @endif

        @if ($buildings->isEmpty())
            <div class="rounded-2xl border border-dashed border-[var(--token-border)] bg-[var(--token-surface-subtle)] py-12 text-center">
                <span class="text-3xl">🏢</span>
                <p class="mt-2 text-sm font-semibold token-muted">No buildings registered yet.</p>
            </div>
        @else
            <div class="token-table-wrap">
                <table class="token-table">
                    <thead>
                        <tr>
                            <th>Building</th>
                            <th>Project</th>
                            <th>Floors</th>
                            <th>Units</th>
                            <th>Description</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($buildings as $building)
                            <tr class="transition">
                                <td class="font-bold">{{ $building->name }}</td>
                                <td class="token-muted">{{ $building->project->name ?? '—' }}</td>
                                <td class="token-muted">{{ $building->total_floors }}</td>
                                <td class="font-bold">{{ $building->units_count }}</td>
                                <td class="token-muted">{{ \Illuminate\Support\Str::limit($building->description, 60) }}</td>
                                <td class="text-right space-x-2">
                                    <button type="button" onclick="document.getElementById('bulk-gen-{{ $building->id }}').classList.toggle('hidden')" class="token-btn token-btn-ghost hover:underline">
                                        ⚡ Bulk Units
                                    </button>
                                    <form method="POST" action="{{ route('developer.buildings.destroy', $building) }}" onsubmit="return confirm('Remove building {{ $building->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="token-btn token-btn-ghost !text-[var(--token-danger)]">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <tr id="bulk-gen-{{ $building->id }}" class="hidden bg-[var(--token-surface-subtle)]">
                                <td colspan="6">
                                    <div class="token-card">
                                        <h4 class="font-black text-xs mb-2">⚡ Bulk Generate Units for {{ $building->name }}</h4>
                                        <form method="POST" action="{{ route('developer.buildings.generate-units', $building) }}" class="space-y-3">
                                            @csrf
                                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3 text-xs">
                                                <div>
                                                    <label class="token-label">Floor Start *</label>
                                                    <input type="number" name="floor_start" value="1" min="1" required class="mt-1 token-field">
                                                </div>
                                                <div>
                                                    <label class="token-label">Floor End *</label>
                                                    <input type="number" name="floor_end" value="10" min="1" required class="mt-1 token-field">
                                                </div>
                                                <div>
                                                    <label class="token-label">Units/Floor *</label>
                                                    <input type="number" name="units_per_floor" value="4" min="1" max="20" required class="mt-1 token-field">
                                                </div>
                                                <div>
                                                    <label class="token-label">Unit Type *</label>
                                                    <select name="unit_type" required class="mt-1 token-field">
                                                        <option value="2_bedroom">2 Bedroom</option>
                                                        <option value="3_bedroom" selected>3 Bedroom</option>
                                                        <option value="4_bedroom">4 Bedroom</option>
                                                        <option value="penthouse">Penthouse</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="token-label">Bed / Bath *</label>
                                                    <div class="flex gap-1 mt-1">
                                                        <input type="number" name="bedrooms" value="3" min="0" required class="token-field !w-1/2 text-center">
                                                        <input type="number" name="bathrooms" value="2" min="1" required class="token-field !w-1/2 text-center">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="token-label">Area (sqm) *</label>
                                                    <input type="number" step="0.5" name="area_sqm" value="135.0" required class="mt-1 token-field">
                                                </div>
                                                <div>
                                                    <label class="token-label">Base Price (ETB) *</label>
                                                    <input type="number" name="base_price" value="12000000" required class="mt-1 token-field">
                                                </div>
                                                <div>
                                                    <label class="token-label">Increment/Floor</label>
                                                    <input type="number" name="price_per_floor_increment" value="150000" class="mt-1 token-field">
                                                </div>
                                            </div>
                                            <div class="flex justify-end pt-1">
                                                <button type="submit" class="token-btn token-btn-primary">
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
    <section id="units" data-section="units" class="section-panel hidden space-y-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[var(--token-border-subtle)] pb-4 gap-4">
            <div>
                <h2 class="token-section-title !border-b-0 !pb-0">Units</h2>
                <p class="text-xs token-muted">Unit inventory across buildings with availability status.</p>
            </div>
            <span class="token-badge token-badge-neutral">
                Total Units: {{ $units->count() }}
            </span>
        </div>

        @if ($units->isEmpty())
            <div class="rounded-2xl border border-dashed border-[var(--token-border)] bg-[var(--token-surface-subtle)] py-12 text-center">
                <span class="text-3xl">🚪</span>
                <p class="mt-2 text-sm font-semibold token-muted">No units registered yet.</p>
            </div>
        @else
            <div class="token-table-wrap">
                <table class="token-table">
                    <thead>
                        <tr>
                            <th>Unit</th>
                            <th>Project</th>
                            <th>Building</th>
                            <th>Type</th>
                            <th>Beds / Baths</th>
                            <th>Area</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($units as $unit)
                            <tr class="transition">
                                <td class="font-bold">{{ $unit->unit_number }}</td>
                                <td class="token-muted">{{ $unit->project->name ?? '—' }}</td>
                                <td class="token-muted">{{ $unit->building->name ?? '—' }}</td>
                                <td class="token-muted">{{ ucwords(str_replace('_', ' ', $unit->unit_type)) }}</td>
                                <td class="token-muted">{{ $unit->bedrooms }} / {{ $unit->bathrooms }}</td>
                                <td class="token-muted">{{ number_format($unit->area_sqm, 1) }} m²</td>
                                <td class="font-bold">{{ $unit->formatted_price }}</td>
                                <td>
                                    @php
                                        $statusStyles = [
                                            'available' => 'token-badge-success',
                                            'reserved' => 'token-badge-warning',
                                            'sold' => 'token-badge-warning',
                                            'contracted' => 'token-badge-info',
                                            'blocked' => 'token-badge-neutral',
                                        ];
                                    @endphp
                                    <span class="token-badge {{ $statusStyles[$unit->status] ?? 'token-badge-neutral' }}">{{ ucfirst($unit->status) }}</span>
                                </td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('developments.update-unit-status', $unit) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" onchange="this.form.submit()" class="token-field !py-0.5 !px-1.5 !text-[10px] font-bold">
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
    <section id="construction" data-section="construction" class="section-panel hidden space-y-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[var(--token-border-subtle)] pb-4 gap-4">
            <div>
                <h2 class="token-section-title !border-b-0 !pb-0">Construction Progress</h2>
                <p class="text-xs token-muted">Milestone-by-milestone progress and field photos across projects.</p>
            </div>
            @if ($projects->isNotEmpty())
                <button type="button" onclick="document.getElementById('new-milestone-drawer').classList.toggle('hidden')" class="token-btn token-btn-primary">
                    + Add Milestone
                </button>
            @endif
        </div>

        {{-- Add Milestone Drawer --}}
        @if ($projects->isNotEmpty())
        <div id="new-milestone-drawer" class="hidden rounded-2xl border border-[var(--token-border)] bg-[var(--token-surface-subtle)] p-5">
            <h3 class="text-sm font-black mb-3">Add Construction Milestone</h3>
            <form method="POST" action="{{ route('developer.milestones.store', $projects->first()) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    <div>
                        <label class="token-label">Project *</label>
                        <select onchange="this.form.action = '/developer/projects/' + this.value + '/milestones'" required class="mt-1 token-field">
                            @foreach ($projects as $pj)
                                <option value="{{ $pj->id }}">{{ $pj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="token-label">Milestone Name *</label>
                        <input type="text" name="milestone_name" required placeholder="e.g. 5th Floor Slab Pouring" class="mt-1 token-field">
                    </div>
                    <div>
                        <label class="token-label">Percentage Completed (0-100) *</label>
                        <input type="number" name="percentage" min="0" max="100" value="0" required class="mt-1 token-field">
                    </div>
                    <div>
                        <label class="token-label">Status *</label>
                        <select name="status" required class="mt-1 token-field">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="token-label">Target Date</label>
                        <input type="text" name="target_date" placeholder="Q3 2027" class="mt-1 token-field">
                    </div>
                    <div>
                        <label class="token-label">Progress Photo</label>
                        <input type="file" name="photo" accept="image/*" class="mt-1 w-full text-xs">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('new-milestone-drawer').classList.add('hidden')" class="token-btn token-btn-ghost">Cancel</button>
                    <button type="submit" class="token-btn token-btn-primary">Save Milestone</button>
                </div>
            </form>
        </div>
        @endif

        @if ($milestones->isEmpty())
            <div class="rounded-2xl border border-dashed border-[var(--token-border)] bg-[var(--token-surface-subtle)] py-12 text-center">
                <span class="text-3xl">🚧</span>
                <p class="mt-2 text-sm font-semibold token-muted">No construction milestones recorded yet.</p>
            </div>
        @else
            @foreach ($projects->groupBy('id') as $projectGroup)
                @php $projectMilestones = $milestones->where('project_id', $projectGroup->first()->id); @endphp
                @if ($projectMilestones->isNotEmpty())
                    <div class="space-y-3">
                        <h3 class="text-sm font-black">{{ $projectMilestones->first()->project->name ?? 'Project' }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($projectMilestones as $milestone)
                                <div class="rounded-2xl border border-[var(--token-border)] bg-[var(--token-surface-subtle)] p-4 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-bold">{{ $milestone->milestone_name }}</p>
                                            <span class="token-badge {{ $milestone->status === 'completed' ? 'token-badge-success' : ($milestone->status === 'in_progress' ? 'token-badge-warning' : 'token-badge-neutral') }}">{{ ucwords(str_replace('_', ' ', $milestone->status)) }}</span>
                                        </div>
                                        <div class="mt-2 flex items-center gap-3">
                                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-[var(--token-border)]">
                                                <div class="h-full rounded-full bg-[var(--token-primary)]" style="width: {{ $milestone->percentage }}%"></div>
                                            </div>
                                            <span class="text-xs font-black">{{ $milestone->percentage }}%</span>
                                        </div>
                                        @if ($milestone->target_date)
                                            <p class="mt-1 text-[11px] token-muted">Target: {{ $milestone->target_date }}</p>
                                        @endif
                                        @if ($milestone->photo_path)
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $milestone->photo_path) }}" alt="{{ $milestone->milestone_name }}" class="h-24 w-full rounded-xl object-cover border border-[var(--token-border)]">
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-3 pt-3 border-t border-[var(--token-border-subtle)] flex items-center justify-between text-xs">
                                        <form method="POST" action="{{ route('developer.milestones.update', [$milestone->project_id, $milestone]) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="milestone_name" value="{{ $milestone->milestone_name }}">
                                            <input type="hidden" name="status" value="{{ $milestone->status }}">
                                            <input type="number" name="percentage" value="{{ $milestone->percentage }}" min="0" max="100" class="token-field !w-16 text-center">
                                            <button type="submit" class="token-btn token-btn-ghost hover:underline">Update %</button>
                                        </form>
                                        <form method="POST" action="{{ route('developer.milestones.destroy', [$milestone->project_id, $milestone]) }}" onsubmit="return confirm('Remove milestone?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="token-btn token-btn-ghost !text-[var(--token-danger)]">Delete</button>
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
    <section id="pricing" data-section="pricing" class="section-panel hidden space-y-6 token-card">
        <h2 class="token-section-title !border-b-0 !pb-0">Pricing</h2>
        <p class="text-xs token-muted">Unit pricing ranges and sales velocity per development project.</p>

        @if ($pricingRows->isEmpty())
            <div class="rounded-2xl border border-dashed border-[var(--token-border)] bg-[var(--token-surface-subtle)] py-12 text-center">
                <span class="text-3xl">💰</span>
                <p class="mt-2 text-sm font-semibold token-muted">No pricing data yet.</p>
            </div>
        @else
            <div class="token-table-wrap">
                <table class="token-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Starting Price</th>
                            <th>Unit Range</th>
                            <th>Units</th>
                            <th>Available</th>
                            <th>Reserved</th>
                            <th>Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pricingRows as $row)
                            <tr class="transition">
                                <td class="font-bold">{{ $row['project']->name }}</td>
                                <td class="font-bold">{{ $row['project']->formatted_starting_price }}</td>
                                <td class="token-muted">
                                    @if ($row['min_price'] !== null)
                                        {{ number_format($row['min_price'], 0) }} &mdash; {{ number_format($row['max_price'], 0) }} ETB
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="font-bold">{{ $row['unit_count'] }}</td>
                                <td class="font-bold text-[var(--token-success)]">{{ $row['available_count'] }}</td>
                                <td class="font-bold text-[var(--token-warning)]">{{ $row['reserved_count'] }}</td>
                                <td class="font-bold text-[var(--token-accent)]">{{ $row['sold_count'] }}</td>
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
    <section id="payment-plans" data-section="payment-plans" class="section-panel hidden space-y-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[var(--token-border-subtle)] pb-4 gap-4">
            <div>
                <h2 class="token-section-title !border-b-0 !pb-0">Payment Plans</h2>
                <p class="text-xs token-muted">Off-plan installment schedules and milestone triggers.</p>
            </div>
            @if ($projects->isNotEmpty())
                <button type="button" onclick="document.getElementById('new-plan-drawer').classList.toggle('hidden')" class="token-btn token-btn-primary">
                    + Add Installment Plan
                </button>
            @endif
        </div>

        {{-- Add Payment Plan Drawer --}}
        @if ($projects->isNotEmpty())
        <div id="new-plan-drawer" class="hidden rounded-2xl border border-[var(--token-border)] bg-[var(--token-surface-subtle)] p-5">
            <h3 class="text-sm font-black mb-3">Add Installment Step</h3>
            <form method="POST" action="{{ route('developer.payment-plans.store', $projects->first()) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                    <div>
                        <label class="token-label">Project *</label>
                        <select onchange="this.form.action = '/developer/projects/' + this.value + '/payment-plans'" required class="mt-1 token-field">
                            @foreach ($projects as $pj)
                                <option value="{{ $pj->id }}">{{ $pj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="token-label">Installment Name *</label>
                        <input type="text" name="installment_name" required placeholder="e.g. 15% Roofing Milestone" class="mt-1 token-field">
                    </div>
                    <div>
                        <label class="token-label">Percentage (%) *</label>
                        <input type="number" step="0.5" name="percentage" min="1" max="100" required placeholder="15" class="mt-1 token-field">
                    </div>
                    <div>
                        <label class="token-label">Milestone Trigger Event</label>
                        <input type="text" name="milestone_trigger" placeholder="e.g. Upon Roof Slab Completion" class="mt-1 token-field">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('new-plan-drawer').classList.add('hidden')" class="token-btn token-btn-ghost">Cancel</button>
                    <button type="submit" class="token-btn token-btn-primary">Save Installment</button>
                </div>
            </form>
        </div>
        @endif

        @if ($paymentPlans->isEmpty())
            <div class="rounded-2xl border border-dashed border-[var(--token-border)] bg-[var(--token-surface-subtle)] py-12 text-center">
                <span class="text-3xl">📅</span>
                <p class="mt-2 text-sm font-semibold token-muted">No payment plans defined yet.</p>
            </div>
        @else
            <div class="token-table-wrap">
                <table class="token-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>#</th>
                            <th>Installment</th>
                            <th>Percentage</th>
                            <th>Trigger</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($paymentPlans as $plan)
                            <tr class="transition">
                                <td class="font-bold">{{ $plan->project->name ?? '—' }}</td>
                                <td class="token-muted">{{ $plan->order_index }}</td>
                                <td class="font-bold">{{ $plan->installment_name }}</td>
                                <td class="font-bold">{{ rtrim(rtrim(number_format((float) $plan->percentage, 2), '0'), '.') }}%</td>
                                <td class="token-muted">{{ $plan->milestone_trigger ?? '—' }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('developer.payment-plans.destroy', $plan) }}" onsubmit="return confirm('Remove installment?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="token-btn token-btn-ghost !text-[var(--token-danger)]">Delete</button>
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
    <section id="buyers" data-section="buyers" class="section-panel hidden space-y-6 token-card">
        <h2 class="token-section-title !border-b-0 !pb-0">Buyers</h2>
        <p class="text-xs token-muted">Registered off-plan buyers across all unit reservations.</p>

        @if ($reservations->isEmpty())
            <div class="rounded-2xl border border-dashed border-[var(--token-border)] bg-[var(--token-surface-subtle)] py-12 text-center">
                <span class="text-3xl">🧑‍🤝‍🧑</span>
                <p class="mt-2 text-sm font-semibold token-muted">No buyers registered yet.</p>
            </div>
        @else
            @php $buyers = $reservations->groupBy('buyer_email'); @endphp
            <div class="token-table-wrap">
                <table class="token-table">
                    <thead>
                        <tr>
                            <th>Buyer</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Reservations</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($buyers as $buyerReservations)
                            <tr class="transition">
                                <td class="font-bold">{{ $buyerReservations->first()->buyer_name }}</td>
                                <td class="token-muted">{{ $buyerReservations->first()->buyer_email }}</td>
                                <td class="token-muted">{{ $buyerReservations->first()->buyer_phone }}</td>
                                <td class="font-bold">{{ $buyerReservations->count() }}</td>
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
    <section id="reservations" data-section="reservations" class="section-panel hidden space-y-6 token-card">
        <h2 class="token-section-title !border-b-0 !pb-0">Reservations</h2>
        <p class="text-xs token-muted">Unit reservations and buyer inquiries progression.</p>

        @if ($reservations->isEmpty())
            <div class="rounded-2xl border border-dashed border-[var(--token-border)] bg-[var(--token-surface-subtle)] py-12 text-center">
                <span class="text-3xl">📝</span>
                <p class="mt-2 text-sm font-semibold token-muted">No reservations yet.</p>
            </div>
        @else
            <div class="token-table-wrap">
                <table class="token-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Project</th>
                            <th>Unit</th>
                            <th>Buyer</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-right">Transition Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reservations as $reservation)
                            <tr class="transition">
                                <td class="token-muted font-mono text-[11px] font-bold">{{ $reservation->reservation_reference }}</td>
                                <td class="token-muted">{{ $reservation->project->name ?? '—' }}</td>
                                <td class="font-bold">{{ $reservation->unit->unit_number ?? '—' }}</td>
                                <td>
                                    <span class="font-bold">{{ $reservation->buyer_name }}</span>
                                    <span class="block text-[10px] token-muted">{{ $reservation->buyer_email }}</span>
                                </td>
                                <td>
                                    @php
                                        $reservationStyles = [
                                            'pending' => 'token-badge-warning',
                                            'reserved' => 'token-badge-success',
                                            'confirmed' => 'token-badge-success',
                                            'contract_signed' => 'token-badge-info',
                                            'contracted' => 'token-badge-warning',
                                            'sold' => 'token-badge-accent',
                                            'cancelled' => 'token-badge-neutral',
                                        ];
                                    @endphp
                                    <span class="token-badge {{ $reservationStyles[$reservation->status] ?? 'token-badge-neutral' }}">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span>
                                </td>
                                <td class="token-muted">{{ $reservation->created_at->format('M d, Y') }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('developer.reservations.status', $reservation) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" onchange="this.form.submit()" class="token-field !py-0.5 !px-2 !text-[10px] font-bold">
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
