import { initCarousels } from './carousel';
import { initPropertyGalleryAndSidebar } from './property-gallery';
import { initCurrencyEngine } from './currency';
import { initLanguageEngine } from './language';
import { initMortgageCalculator } from './mortgage-calculator';
import { initLocationPickerMaps, initPropertyMaps } from './property-map';
import { initPropertyInteractions } from './property-interactions';
import { initCompareMatrix } from './compare-matrix';
import { initGuestGate } from './guest-gate';
import { initSidebarScrollManager } from './sidebar-scroll';

let isAppInitialized = false;

function initAll() {
    if (isAppInitialized) {
        return;
    }
    isAppInitialized = true;

    try { initSidebarScrollManager(); } catch (e) { console.error('Sidebar scroll init error:', e); }
    try { initGuestGate(); } catch (e) { console.error('Guest gate init error:', e); }
    try { initCarousels(); } catch (e) { console.error('Carousel init error:', e); }
    try { initPropertyGalleryAndSidebar(); } catch (e) { console.error('Gallery init error:', e); }
    try { initCurrencyEngine(); } catch (e) { console.error('Currency init error:', e); }
    try { initLanguageEngine(); } catch (e) { console.error('Language init error:', e); }
    try { initMortgageCalculator(); } catch (e) { console.error('Mortgage calc init error:', e); }
    try { initPropertyMaps(); } catch (e) { console.error('Property maps init error:', e); }
    try { initLocationPickerMaps(); } catch (e) { console.error('Location picker init error:', e); }
    try { initCompareMatrix(); } catch (e) { console.error('Compare matrix init error:', e); }
    try { initPropertyInteractions(); } catch (e) { console.error('Property interactions init error:', e); }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
} else {
    initAll();
}
window.addEventListener('load', initAll);
if (typeof window !== 'undefined') {
    window.initPropertyMaps = initPropertyMaps;
    window.initLocationPickerMaps = initLocationPickerMaps;
    window.initCompareMatrix = initCompareMatrix;
    window.initSidebarScrollManager = initSidebarScrollManager;
}

export {
    initCarousels,
    initPropertyGalleryAndSidebar,
    initCurrencyEngine,
    initLanguageEngine,
    initMortgageCalculator,
    initLocationPickerMaps,
    initPropertyMaps,
    initPropertyInteractions,
    initCompareMatrix,
    initGuestGate,
    initSidebarScrollManager,
};



