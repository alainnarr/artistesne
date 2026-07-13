<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConfirmationController extends Controller
{
    /**
     * Signed route: artist confirms profile is up-to-date without changes.
     * Logs the artist in and marks them as confirmed.
     */
    public function confirm(Request $request): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403, 'Ce lien est invalide ou expiré.');

        $artist = Artist::where('confirmation_token', $request->token)->firstOrFail();

        $artist->update([
            'last_confirmed_at' => now(),
            'reminder_sent_at' => null,
            'confirmation_token' => null,
        ]);

        // Log in the associated user so they can access their portal.
        if ($artist->user) {
            auth()->login($artist->user, remember: true);
        }

        return redirect()->route('artist.dashboard')
            ->with('success', 'Votre profil a été confirmé. Merci !');
    }

    /**
     * Signed route: artist wants to update their profile.
     * Logs them in and redirects to the edit form.
     */
    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403, 'Ce lien est invalide ou expiré.');

        $artist = Artist::where('confirmation_token', $request->token)->firstOrFail();

        // Mark confirmed now; any change request will reflect the update intent.
        $artist->update([
            'last_confirmed_at' => now(),
            'reminder_sent_at' => null,
            'confirmation_token' => null,
        ]);

        if ($artist->user) {
            auth()->login($artist->user, remember: true);
        }

        return redirect()->route('artist.profile.edit')
            ->with('info', 'Vous pouvez maintenant mettre à jour votre profil.');
    }
}
