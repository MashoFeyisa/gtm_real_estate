import { initCarousels } from './carousel';
import { initPropertyGalleryAndSidebar } from './property-gallery';

function initAll() {
    initCarousels();
    initPropertyGalleryAndSidebar();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
} else {
    initAll();
}

export { initCarousels, initPropertyGalleryAndSidebar };
