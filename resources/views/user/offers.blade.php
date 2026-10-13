@extends('dashboards.layout')

@section('title', 'My Offers')
@section('portal_name', 'Buyer Offers & Negotiations')

@section('content')
<div class="space-y-6">
    <div class="rounded-3xl border border-[#d9cab3] bg-gradient-to-r from-[#1d3c34] to-[#2d5d4d] p-6 text-white shadow-sm md:p-8">
        <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-3 py-1 text-xs font-bold text-[#f8e7b9] uppercase tracking-wider">Purchase Proposals</span>
        <h1 class="mt-2 text-2xl md:text-3xl font-black text-white">My Offers</h1>
        <p class="mt-1 text-sm text-slate-200">Track and monitor formal property purchase offers and ongoing negotiations.</p>
    </div>

    <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-8">
        @if ($offers->isEmpty())
            <div class="text-center py-12">
                <span class="text-4xl">🤝</span>
                <h3 class="mt-4 text-base font-bold text-slate-800 dark:text-slate-100">No offers submitted</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">You haven't submitted any offers on properties yet.</p>
                <a href="{{ route('properties.index') }}" class="mt-4 inline-block rounded-xl bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white hover:bg-[#152e27] transition">Explore Properties</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-xs uppercase tracking-wider text-slate-500">
                            <th class="pb-3">Property</th>
                            <th class="pb-3">Offered Amount</th>
                            <th class="pb-3">Terms / Financing</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3">Submitted</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($offers as $offer)
                            <tr>
                                <td class="py-3 font-semibold text-slate-900 dark:text-white">
                                    {{ $offer->property->title ?? 'Property #'.$offer->property_id }}
                                </td>
                                <td class="py-3 font-bold text-[#1d3c34] dark:text-[#d4af37]">ETB {{ number_format((float) ($offer->offer_amount ?? $offer->amount ?? 0), 2) }}</td>
                                <td class="py-3 text-slate-600 dark:text-slate-300">{{ $offer->financing_type ?? $offer->terms ?? 'Cash' }}</td>
                                <td class="py-3">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                        {{ $offer->status ?? 'Pending' }}
                                    </span>
                                </td>
                                <td class="py-3 text-xs text-slate-500">{{ $offer->created_at?->diffForHumans() ?? 'Recently' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
