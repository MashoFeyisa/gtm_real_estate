/**
 * Property Multi-Image Card Slider & Offcanvas Preview Drawer Engine
 * High-performance, zero-dependency, touch-enabled with 60fps transitions
 */

export function initPropertyGalleryAndSidebar() {
    // -------------------------------------------------------------
    // 1. PROPERTY CARDS MULTI-IMAGE SLIDER
    // -------------------------------------------------------------
    const cardSliders = document.querySelectorAll('[data-property-slider]');
    cardSliders.forEach((slider, sliderIdx) => {
        const slides = Array.from(slider.querySelectorAll('[data-property-slide]'));
        if (slides.length <= 1) return;

        const prevBtn = slider.querySelector('[data-slider-prev]');
        const nextBtn = slider.querySelector('[data-slider-next]');
        const counter = slider.querySelector('[data-slider-counter]');
        const dots = Array.from(slider.querySelectorAll('[data-slider-dot]'));

        let activeIndex = 0;

        function updateSlider(index) {
            activeIndex = (index + slides.length) % slides.length;

            slides.forEach((slide, idx) => {
                if (idx === activeIndex) {
                    slide.classList.remove('opacity-0', 'pointer-events-none', 'scale-105');
                    slide.classList.add('opacity-100', 'pointer-events-auto', 'scale-100');
                } else {
                    slide.classList.remove('opacity-100', 'pointer-events-auto', 'scale-100');
                    slide.classList.add('opacity-0', 'pointer-events-none', 'scale-105');
                }
            });

            if (counter) {
                counter.textContent = `${activeIndex + 1} / ${slides.length}`;
            }

            dots.forEach((dot, idx) => {
                const isThumb = dot.querySelector('img') !== null;
                if (isThumb) {
                    if (idx === activeIndex) {
                        dot.className = 'relative h-12 w-16 shrink-0 rounded-lg overflow-hidden border-2 border-[#d4af37] ring-1 ring-[#d4af37] scale-105 transition-all duration-300 cursor-pointer';
                    } else {
                        dot.className = 'relative h-12 w-16 shrink-0 rounded-lg overflow-hidden border border-white/20 opacity-70 hover:opacity-100 transition-all duration-300 cursor-pointer';
                    }
                } else {
                    if (idx === activeIndex) {
                        dot.className = 'h-1.5 w-4 rounded-full bg-[#d4af37] transition-all duration-300';
                    } else {
                        dot.className = 'h-1.5 w-1.5 rounded-full bg-white/60 hover:bg-white transition-all duration-300';
                    }
                }
            });
        }

        // Automatic 2-second timing transition
        const intervalAttr = slider.getAttribute('data-slider-interval');
        const autoPlayInterval = intervalAttr ? parseInt(intervalAttr, 10) : 2000;
        let autoPlayTimer = null;
        let isHovered = false;
        let isInteracting = false;
        let isVisibleInViewport = true;

        function startAutoPlay() {
            if (autoPlayTimer) clearInterval(autoPlayTimer);
            if (slides.length <= 1) return;
            autoPlayTimer = setInterval(() => {
                if (!isHovered && !isInteracting && isVisibleInViewport && document.visibilityState === 'visible') {
                    updateSlider(activeIndex + 1);
                }
            }, autoPlayInterval);
        }

        function stopAutoPlay() {
            if (autoPlayTimer) {
                clearInterval(autoPlayTimer);
                autoPlayTimer = null;
            }
        }

        function restartAutoPlay() {
            stopAutoPlay();
            startAutoPlay();
        }

        // Natural stagger between visible cards on initial render
        const initialDelay = (sliderIdx % 4) * 200;
        setTimeout(() => {
            startAutoPlay();
        }, initialDelay);

        // Pause on mouse hover and resume when pointer leaves
        slider.addEventListener('mouseenter', () => {
            isHovered = true;
            stopAutoPlay();
        });

        slider.addEventListener('mouseleave', () => {
            isHovered = false;
            startAutoPlay();
        });

        // Touch swipe support for card images
        let touchStartX = 0;
        slider.addEventListener('touchstart', (e) => {
            isInteracting = true;
            stopAutoPlay();
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        slider.addEventListener('touchend', (e) => {
            const touchEndX = e.changedTouches[0].screenX;
            const diffX = touchEndX - touchStartX;
            if (Math.abs(diffX) > 40) {
                if (diffX < 0) {
                    updateSlider(activeIndex + 1);
                } else {
                    updateSlider(activeIndex - 1);
                }
            }
            setTimeout(() => {
                isInteracting = false;
                startAutoPlay();
            }, 1000);
        }, { passive: true });

        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                updateSlider(activeIndex - 1);
                restartAutoPlay();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                updateSlider(activeIndex + 1);
                restartAutoPlay();
            });
        }

        dots.forEach((dot, idx) => {
            dot.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                updateSlider(idx);
                restartAutoPlay();
            });
        });

        // Pause timer when slider is off-screen to preserve resources
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    isVisibleInViewport = entry.isIntersecting;
                    if (entry.isIntersecting) {
                        startAutoPlay();
                    } else {
                        stopAutoPlay();
                    }
                });
            }, { threshold: 0.1 });
            observer.observe(slider);
        }

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopAutoPlay();
            } else if (isVisibleInViewport) {
                startAutoPlay();
            }
        });
    });

    // -------------------------------------------------------------
    // 2. LUXURY SLIDE-IN SIDEBAR DRAWER
    // -------------------------------------------------------------
    const backdrop = document.getElementById('property-sidebar-backdrop');
    const drawer = document.getElementById('property-sidebar-drawer');
    const closeBtn = document.getElementById('close-property-sidebar');

    if (!drawer || !backdrop) return;

    // Elements inside sidebar
    const titleEl = document.getElementById('sidebar-property-title');
    const badgeEl = document.getElementById('sidebar-property-badge');
    const typeEl = document.getElementById('sidebar-property-type');
    const priceEl = document.getElementById('sidebar-property-price');
    const priceLabelEl = document.getElementById('sidebar-price-label');
    const addressEl = document.getElementById('sidebar-property-address');
    const cityEl = document.getElementById('sidebar-property-city');
    const bedroomsEl = document.getElementById('sidebar-property-bedrooms');
    const bathroomsEl = document.getElementById('sidebar-property-bathrooms');
    const areaEl = document.getElementById('sidebar-property-area');
    const descEl = document.getElementById('sidebar-property-description');
    const agentPhotoEl = document.getElementById('sidebar-agent-photo');
    const agentNameEl = document.getElementById('sidebar-agent-name');
    const agentPhoneEl = document.getElementById('sidebar-agent-phone');
    const agentCallBtn = document.getElementById('sidebar-agent-call-btn');
    const agentWhatsappBtn = document.getElementById('sidebar-agent-whatsapp-btn');
    const fullDetailsLink = document.getElementById('sidebar-full-details-link');
    const buyRequestBtn = document.getElementById('sidebar-buy-request-btn');

    // Sidebar Gallery Slider
    const slidesContainer = document.getElementById('sidebar-gallery-slides');
    const thumbnailsContainer = document.getElementById('sidebar-thumbnails-container');
    const sliderPrevBtn = document.getElementById('sidebar-slider-prev');
    const sliderNextBtn = document.getElementById('sidebar-slider-next');
    const photoCounterEl = document.getElementById('sidebar-photo-counter');
    const sliderControls = document.getElementById('sidebar-slider-controls');

    let currentImages = [];
    let currentImageIndex = 0;
    let isOpen = false;
    let sidebarAutoPlayTimer = null;
    let isSidebarHovered = false;

    function startSidebarAutoPlay() {
        if (sidebarAutoPlayTimer) clearInterval(sidebarAutoPlayTimer);
        if (!isOpen || currentImages.length <= 1) return;
        sidebarAutoPlayTimer = setInterval(() => {
            if (!isSidebarHovered && isOpen && document.visibilityState === 'visible') {
                setSidebarSlide(currentImageIndex + 1);
            }
        }, 2000); // 2-second timing transition
    }

    function stopSidebarAutoPlay() {
        if (sidebarAutoPlayTimer) {
            clearInterval(sidebarAutoPlayTimer);
            sidebarAutoPlayTimer = null;
        }
    }

    function restartSidebarAutoPlay() {
        stopSidebarAutoPlay();
        startSidebarAutoPlay();
    }

    function renderSidebarGallery(images, propertyTitle) {
        currentImages = images && images.length ? images : ['/images/luxury/hero-skyline.jpg'];
        currentImageIndex = 0;

        slidesContainer.innerHTML = '';
        thumbnailsContainer.innerHTML = '';

        if (currentImages.length <= 1) {
            if (sliderControls) sliderControls.classList.add('hidden');
            if (thumbnailsContainer) thumbnailsContainer.classList.add('hidden');
            stopSidebarAutoPlay();
        } else {
            if (sliderControls) sliderControls.classList.remove('hidden');
            if (thumbnailsContainer) thumbnailsContainer.classList.remove('hidden');
            if (isOpen) startSidebarAutoPlay();
        }

        currentImages.forEach((imgUrl, idx) => {
            // Main Slide
            const slide = document.createElement('div');
            slide.className = `absolute inset-0 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] ${
                idx === 0 ? 'opacity-100 scale-100 z-10' : 'opacity-0 scale-105 pointer-events-none z-0'
            }`;
            slide.setAttribute('data-sidebar-slide-index', idx);
            slide.innerHTML = `<img src="${imgUrl}" alt="${propertyTitle} - Photo ${idx + 1}" class="h-full w-full object-cover">`;
            slidesContainer.appendChild(slide);

            // Thumbnail
            if (currentImages.length > 1) {
                const thumb = document.createElement('button');
                thumb.type = 'button';
                thumb.className = `relative h-14 w-20 shrink-0 rounded-xl overflow-hidden border-2 transition-all duration-200 cursor-pointer ${
                    idx === 0 ? 'border-[#d4af37] ring-2 ring-[#d4af37]/40 scale-105' : 'border-white/20 opacity-70 hover:opacity-100 hover:border-white/50'
                }`;
                thumb.innerHTML = `<img src="${imgUrl}" alt="Thumbnail ${idx + 1}" class="h-full w-full object-cover">`;
                thumb.addEventListener('click', (e) => {
                    e.preventDefault();
                    setSidebarSlide(idx);
                    restartSidebarAutoPlay();
                });
                thumbnailsContainer.appendChild(thumb);
            }
        });

        updateSidebarGalleryUI();
    }

    function setSidebarSlide(index) {
        if (!currentImages.length) return;
        currentImageIndex = (index + currentImages.length) % currentImages.length;

        const allSlides = slidesContainer.querySelectorAll('[data-sidebar-slide-index]');
        allSlides.forEach((slide, idx) => {
            if (idx === currentImageIndex) {
                slide.className = 'absolute inset-0 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] opacity-100 scale-100 z-10';
            } else {
                slide.className = 'absolute inset-0 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] opacity-0 scale-105 pointer-events-none z-0';
            }
        });

        const allThumbs = thumbnailsContainer.children;
        Array.from(allThumbs).forEach((thumb, idx) => {
            if (idx === currentImageIndex) {
                thumb.className = 'relative h-14 w-20 shrink-0 rounded-xl overflow-hidden border-2 border-[#d4af37] ring-2 ring-[#d4af37]/40 scale-105 transition-all duration-200 cursor-pointer';
                thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                thumb.className = 'relative h-14 w-20 shrink-0 rounded-xl overflow-hidden border-2 border-white/20 opacity-70 hover:opacity-100 hover:border-white/50 transition-all duration-200 cursor-pointer';
            }
        });

        updateSidebarGalleryUI();
    }

    function updateSidebarGalleryUI() {
        if (photoCounterEl) {
            photoCounterEl.textContent = `${currentImageIndex + 1} / ${currentImages.length}`;
        }
    }

    if (sliderPrevBtn) {
        sliderPrevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            setSidebarSlide(currentImageIndex - 1);
            restartSidebarAutoPlay();
        });
    }

    if (sliderNextBtn) {
        sliderNextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            setSidebarSlide(currentImageIndex + 1);
            restartSidebarAutoPlay();
        });
    }

    // Touch swipe for sidebar main image
    const viewport = document.getElementById('sidebar-gallery-viewport');
    if (viewport) {
        viewport.addEventListener('mouseenter', () => {
            isSidebarHovered = true;
            stopSidebarAutoPlay();
        });

        viewport.addEventListener('mouseleave', () => {
            isSidebarHovered = false;
            if (isOpen) startSidebarAutoPlay();
        });

        let touchStartX = 0;
        viewport.addEventListener('touchstart', (e) => {
            stopSidebarAutoPlay();
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        viewport.addEventListener('touchend', (e) => {
            const touchEndX = e.changedTouches[0].screenX;
            const diffX = touchEndX - touchStartX;
            if (Math.abs(diffX) > 40) {
                if (diffX < 0) {
                    setSidebarSlide(currentImageIndex + 1);
                } else {
                    setSidebarSlide(currentImageIndex - 1);
                }
            }
            setTimeout(() => {
                if (isOpen) startSidebarAutoPlay();
            }, 1000);
        }, { passive: true });
    }

    // Open & populate drawer
    window.openPropertySidebar = function (data) {
        if (!data) return;

        if (titleEl) titleEl.textContent = data.title || 'Luxury Property';
        if (badgeEl) badgeEl.textContent = data.featured ? 'Featured Property' : (data.category ? data.category.toUpperCase() : 'EXCLUSIVE');
        if (typeEl) typeEl.textContent = data.type === 'rent' ? 'For Rent' : 'For Sale';
        if (priceLabelEl) priceLabelEl.textContent = data.type === 'rent' ? 'Rental Rate' : 'Asking Price';

        const formattedPrice = Number(data.price || 0).toLocaleString();
        if (priceEl) priceEl.textContent = `ETB ${formattedPrice}${data.type === 'rent' ? ' / mo' : ''}`;

        if (cityEl) cityEl.textContent = data.city || 'Prime District';
        if (addressEl) addressEl.textContent = `${data.city || 'Addis Ababa'} · ${data.address || 'Central Prime District'}`;
        if (bedroomsEl) bedroomsEl.textContent = data.bedrooms ?? 0;
        if (bathroomsEl) bathroomsEl.textContent = data.bathrooms ?? 0;
        if (areaEl) areaEl.innerHTML = `${Number(data.area ?? 0).toLocaleString()} <span class="text-xs font-semibold text-white/70">sqm</span>`;
        if (descEl) descEl.textContent = data.description || 'Prime real estate investment in a high-demand neighborhood featuring world-class craftsmanship and security.';

        // Agent section
        if (agentNameEl) agentNameEl.textContent = data.agentName || 'GTM Real Estate Central Desk';
        if (agentPhoneEl) agentPhoneEl.textContent = data.agentPhone || 'Direct Office Contact';
        if (agentPhotoEl) {
            agentPhotoEl.src = data.agentPhoto || '/images/default-avatar.svg';
        }

        if (agentCallBtn) {
            const cleanPhone = (data.agentPhone || '').replace(/[^0-9+]/g, '');
            if (cleanPhone) {
                agentCallBtn.href = `tel:${cleanPhone}`;
                agentCallBtn.classList.remove('hidden');
            } else {
                agentCallBtn.classList.add('hidden');
            }
        }

        if (agentWhatsappBtn) {
            if (data.whatsappUrl) {
                agentWhatsappBtn.href = data.whatsappUrl;
                agentWhatsappBtn.classList.remove('hidden');
            } else {
                agentWhatsappBtn.classList.add('hidden');
            }
        }

        // Links
        const showUrl = `/properties/${data.slug || ''}`;
        if (fullDetailsLink) fullDetailsLink.href = showUrl;
        if (buyRequestBtn) buyRequestBtn.href = `${showUrl}#buy-request`;

        // Parse images
        let images = [];
        if (Array.isArray(data.images)) {
            images = data.images;
        } else if (typeof data.images === 'string') {
            try {
                images = JSON.parse(data.images);
            } catch (_) {
                images = [data.images];
            }
        }

        renderSidebarGallery(images, data.title);

        // Animate Drawer In
        document.body.classList.add('overflow-hidden');
        backdrop.classList.remove('pointer-events-none');
        backdrop.classList.remove('opacity-0');
        backdrop.classList.add('opacity-100');

        drawer.classList.remove('translate-x-full');
        drawer.classList.add('translate-x-0');

        isOpen = true;
        if (currentImages.length > 1) {
            startSidebarAutoPlay();
        }
    };

    window.closePropertySidebar = function () {
        if (!isOpen) return;

        stopSidebarAutoPlay();

        drawer.classList.remove('translate-x-0');
        drawer.classList.add('translate-x-full');

        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
        backdrop.classList.add('pointer-events-none');

        document.body.classList.remove('overflow-hidden');
        isOpen = false;
    };

    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.closePropertySidebar();
        });
    }

    backdrop.addEventListener('click', () => {
        window.closePropertySidebar();
    });

    // Keyboard support: ESC to close, Left/Right for gallery
    window.addEventListener('keydown', (e) => {
        if (!isOpen) return;

        if (e.key === 'Escape') {
            e.preventDefault();
            window.closePropertySidebar();
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            setSidebarSlide(currentImageIndex - 1);
            restartSidebarAutoPlay();
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            setSidebarSlide(currentImageIndex + 1);
            restartSidebarAutoPlay();
        }
    });

    // -------------------------------------------------------------
    // 3. ATTACH QUICKVIEW BUTTONS ACROSS HOMEPAGE
    // -------------------------------------------------------------
    document.querySelectorAll('[data-quickview-btn]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const card = btn.closest('[data-property-card]') || btn;
            const dataAttr = card.getAttribute('data-property-json');
            if (dataAttr) {
                try {
                    const data = JSON.parse(dataAttr);
                    window.openPropertySidebar(data);
                } catch (err) {
                    console.error('Error parsing property data', err);
                }
            }
        });
    });
}
