        <section id="settings" data-section="settings" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-[#f9f4ed] p-6 md:p-8 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#e7ddca] pb-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-[#1d3c34] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-white">Admin Console</span>
                        <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold text-[#1d3c34]">15 Section Studio</span>
                    </div>
                    <h2 class="mt-2 text-2xl md:text-3xl font-black text-[#1d3c34]">Site Settings &amp; Landing Page Studio</h2>
                    <p class="mt-1 text-sm text-slate-600">Select any section from the dropside menu to customize branding, color themes, texts, counters, amenities, and landing page content.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" form="site-settings-form" class="inline-flex items-center gap-2 rounded-full bg-[#1d3c34] px-6 py-3 text-sm font-bold text-white shadow-md transition hover:bg-[#254d43] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#1d3c34] focus:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save All Settings
                    </button>
                </div>
            </div>

            <!-- Mobile Dropside Selector (lg:hidden) -->
            <div class="mt-6 lg:hidden">
                <label for="settings-dropside-select" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">
                    Jump to Section (Dropside)
                </label>
                <div class="relative">
                    <select id="settings-dropside-select" class="w-full appearance-none rounded-xl border border-[#d9cab3] bg-white px-4 py-3.5 pr-10 text-sm font-bold text-[#1d3c34] shadow-xs focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                        <option value="panel-branding" selected>1. Site Branding &amp; Contact</option>
                        <option value="panel-theme">2. Color Theme &amp; Palette Studio</option>
                        <option value="panel-hero">3. Homepage Hero &amp; Quick Search</option>
                        <option value="panel-performance">4. Performance Counter Bar</option>
                        <option value="panel-lifestyle">5. Lifestyle &amp; Master Amenities (8 Cards)</option>
                        <option value="panel-featured">6. Featured Properties Section</option>
                        <option value="panel-panorama">7. Architectural Panorama Banner</option>
                        <option value="panel-developments">8. Featured Projects &amp; Developments</option>
                        <option value="panel-latest">9. Latest Properties Section</option>
                        <option value="panel-about">10. About &amp; Heritage Section</option>
                        <option value="panel-news">11. Blog, News &amp; Careers</option>
                        <option value="panel-testimonials">12. Testimonials Section</option>
                        <option value="panel-agents">13. Agents Section</option>
                        <option value="panel-contact">14. VIP Contact &amp; Consultation Suite</option>
                        <option value="panel-newsletter">15. Floating Concierge Dock</option>
                        <option value="panel-currency">16. Currency &amp; Exchange Rates</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <form id="site-settings-form" method="POST" action="{{ route('dashboard.settings.update') }}" enctype="multipart/form-data" class="mt-6">
                @csrf
                <div class="grid lg:grid-cols-12 gap-6 items-start">
                    <!-- Dropside Navigation Sidebar (Desktop) -->
                    <aside class="hidden lg:block lg:col-span-4 xl:col-span-3 sticky top-6">
                        <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf8f3] p-3 shadow-xs">
                            <div class="px-3 py-2 border-b border-[#ebdcc8] mb-2 flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Dropside Navigation</span>
                                <span class="rounded-full bg-[#1d3c34]/10 px-2 py-0.5 text-[10px] font-bold text-[#1d3c34]">16 Sections</span>
                            </div>
                            <nav class="space-y-1 max-h-[calc(100vh-14rem)] overflow-y-auto pr-1">
                                <!-- 1. Site Branding -->
                                <button type="button" data-settings-target="panel-branding" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold transition bg-[#1d3c34] text-white shadow-sm">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <span class="truncate">Site Branding</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-white/20 text-white">Logo</span>
                                </button>

                                <!-- 2. Color Theme & Appearance -->
                                <button type="button" data-settings-target="panel-theme" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                        <span class="truncate">Color Theme &amp; Palette</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Theme</span>
                                </button>

                                <!-- 3. Homepage Hero -->
                                <button type="button" data-settings-target="panel-hero" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                        <span class="truncate">Homepage Hero</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Hero</span>
                                </button>

                                <!-- 3. Performance -->
                                <button type="button" data-settings-target="panel-performance" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                        <span class="truncate">Performance</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Counters</span>
                                </button>

                                <!-- 4. Lifestyle -->
                                <button type="button" data-settings-target="panel-lifestyle" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7M9 3v2m6-2v2M4 7h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V9a2 2 0 012-2z"/></svg>
                                        <span class="truncate">Lifestyle &amp; Amenities</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">8 Cards</span>
                                </button>

                                <!-- 5. Featured Properties -->
                                <button type="button" data-settings-target="panel-featured" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                        <span class="truncate">Featured Properties</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Showcase</span>
                                </button>

                                <!-- 6. Panorama -->
                                <button type="button" data-settings-target="panel-panorama" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="truncate">Panorama Banner</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Skyline</span>
                                </button>

                                <!-- 7. Developments -->
                                <button type="button" data-settings-target="panel-developments" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                                        <span class="truncate">Developments &amp; Projects</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Section</span>
                                </button>

                                <!-- 8. Latest Properties -->
                                <button type="button" data-settings-target="panel-latest" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="truncate">Latest Properties</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Listings</span>
                                </button>

                                <!-- 9. About & Heritage -->
                                <button type="button" data-settings-target="panel-about" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        <span class="truncate">About &amp; Heritage</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Story</span>
                                </button>

                                <!-- 10. Blog & News -->
                                <button type="button" data-settings-target="panel-news" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                        <span class="truncate">Blog, News &amp; Careers</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">News</span>
                                </button>

                                <!-- 11. Testimonials -->
                                <button type="button" data-settings-target="panel-testimonials" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        <span class="truncate">Testimonials</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Reviews</span>
                                </button>

                                <!-- 12. Agents -->
                                <button type="button" data-settings-target="panel-agents" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span class="truncate">Agents</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Team</span>
                                </button>

                                <!-- 13. VIP Contact Suite -->
                                <button type="button" data-settings-target="panel-contact" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span class="truncate">VIP Contact Suite</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">VIP</span>
                                </button>

                                <!-- 14. Floating Concierge -->
                                <button type="button" data-settings-target="panel-newsletter" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                        <span class="truncate">Floating Concierge</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">Footer</span>
                                </button>

                                <!-- 16. Currency & Exchange Rates -->
                                <button type="button" data-settings-target="panel-currency" class="settings-tab-btn w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-[#f0e5d8] transition">
                                    <span class="flex items-center gap-2.5 truncate">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="truncate">Currency &amp; Exchange Rates</span>
                                    </span>
                                    <span class="tab-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase bg-[#ebdcc8] text-[#1d3c34]">FX</span>
                                </button>
                            </nav>

                            <div class="pt-3 mt-2 border-t border-[#ebdcc8]">
                                <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#1d3c34] px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-[#254d43] transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Save All Settings
                                </button>
                            </div>
                        </div>
                    </aside>

                    <!-- Panels Container (Right Column) -->
                    <div class="lg:col-span-8 xl:col-span-9 space-y-6">

                        <!-- PANEL 1: SITE BRANDING & CONTACT -->
                        <div id="panel-branding" data-settings-panel="panel-branding" class="settings-tab-panel space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 1 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Site Identity</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Site Branding &amp; Contact</h3>
                                    <p class="text-xs text-slate-600">Controls the primary logo, brand name, and direct communication channels.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Site Name</label>
                                    <input type="text" name="site_name" value="{{ old('site_name', $siteBrand['name']) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Logo</label>
                                    <div class="flex items-center gap-3">
                                        <label for="site-logo-input" class="cursor-pointer rounded-full bg-[#1d3c34] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#254d43]">
                                            Choose Logo
                                        </label>
                                        <span id="site-logo-name" class="truncate text-sm text-slate-500">Current logo in use</span>
                                    </div>
                                    <input id="site-logo-input" type="file" name="site_logo" accept="image/*" class="sr-only"
                                        onchange="document.getElementById('site-logo-name').textContent = this.files.length ? this.files[0].name : 'Current logo in use';">
                                    <img src="{{ $siteBrand['logoUrl'] }}" alt="Current logo" class="mt-3 h-12 w-12 rounded-xl object-contain ring-1 ring-[#d9cab3] bg-white">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Email</label>
                                    <input type="email" name="contact_email" value="{{ old('contact_email', $siteBrand['email']) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Phone</label>
                                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $siteBrand['phone']) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Address</label>
                                    <input type="text" name="contact_address" value="{{ old('contact_address', $siteBrand['address']) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <div></div>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-theme" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Color Theme &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 2: COLOR THEME & PALETTE STUDIO -->
                        <div id="panel-theme" data-settings-panel="panel-theme" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 2 of 15</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Theme &amp; Appearance</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Color Theme &amp; Palette Studio</h3>
                                    <p class="text-xs text-slate-600">Select any curated luxury color palette or fine-tune individual brand, accent, border, and background colors with real-time preview.</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="resetToDefaultTheme()" class="inline-flex items-center gap-1.5 rounded-full border border-[#d9cab3] bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-[#f5f0e7] transition">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        Reset to Default
                                    </button>
                                    <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                </div>
                            </div>

                            <!-- PRESET PALETTES -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <h4 class="text-sm font-bold text-[#1d3c34] uppercase tracking-wider">Curated Luxury Presets</h4>
                                        <p class="text-xs text-slate-500">Click any preset to automatically configure all complementary colors and preview immediately.</p>
                                    </div>
                                    <input type="hidden" name="theme_preset" id="input_theme_preset" value="{{ old('theme_preset', $siteTheme['preset'] ?? 'emerald') }}">
                                </div>

                                <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3" id="theme-presets-grid">
                                    @foreach (\App\Models\SiteSetting::themePresets() as $key => $preset)
                                        @php
                                            $isActivePreset = ($siteTheme['preset'] ?? 'emerald') === $key;
                                        @endphp
                                        <button type="button"
                                            onclick="applyThemePreset('{{ $key }}')"
                                            data-preset-key="{{ $key }}"
                                            class="theme-preset-card group relative flex flex-col justify-between rounded-xl border-2 p-3.5 text-left transition {{ $isActivePreset ? 'border-[#1d3c34] bg-[#edf2ee]/40 shadow-xs ring-2 ring-[#1d3c34]/20' : 'border-[#e7ddca] bg-[#fdfaf5] hover:border-[#d9cab3] hover:bg-white' }}">
                                            <div>
                                                <div class="flex items-center justify-between gap-1 mb-1.5">
                                                    <span class="text-xs font-bold text-[#1d3c34] truncate">{{ $preset['name'] }}</span>
                                                    <span class="preset-active-indicator {{ $isActivePreset ? '' : 'hidden' }} inline-flex items-center rounded-full bg-[#1d3c34] p-0.5 text-white">
                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed mb-3">{{ $preset['description'] }}</p>
                                            </div>

                                            <div class="flex items-center gap-1.5 pt-2 border-t border-[#ebdcc8]/60">
                                                <div class="h-5 w-5 rounded-full shadow-2xs ring-1 ring-black/10" style="background-color: {{ $preset['colors']['primary'] }};" title="Primary: {{ $preset['colors']['primary'] }}"></div>
                                                <div class="h-5 w-5 rounded-full shadow-2xs ring-1 ring-black/10" style="background-color: {{ $preset['colors']['accent'] }};" title="Accent: {{ $preset['colors']['accent'] }}"></div>
                                                <div class="h-5 w-5 rounded-full shadow-2xs ring-1 ring-black/10" style="background-color: {{ $preset['colors']['secondary'] }};" title="Border: {{ $preset['colors']['secondary'] }}"></div>
                                                <div class="h-5 w-5 rounded-full shadow-2xs ring-1 ring-black/10" style="background-color: {{ $preset['colors']['site_bg'] }};" title="Background: {{ $preset['colors']['site_bg'] }}"></div>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- GRANULAR COLOR PICKERS -->
                            <div class="pt-4 border-t border-[#e7ddca]">
                                <h4 class="text-sm font-bold text-[#1d3c34] uppercase tracking-wider mb-1">Fine-Tune Individual Colors</h4>
                                <p class="text-xs text-slate-500 mb-4">Click any swatch to open the color picker, or directly enter hex codes. The live preview below updates automatically.</p>

                                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                                    <!-- Group 1: Primary Brand -->
                                    <div class="rounded-xl border border-[#e7ddca] bg-[#fbf8f3] p-4 space-y-3 shadow-2xs">
                                        <div class="flex items-center gap-2 border-b border-[#ebdcc8] pb-2">
                                            <span class="h-2 w-2 rounded-full bg-[#1d3c34]"></span>
                                            <span class="text-xs font-bold uppercase tracking-wider text-[#1d3c34]">1. Primary Palette</span>
                                        </div>

                                        <!-- Primary Color -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Primary Brand</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" id="picker_theme_primary_color" value="{{ old('theme_primary_color', $siteTheme['primary'] ?? '#1d3c34') }}" oninput="updateColorFromPicker('theme_primary_color', this.value)" class="h-9 w-10 cursor-pointer rounded-lg border border-[#d9cab3] bg-white p-0.5">
                                                <input type="text" name="theme_primary_color" id="input_theme_primary_color" value="{{ old('theme_primary_color', $siteTheme['primary'] ?? '#1d3c34') }}" maxlength="7" oninput="updateColorFromText('theme_primary_color', this.value)" class="w-full uppercase font-mono text-xs rounded-lg border border-[#d9cab3] bg-white px-2.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                            <span class="text-[10px] text-slate-500">Navigation, main buttons, headers</span>
                                        </div>

                                        <!-- Primary Dark -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Primary Deep / Dark</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" id="picker_theme_primary_dark" value="{{ old('theme_primary_dark', $siteTheme['primary_dark'] ?? '#102b25') }}" oninput="updateColorFromPicker('theme_primary_dark', this.value)" class="h-9 w-10 cursor-pointer rounded-lg border border-[#d9cab3] bg-white p-0.5">
                                                <input type="text" name="theme_primary_dark" id="input_theme_primary_dark" value="{{ old('theme_primary_dark', $siteTheme['primary_dark'] ?? '#102b25') }}" maxlength="7" oninput="updateColorFromText('theme_primary_dark', this.value)" class="w-full uppercase font-mono text-xs rounded-lg border border-[#d9cab3] bg-white px-2.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                            <span class="text-[10px] text-slate-500">Hero background, footer, dark text</span>
                                        </div>

                                        <!-- Primary Hover -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Primary Hover State</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" id="picker_theme_primary_hover" value="{{ old('theme_primary_hover', $siteTheme['primary_hover'] ?? '#254d43') }}" oninput="updateColorFromPicker('theme_primary_hover', this.value)" class="h-9 w-10 cursor-pointer rounded-lg border border-[#d9cab3] bg-white p-0.5">
                                                <input type="text" name="theme_primary_hover" id="input_theme_primary_hover" value="{{ old('theme_primary_hover', $siteTheme['primary_hover'] ?? '#254d43') }}" maxlength="7" oninput="updateColorFromText('theme_primary_hover', this.value)" class="w-full uppercase font-mono text-xs rounded-lg border border-[#d9cab3] bg-white px-2.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                            <span class="text-[10px] text-slate-500">Hover states on interactive buttons</span>
                                        </div>

                                        <!-- Primary Light -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Primary Tint / Soft</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" id="picker_theme_primary_light" value="{{ old('theme_primary_light', $siteTheme['primary_light'] ?? '#dfeee4') }}" oninput="updateColorFromPicker('theme_primary_light', this.value)" class="h-9 w-10 cursor-pointer rounded-lg border border-[#d9cab3] bg-white p-0.5">
                                                <input type="text" name="theme_primary_light" id="input_theme_primary_light" value="{{ old('theme_primary_light', $siteTheme['primary_light'] ?? '#dfeee4') }}" maxlength="7" oninput="updateColorFromText('theme_primary_light', this.value)" class="w-full uppercase font-mono text-xs rounded-lg border border-[#d9cab3] bg-white px-2.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                            <span class="text-[10px] text-slate-500">Badge backgrounds, input rings</span>
                                        </div>
                                    </div>

                                    <!-- Group 2: Accent Palette -->
                                    <div class="rounded-xl border border-[#e7ddca] bg-[#fbf8f3] p-4 space-y-3 shadow-2xs">
                                        <div class="flex items-center gap-2 border-b border-[#ebdcc8] pb-2">
                                            <span class="h-2 w-2 rounded-full bg-[#c5a059]"></span>
                                            <span class="text-xs font-bold uppercase tracking-wider text-[#1d3c34]">2. Luxury Accent</span>
                                        </div>

                                        <!-- Accent Color -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Accent Gold / Highlight</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" id="picker_theme_accent_color" value="{{ old('theme_accent_color', $siteTheme['accent'] ?? '#c5a059') }}" oninput="updateColorFromPicker('theme_accent_color', this.value)" class="h-9 w-10 cursor-pointer rounded-lg border border-[#d9cab3] bg-white p-0.5">
                                                <input type="text" name="theme_accent_color" id="input_theme_accent_color" value="{{ old('theme_accent_color', $siteTheme['accent'] ?? '#c5a059') }}" maxlength="7" oninput="updateColorFromText('theme_accent_color', this.value)" class="w-full uppercase font-mono text-xs rounded-lg border border-[#d9cab3] bg-white px-2.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                            <span class="text-[10px] text-slate-500">Luxury stars, featured badges, accents</span>
                                        </div>

                                        <!-- Accent Dark -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Accent Dark / Text</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" id="picker_theme_accent_dark" value="{{ old('theme_accent_dark', $siteTheme['accent_dark'] ?? '#9b6c17') }}" oninput="updateColorFromPicker('theme_accent_dark', this.value)" class="h-9 w-10 cursor-pointer rounded-lg border border-[#d9cab3] bg-white p-0.5">
                                                <input type="text" name="theme_accent_dark" id="input_theme_accent_dark" value="{{ old('theme_accent_dark', $siteTheme['accent_dark'] ?? '#9b6c17') }}" maxlength="7" oninput="updateColorFromText('theme_accent_dark', this.value)" class="w-full uppercase font-mono text-xs rounded-lg border border-[#d9cab3] bg-white px-2.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                            <span class="text-[10px] text-slate-500">High-contrast accent text, notification numbers</span>
                                        </div>

                                        <!-- Accent Light -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Accent Light / Tint</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" id="picker_theme_accent_light" value="{{ old('theme_accent_light', $siteTheme['accent_light'] ?? '#f9ecd0') }}" oninput="updateColorFromPicker('theme_accent_light', this.value)" class="h-9 w-10 cursor-pointer rounded-lg border border-[#d9cab3] bg-white p-0.5">
                                                <input type="text" name="theme_accent_light" id="input_theme_accent_light" value="{{ old('theme_accent_light', $siteTheme['accent_light'] ?? '#f9ecd0') }}" maxlength="7" oninput="updateColorFromText('theme_accent_light', this.value)" class="w-full uppercase font-mono text-xs rounded-lg border border-[#d9cab3] bg-white px-2.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                            <span class="text-[10px] text-slate-500">Accent pill backgrounds, counter highlights</span>
                                        </div>
                                    </div>

                                    <!-- Group 3: Secondary & Borders -->
                                    <div class="rounded-xl border border-[#e7ddca] bg-[#fbf8f3] p-4 space-y-3 shadow-2xs">
                                        <div class="flex items-center gap-2 border-b border-[#ebdcc8] pb-2">
                                            <span class="h-2 w-2 rounded-full bg-[#d9cab3]"></span>
                                            <span class="text-xs font-bold uppercase tracking-wider text-[#1d3c34]">3. Borders &amp; Structure</span>
                                        </div>

                                        <!-- Secondary Color -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Secondary / Border</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" id="picker_theme_secondary_color" value="{{ old('theme_secondary_color', $siteTheme['secondary'] ?? '#d9cab3') }}" oninput="updateColorFromPicker('theme_secondary_color', this.value)" class="h-9 w-10 cursor-pointer rounded-lg border border-[#d9cab3] bg-white p-0.5">
                                                <input type="text" name="theme_secondary_color" id="input_theme_secondary_color" value="{{ old('theme_secondary_color', $siteTheme['secondary'] ?? '#d9cab3') }}" maxlength="7" oninput="updateColorFromText('theme_secondary_color', this.value)" class="w-full uppercase font-mono text-xs rounded-lg border border-[#d9cab3] bg-white px-2.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                            <span class="text-[10px] text-slate-500">Main card outlines, input borders, dividers</span>
                                        </div>

                                        <!-- Secondary Light -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Secondary Light Divider</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" id="picker_theme_secondary_light" value="{{ old('theme_secondary_light', $siteTheme['secondary_light'] ?? '#e7ddca') }}" oninput="updateColorFromPicker('theme_secondary_light', this.value)" class="h-9 w-10 cursor-pointer rounded-lg border border-[#d9cab3] bg-white p-0.5">
                                                <input type="text" name="theme_secondary_light" id="input_theme_secondary_light" value="{{ old('theme_secondary_light', $siteTheme['secondary_light'] ?? '#e7ddca') }}" maxlength="7" oninput="updateColorFromText('theme_secondary_light', this.value)" class="w-full uppercase font-mono text-xs rounded-lg border border-[#d9cab3] bg-white px-2.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                            <span class="text-[10px] text-slate-500">Subtle separators, panel borders</span>
                                        </div>
                                    </div>

                                    <!-- Group 4: Backgrounds & Text -->
                                    <div class="rounded-xl border border-[#e7ddca] bg-[#fbf8f3] p-4 space-y-3 shadow-2xs">
                                        <div class="flex items-center gap-2 border-b border-[#ebdcc8] pb-2">
                                            <span class="h-2 w-2 rounded-full bg-[#587165]"></span>
                                            <span class="text-xs font-bold uppercase tracking-wider text-[#1d3c34]">4. Canvas &amp; Text</span>
                                        </div>

                                        <!-- Site Background -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Site Page Background</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" id="picker_theme_site_bg" value="{{ old('theme_site_bg', $siteTheme['site_bg'] ?? '#f6f1e8') }}" oninput="updateColorFromPicker('theme_site_bg', this.value)" class="h-9 w-10 cursor-pointer rounded-lg border border-[#d9cab3] bg-white p-0.5">
                                                <input type="text" name="theme_site_bg" id="input_theme_site_bg" value="{{ old('theme_site_bg', $siteTheme['site_bg'] ?? '#f6f1e8') }}" maxlength="7" oninput="updateColorFromText('theme_site_bg', this.value)" class="w-full uppercase font-mono text-xs rounded-lg border border-[#d9cab3] bg-white px-2.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                            <span class="text-[10px] text-slate-500">Main body canvas background</span>
                                        </div>

                                        <!-- Card Background -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Card / Surface Panel</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" id="picker_theme_card_bg" value="{{ old('theme_card_bg', $siteTheme['card_bg'] ?? '#fdfaf5') }}" oninput="updateColorFromPicker('theme_card_bg', this.value)" class="h-9 w-10 cursor-pointer rounded-lg border border-[#d9cab3] bg-white p-0.5">
                                                <input type="text" name="theme_card_bg" id="input_theme_card_bg" value="{{ old('theme_card_bg', $siteTheme['card_bg'] ?? '#fdfaf5') }}" maxlength="7" oninput="updateColorFromText('theme_card_bg', this.value)" class="w-full uppercase font-mono text-xs rounded-lg border border-[#d9cab3] bg-white px-2.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                            <span class="text-[10px] text-slate-500">Surface of cards, modals, and content boxes</span>
                                        </div>

                                        <!-- Text Dark -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Text Dark / Headings</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" id="picker_theme_text_dark" value="{{ old('theme_text_dark', $siteTheme['text_dark'] ?? '#1d3c34') }}" oninput="updateColorFromPicker('theme_text_dark', this.value)" class="h-9 w-10 cursor-pointer rounded-lg border border-[#d9cab3] bg-white p-0.5">
                                                <input type="text" name="theme_text_dark" id="input_theme_text_dark" value="{{ old('theme_text_dark', $siteTheme['text_dark'] ?? '#1d3c34') }}" maxlength="7" oninput="updateColorFromText('theme_text_dark', this.value)" class="w-full uppercase font-mono text-xs rounded-lg border border-[#d9cab3] bg-white px-2.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                            <span class="text-[10px] text-slate-500">High-contrast heading and title text</span>
                                        </div>

                                        <!-- Text Muted -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Muted Secondary Text</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" id="picker_theme_text_muted" value="{{ old('theme_text_muted', $siteTheme['text_muted'] ?? '#587165') }}" oninput="updateColorFromPicker('theme_text_muted', this.value)" class="h-9 w-10 cursor-pointer rounded-lg border border-[#d9cab3] bg-white p-0.5">
                                                <input type="text" name="theme_text_muted" id="input_theme_text_muted" value="{{ old('theme_text_muted', $siteTheme['text_muted'] ?? '#587165') }}" maxlength="7" oninput="updateColorFromText('theme_text_muted', this.value)" class="w-full uppercase font-mono text-xs rounded-lg border border-[#d9cab3] bg-white px-2.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                            <span class="text-[10px] text-slate-500">Subtitles, captions, and descriptions</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- LIVE INTERACTIVE VISUAL PREVIEW STUDIO -->
                            <div class="pt-6 border-t border-[#e7ddca]">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="relative flex h-2.5 w-2.5">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                            </span>
                                            <h4 class="text-sm font-bold text-[#1d3c34] uppercase tracking-wider">Live Interactive Preview</h4>
                                        </div>
                                        <p class="text-xs text-slate-500">Instant visual mockup showing how your palette applies to real components.</p>
                                    </div>
                                    <span class="text-[11px] font-semibold text-slate-400">Updates in real-time</span>
                                </div>

                                <div id="theme-live-preview-box" class="rounded-2xl border p-5 transition-colors duration-300" style="background-color: {{ $siteTheme['site_bg'] ?? '#f6f1e8' }}; border-color: {{ $siteTheme['secondary'] ?? '#d9cab3' }};">
                                    <!-- Mini Header Mockup -->
                                    <div id="prev-header" class="flex flex-col sm:flex-row items-center justify-between gap-3 rounded-xl p-3 shadow-xs mb-5 transition-colors duration-300" style="background-color: {{ $siteTheme['primary_dark'] ?? '#102b25' }};">
                                        <div class="flex items-center gap-2.5">
                                            <div id="prev-logo-icon" class="h-7 w-7 rounded-lg flex items-center justify-center font-black text-xs text-white" style="background-color: {{ $siteTheme['primary'] ?? '#1d3c34' }}; border: 1px solid {{ $siteTheme['secondary'] ?? '#d9cab3' }};">
                                                RE
                                            </div>
                                            <span class="text-sm font-black text-white tracking-tight">{{ $siteBrand['name'] ?? 'Real Estate' }}</span>
                                        </div>
                                        <div class="flex items-center gap-4 text-xs font-semibold">
                                            <span id="prev-nav-active" class="px-2.5 py-1 rounded-lg text-white" style="background-color: {{ $siteTheme['primary'] ?? '#1d3c34' }};">Home</span>
                                            <span class="text-white/80">Properties</span>
                                            <span class="text-white/80">Agents</span>
                                            <button type="button" id="prev-header-cta" class="rounded-full px-3 py-1 text-xs font-bold text-white shadow-2xs transition" style="background-color: {{ $siteTheme['primary'] ?? '#1d3c34' }};">
                                                Contact
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Mockup Content Grid -->
                                    <div class="grid md:grid-cols-12 gap-4">
                                        <!-- Mini Hero Card -->
                                        <div id="prev-hero-card" class="md:col-span-7 rounded-xl p-5 shadow-xs border transition-colors duration-300 flex flex-col justify-between" style="background-color: {{ $siteTheme['card_bg'] ?? '#fdfaf5' }}; border-color: {{ $siteTheme['secondary'] ?? '#d9cab3' }};">
                                            <div>
                                                <div id="prev-hero-badge" class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider mb-2.5" style="background-color: {{ $siteTheme['primary_light'] ?? '#dfeee4' }}; color: {{ $siteTheme['primary'] ?? '#1d3c34' }};">
                                                    <span>Premium Living</span>
                                                </div>
                                                <h5 id="prev-hero-title" class="text-base font-black tracking-tight mb-1" style="color: {{ $siteTheme['text_dark'] ?? '#1d3c34' }};">
                                                    Discover homes that feel like your next chapter.
                                                </h5>
                                                <p id="prev-hero-desc" class="text-xs leading-relaxed mb-4" style="color: {{ $siteTheme['text_muted'] ?? '#587165' }};">
                                                    Curated villas and luxury apartments located in prime neighborhoods.
                                                </p>
                                            </div>

                                            <div class="flex flex-wrap items-center gap-2">
                                                <button type="button" id="prev-btn-primary" class="rounded-full px-3.5 py-1.5 text-xs font-bold text-white shadow-2xs transition" style="background-color: {{ $siteTheme['primary'] ?? '#1d3c34' }};">
                                                    Explore Listings
                                                </button>
                                                <button type="button" id="prev-btn-outline" class="rounded-full border px-3.5 py-1.5 text-xs font-bold transition" style="border-color: {{ $siteTheme['secondary'] ?? '#d9cab3' }}; color: {{ $siteTheme['text_dark'] ?? '#1d3c34' }}; background-color: {{ $siteTheme['card_bg'] ?? '#fdfaf5' }};">
                                                    Book a Visit
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Mini Property Card -->
                                        <div id="prev-prop-card" class="md:col-span-5 rounded-xl p-3.5 shadow-xs border transition-colors duration-300 flex flex-col justify-between" style="background-color: {{ $siteTheme['card_bg'] ?? '#fdfaf5' }}; border-color: {{ $siteTheme['secondary'] ?? '#d9cab3' }};">
                                            <div>
                                                <div id="prev-prop-grad" class="relative h-24 rounded-lg overflow-hidden flex items-start justify-between p-2" style="background: linear-gradient(135deg, {{ $siteTheme['primary_dark'] ?? '#102b25' }} 0%, {{ $siteTheme['primary'] ?? '#1d3c34' }} 100%);">
                                                    <span id="prev-prop-badge-featured" class="rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-slate-900 shadow-2xs" style="background-color: {{ $siteTheme['accent'] ?? '#c5a059' }};">
                                                        ★ Featured
                                                    </span>
                                                    <span id="prev-prop-badge-status" class="rounded-full px-2 py-0.5 text-[9px] font-bold text-white" style="background-color: {{ $siteTheme['primary_dark'] ?? '#102b25' }}; border: 1px solid {{ $siteTheme['secondary'] ?? '#d9cab3' }};">
                                                        For Sale
                                                    </span>
                                                </div>

                                                <div class="mt-2.5">
                                                    <span id="prev-prop-price" class="text-xs font-black tracking-tight" style="color: {{ $siteTheme['primary'] ?? '#1d3c34' }};">$480,000</span>
                                                    <h6 id="prev-prop-title" class="text-xs font-bold truncate mt-0.5" style="color: {{ $siteTheme['text_dark'] ?? '#1d3c34' }};">Grand Horizon Skyline Villa</h6>
                                                    <p id="prev-prop-desc" class="text-[10px] truncate" style="color: {{ $siteTheme['text_muted'] ?? '#587165' }};">4 Beds • 3 Baths • Prime District</p>
                                                </div>
                                            </div>

                                            <div id="prev-prop-foot" class="mt-3 pt-2.5 border-t flex items-center justify-between" style="border-color: {{ $siteTheme['secondary_light'] ?? '#e7ddca' }};">
                                                <div id="prev-prop-verified" class="flex items-center gap-1.5 rounded-full px-2 py-0.5" style="background-color: {{ $siteTheme['accent_light'] ?? '#f9ecd0' }};">
                                                    <span id="prev-prop-verified-text" class="text-[10px] font-bold" style="color: {{ $siteTheme['accent_dark'] ?? '#9b6c17' }};">Verified</span>
                                                </div>
                                                <button type="button" id="prev-prop-btn" class="rounded-full px-3 py-1 text-[10px] font-bold text-white shadow-2xs transition" style="background-color: {{ $siteTheme['primary'] ?? '#1d3c34' }};">
                                                    Details &rarr;
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- NAVIGATION FOOTER -->
                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-branding" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Back: Site Branding
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Theme Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-hero" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Homepage Hero &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 3: HOMEPAGE HERO & QUICK SEARCH -->
                        <div id="panel-hero" data-settings-panel="panel-hero" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 2 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Hero Section</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Homepage Content — Hero Banner &amp; Quick Search</h3>
                                    <p class="text-xs text-slate-600">Customize the main hero title, description, call-to-action buttons, counters, and search filter labels.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Badge</label>
                                    <input type="text" name="hero_badge" value="{{ old('hero_badge', \App\Models\SiteSetting::get('hero_badge', 'Premium living spaces')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Title</label>
                                    <input type="text" name="hero_title" value="{{ old('hero_title', \App\Models\SiteSetting::get('hero_title', 'Discover homes that feel like your next chapter.')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Description</label>
                                    <textarea name="hero_description" rows="2" maxlength="1500" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('hero_description', \App\Models\SiteSetting::get('hero_description', 'Find luxury villas, modern apartments, and smart investments in trusted neighborhoods with a team that knows the market deeply.')) }}</textarea>
                                </div>

                                <!-- Hero Background Static Image Control -->
                                <div class="md:col-span-2 rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-5 shadow-xs">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div>
                                            <label class="block text-sm font-bold text-[#1d3c34]">Hero Background Image</label>
                                            <p class="text-xs text-slate-500">High-resolution cinematic background image displayed behind the hero banner.</p>
                                        </div>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Hero Background Image</span>
                                    </div>
                                    <div class="mt-4 flex flex-col md:flex-row items-start md:items-center gap-5">
                                        <div class="relative h-24 w-40 shrink-0 overflow-hidden rounded-xl border border-[#d9cab3] bg-white shadow-xs">
                                            <img id="preview-hero-image" src="{{ \App\Models\SiteSetting::imageUrl('hero_image', 'images/luxury/hero-skyline.jpg') }}" alt="Hero Background Preview" class="h-full w-full object-cover">
                                        </div>
                                        <div class="flex-1 space-y-2 w-full">
                                            <div class="flex items-center gap-3">
                                                <label for="hero-image-input" class="cursor-pointer rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-[#254d43]">
                                                    Upload New Image
                                                </label>
                                                <span id="hero-image-name" class="truncate text-xs text-slate-500">Current image in use</span>
                                            </div>
                                            <input id="hero-image-input" type="file" name="hero_image_file" accept="image/*" class="sr-only"
                                                onchange="document.getElementById('hero-image-name').textContent = this.files.length ? this.files[0].name : 'Current image in use'; if (this.files && this.files[0]) { document.getElementById('preview-hero-image').src = URL.createObjectURL(this.files[0]); }">
                                            <div class="flex items-center gap-2 pt-1">
                                                <span class="text-xs text-slate-500 whitespace-nowrap">Or Image URL:</span>
                                                <input type="text" name="hero_image" value="{{ old('hero_image', \App\Models\SiteSetting::get('hero_image')) }}" placeholder="e.g. https://... or images/luxury/hero-skyline.jpg" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Primary Button Text</label>
                                    <input type="text" name="hero_primary_button_text" value="{{ old('hero_primary_button_text', \App\Models\SiteSetting::get('hero_primary_button_text', 'Explore Listings')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Secondary Button Text</label>
                                    <input type="text" name="hero_secondary_button_text" value="{{ old('hero_secondary_button_text', \App\Models\SiteSetting::get('hero_secondary_button_text', 'Book a Visit')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Careers Link Text</label>
                                    <input type="text" name="hero_careers_link_text" value="{{ old('hero_careers_link_text', \App\Models\SiteSetting::get('hero_careers_link_text', 'Careers &rarr;')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Featured Property Card Badge</label>
                                    <input type="text" name="hero_featured_badge" value="{{ old('hero_featured_badge', \App\Models\SiteSetting::get('hero_featured_badge', 'Featured property')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Stat 1 Label</label>
                                    <input type="text" name="hero_stat_1_label" value="{{ old('hero_stat_1_label', \App\Models\SiteSetting::get('hero_stat_1_label', 'happy buyers')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Stat 2 Label</label>
                                    <input type="text" name="hero_stat_2_label" value="{{ old('hero_stat_2_label', \App\Models\SiteSetting::get('hero_stat_2_label', 'properties sold')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Stat 3 Value</label>
                                    <input type="text" name="hero_stat_3_value" value="{{ old('hero_stat_3_value', \App\Models\SiteSetting::get('hero_stat_3_value', '18 yrs')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Stat 3 Label</label>
                                    <input type="text" name="hero_stat_3_label" value="{{ old('hero_stat_3_label', \App\Models\SiteSetting::get('hero_stat_3_label', 'market expertise')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Search Filter: Category Label</label>
                                    <input type="text" name="filter_category_label" value="{{ old('filter_category_label', \App\Models\SiteSetting::get('filter_category_label', 'Residence Category')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Search Filter: Type Label</label>
                                    <input type="text" name="filter_type_label" value="{{ old('filter_type_label', \App\Models\SiteSetting::get('filter_type_label', 'Ownership Type')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Search Filter: Location Label</label>
                                    <input type="text" name="filter_location_label" value="{{ old('filter_location_label', \App\Models\SiteSetting::get('filter_location_label', 'Preferred Location')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Search Filter: Submit Button Text</label>
                                    <input type="text" name="filter_button_text" value="{{ old('filter_button_text', \App\Models\SiteSetting::get('filter_button_text', 'Search Properties')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-theme" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Color Theme
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-performance" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Performance &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 3: PERFORMANCE COUNTERS -->
                        <div id="panel-performance" data-settings-panel="panel-performance" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 3 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Counter Section</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Performance Counter Bar</h3>
                                    <p class="text-xs text-slate-600">Control the figures and text labels displayed across the 4 credibility boxes below the hero.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Counter 1 Value</label>
                                    <input type="text" name="counter_1_value" value="{{ old('counter_1_value', \App\Models\SiteSetting::get('counter_1_value', 'ETB 2.8B+')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Counter 1 Label</label>
                                    <input type="text" name="counter_1_label" value="{{ old('counter_1_label', \App\Models\SiteSetting::get('counter_1_label', 'Property Volume')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Counter 2 Label (Properties Sold)</label>
                                    <input type="text" name="counter_2_label" value="{{ old('counter_2_label', \App\Models\SiteSetting::get('counter_2_label', 'properties sold')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Counter 3 Label (Happy Buyers)</label>
                                    <input type="text" name="counter_3_label" value="{{ old('counter_3_label', \App\Models\SiteSetting::get('counter_3_label', 'happy buyers')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Counter 4 Value</label>
                                    <input type="text" name="counter_4_value" value="{{ old('counter_4_value', \App\Models\SiteSetting::get('counter_4_value', '18 Yrs')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Counter 4 Label</label>
                                    <input type="text" name="counter_4_label" value="{{ old('counter_4_label', \App\Models\SiteSetting::get('counter_4_label', 'market expertise')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Happy Buyers Base Count Offset</label>
                                    <p class="mb-2 text-xs text-slate-500">Baseline number added to registered client and buyer accounts to form the displayed happy buyers total.</p>
                                    <input type="number" name="happy_buyers_base_count" min="0" value="{{ old('happy_buyers_base_count', \App\Models\SiteSetting::get('happy_buyers_base_count', 0)) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-hero" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Homepage Hero
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-lifestyle" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Lifestyle &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 4: LIFESTYLE & AMENITIES (8 CARDS) -->
                        <div id="panel-lifestyle" data-settings-panel="panel-lifestyle" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 4 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Amenities Section</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Lifestyle &amp; Master-Planned Amenities (La Gare Inspired)</h3>
                                    <p class="text-xs text-slate-600">Customize the section title, description, and every single title and description for the 8 lifestyle amenity cards.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Amenities Section Badge</label>
                                    <input type="text" name="amenities_badge" value="{{ old('amenities_badge', \App\Models\SiteSetting::get('amenities_badge', 'Master-Planned Excellence')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Amenities Section Title</label>
                                    <input type="text" name="amenities_title" value="{{ old('amenities_title', \App\Models\SiteSetting::get('amenities_title', 'A Curated Lifestyle Beyond Ordinary')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Amenities Section Description</label>
                                    <textarea name="amenities_description" rows="2" maxlength="1500" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('amenities_description', \App\Models\SiteSetting::get('amenities_description', 'Inspired by premier world-class master communities like La Gare, every residence is enveloped in pristine green courtyards, luxury retail boulevards, and total peace of mind.')) }}</textarea>
                                </div>

                                {{-- Amenity 1 & 2 --}}
                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 1</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_1_title" value="{{ old('amenity_1_title', \App\Models\SiteSetting::get('amenity_1_title', 'Jogging & Walking Tracks')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_1_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_1_description', \App\Models\SiteSetting::get('amenity_1_description', 'Shaded, tree-lined pedestrian circuits and safe morning running tracks weaving through the development.')) }}</textarea>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 2</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_2_title" value="{{ old('amenity_2_title', \App\Models\SiteSetting::get('amenity_2_title', 'Lush Parks & Courtyards')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_2_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_2_description', \App\Models\SiteSetting::get('amenity_2_description', 'Manicured botanical gardens, peaceful open courtyards, and tranquil green sanctuaries for relaxation.')) }}</textarea>
                                    </div>
                                </div>

                                {{-- Amenity 3 & 4 --}}
                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 3</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_3_title" value="{{ old('amenity_3_title', \App\Models\SiteSetting::get('amenity_3_title', 'Retail & Dining Boulevard')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_3_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_3_description', \App\Models\SiteSetting::get('amenity_3_description', 'Curated fashion boutiques, specialty cafes, and gourmet international restaurants steps from your lobby.')) }}</textarea>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 4</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_4_title" value="{{ old('amenity_4_title', \App\Models\SiteSetting::get('amenity_4_title', 'Dedicated Cycling Tracks')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_4_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_4_description', \App\Models\SiteSetting::get('amenity_4_description', 'Protected, car-free cycling corridors connecting all residential clusters, green hubs, and community facilities.')) }}</textarea>
                                    </div>
                                </div>

                                {{-- Amenity 5 & 6 --}}
                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 5</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_5_title" value="{{ old('amenity_5_title', \App\Models\SiteSetting::get('amenity_5_title', 'Kids Play & Adventure')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_5_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_5_description', \App\Models\SiteSetting::get('amenity_5_description', 'Safe, modern play areas, soft-turf family recreation zones, and interactive splash fountains.')) }}</textarea>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 6</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_6_title" value="{{ old('amenity_6_title', \App\Models\SiteSetting::get('amenity_6_title', '24/7 Gated Security & Concierge')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_6_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_6_description', \App\Models\SiteSetting::get('amenity_6_description', 'Multi-tier biometric access, discrete on-site security patrols, CCTV surveillance, and VIP reception.')) }}</textarea>
                                    </div>
                                </div>

                                {{-- Amenity 7 & 8 --}}
                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 7</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_7_title" value="{{ old('amenity_7_title', \App\Models\SiteSetting::get('amenity_7_title', 'Wellness & Fitness Club')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_7_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_7_description', \App\Models\SiteSetting::get('amenity_7_description', 'State-of-the-art gym, restorative sauna and steam facilities, outdoor yoga deck, and swimming pool.')) }}</textarea>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-[#d9cab3] bg-[#fcfaf7] p-4">
                                    <h5 class="text-sm font-bold text-[#1d3c34]">Amenity 8</h5>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="amenity_8_title" value="{{ old('amenity_8_title', \App\Models\SiteSetting::get('amenity_8_title', '100% Power & Water Backup')) }}" placeholder="Title" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">
                                        <textarea name="amenity_8_description" rows="2" placeholder="Description" class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-2 text-sm focus:outline-none">{{ old('amenity_8_description', \App\Models\SiteSetting::get('amenity_8_description', 'Heavy-duty industrial generators and deep-well water reserve tanks guarantee zero disruption to your daily life.')) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-performance" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Performance
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-featured" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Featured Properties &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 5: FEATURED PROPERTIES -->
                        <div id="panel-featured" data-settings-panel="panel-featured" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 5 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Featured Properties</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Featured Properties Section</h3>
                                    <p class="text-xs text-slate-600">Header texts for the Featured Properties showcase.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Section Badge</label>
                                    <input type="text" name="featured_properties_badge" value="{{ old('featured_properties_badge', \App\Models\SiteSetting::get('featured_properties_badge', 'Properties')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Section Title</label>
                                    <input type="text" name="featured_properties_title" value="{{ old('featured_properties_title', \App\Models\SiteSetting::get('featured_properties_title', 'Featured Properties')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Link Text</label>
                                    <input type="text" name="featured_properties_link_text" value="{{ old('featured_properties_link_text', \App\Models\SiteSetting::get('featured_properties_link_text', 'View all')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-lifestyle" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Lifestyle
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-panorama" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Panorama &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 6: ARCHITECTURAL PANORAMA -->
                        <div id="panel-panorama" data-settings-panel="panel-panorama" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 6 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Panorama Banner</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Architectural Panorama Banner</h3>
                                    <p class="text-xs text-slate-600">Control the magnificent views visual banner, text, and VIP action buttons.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Panorama Badge</label>
                                    <input type="text" name="panorama_badge" value="{{ old('panorama_badge', \App\Models\SiteSetting::get('panorama_badge', 'Masterplan Skyline')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Panorama Title</label>
                                    <input type="text" name="panorama_title" value="{{ old('panorama_title', \App\Models\SiteSetting::get('panorama_title', 'Get Inspired by Magnificent Views & Elevated Living')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Panorama Description</label>
                                    <textarea name="panorama_description" rows="2" maxlength="1500" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('panorama_description', \App\Models\SiteSetting::get('panorama_description', 'Experience panoramic sunrises, sprawling green courtyards, and iconic architecture engineered to the highest international quality standards.')) }}</textarea>
                                </div>

                                <!-- Panorama Background Static Image Control -->
                                <div class="md:col-span-2 rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-5 shadow-xs">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div>
                                            <label class="block text-sm font-bold text-[#1d3c34]">Panorama Banner Background Image</label>
                                            <p class="text-xs text-slate-500">Wide architectural skyline photograph displayed across the full-width panorama section.</p>
                                        </div>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Panorama Image</span>
                                    </div>
                                    <div class="mt-4 flex flex-col md:flex-row items-start md:items-center gap-5">
                                        <div class="relative h-24 w-40 shrink-0 overflow-hidden rounded-xl border border-[#d9cab3] bg-white shadow-xs">
                                            <img id="preview-panorama-image" src="{{ \App\Models\SiteSetting::imageUrl('panorama_image', 'images/luxury/central-plaza.jpg') }}" alt="Panorama Preview" class="h-full w-full object-cover">
                                        </div>
                                        <div class="flex-1 space-y-2 w-full">
                                            <div class="flex items-center gap-3">
                                                <label for="panorama-image-input" class="cursor-pointer rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-[#254d43]">
                                                    Upload New Image
                                                </label>
                                                <span id="panorama-image-name" class="truncate text-xs text-slate-500">Current image in use</span>
                                            </div>
                                            <input id="panorama-image-input" type="file" name="panorama_image_file" accept="image/*" class="sr-only"
                                                onchange="document.getElementById('panorama-image-name').textContent = this.files.length ? this.files[0].name : 'Current image in use'; if (this.files && this.files[0]) { document.getElementById('preview-panorama-image').src = URL.createObjectURL(this.files[0]); }">
                                            <div class="flex items-center gap-2 pt-1">
                                                <span class="text-xs text-slate-500 whitespace-nowrap">Or Image URL:</span>
                                                <input type="text" name="panorama_image" value="{{ old('panorama_image', \App\Models\SiteSetting::get('panorama_image')) }}" placeholder="e.g. https://... or images/luxury/central-plaza.jpg" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Primary Button Text</label>
                                    <input type="text" name="panorama_primary_button_text" value="{{ old('panorama_primary_button_text', \App\Models\SiteSetting::get('panorama_primary_button_text', 'Schedule Private Tour')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Secondary Button Text</label>
                                    <input type="text" name="panorama_secondary_button_text" value="{{ old('panorama_secondary_button_text', \App\Models\SiteSetting::get('panorama_secondary_button_text', 'View Masterplan Units')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-featured" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Featured Properties
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-developments" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Developments &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 7: DEVELOPMENTS & PROJECTS -->
                        <div id="panel-developments" data-settings-panel="panel-developments" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 7 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Developments</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Featured Projects &amp; Developments</h3>
                                    <p class="text-xs text-slate-600">Manage the Developments section header, badges, and explore link on the homepage.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Section Badge</label>
                                    <input type="text" name="developments_badge" value="{{ old('developments_badge', \App\Models\SiteSetting::get('developments_badge', 'Developments')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Section Title</label>
                                    <input type="text" name="developments_title" value="{{ old('developments_title', \App\Models\SiteSetting::get('developments_title', 'Featured Projects')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Link Text</label>
                                    <input type="text" name="developments_link_text" value="{{ old('developments_link_text', \App\Models\SiteSetting::get('developments_link_text', 'Explore developments')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Link URL</label>
                                    <input type="text" name="developments_link_url" value="{{ old('developments_link_url', \App\Models\SiteSetting::get('developments_link_url', route('developments.index'))) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div class="md:col-span-2 rounded-2xl border border-[#d9cab3] bg-[#fcfaf7] p-5 shadow-xs">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="rounded bg-[#102b25] px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">Live Developments</span>
                                                <h4 class="text-base font-bold text-[#1d3c34]">Dynamic Master Projects &amp; Off-Plan Inventories</h4>
                                            </div>
                                            <p class="mt-1 text-xs text-slate-600">Individual development projects (residential towers, complexes, milestone timelines, unit pricing, and photos) are now dynamically managed with full CRUD and inventory tracking.</p>
                                        </div>
                                        <a href="#developments" onclick="showDashboardSection('developments')" class="inline-flex items-center gap-1.5 rounded-full bg-[#102b25] px-4 py-2 text-xs font-bold text-[#d4af37] hover:bg-[#1d3c34] transition shrink-0 cursor-pointer">
                                            <span>Manage Developments</span>
                                            <span>&rarr;</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-panorama" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Panorama
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-latest" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Latest Properties &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 8: LATEST PROPERTIES -->
                        <div id="panel-latest" data-settings-panel="panel-latest" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 8 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Latest Listings</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Latest Properties Section</h3>
                                    <p class="text-xs text-slate-600">Header texts for the Latest Properties section.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Section Badge</label>
                                    <input type="text" name="latest_properties_badge" value="{{ old('latest_properties_badge', \App\Models\SiteSetting::get('latest_properties_badge', 'New listings')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Section Title</label>
                                    <input type="text" name="latest_properties_title" value="{{ old('latest_properties_title', \App\Models\SiteSetting::get('latest_properties_title', 'Latest Properties')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Link Text</label>
                                    <input type="text" name="latest_properties_link_text" value="{{ old('latest_properties_link_text', \App\Models\SiteSetting::get('latest_properties_link_text', 'Browse more')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-developments" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Developments
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-about" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: About &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 9: ABOUT & HERITAGE -->
                        <div id="panel-about" data-settings-panel="panel-about" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 9 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">About Section</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">About &amp; Heritage Section</h3>
                                    <p class="text-xs text-slate-600">About section story, pillars, and image caption.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">About Badge</label>
                                    <input type="text" name="about_badge" value="{{ old('about_badge', \App\Models\SiteSetting::get('about_badge', 'About '.($siteBrand['name'] ?? 'GTM Real Estate'))) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">About Title</label>
                                    <input type="text" name="about_title" value="{{ old('about_title', \App\Models\SiteSetting::get('about_title', 'Trusted guidance for every move')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">About Description</label>
                                    <textarea name="about_description" rows="3" maxlength="2000" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('about_description', \App\Models\SiteSetting::get('about_description', 'We help buyers, sellers, and investors discover exceptional properties with trust, transparency, and local expertise. From first viewing to final paperwork, we make every step clear and confident.')) }}</textarea>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Pillar 1 Title</label>
                                    <input type="text" name="about_pillar_1_title" value="{{ old('about_pillar_1_title', \App\Models\SiteSetting::get('about_pillar_1_title', '100%')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Pillar 1 Description</label>
                                    <input type="text" name="about_pillar_1_desc" value="{{ old('about_pillar_1_desc', \App\Models\SiteSetting::get('about_pillar_1_desc', 'Clear Legal Titles')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Pillar 2 Title</label>
                                    <input type="text" name="about_pillar_2_title" value="{{ old('about_pillar_2_title', \App\Models\SiteSetting::get('about_pillar_2_title', 'VIP')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Pillar 2 Description</label>
                                    <input type="text" name="about_pillar_2_desc" value="{{ old('about_pillar_2_desc', \App\Models\SiteSetting::get('about_pillar_2_desc', 'Concierge Advisory')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Image Subtitle</label>
                                    <input type="text" name="about_image_subtitle" value="{{ old('about_image_subtitle', \App\Models\SiteSetting::get('about_image_subtitle', 'Heritage of Distinction')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Image Title</label>
                                    <input type="text" name="about_image_title" value="{{ old('about_image_title', \App\Models\SiteSetting::get('about_image_title', 'Addis Ababa & Harar Master Developments')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <!-- About Showcase Static Image Control -->
                                <div class="md:col-span-2 rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-5 shadow-xs">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div>
                                            <label class="block text-sm font-bold text-[#1d3c34]">About Heritage Showcase Image</label>
                                            <p class="text-xs text-slate-500">The photograph displayed in the luxury showcase card next to the About story.</p>
                                        </div>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">About Photo</span>
                                    </div>
                                    <div class="mt-4 flex flex-col md:flex-row items-start md:items-center gap-5">
                                        <div class="relative h-24 w-40 shrink-0 overflow-hidden rounded-xl border border-[#d9cab3] bg-white shadow-xs">
                                            <img id="preview-about-image" src="{{ \App\Models\SiteSetting::imageUrl('about_image', 'images/luxury/retail-boulevard.jpg') }}" alt="About Preview" class="h-full w-full object-cover">
                                        </div>
                                        <div class="flex-1 space-y-2 w-full">
                                            <div class="flex items-center gap-3">
                                                <label for="about-image-input" class="cursor-pointer rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-[#254d43]">
                                                    Upload New Image
                                                </label>
                                                <span id="about-image-name" class="truncate text-xs text-slate-500">Current image in use</span>
                                            </div>
                                            <input id="about-image-input" type="file" name="about_image_file" accept="image/*" class="sr-only"
                                                onchange="document.getElementById('about-image-name').textContent = this.files.length ? this.files[0].name : 'Current image in use'; if (this.files && this.files[0]) { document.getElementById('preview-about-image').src = URL.createObjectURL(this.files[0]); }">
                                            <div class="flex items-center gap-2 pt-1">
                                                <span class="text-xs text-slate-500 whitespace-nowrap">Or Image URL:</span>
                                                <input type="text" name="about_image" value="{{ old('about_image', \App\Models\SiteSetting::get('about_image')) }}" placeholder="e.g. https://... or images/luxury/retail-boulevard.jpg" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-latest" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Latest Properties
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-news" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Blog &amp; News &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 10: BLOG, NEWS & CAREERS -->
                        <div id="panel-news" data-settings-panel="panel-news" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 10 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Blog &amp; News</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Blog, News &amp; Careers</h3>
                                    <p class="text-xs text-slate-600">Configure editorial badge, title, and link text.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">News Badge</label>
                                    <input type="text" name="news_badge" value="{{ old('news_badge', \App\Models\SiteSetting::get('news_badge', 'Blog & News')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">News Title</label>
                                    <input type="text" name="news_title" value="{{ old('news_title', \App\Models\SiteSetting::get('news_title', 'Latest News')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">View All Link Text</label>
                                    <input type="text" name="news_link_text" value="{{ old('news_link_text', \App\Models\SiteSetting::get('news_link_text', 'View all')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-about" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: About &amp; Heritage
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-testimonials" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Testimonials &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 11: TESTIMONIALS -->
                        <div id="panel-testimonials" data-settings-panel="panel-testimonials" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 11 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Testimonials</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Testimonials Section</h3>
                                    <p class="text-xs text-slate-600">Client review banner badge and title.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Testimonials Badge</label>
                                    <input type="text" name="testimonials_badge" value="{{ old('testimonials_badge', \App\Models\SiteSetting::get('testimonials_badge', 'Client feedback')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Testimonials Title</label>
                                    <input type="text" name="testimonials_title" value="{{ old('testimonials_title', \App\Models\SiteSetting::get('testimonials_title', 'Testimonials')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-news" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Blog &amp; News
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-agents" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Agents &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 12: AGENTS -->
                        <div id="panel-agents" data-settings-panel="panel-agents" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 12 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Agents</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Agents Section</h3>
                                    <p class="text-xs text-slate-600">Configure team showcase badges and titles.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Agents Badge</label>
                                    <input type="text" name="agents_badge" value="{{ old('agents_badge', \App\Models\SiteSetting::get('agents_badge', 'Our agents')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Agents Title</label>
                                    <input type="text" name="agents_title" value="{{ old('agents_title', \App\Models\SiteSetting::get('agents_title', 'Real people, local expertise')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Agents Link Text</label>
                                    <input type="text" name="agents_link_text" value="{{ old('agents_link_text', \App\Models\SiteSetting::get('agents_link_text', 'All agents')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-testimonials" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Testimonials
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-contact" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: VIP Contact &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 13: VIP CONTACT SUITE -->
                        <div id="panel-contact" data-settings-panel="panel-contact" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 13 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Contact Section</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">VIP Contact &amp; Consultation Suite</h3>
                                    <p class="text-xs text-slate-600">Heading, descriptive copy, benefit bullet points, and button text.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Badge</label>
                                    <input type="text" name="contact_badge" value="{{ old('contact_badge', \App\Models\SiteSetting::get('contact_badge', 'Contact')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Title</label>
                                    <input type="text" name="contact_title" value="{{ old('contact_title', \App\Models\SiteSetting::get('contact_title', 'Let’s find your next address')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Description</label>
                                    <textarea name="contact_description" rows="2" maxlength="1500" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">{{ old('contact_description', \App\Models\SiteSetting::get('contact_description', 'Register your interest to schedule a private tour or discuss prime residential and commercial investment opportunities.')) }}</textarea>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Side Box Title</label>
                                    <input type="text" name="contact_box_title" value="{{ old('contact_box_title', \App\Models\SiteSetting::get('contact_box_title', 'Speak with an agent')) }}" maxlength="255" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Submit Button Text</label>
                                    <input type="text" name="contact_submit_text" value="{{ old('contact_submit_text', \App\Models\SiteSetting::get('contact_submit_text', 'Send Message')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Side Box Description</label>
                                    <textarea name="contact_box_description" rows="2" maxlength="1500" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">{{ old('contact_box_description', \App\Models\SiteSetting::get('contact_box_description', 'Share your property goals and our senior consultants will prepare a curated portfolio matching your requirements, preferred timing, and financing criteria.')) }}</textarea>
                                </div>

                                <div class="md:col-span-2 space-y-3">
                                    <label class="block text-sm font-semibold text-slate-700">3 Trust / Guarantee Checklist Points</label>
                                    <input type="text" name="contact_benefit_1" value="{{ old('contact_benefit_1', \App\Models\SiteSetting::get('contact_benefit_1', 'Dedicated VIP Property Consultant assigned immediately')) }}" placeholder="Benefit 1" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none text-sm">
                                    <input type="text" name="contact_benefit_2" value="{{ old('contact_benefit_2', \App\Models\SiteSetting::get('contact_benefit_2', 'On-site or virtual architectural masterplan walkthrough')) }}" placeholder="Benefit 2" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none text-sm">
                                    <input type="text" name="contact_benefit_3" value="{{ old('contact_benefit_3', \App\Models\SiteSetting::get('contact_benefit_3', 'Full verified title deed transparency guaranteed')) }}" placeholder="Benefit 3" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none text-sm">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-agents" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Agents
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-newsletter" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Concierge &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 14: FLOATING CONCIERGE -->
                        <div id="panel-newsletter" data-settings-panel="panel-newsletter" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 14 of 14</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Footer &amp; Dock</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Floating Concierge Dock</h3>
                                    <p class="text-xs text-slate-600">Bottom-right floating VIP concierge button and dock links.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Floating Concierge Dock Button Text</label>
                                    <input type="text" name="concierge_button_text" value="{{ old('concierge_button_text', \App\Models\SiteSetting::get('concierge_button_text', 'VIP Tour')) }}" maxlength="100" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-contact" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: VIP Contact
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                    <button type="button" data-settings-jump="panel-currency" class="rounded-full bg-[#2d5d4d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1d3c34] transition">
                                        Next: Currency &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 16: CURRENCY & EXCHANGE RATES -->
                        <div id="panel-currency" data-settings-panel="panel-currency" class="settings-tab-panel hidden space-y-6 rounded-2xl border border-[#e7ddca] bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Section 16 of 16</span>
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-0.5 text-xs font-bold text-[#1d3c34]">Currency</span>
                                    </div>
                                    <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Currency &amp; Exchange Rates</h3>
                                    <p class="text-xs text-slate-600">Set how many Ethiopian Birr (ETB) one unit of each foreign currency is worth. The header currency switcher converts every property price with these rates.</p>
                                </div>
                                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                    Save Settings
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">1 USD = ? ETB</label>
                                    <input type="number" step="0.0001" min="0.0001" name="currency_rate_usd" value="{{ old('currency_rate_usd', \App\Models\SiteSetting::get('currency_rate_usd', 125)) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">1 EUR = ? ETB</label>
                                    <input type="number" step="0.0001" min="0.0001" name="currency_rate_eur" value="{{ old('currency_rate_eur', \App\Models\SiteSetting::get('currency_rate_eur', 135)) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">1 GBP = ? ETB</label>
                                    <input type="number" step="0.0001" min="0.0001" name="currency_rate_gbp" value="{{ old('currency_rate_gbp', \App\Models\SiteSetting::get('currency_rate_gbp', 160)) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">1 AED = ? ETB</label>
                                    <input type="number" step="0.0001" min="0.0001" name="currency_rate_aed" value="{{ old('currency_rate_aed', \App\Models\SiteSetting::get('currency_rate_aed', 34)) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#e7ddca] pt-4 mt-6">
                                <button type="button" data-settings-jump="panel-newsletter" class="rounded-full border border-[#d9cab3] bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                    &larr; Prev: Floating Concierge
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        Save Settings
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </section>

