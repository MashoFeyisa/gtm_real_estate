<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Developer Portal | {{ config('app.name', 'GTM Real Estate') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-styles')
</head>
<body class="bg-[#f5f0e7] text-slate-800 antialiased scroll-smooth">
    @include('layouts.partials.impersonation-banner')
    <div class="flex min-h-screen">
        <aside id="developer-sidebar" class="dashboard-sidebar sidebar-scroll-area w-72 shrink-0 border-r border-[#d9cab3] bg-[#1d3c34] p-6 text-[#f8f3eb] hidden lg:flex flex-col justify-between overflow-y-auto" style="position: sticky; top: 0; height: 100dvh; overflow-y: auto;">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ $siteBrand['logoUrl'] }}" alt="{{ $siteBrand['name'] }} Logo" class="h-10 w-10 rounded-xl object-contain ring-2 ring-[#d9cab3] bg-white/10">
                    <span class="text-xl font-black tracking-tight text-white">{{ $siteBrand['name'] }}</span>
                </a>

                {{-- Sidebar Skeleton placeholder for layout height stability while permissions/badges evaluate --}}
                <div class="sidebar-skeleton hidden pointer-events-none opacity-0 h-0 overflow-hidden" aria-hidden="true">
                    <div class="h-9 w-full rounded-xl bg-white/5 animate-pulse mb-2"></div>
                    <div class="h-9 w-full rounded-xl bg-white/5 animate-pulse mb-2"></div>
                </div>

                <div class="mt-5 rounded-xl border border-white/10 bg-white/5 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Active Desk</p>
                    <p class="text-sm font-black text-white truncate">Developer Portal</p>
                    <p class="text-[11px] text-[#dfeee4]/80">👤 {{ auth()->user()->name }}</p>
                </div>

                <nav class="mt-6 space-y-1.5 min-h-[420px]" aria-label="Developer portal navigation">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span>🏠</span>
                        <span>Home</span>
                    </a>
                    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center justify-between rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
                        <span class="flex items-center gap-2.5">
                            <span>📊</span>
                            <span>Overview</span>
                        </span>
                    </a>
                    @if ($canManageProjects)
                    <a href="#projects" data-target-section="projects" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>🏗️</span>
                            <span>Projects</span>
                        </span>
                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">{{ $stats['projects'] }}</span>
                    </a>
                    @endif
                    @if ($canManageBuildings)
                    <a href="#buildings" data-target-section="buildings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>🏢</span>
                            <span>Buildings</span>
                        </span>
                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">{{ $stats['buildings'] }}</span>
                    </a>
                    @endif
                    @if ($canManageUnits)
                    <a href="#units" data-target-section="units" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>🚪</span>
                            <span>Units</span>
                        </span>
                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">{{ $stats['units'] }}</span>
                    </a>
                    @endif
                    @if ($canManageConstruction)
                    <a href="#construction" data-target-section="construction" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>🚧</span>
                            <span>Construction Progress</span>
                        </span>
                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">{{ $stats['avg_completion'] }}%</span>
                    </a>
                    @endif
                    @if ($canManagePricing)
                    <a href="#pricing" data-target-section="pricing" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>💰</span>
                            <span>Pricing</span>
                        </span>
                    </a>
                    @endif
                    @if ($canManagePaymentPlans)
                    <a href="#payment-plans" data-target-section="payment-plans" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>📅</span>
                            <span>Payment Plans</span>
                        </span>
                    </a>
                    @endif
                    @if ($canManageBuyers)
                    <a href="#buyers" data-target-section="buyers" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>🧑‍🤝‍🧑</span>
                            <span>Buyers</span>
                        </span>
                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">{{ $stats['buyers'] }}</span>
                    </a>
                    @endif
                    @if ($canManageReservations)
                    <a href="#reservations" data-target-section="reservations" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span class="flex items-center gap-2.5">
                            <span>📝</span>
                            <span>Reservations</span>
                        </span>
                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white">{{ $stats['reservations'] }}</span>
                    </a>
                    @endif

                    @include('layouts.partials.authorized-sidebar-links')
                </nav>
            </div>

            @include('dashboard.partials.signed-in-user', ['roleLabel' => 'Developer'])
        </aside>

        <main class="flex-1 px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
            @if (session('success'))
                <div class="mb-6 flex items-center gap-2.5 rounded-2xl border border-[#d7e5d2] bg-[#edf9ee] px-4 py-3.5 text-sm font-medium text-[#214f3a] shadow-xs">
                    <span class="text-base">✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3.5 text-sm font-medium text-rose-800 shadow-xs">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Overview --}}
            <section id="overview" data-section="overview" class="section-panel scroll-mt-24">
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-gradient-to-br from-[#1d3c34] to-[#254d43] p-6 text-white shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-[#d4af37]">Developer Dashboard</p>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-white">Welcome back, {{ auth()->user()->name }}.</h1>
                    <p class="mt-2 max-w-2xl text-sm text-[#dfeee4]">Projects, buildings, units, construction progress, pricing, payment plans, buyers, and reservations &mdash; everything your developer role manages, in one desk.</p>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-2xl border border-[#d9cab3] bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between text-[#587165]">
                            <p class="text-sm font-medium">Avg. Completion</p>
                            <span class="text-xl">🚧</span>
                        </div>
                        <p class="mt-3 text-2xl font-black text-[#1d3c34]">{{ $stats['avg_completion'] }}%</p>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-[#e7ddca]">
                            <div class="h-full rounded-full bg-[#1d3c34]" style="width: {{ $stats['avg_completion'] }}%"></div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-[#d9cab3] bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between text-[#587165]">
                            <p class="text-sm font-medium">Available Units</p>
                            <span class="text-xl">🚪</span>
                        </div>
                        <p class="mt-3 text-2xl font-black text-emerald-700">{{ $stats['available_units'] }}</p>
                        <p class="mt-1 text-[11px] text-slate-500">Ready to sell or reserve</p>
                    </div>
                    <div class="rounded-2xl border border-[#d9cab3] bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between text-[#587165]">
                            <p class="text-sm font-medium">Registered Buyers</p>
                            <span class="text-xl">🧑‍🤝‍🧑</span>
                        </div>
                        <p class="mt-3 text-2xl font-black text-[#1d3c34]">{{ $stats['buyers'] }}</p>
                        <p class="mt-1 text-[11px] text-slate-500">Across all reservations</p>
                    </div>
                </div>
            </section>

            {{-- Projects --}}
            @if ($canManageProjects)
            <section id="projects" data-section="projects" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-[#1d3c34]">Projects</h2>
                <p class="text-sm text-[#587165]">Every development project you manage.</p>

                @if ($projects->isEmpty())
                    <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                        <span class="text-3xl">🏗️</span>
                        <p class="mt-2 text-sm font-semibold text-[#587165]">No projects yet.</p>
                    </div>
                @else
                    <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
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
            <section id="buildings" data-section="buildings" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-[#1d3c34]">Buildings</h2>
                <p class="text-sm text-[#587165]">Building inventory across all your projects.</p>

                @if ($buildings->isEmpty())
                    <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                        <span class="text-3xl">🏢</span>
                        <p class="mt-2 text-sm font-semibold text-[#587165]">No buildings registered yet.</p>
                    </div>
                @else
                    <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                                <tr>
                                    <th class="p-3">Building</th>
                                    <th class="p-3">Project</th>
                                    <th class="p-3">Floors</th>
                                    <th class="p-3">Units</th>
                                    <th class="p-3">Description</th>
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
            <section id="units" data-section="units" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-[#1d3c34]">Units</h2>
                <p class="text-sm text-[#587165]">Unit inventory with availability status.</p>

                @if ($units->isEmpty())
                    <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                        <span class="text-3xl">🚪</span>
                        <p class="mt-2 text-sm font-semibold text-[#587165]">No units registered yet.</p>
                    </div>
                @else
                    <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
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
                                                    'blocked' => 'bg-slate-100 text-slate-600',
                                                ];
                                            @endphp
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $statusStyles[$unit->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($unit->status) }}</span>
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
            <section id="construction" data-section="construction" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-[#1d3c34]">Construction Progress</h2>
                <p class="text-sm text-[#587165]">Milestone-by-milestone progress across projects.</p>

                @if ($milestones->isEmpty())
                    <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                        <span class="text-3xl">🚧</span>
                        <p class="mt-2 text-sm font-semibold text-[#587165]">No construction milestones recorded yet.</p>
                    </div>
                @else
                    @foreach ($projects->groupBy('id') as $projectGroup)
                        @php $projectMilestones = $milestones->where('project_id', $projectGroup->first()->id); @endphp
                        @if ($projectMilestones->isNotEmpty())
                            <div class="mt-6">
                                <h3 class="text-sm font-black text-[#1d3c34]">{{ $projectMilestones->first()->project->name ?? 'Project' }}</h3>
                                <div class="mt-3 space-y-3">
                                    @foreach ($projectMilestones as $milestone)
                                        <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf8f3] p-4">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-bold text-[#1d3c34]">{{ $milestone->milestone_name }}</p>
                                                @php
                                                    $milestoneStyles = [
                                                        'pending' => 'bg-slate-100 text-slate-600',
                                                        'in_progress' => 'bg-amber-100 text-amber-800',
                                                        'completed' => 'bg-emerald-100 text-emerald-800',
                                                    ];
                                                @endphp
                                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $milestoneStyles[$milestone->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucwords(str_replace('_', ' ', $milestone->status)) }}</span>
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
            <section id="pricing" data-section="pricing" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-[#1d3c34]">Pricing</h2>
                <p class="text-sm text-[#587165]">Unit pricing ranges per project.</p>

                @if ($pricingRows->isEmpty())
                    <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                        <span class="text-3xl">💰</span>
                        <p class="mt-2 text-sm font-semibold text-[#587165]">No pricing data yet.</p>
                    </div>
                @else
                    <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
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
            <section id="payment-plans" data-section="payment-plans" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-[#1d3c34]">Payment Plans</h2>
                <p class="text-sm text-[#587165]">Installment schedules per project.</p>

                @if ($paymentPlans->isEmpty())
                    <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                        <span class="text-3xl">📅</span>
                        <p class="mt-2 text-sm font-semibold text-[#587165]">No payment plans defined yet.</p>
                    </div>
                @else
                    <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                                <tr>
                                    <th class="p-3">Project</th>
                                    <th class="p-3">#</th>
                                    <th class="p-3">Installment</th>
                                    <th class="p-3">Percentage</th>
                                    <th class="p-3">Trigger</th>
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
            <section id="buyers" data-section="buyers" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-[#1d3c34]">Buyers</h2>
                <p class="text-sm text-[#587165]">Registered buyers across all reservations.</p>

                @if ($reservations->isEmpty())
                    <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                        <span class="text-3xl">🧑‍🤝‍🧑</span>
                        <p class="mt-2 text-sm font-semibold text-[#587165]">No buyers registered yet.</p>
                    </div>
                @else
                    @php $buyers = $reservations->groupBy('buyer_email'); @endphp
                    <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
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
            <section id="reservations" data-section="reservations" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-[#1d3c34]">Reservations</h2>
                <p class="text-sm text-[#587165]">Unit reservations and buyer inquiries.</p>

                @if ($reservations->isEmpty())
                    <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                        <span class="text-3xl">📝</span>
                        <p class="mt-2 text-sm font-semibold text-[#587165]">No reservations yet.</p>
                    </div>
                @else
                    <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                                <tr>
                                    <th class="p-3">Reference</th>
                                    <th class="p-3">Project</th>
                                    <th class="p-3">Unit</th>
                                    <th class="p-3">Buyer</th>
                                    <th class="p-3">Status</th>
                                    <th class="p-3">Date</th>
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
                                                    'confirmed' => 'bg-emerald-100 text-emerald-800',
                                                    'contracted' => 'bg-[#f9ecd0] text-[#9b6c17]',
                                                    'cancelled' => 'bg-slate-100 text-slate-600',
                                                ];
                                            @endphp
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $reservationStyles[$reservation->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($reservation->status) }}</span>
                                        </td>
                                        <td class="p-3 text-[#587165]">{{ $reservation->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
            @endif
        </main>
    </div>

    <script>
        const navLinks = document.querySelectorAll('.nav-section-link');
        const panels = document.querySelectorAll('.section-panel');

        function activateSection(sectionName) {
            panels.forEach(panel => {
                panel.classList.toggle('hidden', panel.dataset.section !== sectionName);
            });

            navLinks.forEach(link => {
                const active = link.dataset.targetSection === sectionName;
                link.classList.toggle('bg-white/10', active);
                link.classList.toggle('text-white', active);
                link.classList.toggle('text-[#dfeee4]', !active);
            });
        }

        navLinks.forEach(link => {
            link.addEventListener('click', function (event) {
                const target = this.dataset.targetSection;
                if (!target) {
                    return;
                }
                event.preventDefault();
                activateSection(target);
                history.replaceState(null, '', '#' + target);
            });
        });

        const initialHash = window.location.hash.replace('#', '');
        if (initialHash && document.querySelector('[data-section="' + initialHash + '"]')) {
            activateSection(initialHash);
        }
    </script>
    @include('layouts.partials.sidebar-scroll-preserver')
</body>
</html>
