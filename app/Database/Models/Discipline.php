<?php

namespace App\Database\Models;

use App\Database\Model;
use App\Database\Traits\PreventDelete;
use App\Database\Traits\PreventUpdate;
use App\Enums\DisciplineType;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected function casts(): array
    {
        return [
            'enum_type' => DisciplineType::class,
        ];
    }

    /* * * * * * * * VALIDATION * * * * * * * */
    public static function getRules(array $fields = [], $register = null): array
    {
        return [];
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'discipline_id');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */
}
