@extends('app')

@section('title', 'Local Experiences & Cultural Tours | ' . ($siteBrand['name'] ?? 'GTM Real Estate'))
@section('description', 'Discover verified local cultural tours, coffee ceremonies, culinary experiences, and excursions across Ethiopia.')

@section('content')
<div class="min-h-screen bg-[#f6f1e8] py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-[#d9cab3] pb-8">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#102b25]/10 px-3 py-1 text-xs font-black uppercase tracking-wider text-[#102b25]">
                    ☕ Authentic Ethiopian Adventures
                </span>
                <h1 class="mt-3 text-3xl font-black text-[#102b25] sm:text-4xl">
                    Experiences &amp; Tours
                </h1>
                <p class="mt-2 text-sm text-[#587165] max-w-2xl">
                    Immerse yourself in authentic coffee roasting, historic walking tours, nature hikes, and culinary journeys hosted by verified local guides.
                </p>
            </div>

            {{-- Filter Chips / Search --}}
            <form method="GET" action="{{ route('experiences.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Search tours, coffee, hiking..."
                       class="rounded-xl border border-[#d9cab3] bg-white px-4 py-2 text-xs font-bold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                <select name="city" class="rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                    <option value="">All Destinations</option>
                    <option value="Addis Ababa" {{ $selectedCity === 'Addis Ababa' ? 'selected' : '' }}>Addis Ababa</option>
                    <option value="Harar" {{ $selectedCity === 'Harar' ? 'selected' : '' }}>Harar</option>
                    <option value="Hawassa" {{ $selectedCity === 'Hawassa' ? 'selected' : '' }}>Hawassa</option>
                </select>
                <button type="submit" class="rounded-xl bg-[#102b25] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#1a3d34]">
                    Filter
                </button>
            </form>
        </div>

        {{-- Grid --}}
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($experiences as $experience)
                <div class="group flex flex-col overflow-hidden rounded-3xl border border-[#d9cab3] bg-white shadow-sm transition hover:shadow-xl hover:border-[#102b25]">
                    <div class="relative h-52 w-full overflow-hidden bg-slate-900">
                        <img src="{{ $experience->cover_image ?: asset('images/luxury/hero-skyline.jpg') }}"
                             alt="{{ $experience->title }}"
                             class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        <span class="absolute top-3 left-3 rounded-full bg-[#102b25]/90 px-3 py-1 text-[11px] font-black uppercase tracking-wider text-white backdrop-blur-xs">
                            {{ ucfirst($experience->category) }}
                        </span>
                        <span class="absolute bottom-3 right-3 rounded-xl bg-black/80 px-3 py-1 text-xs font-black text-[#d4af37] backdrop-blur-xs">
                            ETB {{ number_format($experience->price_etb) }} / person
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col justify-between p-6">
                        <div>
                            <div class="flex items-center gap-2 text-xs text-[#587165]">
                                <span>📍 {{ $experience->city }}</span>
                                <span>·</span>
                                <span>⏱️ {{ $experience->duration_hours }} hours</span>
                            </div>
                            <h3 class="mt-2 text-lg font-black text-[#102b25] group-hover:text-[#d4af37] transition">
                                <a href="{{ route('experiences.show', $experience) }}">{{ $experience->title }}</a>
                            </h3>
                            <p class="mt-2 text-xs text-slate-600 line-clamp-2">
                                {{ $experience->description }}
                            </p>
                        </div>

                        <div class="mt-6 flex items-center justify-between border-t border-[#f0e4cf] pt-4">
                            <span class="text-xs font-bold text-slate-500">
                                Max {{ $experience->max_participants }} guests
                            </span>
                            <a href="{{ route('experiences.show', $experience) }}"
                               class="inline-flex items-center gap-1 rounded-xl bg-[#102b25] px-4 py-2 text-xs font-black text-white transition hover:bg-[#d4af37] hover:text-[#102b25]">
                                Book Now &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-[#d9cab3] bg-white/60 p-12 text-center">
                    <p class="text-sm font-bold text-slate-600">No experiences matching the selected criteria.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $experiences->links() }}
        </div>
    </div>
</div>
@endsection
