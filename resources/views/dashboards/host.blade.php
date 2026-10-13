@extends('dashboards.layout')

@section('title', 'Host Dashboard')
@section('portal_name', 'Host Workspace')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    <a href="#bookings" data-target-section="bookings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>📅</span>
            <span>Bookings & Arrivals</span>
        </span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $upcomingBookings->count() }}</span>
    </a>
    <a href="#accommodations" data-target-section="accommodations" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>🏡</span>
            <span>Accommodations</span>
        </span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $properties->count() }}</span>
    </a>
    <a href="#reviews-payouts" data-target-section="reviews-payouts" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>💰</span>
            <span>Reviews & Payouts</span>
        </span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $reviews->count() }}</span>
    </a>
@endsection

@section('content')
<div x-data="{ openStayModal: false }" class="space-y-8">
    {{-- Banner --}}
    <div id="overview" class="scroll-mt-6 rounded-3xl border border-[#d9cab3] bg-gradient-to-r from-[#1d3c34] to-[#2d5d4d] p-6 text-white shadow-sm md:p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-3 py-1 text-xs font-bold text-[#f8e7b9] uppercase tracking-wider">Short-Stay Host</span>
                <h1 class="mt-2 text-2xl md:text-3xl font-black text-white">Host Operations &amp; Hospitality Desk</h1>
                <p class="mt-1 text-sm text-slate-200">Manage your short-stay listings, guest check-in calendar, occupancy rates, earnings, payouts, and reviews.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button @click="openStayModal = true" type="button" class="rounded-full bg-emerald-500 hover:bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white transition shadow-sm flex items-center gap-1.5">
                    <span>+</span> Register Short Stay
                </button>
                <a href="{{ route('stays.index') }}" class="rounded-full bg-[#d4af37] px-5 py-2.5 text-xs font-bold text-[#1d3c34] hover:bg-[#b7842d] transition shadow-sm">
                    Short Stays Feed &rarr;
                </a>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 border-t border-white/20 pt-6">
            <div>
                <p class="text-xs font-semibold text-slate-300">Managed Stays</p>
                <p class="text-2xl font-black text-white">{{ $properties->count() }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-300">Total Bookings</p>
                <p class="text-2xl font-black text-white">{{ $bookings->count() }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-300">Est. Occupancy</p>
                <p class="text-2xl font-black text-[#f8e7b9]">{{ $occupancyRate }}%</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-300">Gross Revenue (ETB)</p>
                <p class="text-2xl font-black text-emerald-300">{{ number_format($totalRevenue) }}</p>
            </div>
        </div>
    </div>

    {{-- 1. Upcoming & Checked-In Bookings --}}
    <div id="bookings" class="scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Upcoming &amp; Live Bookings</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e7ddca] text-sm">
                <thead>
                    <tr class="bg-[#fbf9f5] text-left text-xs font-black uppercase text-[#587165]">
                        <th class="py-3 px-4">Booking</th>
                        <th class="py-3 px-4">Property</th>
                        <th class="py-3 px-4">Guest</th>
                        <th class="py-3 px-4">Dates</th>
                        <th class="py-3 px-4">Payout Amount</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca]">
                    @forelse ($upcomingBookings as $booking)
                        <tr class="hover:bg-[#fcfaf7]">
                            <td class="py-3 px-4 font-mono font-bold text-xs text-[#1d3c34]">{{ $booking->booking_reference }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $booking->property?->title ?? 'Stay' }}</td>
                            <td class="py-3 px-4 text-xs font-medium">{{ $booking->guest_name }}</td>
                            <td class="py-3 px-4 text-xs text-slate-600">{{ $booking->check_in_date?->format('M d') }} – {{ $booking->check_out_date?->format('M d') }}</td>
                            <td class="py-3 px-4 font-bold text-emerald-800">{{ number_format($booking->total_amount) }} ETB</td>
                            <td class="py-3 px-4">
                                <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $booking->status === 'checked_in' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-xs text-slate-400">No active bookings right now.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 2. Short-Stay Properties & Calendar Status --}}
    <div id="accommodations" class="scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">My Accommodations &amp; Calendar Availability</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($properties as $prop)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="rounded-full bg-emerald-100 text-emerald-800 px-2 py-0.5 text-[10px] font-bold">Available for Booking</span>
                            <span class="text-xs font-bold text-emerald-800">{{ number_format($prop->price) }} ETB / night</span>
                        </div>
                        <h4 class="mt-2 font-bold text-base text-[#1d3c34] line-clamp-1">{{ $prop->title }}</h4>
                        <p class="text-xs text-slate-500 mt-0.5">📍 {{ $prop->city }}, {{ $prop->subcity ?? 'Bole' }}</p>

                        {{-- Simple Calendar Blocks Visualizer --}}
                        <div class="mt-4 rounded-xl border border-[#e7ddca] bg-white p-3">
                            <span class="text-[11px] font-bold text-slate-700">30-Day Availability Window:</span>
                            <div class="mt-2 grid grid-cols-10 gap-1 text-center">
                                @for ($i = 1; $i <= 20; $i++)
                                    <span class="rounded py-1 text-[9px] font-bold {{ $i % 4 === 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-50 text-emerald-700' }}" title="{{ $i % 4 === 0 ? 'Booked' : 'Available' }}">
                                        {{ $i }}
                                    </span>
                                @endfor
                            </div>
                            <div class="mt-2 flex items-center gap-3 text-[10px] text-slate-500">
                                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Available</span>
                                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-amber-500"></span> Booked</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-[#e7ddca] flex items-center justify-between">
                        <span class="text-xs text-slate-500">{{ $prop->bookings->count() }} total stays</span>
                        <div class="flex items-center gap-2">
                            <form action="{{ route('host.stays.publish', $prop) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="rounded-lg px-2 py-1 text-[11px] font-bold {{ $prop->is_active ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' }}">
                                    {{ $prop->is_active ? 'Pause' : 'Publish' }}
                                </button>
                            </form>
                            <a href="{{ route('properties.show', $prop->slug) }}" class="text-xs font-bold text-[#1d3c34] hover:underline">View &rarr;</a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="col-span-3 text-xs text-slate-400 py-4 text-center">No short-stay properties listed.</p>
            @endforelse
        </div>
    </div>

    {{-- 3. Guest Reviews & Payout History --}}
    <div id="reviews-payouts" class="scroll-mt-6 grid gap-6 md:grid-cols-2">
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Guest Reviews &amp; Replies</h3>
            <div class="mt-4 space-y-4">
                @forelse ($reviews as $rev)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-4 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#1d3c34]">{{ $rev->property?->title ?? 'Stay' }}</span>
                            <span class="font-bold text-[#d4af37]">★ {{ $rev->rating }}/5</span>
                        </div>
                        <p class="text-slate-600 mt-1">"{{ $rev->comment ?? $rev->review_title }}"</p>
                        @if ($rev->host_reply)
                            <div class="mt-2 rounded-lg bg-white p-2 border border-[#e7ddca]">
                                <span class="font-bold text-[#1d3c34]">Your reply:</span> {{ $rev->host_reply }}
                            </div>
                        @else
                            <form method="POST" action="{{ route('host.reviews.reply', $rev) }}" class="mt-3 flex gap-2">
                                @csrf
                                <input type="text" name="host_reply" required placeholder="Write a response to guest..." class="flex-1 rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs">
                                <button type="submit" class="rounded-xl bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#254d43]">Reply</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No guest reviews yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Recent Payout Transactions</h3>
            <div class="mt-4 space-y-3">
                @forelse ($payouts as $pay)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-3 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-mono font-bold text-slate-600">{{ $pay->transaction_number }}</span>
                            <p class="text-slate-500 mt-0.5">{{ $pay->payment_method ?? 'Bank Transfer' }} &bull; {{ $pay->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-emerald-800">{{ number_format($pay->amount) }} ETB</span>
                            <span class="block text-[10px] font-bold text-emerald-700">✓ Settled</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No payout records found.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Register Short-Stay Modal --}}
    <div x-show="openStayModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 p-4 sm:p-6 md:p-20 backdrop-blur-sm" @keydown.escape.window="openStayModal = false">
        <div class="relative mx-auto max-w-2xl rounded-3xl bg-white p-6 md:p-8 shadow-2xl border border-[#e7ddca]" @click.outside="openStayModal = false">
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                <div>
                    <h3 class="text-xl font-black text-[#1d3c34]">Register Short-Stay Accommodation</h3>
                    <p class="text-xs text-slate-500">List guest rooms or apartments for daily/nightly bookings</p>
                </div>
                <button type="button" @click="openStayModal = false" class="rounded-full bg-slate-100 p-2 text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form action="{{ route('host.stays.store') }}" method="POST" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700">Stay Name / Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Luxury Bole Studio with High-Speed WiFi" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Nightly Rate (ETB) *</label>
                        <input type="number" name="price" required min="1" step="any" placeholder="e.g. 3500" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Cleaning Fee (ETB)</label>
                        <input type="number" name="cleaning_fee" value="0" min="0" step="any" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Initial Status *</label>
                        <select name="status" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none">
                            <option value="published">Publish Immediately</option>
                            <option value="draft">Save as Draft</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Bedrooms</label>
                        <input type="number" name="bedrooms" value="1" min="0" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Bathrooms</label>
                        <input type="number" name="bathrooms" value="1" min="1" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Check-in Time</label>
                        <input type="text" name="check_in_time" value="14:00" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Check-out Time</label>
                        <input type="text" name="check_out_time" value="11:00" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">City *</label>
                        <input type="text" name="city" value="Addis Ababa" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Subcity</label>
                        <input type="text" name="subcity" value="Bole" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Neighborhood / Address</label>
                        <input type="text" name="address" placeholder="Near Atlas / Cameroon St." class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">House Rules &amp; Instructions</label>
                    <textarea name="house_rules" rows="2" placeholder="e.g. No smoking indoors. Quiet hours after 22:00. Keycard available at reception." class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Description</label>
                    <textarea name="description" rows="3" placeholder="Describe the space, amenities, proximity to airport, transport, etc." class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none"></textarea>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-[#e7ddca] pt-4">
                    <button type="button" @click="openStayModal = false" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 px-6 py-2.5 text-xs font-bold text-white shadow-sm transition">Register &amp; Save Stay</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
