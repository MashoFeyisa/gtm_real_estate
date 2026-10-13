@php
    $role = auth()->user()->role ?? '';
    $user = auth()->user();
@endphp

@if ($role === 'buyer' || $role === 'client' || $role === 'user')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if ($user && $user->hasAnyPermission(['searches.save', 'properties.save', 'searches.view']))
        <a href="#saved-searches" data-target-section="saved-searches" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🔔</span>
                <span>Saved Searches</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['offers.create', 'offers.view', 'offer.create', 'offer.view']))
        <a href="#offers" data-target-section="offers" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🤝</span>
                <span>My Offers</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['viewings.create', 'viewings.view', 'viewing.create', 'viewing.view']))
        <a href="#viewings" data-target-section="viewings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📅</span>
                <span>Viewings</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['properties.save', 'properties.favorite', 'property.save']))
        <a href="#saved-properties" data-target-section="saved-properties" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>❤️</span>
                <span>Saved Properties</span>
            </span>
        </a>
    @endif

@elseif ($role === 'tenant')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if ($user && $user->hasAnyPermission(['leases.manage', 'leases.view', 'agreements.view', 'leases.terminate', 'agreements.sign']))
        <a href="#leases" data-target-section="leases" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🔑</span>
                <span>Lease Agreements</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['rent.view', 'rent.track', 'rent.manage', 'rent.pay', 'payments.view']))
        <a href="#rent-schedule" data-target-section="rent-schedule" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>💰</span>
                <span>Rent Schedule</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['maintenance.create', 'maintenance.view', 'maintenance.manage', 'maintenance.tickets']))
        <a href="#maintenance" data-target-section="maintenance" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🛠️</span>
                <span>Maintenance</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['documents.view', 'documents.create', 'documents.upload', 'property.documents']))
        <a href="#documents" data-target-section="documents" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📄</span>
                <span>Documents</span>
            </span>
        </a>
    @endif

@elseif ($role === 'traveler' || $role === 'guest')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if ($user && $user->hasAnyPermission(['bookings.view', 'stays.view']))
        <a href="#upcoming-stay" data-target-section="upcoming-stay" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🛎️</span>
                <span>Next Stay & Pass</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['bookings.view', 'stays.view', 'stays.search']))
        <a href="#bookings" data-target-section="bookings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📅</span>
                <span>Booking History</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['reviews.view', 'reviews.create']))
        <a href="#reviews" data-target-section="reviews" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>⭐</span>
                <span>Reviews</span>
            </span>
        </a>
    @endif

@elseif ($role === 'investor')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if ($user && $user->hasAnyPermission(['portfolio.create', 'portfolio.view', 'properties.view']))
        <a href="#portfolio" data-target-section="portfolio" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📈</span>
                <span>Portfolio Assets</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['yields.view', 'market.view', 'benchmarks.view']))
        <a href="#benchmarks" data-target-section="benchmarks" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🏛️</span>
                <span>Market Benchmarks</span>
            </span>
        </a>
    @endif

@elseif ($role === 'seller' || $role === 'property_owner' || $role === 'landlord')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if ($user && $user->hasAnyPermission(['properties.create', 'properties.update', 'properties.view', 'properties.delete']))
        <a href="#properties" data-target-section="properties" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🏠</span>
                <span>My Properties</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['listings.create', 'listings.publish', 'listings.manage', 'properties.publish']))
        <a href="#listings" data-target-section="listings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📋</span>
                <span>Listings Workflow</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['inquiries.view', 'leads.manage', 'inquiries.reply', 'sellers.contact']))
        <a href="#leads-inquiries" data-target-section="leads-inquiries" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>💬</span>
                <span>Leads & Inquiries</span>
            </span>
        </a>
    @endif

@elseif ($role === 'host')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if ($user && $user->hasAnyPermission(['bookings.view', 'bookings.manage', 'stays.view', 'stays.booking_view']))
        <a href="#bookings" data-target-section="bookings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📅</span>
                <span>Bookings & Arrivals</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['stays.listing_create', 'stays.listing_update', 'properties.create', 'properties.view']))
        <a href="#accommodations" data-target-section="accommodations" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🏡</span>
                <span>Accommodations</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['reviews.view', 'reviews.reply', 'payouts.view', 'stays.payouts']))
        <a href="#reviews-payouts" data-target-section="reviews-payouts" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>💰</span>
                <span>Reviews & Payouts</span>
            </span>
        </a>
    @endif

@elseif ($role === 'property_manager')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if ($user && $user->hasAnyPermission(['units.manage', 'units.create', 'units.view', 'buildings.manage']))
        <a href="#units" data-target-section="units" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🏢</span>
                <span>Units & Occupancy</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['tenants.manage', 'tenants.view', 'guests.view']))
        <a href="#tenants-guests" data-target-section="tenants-guests" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🧑‍🤝‍🧑</span>
                <span>Tenants & Guests</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['housekeeping.manage', 'maintenance.manage', 'maintenance.view']))
        <a href="#maintenance-cleaning" data-target-section="maintenance-cleaning" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🧹</span>
                <span>Maintenance & Tasks</span>
            </span>
        </a>
    @endif

@elseif ($role === 'service_provider' || $role === 'professional')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if ($user && $user->hasAnyPermission(['services.jobs_receive', 'services.jobs_accept']))
        <a href="#requests" data-target-section="requests" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🧰</span>
                <span>Job Invitations</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['services.jobs_receive', 'services.reports_upload', 'services.profile_manage']))
        <a href="#appointments-reports" data-target-section="appointments-reports" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📑</span>
                <span>Appointments & Reports</span>
            </span>
        </a>
    @endif

@elseif ($role === 'cleaner' || $role === 'housekeeping')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if ($user && $user->hasAnyPermission(['housekeeping.manage', 'housekeeping.update', 'housekeeping.create', 'cleaner.tasks']))
        <a href="#tasks" data-target-section="tasks" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🧹</span>
                <span>Cleaning Tasks</span>
            </span>
        </a>
    @endif

@elseif ($role === 'verification_officer')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if ($user && $user->hasAnyPermission(['verifications.manage', 'verifications.identity_approve', 'verification_jobs.accept']))
        <a href="#documents" data-target-section="documents" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📎</span>
                <span>Property Documents</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['verifications.company_approve', 'verifications.manage']))
        <a href="#companies-developers" data-target-section="companies-developers" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🏢</span>
                <span>Agencies & Developers</span>
            </span>
        </a>
    @endif

@elseif ($role === 'moderator')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if ($user && $user->hasAnyPermission(['listings.review', 'moderation.manage']))
        <a href="#listings" data-target-section="listings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>📋</span>
                <span>Listings Review</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['reports.resolve', 'reports.view', 'moderation.manage']))
        <a href="#reports" data-target-section="reports" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🚨</span>
                <span>Fraud & Safety Reports</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['reviews.moderate', 'moderation.manage']))
        <a href="#reviews" data-target-section="reviews" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>💬</span>
                <span>Review Moderation</span>
            </span>
        </a>
    @endif

@elseif ($role === 'customer_support')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if ($user && $user->hasAnyPermission(['users.assist', 'users.lookup', 'tickets.manage']))
        <a href="#user-lookup" data-target-section="user-lookup" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🔍</span>
                <span>User Lookup</span>
            </span>
        </a>
    @endif
    @if ($user && $user->hasAnyPermission(['tickets.manage', 'tickets.close']))
        <a href="#tickets" data-target-section="tickets" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🎫</span>
                <span>Support Tickets</span>
            </span>
        </a>
    @endif

@else
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
@endif
