{{-- Multi-Language Switcher Component (Powered by Google Translate) --}}
<div class="relative inline-block text-left" data-language-switcher-wrapper>
    <button type="button" class="language-dropdown-toggle flex items-center gap-1.5 rounded-full border border-[#b9a98b] bg-white/90 px-3 py-1.5 text-xs font-bold text-[#1d3c34] transition hover:bg-[#f0e4cf] shadow-2xs cursor-pointer" aria-label="Select Language">
        <span data-language-active-flag>🇬🇧</span>
        <span data-language-active-code>EN</span>
        <svg class="h-3 w-3 text-[#1d3c34]/70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div class="language-dropdown-menu hidden absolute right-0 mt-2 w-48 rounded-2xl border border-[#d9cab3] bg-white p-1.5 shadow-xl z-50">
        <div class="px-2 py-1 text-[10px] font-black uppercase tracking-wider text-[#587165]">Language</div>
        <button type="button" data-set-language="en" class="flex w-full items-center justify-between rounded-xl px-2.5 py-1.5 text-xs font-semibold text-[#1d3c34] hover:bg-[#f3eadc] transition cursor-pointer">
            <span class="flex items-center gap-2"><span>🇬🇧</span> <span>English</span></span>
            <span class="text-[10px] text-slate-400 font-mono">EN</span>
        </button>
        <button type="button" data-set-language="am" class="flex w-full items-center justify-between rounded-xl px-2.5 py-1.5 text-xs font-semibold text-[#1d3c34] hover:bg-[#f3eadc] transition cursor-pointer">
            <span class="flex items-center gap-2"><span>🇪🇹</span> <span>አማርኛ (Amharic)</span></span>
            <span class="text-[10px] text-slate-400 font-mono">አማ</span>
        </button>
        <button type="button" data-set-language="om" class="flex w-full items-center justify-between rounded-xl px-2.5 py-1.5 text-xs font-semibold text-[#1d3c34] hover:bg-[#f3eadc] transition cursor-pointer">
            <span class="flex items-center gap-2"><span>🇪🇹</span> <span>Afaan Oromoo</span></span>
            <span class="text-[10px] text-slate-400 font-mono">OM</span>
        </button>
        {{-- Hidden Google Translate Element Anchor (No visible widget or icons) --}}
        <div class="google_translate_element hidden" style="display: none !important;"></div>
    </div>
</div>
