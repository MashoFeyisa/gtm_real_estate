{{-- Ethiopian Real Estate Payment Desk & Financial Ledger --}}
@php
    $paymentsList = \App\Models\Payment::with(['property', 'user', 'verifier'])->latest()->get();
    $totalProcessed = $paymentsList->where('status', 'completed')->sum('amount');
    $pendingVerification = $paymentsList->where('status', 'pending')->sum('amount');
    $platformFeesTotal = $paymentsList->where('status', 'completed')->sum('platform_fee');
    $completedCount = $paymentsList->where('status', 'completed')->count();
    $pendingCount = $paymentsList->where('status', 'pending')->count();
    $ledgerEntries = \App\Models\FinancialLedger::with(['payment', 'user'])->latest()->limit(50)->get();
@endphp

<section id="payments" data-section="payments" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#f0e4cf] pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Financial Core</span>
                <span class="text-xs font-bold text-[#587165]">የክፍያ እና ፋይናንስ አስተዳደር</span>
            </div>
            <h2 class="text-2xl font-black text-[#1d3c34] mt-1">Ethiopian Payment Desk &amp; Financial Ledger</h2>
            <p class="text-xs text-slate-500">Audit Telebirr, CBE Birr, and Bank Deposit Slips for earnest money, stays, and reservations.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-full bg-[#102b25] border-2 border-[#d4af37] px-4 py-2 text-xs font-black text-white hover:bg-black hover:text-[#d4af37] transition shadow-xs cursor-pointer" title="Print Ledger">
                <svg class="h-4 w-4 text-[#d4af37]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span class="text-white font-black">Print Ledger / መዝገብ አትም</span>
            </button>
            <button type="button" onclick="openUniversalPaymentModal({title: 'Record New Transaction', purpose: 'Manual / Walk-In Ethiopian Payment Record'})" class="inline-flex items-center gap-1.5 rounded-full bg-[#102b25] px-4 py-2 text-xs font-bold text-[#d4af37] hover:bg-[#1d3c34] transition shadow-xs cursor-pointer">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Record Payment</span>
            </button>
        </div>
    </div>

    {{-- Financial Metrics Overview --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 my-6">
        <div class="rounded-2xl border border-[#d9cab3] bg-[#fffdfa] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Settled Volume</span>
            <p class="mt-1 font-mono text-xl font-black text-[#1d3c34]">ETB {{ number_format($totalProcessed, 2) }}</p>
            <span class="text-[11px] text-emerald-700 font-bold">{{ $completedCount }} completed txn(s)</span>
        </div>

        <div class="rounded-2xl border border-[#d9cab3] bg-[#fffdfa] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pending Slip Verification</span>
            <p class="mt-1 font-mono text-xl font-black text-amber-700">ETB {{ number_format($pendingVerification, 2) }}</p>
            <span class="text-[11px] text-amber-800 font-bold">{{ $pendingCount }} slip(s) awaiting audit</span>
        </div>

        <div class="rounded-2xl border border-[#d9cab3] bg-[#fffdfa] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Net Platform Revenue</span>
            <p class="mt-1 font-mono text-xl font-black text-[#102b25]">ETB {{ number_format($platformFeesTotal, 2) }}</p>
            <span class="text-[11px] text-slate-500">Service fees collected</span>
        </div>

        <div class="rounded-2xl border border-[#d9cab3] bg-[#fffdfa] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Ledger Health</span>
            <p class="mt-1 font-mono text-xl font-black text-slate-800">{{ $ledgerEntries->count() }} Entries</p>
            <span class="text-[11px] text-emerald-600 font-bold">Double-entry auditable</span>
        </div>
    </div>

    @if ($pendingCount > 0)
        <div class="mb-6 flex items-center justify-between gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-amber-900 shadow-xs">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-200 text-base">⚠️</span>
                <div>
                    <h4 class="text-sm font-black text-amber-950">{{ $pendingCount }} Payment Receipt(s) Awaiting Confirmation</h4>
                    <p class="text-xs text-amber-800">Pending bank transfer or mobile money slips must be audited and verified.</p>
                </div>
            </div>
            <span class="rounded-full bg-amber-200/80 px-3 py-1 font-mono text-xs font-black text-amber-950">
                ETB {{ number_format($pendingVerification, 2) }}
            </span>
        </div>
    @endif

    {{-- Tabs: Transactions Queue vs Financial Ledger --}}
    <div class="border-b border-[#f0e4cf] flex items-center gap-4 text-xs font-bold mb-4">
        <button type="button" onclick="switchPaymentDeskTab('queue')" id="tab-btn-queue" class="pb-2.5 border-b-2 border-[#102b25] text-[#102b25] cursor-pointer">
            Transactions &amp; Deposit Slips ({{ $paymentsList->count() }})
        </button>
        <button type="button" onclick="switchPaymentDeskTab('ledger')" id="tab-btn-ledger" class="pb-2.5 border-b-2 border-transparent text-slate-500 hover:text-slate-800 cursor-pointer">
            Financial Ledger
        </button>
    </div>

    {{-- Panel 1: Transactions Queue --}}
    <div id="desk-panel-queue">
        <div class="overflow-x-auto rounded-2xl border border-[#e7ddca]">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-[#102b25] text-[#f8f3eb]">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Txn Code</th>
                        <th class="px-4 py-3 font-semibold">Payer &amp; Channel</th>
                        <th class="px-4 py-3 font-semibold">Category</th>
                        <th class="px-4 py-3 font-semibold">Amount (ETB)</th>
                        <th class="px-4 py-3 font-semibold">Reference / FT</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0e4cf] bg-white">
                    @forelse ($paymentsList as $payment)
                        <tr class="hover:bg-[#fffdfa] transition">
                            <td class="px-4 py-3 font-mono font-bold text-[#1d3c34]">
                                <a href="{{ route('payments.receipt', $payment->transaction_number) }}" class="underline decoration-dotted hover:text-[#254d43]">
                                    {{ $payment->transaction_number }}
                                </a>
                                <span class="block text-[10px] text-slate-400 font-normal">{{ $payment->created_at->format('M d, H:i') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-slate-900">{{ $payment->payer_name }}</p>
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-700">{{ $payment->method_label }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-semibold text-slate-800">{{ $payment->type_label }}</span>
                                @if ($payment->property)
                                    <span class="block text-[10px] text-slate-500 truncate max-w-[150px]">{{ $payment->property->title }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ number_format($payment->amount, 2) }} ETB
                            </td>
                            <td class="px-4 py-3 font-mono text-[11px] text-slate-600 whitespace-nowrap">
                                {{ $payment->bank_transaction_reference ?? '—' }}
                                @if ($payment->deposit_slip_path)
                                    <a href="{{ route('payments.slip', $payment) }}" target="_blank" class="block text-[10px] text-[#1d3c34] font-bold underline">View Attached Slip</a>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $payment->status_badge[1] }}">
                                    {{ $payment->status_badge[0] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-1.5">
                                @if ($payment->status === 'pending')
                                    <form method="POST" action="{{ route('payments.verify', $payment) }}" class="inline-block">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="rounded-lg bg-emerald-700 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-emerald-800 transition cursor-pointer" title="Verify and approve transaction">
                                            ✓ Verify
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('payments.reject', $payment) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to mark this deposit slip as rejected?');">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="admin_notes" value="Rejected during verification audit.">
                                        <button type="submit" class="rounded-lg bg-rose-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-rose-700 transition cursor-pointer" title="Reject deposit slip">
                                            ✕ Reject
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('payments.receipt', $payment->transaction_number) }}" target="_blank" class="rounded-lg border border-[#d9cab3] px-2.5 py-1 text-[11px] font-bold text-slate-700 hover:bg-[#ede3d1] transition">
                                    Receipt
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                No payments recorded yet. Transactions made via Telebirr, CBE Birr, or Bank Transfer will appear here.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Panel 2: Financial Ledger --}}
    <div id="desk-panel-ledger" class="hidden">
        <div class="overflow-x-auto rounded-2xl border border-[#e7ddca]">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-[#102b25] text-[#f8f3eb]">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Entry ID</th>
                        <th class="px-4 py-3 font-semibold">Category</th>
                        <th class="px-4 py-3 font-semibold">Type</th>
                        <th class="px-4 py-3 font-semibold">Amount (ETB)</th>
                        <th class="px-4 py-3 font-semibold">Description</th>
                        <th class="px-4 py-3 font-semibold">Date &amp; Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0e4cf] bg-white font-mono">
                    @forelse ($ledgerEntries as $entry)
                        <tr class="hover:bg-[#fffdfa] transition text-[11px]">
                            <td class="px-4 py-2.5 font-bold text-[#1d3c34]">{{ $entry->ledger_code }}</td>
                            <td class="px-4 py-2.5 font-sans font-semibold text-slate-800">{{ $entry->category_label }}</td>
                            <td class="px-4 py-2.5">
                                <span class="rounded px-1.5 py-0.5 text-[10px] font-bold {{ $entry->entry_type === 'credit' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ strtoupper($entry->entry_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 font-bold {{ $entry->entry_type === 'credit' ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $entry->entry_type === 'credit' ? '+' : '-' }}{{ number_format($entry->amount, 2) }} ETB
                            </td>
                            <td class="px-4 py-2.5 font-sans text-slate-600">{{ $entry->description }}</td>
                            <td class="px-4 py-2.5 text-slate-400 font-sans">{{ $entry->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400 font-sans">
                                Financial ledger will populate automatically when transactions are cleared and certified.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
    function switchPaymentDeskTab(tab) {
        const queuePanel = document.getElementById('desk-panel-queue');
        const ledgerPanel = document.getElementById('desk-panel-ledger');
        const queueBtn = document.getElementById('tab-btn-queue');
        const ledgerBtn = document.getElementById('tab-btn-ledger');

        if (tab === 'queue') {
            queuePanel.classList.remove('hidden');
            ledgerPanel.classList.add('hidden');
            queueBtn.className = 'pb-2.5 border-b-2 border-[#102b25] text-[#102b25] cursor-pointer';
            ledgerBtn.className = 'pb-2.5 border-b-2 border-transparent text-slate-500 hover:text-slate-800 cursor-pointer';
        } else {
            queuePanel.classList.add('hidden');
            ledgerPanel.classList.remove('hidden');
            queueBtn.className = 'pb-2.5 border-b-2 border-transparent text-slate-500 hover:text-slate-800 cursor-pointer';
            ledgerBtn.className = 'pb-2.5 border-b-2 border-[#102b25] text-[#102b25] cursor-pointer';
        }
    }
</script>
