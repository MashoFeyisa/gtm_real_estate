{{-- ========================================================================= --}}
{{-- LUXURY PROPERTY SIDEBAR DRAWER (Slide-over with Smooth Transitions)       --}}
{{-- ========================================================================= --}}
<div id="property-sidebar-backdrop" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-md opacity-0 pointer-events-none transition-opacity duration-500 ease-out" aria-hidden="true"></div>

<aside id="property-sidebar-drawer" class="fixed inset-y-0 right-0 z-50 w-full max-w-xl sm:max-w-2xl bg-gradient-to-b from-[#0c1f1a] via-[#0f2821] to-[#0a1714] text-[#f4efe7] shadow-[0_0_80px_rgba(0,0,0,0.85)] border-l border-[#c5a059]/30 flex flex-col transform translate-x-full transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] will-change-transform overflow-hidden" role="dialog" aria-modal="true" aria-labelledby="sidebar-property-title">
    {{-- Drawer Top Header Bar --}}
    <div class="flex items-center justify-between border-b border-white/10 px-6 py-4.5 bg-black/25 backdrop-blur-md shrink-0">
        <div class="flex items-center gap-2.5">
            <span class="h-2.5 w-2.5 rounded-full bg-[#d4af37] animate-pulse"></span>
            <span id="sidebar-property-badge" class="rounded-full border border-[#d4af37]/40 bg-[#d4af37]/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#e7d8b7]">
                Featured Property
            </span>
            <span id="sidebar-property-type" class="rounded-full bg-white/10 px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider text-[#cbdad0]">
                For Sale
            </span>
        </div>
        <div class="flex items-center gap-2">
            <span class="hidden sm:inline-block text-[11px] font-semibold uppercase tracking-widest text-[#a4b8ad]/70">ESC to close</span>
            <button type="button" id="close-property-sidebar" aria-label="Close property preview" class="group flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-white/10 text-white shadow-xs transition-all duration-200 hover:scale-105 hover:bg-white/20 hover:border-[#d4af37] active:scale-95 cursor-pointer">
                <svg class="h-5 w-5 transition-transform duration-200 group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Drawer Scrollable Content --}}
    <div class="flex-1 overflow-y-auto overscroll-contain px-6 py-6 space-y-6 [scrollbar-width:thin] [scrollbar-color:#c5a059_#0c1f1a]">
        {{-- Luxury High-Resolution Image Slider Gallery --}}
        <div class="relative overflow-hidden rounded-3xl border border-white/15 bg-[#091512] shadow-2xl">
            {{-- Main Image Display Frame --}}
            <div id="sidebar-gallery-viewport" class="relative aspect-[16/10] sm:aspect-[16/9] w-full overflow-hidden bg-black/60 select-none">
                <div id="sidebar-gallery-slides" class="relative h-full w-full">
                    {{-- Dynamically injected image slides with transition --}}
                </div>

                {{-- Left & Right Slider Controls --}}
                <div id="sidebar-slider-controls" class="absolute inset-x-3 top-1/2 -translate-y-1/2 flex items-center justify-between pointer-events-none">
                    <button type="button" id="sidebar-slider-prev" aria-label="Previous photo" class="pointer-events-auto flex h-11 w-11 items-center justify-center rounded-full border border-white/25 bg-[#0c1f1a]/85 text-[#e7d8b7] backdrop-blur-md shadow-xl transition-all duration-200 hover:scale-110 hover:bg-[#102b25] hover:text-white hover:border-[#d4af37] active:scale-95 disabled:opacity-30 disabled:pointer-events-none cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button type="button" id="sidebar-slider-next" aria-label="Next photo" class="pointer-events-auto flex h-11 w-11 items-center justify-center rounded-full border border-white/25 bg-[#0c1f1a]/85 text-[#e7d8b7] backdrop-blur-md shadow-xl transition-all duration-200 hover:scale-110 hover:bg-[#102b25] hover:text-white hover:border-[#d4af37] active:scale-95 disabled:opacity-30 disabled:pointer-events-none cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                {{-- Image Counter Pill --}}
                <div class="absolute bottom-3 right-3 flex items-center gap-1.5 rounded-full border border-white/20 bg-black/75 px-3 py-1 text-xs font-bold text-[#e7d8b7] backdrop-blur-md shadow-md">
                    <svg class="h-3.5 w-3.5 text-[#d4af37]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span id="sidebar-photo-counter">1 / 1</span>
                </div>

                {{-- Location / District Badge --}}
                <div class="absolute bottom-3 left-3 rounded-full border border-white/20 bg-black/75 px-3 py-1 text-xs font-semibold text-white backdrop-blur-md shadow-md" id="sidebar-property-city">
                    Addis Ababa
                </div>
            </div>

            {{-- Thumbnail Selector Strip --}}
            <div id="sidebar-thumbnails-container" class="flex gap-2.5 overflow-x-auto p-3 bg-black/40 border-t border-white/10 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                {{-- Thumbnails dynamically rendered --}}
            </div>
        </div>

        {{-- Property Pricing & Title --}}
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur-md">
            <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-3">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-[#a4b8ad]" id="sidebar-price-label">Asking Price</span>
                    <h3 class="text-3xl font-black text-[#e7d8b7] tracking-tight mt-1" id="sidebar-property-price">ETB 0</h3>
                </div>
                <div class="inline-flex items-center gap-1.5 rounded-full border border-[#d4af37]/40 bg-[#d4af37]/15 px-3.5 py-1 text-xs font-bold text-[#e7d8b7] self-start sm:self-auto">
                    <span>Negotiable</span>
                    <span class="text-[10px] text-[#cbdad0]">· Direct Agent Deal</span>
                </div>
            </div>

            <h2 class="mt-4 text-2xl font-black tracking-tight text-white leading-tight" id="sidebar-property-title">
                Property Title
            </h2>

            <p class="mt-2 flex items-center gap-2 text-sm text-[#cbdad0]" id="sidebar-property-address-row">
                <svg class="h-4 w-4 text-[#d4af37] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span id="sidebar-property-address">Prime Location</span>
            </p>

            {{-- 3 Key Metrics Pills --}}
            <div class="mt-5 grid grid-cols-3 gap-3">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-3.5 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#a4b8ad]">Bedrooms</p>
                    <p class="mt-1 text-xl font-black text-white" id="sidebar-property-bedrooms">0</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-3.5 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#a4b8ad]">Bathrooms</p>
                    <p class="mt-1 text-xl font-black text-white" id="sidebar-property-bathrooms">0</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-3.5 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#a4b8ad]">Area (ካሬ)</p>
                    <p class="mt-1 text-xl font-black text-[#e7d8b7]" id="sidebar-property-area">0 <span class="text-xs font-semibold text-white/70">sqm</span></p>
                </div>
            </div>
        </div>

        {{-- Property Description --}}
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur-md">
            <h4 class="text-xs font-bold uppercase tracking-[0.22em] text-[#e7d8b7]">Property Overview</h4>
            <p class="mt-3 text-sm leading-relaxed text-[#d7e5dc]" id="sidebar-property-description">
                Description goes here.
            </p>
        </div>

        {{-- Assigned Listing Agent Card --}}
        <div class="rounded-3xl border border-white/15 bg-gradient-to-br from-white/10 via-white/5 to-transparent p-5 backdrop-blur-md">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#00b53f]/20 border border-[#00b53f]/40 px-2.5 py-0.5 text-[11px] font-bold text-[#86efac]">
                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Verified Listing Agent
                </span>
                <span class="text-xs font-bold text-[#e7d8b7]">Direct Contact</span>
            </div>

            <div class="mt-4 flex items-center gap-3.5">
                <img id="sidebar-agent-photo" src="" alt="Agent photo" class="h-13 w-13 rounded-full object-cover ring-2 ring-[#d4af37] bg-black/40">
                <div class="min-w-0 flex-1">
                    <p class="truncate font-black text-base text-white" id="sidebar-agent-name">Listing Consultant</p>
                    <p class="truncate text-xs text-[#cbdad0]" id="sidebar-agent-phone">+251 90 000 0000</p>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2.5">
                <a id="sidebar-agent-call-btn" href="#" class="inline-flex flex-1 min-w-[130px] items-center justify-center gap-2 rounded-xl bg-[#00b53f] px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-lg transition hover:bg-[#009b36]">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6.54 5C6.6 5.89 6.75 6.76 6.99 7.59L5.79 8.79C5.38 7.59 5.12 6.32 5.03 5H6.54ZM16.4 17.02C17.25 17.26 18.12 17.41 19 17.47V18.96C17.68 18.87 16.41 18.61 15.2 18.21L16.4 17.02ZM7.5 3H4C3.45 3 3 3.45 3 4C3 13.39 10.61 21 20 21C20.55 21 21 20.55 21 20V16.51C21 15.96 20.55 15.51 20 15.51C18.76 15.51 17.55 15.31 16.43 14.94C16.33 14.9 16.22 14.89 16.12 14.89C15.86 14.89 15.61 14.99 15.41 15.18L13.21 17.38C10.38 15.93 8.06 13.62 6.62 10.79L8.82 8.59C9.1 8.31 9.18 7.92 9.07 7.57C8.7 6.45 8.5 5.25 8.5 4C8.5 3.45 8.05 3 7.5 3Z"/></svg>
                    <span>Call Agent</span>
                </a>
                <a id="sidebar-agent-whatsapp-btn" href="#" target="_blank" rel="noopener noreferrer" class="inline-flex flex-1 min-w-[130px] items-center justify-center gap-2 rounded-xl bg-[#25D366] px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-lg transition hover:bg-[#20ba59]">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.588-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.072-1.047-.042-.313-.102-.7-.234-1.206-.453-2.138-.925-3.528-3.08-3.635-3.223-.106-.143-.87-1.157-.87-2.207 0-1.05.549-1.567.744-1.78.196-.214.428-.268.572-.268.143 0 .287.002.412.008.132.006.309-.05.483.369.179.431.613 1.493.666 1.602.053.109.089.237.017.38-.071.144-.107.233-.214.358-.106.126-.224.281-.32.376-.107.106-.219.222-.094.436.125.214.557.918 1.196 1.488.823.733 1.517.96 1.731 1.067.214.107.339.089.464-.054.126-.143.536-.624.679-.838.143-.214.286-.179.482-.107.197.072 1.25.59 1.464.697.214.107.357.161.41.25.054.089.054.517-.09 1.031z"/></svg>
                    <span>WhatsApp</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Drawer Bottom Fixed Action Footer --}}
    <div class="border-t border-white/10 bg-black/40 px-6 py-4 backdrop-blur-md flex flex-wrap items-center justify-between gap-3 shrink-0">
        <a id="sidebar-full-details-link" href="#" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-[#e7d8b7] hover:underline underline-offset-4">
            <span>View Full Details &rarr;</span>
        </a>
        <a id="sidebar-buy-request-btn" href="#" class="rounded-full bg-gradient-to-r from-[#d4af37] via-[#c5a059] to-[#b38e46] px-6 py-3 text-xs font-bold uppercase tracking-[0.16em] text-[#0c1f1a] shadow-lg shadow-[#d4af37]/20 transition-all duration-200 hover:brightness-110 active:scale-95">
            Submit Formal Request
        </a>
    </div>
</aside>
