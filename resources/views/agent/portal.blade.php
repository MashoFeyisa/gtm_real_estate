<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $agent->name }} — Agent Portal</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f5f0e7] text-slate-800 antialiased scroll-smooth">
    <div class="flex min-h-screen">
        {{-- Sidebar (same structure as the admin dashboard) --}}
        <aside class="w-72 shrink-0 border-r border-[#d9cab3] bg-[#1d3c34] p-6 text-[#f8f3eb]">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ $siteBrand['logoUrl'] }}" alt="{{ $siteBrand['name'] }} Logo" class="h-10 w-10 rounded-xl object-contain ring-2 ring-[#d9cab3] bg-white/10">
                <span class="text-xl font-black tracking-tight text-white">{{ $siteBrand['name'] }}</span>
            </a>

            <div class="mt-8 flex items-center gap-3 rounded-[1.25rem] border border-white/10 bg-white/5 p-4">
                <img src="{{ $agent->photo_url }}" alt="{{ $agent->name }}" class="h-12 w-12 rounded-xl object-cover ring-2 ring-[#d9cab3]">
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-white">{{ $agent->name }}</p>
                    <p class="truncate text-xs text-[#dfeee4]">{{ $agent->bio ?: 'Property consultant' }}</p>
                </div>
            </div>

            <nav class="mt-6 space-y-2">
                <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">Overview</a>
                <a href="#buy-requests" data-target-section="buy-requests" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span>Buy Requests</span>
                    @if ($pendingOrders->isNotEmpty())
                        <span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-xs font-bold text-[#9b6c17]">{{ $pendingOrders->count() }} new</span>
                    @endif
                </a>
                <a href="#listings" data-target-section="listings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span>My Listings</span>
                    <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold">{{ $propertiesCount }}</span>
                </a>
                <a href="#messages" data-target-section="messages" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span>Client Messages</span>
                    @if ($unreadInquiries->isNotEmpty())
                        <span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-xs font-bold text-[#9b6c17]">{{ $unreadInquiries->count() }} new</span>
                    @endif
                </a>
                <a href="#feedback" data-target-section="feedback" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span>Client Reviews</span>
                    <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold">{{ $feedbackCount }}</span>
                </a>
                <a href="#profile" data-target-section="profile" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">My Profile</a>
                <a href="{{ route('agents.show', $agent) }}" class="flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Public Profile</a>
                <a href="{{ route('home') }}" class="flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">View Website</a>
            </nav>

            <div class="mt-10 rounded-[1.25rem] border border-white/10 bg-white/5 p-4">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#d9cab3]">Signed in</p>
                <p class="mt-3 text-lg font-bold text-white">{{ $agent->name }}</p>
                <p class="text-sm text-[#dfeee4]">Agent</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-[#f8f3eb] px-3 py-2.5 text-sm font-bold text-[#1d3c34] transition hover:bg-white">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 px-6 py-12">
            <div id="overview" class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between scroll-mt-24">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Agent Portal</p>
                    <h1 class="mt-2 text-4xl font-black text-[#1d3c34]">Welcome, {{ $agent->name }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-full bg-[#f2e4cb] px-3 py-1 text-sm font-semibold text-[#1d3c34]">{{ $agent->email }}</span>
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
                <div class="mt-6 rounded-xl border border-[#c86b5c] bg-[#fef3f1] px-4 py-3 text-sm font-medium text-[#a24339]">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Overview --}}
            <div data-section="overview" class="section-panel">
                @if ($pendingOrders->isNotEmpty())
                    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-[#d9cab3] bg-[#fffaf2] p-5 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#f9ecd0] text-[#9b6c17]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </span>
                            <div>
                                <h2 class="font-black text-[#1d3c34]">You have {{ $pendingOrders->count() }} pending client request{{ $pendingOrders->count() > 1 ? 's' : '' }}!</h2>
                                <p class="text-xs text-slate-600">Review the client's contact information, connect with them, agree on terms, and submit to Admin.</p>
                            </div>
                        </div>
                        <a href="#buy-requests" data-target-section="buy-requests" class="nav-section-link rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                            Review Requests
                        </a>
                    </div>
                @endif

                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <a href="#listings" data-target-section="listings" class="nav-section-link rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm transition hover:shadow-md">
                        <p class="text-sm font-medium text-slate-500">Total Listings</p>
                        <p class="mt-3 text-3xl font-black text-[#1d3c34]">{{ $propertiesCount }}</p>
                    </a>
                    <a href="#listings" data-target-section="listings" class="nav-section-link rounded-[1.5rem] border border-[#d9cab3] bg-[#edf3ee] p-5 shadow-sm transition hover:shadow-md">
                        <p class="text-sm font-medium text-slate-500">Active Listings</p>
                        <p class="mt-3 text-3xl font-black text-[#2d5d4d]">{{ $publishedCount }}</p>
                    </a>
                    <a href="#messages" data-target-section="messages" class="nav-section-link rounded-[1.5rem] border border-[#d9cab3] bg-[#f4efe7] p-5 shadow-sm transition hover:shadow-md">
                        <p class="text-sm font-medium text-slate-500">New Messages</p>
                        <p class="mt-3 text-3xl font-black text-[#2d5d4d]">{{ $unreadInquiries->count() }}</p>
                    </a>
                    <a href="#buy-requests" data-target-section="buy-requests" class="nav-section-link rounded-[1.5rem] border border-[#d9cab3] bg-[#f9ebd8] p-5 shadow-sm transition hover:shadow-md">
                        <p class="text-sm font-medium text-slate-500">Pending Requests</p>
                        <p class="mt-3 text-3xl font-black text-[#b7842d]">{{ $pendingOrders->count() }}</p>
                    </a>
                </div>

                <div class="mt-10 grid gap-6 lg:grid-cols-2">
                    <div class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-xl font-black text-[#1d3c34]">Recent Listings</h2>
                            <a href="#feedback" data-target-section="feedback" class="nav-section-link rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold text-[#2d5d4d]">
                                {{ $feedbackCount > 0 ? number_format($averageRating, 1).' ★' : 'No rating yet' }}
                            </a>
                        </div>
                        <ul class="mt-5 space-y-4">
                            @forelse ($properties->take(5) as $recent)
                                <li class="flex items-center justify-between gap-3 border-b border-[#e7ddca] pb-3 last:border-b-0 last:pb-0">
                                    <span class="truncate font-medium text-slate-700">{{ $recent->title }}</span>
                                    @php
                                        $badge = match ($recent->status) {
                                            'published' => ['Published', 'bg-[#dfeee4] text-[#1d3c34]'],
                                            'sold', 'rented' => [ucfirst($recent->status), 'bg-[#e9efe9] text-[#2d5d4d]'],
                                            'archived' => ['Archived', 'bg-[#f3ecdb] text-[#9b6c17]'],
                                            'available' => ['Available', 'bg-[#edf2ee] text-[#2d5d4d]'],
                                            default => ['Draft', 'bg-[#f9ecd0] text-[#9b6c17]'],
                                        };
                                    @endphp
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold {{ $badge[1] }}">{{ $badge[0] }}</span>
                                </li>
                            @empty
                                <li class="text-sm text-slate-500">No listings yet. Post your first property in My Listings.</li>
                            @endforelse
                        </ul>
                        <a href="#listings" data-target-section="listings" class="nav-section-link mt-5 inline-block text-sm font-semibold text-[#1d3c34] hover:text-[#244d43]">View all listings</a>
                    </div>

                    <div class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-xl font-black text-[#1d3c34]">Recent Client Messages</h2>
                            <a href="#messages" data-target-section="messages" class="nav-section-link rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-bold text-[#9b6c17]">
                                {{ $unreadInquiries->count() }} new
                            </a>
                        </div>
                        <ul class="mt-5 space-y-4">
                            @forelse ($inquiries->take(4) as $inq)
                                <li class="border-b border-[#e7ddca] pb-3 last:border-b-0 last:pb-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-bold text-[#1d3c34]">{{ $inq->name }}</p>
                                        <span class="text-xs text-slate-400">{{ $inq->created_at?->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-1 line-clamp-2 text-xs text-slate-600">{{ $inq->message }}</p>
                                </li>
                            @empty
                                <li class="text-sm text-slate-500">No client messages yet.</li>
                            @endforelse
                        </ul>
                        <a href="#messages" data-target-section="messages" class="nav-section-link mt-5 inline-block text-sm font-semibold text-[#1d3c34] hover:text-[#244d43]">View all messages</a>
                    </div>
                </div>
            </div>

            {{-- Buy & Rent requests --}}
            <section id="buy-requests" data-section="buy-requests" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-black text-[#1d3c34]">Buy Requests</h2>
                        <p class="mt-1 text-sm text-slate-600">Clients who sent a buy or rent request to you. Contact the client, agree on terms, and submit to Admin.</p>
                    </div>
                    <span class="rounded-full bg-[#f9ebd8] px-4 py-1.5 text-xs font-bold uppercase tracking-[0.14em] text-[#b7842d]">
                        {{ $pendingOrders->count() }} pending
                    </span>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse ($orders as $order)
                        <article class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm md:p-6">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-lg font-black text-[#1d3c34]">
                                        @if ($order->property)
                                            <a href="{{ route('properties.show', $order->property->slug) }}" class="transition hover:text-[#2e5a4c]">{{ $order->property->title }}</a>
                                        @else
                                            Property removed
                                        @endif
                                    </h3>
                                    <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                                        <span class="font-semibold text-[#1d3c34]">{{ $order->name }}</span>
                                        <span>·</span>
                                        <a href="mailto:{{ $order->email }}" class="inline-flex items-center gap-1 font-semibold text-[#2d5d4d] hover:underline">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            {{ $order->email }}
                                        </a>
                                        @if ($order->phone)
                                            <span>·</span>
                                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $order->phone) }}" class="inline-flex items-center gap-1 font-semibold text-[#2d5d4d] hover:underline">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                {{ $order->phone }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] {{ $order->isRental() ? 'bg-[#e7ecf5] text-[#34547d]' : 'bg-[#f4efe7] text-[#1d3c34]' }}">
                                        {{ $order->isRental() ? 'For Rent' : 'For Sale' }}
                                    </span>
                                    @if ($order->offer_amount)
                                        <span class="rounded-full bg-[#f4efe7] px-3 py-1 text-sm font-black text-[#1d3c34]">{{ $order->isRental() ? 'Rent: ETB ' : 'Offer: ETB ' }}{{ number_format($order->offer_amount) }}{{ $order->isRental() ? '/mo' : '' }}</span>
                                    @endif
                                    <span class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] {{ $order->status === 'pending' ? 'bg-[#f9ecd0] text-[#9b6c17]' : ($order->status === 'accepted' ? 'bg-[#dfeee4] text-[#1d3c34]' : 'bg-[#f8ddd9] text-[#a24339]') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>

                            @if ($order->message)
                                <div class="mt-3 rounded-xl bg-[#f9f4ed] px-4 py-3 text-sm leading-7 text-slate-600">
                                    <span class="font-bold text-[#1d3c34]">Client message:</span> {{ $order->message }}
                                </div>
                            @endif

                            @if ($order->agent_note)
                                <p class="mt-3 rounded-xl bg-[#f9f4ed] px-4 py-3 text-sm text-slate-600"><span class="font-bold text-[#1d3c34]">Agreement / Agent note:</span> {{ $order->agent_note }}</p>
                            @endif

                            @if ($order->status === 'accepted')
                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-[#dfeee4] bg-[#edf9ee] px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#214f3a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-bold text-[#214f3a]">
                                                Agreed with client &amp; request sent to Admin
                                                @if ($order->submitted_to_admin_at)
                                                    · {{ $order->submitted_to_admin_at->format('M d, Y H:i') }}
                                                @endif
                                            </p>
                                            <p class="text-xs text-[#4a6b58]">
                                                Admin status: <span class="font-bold uppercase tracking-wider">{{ $order->admin_status ?? 'pending review' }}</span>
                                                @if ($order->admin_note) · Note: {{ $order->admin_note }} @endif
                                            </p>
                                        </div>
                                    </div>
                                    @if ($order->hasAgreement())
                                        <a href="{{ route('orders.agreement', $order) }}" class="rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-white transition hover:bg-[#254d43]">
                                            Download Agreement PDF
                                        </a>
                                    @endif
                                </div>
                            @endif

                            @if ($order->status === 'pending')
                                <div class="mt-4 flex flex-wrap items-end gap-3 border-t border-[#e7ddca] pt-4">
                                    <form method="POST" action="{{ route('agent.orders.status', $order) }}" class="flex flex-1 flex-wrap items-end gap-3">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="accepted">
                                        <div class="min-w-0 flex-1 sm:w-64">
                                            <label class="mb-1 block text-xs font-semibold text-slate-700">Agreement note for Admin &amp; Client</label>
                                            <input type="text" name="agent_note" placeholder="Agreed terms, price or contract conditions..." class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                        </div>
                                        <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]">
                                            Agree &amp; Send to Admin
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('agent.orders.status', $order) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="rounded-full border border-[#c86b5c] bg-[#fef3f1] px-5 py-2.5 text-sm font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            @endif

                            <div class="mt-4 flex items-center justify-between border-t border-[#f0e8d8] pt-3">
                                <span class="text-xs text-slate-400">Received {{ $order->created_at?->format('M d, Y') }}</span>
                                <form method="POST" action="{{ route('agent.orders.destroy', $order) }}" data-confirm="Are you sure you want to delete this buy/rent request?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1 text-xs font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        Delete Request
                                    </button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-[#d9cab3] bg-[#fffaf2] p-10 text-center">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#f9ebd8] text-[#b7842d]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </span>
                            <p class="mt-4 text-sm text-slate-600">No buy requests yet. When a client selects you and submits a request, it appears here.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- My Listings --}}
            <section id="listings" data-section="listings" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-black text-[#1d3c34]">My Listings</h2>
                        <p class="mt-1 text-sm text-slate-600">Post properties for sale or rent, publish or unpublish them, and mark them as sold or rented.</p>
                    </div>
                    <button type="button" id="agent-add-listing-toggle" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]" data-label-create="+ Add Property" data-label-close="Close Form">+ Add Property</button>
                </div>
                <p class="mt-2 text-xs font-bold uppercase tracking-[0.14em] text-[#587165]">{{ $propertiesCount }} total · {{ $publishedCount }} live on site</p>

                {{-- Edit listing form (revealed when editing a property) --}}
                @if ($propertyToEdit)
                    <div id="edit-listing-{{ $propertyToEdit->id }}" class="mt-6 rounded-[1.5rem] border border-[#d9cab3] bg-[#fffaf2] p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <div>
                                <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider text-white">Editing</span>
                                <h3 class="mt-1 text-xl font-black text-[#1d3c34]">Edit: {{ $propertyToEdit->title }}</h3>
                            </div>
                            <a href="{{ route('agent.portal', ['section' => 'listings']) }}" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-[#f1e4cf]">Cancel</a>
                        </div>
                        <form method="POST" action="{{ route('agent.properties.update', $propertyToEdit) }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                                <input type="text" name="title" value="{{ old('title', $propertyToEdit->title) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Price (ETB)</label>
                                <input type="number" name="price" min="0" step="0.01" value="{{ old('price', $propertyToEdit->price) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Type</label>
                                <select name="type" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                    <option value="sale" @selected(old('type', $propertyToEdit->type) === 'sale')>For Sale</option>
                                    <option value="rent" @selected(old('type', $propertyToEdit->type) === 'rent')>For Rent</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Category</label>
                                <select name="category" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                    @foreach (['home', 'villa', 'apartment'] as $categoryOption)
                                        <option value="{{ $categoryOption }}" @selected(old('category', $propertyToEdit->property_category ?? $propertyToEdit->category) === $categoryOption)>{{ ucfirst($categoryOption) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Bedrooms</label>
                                <input type="number" name="bedrooms" min="0" value="{{ old('bedrooms', $propertyToEdit->bedrooms) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Bathrooms</label>
                                <input type="number" name="bathrooms" min="0" value="{{ old('bathrooms', $propertyToEdit->bathrooms) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Area (sq ft)</label>
                                <input type="number" name="area" min="0" value="{{ old('area', $propertyToEdit->area) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">City</label>
                                <input type="text" name="city" value="{{ old('city', $propertyToEdit->city) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Address</label>
                                <input type="text" name="address" value="{{ old('address', $propertyToEdit->address) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                                <textarea name="description" rows="3" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('description', $propertyToEdit->description) }}</textarea>
                            </div>
                            <div class="md:col-span-2">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-sm font-semibold text-slate-700">Property Photos & Cover Image</label>
                                    <span id="agent-edit-counter-pill" class="text-xs font-bold text-[#1d3c34] bg-[#dfeee4] px-2.5 py-0.5 rounded-full hidden">0 photos</span>
                                </div>
                                <p class="text-xs text-slate-500 mb-2.5">Upload gallery photos for this listing. The primary cover image is displayed on property cards and search results. You can set any photo as the cover by clicking <strong>"★ Set Cover"</strong>, upload a new cover photo, or select from luxury background presets.</p>

                                {{-- Hidden inputs for selected cover image or luxury preset --}}
                                <input type="hidden" name="cover_image_id" id="agent-cover-image-id" value="{{ optional($propertyToEdit->images->firstWhere('image_path', $propertyToEdit->image_path))->id }}">
                                <input type="hidden" name="cover_preset" id="agent-cover-preset" value="">

                                {{-- Active Cover Photo Section --}}
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
                                            <label for="agent-cover-file" class="cursor-pointer inline-flex items-center gap-1.5 rounded-xl border border-[#1d3c34]/25 bg-white hover:bg-[#1d3c34] hover:text-white px-3 py-1.5 text-xs font-bold text-[#1d3c34] shadow-2xs transition">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                                <span>Upload New Cover Photo</span>
                                            </label>
                                            <input type="file" id="agent-cover-file" name="cover_image" accept="image/png,image/jpeg,image/webp,image/avif" class="sr-only" onchange="previewAgentCoverImage(this)">
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="relative h-20 w-32 sm:h-24 sm:w-40 shrink-0 overflow-hidden rounded-xl border-2 border-[#d4af37] shadow-sm bg-slate-100">
                                            <img id="agent-active-cover-img" src="{{ $propertyToEdit->primary_image_url }}" alt="Cover Photo" class="h-full w-full object-cover">
                                            <span class="absolute top-1 left-1 rounded bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black uppercase text-[#102b25] shadow-xs">★ Cover</span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-800" id="agent-cover-status-text">Active Cover Image</p>
                                            <p class="text-[11px] text-slate-500 mt-1">To change, click <strong>"★ Set Cover"</strong> on any gallery photo below, upload a new cover above, or pick a luxury preset background.</p>
                                        </div>
                                    </div>
                                </div>

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
                                            <button type="button" onclick="selectAgentPresetBackground('{{ $pr['id'] }}', '{{ asset($pr['file']) }}', {{ $propertyToEdit->id }})" class="group relative rounded-lg overflow-hidden border border-[#d9cab3] hover:border-[#d4af37] aspect-[16/10] text-left transition cursor-pointer shadow-2xs hover:shadow-xs">
                                                <img src="{{ asset($pr['file']) }}" alt="{{ $pr['name'] }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-transparent"></div>
                                                <span class="absolute bottom-1 left-1.5 right-1.5 text-[10px] font-bold text-white truncate drop-shadow">{{ $pr['name'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <div id="agent-edit-dropzone" class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#d9cab3] bg-white p-5 text-center transition-all hover:border-[#1d3c34] hover:bg-[#dfeee4]/20 cursor-pointer">
                                    <input id="agent-edit-images" type="file" name="images[]" multiple accept="image/png,image/jpeg,image/webp,image/avif" class="sr-only">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#1d3c34]/10 text-[#1d3c34] mb-2 pointer-events-none">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700 pointer-events-none"><span class="text-[#1d3c34] underline decoration-2">Click to choose photos</span> or drag & drop here</p>
                                    <p class="text-xs text-slate-400 mt-1 pointer-events-none">PNG, JPG, WEBP — select as many photos as you need</p>
                                </div>
                                <div id="agent-edit-images-preview" class="mt-3.5 hidden grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2.5"></div>

                                @if ($propertyToEdit->images->isNotEmpty() || $propertyToEdit->image_path)
                                    <div class="mt-4 border-t border-[#e7ddca] pt-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <p class="text-xs font-bold uppercase tracking-wider text-slate-700">Current Gallery Photos ({{ $propertyToEdit->images->count() ?: 1 }})</p>
                                            <span class="text-[11px] text-slate-500">Click <strong>"★ Set Cover"</strong> on any photo to update primary cover</span>
                                        </div>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2.5" id="agent-existing-images-container">
                                            @forelse ($propertyToEdit->images as $agentPropImg)
                                                @php
                                                    $isCover = ($agentPropImg->image_path === $propertyToEdit->image_path) || ($loop->first && ! $propertyToEdit->image_path);
                                                @endphp
                                                <div class="group relative rounded-xl overflow-hidden border-2 {{ $isCover ? 'border-[#d4af37] ring-2 ring-[#d4af37]/30' : 'border-[#d9cab3]' }} bg-white aspect-square shadow-2xs transition-all" id="agent-prop-img-{{ $agentPropImg->id }}" data-property-id="{{ $propertyToEdit->id }}">
                                                    <img src="{{ $agentPropImg->image_url }}" alt="Property Photo" class="h-full w-full object-cover">
                                                    
                                                    {{-- Cover Control Badge / Button --}}
                                                    <div class="agent-cover-control absolute top-1 left-1 z-10">
                                                        @if ($isCover)
                                                            <span class="agent-cover-badge rounded-md bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black text-[#102b25] uppercase tracking-wider shadow-xs flex items-center gap-1">★ Cover</span>
                                                        @else
                                                            <button type="button" onclick="setAgentCoverImage({{ $propertyToEdit->id }}, {{ $agentPropImg->id }}, '{{ $agentPropImg->image_url }}')" title="Set as primary cover photo" class="agent-make-cover-btn rounded-md bg-black/75 hover:bg-[#d4af37] hover:text-[#102b25] text-white px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider transition backdrop-blur-xs cursor-pointer shadow-xs">★ Set Cover</button>
                                                        @endif
                                                    </div>

                                                    {{-- Delete Button --}}
                                                    <button type="button" onclick="deleteAgentPropertyImage({{ $propertyToEdit->id }}, {{ $agentPropImg->id }})" title="Remove photo" class="absolute top-1 right-1 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-red-600/90 text-white hover:bg-red-700 transition shadow-xs cursor-pointer">
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
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Status</label>
                                <select name="status" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                    @foreach ([
                                        'published' => 'Published',
                                        'draft' => 'Draft (unpublished)',
                                        'available' => 'Available',
                                        'sold' => 'Sold',
                                        'rented' => 'Rented',
                                        'archived' => 'Archived',
                                    ] as $statusValue => $statusLabel)
                                        <option value="{{ $statusValue }}" @selected(old('status', $propertyToEdit->status) === $statusValue)>{{ $statusLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2 pt-1">
                                <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- New listing form (revealed by the + Add Property button) --}}
                <div id="agent-listing-create-panel" class="mt-6 rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm {{ $errors->any() && ! $propertyToEdit ? '' : 'hidden' }}">
                    <h3 class="text-lg font-black text-[#1d3c34]">Post a New Property</h3>
                    <form method="POST" action="{{ route('agent.properties.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-4 md:grid-cols-2">
                        @csrf
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                            <input type="text" name="title" value="{{ old('title') }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Price (ETB)</label>
                            <input type="number" name="price" min="0" step="0.01" value="{{ old('price') }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Type</label>
                            <select name="type" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                <option value="sale" @selected(old('type', 'sale') === 'sale')>For Sale</option>
                                <option value="rent" @selected(old('type') === 'rent')>For Rent</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Category</label>
                            <select name="category" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                @foreach (['home', 'villa', 'apartment'] as $categoryOption)
                                    <option value="{{ $categoryOption }}" @selected(old('category', 'home') === $categoryOption)>{{ ucfirst($categoryOption) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Bedrooms</label>
                            <input type="number" name="bedrooms" min="0" value="{{ old('bedrooms', 0) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Bathrooms</label>
                            <input type="number" name="bathrooms" min="0" value="{{ old('bathrooms', 0) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Area (sq ft)</label>
                            <input type="number" name="area" min="0" value="{{ old('area') }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">City</label>
                            <input type="text" name="city" value="{{ old('city') }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Address</label>
                            <input type="text" name="address" value="{{ old('address') }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                            <textarea name="description" rows="3" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('description') }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-semibold text-slate-700">Property Photos & Cover Image</label>
                                <span id="agent-create-counter-pill" class="text-xs font-bold text-[#1d3c34] bg-[#dfeee4] px-2.5 py-0.5 rounded-full hidden">0 photos</span>
                            </div>
                            <p class="text-xs text-slate-500 mb-2">Upload multiple photos for this listing, or insert one of the luxury background presets below if you don't have custom photos yet.</p>

                            <input type="hidden" name="cover_preset" id="agent-create-cover-preset" value="">

                            {{-- Quick Luxury Background Preset Selector --}}
                            <div class="mb-3 rounded-xl border border-[#e7ddca] bg-[#fdfaf5] p-3">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                        <span>🏙️</span>
                                        <span>Best Luxury Background Presets (1-Click Insert)</span>
                                    </span>
                                    <span id="agent-create-preset-status" class="text-[11px] text-slate-500">Pick preset if no custom photos</span>
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
                                        <button type="button" onclick="selectAgentCreatePreset('{{ $pr['id'] }}', '{{ $pr['name'] }}')" class="group relative rounded-lg overflow-hidden border border-[#d9cab3] hover:border-[#d4af37] aspect-[16/10] text-left transition cursor-pointer shadow-2xs hover:shadow-xs">
                                            <img src="{{ asset($pr['file']) }}" alt="{{ $pr['name'] }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-transparent"></div>
                                            <span class="absolute bottom-1 left-1.5 right-1.5 text-[10px] font-bold text-white truncate drop-shadow">{{ $pr['name'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div id="agent-create-dropzone" class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#d9cab3] bg-[#fdfaf5] p-5 text-center transition-all hover:border-[#1d3c34] hover:bg-[#dfeee4]/20 cursor-pointer">
                                <input id="agent-create-images" type="file" name="images[]" multiple accept="image/png,image/jpeg,image/webp,image/avif" class="sr-only">
                                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#1d3c34]/10 text-[#1d3c34] mb-2 pointer-events-none">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <p class="text-sm font-bold text-slate-700 pointer-events-none"><span class="text-[#1d3c34] underline decoration-2">Click to choose photos</span> or drag & drop here</p>
                                <p class="text-xs text-slate-400 mt-1 pointer-events-none">PNG, JPG, WEBP — select as many photos as you want (up to 50)</p>
                            </div>
                            <div id="agent-create-images-preview" class="mt-3.5 hidden grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2.5"></div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Publish</label>
                            <select name="status" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                <option value="published" @selected(old('status', 'published') === 'published')>Publish immediately</option>
                                <option value="draft" @selected(old('status') === 'draft')>Save as draft</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 pt-1">
                            <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                                Post Property
                            </button>
                        </div>
                    </form>
                </div>

                {{-- My listings table (same style as the admin dashboard property list) --}}
                <div class="mt-6 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#1d3c34] text-[#f8f3eb]">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Image</th>
                                <th class="px-5 py-4 font-semibold">Title</th>
                                <th class="px-5 py-4 font-semibold">Price</th>
                                <th class="px-5 py-4 font-semibold">Type</th>
                                <th class="px-5 py-4 font-semibold">Details</th>
                                <th class="px-5 py-4 font-semibold">Status</th>
                                <th class="px-5 py-4 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($properties as $property)
                                <tr class="border-t border-[#e7ddca] bg-white">
                                    <td class="px-5 py-4">
                                        <img src="{{ $property->primary_image_url }}" alt="{{ $property->title }}" class="h-12 w-16 rounded-lg object-cover ring-1 ring-[#e7ddca]">
                                    </td>
                                    <td class="max-w-[220px] px-5 py-4">
                                        <p class="truncate font-semibold text-[#1d3c34]">{{ $property->title }}</p>
                                        @if ($property->city || $property->address)
                                            <p class="truncate text-xs text-slate-500">{{ $property->city }}{{ $property->city && $property->address ? ' · ' : '' }}{{ $property->address }}</p>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">ETB {{ number_format($property->price) }}{{ $property->type === 'rent' ? '/mo' : '' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $property->type === 'rent' ? 'bg-[#e7ecf5] text-[#34547d]' : 'bg-[#f4efe7] text-[#1d3c34]' }}">
                                            {{ $property->type === 'rent' ? 'Rent' : 'Sale' }}
                                        </span>
                                        <span class="mt-1 block text-xs text-slate-500">{{ ucfirst($property->property_category ?? 'Property') }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-600">
                                        {{ $property->bedrooms }} bed · {{ $property->bathrooms }} bath<br>
                                        {{ number_format($property->area) }} sq ft
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full {{ $property->is_active ? 'bg-[#dfeee4] text-[#1d3c34]' : 'bg-[#f9ecd0] text-[#9b6c17]' }} px-2.5 py-1 text-xs font-bold">
                                            {{ $property->is_active ? 'Published' : 'Unpublished' }}
                                        </span>
                                        @if (in_array($property->status, ['sold', 'rented']))
                                            <span class="mt-1 block rounded-full bg-[#f8ddd9] px-2.5 py-1 text-xs font-bold text-[#a24339]">{{ ucfirst($property->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('agent.portal', ['section' => 'listings', 'edit_property' => $property->id]) }}" class="rounded-full bg-[#1d3c34] px-3 py-2 text-xs font-bold text-white">Edit</a>
                                            <form method="POST" action="{{ route('agent.properties.togglePublish', $property) }}">
                                                @csrf
                                                <button type="submit" class="rounded-full border border-[#b9a98b] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                                                    {{ $property->is_active ? 'Unpublish' : 'Publish' }}
                                                </button>
                                            </form>
                                            @php
                                                $soldStatus = $property->type === 'rent' ? 'rented' : 'sold';
                                                $markLabel = $property->type === 'rent' ? 'Mark Rented' : 'Mark Sold';
                                            @endphp
                                            <form method="POST" action="{{ route('agent.properties.toggleSold', $property) }}">
                                                @csrf
                                                <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#f8f3eb] px-3 py-2 text-xs font-bold text-[#1d3c34]">
                                                    {{ $property->status === $soldStatus ? 'Mark Available' : $markLabel }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('agent.properties.archive', $property) }}">
                                                @csrf
                                                <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#f9f4ed] px-3 py-2 text-xs font-bold text-[#1d3c34]" {{ $property->status === 'archived' ? 'disabled' : '' }}>
                                                    Archive
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('agent.properties.destroy', $property) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-3 py-2 text-xs font-bold text-[#a24339]" data-confirm="Delete this listing?">Delete</button>
                                            </form>
                                            @if ($property->is_active)
                                                <a href="{{ route('properties.show', $property->slug) }}" class="text-xs font-semibold text-[#2d5d4d] hover:underline">View</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-6 text-center text-slate-500">No listings yet. Click “+ Add Property” to post your first one.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Client messages --}}
            <section id="messages" data-section="messages" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-black text-[#1d3c34]">Client Messages</h2>
                        <p class="mt-1 text-sm text-slate-600">Direct inquiries sent to you through the contact and request forms.</p>
                    </div>
                    @if ($unreadInquiries->isNotEmpty())
                        <a href="{{ route('agent.portal', ['mark_read' => 1]) }}" class="rounded-full border border-[#b9a98b] bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-[#1d3c34] transition hover:bg-[#f0e4cf]">
                            Mark all read ({{ $unreadInquiries->count() }})
                        </a>
                    @endif
                </div>

                <div class="mt-6 space-y-3">
                    @forelse ($inquiries as $inquiry)
                        <article class="message-card cursor-pointer overflow-hidden rounded-[1.5rem] border shadow-sm transition hover:shadow-md {{ $inquiry->read_at ? 'border-[#d9cab3] bg-white' : 'border-[#b9a98b] bg-[#fffaf2]' }}"
                            data-inquiry-id="{{ $inquiry->id }}"
                            data-read="{{ $inquiry->read_at ? '1' : '0' }}"
                            data-read-url="{{ route('agent.inquiries.read', $inquiry) }}">
                            {{-- Collapsed notification header --}}
                            <button type="button" class="message-toggle flex w-full items-center justify-between gap-3 px-5 py-4 text-left">
                                <span class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $inquiry->read_at ? 'bg-[#edf2ee] text-[#2d5d4d]' : 'bg-[#f9ecd0] text-[#9b6c17]' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-black text-[#1d3c34]">
                                            {{ $inquiry->subject }}
                                            @if (! $inquiry->read_at)
                                                <span class="message-new-badge ml-2 inline-block rounded-full bg-[#f9ecd0] px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.14em] text-[#9b6c17]">New</span>
                                            @endif
                                        </span>
                                        <span class="block truncate text-xs text-slate-500">{{ $inquiry->name }} · {{ $inquiry->created_at->format('M d, Y H:i') }}</span>
                                    </span>
                                </span>
                                <svg class="message-chevron h-4 w-4 shrink-0 text-slate-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            {{-- Expanded body --}}
                            <div class="message-body hidden border-t border-[#f0e8d8] px-5 py-4">
                                <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
                                    <span class="font-semibold text-[#1d3c34]">{{ $inquiry->name }}</span>
                                    <a href="mailto:{{ $inquiry->email }}" class="inline-flex items-center gap-1 font-semibold text-[#2d5d4d] hover:underline">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        {{ $inquiry->email }}
                                    </a>
                                    @if ($inquiry->phone)
                                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $inquiry->phone) }}" class="inline-flex items-center gap-1 font-semibold text-[#2d5d4d] hover:underline">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            {{ $inquiry->phone }}
                                        </a>
                                    @endif
                                </div>
                                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $inquiry->message }}</p>

                                <div class="mt-4 flex items-center justify-end border-t border-[#f0e8d8] pt-3">
                                    <form method="POST" action="{{ route('agent.inquiries.destroy', $inquiry) }}" data-confirm="Are you sure you want to delete this message?" onclick="event.stopPropagation()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1.5 text-xs font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            Delete Message
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-[#d9cab3] bg-[#fffaf2] p-10 text-center">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#edf2ee] text-[#2d5d4d]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <p class="mt-4 text-sm text-slate-600">No client messages yet. Inquiries from your public profile or client property requests will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Client reviews / feedback received --}}
            <section id="feedback" data-section="feedback" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-black text-[#1d3c34]">Client Reviews</h2>
                        <p class="mt-1 text-sm text-slate-600">Feedback clients left on your public profile. Admin reviews it before it shows on the home page.</p>
                    </div>
                    <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold text-[#2d5d4d]">
                        {{ $feedbackCount > 0 ? number_format($averageRating, 1).' ★ average · '.$feedbackCount.' approved' : 'No rating yet' }}
                    </span>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse ($feedbacks as $feedback)
                        <article class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-[#1d3c34]">{{ $feedback->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $feedback->email }}</p>
                                </div>
                                <div class="flex shrink-0 flex-wrap items-center gap-2">
                                    <span class="font-bold text-[#9b6c17]">{{ str_repeat('★', $feedback->rating).''.str_repeat('☆', 5 - $feedback->rating) }}</span>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $feedback->is_approved ? 'bg-[#dfeee4] text-[#1d3c34]' : 'bg-[#f9ecd0] text-[#9b6c17]' }}">
                                        {{ $feedback->is_approved ? 'Approved' : 'Pending review' }}
                                    </span>
                                </div>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-slate-600">“{{ $feedback->message }}”</p>
                            <p class="mt-2 text-xs text-slate-400">{{ $feedback->created_at?->format('M d, Y') }}</p>
                        </article>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-[#d9cab3] bg-[#fffaf2] p-10 text-center">
                            <p class="text-sm text-slate-600">No client reviews yet. When clients leave feedback on your public profile, it will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Profile settings --}}
            <section id="profile" data-section="profile" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#1d3c34]">My Profile Settings</h2>
                        <p class="mt-1 text-sm text-slate-600">Update your public agent profile information, photo, and login password.</p>
                    </div>
                    <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">Active Profile</span>
                </div>

                <form method="POST" action="{{ route('agent.profile.update') }}" enctype="multipart/form-data" class="mt-6 grid gap-4 md:grid-cols-2">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $agent->name) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $agent->email) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $agent->phone) }}" placeholder="+251 900 000 000" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Title / Specialty</label>
                        <input type="text" name="bio" value="{{ old('bio', $agent->bio) }}" placeholder="e.g. Senior Property Advisor" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <label class="block text-sm font-semibold text-slate-700">New Password (optional)</label>
                            <button type="button" onclick="generateStrongPassword('#agent-profile-password')" class="text-xs font-semibold text-[#1d3c34] hover:underline">
                                Suggest Strong Password
                            </button>
                        </div>
                        <input id="agent-profile-password" type="password" name="password" minlength="8" placeholder="Leave blank to keep current password" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        <p class="mt-1 text-xs text-slate-500">If changing, must be at least 8 characters with uppercase, lowercase, numbers, and symbols.</p>
                        @error('password')
                            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Profile Photo</label>
                        <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2 text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        @if ($agent->photo_path)
                            <div class="mt-2 flex items-center gap-2">
                                <img src="{{ $agent->photo_url }}" alt="{{ $agent->name }}" class="h-10 w-10 rounded-full object-cover ring-1 ring-[#d9cab3]">
                                <span class="text-xs text-slate-500">Current photo</span>
                            </div>
                        @endif
                    </div>

                    <div class="md:col-span-2 pt-2">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                            Save Profile Changes
                        </button>
                    </div>
                </form>
            </section>
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
                    history.replaceState(null, '', '#' + target);
                    const targetPanel = document.querySelector('[data-section="' + target + '"]');
                    if (targetPanel) {
                        targetPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });

            const params = new URLSearchParams(window.location.search);
            const paramSection = params.get('section');
            const initialHash = window.location.hash.replace('#', '');

            let initialSection = 'overview';
            if (params.has('edit_property')) {
                initialSection = 'listings';
            } else if (paramSection && document.querySelector('[data-section="' + paramSection + '"]')) {
                initialSection = paramSection;
            } else if (initialHash && document.querySelector('[data-section="' + initialHash + '"]')) {
                initialSection = initialHash;
            }

            if (document.querySelector('[data-section="' + initialSection + '"]')) {
                activateSection(initialSection);
            }

            if (params.has('edit_property')) {
                const editPanel = document.getElementById('edit-listing-' + params.get('edit_property'));
                if (editPanel) {
                    setTimeout(function () {
                        editPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 50);
                }
            }

            // "+ Add Property" button toggles the create-listing form.
            const addToggle = document.getElementById('agent-add-listing-toggle');
            const addPanel = document.getElementById('agent-listing-create-panel');

            if (addToggle && addPanel) {
                if (!addPanel.classList.contains('hidden')) {
                    addToggle.textContent = addToggle.dataset.labelClose || 'Close Form';
                }

                addToggle.addEventListener('click', function () {
                    const willShow = addPanel.classList.toggle('hidden') === false;
                    addToggle.textContent = willShow ? (addToggle.dataset.labelClose || 'Close Form') : (addToggle.dataset.labelCreate || '+ Add Property');

                    if (willShow) {
                        addPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            }

            // Message notifications: expand on click and mark as read.
            document.querySelectorAll('.message-card').forEach(function (card) {
                const toggle = card.querySelector('.message-toggle');
                const body = card.querySelector('.message-body');
                const badge = card.querySelector('.message-new-badge');
                const chevron = card.querySelector('.message-chevron');

                if (! toggle || ! body) {
                    return;
                }

                toggle.addEventListener('click', function () {
                    const isOpen = ! body.classList.toggle('hidden');
                    chevron.classList.toggle('rotate-180', isOpen);

                    if (isOpen && card.dataset.read === '0') {
                        card.dataset.read = '1';
                        card.classList.remove('border-[#b9a98b]', 'bg-[#fffaf2]');
                        card.classList.add('border-[#d9cab3]', 'bg-white');
                        if (badge) {
                            badge.remove();
                        }

                        fetch(card.dataset.readUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        }).catch(function () {
                            // Best-effort: the message stays visually read for this session.
                        });
                    }
                });
            });

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

            setupMultiPhotoUploader("agent-create-images", "agent-create-dropzone", "agent-create-images-preview", "agent-create-counter-pill");
            setupMultiPhotoUploader("agent-edit-images", "agent-edit-dropzone", "agent-edit-images-preview", "agent-edit-counter-pill");

            window.deleteAgentPropertyImage = function (propertyId, imageId) {
                if (!confirm('Remove this photo from your listing?')) return;
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;

                fetch(`/agent-portal/properties/${propertyId}/images/${imageId}`, {
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
                        document.getElementById(`agent-prop-img-${imageId}`)?.remove();
                    } else {
                        alert('Could not remove photo.');
                    }
                })
                .catch(() => alert('Network error while removing photo.'));
            };

            window.setAgentCoverImage = function (propertyId, imageId, imageUrl) {
                const hiddenId = document.getElementById('agent-cover-image-id');
                if (hiddenId) hiddenId.value = imageId;

                const hiddenPreset = document.getElementById('agent-cover-preset');
                if (hiddenPreset) hiddenPreset.value = '';

                const activeCoverImg = document.getElementById('agent-active-cover-img');
                if (activeCoverImg && imageUrl) activeCoverImg.src = imageUrl;

                const statusText = document.getElementById('agent-cover-status-text');
                if (statusText) statusText.textContent = 'Active cover photo updated';

                updateAgentThumbnailsCoverState(imageId);

                if (propertyId) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;
                    fetch(`/agent-portal/properties/${propertyId}/images/${imageId}/cover`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    }).then(res => res.json()).then(data => {
                        if (data.ok) showAgentToastNotification('Cover photo updated successfully!');
                    }).catch(err => console.log('Cover updated locally', err));
                }
            };

            window.updateAgentThumbnailsCoverState = function (activeImageId) {
                const container = document.getElementById('agent-existing-images-container');
                if (!container) return;

                const cards = container.querySelectorAll('[id^="agent-prop-img-"]');
                cards.forEach(card => {
                    const id = card.id.replace('agent-prop-img-', '');
                    const controlDiv = card.querySelector('.agent-cover-control');
                    if (!controlDiv) return;

                    const propertyId = card.getAttribute('data-property-id') || '';

                    if (id == activeImageId) {
                        card.classList.remove('border-[#d9cab3]');
                        card.classList.add('border-[#d4af37]', 'ring-2', 'ring-[#d4af37]/30');
                        controlDiv.innerHTML = '<span class="agent-cover-badge rounded-md bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black text-[#102b25] uppercase tracking-wider shadow-xs flex items-center gap-1">★ Cover</span>';
                    } else {
                        card.classList.remove('border-[#d4af37]', 'ring-2', 'ring-[#d4af37]/30');
                        card.classList.add('border-[#d9cab3]');
                        const img = card.querySelector('img');
                        const src = img ? img.src : '';
                        controlDiv.innerHTML = `<button type="button" onclick="setAgentCoverImage(${propertyId}, ${id}, '${src}')" title="Set as primary cover photo" class="agent-make-cover-btn rounded-md bg-black/75 hover:bg-[#d4af37] hover:text-[#102b25] text-white px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider transition backdrop-blur-xs cursor-pointer shadow-xs">★ Set Cover</button>`;
                    }
                });
            };

            window.selectAgentPresetBackground = function (presetId, imageUrl, propertyId) {
                const hiddenPreset = document.getElementById('agent-cover-preset');
                if (hiddenPreset) hiddenPreset.value = presetId;

                const hiddenId = document.getElementById('agent-cover-image-id');
                if (hiddenId) hiddenId.value = '';

                const activeCoverImg = document.getElementById('agent-active-cover-img');
                if (activeCoverImg) activeCoverImg.src = imageUrl;

                const statusText = document.getElementById('agent-cover-status-text');
                if (statusText) statusText.textContent = `Selected luxury preset: ${presetId}`;

                if (propertyId) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;
                    fetch(`/agent-portal/properties/${propertyId}/preset-cover`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ preset: presetId })
                    }).then(res => res.json()).then(data => {
                        if (data.ok) showAgentToastNotification('Luxury background preset set as cover!');
                    }).catch(err => console.log('Preset chosen', err));
                } else {
                    showAgentToastNotification('Luxury background preset selected!');
                }
            };

            window.selectAgentCreatePreset = function (presetId, presetName) {
                const hiddenPreset = document.getElementById('agent-create-cover-preset');
                if (hiddenPreset) hiddenPreset.value = presetId;

                const statusEl = document.getElementById('agent-create-preset-status');
                if (statusEl) {
                    statusEl.textContent = `✓ Selected: ${presetName}`;
                    statusEl.className = 'text-[11px] font-bold text-[#1d3c34]';
                }
                showAgentToastNotification(`Preset "${presetName}" selected as initial cover!`);
            };

            window.previewAgentCoverImage = function (input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const activeCoverImg = document.getElementById('agent-active-cover-img');
                        if (activeCoverImg) activeCoverImg.src = e.target.result;
                        const statusText = document.getElementById('agent-cover-status-text');
                        if (statusText) statusText.textContent = 'New cover photo file selected (will save with form)';
                        showAgentToastNotification('New cover photo selected!');
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            };

            function showAgentToastNotification(message) {
                let toast = document.getElementById('global-agent-toast');
                if (!toast) {
                    toast = document.createElement('div');
                    toast.id = 'global-agent-toast';
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
    @include('layouts.partials.confirm-modal')
</body>
</html>
