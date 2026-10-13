<div id="add-unit-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-xs p-4 sm:p-6" aria-labelledby="add-unit-modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center py-6 sm:py-10">
        <div class="relative w-full max-w-lg rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
            <div>
                <span class="rounded bg-[#102b25] px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Inventory</span>
                <h3 class="text-xl font-black text-[#1d3c34] mt-1">Add Unit to Inventory</h3>
                <p id="add-unit-project-title" class="text-xs text-slate-500">Adding unit to development inventory.</p>
            </div>
            <button type="button" onclick="closeAddUnitModal()" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="add-unit-form" method="POST" action="" class="mt-5 space-y-4 text-xs">
            @csrf

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Unit Number *</label>
                    <input type="text" name="unit_number" required placeholder="e.g. A-302, PH-1501" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Floor Level *</label>
                    <input type="text" name="floor_number" required placeholder="e.g. 3, 15, Penthouse" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Unit Type *</label>
                    <select name="unit_type" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="1_bedroom">1 Bedroom</option>
                        <option value="2_bedroom" selected>2 Bedroom</option>
                        <option value="3_bedroom">3 Bedroom</option>
                        <option value="4_bedroom">4 Bedroom</option>
                        <option value="penthouse">Penthouse</option>
                        <option value="duplex">Duplex</option>
                        <option value="studio">Studio</option>
                        <option value="retail_shop">Retail Shop</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Bedrooms *</label>
                    <input type="number" name="bedrooms" min="0" max="20" value="2" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Bathrooms *</label>
                    <input type="number" name="bathrooms" min="1" max="20" value="2" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Area (sqm) *</label>
                    <input type="number" name="area_sqm" step="0.1" min="1" required placeholder="125.5" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Total Price (ETB) *</label>
                    <input type="number" name="price" step="1000" min="1" required placeholder="14500000" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Orientation / View</label>
                    <input type="text" name="orientation" placeholder="e.g. Bole Road View" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Parking Spaces</label>
                    <input type="number" name="parking_spaces" min="0" value="1" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Initial Status *</label>
                    <select name="status" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3 py-2.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                        <option value="available" selected>Available</option>
                        <option value="reserved">Reserved</option>
                        <option value="contracted">Contracted</option>
                        <option value="sold">Sold</option>
                        <option value="blocked">Blocked</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#f0e4cf]">
                <button type="button" onclick="closeAddUnitModal()" class="rounded-xl border border-slate-200 px-4 py-2.5 font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-[#1d3c34] px-6 py-2.5 font-bold text-white shadow-xs hover:bg-[#254d43] cursor-pointer">
                    Add Unit to Inventory
                </button>
            </div>
        </form>
    </div>
    </div>
</div>

<script>
    function openAddUnitModal(projectId, projectName) {
        const modal = document.getElementById('add-unit-modal');
        const form = document.getElementById('add-unit-form');
        const title = document.getElementById('add-unit-project-title');
        
        if (title) {
            title.textContent = 'Adding unit to: ' + projectName;
        }
        if (form) {
            form.action = "{{ url('/developments') }}/" + projectId + "/units";
        }
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeAddUnitModal() {
        const modal = document.getElementById('add-unit-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }
</script>
