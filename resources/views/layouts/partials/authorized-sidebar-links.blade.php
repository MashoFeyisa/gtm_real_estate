@php
    $user = auth()->user();
    if (! $user) {
        return;
    }

    // Admins manage from the central Admin Desk and do not have the user Granted Access block
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
        'buyer' => ['buyer.dashboard', 'user.offers', 'user.viewings'],
        'host' => ['host.dashboard', 'user.bookings'],
        'agent' => ['agent.portal', 'user.properties.create', 'user.offers', 'user.viewings', 'user.leases', 'user.inquiries', 'user.reviews'],
        'tenant', 'renter' => ['tenant.dashboard', 'user.leases'],
        'seller', 'landlord', 'property_owner' => ['seller.dashboard', 'user.properties.create', 'user.offers', 'user.inquiries'],
        default => [],
    };

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
        $grantedItems->push([
            'key' => $permission->key,
            'title' => $permission->menu_label ?: $permission->label,
            'icon' => $permission->icon ?: '✨',
            'route' => $permission->route,
        ]);
    }
@endphp

@if ($grantedItems->isNotEmpty())
    <div class="pt-4 mt-4 border-t border-white/10" id="authorized-granted-sidebar-section">
        <div class="px-3 pb-2 flex items-center justify-between">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-[#d4af37]">Granted Access</p>
            <span class="rounded-full bg-[#d4af37]/20 text-[#d4af37] px-2 py-0.5 text-[9px] font-black border border-[#d4af37]/30">
                {{ $grantedItems->count() }}
            </span>
        </div>
        <div class="space-y-1">
            @foreach ($grantedItems as $item)
                <a href="{{ route($item['route']) }}" class="authorized-sidebar-link flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-[#dfeee4] transition hover:bg-white/10 hover:text-white group">
                    <span class="flex items-center gap-2 truncate">
                        <span>{{ $item['icon'] }}</span>
                        <span class="truncate">{{ $item['title'] }}</span>
                    </span>
                    <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-1.5 py-0.5 text-[9px] font-bold text-[#f8e7b9] shrink-0 opacity-80 group-hover:opacity-100">
                        &rarr;
                    </span>
                </a>
            @endforeach
        </div>
    </div>
@endif
