<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
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

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->agentProfile && ! $user->agentProfile->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your agent account has been deactivated. Please contact an administrator.',
                ])->onlyInput('email');
            }

            $destination = match (true) {
                $user->isAdmin() => '/dashboard',
                (bool) $user->agentProfile => '/agent-portal',
                default => '/app',
            };

            return redirect()->intended($destination);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
