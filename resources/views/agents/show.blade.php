@extends('app')

@section('title', $agent->name.' — Agent Profile')
@section('description', Str::limit(strip_tags($agent->bio ?: 'Contact '.$agent->name.', property consultant.'), 160))

@section('content')
    <section class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
        <a href="{{ route('agents') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#587165] transition hover:text-[#1d3c34]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            All agents
        </a>

        <div class="mt-4 overflow-hidden rounded-[1.75rem] border border-[#d9cab3] bg-[#fffaf4] shadow-sm">
            <div class="grid gap-5 p-5 md:p-6 lg:grid-cols-[220px_1fr]">
                <div>
                    <div class="overflow-hidden rounded-[1.25rem] border border-[#d9cab3] bg-[#e8efe8] shadow-md">
                        <img src="{{ $agent->photo_url }}" alt="{{ $agent->name }}" class="h-56 w-full object-cover">
                    </div>

                    @if ($agent->feedback_count > 0)
                        <div class="mt-4 rounded-2xl bg-[#f9f4ed] p-3 text-center">
                            <p class="text-2xl font-black text-[#1d3c34]">{{ number_format($agent->average_rating, 1) }}<span class="text-base text-[#9b6c17]"> ★</span></p>
                            <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#587165]">
                                {{ $agent->feedback_count }} client feedback{{ $agent->feedback_count === 1 ? '' : 's' }}
                            </p>
                        </div>
                    @endif
                </div>

                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Agent Profile</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-[#1d3c34]">{{ $agent->name }}</h1>
                    <p class="mt-1 text-base font-semibold text-[#587165]">{{ $agent->bio ?: 'Property consultant' }}</p>

                    <div class="mt-4 space-y-2">
                        <div class="flex items-center gap-3 text-sm text-slate-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#2d5d4d]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <a href="mailto:{{ $agent->email }}" class="font-semibold transition hover:text-[#1d3c34]">{{ $agent->email }}</a>
                        </div>
                        @if ($agent->phone)
                            <div class="flex items-center gap-3 text-sm text-slate-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#2d5d4d]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $agent->phone) }}" class="font-semibold transition hover:text-[#1d3c34]">{{ $agent->phone }}</a>
                            </div>
                        @endif
                    </div>

                    @php
                        $agentRawPhone = $agent->phone ?? '';
                        $agentCleanPhone = preg_replace('/[^0-9]/', '', $agentRawPhone);
                        if (str_starts_with($agentCleanPhone, '0')) {
                            $agentCleanPhone = '251' . substr($agentCleanPhone, 1);
                        }
                        $agentTelPhone = preg_replace('/[^0-9+]/', '', $agentRawPhone);
                    @endphp

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="#contact-agent" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-[#1d3c34]/20 transition hover:bg-[#254d43]">
                            Contact {{ $agent->name }}
                        </a>
                        @if ($agentTelPhone)
                            <a href="tel:{{ $agentTelPhone }}" class="inline-flex items-center gap-2 rounded-full bg-[#00b53f] px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-[#00b53f]/20 transition hover:bg-[#009b36]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                Call Agent
                            </a>
                        @endif
                        @if ($agentCleanPhone)
                            <a href="https://wa.me/{{ $agentCleanPhone }}?text={{ urlencode('Hello ' . $agent->name . ', I found your profile on GTM Real Estate and would like to inquire about your property listings.') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full bg-[#25D366] px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-[#25D366]/20 transition hover:bg-[#20ba59]">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.588-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.072-1.047-.042-.313-.102-.7-.234-1.206-.453-2.138-.925-3.528-3.08-3.635-3.223-.106-.143-.87-1.157-.87-2.207 0-1.05.549-1.567.744-1.78.196-.214.428-.268.572-.268.143 0 .287.002.412.008.132.006.309-.05.483.369.179.431.613 1.493.666 1.602.053.109.089.237.017.38-.071.144-.107.233-.214.358-.106.126-.224.281-.32.376-.107.106-.219.222-.094.436.125.214.557.918 1.196 1.488.823.733 1.517.96 1.731 1.067.214.107.339.089.464-.054.126-.143.536-.624.679-.838.143-.214.286-.179.482-.107.197.072 1.25.59 1.464.697.214.107.357.161.41.25.054.089.054.517-.09 1.031z"/></svg>
                                WhatsApp Chat
                            </a>
                        @endif
                        <a href="#feedback" class="rounded-full border border-[#b9a98b] px-5 py-2.5 text-sm font-bold text-[#1d3c34] transition hover:bg-[#f0e4cf]">
                            Give Feedback
                        </a>
                    </div>

                    <div class="mt-6 grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-[#edf2ee] p-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#587165]">Listings</p>
                            <p class="mt-1 text-xl font-black text-[#1d3c34]">{{ $agent->properties_count }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#f4efe7] p-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#587165]">Feedback</p>
                            <p class="mt-1 text-xl font-black text-[#1d3c34]">{{ $agent->feedback_count }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#e9efe9] p-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#587165]">Rating</p>
                            <p class="mt-1 text-xl font-black text-[#1d3c34]">
                                {{ $agent->feedback_count > 0 ? number_format($agent->average_rating, 1).' ★' : '—' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Agent Storefront Listings --}}
        <div class="mt-12">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">Storefront</p>
                    <h2 class="text-2xl font-black text-[#1d3c34]">Properties Listed by {{ $agent->name }}</h2>
                </div>

                {{-- Filter Tabs like Jiji --}}
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('agents.show', $agent) }}" class="rounded-full px-4 py-1.5 text-xs font-bold transition {{ ($selectedType ?? 'all') === 'all' ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-white text-[#1d3c34] hover:bg-[#f8f3eb]' }}">
                        All Listings ({{ $agent->properties_count }})
                    </a>
                    <a href="{{ route('agents.show', [$agent, 'type' => 'sale']) }}" class="rounded-full px-4 py-1.5 text-xs font-bold transition {{ ($selectedType ?? 'all') === 'sale' ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-white text-[#1d3c34] hover:bg-[#f8f3eb]' }}">
                        For Sale ({{ $saleCount ?? 0 }})
                    </a>
                    <a href="{{ route('agents.show', [$agent, 'type' => 'rent']) }}" class="rounded-full px-4 py-1.5 text-xs font-bold transition {{ ($selectedType ?? 'all') === 'rent' ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-white text-[#1d3c34] hover:bg-[#f8f3eb]' }}">
                        For Rent ({{ $rentCount ?? 0 }})
                    </a>
                </div>
            </div>

            @if ($properties->isNotEmpty())
                <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($properties as $property)
                        @php
                            $cardGallery = $property->gallery_images;
                        @endphp
                        <article class="group overflow-hidden rounded-[1.75rem] border border-[#d9cab3] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                            <div class="relative">
                                @if (count($cardGallery) > 1)
                                    <div data-property-slider class="group/slider relative h-52 w-full overflow-hidden bg-[#e8efe8] shrink-0 select-none">
                                        <a href="{{ route('properties.show', $property->slug) }}" class="absolute inset-0 z-10" aria-label="{{ $property->title }}"></a>
                                        @foreach ($cardGallery as $idx => $imgUrl)
                                            <div data-property-slide class="absolute inset-0 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] {{ $idx === 0 ? 'opacity-100 scale-100 z-10' : 'opacity-0 scale-105 pointer-events-none z-0' }}">
                                                <img src="{{ $imgUrl }}" alt="{{ $property->title }} - Photo {{ $idx + 1 }}" class="h-full w-full object-cover">
                                            </div>
                                        @endforeach

                                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent pointer-events-none"></div>

                                        <div class="absolute inset-x-2 top-1/2 -translate-y-1/2 flex items-center justify-between pointer-events-none z-20">
                                            <button type="button" data-slider-prev aria-label="Previous image" class="pointer-events-auto flex h-7 w-7 items-center justify-center rounded-full bg-black/65 text-[#e7d8b7] backdrop-blur-md opacity-0 group-hover/slider:opacity-100 transition-all duration-200 hover:bg-[#102b25] hover:text-white hover:border-[#d4af37] border border-white/20 active:scale-90 cursor-pointer">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                            </button>
                                            <button type="button" data-slider-next aria-label="Next image" class="pointer-events-auto flex h-7 w-7 items-center justify-center rounded-full bg-black/65 text-[#e7d8b7] backdrop-blur-md opacity-0 group-hover/slider:opacity-100 transition-all duration-200 hover:bg-[#102b25] hover:text-white hover:border-[#d4af37] border border-white/20 active:scale-90 cursor-pointer">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                            </button>
                                        </div>

                                        <div class="absolute bottom-2.5 right-2.5 z-20">
                                            <span data-slider-counter class="rounded-full bg-black/75 px-2 py-0.5 text-[10px] font-bold text-[#e7d8b7] backdrop-blur-xs border border-white/20">
                                                1 / {{ count($cardGallery) }}
                                            </span>
                                        </div>
                                    </div>
                                @elseif (count($cardGallery) === 1)
                                    <a href="{{ route('properties.show', $property->slug) }}" class="block relative">
                                        <img src="{{ $cardGallery[0] }}" alt="{{ $property->title }}" class="h-52 w-full object-cover transition duration-300 group-hover:scale-105">
                                    </a>
                                @else
                                    <a href="{{ route('properties.show', $property->slug) }}" class="block relative">
                                        <div class="flex h-52 items-center justify-center bg-gradient-to-br from-[#dfe8e3] via-[#edf3ee] to-[#e9d9b7] text-lg font-black text-[#1d3c34]">
                                            {{ Str::limit($property->title, 18) }}
                                        </div>
                                    </a>
                                @endif
                                <div class="absolute left-3 top-3 flex gap-2 z-20 pointer-events-none">
                                    <span class="rounded-full bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-[0.16em] text-white">
                                        {{ $property->type === 'rent' ? 'Rent' : 'Sale' }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-5">
                                <div class="flex items-center justify-between text-xs text-[#587165]">
                                    <span class="font-bold uppercase tracking-wider">{{ ucfirst($property->property_category ?? 'Property') }}</span>
                                    <span>{{ $property->city ?? 'Addis Ababa' }}</span>
                                </div>

                                <h3 class="mt-2 text-lg font-bold text-[#1d3c34] group-hover:text-[#2d5d4d]">
                                    <a href="{{ route('properties.show', $property->slug) }}">{{ $property->title }}</a>
                                </h3>

                                <div class="mt-3 flex items-baseline gap-2">
                                    <span class="text-xl font-black text-[#1d3c34]">ETB {{ number_format($property->price) }}</span>
                                    @if ($property->type === 'rent')
                                        <span class="text-xs font-semibold text-[#587165]">/ mo</span>
                                    @endif
                                </div>

                                <div class="mt-3 flex items-center gap-3 text-xs text-slate-600 border-t border-[#f0e4cf] pt-3">
                                    <span>{{ $property->bedrooms }} beds</span>
                                    <span>•</span>
                                    <span>{{ $property->bathrooms }} baths</span>
                                    <span>•</span>
                                    <span>{{ $property->area }} sqm (ካሬ)</span>
                                </div>

                                <div class="mt-4 flex items-center justify-between">
                                    <a href="{{ route('properties.show', $property->slug) }}" class="inline-flex rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                        View Listing
                                    </a>
                                    @if ($agentTelPhone)
                                        <a href="tel:{{ $agentTelPhone }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#00b53f] hover:underline">
                                            <span>Call</span> &rarr;
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($properties->hasPages())
                    <div class="mt-8">
                        {{ $properties->links() }}
                    </div>
                @endif
            @else
                <div class="mt-6 rounded-[2rem] border border-dashed border-[#d9cab3] bg-white p-10 text-center text-slate-600">
                    No active {{ $selectedType === 'all' ? '' : $selectedType }} properties currently found for {{ $agent->name }}.
                </div>
            @endif
        </div>

        <div id="contact-agent" class="mt-10 scroll-mt-24">
            <div class="rounded-[2rem] bg-[#1d3c34] p-8 text-[#f9f3e9] shadow-lg shadow-[#1d3c34]/20 lg:p-10">
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#d9cab3]">Contact</p>
                <h2 class="mt-3 text-3xl font-black">Message {{ $agent->name }}</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-[#ebefd9]">
                    Share what you are looking for and {{ $agent->name }} will get back to you with the right options for your budget and preferred location.
                </p>

                <div class="mt-8 rounded-[1.5rem] bg-[#f8f3eb] p-6 text-[#1d3c34] md:p-8">
                    @if (session('success'))
                        <div class="mb-4 rounded-xl border border-[#d9cab3] bg-[#edf2ee] px-4 py-3 text-sm font-semibold text-[#1d3c34]">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-4 rounded-xl border border-[#c86b5c] bg-[#fef3f1] px-4 py-3 text-sm font-semibold text-[#a24339]">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="agent_id" value="{{ $agent->id }}">

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
                                <input id="subject" name="subject" type="text" value="{{ old('subject', 'Inquiry for '.$agent->name) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
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

        <div id="feedback" class="mt-10 grid gap-8 scroll-mt-24 lg:grid-cols-[1.1fr_0.9fr]">
            <div>
                <h2 class="text-xl font-black text-[#1d3c34]">Client Feedback</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($feedbacks as $feedback)
                        <article class="rounded-[1.25rem] border border-[#d9cab3] bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-bold text-[#1d3c34]">{{ $feedback->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $feedback->created_at->format('M d, Y') }}</p>
                                </div>
                                <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-bold text-[#9b6c17]">{{ str_repeat('★', $feedback->rating) }}{{ str_repeat('☆', 5 - $feedback->rating) }}</span>
                            </div>
                            <p class="mt-4 text-sm leading-7 text-slate-600">{{ $feedback->message }}</p>
                        </article>
                    @empty
                        <div class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-6 text-center shadow-sm">
                            <p class="text-sm text-slate-600">No feedback yet. Be the first to share your experience with {{ $agent->name }}.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#f9f4ed] p-6 md:p-8">
                <h3 class="text-xl font-black text-[#1d3c34]">Give Feedback</h3>
                <p class="mt-2 text-sm leading-7 text-slate-600">Worked with {{ $agent->name }}? Rate your experience and help other clients.</p>

                @if (session('feedback_success'))
                    <div class="mt-4 rounded-xl border border-[#d9cab3] bg-[#edf2ee] px-4 py-3 text-sm font-semibold text-[#1d3c34]">
                        {{ session('feedback_success') }}
                    </div>
                @endif
                @if ($errors->feedback->any())
                    <div class="mt-4 rounded-xl border border-[#c86b5c] bg-[#fef3f1] px-4 py-3 text-sm font-semibold text-[#a24339]">
                        @foreach ($errors->feedback->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('agents.feedback', $agent) }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label for="feedback-name" class="mb-1 block text-sm font-semibold text-slate-700">Your Name</label>
                        <input id="feedback-name" name="name" type="text" value="{{ old('name') }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label for="feedback-email" class="mb-1 block text-sm font-semibold text-slate-700">Your Email</label>
                        <input id="feedback-email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label for="feedback-rating" class="mb-1 block text-sm font-semibold text-slate-700">Rating</label>
                        <select id="feedback-rating" name="rating" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="5" {{ old('rating', '5') == '5' ? 'selected' : '' }}>★★★★★ — Excellent</option>
                            <option value="4" {{ old('rating') == '4' ? 'selected' : '' }}>★★★★ — Very good</option>
                            <option value="3" {{ old('rating') == '3' ? 'selected' : '' }}>★★★ — Good</option>
                            <option value="2" {{ old('rating') == '2' ? 'selected' : '' }}>★★ — Fair</option>
                            <option value="1" {{ old('rating') == '1' ? 'selected' : '' }}>★ — Poor</option>
                        </select>
                    </div>

                    <div>
                        <label for="feedback-message" class="mb-1 block text-sm font-semibold text-slate-700">Your Experience</label>
                        <textarea id="feedback-message" name="message" rows="4" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                        Submit Feedback
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
