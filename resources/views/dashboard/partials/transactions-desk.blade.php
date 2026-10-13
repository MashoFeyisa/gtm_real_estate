<section id="transactions" data-section="transactions" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
    <div class="flex flex-col gap-4 border-b border-[#e7ddca] pb-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold text-[#1d3c34]">Transaction Engine</span>
            <h2 class="mt-2 text-2xl font-black text-[#1d3c34]">Transactions</h2>
            <p class="mt-1 text-sm text-slate-600">Purchase orders, rent schedules, commission events and payment confirmations across the marketplace.</p>
        </div>
        <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-black uppercase tracking-wider text-[#9b6c17]">
            {{ ($orders ?? collect())->count() + ($commissions ?? collect())->count() }} records
        </span>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-full bg-[#102b25] border-2 border-[#d4af37] px-4 py-2 text-xs font-black text-white hover:bg-black hover:text-[#d4af37] transition shadow-xs cursor-pointer" title="Print Ledger">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
            <span class="text-white font-black">Print Ledger / መዝገብ አትም</span>
        </button>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf8f3] p-4">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#587165]">Orders</p>
            <p class="mt-2 text-2xl font-black text-[#1d3c34]">{{ ($orders ?? collect())->count() }}</p>
        </div>
        <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf8f3] p-4">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#587165]">Rent Schedules</p>
            <p class="mt-2 text-2xl font-black text-[#1d3c34]">{{ ($rentSchedules ?? collect())->count() }}</p>
        </div>
        <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf8f3] p-4">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#587165]">Payments Awaiting Confirmation</p>
            <p class="mt-2 text-2xl font-black text-[#1d3c34]">{{ $pendingPaymentsCount ?? ($pendingPayments ?? collect())->count() }}</p>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                <tr>
                    <th class="p-3">Type</th>
                    <th class="p-3">Reference</th>
                    <th class="p-3">Parties</th>
                    <th class="p-3">Amount</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0e8d8]">
                @forelse (($orders ?? collect()) as $order)
                    <tr class="transition hover:bg-[#fbf8f3]">
                        <td class="p-3"><span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-[10px] font-bold text-[#9b6c17]">Order</span></td>
                        <td class="p-3 font-mono text-[11px] font-bold text-[#587165]">{{ $order->order_reference ?? 'Order #'.$order->id }}</td>
                        <td class="p-3 text-[#587165]">
                            {{ $order->user->name ?? $order->customer_name ?? '—' }}
                            <span class="block text-[10px] text-slate-400">{{ $order->property->title ?? 'Property removed' }}</span>
                        </td>
                        <td class="p-3 font-bold text-[#1d3c34]">{{ number_format((float) ($order->total_amount ?? $order->price ?? 0)) }} {{ strtoupper((string) ($order->currency ?? 'ETB')) }}</td>
                        <td class="p-3 font-bold text-[#1d3c34]">{{ ucwords(str_replace('_', ' ', (string) ($order->status ?? 'pending'))) }}</td>
                        <td class="p-3 text-[#587165]">{{ $order->created_at?->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-sm font-semibold text-[#587165]">No orders recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 overflow-x-auto rounded-2xl border border-[#e7ddca]">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                <tr>
                    <th class="p-3">Type</th>
                    <th class="p-3">Reference</th>
                    <th class="p-3">Parties</th>
                    <th class="p-3">Amount</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0e8d8]">
                @forelse (($commissions ?? collect()) as $commission)
                    <tr class="transition hover:bg-[#fbf8f3]">
                        <td class="p-3"><span class="rounded-full bg-[#edf2ee] px-2 py-0.5 text-[10px] font-bold text-[#1d3c34]">Commission</span></td>
                        <td class="p-3 font-mono text-[11px] font-bold text-[#587165]">COM-{{ $commission->id }}</td>
                        <td class="p-3 text-[#587165]">
                            {{ $commission->agent->name ?? 'Agent' }}
                            <span class="block text-[10px] text-slate-400">{{ $commission->property->title ?? 'Property removed' }}</span>
                        </td>
                        <td class="p-3 font-bold text-[#1d3c34]">{{ number_format((float) ($commission->commission_amount ?? 0)) }} ETB</td>
                        <td class="p-3 font-bold text-[#1d3c34]">{{ ucfirst((string) ($commission->status ?? 'pending')) }}</td>
                        <td class="p-3 text-[#587165]">{{ $commission->created_at?->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-sm font-semibold text-[#587165]">No commission events yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
