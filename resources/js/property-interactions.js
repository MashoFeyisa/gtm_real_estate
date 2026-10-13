/**
 * Property Like & Favorite Interaction Handler
 *
 * Handles AJAX toggle of likes and favorites on property cards.
 * For unauthenticated users it opens the auth-required modal.
 */

/**
 * @param {HTMLElement} group
 */
function initInteractionGroup(group) {
    const isAuthenticated = group.dataset.isAuthenticated === 'true';
    const likeUrl = group.dataset.likeUrl;
    const favoriteUrl = group.dataset.favoriteUrl;
    const registerUrl = group.dataset.registerUrl;
    const csrf = group.dataset.csrf;
    const pageUrl = group.dataset.pageUrl;

    const likeBtn = group.querySelector('.like-btn');
    const favoriteBtn = group.querySelector('.favorite-btn');

    /**
     * @param {string} url
     * @param {HTMLElement} btn
     * @param {'like'|'favorite'} type
     */
    async function toggle(url, btn, type) {
        if (!isAuthenticated) {
            const destination = new URL(registerUrl, window.location.origin);
            destination.searchParams.set('redirect', pageUrl);
            window.location.assign(destination.toString());
            return;
        }

        btn.disabled = true;
        btn.style.opacity = '0.6';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Request failed');
            }

            const data = await response.json();

            if (type === 'like') {
                const icon = btn.querySelector('.like-icon');
                const countEl = btn.querySelector('.like-count');
                const isActive = data.liked;

                btn.classList.toggle('bg-rose-50', isActive);
                btn.classList.toggle('border-rose-300', isActive);
                btn.classList.toggle('text-rose-600', isActive);
                btn.classList.toggle('bg-white/80', !isActive);
                btn.classList.toggle('border-slate-200', !isActive);
                btn.classList.toggle('text-slate-500', !isActive);

                if (icon) { icon.setAttribute('fill', isActive ? 'currentColor' : 'none'); }
                if (countEl) { countEl.textContent = data.likes_count > 0 ? data.likes_count : ''; }
                btn.title = isActive ? 'Unlike this property' : 'Like this property';
            } else {
                const icon = btn.querySelector('.favorite-icon');
                const countEl = btn.querySelector('.favorite-count');
                const isActive = data.favorited;

                btn.classList.toggle('bg-amber-50', isActive);
                btn.classList.toggle('border-amber-300', isActive);
                btn.classList.toggle('text-amber-600', isActive);
                btn.classList.toggle('bg-white/80', !isActive);
                btn.classList.toggle('border-slate-200', !isActive);
                btn.classList.toggle('text-slate-500', !isActive);

                if (icon) { icon.setAttribute('fill', isActive ? 'currentColor' : 'none'); }
                if (countEl) { countEl.textContent = data.favorites_count > 0 ? data.favorites_count : ''; }
                btn.title = isActive ? 'Remove from favorites' : 'Save to favorites';
            }
        } catch (err) {
            console.error('Property interaction failed:', err);
        } finally {
            btn.disabled = false;
            btn.style.opacity = '';
        }
    }

    if (likeBtn) {
        likeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggle(likeUrl, likeBtn, 'like');
        });
    }

    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggle(favoriteUrl, favoriteBtn, 'favorite');
        });
    }
}

