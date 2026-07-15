<?php

namespace App\Database\Models;

use App\Contracts\RepositoryableContract;
use App\Database\Model;
use App\Enums\ArtistShowContact;
use App\Enums\ArtistStatus;
use Database\Factories\ArtistFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Scout\Searchable;

class Artist extends Model implements RepositoryableContract
{
    use HasFactory;
    use Searchable;

    protected static function newFactory(): ArtistFactory
    {
        return ArtistFactory::new();
    }

    protected $table = 'artists';

    protected $fillable = [
        'registration_id',
        'user_id',
        'slug',
        'artist_name',
        'email',
        'phone',
        'rep_image',
        'biography',
        'city',
        'discipline_main_id',
        'discipline_secondary',
        'activities',
        'secondary_activities',
        'keywords',
        'links',
        'collaborations',
        'enum_status',
        'enum_show_contact',
        'published_at',
        'last_confirmed_at',
        'reminder_sent_at',
        'confirmation_token',
    ];

    protected $casts = [
        'enum_status' => ArtistStatus::class,
        'enum_show_contact' => ArtistShowContact::class,
        'activities' => 'array',
        'secondary_activities' => 'array',
        'keywords' => 'array',
        'links' => 'array',
        'collaborations' => 'array',
        'published_at' => 'datetime',
        'last_confirmed_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    /* * * * * * * * VALIDATION * * * * * * * */
    public static function getRules(array $fields = [], $register = null): array
    {
        return [];
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * SCOPES & HELPERS * * * * * * * */
    public function scopePublished($query)
    {
        return $query->where('enum_status', ArtistStatus::Published->value);
    }

    public function isPublished(): bool
    {
        return $this->enum_status === ArtistStatus::Published;
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

    /* * * * * * * * RELATIONS * * * * * * * */
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

    /** @return BelongsTo<Repository, $this> */
    public function repImage(): BelongsTo
    {
        return $this->belongsTo(Repository::class, 'rep_image');
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
}
