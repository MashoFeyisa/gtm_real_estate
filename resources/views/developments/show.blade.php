@extends('app')

@section('title', $project->name . ' — Developer Project & Unit Inventory')
@section('description', $project->description ? \Illuminate\Support\Str::limit($project->description, 160) : 'Explore unit availability and construction progress for ' . $project->name)

@section('content')
    <div class="bg-[#fcfbf9] pb-16">
        {{-- Success Reservation Alert Banner --}}
        @if (session('developer_reservation_success'))
            @php $res = session('developer_reservation_success'); @endphp
            <div class="mx-auto max-w-7xl px-6 pt-6 lg:px-8">
                <div class="rounded-2xl border-2 border-[#0f5132] bg-[#eef7f2] p-6 shadow-md animate-in fade-in duration-300">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="text-3xl">🎉</span>
                            <div>
                                <h3 class="text-base font-black text-[#0f5132]">Unit Reservation Requested Successfully!</h3>
                                <p class="mt-1 text-xs text-slate-700">
                                    Official reservation reference: <strong class="font-mono text-sm text-[#1d3c34]">{{ $res['reference'] }}</strong> for <strong class="text-[#1d3c34]">Unit {{ $res['unit'] }}</strong> at {{ $res['project'] }}.
                                </p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    The developer's sales desk ({{ $res['developer'] }}) has been notified and will contact <strong class="text-slate-800">{{ $res['buyer_name'] }}</strong> with contract documents.
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center rounded-xl bg-[#0f5132] px-3.5 py-2 text-xs font-bold text-white shadow-xs">
                            Status: Pending Review
                        </span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Hero Showcase Header --}}
        <section class="border-b border-[#d9cab3]/60 bg-white">
            <div class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
                {{-- Breadcrumbs & Badges --}}
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-xs text-[#587165]">
                        <a href="{{ route('developments.index') }}" class="hover:underline font-bold">Developments</a>
                        <span>/</span>
                        <span>{{ $project->city }}</span>
                        <span>/</span>
                        <span class="text-slate-800 font-bold">{{ $project->name }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="rounded-lg bg-[#f0e4cf] px-3 py-1 text-xs font-black uppercase text-[#1d3c34]">
                            {{ $project->project_code }}
                        </span>
                        @if (auth()->check() && (auth()->user()->isAdmin() || (auth()->user()->agentProfile && auth()->user()->agentProfile->id === $project->agent_id)))
                            <button type="button" onclick="openEditProjectModal({{ json_encode($project) }})" class="inline-flex items-center gap-1.5 rounded-lg border border-[#d9cab3] bg-[#fffaf0] px-3 py-1 text-xs font-bold text-[#9b6c17] hover:bg-[#f5ecdd] transition cursor-pointer">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit Development</span>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Title & Developer Credentials --}}
                <div class="mt-5 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#587165]">
                                🏗️ Developer: <strong class="text-[#1d3c34]">{{ $project->developer_name }}</strong>
                            </span>
                            @if ($project->developer_phone)
                                <span class="text-xs text-slate-400">· {{ $project->developer_phone }}</span>
                            @endif
                        </div>
                        <h1 class="mt-1 text-3xl sm:text-4xl font-black text-[#1d3c34]">
                            {{ $project->name }}
                        </h1>
                        <p class="mt-2 text-xs text-slate-600 flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-[#587165]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <span>{{ $project->address ?? $project->city }}</span>
                        </p>
                    </div>

                    {{-- Starting Price Card --}}
                    <div class="rounded-2xl border border-[#d9cab3] bg-[#fffdfa] p-4 text-left sm:text-right">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-[#587165]">Starting From</span>
                        <span class="text-2xl font-black text-[#1d3c34] currency-price" data-base-price="{{ $project->starting_price }}">
                            {{ $project->formatted_starting_price }}
                        </span>
                        <span class="block text-[11px] font-semibold text-slate-500">Flexible Developer Installments Available</span>
                    </div>
                </div>

                {{-- Hero Gallery Grid --}}
                <div class="mt-8 grid gap-4 md:grid-cols-3">
                    <div class="relative overflow-hidden rounded-2xl md:col-span-2 h-72 sm:h-96 bg-slate-100">
                        <img src="{{ $project->primary_image_url }}" alt="{{ $project->name }}" class="h-full w-full object-cover">
                    </div>
                    <div class="grid gap-4 grid-cols-2 md:grid-cols-1">
                        @if ($project->gallery && count($project->gallery) > 1)
                            @foreach (array_slice($project->gallery, 1, 2) as $imgUrl)
                                <div class="overflow-hidden rounded-2xl h-36 sm:h-44 bg-slate-100">
                                    <img src="{{ $imgUrl }}" alt="{{ $project->name }}" class="h-full w-full object-cover">
                                </div>
                            @endforeach
                        @else
                            <div class="overflow-hidden rounded-2xl h-36 sm:h-44 bg-slate-100">
                                <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80" alt="{{ $project->name }}" class="h-full w-full object-cover">
                            </div>
                            <div class="overflow-hidden rounded-2xl h-36 sm:h-44 bg-slate-100">
                                <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80" alt="{{ $project->name }}" class="h-full w-full object-cover">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- Main Content Container --}}
        <div class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-3">
                {{-- Left 2 Columns: Description, Construction Progress, Payment Plan, Unit Inventory --}}
                <div class="space-y-10 lg:col-span-2">

                    {{-- 1. Project Overview & Facilities --}}
                    <section class="rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-xs">
                        <h2 class="text-xl font-black text-[#1d3c34]">About the Development</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-700 whitespace-pre-line">
                            {{ $project->description }}
                        </p>

                        {{-- Verified Facilities / Amenities --}}
                        @if ($project->facilities && count($project->facilities) > 0)
                            <div class="mt-8 border-t border-[#f0e4cf] pt-6">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-[#587165] mb-4">
                                    Infrastructure, Utilities & Amenities
                                </h3>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach ($project->facilities as $facility)
                                        <div class="flex items-center gap-2.5 rounded-xl border border-[#d9cab3]/60 bg-[#fffdfa] px-3.5 py-2.5 text-xs font-semibold text-slate-800">
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#102b25] text-[11px] font-bold text-[#d4af37]">✓</span>
                                            <span>{{ $facility }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </section>

                    {{-- 2. Construction Progress & Milestones --}}
                    <section id="construction-progress" class="rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#f0e4cf] pb-5">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-[#102b25] px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]"></span>
                                    <h2 class="text-xl font-black text-[#1d3c34]">Construction Progress &amp; Milestones</h2>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Live physical execution milestones and engineer verification status</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Overall Progress</span>
                                    <span class="text-2xl font-black text-[#1d3c34]">{{ $project->overall_completion_percentage }}%</span>
                                </div>
                            </div>
                        </div>

                        {{-- Overall Progress Bar --}}
                        <div class="mt-6">
                            <div class="flex justify-between text-xs font-bold text-[#1d3c34] mb-2">
                                <span>Master Project Completion</span>
                                <span>{{ $project->overall_completion_percentage }}%</span>
                            </div>
                            <div class="h-3.5 w-full overflow-hidden rounded-full bg-[#f0e4cf]">
                                <div class="h-full bg-gradient-to-r from-[#d4af37] via-[#254d43] to-[#0f5132] transition-all duration-700" style="width: {{ min(100, max(5, $project->overall_completion_percentage)) }}%"></div>
                            </div>
                        </div>

                        {{-- Milestone Stage Breakdown --}}
                        <div class="mt-6 space-y-4">
                            @forelse ($project->milestones as $milestone)
                                <div class="rounded-2xl border border-[#f0e4cf] bg-[#fffdfa] p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#1d3c34] text-[11px] font-bold text-white">
                                                {{ $milestone->order_index }}
                                            </span>
                                            <span class="text-sm font-bold text-[#1d3c34]">{{ $milestone->milestone_name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if ($milestone->percentage >= 100)
                                                <span class="rounded-full bg-[#eef7f2] px-2.5 py-0.5 text-[11px] font-bold text-[#0f5132]">Completed</span>
                                            @elseif ($milestone->percentage > 0)
                                                <span class="rounded-full bg-[#fdf8e6] px-2.5 py-0.5 text-[11px] font-bold text-[#9b6c17]">In Progress</span>
                                            @else
                                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-500">Upcoming</span>
                                            @endif
                                            <span class="text-xs font-black text-[#1d3c34]">{{ $milestone->percentage }}%</span>
                                        </div>
                                    </div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                                        <div class="h-full {{ $milestone->percentage >= 100 ? 'bg-[#0f5132]' : 'bg-[#d4af37]' }}" style="width: {{ $milestone->percentage }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-[#d9cab3] p-6 text-center text-xs text-slate-500">
                                    Milestones being finalized by the engineering team.
                                </div>
                            @endforelse
                        </div>
                    </section>

                    {{-- 3. Developer Milestone Payment Plan --}}
                    <section id="payment-plans" class="rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-xs">
                        <div class="flex items-center gap-2 border-b border-[#f0e4cf] pb-4">
                            <span class="rounded bg-[#102b25] px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]"></span>
                            <h2 class="text-xl font-black text-[#1d3c34]">Developer Milestone Payment Schedule</h2>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">
                            Installments tied directly to verified civil construction stages for maximum buyer financial protection.
                        </p>

                        <div class="mt-5 overflow-hidden rounded-2xl border border-[#d9cab3]/70">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#102b25] text-white">
                                    <tr>
                                        <th class="px-4 py-3 font-bold uppercase tracking-wider">Installment Stage</th>
                                        <th class="px-4 py-3 font-bold uppercase tracking-wider text-right">% Ratio</th>
                                        <th class="px-4 py-3 font-bold uppercase tracking-wider">Milestone Trigger</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#f0e4cf] bg-white text-slate-700">
                                    @forelse ($project->paymentPlans as $plan)
                                        <tr class="hover:bg-[#fffdfa]">
                                            <td class="px-4 py-3 font-bold text-[#1d3c34]">
                                                {{ $plan->installment_name }}
                                            </td>
                                            <td class="px-4 py-3 font-black text-[#9b6c17] text-right text-sm">
                                                {{ (float) $plan->percentage }}%
                                            </td>
                                            <td class="px-4 py-3 text-slate-600">
                                                {{ $plan->milestone_trigger ?? 'Standard developer schedule' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-6 text-center text-slate-400">
                                                Standard 10% Reservation, 80% Construction Milestones, 10% Handover.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    {{-- 4. Interactive Unit Inventory Matrix --}}
                    <section id="units-inventory" class="rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#f0e4cf] pb-5">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-[#102b25] px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]"></span>
                                    <h2 class="text-xl font-black text-[#1d3c34]">Unit Inventory &amp; Availability Matrix</h2>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Live master unit inventory with floor levels, orientations, and instant reservation</p>
                            </div>

                            <div class="flex items-center gap-2 text-xs">
                                <span class="inline-flex items-center gap-1 rounded-full bg-[#eef7f2] px-3 py-1 font-bold text-[#0f5132]">
                                    <span class="h-2 w-2 rounded-full bg-[#0f5132]"></span>
                                    {{ $availableUnitsCount }} Available
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-[#fdf8e6] px-3 py-1 font-bold text-[#9b6c17]">
                                    <span class="h-2 w-2 rounded-full bg-[#9b6c17]"></span>
                                    {{ $reservedUnitsCount }} Reserved
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 font-bold text-slate-500">
                                    <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                                    {{ $soldUnitsCount }} Sold
                                </span>
                            </div>
                        </div>

                        {{-- Units List Grouped by Building --}}
                        <div class="mt-6 space-y-8">
                            @forelse ($unitsGroupedByBuilding as $buildingName => $buildingUnits)
                                <div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="text-sm">🏢</span>
                                        <h3 class="text-sm font-black uppercase tracking-wider text-[#1d3c34]">{{ $buildingName }}</h3>
                                        <span class="rounded-full bg-[#f0e4cf] px-2 py-0.2 text-[10px] font-bold text-[#1d3c34]">
                                            {{ $buildingUnits->count() }} Units
                                        </span>
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        @foreach ($buildingUnits as $unit)
                                            <div class="relative flex flex-col justify-between rounded-2xl border border-[#d9cab3]/80 bg-[#fffdfa] p-4 transition hover:border-[#1d3c34]/50 hover:shadow-md">
                                                <div>
                                                    <div class="flex items-start justify-between gap-2">
                                                        <div>
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="text-base font-black text-[#1d3c34]">Unit {{ $unit->unit_number }}</span>
                                                                <span class="text-xs text-slate-500">· Floor {{ $unit->floor_number }}</span>
                                                            </div>
                                                            <p class="text-xs font-semibold text-[#587165]">
                                                                {{ $unit->bedrooms }} Bed · {{ $unit->bathrooms }} Bath · {{ (float) $unit->area_sqm }} sqm
                                                            </p>
                                                        </div>

                                                        {{-- Unit Status Badge --}}
                                                        @if ($unit->status === 'available')
                                                            <span class="rounded-full bg-[#eef7f2] px-2.5 py-1 text-[11px] font-black text-[#0f5132]">
                                                                Available
                                                            </span>
                                                        @elseif ($unit->status === 'reserved')
                                                            <span class="rounded-full bg-[#fdf8e6] px-2.5 py-1 text-[11px] font-black text-[#9b6c17]">
                                                                Reserved
                                                            </span>
                                                        @elseif ($unit->status === 'sold')
                                                            <span class="rounded-full bg-slate-200 px-2.5 py-1 text-[11px] font-bold text-slate-600">
                                                                Sold
                                                            </span>
                                                        @else
                                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500">
                                                                {{ ucfirst($unit->status) }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if ($unit->orientation)
                                                        <p class="mt-2 text-[11px] text-slate-500 flex items-center gap-1">
                                                            <span>🧭</span> {{ $unit->orientation }}
                                                        </p>
                                                    @endif
                                                </div>

                                                <div class="mt-4 pt-3 border-t border-[#f0e4cf] flex items-end justify-between gap-2">
                                                    <div>
                                                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Price</span>
                                                        <span class="text-sm font-black text-[#1d3c34] currency-price" data-base-price="{{ $unit->price }}">
                                                            {{ $unit->formatted_price }}
                                                        </span>
                                                        <span class="block text-[10px] text-slate-500">
                                                            ~ {{ $unit->formatted_price_per_sqm }}
                                                        </span>
                                                    </div>

                                                    @if ($unit->status === 'available')
                                                        <button type="button" onclick="openReservationModal({{ $unit->id }}, '{{ $unit->unit_number }}', {{ (float) $unit->price }}, '{{ $buildingName }}')" class="rounded-xl bg-[#1d3c34] px-3.5 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-[#254d43] cursor-pointer">
                                                            Reserve Unit
                                                        </button>
                                                    @else
                                                        <button type="button" disabled class="rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400 cursor-not-allowed">
                                                            {{ ucfirst($unit->status) }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-[#d9cab3] p-8 text-center text-xs text-slate-500">
                                    Units currently being uploaded to the inventory matrix.
                                </div>
                            @endforelse
                        </div>
                    </section>

                    {{-- 5. Project Location on the Map --}}
                    <section id="project-location" class="mt-6 rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-xs">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-black text-[#1d3c34]">Project Location &amp; Access</h2>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $project->subcity ? $project->subcity.', ' : '' }}{{ $project->city }}{{ $project->address ? ' · '.$project->address : '' }}
                                </p>
                            </div>
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $project->coordinates[0] }},{{ $project->coordinates[1] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-full border border-[#d9cab3] bg-[#fffaf2] px-4 py-2 text-xs font-bold text-[#1d3c34] transition hover:bg-[#f1e6d2]">
                                Open in Google Maps &rarr;
                            </a>
                        </div>

                        <div class="mt-5 overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white shadow-sm">
                            <div class="flex items-center justify-between border-b border-[#f0e4cf] bg-[#fffaf2] px-5 py-3">
                                <span class="text-xs font-bold text-[#102b25]">Master Site Plan Marker</span>
                                <span class="text-[11px] text-slate-500">Interactive: drag or zoom to inspect the surroundings</span>
                            </div>
                            <div id="project-location-map"
                                data-lat="{{ $project->coordinates[0] }}"
                                data-lng="{{ $project->coordinates[1] }}"
                                data-name="{{ $project->name }}"
                                data-developer="{{ $project->developer_name }}"
                                data-address="{{ $project->subcity ? $project->subcity.', ' : '' }}{{ $project->city }}{{ $project->address ? ' · '.$project->address : '' }}"
                                data-price="{{ $project->starting_price }}"
                                data-units="{{ $project->units->count() }}"
                                data-handover="{{ $project->expected_completion_date ?? 'TBD' }}"
                                data-url="{{ route('developments.show', $project->slug) }}"
                                data-coordinates-source="{{ $project->latitude && $project->longitude ? 'exact' : 'area' }}"
                                class="h-[380px] w-full z-10"
                                style="height: 380px; min-height: 380px; width: 100%; display: block; position: relative;"></div>
                        </div>

                        @unless ($project->latitude && $project->longitude)
                            <p class="mt-3 text-[11px] font-semibold text-[#9b6c17]">
                                This project has no exact map pin yet — the marker shows the {{ $project->subcity ?? $project->city }} area centre.
                            </p>
                        @endunless
                    </section>
                </div>

                {{-- Right 1 Column: Key Metrics & Developer Contact Card --}}
                <div class="space-y-6">
                    <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs sticky top-24">
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-[#102b25] px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Developer CRM</span>
                            <h3 class="text-base font-black text-[#1d3c34]">Sales & Inquiries Desk</h3>
                        </div>

                        <div class="mt-4 space-y-3 text-xs text-slate-600">
                            <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-2">
                                <span class="font-bold">Project Code</span>
                                <span class="font-mono font-bold text-[#1d3c34]">{{ $project->project_code }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-2">
                                <span class="font-bold">Construction Stage</span>
                                <span class="font-bold capitalize text-[#1d3c34]">{{ str_replace('_', ' ', $project->construction_status) }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-2">
                                <span class="font-bold">Target Handover</span>
                                <span class="font-bold text-[#1d3c34]">{{ $project->expected_completion_date ?? 'TBD' }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-2">
                                <span class="font-bold">Land Area</span>
                                <span class="font-bold text-[#1d3c34]">{{ $project->land_area_sqm ? number_format((float) $project->land_area_sqm) . ' sqm' : 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold">Available Inventory</span>
                                <span class="font-bold text-[#0f5132]">{{ $availableUnitsCount }} of {{ $project->units->count() }} Units</span>
                            </div>
                        </div>

                        <div class="mt-6 border-t border-[#f0e4cf] pt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#587165] mb-2">Developer Representative</h4>
                            <p class="text-xs font-bold text-[#1d3c34]">{{ $project->developer_name }}</p>
                            @if ($project->developer_phone)
                                <a href="tel:{{ $project->developer_phone }}" class="mt-1 block text-xs font-semibold text-slate-600 hover:text-[#1d3c34]">
                                    📞 {{ $project->developer_phone }}
                                </a>
                            @endif
                            @if ($project->developer_email)
                                <a href="mailto:{{ $project->developer_email }}" class="mt-0.5 block text-xs font-semibold text-slate-600 hover:text-[#1d3c34]">
                                    ✉️ {{ $project->developer_email }}
                                </a>
                            @endif
                        </div>

                        <div class="mt-6">
                            <a href="#units-inventory" class="block w-full text-center rounded-xl bg-[#1d3c34] py-3 text-xs font-bold text-white shadow-xs hover:bg-[#254d43] transition">
                                View Available Units
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Unit Reservation Modal --}}
    <div id="unit-reservation-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-xs p-4">
        <div class="w-full max-w-lg rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
            <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
                <div>
                    <span class="rounded bg-[#102b25] px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Off-Plan Sales</span>
                    <h3 class="text-lg font-black text-[#1d3c34] mt-1">Reserve Off-Plan Unit</h3>
                </div>
                <button type="button" onclick="closeReservationModal()" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mt-4 rounded-xl bg-[#fffdfa] border border-[#f0e4cf] p-3 text-xs text-slate-700">
                <p>Reserving: <strong id="modal-unit-label" class="text-[#1d3c34]">Unit A-101</strong> at <strong class="text-[#1d3c34]">{{ $project->name }}</strong></p>
                <p class="mt-1">Price: <strong id="modal-price-label" class="text-[#9b6c17]">ETB 0</strong></p>
            </div>

            <form id="reservation-form" method="POST" action="" class="mt-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Full Legal Name *</label>
                    <input type="text" name="buyer_name" required placeholder="Abebe Bikila" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Email Address *</label>
                        <input type="email" name="buyer_email" required placeholder="buyer@example.com" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Phone Number *</label>
                        <input type="text" name="buyer_phone" required placeholder="+251 91 123 4567" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Notes / Financing Preference (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Interested in 20% down payment plan with diaspora remittance." class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeReservationModal()" class="rounded-xl border border-slate-200 px-4 py-2.5 font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-xl bg-[#1d3c34] px-5 py-2.5 font-bold text-white shadow-xs hover:bg-[#254d43] cursor-pointer">
                        Confirm Unit Reservation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openReservationModal(unitId, unitNumber, priceAmount, buildingName) {
            const modal = document.getElementById('unit-reservation-modal');
            const form = document.getElementById('reservation-form');
            document.getElementById('modal-unit-label').textContent = 'Unit ' + unitNumber + ' (' + buildingName + ')';
            document.getElementById('modal-price-label').textContent = window.GTM_Currency
                ? window.GTM_Currency.formatPrice(priceAmount)
                : `ETB ${Number(priceAmount).toLocaleString()}`;
            
            form.action = "{{ url('/developments/' . $project->id . '/units') }}/" + unitId + "/reserve";
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeReservationModal() {
            const modal = document.getElementById('unit-reservation-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>

    @if (auth()->check() && (auth()->user()->isAdmin() || (auth()->user()->agentProfile && auth()->user()->agentProfile->id === $project->agent_id)))
        @include('developments.partials.edit-project-modal')
    @endif
@endsection
