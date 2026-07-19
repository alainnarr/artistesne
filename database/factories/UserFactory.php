<?php

namespace Database\Factories;

use App\Database\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'enum_role' => UserRole::ARTIST,
            'adfs_id' => null,
            'magic_link_token' => null,
            'magic_link_sent_at' => null,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (): array => [
            'enum_role' => UserRole::ADMIN,
        ]);
    }

    public function artist(): static
    {
        return $this->state(fn (): array => [
            'enum_role' => UserRole::ARTIST,
        ]);
    }

    public function withAdfsId(string $adfsId): static
    {
        return $this->state(fn (): array => [
            'adfs_id' => $adfsId,
        ]);
    }

    public function withMagicLink(): static
    {
        return $this->state(fn (): array => [
            'magic_link_token' => Str::random(64),
            'magic_link_sent_at' => now(),
        ]);
    }
}
