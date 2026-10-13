{{-- Module 4: Formal Offer & Multi-Round Negotiation Desk --}}
<section id="offers-negotiations" data-section="offers-negotiations" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#f0e4cf] pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[11px] font-black uppercase tracking-wider text-[#d4af37]">Negotiations</span>
                <h2 class="text-2xl font-black text-[#1d3c34]">Formal Offer & Negotiation Desk</h2>
            </div>
            <p class="mt-1 text-xs text-slate-500">Manage buyer purchase proposals, earnest deposits, multi-round counteroffers, and deal acceptances.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <span class="rounded-full bg-[#f8f3eb] px-3 py-1 text-xs font-bold text-[#1d3c34] border border-[#ecd9be]">
                Total: {{ isset($offers) ? $offers->count() : 0 }}
            </span>
            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-800 border border-amber-200">
                Active: {{ isset($offers) ? $offers->whereIn('status', ['pending', 'countered', 'under_review'])->count() : 0 }}
            </span>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                Accepted: {{ isset($offers) ? $offers->where('status', 'accepted')->count() : 0 }}
            </span>
        </div>
    </div>

    @if (isset($offers) && $offers->isNotEmpty())
        <div class="mt-6 space-y-4">
            @foreach ($offers as $offer)
                @php
                    $cleanClientPhone = preg_replace('/[^0-9]/', '', $offer->buyer_phone);
                    if (str_starts_with($cleanClientPhone, '0')) {
                        $cleanClientPhone = '251' . substr($cleanClientPhone, 1);
                    }
                @endphp
                <div class="rounded-2xl border border-[#d9cab3] bg-white p-5 shadow-xs transition hover:border-[#102b25]/40">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between border-b border-[#f0e4cf] pb-4">
                        {{-- Property & Offer Meta --}}
                        <div class="flex items-start gap-3.5">
                            @if ($offer->property && $offer->property->image_url)
                                <img src="{{ $offer->property->image_url }}" alt="{{ $offer->property->title }}" class="h-16 w-16 shrink-0 rounded-2xl object-cover border border-[#d9cab3]">
                            @else
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-[#102b25] text-xl text-[#d4af37]">
                                    💼
                                </div>
                            @endif

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-xs font-black text-[#102b25] bg-[#102b25]/5 px-2 py-0.5 rounded border border-[#102b25]/10">
                                        {{ $offer->offer_reference }}
                                    </span>
                                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider
                                        @if($offer->status === 'accepted') bg-emerald-100 text-emerald-800
                                        @elseif($offer->status === 'countered') bg-amber-100 text-amber-800
                                        @elseif($offer->status === 'declined') bg-rose-100 text-rose-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $offer->status)) }}
                                    </span>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700">
                                        {{ $offer->formatted_financing_type }}
                                    </span>
                                </div>

                                <h4 class="mt-1 font-bold text-base text-[#1d3c34]">
                                    @if ($offer->property)
                                        <a href="{{ route('properties.show', $offer->property->slug) }}" target="_blank" class="hover:underline">
                                            {{ $offer->property->title }}
                                        </a>
                                    @else
                                        <span>Property Listing</span>
                                    @endif
                                </h4>

                                <div class="mt-1 text-xs text-slate-500">
                                    <span>Listed Asking Price: <strong>{{ $offer->formatted_original_price }}</strong></span>
                                    @if ($offer->agent)
                                        <span> · Assigned Agent: <strong class="text-[#1d3c34]">{{ $offer->agent->name }}</strong></span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Financial Proposal Figures --}}
                        <div class="flex flex-wrap items-center gap-4 bg-[#f8f3eb] p-3.5 rounded-2xl border border-[#ecd9be] text-xs">
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-500">Initial Offer</span>
                                <span class="font-black text-sm text-[#102b25]">{{ $offer->formatted_offered_price }}</span>
                                <span class="block text-[10px] font-bold {{ $offer->difference_percentage <= 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                                    {{ $offer->difference_percentage > 0 ? '+' : '' }}{{ $offer->difference_percentage }}% vs list
                                </span>
                            </div>

                            @if ($offer->current_counter_amount)
                                <div class="border-l border-[#d9cab3] pl-4">
                                    <span class="block text-[10px] uppercase font-bold text-[#b45309]">Latest Counter</span>
                                    <span class="font-black text-sm text-[#b45309]">{{ $offer->formatted_counter_amount }}</span>
                                    <span class="block text-[10px] text-slate-500 font-semibold">By: {{ ucfirst(str_replace('_', ' ', $offer->latest_counter_by ?? 'party')) }}</span>
                                </div>
                            @endif

                            @if ($offer->earnest_deposit)
                                <div class="border-l border-[#d9cab3] pl-4">
                                    <span class="block text-[10px] uppercase font-bold text-slate-500">Earnest Deposit</span>
                                    <span class="font-bold text-slate-800">{{ $offer->formatted_deposit }}</span>
                                </div>
                            @endif

                            @if ($offer->expires_at)
                                <div class="border-l border-[#d9cab3] pl-4">
                                    <span class="block text-[10px] uppercase font-bold text-slate-500">Validity</span>
                                    <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::parse($offer->expires_at)->format('M d, Y') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Client Details & Notes --}}
                    <div class="mt-3 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs text-slate-600">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-[#1d3c34]">{{ $offer->buyer_name }}</span>
                            <span>·</span>
                            <a href="tel:{{ $offer->buyer_phone }}" class="text-[#00b53f] hover:underline font-semibold">📞 {{ $offer->buyer_phone }}</a>
                            <span>·</span>
                            <a href="https://wa.me/{{ $cleanClientPhone }}" target="_blank" class="text-[#25D366] hover:underline font-semibold">💬 WhatsApp</a>
                            <span>·</span>
                            <span class="text-slate-400">{{ $offer->buyer_email }}</span>
                        </div>

                        @if ($offer->buyer_notes)
                            <p class="text-slate-600 italic">
                                Terms: “{{ $offer->buyer_notes }}”
                            </p>
                        @endif
                    </div>

                    {{-- Multi-Round Negotiation Ledger --}}
                    @if ($offer->negotiations && $offer->negotiations->isNotEmpty())
                        <div class="mt-4 rounded-xl bg-[#fffdfa] p-3.5 border border-[#e8ddcd]">
                            <h5 class="text-[11px] font-black uppercase tracking-wider text-[#102b25] mb-2 flex items-center gap-1.5">
                                <span>📜 Negotiation Event Ledger</span>
                                <span class="rounded bg-slate-100 px-1.5 py-0.2 text-[9px] font-bold text-slate-600">{{ $offer->negotiations->count() }} rounds</span>
                            </h5>
                            <div class="space-y-1.5">
                                @foreach ($offer->negotiations as $idx => $round)
                                    <div class="flex items-center justify-between rounded-lg bg-white p-2 text-xs border border-[#ecd9be]">
                                        <div class="flex items-center gap-2">
                                            <span class="rounded px-1.5 py-0.5 text-[9px] font-black uppercase
                                                @if($round->sender_type === 'buyer') bg-slate-100 text-slate-700
                                                @elseif($round->sender_type === 'admin') bg-[#102b25] text-[#d4af37]
                                                @else bg-amber-100 text-amber-800 @endif">
                                                Round {{ $idx + 1 }}: {{ ucfirst(str_replace('_', ' ', $round->sender_type)) }}
                                            </span>
                                            <span class="font-black text-[#102b25]">ETB {{ number_format($round->amount) }}</span>
                                            @if ($round->message)
                                                <span class="text-slate-500 italic text-[11px]">“{{ $round->message }}”</span>
                                            @endif
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $round->created_at->format('M d, H:i') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Actions Desk (Counter, Accept, Reject) --}}
                    @if (! in_array($offer->status, ['accepted', 'declined'], true))
                        <div class="mt-4 pt-4 border-t border-[#f0e4cf] flex flex-wrap items-center justify-between gap-3">
                            {{-- Counter Offer Form --}}
                            <form method="POST" action="{{ route('offers.counter', $offer) }}" class="flex flex-wrap items-center gap-2 flex-1 min-w-[280px]">
                                @csrf
                                <input type="number" step="0.01" min="1000" name="counter_amount" value="{{ $offer->current_counter_amount ?? $offer->offered_price }}" placeholder="Counter Amount (ETB)" required class="rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25] w-40">
                                <input type="text" name="message" placeholder="Counter terms or explanation..." class="rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25] flex-1 min-w-[180px]">
                                <button type="submit" class="rounded-xl bg-[#102b25] px-4 py-1.5 text-xs font-bold text-[#d4af37] hover:bg-[#1d3c34] transition">
                                    Send Counter
                                </button>
                            </form>

                            {{-- Final Acceptance / Rejection --}}
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('offers.accept', $offer) }}" onsubmit="return confirm('Are you sure you want to formally ACCEPT this offer of ETB {{ number_format($offer->current_counter_amount ?? $offer->offered_price) }}?');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 transition shadow-xs">
                                        ✓ Accept Deal
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('offers.reject', $offer) }}" onsubmit="return confirm('Decline this offer?');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="rounded-xl border border-rose-300 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-100 transition">
                                        ✕ Decline
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- Accepted Offer: Conveyance & Closing Desk --}}
                    @if ($offer->status === 'accepted')
                        <div class="mt-4 pt-4 border-t border-[#f0e4cf] bg-[#fbfdfa] rounded-2xl p-4 border border-emerald-200">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded bg-emerald-600 px-2 py-0.5 text-[9px] font-black uppercase text-white">Deal Stage: Closing</span>
                                        <span class="text-xs font-black text-emerald-950">
                                            {{ $offer->offer_type === 'rental' ? 'Lease Enrollment & Tenancy' : 'Sales Contract & Conveyance' }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Locked Price: <strong>ETB {{ number_format($offer->current_counter_amount ?? $offer->offered_price) }}</strong></p>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($offer->order_id)
                                        <a href="{{ route('orders.agreement', $offer->order_id) }}" target="_blank" class="rounded-xl bg-[#102b25] px-3.5 py-1.5 text-xs font-bold text-[#d4af37] hover:bg-[#1d3c34] transition flex items-center gap-1.5">
                                            <span>📄 Sales Contract (PDF)</span>
                                        </a>
                                    @elseif ($offer->lease_id)
                                        <a href="{{ route('leases.agreement', $offer->lease_id) }}" target="_blank" class="rounded-xl bg-[#102b25] px-3.5 py-1.5 text-xs font-bold text-[#d4af37] hover:bg-[#1d3c34] transition flex items-center gap-1.5">
                                            <span>📜 Tenancy Agreement (PDF)</span>
                                        </a>
                                    @else
                                        <form method="POST" action="{{ route('offers.convert-to-deal', $offer) }}" onsubmit="return confirm('Convert this accepted offer into an official contract and generate legal agreements?');">
                                            @csrf
                                            <button type="submit" class="rounded-xl bg-[#102b25] px-4 py-1.5 text-xs font-black text-[#d4af37] hover:bg-[#1d3c34] transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                                                <span>⚡ Proceed to Closing &amp; Generate Contract</span>
                                            </button>
                                        </form>
                                    @endif

                                    <button type="button" onclick="openUniversalPaymentModal({title: 'Record Earnest / Security Deposit', purpose: 'Deposit for {{ $offer->offer_reference }}', property_id: {{ $offer->property_id }}, amount: {{ $offer->deposit_proposed ?: round(($offer->current_counter_amount ?? $offer->offered_price) * 0.1, 2) }}, payer_name: '{{ addslashes($offer->buyer_name) }}', payer_email: '{{ addslashes($offer->buyer_email) }}', payer_phone: '{{ addslashes($offer->buyer_phone) }}'})" class="rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs font-bold text-[#1d3c34] hover:bg-[#fcf8f2] transition cursor-pointer">
                                        💳 Record Deposit
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2 border-t border-emerald-100 text-center text-[10px]">
                                <div class="rounded-lg bg-emerald-100/70 p-1.5 font-bold text-emerald-900">
                                    ✓ 1. Offer Accepted
                                </div>
                                <div class="rounded-lg {{ ($offer->order_id || $offer->lease_id) ? 'bg-emerald-100/70 text-emerald-900 font-bold' : 'bg-slate-100 text-slate-500' }} p-1.5">
                                    {{ ($offer->order_id || $offer->lease_id) ? '✓ 2. Agreement Generated' : '2. Contract Pending' }}
                                </div>
                                <div class="rounded-lg bg-slate-100 p-1.5 text-slate-600">
                                    3. Deposit &amp; Escrow
                                </div>
                                <div class="rounded-lg bg-slate-100 p-1.5 text-slate-600">
                                    4. Handover &amp; Notary
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fffdfa] p-10 text-center">
            <span class="text-3xl">💼</span>
            <h3 class="mt-2 text-base font-bold text-[#1d3c34]">No Formal Offers Yet</h3>
            <p class="mt-1 text-xs text-slate-500">Buyer proposals, financing requests, and multi-round negotiations will appear here.</p>
        </div>
    @endif
</section>
