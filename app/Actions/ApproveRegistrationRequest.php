<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\Artist;
use App\Models\ArtistRegistrationRequest;
use App\Models\User;
use App\Notifications\MagicLinkNotification;
use App\Notifications\RegistrationApprovedNotification;
use Illuminate\Support\Str;

/**
 * Approves a registration request: provisions the artist User + Artist profile,
 * marks the request approved and sends the welcome + magic-link notifications.
 *
 * Shared by the Filament edit page and the list-table row action so the
 * provisioning logic lives in a single place.
 */
class ApproveRegistrationRequest
{
    public function __invoke(ArtistRegistrationRequest $request, User $reviewer, ?string $notes = null): User
    {
        $user = User::firstOrCreate(
            ['email' => $request->email],
            [
                'name' => $request->artist_name,
                'role' => UserRole::Artist,
                'password' => null,
            ],
        );

        Artist::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $request->artist_name,
                'slug' => $this->uniqueSlug($request->artist_name),
                'discipline' => $request->main_domain,
                'email' => $request->email,
                'phone' => $request->phone,
                'city' => $request->residence_location,
                'biography' => '',
                'links' => [],
                'display_contact_button' => (bool) $request->display_contact_button,
            ],
        );

        $request->approve($reviewer, $notes);

        $user->notify(new RegistrationApprovedNotification($request->artist_name));
        $user->notify(new MagicLinkNotification);
        $user->forceFill(['last_magic_link_sent_at' => now()])->save();

        return $user;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Artist::where('slug', $slug)->exists()) {
            $slug = $base.'-'.++$i;
        }

        return $slug;
    }
}
