@extends('dashboards.layout')

@section('title', 'GTM Real Estate Company — Executive Command')
@section('portal_name', 'Company Workspace')

@section('sidebar-nav')
    @php
        $pendingReportsCount = $reports->where('status', 'pending')->count();
    @endphp
    <div class="pt-3 pb-1">
        <p class="px-3 token-sidebar-heading">Marketplace Desks</p>
    </div>
    <x-sidebar-item href="#branches" icon="🏢" label="Branches" :count="$branches->total()" />
    <x-sidebar-item href="#agents" icon="👔" label="Agents" :count="$agents->total()" />
    <x-sidebar-item href="#listings" icon="🏷️" label="Listings" :count="$listings->total()" />
    <x-sidebar-item href="#properties" icon="🏡" label="Properties" :count="$properties->total()" />
    <x-sidebar-item href="#clients" icon="👥" label="Clients" :count="$clients->total()" />
    <x-sidebar-item href="#transactions" icon="💳" label="Transactions" :count="$orders->total() + $rentSchedules->count()" />
    <x-sidebar-item href="#reports" icon="🚨" label="Client Reports" :badge="$pendingReportsCount > 0 ? ($pendingReportsCount . ' Issues') : 'Live'" :alert="$pendingReportsCount > 0" />
    <x-sidebar-item href="#subscription" icon="👑" label="Subscription" :badge="strtoupper($companyWhiteLabel->subscription_tier ?? 'STANDARD')" />
    <x-sidebar-item href="#bank-accounts" icon="🏦" label="Bank Accounts" :count="$bankAccounts->count()" />
@endsection

