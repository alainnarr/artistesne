<?php

namespace App\Services;

use App\Contracts\RepositoryableContract;
use App\Database\Models\Repository;
use App\Enums\RepositoryDisk;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RepositoriesService
{
    private readonly Repository $repository;

    public function __construct(?Repository $repository = null)
    {
        $this->repository = $repository ?? new Repository();
    }

    public function create(
        RepositoryableContract $repositoryable,
        ?UploadedFile $file = null,
        RepositoryDisk $disk = RepositoryDisk::PUBLIC
    ): Repository {
        if (is_null($file)) {
            throw new Exception('Repositories - File not sended');
        }

        $path = null;

        try {
            $path = $this->storage_storeFile($file, $disk);

            $repository = new Repository([
                'enum_disk' => $disk,
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
            $repositoryable->repositories()->save($repository);

            return $repository;
        } catch (Exception $exception) {
            if ($path) {
                $this->storage_destroyFile($path, $disk);
            }

            throw $exception;
        }
    }

    public function createMultiple(
        RepositoryableContract $repositoryable,
        array $files,
        RepositoryDisk $disk = RepositoryDisk::PUBLIC
    ): array {
        $records = [];

        foreach ($files as $file) {
            $records[] = $this->create($repositoryable, $file, $disk);
        }

        return $records;
    }

    public function update(
        int $repositoryId,
        UploadedFile $file
    ): Repository {
        $repository = $this->repository->findOrFail($repositoryId);

        $oldPath = $repository->path;
        $disk = $repository->enum_disk;

        $newPath = null;
        try {
            $newPath = $this->storage_storeFile($file, $disk);

            $repository->update([
                'path' => $newPath,
                'name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            if ($oldPath) {
                $this->storage_destroyFile($oldPath, $disk);
            }

            return $repository->refresh();
        } catch (Exception $exception) {
            if ($newPath) {
                $this->storage_destroyFile($newPath, $disk);
            }
            throw $exception;
        }
    }

    public function download(Repository $repository): StreamedResponse
    {
        return Storage::disk($repository->enum_disk->value)->download($repository->path, $repository->name);
    }

    public function delete(int $repositoryId): bool
    {
        $repository = $this->repository->findOrFail($repositoryId);

        if ($repository->path) {
            $this->storage_destroyFile($repository->path, $repository->enum_disk);
        }

        return (bool) $repository->delete();
    }

    public function deleteMultiple(array $repositoryIds): int
    {
        $deleted = 0;

        foreach ($repositoryIds as $repositoryId) {
            $deleted += (int) $this->delete((int) $repositoryId);
        }

        return $deleted;
    }

    public function sync(
        RepositoryableContract $repositoryable,
        array $files,
        RepositoryDisk $disk = RepositoryDisk::PUBLIC
    ): array {
        $keepIds = array_map('intval', Arr::get($files, 'keep', []));
        $newFiles = Arr::get($files, 'new', []);

        $currentIds = $repositoryable->repositories()
            ->pluck('id')
            ->toArray();

        $toDelete = array_diff($currentIds, $keepIds);

        $deletedCount = $this->deleteMultiple($toDelete);

        $created = $this->createMultiple($repositoryable, $newFiles, $disk);

        return [
            'kept' => $keepIds,
            'deleted' => array_values($toDelete),
            'deleted_count' => $deletedCount,
            'created' => $created,
        ];
    }

    private function storage_storeFile(UploadedFile $file, RepositoryDisk $disk): string
    {
        return $file->store($this->getBasePath(), $disk->value);
    }

    public function storage_destroyFile(string $path, RepositoryDisk $disk): bool
    {
        if ($this->storage_existFile($path, $disk) && str_starts_with($path, 'repositories/')) {
            Storage::disk($disk->value)->delete($path);

            return true;
        }

        return false;
    }

    private function storage_existFile(string $path, RepositoryDisk $disk): bool
    {
        return Storage::disk($disk->value)->exists($path);
    }

    private function getBasePath(): string
    {
        $uuid = Str::uuid()->toString();
        $parts = str_split($uuid, 2);

        return 'repositories/'.Arr::first($parts).'/'.Arr::last($parts);
    }

    public function replicateRepository(
        Repository $repository,
        RepositoryableContract $newRepositoryable,
    ): Repository {
        $disk = $repository->enum_disk;
        $newPath = null;

        try {
            $extension = pathinfo($repository->path, PATHINFO_EXTENSION);

            $newPath = $this->getBasePath()
                . '/'
                . Str::uuid()
                . ($extension ? '.' . $extension : '');

            if (! Storage::disk($disk->value)->copy($repository->path, $newPath)) {
                throw new Exception("Repositories - Failed to copy file from [{$repository->path}] to [{$newPath}]");
            }
            return $newRepositoryable->repositories()->create([
                'enum_disk' => $disk,
                'path' => $newPath,
                'name' => $repository->name,
                'file_type' => $repository->file_type,
                'size' => $repository->size,
            ]);

        } catch (Exception $exception) {
            if ($newPath) {
                $this->storage_destroyFile($newPath, $disk);
            }

            throw $exception;
        }
    }
}
