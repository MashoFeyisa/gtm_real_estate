{{-- Branches Network Desk for GTM Real Estate Company --}}
<section id="branches" data-section="branches" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[11px] font-black uppercase tracking-wider text-[#d4af37] border border-[#d4af37]/40">★ GTM Real Estate ★</span>
                <h2 class="text-2xl md:text-3xl font-black text-[#1d3c34]">Branch Offices Network</h2>
            </div>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">
                Manage central headquarters, regional branch offices, branch managers, phone numbers, and assigned agent rosters.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-[#f8f3eb] px-3 py-1 text-xs font-bold text-[#1d3c34] border border-[#ecd9be]">
                Total: {{ $branches->count() }}
            </span>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                Active: {{ $branches->where('status', 'active')->count() }}
            </span>
            <button type="button" class="toggle-create-form rounded-full bg-[#1d3c34] px-5 py-2 text-xs md:text-sm font-bold text-white transition hover:bg-[#254d43] shadow-sm cursor-pointer ml-2" data-target="branches-create-panel" data-label-create="+ Add Branch" data-label-close="Close Form">
                + Add Branch
            </button>
        </div>
    </div>

    {{-- Top KPI Cards for Branches --}}
    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl border border-[#ecd9be] bg-[#fbf8f2] p-4 text-center">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">Total Branches</span>
            <p class="mt-1 text-2xl font-black text-[#1d3c34]">{{ $branches->count() }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 text-center">
            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800">Active Offices</span>
            <p class="mt-1 text-2xl font-black text-emerald-900">{{ $branches->where('status', 'active')->count() }}</p>
        </div>
        <div class="rounded-2xl border border-[#d4af37]/40 bg-amber-50/50 p-4 text-center">
            <span class="text-[10px] font-black uppercase tracking-wider text-[#9b6c17]">Headquarters</span>
            <p class="mt-1 text-sm font-bold text-[#1d3c34] truncate">{{ $branches->where('is_headquarters', true)->first()?->name ?? 'Bole HQ' }}</p>
        </div>
        <div class="rounded-2xl border border-blue-200 bg-blue-50/40 p-4 text-center">
            <span class="text-[10px] font-black uppercase tracking-wider text-blue-800">Assigned Agents</span>
            <p class="mt-1 text-2xl font-black text-blue-900">{{ $agents->whereNotNull('branch_id')->count() }}</p>
        </div>
    </div>

    {{-- Add Branch Form Panel --}}
    <div id="branches-create-panel" class="create-form-panel mt-6 hidden rounded-[1.5rem] border border-[#e7ddca] bg-[#f9f5ee] p-6">
        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3">
            <h3 class="text-xl font-black text-[#1d3c34]">Add New Company Branch</h3>
            <button type="button" class="toggle-create-form text-xs font-bold text-rose-700 hover:underline cursor-pointer" data-target="branches-create-panel">Close</button>
        </div>

        <form method="POST" action="{{ request()->routeIs('company.*') ? route('company.branches.store') : route('dashboard.branches.store') }}" class="mt-5 space-y-4">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Branch Name *</label>
                    <input type="text" name="name" required placeholder="e.g. GTM CMC Luxury Center" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Branch Code *</label>
                    <input type="text" name="code" required placeholder="e.g. BR-CMC-03" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">City *</label>
                    <input type="text" name="city" value="Addis Ababa" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Subcity</label>
                    <input type="text" name="subcity" placeholder="e.g. Bole, Yeka, Kirkos" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Physical Address / Street</label>
                    <input type="text" name="address" placeholder="e.g. Sunshine Commercial Plaza, 4th Floor" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Branch Manager Name</label>
                    <input type="text" name="manager_name" placeholder="e.g. Dawit Tadesse" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Manager Phone Number</label>
                    <input type="text" name="phone" placeholder="+251 9..." class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Branch Email</label>
                    <input type="email" name="email" placeholder="branch@seyidrealestate.com" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 pt-2">
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="is_headquarters" value="1" class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                        <span>Set as Corporate Headquarters</span>
                    </label>

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700">Status:</label>
                        <select name="status" class="rounded-lg border border-[#d9cab3] bg-white px-2.5 py-1 text-xs font-bold text-[#1d3c34]">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="rounded-full bg-[#1d3c34] px-7 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-[#254d43] transition cursor-pointer">
                    Save New Branch
                </button>
            </div>
        </form>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#fdfaf5] p-3.5 rounded-2xl border border-[#ebdcc9]">
        <div class="relative flex-1 max-w-md">
            <input type="text" id="dashboard-branches-search" placeholder="Search branches by name, code, manager, city..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2 pl-9 pr-3 text-xs text-[#102b25] placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
            <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <span class="text-xs font-semibold text-slate-500">
            Showing <strong class="text-[#1d3c34]">{{ $branches->count() }}</strong> branches
        </span>
    </div>

    {{-- Branches Data Table --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-[#e7ddca] bg-white shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#fbf8f3] text-[10px] font-black uppercase tracking-wider text-[#587165] border-b border-[#e7ddca]">
                    <tr>
                        <th class="px-4 py-3.5">Branch Name &amp; Code</th>
                        <th class="px-4 py-3.5">Location</th>
                        <th class="px-4 py-3.5">Manager</th>
                        <th class="px-4 py-3.5 text-center">Agents</th>
                        <th class="px-4 py-3.5 text-center">Properties</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0e8d8]">
                    @forelse ($branches as $branch)
                        <tr class="hover:bg-[#fcfaf7] transition dashboard-branch-row" data-search="{{ strtolower($branch->name . ' ' . $branch->code . ' ' . $branch->city . ' ' . $branch->subcity . ' ' . $branch->manager_name) }}">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-[#1d3c34]">{{ $branch->name }}</span>
                                    @if ($branch->is_headquarters)
                                        <span class="rounded-full bg-[#102b25] px-2 py-0.5 text-[9px] font-black uppercase text-[#d4af37] border border-[#d4af37]/40">★ HQ</span>
                                    @endif
                                </div>
                                <span class="font-mono text-[10px] text-slate-400 mt-0.5 block">Code: {{ $branch->code }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                <span class="font-semibold text-slate-800">{{ $branch->city }}</span>
                                @if ($branch->subcity)
                                    <span class="text-slate-500">({{ $branch->subcity }})</span>
                                @endif
                                @if ($branch->address)
                                    <p class="text-[11px] text-slate-400 truncate max-w-xs">{{ $branch->address }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-700">
                                <span class="font-semibold text-slate-800 block">{{ $branch->manager_name ?: 'Not Assigned' }}</span>
                                @if ($branch->phone)
                                    <a href="tel:{{ $branch->phone }}" class="text-[11px] text-[#2d5d4d] hover:underline block">{{ $branch->phone }}</a>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center justify-center rounded-full bg-blue-100 text-blue-900 font-bold px-2 py-0.5 text-xs">
                                    {{ $branch->agents->count() }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center justify-center rounded-full bg-[#f8f3eb] text-[#1d3c34] font-bold px-2 py-0.5 text-xs border border-[#ecd9be]">
                                    {{ $branch->properties->count() }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $branch->status === 'active' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($branch->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" onclick="openBranchEditModal({{ json_encode($branch) }})" class="rounded-lg border border-[#d9cab3] bg-white px-2.5 py-1 text-[11px] font-bold text-[#1d3c34] hover:bg-[#fbf6ed] transition cursor-pointer">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ request()->routeIs('company.*') ? route('company.branches.destroy', $branch) : route('dashboard.branches.destroy', $branch) }}" onsubmit="return confirm('Are you sure you want to delete branch {{ $branch->name }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-2 py-1 text-[11px] font-bold text-rose-700 hover:bg-rose-100 transition cursor-pointer">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                No branches recorded yet. Click "+ Add Branch" above to register your first branch office.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($branches->hasPages())
            <div class="mt-6 border-t border-[#e7ddca] pt-4">
                {{ $branches->fragment('branches')->links() }}
            </div>
        @endif
    </div>
</section>

{{-- Edit Branch Modal --}}
<div id="branch-edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
    <div class="w-full max-w-lg rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
            <h3 class="text-xl font-black text-[#1d3c34]">Edit Branch Office</h3>
            <button type="button" onclick="closeBranchEditModal()" class="rounded-full p-1.5 text-slate-400 hover:bg-[#f5f0e7] hover:text-slate-700 cursor-pointer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="branch-edit-form" method="POST" action="" class="mt-4 space-y-3.5">
            @csrf
            @method('PUT')

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Branch Name *</label>
                    <input type="text" id="edit-branch-name" name="name" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Branch Code *</label>
                    <input type="text" id="edit-branch-code" name="code" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">City *</label>
                    <input type="text" id="edit-branch-city" name="city" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Subcity</label>
                    <input type="text" id="edit-branch-subcity" name="subcity" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Status</label>
                    <select id="edit-branch-status" name="status" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Physical Address</label>
                <input type="text" id="edit-branch-address" name="address" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Manager Name</label>
                    <input type="text" id="edit-branch-manager" name="manager_name" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Manager Phone</label>
                    <input type="text" id="edit-branch-phone" name="phone" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="edit-branch-hq" name="is_headquarters" value="1" class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                <label for="edit-branch-hq" class="text-xs font-semibold text-slate-700 cursor-pointer">Designate as Corporate Headquarters</label>
            </div>

            <div class="flex items-center justify-end gap-2.5 border-t border-[#f0e4cf] pt-3.5 mt-2">
                <button type="button" onclick="closeBranchEditModal()" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                    Cancel
                </button>
                <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-[#254d43] transition">
                    Update Branch
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openBranchEditModal(branch) {
        const modal = document.getElementById('branch-edit-modal');
        const form = document.getElementById('branch-edit-form');
        if (!modal || !form) return;

        form.action = (window.location.pathname.startsWith('/company') ? '/company/branches/' : '/dashboard/branches/') + branch.id;
        document.getElementById('edit-branch-name').value = branch.name || '';
        document.getElementById('edit-branch-code').value = branch.code || '';
        document.getElementById('edit-branch-city').value = branch.city || '';
        document.getElementById('edit-branch-subcity').value = branch.subcity || '';
        document.getElementById('edit-branch-address').value = branch.address || '';
        document.getElementById('edit-branch-manager').value = branch.manager_name || '';
        document.getElementById('edit-branch-phone').value = branch.phone || '';
        document.getElementById('edit-branch-status').value = branch.status || 'active';
        document.getElementById('edit-branch-hq').checked = Boolean(branch.is_headquarters);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeBranchEditModal() {
        const modal = document.getElementById('branch-edit-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    // Branch Search
    document.addEventListener('DOMContentLoaded', function () {
        const branchSearch = document.getElementById('dashboard-branches-search');
        if (branchSearch) {
            branchSearch.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();
                document.querySelectorAll('.dashboard-branch-row').forEach(row => {
                    const searchData = row.dataset.search || '';
                    row.style.display = searchData.includes(query) ? '' : 'none';
                });
            });
        }
    });
</script>
