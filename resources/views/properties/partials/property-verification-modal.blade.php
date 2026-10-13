{{-- Modal: Admin Property Legal Verification Certification --}}
<div id="property-verification-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="relative w-full max-w-xl rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-2xl animate-in fade-in zoom-in-95 duration-200 my-8">
        <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
            <div>
                <span class="rounded bg-[#102b25] px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Admin Certification</span>
                <h3 class="text-xl font-black text-[#1d3c34] mt-1">Property Legal Verification</h3>
                <p id="pv-property-title" class="text-xs text-slate-500 font-semibold mt-0.5">Certify property authenticity, title deed, and cadastre reference.</p>
            </div>
            <button type="button" onclick="closeVerificationModal()" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="property-verification-form" method="POST" action="" class="mt-5 space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Verification Level *</label>
                    <select id="pv-level" name="verification_level" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="0">Level 0 — Unverified</option>
                        <option value="1">Level 1 — Identity Verified</option>
                        <option value="2">Level 2 — Agent Licensed</option>
                        <option value="3">Level 3 — Documents Submitted</option>
                        <option value="4">Level 4 — Professionally Reviewed</option>
                        <option value="5">Level 5 — Transaction Ready</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Audit Status *</label>
                    <select id="pv-status" name="verification_status" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="unverified">Unverified</option>
                        <option value="pending">Pending Review</option>
                        <option value="verified">Verified &amp; Certified</option>
                        <option value="rejected">Rejected / Incomplete</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Official Title Deed / Carta No.</label>
                    <input type="text" id="pv-deed-number" name="title_deed_number" placeholder="e.g. AA-BOL-10492/2016" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">City Cadastre Reference Code</label>
                    <input type="text" id="pv-cadastre-code" name="cadastre_code" placeholder="e.g. CAD-ET-AA-0924" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-2">Award Certified Verification Badges</label>
                <div class="space-y-2 rounded-2xl border border-[#f0e4cf] bg-[#fffdfa] p-3.5">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="verified_badges[]" value="title_deed_verified" id="badge-title-deed" class="rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                        <span class="text-xs font-semibold text-slate-800">Title Deed Verified (ካርታ የተረጋገጠ)</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="verified_badges[]" value="site_plan_verified" id="badge-site-plan" class="rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                        <span class="text-xs font-semibold text-slate-800">Site Plan &amp; Mapa Matched (ማፓ የተረጋገጠ)</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="verified_badges[]" value="physical_inspection_verified" id="badge-inspection" class="rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                        <span class="text-xs font-semibold text-slate-800">Physical Inspection Verified (በቦታው ተገኝቶ የታየ)</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="verified_badges[]" value="cadastre_verified" id="badge-cadastre" class="rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                        <span class="text-xs font-semibold text-slate-800">Municipal Cadastre Registry Verified (ከካዳስተር ጋር የተገናዘበ)</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="verified_badges[]" value="licensed_agent" id="badge-agent" class="rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                        <span class="text-xs font-semibold text-slate-800">Licensed Broker / Agency Certified (ፈቃድ ያለው ደላላ)</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Official Verification Audit Notes</label>
                <textarea id="pv-notes" name="verification_notes" rows="2" placeholder="Record verification checks, municipal department contacts, or reasons for status..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20"></textarea>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3 border-t border-[#f0e4cf]">
                <button type="button" onclick="closeVerificationModal()" class="rounded-xl border border-slate-200 px-4 py-2 font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-[#1d3c34] px-5 py-2 font-bold text-white shadow-xs hover:bg-[#254d43] cursor-pointer">
                    Save Certification
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openVerificationModal(prop) {
        const modal = document.getElementById('property-verification-modal');
        const form = document.getElementById('property-verification-form');
        if (!modal || !form || !prop) return;

        form.action = `/properties/${prop.id}/verification`;
        document.getElementById('pv-property-title').textContent = `${prop.title} (${prop.property_code || 'ID: ' + prop.id})`;
        document.getElementById('pv-level').value = prop.verification_level ?? 0;
        document.getElementById('pv-status').value = prop.verification_status ?? 'unverified';
        document.getElementById('pv-deed-number').value = prop.title_deed_number || '';
        document.getElementById('pv-cadastre-code').value = prop.cadastre_code || '';
        document.getElementById('pv-notes').value = prop.verification_notes || '';

        const badges = Array.isArray(prop.verified_badges) ? prop.verified_badges : [];
        document.getElementById('badge-title-deed').checked = badges.includes('title_deed_verified');
        document.getElementById('badge-site-plan').checked = badges.includes('site_plan_verified');
        document.getElementById('badge-inspection').checked = badges.includes('physical_inspection_verified');
        document.getElementById('badge-cadastre').checked = badges.includes('cadastre_verified');
        document.getElementById('badge-agent').checked = badges.includes('licensed_agent');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeVerificationModal() {
        const modal = document.getElementById('property-verification-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
</script>
