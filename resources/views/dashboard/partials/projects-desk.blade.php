<section id="projects" data-section="projects" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
    {{-- Header --}}
    <div class="flex flex-col gap-4 border-b border-[#e7ddca] pb-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold text-[#1d3c34]">New Projects Mode</span>
            <h2 class="mt-2 text-2xl font-black text-[#1d3c34]">Developments &amp; Projects</h2>
            <p class="mt-1 text-sm text-slate-600">Manage developer projects, inventory units, construction milestones, and track all CRUD lifecycle actions.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-black uppercase tracking-wider text-[#9b6c17]">
                {{ ($projects ?? collect())->count() }} {{ Str::plural('project', ($projects ?? collect())->count()) }}
            </span>
            <button type="button" onclick="toggleCreateProjectForm()" class="inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#254d43]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Add Development</span>
            </button>
        </div>
    </div>

    {{-- Collapsible Create Development Panel --}}
    <div id="projects-create-panel" class="mt-6 hidden rounded-2xl border border-[#d9cab3] bg-[#fdfaf5] p-6 shadow-sm animate-in fade-in duration-200">
        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
            <div>
                <span class="rounded-full bg-[#e2ede7] px-2.5 py-0.5 text-[11px] font-bold text-[#1d3c34]">Create New</span>
                <h3 class="mt-1 text-lg font-black text-[#1d3c34]">New Development Project</h3>
            </div>
            <button type="button" onclick="toggleCreateProjectForm()" class="rounded-full p-1.5 text-slate-400 hover:bg-[#f2eadc] hover:text-slate-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('developments.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-5">
            @csrf
            <input type="hidden" name="from_dashboard" value="1">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Project Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Bole Luxury Skyview Towers" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Developer / Company Name *</label>
                    <input type="text" name="developer_name" required placeholder="e.g. Sunshine Construction PLC" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Developer Phone</label>
                    <input type="text" name="developer_phone" placeholder="+251 911 234 567" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Developer Email</label>
                    <input type="email" name="developer_email" placeholder="sales@developer.et" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">City *</label>
                    <input type="text" name="city" value="Addis Ababa" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Subcity / District</label>
                    <input type="text" name="subcity" placeholder="e.g. Bole, Kirkos, Yeka" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Street Address / Landmark</label>
                    <input type="text" name="address" placeholder="e.g. Next to Edna Mall, Cameroon Street, Bole" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Project Type *</label>
                    <select name="project_type" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                        <option value="apartments">Apartments</option>
                        <option value="residential">Residential Complex</option>
                        <option value="luxury_villas">Luxury Villas</option>
                        <option value="mixed_use">Mixed Use</option>
                        <option value="commercial">Commercial Center</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Construction Status *</label>
                    <select name="construction_status" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                        <option value="planning">Planning</option>
                        <option value="pre_construction">Pre-Construction</option>
                        <option value="under_construction" selected>Under Construction</option>
                        <option value="near_completion">Near Completion</option>
                        <option value="completed">Completed</option>
                        <option value="delivered">Delivered / Handed Over</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Overall Progress (%)</label>
                    <input type="number" name="overall_completion_percentage" min="0" max="100" value="25" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Starting Price (ETB) *</label>
                    <input type="number" step="0.01" name="starting_price" required placeholder="e.g. 8500000" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Expected Completion Date</label>
                    <input type="text" name="expected_completion_date" placeholder="e.g. Q4 2026 or Dec 2026" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Land Area (sqm)</label>
                    <input type="number" step="0.01" name="land_area_sqm" placeholder="e.g. 2500" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Total Planned Buildings</label>
                    <input type="number" name="total_buildings_count" min="1" value="1" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Description</label>
                <textarea name="description" rows="3" placeholder="Provide a summary of the project architecture, features, and developer background..." class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]"></textarea>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Key Facilities &amp; Amenities</label>
                    <textarea name="facilities_input" rows="2" placeholder="e.g. Underground Parking, Standby Generator, 24/7 Gated Security, Elevators (separate lines or commas)" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Featured Image URL or Upload</label>
                    <input type="text" name="featured_image" placeholder="https://... image URL" class="mb-2 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    <input type="file" name="featured_image_file" accept="image/*" class="w-full text-xs text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-[#edf2ee] file:px-3 file:py-1 file:text-xs file:font-semibold file:text-[#1d3c34]">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_featured" value="1" id="create-is-featured" class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                <label for="create-is-featured" class="text-xs font-bold text-slate-700">Feature this project prominently on the homepage and developments directory</label>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-[#e7ddca] pt-4">
                <button type="button" onclick="toggleCreateProjectForm()" class="rounded-full border border-[#d9cab3] bg-white px-5 py-2 text-xs font-bold text-slate-700 hover:bg-[#f5f0e7]">
                    Cancel
                </button>
                <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#254d43]">
                    Create Development Project
                </button>
            </div>
        </form>
    </div>

    {{-- Projects Table --}}
    @if (($projects ?? collect())->isEmpty())
        <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
            <span class="text-4xl">🏗️</span>
            <p class="mt-2 text-base font-bold text-[#1d3c34]">No development projects yet.</p>
            <p class="mt-1 text-xs text-slate-500">Get started by creating your first real estate development project.</p>
            <button type="button" onclick="toggleCreateProjectForm()" class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                + Add Development
            </button>
        </div>
    @else
        <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                    <tr>
                        <th class="p-3">Code</th>
                        <th class="p-3">Project</th>
                        <th class="p-3">Developer</th>
                        <th class="p-3">Type</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Progress</th>
                        <th class="p-3 text-center">Buildings</th>
                        <th class="p-3 text-center">Units</th>
                        <th class="p-3">Starting Price</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0e8d8]">
                    @foreach ($projects as $project)
                        @php
                            $projectData = [
                                'id' => $project->id,
                                'update_url' => route('developments.update', $project),
                                'name' => $project->name,
                                'developer_name' => $project->developer_name,
                                'developer_phone' => $project->developer_phone,
                                'developer_email' => $project->developer_email,
                                'city' => $project->city,
                                'subcity' => $project->subcity,
                                'address' => $project->address,
                                'project_type' => $project->project_type,
                                'construction_status' => $project->construction_status,
                                'overall_completion_percentage' => $project->overall_completion_percentage,
                                'expected_completion_date' => $project->expected_completion_date,
                                'starting_price' => (float) $project->starting_price,
                                'land_area_sqm' => (float) $project->land_area_sqm,
                                'description' => $project->description,
                                'facilities_text' => is_array($project->facilities) ? implode("\n", $project->facilities) : '',
                                'featured_image' => $project->featured_image,
                                'is_featured' => (bool) $project->is_featured,
                            ];
                        @endphp
                        <tr class="transition hover:bg-[#fbf8f3]">
                            <td class="p-3 font-mono text-[11px] font-bold text-[#587165] whitespace-nowrap">
                                {{ $project->project_code }}
                                @if ($project->is_featured)
                                    <span class="ml-1 inline-block rounded bg-amber-100 px-1 py-0.2 text-[9px] font-black uppercase text-amber-800">Featured</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <a href="{{ route('developments.show', $project->slug) }}" class="font-bold text-[#1d3c34] hover:underline">
                                    {{ $project->name }}
                                </a>
                                <span class="block text-[10px] font-semibold text-slate-400">
                                    {{ $project->city }}{{ $project->subcity ? ', ' . $project->subcity : '' }}
                                </span>
                            </td>
                            <td class="p-3 text-[#587165]">
                                <span class="font-medium text-slate-800">{{ $project->developer_name }}</span>
                                @if ($project->developer_phone)
                                    <span class="block text-[10px] text-slate-400">{{ $project->developer_phone }}</span>
                                @endif
                            </td>
                            <td class="p-3 text-[#587165] whitespace-nowrap">
                                <span class="rounded-full bg-[#f4ece1] px-2 py-0.5 text-[10px] font-semibold text-[#6d4d13]">
                                    {{ ucwords(str_replace('_', ' ', $project->project_type)) }}
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                <span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-[10px] font-bold text-[#9b6c17]">
                                    {{ ucwords(str_replace('_', ' ', $project->construction_status)) }}
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-14 overflow-hidden rounded-full bg-[#e7ddca]">
                                        <div class="h-full rounded-full bg-[#1d3c34]" style="width: {{ $project->overall_completion_percentage }}%"></div>
                                    </div>
                                    <span class="text-[11px] font-bold text-[#1d3c34]">{{ $project->overall_completion_percentage }}%</span>
                                </div>
                            </td>
                            <td class="p-3 text-center font-bold text-[#1d3c34]">{{ $project->buildings->count() }}</td>
                            <td class="p-3 text-center font-bold text-[#1d3c34]">{{ $project->units->count() }}</td>
                            <td class="p-3 font-bold text-[#1d3c34] whitespace-nowrap">{{ $project->formatted_starting_price }}</td>
                            <td class="p-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Showcase link --}}
                                    <a href="{{ route('developments.show', $project->slug) }}" target="_blank" class="rounded-lg border border-[#d9cab3] bg-white p-1.5 text-[#1d3c34] shadow-xs transition hover:bg-[#f2eadc]" title="View Public Showcase">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    {{-- Edit Project Button --}}
                                    <button type="button" onclick='openEditProjectModal({!! json_encode($projectData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!})' class="rounded-lg border border-[#d9cab3] bg-white p-1.5 text-[#9b6c17] shadow-xs transition hover:bg-[#f9ecd0]" title="Edit Project">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    {{-- Quick Add Unit Button --}}
                                    <button type="button" onclick="openAddUnitModal({{ $project->id }}, '{{ addslashes($project->name) }}', '{{ route('developments.units.store', $project) }}')" class="rounded-lg border border-[#1d3c34] bg-[#edf2ee] px-2 py-1 text-[11px] font-bold text-[#1d3c34] transition hover:bg-[#1d3c34] hover:text-white" title="Add Unit to Inventory">
                                        + Unit
                                    </button>

                                    {{-- Delete Project Form --}}
                                    <form method="POST" action="{{ route('developments.destroy', $project) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete development project \'{{ addslashes($project->name) }}\' and all associated inventory units? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="from_dashboard" value="1">
                                        <button type="submit" class="rounded-lg border border-red-200 bg-red-50 p-1.5 text-red-600 shadow-xs transition hover:bg-red-100 hover:text-red-700" title="Delete Project">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Development CRUD Activity & Audit Trail --}}
    <div class="mt-10 border-t border-[#e7ddca] pt-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="rounded-full bg-[#102b25] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Real-Time Audit Trail</span>
                <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Development CRUD Activity &amp; Logs</h3>
                <p class="mt-0.5 text-xs text-slate-600">Audit trail of development project creations, edits, inventory changes, unit status transitions, and deletions.</p>
            </div>
            <div>
                <span class="rounded-full bg-[#edf3ee] px-3 py-1 text-xs font-bold text-[#2d5d4d]">
                    {{ ($developmentActivities ?? collect())->count() }} Recent Events
                </span>
            </div>
        </div>

        @if (($developmentActivities ?? collect())->isEmpty())
            <div class="mt-4 rounded-xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-8 text-center">
                <span class="text-2xl">📋</span>
                <p class="mt-1 text-xs font-semibold text-slate-600">No recent development CRUD activity recorded yet.</p>
                <p class="text-[11px] text-slate-400">Actions taken on development projects or units will automatically record here.</p>
            </div>
        @else
            <div class="mt-4 overflow-x-auto rounded-xl border border-[#e7ddca]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                        <tr>
                            <th class="p-3">Timestamp</th>
                            <th class="p-3">Actor</th>
                            <th class="p-3">Action</th>
                            <th class="p-3">Target / Project</th>
                            <th class="p-3">Details &amp; Reason</th>
                            <th class="p-3">Network</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e8d8]">
                        @foreach ($developmentActivities as $activity)
                            @php
                                $action = $activity->action;
                                $badgeStyle = match(true) {
                                    str_contains($action, '.created') => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    str_contains($action, '.updated') => 'bg-blue-100 text-blue-800 border-blue-200',
                                    str_contains($action, '.deleted') => 'bg-rose-100 text-rose-800 border-rose-200',
                                    str_contains($action, 'unit_status') => 'bg-amber-100 text-amber-800 border-amber-200',
                                    str_contains($action, 'milestone') => 'bg-purple-100 text-purple-800 border-purple-200',
                                    default => 'bg-slate-100 text-slate-800 border-slate-200',
                                };
                            @endphp
                            <tr class="transition hover:bg-[#fbf8f3]">
                                <td class="p-3 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                                    {{ $activity->created_at?->format('M d, Y H:i:s') ?? '—' }}
                                    <span class="block text-[10px] text-slate-400">{{ $activity->created_at?->diffForHumans() }}</span>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span class="font-bold text-[#1d3c34]">{{ $activity->actor_name ?? $activity->user?->name ?? 'System' }}</span>
                                    @if ($activity->user?->role)
                                        <span class="block text-[10px] font-semibold uppercase text-slate-400">{{ $activity->user->role }}</span>
                                    @endif
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span class="inline-block rounded-full border px-2 py-0.5 font-mono text-[10px] font-bold {{ $badgeStyle }}">
                                        {{ $action }}
                                    </span>
                                </td>
                                <td class="p-3 whitespace-nowrap font-medium text-slate-700">
                                    @if ($activity->entity_type)
                                        <span class="text-slate-500 font-semibold">{{ class_basename($activity->entity_type) }}</span>
                                        <span class="font-bold text-[#1d3c34]">#{{ $activity->entity_id }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="p-3 text-slate-600 max-w-md">
                                    <div class="font-medium text-slate-800">{{ $activity->reason ?? 'Development project action performed.' }}</div>
                                    @if ($activity->new_value)
                                        <div class="mt-0.5 text-[11px] text-slate-500">
                                            @if (isset($activity->new_value['name']))
                                                <span class="font-semibold text-[#1d3c34]">{{ $activity->new_value['name'] }}</span>
                                            @endif
                                            @if (isset($activity->new_value['unit_number']))
                                                <span>Unit {{ $activity->new_value['unit_number'] }}</span>
                                                @if (isset($activity->new_value['status']))
                                                    <span class="font-bold text-amber-700">({{ $activity->new_value['status'] }})</span>
                                                @endif
                                            @endif
                                            @if (isset($activity->new_value['percentage']))
                                                <span>Progress: {{ $activity->new_value['percentage'] }}%</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="p-3 whitespace-nowrap font-mono text-[10px] text-slate-400">
                                    {{ $activity->ip_address ?? '127.0.0.1' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Quick Add Unit Modal --}}
    <div id="admin-add-unit-modal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-2xl md:p-8 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                <div>
                    <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold text-[#1d3c34]">Unit Inventory</span>
                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Add Unit to Project</h3>
                    <p id="add-unit-project-title" class="mt-0.5 text-xs font-semibold text-[#9b6c17]"></p>
                </div>
                <button type="button" onclick="closeAddUnitModal()" class="rounded-full p-2 text-slate-400 hover:bg-[#f5f0e7] hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="admin-add-unit-form" method="POST" action="" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="from_dashboard" value="1">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Unit Number *</label>
                        <input type="text" name="unit_number" required placeholder="e.g. A-302" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Floor Number *</label>
                        <input type="text" name="floor_number" required placeholder="e.g. 3rd Floor" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Unit Type *</label>
                        <select name="unit_type" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                            <option value="studio">Studio</option>
                            <option value="1_bedroom">1 Bedroom</option>
                            <option value="2_bedroom" selected>2 Bedroom</option>
                            <option value="3_bedroom">3 Bedroom</option>
                            <option value="4_bedroom">4 Bedroom</option>
                            <option value="penthouse">Penthouse</option>
                            <option value="duplex">Duplex</option>
                            <option value="retail_shop">Retail Shop</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Inventory Status *</label>
                        <select name="status" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                            <option value="available" selected>Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="contracted">Contracted</option>
                            <option value="sold">Sold</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Bedrooms *</label>
                        <input type="number" name="bedrooms" min="0" max="20" value="2" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Baths *</label>
                        <input type="number" name="bathrooms" min="1" max="20" value="2" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Area (sqm) *</label>
                        <input type="number" step="0.01" name="area_sqm" min="1" required placeholder="110" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Parking</label>
                        <input type="number" name="parking_spaces" min="0" value="1" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Price (ETB) *</label>
                        <input type="number" step="0.01" name="price" min="1" required placeholder="e.g. 7800000" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Orientation / View</label>
                        <input type="text" name="orientation" placeholder="e.g. East Sunrise, Pool View" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="closeAddUnitModal()" class="rounded-full border border-[#d9cab3] bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7]">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#254d43]">
                        Add Unit to Inventory
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Development Modal --}}
    <div id="admin-edit-project-modal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm overflow-y-auto">
        <div class="my-8 w-full max-w-3xl rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-2xl md:p-8 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                <div>
                    <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-bold uppercase tracking-wider text-[#9b6c17]">Edit Development</span>
                    <h3 id="edit-project-modal-title" class="mt-2 text-xl font-black text-[#1d3c34]">Update Project Details</h3>
                </div>
                <button type="button" onclick="closeEditProjectModal()" class="rounded-full p-2 text-slate-400 hover:bg-[#f5f0e7] hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="admin-edit-project-form" method="POST" action="" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="from_dashboard" value="1">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Project Name *</label>
                        <input type="text" name="name" id="edit-project-name" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Developer Name *</label>
                        <input type="text" name="developer_name" id="edit-project-developer-name" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Developer Phone</label>
                        <input type="text" name="developer_phone" id="edit-project-developer-phone" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Developer Email</label>
                        <input type="email" name="developer_email" id="edit-project-developer-email" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">City *</label>
                        <input type="text" name="city" id="edit-project-city" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Subcity</label>
                        <input type="text" name="subcity" id="edit-project-subcity" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Street Address</label>
                        <input type="text" name="address" id="edit-project-address" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Project Type *</label>
                        <select name="project_type" id="edit-project-type" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                            <option value="apartments">Apartments</option>
                            <option value="residential">Residential Complex</option>
                            <option value="luxury_villas">Luxury Villas</option>
                            <option value="mixed_use">Mixed Use</option>
                            <option value="commercial">Commercial Center</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Construction Status *</label>
                        <select name="construction_status" id="edit-project-status" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                            <option value="planning">Planning</option>
                            <option value="pre_construction">Pre-Construction</option>
                            <option value="under_construction">Under Construction</option>
                            <option value="near_completion">Near Completion</option>
                            <option value="completed">Completed</option>
                            <option value="delivered">Delivered</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Completion (%)</label>
                        <input type="number" name="overall_completion_percentage" id="edit-project-completion" min="0" max="100" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Starting Price (ETB) *</label>
                        <input type="number" step="0.01" name="starting_price" id="edit-project-starting-price" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Expected Completion Date</label>
                        <input type="text" name="expected_completion_date" id="edit-project-expected-completion" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Land Area (sqm)</label>
                        <input type="number" step="0.01" name="land_area_sqm" id="edit-project-land-area" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Description</label>
                    <textarea name="description" id="edit-project-description" rows="3" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Facilities &amp; Amenities</label>
                        <textarea name="facilities_input" id="edit-project-facilities" rows="2" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]"></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Featured Image URL or File</label>
                        <input type="text" name="featured_image" id="edit-project-featured-image" placeholder="Image URL" class="mb-2 w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                        <input type="file" name="featured_image_file" accept="image/*" class="w-full text-xs text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-[#edf2ee] file:px-3 file:py-1 file:text-xs file:font-semibold file:text-[#1d3c34]">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_featured" value="1" id="edit-project-is-featured" class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                    <label for="edit-project-is-featured" class="text-xs font-bold text-slate-700">Mark as Featured Development</label>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditProjectModal()" class="rounded-full border border-[#d9cab3] bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7]">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#254d43]">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    function toggleCreateProjectForm() {
        const panel = document.getElementById('projects-create-panel');
        if (!panel) return;
        panel.classList.toggle('hidden');
        if (!panel.classList.contains('hidden')) {
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function openEditProjectModal(project) {
        if (!project) return;
        const modal = document.getElementById('admin-edit-project-modal');
        const form = document.getElementById('admin-edit-project-form');
        if (!modal || !form) return;

        form.action = project.update_url || ('/developments/' + project.id);
        document.getElementById('edit-project-modal-title').textContent = 'Edit: ' + (project.name || 'Development');

        document.getElementById('edit-project-name').value = project.name || '';
        document.getElementById('edit-project-developer-name').value = project.developer_name || '';
        document.getElementById('edit-project-developer-phone').value = project.developer_phone || '';
        document.getElementById('edit-project-developer-email').value = project.developer_email || '';
        document.getElementById('edit-project-city').value = project.city || 'Addis Ababa';
        document.getElementById('edit-project-subcity').value = project.subcity || '';
        document.getElementById('edit-project-address').value = project.address || '';
        document.getElementById('edit-project-type').value = project.project_type || 'apartments';
        document.getElementById('edit-project-status').value = project.construction_status || 'under_construction';
        document.getElementById('edit-project-completion').value = project.overall_completion_percentage ?? 0;
        document.getElementById('edit-project-starting-price').value = project.starting_price || 0;
        document.getElementById('edit-project-expected-completion').value = project.expected_completion_date || '';
        document.getElementById('edit-project-land-area').value = project.land_area_sqm || '';
        document.getElementById('edit-project-description').value = project.description || '';
        document.getElementById('edit-project-facilities').value = project.facilities_text || '';
        document.getElementById('edit-project-featured-image').value = project.featured_image || '';
        document.getElementById('edit-project-is-featured').checked = !!project.is_featured;

        modal.classList.remove('hidden');
    }

    function closeEditProjectModal() {
        const modal = document.getElementById('admin-edit-project-modal');
        if (modal) modal.classList.add('hidden');
    }

    function openAddUnitModal(projectId, projectName, actionUrl) {
        const modal = document.getElementById('admin-add-unit-modal');
        const form = document.getElementById('admin-add-unit-form');
        const titleEl = document.getElementById('add-unit-project-title');
        if (!modal || !form) return;

        form.action = actionUrl || ('/developments/' + projectId + '/units');
        if (titleEl) titleEl.textContent = 'Project: ' + projectName;

        modal.classList.remove('hidden');
    }

    function closeAddUnitModal() {
        const modal = document.getElementById('admin-add-unit-modal');
        if (modal) modal.classList.add('hidden');
    }

    // Close modals on escape key or backdrop click
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditProjectModal();
            closeAddUnitModal();
        }
    });

    ['admin-edit-project-modal', 'admin-add-unit-modal'].forEach(function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        }
    });
</script>
