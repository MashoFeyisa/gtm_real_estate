<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Respond with either JSON or a redirect with a flash message.
     */
    protected function respond(Request $request, string $message, bool $success = true, int $status = 200): JsonResponse|RedirectResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], $status);
        }

        $user = $request->user();
        $targetRoute = match (true) {
            $user?->isAdmin() => 'dashboard',
            $user?->isFinanceStaff() => 'finance.index',
            $user?->isReceptionist() => 'receptionist.dashboard',
            default => 'client.portal',
        };

        return redirect()
            ->route($targetRoute)
            ->withFragment('profile')
            ->with($success ? 'success' : 'error', $message);
    }

    /**
     * Update the authenticated user's profile information.
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'portal_bg' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'portal_bg_preset' => ['nullable', 'string', 'max:255'],
            'remove_portal_bg' => ['nullable', 'boolean'],
        ]);

        $oldData = $user->only(['name', 'phone', 'photo_path']);

        $data = [
            'name' => trim($validated['name']),
            'phone' => filled($validated['phone'] ?? null) ? trim($validated['phone']) : null,
        ];

        if (filled($validated['password'] ?? null)) {
            $data['password'] = $validated['password'];
        }

        if ($request->boolean('remove_photo')) {
            if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
                Storage::disk('public')->delete($user->photo_path);
            }
            $data['photo_path'] = null;
        } elseif ($request->hasFile('photo')) {
            if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
                Storage::disk('public')->delete($user->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('user-photos', 'public');
        }

        if ($request->boolean('remove_portal_bg')) {
            if ($user->portal_bg_path && Storage::disk('public')->exists($user->portal_bg_path)) {
                Storage::disk('public')->delete($user->portal_bg_path);
            }
            $data['portal_bg_path'] = null;
        } elseif ($request->hasFile('portal_bg')) {
            if ($user->portal_bg_path && Storage::disk('public')->exists($user->portal_bg_path)) {
                Storage::disk('public')->delete($user->portal_bg_path);
            }
            $data['portal_bg_path'] = $request->file('portal_bg')->store('portal-backgrounds', 'public');
        } elseif ($request->filled('portal_bg_preset')) {
            $preset = $request->input('portal_bg_preset');
            $allowedPresets = [
                'images/luxury/hero-skyline.jpg',
                'images/luxury/luxury-towers.jpg',
                'images/luxury/central-plaza.jpg',
                'images/luxury/panoramic-park.jpg',
                'images/luxury/retail-boulevard.jpg',
            ];
            if (in_array($preset, $allowedPresets, true)) {
                if ($user->portal_bg_path && Storage::disk('public')->exists($user->portal_bg_path)) {
                    Storage::disk('public')->delete($user->portal_bg_path);
                }
                $data['portal_bg_path'] = $preset;
            }
        }

        $user->update($data);

        AuditLog::log(
            action: 'profile.updated',
            entity: $user,
            oldValue: $oldData,
            newValue: $user->only(['name', 'phone', 'photo_path']),
            reason: 'User updated personal profile details',
            user: $user,
            request: $request
        );

        return $this->respond($request, 'Profile details updated successfully.');
    }

    /**
     * Update security credentials: Password and 2FA/OTP toggle.
     */
    public function updateSecurity(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'two_factor_enabled' => ['nullable', 'boolean'],
        ]);

        $updates = [];

        if (filled($validated['password'] ?? null)) {
            if ($user->password && ! empty($validated['current_password'])) {
                if (! Hash::check($validated['current_password'], $user->password)) {
                    return $this->respond($request, 'The provided current password does not match our records.', false, 422);
                }
            }

            $updates['password'] = $validated['password'];
        }

        if ($request->has('two_factor_enabled')) {
            $updates['two_factor_enabled'] = $request->boolean('two_factor_enabled');
        }

        if ($updates !== []) {
            $user->update($updates);

            AuditLog::log(
                action: 'security.updated',
                entity: $user,
                oldValue: ['two_factor_enabled' => (bool) $user->getOriginal('two_factor_enabled')],
                newValue: ['two_factor_enabled' => (bool) $user->two_factor_enabled, 'password_changed' => isset($updates['password'])],
                reason: 'User modified security or two-factor settings',
                user: $user,
                request: $request
            );
        }

        return $this->respond($request, 'Security and authentication settings updated.');
    }

    /**
     * Update notification preferences and UI language.
     */
    public function updatePreferences(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'language' => ['nullable', 'string', 'in:en,am,om'],
            'notification_preferences' => ['nullable', 'array'],
            'notification_preferences.email' => ['nullable', 'boolean'],
            'notification_preferences.in_app' => ['nullable', 'boolean'],
            'notification_preferences.sms' => ['nullable', 'boolean'],
        ]);

        $updates = [];

        if (! empty($validated['language'])) {
            $updates['language'] = $validated['language'];
            session(['locale' => $validated['language']]);
            app()->setLocale($validated['language']);
        }

        if (isset($validated['notification_preferences'])) {
            $updates['notification_preferences'] = [
                'email' => (bool) ($validated['notification_preferences']['email'] ?? false),
                'in_app' => (bool) ($validated['notification_preferences']['in_app'] ?? false),
                'sms' => (bool) ($validated['notification_preferences']['sms'] ?? false),
            ];
        }

        if ($updates !== []) {
            $user->update($updates);

            AuditLog::log(
                action: 'preferences.updated',
                entity: $user,
                newValue: $updates,
                reason: 'User updated language and notification preferences',
                user: $user,
                request: $request
            );
        }

        return $this->respond($request, 'Preferences saved successfully.');
    }

    /**
     * Request account deactivation or deletion.
     */
    public function deactivate(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
            'action_type' => ['required', 'string', 'in:deactivate,delete'],
        ]);

        $isDelete = $validated['action_type'] === 'delete';

        $user->update([
            'deactivated_at' => now(),
            'deactivation_reason' => $validated['reason'],
            'status' => 'inactive',
        ]);

        AuditLog::log(
            action: $isDelete ? 'account.deletion_requested' : 'account.deactivated',
            entity: $user,
            oldValue: ['status' => 'active'],
            newValue: ['status' => 'inactive', 'reason' => $validated['reason']],
            reason: $validated['reason'],
            user: $user,
            request: $request
        );

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $msg = $isDelete
            ? 'Your account deletion request has been logged. Our administrators will review and finalize the removal.'
            : 'Your account has been deactivated. You may contact support to reactivate at any time.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'redirect' => route('login'),
            ]);
        }

        return redirect()->route('login')->with('success', $msg);
    }

    /**
     * Get recent login history for the authenticated user.
     */
    public function loginHistory(Request $request): JsonResponse
    {
        $user = $request->user();

        $history = AuditLog::query()
            ->where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('action', 'auth.login')
                    ->orWhere('action', 'like', 'auth.%')
                    ->orWhere('action', 'like', 'security.%');
            })
            ->latest()
            ->take(15)
            ->get(['id', 'action', 'ip_address', 'device', 'created_at']);

        return response()->json([
            'success' => true,
            'history' => $history->map(fn ($log) => [
                'action' => ucfirst(str_replace(['auth.', 'security.', '_'], ['', '', ' '], $log->action)),
                'ip_address' => $log->ip_address ?: 'Unknown IP',
                'device' => $log->device ?: 'Browser session',
                'timestamp' => $log->created_at?->diffForHumans() ?? 'Just now',
                'date' => $log->created_at?->format('M d, Y H:i:s') ?? '',
            ]),
        ]);
    }
}
