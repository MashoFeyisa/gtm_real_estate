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
                <div class="mt-10 space-y-4">
                    @foreach ($jobs as $job)
                        <article class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <span class="rounded-full bg-[#edf2ee] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#1d3c34]">{{ $job->category ?: 'Organization' }}</span>
                                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-[#587165]">{{ $job->status }}</span>
                            </div>
                            <h2 class="mt-4 text-2xl font-black text-[#1d3c34]">{{ $job->title }}</h2>
                            <p class="mt-3 text-sm leading-7 text-slate-600">{{ Str::limit(strip_tags($job->content), 180) }}</p>
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
