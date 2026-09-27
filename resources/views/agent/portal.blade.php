@extends('app')

@section('title', $agent->name.' — Agent Portal')
@section('description', 'Manage your buy requests, client messages, and listings in the agent portal.')

@section('content')
    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        {{-- Welcome hero --}}
        <div class="overflow-hidden rounded-[1.5rem] bg-gradient-to-br from-[#1d3c34] via-[#2d5145] to-[#5b6d60] p-5 text-[#f9f3e9] shadow-lg shadow-[#1d3c34]/20 md:p-6">
            <div class="pointer-events-none absolute -top-24 -right-24 h-64 w-64 rounded-full bg-white/5"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-16 h-72 w-72 rounded-full bg-[#d9cab3]/10"></div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <img src="{{ $agent->photo_url }}" alt="{{ $agent->name }}" class="h-14 w-14 rounded-xl object-cover ring-2 ring-[#d9cab3] shadow-md">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-[#e7d8b7]">Agent Portal</p>
                        <h1 class="mt-0.5 text-2xl font-black md:text-3xl">{{ $agent->name }}</h1>
                        <p class="mt-0.5 text-xs text-[#dfeee4]">{{ $agent->bio ?: 'Property consultant' }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('agents.show', $agent) }}" class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Public Profile
                    </a>
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3m10-11v10a1 1 0 01-1 1h-3m-6 0h6" />
                        </svg>
                        Home
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-[#f8f3eb] px-4 py-2 text-sm font-bold text-[#1d3c34] transition hover:bg-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="mt-6 flex items-start gap-3 rounded-xl border border-[#d7e5d2] bg-[#edf9ee] px-4 py-3 text-sm font-medium text-[#214f3a]">
                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Stat cards --}}
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">New Messages</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#edf2ee] text-[#2d5d4d]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-[#1d3c34]">{{ $unreadInquiries->count() }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#f9ebd8] p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Pending Requests</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#f1e4cf] text-[#b7842d]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-[#b7842d]">{{ $pendingOrders->count() }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#f4efe7] p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">My Listings</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#e8e0d0] text-[#1d3c34]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3m10-11v10a1 1 0 01-1 1h-3m-6 0h6" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-[#2d5d4d]">{{ $propertiesCount }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#edf3ee] p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Rating</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#dfeee4] text-[#9b6c17]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-[#2d5d4d]">{{ $feedbackCount > 0 ? number_format($averageRating, 1).' ★' : '—' }}</p>
            </div>
        </div>

        {{-- Buy requests (orders) --}}
        <section class="mt-12">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-black text-[#1d3c34]">Buy Requests</h2>
                    <p class="mt-1 text-sm text-slate-600">Clients who asked to buy one of your listings. Accept or reject each request.</p>
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
                                <p class="mt-1 text-sm text-slate-600">
                                    {{ $order->name }} · <a href="mailto:{{ $order->email }}" class="font-semibold text-[#2d5d4d]">{{ $order->email }}</a>
                                    @if ($order->phone) · <a href="tel:{{ preg_replace('/[^0-9+]/', '', $order->phone) }}" class="font-semibold text-[#2d5d4d]">{{ $order->phone }}</a>@endif
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] {{ $order->isRental() ? 'bg-[#e7ecf5] text-[#34547d]' : 'bg-[#f4efe7] text-[#1d3c34]' }}">
                                    {{ $order->isRental() ? 'For Rent' : 'For Sale' }}
                                </span>
                                @if ($order->offer_amount)
                                    <span class="rounded-full bg-[#f4efe7] px-3 py-1 text-sm font-black text-[#1d3c34]">{{ $order->isRental() ? 'Rent: $' : 'Offer: $' }}{{ number_format($order->offer_amount) }}{{ $order->isRental() ? '/mo' : '' }}</span>
                                @endif
                                <span class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] {{ $order->status === 'pending' ? 'bg-[#f9ecd0] text-[#9b6c17]' : ($order->status === 'accepted' ? 'bg-[#dfeee4] text-[#1d3c34]' : 'bg-[#f8ddd9] text-[#a24339]') }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>

                        @if ($order->message)
                            <p class="mt-3 rounded-xl bg-[#f9f4ed] px-4 py-3 text-sm leading-7 text-slate-600">{{ $order->message }}</p>
                        @endif

                        @if ($order->agent_note)
                            <p class="mt-3 rounded-xl bg-[#f9f4ed] px-4 py-3 text-sm text-slate-600"><span class="font-bold text-[#1d3c34]">Your note:</span> {{ $order->agent_note }}</p>
                        @endif

                        @if ($order->status === 'accepted' && $order->hasAgreement())
                            <div class="mt-4 flex flex-wrap items-center gap-3 rounded-xl border border-[#dfeee4] bg-[#edf9ee] px-4 py-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#214f3a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-[#214f3a]">{{ $order->isRental() ? 'Rental' : 'Purchase' }} agreement generated{{ $order->agreed_at ? ' · '.$order->agreed_at->format('M d, Y') : '' }}</p>
                                    <p class="text-xs text-[#4a6b58]">Branded with the company logo and campaign header, ready to sign.</p>
                                </div>
                                <a href="{{ route('orders.agreement', $order) }}" class="rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-white transition hover:bg-[#254d43]">
                                    Download PDF
                                </a>
                            </div>
                        @endif

                        @if ($order->status === 'pending')
                            <div class="mt-4 flex flex-wrap items-end gap-3">
                                <form method="POST" action="{{ route('agent.orders.status', $order) }}" class="flex flex-wrap items-end gap-3">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="accepted">
                                    <input type="text" name="agent_note" placeholder="Optional note to the client" class="min-w-0 flex-1 rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4] sm:w-64">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]">Accept</button>
                                </form>
                                <form method="POST" action="{{ route('agent.orders.status', $order) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="rounded-full border border-[#c86b5c] bg-[#fef3f1] px-5 py-2.5 text-sm font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">Reject</button>
                                </form>
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-[#d9cab3] bg-[#fffaf2] p-10 text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#f9ebd8] text-[#b7842d]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </span>
                        <p class="mt-4 text-sm text-slate-600">No buy requests yet. When a client clicks “Buy / Request” on one of your listings, it appears here.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Client messages (notifications) --}}
        <section class="mt-12">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-black text-[#1d3c34]">Client Messages</h2>
                    <p class="mt-1 text-sm text-slate-600">Inquiries sent to you through the contact form.</p>
                </div>
                @if ($unreadInquiries->isNotEmpty())
                    <a href="{{ route('agent.portal', ['mark_read' => 1]) }}" class="rounded-full border border-[#b9a98b] bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-[#1d3c34] transition hover:bg-[#f0e4cf]">
                        Mark all read ({{ $unreadInquiries->count() }})
                    </a>
                @endif
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($inquiries as $inquiry)
                    <article class="rounded-[1.5rem] border p-5 shadow-sm md:p-6 {{ $inquiry->read_at ? 'border-[#d9cab3] bg-white' : 'border-[#b9a98b] bg-[#fffaf2]' }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-black text-[#1d3c34]">
                                    {{ $inquiry->subject }}
                                    @if (! $inquiry->read_at)
                                        <span class="ml-2 inline-block rounded-full bg-[#f9ecd0] px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.14em] text-[#9b6c17]">New</span>
                                    @endif
                                </h3>
                                <p class="mt-1 text-sm text-slate-600">
                                    {{ $inquiry->name }} · <a href="mailto:{{ $inquiry->email }}" class="font-semibold text-[#2d5d4d]">{{ $inquiry->email }}</a>
                                    @if ($inquiry->phone) · <a href="tel:{{ preg_replace('/[^0-9+]/', '', $inquiry->phone) }}" class="font-semibold text-[#2d5d4d]">{{ $inquiry->phone }}</a>@endif
                                </p>
                            </div>
                            <span class="text-xs text-slate-500">{{ $inquiry->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $inquiry->message }}</p>
                    </article>
                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-[#d9cab3] bg-[#fffaf2] p-10 text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#edf2ee] text-[#2d5d4d]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <p class="mt-4 text-sm text-slate-600">No client messages yet. Inquiries from your public profile will appear here.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </section>
@endsection
