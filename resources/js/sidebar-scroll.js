/**
 * Preserves sidebar scroll position across navigation, section changes, and dynamic updates.
 * Prevents scroll jumping by isolating the sidebar scroll container and restoring scrollTop.
 */
export function initSidebarScrollManager() {
    const getSidebar = () => document.querySelector('aside.dashboard-sidebar, aside.sidebar-scroll-area, aside');
    const storageKey = 'gtm_sidebar_scroll_' + (window.location.pathname || 'dashboard');

    const restore = (sidebar) => {
        if (!sidebar) return;
        const saved = sessionStorage.getItem(storageKey);
        if (saved !== null) {
            const targetTop = parseInt(saved, 10);
            if (!isNaN(targetTop)) {
                sidebar.scrollTop = targetTop;
                requestAnimationFrame(() => {
                    sidebar.scrollTop = targetTop;
                });
            }
        }
    };

    const save = (sidebar) => {
        if (!sidebar) return;
        sessionStorage.setItem(storageKey, sidebar.scrollTop);
    };

    const sidebar = getSidebar();
    if (!sidebar) return;

    // Guarantee sticky 100dvh scroll area
    sidebar.classList.add('dashboard-sidebar', 'sidebar-scroll-area');
    sidebar.style.position = 'sticky';
    sidebar.style.top = '0';
    sidebar.style.height = '100dvh';
    sidebar.style.maxHeight = '100dvh';
    sidebar.style.overflowY = 'auto';

    restore(sidebar);

    sidebar.addEventListener('scroll', () => {
        save(sidebar);
    }, { passive: true });

    sidebar.addEventListener('click', (e) => {
        const link = e.target.closest('a, button, [data-target-section]');
        if (!link) return;

        const currentTop = sidebar.scrollTop;
        save(sidebar);

        const lockScroll = () => {
            sidebar.scrollTop = currentTop;
        };

        lockScroll();
        requestAnimationFrame(lockScroll);
        setTimeout(lockScroll, 10);
        setTimeout(lockScroll, 50);
        setTimeout(lockScroll, 150);
    }, true);

    window.addEventListener('pageshow', () => {
        restore(getSidebar());
    });
}
