<?php

declare(strict_types=1);

namespace App\Database\Models;

use App\Database\Model;
use App\Database\Traits\PreventUpdate;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityArtist extends Model
{
    use PreventUpdate;

    protected $table = 'activities_artists';

    protected $fillable = [
        'activity_id',
        'artist_id'
    ];

    /* * * * * * * * VALIDATION * * * * * * * */
    /** @return array<string, string|array> */
    public static function getRules(array $fields = [], $register = null): array
    {
        $rules = [
            'activity_id' => 'required|integer|exists:activities,id',
            'artist_id' => 'required|integer|exists:artists,id',
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    /** @return BelongsTo<Activity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    /** @return BelongsTo<Artist, $this> */
    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */
}
