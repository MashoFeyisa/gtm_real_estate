/**
 * Real Estate Luxury Carousel Engine
 * High-performance, zero-dependency, touch & drag responsive slider
 */
export function initCarousels() {
    const carousels = document.querySelectorAll('[data-carousel]');
    if (!carousels.length) return;

    carousels.forEach((carousel) => {
        const name = carousel.getAttribute('data-carousel');
        const track = carousel.querySelector('[data-carousel-track]');
        if (!track) return;

        const prevBtn = document.querySelector(`[data-carousel-prev="${name}"]`);
        const nextBtn = document.querySelector(`[data-carousel-next="${name}"]`);
        const navContainer = document.querySelector(`[data-carousel-nav="${name}"]`);
        const dotsContainer = document.querySelector(`[data-carousel-dots="${name}"]`);

        const slides = Array.from(track.querySelectorAll('[data-carousel-slide]'));
        if (slides.length === 0) return;

        let dots = [];
        let isDragging = false;
        let startX = 0;
        let startScrollLeft = 0;
        let hasMoved = false;

        function checkOverflow() {
            const hasMore = track.scrollWidth > track.clientWidth + 8;

            if (navContainer) {
                if (slides.length > 1) {
                    navContainer.classList.remove('hidden');
                    navContainer.classList.add('flex');
                } else {
                    navContainer.classList.add('hidden');
                    navContainer.classList.remove('flex');
                }
            }

            if (dotsContainer) {
                if (hasMore || (slides.length > 1 && window.innerWidth < 1024)) {
                    dotsContainer.classList.remove('hidden');
                    dotsContainer.classList.add('flex');
                } else {
                    dotsContainer.classList.add('hidden');
                    dotsContainer.classList.remove('flex');
                }
            }

            return hasMore;
        }

        function createDots() {
            if (!dotsContainer) return;
            dotsContainer.innerHTML = '';
            dots = [];

            slides.forEach((slide, index) => {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.setAttribute('aria-label', `Slide ${index + 1}`);
                dot.className = index === 0
                    ? 'h-2.5 w-7 rounded-full bg-[#102b25] transition-all duration-300 cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#9b6c17]'
                    : 'h-2.5 w-2.5 rounded-full bg-[#d9cab3] hover:bg-[#b38e46] transition-all duration-300 cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#9b6c17]';

                dot.addEventListener('click', (e) => {
                    e.preventDefault();
                    slide.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
                });

                dotsContainer.appendChild(dot);
                dots.push(dot);
            });
        }

        function updateState() {
            const hasMore = checkOverflow();
            if (!hasMore) {
                if (prevBtn) prevBtn.disabled = true;
                if (nextBtn) nextBtn.disabled = true;
                return;
            }

            const scrollLeft = track.scrollLeft;
            const maxScrollLeft = track.scrollWidth - track.clientWidth - 6;

            if (prevBtn) {
                prevBtn.disabled = scrollLeft <= 4;
            }
            if (nextBtn) {
                nextBtn.disabled = scrollLeft >= maxScrollLeft;
            }

            // Determine active slide
            let activeIndex = 0;
            let minDiff = Infinity;
            const trackOffset = track.getBoundingClientRect().left;

            slides.forEach((slide, idx) => {
                const diff = Math.abs(slide.getBoundingClientRect().left - trackOffset);
                if (diff < minDiff) {
                    minDiff = diff;
                    activeIndex = idx;
                }
            });

            // Update dots
            dots.forEach((dot, idx) => {
                if (idx === activeIndex) {
                    dot.className = 'h-2.5 w-7 rounded-full bg-[#102b25] transition-all duration-300 cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#9b6c17]';
                } else {
                    dot.className = 'h-2.5 w-2.5 rounded-full bg-[#d9cab3] hover:bg-[#b38e46] transition-all duration-300 cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#9b6c17]';
                }
            });
        }

        function scrollByDirection(direction) {
            const firstSlide = slides[0];
            const slideWidth = firstSlide ? firstSlide.offsetWidth : 320;
            const gap = 24; // 1.5rem (gap-6)
            const step = slideWidth + gap;

            track.scrollBy({
                left: direction * step,
                behavior: 'smooth'
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                scrollByDirection(-1);
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                scrollByDirection(1);
            });
        }

        // Debounced scroll listener
        let ticking = false;
        track.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    updateState();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        // Pointer / Drag events for desktop smooth dragging
        track.addEventListener('pointerdown', (e) => {
            if (e.button !== 0) return; // Only primary mouse button
            isDragging = true;
            hasMoved = false;
            startX = e.pageX - track.offsetLeft;
            startScrollLeft = track.scrollLeft;
            track.classList.add('cursor-grabbing');
        });

        window.addEventListener('pointermove', (e) => {
            if (!isDragging) return;
            const x = e.pageX - track.offsetLeft;
            const walk = x - startX;
            if (Math.abs(walk) > 6) {
                hasMoved = true;
            }
            track.scrollLeft = startScrollLeft - walk;
        });

        const stopDragging = () => {
            if (!isDragging) return;
            isDragging = false;
            track.classList.remove('cursor-grabbing');
        };

        window.addEventListener('pointerup', stopDragging);
        window.addEventListener('pointercancel', stopDragging);

        // Prevent unintentional clicks when dragging
        track.addEventListener('click', (e) => {
            if (hasMoved) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);

        // Keyboard navigation
        carousel.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                scrollByDirection(-1);
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                scrollByDirection(1);
            }
        });

        createDots();
        updateState();

        // Optional carousel autoplay (if data-carousel-autoplay or data-carousel-interval specified)
        const autoPlayAttr = carousel.getAttribute('data-carousel-autoplay');
        const intervalAttr = carousel.getAttribute('data-carousel-interval');
        if (autoPlayAttr !== null || intervalAttr !== null) {
            const carouselInterval = parseInt(intervalAttr || '2000', 10);
            let carouselTimer = null;
            let isCarouselHovered = false;

            function startCarouselAutoPlay() {
                if (carouselTimer) clearInterval(carouselTimer);
                carouselTimer = setInterval(() => {
                    if (!isCarouselHovered && !isDragging && document.visibilityState === 'visible') {
                        const maxScroll = track.scrollWidth - track.clientWidth - 8;
                        if (track.scrollLeft >= maxScroll) {
                            track.scrollTo({ left: 0, behavior: 'smooth' });
                        } else {
                            scrollByDirection(1);
                        }
                    }
                }, carouselInterval);
            }

            function stopCarouselAutoPlay() {
                if (carouselTimer) {
                    clearInterval(carouselTimer);
                    carouselTimer = null;
                }
            }

            carousel.addEventListener('mouseenter', () => {
                isCarouselHovered = true;
                stopCarouselAutoPlay();
            });

            carousel.addEventListener('mouseleave', () => {
                isCarouselHovered = false;
                startCarouselAutoPlay();
            });

            startCarouselAutoPlay();
        }

        // Responsive observer
        if (window.ResizeObserver) {
            const ro = new ResizeObserver(() => {
                updateState();
            });
            ro.observe(track);
        } else {
            window.addEventListener('resize', updateState);
        }
    });
}
