<?php

declare(strict_types=1);

namespace App\Database\Models;

use App\Database\Concerns\HasApprovalStatus;
use App\Database\Model;
use App\Database\Traits\PreventDelete;
use App\Enums\ApprovalStatus;
use Database\Factories\ArtistChangeRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistChangeRequest extends Model
{
    use HasApprovalStatus;
    use HasFactory;
    use PreventDelete;

    protected static function newFactory(): ArtistChangeRequestFactory
    {
        return ArtistChangeRequestFactory::new();
    }

    protected $table = 'artist_change_requests';

    protected $fillable = [
        'artist_id',
        'submitted_by',
        'payload',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApprovalStatus::class,
            'payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    /* * * * * * * * VALIDATION * * * * * * * */
    public static function getRules(array $fields = [], $register = null): array
    {
        $rules = [
            'artist_id' => 'required|integer|exists:artists,id',
            'submitted_by' => 'required|integer|exists:users,id',
            'payload' => 'required|array',
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
        return $this->belongsTo(Artist::class);
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */

    /**
     * Apply the proposed payload to the related artist.
     *
     * Only keys in the explicit allowlist are applied to prevent privilege
     * escalation (e.g. an attacker crafting a payload with user_id or slug).
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

        if (! empty($safe)) {
            $this->artist->update($safe);
        }
    }
}
