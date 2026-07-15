<?php

declare(strict_types=1);

namespace App\Actions;

use App\Database\Models\Artist as NewArtist;
use App\Database\Models\Registration;
use App\Database\Models\User;
use App\Enums\ArtistShowContact;
use App\Enums\ArtistStatus;
use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Notifications\MagicLinkNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Approves a new-style Registration: provisions the User + new Artist profile,
 * marks the registration approved and sends welcome + magic-link notifications.
 */
class ApproveRegistration
{
    public function __invoke(Registration $registration, User $reviewer, ?string $notes = null): User
    {
        [$user] = DB::transaction(function () use ($registration, $reviewer, $notes) {
            $user = User::firstOrCreate(
                ['email' => $registration->email],
                [
                    'name' => $registration->artist_name,
                    'role' => UserRole::Artist,
                    'password' => null,
                ],
            );

            $slug = $this->uniqueSlug($registration->artist_name);

            NewArtist::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'registration_id' => $registration->id,
                    'slug' => $slug,
                    'artist_name' => $registration->artist_name,
                    'email' => $registration->email,
                    'phone' => $registration->phone,
                    'city' => $registration->residence_location,
                    'discipline_main_id' => $registration->discipline_main,
                    'enum_status' => ArtistStatus::Draft->value,
                    'enum_show_contact' => ArtistShowContact::HIDE->value,
                ],
            );

            $registration->update([
                'enum_status' => RegistrationStatus::APPROVED,
                'reviewed_at' => now(),
                'reviewed_by' => $reviewer->id,
                'review_notes' => $notes,
            ]);

            return [$user];
        });

        // A single email confirms the decision AND provides the magic link
        // (MagicLinkNotification already includes the approval message when
        // afterApproval is true) — sending a separate RegistrationApprovedNotification
        // on top of this resulted in artists receiving two emails.
        // last_magic_link_sent_at is stamped BEFORE notify(): MagicLinkNotification's
        // toMail() persists a fresh magic_link_token on $user via its own save(), and
        // saving this stale in-memory $user afterwards would silently overwrite
        // (clobber) that token back to its previous value.
        $user->forceFill(['last_magic_link_sent_at' => now()])->save();
        $user->notify(new MagicLinkNotification(afterApproval: true));

        return $user;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (NewArtist::where('slug', $slug)->exists()) {
            $slug = $base.'-'.++$i;
        }

        return $slug;
    }
}
