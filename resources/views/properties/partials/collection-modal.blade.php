@auth
<div id="save-to-collection-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-md rounded-3xl bg-white p-6 md:p-8 shadow-2xl border border-amber-100">
        <button type="button" onclick="document.getElementById('save-to-collection-modal').classList.add('hidden')" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            </div>
            <div>
                <h3 class="text-xl font-black text-[#1d3c34]">Save to Collection</h3>
                <p class="text-xs text-slate-500">Organize your favorite listings into custom investment portfolios.</p>
            </div>
        </div>

        {{-- Existing Collections List --}}
        @php
            $userCollections = auth()->user()->savedCollections()->with('properties')->get();
        @endphp

        <div class="mt-6 space-y-2.5 max-h-48 overflow-y-auto pr-1">
            @forelse ($userCollections as $col)
                @php
                    $isSaved = $col->properties->contains($property->id);
                @endphp
                <form method="POST" action="{{ route('collections.toggleProperty', [$col, $property]) }}" class="flex items-center justify-between p-3 rounded-xl border {{ $isSaved ? 'border-emerald-300 bg-emerald-50/60' : 'border-[#ecd9be] bg-[#fffaf2]' }}">
                    @csrf
                    <div>
                        <span class="font-bold text-xs text-[#1d3c34] block">{{ $col->name }}</span>
                        <span class="text-[10px] text-slate-500">{{ $col->properties->count() }} {{ Str::plural('property', $col->properties->count()) }}</span>
                    </div>
                    <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-black {{ $isSaved ? 'bg-emerald-700 text-white' : 'bg-[#102b25] text-[#d4af37]' }}">
                        {{ $isSaved ? '✓ Saved' : '+ Add' }}
                    </button>
                </form>
            @empty
                <p class="text-xs text-slate-500 italic text-center py-2">No collections created yet. Start one below!</p>
            @endforelse
        </div>

        {{-- Create New Collection Form --}}
        <div class="mt-6 border-t border-[#f0e4cf] pt-4">
            <h4 class="text-xs font-black uppercase tracking-wider text-[#1d3c34]">Create New Collection</h4>
            <form method="POST" action="{{ route('collections.store') }}" class="mt-3 space-y-3">
                @csrf
                <input type="hidden" name="property_id" value="{{ $property->id }}">
                <div>
                    <input type="text" name="name" required placeholder="Collection name (e.g. Bole 4-Bed Villas)" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                </div>
                <div>
                    <input type="text" name="description" placeholder="Optional description or investment target..." class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                </div>
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
                        <input type="checkbox" name="is_public" value="1" class="rounded text-[#102b25]">
                        <span>Publicly shareable collection</span>
                    </label>
                    <button type="submit" class="rounded-xl bg-[#102b25] px-4 py-2 text-xs font-black uppercase tracking-wider text-[#d4af37] hover:bg-[#1d3c34]">
                        Create & Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endauth
