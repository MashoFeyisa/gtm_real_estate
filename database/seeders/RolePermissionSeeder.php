<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the persona/permission catalog from config/personas.php,
     * seed relational Role models, default role grants, and sync user roles.
     */
    public function run(): void
    {
        $sortOrder = 0;

        foreach (config('personas.personas', []) as $persona) {
            foreach ($persona['permissions'] ?? [] as $key => $label) {
                // First persona to mention a key owns its catalog group.
                Permission::query()->firstOrCreate(
                    ['key' => $key],
                    [
                        'label' => $label,
                        'group' => $persona['key'],
                        'sort_order' => ++$sortOrder,
                    ]
                );
            }
        }

        // Database-driven user-facing menu mapping (permission -> menu_label, icon, route)
        $menuMapping = [
            'properties.create' => ['menu_label' => 'Post Property', 'icon' => '🏠', 'route' => 'user.properties.create'],
            'property.create' => ['menu_label' => 'Post Property', 'icon' => '🏠', 'route' => 'user.properties.create'],
            'bookings.view' => ['menu_label' => 'My Bookings', 'icon' => '🧳', 'route' => 'user.bookings'],
            'booking.view' => ['menu_label' => 'My Bookings', 'icon' => '🧳', 'route' => 'user.bookings'],
            'offers.create' => ['menu_label' => 'My Offers', 'icon' => '🤝', 'route' => 'user.offers'],
            'offer.create' => ['menu_label' => 'My Offers', 'icon' => '🤝', 'route' => 'user.offers'],
            'offers.view' => ['menu_label' => 'My Offers', 'icon' => '🤝', 'route' => 'user.offers'],
            'offer.view' => ['menu_label' => 'My Offers', 'icon' => '🤝', 'route' => 'user.offers'],
            'viewings.view' => ['menu_label' => 'My Viewings', 'icon' => '👁️', 'route' => 'user.viewings'],
            'viewing.view' => ['menu_label' => 'My Viewings', 'icon' => '👁️', 'route' => 'user.viewings'],
            'viewings.create' => ['menu_label' => 'My Viewings', 'icon' => '👁️', 'route' => 'user.viewings'],
            'viewing.create' => ['menu_label' => 'My Viewings', 'icon' => '👁️', 'route' => 'user.viewings'],
            'leases.view' => ['menu_label' => 'My Leases & Rent', 'icon' => '🔑', 'route' => 'user.leases'],
            'lease.view' => ['menu_label' => 'My Leases & Rent', 'icon' => '🔑', 'route' => 'user.leases'],
            'rent.pay' => ['menu_label' => 'My Leases & Rent', 'icon' => '🔑', 'route' => 'user.leases'],
            'maintenance.create' => ['menu_label' => 'Maintenance Requests', 'icon' => '🛠️', 'route' => 'user.maintenance'],
            'maintenance.view' => ['menu_label' => 'Maintenance Requests', 'icon' => '🛠️', 'route' => 'user.maintenance'],
            'reviews.create' => ['menu_label' => 'My Reviews', 'icon' => '⭐', 'route' => 'user.reviews'],
            'reviews.view' => ['menu_label' => 'My Reviews', 'icon' => '⭐', 'route' => 'user.reviews'],
            'inquiries.view' => ['menu_label' => 'My Inquiries', 'icon' => '💬', 'route' => 'user.inquiries'],
            'inquiries.create' => ['menu_label' => 'My Inquiries', 'icon' => '💬', 'route' => 'user.inquiries'],
        ];

        foreach ($menuMapping as $permKey => $meta) {
            $perm = Permission::query()->firstOrNew(['key' => $permKey]);
            if (! $perm->exists) {
                $perm->label = $meta['menu_label'];
                $perm->group = explode('.', $permKey)[0];
                $perm->sort_order = ++$sortOrder;
            }
            $perm->menu_label = $meta['menu_label'];
            $perm->icon = $meta['icon'];
            $perm->route = $meta['route'];
            $perm->save();
        }

        $permissionIds = Permission::query()->pluck('id', 'key');

        // 1. Seed Role records for all platform personas
        foreach (config('personas.personas', []) as $persona) {
            $roleSlug = $persona['role'] ?? null;
            if (! $roleSlug) {
                continue;
            }

            Role::query()->firstOrCreate(
                ['slug' => $roleSlug],
                [
                    'name' => $persona['label'] ?? ucfirst(str_replace('_', ' ', $roleSlug)),
                    'description' => $persona['description'] ?? null,
                    'is_system' => true,
                ]
            );
        }

        // 2. Also ensure any assignable role has a Role entry
        foreach (config('personas.assignable_roles', []) as $assignableRole) {
            Role::query()->firstOrCreate(
                ['slug' => $assignableRole],
                [
                    'name' => ucfirst(str_replace('_', ' ', $assignableRole)),
                    'description' => 'System role for '.ucfirst(str_replace('_', ' ', $assignableRole)),
                    'is_system' => true,
                ]
            );
        }

        $roleModelsBySlug = Role::query()->pluck('id', 'slug');

        // 3. Seed RolePermission grants
        foreach (config('personas.personas', []) as $persona) {
            $permissionKeys = array_keys($persona['permissions'] ?? []);
            $roleTargets = array_values(array_unique(array_filter(array_merge(
                [$persona['role'] ?? null],
                $persona['legacy_roles'] ?? [],
            ))));

            foreach ($roleTargets as $roleSlug) {
                $roleId = $roleModelsBySlug->get($roleSlug);

                foreach ($permissionKeys as $key) {
                    if (! isset($permissionIds[$key])) {
                        continue;
                    }

                    RolePermission::query()->firstOrCreate([
                        'role' => $roleSlug,
                        'permission_id' => $permissionIds[$key],
                    ], [
                        'role_id' => $roleId,
                    ]);

                    // Backfill role_id if it was null previously
                    if ($roleId) {
                        RolePermission::query()
                            ->where('role', $roleSlug)
                            ->where('permission_id', $permissionIds[$key])
                            ->whereNull('role_id')
                            ->update(['role_id' => $roleId]);
                    }
                }
            }
        }

        // 4. Attach relational user_roles for every user with a matching role slug
        foreach (Role::all() as $role) {
            $matchingUsers = User::where('role', $role->slug)->get();
            foreach ($matchingUsers as $user) {
                if (! $user->roles()->where('roles.id', $role->id)->exists()) {
                    $user->roles()->attach($role->id);
                }
            }
        }
    }
}
