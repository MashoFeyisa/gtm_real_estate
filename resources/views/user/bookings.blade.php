@extends('dashboards.layout')

@section('title', 'My Bookings')
@section('portal_name', 'Reservations & Bookings')

@section('content')
<div class="space-y-6">
    <div class="rounded-3xl border border-[#d9cab3] bg-gradient-to-r from-[#1d3c34] to-[#2d5d4d] p-6 text-white shadow-sm md:p-8">
        <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-3 py-1 text-xs font-bold text-[#f8e7b9] uppercase tracking-wider">Stay &amp; Property Bookings</span>
        <h1 class="mt-2 text-2xl md:text-3xl font-black text-white">My Bookings</h1>
        <p class="mt-1 text-sm text-slate-200">Track and review all your property and short stay reservations.</p>
    </div>

    <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-8">
        @if ($bookings->isEmpty())
            <div class="text-center py-12">
                <span class="text-4xl">🧳</span>
                <h3 class="mt-4 text-base font-bold text-slate-800 dark:text-slate-100">No bookings found</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">You currently have no stay or property reservations.</p>
                <a href="{{ route('properties.index') }}" class="mt-4 inline-block rounded-xl bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white hover:bg-[#152e27] transition">Browse Properties</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-xs uppercase tracking-wider text-slate-500">
                            <th class="pb-3">Property</th>
                            <th class="pb-3">Dates</th>
                            <th class="pb-3">Guests</th>
                            <th class="pb-3">Total (ETB)</th>
                            <th class="pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($bookings as $booking)
                            <tr>
                                <td class="py-3 font-semibold text-slate-900 dark:text-white">
                                    {{ $booking->property->title ?? 'Reserved Property #'.$booking->property_id }}
                                </td>
                                <td class="py-3 text-slate-600 dark:text-slate-300">
                                    {{ $booking->check_in ? \Carbon\Carbon::parse($booking->check_in)->format('M d, Y') : '-' }} &rarr;
                                    {{ $booking->check_out ? \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') : '-' }}
                                </td>
                                <td class="py-3 text-slate-600 dark:text-slate-300">{{ $booking->guests_count ?? 1 }}</td>
                                <td class="py-3 font-bold text-[#1d3c34] dark:text-[#d4af37]">ETB {{ number_format((float) ($booking->total_price ?? 0), 2) }}</td>
                                <td class="py-3">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        {{ $booking->status ?? 'Confirmed' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
