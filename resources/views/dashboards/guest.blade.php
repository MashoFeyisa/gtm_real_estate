@extends('dashboards.layout')

@section('title', 'Guest Dashboard')
@section('portal_name', 'Guest / Traveler Workspace')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if ($upcomingBooking && auth()->user()->hasAnyPermission(['bookings.view', 'stays.view']))
        <a href="#upcoming-stay" data-target-section="upcoming-stay" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🛎️</span>
                <span>Next Stay & Pass</span>
            </span>
            <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-2 py-0.5 text-[10px] font-bold text-[#f8e7b9]">1</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['bookings.view', 'stays.view', 'stays.search']))
        <a href="#bookings" data-target-section="bookings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📅</span>
                <span>Bookings</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $bookings->count() }}</span>
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
@endsection

@section('content')
<div class="space-y-8">
    {{-- Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- 1. Active / Upcoming Stay & Check-In Guide --}}
    @if ($upcomingBooking)
        <div id="upcoming-stay" data-section="upcoming-stay" class="section-panel hidden scroll-mt-6 rounded-3xl border-2 border-[#d4af37] bg-[#fdfbf7] p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-[#1d3c34] text-[#d4af37] px-3 py-1 text-xs font-black uppercase tracking-wider">Next Stay</span>
                    <span class="text-xs font-mono font-bold text-slate-600">{{ $upcomingBooking->booking_reference }}</span>
                </div>
                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ match ($upcomingBooking->status) { 'checked_in' => 'bg-emerald-100 text-emerald-800', 'confirmed' => 'bg-blue-100 text-blue-800', default => 'bg-amber-100 text-amber-800' } }}">
                    {{ ucfirst(str_replace('_', ' ', $upcomingBooking->status)) }}
                </span>
            </div>

            <div class="mt-4 grid gap-6 md:grid-cols-3">
                <div class="md:col-span-2">
                    <h3 class="text-xl font-black text-[#1d3c34]">{{ $upcomingBooking->property?->title ?? 'Reserved Residence' }}</h3>
                    <p class="text-xs text-slate-500 mt-1">📍 {{ $upcomingBooking->property?->city ?? 'Addis Ababa' }}, {{ $upcomingBooking->property?->subcity ?? 'Bole' }}</p>
                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                        <div class="rounded-xl border border-[#e7ddca] bg-white p-3">
                            <span class="text-slate-400 font-semibold">Check-In</span>
                            <p class="font-bold text-sm text-[#1d3c34] mt-0.5">{{ $upcomingBooking->check_in_date?->format('M d, Y') }}</p>
                            <span class="text-[10px] text-slate-500">From 2:00 PM</span>
                        </div>
                        <div class="rounded-xl border border-[#e7ddca] bg-white p-3">
                            <span class="text-slate-400 font-semibold">Check-Out</span>
                            <p class="font-bold text-sm text-[#1d3c34] mt-0.5">{{ $upcomingBooking->check_out_date?->format('M d, Y') }}</p>
                            <span class="text-[10px] text-slate-500">By 11:00 AM</span>
                        </div>
                        <div class="rounded-xl border border-[#e7ddca] bg-white p-3">
                            <span class="text-slate-400 font-semibold">Total Paid</span>
                            <p class="font-bold text-sm text-emerald-800 mt-0.5">{{ number_format($upcomingBooking->total_amount) }} ETB</p>
                            <span class="text-[10px] text-slate-500">{{ $upcomingBooking->total_nights ?? 1 }} nights</span>
                        </div>
                    </div>
                </div>

                {{-- Check-In Instructions Card --}}
                <div class="rounded-2xl border border-[#d9cab3] bg-white p-4 flex flex-col justify-between">
                    <div>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-[#9b6c17]">Check-In Access Pass</h4>
                        <div class="mt-3 space-y-2 text-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-1.5">
                                <span class="text-slate-500">Door Pin / Code:</span>
                                <span class="font-mono font-bold text-sm text-[#1d3c34] bg-slate-100 px-2 py-0.5 rounded">8492#</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-1.5">
                                <span class="text-slate-500">Wi-Fi Network:</span>
                                <span class="font-mono font-semibold text-[#1d3c34]">GTM_Stay_VIP</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Host Contact:</span>
                                <span class="font-semibold text-[#1d3c34]">+251 911 234 567</span>
                            </div>
                        </div>
                    </div>
                    @if ($upcomingBooking->property)
                        <div class="mt-4 pt-3 border-t border-[#e7ddca]">
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
    <div id="bookings" data-section="bookings" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">My Booking History (All Statuses)</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e7ddca] text-sm">
                <thead>
                    <tr class="bg-[#fbf9f5] text-left text-xs font-black uppercase text-[#587165]">
                        <th class="py-3 px-4">Booking Ref</th>
                        <th class="py-3 px-4">Property</th>
                        <th class="py-3 px-4">Dates</th>
                        <th class="py-3 px-4">Total (ETB)</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Receipt / Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ddca]">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-[#fcfaf7]">
                            <td class="py-3 px-4 font-mono font-bold text-xs text-[#1d3c34]">{{ $booking->booking_reference }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $booking->property?->title ?? 'Stay Accommodation' }}</td>
                            <td class="py-3 px-4 text-xs text-slate-600">
                                {{ $booking->check_in_date?->format('M d') }} – {{ $booking->check_out_date?->format('M d, Y') }}
                            </td>
                            <td class="py-3 px-4 font-bold text-emerald-800">{{ number_format($booking->total_amount) }} ETB</td>
                            <td class="py-3 px-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ match ($booking->status) { 'completed' => 'bg-emerald-100 text-emerald-800', 'checked_in' => 'bg-blue-100 text-blue-800', 'cancelled' => 'bg-rose-100 text-rose-800', default => 'bg-amber-100 text-amber-800' } }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if ($booking->property)
                                    <a href="{{ route('properties.show', $booking->property->slug) }}" class="text-xs font-bold text-[#1d3c34] hover:underline">View &rarr;</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-xs text-slate-400">No bookings yet. Explore Ethiopian short stays to book your first trip.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. Reviews Written --}}
    <div id="reviews" data-section="reviews" class="section-panel hidden scroll-mt-6 rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-[#1d3c34] border-b border-[#e7ddca] pb-3">My Reviews &amp; Host Responses</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @forelse ($reviews as $rev)
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-4">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-sm text-[#1d3c34]">{{ $rev->property?->title ?? 'Stay Experience' }}</span>
                        <span class="text-xs font-black text-[#d4af37]">★ {{ $rev->rating }}/5</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-2 font-medium">"{{ $rev->comment ?? $rev->review_title }}"</p>
                    @if ($rev->host_reply)
                        <div class="mt-3 rounded-xl bg-white p-2.5 border border-[#e7ddca] text-xs">
                            <span class="font-bold text-[#1d3c34]">Host Response:</span>
                            <p class="text-slate-600 mt-0.5">{{ $rev->host_reply }}</p>
                        </div>
                    @endif
                </div>
            @empty
                <p class="col-span-2 text-xs text-slate-400 py-4 text-center">No reviews written yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
