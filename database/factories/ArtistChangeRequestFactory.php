<?php

namespace Database\Factories;

use App\Database\Models\Activity;
use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\Discipline;
use App\Database\Models\Keyword;
use App\Database\Models\Link;
use App\Database\Models\Repository;
use App\Database\Models\User;
use App\Enums\ArtistChangeRequestStatus;
use App\Enums\ArtistShowContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArtistChangeRequest>
 */
class ArtistChangeRequestFactory extends Factory
{
    protected $model = ArtistChangeRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'artist_id' => Artist::factory(),
            'payload' => ['image' => fake()->boolean(80)],
            'enum_status' => ArtistChangeRequestStatus::PENDING->value,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ArtistChangeRequest $changeRequest): void {
            $changeRequest->payload = $this->makePayload(
                $changeRequest->artist,
                $changeRequest->payload['image'] ?? false
            );
        })->afterCreating(function (ArtistChangeRequest $changeRequest): void {
            if ($changeRequest->payload['image'] ?? false) {
                Repository::factory()
                    ->image($changeRequest)
                    ->create();
            }
        });
    }

    protected function makePayload(Artist $artist, bool $hasImage): array
    {
        $secondaryDiscipline = Discipline::query()->inRandomOrder()->value('id');

        return [
            'artist_name' => fake()->name(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => '+41'.$this->faker->numerify('#########'),
            'biography' => '<p>'.$this->faker->paragraph(4).'</p>',
            'city' => fake()->randomElement([
                'Neuchâtel',
                'La Chaux-de-Fonds',
                'Le Locle',
                'Boudry',
                'Cortaillod',
                'Val-de-Ruz',
            ]),
            'discipline_secondary' => $secondaryDiscipline,
            'enum_show_contact' => fake()->randomElement([
                ArtistShowContact::SHOW->value,
                ArtistShowContact::HIDE->value,
            ]),
            'activities' => $this->makeActivities($artist, $secondaryDiscipline),
            'keywords' => $this->makeKeywords(),
            'links' => $this->makeLinks(),
            'image' => $hasImage,
        ];
    }

    /**
     * Activities must belong to artist main discipline and the new secondary discipline.
     *
     * If the change request modifies discipline_secondary, activities must follow
     * the new discipline. Otherwise, keep the artist current secondary discipline.
     */
    protected function makeActivities(Artist $artist, ?int $secondaryDiscipline = null): array
    {
        $secondaryDiscipline ??= $artist->discipline_secondary;

        $mainActivities = Activity::query()
            ->where('discipline_id', $artist->discipline_main)
            ->inRandomOrder()
            ->limit(fake()->numberBetween(1, 3))
            ->pluck('id');

        $secondaryActivities = collect();

        if ($secondaryDiscipline) {
            $secondaryActivities = Activity::query()
                ->where('discipline_id', $secondaryDiscipline)
                ->inRandomOrder()
                ->limit(fake()->numberBetween(1, 2))
                ->pluck('id');
        }

        return $mainActivities
            ->merge($secondaryActivities)
            ->unique()
            ->values()
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    /**
     * Creates realistic keywords through KeywordFactory.
     */
    protected function makeKeywords(): array
    {
        return collect(KeywordFactory::keywords())
            ->random(fake()->numberBetween(2, 5))
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Creates artist links without duplicated types.
     */
    protected function makeLinks(): array
    {
        return Link::factory()
            ->count(fake()->numberBetween(2, 5))
            ->make()
            ->unique('enum_type')
            ->map(fn (Link $link): array => [
                'enum_type' => $link->enum_type->value,
                'link' => $link->link,
            ])
            ->values()
            ->toArray();
    }

    public function approved(?User $reviewer = null): static
    {
        return $this
            ->state([
                'enum_status' => ArtistChangeRequestStatus::APPROVED->value,
                'reviewed_at' => now(),
                'reviewed_by' => $reviewer?->id,
                'review_notes' => fake()->sentence(),
            ])
            ->afterMaking(function (ArtistChangeRequest $changeRequest): void {
                $changeRequest->payload = $this->makePayload($changeRequest->artist, true);
            });
    }

    public function onlyBiography(): static
    {
        return $this->state([
            'payload' => [
                'biography' => '<p>'.$this->faker->paragraph(4).'</p>',
                'image' => false,
            ],
        ]);
    }

    public function onlyActivities(): static
{
    return $this
        ->state(['payload' => ['image' => false]])
        ->afterMaking(function (ArtistChangeRequest $changeRequest): void {
            $changeRequest->payload = [
                'activities' => $this->makeActivities(
                    $changeRequest->artist,
                    $changeRequest->artist->discipline_secondary
                ),
                'image' => false,
            ];
        });
}

    public function onlyKeywords(): static
    {
        return $this->state([
            'payload' => [
                'keywords' => $this->makeKeywords(),
                'image' => false,
            ],
        ]);
    }

    public function onlyLinks(): static
    {
        return $this->state([
            'payload' => [
                'links' => $this->makeLinks(),
                'image' => false,
            ],
        ]);
    }

    public function onlySecondaryDiscipline(): static
    {
        return $this->state([
            'payload' => [
                'discipline_secondary' => Discipline::query()->inRandomOrder()->value('id'),
                'image' => false,
            ],
        ]);
    }

    public function onlyArtistName(): static
    {
        return $this->state([
            'payload' => [
                'artist_name' => fake()->name(),
                'image' => false,
            ],
        ]);
    }

    public function withNewImage(): static
    {
        return $this->state(['payload' => ['image' => true]]);
    }

    public function multipleChanges(): static
    {
        return $this
            ->afterMaking(function (ArtistChangeRequest $changeRequest): void {
                $changeRequest->payload = $this->makePayload($changeRequest->artist, true);
            });
    }
}
