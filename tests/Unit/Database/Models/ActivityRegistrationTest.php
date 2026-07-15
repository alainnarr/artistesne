<?php

namespace Tests\Unit\Database\Models;

use App\Database\Models\ActivityRegistration;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel(): ActivityRegistration
    {
        return new ActivityRegistration;
    }

    public function test_get_table_returns_table_name(): void
    {
        $model = $this->makeModel();

        $this->assertEquals('activities_registrations', $model->getTable());
    }

    public function test_get_fillable_returns_array(): void
    {
        $model = $this->makeModel();

        $this->assertEquals(['activity_id', 'registration_id'], $model->getFillable());
    }

    public function test_get_updatable_returns_empty_array(): void
    {
        $model = $this->makeModel();

        $this->assertEquals([], $model->getUpdatable());
    }

    public function test_get_rules_returns_all_rules_when_fields_empty(): void
    {
        $rules = ActivityRegistration::getRules();

        $this->assertEquals([
            'activity_id' => 'required|integer|exists:activities,id',
            'registration_id' => 'required|integer|exists:registrations,id',
        ], $rules);
    }

    public function test_get_rules_filters_by_fields(): void
    {
        $rules = ActivityRegistration::getRules(['activity_id']);

        $this->assertEquals(['activity_id'], array_keys($rules));
    }

    public function test_activities_relation(): void
    {
        $model = $this->makeModel();
        $relation = $model->activities();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('activity_id', $relation->getForeignKeyName());
    }

    public function test_registrations_relation(): void
    {
        $model = $this->makeModel();
        $relation = $model->registrations();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('registration_id', $relation->getForeignKeyName());
    }
}
