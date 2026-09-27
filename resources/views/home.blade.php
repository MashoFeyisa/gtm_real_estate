@extends('app')

@section('title', $siteBrand['name'].' — Premium Living Spaces')
@section('description', 'Find luxury villas, modern apartments, and smart investments in trusted neighborhoods with a team that knows the market deeply.')

@section('content')
    @php
        $getPropertyCategory = fn ($property) => strtolower((string) ($property->property_category ?? $property->category ?? 'home'));
    @endphp

    <section id="home" class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
        <div class="grid items-center gap-10 lg:grid-cols-[1.2fr_0.8fr]">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-[#d7c8a9] bg-[#f1e4cf] px-3 py-1 text-xs font-bold uppercase tracking-[0.22em] text-[#1d3c34]">
                    Premium living spaces
                </div>
                <h1 class="mt-6 text-4xl font-black tracking-tight text-[#1d3c34] sm:text-5xl md:text-6xl">
                    Discover homes that feel like your next chapter.
                </h1>
                <p class="mt-5 max-w-xl text-lg leading-8 text-slate-600">
                    Find luxury villas, modern apartments, and smart investments in trusted neighborhoods with a team that knows the market deeply.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="{{ route('properties.index') }}" class="rounded-full bg-[#1d3c34] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-[#1d3c34]/20 transition hover:bg-[#264d41]">Explore Listings</a>
                    <a href="{{ url('/app#contact') }}" class="rounded-full border border-[#b9a98b] bg-[#f9f4ec] px-6 py-3 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">Book a Visit</a>
                </div>
                <div class="mt-8 flex flex-wrap gap-6 text-sm text-slate-600">
                    <div><span class="block text-2xl font-black text-[#1d3c34]">1.2k+</span> happy buyers</div>
                    <div><span class="block text-2xl font-black text-[#1d3c34]">{{ $homeStats['sold'] ?? 0 }}</span> properties sold</div>
                    <div><span class="block text-2xl font-black text-[#1d3c34]">18 yrs</span> market expertise</div>
                </div>
            </div>

            <div class="relative">
                @php
                    $heroProperty = $featuredProperties->first();
                @endphp
                <div class="rounded-[2rem] border border-[#d9cab3] bg-[#fffaf4] p-6 shadow-[0_30px_80px_rgba(29,60,52,0.18)]">
                    <div class="rounded-[1.5rem] bg-gradient-to-br from-[#1d3c34] via-[#2d5145] to-[#5b6d60] p-4 text-[#f9f3e9] md:p-5">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#e7d8b7]">Featured property</p>
                            <span class="rounded-full bg-[#e5d5af] px-2.5 py-1 text-xs font-bold text-[#1d3c34]">{{ $heroProperty ? ucfirst($heroProperty->type) : 'For Sale' }}</span>
                        </div>

                        @if ($heroProperty)
                            <a href="{{ route('properties.show', $heroProperty->slug) }}" class="mt-4 block overflow-hidden rounded-2xl">
                                @if ($heroProperty->image_path)
                                    <img src="{{ asset('storage/' . $heroProperty->image_path) }}" alt="{{ $heroProperty->title }}" class="h-48 w-full object-cover transition duration-300 hover:scale-105">
                                @else
                                    <div class="flex h-48 items-center justify-center bg-gradient-to-br from-[#dfe8e3] via-[#edf3ee] to-[#e9d9b7] text-2xl font-black text-[#1d3c34]">
                                        {{ Str::limit($heroProperty->title, 18) }}
                                    </div>
                                @endif
                            </a>
                            <h2 class="mt-4 text-2xl font-black">
                                <a href="{{ route('properties.show', $heroProperty->slug) }}" class="transition hover:text-[#e7d8b7]">{{ $heroProperty->title }}</a>
                            </h2>
                            <p class="mt-1 text-sm text-[#dfeee4]">{{ $heroProperty->bedrooms }} bed • {{ $heroProperty->bathrooms }} bath • {{ number_format($heroProperty->area) }} sq ft</p>
                            <div class="mt-5 rounded-2xl bg-[#f6f0e2] p-4 text-[#1d3c34]">
                                <div class="flex items-end justify-between">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.2em] text-[#617466]">{{ $heroProperty->type === 'rent' ? 'Starting at' : 'Price' }}</p>
                                        <p class="mt-2 text-2xl font-black">${{ number_format($heroProperty->price) }}{{ $heroProperty->type === 'rent' ? '/mo' : '' }}</p>
                                    </div>
                                    <a href="{{ route('properties.show', $heroProperty->slug) }}" class="rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-white transition hover:bg-[#254d43]">View details</a>
                                </div>
                            </div>
                        @else
                            <div class="mt-4 overflow-hidden rounded-2xl bg-gradient-to-br from-[#dfe8e3] via-[#edf3ee] to-[#e9d9b7]">
                                <div class="flex h-48 items-center justify-center text-2xl font-black text-[#1d3c34]">Greenview Residence</div>
                            </div>
                            <h2 class="mt-4 text-2xl font-black">Greenview Residence</h2>
                            <p class="mt-1 text-sm text-[#dfeee4]">4 bed • 3 bath • 2,600 sq ft</p>
                            <div class="mt-5 rounded-2xl bg-[#f6f0e2] p-4 text-[#1d3c34]">
                                <div class="flex items-end justify-between">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.2em] text-[#617466]">Starting at</p>
                                        <p class="mt-2 text-2xl font-black">$480k</p>
                                    </div>
                                    <div class="rounded-full bg-[#d9cab3] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">Prime location</div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="mt-6 grid grid-cols-3 gap-2 sm:gap-3">
                        <div class="rounded-2xl bg-[#edf2ed] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-[#60716c]">Homes</p>
                            <p class="mt-3 text-2xl font-black text-[#1d3c34]">{{ $homeStats['homes'] }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#f6e9d2] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-[#60716c]">Villas</p>
                            <p class="mt-3 text-2xl font-black text-[#1d3c34]">{{ $homeStats['villas'] }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#e9eee7] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-[#60716c]">Apartments</p>
                            <p class="mt-3 text-2xl font-black text-[#1d3c34]">{{ $homeStats['apartments'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Properties</p>
                <h2 class="mt-2 text-3xl font-black text-[#1d3c34]">Featured Properties</h2>
            </div>
            <a href="{{ route('properties.index') }}" class="text-sm font-semibold text-[#1d3c34] hover:text-[#244d43]">View all</a>
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-3">
            @forelse ($featuredProperties as $property)
                <a href="{{ route('properties.show', $property->slug) }}" class="block overflow-hidden rounded-[1.75rem] border border-[#d9cab3] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    @if ($property->image_path)
                        <img src="{{ asset('storage/' . $property->image_path) }}" alt="{{ $property->title }}" class="h-52 w-full object-cover transition duration-300 hover:scale-105">
                    @else
                        <div class="flex h-52 items-center justify-center bg-gradient-to-br from-[#dfe8e3] via-[#edf3ee] to-[#e9d9b7] text-xl font-black text-[#1d3c34]">
                            {{ Str::limit($property->title, 18) }}
                        </div>
                    @endif
                    <div class="p-6">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">{{ ucfirst($getPropertyCategory($property)) }}</p>
                        <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">{{ $property->title }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $property->bedrooms }} bed • {{ $property->bathrooms }} bath • {{ number_format($property->area) }} sq ft</p>
                        <div class="mt-5 flex items-center justify-between">
                            <span class="text-xl font-black text-[#1d3c34]">${{ number_format($property->price) }}</span>
                            <span class="text-sm text-slate-500">{{ $property->city ?: 'Featured' }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <article class="overflow-hidden rounded-[1.75rem] border border-[#d9cab3] bg-white shadow-sm">
                    <div class="h-52 bg-gradient-to-br from-[#dfe8e3] via-[#edf3ee] to-[#e9d9b7]"></div>
                    <div class="p-6">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">For Sale</p>
                        <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">Verona Heights</h3>
                        <p class="mt-2 text-sm text-slate-600">3 bed • 2 bath • 1,980 sq ft</p>
                        <div class="mt-5 flex items-center justify-between">
                            <span class="text-xl font-black text-[#1d3c34]">$410k</span>
                            <span class="text-sm text-slate-500">Harar</span>
                        </div>
                    </div>
                </article>
                <article class="overflow-hidden rounded-[1.75rem] border border-[#d9cab3] bg-[#f4efe7] shadow-sm">
                    <div class="h-52 bg-gradient-to-br from-[#d8c9a8] via-[#f8f3eb] to-[#e7ede7]"></div>
                    <div class="p-6">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">For Rent</p>
                        <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">Aster Apartments</h3>
                        <p class="mt-2 text-sm text-slate-600">2 bed • 2 bath • 1,240 sq ft</p>
                        <div class="mt-5 flex items-center justify-between">
                            <span class="text-xl font-black text-[#1d3c34]">$1,200/mo</span>
                            <span class="text-sm text-slate-500">City Center</span>
                        </div>
                    </div>
                </article>
                <article class="overflow-hidden rounded-[1.75rem] border border-[#d9cab3] bg-white shadow-sm">
                    <div class="h-52 bg-gradient-to-br from-[#e3ece7] via-[#f7f3eb] to-[#dfe8dc]"></div>
                    <div class="p-6">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">Investment</p>
                        <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">Cedar Grove</h3>
                        <p class="mt-2 text-sm text-slate-600">4 bed • 3 bath • 2,600 sq ft</p>
                        <div class="mt-5 flex items-center justify-between">
                            <span class="text-xl font-black text-[#1d3c34]">$540k</span>
                            <span class="text-sm text-slate-500">Bole</span>
                        </div>
                    </div>
                </article>
            @endforelse
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">New listings</p>
                <h2 class="mt-2 text-3xl font-black text-[#1d3c34]">Latest Properties</h2>
            </div>
            <a href="{{ route('properties.index') }}" class="text-sm font-semibold text-[#1d3c34] hover:text-[#244d43]">Browse more</a>
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-3">
            @forelse ($latestProperties as $property)
                <a href="{{ route('properties.show', $property->slug) }}" class="block rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">{{ $property->featured ? 'Featured' : 'New' }}</p>
                    <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">{{ $property->title }}</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $property->description ? Str::limit(strip_tags($property->description), 120) : 'Premium property available in a strong investment area.' }}</p>
                </a>
            @empty
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">New</p>
                    <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">Sunrise Apartments</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">Modern, light-filled homes with premium finishes and neighborhood access.</p>
                </div>
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-[#f4efe7] p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">Updated</p>
                    <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">Hillcrest Villas</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">Spacious villas designed for lifestyle, comfort, and long-term returns.</p>
                </div>
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">Popular</p>
                    <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">Greenway Residences</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">A growing urban community balancing convenience, green space, and value.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Developments</p>
                <h2 class="mt-2 text-3xl font-black text-[#1d3c34]">Featured Projects</h2>
            </div>
            <a href="{{ route('projects') }}" class="text-sm font-semibold text-[#1d3c34] hover:text-[#244d43]">Explore projects</a>
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-3">
            @forelse ($featuredProjects as $project)
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-gradient-to-br from-[#1d3c34] to-[#2f5248] p-6 text-[#f8f3eb] shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#d9cab3]">{{ ucfirst($project->type) }}</p>
                    <h3 class="mt-4 text-xl font-bold">{{ $project->title }}</h3>
                    <p class="mt-3 text-sm leading-7 text-[#eaf3ec]">{{ Str::limit(strip_tags($project->content), 140) }}</p>
                </div>
            @empty
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-gradient-to-br from-[#1d3c34] to-[#2f5248] p-6 text-[#f8f3eb] shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#d9cab3]">Luxury</p>
                    <h3 class="mt-4 text-xl font-bold">Emerald Heights</h3>
                    <p class="mt-3 text-sm leading-7 text-[#eaf3ec]">Residential living designed around comfort, green views, and daily convenience.</p>
                </div>
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-[#f4efe7] p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">Commercial</p>
                    <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">Harar Square</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">Mixed-use development and retail spaces scheduled for the next growth corridor.</p>
                </div>
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">Future plan</p>
                    <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">Oakland Park</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">A connected master-planned community focused on smart, sustainable growth.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section id="news" class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Blog & News</p>
                <h2 class="mt-2 text-3xl font-black text-[#1d3c34]">Latest News</h2>
            </div>
            <a href="{{ route('blogs') }}" class="text-sm font-semibold text-[#1d3c34] hover:text-[#244d43]">View all</a>
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-3">
            @php
                $publicPosts = $posts->take(3);
            @endphp

            @forelse ($publicPosts as $post)
                <article class="overflow-hidden rounded-[1.75rem] border border-[#d9cab3] bg-white shadow-sm">
                    @if ($post->image_path)
                        <img src="{{ asset('storage/' . $post->image_path) }}" alt="{{ $post->title }}" class="h-48 w-full object-cover">
                    @endif
                    <div class="p-6">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">{{ ucfirst($post->type) }}</p>
                            @if ($post->category)
                                <span class="rounded-full bg-[#eef2ee] px-2 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#1d3c34]">{{ $post->category }}</span>
                            @endif
                        </div>
                        <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">{{ $post->title }}</h3>
                        <p class="mt-3 text-base leading-7 text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 150) }}</p>
                        <div class="mt-6 flex items-center justify-between gap-3">
                            <span class="inline-block text-xs font-semibold uppercase tracking-[0.18em] text-[#1d3c34]">By {{ $post->author_name ?? $post->user->name }}</span>
                            @if ($post->published_at)
                                <span class="text-xs text-slate-500">{{ $post->published_at->format('M d, Y') }}</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <article class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">Blog</p>
                    <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">How to choose the right neighborhood</h3>
                    <p class="mt-3 text-base leading-7 text-slate-600">Learn what matters most when buying a property, from schools to commute time and future growth.</p>
                    <a href="#" class="mt-6 inline-block text-sm font-semibold text-[#1d3c34]">Read more</a>
                </article>

                <article class="rounded-[1.75rem] border border-[#d9cab3] bg-[#f4efe7] p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">News</p>
                    <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">New housing projects in the city center</h3>
                    <p class="mt-3 text-base leading-7 text-slate-600">Several modern residential buildings are opening this quarter with flexible payment plans and smart features.</p>
                    <a href="#" class="mt-6 inline-block text-sm font-semibold text-[#1d3c34]">Read more</a>
                </article>

                <article class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">Market</p>
                    <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">Investment tips for first-time buyers</h3>
                    <p class="mt-3 text-base leading-7 text-slate-600">Discover practical steps to evaluate property value, rental demand, and long-term return potential.</p>
                    <a href="#" class="mt-6 inline-block text-sm font-semibold text-[#1d3c34]">Read more</a>
                </article>
            @endforelse
        </div>
    </section>

    <section id="about" class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="rounded-[2rem] border border-[#d9cab3] bg-gradient-to-r from-[#f8f3eb] to-[#eef2ee] p-8 shadow-sm">
            <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">About {{ $siteBrand['name'] }}</p>
            <h2 class="mt-3 text-3xl font-black text-[#1d3c34]">Trusted guidance for every move</h2>
            <p class="mt-4 max-w-3xl text-lg leading-8 text-slate-600">
                We help buyers, sellers, and investors discover exceptional properties with trust, transparency, and local expertise. From first viewing to final paperwork, we make every step clear and confident.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Client feedback</p>
                <h2 class="mt-2 text-3xl font-black text-[#1d3c34]">Testimonials</h2>
            </div>
        </div>
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @forelse ($testimonials as $testimonial)
                <div class="flex flex-col rounded-[1.5rem] border border-[#d9cab3] {{ $loop->index % 2 === 1 ? 'bg-[#f4efe7]' : 'bg-white' }} p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-bold text-[#9b6c17]">{{ str_repeat('★', $testimonial->rating) }}{{ str_repeat('☆', 5 - $testimonial->rating) }}</span>
                        @if ($testimonial->created_at)
                            <span class="text-[11px] text-slate-500">{{ $testimonial->created_at->format('M d, Y') }}</span>
                        @endif
                    </div>
                    <p class="mt-3 flex-1 text-sm leading-7 text-slate-600">“{{ Str::limit($testimonial->message, 220) }}”</p>
                    <div class="mt-4 flex items-center gap-3 border-t border-[#e7dcc4] pt-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#1d3c34] text-xs font-black text-[#f8f3eb]">
                            {{ collect(explode(' ', $testimonial->name))->map(fn ($word) => Str::upper(Str::substr($word, 0, 1)))->take(2)->implode('') }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-[#1d3c34]">{{ $testimonial->name }}</p>
                            @if ($testimonial->agent)
                                <p class="truncate text-xs text-slate-500">Client of {{ $testimonial->agent->name }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm">
                    <p class="text-lg leading-8 text-slate-600">“The team helped us find the right home in less than two weeks, and the process felt clear from start to finish.”</p>
                    <div class="mt-5 text-sm font-bold text-[#1d3c34]">Amanuel & Selam</div>
                </div>
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#f4efe7] p-5 shadow-sm">
                    <p class="text-lg leading-8 text-slate-600">“Their local knowledge and honest guidance gave us confidence in every investment decision.”</p>
                    <div class="mt-5 text-sm font-bold text-[#1d3c34]">Hana T.</div>
                </div>
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm">
                    <p class="text-lg leading-8 text-slate-600">“From viewings to paperwork, we felt supported at every step and truly valued as clients.”</p>
                    <div class="mt-5 text-sm font-bold text-[#1d3c34]">Tesfaye G.</div>
                </div>
            @endforelse
        </div>
    </section>

    <section id="agents" class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Our agents</p>
                <h2 class="mt-2 text-3xl font-black text-[#1d3c34]">Real people, local expertise</h2>
            </div>
            <a href="{{ route('agents') }}" class="text-sm font-semibold text-[#1d3c34] hover:text-[#244d43]">All agents</a>
        </div>
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @php
                $displayAgents = ($agents ?? collect())->take(3);
            @endphp

            @forelse ($displayAgents as $index => $agent)
                <div class="overflow-hidden rounded-[1.5rem] border border-[#d9cab3] {{ $index % 2 === 1 ? 'bg-[#f4efe7]' : 'bg-white' }} shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <a href="{{ route('agents.show', $agent) }}" class="block h-44 w-full overflow-hidden bg-[#e8efe8]">
                        <img src="{{ $agent->photo_url }}" alt="{{ $agent->name }}" class="h-full w-full object-cover">
                    </a>
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-[#1d3c34]">
                            <a href="{{ route('agents.show', $agent) }}" class="transition hover:text-[#2e5a4c]">{{ $agent->name }}</a>
                        </h3>
                        <p class="mt-1 text-sm text-slate-600">{{ $agent->bio ?: 'Property consultant' }}</p>
                        @if ($agent->feedback_count > 0)
                            <p class="mt-1 text-xs font-bold text-[#9b6c17]">{{ number_format($agent->average_rating, 1) }} ★ ({{ $agent->feedback_count }})</p>
                        @endif
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ route('agents.show', $agent) }}" class="rounded-full bg-[#1d3c34] px-4 py-1.5 text-xs font-bold uppercase tracking-[0.14em] text-white transition hover:bg-[#254d43]">Contact {{ $agent->name }}</a>
                            @if ($agent->phone)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $agent->phone) }}" class="rounded-full border border-[#b9a98b] px-4 py-1.5 text-xs font-bold uppercase tracking-[0.14em] text-[#1d3c34] transition hover:bg-[#f0e4cf]">Call</a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#e8efe8] text-xl font-black text-[#1d3c34]">TB</div>
                    <h3 class="mt-5 text-xl font-bold text-[#1d3c34]">Tadesse Bekele</h3>
                    <p class="mt-2 text-sm text-slate-600">Senior property consultant</p>
                </div>
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-[#f4efe7] p-6 shadow-sm">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#e9d9b7] text-xl font-black text-[#1d3c34]">MA</div>
                    <h3 class="mt-5 text-xl font-bold text-[#1d3c34]">Mekdes Ali</h3>
                    <p class="mt-2 text-sm text-slate-600">Investment advisor</p>
                </div>
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#dfe9e2] text-xl font-black text-[#1d3c34]">YG</div>
                    <h3 class="mt-5 text-xl font-bold text-[#1d3c34]">Yohannes Gebre</h3>
                    <p class="mt-2 text-sm text-slate-600">Sales manager</p>
                </div>
            @endforelse
        </div>
    </section>

    <section id="contact" class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="rounded-[2rem] bg-[#1d3c34] p-8 text-[#f9f3e9] shadow-lg shadow-[#1d3c34]/20 lg:p-10">
            <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#d9cab3]">Contact</p>
            <h2 class="mt-3 text-3xl font-black">Let’s find your next address</h2>
            <div class="mt-6 flex flex-wrap gap-6 text-base text-[#ebefd9]">
                <span>Email: {{ $siteBrand['email'] }}</span>
                <span>Phone: {{ $siteBrand['phone'] }}</span>
                <span>{{ $siteBrand['address'] }}</span>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
                <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6">
                    <h3 class="text-xl font-bold text-white">Speak with an agent</h3>
                    <p class="mt-3 text-sm leading-7 text-[#ebefd9]">
                        Share your property goals and we will contact you with the right options for your budget, lifestyle, and preferred location.
                    </p>
                </div>

                <div class="rounded-[1.5rem] bg-[#f8f3eb] p-6 text-[#1d3c34]">
                    @if (session('success'))
                        <div class="mb-4 rounded-xl border border-[#d9cab3] bg-[#edf2ee] px-4 py-3 text-sm font-semibold text-[#1d3c34]">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" class="space-y-4">
                        @csrf

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="name" class="mb-1 block text-sm font-semibold">Name</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                            <div>
                                <label for="email" class="mb-1 block text-sm font-semibold">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="agent_id" class="mb-1 block text-sm font-semibold">Contact Agent</label>
                                <select id="agent_id" name="agent_id" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                    <option value="">General inquiry</option>
                                    @foreach ($agents ?? collect() as $agent)
                                        <option value="{{ $agent->id }}" {{ old('agent_id', request('agent')) == $agent->id ? 'selected' : '' }}>{{ $agent->name }} — {{ $agent->bio ?: 'Agent' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="phone" class="mb-1 block text-sm font-semibold">Phone</label>
                                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="mb-1 block text-sm font-semibold">Subject</label>
                            <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>

                        <div>
                            <label for="message" class="mb-1 block text-sm font-semibold">Message</label>
                            <textarea id="message" name="message" rows="5" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('message') }}</textarea>
                        </div>

                        <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="rounded-[2rem] bg-[#1d3c34] p-8 text-[#f9f3e9] shadow-lg shadow-[#1d3c34]/20 lg:p-10">
            <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#d9cab3]">Stay informed</p>
                    <h2 class="mt-3 text-3xl font-black">Newsletter</h2>
                </div>
                <form class="flex w-full max-w-xl flex-col gap-3 sm:flex-row">
                    <input type="email" placeholder="Enter your email" class="w-full rounded-full border border-[#d9cab3] bg-white px-4 py-3 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    <button type="button" class="rounded-full bg-[#d9cab3] px-5 py-3 text-sm font-bold text-[#1d3c34]">Subscribe</button>
                </form>
            </div>
        </div>
    </section>
@endsection
