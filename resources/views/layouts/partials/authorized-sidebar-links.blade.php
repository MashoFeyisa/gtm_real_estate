@php
    $user = auth()->user();
    if (! $user) {
        return;
    }

    $currentRoute = request()->route() ? request()->route()->getName() : '';

    // Capability desks mapped to their permissions and routes
    $capabilityDesks = [
        [
            'title' => 'Properties & Listings',
            'icon' => '🏡',
            'route' => 'seller.dashboard',
            'permissions' => ['properties.create', 'properties.update', 'listings.create', 'listings.publish'],
            'roles' => ['property_owner', 'seller', 'landlord'],
        ],
        [
            'title' => 'Short Stays & Host Desk',
            'icon' => '🛏️',
            'route' => 'host.dashboard',
            'permissions' => ['stays.listing_create', 'stays.listing_update', 'stays.pricing_manage', 'stays.availability_manage'],
            'roles' => ['host'],
        ],
        [
            'title' => 'Developer & Projects',
            'icon' => '🏗️',
            'route' => 'developer.portal',
            'permissions' => ['projects.create', 'projects.update', 'buildings.create', 'units.create'],
            'roles' => ['developer'],
        ],
        [
            'title' => 'Property Operations',
            'icon' => '🏢',
            'route' => 'property-manager.dashboard',
            'permissions' => ['buildings.manage', 'units.manage', 'tenants.manage', 'housekeeping.manage'],
            'roles' => ['property_manager'],
        ],
        [
            'title' => 'Leases & Rent Roll',
            'icon' => '🔑',
            'route' => 'tenant.dashboard',
            'permissions' => ['leases.manage', 'leases.terminate', 'rent.manage', 'rent.track'],
            'roles' => ['tenant', 'renter'],
        ],
        [
            'title' => 'Finance & Settlement',
            'icon' => '💰',
            'route' => 'finance.index',
            'permissions' => ['ledger.view', 'ledger.create', 'reconciliation.view', 'reconciliation.manage', 'tax.manage', 'tax.view', 'payments.manage'],
            'roles' => ['finance_officer'],
        ],
        [
            'title' => 'Agent CRM Portal',
            'icon' => '💼',
            'route' => 'agent.portal',
            'permissions' => ['crm.view', 'crm.manage', 'leads.manage'],
            'roles' => ['agent'],
        ],
        [
            'title' => 'Company Agency Desk',
            'icon' => '🏛️',
            'route' => 'company.dashboard',
            'permissions' => ['agents.manage', 'agents.create', 'agents.update'],
            'roles' => ['company', 'company_admin', 'branch_manager', 'real_estate_company'],
        ],
        [
            'title' => 'Content Moderation',
            'icon' => '⚖️',
            'route' => 'moderator.dashboard',
            'permissions' => ['listings.review', 'moderation.manage', 'reviews.moderate', 'reports.resolve'],
            'roles' => ['moderator'],
        ],
        [
            'title' => 'Property Verification',
            'icon' => '🔍',
            'route' => 'verification-officer.dashboard',
            'permissions' => ['verifications.manage', 'verifications.identity_approve', 'verifications.company_approve', 'verification_jobs.accept'],
            'roles' => ['verification_officer', 'verification_company'],
        ],
        [
            'title' => 'Help Desk Support',
            'icon' => '🎧',
            'route' => 'customer-support.dashboard',
            'permissions' => ['tickets.manage', 'tickets.close', 'users.assist'],
            'roles' => ['customer_support'],
        ],
        [
            'title' => 'Housekeeping & Cleaning',
            'icon' => '🧹',
            'route' => 'cleaner.dashboard',
            'permissions' => ['housekeeping.manage', 'housekeeping.update', 'housekeeping.create'],
            'roles' => ['cleaner', 'housekeeping'],
        ],
        [
            'title' => 'Professional Services',
            'icon' => '🔧',
            'route' => 'professional.dashboard',
            'permissions' => ['services.jobs_receive', 'services.jobs_accept', 'services.profile_manage'],
            'roles' => ['service_provider', 'professional'],
        ],
        [
            'title' => 'Investor Portfolios',
            'icon' => '📈',
            'route' => 'investor.dashboard',
            'permissions' => ['portfolio.create', 'yields.view'],
            'roles' => ['investor'],
        ],
        [
            'title' => 'Marketing Campaigns',
            'icon' => '📣',
            'route' => 'campaign.dashboard',
            'permissions' => ['campaigns.manage', 'campaigns.approve'],
            'roles' => ['campaign_officer'],
        ],
        [
            'title' => 'Buyer & Search Hub',
            'icon' => '🛒',
            'route' => 'buyer.dashboard',
            'permissions' => ['properties.browse', 'searches.save', 'viewings.book', 'offers.create'],
            'roles' => ['buyer', 'client', 'user'],
        ],
        [
            'title' => 'Admin Command Desk',
            'icon' => '🛡️',
            'route' => 'dashboard',
            'permissions' => ['platform.roles_manage', 'platform.audit_view', 'platform.users_manage', 'platform.settings_manage'],
            'roles' => ['admin', 'super_admin'],
        ],
    ];

    // Filter to desks where the user has at least one granted permission,
    // and is not currently on that exact desk,
    // and where the route exists.
    $grantedDesks = collect($capabilityDesks)->filter(function ($desk) use ($user, $currentRoute) {
        if (! Route::has($desk['route'])) {
            return false;
        }

        // Don't show link if currently on that exact route
        if ($currentRoute === $desk['route']) {
            return false;
        }

        // Don't show if this desk is the user's primary role desk
        if (in_array($user->role, $desk['roles'] ?? [], true)) {
            return false;
        }

        // Check if user has permission
        if ($user->isAdmin()) {
            return true;
        }

        return $user->hasAnyPermission($desk['permissions']);
    })->values();
@endphp

@if ($grantedDesks->isNotEmpty())
    <div class="pt-4 mt-4 border-t border-white/10" id="authorized-granted-sidebar-section">
        <div class="px-3 pb-2 flex items-center justify-between">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-[#d4af37]">Granted Access</p>
            <span class="rounded-full bg-[#d4af37]/20 text-[#d4af37] px-2 py-0.5 text-[9px] font-black border border-[#d4af37]/30">
                {{ $grantedDesks->count() }}
            </span>
        </div>
        <div class="space-y-1">
            @foreach ($grantedDesks as $desk)
                <a href="{{ route($desk['route']) }}" class="authorized-sidebar-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10 hover:text-white group">
                    <span class="flex items-center gap-2 truncate">
                        <span>{{ $desk['icon'] }}</span>
                        <span class="truncate">{{ $desk['title'] }}</span>
                    </span>
                    <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-1.5 py-0.5 text-[9px] font-bold text-[#f8e7b9] shrink-0 opacity-80 group-hover:opacity-100">
                        &rarr;
                    </span>
                </a>
            @endforeach
        </div>
    </div>
@endif
