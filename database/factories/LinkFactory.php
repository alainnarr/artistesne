<?php

namespace Database\Factories;

use App\Database\Models\Link;
use App\Enums\LinkType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Link>
 */
class LinkFactory extends Factory
{
    protected $model = Link::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(
            collect(LinkType::cases())
                ->reject(fn (LinkType $type) => $type === LinkType::COLLABORATION)
                ->values()
                ->all()
        );

        return [
            'enum_type' => $type->value,
            'link' => $this->generateUrl($type),
        ];
    }

    /**
     * Link used in Registration.
     * There can be more than one collaboration
     */
    public function collaboration(): static
    {
        return $this->state(fn (): array => [
            'enum_type' => LinkType::COLLABORATION->value,
            'link' => fake()->url(),
        ]);
    }

    /**
     * Payload of links for Registration.
     *
     * Registration allows multiple collaborations.
     *
     * @return array<int, array<string, string>>
     */
    public static function collaborationPayload(int $count = 2): array
    {
        return self::new()
            ->collaboration()
            ->count($count)
            ->make()
            ->map(fn (Link $link): array => [
                'enum_type' => $link->enum_type->value,
                'link' => $link->link,
            ])
            ->toArray();
    }

    /**
     * Generates a URL consistent with the type.
     */
    private function generateUrl(LinkType $type): string
    {
        return match ($type) {
            LinkType::WEBSITE => fake()->url(),
            LinkType::INSTAGRAM => 'https://instagram.com/'.fake()->userName(),
            LinkType::FACEBOOK => 'https://facebook.com/'.fake()->userName(),
            LinkType::TIKTOK => 'https://tiktok.com/@'.fake()->userName(),
            LinkType::YOUTUBE => 'https://youtube.com/@'.fake()->userName(),
            LinkType::VIMEO => 'https://vimeo.com/'.fake()->numberBetween(100000, 999999),
            LinkType::BANDCAMP => 'https://'.fake()->userName().'.bandcamp.com',
            LinkType::SOUNDCLOUD => 'https://soundcloud.com/'.fake()->userName(),
            LinkType::SPOTIFY => 'https://open.spotify.com/artist/'.fake()->uuid(),
            LinkType::LINKEDIN => 'https://linkedin.com/in/'.fake()->userName(),
            LinkType::X => 'https://x.com/'.fake()->userName(),
            LinkType::DEVIANTART => 'https://'.fake()->userName().'.deviantart.com',
            LinkType::TWITCH => 'https://twitch.tv/'.fake()->userName(),
            LinkType::PINTEREST => 'https://pinterest.com/'.fake()->userName(),
            LinkType::FLICKR => 'https://flickr.com/photos/'.fake()->userName(),
            LinkType::OTHER => fake()->url(),
            LinkType::COLLABORATION => fake()->url(),
        };
    }
}
