@extends('app')

@section('title', $title ?? 'Real Estate')
@section('description', $intro ?? 'Explore our real estate services and opportunities.')

@section('content')
    <section class="mx-auto max-w-5xl px-6 py-16 lg:px-8">
        <div class="rounded-[2rem] border border-[#d9cab3] bg-[#fffaf4] p-8 shadow-sm md:p-10">
            <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Public Page</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight text-[#1d3c34] md:text-5xl">{{ $title }}</h1>
            <p class="mt-4 max-w-2xl text-base leading-8 text-slate-600 md:text-lg">{{ $intro }}</p>

            @if (in_array(($page ?? null), ['careers', 'job-details', 'jobs', 'job']) && isset($jobs) && $jobs->isNotEmpty())
                <div class="mt-10 space-y-6">
                    @foreach ($jobs as $job)
                        @php
                            $applyUrl = $job->apply_link ?: 'mailto:'.config('mail.from.address');
                            $requirementLines = collect(preg_split('/\r\n|\r|\n/', (string) $job->requirements))
                                ->map(fn ($line) => trim($line))
                                ->filter();
                        @endphp
                        <article class="rounded-[1.5rem] border border-[#d9cab3] bg-white overflow-hidden shadow-sm transition-all duration-300 hover:shadow-lg">
                            @if ($job->image_url)
                                <div class="relative h-52 sm:h-64 md:h-72 w-full overflow-hidden bg-[#0c1f1a]">
                                    <img src="{{ $job->image_url }}" alt="{{ $job->title }}" class="h-full w-full object-cover transition-transform duration-700 hover:scale-105">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent pointer-events-none"></div>
                                    <div class="absolute top-4 left-4 flex flex-wrap items-center gap-2 z-10">
                                        <span class="rounded-full bg-[#1d3c34]/90 backdrop-blur-md px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-[#dfeee4] border border-white/20">Now Hiring</span>
                                        <span class="rounded-full bg-white/90 backdrop-blur-md px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-[#1d3c34]">{{ $job->category ?: 'Organization' }}</span>
                                    </div>
                                    @if ($job->job_type)
                                        <div class="absolute top-4 right-4 z-10">
                                            <span class="rounded-full bg-[#f9ecd0]/90 backdrop-blur-md px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-[#9b6c17]">{{ $job->job_type }}</span>
                                        </div>
                                    @endif
                                    <div class="absolute bottom-4 left-4 right-4 z-10">
                                        <h2 class="text-2xl sm:text-3xl font-black text-white drop-shadow-md">{{ $job->title }}</h2>
                                    </div>
                                </div>
                            @endif

                            <div class="p-5 md:p-6">
                                @if (! $job->image_url)
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full bg-[#1d3c34] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#dfeee4]">Now Hiring</span>
                                            <span class="rounded-full bg-[#edf2ee] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#1d3c34]">{{ $job->category ?: 'Organization' }}</span>
                                        </div>
                                        @if ($job->job_type)
                                            <span class="rounded-full bg-[#f9ecd0] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#9b6c17]">{{ $job->job_type }}</span>
                                        @endif
                                    </div>
                                    <h2 class="mt-4 text-2xl font-black text-[#1d3c34]">{{ $job->title }}</h2>
                                @endif

                                <p class="mt-3 text-base leading-7 text-slate-700">{{ Str::limit(strip_tags($job->content), 400) }}</p>

                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-[#f0e8dc] pt-3 text-xs">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="font-bold text-[#1d3c34]">By {{ $job->author_name ?: ($job->user?->name ?: 'Admin User') }}</span>
                                        <span class="text-slate-300">•</span>
                                        <span class="font-medium text-slate-500">{{ $job->published_at ? $job->published_at->format('M d, Y') : ($job->created_at ? $job->created_at->format('M d, Y') : 'Sep 29, 2026') }}</span>
                                    </div>

                                    <div class="flex flex-wrap gap-x-5 gap-y-1 font-semibold uppercase tracking-[0.14em] text-[#587165]">
                                        @if ($job->job_location)
                                            <span>{{ $job->job_location }}</span>
                                        @endif
                                        @if ($job->salary_range)
                                            <span>{{ $job->salary_range }}</span>
                                        @endif
                                        @if ($job->experience_level)
                                            <span>{{ $job->experience_level }} experience</span>
                                        @endif
                                        @if ($job->application_deadline)
                                            <span>Apply by {{ $job->application_deadline->format('M d, Y') }}</span>
                                        @endif
                                    </div>
                                </div>

                                @if ($requirementLines->isNotEmpty())
                                    <div class="mt-4 rounded-2xl bg-[#f9f4ed] p-4">
                                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">Requirements</p>
                                        <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-600">
                                            @foreach ($requirementLines as $requirement)
                                                <li class="flex gap-2">
                                                    <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-[#2d5d4d]"></span>
                                                    <span>{{ $requirement }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                        @if ($job->education_level)
                                            <p class="mt-3 text-sm font-semibold text-[#1d3c34]">Education: {{ $job->education_level }}</p>
                                        @endif
                                    </div>
                                @elseif ($job->education_level)
                                    <p class="mt-3 text-sm font-semibold text-[#1d3c34]">Education: {{ $job->education_level }}</p>
                                @endif

                                <div class="mt-5 rounded-2xl border border-[#d9cab3] bg-[#f9f4ed] p-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0 flex-1">
                                            <span class="text-xs font-bold uppercase tracking-[0.18em] text-[#587165]">Apply Link</span>
                                            <div class="mt-1">
                                                @if ($job->apply_link)
                                                    <a href="{{ $job->apply_link }}" target="_blank" rel="noopener" class="text-sm font-semibold text-[#1d3c34] underline hover:text-[#2d5d4d] break-all block">
                                                        {{ $job->apply_link }}
                                                    </a>
                                                @else
                                                    <a href="{{ $applyUrl }}" class="text-sm font-semibold text-[#1d3c34] underline hover:text-[#2d5d4d]">
                                                        {{ config('mail.from.address') }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ $applyUrl }}" target="_blank" rel="noopener" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-full bg-[#1d3c34] px-6 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]">
                                            Apply Now
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4-4m0 0h-6m6 0v6M14 3v4a1 1 0 01-1 1H9m-6 8v3a2 2 0 002 2h3" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @elseif (in_array(($page ?? null), ['careers', 'job-details', 'jobs', 'job']))
                <div class="mt-10 rounded-[1.5rem] border border-[#d9cab3] bg-white p-8 text-center text-slate-500">
                    <p class="text-base font-semibold text-[#1d3c34]">No open positions currently available.</p>
                    <p class="mt-2 text-sm text-slate-500">Please check back soon for future job openings.</p>
                </div>
            @endif

            @if (($page ?? null) === 'projects')
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    <div class="rounded-[1.75rem] border border-[#d9cab3] bg-gradient-to-br from-[#1d3c34] to-[#2f5248] p-6 text-[#f8f3eb] shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#d9cab3]">{{ \App\Models\SiteSetting::get('project_1_tag', 'Luxury') }}</p>
                        <h3 class="mt-4 text-xl font-bold">{{ \App\Models\SiteSetting::get('project_1_title', 'Emerald Heights') }}</h3>
                        <p class="mt-3 text-sm leading-7 text-[#eaf3ec]">{{ \App\Models\SiteSetting::get('project_1_description', 'Residential living designed around comfort, green views, and daily convenience.') }}</p>
                    </div>
                    <div class="rounded-[1.75rem] border border-[#d9cab3] bg-[#f4efe7] p-6 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">{{ \App\Models\SiteSetting::get('project_2_tag', 'Commercial') }}</p>
                        <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">{{ \App\Models\SiteSetting::get('project_2_title', 'Harar Square') }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ \App\Models\SiteSetting::get('project_2_description', 'Mixed-use development and retail spaces scheduled for the next growth corridor.') }}</p>
                    </div>
                    <div class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">{{ \App\Models\SiteSetting::get('project_3_tag', 'Future plan') }}</p>
                        <h3 class="mt-4 text-xl font-bold text-[#1d3c34]">{{ \App\Models\SiteSetting::get('project_3_title', 'Oakland Park') }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ \App\Models\SiteSetting::get('project_3_description', 'A connected master-planned community focused on smart, sustainable growth.') }}</p>
                    </div>
                </div>
            @endif

            <div class="mt-8 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl bg-[#edf2ee] p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">Property Search</p>
                    <p class="mt-3 text-2xl font-black text-[#1d3c34]">Quick</p>
                </div>
                <div class="rounded-2xl bg-[#f4efe7] p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">Featured</p>
                    <p class="mt-3 text-2xl font-black text-[#1d3c34]">Locations</p>
                </div>
                <div class="rounded-2xl bg-[#e9efe9] p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">Support</p>
                    <p class="mt-3 text-2xl font-black text-[#1d3c34]">Experts</p>
                </div>
            </div>
        </div>
    </section>
@endsection
