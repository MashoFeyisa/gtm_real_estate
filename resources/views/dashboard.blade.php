<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f5f0e7] text-slate-800 antialiased scroll-smooth">
    <div class="flex min-h-screen">
        <aside class="w-72 shrink-0 border-r border-[#d9cab3] bg-[#1d3c34] p-6 text-[#f8f3eb]">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset($siteBrand['logo']) }}" alt="{{ $siteBrand['name'] }} Logo" class="h-10 w-10 rounded-xl object-cover ring-2 ring-[#d9cab3]">
                <span class="text-xl font-black tracking-tight text-white">{{ $siteBrand['name'] }}</span>
            </a>

            <nav class="mt-8 space-y-2">
                <a href="{{ route('home') }}" class="flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Home</a>
                <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">Overview</a>
                <a href="#properties" data-target-section="properties" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Properties</a>
                <a href="#orders" data-target-section="orders" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span>Orders</span>
                    @if (isset($unreadAdminRequests) && $unreadAdminRequests->isNotEmpty())
                        <span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-xs font-bold text-[#9b6c17]">{{ $unreadAdminRequests->count() }} new</span>
                    @endif
                </a>
                <a href="#blog-posts" data-target-section="blog-posts" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Blog &amp; News</a>
                <a href="#job-postings" data-target-section="job-postings" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Job Postings</a>
                <a href="#agents" data-target-section="agents" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Agents</a>
                <a href="#testimonials" data-target-section="testimonials" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Client Testimonials</a>
                <a href="#settings" data-target-section="settings" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Site Settings</a>
                <a href="#users" data-target-section="users" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Users</a>
                <a href="#profile" data-target-section="profile" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">My Profile</a>
            </nav>

            <div class="mt-10 rounded-[1.25rem] border border-white/10 bg-white/5 p-4">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#d9cab3]">Signed in</p>
                <p class="mt-3 text-lg font-bold text-white">{{ auth()->user()->name }}</p>
                <p class="text-sm text-[#dfeee4]">Administrator</p>
            </div>
        </aside>

        <main class="flex-1 px-6 py-12">
        <div id="overview" class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between scroll-mt-24">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Control Panel</p>
                <h1 class="mt-2 text-4xl font-black text-[#1d3c34]">Admin Dashboard</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-[#f2e4cb] px-3 py-1 text-sm font-semibold text-[#1d3c34]">{{ auth()->user()->name ?? 'Admin' }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-full border border-[#cdbb97] bg-white px-4 py-2 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f1e4cf]">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="mt-6 rounded-xl border border-[#d7e5d2] bg-[#edf9ee] px-4 py-3 text-sm font-medium text-[#214f3a]">
                {{ session('success') }}
            </div>
        @endif

        <div data-section="overview" class="section-panel">
            @if (isset($unreadAdminRequests) && $unreadAdminRequests->isNotEmpty())
                <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-[#b7842d]/30 bg-[#fef9ec] p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#f9ecd0] text-[#9b6c17]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="font-black text-[#1d3c34]">Agent Agreement Requests Pending Admin Review ({{ $unreadAdminRequests->count() }} new)</h2>
                            <p class="text-xs text-slate-600">Agents have reached agreement with clients and requested admin confirmation and agreement review.</p>
                        </div>
                    </div>
                    <a href="#orders" data-target-section="orders" class="nav-section-link rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                        Review Requests
                    </a>
                </div>
            @endif

            <div class="mt-8 grid gap-6 md:grid-cols-4">
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Total Properties</p>
                    <p class="mt-3 text-3xl font-black text-[#1d3c34]">{{ $overviewStats['totalProperties'] }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#edf3ee] p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Active Listings</p>
                    <p class="mt-3 text-3xl font-black text-[#2d5d4d]">{{ $overviewStats['activeListings'] }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#f4efe7] p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Agents</p>
                    <p class="mt-3 text-3xl font-black text-[#2d5d4d]">{{ $agents->count() }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#f9ebd8] p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Pending Reviews</p>
                    <p class="mt-3 text-3xl font-black text-[#b7842d]">{{ $overviewStats['pendingReviews'] }}</p>
                </div>
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-2">
                <section class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <h2 class="text-2xl font-black text-[#1d3c34]">Recent Properties</h2>
                    <ul class="mt-5 space-y-4">
                        @forelse ($recentProperties as $recent)
                            <li class="flex items-center justify-between border-b border-[#e7ddca] pb-3 last:border-b-0 last:pb-0">
                                <span class="font-medium text-slate-700">{{ $recent->title }}</span>
                                @php
                                    $badge = match ($recent->status) {
                                        'published' => ['Published', 'bg-[#dfeee4] text-[#1d3c34]'],
                                        'sold' => ['Sold', 'bg-[#e9efe9] text-[#2d5d4d]'],
                                        'archived' => ['Archived', 'bg-[#f3ecdb] text-[#9b6c17]'],
                                        'available' => ['Available', 'bg-[#edf2ee] text-[#2d5d4d]'],
                                        default => ['Draft', 'bg-[#f9ecd0] text-[#9b6c17]'],
                                    };
                                @endphp
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $badge[1] }}">{{ $badge[0] }}</span>
                            </li>
                        @empty
                            <li class="text-sm text-slate-500">No properties yet. Create your first listing in the Properties section.</li>
                        @endforelse
                    </ul>
                </section>

            </div>
        </div>

        @php
            $editingProperty = request()->query('edit_property');
            $propertyToEdit = $editingProperty ? $properties->firstWhere('id', (int) $editingProperty) : null;
        @endphp

        <section id="properties" data-section="properties" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-2xl font-black text-[#1d3c34]">Property Management</h2>
                <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">Admin Access</span>
            </div>

            <div class="mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#f9f4ed] p-5">
                @if ($propertyToEdit)
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h3 class="text-xl font-black text-[#1d3c34]">Edit Property</h3>
                        <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-[#2d5d4d]">Cancel</a>
                    </div>
                @else
                    <h3 class="text-xl font-black text-[#1d3c34]">Create Property</h3>
                @endif

                <form method="POST" action="{{ $propertyToEdit ? route('dashboard.properties.update', $propertyToEdit) : route('dashboard.properties.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf
                    @if ($propertyToEdit)
                        @method('PUT')
                    @endif

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                        <input type="text" name="title" value="{{ old('title', $propertyToEdit?->title) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                        <textarea name="description" rows="4" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('description', $propertyToEdit?->description) }}</textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Price</label>
                        <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $propertyToEdit?->price) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Type</label>
                        <select name="type" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="sale" {{ old('type', $propertyToEdit?->type) === 'sale' ? 'selected' : '' }}>Sale</option>
                            <option value="rent" {{ old('type', $propertyToEdit?->type) === 'rent' ? 'selected' : '' }}>Rent</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Category</label>
                        <select name="category" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="home" {{ old('category', $propertyToEdit?->property_category ?? $propertyToEdit?->category) === 'home' ? 'selected' : '' }}>Home</option>
                            <option value="villa" {{ old('category', $propertyToEdit?->property_category ?? $propertyToEdit?->category) === 'villa' ? 'selected' : '' }}>Villa</option>
                            <option value="apartment" {{ old('category', $propertyToEdit?->property_category ?? $propertyToEdit?->category) === 'apartment' ? 'selected' : '' }}>Apartment</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Assigned Agent</label>
                        <select name="agent_id" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="">— No agent (orders go to admin only) —</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}" {{ (string) old('agent_id', $propertyToEdit?->agent_id) === (string) $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Bedrooms</label>
                        <input type="number" name="bedrooms" min="0" value="{{ old('bedrooms', $propertyToEdit?->bedrooms) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Bathrooms</label>
                        <input type="number" name="bathrooms" min="0" value="{{ old('bathrooms', $propertyToEdit?->bathrooms) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Area (sq ft)</label>
                        <input type="number" name="area" min="0" value="{{ old('area', $propertyToEdit?->area) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Status</label>
                        <select name="status" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="draft" {{ old('status', $propertyToEdit?->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $propertyToEdit?->status) === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="available" {{ old('status', $propertyToEdit?->status) === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="sold" {{ old('status', $propertyToEdit?->status) === 'sold' ? 'selected' : '' }}>Sold</option>
                            <option value="archived" {{ old('status', $propertyToEdit?->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">City</label>
                        <input type="text" name="city" value="{{ old('city', $propertyToEdit?->city) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Address</label>
                        <input type="text" name="address" value="{{ old('address', $propertyToEdit?->address) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Property Image</label>
                        <div class="flex items-center gap-3">
                            <label for="property-image-input" class="cursor-pointer rounded-full bg-[#1d3c34] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#254d43]">
                                Property Image
                            </label>
                            <span id="property-image-name" class="truncate text-sm text-slate-500">No file chosen</span>
                        </div>
                        <input id="property-image-input" type="file" name="image" accept="image/*" class="sr-only"
                            onchange="document.getElementById('property-image-name').textContent = this.files.length ? this.files[0].name : 'No file chosen';">
                        @if ($propertyToEdit && $propertyToEdit->image_path)
                            <img src="{{ asset('storage/' . $propertyToEdit->image_path) }}" alt="{{ $propertyToEdit->title }}" class="mt-3 h-28 w-full rounded-xl object-cover shadow-sm ring-1 ring-[#d9cab3]">
                        @endif
                    </div>

                    <div class="md:col-span-2 flex items-center gap-3">
                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <input type="checkbox" name="featured" value="1" {{ old('featured', $propertyToEdit?->featured) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#2d5d4d]">
                            Featured property
                        </label>
                    </div>

                    <div class="md:col-span-2 flex gap-3">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                            {{ $propertyToEdit ? 'Update Property' : 'Create Property' }}
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section id="orders" data-section="orders" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-2xl font-black text-[#1d3c34]">Buy &amp; Rent Orders</h2>
                <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">{{ $orders->count() }} Total</span>
            </div>

            @if (isset($agentRequestsToAdmin) && $agentRequestsToAdmin->isNotEmpty())
                <div class="mt-6 rounded-2xl border border-[#b7842d]/40 bg-[#fffdf7] p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#f9ecd0] text-[#9b6c17]">
                                🔔
                            </span>
                            <div>
                                <h3 class="font-black text-[#1d3c34]">Agent Agreement Requests Pending Admin Action</h3>
                                <p class="text-xs text-slate-600">These agreements were negotiated by agents with clients and submitted to Admin.</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-bold text-[#9b6c17]">{{ $agentRequestsToAdmin->count() }} submitted</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @foreach ($agentRequestsToAdmin as $adminOrder)
                            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-[#e7ddca] bg-white p-4">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-[#dfeee4] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Agent: {{ $adminOrder->agent?->name ?? 'Unassigned' }}</span>
                                        <span class="text-xs font-bold text-slate-400">→</span>
                                        <span class="font-bold text-[#1d3c34]">{{ $adminOrder->name }}</span>
                                        <span class="text-xs text-slate-500">({{ $adminOrder->email }}{{ $adminOrder->phone ? ' · '.$adminOrder->phone : '' }})</span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700">Property: {{ $adminOrder->property?->title ?? 'Removed' }} · {{ $adminOrder->isRental() ? 'Rent: $' : 'Offer: $' }}{{ number_format($adminOrder->offer_amount ?? 0, 2) }}</p>
                                    @if ($adminOrder->agent_note)
                                        <p class="text-xs italic text-slate-600"><span class="font-bold text-[#1d3c34]">Agent Agreement Note:</span> “{{ $adminOrder->agent_note }}”</p>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($adminOrder->hasAgreement())
                                        <a href="{{ route('orders.agreement', $adminOrder) }}" class="rounded-full border border-[#1d3c34] px-3 py-1.5 text-xs font-bold text-[#1d3c34] hover:bg-[#edf2ee]">
                                            Agreement PDF
                                        </a>
                                    @endif
                                    @if ($adminOrder->admin_status !== 'approved')
                                        <form method="POST" action="{{ route('dashboard.orders.review', $adminOrder) }}">
                                            @csrf
                                            <input type="hidden" name="admin_status" value="approved">
                                            <button type="submit" class="rounded-full bg-[#1d3c34] px-4 py-1.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                                Approve Request
                                            </button>
                                        </form>
                                    @else
                                        <span class="rounded-full bg-[#dfeee4] px-3 py-1.5 text-xs font-bold text-[#1d3c34]">Approved by Admin</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($orders->isNotEmpty())
                <div class="mt-6 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca] bg-white">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#2d5d4d] text-[#f8f3eb]">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Client</th>
                                <th class="px-5 py-4 font-semibold">Agent</th>
                                <th class="px-5 py-4 font-semibold">Property</th>
                                <th class="px-5 py-4 font-semibold">Type</th>
                                <th class="px-5 py-4 font-semibold">Status</th>
                                <th class="px-5 py-4 font-semibold">Agreement</th>
                                <th class="px-5 py-4 font-semibold">Requested</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                @php
                                    $orderStatusBadge = match ($order->status) {
                                        'accepted' => ['Accepted', 'bg-[#dfeee4] text-[#1d3c34]'],
                                        'rejected' => ['Rejected', 'bg-[#f7e2e2] text-[#8f3b3b]'],
                                        default => ['Pending', 'bg-[#f9ecd0] text-[#9b6c17]'],
                                    };
                                @endphp
                                <tr class="border-t border-[#e7ddca] align-top">
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-[#1d3c34]">{{ $order->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $order->email }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="font-medium text-slate-700">{{ $order->agent?->name ?? 'None' }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-slate-700">{{ $order->property?->title ?? 'Property removed' }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $order->isRental() ? 'Rent: $'.number_format($order->offer_amount ?? 0, 2).'/mo' : 'Offer: $'.number_format($order->offer_amount ?? 0, 2) }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $order->isRental() ? 'bg-[#dfeee4] text-[#1d3c34]' : 'bg-[#f2e4cb] text-[#1d3c34]' }}">
                                            {{ $order->isRental() ? 'For Rent' : 'For Sale' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $orderStatusBadge[1] }}">{{ $orderStatusBadge[0] }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($order->hasAgreement())
                                            <span class="inline-block rounded-full bg-[#dfeee4] px-2.5 py-1 text-xs font-bold text-[#1d3c34]">
                                                Agreement generated {{ $order->agreed_at?->format('M d, Y') }}
                                            </span>
                                            <a href="{{ route('orders.agreement', $order) }}" class="mt-2 block w-fit rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#254d43]">
                                                Download PDF
                                            </a>
                                        @elseif ($order->status === 'accepted')
                                            <span class="inline-block rounded-full bg-[#f9ecd0] px-2.5 py-1 text-xs font-bold text-[#9b6c17]">Agreement pending</span>
                                        @else
                                            <span class="inline-block rounded-full bg-[#f3ecdb] px-2.5 py-1 text-xs font-bold text-slate-500">No agreement yet</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $order->created_at?->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mt-6 text-sm text-slate-500">No orders yet. Buy and rent requests from clients will appear here.</p>
            @endif
        </section>

        <section id="settings" data-section="settings" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-[#f9f4ed] p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-2xl font-black text-[#1d3c34]">Site Branding &amp; Contact</h2>
                <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">Admin Access</span>
            </div>
            <p class="mt-2 text-sm text-slate-600">These values control the logo, site name, and contact details shown in the header, footer, and homepage.</p>

            <form method="POST" action="{{ route('dashboard.settings.update') }}" enctype="multipart/form-data" class="mt-6 grid gap-4 rounded-[1.25rem] border border-[#e7ddca] bg-white p-5 md:grid-cols-2">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Site Name</label>
                    <input type="text" name="site_name" value="{{ old('site_name', $siteBrand['name']) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Logo</label>
                    <div class="flex items-center gap-3">
                        <label for="site-logo-input" class="cursor-pointer rounded-full bg-[#1d3c34] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#254d43]">
                            Choose Logo
                        </label>
                        <span id="site-logo-name" class="truncate text-sm text-slate-500">Current logo in use</span>
                    </div>
                    <input id="site-logo-input" type="file" name="site_logo" accept="image/*" class="sr-only"
                        onchange="document.getElementById('site-logo-name').textContent = this.files.length ? this.files[0].name : 'Current logo in use';">
                    <img src="{{ asset($siteBrand['logo']) }}" alt="Current logo" class="mt-3 h-12 w-12 rounded-xl object-cover ring-1 ring-[#d9cab3]">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $siteBrand['email']) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $siteBrand['phone']) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Address</label>
                    <input type="text" name="contact_address" value="{{ old('contact_address', $siteBrand['address']) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                </div>

                <div class="md:col-span-2">
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                        Save Settings
                    </button>
                </div>
            </form>
        </section>

        @php
            $editUserId = request()->query('edit_user');
            $userToEdit = $editUserId ? $users->firstWhere('id', (int) $editUserId) : null;
        @endphp

        <section id="users" data-section="users" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-[#f9f4ed] p-6 shadow-sm">
            <h2 class="text-2xl font-black text-[#1d3c34]">User Manager</h2>

            <div class="mt-6 grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
                <div class="overflow-x-auto rounded-[1.25rem] border border-[#e7ddca] bg-white">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#2d5d4d] text-[#f8f3eb]">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Name</th>
                                <th class="px-5 py-4 font-semibold">Email</th>
                                <th class="px-5 py-4 font-semibold">Role</th>
                                <th class="px-5 py-4 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="border-t border-[#e7ddca]">
                                    <td class="px-5 py-4 font-semibold text-[#1d3c34]">{{ $user->name }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $user->email }}</td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full {{ $user->role === 'admin' ? 'bg-[#dfeee4]' : 'bg-[#f9ecd0]' }} px-2.5 py-1 text-xs font-bold {{ $user->role === 'admin' ? 'text-[#1d3c34]' : 'text-[#9b6c17]' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('dashboard', ['edit_user' => $user->id, 'section' => 'users']) }}" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white">Edit</a>
                                            <form method="POST" action="{{ route('dashboard.users.delete', $user) }}" onsubmit="return confirm('Delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1.5 text-xs font-bold text-[#a24339]" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="rounded-[1.25rem] border border-[#d9cab3] bg-white p-5">
                    <h3 class="text-xl font-black text-[#1d3c34]">{{ $userToEdit ? 'Edit User' : 'Add User' }}</h3>
                    <form method="POST" action="{{ $userToEdit ? route('dashboard.users.update', $userToEdit) : route('dashboard.users.store') }}" class="mt-5 space-y-4">
                        @csrf
                        @if ($userToEdit)
                            @method('PUT')
                        @endif
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Name</label>
                            <input type="text" name="name" value="{{ old('name', $userToEdit?->name) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', $userToEdit?->email) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">{{ $userToEdit ? 'New Password (optional)' : 'Password' }}</label>
                            <input type="password" name="password" {{ $userToEdit ? '' : 'required' }} class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Role</label>
                            <select name="role" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                <option value="user" {{ old('role', $userToEdit?->role) === 'user' ? 'selected' : '' }}>User</option>
                                <option value="agent" {{ old('role', $userToEdit?->role) === 'agent' ? 'selected' : '' }}>Agent</option>
                                <option value="admin" {{ old('role', $userToEdit?->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-3">
                            <button type="submit" class="w-full rounded-xl bg-[#1d3c34] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                                {{ $userToEdit ? 'Update User' : 'Save User' }}
                            </button>
                            @if ($userToEdit)
                                <a href="{{ route('dashboard', ['section' => 'users']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-4 py-3 text-center text-sm font-bold text-[#1d3c34]">
                                    Cancel edit
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section id="profile" data-section="profile" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[#1d3c34]">My Admin Profile</h2>
                    <p class="mt-1 text-sm text-slate-600">Update your account name, email address, and login password.</p>
                </div>
                <span class="rounded-full bg-[#dfeee4] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">Administrator</span>
            </div>

            <form method="POST" action="{{ route('dashboard.profile.update') }}" class="mt-6 max-w-xl space-y-4 rounded-[1.25rem] border border-[#e7ddca] bg-[#fdfaf5] p-6">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">New Password (optional)</label>
                    <input type="password" name="password" minlength="6" placeholder="Leave blank to keep your current password" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                </div>

                <div class="pt-2">
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                        Update Profile
                    </button>
                </div>
            </form>
        </section>

        @php
            $editAgentId = request()->query('edit_agent');
            $agentToEdit = $editAgentId ? $agents->firstWhere('id', (int) $editAgentId) : null;
        @endphp

        <section id="agents" data-section="agents" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-2xl font-black text-[#1d3c34]">{{ $agentToEdit ? 'Edit Agent' : 'Agents Management' }}</h2>
                <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">Shown on Home Page</span>
            </div>

            <div class="mt-6 rounded-[1.25rem] border border-[#d9cab3] bg-[#f9f4ed] p-5">
                @if ($agentToEdit)
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h3 class="text-xl font-black text-[#1d3c34]">Editing: {{ $agentToEdit->name }}</h3>
                        <a href="{{ route('dashboard', ['section' => 'agents']) }}" class="text-sm font-semibold text-[#2d5d4d]">Cancel</a>
                    </div>
                @else
                    <h3 class="text-xl font-black text-[#1d3c34]">Add Agent</h3>
                @endif
                <form method="POST" action="{{ $agentToEdit ? route('dashboard.agents.update', $agentToEdit) : route('dashboard.agents.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf
                    @if ($agentToEdit)
                        @method('PUT')
                    @endif
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Name</label>
                        <input type="text" name="name" value="{{ old('name', $agentToEdit?->name) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email', $agentToEdit?->email) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $agentToEdit?->phone) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Title / Specialty (bio)</label>
                        <input type="text" name="bio" value="{{ old('bio', $agentToEdit?->bio) }}" placeholder="e.g. Senior property consultant" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Portal {{ $agentToEdit ? 'New Password (optional)' : 'Password (optional)' }}</label>
                        <input type="password" name="password" minlength="6" placeholder="{{ $agentToEdit ? 'Leave blank to keep current' : 'Leave blank to auto-generate' }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        <p class="mt-1 text-xs text-slate-500">Creates a login account so the agent can sign in to their portal.</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Photo</label>
                        <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        @if ($agentToEdit && $agentToEdit->photo_path)
                            <img src="{{ asset('storage/'.$agentToEdit->photo_path) }}" alt="{{ $agentToEdit->name }}" class="mt-2 h-16 w-16 rounded-full object-cover ring-1 ring-[#d9cab3]">
                        @endif
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                            {{ $agentToEdit ? 'Update Agent' : 'Add Agent' }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-6 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#1d3c34] text-[#f8f3eb]">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Photo</th>
                            <th class="px-5 py-4 font-semibold">Name</th>
                            <th class="px-5 py-4 font-semibold">Email</th>
                            <th class="px-5 py-4 font-semibold">Phone</th>
                            <th class="px-5 py-4 font-semibold">Title / Specialty</th>
                            <th class="px-5 py-4 font-semibold">Portal Account</th>
                            <th class="px-5 py-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($agents as $agent)
                            <tr class="border-t border-[#e7ddca] bg-white">
                                <td class="px-5 py-4">
                                    <img src="{{ $agent->photo_url }}" alt="{{ $agent->name }}" class="h-10 w-10 rounded-full object-cover ring-1 ring-[#d9cab3]">
                                </td>
                                <td class="px-5 py-4 font-semibold text-[#1d3c34]">{{ $agent->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $agent->email }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $agent->phone }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $agent->bio }}</td>
                                <td class="px-5 py-4">
                                    @if ($agent->account)
                                        <span class="rounded-full bg-[#dfeee4] px-2.5 py-1 text-xs font-bold text-[#1d3c34]">Active</span>
                                    @else
                                        <span class="rounded-full bg-[#f9ecd0] px-2.5 py-1 text-xs font-bold text-[#9b6c17]">None</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('dashboard', ['edit_agent' => $agent->id, 'section' => 'agents']) }}" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('dashboard.agents.destroy', $agent) }}" onsubmit="return confirm('Delete this agent?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1.5 text-xs font-bold text-[#a24339]">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="border-t border-[#e7ddca] bg-white">
                                <td colspan="7" class="px-5 py-6 text-center text-slate-500">No agents yet. Add one above to feature them on the home page.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @php
            $editTestimonialId = request()->query('edit_testimonial');
            $testimonialToEdit = $editTestimonialId ? $testimonials->firstWhere('id', (int) $editTestimonialId) : null;
        @endphp

        <section id="testimonials" data-section="testimonials" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[#1d3c34]">{{ $testimonialToEdit ? 'Edit Client Testimonial' : 'Client Testimonials Management' }}</h2>
                    <p class="mt-1 text-sm text-slate-600">Add, edit, approve, delete, and restore client testimonials displayed on the homepage.</p>
                </div>
                <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">{{ $testimonials->whereNull('deleted_at')->count() }} Active</span>
            </div>

            <div class="mt-6 rounded-[1.25rem] border border-[#d9cab3] bg-[#f9f4ed] p-5">
                @if ($testimonialToEdit)
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h3 class="text-xl font-black text-[#1d3c34]">Editing Testimonial from: {{ $testimonialToEdit->name }}</h3>
                        <a href="{{ route('dashboard', ['section' => 'testimonials']) }}" class="text-sm font-semibold text-[#2d5d4d]">Cancel</a>
                    </div>
                @else
                    <h3 class="text-xl font-black text-[#1d3c34]">Add Client Testimonial</h3>
                @endif

                <form method="POST" action="{{ $testimonialToEdit ? route('dashboard.testimonials.update', $testimonialToEdit) : route('dashboard.testimonials.store') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf
                    @if ($testimonialToEdit)
                        @method('PUT')
                    @endif

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Client Name</label>
                        <input type="text" name="name" value="{{ old('name', $testimonialToEdit?->name) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Client Email (optional)</label>
                        <input type="email" name="email" value="{{ old('email', $testimonialToEdit?->email) }}" placeholder="client@example.com" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Assigned Agent (optional)</label>
                        <select name="agent_id" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="">General Agency Testimonial (No specific agent)</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}" {{ (string) old('agent_id', $testimonialToEdit?->agent_id) === (string) $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Rating</label>
                        <select name="rating" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            @foreach ([5 => '★★★★★ (5 Stars)', 4 => '★★★★☆ (4 Stars)', 3 => '★★★☆☆ (3 Stars)', 2 => '★★☆☆☆ (2 Stars)', 1 => '★☆☆☆☆ (1 Star)'] as $val => $label)
                                <option value="{{ $val }}" {{ (int) old('rating', $testimonialToEdit?->rating ?? 5) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Testimonial Message</label>
                        <textarea name="message" rows="3" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('message', $testimonialToEdit?->message) }}</textarea>
                    </div>

                    <div class="md:col-span-2 flex items-center gap-2">
                        <input type="checkbox" id="testimonial-approved" name="is_approved" value="1" {{ old('is_approved', $testimonialToEdit?->is_approved ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#dfeee4]">
                        <label for="testimonial-approved" class="text-sm font-semibold text-slate-700">Approved (Show on home page)</label>
                    </div>

                    <div class="md:col-span-2 flex items-center gap-3">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]">
                            {{ $testimonialToEdit ? 'Update Testimonial' : 'Save Testimonial' }}
                        </button>
                        @if ($testimonialToEdit)
                            <a href="{{ route('dashboard', ['section' => 'testimonials']) }}" class="rounded-full border border-[#d9cab3] bg-white px-5 py-2.5 text-sm font-bold text-[#1d3c34]">
                                Cancel
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Active Testimonials Table --}}
            <div class="mt-8 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#1d3c34] text-[#f8f3eb]">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Client</th>
                            <th class="px-5 py-4 font-semibold">Agent</th>
                            <th class="px-5 py-4 font-semibold">Rating</th>
                            <th class="px-5 py-4 font-semibold">Message</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($testimonials->whereNull('deleted_at') as $item)
                            <tr class="border-t border-[#e7ddca] bg-white">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-[#1d3c34]">{{ $item->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->email }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-700">
                                    {{ $item->agent?->name ?? 'General / Agency' }}
                                </td>
                                <td class="px-5 py-4 font-bold text-[#9b6c17]">
                                    {{ str_repeat('★', $item->rating) }}
                                </td>
                                <td class="max-w-xs px-5 py-4 text-slate-600">
                                    <p class="line-clamp-2 text-xs leading-5">“{{ $item->message }}”</p>
                                </td>
                                <td class="px-5 py-4">
                                    <form method="POST" action="{{ route('dashboard.testimonials.toggleApproval', $item) }}">
                                        @csrf
                                        <button type="submit" class="rounded-full px-2.5 py-1 text-xs font-bold {{ $item->is_approved ? 'bg-[#dfeee4] text-[#1d3c34]' : 'bg-[#f9ecd0] text-[#9b6c17]' }}">
                                            {{ $item->is_approved ? 'Approved' : 'Pending' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('dashboard', ['edit_testimonial' => $item->id, 'section' => 'testimonials']) }}" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('dashboard.testimonials.destroy', $item) }}" onsubmit="return confirm('Delete this testimonial? You can restore it later.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1.5 text-xs font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="border-t border-[#e7ddca] bg-white">
                                <td colspan="6" class="px-5 py-6 text-center text-slate-500">No active testimonials yet. Add one above.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Trashed / Deleted Testimonials Table with Restore Action --}}
            @if ($trashedTestimonials->isNotEmpty())
                <div class="mt-8 rounded-[1.25rem] border border-[#d9cab3] bg-[#fffaf2] p-5">
                    <div class="flex items-center justify-between gap-4">
                        <h3 class="text-lg font-black text-[#1d3c34]">Deleted Testimonials (Trash)</h3>
                        <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-bold text-[#9b6c17]">{{ $trashedTestimonials->count() }} Trashed</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-600">These testimonials have been deleted and are hidden from the public website. You can restore them anytime.</p>

                    <div class="mt-4 overflow-x-auto rounded-xl border border-[#e7ddca] bg-white">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-[#9b6c17] text-[#f8f3eb]">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Client</th>
                                    <th class="px-4 py-3 font-semibold">Agent</th>
                                    <th class="px-4 py-3 font-semibold">Rating</th>
                                    <th class="px-4 py-3 font-semibold">Message</th>
                                    <th class="px-4 py-3 font-semibold">Deleted At</th>
                                    <th class="px-4 py-3 font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($trashedTestimonials as $trashed)
                                    <tr class="border-t border-[#e7ddca]">
                                        <td class="px-4 py-3 font-semibold text-[#1d3c34]">{{ $trashed->name }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $trashed->agent?->name ?? 'General' }}</td>
                                        <td class="px-4 py-3 font-bold text-[#9b6c17]">{{ str_repeat('★', $trashed->rating) }}</td>
                                        <td class="max-w-xs px-4 py-3 text-xs text-slate-600 truncate">{{ $trashed->message }}</td>
                                        <td class="px-4 py-3 text-xs text-slate-500">{{ $trashed->deleted_at?->format('M d, Y H:i') }}</td>
                                        <td class="px-4 py-3">
                                            <form method="POST" action="{{ route('dashboard.testimonials.restore', $trashed->id) }}">
                                                @csrf
                                                <button type="submit" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                                    Restore
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>

        @php
            $editPostId = request()->query('edit_post');
            $postToEdit = $editPostId ? $posts->firstWhere('id', (int) $editPostId) : null;
        @endphp

        @if (auth()->user()->canCreateContent())
            <section id="blog-posts" data-section="blog-posts" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-black text-[#1d3c34]">{{ $postToEdit ? 'Edit Blog & News Post' : 'Blog & News Post' }}</h2>
                    <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">
                        {{ $postToEdit ? 'Edit Mode' : 'Admin Access' }}
                    </span>
                </div>

                <form method="POST" action="{{ $postToEdit ? route('dashboard.posts.update', $postToEdit) : route('dashboard.posts.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-4 md:grid-cols-2">
                    @csrf
                    @if ($postToEdit)
                        @method('PUT')
                    @endif

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                        <input type="text" name="title" value="{{ old('title', $postToEdit?->title) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $postToEdit?->slug) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Type</label>
                        <select name="type" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="blog" {{ old('type', $postToEdit?->type) === 'blog' ? 'selected' : '' }}>Blog</option>
                            <option value="news" {{ old('type', $postToEdit?->type) === 'news' ? 'selected' : '' }}>News</option>
                            <option value="listing" {{ old('type', $postToEdit?->type) === 'listing' ? 'selected' : '' }}>New Listing</option>
                            <option value="project" {{ old('type', $postToEdit?->type) === 'project' ? 'selected' : '' }}>Featured Project</option>
                            <option value="job" {{ old('type', $postToEdit?->type) === 'job' ? 'selected' : '' }}>Organization Job</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Category</label>
                        <input type="text" name="category" value="{{ old('category', $postToEdit?->category) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Tags</label>
                        <input type="text" name="tags" value="{{ old('tags', $postToEdit?->tags) }}" placeholder="market,harar,property" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Author</label>
                        <input type="text" name="author_name" value="{{ old('author_name', $postToEdit?->author_name ?? auth()->user()->name) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Status</label>
                        <select name="status" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="draft" {{ old('status', $postToEdit?->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $postToEdit?->status) === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="scheduled" {{ old('status', $postToEdit?->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Publication date</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', $postToEdit?->published_at?->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Featured image</label>
                        <input type="file" name="image" accept="image/*" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Social image</label>
                        <input type="file" name="social_image" accept="image/*" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">SEO title</label>
                        <input type="text" name="seo_title" value="{{ old('seo_title', $postToEdit?->seo_title) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">SEO description</label>
                        <textarea name="seo_description" rows="2" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('seo_description', $postToEdit?->seo_description) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Related post IDs</label>
                        <input type="text" name="related_posts" value="{{ old('related_posts', $postToEdit?->related_post_ids) }}" placeholder="1, 2, 3" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Content</label>
                        <textarea name="content" rows="8" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('content', $postToEdit?->content) }}</textarea>
                    </div>

                    <div class="md:col-span-2 flex flex-wrap gap-3">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                            {{ $postToEdit ? 'Update Post' : 'Publish Post' }}
                        </button>
                        @if ($postToEdit)
                            <a href="{{ route('dashboard', ['section' => 'blog-posts']) }}" class="rounded-full border border-[#d9cab3] bg-white px-5 py-3 text-sm font-bold text-[#1d3c34]">Cancel edit</a>
                        @endif
                    </div>
                </form>
            </section>
        @endif

        @php
            $editJobId = request()->query('edit_job');
            $jobToEdit = $editJobId ? $jobs->firstWhere('id', (int) $editJobId) : null;
        @endphp

        @if (auth()->user()->canCreateContent())
            <section id="job-postings" data-section="job-postings" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-black text-[#1d3c34]">{{ $jobToEdit ? 'Edit Job Posting' : 'Job Postings' }}</h2>
                    <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">
                        {{ $jobToEdit ? 'Edit Mode' : 'Admin Access' }}
                    </span>
                </div>

                <div class="mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#f9f4ed] p-5">
                    <h3 class="text-xl font-black text-[#1d3c34]">{{ $jobToEdit ? 'Edit Job Opening' : 'Post a Job Opening' }}</h3>
                    <form method="POST" action="{{ $jobToEdit ? route('dashboard.posts.update', $jobToEdit) : route('dashboard.posts.store') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                        @csrf
                        @if ($jobToEdit)
                            @method('PUT')
                        @endif
                        <input type="hidden" name="type" value="job">

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Job Title</label>
                            <input type="text" name="title" value="{{ old('title', $jobToEdit?->title) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Department / Category</label>
                            <input type="text" name="category" value="{{ old('category', $jobToEdit?->category) }}" placeholder="Sales, Marketing..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Location</label>
                            <input type="text" name="job_location" value="{{ old('job_location', $jobToEdit?->job_location) }}" placeholder="Addis Ababa, Ethiopia" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Employment Type</label>
                            <select name="job_type" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                @foreach (['Full-time', 'Part-time', 'Contract', 'Internship'] as $employmentType)
                                    <option value="{{ $employmentType }}" {{ old('job_type', $jobToEdit?->job_type) === $employmentType ? 'selected' : '' }}>{{ $employmentType }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Salary Range</label>
                            <input type="text" name="salary_range" value="{{ old('salary_range', $jobToEdit?->salary_range) }}" placeholder="ETB 15,000 - 25,000" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Experience Required</label>
                            <input type="text" name="experience_level" value="{{ old('experience_level', $jobToEdit?->experience_level) }}" placeholder="2+ years" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Education Required</label>
                            <input type="text" name="education_level" value="{{ old('education_level', $jobToEdit?->education_level) }}" placeholder="Bachelor's Degree in ..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Application Deadline</label>
                            <input type="date" name="application_deadline" value="{{ old('application_deadline', $jobToEdit?->application_deadline?->format('Y-m-d')) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Status</label>
                            <select name="status" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                <option value="published" {{ old('status', $jobToEdit?->status ?? 'published') === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="draft" {{ old('status', $jobToEdit?->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Apply Link</label>
                            <input type="url" name="apply_link" value="{{ old('apply_link', $jobToEdit?->apply_link) }}" placeholder="https://... or mailto:hr@realestate.com" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <p class="mt-1 text-xs text-slate-500">Shown as the Apply button on the careers page. Leave empty to default to the contact email.</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Job Description</label>
                            <textarea name="content" rows="5" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('content', $jobToEdit?->content) }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Requirements</label>
                            <textarea name="requirements" rows="4" placeholder="One requirement per line..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('requirements', $jobToEdit?->requirements) }}</textarea>
                        </div>

                        <div class="md:col-span-2 flex flex-wrap gap-3">
                            <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                                {{ $jobToEdit ? 'Update Job Posting' : 'Post Job Opening' }}
                            </button>
                            @if ($jobToEdit)
                                <a href="{{ route('dashboard', ['section' => 'job-postings']) }}" class="rounded-full border border-[#d9cab3] bg-white px-5 py-3 text-sm font-bold text-[#1d3c34]">Cancel edit</a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="mt-6 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#2d5d4d] text-[#f8f3eb]">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Title</th>
                                <th class="px-5 py-4 font-semibold">Location</th>
                                <th class="px-5 py-4 font-semibold">Deadline</th>
                                <th class="px-5 py-4 font-semibold">Status</th>
                                <th class="px-5 py-4 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($jobs as $job)
                                <tr class="border-t border-[#e7ddca] bg-white">
                                    <td class="px-5 py-4 font-semibold text-[#1d3c34]">{{ $job->title }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $job->job_location ?? '—' }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $job->application_deadline?->format('M d, Y') ?? '—' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full {{ $job->status === 'published' ? 'bg-[#dfeee4] text-[#1d3c34]' : 'bg-[#edf2ee] text-[#2d5d4d]' }} px-2.5 py-1 text-xs font-bold">
                                            {{ ucfirst($job->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('dashboard', ['edit_job' => $job->id, 'section' => 'job-postings']) }}" class="rounded-full bg-[#1d3c34] px-3 py-2 text-xs font-bold text-white">Edit</a>
                                            <form method="POST" action="{{ route('dashboard.posts.togglePublish', $job) }}">
                                                @csrf
                                                <button type="submit" class="rounded-full border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                                                    {{ $job->status === 'published' ? 'Unpublish' : 'Publish' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('dashboard.posts.delete', $job) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-3 py-2 text-xs font-bold text-[#a24339]" onclick="return confirm('Delete this job posting?')">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-6 text-center text-slate-500">No job postings yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="mt-10 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-2xl font-black text-[#1d3c34]">Content Library</h2>
                <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">{{ $posts->count() }} posts</span>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="mt-5 grid gap-3 md:grid-cols-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts" class="rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5">
                <select name="type" class="rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5">
                    <option value="">All types</option>
                    <option value="blog" {{ request('type') === 'blog' ? 'selected' : '' }}>Blog</option>
                    <option value="news" {{ request('type') === 'news' ? 'selected' : '' }}>News</option>
                    <option value="listing" {{ request('type') === 'listing' ? 'selected' : '' }}>New Listing</option>
                    <option value="project" {{ request('type') === 'project' ? 'selected' : '' }}>Featured Project</option>
                    <option value="job" {{ request('type') === 'job' ? 'selected' : '' }}>Organization Job</option>
                </select>
                <select name="status" class="rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5">
                    <option value="">All status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                </select>
                <button type="submit" class="rounded-xl bg-[#1d3c34] px-4 py-2.5 text-sm font-bold text-white">Apply filters</button>
            </form>

            <div class="mt-6 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#2d5d4d] text-[#f8f3eb]">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Title</th>
                            <th class="px-5 py-4 font-semibold">Type</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 font-semibold">Date</th>
                            <th class="px-5 py-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            <tr class="border-t border-[#e7ddca] bg-white">
                                <td class="px-5 py-4 font-semibold text-[#1d3c34]">{{ $post->title }}</td>
                                <td class="px-5 py-4 text-slate-600 uppercase">{{ $post->type }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full {{ $post->status === 'published' ? 'bg-[#dfeee4] text-[#1d3c34]' : ($post->status === 'scheduled' ? 'bg-[#f9ecd0] text-[#9b6c17]' : 'bg-[#edf2ee] text-[#2d5d4d]') }} px-2.5 py-1 text-xs font-bold">
                                        {{ ucfirst($post->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $post->published_at?->format('M d, Y') ?? $post->scheduled_for?->format('M d, Y') ?? 'Not set' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('dashboard', ['edit_post' => $post->id, 'section' => 'blog-posts']) }}" class="rounded-full bg-[#1d3c34] px-3 py-2 text-xs font-bold text-white">Edit</a>
                                        <form method="POST" action="{{ route('dashboard.posts.togglePublish', $post) }}">
                                            @csrf
                                            <button type="submit" class="rounded-full border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                                                {{ $post->status === 'published' ? 'Unpublish' : 'Publish' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('dashboard.posts.delete', $post) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-3 py-2 text-xs font-bold text-[#a24339]" onclick="return confirm('Delete this post?')">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-slate-500">No posts match the current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-10 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-2xl font-black text-[#1d3c34]">Property List</h2>
                <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">{{ $properties->count() }} items</span>
            </div>

            <div class="mt-6 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#2d5d4d] text-[#f8f3eb]">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Title</th>
                            <th class="px-5 py-4 font-semibold">Price</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 font-semibold">Featured</th>
                            <th class="px-5 py-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($properties as $property)
                            <tr class="border-t border-[#e7ddca] bg-white">
                                <td class="px-5 py-4 font-semibold text-[#1d3c34]">{{ $property->title }}</td>
                                <td class="px-5 py-4 text-slate-600">${{ number_format($property->price, 0) }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full {{ $property->status === 'published' ? 'bg-[#dfeee4] text-[#1d3c34]' : ($property->status === 'archived' ? 'bg-[#f8ddd9] text-[#a24339]' : 'bg-[#f9ecd0] text-[#9b6c17]') }} px-2.5 py-1 text-xs font-bold">
                                        {{ ucfirst($property->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $property->featured ? 'Yes' : 'No' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('dashboard', ['edit_property' => $property->id, 'section' => 'properties']) }}" class="rounded-full bg-[#1d3c34] px-3 py-2 text-xs font-bold text-white">Edit</a>
                                        <form method="POST" action="{{ route('dashboard.properties.togglePublish', $property) }}">
                                            @csrf
                                            <button type="submit" class="rounded-full border border-[#b9a98b] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                                                {{ $property->is_active ? 'Unpublish' : 'Publish' }}
                                            </button>
                                        </form>
                                        @php
                                            $soldStatus = $property->type === 'rent' ? 'rented' : 'sold';
                                            $soldLabel = $property->type === 'rent' ? 'Rented' : 'Sold';
                                            $markLabel = $property->type === 'rent' ? 'Mark Rented' : 'Mark Sold';
                                        @endphp
                                        <form method="POST" action="{{ route('dashboard.properties.toggleSold', $property) }}">
                                            @csrf
                                            <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#f8f3eb] px-3 py-2 text-xs font-bold text-[#1d3c34]">
                                                {{ $property->status === $soldStatus ? 'Mark Available' : $markLabel }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('dashboard.properties.archive', $property) }}">
                                            @csrf
                                            <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#f9f4ed] px-3 py-2 text-xs font-bold text-[#1d3c34]" {{ $property->status === 'archived' ? 'disabled' : '' }}>
                                                Archive
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('dashboard.properties.destroy', $property) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-3 py-2 text-xs font-bold text-[#a24339]" onclick="return confirm('Delete this property?')">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-slate-500">No properties yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($posts->isNotEmpty())
            <section class="mt-10 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <h2 class="text-2xl font-black text-[#1d3c34]">Latest Blog & News</h2>
                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    @foreach ($posts as $post)
                        <article class="rounded-[1.25rem] border border-[#e7ddca] bg-[#f9f4ed] p-5">
                            <div class="flex items-center justify-between gap-3">
                                <span class="rounded-full bg-[#dfeee4] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#1d3c34]">{{ $post->type }}</span>
                                <span class="text-xs text-slate-500">{{ $post->user->name }}</span>
                            </div>
                            <h3 class="mt-4 text-xl font-black text-[#1d3c34]">{{ $post->title }}</h3>
                            <p class="mt-3 line-clamp-4 text-sm leading-6 text-slate-600">{{ $post->content }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navLinks = document.querySelectorAll('.nav-section-link');
            const panels = document.querySelectorAll('.section-panel');

            function activateSection(sectionName) {
                panels.forEach(panel => {
                    const visible = panel.dataset.section === sectionName;
                    panel.classList.toggle('hidden', !visible);
                });

                navLinks.forEach(link => {
                    const active = link.dataset.targetSection === sectionName;
                    link.classList.toggle('bg-white/10', active);
                    link.classList.toggle('text-white', active);
                    link.classList.toggle('text-[#dfeee4]', !active);
                    link.classList.toggle('font-semibold', true);
                });
            }

            navLinks.forEach(link => {
                link.addEventListener('click', function (event) {
                    const target = this.dataset.targetSection;
                    if (!target) {
                        return;
                    }

                    event.preventDefault();
                    activateSection(target);
                    const targetPanel = document.querySelector('[data-section="' + target + '"]');
                    if (targetPanel) {
                        targetPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });

            const params = new URLSearchParams(window.location.search);
            const paramSection = params.get('section');
            const editSection = params.has('edit_user') ? 'users'
                : params.has('edit_post') ? 'blog-posts'
                : params.has('edit_property') ? 'properties'
                : params.has('edit_agent') ? 'agents'
                : params.has('edit_testimonial') ? 'testimonials'
                : null;
            const initialHash = window.location.hash.replace('#', '');
            const initialSection = initialHash || paramSection || editSection || 'overview';

            if (document.querySelector('[data-section="' + initialSection + '"]')) {
                activateSection(initialSection);
            }
        });
    </script>
</body>
</html>
