<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Folio #{{ $booking->booking_reference }} — {{ $siteBrand['name'] }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .no-print {
                display: none !important;
            }
            .print-shadow-none {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
        }
    </style>
</head>
<body class="bg-[#f5f0e7] min-h-screen py-8 px-4 text-slate-800 antialiased">
    <div class="max-w-3xl mx-auto">
        {{-- Top Action Toolbar --}}
        <div class="no-print mb-4 flex items-center justify-between">
            <a href="{{ route('receptionist.dashboard') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-[#d9cab3] bg-white px-4 py-2 text-xs font-bold text-[#1d3c34] hover:bg-[#f0e4cf] transition">
                &larr; Back to Front Desk
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-xl bg-[#1d3c34] px-5 py-2 text-xs font-bold text-[#d4af37] hover:bg-black transition shadow-xs">
                🖨️ Print Official Guest Folio
            </button>
        </div>

        {{-- Printable Folio Card --}}
        <div class="bg-white rounded-3xl border border-[#d9cab3] p-8 shadow-sm print-shadow-none">
            {{-- Header --}}
            <div class="flex items-start justify-between border-b border-[#ebdcc9] pb-6">
                <div>
                    <h1 class="text-2xl font-black text-[#102b25] tracking-tight">{{ $siteBrand['name'] }}</h1>
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Hospitality &amp; Short Stays Division</p>
                    <p class="text-xs text-slate-600 mt-1">Addis Ababa, Ethiopia &bull; Tel: +251 11 661 0000</p>
                    <p class="text-[11px] text-slate-400">TIN: 0098765432 &bull; VAT Reg: 88762514</p>
                </div>
                <div class="text-right">
                    <span class="rounded-lg bg-[#102b25] text-[#d4af37] px-3 py-1 font-mono font-black text-xs uppercase">
                        Official Guest Folio
                    </span>
                    <p class="mt-2 text-xs font-bold text-slate-400 uppercase">Folio / Stay Ref</p>
                    <p class="font-mono text-base font-black text-[#102b25]">{{ $booking->booking_reference }}</p>
                    <p class="text-[11px] text-slate-500">Date Issued: {{ \Carbon\Carbon::now()->format('M d, Y') }}</p>
                </div>
            </div>

            {{-- Guest & Room Information --}}
            <div class="mt-6 grid grid-cols-2 gap-6 bg-[#fcf9f5] rounded-2xl p-5 border border-[#ebdcc9]">
                <div>
                    <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Guest Information</p>
                    <p class="text-base font-black text-[#102b25] mt-0.5">{{ $booking->guest_name ?? $booking->user?->name ?? 'Guest' }}</p>
                    <p class="text-xs text-slate-600">ID/Passport: <strong>{{ $booking->guest_id_number ?: 'Not provided' }}</strong></p>
                    <p class="text-xs text-slate-600">Phone: <strong>{{ $booking->guest_phone ?? $booking->user?->phone ?? '—' }}</strong></p>
                    <p class="text-xs text-slate-600">Email: {{ $booking->guest_email ?? $booking->user?->email ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Accommodation Details</p>
                    <p class="text-base font-black text-[#102b25] mt-0.5">{{ $booking->property?->title ?? 'Hotel / Guest Accommodation' }}</p>
                    <p class="text-xs text-slate-600">Room Assigned: <strong class="text-[#102b25] font-mono text-sm bg-white px-2 py-0.5 rounded border border-[#d9cab3]">{{ $booking->room_number ?: 'Standard Room' }}</strong></p>
                    <p class="text-xs text-slate-600 mt-1">Arrival: <strong>{{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d, Y') }}</strong></p>
                    <p class="text-xs text-slate-600">Departure: <strong>{{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') }}</strong> ({{ $booking->total_nights }} nights)</p>
                </div>
            </div>

            {{-- Charges Breakdown Table --}}
            <div class="mt-6">
                <h3 class="text-xs font-black uppercase tracking-wider text-[#102b25] mb-2">Itemized Charges</h3>
                <table class="w-full text-left text-xs border border-[#ebdcc9] rounded-xl overflow-hidden">
                    <thead class="bg-[#ebdcc9]/40 text-[#102b25] uppercase text-[10px] font-bold">
                        <tr>
                            <th class="p-3">Description</th>
                            <th class="p-3 text-center">Nights / Qty</th>
                            <th class="p-3 text-right">Rate (ETB)</th>
                            <th class="p-3 text-right">Total (ETB)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#ebdcc9]/30">
                        <tr>
                            <td class="p-3 font-semibold text-slate-800">
                                Accommodation Charge (Room {{ $booking->room_number ?: 'Standard' }})
                            </td>
                            <td class="p-3 text-center font-bold">{{ $booking->total_nights }}</td>
                            <td class="p-3 text-right">{{ number_format($booking->nightly_price, 2) }}</td>
                            <td class="p-3 text-right font-bold text-slate-900">{{ number_format($booking->accommodation_total ?: ($booking->nightly_price * $booking->total_nights), 2) }}</td>
                        </tr>
                        @if ((float) $booking->cleaning_fee > 0)
                            <tr>
                                <td class="p-3 font-semibold text-slate-800">Room Sanitization &amp; Cleaning Fee</td>
                                <td class="p-3 text-center">1</td>
                                <td class="p-3 text-right">{{ number_format($booking->cleaning_fee, 2) }}</td>
                                <td class="p-3 text-right font-bold text-slate-900">{{ number_format($booking->cleaning_fee, 2) }}</td>
                            </tr>
                        @endif
                        @if ((float) $booking->service_fee > 0)
                            <tr>
                                <td class="p-3 font-semibold text-slate-800">Hospitality Concierge Fee</td>
                                <td class="p-3 text-center">1</td>
                                <td class="p-3 text-right">{{ number_format($booking->service_fee, 2) }}</td>
                                <td class="p-3 text-right font-bold text-slate-900">{{ number_format($booking->service_fee, 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Summary & Payments Section --}}
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-[#ebdcc9]">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-[#102b25] mb-2">Payments &amp; Settlement</h3>
                    @if ($booking->payments->isEmpty())
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-bold">
                            Payment Pending / Due at Front Desk
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach ($booking->payments as $pay)
                                <div class="rounded-xl border border-[#ebdcc9] bg-[#fcf9f5] p-2.5 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-slate-800 capitalize">{{ str_replace('_', ' ', $pay->payment_method) }}</span>
                                        <span class="font-mono font-black text-emerald-700">-{{ number_format($pay->amount, 2) }} ETB</span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        Ref: {{ $pay->transaction_number }} &bull; {{ \Carbon\Carbon::parse($pay->created_at)->format('M d, H:i') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="bg-[#fcf9f5] rounded-2xl p-4 border border-[#ebdcc9] space-y-2">
                    @php
                        $subtotal = (float) $booking->total_amount / 1.15;
                        $vat15 = (float) $booking->total_amount - $subtotal;
                        $totalPaid = (float) $booking->payments->where('status', 'confirmed')->sum('amount');
                        if ($booking->isPaid() && $totalPaid == 0) {
                            $totalPaid = (float) $booking->total_amount;
                        }
                        $balanceDue = max(0, (float) $booking->total_amount - $totalPaid);
                    @endphp

                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <span>Subtotal (Net Accommodation):</span>
                        <span>{{ number_format($subtotal, 2) }} ETB</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <span>VAT (15% ERCA Compliant):</span>
                        <span>{{ number_format($vat15, 2) }} ETB</span>
                    </div>
                    <div class="flex items-center justify-between text-sm font-black text-[#102b25] pt-2 border-t border-[#ebdcc9]">
                        <span>Grand Total:</span>
                        <span>{{ number_format($booking->total_amount, 2) }} ETB</span>
                    </div>
                    <div class="flex items-center justify-between text-xs font-bold text-emerald-700">
                        <span>Total Paid / Settled:</span>
                        <span>-{{ number_format($totalPaid, 2) }} ETB</span>
                    </div>
                    <div class="flex items-center justify-between text-sm font-black pt-2 border-t border-[#ebdcc9] {{ $balanceDue > 0 ? 'text-rose-600' : 'text-[#102b25]' }}">
                        <span>Balance Due:</span>
                        <span>{{ number_format($balanceDue, 2) }} ETB</span>
                    </div>
                </div>
            </div>

            {{-- Signature Lines --}}
            <div class="mt-12 pt-8 border-t border-[#ebdcc9] grid grid-cols-2 gap-8 text-center text-xs">
                <div>
                    <div class="border-b border-slate-400 pb-1 mb-2"></div>
                    <p class="font-bold text-slate-800">Front Desk Cashier / Receptionist</p>
                    <p class="text-[10px] text-slate-400">GTM Real Estate &bull; Verified Signature</p>
                </div>
                <div>
                    <div class="border-b border-slate-400 pb-1 mb-2"></div>
                    <p class="font-bold text-slate-800">Guest Signature</p>
                    <p class="text-[10px] text-slate-400">Acknowledgment of Charges &amp; Stay</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
