@extends('dashboards.layout')

@section('title', 'GTM Real Estate Company — Executive Command')
@section('portal_name', 'Company Workspace')

@section('sidebar-nav')
    @php
        $pendingReportsCount = $reports->where('status', 'pending')->count();
    @endphp
    <a href="#overview" data-target-section="overview" class="nav-section-link company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
        <span class="flex items-center gap-2"><span>⚡</span><span>Executive Overview</span></span>
    </a>
    <div class="pt-3 pb-1">
        <p class="px-3 text-[10px] font-black uppercase tracking-[0.2em] text-[#d4af37]">Marketplace Desks</p>
    </div>
    <a href="#branches" data-target-section="branches" class="nav-section-link company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
        <span class="flex items-center gap-2"><span>🏢</span><span>Branches</span></span>
        <span class="rounded-full bg-[#d4af37] px-2 py-0.5 text-[10px] font-black text-[#102b25]">{{ $branches->total() }}</span>
    </a>
    <a href="#agents" data-target-section="agents" class="nav-section-link company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
        <span class="flex items-center gap-2"><span>👔</span><span>Agents</span></span>
        <span class="rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-bold text-white">{{ $agents->total() }}</span>
    </a>
    <a href="#listings" data-target-section="listings" class="nav-section-link company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
        <span class="flex items-center gap-2"><span>🏷️</span><span>Listings</span></span>
        <span class="rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-bold text-white">{{ $listings->total() }}</span>
    </a>
    <a href="#properties" data-target-section="properties" class="nav-section-link company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
        <span class="flex items-center gap-2"><span>🏡</span><span>Properties</span></span>
        <span class="rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-bold text-white">{{ $properties->total() }}</span>
    </a>
    <a href="#clients" data-target-section="clients" class="nav-section-link company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
        <span class="flex items-center gap-2"><span>👥</span><span>Clients</span></span>
        <span class="rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-bold text-white">{{ $clients->total() }}</span>
    </a>
    <a href="#transactions" data-target-section="transactions" class="nav-section-link company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
        <span class="flex items-center gap-2"><span>💳</span><span>Transactions</span></span>
        <span class="rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-bold text-white">{{ $orders->total() + $rentSchedules->count() }}</span>
    </a>
    <a href="#reports" data-target-section="reports" class="nav-section-link company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
        <span class="flex items-center gap-2"><span>🚨</span><span>Client Reports</span></span>
        @if ($pendingReportsCount > 0)
            <span class="rounded-full bg-rose-500/20 border border-rose-400 px-2 py-0.5 text-[10px] font-bold text-rose-300 animate-pulse">{{ $pendingReportsCount }} Issues</span>
        @else
            <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-2 py-0.5 text-[10px] font-bold text-[#d4af37]">Live</span>
        @endif
    </a>
    <a href="#subscription" data-target-section="subscription" class="nav-section-link company-nav-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10">
        <span class="flex items-center gap-2"><span>👑</span><span>Subscription</span></span>
        <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-2 py-0.5 text-[10px] font-black text-[#d4af37]">{{ strtoupper($companyWhiteLabel->subscription_tier ?? 'STANDARD') }}</span>
    </a>
@endsection

@section('content')
<div class="space-y-8">
    {{-- Alerts --}}
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-300 bg-emerald-50 px-5 py-3.5 text-xs font-bold text-emerald-900 shadow-xs">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-xs font-medium text-rose-900">
            <p class="font-bold mb-1">Please address the following:</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ═══════════════════ 0. EXECUTIVE OVERVIEW PANEL ═══════════════════ --}}
    <div id="overview" data-section="overview" data-company-section="overview" class="section-panel company-section-panel space-y-6">
        <div class="relative overflow-hidden rounded-[2rem] border-2 border-[#102b25] bg-gradient-to-br from-[#102b25] via-[#163a31] to-[#0c201c] p-7 md:p-9 text-[#f8f3eb] shadow-xl">
            <div class="absolute -right-12 -bottom-12 h-72 w-72 rounded-full bg-[#d4af37]/10 blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-md bg-[#d4af37] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-widest text-[#102b25]">GTM Enterprise</span>
                        <span class="rounded-full bg-white/10 px-3 py-0.5 text-xs font-semibold text-[#f0e8d8] border border-white/15">Real Estate Company</span>
                        <span class="text-xs font-semibold text-[#d4af37]">Enterprise Hub</span>
                    </div>
                    <h2 class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white font-serif">
                        GTM Real Estate Company
                    </h2>
                    <p class="mt-2 text-xs sm:text-sm text-[#dfeee4] max-w-2xl leading-relaxed">
                        Autonomous Enterprise Command Center: Orchestrating branches, licensed broker networks, market listings, property assets, client CRM relationships, escrow transactions, and executive performance reporting.
                    </p>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-white/10 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                <div onclick="switchCompanySection('branches')" class="cursor-pointer rounded-2xl bg-white/5 p-3 text-center transition hover:bg-white/10 border border-white/10">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Branches</span>
                    <span class="block text-2xl font-black text-white mt-0.5">{{ $companyKpis['totalBranches'] }}</span>
                </div>
                <div onclick="switchCompanySection('agents')" class="cursor-pointer rounded-2xl bg-white/5 p-3 text-center transition hover:bg-white/10 border border-white/10">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Agents</span>
                    <span class="block text-2xl font-black text-white mt-0.5">{{ $companyKpis['totalAgents'] }}</span>
                </div>
                <div onclick="switchCompanySection('listings')" class="cursor-pointer rounded-2xl bg-white/5 p-3 text-center transition hover:bg-white/10 border border-white/10">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Listings</span>
                    <span class="block text-2xl font-black text-white mt-0.5">{{ $companyKpis['totalListings'] }}</span>
                </div>
                <div onclick="switchCompanySection('properties')" class="cursor-pointer rounded-2xl bg-white/5 p-3 text-center transition hover:bg-white/10 border border-white/10">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Properties</span>
                    <span class="block text-2xl font-black text-white mt-0.5">{{ $companyKpis['totalProperties'] }}</span>
                </div>
                <div onclick="switchCompanySection('clients')" class="cursor-pointer rounded-2xl bg-white/5 p-3 text-center transition hover:bg-white/10 border border-white/10">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Clients</span>
                    <span class="block text-2xl font-black text-white mt-0.5">{{ $companyKpis['totalClients'] }}</span>
                </div>
                <div onclick="switchCompanySection('transactions')" class="cursor-pointer rounded-2xl bg-white/5 p-3 text-center transition hover:bg-white/10 border border-white/10">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Transactions</span>
                    <span class="block text-2xl font-black text-white mt-0.5">{{ $orders->count() + $rentSchedules->count() }}</span>
                </div>
                <div onclick="switchCompanySection('reports')" class="cursor-pointer rounded-2xl bg-white/5 p-3 text-center transition hover:bg-white/10 border border-white/10">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Client Reports</span>
                    <span class="block text-2xl font-black text-white mt-0.5">{{ $reports->count() }}</span>
                </div>
            </div>
        </div>

        {{-- Highlights Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-sm">
                <span class="text-[10px] font-black uppercase tracking-wider text-[#587165]">Asset Portfolio Value</span>
                <p class="mt-2 text-2xl font-black text-[#102b25]">ETB {{ number_format($companyKpis['portfolioValuation'], 0) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $properties->count() }} active property listings</p>
            </div>
            <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-sm">
                <span class="text-[10px] font-black uppercase tracking-wider text-[#587165]">Gross Deal Turnover</span>
                <p class="mt-2 text-2xl font-black text-emerald-800">ETB {{ number_format($companyKpis['grossSalesVolume'], 0) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $orders->count() }} sales orders settled</p>
            </div>
            <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-sm">
                <span class="text-[10px] font-black uppercase tracking-wider text-[#587165]">Rental Yield Collected</span>
                <p class="mt-2 text-2xl font-black text-[#9b6c17]">ETB {{ number_format($companyKpis['rentCollected'], 0) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $rentSchedules->where('status', 'paid')->count() }} settled rent installments</p>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- ═══════════════════ 1. BRANCHES DESK ═══════════════════ --}}
    <div id="branches" data-section="branches" data-company-section="branches" class="section-panel company-section-panel hidden">
        @include('dashboard.partials.branches-desk')
    </div>

    {{-- ═══════════════════ 2. AGENTS DESK ═══════════════════ --}}
    <div id="agents" data-section="agents" data-company-section="agents" class="section-panel company-section-panel hidden space-y-6">
        <section class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-6 gap-4">
                <div>
                    <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Broker Workforce</span>
                    <h2 class="mt-1 text-2xl font-black text-[#102b25]">Licensed Broker &amp; Agent Roster</h2>
                    <p class="mt-1 text-xs text-slate-500">Manage real estate broker licenses, branch allocations, and commission split ratios.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-[#fbf8f3] border border-[#d9cab3] px-3.5 py-1 text-xs font-bold text-[#102b25]">
                        Total Agents: {{ $agents->total() }}
                    </span>
                    <button type="button" onclick="document.getElementById('new-agent-drawer').classList.toggle('hidden')" class="rounded-full bg-[#102b25] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#1d3c34] transition cursor-pointer">
                        + Add Corporate Agent
                    </button>
                </div>
            </div>

            {{-- New Corporate Agent Drawer --}}
            <div id="new-agent-drawer" class="hidden my-6 rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-6 shadow-sm">
                <h3 class="text-sm font-black text-[#102b25] mb-3">Add / Invite Corporate Agent</h3>
                <form method="POST" action="{{ route('company.agents.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700">Agent Full Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Dawit Tadesse" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700">Email Address *</label>
                            <input type="email" name="email" required placeholder="agent@company.com" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700">Phone Number</label>
                            <input type="text" name="phone" placeholder="+251 91 123 4567" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700">Assigned Branch</label>
                            <select name="branch_id" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2">
                                <option value="">-- HQ Operations --</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700">Commission Split Rate (%)</label>
                            <input type="number" step="0.5" name="commission_rate" value="70.0" min="0" max="100" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700">License Number</label>
                            <input type="text" name="license_number" placeholder="SRE-LIC-0089" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="document.getElementById('new-agent-drawer').classList.add('hidden')" class="rounded-full px-4 py-1.5 text-xs font-bold text-slate-500">Cancel</button>
                        <button type="submit" class="rounded-full bg-[#102b25] px-5 py-1.5 text-xs font-bold text-white hover:bg-[#1d3c34]">Save Agent</button>
                    </div>
                </form>
            </div>

            <div class="mt-6 overflow-x-auto rounded-2xl border border-[#d9cab3]">
                <table class="w-full border-collapse text-left text-xs text-slate-700">
                    <thead class="bg-[#102b25] text-[#f8f3eb]">
                        <tr>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">Broker Profile</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">Assigned Branch</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">License Number</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px] text-center">Split Rate</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px] text-center">Properties</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">Status</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e4cf]">
                        @forelse ($agents as $agent)
                            <tr class="transition hover:bg-[#fbf8f3]">
                                <td class="px-4 py-3.5 font-bold text-[#102b25]">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#102b25] text-white font-black text-xs">
                                            {{ strtoupper(substr($agent->name, 0, 2)) }}
                                        </span>
                                        <div>
                                            <p class="font-bold text-[#102b25]">
                                                <a href="{{ route('agents.show', $agent->id) }}" target="_blank" class="hover:underline hover:text-[#d4af37]">
                                                    {{ $agent->name }} ↗
                                                </a>
                                            </p>
                                            <p class="text-[10px] text-slate-400">{{ $agent->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-semibold text-slate-700">{{ $agent->branch?->name ?: 'HQ Operations' }}</span>
                                </td>
                                <td class="px-4 py-3.5 font-mono font-bold text-slate-600">
                                    {{ $agent->license_number ?: 'SRE-LIC-'.str_pad($agent->id, 4, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold text-[#9b6c17]">
                                    {{ $agent->commission_rate ?? $agent->split_rate ?? 70 }}%
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="rounded-full bg-[#f4ebe1] px-2.5 py-0.5 font-bold text-[#102b25]">
                                        {{ $agent->properties->count() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $agent->license_verified ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $agent->license_verified ? 'Verified Broker' : 'Pending KYC' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right space-x-2">
                                    <form method="POST" action="{{ route('company.agents.toggleActive', $agent) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold {{ $agent->is_active ? 'text-amber-700 hover:text-amber-900' : 'text-emerald-700 hover:text-emerald-900' }}">
                                            {{ $agent->is_active ? 'Suspend' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('company.agents.destroy', $agent) }}" onsubmit="return confirm('Remove agent {{ $agent->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">No agents registered.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($agents instanceof \Illuminate\Pagination\LengthAwarePaginator && $agents->hasPages())
                <div class="mt-6 border-t border-[#f0e4cf] pt-4">
                    {{ $agents->fragment('agents')->links() }}
                </div>
            @endif
        </section>
    </div>

    {{-- ═══════════════════ 3. LISTINGS DESK ═══════════════════ --}}
    <div id="listings" data-section="listings" data-company-section="listings" class="section-panel company-section-panel hidden space-y-6">
        @include('dashboard.partials.company-listings-desk')

        {{-- Listing Merge Desk (SRS: properties.merge) --}}
        <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-[#f0e4cf]">
                <div>
                    <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Listing Governance</span>
                    <h3 class="text-base font-black text-[#102b25] mt-1">Merge Duplicate Listings Desk</h3>
                    <p class="text-xs text-slate-500">Consolidate multiple duplicate listing submissions into one primary record while preserving orders, inquiries, and offers.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('company.listings.merge') }}" class="mt-4 space-y-4" onsubmit="return confirm('Are you sure you want to merge these listings? All inquiries and offers will be redirected to the primary property.')">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700">Primary Property (To Keep) *</label>
                        <select name="primary_property_id" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 font-semibold">
                            <option value="">-- Select Master Property --</option>
                            @foreach ($properties as $prop)
                                <option value="{{ $prop->id }}">#{{ $prop->id }} — {{ $prop->title }} (ETB {{ number_format($prop->price) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700">Duplicate Property (To Archive & Merge) *</label>
                        <select name="duplicate_property_id" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 font-semibold">
                            <option value="">-- Select Duplicate Property --</option>
                            @foreach ($properties as $prop)
                                <option value="{{ $prop->id }}">#{{ $prop->id }} — {{ $prop->title }} (ETB {{ number_format($prop->price) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" class="rounded-full bg-[#102b25] px-6 py-2 text-xs font-bold text-white hover:bg-[#1d3c34] transition shadow-xs">
                        Execute Property Merge
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════════════ 4. PROPERTIES DESK (Listing Management Studio) ═══════════════════ --}}
    <div id="properties" data-section="properties" data-company-section="properties" class="section-panel company-section-panel hidden space-y-6">
        <section class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-6">
                <div>
                    <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Listing Management</span>
                    <h2 class="mt-1 text-2xl font-black text-[#102b25]">Company Property Studio</h2>
                    <p class="mt-1 text-xs text-slate-500">Post new listings, edit details, publish or unpublish, and mark each one available, rented, or sold.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-[#fbf8f3] border border-[#d9cab3] px-3.5 py-1 text-xs font-bold text-[#102b25]">
                        Total: {{ $properties->total() }}
                    </span>
                    <span class="rounded-full bg-emerald-50 border border-emerald-200 px-3.5 py-1 text-xs font-bold text-emerald-800">
                        Live: {{ $properties->where('is_active', true)->count() }}
                    </span>
                    <button type="button" id="company-add-listing-toggle" class="rounded-full bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white transition hover:bg-[#254d43] shadow-sm cursor-pointer" data-label-create="+ New Listing / Property" data-label-close="Close Form">+ New Listing / Property</button>
                </div>
            </div>

            {{-- Collapsible Listing Form --}}
            <div id="company-listing-form" class="mt-6 rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-6 shadow-sm hidden">
                <h3 class="text-base font-black text-[#102b25] mb-4">
                    {{ $companyPropertyToEdit ? 'Edit Property: ' . $companyPropertyToEdit->title : 'Create New Listing / Property' }}
                </h3>
                <form method="POST" action="{{ $companyPropertyToEdit ? route('company.listings.update', $companyPropertyToEdit) : route('company.listings.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @if ($companyPropertyToEdit)
                        @method('PUT')
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700">Property Title *</label>
                            <input type="text" name="title" value="{{ old('title', $companyPropertyToEdit?->title) }}" required placeholder="e.g. Luxury 4-Bedroom Villa in Bole" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700">Price (ETB) *</label>
                            <input type="number" step="1000" name="price" value="{{ old('price', $companyPropertyToEdit?->price) }}" required placeholder="18000000" class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700">Type *</label>
                            <select name="type" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                                <option value="sale" {{ old('type', $companyPropertyToEdit?->type) === 'sale' ? 'selected' : '' }}>For Sale</option>
                                <option value="rent" {{ old('type', $companyPropertyToEdit?->type) === 'rent' ? 'selected' : '' }}>For Rent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700">Category *</label>
                            <select name="category" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                                @foreach ($propertyCategories as $cat)
                                    <option value="{{ $cat->slug }}" {{ old('category', $companyPropertyToEdit?->category) === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700">City *</label>
                            <input type="text" name="city" value="{{ old('city', $companyPropertyToEdit?->city ?? 'Addis Ababa') }}" required class="mt-1 w-full rounded-xl border border-[#d9cab3] px-3 py-2 bg-white">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="document.getElementById('company-listing-form').classList.add('hidden')" class="rounded-full px-4 py-1.5 text-xs font-bold text-slate-500">Cancel</button>
                        <button type="submit" class="rounded-full bg-[#102b25] px-6 py-2 text-xs font-bold text-white hover:bg-[#1d3c34]">Save Property</button>
                    </div>
                </form>
            </div>

            {{-- Properties Table --}}
            <div class="mt-6 overflow-x-auto rounded-2xl border border-[#d9cab3]">
                <table class="w-full border-collapse text-left text-xs text-slate-700">
                    <thead class="bg-[#102b25] text-[#f8f3eb]">
                        <tr>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">Property Title</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">Type / Category</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">Price</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">Assigned Agent</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">Status</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e4cf]">
                        @forelse ($properties as $property)
                            <tr class="transition hover:bg-[#fbf8f3]">
                                <td class="px-4 py-3.5 font-bold text-[#102b25]">
                                    {{ $property->title }}
                                </td>
                                <td class="px-4 py-3.5 uppercase font-semibold text-slate-600">
                                    {{ $property->type }} &bull; {{ $property->category }}
                                </td>
                                <td class="px-4 py-3.5 font-bold text-emerald-800">
                                    ETB {{ number_format($property->price) }}
                                </td>
                                <td class="px-4 py-3.5">
                                    {{ $property->agent?->name ?: 'Unassigned' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $property->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $property->is_active ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        <form method="POST" action="{{ route('company.listings.publish', $property) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="rounded-lg border border-[#d9cab3] bg-white px-2.5 py-1 text-[11px] font-bold text-[#1d3c34] hover:bg-[#fbf6ed] transition cursor-pointer">
                                                {{ $property->is_active ? 'Unpublish' : 'Publish' }}
                                            </button>
                                        </form>
                                        @foreach (['available', 'rented', 'sold'] as $propertyStatus)
                                            <form method="POST" action="{{ route('company.listings.status', ['property' => $property, 'status' => $propertyStatus]) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-[#d9cab3] px-2.5 py-1 text-[11px] font-bold text-[#1d3c34] transition hover:bg-[#fbf6ed] cursor-pointer {{ $property->status === $propertyStatus ? 'bg-[#f9ecd0] border-[#c5a059]' : 'bg-white' }}">
                                                    {{ ucfirst($propertyStatus) }}
                                                </button>
                                            </form>
                                        @endforeach
                                        <form method="POST" action="{{ route('company.listings.destroy', $property) }}" onsubmit="return confirm('Delete listing &quot;{{ $property->title }}&quot;?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-rose-200 px-2.5 py-1 text-[11px] font-bold text-rose-700 transition hover:bg-rose-50 cursor-pointer">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm font-semibold text-slate-500">
                                    No listings yet — use “+ New Listing / Property” to post your first one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($properties->hasPages())
                <div class="mt-5">{{ $properties->fragment('properties')->links() }}</div>
            @endif
        </section>
    </div>

    {{-- ═══════════════════ 5. CLIENTS CRM DESK ═══════════════════ --}}
    <div id="clients" data-section="clients" data-company-section="clients" class="section-panel company-section-panel hidden space-y-6">
        @include('dashboard.partials.clients-desk')

        {{-- Client Reassignment Desk --}}
        <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-sm">
            <h3 class="text-base font-black text-[#102b25] mb-2">Reassign Client Portfolio</h3>
            <p class="text-xs text-slate-500 mb-4">Reassign high-value buyer or tenant leads to dedicated agents or branch offices.</p>
            <div class="overflow-x-auto rounded-2xl border border-[#e7ddca]">
                <table class="min-w-full divide-y divide-[#e7ddca] text-xs">
                    <thead class="bg-[#fbf9f5] text-left uppercase text-[#587165] font-black">
                        <tr>
                            <th class="py-3 px-4">Client Name</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Role</th>
                            <th class="py-3 px-4">Reassign to Agent</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7ddca]">
                        @foreach ($clients as $client)
                            <tr class="hover:bg-[#fcfaf7]">
                                <td class="py-3 px-4 font-bold text-[#102b25]">{{ $client->name }}</td>
                                <td class="py-3 px-4 text-slate-500">{{ $client->email }}</td>
                                <td class="py-3 px-4 font-semibold uppercase text-[10px]">{{ $client->role }}</td>
                                <td class="py-3 px-4">
                                    <form id="reassign-form-{{ $client->id }}" method="POST" action="{{ route('company.clients.reassign', $client) }}">
                                        @csrf
                                        <select name="agent_id" required class="rounded-xl border border-[#d9cab3] bg-white px-2.5 py-1 text-xs">
                                            <option value="">-- Choose Agent --</option>
                                            @foreach ($agents as $ag)
                                                <option value="{{ $ag->id }}">{{ $ag->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button type="submit" form="reassign-form-{{ $client->id }}" class="rounded-full bg-[#102b25] px-3.5 py-1 text-[11px] font-bold text-white hover:bg-[#1d3c34]">
                                        Reassign
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ 6. TRANSACTIONS DESK ═══════════════════ --}}
    <div id="transactions" data-section="transactions" data-company-section="transactions" class="section-panel company-section-panel hidden">
        @include('dashboard.partials.transactions-desk')
    </div>

    {{-- ═══════════════════ 7. REPORTS DESK ═══════════════════ --}}
    <div id="reports" data-section="reports" data-company-section="reports" class="section-panel company-section-panel hidden">
        @include('dashboard.partials.reports-desk')
    </div>

    {{-- ═══════════════════ 8. SUBSCRIPTION DESK ═══════════════════ --}}
    <div id="subscription" data-section="subscription" data-company-section="subscription" class="section-panel company-section-panel hidden space-y-6">
        <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-6 gap-4">
                <div>
                    <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Enterprise Plan</span>
                    <h2 class="mt-1 text-2xl font-black text-[#102b25]">Company Subscription &amp; Tier Management</h2>
                    <p class="mt-1 text-xs text-slate-500">Configure corporate branding white-labeling, agent team limits, and platform fee tiers.</p>
                </div>
                <div>
                    <span class="rounded-full bg-[#102b25] px-4 py-1.5 text-xs font-black text-[#d4af37] border border-[#d4af37]/40 shadow-xs">
                        Current: {{ strtoupper($companyWhiteLabel->subscription_tier ?? 'STANDARD') }}
                    </span>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Standard Tier --}}
                <div class="rounded-2xl border-2 {{ ($companyWhiteLabel->subscription_tier ?? 'standard') === 'standard' ? 'border-[#102b25] bg-[#fbf8f3]' : 'border-slate-200 bg-white' }} p-6 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase">Starter</span>
                        <h3 class="text-xl font-black text-[#102b25] mt-1">Standard</h3>
                        <p class="text-2xl font-black text-[#102b25] mt-2">Free <span class="text-xs text-slate-400 font-normal">/ month</span></p>
                        <ul class="mt-4 space-y-2 text-xs text-slate-600">
                            <li>✓ Up to 5 agents</li>
                            <li>✓ Standard listing placement</li>
                            <li>✓ Direct messaging & inquiries</li>
                        </ul>
                    </div>
                    <form method="POST" action="{{ route('company.subscription.update') }}" class="mt-6">
                        @csrf
                        <input type="hidden" name="subscription_tier" value="standard">
                        <button type="submit" class="w-full rounded-full py-2 text-xs font-bold {{ ($companyWhiteLabel->subscription_tier ?? 'standard') === 'standard' ? 'bg-slate-300 text-slate-700 cursor-default' : 'bg-[#102b25] text-white hover:bg-[#1d3c34]' }}">
                            {{ ($companyWhiteLabel->subscription_tier ?? 'standard') === 'standard' ? 'Active Plan' : 'Select Standard' }}
                        </button>
                    </form>
                </div>

                {{-- Premium Tier --}}
                <div class="rounded-2xl border-2 {{ ($companyWhiteLabel->subscription_tier ?? '') === 'premium' ? 'border-[#102b25] bg-[#fbf8f3]' : 'border-[#d4af37]/50 bg-white' }} p-6 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-[#9b6c17] uppercase">Growth</span>
                        <h3 class="text-xl font-black text-[#102b25] mt-1">Premium</h3>
                        <p class="text-2xl font-black text-[#102b25] mt-2">15,000 ETB <span class="text-xs text-slate-400 font-normal">/ month</span></p>
                        <ul class="mt-4 space-y-2 text-xs text-slate-600">
                            <li>✓ Up to 25 agents</li>
                            <li>✓ Featured listing priority</li>
                            <li>✓ Advanced CRM analytics</li>
                            <li>✓ Automated contract generator</li>
                        </ul>
                    </div>
                    <form method="POST" action="{{ route('company.subscription.update') }}" class="mt-6">
                        @csrf
                        <input type="hidden" name="subscription_tier" value="premium">
                        <button type="submit" class="w-full rounded-full py-2 text-xs font-bold {{ ($companyWhiteLabel->subscription_tier ?? '') === 'premium' ? 'bg-slate-300 text-slate-700 cursor-default' : 'bg-[#102b25] text-white hover:bg-[#1d3c34]' }}">
                            {{ ($companyWhiteLabel->subscription_tier ?? '') === 'premium' ? 'Active Plan' : 'Select Premium' }}
                        </button>
                    </form>
                </div>

                {{-- Enterprise Tier --}}
                <div class="rounded-2xl border-2 {{ ($companyWhiteLabel->subscription_tier ?? '') === 'enterprise' ? 'border-[#102b25] bg-[#fbf8f3]' : 'border-[#102b25] bg-white' }} p-6 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-[#102b25] uppercase">Scale</span>
                        <h3 class="text-xl font-black text-[#102b25] mt-1">Enterprise</h3>
                        <p class="text-2xl font-black text-[#102b25] mt-2">45,000 ETB <span class="text-xs text-slate-400 font-normal">/ month</span></p>
                        <ul class="mt-4 space-y-2 text-xs text-slate-600">
                            <li>✓ Unlimited agents &amp; branches</li>
                            <li>✓ Custom white-label portal</li>
                            <li>✓ Dedicated API integration</li>
                            <li>✓ VIP 24/7 support</li>
                        </ul>
                    </div>
                    <form method="POST" action="{{ route('company.subscription.update') }}" class="mt-6">
                        @csrf
                        <input type="hidden" name="subscription_tier" value="enterprise">
                        <button type="submit" class="w-full rounded-full py-2 text-xs font-bold {{ ($companyWhiteLabel->subscription_tier ?? '') === 'enterprise' ? 'bg-slate-300 text-slate-700 cursor-default' : 'bg-[#102b25] text-white hover:bg-[#1d3c34]' }}">
                            {{ ($companyWhiteLabel->subscription_tier ?? '') === 'enterprise' ? 'Active Plan' : 'Select Enterprise' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Universal Ethiopian Payment Modal --}}
@include('properties.partials.payment-modal')

<script>
    const validCompanySections = ['overview', 'branches', 'agents', 'listings', 'properties', 'clients', 'transactions', 'reports', 'subscription'];

    function switchCompanySection(sectionName) {
        if (!validCompanySections.includes(sectionName)) {
            return;
        }

        const panels = document.querySelectorAll('.company-section-panel');
        const links = document.querySelectorAll('.company-nav-link');

        panels.forEach(p => {
            if (p.getAttribute('data-company-section') === sectionName) {
                p.classList.remove('hidden');
                p.querySelectorAll('.section-panel').forEach(sp => sp.classList.remove('hidden'));
            } else {
                p.classList.add('hidden');
            }
        });

        links.forEach(l => {
            const target = l.getAttribute('data-target-section');
            if (target === sectionName) {
                l.classList.add('bg-white/15', 'text-white', 'shadow-xs');
                l.classList.remove('text-[#dfeee4]');
            } else {
                l.classList.remove('bg-white/15', 'text-white', 'shadow-xs');
                l.classList.add('text-[#dfeee4]');
            }
        });

        if (window.location.hash !== '#' + sectionName) {
            window.location.hash = sectionName;
        }
    }
    window.switchCompanySection = switchCompanySection;

    document.addEventListener('DOMContentLoaded', function() {
        // Toggle create form panels
        document.querySelectorAll('.toggle-create-form').forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.getAttribute('data-target');
                const panel = document.getElementById(targetId);
                if (!panel) return;

                const isHidden = panel.classList.contains('hidden');
                if (isHidden) {
                    panel.classList.remove('hidden');
                    if (button.dataset.labelClose) {
                        button.textContent = button.dataset.labelClose;
                    }
                } else {
                    panel.classList.add('hidden');
                    if (button.dataset.labelCreate) {
                        button.textContent = button.dataset.labelCreate;
                    }
                }
            });
        });

        // Delegate clicks on company nav links
        document.addEventListener('click', function(e) {
            const targetEl = e.target.closest('[data-target-section]');
            if (targetEl) {
                const targetSection = targetEl.getAttribute('data-target-section');
                if (targetSection && validCompanySections.includes(targetSection)) {
                    e.preventDefault();
                    switchCompanySection(targetSection);
                }
            }
        });

        window.addEventListener('hashchange', function() {
            const section = window.location.hash.replace('#', '');
            if (section && validCompanySections.includes(section)) {
                switchCompanySection(section);
            }
        });

        const urlParams = new URLSearchParams(window.location.search);
        const querySection = urlParams.get('section');
        const initialHash = window.location.hash.replace('#', '') || querySection || 'overview';
        if (validCompanySections.includes(initialHash)) {
            switchCompanySection(initialHash);
        }

        const toggleButton = document.getElementById('company-add-listing-toggle');
        const form = document.getElementById('company-listing-form');
        if (toggleButton && form) {
            toggleButton.addEventListener('click', function () {
                const willShow = form.classList.contains('hidden');
                form.classList.toggle('hidden', !willShow);
            });
        }
    });
</script>
@endsection
