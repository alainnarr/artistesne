<?php

namespace Tests\Unit\Database;

use App\Database\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel()
    {
        return new class extends Model {
            protected $table = 'test_table';
            protected $auditable = '_test_table';
            protected $fillable = ['f1', 'f2'];
            protected $updatable = ['u1', 'u2'];
        };
    }

    public function testGetTableReturnsTableName()
    {
        $model = $this->makeModel();

        $this->assertEquals('test_table', $model->getTable());
    }

    public function testGetAuditableReturnsAuditableName()
    {
        $model = $this->makeModel();

        $this->assertEquals('_test_table', $model->getAuditable());
    }

    public function testGetFillableReturnsArray()
    {
        $model = $this->makeModel();

        $this->assertEquals(['f1', 'f2'], $model->getFillable());
    }

    public function testGetUpdatableReturnsArray()
    {
        $model = $this->makeModel();

        $this->assertEquals(['u1', 'u2'], $model->getUpdatable());
    }

    public function testGetRulesReturnsEmptyArray()
    {
        $rules = Model::getRules();

        $this->assertIsArray($rules);
        $this->assertEmpty($rules);
    }

    public function test_created_by_relation(): void
    {
        $model = $this->makeModel();

        $relation = $model->createdBy();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());

        $this->assertEquals('created_by', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
    }

    public function test_updated_by_relation(): void
    {
        $model = $this->makeModel();

        $relation = $model->updatedBy();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());

        $this->assertEquals('updated_by', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
    }
}
