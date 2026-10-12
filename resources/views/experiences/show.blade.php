@extends('app')

@section('title', $experience->title . ' | ' . ($siteBrand['name'] ?? 'GTM Real Estate'))
@section('description', Str::limit(strip_tags($experience->description), 150))

@section('content')
<div class="min-h-screen bg-[#f6f1e8] py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs font-bold text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-[#102b25]">Home</a>
            <span>&rsaquo;</span>
            <a href="{{ route('experiences.index') }}" class="hover:text-[#102b25]">Experiences</a>
            <span>&rsaquo;</span>
            <span class="text-[#102b25]">{{ $experience->title }}</span>
        </nav>

        {{-- Main Grid --}}
        <div class="mt-6 grid gap-10 lg:grid-cols-3">
            {{-- Details (Left 2 cols) --}}
            <div class="space-y-8 lg:col-span-2">
                {{-- Banner --}}
                <div class="relative h-96 w-full overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-slate-900 shadow-md">
                    <img src="{{ $experience->cover_image ?: asset('images/luxury/hero-skyline.jpg') }}"
                         alt="{{ $experience->title }}"
                         class="h-full w-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6">
                        <span class="rounded-full bg-[#d4af37] px-3 py-1 text-[11px] font-black uppercase tracking-wider text-[#102b25]">
                            {{ ucfirst($experience->category) }}
                        </span>
                        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">
                            {{ $experience->title }}
                        </h1>
                        <p class="mt-1 text-xs text-[#dfeee4]">
                            📍 {{ $experience->location_address ?: $experience->city }} · ⏱️ {{ $experience->duration_hours }} hours · 👥 Up to {{ $experience->max_participants }} people
                        </p>
                    </div>
                </div>

                {{-- Description & Overview --}}
                <div class="rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xs sm:p-8">
                    <h2 class="text-xl font-black text-[#102b25]">About this Experience</h2>
                    <p class="mt-4 text-sm leading-relaxed text-slate-700 whitespace-pre-line">
                        {{ $experience->description }}
                    </p>

                    @if ($experience->meeting_point)
                        <div class="mt-6 rounded-2xl border border-[#f0e4cf] bg-[#fdfaf5] p-4">
                            <span class="text-xs font-black uppercase tracking-wider text-[#587165]">Meeting Point</span>
                            <p class="mt-1 text-sm font-bold text-[#102b25]">🧭 {{ $experience->meeting_point }}</p>
                        </div>
                    @endif

                    @if (!empty($experience->included_items))
                        <div class="mt-6 border-t border-[#f0e4cf] pt-6">
                            <h3 class="text-sm font-black uppercase tracking-wider text-[#587165]">What is Included</h3>
                            <ul class="mt-3 grid gap-2 sm:grid-cols-2 text-xs font-semibold text-slate-700">
                                @foreach ((array) $experience->included_items as $item)
                                    <li class="flex items-center gap-2">
                                        <span class="text-emerald-700 font-bold">✓</span> {{ $item }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Booking Sidebar (Right 1 col) --}}
            <div>
                <div class="sticky top-28 rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-xl sm:p-8"
                     x-data="{
                        pricePerPerson: {{ $experience->price_etb }},
                        participants: 1,
                        get totalPrice() { return this.pricePerPerson * this.participants; }
                     }">
                    <div class="flex items-baseline justify-between border-b border-[#f0e4cf] pb-4">
                        <div>
                            <span class="text-2xl font-black text-[#102b25]">ETB {{ number_format($experience->price_etb) }}</span>
                            <span class="text-xs text-slate-500">/ person</span>
                        </div>
                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-black text-emerald-800">
                            Verified Tour
                        </span>
                    </div>

                    @if (session('success'))
                        <div class="mt-4 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-xs font-bold text-emerald-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('experiences.book', $experience) }}" class="mt-6 space-y-4">
                        @csrf

                        {{-- Dual-mode Ethiopian date picker --}}
                        <div>
                            <x-ethiopian-date-picker
                                name="scheduled_date"
                                id="experience-scheduled-date"
                                label="Tour Date"
                                min="{{ date('Y-m-d') }}"
                                value="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Time Slot</label>
                            <select name="scheduled_time" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-xs font-bold text-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                                <option value="09:00">09:00 AM (Morning Session)</option>
                                <option value="14:00">02:00 PM (Afternoon Session)</option>
                                <option value="17:00">05:00 PM (Sunset / Evening)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Guests Count</label>
                            <div class="mt-1 flex items-center justify-between rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2">
                                <span class="text-xs font-bold text-slate-700">Participants:</span>
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="participants = Math.max(1, participants - 1)" class="h-7 w-7 rounded-lg bg-[#e7ddca] text-xs font-black text-[#102b25] hover:bg-[#d9cab3]">-</button>
                                    <span class="text-sm font-black text-[#102b25]" x-text="participants"></span>
                                    <button type="button" @click="participants = Math.min({{ $experience->max_participants }}, participants + 1)" class="h-7 w-7 rounded-lg bg-[#e7ddca] text-xs font-black text-[#102b25] hover:bg-[#d9cab3]">+</button>
                                </div>
                            </div>
                            <input type="hidden" name="participants_count" :value="participants">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Contact Full Name</label>
                            <input type="text" name="contact_name" value="{{ old('contact_name', auth()->user()?->name) }}" required
                                   class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2 text-xs font-bold text-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Email</label>
                                <input type="email" name="contact_email" value="{{ old('contact_email', auth()->user()?->email) }}" required
                                       class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2 text-xs font-bold text-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Phone</label>
                                <input type="tel" name="contact_phone" value="{{ old('contact_phone', auth()->user()?->phone ?? '+251 91 ') }}" required
                                       class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2 text-xs font-bold text-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Payment Method</label>
                            <div class="mt-1 grid grid-cols-2 gap-2">
                                <label class="flex items-center gap-2 rounded-xl border border-[#d9cab3] bg-[#fdfaf5] p-2.5 text-xs font-bold text-[#102b25] cursor-pointer hover:border-[#102b25]">
                                    <input type="radio" name="payment_method" value="telebirr" checked class="text-[#102b25]">
                                    <span>📱 Telebirr</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-xl border border-[#d9cab3] bg-[#fdfaf5] p-2.5 text-xs font-bold text-[#102b25] cursor-pointer hover:border-[#102b25]">
                                    <input type="radio" name="payment_method" value="cbe_birr" class="text-[#102b25]">
                                    <span>🏦 CBE Birr</span>
                                </label>
                            </div>
                        </div>

                        {{-- Total Calculation --}}
                        <div class="rounded-2xl border border-[#f0e4cf] bg-[#fdfaf5] p-4 text-xs">
                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal</span>
                                <span>ETB <span x-text="totalPrice.toLocaleString()"></span></span>
                            </div>
                            <div class="mt-1 flex justify-between font-black text-[#102b25] text-sm border-t border-[#f0e4cf] pt-2">
                                <span>Total Due</span>
                                <span>ETB <span x-text="totalPrice.toLocaleString()"></span></span>
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full rounded-2xl bg-[#102b25] py-3 text-center text-xs font-black uppercase tracking-wider text-white shadow-md transition hover:bg-[#d4af37] hover:text-[#102b25] active:scale-98">
                            Reserve &amp; Confirm
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
