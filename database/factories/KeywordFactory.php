<?php

namespace Database\Factories;

use App\Database\Models\Keyword;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Keyword>
 */
class KeywordFactory extends Factory
{
    protected $model = Keyword::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->unique()->randomElement(self::keywords()),
        ];
    }

    /**
     * List of keywords related to artistic practices
     *
     * @return array<int, string>
     */
    public static function keywords(): array
    {
        return [
            // Techniques visuelles
            'peinture',
            'huile',
            'aquarelle',
            'acrylique',
            'dessin',
            'illustration',
            'gravure',
            'sculpture',
            'photographie',
            'vidéo',
            'installation',
            'art numérique',
            'art génératif',

            // Musique
            'composition',
            'improvisation',
            'musique électronique',
            'musique contemporaine',
            'jazz',
            'classique',
            'chanson',
            'sound design',
            'production musicale',

            // Spectacle vivant
            'théâtre',
            'danse contemporaine',
            'performance',
            'cirque',
            'mise en scène',
            'chorégraphie',

            // Littérature
            'poésie',
            'roman',
            'écriture',
            'fiction',
            'traduction',

            // Pratiques transversales
            'expérimental',
            'interdisciplinaire',
            'art contemporain',
            'médiation culturelle',
            'transmission',
            'recherche artistique',
            'écologie',
            'territoire',
            'patrimoine',
        ];
    }
}
