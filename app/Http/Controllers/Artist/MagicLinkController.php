<?php

namespace App\Http\Controllers\Artist;

use App\Database\Models\User;
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

        // Single-use: the token embedded in the URL must match the one most
        // recently issued for this user and not have been consumed yet.
        $token = (string) $request->query('token');

        if ($token === '' || blank($user->magic_link_token) || ! hash_equals((string) $user->magic_link_token, $token)) {
            return redirect()->route('artist.login')
                ->with('error', 'Ce lien de connexion a déjà été utilisé ou n\'est plus valide. Demandez-en un nouveau ci-dessous.');
        }

        $user->forceFill(['magic_link_token' => null])->save();

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route('artist.dashboard');
    }
}
