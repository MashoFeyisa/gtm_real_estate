{{-- Module 4: Property Viewing Appointments Desk --}}
<section id="viewings" data-section="viewings" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#f0e4cf] pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[11px] font-black uppercase tracking-wider text-[#d4af37]">Appointments</span>
                <h2 class="text-2xl font-black text-[#1d3c34]">Property Viewing Appointments</h2>
            </div>
            <p class="mt-1 text-xs text-slate-500">Coordinate on-site visits and live virtual tours with clients.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <span class="rounded-full bg-[#f8f3eb] px-3 py-1 text-xs font-bold text-[#1d3c34] border border-[#ecd9be]">
                Total: {{ isset($viewings) ? $viewings->count() : 0 }}
            </span>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                Confirmed: {{ isset($viewings) ? $viewings->where('status', 'confirmed')->count() : 0 }}
            </span>
            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-800 border border-amber-200">
                Requested: {{ isset($viewings) ? $viewings->where('status', 'requested')->count() : 0 }}
            </span>
        </div>
    </div>

    @if (isset($viewings) && $viewings->isNotEmpty())
        <div class="mt-6 divide-y divide-[#f0e4cf] overflow-hidden rounded-2xl border border-[#d9cab3] bg-white">
            @foreach ($viewings as $viewing)
                @php
                    $cleanClientPhone = preg_replace('/[^0-9]/', '', $viewing->buyer_phone);
                    if (str_starts_with($cleanClientPhone, '0')) {
                        $cleanClientPhone = '251' . substr($cleanClientPhone, 1);
                    }
                @endphp
                <div class="p-5 transition hover:bg-[#fffdfa]">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        {{-- Left: Viewing & Property info --}}
                        <div class="flex items-start gap-4">
                            @if ($viewing->property && $viewing->property->image_url)
                                <img src="{{ $viewing->property->image_url }}" alt="{{ $viewing->property->title }}" class="h-16 w-16 shrink-0 rounded-2xl object-cover border border-[#d9cab3]">
                            @else
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-[#102b25] text-xl text-[#d4af37]">
                                    🏠
                                </div>
                            @endif

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-xs font-black text-[#102b25] bg-[#102b25]/5 px-2 py-0.5 rounded border border-[#102b25]/10">
                                        {{ $viewing->viewing_reference }}
                                    </span>
                                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider
                                        @if($viewing->status === 'confirmed') bg-emerald-100 text-emerald-800
                                        @elseif($viewing->status === 'requested') bg-amber-100 text-amber-800
                                        @elseif($viewing->status === 'completed') bg-blue-100 text-blue-800
                                        @elseif($viewing->status === 'cancelled') bg-rose-100 text-rose-800
                                        @else bg-slate-100 text-slate-700 @endif">
                                        {{ ucfirst($viewing->status) }}
                                    </span>
                                    <span class="text-[11px] font-semibold text-slate-500">
                                        {{ $viewing->viewing_type === 'in_person' ? '🚶 In-Person Guided' : '📹 Virtual Video Tour' }}
                                    </span>
                                </div>

                                <h4 class="mt-1 font-bold text-base text-[#1d3c34]">
                                    @if ($viewing->property)
                                        <a href="{{ route('properties.show', $viewing->property->slug) }}" target="_blank" class="hover:underline">
                                            {{ $viewing->property->title }}
                                        </a>
                                    @else
                                        <span>Property Listing</span>
                                    @endif
                                </h4>

                                <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-slate-600">
                                    <span class="font-semibold text-[#102b25]">
                                        📅 {{ \Carbon\Carbon::parse($viewing->viewing_date)->format('M d, Y') }}
                                    </span>
                                    <span>·</span>
                                    <span>⏰ {{ $viewing->formatted_time_slot }}</span>
                                    @if ($viewing->agent)
                                        <span>·</span>
                                        <span>Agent: <strong class="text-[#1d3c34]">{{ $viewing->agent->name }}</strong></span>
                                    @endif
                                </div>

                                @if ($viewing->buyer_notes)
                                    <p class="mt-2 rounded-xl bg-[#f8f3eb] p-2.5 text-xs text-slate-700 italic border border-[#ecd9be]">
                                        “{{ $viewing->buyer_notes }}”
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Middle: Buyer contact info --}}
                        <div class="rounded-xl bg-[#f9f5ed] p-3 text-xs min-w-[200px] border border-[#ecd9be]">
                            <span class="block text-[10px] uppercase font-bold text-[#60716c]">Client Contact</span>
                            <span class="font-bold text-[#1d3c34] block text-sm">{{ $viewing->buyer_name }}</span>
                            <div class="mt-1.5 flex items-center gap-2">
                                <a href="tel:{{ $viewing->buyer_phone }}" class="inline-flex items-center gap-1 rounded bg-[#00b53f] px-2 py-1 text-[11px] font-bold text-white hover:bg-[#009b36]">
                                    📞 Call
                                </a>
                                <a href="https://wa.me/{{ $cleanClientPhone }}" target="_blank" class="inline-flex items-center gap-1 rounded bg-[#25D366] px-2 py-1 text-[11px] font-bold text-white hover:bg-[#20ba59]">
                                    💬 WhatsApp
                                </a>
                                <span class="text-slate-500 truncate" title="{{ $viewing->buyer_phone }}">{{ $viewing->buyer_phone }}</span>
                            </div>
                            <span class="mt-1 block text-slate-500 truncate">{{ $viewing->buyer_email }}</span>
                        </div>

                        {{-- Right: Status Action Form --}}
                        <div class="flex flex-col gap-2 shrink-0">
                            <form method="POST" action="{{ route('viewings.update-status', $viewing) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <select name="status" class="rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                                    <option value="requested" {{ $viewing->status === 'requested' ? 'selected' : '' }}>Requested</option>
                                    <option value="confirmed" {{ $viewing->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="rescheduled" {{ $viewing->status === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                                    <option value="completed" {{ $viewing->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $viewing->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="no_show" {{ $viewing->status === 'no_show' ? 'selected' : '' }}>No Show</option>
                                </select>
                                <button type="submit" class="rounded-xl bg-[#102b25] px-3.5 py-1.5 text-xs font-bold text-[#d4af37] hover:bg-[#1d3c34] transition">
                                    Update
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fffdfa] p-10 text-center">
            <span class="text-3xl">📅</span>
            <h3 class="mt-2 text-base font-bold text-[#1d3c34]">No Viewing Appointments Yet</h3>
            <p class="mt-1 text-xs text-slate-500">Requests submitted by buyers on property detail pages will appear here for schedule coordination.</p>
        </div>
    @endif
</section>
