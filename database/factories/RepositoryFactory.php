<?php

namespace Database\Factories;

use App\Database\Model;
use App\Database\Models\Repository;
use App\Enums\RepositoryDisk;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<Repository>
 */
class RepositoryFactory extends Factory
{
    protected $model = Repository::class;

    public function definition(): array
    {
        $filename = Str::uuid().'.jpg';
        $path = 'seed/'.$filename;

        Storage::disk(RepositoryDisk::PUBLIC->value)
            ->put($path, fake()->image());

        return [
            'name' => $filename,
            'file_type' => 'image/jpeg',
            'size' => Storage::disk(RepositoryDisk::PUBLIC->value)->size($path),
            'enum_disk' => RepositoryDisk::PUBLIC->value,
            'path' => $path,
        ];
    }

    public function image(Model $owner): static
    {
        return $this->state(function () use ($owner) {
            $filename = Str::uuid().'.jpg';
            $path = 'images/'.$filename;

            Storage::disk(RepositoryDisk::PUBLIC->value)
                ->put($path, fake()->image());

            return [
                'repositoryable_id' => $owner->getKey(),
                'repositoryable_type' => $owner::class,
                'name' => $filename,
                'file_type' => 'image/jpeg',
                'size' => Storage::disk(RepositoryDisk::PUBLIC->value)->size($path),
                'enum_disk' => RepositoryDisk::PUBLIC->value,
                'path' => $path,
            ];
        });
    }

    public function document(Model $owner): static
    {
        return $this->state(function () use ($owner) {
            $filename = Str::uuid().'.pdf';
            $path = 'documents/'.$filename;

            Storage::disk(RepositoryDisk::PRIVATE->value)
                ->put($path, fake()->randomHtml());

            return [
                'repositoryable_id' => $owner->getKey(),
                'repositoryable_type' => $owner::class,
                'name' => $filename,
                'file_type' => 'application/pdf',
                'size' => Storage::disk(RepositoryDisk::PRIVATE->value)->size($path),
                'enum_disk' => RepositoryDisk::PRIVATE->value,
                'path' => $path,
            ];
        });
    }
}
