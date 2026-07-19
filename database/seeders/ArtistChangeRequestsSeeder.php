<?php

namespace Database\Seeders;

use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\User;
use App\Enums\ArtistChangeRequestStatus;
use App\Enums\ArtistStatus;
use App\Services\ArtistChangeRequestsService;
use Illuminate\Database\Seeder;

class ArtistChangeRequestsSeeder extends Seeder
{
    public function run(): void
    {
        $artistChangeRequestsService = app(ArtistChangeRequestsService::class);

        $admin = User::query()->where('enum_role', 'admin')->first();

        if (! $admin) {
            $this->command->error('No admin user found. Run UsersSeeder first.');
            return;
        }

        $artists = Artist::query()->inRandomOrder()->get();

        if ($artists->isEmpty()) {
            $this->command->warn('No artists found.');
            return;
        }

        $artistsWithRequests = $artists->random(min(25, $artists->count()));

        /* Pending requests */
        $pendingArtists = $artistsWithRequests->random(min(10, $artistsWithRequests->count()));
        foreach ($pendingArtists as $artist) {
            ArtistChangeRequest::factory()->state(['artist_id' => $artist->id])->create();
        }

        /* Approved requests */
        $remaining = $artistsWithRequests->whereNotIn('id', $pendingArtists->pluck('id'));

        $approvedArtists = $remaining->random(min(10, $remaining->count()));
        foreach ($approvedArtists as $artist) {
            if (!$this->canCreateChangeRequest($artist)) {
                continue;
            }
            $this->removeChangesRequested($artist);

            $factory = ArtistChangeRequest::factory()->approved($admin);

            $type = fake()->randomElement([
                'onlyBiography',
                'onlyActivities',
                'onlyKeywords',
                'onlyLinks',
                'onlySecondaryDiscipline',
                'onlyArtistName',
                'withNewImage',
                'multipleChanges',
            ]);

            /* Draft artists need an image to become published. */
            if ($artist->enum_status === ArtistStatus::DRAFT) {
                $type = fake()->randomElement(['withNewImage', 'multipleChanges']);
            }

            $changeRequest = $factory->{$type}()->create(['artist_id' => $artist->id]);

            $artistChangeRequestsService->changeStatus(
                $changeRequest,
                ArtistChangeRequestStatus::APPROVED,
                'Approved for demo data.'
            );
        }

        /* Some rejected requests */
        $availableRejectedArtists = $artistsWithRequests
            ->whereNotIn('id', $pendingArtists->pluck('id'))
            ->whereNotIn('id', $approvedArtists->pluck('id'));

        $rejectedArtists = $availableRejectedArtists->random(min(5, $availableRejectedArtists->count()));

        foreach ($rejectedArtists as $artist) {
            $changeRequest = ArtistChangeRequest::factory()->create(['artist_id' => $artist->id]);

            $artistChangeRequestsService->changeStatus(
                $changeRequest,
                ArtistChangeRequestStatus::REJECTED,
                'Request rejected for demo data.'
            );
        }

        $this->command->info('Artist change requests seeded.');
    }

    private function canCreateChangeRequest(Artist $artist): bool
    {
        return ! ArtistChangeRequest::query()
            ->where('artist_id', $artist->id)
            ->where('enum_status', ArtistChangeRequestStatus::PENDING->value)
            ->exists();
    }

    private function removeChangesRequested(Artist $artist): void
    {
        ArtistChangeRequest::query()
            ->where('artist_id', $artist->id)
            ->where('enum_status', ArtistChangeRequestStatus::CHANGES_REQUESTED->value)
            ->delete();
    }
}
