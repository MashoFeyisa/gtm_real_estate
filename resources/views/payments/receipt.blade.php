<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Receipt #{{ $payment->transaction_number }} - {{ config('app.name', 'GTM Real Estate') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .receipt-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-[#f5f0e6] font-['Plus_Jakarta_Sans',sans-serif] text-slate-800 antialiased p-4 md:p-10">

    <div class="mx-auto max-w-2xl">
        {{-- Navigation & Print Toolbar --}}
        <div class="no-print mb-6 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ $payment->property ? route('properties.show', $payment->property->slug) : url('/') }}" class="inline-flex items-center gap-1.5 rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-bold text-[#1d3c34] hover:bg-[#ede3d1] transition shadow-xs">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Listing</span>
            </a>

            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition shadow-xs cursor-pointer">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print Receipt / PDF</span>
                </button>
            </div>
        </div>

        {{-- Official Ethiopian Payment Receipt Card --}}
        <div class="receipt-card relative rounded-3xl border-2 border-[#102b25]/20 bg-white p-6 sm:p-10 shadow-xl overflow-hidden">
            {{-- Top Ethiopian Flag Accent Strip --}}
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-[#009b44] via-[#fcd116] to-[#eb1c24]"></div>

            {{-- Receipt Header --}}
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 border-b border-[#f0e4cf] pb-6 mt-1">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Ethiopian Real Estate Digital System</span>
                        <span class="text-xs font-bold text-[#587165]">የክፍያ ማረጋገጫ ደረሰኝ</span>
                    </div>
                    <h1 class="mt-2 text-2xl font-black text-[#1d3c34] tracking-tight">Official Payment Receipt</h1>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">FIN-{{ $payment->transaction_number }}</p>
                </div>

                <div class="text-right sm:self-start">
                    <span class="inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs font-black {{ $payment->status_badge[1] }}">
                        <span class="h-2 w-2 rounded-full {{ $payment->status === 'completed' ? 'bg-emerald-600' : 'bg-amber-500' }}"></span>
                        {{ $payment->status_badge[0] }}
                    </span>
                    <p class="mt-1 text-[11px] text-slate-400">{{ $payment->created_at->format('M d, Y · H:i T') }}</p>
                </div>
            </div>

            {{-- Confirmation Notice & Admin Action --}}
            @if ($payment->status === 'pending')
                <div class="mt-5 rounded-2xl border-2 border-amber-300 bg-[#fffdf0] p-4 text-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-start gap-2.5">
                            <span class="text-xl">⏳</span>
                            <div>
                                <strong class="font-black text-[#1d3c34] text-sm block">Payment Receipt Submitted — Awaiting Confirmation</strong>
                                <p class="text-xs text-slate-600 mt-0.5">Your deposit slip or bank transaction code has been submitted to the platform. Platform administrators audit the reference and confirm your transaction.</p>
                            </div>
                        </div>

                        @if (auth()->check() && auth()->user()->isAdmin())
                            <div class="no-print shrink-0 flex items-center gap-2">
                                <form method="POST" action="{{ route('payments.verify', $payment) }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2 text-xs font-black text-white hover:bg-emerald-800 transition shadow-sm cursor-pointer">
                                        ✓ Confirm &amp; Approve Now
                                    </button>
                                </form>
                                <a href="{{ url('/dashboard#payments') }}" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
                                    Payments Desk
                                </a>
                            </div>
                        @else
                            <div class="no-print shrink-0">
                                <a href="{{ url('/dashboard#payments') }}" class="rounded-xl bg-[#102b25] px-3.5 py-2 text-xs font-black text-[#d4af37] hover:bg-[#1d3c34] transition">
                                    Admin Confirmation Desk &rarr;
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif ($payment->status === 'completed')
                <div class="mt-5 rounded-2xl border border-emerald-300 bg-[#f0f9f3] p-3.5 text-xs flex items-center gap-2.5">
                    <span class="text-lg text-emerald-600 font-black">✓</span>
                    <div>
                        <strong class="font-black text-emerald-900">Confirmed &amp; Verified Transaction</strong>
                        <span class="text-xs text-slate-600 ml-1">Official financial ledger entries have been recorded and stamped.</span>
                    </div>
                </div>
            @endif

            {{-- Payer & Transaction Quick Grid --}}
            <div class="grid gap-4 sm:grid-cols-2 border-b border-[#f0e4cf] py-6 text-xs">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Payer Information / የከፋይ መረጃ</span>
                    <p class="mt-1 text-sm font-black text-[#1d3c34]">{{ $payment->payer_name }}</p>
                    <p class="text-slate-600 font-mono">{{ $payment->payer_email }}</p>
                    @if ($payment->payer_phone)
                        <p class="text-slate-600 font-mono">{{ $payment->payer_phone }}</p>
                    @endif
                </div>

                <div class="sm:text-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Payment Channel / የክፍያ መንገድ</span>
                    <p class="mt-1 text-sm font-black text-[#1d3c34]">{{ $payment->method_label }}</p>
                    @if ($payment->bank_name)
                        <p class="text-slate-600">{{ $payment->bank_name }}</p>
                    @endif
                    @if ($payment->bank_transaction_reference)
                        <p class="text-slate-700 font-mono font-bold">Ref/FT: {{ $payment->bank_transaction_reference }}</p>
                    @endif
                </div>
            </div>

            {{-- Property Reference where applicable --}}
            @if ($payment->property)
                <div class="border-b border-[#f0e4cf] py-4 bg-[#fffdfa] -mx-6 sm:-mx-10 px-6 sm:px-10">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#587165]">Associated Property</span>
                    <div class="flex items-center justify-between gap-3 mt-1">
                        <div>
                            <p class="text-sm font-black text-[#1d3c34]">{{ $payment->property->title }}</p>
                            <p class="text-xs text-slate-500">{{ $payment->property->city }}{{ $payment->property->address ? ' · '.$payment->property->address : '' }}</p>
                        </div>
                        <span class="font-mono text-xs font-bold text-[#1d3c34] bg-white border border-[#e8dcc7] px-2.5 py-1 rounded-lg">
                            {{ $payment->property->property_code }}
                        </span>
                    </div>
                </div>
            @endif

            {{-- Breakdown Table --}}
            <div class="py-6">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-3 block">Financial Breakdown</span>
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 text-left">
                            <th class="pb-2 font-bold uppercase tracking-wider">Item Description</th>
                            <th class="pb-2 text-right font-bold uppercase tracking-wider">Amount (ETB)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="py-3 text-slate-700 font-semibold">
                                {{ $payment->type_label }}
                                @if ($payment->notes)
                                    <span class="block text-[11px] text-slate-400 font-normal">{{ $payment->notes }}</span>
                                @endif
                            </td>
                            <td class="py-3 text-right font-mono font-bold text-slate-900">
                                {{ number_format($payment->amount, 2) }} ETB
                            </td>
                        </tr>
                        @if ($payment->platform_fee > 0)
                            <tr class="text-slate-500">
                                <td class="py-2">Platform Service Fee (Inclusive)</td>
                                <td class="py-2 text-right font-mono">{{ number_format($payment->platform_fee, 2) }} ETB</td>
                            </tr>
                        @endif
                        @if ($payment->tax_amount > 0)
                            <tr class="text-slate-500">
                                <td class="py-2">15% VAT on Service Fee (Inclusive)</td>
                                <td class="py-2 text-right font-mono">{{ number_format($payment->tax_amount, 2) }} ETB</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-[#102b25] text-sm">
                            <th class="pt-4 text-left font-black text-[#1d3c34]">Total Paid / ጠቅላላ የተከፈለ</th>
                            <th class="pt-4 text-right font-black font-mono text-[#1d3c34] text-base">
                                {{ number_format($payment->amount, 2) }} ETB
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Deposit Slip Reference if available --}}
            @if ($payment->deposit_slip_path)
                <div class="border-t border-[#f0e4cf] pt-4 text-xs flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Attached Deposit Slip (የባንክ ደረሰኝ ሰነድ)</span>
                        <p class="font-mono text-slate-700 text-[11px]">FT Reference: {{ $payment->bank_transaction_reference ?? 'Verified' }}</p>
                    </div>
                    <a href="{{ route('payments.slip', $payment) }}" target="_blank" class="no-print inline-flex items-center gap-1 rounded-lg border border-[#d9cab3] bg-[#fbf7f0] px-3 py-1.5 text-xs font-bold text-[#1d3c34] hover:bg-[#efe5d3]">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>View Slip</span>
                    </a>
                </div>
            @endif

            {{-- Official Stamp & Verification Watermark --}}
            <div class="mt-8 border-t border-dashed border-[#d9cab3] pt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="h-14 w-14 rounded-2xl border-2 border-dashed border-[#102b25]/40 bg-[#f4efe4] p-1.5 flex items-center justify-center text-center">
                        <span class="text-[9px] font-black text-[#102b25] tracking-tighter uppercase leading-tight">OFFICIAL<br>VERIFIED<br>STAMP</span>
                    </div>
                    <div>
                        <p class="text-xs font-black text-[#102b25]">National Real Estate System Certificate</p>
                        <p class="text-[10px] text-slate-400">Digital verification stamp issued in Addis Ababa, Ethiopia.</p>
                        @if ($payment->verified_at)
                            <p class="text-[10px] font-mono text-emerald-700 font-bold">Verified on {{ $payment->verified_at->format('M d, Y H:i') }}</p>
                        @endif
                    </div>
                </div>

                <div class="text-right">
                    <span class="font-mono text-[10px] text-slate-400 block">QR AUDIT ID</span>
                    <span class="font-mono text-xs font-bold text-[#1d3c34] tracking-widest">{{ substr(md5($payment->transaction_number), 0, 16) }}</span>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
