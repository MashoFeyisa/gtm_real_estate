    {{-- Sell/Rent Property Agreement Modal for Admin --}}
    <div id="admin-agreement-modal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-2xl md:p-8">
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                <div>
                    <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-bold uppercase tracking-wider text-[#9b6c17]">Official Agreement</span>
                    <h3 id="modal-property-title" class="mt-2 text-xl font-black text-[#1d3c34]">Sell Property with Agreement</h3>
                </div>
                <button type="button" id="close-agreement-modal" class="rounded-full p-2 text-slate-400 hover:bg-[#f5f0e7] hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="admin-agreement-form" method="POST" action="" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Buyer / Tenant Full Name *</label>
                    <input type="text" name="buyer_name" required placeholder="e.g. Abebe Bikila" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Email Address *</label>
                        <input type="email" name="buyer_email" required placeholder="client@example.com" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Phone Number</label>
                        <input type="text" name="buyer_phone" placeholder="+251 9..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Agreed Price (ETB) *</label>
                        <input type="number" step="0.01" name="deal_price" id="modal-deal-price" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div id="modal-lease-months-container">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Lease Months</label>
                        <input type="number" name="lease_months" id="modal-lease-months" min="1" max="120" value="12" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Special Terms / Agreement Notes</label>
                    <textarea name="admin_note" rows="2" placeholder="Optional conditions or payment terms..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]"></textarea>
                </div>
                <div class="mt-6 flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button type="button" id="cancel-agreement-modal" class="rounded-full border border-[#d9cab3] bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-[#f5f0e7]">
                        Cancel
                    </button>
                    <button type="submit" name="submit_action" value="edit" class="rounded-full border border-[#b7842d] bg-[#fdf8ef] px-5 py-2.5 text-sm font-bold text-[#9b6c17] transition hover:bg-[#f9ecd0]">
                        Edit Content & Generate
                    </button>
                    <button type="submit" name="submit_action" value="generate" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]">
                        Generate Directly
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Client Inquiry / Message Details Modal for Admin --}}
    <div id="admin-inquiry-modal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-2xl md:p-8 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                <div class="min-w-0 pr-4">
                    <span class="rounded-full bg-[#dfeee4] px-3 py-1 text-xs font-bold uppercase tracking-wider text-[#1d3c34]">Message Details</span>
                    <h3 id="modal-inquiry-subject" class="mt-2 truncate text-xl font-black text-[#1d3c34]">Subject</h3>
                </div>
                <button type="button" id="close-inquiry-modal" class="shrink-0 rounded-full p-2 text-slate-400 hover:bg-[#f5f0e7] hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mt-5 space-y-4">
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fcfaf7] p-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-slate-600">
                        <div>
                            <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px] block">Sender Name</span>
                            <span id="modal-inquiry-name" class="font-bold text-sm text-[#1d3c34] block"></span>
                        </div>
                        <div>
                            <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px] block">Assigned Agent</span>
                            <span id="modal-inquiry-agent" class="font-bold text-sm text-[#1d3c34] block"></span>
                        </div>
                        <div>
                            <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px] block">Email</span>
                            <a id="modal-inquiry-email" href="#" class="font-semibold text-[#2d5d4d] hover:underline block truncate"></a>
                        </div>
                        <div>
                            <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px] block">Phone</span>
                            <a id="modal-inquiry-phone" href="#" class="font-semibold text-[#2d5d4d] hover:underline block"></a>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px] block">Received Date</span>
                            <span id="modal-inquiry-received" class="text-slate-600 block"></span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Message Content</label>
                    <div id="modal-inquiry-message" class="max-h-60 overflow-y-auto whitespace-pre-wrap rounded-2xl border border-[#d9cab3] bg-white p-4 text-sm leading-relaxed text-slate-700"></div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-end gap-3 border-t border-[#e7ddca] pt-4">
                <button type="button" id="cancel-inquiry-modal" class="rounded-full border border-[#d9cab3] bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                    Close
                </button>
                <a id="modal-inquiry-reply" href="#" class="inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Reply via Email
                </a>
            </div>
        </div>
    </div>

