<?php

namespace Tests\Unit\Services;

use App\Database\Models\Discipline;
use App\Database\Models\Activity;
use App\Database\Models\Synonym;
use App\Services\SynonymsService;
use App\Enums\DisciplineType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\Services\Traits\Trait_Seed;

class SynonymsServiceTest extends TestCase
{
    use RefreshDatabase;
    use Trait_Seed;

    private SynonymsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new SynonymsService();
    }

    private function createActivity(): Activity
    {
        $discipline = $this->seedDiscipline();
        return $this->seedActivity($discipline);
    }

    public function testCreateCreatesSynonymForActivity(): void
    {
        $activity = $this->createActivity();
        $synonym = $this->service->create($activity, 'Running');

        $this->assertInstanceOf(Synonym::class, $synonym);
        $this->assertDatabaseHas('synonyms', ['activity_id' => $activity->id, 'label' => 'Running']);
    }

    public function testUpdateChangesExistingSynonym(): void
    {
        $activity = $this->createActivity();
        $this->service->create($activity, 'Running');
        $synonym = $this->service->update($activity, 'Running', 'Jogging');

        $this->assertEquals('Jogging', $synonym->label);
        $this->assertDatabaseHas('synonyms', ['id' => $synonym->id, 'label' => 'Jogging']);
    }

    public function testDeleteRemovesSynonym(): void
    {
        $activity = $this->createActivity();
        $synonym = $this->service->create($activity, 'Running');
        $this->service->delete($activity, 'Running');

        $this->assertDatabaseMissing('synonyms', ['id' => $synonym->id]);
    }
}
