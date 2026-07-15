<?php

namespace Database\Factories;

use App\Database\Models\Artist;
use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Database\Models\User;
use App\Enums\ArtistShowContact;
use App\Enums\ArtistStatus;
use App\Enums\RegistrationStatus;
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

        return [
            'registration_id' => function () use ($name) {
                return Registration::create([
                    'real_name' => $name,
                    'artist_name' => $name,
                    'birth_date' => $this->faker->date(),
                    'email' => $this->faker->unique()->safeEmail(),
                    'phone' => '+41'.$this->faker->numerify('#########'),
                    'residence_location' => 'Neuchâtel',
                    'discipline_main' => Discipline::query()->inRandomOrder()->value('id'),
                    'enum_status' => RegistrationStatus::APPROVED->value,
                ])->id;
            },
            'user_id' => User::factory()->artist(),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'artist_name' => $name,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '+41'.$this->faker->numerify('#########'),
            'city' => $this->faker->randomElement(['Neuchâtel', 'La Chaux-de-Fonds', 'Le Locle', 'Boudry', 'Yverdon-les-Bains']),
            'biography' => '<p>'.$this->faker->paragraph(4).'</p><p>'.$this->faker->paragraph(3).'</p>',
            'discipline_main_id' => Discipline::query()->inRandomOrder()->value('id'),
            'activities' => $this->faker->randomElements(
                ['Peintre', 'Sculpteur·trice', 'Musicien·ne', 'Photographe', 'Comédien·ne', 'Danseur·euse', 'Illustrateur·trice', 'Céramiste'],
                $this->faker->numberBetween(1, 3),
            ),
            'secondary_activities' => [],
            'keywords' => [],
            'links' => [
                ['label' => 'Site web', 'url' => $this->faker->url()],
            ],
            'collaborations' => [],
            'enum_status' => ArtistStatus::Draft->value,
            'enum_show_contact' => ArtistShowContact::HIDE->value,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'enum_status' => ArtistStatus::Published->value,
            'published_at' => now(),
        ]);
    }
}
