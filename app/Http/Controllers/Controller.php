<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class Controller
{
    use AuthorizesRequests;

    protected function redirectGuestToSignup(Request $request, string $returnUrl, string $message): RedirectResponse
    {
        $request->session()->put('url.intended', $returnUrl);

        return redirect()->route('register')->with('signup_prompt', $message);
    }
}
