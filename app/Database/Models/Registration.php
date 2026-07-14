<?php

namespace App\Database\Models;

use App\Database\Model;
use App\Database\Traits\PreventDelete;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Registration extends Model
{
    use PreventDelete;

    protected $table = 'registrations';

    protected $fillable = [
        'real_name',
        'artist_name',
        'url',
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

    protected function casts(): array
    {
        return [
            'enum_status' => RegistrationStatus::class,
        ];
    }

    /* * * * * * * * VALIDATION * * * * * * * */
    public static function getRules(array $fields = [], $register = null): array
{
    $id = $register['id'] ?? null;

    $rules = [
        'real_name' => 'required|string|max:125',
        'artist_name' => 'required|string|max:125',
        'url' => 'required|string|max:255',
        'birth_date' => 'required|date',
        'email' => [
            'required',
            'email',
            'max:125',
            Rule::unique('registrations', 'email')->ignore($id),
        ],
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
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activities_registrations', 'registration_id', 'activity_id');
    }

    public function activitiesRegistrations(): HasMany
    {
        return $this->hasMany(ActivityRegistration::class, 'registration_id');
    }

    public function disciplineMain(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'discipline_main');
    }

    public function disciplineSecondary(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'discipline_secondary');
    }

    public function artist(): HasOne
    {
        return $this->hasOne(Artist::class, 'registration_id');
    }

    public function repositories(): MorphMany
    {
        return $this->morphMany(Repository::class, 'repositoryable');
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class, 'registration_id');
    }

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
