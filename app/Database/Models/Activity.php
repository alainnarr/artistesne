<?php

namespace App\Database\Models;

use App\Database\Model;
use App\Database\Traits\PreventUpdate;
use App\Database\Traits\PreventDelete;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use PreventUpdate;
    use PreventDelete;

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
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'discipline_id');
    }

    public function synonyms(): HasMany
    {
        return $this->hasMany(Synonym::class, 'activity_id');
    }

    public function registrations(): BelongsToMany
    {
        return $this->belongsToMany(Registration::class, 'activities_registrations', 'activity_id', 'registration_id');
    }

    public function registrationActivities(): HasMany
    {
        return $this->hasMany(ActivityRegistration::class, 'activity_id');
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'activities_artists', 'activity_id', 'artist_id');
    }

    public function artistActivities(): HasMany
    {
        return $this->hasMany(ActivityArtist::class, 'activity_id');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */
}
