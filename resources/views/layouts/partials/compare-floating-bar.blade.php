@php
    $globalComparedIds = session('compared_property_ids', []);
    $globalComparedCount = count($globalComparedIds);
    $globalCompareQuery = !empty($globalComparedIds) ? ['ids' => implode(',', $globalComparedIds)] : [];
@endphp

{{-- Global Floating Compare Matrix Bottom Bar --}}
<div id="floating-compare-bar" class="fixed bottom-6 inset-x-0 z-40 mx-auto max-w-lg px-4 transition-all duration-300 {{ $globalComparedCount > 0 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8 pointer-events-none' }}">
    <div class="flex items-center justify-between gap-4 rounded-2xl border border-[#d4af37]/80 bg-[#102b25]/95 px-5 py-3.5 text-white shadow-2xl backdrop-blur-md ring-1 ring-black/40">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#d4af37] text-[#102b25] shadow-md">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            </div>
            <div>
                <p class="text-sm font-black text-[#fdf8f0]">
                    <span class="compare-matrix-counter">Compare Matrix ({{ $globalComparedCount }})</span>
                </p>
                <p class="text-[11px] text-[#d9cab3]">Marked for side-by-side evaluation (max 4)</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('properties.compare', $globalCompareQuery) }}" class="compare-matrix-link inline-flex items-center gap-2 rounded-xl bg-[#d4af37] px-4 py-2 text-xs font-black text-[#102b25] shadow-md transition hover:bg-[#e2c159] active:scale-95 cursor-pointer">
                <span>View Matrix</span>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </a>
        </div>
    </div>
</div>
