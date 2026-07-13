<?php

namespace Database\Factories;

use App\Enums\ApprovalStatus;
use App\Models\ArtistRegistrationRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArtistRegistrationRequest>
 */
class ArtistRegistrationRequestFactory extends Factory
{
    protected $model = ArtistRegistrationRequest::class;

    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'artist_name' => $this->faker->name(),
            'show_artist_name' => true,
            'birth_date' => $this->faker->date(),
            'email' => $this->faker->unique()->safeEmail(),
            'display_contact_button' => false,
            'phone' => '+41 '.$this->faker->numerify('## ### ## ##'),
            'residence_location' => 'Neuchâtel',
            'locality' => 'Neuchâtel',
            'commune' => null,
            'canton_link' => null,
            'main_domain' => $this->faker->randomElement(['Musique', 'Arts visuels', 'Spectacle vivant']),
            'main_activity' => $this->faker->randomElement(['Peintre', 'Chanteur-euse', 'Comédien-ne']),
            'main_activity_other' => null,
            'main_activities' => $this->faker->paragraph(2),
            'training' => $this->faker->sentence(),
            'paid_activity' => $this->faker->sentence(),
            'recognition' => null,
            'recent_achievement' => null,
            'last_activity' => $this->faker->year().' — '.$this->faker->city(),
            'documents_info' => null,
            'links' => [],
            'documents' => [],
            'status' => ApprovalStatus::Pending,
        ];
    }
}
