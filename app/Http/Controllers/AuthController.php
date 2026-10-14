<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AuditLog;
use App\Models\Property;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm(Request $request): View
    {
        $this->rememberIntendedRedirect($request);

        $agentsCount = Agent::count();
        $listingsCount = Property::where('status', 'published')->count();
        if ($listingsCount === 0) {
            $listingsCount = Property::count();
        }

        return view('auth.login', compact('agentsCount', 'listingsCount'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);

            $request->session()->regenerate();
            $request->session()->put('last_activity_time', time());

            $user = Auth::user();

            if ($user->deactivated_at || $user->status === 'suspended') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'This account has been deactivated or suspended. Please contact support.',
                ])->onlyInput('email');
            }

            if ($user->agentProfile && ! $user->agentProfile->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your agent account has been deactivated. Please contact an administrator.',
                ])->onlyInput('email');
            }

            AuditLog::log(
                action: 'auth.login',
                entity: $user,
                reason: 'User logged in successfully',
                user: $user,
                request: $request
            );

            // Each role lands on its own portal: admin dashboard, company, finance,
            // receptionist, agent portal, shared persona portal, or client portal.
            $intended = $request->session()->get('url.intended');
            if ($intended) {
                $intendedPath = parse_url($intended, PHP_URL_PATH);
                if ($intendedPath === '/dashboard' && ! $user->isAdmin() && ! $user->canManagePermissions()) {
                    $request->session()->forget('url.intended');
                }
            }

            return redirect()->intended($user->dashboard_url);
        }

        RateLimiter::hit($throttleKey);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Keep session alive when user is actively interacting with the web application.
     */
    public function keepAlive(Request $request)
    {
        if (Auth::check()) {
            $request->session()->put('last_activity_time', time());

            return response()->json([
                'status' => 'active',
                'last_activity_time' => time(),
            ]);
        }

        return response()->json([
            'status' => 'unauthenticated',
        ], 401);
    }

    /**
     * Show the client registration form.
     */
    public function showRegisterForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to(Auth::user()->dashboard_url);
        }

        $this->rememberIntendedRedirect($request);

        return view('auth.register');
    }

    private function rememberIntendedRedirect(Request $request): void
    {
        $destination = $request->query('redirect');
        if (! is_string($destination) || $destination === '') {
            return;
        }

        $destinationHost = parse_url($destination, PHP_URL_HOST);
        $destinationScheme = parse_url($destination, PHP_URL_SCHEME);
        $destinationPort = parse_url($destination, PHP_URL_PORT);
        $destinationPath = parse_url($destination, PHP_URL_PATH);

        if ($destinationHost !== null && strtolower($destinationHost) !== strtolower($request->getHost())) {
            return;
        }

        if ($destinationScheme !== null && $destinationScheme !== $request->getScheme()) {
            return;
        }

        if ($destinationPort !== null && $destinationPort !== $request->getPort()) {
            return;
        }

        if (! is_string($destinationPath) || ! str_starts_with($destinationPath, '/') || str_starts_with($destinationPath, '//')) {
            return;
        }

        $query = parse_url($destination, PHP_URL_QUERY);
        $fragment = parse_url($destination, PHP_URL_FRAGMENT);
        $intended = $destinationPath.($query !== null ? '?'.$query : '').($fragment !== null ? '#'.$fragment : '');

        $request->session()->put('url.intended', $intended);
    }

    /**
     * Handle a client registration request.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'client',
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('last_activity_time', time());

        return redirect()->intended(route('client.portal'));
    }
}
