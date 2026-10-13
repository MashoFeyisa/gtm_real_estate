        @php
            $editTestimonialId = request()->query('edit_testimonial');
            $testimonialToEdit = $editTestimonialId ? $testimonials->firstWhere('id', (int) $editTestimonialId) : null;
        @endphp

        <section id="testimonials" data-section="testimonials" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[#1d3c34]">Client Testimonials</h2>
                    <p class="mt-1 text-sm text-slate-600">Client testimonials are submitted from agent profile pages. Review them here: edit, approve for the homepage, delete, or restore. Testimonials cannot be created manually.</p>
                </div>
            </div>

            @if ($testimonialToEdit)
                <div class="create-form-panel mt-6 rounded-[1.25rem] border border-[#d9cab3] bg-[#f9f4ed] p-5">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h3 class="text-xl font-black text-[#1d3c34]">Editing Testimonial from: {{ $testimonialToEdit->name }}</h3>
                        <a href="{{ route('dashboard', ['section' => 'testimonials']) }}" class="text-sm font-semibold text-[#2d5d4d]">Cancel</a>
                    </div>

                    <form method="POST" action="{{ route('dashboard.testimonials.update', $testimonialToEdit) }}" class="grid gap-4 md:grid-cols-2">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Client Name</label>
                            <input type="text" name="name" value="{{ old('name', $testimonialToEdit?->name) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Client Email (optional)</label>
                            <input type="email" name="email" value="{{ old('email', $testimonialToEdit?->email) }}" placeholder="client@example.com" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Assigned Agent (optional)</label>
                            <select name="agent_id" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                <option value="">General Agency Testimonial (No specific agent)</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ (string) old('agent_id', $testimonialToEdit?->agent_id) === (string) $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Rating</label>
                            <select name="rating" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                @foreach ([5 => '★★★★★ (5 Stars)', 4 => '★★★★☆ (4 Stars)', 3 => '★★★☆☆ (3 Stars)', 2 => '★★☆☆☆ (2 Stars)', 1 => '★☆☆☆☆ (1 Star)'] as $val => $label)
                                    <option value="{{ $val }}" {{ (int) old('rating', $testimonialToEdit?->rating ?? 5) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Testimonial Message</label>
                            <textarea name="message" rows="3" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('message', $testimonialToEdit?->message) }}</textarea>
                        </div>

                        <div class="md:col-span-2 flex items-center gap-2">
                            <input type="checkbox" id="testimonial-approved" name="is_approved" value="1" {{ old('is_approved', $testimonialToEdit?->is_approved) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#dfeee4]">
                            <label for="testimonial-approved" class="text-sm font-semibold text-slate-700">Approved (Show on home page)</label>
                        </div>

                        <div class="md:col-span-2 flex items-center gap-3">
                            <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]">
                                Update Testimonial
                            </button>
                            <a href="{{ route('dashboard', ['section' => 'testimonials']) }}" class="rounded-full border border-[#d9cab3] bg-white px-5 py-2.5 text-sm font-bold text-[#1d3c34]">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Testimonials Search Bar --}}
            <div class="mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#fbf8f3] p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" id="dashboard-testimonials-search" placeholder="Search testimonials by client name, email, agent, message, rating..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2 pl-9 pr-8 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                        <button type="button" id="dashboard-testimonials-search-clear" class="hidden absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 transition" title="Clear search">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <span id="dashboard-testimonials-count" class="shrink-0 rounded-full bg-[#1d3c34]/10 px-3 py-1 text-xs font-bold text-[#1d3c34]">{{ $testimonials->whereNull('deleted_at')->count() }} testimonials</span>
                </div>
            </div>

            {{-- Active Testimonials Table --}}
            <div class="mt-4 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#1d3c34] text-[#f8f3eb]">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Client</th>
                            <th class="px-5 py-4 font-semibold">Agent</th>
                            <th class="px-5 py-4 font-semibold">Rating</th>
                            <th class="px-5 py-4 font-semibold">Message</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($testimonials->whereNull('deleted_at') as $item)
                            <tr class="dashboard-testimonial-row border-t border-[#e7ddca] bg-white" data-search="{{ strtolower($item->name . ' ' . ($item->email ?? '') . ' ' . ($item->agent?->name ?? '') . ' ' . $item->message . ' ' . $item->rating . ' ' . ($item->is_approved ? 'approved' : 'pending')) }}">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-[#1d3c34]">{{ $item->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->email }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-700">
                                    {{ $item->agent?->name ?? 'General / Agency' }}
                                </td>
                                <td class="px-5 py-4 font-bold text-[#9b6c17]">
                                    {{ str_repeat('★', $item->rating) }}
                                </td>
                                <td class="max-w-xs px-5 py-4 text-slate-600">
                                    <p class="line-clamp-2 text-xs leading-5">“{{ $item->message }}”</p>
                                </td>
                                <td class="px-5 py-4">
                                    <form method="POST" action="{{ route('dashboard.testimonials.toggleApproval', $item) }}">
                                        @csrf
                                        <button type="submit" class="rounded-full px-2.5 py-1 text-xs font-bold {{ $item->is_approved ? 'bg-[#dfeee4] text-[#1d3c34]' : 'bg-[#f9ecd0] text-[#9b6c17]' }}">
                                            {{ $item->is_approved ? 'Approved' : 'Pending' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('dashboard', ['edit_testimonial' => $item->id, 'section' => 'testimonials']) }}" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('dashboard.testimonials.destroy', $item) }}" data-confirm="Delete this testimonial? You can restore it later.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1.5 text-xs font-bold text-[#a24339] transition hover:bg-[#fbe4e0]">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="border-t border-[#e7ddca] bg-white">
                                <td colspan="6" class="px-5 py-6 text-center text-slate-500">No client testimonials yet. Feedback submitted from agent profile pages will appear here.</td>
                            </tr>
                        @endforelse
                        <tr id="dashboard-testimonials-no-results" class="hidden border-t border-[#e7ddca] bg-white">
                            <td colspan="6" class="px-5 py-6 text-center text-slate-500">No testimonials found matching your search.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if ($testimonials->hasPages())
                <div class="mt-6 border-t border-[#e7ddca] pt-4">
                    {{ $testimonials->fragment('testimonials')->links() }}
                </div>
            @endif

            {{-- Trashed / Deleted Testimonials Table with Restore Action --}}
            @if ($trashedTestimonials->isNotEmpty())
                <div class="mt-8 rounded-[1.25rem] border border-[#d9cab3] bg-[#fffaf2] p-5">
                    <div class="flex items-center justify-between gap-4">
                        <h3 class="text-lg font-black text-[#1d3c34]">Deleted Testimonials (Trash)</h3>
                        <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-bold text-[#9b6c17]">{{ $trashedTestimonials->count() }} Trashed</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-600">These testimonials have been deleted and are hidden from the public website. You can restore them anytime.</p>

                    <div class="mt-4 overflow-x-auto rounded-xl border border-[#e7ddca] bg-white">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-[#9b6c17] text-[#f8f3eb]">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Client</th>
                                    <th class="px-4 py-3 font-semibold">Agent</th>
                                    <th class="px-4 py-3 font-semibold">Rating</th>
                                    <th class="px-4 py-3 font-semibold">Message</th>
                                    <th class="px-4 py-3 font-semibold">Deleted At</th>
                                    <th class="px-4 py-3 font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($trashedTestimonials as $trashed)
                                    <tr class="border-t border-[#e7ddca]">
                                        <td class="px-4 py-3 font-semibold text-[#1d3c34]">{{ $trashed->name }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $trashed->agent?->name ?? 'General' }}</td>
                                        <td class="px-4 py-3 font-bold text-[#9b6c17]">{{ str_repeat('★', $trashed->rating) }}</td>
                                        <td class="max-w-xs px-4 py-3 text-xs text-slate-600 truncate">{{ $trashed->message }}</td>
                                        <td class="px-4 py-3 text-xs text-slate-500">{{ $trashed->deleted_at?->format('M d, Y H:i') }}</td>
                                        <td class="px-4 py-3">
                                            <form method="POST" action="{{ route('dashboard.testimonials.restore', $trashed->id) }}">
                                                @csrf
                                                <button type="submit" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                                    Restore
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>

