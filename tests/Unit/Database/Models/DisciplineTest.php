<?php

namespace Tests\Unit\Database;

use App\Database\Models\Discipline;
use App\Enums\DisciplineType;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rules\Enum;
use Tests\TestCase;

class DisciplineTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel(): Discipline
    {
        return new Discipline();
    }

    public function testGetTableReturnsTableName(): void
    {
        $model = $this->makeModel();

        $this->assertEquals('disciplines', $model->getTable());
    }

    public function testGetFillableReturnsArray(): void
    {
        $model = $this->makeModel();

        $this->assertEquals(['code', 'label', 'enum_type'], $model->getFillable());
    }

    public function testGetUpdatableReturnsArray(): void
    {
        $model = $this->makeModel();

        $this->assertEquals([], $model->getUpdatable());
    }

    public function testCastsEnumTypeToDisciplineType(): void
    {
        $model = $this->makeModel();
        $casts = $model->getCasts();

        $this->assertArrayHasKey('enum_type', $casts);
        $this->assertEquals(DisciplineType::class, $casts['enum_type']);
    }

    public function testGetRulesReturnsValidationRules(): void
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

    public function testGetRulesReturnsFilteredFields(): void
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

    public function testEnumTypeAttributeReturnsEnumInstance(): void
    {
        $model = $this->makeModel();
        $model->enum_type = DisciplineType::MAIN->value;

        $this->assertInstanceOf(DisciplineType::class, $model->enum_type);
        $this->assertEquals(DisciplineType::MAIN, $model->enum_type);
    }

    public function testActivitiesRelation(): void
    {
        $relation = $this->makeModel()->activities();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    public function testArtistsRelation(): void
    {
        $relation = $this->makeModel()->artists();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    public function testMainRegistrationsRelation(): void
    {
        $relation = $this->makeModel()->mainRegistrations();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    public function testSecondaryRegistrationsRelation(): void
    {
        $relation = $this->makeModel()->secondaryRegistrations();

        $this->assertInstanceOf(HasMany::class, $relation);
    }
}
