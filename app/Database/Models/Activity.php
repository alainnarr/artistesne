<?php

declare(strict_types=1);

namespace App\Database\Models;

use App\Database\Model;
use App\Database\Traits\PreventDelete;
use App\Database\Traits\PreventUpdate;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
    /** @return array<string, string|array> */
    public static function getRules(array $fields = [], $register = null): array
    {
        $id = $register['id'] ?? null;

        $rules = [
            'discipline_id' => 'required|exists:disciplines,id',
            'code' => 'required|string|max:50|unique:activities,code,' . $id . ',id',
            'label' => 'required|string|max:100'
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    /** @return BelongsTo<Discipline, $this> */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'discipline_id');
    }

    /** @return HasMany<Synonym, $this> */
    public function synonyms(): HasMany
    {
        return $this->hasMany(Synonym::class, 'activity_id');
    }

    /** @return BelongsToMany<Registration, $this> */
    public function registrations(): BelongsToMany
    {
        return $this->belongsToMany(Registration::class, 'activities_registrations', 'activity_id', 'registration_id');
    }

    /** @return HasMany<ActivityRegistration, $this> */
    public function registrationActivities(): HasMany
    {
        return $this->hasMany(ActivityRegistration::class, 'activity_id');
    }

    /** @return BelongsToMany<Artist, $this> */
    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'activities_artists', 'activity_id', 'artist_id');
    }

    /** @return HasMany<ActivityArtist, $this> */
    public function artistActivities(): HasMany
    {
        return $this->hasMany(ActivityArtist::class, 'activity_id');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */
}
