{{-- Client Reports & Trust Desk Section for Admin Dashboard --}}
<section id="reports" data-section="reports" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded bg-rose-600 px-2.5 py-0.5 text-[11px] font-black uppercase tracking-wider text-white">Trust &amp; Safety</span>
                <h2 class="text-2xl md:text-3xl font-black text-[#1d3c34]">Client Reports &amp; Incidents</h2>
            </div>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">
                Receive and investigate trust &amp; safety scam reports, misleading listings, broker misconduct, and direct client complaints.
            </p>
        </div>

        @php
            $allReports = isset($reports) ? $reports : collect();
            $pendingCount = $allReports->where('status', 'pending')->count();
            $investigatingCount = $allReports->where('status', 'under_investigation')->count();
            $resolvedCount = $allReports->where('status', 'resolved')->count();
            $dismissedCount = $allReports->where('status', 'dismissed')->count();
        @endphp

        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-[#f8f3eb] px-3 py-1 text-xs font-bold text-[#1d3c34] border border-[#ecd9be]">
                Total: {{ $allReports->count() }}
            </span>
            <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800 border border-rose-200">
                Pending: {{ $pendingCount }}
            </span>
            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-800 border border-blue-200">
                Investigating: {{ $investigatingCount }}
            </span>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                Resolved: {{ $resolvedCount }}
            </span>
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#fdfaf5] p-3.5 rounded-2xl border border-[#ebdcc9]">
        <div class="relative flex-1 max-w-md">
            <input type="text" id="dashboard-reports-search" placeholder="Search reports by ID, client, reason, property..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2 pl-9 pr-3 text-xs text-[#102b25] placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
            <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <div class="flex flex-wrap items-center gap-1.5" id="reports-status-filters">
            <button type="button" onclick="filterReportsByStatus('all', this)" class="report-filter-btn active rounded-xl px-3 py-1.5 text-xs font-bold transition bg-[#1d3c34] text-white">
                All ({{ $allReports->count() }})
            </button>
            <button type="button" onclick="filterReportsByStatus('pending', this)" class="report-filter-btn rounded-xl px-3 py-1.5 text-xs font-bold transition bg-white border border-[#d9cab3] text-slate-700 hover:bg-[#fbf6ed]">
                Pending ({{ $pendingCount }})
            </button>
            <button type="button" onclick="filterReportsByStatus('under_investigation', this)" class="report-filter-btn rounded-xl px-3 py-1.5 text-xs font-bold transition bg-white border border-[#d9cab3] text-slate-700 hover:bg-[#fbf6ed]">
                Investigating ({{ $investigatingCount }})
            </button>
            <button type="button" onclick="filterReportsByStatus('resolved', this)" class="report-filter-btn rounded-xl px-3 py-1.5 text-xs font-bold transition bg-white border border-[#d9cab3] text-slate-700 hover:bg-[#fbf6ed]">
                Resolved ({{ $resolvedCount }})
            </button>
            <button type="button" onclick="filterReportsByStatus('dismissed', this)" class="report-filter-btn rounded-xl px-3 py-1.5 text-xs font-bold transition bg-white border border-[#d9cab3] text-slate-700 hover:bg-[#fbf6ed]">
                Dismissed ({{ $dismissedCount }})
            </button>
        </div>
    </div>

    {{-- Reports List --}}
    @if ($allReports->isEmpty())
        <div class="mt-8 rounded-3xl border border-dashed border-[#d9cab3] bg-[#fffdfa] p-12 text-center">
            <span class="text-4xl">🛡️</span>
            <h3 class="mt-3 text-lg font-bold text-[#1d3c34]">No Reports Received Yet</h3>
            <p class="mt-1 text-xs text-slate-500 max-w-md mx-auto">
                When clients report scam properties, inaccurate pricing, fake listings, or direct service complaints, they will be delivered directly here with high priority.
            </p>
        </div>
    @else
        <div class="mt-6 space-y-4" id="reports-list-container">
            @foreach ($allReports as $report)
                @php
                    $statusStyles = match ($report->status) {
                        'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
                        'under_investigation' => 'bg-blue-100 text-blue-800 border-blue-300',
                        'resolved' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                        'dismissed' => 'bg-slate-100 text-slate-700 border-slate-300',
                        default => 'bg-slate-100 text-slate-700 border-slate-300',
                    };

                    $reasonStyles = match ($report->reason) {
                        'scam', 'fake_property' => 'bg-rose-100 text-rose-800 border-rose-200',
                        'wrong_price', 'misleading' => 'bg-amber-100 text-amber-800 border-amber-200',
                        'agent_misconduct' => 'bg-purple-100 text-purple-800 border-purple-200',
                        'service_complaint' => 'bg-blue-100 text-blue-800 border-blue-200',
                        default => 'bg-slate-100 text-slate-800 border-slate-200',
                    };

                    $searchCorpus = strtolower(
                        $report->report_number . ' ' .
                        $report->reason . ' ' .
                        $report->reason_label . ' ' .
                        ($report->subject ?? '') . ' ' .
                        ($report->user?->name ?? '') . ' ' .
                        ($report->contact_email ?? '') . ' ' .
                        ($report->property?->title ?? '') . ' ' .
                        $report->description
                    );
                @endphp

                <div class="report-card rounded-2xl border border-[#d9cab3] bg-white p-5 shadow-xs transition hover:border-[#1d3c34]/50" data-status="{{ $report->status }}" data-search="{{ $searchCorpus }}">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between border-b border-[#f0e4cf] pb-4">
                        {{-- Report Info & Subject --}}
                        <div class="flex items-start gap-3.5">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 text-xl font-bold">
                                🚨
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-xs font-black text-[#102b25] bg-[#102b25]/5 px-2.5 py-0.5 rounded border border-[#102b25]/10">
                                        {{ $report->report_number }}
                                    </span>
                                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider border {{ $statusStyles }}">
                                        {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                    </span>
                                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $reasonStyles }}">
                                        {{ $report->reason_label }}
                                    </span>
                                    <span class="text-[11px] text-slate-400">
                                        {{ $report->created_at->format('M d, Y · h:i A') }} ({{ $report->created_at->diffForHumans() }})
                                    </span>
                                </div>

                                {{-- Subject / Property Header --}}
                                <div class="mt-2">
                                    @if ($report->property)
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-600">Reported Property:</span>
                                            <a href="{{ route('properties.show', $report->property->slug) }}" target="_blank" class="text-sm font-black text-[#1d3c34] hover:underline flex items-center gap-1">
                                                <span>{{ $report->property->title }}</span>
                                                <svg class="h-3 w-3 inline text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                            <span class="text-xs text-slate-500 font-mono">({{ $report->property->property_code }})</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="rounded bg-[#1d3c34] text-[#d4af37] px-2 py-0.5 text-[10px] font-black uppercase">HQ Direct Report</span>
                                            <span class="text-sm font-black text-[#1d3c34]">{{ $report->subject ?: 'General Platform Incident' }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Reporting Client Details --}}
                        <div class="rounded-xl bg-[#fdfaf5] border border-[#ebdcc9] p-3 text-xs lg:min-w-64">
                            <p class="text-[10px] font-black uppercase tracking-wider text-[#8c6b25]">Reported By</p>
                            <p class="font-bold text-[#1d3c34] mt-0.5">{{ $report->user?->name ?: 'Guest / Anonymous' }}</p>
                            @if ($report->contact_email)
                                <p class="text-slate-600 text-[11px] mt-0.5 flex items-center gap-1">
                                    <span>✉️</span>
                                    <a href="mailto:{{ $report->contact_email }}" class="hover:underline text-[#1d3c34] font-medium">{{ $report->contact_email }}</a>
                                </p>
                            @endif
                            @if ($report->user?->phone)
                                <p class="text-slate-600 text-[11px] mt-0.5 flex items-center gap-1">
                                    <span>📞</span>
                                    <span>{{ $report->user->phone }}</span>
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Description & Evidence Blockquote --}}
                    <div class="mt-4 rounded-xl bg-[#fcf9f5] border-l-4 border-rose-500 p-4 text-xs text-slate-700">
                        <p class="font-bold text-[#1d3c34] text-[11px] uppercase tracking-wider mb-1">Client Statement &amp; Evidence:</p>
                        <p class="leading-relaxed whitespace-pre-wrap italic">"{{ $report->description }}"</p>
                    </div>

                    {{-- Resolution Notes (if resolved or investigating) --}}
                    @if ($report->resolution_notes || $report->resolver)
                        <div class="mt-3 rounded-xl bg-slate-50 border border-slate-200 p-3 text-xs text-slate-700">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[#1d3c34]">Admin Resolution Notes:</span>
                                @if ($report->resolver)
                                    <span class="text-[10px] text-slate-500">Handled by {{ $report->resolver->name }}</span>
                                @endif
                            </div>
                            <p class="mt-1 text-slate-600">{{ $report->resolution_notes ?: 'No detailed notes logged.' }}</p>
                        </div>
                    @endif

                    {{-- Action Footer --}}
                    <div class="mt-4 pt-3 border-t border-[#f0e4cf] flex flex-wrap items-center justify-between gap-3">
                        <div class="text-[11px] text-slate-500">
                            Status: <strong class="text-[#1d3c34]">{{ ucfirst(str_replace('_', ' ', $report->status)) }}</strong>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Button to Toggle Resolution Form --}}
                            <button type="button" onclick="toggleReportResolutionPanel('{{ $report->id }}')" class="rounded-xl border border-[#cdbb97] bg-white px-3.5 py-1.5 text-xs font-bold text-[#1d3c34] hover:bg-[#f1e4cf] transition cursor-pointer">
                                ⚙️ Update Status &amp; Notes
                            </button>

                            {{-- Delete Action --}}
                            <form method="POST" action="{{ route('reports.destroy', $report->id) }}" onsubmit="return confirm('Are you sure you want to remove Report #{{ $report->report_number }}?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-xl border border-rose-200 bg-white px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition cursor-pointer">
                                    🗑️ Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Inline Resolution Form (Hidden by default) --}}
                    <div id="report-resolution-panel-{{ $report->id }}" class="hidden mt-4 pt-4 border-t border-dashed border-[#d9cab3] bg-[#fffefb] p-4 rounded-xl border border-[#ebdcc9]">
                        <h4 class="text-xs font-black uppercase tracking-wider text-[#1d3c34] mb-3">Admin Investigation &amp; Resolution Actions</h4>
                        <form method="POST" action="{{ route('reports.update', $report->id) }}" class="space-y-3">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Report Status <span class="text-red-500">*</span></label>
                                    <select name="status" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34] focus:ring-2 focus:ring-[#1d3c34]">
                                        <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>⏳ Pending (Under initial queue)</option>
                                        <option value="under_investigation" {{ $report->status === 'under_investigation' ? 'selected' : '' }}>🔍 Under Investigation</option>
                                        <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>✅ Resolved (Action taken)</option>
                                        <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>❌ Dismissed (False alarm / Invalid)</option>
                                    </select>
                                </div>

                                @if ($report->property)
                                    <div class="flex items-center gap-2 pt-6">
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="suspend_property" value="1" class="rounded border-[#d9cab3] text-rose-600 focus:ring-rose-500">
                                            <span class="text-xs font-bold text-rose-700">🚨 Delist / Archive this property from public portal</span>
                                        </label>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Resolution Findings &amp; Officer Notes</label>
                                <textarea name="resolution_notes" rows="2" placeholder="Record investigation findings, corrective actions taken, or reasons for dismissal..." class="w-full rounded-xl border border-[#d9cab3] bg-white p-2.5 text-xs text-[#1d3c34] focus:ring-2 focus:ring-[#1d3c34]">{{ $report->resolution_notes }}</textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-1">
                                <button type="button" onclick="toggleReportResolutionPanel('{{ $report->id }}')" class="rounded-xl border border-slate-300 px-3.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-100 cursor-pointer">
                                    Cancel
                                </button>
                                <button type="submit" class="rounded-xl bg-[#1d3c34] px-5 py-1.5 text-xs font-bold text-white hover:bg-[#254d43] transition cursor-pointer shadow-xs">
                                    Save Report Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($reports instanceof \Illuminate\Pagination\LengthAwarePaginator && $reports->hasPages())
            <div class="mt-6 border-t border-[#e7ddca] pt-4">
                {{ $reports->fragment('reports')->links() }}
            </div>
        @endif

        <p id="no-matching-reports-msg" class="hidden mt-8 text-center text-xs text-slate-500 italic p-8 rounded-2xl bg-[#fdfaf5] border border-dashed border-[#d9cab3]">
            No reports match your search query or status filter.
        </p>
    @endif
</section>

<script>
    function toggleReportResolutionPanel(reportId) {
        const panel = document.getElementById('report-resolution-panel-' + reportId);
        if (panel) {
            panel.classList.toggle('hidden');
        }
    }

    let currentReportStatusFilter = 'all';

    function filterReportsByStatus(status, btnEl) {
        currentReportStatusFilter = status;
        document.querySelectorAll('.report-filter-btn').forEach(btn => {
            btn.classList.remove('bg-[#1d3c34]', 'text-white');
            btn.classList.add('bg-white', 'border', 'border-[#d9cab3]', 'text-slate-700');
        });

        const activeBtn = btnEl || event?.currentTarget;
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'border', 'border-[#d9cab3]', 'text-slate-700');
            activeBtn.classList.add('bg-[#1d3c34]', 'text-white');
        }

        applyReportsFiltering();
    }

    function applyReportsFiltering() {
        const query = (document.getElementById('dashboard-reports-search')?.value || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.report-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const cardStatus = card.dataset.status;
            const cardSearch = card.dataset.search || '';

            const matchesStatus = (currentReportStatusFilter === 'all') || (cardStatus === currentReportStatusFilter);
            const matchesQuery = !query || cardSearch.includes(query);

            if (matchesStatus && matchesQuery) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noMatchMsg = document.getElementById('no-matching-reports-msg');
        if (noMatchMsg) {
            noMatchMsg.classList.toggle('hidden', visibleCount > 0 || cards.length === 0);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('dashboard-reports-search');
        if (searchInput) {
            searchInput.addEventListener('input', applyReportsFiltering);
        }
    });
</script>
