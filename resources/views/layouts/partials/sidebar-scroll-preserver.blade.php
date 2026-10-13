<script id="sidebar-scroll-preserver">
    (function () {
        function getSidebar() {
            return document.querySelector('#admin-sidebar, #role-sidebar, aside.dashboard-sidebar, aside.sidebar-scroll-area, aside');
        }

        const pathKey = window.location.pathname || 'dashboard';
        const storageKey = 'gtm_sidebar_scroll_' + pathKey;
        const mainScrollKey = 'gtm_main_scroll_' + pathKey;

        function restoreSidebarScroll(sidebar) {
            if (!sidebar) return;
            const saved = sessionStorage.getItem(storageKey);
            if (saved !== null) {
                const targetTop = parseInt(saved, 10);
                if (!isNaN(targetTop)) {
                    sidebar.scrollTop = targetTop;
                    requestAnimationFrame(function () {
                        sidebar.scrollTop = targetTop;
                    });
                }
            }
        }

        function saveSidebarScroll(sidebar) {
            if (!sidebar) return;
            sessionStorage.setItem(storageKey, sidebar.scrollTop);
        }

        function restoreMainScroll() {
            const saved = sessionStorage.getItem(mainScrollKey);
            if (saved !== null) {
                const targetTop = parseInt(saved, 10);
                if (!isNaN(targetTop)) {
                    window.scrollTo({ top: targetTop, behavior: 'instant' });
                    requestAnimationFrame(function () {
                        window.scrollTo({ top: targetTop, behavior: 'instant' });
                    });
                    setTimeout(function () {
                        window.scrollTo({ top: targetTop, behavior: 'instant' });
                    }, 50);
                }
            }
        }

        function saveMainScroll() {
            const y = window.pageYOffset || document.documentElement.scrollTop || window.scrollY || 0;
            sessionStorage.setItem(mainScrollKey, y);
        }

        function initSidebarScrollPersistence() {
            const sidebar = getSidebar();
            if (!sidebar) return;

            sidebar.classList.add('dashboard-sidebar', 'sidebar-scroll-area');
            sidebar.style.position = 'sticky';
            sidebar.style.top = '0';
            sidebar.style.height = '100dvh';
            sidebar.style.maxHeight = '100dvh';
            sidebar.style.overflowY = 'auto';

            restoreSidebarScroll(sidebar);
            restoreMainScroll();

            sidebar.addEventListener('scroll', function () {
                saveSidebarScroll(sidebar);
            }, { passive: true });

            window.addEventListener('scroll', function () {
                saveMainScroll();
            }, { passive: true });

            sidebar.addEventListener('click', function (e) {
                const link = e.target.closest('a, button, [data-target-section]');
                if (!link) return;

                const topBefore = sidebar.scrollTop;
                saveSidebarScroll(sidebar);

                const keepPosition = function () {
                    sidebar.scrollTop = topBefore;
                };

                keepPosition();
                requestAnimationFrame(keepPosition);
                setTimeout(keepPosition, 10);
                setTimeout(keepPosition, 50);
                setTimeout(keepPosition, 150);
            }, true);
        }

        // Global Toast Notification Helper for Dashboards
        function showDashboardToast(message, type) {
            type = type || 'success';
            let container = document.getElementById('dashboard-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'dashboard-toast-container';
                container.style.cssText = 'position: fixed; top: 1.5rem; right: 1.5rem; z-index: 99999; display: flex; flex-direction: column; gap: 0.75rem; pointer-events: none; max-width: 28rem; width: calc(100vw - 3rem);';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = 'dashboard-toast shadow-2xl transition-all duration-300 transform translate-y-2 opacity-0';
            toast.style.cssText = 'pointer-events: auto; padding: 1rem 1.25rem; border-radius: 1rem; display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.875rem; font-weight: 600; line-height: 1.4;';

            if (type === 'success') {
                toast.style.backgroundColor = '#1d3c34';
                toast.style.color = '#ffffff';
                toast.style.border = '1px solid #d4af37';
                toast.innerHTML = `
                    <span style="font-size: 1.25rem; line-height: 1; color: #d4af37;">✓</span>
                    <div style="flex: 1;">${message}</div>
                    <button type="button" style="background: none; border: none; color: #dfeee4; cursor: pointer; font-size: 1rem; line-height: 1; padding: 0 0 0 0.5rem;" aria-label="Close">✕</button>
                `;
            } else {
                toast.style.backgroundColor = '#991b1b';
                toast.style.color = '#ffffff';
                toast.style.border = '1px solid #f87171';
                toast.innerHTML = `
                    <span style="font-size: 1.25rem; line-height: 1; color: #fecaca;">⚠️</span>
                    <div style="flex: 1;">${message}</div>
                    <button type="button" style="background: none; border: none; color: #fecaca; cursor: pointer; font-size: 1rem; line-height: 1; padding: 0 0 0 0.5rem;" aria-label="Close">✕</button>
                `;
            }

            const closeBtn = toast.querySelector('button');
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    toast.classList.add('opacity-0', 'translate-y-2');
                    setTimeout(() => toast.remove(), 300);
                });
            }

            container.appendChild(toast);
            requestAnimationFrame(function () {
                toast.classList.remove('opacity-0', 'translate-y-2');
            });

            setTimeout(function () {
                if (toast.parentNode) {
                    toast.classList.add('opacity-0', 'translate-y-2');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 6000);
        }

        window.showDashboardToast = showDashboardToast;

        // Universal AJAX / Fetch Form Submitter for All Dashboard Forms
        function initDashboardAjaxForms() {
            document.addEventListener('submit', async function (e) {
                const form = e.target;
                if (!form || form.tagName !== 'FORM') return;

                // Opt-out checks
                if (form.dataset.noAjax === 'true' || form.hasAttribute('data-no-ajax')) return;
                if (form.getAttribute('target') === '_blank') return;
                
                const method = (form.getAttribute('method') || 'GET').toUpperCase();
                if (method === 'GET') return; // Do not intercept search/filter GET forms

                const action = form.getAttribute('action') || window.location.href;
                if (action.includes('/logout') || action.includes('download')) return;

                e.preventDefault();

                const sidebar = getSidebar();
                const currentScrollY = window.pageYOffset || document.documentElement.scrollTop || window.scrollY || 0;
                const currentSidebarScroll = sidebar ? sidebar.scrollTop : 0;
                saveMainScroll();
                if (sidebar) saveSidebarScroll(sidebar);

                const activeSection = form.closest('.section-panel')?.dataset?.section || 
                                     form.closest('.section-panel')?.id || 
                                     window.location.hash.replace('#', '') || 
                                     'overview';

                // Submit button loading state
                const submitBtn = e.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
                let originalBtnText = '';
                if (submitBtn) {
                    originalBtnText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                }

                try {
                    const formData = new FormData(form);
                    if (e.submitter && e.submitter.name && !formData.has(e.submitter.name)) {
                        formData.append(e.submitter.name, e.submitter.value);
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                      formData.get('_token');

                    const response = await fetch(action, {
                        method: method,
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json, text/html, */*',
                            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
                        }
                    });

                    const contentType = response.headers.get('content-type') || '';

                    if (response.ok) {
                        let successMsg = 'Changes saved successfully.';
                        let targetSection = activeSection;

                        if (contentType.includes('application/json')) {
                            const data = await response.json();
                            successMsg = data.message || data.success || successMsg;
                            if (data.section) targetSection = data.section;
                        } else {
                            const html = await response.text();
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');

                            const flashEl = doc.querySelector('.session-success, [class*="bg-[#edf9ee]"], [class*="bg-emerald-"], .alert-success');
                            if (flashEl) {
                                successMsg = flashEl.textContent.trim();
                            }

                            // If not the site settings form, update the section content with newly returned HTML
                            if (!form.id || !form.id.includes('site-settings')) {
                                const newPanel = doc.querySelector(`[data-section="${targetSection}"], #${targetSection}.section-panel`);
                                const currPanel = document.querySelector(`[data-section="${targetSection}"], #${targetSection}.section-panel`);
                                if (newPanel && currPanel) {
                                    currPanel.innerHTML = newPanel.innerHTML;
                                }
                            }
                        }

                        // Close any parent modal on successful submit
                        const parentModal = form.closest('#quick-permissions-modal, [id$="-modal"]');
                        if (parentModal) {
                            parentModal.classList.add('hidden');
                        }

                        showDashboardToast(successMsg, 'success');

                        // Keep active section without anchor jump
                        if (typeof window.activateSection === 'function') {
                            window.activateSection(targetSection, true);
                        }

                        // Maintain both main content and sidebar scroll positions exactly
                        window.scrollTo({ top: currentScrollY, behavior: 'instant' });
                        requestAnimationFrame(function () {
                            window.scrollTo({ top: currentScrollY, behavior: 'instant' });
                            if (sidebar) sidebar.scrollTop = currentSidebarScroll;
                        });
                        setTimeout(function () {
                            window.scrollTo({ top: currentScrollY, behavior: 'instant' });
                            if (sidebar) sidebar.scrollTop = currentSidebarScroll;
                        }, 50);

                    } else if (response.status === 422) {
                        let errorMsg = 'Please fix the validation errors.';
                        if (contentType.includes('application/json')) {
                            const data = await response.json();
                            if (data.errors) {
                                errorMsg = Object.values(data.errors).flat().join('<br>');
                            } else if (data.message) {
                                errorMsg = data.message;
                            }
                        }
                        showDashboardToast(errorMsg, 'error');
                        window.scrollTo({ top: currentScrollY, behavior: 'instant' });
                    } else {
                        let errorMsg = 'An error occurred (' + response.status + '). Please try again.';
                        if (contentType.includes('application/json')) {
                            try {
                                const data = await response.json();
                                if (data.message) {
                                    errorMsg = data.message;
                                }
                            } catch (_) {}
                        } else if (response.status === 403) {
                            errorMsg = 'Access Denied (403): Permission denied.';
                        }
                        showDashboardToast(errorMsg, 'error');
                        window.scrollTo({ top: currentScrollY, behavior: 'instant' });
                    }
                } catch (err) {
                    console.error('Dashboard form AJAX error:', err);
                    showDashboardToast('Network error while saving. Please check your connection.', 'error');
                    window.scrollTo({ top: currentScrollY, behavior: 'instant' });
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                        submitBtn.innerHTML = originalBtnText;
                    }
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                initSidebarScrollPersistence();
                initDashboardAjaxForms();
            });
        } else {
            initSidebarScrollPersistence();
            initDashboardAjaxForms();
        }

        window.addEventListener('pageshow', function () {
            const sidebar = getSidebar();
            if (sidebar) restoreSidebarScroll(sidebar);
            restoreMainScroll();
        });
    })();
</script>
