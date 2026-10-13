{{-- Universal Ethiopian Real Estate Payment & Deposit Slip Modal --}}
<div id="universal-payment-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/65 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="relative w-full max-w-xl rounded-3xl border border-[#d9cab3] bg-white p-6 sm:p-8 shadow-2xl animate-in fade-in zoom-in-95 duration-200 my-8">
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Ethiopian Payment Core</span>
                    <span class="text-xs font-bold text-[#587165]">ደህንነቱ የተጠበቀ ክፍያ</span>
                </div>
                <h3 id="payment-modal-title" class="text-xl font-black text-[#1d3c34] mt-1">Make Payment / Submit Deposit</h3>
                <p id="payment-modal-subtitle" class="text-xs text-slate-500">Official Ethiopian Birr (ETB) transaction checkout.</p>
            </div>
            <button type="button" onclick="closeUniversalPaymentModal()" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="universal-payment-form" method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4 text-xs">
            @csrf
            <input type="hidden" name="payment_type" id="modal-payment-type" value="earnest_deposit">
            <input type="hidden" name="property_id" id="modal-property-id" value="">
            <input type="hidden" name="payable_type" id="modal-payable-type" value="">
            <input type="hidden" name="payable_id" id="modal-payable-id" value="">

            {{-- Amount Display Card --}}
            <div class="rounded-2xl border border-[#102b25]/15 bg-[#fffdfa] p-4 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Transaction Amount</span>
                    <p id="payment-modal-purpose" class="text-xs font-bold text-[#1d3c34]">Earnest Money / Reservation Deposit</p>
                </div>
                <div class="text-right">
                    <div class="flex items-center gap-1 justify-end">
                        <span class="text-xs font-bold text-slate-500">ETB</span>
                        <input type="number" step="0.01" min="1" name="amount" id="modal-payment-amount" required class="w-36 text-right font-mono font-black text-xl text-[#1d3c34] bg-white border border-[#d9cab3] rounded-xl px-2 py-1 focus:ring-2 focus:ring-[#1d3c34]/20 focus:outline-none" value="50000">
                    </div>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Ethiopian Birr</span>
                </div>
            </div>

            {{-- Payer Information --}}
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Your Full Name *</label>
                    <input type="text" name="payer_name" required value="{{ auth()->check() ? auth()->user()->name : '' }}" placeholder="Abebe Kebede" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Email Address *</label>
                    <input type="email" name="payer_email" required value="{{ auth()->check() ? auth()->user()->email : '' }}" placeholder="abebe@example.com" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">Phone Number (+251) *</label>
                    <input type="tel" name="payer_phone" required placeholder="+251 91 123 4567" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]/20">
                </div>
            </div>

            {{-- Payment Channel Selection --}}
            <div>
                <label class="block font-bold text-slate-700 mb-2">Select Ethiopian Payment Method *</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                    {{-- Telebirr --}}
                    <label class="relative flex flex-col items-center justify-center p-3 rounded-2xl border-2 border-[#d9cab3] bg-white text-center cursor-pointer hover:border-[#0072bc] has-checked:border-[#0072bc] has-checked:bg-[#f0f7fc] transition">
                        <input type="radio" name="payment_method" value="telebirr" checked onchange="switchPaymentChannel('telebirr')" class="sr-only">
                        <span class="text-sm font-black text-[#0072bc]">telebirr</span>
                        <span class="text-[10px] font-bold text-slate-500 mt-0.5">ቴሌብር</span>
                    </label>

                    {{-- CBE Birr --}}
                    <label class="relative flex flex-col items-center justify-center p-3 rounded-2xl border-2 border-[#d9cab3] bg-white text-center cursor-pointer hover:border-[#6f1d75] has-checked:border-[#6f1d75] has-checked:bg-[#faf0fb] transition">
                        <input type="radio" name="payment_method" value="cbe_birr" onchange="switchPaymentChannel('cbe_birr')" class="sr-only">
                        <span class="text-sm font-black text-[#6f1d75]">CBE Birr</span>
                        <span class="text-[10px] font-bold text-slate-500 mt-0.5">ሲቢኢ ብር</span>
                    </label>

                    {{-- Bank Transfer / Deposit Slip --}}
                    <label class="relative flex flex-col items-center justify-center p-3 rounded-2xl border-2 border-[#d9cab3] bg-white text-center cursor-pointer hover:border-[#1d3c34] has-checked:border-[#1d3c34] has-checked:bg-[#edf4f0] transition">
                        <input type="radio" name="payment_method" value="bank_transfer" onchange="switchPaymentChannel('bank_transfer')" class="sr-only">
                        <span class="text-xs font-black text-[#1d3c34]">Bank Transfer</span>
                        <span class="text-[10px] font-bold text-slate-500 mt-0.5">የባንክ ደረሰኝ</span>
                    </label>
                </div>
            </div>

            {{-- Channel-Specific Interactive Panels --}}
            {{-- 1. Telebirr Panel --}}
            <div id="panel-telebirr" class="payment-method-panel rounded-2xl border border-[#0072bc]/20 bg-[#f4f9fd] p-4 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="rounded bg-[#0072bc] px-2 py-0.5 text-[10px] font-bold text-white uppercase">Telebirr Direct</span>
                    <span class="text-[11px] text-slate-600">Pay directly using Telebirr SuperApp or USSD *127#</span>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Telebirr Mobile Number</label>
                    <input type="tel" placeholder="09XXXXXXXX" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs focus:ring-2 focus:ring-[#0072bc]/20 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Telebirr Transaction Code / SMS Reference (ከተከፈለ በኋላ የሚላክ ኮድ)</label>
                    <input type="text" name="bank_transaction_reference" id="telebirr-ref-input" placeholder="e.g. TB1082947192" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-mono focus:ring-2 focus:ring-[#0072bc]/20 focus:outline-none">
                </div>
            </div>

            {{-- 2. CBE Birr Panel --}}
            <div id="panel-cbe_birr" class="payment-method-panel hidden rounded-2xl border border-[#6f1d75]/20 bg-[#faf2fb] p-4 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="rounded bg-[#6f1d75] px-2 py-0.5 text-[10px] font-bold text-white uppercase">CBE Birr</span>
                    <span class="text-[11px] text-slate-600">Commercial Bank of Ethiopia Mobile Banking</span>
                </div>
                <p class="text-[11px] text-slate-600">Transfer funds to GTM CBE Merchant Account: <strong class="font-mono text-[#6f1d75]">284920</strong></p>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">CBE Transaction / FT Reference Number</label>
                    <input type="text" placeholder="e.g. FT262799XXXX" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-mono focus:ring-2 focus:ring-[#6f1d75]/20 focus:outline-none">
                </div>
            </div>

            {{-- 3. Bank Transfer & Physical Deposit Slip Upload Panel --}}
            <div id="panel-bank_transfer" class="payment-method-panel hidden rounded-2xl border border-[#1d3c34]/20 bg-[#fbf8f2] p-4 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="rounded bg-[#1d3c34] px-2 py-0.5 text-[10px] font-bold text-[#d4af37] uppercase">Official Bank Accounts</span>
                    <span class="text-[11px] text-slate-600">Deposit at any branch or mobile banking</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px] bg-white p-3 rounded-xl border border-[#e8dcc7]">
                    <div>
                        <strong class="block text-[#1d3c34]">CBE Bank</strong>
                        <span class="font-mono text-slate-600 font-bold">1000 2837 4920 1</span>
                    </div>
                    <div>
                        <strong class="block text-[#1d3c34]">Awash Bank</strong>
                        <span class="font-mono text-slate-600 font-bold">0132 0492 8172 00</span>
                    </div>
                    <div>
                        <strong class="block text-[#1d3c34]">Dashen Bank</strong>
                        <span class="font-mono text-slate-600 font-bold">0029 3847 2918 01</span>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Bank Name Used *</label>
                        <select name="bank_name" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs focus:ring-2 focus:ring-[#1d3c34]/20 focus:outline-none">
                            <option value="Commercial Bank of Ethiopia">Commercial Bank of Ethiopia (CBE)</option>
                            <option value="Awash Bank">Awash Bank</option>
                            <option value="Dashen Bank">Dashen Bank</option>
                            <option value="Bank of Abyssinia">Bank of Abyssinia</option>
                            <option value="Cooperative Bank of Oromia">Coop Bank of Oromia</option>
                            <option value="Other Ethiopian Bank">Other Licensed Bank</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Bank FT / Transaction Reference *</label>
                        <input type="text" name="bank_transaction_reference" placeholder="e.g. FT2627498102" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-mono focus:ring-2 focus:ring-[#1d3c34]/20 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Attach Deposit Slip / Transfer Screenshot (የደረሰኝ ፎቶ) *</label>
                    <input type="file" name="deposit_slip" accept=".pdf,image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#1d3c34] file:text-white hover:file:bg-[#254d43]">
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block font-bold text-slate-700 mb-1">Notes / Special Instructions (Optional)</label>
                <textarea name="notes" rows="2" placeholder="e.g. 10% earnest money deposit for formal purchase offer" class="w-full rounded-xl border border-[#d9cab3] bg-[#fffdfa] px-3.5 py-2 text-xs focus:ring-2 focus:ring-[#1d3c34]/20 focus:outline-none"></textarea>
            </div>

            {{-- Submit buttons --}}
            <div class="pt-3 flex items-center justify-end gap-3 border-t border-[#f0e4cf]">
                <button type="button" onclick="closeUniversalPaymentModal()" class="rounded-xl border border-slate-200 px-4 py-2 font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="payment-modal-submit" class="rounded-xl bg-[#1d3c34] px-6 py-2.5 font-bold text-white shadow-xs hover:bg-[#254d43] transition cursor-pointer">
                    Confirm &amp; Submit Payment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUniversalPaymentModal(config = {}) {
        const modal = document.getElementById('universal-payment-modal');
        if (!modal) return;

        const form = document.getElementById('universal-payment-form');
        const amountInput = document.getElementById('modal-payment-amount');
        const submitButton = document.getElementById('payment-modal-submit');

        // Always reset to the default endpoint so a previous target never leaks in.
        form.action = config.formAction || '{{ route('payments.store') }}';
        form.querySelector('#modal-payment-type').value = config.type || 'earnest_deposit';
        form.querySelector('#modal-property-id').value = config.propertyId || '';
        form.querySelector('#modal-payable-type').value = config.payableType || '';
        form.querySelector('#modal-payable-id').value = config.payableId || '';

        amountInput.value = config.amount || 50000;
        amountInput.readOnly = config.lockAmount === true;
        amountInput.classList.toggle('bg-slate-100', amountInput.readOnly);

        if (submitButton) submitButton.textContent = config.submitLabel || 'Confirm & Submit Payment';
        if (config.title) document.getElementById('payment-modal-title').textContent = config.title;
        if (config.purpose) document.getElementById('payment-modal-purpose').textContent = config.purpose;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeUniversalPaymentModal() {
        const modal = document.getElementById('universal-payment-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function switchPaymentChannel(channel) {
        document.querySelectorAll('.payment-method-panel').forEach(panel => {
            panel.classList.add('hidden');
        });
        const active = document.getElementById(`panel-${channel}`);
        if (active) active.classList.remove('hidden');
    }
</script>
