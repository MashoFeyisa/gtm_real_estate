<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rent Receipt #{{ $schedule->id }} - {{ config('app.name', 'GTM Real Estate') }}</title>
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
        <div class="no-print mb-6 flex items-center justify-between">
            <a href="{{ route('tenant.dashboard') }}#rent-schedule" class="inline-flex items-center gap-1.5 rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-bold text-[#1d3c34] hover:bg-[#ede3d1] transition shadow-xs">
                &larr; Back to Tenant Dashboard
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition shadow-xs cursor-pointer">
                <span>Print Receipt / PDF</span>
            </button>
        </div>

        <div class="receipt-card relative rounded-3xl border-2 border-[#102b25]/20 bg-white p-6 sm:p-10 shadow-xl overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-[#009b44] via-[#fcd116] to-[#eb1c24]"></div>

            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 border-b border-[#f0e4cf] pb-6 mt-1">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Ethiopian Tenancy System</span>
                        <span class="text-xs font-bold text-[#587165]">የኪራይ ክፍያ ደረሰኝ</span>
                    </div>
                    <h1 class="mt-2 text-2xl font-black text-[#1d3c34] tracking-tight">Rent Installment Receipt</h1>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">REC-LSE-{{ $lease?->lease_code ?? $schedule->id }}-#{{ $schedule->installment_number }}</p>
                </div>

                <div class="text-right sm:self-start">
                    <span class="inline-flex items-center gap-1 rounded-full border border-emerald-300 bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-800">
                        <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                        Settled &amp; Paid
                    </span>
                    <p class="mt-1 text-[11px] text-slate-400">{{ $schedule->paid_at ? $schedule->paid_at->format('M d, Y') : now()->format('M d, Y') }}</p>
                </div>
            </div>

            <div class="my-6 rounded-2xl bg-[#fbf8f3] border border-[#e8dccb] p-6 text-center">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Amount Paid</span>
                <div class="mt-1 text-3xl sm:text-4xl font-black text-emerald-800 font-mono">
                    {{ number_format($schedule->amount_paid ?: $schedule->amount_due, 2) }} <span class="text-lg">ETB</span>
                </div>
                <p class="mt-1 text-xs text-slate-600">Period: <strong>{{ $schedule->period_label ?? ('Installment #' . $schedule->installment_number) }}</strong></p>
            </div>

            <div class="space-y-3 text-xs border-t border-[#f0e4cf] pt-4">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Lease Reference</span>
                    <span class="font-bold text-[#1d3c34]">{{ $lease?->lease_code ?? '—' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Rented Property</span>
                    <span class="font-bold text-[#1d3c34]">{{ $lease?->property?->title ?? 'Tenancy Property' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Tenant</span>
                    <span class="font-bold text-slate-800">{{ $user->name }} ({{ $user->email }})</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Payment Method</span>
                    <span class="font-bold text-slate-800">{{ ucfirst(str_replace('_', ' ', $schedule->payment_method ?? 'Telebirr')) }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Settlement Date</span>
                    <span class="font-bold text-slate-800">{{ $schedule->paid_at?->format('M d, Y H:i') ?? now()->format('M d, Y') }}</span>
                </div>
            </div>

            <div class="mt-8 pt-4 border-t border-slate-200 text-center text-[10px] text-slate-400">
                <p>Official Ethiopian Tenancy Voucher &bull; Verified via {{ config('app.name', 'GTM Real Estate') }} Digital Platform</p>
            </div>
        </div>
    </div>
</body>
</html>
