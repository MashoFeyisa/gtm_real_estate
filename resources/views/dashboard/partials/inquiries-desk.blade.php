        <section id="inquiries" data-section="inquiries" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[#1d3c34]">Message</h2>
                    <p class="mt-1 text-sm text-slate-500">Direct client contact requests and property inquiries submitted through the website.</p>
                </div>
                <div class="flex items-center gap-2">
                    @php $unreadInquiriesCount = isset($inquiries) ? $inquiries->whereNull('read_at')->count() : 0; @endphp
                    <span id="inquiries-unread-pill" class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#9b6c17] {{ $unreadInquiriesCount > 0 ? '' : 'hidden' }}">
                        <span id="inquiries-unread-count">{{ $unreadInquiriesCount }}</span> New
                    </span>
                    <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">{{ isset($inquiries) ? $inquiries->count() : 0 }} Total</span>
                </div>
            </div>

            @if (isset($inquiries) && $inquiries->isNotEmpty())
                <div class="mt-6 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca] bg-white">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#2d5d4d] text-[#f8f3eb]">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Client</th>
                                <th class="px-5 py-4 font-semibold">Assigned Agent</th>
                                <th class="px-5 py-4 font-semibold">Subject</th>
                                <th class="w-44 max-w-[170px] px-5 py-4 font-semibold">Message</th>
                                <th class="px-5 py-4 font-semibold">Received</th>
                                <th class="px-5 py-4 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f1e8de]">
                            @foreach ($inquiries as $inquiry)
                                <tr id="inquiry-row-{{ $inquiry->id }}" class="border-t border-[#e7ddca] align-top transition hover:bg-[#fdf9f4] {{ ! $inquiry->read_at ? 'bg-[#fffdf9]' : '' }}"
                                    data-inquiry-row="{{ $inquiry->id }}">
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-[#1d3c34]">{{ $inquiry->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $inquiry->email }}</p>
                                        @if ($inquiry->phone)
                                            <p class="text-xs text-slate-500">{{ $inquiry->phone }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($inquiry->agent)
                                            <span class="rounded-full bg-[#dfeee4] px-2.5 py-1 text-xs font-bold text-[#1d3c34]">{{ $inquiry->agent->name }}</span>
                                        @else
                                            <span class="rounded-full bg-[#f4efe7] px-2.5 py-1 text-xs font-bold text-slate-600">Admin / General</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-[#1d3c34]">{{ $inquiry->subject }}</p>
                                        @if (! $inquiry->read_at)
                                            <span class="inquiry-badge-new mt-1 inline-block rounded-full bg-[#f9ecd0] px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.14em] text-[#9b6c17]">New</span>
                                        @endif
                                    </td>
                                    <td class="w-44 max-w-[170px] px-5 py-4 text-xs leading-5 text-slate-600">
                                        <div class="flex items-center justify-between gap-1.5">
                                            <button type="button" class="view-inquiry-btn truncate block max-w-[100px] text-left text-xs text-slate-600 hover:text-[#1d3c34] hover:underline cursor-pointer"
                                                title="Click to view message"
                                                data-id="{{ $inquiry->id }}"
                                                data-read="{{ $inquiry->read_at ? '1' : '0' }}"
                                                data-read-url="{{ route('dashboard.inquiries.read', $inquiry) }}"
                                                data-name="{{ $inquiry->name }}"
                                                data-email="{{ $inquiry->email }}"
                                                data-phone="{{ $inquiry->phone ?? '' }}"
                                                data-agent="{{ $inquiry->agent?->name ?? 'Admin / General' }}"
                                                data-subject="{{ $inquiry->subject }}"
                                                data-message="{{ $inquiry->message }}"
                                                data-received="{{ $inquiry->created_at?->format('M d, Y H:i') }}">
                                                {{ Str::limit($inquiry->message, 24) }}
                                            </button>
                                            <button type="button" class="view-inquiry-btn shrink-0 inline-flex items-center gap-1 rounded-full border border-[#d9cab3] bg-[#fbf8f3] px-2.5 py-1 text-[11px] font-bold text-[#1d3c34] transition hover:bg-[#dfeee4] hover:border-[#2d5d4d] shadow-xs cursor-pointer"
                                                data-id="{{ $inquiry->id }}"
                                                data-read="{{ $inquiry->read_at ? '1' : '0' }}"
                                                data-read-url="{{ route('dashboard.inquiries.read', $inquiry) }}"
                                                data-name="{{ $inquiry->name }}"
                                                data-email="{{ $inquiry->email }}"
                                                data-phone="{{ $inquiry->phone ?? '' }}"
                                                data-agent="{{ $inquiry->agent?->name ?? 'Admin / General' }}"
                                                data-subject="{{ $inquiry->subject }}"
                                                data-message="{{ $inquiry->message }}"
                                                data-received="{{ $inquiry->created_at?->format('M d, Y H:i') }}">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>View</span>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-500 whitespace-nowrap">{{ $inquiry->created_at?->format('M d, Y H:i') }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <form method="POST" action="{{ route('dashboard.inquiries.destroy', $inquiry) }}" data-confirm="Are you sure you want to delete this message?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1.5 text-xs font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($inquiries->hasPages())
                    <div class="mt-6 border-t border-[#e7ddca] pt-4">
                        {{ $inquiries->fragment('inquiries')->links() }}
                    </div>
                @endif
            @else
                <p class="mt-6 text-sm text-slate-500">No messages or inquiries yet.</p>
            @endif
        </section>

