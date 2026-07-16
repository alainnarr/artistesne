<?php

namespace Tests\Unit\Services;

use App\Contracts\RepositoryableContract;
use App\Database\Models\Repository;
use App\Enums\RepositoryDisk;
use App\Services\RepositoriesService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;
use Tests\Unit\Services\Traits\Trait_Seed;

class RepositoriesServiceTest extends TestCase
{
    use RefreshDatabase;
    use Trait_Seed;

    private RepositoriesService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->service = new RepositoriesService;
        Schema::create('test_repositoryables', function ($table) {
            $table->id();
            $table->timestamps();
        });

        $this->service = new RepositoriesService(new Repository());
    }

    private function makeRepositoryable(): Model
    {
        return new class extends Model implements RepositoryableContract
        {
            protected $table = 'test_repositoryables';

            protected $guarded = [];

            public function repositories(): MorphMany
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

    // public function test_create_removes_file_when_repository_creation_fails(): void
    // {
    //     $model = new class extends Model implements RepositoryableContract
    //     {
    //         protected $table = 'test_repositoryables';

    //         protected $guarded = [];

    //         public function repositories(): MorphMany
    //         {
    //             return $this->morphMany(
    //                 Repository::class,
    //                 'repositoryable'
    //             );
    //         }
    //     };

    //     $model->save();

    //     return $model;
    // }

    public function testCreateRepositoryWithFile(): void
    {
        $model = $this->makeRepositoryable();
        $file = UploadedFile::fake()->image('photo.jpg', 100, 100);
        $repository = $this->service->create($model, $file);

        $this->assertInstanceOf(Repository::class, $repository);
        $this->assertEquals('photo.jpg', $repository->name);
        $this->assertEquals('image/jpeg', $repository->file_type);
        $this->assertEquals(RepositoryDisk::PUBLIC, $repository->enum_disk);

        Storage::disk('public')->assertExists($repository->path);
    }

    public function testCreateWithoutFileThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Repositories - File not sended');

        $model = $this->makeRepositoryable();
        $this->service->create($model, null);
    }

    public function testCreateMultipleRepositories(): void
    {
        $model = $this->makeRepositoryable();

        $repositories = $this->service->createMultiple(
            $model,
            [
                UploadedFile::fake()->image('one.jpg'),
                UploadedFile::fake()->image('two.jpg'),
            ]
        );

        $this->assertCount(2, $repositories);
        $this->assertDatabaseCount('repositories', 2);
    }

    public function testUpdateRepositoryReplacesFile(): void
    {
        $model = $this->makeRepositoryable();
        $oldPath = 'repositories/aa/bb/old.jpg';
        Storage::disk('public')->put($oldPath, 'old');
        $repository = $model->repositories()->create([
            'enum_disk' => RepositoryDisk::PUBLIC,
            'path' => $oldPath,
            'name' => 'old.jpg',
            'file_type' => 'image/jpeg',
            'size' => 10,
        ]);
        $updated = $this->service->update($repository->id, UploadedFile::fake()->image('new.jpg'));

        $this->assertEquals('new.jpg', $updated->name);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($updated->path);
    }

    public function testDeleteRepositoryRemovesFileAndRecord(): void
    {
        $model = $this->makeRepositoryable();
        $path = 'repositories/aa/bb/file.jpg';
        Storage::disk('public')->put($path, 'content');
        $repository = $model->repositories()->create([
            'enum_disk' => RepositoryDisk::PUBLIC,
            'path' => $path,
            'name' => 'file.jpg',
            'file_type' => 'image/jpeg',
            'size' => 10,
        ]);
        $result = $this->service->delete($repository->id);

        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('repositories', ['id' => $repository->id]);
    }

    public function testDeleteMultipleRepositories(): void
    {
        $model = $this->makeRepositoryable();
        $ids = [];
        foreach (['one.jpg', 'two.jpg'] as $file) {
            $repository = $model->repositories()->create([
                'enum_disk' => RepositoryDisk::PUBLIC,
                'path' => "repositories/aa/$file",
                'name' => $file,
                'file_type' => 'image/jpeg',
                'size' => 10,
            ]);
            Storage::disk('public')->put($repository->path, 'content');
            $ids[] = $repository->id;
        }
        $deleted = $this->service->deleteMultiple($ids);

        $this->assertEquals(2, $deleted);
        $this->assertDatabaseCount('repositories', 0);
    }

    public function testSyncKeepsDeletesAndCreatesRepositories(): void
    {
        $model = $this->makeRepositoryable();
        $keep = $model->repositories()->create([
            'enum_disk' => RepositoryDisk::PUBLIC,
            'path' => 'repositories/aa/keep.jpg',
            'name' => 'keep.jpg',
            'file_type' => 'image/jpeg',
            'size' => 10,
        ]);
        $delete = $model->repositories()->create([
            'enum_disk' => RepositoryDisk::PUBLIC,
            'path' => 'repositories/aa/delete.jpg',
            'name' => 'delete.jpg',
            'file_type' => 'image/jpeg',
            'size' => 10,
        ]);
        Storage::disk('public')->put($delete->path, 'content');
        $result = $this->service->sync(
            $model,
            [
                'keep' => [$keep->id],
                'new' => [
                    UploadedFile::fake()->image('new.jpg'),
                ],
            ]
        );

        $this->assertEquals([$keep->id], $result['kept']);
        $this->assertEquals([$delete->id], $result['deleted']);
        $this->assertEquals(1, $result['deleted_count']);
        $this->assertCount(1, $result['created']);
    }

    public function testReplicateRepositoryCopiesFileAndCreatesNewRecord(): void
    {
        $model = $this->makeRepositoryable();
        $newModel = $this->makeRepositoryable();
        $originalPath = 'repositories/aa/file.jpg';
        Storage::disk('public')->put($originalPath, 'file-content');
        $repository = $model->repositories()->create([
            'enum_disk' => RepositoryDisk::PUBLIC,
            'path' => $originalPath,
            'name' => 'file.jpg',
            'file_type' => 'image/jpeg',
            'size' => 12,
        ]);
        $copy = $this->service->replicateRepository($repository, $newModel);

        $this->assertInstanceOf(Repository::class, $copy);
        $this->assertNotEquals($repository->path, $copy->path);
        Storage::disk('public')->assertExists($repository->path);
        Storage::disk('public')->assertExists($copy->path);
        $this->assertEquals($repository->name, $copy->name);
        $this->assertEquals($repository->file_type, $copy->file_type);
        $this->assertEquals($repository->size, $copy->size);
        $this->assertNotEquals($repository->repositoryable_id, $copy->repositoryable_id);
    }

    public function testStorageDestroyFileReturnsFalseWhenFileDoesNotExist(): void
    {
        $result = $this->service->storage_destroyFile('repositories/aa/file.jpg', RepositoryDisk::PUBLIC);

        $this->assertFalse($result);
    }


    public function testCreateRemovesFileWhenRepositoryCreationFails(): void
    {
        $model = new class extends Model {
            protected $table = 'test_repositoryables';

            public function repositories()
            {
                throw new Exception('Database error');
            }
        };

        try {
            $this->service->create($model, UploadedFile::fake()->image('error.jpg'));
        } catch (Exception $exception) {
            $this->assertEquals('Database error', $exception->getMessage());
        }

        $this->assertEmpty(Storage::disk('public')->allFiles('repositories'));
    }

    public function testUpdateRepositoryReplacesFileAndUpdatesData(): void
    {
        Storage::fake(RepositoryDisk::PUBLIC->value);
        $artist = $this->seedArtist();
        $oldFile = UploadedFile::fake()->create('old.pdf', 100, 'application/pdf');
        $repository = $this->service->create($artist, $oldFile);
        $oldPath = $repository->path;
        $newFile = UploadedFile::fake()->create('new.pdf', 200, 'application/pdf');
        $updated = $this->service->update($repository->id, $newFile);

        $this->assertNotEquals($oldPath, $updated->path);
        $this->assertEquals('new.pdf', $updated->name);
        $this->assertEquals('application/pdf', $updated->file_type);
        $this->assertEquals(200 * 1024, $updated->size);

        Storage::disk(RepositoryDisk::PUBLIC->value)->assertExists($updated->path);
        Storage::disk(RepositoryDisk::PUBLIC->value)->assertMissing($oldPath);
    }

    public function testReplicateRepositoryCreatesCopyForNewOwner(): void
    {
        Storage::fake(RepositoryDisk::PUBLIC->value);
        $artist = $this->seedArtist();
        $newArtist = $this->seedArtist();
        $file = UploadedFile::fake()->create('image.jpg', 300, 'image/jpeg');
        $repository = $this->service->create($artist, $file);
        Storage::disk(RepositoryDisk::PUBLIC->value)->put($repository->path, 'content');
        $replicated = $this->service->replicateRepository($repository, $newArtist);

        $this->assertDatabaseHas('repositories', [
            'id' => $replicated->id,
            'name' => $repository->name,
            'file_type' => $repository->file_type,
            'size' => $repository->size,
        ]);
        Storage::disk(RepositoryDisk::PUBLIC->value)->assertExists($replicated->path);
        $this->assertNotEquals($repository->path, $replicated->path);
    }

    public function testReplicateRepositoryThrowsExceptionWhenCopyFails(): void
    {
        Storage::fake(RepositoryDisk::PUBLIC->value);
        $artist = $this->seedArtist();
        $newArtist = $this->seedArtist();
        $repository = $artist->repositories()->create([
            'enum_disk' => RepositoryDisk::PUBLIC,
            'path' => 'repositories/test/file.pdf',
            'name' => 'file.pdf',
            'file_type' => 'application/pdf',
            'size' => 100,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Repositories - Failed to copy file");
        $this->service->replicateRepository($repository, $newArtist);
    }

    public function testReplicateRepositoryThrowsExceptionWhenOriginalFileDoesNotExist(): void
    {
        $model = $this->makeRepositoryable();
        $newModel = $this->makeRepositoryable();
        $repository = $model->repositories()->create([
            'enum_disk' => RepositoryDisk::PUBLIC,
            'path' => 'repositories/aa/missing.jpg',
            'name' => 'missing.jpg',
            'file_type' => 'image/jpeg',
            'size' => 10,
        ]);

        $this->expectException(Exception::class);
        $this->service->replicateRepository($repository, $newModel);
    }

    public function testUpdateRemovesNewFileWhenUpdateFails(): void
    {
        Storage::fake(RepositoryDisk::PUBLIC->value);
        $repository = Mockery::mock(Repository::class)->makePartial();
        $repository->path = 'repositories/old/file.pdf';
        $repository->enum_disk = RepositoryDisk::PUBLIC;
        $repository->shouldReceive('update')->once()->andThrow(new Exception('Update failed'));

        $mockRepository = Mockery::mock(Repository::class)->makePartial();
        $mockRepository->shouldReceive('findOrFail')->once()->andReturn($repository);
        $service = new RepositoriesService($mockRepository);
        $file = UploadedFile::fake()->create('new.pdf', 100, 'application/pdf');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Update failed');

        try {
            $service->update(1, $file);
        } finally {
            $files = Storage::disk(RepositoryDisk::PUBLIC->value)->allFiles('repositories');
            $this->assertCount(0, $files);
        }
    }
}
