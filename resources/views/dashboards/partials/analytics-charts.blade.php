@php
    $analyticsService = app(\App\Services\DashboardAnalyticsService::class);
    $analyticsData = $analyticsService->getDashboardData(auth()->user(), request('period', '30d'));
    $perms = $analyticsData['permissions'];
@endphp

<div id="overview-analytics-root" class="space-y-8" data-initial-period="{{ $analyticsData['period'] }}">
    {{-- Header Banner & Date Filter Toolbar --}}
    <div class="token-hero">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="token-hero-chip">
                        @yield('portal_name', ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'Workspace'))) Intelligence
                    </span>
                    <span class="token-hero-tag">
                        Live Analytics
                    </span>
                </div>
                <h1 class="token-hero-title mt-2 text-2xl md:text-3xl">Performance Overview &amp; Insights</h1>
                <p class="token-hero-sub mt-1 text-sm max-w-2xl">
                    Real-time operational metrics, asking-price indicators, portfolio trends, and pipeline activity tailored to your verified access.
                </p>
            </div>

            {{-- Date Filter (7D, 30D, 90D, 1Y) --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <span class="token-hero-sub text-xs font-semibold">Period:</span>
                <div class="token-period-bar" id="analytics-date-filter" role="group" aria-label="Date Range Filter">
                    @foreach (['7d' => '7D', '30d' => '30D', '90d' => '90D', '1y' => '1Y'] as $key => $label)
                        <button type="button"
                                data-period="{{ $key }}"
                                onclick="switchAnalyticsPeriod('{{ $key }}')"
                                class="period-btn token-period-btn {{ $analyticsData['period'] === $key ? 'token-period-btn-active' : '' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Stat Cards Row (Permission-Gated) --}}
        @if (!empty($analyticsData['stat_cards']))
            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 border-t border-[var(--token-hero-border)] pt-6" id="overview-stat-cards">
                @foreach ($analyticsData['stat_cards'] as $card)
                    <div class="token-hero-stat transition">
                        <div class="flex items-center justify-between gap-2">
                            <span class="token-hero-stat-label truncate">{{ $card['label'] }}</span>
                            <span class="text-base">{{ $card['icon'] }}</span>
                        </div>
                        <p class="token-hero-stat-value mt-2 text-2xl stat-value" data-stat-key="{{ $card['key'] }}">{{ $card['value'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Main Chart Visualizations Grid --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- 1. Line Chart: Market Asking-Price Trend (Platform Estimate) --}}
        @if ($perms['market_trends'])
            <div class="chart-card token-chart-card">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[var(--token-border-subtle)] pb-4 mb-4">
                    <div>
                        <h2 class="token-chart-title text-base">
                            {{ $analyticsData['charts']['market_price_trends']['title'] ?? 'Average Asking Price / m²' }}
                        </h2>
                        {{-- MANDATORY LABEL REQUIREMENT --}}
                        <div class="token-badge token-badge-accent mt-1">
                            <span>📈</span>
                            <span>asking-price indicator / platform estimate</span>
                        </div>
                    </div>
                    <span class="token-chart-sub text-[11px] font-semibold">ETB per sq. meter</span>
                </div>

                {{-- Chart skeleton while loading --}}
                <div class="chart-skeleton token-chart-tile min-h-[260px] flex items-center justify-center animate-pulse">
                    <span class="token-muted text-xs font-medium">Loading platform trends...</span>
                </div>

                {{-- Chart Canvas --}}
                <div class="chart-container hidden relative min-h-[260px] w-full">
                    <canvas id="chart-market-price" height="260"></canvas>
                </div>
            </div>
        @endif

        {{-- 2. Line Chart: Platform Activity Trends --}}
        <div class="chart-card token-chart-card">
            <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-4 mb-4">
                <div>
                    <h2 class="token-chart-title text-base">
                        {{ $analyticsData['charts']['activity_trends']['title'] ?? 'Engagement & Interaction Trends' }}
                    </h2>
                    <p class="token-chart-sub text-xs">Views, tours, and inquiries over the selected timeframe</p>
                </div>
                <span class="token-badge token-badge-neutral">
                    Volume
                </span>
            </div>

            <div class="chart-skeleton token-chart-tile min-h-[260px] flex items-center justify-center animate-pulse">
                <span class="token-muted text-xs font-medium">Loading engagement trends...</span>
            </div>

            <div class="chart-container hidden relative min-h-[260px] w-full">
                <canvas id="chart-activity-trends" height="260"></canvas>
            </div>
        </div>

        {{-- 3. Bar Chart: Category Inventory vs Demand Comparisons --}}
        @if (isset($analyticsData['charts']['category_comparisons']))
            <div class="chart-card token-chart-card">
                <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-4 mb-4">
                    <div>
                        <h2 class="token-chart-title text-base">
                            {{ $analyticsData['charts']['category_comparisons']['title'] }}
                        </h2>
                        <p class="token-chart-sub text-xs">Inventory comparison across residential &amp; commercial sectors</p>
                    </div>
                    <span class="token-badge token-badge-accent">
                        Breakdown
                    </span>
                </div>

                <div class="chart-skeleton token-chart-tile min-h-[260px] flex items-center justify-center animate-pulse">
                    <span class="token-muted text-xs font-medium">Loading inventory comparisons...</span>
                </div>

                <div class="chart-container hidden relative min-h-[260px] w-full">
                    <canvas id="chart-category-comparisons" height="260"></canvas>
                </div>
            </div>
        @endif

        {{-- 4. Donut Chart (STRICT REQUIREMENT: MAX 5 SLICES FOR SHARES!) --}}
        @if (isset($analyticsData['charts']['shares_donut']))
            <div class="chart-card token-chart-card flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-4 mb-4">
                        <div>
                            <h2 class="token-chart-title text-base">
                                {{ $analyticsData['charts']['shares_donut']['title'] }}
                            </h2>
                            <p class="token-chart-sub text-xs">Distribution shares (capped at top 5 slices)</p>
                        </div>
                        <span class="token-badge token-badge-neutral">
                            Shares (≤ 5)
                        </span>
                    </div>

                    <div class="chart-skeleton token-chart-tile min-h-[240px] flex items-center justify-center animate-pulse">
                        <span class="token-muted text-xs font-medium">Loading shares...</span>
                    </div>

                    <div class="chart-container hidden relative min-h-[240px] w-full flex items-center justify-center">
                        <canvas id="chart-shares-donut" height="240"></canvas>
                    </div>
                </div>

                {{-- Donut Custom Legend --}}
                <div class="mt-4 pt-3 border-t border-[var(--token-border-subtle)] flex flex-wrap items-center justify-center gap-3 text-xs">
                    @foreach ($analyticsData['charts']['shares_donut']['labels'] as $index => $label)
                        <div class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-full" style="background-color: var(--token-chart-{{ $analyticsData['charts']['shares_donut']['colors'][$index] ?? 'primary' }})"></span>
                            <span class="font-medium">{{ $label }}</span>
                            <span class="font-bold">{{ $analyticsData['charts']['shares_donut']['series'][$index] ?? 0 }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- 5. Agent CRM Funnel (Shown Strictly for CRM / Agent Permissions) --}}
    @if ($perms['agent_crm'] && isset($analyticsData['charts']['agent_crm_funnel']))
        <div class="token-chart-card">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[var(--token-border-subtle)] pb-4 mb-6">
                <div>
                    <h2 class="token-chart-title text-lg">
                        {{ $analyticsData['charts']['agent_crm_funnel']['title'] }}
                    </h2>
                    <p class="token-chart-sub text-xs">Step-by-step conversion pipeline from prospective lead to closed deal</p>
                </div>
                <span class="token-badge token-badge-success">
                    Conversion Pipeline
                </span>
            </div>

            {{-- Funnel Step Cards --}}
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                @foreach ($analyticsData['charts']['agent_crm_funnel']['stages'] as $stage)
                    <div class="token-chart-tile text-center">
                        <span class="token-muted text-xs font-semibold block">{{ $stage['name'] }}</span>
                        <p class="mt-1 text-2xl font-black">{{ $stage['count'] }}</p>
                        <span class="token-badge token-badge-accent mt-1">
                            {{ $stage['conversion'] }} Conv.
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Funnel Horizontal Visualization Canvas --}}
            <div class="mt-6 pt-4 border-t border-[var(--token-border-subtle)]">
                <div class="chart-skeleton token-chart-tile min-h-[160px] flex items-center justify-center animate-pulse">
                    <span class="token-muted text-xs font-medium">Rendering funnel stages...</span>
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
                <div class="token-chart-card">
                    <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-3 mb-4">
                        <div>
                            <h2 class="token-chart-title text-base">
                                {{ $analyticsData['progress_bars']['construction']['title'] }}
                            </h2>
                            <p class="token-chart-sub text-xs">Real-time completion tracking on active project developments</p>
                        </div>
                        <span class="token-badge token-badge-accent">Milestones</span>
                    </div>

                    <div class="space-y-4">
                        @foreach ($analyticsData['progress_bars']['construction']['items'] as $item)
                            <div>
                                <div class="flex items-center justify-between gap-2 text-xs mb-1">
                                    <span class="font-bold">{{ $item['name'] }}</span>
                                    <span class="font-extrabold">{{ $item['percentage'] }}% &bull; {{ $item['status'] }}</span>
                                </div>
                                <div class="h-3 w-full rounded-full bg-[var(--token-surface-hover)] overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-700 {{ $item['color'] === 'emerald' ? 'token-fill-success' : ($item['color'] === 'amber' ? 'token-fill-warning' : 'token-fill-info') }}"
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
                <div class="token-chart-card">
                    <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-3 mb-4">
                        <div>
                            <h2 class="token-chart-title text-base">
                                {{ $occ['title'] }}
                            </h2>
                            <p class="token-chart-sub text-xs">Total units managed vs occupied leases</p>
                        </div>
                        <span class="token-badge token-badge-success">{{ $occ['occupancy_rate'] }}% Target</span>
                    </div>

                    <div class="mt-4">
                        <div class="flex items-center justify-between gap-2 text-xs mb-1.5 font-bold">
                            <span class="token-badge token-badge-success">Occupied: {{ $occ['occupied_units'] }}</span>
                            <span class="token-badge token-badge-warning">Vacant: {{ $occ['vacant_units'] }}</span>
                            <span class="token-badge token-badge-neutral">Total: {{ $occ['total_units'] }} Units</span>
                        </div>
                        <div class="h-4 w-full rounded-full bg-[var(--token-surface-hover)] overflow-hidden flex">
                            <div class="h-full token-fill-success transition-all duration-700" style="width: {{ $occ['occupancy_rate'] }}%;"></div>
                            <div class="h-full token-fill-warning transition-all duration-700" style="width: {{ max(0, 100 - $occ['occupancy_rate']) }}%;"></div>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-[11px] token-muted">
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full token-fill-success"></span>
                                <span>Occupied (Active Lease / Check-in)</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full token-fill-warning"></span>
                                <span>Vacant (Turnover / Marketing)</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- 7. Recent Records Table with Status Badges --}}
    <div class="token-chart-card">
        <div class="flex items-center justify-between gap-3 border-b border-[var(--token-border-subtle)] pb-3 mb-4">
            <div>
                <h2 class="token-chart-title text-base">Recent Records &amp; Activity Log</h2>
                <p class="token-chart-sub text-xs">Most recent actions, transactions, and requests permitted for your profile</p>
            </div>
            <span class="token-badge token-badge-neutral">
                Verified Records
            </span>
        </div>

        @if (!empty($analyticsData['recent_records']))
            <div class="overflow-x-auto">
                <table class="token-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Subject / Title</th>
                            <th>Counterparty / Client</th>
                            <th>Amount / Terms</th>
                            <th>Date</th>
                            <th class="text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($analyticsData['recent_records'] as $record)
                            <tr>
                                <td>
                                    <span class="token-badge token-badge-neutral">
                                        {{ $record['category'] }}
                                    </span>
                                </td>
                                <td class="font-semibold max-w-[220px]">
                                    {{ $record['title'] }}
                                </td>
                                <td class="token-muted">
                                    {{ $record['counterparty'] }}
                                </td>
                                <td class="font-mono font-bold">
                                    {{ $record['amount'] }}
                                </td>
                                <td class="token-muted">
                                    {{ $record['date'] }}
                                </td>
                                <td class="text-right">
                                    <span class="{{ $record['badge']['class'] }}">
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
            <div class="py-10 text-center token-muted">
                <span class="text-4xl block mb-2">📋</span>
                <p class="text-sm font-semibold">No recent records found for this period</p>
                <p class="text-xs mt-0.5">As you create listings, book stays, or submit offers, status logs will appear here.</p>
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

        // ── Token palette helpers: charts resolve colors from CSS variables so
        //    they re-theme automatically in light and dark mode. ──────────────
        function cssVar(name) {
            return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        }

        // Chart palette references arrive from the payload as token names,
        // optionally suffixed with an alpha (e.g. "primary:0.08").
        const CHART_TOKEN_NAMES = ['primary', 'primary-2', 'primary-3', 'warning', 'info', 'success', 'accent', 'danger', 'grid', 'text'];

        function tc(name) {
            return cssVar('--token-chart-' + name);
        }

        function toToken(ref) {
            if (typeof ref !== 'string') return ref;
            const [name, alpha] = ref.trim().split(':');
            if (!CHART_TOKEN_NAMES.includes(name)) return ref;
            const base = tc(name);
            if (alpha !== undefined && alpha !== '') {
                return `color-mix(in srgb, ${base} ${Math.round(parseFloat(alpha) * 100)}%, transparent)`;
            }
            return base;
        }

        function isDarkMode() {
            return document.documentElement.classList.contains('dark');
        }

        function getChartTextColor() {
            return tc('text');
        }

        function getChartGridColor() {
            return tc('grid');
        }

        function adaptDatasets(datasets) {
            if (!datasets || !Array.isArray(datasets)) return datasets;
            return datasets.map(ds => {
                const copy = Object.assign({}, ds);
                if ('borderColor' in copy) copy.borderColor = toToken(copy.borderColor);
                if ('backgroundColor' in copy) copy.backgroundColor = toToken(copy.backgroundColor);
                return copy;
            });
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
                        datasets: adaptDatasets(config.datasets)
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
                        datasets: adaptDatasets(config.datasets)
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
                        datasets: adaptDatasets(config.datasets)
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
                const sliceColors = (config.colors && config.colors.length ? config.colors : ['primary', 'warning', 'info', 'success', 'accent']).map(toToken);
                chartInstances.donut = new Chart(donutCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: config.labels,
                        datasets: [{
                            data: config.series,
                            backgroundColor: sliceColors,
                            borderWidth: 2,
                            borderColor: cssVar('--token-surface')
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
                const funnelColors = funnelConfig.stages.map(s => toToken(s.color || 'primary'));

                chartInstances.funnel = new Chart(funnelCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: counts,
                            backgroundColor: funnelColors,
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
                btn.classList.toggle('token-period-btn-active', btn.dataset.period === periodKey);
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

        window.addEventListener('themechanged', function() {
            initCharts(dashboardData);
        });

        if (window.MutationObserver) {
            const themeObserver = new MutationObserver(function(mutations) {
                for (let m of mutations) {
                    if (m.attributeName === 'class') {
                        initCharts(dashboardData);
                        break;
                    }
                }
            });
            themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootAnalytics);
        } else {
            bootAnalytics();
        }
    })();
</script>
