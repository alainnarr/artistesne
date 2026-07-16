<?php

namespace Tests\Unit\Database\Models;

use App\Database\Models\Discipline;
use App\Enums\DisciplineType;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisciplineTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel(): Discipline
    {
        return new Discipline;
    }

    public function test_get_table_returns_table_name(): void
    {
        $model = $this->makeModel();

        $this->assertEquals('disciplines', $model->getTable());
    }

    public function test_get_fillable_returns_array(): void
    {
        $model = $this->makeModel();

        $this->assertEquals(['code', 'label', 'enum_type'], $model->getFillable());
    }

    public function test_get_updatable_returns_array(): void
    {
        $model = $this->makeModel();

        $this->assertEquals([], $model->getUpdatable());
    }

    public function test_casts_enum_type_to_discipline_type(): void
    {
        $model = $this->makeModel();
        $casts = $model->getCasts();

        $this->assertArrayHasKey('enum_type', $casts);
        $this->assertEquals(DisciplineType::class, $casts['enum_type']);
    }

    public function test_get_rules_returns_empty_array(): void
    {
        $rules = Discipline::getRules();

        $this->assertCount(3, $rules);
        $this->assertEquals('required|string|max:50|unique:disciplines,code,,id', $rules['code']);
        $this->assertEquals('required|string|max:100', $rules['label']);
        $this->assertIsArray($rules['enum_type']);
        $this->assertCount(2, $rules['enum_type']);
        $this->assertEquals('required', $rules['enum_type'][0]);
        $this->assertInstanceOf(Enum::class, $rules['enum_type'][1]);
    }

    public function test_get_rules_returns_empty_array_when_filtering_fields(): void
    {
        $rules = Discipline::getRules(['code']);

        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('code', $rules);
        $this->assertArrayNotHasKey('label', $rules);
        $this->assertArrayNotHasKey('enum_type', $rules);
        $this->assertEquals('required|string|max:50|unique:disciplines,code,,id', $rules['code']);
    }

    public function testGetRulesReturnsRulesForUpdate(): void
    {
        $rules = Discipline::getRules([], ['id' => 15]);

        $this->assertEquals('required|string|max:50|unique:disciplines,code,15,id', $rules['code']);
    }

    public function test_enum_type_attribute_returns_enum_instance(): void
    {
        $model = $this->makeModel();
        $model->enum_type = DisciplineType::MAIN->value;

        $this->assertInstanceOf(DisciplineType::class, $model->enum_type);
        $this->assertEquals(DisciplineType::MAIN, $model->enum_type);
    }

    public function test_activities_relation(): void
    {
        $model = $this->makeModel();
        $relation = $model->activities();

        $this->assertInstanceOf(HasMany::class, $relation);
    }
}
