<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Reconciliation — GTM Real Estate</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-[#f5f0e7] p-4 sm:p-10 text-slate-800 antialiased">
    <div class="max-w-5xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('finance.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white border border-[#d9cab3] px-4 py-2 text-xs font-bold text-[#102b25] hover:bg-[#f0e4cf] transition">← Back to Finance Desk</a>
        </div>

        <div class="rounded-3xl border border-[#d9cab3] bg-white p-8 shadow-lg">
            <h1 class="text-2xl font-black text-[#102b25]">Bank Reference Reconciliation</h1>
            <p class="text-xs font-bold text-[#9b6c17] mb-6">የባንክ ማጣቀሻ ማስታረቂያ (SRS §133)</p>

            <form method="POST" action="{{ route('finance.reconciliation.match') }}" class="mb-8 flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Bank reference to match</label>
                    <input type="text" name="reference" class="rounded-lg border border-[#d9cab3] px-3 py-2 text-sm" placeholder="Bank FT / deposit reference" required>
                </div>
                <button type="submit" class="rounded-lg bg-[#102b25] px-4 py-2 text-xs font-black text-[#d4af37]">Match &amp; settle</button>
            </form>

            <h2 class="text-sm font-black uppercase text-[#102b25] mb-2">Unmatched (pending) references</h2>
            <table class="w-full text-left text-xs mb-8">
                <thead class="bg-[#102b25] text-white uppercase text-[10px]">
                    <tr><th class="p-2">Txn #</th><th class="p-2">Reference</th><th class="p-2">Payer</th><th class="p-2 text-right">Amount</th></tr>
                </thead>
                <tbody class="divide-y divide-[#ebdcc9]">
                    @forelse($pending as $payment)
                        <tr><td class="p-2 font-mono">{{ $payment->transaction_number }}</td><td class="p-2 font-mono">{{ $payment->bank_transaction_reference }}</td><td class="p-2">{{ $payment->payer_name }}</td><td class="p-2 text-right">{{ number_format($payment->amount, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="p-4 text-center text-slate-400">No pending references.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <h2 class="text-sm font-black uppercase text-[#102b25] mb-2">Matched (settled) references</h2>
            <table class="w-full text-left text-xs">
                <thead class="bg-[#102b25] text-white uppercase text-[10px]">
                    <tr><th class="p-2">Txn #</th><th class="p-2">Reference</th><th class="p-2">Payer</th><th class="p-2 text-right">Amount</th></tr>
                </thead>
                <tbody class="divide-y divide-[#ebdcc9]">
                    @forelse($matched as $payment)
                        <tr><td class="p-2 font-mono">{{ $payment->transaction_number }}</td><td class="p-2 font-mono">{{ $payment->bank_transaction_reference }}</td><td class="p-2">{{ $payment->payer_name }}</td><td class="p-2 text-right">{{ number_format($payment->amount, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="p-4 text-center text-slate-400">No matched references.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
