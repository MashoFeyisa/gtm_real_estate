@extends('app')

@section('title', 'Our Agents')
@section('description', 'Meet our property consultants. Contact an agent, call directly, or leave feedback about your experience.')

@section('content')
    <section class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
        <div class="text-center">
            <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Our team</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-[#1d3c34] md:text-4xl">Meet Our Agents</h1>
            <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-slate-600 md:text-base md:leading-8">
                Connect with property consultants who understand your local goals and investment priorities. Call directly, send a message, or leave feedback about your experience.
            </p>
        </div>

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($agents as $agent)
                <article class="overflow-hidden rounded-[1.5rem] border border-[#d9cab3] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <a href="{{ route('agents.show', $agent) }}" class="block">
                        <div class="relative h-48 w-full overflow-hidden bg-[#e8efe8]">
                            <img src="{{ $agent->photo_url }}" alt="{{ $agent->name }}" class="h-full w-full object-cover transition duration-300 hover:scale-105">
                        </div>
                    </a>
                    <div class="p-5">
                        <h2 class="text-lg font-black text-[#1d3c34]">
                            <a href="{{ route('agents.show', $agent) }}" class="transition hover:text-[#2e5a4c]">{{ $agent->name }}</a>
                        </h2>
                        <p class="mt-0.5 text-xs font-semibold text-[#587165]">{{ $agent->bio ?: 'Property consultant' }}</p>

                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-xs font-bold text-[#9b6c17]">
                                @if ($agent->feedback_count > 0)
                                    {{ number_format($agent->average_rating, 1) }} ★
                                @else
                                    New agent
                                @endif
                            </span>
                            <span class="text-[11px] text-slate-500">({{ $agent->feedback_count }} feedback{{ $agent->feedback_count === 1 ? '' : 's' }})</span>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('agents.show', $agent) }}" class="rounded-full bg-[#1d3c34] px-4 py-1.5 text-xs font-bold uppercase tracking-[0.14em] text-white transition hover:bg-[#254d43]">
                                View Profile
                            </a>
                            @if ($agent->phone)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $agent->phone) }}" class="rounded-full border border-[#b9a98b] px-4 py-1.5 text-xs font-bold uppercase tracking-[0.14em] text-[#1d3c34] transition hover:bg-[#f0e4cf]">
                                    Call Now
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-[1.5rem] border border-[#d9cab3] bg-white p-10 text-center shadow-sm">
                    <h2 class="text-xl font-black text-[#1d3c34]">No agents available yet</h2>
                    <p class="mt-2 text-sm text-slate-600">Our team profiles are being prepared. Please check back soon or use the contact form to reach us.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
