@extends('dashboards.layout')

@section('title', 'Guest & Traveler Dashboard')
@section('portal_name', 'Guest / Traveler Workspace')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>

    @if ($upcomingBooking && auth()->user()->hasAnyPermission(['bookings.view', 'stays.view']))
        <a href="#upcoming-stay" data-target-section="upcoming-stay" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🛎️</span>
                <span>Next Stay &amp; Pass</span>
            </span>
            <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-2 py-0.5 text-[10px] font-bold text-[#f8e7b9]">1</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['bookings.view', 'stays.view', 'stays.search']))
        <a href="#bookings" data-target-section="bookings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📅</span>
                <span>My Bookings</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $bookings->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['properties.save']))
        <a href="#saved-stays" data-target-section="saved-stays" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>❤️</span>
                <span>Saved Stays</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $savedStays->count() }}</span>
        </a>
    @endif

    @if (auth()->user()->hasAnyPermission(['reviews.view', 'reviews.create']))
        <a href="#reviews" data-target-section="reviews" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>⭐</span>
                <span>My Reviews</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $reviews->count() }}</span>
        </a>
    @endif

    <a href="#find-stays" data-target-section="find-stays" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
        <span class="flex items-center gap-2.5">
            <span>✨</span>
            <span>Explore Stays</span>
        </span>
        <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $availableStays->count() }}</span>
    </a>
@endsection

