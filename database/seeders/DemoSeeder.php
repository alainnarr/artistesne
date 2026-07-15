<?php

namespace Database\Seeders;

use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Database\Models\SearchSynonym;
use App\Database\Models\User;
use App\Enums\ApprovalStatus;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Throwable;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin Démo',
            'email' => 'admin@inventaire.test',
        ]);

        $reviewer = User::factory()->admin()->withTwoFactor()->create([
            'name' => 'Équipe Modération',
            'email' => 'moderation@inventaire.test',
        ]);

        User::factory()->admin()->create([
            'name' => 'Support Démo',
            'email' => 'support@inventaire.test',
        ]);

        $musique = Discipline::where('code', 'musique')->first();
        $artsVisuels = Discipline::where('code', 'visuels')->first();

        $profiles = collect([
            ['name' => 'Élodie Marchand', 'discipline_id' => $artsVisuels?->id, 'city' => 'Neuchâtel'],
            ['name' => 'Lucas Berthod', 'discipline_id' => $artsVisuels?->id, 'city' => 'La Chaux-de-Fonds'],
            ['name' => 'Marie Aubert', 'discipline_id' => $musique?->id, 'city' => 'Neuchâtel'],
            ['name' => 'Théo Vuillemin', 'discipline_id' => $artsVisuels?->id, 'city' => 'Le Locle'],
            ['name' => 'Camille Roulin', 'discipline_id' => $musique?->id, 'city' => 'Neuchâtel'],
        ]);

        $publishedArtists = $profiles->map(function (array $row) {
            $user = User::factory()->artist()->create([
                'name' => $row['name'],
                'email' => Str::slug($row['name']).'@inventaire.test',
            ]);

            return Artist::factory()->published()->create([
                'user_id' => $user->id,
                'artist_name' => $row['name'],
                'slug' => Str::slug($row['name']),
                'discipline_main_id' => $row['discipline_id'],
                'city' => $row['city'],
                'email' => $user->email,
                'last_confirmed_at' => now()->subDays(fake()->numberBetween(10, 170)),
            ]);
        });

        $draftArtists = collect(range(1, 3))->map(function (int $index) {
            $name = 'Brouillon Artiste '.$index;
            $user = User::factory()->artist()->create([
                'name' => $name,
                'email' => 'brouillon-artiste-'.$index.'@inventaire.test',
            ]);

            return Artist::factory()->create([
                'user_id' => $user->id,
                'artist_name' => $name,
                'slug' => 'brouillon-artiste-'.$index,
                'email' => $user->email,
                'published_at' => null,
            ]);
        });

        // Pending / approved / rejected registrations (new model).
        Registration::create([
            'real_name' => 'Sonia Petris',
            'artist_name' => 'Sonia Petris',
            'birth_date' => now()->subYears(30)->toDateString(),
            'email' => 'sonia.petris@inventaire.test',
            'phone' => '+41791234567',
            'residence_location' => 'Neuchâtel',
            'discipline_main' => $musique?->id,
            'enum_status' => RegistrationStatus::OPEN->value,
        ]);

        Registration::create([
            'real_name' => 'Ruben Wyss',
            'artist_name' => 'Ruben Wyss',
            'birth_date' => now()->subYears(28)->toDateString(),
            'email' => 'ruben.wyss@inventaire.test',
            'phone' => '+41791234568',
            'residence_location' => 'Neuchâtel',
            'discipline_main' => $artsVisuels?->id,
            'enum_status' => RegistrationStatus::REJECTED->value,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now()->subDays(2),
            'review_notes' => 'Pièces justificatives insuffisantes pour le moment.',
        ]);

        // Change requests (new model).
        $elodie = $publishedArtists->first();
        $lucas = $publishedArtists->get(1);

        ArtistChangeRequest::factory()->create([
            'artist_id' => $elodie->id,
            'submitted_by' => $elodie->user_id,
            'payload' => [
                'biography' => '<p>Artiste peintre établie à Neuchâtel, mise à jour de sa biographie.</p>',
            ],
        ]);

        ArtistChangeRequest::factory()->create([
            'artist_id' => $lucas->id,
            'submitted_by' => $lucas->user_id,
            'status' => ApprovalStatus::Approved->value,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDays(5),
            'review_notes' => 'Mise à jour validée et appliquée.',
            'payload' => [
                'city' => 'La Chaux-de-Fonds',
            ],
        ]);

        collect([
            ['term' => 'peinture', 'synonyms' => ['art pictural', 'peinture contemporaine'], 'one_way' => false],
            ['term' => 'photo', 'synonyms' => ['photographie', 'image'], 'one_way' => false],
            ['term' => 'théâtre', 'synonyms' => ['arts de la scène', 'spectacle vivant'], 'one_way' => false],
            ['term' => 'musique', 'synonyms' => ['composition', 'interprétation', 'sound design'], 'one_way' => false],
            ['term' => 'neuchâtel', 'synonyms' => ['neuchatelois', 'neuchâtelois'], 'one_way' => true],
        ])->each(function (array $synonym): void {
            SearchSynonym::updateOrCreate(
                ['term' => $synonym['term']],
                [
                    'synonyms' => $synonym['synonyms'],
                    'one_way' => $synonym['one_way'],
                ],
            );
        });

        // Sync published artists to Meilisearch search index when available.
        if (config('scout.driver') !== 'null') {
            try {
                $this->command->call('scout:import', ['model' => 'App\Database\Models\Artist']);
            } catch (Throwable $exception) {
                $this->command->warn('Scout import skipped: '.$exception->getMessage());
            }
        }

        $this->command->info('Demo staging data seeded: admins, artists, registrations, change requests and synonyms.');
    }
}
