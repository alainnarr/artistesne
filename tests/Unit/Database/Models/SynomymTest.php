<?php

namespace Tests\Unit\Database;

use App\Database\Models\Synonym;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SynonymTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel(): Synonym
    {
        return new Synonym();
    }

    public function testGetTableReturnsTableName(): void
    {
        $model = $this->makeModel();

        $this->assertEquals('synonyms', $model->getTable());
    }

    public function testGetFillableReturnsArray(): void
    {
        $model = $this->makeModel();

        $this->assertEquals(['activity_id', 'label',], $model->getFillable());
    }

    public function testGetUpdatableReturnsArray(): void
    {
        $model = $this->makeModel();

        $this->assertEquals([], $model->getUpdatable());
    }

    public function testGetRulesReturnsEmptyArray(): void
    {
        $rules = Synonym::getRules();

        $this->assertEquals([
            'activity_id' => 'required|integer|exists:activities,id',
            'label' => 'required|string|max:125',
        ], $rules);
    }

    public function testGetRulesReturnsFilteredArray(): void
    {
        $rules = Synonym::getRules(['label']);

        $this->assertEquals([
            'label' => 'required|string|max:125',
        ], $rules);
    }

    public function testGetRulesReturnsEmptyArrayWhenNoMatchingFields(): void
    {
        $rules = Synonym::getRules(['non_existing_field']);

        $this->assertEquals([], $rules);
    }

    public function testActivityRelation(): void
    {
        $model = $this->makeModel();
        $relation = $model->activity();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }
}
