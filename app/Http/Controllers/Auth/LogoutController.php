<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Logs a user out of the "web" guard (artist portal).
 *
 * Replaces Fortify's `AuthenticatedSessionController@destroy` — this app
 * removed Fortify entirely since none of its features (password login,
 * password reset, email verification, two-factor) are used: artists
 * authenticate via magic link, admins via AD FS through the Filament panel
 * (which has its own, separate logout flow).
 */
class LogoutController
{
    public function __invoke(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('public.home');
    }
}
