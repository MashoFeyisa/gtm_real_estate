<div id="create-project-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-xs p-4 sm:p-6" aria-labelledby="create-project-modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center py-6 sm:py-10">
        <div class="relative w-full max-w-2xl rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
            <div>
                <span class="rounded bg-[#102b25] px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Developments</span>
                <h3 class="text-xl font-black text-[#1d3c34] mt-1">Post New Development Project</h3>
                <p class="text-xs text-slate-500">Publish a master development project with buildings, units, and construction progress.</p>
            </div>
            <button type="button" onclick="closeCreateProjectModal()" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('developments.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4 text-xs">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Development / Project Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Bole Atlas Luxury Heights" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Developer / Construction Company *</label>
                    <input type="text" name="developer_name" required placeholder="e.g. Noah Real Estate / Flintstone" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Developer Phone</label>
                    <input type="text" name="developer_phone" placeholder="+251 91 123 4567" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Developer Email</label>
                    <input type="email" name="developer_email" placeholder="sales@developer.et" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">City *</label>
                    <input type="text" name="city" required value="Addis Ababa" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Subcity / District</label>
                    <input type="text" name="subcity" placeholder="e.g. Bole, Kirkos, Yeka" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Street Address / Landmark</label>
                    <input type="text" name="address" placeholder="e.g. Near Edna Mall" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            @include('layouts.partials.location-picker', [
                'pickerId' => 'create-project-location-map',
                'latInputId' => 'create-project-latitude',
                'lngInputId' => 'create-project-longitude',
                'lat' => old('latitude'),
                'lng' => old('longitude'),
                'pickerHelp' => 'Click the map or drag the pin onto the project site; typing the sub-city recenters the map.',
            ])

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Project Type *</label>
                    <select name="project_type" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="apartments">Apartments & Residences</option>
                        <option value="mixed_use">Mixed-Use Complex</option>
                        <option value="luxury_villas">Luxury Gated Villas</option>
                        <option value="commercial">Commercial / Offices</option>
                        <option value="residential">Residential Estate</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Construction Status *</label>
                    <select name="construction_status" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="under_construction">Under Construction</option>
                        <option value="pre_construction">Pre-Construction / Off-Plan</option>
                        <option value="near_completion">Near Completion</option>
                        <option value="completed">Completed / Handover</option>
                        <option value="planning">Planning & Permitting</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Completion Progress (%)</label>
                    <input type="number" name="overall_completion_percentage" min="0" max="100" value="25" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Starting Price (ETB) *</label>
                    <input type="number" name="starting_price" required min="0" step="1000" placeholder="12500000" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Expected Handover Date</label>
                    <input type="text" name="expected_completion_date" placeholder="e.g. Q4 2027" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Land Area (sqm)</label>
                    <input type="number" name="land_area_sqm" placeholder="e.g. 4500" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Infrastructure & Amenities (One per line or comma-separated)</label>
                <textarea name="facilities_input" rows="2" placeholder="3-Phase Power & Dual 500kVA Generators&#10;Municipal Water + 200,000L Reservoir Tank&#10;High-Speed Elevators&#10;Underground Resident Parking&#10;24/7 Gated Security & CCTV" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Project Description & Architecture</label>
                <textarea name="description" rows="3" placeholder="Describe the master development, architecture, finishes, access roads, and surroundings..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20"></textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Cover Photo (File Upload)</label>
                    <input type="file" name="featured_image_file" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#1d3c34] file:text-white hover:file:bg-[#254d43]">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Or Photo URL (Fallback)</label>
                    <input type="text" name="featured_image" placeholder="https://images.unsplash.com/..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#f0e4cf]">
                <button type="button" onclick="closeCreateProjectModal()" class="rounded-xl border border-slate-200 px-4 py-2.5 font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-[#1d3c34] px-6 py-2.5 font-bold text-white shadow-xs hover:bg-[#254d43] cursor-pointer">
                    Publish Development Project
                </button>
            </div>
        </form>
    </div>
    </div>
</div>

<script>
    function openCreateProjectModal() {
        const modal = document.getElementById('create-project-modal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeCreateProjectModal() {
        const modal = document.getElementById('create-project-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }
</script>
