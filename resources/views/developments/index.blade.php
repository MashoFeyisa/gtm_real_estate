@extends('app')

@section('title', 'Ethiopian Real Estate Developments & Off-Plan Projects — GTM Real Estate')
@section('description', 'Explore master-planned real estate developments, off-plan residential towers, and developer unit inventories across Addis Ababa and Ethiopia.')

@section('content')
    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        {{-- Header Section --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#587165]">Real Estate Developments & Off-Plan</p>
                </div>
                <h1 class="mt-2 text-3xl sm:text-4xl font-black tracking-tight text-[#1d3c34]">
                    Master Projects & Developer Units
                </h1>
                <p class="mt-1.5 text-sm text-slate-600 max-w-2xl">
                    Discover off-plan residential towers, luxury mixed-use complexes, verified construction milestones, milestone-based developer payment plans, and direct unit inventory.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('properties.index') }}" class="inline-flex items-center rounded-full border border-[#d9cab3] bg-[#fffaf2] px-4 py-2 text-xs font-bold text-[#1d3c34] transition hover:bg-[#f1e6d2]">
                    Individual Properties
                </a>
                <a href="{{ route('stays.index') }}" class="inline-flex items-center rounded-full border border-[#d9cab3] bg-[#fffaf2] px-4 py-2 text-xs font-bold text-[#1d3c34] transition hover:bg-[#f1e6d2]">
                    Short-Stays
                </a>
            </div>
        </div>

        {{-- Filter & Search Toolbar --}}
        <div class="mt-8 rounded-2xl border border-[#d9cab3] bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('developments.index') }}" class="grid gap-3 sm:grid-cols-2 md:grid-cols-5 items-end">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#587165] mb-1.5">Location / Subcity</label>
                    <input type="text" name="city" value="{{ request('city') }}" placeholder="e.g. Bole, Kirkos, Kazanchis" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2.5 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#587165] mb-1.5">Project Type</label>
                    <select name="project_type" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="">All Project Types</option>
                        <option value="apartments" {{ request('project_type') == 'apartments' ? 'selected' : '' }}>Apartments & Residences</option>
                        <option value="mixed_use" {{ request('project_type') == 'mixed_use' ? 'selected' : '' }}>Mixed-Use Complex</option>
                        <option value="luxury_villas" {{ request('project_type') == 'luxury_villas' ? 'selected' : '' }}>Gated Villas</option>
                        <option value="commercial" {{ request('project_type') == 'commercial' ? 'selected' : '' }}>Commercial / Offices</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#587165] mb-1.5">Construction Status</label>
                    <select name="construction_status" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="">Any Status</option>
                        <option value="under_construction" {{ request('construction_status') == 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                        <option value="near_completion" {{ request('construction_status') == 'near_completion' ? 'selected' : '' }}>Near Completion</option>
                        <option value="pre_construction" {{ request('construction_status') == 'pre_construction' ? 'selected' : '' }}>Pre-Construction / Planning</option>
                        <option value="completed" {{ request('construction_status') == 'completed' ? 'selected' : '' }}>Completed / Handover</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#587165] mb-1.5">Min. Progress (%)</label>
                    <select name="min_completion" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="">Any Progress</option>
                        <option value="25" {{ request('min_completion') == '25' ? 'selected' : '' }}>25%+ Completed</option>
                        <option value="50" {{ request('min_completion') == '50' ? 'selected' : '' }}>50%+ Completed</option>
                        <option value="75" {{ request('min_completion') == '75' ? 'selected' : '' }}>75%+ Completed</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="w-full rounded-xl bg-[#1d3c34] py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                        Search Projects
                    </button>
                    @if (request()->hasAny(['city', 'project_type', 'construction_status', 'min_completion']))
                        <a href="{{ route('developments.index') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-50">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Projects Grid --}}
        <div class="mt-8 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($projects as $project)
                <article class="group flex flex-col overflow-hidden rounded-[1.75rem] border border-[#d9cab3] bg-white shadow-sm transition hover:shadow-xl hover:border-[#1d3c34]/40">
                    <div class="relative h-64 w-full overflow-hidden bg-slate-100">
                        <img src="{{ $project->primary_image_url }}" alt="{{ $project->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-transparent"></div>

                        {{-- Badges --}}
                        <div class="absolute top-3 left-3 right-3 flex items-center justify-between">
                            <span class="rounded-lg bg-[#102b25]/90 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-[#d4af37] backdrop-blur-xs">
                                {{ str_replace('_', ' ', $project->project_type) }}
                            </span>
                            <span class="rounded-lg bg-white/95 px-2.5 py-1 text-[11px] font-black uppercase text-[#1d3c34] shadow-xs">
                                {{ $project->project_code }}
                            </span>
                        </div>

                        {{-- Handover & Progress Badge --}}
                        <div class="absolute bottom-3 left-3 right-3">
                            <div class="flex items-center justify-between text-white text-xs font-bold mb-1.5">
                                <span>Progress: {{ $project->overall_completion_percentage }}%</span>
                                <span class="text-white/90">Handover: {{ $project->expected_completion_date ?? 'TBD' }}</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-white/30 backdrop-blur-xs">
                                <div class="h-full bg-gradient-to-r from-[#d4af37] to-[#88d49e] transition-all duration-500" style="width: {{ min(100, max(5, $project->overall_completion_percentage)) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col p-6">
                        {{-- Developer & Title --}}
                        <p class="text-xs font-bold uppercase tracking-wider text-[#587165]">
                            🏗️ {{ $project->developer_name }}
                        </p>
                        <h2 class="mt-1 text-xl font-black text-[#1d3c34] group-hover:text-[#254d43] transition">
                            <a href="{{ route('developments.show', $project->slug) }}">
                                {{ $project->name }}
                            </a>
                        </h2>

                        <p class="mt-2 text-xs text-slate-500 flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-[#587165] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <span>{{ $project->address ?? $project->city }}</span>
                        </p>

                        {{-- Key Stats Grid --}}
                        <div class="mt-4 grid grid-cols-3 gap-2 rounded-xl bg-[#fffdfa] border border-[#f0e4cf] p-3 text-center">
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Towers</span>
                                <span class="text-sm font-black text-[#1d3c34]">{{ $project->total_buildings_count }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Available</span>
                                <span class="text-sm font-black text-[#0f5132]">{{ $project->available_units_count }} Units</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Units</span>
                                <span class="text-sm font-black text-[#1d3c34]">{{ $project->total_units_count }}</span>
                            </div>
                        </div>

                        {{-- Facilities Preview --}}
                        @if ($project->facilities && count($project->facilities) > 0)
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @foreach (array_slice($project->facilities, 0, 3) as $fac)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-[#eef7f2] px-2 py-0.5 text-[10px] font-bold text-[#1d3c34]">
                                        <span class="text-[#0f5132]">✓</span> {{ $fac }}
                                    </span>
                                @endforeach
                                @if (count($project->facilities) > 3)
                                    <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500">
                                        +{{ count($project->facilities) - 3 }} more
                                    </span>
                                @endif
                            </div>
                        @endif

                        {{-- Price and CTA Footer --}}
                        <div class="mt-auto pt-5 border-t border-[#f0e4cf] flex items-center justify-between gap-2">
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Starting From</span>
                                <span class="text-base font-black text-[#1d3c34] currency-price" data-base-price="{{ $project->starting_price }}">
                                    {{ $project->formatted_starting_price }}
                                </span>
                            </div>

                            <a href="{{ route('developments.show', $project->slug) }}" class="inline-flex items-center gap-1 rounded-xl bg-[#1d3c34] px-4 py-2.5 text-xs font-bold text-white shadow-xs transition hover:bg-[#254d43]">
                                <span>View Units</span>
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-[#d9cab3] bg-white p-12 text-center">
                    <span class="text-4xl">🏗️</span>
                    <h3 class="mt-2 text-lg font-bold text-[#1d3c34]">No Developments Matching Criteria</h3>
                    <p class="mt-1 text-xs text-slate-500">Try adjusting your filters or search keywords.</p>
                    <a href="{{ route('developments.index') }}" class="mt-4 inline-flex items-center rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white">
                        Reset Filters
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($projects->hasPages())
            <div class="mt-10">
                {{ $projects->links() }}
            </div>
        @endif
    </section>
@endsection
