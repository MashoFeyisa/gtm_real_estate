@extends('app')

@section('title', 'Edit Agreement Content — ' . ($order->property?->title ?? 'Order #'.$order->id))

@section('content')
<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('dashboard', ['section' => 'orders']) }}" class="inline-flex items-center gap-1 text-sm font-medium text-[#587165] hover:text-[#1d3c34]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Dashboard
        </a>
        <h1 class="mt-3 text-2xl font-black text-[#1d3c34]">Edit Agreement Content</h1>
        <p class="mt-1 text-sm text-slate-600">
            Review and edit the agreement details below before generating the PDF for
            <strong>{{ $order->property?->title ?? 'Order #'.$order->id }}</strong>.
        </p>
        <span class="mt-2 inline-block rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider {{ $order->isRental() ? 'bg-[#dfeee4] text-[#1d3c34]' : 'bg-[#f2e4cb] text-[#1d3c34]' }}">
            {{ $order->isRental() ? 'Rental Agreement' : 'Purchase Agreement' }}
        </span>
    </div>

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
            <ul class="list-disc pl-4 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('dashboard.orders.storeAgreementContent', $order) }}">
        @csrf

        {{-- Seller / Landlord Details --}}
        <div class="rounded-[1.25rem] border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-[#1d3c34]">
                {{ $order->isRental() ? 'አከራይ (Landlord)' : 'ሻጭ (Seller)' }}
            </h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Full Name</label>
                    <input type="text" name="seller_name" value="{{ old('seller_name', $content['seller_name']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Phone</label>
                    <input type="text" name="seller_phone" value="{{ old('seller_phone', $content['seller_phone']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Email</label>
                    <input type="email" name="seller_email" value="{{ old('seller_email', $content['seller_email']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Address</label>
                    <input type="text" name="seller_address" value="{{ old('seller_address', $content['seller_address']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
            </div>
        </div>

        {{-- Buyer / Tenant Details --}}
        <div class="mt-5 rounded-[1.25rem] border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-[#1d3c34]">
                {{ $order->isRental() ? 'ተከራይ (Tenant)' : 'ገዢ (Buyer)' }}
            </h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Full Name</label>
                    <input type="text" name="buyer_name" value="{{ old('buyer_name', $content['buyer_name']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Phone</label>
                    <input type="text" name="buyer_phone" value="{{ old('buyer_phone', $content['buyer_phone']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Email</label>
                    <input type="email" name="buyer_email" value="{{ old('buyer_email', $content['buyer_email']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
            </div>
        </div>

        {{-- Property Details --}}
        <div class="mt-5 rounded-[1.25rem] border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-[#1d3c34]">የንብረት መረጃ (Property Details)</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Property Title</label>
                    <input type="text" name="property_title" value="{{ old('property_title', $content['property_title']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">City</label>
                    <input type="text" name="property_city" value="{{ old('property_city', $content['property_city']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Full Address</label>
                    <input type="text" name="property_address" value="{{ old('property_address', $content['property_address']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Property Category</label>
                    <input type="text" name="property_category" value="{{ old('property_category', $content['property_category']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Bedrooms</label>
                    <input type="text" name="bedrooms" value="{{ old('bedrooms', $content['bedrooms']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Bathrooms</label>
                    <input type="text" name="bathrooms" value="{{ old('bathrooms', $content['bathrooms']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Area (sqm)</label>
                    <input type="text" name="area" value="{{ old('area', $content['area']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
            </div>
        </div>

        {{-- Financial Details --}}
        <div class="mt-5 rounded-[1.25rem] border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-[#1d3c34]">የገንዘብ ዝርዝር (Financial Details)</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">
                        {{ $order->isRental() ? 'Monthly Rent (ETB)' : 'Sale Price (ETB)' }}
                    </label>
                    <input type="text" name="offer_amount" value="{{ old('offer_amount', $content['offer_amount']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                </div>
                @if ($order->isRental())
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Lease Duration (Months)</label>
                        <input type="text" name="lease_months" value="{{ old('lease_months', $content['lease_months']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                @endif
            </div>
        </div>

        {{-- Notes & Special Terms --}}
        <div class="mt-5 rounded-[1.25rem] border border-[#e7ddca] bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-[#1d3c34]">ማስታወሻና ልዩ ስምምነቶች (Notes & Terms)</h2>
            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Agent Agreement Note</label>
                    <textarea name="agent_note" rows="2" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">{{ old('agent_note', $content['agent_note']) }}</textarea>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Special Terms / Additional Conditions</label>
                    <textarea name="special_terms" rows="3" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">{{ old('special_terms', $content['special_terms']) }}</textarea>
                    <p class="mt-1 text-xs text-slate-500">These will appear in the "Additional Agreement" section of the PDF.</p>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-8 flex items-center justify-end gap-3">
            <a href="{{ route('dashboard', ['section' => 'orders']) }}" class="rounded-full border border-[#d9cab3] bg-white px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-[#f5f0e7]">
                Cancel
            </a>
            <button type="submit" class="rounded-full bg-[#1d3c34] px-8 py-2.5 text-sm font-bold text-white transition hover:bg-[#254d43]">
                Save & Generate Agreement PDF
            </button>
        </div>
    </form>
</div>
@endsection
