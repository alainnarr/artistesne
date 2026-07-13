<?php

namespace App\Http\Controllers\Artist;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MagicLinkController
{
    /**
     * Consume a signed magic link URL and log the user in.
     */
    public function consume(Request $request, User $user): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('artist.login')
                ->with('error', 'Ce lien de connexion est expiré ou invalide. Demandez-en un nouveau ci-dessous.');
        }

        if (! $user->isArtist()) {
            return redirect()->route('artist.login')
                ->with('error', 'Ce lien ne correspond pas à un compte artiste valide.');
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route('artist.dashboard');
    }
}
