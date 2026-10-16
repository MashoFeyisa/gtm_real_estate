@php
    $allAccounts = $bankAccounts ?? \App\Models\BankAccount::query()->orderBy('sort_order')->orderBy('id')->get();
@endphp

<section id="bank-accounts" data-section="bank-accounts" class="section-panel scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[11px] font-black uppercase tracking-wider text-[#d4af37] border border-[#d4af37]/40">★ Corporate Treasury ★</span>
                <h2 class="text-2xl md:text-3xl font-black text-[#1d3c34]">Bank Accounts &amp; Merchant Codes</h2>
            </div>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">
                Manage default commercial bank accounts and CBE Birr merchant shortcodes. Active accounts are shown directly to buyers, tenants, and clients paying via Bank Transfer or CBE Birr.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-[#f8f3eb] px-3 py-1 text-xs font-bold text-[#1d3c34] border border-[#ecd9be]">
                Total: {{ $allAccounts->count() }}
            </span>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                Active: {{ $allAccounts->where('is_active', true)->count() }}
            </span>
            <button type="button" class="toggle-create-form rounded-full bg-[#1d3c34] px-5 py-2 text-xs md:text-sm font-bold text-white transition hover:bg-[#254d43] shadow-sm cursor-pointer ml-2" data-target="bank-accounts-create-panel" data-label-create="+ Add Account" data-label-close="Close Form">
                + Add Account
            </button>
        </div>
    </div>

    {{-- Top KPI Cards for Bank Accounts --}}
    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl border border-[#ecd9be] bg-[#fbf8f2] p-4 text-center">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">Commercial Banks</span>
            <p class="mt-1 text-2xl font-black text-[#1d3c34]">{{ $allAccounts->where('type', 'bank')->count() }}</p>
        </div>
        <div class="rounded-2xl border border-[#6f1d75]/30 bg-[#faf2fb] p-4 text-center">
            <span class="text-[10px] font-black uppercase tracking-wider text-[#6f1d75]">CBE Birr Merchant</span>
            <p class="mt-1 text-2xl font-black text-[#6f1d75]">{{ $allAccounts->where('type', 'cbe_birr')->count() }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 text-center">
            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800">Active in Checkout</span>
            <p class="mt-1 text-2xl font-black text-emerald-900">{{ $allAccounts->where('is_active', true)->count() }}</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-4 text-center">
            <span class="text-[10px] font-black uppercase tracking-wider text-[#9b6c17]">Default Merchant Code</span>
            <p class="mt-1 text-sm font-mono font-black text-[#6f1d75] truncate">
                {{ $allAccounts->where('type', 'cbe_birr')->where('is_active', true)->first()?->account_number ?? '284920' }}
            </p>
        </div>
    </div>

    {{-- Add Bank Account Form Panel --}}
    <div id="bank-accounts-create-panel" class="create-form-panel mt-6 hidden rounded-[1.5rem] border border-[#e7ddca] bg-[#f9f5ee] p-6">
        <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3">
            <h3 class="text-xl font-black text-[#1d3c34]">Add New Bank or Merchant Account</h3>
            <button type="button" class="toggle-create-form text-xs font-bold text-rose-700 hover:underline cursor-pointer" data-target="bank-accounts-create-panel">Close</button>
        </div>

        <form method="POST" action="{{ route('company.bank-accounts.store') }}" class="mt-5 space-y-4">
            @csrf
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Bank / Channel Name *</label>
                    <input type="text" name="bank_name" required placeholder="e.g. CBE Bank, Awash Bank, Dashen Bank" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Account Type *</label>
                    <select name="type" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                        <option value="bank" selected>Commercial Bank (ባንክ ሒሳብ)</option>
                        <option value="cbe_birr">CBE Birr Merchant (የሲቢኢ ብር ነጋዴ ኮድ)</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Account Number / Till Code *</label>
                    <input type="text" name="account_number" required placeholder="e.g. 1000 2837 4920 1 or 284920" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-mono font-bold focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Beneficiary / Company Name</label>
                    <input type="text" name="account_name" value="GTM Real Estate PLC" placeholder="e.g. GTM Real Estate PLC" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Branch Name (Optional)</label>
                    <input type="text" name="branch" placeholder="e.g. Finfinne Branch, HQ" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Sort Order</label>
                    <input type="number" name="sort_order" value="1" min="0" max="999" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Instructions / Notes (Optional)</label>
                <textarea name="instructions" rows="2" placeholder="e.g. Deposit at any Commercial Bank of Ethiopia branch or via CBE Mobile Banking." class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#1d3c34]"></textarea>
            </div>

            <div class="flex items-center gap-2">
                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                    <span>Active and visible in client payment modal</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-[#e7ddca] pt-3">
                <button type="button" class="toggle-create-form rounded-xl border border-slate-300 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 cursor-pointer" data-target="bank-accounts-create-panel">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white transition hover:bg-[#254d43] shadow-xs cursor-pointer">
                    Save Account
                </button>
            </div>
        </form>
    </div>

    {{-- Accounts Roster Table --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-[#ecd9be] bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[#ecd9be] bg-[#fbf8f2] text-[11px] font-black uppercase tracking-wider text-[#1d3c34]">
                        <th class="px-4 py-3.5">Bank / Channel</th>
                        <th class="px-4 py-3.5">Account / Code</th>
                        <th class="px-4 py-3.5">Type</th>
                        <th class="px-4 py-3.5">Beneficiary</th>
                        <th class="px-4 py-3.5">Branch</th>
                        <th class="px-4 py-3.5 text-center">Order</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f2e7d5]">
                    @forelse ($allAccounts as $account)
                        <tr class="hover:bg-[#fcfaf7] transition">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg {{ $account->type === 'cbe_birr' ? 'bg-[#6f1d75] text-white' : 'bg-[#102b25] text-[#d4af37]' }} font-black text-xs">
                                        {{ $account->type === 'cbe_birr' ? 'CBE' : strtoupper(substr($account->bank_name, 0, 2)) }}
                                    </span>
                                    <span class="font-bold text-sm text-[#1d3c34]">{{ $account->bank_name }}</span>
                                </div>
                                @if ($account->instructions)
                                    <p class="text-[10px] text-slate-400 mt-0.5 truncate max-w-xs">{{ $account->instructions }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-mono text-sm font-bold text-slate-800 select-all">{{ $account->account_number }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $account->type === 'cbe_birr' ? 'bg-[#faf2fb] text-[#6f1d75] border border-[#6f1d75]/30' : 'bg-[#edf4f0] text-[#1d3c34] border border-[#1d3c34]/20' }}">
                                    {{ $account->type === 'cbe_birr' ? 'CBE Birr Merchant' : 'Commercial Bank' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 font-medium">
                                {{ $account->account_name ?: '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 font-medium">
                                {{ $account->branch ?: '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-center font-mono font-bold text-slate-600">
                                {{ $account->sort_order }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $account->is_active ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $account->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <form method="POST" action="{{ route('company.bank-accounts.toggle-active', $account) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[11px] font-bold {{ $account->is_active ? 'text-amber-800 hover:bg-amber-50' : 'text-emerald-800 hover:bg-emerald-50' }} transition cursor-pointer">
                                            {{ $account->is_active ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>

                                    <button type="button" onclick="openBankAccountEditModal({{ json_encode($account) }})" class="rounded-lg border border-[#d9cab3] bg-white px-2.5 py-1 text-[11px] font-bold text-[#1d3c34] hover:bg-[#fbf6ed] transition cursor-pointer">
                                        Edit
                                    </button>

                                    <form method="POST" action="{{ route('company.bank-accounts.destroy', $account) }}" onsubmit="return confirm('Are you sure you want to delete {{ $account->bank_name }} ({{ $account->account_number }})?');" class="inline">
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
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                No bank accounts configured. Click "+ Add Account" to configure one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- Edit Bank Account Modal --}}
<div id="edit-bank-account-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 p-4 backdrop-blur-xs flex items-center justify-center">
    <div class="relative w-full max-w-xl rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-2xl md:p-8" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
            <div>
                <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Edit Details</span>
                <h3 class="mt-1 text-xl font-black text-[#1d3c34]">Update Bank / Merchant Account</h3>
            </div>
            <button type="button" onclick="closeBankAccountEditModal()" class="rounded-full bg-slate-100 p-2 text-slate-500 hover:bg-slate-200 hover:text-slate-700 cursor-pointer">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="edit-bank-account-form" method="POST" action="" class="mt-5 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Bank / Channel Name *</label>
                    <input type="text" id="edit-bank-name" name="bank_name" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Account Type *</label>
                    <select id="edit-bank-type" name="type" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                        <option value="bank">Commercial Bank (ባንክ ሒሳብ)</option>
                        <option value="cbe_birr">CBE Birr Merchant (የሲቢኢ ብር ነጋዴ ኮድ)</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Account Number / Till Code *</label>
                    <input type="text" id="edit-bank-account-number" name="account_number" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-mono font-bold focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Beneficiary / Company Name</label>
                    <input type="text" id="edit-bank-account-name" name="account_name" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Branch Name (Optional)</label>
                    <input type="text" id="edit-bank-branch" name="branch" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Sort Order</label>
                    <input type="number" id="edit-bank-sort-order" name="sort_order" min="0" max="999" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Instructions / Notes (Optional)</label>
                <textarea id="edit-bank-instructions" name="instructions" rows="2" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#1d3c34]"></textarea>
            </div>

            <div class="flex items-center gap-2">
                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="checkbox" id="edit-bank-is-active" name="is_active" value="1" class="rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                    <span>Active and visible in client payment checkout modal</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-[#f0e4cf] pt-4">
                <button type="button" onclick="closeBankAccountEditModal()" class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-[#1d3c34] px-5 py-2 text-xs font-bold text-white transition hover:bg-[#254d43] shadow-xs cursor-pointer">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openBankAccountEditModal(account) {
        const modal = document.getElementById('edit-bank-account-modal');
        const form = document.getElementById('edit-bank-account-form');
        if (!modal || !form) return;

        form.action = '/company/bank-accounts/' + account.id;
        document.getElementById('edit-bank-name').value = account.bank_name || '';
        document.getElementById('edit-bank-type').value = account.type || 'bank';
        document.getElementById('edit-bank-account-number').value = account.account_number || '';
        document.getElementById('edit-bank-account-name').value = account.account_name || '';
        document.getElementById('edit-bank-branch').value = account.branch || '';
        document.getElementById('edit-bank-sort-order').value = account.sort_order || 0;
        document.getElementById('edit-bank-instructions').value = account.instructions || '';
        document.getElementById('edit-bank-is-active').checked = !!account.is_active;

        modal.classList.remove('hidden');
    }

    function closeBankAccountEditModal() {
        const modal = document.getElementById('edit-bank-account-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBankAccountEditModal();
        }
    });

    document.getElementById('edit-bank-account-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeBankAccountEditModal();
        }
    });
</script>
