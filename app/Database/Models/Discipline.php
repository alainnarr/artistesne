<?php

declare(strict_types=1);

namespace App\Database\Models;

use App\Database\Model;
use App\Database\Traits\PreventDelete;
use App\Database\Traits\PreventUpdate;
use App\Enums\DisciplineType;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rules\Enum;

class Discipline extends Model
{
    use PreventDelete;
    use PreventUpdate;

    protected $table = 'disciplines';

    protected $fillable = [
        'code',
        'label',
        'enum_type',
    ];

    protected $updatable = [];

    /** @return array<string, class-string|'datetime'> */
    protected function casts(): array
    {
        return [
            'enum_type' => DisciplineType::class,
        ];
    }

    /* * * * * * * * VALIDATION * * * * * * * */
    /** @return array<string, string|array> */
    public static function getRules(array $fields = [], $register = null): array
    {
        $id = $register['id'] ?? null;

        $rules = [
            'code' => 'required|string|max:50|unique:disciplines,code,' . $id . ',id',
            'label' => 'required|string|max:100',
            'enum_type' => ['required', new Enum(DisciplineType::class)],
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    /** @return HasMany<Activity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'discipline_id');
    }

    /** @return HasMany<Artist, $this> */
    public function mainArtists(): HasMany
    {
        return $this->hasMany(Artist::class, 'discipline_main');
    }

    /** @return HasMany<Artist, $this> */
    public function secondaryArtists(): HasMany
    {
        return $this->hasMany(Artist::class, 'discipline_secondary');
    }

    /** @return HasMany<Registration, $this> */
    public function mainRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'discipline_main');
    }

    /** @return HasMany<Registration, $this> */
    public function secondaryRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'discipline_secondary');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */
}
