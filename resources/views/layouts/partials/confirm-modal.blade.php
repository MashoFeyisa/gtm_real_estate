<div id="custom-confirm-modal" class="fixed inset-0 z-[999] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="custom-confirm-title">
    {{-- Backdrop --}}
    <div id="custom-confirm-backdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

    {{-- Modal Box --}}
    <div class="relative z-10 w-full max-w-md rounded-2xl border border-[#d9cab3] bg-white p-6 shadow-2xl transition-all sm:my-8 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-[#fecaca] bg-[#fef2f2] text-[#b91c1c]">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 id="custom-confirm-title" class="text-lg font-bold text-[#1d3c34]">Confirm Deletion</h3>
                <p id="custom-confirm-message" class="mt-2 text-sm leading-relaxed text-slate-600">Are you sure you want to delete this request?</p>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-center justify-end gap-3 border-t border-[#f0e7db] pt-4">
            <button type="button" id="custom-confirm-cancel" class="rounded-xl border border-[#d9cab3] bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-[#f8f3eb] focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                Cancel
            </button>
            <button type="button" id="custom-confirm-submit" class="rounded-xl bg-[#a24339] px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[#85352c] focus:outline-none focus:ring-2 focus:ring-[#f8ddd9]">
                Yes, Delete
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        let pendingForm = null;

        function openConfirmModal(message, form) {
            const modal = document.getElementById('custom-confirm-modal');
            if (!modal) return;
            const msgEl = document.getElementById('custom-confirm-message');
            if (msgEl && message) {
                msgEl.textContent = message;
            }
            pendingForm = form;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeConfirmModal() {
            const modal = document.getElementById('custom-confirm-modal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            pendingForm = null;
        }

        document.addEventListener('click', function (e) {
            const cancelBtn = e.target.closest('#custom-confirm-cancel');
            const backdrop = e.target.closest('#custom-confirm-backdrop');
            if (cancelBtn || backdrop) {
                e.preventDefault();
                closeConfirmModal();
                return;
            }

            const submitBtn = e.target.closest('#custom-confirm-submit');
            if (submitBtn && pendingForm) {
                e.preventDefault();
                const formToSubmit = pendingForm;
                closeConfirmModal();
                formToSubmit.dataset.confirmed = 'true';
                formToSubmit.submit();
                return;
            }

            const trigger = e.target.closest('[data-confirm]');
            if (trigger) {
                const form = trigger.tagName && trigger.tagName.toLowerCase() === 'form' ? trigger : trigger.closest('form');
                if (form && form.dataset.confirmed !== 'true') {
                    e.preventDefault();
                    e.stopPropagation();
                    const message = trigger.getAttribute('data-confirm');
                    openConfirmModal(message, form);
                }
            }
        });

        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (form && form.hasAttribute('data-confirm') && form.dataset.confirmed !== 'true') {
                e.preventDefault();
                const message = form.getAttribute('data-confirm');
                openConfirmModal(message, form);
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeConfirmModal();
            }
        });
    })();
</script>
