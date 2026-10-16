<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VAT Report — GTM Real Estate</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-[#f5f0e7] p-4 sm:p-10 text-slate-800 antialiased">
    <div class="max-w-4xl mx-auto">
        <div class="no-print mb-6 flex items-center justify-between">
            <a href="{{ route('finance.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white border border-[#d9cab3] px-4 py-2 text-xs font-bold text-[#102b25] hover:bg-[#f0e4cf] transition">
                ← Back to Finance Desk
            </a>
            <button type="button" onclick="window.print()" class="rounded-xl bg-[#102b25] px-5 py-2.5 text-xs font-black text-[#d4af37] hover:bg-black transition shadow-md cursor-pointer">
                🖨️ Print VAT Report
            </button>
        </div>

        <div class="rounded-3xl border border-[#d9cab3] bg-white p-8 sm:p-12 shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b-2 border-[#102b25] pb-6 gap-4">
                <div>
                    <span class="rounded bg-[#102b25] text-[#d4af37] text-xs font-black px-2.5 py-1 uppercase tracking-wider">GTM Real Estate</span>
                    <h1 class="text-2xl font-black text-[#102b25] mt-2">VAT PAYABLE REPORT</h1>
                    <p class="text-xs font-bold text-[#9b6c17]">የተጨማሪ እሴት ታክስ መግለጫ (SRS §170)</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold text-slate-500">Outstanding VAT payable</p>
                    <p class="text-2xl font-black text-[#102b25]">{{ number_format($vatBalance, 2) }} ETB</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-[#ebdcc9] mt-8">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#102b25] text-white uppercase text-[10px]">
                        <tr>
                            <th class="p-3">Period</th>
                            <th class="p-3 text-right">Collected (credit)</th>
                            <th class="p-3 text-right">Reversed (refunds)</th>
                            <th class="p-3 text-right">Remitted</th>
                            <th class="p-3 text-right">Net movement</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#ebdcc9] bg-white">
                        @forelse ($byPeriod as $row)
                            <tr class="hover:bg-[#fcf9f5]">
                                <td class="p-3 font-bold text-[#102b25]">{{ $row['period'] }}</td>
                                <td class="p-3 text-right font-black text-emerald-700">{{ number_format($row['collected'], 2) }}</td>
                                <td class="p-3 text-right font-black text-rose-700">{{ number_format($row['reversed'], 2) }}</td>
                                <td class="p-3 text-right font-black text-slate-600">{{ number_format($row['remitted'], 2) }}</td>
                                <td class="p-3 text-right font-black text-[#102b25]">{{ number_format($row['net'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-slate-400">No VAT movement recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <h2 class="text-sm font-black text-[#102b25] uppercase mt-10 mb-3">Remittances to the tax authority</h2>
            <div class="overflow-x-auto rounded-2xl border border-[#ebdcc9]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#102b25] text-white uppercase text-[10px]">
                        <tr>
                            <th class="p-3">Date</th>
                            <th class="p-3">Period covered</th>
                            <th class="p-3">Reference</th>
                            <th class="p-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#ebdcc9] bg-white">
                        @forelse ($remittances as $remittance)
                            <tr>
                                <td class="p-3 text-slate-500">{{ $remittance->created_at->format('M d, Y') }}</td>
                                <td class="p-3 text-slate-600">{{ $remittance->period_start->format('M d, Y') }} – {{ $remittance->period_end->format('M d, Y') }}</td>
                                <td class="p-3 font-mono text-slate-600">{{ $remittance->reference }}</td>
                                <td class="p-3 text-right font-black text-[#102b25]">{{ number_format($remittance->amount, 2) }} ETB</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-400">No VAT remittances recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
