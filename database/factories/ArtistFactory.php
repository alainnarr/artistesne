<?php

namespace Database\Factories;

use App\Database\Models\Artist;
use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Database\Models\User;
use App\Database\Models\Repository;
use App\Enums\ArtistShowContact;
use App\Enums\ArtistStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Artist>
 */
class ArtistFactory extends Factory
{
    protected $model = Artist::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $registration = Registration::factory()->create();
        $user = User::factory()->artist()->create();

        return [
            'registration_id' => $registration->id,

            'user_id' => $user->id,

            'slug' => $registration->slug,
            'artist_name' => $registration->name,
            'email' => $registration->email,
            'phone' => $registration->phone,
            'city' => $registration->city,
            'discipline_main' => $registration->discipline_main,
            'discipline_secondary' => $registration->discipline_secondary,

            'enum_status' => ArtistStatus::DRAFT->value,
            'enum_show_contact' => ArtistShowContact::HIDE->value,
            'published_at' => null,
            'last_confirmed_at' => null,
            'reminder_sent_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Artist $artist): void {
            $activities = $artist->registration
                ->activities()
                ->pluck('activities.id')
                ->toArray();

            if (! empty($activities)) {
                $artist->activities()->sync($activities);
            }
        });
    }

    public function published(): static
    {
        return $this
            ->state(fn (): array => [
                'enum_status' => ArtistStatus::PUBLISHED->value,
                'published_at' => now(),
            ])
            ->afterCreating(function (Artist $artist): void {
                Repository::factory()
                    ->image($artist)
                    ->create();
            });
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'enum_status' => ArtistStatus::DRAFT->value,
            'published_at' => null,
        ]);
    }

    public function showContact(): static
    {
        return $this->state(fn (): array => [
            'enum_show_contact' => ArtistShowContact::SHOW->value,
        ]);
    }

    public function hideContact(): static
    {
        return $this->state(fn (): array => [
            'enum_show_contact' => ArtistShowContact::HIDE->value,
        ]);
    }
}
