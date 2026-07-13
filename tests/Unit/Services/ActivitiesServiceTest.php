<?php

namespace Tests\Unit\Services;

use App\Database\Models\Activity;
use App\Database\Models\ActivityRegistration;
use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Enums\RegistrationStatus;
use App\Services\ActivitiesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\DisciplineType;

class ActivitiesServiceTest extends TestCase
{
    use RefreshDatabase;

    private ActivitiesService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ActivitiesService();
    }

    private function makeRegistration(): Registration
    {
        $discipline = Discipline::create([
            'label' => 'Music',
            'code' => 'music',
            'enum_type' => DisciplineType::MAIN->value,
        ]);

        return Registration::create([
            'real_name' => 'John Doe',
            'artist_name' => 'JD',
            'url' => 'https://example.com',
            'birth_date' => '1990-01-01',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'residence_location' => 'Bern',
            'discipline_main' => $discipline->id,
            'enum_status' => RegistrationStatus::PENDING->value,
        ]);
    }

    private function makeActivity(int $disciplineId, ?string $code = null): Activity
    {
        return Activity::create([
            'discipline_id' => $disciplineId,
            'code' => $code ?? 'activity-' . uniqid(),
            'label' => 'Dance',
        ]);
    }

    public function testAttachCreatesActivityRegistration(): void
    {
        $registration = $this->makeRegistration();
        $activity = $this->makeActivity($registration->discipline_main);

        $result = $this->service->attach($registration, $activity->id);

        $this->assertInstanceOf(ActivityRegistration::class, $result);
        $this->assertDatabaseHas('activities_registrations', [
            'activity_id' => $activity->id,
            'registration_id' => $registration->id,
        ]);
    }

    public function testAttachIsIdempotent(): void
    {
        $registration = $this->makeRegistration();
        $activity = $this->makeActivity($registration->discipline_main);

        $this->service->attach($registration, $activity->id);
        $this->service->attach($registration, $activity->id);

        $this->assertEquals(1, ActivityRegistration::where([
            'activity_id' => $activity->id,
            'registration_id' => $registration->id,
        ])->count());
    }

    public function testDetachRemovesActivityRegistration(): void
    {
        $registration = $this->makeRegistration();
        $activity = $this->makeActivity($registration->discipline_main);

        $this->service->attach($registration, $activity->id);
        $result = $this->service->detach($registration, $activity->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('activities_registrations', [
            'activity_id' => $activity->id,
            'registration_id' => $registration->id,
        ]);
    }

    public function testDetachReturnsFalseWhenNothingToDelete(): void
    {
        $registration = $this->makeRegistration();
        $activity = $this->makeActivity($registration->discipline_main);

        $result = $this->service->detach($registration, $activity->id);

        $this->assertFalse($result);
    }

    public function testAttachMultipleCreatesAllRecords(): void
    {
        $registration = $this->makeRegistration();
        $activityOne = $this->makeActivity($registration->discipline_main);
        $activityTwo = $this->makeActivity($registration->discipline_main);

        $records = $this->service->attachMultiple($registration, [$activityOne->id, $activityTwo->id]);

        $this->assertCount(2, $records);
        $this->assertEquals(2, ActivityRegistration::where('registration_id', $registration->id)->count());
    }

    public function testDetachMultipleRemovesAllRecords(): void
    {
        $registration = $this->makeRegistration();
        $activityOne = $this->makeActivity($registration->discipline_main);
        $activityTwo = $this->makeActivity($registration->discipline_main);

        $this->service->attachMultiple($registration, [$activityOne->id, $activityTwo->id]);
        $deleted = $this->service->detachMultiple($registration, [$activityOne->id, $activityTwo->id]);

        $this->assertEquals(2, $deleted);
        $this->assertEquals(0, ActivityRegistration::where('registration_id', $registration->id)->count());
    }

    public function testSyncAttachesNewAndDetachesRemoved(): void
    {
        $registration = $this->makeRegistration();
        $activityOne = $this->makeActivity($registration->discipline_main);
        $activityTwo = $this->makeActivity($registration->discipline_main);
        $activityThree = $this->makeActivity($registration->discipline_main);

        $this->service->attachMultiple($registration, [$activityOne->id, $activityTwo->id]);
        $this->service->sync($registration, [$activityTwo->id, $activityThree->id]);

        $currentIds = ActivityRegistration::where('registration_id', $registration->id)
            ->pluck('activity_id')
            ->sort()
            ->values()
            ->toArray();

        $this->assertEquals(
            collect([$activityTwo->id, $activityThree->id])->sort()->values()->toArray(),
            $currentIds
        );
    }

    public function testSyncWithEmptyArrayDetachesAll(): void
    {
        $registration = $this->makeRegistration();
        $activity = $this->makeActivity($registration->discipline_main);

        $this->service->attachMultiple($registration, [$activity->id]);
        $this->service->sync($registration, []);

        $this->assertEquals(0, ActivityRegistration::where('registration_id', $registration->id)->count());
    }
}