@section('content')
<div class="space-y-8">
    {{-- Alerts --}}
    @if (session('success'))
        <div class="token-alert token-alert-success text-xs font-bold shadow-xs">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="token-alert token-alert-danger text-xs font-medium">
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
        <div class="token-hero relative overflow-hidden">
            <div class="absolute -right-12 -bottom-12 h-72 w-72 rounded-full bg-[var(--token-accent-light)]/10 blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="token-badge token-badge-accent">GTM Enterprise</span>
                        <span class="token-hero-tag !text-xs">Real Estate Company</span>
                        <span class="text-xs font-semibold text-[var(--token-accent-light)]">Enterprise Hub</span>
                    </div>
                    <h2 class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight token-hero-title font-serif">
                        GTM Real Estate Company
                    </h2>
                    <p class="mt-2 text-xs sm:text-sm token-hero-sub max-w-2xl leading-relaxed">
                        Autonomous Enterprise Command Center: Orchestrating branches, licensed broker networks, market listings, property assets, client CRM relationships, escrow transactions, and executive performance reporting.
                    </p>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-[var(--token-hero-border)] grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                <div onclick="switchCompanySection('branches')" class="cursor-pointer token-hero-stat text-center transition">
                    <span class="block token-hero-stat-label">Branches</span>
                    <span class="block text-2xl font-black token-hero-stat-value mt-0.5">{{ $companyKpis['totalBranches'] }}</span>
                </div>
                <div onclick="switchCompanySection('agents')" class="cursor-pointer token-hero-stat text-center transition">
                    <span class="block token-hero-stat-label">Agents</span>
                    <span class="block text-2xl font-black token-hero-stat-value mt-0.5">{{ $companyKpis['totalAgents'] }}</span>
                </div>
                <div onclick="switchCompanySection('listings')" class="cursor-pointer token-hero-stat text-center transition">
                    <span class="block token-hero-stat-label">Listings</span>
                    <span class="block text-2xl font-black token-hero-stat-value mt-0.5">{{ $companyKpis['totalListings'] }}</span>
                </div>
                <div onclick="switchCompanySection('properties')" class="cursor-pointer token-hero-stat text-center transition">
                    <span class="block token-hero-stat-label">Properties</span>
                    <span class="block text-2xl font-black token-hero-stat-value mt-0.5">{{ $companyKpis['totalProperties'] }}</span>
                </div>
                <div onclick="switchCompanySection('clients')" class="cursor-pointer token-hero-stat text-center transition">
                    <span class="block token-hero-stat-label">Clients</span>
                    <span class="block text-2xl font-black token-hero-stat-value mt-0.5">{{ $companyKpis['totalClients'] }}</span>
                </div>
                <div onclick="switchCompanySection('transactions')" class="cursor-pointer token-hero-stat text-center transition">
                    <span class="block token-hero-stat-label">Transactions</span>
                    <span class="block text-2xl font-black token-hero-stat-value mt-0.5">{{ $orders->count() + $rentSchedules->count() }}</span>
                </div>
                <div onclick="switchCompanySection('reports')" class="cursor-pointer token-hero-stat text-center transition">
                    <span class="block token-hero-stat-label">Client Reports</span>
                    <span class="block text-2xl font-black token-hero-stat-value mt-0.5">{{ $reports->count() }}</span>
                </div>
            </div>
        </div>

        {{-- Highlights Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="token-card">
                <span class="token-stat-title">Asset Portfolio Value</span>
                <p class="!mt-2 token-stat-value token-stat-primary">ETB {{ number_format($companyKpis['portfolioValuation'], 0) }}</p>
                <p class="mt-1 text-xs token-muted">{{ $properties->count() }} active property listings</p>
            </div>
            <div class="token-card">
                <span class="token-stat-title">Gross Deal Turnover</span>
                <p class="!mt-2 token-stat-value token-stat-success">ETB {{ number_format($companyKpis['grossSalesVolume'], 0) }}</p>
                <p class="mt-1 text-xs token-muted">{{ $orders->count() }} sales orders settled</p>
            </div>
            <div class="token-card">
                <span class="token-stat-title">Rental Yield Collected</span>
                <p class="!mt-2 token-stat-value token-stat-warning">ETB {{ number_format($companyKpis['rentCollected'], 0) }}</p>
                <p class="mt-1 text-xs token-muted">{{ $rentSchedules->where('status', 'paid')->count() }} settled rent installments</p>
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
        <section class="token-card !rounded-[1.75rem] md:!p-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[var(--token-border-subtle)] pb-6 gap-4">
                <div>
                    <span class="token-badge token-badge-accent">Broker Workforce</span>
                    <h2 class="mt-1 text-2xl font-black">Licensed Broker &amp; Agent Roster</h2>
                    <p class="mt-1 text-xs token-muted">Manage real estate broker licenses, branch allocations, and commission split ratios.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="token-badge token-badge-neutral">
                        Total Agents: {{ $agents->total() }}
                    </span>
                    <button type="button" onclick="document.getElementById('new-agent-drawer').classList.toggle('hidden')" class="token-btn token-btn-primary transition cursor-pointer">
                        + Add Corporate Agent
                    </button>
                </div>
            </div>

            {{-- New Corporate Agent Drawer --}}
            <div id="new-agent-drawer" class="hidden my-6 rounded-2xl border border-[var(--token-border)] bg-[var(--token-surface-subtle)] p-6 shadow-sm">
                <h3 class="text-sm font-black mb-3">Add / Invite Corporate Agent</h3>
                <form method="POST" action="{{ route('company.agents.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                        <div>
                            <label class="token-label">Agent Full Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Dawit Tadesse" class="mt-1 token-field !text-xs">
                        </div>
                        <div>
                            <label class="token-label">Email Address *</label>
                            <input type="email" name="email" required placeholder="agent@company.com" class="mt-1 token-field !text-xs">
                        </div>
                        <div>
                            <label class="token-label">Phone Number</label>
                            <input type="text" name="phone" placeholder="+251 91 123 4567" class="mt-1 token-field !text-xs">
                        </div>
                        <div>
                            <label class="token-label">Assigned Branch</label>
                            <select name="branch_id" class="mt-1 token-field !text-xs">
                                <option value="">-- HQ Operations --</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="token-label">Commission Split Rate (%)</label>
                            <input type="number" step="0.5" name="commission_rate" value="70.0" min="0" max="100" class="mt-1 token-field !text-xs">
                        </div>
                        <div>
                            <label class="token-label">License Number</label>
                            <input type="text" name="license_number" placeholder="SRE-LIC-0089" class="mt-1 token-field !text-xs">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="document.getElementById('new-agent-drawer').classList.add('hidden')" class="token-btn token-btn-ghost">Cancel</button>
                        <button type="submit" class="token-btn token-btn-primary">Save Agent</button>
                    </div>
                </form>
            </div>

            <div class="mt-6 token-table-wrap">
                <table class="token-table !text-xs">
                    <thead>
                        <tr>
                            <th>Broker Profile</th>
                            <th>Assigned Branch</th>
                            <th>License Number</th>
                            <th class="!text-center">Split Rate</th>
                            <th class="!text-center">Properties</th>
                            <th>Status</th>
                            <th class="!text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($agents as $agent)
                            <tr class="transition">
                                <td class="font-bold">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--token-primary)] text-[var(--token-text-on-color)] font-black text-xs">
                                            {{ strtoupper(substr($agent->name, 0, 2)) }}
                                        </span>
                                        <div>
                                            <p class="font-bold">
                                                <a href="{{ route('agents.show', $agent->id) }}" target="_blank" class="hover:underline hover:text-[var(--token-accent)]">
                                                    {{ $agent->name }} ↗
                                                </a>
                                            </p>
                                            <p class="text-[10px] token-muted">{{ $agent->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-semibold">{{ $agent->branch?->name ?: 'HQ Operations' }}</span>
                                </td>
                                <td class="font-mono font-bold token-muted">
                                    {{ $agent->license_number ?: 'SRE-LIC-'.str_pad($agent->id, 4, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="text-center font-bold text-[var(--token-warning)]">
                                    {{ $agent->commission_rate ?? $agent->split_rate ?? 70 }}%
                                </td>
                                <td class="text-center">
                                    <span class="token-badge token-badge-neutral">
                                        {{ $agent->properties->count() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="token-badge uppercase {{ $agent->license_verified ? 'token-badge-success' : 'token-badge-warning' }}">
                                        {{ $agent->license_verified ? 'Verified Broker' : 'Pending KYC' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right space-x-2">
                                    <form method="POST" action="{{ route('company.agents.toggleActive', $agent) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold {{ $agent->is_active ? 'text-[var(--token-warning)] hover:underline' : 'text-[var(--token-success)] hover:underline' }}">
                                            {{ $agent->is_active ? 'Suspend' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('company.agents.destroy', $agent) }}" onsubmit="return confirm('Remove agent {{ $agent->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="token-btn token-btn-ghost !text-[var(--token-danger)]">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-6 text-center token-muted">No agents registered.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($agents instanceof \Illuminate\Pagination\LengthAwarePaginator && $agents->hasPages())
                <div class="mt-6 border-t border-[var(--token-border-subtle)] pt-4">
                    {{ $agents->fragment('agents')->links() }}
                </div>
            @endif
        </section>
    </div>

    {{-- ═══════════════════ 3. LISTINGS DESK ═══════════════════ --}}
    <div id="listings" data-section="listings" data-company-section="listings" class="section-panel company-section-panel hidden space-y-6">
        @include('dashboard.partials.company-listings-desk')

        {{-- Listing Merge Desk (SRS: properties.merge) --}}
        <div class="token-card">
            <div class="flex items-center justify-between pb-4 border-b border-[var(--token-border-subtle)]">
                <div>
                    <span class="token-badge token-badge-accent">Listing Governance</span>
                    <h3 class="text-base font-black mt-1">Merge Duplicate Listings Desk</h3>
                    <p class="text-xs token-muted">Consolidate multiple duplicate listing submissions into one primary record while preserving orders, inquiries, and offers.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('company.listings.merge') }}" class="mt-4 space-y-4" onsubmit="return confirm('Are you sure you want to merge these listings? All inquiries and offers will be redirected to the primary property.')">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="token-label">Primary Property (To Keep) *</label>
                        <select name="primary_property_id" required class="mt-1 token-field !text-xs font-semibold">
                            <option value="">-- Select Master Property --</option>
                            @foreach ($properties as $prop)
                                <option value="{{ $prop->id }}">#{{ $prop->id }} — {{ $prop->title }} (ETB {{ number_format($prop->price) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="token-label">Duplicate Property (To Archive & Merge) *</label>
                        <select name="duplicate_property_id" required class="mt-1 token-field !text-xs font-semibold">
                            <option value="">-- Select Duplicate Property --</option>
                            @foreach ($properties as $prop)
                                <option value="{{ $prop->id }}">#{{ $prop->id }} — {{ $prop->title }} (ETB {{ number_format($prop->price) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" class="token-btn token-btn-primary transition shadow-xs">
                        Execute Property Merge
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════════════ 4. PROPERTIES DESK (Listing Management Studio) ═══════════════════ --}}
    <div id="properties" data-section="properties" data-company-section="properties" class="section-panel company-section-panel hidden space-y-6">
        <section class="token-card !rounded-[1.75rem] md:!p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-[var(--token-border-subtle)] pb-6">
                <div>
                    <span class="token-badge token-badge-accent">Listing Management</span>
                    <h2 class="mt-1 text-2xl font-black">Company Property Studio</h2>
                    <p class="mt-1 text-xs token-muted">Post new listings, edit details, publish or unpublish, and mark each one available, rented, or sold.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="token-badge token-badge-neutral">
                        Total: {{ $properties->total() }}
                    </span>
                    <span class="token-badge token-badge-success">
                        Live: {{ $properties->where('is_active', true)->count() }}
                    </span>
                    <button type="button" id="company-add-listing-toggle" class="token-btn token-btn-primary transition shadow-sm cursor-pointer" data-label-create="+ New Listing / Property" data-label-close="Close Form">+ New Listing / Property</button>
                </div>
            </div>

            {{-- Collapsible Listing Form --}}
            <div id="company-listing-form" class="mt-6 rounded-2xl border border-[var(--token-border)] bg-[var(--token-surface-subtle)] p-6 shadow-sm hidden">
                <h3 class="text-base font-black mb-4">
                    {{ $companyPropertyToEdit ? 'Edit Property: ' . $companyPropertyToEdit->title : 'Create New Listing / Property' }}
                </h3>
                <form method="POST" action="{{ $companyPropertyToEdit ? route('company.listings.update', $companyPropertyToEdit) : route('company.listings.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @if ($companyPropertyToEdit)
                        @method('PUT')
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                        <div class="sm:col-span-2">
                            <label class="token-label">Property Title *</label>
                            <input type="text" name="title" value="{{ old('title', $companyPropertyToEdit?->title) }}" required placeholder="e.g. Luxury 4-Bedroom Villa in Bole" class="mt-1 token-field !text-xs">
                        </div>
                        <div>
                            <label class="token-label">Price (ETB) *</label>
                            <input type="number" step="1000" name="price" value="{{ old('price', $companyPropertyToEdit?->price) }}" required placeholder="18000000" class="mt-1 token-field !text-xs">
                        </div>
                        <div>
                            <label class="token-label">Type *</label>
                            <select name="type" required class="mt-1 token-field !text-xs">
                                <option value="sale" {{ old('type', $companyPropertyToEdit?->type) === 'sale' ? 'selected' : '' }}>For Sale</option>
                                <option value="rent" {{ old('type', $companyPropertyToEdit?->type) === 'rent' ? 'selected' : '' }}>For Rent</option>
                            </select>
                        </div>
                        <div>
                            <label class="token-label">Category *</label>
                            <select name="category" required class="mt-1 token-field !text-xs">
                                @foreach ($propertyCategories as $cat)
                                    <option value="{{ $cat->slug }}" {{ old('category', $companyPropertyToEdit?->category) === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="token-label">City *</label>
                            <input type="text" name="city" value="{{ old('city', $companyPropertyToEdit?->city ?? 'Addis Ababa') }}" required class="mt-1 token-field !text-xs">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="document.getElementById('company-listing-form').classList.add('hidden')" class="token-btn token-btn-ghost">Cancel</button>
                        <button type="submit" class="token-btn token-btn-primary">Save Property</button>
                    </div>
                </form>
            </div>

            {{-- Properties Table --}}
            <div class="mt-6 token-table-wrap">
                <table class="token-table !text-xs">
                    <thead>
                        <tr>
                            <th>Property Title</th>
                            <th>Type / Category</th>
                            <th>Price</th>
                            <th>Assigned Agent</th>
                            <th>Status</th>
                            <th class="!text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($properties as $property)
                            <tr class="transition">
                                <td class="font-bold">
                                    {{ $property->title }}
                                </td>
                                <td class="uppercase font-semibold token-muted">
                                    {{ $property->type }} &bull; {{ $property->category }}
                                </td>
                                <td class="font-bold text-[var(--token-success)]">
                                    ETB {{ number_format($property->price) }}
                                </td>
                                <td>
                                    {{ $property->agent?->name ?: 'Unassigned' }}
                                </td>
                                <td>
                                    <span class="token-badge uppercase {{ $property->is_active ? 'token-badge-success' : 'token-badge-neutral' }}">
                                        {{ $property->is_active ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        <form method="POST" action="{{ route('company.listings.publish', $property) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="token-btn token-btn-secondary !px-2.5 !py-1 !text-[11px] transition cursor-pointer">
                                                {{ $property->is_active ? 'Unpublish' : 'Publish' }}
                                            </button>
                                        </form>
                                        @foreach (['available', 'rented', 'sold'] as $propertyStatus)
                                            <form method="POST" action="{{ route('company.listings.status', ['property' => $property, 'status' => $propertyStatus]) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="token-btn token-btn-secondary !px-2.5 !py-1 !text-[11px] transition cursor-pointer {{ $property->status === $propertyStatus ? '!bg-[var(--token-accent-surface)] !border-[var(--token-accent-border)] !text-[var(--token-accent-text)]' : '' }}">
                                                    {{ ucfirst($propertyStatus) }}
                                                </button>
                                            </form>
                                        @endforeach
                                        <form method="POST" action="{{ route('company.listings.destroy', $property) }}" onsubmit="return confirm('Delete listing &quot;{{ $property->title }}&quot;?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="token-btn token-btn-danger !px-2.5 !py-1 !text-[11px] transition cursor-pointer">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-sm font-semibold token-muted">
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
        <div class="token-card">
            <h3 class="text-base font-black mb-2">Reassign Client Portfolio</h3>
            <p class="text-xs token-muted mb-4">Reassign high-value buyer or tenant leads to dedicated agents or branch offices.</p>
            <div class="token-table-wrap">
                <table class="token-table !text-xs">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Reassign to Agent</th>
                            <th class="!text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($clients as $client)
                            <tr>
                                <td class="font-bold">{{ $client->name }}</td>
                                <td class="token-muted">{{ $client->email }}</td>
                                <td class="font-semibold uppercase text-[10px]">{{ $client->role }}</td>
                                <td>
                                    <form id="reassign-form-{{ $client->id }}" method="POST" action="{{ route('company.clients.reassign', $client) }}">
                                        @csrf
                                        <select name="agent_id" required class="token-field !px-2.5 !py-1 !text-xs">
                                            <option value="">-- Choose Agent --</option>
                                            @foreach ($agents as $ag)
                                                <option value="{{ $ag->id }}">{{ $ag->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button type="submit" form="reassign-form-{{ $client->id }}" class="token-btn token-btn-primary">
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
        <div class="token-card md:!p-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-[var(--token-border-subtle)] pb-6 gap-4">
                <div>
                    <span class="token-badge token-badge-accent">Enterprise Plan</span>
                    <h2 class="mt-1 text-2xl font-black">Company Subscription &amp; Tier Management</h2>
                    <p class="mt-1 text-xs token-muted">Configure corporate branding white-labeling, agent team limits, and platform fee tiers.</p>
                </div>
                <div>
                    <span class="token-badge token-badge-accent shadow-xs">
                        Current: {{ strtoupper($companyWhiteLabel->subscription_tier ?? 'STANDARD') }}
                    </span>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Standard Tier --}}
                <div class="rounded-2xl border-2 {{ ($companyWhiteLabel->subscription_tier ?? 'standard') === 'standard' ? 'border-[var(--token-primary)] bg-[var(--token-surface-subtle)]' : 'border-[var(--token-border)] bg-[var(--token-surface)]' }} p-6 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold token-muted uppercase">Starter</span>
                        <h3 class="token-section-title !border-b-0 !pb-0 mt-1">Standard</h3>
                        <p class="text-2xl font-black mt-2">Free <span class="text-xs token-muted font-normal">/ month</span></p>
                        <ul class="mt-4 space-y-2 text-xs">
                            <li>✓ Up to 5 agents</li>
                            <li>✓ Standard listing placement</li>
                            <li>✓ Direct messaging & inquiries</li>
                        </ul>
                    </div>
                    <form method="POST" action="{{ route('company.subscription.update') }}" class="mt-6">
                        @csrf
                        <input type="hidden" name="subscription_tier" value="standard">
                        <button type="submit" class="w-full token-btn token-btn-primary {{ ($companyWhiteLabel->subscription_tier ?? 'standard') === 'standard' ? '!bg-[var(--token-border)] !text-[var(--token-text-muted)] cursor-default' : '' }}">
                            {{ ($companyWhiteLabel->subscription_tier ?? 'standard') === 'standard' ? 'Active Plan' : 'Select Standard' }}
                        </button>
                    </form>
                </div>

                {{-- Premium Tier --}}
                <div class="rounded-2xl border-2 {{ ($companyWhiteLabel->subscription_tier ?? '') === 'premium' ? 'border-[var(--token-primary)] bg-[var(--token-surface-subtle)]' : 'border-[var(--token-accent-border)] bg-[var(--token-surface)]' }} p-6 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-[var(--token-warning)] uppercase">Growth</span>
                        <h3 class="token-section-title !border-b-0 !pb-0 mt-1">Premium</h3>
                        <p class="text-2xl font-black mt-2">15,000 ETB <span class="text-xs token-muted font-normal">/ month</span></p>
                        <ul class="mt-4 space-y-2 text-xs">
                            <li>✓ Up to 25 agents</li>
                            <li>✓ Featured listing priority</li>
                            <li>✓ Advanced CRM analytics</li>
                            <li>✓ Automated contract generator</li>
                        </ul>
                    </div>
                    <form method="POST" action="{{ route('company.subscription.update') }}" class="mt-6">
                        @csrf
                        <input type="hidden" name="subscription_tier" value="premium">
                        <button type="submit" class="w-full token-btn token-btn-primary {{ ($companyWhiteLabel->subscription_tier ?? '') === 'premium' ? '!bg-[var(--token-border)] !text-[var(--token-text-muted)] cursor-default' : '' }}">
                            {{ ($companyWhiteLabel->subscription_tier ?? '') === 'premium' ? 'Active Plan' : 'Select Premium' }}
                        </button>
                    </form>
                </div>

                {{-- Enterprise Tier --}}
                <div class="rounded-2xl border-2 {{ ($companyWhiteLabel->subscription_tier ?? '') === 'enterprise' ? 'border-[var(--token-primary)] bg-[var(--token-surface-subtle)]' : 'border-[var(--token-primary)] bg-[var(--token-surface)]' }} p-6 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase">Scale</span>
                        <h3 class="token-section-title !border-b-0 !pb-0 mt-1">Enterprise</h3>
                        <p class="text-2xl font-black mt-2">45,000 ETB <span class="text-xs token-muted font-normal">/ month</span></p>
                        <ul class="mt-4 space-y-2 text-xs">
                            <li>✓ Unlimited agents &amp; branches</li>
                            <li>✓ Custom white-label portal</li>
                            <li>✓ Dedicated API integration</li>
                            <li>✓ VIP 24/7 support</li>
                        </ul>
                    </div>
                    <form method="POST" action="{{ route('company.subscription.update') }}" class="mt-6">
                        @csrf
                        <input type="hidden" name="subscription_tier" value="enterprise">
                        <button type="submit" class="w-full token-btn token-btn-primary {{ ($companyWhiteLabel->subscription_tier ?? '') === 'enterprise' ? '!bg-[var(--token-border)] !text-[var(--token-text-muted)] cursor-default' : '' }}">
                            {{ ($companyWhiteLabel->subscription_tier ?? '') === 'enterprise' ? 'Active Plan' : 'Select Enterprise' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ 9. BANK ACCOUNTS & TREASURY DESK ═══════════════════ --}}
    <div id="bank-accounts" data-section="bank-accounts" data-company-section="bank-accounts" class="section-panel company-section-panel hidden space-y-6">
        @include('dashboard.partials.bank-accounts-desk')
    </div>
</div>

{{-- Universal Ethiopian Payment Modal --}}
@include('properties.partials.payment-modal')

<script>
    const validCompanySections = ['overview', 'branches', 'agents', 'listings', 'properties', 'clients', 'transactions', 'reports', 'subscription', 'bank-accounts'];

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
                l.classList.add('token-nav-link-active');
            } else {
                l.classList.remove('token-nav-link-active');
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
