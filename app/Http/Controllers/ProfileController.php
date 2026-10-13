<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Update the authenticated user's own profile.
     *
     * Every signed-in user controls their own name, password, and photo.
     * Email is deliberately ignored here: it is the login identity and is only
     * changed by an administrator, and role is never user-editable.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'portal_bg' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'portal_bg_preset' => ['nullable', 'string', 'max:255'],
            'remove_portal_bg' => ['nullable', 'boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? $user->phone,
        ];

        if (filled($validated['password'] ?? null)) {
            $data['password'] = $validated['password'];
        }

        if ($request->hasFile('photo')) {
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

        $targetRoute = match (true) {
            $user->isAdmin() => 'dashboard',
            $user->isFinanceStaff() => 'finance.index',
            $user->isReceptionist() => 'receptionist.dashboard',
            default => 'client.portal',
        };

        return redirect()
            ->route($targetRoute)
            ->withFragment('profile')
            ->with('success', 'Your profile was updated successfully.');
    }
}
