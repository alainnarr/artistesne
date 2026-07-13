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
use App\Models\User;

trait Trait_Seed
{
    public function seedDiscipline(DisciplineType $type = DisciplineType::MAIN): Discipline
    {
        $disciplineMain = Discipline::updateOrCreate(['code' => 'main',], [
            'code' => 'main',
            'label' => 'Main Discipline',
            'enum_type' => DisciplineType::MAIN,
        ]);

        $disciplineSecondary = Discipline::updateOrCreate(['code' => 'secondary',], [
            'code' => 'secondary',
            'label' => 'Secondary Discipline',
            'enum_type' => DisciplineType::SECONDARY,
        ]);

        return $type === DisciplineType::MAIN ? $disciplineMain : $disciplineSecondary;
    }

    public function seedActivity(Discipline $discipline): Activity
    {
        return Activity::updateOrCreate(['code' => 'activity',], [
            'discipline_id' => $discipline->id,
            'code' => 'activity',
            'label' => 'Activity',
        ]);
    }

    public function seedRegistration(): Registration
    {
        $disciplineMain = $this->seedDiscipline(DisciplineType::MAIN);
        $disciplineSecondary = $this->seedDiscipline(DisciplineType::SECONDARY);

        return Registration::updateOrCreate(['email' => 'test@test.com',], [
            'real_name' => 'Real Name',
            'artist_name' => 'Test',
            'url' => 'ne.ch/test',
            'birth_date' => '2020/01/01',
            'phone' => '+41000000000',
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
        ]);
    }

    public function seedUser(): User
    {
        return User::updateOrCreate(['email' => 'test@test.com',], [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function seedArtist(): Artist
    {
        $user = $this->seedUser();
        $registration = $this->seedRegistration();
        $disciplineSecondary = $this->seedDiscipline(DisciplineType::SECONDARY);

        return Artist::updateOrCreate(['registration_id' => $registration->id,], [
            'user_id' => $user->id,
            'artist_name' => 'Test',
            'email' => 'test@test.com',
            'phone' => '+41000000000',
            'rep_image' => null,
            'biography' => 'Biography',
            'city' => 'Neuchatel',
            'discipline_secondary' => $disciplineSecondary->id,
            'enum_status' => ArtistStatus::Published->value,
            'enum_show_contact' => ArtistShowContact::SHOW->value,
        ]);
    }
}
