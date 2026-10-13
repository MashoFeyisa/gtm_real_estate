        @php
            $editingProperty = request()->query('edit_property');
            $propertyToEdit = $editingProperty ? ($properties->firstWhere('id', (int) $editingProperty) ?? \App\Models\Property::find((int) $editingProperty)) : null;
        @endphp

        <section id="properties" data-section="properties" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-2xl font-black text-[#1d3c34]">Property Management</h2>
                <button type="button" class="toggle-create-form rounded-full bg-[#1d3c34] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]" data-target="properties-create-panel" data-label-create="+ Add Property" data-label-close="Close Form">+ Add Property</button>
            </div>

            <div id="properties-create-panel" class="create-form-panel mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#f9f4ed] p-5 {{ $propertyToEdit ? '' : 'hidden' }}">
                @if ($propertyToEdit)
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h3 class="text-xl font-black text-[#1d3c34]">Edit Property</h3>
                        <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-[#2d5d4d]">Cancel</a>
                    </div>
                @else
                    <h3 class="text-xl font-black text-[#1d3c34]">Create Property</h3>
                @endif

                <form method="POST" action="{{ $propertyToEdit ? route('dashboard.properties.update', $propertyToEdit) : route('dashboard.properties.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf
                    @if ($propertyToEdit)
                        @method('PUT')
                    @endif

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                        <input type="text" name="title" value="{{ old('title', $propertyToEdit?->title) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                        <textarea name="description" rows="4" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('description', $propertyToEdit?->description) }}</textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Price (ETB)</label>
                        <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $propertyToEdit?->price) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Type</label>
                        <select name="type" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="sale" {{ old('type', $propertyToEdit?->type) === 'sale' ? 'selected' : '' }}>Sale</option>
                            <option value="rent" {{ old('type', $propertyToEdit?->type) === 'rent' ? 'selected' : '' }}>Rent</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Category</label>
                        @php
                            $currentCat = old('category', $propertyToEdit?->property_category ?? $propertyToEdit?->category ?? 'home');
                        @endphp
                        <select name="category" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            @php
                                $legacyCategoryOptions = [
                                    'home' => 'Home / House',
                                    'villa' => 'Villa',
                                    'apartment' => 'Apartment',
                                    'condominium' => 'Condominium',
                                    'guest_house' => 'Guest House (Short Stay)',
                                    'hotel' => 'Hotel (Short Stay)',
                                ];
                                $categoryAliases = [
                                    'house' => ['home'],
                                    'condominium' => ['condo'],
                                    'guesthouse' => ['guest_house'],
                                    'guest_house' => ['guesthouse'],
                                    'hotel' => ['hotels'],
                                ];
                                $deskCategories = ($propertyCategories ?? collect())->where('is_active');
                            @endphp
                            @if ($deskCategories->isNotEmpty())
                                @foreach ($deskCategories->groupBy('group') as $categoryGroup => $categoryGroupOptions)
                                    <optgroup label="{{ $categoryGroup }}">
                                        @foreach ($categoryGroupOptions as $categoryOption)
                                            <option value="{{ $categoryOption->slug }}" {{ in_array($currentCat, array_merge([$categoryOption->slug, $categoryOption->name], $categoryAliases[$categoryOption->slug] ?? []), true) ? 'selected' : '' }}>{{ $categoryOption->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                                @unless ($deskCategories->pluck('slug')->contains($currentCat))
                                    <option value="{{ $currentCat }}" selected>Legacy: {{ ucwords(str_replace('_', ' ', (string) $currentCat)) }}</option>
                                @endunless
                            @else
                                @foreach ($legacyCategoryOptions as $legacyCategoryValue => $legacyCategoryLabel)
                                    <option value="{{ $legacyCategoryValue }}" {{ in_array($currentCat, array_merge([$legacyCategoryValue], $categoryAliases[$legacyCategoryValue] ?? []), true) ? 'selected' : '' }}>{{ $legacyCategoryLabel }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Assigned Agent</label>
                        <select name="agent_id" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="">— No agent (orders go to admin only) —</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}" {{ (string) old('agent_id', $propertyToEdit?->agent_id) === (string) $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Bedrooms</label>
                        <input type="number" name="bedrooms" min="0" value="{{ old('bedrooms', $propertyToEdit?->bedrooms) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Bathrooms</label>
                        <input type="number" name="bathrooms" min="0" value="{{ old('bathrooms', $propertyToEdit?->bathrooms) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Area (sq ft)</label>
                        <input type="number" name="area" min="0" value="{{ old('area', $propertyToEdit?->area) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Status</label>
                        <select name="status" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="draft" {{ old('status', $propertyToEdit?->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $propertyToEdit?->status) === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="available" {{ old('status', $propertyToEdit?->status) === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="sold" {{ old('status', $propertyToEdit?->status) === 'sold' ? 'selected' : '' }}>Sold</option>
                            <option value="archived" {{ old('status', $propertyToEdit?->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">City</label>
                        <input type="text" name="city" value="{{ old('city', $propertyToEdit?->city) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Address</label>
                        <input type="text" name="address" value="{{ old('address', $propertyToEdit?->address) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    @include('layouts.partials.location-picker', [
                        'pickerId' => 'admin-property-location-map',
                        'latInputId' => 'admin-property-latitude',
                        'lngInputId' => 'admin-property-longitude',
                        'lat' => old('latitude', $propertyToEdit?->latitude),
                        'lng' => old('longitude', $propertyToEdit?->longitude),
                        'pickerHelp' => 'Click the map or drag the pin so this property plots correctly on the public map.',
                    ])

                    {{-- Ethiopian Physical Specifications & Utility Infrastructure (Module 1) --}}
                    <div class="md:col-span-2 rounded-2xl border border-[#ecd9be] bg-[#fffaf2] p-4.5">
                        <div class="flex items-center justify-between mb-3 border-b border-[#ecd9be] pb-2.5">
                            <div>
                                <h4 class="text-sm font-black text-[#1d3c34]">Physical Specifications & Utilities</h4>
                                <p class="text-xs text-slate-500">Verified architectural parameters and infrastructure readiness</p>
                            </div>
                            <span class="rounded-full bg-[#102b25]/10 px-2.5 py-0.5 text-[11px] font-mono font-bold text-[#102b25]">
                                Verified Specifications
                            </span>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-4">
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Building Area (sqm)</label>
                                <input type="number" name="building_area" min="0" value="{{ old('building_area', $propertyToEdit?->building_area) }}" placeholder="e.g. 400" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Stories / Total Floors</label>
                                <input type="number" name="floors" min="0" value="{{ old('floors', $propertyToEdit?->floors ?? 1) }}" placeholder="e.g. 1 for G+0" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Parking Spaces</label>
                                <input type="number" name="parking_spaces" min="0" value="{{ old('parking_spaces', $propertyToEdit?->parking_spaces ?? 1) }}" placeholder="e.g. 1" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Asset Condition</label>
                                <select name="condition" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                    <option value="new" {{ old('condition', $propertyToEdit?->condition) === 'new' ? 'selected' : '' }}>Brand New / Off-Plan</option>
                                    <option value="excellent" {{ old('condition', $propertyToEdit?->condition ?? 'excellent') === 'excellent' ? 'selected' : '' }}>Excellent</option>
                                    <option value="good" {{ old('condition', $propertyToEdit?->condition) === 'good' ? 'selected' : '' }}>Good</option>
                                    <option value="renovated" {{ old('condition', $propertyToEdit?->condition) === 'renovated' ? 'selected' : '' }}>Renovated</option>
                                    <option value="under_construction" {{ old('condition', $propertyToEdit?->condition) === 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-[#ecd9be]">
                            <label class="block text-xs font-black uppercase tracking-wider text-[#587165] mb-2">Ethiopian Utility Readiness</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs font-semibold text-slate-700">
                                <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                    <input type="checkbox" name="has_electricity" value="1" {{ old('has_electricity', $propertyToEdit?->has_electricity ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <span>3-Phase / Electric</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                    <input type="checkbox" name="has_water" value="1" {{ old('has_water', $propertyToEdit?->has_water ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <span>Municipal Water Line</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                    <input type="checkbox" name="has_water_tank" value="1" {{ old('has_water_tank', $propertyToEdit?->has_water_tank ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <span>Water Reservoir Tank</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                    <input type="checkbox" name="has_generator" value="1" {{ old('has_generator', $propertyToEdit?->has_generator ?? false) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <span>Backup Standby Generator</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                    <input type="checkbox" name="has_solar" value="1" {{ old('has_solar', $propertyToEdit?->has_solar ?? false) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <span>Solar Energy System</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-xl bg-white p-2.5 border border-[#d9cab3] hover:border-[#1d3c34] cursor-pointer">
                                    <input type="checkbox" name="has_security" value="1" {{ old('has_security', $propertyToEdit?->has_security ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <span>24/7 Gated Security</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-semibold text-slate-700">Property Photos & Cover Image</label>
                            <span id="property-image-counter-pill" class="text-xs font-bold text-[#1d3c34] bg-[#dfeee4] px-2.5 py-0.5 rounded-full hidden">0 photos</span>
                        </div>
                        <p class="text-xs text-slate-500 mb-2.5">Upload multiple photos for this listing. The primary cover image is shown on search cards, sliders, and featured banners. You can change the cover image anytime by clicking <strong>"★ Set Cover"</strong> on any photo below, uploading a new cover file, or selecting a luxury background preset.</p>

                        {{-- Hidden inputs for selected cover image or luxury preset --}}
                        <input type="hidden" name="cover_image_id" id="admin-cover-image-id" value="{{ $propertyToEdit ? optional($propertyToEdit->images->firstWhere('image_path', $propertyToEdit->image_path))->id : '' }}">
                        <input type="hidden" name="cover_preset" id="admin-cover-preset" value="">

                        {{-- Active Cover Photo Section (when editing) --}}
                        @if ($propertyToEdit)
                            <div class="mb-4 rounded-2xl border border-[#d9cab3] bg-gradient-to-r from-[#fdfaf5] via-white to-[#fbf8f3] p-4 shadow-2xs">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#d4af37] text-[#102b25] text-xs font-black shadow-xs">★</span>
                                        <div>
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-800">Primary Cover Photo</span>
                                            <span class="text-[11px] text-slate-500 ml-1.5">(Displayed on website cards and banner headers)</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="admin-cover-file" class="cursor-pointer inline-flex items-center gap-1.5 rounded-xl border border-[#1d3c34]/25 bg-white hover:bg-[#1d3c34] hover:text-white px-3 py-1.5 text-xs font-bold text-[#1d3c34] shadow-2xs transition">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            <span>Upload New Cover Photo</span>
                                        </label>
                                        <input type="file" id="admin-cover-file" name="cover_image" accept="image/png,image/jpeg,image/webp,image/avif" class="sr-only" onchange="previewAdminCoverImage(this)">
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="relative h-20 w-32 sm:h-24 sm:w-40 shrink-0 overflow-hidden rounded-xl border-2 border-[#d4af37] shadow-sm bg-slate-100">
                                        <img id="admin-active-cover-img" src="{{ $propertyToEdit->primary_image_url }}" alt="Cover Photo" class="h-full w-full object-cover">
                                        <span class="absolute top-1 left-1 rounded bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black uppercase text-[#102b25] shadow-xs">★ Cover</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800" id="admin-cover-status-text">Active Cover Image</p>
                                        <p class="text-[11px] text-slate-500 mt-1">To change, click <strong>"★ Set Cover"</strong> on any gallery photo below, upload a new cover above, or pick a luxury preset background.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Quick Luxury Background Preset Selector --}}
                        <div class="mb-4 rounded-xl border border-[#e7ddca] bg-[#fdfaf5] p-3.5">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <span>🏙️</span>
                                    <span>Best Luxury Background Presets (1-Click Insert)</span>
                                </span>
                                <span class="text-[11px] text-slate-500">Insert high-definition architecture background when photos are unavailable</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                @php
                                    $presets = [
                                        ['id' => 'hero-skyline', 'name' => 'Skyline Luxury', 'file' => 'images/luxury/hero-skyline.jpg'],
                                        ['id' => 'luxury-towers', 'name' => 'Modern Towers', 'file' => 'images/luxury/luxury-towers.jpg'],
                                        ['id' => 'central-plaza', 'name' => 'Central Plaza', 'file' => 'images/luxury/central-plaza.jpg'],
                                        ['id' => 'panoramic-park', 'name' => 'Panoramic Park', 'file' => 'images/luxury/panoramic-park.jpg'],
                                        ['id' => 'retail-boulevard', 'name' => 'Retail Boulevard', 'file' => 'images/luxury/retail-boulevard.jpg'],
                                    ];
                                @endphp
                                @foreach ($presets as $pr)
                                    <button type="button" onclick="selectAdminPresetBackground('{{ $pr['id'] }}', '{{ asset($pr['file']) }}', {{ $propertyToEdit ? $propertyToEdit->id : 'null' }})" class="group relative rounded-lg overflow-hidden border border-[#d9cab3] hover:border-[#d4af37] aspect-[16/10] text-left transition cursor-pointer shadow-2xs hover:shadow-xs">
                                        <img src="{{ asset($pr['file']) }}" alt="{{ $pr['name'] }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-transparent"></div>
                                        <span class="absolute bottom-1 left-1.5 right-1.5 text-[10px] font-bold text-white truncate drop-shadow">{{ $pr['name'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Drag & Drop Area --}}
                        <div id="admin-property-dropzone" class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#d9cab3] bg-white p-5 text-center transition-all hover:border-[#1d3c34] hover:bg-[#dfeee4]/20 cursor-pointer">
                            <input id="property-image-input" type="file" name="images[]" multiple accept="image/png,image/jpeg,image/webp,image/avif" class="sr-only">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#1d3c34]/10 text-[#1d3c34] mb-2 pointer-events-none">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700 pointer-events-none"><span class="text-[#1d3c34] underline decoration-2">Click to select photos</span> or drag and drop here</p>
                            <p class="text-xs text-slate-400 mt-1 pointer-events-none">PNG, JPG, WEBP — select as many as you need (up to 50 photos)</p>
                        </div>
                        
                        {{-- Live Preview for newly selected files --}}
                        <div id="new-property-images-preview" class="mt-3 hidden grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2.5"></div>

                        @if ($propertyToEdit && ($propertyToEdit->images->isNotEmpty() || $propertyToEdit->image_path))
                            <div class="mt-4 border-t border-[#e7ddca] pt-3">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-700">Current Photos ({{ $propertyToEdit->images->count() ?: 1 }})</p>
                                    <span class="text-[11px] text-slate-500">Click <strong>"★ Set Cover"</strong> on any photo to update primary cover</span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2.5" id="admin-existing-images-container">
                                    @forelse ($propertyToEdit->images as $propImg)
                                        @php
                                            $isCover = ($propImg->image_path === $propertyToEdit->image_path) || ($loop->first && ! $propertyToEdit->image_path);
                                        @endphp
                                        <div class="group relative rounded-xl overflow-hidden border-2 {{ $isCover ? 'border-[#d4af37] ring-2 ring-[#d4af37]/30' : 'border-[#d9cab3]' }} bg-white aspect-square shadow-2xs transition-all" id="admin-prop-img-{{ $propImg->id }}" data-property-id="{{ $propertyToEdit->id }}">
                                            <img src="{{ $propImg->image_url }}" alt="Property Photo" class="h-full w-full object-cover">
                                            
                                            {{-- Cover Control Badge / Button --}}
                                            <div class="admin-cover-control absolute top-1 left-1 z-10">
                                                @if ($isCover)
                                                    <span class="admin-cover-badge rounded-md bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black text-[#102b25] uppercase tracking-wider shadow-xs flex items-center gap-1">★ Cover</span>
                                                @else
                                                    <button type="button" onclick="setAdminCoverImage({{ $propertyToEdit->id }}, {{ $propImg->id }}, '{{ $propImg->image_url }}')" title="Set as primary cover photo" class="admin-make-cover-btn rounded-md bg-black/75 hover:bg-[#d4af37] hover:text-[#102b25] text-white px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider transition backdrop-blur-xs cursor-pointer shadow-xs">★ Set Cover</button>
                                                @endif
                                            </div>

                                            {{-- Delete Button --}}
                                            <button type="button" onclick="deleteAdminPropertyImage({{ $propertyToEdit->id }}, {{ $propImg->id }})" title="Remove photo" class="absolute top-1 right-1 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-red-600/90 text-white hover:bg-red-700 transition shadow-xs cursor-pointer">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @empty
                                        @if ($propertyToEdit->image_path)
                                            <div class="relative rounded-xl overflow-hidden border-2 border-[#d4af37] bg-white aspect-square shadow-2xs">
                                                <img src="{{ $propertyToEdit->primary_image_url }}" alt="{{ $propertyToEdit->title }}" class="h-full w-full object-cover">
                                                <span class="absolute top-1 left-1 rounded-md bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black text-[#102b25] uppercase tracking-wider shadow-xs">★ Cover</span>
                                            </div>
                                        @endif
                                    @endforelse
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="md:col-span-2 flex items-center gap-3">
                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <input type="checkbox" name="featured" value="1" {{ old('featured', $propertyToEdit?->featured) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#2d5d4d]">
                            Featured property
                        </label>
                    </div>

                    <div class="md:col-span-2 flex gap-3">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                            {{ $propertyToEdit ? 'Update Property' : 'Create Property' }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- Merged Properties & Developments Catalog (Progress >= 100% is Property, < 100% is Development) --}}
            <div class="mt-8 rounded-[1.25rem] border border-[#e7ddca]">
                <div class="flex flex-wrap items-center justify-between gap-4 rounded-t-[1.25rem] bg-[#2d5d4d] px-5 py-4">
                    <div>
                        <h3 class="text-lg font-black text-white">Properties &amp; Developments Catalog</h3>
                        <p class="text-xs text-[#dfeee4]">100% Completed projects are classified as Properties; &lt;100% are active Developments.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" onclick="openCreateProjectModal()" class="inline-flex items-center gap-1.5 rounded-full bg-[#102b25] px-3.5 py-1.5 text-xs font-bold text-[#d4af37] hover:bg-[#1d3c34] transition shadow-xs cursor-pointer">
                            + Post Development
                        </button>
                        <a href="{{ route('developments.index') }}" target="_blank" class="inline-flex items-center gap-1 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold text-white hover:bg-white/20 transition">
                            Public Developments &rarr;
                        </a>
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-white">
                            {{ $properties->count() + $projects->count() }} items
                        </span>
                    </div>
                </div>

                {{-- Filter Tabs & Search Bar --}}
                <div class="border-b border-[#e7ddca] bg-[#fbf8f3] px-5 py-3.5 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2" id="catalog-filter-tabs">
                            <button type="button" onclick="filterCatalogType('all')" data-catalog-filter="all" class="catalog-filter-btn rounded-full bg-[#1d3c34] px-3.5 py-1 text-xs font-bold text-white shadow-xs">
                                All ({{ $properties->count() + $projects->count() }})
                            </button>
                            @php
                                $completedProjectsCount = $projects->filter(fn($p) => (int)$p->overall_completion_percentage >= 100)->count();
                                $activeDevelopmentsCount = $projects->filter(fn($p) => (int)$p->overall_completion_percentage < 100)->count();
                                $totalPropertiesCount = $properties->count() + $completedProjectsCount;
                            @endphp
                            <button type="button" onclick="filterCatalogType('property')" data-catalog-filter="property" class="catalog-filter-btn rounded-full border border-[#d9cab3] bg-white px-3.5 py-1 text-xs font-bold text-[#1d3c34] hover:bg-[#edf2ee]">
                                Properties (100% Complete) ({{ $totalPropertiesCount }})
                            </button>
                            <button type="button" onclick="filterCatalogType('development')" data-catalog-filter="development" class="catalog-filter-btn rounded-full border border-[#d9cab3] bg-white px-3.5 py-1 text-xs font-bold text-[#1d3c34] hover:bg-[#edf2ee]">
                                Developments (&lt;100% In Progress) ({{ $activeDevelopmentsCount }})
                            </button>
                        </div>
                        <span id="dashboard-catalog-count" class="shrink-0 rounded-full bg-[#1d3c34]/10 px-3 py-1 text-xs font-bold text-[#1d3c34]">
                            {{ $properties->count() + $projects->count() }} items
                        </span>
                    </div>

                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" id="dashboard-catalog-search" placeholder="Search by title/name, price, status, location, developer, or type..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2 pl-9 pr-8 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                        <button type="button" id="dashboard-catalog-search-clear" class="hidden absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 transition" title="Clear search">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto bg-white">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#f4efe7] text-[#1d3c34]">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Title / Project</th>
                                <th class="px-5 py-3 font-semibold">Classification</th>
                                <th class="px-5 py-3 font-semibold">Price</th>
                                <th class="px-5 py-3 font-semibold">Progress / Status</th>
                                <th class="px-5 py-3 font-semibold">Featured</th>
                                <th class="px-5 py-3 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- 1. Standard Property Listings (100% completed ready units) --}}
                            @foreach ($properties as $property)
                                <tr class="dashboard-catalog-row border-t border-[#e7ddca]" data-catalog-type="property" data-search="{{ strtolower($property->title . ' ' . $property->price . ' ' . $property->status . ' ' . $property->type . ' ' . ($property->city ?? '') . ' ' . ($property->address ?? '') . ' ' . ($property->agent?->name ?? '') . ' property 100%') }}">
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-[#1d3c34]">{{ $property->title }}</div>
                                        <div class="text-xs text-slate-500">{{ $property->city ?? 'Addis Ababa' }} {{ $property->address ? '· '.$property->address : '' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-[#dfeee4] px-2.5 py-1 text-xs font-bold text-[#1d3c34]">
                                            <span class="h-1.5 w-1.5 rounded-full bg-[#2d5d4d]"></span> Property
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-slate-700 font-semibold">ETB {{ number_format($property->price, 0) }}</td>
                                    <td class="px-5 py-4">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2">
                                                <div class="h-2 w-16 overflow-hidden rounded-full bg-emerald-100">
                                                    <div class="h-full bg-emerald-600" style="width: 100%"></div>
                                                </div>
                                                <span class="text-xs font-bold text-emerald-800">100%</span>
                                            </div>
                                            <span class="inline-block rounded-full {{ $property->status === 'published' ? 'bg-[#dfeee4] text-[#1d3c34]' : ($property->status === 'archived' ? 'bg-[#f8ddd9] text-[#a24339]' : 'bg-[#f9ecd0] text-[#9b6c17]') }} px-2 py-0.5 text-[10px] font-bold">
                                                {{ ucfirst($property->status) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600 text-xs">{{ $property->featured ? 'Yes' : 'No' }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex flex-wrap items-center justify-end gap-1.5">
                                            <a href="{{ route('dashboard', ['edit_property' => $property->id, 'section' => 'properties']) }}" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#254d43]">Edit</a>
                                            <form method="POST" action="{{ route('dashboard.properties.togglePublish', $property) }}">
                                                @csrf
                                                <button type="submit" class="rounded-full border border-[#b9a98b] bg-white px-2.5 py-1.5 text-xs font-bold text-[#1d3c34]">
                                                    {{ $property->is_active ? 'Unpublish' : 'Publish' }}
                                                </button>
                                            </form>
                                            @php
                                                $soldStatus = $property->type === 'rent' ? 'rented' : 'sold';
                                                $soldLabel = $property->type === 'rent' ? 'Rented' : 'Sold';
                                                $markLabel = $property->type === 'rent' ? 'Mark Rented' : 'Mark Sold';
                                                $latestAgreementOrder = $property->orders ? $property->orders->firstWhere('agreement_path', '!=', null) : null;
                                            @endphp
                                            <form method="POST" action="{{ route('dashboard.properties.toggleSold', $property) }}">
                                                @csrf
                                                <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#f8f3eb] px-2.5 py-1.5 text-xs font-bold text-[#1d3c34]">
                                                    {{ $property->status === $soldStatus ? 'Mark Available' : $markLabel }}
                                                </button>
                                            </form>
                                            @if ($latestAgreementOrder)
                                                <a href="{{ route('orders.agreement', $latestAgreementOrder) }}" class="inline-flex items-center gap-1 rounded-full border border-[#1d3c34] bg-[#edf2ee] px-2.5 py-1.5 text-xs font-bold text-[#1d3c34] hover:bg-[#dfeee4]">
                                                    Agreement
                                                </a>
                                            @else
                                                <button type="button" class="open-agreement-modal rounded-full bg-[#b7842d] px-2.5 py-1.5 text-xs font-bold text-white hover:bg-[#966b22]"
                                                    data-property-id="{{ $property->id }}"
                                                    data-property-title="{{ $property->title }}"
                                                    data-property-price="{{ $property->price }}"
                                                    data-property-type="{{ $property->type }}"
                                                    data-action-url="{{ route('dashboard.properties.generateAgreement', $property) }}">
                                                    {{ $property->type === 'rent' ? 'Rent Agrmt' : 'Sell Agrmt' }}
                                                </button>
                                            @endif
                                            <form method="POST" action="{{ route('dashboard.properties.destroy', $property) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-2.5 py-1.5 text-xs font-bold text-[#a24339]" data-confirm="Delete this property?">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            {{-- 2. Master Development Projects (Classified by overall_completion_percentage: >= 100% is Property, < 100% is Development) --}}
                            @foreach ($projects as $prj)
                                @php
                                    $isCompleted = (int)$prj->overall_completion_percentage >= 100;
                                    $classificationType = $isCompleted ? 'property' : 'development';
                                @endphp
                                <tr class="dashboard-catalog-row border-t border-[#e7ddca] bg-[#fdfbf7]" data-catalog-type="{{ $classificationType }}" data-search="{{ strtolower($prj->name . ' ' . $prj->project_code . ' ' . $prj->developer_name . ' ' . ($prj->subcity ?? $prj->city ?? '') . ' ' . ($isCompleted ? 'property completed 100%' : 'development in progress') . ' ' . $prj->overall_completion_percentage . '%') }}">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs font-bold text-[#1d3c34]">{{ $prj->project_code }}</span>
                                            <a href="{{ route('developments.show', $prj->slug) }}" class="font-bold text-[#1d3c34] hover:underline">{{ $prj->name }}</a>
                                        </div>
                                        <div class="text-xs text-slate-500">{{ $prj->developer_name }} · {{ $prj->subcity ?? $prj->city }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($isCompleted)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-[#dfeee4] px-2.5 py-1 text-xs font-bold text-[#1d3c34]">
                                                <span class="h-1.5 w-1.5 rounded-full bg-[#2d5d4d]"></span> Property (100% Done)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-[#f9ecd0] px-2.5 py-1 text-xs font-bold text-[#9b6c17]">
                                                <span class="h-1.5 w-1.5 rounded-full bg-[#b7842d]"></span> Development ({{ $prj->overall_completion_percentage }}%)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-slate-700 font-semibold whitespace-nowrap">{{ $prj->formatted_starting_price }}</td>
                                    <td class="px-5 py-4">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2">
                                                <div class="h-2 w-16 overflow-hidden rounded-full bg-slate-200">
                                                    <div class="h-full {{ $isCompleted ? 'bg-emerald-600' : 'bg-[#b7842d]' }}" style="width: {{ min(100, (int)$prj->overall_completion_percentage) }}%"></div>
                                                </div>
                                                <span class="text-xs font-bold {{ $isCompleted ? 'text-emerald-800' : 'text-[#9b6c17]' }}">{{ $prj->overall_completion_percentage }}%</span>
                                            </div>
                                            <span class="inline-block rounded-full bg-[#eef7f2] px-2 py-0.5 text-[10px] font-bold text-[#0f5132]">
                                                {{ $prj->available_units_count }} / {{ $prj->units->count() }} Units Avail
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600 text-xs">—</td>
                                    <td class="px-5 py-4 text-right whitespace-nowrap">
                                        <div class="flex flex-wrap items-center justify-end gap-1.5">
                                            <a href="{{ route('developments.show', $prj->slug) }}" class="inline-flex items-center gap-1 rounded-full bg-[#1d3c34] px-2.5 py-1.5 text-xs font-bold text-white hover:bg-[#254d43]">
                                                Showcase
                                            </a>
                                            <button type="button" onclick="openEditProjectModal({{ json_encode($prj) }})" class="rounded-full border border-[#d9cab3] bg-[#fffaf0] px-2.5 py-1.5 text-xs font-bold text-[#9b6c17] hover:bg-[#f5ecdd] cursor-pointer">
                                                Edit
                                            </button>
                                            <button type="button" onclick="openAddUnitModal({{ $prj->id }}, '{{ addslashes($prj->name) }}')" class="rounded-full border border-[#d9cab3] bg-white px-2.5 py-1.5 text-xs font-bold text-[#1d3c34] hover:bg-[#f5ecdd] cursor-pointer">
                                                + Unit
                                            </button>
                                            <form method="POST" action="{{ route('developments.destroy', $prj) }}" onsubmit="return confirm('Delete this development project?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-full border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-bold text-red-600 hover:bg-red-100 cursor-pointer">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            <tr id="dashboard-catalog-no-results" class="hidden border-t border-[#e7ddca] bg-white">
                                <td colspan="6" class="px-5 py-6 text-center text-slate-500">No properties or developments found matching your criteria.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if ($properties->hasPages())
                    <div class="mt-6 border-t border-[#e7ddca] pt-4">
                        {{ $properties->fragment('properties')->links() }}
                    </div>
                @endif
            </div>

            {{-- Off-Plan Unit Reservations Received --}}
            <div class="mt-10 rounded-[1.25rem] border border-[#e7ddca] bg-white p-5 shadow-xs">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-base font-black text-[#1d3c34]">Off-Plan Unit Reservations Received</h3>
                        <p class="text-xs text-slate-500">Prospective buyer inquiries and unit holds on development projects.</p>
                    </div>
                    <span class="rounded-full bg-[#102b25] px-3 py-1 text-xs font-black text-[#d4af37]">
                        {{ $unitReservations instanceof \Illuminate\Pagination\LengthAwarePaginator ? $unitReservations->total() : $unitReservations->count() }} Total
                    </span>
                </div>

                @if (isset($unitReservations) && $unitReservations->isNotEmpty())
                    <div class="overflow-x-auto rounded-xl border border-[#d9cab3]/80">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#102b25] text-white">
                                <tr>
                                    <th class="px-4 py-3 font-bold uppercase">Reference</th>
                                    <th class="px-4 py-3 font-bold uppercase">Buyer Name</th>
                                    <th class="px-4 py-3 font-bold uppercase">Contact</th>
                                    <th class="px-4 py-3 font-bold uppercase">Project &amp; Unit</th>
                                    <th class="px-4 py-3 font-bold uppercase">Status</th>
                                    <th class="px-4 py-3 font-bold uppercase">Date</th>
                                    <th class="px-4 py-3 font-bold uppercase text-right">Unit Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0e4cf] bg-white text-slate-700">
                                @foreach ($unitReservations as $resItem)
                                    <tr class="hover:bg-[#fffdfa]">
                                        <td class="px-4 py-3 font-mono font-bold text-[#1d3c34] whitespace-nowrap">
                                            {{ $resItem->reservation_reference }}
                                        </td>
                                        <td class="px-4 py-3 font-bold text-[#1d3c34]">
                                            {{ $resItem->buyer_name }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">
                                            <p>{{ $resItem->buyer_email }}</p>
                                            <p class="text-[11px] text-slate-500">{{ $resItem->buyer_phone }}</p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-bold text-[#1d3c34]">{{ $resItem->project?->name }}</span>
                                            <span class="block text-[11px] text-[#587165]">Unit {{ $resItem->unit?->unit_number }} ({{ $resItem->unit?->formatted_price }})</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full bg-[#fdf8e6] px-2.5 py-0.5 text-[11px] font-bold text-[#9b6c17]">
                                                {{ ucfirst($resItem->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                                            {{ $resItem->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if ($resItem->unit)
                                                <form method="POST" action="{{ route('developments.update-unit-status', $resItem->unit) }}" class="inline-flex items-center gap-1.5">
                                                    @csrf
                                                    @method('PUT')
                                                    <select name="status" onchange="this.form.submit()" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[11px] font-semibold text-slate-700">
                                                        <option value="available" {{ $resItem->unit->status == 'available' ? 'selected' : '' }}>Available</option>
                                                        <option value="reserved" {{ $resItem->unit->status == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                                        <option value="contracted" {{ $resItem->unit->status == 'contracted' ? 'selected' : '' }}>Contracted</option>
                                                        <option value="sold" {{ $resItem->unit->status == 'sold' ? 'selected' : '' }}>Sold</option>
                                                    </select>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($unitReservations instanceof \Illuminate\Pagination\LengthAwarePaginator && $unitReservations->hasPages())
                        <div class="mt-4 border-t border-[#d9cab3] pt-3">
                            {{ $unitReservations->fragment('properties')->links() }}
                        </div>
                    @endif
                @else
                    <div class="rounded-xl border border-dashed border-[#d9cab3] bg-[#fffdfa] p-6 text-center text-xs text-slate-500">
                        No off-plan unit reservations received yet. Prospective buyers can reserve units directly on project pages.
                    </div>
                @endif
            </div>

            @include('developments.partials.create-project-modal')
            @include('developments.partials.edit-project-modal')
            @include('developments.partials.add-unit-modal')
        </section>

