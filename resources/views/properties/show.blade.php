<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $property->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f6f1e8] text-slate-800 antialiased">
    <main class="mx-auto max-w-6xl px-6 py-12 lg:px-8">
        <a href="{{ route('properties.index') }}" class="inline-flex items-center rounded-full border border-[#d9cab3] bg-[#fffaf2] px-4 py-2 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f1e6d2]">&larr; Back to properties</a>

        <div class="mt-8 overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white shadow-[0_30px_80px_rgba(29,60,52,0.14)]">
            <div class="grid gap-0 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="bg-[#edf2ee]">
                    @if ($property->image_path)
                        <img src="{{ asset('storage/' . $property->image_path) }}" alt="{{ $property->title }}" class="h-full min-h-[360px] w-full object-cover">
                    @else
                        <div class="flex h-full min-h-[360px] items-center justify-center bg-gradient-to-br from-[#dfe8e3] via-[#edf3ee] to-[#e9d9b7] text-3xl font-black text-[#1d3c34]">
                            {{ Str::limit($property->title, 18) }}
                        </div>
                    @endif
                </div>

                <div class="p-8 lg:p-10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#587165]">{{ ucfirst($property->type) }}</p>
                        @if ($property->featured)
                            <span class="rounded-full bg-[#f1e4cf] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#1d3c34]">Featured</span>
                        @endif
                    </div>

                    <h1 class="mt-5 text-4xl font-black tracking-tight text-[#1d3c34]">{{ $property->title }}</h1>
                    <p class="mt-3 text-lg text-slate-600">{{ $property->city ?? 'Location available' }} · {{ $property->address ?? 'Available in a prime district' }}</p>

                    <p class="mt-6 text-4xl font-black text-[#1d3c34]">${{ number_format($property->price, 2) }}</p>

                    <div class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl bg-[#f8f3eb] p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-[#60716c]">Bedrooms</p>
                            <p class="mt-2 text-2xl font-black text-[#1d3c34]">{{ $property->bedrooms }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#edf2ee] p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-[#60716c]">Bathrooms</p>
                            <p class="mt-2 text-2xl font-black text-[#1d3c34]">{{ $property->bathrooms }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#f3ecdb] p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-[#60716c]">Area</p>
                            <p class="mt-2 text-2xl font-black text-[#1d3c34]">{{ $property->area }} sq ft</p>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ url('/app#contact') }}" class="rounded-full bg-[#1d3c34] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-[#1d3c34]/20 transition hover:bg-[#264d41]">Book a visit</a>
                        <a href="{{ route('properties.index') }}" class="rounded-full border border-[#d9cab3] bg-[#f9f4ec] px-6 py-3 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">View more listings</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 rounded-[2rem] border border-[#d9cab3] bg-white p-8 shadow-sm">
            <h2 class="text-2xl font-black text-[#1d3c34]">Description</h2>
            <p class="mt-4 text-base leading-8 text-slate-600">{{ $property->description ?: 'This property offers a premium living experience in a desirable location with thoughtful design, practical comfort, and strong investment value.' }}</p>
        </div>
    </main>
</body>
</html>
