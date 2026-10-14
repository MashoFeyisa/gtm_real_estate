<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Travel Receipt #{{ $booking->booking_reference }} - {{ config('app.name', 'GTM Real Estate') }}</title>
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
            <a href="{{ route('guest.dashboard') }}#bookings" class="inline-flex items-center gap-1.5 rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-bold text-[#1d3c34] hover:bg-[#ede3d1] transition shadow-xs">
                &larr; Back to Guest Dashboard
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition shadow-xs cursor-pointer">
                <span>Print Travel Receipt / PDF</span>
            </button>
        </div>

        <div class="receipt-card relative rounded-3xl border-2 border-[#102b25]/20 bg-white p-6 sm:p-10 shadow-xl overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-[#009b44] via-[#fcd116] to-[#eb1c24]"></div>

            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 border-b border-[#f0e4cf] pb-6 mt-1">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Ethiopian Tourism &amp; Stays</span>
                        <span class="text-xs font-bold text-[#587165]">የጉዞ ክፍያ ደረሰኝ</span>
                    </div>
                    <h1 class="mt-2 text-2xl font-black text-[#1d3c34] tracking-tight">Travel &amp; Stay Receipt</h1>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $booking->booking_reference }}</p>
                </div>

                <div class="text-right sm:self-start">
                    <span class="inline-flex items-center gap-1 rounded-full border {{ $booking->isPaid() ? 'border-emerald-300 bg-emerald-100 text-emerald-800' : 'border-amber-300 bg-amber-100 text-amber-800' }} px-3 py-1 text-xs font-black">
                        <span class="h-2 w-2 rounded-full {{ $booking->isPaid() ? 'bg-emerald-600' : 'bg-amber-600' }}"></span>
                        {{ $booking->isPaid() ? 'Paid & Confirmed' : ucfirst(str_replace('_', ' ', $booking->status)) }}
                    </span>
                    <p class="mt-1 text-[11px] text-slate-400">Issued: {{ now()->format('M d, Y') }}</p>
                </div>
            </div>

            <div class="my-6 rounded-2xl bg-[#fbf8f3] border border-[#e8dccb] p-6 text-center">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Stay Amount</span>
                <div class="mt-1 text-3xl sm:text-4xl font-black text-emerald-800 font-mono">
                    {{ number_format($booking->total_amount, 2) }} <span class="text-lg">{{ $booking->currency ?? 'ETB' }}</span>
                </div>
                <p class="mt-1 text-xs text-slate-600">{{ $booking->total_nights }} Night(s) &bull; {{ $booking->guests_count }} Guest(s)</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-b border-[#f0e4cf] pb-6 text-xs">
                <div>
                    <h4 class="font-black text-slate-400 uppercase tracking-wider mb-2">Guest Information</h4>
                    <p class="font-bold text-sm text-[#1d3c34]">{{ $booking->guest_name }}</p>
                    <p class="text-slate-600 mt-0.5">{{ $booking->guest_email }}</p>
                    <p class="text-slate-600">{{ $booking->guest_phone }}</p>
                </div>

                <div>
                    <h4 class="font-black text-slate-400 uppercase tracking-wider mb-2">Property &amp; Dates</h4>
                    <p class="font-bold text-sm text-[#1d3c34]">{{ $booking->property?->title ?? 'Stay Accommodation' }}</p>
                    <p class="text-slate-600 mt-0.5">📍 {{ $booking->property?->city ?? 'Addis Ababa' }}, {{ $booking->property?->subcity ?? 'Ethiopia' }}</p>
                    <p class="text-slate-700 font-semibold mt-1">
                        {{ $booking->check_in_date?->format('M d, Y') }} &rarr; {{ $booking->check_out_date?->format('M d, Y') }}
                    </p>
                </div>
            </div>

            <div class="mt-6 border-b border-[#f0e4cf] pb-6 text-xs space-y-2">
                <h4 class="font-black text-slate-400 uppercase tracking-wider mb-3">Itemized Billing Breakdown</h4>
                <div class="flex justify-between text-slate-600">
                    <span>Accommodation ({{ $booking->total_nights }} nights &times; {{ number_format($booking->nightly_price, 2) }})</span>
                    <span class="font-mono font-bold">{{ number_format($booking->accommodation_total, 2) }} ETB</span>
                </div>
                @if ($booking->cleaning_fee > 0)
                    <div class="flex justify-between text-slate-600">
                        <span>Cleaning &amp; Turnover Fee</span>
                        <span class="font-mono font-bold">{{ number_format($booking->cleaning_fee, 2) }} ETB</span>
                    </div>
                @endif
                @if ($booking->service_fee > 0)
                    <div class="flex justify-between text-slate-600">
                        <span>Service &amp; Technology Fee</span>
                        <span class="font-mono font-bold">{{ number_format($booking->service_fee, 2) }} ETB</span>
                    </div>
                @endif
                @if ($booking->security_deposit > 0)
                    <div class="flex justify-between text-slate-600">
                        <span>Refundable Security Deposit</span>
                        <span class="font-mono font-bold">{{ number_format($booking->security_deposit, 2) }} ETB</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm font-black text-[#1d3c34] pt-2 border-t border-[#f0e4cf]">
                    <span>Total Amount</span>
                    <span class="font-mono text-emerald-800">{{ number_format($booking->total_amount, 2) }} ETB</span>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
                <div>
                    <p class="font-bold text-slate-700">GTM Real Estate &amp; Hospitality Platform</p>
                    <p>Addis Ababa, Ethiopia &bull; support@gtmrealestate.et</p>
                    <p class="mt-0.5">Booking verified via platform electronic ledger.</p>
                </div>

                <div class="border border-[#e8dccb] bg-[#fbf8f3] rounded-xl p-3 text-center">
                    <div class="font-mono text-[9px] text-slate-400">QR VERIFICATION</div>
                    <div class="h-14 w-14 mx-auto my-1 bg-white border border-slate-300 rounded flex items-center justify-center font-mono text-[9px] font-bold text-slate-700">
                        [QR STAMP]
                    </div>
                    <span class="font-mono text-[9px] text-[#1d3c34]">{{ substr(md5($booking->booking_reference), 0, 10) }}</span>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
