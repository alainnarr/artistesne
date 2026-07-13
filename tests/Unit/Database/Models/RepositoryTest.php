<?php

namespace Tests\Unit\Database;

use App\Database\Models\Repository;
use App\Enums\RepositoryDisk;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Enum;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel(): Repository
    {
        return new Repository();
    }

    public function testGetTableReturnsTableName(): void
    {
        $model = $this->makeModel();

        $this->assertEquals('repositories', $model->getTable());
    }

    public function testGetFillableReturnsArray(): void
    {
        $model = $this->makeModel();

        $this->assertEquals(['name', 'file_type', 'size', 'enum_disk', 'path', ], $model->getFillable());
    }

    public function testGetUpdatableReturnsArray(): void
    {
        $model = $this->makeModel();

        $this->assertEquals(['name', 'file_type', 'size', 'enum_disk', 'path', ], $model->getUpdatable());
    }

    public function testCastsEnumDiskToRepositoryDisk(): void
    {
        $model = $this->makeModel();
        $casts = $model->getCasts();

        $this->assertArrayHasKey('enum_disk', $casts);
        $this->assertEquals(RepositoryDisk::class, $casts['enum_disk']);
    }

    public function testGetRulesReturnsAllRules(): void
    {
        $rules = Repository::getRules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('file_type', $rules);
        $this->assertArrayHasKey('size', $rules);
        $this->assertArrayHasKey('enum_disk', $rules);
        $this->assertArrayHasKey('path', $rules);
    }

    public function testEnumDiskRuleUsesRepositoryDiskEnum(): void
    {
        $rules = Repository::getRules();

        $this->assertIsArray($rules['enum_disk']);
        $this->assertInstanceOf(Enum::class, $rules['enum_disk'][1]);
    }

    public function testGetRulesReturnsOnlyRequestedFields(): void
    {
        $rules = Repository::getRules(['name', 'path',]);

        $this->assertCount(2, $rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('path', $rules);
        $this->assertArrayNotHasKey('size', $rules);
    }

    public function testEnumDiskAttributeReturnsEnumInstance(): void
    {
        $model = $this->makeModel();
        $model->enum_disk = RepositoryDisk::PUBLIC->value;

        $this->assertInstanceOf(RepositoryDisk::class, $model->enum_disk);
        $this->assertEquals(RepositoryDisk::PUBLIC, $model->enum_disk);
    }

    public function testFileAccessorReturnsStorageUrl(): void
    {
        Storage::fake('public');
        $model = $this->makeModel();
        $model->path = 'repositories/test/file.jpg';

        $this->assertEquals(Storage::url('repositories/test/file.jpg'), $model->file);
    }

    public function testHasFileReturnsTrueWhenFileExists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('repositories/test/file.jpg', 'content');

        $model = $this->makeModel();
        $model->enum_disk = RepositoryDisk::PUBLIC;
        $model->path = 'repositories/test/file.jpg';

        $this->assertTrue($model->has_file);
    }

    public function testHasFileReturnsFalseWhenFileDoesNotExist(): void
    {
        Storage::fake('public');
        $model = $this->makeModel();
        $model->enum_disk = RepositoryDisk::PUBLIC;
        $model->path = 'repositories/test/file.jpg';

        $this->assertFalse($model->has_file);
    }

    public function testRepositoryableRelation(): void
    {
        $model = $this->makeModel();
        $relation = $model->repositoryable();

        $this->assertInstanceOf(MorphTo::class, $relation);
    }
}
