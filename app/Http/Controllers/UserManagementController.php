<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\UserPermissionOverride;
use App\Services\CommissionSplitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    /**
     * Store a newly created user in storage.
     */
    public function storeUser(Request $request)
    {
        $user = auth()->user();

        if (! $user || ! $user->canCreateUsers()) {
            abort(403, 'Admin access or user creation permission required.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'role' => ['required', 'string', Rule::in(config('personas.assignable_roles', []))],
            'permission_mode' => ['nullable', 'string', 'in:role_default,all,custom'],
            'granted_permissions' => ['nullable', 'array'],
            'granted_permissions.*' => ['integer', 'exists:permissions,id'],
            'denied_permissions' => ['nullable', 'array'],
            'denied_permissions.*' => ['integer', 'exists:permissions,id'],
            'permission_overrides' => ['nullable', 'array'],
            'permission_overrides.*' => ['required', 'string', 'in:default,allow,deny'],
            'phone' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'tier' => ['nullable', 'string', 'in:junior,senior,principal'],
            'split_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_verified' => ['nullable', 'boolean'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $createdUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        $roleModel = Role::where('slug', $validated['role'])->first();
        if ($roleModel) {
            $createdUser->roles()->sync([$roleModel->id]);
        }

        $this->applyUserPermissions($createdUser, $validated);

        if ($validated['role'] === 'agent') {
            $tier = $validated['tier'] ?? 'junior';
            $defaultSplit = app(CommissionSplitService::class)->getTierDetails($tier)['default_split'];

            $agent = Agent::query()->firstOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['name'],
                    'password' => $validated['password'],
                    'user_id' => $createdUser->id,
                    'phone' => $validated['phone'] ?? null,
                    'bio' => $validated['bio'] ?? null,
                    'tier' => $tier,
                    'split_percentage' => $validated['split_percentage'] ?? $defaultSplit,
                    'license_number' => $validated['license_number'] ?? null,
                    'license_verified' => $validated['license_verified'] ?? false,
                    'commission_rate' => $validated['commission_rate'] ?? 5,
                    'is_active' => $validated['is_active'] ?? true,
                    'photo_path' => $this->storeAgentPhotoUpload($request),
                ]
            );
            if (! $agent->user_id) {
                $agent->forceFill(['user_id' => $createdUser->id])->save();
            }
        }

        $message = 'User registered successfully.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'section' => 'users',
            ]);
        }

        return redirect()->to(route('dashboard', ['section' => 'users']))->with('success', $message);
    }

    /**
     * Update the specified user in storage.
     */
    public function updateUser(Request $request, User $user)
    {
        $currentUser = auth()->user();

        if (! $currentUser || ! $currentUser->canEditUsers()) {
            abort(403, 'Admin access or user edit permission required.');
        }

        if ($user->isAdmin() && ! $currentUser->isAdmin()) {
            abort(403, 'Only administrators can modify administrator accounts.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'role' => ['required', 'string', Rule::in(config('personas.assignable_roles', []))],
            'permission_mode' => ['nullable', 'string', 'in:role_default,all,custom'],
            'granted_permissions' => ['nullable', 'array'],
            'granted_permissions.*' => ['integer', 'exists:permissions,id'],
            'denied_permissions' => ['nullable', 'array'],
            'denied_permissions.*' => ['integer', 'exists:permissions,id'],
            'permission_overrides' => ['nullable', 'array'],
            'permission_overrides.*' => ['required', 'string', 'in:default,allow,deny'],
            'phone' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'tier' => ['nullable', 'string', 'in:junior,senior,principal'],
            'split_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_verified' => ['nullable', 'boolean'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (filled($validated['password'] ?? null)) {
            $userData['password'] = $validated['password'];
        }

        $user->update($userData);

        $roleModel = Role::where('slug', $validated['role'])->first();
        if ($roleModel) {
            $user->roles()->sync([$roleModel->id]);
        }

        $this->applyUserPermissions($user, $validated);

        if ($user->agentProfile) {
            $agentData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];
            if (filled($validated['password'] ?? null)) {
                $agentData['password'] = $validated['password'];
            }

            /*
             * Only touch agent profile fields that were actually submitted, so partial
             * updates (e.g. role/permission-only saves) never wipe existing agent data.
             */
            foreach (['phone', 'bio', 'license_number'] as $agentField) {
                if ($request->exists($agentField)) {
                    $agentData[$agentField] = $validated[$agentField] ?? null;
                }
            }

            if (filled($validated['tier'] ?? null)) {
                $agentData['tier'] = $validated['tier'];
            }

            if (filled($validated['commission_rate'] ?? null)) {
                $agentData['commission_rate'] = $validated['commission_rate'];
            }

            if ($request->exists('split_percentage')) {
                $tierForSplit = filled($validated['tier'] ?? null) ? $validated['tier'] : $user->agentProfile->tier;
                $agentData['split_percentage'] = $validated['split_percentage']
                    ?? app(CommissionSplitService::class)->getTierDetails($tierForSplit)['default_split'];
            }

            if ($request->exists('license_verified')) {
                $agentData['license_verified'] = $validated['license_verified'] ?? false;
            }

            if ($request->exists('is_active')) {
                $agentData['is_active'] = $validated['is_active'] ?? false;
            }

            if ($request->hasFile('photo')) {
                $agentData['photo_path'] = $this->storeAgentPhotoUpload($request);
            }

            $user->agentProfile->update($agentData);
        }

        $message = 'User updated successfully.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'section' => 'users',
            ]);
        }

        return redirect()->to(route('dashboard', ['section' => 'users']))->with('success', $message);
    }

    /**
     * Store an uploaded agent photo on the public disk.
     */
    protected function storeAgentPhotoUpload(Request $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        return $request->file('photo')->store('agent-photos', 'public');
    }

    /**
     * Apply permission configuration to the user based on selected mode.
     *
     * @param  array<string, mixed>  $validated
     */
    protected function applyUserPermissions(User $user, array $validated): void
    {
        $mode = $validated['permission_mode'] ?? null;

        // If explicitly set to role_default: wipe overrides so standard role permissions apply.
        if ($mode === 'role_default') {
            $user->permissionOverrides()->delete();
            $user->userPermissions()->delete();

            return;
        }

        // If mode is 'all': grant every single permission in the database.
        if ($mode === 'all') {
            $allPermissionIds = Permission::query()->pluck('id')->all();
            $this->assignExplicitPermissions($user, $allPermissionIds, []);

            return;
        }

        // If mode is 'custom' or granted_permissions array is supplied:
        if ($mode === 'custom' || isset($validated['granted_permissions'])) {
            $grantedIds = $validated['granted_permissions'] ?? [];
            $deniedIds = $validated['denied_permissions'] ?? [];
            $this->assignExplicitPermissions($user, $grantedIds, $deniedIds);

            return;
        }

        // Backward compatibility with legacy permission_overrides map
        if (isset($validated['permission_overrides'])) {
            $this->syncPermissionOverrides($user, $validated['permission_overrides']);
        }
    }

    /**
     * Assign explicit permissions to a user.
     *
     * @param  array<int, int>  $grantedIds
     * @param  array<int, int>  $deniedIds
     */
    protected function assignExplicitPermissions(User $user, array $grantedIds, array $deniedIds = []): void
    {
        $user->permissionOverrides()->delete();
        $user->userPermissions()->delete();

        $grantedIds = array_unique(array_map('intval', $grantedIds));
        $deniedIds = array_diff(array_unique(array_map('intval', $deniedIds)), $grantedIds);

        $now = now();
        $overrideRows = [];
        $userPermRows = [];

        foreach ($grantedIds as $pId) {
            $overrideRows[] = [
                'user_id' => $user->id,
                'permission_id' => $pId,
                'granted' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $userPermRows[] = [
                'user_id' => $user->id,
                'permission_id' => $pId,
                'granted' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach ($deniedIds as $pId) {
            $overrideRows[] = [
                'user_id' => $user->id,
                'permission_id' => $pId,
                'granted' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $userPermRows[] = [
                'user_id' => $user->id,
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
    }

    /**
     * Replace the user's per-account permission overrides with the
     * submitted three-state selection (default / allow / deny).
     *
     * @param  array<string, string>  $overrides
     */
    protected function syncPermissionOverrides(User $user, array $overrides): void
    {
        $validPermissionIds = Permission::query()->pluck('id')->flip();

        $rows = [];
        $userPermRows = [];
        $now = now();

        foreach ($overrides as $permissionId => $state) {
            if (! isset($validPermissionIds[(int) $permissionId])) {
                continue;
            }

            // 'default' means do not override; inherit role default!
            if ($state === 'default') {
                continue;
            }

            $isGranted = ($state === 'allow');

            $rows[] = [
                'user_id' => $user->id,
                'permission_id' => (int) $permissionId,
                'granted' => $isGranted,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $userPermRows[] = [
                'user_id' => $user->id,
                'permission_id' => (int) $permissionId,
                'granted' => $isGranted,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $user->permissionOverrides()->delete();
        $user->userPermissions()->delete();

        if ($rows !== []) {
            UserPermissionOverride::query()->insert($rows);
        }
        if ($userPermRows !== []) {
            UserPermission::query()->insert($userPermRows);
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function deleteUser(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $currentUser = auth()->user();

        if (! $currentUser || ! $currentUser->canDeleteUsers()) {
            abort(403, 'Admin access or user delete permission required.');
        }

        if ($user->isAdmin() && ! $currentUser->isAdmin()) {
            abort(403, 'Only administrators can delete administrator accounts.');
        }

        if ($user->id === $currentUser->id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'You cannot delete your own account.'], 422);
            }

            return redirect()->to(route('dashboard').'#users')->withErrors(['error' => 'You cannot delete your own account.']);
        }

        if ($user->agentProfile) {
            $user->agentProfile->delete();
        }

        $user->delete();

        $message = 'User deleted successfully.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'section' => 'users',
            ]);
        }

        return redirect()->to(route('dashboard').'#users')->with('success', $message);
    }

    /**
     * Impersonate the specified user (Forbidden by security boundary).
     */
    public function impersonate(Request $request, User $user)
    {
        abort(403, 'Impersonation is not permitted. Administrators cannot open or act inside other users\' dashboards.');
    }

    /**
     * Leave impersonation and restore original admin session.
     */
    public function leaveImpersonation(Request $request)
    {
        $adminId = session()->pull('impersonating_admin_id');

        if (! $adminId) {
            return redirect()->route('dashboard');
        }

        $admin = User::find($adminId);

        if ($admin) {
            auth()->login($admin);
        }

        return redirect()->to(route('dashboard').'#users')->with('success', 'Returned to your administrator account.');
    }
}
