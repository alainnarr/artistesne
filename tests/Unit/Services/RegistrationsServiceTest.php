<?php

namespace Tests\Unit\Services;

use App\Database\Models\Activity;
use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Enums\DisciplineType;
use App\Enums\RegistrationStatus;
use App\Services\RegistrationsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Tests\Unit\Services\Traits\Trait_Seed;

class RegistrationsServiceTest extends TestCase
{
    use RefreshDatabase;
    use Trait_Seed;

    private RegistrationsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->service = $this->app->make(RegistrationsService::class);
    }

    private function validData(array $overrides = []): array
    {
        $discipline = $this->seedDiscipline(DisciplineType::MAIN);

        return array_merge([
            'real_name' => 'John Doe',
            'artist_name' => 'JD',
            'url' => 'https://example.com',
            'birth_date' => '1990-01-01',
            'email' => 'john-' . uniqid() . '@example.com',
            'phone' => '123456789',
            'residence_location' => 'Bern',
            'discipline_main' => $discipline->id,
            'enum_status' => RegistrationStatus::PENDING->value,
        ], $overrides);
    }

    private function makeDisciplineWithActivity(): array
    {
        $discipline = $this->seedDiscipline(DisciplineType::MAIN);
        $activity = $this->seedActivity($discipline);

        return [$discipline, $activity];
    }

    public function testCreatePersistsRegistration(): void
    {
        $data = $this->validData();

        $registration = $this->service->create($data);

        $this->assertInstanceOf(Registration::class, $registration);
        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'email' => $data['email'],
        ]);
    }

    public function testCreateAttachesActivities(): void
    {
        [$discipline, $activity] = $this->makeDisciplineWithActivity();

        $data = $this->validData([
            'discipline_main' => $discipline->id,
            'activities' => [$activity->id],
        ]);

        $registration = $this->service->create($data);

        $this->assertDatabaseHas('activities_registrations', [
            'registration_id' => $registration->id,
            'activity_id' => $activity->id,
        ]);
    }

    public function testCreateStoresFiles(): void
    {
        $data = $this->validData([
            'files' => [
                UploadedFile::fake()->image('photo.jpg'),
            ],
        ]);

        $registration = $this->service->create($data);

        $this->assertDatabaseHas('repositories', [
            'repositoryable_id' => $registration->id,
            'repositoryable_type' => Registration::class,
            'name' => 'photo.jpg',
        ]);
    }

    public function testCreateThrowsValidationExceptionWhenDataInvalid(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create([]);
    }

    public function testUpdatePersistsChanges(): void
    {
        $registration = $this->service->create($this->validData());

        $updated = $this->service->update($registration, [
            'artist_name' => 'New Name',
        ]);

        $this->assertEquals('New Name', $updated->artist_name);
        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'artist_name' => 'New Name',
        ]);
    }

    public function testUpdateSyncsActivities(): void
    {
        [$discipline, $activityOne] = $this->makeDisciplineWithActivity();

        $activityTwo = Activity::create([
            'discipline_id' => $discipline->id,
            'code' => 'activity-' . uniqid(),
            'label' => 'Singing',
        ]);

        $registration = $this->service->create($this->validData([
            'discipline_main' => $discipline->id,
            'activities' => [$activityOne->id],
        ]));

        $this->service->update($registration, [
            'activities' => [$activityTwo->id],
        ]);

        $this->assertDatabaseMissing('activities_registrations', [
            'registration_id' => $registration->id,
            'activity_id' => $activityOne->id,
        ]);
        $this->assertDatabaseHas('activities_registrations', [
            'registration_id' => $registration->id,
            'activity_id' => $activityTwo->id,
        ]);
    }

    public function testUpdateSyncsFiles(): void
    {
        $registration = $this->service->create($this->validData([
            'files' => [UploadedFile::fake()->image('old.jpg')],
        ]));

        $oldRepositoryId = $registration->fresh(['repositories'])->repositories->first()->id;

        $this->service->update($registration, [
            'files' => [
                'keep' => [],
                'new' => [UploadedFile::fake()->image('new.jpg')],
            ],
        ]);

        $this->assertDatabaseMissing('repositories', [
            'id' => $oldRepositoryId,
        ]);
        $this->assertDatabaseHas('repositories', [
            'repositoryable_id' => $registration->id,
            'repositoryable_type' => Registration::class,
            'name' => 'new.jpg',
        ]);
    }

    public function testUpdateDoesNotTouchActivitiesWhenKeyNotPresent(): void
    {
        [$discipline, $activity] = $this->makeDisciplineWithActivity();

        $registration = $this->service->create($this->validData([
            'discipline_main' => $discipline->id,
            'activities' => [$activity->id],
        ]));

        $this->service->update($registration, [
            'artist_name' => 'Another Name',
        ]);

        $this->assertDatabaseHas('activities_registrations', [
            'registration_id' => $registration->id,
            'activity_id' => $activity->id,
        ]);
    }

    public function testChangeStatusUpdatesRegistrationFields(): void
    {
        $registration = $this->service->create($this->validData());

        $updated = $this->service->changeStatus(
            $registration,
            RegistrationStatus::APPROVED,
            'Looks good'
        );

        $this->assertEquals(RegistrationStatus::APPROVED, $updated->enum_status);
        $this->assertNotNull($updated->reviewed_at);
        $this->assertEquals('Looks good', $updated->review_notes);

        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'review_notes' => 'Looks good',
        ]);

        $this->assertDatabaseHas('newusers', [
            'email' => $registration->email,
            'name' => $registration->name,
        ]);

        $this->assertDatabaseHas('newartists', [
            'registration_id' => $registration->id,
            'artist_name' => $registration->name,
            'email' => $registration->email,
        ]);
    }
}
