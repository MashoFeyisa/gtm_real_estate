@php
    $uploadAction = isset($property) ? route('properties.documents.upload', $property) : '#';
@endphp
{{-- Modal: Upload Ethiopian Property Legal Document --}}
<div id="upload-document-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="relative w-full max-w-lg rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-2xl animate-in fade-in zoom-in-95 duration-200 my-8">
        <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
            <div>
                <span class="rounded bg-[#102b25] px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Legal Documentation</span>
                <h3 class="text-lg font-black text-[#1d3c34] mt-1">Upload Property Document</h3>
                <p id="upload-doc-modal-prop-subtitle" class="text-xs text-slate-500">{{ isset($property) ? $property->title : 'Submit official Ethiopian title deed, site plan, or permits for verification.' }}</p>
            </div>
            <button type="button" onclick="closeUploadDocumentModal()" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="property-document-upload-form" method="POST" action="{{ $uploadAction }}" enctype="multipart/form-data" class="mt-5 space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-slate-700 mb-1">Document Category *</label>
                <select name="document_type" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                    <option value="title_deed_carta">Land Title Deed / Carta (የይዞታ ካርታ)</option>
                    <option value="site_plan_mapa">Site Plan / Mapa (ማፓ / Cadastre)</option>
                    <option value="building_permit">Building Permit (የግንባታ ፈቃድ)</option>
                    <option value="ownership_certificate">Ownership Certificate (የባለቤትነት ማረጋገጫ)</option>
                    <option value="tax_clearance">Municipal Tax Clearance (የግብር ክሊራንስ)</option>
                    <option value="power_of_attorney">Power of Attorney / Wekilna (የውክልና ማስረጃ)</option>
                    <option value="seller_id">Owner ID / Passport (መታወቂያ)</option>
                    <option value="other">Other Legal / Architectural Record</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Document Title / Label *</label>
                <input type="text" name="title" required placeholder="e.g. Official Bole Subcity Land Holding Deed" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Official Reference Number</label>
                    <input type="text" name="document_number" placeholder="e.g. AA-BOL-10492/2016" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Access Privacy *</label>
                    <select name="access_level" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="confidential">Confidential (Admin &amp; Agent Only)</option>
                        <option value="verification_only">Verification Only (Badge Display)</option>
                        <option value="transaction_participants">Transaction Participants (Offer/Viewing)</option>
                        <option value="public">Public (Redacted Summary)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Select File (PDF, JPG, PNG up to 12MB) *</label>
                <input type="file" name="document_file" required accept=".pdf,image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#1d3c34] file:text-white hover:file:bg-[#254d43]">
                <p class="mt-1 text-[11px] text-slate-400">Files are encrypted and stored safely adhering to privacy standards.</p>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3 border-t border-[#f0e4cf]">
                <button type="button" onclick="closeUploadDocumentModal()" class="rounded-xl border border-slate-200 px-4 py-2 font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-[#1d3c34] px-5 py-2 font-bold text-white shadow-xs hover:bg-[#254d43] cursor-pointer">
                    Upload for Verification
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPropertyDocumentModal(propertyId, propertyTitle) {
        const form = document.getElementById('property-document-upload-form');
        if (form && propertyId) {
            form.action = `/properties/${propertyId}/documents`;
        }
        const subtitle = document.getElementById('upload-doc-modal-prop-subtitle');
        if (subtitle && propertyTitle) {
            subtitle.textContent = `For: ${propertyTitle}`;
        }
        openUploadDocumentModal();
    }

    function openUploadDocumentModal() {
        const modal = document.getElementById('upload-document-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeUploadDocumentModal() {
        const modal = document.getElementById('upload-document-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
</script>
