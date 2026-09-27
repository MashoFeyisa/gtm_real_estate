<footer class="mt-8 bg-[#102b25] text-[#e6ede6]">
    <div class="mx-auto grid max-w-7xl gap-10 px-6 py-14 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
        <div>
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset($siteBrand['logo']) }}" alt="{{ $siteBrand['name'] }} Logo" class="h-10 w-10 rounded-xl object-cover ring-2 ring-[#d9cab3]">
                <span class="text-2xl font-black text-white">{{ $siteBrand['name'] }}</span>
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
                <li>{{ $siteBrand['address'] }}</li>
                <li>{{ $siteBrand['phone'] }}</li>
                <li>{{ $siteBrand['email'] }}</li>
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 py-6 text-sm text-[#b8c7bd] md:flex-row lg:px-8">
            <p>© {{ date('Y') }} {{ $siteBrand['name'] }}. All rights reserved.</p>
            <div class="flex gap-6">
                <a href="{{ route('privacy-policy') }}" class="transition hover:text-white">Privacy Policy</a>
                <a href="{{ route('terms') }}" class="transition hover:text-white">Terms</a>
            </div>
        </div>
    </div>
</footer>
