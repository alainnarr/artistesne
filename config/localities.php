<?php

/*
|--------------------------------------------------------------------------
| Localités (communes du canton de Neuchâtel)
|--------------------------------------------------------------------------
|
| Regroupées par région pour le sélecteur de lieu de résidence. Les deux
| dernières entrées ("Autre" / "Hors canton") déclenchent l'affichage de
| champs complémentaires sur le formulaire de demande de référencement.
|
*/

return [

    'outside_canton_value' => 'Hors canton',

    'groups' => [
        'Région Littoral' => [
            'Boudry',
            'Cornaux',
            'Cortaillod',
            'Cressier',
            'La Grande Béroche',
            'Laténa',
            'Le Landeron',
            'Lignières',
            'Milvignes',
            'Neuchâtel',
            'Rochefort',
        ],
        'Région Montagnes' => [
            'Brot-Plamboz',
            'La Brévine',
            'La Chaux-de-Fonds',
            'La Chaux-du-Milieu',
            'La Sagne',
            'Le Cerneux-Péquignot',
            'Le Locle',
            'Les Planchettes',
            'Les Ponts-de-Martel',
        ],
        'Région Val-de-Ruz' => [
            'Val-de-Ruz',
        ],
        'Région Val-de-Travers' => [
            'La Côte-aux-Fées',
            'Les Verrières',
            'Val-de-Travers',
        ],
        'Autre' => [
            'Hors canton',
        ],
    ],

];
