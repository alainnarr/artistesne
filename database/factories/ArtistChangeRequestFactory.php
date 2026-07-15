<?php

namespace Database\Factories;

use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\User;
use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArtistChangeRequest>
 */
class ArtistChangeRequestFactory extends Factory
{
    protected $model = ArtistChangeRequest::class;

    public function definition(): array
    {
        return [
            'artist_id' => Artist::factory(),
            'submitted_by' => User::factory()->artist(),
            'payload' => [
                'biography' => '<p>'.$this->faker->paragraph(5).'</p>',
            ],
            'status' => ApprovalStatus::Pending->value,
        ];
    }
}
