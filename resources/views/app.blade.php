<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RealEstate')</title>
    <meta name="description" content="@yield('description', 'Find your dream property with our trusted real estate platform.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-[#f6f1e8] text-slate-800 antialiased">
    @php
        $posts = $posts ?? collect();
        $featuredProperties = $featuredProperties ?? collect();
        $latestProperties = $latestProperties ?? collect();
        $featuredProjects = $featuredProjects ?? collect();
        $homeStats = $homeStats ?? [
            'homes' => 0,
            'villas' => 0,
            'apartments' => 0,
            'sold' => 0,
        ];
        $logo = asset('images/logo1.jpg');
        $getPropertyCategory = fn ($property) => strtolower((string) ($property->property_category ?? $property->category ?? 'home'));
    @endphp

    <header class="sticky top-0 z-50 border-b border-[#d9cab3] bg-[#f8f3eb]/95 backdrop-blur-md shadow-sm">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
            <a href="{{ url('/app') }}" class="flex items-center gap-3">
                <img src="{{ $logo }}" alt="Real Estate Logo" class="h-12 w-12 rounded-2xl object-cover ring-2 ring-[#d9cab3] shadow-md">
                <span class="text-2xl font-black tracking-tight text-[#1c3d32]">
                    Real <span class="text-[#6d7f6a]">Estate</span>
                </span>
            </a>

            <div class="hidden items-center gap-7 md:flex">
                <a href="{{ route('home') }}" class="text-sm font-semibold text-[#1d3c34] transition hover:text-[#2e5a4c]">Home</a>
                <a href="{{ route('properties.index') }}" class="text-sm font-semibold text-[#1d3c34] transition hover:text-[#2e5a4c]">Properties</a>
                <a href="{{ url('/app#news') }}" class="text-sm font-semibold text-[#1d3c34] transition hover:text-[#2e5a4c]">Blog & News</a>
                {{-- <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-[#1d3c34] transition hover:text-[#2e5a4c]">Admin</a> --}}
                <a href="{{ url('/app#contact') }}" class="text-sm font-semibold text-[#1d3c34] transition hover:text-[#2e5a4c]">Contact</a>
            </div>

            <div class="hidden items-center gap-3 md:flex">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-full border border-[#b9a98b] px-4 py-2 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">login</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-full border border-[#b9a98b] px-4 py-2 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">Login</a>
                @endauth
                <a href="{{ route('properties.index') }}" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-sm font-semibold text-[#f8f3eb] shadow-md shadow-[#1d3c34]/20 transition hover:bg-[#264d41]">List Property</a>
            </div>

            <button type="button" class="rounded-xl border border-[#d9cab3] bg-[#fffaf2] p-2 text-[#1d3c34] md:hidden" aria-label="Open menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </nav>
    </header>

    <main>
        <section id="home" class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
            <div class="grid items-center gap-10 lg:grid-cols-[1.2fr_0.8fr]">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-[#d7c8a9] bg-[#f1e4cf] px-3 py-1 text-xs font-bold uppercase tracking-[0.22em] text-[#1d3c34]">
                        Premium living spaces
                    </div>
                    <h1 class="mt-6 text-5xl font-black tracking-tight text-[#1d3c34] md:text-6xl">
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
                    <div class="rounded-[2rem] border border-[#d9cab3] bg-[#fffaf4] p-6 shadow-[0_30px_80px_rgba(29,60,52,0.18)]">
                        <div class="rounded-[1.5rem] bg-gradient-to-br from-[#1d3c34] via-[#2d5145] to-[#5b6d60] p-6 text-[#f9f3e9]">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#e7d8b7]">Featured property</p>
                                <span class="rounded-full bg-[#e5d5af] px-2.5 py-1 text-xs font-bold text-[#1d3c34]">For Sale</span>
                            </div>
                            <h2 class="mt-6 text-3xl font-black">Greenview Residence</h2>
                            <p class="mt-2 text-sm text-[#dfeee4]">4 bed • 3 bath • 2,600 sq ft</p>
                            <div class="mt-8 rounded-2xl bg-[#f6f0e2] p-4 text-[#1d3c34]">
                                <div class="flex items-end justify-between">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.2em] text-[#617466]">Starting at</p>
                                        <p class="mt-2 text-3xl font-black">$480k</p>
                                    </div>
                                    <div class="rounded-full bg-[#d9cab3] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">Prime location</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-3 gap-3">
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
                        <div class="h-52 bg-gradient-to-br from-[#dfe8e3] via-[#edf3ee] to-[#e9d9b7]"></div>
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
                <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-[#1d3c34] hover:text-[#244d43]">View admin</a>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-3">
                @php($publicPosts = $posts->take(3))

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
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">About GTP</p>
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
            <div class="mt-8 grid gap-6 md:grid-cols-3">
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <p class="text-lg leading-8 text-slate-600">“The team helped us find the right home in less than two weeks, and the process felt clear from start to finish.”</p>
                    <div class="mt-5 text-sm font-bold text-[#1d3c34]">Amanuel & Selam</div>
                </div>
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-[#f4efe7] p-6 shadow-sm">
                    <p class="text-lg leading-8 text-slate-600">“Their local knowledge and honest guidance gave us confidence in every investment decision.”</p>
                    <div class="mt-5 text-sm font-bold text-[#1d3c34]">Hana T.</div>
                </div>
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <p class="text-lg leading-8 text-slate-600">“From viewings to paperwork, we felt supported at every step and truly valued as clients.”</p>
                    <div class="mt-5 text-sm font-bold text-[#1d3c34]">Tesfaye G.</div>
                </div>
            </div>
        </section>

        <section id="agents" class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Our agents</p>
                    <h2 class="mt-2 text-3xl font-black text-[#1d3c34]">Real people, local expertise</h2>
                </div>
            </div>
            <div class="mt-8 grid gap-6 md:grid-cols-3">
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
            </div>
        </section>

        <section id="contact" class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
            <div class="rounded-[2rem] bg-[#1d3c34] p-8 text-[#f9f3e9] shadow-lg shadow-[#1d3c34]/20 lg:p-10">
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#d9cab3]">Contact</p>
                <h2 class="mt-3 text-3xl font-black">Let’s find your next address</h2>
                <div class="mt-6 flex flex-wrap gap-6 text-base text-[#ebefd9]">
                    <span>Email: gtmrealstate@gmail.com</span>
                    <span>Phone: +251 993722346</span>
                    <span>Harar, Ethiopia</span>
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
                                    <label for="phone" class="mb-1 block text-sm font-semibold">Phone</label>
                                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>
                                <div>
                                    <label for="subject" class="mb-1 block text-sm font-semibold">Subject</label>
                                    <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                </div>
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

        @yield('content')
    </main>

    <footer class="mt-8 bg-[#102b25] text-[#e6ede6]">
        <div class="mx-auto grid max-w-7xl gap-10 px-6 py-14 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
            <div>
                <a href="{{ url('/') }}" class="text-2xl font-black text-white">
                    Real<span class="text-[#d9cab3]">Estate</span>
                </a>
                <p class="mt-4 max-w-sm text-sm leading-6 text-[#d3ded5]">
                    Find the perfect place to live, invest, and build your future. Discover properties from trusted agents and owners.
                </p>
            </div>

            <div>
                <h3 class="text-sm font-bold uppercase tracking-[0.22em] text-[#d9cab3]">Company</h3>
                <ul class="mt-4 space-y-3 text-sm text-[#d8ded6]">
                    <li><a href="{{ url('/app#about') }}" class="transition hover:text-white">About Us</a></li>
                    <li><a href="{{ url('/app#agents') }}" class="transition hover:text-white">Our Agents</a></li>
                    <li><a href="{{ route('properties.index') }}" class="transition hover:text-white">Properties</a></li>
                    <li><a href="{{ url('/app#contact') }}" class="transition hover:text-white">Contact</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-bold uppercase tracking-[0.22em] text-[#d9cab3]">Properties</h3>
                <ul class="mt-4 space-y-3 text-sm text-[#d8ded6]">
                    <li><a href="{{ route('properties.index') }}" class="transition hover:text-white">Houses</a></li>
                    <li><a href="{{ route('properties.index') }}" class="transition hover:text-white">Apartments</a></li>
                    <li><a href="{{ route('properties.index') }}" class="transition hover:text-white">Villas</a></li>
                    <li><a href="{{ route('properties.index') }}" class="transition hover:text-white">Land</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-bold uppercase tracking-[0.22em] text-[#d9cab3]">Contact us</h3>
                <ul class="mt-4 space-y-3 text-sm text-[#d8ded6]">
                    <li>Harar, Ethiopia</li>
                    <li>+251 993722346</li>
                    <li>gtmrealstate@gmail.com</li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 py-6 text-sm text-[#b8c7bd] md:flex-row lg:px-8">
                <p>© {{ date('Y') }} RealEstate. All rights reserved.</p>
                <div class="flex gap-6">
                    <a href="#" class="transition hover:text-white">Privacy Policy</a>
                    <a href="#" class="transition hover:text-white">Terms</a>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

