@extends('dashboards.layout')

@section('title', 'Post Property')
@section('portal_name', 'Post Property Desk')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="rounded-3xl border border-[#d9cab3] bg-gradient-to-r from-[#1d3c34] to-[#2d5d4d] p-6 text-white shadow-sm md:p-8">
        <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-3 py-1 text-xs font-bold text-[#f8e7b9] uppercase tracking-wider">Property Creation</span>
        <h1 class="mt-2 text-2xl md:text-3xl font-black text-white">Post New Property</h1>
        <p class="mt-1 text-sm text-slate-200">Submit your property listing to the platform marketplace.</p>
    </div>

    <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-8">
        <form action="{{ route('user.properties.store') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Property Title *</label>
                <input type="text" id="title" name="title" required value="{{ old('title') }}" placeholder="e.g. Modern Luxury Villa in Bole Atlas" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#1d3c34] focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                @error('title') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="price" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Price (ETB) *</label>
                    <input type="number" id="price" name="price" required min="0" value="{{ old('price') }}" placeholder="e.g. 5500000" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#1d3c34] focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    @error('price') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="type" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Listing Type</label>
                    <select id="type" name="type" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#1d3c34] focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <option value="sale" @selected(old('type') === 'sale')>For Sale</option>
                        <option value="rent" @selected(old('type') === 'rent')>For Rent</option>
                        <option value="short_stay" @selected(old('type') === 'short_stay')>Short Stay</option>
                    </select>
                </div>
                <div>
                    <label for="city" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">City</label>
                    <input type="text" id="city" name="city" value="{{ old('city', 'Addis Ababa') }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#1d3c34] focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="bedrooms" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Bedrooms</label>
                    <input type="number" id="bedrooms" name="bedrooms" min="0" value="{{ old('bedrooms', 2) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#1d3c34] focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                </div>
                <div>
                    <label for="bathrooms" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Bathrooms</label>
                    <input type="number" id="bathrooms" name="bathrooms" min="0" value="{{ old('bathrooms', 1) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#1d3c34] focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                </div>
                <div>
                    <label for="area" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Area (sqm)</label>
                    <input type="number" id="area" name="area" min="0" value="{{ old('area', 80) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#1d3c34] focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Description</label>
                <textarea id="description" name="description" rows="4" placeholder="Detailed property overview, amenities, views, and standout features..." class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#1d3c34] focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <a href="{{ auth()->user()->dashboard_url }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition dark:border-slate-700 dark:text-slate-300">Cancel</a>
                <button type="submit" class="rounded-xl bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white hover:bg-[#152e27] transition shadow-sm">Save &amp; Post Property</button>
            </div>
        </form>
    </div>
</div>
@endsection
