<?php

/*
|--------------------------------------------------------------------------
| Taxonomie des activités artistiques
|--------------------------------------------------------------------------
|
| Les domaines et activités principales/secondaires sont administrés en
| base via les modèles App\Database\Models\Discipline et Activity — voir
| database/seeders/DisciplinesSeeder.php et ActivitiesSeeder.php.
|
| Ce fichier ne conserve que la valeur spéciale "autre", qui permet à
| l'utilisateur de préciser une activité principale non listée dans le
| formulaire d'inscription (voir RegisterArtist::isOtherActivity()).
|
*/

return [

    'other_value' => 'autre',
    'other_label' => 'Autre activité principale',

];
