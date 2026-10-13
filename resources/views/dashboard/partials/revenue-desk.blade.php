<section id="revenue" data-section="revenue" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
    <div class="flex items-center justify-between border-b border-[#e7ddca] pb-5">
        <div>
            <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold text-[#1d3c34]">Financial Summary</span>
            <h2 class="mt-2 text-2xl font-black text-[#1d3c34]">Revenue</h2>
            <p class="mt-1 text-sm text-slate-600">Platform-derived commission indicators and payment confirmations. Detailed ledgers live in the Finance desk.</p>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-[#1d3c34] bg-[#1d3c34] p-5 text-white">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#dfeee4]">Paid Commissions</p>
            <p class="mt-2 text-2xl font-black text-white">{{ number_format((float) ($paidCommissionsTotal ?? 0)) }}</p>
            <p class="mt-1 text-[11px] text-[#dfeee4]/80">ETB collected</p>
        </div>
        <div class="rounded-2xl border border-[#d9cab3] bg-white p-5">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#587165]">Pending Commissions</p>
            <p class="mt-2 text-2xl font-black text-[#9b6c17]">{{ number_format((float) ($pendingCommissionsTotal ?? 0)) }}</p>
            <p class="mt-1 text-[11px] text-slate-500">ETB awaiting payout</p>
        </div>
        <div class="rounded-2xl border border-[#d9cab3] bg-white p-5">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#587165]">Payments Pending</p>
            <p class="mt-2 text-2xl font-black text-[#1d3c34]">{{ $pendingPaymentsCount ?? ($pendingPayments ?? collect())->count() }}</p>
            <p class="mt-1 text-[11px] text-slate-500">Awaiting finance confirmation</p>
        </div>
        <div class="rounded-2xl border border-[#d9cab3] bg-white p-5">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#587165]">Rent Collected</p>
            <p class="mt-2 text-2xl font-black text-emerald-700">{{ number_format((float) ($rentSchedules ?? collect())->sum('amount_paid')) }}</p>
            <p class="mt-1 text-[11px] text-slate-500">ETB across rent schedules</p>
        </div>
    </div>

    @if (! empty($adminCharts['monthly_trends']))
        @php
            $trends = collect($adminCharts['monthly_trends'])
                ->take(-6);
        @endphp
        <div class="mt-6 rounded-2xl border border-[#e7ddca] bg-[#fbf8f3] p-5">
            <p class="text-[10px] font-black uppercase tracking-wider text-[#587165]">Monthly Marketplace Trend (last {{ $trends->count() }} months)</p>
            <div class="mt-4 space-y-2">
                @foreach ($trends as $month => $trend)
                    @php
                        $trendTotal = (float) (is_array($trend) ? array_sum(array_filter($trend, 'is_numeric')) : (is_numeric($trend) ? $trend : 0));
                        $trendMax = max(1, (float) $trends->map(function ($value) {
                            return is_array($value) ? array_sum(array_filter($value, 'is_numeric')) : (is_numeric($value) ? (float) $value : 0);
                        })->max());
                    @endphp
                    <div class="flex items-center gap-3">
                        <span class="w-24 shrink-0 text-[11px] font-bold text-[#587165]">{{ $month }}</span>
                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-[#e7ddca]">
                            <div class="h-full rounded-full bg-[#1d3c34]" style="width: {{ ($trendTotal / $trendMax) * 100 }}%"></div>
                        </div>
                        <span class="w-16 shrink-0 text-right text-[11px] font-bold text-[#1d3c34]">{{ number_format($trendTotal) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
