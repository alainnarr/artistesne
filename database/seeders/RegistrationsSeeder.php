<?php

namespace Database\Seeders;

use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Database\Models\Repository;
use App\Database\Models\User;
use App\Enums\RegistrationStatus;
use App\Services\ArtistsService;
use App\Enums\ArtistStatus;
use App\Services\RegistrationsService;
use Illuminate\Database\Seeder;

class RegistrationsSeeder extends Seeder
{
    public function run(): void
    {
        $registrationsService = app(RegistrationsService::class);
        $artistsService = app(ArtistsService::class);

        $admin = User::query()->where('enum_role', 'admin')->first();

        if (! $admin) {
            $this->command->error('No admin user found. Run UsersSeeder first.');
            return;
        }

        $profiles = $this->getProfiles();

        foreach ($profiles as $profile) {
            $registration = Registration::firstOrCreate(
                ['email' => $profile['email']],
                Registration::factory()->make([
                    'real_name' => $profile['name'],
                    'artist_name' => $profile['name'],
                    'residence_location' => $profile['city'],
                    'discipline_main' => Discipline::where('code', $profile['discipline_main_code'])->value('id'),
                ])->toArray()
            );


            if ($profile['registration_status'] === RegistrationStatus::APPROVED) {
                $registrationsService->changeStatus($registration, RegistrationStatus::APPROVED);
                if ($profile['artist_published']) {
                    $artist = $registration->fresh()->artist;

                   if ($artist) {
                       // Ensure the artist has a portrait before publishing.
                       if (!$artist->image()->exists()) {
                           Repository::factory()
                               ->image($artist)
                               ->create();
                       }
                       $artistsService->changeStatus($artist->fresh(), ArtistStatus::PUBLISHED);
                   }
                }
            }

            if ($profile['registration_status'] === RegistrationStatus::REJECTED) {
                $registrationsService->changeStatus($registration, RegistrationStatus::REJECTED);
            }

            if ($profile['registration_status'] === RegistrationStatus::PENDING) {
                $registrationsService->changeStatus($registration, RegistrationStatus::PENDING);
            }
        }

        Registration::factory()
            ->open()
            ->count(10)
            ->create();

        Registration::factory()
            ->pending($admin->id)
            ->count(5)
            ->create();

        Registration::factory()
            ->rejected($admin->id)
            ->count(3)
            ->create();

        Registration::factory()
            ->count(30)
            ->create()
            ->each(function (Registration $registration) use ($registrationsService): void {
                $registrationsService->changeStatus($registration, RegistrationStatus::APPROVED
                );
            });

        $this->command->info('Registrations seeded.');
    }

    private function getProfiles()
    {
        return [
            [
                'name' => 'Élodie Marchand',
                'email' => 'elodie.marchand@inventaire.test',
                'city' => 'Neuchâtel',
                'discipline_main_code' => 'visuels',
                'registration_status' => RegistrationStatus::APPROVED,
                'artist_published' => true,
            ],
            [
                'name' => 'Lucas Berthod',
                'email' => 'lucas.berthod@inventaire.test',
                'city' => 'La Chaux-de-Fonds',
                'discipline_main_code' => 'visuels',
                'registration_status' => RegistrationStatus::APPROVED,
                'artist_published' => true,
            ],
            [
                'name' => 'Marie Aubert',
                'email' => 'marie.aubert@inventaire.test',
                'city' => 'Neuchâtel',
                'discipline_main_code' => 'musique',
                'registration_status' => RegistrationStatus::APPROVED,
                'artist_published' => true,
            ],
            [
                'name' => 'Théo Vuillemin',
                'email' => 'theo.vuillemin@inventaire.test',
                'city' => 'Le Locle',
                'discipline_main_code' => 'visuels',
                'registration_status' => RegistrationStatus::APPROVED,
                'artist_published' => true,
            ],
            [
                'name' => 'Camille Roulin',
                'email' => 'camille.roulin@inventaire.test',
                'city' => 'Neuchâtel',
                'discipline_main_code' => 'musique',
                'registration_status' => RegistrationStatus::OPEN,
                'artist_published' => false,
            ],
            [
                'name' => 'Sonia Petris',
                'email' => 'sonia.petris@inventaire.test',
                'city' => 'Neuchâtel',
                'discipline_main_code' => 'visuels',
                'registration_status' => RegistrationStatus::REJECTED,
                'artist_published' => false,
            ],
        ];
    }
}
