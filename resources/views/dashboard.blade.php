<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f5f0e7] text-slate-800 antialiased scroll-smooth">
    <div class="flex min-h-screen">
        <aside class="w-72 shrink-0 border-r border-[#d9cab3] bg-[#1d3c34] p-6 text-[#f8f3eb]">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ $siteBrand['logoUrl'] }}" alt="{{ $siteBrand['name'] }} Logo" class="h-10 w-10 rounded-xl object-contain ring-2 ring-[#d9cab3] bg-white/10">
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
                <a href="#commissions" data-target-section="commissions" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span>Commissions</span>
                    @if (isset($overviewStats['pendingCommissions']) && $overviewStats['pendingCommissions'] > 0)
                        <span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-xs font-bold text-[#9b6c17]">{{ $overviewStats['pendingCommissions'] }}</span>
                    @endif
                </a>
                <a href="#inquiries" data-target-section="inquiries" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span>Message</span>
                    @if (isset($inquiries) && $inquiries->whereNull('read_at')->isNotEmpty())
                        <span id="sidebar-inquiries-badge" class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-xs font-bold text-[#9b6c17]">{{ $inquiries->whereNull('read_at')->count() }} new</span>
                    @endif
                </a>
                <a href="#blog-posts" data-target-section="blog-posts" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Blog &amp; News</a>
                <a href="#job-postings" data-target-section="job-postings" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Job Postings</a>
                <a href="#agents" data-target-section="agents" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Agents</a>
                <a href="#testimonials" data-target-section="testimonials" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Client Testimonials</a>
                <a href="#subscribers" data-target-section="subscribers" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span>Subscribers</span>
                    <span class="rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold">{{ $subscribers->count() }}</span>
                </a>
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

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-[#c86b5c] bg-[#fef3f1] p-4 text-sm font-medium text-[#a24339]">
                <p class="font-bold mb-1">Please fix the following errors:</p>
                <ul class="list-disc pl-5 space-y-1 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
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

            <div class="mt-8 grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
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
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#f0f6f3] p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Happy Buyers (Subscribers)</p>
                    <p class="mt-3 text-3xl font-black text-[#1d3c34]">{{ (int) \App\Models\SiteSetting::get('happy_buyers_base_count', 0) + $subscribers->count() }}</p>
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
                <button type="button" class="toggle-create-form rounded-full bg-[#1d3c34] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]" data-target="properties-create-panel" data-label-create="+ Add Property" data-label-close="Close Form">+ Add Property</button>
            </div>

            <div id="properties-create-panel" class="create-form-panel mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#f9f4ed] p-5 {{ $propertyToEdit ? '' : 'hidden' }}">
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
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Price (ETB)</label>
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
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-semibold text-slate-700">Property Photos & Cover Image</label>
                            <span id="property-image-counter-pill" class="text-xs font-bold text-[#1d3c34] bg-[#dfeee4] px-2.5 py-0.5 rounded-full hidden">0 photos</span>
                        </div>
                        <p class="text-xs text-slate-500 mb-2.5">Upload multiple photos for this listing. The primary cover image is shown on search cards, sliders, and featured banners. You can change the cover image anytime by clicking <strong>"★ Set Cover"</strong> on any photo below, uploading a new cover file, or selecting a luxury background preset.</p>

                        {{-- Hidden inputs for selected cover image or luxury preset --}}
                        <input type="hidden" name="cover_image_id" id="admin-cover-image-id" value="{{ $propertyToEdit ? optional($propertyToEdit->images->firstWhere('image_path', $propertyToEdit->image_path))->id : '' }}">
                        <input type="hidden" name="cover_preset" id="admin-cover-preset" value="">

                        {{-- Active Cover Photo Section (when editing) --}}
                        @if ($propertyToEdit)
                            <div class="mb-4 rounded-2xl border border-[#d9cab3] bg-gradient-to-r from-[#fdfaf5] via-white to-[#fbf8f3] p-4 shadow-2xs">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#d4af37] text-[#102b25] text-xs font-black shadow-xs">★</span>
                                        <div>
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-800">Primary Cover Photo</span>
                                            <span class="text-[11px] text-slate-500 ml-1.5">(Displayed on website cards and banner headers)</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="admin-cover-file" class="cursor-pointer inline-flex items-center gap-1.5 rounded-xl border border-[#1d3c34]/25 bg-white hover:bg-[#1d3c34] hover:text-white px-3 py-1.5 text-xs font-bold text-[#1d3c34] shadow-2xs transition">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            <span>Upload New Cover Photo</span>
                                        </label>
                                        <input type="file" id="admin-cover-file" name="cover_image" accept="image/png,image/jpeg,image/webp,image/avif" class="sr-only" onchange="previewAdminCoverImage(this)">
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="relative h-20 w-32 sm:h-24 sm:w-40 shrink-0 overflow-hidden rounded-xl border-2 border-[#d4af37] shadow-sm bg-slate-100">
                                        <img id="admin-active-cover-img" src="{{ $propertyToEdit->primary_image_url }}" alt="Cover Photo" class="h-full w-full object-cover">
                                        <span class="absolute top-1 left-1 rounded bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black uppercase text-[#102b25] shadow-xs">★ Cover</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800" id="admin-cover-status-text">Active Cover Image</p>
                                        <p class="text-[11px] text-slate-500 mt-1">To change, click <strong>"★ Set Cover"</strong> on any gallery photo below, upload a new cover above, or pick a luxury preset background.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Quick Luxury Background Preset Selector --}}
                        <div class="mb-4 rounded-xl border border-[#e7ddca] bg-[#fdfaf5] p-3.5">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <span>🏙️</span>
                                    <span>Best Luxury Background Presets (1-Click Insert)</span>
                                </span>
                                <span class="text-[11px] text-slate-500">Insert high-definition architecture background when photos are unavailable</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                @php
                                    $presets = [
                                        ['id' => 'hero-skyline', 'name' => 'Skyline Luxury', 'file' => 'images/luxury/hero-skyline.jpg'],
                                        ['id' => 'luxury-towers', 'name' => 'Modern Towers', 'file' => 'images/luxury/luxury-towers.jpg'],
                                        ['id' => 'central-plaza', 'name' => 'Central Plaza', 'file' => 'images/luxury/central-plaza.jpg'],
                                        ['id' => 'panoramic-park', 'name' => 'Panoramic Park', 'file' => 'images/luxury/panoramic-park.jpg'],
                                        ['id' => 'retail-boulevard', 'name' => 'Retail Boulevard', 'file' => 'images/luxury/retail-boulevard.jpg'],
                                    ];
                                @endphp
                                @foreach ($presets as $pr)
                                    <button type="button" onclick="selectAdminPresetBackground('{{ $pr['id'] }}', '{{ asset($pr['file']) }}', {{ $propertyToEdit ? $propertyToEdit->id : 'null' }})" class="group relative rounded-lg overflow-hidden border border-[#d9cab3] hover:border-[#d4af37] aspect-[16/10] text-left transition cursor-pointer shadow-2xs hover:shadow-xs">
                                        <img src="{{ asset($pr['file']) }}" alt="{{ $pr['name'] }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-transparent"></div>
                                        <span class="absolute bottom-1 left-1.5 right-1.5 text-[10px] font-bold text-white truncate drop-shadow">{{ $pr['name'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Drag & Drop Area --}}
                        <div id="admin-property-dropzone" class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#d9cab3] bg-white p-5 text-center transition-all hover:border-[#1d3c34] hover:bg-[#dfeee4]/20 cursor-pointer">
                            <input id="property-image-input" type="file" name="images[]" multiple accept="image/png,image/jpeg,image/webp,image/avif" class="sr-only">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#1d3c34]/10 text-[#1d3c34] mb-2 pointer-events-none">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700 pointer-events-none"><span class="text-[#1d3c34] underline decoration-2">Click to select photos</span> or drag and drop here</p>
                            <p class="text-xs text-slate-400 mt-1 pointer-events-none">PNG, JPG, WEBP — select as many as you need (up to 50 photos)</p>
                        </div>
                        
                        {{-- Live Preview for newly selected files --}}
                        <div id="new-property-images-preview" class="mt-3 hidden grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2.5"></div>

                        @if ($propertyToEdit && ($propertyToEdit->images->isNotEmpty() || $propertyToEdit->image_path))
                            <div class="mt-4 border-t border-[#e7ddca] pt-3">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-700">Current Photos ({{ $propertyToEdit->images->count() ?: 1 }})</p>
                                    <span class="text-[11px] text-slate-500">Click <strong>"★ Set Cover"</strong> on any photo to update primary cover</span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2.5" id="admin-existing-images-container">
                                    @forelse ($propertyToEdit->images as $propImg)
                                        @php
                                            $isCover = ($propImg->image_path === $propertyToEdit->image_path) || ($loop->first && ! $propertyToEdit->image_path);
                                        @endphp
                                        <div class="group relative rounded-xl overflow-hidden border-2 {{ $isCover ? 'border-[#d4af37] ring-2 ring-[#d4af37]/30' : 'border-[#d9cab3]' }} bg-white aspect-square shadow-2xs transition-all" id="admin-prop-img-{{ $propImg->id }}" data-property-id="{{ $propertyToEdit->id }}">
                                            <img src="{{ $propImg->image_url }}" alt="Property Photo" class="h-full w-full object-cover">
                                            
                                            {{-- Cover Control Badge / Button --}}
                                            <div class="admin-cover-control absolute top-1 left-1 z-10">
                                                @if ($isCover)
                                                    <span class="admin-cover-badge rounded-md bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black text-[#102b25] uppercase tracking-wider shadow-xs flex items-center gap-1">★ Cover</span>
                                                @else
                                                    <button type="button" onclick="setAdminCoverImage({{ $propertyToEdit->id }}, {{ $propImg->id }}, '{{ $propImg->image_url }}')" title="Set as primary cover photo" class="admin-make-cover-btn rounded-md bg-black/75 hover:bg-[#d4af37] hover:text-[#102b25] text-white px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider transition backdrop-blur-xs cursor-pointer shadow-xs">★ Set Cover</button>
                                                @endif
                                            </div>

                                            {{-- Delete Button --}}
                                            <button type="button" onclick="deleteAdminPropertyImage({{ $propertyToEdit->id }}, {{ $propImg->id }})" title="Remove photo" class="absolute top-1 right-1 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-red-600/90 text-white hover:bg-red-700 transition shadow-xs cursor-pointer">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @empty
                                        @if ($propertyToEdit->image_path)
                                            <div class="relative rounded-xl overflow-hidden border-2 border-[#d4af37] bg-white aspect-square shadow-2xs">
                                                <img src="{{ $propertyToEdit->primary_image_url }}" alt="{{ $propertyToEdit->title }}" class="h-full w-full object-cover">
                                                <span class="absolute top-1 left-1 rounded-md bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black text-[#102b25] uppercase tracking-wider shadow-xs">★ Cover</span>
                                            </div>
                                        @endif
                                    @endforelse
                                </div>
                            </div>
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

            {{-- Property List (existing listings) --}}
            <div class="mt-8 rounded-[1.25rem] border border-[#e7ddca]">
                <div class="flex items-center justify-between gap-4 rounded-t-[1.25rem] bg-[#2d5d4d] px-5 py-4">
                    <h3 class="text-lg font-black text-white">Property List</h3>
                    <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-white">{{ $properties->count() }} items</span>
                </div>

                {{-- Properties Search Bar --}}
                <div class="border-b border-[#e7ddca] bg-[#fbf8f3] px-5 py-3.5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="relative flex-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input type="text" id="dashboard-properties-search" placeholder="Search properties by title, price, status, type, location..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2 pl-9 pr-8 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                            <button type="button" id="dashboard-properties-search-clear" class="hidden absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 transition" title="Clear search">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <span id="dashboard-properties-count" class="shrink-0 rounded-full bg-[#1d3c34]/10 px-3 py-1 text-xs font-bold text-[#1d3c34]">{{ $properties->count() }} properties</span>
                    </div>
                </div>

                <div class="overflow-x-auto bg-white">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#f4efe7] text-[#1d3c34]">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Title</th>
                                <th class="px-5 py-3 font-semibold">Price</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 font-semibold">Featured</th>
                                <th class="px-5 py-3 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($properties as $property)
                                <tr class="dashboard-property-row border-t border-[#e7ddca]" data-search="{{ strtolower($property->title . ' ' . $property->price . ' ' . $property->status . ' ' . $property->type . ' ' . ($property->city ?? '') . ' ' . ($property->address ?? '') . ' ' . ($property->agent?->name ?? '')) }}">
                                    <td class="px-5 py-4 font-semibold text-[#1d3c34]">{{ $property->title }}</td>
                                    <td class="px-5 py-4 text-slate-600">ETB {{ number_format($property->price, 0) }}</td>
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
                                                $latestAgreementOrder = $property->orders ? $property->orders->firstWhere('agreement_path', '!=', null) : null;
                                            @endphp
                                            <form method="POST" action="{{ route('dashboard.properties.toggleSold', $property) }}">
                                                @csrf
                                                <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#f8f3eb] px-3 py-2 text-xs font-bold text-[#1d3c34]">
                                                    {{ $property->status === $soldStatus ? 'Mark Available' : $markLabel }}
                                                </button>
                                            </form>
                                            @if ($latestAgreementOrder)
                                                <a href="{{ route('orders.agreement', $latestAgreementOrder) }}" class="inline-flex items-center gap-1 rounded-full border border-[#1d3c34] bg-[#edf2ee] px-3 py-2 text-xs font-bold text-[#1d3c34] transition hover:bg-[#dfeee4]">
                                                    View Agreement
                                                </a>
                                            @else
                                                <button type="button" class="open-agreement-modal rounded-full bg-[#b7842d] px-3 py-2 text-xs font-bold text-white transition hover:bg-[#966b22]"
                                                    data-property-id="{{ $property->id }}"
                                                    data-property-title="{{ $property->title }}"
                                                    data-property-price="{{ $property->price }}"
                                                    data-property-type="{{ $property->type }}"
                                                    data-action-url="{{ route('dashboard.properties.generateAgreement', $property) }}">
                                                    {{ $property->type === 'rent' ? 'Rent with Agreement' : 'Sell with Agreement' }}
                                                </button>
                                            @endif
                                            <form method="POST" action="{{ route('dashboard.properties.archive', $property) }}">
                                                @csrf
                                                <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#f9f4ed] px-3 py-2 text-xs font-bold text-[#1d3c34]" {{ $property->status === 'archived' ? 'disabled' : '' }}>
                                                    Archive
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('dashboard.properties.destroy', $property) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-3 py-2 text-xs font-bold text-[#a24339]" data-confirm="Delete this property?">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-6 text-center text-slate-500">No properties yet. Click “+ Add Property” to create one.</td>
                                </tr>
                            @endforelse
                            <tr id="dashboard-properties-no-results" class="hidden border-t border-[#e7ddca] bg-white">
                                <td colspan="5" class="px-5 py-6 text-center text-slate-500">No properties found matching your search.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
                                    <p class="text-sm font-semibold text-slate-700">Property: {{ $adminOrder->property?->title ?? 'Removed' }} · {{ $adminOrder->isRental() ? 'Rent: ETB ' : 'Offer: ETB ' }}{{ number_format($adminOrder->offer_amount ?? 0, 2) }}</p>
                                    @if ($adminOrder->agent_note)
                                        <p class="text-xs italic text-slate-600"><span class="font-bold text-[#1d3c34]">Agent Agreement Note:</span> “{{ $adminOrder->agent_note }}”</p>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($adminOrder->hasAgreement())
                                        <a href="{{ route('orders.agreement', $adminOrder) }}" class="rounded-full border border-[#1d3c34] px-3 py-1.5 text-xs font-bold text-[#1d3c34] hover:bg-[#edf2ee]">
                                            Agreement PDF
                                        </a>
                                        <a href="{{ route('dashboard.orders.editAgreement', $adminOrder) }}" class="rounded-full border border-[#b7842d] bg-[#fdf8ef] px-3 py-1.5 text-xs font-bold text-[#9b6c17] transition hover:bg-[#f9ecd0]">
                                            Edit Content
                                        </a>
                                    @else
                                        <form method="POST" action="{{ route('dashboard.orders.generateAgreement', $adminOrder) }}">
                                            @csrf
                                            <button type="submit" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                                Generate Agreement
                                            </button>
                                        </form>
                                        <a href="{{ route('dashboard.orders.editAgreement', $adminOrder) }}" class="rounded-full border border-[#b7842d] bg-[#fdf8ef] px-3 py-1.5 text-xs font-bold text-[#9b6c17] transition hover:bg-[#f9ecd0]">
                                            Edit & Generate
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
                                    <form method="POST" action="{{ route('dashboard.orders.destroy', $adminOrder) }}" data-confirm="Are you sure you want to delete this request?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1.5 text-xs font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($orders->isNotEmpty())
                {{-- Orders Search Bar --}}
                <div class="mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#fbf8f3] p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="relative flex-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input type="text" id="dashboard-orders-search" placeholder="Search orders by client name, email, phone, property, agent, status, type..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2 pl-9 pr-8 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                            <button type="button" id="dashboard-orders-search-clear" class="hidden absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 transition" title="Clear search">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <span id="dashboard-orders-count" class="shrink-0 rounded-full bg-[#1d3c34]/10 px-3 py-1 text-xs font-bold text-[#1d3c34]">{{ $orders->count() }} orders</span>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca] bg-white">
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
                                <th class="px-5 py-4 font-semibold">Actions</th>
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
                                <tr class="dashboard-order-row border-t border-[#e7ddca] align-top" data-search="{{ strtolower($order->name . ' ' . $order->email . ' ' . ($order->phone ?? '') . ' ' . ($order->property?->title ?? '') . ' ' . ($order->agent?->name ?? '') . ' ' . $order->status . ' ' . ($order->isRental() ? 'rent' : 'sale') . ' ' . ($order->offer_amount ?? '')) }}">
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-[#1d3c34]">{{ $order->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $order->email }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="font-medium text-slate-700">{{ $order->agent?->name ?? 'None (Direct Admin)' }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-slate-700">{{ $order->property?->title ?? 'Property removed' }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $order->isRental() ? 'Rent: ETB '.number_format($order->offer_amount ?? 0, 2).'/mo' : 'Offer: ETB '.number_format($order->offer_amount ?? 0, 2) }}
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
                                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                                <a href="{{ route('orders.agreement', $order) }}" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#254d43]">
                                                    Download PDF
                                                </a>
                                                <form method="POST" action="{{ route('dashboard.orders.generateAgreement', $order) }}">
                                                    @csrf
                                                    <button type="submit" class="text-xs text-[#2d5d4d] underline hover:text-[#1d3c34]">
                                                        Regenerate
                                                    </button>
                                                </form>
                                                <a href="{{ route('dashboard.orders.editAgreement', $order) }}" class="text-xs text-[#9b6c17] underline hover:text-[#b7842d]">
                                                    Edit & Regenerate
                                                </a>
                                            </div>
                                        @else
                                            <span class="inline-block rounded-full bg-[#f3ecdb] px-2.5 py-1 text-xs font-bold text-slate-500">No agreement yet</span>
                                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                                <form method="POST" action="{{ route('dashboard.orders.generateAgreement', $order) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                                        Generate Agreement
                                                    </button>
                                                </form>
                                                <a href="{{ route('dashboard.orders.editAgreement', $order) }}" class="rounded-full border border-[#b7842d] bg-[#fdf8ef] px-3 py-1.5 text-xs font-bold text-[#9b6c17] transition hover:bg-[#f9ecd0]">
                                                    Edit & Generate
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $order->created_at?->format('M d, Y') }}</td>
                                    <td class="px-5 py-4">
                                        <form method="POST" action="{{ route('dashboard.orders.destroy', $order) }}" data-confirm="Are you sure you want to delete this order? This will also remove any generated agreement.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1.5 text-xs font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="dashboard-orders-no-results" class="hidden border-t border-[#e7ddca] bg-white">
                                <td colspan="8" class="px-5 py-6 text-center text-slate-500">No orders found matching your search.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mt-6 text-sm text-slate-500">No orders yet. Buy and rent requests from clients will appear here.</p>
            @endif
        </section>

        {{-- ═══════════════════ COMMISSIONS ═══════════════════ --}}
        <section id="commissions" data-section="commissions" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[#1d3c34]">Agent Commissions</h2>
                    <p class="mt-1 text-sm text-slate-500">Commission fees owed by agents on completed buy/rent deals.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <div class="rounded-2xl border border-[#d9cab3] bg-[#f9f4ec] px-4 py-2 text-center">
                        <p class="text-xs font-bold uppercase tracking-widest text-[#587165]">Pending</p>
                        <p class="mt-1 text-lg font-black text-[#1d3c34]">ETB {{ number_format($pendingCommissionsTotal ?? 0, 2) }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#d9cab3] bg-[#edf9ee] px-4 py-2 text-center">
                        <p class="text-xs font-bold uppercase tracking-widest text-[#587165]">Collected</p>
                        <p class="mt-1 text-lg font-black text-[#1d3c34]">ETB {{ number_format($paidCommissionsTotal ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>

            @if (isset($commissions) && $commissions->isNotEmpty())
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e9ddd0] text-sm">
                        <thead>
                            <tr class="bg-[#f8f3eb]">
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-[#587165]">Agent</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-[#587165]">Property</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-[#587165]">Deal Price</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-[#587165]">Rate</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-[#587165]">Commission</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-[#587165]">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-[#587165]">Date</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-[#587165]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f1e8de]">
                            @foreach ($commissions as $commission)
                                <tr class="transition hover:bg-[#fdf9f4]">
                                    <td class="px-5 py-4 font-semibold text-[#1d3c34]">{{ $commission->agent?->name ?? '—' }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ Str::limit($commission->property?->title ?? '—', 30) }}</td>
                                    <td class="px-5 py-4 font-semibold text-[#1d3c34]">ETB {{ number_format($commission->property_price, 2) }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $commission->commission_rate }}%</td>
                                    <td class="px-5 py-4 font-black text-[#1d3c34]">ETB {{ number_format($commission->commission_amount, 2) }}</td>
                                    <td class="px-5 py-4">
                                        @if ($commission->status === 'paid')
                                            <span class="rounded-full bg-[#edf9ee] px-2.5 py-1 text-xs font-bold text-[#1a5c35]">Paid</span>
                                        @elseif ($commission->status === 'waived')
                                            <span class="rounded-full bg-[#f3f0fb] px-2.5 py-1 text-xs font-bold text-[#5b46a1]">Waived</span>
                                        @else
                                            <span class="rounded-full bg-[#fef9ec] px-2.5 py-1 text-xs font-bold text-[#9b6c17]">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-slate-500">{{ $commission->created_at?->format('M d, Y') }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <form method="POST" action="{{ route('dashboard.commissions.status', $commission) }}" class="flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <div class="relative w-16">
                                                    <input type="number" step="0.1" min="0" max="100" name="commission_rate" value="{{ $commission->commission_rate }}" title="Update Commission Rate" class="w-full rounded-lg border border-[#d9cab3] bg-white px-1.5 py-1 pr-4 text-xs font-semibold text-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                                                    <span class="absolute right-1 top-1 text-[10px] text-slate-400">%</span>
                                                </div>
                                                <select name="status" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                                    <option value="pending" @selected($commission->status === 'pending')>Pending</option>
                                                    <option value="paid" @selected($commission->status === 'paid')>Paid</option>
                                                    <option value="waived" @selected($commission->status === 'waived')>Waived</option>
                                                </select>
                                                <button type="submit" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#254d43]">Save</button>
                                            </form>
                                            <form method="POST" action="{{ route('dashboard.commissions.destroy', $commission) }}" data-confirm="Are you sure you want to delete this commission record?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1.5 text-xs font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
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
            @else
                <p class="mt-6 text-sm text-slate-500">No commission records yet. They are created automatically when an agent accepts a buy/rent request.</p>
            @endif
        </section>

        {{-- ═══════════════════ MESSAGES / INQUIRIES ═══════════════════ --}}
        <section id="inquiries" data-section="inquiries" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[#1d3c34]">Message</h2>
                    <p class="mt-1 text-sm text-slate-500">Direct client contact requests and property inquiries submitted through the website.</p>
                </div>
                <div class="flex items-center gap-2">
                    @php $unreadInquiriesCount = isset($inquiries) ? $inquiries->whereNull('read_at')->count() : 0; @endphp
                    <span id="inquiries-unread-pill" class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#9b6c17] {{ $unreadInquiriesCount > 0 ? '' : 'hidden' }}">
                        <span id="inquiries-unread-count">{{ $unreadInquiriesCount }}</span> New
                    </span>
                    <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">{{ isset($inquiries) ? $inquiries->count() : 0 }} Total</span>
                </div>
            </div>

            @if (isset($inquiries) && $inquiries->isNotEmpty())
                <div class="mt-6 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca] bg-white">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#2d5d4d] text-[#f8f3eb]">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Client</th>
                                <th class="px-5 py-4 font-semibold">Assigned Agent</th>
                                <th class="px-5 py-4 font-semibold">Subject</th>
                                <th class="w-44 max-w-[170px] px-5 py-4 font-semibold">Message</th>
                                <th class="px-5 py-4 font-semibold">Received</th>
                                <th class="px-5 py-4 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f1e8de]">
                            @foreach ($inquiries as $inquiry)
                                <tr id="inquiry-row-{{ $inquiry->id }}" class="border-t border-[#e7ddca] align-top transition hover:bg-[#fdf9f4] {{ ! $inquiry->read_at ? 'bg-[#fffdf9]' : '' }}"
                                    data-inquiry-row="{{ $inquiry->id }}">
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-[#1d3c34]">{{ $inquiry->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $inquiry->email }}</p>
                                        @if ($inquiry->phone)
                                            <p class="text-xs text-slate-500">{{ $inquiry->phone }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($inquiry->agent)
                                            <span class="rounded-full bg-[#dfeee4] px-2.5 py-1 text-xs font-bold text-[#1d3c34]">{{ $inquiry->agent->name }}</span>
                                        @else
                                            <span class="rounded-full bg-[#f4efe7] px-2.5 py-1 text-xs font-bold text-slate-600">Admin / General</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-[#1d3c34]">{{ $inquiry->subject }}</p>
                                        @if (! $inquiry->read_at)
                                            <span class="inquiry-badge-new mt-1 inline-block rounded-full bg-[#f9ecd0] px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.14em] text-[#9b6c17]">New</span>
                                        @endif
                                    </td>
                                    <td class="w-44 max-w-[170px] px-5 py-4 text-xs leading-5 text-slate-600">
                                        <div class="flex items-center justify-between gap-1.5">
                                            <button type="button" class="view-inquiry-btn truncate block max-w-[100px] text-left text-xs text-slate-600 hover:text-[#1d3c34] hover:underline cursor-pointer"
                                                title="Click to view message"
                                                data-id="{{ $inquiry->id }}"
                                                data-read="{{ $inquiry->read_at ? '1' : '0' }}"
                                                data-read-url="{{ route('dashboard.inquiries.read', $inquiry) }}"
                                                data-name="{{ $inquiry->name }}"
                                                data-email="{{ $inquiry->email }}"
                                                data-phone="{{ $inquiry->phone ?? '' }}"
                                                data-agent="{{ $inquiry->agent?->name ?? 'Admin / General' }}"
                                                data-subject="{{ $inquiry->subject }}"
                                                data-message="{{ $inquiry->message }}"
                                                data-received="{{ $inquiry->created_at?->format('M d, Y H:i') }}">
                                                {{ Str::limit($inquiry->message, 24) }}
                                            </button>
                                            <button type="button" class="view-inquiry-btn shrink-0 inline-flex items-center gap-1 rounded-full border border-[#d9cab3] bg-[#fbf8f3] px-2.5 py-1 text-[11px] font-bold text-[#1d3c34] transition hover:bg-[#dfeee4] hover:border-[#2d5d4d] shadow-xs cursor-pointer"
                                                data-id="{{ $inquiry->id }}"
                                                data-read="{{ $inquiry->read_at ? '1' : '0' }}"
                                                data-read-url="{{ route('dashboard.inquiries.read', $inquiry) }}"
                                                data-name="{{ $inquiry->name }}"
                                                data-email="{{ $inquiry->email }}"
                                                data-phone="{{ $inquiry->phone ?? '' }}"
                                                data-agent="{{ $inquiry->agent?->name ?? 'Admin / General' }}"
                                                data-subject="{{ $inquiry->subject }}"
                                                data-message="{{ $inquiry->message }}"
                                                data-received="{{ $inquiry->created_at?->format('M d, Y H:i') }}">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>View</span>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-500 whitespace-nowrap">{{ $inquiry->created_at?->format('M d, Y H:i') }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <form method="POST" action="{{ route('dashboard.inquiries.destroy', $inquiry) }}" data-confirm="Are you sure you want to delete this message?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1.5 text-xs font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mt-6 text-sm text-slate-500">No messages or inquiries yet.</p>
            @endif
        </section>

                <section id="settings" data-section="settings" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-[#f9f4ed] p-6 md:p-8 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#e7ddca] pb-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-[#1d3c34] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-white">Admin Console</span>
                        <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold text-[#1d3c34]">14 Section Studio</span>
                    </div>
                    <h2 class="mt-2 text-2xl md:text-3xl font-black text-[#1d3c34]">Site Settings &amp; Landing Page Studio</h2>
                    <p class="mt-1 text-sm text-slate-600">Select any section from the dropside menu to customize branding, texts, counters, amenities, and landing page content.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" form="site-settings-form" class="inline-flex items-center gap-2 rounded-full bg-[#1d3c34] px-6 py-3 text-sm font-bold text-white shadow-md transition hover:bg-[#254d43] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#1d3c34] focus:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save All Settings
                    </button>
                </div>
            </div>

            <!-- Mobile Dropside Selector (lg:hidden) -->
            <div class="mt-6 lg:hidden">
                <label for="settings-dropside-select" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">
                    Jump to Section (Dropside)
                </label>
                <div class="relative">
                    <select id="settings-dropside-select" class="w-full appearance-none rounded-xl border border-[#d9cab3] bg-white px-4 py-3.5 pr-10 text-sm font-bold text-[#1d3c34] shadow-xs focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                        <option value="panel-branding" selected>1. Site Branding &amp; Contact</option>
                        <option value="panel-hero">2. Homepage Hero &amp; Quick Search</option>
                        <option value="panel-performance">3. Performance Counter Bar</option>
                        <option value="panel-lifestyle">4. Lifestyle &amp; Master Amenities (8 Cards)</option>
                        <option value="panel-featured">5. Featured Properties Section</option>
                        <option value="panel-panorama">6. Architectural Panorama Banner</option>
                        <option value="panel-developments">7. Featured Projects &amp; Developments</option>
                        <option value="panel-latest">8. Latest Properties Section</option>
                        <option value="panel-about">9. About &amp; Heritage Section</option>
                        <option value="panel-news">10. Blog, News &amp; Careers</option>
                        <option value="panel-testimonials">11. Testimonials Section</option>
                        <option value="panel-agents">12. Agents Section</option>
                        <option value="panel-contact">13. VIP Contact &amp; Consultation Suite</option>
                        <option value="panel-newsletter">14. Newsletter &amp; Floating Concierge</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <form id="site-settings-form" method="POST" action="{{ route('dashboard.settings.update') }}" enctype="multipart/form-data" class="mt-6">
                @csrf
                <div class="grid lg:grid-cols-12 gap-6 items-start">
                    <!-- Dropside Navigation Sidebar (Desktop) -->
                    <aside class="hidden lg:block lg:col-span-4 xl:col-span-3 sticky top-6">
                        <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf8f3] p-3 shadow-xs">
                            <div class="px-3 py-2 border-b border-[#ebdcc8] mb-2 flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Dropside Navigation</span>
                                <span class="rounded-full bg-[#1d3c34]/10 px-2 py-0.5 text-[10px] font-bold text-[#1d3c34]">14 Sections</span>
                            </div>
                            <nav class="space-y-1 max-h-[calc(100vh-14rem)] overflow-y-auto pr-1">
                                <!-- 1. Site Branding -->
                                <button type="button" data-settings-target="panel-branding" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold transition bg-[#1d3c34] text-white shadow-sm">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <span class="truncate">Site Branding</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-white/20 text-white">Logo</span>
                                </button>

                                <!-- 2. Homepage Hero -->
                                <button type="button" data-settings-target="panel-hero" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                        <span class="truncate">Homepage Hero</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Hero</span>
                                </button>

                                <!-- 3. Performance -->
                                <button type="button" data-settings-target="panel-performance" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                        <span class="truncate">Performance</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Counters</span>
                                </button>

                                <!-- 4. Lifestyle -->
                                <button type="button" data-settings-target="panel-lifestyle" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7M9 3v2m6-2v2M4 7h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V9a2 2 0 012-2z"/></svg>
                                        <span class="truncate">Lifestyle &amp; Amenities</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">8 Cards</span>
                                </button>

                                <!-- 5. Featured Properties -->
                                <button type="button" data-settings-target="panel-featured" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                        <span class="truncate">Featured Properties</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Showcase</span>
                                </button>

                                <!-- 6. Panorama -->
                                <button type="button" data-settings-target="panel-panorama" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="truncate">Panorama Banner</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Skyline</span>
                                </button>

                                <!-- 7. Developments -->
                                <button type="button" data-settings-target="panel-developments" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                                        <span class="truncate">Developments &amp; Projects</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">3 Projects</span>
                                </button>

                                <!-- 8. Latest Properties -->
                                <button type="button" data-settings-target="panel-latest" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="truncate">Latest Properties</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Listings</span>
                                </button>

                                <!-- 9. About & Heritage -->
                                <button type="button" data-settings-target="panel-about" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        <span class="truncate">About &amp; Heritage</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Story</span>
                                </button>

                                <!-- 10. Blog & News -->
                                <button type="button" data-settings-target="panel-news" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                        <span class="truncate">Blog, News &amp; Careers</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">News</span>
                                </button>

                                <!-- 11. Testimonials -->
                                <button type="button" data-settings-target="panel-testimonials" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        <span class="truncate">Testimonials</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Reviews</span>
                                </button>

                                <!-- 12. Agents -->
                                <button type="button" data-settings-target="panel-agents" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span class="truncate">Agents</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Team</span>
                                </button>

                                <!-- 13. VIP Contact Suite -->
                                <button type="button" data-settings-target="panel-contact" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span class="truncate">VIP Contact Suite</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">VIP</span>
                                </button>

                                <!-- 14. Newsletter & Concierge -->
                                <button type="button" data-settings-target="panel-newsletter" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                        <span class="truncate">Newsletter &amp; Dock</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Footer</span>
                                </button>
                            </nav>

                            <div class="pt-3 mt-2 border-t border-[#ebdcc8]">
                                <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#1d3c34] px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-[#254d43] transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Save All Settings
                                </button>
                            </div>
                        </div>
                    </aside>

                    <!-- Panels Container (Right Column) -->
                    <div class="lg:col-span-8 xl:col-span-9 space-y-6">

                        <!-- PANEL 1: SITE BRANDING & CONTACT -->
                        <div id="panel-branding" data-settings-panel="panel-branding" class="settings-tab-panel space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 1 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Site Identity</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Site Branding &amp; Contact</h3>
                                    <p class="text-xs text-slate-600">Controls the primary logo, brand name, and direct communication channels.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
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
                                    <img src="{{ $siteBrand['logoUrl'] }}" alt="Current logo" class="mt-3 h-12 w-12 rounded-xl object-contain ring-1 ring-[#d9cab3] bg-white">
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
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <div></div>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-hero" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Homepage Hero &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 2: HOMEPAGE HERO & QUICK SEARCH -->
                        <div id="panel-hero" data-settings-panel="panel-hero" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 2 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Hero Section</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Homepage Content — Hero Banner &amp; Quick Search</h3>
                                    <p class="text-xs text-slate-600">Customize the main hero title, description, call-to-action buttons, counters, and search filter labels.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Badge</label>
                                    <input type="text" name="hero_badge" value="{{ old('hero_badge', \App\Models\SiteSetting::get('hero_badge', 'Premium living spaces')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Title</label>
                                    <input type="text" name="hero_title" value="{{ old('hero_title', \App\Models\SiteSetting::get('hero_title', 'Discover homes that feel like your next chapter.')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Description</label>
                                    <textarea name="hero_description" rows="2" maxlength="1500" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('hero_description', \App\Models\SiteSetting::get('hero_description', 'Find luxury villas, modern apartments, and smart investments in trusted neighborhoods with a team that knows the market deeply.')) }}</textarea>
                                </div>

                                <!-- Hero Background Static Image Control -->
                                <div class="md:col-span-2 rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-5 shadow-xs">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div>
                                            <label class="block text-sm font-bold text-[#1d3c34]">Hero Background Image</label>
                                            <p class="text-xs text-slate-500">High-resolution cinematic background image displayed behind the hero banner.</p>
                                        </div>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Hero Background Image</span>
                                    </div>
                                    <div class="mt-4 flex flex-col md:flex-row items-start md:items-center gap-5">
                                        <div class="relative h-24 w-40 shrink-0 overflow-hidden rounded-xl border border-[#d9cab3] bg-white shadow-xs">
                                            <img id="preview-hero-image" src="{{ \App\Models\SiteSetting::imageUrl('hero_image', 'images/luxury/hero-skyline.jpg') }}" alt="Hero Background Preview" class="h-full w-full object-cover">
                                        </div>
                                        <div class="flex-1 space-y-2 w-full">
                                            <div class="flex items-center gap-3">
                                                <label for="hero-image-input" class="cursor-pointer rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-[#254d43]">
                                                    Upload New Image
                                                </label>
                                                <span id="hero-image-name" class="truncate text-xs text-slate-500">Current image in use</span>
                                            </div>
                                            <input id="hero-image-input" type="file" name="hero_image_file" accept="image/*" class="sr-only"
                                                onchange="document.getElementById('hero-image-name').textContent = this.files.length ? this.files[0].name : 'Current image in use'; if (this.files && this.files[0]) { document.getElementById('preview-hero-image').src = URL.createObjectURL(this.files[0]); }">
                                            <div class="flex items-center gap-2 pt-1">
                                                <span class="text-xs text-slate-500 whitespace-nowrap">Or Image URL:</span>
                                                <input type="text" name="hero_image" value="{{ old('hero_image', \App\Models\SiteSetting::get('hero_image')) }}" placeholder="e.g. https://... or images/luxury/hero-skyline.jpg" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Primary Button Text</label>
                                    <input type="text" name="hero_primary_button_text" value="{{ old('hero_primary_button_text', \App\Models\SiteSetting::get('hero_primary_button_text', 'Explore Listings')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Secondary Button Text</label>
                                    <input type="text" name="hero_secondary_button_text" value="{{ old('hero_secondary_button_text', \App\Models\SiteSetting::get('hero_secondary_button_text', 'Book a Visit')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Careers Link Text</label>
                                    <input type="text" name="hero_careers_link_text" value="{{ old('hero_careers_link_text', \App\Models\SiteSetting::get('hero_careers_link_text', 'Careers &rarr;')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Featured Property Card Badge</label>
                                    <input type="text" name="hero_featured_badge" value="{{ old('hero_featured_badge', \App\Models\SiteSetting::get('hero_featured_badge', 'Featured property')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Stat 1 Label</label>
                                    <input type="text" name="hero_stat_1_label" value="{{ old('hero_stat_1_label', \App\Models\SiteSetting::get('hero_stat_1_label', 'happy buyers')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Stat 2 Label</label>
                                    <input type="text" name="hero_stat_2_label" value="{{ old('hero_stat_2_label', \App\Models\SiteSetting::get('hero_stat_2_label', 'properties sold')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Stat 3 Value</label>
                                    <input type="text" name="hero_stat_3_value" value="{{ old('hero_stat_3_value', \App\Models\SiteSetting::get('hero_stat_3_value', '18 yrs')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Stat 3 Label</label>
                                    <input type="text" name="hero_stat_3_label" value="{{ old('hero_stat_3_label', \App\Models\SiteSetting::get('hero_stat_3_label', 'market expertise')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Search Filter: Category Label</label>
                                    <input type="text" name="filter_category_label" value="{{ old('filter_category_label', \App\Models\SiteSetting::get('filter_category_label', 'Residence Category')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Search Filter: Type Label</label>
                                    <input type="text" name="filter_type_label" value="{{ old('filter_type_label', \App\Models\SiteSetting::get('filter_type_label', 'Ownership Type')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Search Filter: Location Label</label>
                                    <input type="text" name="filter_location_label" value="{{ old('filter_location_label', \App\Models\SiteSetting::get('filter_location_label', 'Preferred Location')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Search Filter: Submit Button Text</label>
                                    <input type="text" name="filter_button_text" value="{{ old('filter_button_text', \App\Models\SiteSetting::get('filter_button_text', 'Search Properties')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-branding" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Site Branding
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-performance" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Performance &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 3: PERFORMANCE COUNTERS -->
                        <div id="panel-performance" data-settings-panel="panel-performance" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 3 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Counter Section</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Performance Counter Bar</h3>
                                    <p class="text-xs text-slate-600">Control the figures and text labels displayed across the 4 credibility boxes below the hero.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Counter 1 Value</label>
                                    <input type="text" name="counter_1_value" value="{{ old('counter_1_value', \App\Models\SiteSetting::get('counter_1_value', 'ETB 2.8B+')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Counter 1 Label</label>
                                    <input type="text" name="counter_1_label" value="{{ old('counter_1_label', \App\Models\SiteSetting::get('counter_1_label', 'Property Volume')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Counter 2 Label (Properties Sold)</label>
                                    <input type="text" name="counter_2_label" value="{{ old('counter_2_label', \App\Models\SiteSetting::get('counter_2_label', 'properties sold')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Counter 3 Label (Happy Buyers)</label>
                                    <input type="text" name="counter_3_label" value="{{ old('counter_3_label', \App\Models\SiteSetting::get('counter_3_label', 'happy buyers')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Counter 4 Value</label>
                                    <input type="text" name="counter_4_value" value="{{ old('counter_4_value', \App\Models\SiteSetting::get('counter_4_value', '18 Yrs')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Counter 4 Label</label>
                                    <input type="text" name="counter_4_label" value="{{ old('counter_4_label', \App\Models\SiteSetting::get('counter_4_label', 'market expertise')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Happy Buyers Base Count Offset</label>
                                    <p class="mb-2 text-xs text-slate-500">Baseline number added to live newsletter email subscribers (Current total: {{ (int) \App\Models\SiteSetting::get('happy_buyers_base_count', 0) + $subscribers->count() }} happy buyers).</p>
                                    <input type="number" name="happy_buyers_base_count" min="0" value="{{ old('happy_buyers_base_count', \App\Models\SiteSetting::get('happy_buyers_base_count', 0)) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-hero" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Homepage Hero
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-lifestyle" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Lifestyle &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 4: LIFESTYLE & AMENITIES (8 CARDS) -->
                        <div id="panel-lifestyle" data-settings-panel="panel-lifestyle" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 4 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Amenities Section</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Lifestyle &amp; Master-Planned Amenities (La Gare Inspired)</h3>
                                    <p class="text-xs text-slate-600">Customize the section title, description, and every single title and description for the 8 lifestyle amenity cards.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Amenities Section Badge</label>
                                    <input type="text" name="amenities_badge" value="{{ old('amenities_badge', \App\Models\SiteSetting::get('amenities_badge', 'Master-Planned Excellence')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Amenities Section Title</label>
                                    <input type="text" name="amenities_title" value="{{ old('amenities_title', \App\Models\SiteSetting::get('amenities_title', 'A Curated Lifestyle Beyond Ordinary')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Amenities Section Description</label>
                                    <textarea name="amenities_description" rows="2" maxlength="1500" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('amenities_description', \App\Models\SiteSetting::get('amenities_description', 'Inspired by premier world-class master communities like La Gare, every residence is enveloped in pristine green courtyards, luxury retail boulevards, and total peace of mind.')) }}</textarea>
                                </div>

                                {{-- Amenity 1 & 2 --}}
                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 1</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_1_title" value="{{ old('amenity_1_title', \App\Models\SiteSetting::get('amenity_1_title', 'Jogging & Walking Tracks')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_1_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_1_description', \App\Models\SiteSetting::get('amenity_1_description', 'Shaded, tree-lined pedestrian circuits and safe morning running tracks weaving through the development.')) }}</textarea>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 2</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_2_title" value="{{ old('amenity_2_title', \App\Models\SiteSetting::get('amenity_2_title', 'Lush Parks & Courtyards')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_2_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_2_description', \App\Models\SiteSetting::get('amenity_2_description', 'Manicured botanical gardens, peaceful open courtyards, and tranquil green sanctuaries for relaxation.')) }}</textarea>
                                    </div>
                                </div>

                                {{-- Amenity 3 & 4 --}}
                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 3</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_3_title" value="{{ old('amenity_3_title', \App\Models\SiteSetting::get('amenity_3_title', 'Retail & Dining Boulevard')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_3_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_3_description', \App\Models\SiteSetting::get('amenity_3_description', 'Curated fashion boutiques, specialty cafes, and gourmet international restaurants steps from your lobby.')) }}</textarea>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 4</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_4_title" value="{{ old('amenity_4_title', \App\Models\SiteSetting::get('amenity_4_title', 'Dedicated Cycling Tracks')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_4_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_4_description', \App\Models\SiteSetting::get('amenity_4_description', 'Protected, car-free cycling corridors connecting all residential clusters, green hubs, and community facilities.')) }}</textarea>
                                    </div>
                                </div>

                                {{-- Amenity 5 & 6 --}}
                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 5</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_5_title" value="{{ old('amenity_5_title', \App\Models\SiteSetting::get('amenity_5_title', 'Kids Play & Adventure')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_5_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_5_description', \App\Models\SiteSetting::get('amenity_5_description', 'Safe, modern play areas, soft-turf family recreation zones, and interactive splash fountains.')) }}</textarea>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 6</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_6_title" value="{{ old('amenity_6_title', \App\Models\SiteSetting::get('amenity_6_title', '24/7 Gated Security & Concierge')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_6_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_6_description', \App\Models\SiteSetting::get('amenity_6_description', 'Multi-tier biometric access, discrete on-site security patrols, CCTV surveillance, and VIP reception.')) }}</textarea>
                                    </div>
                                </div>

                                {{-- Amenity 7 & 8 --}}
                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 7</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_7_title" value="{{ old('amenity_7_title', \App\Models\SiteSetting::get('amenity_7_title', 'Wellness & Fitness Club')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_7_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_7_description', \App\Models\SiteSetting::get('amenity_7_description', 'State-of-the-art gym, restorative sauna and steam facilities, outdoor yoga deck, and swimming pool.')) }}</textarea>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 8</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_8_title" value="{{ old('amenity_8_title', \App\Models\SiteSetting::get('amenity_8_title', '100% Power & Water Backup')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_8_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_8_description', \App\Models\SiteSetting::get('amenity_8_description', 'Heavy-duty industrial generators and deep-well water reserve tanks guarantee zero disruption to your daily life.')) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-performance" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Performance
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-featured" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Featured Properties &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 5: FEATURED PROPERTIES -->
                        <div id="panel-featured" data-settings-panel="panel-featured" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 5 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Featured Properties</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Featured Properties Section</h3>
                                    <p class="text-xs text-slate-600">Header texts for the Featured Properties showcase.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Section Badge</label>
                                    <input type="text" name="featured_properties_badge" value="{{ old('featured_properties_badge', \App\Models\SiteSetting::get('featured_properties_badge', 'Properties')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Section Title</label>
                                    <input type="text" name="featured_properties_title" value="{{ old('featured_properties_title', \App\Models\SiteSetting::get('featured_properties_title', 'Featured Properties')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Link Text</label>
                                    <input type="text" name="featured_properties_link_text" value="{{ old('featured_properties_link_text', \App\Models\SiteSetting::get('featured_properties_link_text', 'View all')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-lifestyle" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Lifestyle
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-panorama" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Panorama &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 6: ARCHITECTURAL PANORAMA -->
                        <div id="panel-panorama" data-settings-panel="panel-panorama" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 6 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Panorama Banner</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Architectural Panorama Banner</h3>
                                    <p class="text-xs text-slate-600">Control the magnificent views visual banner, text, and VIP action buttons.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Panorama Badge</label>
                                    <input type="text" name="panorama_badge" value="{{ old('panorama_badge', \App\Models\SiteSetting::get('panorama_badge', 'Masterplan Skyline')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Panorama Title</label>
                                    <input type="text" name="panorama_title" value="{{ old('panorama_title', \App\Models\SiteSetting::get('panorama_title', 'Get Inspired by Magnificent Views & Elevated Living')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Panorama Description</label>
                                    <textarea name="panorama_description" rows="2" maxlength="1500" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('panorama_description', \App\Models\SiteSetting::get('panorama_description', 'Experience panoramic sunrises, sprawling green courtyards, and iconic architecture engineered to the highest international quality standards.')) }}</textarea>
                                </div>

                                <!-- Panorama Background Static Image Control -->
                                <div class="md:col-span-2 rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-5 shadow-xs">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div>
                                            <label class="block text-sm font-bold text-[#1d3c34]">Panorama Banner Background Image</label>
                                            <p class="text-xs text-slate-500">Wide architectural skyline photograph displayed across the full-width panorama section.</p>
                                        </div>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Panorama Image</span>
                                    </div>
                                    <div class="mt-4 flex flex-col md:flex-row items-start md:items-center gap-5">
                                        <div class="relative h-24 w-40 shrink-0 overflow-hidden rounded-xl border border-[#d9cab3] bg-white shadow-xs">
                                            <img id="preview-panorama-image" src="{{ \App\Models\SiteSetting::imageUrl('panorama_image', 'images/luxury/central-plaza.jpg') }}" alt="Panorama Preview" class="h-full w-full object-cover">
                                        </div>
                                        <div class="flex-1 space-y-2 w-full">
                                            <div class="flex items-center gap-3">
                                                <label for="panorama-image-input" class="cursor-pointer rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-[#254d43]">
                                                    Upload New Image
                                                </label>
                                                <span id="panorama-image-name" class="truncate text-xs text-slate-500">Current image in use</span>
                                            </div>
                                            <input id="panorama-image-input" type="file" name="panorama_image_file" accept="image/*" class="sr-only"
                                                onchange="document.getElementById('panorama-image-name').textContent = this.files.length ? this.files[0].name : 'Current image in use'; if (this.files && this.files[0]) { document.getElementById('preview-panorama-image').src = URL.createObjectURL(this.files[0]); }">
                                            <div class="flex items-center gap-2 pt-1">
                                                <span class="text-xs text-slate-500 whitespace-nowrap">Or Image URL:</span>
                                                <input type="text" name="panorama_image" value="{{ old('panorama_image', \App\Models\SiteSetting::get('panorama_image')) }}" placeholder="e.g. https://... or images/luxury/central-plaza.jpg" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Primary Button Text</label>
                                    <input type="text" name="panorama_primary_button_text" value="{{ old('panorama_primary_button_text', \App\Models\SiteSetting::get('panorama_primary_button_text', 'Schedule Private Tour')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Secondary Button Text</label>
                                    <input type="text" name="panorama_secondary_button_text" value="{{ old('panorama_secondary_button_text', \App\Models\SiteSetting::get('panorama_secondary_button_text', 'View Masterplan Units')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-featured" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Featured Properties
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-developments" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Developments &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 7: DEVELOPMENTS & PROJECTS -->
                        <div id="panel-developments" data-settings-panel="panel-developments" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 7 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Developments</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Featured Projects &amp; Developments</h3>
                                    <p class="text-xs text-slate-600">Manage the Developments section header and individual project cards (Emerald Heights, Harar Square, Oakland Park).</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Section Badge</label>
                                    <input type="text" name="developments_badge" value="{{ old('developments_badge', \App\Models\SiteSetting::get('developments_badge', 'Developments')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Section Title</label>
                                    <input type="text" name="developments_title" value="{{ old('developments_title', \App\Models\SiteSetting::get('developments_title', 'Featured Projects')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Link Text</label>
                                    <input type="text" name="developments_link_text" value="{{ old('developments_link_text', \App\Models\SiteSetting::get('developments_link_text', 'Explore projects')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Link URL</label>
                                    <input type="text" name="developments_link_url" value="{{ old('developments_link_url', \App\Models\SiteSetting::get('developments_link_url', '/projects')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <!-- Project 1 (Emerald Heights) -->
                                <div class="md:col-span-2 rounded-2xl border border-[#d9cab3] bg-[#fcfaf7] p-5 shadow-xs">
                                    <h4 class="text-base font-bold text-[#1d3c34]">Project 1 (Luxury Card)</h4>
                                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-sm font-semibold text-slate-700">Category Tag</label>
                                            <input type="text" name="project_1_tag" value="{{ old('project_1_tag', \App\Models\SiteSetting::get('project_1_tag', 'Luxury')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-semibold text-slate-700">Project Title</label>
                                            <input type="text" name="project_1_title" value="{{ old('project_1_title', \App\Models\SiteSetting::get('project_1_title', 'Emerald Heights')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                                            <textarea name="project_1_description" rows="2" maxlength="1000" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('project_1_description', \App\Models\SiteSetting::get('project_1_description', 'Residential living designed around comfort, green views, and daily convenience.')) }}</textarea>
                                        </div>
                                        <div class="md:col-span-2 pt-2 border-t border-[#ebdcc8]">
                                            <label class="mb-1 block text-xs font-bold text-[#1d3c34]">Project 1 Card Image</label>
                                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                                <div class="relative h-16 w-28 shrink-0 overflow-hidden rounded-xl border border-[#d9cab3] bg-white shadow-xs">
                                                    <img id="preview-project-1-image" src="{{ \App\Models\SiteSetting::imageUrl('project_1_image', 'images/luxury/luxury-towers.jpg') }}" alt="Project 1 Preview" class="h-full w-full object-cover">
                                                </div>
                                                <div class="flex-1 space-y-1.5 w-full">
                                                    <div class="flex items-center gap-3">
                                                        <label for="project-1-image-input" class="cursor-pointer rounded-full bg-[#1d3c34] px-3.5 py-1.5 text-xs font-bold text-white shadow-xs transition hover:bg-[#254d43]">
                                                            Upload Image
                                                        </label>
                                                        <span id="project-1-image-name" class="truncate text-xs text-slate-500">Current image in use</span>
                                                    </div>
                                                    <input id="project-1-image-input" type="file" name="project_1_image_file" accept="image/*" class="sr-only"
                                                        onchange="document.getElementById('project-1-image-name').textContent = this.files.length ? this.files[0].name : 'Current image in use'; if (this.files && this.files[0]) { document.getElementById('preview-project-1-image').src = URL.createObjectURL(this.files[0]); }">
                                                    <input type="text" name="project_1_image" value="{{ old('project_1_image', \App\Models\SiteSetting::get('project_1_image')) }}" placeholder="Or paste image URL / path (e.g. images/luxury/luxury-towers.jpg)" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Project 2 (Harar Square) -->
                                <div class="md:col-span-2 rounded-2xl border border-[#d9cab3] bg-[#fcfaf7] p-5 shadow-xs">
                                    <h4 class="text-base font-bold text-[#1d3c34]">Project 2 (Commercial Card)</h4>
                                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-sm font-semibold text-slate-700">Category Tag</label>
                                            <input type="text" name="project_2_tag" value="{{ old('project_2_tag', \App\Models\SiteSetting::get('project_2_tag', 'Commercial')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-semibold text-slate-700">Project Title</label>
                                            <input type="text" name="project_2_title" value="{{ old('project_2_title', \App\Models\SiteSetting::get('project_2_title', 'Harar Square')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                                            <textarea name="project_2_description" rows="2" maxlength="1000" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('project_2_description', \App\Models\SiteSetting::get('project_2_description', 'Mixed-use development and retail spaces scheduled for the next growth corridor.')) }}</textarea>
                                        </div>
                                        <div class="md:col-span-2 pt-2 border-t border-[#ebdcc8]">
                                            <label class="mb-1 block text-xs font-bold text-[#1d3c34]">Project 2 Card Image</label>
                                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                                <div class="relative h-16 w-28 shrink-0 overflow-hidden rounded-xl border border-[#d9cab3] bg-white shadow-xs">
                                                    <img id="preview-project-2-image" src="{{ \App\Models\SiteSetting::imageUrl('project_2_image', 'images/luxury/retail-boulevard.jpg') }}" alt="Project 2 Preview" class="h-full w-full object-cover">
                                                </div>
                                                <div class="flex-1 space-y-1.5 w-full">
                                                    <div class="flex items-center gap-3">
                                                        <label for="project-2-image-input" class="cursor-pointer rounded-full bg-[#1d3c34] px-3.5 py-1.5 text-xs font-bold text-white shadow-xs transition hover:bg-[#254d43]">
                                                            Upload Image
                                                        </label>
                                                        <span id="project-2-image-name" class="truncate text-xs text-slate-500">Current image in use</span>
                                                    </div>
                                                    <input id="project-2-image-input" type="file" name="project_2_image_file" accept="image/*" class="sr-only"
                                                        onchange="document.getElementById('project-2-image-name').textContent = this.files.length ? this.files[0].name : 'Current image in use'; if (this.files && this.files[0]) { document.getElementById('preview-project-2-image').src = URL.createObjectURL(this.files[0]); }">
                                                    <input type="text" name="project_2_image" value="{{ old('project_2_image', \App\Models\SiteSetting::get('project_2_image')) }}" placeholder="Or paste image URL / path (e.g. images/luxury/retail-boulevard.jpg)" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Project 3 (Oakland Park) -->
                                <div class="md:col-span-2 rounded-2xl border border-[#d9cab3] bg-[#fcfaf7] p-5 shadow-xs">
                                    <h4 class="text-base font-bold text-[#1d3c34]">Project 3 (Future Plan Card)</h4>
                                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-sm font-semibold text-slate-700">Category Tag</label>
                                            <input type="text" name="project_3_tag" value="{{ old('project_3_tag', \App\Models\SiteSetting::get('project_3_tag', 'Future plan')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-semibold text-slate-700">Project Title</label>
                                            <input type="text" name="project_3_title" value="{{ old('project_3_title', \App\Models\SiteSetting::get('project_3_title', 'Oakland Park')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                                            <textarea name="project_3_description" rows="2" maxlength="1000" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('project_3_description', \App\Models\SiteSetting::get('project_3_description', 'A connected master-planned community focused on smart, sustainable growth.')) }}</textarea>
                                        </div>
                                        <div class="md:col-span-2 pt-2 border-t border-[#ebdcc8]">
                                            <label class="mb-1 block text-xs font-bold text-[#1d3c34]">Project 3 Card Image</label>
                                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                                <div class="relative h-16 w-28 shrink-0 overflow-hidden rounded-xl border border-[#d9cab3] bg-white shadow-xs">
                                                    <img id="preview-project-3-image" src="{{ \App\Models\SiteSetting::imageUrl('project_3_image', 'images/luxury/panoramic-park.jpg') }}" alt="Project 3 Preview" class="h-full w-full object-cover">
                                                </div>
                                                <div class="flex-1 space-y-1.5 w-full">
                                                    <div class="flex items-center gap-3">
                                                        <label for="project-3-image-input" class="cursor-pointer rounded-full bg-[#1d3c34] px-3.5 py-1.5 text-xs font-bold text-white shadow-xs transition hover:bg-[#254d43]">
                                                            Upload Image
                                                        </label>
                                                        <span id="project-3-image-name" class="truncate text-xs text-slate-500">Current image in use</span>
                                                    </div>
                                                    <input id="project-3-image-input" type="file" name="project_3_image_file" accept="image/*" class="sr-only"
                                                        onchange="document.getElementById('project-3-image-name').textContent = this.files.length ? this.files[0].name : 'Current image in use'; if (this.files && this.files[0]) { document.getElementById('preview-project-3-image').src = URL.createObjectURL(this.files[0]); }">
                                                    <input type="text" name="project_3_image" value="{{ old('project_3_image', \App\Models\SiteSetting::get('project_3_image')) }}" placeholder="Or paste image URL / path (e.g. images/luxury/panoramic-park.jpg)" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-panorama" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Panorama
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-latest" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Latest Properties &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 8: LATEST PROPERTIES -->
                        <div id="panel-latest" data-settings-panel="panel-latest" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 8 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Latest Listings</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Latest Properties Section</h3>
                                    <p class="text-xs text-slate-600">Header texts for the Latest Properties section.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Section Badge</label>
                                    <input type="text" name="latest_properties_badge" value="{{ old('latest_properties_badge', \App\Models\SiteSetting::get('latest_properties_badge', 'New listings')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Section Title</label>
                                    <input type="text" name="latest_properties_title" value="{{ old('latest_properties_title', \App\Models\SiteSetting::get('latest_properties_title', 'Latest Properties')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Link Text</label>
                                    <input type="text" name="latest_properties_link_text" value="{{ old('latest_properties_link_text', \App\Models\SiteSetting::get('latest_properties_link_text', 'Browse more')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-developments" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Developments
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-about" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: About &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 9: ABOUT & HERITAGE -->
                        <div id="panel-about" data-settings-panel="panel-about" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 9 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">About Section</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">About &amp; Heritage Section</h3>
                                    <p class="text-xs text-slate-600">About section story, pillars, and image caption.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">About Badge</label>
                                    <input type="text" name="about_badge" value="{{ old('about_badge', \App\Models\SiteSetting::get('about_badge', 'About '.($siteBrand['name'] ?? 'GTM Real Estate'))) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">About Title</label>
                                    <input type="text" name="about_title" value="{{ old('about_title', \App\Models\SiteSetting::get('about_title', 'Trusted guidance for every move')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">About Description</label>
                                    <textarea name="about_description" rows="3" maxlength="2000" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('about_description', \App\Models\SiteSetting::get('about_description', 'We help buyers, sellers, and investors discover exceptional properties with trust, transparency, and local expertise. From first viewing to final paperwork, we make every step clear and confident.')) }}</textarea>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Pillar 1 Title</label>
                                    <input type="text" name="about_pillar_1_title" value="{{ old('about_pillar_1_title', \App\Models\SiteSetting::get('about_pillar_1_title', '100%')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Pillar 1 Description</label>
                                    <input type="text" name="about_pillar_1_desc" value="{{ old('about_pillar_1_desc', \App\Models\SiteSetting::get('about_pillar_1_desc', 'Clear Legal Titles')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Pillar 2 Title</label>
                                    <input type="text" name="about_pillar_2_title" value="{{ old('about_pillar_2_title', \App\Models\SiteSetting::get('about_pillar_2_title', 'VIP')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Pillar 2 Description</label>
                                    <input type="text" name="about_pillar_2_desc" value="{{ old('about_pillar_2_desc', \App\Models\SiteSetting::get('about_pillar_2_desc', 'Concierge Advisory')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Image Subtitle</label>
                                    <input type="text" name="about_image_subtitle" value="{{ old('about_image_subtitle', \App\Models\SiteSetting::get('about_image_subtitle', 'Heritage of Distinction')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Image Title</label>
                                    <input type="text" name="about_image_title" value="{{ old('about_image_title', \App\Models\SiteSetting::get('about_image_title', 'Addis Ababa & Harar Master Developments')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <!-- About Showcase Static Image Control -->
                                <div class="md:col-span-2 rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-5 shadow-xs">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div>
                                            <label class="block text-sm font-bold text-[#1d3c34]">About Heritage Showcase Image</label>
                                            <p class="text-xs text-slate-500">The photograph displayed in the luxury showcase card next to the About story.</p>
                                        </div>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">About Photo</span>
                                    </div>
                                    <div class="mt-4 flex flex-col md:flex-row items-start md:items-center gap-5">
                                        <div class="relative h-24 w-40 shrink-0 overflow-hidden rounded-xl border border-[#d9cab3] bg-white shadow-xs">
                                            <img id="preview-about-image" src="{{ \App\Models\SiteSetting::imageUrl('about_image', 'images/luxury/retail-boulevard.jpg') }}" alt="About Preview" class="h-full w-full object-cover">
                                        </div>
                                        <div class="flex-1 space-y-2 w-full">
                                            <div class="flex items-center gap-3">
                                                <label for="about-image-input" class="cursor-pointer rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-[#254d43]">
                                                    Upload New Image
                                                </label>
                                                <span id="about-image-name" class="truncate text-xs text-slate-500">Current image in use</span>
                                            </div>
                                            <input id="about-image-input" type="file" name="about_image_file" accept="image/*" class="sr-only"
                                                onchange="document.getElementById('about-image-name').textContent = this.files.length ? this.files[0].name : 'Current image in use'; if (this.files && this.files[0]) { document.getElementById('preview-about-image').src = URL.createObjectURL(this.files[0]); }">
                                            <div class="flex items-center gap-2 pt-1">
                                                <span class="text-xs text-slate-500 whitespace-nowrap">Or Image URL:</span>
                                                <input type="text" name="about_image" value="{{ old('about_image', \App\Models\SiteSetting::get('about_image')) }}" placeholder="e.g. https://... or images/luxury/retail-boulevard.jpg" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-latest" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Latest Properties
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-news" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Blog &amp; News &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 10: BLOG, NEWS & CAREERS -->
                        <div id="panel-news" data-settings-panel="panel-news" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 10 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Blog &amp; News</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Blog, News &amp; Careers</h3>
                                    <p class="text-xs text-slate-600">Configure editorial badge, title, and link text.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">News Badge</label>
                                    <input type="text" name="news_badge" value="{{ old('news_badge', \App\Models\SiteSetting::get('news_badge', 'Blog & News')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">News Title</label>
                                    <input type="text" name="news_title" value="{{ old('news_title', \App\Models\SiteSetting::get('news_title', 'Latest News')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">View All Link Text</label>
                                    <input type="text" name="news_link_text" value="{{ old('news_link_text', \App\Models\SiteSetting::get('news_link_text', 'View all')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-about" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: About &amp; Heritage
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-testimonials" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Testimonials &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 11: TESTIMONIALS -->
                        <div id="panel-testimonials" data-settings-panel="panel-testimonials" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 11 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Testimonials</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Testimonials Section</h3>
                                    <p class="text-xs text-slate-600">Client review banner badge and title.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Testimonials Badge</label>
                                    <input type="text" name="testimonials_badge" value="{{ old('testimonials_badge', \App\Models\SiteSetting::get('testimonials_badge', 'Client feedback')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Testimonials Title</label>
                                    <input type="text" name="testimonials_title" value="{{ old('testimonials_title', \App\Models\SiteSetting::get('testimonials_title', 'Testimonials')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-news" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Blog &amp; News
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-agents" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Agents &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 12: AGENTS -->
                        <div id="panel-agents" data-settings-panel="panel-agents" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 12 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Agents</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Agents Section</h3>
                                    <p class="text-xs text-slate-600">Configure team showcase badges and titles.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Agents Badge</label>
                                    <input type="text" name="agents_badge" value="{{ old('agents_badge', \App\Models\SiteSetting::get('agents_badge', 'Our agents')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Agents Title</label>
                                    <input type="text" name="agents_title" value="{{ old('agents_title', \App\Models\SiteSetting::get('agents_title', 'Real people, local expertise')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Agents Link Text</label>
                                    <input type="text" name="agents_link_text" value="{{ old('agents_link_text', \App\Models\SiteSetting::get('agents_link_text', 'All agents')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-testimonials" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Testimonials
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-contact" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: VIP Contact &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 13: VIP CONTACT SUITE -->
                        <div id="panel-contact" data-settings-panel="panel-contact" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 13 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Contact Section</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">VIP Contact &amp; Consultation Suite</h3>
                                    <p class="text-xs text-slate-600">Heading, descriptive copy, benefit bullet points, and button text.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Badge</label>
                                    <input type="text" name="contact_badge" value="{{ old('contact_badge', \App\Models\SiteSetting::get('contact_badge', 'Contact')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Title</label>
                                    <input type="text" name="contact_title" value="{{ old('contact_title', \App\Models\SiteSetting::get('contact_title', 'Let’s find your next address')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Description</label>
                                    <textarea name="contact_description" rows="2" maxlength="1500" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">{{ old('contact_description', \App\Models\SiteSetting::get('contact_description', 'Register your interest to schedule a private tour or discuss prime residential and commercial investment opportunities.')) }}</textarea>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Side Box Title</label>
                                    <input type="text" name="contact_box_title" value="{{ old('contact_box_title', \App\Models\SiteSetting::get('contact_box_title', 'Speak with an agent')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Submit Button Text</label>
                                    <input type="text" name="contact_submit_text" value="{{ old('contact_submit_text', \App\Models\SiteSetting::get('contact_submit_text', 'Send Message')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Side Box Description</label>
                                    <textarea name="contact_box_description" rows="2" maxlength="1500" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">{{ old('contact_box_description', \App\Models\SiteSetting::get('contact_box_description', 'Share your property goals and our senior consultants will prepare a curated portfolio matching your requirements, preferred timing, and financing criteria.')) }}</textarea>
                                </div>

                                <div class="md:col-span-2 space-y-3">
                                    <label class="block text-sm font-semibold text-slate-700">3 Trust / Guarantee Checklist Points</label>
                                    <input type="text" name="contact_benefit_1" value="{{ old('contact_benefit_1', \App\Models\SiteSetting::get('contact_benefit_1', 'Dedicated VIP Property Consultant assigned immediately')) }}" placeholder="Benefit 1" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none text-sm">
                                    <input type="text" name="contact_benefit_2" value="{{ old('contact_benefit_2', \App\Models\SiteSetting::get('contact_benefit_2', 'On-site or virtual architectural masterplan walkthrough')) }}" placeholder="Benefit 2" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none text-sm">
                                    <input type="text" name="contact_benefit_3" value="{{ old('contact_benefit_3', \App\Models\SiteSetting::get('contact_benefit_3', 'Full verified title deed transparency guaranteed')) }}" placeholder="Benefit 3" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none text-sm">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-agents" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Agents
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-newsletter" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Newsletter &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 14: NEWSLETTER & FLOATING CONCIERGE -->
                        <div id="panel-newsletter" data-settings-panel="panel-newsletter" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 14 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Footer &amp; Dock</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Newsletter &amp; Floating Concierge</h3>
                                    <p class="text-xs text-slate-600">Newsletter subscription card texts and bottom-right floating VIP concierge button.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Newsletter Badge</label>
                                    <input type="text" name="newsletter_badge" value="{{ old('newsletter_badge', \App\Models\SiteSetting::get('newsletter_badge', 'Stay informed')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Newsletter Title</label>
                                    <input type="text" name="newsletter_title" value="{{ old('newsletter_title', \App\Models\SiteSetting::get('newsletter_title', 'Newsletter')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Newsletter Description</label>
                                    <textarea name="newsletter_description" rows="2" maxlength="1500" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">{{ old('newsletter_description', \App\Models\SiteSetting::get('newsletter_description', 'Receive exclusive off-market previews, masterplan phase launches, and quarterly Ethiopian property intelligence.')) }}</textarea>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Newsletter Input Placeholder</label>
                                    <input type="text" name="newsletter_placeholder" value="{{ old('newsletter_placeholder', \App\Models\SiteSetting::get('newsletter_placeholder', 'Enter your email')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Newsletter Submit Button Text</label>
                                    <input type="text" name="newsletter_button_text" value="{{ old('newsletter_button_text', \App\Models\SiteSetting::get('newsletter_button_text', 'Subscribe')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Floating Concierge Dock Button Text</label>
                                    <input type="text" name="concierge_button_text" value="{{ old('concierge_button_text', \App\Models\SiteSetting::get('concierge_button_text', 'VIP Tour')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-contact" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: VIP Contact
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </section>

        <section id="subscribers" data-section="subscribers" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[#1d3c34]">Newsletter Subscribers &amp; Happy Buyers</h2>
                    <p class="mt-1 text-sm text-slate-600">Clients who wrote their email and clicked subscribe on the home page. Total subscribers: <span class="font-bold text-[#1d3c34]">{{ $subscribers->count() }}</span> (Total displayed happy buyers: <span class="font-bold text-[#1d3c34]">{{ (int) \App\Models\SiteSetting::get('happy_buyers_base_count', 0) + $subscribers->count() }}</span>).</p>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca] bg-white">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#2d5d4d] text-[#f8f3eb]">
                        <tr>
                            <th class="px-5 py-4 font-semibold">#</th>
                            <th class="px-5 py-4 font-semibold">Subscriber Email</th>
                            <th class="px-5 py-4 font-semibold">IP Address</th>
                            <th class="px-5 py-4 font-semibold">Subscribed Date</th>
                            <th class="px-5 py-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e4cf]">
                        @forelse ($subscribers as $index => $subscriber)
                            <tr class="hover:bg-[#fbf7f0]">
                                <td class="px-5 py-4 font-semibold text-slate-500">{{ $index + 1 }}</td>
                                <td class="px-5 py-4 font-semibold text-[#1d3c34]">
                                    <a href="mailto:{{ $subscriber->email }}" class="hover:underline">{{ $subscriber->email }}</a>
                                </td>
                                <td class="px-5 py-4 text-slate-500">{{ $subscriber->ip_address ?? '—' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $subscriber->created_at?->format('M d, Y H:i') ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <form method="POST" action="{{ route('dashboard.subscribers.destroy', $subscriber) }}" data-confirm="Remove this subscriber?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1 text-xs font-semibold text-red-600 transition hover:bg-red-100 cursor-pointer">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-500">
                                    No subscribers yet. When clients submit their email in the newsletter subscription box, they will appear here.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
                                            <form method="POST" action="{{ route('dashboard.users.delete', $user) }}" data-confirm="Delete this user?">
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
                            <div class="mb-1 flex items-center justify-between">
                                <label class="block text-sm font-semibold text-slate-700">{{ $userToEdit ? 'New Password (optional)' : 'Password' }}</label>
                                <button type="button" onclick="generateStrongPassword('#user-form-password')" class="text-xs font-semibold text-[#1d3c34] hover:underline">
                                    Suggest Strong Password
                                </button>
                            </div>
                            <input id="user-form-password" type="password" name="password" minlength="8" {{ $userToEdit ? '' : 'required' }} class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <p class="mt-1 text-xs text-slate-500">Must be at least 8 characters with uppercase, lowercase, numbers, and symbols.</p>
                            @error('password')
                                <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Role</label>
                            <select name="role" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                @if ($userToEdit && $userToEdit->role === 'user')
                                    <option value="user" {{ old('role', $userToEdit->role) === 'user' ? 'selected' : '' }}>User (Legacy)</option>
                                @endif
                                <option value="agent" {{ old('role', $userToEdit?->role ?? 'agent') === 'agent' ? 'selected' : '' }}>Agent</option>
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
                    <div class="mb-1 flex items-center justify-between">
                        <label class="block text-sm font-semibold text-slate-700">New Password (optional)</label>
                        <button type="button" onclick="generateStrongPassword('#profile-form-password')" class="text-xs font-semibold text-[#1d3c34] hover:underline">
                            Suggest Strong Password
                        </button>
                    </div>
                    <input id="profile-form-password" type="password" name="password" minlength="8" placeholder="Leave blank to keep your current password" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    <p class="mt-1 text-xs text-slate-500">If changing, must be at least 8 characters with uppercase, lowercase, numbers, and symbols.</p>
                    @error('password')
                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
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
                @if (! $agentToEdit)
                    <button type="button" class="toggle-create-form rounded-full bg-[#1d3c34] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]" data-target="agents-create-panel" data-label-create="+ Add Agent" data-label-close="Close Form">+ Add Agent</button>
                @endif
            </div>

            {{-- Add / edit agent form (shown before the list when opened) --}}
            <div id="agents-create-panel" class="create-form-panel mt-6 rounded-[1.25rem] border border-[#d9cab3] bg-[#f9f4ed] p-5 {{ $agentToEdit ? '' : 'hidden' }}">
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
                        <div class="mb-1 flex items-center justify-between">
                            <label class="block text-sm font-semibold text-slate-700">Portal {{ $agentToEdit ? 'New Password (optional)' : 'Password (optional)' }}</label>
                            <button type="button" onclick="generateStrongPassword('#agent-form-password')" class="text-xs font-semibold text-[#1d3c34] hover:underline">
                                Suggest Strong Password
                            </button>
                        </div>
                        <input id="agent-form-password" type="password" name="password" minlength="8" placeholder="{{ $agentToEdit ? 'Leave blank to keep current' : 'Leave blank to auto-generate strong password' }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        <p class="mt-1 text-xs text-slate-500">Must be at least 8 characters with uppercase, lowercase, numbers, and symbols. If left blank when creating, a 16-character secure password is auto-generated.</p>
                        @error('password')
                            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Photo</label>
                        <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        @if ($agentToEdit && $agentToEdit->photo_path)
                            <img src="{{ asset('storage/'.$agentToEdit->photo_path) }}" alt="{{ $agentToEdit->name }}" class="mt-2 h-16 w-16 rounded-full object-cover ring-1 ring-[#d9cab3]">
                        @endif
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Commission Rate (%)</label>
                        <input type="number" name="commission_rate" value="{{ old('commission_rate', $agentToEdit?->commission_rate ?? 5) }}" min="0" max="100" step="0.01" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        <p class="mt-1 text-xs text-slate-500">Percentage of deal price the agent owes to admin per completed sale.</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Account Permission</label>
                        <input type="hidden" name="is_active" value="0">
                        <label class="mt-2 flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $agentToEdit ? $agentToEdit->is_active : true) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                            <span class="text-sm font-medium text-slate-700">Active (can login & access Agent Portal)</span>
                        </label>
                        <p class="mt-1 text-xs text-slate-500">When unchecked, agent is deactivated and blocked from portal login.</p>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                            {{ $agentToEdit ? 'Update Agent' : 'Add Agent' }}
                        </button>
                    </div>
                </form>
            </div>
            {{-- Agents Search Bar --}}
            <div class="mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#fbf8f3] p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" id="dashboard-agents-search" placeholder="Search agents by name, email, phone, bio, status..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2 pl-9 pr-8 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                        <button type="button" id="dashboard-agents-search-clear" class="hidden absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 transition" title="Clear search">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <span id="dashboard-agents-count" class="shrink-0 rounded-full bg-[#1d3c34]/10 px-3 py-1 text-xs font-bold text-[#1d3c34]">{{ $agents->count() }} agents</span>
                </div>
            </div>

            {{-- Agents list (shown first) --}}
            <div class="mt-4 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#1d3c34] text-[#f8f3eb]">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Photo</th>
                            <th class="px-5 py-4 font-semibold">Name</th>
                            <th class="px-5 py-4 font-semibold">Email</th>
                            <th class="px-5 py-4 font-semibold">Phone</th>
                            <th class="px-5 py-4 font-semibold">Title / Specialty</th>
                            <th class="px-5 py-4 font-semibold">Commission Rate</th>
                            <th class="px-5 py-4 font-semibold">Portal Account</th>
                            <th class="px-5 py-4 font-semibold">Status / Permission</th>
                            <th class="px-5 py-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($agents as $agent)
                            <tr class="dashboard-agent-row border-t border-[#e7ddca] bg-white" data-search="{{ strtolower($agent->name . ' ' . $agent->email . ' ' . ($agent->phone ?? '') . ' ' . ($agent->bio ?? '') . ' ' . ($agent->is_active ? 'active' : 'inactive')) }}">
                                <td class="px-5 py-4">
                                    <img src="{{ $agent->photo_url }}" alt="{{ $agent->name }}" class="h-10 w-10 rounded-full object-cover ring-1 ring-[#d9cab3]">
                                </td>
                                <td class="px-5 py-4 font-semibold text-[#1d3c34]">{{ $agent->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $agent->email }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $agent->phone }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $agent->bio }}</td>
                                <td class="px-5 py-4">
                                    <form method="POST" action="{{ route('dashboard.agents.commissionRate', $agent) }}" class="flex items-center gap-1.5">
                                        @csrf
                                        @method('PUT')
                                        <div class="relative w-20">
                                            <input type="number" step="0.1" min="0" max="100" name="commission_rate" value="{{ old('commission_rate', $agent->commission_rate ?? 5.0) }}" class="w-full rounded-lg border border-[#d9cab3] bg-white px-2 py-1 pr-6 text-xs font-semibold text-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                                            <span class="absolute right-2 top-1 text-xs text-slate-400">%</span>
                                        </div>
                                        <button type="submit" class="rounded-full bg-[#1d3c34] px-2.5 py-1 text-[11px] font-bold text-white transition hover:bg-[#254d43]">Set</button>
                                    </form>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($agent->account)
                                        <span class="rounded-full bg-[#dfeee4] px-2.5 py-1 text-xs font-bold text-[#1d3c34]">Active</span>
                                    @else
                                        <span class="rounded-full bg-[#f9ecd0] px-2.5 py-1 text-xs font-bold text-[#9b6c17]">None</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <form method="POST" action="{{ route('dashboard.agents.toggleActive', $agent) }}">
                                        @csrf
                                        @if ($agent->is_active)
                                            <button type="submit" title="Click to deactivate agent" class="inline-flex items-center gap-1.5 rounded-full bg-[#dfeee4] px-3 py-1 text-xs font-bold text-[#1d3c34] hover:bg-[#cbe3d3] transition">
                                                <span class="h-2 w-2 rounded-full bg-[#2c7a4d]"></span> Active
                                            </button>
                                        @else
                                            <button type="submit" title="Click to activate agent" class="inline-flex items-center gap-1.5 rounded-full bg-[#fbeae8] px-3 py-1 text-xs font-bold text-[#b94a48] hover:bg-[#f7d6d3] transition">
                                                <span class="h-2 w-2 rounded-full bg-[#b94a48]"></span> Inactive
                                            </button>
                                        @endif
                                    </form>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('dashboard', ['edit_agent' => $agent->id, 'section' => 'agents']) }}" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('dashboard.agents.destroy', $agent) }}" data-confirm="Delete this agent?">
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
                                <td colspan="9" class="px-5 py-6 text-center text-slate-500">No agents yet. Click “+ Add Agent” to create one.</td>
                            </tr>
                        @endforelse
                        <tr id="dashboard-agents-no-results" class="hidden border-t border-[#e7ddca] bg-white">
                            <td colspan="9" class="px-5 py-6 text-center text-slate-500">No agents found matching your search.</td>
                        </tr>
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
                    <h2 class="text-2xl font-black text-[#1d3c34]">Client Testimonials</h2>
                    <p class="mt-1 text-sm text-slate-600">Client testimonials are submitted from agent profile pages. Review them here: edit, approve for the homepage, delete, or restore. Testimonials cannot be created manually.</p>
                </div>
            </div>

            @if ($testimonialToEdit)
                <div class="create-form-panel mt-6 rounded-[1.25rem] border border-[#d9cab3] bg-[#f9f4ed] p-5">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h3 class="text-xl font-black text-[#1d3c34]">Editing Testimonial from: {{ $testimonialToEdit->name }}</h3>
                        <a href="{{ route('dashboard', ['section' => 'testimonials']) }}" class="text-sm font-semibold text-[#2d5d4d]">Cancel</a>
                    </div>

                    <form method="POST" action="{{ route('dashboard.testimonials.update', $testimonialToEdit) }}" class="grid gap-4 md:grid-cols-2">
                        @csrf
                        @method('PUT')

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
                            <input type="checkbox" id="testimonial-approved" name="is_approved" value="1" {{ old('is_approved', $testimonialToEdit?->is_approved) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#dfeee4]">
                            <label for="testimonial-approved" class="text-sm font-semibold text-slate-700">Approved (Show on home page)</label>
                        </div>

                        <div class="md:col-span-2 flex items-center gap-3">
                            <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]">
                                Update Testimonial
                            </button>
                            <a href="{{ route('dashboard', ['section' => 'testimonials']) }}" class="rounded-full border border-[#d9cab3] bg-white px-5 py-2.5 text-sm font-bold text-[#1d3c34]">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Testimonials Search Bar --}}
            <div class="mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#fbf8f3] p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" id="dashboard-testimonials-search" placeholder="Search testimonials by client name, email, agent, message, rating..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2 pl-9 pr-8 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                        <button type="button" id="dashboard-testimonials-search-clear" class="hidden absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 transition" title="Clear search">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <span id="dashboard-testimonials-count" class="shrink-0 rounded-full bg-[#1d3c34]/10 px-3 py-1 text-xs font-bold text-[#1d3c34]">{{ $testimonials->whereNull('deleted_at')->count() }} testimonials</span>
                </div>
            </div>

            {{-- Active Testimonials Table --}}
            <div class="mt-4 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
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
                            <tr class="dashboard-testimonial-row border-t border-[#e7ddca] bg-white" data-search="{{ strtolower($item->name . ' ' . ($item->email ?? '') . ' ' . ($item->agent?->name ?? '') . ' ' . $item->message . ' ' . $item->rating . ' ' . ($item->is_approved ? 'approved' : 'pending')) }}">
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
                                        <form method="POST" action="{{ route('dashboard.testimonials.destroy', $item) }}" data-confirm="Delete this testimonial? You can restore it later.">
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
                                <td colspan="6" class="px-5 py-6 text-center text-slate-500">No client testimonials yet. Feedback submitted from agent profile pages will appear here.</td>
                            </tr>
                        @endforelse
                        <tr id="dashboard-testimonials-no-results" class="hidden border-t border-[#e7ddca] bg-white">
                            <td colspan="6" class="px-5 py-6 text-center text-slate-500">No testimonials found matching your search.</td>
                        </tr>
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
                    <h2 class="text-2xl font-black text-[#1d3c34]">Blog &amp; News</h2>
                    <button type="button" class="toggle-create-form rounded-full bg-[#1d3c34] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]" data-target="blog-posts-create-panel" data-label-create="+ Add Post" data-label-close="Close Form">+ Add Post</button>
                </div>

                <div id="blog-posts-create-panel" class="create-form-panel mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#f9f4ed] p-5 {{ $postToEdit ? '' : 'hidden' }}">
                @if ($postToEdit)
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h3 class="text-xl font-black text-[#1d3c34]">Edit Post</h3>
                        <a href="{{ route('dashboard', ['section' => 'blog-posts']) }}" class="text-sm font-semibold text-[#2d5d4d]">Cancel</a>
                    </div>
                @else
                    <h3 class="text-xl font-black text-[#1d3c34]">Create Post</h3>
                @endif

                <form method="POST" action="{{ $postToEdit ? route('dashboard.posts.update', $postToEdit) : route('dashboard.posts.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
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
                </div>

                {{-- Existing posts list --}}
                <div class="mt-8 rounded-[1.25rem] border border-[#e7ddca]">
                    <div class="flex items-center justify-between gap-4 rounded-t-[1.25rem] bg-[#2d5d4d] px-5 py-4">
                        <h3 class="text-lg font-black text-white">Content Library</h3>
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-white">{{ $posts->count() }} posts</span>
                    </div>

                    <form method="GET" action="{{ route('dashboard') }}" class="grid gap-3 px-5 py-4 md:grid-cols-4">
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

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-[#f4efe7] text-[#1d3c34]">
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
                                    <tr class="border-t border-[#e7ddca]">
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
                                                    <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-3 py-2 text-xs font-bold text-[#a24339]" data-confirm="Delete this post?">Delete</button>
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
                </div>
            </section>
        @endif

        @php
            $editJobId = request()->query('edit_job');
            $jobToEdit = $editJobId ? $jobs->firstWhere('id', (int) $editJobId) : null;
        @endphp

        @if (auth()->user()->canCreateContent())
            <section id="job-postings" data-section="job-postings" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-black text-[#1d3c34]">Job Postings</h2>
                    <button type="button" class="toggle-create-form rounded-full bg-[#1d3c34] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]" data-target="job-postings-create-panel" data-label-create="+ Add Job Posting" data-label-close="Close Form">+ Add Job Posting</button>
                </div>

                <div id="job-postings-create-panel" class="create-form-panel mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#f9f4ed] p-5 {{ $jobToEdit ? '' : 'hidden' }}">
                    <h3 class="text-xl font-black text-[#1d3c34]">{{ $jobToEdit ? 'Edit Job Opening' : 'Post a Job Opening' }}</h3>
                    <form method="POST" action="{{ $jobToEdit ? route('dashboard.posts.update', $jobToEdit) : route('dashboard.posts.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
                        @csrf
                        @if ($jobToEdit)
                            @method('PUT')
                        @endif
                        <input type="hidden" name="type" value="job">
                        <input type="hidden" name="section" value="job-postings">

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Job Title</label>
                            <input type="text" name="title" value="{{ old('title', $jobToEdit?->title) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        {{-- Job Top Image / Header Banner Section --}}
                        <div class="md:col-span-2 rounded-2xl border border-[#d9cab3] bg-[#fdfaf5] p-4 sm:p-5 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                                <div>
                                    <label class="block text-sm font-bold text-[#1d3c34]">Top Image / Header Banner</label>
                                    <p class="text-xs text-slate-500">Insert an eye-catching top image to display prominently at the top of this job opening.</p>
                                </div>
                                <span class="inline-flex items-center gap-1.5 self-start rounded-full bg-[#1d3c34]/10 px-3 py-1 text-xs font-bold text-[#1d3c34]">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Job Top Banner
                                </span>
                            </div>

                            <div class="grid gap-4 md:grid-cols-12 items-start">
                                {{-- Preview Box --}}
                                <div class="md:col-span-5">
                                    <div class="relative aspect-[16/9] w-full overflow-hidden rounded-xl border-2 border-dashed border-[#d9cab3] bg-[#f0e8dc] flex items-center justify-center shadow-inner group">
                                        <img id="job-active-top-image" src="{{ $jobToEdit?->image_url ?: '' }}" alt="Job Top Image Preview" class="h-full w-full object-cover {{ $jobToEdit?->image_url ? '' : 'hidden' }}">
                                        <div id="job-top-image-placeholder" class="text-center p-4 {{ $jobToEdit?->image_url ? 'hidden' : '' }}">
                                            <svg class="mx-auto h-9 w-9 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <p class="mt-1 text-xs font-semibold text-slate-500">No top image selected</p>
                                            <p class="text-[10px] text-slate-400">Upload a custom image or pick a preset</p>
                                        </div>
                                        <span id="job-image-badge" class="absolute top-2 right-2 rounded-full bg-[#102b25]/85 backdrop-blur-md px-2.5 py-0.5 text-[10px] font-bold text-[#e7d8b7] border border-white/20 {{ $jobToEdit?->image_url ? '' : 'hidden' }}">
                                            ★ Active Banner
                                        </span>
                                    </div>
                                    @if ($jobToEdit?->image_url)
                                        <label class="mt-2.5 flex items-center gap-2 cursor-pointer text-xs font-semibold text-[#a24339] hover:underline">
                                            <input type="checkbox" name="remove_image" value="1" id="job-remove-image-chk" onchange="toggleJobRemoveImage(this)" class="rounded border-[#d9cab3] text-[#a24339] focus:ring-[#a24339]">
                                            Remove current top image
                                        </label>
                                    @endif
                                </div>

                                {{-- Upload & Presets Inputs --}}
                                <div class="md:col-span-7 space-y-3">
                                    {{-- File Upload --}}
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Upload Custom Top Image</label>
                                        <input type="file" name="image" id="job-image-input" accept="image/png,image/jpeg,image/webp,image/avif" onchange="previewJobTopImage(event)" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-[#1d3c34] file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-white hover:file:bg-[#254d43] focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                        <p class="mt-1 text-[11px] text-slate-500">Supported: JPG, PNG, WebP, AVIF up to 20MB.</p>
                                    </div>

                                    {{-- Hidden Preset Input --}}
                                    <input type="hidden" name="image_preset" id="job-image-preset" value="">

                                    {{-- 1-Click Luxury Presets --}}
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Or Insert a 1-Click Luxury Preset Background:</label>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                            @php
                                                $jobPresets = [
                                                    ['id' => 'hero-skyline', 'name' => 'City Skyline', 'file' => 'images/luxury/hero-skyline.jpg'],
                                                    ['id' => 'luxury-towers', 'name' => 'Modern Towers', 'file' => 'images/luxury/luxury-towers.jpg'],
                                                    ['id' => 'central-plaza', 'name' => 'Corporate Plaza', 'file' => 'images/luxury/central-plaza.jpg'],
                                                    ['id' => 'panoramic-park', 'name' => 'Park View', 'file' => 'images/luxury/panoramic-park.jpg'],
                                                    ['id' => 'retail-boulevard', 'name' => 'Commercial Hub', 'file' => 'images/luxury/retail-boulevard.jpg'],
                                                ];
                                            @endphp
                                            @foreach ($jobPresets as $preset)
                                                <button type="button" onclick="selectJobPresetBackground('{{ $preset['id'] }}', '{{ asset($preset['file']) }}')" class="group/preset relative flex items-center gap-2 overflow-hidden rounded-lg border border-[#d9cab3] bg-white p-1.5 text-left transition hover:border-[#1d3c34] hover:shadow-sm focus:outline-none cursor-pointer">
                                                    <img src="{{ asset($preset['file']) }}" alt="{{ $preset['name'] }}" class="h-8 w-10 shrink-0 rounded object-cover">
                                                    <span class="truncate text-[11px] font-semibold text-slate-700 group-hover/preset:text-[#1d3c34]">{{ $preset['name'] }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                            <input type="text" name="apply_link" value="{{ old('apply_link', $jobToEdit?->apply_link) }}" placeholder="https://... or mailto:hr@realestate.com" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <p class="mt-1 text-xs text-slate-500">Shown as the Apply button on the careers page and login page. Leave empty to default to the contact email.</p>
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

                {{-- Job Postings Search Bar --}}
                <div class="mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#fbf8f3] p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="relative flex-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input type="text" id="dashboard-jobs-search" placeholder="Search job postings by title, location, category, status..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2 pl-9 pr-8 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                            <button type="button" id="dashboard-jobs-search-clear" class="hidden absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 transition" title="Clear search">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <span id="dashboard-jobs-count" class="shrink-0 rounded-full bg-[#1d3c34]/10 px-3 py-1 text-xs font-bold text-[#1d3c34]">{{ $jobs->count() }} jobs</span>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#2d5d4d] text-[#f8f3eb]">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Top Image</th>
                                <th class="px-5 py-4 font-semibold">Title</th>
                                <th class="px-5 py-4 font-semibold">Location</th>
                                <th class="px-5 py-4 font-semibold">Deadline</th>
                                <th class="px-5 py-4 font-semibold">Apply Link</th>
                                <th class="px-5 py-4 font-semibold">Status</th>
                                <th class="px-5 py-4 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($jobs as $job)
                                <tr class="dashboard-job-row border-t border-[#e7ddca] bg-white" data-search="{{ strtolower($job->title . ' ' . ($job->job_location ?? '') . ' ' . ($job->category ?? '') . ' ' . $job->status) }}">
                                    <td class="px-5 py-4">
                                        @if ($job->image_url)
                                            <img src="{{ $job->image_url }}" alt="{{ $job->title }}" class="h-12 w-16 rounded-lg object-cover ring-1 ring-[#e7ddca] shadow-sm">
                                        @else
                                            <div class="flex h-12 w-16 items-center justify-center rounded-lg bg-gradient-to-br from-[#dfe8e3] via-[#edf3ee] to-[#e9d9b7] text-[8px] font-bold text-[#1d3c34]">No Image</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-[#1d3c34]">{{ $job->title }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $job->job_location ?? '—' }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $job->application_deadline?->format('M d, Y') ?? '—' }}</td>
                                    <td class="px-5 py-4 text-xs">
                                        @if ($job->apply_link)
                                            <a href="{{ $job->apply_link }}" target="_blank" class="font-semibold text-[#2d5d4d] hover:underline break-all block" title="{{ $job->apply_link }}">
                                                {{ $job->apply_link }}
                                            </a>
                                        @else
                                            <span class="text-slate-400">Default email</span>
                                        @endif
                                    </td>
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
                                                <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-3 py-2 text-xs font-bold text-[#a24339]" data-confirm="Delete this job posting?">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-6 text-center text-slate-500">No job postings yet.</td>
                                </tr>
                            @endforelse
                            <tr id="dashboard-jobs-no-results" class="hidden border-t border-[#e7ddca] bg-white">
                                <td colspan="7" class="px-5 py-6 text-center text-slate-500">No job postings found matching your search.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

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
        function generateStrongPassword(targetSelector) {
            const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            const lower = 'abcdefghjkmnpqrstuvwxyz';
            const numbers = '23456789';
            const symbols = '!@#$%&*?+';

            let pwd = '';
            pwd += upper[Math.floor(Math.random() * upper.length)];
            pwd += lower[Math.floor(Math.random() * lower.length)];
            pwd += numbers[Math.floor(Math.random() * numbers.length)];
            pwd += symbols[Math.floor(Math.random() * symbols.length)];

            const all = upper + lower + numbers + symbols;
            for (let i = 4; i < 16; i++) {
                pwd += all[Math.floor(Math.random() * all.length)];
            }
            pwd = pwd.split('').sort(() => 0.5 - Math.random()).join('');

            const input = typeof targetSelector === 'string' ? document.querySelector(targetSelector) : targetSelector;
            if (input) {
                input.value = pwd;
                input.type = 'text';
                navigator.clipboard?.writeText(pwd).catch(() => {});
                alert('Generated Strong Password:\n\n' + pwd + '\n\n(Copied to clipboard and filled in the input field)');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Settings Dropside Navigation
            const settingsTabButtons = document.querySelectorAll('.settings-tab-btn');
            const settingsTabPanels = document.querySelectorAll('.settings-tab-panel');
            const settingsDropsideSelect = document.getElementById('settings-dropside-select');

            function activateSettingsTab(targetId) {
                settingsTabPanels.forEach(panel => {
                    const isMatch = panel.id === targetId;
                    panel.classList.toggle('hidden', !isMatch);
                });

                settingsTabButtons.forEach(btn => {
                    const isMatch = btn.dataset.settingsTarget === targetId;
                    btn.classList.toggle('bg-[#1d3c34]', isMatch);
                    btn.classList.toggle('text-white', isMatch);
                    btn.classList.toggle('shadow-sm', isMatch);
                    btn.classList.toggle('text-slate-700', !isMatch);
                    btn.classList.toggle('hover:bg-[#f0e5d8]', !isMatch);

                    const badge = btn.querySelector('.tab-badge');
                    if (badge) {
                        badge.classList.toggle('bg-white/20', isMatch);
                        badge.classList.toggle('text-white', isMatch);
                        badge.classList.toggle('bg-[#ebdcc8]', !isMatch);
                        badge.classList.toggle('text-[#1d3c34]', !isMatch);
                    }
                });

                if (settingsDropsideSelect && settingsDropsideSelect.value !== targetId) {
                    settingsDropsideSelect.value = targetId;
                }
            }

            settingsTabButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.dataset.settingsTarget;
                    activateSettingsTab(targetId);
                });
            });

            if (settingsDropsideSelect) {
                settingsDropsideSelect.addEventListener('change', function() {
                    activateSettingsTab(this.value);
                });
            }

            document.querySelectorAll('[data-settings-jump]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.dataset.settingsJump;
                    activateSettingsTab(targetId);
                    const panel = document.getElementById(targetId);
                    if (panel) {
                        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });

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
            let initialHash = window.location.hash.replace('#', '');
            if (initialHash === 'message' || initialHash === 'messages') {
                initialHash = 'inquiries';
            }
            const initialSection = initialHash || paramSection || editSection || 'overview';

            if (document.querySelector('[data-section="' + initialSection + '"]')) {
                activateSection(initialSection);
            }

            // "+ Add" buttons toggle their create/edit form panel.
            document.querySelectorAll('.toggle-create-form').forEach(function (button) {
                button.addEventListener('click', function () {
                    const panel = document.getElementById(button.dataset.target);
                    if (! panel) {
                        return;
                    }

                    const willShow = panel.classList.toggle('hidden') === false;
                    button.textContent = willShow ? (button.dataset.labelClose || 'Close Form') : (button.dataset.labelCreate || '+ Add');

                    if (willShow) {
                        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });

            // Agreement modal logic
            const agreementModal = document.getElementById('admin-agreement-modal');
            const agreementForm = document.getElementById('admin-agreement-form');
            const modalTitle = document.getElementById('modal-property-title');
            const modalPrice = document.getElementById('modal-deal-price');
            const modalLeaseContainer = document.getElementById('modal-lease-months-container');

            document.querySelectorAll('.open-agreement-modal').forEach(button => {
                button.addEventListener('click', function () {
                    if (! agreementModal || ! agreementForm) return;
                    const actionUrl = this.dataset.actionUrl;
                    const title = this.dataset.propertyTitle;
                    const price = this.dataset.propertyPrice;
                    const type = this.dataset.propertyType;

                    agreementForm.action = actionUrl;
                    modalTitle.textContent = (type === 'rent' ? 'Rent: ' : 'Sell: ') + title;
                    modalPrice.value = price || '';
                    if (modalLeaseContainer) {
                        modalLeaseContainer.classList.toggle('hidden', type !== 'rent');
                    }
                    agreementModal.classList.remove('hidden');
                });
            });

            const closeAgreementModal = () => {
                if (agreementModal) agreementModal.classList.add('hidden');
            };

            document.getElementById('close-agreement-modal')?.addEventListener('click', closeAgreementModal);
            document.getElementById('cancel-agreement-modal')?.addEventListener('click', closeAgreementModal);
            agreementModal?.addEventListener('click', function (e) {
                if (e.target === agreementModal) closeAgreementModal();
            });

            // Client Inquiry / Message modal logic
            const inquiryModal = document.getElementById('admin-inquiry-modal');
            const modalInquirySubject = document.getElementById('modal-inquiry-subject');
            const modalInquiryName = document.getElementById('modal-inquiry-name');
            const modalInquiryEmail = document.getElementById('modal-inquiry-email');
            const modalInquiryPhone = document.getElementById('modal-inquiry-phone');
            const modalInquiryAgent = document.getElementById('modal-inquiry-agent');
            const modalInquiryReceived = document.getElementById('modal-inquiry-received');
            const modalInquiryMessage = document.getElementById('modal-inquiry-message');
            const modalInquiryReply = document.getElementById('modal-inquiry-reply');

            document.querySelectorAll('.view-inquiry-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (! inquiryModal) return;

                    const id = this.dataset.id;
                    const isRead = this.dataset.read === '1';
                    const readUrl = this.dataset.readUrl;
                    const name = this.dataset.name || 'Anonymous';
                    const email = this.dataset.email || '';
                    const phone = this.dataset.phone || '';
                    const agent = this.dataset.agent || 'Admin / General';
                    const subject = this.dataset.subject || 'No Subject';
                    const message = this.dataset.message || '';
                    const received = this.dataset.received || '';

                    modalInquirySubject.textContent = subject;
                    modalInquiryName.textContent = name;
                    modalInquiryEmail.textContent = email || 'N/A';
                    modalInquiryEmail.href = email ? 'mailto:' + email : '#';
                    modalInquiryPhone.textContent = phone || 'None provided';
                    modalInquiryPhone.href = phone ? 'tel:' + phone.replace(/[^0-9+]/g, '') : '#';
                    modalInquiryAgent.textContent = agent;
                    modalInquiryReceived.textContent = received;
                    modalInquiryMessage.textContent = message;
                    if (modalInquiryReply) {
                        modalInquiryReply.href = email ? 'mailto:' + email + '?subject=' + encodeURIComponent('Re: ' + subject) : '#';
                        modalInquiryReply.classList.toggle('hidden', ! email);
                    }

                    inquiryModal.classList.remove('hidden');
                    inquiryModal.classList.add('flex');

                    // If message is new (unread), mark it as read and reduce the unread count!
                    if (! isRead && readUrl) {
                        document.querySelectorAll('.view-inquiry-btn[data-id="' + id + '"]').forEach(function (b) {
                            b.dataset.read = '1';
                        });

                        const row = document.querySelector('[data-inquiry-row="' + id + '"]');
                        if (row) {
                            row.classList.remove('bg-[#fffdf9]');
                            const newBadge = row.querySelector('.inquiry-badge-new');
                            if (newBadge) {
                                newBadge.remove();
                            }
                        }

                        // Reduce sidebar badge count
                        const sidebarBadge = document.getElementById('sidebar-inquiries-badge');
                        if (sidebarBadge) {
                            const count = parseInt(sidebarBadge.textContent, 10) || 0;
                            const newCount = Math.max(0, count - 1);
                            if (newCount > 0) {
                                sidebarBadge.textContent = newCount + ' new';
                            } else {
                                sidebarBadge.remove();
                            }
                        }

                        // Reduce section header unread pill
                        const headerPill = document.getElementById('inquiries-unread-pill');
                        const headerCountEl = document.getElementById('inquiries-unread-count');
                        if (headerPill && headerCountEl) {
                            const count = parseInt(headerCountEl.textContent, 10) || 0;
                            const newCount = Math.max(0, count - 1);
                            if (newCount > 0) {
                                headerCountEl.textContent = newCount;
                            } else {
                                headerPill.classList.add('hidden');
                            }
                        }

                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        fetch(readUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        }).then(function (res) {
                            return res.json();
                        }).then(function (data) {
                            if (data && typeof data.unread_count === 'number') {
                                const currentBadge = document.getElementById('sidebar-inquiries-badge');
                                if (data.unread_count > 0 && currentBadge) {
                                    currentBadge.textContent = data.unread_count + ' new';
                                } else if (data.unread_count === 0 && currentBadge) {
                                    currentBadge.remove();
                                }
                                if (headerPill && headerCountEl) {
                                    if (data.unread_count > 0) {
                                        headerCountEl.textContent = data.unread_count;
                                        headerPill.classList.remove('hidden');
                                    } else {
                                        headerPill.classList.add('hidden');
                                    }
                                }
                            }
                        }).catch(function () {});
                    }
                });
            });

            const closeInquiryModal = () => {
                if (inquiryModal) {
                    inquiryModal.classList.add('hidden');
                    inquiryModal.classList.remove('flex');
                }
            };

            document.getElementById('close-inquiry-modal')?.addEventListener('click', closeInquiryModal);
            document.getElementById('cancel-inquiry-modal')?.addEventListener('click', closeInquiryModal);
            inquiryModal?.addEventListener('click', function (e) {
                if (e.target === inquiryModal) closeInquiryModal();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && inquiryModal && ! inquiryModal.classList.contains('hidden')) {
                    closeInquiryModal();
                }
            });

            // Dedicated search for each dashboard page/section
            function setupTableSearch(inputId, clearBtnId, rowSelector, noResultsId, countBadgeId, singularLabel, pluralLabel) {
                const input = document.getElementById(inputId);
                const clearBtn = document.getElementById(clearBtnId);
                const noResultsRow = document.getElementById(noResultsId);
                const countBadge = document.getElementById(countBadgeId);
                if (!input) return;

                function filterRows() {
                    const query = (input.value || '').trim().toLowerCase();
                    if (clearBtn) {
                        clearBtn.classList.toggle('hidden', !query);
                    }
                    const rows = document.querySelectorAll(rowSelector);
                    let visibleCount = 0;
                    rows.forEach(function (row) {
                        const text = (row.dataset.search || row.textContent || '').toLowerCase();
                        const match = !query || text.includes(query);
                        row.style.display = match ? '' : 'none';
                        if (match) visibleCount++;
                    });

                    if (noResultsRow) {
                        noResultsRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
                    }
                    if (countBadge) {
                        if (query) {
                            countBadge.textContent = visibleCount + ' of ' + rows.length + ' matching';
                        } else {
                            countBadge.textContent = rows.length + ' ' + (rows.length === 1 ? singularLabel : pluralLabel);
                        }
                    }
                }

                input.addEventListener('input', filterRows);
                if (clearBtn) {
                    clearBtn.addEventListener('click', function () {
                        input.value = '';
                        filterRows();
                        input.focus();
                    });
                }
            }

            setupTableSearch('dashboard-properties-search', 'dashboard-properties-search-clear', '.dashboard-property-row', 'dashboard-properties-no-results', 'dashboard-properties-count', 'property', 'properties');
            setupTableSearch('dashboard-orders-search', 'dashboard-orders-search-clear', '.dashboard-order-row', 'dashboard-orders-no-results', 'dashboard-orders-count', 'order', 'orders');
            setupTableSearch('dashboard-agents-search', 'dashboard-agents-search-clear', '.dashboard-agent-row', 'dashboard-agents-no-results', 'dashboard-agents-count', 'agent', 'agents');
            setupTableSearch('dashboard-testimonials-search', 'dashboard-testimonials-search-clear', '.dashboard-testimonial-row', 'dashboard-testimonials-no-results', 'dashboard-testimonials-count', 'testimonial', 'testimonials');
            // Multi-Photo Accumulator & Dropzone Uploader
            function setupMultiPhotoUploader(inputId, dropzoneId, previewId, counterPillId) {
                const input = document.getElementById(inputId);
                const dropzone = document.getElementById(dropzoneId);
                const preview = document.getElementById(previewId);
                const counterPill = counterPillId ? document.getElementById(counterPillId) : null;
                if (!input || !preview) return;

                let dt = new DataTransfer();

                function renderPreviews() {
                    preview.innerHTML = "";
                    const files = Array.from(dt.files);

                    if (counterPill) {
                        if (files.length > 0) {
                            counterPill.textContent = `${files.length} photo${files.length > 1 ? "s" : ""} added`;
                            counterPill.classList.remove("hidden");
                        } else {
                            counterPill.classList.add("hidden");
                        }
                    }

                    if (files.length === 0) {
                        preview.classList.add("hidden");
                        preview.classList.remove("grid");
                        return;
                    }

                    preview.classList.remove("hidden");
                    preview.classList.add("grid");

                    files.forEach((file, idx) => {
                        const card = document.createElement("div");
                        card.className = "group relative rounded-xl overflow-hidden border border-[#d9cab3] bg-white aspect-square shadow-2xs transition-all hover:shadow-md";

                        const isCover = idx === 0;
                        const sizeMb = (file.size / (1024 * 1024)).toFixed(1);

                        const img = document.createElement("img");
                        img.className = "h-full w-full object-cover";
                        img.src = URL.createObjectURL(file);

                        const badge = document.createElement("span");
                        badge.className = isCover
                            ? "absolute top-1 left-1 rounded bg-[#102b25]/90 px-1.5 py-0.5 text-[9px] font-bold text-[#e7d8b7] uppercase tracking-wider shadow-xs"
                            : "absolute top-1 left-1 rounded bg-black/70 px-1.5 py-0.5 text-[9px] font-bold text-white";
                        badge.textContent = isCover ? "Cover" : `#${idx + 1}`;

                        const sizeBadge = document.createElement("span");
                        sizeBadge.className = "absolute bottom-1 left-1 rounded bg-black/60 px-1 py-0.5 text-[8px] font-medium text-white/90 backdrop-blur-xs";
                        sizeBadge.textContent = `${sizeMb} MB`;

                        const removeBtn = document.createElement("button");
                        removeBtn.type = "button";
                        removeBtn.title = "Remove this photo from upload batch";
                        removeBtn.className = "absolute top-1 right-1 flex h-6 w-6 items-center justify-center rounded-full bg-red-600/90 text-white hover:bg-red-700 transition shadow-xs cursor-pointer";
                        removeBtn.innerHTML = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
                        removeBtn.addEventListener("click", (e) => {
                            e.stopPropagation();
                            e.preventDefault();
                            const newDt = new DataTransfer();
                            Array.from(dt.files).forEach((f, i) => {
                                if (i !== idx) newDt.items.add(f);
                            });
                            dt = newDt;
                            input.files = dt.files;
                            renderPreviews();
                        });

                        card.appendChild(img);
                        card.appendChild(badge);
                        card.appendChild(sizeBadge);
                        card.appendChild(removeBtn);
                        preview.appendChild(card);
                    });
                }

                input.addEventListener("change", function () {
                    Array.from(this.files || []).forEach(file => {
                        if (file.type.startsWith("image/")) {
                            dt.items.add(file);
                        }
                    });
                    this.files = dt.files;
                    renderPreviews();
                });

                if (dropzone) {
                    dropzone.addEventListener("click", (e) => {
                        if (e.target !== input) {
                            input.click();
                        }
                    });

                    ["dragenter", "dragover"].forEach(name => {
                        dropzone.addEventListener(name, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            dropzone.classList.add("border-[#1d3c34]", "bg-[#dfeee4]/40");
                        });
                    });

                    ["dragleave", "drop"].forEach(name => {
                        dropzone.addEventListener(name, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            dropzone.classList.remove("border-[#1d3c34]", "bg-[#dfeee4]/40");
                        });
                    });

                    dropzone.addEventListener("drop", (e) => {
                        if (e.dataTransfer && e.dataTransfer.files) {
                            Array.from(e.dataTransfer.files).forEach(file => {
                                if (file.type.startsWith("image/")) {
                                    dt.items.add(file);
                                }
                            });
                            input.files = dt.files;
                            renderPreviews();
                        }
                    });
                }
            }

            setupMultiPhotoUploader("property-image-input", "admin-property-dropzone", "new-property-images-preview", "property-image-counter-pill");
            window.deleteAdminPropertyImage = function (propertyId, imageId) {
                if (!confirm('Remove this photo from the property?')) return;
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;

                fetch(`/dashboard/properties/${propertyId}/images/${imageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.ok) {
                        document.getElementById(`admin-prop-img-${imageId}`)?.remove();
                    } else {
                        alert('Could not remove image.');
                    }
                })
                .catch(() => alert('Network error while removing photo.'));
            };

            window.setAdminCoverImage = function (propertyId, imageId, imageUrl) {
                const hiddenId = document.getElementById('admin-cover-image-id');
                if (hiddenId) hiddenId.value = imageId;

                const hiddenPreset = document.getElementById('admin-cover-preset');
                if (hiddenPreset) hiddenPreset.value = '';

                const activeCoverImg = document.getElementById('admin-active-cover-img');
                if (activeCoverImg && imageUrl) activeCoverImg.src = imageUrl;

                const statusText = document.getElementById('admin-cover-status-text');
                if (statusText) statusText.textContent = 'Active cover photo updated';

                updateAdminThumbnailsCoverState(imageId);

                if (propertyId) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;
                    fetch(`/dashboard/properties/${propertyId}/images/${imageId}/cover`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    }).then(res => res.json()).then(data => {
                        if (data.ok) showToastNotification('Cover photo updated successfully!');
                    }).catch(err => console.log('Cover updated locally', err));
                }
            };

            window.updateAdminThumbnailsCoverState = function (activeImageId) {
                const container = document.getElementById('admin-existing-images-container');
                if (!container) return;

                const cards = container.querySelectorAll('[id^="admin-prop-img-"]');
                cards.forEach(card => {
                    const id = card.id.replace('admin-prop-img-', '');
                    const controlDiv = card.querySelector('.admin-cover-control');
                    if (!controlDiv) return;

                    const propertyId = card.getAttribute('data-property-id') || '';

                    if (id == activeImageId) {
                        card.classList.remove('border-[#d9cab3]');
                        card.classList.add('border-[#d4af37]', 'ring-2', 'ring-[#d4af37]/30');
                        controlDiv.innerHTML = '<span class="admin-cover-badge rounded-md bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black text-[#102b25] uppercase tracking-wider shadow-xs flex items-center gap-1">★ Cover</span>';
                    } else {
                        card.classList.remove('border-[#d4af37]', 'ring-2', 'ring-[#d4af37]/30');
                        card.classList.add('border-[#d9cab3]');
                        const img = card.querySelector('img');
                        const src = img ? img.src : '';
                        controlDiv.innerHTML = `<button type="button" onclick="setAdminCoverImage(${propertyId}, ${id}, '${src}')" title="Set as primary cover photo" class="admin-make-cover-btn rounded-md bg-black/75 hover:bg-[#d4af37] hover:text-[#102b25] text-white px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider transition backdrop-blur-xs cursor-pointer shadow-xs">★ Set Cover</button>`;
                    }
                });
            };

            window.selectAdminPresetBackground = function (presetId, imageUrl, propertyId) {
                const hiddenPreset = document.getElementById('admin-cover-preset');
                if (hiddenPreset) hiddenPreset.value = presetId;

                const hiddenId = document.getElementById('admin-cover-image-id');
                if (hiddenId) hiddenId.value = '';

                const activeCoverImg = document.getElementById('admin-active-cover-img');
                if (activeCoverImg) activeCoverImg.src = imageUrl;

                const statusText = document.getElementById('admin-cover-status-text');
                if (statusText) statusText.textContent = `Selected luxury preset: ${presetId}`;

                if (propertyId) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;
                    fetch(`/dashboard/properties/${propertyId}/preset-cover`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ preset: presetId })
                    }).then(res => res.json()).then(data => {
                        if (data.ok) showToastNotification('Luxury background preset set as cover!');
                    }).catch(err => console.log('Preset chosen', err));
                } else {
                    showToastNotification('Luxury background preset selected!');
                }
            };

            window.previewAdminCoverImage = function (input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const activeCoverImg = document.getElementById('admin-active-cover-img');
                        if (activeCoverImg) activeCoverImg.src = e.target.result;
                        const statusText = document.getElementById('admin-cover-status-text');
                        if (statusText) statusText.textContent = 'New cover photo file selected (will save with form)';
                        showToastNotification('New cover photo selected!');
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            };

            window.previewJobTopImage = function (event) {
                const input = event.target;
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('job-active-top-image');
                        const placeholder = document.getElementById('job-top-image-placeholder');
                        const badge = document.getElementById('job-image-badge');
                        const presetInput = document.getElementById('job-image-preset');
                        const removeChk = document.getElementById('job-remove-image-chk');

                        if (presetInput) presetInput.value = '';
                        if (removeChk) removeChk.checked = false;

                        if (preview) {
                            preview.src = e.target.result;
                            preview.classList.remove('hidden', 'opacity-30');
                        }
                        if (placeholder) placeholder.classList.add('hidden');
                        if (badge) {
                            badge.textContent = '★ New Upload';
                            badge.classList.remove('hidden');
                        }
                        showToastNotification('Job top image file selected!');
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            };

            window.selectJobPresetBackground = function (presetId, imageUrl) {
                const presetInput = document.getElementById('job-image-preset');
                const fileInput = document.getElementById('job-image-input');
                const preview = document.getElementById('job-active-top-image');
                const placeholder = document.getElementById('job-top-image-placeholder');
                const badge = document.getElementById('job-image-badge');
                const removeChk = document.getElementById('job-remove-image-chk');

                if (presetInput) presetInput.value = presetId;
                if (fileInput) fileInput.value = '';
                if (removeChk) removeChk.checked = false;

                if (preview) {
                    preview.src = imageUrl;
                    preview.classList.remove('hidden', 'opacity-30');
                }
                if (placeholder) placeholder.classList.add('hidden');
                if (badge) {
                    badge.textContent = '★ Preset: ' + presetId;
                    badge.classList.remove('hidden');
                }
                showToastNotification('Luxury preset top image selected!');
            };

            window.toggleJobRemoveImage = function (checkbox) {
                const preview = document.getElementById('job-active-top-image');
                const badge = document.getElementById('job-image-badge');
                if (checkbox.checked) {
                    if (preview) preview.classList.add('opacity-30');
                    if (badge) badge.classList.add('hidden');
                    showToastNotification('Top image will be removed on save');
                } else {
                    if (preview) preview.classList.remove('opacity-30');
                    if (badge) badge.classList.remove('hidden');
                }
            };

            function showToastNotification(message) {
                let toast = document.getElementById('global-admin-toast');
                if (!toast) {
                    toast = document.createElement('div');
                    toast.id = 'global-admin-toast';
                    toast.className = 'fixed bottom-5 right-5 z-[9999] flex items-center gap-2 rounded-xl bg-[#102b25] text-[#e7d8b7] px-4 py-3 shadow-2xl border border-[#d4af37]/40 text-sm font-semibold transition-all duration-300 opacity-0 translate-y-2 pointer-events-none';
                    document.body.appendChild(toast);
                }
                toast.innerHTML = `<span class="text-[#d4af37]">★</span> <span>${message}</span>`;
                toast.classList.remove('opacity-0', 'translate-y-2');
                toast.classList.add('opacity-100', 'translate-y-0');
                setTimeout(() => {
                    toast.classList.remove('opacity-100', 'translate-y-0');
                    toast.classList.add('opacity-0', 'translate-y-2');
                }, 2800);
            }
        });
    </script>

    {{-- Sell/Rent Property Agreement Modal for Admin --}}
    <div id="admin-agreement-modal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-2xl md:p-8">
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                <div>
                    <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-bold uppercase tracking-wider text-[#9b6c17]">Official Agreement</span>
                    <h3 id="modal-property-title" class="mt-2 text-xl font-black text-[#1d3c34]">Sell Property with Agreement</h3>
                </div>
                <button type="button" id="close-agreement-modal" class="rounded-full p-2 text-slate-400 hover:bg-[#f5f0e7] hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="admin-agreement-form" method="POST" action="" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Buyer / Tenant Full Name *</label>
                    <input type="text" name="buyer_name" required placeholder="e.g. Abebe Bikila" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Email Address *</label>
                        <input type="email" name="buyer_email" required placeholder="client@example.com" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Phone Number</label>
                        <input type="text" name="buyer_phone" placeholder="+251 9..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Agreed Price (ETB) *</label>
                        <input type="number" step="0.01" name="deal_price" id="modal-deal-price" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div id="modal-lease-months-container">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Lease Months</label>
                        <input type="number" name="lease_months" id="modal-lease-months" min="1" max="120" value="12" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Special Terms / Agreement Notes</label>
                    <textarea name="admin_note" rows="2" placeholder="Optional conditions or payment terms..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]"></textarea>
                </div>
                <div class="mt-6 flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button type="button" id="cancel-agreement-modal" class="rounded-full border border-[#d9cab3] bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-[#f5f0e7]">
                        Cancel
                    </button>
                    <button type="submit" name="submit_action" value="edit" class="rounded-full border border-[#b7842d] bg-[#fdf8ef] px-5 py-2.5 text-sm font-bold text-[#9b6c17] transition hover:bg-[#f9ecd0]">
                        Edit Content & Generate
                    </button>
                    <button type="submit" name="submit_action" value="generate" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]">
                        Generate Directly
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Client Inquiry / Message Details Modal for Admin --}}
    <div id="admin-inquiry-modal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-2xl md:p-8 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                <div class="min-w-0 pr-4">
                    <span class="rounded-full bg-[#dfeee4] px-3 py-1 text-xs font-bold uppercase tracking-wider text-[#1d3c34]">Message Details</span>
                    <h3 id="modal-inquiry-subject" class="mt-2 truncate text-xl font-black text-[#1d3c34]">Subject</h3>
                </div>
                <button type="button" id="close-inquiry-modal" class="shrink-0 rounded-full p-2 text-slate-400 hover:bg-[#f5f0e7] hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mt-5 space-y-4">
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fcfaf7] p-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-slate-600">
                        <div>
                            <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px] block">Sender Name</span>
                            <span id="modal-inquiry-name" class="font-bold text-sm text-[#1d3c34] block"></span>
                        </div>
                        <div>
                            <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px] block">Assigned Agent</span>
                            <span id="modal-inquiry-agent" class="font-bold text-sm text-[#1d3c34] block"></span>
                        </div>
                        <div>
                            <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px] block">Email</span>
                            <a id="modal-inquiry-email" href="#" class="font-semibold text-[#2d5d4d] hover:underline block truncate"></a>
                        </div>
                        <div>
                            <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px] block">Phone</span>
                            <a id="modal-inquiry-phone" href="#" class="font-semibold text-[#2d5d4d] hover:underline block"></a>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px] block">Received Date</span>
                            <span id="modal-inquiry-received" class="text-slate-600 block"></span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Message Content</label>
                    <div id="modal-inquiry-message" class="max-h-60 overflow-y-auto whitespace-pre-wrap rounded-2xl border border-[#d9cab3] bg-white p-4 text-sm leading-relaxed text-slate-700"></div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-end gap-3 border-t border-[#e7ddca] pt-4">
                <button type="button" id="cancel-inquiry-modal" class="rounded-full border border-[#d9cab3] bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                    Close
                </button>
                <a id="modal-inquiry-reply" href="#" class="inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Reply via Email
                </a>
            </div>
        </div>
    </div>

    @include('layouts.partials.confirm-modal')
</body>
</html>
