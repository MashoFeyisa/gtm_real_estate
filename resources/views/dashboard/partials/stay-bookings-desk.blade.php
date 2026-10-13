        <section id="stay-bookings" data-section="stay-bookings" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#d4af37]">Stay Platform</span>
                        <h2 class="text-2xl font-black text-[#1d3c34]">Stay Bookings</h2>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">Guest-house and short-stay reservations booked against properties across the platform.</p>
                </div>
                <a href="{{ route('stays.index') }}" target="_blank" class="rounded-full border border-[#d9cab3] bg-[#fffaf2] px-4 py-2 text-xs font-bold text-[#1d3c34] transition hover:bg-[#f0e4cf]">
                    View Public Stays &rarr;
                </a>
            </div>

            @if (isset($bookings) && $bookings->isNotEmpty())
                <div class="mt-6 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
                    <table class="min-w-full divide-y divide-[#e7ddca] text-left text-sm">
                        <thead class="bg-[#f9f4ed] text-xs font-bold uppercase tracking-wider text-[#60716c]">
                            <tr>
                                <th class="px-5 py-3">Reference</th>
                                <th class="px-5 py-3">Stay / Property</th>
                                <th class="px-5 py-3">Guest Details</th>
                                <th class="px-5 py-3">Dates &amp; Nights</th>
                                <th class="px-5 py-3">Total Amount</th>
                                <th class="px-5 py-3">Payment</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e7ddca] bg-white text-xs">
                            @foreach ($bookings as $booking)
                                @php
                                    $statusColors = [
                                        'confirmed' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'checked_in' => 'bg-blue-100 text-blue-800 border-blue-300',
                                        'completed' => 'bg-purple-100 text-purple-800 border-purple-300',
                                        'cancelled' => 'bg-red-100 text-red-800 border-red-300',
                                        'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
                                    ];
                                    [$paymentLabel, $paymentClasses] = $booking->payment_status_badge;
                                @endphp
                                <tr class="transition hover:bg-[#fefbf6]">
                                    <td class="px-5 py-4">
                                        <span class="font-mono font-bold text-[#1d3c34]">{{ $booking->booking_reference }}</span>
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-[#1d3c34]">
                                        {{ $booking->property?->title ?? 'Property #'.$booking->property_id }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-bold text-[#1d3c34]">{{ $booking->guest_name }}</p>
                                        <p class="text-slate-500">{{ $booking->guest_email }} · {{ $booking->guest_phone }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">
                                        {{ $booking->check_in_date->format('M d') }} &rarr; {{ $booking->check_out_date->format('M d, Y') }}
                                        <span class="block text-slate-400">({{ $booking->total_nights }} nights · {{ $booking->guests_count }} guests)</span>
                                    </td>
                                    <td class="px-5 py-4 font-bold text-[#1d3c34]">
                                        {{ number_format($booking->total_amount, 2) }} {{ $booking->currency }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $paymentClasses }}">
                                            {{ $paymentLabel }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $statusColors[$booking->status] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <form method="POST" action="{{ route('bookings.update-status', $booking) }}" class="flex items-center gap-1.5">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" onchange="this.form.submit()" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-xs font-semibold text-[#1d3c34]">
                                                <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                <option value="checked_in" {{ $booking->status === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                                                <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($bookings->hasPages())
                    <div class="mt-6 border-t border-[#e7ddca] pt-4">
                        {{ $bookings->fragment('stay-bookings')->links() }}
                    </div>
                @endif
            @else
                <div class="rounded-2xl border border-dashed border-[#d9cab3] bg-[#fffdfa] p-10 text-center">
                    <span class="text-3xl">🛏️</span>
                    <h3 class="mt-2 text-base font-bold text-[#1d3c34]">No Stay Bookings Yet</h3>
                    <p class="mt-1 text-xs text-slate-500">Short stay guest bookings will appear here.</p>
                </div>
            @endif
        </section>

