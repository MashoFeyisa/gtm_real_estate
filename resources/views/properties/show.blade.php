@extends('app')

@section('title', $property->title)
@section('description', Str::limit(strip_tags($property->description ?: $property->title), 160))

@section('content')
    <section class="mx-auto max-w-6xl px-6 py-12 lg:px-8">
        <a href="{{ route('properties.index') }}" class="inline-flex items-center rounded-full border border-[#d9cab3] bg-[#fffaf2] px-4 py-2 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f1e6d2]">&larr; Back to properties</a>

        <div class="mt-8 overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white shadow-[0_30px_80px_rgba(29,60,52,0.14)]">
            <div class="grid gap-0 md:grid-cols-[1.2fr_0.8fr]">
                <div class="bg-[#edf2ee]">
                    @if ($property->image_path)
                        <img src="{{ asset('storage/' . $property->image_path) }}" alt="{{ $property->title }}" class="h-full min-h-[360px] w-full object-cover">
                    @else
                        <div class="flex h-full min-h-[360px] items-center justify-center bg-gradient-to-br from-[#dfe8e3] via-[#edf3ee] to-[#e9d9b7] text-3xl font-black text-[#1d3c34]">
                            {{ Str::limit($property->title, 18) }}
                        </div>
                    @endif
                </div>

                <div class="p-6 md:p-8 lg:p-10">
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
                        <a href="#buy-request" class="rounded-full bg-[#1d3c34] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-[#1d3c34]/20 transition hover:bg-[#264d41]">Buy / Request</a>
                        <a href="{{ route('properties.index') }}" class="rounded-full border border-[#d9cab3] bg-[#f9f4ec] px-6 py-3 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f0e4cf]">View more listings</a>
                    </div>

                    @if ($property->agent)
                        <div class="mt-8 rounded-2xl border border-[#d9cab3] bg-[#f8f3eb] p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#587165]">Listed by</p>
                            <div class="mt-3 flex items-center gap-3">
                                <img src="{{ $property->agent->photo_url }}" alt="{{ $property->agent->name }}" class="h-12 w-12 rounded-full object-cover ring-1 ring-[#d9cab3]">
                                <div class="min-w-0">
                                    <a href="{{ route('agents.show', $property->agent) }}" class="block truncate font-bold text-[#1d3c34] hover:text-[#2e5a4c]">{{ $property->agent->name }}</a>
                                    <span class="block truncate text-xs text-slate-600">{{ $property->agent->bio ?: 'Property consultant' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-8 rounded-[2rem] border border-[#d9cab3] bg-white p-8 shadow-sm">
            <h2 class="text-2xl font-black text-[#1d3c34]">Description</h2>
            <p class="mt-4 text-base leading-8 text-slate-600">{{ $property->description ?: 'This property offers a premium living experience in a desirable location with thoughtful design, practical comfort, and strong investment value.' }}</p>
        </div>

        <div id="buy-request" class="mt-8 scroll-mt-24 rounded-[2rem] bg-[#1d3c34] p-8 text-[#f9f3e9] shadow-lg shadow-[#1d3c34]/20 lg:p-10">
            <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#d9cab3]">{{ $property->type === 'rent' ? 'Rental Request' : 'Buy Request' }}</p>
            <h2 class="mt-3 text-3xl font-black">Request “{{ $property->title }}”</h2>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-[#ebefd9]">
                Send your {{ $property->type === 'rent' ? 'rental' : 'purchase' }} request to{{ $property->agent ? ' '.$property->agent->name : ' our sales team' }}. Once you agree, a branded {{ $property->type === 'rent' ? 'rental' : 'purchase' }} agreement is generated for you.
            </p>

            <div class="mt-8 rounded-[1.5rem] bg-[#f8f3eb] p-6 text-[#1d3c34] md:p-8">
                @if (session('success'))
                    <div class="mb-4 rounded-xl border border-[#d7e5d2] bg-[#edf9ee] px-4 py-3 text-sm font-semibold text-[#214f3a]">
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

                <form method="POST" action="{{ route('orders.store', $property) }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <div>
                        <label for="order-name" class="mb-1 block text-sm font-semibold">Your Name</label>
                        <input id="order-name" name="name" type="text" value="{{ old('name') }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div>
                        <label for="order-email" class="mb-1 block text-sm font-semibold">Email</label>
                        <input id="order-email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div>
                        <label for="order-phone" class="mb-1 block text-sm font-semibold">Phone</label>
                        <input id="order-phone" name="phone" type="tel" value="{{ old('phone') }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div>
                        <label for="order-agent" class="mb-1 block text-sm font-semibold">Select Agent</label>
                        <select id="order-agent" name="agent_id" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            @if (isset($agents) && $agents->isNotEmpty())
                                @foreach ($agents as $agentOption)
                                    <option value="{{ $agentOption->id }}" {{ (string) old('agent_id', $property->agent_id) === (string) $agentOption->id ? 'selected' : '' }}>
                                        {{ $agentOption->name }}{{ $agentOption->bio ? ' — '.$agentOption->bio : '' }}
                                    </option>
                                @endforeach
                            @elseif ($property->agent)
                                <option value="{{ $property->agent->id }}" selected>{{ $property->agent->name }}</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label for="order-offer" class="mb-1 block text-sm font-semibold">{{ $property->type === 'rent' ? 'Your Proposed Monthly Rent (USD, optional)' : 'Your Offer (USD, optional)' }}</label>
                        <input id="order-offer" name="offer_amount" type="number" step="0.01" min="0" value="{{ old('offer_amount') }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    @if ($property->type === 'rent')
                        <div>
                            <label for="order-lease-start" class="mb-1 block text-sm font-semibold">Preferred Lease Start</label>
                            <input id="order-lease-start" name="lease_start" type="date" value="{{ old('lease_start') }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label for="order-lease-months" class="mb-1 block text-sm font-semibold">Lease Duration</label>
                            <select id="order-lease-months" name="lease_months" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                @foreach ([6 => '6 months', 12 => '12 months', 24 => '24 months', 36 => '36 months'] as $months => $label)
                                    <option value="{{ $months }}" {{ (string) old('lease_months', '12') === (string) $months ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="md:col-span-2">
                        <label for="order-message" class="mb-1 block text-sm font-semibold">Message</label>
                        <textarea id="order-message" name="message" rows="4" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('message') }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                            Send Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
