<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commission Voucher — {{ $commission->voucher_number ?? 'Voucher' }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .print-card { border: none !important; shadow: none !important; }
        }
    </style>
</head>
<body class="bg-[#f5f0e7] p-4 sm:p-10 text-slate-800 antialiased">
    <div class="max-w-3xl mx-auto">
        {{-- Print Action Controls --}}
        <div class="no-print mb-6 flex items-center justify-between">
            <a href="{{ url()->previous() ?: route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-white border border-[#d9cab3] px-4 py-2 text-xs font-bold text-[#102b25] hover:bg-[#f0e4cf] transition">
                ← Back / ተመለስ
            </a>
            <div class="flex items-center gap-3">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-[#102b25] px-5 py-2.5 text-xs font-black text-[#d4af37] hover:bg-black transition shadow-md cursor-pointer">
                    <span>🖨️</span>
                    <span>Print Official Voucher / ቫውቸር አትም</span>
                </button>
            </div>
        </div>

        {{-- Official Voucher Card --}}
        <div class="print-card rounded-3xl border-2 border-[#102b25] bg-white p-8 sm:p-12 shadow-xl relative overflow-hidden">
            {{-- Watermark --}}
            <div class="absolute inset-0 flex items-center justify-center opacity-4 pointer-events-none select-none">
                <span class="text-8xl font-black uppercase text-[#102b25] rotate-[-25deg]">SETTLED</span>
            </div>

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b-2 border-[#102b25] pb-6 gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-[#102b25] text-[#d4af37] text-xs font-black px-2.5 py-1 uppercase tracking-wider">GTM Real Estate</span>
                        <span class="text-xs font-bold text-slate-500 uppercase">Ethiopian Certified Realty</span>
                    </div>
                    <h1 class="text-2xl font-black text-[#102b25] mt-2">AGENT COMMISSION DISBURSEMENT VOUCHER</h1>
                    <p class="text-xs font-bold text-[#9b6c17]">የወኪል ኮሚሽን ክፍያ ማረጋገጫ ቫውቸር</p>
                </div>

                <div class="text-right">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Voucher Number</p>
                    <p class="font-mono text-base font-black text-[#102b25]">{{ $commission->voucher_number ?? 'VCH-PENDING' }}</p>
                    <p class="text-[10px] text-slate-500 mt-1">
                        Date: <strong>{{ $commission->paid_at ? $commission->paid_at->format('M d, Y') : now()->format('M d, Y') }}</strong>
                    </p>
                </div>
            </div>

            {{-- Beneficiary & Deal Particulars --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 my-6 p-5 rounded-2xl bg-[#fcf9f5] border border-[#ebdcc9] text-xs">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Beneficiary / ተቀባይ ወኪል</span>
                    <div class="flex items-center gap-2 mt-1">
                        <h3 class="text-sm font-black text-[#102b25]">{{ $commission->agent?->name }}</h3>
                        @php
                            $tierKey = $commission->agent_tier_applied ?? $commission->agent?->tier ?? 'junior';
                            $tierInfo = (new \App\Services\CommissionSplitService())->getTierDetails($tierKey);
                        @endphp
                        <span class="rounded-full px-2 py-0.5 text-[9px] font-black uppercase border {{ $tierInfo['badge_class'] }}">
                            {{ $tierInfo['name'] }} ({{ (int) ($commission->agent?->effective_split_percentage ?? $tierInfo['default_split']) }}%)
                        </span>
                    </div>
                    <p class="text-slate-600 mt-1">Email: <strong>{{ $commission->agent?->email }}</strong></p>
                    <p class="text-slate-600">Phone: <strong>{{ $commission->agent?->phone }}</strong></p>
                    @if ($commission->agent?->license_number)
                        <p class="text-slate-600">
                            License: <strong class="font-mono">{{ $commission->agent->license_number }}</strong>
                            @if ($commission->agent->license_verified)
                                <span class="text-emerald-700 font-bold text-[10px]">✓ ERCA Verified</span>
                            @endif
                        </p>
                    @endif
                </div>

                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Property & Deal Details / የንብረት ዝርዝር</span>
                    <h3 class="text-sm font-black text-[#102b25] mt-1">{{ $commission->property?->title ?? 'Property Deal' }}</h3>
                    <p class="text-slate-600">Property Code: <strong>{{ $commission->property?->property_code ?? 'N/A' }}</strong></p>
                    <p class="text-slate-600">Deal Value: <strong>{{ number_format($commission->property_price, 2) }} ETB</strong></p>
                    <p class="text-slate-600">
                        Deal Split Type: 
                        <span class="font-bold text-[#102b25] uppercase">
                            {{ str_replace('_', ' ', $commission->split_type ?? 'Single Agent') }}
                        </span>
                    </p>
                </div>
            </div>

            {{-- Multi-Party Broker Split Summary (if Co-Brokered or Referral) --}}
            @if ($commission->isCoBrokered() || $commission->hasReferral() || $commission->company_retained_amount > 0)
                <div class="mb-6 rounded-2xl border border-[#d4af37]/40 bg-[#fffdfa] p-4 text-xs">
                    <div class="flex items-center justify-between border-b border-[#ebdcc9] pb-2 mb-3">
                        <span class="font-black text-[#102b25] uppercase text-[11px] flex items-center gap-1.5">
                            <span>🤝</span>
                            <span>Multi-Party Brokerage Split Hierarchy (የደላሎች ክፍፍል መዋቅር)</span>
                        </span>
                        <span class="text-[10px] font-bold text-slate-400">Total Pool: {{ number_format($commission->total_deal_commission ?: $commission->commission_amount, 2) }} ETB</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="rounded-xl bg-white border border-[#ebdcc9] p-2.5">
                            <span class="text-[9px] font-bold uppercase text-slate-400">Listing Agent</span>
                            <p class="font-bold text-[#102b25] truncate">{{ $commission->listingAgent?->name ?? $commission->agent?->name }}</p>
                            <p class="font-mono font-bold text-emerald-800 mt-1">{{ number_format($commission->listing_agent_amount ?: $commission->commission_amount, 2) }} ETB</p>
                        </div>

                        @if ($commission->isCoBrokered())
                            <div class="rounded-xl bg-white border border-[#ebdcc9] p-2.5">
                                <span class="text-[9px] font-bold uppercase text-slate-400">Selling / Co-Broker</span>
                                <p class="font-bold text-[#102b25] truncate">{{ $commission->sellingAgent?->name ?? 'Co-Broker' }}</p>
                                <p class="font-mono font-bold text-sky-800 mt-1">{{ number_format($commission->selling_agent_amount, 2) }} ETB</p>
                            </div>
                        @endif

                        @if ($commission->hasReferral())
                            <div class="rounded-xl bg-white border border-[#ebdcc9] p-2.5">
                                <span class="text-[9px] font-bold uppercase text-slate-400">Referral Bonus</span>
                                <p class="font-bold text-[#102b25] truncate">{{ $commission->referralAgent?->name ?? 'Referrer' }}</p>
                                <p class="font-mono font-bold text-amber-800 mt-1">{{ number_format($commission->referral_amount, 2) }} ETB</p>
                            </div>
                        @endif

                        <div class="rounded-xl bg-white border border-[#ebdcc9] p-2.5">
                            <span class="text-[9px] font-bold uppercase text-slate-400">Agency Retained</span>
                            <p class="font-bold text-[#102b25]">GTM Realty</p>
                            <p class="font-mono font-bold text-[#9b6c17] mt-1">{{ number_format($commission->company_retained_amount, 2) }} ETB</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Accounting Breakdown Table --}}
            <div class="overflow-x-auto rounded-2xl border border-[#ebdcc9] my-6">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#102b25] text-white uppercase text-[10px]">
                        <tr>
                            <th class="p-3">Item / Description</th>
                            <th class="p-3 text-right">Rate / Basis</th>
                            <th class="p-3 text-right">Debit / Credit (ETB)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#ebdcc9] bg-white">
                        <tr>
                            <td class="p-3 font-bold text-[#102b25]">
                                Gross Agent Commission Share
                                <span class="block text-[10px] text-slate-500 font-normal">
                                    {{ $tierInfo['name'] }} tier allocation from closed transaction
                                </span>
                            </td>
                            <td class="p-3 text-right font-mono">{{ $commission->commission_rate }}%</td>
                            <td class="p-3 text-right font-black text-[#102b25]">{{ number_format($commission->commission_amount, 2) }} ETB</td>
                        </tr>
                        <tr class="bg-rose-50/50">
                            <td class="p-3 font-bold text-rose-900">
                                Less: Ethiopian Withholding Tax (WHT)
                                <span class="block text-[10px] text-rose-700 font-normal">Ministry of Revenues (ERCA) statutory 2% deduction</span>
                            </td>
                            <td class="p-3 text-right font-mono text-rose-800">-{{ $commission->withholding_tax_rate ?? 2.0 }}%</td>
                            <td class="p-3 text-right font-black text-rose-700">-{{ number_format($commission->withholding_tax_amount, 2) }} ETB</td>
                        </tr>
                        <tr class="bg-[#fcf9f5] text-sm">
                            <td class="p-4 font-black text-[#102b25] uppercase tracking-wider">
                                Net Disbursed Amount / የተጣራ የተከፈለ
                            </td>
                            <td class="p-4 text-right font-bold text-slate-500">Net Payable</td>
                            <td class="p-4 text-right font-black text-xl text-emerald-800">
                                {{ number_format($commission->net_payout_amount ?: ($commission->commission_amount - $commission->withholding_tax_amount), 2) }} ETB
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Disbursement Method & Audit References --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 my-6 p-4 rounded-2xl bg-white border border-[#ebdcc9] text-xs">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Payment Channel</span>
                    <p class="font-black text-[#102b25] mt-0.5 capitalize">{{ str_replace('_', ' ', $commission->payout_method ?? 'Bank Transfer') }}</p>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Transaction / Check Ref</span>
                    <p class="font-mono font-bold text-[#102b25] mt-0.5">{{ $commission->payout_reference ?? 'DIRECT-SETTLED' }}</p>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Authorized By</span>
                    <p class="font-bold text-[#102b25] mt-0.5">{{ $commission->disbursedBy?->name ?? 'Finance Department' }}</p>
                </div>
            </div>

            @if ($commission->admin_note)
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 mb-8">
                    <strong>Audit Note:</strong> {{ $commission->admin_note }}
                </div>
            @endif

            {{-- Signatures & Stamp Section --}}
            <div class="grid grid-cols-2 gap-8 pt-8 border-t-2 border-dashed border-[#d9cab3] text-xs mt-8">
                <div>
                    <p class="font-bold text-[#102b25] uppercase text-[10px]">Prepared & Authorized By (Finance)</p>
                    <div class="mt-8 pt-2 border-t border-slate-400 w-48 text-[11px] text-slate-600">
                        Signature & Official Seal
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-bold text-[#102b25] uppercase text-[10px]">Beneficiary Agent Acknowledgment</p>
                    <div class="mt-8 pt-2 border-t border-slate-400 w-48 ml-auto text-[11px] text-slate-600">
                        Agent Signature & Date
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
