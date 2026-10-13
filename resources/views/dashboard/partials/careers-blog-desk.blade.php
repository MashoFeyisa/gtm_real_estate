        @php
            $editPostId = request()->query('edit_post');
            $postToEdit = $editPostId ? $posts->firstWhere('id', (int) $editPostId) : null;
        @endphp

        @if (auth()->user()->canCreateContent())
            <section id="blog-posts" data-section="blog-posts" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-black text-[#1d3c34]">Blog &amp; News</h2>
                    <button type="button" class="toggle-create-form rounded-full bg-[#1d3c34] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]" data-target="blog-posts-create-panel" data-label-create="+ Add Post" data-label-close="Close Form">+ Add Post</button>
                </div>

                <div id="blog-posts-create-panel" class="create-form-panel mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#f9f4ed] p-5 {{ $postToEdit ? '' : 'hidden' }}">
                @if ($postToEdit)
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h3 class="text-xl font-black text-[#1d3c34]">Edit Post</h3>
                        <a href="{{ route('dashboard', ['section' => 'blog-posts']) }}" class="text-sm font-semibold text-[#2d5d4d]">Cancel</a>
                    </div>
                @else
                    <h3 class="text-xl font-black text-[#1d3c34]">Create Post</h3>
                @endif

                <form method="POST" action="{{ $postToEdit ? route('dashboard.posts.update', $postToEdit) : route('dashboard.posts.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf
                    @if ($postToEdit)
                        @method('PUT')
                    @endif

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                        <input type="text" name="title" value="{{ old('title', $postToEdit?->title) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $postToEdit?->slug) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Type</label>
                        <select name="type" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="blog" {{ old('type', $postToEdit?->type) === 'blog' ? 'selected' : '' }}>Blog</option>
                            <option value="news" {{ old('type', $postToEdit?->type) === 'news' ? 'selected' : '' }}>News</option>
                            <option value="listing" {{ old('type', $postToEdit?->type) === 'listing' ? 'selected' : '' }}>New Listing</option>
                            <option value="project" {{ old('type', $postToEdit?->type) === 'project' ? 'selected' : '' }}>Featured Project</option>
                            <option value="job" {{ old('type', $postToEdit?->type) === 'job' ? 'selected' : '' }}>Organization Job</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Category</label>
                        <input type="text" name="category" value="{{ old('category', $postToEdit?->category) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Tags</label>
                        <input type="text" name="tags" value="{{ old('tags', $postToEdit?->tags) }}" placeholder="market,harar,property" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Author</label>
                        <input type="text" name="author_name" value="{{ old('author_name', $postToEdit?->author_name ?? auth()->user()->name) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Status</label>
                        <select name="status" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="draft" {{ old('status', $postToEdit?->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $postToEdit?->status) === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="scheduled" {{ old('status', $postToEdit?->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Publication date</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', $postToEdit?->published_at?->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Featured image</label>
                        <input type="file" name="image" accept="image/*" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Social image</label>
                        <input type="file" name="social_image" accept="image/*" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">SEO title</label>
                        <input type="text" name="seo_title" value="{{ old('seo_title', $postToEdit?->seo_title) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">SEO description</label>
                        <textarea name="seo_description" rows="2" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('seo_description', $postToEdit?->seo_description) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Related post IDs</label>
                        <input type="text" name="related_posts" value="{{ old('related_posts', $postToEdit?->related_post_ids) }}" placeholder="1, 2, 3" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Content</label>
                        <textarea name="content" rows="8" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('content', $postToEdit?->content) }}</textarea>
                    </div>

                    <div class="md:col-span-2 flex flex-wrap gap-3">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                            {{ $postToEdit ? 'Update Post' : 'Publish Post' }}
                        </button>
                        @if ($postToEdit)
                            <a href="{{ route('dashboard', ['section' => 'blog-posts']) }}" class="rounded-full border border-[#d9cab3] bg-white px-5 py-3 text-sm font-bold text-[#1d3c34]">Cancel edit</a>
                        @endif
                    </div>
                </form>
                </div>

                {{-- Existing posts list --}}
                <div class="mt-8 rounded-[1.25rem] border border-[#e7ddca]">
                    <div class="flex items-center justify-between gap-4 rounded-t-[1.25rem] bg-[#2d5d4d] px-5 py-4">
                        <h3 class="text-lg font-black text-white">Content Library</h3>
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-white">{{ $posts->count() }} posts</span>
                    </div>

                    <form method="GET" action="{{ route('dashboard') }}" class="grid gap-3 px-5 py-4 md:grid-cols-4">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts" class="rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5">
                        <select name="type" class="rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5">
                            <option value="">All types</option>
                            <option value="blog" {{ request('type') === 'blog' ? 'selected' : '' }}>Blog</option>
                            <option value="news" {{ request('type') === 'news' ? 'selected' : '' }}>News</option>
                            <option value="listing" {{ request('type') === 'listing' ? 'selected' : '' }}>New Listing</option>
                            <option value="project" {{ request('type') === 'project' ? 'selected' : '' }}>Featured Project</option>
                            <option value="job" {{ request('type') === 'job' ? 'selected' : '' }}>Organization Job</option>
                        </select>
                        <select name="status" class="rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5">
                            <option value="">All status</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        </select>
                        <button type="submit" class="rounded-xl bg-[#1d3c34] px-4 py-2.5 text-sm font-bold text-white">Apply filters</button>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-[#f4efe7] text-[#1d3c34]">
                                <tr>
                                    <th class="px-5 py-4 font-semibold">Title</th>
                                    <th class="px-5 py-4 font-semibold">Type</th>
                                    <th class="px-5 py-4 font-semibold">Status</th>
                                    <th class="px-5 py-4 font-semibold">Date</th>
                                    <th class="px-5 py-4 font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($posts as $post)
                                    <tr class="border-t border-[#e7ddca]">
                                        <td class="px-5 py-4 font-semibold text-[#1d3c34]">{{ $post->title }}</td>
                                        <td class="px-5 py-4 text-slate-600 uppercase">{{ $post->type }}</td>
                                        <td class="px-5 py-4">
                                            <span class="rounded-full {{ $post->status === 'published' ? 'bg-[#dfeee4] text-[#1d3c34]' : ($post->status === 'scheduled' ? 'bg-[#f9ecd0] text-[#9b6c17]' : 'bg-[#edf2ee] text-[#2d5d4d]') }} px-2.5 py-1 text-xs font-bold">
                                                {{ ucfirst($post->status) }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-slate-600">{{ $post->published_at?->format('M d, Y') ?? $post->scheduled_for?->format('M d, Y') ?? 'Not set' }}</td>
                                        <td class="px-5 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('dashboard', ['edit_post' => $post->id, 'section' => 'blog-posts']) }}" class="rounded-full bg-[#1d3c34] px-3 py-2 text-xs font-bold text-white">Edit</a>
                                                <form method="POST" action="{{ route('dashboard.posts.togglePublish', $post) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded-full border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                                                        {{ $post->status === 'published' ? 'Unpublish' : 'Publish' }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('dashboard.posts.delete', $post) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-3 py-2 text-xs font-bold text-[#a24339]" data-confirm="Delete this post?">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-6 text-center text-slate-500">No posts match the current filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($posts->hasPages())
                        <div class="mt-6 border-t border-[#e7ddca] pt-4">
                            {{ $posts->fragment('blog-posts')->links() }}
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @php
            $editJobId = request()->query('edit_job');
            $jobToEdit = $editJobId ? ($jobs->firstWhere('id', (int) $editJobId) ?? \App\Models\BlogPost::find((int) $editJobId)) : null;
        @endphp

        @if (auth()->user()->canCreateContent())
            <section id="job-postings" data-section="job-postings" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#1d3c34]">Job Postings</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Manage organizational job openings, descriptions, and hiring deadlines.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <a href="#job-applications" data-target-section="job-applications" class="nav-section-link inline-flex items-center gap-2 rounded-full border border-[#d9cab3] bg-[#fbf8f3] px-4 py-2.5 text-xs sm:text-sm font-bold text-[#1d3c34] hover:bg-[#1d3c34] hover:text-white transition shadow-xs">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>View Applications ({{ $jobApplications->count() }})</span>
                            @if ($pendingApplicationsCount > 0)
                                <span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-[10px] font-bold text-[#9b6c17]">{{ $pendingApplicationsCount }} new</span>
                            @endif
                        </a>
                        <button type="button" class="toggle-create-form rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs sm:text-sm font-bold text-white transition hover:bg-[#254d43]" data-target="job-postings-create-panel" data-label-create="+ Add Job Posting" data-label-close="Close Form">+ Add Job Posting</button>
                    </div>
                </div>

                <div id="job-postings-create-panel" class="create-form-panel mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#f9f4ed] p-5 {{ $jobToEdit ? '' : 'hidden' }}">
                    <h3 class="text-xl font-black text-[#1d3c34]">{{ $jobToEdit ? 'Edit Job Opening' : 'Post a Job Opening' }}</h3>
                    <form method="POST" action="{{ $jobToEdit ? route('dashboard.posts.update', $jobToEdit) : route('dashboard.posts.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
                        @csrf
                        @if ($jobToEdit)
                            @method('PUT')
                        @endif
                        <input type="hidden" name="type" value="job">
                        <input type="hidden" name="section" value="job-postings">

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Job Title</label>
                            <input type="text" name="title" value="{{ old('title', $jobToEdit?->title) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        {{-- Job Top Image / Header Banner Section --}}
                        <div class="md:col-span-2 rounded-2xl border border-[#d9cab3] bg-[#fdfaf5] p-4 sm:p-5 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                                <div>
                                    <label class="block text-sm font-bold text-[#1d3c34]">Top Image / Header Banner</label>
                                    <p class="text-xs text-slate-500">Insert an eye-catching top image to display prominently at the top of this job opening.</p>
                                </div>
                                <span class="inline-flex items-center gap-1.5 self-start rounded-full bg-[#1d3c34]/10 px-3 py-1 text-xs font-bold text-[#1d3c34]">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Job Top Banner
                                </span>
                            </div>

                            <div class="grid gap-4 md:grid-cols-12 items-start">
                                {{-- Preview Box --}}
                                <div class="md:col-span-5">
                                    <div class="relative aspect-[16/9] w-full overflow-hidden rounded-xl border-2 border-dashed border-[#d9cab3] bg-[#f0e8dc] flex items-center justify-center shadow-inner group">
                                        <img id="job-active-top-image" src="{{ $jobToEdit?->image_url ?: '' }}" alt="Job Top Image Preview" class="h-full w-full object-cover {{ $jobToEdit?->image_url ? '' : 'hidden' }}">
                                        <div id="job-top-image-placeholder" class="text-center p-4 {{ $jobToEdit?->image_url ? 'hidden' : '' }}">
                                            <svg class="mx-auto h-9 w-9 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <p class="mt-1 text-xs font-semibold text-slate-500">No top image selected</p>
                                            <p class="text-[10px] text-slate-400">Upload a custom image or pick a preset</p>
                                        </div>
                                        <span id="job-image-badge" class="absolute top-2 right-2 rounded-full bg-[#102b25]/85 backdrop-blur-md px-2.5 py-0.5 text-[10px] font-bold text-[#e7d8b7] border border-white/20 {{ $jobToEdit?->image_url ? '' : 'hidden' }}">
                                            ★ Active Banner
                                        </span>
                                    </div>
                                    @if ($jobToEdit?->image_url)
                                        <label class="mt-2.5 flex items-center gap-2 cursor-pointer text-xs font-semibold text-[#a24339] hover:underline">
                                            <input type="checkbox" name="remove_image" value="1" id="job-remove-image-chk" onchange="toggleJobRemoveImage(this)" class="rounded border-[#d9cab3] text-[#a24339] focus:ring-[#a24339]">
                                            Remove current top image
                                        </label>
                                    @endif
                                </div>

                                {{-- Upload & Presets Inputs --}}
                                <div class="md:col-span-7 space-y-3">
                                    {{-- File Upload --}}
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Upload Custom Top Image</label>
                                        <input type="file" name="image" id="job-image-input" accept="image/png,image/jpeg,image/webp,image/avif" onchange="previewJobTopImage(event)" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-[#1d3c34] file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-white hover:file:bg-[#254d43] focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                        <p class="mt-1 text-[11px] text-slate-500">Supported: JPG, PNG, WebP, AVIF up to 20MB.</p>
                                    </div>

                                    {{-- Hidden Preset Input --}}
                                    <input type="hidden" name="image_preset" id="job-image-preset" value="">

                                    {{-- 1-Click Luxury Presets --}}
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Or Insert a 1-Click Luxury Preset Background:</label>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                            @php
                                                $jobPresets = [
                                                    ['id' => 'hero-skyline', 'name' => 'City Skyline', 'file' => 'images/luxury/hero-skyline.jpg'],
                                                    ['id' => 'luxury-towers', 'name' => 'Modern Towers', 'file' => 'images/luxury/luxury-towers.jpg'],
                                                    ['id' => 'central-plaza', 'name' => 'Corporate Plaza', 'file' => 'images/luxury/central-plaza.jpg'],
                                                    ['id' => 'panoramic-park', 'name' => 'Park View', 'file' => 'images/luxury/panoramic-park.jpg'],
                                                    ['id' => 'retail-boulevard', 'name' => 'Commercial Hub', 'file' => 'images/luxury/retail-boulevard.jpg'],
                                                ];
                                            @endphp
                                            @foreach ($jobPresets as $preset)
                                                <button type="button" onclick="selectJobPresetBackground('{{ $preset['id'] }}', '{{ asset($preset['file']) }}')" class="group/preset relative flex items-center gap-2 overflow-hidden rounded-lg border border-[#d9cab3] bg-white p-1.5 text-left transition hover:border-[#1d3c34] hover:shadow-sm focus:outline-none cursor-pointer">
                                                    <img src="{{ asset($preset['file']) }}" alt="{{ $preset['name'] }}" class="h-8 w-10 shrink-0 rounded object-cover">
                                                    <span class="truncate text-[11px] font-semibold text-slate-700 group-hover/preset:text-[#1d3c34]">{{ $preset['name'] }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Department / Category</label>
                            <input type="text" name="category" value="{{ old('category', $jobToEdit?->category) }}" placeholder="Sales, Marketing..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Location</label>
                            <input type="text" name="job_location" value="{{ old('job_location', $jobToEdit?->job_location) }}" placeholder="Addis Ababa, Ethiopia" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Employment Type</label>
                            <select name="job_type" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                @foreach (['Full-time', 'Part-time', 'Contract', 'Internship'] as $employmentType)
                                    <option value="{{ $employmentType }}" {{ old('job_type', $jobToEdit?->job_type) === $employmentType ? 'selected' : '' }}>{{ $employmentType }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Salary Range</label>
                            <input type="text" name="salary_range" value="{{ old('salary_range', $jobToEdit?->salary_range) }}" placeholder="ETB 15,000 - 25,000" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Experience Required</label>
                            <input type="text" name="experience_level" value="{{ old('experience_level', $jobToEdit?->experience_level) }}" placeholder="2+ years" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Education Required</label>
                            <input type="text" name="education_level" value="{{ old('education_level', $jobToEdit?->education_level) }}" placeholder="Bachelor's Degree in ..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Application Deadline</label>
                            <input type="date" name="application_deadline" value="{{ old('application_deadline', $jobToEdit?->application_deadline?->format('Y-m-d')) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Status</label>
                            <select name="status" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                <option value="published" {{ old('status', $jobToEdit?->status ?? 'published') === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="draft" {{ old('status', $jobToEdit?->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Apply Link</label>
                            <input type="text" name="apply_link" value="{{ old('apply_link', $jobToEdit?->apply_link) }}" placeholder="https://... or mailto:hr@realestate.com" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <p class="mt-1 text-xs text-slate-500">Shown as the Apply button on the careers page and login page. Leave empty to default to the contact email.</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Job Description</label>
                            <textarea name="content" rows="5" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('content', $jobToEdit?->content) }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Requirements</label>
                            <textarea name="requirements" rows="4" placeholder="One requirement per line..." class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('requirements', $jobToEdit?->requirements) }}</textarea>
                        </div>

                        <div class="md:col-span-2 flex flex-wrap gap-3">
                            <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                                {{ $jobToEdit ? 'Update Job Posting' : 'Post Job Opening' }}
                            </button>
                            @if ($jobToEdit)
                                <a href="{{ route('dashboard', ['section' => 'job-postings']) }}" class="rounded-full border border-[#d9cab3] bg-white px-5 py-3 text-sm font-bold text-[#1d3c34]">Cancel edit</a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- Job Postings Search Bar --}}
                <div class="mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#fbf8f3] p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="relative flex-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input type="text" id="dashboard-jobs-search" placeholder="Search job postings by title, location, category, status..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2 pl-9 pr-8 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                            <button type="button" id="dashboard-jobs-search-clear" class="hidden absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 transition" title="Clear search">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <span id="dashboard-jobs-count" class="shrink-0 rounded-full bg-[#1d3c34]/10 px-3 py-1 text-xs font-bold text-[#1d3c34]">{{ $jobs->count() }} jobs</span>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#2d5d4d] text-[#f8f3eb]">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Top Image</th>
                                <th class="px-5 py-4 font-semibold">Title</th>
                                <th class="px-5 py-4 font-semibold">Applicants</th>
                                <th class="px-5 py-4 font-semibold">Location</th>
                                <th class="px-5 py-4 font-semibold">Deadline</th>
                                <th class="px-5 py-4 font-semibold">Apply Link</th>
                                <th class="px-5 py-4 font-semibold">Status</th>
                                <th class="px-5 py-4 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($jobs as $job)
                                <tr class="dashboard-job-row border-t border-[#e7ddca] bg-white" data-search="{{ strtolower($job->title . ' ' . ($job->job_location ?? '') . ' ' . ($job->category ?? '') . ' ' . $job->status) }}">
                                    <td class="px-5 py-4">
                                        @if ($job->image_url)
                                            <img src="{{ $job->image_url }}" alt="{{ $job->title }}" class="h-12 w-16 rounded-lg object-cover ring-1 ring-[#e7ddca] shadow-sm">
                                        @else
                                            <div class="flex h-12 w-16 items-center justify-center rounded-lg bg-gradient-to-br from-[#dfe8e3] via-[#edf3ee] to-[#e9d9b7] text-[8px] font-bold text-[#1d3c34]">No Image</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-[#1d3c34]">{{ $job->title }}</td>
                                    <td class="px-5 py-4">
                                        @php
                                            $jobApps = $jobApplications->where('blog_post_id', $job->id);
                                            $jobAppsCount = $jobApps->count();
                                            $jobPendingCount = $jobApps->where('status', 'pending')->count();
                                        @endphp
                                        <a href="#job-applications" data-target-section="job-applications" onclick="filterApplicationsByJobTitle({{ json_encode($job->title) }})" class="nav-section-link inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold transition {{ $jobAppsCount > 0 ? 'bg-[#102b25]/10 text-[#102b25] hover:bg-[#102b25] hover:text-white' : 'bg-slate-100 text-slate-400' }}">
                                            <span>{{ $jobAppsCount }} {{ Str::plural('applicant', $jobAppsCount) }}</span>
                                            @if ($jobPendingCount > 0)
                                                <span class="rounded-full bg-[#f9ecd0] px-1.5 py-0.2 text-[9px] font-black text-[#9b6c17]">{{ $jobPendingCount }} new</span>
                                            @endif
                                        </a>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $job->job_location ?? '—' }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $job->application_deadline?->format('M d, Y') ?? '—' }}</td>
                                    <td class="px-5 py-4 text-xs">
                                        @if ($job->apply_link)
                                            <a href="{{ $job->apply_link }}" target="_blank" class="font-semibold text-[#2d5d4d] hover:underline break-all block" title="{{ $job->apply_link }}">
                                                {{ $job->apply_link }}
                                            </a>
                                        @else
                                            <span class="text-slate-400">Default email</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full {{ $job->status === 'published' ? 'bg-[#dfeee4] text-[#1d3c34]' : 'bg-[#edf2ee] text-[#2d5d4d]' }} px-2.5 py-1 text-xs font-bold">
                                            {{ ucfirst($job->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('dashboard', ['edit_job' => $job->id, 'section' => 'job-postings']) }}" class="rounded-full bg-[#1d3c34] px-3 py-2 text-xs font-bold text-white">Edit</a>
                                            <form method="POST" action="{{ route('dashboard.posts.togglePublish', $job) }}">
                                                @csrf
                                                <button type="submit" class="rounded-full border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                                                    {{ $job->status === 'published' ? 'Unpublish' : 'Publish' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('dashboard.posts.delete', $job) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-3 py-2 text-xs font-bold text-[#a24339]" data-confirm="Delete this job posting?">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-6 text-center text-slate-500">No job postings yet.</td>
                                </tr>
                            @endforelse
                            <tr id="dashboard-jobs-no-results" class="hidden border-t border-[#e7ddca] bg-white">
                                <td colspan="8" class="px-5 py-6 text-center text-slate-500">No job postings found matching your search.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if ($jobs instanceof \Illuminate\Pagination\LengthAwarePaginator && $jobs->hasPages())
                    <div class="mt-6 border-t border-[#e7ddca] pt-4">
                        {{ $jobs->fragment('job-postings')->links() }}
                    </div>
                @endif
            </section>
        @endif

        {{-- JOB APPLICATIONS & RESUMES MANAGEMENT SECTION --}}
        <section id="job-applications" data-section="job-applications" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[#1d3c34]">Job Applications &amp; Candidate Resumes</h2>
                    <p class="mt-1 text-xs sm:text-sm text-slate-500">Inspect candidate profiles, read cover letters, and download attached PDF or DOCX resumes.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-[#1d3c34] px-3.5 py-1 text-xs font-bold text-white shadow-xs">
                        {{ $jobApplications instanceof \Illuminate\Pagination\LengthAwarePaginator ? $jobApplications->total() : $jobApplications->count() }} Total
                    </span>
                    @if ($pendingApplicationsCount > 0)
                        <span class="rounded-full bg-[#f9ecd0] px-3.5 py-1 text-xs font-bold text-[#9b6c17] border border-[#d4af37]/30">
                            {{ $pendingApplicationsCount }} Pending Review
                        </span>
                    @endif
                </div>
            </div>

            {{-- Application Metrics --}}
            <div class="mt-6 grid gap-4 grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Applicants</p>
                    <p class="mt-2 text-2xl font-black text-[#1d3c34]">{{ $jobApplications->count() }}</p>
                </div>
                <div class="rounded-2xl border border-[#d4af37]/30 bg-[#fefcf6] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#9b6c17]">Pending Review</p>
                    <p class="mt-2 text-2xl font-black text-[#9b6c17]">{{ $pendingApplicationsCount }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Shortlisted</p>
                    <p class="mt-2 text-2xl font-black text-emerald-800">{{ $jobApplications->where('status', 'shortlisted')->count() }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Resumes on File</p>
                    <p class="mt-2 text-2xl font-black text-slate-700">{{ $jobApplications->whereNotNull('resume_path')->count() }}</p>
                </div>
            </div>

            {{-- Search & Filter Controls --}}
            <div class="mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#fbf8f3] p-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" id="dashboard-applications-search" placeholder="Search applicants by name, email, phone, role, education, city..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2 pl-9 pr-8 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#1d3c34]" oninput="filterJobApplicationsTable()">
                    </div>

                    <!-- Status Filter Pills -->
                    <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
                        <button type="button" onclick="filterApplicationsByStatus('all')" class="app-status-filter-btn rounded-lg bg-[#1d3c34] text-white px-3 py-1.5 transition" data-status="all">All</button>
                        <button type="button" onclick="filterApplicationsByStatus('pending')" class="app-status-filter-btn rounded-lg bg-white border border-[#d9cab3] text-slate-700 px-3 py-1.5 hover:bg-[#fbf9f5] transition" data-status="pending">Pending</button>
                        <button type="button" onclick="filterApplicationsByStatus('shortlisted')" class="app-status-filter-btn rounded-lg bg-white border border-[#d9cab3] text-slate-700 px-3 py-1.5 hover:bg-[#fbf9f5] transition" data-status="shortlisted">Shortlisted</button>
                        <button type="button" onclick="filterApplicationsByStatus('reviewed')" class="app-status-filter-btn rounded-lg bg-white border border-[#d9cab3] text-slate-700 px-3 py-1.5 hover:bg-[#fbf9f5] transition" data-status="reviewed">Reviewed</button>
                        <button type="button" onclick="filterApplicationsByStatus('rejected')" class="app-status-filter-btn rounded-lg bg-white border border-[#d9cab3] text-slate-700 px-3 py-1.5 hover:bg-[#fbf9f5] transition" data-status="rejected">Rejected</button>
                    </div>
                </div>
            </div>

            {{-- Applications Data Table --}}
            <div class="mt-4 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
                <table class="min-w-full text-left text-sm" id="dashboard-job-applications-table">
                    <thead class="bg-[#f9f4ed] text-xs font-bold uppercase tracking-[0.14em] text-[#587165]">
                        <tr>
                            <th class="px-5 py-4">Applicant</th>
                            <th class="px-5 py-4">Position</th>
                            <th class="px-5 py-4">Contact</th>
                            <th class="px-5 py-4">Experience &amp; Edu</th>
                            <th class="px-5 py-4">Resume File</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e8dc] bg-white">
                        @forelse ($jobApplications as $appItem)
                            <tr class="job-application-row hover:bg-[#fbf9f5] transition" data-status="{{ $appItem->status }}" data-search="{{ strtolower($appItem->full_name.' '.$appItem->email.' '.$appItem->phone.' '.$appItem->job_title.' '.$appItem->current_location.' '.$appItem->education_level.' '.$appItem->experience_years) }}">
                                {{-- Applicant --}}
                                <td class="px-5 py-4">
                                    <div class="font-extrabold text-[#1d3c34]">{{ $appItem->full_name }}</div>
                                    @if ($appItem->current_location)
                                        <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                            <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                            {{ $appItem->current_location }}
                                        </div>
                                    @endif
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        Applied {{ $appItem->created_at->diffForHumans() }}
                                    </div>
                                </td>

                                {{-- Position --}}
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-[#102b25]/10 px-2.5 py-0.5 text-xs font-bold text-[#102b25]">
                                        {{ $appItem->job_title }}
                                    </span>
                                </td>

                                {{-- Contact --}}
                                <td class="px-5 py-4 text-xs">
                                    <a href="mailto:{{ $appItem->email }}" class="font-semibold text-slate-800 hover:text-[#1d3c34] hover:underline block truncate max-w-[180px]">
                                        {{ $appItem->email }}
                                    </a>
                                    <a href="tel:{{ $appItem->phone }}" class="text-slate-600 hover:underline block mt-0.5">
                                        {{ $appItem->phone }}
                                    </a>
                                </td>

                                {{-- Experience & Education --}}
                                <td class="px-5 py-4 text-xs">
                                    <div class="font-semibold text-slate-700">{{ $appItem->experience_years ?: 'Not specified' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $appItem->education_level ?: '—' }}</div>
                                </td>

                                {{-- Resume Download Button with PDF / DOCX badge --}}
                                <td class="px-5 py-4">
                                    @if ($appItem->resume_path)
                                        <a href="{{ route('dashboard.job-applications.download', $appItem) }}" class="inline-flex items-center gap-2 rounded-xl border border-[#d9cab3] bg-[#fbf9f5] px-3 py-1.5 text-xs font-bold text-slate-800 hover:bg-[#102b25] hover:text-white transition shadow-xs group">
                                            @if ($appItem->is_pdf)
                                                <span class="rounded bg-[#e02424] px-1.5 py-0.5 text-[9px] font-black text-white">PDF</span>
                                            @else
                                                <span class="rounded bg-[#2563eb] px-1.5 py-0.5 text-[9px] font-black text-white">DOCX</span>
                                            @endif
                                            <span class="max-w-[110px] truncate">{{ $appItem->resume_original_name }}</span>
                                            <svg class="h-3.5 w-3.5 text-slate-400 group-hover:text-white transition shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>
                                        <div class="text-[10px] text-slate-400 mt-0.5 pl-1">{{ $appItem->formatted_file_size }}</div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">No document</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-4">
                                    @php
                                        $statusClass = match ($appItem->status) {
                                            'pending' => 'bg-[#f9ecd0] text-[#9b6c17] border-[#d4af37]/40',
                                            'shortlisted' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                            'reviewed' => 'bg-blue-100 text-blue-800 border-blue-300',
                                            'rejected' => 'bg-rose-100 text-rose-800 border-rose-300',
                                            'hired' => 'bg-purple-100 text-purple-800 border-purple-300',
                                            default => 'bg-slate-100 text-slate-800 border-slate-300',
                                        };
                                    @endphp
                                    <form method="POST" action="{{ route('dashboard.job-applications.status', $appItem) }}" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()" class="rounded-full border px-2.5 py-1 text-xs font-bold cursor-pointer focus:outline-none focus:ring-1 focus:ring-[#1d3c34] {{ $statusClass }}">
                                            <option value="pending" {{ $appItem->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="reviewed" {{ $appItem->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                            <option value="shortlisted" {{ $appItem->status === 'shortlisted' ? 'selected' : '' }}>Shortlisted</option>
                                            <option value="rejected" {{ $appItem->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                            <option value="hired" {{ $appItem->status === 'hired' ? 'selected' : '' }}>Hired</option>
                                        </select>
                                    </form>
                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- View Details Modal Button -->
                                        <button type="button" onclick="viewCandidateDetails({{ json_encode([
                                            'id' => $appItem->id,
                                            'name' => $appItem->full_name,
                                            'job_title' => $appItem->job_title,
                                            'email' => $appItem->email,
                                            'phone' => $appItem->phone,
                                            'location' => $appItem->current_location,
                                            'experience' => $appItem->experience_years,
                                            'education' => $appItem->education_level,
                                            'portfolio' => $appItem->portfolio_url,
                                            'expected_salary' => $appItem->expected_salary,
                                            'start_date' => $appItem->available_start_date,
                                            'cover_letter' => $appItem->cover_letter,
                                            'resume_download_url' => route('dashboard.job-applications.download', $appItem),
                                            'resume_name' => $appItem->resume_original_name,
                                            'resume_size' => $appItem->formatted_file_size,
                                            'is_pdf' => $appItem->is_pdf,
                                            'status' => $appItem->status,
                                            'status_action' => route('dashboard.job-applications.status', $appItem),
                                            'applied_at' => $appItem->created_at->format('M d, Y h:i A'),
                                        ]) }})" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#254d43] transition cursor-pointer">
                                            View
                                        </button>

                                        <!-- Delete Application Form -->
                                        <form method="POST" action="{{ route('dashboard.job-applications.destroy', $appItem) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-3 py-1.5 text-xs font-bold text-[#a24339] hover:bg-[#a24339] hover:text-white transition cursor-pointer" onclick="return confirm('Are you sure you want to delete this job application from {{ addslashes($appItem->full_name) }}?')">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="dashboard-applications-empty-row">
                                <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f4efe7] text-slate-400 mb-3">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="font-bold text-[#1d3c34]">No job applications received yet.</p>
                                    <p class="text-xs text-slate-400 mt-1">Applications submitted via the public careers page or home page will appear here with attached resumes.</p>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="dashboard-applications-no-results" class="hidden border-t border-[#e7ddca] bg-white">
                            <td colspan="7" class="px-5 py-8 text-center text-slate-500">No applications match your search filter.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if ($jobApplications->hasPages())
                <div class="mt-6 border-t border-[#e7ddca] pt-4">
                    {{ $jobApplications->fragment('job-applications')->links() }}
                </div>
            @endif
        </section>

        {{-- CANDIDATE DETAILS MODAL (Admin View) --}}
        <div id="admin-candidate-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" onclick="closeCandidateDetailsModal()"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-2xl rounded-3xl border border-[#d9cab3] bg-white shadow-2xl overflow-hidden my-8">
                    <!-- Modal Header -->
                    <div class="p-6 bg-gradient-to-r from-[#102b25] to-[#1c473c] text-white flex items-start justify-between">
                        <div>
                            <span class="inline-block rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-3 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#e7d8b7]" id="modal-c-applied-at">Applied</span>
                            <h3 class="mt-2 text-2xl font-black text-white" id="modal-c-name">Candidate Name</h3>
                            <p class="text-xs text-[#c2d7cb]" id="modal-c-job-title">Position Applied For</p>
                        </div>
                        <button type="button" onclick="closeCandidateDetailsModal()" class="rounded-full bg-white/10 p-2 text-white hover:bg-white/20 transition cursor-pointer">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-5 bg-[#fffdfa] max-h-[75vh] overflow-y-auto">
                        <!-- Contact Grid -->
                        <div class="grid grid-cols-2 gap-3 p-4 rounded-2xl bg-[#f9f5ee] border border-[#e7ddca] text-xs">
                            <div>
                                <span class="font-bold text-slate-500 uppercase text-[10px] block">Email</span>
                                <a id="modal-c-email" href="#" class="font-extrabold text-[#102b25] hover:underline block truncate"></a>
                            </div>
                            <div>
                                <span class="font-bold text-slate-500 uppercase text-[10px] block">Phone</span>
                                <a id="modal-c-phone" href="#" class="font-extrabold text-[#102b25] hover:underline block"></a>
                            </div>
                            <div>
                                <span class="font-bold text-slate-500 uppercase text-[10px] block">Location</span>
                                <span id="modal-c-location" class="font-semibold text-slate-800"></span>
                            </div>
                            <div>
                                <span class="font-bold text-slate-500 uppercase text-[10px] block">Availability</span>
                                <span id="modal-c-start-date" class="font-semibold text-slate-800"></span>
                            </div>
                        </div>

                        <!-- Qualifications -->
                        <div class="grid grid-cols-3 gap-3 text-xs">
                            <div class="p-3 rounded-xl border border-[#e7ddca] bg-white">
                                <span class="text-[10px] font-bold uppercase text-slate-400 block">Experience</span>
                                <span id="modal-c-experience" class="font-bold text-slate-800"></span>
                            </div>
                            <div class="p-3 rounded-xl border border-[#e7ddca] bg-white">
                                <span class="text-[10px] font-bold uppercase text-slate-400 block">Education</span>
                                <span id="modal-c-education" class="font-bold text-slate-800"></span>
                            </div>
                            <div class="p-3 rounded-xl border border-[#e7ddca] bg-white">
                                <span class="text-[10px] font-bold uppercase text-slate-400 block">Salary Expectation</span>
                                <span id="modal-c-salary" class="font-bold text-slate-800"></span>
                            </div>
                        </div>

                        <!-- Portfolio Link -->
                        <div id="modal-c-portfolio-wrapper" class="hidden text-xs">
                            <span class="font-bold text-slate-500 uppercase text-[10px] block mb-1">Portfolio / LinkedIn</span>
                            <a id="modal-c-portfolio" href="#" target="_blank" rel="noopener" class="text-[#102b25] font-bold underline hover:text-[#d4af37] break-all"></a>
                        </div>

                        <!-- Resume Card -->
                        <div class="p-4 rounded-2xl border-2 border-dashed border-[#d4af37]/60 bg-[#fcf9f2] flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div id="modal-c-resume-badge" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#e02424] text-white font-black text-xs">PDF</div>
                                <div>
                                    <p id="modal-c-resume-name" class="font-extrabold text-sm text-[#102b25] truncate max-w-[250px]"></p>
                                    <p id="modal-c-resume-size" class="text-xs text-slate-500"></p>
                                </div>
                            </div>
                            <a id="modal-c-resume-download" href="#" class="inline-flex items-center gap-2 rounded-full bg-[#102b25] px-5 py-2.5 text-xs font-bold text-[#e7d8b7] hover:bg-[#1a3d34] transition shadow-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Download CV</span>
                            </a>
                        </div>

                        <!-- Cover Letter -->
                        <div>
                            <span class="font-bold text-xs uppercase tracking-wider text-[#102b25] block mb-2">Cover Letter / Personal Statement:</span>
                            <div id="modal-c-cover-letter" class="p-4 rounded-2xl bg-[#f8f5ef] border border-[#e7ddca] text-xs leading-relaxed text-slate-700 whitespace-pre-wrap max-h-48 overflow-y-auto">
                            </div>
                        </div>

                        <!-- Status Update Form -->
                        <form id="modal-c-status-form" method="POST" action="" class="pt-3 border-t border-[#f0e8dc] flex items-center justify-between">
                            @csrf
                            @method('PATCH')
                            <div class="flex items-center gap-3">
                                <label for="modal-c-status-select" class="text-xs font-bold text-slate-700">Update Status:</label>
                                <select id="modal-c-status-select" name="status" class="rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-xs font-bold text-slate-800">
                                    <option value="pending">Pending</option>
                                    <option value="reviewed">Reviewed</option>
                                    <option value="shortlisted">Shortlisted</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="hired">Hired</option>
                                </select>
                                <button type="submit" class="rounded-xl bg-[#102b25] px-4 py-1.5 text-xs font-bold text-white hover:bg-[#254d43] transition">
                                    Save
                                </button>
                            </div>
                            <button type="button" onclick="closeCandidateDetailsModal()" class="rounded-xl border border-[#d9cab3] bg-white px-4 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                Close
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        @if ($posts->isNotEmpty())
            <section class="mt-10 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <h2 class="text-2xl font-black text-[#1d3c34]">Latest Blog & News</h2>
                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    @foreach ($posts as $post)
                        <article class="rounded-[1.25rem] border border-[#e7ddca] bg-[#f9f4ed] p-5">
                            <div class="flex items-center justify-between gap-3">
                                <span class="rounded-full bg-[#dfeee4] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#1d3c34]">{{ $post->type }}</span>
                                <span class="text-xs text-slate-500">{{ $post->user->name }}</span>
                            </div>
                            <h3 class="mt-4 text-xl font-black text-[#1d3c34]">{{ $post->title }}</h3>
                            <p class="mt-3 line-clamp-4 text-sm leading-6 text-slate-600">{{ $post->content }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Section: System Activity & Audit Trail (Full Multi-Actor Observability) --}}
