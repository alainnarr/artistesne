<?php

namespace Tests\Unit\Database\Models;

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
        return new Repository;
    }

    public function test_get_table_returns_table_name(): void
    {
        $model = $this->makeModel();

        $this->assertEquals('repositories', $model->getTable());
    }

    public function test_get_fillable_returns_array(): void
    {
        $model = $this->makeModel();

        $this->assertEquals(['name', 'file_type', 'size', 'enum_disk', 'path'], $model->getFillable());
    }

    public function test_get_updatable_returns_array(): void
    {
        $model = $this->makeModel();

        $this->assertEquals(['name', 'file_type', 'size', 'enum_disk', 'path'], $model->getUpdatable());
    }

    public function test_casts_enum_disk_to_repository_disk(): void
    {
        $model = $this->makeModel();
        $casts = $model->getCasts();

        $this->assertArrayHasKey('enum_disk', $casts);
        $this->assertEquals(RepositoryDisk::class, $casts['enum_disk']);
    }

    public function test_get_rules_returns_all_rules(): void
    {
        $rules = Repository::getRules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('file_type', $rules);
        $this->assertArrayHasKey('size', $rules);
        $this->assertArrayHasKey('enum_disk', $rules);
        $this->assertArrayHasKey('path', $rules);
    }

    public function test_enum_disk_rule_uses_repository_disk_enum(): void
    {
        $rules = Repository::getRules();

        $this->assertIsArray($rules['enum_disk']);
        $this->assertInstanceOf(Enum::class, $rules['enum_disk'][1]);
    }

    public function test_get_rules_returns_only_requested_fields(): void
    {
        $rules = Repository::getRules(['name', 'path']);

        $this->assertCount(2, $rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('path', $rules);
        $this->assertArrayNotHasKey('size', $rules);
    }

    public function test_enum_disk_attribute_returns_enum_instance(): void
    {
        $model = $this->makeModel();
        $model->enum_disk = RepositoryDisk::PUBLIC->value;

        $this->assertInstanceOf(RepositoryDisk::class, $model->enum_disk);
        $this->assertEquals(RepositoryDisk::PUBLIC, $model->enum_disk);
    }

    public function test_file_accessor_returns_storage_url(): void
    {
        Storage::fake('public');
        $model = $this->makeModel();
        $model->enum_disk = RepositoryDisk::PUBLIC;
        $model->path = 'repositories/test/file.jpg';

        $this->assertEquals(Storage::disk('public')->url('repositories/test/file.jpg'), $model->file);
    }

    public function test_has_file_returns_true_when_file_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('repositories/test/file.jpg', 'content');

        $model = $this->makeModel();
        $model->enum_disk = RepositoryDisk::PUBLIC;
        $model->path = 'repositories/test/file.jpg';

        $this->assertTrue($model->has_file);
    }

    public function test_has_file_returns_false_when_file_does_not_exist(): void
    {
        Storage::fake('public');
        $model = $this->makeModel();
        $model->enum_disk = RepositoryDisk::PUBLIC;
        $model->path = 'repositories/test/file.jpg';

        $this->assertFalse($model->has_file);
    }

    public function test_repositoryable_relation(): void
    {
        $model = $this->makeModel();
        $relation = $model->repositoryable();

        $this->assertInstanceOf(MorphTo::class, $relation);
    }
}
