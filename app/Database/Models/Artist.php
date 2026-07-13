<?php

namespace App\Database\Models;

use App\Database\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\ArtistShowContact;
use App\Enums\ArtistStatus;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\User;
use Illuminate\Validation\Rules\Enum;

class Artist extends Model
{
    protected $table = 'newartists';

    protected $fillable = [
        'registration_id',
        'user_id',
        'artist_name',
        'email',
        'phone',
        'rep_image',
        'biography',
        'city',
        'discipline_secondary',
        'enum_status',
        'enum_show_contact',
        'published_at',
        'confirmed_at',
        'reminded_at',
    ];

    protected $casts = [
        'enum_status' => ArtistStatus::class,
        'enum_show_contact' => ArtistShowContact::class,
    ];

    /* * * * * * * * VALIDATION * * * * * * * */
    public static function getRules(array $fields = [], $register = null): array
    {
        $id = $register['id'] ?? null;

        $rules = [
            'registration_id' => 'required|exists:registrations,id',
            'user_id' => 'required|exists:users,id',
            'artist_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:125',
            'phone' => 'nullable|string|max:15',
            'rep_image' => 'nullable|exists:repositories,id',
            'biography' => 'nullable|string',
            'city' => 'nullable|string|max:125',
            'discipline_secondary' => 'nullable|exists:disciplines,id',
            'enum_status' => ['required', new Enum(ArtistStatus::class)],
            'enum_show_contact' => ['required', new Enum(ArtistShowContact::class)],
            'published_at' => 'nullable|date',
            'confirmed_at' => 'nullable|date',
            'reminded_at' => 'nullable|date'
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class, 'keywords_artists', 'artist_id', 'keyword_id');
    }

    public function keywordsArtists(): HasMany
    {
        return $this->hasMany(KeywordArtist::class, 'artist_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'artist_id');
    }

    public function activitiesArtists(): HasMany
    {
        return $this->hasMany(ActivityArtist::class, 'artist_id');
    }

    public function disciplineSecondary(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'discipline_secondary');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function repImage(): BelongsTo
    {
        return $this->belongsTo(Repository::class, 'rep_image');
    }

    public function repositories(): MorphMany
    {
        return $this->morphMany(Repository::class, 'repositoryable');
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class, 'artist_id');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */
}
