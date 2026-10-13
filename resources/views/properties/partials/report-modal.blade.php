<div id="report-property-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-lg rounded-3xl bg-white p-6 md:p-8 shadow-2xl border border-rose-100">
        <button type="button" onclick="document.getElementById('report-property-modal').classList.add('hidden')" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 class="text-xl font-black text-[#1d3c34]">Report Listing</h3>
                <p class="text-xs text-slate-500">Protect the marketplace against fraudulent or misleading listings.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('properties.report', $property) }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Reason for Reporting <span class="text-red-500">*</span></label>
                <select name="reason" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-rose-500">
                    <option value="scam">🚨 Suspected Scam / Advance Payment Fraud</option>
                    <option value="fake_property">❌ Fake Property / Does Not Exist in Specified Location</option>
                    <option value="suspicious_documents">📑 Suspicious or Inauthentic Land/Title Documents</option>
                    <option value="wrong_price">💰 Price is Grossly Inaccurate or Misleading</option>
                    <option value="unavailable">🚫 Property is Already Sold or Unavailable</option>
                    <option value="duplicate">📑 Duplicate Listing by Unauthorized Broker</option>
                    <option value="misleading">⚠️ Misleading Photos or Inaccurate Specifications</option>
                    <option value="inappropriate_content">🔞 Inappropriate Content or Contact Infringement</option>
                    <option value="other">ℹ️ Other Marketplace Guideline Violation</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Description & Evidence <span class="text-red-500">*</span></label>
                <textarea name="description" rows="3" required placeholder="Please provide specific details explaining what is wrong or suspicious about this listing..." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-medium text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Your Contact Email (Optional)</label>
                <input type="email" name="contact_email" value="{{ auth()->user()?->email ?? '' }}" placeholder="For follow-up from our Trust & Safety officer" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-medium text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-rose-500">
            </div>

            <div class="rounded-xl bg-rose-50 p-3 text-[11px] text-rose-800">
                🔒 Reports are reviewed strictly by our Verification & Safety Compliance officers. The listing poster will not see your identity.
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('report-property-modal').classList.add('hidden')" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-rose-600 px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-white hover:bg-rose-700 transition shadow-sm">
                    Submit Safety Report
                </button>
            </div>
        </form>
    </div>
</div>
