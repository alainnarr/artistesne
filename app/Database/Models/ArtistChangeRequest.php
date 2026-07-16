<?php

declare(strict_types=1);

namespace App\Database\Models;

use App\Database\Model;
use App\Database\Traits\PreventUpdate;
use App\Database\Traits\PreventDelete;
use App\Enums\ArtistChangeRequestStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Validation\Rules\Enum;
use App\Database\Concerns\HasApprovalStatus;
use App\Enums\ApprovalStatus;
use App\Enums\ArtistStatus;
use Database\Factories\ArtistChangeRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArtistChangeRequest extends Model
{
    use HasApprovalStatus;
    use HasFactory;
    use PreventDelete;
    use PreventUpdate;

    protected $table = 'artist_change_requests';

    /** @return ArtistChangeRequestFactory<ArtistChangeRequest, $this> */
    protected static function newFactory(): ArtistChangeRequestFactory
    {
        return ArtistChangeRequestFactory::new();
    }

    protected $fillable = [
        'artist_id',
        'payload',
        'enum_status',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
    ];

    protected $updatable = [
        'enum_status',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
    ];

    /** @return array<string, class-string|'datetime'> */
    protected function casts(): array
    {
        return [
            'enum_status' => ArtistChangeRequestStatus::class,
            'status' => ApprovalStatus::class,
            'payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    /* * * * * * * * VALIDATION * * * * * * * */
    /** @return array<string, string|array> */
    public static function getRules(array $fields = [], $register = null): array
    {
        $rules = [
            'artist_id' => 'required|integer|exists:artists,id',
            'payload' => 'required|json',
            'enum_status' => ['required', new Enum(ArtistChangeRequestStatus::class)],
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
    /** @return BelongsTo<Artist, $this> */
    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }

    /** @return MorphMany<Repository, $this> */
    public function repositories(): MorphMany
    {
        return $this->morphMany(Repository::class, 'repositoryable');
    }

    /** @return MorphOne<Repository, $this> */
    public function image(): MorphOne
    {
        return $this->morphOne(Repository::class, 'repositoryable');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */

    /**
     * Apply the proposed payload to the related artist and publish it.
     *
     * Only keys in the explicit allowlist are applied to prevent privilege
     * escalation (e.g. an attacker crafting a payload with user_id or slug).
     *
     * Admin approval doubles as the publication step: whether this is the
     * artist's very first submission or a later edit, approving it makes
     * the profile (re)appear in the public directory.
     */
    public function apply(): void
    {
        $allowed = [
            'artist_name',
            'biography',
            'city',
            'discipline_main_id',
            'discipline_secondary',
            'activities',
            'secondary_activities',
            'keywords',
            'links',
            'collaborations',
            'enum_show_contact',
        ];

        $safe = array_intersect_key($this->payload, array_flip($allowed));

        $safe['enum_status'] = ArtistStatus::Published->value;
        $safe['published_at'] = $this->artist->published_at ?? now();

        $this->artist->update($safe);
    }
}
