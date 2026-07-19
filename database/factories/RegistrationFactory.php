<?php

namespace Database\Factories;

use App\Database\Models\Activity;
use App\Database\Models\Link;
use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Database\Models\Repository;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();
        $residence = $this->generateResidence();

        return [
            'real_name' => $name,
            'artist_name' => $name,
            'slug' => Str::slug($name).'_'.Str::lower(Str::random(5)),

            'birth_date' => fake()
                ->dateTimeBetween('-70 years', '-18 years')
                ->format('Y-m-d'),

            'email' => fake()->unique()->safeEmail(),
            'phone' => '+41'.fake()->numerify('#########'),
            'residence_location' => $residence['residence_location'],
            'locality' => $residence['locality'],
            'canton_link' => null,

            'discipline_main' => Discipline::query()->inRandomOrder()->value('id'),
            'discipline_secondary' => Discipline::query()->whereNotNull('id')->inRandomOrder()->value('id'),

            'training' => fake()->optional()->paragraph(),
            'paid_work' => fake()->optional()->paragraph(),
            'recognition' => fake()->optional()->paragraph(),
            'recent_achievements' => fake()->optional()->paragraph(),
            'last_work' => fake()->optional()->paragraph(),

            'enum_status' => RegistrationStatus::OPEN->value,

            'reviewed_at' => null,
            'reviewed_by' => null,
            'review_notes' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Registration $registration): void {
            $this->createActivities($registration);
            $this->createCollaborationLinks($registration);
            $this->createDocuments($registration);
        });
    }

    public function open(): static
    {
        return $this->state(fn (): array => [
            'enum_status' => RegistrationStatus::OPEN->value,
            'reviewed_at' => null,
            'reviewed_by' => null,
            'review_notes' => null,
        ]);
    }

    public function pending(?int $reviewedBy = null): static
    {
        return $this->state(fn (): array => [
            'enum_status' => RegistrationStatus::PENDING->value,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewedBy,
            'review_notes' => 'Explications demandées',
        ]);
    }

    public function approved(?int $reviewedBy = null): static
    {
        return $this->state(fn (): array => [
            'enum_status' => RegistrationStatus::APPROVED->value,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewedBy,
            'review_notes' => null,
        ]);
    }

    public function rejected(?int $reviewedBy = null): static
    {
        return $this->state(fn (): array => [
            'enum_status' => RegistrationStatus::REJECTED->value,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewedBy,
            'review_notes' => 'Dossier insuffisant pour validation.',
        ]);
    }

    /**
     * Creates activities linked to the registration.
     */
    private function createActivities(Registration $registration): void
    {
        $mainActivities = Activity::query()
            ->where('discipline_id', $registration->discipline_main)
            ->inRandomOrder()
            ->limit(fake()->numberBetween(1, 3))
            ->pluck('id');

        $secondaryActivities = collect();
        if ($registration->discipline_secondary) {
            $secondaryActivities = Activity::query()
                ->where('discipline_id', $registration->discipline_secondary)
                ->inRandomOrder()
                ->limit(fake()->numberBetween(1, 2))
                ->pluck('id');
        }

        $registration->activities()->sync($mainActivities->merge($secondaryActivities)->unique()->values()->all());
    }

    /**
     * Creates collaboration links.
     *
     * Registrations are allowed to have multiple collaboration links.
     */
    private function createCollaborationLinks(Registration $registration): void
    {
        Link::factory()
            ->collaboration()
            ->count(fake()->numberBetween(1, 3))
            ->create([
                'registration_id' => $registration->id,
            ]);
    }

    /**
     * Creates private documents attached to the registration.
     */
    private function createDocuments(Registration $registration): void
    {
        if (! fake()->boolean(70)) {
            return;
        }

        Repository::factory()
            ->document($registration)
            ->count(fake()->numberBetween(1, 3))
            ->create();
    }

    /**
     * Generates a valid residence according to the application rules.
     *
     * For Neuchâtel canton communes:
     * - residence_location = commune
     * - locality = null
     *
     * For external communes:
     * - residence_location = canton
     * - locality = commune
     *
     * @return array{
     *     residence_location: string,
     *     locality: string|null
     * }
     */
    private function generateResidence(): array
    {
        $neuchatelCommunes = [
            'Neuchâtel',
            'La Chaux-de-Fonds',
            'Le Locle',
            'Boudry',
            'Val-de-Travers',
            'Val-de-Ruz',
            'Milvignes',
            'Peseux',
            'Cortaillod',
            'Colombier',
        ];

        $externalCommunes = [
            [
                'canton' => 'Vaud',
                'commune' => 'Yverdon-les-Bains',
            ],
            [
                'canton' => 'Fribourg',
                'commune' => 'Estavayer',
            ],
            [
                'canton' => 'Jura',
                'commune' => 'Delémont',
            ],
            [
                'canton' => 'Genève',
                'commune' => 'Carouge',
            ],
        ];

        if (fake()->boolean(85)) {
            return [
                'residence_location' => fake()->randomElement($neuchatelCommunes),
                'locality' => null,
            ];
        }

        $external = fake()->randomElement($externalCommunes);

        return [
            'residence_location' => $external['canton'],
            'locality' => $external['commune'],
        ];
    }
}
