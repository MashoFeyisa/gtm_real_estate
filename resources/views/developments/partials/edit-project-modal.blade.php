<div id="edit-project-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-xs p-4 sm:p-6" aria-labelledby="edit-project-modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center py-6 sm:py-10">
        <div class="relative w-full max-w-2xl rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
            <div>
                <span class="rounded bg-[#102b25] px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Developments</span>
                <h3 class="text-xl font-black text-[#1d3c34] mt-1">Edit Development Project</h3>
                <p class="text-xs text-slate-500">Update master development details, construction status, completion, and pricing.</p>
            </div>
            <button type="button" onclick="closeEditProjectModal()" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="edit-project-form" method="POST" action="" enctype="multipart/form-data" class="mt-5 space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Development / Project Name *</label>
                    <input type="text" id="edit-prj-name" name="name" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Developer / Construction Company *</label>
                    <input type="text" id="edit-prj-developer" name="developer_name" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Developer Phone</label>
                    <input type="text" id="edit-prj-phone" name="developer_phone" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Developer Email</label>
                    <input type="email" id="edit-prj-email" name="developer_email" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">City *</label>
                    <input type="text" id="edit-prj-city" name="city" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Subcity / District</label>
                    <input type="text" id="edit-prj-subcity" name="subcity" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Street Address / Landmark</label>
                    <input type="text" id="edit-prj-address" name="address" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            @include('layouts.partials.location-picker', [
                'pickerId' => 'edit-project-location-map',
                'latInputId' => 'edit-prj-latitude',
                'lngInputId' => 'edit-prj-longitude',
                'pickerHelp' => 'Click the map or drag the pin onto the project site.',
            ])

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Project Type *</label>
                    <select id="edit-prj-type" name="project_type" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="apartments">Apartments & Residences</option>
                        <option value="mixed_use">Mixed-Use Complex</option>
                        <option value="luxury_villas">Luxury Gated Villas</option>
                        <option value="commercial">Commercial / Offices</option>
                        <option value="residential">Residential Estate</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Construction Status *</label>
                    <select id="edit-prj-status" name="construction_status" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="under_construction">Under Construction</option>
                        <option value="pre_construction">Pre-Construction / Off-Plan</option>
                        <option value="near_completion">Near Completion</option>
                        <option value="completed">Completed / Handover</option>
                        <option value="planning">Planning & Permitting</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Completion Progress (%)</label>
                    <input type="number" id="edit-prj-completion" name="overall_completion_percentage" min="0" max="100" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Starting Price (ETB) *</label>
                    <input type="number" id="edit-prj-price" name="starting_price" required min="0" step="1000" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Expected Handover Date</label>
                    <input type="text" id="edit-prj-handover" name="expected_completion_date" placeholder="e.g. Q4 2027" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Land Area (sqm)</label>
                    <input type="number" id="edit-prj-area" name="land_area_sqm" placeholder="e.g. 4500" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Infrastructure & Amenities (One per line or comma-separated)</label>
                <textarea id="edit-prj-facilities" name="facilities_input" rows="2" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Project Description & Architecture</label>
                <textarea id="edit-prj-description" name="description" rows="3" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20"></textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Replace Cover Photo (Upload)</label>
                    <input type="file" name="featured_image_file" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#1d3c34] file:text-white hover:file:bg-[#254d43]">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Or Photo URL (Fallback)</label>
                    <input type="text" id="edit-prj-image-url" name="featured_image" placeholder="https://..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="edit-prj-featured" name="is_featured" value="1" class="rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                    <span class="font-bold text-slate-700">Feature this project on Home Page</span>
                </label>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#f0e4cf]">
                <button type="button" onclick="closeEditProjectModal()" class="rounded-xl border border-slate-200 px-4 py-2.5 font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-[#1d3c34] px-6 py-2.5 font-bold text-white shadow-xs hover:bg-[#254d43] cursor-pointer">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
    </div>
</div>

<script>
    function openEditProjectModal(project) {
        const modal = document.getElementById('edit-project-modal');
        const form = document.getElementById('edit-project-form');
        if (!modal || !form || !project) return;

        form.action = `/developments/${project.id}`;

        document.getElementById('edit-prj-name').value = project.name || '';
        document.getElementById('edit-prj-developer').value = project.developer_name || '';
        document.getElementById('edit-prj-phone').value = project.developer_phone || '';
        document.getElementById('edit-prj-email').value = project.developer_email || '';
        document.getElementById('edit-prj-city').value = project.city || 'Addis Ababa';
        document.getElementById('edit-prj-subcity').value = project.subcity || '';
        document.getElementById('edit-prj-address').value = project.address || '';
        document.getElementById('edit-prj-type').value = project.project_type || 'apartments';
        document.getElementById('edit-prj-status').value = project.construction_status || 'under_construction';
        document.getElementById('edit-prj-completion').value = project.overall_completion_percentage ?? 0;
        document.getElementById('edit-prj-price').value = project.starting_price || 0;
        document.getElementById('edit-prj-handover').value = project.expected_completion_date || '';
        document.getElementById('edit-prj-area').value = project.land_area_sqm || '';
        document.getElementById('edit-prj-description').value = project.description || '';
        document.getElementById('edit-prj-image-url').value = project.featured_image || '';

        if (typeof window.setLocationPickerPosition === 'function') {
            window.setLocationPickerPosition('edit-project-location-map', project.latitude, project.longitude);
        }

        if (Array.isArray(project.facilities)) {
            document.getElementById('edit-prj-facilities').value = project.facilities.join('\n');
        } else if (typeof project.facilities === 'string') {
            document.getElementById('edit-prj-facilities').value = project.facilities;
        } else {
            document.getElementById('edit-prj-facilities').value = '';
        }

        const featuredCheckbox = document.getElementById('edit-prj-featured');
        if (featuredCheckbox) {
            featuredCheckbox.checked = !!project.is_featured;
        }

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeEditProjectModal() {
        const modal = document.getElementById('edit-project-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }
</script>
