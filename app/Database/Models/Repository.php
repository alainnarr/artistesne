<?php

namespace App\Database\Models;

use App\Database\Model;
use App\Database\Traits\PreventUpdate;
use Database\Factories\RepositoryFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Enums\RepositoryDisk;
use Illuminate\Validation\Rules\Enum;

/**
 * @property-read bool $has_file
 * @property-read string $file
 */
class Repository extends Model
{
    use HasFactory;
    use PreventUpdate;

    protected $table = 'repositories';

    protected $fillable = [
        'name',
        'file_type',
        'size',
        'enum_disk',
        'path',
    ];

    protected $updatable = [
        'name',
        'file_type',
        'size',
        'enum_disk',
        'path',
    ];

    protected $appends = [
        'has_file',
        'file',
    ];

    /** @return array<string, class-string|'datetime'> */
    protected function casts(): array
    {
        return [
            'enum_disk' => RepositoryDisk::class,
        ];
    }

    /** @return RepositoryFactory<Repository, $this> */
    protected static function newFactory(): RepositoryFactory
    {
        return RepositoryFactory::new();
    }

    /* * * * * * * * VALIDATION * * * * * * * */
    /** @return array<string, string|array> */
    public static function getRules(array $fields = [], $register = null): array
    {
        $id = $register['id'] ?? null;
        $rules = [
            'name' => 'required|string|max:255',
            'file_type' => 'required|string|max:100',
            'size' => 'required|numeric',
            'enum_disk' => ['required', new Enum(RepositoryDisk::class)],
            'path' => 'required|string|max:255|unique:repositories,path,' . $id . ',id',
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    /** @return MorphTo<RepositoryableContract, $this> */
    public function repositoryable(): MorphTo
    {
        return $this->morphTo();
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */

    /* * * * * * * * ACCESSORS * * * * * * * */
    /** @return Attribute<string, never> */
    public function file(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                // The "private" disk has no public URL (documents attached to
                // a Registration must never be reachable by a guessable/direct
                // link) — only "public" (e.g. artist portraits) can be linked to.
                if ($this->enum_disk !== RepositoryDisk::PUBLIC) {
                    return '';
                }

                return Storage::disk($this->enum_disk->value)->url($this->path);
            }
        );
    }

    /** @return Attribute<bool, never> */
    public function hasFile(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => Storage::disk($this->enum_disk->value)->exists($this->path)
        );
    }
    /* * * * * * * * END - ACCESSORS * * * * * * * */
}
