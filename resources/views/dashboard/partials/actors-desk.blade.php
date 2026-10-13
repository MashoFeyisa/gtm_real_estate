{{-- Actor rosters: Hosts, Companies, Developers --}}
<section id="hosts" data-section="hosts" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
    <div class="flex items-center justify-between border-b border-[#e7ddca] pb-5">
        <div>
            <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold text-[#1d3c34]">Marketplace Actors</span>
            <h2 class="mt-2 text-2xl font-black text-[#1d3c34]">Hosts</h2>
            <p class="mt-1 text-sm text-slate-600">Short-stay hosts with listings, availability, pricing and bookings.</p>
        </div>
        <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-black uppercase tracking-wider text-[#9b6c17]">{{ $hostUsers->count() }} on desk</span>
    </div>

    @if ($hostUsers->isEmpty())
        <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
            <span class="text-3xl">🏠</span>
            <p class="mt-2 text-sm font-semibold text-[#587165]">No hosts registered yet.</p>
        </div>
    @else
        <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                    <tr>
                        <th class="p-3">Host</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0e8d8]">
                    @foreach ($hostUsers as $host)
                        <tr class="transition hover:bg-[#fbf8f3]">
                            <td class="p-3 font-bold text-[#1d3c34]">{{ $host->name }}</td>
                            <td class="p-3 text-[#587165]">{{ $host->email }}</td>
                            <td class="p-3">
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">Active</span>
                            </td>
                            <td class="p-3 text-[#587165]">{{ $host->created_at?->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

<section id="companies" data-section="companies" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
    <div class="flex items-center justify-between border-b border-[#e7ddca] pb-5">
        <div>
            <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold text-[#1d3c34]">Marketplace Actors</span>
            <h2 class="mt-2 text-2xl font-black text-[#1d3c34]">Companies</h2>
            <p class="mt-1 text-sm text-slate-600">Real estate companies with branches, agents and managers.</p>
        </div>
        <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-black uppercase tracking-wider text-[#9b6c17]">{{ $companyUsers->count() }} on desk</span>
    </div>

    @if ($companyUsers->isEmpty() && ($branches?->isEmpty() ?? true))
        <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
            <span class="text-3xl">🏢</span>
            <p class="mt-2 text-sm font-semibold text-[#587165]">No companies registered yet.</p>
        </div>
    @else
        <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                    <tr>
                        <th class="p-3">Company</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0e8d8]">
                    @foreach ($companyUsers as $company)
                        <tr class="transition hover:bg-[#fbf8f3]">
                            <td class="p-3 font-bold text-[#1d3c34]">{{ $company->name }}</td>
                            <td class="p-3 text-[#587165]">{{ $company->email }}</td>
                            <td class="p-3 text-[#587165]">{{ $company->created_at?->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if (($branches?->isNotEmpty() ?? false))
            <div class="mt-6 rounded-2xl border border-[#e7ddca] bg-[#fbf8f3] p-4">
                <p class="text-[10px] font-black uppercase tracking-wider text-[#587165]">Branches</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($branches->take(12) as $branch)
                        <span class="rounded-full bg-white px-3 py-1 text-[11px] font-bold text-[#1d3c34] border border-[#e7ddca]">{{ $branch->name }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</section>

<section id="developers" data-section="developers" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
    <div class="flex items-center justify-between border-b border-[#e7ddca] pb-5">
        <div>
            <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold text-[#1d3c34]">Marketplace Actors</span>
            <h2 class="mt-2 text-2xl font-black text-[#1d3c34]">Developers</h2>
            <p class="mt-1 text-sm text-slate-600">Developer accounts running projects, buildings, units and sales.</p>
        </div>
        <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-black uppercase tracking-wider text-[#9b6c17]">{{ $developerUsers->count() }} on desk</span>
    </div>

    @if ($developerUsers->isEmpty())
        <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
            <span class="text-3xl">🏗️</span>
            <p class="mt-2 text-sm font-semibold text-[#587165]">No developer accounts yet.</p>
        </div>
    @else
        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($developerUsers as $developer)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf8f3] p-4">
                    <div class="flex items-center gap-3">
                        @if ($developer->photo_url)
                            <img src="{{ $developer->photo_url }}" alt="{{ $developer->name }}" class="h-10 w-10 rounded-full object-cover ring-2 ring-[#d9cab3]">
                        @else
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#1d3c34] text-sm font-bold text-white">{{ $developer->initials ?: 'GT' }}</span>
                        @endif
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-[#1d3c34]">{{ $developer->name }}</p>
                            <p class="text-[11px] text-slate-500">{{ $developer->email }}</p>
                        </div>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-500">Joined {{ $developer->created_at?->format('M d, Y') }}</p>
                </div>
            @endforeach
        </div>
    @endif
</section>
