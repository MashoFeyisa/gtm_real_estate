        <section id="orders" data-section="orders" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[#1d3c34]">Buy &amp; Rent Orders</h2>
                    <p class="mt-1 text-xs text-slate-500">Orders, tenant leases, and sales agreements strictly for rent and sold properties.</p>
                </div>
                <span class="rounded-full bg-[#edf2ee] px-3.5 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">{{ $orders->count() }} Total</span>
            </div>

            @if (isset($agentRequestsToAdmin) && $agentRequestsToAdmin->isNotEmpty())
                <div class="mt-6 rounded-2xl border border-[#b7842d]/40 bg-[#fffdf7] p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#f9ecd0] text-[#9b6c17]">
                                🔔
                            </span>
                            <div>
                                <h3 class="font-black text-[#1d3c34]">Agent Agreement Requests Pending Admin Action</h3>
                                <p class="text-xs text-slate-600">These agreements were negotiated by agents with clients and submitted to Admin.</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-bold text-[#9b6c17]">{{ $agentRequestsToAdmin->count() }} submitted</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @foreach ($agentRequestsToAdmin as $adminOrder)
                            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-[#e7ddca] bg-white p-4">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-[#dfeee4] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Agent: {{ $adminOrder->agent?->name ?? 'Unassigned' }}</span>
                                        <span class="text-xs font-bold text-slate-400">→</span>
                                        <span class="font-bold text-[#1d3c34]">{{ $adminOrder->name }}</span>
                                        <span class="text-xs text-slate-500">({{ $adminOrder->email }}{{ $adminOrder->phone ? ' · '.$adminOrder->phone : '' }})</span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700">Property: {{ $adminOrder->property?->title ?? 'Removed' }} · {{ $adminOrder->isRental() ? 'Rent: ETB ' : 'Offer: ETB ' }}{{ number_format($adminOrder->offer_amount ?? 0, 2) }}</p>
                                    @if ($adminOrder->agent_note)
                                        <p class="text-xs italic text-slate-600"><span class="font-bold text-[#1d3c34]">Agent Agreement Note:</span> “{{ $adminOrder->agent_note }}”</p>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($adminOrder->hasAgreement())
                                        <a href="{{ route('orders.agreement', $adminOrder) }}" class="rounded-full border border-[#1d3c34] px-3 py-1.5 text-xs font-bold text-[#1d3c34] hover:bg-[#edf2ee]">
                                            Agreement PDF
                                        </a>
                                        <a href="{{ route('dashboard.orders.editAgreement', $adminOrder) }}" class="rounded-full border border-[#b7842d] bg-[#fdf8ef] px-3 py-1.5 text-xs font-bold text-[#9b6c17] transition hover:bg-[#f9ecd0]">
                                            Edit Content
                                        </a>
                                    @else
                                        <form method="POST" action="{{ route('dashboard.orders.generateAgreement', $adminOrder) }}">
                                            @csrf
                                            <button type="submit" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                                Generate Agreement
                                            </button>
                                        </form>
                                        <a href="{{ route('dashboard.orders.editAgreement', $adminOrder) }}" class="rounded-full border border-[#b7842d] bg-[#fdf8ef] px-3 py-1.5 text-xs font-bold text-[#9b6c17] transition hover:bg-[#f9ecd0]">
                                            Edit & Generate
                                        </a>
                                    @endif
                                    @if ($adminOrder->admin_status !== 'approved')
                                        <form method="POST" action="{{ route('dashboard.orders.review', $adminOrder) }}">
                                            @csrf
                                            <input type="hidden" name="admin_status" value="approved">
                                            <button type="submit" class="rounded-full bg-[#1d3c34] px-4 py-1.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                                Approve Request
                                            </button>
                                        </form>
                                    @else
                                        <span class="rounded-full bg-[#dfeee4] px-3 py-1.5 text-xs font-bold text-[#1d3c34]">Approved by Admin</span>
                                    @endif
                                    <form method="POST" action="{{ route('dashboard.orders.destroy', $adminOrder) }}" data-confirm="Are you sure you want to delete this request?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1.5 text-xs font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($orders->isNotEmpty())
                {{-- Orders Filters & Search Bar --}}
                <div class="mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#fbf8f3] p-4 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2" id="orders-filter-tabs">
                            <button type="button" onclick="filterOrderType('all')" data-order-filter="all" class="order-filter-btn rounded-full bg-[#1d3c34] px-3.5 py-1 text-xs font-bold text-white shadow-xs">
                                All ({{ $orders->count() }})
                            </button>
                            @php
                                $rentOrdersCount = $orders->filter(fn($o) => $o->isRental() || $o->property?->type === 'rent')->count();
                                $soldOrdersCount = $orders->filter(fn($o) => !$o->isRental() && ($o->property?->status === 'sold' || $o->property?->type === 'sale'))->count();
                            @endphp
                            <button type="button" onclick="filterOrderType('rent')" data-order-filter="rent" class="order-filter-btn rounded-full border border-[#d9cab3] bg-white px-3.5 py-1 text-xs font-bold text-[#1d3c34] hover:bg-[#edf2ee]">
                                Rent Orders ({{ $rentOrdersCount }})
                            </button>
                            <button type="button" onclick="filterOrderType('sale')" data-order-filter="sale" class="order-filter-btn rounded-full border border-[#d9cab3] bg-white px-3.5 py-1 text-xs font-bold text-[#1d3c34] hover:bg-[#edf2ee]">
                                Sold Property Orders ({{ $soldOrdersCount }})
                            </button>
                        </div>
                        <span id="dashboard-orders-count" class="shrink-0 rounded-full bg-[#1d3c34]/10 px-3 py-1 text-xs font-bold text-[#1d3c34]">{{ $orders->count() }} orders</span>
                    </div>

                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" id="dashboard-orders-search" placeholder="Search orders by client name, email, phone, property, agent, status, type..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2 pl-9 pr-8 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                        <button type="button" id="dashboard-orders-search-clear" class="hidden absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 transition" title="Clear search">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca] bg-white">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#2d5d4d] text-[#f8f3eb]">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Client</th>
                                <th class="px-5 py-4 font-semibold">Agent</th>
                                <th class="px-5 py-4 font-semibold">Property</th>
                                <th class="px-5 py-4 font-semibold">Type</th>
                                <th class="px-5 py-4 font-semibold">Status</th>
                                <th class="px-5 py-4 font-semibold">Agreement</th>
                                <th class="px-5 py-4 font-semibold">Requested</th>
                                <th class="px-5 py-4 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                @php
                                    $orderStatusBadge = match ($order->status) {
                                        'accepted' => ['Accepted', 'bg-[#dfeee4] text-[#1d3c34]'],
                                        'rejected' => ['Rejected', 'bg-[#f7e2e2] text-[#8f3b3b]'],
                                        default => ['Pending', 'bg-[#f9ecd0] text-[#9b6c17]'],
                                    };
                                @endphp
                                <tr class="dashboard-order-row border-t border-[#e7ddca] align-top" data-order-type="{{ ($order->isRental() || $order->property?->type === 'rent') ? 'rent' : 'sale' }}" data-search="{{ strtolower($order->name . ' ' . $order->email . ' ' . ($order->phone ?? '') . ' ' . ($order->property?->title ?? '') . ' ' . ($order->agent?->name ?? '') . ' ' . $order->status . ' ' . ($order->isRental() ? 'rent' : 'sale') . ' ' . ($order->offer_amount ?? '')) }}">
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-[#1d3c34]">{{ $order->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $order->email }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="font-medium text-slate-700">{{ $order->agent?->name ?? 'None (Direct Admin)' }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-slate-700">{{ $order->property?->title ?? 'Property removed' }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $order->isRental() ? 'Rent: ETB '.number_format($order->offer_amount ?? 0, 2).'/mo' : 'Offer: ETB '.number_format($order->offer_amount ?? 0, 2) }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $order->isRental() ? 'bg-[#dfeee4] text-[#1d3c34]' : 'bg-[#f2e4cb] text-[#1d3c34]' }}">
                                            {{ $order->isRental() ? 'For Rent' : 'For Sale' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $orderStatusBadge[1] }}">{{ $orderStatusBadge[0] }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($order->hasAgreement())
                                            <span class="inline-block rounded-full bg-[#dfeee4] px-2.5 py-1 text-xs font-bold text-[#1d3c34]">
                                                Agreement generated {{ $order->agreed_at?->format('M d, Y') }}
                                            </span>
                                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                                <a href="{{ route('orders.agreement', $order) }}" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#254d43]">
                                                    Download PDF
                                                </a>
                                                <form method="POST" action="{{ route('dashboard.orders.generateAgreement', $order) }}">
                                                    @csrf
                                                    <button type="submit" class="text-xs text-[#2d5d4d] underline hover:text-[#1d3c34]">
                                                        Regenerate
                                                    </button>
                                                </form>
                                                <a href="{{ route('dashboard.orders.editAgreement', $order) }}" class="text-xs text-[#9b6c17] underline hover:text-[#b7842d]">
                                                    Edit & Regenerate
                                                </a>
                                            </div>
                                        @else
                                            <span class="inline-block rounded-full bg-[#f3ecdb] px-2.5 py-1 text-xs font-bold text-slate-500">No agreement yet</span>
                                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                                <form method="POST" action="{{ route('dashboard.orders.generateAgreement', $order) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                                        Generate Agreement
                                                    </button>
                                                </form>
                                                <a href="{{ route('dashboard.orders.editAgreement', $order) }}" class="rounded-full border border-[#b7842d] bg-[#fdf8ef] px-3 py-1.5 text-xs font-bold text-[#9b6c17] transition hover:bg-[#f9ecd0]">
                                                    Edit & Generate
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $order->created_at?->format('M d, Y') }}</td>
                                    <td class="px-5 py-4">
                                        <form method="POST" action="{{ route('dashboard.orders.destroy', $order) }}" data-confirm="Are you sure you want to delete this order? This will also remove any generated agreement.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1.5 text-xs font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="dashboard-orders-no-results" class="hidden border-t border-[#e7ddca] bg-white">
                                <td colspan="8" class="px-5 py-6 text-center text-slate-500">No orders found matching your search.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if ($orders->hasPages())
                    <div class="mt-6 border-t border-[#e7ddca] pt-4">
                        {{ $orders->fragment('orders')->links() }}
                    </div>
                @endif
            @else
                <p class="mt-6 text-sm text-slate-500">No orders yet. Buy and rent requests from clients will appear here.</p>
            @endif
        </section>

