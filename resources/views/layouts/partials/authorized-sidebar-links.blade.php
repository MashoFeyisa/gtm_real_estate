@php
    $user = auth()->user();
    if (! $user) {
        return;
    }

    // Admins manage from the central Admin Desk and do not have persona sidebar links
    if ($user->isAdmin()) {
        return;
    }

    $currentRoute = request()->route() ? request()->route()->getName() : '';

    // Default permissions for the user's primary persona/role
    $roleConfig = collect(config('personas.personas', []))->first(function ($p) use ($user) {
        return ($p['role'] ?? null) === $user->role || in_array($user->role ?? '', $p['legacy_roles'] ?? [], true);
    });
    $defaultRolePermKeys = $roleConfig ? array_keys($roleConfig['permissions'] ?? []) : [];

    $aliasMap = [
        'property.create' => 'properties.create',
        'properties.create' => 'property.create',
        'property.view' => 'properties.view',
        'properties.view' => 'property.view',
        'offer.create' => 'offers.create',
        'offers.create' => 'offer.create',
        'offer.view' => 'offers.view',
        'offers.view' => 'offer.view',
        'viewing.view' => 'viewings.view',
        'viewings.view' => 'viewing.view',
        'viewing.create' => 'viewings.create',
        'viewings.create' => 'viewing.create',
        'booking.view' => 'bookings.view',
        'bookings.view' => 'booking.view',
        'lease.view' => 'leases.view',
        'leases.view' => 'lease.view',
        'inquiry.view' => 'inquiries.view',
        'inquiries.view' => 'inquiry.view',
        'inquiry.create' => 'inquiries.create',
        'inquiries.create' => 'inquiry.create',
        'review.view' => 'reviews.view',
        'reviews.view' => 'review.view',
        'review.create' => 'reviews.create',
        'reviews.create' => 'review.create',
    ];

    // Routes that this role's base dashboard already handles internally
    $roleBaseHandledRoutes = match ($user->role) {
        'buyer' => ['buyer.dashboard', 'user.offers', 'user.viewings', 'user.inquiries'],
        'host' => ['host.dashboard', 'user.bookings'],
        'agent' => ['agent.portal', 'user.properties.create', 'user.offers', 'user.viewings', 'user.leases', 'user.inquiries', 'user.reviews'],
        'tenant', 'renter' => ['tenant.dashboard', 'user.leases', 'user.maintenance'],
        'seller', 'landlord', 'property_owner' => ['seller.dashboard', 'user.properties.create', 'user.offers', 'user.inquiries', 'user.leases', 'user.maintenance'],
        'guest', 'traveler' => ['guest.dashboard', 'user.bookings', 'user.reviews'],
        default => [],
    };

    // Sensible semantic grouping and target routing for permission-granted features
    $groupRegistry = [
        'user.leases' => ['group' => 'Living', 'title' => 'Leases & Rent', 'target' => '#leases', 'section' => 'leases', 'in_dashboard' => true],
        'user.maintenance' => ['group' => 'Living', 'title' => 'Maintenance Requests', 'target' => '#maintenance', 'section' => 'maintenance', 'in_dashboard' => true],
        'user.bookings' => ['group' => 'Stays', 'title' => 'My Bookings', 'target' => '#bookings', 'section' => 'bookings', 'in_dashboard' => true],
        'user.properties.create' => ['group' => 'Properties', 'title' => 'Post Property', 'target' => route('user.properties.create'), 'section' => null, 'in_dashboard' => false],
        'user.offers' => ['group' => 'Deals', 'title' => 'My Offers', 'target' => '#offers', 'section' => 'offers', 'in_dashboard' => true],
        'user.viewings' => ['group' => 'Viewings', 'title' => 'My Viewings', 'target' => '#viewings', 'section' => 'viewings', 'in_dashboard' => true],
        'user.inquiries' => ['group' => 'Communication', 'title' => 'My Inquiries', 'target' => '#inquiries', 'section' => 'inquiries', 'in_dashboard' => true],
        'user.reviews' => ['group' => 'Reviews', 'title' => 'My Reviews', 'target' => '#reviews', 'section' => 'reviews', 'in_dashboard' => true],
    ];

    // Query database for all permissions that have a user-facing route configured
    $mappedPermissions = \App\Models\Permission::query()
        ->whereNotNull('route')
        ->where('route', '!=', '')
        ->get(['id', 'key', 'menu_label', 'label', 'icon', 'route']);

    // Admin routes blacklist: ensure NO admin route could ever be listed
    $adminRoutes = [
        'dashboard',
        'role.portal',
        'finance.index',
        'moderator.dashboard',
        'verification-officer.dashboard',
        'customer-support.dashboard',
    ];

    $grantedItems = collect();
    $seenRoutes = [];

    foreach ($mappedPermissions as $permission) {
        // Must NOT be an admin route
        if (in_array($permission->route, $adminRoutes, true) || str_starts_with($permission->route, 'dashboard.')) {
            continue;
        }

        // Must be a valid existing registered route
        if (! \Illuminate\Support\Facades\Route::has($permission->route)) {
            continue;
        }

        // Don't show if user is currently on that exact route
        if ($currentRoute === $permission->route) {
            continue;
        }

        // Must NOT be a route already handled in this role's base menu
        if (in_array($permission->route, $roleBaseHandledRoutes, true)) {
            continue;
        }

        // De-duplicate by route
        if (isset($seenRoutes[$permission->route])) {
            continue;
        }

        // Check if user holds this permission
        if (! $user->hasPermission($permission->key)) {
            continue;
        }

        // Determine whether this permission is already part of the user's default role setup
        $isRoleDefault = in_array($permission->key, $defaultRolePermKeys, true)
            || (isset($aliasMap[$permission->key]) && in_array($aliasMap[$permission->key], $defaultRolePermKeys, true));

        // Check for an explicit user override
        $hasUserOverride = $user->permissionOverrides()->where('permission_id', $permission->id)->where('granted', true)->exists()
            || $user->userPermissions()->where('permission_id', $permission->id)->where('granted', true)->exists();

        // If it's a default role capability and has no custom override, it's not an extra grant
        if ($isRoleDefault && ! $hasUserOverride) {
            continue;
        }

        $seenRoutes[$permission->route] = true;

        $meta = $groupRegistry[$permission->route] ?? [
            'group' => 'Features',
            'title' => $permission->menu_label ?: $permission->label,
            'target' => route($permission->route),
            'section' => null,
            'in_dashboard' => false,
        ];

        $cleanIcon = trim((string) ($permission->icon ?: '✨'));

        $grantedItems->push([
            'key' => $permission->key,
            'group' => $meta['group'],
            'title' => $meta['title'],
            'icon' => $cleanIcon,
            'target' => $meta['target'],
            'section' => $meta['section'],
            'in_dashboard' => $meta['in_dashboard'],
        ]);
    }

    $groupedGrantedItems = $grantedItems->groupBy('group');
@endphp

@if ($groupedGrantedItems->isNotEmpty())
    @foreach ($groupedGrantedItems as $groupName => $items)
        <div class="pt-3">
            <div class="px-3 pb-1 token-sidebar-heading">{{ $groupName }}</div>
            <div class="space-y-1">
                @foreach ($items as $item)
                    @if ($item['in_dashboard'])
                        <x-sidebar-item :href="$item['target']" :target-section="$item['section']" :icon="$item['icon']" :label="$item['title']" />
                    @else
                        <x-sidebar-item :href="$item['target']" :icon="$item['icon']" :label="$item['title']" />
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach
@endif
