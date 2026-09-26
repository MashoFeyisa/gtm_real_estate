<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f6f1e8] text-slate-800 antialiased">
    <main class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Listings</p>
                <h1 class="mt-2 text-4xl font-black tracking-tight text-[#1d3c34]">Properties</h1>
            </div>
            <a href="{{ route('home') }}" class="inline-flex items-center rounded-full border border-[#d9cab3] bg-[#fffaf2] px-4 py-2 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f1e6d2]">Back home</a>
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('properties.index') }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ ($selectedCategory ?? 'all') === 'all' ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-[#fffaf2] text-[#1d3c34]' }}">
                All
            </a>
            <a href="{{ route('properties.villas') }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ ($selectedCategory ?? 'all') === 'villa' ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-[#fffaf2] text-[#1d3c34]' }}">
                Villas
            </a>
            <a href="{{ route('properties.homes') }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ ($selectedCategory ?? 'all') === 'home' ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-[#fffaf2] text-[#1d3c34]' }}">
                Homes
            </a>
            <a href="{{ route('properties.apartments') }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ ($selectedCategory ?? 'all') === 'apartment' ? 'bg-[#1d3c34] text-white' : 'border border-[#d9cab3] bg-[#fffaf2] text-[#1d3c34]' }}">
                Apartments
            </a>
        </div>

        @if ($properties->isEmpty())
            <div class="mt-8 rounded-[2rem] border border-dashed border-[#d9cab3] bg-[#fffaf2] p-10 text-center text-slate-600">
                No properties available yet.
            </div>
        @else
            <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($properties as $property)
                    <article class="overflow-hidden rounded-[1.75rem] border border-[#d9cab3] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        @if ($property->image_path)
                            <img src="{{ asset('storage/' . $property->image_path) }}" alt="{{ $property->title }}" class="h-56 w-full object-cover">
                        @else
                            <div class="flex h-56 items-center justify-center bg-gradient-to-br from-[#dfe8e3] via-[#edf3ee] to-[#e9d9b7] text-xl font-black text-[#1d3c34]">
                                {{ Str::limit($property->title, 18) }}
                            </div>
                        @endif

                        <div class="p-6">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">{{ ucfirst($property->property_category ?? $property->category ?? 'property') }}</p>
                                @if ($property->featured)
                                    <span class="rounded-full bg-[#f1e4cf] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#1d3c34]">Featured</span>
                                @endif
                            </div>
                            <h2 class="mt-4 text-2xl font-black text-[#1d3c34]">{{ $property->title }}</h2>
                            <p class="mt-2 text-sm text-slate-600">{{ $property->city ?? 'Location available' }} · {{ $property->address ?? 'Prime location' }}</p>
                            <p class="mt-4 text-3xl font-black text-[#1d3c34]">${{ number_format($property->price, 2) }}</p>
                            <div class="mt-4 flex items-center gap-4 text-sm text-slate-600">
                                <span>{{ $property->bedrooms }} beds</span>
                                <span>{{ $property->bathrooms }} baths</span>
                                <span>{{ $property->area }} sq ft</span>
                            </div>
                            <a href="{{ route('properties.show', $property->slug) }}" class="mt-6 inline-flex rounded-full bg-[#1d3c34] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#264d41]">
                                View Details
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </main>
</body>
</html>
