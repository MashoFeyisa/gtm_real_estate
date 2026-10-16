<div id="property-reviews-section" class="scroll-mt-24 flex flex-col h-full rounded-[2rem] border border-[#d9cab3] bg-white p-6 sm:p-8 shadow-sm dark:bg-[#111827] dark:border-slate-800 min-w-0 break-words">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#f0e4cf] pb-5 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#d4af37]">Verified Feedback</span>
                <h3 class="text-2xl font-black text-[#1d3c34] dark:text-white">Guest & Client Reviews</h3>
            </div>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Transparent two-way reviews from verified guests, tenants, and property purchasers.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-right">
                <div class="flex items-center gap-1.5 justify-end">
                    <span class="text-2xl font-black text-[#102b25] dark:text-white">{{ number_format($property->average_rating, 1) }}</span>
                    <span class="text-lg text-amber-500">★</span>
                </div>
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ $property->reviews_count }} {{ Str::plural('review', $property->reviews_count) }}</span>
            </div>
        </div>
    </div>

    {{-- Sub-Criteria Scorecard --}}
    @if ($property->reviews_count > 0)
        @php
            $publishedReviews = $property->reviews()->where('status', 'published')->get();
            $avgClean = $publishedReviews->avg('cleanliness_rating') ?: 5;
            $avgAcc = $publishedReviews->avg('accuracy_rating') ?: 5;
            $avgComm = $publishedReviews->avg('communication_rating') ?: 5;
            $avgLoc = $publishedReviews->avg('location_rating') ?: 5;
            $avgVal = $publishedReviews->avg('value_rating') ?: 5;
        @endphp
        <div class="mt-6 grid grid-cols-2 sm:grid-cols-5 gap-3 border-b border-[#f0e4cf] pb-6">
            <div class="rounded-xl bg-[#fffaf2] p-3 text-center border border-[#ecd9be]">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-[#60716c]">Cleanliness</span>
                <span class="text-sm font-black text-[#102b25]">{{ number_format($avgClean, 1) }} ★</span>
            </div>
            <div class="rounded-xl bg-[#fffaf2] p-3 text-center border border-[#ecd9be]">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-[#60716c]">Accuracy</span>
                <span class="text-sm font-black text-[#102b25]">{{ number_format($avgAcc, 1) }} ★</span>
            </div>
            <div class="rounded-xl bg-[#fffaf2] p-3 text-center border border-[#ecd9be]">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-[#60716c]">Communication</span>
                <span class="text-sm font-black text-[#102b25]">{{ number_format($avgComm, 1) }} ★</span>
            </div>
            <div class="rounded-xl bg-[#fffaf2] p-3 text-center border border-[#ecd9be]">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-[#60716c]">Location</span>
                <span class="text-sm font-black text-[#102b25]">{{ number_format($avgLoc, 1) }} ★</span>
            </div>
            <div class="rounded-xl bg-[#fffaf2] p-3 text-center border border-[#ecd9be] col-span-2 sm:col-span-1">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-[#60716c]">Value</span>
                <span class="text-sm font-black text-[#102b25]">{{ number_format($avgVal, 1) }} ★</span>
            </div>
        </div>
    @endif

    {{-- Reviews List --}}
    <div class="mt-6 space-y-4">
        @forelse ($property->reviews()->where('status', 'published')->with('user')->latest()->limit(8)->get() as $review)
            <div class="rounded-2xl border border-[#f0e4cf] bg-[#fffdfa] p-4 text-xs">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#f0e4cf] pb-2.5">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#102b25] font-black text-xs text-[#d4af37]">
                            {{ strtoupper(substr($review->user?->name ?? 'G', 0, 1)) }}
                        </div>
                        <div>
                            <span class="font-bold text-[#1d3c34]">{{ $review->user?->name ?? 'Marketplace Client' }}</span>
                            @if ($review->is_verified_stay)
                                <span class="ml-1.5 inline-flex items-center gap-0.5 rounded-full bg-emerald-100 px-2 py-0.2 text-[9px] font-black uppercase text-emerald-800">
                                    ✓ Verified Stay
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-black text-amber-500">{{ str_repeat('★', $review->rating) }}</span>
                        <span class="text-[10px] text-slate-400">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                @if ($review->review_title)
                    <h5 class="mt-2.5 font-black text-sm text-[#102b25]">{{ $review->review_title }}</h5>
                @endif
                <p class="mt-1.5 text-slate-600 leading-relaxed">{{ $review->review_body }}</p>

                @if ($review->host_reply)
                    <div class="mt-3 rounded-xl bg-[#f4ece1] p-3 text-[11px] border-l-2 border-[#102b25]">
                        <span class="font-bold text-[#102b25] block">Host Response:</span>
                        <p class="text-slate-700 mt-0.5">{{ $review->host_reply }}</p>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-[#d9cab3] p-8 text-center text-slate-500">
                <p class="text-sm font-bold text-[#1d3c34]">No public reviews yet</p>
                <p class="mt-1 text-xs">Be the first verified guest or property inspector to share your experience.</p>
            </div>
        @endforelse
    </div>

    {{-- Review Submission Form --}}
    <div class="mt-8 border-t border-[#f0e4cf] pt-6">
        @auth
            <h4 class="text-base font-black text-[#1d3c34]">Write a Review</h4>
            <p class="text-xs text-slate-500">Your genuine review helps other buyers, tenants, and guests make confident decisions.</p>

            <form method="POST" action="{{ route('properties.reviews.store', $property) }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Overall Rating <span class="text-red-500">*</span></label>
                        <select name="rating" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                            <option value="5" selected>5 Stars (Exceptional)</option>
                            <option value="4">4 Stars (Good)</option>
                            <option value="3">3 Stars (Average)</option>
                            <option value="2">2 Stars (Below Average)</option>
                            <option value="1">1 Star (Poor)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Cleanliness</label>
                        <select name="cleanliness_rating" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                            <option value="5" selected>5 / 5</option>
                            <option value="4">4 / 5</option>
                            <option value="3">3 / 5</option>
                            <option value="2">2 / 5</option>
                            <option value="1">1 / 5</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Accuracy</label>
                        <select name="accuracy_rating" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                            <option value="5" selected>5 / 5</option>
                            <option value="4">4 / 5</option>
                            <option value="3">3 / 5</option>
                            <option value="2">2 / 5</option>
                            <option value="1">1 / 5</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Communication</label>
                        <select name="communication_rating" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                            <option value="5" selected>5 / 5</option>
                            <option value="4">4 / 5</option>
                            <option value="3">3 / 5</option>
                            <option value="2">2 / 5</option>
                            <option value="1">1 / 5</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Location</label>
                        <select name="location_rating" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                            <option value="5" selected>5 / 5</option>
                            <option value="4">4 / 5</option>
                            <option value="3">3 / 5</option>
                            <option value="2">2 / 5</option>
                            <option value="1">1 / 5</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Value for Money</label>
                        <select name="value_rating" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                            <option value="5" selected>5 / 5</option>
                            <option value="4">4 / 5</option>
                            <option value="3">3 / 5</option>
                            <option value="2">2 / 5</option>
                            <option value="1">1 / 5</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Review Headline (Optional)</label>
                    <input type="text" name="review_title" placeholder="e.g. Beautiful modern finishes and quiet Bole neighborhood" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Detailed Review <span class="text-red-500">*</span></label>
                    <textarea name="review_body" rows="3" required placeholder="Describe your stay, physical condition, utilities reliability, or viewing experience..." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-medium text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]"></textarea>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <span class="text-[11px] text-slate-500">🛡️ Verified stay status automatically checked against your reservations</span>
                    <button type="submit" class="rounded-xl bg-[#102b25] px-6 py-2.5 text-xs font-black uppercase tracking-wider text-[#d4af37] hover:bg-[#1d3c34] transition">
                        Post Review
                    </button>
                </div>
            </form>
        @else
            <div class="rounded-2xl bg-[#fffaf2] p-4 text-center border border-[#ecd9be]">
                <p class="text-xs font-bold text-[#1d3c34]">Have you viewed, rented, or stayed at this property?</p>
                <a href="{{ route('register') }}" class="mt-2 inline-flex items-center gap-1.5 rounded-xl bg-[#102b25] px-4 py-2 text-xs font-bold text-[#d4af37] hover:bg-[#1d3c34] transition">
                    Create an account to leave a verified review
                </a>
            </div>
        @endauth
    </div>
</div>
