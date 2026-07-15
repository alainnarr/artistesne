<?php

namespace Tests\Unit\Database\Models;

use App\Database\Models\Activity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

        $this->assertEquals([], $rules);
    }

    public function test_get_rules_returns_empty_array_when_filtering_fields(): void
    {
        $rules = Activity::getRules(['code']);

        $this->assertEquals([], $rules);
    }

    public function test_discipline_relation(): void
    {
        $model = $this->makeModel();
        $relation = $model->discipline();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    // TODO: Uncomment this test when the Synonym model is implemented
    // public function testSynonymsRelation(): void
    // {
    //     $model = $this->makeModel();
    //     $relation = $model->synonyms();

    //     $this->assertInstanceOf(HasMany::class, $relation);
    // }
}
