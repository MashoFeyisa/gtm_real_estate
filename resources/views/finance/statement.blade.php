<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Audit Statement — GTM Real Estate</title>
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
            <a href="{{ url()->previous() ?: route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-white border border-[#d9cab3] px-4 py-2 text-xs font-bold text-[#102b25] hover:bg-[#f0e4cf] transition">
                ← Back / ተመለስ
            </a>
            <button type="button" onclick="window.print()" class="rounded-xl bg-[#102b25] px-5 py-2.5 text-xs font-black text-[#d4af37] hover:bg-black transition shadow-md cursor-pointer">
                🖨️ Print Statement / መግለጫ አትም
            </button>
        </div>

        <div class="rounded-3xl border border-[#d9cab3] bg-white p-8 sm:p-12 shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b-2 border-[#102b25] pb-6 gap-4">
                <div>
                    <span class="rounded bg-[#102b25] text-[#d4af37] text-xs font-black px-2.5 py-1 uppercase tracking-wider">GTM Real Estate</span>
                    <h1 class="text-2xl font-black text-[#102b25] mt-2">OFFICIAL FINANCIAL AUDIT STATEMENT</h1>
                    <p class="text-xs font-bold text-[#9b6c17]">የተጠቃለለ የፋይናንስ እና ኦዲት መግለጫ</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold text-slate-500">Period: <strong class="capitalize">{{ str_replace('_', ' ', $period) }}</strong></p>
                    <p class="text-xs text-slate-400">Generated: {{ now()->format('M d, Y H:i') }}</p>
                </div>
            </div>

            {{-- Summary Totals --}}
            <div class="grid grid-cols-3 gap-4 my-8 p-6 rounded-2xl bg-[#fcf9f5] border border-[#ebdcc9] text-center">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Gross Credits (Inflows)</p>
                    <p class="text-xl font-black text-emerald-700 mt-1">{{ number_format($totalCredit, 2) }} ETB</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Gross Debits (Disbursed)</p>
                    <p class="text-xl font-black text-rose-700 mt-1">{{ number_format($totalDebit, 2) }} ETB</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Net Balance Retained</p>
                    <p class="text-xl font-black text-[#102b25] mt-1">{{ number_format($netBalance, 2) }} ETB</p>
                </div>
            </div>

            {{-- Ledger Items --}}
            <div class="overflow-x-auto rounded-2xl border border-[#ebdcc9]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#102b25] text-white uppercase text-[10px]">
                        <tr>
                            <th class="p-3">Ledger Code</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Category</th>
                            <th class="p-3">Description</th>
                            <th class="p-3 text-right">Inflow (Credit)</th>
                            <th class="p-3 text-right">Outflow (Debit)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#ebdcc9] bg-white">
                        @forelse ($ledgers as $ledger)
                            <tr class="hover:bg-[#fcf9f5]">
                                <td class="p-3 font-mono font-bold text-slate-600">{{ $ledger->ledger_code }}</td>
                                <td class="p-3 text-slate-500 whitespace-nowrap">{{ $ledger->created_at->format('M d, Y') }}</td>
                                <td class="p-3 font-bold text-[#102b25] capitalize">{{ str_replace('_', ' ', $ledger->category) }}</td>
                                <td class="p-3 text-slate-600 max-w-xs">{{ $ledger->description }}</td>
                                <td class="p-3 text-right font-black text-emerald-700">
                                    {{ $ledger->entry_type === 'credit' ? number_format($ledger->amount, 2).' ETB' : '-' }}
                                </td>
                                <td class="p-3 text-right font-black text-rose-700">
                                    {{ $ledger->entry_type === 'debit' ? number_format($ledger->amount, 2).' ETB' : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-slate-400">No ledger transactions for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-300 flex justify-between text-xs text-slate-500">
                <p>Audited By: GTM Real Estate Finance Division</p>
                <p>Certified Official Record</p>
            </div>
        </div>
    </div>
</body>
</html>
