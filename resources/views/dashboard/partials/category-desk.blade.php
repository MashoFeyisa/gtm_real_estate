<section id="categories" data-section="categories" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
    <div class="flex flex-col gap-4 border-b border-[#e7ddca] pb-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-[#1d3c34] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-white">Master Data</span>
                <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold text-[#1d3c34]">{{ $propertyCategories->count() }} categories · {{ $listingTypes->count() }} listing types</span>
            </div>
            <h2 class="mt-2 text-2xl md:text-3xl font-black text-[#1d3c34]">Category Management</h2>
            <p class="mt-1 text-sm text-slate-600">Database-configurable property categories, listing types, and the five transaction modes: BUY, RENT, LEASE, STAY, and NEW PROJECTS. Every form on the platform reads from here.</p>
        </div>
    </div>

    @if ($errors->has('category') || $errors->has('listing_type'))
        <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">
            {{ $errors->first('category') ?: $errors->first('listing_type') }}
        </div>
    @endif

    {{-- Transaction Modes Overview --}}
    <div class="mt-6 grid gap-3 sm:grid-cols-3 lg:grid-cols-5">
        @foreach ($transactionModes as $modeKey => $modeLabel)
            <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf8f3] p-4">
                <p class="text-sm font-black text-[#1d3c34]">{{ $modeLabel }}</p>
                <p class="mt-1 text-[11px] text-slate-500">{{ $listingTypes->where('mode', $modeKey)->count() }} listing type(s)</p>
            </div>
        @endforeach
    </div>

    {{-- Create Category --}}
    <div class="mt-8 rounded-2xl border border-[#d9cab3] bg-[#f9f4ed] p-5">
        <h3 class="text-sm font-black uppercase tracking-wider text-[#1d3c34]">Add Property Category</h3>
        <form action="{{ route('admin.categories.store') }}" method="POST" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @csrf
            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Category group</label>
                <select name="group" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
                    @foreach ($categoryGroups as $group)
                        <option value="{{ $group }}">{{ $group }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Name</label>
                <input type="text" name="name" required maxlength="120" placeholder="e.g. Beach house" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Icon (emoji)</label>
                <input type="text" name="icon" maxlength="30" placeholder="🏠" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Sort order</label>
                <input type="number" name="sort_order" min="0" max="9999" value="0" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-[#1d3c34] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43] cursor-pointer">Add Category</button>
            </div>
        </form>
    </div>

    {{-- Category Groups --}}
    @foreach ($propertyCategories->groupBy('group') as $group => $groupCategories)
        <div class="mt-8">
            <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-wider text-[#1d3c34]">
                <span>{{ $groupCategories->first()->icon ?? '🏷️' }}</span> {{ $group }}
                <span class="rounded-full bg-[#edf2ee] px-2 py-0.5 text-[10px] font-bold text-[#587165]">{{ $groupCategories->count() }}</span>
            </h3>
            <div class="mt-3 space-y-2.5">
                @foreach ($groupCategories as $category)
                    <div class="flex flex-col gap-2 rounded-2xl border border-[#e7ddca] bg-[#fbf8f3] p-3 lg:flex-row lg:items-center">
                        <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="grid flex-1 gap-2 sm:grid-cols-2 lg:grid-cols-5">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $category->name }}" required maxlength="120" class="rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-sm">
                            <select name="group" class="rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-sm">
                                @foreach ($categoryGroups as $groupOption)
                                    <option value="{{ $groupOption }}" {{ $category->group === $groupOption ? 'selected' : '' }}>{{ $groupOption }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="icon" value="{{ $category->icon }}" maxlength="30" placeholder="🏠" class="rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-sm text-center">
                            <div class="flex items-center gap-2">
                                <input type="number" name="sort_order" value="{{ $category->sort_order }}" min="0" max="9999" class="w-20 rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-sm">
                                <label class="flex items-center gap-1.5 text-[11px] font-bold text-slate-600">
                                    <input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34]">
                                    Active
                                </label>
                            </div>
                            <button type="submit" class="rounded-xl bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43] cursor-pointer">Save</button>
                        </form>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete category &quot;{{ $category->name }}&quot;?')" class="lg:ml-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full rounded-xl border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-50 cursor-pointer lg:w-auto">Delete</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- Listing Types --}}
    <div class="mt-10 border-t border-[#e7ddca] pt-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-2xl font-black text-[#1d3c34]">Listing Types &amp; Transaction Modes</h3>
                <p class="mt-1 text-sm text-slate-600">Every listing type belongs to one of the five marketplace modes.</p>
            </div>
        </div>

        <div class="mt-5 rounded-2xl border border-[#d9cab3] bg-[#f9f4ed] p-5">
            <h4 class="text-sm font-black uppercase tracking-wider text-[#1d3c34]">Add Listing Type</h4>
            <form action="{{ route('admin.listing-types.store') }}" method="POST" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                @csrf
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Transaction mode</label>
                    <select name="mode" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
                        @foreach ($transactionModes as $modeKey => $modeLabel)
                            <option value="{{ $modeKey }}">{{ $modeLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Name</label>
                    <input type="text" name="name" required maxlength="120" placeholder="e.g. For Sale" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Description</label>
                    <input type="text" name="description" maxlength="500" placeholder="Optional note" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Sort order</label>
                    <input type="number" name="sort_order" min="0" max="9999" value="0" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-xl bg-[#1d3c34] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43] cursor-pointer">Add Listing Type</button>
                </div>
            </form>
        </div>

        @foreach ($listingTypes->groupBy('mode') as $modeKey => $modeTypes)
            <div class="mt-6">
                <h4 class="flex items-center gap-2 text-sm font-black uppercase tracking-wider text-[#1d3c34]">
                    <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#d4af37]">{{ $transactionModes[$modeKey] ?? $modeKey }}</span>
                    <span class="text-[11px] font-bold text-slate-500">{{ $modeTypes->count() }} type(s)</span>
                </h4>
                <div class="mt-3 space-y-2.5">
                    @foreach ($modeTypes as $listingType)
                        <div class="flex flex-col gap-2 rounded-2xl border border-[#e7ddca] bg-[#fbf8f3] p-3 lg:flex-row lg:items-center">
                            <form action="{{ route('admin.listing-types.update', $listingType) }}" method="POST" class="grid flex-1 gap-2 sm:grid-cols-2 lg:grid-cols-5">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $listingType->name }}" required maxlength="120" class="rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-sm">
                                <select name="mode" class="rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-sm">
                                    @foreach ($transactionModes as $modeOptionKey => $modeOptionLabel)
                                        <option value="{{ $modeOptionKey }}" {{ $listingType->mode === $modeOptionKey ? 'selected' : '' }}>{{ $modeOptionLabel }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="description" value="{{ $listingType->description }}" maxlength="500" class="rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <input type="number" name="sort_order" value="{{ $listingType->sort_order }}" min="0" max="9999" class="w-20 rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-sm">
                                    <label class="flex items-center gap-1.5 text-[11px] font-bold text-slate-600">
                                        <input type="checkbox" name="is_active" value="1" {{ $listingType->is_active ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34]">
                                        Active
                                    </label>
                                </div>
                                <button type="submit" class="rounded-xl bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43] cursor-pointer">Save</button>
                            </form>
                            <form action="{{ route('admin.listing-types.destroy', $listingType) }}" method="POST" onsubmit="return confirm('Delete listing type &quot;{{ $listingType->name }}&quot;?')" class="lg:ml-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full rounded-xl border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-50 cursor-pointer lg:w-auto">Delete</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</section>
