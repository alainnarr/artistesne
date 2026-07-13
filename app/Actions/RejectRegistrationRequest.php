<?php

namespace App\Actions;

use App\Models\ArtistRegistrationRequest;
use App\Models\User;
use App\Notifications\RegistrationRejectedNotification;

/**
 * Rejects a registration request and notifies the applicant. Shared by the
 * Filament edit page and the list-table row action.
 */
class RejectRegistrationRequest
{
    public function __invoke(ArtistRegistrationRequest $request, User $reviewer, ?string $notes = null): void
    {
        $request->reject($reviewer, $notes);

        // The applicant has no account yet, so notify them on the fly.
        $applicant = new User(['email' => $request->email, 'name' => $request->artist_name]);
        $applicant->notify(new RegistrationRejectedNotification($request->artist_name, $notes));
    }
}
