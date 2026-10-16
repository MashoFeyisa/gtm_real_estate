@php
    $role = auth()->user()->role ?? '';
    $user = auth()->user();
@endphp

@if ($role === 'buyer' || $role === 'client' || $role === 'user')
    @if ($user && $user->hasAnyPermission(['searches.save', 'properties.save', 'searches.view']))
        <x-sidebar-item href="#saved-searches" icon="🔔" label="Saved Searches" />
    @endif
    @if ($user && $user->hasAnyPermission(['offers.create', 'offers.view', 'offer.create', 'offer.view']))
        <x-sidebar-item href="#offers" icon="🤝" label="My Offers" />
    @endif
    @if ($user && $user->hasAnyPermission(['viewings.create', 'viewings.view', 'viewing.create', 'viewing.view']))
        <x-sidebar-item href="#viewings" icon="📅" label="Viewings" />
    @endif
    @if ($user && $user->hasAnyPermission(['properties.save', 'properties.favorite', 'property.save']))
        <x-sidebar-item href="#saved-properties" icon="❤️" label="Saved Properties" />
    @endif

@elseif ($role === 'tenant')
    @if ($user && $user->hasAnyPermission(['leases.manage', 'leases.view', 'agreements.view', 'leases.terminate', 'agreements.sign']))
        <x-sidebar-item href="#leases" icon="🔑" label="Lease Agreements" />
    @endif
    @if ($user && $user->hasAnyPermission(['rent.view', 'rent.track', 'rent.manage', 'rent.pay', 'payments.view']))
        <x-sidebar-item href="#rent-schedule" icon="💰" label="Rent Schedule" />
    @endif
    @if ($user && $user->hasAnyPermission(['maintenance.create', 'maintenance.view', 'maintenance.manage', 'maintenance.tickets']))
        <x-sidebar-item href="#maintenance" icon="🛠️" label="Maintenance" />
    @endif
    @if ($user && $user->hasAnyPermission(['documents.view', 'documents.create', 'documents.upload', 'property.documents']))
        <x-sidebar-item href="#documents" icon="📄" label="Documents" />
    @endif

@elseif ($role === 'traveler' || $role === 'guest')
    @if ($user && $user->hasAnyPermission(['bookings.view', 'stays.view']))
        <x-sidebar-item href="#upcoming-stay" icon="🛎️" label="Next Stay & Pass" />
    @endif
    @if ($user && $user->hasAnyPermission(['bookings.view', 'stays.view', 'stays.search']))
        <x-sidebar-item href="#bookings" icon="📅" label="Booking History" />
    @endif
    @if ($user && $user->hasAnyPermission(['reviews.view', 'reviews.create']))
        <x-sidebar-item href="#reviews" icon="⭐" label="Reviews" />
    @endif

@elseif ($role === 'investor')
    @if ($user && $user->hasAnyPermission(['portfolio.create', 'portfolio.view', 'properties.view']))
        <x-sidebar-item href="#portfolio" icon="📈" label="Portfolio Assets" />
    @endif
    @if ($user && $user->hasAnyPermission(['yields.view', 'market.view', 'benchmarks.view']))
        <x-sidebar-item href="#benchmarks" icon="🏛️" label="Market Benchmarks" />
    @endif

@elseif ($role === 'seller' || $role === 'property_owner' || $role === 'landlord')
    @if ($user && $user->hasAnyPermission(['properties.create', 'properties.update', 'properties.view', 'properties.delete']))
        <x-sidebar-item href="#properties" icon="🏠" label="My Properties" />
    @endif
    @if ($user && $user->hasAnyPermission(['listings.create', 'listings.publish', 'listings.manage', 'properties.publish']))
        <x-sidebar-item href="#listings" icon="📋" label="Listings Workflow" />
    @endif
    @if ($user && $user->hasAnyPermission(['inquiries.view', 'leads.manage', 'inquiries.reply', 'sellers.contact']))
        <x-sidebar-item href="#leads-inquiries" icon="💬" label="Leads & Inquiries" />
    @endif

