<?php

declare(strict_types=1);

namespace App\Database\Models;

use App\Database\Model;
use App\Database\Traits\PreventDelete;
use App\Database\Traits\PreventUpdate;
use App\Enums\ArtistShowContact;
use App\Enums\ArtistStatus;
use App\Contracts\RepositoryableContract;
use Database\Factories\ArtistFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rules\Enum;
use Laravel\Scout\Searchable;

class Artist extends Model implements RepositoryableContract
{
    use HasFactory;
    use Searchable;
    use PreventDelete;
    use PreventUpdate;

    protected $table = 'artists';

    protected $fillable = [
        'registration_id',
        'user_id',
        'slug',
        'artist_name',
        'email',
        'phone',
        'biography',
        'city',
        'discipline_main',
        'discipline_secondary',
        'enum_status',
        'enum_show_contact',
        'published_at',
        'last_confirmed_at',
        'reminder_sent_at',
    ];

    protected $updatable = [
        'artist_name',
        'email',
        'phone',
        'biography',
        'city',
        'discipline_secondary',
        'enum_status',
        'enum_show_contact',
        'published_at',
        'last_confirmed_at',
        'reminder_sent_at',
    ];

    /** @return array<string, class-string|'datetime'> */
    protected function casts(): array
    {
        return [
            'enum_status' => ArtistStatus::class,
            'enum_show_contact' => ArtistShowContact::class,
            'published_at' => 'datetime',
            'last_confirmed_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    /** @return ArtistFactory<Artist, $this> */
    protected static function newFactory(): ArtistFactory
    {
        return ArtistFactory::new();
    }

    /* * * * * * * * VALIDATION * * * * * * * */
    /** @return array<string, string|array> */
    public static function getRules(array $fields = [], $register = null): array
    {
        $id = $register?->id ?? null;
        $rules = [
            'registration_id' => 'required|exists:registrations,id|unique:artists,registration_id,'.$id.',id',
            'user_id' => 'required|exists:users,id|unique:artists,user_id,'.$id.',id',
            'slug' => 'required|string|max:255|unique:artists,slug,'.$id.',id',
            'artist_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:125',
            'phone' => 'nullable|string|max:15',
            'biography' => 'nullable|string',
            'city' => 'nullable|string|max:125',
            'discipline_main' => 'required|exists:disciplines,id',
            'discipline_secondary' => 'nullable|exists:disciplines,id',
            'enum_status' => ['required', new Enum(ArtistStatus::class)],
            'enum_show_contact' => ['required', new Enum(ArtistShowContact::class)],
            'published_at' => 'nullable|date',
            'last_confirmed_at' => 'nullable|date',
            'reminder_sent_at' => 'nullable|date'
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    /** @return BelongsToMany<Keyword, $this> */
    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class, 'keywords_artists', 'artist_id', 'keyword_id');
    }

    /** @return HasMany<KeywordArtist, $this> */
    public function keywordsArtists(): HasMany
    {
        return $this->hasMany(KeywordArtist::class, 'artist_id');
    }

    /** @return BelongsToMany<Activity, $this> */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activities_artists', 'artist_id', 'activity_id');
    }

    /** @return HasMany<ActivityArtist, $this> */
    public function activitiesArtists(): HasMany
    {
        return $this->hasMany(ActivityArtist::class, 'artist_id');
    }

    /** @return BelongsTo<Discipline, $this> */
    public function disciplineMain(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'discipline_main_id');
    }

    /** @return BelongsTo<Discipline, $this> */
    public function disciplineSecondary(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'discipline_secondary');
    }

    /** @return BelongsTo<Registration, $this> */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return MorphOne<Repository, $this> */
    public function image(): MorphOne
    {
        return $this->morphOne(Repository::class, 'repositoryable');
    }

    /** @return MorphMany<Repository, $this> */
    public function repositories(): MorphMany
    {
        return $this->morphMany(Repository::class, 'repositoryable');
    }

    /** @return HasMany<ArtistChangeRequest, $this> */
    public function changeRequests(): HasMany
    {
        return $this->hasMany(ArtistChangeRequest::class, 'artist_id');
    }

    /** @return ArtistChangeRequest|null */
    public function pendingChangeRequest(): ?ArtistChangeRequest
    {
        return $this->changeRequests()->pending()->latest()->first();
    }

    /** @return HasMany<Link, $this> */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class, 'artist_id');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */

    /* * * * * * * * SCOPES & HELPERS * * * * * * * */
    public function scopePublished($query)
    {
        return $query->where('enum_status', ArtistStatus::PUBLISHED->value);
    }

    public function isPublished(): bool
    {
        return $this->enum_status === ArtistStatus::PUBLISHED;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
    /* * * * * * * * END - SCOPES & HELPERS * * * * * * * */

    /* * * * * * * * SCOUT * * * * * * * */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'artist_name' => $this->artist_name,
            'city' => $this->city,
            'discipline' => $this->disciplineMain?->label,
            'biography' => $this->biography,
        ];
    }
    /* * * * * * * * END - SCOUT * * * * * * * */

    /* * * * * * * * ACCESSORS (backward-compat with legacy template contracts) * * * * * * * */
    /** Maps $artist->name → artist_name for card/template compatibility. */
    protected function name(): Attribute
    {
        return Attribute::make(get: fn () => $this->artist_name);
    }

    /** Maps $artist->discipline → primary discipline label. */
    protected function discipline(): Attribute
    {
        return Attribute::make(get: fn () => $this->disciplineMain !== null ? $this->disciplineMain->label : '');
    }

    /** Maps $artist->secondary_discipline → secondary discipline label. */
    protected function secondaryDiscipline(): Attribute
    {
        return Attribute::make(get: fn () => $this->disciplineSecondary !== null ? $this->disciplineSecondary->label : null);
    }
    /* * * * * * * * END - ACCESSORS * * * * * * * */
}