export function initPropertyComparisons() {
    function showComparisonToast(message) {
        let toast = document.getElementById('global-comparison-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'global-comparison-toast';
            toast.className = 'fixed bottom-5 right-5 z-[9999] flex items-center gap-2 rounded-xl bg-[#102b25] text-[#e7d8b7] px-4 py-3 shadow-2xl border border-[#d4af37]/40 text-sm font-semibold transition-all duration-300 opacity-0 translate-y-2 pointer-events-none';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<svg class="h-4 w-4 text-[#d4af37] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg> <span>${message}</span>`;
        toast.classList.remove('opacity-0', 'translate-y-2');
        toast.classList.add('opacity-100', 'translate-y-0');
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.classList.remove('opacity-100', 'translate-y-0');
            toast.classList.add('opacity-0', 'translate-y-2');
        }, 3000);
    }

    document.querySelectorAll('.compare-toggle-form').forEach((form) => {
        if (form.dataset.compareBound === 'true' || form._compareBound) {
            return;
        }
        form.dataset.compareBound = 'true';
        form._compareBound = true;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const btn = form.querySelector('.compare-toggle-btn');
            const token = form.querySelector('input[name="_token"]')?.value;
            if (!btn || !token) return;

            btn.disabled = true;
            btn.style.opacity = '0.6';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) throw new Error('Compare request failed');

                const data = await response.json();
                if (data.success) {
                    const navBtn = document.getElementById('compare-matrix-nav-btn');
                    if (navBtn) {
                        if (data.count > 0) {
                            navBtn.classList.remove('hidden');
                        } else {
                            navBtn.classList.add('hidden');
                        }
                    }

                    document.querySelectorAll('.compare-matrix-counter').forEach((cnt) => {
                        cnt.textContent = `Compare Matrix (${data.count})`;
                    });

                    // Update all compare matrix links with query param ids
                    const compareUrl = data.ids && data.ids.length > 0 
                        ? `/properties/compare?ids=${data.ids.join(',')}` 
                        : '/properties/compare';
                    document.querySelectorAll('.compare-matrix-link, a[href*="/properties/compare"]').forEach((link) => {
                        link.href = compareUrl;
                    });

                    // Sync Floating Compare Matrix Bar
                    const floatBar = document.getElementById('floating-compare-bar');
                    if (floatBar) {
                        if (data.count > 0) {
                            floatBar.classList.remove('opacity-0', 'translate-y-8', 'pointer-events-none');
                            floatBar.classList.add('opacity-100', 'translate-y-0');
                        } else {
                            floatBar.classList.remove('opacity-100', 'translate-y-0');
                            floatBar.classList.add('opacity-0', 'translate-y-8', 'pointer-events-none');
                        }
                    }

                    // Sync Global Header Nav Button
                    document.querySelectorAll('.global-compare-header-btn').forEach((gBtn) => {
                        if (data.count > 0) {
                            gBtn.classList.remove('hidden');
                        } else {
                            gBtn.classList.add('hidden');
                        }
                    });

                    const activeIds = (data.ids || []).map(Number);
                    document.querySelectorAll('.compare-toggle-form').forEach((f) => {
                        const pid = Number(f.dataset.propertyId);
                        const fBtn = f.querySelector('.compare-toggle-btn');
                        const fText = f.querySelector('.compare-btn-text');
                        if (!fBtn) return;

                        const isCompared = activeIds.includes(pid);
                        fBtn.setAttribute('data-compared', isCompared ? '1' : '0');

                        if (fBtn.classList.contains('compare-img-btn')) {
                            if (isCompared) {
                                fBtn.classList.remove('bg-[#102b25]/85', 'text-[#fdf8f0]', 'border-white/30', 'border-white/25');
                                fBtn.classList.add('bg-[#d4af37]', 'text-[#102b25]', 'border-[#d4af37]', 'ring-2', 'ring-[#d4af37]/40', 'font-black');
                                fBtn.title = 'Remove from comparison matrix';
                                if (fText) fText.textContent = 'Compared';
                            } else {
                                fBtn.classList.remove('bg-[#d4af37]', 'text-[#102b25]', 'border-[#d4af37]', 'ring-2', 'ring-[#d4af37]/40', 'font-black');
                                fBtn.classList.add('bg-[#102b25]/85', 'text-[#fdf8f0]', 'border-white/30');
                                fBtn.title = 'Compare side by side';
                                if (fText) fText.textContent = 'Compare';
                            }
                        }

                        if (fBtn.classList.contains('compare-card-btn')) {
                            if (isCompared) {
                                fBtn.classList.remove('bg-[#1d3c34]', 'text-white', 'hover:bg-[#264d41]');
                                fBtn.classList.add('bg-[#d4af37]', 'text-[#102b25]');
                                fBtn.title = 'Remove from comparison';
                                if (fText) fText.textContent = 'Compared';
                            } else {
                                fBtn.classList.remove('bg-[#d4af37]', 'text-[#102b25]');
                                fBtn.classList.add('bg-[#1d3c34]', 'text-white', 'hover:bg-[#264d41]');
                                fBtn.title = 'Compare side by side';
                                if (fText) fText.textContent = 'Compare';
                            }
                        }

                        if (fBtn.classList.contains('compare-detail-btn')) {
                            if (isCompared) {
                                fBtn.classList.remove('border-[#102b25]', 'bg-[#102b25]', 'text-white', 'hover:bg-[#1d3c34]', 'hover:border-[#1d3c34]', 'hover:text-[#d4af37]');
                                fBtn.classList.add('bg-[#d4af37]', 'border-[#d4af37]', 'text-[#102b25]', 'font-black');
                                fBtn.title = 'Remove from comparison';
                                if (fText) fText.textContent = 'Compared';
                            } else {
                                fBtn.classList.remove('bg-[#d4af37]', 'border-[#d4af37]', 'text-[#102b25]', 'font-black');
                                fBtn.classList.add('border-[#102b25]', 'bg-[#102b25]', 'text-white');
                                fBtn.title = 'Compare side by side';
                                if (fText) fText.textContent = 'Compare';
                            }
                        }
                    });

                    showComparisonToast(data.message || (data.action === 'added' ? 'Added to comparison matrix' : 'Removed from comparison matrix'));
                }
            } catch (err) {
                console.error('Compare toggle failed:', err);
            } finally {
                btn.disabled = false;
                btn.style.opacity = '';
            }
        });
    });
}

export function initPropertyInteractions() {
    document.querySelectorAll('.property-interaction-group').forEach(initInteractionGroup);
    initPropertyComparisons();
}
