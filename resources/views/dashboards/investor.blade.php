@extends('dashboards.layout')

@section('title', 'Investor Dashboard')
@section('portal_name', 'Real Estate Investor Workspace')

@section('sidebar-nav')
    @if (auth()->user()->hasAnyPermission(['portfolio.create', 'portfolio.view', 'properties.view']))
        <x-sidebar-item href="#portfolio" icon="📈" label="Portfolio Assets" :count="$portfolios->count()" />
    @endif
    @if (auth()->user()->hasAnyPermission(['yields.view', 'market.view', 'benchmarks.view']))
        <x-sidebar-item href="#benchmarks" icon="🏛️" label="Market Benchmarks" :count="count($marketIndicators)" />
    @endif
@endsection

@section('content')
<div class="space-y-8">
    {{-- Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- Add Asset Modal / Form --}}
    <div id="add-asset-form" class="hidden token-card">
        <h3 class="text-lg font-black mb-3">Add Asset to Portfolio</h3>
        <form method="POST" action="{{ route('investor.portfolios.store') }}" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
            @csrf
            <div>
                <label class="token-label">Asset Name *</label>
                <input type="text" name="asset_name" required placeholder="e.g. Bole Atlas 3BR Luxury Flat" class="token-field mt-1">
            </div>
            <div>
                <label class="token-label">Asset Type *</label>
                <select name="asset_type" required class="token-field mt-1">
                    <option value="residential">Residential</option>
                    <option value="commercial">Commercial</option>
                    <option value="mixed">Mixed-Use</option>
                    <option value="land">Development Land</option>
                </select>
            </div>
            <div>
                <label class="token-label">Location / City *</label>
                <input type="text" name="location" required placeholder="Addis Ababa, Bole" class="token-field mt-1">
            </div>
            <div>
                <label class="token-label">Purchase Price (ETB) *</label>
                <input type="number" name="purchase_price" required placeholder="18500000" class="token-field mt-1">
            </div>
            <div>
                <label class="token-label">Current Valuation (ETB) *</label>
                <input type="number" name="current_valuation" required placeholder="22000000" class="token-field mt-1">
            </div>
            <div>
                <label class="token-label">Monthly Rent Income (ETB)</label>
                <input type="number" name="monthly_rental_income" placeholder="150000" class="token-field mt-1">
            </div>
            <div>
                <label class="token-label">Monthly Expenses (ETB)</label>
                <input type="number" name="monthly_expenses" placeholder="15000" class="token-field mt-1">
            </div>
            <div>
                <label class="token-label">Occupancy Rate (%)</label>
                <input type="number" name="occupancy_rate" value="100" min="0" max="100" class="token-field mt-1">
            </div>
            <div class="sm:col-span-2 md:col-span-3 flex justify-end">
                <button type="submit" class="token-btn token-btn-primary">
                    Save Asset
                </button>
            </div>
        </form>
    </div>

    {{-- 1. Portfolio Assets Table --}}
    <div id="portfolio" data-section="portfolio" class="section-panel hidden scroll-mt-6 token-card">
        <h2 class="token-section-title">My Assets &amp; Yield Breakdown</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="token-table">
                <thead>
                    <tr>
                        <th>Asset</th>
                        <th>Type / Location</th>
                        <th>Valuation (ETB)</th>
                        <th>Monthly Rent</th>
                        <th>Gross Yield (Est.)</th>
                        <th>Net Yield (Est.)</th>
                        <th>Occupancy</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($portfolios as $asset)
                        <tr>
                            <td class="font-bold">{{ $asset->asset_name }}</td>
                            <td class="text-xs token-muted">
                                <span class="token-badge token-badge-neutral font-semibold uppercase !text-[10px]">{{ $asset->asset_type }}</span> &bull; {{ $asset->location }}
                            </td>
                            <td class="font-bold">{{ number_format($asset->current_valuation) }} ETB</td>
                            <td class="font-semibold text-[var(--token-success)]">{{ number_format($asset->monthly_rental_income) }} ETB</td>
                            <td class="font-bold text-[var(--token-warning)]">{{ $asset->gross_yield }}% <span class="text-[9px] font-normal token-muted">(Est.)</span></td>
                            <td class="font-bold text-[var(--token-success)]">{{ $asset->net_yield }}% <span class="text-[9px] font-normal token-muted">(Est.)</span></td>
                            <td class="text-xs font-bold">{{ $asset->occupancy_rate }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-xs token-muted">No assets in portfolio yet. Add your property investments to calculate yields.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 2. Market Indicators & Yield Benchmarks (Labelled as Estimates) --}}
    <div id="benchmarks" data-section="benchmarks" class="section-panel hidden scroll-mt-6 token-card">
        <div class="border-b border-[var(--token-border-subtle)] pb-3">
            <div class="flex items-center gap-2">
                <span class="token-badge token-badge-accent uppercase tracking-wider">Ethiopia Market Intelligence</span>
                <span class="text-xs font-bold text-[var(--token-warning)]">★ All benchmark metrics are estimates</span>
            </div>
            <h2 class="mt-2 text-xl font-black">Market Indicators &amp; Regional Benchmarks (Estimates)</h2>
            <p class="text-xs token-muted">Live district benchmarks derived from Ethiopian transaction averages and rental registry data.</p>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($marketIndicators as $indicator)
                <div class="token-chart-tile flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-sm">{{ $indicator['district'] }}</h4>
                            <span class="token-badge token-badge-success !text-[10px]">{{ $indicator['rental_growth'] }}</span>
                        </div>
                        <p class="text-xs token-muted mt-0.5">{{ $indicator['property_type'] }}</p>

                        <div class="mt-4 grid grid-cols-2 gap-2 text-xs border-t border-[var(--token-border-subtle)] pt-3">
                            <div>
                                <span class="token-muted">Avg. Price / m²:</span>
                                <p class="font-bold text-sm">{{ number_format($indicator['avg_price_sqm']) }} ETB</p>
                            </div>
                            <div>
                                <span class="token-muted">Cap Rate (Est.):</span>
                                <p class="font-bold text-sm text-[var(--token-success)]">{{ $indicator['cap_rate_estimate'] }}%</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-t border-[var(--token-border-subtle)] flex items-center justify-between text-[11px] token-muted">
                        <span>Market Liquidity: <strong>{{ $indicator['liquidity'] }}</strong></span>
                        <span class="text-[10px] italic token-muted">(Market Estimate)</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
