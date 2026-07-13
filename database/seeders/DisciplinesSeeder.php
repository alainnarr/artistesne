<?php

namespace Database\Seeders;

use App\Database\Models\Discipline;
use Illuminate\Database\Seeder;
use App\Enums\DisciplineType;

class DisciplinesSeeder extends Seeder
{
    public function run(): void
    {
        $disciplines = self::getDisciplines();

        foreach ($disciplines as $discipline) {
            unset($discipline['activities']);
            Discipline::firstOrCreate(['code' => $discipline['code']], $discipline);
        }

        $this->command?->info("Disciplines seeded: ".count($disciplines).' discipline(s).');
    }

    public static function getDisciplines(): array
    {
        return [
            [
                'code' => 'musique',
                'label' => 'Musique',
                'enum_type' => DisciplineType::MAIN->value,
                'activities' => [
                    ['code' => 'chanteur', 'label' => 'Chanteur-euse'],
                    ['code' => 'instrumentiste', 'label' => 'Instrumentiste'],
                    ['code' => 'compositeur', 'label' => 'Compositeur-ice'],
                    ['code' => 'arrangeur', 'label' => 'Arrangeur-euse'],
                    ['code' => 'producteur', 'label' => 'Producteur-ice'],
                    ['code' => 'chef_orchestre', 'label' => 'Chef-fe d\'orchestre'],
                    ['code' => 'chef_choeur', 'label' => 'Chef-fe de chœur'],
                    ['code' => 'dj', 'label' => 'DJ'],
                    ['code' => 'sound_designer', 'label' => 'Sound designer'],
                    ['code' => 'parolier', 'label' => 'Parolier-ère'],
                ],
            ],
            [
                'code' => 'spectacle',
                'label' => 'Spectacle vivant',
                'enum_type' => DisciplineType::MAIN->value,
                'activities' => [
                    ['code' => 'comedien', 'label' => 'Comédien-ne'],
                    ['code' => 'danseur', 'label' => 'Danseur-euse'],
                    ['code' => 'choregraphe', 'label' => 'Chorégraphe'],
                    ['code' => 'metteur_en_scene', 'label' => 'Metteur-euse en scène'],
                    ['code' => 'createur_lumière', 'label' => 'Créateur-ice lumière'],
                    ['code' => 'dramaturge', 'label' => 'Dramaturge'],
                    ['code' => 'performeur', 'label' => 'Performeur-euse'],
                    ['code' => 'conteur', 'label' => 'Conteur-euse'],
                    ['code' => 'humoriste', 'label' => 'Humoriste'],
                    ['code' => 'clown', 'label' => 'Clown'],
                    ['code' => 'marionnettiste', 'label' => 'Marionnettiste'],
                    ['code' => 'scenographe', 'label' => 'Scénographe'],
                    ['code' => 'circassien', 'label' => 'Circassien-ne'],
                    ['code' => 'magicien', 'label' => 'Magicien-ne'],
                    ['code' => 'mime', 'label' => 'Mime'],
                    ['code' => 'slammeur', 'label' => 'Slammeur-euse'],
                ],
            ],
            [
                'code' => 'visuels',
                'label' => 'Arts visuels',
                'enum_type' => DisciplineType::MAIN->value,
                'activities' => [
                    ['code' => 'peintre', 'label' => 'Peintre'],
                    ['code' => 'dessinateur', 'label' => 'Dessinateur-ice'],
                    ['code' => 'illustrateur', 'label' => 'Illustrateur-ice'],
                    ['code' => 'photographe', 'label' => 'Photographe'],
                    ['code' => 'graveur', 'label' => 'Graveur-euse'],
                    ['code' => 'sculpteur', 'label' => 'Sculpteur-ice'],
                    ['code' => 'plasticien', 'label' => 'Plasticien-ne'],
                    ['code' => 'videaste', 'label' => 'Vidéaste'],
                ],
            ],
            [
                'code' => 'audiovisuel',
                'label' => 'Cinéma et audiovisuel',
                'enum_type' => DisciplineType::MAIN->value,
                'activities' => [
                    ['code' => 'acteur', 'label' => 'Acteur-ice'],
                    ['code' => 'realisateur', 'label' => 'Réalisateur-ice'],
                    ['code' => 'scenariste', 'label' => 'Scénariste'],
                    ['code' => 'directeur_photo', 'label' => 'Directeur-ice de la photographie'],
                    ['code' => 'producteur', 'label' => 'Producteur-ice'],
                ],
            ],
            [
                'code' => 'litterature',
                'label' => 'Littérature et écriture',
                'enum_type' => DisciplineType::MAIN->value,
                'activities' => [
                    ['code' => 'auteur', 'label' => 'Auteur-ice'],
                    ['code' => 'romancier', 'label' => 'Romancier-ière'],
                    ['code' => 'nouvelliste', 'label' => 'Nouvelliste'],
                    ['code' => 'poete', 'label' => 'Poète'],
                    ['code' => 'essayiste', 'label' => 'Essayiste'],
                    ['code' => 'dramaturge', 'label' => 'Dramaturge'],
                    ['code' => 'traducteur', 'label' => 'Traducteur-ice littéraire'],
                    ['code' => 'bedeiste', 'label' => 'Bédéiste'],
                ],
            ],
            [
                'code' => 'numeriques',
                'label' => 'Arts numériques',
                'enum_type' => DisciplineType::MAIN->value,
                'activities' => [
                    ['code' => 'multimedia', 'label' => 'Artiste multimédia'],
                    ['code' => 'generatif', 'label' => 'Artiste génératif-ive'],
                    ['code' => 'installation_numerique', 'label' => 'Artiste d\'installation numérique'],
                    ['code' => 'interactif', 'label' => 'Artiste interactif-tive'],
                    ['code' => 'realite_virtuelle', 'label' => 'Artiste en réalité virtuelle et augmentée'],
                    ['code' => 'jeux_video', 'label' => 'Créateur-ice de jeux vidéo'],
                    ['code' => 'sonore', 'label' => 'Artiste sonore'],
                    ['code' => 'vj', 'label' => 'VJ'],
                    ['code' => 'motion_designer', 'label' => 'Motion designer'],
                ],
            ],
            [
                'code' => 'secondaire',
                'label' => 'Activités secondaires',
                'enum_type' => DisciplineType::SECONDARY->value,
                'activities' => [
                    ['code' => 'enseignement', 'label' => 'Enseignement / transmission'],
                    ['code' => 'meditation', 'label' => 'Médiation culturelle'],
                    ['code' => 'direction', 'label' => 'Direction artistique'],
                    ['code' => 'recherche', 'label' => 'Recherche artistique'],
                    ['code' => 'curation', 'label' => 'Curation'],
                    ['code' => 'diffusion', 'label' => 'Diffusion'],
                    ['code' => 'conseil', 'label' => 'Conseil artistique'],
                    ['code' => 'programmation', 'label' => 'Programmation'],
                    ['code' => 'administration', 'label' => 'Administration culturelle'],
                    ['code' => 'edition', 'label' => 'Édition et publication']
                ],
            ],
        ];
    }
}
