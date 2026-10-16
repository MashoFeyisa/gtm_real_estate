@extends('dashboards.layout')

@section('title', 'Host Workspace & Short-Stay Hub')
@section('portal_name', 'Short-Stay Host Desk')

@section('sidebar-nav')
    <x-sidebar-item href="#bookings" icon="📅" label="Bookings & Front-Desk" :count="$upcomingBookings->count()" />
    <x-sidebar-item href="#accommodations" icon="🏡" label="Accommodations" :count="$properties->count()" />
    <x-sidebar-item href="#calendar" icon="🗓️" label="Calendar & Blocks" />
    <x-sidebar-item href="#pricing" icon="🏷️" label="Pricing Rules" />
    <x-sidebar-item href="#rules-manual" icon="📖" label="Rules & Manual" />
    <x-sidebar-item href="#cleaning" icon="🧹" label="Cleaning & Housekeeping" :count="$cleaningTasks->where('status', 'pending')->count()" />
    <x-sidebar-item href="#reviews-payouts" icon="⭐" label="Reviews & Payouts" :count="$reviews->count()" />
@endsection

@section('content')
<div x-data="{ openStayModal: false, activeEditProp: null }" class="space-y-8">
    {{-- OVERVIEW SECTION --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="token-stat-card">
                <span class="token-stat-title">Gross Payouts</span>
                <p class="token-stat-value token-stat-primary">{{ number_format($totalRevenue) }} <span class="text-xs font-normal token-muted">ETB</span></p>
                <span class="text-[11px] font-bold text-[var(--token-success)]">Settled &amp; Confirmed</span>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">30-Day Occupancy</span>
                <p class="token-stat-value token-stat-primary">{{ $occupancyRate }}%</p>
                <span class="text-[11px] font-medium token-muted">Based on booked nights</span>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Active Accommodations</span>
                <p class="token-stat-value token-stat-primary">{{ $properties->where('is_active', true)->count() }}</p>
                <span class="text-[11px] font-medium token-muted">{{ $properties->count() }} Total registered</span>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Pending Requests</span>
                <p class="token-stat-value token-stat-warning">{{ $pendingRequests->count() }}</p>
                <span class="text-[11px] font-bold text-[var(--token-warning)]">Require triage action</span>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- 1. BOOKINGS & FRONT-DESK SECTION --}}
    <div id="bookings" data-section="bookings" class="section-panel hidden scroll-mt-6 space-y-6">
        {{-- Pending Booking Requests (Triage) --}}
        @if ($pendingRequests->isNotEmpty())
            <div class="token-alert token-alert-warning space-y-4">
                <h3 class="text-lg font-black flex items-center gap-2">
                    <span>⚡</span>
                    <span>Pending Booking Requests (Awaiting Confirmation)</span>
                </h3>
                <p class="text-xs token-muted">Review guest dates and confirm or decline reservation.</p>

                <div class="token-table-wrap">
                    <table class="token-table">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Accommodation</th>
                                <th>Guest Details</th>
                                <th>Dates</th>
                                <th>Amount</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingRequests as $req)
                                <tr>
                                    <td class="font-mono font-bold text-xs">{{ $req->booking_reference }}</td>
                                    <td class="font-semibold">{{ $req->property?->title }}</td>
                                    <td class="text-xs">
                                        <span class="font-bold block">{{ $req->guest_name }}</span>
                                        <span class="token-muted">{{ $req->guest_phone ?? $req->guest_email }}</span>
                                    </td>
                                    <td class="text-xs">{{ $req->check_in_date?->format('M d') }} &rarr; {{ $req->check_out_date?->format('M d, Y') }} ({{ $req->total_nights }}n)</td>
                                    <td class="font-bold text-[var(--token-success)]">{{ number_format($req->total_amount) }} ETB</td>
                                    <td class="text-right space-x-2">
                                        <form action="{{ route('host.bookings.accept', $req) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="token-btn token-btn-primary !py-1 !px-3 !text-xs">Accept</button>
                                        </form>
                                        <form action="{{ route('host.bookings.reject', $req) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Decline this booking request?')" class="token-btn token-btn-danger !py-1 !px-3 !text-xs">Decline</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Upcoming & Active Bookings Table --}}
        <div class="token-card">
            <h3 class="text-lg font-black border-b border-[var(--token-border)] pb-3">Guest Arrivals &amp; Live Stays</h3>
            <div class="mt-4 token-table-wrap">
                <table class="token-table">
                    <thead>
                        <tr>
                            <th>Booking Ref</th>
                            <th>Accommodation</th>
                            <th>Guest</th>
                            <th>Dates</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-right">Front-Desk Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($upcomingBookings as $booking)
                            <tr>
                                <td class="font-mono font-bold text-xs">{{ $booking->booking_reference }}</td>
                                <td class="font-semibold">{{ $booking->property?->title ?? 'Stay' }}</td>
                                <td class="text-xs">
                                    <span class="font-bold block">{{ $booking->guest_name }}</span>
                                    <span class="token-muted">{{ $booking->guest_phone ?? $booking->guest_email }}</span>
                                </td>
                                <td class="text-xs token-muted">
                                    {{ $booking->check_in_date?->format('M d') }} – {{ $booking->check_out_date?->format('M d, Y') }}
                                    <span class="block text-[10px]">({{ $booking->total_nights }} nights)</span>
                                </td>
                                <td class="font-bold text-[var(--token-success)]">{{ number_format($booking->total_amount) }} ETB</td>
                                <td>
                                    <span class="token-badge {{ $booking->status === 'checked_in' ? 'token-badge-success' : ($booking->status === 'confirmed' ? 'token-badge-info' : 'token-badge-neutral') }}">
                                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    @if ($booking->status === 'confirmed')
                                        <form action="{{ route('host.bookings.check-in', $booking) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="token-btn token-btn-primary !py-1.5 !px-3 !text-xs">Mark Checked-In</button>
                                        </form>
                                    @elseif ($booking->status === 'checked_in')
                                        <form action="{{ route('host.bookings.check-out', $booking) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="token-btn token-btn-secondary !py-1.5 !px-3 !text-xs">Check-Out &amp; Clean</button>
                                        </form>
                                    @else
                                        <span class="text-xs token-muted">&mdash;</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-xs token-muted">No active bookings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 2. ACCOMMODATIONS CRUD SECTION --}}
    <div id="accommodations" data-section="accommodations" class="section-panel hidden scroll-mt-6 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[var(--token-border)] pb-4">
            <div>
                <h3 class="text-lg font-black">My Registered Accommodations</h3>
                <p class="text-xs token-muted">Manage short-stay properties, units, and listing lifecycle</p>
            </div>
            <button type="button" @click="openStayModal = true" class="token-btn token-btn-primary flex items-center gap-2">
                <span>➕</span>
                <span>Register New Accommodation</span>
            </button>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($properties as $prop)
                <div class="token-card flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="token-badge {{ $prop->is_active ? 'token-badge-success' : 'token-badge-neutral' }}">
                                {{ $prop->is_active ? 'Published & Active' : 'Draft / Paused' }}
                            </span>
                            <span class="text-xs font-bold text-[var(--token-success)]">{{ number_format($prop->price) }} ETB / nt</span>
                        </div>
                        <h4 class="mt-3 font-bold text-base">{{ $prop->title }}</h4>
                        <p class="text-xs token-muted mt-1">📍 {{ $prop->city }}, {{ $prop->subcity ?? 'Addis Ababa' }}</p>
                        <p class="text-xs token-muted mt-2 line-clamp-2">{{ $prop->description ?? 'Comfortable short-stay unit.' }}</p>

                        <div class="mt-3 flex items-center gap-3 text-xs token-muted border-t border-[var(--token-border-subtle)] pt-2">
                            <span>🛏️ {{ $prop->bedrooms ?? 1 }} Bed</span>
                            <span>🚿 {{ $prop->bathrooms ?? 1 }} Bath</span>
                            <span>⏱️ Min {{ $prop->minimum_stay ?? 1 }}n</span>
                            <span>{{ $prop->instant_booking_enabled ? '⚡ Instant' : '📝 Request' }}</span>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-[var(--token-border)] flex items-center justify-between">
                        <form action="{{ route('host.stays.publish', $prop) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="token-btn {{ $prop->is_active ? 'token-btn-secondary' : 'token-btn-primary' }} !py-1.5 !px-3 !text-xs">
                                {{ $prop->is_active ? 'Pause Listing' : 'Publish Listing' }}
                            </button>
                        </form>

                        <div class="flex items-center gap-2">
                            <form action="{{ route('host.stays.destroy', $prop) }}" method="POST" class="inline" onsubmit="return confirm('Permanently remove this accommodation listing?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="token-btn token-btn-ghost !text-[var(--token-danger)] !py-1.5 !px-2.5 !text-xs">Delete</button>
                            </form>
                            <a href="{{ route('properties.show', $prop->slug) }}" class="token-btn token-btn-primary !py-1.5 !px-3 !text-xs">Preview</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-12 text-center">
                    <p class="text-sm font-bold token-muted">No accommodations registered yet.</p>
                    <button type="button" @click="openStayModal = true" class="mt-3 token-btn token-btn-primary">Register First Stay</button>
                </div>
            @endforelse
        </div>
    </div>

    {{-- 3. CALENDAR & AVAILABILITY BLOCKS SECTION --}}
    <div id="calendar" data-section="calendar" class="section-panel hidden scroll-mt-6 space-y-6">
        <div class="token-card">
            <h3 class="text-lg font-black border-b border-[var(--token-border)] pb-3">Calendar Control &amp; Date Blocking</h3>
            <p class="text-xs token-muted mt-1">Block dates for owner maintenance, or import external bookings from Airbnb / Booking.com.</p>

            <div class="mt-6 grid gap-6 md:grid-cols-2">
                {{-- Block Dates Form --}}
                <div class="token-card !p-5 bg-[var(--token-surface-subtle)]">
                    <h4 class="font-bold text-sm flex items-center gap-2">
                        <span>🔒</span>
                        <span>Block Dates (Maintenance / Personal)</span>
                    </h4>
                    @if ($properties->isNotEmpty())
                        <form action="{{ route('host.calendar.block', $properties->first()) }}" method="POST" class="mt-4 space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold token-muted">Target Accommodation</label>
                                <select onchange="this.form.action = '/host/stays/' + this.value + '/calendar/block'" class="mt-1 token-field">
                                    @foreach ($properties as $prop)
                                        <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold token-muted">Check-in Date *</label>
                                    <input type="date" name="check_in_date" required min="{{ now()->toDateString() }}" class="mt-1 token-field">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold token-muted">Check-out Date *</label>
                                    <input type="date" name="check_out_date" required min="{{ now()->addDay()->toDateString() }}" class="mt-1 token-field">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold token-muted">Reason / Notes</label>
                                <input type="text" name="reason" placeholder="e.g. Deep cleaning & repaint" class="mt-1 token-field">
                            </div>
                            <button type="submit" class="w-full token-btn token-btn-primary">Block Dates on Calendar</button>
                        </form>
                    @endif
                </div>

                {{-- Record External Reservation Form --}}
                <div class="token-card !p-5 bg-[var(--token-surface-subtle)]">
                    <h4 class="font-bold text-sm flex items-center gap-2">
                        <span>🌐</span>
                        <span>Record External Reservation</span>
                    </h4>
                    @if ($properties->isNotEmpty())
                        <form action="{{ route('host.calendar.external', $properties->first()) }}" method="POST" class="mt-4 space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold token-muted">Target Accommodation</label>
                                <select onchange="this.form.action = '/host/stays/' + this.value + '/calendar/external'" class="mt-1 token-field">
                                    @foreach ($properties as $prop)
                                        <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold token-muted">Guest Name *</label>
                                    <input type="text" name="guest_name" required placeholder="Guest full name" class="mt-1 token-field">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold token-muted">Platform *</label>
                                    <select name="platform" class="mt-1 token-field">
                                        <option value="airbnb">Airbnb</option>
                                        <option value="booking_com">Booking.com</option>
                                        <option value="vrbo">Vrbo</option>
                                        <option value="walk_in">Walk-in / Offline</option>
                                        <option value="direct">Direct Call</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold token-muted">Check-in *</label>
                                    <input type="date" name="check_in_date" required min="{{ now()->toDateString() }}" class="mt-1 token-field">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold token-muted">Check-out *</label>
                                    <input type="date" name="check_out_date" required min="{{ now()->addDay()->toDateString() }}" class="mt-1 token-field">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold token-muted">Payout Amount (ETB)</label>
                                <input type="number" name="payout_amount" step="any" placeholder="e.g. 12000" class="mt-1 token-field">
                            </div>
                            <button type="submit" class="w-full token-btn token-btn-primary">Record External Booking</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Existing Blocks & External Bookings --}}
            <div class="mt-8 border-t border-[var(--token-border)] pt-5">
                <h4 class="font-bold text-sm">Active Blocks &amp; External Calendar Reservations</h4>
                @php
                    $blockedOrExternal = $bookings->filter(fn ($b) => str_starts_with($b->booking_reference, 'BLK-') || str_starts_with($b->booking_reference, 'EXT-'))->where('status', '!=', 'cancelled');
                @endphp
                <div class="mt-3 token-table-wrap">
                    <table class="token-table">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Property</th>
                                <th>Description / Guest</th>
                                <th>Dates</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($blockedOrExternal as $block)
                                <tr>
                                    <td class="font-mono font-bold">{{ $block->booking_reference }}</td>
                                    <td class="font-semibold">{{ $block->property?->title }}</td>
                                    <td>{{ $block->guest_name }}</td>
                                    <td>{{ $block->check_in_date?->format('M d, Y') }} &rarr; {{ $block->check_out_date?->format('M d, Y') }}</td>
                                    <td class="text-right">
                                        <form action="{{ route('host.calendar.unblock', $block) }}" method="POST" class="inline" onsubmit="return confirm('Remove block and reopen these dates?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="token-btn token-btn-danger !py-1 !px-2.5 !text-xs">Unblock</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center token-muted">No active manual blocks or external bookings.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. PRICING RULES SECTION --}}
    <div id="pricing" data-section="pricing" class="section-panel hidden scroll-mt-6 space-y-6">
        <div class="token-card">
            <h3 class="text-lg font-black border-b border-[var(--token-border)] pb-3">Nightly Pricing &amp; Seasonal Rules</h3>
            <p class="text-xs token-muted mt-1">Configure weekend premiums, seasonal peaks, cleaning fees, and minimum stay lengths per property.</p>

            <div class="mt-6 space-y-6">
                @forelse ($properties as $prop)
                    <div class="token-card !p-5 bg-[var(--token-surface-subtle)]">
                        <h4 class="font-bold text-sm mb-3">🏡 {{ $prop->title }}</h4>
                        <form action="{{ route('host.pricing.update', $prop) }}" method="POST" class="grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-[11px] font-bold token-muted">Base Nightly (ETB) *</label>
                                <input type="number" name="price" value="{{ $prop->price }}" required min="1" step="any" class="mt-1 token-field">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold token-muted">Weekend Nightly</label>
                                <input type="number" name="weekend_price" value="{{ $prop->weekend_price }}" min="1" step="any" placeholder="Optional" class="mt-1 token-field">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold token-muted">Seasonal Peak Rate</label>
                                <input type="number" name="seasonal_price" value="{{ $prop->seasonal_price }}" min="1" step="any" placeholder="Optional" class="mt-1 token-field">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold token-muted">Min Stay (Nights) *</label>
                                <input type="number" name="minimum_stay" value="{{ $prop->minimum_stay ?? 1 }}" required min="1" class="mt-1 token-field">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold token-muted">Cleaning Fee</label>
                                <input type="number" name="cleaning_fee" value="{{ $prop->cleaning_fee ?? 0 }}" min="0" step="any" class="mt-1 token-field">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full token-btn token-btn-primary">Update Pricing</button>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="text-xs token-muted py-4 text-center">No accommodations found.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 5. HOUSE RULES & MANUAL SECTION --}}
    <div id="rules-manual" data-section="rules-manual" class="section-panel hidden scroll-mt-6 space-y-6">
        <div class="token-card">
            <h3 class="text-lg font-black border-b border-[var(--token-border)] pb-3">House Rules, Manual &amp; Guest Arrival Guide</h3>
            <p class="text-xs token-muted mt-1">Provide clear house rules, WiFi passwords, appliance instructions, lockbox codes, and check-in times.</p>

            <div class="mt-6 space-y-6">
                @forelse ($properties as $prop)
                    <div class="token-card !p-5 bg-[var(--token-surface-subtle)]">
                        <h4 class="font-bold text-sm mb-3">📖 {{ $prop->title }}</h4>
                        <form action="{{ route('host.rules.update', $prop) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="block text-xs font-bold token-muted">Check-In Time</label>
                                    <input type="text" name="check_in_time" value="{{ $prop->check_in_time ?? '14:00' }}" class="mt-1 token-field">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold token-muted">Check-Out Time</label>
                                    <input type="text" name="check_out_time" value="{{ $prop->check_out_time ?? '11:00' }}" class="mt-1 token-field">
                                </div>
                                <div class="flex items-center gap-2 pt-6">
                                    <input type="checkbox" name="instant_booking_enabled" value="1" id="instant_{{ $prop->id }}" {{ $prop->instant_booking_enabled ? 'checked' : '' }} class="h-4 w-4 rounded border-[var(--token-border)] text-[var(--token-primary)] focus:ring-[var(--token-primary)]">
                                    <label for="instant_{{ $prop->id }}" class="text-xs font-bold token-muted">Enable Instant Booking</label>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="block text-xs font-bold token-muted">House Rules (Quiet hours, smoking, pets)</label>
                                    <textarea name="house_rules" rows="3" class="mt-1 token-field">{{ $prop->house_rules }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold token-muted">House Manual (WiFi, appliances, water heater)</label>
                                    <textarea name="house_manual" rows="3" class="mt-1 token-field">{{ $prop->house_manual }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold token-muted">Check-In Instructions (Lockbox, key pickup)</label>
                                    <textarea name="check_in_instructions" rows="3" class="mt-1 token-field">{{ $prop->check_in_instructions }}</textarea>
                                </div>
                            </div>

                            <button type="submit" class="token-btn token-btn-primary">Save Rules &amp; Guide</button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs token-muted py-4 text-center">No accommodations found.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 6. CLEANING & HOUSEKEEPING DISPATCH SECTION --}}
    <div id="cleaning" data-section="cleaning" class="section-panel hidden scroll-mt-6 space-y-6">
        <div class="token-card">
            <h3 class="text-lg font-black border-b border-[var(--token-border)] pb-3">Cleaning &amp; Housekeeping Operations</h3>
            <p class="text-xs token-muted mt-1">Schedule pre-check-in cleaning, turnover sanitization, linen replenishment, and inspect room readiness.</p>

            {{-- Dispatch Cleaning Form --}}
            @if ($properties->isNotEmpty())
                <div class="mt-6 token-card !p-5 bg-[var(--token-surface-subtle)]">
                    <h4 class="font-bold text-sm">🧹 Schedule New Cleaning Task</h4>
                    <form action="{{ route('host.cleaning.store', $properties->first()) }}" method="POST" class="mt-4 grid gap-3 sm:grid-cols-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold token-muted">Accommodation *</label>
                            <select onchange="this.form.action = '/host/stays/' + this.value + '/cleaning'" class="mt-1 token-field">
                                @foreach ($properties as $prop)
                                    <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold token-muted">Cleaning Type *</label>
                            <select name="service_type" class="mt-1 token-field">
                                <option value="turnover_cleaning">Turnover Cleaning (Between Stays)</option>
                                <option value="room_cleaning">Standard Room Cleaning</option>
                                <option value="deep_cleaning">Deep Sanitization &amp; Wash</option>
                                <option value="extra_towels">Linen &amp; Towel Change</option>
                                <option value="maintenance">Minor Maintenance / Repair</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold token-muted">Priority *</label>
                            <select name="priority" class="mt-1 token-field">
                                <option value="normal">Normal</option>
                                <option value="urgent">Urgent (Guest Arriving Today)</option>
                                <option value="low">Low Priority</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold token-muted">Room / Unit Ref</label>
                            <input type="text" name="room_number" placeholder="e.g. Unit 302" class="mt-1 token-field">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-bold token-muted">Specific Instructions / Scope *</label>
                            <input type="text" name="description" required placeholder="e.g. Sanitize bathroom, mop hardwood floors, replenish coffee capsules" class="mt-1 token-field">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full token-btn token-btn-primary">Dispatch Task</button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Active Cleaning Queue --}}
            <div class="mt-6">
                <h4 class="font-bold text-sm">Housekeeping Task Queue</h4>
                <div class="mt-3 token-table-wrap">
                    <table class="token-table">
                        <thead>
                            <tr>
                                <th>Accommodation</th>
                                <th>Type</th>
                                <th>Room</th>
                                <th>Scope / Notes</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cleaningTasks as $task)
                                <tr>
                                    <td class="font-semibold">{{ $task->property?->title }}</td>
                                    <td>{{ $task->service_type_label }}</td>
                                    <td class="font-mono">{{ $task->room_number ?? 'Main Unit' }}</td>
                                    <td class="token-muted max-w-xs">{{ $task->description }}</td>
                                    <td>
                                        <span class="token-badge {{ $task->priority === 'urgent' ? 'token-badge-danger' : 'token-badge-neutral' }}">{{ ucfirst($task->priority) }}</span>
                                    </td>
                                    <td>
                                        <span class="token-badge {{ $task->status === 'completed' ? 'token-badge-success' : 'token-badge-warning' }}">
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        @if ($task->status !== 'completed')
                                            <form action="{{ route('host.cleaning.complete', $task) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="token-btn token-btn-primary !py-1 !px-2.5 !text-xs">Mark Inspected &amp; Ready</button>
                                            </form>
                                        @else
                                            <span class="token-muted text-xs">✓ Inspected</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-4 text-center token-muted">No cleaning tasks logged.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 7. REVIEWS & PAYOUTS SECTION --}}
    <div id="reviews-payouts" data-section="reviews-payouts" class="section-panel hidden scroll-mt-6 grid gap-6 md:grid-cols-2">
        <div class="token-card">
            <h3 class="text-lg font-black border-b border-[var(--token-border)] pb-3">Guest Reviews &amp; Replies</h3>
            <div class="mt-4 space-y-4">
                @forelse ($reviews as $rev)
                    <div class="token-card !p-4 text-xs bg-[var(--token-surface-subtle)]">
                        <div class="flex items-center justify-between">
                            <span class="font-bold">{{ $rev->property?->title ?? 'Stay' }}</span>
                            <span class="font-bold text-[var(--token-warning)]">★ {{ $rev->rating }}/5</span>
                        </div>
                        <p class="token-muted mt-1">"{{ $rev->comment ?? $rev->review_title }}"</p>
                        @if ($rev->host_reply)
                            <div class="mt-2 rounded-lg bg-[var(--token-surface)] p-2.5 border border-[var(--token-border)]">
                                <span class="font-bold">Your reply:</span> {{ $rev->host_reply }}
                                <span class="block text-[10px] token-muted mt-0.5">{{ $rev->host_replied_at?->format('M d, Y') }}</span>
                            </div>
                        @else
                            <form method="POST" action="{{ route('host.reviews.reply', $rev) }}" class="mt-3 flex gap-2">
                                @csrf
                                <input type="text" name="host_reply" required placeholder="Write a professional host reply..." class="flex-1 token-field !py-1.5 !text-xs">
                                <button type="submit" class="token-btn token-btn-primary !py-1.5 !px-3 !text-xs">Reply</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-xs token-muted py-6 text-center">No guest reviews yet.</p>
                @endforelse
            </div>
        </div>

        <div class="token-card">
            <h3 class="text-lg font-black border-b border-[var(--token-border)] pb-3">Recent Payout Settlements</h3>
            <div class="mt-4 space-y-3">
                @forelse ($payouts as $pay)
                    <div class="token-card !p-3 flex items-center justify-between text-xs bg-[var(--token-surface-subtle)]">
                        <div>
                            <span class="font-mono font-bold token-muted">{{ $pay->transaction_number }}</span>
                            <p class="token-muted mt-0.5">{{ $pay->payment_method ?? 'Bank Transfer' }} &bull; {{ $pay->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-[var(--token-success)]">{{ number_format($pay->amount) }} ETB</span>
                            <span class="block text-[10px] font-bold text-[var(--token-success)]">✓ Settled</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs token-muted py-6 text-center">No payout records found.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- REGISTER SHORT-STAY MODAL --}}
    <div x-show="openStayModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-[var(--token-overlay)] p-4 sm:p-6 md:p-20 backdrop-blur-xs" @keydown.escape.window="openStayModal = false">
        <div class="relative mx-auto max-w-2xl token-card !p-6 md:!p-8 shadow-2xl" @click.outside="openStayModal = false">
            <div class="flex items-center justify-between border-b border-[var(--token-border)] pb-4">
                <div>
                    <h3 class="text-xl font-black">Register Short-Stay Accommodation</h3>
                    <p class="text-xs token-muted">List guest rooms or apartments for daily/nightly bookings</p>
                </div>
                <button type="button" @click="openStayModal = false" class="token-btn token-btn-ghost !p-2 !rounded-full">✕</button>
            </div>

            <form action="{{ route('host.stays.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold token-muted">Stay Name / Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Luxury Bole Studio with High-Speed WiFi" class="mt-1 token-field" />
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="block text-xs font-bold token-muted">Nightly Rate (ETB) *</label>
                        <input type="number" name="price" required min="1" step="any" placeholder="3500" class="mt-1 token-field" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold token-muted">Weekend Nightly</label>
                        <input type="number" name="weekend_price" min="1" step="any" placeholder="4000" class="mt-1 token-field" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold token-muted">Cleaning Fee</label>
                        <input type="number" name="cleaning_fee" value="0" min="0" step="any" class="mt-1 token-field" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold token-muted">Min Stay (Nights)</label>
                        <input type="number" name="minimum_stay" value="1" min="1" class="mt-1 token-field" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="block text-xs font-bold token-muted">Bedrooms</label>
                        <input type="number" name="bedrooms" value="1" min="0" class="mt-1 token-field" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold token-muted">Bathrooms</label>
                        <input type="number" name="bathrooms" value="1" min="1" class="mt-1 token-field" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold token-muted">Check-In</label>
                        <input type="text" name="check_in_time" value="14:00" class="mt-1 token-field" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold token-muted">Check-Out</label>
                        <input type="text" name="check_out_time" value="11:00" class="mt-1 token-field" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-bold token-muted">City *</label>
                        <input type="text" name="city" value="Addis Ababa" required class="mt-1 token-field" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold token-muted">Subcity</label>
                        <input type="text" name="subcity" value="Bole" class="mt-1 token-field" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold token-muted">Cover Photo</label>
                        <input type="file" name="image" accept="image/*" class="mt-1 token-field" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold token-muted">House Rules</label>
                    <textarea name="house_rules" rows="2" placeholder="e.g. No smoking indoors. Quiet hours after 22:00." class="mt-1 token-field"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold token-muted">Description</label>
                    <textarea name="description" rows="2" placeholder="Describe the space, amenities, proximity to airport, transport, etc." class="mt-1 token-field"></textarea>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-[var(--token-border)] pt-4">
                    <button type="button" @click="openStayModal = false" class="token-btn token-btn-ghost">Cancel</button>
                    <button type="submit" class="token-btn token-btn-primary">Register &amp; Save Stay</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
