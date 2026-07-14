<?php

namespace Tests\Unit\Services\Traits;

use App\Database\Models\Discipline;
use App\Enums\DisciplineType;
use App\Database\Models\Activity;
use App\Database\Models\Registration;
use App\Enums\RegistrationStatus;
use App\Database\Models\Artist;
use App\Enums\ArtistShowContact;
use App\Enums\ArtistStatus;
use App\Database\Models\User;
use App\Database\Models\ArtistChangeRequest;
use App\Enums\ArtistChangeRequestStatus;
use App\Enums\UserRole;
use Illuminate\Support\Str;

trait Trait_Seed
{
    public function seedDiscipline(DisciplineType $type = DisciplineType::MAIN, array $attributes = []): Discipline
    {
        $code = $attributes['code'] ?? ($type === DisciplineType::MAIN ? 'main-' . Str::random(8) : 'secondary-' . Str::random(8));

        return Discipline::updateOrCreate(
            ['code' => $code],
            array_merge([
                'code' => $code,
                'label' => $type === DisciplineType::MAIN ? 'Main Discipline' : 'Secondary Discipline',
                'enum_type' => $type,
            ], $attributes)
        );
    }

    public function seedActivity(Discipline $discipline, array $attributes = []): Activity
    {
        $code = $attributes['code'] ?? 'activity-' . $discipline->id;

        return Activity::updateOrCreate(
            ['code' => $code, 'discipline_id' => $discipline->id],
            array_merge([
                'discipline_id' => $discipline->id,
                'code' => $code,
                'label' => 'Activity',
            ], $attributes)
        );
    }

    public function seedRegistration(array $attributes = []): Registration
    {
        $email = $attributes['email'] ?? 'test-' . Str::random(10) . '@test.com';

        $disciplineMain = $this->seedDiscipline(DisciplineType::MAIN);
        $disciplineSecondary = $this->seedDiscipline(DisciplineType::SECONDARY);

        return Registration::updateOrCreate(
            ['email' => $email],
            array_merge([
                            'real_name' => 'Real Name',
                            'artist_name' => 'Test',
                            'url' => 'ne.ch/test',
                            'birth_date' => '2020/01/01',
                            'phone' => '+41000000000',
                            'email' => $email,
                            'residence_location' => 'Residence',
                            'locality' => 'City',
                            'canton_link' => 'Canton link',
                            'discipline_main' => $disciplineMain->id,
                            'discipline_secondary' => $disciplineSecondary->id,
                            'training' => 'Training',
                            'paid_work' => 'Paid work',
                            'recognition' => 'Recognition',
                            'recent_achievements' => 'Recent achievements',
                            'last_work' => 'Last work',
                            'enum_status' => RegistrationStatus::APPROVED->value,
                        ], $attributes)
        );
    }

    public function seedUser(array $attributes = []): User
    {
        $email = $attributes['email'] ?? 'test-' . Str::random(10) . '@test.com';

        return User::updateOrCreate(
            ['email' => $email],
            array_merge([
                            'uuid' => (string) Str::uuid(),
                            'email' => $email,
                            'name' => $attributes['name'] ?? 'Name',
                            'enum_role' => $attributes['enum_role'] ?? UserRole::Artist,
                            'adfs_id' => $attributes['adfs_id'] ?? null,
                            'magic_link' => $attributes['magic_link'] ?? null,
                        ], $attributes)
        );
    }

    public function seedArtist(array $attributes = []): Artist
    {
        $email = $attributes['email'] ?? 'test-' . Str::random(10) . '@test.com';

        $user = $this->seedUser(['email' => $email]);
        $registration = $this->seedRegistration(['email' => $email]);
        $disciplineSecondary = $this->seedDiscipline(DisciplineType::SECONDARY);

        return Artist::updateOrCreate(
            ['registration_id' => $registration->id],
            array_merge([
                            'user_id' => $user->id,
                            'artist_name' => 'Test',
                            'email' => $email,
                            'phone' => '+41000000000',
                            'rep_image' => null,
                            'biography' => 'Biography',
                            'city' => 'Neuchâtel',
                            'discipline_secondary' => $disciplineSecondary->id,
                            'enum_status' => ArtistStatus::Published->value,
                            'enum_show_contact' => ArtistShowContact::SHOW->value,
                        ], $attributes)
        );
    }

    public function seedArtistChangeRequest(
        ?Artist $artist = null,
        ArtistChangeRequestStatus $status = ArtistChangeRequestStatus::PENDING,
        array $payload = ['artist_name' => 'Changed'],
    ): ArtistChangeRequest {
        $artist ??= $this->seedArtist();

        return ArtistChangeRequest::create([
            'artist_id' => $artist->id,
            'payload' => json_encode($payload),
            'enum_status' => $status,
        ]);
    }
}
