<?php

namespace Tests\Unit\Database;

use App\Database\Models\ActivityRegistration;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel(): ActivityRegistration
    {
        return new ActivityRegistration();
    }

    public function testGetTableReturnsTableName(): void
    {
        $model = $this->makeModel();

        $this->assertEquals('activities_registrations', $model->getTable());
    }

    public function testGetFillableReturnsArray(): void
    {
        $model = $this->makeModel();

        $this->assertEquals(['activity_id', 'registration_id'], $model->getFillable());
    }

    public function testGetUpdatableReturnsEmptyArray(): void
    {
        $model = $this->makeModel();

        $this->assertEquals([], $model->getUpdatable());
    }

    public function testGetRulesReturnsAllRulesWhenFieldsEmpty(): void
    {
        $rules = ActivityRegistration::getRules();

        $this->assertEquals([
            'activity_id' => 'required|integer|exists:activities,id',
            'registration_id' => 'required|integer|exists:registrations,id',
        ], $rules);
    }

    public function testGetRulesFiltersByFields(): void
    {
        $rules = ActivityRegistration::getRules(['activity_id']);

        $this->assertEquals(['activity_id'], array_keys($rules));
    }

    public function testActivitiesRelation(): void
    {
        $model = $this->makeModel();
        $relation = $model->activities();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('activity_id', $relation->getForeignKeyName());
    }

    public function testRegistrationsRelation(): void
    {
        $model = $this->makeModel();
        $relation = $model->registrations();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('registration_id', $relation->getForeignKeyName());
    }
}