@section('content')
<div class="space-y-8">

    {{-- System Status Banners --}}
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs text-emerald-700 hover:text-emerald-900 font-bold">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-sm font-semibold text-rose-900 shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs text-rose-700 hover:text-rose-900 font-bold">&times;</button>
        </div>
    @endif

    {{-- 0. Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- 1. Active / Upcoming Stay & Access Pass --}}
    @if ($upcomingBooking)
        <div id="upcoming-stay" data-section="upcoming-stay" class="section-panel hidden scroll-mt-6 rounded-3xl border-2 border-[#d4af37] bg-[#fdfbf7] p-6 sm:p-8 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#e7ddca] pb-4">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="rounded-full bg-[#1d3c34] text-[#d4af37] px-3 py-1 text-xs font-black uppercase tracking-wider">Next Stay</span>
                    <span class="text-xs font-mono font-bold text-slate-700">{{ $upcomingBooking->booking_reference }}</span>
                    @if ($upcomingBooking->room_number)
                        <span class="rounded-md bg-slate-100 text-slate-700 px-2 py-0.5 text-xs font-mono font-semibold">Room/Unit: {{ $upcomingBooking->room_number }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ match ($upcomingBooking->status) { 'checked_in' => 'bg-emerald-100 text-emerald-800 border border-emerald-300', 'confirmed' => 'bg-blue-100 text-blue-800 border border-blue-300', default => 'bg-amber-100 text-amber-800 border border-amber-300' } }}">
                        {{ ucfirst(str_replace('_', ' ', $upcomingBooking->status)) }}
                    </span>
                    @if ($upcomingBooking->isPaid())
                        <span class="rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 px-2.5 py-0.5 text-[11px] font-bold">Paid</span>
                    @endif
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-4">
                    <div>
                        <h3 class="text-2xl font-black text-[#1d3c34]">{{ $upcomingBooking->property?->title ?? 'Reserved Residence' }}</h3>
                        <p class="text-xs text-slate-500 mt-1 font-medium">📍 {{ $upcomingBooking->property?->city ?? 'Addis Ababa' }}, {{ $upcomingBooking->property?->subcity ?? 'Bole' }}</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                        <div class="rounded-2xl border border-[#e7ddca] bg-white p-3.5 shadow-2xs">
                            <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">Check-In</span>
                            <p class="font-black text-sm text-[#1d3c34] mt-1">{{ $upcomingBooking->check_in_date?->format('M d, Y') }}</p>
                            <span class="text-[10px] text-slate-500">From 2:00 PM</span>
                        </div>
                        <div class="rounded-2xl border border-[#e7ddca] bg-white p-3.5 shadow-2xs">
                            <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">Check-Out</span>
                            <p class="font-black text-sm text-[#1d3c34] mt-1">{{ $upcomingBooking->check_out_date?->format('M d, Y') }}</p>
                            <span class="text-[10px] text-slate-500">By 11:00 AM</span>
                        </div>
                        <div class="rounded-2xl border border-[#e7ddca] bg-white p-3.5 shadow-2xs">
                            <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">Total</span>
                            <p class="font-black text-sm text-emerald-800 mt-1">{{ number_format($upcomingBooking->total_amount, 2) }} ETB</p>
                            <span class="text-[10px] text-slate-500">{{ $upcomingBooking->total_nights }} night(s) &bull; {{ $upcomingBooking->guests_count }} guest(s)</span>
                        </div>
                    </div>

                    {{-- Interactive Check-In & Check-Out State Machine Actions --}}
                    <div class="rounded-2xl border border-[#d9cab3] bg-white p-4 flex flex-wrap items-center justify-between gap-3 shadow-2xs">
                        <div>
                            <span class="text-xs font-bold text-slate-700">Self Check-In / Out Transition</span>
                            <p class="text-[11px] text-slate-500 mt-0.5">Activate door PIN upon arrival or conclude your stay when leaving.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($upcomingBooking->status === 'confirmed')
                                <form method="POST" action="{{ route('guest.bookings.check-in', $upcomingBooking) }}">
                                    @csrf
                                    <button type="submit" class="rounded-xl bg-emerald-700 hover:bg-emerald-800 px-4 py-2 text-xs font-bold text-white shadow-xs cursor-pointer transition">
                                        🗝️ Check In Now
                                    </button>
                                </form>
                            @elseif ($upcomingBooking->status === 'checked_in')
                                <form method="POST" action="{{ route('guest.bookings.check-out', $upcomingBooking) }}" onsubmit="return confirm('Confirm check-out? Your access pass will be concluded.');">
                                    @csrf
                                    <button type="submit" class="rounded-xl bg-[#1d3c34] hover:bg-[#254d43] px-4 py-2 text-xs font-bold text-white shadow-xs cursor-pointer transition">
                                        🚪 Complete Check-Out
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('guest.bookings.receipt', $upcomingBooking) }}" target="_blank" class="rounded-xl border border-[#d9cab3] bg-[#fbf9f5] hover:bg-[#ede3d1] px-3 py-2 text-xs font-bold text-[#1d3c34] transition">
                                🧾 View Receipt
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Access Pass Card --}}
                <div class="rounded-2xl border-2 border-[#d9cab3] bg-white p-5 flex flex-col justify-between shadow-xs">
                    <div>
                        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-2">
                            <h4 class="font-black text-xs uppercase tracking-wider text-[#9b6c17]">Access Pass</h4>
                            <span class="font-mono text-[10px] text-slate-400">ENCRYPTED</span>
                        </div>
                        <div class="mt-4 space-y-3 text-xs">
                            <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-2">
                                <span class="text-slate-500">Door PIN Code:</span>
                                <span class="font-mono font-bold text-sm text-[#1d3c34] bg-[#f8f5ee] px-2.5 py-1 rounded-lg border border-[#e8dccb]">
                                    {{ $upcomingBooking->status === 'checked_in' || $upcomingBooking->isPaid() ? '8492#' : 'Locked until check-in' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-2">
                                <span class="text-slate-500">Wi-Fi Network:</span>
                                <span class="font-mono font-semibold text-[#1d3c34]">GTM_Stay_VIP</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-2">
                                <span class="text-slate-500">Wi-Fi Password:</span>
                                <span class="font-mono font-semibold text-[#1d3c34]">guestwelcome2026</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Host Contact:</span>
                                <span class="font-semibold text-[#1d3c34]">{{ $upcomingBooking->property?->host?->phone ?? '+251 911 234 567' }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($upcomingBooking->property)
                        <div class="mt-5 pt-3 border-t border-[#e7ddca] flex items-center justify-between">
                            <a href="{{ route('properties.show', $upcomingBooking->property->slug) }}" class="text-xs font-bold text-[#1d3c34] hover:underline">
                                View House Rules &amp; Map &rarr;
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- 2. Bookings Across All Statuses --}}
    <div id="bookings" data-section="bookings" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#e7ddca] pb-4">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34]">My Booking History (All Statuses)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Manage your short stay reservations, modify dates, cancel with refund calculation, or download travel receipts.</p>
            </div>
            <a href="#find-stays" data-target-section="find-stays" class="nav-section-link inline-flex items-center gap-1.5 rounded-xl bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition shadow-xs self-start sm:self-auto cursor-pointer">
                <span>➕ Find New Stay</span>
            </a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e7ddca] text-sm">
                <thead>
                    <tr class="bg-[#fbf9f5] text-left text-xs font-black uppercase text-[#587165]">
                        <th class="py-3 px-4">Booking Ref</th>
                        <th class="py-3 px-4">Property</th>
                        <th class="py-3 px-4">Stay Dates</th>
                        <th class="py-3 px-4">Nights/Guests</th>
                        <th class="py-3 px-4">Total Amount</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Workflow Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca]">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-[#fcfaf7] transition">
                            <td class="py-4 px-4 font-mono font-bold text-xs text-[#1d3c34]">
                                {{ $booking->booking_reference }}
                                @if ($booking->refund_amount > 0)
                                    <div class="text-[10px] text-amber-700 font-mono mt-0.5 font-bold">
                                        Refund: {{ number_format($booking->refund_amount, 2) }} ETB
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-bold text-slate-800 block">{{ $booking->property?->title ?? 'Stay Accommodation' }}</span>
                                <span class="text-xs text-slate-500">📍 {{ $booking->property?->city ?? 'Addis Ababa' }}</span>
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-600">
                                {{ $booking->check_in_date?->format('M d, Y') }} &rarr; {{ $booking->check_out_date?->format('M d, Y') }}
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-600 font-semibold">
                                {{ $booking->total_nights }} night(s) &bull; {{ $booking->guests_count }} guest(s)
                            </td>
                            <td class="py-4 px-4 font-mono font-bold text-emerald-800">
                                {{ number_format($booking->total_amount, 2) }} ETB
                                @if (! $booking->isPaid() && $booking->status === 'pending')
                                    <span class="block text-[10px] font-sans font-bold text-amber-600">Unpaid</span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ match ($booking->status) { 'completed' => 'bg-emerald-100 text-emerald-800 border border-emerald-300', 'checked_in' => 'bg-blue-100 text-blue-800 border border-blue-300', 'cancelled' => 'bg-rose-100 text-rose-800 border border-rose-300', default => 'bg-amber-100 text-amber-800 border border-amber-300' } }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    {{-- Travel Receipt --}}
                                    <a href="{{ route('guest.bookings.receipt', $booking) }}" target="_blank" class="rounded-lg border border-[#d9cab3] bg-white px-2.5 py-1 text-[11px] font-bold text-[#1d3c34] hover:bg-[#ede3d1] transition shadow-2xs">
                                        🧾 Receipt
                                    </a>

                                    {{-- Modify Dates Modal Trigger (pending or confirmed) --}}
                                    @if (in_array($booking->status, ['pending', 'confirmed']))
                                        <details class="relative inline-block text-left">
                                            <summary class="list-none rounded-lg border border-[#d9cab3] bg-white px-2.5 py-1 text-[11px] font-bold text-[#1d3c34] hover:bg-[#ede3d1] transition cursor-pointer shadow-2xs">
                                                ✏️ Modify
                                            </summary>
                                            <div class="absolute right-0 z-20 mt-2 w-80 rounded-2xl border border-[#d9cab3] bg-white p-4 shadow-xl">
                                                <h4 class="font-black text-xs text-[#1d3c34] border-b border-[#e7ddca] pb-2 mb-3">Modify Booking Dates</h4>
                                                <form method="POST" action="{{ route('guest.bookings.modify', $booking) }}" class="space-y-3 text-left">
                                                    @csrf
                                                    <div>
                                                        <label class="block text-[10px] font-bold uppercase text-slate-500">Check-In Date</label>
                                                        <input type="date" name="check_in_date" value="{{ $booking->check_in_date?->format('Y-m-d') }}" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-bold uppercase text-slate-500">Check-Out Date</label>
                                                        <input type="date" name="check_out_date" value="{{ $booking->check_out_date?->format('Y-m-d') }}" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-bold uppercase text-slate-500">Guests Count</label>
                                                        <input type="number" name="guests_count" value="{{ $booking->guests_count }}" min="1" max="10" class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                                                    </div>
                                                    <div class="pt-2 flex justify-end gap-2">
                                                        <button type="submit" class="rounded-lg bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#254d43] transition cursor-pointer">
                                                            Save Dates
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>

                                        {{-- Cancel Reservation Trigger --}}
                                        <details class="relative inline-block text-left">
                                            <summary class="list-none rounded-lg border border-rose-300 bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 hover:bg-rose-100 transition cursor-pointer shadow-2xs">
                                                ❌ Cancel
                                            </summary>
                                            <div class="absolute right-0 z-20 mt-2 w-80 rounded-2xl border border-rose-200 bg-white p-4 shadow-xl text-left">
                                                <h4 class="font-black text-xs text-rose-800 border-b border-rose-100 pb-2 mb-2">Cancel Reservation</h4>
                                                <div class="rounded-xl bg-amber-50 border border-amber-200 p-2 text-[10px] text-amber-800 mb-3">
                                                    <strong>Ethiopian Cancellation Policy:</strong>
                                                    <p class="mt-0.5">&bull; &gt; 48 hrs prior: 100% refund of accommodation.</p>
                                                    <p>&bull; &lt; 48 hrs prior: 50% refund.</p>
                                                    <p>&bull; After check-in date: Non-refundable.</p>
                                                </div>
                                                <form method="POST" action="{{ route('guest.bookings.cancel', $booking) }}" onsubmit="return confirm('Are you sure you want to cancel this booking?');" class="space-y-3">
                                                    @csrf
                                                    <div>
                                                        <label class="block text-[10px] font-bold uppercase text-slate-500">Reason for Cancellation</label>
                                                        <textarea name="cancellation_reason" rows="2" placeholder="e.g. Flight rescheduled, personal emergency" class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800"></textarea>
                                                    </div>
                                                    <div class="flex justify-end gap-2">
                                                        <button type="submit" class="rounded-lg bg-rose-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-rose-800 transition cursor-pointer">
                                                            Confirm Cancellation
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>
                                    @endif

                                    {{-- Pay Now Button if Unpaid --}}
                                    @if ($booking->status === 'pending' && ! $booking->isPaid())
                                        <details class="relative inline-block text-left">
                                            <summary class="list-none rounded-lg bg-emerald-700 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-emerald-800 transition cursor-pointer shadow-2xs">
                                                💳 Pay Now
                                            </summary>
                                            <div class="absolute right-0 z-20 mt-2 w-80 rounded-2xl border border-[#d9cab3] bg-white p-4 shadow-xl text-left">
                                                <h4 class="font-black text-xs text-[#1d3c34] border-b border-[#e7ddca] pb-2 mb-3">Pay Reservation ({{ number_format($booking->total_amount, 2) }} ETB)</h4>
                                                <form method="POST" action="{{ route('bookings.pay', $booking) }}" enctype="multipart/form-data" class="space-y-3">
                                                    @csrf
                                                    <div>
                                                        <label class="block text-[10px] font-bold uppercase text-slate-500">Payment Gateway</label>
                                                        <select name="payment_method" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                                                            <option value="telebirr">Telebirr (Instant Clearance)</option>
                                                            <option value="cbe_birr">CBE Birr</option>
                                                            <option value="bank_transfer">Bank Wire / Deposit</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-bold uppercase text-slate-500">Transaction Reference</label>
                                                        <input type="text" name="bank_transaction_reference" placeholder="e.g. FT2610XXXX" class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-bold uppercase text-slate-500">Deposit Slip / Screenshot (optional)</label>
                                                        <input type="file" name="deposit_slip" accept="image/*,.pdf" class="mt-1 w-full text-xs text-slate-500">
                                                    </div>
                                                    <div class="flex justify-end gap-2 pt-2">
                                                        <button type="submit" class="rounded-lg bg-emerald-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-800 transition cursor-pointer">
                                                            Submit Payment
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-slate-400">
                                No bookings found. Explore available short stays to reserve your next accommodation.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. Saved Stays & Favorites --}}
    <div id="saved-stays" data-section="saved-stays" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 sm:p-8 shadow-sm">
        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34]">Saved Stays &amp; Favorites</h2>
                <p class="text-xs text-slate-500 mt-0.5">Short-stay vacation residences and luxury suites you have bookmarked.</p>
            </div>
            <span class="rounded-full bg-[#fbf8f3] border border-[#e8dccb] px-3 py-1 text-xs font-bold text-[#1d3c34]">
                {{ $savedStays->count() }} Saved
            </span>
        </div>

        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($savedStays as $favorite)
                @php $prop = $favorite->property; @endphp
                @if ($prop)
                    <div class="group relative rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] overflow-hidden hover:shadow-md transition">
                        <div class="h-44 w-full bg-slate-200 overflow-hidden relative">
                            @if ($prop->images->isNotEmpty())
                                <img src="{{ asset('storage/' . $prop->images->first()->image_path) }}" alt="{{ $prop->title }}" class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="h-full w-full flex items-center justify-center text-slate-400 text-3xl">🏡</div>
                            @endif
                            <div class="absolute top-3 right-3">
                                <form method="POST" action="{{ route('guest.saved-stays.destroy', $favorite) }}" onsubmit="return confirm('Remove from saved stays?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Remove Favorite" class="rounded-full bg-white/90 hover:bg-white p-2 text-rose-600 shadow-xs cursor-pointer transition">
                                        ❤️
                                    </button>
                                </form>
                            </div>
                            <div class="absolute bottom-3 left-3">
                                <span class="rounded-md bg-[#102b25]/80 text-[#d4af37] px-2 py-0.5 text-[10px] font-bold">
                                    {{ $prop->bedrooms ?? 1 }} Bed &bull; {{ $prop->bathrooms ?? 1 }} Bath
                                </span>
                            </div>
                        </div>

                        <div class="p-4">
                            <h3 class="font-black text-sm text-[#1d3c34] truncate">{{ $prop->title }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">📍 {{ $prop->city }}, {{ $prop->subcity }}</p>
                            <div class="mt-3 flex items-center justify-between border-t border-[#e7ddca] pt-3">
                                <span class="font-mono font-bold text-sm text-emerald-800">
                                    {{ number_format($prop->price, 2) }} ETB <span class="text-[10px] text-slate-500 font-sans">/night</span>
                                </span>
                                <a href="{{ route('properties.show', $prop->slug) }}" class="rounded-lg bg-[#1d3c34] hover:bg-[#254d43] px-3 py-1.5 text-xs font-bold text-white transition">
                                    View Stay &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="col-span-full py-8 text-center text-xs text-slate-400">
                    You have not saved any short-stay accommodations yet. Click the heart icon on any stay to bookmark it here.
                </div>
            @endforelse
        </div>
    </div>

    {{-- 4. My Reviews & Host Responses --}}
    <div id="reviews" data-section="reviews" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#e7ddca] pb-4">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34]">My Reviews &amp; Host Responses</h2>
                <p class="text-xs text-slate-500 mt-0.5">Share feedback on completed stays, update past reviews, and view host responses.</p>
            </div>
            @if ($completedStaysForReview->isNotEmpty())
                <details class="relative inline-block text-left">
                    <summary class="list-none inline-flex items-center gap-1.5 rounded-xl bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition shadow-xs cursor-pointer">
                        <span>⭐ Write New Review</span>
                    </summary>
                    <div class="absolute right-0 z-20 mt-2 w-96 rounded-2xl border border-[#d9cab3] bg-white p-5 shadow-2xl">
                        <h4 class="font-black text-xs text-[#1d3c34] border-b border-[#e7ddca] pb-2 mb-3">Write Review for Completed Stay</h4>
                        <form method="POST" action="{{ route('guest.reviews.store') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500">Select Stay</label>
                                <select name="booking_id" required onchange="document.getElementById('review_prop_id').value = this.options[this.selectedIndex].getAttribute('data-property-id')" class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                                    <option value="">-- Choose Completed Stay --</option>
                                    @foreach ($completedStaysForReview as $b)
                                        <option value="{{ $b->id }}" data-property-id="{{ $b->property_id }}">
                                            {{ $b->property?->title ?? 'Stay' }} ({{ $b->booking_reference }})
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="property_id" id="review_prop_id" value="{{ $completedStaysForReview->first()?->property_id }}">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500">Rating (1 to 5 Stars)</label>
                                <select name="rating" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                                    <option value="5">⭐⭐⭐⭐⭐ 5 - Exceptional Experience</option>
                                    <option value="4">⭐⭐⭐⭐ 4 - Very Good</option>
                                    <option value="3">⭐⭐⭐ 3 - Average</option>
                                    <option value="2">⭐⭐ 2 - Below Expectations</option>
                                    <option value="1">⭐ 1 - Poor</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500">Review Title</label>
                                <input type="text" name="review_title" placeholder="e.g. Wonderful stay in Bole!" class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500">Review Comments</label>
                                <textarea name="review_body" rows="3" required placeholder="Describe cleanliness, host communication, accuracy, and location..." class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2.5 py-1.5 text-xs text-slate-800"></textarea>
                            </div>
                            <div class="flex justify-end pt-2">
                                <button type="submit" class="rounded-lg bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition cursor-pointer">
                                    Publish Review
                                </button>
                            </div>
                        </form>
                    </div>
                </details>
            @endif
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @forelse ($reviews as $rev)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-black text-sm text-[#1d3c34]">{{ $rev->property?->title ?? 'Stay Experience' }}</span>
                            <span class="text-xs font-black text-[#d4af37]">
                                @for ($i = 0; $i < $rev->rating; $i++) ★ @endfor
                                <span class="text-slate-400">({{ $rev->rating }}/5)</span>
                            </span>
                        </div>
                        @if ($rev->review_title)
                            <h4 class="text-xs font-bold text-slate-800 mt-2">{{ $rev->review_title }}</h4>
                        @endif
                        <p class="text-xs text-slate-600 mt-1 font-medium italic">"{{ $rev->review_body }}"</p>

                        @if ($rev->host_reply)
                            <div class="mt-4 rounded-xl bg-white p-3 border border-[#e7ddca] text-xs">
                                <span class="font-bold text-[#1d3c34] flex items-center gap-1">
                                    <span>💬</span> Host Response:
                                </span>
                                <p class="text-slate-600 mt-1">{{ $rev->host_reply }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 pt-3 border-t border-[#e7ddca] flex items-center justify-between">
                        <span class="text-[10px] text-slate-400">{{ $rev->created_at?->format('M d, Y') }}</span>
                        <div class="flex items-center gap-2">
                            {{-- Edit Review Modal --}}
                            <details class="relative inline-block text-left">
                                <summary class="list-none text-xs font-bold text-[#1d3c34] hover:underline cursor-pointer">
                                    Edit
                                </summary>
                                <div class="absolute right-0 bottom-full z-20 mb-2 w-80 rounded-2xl border border-[#d9cab3] bg-white p-4 shadow-xl">
                                    <h4 class="font-black text-xs text-[#1d3c34] border-b border-[#e7ddca] pb-2 mb-3">Edit Review</h4>
                                    <form method="POST" action="{{ route('guest.reviews.update', $rev) }}" class="space-y-3">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase text-slate-500">Rating</label>
                                            <select name="rating" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2 py-1 text-xs">
                                                @for ($i = 5; $i >= 1; $i--)
                                                    <option value="{{ $i }}" @selected($rev->rating == $i)>{{ $i }} Stars</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase text-slate-500">Title</label>
                                            <input type="text" name="review_title" value="{{ $rev->review_title }}" class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2 py-1 text-xs">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase text-slate-500">Review Body</label>
                                            <textarea name="review_body" rows="3" required class="mt-1 w-full rounded-lg border border-[#d9cab3] px-2 py-1 text-xs">{{ $rev->review_body }}</textarea>
                                        </div>
                                        <div class="flex justify-end pt-1">
                                            <button type="submit" class="rounded-lg bg-[#1d3c34] px-3 py-1 text-xs font-bold text-white hover:bg-[#254d43]">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </details>

                            {{-- Delete Review --}}
                            <form method="POST" action="{{ route('guest.reviews.destroy', $rev) }}" onsubmit="return confirm('Delete this review permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-rose-600 hover:underline cursor-pointer">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-xs text-slate-400">
                    No reviews written yet. After completing your stay, share your feedback with the host!
                </div>
            @endforelse
        </div>
    </div>

    {{-- 5. Explore Available Stays --}}
    <div id="find-stays" data-section="find-stays" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#e7ddca] pb-4">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34]">Explore Short-Stay Accommodations</h2>
                <p class="text-xs text-slate-500 mt-0.5">Top-rated vacation rentals, luxury apartments, and serviced suites in Addis Ababa &amp; Ethiopia.</p>
            </div>
            <a href="{{ route('stays.index') }}" class="rounded-xl border border-[#d9cab3] bg-[#fbf9f5] hover:bg-[#ede3d1] px-4 py-2 text-xs font-bold text-[#1d3c34] transition">
                View All Stays &rarr;
            </a>
        </div>

        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($availableStays as $stay)
                <div class="group rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] overflow-hidden hover:shadow-md transition">
                    <div class="h-36 w-full bg-slate-200 overflow-hidden relative">
                        @if ($stay->images->isNotEmpty())
                            <img src="{{ asset('storage/' . $stay->images->first()->image_path) }}" alt="{{ $stay->title }}" class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="h-full w-full flex items-center justify-center text-slate-400 text-2xl">🏡</div>
                        @endif
                        <span class="absolute bottom-2 left-2 rounded-md bg-[#102b25]/85 text-[#d4af37] px-2 py-0.5 text-[9px] font-bold">
                            {{ $stay->bedrooms ?? 1 }} BR &bull; {{ $stay->bathrooms ?? 1 }} BA
                        </span>
                    </div>
                    <div class="p-3.5">
                        <h4 class="font-black text-xs text-[#1d3c34] truncate">{{ $stay->title }}</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">📍 {{ $stay->city }}, {{ $stay->subcity }}</p>
                        <div class="mt-3 flex items-center justify-between border-t border-[#e7ddca] pt-2">
                            <span class="font-mono font-bold text-xs text-emerald-800">
                                {{ number_format($stay->price) }} ETB
                            </span>
                            <a href="{{ route('properties.show', $stay->slug) }}" class="rounded-md bg-[#1d3c34] hover:bg-[#254d43] px-2.5 py-1 text-[10px] font-bold text-white transition">
                                Book &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-xs text-slate-400">
                    No active stays currently featured. Check back soon.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
