<?php

namespace App\Database\Models;

use App\Database\Model;
use App\Database\Traits\PreventDelete;
use App\Database\Traits\PreventUpdate;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use PreventDelete;
    use PreventUpdate;

    protected $table = 'activities';

    protected $fillable = [
        'discipline_id',
        'code',
        'label',
    ];

    protected $updatable = [];

    /* * * * * * * * VALIDATION * * * * * * * */
    public static function getRules(array $fields = [], $register = null): array
    {
        return [];
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'discipline_id');
    }

    /** @return HasMany<Synonym, $this> */
    public function synonyms(): HasMany
    {
        return $this->hasMany(Synonym::class, 'activity_id');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */
}
