@extends('dashboards.layout')

@section('title', 'Investor Dashboard')
@section('portal_name', 'Real Estate Investor Workspace')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if (auth()->user()->hasAnyPermission(['portfolio.create', 'portfolio.view', 'properties.view']))
        <a href="#portfolio" data-target-section="portfolio" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📈</span>
                <span>Portfolio Assets</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $portfolios->count() }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['yields.view', 'market.view', 'benchmarks.view']))
        <a href="#benchmarks" data-target-section="benchmarks" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🏛️</span>
                <span>Market Benchmarks</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ count($marketIndicators) }}</span>
        </a>
    @endif
@endsection

@section('content')
<div class="space-y-8">
    {{-- Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- Add Asset Modal / Form --}}
    <div id="add-asset-form" class="hidden rounded-3xl border border-[#d4af37] bg-[#fbf8f3] p-6 shadow-sm">
        <h3 class="text-lg font-black text-[#1d3c34] mb-3">Add Asset to Portfolio</h3>
        <form method="POST" action="{{ route('investor.portfolios.store') }}" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700">Asset Name *</label>
                <input type="text" name="asset_name" required placeholder="e.g. Bole Atlas 3BR Luxury Flat" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Asset Type *</label>
                <select name="asset_type" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                    <option value="residential">Residential</option>
                    <option value="commercial">Commercial</option>
                    <option value="mixed">Mixed-Use</option>
                    <option value="land">Development Land</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Location / City *</label>
                <input type="text" name="location" required placeholder="Addis Ababa, Bole" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Purchase Price (ETB) *</label>
                <input type="number" name="purchase_price" required placeholder="18500000" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Current Valuation (ETB) *</label>
                <input type="number" name="current_valuation" required placeholder="22000000" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Monthly Rent Income (ETB)</label>
                <input type="number" name="monthly_rental_income" placeholder="150000" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Monthly Expenses (ETB)</label>
                <input type="number" name="monthly_expenses" placeholder="15000" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Occupancy Rate (%)</label>
                <input type="number" name="occupancy_rate" value="100" min="0" max="100" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
            </div>
            <div class="sm:col-span-2 md:col-span-3 flex justify-end">
                <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white hover:bg-[#254d43]">
                    Save Asset
                </button>
            </div>
        </form>
    </div>

    {{-- 1. Portfolio Assets Table --}}
    <div id="portfolio" data-section="portfolio" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">My Assets &amp; Yield Breakdown</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e7ddca] text-sm">
                <thead>
                    <tr class="bg-[#fbf9f5] text-left text-xs font-black uppercase text-[#587165]">
                        <th class="py-3 px-4">Asset</th>
                        <th class="py-3 px-4">Type / Location</th>
                        <th class="py-3 px-4">Valuation (ETB)</th>
                        <th class="py-3 px-4">Monthly Rent</th>
                        <th class="py-3 px-4">Gross Yield (Est.)</th>
                        <th class="py-3 px-4">Net Yield (Est.)</th>
                        <th class="py-3 px-4">Occupancy</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca]">
                    @forelse ($portfolios as $asset)
                        <tr class="hover:bg-[#fcfaf7]">
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $asset->asset_name }}</td>
                            <td class="py-3 px-4 text-xs text-slate-600">
                                <span class="font-semibold uppercase text-[10px] bg-slate-100 px-1.5 py-0.5 rounded">{{ $asset->asset_type }}</span> &bull; {{ $asset->location }}
                            </td>
                            <td class="py-3 px-4 font-bold text-[#1d3c34]">{{ number_format($asset->current_valuation) }} ETB</td>
                            <td class="py-3 px-4 font-semibold text-emerald-800">{{ number_format($asset->monthly_rental_income) }} ETB</td>
                            <td class="py-3 px-4 font-bold text-amber-700">{{ $asset->gross_yield }}% <span class="text-[9px] font-normal text-slate-400">(Est.)</span></td>
                            <td class="py-3 px-4 font-bold text-emerald-700">{{ $asset->net_yield }}% <span class="text-[9px] font-normal text-slate-400">(Est.)</span></td>
                            <td class="py-3 px-4 text-xs font-bold">{{ $asset->occupancy_rate }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-xs text-slate-400">No assets in portfolio yet. Add your property investments to calculate yields.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 2. Market Indicators & Yield Benchmarks (Labelled as Estimates) --}}
    <div id="benchmarks" data-section="benchmarks" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <div class="border-b border-[#e7ddca] pb-3">
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#d4af37]">Ethiopia Market Intelligence</span>
                <span class="text-xs font-bold text-amber-700">★ All benchmark metrics are estimates</span>
            </div>
            <h2 class="mt-2 text-xl font-black text-[#1d3c34]">Market Indicators &amp; Regional Benchmarks (Estimates)</h2>
            <p class="text-xs text-slate-500">Live district benchmarks derived from Ethiopian transaction averages and rental registry data.</p>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($marketIndicators as $indicator)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-sm text-[#1d3c34]">{{ $indicator['district'] }}</h4>
                            <span class="rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5">{{ $indicator['rental_growth'] }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $indicator['property_type'] }}</p>

                        <div class="mt-4 grid grid-cols-2 gap-2 text-xs border-t border-[#e7ddca] pt-3">
                            <div>
                                <span class="text-slate-400">Avg. Price / m²:</span>
                                <p class="font-bold text-sm text-slate-800">{{ number_format($indicator['avg_price_sqm']) }} ETB</p>
                            </div>
                            <div>
                                <span class="text-slate-400">Cap Rate (Est.):</span>
                                <p class="font-bold text-sm text-emerald-800">{{ $indicator['cap_rate_estimate'] }}%</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-t border-[#e7ddca] flex items-center justify-between text-[11px] text-slate-500">
                        <span>Market Liquidity: <strong class="text-slate-700">{{ $indicator['liquidity'] }}</strong></span>
                        <span class="text-[10px] italic text-slate-400">(Market Estimate)</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
