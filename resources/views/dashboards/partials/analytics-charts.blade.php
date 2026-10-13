@php
    $analyticsService = app(\App\Services\DashboardAnalyticsService::class);
    $analyticsData = $analyticsService->getDashboardData(auth()->user(), request('period', '30d'));
    $perms = $analyticsData['permissions'];
@endphp

<div id="overview-analytics-root" class="space-y-8" data-initial-period="{{ $analyticsData['period'] }}">
    {{-- Header Banner & Date Filter Toolbar --}}
    <div class="rounded-3xl border border-[#d9cab3] bg-gradient-to-r from-[#1d3c34] to-[#2d5d4d] p-6 text-white shadow-sm md:p-8 dark:border-slate-800">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-3 py-1 text-[11px] font-bold text-[#f8e7b9] uppercase tracking-wider">
                        @yield('portal_name', ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'Workspace'))) Intelligence
                    </span>
                    <span class="rounded-full bg-white/10 px-2.5 py-0.5 text-[10px] font-semibold text-[#dfeee4]">
                        Live Analytics
                    </span>
                </div>
                <h1 class="mt-2 text-2xl md:text-3xl font-black text-white">Performance Overview &amp; Insights</h1>
                <p class="mt-1 text-sm text-slate-200 max-w-2xl">
                    Real-time operational metrics, asking-price indicators, portfolio trends, and pipeline activity tailored to your verified access.
                </p>
            </div>

            {{-- Date Filter (7D, 30D, 90D, 1Y) --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <span class="text-xs font-semibold text-slate-300">Period:</span>
                <div class="inline-flex rounded-2xl bg-black/20 p-1 border border-white/15 backdrop-blur-xs" id="analytics-date-filter" role="group" aria-label="Date Range Filter">
                    @foreach (['7d' => '7D', '30d' => '30D', '90d' => '90D', '1y' => '1Y'] as $key => $label)
                        <button type="button"
                                data-period="{{ $key }}"
                                onclick="switchAnalyticsPeriod('{{ $key }}')"
                                class="period-btn rounded-xl px-3.5 py-1.5 text-xs font-bold transition cursor-pointer {{ $analyticsData['period'] === $key ? 'bg-[#d4af37] text-[#1d3c34] shadow-xs' : 'text-[#f8f3eb] hover:bg-white/10' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Stat Cards Row (Permission-Gated) --}}
        @if (!empty($analyticsData['stat_cards']))
            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 border-t border-white/20 pt-6" id="overview-stat-cards">
                @foreach ($analyticsData['stat_cards'] as $card)
                    <div class="rounded-2xl bg-white/10 p-4 border border-white/10 backdrop-blur-xs transition hover:bg-white/15">
                        <div class="flex items-center justify-between text-xs text-slate-300">
                            <span class="font-medium truncate">{{ $card['label'] }}</span>
                            <span class="text-base">{{ $card['icon'] }}</span>
                        </div>
                        <p class="mt-2 text-2xl font-black text-white stat-value" data-stat-key="{{ $card['key'] }}">{{ $card['value'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Main Chart Visualizations Grid --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- 1. Line Chart: Market Asking-Price Trend (Platform Estimate) --}}
        @if ($perms['market_trends'])
            <div class="chart-card rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[#e7ddca] pb-4 mb-4 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-black text-[#1d3c34] dark:text-slate-100">
                            {{ $analyticsData['charts']['market_price_trends']['title'] ?? 'Average Asking Price / m²' }}
                        </h2>
                        {{-- MANDATORY LABEL REQUIREMENT --}}
                        <div class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-[#d4af37]/15 border border-[#d4af37]/30 px-2.5 py-0.5 text-[10px] font-bold text-[#9b6c17] dark:text-[#f8e7b9]">
                            <span>📈</span>
                            <span>asking-price indicator / platform estimate</span>
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-400">ETB per sq. meter</span>
                </div>

                {{-- Chart skeleton while loading --}}
                <div class="chart-skeleton min-h-[260px] flex items-center justify-center animate-pulse bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                    <span class="text-xs text-slate-400 font-medium">Loading platform trends...</span>
                </div>

                {{-- Chart Canvas --}}
                <div class="chart-container hidden relative min-h-[260px] w-full">
                    <canvas id="chart-market-price" height="260"></canvas>
                </div>
            </div>
        @endif

        {{-- 2. Line Chart: Platform Activity Trends --}}
        <div class="chart-card rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4 mb-4 dark:border-slate-800">
                <div>
                    <h2 class="text-base font-black text-[#1d3c34] dark:text-slate-100">
                        {{ $analyticsData['charts']['activity_trends']['title'] ?? 'Engagement & Interaction Trends' }}
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Views, tours, and inquiries over the selected timeframe</p>
                </div>
                <span class="rounded-full bg-[#1d3c34]/10 px-2.5 py-1 text-[10px] font-bold text-[#1d3c34] dark:bg-slate-800 dark:text-slate-300">
                    Volume
                </span>
            </div>

            <div class="chart-skeleton min-h-[260px] flex items-center justify-center animate-pulse bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                <span class="text-xs text-slate-400 font-medium">Loading engagement trends...</span>
            </div>

            <div class="chart-container hidden relative min-h-[260px] w-full">
                <canvas id="chart-activity-trends" height="260"></canvas>
            </div>
        </div>

        {{-- 3. Bar Chart: Category Inventory vs Demand Comparisons --}}
        @if (isset($analyticsData['charts']['category_comparisons']))
            <div class="chart-card rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4 mb-4 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-black text-[#1d3c34] dark:text-slate-100">
                            {{ $analyticsData['charts']['category_comparisons']['title'] }}
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Inventory comparison across residential &amp; commercial sectors</p>
                    </div>
                    <span class="rounded-full bg-[#d4af37]/15 px-2.5 py-1 text-[10px] font-bold text-[#9b6c17] dark:text-[#f8e7b9]">
                        Breakdown
                    </span>
                </div>

                <div class="chart-skeleton min-h-[260px] flex items-center justify-center animate-pulse bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                    <span class="text-xs text-slate-400 font-medium">Loading inventory comparisons...</span>
                </div>

                <div class="chart-container hidden relative min-h-[260px] w-full">
                    <canvas id="chart-category-comparisons" height="260"></canvas>
                </div>
            </div>
        @endif

        {{-- 4. Donut Chart (STRICT REQUIREMENT: MAX 5 SLICES FOR SHARES!) --}}
        @if (isset($analyticsData['charts']['shares_donut']))
            <div class="chart-card rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4 mb-4 dark:border-slate-800">
                        <div>
                            <h2 class="text-base font-black text-[#1d3c34] dark:text-slate-100">
                                {{ $analyticsData['charts']['shares_donut']['title'] }}
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Distribution shares (capped at top 5 slices)</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            Shares (≤ 5)
                        </span>
                    </div>

                    <div class="chart-skeleton min-h-[240px] flex items-center justify-center animate-pulse bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                        <span class="text-xs text-slate-400 font-medium">Loading shares...</span>
                    </div>

                    <div class="chart-container hidden relative min-h-[240px] w-full flex items-center justify-center">
                        <canvas id="chart-shares-donut" height="240"></canvas>
                    </div>
                </div>

                {{-- Donut Custom Legend --}}
                <div class="mt-4 pt-3 border-t border-[#e7ddca] dark:border-slate-800 flex flex-wrap items-center justify-center gap-3 text-xs">
                    @foreach ($analyticsData['charts']['shares_donut']['labels'] as $index => $label)
                        <div class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $analyticsData['charts']['shares_donut']['colors'][$index] ?? '#1d3c34' }}"></span>
                            <span class="text-slate-600 dark:text-slate-300 font-medium">{{ $label }}</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">{{ $analyticsData['charts']['shares_donut']['series'][$index] ?? 0 }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- 5. Agent CRM Funnel (Shown Strictly for CRM / Agent Permissions) --}}
    @if ($perms['agent_crm'] && isset($analyticsData['charts']['agent_crm_funnel']))
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#e7ddca] pb-4 mb-6 dark:border-slate-800">
                <div>
                    <h2 class="text-lg font-black text-[#1d3c34] dark:text-slate-100">
                        {{ $analyticsData['charts']['agent_crm_funnel']['title'] }}
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Step-by-step conversion pipeline from prospective lead to closed deal</p>
                </div>
                <span class="rounded-full bg-emerald-100 border border-emerald-200 px-3 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800/40">
                    Conversion Pipeline
                </span>
            </div>

            {{-- Funnel Step Cards --}}
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                @foreach ($analyticsData['charts']['agent_crm_funnel']['stages'] as $stage)
                    <div class="rounded-2xl border border-slate-100 bg-[#fbf8f3] p-4 text-center dark:border-slate-800 dark:bg-slate-800/50">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block">{{ $stage['name'] }}</span>
                        <p class="mt-1 text-2xl font-black text-[#1d3c34] dark:text-slate-100">{{ $stage['count'] }}</p>
                        <span class="mt-1 inline-block rounded-full bg-white px-2 py-0.5 text-[10px] font-bold text-[#b7842d] border border-slate-200 dark:bg-slate-700 dark:border-slate-600 dark:text-amber-300">
                            {{ $stage['conversion'] }} Conv.
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Funnel Horizontal Visualization Canvas --}}
            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                <div class="chart-skeleton min-h-[160px] flex items-center justify-center animate-pulse bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                    <span class="text-xs text-slate-400 font-medium">Rendering funnel stages...</span>
                </div>
                <div class="chart-container hidden relative min-h-[160px] w-full">
                    <canvas id="chart-crm-funnel" height="160"></canvas>
                </div>
            </div>
        </div>
    @endif

    {{-- 6. Progress Bars: Construction Milestones & Occupancy Rate --}}
    @if (!empty($analyticsData['progress_bars']))
        <div class="grid gap-6 {{ isset($analyticsData['progress_bars']['construction'], $analyticsData['progress_bars']['occupancy']) ? 'lg:grid-cols-2' : '' }}">
            {{-- Construction Milestones (Developer / Projects) --}}
            @if (isset($analyticsData['progress_bars']['construction']))
                <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3 mb-4 dark:border-slate-800">
                        <div>
                            <h2 class="text-base font-black text-[#1d3c34] dark:text-slate-100">
                                {{ $analyticsData['progress_bars']['construction']['title'] }}
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Real-time completion tracking on active project developments</p>
                        </div>
                        <span class="text-xs font-bold text-[#d4af37]">Milestones</span>
                    </div>

                    <div class="space-y-4">
                        @foreach ($analyticsData['progress_bars']['construction']['items'] as $item)
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $item['name'] }}</span>
                                    <span class="font-extrabold text-[#1d3c34] dark:text-[#f8e7b9]">{{ $item['percentage'] }}% &bull; {{ $item['status'] }}</span>
                                </div>
                                <div class="h-3 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-700 {{ $item['color'] === 'emerald' ? 'bg-emerald-600' : ($item['color'] === 'amber' ? 'bg-[#d4af37]' : 'bg-sky-600') }}"
                                         style="width: {{ $item['percentage'] }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Occupancy Progress (Property Manager / Landlord) --}}
            @if (isset($analyticsData['progress_bars']['occupancy']))
                @php $occ = $analyticsData['progress_bars']['occupancy']; @endphp
                <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3 mb-4 dark:border-slate-800">
                        <div>
                            <h2 class="text-base font-black text-[#1d3c34] dark:text-slate-100">
                                {{ $occ['title'] }}
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Total units managed vs occupied leases</p>
                        </div>
                        <span class="text-xs font-bold text-emerald-600">{{ $occ['occupancy_rate'] }}% Target</span>
                    </div>

                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs mb-1.5 font-bold">
                            <span class="text-emerald-700 dark:text-emerald-400">Occupied: {{ $occ['occupied_units'] }}</span>
                            <span class="text-amber-700 dark:text-amber-400">Vacant: {{ $occ['vacant_units'] }}</span>
                            <span class="text-slate-700 dark:text-slate-300">Total: {{ $occ['total_units'] }} Units</span>
                        </div>
                        <div class="h-4 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden flex">
                            <div class="h-full bg-emerald-600 transition-all duration-700" style="width: {{ $occ['occupancy_rate'] }}%;"></div>
                            <div class="h-full bg-amber-400 transition-all duration-700" style="width: {{ max(0, 100 - $occ['occupancy_rate']) }}%;"></div>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                                <span>Occupied (Active Lease / Check-in)</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                                <span>Vacant (Turnover / Marketing)</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- 7. Recent Records Table with Status Badges --}}
    <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3 mb-4 dark:border-slate-800">
            <div>
                <h2 class="text-base font-black text-[#1d3c34] dark:text-slate-100">Recent Records &amp; Activity Log</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Most recent actions, transactions, and requests permitted for your profile</p>
            </div>
            <span class="rounded-full bg-[#1d3c34]/10 px-2.5 py-0.5 text-xs font-bold text-[#1d3c34] dark:bg-slate-800 dark:text-slate-300">
                Verified Records
            </span>
        </div>

        @if (!empty($analyticsData['recent_records']))
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#e7ddca] text-slate-400 uppercase tracking-wider text-[10px] dark:border-slate-800">
                            <th class="py-2.5 px-3">Type</th>
                            <th class="py-2.5 px-3">Subject / Title</th>
                            <th class="py-2.5 px-3">Counterparty / Client</th>
                            <th class="py-2.5 px-3">Amount / Terms</th>
                            <th class="py-2.5 px-3">Date</th>
                            <th class="py-2.5 px-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f2ebd9] dark:divide-slate-800/60">
                        @foreach ($analyticsData['recent_records'] as $record)
                            <tr class="hover:bg-[#fbf8f3] dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-3">
                                    <span class="inline-block rounded-md bg-slate-100 px-2 py-0.5 font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        {{ $record['category'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-semibold text-slate-900 dark:text-slate-100 max-w-[220px] truncate">
                                    {{ $record['title'] }}
                                </td>
                                <td class="py-3 px-3 text-slate-600 dark:text-slate-400">
                                    {{ $record['counterparty'] }}
                                </td>
                                <td class="py-3 px-3 font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $record['amount'] }}
                                </td>
                                <td class="py-3 px-3 text-slate-500 dark:text-slate-400">
                                    {{ $record['date'] }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <span class="inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $record['badge']['class'] }}">
                                        {{ $record['badge']['text'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            {{-- Empty State --}}
            <div class="py-10 text-center text-slate-400">
                <span class="text-4xl block mb-2">📋</span>
                <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">No recent records found for this period</p>
                <p class="text-xs text-slate-400 mt-0.5">As you create listings, book stays, or submit offers, status logs will appear here.</p>
            </div>
        @endif
    </div>
</div>

{{-- Inline Data Payload for Instant 0ms Chart Rendering --}}
<script id="analytics-initial-data" type="application/json">
    {!! json_encode($analyticsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
</script>

<script>
    (function() {
        const rawDataEl = document.getElementById('analytics-initial-data');
        if (!rawDataEl) return;

        let dashboardData = JSON.parse(rawDataEl.textContent || '{}');
        const chartInstances = {};

        function isDarkMode() {
            return document.documentElement.classList.contains('dark');
        }

        function getChartTextColor() {
            return isDarkMode() ? '#94a3b8' : '#64748b';
        }

        function getChartGridColor() {
            return isDarkMode() ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';
        }

        function destroyAllCharts() {
            Object.keys(chartInstances).forEach(key => {
                if (chartInstances[key]) {
                    chartInstances[key].destroy();
                    delete chartInstances[key];
                }
            });
        }

        function initCharts(data) {
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js is loading or not available.');
                return;
            }

            destroyAllCharts();

            const charts = data.charts || {};
            const textColor = getChartTextColor();
            const gridColor = getChartGridColor();

            // 1. Market Asking Price Trends Line Chart
            const priceCanvas = document.getElementById('chart-market-price');
            if (priceCanvas && charts.market_price_trends) {
                const config = charts.market_price_trends;
                chartInstances.marketPrice = new Chart(priceCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: config.labels,
                        datasets: config.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: {
                                labels: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 11 } }
                            },
                            tooltip: {
                                padding: 10,
                                cornerRadius: 8
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: gridColor },
                                ticks: { color: textColor, font: { size: 11 } }
                            },
                            y: {
                                grid: { color: gridColor },
                                ticks: {
                                    color: textColor,
                                    font: { size: 11 },
                                    callback: function(v) { return v >= 1000 ? (v / 1000) + 'k' : v; }
                                }
                            }
                        }
                    }
                });
            }

            // 2. Activity Trends Line Chart
            const actCanvas = document.getElementById('chart-activity-trends');
            if (actCanvas && charts.activity_trends) {
                const config = charts.activity_trends;
                chartInstances.activity = new Chart(actCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: config.labels,
                        datasets: config.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: {
                                labels: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 11 } }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: gridColor },
                                ticks: { color: textColor, font: { size: 11 } }
                            },
                            y: {
                                grid: { color: gridColor },
                                ticks: { color: textColor, font: { size: 11 } }
                            }
                        }
                    }
                });
            }

            // 3. Category Comparisons Bar Chart
            const catCanvas = document.getElementById('chart-category-comparisons');
            if (catCanvas && charts.category_comparisons) {
                const config = charts.category_comparisons;
                chartInstances.categories = new Chart(catCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: config.labels,
                        datasets: config.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 11 } }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: textColor, font: { size: 11 } }
                            },
                            y: {
                                grid: { color: gridColor },
                                ticks: { color: textColor, font: { size: 11 } }
                            }
                        }
                    }
                });
            }

            // 4. Shares Donut Chart (Max 5 Slices)
            const donutCanvas = document.getElementById('chart-shares-donut');
            if (donutCanvas && charts.shares_donut) {
                const config = charts.shares_donut;
                chartInstances.donut = new Chart(donutCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: config.labels,
                        datasets: [{
                            data: config.series,
                            backgroundColor: config.colors,
                            borderWidth: 2,
                            borderColor: isDarkMode() ? '#0f172a' : '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) { return ' ' + ctx.label + ': ' + ctx.parsed + '%'; }
                                }
                            }
                        }
                    }
                });
            }

            // 5. Agent CRM Funnel Horizontal Bar
            const funnelCanvas = document.getElementById('chart-crm-funnel');
            if (funnelCanvas && charts.agent_crm_funnel) {
                const funnelConfig = charts.agent_crm_funnel;
                const labels = funnelConfig.stages.map(s => s.name);
                const counts = funnelConfig.stages.map(s => s.count);
                const colors = funnelConfig.stages.map(s => s.color);

                chartInstances.funnel = new Chart(funnelCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: counts,
                            backgroundColor: colors,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                grid: { color: gridColor },
                                ticks: { color: textColor, font: { size: 11 } }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: textColor, font: { size: 11, weight: 'bold' } }
                            }
                        }
                    }
                });
            }

            // Reveal all charts and hide loading skeletons
            document.querySelectorAll('#overview-analytics-root .chart-skeleton').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('#overview-analytics-root .chart-container').forEach(el => el.classList.remove('hidden'));
        }

        window.switchAnalyticsPeriod = function(periodKey) {
            // Update button styles
            document.querySelectorAll('#analytics-date-filter .period-btn').forEach(btn => {
                const isActive = btn.dataset.period === periodKey;
                btn.classList.toggle('bg-[#d4af37]', isActive);
                btn.classList.toggle('text-[#1d3c34]', isActive);
                btn.classList.toggle('shadow-xs', isActive);
                btn.classList.toggle('text-[#f8f3eb]', !isActive);
            });

            // Instant switch if precalculated payload exists
            if (dashboardData.periods_data && dashboardData.periods_data[periodKey]) {
                const pData = dashboardData.periods_data[periodKey];
                if (pData.stat_cards) {
                    pData.stat_cards.forEach(card => {
                        const valEl = document.querySelector(`[data-stat-key="${card.key}"]`);
                        if (valEl) valEl.textContent = card.value;
                    });
                }
                initCharts({ charts: pData.charts });
                return;
            }

            // Fallback fetch via AJAX endpoint
            fetch(`/dashboard/user-overview/charts?period=${periodKey}`)
                .then(r => r.json())
                .then(freshData => {
                    dashboardData = freshData;
                    if (freshData.stat_cards) {
                        freshData.stat_cards.forEach(card => {
                            const valEl = document.querySelector(`[data-stat-key="${card.key}"]`);
                            if (valEl) valEl.textContent = card.value;
                        });
                    }
                    initCharts(freshData);
                })
                .catch(err => console.error('Error fetching analytics period data:', err));
        };

        // Initialize when DOM and Chart are ready
        function bootAnalytics() {
            if (typeof Chart !== 'undefined') {
                initCharts(dashboardData);
            } else {
                // Polling fallback if script is deferred
                const checkInterval = setInterval(function() {
                    if (typeof Chart !== 'undefined') {
                        clearInterval(checkInterval);
                        initCharts(dashboardData);
                    }
                }, 50);
                setTimeout(function() { clearInterval(checkInterval); }, 5000);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootAnalytics);
        } else {
            bootAnalytics();
        }
    })();
</script>
