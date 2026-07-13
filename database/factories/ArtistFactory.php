<?php

namespace Database\Factories;

use App\Enums\ArtistStatus;
use App\Models\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Artist>
 */
class ArtistFactory extends Factory
{
    protected $model = Artist::class;

    public function definition(): array
    {
        $name = $this->faker->name();
        $discipline = $this->faker->randomElement(['Peinture', 'Sculpture', 'Musique', 'Photographie', 'Théâtre', 'Danse']);

        return [
            'user_id' => null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'discipline' => $discipline,
            'secondary_discipline' => null,
            'city' => $this->faker->randomElement(['Neuchâtel', 'La Chaux-de-Fonds', 'Le Locle', 'Boudry', 'Yverdon-les-Bains']),
            'biography' => '<p>'.$this->faker->paragraph(4).'</p><p>'.$this->faker->paragraph(3).'</p>',
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'cover_image' => null,
            'links' => [
                ['label' => 'Site web', 'url' => $this->faker->url()],
            ],
            'activities' => $this->faker->randomElements(
                ['Peintre', 'Sculpteur·trice', 'Musicien·ne', 'Photographe', 'Comédien·ne', 'Danseur·euse', 'Illustrateur·trice', 'Céramiste'],
                $this->faker->numberBetween(1, 3),
            ),
            'secondary_activities' => [],
            'keywords' => [],
            'collaborations' => [],
            'status' => ArtistStatus::Draft,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ArtistStatus::Published,
            'published_at' => now(),
        ]);
    }
}
