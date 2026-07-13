<?php

/*
|--------------------------------------------------------------------------
| Taxonomie des activités artistiques
|--------------------------------------------------------------------------
|
| Liste fermée des activités principales par domaine. En cours de validation
| par le SCNE : cette liste peut évoluer. La valeur spéciale "autre" permet
| à l'utilisateur de préciser une activité non listée.
|
| Les clés de `main_activities` sont les slugs stables des domaines
| artistiques, administrés en base via TaxonomyTerm (type "domain") —
| voir database/seeders/TaxonomyTermsSeeder.php.
|
*/

return [

    'other_value' => 'autre',
    'other_label' => 'Autre activité principale',

    'main_activities' => [
        'musique' => [
            'Chanteur-euse',
            'Instrumentiste',
            'Compositeur-ice',
            'Arrangeur-euse',
            'Producteur-ice',
            "Chef-fe d'orchestre",
            'Chef-fe de chœur',
            'DJ',
            'Sound designer',
            'Parolier-ère',
        ],
        'spectacle_vivant' => [
            'Comédien-ne',
            'Danseur-euse',
            'Chorégraphe',
            'Metteur-euse en scène',
            'Créateur-ice lumière',
            'Dramaturge',
            'Performeur-euse',
            'Conteur-euse',
            'Humoriste',
            'Clown',
            'Marionnettiste',
            'Scénographe',
            'Circassien-ne',
            'Magicien-ne',
            'Mime',
            'Slammeur-euse',
        ],
        'arts_visuels' => [
            'Peintre',
            'Dessinateur-ice',
            'Illustrateur-ice',
            'Photographe',
            'Graveur-euse',
            'Sculpteur-ice',
            'Plasticien-ne',
            'Vidéaste',
        ],
        'cinema_audiovisuel' => [
            'Acteur-ice',
            'Réalisateur-ice',
            'Scénariste',
            'Directeur-ice de la photographie',
            'Producteur-ice',
        ],
        'litterature_ecriture' => [
            'Auteur-ice',
            'Romancier-ière',
            'Nouvelliste',
            'Poète',
            'Essayiste',
            'Dramaturge',
            'Traducteur-ice littéraire',
            'Bédéiste',
        ],
        'arts_numeriques' => [
            'Artiste multimédia',
            'Artiste génératif-ive',
            "Artiste d'installation numérique",
            'Artiste interactif-tive',
            'Artiste en réalité virtuelle et augmentée',
            'Créateur-ice de jeux vidéo',
            'Artiste sonore',
            'VJ',
            'Motion designer',
        ],
    ],

];
