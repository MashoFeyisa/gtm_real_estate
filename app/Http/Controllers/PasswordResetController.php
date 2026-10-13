<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Show the forgot-password form.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->intended($request->user()->dashboard_url);
        }

        return view('auth.forgot-password');
    }

    /**
     * Send the password reset link to the given account email.
     */
    public function email(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::broker()->sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->withErrors(['email' => __($status)]);
        }

        return back()->with('status', __($status));
    }

    /**
     * Show the form that consumes the emailed reset token.
     */
    public function reset(Request $request, string $token): View|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->intended($request->user()->dashboard_url);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Validate the token and persist the new password, then sign the user in.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $resetUser = null;

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Authenticatable $user, string $password) use (&$resetUser): void {
                $user->forceFill(['password' => Hash::make($password)])->save();

                $resetUser = $user;
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        auth()->login($resetUser);

        $request->session()->regenerate();
        $request->session()->put('last_activity_time', time());

        return redirect()->intended($resetUser->dashboard_url)
            ->with('success', 'Your password has been reset successfully. You are now signed in.');
    }
}
