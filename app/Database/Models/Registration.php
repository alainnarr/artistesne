<?php

declare(strict_types=1);

namespace App\Database\Models;

use App\Contracts\RepositoryableContract;
use App\Database\Model;
use App\Database\Traits\PreventDelete;
use App\Enums\RegistrationStatus;
use Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Validation\Rules\Enum;

class Registration extends Model implements RepositoryableContract
{
    use HasFactory;
    use PreventDelete;

    protected $table = 'registrations';

    protected $fillable = [
        'real_name',
        'artist_name',
        'slug',
        'birth_date',
        'email',
        'phone',
        'residence_location',
        'locality',
        'canton_link',
        'discipline_main',
        'discipline_secondary',
        'training',
        'paid_work',
        'recognition',
        'recent_achievements',
        'last_work',
        'enum_status',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
    ];

    protected $appends = [];

    /** @return array<string, class-string|'datetime'> */
    protected function casts(): array
    {
        return [
            'enum_status' => RegistrationStatus::class,
            'birth_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return RegistrationFactory<Registration, $this> */
    protected static function newFactory(): RegistrationFactory
    {
        return RegistrationFactory::new();
    }
    /* * * * * * * * VALIDATION * * * * * * * */
    /** @return array<string, string|array> */
    public static function getRules(array $fields = [], $register = null): array
    {
        $id = $register['id'] ?? null;

        $rules = [
            'real_name' => 'required|string|max:125',
            'artist_name' => 'required|string|max:125',
            'slug' => 'required|string|max:255|unique:registrations,slug,' . $id . ',id',
            'birth_date' => 'required|date',
            'email' => 'required|email|max:125|unique:registrations,email,' . $id . ',id',
            'phone' => 'required|string|max:15',
            'residence_location' => 'required|string|max:125',
            'locality' => 'nullable|string|max:125',
            'canton_link' => 'nullable|string',
            'discipline_main' => 'required|integer|exists:disciplines,id',
            'discipline_secondary' => 'nullable|integer|exists:disciplines,id',
            'training' => 'nullable|string',
            'paid_work' => 'nullable|string',
            'recognition' => 'nullable|string',
            'recent_achievements' => 'nullable|string',
            'last_work' => 'nullable|string',
            'enum_status' => ['required', new Enum(RegistrationStatus::class)],
            'reviewed_at' => 'nullable|date',
            'reviewed_by' => 'nullable|integer|exists:users,id',
            'review_notes' => 'nullable|string',
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    /** @return BelongsToMany<Activity, $this> */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activities_registrations', 'registration_id', 'activity_id');
    }

    /** @return HasMany<ActivityRegistration, $this> */
    public function activitiesRegistrations(): HasMany
    {
        return $this->hasMany(ActivityRegistration::class, 'registration_id');
    }

    /** @return BelongsTo<Discipline, $this> */
    public function disciplineMain(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'discipline_main');
    }

    /** @return BelongsTo<Discipline, $this> */
    public function disciplineSecondary(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'discipline_secondary');
    }

    /** @return HasOne<Artist, $this> */
    public function artist(): HasOne
    {
        return $this->hasOne(Artist::class, 'registration_id');
    }

    /** @return MorphMany<Repository, $this> */
    public function repositories(): MorphMany
    {
        return $this->morphMany(Repository::class, 'repositoryable');
    }

    /** @return HasMany<Link, $this> */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class, 'registration_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */

    /* * * * * * * * ACCESSORS * * * * * * * */
    public function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->artist_name ?: $this->real_name,
        );
    }

    public function city(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->locality ?: $this->residence_location,
        );
    }
    /* * * * * * * * END - ACCESSORS * * * * * * * */
}
