{{--
    Auth-Required Modal
    Shown when a guest tries to like or favorite a property.
    Provides sign-in and sign-up links.
--}}
<div id="auth-required-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center px-4" aria-modal="true" role="dialog">
    {{-- Backdrop --}}
    <div id="auth-modal-backdrop" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

    {{-- Modal Card --}}
    <div class="relative z-10 w-full max-w-md overflow-hidden rounded-[2rem] bg-white shadow-2xl ring-1 ring-[#d9cab3]">
        {{-- Header --}}
        <div class="bg-gradient-to-br from-[#1d3c34] to-[#2e5445] px-8 py-7 text-white">
            <div class="flex items-center gap-3">
                <span class="text-3xl" id="auth-modal-icon">❤️</span>
                <div>
                    <h2 class="text-xl font-black" id="auth-modal-title">Sign in to continue</h2>
                    <p class="mt-0.5 text-sm text-[#dfeee4]" id="auth-modal-subtitle">Create an account or sign in to like properties</p>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-8 py-7">
            <div class="space-y-3">
                <a href="{{ route('login') }}" id="auth-modal-login-link"
                   class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#1d3c34] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#264d41] active:scale-[0.98]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                    Sign In
                </a>
                <a href="{{ route('register') }}" id="auth-modal-register-link"
                   class="flex w-full items-center justify-center gap-2 rounded-xl border border-[#d9cab3] bg-[#fffaf2] px-5 py-3 text-sm font-bold text-[#1d3c34] transition hover:bg-[#f1e6d2] active:scale-[0.98]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                    </svg>
                    Create Free Account
                </a>
            </div>
            <button type="button" id="auth-modal-close"
                    class="mt-5 w-full rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-500 transition hover:text-[#1d3c34]">
                Maybe later
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('auth-required-modal');
        const backdrop = document.getElementById('auth-modal-backdrop');
        const closeBtn = document.getElementById('auth-modal-close');
        const modalIcon = document.getElementById('auth-modal-icon');
        const modalTitle = document.getElementById('auth-modal-title');
        const modalSubtitle = document.getElementById('auth-modal-subtitle');
        const loginLink = document.getElementById('auth-modal-login-link');
        const registerLink = document.getElementById('auth-modal-register-link');

        /**
         * Open the auth modal, optionally with a redirect URL and action context.
         *
         * @param {string} [redirectUrl]
         * @param {'like'|'favorite'} [action]
         */
        window.openAuthModal = function (redirectUrl, action) {
            if (action === 'favorite') {
                modalIcon.textContent = '🔖';
                modalTitle.textContent = 'Sign in to save favorites';
                modalSubtitle.textContent = 'Create an account or sign in to save properties';
            } else {
                modalIcon.textContent = '❤️';
                modalTitle.textContent = 'Sign in to like properties';
                modalSubtitle.textContent = 'Create an account or sign in to like properties';
            }

            if (redirectUrl) {
                const encoded = encodeURIComponent(redirectUrl);
                loginLink.href = '{{ route('login') }}?redirect=' + encoded;
                registerLink.href = '{{ route('register') }}?redirect=' + encoded;
            } else {
                loginLink.href = '{{ route('login') }}';
                registerLink.href = '{{ route('register') }}';
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        };

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        if (closeBtn) { closeBtn.addEventListener('click', closeModal); }
        if (backdrop) { backdrop.addEventListener('click', closeModal); }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    })();
</script>
