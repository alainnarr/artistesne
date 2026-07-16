<?php

namespace Database\Factories;

use App\Database\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'email' => $this->faker->unique()->safeEmail(),
            'name' => $this->faker->name(),
            'enum_role' => UserRole::Artist,
            'adfs_id' => null,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'enum_role' => UserRole::Admin,
            'adfs_id' => null,
        ]);
    }

    /**
     * Set the adfs_id for this user (simulates an AD FS-authenticated admin).
     */
    public function withAdfsId(string $adfsId = 'adfs-sub-test-1234'): static
    {
        return $this->state(fn (array $attributes) => [
            'adfs_id' => $adfsId,
        ]);
    }

    public function artist(): static
    {
        return $this->state(fn (array $attributes) => [
            'enum_role' => UserRole::Artist,
            'password' => null,
        ]);
    }
}
