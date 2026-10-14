@extends('dashboards.layout')

@section('title', 'Host Workspace & Short-Stay Hub')
@section('portal_name', 'Short-Stay Host Desk')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>

    <a href="#bookings" data-target-section="bookings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>📅</span>
            <span>Bookings &amp; Front-Desk</span>
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

    <a href="#calendar" data-target-section="calendar" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>🗓️</span>
            <span>Calendar &amp; Blocks</span>
        </span>
    </a>

    <a href="#pricing" data-target-section="pricing" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>🏷️</span>
            <span>Pricing Rules</span>
        </span>
    </a>

    <a href="#rules-manual" data-target-section="rules-manual" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>📖</span>
            <span>Rules &amp; Manual</span>
        </span>
    </a>

    <a href="#cleaning" data-target-section="cleaning" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>🧹</span>
            <span>Cleaning &amp; Housekeeping</span>
        </span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $cleaningTasks->where('status', 'pending')->count() }}</span>
    </a>

    <a href="#reviews-payouts" data-target-section="reviews-payouts" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>⭐</span>
            <span>Reviews &amp; Payouts</span>
        </span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $reviews->count() }}</span>
    </a>
@endsection

@section('content')
<div x-data="{ openStayModal: false, activeEditProp: null }" class="space-y-8">
    {{-- OVERVIEW SECTION --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-3xl border border-[#e7ddca] bg-white p-5 shadow-sm">
                <span class="text-xs font-bold text-slate-500">Gross Payouts</span>
                <p class="mt-2 text-2xl font-black text-[#1d3c34]">{{ number_format($totalRevenue) }} <span class="text-xs font-normal text-slate-500">ETB</span></p>
                <span class="text-[11px] font-medium text-emerald-700">Settled &amp; Confirmed</span>
            </div>
            <div class="rounded-3xl border border-[#e7ddca] bg-white p-5 shadow-sm">
                <span class="text-xs font-bold text-slate-500">30-Day Occupancy</span>
                <p class="mt-2 text-2xl font-black text-[#1d3c34]">{{ $occupancyRate }}%</p>
                <span class="text-[11px] font-medium text-slate-500">Based on booked nights</span>
            </div>
            <div class="rounded-3xl border border-[#e7ddca] bg-white p-5 shadow-sm">
                <span class="text-xs font-bold text-slate-500">Active Accommodations</span>
                <p class="mt-2 text-2xl font-black text-[#1d3c34]">{{ $properties->where('is_active', true)->count() }}</p>
                <span class="text-[11px] font-medium text-slate-500">{{ $properties->count() }} Total registered</span>
            </div>
            <div class="rounded-3xl border border-[#e7ddca] bg-white p-5 shadow-sm">
                <span class="text-xs font-bold text-slate-500">Pending Requests</span>
                <p class="mt-2 text-2xl font-black text-amber-800">{{ $pendingRequests->count() }}</p>
                <span class="text-[11px] font-medium text-amber-700">Require triage action</span>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- 1. BOOKINGS & FRONT-DESK SECTION --}}
    <div id="bookings" data-section="bookings" class="section-panel hidden scroll-mt-6 space-y-6">
        {{-- Pending Booking Requests (Triage) --}}
        @if ($pendingRequests->isNotEmpty())
            <div class="rounded-3xl border-2 border-amber-300 bg-amber-50/60 p-6 shadow-sm">
                <h3 class="text-lg font-black text-amber-900 flex items-center gap-2">
                    <span>⚡</span>
                    <span>Pending Booking Requests (Awaiting Confirmation)</span>
                </h3>
                <p class="text-xs text-amber-700 mt-1">Review guest dates and confirm or decline reservation.</p>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-amber-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-bold text-amber-900">
                                <th class="py-2.5 px-3">Ref</th>
                                <th class="py-2.5 px-3">Accommodation</th>
                                <th class="py-2.5 px-3">Guest Details</th>
                                <th class="py-2.5 px-3">Dates</th>
                                <th class="py-2.5 px-3">Amount</th>
                                <th class="py-2.5 px-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-200">
                            @foreach ($pendingRequests as $req)
                                <tr>
                                    <td class="py-3 px-3 font-mono font-bold text-xs">{{ $req->booking_reference }}</td>
                                    <td class="py-3 px-3 font-semibold">{{ $req->property?->title }}</td>
                                    <td class="py-3 px-3 text-xs">
                                        <span class="font-bold block">{{ $req->guest_name }}</span>
                                        <span class="text-slate-600">{{ $req->guest_phone ?? $req->guest_email }}</span>
                                    </td>
                                    <td class="py-3 px-3 text-xs">{{ $req->check_in_date?->format('M d') }} &rarr; {{ $req->check_out_date?->format('M d, Y') }} ({{ $req->total_nights }}n)</td>
                                    <td class="py-3 px-3 font-bold text-emerald-900">{{ number_format($req->total_amount) }} ETB</td>
                                    <td class="py-3 px-3 text-right space-x-2">
                                        <form action="{{ route('host.bookings.accept', $req) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white px-3 py-1.5 text-xs font-bold transition">Accept</button>
                                        </form>
                                        <form action="{{ route('host.bookings.reject', $req) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Decline this booking request?')" class="rounded-xl bg-rose-100 text-rose-800 hover:bg-rose-200 px-3 py-1.5 text-xs font-bold transition">Decline</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Upcoming & Active Bookings Table --}}
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Guest Arrivals &amp; Live Stays</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-[#e7ddca] text-sm">
                    <thead>
                        <tr class="bg-[#fbf9f5] text-left text-xs font-black uppercase text-[#587165]">
                            <th class="py-3 px-4">Booking Ref</th>
                            <th class="py-3 px-4">Accommodation</th>
                            <th class="py-3 px-4">Guest</th>
                            <th class="py-3 px-4">Dates</th>
                            <th class="py-3 px-4">Total</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Front-Desk Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7ddca]">
                        @forelse ($upcomingBookings as $booking)
                            <tr class="hover:bg-[#fcfaf7]">
                                <td class="py-3 px-4 font-mono font-bold text-xs text-[#1d3c34]">{{ $booking->booking_reference }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $booking->property?->title ?? 'Stay' }}</td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="font-bold block text-slate-800">{{ $booking->guest_name }}</span>
                                    <span class="text-slate-500">{{ $booking->guest_phone ?? $booking->guest_email }}</span>
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-600">
                                    {{ $booking->check_in_date?->format('M d') }} – {{ $booking->check_out_date?->format('M d, Y') }}
                                    <span class="block text-[10px] text-slate-400">({{ $booking->total_nights }} nights)</span>
                                </td>
                                <td class="py-3 px-4 font-bold text-emerald-800">{{ number_format($booking->total_amount) }} ETB</td>
                                <td class="py-3 px-4">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $booking->status === 'checked_in' ? 'bg-emerald-100 text-emerald-800' : ($booking->status === 'confirmed' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800') }}">
                                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if ($booking->status === 'confirmed')
                                        <form action="{{ route('host.bookings.check-in', $booking) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="rounded-xl bg-[#1d3c34] hover:bg-[#254d43] text-white px-3 py-1.5 text-xs font-bold transition">Mark Checked-In</button>
                                        </form>
                                    @elseif ($booking->status === 'checked_in')
                                        <form action="{{ route('host.bookings.check-out', $booking) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="rounded-xl bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 text-xs font-bold transition">Check-Out &amp; Clean</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">&mdash;</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-xs text-slate-400">No active bookings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 2. ACCOMMODATIONS CRUD SECTION --}}
    <div id="accommodations" data-section="accommodations" class="section-panel hidden scroll-mt-6 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#e7ddca] pb-4">
            <div>
                <h3 class="text-lg font-black text-[#1d3c34]">My Registered Accommodations</h3>
                <p class="text-xs text-slate-500">Manage short-stay properties, units, and listing lifecycle</p>
            </div>
            <button type="button" @click="openStayModal = true" class="rounded-2xl bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] shadow-sm flex items-center gap-2">
                <span>➕</span>
                <span>Register New Accommodation</span>
            </button>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($properties as $prop)
                <div class="rounded-3xl border border-[#e7ddca] bg-white p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase {{ $prop->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $prop->is_active ? 'Published & Active' : 'Draft / Paused' }}
                            </span>
                            <span class="text-xs font-bold text-emerald-800">{{ number_format($prop->price) }} ETB / nt</span>
                        </div>
                        <h4 class="mt-3 font-bold text-base text-[#1d3c34]">{{ $prop->title }}</h4>
                        <p class="text-xs text-slate-500 mt-1">📍 {{ $prop->city }}, {{ $prop->subcity ?? 'Addis Ababa' }}</p>
                        <p class="text-xs text-slate-600 mt-2 line-clamp-2">{{ $prop->description ?? 'Comfortable short-stay unit.' }}</p>

                        <div class="mt-3 flex items-center gap-3 text-xs text-slate-500 border-t border-[#f2ede4] pt-2">
                            <span>🛏️ {{ $prop->bedrooms ?? 1 }} Bed</span>
                            <span>🚿 {{ $prop->bathrooms ?? 1 }} Bath</span>
                            <span>⏱️ Min {{ $prop->minimum_stay ?? 1 }}n</span>
                            <span>{{ $prop->instant_booking_enabled ? '⚡ Instant' : '📝 Request' }}</span>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-[#e7ddca] flex items-center justify-between">
                        <form action="{{ route('host.stays.publish', $prop) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="rounded-xl px-3 py-1.5 text-xs font-bold {{ $prop->is_active ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' }}">
                                {{ $prop->is_active ? 'Pause Listing' : 'Publish Listing' }}
                            </button>
                        </form>

                        <div class="flex items-center gap-2">
                            <form action="{{ route('host.stays.destroy', $prop) }}" method="POST" class="inline" onsubmit="return confirm('Permanently remove this accommodation listing?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-xl px-2.5 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50">Delete</button>
                            </form>
                            <a href="{{ route('properties.show', $prop->slug) }}" class="rounded-xl bg-[#1d3c34] text-white px-3 py-1.5 text-xs font-bold hover:bg-[#254d43]">Preview</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-12 text-center">
                    <p class="text-sm font-bold text-slate-500">No accommodations registered yet.</p>
                    <button type="button" @click="openStayModal = true" class="mt-3 rounded-2xl bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43]">Register First Stay</button>
                </div>
            @endforelse
        </div>
    </div>

    {{-- 3. CALENDAR & AVAILABILITY BLOCKS SECTION --}}
    <div id="calendar" data-section="calendar" class="section-panel hidden scroll-mt-6 space-y-6">
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Calendar Control &amp; Date Blocking</h3>
            <p class="text-xs text-slate-500 mt-1">Block dates for owner maintenance, or import external bookings from Airbnb / Booking.com.</p>

            <div class="mt-6 grid gap-6 md:grid-cols-2">
                {{-- Block Dates Form --}}
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5">
                    <h4 class="font-bold text-sm text-[#1d3c34] flex items-center gap-2">
                        <span>🔒</span>
                        <span>Block Dates (Maintenance / Personal)</span>
                    </h4>
                    @if ($properties->isNotEmpty())
                        <form action="{{ route('host.calendar.block', $properties->first()) }}" method="POST" class="mt-4 space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700">Target Accommodation</label>
                                <select onchange="this.form.action = '/host/stays/' + this.value + '/calendar/block'" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                                    @foreach ($properties as $prop)
                                        <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Check-in Date *</label>
                                    <input type="date" name="check_in_date" required min="{{ now()->toDateString() }}" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Check-out Date *</label>
                                    <input type="date" name="check_out_date" required min="{{ now()->addDay()->toDateString() }}" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700">Reason / Notes</label>
                                <input type="text" name="reason" placeholder="e.g. Deep cleaning & repaint" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                            </div>
                            <button type="submit" class="w-full rounded-xl bg-[#1d3c34] hover:bg-[#254d43] text-white py-2 text-xs font-bold transition">Block Dates on Calendar</button>
                        </form>
                    @endif
                </div>

                {{-- Record External Reservation Form --}}
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5">
                    <h4 class="font-bold text-sm text-[#1d3c34] flex items-center gap-2">
                        <span>🌐</span>
                        <span>Record External Reservation</span>
                    </h4>
                    @if ($properties->isNotEmpty())
                        <form action="{{ route('host.calendar.external', $properties->first()) }}" method="POST" class="mt-4 space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700">Target Accommodation</label>
                                <select onchange="this.form.action = '/host/stays/' + this.value + '/calendar/external'" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                                    @foreach ($properties as $prop)
                                        <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Guest Name *</label>
                                    <input type="text" name="guest_name" required placeholder="Guest full name" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Platform *</label>
                                    <select name="platform" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                                        <option value="airbnb">Airbnb</option>
                                        <option value="booking_com">Booking.com</option>
                                        <option value="vrbo">Vrbo</option>
                                        <option value="walk_in">Walk-in / Offline</option>
                                        <option value="direct">Direct Call</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Check-in *</label>
                                    <input type="date" name="check_in_date" required min="{{ now()->toDateString() }}" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Check-out *</label>
                                    <input type="date" name="check_out_date" required min="{{ now()->addDay()->toDateString() }}" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700">Payout Amount (ETB)</label>
                                <input type="number" name="payout_amount" step="any" placeholder="e.g. 12000" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                            </div>
                            <button type="submit" class="w-full rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white py-2 text-xs font-bold transition">Record External Booking</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Existing Blocks & External Bookings --}}
            <div class="mt-8 border-t border-[#e7ddca] pt-5">
                <h4 class="font-bold text-sm text-[#1d3c34]">Active Blocks &amp; External Calendar Reservations</h4>
                @php
                    $blockedOrExternal = $bookings->filter(fn ($b) => str_starts_with($b->booking_reference, 'BLK-') || str_starts_with($b->booking_reference, 'EXT-'))->where('status', '!=', 'cancelled');
                @endphp
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e7ddca] text-xs">
                        <thead>
                            <tr class="bg-[#fbf9f5] text-left font-bold text-slate-600">
                                <th class="py-2.5 px-3">Reference</th>
                                <th class="py-2.5 px-3">Property</th>
                                <th class="py-2.5 px-3">Description / Guest</th>
                                <th class="py-2.5 px-3">Dates</th>
                                <th class="py-2.5 px-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e7ddca]">
                            @forelse ($blockedOrExternal as $block)
                                <tr>
                                    <td class="py-2.5 px-3 font-mono font-bold">{{ $block->booking_reference }}</td>
                                    <td class="py-2.5 px-3 font-semibold">{{ $block->property?->title }}</td>
                                    <td class="py-2.5 px-3">{{ $block->guest_name }}</td>
                                    <td class="py-2.5 px-3">{{ $block->check_in_date?->format('M d, Y') }} &rarr; {{ $block->check_out_date?->format('M d, Y') }}</td>
                                    <td class="py-2.5 px-3 text-right">
                                        <form action="{{ route('host.calendar.unblock', $block) }}" method="POST" class="inline" onsubmit="return confirm('Remove block and reopen these dates?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 px-2.5 py-1 font-bold">Unblock</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-slate-400">No active manual blocks or external bookings.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. PRICING RULES SECTION --}}
    <div id="pricing" data-section="pricing" class="section-panel hidden scroll-mt-6 space-y-6">
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Nightly Pricing &amp; Seasonal Rules</h3>
            <p class="text-xs text-slate-500 mt-1">Configure weekend premiums, seasonal peaks, cleaning fees, and minimum stay lengths per property.</p>

            <div class="mt-6 space-y-6">
                @forelse ($properties as $prop)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5">
                        <h4 class="font-bold text-sm text-[#1d3c34] mb-3">🏡 {{ $prop->title }}</h4>
                        <form action="{{ route('host.pricing.update', $prop) }}" method="POST" class="grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700">Base Nightly (ETB) *</label>
                                <input type="number" name="price" value="{{ $prop->price }}" required min="1" step="any" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700">Weekend Nightly</label>
                                <input type="number" name="weekend_price" value="{{ $prop->weekend_price }}" min="1" step="any" placeholder="Optional" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700">Seasonal Peak Rate</label>
                                <input type="number" name="seasonal_price" value="{{ $prop->seasonal_price }}" min="1" step="any" placeholder="Optional" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700">Min Stay (Nights) *</label>
                                <input type="number" name="minimum_stay" value="{{ $prop->minimum_stay ?? 1 }}" required min="1" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700">Cleaning Fee</label>
                                <input type="number" name="cleaning_fee" value="{{ $prop->cleaning_fee ?? 0 }}" min="0" step="any" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full rounded-xl bg-[#1d3c34] hover:bg-[#254d43] text-white py-2 text-xs font-bold transition">Update Pricing</button>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No accommodations found.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 5. HOUSE RULES & MANUAL SECTION --}}
    <div id="rules-manual" data-section="rules-manual" class="section-panel hidden scroll-mt-6 space-y-6">
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">House Rules, Manual &amp; Guest Arrival Guide</h3>
            <p class="text-xs text-slate-500 mt-1">Provide clear house rules, WiFi passwords, appliance instructions, lockbox codes, and check-in times.</p>

            <div class="mt-6 space-y-6">
                @forelse ($properties as $prop)
                    <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5">
                        <h4 class="font-bold text-sm text-[#1d3c34] mb-3">📖 {{ $prop->title }}</h4>
                        <form action="{{ route('host.rules.update', $prop) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Check-In Time</label>
                                    <input type="text" name="check_in_time" value="{{ $prop->check_in_time ?? '14:00' }}" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Check-Out Time</label>
                                    <input type="text" name="check_out_time" value="{{ $prop->check_out_time ?? '11:00' }}" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                                </div>
                                <div class="flex items-center gap-2 pt-6">
                                    <input type="checkbox" name="instant_booking_enabled" value="1" id="instant_{{ $prop->id }}" {{ $prop->instant_booking_enabled ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <label for="instant_{{ $prop->id }}" class="text-xs font-bold text-slate-700">Enable Instant Booking</label>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">House Rules (Quiet hours, smoking, pets)</label>
                                    <textarea name="house_rules" rows="3" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">{{ $prop->house_rules }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">House Manual (WiFi, appliances, water heater)</label>
                                    <textarea name="house_manual" rows="3" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">{{ $prop->house_manual }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Check-In Instructions (Lockbox, key pickup)</label>
                                    <textarea name="check_in_instructions" rows="3" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">{{ $prop->check_in_instructions }}</textarea>
                                </div>
                            </div>

                            <button type="submit" class="rounded-xl bg-[#1d3c34] hover:bg-[#254d43] text-white px-4 py-2 text-xs font-bold transition">Save Rules &amp; Guide</button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No accommodations found.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 6. CLEANING & HOUSEKEEPING DISPATCH SECTION --}}
    <div id="cleaning" data-section="cleaning" class="section-panel hidden scroll-mt-6 space-y-6">
        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Cleaning &amp; Housekeeping Operations</h3>
            <p class="text-xs text-slate-500 mt-1">Schedule pre-check-in cleaning, turnover sanitization, linen replenishment, and inspect room readiness.</p>

            {{-- Dispatch Cleaning Form --}}
            @if ($properties->isNotEmpty())
                <div class="mt-6 rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5">
                    <h4 class="font-bold text-sm text-[#1d3c34]">🧹 Schedule New Cleaning Task</h4>
                    <form action="{{ route('host.cleaning.store', $properties->first()) }}" method="POST" class="mt-4 grid gap-3 sm:grid-cols-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700">Accommodation *</label>
                            <select onchange="this.form.action = '/host/stays/' + this.value + '/cleaning'" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                                @foreach ($properties as $prop)
                                    <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700">Cleaning Type *</label>
                            <select name="service_type" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                                <option value="turnover_cleaning">Turnover Cleaning (Between Stays)</option>
                                <option value="room_cleaning">Standard Room Cleaning</option>
                                <option value="deep_cleaning">Deep Sanitization &amp; Wash</option>
                                <option value="extra_towels">Linen &amp; Towel Change</option>
                                <option value="maintenance">Minor Maintenance / Repair</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700">Priority *</label>
                            <select name="priority" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                                <option value="normal">Normal</option>
                                <option value="urgent">Urgent (Guest Arriving Today)</option>
                                <option value="low">Low Priority</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700">Room / Unit #</label>
                            <input type="text" name="room_number" placeholder="e.g. Unit 302" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-bold text-slate-700">Specific Instructions / Scope *</label>
                            <input type="text" name="description" required placeholder="e.g. Sanitize bathroom, mop hardwood floors, replenish coffee capsules" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full rounded-xl bg-[#1d3c34] hover:bg-[#254d43] text-white py-2 text-xs font-bold transition">Dispatch Task</button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Active Cleaning Queue --}}
            <div class="mt-6">
                <h4 class="font-bold text-sm text-[#1d3c34]">Housekeeping Task Queue</h4>
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e7ddca] text-xs">
                        <thead>
                            <tr class="bg-[#fbf9f5] text-left font-bold text-slate-600">
                                <th class="py-2.5 px-3">Accommodation</th>
                                <th class="py-2.5 px-3">Type</th>
                                <th class="py-2.5 px-3">Room</th>
                                <th class="py-2.5 px-3">Scope / Notes</th>
                                <th class="py-2.5 px-3">Priority</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e7ddca]">
                            @forelse ($cleaningTasks as $task)
                                <tr>
                                    <td class="py-3 px-3 font-semibold">{{ $task->property?->title }}</td>
                                    <td class="py-3 px-3">{{ $task->service_type_label }}</td>
                                    <td class="py-3 px-3 font-mono">{{ $task->room_number ?? 'Main Unit' }}</td>
                                    <td class="py-3 px-3 text-slate-600 max-w-xs">{{ $task->description }}</td>
                                    <td class="py-3 px-3">
                                        <span class="rounded px-2 py-0.5 font-bold {{ $task->priority === 'urgent' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700' }}">{{ ucfirst($task->priority) }}</span>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="rounded-full px-2 py-0.5 font-bold {{ $task->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        @if ($task->status !== 'completed')
                                            <form action="{{ route('host.cleaning.complete', $task) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white px-2.5 py-1 font-bold">Mark Inspected &amp; Ready</button>
                                            </form>
                                        @else
                                            <span class="text-slate-400">✓ Inspected</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-4 text-center text-slate-400">No cleaning tasks logged.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 7. REVIEWS & PAYOUTS SECTION --}}
    <div id="reviews-payouts" data-section="reviews-payouts" class="section-panel hidden scroll-mt-6 grid gap-6 md:grid-cols-2">
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
                            <div class="mt-2 rounded-lg bg-white p-2.5 border border-[#e7ddca]">
                                <span class="font-bold text-[#1d3c34]">Your reply:</span> {{ $rev->host_reply }}
                                <span class="block text-[10px] text-slate-400 mt-0.5">{{ $rev->host_replied_at?->format('M d, Y') }}</span>
                            </div>
                        @else
                            <form method="POST" action="{{ route('host.reviews.reply', $rev) }}" class="mt-3 flex gap-2">
                                @csrf
                                <input type="text" name="host_reply" required placeholder="Write a professional host reply..." class="flex-1 rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs">
                                <button type="submit" class="rounded-xl bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#254d43]">Reply</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-6 text-center">No guest reviews yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">Recent Payout Settlements</h3>
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
                    <p class="text-xs text-slate-400 py-6 text-center">No payout records found.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- REGISTER SHORT-STAY MODAL --}}
    <div x-show="openStayModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 p-4 sm:p-6 md:p-20 backdrop-blur-sm" @keydown.escape.window="openStayModal = false">
        <div class="relative mx-auto max-w-2xl rounded-3xl bg-white p-6 md:p-8 shadow-2xl border border-[#e7ddca]" @click.outside="openStayModal = false">
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                <div>
                    <h3 class="text-xl font-black text-[#1d3c34]">Register Short-Stay Accommodation</h3>
                    <p class="text-xs text-slate-500">List guest rooms or apartments for daily/nightly bookings</p>
                </div>
                <button type="button" @click="openStayModal = false" class="rounded-full bg-slate-100 p-2 text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form action="{{ route('host.stays.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700">Stay Name / Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Luxury Bole Studio with High-Speed WiFi" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Nightly Rate (ETB) *</label>
                        <input type="number" name="price" required min="1" step="any" placeholder="3500" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Weekend Nightly</label>
                        <input type="number" name="weekend_price" min="1" step="any" placeholder="4000" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Cleaning Fee</label>
                        <input type="number" name="cleaning_fee" value="0" min="0" step="any" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Min Stay (Nights)</label>
                        <input type="number" name="minimum_stay" value="1" min="1" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
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
                        <label class="block text-xs font-bold text-slate-700">Check-In</label>
                        <input type="text" name="check_in_time" value="14:00" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Check-Out</label>
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
                        <label class="block text-xs font-bold text-slate-700">Cover Photo</label>
                        <input type="file" name="image" accept="image/*" class="mt-1 w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#1d3c34] file:text-white" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">House Rules</label>
                    <textarea name="house_rules" rows="2" placeholder="e.g. No smoking indoors. Quiet hours after 22:00." class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Description</label>
                    <textarea name="description" rows="2" placeholder="Describe the space, amenities, proximity to airport, transport, etc." class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none"></textarea>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-[#e7ddca] pt-4">
                    <button type="button" @click="openStayModal = false" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="rounded-xl bg-[#1d3c34] hover:bg-[#254d43] px-6 py-2.5 text-xs font-bold text-white shadow-sm transition">Register &amp; Save Stay</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
