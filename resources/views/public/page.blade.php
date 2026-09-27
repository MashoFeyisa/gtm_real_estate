@extends('app')

@section('title', $title ?? 'Real Estate')
@section('description', $intro ?? 'Explore our real estate services and opportunities.')

@section('content')
    <section class="mx-auto max-w-5xl px-6 py-16 lg:px-8">
        <div class="rounded-[2rem] border border-[#d9cab3] bg-[#fffaf4] p-8 shadow-sm md:p-10">
            <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Public Page</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight text-[#1d3c34] md:text-5xl">{{ $title }}</h1>
            <p class="mt-4 max-w-2xl text-base leading-8 text-slate-600 md:text-lg">{{ $intro }}</p>

            @if (($page ?? null) === 'careers' && isset($jobs) && $jobs->isNotEmpty())
                <div class="mt-10 space-y-6">
                    @foreach ($jobs as $job)
                        @php
                            $applyUrl = $job->apply_link ?: 'mailto:'.config('mail.from.address');
                            $requirementLines = collect(preg_split('/\r\n|\r|\n/', (string) $job->requirements))
                                ->map(fn ($line) => trim($line))
                                ->filter();
                        @endphp
                        <article class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm md:p-6">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <span class="rounded-full bg-[#edf2ee] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#1d3c34]">{{ $job->category ?: 'Organization' }}</span>
                                @if ($job->job_type)
                                    <span class="rounded-full bg-[#f9ecd0] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#9b6c17]">{{ $job->job_type }}</span>
                                @endif
                            </div>
                            <h2 class="mt-4 text-2xl font-black text-[#1d3c34]">{{ $job->title }}</h2>

                            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-xs font-semibold uppercase tracking-[0.14em] text-[#587165]">
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

                            <p class="mt-3 text-sm leading-7 text-slate-600">{{ Str::limit(strip_tags($job->content), 300) }}</p>

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

                            <div class="mt-5">
                                <a href="{{ $applyUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full bg-[#1d3c34] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]">
                                    Apply Now
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4-4m0 0h-6m6 0v6M14 3v4a1 1 0 01-1 1H9m-6 8v3a2 2 0 002 2h3" />
                                    </svg>
                                </a>
                            </div>
                        </article>
                    @endforeach
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
