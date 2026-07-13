<?php

namespace Database\Factories;

use App\Enums\ApprovalStatus;
use App\Models\Artist;
use App\Models\ArtistChangeRequest;
use App\Models\User;
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
            'status' => ApprovalStatus::Pending,
        ];
    }
}
