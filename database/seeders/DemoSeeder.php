<?php

namespace Database\Seeders;

use App\Enums\ApprovalStatus;
use App\Models\Artist;
use App\Models\ArtistChangeRequest;
use App\Models\ArtistRegistrationRequest;
use App\Models\SearchSynonym;
use App\Models\User;
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

        $profiles = collect([
            [
                'name' => 'Élodie Marchand',
                'discipline' => 'Peinture',
                'secondary_discipline' => 'Arts numériques',
                'city' => 'Neuchâtel',
                'activities' => ['Peintre', 'Graveuse', 'Muraliste'],
                'secondary_activities' => ['Atelier pour enfants', 'Résidences artistiques'],
                'keywords' => ['paysage lacustre', 'abstraction', 'aquarelle', 'techniques mixtes'],
                'collaborations' => [
                    ['name' => 'Musée d\'art et d\'histoire de Neuchâtel', 'url' => 'https://mahn.ch'],
                    ['name' => 'Centre d\'art Pasquart', 'url' => 'https://pasquart.ch'],
                ],
                'links' => [
                    ['label' => 'Site web', 'url' => 'https://elodie-marchand.ch'],
                    ['label' => 'Instagram', 'url' => 'https://instagram.com/elodie.marchand'],
                ],
                'biography' => '<p>Artiste peintre établie à Neuchâtel, Élodie Marchand explore depuis vingt ans les paysages lacustres du Jura. Son travail récent intègre des techniques mixtes mêlant aquarelle et collage, créant des compositions qui oscillent entre figuration et abstraction.</p><p>Elle a exposé en 2025 au Musée d\'art et d\'histoire de Neuchâtel et a participé à plusieurs résidences à travers la Suisse romande.</p>',
                'display_contact_button' => true,
            ],
            [
                'name' => 'Lucas Berthod',
                'discipline' => 'Arts visuels',
                'secondary_discipline' => 'Céramique',
                'city' => 'La Chaux-de-Fonds',
                'activities' => ['Sculpteur', 'Céramiste', 'Installateur'],
                'secondary_activities' => ['Cours de céramique', 'Scénographie'],
                'keywords' => ['sculpture', 'bronze', 'espace public', 'matière'],
                'collaborations' => [
                    ['name' => 'Ville de La Chaux-de-Fonds', 'url' => 'https://chaux-de-fonds.ch'],
                ],
                'links' => [
                    ['label' => 'Site officiel', 'url' => 'https://lucas-berthod.ch'],
                    ['label' => 'Instagram', 'url' => 'https://instagram.com/lucas.berthod'],
                ],
                'biography' => '<p>Lucas Berthod est sculpteur et céramiste basé à La Chaux-de-Fonds. Il travaille principalement le bronze et la terre cuite, explorant la relation entre l\'objet et l\'espace public.</p><p>Ses installations ont été présentées dans plusieurs villes de Suisse et à l\'étranger.</p>',
                'display_contact_button' => false,
            ],
            [
                'name' => 'Marie Aubert',
                'discipline' => 'Musique',
                'secondary_discipline' => 'Composition',
                'city' => 'Neuchâtel',
                'activities' => ['Compositrice', 'Interprète', 'Arrangeuse'],
                'secondary_activities' => ['Ateliers de création sonore', 'Musique de film'],
                'keywords' => ['jazz', 'improvisation', 'électroacoustique', 'voix'],
                'collaborations' => [
                    ['name' => 'Festival de Musique de Neuchâtel', 'url' => null],
                    ['name' => 'Ensemble Vortex', 'url' => 'https://ensemblevortex.ch'],
                ],
                'links' => [
                    ['label' => 'SoundCloud', 'url' => 'https://soundcloud.com/marie-aubert'],
                    ['label' => 'Bandcamp', 'url' => 'https://marieaubert.bandcamp.com'],
                ],
                'biography' => '<p>Marie Aubert est compositrice et interprète. Son univers musical croise jazz contemporain, improvisation libre et exploration électroacoustique. Elle a fondé le trio "Latitudes" avec lequel elle tourne régulièrement en Europe.</p>',
                'display_contact_button' => true,
            ],
            [
                'name' => 'Théo Vuillemin',
                'discipline' => 'Photographie',
                'secondary_discipline' => null,
                'city' => 'Le Locle',
                'activities' => ['Photographe documentaire', 'Vidéaste'],
                'secondary_activities' => ['Reportage social', 'Photographie industrielle'],
                'keywords' => ['documentaire', 'portrait', 'territoire', 'noir et blanc'],
                'collaborations' => [],
                'links' => [
                    ['label' => 'Site web', 'url' => 'https://theovuillemin.com'],
                    ['label' => 'Instagram', 'url' => 'https://instagram.com/theo.vuillemin'],
                ],
                'biography' => '<p>Théo Vuillemin documente depuis dix ans les mutations du territoire neuchâtelois. Son travail photographique interroge la relation entre industrie, paysage et mémoire collective.</p>',
                'display_contact_button' => true,
            ],
            [
                'name' => 'Camille Roulin',
                'discipline' => 'Spectacle vivant',
                'secondary_discipline' => 'Danse',
                'city' => 'Neuchâtel',
                'activities' => ['Metteuse en scène', 'Comédienne', 'Chorégraphe'],
                'secondary_activities' => ['Formation théâtrale', 'Dramaturgie'],
                'keywords' => ['théâtre contemporain', 'performance', 'corps', 'texte'],
                'collaborations' => [
                    ['name' => 'Théâtre du Passage', 'url' => 'https://theatredupassage.ch'],
                    ['name' => 'Arsenic Lausanne', 'url' => 'https://arsenic.ch'],
                ],
                'links' => [
                    ['label' => 'Site web', 'url' => 'https://camilleroulin.ch'],
                ],
                'biography' => '<p>Camille Roulin est metteuse en scène et comédienne. Ses créations, souvent à la frontière du théâtre et de la performance, ont été jouées dans plusieurs espaces neuchâtelois et romands.</p><p>Elle collabore régulièrement avec le Théâtre du Passage et dirige des ateliers de jeu auprès de publics variés.</p>',
                'display_contact_button' => true,
            ],
            [
                'name' => 'Nora Jeanneret',
                'discipline' => 'Littérature',
                'secondary_discipline' => 'Arts visuels',
                'city' => 'Boudry',
                'activities' => ['Autrice', 'Performeuse', 'Poétesse'],
                'secondary_activities' => ['Ateliers d\'écriture'],
                'keywords' => ['poésie sonore', 'lecture publique', 'édition indépendante'],
                'collaborations' => [
                    ['name' => 'Bibliothèque publique de Neuchâtel', 'url' => null],
                ],
                'links' => [
                    ['label' => 'Substack', 'url' => 'https://norajeanneret.substack.com'],
                ],
                'biography' => '<p>Nora Jeanneret développe un travail à la croisée de la poésie, de la performance et du dessin éditorial.</p>',
                'display_contact_button' => false,
            ],
            [
                'name' => 'Ali Rezzak',
                'discipline' => 'Cinéma',
                'secondary_discipline' => 'Photographie',
                'city' => 'Le Locle',
                'activities' => ['Réalisateur', 'Monteur', 'Photographe'],
                'secondary_activities' => ['Direction photo'],
                'keywords' => ['documentaire', 'territoire', 'mémoire ouvrière'],
                'collaborations' => [
                    ['name' => 'Festival du Film Vert', 'url' => 'https://festivaldufilmvert.ch'],
                ],
                'links' => [
                    ['label' => 'Vimeo', 'url' => 'https://vimeo.com/alirezzak'],
                ],
                'biography' => '<p>Ali Rezzak réalise des films documentaires centrés sur les récits de travail, de migration et de territoire.</p>',
                'display_contact_button' => true,
            ],
            [
                'name' => 'Lina Aebi',
                'discipline' => 'Design',
                'secondary_discipline' => 'Illustration',
                'city' => 'La Chaux-de-Fonds',
                'activities' => ['Designer graphique', 'Illustratrice'],
                'secondary_activities' => ['Direction artistique'],
                'keywords' => ['typographie', 'édition', 'signalétique'],
                'collaborations' => [
                    ['name' => 'École d\'art de La Chaux-de-Fonds', 'url' => null],
                ],
                'links' => [
                    ['label' => 'Portfolio', 'url' => 'https://linaaebi.ch'],
                ],
                'biography' => '<p>Lina Aebi accompagne des institutions culturelles et des associations locales sur des projets d\'identité visuelle et d\'édition.</p>',
                'display_contact_button' => false,
            ],
        ]);

        $publishedArtists = $profiles->map(function (array $row) {
            $user = User::factory()->artist()->create([
                'name' => $row['name'],
                'email' => Str::slug($row['name']).'@inventaire.test',
            ]);

            return Artist::factory()->published()->create([
                'user_id' => $user->id,
                'name' => $row['name'],
                'slug' => Str::slug($row['name']),
                'discipline' => $row['discipline'],
                'secondary_discipline' => $row['secondary_discipline'],
                'city' => $row['city'],
                'biography' => $row['biography'],
                'email' => $user->email,
                'display_contact_button' => $row['display_contact_button'],
                'links' => $row['links'],
                'activities' => $row['activities'],
                'secondary_activities' => $row['secondary_activities'],
                'keywords' => $row['keywords'],
                'collaborations' => $row['collaborations'],
                'last_confirmed_at' => now()->subDays(fake()->numberBetween(10, 170)),
            ]);
        });

        $generatedPublishedArtists = collect(range(1, 10))->map(function (int $index) {
            $name = 'Artiste Démo '.$index;
            $user = User::factory()->artist()->create([
                'name' => $name,
                'email' => 'artiste-demo-'.$index.'@inventaire.test',
            ]);

            return Artist::factory()->published()->create([
                'user_id' => $user->id,
                'name' => $name,
                'slug' => 'artiste-demo-'.$index,
                'email' => $user->email,
                'display_contact_button' => true,
                'last_confirmed_at' => now()->subDays(40),
            ]);
        });

        $draftArtists = collect(range(1, 5))->map(function (int $index) {
            $name = 'Brouillon Artiste '.$index;
            $user = User::factory()->artist()->create([
                'name' => $name,
                'email' => 'brouillon-artiste-'.$index.'@inventaire.test',
            ]);

            return Artist::factory()->create([
                'user_id' => $user->id,
                'name' => $name,
                'slug' => 'brouillon-artiste-'.$index,
                'email' => $user->email,
                'display_contact_button' => false,
                'published_at' => null,
            ]);
        });

        ArtistRegistrationRequest::factory()->count(4)->create();

        ArtistRegistrationRequest::factory()->create([
            'artist_name' => 'Sonia Petris',
            'email' => 'sonia.petris@inventaire.test',
            'status' => ApprovalStatus::Approved,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDays(4),
            'review_notes' => 'Dossier validé, profil prêt pour envoi du lien magique.',
        ]);

        ArtistRegistrationRequest::factory()->create([
            'artist_name' => 'Ruben Wyss',
            'email' => 'ruben.wyss@inventaire.test',
            'status' => ApprovalStatus::Rejected,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now()->subDays(2),
            'review_notes' => 'Pièces justificatives insuffisantes pour le moment.',
        ]);

        ArtistRegistrationRequest::factory()->create([
            'artist_name' => 'Hélène Perrin',
            'email' => 'helene.perrin@inventaire.test',
            'status' => ApprovalStatus::ChangesRequested,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now()->subDay(),
            'review_notes' => 'Merci de compléter la section activités récentes et les liens de référence.',
        ]);

        $elodie = $publishedArtists->first();
        $lucas = $publishedArtists->get(1);
        $marie = $publishedArtists->get(2);
        $generatedArtist = $generatedPublishedArtists->first();

        ArtistChangeRequest::factory()->create([
            'artist_id' => $elodie->id,
            'submitted_by' => $elodie->user_id,
            'payload' => [
                'biography' => '<p>Artiste peintre établie à Neuchâtel, Élodie Marchand explore depuis vingt ans les paysages lacustres du Jura. Son travail récent intègre des techniques mixtes mêlant aquarelle et collage.</p><p>Elle a exposé en 2025 au Musée d\'art et d\'histoire de Neuchâtel.</p>',
                'discipline' => 'Peinture et techniques mixtes',
            ],
        ]);

        ArtistChangeRequest::factory()->create([
            'artist_id' => $lucas->id,
            'submitted_by' => $lucas->user_id,
            'payload' => [
                'links' => [
                    ['label' => 'Site officiel', 'url' => 'https://lucas-berthod.ch'],
                    ['label' => 'Instagram', 'url' => 'https://instagram.com/lucas.berthod'],
                ],
                'phone' => '+41 32 555 12 34',
            ],
        ]);

        ArtistChangeRequest::factory()->create([
            'artist_id' => $marie->id,
            'submitted_by' => $marie->user_id,
            'status' => ApprovalStatus::Approved,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDays(5),
            'review_notes' => 'Mise à jour validée et appliquée.',
            'payload' => [
                'secondary_activities' => ['Ateliers de création sonore', 'Musique de film', 'Mentorat'],
                'keywords' => ['jazz', 'improvisation', 'électroacoustique', 'voix', 'piano préparé'],
            ],
        ]);

        ArtistChangeRequest::factory()->create([
            'artist_id' => $generatedArtist->id,
            'submitted_by' => $generatedArtist->user_id,
            'status' => ApprovalStatus::Rejected,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now()->subDays(3),
            'review_notes' => 'Lien fourni invalide, demande refusée.',
            'payload' => [
                'links' => [
                    ['label' => 'Site web', 'url' => 'https://example.invalid/profile'],
                ],
            ],
        ]);

        ArtistChangeRequest::factory()->create([
            'artist_id' => $generatedArtist->id,
            'submitted_by' => $generatedArtist->user_id,
            'status' => ApprovalStatus::ChangesRequested,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDay(),
            'review_notes' => 'Merci de préciser la commune de résidence et les collaborations.',
            'payload' => [
                'city' => 'Neuchâtel',
                'collaborations' => [
                    ['name' => 'Association culturelle locale', 'url' => null],
                ],
            ],
        ]);

        foreach ($draftArtists->take(2) as $draftArtist) {
            ArtistChangeRequest::factory()->create([
                'artist_id' => $draftArtist->id,
                'submitted_by' => $draftArtist->user_id,
                'payload' => [
                    'biography' => '<p>Demande de publication en attente de validation éditoriale.</p>',
                ],
            ]);
        }

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

        $this->call(KeywordTaxonomySeeder::class);

        // Sync published artists to Meilisearch search index when available.
        if (config('scout.driver') !== 'null') {
            try {
                $this->command->call('scout:import', ['model' => 'App\Models\Artist']);
            } catch (Throwable $exception) {
                $this->command->warn('Scout import skipped: '.$exception->getMessage());
            }
        }

        $this->command->info('Demo staging data seeded: admins, artists, registration requests, change requests, keywords and synonyms.');
    }
}
