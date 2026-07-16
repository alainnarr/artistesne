<?php

namespace Tests\Unit\Database\Models;

use App\Database\Models\Activity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel(): Activity
    {
        return new Activity;
    }

    public function test_get_table_returns_table_name(): void
    {
        $model = $this->makeModel();

        $this->assertEquals('activities', $model->getTable());
    }

    public function test_get_fillable_returns_array(): void
    {
        $model = $this->makeModel();

        $this->assertEquals(['discipline_id', 'code', 'label'], $model->getFillable());
    }

    public function test_get_updatable_returns_array(): void
    {
        $model = $this->makeModel();

        $this->assertEquals([], $model->getUpdatable());
    }

    public function test_get_rules_returns_empty_array(): void
    {
        $rules = Activity::getRules();

        $this->assertCount(3, $rules);

        $this->assertEquals('required|exists:disciplines,id', $rules['discipline_id']);
        $this->assertEquals('required|string|max:50|unique:activities,code,,id', $rules['code']);
        $this->assertEquals('required|string|max:100', $rules['label']);
    }

    public function test_get_rules_returns_empty_array_when_filtering_fields(): void
    {
        $rules = Activity::getRules(['code']);

        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('code', $rules);
        $this->assertArrayNotHasKey('discipline_id', $rules);
        $this->assertArrayNotHasKey('label', $rules);
        $this->assertEquals('required|string|max:50|unique:activities,code,,id', $rules['code']);
    }

    public function testGetRulesReturnsRulesForUpdate(): void
    {
        $rules = Activity::getRules([], ['id' => 15]);

        $this->assertEquals('required|string|max:50|unique:activities,code,15,id', $rules['code']);
    }

    public function test_discipline_relation(): void
    {
        $relation = $this->makeModel()->discipline();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    public function testSynonymsRelation(): void
    {
        $relation = $this->makeModel()->synonyms();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    public function testRegistrationsRelation(): void
    {
        $relation = $this->makeModel()->registrations();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
    }

    public function testRegistrationActivitiesRelation(): void
    {
        $relation = $this->makeModel()->registrationActivities();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    public function testArtistsRelation(): void
    {
        $relation = $this->makeModel()->artists();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
    }

    public function testArtistActivitiesRelation(): void
    {
        $relation = $this->makeModel()->artistActivities();

        $this->assertInstanceOf(HasMany::class, $relation);
    }
}
