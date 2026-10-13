@php
    $currentAgentId = auth()->user()?->agentProfile?->id;
    $commissions = $commissions ?? ($currentAgentId ? \App\Models\Commission::where(function ($q) use ($currentAgentId) {
        $q->where('agent_id', $currentAgentId)
            ->orWhere('listing_agent_id', $currentAgentId)
            ->orWhere('selling_agent_id', $currentAgentId)
            ->orWhere('referral_agent_id', $currentAgentId);
    })->with(['property', 'disbursedBy', 'order', 'lease', 'listingAgent', 'sellingAgent', 'referralAgent'])->latest()->get() : collect());

    $grossTotal = (float) $commissions->sum('commission_amount');
    $whtTotal = (float) $commissions->sum('withholding_tax_amount');
    $netTotal = (float) $commissions->where('status', 'paid')->sum('net_payout_amount');
    $pendingTotal = (float) $commissions->where('status', 'pending')->sum('commission_amount');
    $paidCount = $commissions->where('status', 'paid')->count();
    $pendingCount = $commissions->where('status', 'pending')->count();
@endphp

<section id="commissions" data-section="commissions" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#f0e4cf] pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Commission Desk</span>
                <span class="text-xs font-bold text-[#587165]">የኮሚሽን ክፍያ እና የቫውቸር ሰነዶች</span>
            </div>
            <h2 class="text-2xl font-black text-[#1d3c34] mt-1">My Earned Commissions &amp; Payouts</h2>
            <p class="text-xs text-slate-500">Track earnings from closed sales, lease brokering, co-brokered side splits, 2% Ethiopian statutory withholding tax (WHT) deductions, and official disbursement vouchers.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-1.5 text-xs font-bold text-[#1d3c34]">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span>ERCA 2% WHT Compliant</span>
            </span>
        </div>
    </div>

    {{-- Agent Compensation Tier Badge --}}
    @if (auth()->user()?->agentProfile)
        @php
            $authAgent = auth()->user()->agentProfile;
            $tierDetails = $authAgent->tier_details;
        @endphp
        <div class="mt-4 rounded-2xl border border-[#d9cab3] bg-gradient-to-r from-[#102b25] via-[#1d3c34] to-[#102b25] p-4 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#d4af37] text-[#102b25] text-lg font-black shadow-md">★</span>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-black text-sm text-[#d4af37] uppercase tracking-wider">{{ $tierDetails['name'] }}</span>
                        <span class="text-xs text-slate-300">({{ $tierDetails['amharic'] }})</span>
                    </div>
                    <p class="text-xs text-slate-200 mt-0.5">
                        Deal Split Compensation: <strong>{{ (int) $authAgent->effective_split_percentage }}% Agent Payout</strong> &bull; Agency Overhead: {{ 100 - (int) $authAgent->effective_split_percentage }}%
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if ($authAgent->license_number)
                    <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-mono text-[#d4af37] border border-white/20">
                        Lic: {{ $authAgent->license_number }}
                    </span>
                @endif
                @if ($authAgent->license_verified)
                    <span class="rounded-full bg-emerald-500/20 text-emerald-300 px-2.5 py-1 text-xs font-bold border border-emerald-400/30">
                        ✓ ERCA Verified
                    </span>
                @endif
            </div>
        </div>
    @endif

    {{-- Tax Compliance Notice --}}
    <div class="mt-4 rounded-xl border border-[#d4af37]/30 bg-[#fdfbf6] p-4 flex items-start gap-3">
        <span class="text-xl">📜</span>
        <div class="text-xs text-[#102b25]">
            <p class="font-bold">Ethiopian Ministry of Revenues Statutory Brokerage Tax Policy (የገቢዎች ሚኒስቴር ደንብ)</p>
            <p class="mt-0.5 text-slate-600">
                In compliance with Ethiopian Revenue &amp; Customs regulations, a statutory <strong>2% Withholding Tax (WHT)</strong> is automatically deducted from certified agent commissions upon admin disbursement. Every settled commission issues an authenticated audit voucher (<code class="font-mono text-[#1d3c34]">VCH-COMM-YYYY-XXXX</code>) for tax clearance and banking verification.
            </p>
        </div>
    </div>

    {{-- Metrics Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 my-6">
        <div class="rounded-2xl border border-[#d9cab3] bg-[#fffdfa] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Deals</span>
            <p class="mt-1 font-mono text-2xl font-black text-[#1d3c34]">{{ $commissions->count() }}</p>
            <span class="text-[11px] text-slate-500">{{ $paidCount }} disbursed &bull; {{ $pendingCount }} pending</span>
        </div>

        <div class="rounded-2xl border border-[#d9cab3] bg-[#fffdfa] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#587165]">Gross Earned</span>
            <p class="mt-1 font-mono text-xl font-black text-[#1d3c34]">{{ number_format($grossTotal, 2) }} <span class="text-xs font-semibold">ETB</span></p>
            <span class="text-[11px] text-slate-500">Before statutory deductions</span>
        </div>

        <div class="rounded-2xl border border-[#d9cab3] bg-[#fbf6ec] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#9b6c17]">2% Withholding Tax</span>
            <p class="mt-1 font-mono text-xl font-black text-[#9b6c17]">-{{ number_format($whtTotal, 2) }} <span class="text-xs font-semibold">ETB</span></p>
            <span class="text-[11px] text-[#9b6c17]/80 font-medium">Remitted to ERCA</span>
        </div>

        <div class="rounded-2xl border border-[#d9cab3] bg-[#edf3ee] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#2d5d4d]">Net Paid Out</span>
            <p class="mt-1 font-mono text-xl font-black text-[#0f5132]">{{ number_format($netTotal, 2) }} <span class="text-xs font-semibold">ETB</span></p>
            <span class="text-[11px] text-emerald-700 font-bold">Cleared to Bank / Telebirr</span>
        </div>
    </div>

    {{-- Commissions Table --}}
    <div class="mt-6">
        @if ($commissions->isNotEmpty())
            <div class="overflow-x-auto rounded-2xl border border-[#d9cab3]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#102b25] text-white">
                        <tr>
                            <th class="px-4 py-3 font-bold uppercase">Voucher / Ref</th>
                            <th class="px-4 py-3 font-bold uppercase">Property &amp; Deal</th>
                            <th class="px-4 py-3 font-bold uppercase">Deal Value</th>
                            <th class="px-4 py-3 font-bold uppercase">Split</th>
                            <th class="px-4 py-3 font-bold uppercase">Gross Comm.</th>
                            <th class="px-4 py-3 font-bold uppercase">2% WHT</th>
                            <th class="px-4 py-3 font-bold uppercase">Net Payout</th>
                            <th class="px-4 py-3 font-bold uppercase">Status</th>
                            <th class="px-4 py-3 font-bold uppercase text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e4cf] bg-white text-slate-700">
                        @foreach ($commissions as $comm)
                            <tr class="hover:bg-[#fffdfa] transition">
                                <td class="px-4 py-3 font-mono">
                                    @if ($comm->voucher_number)
                                        <span class="font-bold text-[#1d3c34]">{{ $comm->voucher_number }}</span>
                                    @else
                                        <span class="text-slate-400 italic">VCH-PENDING</span>
                                    @endif
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $comm->created_at->format('M d, Y') }}
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="font-bold text-[#1d3c34] max-w-[200px] truncate" title="{{ $comm->property?->title ?? 'N/A' }}">
                                        {{ $comm->property?->title ?? 'Direct Brokerage Deal' }}
                                    </div>
                                    <div class="text-[10px] text-slate-500">
                                        @if ($comm->lease)
                                            <span class="rounded bg-sky-100 px-1.5 py-0.2 text-sky-800 font-bold">Rental Lease #{{ $comm->lease->lease_number }}</span>
                                        @elseif ($comm->order)
                                            <span class="rounded bg-emerald-100 px-1.5 py-0.2 text-emerald-800 font-bold">Property Sale #{{ $comm->order->id }}</span>
                                        @else
                                            <span class="rounded bg-slate-100 px-1.5 py-0.2 text-slate-700 font-bold">Direct Transaction</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3 font-mono font-semibold text-slate-600">
                                    {{ number_format((float) $comm->property_price, 2) }} ETB
                                </td>

                                <td class="px-4 py-3 font-mono">
                                    <span class="font-bold text-slate-700">{{ $comm->commission_rate }}%</span>
                                    <div class="mt-0.5">
                                        @if ($comm->split_type === 'co_brokered')
                                            <span class="rounded bg-sky-100 text-sky-800 text-[9px] font-black uppercase px-1.5 py-0.2">Co-Broker</span>
                                        @elseif ($comm->split_type === 'referral')
                                            <span class="rounded bg-amber-100 text-amber-800 text-[9px] font-black uppercase px-1.5 py-0.2">Referral</span>
                                        @else
                                            <span class="rounded bg-slate-100 text-slate-700 text-[9px] font-black uppercase px-1.5 py-0.2">Direct</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3 font-mono font-bold text-[#1d3c34]">
                                    {{ number_format((float) $comm->commission_amount, 2) }} ETB
                                </td>

                                <td class="px-4 py-3 font-mono font-semibold text-[#9b6c17]">
                                    @if ($comm->withholding_tax_amount > 0)
                                        -{{ number_format((float) $comm->withholding_tax_amount, 2) }} ETB
                                        <span class="text-[10px] text-slate-400 block">({{ $comm->withholding_tax_rate }}%)</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 font-mono font-black text-[#0f5132]">
                                    @if ($comm->net_payout_amount)
                                        {{ number_format((float) $comm->net_payout_amount, 2) }} ETB
                                    @else
                                        {{ number_format((float) $comm->commission_amount, 2) }} ETB
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if ($comm->isPaid())
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Disbursed</span>
                                        </span>
                                        @if ($comm->payout_method)
                                            <div class="text-[10px] text-slate-500 mt-1 font-sans">
                                                Via {{ strtoupper(str_replace('_', ' ', $comm->payout_method)) }}
                                                @if ($comm->payout_reference)
                                                    <span class="block font-mono text-slate-400 truncate max-w-[120px]">{{ $comm->payout_reference }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    @elseif ($comm->isPending())
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 border border-amber-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            <span>Pending Audit</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">
                                            <span>{{ ucfirst($comm->status) }}</span>
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-right">
                                    @if ($comm->isPaid())
                                        <a href="{{ route('commissions.voucher', $comm->id) }}" target="_blank" class="inline-flex items-center gap-1 rounded-xl bg-[#102b25] px-3 py-1.5 text-xs font-bold text-[#f7f0e1] hover:bg-[#1d3c34] transition shadow-xs">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            <span>Voucher</span>
                                        </a>
                                    @else
                                        <span class="text-[11px] text-slate-400 italic">Pending Admin Approval</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($commissions instanceof \Illuminate\Pagination\LengthAwarePaginator && $commissions->hasPages())
                <div class="mt-6 border-t border-[#e7ddca] pt-4">
                    {{ $commissions->fragment('commissions')->links() }}
                </div>
            @endif
        @else
            <div class="rounded-2xl border border-dashed border-[#d9cab3] bg-[#fffdfa] p-10 text-center">
                <span class="text-3xl">💰</span>
                <h3 class="mt-2 text-base font-bold text-[#1d3c34]">No Commission Records Found</h3>
                <p class="mt-1 text-xs text-slate-500">Commissions are automatically accrued whenever your listed properties are sold or leased through client contracts.</p>
            </div>
        @endif
    </div>
</section>
