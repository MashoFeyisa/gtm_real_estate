/**
 * Unified Compare Matrix Handler
 *
 * Automatically marks and highlights property cards and images when clicking
 * the Compare button, synchronizing with the comparison matrix floating bar,
 * global header counters, and toast notifications.
 */

export function initCompareMatrix() {
    const checkIconSvg = `<svg class="h-3.5 w-3.5 text-[#102b25]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>`;
    const matrixIconSvg = `<svg class="h-3.5 w-3.5 text-[#d4af37]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>`;

    function showComparisonToast(message, compareUrl) {
        let toast = document.getElementById('comparison-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'comparison-toast';
            toast.className = 'fixed top-20 right-5 z-50 flex items-center gap-3 rounded-2xl border border-[#d4af37] bg-[#102b25] px-4 py-3 text-white shadow-2xl backdrop-blur-md transition-all duration-300 translate-y-[-20px] opacity-0 pointer-events-none';
            document.body.appendChild(toast);
        }

        toast.innerHTML = `
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[#d4af37] text-[#102b25] shadow-sm">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-white">${message}</p>
                ${compareUrl ? `<a href="${compareUrl}" class="text-[11px] font-extrabold text-[#d4af37] hover:underline">Open Compare Matrix &rarr;</a>` : ''}
            </div>
        `;

        toast.classList.remove('translate-y-[-20px]', 'opacity-0', 'pointer-events-none');
        toast.classList.add('translate-y-0', 'opacity-100');

        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-[-20px]', 'opacity-0', 'pointer-events-none');
        }, 3500);
    }

    // Attach to all compare toggle forms
    document.querySelectorAll('.compare-toggle-form').forEach(form => {
        if (form._compareBound || form.dataset.compareBound === 'true') return;
        form._compareBound = true;
        form.dataset.compareBound = 'true';

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            e.stopPropagation();

            const btn = form.querySelector('.compare-toggle-btn');
            const token = form.querySelector('input[name="_token"]')?.value;
            if (!btn || !token) return;

            btn.disabled = true;
            btn.style.opacity = '0.6';

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!res.ok) throw new Error('Network error');

                const data = await res.json();
                if (data.success) {
                    const activeIds = (data.ids || []).map(Number);
                    if (Array.isArray(data.ids)) {
                        try { localStorage.setItem('compared_property_ids', JSON.stringify(data.ids)); } catch (err) {}
                    }

                    // 1. Update all counter labels
                    document.querySelectorAll('.compare-matrix-counter').forEach(cnt => {
                        if (cnt.closest('#global-header-compare-btn') || cnt.closest('header')) {
                            cnt.textContent = `Compare (${data.count})`;
                        } else {
                            cnt.textContent = `Compare Matrix (${data.count})`;
                        }
                    });

                    // 2. Update all Compare Matrix link URLs
                    const compareBaseUrl = '/properties/compare';
                    const compareUrl = activeIds.length > 0 
                        ? `${compareBaseUrl}?ids=${activeIds.join(',')}` 
                        : compareBaseUrl;

                    document.querySelectorAll('.compare-matrix-link, a[href*="/properties/compare"]').forEach(link => {
                        link.href = compareUrl;
                    });

                    // 3. Sync Floating Bottom Bar
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

                    // 4. Sync Global Header Nav Button
                    document.querySelectorAll('.global-compare-header-btn').forEach(gBtn => {
                        if (data.count > 0) {
                            gBtn.classList.remove('hidden');
                        } else {
                            gBtn.classList.add('hidden');
                        }
                    });

                    // 5. Update every card and button on page
                    document.querySelectorAll('[data-property-id]').forEach(el => {
                        const pid = Number(el.dataset.propertyId);
                        if (!pid) return;
                        const isCompared = activeIds.includes(pid);

                        // Card frame highlighting
                        if (el.hasAttribute('data-property-card') || el.classList.contains('property-card')) {
                            if (isCompared) {
                                el.classList.add('is-marked-for-compare', 'ring-3', 'ring-[#d4af37]', 'border-[#d4af37]', 'shadow-xl');
                                el.classList.remove('border-[#d9cab3]');
                            } else {
                                el.classList.remove('is-marked-for-compare', 'ring-3', 'ring-[#d4af37]', 'border-[#d4af37]', 'shadow-xl');
                                el.classList.add('border-[#d9cab3]');
                            }
                        }

                        // Image badge marker
                        const marker = el.querySelector('.compare-matrix-marker-badge');
                        if (marker) {
                            if (isCompared) {
                                marker.classList.remove('opacity-0', 'scale-95', 'pointer-events-none', 'hidden');
                                marker.classList.add('opacity-100', 'scale-100');
                            } else {
                                marker.classList.remove('opacity-100', 'scale-100');
                                marker.classList.add('opacity-0', 'scale-95', 'pointer-events-none', 'hidden');
                            }
                        }

                        // Compare buttons inside this element or if el is the form
                        const fBtn = el.querySelector ? el.querySelector('.compare-toggle-btn') : null;
                        const fText = el.querySelector ? el.querySelector('.compare-btn-text') : null;
                        const fIconWrap = el.querySelector ? el.querySelector('.compare-icon-wrap') : null;

                        if (fBtn) {
                            fBtn.setAttribute('data-compared', isCompared ? '1' : '0');
                            if (fBtn.classList.contains('compare-img-btn')) {
                                if (isCompared) {
                                    fBtn.classList.remove('bg-[#102b25]/85', 'text-[#fdf8f0]', 'border-white/30', 'border-white/25');
                                    fBtn.classList.add('bg-[#d4af37]', 'text-[#102b25]', 'border-[#d4af37]', 'ring-2', 'ring-[#d4af37]/40', 'font-black');
                                    fBtn.title = 'Remove from comparison matrix';
                                    if (fText) fText.textContent = '✓ In Matrix';
                                    if (fIconWrap) fIconWrap.innerHTML = checkIconSvg;
                                } else {
                                    fBtn.classList.remove('bg-[#d4af37]', 'text-[#102b25]', 'border-[#d4af37]', 'ring-2', 'ring-[#d4af37]/40', 'font-black');
                                    fBtn.classList.add('bg-[#102b25]/85', 'text-[#fdf8f0]', 'border-white/25');
                                    fBtn.title = 'Compare side by side in matrix';
                                    if (fText) fText.textContent = 'Compare';
                                    if (fIconWrap) fIconWrap.innerHTML = matrixIconSvg;
                                }
                            } else if (fBtn.classList.contains('compare-detail-btn')) {
                                if (isCompared) {
                                    fBtn.classList.remove('border-[#102b25]', 'bg-[#102b25]', 'text-white', 'hover:bg-[#1d3c34]', 'hover:border-[#1d3c34]', 'hover:text-[#d4af37]');
                                    fBtn.classList.add('bg-[#d4af37]', 'border-[#d4af37]', 'text-[#102b25]', 'font-black');
                                    fBtn.title = 'Remove from comparison matrix';
                                    if (fText) fText.textContent = '✓ In Matrix';
                                } else {
                                    fBtn.classList.remove('bg-[#d4af37]', 'border-[#d4af37]', 'text-[#102b25]', 'font-black');
                                    fBtn.classList.add('border-[#102b25]', 'bg-[#102b25]', 'text-white');
                                    fBtn.title = 'Compare side by side in matrix';
                                    if (fText) fText.textContent = 'Compare';
                                }
                            } else {
                                if (isCompared) {
                                    fBtn.classList.remove('bg-[#1d3c34]', 'text-white', 'hover:bg-[#264d41]');
                                    fBtn.classList.add('bg-[#d4af37]', 'text-[#102b25]', 'font-black');
                                    fBtn.title = 'Remove from comparison matrix';
                                    if (fText) fText.textContent = '✓ In Matrix';
                                } else {
                                    fBtn.classList.remove('bg-[#d4af37]', 'text-[#102b25]', 'font-black');
                                    fBtn.classList.add('bg-[#1d3c34]', 'text-white', 'hover:bg-[#264d41]');
                                    fBtn.title = 'Compare side by side in matrix';
                                    if (fText) fText.textContent = 'Compare';
                                }
                            }
                        }
                    });

                    // Toast message
                    const toastText = data.action === 'added' 
                        ? `✓ Marked automatically for Compare Matrix (${data.count}/4)`
                        : `Removed from Compare Matrix (${data.count}/4)`;
                    showComparisonToast(toastText, compareUrl);
                }
            } catch (err) {
                console.error('Compare failed:', err);
            } finally {
                btn.disabled = false;
                btn.style.opacity = '';
            }
        });
    });
}
