<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\UserPermissionOverride;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleManagementController extends Controller
{
    protected function roleRedirect(string $message, Request $request, string $section = 'roles-permissions'): RedirectResponse|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'section' => $section,
            ]);
        }

        return redirect()->to(route('dashboard').'#'.$section)->with('success', $message);
    }

    /**
     * Create a new custom role.
     */
    public function storeRole(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canManageRoles()) {
            abort(403, 'Admin access or role management permission required.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = Str::slug($validated['name'], '_');
        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        AuditLog::log(
            action: 'role.created',
            entityType: 'Role',
            entityId: $role->id,
            oldValue: null,
            newValue: $role->toArray(),
            reason: $request->input('reason', 'Role created by administrator'),
            user: $user,
            request: $request
        );

        return $this->roleRedirect("Role '{$role->name}' created successfully.", $request);
    }

    /**
     * Update an existing role.
     */
    public function updateRole(Request $request, Role $role): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canManageRoles()) {
            abort(403, 'Admin access or role management permission required.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role->id)],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $oldValue = $role->only(['name', 'description']);
        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        AuditLog::log(
            action: 'role.updated',
            entityType: 'Role',
            entityId: $role->id,
            oldValue: $oldValue,
            newValue: $role->only(['name', 'description']),
            reason: $request->input('reason', 'Role updated by administrator'),
            user: $user,
            request: $request
        );

        return $this->roleRedirect("Role '{$role->name}' updated successfully.", $request);
    }

    /**
     * Delete an existing custom role.
     */
    public function destroyRole(Request $request, Role $role): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canManageRoles()) {
            abort(403, 'Admin access or role management permission required.');
        }

        if ($role->is_system) {
            return back()->withErrors(['error' => "System role '{$role->name}' cannot be deleted."]);
        }

        $oldValue = $role->toArray();
        $roleName = $role->name;

        $role->rolePermissions()->delete();
        $role->users()->detach();
        $role->delete();

        AuditLog::log(
            action: 'role.deleted',
            entityType: 'Role',
            entityId: $role->id,
            oldValue: $oldValue,
            newValue: null,
            reason: $request->input('reason', 'Role deleted by administrator'),
            user: $user,
            request: $request
        );

        return $this->roleRedirect("Role '{$roleName}' deleted successfully.", $request);
    }

    /**
     * Create a new granular permission (format: resource.action).
     */
    public function storePermission(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canManagePermissions()) {
            abort(403, 'Admin access or permission management required.');
        }

        $validated = $request->validate([
            'key' => [
                'required',
                'string',
                'max:100',
                'unique:permissions,key',
                'regex:/^[a-z0-9_-]+\.[a-z0-9_-]+$/i',
            ],
            'label' => ['required', 'string', 'max:150'],
            'group' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'menu_label' => ['nullable', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:50'],
            'route' => ['nullable', 'string', 'max:150'],
        ]);

        $normalizedKey = Str::lower(trim($validated['key']));
        $group = $validated['group'] ?: explode('.', $normalizedKey)[0];

        $permission = Permission::create([
            'key' => $normalizedKey,
            'label' => trim($validated['label']),
            'menu_label' => ! empty($validated['menu_label']) ? trim($validated['menu_label']) : null,
            'icon' => ! empty($validated['icon']) ? trim($validated['icon']) : null,
            'route' => ! empty($validated['route']) ? trim($validated['route']) : null,
            'group' => Str::lower(trim($group)),
            'description' => $validated['description'] ?? null,
            'sort_order' => (int) (Permission::max('sort_order') ?? 0) + 1,
        ]);

        AuditLog::log(
            action: 'permission.created',
            entityType: 'Permission',
            entityId: $permission->id,
            oldValue: null,
            newValue: $permission->toArray(),
            reason: $request->input('reason', 'Permission created by administrator'),
            user: $user,
            request: $request
        );

        return $this->roleRedirect("Permission '{$permission->key}' created successfully.", $request);
    }

    /**
     * Delete a permission from the catalog.
     */
    public function destroyPermission(Request $request, Permission $permission): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canManagePermissions()) {
            abort(403, 'Admin access or permission management required.');
        }

        $oldValue = $permission->toArray();
        $permKey = $permission->key;

        $permission->roleGrants()->delete();
        $permission->userOverrides()->delete();
        UserPermission::where('permission_id', $permission->id)->delete();
        $permission->delete();

        AuditLog::log(
            action: 'permission.deleted',
            entityType: 'Permission',
            entityId: $permission->id,
            oldValue: $oldValue,
            newValue: null,
            reason: $request->input('reason', 'Permission deleted by administrator'),
            user: $user,
            request: $request
        );

        return $this->roleRedirect("Permission '{$permKey}' deleted successfully.", $request);
    }

    /**
     * Save the admin's permission checklist for a persona's role or custom role.
     */
    public function updateRolePermissions(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->canManageRoles()) {
            abort(403, 'Admin access or role management permission required.');
        }

        $manageablePersonas = collect(config('personas.personas', []))
            ->filter(fn (array $persona) => filled($persona['role'] ?? null));

        $roleSlugs = Role::pluck('slug')->all();
        $allowedKeys = array_unique(array_merge($manageablePersonas->pluck('key')->all(), $roleSlugs));

        $validated = $request->validate([
            'persona' => ['required', 'string', Rule::in($allowedKeys)],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $personaKey = $validated['persona'];
        $persona = $manageablePersonas->firstWhere('key', $personaKey);
        $roleRecord = Role::where('slug', $personaKey)->first();

        $roleSlug = $persona ? $persona['role'] : ($roleRecord ? $roleRecord->slug : $personaKey);
        $roleId = $roleRecord?->id;

        $newPermissionIds = collect($validated['permission_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $oldPermissionIds = RolePermission::query()
            ->where('role', $roleSlug)
            ->pluck('permission_id')
            ->all();

        RolePermission::query()
            ->where('role', $roleSlug)
            ->whereNotIn('permission_id', $newPermissionIds)
            ->delete();

        foreach ($newPermissionIds as $permissionId) {
            RolePermission::query()->updateOrCreate(
                ['role' => $roleSlug, 'permission_id' => $permissionId],
                ['role_id' => $roleId]
            );
        }

        AuditLog::log(
            action: 'role.permissions_updated',
            entityType: 'RolePermission',
            entityId: $roleSlug,
            oldValue: ['permission_ids' => $oldPermissionIds],
            newValue: ['permission_ids' => $newPermissionIds->all()],
            reason: $validated['reason'] ?? 'Permissions modified by administrator',
            user: $user,
            request: $request
        );

        $label = $persona ? $persona['label'] : ($roleRecord ? $roleRecord->name : $roleSlug);

        return $this->roleRedirect("Permissions for '{$label}' updated successfully.", $request);
    }

    /**
     * Assign per-user permissions.
     */
    public function updateUserPermissions(Request $request, User $targetUser): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canManagePermissions()) {
            abort(403, 'Admin access or user permission management required.');
        }

        if ($targetUser->isAdmin() && ! $user->isAdmin()) {
            abort(403, 'Only administrators can modify administrator permissions.');
        }

        $validated = $request->validate([
            'permission_mode' => ['nullable', 'string', 'in:role_default,all,custom'],
            'granted_permission_ids' => ['nullable', 'array'],
            'granted_permission_ids.*' => ['integer', 'exists:permissions,id'],
            'granted_permissions' => ['nullable', 'array'],
            'granted_permissions.*' => ['integer', 'exists:permissions,id'],
            'denied_permission_ids' => ['nullable', 'array'],
            'denied_permission_ids.*' => ['integer', 'exists:permissions,id'],
            'denied_permissions' => ['nullable', 'array'],
            'denied_permissions.*' => ['integer', 'exists:permissions,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $mode = $validated['permission_mode'] ?? 'custom';
        $oldPermissions = UserPermission::where('user_id', $targetUser->id)->get()->toArray();

        UserPermission::where('user_id', $targetUser->id)->delete();
        UserPermissionOverride::where('user_id', $targetUser->id)->delete();

        if ($mode === 'role_default') {
            AuditLog::log(
                action: 'user.permissions_reset',
                entityType: 'User',
                entityId: $targetUser->id,
                oldValue: $oldPermissions,
                newValue: ['mode' => 'role_default'],
                reason: $validated['reason'] ?? 'Permissions reset to role defaults by admin',
                user: $user,
                request: $request
            );

            return $this->roleRedirect("Permissions for '{$targetUser->name}' reset to role defaults.", $request, 'users');
        }

        if ($mode === 'all') {
            $grantedIds = Permission::query()->pluck('id')->all();
            $deniedIds = [];
        } else {
            $rawGranted = $validated['granted_permission_ids'] ?? $validated['granted_permissions'] ?? [];
            $rawDenied = $validated['denied_permission_ids'] ?? $validated['denied_permissions'] ?? [];
            $grantedIds = array_unique(array_map('intval', $rawGranted));
            $deniedIds = array_diff(array_unique(array_map('intval', $rawDenied)), $grantedIds);
        }

        $now = now();
        $overrideRows = [];
        $userPermRows = [];

        foreach ($grantedIds as $pId) {
            $overrideRows[] = [
                'user_id' => $targetUser->id,
                'permission_id' => $pId,
                'granted' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $userPermRows[] = [
                'user_id' => $targetUser->id,
                'permission_id' => $pId,
                'granted' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach ($deniedIds as $pId) {
            $overrideRows[] = [
                'user_id' => $targetUser->id,
                'permission_id' => $pId,
                'granted' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $userPermRows[] = [
                'user_id' => $targetUser->id,
                'permission_id' => $pId,
                'granted' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($overrideRows !== []) {
            UserPermissionOverride::query()->insert($overrideRows);
        }
        if ($userPermRows !== []) {
            UserPermission::query()->insert($userPermRows);
        }

        AuditLog::log(
            action: 'user.permissions_assigned',
            entityType: 'User',
            entityId: $targetUser->id,
            oldValue: $oldPermissions,
            newValue: [
                'mode' => $mode,
                'granted_count' => count($grantedIds),
                'granted' => $grantedIds,
                'denied' => $deniedIds,
            ],
            reason: $validated['reason'] ?? 'User permissions updated by admin',
            user: $user,
            request: $request
        );

        $count = count($grantedIds);

        return $this->roleRedirect("Updated permissions for '{$targetUser->name}': {$count} permissions granted.", $request, 'users');
    }

    /**
     * Grant or revoke dashboard access for a user.
     */
    public function toggleDashboardAccess(Request $request, User $targetUser): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user || (! $user->canManagePermissions() && ! $user->canEditUsers())) {
            abort(403, 'Admin access or user management permission required.');
        }

        $oldAccess = (bool) $targetUser->dashboard_access_enabled;
        $targetUser->dashboard_access_enabled = ! $oldAccess;
        $targetUser->save();

        AuditLog::log(
            action: 'dashboard.access_toggled',
            entityType: 'User',
            entityId: $targetUser->id,
            oldValue: ['dashboard_access_enabled' => $oldAccess],
            newValue: ['dashboard_access_enabled' => $targetUser->dashboard_access_enabled],
            reason: $request->input('reason', 'Admin toggled dashboard access'),
            user: $user,
            request: $request
        );

        $state = $targetUser->dashboard_access_enabled ? 'granted' : 'revoked';

        return $this->roleRedirect("Dashboard access {$state} for '{$targetUser->name}'.", $request, 'users');
    }

    /**
     * Enable or disable image/media access for a user.
     */
    public function toggleMediaAccess(Request $request, User $targetUser): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user || (! $user->canManagePermissions() && ! $user->canEditUsers())) {
            abort(403, 'Admin access or user management permission required.');
        }

        $oldAccess = (bool) $targetUser->media_access_enabled;
        $targetUser->media_access_enabled = ! $oldAccess;
        $targetUser->save();

        AuditLog::log(
            action: 'media.access_toggled',
            entityType: 'User',
            entityId: $targetUser->id,
            oldValue: ['media_access_enabled' => $oldAccess],
            newValue: ['media_access_enabled' => $targetUser->media_access_enabled],
            reason: $request->input('reason', 'Admin toggled media access'),
            user: $user,
            request: $request
        );

        $state = $targetUser->media_access_enabled ? 'enabled' : 'disabled';

        return $this->roleRedirect("Media/Image access {$state} for '{$targetUser->name}'.", $request, 'users');
    }

    /**
     * Approve or suspend a user.
     */
    public function updateUserStatus(Request $request, User $targetUser): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canManageUserStatus()) {
            abort(403, 'Admin access or user status management permission required.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:active,suspended,pending'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $oldStatus = $targetUser->status;
        $targetUser->status = $validated['status'];
        $targetUser->save();

        AuditLog::log(
            action: 'user.status_updated',
            entityType: 'User',
            entityId: $targetUser->id,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => $targetUser->status],
            reason: $validated['reason'] ?? "Admin changed status to {$validated['status']}",
            user: $user,
            request: $request
        );

        return $this->roleRedirect("Account status for '{$targetUser->name}' updated to {$targetUser->status}.", $request, 'users');
    }
}
