<?php

namespace Tests\Unit\Services;

use App\Database\Models\Repository;
use App\Enums\RepositoryDisk;
use App\Services\RepositoriesService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Exception;
use Illuminate\Support\Facades\Schema;

class RepositoriesServiceTest extends TestCase
{
    use RefreshDatabase;

    private RepositoriesService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->service = new RepositoriesService();
        Schema::create('test_repositoryables', function ($table) {
            $table->id();
            $table->timestamps();
        });
    }

    private function makeRepositoryable(): Model
    {
        return new class extends Model {
            protected $table = 'test_repositoryables';
            protected $guarded = [];

            public function repositories()
            {
                return $this->morphMany(Repository::class, 'repositoryable');
            }
        };
    }

    public function test_create_repository_with_file(): void
    {
        $model = $this->makeRepositoryable();
        $model->id = 1;
        $file = UploadedFile::fake()->image('photo.jpg');
        $repository = $this->service->create($model, $file);

        $this->assertInstanceOf(Repository::class, $repository);
        $this->assertEquals('photo.jpg', $repository->name);
        $this->assertEquals(RepositoryDisk::PUBLIC, $repository->enum_disk);
        Storage::disk('public')->assertExists($repository->path);
    }

    public function test_create_without_file_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $model = $this->makeRepositoryable();

        $this->service->create($model, null);
    }

    public function test_create_multiple_repositories(): void
    {
        $model = $this->makeRepositoryable();
        $model->save();

        $files = [
            UploadedFile::fake()->image('one.jpg'),
            UploadedFile::fake()->image('two.jpg'),
        ];

        $repositories = $this->service->createMultiple($model, $files);

        $this->assertCount(2, $repositories);
    }

    public function test_update_repository_replaces_file(): void
    {
        $oldPath = 'repositories/aa/bb/old.jpg';
        Storage::disk('public')->put($oldPath, 'old content');
        $repositoryable = $this->makeRepositoryable();
        $repositoryable->id = 1;

        $repository = new Repository([
            'name' => 'old.jpg',
            'file_type' => 'image/jpeg',
            'size' => 100,
            'enum_disk' => RepositoryDisk::PUBLIC,
            'path' => $oldPath,
        ]);
        $repository->repositoryable()->associate($repositoryable);
        $repository->save();

        $newFile = UploadedFile::fake()->image('new.jpg');
        $updated = $this->service->update($repository->id, $newFile);

        $this->assertEquals('new.jpg', $updated->name);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($updated->path);
    }

    public function test_storage_destroy_file_removes_file(): void
    {
        $path = 'repositories/aa/bb/file.jpg';
        Storage::disk('public')->put($path, 'content');
        $result = $this->service->storage_destroyFile($path, RepositoryDisk::PUBLIC);

        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_storage_destroy_file_does_not_remove_outside_repository_folder(): void
    {
        $path = 'other/file.jpg';
        Storage::disk('public')->put($path, 'content');
        $result = $this->service->storage_destroyFile($path, RepositoryDisk::PUBLIC);

        $this->assertFalse($result);
        Storage::disk('public')->assertExists($path);
    }

    public function test_create_removes_file_when_repository_creation_fails(): void
    {
        $model = new class extends Model {
            protected $table = 'test_repositoryables';
            protected $guarded = [];

            public function repositories()
            {
                throw new Exception('Database error');
            }
        };

        $file = UploadedFile::fake()->image('error.jpg');

        try {
            $this->service->create($model, $file);
        } catch (Exception $exception) {
            $this->assertEquals('Database error', $exception->getMessage());
        }

        $files = Storage::disk('public')->allFiles('repositories');

        $this->assertEmpty($files);
    }
}
