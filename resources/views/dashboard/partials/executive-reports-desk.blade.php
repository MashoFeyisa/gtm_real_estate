{{-- GTM Real Estate Company - Executive Intelligence & Reports Desk --}}
@php
    $allBranches = isset($branches) ? $branches : collect();
    $allProperties = isset($properties) ? $properties : collect();
    $allAgents = isset($agents) ? $agents : collect();
    $allOrders = isset($orders) ? $orders : collect();
    $allListings = isset($listings) ? $listings : collect();

    $portfolioTotalValuation = $allProperties->sum('price');
    $grossOrdersVolume = $allOrders->sum(fn ($o) => (float) ($o->property_price ?? $o->offer_amount ?? $o->property?->price ?? 0));
    $activePropertiesCount = $allProperties->where('is_active', true)->count();
    $verifiedAgentsCount = $allAgents->where('license_verified', true)->count();
    $avgPropertyPrice = $allProperties->count() > 0 ? ($portfolioTotalValuation / $allProperties->count()) : 0;
@endphp

<section id="executive-reports" data-section="executive-reports" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#d4af37]">Enterprise Intelligence</span>
                <span class="text-xs font-bold text-[#587165]">የሪፖርቶች እና የአፈጻጸም ማጠቃለያ</span>
            </div>
            <h2 class="mt-1 text-2xl md:text-3xl font-black text-[#1d3c34]">GTM Real Estate Executive Reports</h2>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">
                Consolidated business intelligence, branch performance comparisons, sales volume metrics, and exportable financial dossiers.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="exportExecutiveBranchCsv()" class="inline-flex items-center gap-1.5 rounded-full border border-[#d9cab3] bg-[#fbf8f3] px-4 py-2.5 text-xs font-bold text-[#1d3c34] hover:bg-[#1d3c34] hover:text-white transition shadow-xs cursor-pointer">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export Branches CSV</span>
            </button>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-[#d4af37] shadow-sm transition hover:bg-[#254d43] cursor-pointer">
                <svg class="h-4 w-4 text-[#d4af37]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Executive Brief</span>
            </button>
        </div>
    </div>

    {{-- 4 Big Enterprise Executive KPIs --}}
    <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Asset Portfolio Valuation</span>
            <div class="mt-1 flex items-baseline gap-1">
                <span class="text-xl font-black text-[#1d3c34]">ETB {{ number_format($portfolioTotalValuation, 0) }}</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-500">{{ $allProperties->count() }} properties across all branches</p>
        </div>

        <div class="rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Cumulative Sales Volume</span>
            <div class="mt-1 flex items-baseline gap-1">
                <span class="text-xl font-black text-emerald-800">ETB {{ number_format($grossOrdersVolume, 0) }}</span>
            </div>
            <p class="mt-1 text-[11px] text-emerald-700 font-medium">{{ $allOrders->count() }} signed acquisition orders</p>
        </div>

        <div class="rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Active Branch Network</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-black text-[#9b6c17]">{{ $allBranches->where('status', 'active')->count() }} / {{ $allBranches->count() }}</span>
                <span class="text-xs font-semibold text-slate-500">Active</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-400">HQ + Regional hubs in Ethiopia</p>
        </div>

        <div class="rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Average Unit Valuation</span>
            <div class="mt-1 flex items-baseline gap-1">
                <span class="text-xl font-black text-[#102b25]">ETB {{ number_format($avgPropertyPrice, 0) }}</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-500">Median listed property price</p>
        </div>
    </div>

    {{-- Branch Performance Matrix Table --}}
    <div class="mt-8">
        <div class="flex items-center justify-between pb-3">
            <div>
                <h3 class="text-lg font-black text-[#1d3c34]">Branch Performance &amp; Allocation Matrix</h3>
                <p class="text-xs text-slate-500">Distribution of licensed agents, property inventory, and active listings per branch office.</p>
            </div>
            <a href="#branches" data-target-section="branches" class="nav-section-link text-xs font-extrabold text-[#1d3c34] hover:underline">
                Manage Branches →
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-[#d9cab3] bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs text-slate-700" id="executive-branch-matrix-table">
                    <thead class="bg-[#1d3c34] text-[#f8f3eb]">
                        <tr>
                            <th class="px-4 py-3 font-bold uppercase tracking-wider text-[11px]">Branch Office</th>
                            <th class="px-4 py-3 font-bold uppercase tracking-wider text-[11px]">Branch Code</th>
                            <th class="px-4 py-3 font-bold uppercase tracking-wider text-[11px]">City / Location</th>
                            <th class="px-4 py-3 font-bold uppercase tracking-wider text-[11px] text-center">Assigned Agents</th>
                            <th class="px-4 py-3 font-bold uppercase tracking-wider text-[11px] text-center">Managed Properties</th>
                            <th class="px-4 py-3 font-bold uppercase tracking-wider text-[11px]">Branch Manager</th>
                            <th class="px-4 py-3 font-bold uppercase tracking-wider text-[11px]">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e4cf]">
                        @forelse ($allBranches as $branch)
                            <tr class="transition hover:bg-[#fbf8f3]">
                                <td class="px-4 py-3 font-bold text-[#1d3c34]">
                                    <div class="flex items-center gap-2">
                                        @if ($branch->is_headquarters)
                                            <span class="rounded bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black uppercase text-[#102b25]">HQ</span>
                                        @endif
                                        <span>{{ $branch->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-mono font-bold text-slate-600">{{ $branch->code }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $branch->city }}, {{ $branch->region }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="rounded-full bg-[#f4ebe1] px-2.5 py-0.5 font-bold text-[#1d3c34]">
                                        {{ $branch->agents->count() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 font-bold text-emerald-800">
                                        {{ $branch->properties->count() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-700">
                                    {{ $branch->manager_name ?: 'HQ Operations' }}
                                    @if ($branch->phone)
                                        <span class="block text-[10px] text-slate-400 font-mono">{{ $branch->phone }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $branch->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($branch->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-slate-500">No branches registered.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Asset Breakdown & Broker Leaderboard Grid --}}
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Real Estate Portfolio by Category --}}
        <div class="rounded-2xl border border-[#d9cab3] bg-white p-5 shadow-xs">
            <h4 class="text-sm font-black uppercase tracking-wider text-[#1d3c34]">Asset Category Breakdown</h4>
            <p class="mt-0.5 text-xs text-slate-500">Portfolio distribution by property type.</p>

            @php
                $categories = $allProperties->groupBy('category');
            @endphp

            <div class="mt-4 space-y-3">
                @foreach ($categories as $catName => $items)
                    @php
                        $percentage = $allProperties->count() > 0 ? round(($items->count() / $allProperties->count()) * 100) : 0;
                        $catVal = $items->sum('price');
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                            <span>{{ ucfirst($catName ?: 'Standard') }} ({{ $items->count() }})</span>
                            <span class="font-mono text-[#1d3c34]">ETB {{ number_format($catVal, 0) }} ({{ $percentage }}%)</span>
                        </div>
                        <div class="mt-1 h-2 w-full rounded-full bg-[#f0e8d8] overflow-hidden">
                            <div class="h-full rounded-full bg-[#1d3c34]" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Top Producing Agents Leaderboard --}}
        <div class="rounded-2xl border border-[#d9cab3] bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-black uppercase tracking-wider text-[#1d3c34]">Agent Performance Roster</h4>
                    <p class="mt-0.5 text-xs text-slate-500">Broker roster, license compliance and branch assignments.</p>
                </div>
                <a href="#users" data-target-section="users" class="nav-section-link text-xs font-bold text-[#1d3c34] hover:underline">
                    View All →
                </a>
            </div>

            <div class="mt-4 space-y-2.5 max-h-72 overflow-y-auto">
                @forelse ($allAgents->take(5) as $agent)
                    <div class="flex items-center justify-between rounded-xl border border-[#f0e4cf] bg-[#fbf8f3] p-3 transition hover:border-[#1d3c34]">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#1d3c34] text-xs font-black text-white">
                                {{ strtoupper(substr($agent->name, 0, 2)) }}
                            </span>
                            <div>
                                <p class="text-xs font-bold text-[#1d3c34]">{{ $agent->name }}</p>
                                <p class="text-[10px] text-slate-400">
                                    {{ $agent->branch?->name ?: 'Branch Assigned' }} • Split: {{ $agent->split_rate ?? 70 }}%
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                                {{ $agent->license_verified ? 'Verified Broker' : 'Pending KYC' }}
                            </span>
                            <p class="text-[10px] text-slate-400 mt-0.5">Lic: {{ $agent->license_number ?: 'Active' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 py-4 text-center">No agents registered.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Client Incident & Trust Reports Log (Recorded from Client Report) --}}
    @php
        $allClientReports = isset($reports) ? $reports : collect();
        $pendingClientReports = $allClientReports->where('status', 'pending')->count();
        $investigatingClientReports = $allClientReports->where('status', 'under_investigation')->count();
        $resolvedClientReports = $allClientReports->where('status', 'resolved')->count();
    @endphp
    <div class="mt-8 border-t border-[#f0e4cf] pt-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded bg-rose-600 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-white">Trust &amp; Safety Log</span>
                    <span class="text-xs font-bold text-[#587165]">ከደንበኞች የተመዘገቡ ሪፖርቶች</span>
                </div>
                <h3 class="mt-1 text-xl font-black text-[#1d3c34]">Client Incident &amp; Misconduct Reports</h3>
                <p class="text-xs text-slate-500">Live feed of dispute notices, fraud alerts, listing reports, and direct complaints recorded from the client portal.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-[#f8f3eb] px-3 py-1 text-xs font-bold text-[#1d3c34] border border-[#ecd9be]">
                    Total Reports: {{ $allClientReports->count() }}
                </span>
                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800 border border-rose-200">
                    Pending: {{ $pendingClientReports }}
                </span>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                    Resolved: {{ $resolvedClientReports }}
                </span>
            </div>
        </div>

        @if ($allClientReports->isEmpty())
            <div class="mt-4 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fffdfa] p-8 text-center">
                <span class="text-3xl">🛡️</span>
                <h4 class="mt-2 text-sm font-bold text-[#1d3c34]">No Client Reports Recorded</h4>
                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                    When clients or tenants report an incident, price discrepancy, or misconduct from their portal, it will be automatically logged and actionable here.
                </p>
            </div>
        @else
            <div class="mt-4 overflow-hidden rounded-2xl border border-[#d9cab3] bg-white shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-xs text-slate-700">
                        <thead class="bg-[#102b25] text-[#f8f3eb]">
                            <tr>
                                <th class="px-4 py-3 font-bold uppercase tracking-wider text-[11px]">Report #</th>
                                <th class="px-4 py-3 font-bold uppercase tracking-wider text-[11px]">Client / Contact</th>
                                <th class="px-4 py-3 font-bold uppercase tracking-wider text-[11px]">Reason / Category</th>
                                <th class="px-4 py-3 font-bold uppercase tracking-wider text-[11px]">Subject &amp; Property</th>
                                <th class="px-4 py-3 font-bold uppercase tracking-wider text-[11px]">Status</th>
                                <th class="px-4 py-3 font-bold uppercase tracking-wider text-[11px]">Date</th>
                                <th class="px-4 py-3 font-bold uppercase tracking-wider text-[11px] text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0e4cf]">
                            @foreach ($allClientReports as $report)
                                @php
                                    $reportBadgeClass = match ($report->status) {
                                        'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
                                        'under_investigation' => 'bg-blue-100 text-blue-800 border-blue-300',
                                        'resolved' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        default => 'bg-slate-100 text-slate-700 border-slate-300',
                                    };
                                @endphp
                                <tr class="transition hover:bg-[#fbf8f3]">
                                    <td class="px-4 py-3 font-mono font-bold text-[#102b25]">
                                        {{ $report->report_number }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-[#1d3c34]">{{ $report->user?->name ?: 'Guest / Anonymous' }}</div>
                                        @if ($report->contact_email)
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $report->contact_email }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded bg-rose-50 border border-rose-200 px-2 py-0.5 text-[10px] font-bold text-rose-800">
                                            {{ $report->reason_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-[#1d3c34] truncate max-w-xs">{{ $report->subject ?: 'General Issue' }}</p>
                                        @if ($report->property)
                                            <a href="{{ route('properties.show', $report->property->slug) }}" target="_blank" class="text-[10px] text-emerald-800 hover:underline">
                                                🏡 {{ $report->property->title }}
                                            </a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase border {{ $reportBadgeClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-500 whitespace-nowrap text-[11px]">
                                        {{ $report->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" onclick="toggleCompanyReportPanel('{{ $report->id }}')" class="rounded-lg border border-[#cdbb97] bg-white px-2.5 py-1 text-[11px] font-bold text-[#1d3c34] hover:bg-[#f1e4cf] transition cursor-pointer">
                                                Update
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                {{-- Inline status update dropdown panel --}}
                                <tr id="company-report-panel-{{ $report->id }}" class="hidden bg-[#fcf9f2]">
                                    <td colspan="7" class="p-4 border-t border-[#f0e4cf]">
                                        <div class="rounded-xl border border-[#ebdcc9] bg-white p-4">
                                            <div class="mb-2">
                                                <p class="text-[11px] font-bold text-slate-500 uppercase">Client Description:</p>
                                                <p class="text-xs text-slate-800 italic bg-[#fcfaf7] p-2.5 rounded-lg border border-[#ebdcc9] mt-1 whitespace-pre-wrap">"{{ $report->description }}"</p>
                                            </div>
                                            <form method="POST" action="{{ route('reports.update', $report->id) }}" class="flex flex-col sm:flex-row sm:items-end gap-3 mt-3">
                                                @csrf
                                                @method('PUT')
                                                <div class="flex-1">
                                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status</label>
                                                    <select name="status" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-1.5 text-xs font-bold text-[#1d3c34]">
                                                        <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                        <option value="under_investigation" {{ $report->status === 'under_investigation' ? 'selected' : '' }}>Under Investigation</option>
                                                        <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                                        <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                                                    </select>
                                                </div>
                                                <div class="flex-2">
                                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Resolution Notes</label>
                                                    <input type="text" name="resolution_notes" value="{{ $report->resolution_notes }}" placeholder="Add investigation findings or action taken..." class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-1.5 text-xs text-[#1d3c34]">
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <button type="submit" class="rounded-lg bg-[#102b25] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#1a443b] transition cursor-pointer">
                                                        Save
                                                    </button>
                                                    <button type="button" onclick="toggleCompanyReportPanel('{{ $report->id }}')" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-100 cursor-pointer">
                                                        Cancel
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
            </div>
        @endif
    </div>
</section>

<script>
    function exportExecutiveBranchCsv() {
        const rows = [];
        const table = document.getElementById('executive-branch-matrix-table');
        if (!table) return;

        const headers = Array.from(table.querySelectorAll('thead th')).map(th => `"${th.innerText.trim()}"`);
        rows.push(headers.join(','));

        table.querySelectorAll('tbody tr').forEach(tr => {
            const cols = Array.from(tr.querySelectorAll('td')).map(td => `"${td.innerText.replace(/\n/g, ' ').replace(/\s+/g, ' ').trim()}"`);
            rows.push(cols.join(','));
        });

        const csvContent = "data:text/csv;charset=utf-8," + rows.join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `gtm_real_estate_branch_matrix_${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function toggleCompanyReportPanel(reportId) {
        const panel = document.getElementById('company-report-panel-' + reportId);
        if (panel) {
            panel.classList.toggle('hidden');
        }
    }
</script>
