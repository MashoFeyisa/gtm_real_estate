@extends('dashboards.layout')

@section('title', 'My Viewings')
@section('portal_name', 'Property Viewings & Inspections')

@section('content')
<div class="space-y-6">
    <div class="rounded-3xl border border-[#d9cab3] bg-gradient-to-r from-[#1d3c34] to-[#2d5d4d] p-6 text-white shadow-sm md:p-8">
        <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-3 py-1 text-xs font-bold text-[#f8e7b9] uppercase tracking-wider">Scheduled Inspections</span>
        <h1 class="mt-2 text-2xl md:text-3xl font-black text-white">My Viewings</h1>
        <p class="mt-1 text-sm text-slate-200">View upcoming, confirmed, and past property viewing appointments.</p>
    </div>

    <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-8">
        @if ($viewings->isEmpty())
            <div class="text-center py-12">
                <span class="text-4xl">👁️</span>
                <h3 class="mt-4 text-base font-bold text-slate-800 dark:text-slate-100">No scheduled viewings</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">You haven't scheduled any in-person or virtual property viewings.</p>
                <a href="{{ route('properties.index') }}" class="mt-4 inline-block rounded-xl bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white hover:bg-[#152e27] transition">Search Properties</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-xs uppercase tracking-wider text-slate-500">
                            <th class="pb-3">Property</th>
                            <th class="pb-3">Scheduled Date</th>
                            <th class="pb-3">Time</th>
                            <th class="pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($viewings as $viewing)
                            <tr>
                                <td class="py-3 font-semibold text-slate-900 dark:text-white">
                                    {{ $viewing->property->title ?? 'Property #'.$viewing->property_id }}
                                </td>
                                <td class="py-3 text-slate-600 dark:text-slate-300">
                                    {{ $viewing->preferred_date ? \Carbon\Carbon::parse($viewing->preferred_date)->format('M d, Y') : ($viewing->viewing_date ? \Carbon\Carbon::parse($viewing->viewing_date)->format('M d, Y') : '-') }}
                                </td>
                                <td class="py-3 text-slate-600 dark:text-slate-300">{{ $viewing->preferred_time ?? $viewing->time_slot ?? 'Flexible' }}</td>
                                <td class="py-3">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                        {{ $viewing->status ?? 'Requested' }}
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
