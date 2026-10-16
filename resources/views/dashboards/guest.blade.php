@extends('dashboards.layout')

@section('title', 'Guest & Traveler Dashboard')
@section('portal_name', 'Guest / Traveler Workspace')

@section('sidebar-nav')
    @if ($upcomingBooking && auth()->user()->hasAnyPermission(['bookings.view', 'stays.view']))
        <x-sidebar-item href="#upcoming-stay" icon="🛎️" label="Next Stay & Pass" badge="1" />
    @endif

    @if (auth()->user()->hasAnyPermission(['bookings.view', 'stays.view', 'stays.search']))
        <x-sidebar-item href="#bookings" icon="📅" label="My Bookings" :count="$bookings->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['properties.save']))
        <x-sidebar-item href="#saved-stays" icon="❤️" label="Saved Stays" :count="$savedStays->count()" />
    @endif

    @if (auth()->user()->hasAnyPermission(['reviews.view', 'reviews.create']))
        <x-sidebar-item href="#reviews" icon="⭐" label="My Reviews" :count="$reviews->count()" />
    @endif

    <x-sidebar-item href="#find-stays" icon="✨" label="Explore Stays" :count="$availableStays->count()" />
@endsection

@section('content')
<div class="space-y-8">

    {{-- System Status Banners --}}
    @if (session('success'))
        <div class="token-alert token-alert-success flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-bold">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="token-alert token-alert-danger flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-bold">&times;</button>
        </div>
    @endif

    {{-- 0. Overview Analytics --}}
    <div id="overview" data-section="overview" class="section-panel scroll-mt-6 space-y-8">
        @include('dashboards.partials.analytics-charts')
    </div>

    {{-- 1. Active / Upcoming Stay & Access Pass --}}
    @if ($upcomingBooking)
        <div id="upcoming-stay" data-section="upcoming-stay" class="section-panel hidden scroll-mt-6 token-card">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[var(--token-border-subtle)] pb-4">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="token-badge token-badge-accent uppercase">Next Stay</span>
                    <span class="text-xs font-mono font-bold">{{ $upcomingBooking->booking_reference }}</span>
                    @if ($upcomingBooking->room_number)
                        <span class="token-badge token-badge-neutral font-mono">Room/Unit: {{ $upcomingBooking->room_number }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="token-badge {{ match ($upcomingBooking->status) { 'checked_in' => 'token-badge-success', 'confirmed' => 'token-badge-info', default => 'token-badge-warning' } }}">
                        {{ ucfirst(str_replace('_', ' ', $upcomingBooking->status)) }}
                    </span>
                    @if ($upcomingBooking->isPaid())
                        <span class="token-badge token-badge-success">Paid</span>
                    @endif
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-4">
                    <div>
                        <h3 class="text-2xl font-black">{{ $upcomingBooking->property?->title ?? 'Reserved Residence' }}</h3>
                        <p class="text-xs token-muted mt-1 font-medium">📍 {{ $upcomingBooking->property?->city ?? 'Addis Ababa' }}, {{ $upcomingBooking->property?->subcity ?? 'Bole' }}</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                        <div class="token-chart-tile">
                            <span class="token-muted font-bold uppercase text-[10px] tracking-wider">Check-In</span>
                            <p class="font-black text-sm mt-1">{{ $upcomingBooking->check_in_date?->format('M d, Y') }}</p>
                            <span class="text-[10px] token-muted">From 2:00 PM</span>
                        </div>
                        <div class="token-chart-tile">
                            <span class="token-muted font-bold uppercase text-[10px] tracking-wider">Check-Out</span>
                            <p class="font-black text-sm mt-1">{{ $upcomingBooking->check_out_date?->format('M d, Y') }}</p>
                            <span class="text-[10px] token-muted">By 11:00 AM</span>
                        </div>
                        <div class="token-chart-tile">
                            <span class="token-muted font-bold uppercase text-[10px] tracking-wider">Total</span>
                            <p class="font-black text-sm mt-1 text-[var(--token-success)]">{{ number_format($upcomingBooking->total_amount, 2) }} ETB</p>
                            <span class="text-[10px] token-muted">{{ $upcomingBooking->total_nights }} night(s) &bull; {{ $upcomingBooking->guests_count }} guest(s)</span>
                        </div>
                    </div>

                    {{-- Interactive Check-In & Check-Out State Machine Actions --}}
                    <div class="token-chart-tile flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <span class="text-xs font-bold">Self Check-In / Out Transition</span>
                            <p class="text-[11px] token-muted mt-0.5">Activate door PIN upon arrival or conclude your stay when leaving.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($upcomingBooking->status === 'confirmed')
                                <form method="POST" action="{{ route('guest.bookings.check-in', $upcomingBooking) }}">
                                    @csrf
                                    <button type="submit" class="token-btn token-btn-primary cursor-pointer transition">
                                        🗝️ Check In Now
                                    </button>
                                </form>
                            @elseif ($upcomingBooking->status === 'checked_in')
                                <form method="POST" action="{{ route('guest.bookings.check-out', $upcomingBooking) }}" onsubmit="return confirm('Confirm check-out? Your access pass will be concluded.');">
                                    @csrf
                                    <button type="submit" class="token-btn token-btn-primary cursor-pointer transition">
                                        🚪 Complete Check-Out
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('guest.bookings.receipt', $upcomingBooking) }}" target="_blank" class="token-btn token-btn-secondary transition">
                                🧾 View Receipt
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Access Pass Card --}}
                <div class="token-chart-tile flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-2">
                            <h4 class="font-black text-xs uppercase tracking-wider text-[var(--token-accent)]">Access Pass</h4>
                            <span class="font-mono text-[10px] token-muted">ENCRYPTED</span>
                        </div>
                        <div class="mt-4 space-y-3 text-xs">
                            <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-2">
                                <span class="token-muted">Door PIN Code:</span>
                                <span class="font-mono font-bold text-sm px-2.5 py-1 rounded-lg bg-[var(--token-surface-subtle)] border border-[var(--token-border)]">
                                    {{ $upcomingBooking->status === 'checked_in' || $upcomingBooking->isPaid() ? '8492#' : 'Locked until check-in' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-2">
                                <span class="token-muted">Wi-Fi Network:</span>
                                <span class="font-mono font-semibold">GTM_Stay_VIP</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-2">
                                <span class="token-muted">Wi-Fi Password:</span>
                                <span class="font-mono font-semibold">guestwelcome2026</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="token-muted">Host Contact:</span>
                                <span class="font-semibold">{{ $upcomingBooking->property?->host?->phone ?? '+251 911 234 567' }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($upcomingBooking->property)
                        <div class="mt-5 pt-3 border-t border-[var(--token-border-subtle)] flex items-center justify-between">
                            <a href="{{ route('properties.show', $upcomingBooking->property->slug) }}" class="text-xs font-bold hover:underline">
                                View House Rules &amp; Map &rarr;
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- 2. Bookings Across All Statuses --}}
    <div id="bookings" data-section="bookings" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[var(--token-border-subtle)] pb-4">
            <div>
                <h2 class="text-xl font-black">My Booking History (All Statuses)</h2>
                <p class="text-xs token-muted mt-0.5">Manage your short stay reservations, modify dates, cancel with refund calculation, or download travel receipts.</p>
            </div>
            <a href="#find-stays" data-target-section="find-stays" class="nav-section-link token-btn token-btn-primary self-start sm:self-auto cursor-pointer">
                <span>➕ Find New Stay</span>
            </a>
        </div>

        <div class="mt-6 token-table-wrap">
            <table class="token-table">
                <thead>
                    <tr>
                        <th>Booking Ref</th>
                        <th>Property</th>
                        <th>Stay Dates</th>
                        <th>Nights/Guests</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th class="text-right">Workflow Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="font-mono font-bold text-xs">
                                {{ $booking->booking_reference }}
                                @if ($booking->refund_amount > 0)
                                    <div class="text-[10px] font-mono mt-0.5 font-bold text-[var(--token-warning)]">
                                        Refund: {{ number_format($booking->refund_amount, 2) }} ETB
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="font-bold block">{{ $booking->property?->title ?? 'Stay Accommodation' }}</span>
                                <span class="text-xs token-muted">📍 {{ $booking->property?->city ?? 'Addis Ababa' }}</span>
                            </td>
                            <td class="text-xs">
                                {{ $booking->check_in_date?->format('M d, Y') }} &rarr; {{ $booking->check_out_date?->format('M d, Y') }}
                            </td>
                            <td class="text-xs font-semibold">
                                {{ $booking->total_nights }} night(s) &bull; {{ $booking->guests_count }} guest(s)
                            </td>
                            <td class="font-mono font-bold text-[var(--token-success)]">
                                {{ number_format($booking->total_amount, 2) }} ETB
                                @if (! $booking->isPaid() && $booking->status === 'pending')
                                    <span class="block text-[10px] font-sans font-bold text-[var(--token-warning)]">Unpaid</span>
                                @endif
                            </td>
                            <td>
                                <span class="token-badge {{ match ($booking->status) { 'completed' => 'token-badge-success', 'checked_in' => 'token-badge-info', 'cancelled' => 'token-badge-danger', default => 'token-badge-warning' } }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    {{-- Travel Receipt --}}
                                    <a href="{{ route('guest.bookings.receipt', $booking) }}" target="_blank" class="token-btn token-btn-secondary !px-2.5 !py-1 !text-[11px] transition">
                                        🧾 Receipt
                                    </a>

                                    {{-- Modify Dates Modal Trigger (pending or confirmed) --}}
                                    @if (in_array($booking->status, ['pending', 'confirmed']))
                                        <details class="relative inline-block text-left">
                                            <summary class="list-none token-btn token-btn-secondary !px-2.5 !py-1 !text-[11px] transition cursor-pointer">
                                                ✏️ Modify
                                            </summary>
                                            <div class="token-dropdown absolute right-0 z-20 mt-2 w-80 p-4">
                                                <h4 class="font-black text-xs border-b border-[var(--token-border-subtle)] pb-2 mb-3">Modify Booking Dates</h4>
                                                <form method="POST" action="{{ route('guest.bookings.modify', $booking) }}" class="space-y-3 text-left">
                                                    @csrf
                                                    <div>
                                                        <label class="token-label uppercase">Check-In Date</label>
                                                        <input type="date" name="check_in_date" value="{{ $booking->check_in_date?->format('Y-m-d') }}" required class="token-field mt-1">
                                                    </div>
                                                    <div>
                                                        <label class="token-label uppercase">Check-Out Date</label>
                                                        <input type="date" name="check_out_date" value="{{ $booking->check_out_date?->format('Y-m-d') }}" required class="token-field mt-1">
                                                    </div>
                                                    <div>
                                                        <label class="token-label uppercase">Guests Count</label>
                                                        <input type="number" name="guests_count" value="{{ $booking->guests_count }}" min="1" max="10" class="token-field mt-1">
                                                    </div>
                                                    <div class="pt-2 flex justify-end gap-2">
                                                        <button type="submit" class="token-btn token-btn-primary cursor-pointer">
                                                            Save Dates
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>

                                        {{-- Cancel Reservation Trigger --}}
                                        <details class="relative inline-block text-left">
                                            <summary class="list-none token-btn token-btn-danger !px-2.5 !py-1 !text-[11px] transition cursor-pointer">
                                                ❌ Cancel
                                            </summary>
                                            <div class="token-dropdown absolute right-0 z-20 mt-2 w-80 p-4 text-left">
                                                <h4 class="font-black text-xs text-[var(--token-danger)] border-b border-[var(--token-border-subtle)] pb-2 mb-2">Cancel Reservation</h4>
                                                <div class="token-alert token-alert-warning text-[10px] mb-3">
                                                    <strong>Ethiopian Cancellation Policy:</strong>
                                                    <p class="mt-0.5">&bull; &gt; 48 hrs prior: 100% refund of accommodation.</p>
                                                    <p>&bull; &lt; 48 hrs prior: 50% refund.</p>
                                                    <p>&bull; After check-in date: Non-refundable.</p>
                                                </div>
                                                <form method="POST" action="{{ route('guest.bookings.cancel', $booking) }}" onsubmit="return confirm('Are you sure you want to cancel this booking?');" class="space-y-3">
                                                    @csrf
                                                    <div>
                                                        <label class="token-label uppercase">Reason for Cancellation</label>
                                                        <textarea name="cancellation_reason" rows="2" placeholder="e.g. Flight rescheduled, personal emergency" class="token-field mt-1"></textarea>
                                                    </div>
                                                    <div class="flex justify-end gap-2">
                                                        <button type="submit" class="token-btn token-btn-danger cursor-pointer">
                                                            Confirm Cancellation
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>
                                    @endif

                                    {{-- Pay Now Button if Unpaid --}}
                                    @if ($booking->status === 'pending' && ! $booking->isPaid())
                                        <details class="relative inline-block text-left">
                                            <summary class="list-none token-btn token-btn-primary !px-2.5 !py-1 !text-[11px] transition cursor-pointer">
                                                💳 Pay Now
                                            </summary>
                                            <div class="token-dropdown absolute right-0 z-20 mt-2 w-80 p-4 text-left">
                                                <h4 class="font-black text-xs border-b border-[var(--token-border-subtle)] pb-2 mb-3">Pay Reservation ({{ number_format($booking->total_amount, 2) }} ETB)</h4>
                                                <form method="POST" action="{{ route('bookings.pay', $booking) }}" enctype="multipart/form-data" class="space-y-3">
                                                    @csrf
                                                    <div>
                                                        <label class="token-label uppercase">Payment Gateway</label>
                                                        <select name="payment_method" required class="token-field mt-1">
                                                            <option value="telebirr">Telebirr (Instant Clearance)</option>
                                                            <option value="cbe_birr">CBE Birr</option>
                                                            <option value="bank_transfer">Bank Wire / Deposit</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="token-label uppercase">Transaction Reference</label>
                                                        <input type="text" name="bank_transaction_reference" placeholder="e.g. FT2610XXXX" class="token-field mt-1">
                                                    </div>
                                                    <div>
                                                        <label class="token-label uppercase">Deposit Slip / Screenshot (optional)</label>
                                                        <input type="file" name="deposit_slip" accept="image/*,.pdf" class="mt-1 w-full text-xs token-muted">
                                                    </div>
                                                    <div class="flex justify-end gap-2 pt-2">
                                                        <button type="submit" class="token-btn token-btn-primary cursor-pointer">
                                                            Submit Payment
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-xs token-muted">
                                No bookings found. Explore available short stays to reserve your next accommodation.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. Saved Stays & Favorites --}}
    <div id="saved-stays" data-section="saved-stays" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-4">
            <div>
                <h2 class="text-xl font-black">Saved Stays &amp; Favorites</h2>
                <p class="text-xs token-muted mt-0.5">Short-stay vacation residences and luxury suites you have bookmarked.</p>
            </div>
            <span class="token-badge token-badge-neutral">
                {{ $savedStays->count() }} Saved
            </span>
        </div>

        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($savedStays as $favorite)
                @php $prop = $favorite->property; @endphp
                @if ($prop)
                    <div class="group relative rounded-2xl border border-[var(--token-border)] bg-[var(--token-surface)] overflow-hidden hover:shadow-md transition">
                        <div class="h-44 w-full bg-[var(--token-surface-subtle)] overflow-hidden relative">
                            @if ($prop->images->isNotEmpty())
                                <img src="{{ asset('storage/' . $prop->images->first()->image_path) }}" alt="{{ $prop->title }}" class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="h-full w-full flex items-center justify-center token-muted text-3xl">🏡</div>
                            @endif
                            <div class="absolute top-3 right-3">
                                <form method="POST" action="{{ route('guest.saved-stays.destroy', $favorite) }}" onsubmit="return confirm('Remove from saved stays?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Remove Favorite" class="rounded-full bg-[var(--token-surface)]/90 hover:bg-[var(--token-surface)] p-2 text-[var(--token-danger)] shadow-xs cursor-pointer transition">
                                        ❤️
                                    </button>
                                </form>
                            </div>
                            <div class="absolute bottom-3 left-3">
                                <span class="rounded-md px-2 py-0.5 text-[10px] font-bold bg-[var(--token-hero-start)]/85 text-[var(--token-accent-light)]">
                                    {{ $prop->bedrooms ?? 1 }} Bed &bull; {{ $prop->bathrooms ?? 1 }} Bath
                                </span>
                            </div>
                        </div>

                        <div class="p-4">
                            <h3 class="font-black text-sm truncate">{{ $prop->title }}</h3>
                            <p class="text-xs token-muted mt-0.5">📍 {{ $prop->city }}, {{ $prop->subcity }}</p>
                            <div class="mt-3 flex items-center justify-between border-t border-[var(--token-border-subtle)] pt-3">
                                <span class="font-mono font-bold text-sm text-[var(--token-success)]">
                                    {{ number_format($prop->price, 2) }} ETB <span class="text-[10px] token-muted font-sans">/night</span>
                                </span>
                                <a href="{{ route('properties.show', $prop->slug) }}" class="token-btn token-btn-primary !px-3 !py-1.5 !text-xs">
                                    View Stay &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="col-span-full py-8 text-center text-xs token-muted">
                    You have not saved any short-stay accommodations yet. Click the heart icon on any stay to bookmark it here.
                </div>
            @endforelse
        </div>
    </div>

    {{-- 4. My Reviews & Host Responses --}}
    <div id="reviews" data-section="reviews" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[var(--token-border-subtle)] pb-4">
            <div>
                <h2 class="text-xl font-black">My Reviews &amp; Host Responses</h2>
                <p class="text-xs token-muted mt-0.5">Share feedback on completed stays, update past reviews, and view host responses.</p>
            </div>
            @if ($completedStaysForReview->isNotEmpty())
                <details class="relative inline-block text-left">
                    <summary class="list-none token-btn token-btn-primary cursor-pointer">
                        <span>⭐ Write New Review</span>
                    </summary>
                    <div class="token-dropdown absolute right-0 z-20 mt-2 w-96 p-5">
                        <h4 class="font-black text-xs border-b border-[var(--token-border-subtle)] pb-2 mb-3">Write Review for Completed Stay</h4>
                        <form method="POST" action="{{ route('guest.reviews.store') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="token-label uppercase">Select Stay</label>
                                <select name="booking_id" required onchange="document.getElementById('review_prop_id').value = this.options[this.selectedIndex].getAttribute('data-property-id')" class="token-field mt-1">
                                    <option value="">-- Choose Completed Stay --</option>
                                    @foreach ($completedStaysForReview as $b)
                                        <option value="{{ $b->id }}" data-property-id="{{ $b->property_id }}">
                                            {{ $b->property?->title ?? 'Stay' }} ({{ $b->booking_reference }})
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="property_id" id="review_prop_id" value="{{ $completedStaysForReview->first()?->property_id }}">
                            </div>
                            <div>
                                <label class="token-label uppercase">Rating (1 to 5 Stars)</label>
                                <select name="rating" required class="token-field mt-1">
                                    <option value="5">⭐⭐⭐⭐⭐ 5 - Exceptional Experience</option>
                                    <option value="4">⭐⭐⭐⭐ 4 - Very Good</option>
                                    <option value="3">⭐⭐⭐ 3 - Average</option>
                                    <option value="2">⭐⭐ 2 - Below Expectations</option>
                                    <option value="1">⭐ 1 - Poor</option>
                                </select>
                            </div>
                            <div>
                                <label class="token-label uppercase">Review Title</label>
                                <input type="text" name="review_title" placeholder="e.g. Wonderful stay in Bole!" class="token-field mt-1">
                            </div>
                            <div>
                                <label class="token-label uppercase">Review Comments</label>
                                <textarea name="review_body" rows="3" required placeholder="Describe cleanliness, host communication, accuracy, and location..." class="token-field mt-1"></textarea>
                            </div>
                            <div class="flex justify-end pt-2">
                                <button type="submit" class="token-btn token-btn-primary cursor-pointer">
                                    Publish Review
                                </button>
                            </div>
                        </form>
                    </div>
                </details>
            @endif
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @forelse ($reviews as $rev)
                <div class="token-chart-tile flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-black text-sm">{{ $rev->property?->title ?? 'Stay Experience' }}</span>
                            <span class="text-xs font-black text-[var(--token-accent)]">
                                @for ($i = 0; $i < $rev->rating; $i++) ★ @endfor
                                <span class="token-muted">({{ $rev->rating }}/5)</span>
                            </span>
                        </div>
                        @if ($rev->review_title)
                            <h4 class="text-xs font-bold mt-2">{{ $rev->review_title }}</h4>
                        @endif
                        <p class="text-xs mt-1 font-medium italic">"{{ $rev->review_body }}"</p>

                        @if ($rev->host_reply)
                            <div class="mt-4 token-chart-tile text-xs">
                                <span class="font-bold flex items-center gap-1">
                                    <span>💬</span> Host Response:
                                </span>
                                <p class="mt-1">{{ $rev->host_reply }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 pt-3 border-t border-[var(--token-border-subtle)] flex items-center justify-between">
                        <span class="text-[10px] token-muted">{{ $rev->created_at?->format('M d, Y') }}</span>
                        <div class="flex items-center gap-2">
                            {{-- Edit Review Modal --}}
                            <details class="relative inline-block text-left">
                                <summary class="list-none text-xs font-bold hover:underline cursor-pointer">
                                    Edit
                                </summary>
                                <div class="token-dropdown absolute right-0 bottom-full z-20 mb-2 w-80 p-4">
                                    <h4 class="font-black text-xs border-b border-[var(--token-border-subtle)] pb-2 mb-3">Edit Review</h4>
                                    <form method="POST" action="{{ route('guest.reviews.update', $rev) }}" class="space-y-3">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="token-label uppercase">Rating</label>
                                            <select name="rating" required class="token-field mt-1">
                                                @for ($i = 5; $i >= 1; $i--)
                                                    <option value="{{ $i }}" @selected($rev->rating == $i)>{{ $i }} Stars</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div>
                                            <label class="token-label uppercase">Title</label>
                                            <input type="text" name="review_title" value="{{ $rev->review_title }}" class="token-field mt-1">
                                        </div>
                                        <div>
                                            <label class="token-label uppercase">Review Body</label>
                                            <textarea name="review_body" rows="3" required class="token-field mt-1">{{ $rev->review_body }}</textarea>
                                        </div>
                                        <div class="flex justify-end pt-1">
                                            <button type="submit" class="token-btn token-btn-primary">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </details>

                            {{-- Delete Review --}}
                            <form method="POST" action="{{ route('guest.reviews.destroy', $rev) }}" onsubmit="return confirm('Delete this review permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-[var(--token-danger)] hover:underline cursor-pointer">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-xs token-muted">
                    No reviews written yet. After completing your stay, share your feedback with the host!
                </div>
            @endforelse
        </div>
    </div>

    {{-- 5. Explore Available Stays --}}
    <div id="find-stays" data-section="find-stays" class="section-panel hidden scroll-mt-6 token-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[var(--token-border-subtle)] pb-4">
            <div>
                <h2 class="text-xl font-black">Explore Short-Stay Accommodations</h2>
                <p class="text-xs token-muted mt-0.5">Top-rated vacation rentals, luxury apartments, and serviced suites in Addis Ababa &amp; Ethiopia.</p>
            </div>
            <a href="{{ route('stays.index') }}" class="token-btn token-btn-secondary transition">
                View All Stays &rarr;
            </a>
        </div>

        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($availableStays as $stay)
                <div class="group rounded-2xl border border-[var(--token-border)] bg-[var(--token-surface)] overflow-hidden hover:shadow-md transition">
                    <div class="h-36 w-full bg-[var(--token-surface-subtle)] overflow-hidden relative">
                        @if ($stay->images->isNotEmpty())
                            <img src="{{ asset('storage/' . $stay->images->first()->image_path) }}" alt="{{ $stay->title }}" class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="h-full w-full flex items-center justify-center token-muted text-2xl">🏡</div>
                        @endif
                        <span class="absolute bottom-2 left-2 rounded-md px-2 py-0.5 text-[9px] font-bold bg-[var(--token-hero-start)]/85 text-[var(--token-accent-light)]">
                            {{ $stay->bedrooms ?? 1 }} BR &bull; {{ $stay->bathrooms ?? 1 }} BA
                        </span>
                    </div>
                    <div class="p-3.5">
                        <h4 class="font-black text-xs truncate">{{ $stay->title }}</h4>
                        <p class="text-[10px] token-muted mt-0.5">📍 {{ $stay->city }}, {{ $stay->subcity }}</p>
                        <div class="mt-3 flex items-center justify-between border-t border-[var(--token-border-subtle)] pt-2">
                            <span class="font-mono font-bold text-xs text-[var(--token-success)]">
                                {{ number_format($stay->price) }} ETB
                            </span>
                            <a href="{{ route('properties.show', $stay->slug) }}" class="token-btn token-btn-primary !px-2.5 !py-1 !text-[10px]">
                                Book &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-xs token-muted">
                    No active stays currently featured. Check back soon.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
