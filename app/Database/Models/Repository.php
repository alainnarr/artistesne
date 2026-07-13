<?php

namespace App\Database\Models;

use App\Database\Model;
use App\Database\Traits\PreventUpdate;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Enums\RepositoryDisk;
use Illuminate\Validation\Rules\Enum;

class Repository extends Model
{
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
        'file'
    ];

    protected function casts(): array
    {
        return [
            'enum_disk' => RepositoryDisk::class,
        ];
    }

    /* * * * * * * * VALIDATION * * * * * * * */
    public static function getRules(array $fields = [], $register = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'file_type' => 'required|string|max:100',
            'size' => 'required|numeric',
            'enum_disk' => ['required', new Enum(RepositoryDisk::class)],
            'path' => 'required|string|max:255',
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    public function repositoryable(): MorphTo
    {
        return $this->morphTo();
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */

    /* * * * * * * * ACCESSORS * * * * * * * */
    public function file(): Attribute
    {
        return Attribute::make(
            get: fn($value) =>
                Storage::url($this->path) ?? asset('images/no_image.png')
        );
    }
    public function hasFile(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Storage::disk($this->enum_disk->value)->exists($this->path)
        );
    }
    /* * * * * * * * END - ACCESSORS * * * * * * * */
}