@elseif ($role === 'host')
    @if ($user && $user->hasAnyPermission(['bookings.view', 'bookings.manage', 'stays.view', 'stays.booking_view']))
        <x-sidebar-item href="#bookings" icon="📅" label="Bookings & Arrivals" />
    @endif
    @if ($user && $user->hasAnyPermission(['stays.listing_create', 'stays.listing_update', 'properties.create', 'properties.view']))
        <x-sidebar-item href="#accommodations" icon="🏡" label="Accommodations" />
    @endif
    @if ($user && $user->hasAnyPermission(['reviews.view', 'reviews.reply', 'payouts.view', 'stays.payouts']))
        <x-sidebar-item href="#reviews-payouts" icon="💰" label="Reviews & Payouts" />
    @endif

@elseif ($role === 'property_manager')
    @if ($user && $user->hasAnyPermission(['units.manage', 'units.create', 'units.view', 'buildings.manage']))
        <x-sidebar-item href="#units" icon="🏢" label="Units & Occupancy" />
    @endif
    @if ($user && $user->hasAnyPermission(['tenants.manage', 'tenants.view', 'guests.view']))
        <x-sidebar-item href="#tenants-guests" icon="🧑‍🤝‍🧑" label="Tenants & Guests" />
    @endif
    @if ($user && $user->hasAnyPermission(['housekeeping.manage', 'maintenance.manage', 'maintenance.view']))
        <x-sidebar-item href="#maintenance-cleaning" icon="🧹" label="Maintenance & Tasks" />
    @endif

@elseif ($role === 'service_provider' || $role === 'professional')
    @if ($user && $user->hasAnyPermission(['services.jobs_receive', 'services.jobs_accept']))
        <x-sidebar-item href="#requests" icon="🧰" label="Job Invitations" />
    @endif
    @if ($user && $user->hasAnyPermission(['services.jobs_receive', 'services.reports_upload', 'services.profile_manage']))
        <x-sidebar-item href="#appointments-reports" icon="📑" label="Appointments & Reports" />
    @endif

@elseif ($role === 'cleaner' || $role === 'housekeeping')
    @if ($user && $user->hasAnyPermission(['housekeeping.manage', 'housekeeping.update', 'housekeeping.create', 'cleaner.tasks']))
        <x-sidebar-item href="#tasks" icon="🧹" label="Cleaning Tasks" />
    @endif

@elseif ($role === 'verification_officer')
    @if ($user && $user->hasAnyPermission(['verifications.manage', 'verifications.identity_approve', 'verification_jobs.accept']))
        <x-sidebar-item href="#documents" icon="📎" label="Property Documents" />
    @endif
    @if ($user && $user->hasAnyPermission(['verifications.company_approve', 'verifications.manage']))
        <x-sidebar-item href="#companies-developers" icon="🏢" label="Agencies & Developers" />
    @endif

@elseif ($role === 'moderator')
    @if ($user && $user->hasAnyPermission(['listings.review', 'moderation.manage']))
        <x-sidebar-item href="#listings" icon="📋" label="Listings Review" />
    @endif
    @if ($user && $user->hasAnyPermission(['reports.resolve', 'reports.view', 'moderation.manage']))
        <x-sidebar-item href="#reports" icon="🚨" label="Fraud & Safety Reports" />
    @endif
    @if ($user && $user->hasAnyPermission(['reviews.moderate', 'moderation.manage']))
        <x-sidebar-item href="#reviews" icon="💬" label="Review Moderation" />
    @endif

@elseif ($role === 'customer_support')
    @if ($user && $user->hasAnyPermission(['users.assist', 'users.lookup', 'tickets.manage']))
        <x-sidebar-item href="#user-lookup" icon="🔍" label="User Lookup" />
    @endif
    @if ($user && $user->hasAnyPermission(['tickets.manage', 'tickets.close']))
        <x-sidebar-item href="#tickets" icon="🎫" label="Support Tickets" />
    @endif

@else
@endif
